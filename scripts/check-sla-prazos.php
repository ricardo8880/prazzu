<?php

$root = dirname(__DIR__);
$requiredFiles = [
    'app/Services/PrazzuSlaEngine.php',
    'app/Services/PrazzuSlaService.php',
    'app/Console/Commands/RecalcularSlaPrazzu.php',
    'app/Console/Commands/ProcessarRoadmapPrazzuInterno.php',
    'routes/console.php',
];

$result = [
    'lote' => '06-sla-prazos',
    'ok' => true,
    'checks' => [],
];

foreach ($requiredFiles as $file) {
    $path = $root . DIRECTORY_SEPARATOR . $file;
    $exists = is_file($path);
    $result['checks'][] = ['check' => "arquivo:{$file}", 'ok' => $exists];
    $result['ok'] = $result['ok'] && $exists;
}

$engine = file_get_contents($root . '/app/Services/PrazzuSlaEngine.php') ?: '';
foreach (['sem_sla', 'em_andamento', 'risco', 'vencido', 'concluido_no_prazo', 'concluido_atrasado'] as $status) {
    $ok = str_contains($engine, $status);
    $result['checks'][] = ['check' => "status_oficial:{$status}", 'ok' => $ok];
    $result['ok'] = $result['ok'] && $ok;
}

$service = file_get_contents($root . '/app/Services/PrazzuSlaService.php') ?: '';
foreach (['recalcularItensControle', 'CachedSchema::hasTable', 'sla_status'] as $needle) {
    $ok = str_contains($service, $needle);
    $result['checks'][] = ['check' => "service_contem:{$needle}", 'ok' => $ok];
    $result['ok'] = $result['ok'] && $ok;
}

$command = file_get_contents($root . '/app/Console/Commands/RecalcularSlaPrazzu.php') ?: '';
$ok = str_contains($command, 'prazzu:sla-recalcular');
$result['checks'][] = ['check' => 'comando:prazzu:sla-recalcular', 'ok' => $ok];
$result['ok'] = $result['ok'] && $ok;

$schedule = file_get_contents($root . '/routes/console.php') ?: '';
$ok = str_contains($schedule, "prazzu:sla-recalcular --limit=5000") && str_contains($schedule, '->hourly()');
$result['checks'][] = ['check' => 'scheduler_sla_horario', 'ok' => $ok];
$result['ok'] = $result['ok'] && $ok;

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
exit($result['ok'] ? 0 : 1);
