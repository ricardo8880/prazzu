<?php

$root = dirname(__DIR__);

$files = [
    'workflow' => $root . '/app/Services/OperationalWorkflowService.php',
    'centro_service' => $root . '/app/Services/CentroOperacionalService.php',
    'pendencias_page' => $root . '/app/Filament/Pages/Pendencias.php',
    'centro_view' => $root . '/resources/views/filament/pages/centro-operacional.blade.php',
    'pendencias_view' => $root . '/resources/views/filament/pages/compliance-pendencias.blade.php',
    'sql_lote_04' => $root . '/database/sql/lote_04_fluxo_operacional.sql',
    'sql_compat_lote_05' => $root . '/database/sql/lote_05_fluxo_operacional.sql',
];

$contents = [];
foreach ($files as $key => $file) {
    $contents[$key] = is_file($file) ? (string) file_get_contents($file) : '';
}

$checks = [
    'workflow_service_exists' => is_file($files['workflow']),
    'official_flow_declared' => str_contains($contents['workflow'], 'Cliente -> Empresa -> ItemControle -> Mesa -> Pendências'),
    'workflow_has_stage_for_item' => str_contains($contents['workflow'], 'function stageForItem'),
    'workflow_has_payload_enrichment' => str_contains($contents['workflow'], 'function enrichPayload'),
    'centro_uses_workflow_service' => str_contains($contents['centro_service'], 'OperationalWorkflowService::class'),
    'centro_payload_has_stage_label' => str_contains($contents['centro_service'], 'workflow_stage_label'),
    'centro_payload_has_next_action' => str_contains($contents['centro_service'], 'workflow_next_action'),
    'pendencias_uses_workflow_service' => str_contains($contents['pendencias_page'], 'OperationalWorkflowService::class'),
    'pendencias_enriches_payload' => str_contains($contents['pendencias_page'], 'enrichPayload($item)'),
    'centro_view_displays_workflow' => str_contains($contents['centro_view'], 'workflow_stage_label') && str_contains($contents['centro_view'], 'workflow_next_action'),
    'pendencias_view_displays_workflow' => str_contains($contents['pendencias_view'], 'workflow_stage_label') && str_contains($contents['pendencias_view'], 'workflow_next_action'),
    'no_schema_change_lote_04_marker_exists' => is_file($files['sql_lote_04']),
    'legacy_operational_check_marker_exists' => is_file($files['sql_compat_lote_05']),
];

$failed = array_keys(array_filter($checks, fn (bool $ok): bool => ! $ok));

$result = [
    'lote' => '04',
    'escopo' => 'Fluxo operacional final',
    'checks' => $checks,
    'status' => $failed === [] ? 'ok' : 'falhou',
    'falhas' => $failed,
    'observacao' => 'Check estático: confirma que Mesa Operacional e Pendências usam a mesma leitura oficial do fluxo e que não há alteração de schema.',
];

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;

exit($failed === [] ? 0 : 1);
