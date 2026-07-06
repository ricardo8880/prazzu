<?php

/**
 * Verificação mínima de ambiente do Prazzu.
 *
 * Este script não inicializa Laravel/Artisan, justamente para funcionar mesmo
 * quando alguma extensão obrigatória ainda estiver ausente no PHP CLI.
 */

$root = dirname(__DIR__);
$errors = [];
$warnings = [];
$ok = [];

function check_result(bool $condition, string $success, string $failure, array &$ok, array &$errors): void
{
    if ($condition) {
        $ok[] = $success;
        return;
    }

    $errors[] = $failure;
}

function warning_result(bool $condition, string $success, string $failure, array &$ok, array &$warnings): void
{
    if ($condition) {
        $ok[] = $success;
        return;
    }

    $warnings[] = $failure;
}

function command_exists(string $command): bool
{
    $where = stripos(PHP_OS_FAMILY, 'Windows') !== false ? 'where' : 'command -v';
    $output = [];
    $code = 1;
    @exec($where . ' ' . escapeshellarg($command) . ' 2>/dev/null', $output, $code);

    return $code === 0 && ! empty($output);
}

function env_pairs_from_file(string $file): array
{
    if (! is_file($file)) {
        return [];
    }

    $pairs = [];
    foreach (file($file, FILE_IGNORE_NEW_LINES) ?: [] as $line) {
        $trimmed = trim($line);
        if ($trimmed === '' || str_starts_with($trimmed, '#') || ! str_contains($trimmed, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $trimmed, 2);
        $key = trim($key);
        if ($key !== '') {
            $pairs[$key] = trim($value);
        }
    }

    ksort($pairs);

    return $pairs;
}

function env_keys_from_file(string $file): array
{
    return array_keys(env_pairs_from_file($file));
}

$requiredPhpExtensions = [
    'bcmath',
    'ctype',
    'curl',
    'dom',
    'fileinfo',
    'filter',
    'gd',
    'iconv',
    'intl',
    'json',
    'libxml',
    'mbstring',
    'openssl',
    'pdo',
    'pdo_mysql',
    'tokenizer',
    'xml',
    'zip',
];

$recommendedPhpExtensions = [
    'exif',
    'opcache',
    'redis',
];

check_result(version_compare(PHP_VERSION, '8.2.0', '>='), 'PHP >= 8.2 encontrado: ' . PHP_VERSION, 'PHP precisa ser >= 8.2. Versão atual: ' . PHP_VERSION, $ok, $errors);

foreach ($requiredPhpExtensions as $extension) {
    check_result(extension_loaded($extension), 'Extensão obrigatória carregada: ' . $extension, 'Extensão obrigatória ausente no PHP CLI: ' . $extension, $ok, $errors);
}

foreach ($recommendedPhpExtensions as $extension) {
    warning_result(extension_loaded($extension), 'Extensão recomendada carregada: ' . $extension, 'Extensão recomendada ausente no PHP CLI: ' . $extension, $ok, $warnings);
}

check_result(is_file($root . '/artisan'), 'Arquivo artisan encontrado.', 'Arquivo artisan não encontrado na raiz do projeto.', $ok, $errors);
check_result(is_file($root . '/composer.json'), 'composer.json encontrado.', 'composer.json não encontrado.', $ok, $errors);
check_result(is_file($root . '/package.json'), 'package.json encontrado.', 'package.json não encontrado.', $ok, $errors);

warning_result(command_exists('composer'), 'Composer disponível no PATH.', 'Composer não encontrado no PATH.', $ok, $warnings);
warning_result(command_exists('node'), 'Node.js disponível no PATH.', 'Node.js não encontrado no PATH.', $ok, $warnings);
warning_result(command_exists('npm'), 'npm disponível no PATH.', 'npm não encontrado no PATH.', $ok, $warnings);

$writableDirectories = [
    'bootstrap/cache',
    'storage',
    'storage/app',
    'storage/app/private',
    'storage/app/public',
    'storage/framework',
    'storage/framework/cache',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/logs',
];

foreach ($writableDirectories as $directory) {
    $path = $root . '/' . $directory;
    if (! is_dir($path)) {
        @mkdir($path, 0775, true);
    }

    check_result(is_dir($path), 'Diretório existe: ' . $directory, 'Diretório obrigatório não existe: ' . $directory, $ok, $errors);
    if (is_dir($path)) {
        check_result(is_writable($path), 'Diretório gravável: ' . $directory, 'Diretório sem permissão de escrita: ' . $directory, $ok, $errors);
    }
}

$envExamplePairs = env_pairs_from_file($root . '/.env.example');
$envExampleKeys = array_keys($envExamplePairs);
$envKeys = env_keys_from_file($root . '/.env');

$appKeyExample = $envExamplePairs['APP_KEY'] ?? '';
check_result($appKeyExample === '', '.env.example não contém APP_KEY real.', '.env.example parece conter APP_KEY real.', $ok, $errors);
warning_result(! is_file($root . '/.env'), '.env não está presente no pacote/projeto analisado.', '.env existe neste projeto. Não envie este arquivo em ZIPs ou commits.', $ok, $warnings);

$requiredEnvKeys = [
    'APP_NAME',
    'APP_ENV',
    'APP_KEY',
    'APP_DEBUG',
    'APP_URL',
    'DB_CONNECTION',
    'DB_HOST',
    'DB_PORT',
    'DB_DATABASE',
    'DB_USERNAME',
    'DB_PASSWORD',
    'CACHE_STORE',
    'QUEUE_CONNECTION',
    'SESSION_DRIVER',
    'FILESYSTEM_DISK',
    'MAIL_MAILER',
    'MAIL_HOST',
    'MAIL_PORT',
    'MAIL_FROM_ADDRESS',
    'ASAAS_BASE_URL',
    'ASAAS_API_KEY',
    'ASAAS_WEBHOOK_TOKEN',
    'SOCKET_IO_PORT',
    'SOCKET_IO_HOST',
    'SOCKET_IO_ALLOWED_ORIGINS',
    'VITE_SOCKET_IO_URL',
];

foreach ($requiredEnvKeys as $key) {
    check_result(in_array($key, $envExampleKeys, true), '.env.example contém ' . $key, '.env.example não contém ' . $key, $ok, $errors);
}

if ($envKeys !== []) {
    foreach (['APP_KEY', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'] as $key) {
        warning_result(in_array($key, $envKeys, true), '.env contém ' . $key, '.env não contém ' . $key, $ok, $warnings);
    }
}

$payload = [
    'status' => empty($errors) ? 'ok' : 'erro',
    'php_version' => PHP_VERSION,
    'php_binary' => PHP_BINARY,
    'errors' => $errors,
    'warnings' => $warnings,
    'ok' => $ok,
];

echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;

exit(empty($errors) ? 0 : 1);
