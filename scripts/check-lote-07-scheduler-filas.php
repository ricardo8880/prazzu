<?php

$base = dirname(__DIR__);
$erros = [];

function assert_lote_07(bool $condicao, string $mensagem, array &$erros): void
{
    if (! $condicao) {
        $erros[] = $mensagem;
    }
}

$arquivos = [
    'routes/console.php',
    'app/Notifications/ItemControleVencimentoNotification.php',
    'app/Console/Commands/MonitorarFilasPrazzu.php',
    'app/Console/Commands/ReprocessarFilasPrazzu.php',
    'database/sql/lote_07_filas_scheduler_notificacoes.sql',
    'docs/lotes/lote_07_scheduler_filas_notificacoes.md',
];

foreach ($arquivos as $arquivo) {
    assert_lote_07(is_file($base . DIRECTORY_SEPARATOR . $arquivo), "Arquivo obrigatório ausente: {$arquivo}", $erros);
}

$notification = @file_get_contents($base . '/app/Notifications/ItemControleVencimentoNotification.php') ?: '';
assert_lote_07(str_contains($notification, 'ShouldQueue'), 'ItemControleVencimentoNotification não implementa ShouldQueue.', $erros);
assert_lote_07(str_contains($notification, "public string $" . "queue = 'notificacoes'"), 'Fila notificacoes não foi definida na Notification.', $erros);
assert_lote_07(str_contains($notification, 'public int $tries = 3'), 'Tentativas da Notification não foram definidas.', $erros);

$routes = @file_get_contents($base . '/routes/console.php') ?: '';
foreach ([
    'prazzu:filas-monitorar',
    'queue:prune-failed --hours=168',
    'prazzu:filas-reprocessar --limit=25',
    'item-controle:notificar-vencimentos',
    'asaas:reconciliar-assinaturas',
    'prazzu:sla-recalcular',
] as $needle) {
    assert_lote_07(str_contains($routes, $needle), "Agendamento/command ausente em routes/console.php: {$needle}", $erros);
}

$monitor = @file_get_contents($base . '/app/Console/Commands/MonitorarFilasPrazzu.php') ?: '';
assert_lote_07(str_contains($monitor, "protected $" . "signature = 'prazzu:filas-monitorar"), 'Signature do monitor de filas ausente.', $erros);
assert_lote_07(str_contains($monitor, "CachedSchema::hasTable('jobs')"), 'Monitor não valida tabela jobs.', $erros);
assert_lote_07(str_contains($monitor, "CachedSchema::hasTable('failed_jobs')"), 'Monitor não valida tabela failed_jobs.', $erros);

$reprocessar = @file_get_contents($base . '/app/Console/Commands/ReprocessarFilasPrazzu.php') ?: '';
assert_lote_07(str_contains($reprocessar, "protected $" . "signature = 'prazzu:filas-reprocessar"), 'Signature do reprocessador de filas ausente.', $erros);
assert_lote_07(str_contains($reprocessar, "Artisan::call('queue:retry'"), 'Reprocessador não chama queue:retry.', $erros);
assert_lote_07(str_contains($reprocessar, '--dry-run'), 'Reprocessador não possui dry-run.', $erros);

$sql = @file_get_contents($base . '/database/sql/lote_07_filas_scheduler_notificacoes.sql') ?: '';
foreach (['`jobs`', '`job_batches`', '`failed_jobs`', '`notifications`'] as $tabela) {
    assert_lote_07(str_contains($sql, "CREATE TABLE IF NOT EXISTS {$tabela}"), "SQL não cria {$tabela} de forma idempotente.", $erros);
}
assert_lote_07(! preg_match('/\bDROP\s+TABLE\b/i', $sql), 'SQL contém DROP TABLE.', $erros);
assert_lote_07(! preg_match('/\bDROP\s+COLUMN\b/i', $sql), 'SQL contém DROP COLUMN.', $erros);

if ($erros !== []) {
    echo json_encode(['ok' => false, 'erros' => $erros], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
    exit(1);
}

echo json_encode(['ok' => true, 'lote' => 7, 'checks' => count($arquivos) + 20], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;
