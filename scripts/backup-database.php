#!/usr/bin/env php
<?php

/**
 * Backup seguro do banco Prazzu.
 * Usa as credenciais DB_* do .env e gera arquivo em storage/app/backups/database.
 */

$basePath = dirname(__DIR__);
$envPath = $basePath . '/.env';
$backupDir = $basePath . '/storage/app/backups/database';

function read_env_value(string $path, string $key, ?string $default = null): ?string
{
    if (! is_file($path)) {
        return $default;
    }

    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || ! str_contains($line, '=')) {
            continue;
        }
        [$envKey, $value] = explode('=', $line, 2);
        if (trim($envKey) !== $key) {
            continue;
        }
        return trim(trim($value), "\"'");
    }

    return $default;
}

$db = [
    'connection' => read_env_value($envPath, 'DB_CONNECTION', 'mysql'),
    'host' => read_env_value($envPath, 'DB_HOST', '127.0.0.1'),
    'port' => read_env_value($envPath, 'DB_PORT', '3306'),
    'database' => read_env_value($envPath, 'DB_DATABASE'),
    'username' => read_env_value($envPath, 'DB_USERNAME'),
    'password' => read_env_value($envPath, 'DB_PASSWORD', ''),
];

if ($db['connection'] !== 'mysql') {
    fwrite(STDERR, "Backup automático suportado apenas para DB_CONNECTION=mysql.\n");
    exit(1);
}

foreach (['database', 'username'] as $required) {
    if (empty($db[$required])) {
        fwrite(STDERR, "Configuração ausente no .env: DB_" . strtoupper($required) . "\n");
        exit(1);
    }
}

if (! is_dir($backupDir) && ! mkdir($backupDir, 0750, true) && ! is_dir($backupDir)) {
    fwrite(STDERR, "Não foi possível criar a pasta de backup: {$backupDir}\n");
    exit(1);
}

$timestamp = date('Ymd_His');
$output = $backupDir . "/prazzu_backup_{$db['database']}_{$timestamp}.sql";
$mysqldump = trim((string) shell_exec('command -v mysqldump'));

if ($mysqldump === '') {
    fwrite(STDERR, "mysqldump não encontrado no PATH. Instale o cliente MySQL/MariaDB.\n");
    exit(1);
}

$command = [
    escapeshellcmd($mysqldump),
    '--single-transaction',
    '--quick',
    '--routines',
    '--triggers',
    '--events',
    '--default-character-set=utf8mb4',
    '-h' . escapeshellarg((string) $db['host']),
    '-P' . escapeshellarg((string) $db['port']),
    '-u' . escapeshellarg((string) $db['username']),
];

$env = '';
if ((string) $db['password'] !== '') {
    $env = 'MYSQL_PWD=' . escapeshellarg((string) $db['password']) . ' ';
}

$command[] = escapeshellarg((string) $db['database']);
$fullCommand = $env . implode(' ', $command) . ' > ' . escapeshellarg($output) . ' 2>&1';

exec($fullCommand, $lines, $exitCode);

if ($exitCode !== 0 || ! is_file($output) || filesize($output) === 0) {
    @unlink($output);
    fwrite(STDERR, "Falha ao gerar backup. Código: {$exitCode}\n");
    if ($lines !== []) {
        fwrite(STDERR, implode("\n", $lines) . "\n");
    }
    exit(1);
}

@chmod($output, 0640);

echo json_encode([
    'status' => 'ok',
    'backup' => str_replace($basePath . '/', '', $output),
    'bytes' => filesize($output),
    'created_at' => date(DATE_ATOM),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
