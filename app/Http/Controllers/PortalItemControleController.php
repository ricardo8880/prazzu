<?php

namespace App\Http\Controllers;


use App\Support\CachedSchema;
use App\Models\ItemControle;
use App\Models\ItemControleAnexo;
use App\Models\ItemControleAssinatura;
use App\Models\ItemControleComentario;
use App\Models\ItemControleTimeline;
use App\Models\PrazzuClientPortalMessage;
use App\Models\PortalMensagem;
use App\Support\ItemControleAnexoUploader;
use App\Support\AtendimentoPortalService;
use App\Services\ItemControleStatusService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

class PortalItemControleController extends Controller
{
    public function __construct(private readonly ItemControleStatusService $statusService)
    {
    }

    public function show(string $token): View
    {
        $item = $this->getItemDisponivel($token);

        return view('portal.item-controle-show', [
            'item' => $item,
        ]);
    }

    public function assinar(Request $request, string $token): RedirectResponse
    {
        $item = $this->getItemDisponivel($token);

        $validator = Validator::make($request->all(), [
            'nome' => ['bail', 'required', 'string', 'min:2', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'documento' => ['nullable', 'string', 'max:100'],
            'aceite' => ['accepted'],
            'website' => ['nullable', 'prohibited'],
        ], [
            'nome.required' => 'Informe seu nome para assinar.',
            'nome.min' => 'Informe um nome com pelo menos 2 caracteres.',
            'email.email' => 'Informe um e-mail válido.',
            'aceite.accepted' => 'Você precisa confirmar o aceite para assinar.',
            'website.prohibited' => 'Requisição inválida. Atualize a página e tente novamente.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput();
        }

        $dados = $validator->validated();

        $aceiteTexto = 'Declaro que li e concordo com as informações apresentadas neste item/documento, registrando minha assinatura eletrônica interna.';

        $hash = hash('sha256', implode('|', [
            $item->id,
            $item->portal_token,
            $dados['nome'],
            $dados['email'] ?? '',
            $dados['documento'] ?? '',
            $request->ip(),
            $request->userAgent(),
            now()->toDateTimeString(),
            $aceiteTexto,
        ]));

        $payloadAssinatura = [
            'item_controle_id' => $item->id,
            'empresa_id' => $item->empresa_id,
            'user_id' => auth()->id(),
            'nome' => $dados['nome'],
            'email' => $dados['email'] ?? null,
            'documento' => $dados['documento'] ?? null,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'assinado_em' => now(),
        ];

        if (CachedSchema::hasColumn('item_controle_assinaturas', 'hash_assinatura')) {
            $payloadAssinatura['hash_assinatura'] = $hash;
        }

        if (CachedSchema::hasColumn('item_controle_assinaturas', 'aceite_texto')) {
            $payloadAssinatura['aceite_texto'] = $aceiteTexto;
        }

        try {
            DB::transaction(function () use ($item, $payloadAssinatura, $dados, $hash, $aceiteTexto, $request): void {
                ItemControleAssinatura::query()->create($payloadAssinatura);

                $this->statusService->registrarAssinaturaPortal($item, [
                    'nome' => $dados['nome'],
                    'email' => $dados['email'] ?? null,
                    'hash_assinatura' => $hash,
                    'concluido_em' => now()->toDateTimeString(),
                    'canal' => 'portal_cliente',
                ]);

                ItemControleTimeline::query()->create([
                    'item_controle_id' => $item->id,
                    'empresa_id' => $item->empresa_id,
                    'user_id' => null,
                    'tipo' => 'assinatura',
                    'titulo' => 'Assinatura registrada pelo portal do cliente',
                    'descricao' => 'Assinado por ' . $dados['nome'],
                    'dados' => [
                        'nome' => $dados['nome'],
                        'email' => $dados['email'] ?? null,
                        'documento' => $dados['documento'] ?? null,
                        'hash_assinatura' => $hash,
                        'aceite_texto' => $aceiteTexto,
                        'ip' => $request->ip(),
                    ],
                ]);
            });
        } catch (Throwable $exception) {
            Log::error('Falha ao registrar assinatura pelo portal do cliente.', [
                'item_controle_id' => $item->id,
                'empresa_id' => $item->empresa_id,
                'token_hash' => hash('sha256', $token),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);

            return back()
                ->withInput()
                ->withErrors(['assinatura' => 'Não foi possível registrar a assinatura agora. Tente novamente em instantes.']);
        }

        return redirect()
            ->route('portal.item-controles.show', ['token' => $token])
            ->with('success', 'Assinatura registrada com sucesso.');
    }


    public function mensagem(Request $request, string $token): RedirectResponse
    {
        $item = $this->getItemDisponivel($token);

        $validator = Validator::make($request->all(), [
            'client_name' => ['bail', 'required', 'string', 'min:2', 'max:255'],
            'client_email' => ['nullable', 'email', 'max:255'],
            'message' => ['bail', 'required', 'string', 'min:2', 'max:5000'],
            'website' => ['nullable', 'prohibited'],
        ], [
            'client_name.required' => 'Informe seu nome para enviar a mensagem.',
            'client_name.min' => 'Informe um nome com pelo menos 2 caracteres.',
            'client_email.email' => 'Informe um e-mail válido.',
            'message.required' => 'Escreva a mensagem antes de enviar.',
            'message.min' => 'A mensagem precisa ter pelo menos 2 caracteres.',
            'website.prohibited' => 'Requisição inválida. Atualize a página e tente novamente.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $dados = $validator->validated();

        try {
            DB::transaction(function () use ($item, $dados, $request): void {
                PrazzuClientPortalMessage::query()->create([
                    'empresa_id' => $item->empresa_id,
                    'item_controle_id' => $item->id,
                    'user_id' => null,
                    'client_name' => $dados['client_name'],
                    'client_email' => $dados['client_email'] ?? $item->portal_cliente_email,
                    'message' => $dados['message'],
                ]);

                $mensagemCentral = $this->registrarMensagemCentralPortal($item, $dados['client_name'], $dados['client_email'] ?? $item->portal_cliente_email, $dados['message']);

                if ($mensagemCentral) {
                    $atendimento = app(AtendimentoPortalService::class)->registrarMensagem($mensagemCentral);

                    if (! $atendimento) {
                        throw new \RuntimeException('Mensagem salva no portal do item, mas não foi possível gerar atendimento operacional.');
                    }
                }

                ItemControleComentario::query()->create([
                    'item_controle_id' => $item->id,
                    'user_id' => null,
                    'comentario' => '[Portal cliente] ' . $dados['client_name'] . ': ' . $dados['message'],
                ]);

                ItemControleTimeline::query()->create([
                    'item_controle_id' => $item->id,
                    'empresa_id' => $item->empresa_id,
                    'user_id' => null,
                    'tipo' => 'comentario',
                    'titulo' => 'Mensagem enviada pelo portal do cliente',
                    'descricao' => $dados['message'],
                    'dados' => [
                        'client_name' => $dados['client_name'],
                        'client_email' => $dados['client_email'] ?? null,
                        'ip' => $request->ip(),
                    ],
                ]);

                $this->statusService->registrarMensagemPortal($item, [
                    'client_name' => $dados['client_name'],
                    'client_email' => $dados['client_email'] ?? null,
                ]);
            });
        } catch (Throwable $exception) {
            Log::error('Falha ao registrar mensagem pelo portal do cliente.', [
                'item_controle_id' => $item->id,
                'empresa_id' => $item->empresa_id,
                'token_hash' => hash('sha256', $token),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);

            return back()
                ->withInput()
                ->withErrors(['message' => 'Não foi possível enviar a mensagem agora. Tente novamente em instantes.']);
        }

        return redirect()
            ->route('portal.item-controles.show', ['token' => $token])
            ->with('success', 'Mensagem enviada com sucesso.');
    }

    public function enviarDocumento(Request $request, string $token): RedirectResponse
    {
        $item = $this->getItemDisponivel($token);

        $validator = Validator::make($request->all(), [
            'client_name' => ['bail', 'required', 'string', 'min:2', 'max:255'],
            'client_email' => ['nullable', 'email', 'max:255'],
            'observacao' => ['nullable', 'string', 'max:2000'],
            'website' => ['nullable', 'prohibited'],
            'documento' => ['bail', 'required', 'file', 'max:' . ItemControleAnexoUploader::MAX_SIZE_KB, 'mimes:' . implode(',', ItemControleAnexoUploader::ALLOWED_EXTENSIONS), 'mimetypes:' . implode(',', ItemControleAnexoUploader::ALLOWED_MIME_TYPES)],
        ], [
            'client_name.required' => 'Informe seu nome para enviar o documento.',
            'client_name.min' => 'Informe um nome com pelo menos 2 caracteres.',
            'client_email.email' => 'Informe um e-mail válido.',
            'documento.required' => 'Selecione um arquivo para enviar.',
            'documento.max' => 'O arquivo pode ter no máximo 10 MB.',
            'documento.mimes' => 'Formato inválido. Envie PDF, Word, Excel, CSV, TXT ou imagem.',
            'documento.mimetypes' => 'Tipo de arquivo não permitido. Envie um arquivo válido e seguro.',
            'website.prohibited' => 'Requisição inválida. Atualize a página e tente novamente.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $dados = $validator->validated();
        $arquivo = $request->file('documento');
        $extensao = strtolower((string) $arquivo->getClientOriginalExtension());

        if (! in_array($extensao, ItemControleAnexoUploader::ALLOWED_EXTENSIONS, true)) {
            return back()
                ->withErrors(['documento' => 'Extensão não permitida. Envie PDF, Word, Excel, CSV, TXT ou imagem.'])
                ->withInput();
        }

        if (! $arquivo->isValid()) {
            return back()
                ->withErrors(['documento' => 'O arquivo enviado não pôde ser processado. Selecione o arquivo novamente.'])
                ->withInput();
        }

        try {
            $path = $arquivo->store('portal-cliente/documentos', 'public');

            if (! $path) {
                throw new \RuntimeException('Falha ao gravar arquivo no disco público.');
            }

            DB::transaction(function () use ($item, $dados, $arquivo, $path, $request): void {
                ItemControleAnexo::query()->create([
                    'item_controle_id' => $item->id,
                    'user_id' => null,
                    'arquivo' => $path,
                    'nome_original' => $arquivo->getClientOriginalName(),
                    'mime_type' => $arquivo->getClientMimeType(),
                    'tamanho_bytes' => $arquivo->getSize(),
                    'observacao' => trim((string) ($dados['observacao'] ?? '')) ?: 'Documento enviado pelo portal do cliente.',
                ]);

                $mensagemDocumento = 'Documento enviado pelo portal: ' . $arquivo->getClientOriginalName() . (filled($dados['observacao'] ?? null) ? "\n\n" . $dados['observacao'] : '');

                PrazzuClientPortalMessage::query()->create([
                    'empresa_id' => $item->empresa_id,
                    'item_controle_id' => $item->id,
                    'user_id' => null,
                    'client_name' => $dados['client_name'],
                    'client_email' => $dados['client_email'] ?? $item->portal_cliente_email,
                    'message' => $mensagemDocumento,
                    'attachment' => $path,
                ]);

                $mensagemCentral = $this->registrarMensagemCentralPortal($item, $dados['client_name'], $dados['client_email'] ?? $item->portal_cliente_email, $mensagemDocumento . "\n\nAnexo: " . $path);

                if ($mensagemCentral) {
                    $atendimento = app(AtendimentoPortalService::class)->registrarMensagem($mensagemCentral);

                    if (! $atendimento) {
                        throw new \RuntimeException('Documento salvo no portal do item, mas não foi possível gerar atendimento operacional.');
                    }
                }

                ItemControleTimeline::query()->create([
                    'item_controle_id' => $item->id,
                    'empresa_id' => $item->empresa_id,
                    'user_id' => null,
                    'tipo' => 'anexo',
                    'titulo' => 'Documento enviado pelo portal do cliente',
                    'descricao' => $arquivo->getClientOriginalName(),
                    'dados' => [
                        'path' => $path,
                        'client_name' => $dados['client_name'],
                        'client_email' => $dados['client_email'] ?? null,
                        'ip' => $request->ip(),
                    ],
                ]);

                $this->statusService->registrarDocumentoPortal($item, [
                    'arquivo' => $path,
                    'nome_original' => $arquivo->getClientOriginalName(),
                    'client_name' => $dados['client_name'],
                    'client_email' => $dados['client_email'] ?? null,
                ]);
            });
        } catch (Throwable $exception) {
            Log::error('Falha ao receber documento pelo portal do cliente.', [
                'item_controle_id' => $item->id,
                'empresa_id' => $item->empresa_id,
                'token_hash' => hash('sha256', $token),
                'nome_original' => $arquivo->getClientOriginalName(),
                'mime_type' => $arquivo->getClientMimeType(),
                'tamanho_bytes' => $arquivo->getSize(),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);

            return back()
                ->withInput()
                ->withErrors(['documento' => 'Não foi possível enviar o documento agora. Tente novamente em instantes.']);
        }

        return redirect()
            ->route('portal.item-controles.show', ['token' => $token])
            ->with('success', 'Documento enviado com sucesso.');
    }


    private function registrarMensagemCentralPortal(ItemControle $item, string $nome, ?string $email, string $mensagem): ?PortalMensagem
    {
        if (! CachedSchema::hasTable('portal_mensagens')) {
            return null;
        }

        $payload = [
            'empresa_id' => (int) $item->empresa_id,
            'item_controle_id' => (int) $item->id,
            'user_id' => null,
            'nome' => trim($nome),
            'email' => $email ? trim($email) : null,
            'mensagem' => trim($mensagem),
            'origem' => 'cliente',
        ];

        if (CachedSchema::hasColumn('portal_mensagens', 'conversa_status')) {
            $payload['conversa_status'] = 'aberta';
        }

        return PortalMensagem::query()->create($payload);
    }

    protected function getItemDisponivel(string $token): ItemControle
    {
        $item = ItemControle::query()
            ->where('portal_token', $token)
            ->with([
                'empresa:id,razao_social,nome_fantasia',
                'responsavel:id,nome,email,telefone,cargo',
                'categoria:id,nome,cor',
                'tags:id,nome,cor',
                'checklists',
                'anexos',
                'assinaturas',
                'ultimaAssinatura',
                'timelines.user:id,name',
                'etapasOperacionais.etapa',
                'comentarios.user:id,name',
                'comentariosKanban.user:id,name',
                'clientPortalMessages',
                'documentVersions',
                'dependencies',
                'dependencies.dependsOnItem:id,titulo,status',
            ])
            ->withCount([
                'assinaturas',
            ])
            ->firstOrFail();

        abort_unless($item->portalEstaDisponivel(), 403, 'Este link não está disponível ou expirou.');

        return $item;
    }
}
