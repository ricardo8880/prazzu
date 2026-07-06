<?php

$root = dirname(__DIR__);

$checks = [
    'support_security_exists' => file_exists($root . '/app/Support/PortalClienteSecurity.php'),
    'controller_download_method' => str_contains(file_get_contents($root . '/app/Http/Controllers/PortalClientePublicoController.php'), 'function downloadDocumento('),
    'controller_uses_security_service' => str_contains(file_get_contents($root . '/app/Http/Controllers/PortalClientePublicoController.php'), 'PortalClienteSecurity::empresaPorToken'),
    'route_secure_download' => str_contains(file_get_contents($root . '/routes/web.php'), 'portal.cliente.documentos.download'),
    'portal_data_secure_url' => str_contains(file_get_contents($root . '/app/Support/PortalClienteData.php'), 'PortalClienteSecurity::downloadDocumentoUrl'),
    'no_direct_document_asset_in_portal_data' => ! str_contains(file_get_contents($root . '/app/Support/PortalClienteData.php'), 'asset(\'storage/\' . $documento->arquivo'),
];

$failed = array_keys(array_filter($checks, fn (bool $ok): bool => ! $ok));

$result = [
    'lote' => 8,
    'escopo' => 'Portal do Cliente',
    'ok' => $failed === [],
    'checks' => $checks,
    'failed' => $failed,
    'observacao' => 'Validação estática. Rode também php artisan route:list e teste download no ambiente com ext-mbstring ativa.',
];

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;

exit($failed === [] ? 0 : 1);
