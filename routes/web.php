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
use Illuminate\Support\Facades\Cache;



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
    Route::get('/admin/portal-cliente/chat-estado', function (Request $request) {
        $empresaId = $request->integer('empresa');

        abort_if(! $empresaId || ! PortalClienteData::usuarioPodeAcessarEmpresa($empresaId), 403);

        if (CachedSchema::hasTable('portal_mensagens')) {
            $ultimoIdCliente = PortalMensagem::query()
                ->where('empresa_id', $empresaId)
                ->where(function ($query): void {
                    $query->where('origem', 'cliente')
                        ->orWhere('origem', 'portal_cliente')
                        ->orWhere('origem', 'client');
                })
                ->max('id');

            if ($ultimoIdCliente) {
                Cache::put('portal_suporte_visualizou_cliente_empresa_' . $empresaId, (int) $ultimoIdCliente, now()->addHours(8));
            }
        }

        $mensagens = PortalMensagem::query()
            ->where('empresa_id', $empresaId)
            ->when(
                CachedSchema::hasColumn('portal_mensagens', 'conversa_status'),
                fn ($query) => $query->where('conversa_status', 'aberta')
            )
            ->oldest()
            ->limit(80)
            ->get()
            ->map(function (PortalMensagem $mensagem): array {
                $origem = strtolower((string) $mensagem->origem);
                $isCliente = in_array($origem, ['cliente', 'portal_cliente', 'client'], true);
                $createdAt = $mensagem->created_at;

                return [
                    'id' => (int) $mensagem->id,
                    'class' => $isCliente ? 'cliente' : 'equipe',
                    'author' => trim((string) $mensagem->nome) ?: ($isCliente ? 'Cliente' : 'Equipe'),
                    'text' => trim((string) $mensagem->mensagem),
                    'time' => $createdAt ? $createdAt->timezone(config('app.timezone'))->format('d/m/Y H:i') : '',
                    'attachments' => [],
                ];
            })
            ->filter(fn (array $mensagem): bool => $mensagem['text'] !== '')
            ->values()
            ->all();

        $typing = Cache::get('portal_cliente_digitando_empresa_' . $empresaId);
        $typingAtivo = is_array($typing) && (int) ($typing['timestamp'] ?? 0) >= now()->subSeconds(8)->timestamp;

        if (! $typingAtivo) {
            Cache::forget('portal_cliente_digitando_empresa_' . $empresaId);
        }

        return response()->json([
            'ok' => true,
            'messages' => $mensagens,
            'client_typing' => $typingAtivo,
            'client_typing_name' => $typingAtivo ? trim((string) ($typing['nome'] ?? 'Cliente')) : null,
            'support_seen_until_id' => Cache::get('portal_suporte_visualizou_cliente_empresa_' . $empresaId),
            'client_seen_until_id' => Cache::get('portal_cliente_visualizou_suporte_empresa_' . $empresaId),
        ]);
    })->middleware('throttle:900,1')->name('admin.portal-cliente.chat.estado');

    Route::post('/admin/portal-cliente/mensagem', function (Request $request) {
        $empresaId = $request->integer('empresa');

        abort_if(! $empresaId || ! PortalClienteData::usuarioPodeAcessarEmpresa($empresaId), 403);
        abort_if(! CachedSchema::hasTable('portal_mensagens'), 500, 'Tabela portal_mensagens não encontrada.');

        $dados = $request->validate([
            'mensagem' => ['required', 'string', 'max:5000'],
        ]);

        $mensagemTexto = trim((string) $dados['mensagem']);

        abort_if($mensagemTexto === '', 422, 'Digite uma mensagem antes de enviar.');

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
        Cache::forget('portal_suporte_digitando_empresa_' . $empresaId);

        $mensagens = PortalMensagem::query()
            ->where('empresa_id', $empresaId)
            ->when(
                CachedSchema::hasColumn('portal_mensagens', 'conversa_status'),
                fn ($query) => $query->where('conversa_status', 'aberta')
            )
            ->oldest()
            ->limit(80)
            ->get()
            ->map(function (PortalMensagem $mensagem): array {
                $origem = strtolower((string) $mensagem->origem);
                $isCliente = in_array($origem, ['cliente', 'portal_cliente', 'client'], true);
                $createdAt = $mensagem->created_at;

                return [
                    'id' => (int) $mensagem->id,
                    'class' => $isCliente ? 'cliente' : 'equipe',
                    'author' => trim((string) $mensagem->nome) ?: ($isCliente ? 'Cliente' : 'Equipe'),
                    'text' => trim((string) $mensagem->mensagem),
                    'time' => $createdAt ? $createdAt->timezone(config('app.timezone'))->format('d/m/Y H:i') : '',
                    'attachments' => [],
                ];
            })
            ->filter(fn (array $mensagem): bool => $mensagem['text'] !== '')
            ->values()
            ->all();

        return response()->json([
            'ok' => true,
            'message_id' => (int) $mensagem->id,
            'messages' => $mensagens,
            'support_seen_until_id' => Cache::get('portal_suporte_visualizou_cliente_empresa_' . $empresaId),
            'client_seen_until_id' => Cache::get('portal_cliente_visualizou_suporte_empresa_' . $empresaId),
        ]);
    })->middleware('throttle:120,1')->name('admin.portal-cliente.chat.mensagem');

    Route::post('/admin/portal-cliente/digitando', function (Request $request) {
        $empresaId = $request->integer('empresa');

        abort_if(! $empresaId || ! PortalClienteData::usuarioPodeAcessarEmpresa($empresaId), 403);

        $text = trim((string) $request->input('text', ''));

        if ($text === '') {
            Cache::forget('portal_suporte_digitando_empresa_' . $empresaId);
        } else {
            Cache::put('portal_suporte_digitando_empresa_' . $empresaId, [
                'nome' => auth()->user()?->name ?: 'Suporte',
                'timestamp' => now()->timestamp,
            ], now()->addSeconds(10));
        }

        return response()->json(['ok' => true]);
    })->middleware('throttle:600,1')->name('admin.portal-cliente.digitando');
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

    Route::post('/portal/cliente/{token}/mensagem', [PortalClientePublicoController::class, 'mensagem'])
        ->where('token', '[A-Za-z0-9]{32,128}')
        ->middleware('throttle:60,1')
        ->name('portal.cliente.mensagem');

    Route::get('/portal/cliente/{token}/chat-estado', [PortalClientePublicoController::class, 'estadoChat'])
        ->where('token', '[A-Za-z0-9]{32,128}')
        ->middleware('throttle:900,1')
        ->name('portal.cliente.chat.estado');

    Route::post('/portal/cliente/{token}/debug-log', [PortalClientePublicoController::class, 'debugLog'])
        ->where('token', '[A-Za-z0-9]{32,128}')
        ->middleware('throttle:120,1')
        ->name('portal.cliente.debug-log.publico');

    Route::post('/portal/cliente/{token}/digitando', [PortalClientePublicoController::class, 'digitando'])
        ->where('token', '[A-Za-z0-9]{32,128}')
        ->middleware('throttle:600,1')
        ->name('portal.cliente.digitando');

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

    \Illuminate\Support\Facades\Log::warning('[AUDITORIA_DEBUG] browser:' . ($payload['step'] ?? 'unknown'), array_merge([
        'auth_user_id' => auth()->id(),
        'ip' => $request->ip(),
        'user_agent' => $request->userAgent(),
    ], $payload));

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
