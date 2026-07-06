<?php

$root = dirname(__DIR__);

$files = [
    'service' => $root . '/app/Services/OperationalWorkflowService.php',
    'centro' => $root . '/app/Services/CentroOperacionalService.php',
    'pendencias' => $root . '/app/Filament/Pages/Pendencias.php',
];

$contents = [];
foreach ($files as $key => $file) {
    $contents[$key] = is_file($file) ? (string) file_get_contents($file) : '';
}

$checks = [
    'workflow_service_exists' => is_file($files['service']),
    'official_stages_declared' => str_contains($contents['service'], 'Cliente -> Empresa -> ItemControle -> Mesa -> Pendências'),
    'centro_payload_has_stage' => str_contains($contents['centro'], 'workflow_stage_label'),
    'centro_payload_has_next_action' => str_contains($contents['centro'], 'workflow_next_action'),
    'pendencias_enriches_workflow' => str_contains($contents['pendencias'], 'OperationalWorkflowService::class'),
    'no_database_change_required' => is_file($root . '/database/sql/lote_05_fluxo_operacional.sql'),
];

$failed = array_keys(array_filter($checks, fn (bool $ok): bool => ! $ok));

$result = [
    'lote' => '05',
    'escopo' => 'Fluxo Operacional',
    'checks' => $checks,
    'status' => $failed === [] ? 'ok' : 'falhou',
    'falhas' => $failed,
    'observacao' => 'Check estático: valida que Mesa Operacional e Pendências recebem etapa, dono e próxima ação do fluxo oficial.',
];

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;

exit($failed === [] ? 0 : 1);
