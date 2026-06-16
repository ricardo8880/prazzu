<?php

namespace App\Http\Controllers;


use App\Support\CachedSchema;
use App\Models\PortalDocumento;
use App\Models\PortalMensagem;
use App\Models\ItemControle;
use App\Models\PortalSolicitacao;
use App\Services\ItemControleStatusService;
use App\Support\AtendimentoPortalService;
use App\Support\ItemControleAnexoUploader;
use App\Support\PortalClienteData;
use App\Support\PortalChatMessageContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Throwable;
use Illuminate\View\View;

class PortalClientePublicoController extends Controller
{
    public function __construct(private readonly ItemControleStatusService $statusService)
    {
    }

    public function show(string $token): View
    {
        $empresa = $this->empresaPorToken($token);

        abort_if(! $empresa, 404);
        abort_if(! $this->portalDisponivel($empresa), 403, 'Portal indisponível ou expirado.');

        $empresaId = (int) $empresa->id;

        return view('portal.cliente.show', PortalClienteData::data($empresaId, true) + [
            'token' => $token,
            'modoPublico' => true,
            'socketIoConfig' => $this->socketIoConfigCliente($empresaId, $token),
        ]);
    }


    /**
     * Dados usados pelo front público para entrar na sala Socket.IO da empresa.
     * O socket valida a assinatura com o mesmo APP_KEY do Laravel.
     *
     * @return array<string, mixed>
     */
    private function socketIoConfigCliente(int $empresaId, string $token): array
    {
        $actor = 'cliente';
        $secret = (string) config('app.key');
        $room = 'empresa:' . $empresaId . ':portal';

        return [
            'enabled' => true,
            'url' => rtrim((string) env('VITE_SOCKET_IO_URL', env('SOCKET_IO_URL', 'http://127.0.0.1:3001')), '/'),
            'empresaId' => $empresaId,
            'actor' => $actor,
            'token' => $token,
            'room' => $room,
            'roomScope' => 'portal',
            'signature' => hash_hmac('sha256', $empresaId . '|' . $actor . '|' . $token . '|' . $room, $secret),
            'syncUrl' => route('portal.cliente.mensagens-novas', ['token' => $token]),
            'seenUrl' => url('/portal/cliente/' . $token . '/mensagem-visualizada'),
        ];
    }

    public function debugLog(Request $request, string $token): \Illuminate\Http\JsonResponse
    {
        $empresa = $this->empresaPorToken($token);

        abort_if(! $empresa, 404);
        abort_if(! $this->portalDisponivel($empresa), 403, 'Portal indisponível ou expirado.');

        $payload = $request->validate([
            'step' => ['nullable', 'string', 'max:160'],
            'page' => ['nullable', 'string', 'max:255'],
            'url' => ['nullable', 'string', 'max:1000'],
            'pathname' => ['nullable', 'string', 'max:500'],
            'timestamp' => ['nullable', 'string', 'max:120'],
            'target' => ['nullable', 'string', 'max:120'],
            'tag' => ['nullable', 'string', 'max:80'],
            'text' => ['nullable', 'string', 'max:180'],
            'id' => ['nullable', 'string', 'max:180'],
            'className' => ['nullable', 'string', 'max:500'],
            'href' => ['nullable', 'string', 'max:1000'],
            'name' => ['nullable', 'string', 'max:180'],
            'type' => ['nullable', 'string', 'max:80'],
            'tamanhoMensagem' => ['nullable', 'integer'],
            'quantidadeArquivos' => ['nullable', 'integer'],
            'duration_ms' => ['nullable', 'numeric'],
            'status' => ['nullable', 'integer'],
            'message_id' => ['nullable', 'integer'],
            'total_mensagens' => ['nullable', 'integer'],
            'ultimo_id' => ['nullable', 'integer'],
            'erro' => ['nullable', 'string', 'max:1000'],
            'fase' => ['nullable', 'string', 'max:120'],
            'socket_id' => ['nullable', 'string', 'max:120'],
            'socket_connected' => ['nullable', 'boolean'],
            'ack' => ['nullable'],
        ]);

        

        return response()->json(['ok' => true]);
    }


