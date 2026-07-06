<?php

$root = dirname(__DIR__);

$checks = [
    'service_core_exists' => file_exists($root . '/app/Services/ItemControleCoreService.php'),
    'model_uses_core_service' => str_contains((string) file_get_contents($root . '/app/Models/ItemControle.php'), 'ItemControleCoreService'),
    'observer_normalizes_before_save' => str_contains((string) file_get_contents($root . '/app/Observers/ItemControleObserver.php'), 'normalizeBeforeSave'),
    'model_has_operational_payload' => str_contains((string) file_get_contents($root . '/app/Models/ItemControle.php'), 'getOperationalPayload'),
    'model_has_transition_helper' => str_contains((string) file_get_contents($root . '/app/Models/ItemControle.php'), 'alterarStatusOperacional'),
];

$failed = array_keys(array_filter($checks, fn (bool $ok): bool => ! $ok));

$result = [
    'lote' => '04',
    'escopo' => 'Arquitetura do núcleo / ItemControle',
    'checks' => $checks,
    'status' => $failed === [] ? 'ok' : 'falhou',
    'falhas' => $failed,
    'observacao' => 'Check estático: não depende de conexão com banco nem de artisan.',
];

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;

exit($failed === [] ? 0 : 1);
