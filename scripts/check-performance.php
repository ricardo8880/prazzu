<?php

$basePath = dirname(__DIR__);
$errors = [];
$warnings = [];
$ok = [];

$requiredFiles = [
    'app/Support/PrazzuPerformance.php',
    'database/sql/lote_13_performance.sql',
];

foreach ($requiredFiles as $file) {
    if (is_file($basePath.'/'.$file)) {
        $ok[] = "Arquivo encontrado: {$file}";
    } else {
        $errors[] = "Arquivo ausente: {$file}";
    }
}

$env = is_file($basePath.'/.env') ? file_get_contents($basePath.'/.env') : '';

if ($env !== '') {
    foreach ([
        'APP_ENV=local' => 'APP_ENV ainda está local; em produção use APP_ENV=production.',
        'APP_DEBUG=true' => 'APP_DEBUG=true reduz segurança e performance; em produção use false.',
        'CACHE_STORE=file' => 'CACHE_STORE=file funciona, mas Redis/database tende a escalar melhor.',
        'QUEUE_CONNECTION=sync' => 'QUEUE_CONNECTION=sync executa tarefas no request; use database/redis em produção.',
    ] as $needle => $message) {
        if (str_contains($env, $needle)) {
            $warnings[] = $message;
        }
    }
}

$composer = json_decode(@file_get_contents($basePath.'/composer.json') ?: '{}', true);
$config = $composer['config'] ?? [];

if (($config['optimize-autoloader'] ?? false) === true) {
    $ok[] = 'Composer optimize-autoloader ativo.';
} else {
    $warnings[] = 'Ative config.optimize-autoloader no composer.json para produção.';
}

$storageDirs = [
    'storage/framework/cache',
    'storage/framework/views',
    'storage/logs',
];

foreach ($storageDirs as $dir) {
    if (is_dir($basePath.'/'.$dir) && is_writable($basePath.'/'.$dir)) {
        $ok[] = "Diretório gravável: {$dir}";
    } else {
        $warnings[] = "Diretório não gravável ou ausente: {$dir}";
    }
}

$schemaSql = is_file($basePath.'/database/sql/lote_13_performance.sql')
    ? file_get_contents($basePath.'/database/sql/lote_13_performance.sql')
    : '';

foreach (['item_controles', 'audit_logs', 'asaas_webhook_events'] as $table) {
    if ($schemaSql !== '' && str_contains($schemaSql, $table)) {
        $ok[] = "Índices de performance contemplam {$table}.";
    } else {
        $warnings[] = "SQL de performance não menciona {$table}.";
    }
}

foreach ($ok as $line) {
    echo "[OK] {$line}\n";
}

foreach ($warnings as $line) {
    echo "[AVISO] {$line}\n";
}

foreach ($errors as $line) {
    echo "[ERRO] {$line}\n";
}

exit($errors === [] ? 0 : 1);