    public function digitando(Request $request, string $token): JsonResponse
    {
        $inicio = microtime(true);
        $empresa = $this->empresaPorToken($token);

        abort_if(! $empresa, 404);
        abort_if(! $this->portalDisponivel($empresa), 403, 'Portal indisponível ou expirado.');

        $dados = $request->validate([
            'nome' => ['nullable', 'string', 'max:255'],
        ]);

        $nome = trim((string) ($dados['nome'] ?? ''));

        Cache::put($this->cacheKeyClienteDigitando((int) $empresa->id), [
            'nome' => $nome !== '' ? $nome : ($empresa->nome_fantasia ?? $empresa->razao_social ?? 'Cliente'),
            'timestamp' => now()->timestamp,
        ], now()->addSeconds(10));

        

        return response()->json(['ok' => true]);
    }

    public function mensagem(Request $request, string $token): RedirectResponse|JsonResponse
    {
        $inicio = microtime(true);
        $empresa = $this->empresaPorToken($token);

        abort_if(! $empresa, 404);
        abort_if(! $this->portalDisponivel($empresa), 403, 'Portal indisponível ou expirado.');
        abort_if(! CachedSchema::hasTable('portal_mensagens'), 500, 'Tabela portal_mensagens não encontrada.');

        

        $validator = Validator::make($request->all(), [
            'nome' => ['nullable', 'string', 'min:2', 'max:255', 'not_regex:/^\s*$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'mensagem' => ['nullable', 'string', 'max:5000'],
            'anexos' => ['nullable', 'array', 'max:5'],
            'anexos.*' => ['bail', 'file', 'max:' . ItemControleAnexoUploader::MAX_SIZE_KB, 'mimes:' . implode(',', ItemControleAnexoUploader::ALLOWED_EXTENSIONS), 'mimetypes:' . implode(',', ItemControleAnexoUploader::ALLOWED_MIME_TYPES)],
            'website' => ['nullable', 'prohibited'],
            '_portal_ajax' => ['nullable'],
        ], [
            'nome.min' => 'Informe um nome com pelo menos 2 caracteres.',
            'nome.not_regex' => 'Informe um nome válido.',
            'email.email' => 'Informe um e-mail válido.',
            'mensagem.max' => 'A mensagem pode ter no máximo 5000 caracteres.',
            'anexos.array' => 'Envie os anexos novamente.',
            'anexos.max' => 'Envie no máximo 5 anexos por mensagem.',
            'anexos.*.file' => 'Um dos anexos não pôde ser lido. Selecione o arquivo novamente.',
            'anexos.*.max' => 'Cada anexo pode ter no máximo 10 MB.',
            'anexos.*.mimes' => 'Formato inválido. Envie PDF, Word, Excel, CSV, TXT ou imagem.',
            'anexos.*.mimetypes' => 'Tipo de arquivo não permitido. Envie um arquivo válido e seguro.',
            'website.prohibited' => 'Requisição inválida. Atualize a página e tente novamente.',
        ]);

        $validator->after(function ($validator) use ($request): void {
            $texto = trim((string) $request->input('mensagem', ''));
            $arquivos = array_values(array_filter($request->file('anexos', [])));

            if ($texto === '' && $arquivos === []) {
                $validator->errors()->add('mensagem', 'Escreva uma mensagem ou selecione ao menos um anexo para enviar.');
            }

            foreach ($arquivos as $arquivo) {
                $extensao = strtolower((string) $arquivo->getClientOriginalExtension());

                if (! in_array($extensao, ItemControleAnexoUploader::ALLOWED_EXTENSIONS, true)) {
                    $validator->errors()->add('anexos', 'O arquivo "' . $arquivo->getClientOriginalName() . '" possui extensão inválida.');
                }

                if (! $arquivo->isValid()) {
                    $validator->errors()->add('anexos', 'O arquivo "' . $arquivo->getClientOriginalName() . '" não pôde ser processado.');
                }
            }
        });

        if ($validator->fails()) {
            

            if ($this->querRespostaJsonPortal($request)) {
                return response()->json([
                    'ok' => false,
                    'message' => $validator->errors()->first() ?: 'Revise os dados enviados.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            return back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();
        $data['nome'] = trim((string) ($data['nome'] ?? '')) ?: ($empresa->nome_fantasia ?? $empresa->razao_social ?? 'Cliente do portal');
        $data['email'] = trim((string) ($data['email'] ?? '')) ?: ($empresa->email ?? null);
        $arquivos = array_values(array_filter($request->file('anexos', [])));
        $anexosArmazenados = [];

        try {
            foreach ($arquivos as $arquivo) {
                $inicioAnexo = microtime(true);
                

                $path = $arquivo->store('portal-cliente/chat', 'public');

                if (! $path) {
                    throw new \RuntimeException('Falha ao gravar anexo no disco público.');
                }

                $anexosArmazenados[] = [
                    'path' => $path,
                    'nome_original' => $arquivo->getClientOriginalName(),
                    'mime_type' => $arquivo->getClientMimeType(),
                    'tamanho_bytes' => $arquivo->getSize(),
                    'tipo' => str_starts_with((string) $arquivo->getClientMimeType(), 'image/') ? 'imagem' : 'documento',
                    'download_url' => asset('storage/' . $path),
                ];

                
            }

            $mensagemCriada = null;

            $inicioTransacao = microtime(true);
            DB::transaction(function () use ($empresa, $data, $anexosArmazenados, &$mensagemCriada): void {
                $texto = trim((string) ($data['mensagem'] ?? ''));
                $textoFinal = $this->mensagemComAnexos($texto, $anexosArmazenados);

                $mensagem = PortalMensagem::create($this->payloadMensagemCliente(
                    (int) $empresa->id,
                    trim((string) $data['nome']),
                    $data['email'] ?? null,
                    $textoFinal
                ));

                $this->registrarDocumentosDoChat((int) $empresa->id, $mensagem, $anexosArmazenados);

                $mensagemCriada = $mensagem;
            });

            

            if ($mensagemCriada instanceof PortalMensagem) {
                $mensagemIdAtendimento = (int) $mensagemCriada->id;
                $empresaIdAtendimento = (int) $empresa->id;
                $tokenHashAtendimento = hash('sha256', $token);

                app()->terminating(function () use ($mensagemIdAtendimento, $empresaIdAtendimento, $tokenHashAtendimento): void {
                    try {
                        $inicioAtendimento = microtime(true);
                        

                        $mensagemAtendimento = PortalMensagem::find($mensagemIdAtendimento);

                        if ($mensagemAtendimento instanceof PortalMensagem) {
                            app(AtendimentoPortalService::class)->registrarMensagem($mensagemAtendimento);
                        }

                        
                    } catch (Throwable $atendimentoException) {
                        
                    }
                });
            }
        } catch (Throwable $exception) {
            foreach ($anexosArmazenados as $anexo) {
                $this->removerArquivoPublico((string) ($anexo['path'] ?? ''));
            }

            

            if ($this->querRespostaJsonPortal($request)) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Não foi possível enviar a mensagem agora. Tente novamente em instantes.',
                ], 500);
            }

            return $this->redirectPortal($token, 'chat')
                ->withInput()
                ->withErrors(['mensagem' => 'Não foi possível enviar a mensagem agora. Tente novamente em instantes.']);
        }


        

        if ($this->querRespostaJsonPortal($request)) {
            return response()->json([
                'ok' => true,
                'message' => $anexosArmazenados === [] ? 'Mensagem enviada para a equipe.' : 'Mensagem e anexo(s) enviados para a equipe.',
                'chat_message' => $mensagemCriada instanceof PortalMensagem
                    ? $this->formatarMensagemTempoReal([
                        'id' => $mensagemCriada->id,
                        'origem' => $mensagemCriada->origem,
                        'css_class' => 'cliente',
                        'nome' => $mensagemCriada->nome,
                        'mensagem_texto' => $mensagemCriada->mensagem,
                        'created_at_label' => optional($mensagemCriada->created_at)->format('d/m/Y H:i') ?: 'agora',
                        'attachments' => $anexosArmazenados,
                        'room' => 'empresa:' . (int) $empresa->id . ':portal',
                    ])
                    : null,
            ]);
        }

        return $this->redirectPortal($token, 'chat')->with('success', $anexosArmazenados === [] ? 'Mensagem enviada para a equipe.' : 'Mensagem e anexo(s) enviados para a equipe.');
    }


    public function mensagensNovas(Request $request, string $token): JsonResponse
    {
        $empresa = $this->empresaPorToken($token);

        abort_if(! $empresa, 404);
        abort_if(! $this->portalDisponivel($empresa), 403, 'Portal indisponível ou expirado.');
        abort_if(! CachedSchema::hasTable('portal_mensagens'), 500, 'Tabela portal_mensagens não encontrada.');

        $empresaId = (int) $empresa->id;
        $afterId = max(0, $request->integer('after_id'));

        $mensagens = PortalMensagem::query()
            ->where('empresa_id', $empresaId)
            ->when($afterId > 0, fn ($query) => $query->where('id', '>', $afterId))
            ->orderBy('id')
            ->limit(50)
            ->get()
            ->map(function (PortalMensagem $mensagem): array {
                $origem = strtolower((string) $mensagem->origem);
                $classe = in_array($origem, ['interno', 'suporte', 'equipe'], true) ? 'equipe' : 'cliente';

                return $this->formatarMensagemTempoReal([
                    'id' => $mensagem->id,
                    'origem' => $mensagem->origem,
                    'css_class' => $classe,
                    'nome' => $mensagem->nome ?: ($classe === 'equipe' ? 'Equipe' : 'Cliente'),
                    'mensagem_texto' => $mensagem->mensagem,
                    'created_at_label' => optional($mensagem->created_at)->format('d/m/Y H:i') ?: 'agora',
                    'attachments' => [],
                    'room' => 'empresa:' . (int) $mensagem->empresa_id . ':portal',
                    'room_scope' => 'portal',
                ]);
            })
            ->values();


        return response()->json([
            'ok' => true,
            'messages' => $mensagens,
        ]);
    }


    public function mensagemVisualizada(Request $request, string $token): JsonResponse
    {
        $empresa = $this->empresaPorToken($token);

        abort_if(! $empresa, 404);
        abort_if(! $this->portalDisponivel($empresa), 403, 'Portal indisponível ou expirado.');
        abort_if(! CachedSchema::hasTable('portal_mensagens'), 500, 'Tabela portal_mensagens não encontrada.');

        $data = $request->validate([
            'message_id' => ['required', 'integer', 'min:1'],
        ]);

        $empresaId = (int) $empresa->id;
        $messageId = (int) $data['message_id'];

        Cache::put($this->cacheKeyVisualizadoCliente($empresaId), $messageId, now()->addHours(8));

        if (CachedSchema::hasColumn('portal_mensagens', 'visualizada_em')) {
            PortalMensagem::query()
                ->where('empresa_id', $empresaId)
                ->where('origem', 'interno')
                ->where('id', '<=', $messageId)
                ->whereNull('visualizada_em')
                ->update(['visualizada_em' => now()]);
        }

        return response()->json(['ok' => true]);
    }

    public function estadoChat(string $token): JsonResponse
    {
        $inicio = microtime(true);
        $empresa = $this->empresaPorToken($token);

        abort_if(! $empresa, 404);
        abort_if(! $this->portalDisponivel($empresa), 403, 'Portal indisponível ou expirado.');

        $empresaId = (int) $empresa->id;

        $this->registrarVisualizacaoCliente($empresaId);

        $mensagens = $this->mensagensTempoReal($empresaId);
        $supportTyping = $this->suporteEstaDigitando($empresaId);
        $supportTypingName = $this->nomeSuporteDigitando($empresaId);

        

        return response()->json([
            'ok' => true,
            'messages' => $mensagens,
            'support_typing' => $supportTyping,
            'support_typing_name' => $supportTypingName,
            'support_seen_until_id' => Cache::get($this->cacheKeyVisualizadoCliente($empresaId)),
            'client_seen_until_id' => Cache::get($this->cacheKeyVisualizadoSuporte($empresaId)),
        ]);
    }

    private function registrarVisualizacaoCliente(int $empresaId): void
    {
        if (! CachedSchema::hasTable('portal_mensagens')) {
            return;
        }

        $ultimoIdEquipe = PortalMensagem::query()
            ->where('empresa_id', $empresaId)
            ->where('origem', 'interno')
            ->max('id');

        if ($ultimoIdEquipe) {
            Cache::put($this->cacheKeyVisualizadoCliente($empresaId), (int) $ultimoIdEquipe, now()->addHours(8));

            if (CachedSchema::hasColumn('portal_mensagens', 'visualizada_em')) {
                PortalMensagem::query()
                    ->where('empresa_id', $empresaId)
                    ->where('origem', 'interno')
                    ->whereNull('visualizada_em')
                    ->where('id', '<=', (int) $ultimoIdEquipe)
                    ->update(['visualizada_em' => now()]);
            }
        }
    }

    private function suporteEstaDigitando(int $empresaId): bool
    {
        $estado = Cache::get($this->cacheKeySuporteDigitando($empresaId));

        if (! is_array($estado)) {
            return false;
        }

        if ((int) ($estado['timestamp'] ?? 0) < now()->subSeconds(8)->timestamp) {
            Cache::forget($this->cacheKeySuporteDigitando($empresaId));
            return false;
        }

        return true;
    }

    private function nomeSuporteDigitando(int $empresaId): ?string
    {
        $estado = Cache::get($this->cacheKeySuporteDigitando($empresaId));

        return is_array($estado) ? trim((string) ($estado['nome'] ?? 'Suporte')) : null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function mensagensTempoReal(int $empresaId): array
    {
        if (! CachedSchema::hasTable('portal_mensagens')) {
            return [];
        }

        return PortalMensagem::query()
            ->where('empresa_id', $empresaId)
            ->when(
                CachedSchema::hasColumn('portal_mensagens', 'conversa_status'),
                fn ($query) => $query->where('conversa_status', 'aberta')
            )
            ->oldest()
            ->limit(120)
            ->get()
            ->map(fn (PortalMensagem $mensagem): array => $this->formatarMensagemTempoReal([
                'id' => $mensagem->id,
                'origem' => $mensagem->origem,
                'css_class' => in_array((string) $mensagem->origem, ['cliente', 'portal_cliente', 'client'], true) ? 'cliente' : 'equipe',
                'nome' => $mensagem->nome,
                'mensagem_texto' => $mensagem->mensagem,
                'created_at_label' => optional($mensagem->created_at)->format('d/m/Y H:i') ?: 'agora',
                'attachments' => [],
                'room' => 'empresa:' . $empresaId . ':portal',
                'room_scope' => 'portal',
            ]))
            ->values()
            ->all();
    }

    private function formatarMensagemTempoReal(array $mensagem): array
    {
        $empresaId = $this->empresaIdFromRoom((string) ($mensagem['room'] ?? ''));

        return PortalChatMessageContract::fromArray($mensagem, [
            'empresa_id' => $empresaId,
            'room' => (string) ($mensagem['room'] ?? ''),
            'room_scope' => (string) ($mensagem['room_scope'] ?? 'portal'),
        ]);
    }


    /**
     * @return array<int, array<string, mixed>>
     */
    private function normalizarAnexosMensagem(mixed $anexos): array
    {
        if (! is_iterable($anexos)) {
            return [];
        }

        return collect($anexos)
            ->map(function (mixed $anexo): array {
                $item = is_array($anexo) ? $anexo : [];
                $url = (string) ($item['url'] ?? $item['download_url'] ?? $item['preview_url'] ?? '');
                $mime = (string) ($item['mime_type'] ?? $item['mime'] ?? '');
                $sizeBytes = (int) ($item['size'] ?? $item['tamanho_bytes'] ?? 0);

                return [
                    'url' => $url,
                    'name' => (string) ($item['name'] ?? $item['nome'] ?? $item['nome_original'] ?? 'Anexo'),
                    'size' => (string) ($item['size_label'] ?? ($sizeBytes > 0 ? $this->formatarBytes($sizeBytes) : ($mime !== '' ? $mime : 'arquivo'))),
                    'mime_type' => $mime,
                    'is_image' => (bool) ($item['is_image'] ?? $item['is_imagem'] ?? str_starts_with($mime, 'image/')),
                ];
            })
            ->filter(fn (array $anexo): bool => trim((string) ($anexo['url'] ?? '')) !== '')
            ->values()
            ->all();
    }

    /**
     * Extrai anexos gravados no texto legado: "- nome | url | mime | bytes".
     * @return array<int, array<string, mixed>>
     */
    private function extrairAnexosMensagem(string $texto): array
    {
        if ($texto === '' || ! str_contains($texto, 'Anexos enviados:')) {
            return [];
        }

        preg_match_all('/^-\s*(.+?)\s*\|\s*(https?:\/\/\S+)(?:\s*\|\s*([^|\r\n]+))?(?:\s*\|\s*([^\r\n]+))?/mi', $texto, $matches, PREG_SET_ORDER);

        return collect($matches)
            ->map(function (array $match): array {
                $nome = trim((string) ($match[1] ?? 'Anexo')) ?: 'Anexo';
                $url = trim((string) ($match[2] ?? ''));
                $mime = trim((string) ($match[3] ?? ''));
                $size = (int) trim((string) ($match[4] ?? '0'));

                return [
                    'url' => $url,
                    'name' => $nome,
                    'size' => $size > 0 ? $this->formatarBytes($size) : ($mime !== '' ? $mime : 'arquivo'),
                    'mime_type' => $mime,
                    'is_image' => str_starts_with($mime, 'image/'),
                ];
            })
            ->filter(fn (array $anexo): bool => $anexo['url'] !== '')
            ->values()
            ->all();
    }

    private function removerBlocoAnexosMensagem(string $texto): string
    {
        if ($texto === '' || ! str_contains($texto, 'Anexos enviados:')) {
            return $texto;
        }

        $limpo = preg_replace('/\n?Anexos enviados:\s*(?:\n-\s*.+?(?:\r?\n|$))+/si', '', $texto) ?? $texto;

        return trim($limpo) !== '' ? trim($limpo) : 'Arquivo(s) enviado(s).';
    }

    private function formatarBytes(int $bytes): string
    {
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1, ',', '.') . ' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 1, ',', '.') . ' KB';
        }

        return $bytes . ' B';
    }


    private function empresaIdFromRoom(string $room): int
    {
        if (preg_match('/^empresa:(\d+):/', $room, $matches) !== 1) {
            return 0;
        }

        return (int) $matches[1];
    }

    private function socketMessageSignature(int $empresaId, string $room, string $actor, int $messageId): string
    {
        return hash_hmac('sha256', $empresaId . '|' . $room . '|' . $actor . '|' . $messageId, (string) config('app.key'));
    }

    public function solicitacao(Request $request, string $token): RedirectResponse
    {
        $empresa = $this->empresaPorToken($token);

        abort_if(! $empresa, 404);
        abort_if(! $this->portalDisponivel($empresa), 403, 'Portal indisponível ou expirado.');
        abort_if(! CachedSchema::hasTable('portal_solicitacoes'), 500, 'Tabela portal_solicitacoes não encontrada.');

        $data = $request->validate([
            'titulo' => ['bail', 'required', 'string', 'min:3', 'max:255', 'not_regex:/^\s*$/'],
            'descricao' => ['bail', 'required', 'string', 'min:5', 'max:5000', 'not_regex:/^\s*$/'],
            'prioridade' => ['required', 'in:baixa,media,alta,urgente'],
            'item_controle_id' => ['nullable', 'integer'],
            'website' => ['nullable', 'prohibited'],
            '_portal_ajax' => ['nullable'],
        ], [
            'titulo.required' => 'Informe um título para a solicitação.',
            'titulo.min' => 'O título precisa ter pelo menos 3 caracteres.',
            'titulo.not_regex' => 'Informe um título válido para a solicitação.',
            'descricao.required' => 'Descreva a solicitação antes de enviar.',
            'descricao.min' => 'A descrição precisa ter pelo menos 5 caracteres.',
            'descricao.not_regex' => 'Informe uma descrição válida para a solicitação.',
            'prioridade.required' => 'Selecione a prioridade.',
            'prioridade.in' => 'Selecione uma prioridade válida.',
            'website.prohibited' => 'Requisição inválida. Atualize a página e tente novamente.',
        ]);

        $item = $this->itemPortalDaEmpresa((int) $empresa->id, $data['item_controle_id'] ?? null);

        if (! empty($data['item_controle_id']) && ! $item) {
            return back()
                ->withInput()
                ->withErrors(['item_controle_id' => 'O item selecionado não está disponível para este portal.']);
        }

        try {
            DB::transaction(function () use ($empresa, $data, $item): void {
                $payload = [
                    'empresa_id' => (int) $empresa->id,
                    'titulo' => $data['titulo'],
                    'descricao' => $data['descricao'],
                    'prioridade' => $data['prioridade'],
                    'status' => 'aberto',
                ];

                if ($item && CachedSchema::hasColumn('portal_solicitacoes', 'item_controle_id')) {
                    $payload['item_controle_id'] = $item->id;
                }

                if (CachedSchema::hasColumn('portal_solicitacoes', 'origem')) {
                    $payload['origem'] = 'portal_cliente';
                }

                $solicitacao = PortalSolicitacao::query()->create($payload);

                $atendimento = app(AtendimentoPortalService::class)->registrarSolicitacao($solicitacao);

                if (! $atendimento) {
                    throw new \RuntimeException('Solicitação salva no portal, mas não foi possível gerar atendimento operacional.');
                }

                if ($item) {
                    $this->statusService->registrarSolicitacaoCliente($item, [
                        'solicitacao_id' => $solicitacao->id,
                        'prioridade' => $data['prioridade'],
                    ]);
                }
            });
        } catch (Throwable $exception) {
            

            return $this->redirectPortal($token, 'solicitacao')
                ->withInput()
                ->withErrors(['solicitacao' => 'Não foi possível abrir a solicitação agora. Tente novamente em instantes.']);
        }

        return $this->redirectPortal($token, 'chat')->with('success', 'Solicitação aberta com sucesso. A equipe já recebeu seu pedido.');
    }


    public function responderPendencia(Request $request, string $token, int|string $solicitacao): RedirectResponse
    {
        $empresa = $this->empresaPorToken($token);

        abort_if(! $empresa, 404);
        abort_if(! $this->portalDisponivel($empresa), 403, 'Portal indisponível ou expirado.');
        abort_if(! CachedSchema::hasTable('portal_solicitacoes'), 500, 'Tabela portal_solicitacoes não encontrada.');
        abort_if(! CachedSchema::hasTable('portal_mensagens'), 500, 'Tabela portal_mensagens não encontrada.');

        $solicitacao = PortalSolicitacao::query()
            ->whereKey($solicitacao)
            ->where('empresa_id', (int) $empresa->id)
            ->firstOrFail();

        $data = $request->validate([
            'nome' => ['nullable', 'string', 'min:2', 'max:255', 'not_regex:/^\s*$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'resposta' => ['bail', 'required', 'string', 'min:2', 'max:5000', 'not_regex:/^\s*$/'],
            'website' => ['nullable', 'prohibited'],
            '_portal_ajax' => ['nullable'],
        ], [
            'nome.min' => 'Informe um nome com pelo menos 2 caracteres.',
            'nome.not_regex' => 'Informe um nome válido.',
            'email.email' => 'Informe um e-mail válido.',
            'resposta.required' => 'Escreva a resposta antes de enviar.',
            'resposta.min' => 'A resposta precisa ter pelo menos 2 caracteres.',
            'resposta.not_regex' => 'Escreva uma resposta válida antes de enviar.',
            'website.prohibited' => 'Requisição inválida. Atualize a página e tente novamente.',
        ]);

        $data['nome'] = trim((string) ($data['nome'] ?? '')) ?: ($empresa->nome_fantasia ?? $empresa->razao_social ?? 'Cliente do portal');
        $data['email'] = trim((string) ($data['email'] ?? '')) ?: ($empresa->email ?? null);

        try {
            DB::transaction(function () use ($empresa, $solicitacao, $data): void {
                $mensagem = PortalMensagem::create($this->payloadMensagemCliente((int) $empresa->id, trim((string) $data['nome']), $data['email'] ?? null, sprintf(
                    "Resposta da pendência: %s\n\n%s",
                    $solicitacao->titulo,
                    trim((string) $data['resposta'])
                ), $solicitacao->item_controle_id));

                $update = [];

                if (CachedSchema::hasColumn('portal_solicitacoes', 'status')) {
                    $update['status'] = 'aguardando_equipe';
                }

                if (CachedSchema::hasColumn('portal_solicitacoes', 'resposta')) {
                    $update['resposta'] = trim((string) $data['resposta']);
                }

                if (! empty($update)) {
                    $solicitacao->forceFill($update)->save();
                }

                $atendimento = app(AtendimentoPortalService::class)->registrarRespostaSolicitacao($solicitacao->refresh(), $mensagem);

                if (! $atendimento) {
                    throw new \RuntimeException('Resposta salva no portal, mas não foi possível registrar no atendimento operacional.');
                }

                if ($solicitacao->item_controle_id) {
                    $item = $this->itemPortalDaEmpresa((int) $empresa->id, $solicitacao->item_controle_id);

                    if ($item) {
                        $this->statusService->registrarRespostaPendencia($item, [
                            'solicitacao_id' => $solicitacao->id,
                            'nome' => trim((string) $data['nome']),
                            'email' => $data['email'] ?? null,
                        ]);
                    }
                }
            });
        } catch (Throwable $exception) {
            

            return $this->redirectPortal($token, 'pendencias')
                ->withInput()
                ->withErrors(['resposta' => 'Não foi possível enviar a resposta agora. Tente novamente em instantes.']);
        }

        return $this->redirectPortal($token, 'chat')->with('success', 'Resposta enviada para a equipe.');
    }


    /**
     * @param  array<int, array<string, mixed>>  $anexos
     */
    private function mensagemComAnexos(string $mensagem, array $anexos): string
    {
        $partes = [];

        if (trim($mensagem) !== '') {
            $partes[] = trim($mensagem);
        }

        if ($anexos !== []) {
            $linhas = ['Anexos enviados:'];

            foreach ($anexos as $anexo) {
                $nome = trim((string) ($anexo['nome_original'] ?? 'Anexo')) ?: 'Anexo';
                $url = trim((string) ($anexo['download_url'] ?? ''));
                $mime = trim((string) ($anexo['mime_type'] ?? ''));

                if ($url !== '') {
                    $linhas[] = '- ' . $nome . ' | ' . $url . ($mime !== '' ? ' | ' . $mime : '');
                }
            }

            $partes[] = implode("
", $linhas);
        }

        return trim(implode("

", array_filter($partes)));
    }

    /**
     * @param  array<int, array<string, mixed>>  $anexos
     */
    private function registrarDocumentosDoChat(int $empresaId, PortalMensagem $mensagem, array $anexos): void
    {
        if ($anexos === [] || ! CachedSchema::hasTable('portal_documentos')) {
            return;
        }

        foreach ($anexos as $anexo) {
            $payload = [
                'empresa_id' => $empresaId,
                'titulo' => mb_substr((string) ($anexo['nome_original'] ?? 'Anexo do chat'), 0, 255),
                'tipo' => (string) ($anexo['tipo'] ?? 'documento'),
                'conteudo' => 'Arquivo enviado pelo chat do Portal do Cliente na mensagem #' . $mensagem->id . '.',
                'arquivo' => (string) ($anexo['path'] ?? ''),
                'visivel_cliente' => true,
                'criado_por' => null,
            ];

            if ($mensagem->item_controle_id && CachedSchema::hasColumn('portal_documentos', 'item_controle_id')) {
                $payload['item_controle_id'] = (int) $mensagem->item_controle_id;
            }

            PortalDocumento::query()->create($payload);
        }
    }

    private function removerArquivoPublico(string $path): void
    {
        if ($path === '') {
            return;
        }

        try {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        } catch (Throwable) {
            // Evita transformar falha de limpeza em erro para o cliente; o erro principal já foi registrado no log.
        }
    }

    private function itemPortalDaEmpresa(int $empresaId, int|string|null $itemControleId): ?ItemControle
    {
        if (blank($itemControleId) || ! CachedSchema::hasTable('item_controles')) {
            return null;
        }

        return ItemControle::query()
            ->whereKey($itemControleId)
            ->where('empresa_id', $empresaId)
            ->when(CachedSchema::hasColumn('item_controles', 'portal_ativo'), fn ($query) => $query->where('portal_ativo', true))
            ->first();
    }

    private function empresaPorToken(string $token): ?object
    {
        if (! CachedSchema::hasTable('empresas') || ! CachedSchema::hasColumn('empresas', 'portal_token')) {
            return null;
        }

        return DB::table('empresas')->where('portal_token', $token)->first();
    }


    private function payloadMensagemCliente(int $empresaId, string $nome, ?string $email, string $mensagem, ?int $itemControleId = null): array
    {
        $payload = [
            'empresa_id' => $empresaId,
            'nome' => $nome,
            'email' => $email,
            'mensagem' => $mensagem,
            'origem' => 'cliente',
        ];

        if ($itemControleId && CachedSchema::hasColumn('portal_mensagens', 'item_controle_id')) {
            $payload['item_controle_id'] = $itemControleId;
        }

        if (CachedSchema::hasColumn('portal_mensagens', 'conversa_status')) {
            $payload['conversa_status'] = 'aberta';
        }

        return $payload;
    }


    private function querRespostaJsonPortal(Request $request): bool
    {
        return $request->expectsJson()
            || $request->ajax()
            || $request->boolean('_portal_ajax')
            || $request->headers->get('X-Portal-Ajax') === '1'
            || str_contains((string) $request->headers->get('Accept'), 'application/json');
    }

    private function redirectPortal(string $token, string $anchor = 'chat'): RedirectResponse
    {
        $anchor = trim($anchor, '#');

        return redirect()->to(route('portal.cliente.show', ['token' => $token]) . ($anchor !== '' ? '#' . $anchor : ''));
    }

    private function portalDisponivel(object $empresa): bool
    {
        if (CachedSchema::hasColumn('empresas', 'portal_ativo') && ! (bool) ($empresa->portal_ativo ?? false)) {
            return false;
        }

        if (CachedSchema::hasColumn('empresas', 'portal_expira_em') && ! empty($empresa->portal_expira_em)) {
            return now()->lessThanOrEqualTo($empresa->portal_expira_em);
        }

        return true;
    }
    private function cacheKeyClienteDigitando(int $empresaId): string
    {
        return 'portal_cliente_digitando_empresa_' . $empresaId;
    }

    private function cacheKeySuporteDigitando(int $empresaId): string
    {
        return 'portal_suporte_digitando_empresa_' . $empresaId;
    }

    private function cacheKeyVisualizadoCliente(int $empresaId): string
    {
        return 'portal_cliente_visualizou_suporte_empresa_' . $empresaId;
    }

    private function cacheKeyVisualizadoSuporte(int $empresaId): string
    {
        return 'portal_suporte_visualizou_cliente_empresa_' . $empresaId;
    }
}
