<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AsaasWebhookController;
use App\Http\Controllers\Admin\GlobalSearchController;
use App\Http\Controllers\AuditoriaDetalhadaExportController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\Auth\WhiteLabelSsoController;
use App\Http\Controllers\PublicEmpresaCadastroController;
use App\Http\Controllers\PortalClienteAreaController;
use App\Http\Controllers\PortalClienteAuthController;
use App\Http\Controllers\PortalClientePasswordController;
use App\Http\Controllers\PortalClientePublicoController;
use App\Http\Controllers\PortalItemControleController;
use App\Http\Middleware\RedirectIfPortalClienteAuthenticated;
use App\Http\Middleware\ValidatePortalPublicAccess;
use App\Models\ItemControle;
use App\Services\ItemControlePdfService;
use App\Models\PortalMensagem;
use App\Support\CachedSchema;
use App\Support\PortalClienteData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;


Route::middleware(['guest:portal_cliente', RedirectIfPortalClienteAuthenticated::class])->group(function (): void {
    Route::get('/portal-cliente/login', [PortalClienteAuthController::class, 'loginForm'])
        ->name('portal.cliente.login');

    Route::post('/portal-cliente/login', [PortalClienteAuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('portal.cliente.login.store');
});

Route::middleware(['guest:portal_cliente'])->group(function (): void {
    Route::get('/portal-cliente/esqueci-senha', [PortalClientePasswordController::class, 'forgotForm'])
        ->name('portal.cliente.forgot');

    Route::post('/portal-cliente/esqueci-senha', [PortalClientePasswordController::class, 'sendResetLink'])
        ->middleware('throttle:3,5')
        ->name('portal.cliente.forgot.store');

    Route::get('/portal-cliente/resetar-senha/{token}', [PortalClientePasswordController::class, 'resetForm'])
        ->where('token', '[A-Za-z0-9]{40,120}')
        ->name('portal.cliente.password.reset');

    Route::post('/portal-cliente/resetar-senha/{token}', [PortalClientePasswordController::class, 'resetPassword'])
        ->where('token', '[A-Za-z0-9]{40,120}')
        ->middleware('throttle:5,5')
        ->name('portal.cliente.password.update');

    Route::get('/portal-cliente/convite/{token}', [PortalClientePasswordController::class, 'conviteForm'])
        ->where('token', '[A-Za-z0-9]{40,120}')
        ->name('portal.cliente.convite');

    Route::post('/portal-cliente/convite/{token}', [PortalClientePasswordController::class, 'aceitarConvite'])
        ->where('token', '[A-Za-z0-9]{40,120}')
        ->middleware('throttle:5,5')
        ->name('portal.cliente.convite.aceitar');
});

Route::middleware(['auth:portal_cliente'])->group(function (): void {
    Route::get('/portal-cliente', [PortalClienteAreaController::class, 'dashboard'])
        ->name('portal.cliente.dashboard');

    Route::get('/portal-cliente/atendimentos/novo', [PortalClienteAreaController::class, 'novo'])
        ->name('portal.cliente.atendimentos.create');

    Route::post('/portal-cliente/atendimentos', [PortalClienteAreaController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('portal.cliente.atendimentos.store');

    Route::get('/portal-cliente/atendimentos/{atendimento}', [PortalClienteAreaController::class, 'atendimento'])
        ->whereNumber('atendimento')
        ->name('portal.cliente.atendimentos.show');

    Route::post('/portal-cliente/atendimentos/{atendimento}/mensagem', [PortalClienteAreaController::class, 'mensagem'])
        ->whereNumber('atendimento')
        ->middleware('throttle:30,1')
        ->name('portal.cliente.atendimentos.mensagem');

    Route::get('/portal-cliente/atendimentos/{atendimento}/chat-estado', [PortalClienteAreaController::class, 'estadoChat'])
        ->whereNumber('atendimento')
        ->middleware('throttle:900,1')
        ->name('portal.cliente.atendimentos.chat.estado');

    Route::get('/portal-cliente/atendimentos/{atendimento}/interacoes/{interacao}/anexo', [PortalClienteAreaController::class, 'anexo'])
        ->whereNumber('atendimento')
        ->whereNumber('interacao')
        ->name('portal.cliente.atendimentos.anexo');

    Route::post('/portal-cliente/debug-log', [PortalClienteAreaController::class, 'debugLog'])
        ->middleware('throttle:120,1')
        ->name('portal.cliente.debug-log');

    Route::post('/portal-cliente/logout', [PortalClienteAuthController::class, 'logout'])
        ->name('portal.cliente.logout');
});


Route::middleware(['auth'])->group(function (): void {

    Route::post('/admin/portal-cliente/debug-log', function (Request $request) {
        $payload = $request->validate([
            'step' => ['nullable', 'string', 'max:160'],
            'page' => ['nullable', 'string', 'max:255'],
            'url' => ['nullable', 'string', 'max:1000'],
            'pathname' => ['nullable', 'string', 'max:500'],
            'timestamp' => ['nullable', 'string', 'max:120'],
            'empresa_id' => ['nullable', 'integer'],
            'socket_id' => ['nullable', 'string', 'max:120'],
            'socket_connected' => ['nullable', 'boolean'],
            'message_id' => ['nullable', 'integer'],
            'status' => ['nullable', 'integer'],
            'duration_ms' => ['nullable', 'numeric'],
            'tamanho_mensagem' => ['nullable', 'integer'],
            'quantidade_anexos' => ['nullable', 'integer'],
            'fase' => ['nullable', 'string', 'max:120'],
            'erro' => ['nullable', 'string', 'max:1000'],
            'ack' => ['nullable'],
        ]);

        

        return response()->json(['ok' => true]);
    })->middleware('throttle:180,1')->name('admin.portal-cliente.debug-log');


    Route::post('/admin/portal-cliente/mensagem-visualizada', function (Request $request) {
        $data = $request->validate([
            'empresa' => ['required', 'integer', 'min:1'],
            'message_id' => ['required', 'integer', 'min:1'],
        ]);

        $empresaId = (int) $data['empresa'];
        $messageId = (int) $data['message_id'];

        abort_if(! PortalClienteData::usuarioPodeAcessarEmpresa($empresaId), 403);
        abort_if(! CachedSchema::hasTable('portal_mensagens'), 500, 'Tabela portal_mensagens não encontrada.');

        if (CachedSchema::hasColumn('portal_mensagens', 'visualizada_em')) {
            PortalMensagem::query()
                ->where('empresa_id', $empresaId)
                ->whereIn('origem', ['cliente', 'portal_cliente', 'client'])
                ->where('id', '<=', $messageId)
                ->whereNull('visualizada_em')
                ->update(['visualizada_em' => now()]);
        }

        return response()->json(['ok' => true]);
    })->middleware('throttle:240,1')->name('admin.portal-cliente.chat.mensagem-visualizada');


    Route::get('/admin/portal-cliente/mensagens-novas', function (Request $request) {
        $empresaId = $request->integer('empresa');
        $afterId = max(0, $request->integer('after_id'));

        abort_if(! $empresaId || ! PortalClienteData::usuarioPodeAcessarEmpresa($empresaId), 403);
        abort_if(! CachedSchema::hasTable('portal_mensagens'), 500, 'Tabela portal_mensagens não encontrada.');

        $formatarBytes = static function (int $bytes): string {
            if ($bytes >= 1048576) {
                return number_format($bytes / 1048576, 1, ',', '.') . ' MB';
            }

            if ($bytes >= 1024) {
                return number_format($bytes / 1024, 1, ',', '.') . ' KB';
            }

            return $bytes . ' B';
        };

        $extrairAnexosMensagem = static function (string $texto) use ($formatarBytes): array {
            if ($texto === '' || ! str_contains($texto, 'Anexos enviados:')) {
                return [];
            }

            preg_match_all('/^-\s*(.+?)\s*\|\s*(https?:\/\/\S+)(?:\s*\|\s*([^|\r\n]+))?(?:\s*\|\s*([^\r\n]+))?/mi', $texto, $matches, PREG_SET_ORDER);

            return collect($matches)
                ->map(function (array $match) use ($formatarBytes): array {
                    $nome = trim((string) ($match[1] ?? 'Anexo')) ?: 'Anexo';
                    $url = trim((string) ($match[2] ?? ''));
                    $mime = trim((string) ($match[3] ?? ''));
                    $size = (int) trim((string) ($match[4] ?? '0'));

                    return [
                        'url' => $url,
                        'name' => $nome,
                        'size' => $size > 0 ? $formatarBytes($size) : ($mime !== '' ? $mime : 'arquivo'),
                        'mime_type' => $mime,
                        'is_image' => str_starts_with($mime, 'image/'),
                    ];
                })
                ->filter(fn (array $anexo): bool => $anexo['url'] !== '')
                ->values()
                ->all();
        };

        $removerBlocoAnexosMensagem = static function (string $texto): string {
            if ($texto === '' || ! str_contains($texto, 'Anexos enviados:')) {
                return $texto;
            }

            $limpo = preg_replace('/\n?Anexos enviados:\s*(?:\n-\s*.+?(?:\r?\n|$))+/si', '', $texto) ?? $texto;

            return trim($limpo) !== '' ? trim($limpo) : 'Arquivo(s) enviado(s).';
        };

        if (CachedSchema::hasColumn('portal_mensagens', 'visualizada_em')) {
            PortalMensagem::query()
                ->where('empresa_id', $empresaId)
                ->whereIn('origem', ['cliente', 'portal_cliente', 'client'])
                ->whereNull('visualizada_em')
                ->update(['visualizada_em' => now()]);
        }

        $mensagens = PortalMensagem::query()
            ->where('empresa_id', $empresaId)
            ->when(
                CachedSchema::hasColumn('portal_mensagens', 'conversa_status'),
                fn ($query) => $query->where('conversa_status', 'aberta')
            )
            ->when($afterId > 0, fn ($query) => $query->where('id', '>', $afterId))
            ->orderBy('id')
            ->limit(50)
            ->get()
            ->map(function (PortalMensagem $mensagem) use ($empresaId, $extrairAnexosMensagem, $removerBlocoAnexosMensagem): array {
                $origem = strtolower((string) $mensagem->origem);
                $classe = in_array($origem, ['cliente', 'portal_cliente', 'client'], true) ? 'cliente' : 'equipe';
                $textoOriginal = trim((string) $mensagem->mensagem);
                $textoLimpo = $removerBlocoAnexosMensagem($textoOriginal);
                $anexos = $extrairAnexosMensagem($textoOriginal);
                $room = 'empresa:' . $empresaId . ':portal';
                $actor = $classe === 'cliente' ? 'cliente' : 'suporte';

                return [
                    'id' => (int) $mensagem->id,
                    'message_id' => (int) $mensagem->id,
                    'empresa_id' => $empresaId,
                    'room' => $room,
                    'room_scope' => 'portal',
                    'class' => $classe,
                    'actor' => $actor,
                    'server_signature' => hash_hmac('sha256', $empresaId . '|' . $room . '|' . $actor . '|' . (int) $mensagem->id, (string) config('app.key')),
                    'origem' => $mensagem->origem,
                    'author' => $mensagem->nome ?: ($classe === 'cliente' ? 'Cliente' : 'Equipe'),
                    'nome' => $mensagem->nome ?: ($classe === 'cliente' ? 'Cliente' : 'Equipe'),
                    'text' => $textoLimpo,
                    'mensagem' => $textoLimpo,
                    'time' => optional($mensagem->created_at)->format('d/m/Y H:i') ?: 'agora',
                    'created_at_label' => optional($mensagem->created_at)->format('d/m/Y H:i') ?: 'agora',
                    'attachments' => $anexos,
                ];
            })
            ->values();


        return response()->json([
            'ok' => true,
            'messages' => $mensagens,
        ]);
    })->middleware('throttle:120,1')->name('admin.portal-cliente.chat.mensagens-novas');

    Route::get('/admin/portal-cliente/mensagem', function () {
        

        return redirect('/admin/portal-cliente');
    })->middleware('throttle:60,1')->name('admin.portal-cliente.chat.mensagem.get-redirect');

    Route::post('/admin/portal-cliente/mensagem', function (Request $request) {
        $inicio = microtime(true);
        $empresaId = $request->integer('empresa');

        

        abort_if(! $empresaId || ! PortalClienteData::usuarioPodeAcessarEmpresa($empresaId), 403);
        abort_if(! CachedSchema::hasTable('portal_mensagens'), 500, 'Tabela portal_mensagens não encontrada.');

        $dados = $request->validate([
            'mensagem' => ['nullable', 'string', 'max:5000', 'required_without:portalAnexos.0'],
            'portalAnexos' => ['array', 'max:5'],
            'portalAnexos.*' => ['file', 'max:10240', 'mimes:jpg,jpeg,png,webp,pdf,doc,docx,xls,xlsx,txt,csv'],
        ], [
            'mensagem.required_without' => 'Digite uma mensagem ou anexe pelo menos um arquivo.',
            'portalAnexos.max' => 'Envie no máximo 5 arquivos por mensagem.',
            'portalAnexos.*.max' => 'Cada arquivo deve ter no máximo 10 MB.',
            'portalAnexos.*.mimes' => 'Use apenas imagem, PDF, Word, Excel, TXT ou CSV.',
        ]);

        $mensagemTexto = trim((string) ($dados['mensagem'] ?? ''));
        $anexos = collect($request->file('portalAnexos', []))
            ->filter()
            ->map(function ($arquivo) use ($empresaId): array {
                $nomeOriginal = $arquivo->getClientOriginalName() ?: 'anexo';
                $nomeSeguro = substr((string) pathinfo($nomeOriginal, PATHINFO_FILENAME), 0, 80);
                $nomeSeguro = preg_replace('/[^A-Za-z0-9_-]+/', '-', $nomeSeguro) ?: 'anexo';
                $extensao = strtolower($arquivo->getClientOriginalExtension() ?: 'bin');
                $arquivoNome = trim($nomeSeguro, '-') . '-' . now()->format('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $extensao;
                $caminho = $arquivo->storeAs('portal-chat/' . $empresaId, $arquivoNome, 'public');

                return [
                    'nome' => $nomeOriginal,
                    'url' => asset(Storage::url($caminho)),
                    'mime_type' => $arquivo->getMimeType(),
                    'size' => $arquivo->getSize(),
                    'size_label' => $arquivo->getSize() ? number_format($arquivo->getSize() / 1024, 1, ',', '.') . ' KB' : 'arquivo',
                    'is_image' => str_starts_with((string) $arquivo->getMimeType(), 'image/'),
                ];
            })
            ->values();

        if ($anexos->isNotEmpty()) {
            $linhasAnexos = $anexos
                ->map(fn (array $anexo): string => '- ' . ($anexo['nome'] ?? 'Anexo') . ' | ' . ($anexo['url'] ?? '') . ' | ' . ($anexo['mime_type'] ?? 'application/octet-stream') . ' | ' . ($anexo['size'] ?? ''))
                ->implode("\n");

            $mensagemTexto = trim($mensagemTexto . "\n\nAnexos enviados:\n" . $linhasAnexos);
        }

        abort_if($mensagemTexto === '', 422, 'Digite uma mensagem ou anexe pelo menos um arquivo.');

        $payload = [
            'empresa_id' => $empresaId,
            'user_id' => auth()->id(),
            'nome' => auth()->user()?->name,
            'email' => auth()->user()?->email,
            'mensagem' => $mensagemTexto,
            'origem' => 'interno',
        ];

        if (CachedSchema::hasColumn('portal_mensagens', 'conversa_status')) {
            $payload['conversa_status'] = 'aberta';
        }

        $mensagem = PortalMensagem::create($payload);

        $room = 'empresa:' . $empresaId . ':portal';
        $actor = 'suporte';

        $chatMessage = [
            'id' => (int) $mensagem->id,
            'message_id' => (int) $mensagem->id,
            'empresa_id' => $empresaId,
            'room' => $room,
            'room_scope' => 'portal',
            'class' => 'equipe',
            'actor' => $actor,
            'server_signature' => hash_hmac('sha256', $empresaId . '|' . $room . '|' . $actor . '|' . (int) $mensagem->id, (string) config('app.key')),
            'origem' => 'interno',
            'author' => auth()->user()?->name ?: 'Equipe',
            'nome' => auth()->user()?->name ?: 'Equipe',
            'text' => trim((string) $request->input('mensagem', '')),
            'mensagem' => trim((string) $request->input('mensagem', '')),
            'time' => optional($mensagem->created_at)->format('d/m/Y H:i') ?: 'agora',
            'created_at_label' => optional($mensagem->created_at)->format('d/m/Y H:i') ?: 'agora',
            'attachments' => $anexos->all(),
        ];

        

        $responsePayload = [
            'ok' => true,
            'message_id' => (int) $mensagem->id,
            'chat_message' => $chatMessage,
        ];

        if (! $request->expectsJson() && ! $request->ajax()) {
            

            return redirect('/admin/portal-cliente')
                ->with('success', 'Mensagem enviada.');
        }

        return response()->json($responsePayload);
    })->middleware('throttle:120,1')->name('admin.portal-cliente.chat.mensagem');
});

Route::get('/auth/white-label/sso', [WhiteLabelSsoController::class, 'redirect'])
    ->middleware('guest')
    ->name('white-label.sso.redirect');

Route::get('/', function () {
    return view('landing-contabilidade');
});

Route::get('/planos', function () {
    return view('planos');
})->name('planos');

Route::get('/item-controles/{itemControle}/pdf', function (
    ItemControle $itemControle,
    ItemControlePdfService $service
) {
    return $service->gerar($itemControle);
})->middleware('auth')->name('item-controles.pdf');

Route::get('/cadastro-empresa', [PublicEmpresaCadastroController::class, 'create'])
    ->name('empresa.cadastro.create');

Route::post('/cadastro-empresa', [PublicEmpresaCadastroController::class, 'store'])
    ->name('empresa.cadastro.store');

Route::middleware([ValidatePortalPublicAccess::class])->group(function (): void {
    Route::get('/portal/cliente/{token}', [PortalClientePublicoController::class, 'show'])
        ->where('token', '[A-Za-z0-9]{32,128}')
        ->name('portal.cliente.show');

    Route::get('/portal/cliente/{token}/mensagens-novas', [PortalClientePublicoController::class, 'mensagensNovas'])
        ->where('token', '[A-Za-z0-9]{32,128}')
        ->middleware('throttle:120,1')
        ->name('portal.cliente.mensagens-novas');

    Route::post('/portal/cliente/{token}/mensagem', [PortalClientePublicoController::class, 'mensagem'])
        ->where('token', '[A-Za-z0-9]{32,128}')
        ->middleware('throttle:60,1')
        ->name('portal.cliente.mensagem');

    Route::post('/portal/cliente/{token}/mensagem-visualizada', [PortalClientePublicoController::class, 'mensagemVisualizada'])
        ->where('token', '[A-Za-z0-9]{32,128}')
        ->middleware('throttle:240,1')
        ->name('portal.cliente.mensagem-visualizada');

    Route::post('/portal/cliente/{token}/debug-log', [PortalClientePublicoController::class, 'debugLog'])
        ->where('token', '[A-Za-z0-9]{32,128}')
        ->middleware('throttle:120,1')
        ->name('portal.cliente.debug-log.publico');

    Route::post('/portal/cliente/{token}/solicitacao', [PortalClientePublicoController::class, 'solicitacao'])
        ->where('token', '[A-Za-z0-9]{32,128}')
        ->middleware('throttle:30,1')
        ->name('portal.cliente.solicitacao');

    Route::post('/portal/cliente/{token}/solicitacoes', [PortalClientePublicoController::class, 'solicitacao'])
        ->where('token', '[A-Za-z0-9]{32,128}')
        ->middleware('throttle:30,1')
        ->name('portal.cliente.solicitacoes.store');

    Route::post('/portal/cliente/{token}/pendencia/{solicitacao}/responder', [PortalClientePublicoController::class, 'responderPendencia'])
        ->where('token', '[A-Za-z0-9]{32,128}')
        ->whereNumber('solicitacao')
        ->middleware('throttle:60,1')
        ->name('portal.cliente.pendencia.responder');

    Route::get('/portal/itens/{token}', [PortalItemControleController::class, 'show'])
        ->where('token', '[A-Za-z0-9]{32,128}')
        ->name('portal.item-controles.show');

    Route::post('/portal/itens/{token}/assinar', [PortalItemControleController::class, 'assinar'])
        ->where('token', '[A-Za-z0-9]{32,128}')
        ->name('portal.item-controles.assinar');

    Route::post('/portal/itens/{token}/mensagem', [PortalItemControleController::class, 'mensagem'])
        ->where('token', '[A-Za-z0-9]{32,128}')
        ->name('portal.item-controles.mensagem');

    Route::post('/portal/itens/{token}/documentos', [PortalItemControleController::class, 'enviarDocumento'])
        ->where('token', '[A-Za-z0-9]{32,128}')
        ->name('portal.item-controles.documentos');
});


Route::get('/admin/busca-global', GlobalSearchController::class)
    ->middleware('auth')
    ->name('admin.global-search');

Route::post('/admin/auditoria/debug-log', function (\Illuminate\Http\Request $request) {
    $payload = $request->validate([
        'step' => ['nullable', 'string', 'max:120'],
        'debug_session_id' => ['nullable', 'string', 'max:120'],
        'location' => ['nullable', 'string', 'max:1000'],
        'ready_state' => ['nullable', 'string', 'max:80'],
        'timestamp' => ['nullable', 'string', 'max:120'],
        'opened_modals' => ['nullable'],
        'trigger_count' => ['nullable'],
        'modal_count' => ['nullable'],
        'modal_id' => ['nullable', 'string', 'max:180'],
        'modal_exists' => ['nullable'],
        'modal_already_open' => ['nullable'],
        'modal_class' => ['nullable', 'string', 'max:300'],
        'aria_hidden' => ['nullable', 'string', 'max:40'],
        'opened_modals_after_close' => ['nullable'],
        'source' => ['nullable', 'string', 'max:120'],
        'event_type' => ['nullable', 'string', 'max:80'],
        'event_phase' => ['nullable'],
        'document_ready_state' => ['nullable', 'string', 'max:80'],
        'livewire_present' => ['nullable'],
        'alpine_present' => ['nullable'],
        'trigger' => ['nullable', 'array'],
        'active_element' => ['nullable', 'array'],
    ]);

    

    return response()->json(['ok' => true]);
})->middleware(['auth'])->name('auditoria.debug-log');


Route::get('/admin/auditoria-detalhada/exportar', AuditoriaDetalhadaExportController::class)
    ->middleware('auth')
    ->name('auditoria-detalhada.exportar');

Route::get('/billing/sucesso', [BillingController::class, 'sucesso'])
    ->name('billing.sucesso');

Route::get('/billing/bloqueado', [BillingController::class, 'bloqueado'])
    ->middleware('auth')
    ->name('billing.bloqueado');

Route::get('/billing/empresas/{empresa}/pagar', [BillingController::class, 'pagar'])
    ->middleware('auth')
    ->name('billing.pagar');

Route::post('/billing/assinaturas/{assinatura}/cancelar', [BillingController::class, 'cancelar'])
    ->middleware('auth')
    ->name('billing.cancelar');

Route::post('/webhooks/asaas', AsaasWebhookController::class)
    ->name('webhooks.asaas');

Route::post('/asaas/webhook', AsaasWebhookController::class)
    ->name('asaas.webhook');
