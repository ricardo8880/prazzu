<?php

$root = dirname(__DIR__);

$files = [
    'routes' => file_get_contents($root . '/routes/web.php'),
    'public_controller' => file_get_contents($root . '/app/Http/Controllers/PortalClientePublicoController.php'),
    'item_controller' => file_get_contents($root . '/app/Http/Controllers/PortalItemControleController.php'),
    'password_controller' => file_get_contents($root . '/app/Http/Controllers/PortalClientePasswordController.php'),
    'auth_controller' => file_get_contents($root . '/app/Http/Controllers/PortalClienteAuthController.php'),
    'security' => file_get_contents($root . '/app/Support/PortalClienteSecurity.php'),
    'portal_data' => file_get_contents($root . '/app/Support/PortalClienteData.php'),
];

$checks = [
    'token_pattern_public_routes' => substr_count($files['routes'], "->where('token', '[A-Za-z0-9]{32,128}')") >= 12,
    'public_routes_under_security_middleware' => str_contains($files['routes'], 'Route::middleware([ValidatePortalPublicAccess::class])->group'),
    'portal_empresa_isolamento_por_sessao' => str_contains($files['public_controller'], 'validarSessaoPortalCliente((int) $empresa->id)'),
    'portal_item_isolamento_por_sessao' => str_contains($files['item_controller'], 'validarSessaoPortalCliente((int) $item->empresa_id)'),
    'download_documento_autorizado_por_token' => str_contains($files['public_controller'], 'PortalClienteSecurity::documentoAutorizadoParaToken'),
    'download_externo_somente_http_https' => str_contains($files['public_controller'], 'urlExternaPermitida')
        && str_contains($files['public_controller'], "['http', 'https']"),
    'portal_data_sem_asset_direto_documento' => ! str_contains($files['portal_data'], 'asset(\'storage/\' . $documento->arquivo'),
    'upload_publico_validado_mime_extensao_tamanho' => str_contains($files['public_controller'], 'ItemControleAnexoUploader::MAX_SIZE_KB')
        && str_contains($files['public_controller'], 'ItemControleAnexoUploader::ALLOWED_EXTENSIONS')
        && str_contains($files['public_controller'], 'ItemControleAnexoUploader::ALLOWED_MIME_TYPES'),
    'portal_item_upload_validado' => str_contains($files['item_controller'], 'ItemControleAnexoUploader::MAX_SIZE_KB')
        && str_contains($files['item_controller'], 'ItemControleAnexoUploader::ALLOWED_EXTENSIONS')
        && str_contains($files['item_controller'], 'ItemControleAnexoUploader::ALLOWED_MIME_TYPES'),
    'mensagem_cliente_gera_atendimento_alerta' => str_contains($files['public_controller'], 'AtendimentoPortalService')
        && str_contains($files['public_controller'], 'registrarMensagem'),
    'pendencia_resposta_atualiza_status_e_data' => str_contains($files['public_controller'], '$update[\'status\'] = \'aguardando_equipe\'')
        && str_contains($files['public_controller'], "portal_solicitacoes', 'respondido_em'")
        && str_contains($files['public_controller'], "portal_solicitacoes', 'respondido_por'"),
    'reset_hash_token_expiracao_uso_unico' => str_contains($files['password_controller'], 'Hash::make($token)')
        && str_contains($files['password_controller'], "whereNull('used_at')")
        && str_contains($files['password_controller'], "where('expires_at', '>', now())"),
    'convite_hash_token_expiracao_uso_unico' => str_contains($files['password_controller'], 'localizarTokenValido($email, $token, \'convite\')')
        && str_contains($files['password_controller'], 'aceitarConvite')
        && (substr_count($files['password_controller'], "forceFill(['used_at' => now()])") >= 2),
    'login_throttle_e_cliente_ativo' => str_contains($files['auth_controller'], 'ensureIsNotRateLimited')
        && str_contains($files['auth_controller'], 'estaAtivo()'),
    'portal_item_posts_com_throttle' => str_contains($files['routes'], "portal.item-controles.assinar")
        && substr_count($files['routes'], "->middleware('throttle:") >= 12,
];

$failed = array_keys(array_filter($checks, fn (bool $ok): bool => ! $ok));

$result = [
    'lote' => 3,
    'escopo' => 'Portal do Cliente',
    'ok' => $failed === [],
    'checks' => $checks,
    'failed' => $failed,
    'observacao' => 'Validação estática complementar. O ambiente sem ext-mbstring impede route:list/artisan, então foram executados php -l e este checklist estrutural.',
];

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;

exit($failed === [] ? 0 : 1);
