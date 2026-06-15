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

        return view('portal.cliente.show', PortalClienteData::data((int) $empresa->id, true) + [
            'token' => $token,
            'modoPublico' => true,
        ]);
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
        ]);

        Log::info('[PORTAL_CLIENTE_PUBLICO_DEBUG] ' . ($payload['step'] ?? 'browser'), array_merge([
            'empresa_id' => (int) $empresa->id,
            'token_hash' => hash('sha256', $token),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ], $payload));

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

        Log::info('[PORTAL_CHAT_CLIENTE_DIGITANDO] estado', [
            'empresa_id' => (int) $empresa->id,
            'token_hash' => hash('sha256', $token),
            'nome_informado' => $nome !== '',
            'ip' => $request->ip(),
            'duracao_ms' => round((microtime(true) - $inicio) * 1000, 2),
        ]);

        return response()->json(['ok' => true]);
    }

    public function mensagem(Request $request, string $token): RedirectResponse|JsonResponse
    {
        $inicio = microtime(true);
        $empresa = $this->empresaPorToken($token);

        abort_if(! $empresa, 404);
        abort_if(! $this->portalDisponivel($empresa), 403, 'Portal indisponível ou expirado.');
        abort_if(! CachedSchema::hasTable('portal_mensagens'), 500, 'Tabela portal_mensagens não encontrada.');

        Log::info('[PORTAL_CHAT_CLIENTE_ENVIO] inicio', [
            'empresa_id' => (int) $empresa->id,
            'token_hash' => hash('sha256', $token),
            'ip' => $request->ip(),
            'ajax' => $this->querRespostaJsonPortal($request),
            'tamanho_mensagem' => strlen((string) $request->input('mensagem', '')),
            'quantidade_anexos' => count(array_filter($request->file('anexos', []))),
        ]);

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
            Log::warning('[PORTAL_CHAT_CLIENTE_ENVIO] validacao_falhou', [
                'empresa_id' => (int) $empresa->id,
                'token_hash' => hash('sha256', $token),
                'erros' => $validator->errors()->toArray(),
                'duracao_ms' => round((microtime(true) - $inicio) * 1000, 2),
            ]);

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
                Log::info('[PORTAL_CHAT_CLIENTE_ENVIO] anexo_inicio', [
                    'empresa_id' => (int) $empresa->id,
                    'nome' => $arquivo->getClientOriginalName(),
                    'mime' => $arquivo->getClientMimeType(),
                    'tamanho_bytes' => $arquivo->getSize(),
                ]);

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

                Log::info('[PORTAL_CHAT_CLIENTE_ENVIO] anexo_fim', [
                    'empresa_id' => (int) $empresa->id,
                    'path' => $path,
                    'duracao_ms' => round((microtime(true) - $inicioAnexo) * 1000, 2),
                ]);
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

            Log::info('[PORTAL_CHAT_CLIENTE_ENVIO] transacao_fim', [
                'empresa_id' => (int) $empresa->id,
                'mensagem_id' => $mensagemCriada instanceof PortalMensagem ? (int) $mensagemCriada->id : null,
                'quantidade_anexos' => count($anexosArmazenados),
                'duracao_transacao_ms' => round((microtime(true) - $inicioTransacao) * 1000, 2),
                'duracao_total_ate_salvar_ms' => round((microtime(true) - $inicio) * 1000, 2),
            ]);

            if ($mensagemCriada instanceof PortalMensagem) {
                $mensagemIdAtendimento = (int) $mensagemCriada->id;
                $empresaIdAtendimento = (int) $empresa->id;
                $tokenHashAtendimento = hash('sha256', $token);

                app()->terminating(function () use ($mensagemIdAtendimento, $empresaIdAtendimento, $tokenHashAtendimento): void {
                    try {
                        $inicioAtendimento = microtime(true);
                        Log::info('[PORTAL_CHAT_CLIENTE_ATENDIMENTO] inicio_terminating', [
                            'empresa_id' => $empresaIdAtendimento,
                            'mensagem_id' => $mensagemIdAtendimento,
                            'token_hash' => $tokenHashAtendimento,
                        ]);

                        $mensagemAtendimento = PortalMensagem::find($mensagemIdAtendimento);

                        if ($mensagemAtendimento instanceof PortalMensagem) {
                            app(AtendimentoPortalService::class)->registrarMensagem($mensagemAtendimento);
                        }

                        Log::info('[PORTAL_CHAT_CLIENTE_ATENDIMENTO] fim_terminating', [
                            'empresa_id' => $empresaIdAtendimento,
                            'mensagem_id' => $mensagemIdAtendimento,
                            'duracao_ms' => round((microtime(true) - $inicioAtendimento) * 1000, 2),
                        ]);
                    } catch (Throwable $atendimentoException) {
                        Log::warning('Mensagem do portal público salva, mas não foi possível gerar/atualizar atendimento operacional.', [
                            'empresa_id' => $empresaIdAtendimento,
                            'mensagem_id' => $mensagemIdAtendimento,
                            'token_hash' => $tokenHashAtendimento,
                            'message' => $atendimentoException->getMessage(),
                            'file' => $atendimentoException->getFile(),
                            'line' => $atendimentoException->getLine(),
                        ]);
                    }
                });
            }
        } catch (Throwable $exception) {
            foreach ($anexosArmazenados as $anexo) {
                $this->removerArquivoPublico((string) ($anexo['path'] ?? ''));
            }

            Log::error('Falha ao enviar mensagem pelo portal público do cliente.', [
                'empresa_id' => (int) $empresa->id,
                'token_hash' => hash('sha256', $token),
                'anexos' => array_map(fn (array $anexo): string => (string) ($anexo['path'] ?? ''), $anexosArmazenados),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);

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

        Cache::forget($this->cacheKeyClienteDigitando((int) $empresa->id));

        Log::info('[PORTAL_CHAT_CLIENTE_ENVIO] fim_resposta', [
            'empresa_id' => (int) $empresa->id,
            'token_hash' => hash('sha256', $token),
            'mensagem_id' => $mensagemCriada instanceof PortalMensagem ? (int) $mensagemCriada->id : null,
            'ajax' => $this->querRespostaJsonPortal($request),
            'duracao_total_ms' => round((microtime(true) - $inicio) * 1000, 2),
        ]);

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
                        'attachments' => [],
                    ])
                    : null,
            ]);
        }

        return $this->redirectPortal($token, 'chat')->with('success', $anexosArmazenados === [] ? 'Mensagem enviada para a equipe.' : 'Mensagem e anexo(s) enviados para a equipe.');
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

        Log::info('[PORTAL_CHAT_CLIENTE_ESTADO] fim', [
            'empresa_id' => $empresaId,
            'token_hash' => hash('sha256', $token),
            'total_mensagens' => count($mensagens),
            'ultimo_id' => collect($mensagens)->max('id'),
            'suporte_digitando' => $supportTyping,
            'duracao_ms' => round((microtime(true) - $inicio) * 1000, 2),
        ]);

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
            ]))
            ->values()
            ->all();
    }

    private function formatarMensagemTempoReal(array $mensagem): array
    {
        $classe = (string) ($mensagem['css_class'] ?? (($mensagem['origem'] ?? 'cliente') === 'interno' ? 'equipe' : 'cliente'));
        $texto = trim((string) ($mensagem['mensagem_texto'] ?? $mensagem['mensagem'] ?? ''));
        $nome = trim((string) ($mensagem['nome'] ?? $mensagem['autor_label'] ?? ($classe === 'equipe' ? 'Equipe' : 'Cliente')));

        return [
            'id' => (int) ($mensagem['id'] ?? 0),
            'source' => (string) ($mensagem['source'] ?? 'portal_mensagens'),
            'class' => $classe === 'equipe' ? 'equipe' : 'cliente',
            'author' => $nome !== '' ? $nome : ($classe === 'equipe' ? 'Equipe' : 'Cliente'),
            'text' => $texto,
            'time' => (string) ($mensagem['created_at_label'] ?? ''),
            'attachments' => collect($mensagem['attachments'] ?? [])->map(fn ($anexo): array => [
                'url' => (string) ($anexo['url'] ?? ''),
                'name' => (string) ($anexo['nome'] ?? 'Anexo'),
                'size' => (string) ($anexo['size_label'] ?? ($anexo['mime_type'] ?? 'arquivo')),
                'is_image' => (bool) ($anexo['is_image'] ?? false),
            ])->values()->all(),
        ];
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
            Log::error('Falha ao abrir solicitação pelo portal público do cliente.', [
                'empresa_id' => (int) $empresa->id,
                'token_hash' => hash('sha256', $token),
                'item_controle_id' => $data['item_controle_id'] ?? null,
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);

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
            Log::error('Falha ao responder pendência pelo portal público do cliente.', [
                'empresa_id' => (int) $empresa->id,
                'solicitacao_id' => $solicitacao->id,
                'token_hash' => hash('sha256', $token),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);

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
