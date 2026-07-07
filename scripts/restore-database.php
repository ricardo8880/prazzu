#!/usr/bin/env php
<?php

/**
 * Restore controlado do banco Prazzu.
 * Uso: php scripts/restore-database.php caminho/do/backup.sql --confirmo-restore
 */

$basePath = dirname(__DIR__);
$envPath = $basePath . '/.env';
$backupFile = $argv[1] ?? null;
$confirmed = in_array('--confirmo-restore', $argv, true);

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

if ($backupFile === null || ! is_file($backupFile)) {
    fwrite(STDERR, "Informe um arquivo SQL válido. Ex: php scripts/restore-database.php storage/app/backups/database/backup.sql --confirmo-restore\n");
    exit(1);
}

if (! $confirmed) {
    fwrite(STDERR, "Restore bloqueado. Reexecute com --confirmo-restore após fazer backup novo e validar a janela de manutenção.\n");
    exit(1);
}

$db = [
    'connection' => read_env_value($envPath, 'DB_CONNECTION', 'mysql'),
    'host' => read_env_value($envPath, 'DB_HOST', '127.0.0.1'),
    'port' => read_env_value($envPath, 'DB_PORT', '3306'),
    'database' => read_env_value($envPath, 'DB_DATABASE'),
    'username' => read_env_value($envPath, 'DB_USERNAME'),
    'password' => read_env_value($envPath, 'DB_PASSWORD', ''),
];

if ($db['connection'] !== 'mysql' || empty($db['database']) || empty($db['username'])) {
    fwrite(STDERR, "Configuração DB_* inválida para restore MySQL.\n");
    exit(1);
}

$mysql = trim((string) shell_exec('command -v mysql'));
if ($mysql === '') {
    fwrite(STDERR, "mysql não encontrado no PATH. Instale o cliente MySQL/MariaDB.\n");
    exit(1);
}

$command = [
    escapeshellcmd($mysql),
    '--default-character-set=utf8mb4',
    '-h' . escapeshellarg((string) $db['host']),
    '-P' . escapeshellarg((string) $db['port']),
    '-u' . escapeshellarg((string) $db['username']),
    escapeshellarg((string) $db['database']),
];

$env = '';
if ((string) $db['password'] !== '') {
    $env = 'MYSQL_PWD=' . escapeshellarg((string) $db['password']) . ' ';
}

$fullCommand = $env . implode(' ', $command) . ' < ' . escapeshellarg($backupFile) . ' 2>&1';
exec($fullCommand, $lines, $exitCode);

if ($exitCode !== 0) {
    fwrite(STDERR, "Falha no restore. Código: {$exitCode}\n");
    if ($lines !== []) {
        fwrite(STDERR, implode("\n", $lines) . "\n");
    }
    exit(1);
}

echo json_encode([
    'status' => 'ok',
    'restored_file' => $backupFile,
    'database' => $db['database'],
    'restored_at' => date(DATE_ATOM),
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;
