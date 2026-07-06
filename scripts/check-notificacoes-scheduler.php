<?php

$basePath = dirname(__DIR__);

$checks = [];

$read = static function (string $relative) use ($basePath): string {
    $path = $basePath . DIRECTORY_SEPARATOR . $relative;

    return is_file($path) ? (string) file_get_contents($path) : '';
};

$assertContains = static function (string $name, string $content, array $needles) use (&$checks): void {
    $missing = [];

    foreach ($needles as $needle) {
        if (! str_contains($content, $needle)) {
            $missing[] = $needle;
        }
    }

    $checks[] = [
        'name' => $name,
        'status' => $missing === [] ? 'ok' : 'fail',
        'missing' => $missing,
    ];
};

$command = $read('app/Console/Commands/NotificarVencimentoItensControle.php');
$notification = $read('app/Notifications/ItemControleVencimentoNotification.php');
$itemControle = $read('app/Models/ItemControle.php');
$routesConsole = $read('routes/console.php');

$assertContains('comando_notificacoes', $command, [
    '{--dry-run',
    '{--empresa_id=',
    '{--limit=0',
    'notificacaoJaRegistrada',
    'tipo_notificacao',
    'enviado_em',
    'forceFill($updates)->save()',
]);

$assertContains('notification_channels', $notification, [
    'public ?Configuracao $configuracao = null',
    'enviar_email',
    'enviar_sistema',
    'return $channels;',
    'canaisAtivos',
]);

$assertContains('item_controle_flags_notificacao', $itemControle, [
    "'notificado_3_dias'",
    "'notificado_no_dia'",
    "'notificado_vencido'",
    "'ultimo_alerta_enviado_em'",
    "'ultimo_lembrete_enviado_em'",
    "'qtd_lembretes_enviados'",
]);

$assertContains('scheduler_notificacoes', $routesConsole, [
    "item-controle:notificar-vencimentos --limit=1000",
    "->dailyAt('08:00')",
    '->withoutOverlapping(60)',
    '->onOneServer()',
]);

$failed = array_values(array_filter($checks, static fn (array $check): bool => $check['status'] !== 'ok'));

$result = [
    'lote' => '07-notificacoes-scheduler',
    'checked_at' => date(DATE_ATOM),
    'status' => $failed === [] ? 'ok' : 'fail',
    'checks' => $checks,
];

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;

exit($failed === [] ? 0 : 1);
