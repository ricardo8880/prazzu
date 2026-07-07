<?php
/**
 * Lote 01 - Segurança e Ambiente de Produção
 *
 * Valida hardening básico sem depender do Laravel inicializar. O projeto usa
 * banco SQL oficial, então este check também garante que nenhum script padrão
 * do Composer execute migrations automaticamente.
 *
 * Uso: php scripts/check-lote-01-ambiente.php
 */

$basePath = dirname(__DIR__);
$errors = [];
$warnings = [];
$ok = [];

function add_result(array &$list, string $message): void
{
    $list[] = $message;
}

function env_pairs(string $file): array
{
    if (! is_file($file)) {
        return [];
    }

    $pairs = [];
    foreach (file($file, FILE_IGNORE_NEW_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || ! str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $pairs[trim($key)] = trim($value, " \t\n\r\0\x0B\"'");
    }

    return $pairs;
}

$requiredExtensions = [
    'bcmath', 'ctype', 'curl', 'dom', 'fileinfo', 'filter', 'gd', 'iconv', 'intl',
    'json', 'libxml', 'mbstring', 'openssl', 'pdo', 'pdo_mysql', 'session',
    'tokenizer', 'xml', 'zip',
];

foreach ($requiredExtensions as $extension) {
    if (extension_loaded($extension)) {
        add_result($ok, "Extensão PHP carregada: {$extension}");
    } else {
        add_result($errors, "Extensão PHP ausente: {$extension}");
    }
}

if (version_compare(PHP_VERSION, '8.2.0', '>=')) {
    add_result($ok, 'PHP >= 8.2: ' . PHP_VERSION);
} else {
    add_result($errors, 'PHP precisa ser >= 8.2. Versão atual: ' . PHP_VERSION);
}

$composerJson = $basePath . DIRECTORY_SEPARATOR . 'composer.json';
if (is_file($composerJson)) {
    $composer = json_decode((string) file_get_contents($composerJson), true);
    if (json_last_error() === JSON_ERROR_NONE) {
        add_result($ok, 'composer.json válido');
        foreach (($composer['scripts'] ?? []) as $name => $script) {
            $scriptText = implode("\n", is_array($script) ? $script : [$script]);
            if (stripos($scriptText, 'artisan migrate') !== false) {
                add_result($errors, "Composer script '{$name}' executa migration automaticamente. O projeto usa SQL oficial.");
            }
        }
    } else {
        add_result($errors, 'composer.json inválido: ' . json_last_error_msg());
    }
} else {
    add_result($errors, 'composer.json não encontrado');
}

$requiredFiles = [
    '.env.example',
    '.env.production.example',
    'config/prazzu_security.php',
    'app/Http/Middleware/ApplySecurityHeaders.php',
    'app/Http/Middleware/BlockUnsafeDebugParameters.php',
];

foreach ($requiredFiles as $file) {
    if (is_file($basePath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $file))) {
        add_result($ok, "Arquivo encontrado: {$file}");
    } else {
        add_result($errors, "Arquivo obrigatório não encontrado: {$file}");
    }
}

$envProduction = env_pairs($basePath . DIRECTORY_SEPARATOR . '.env.production.example');
$expectedProduction = [
    'APP_ENV' => 'production',
    'APP_DEBUG' => 'false',
    'SESSION_ENCRYPT' => 'true',
    'SESSION_SECURE_COOKIE' => 'true',
    'SESSION_HTTP_ONLY' => 'true',
    'PRAZZU_SECURITY_HEADERS_ENABLED' => 'true',
    'PRAZZU_HSTS_ENABLED' => 'true',
    'PRAZZU_ALLOW_DEBUG_QUERY_PARAMETERS' => 'false',
    'PRAZZU_BLOCK_DEBUG_QUERY_PARAMETERS_IN_PRODUCTION' => 'true',
];

foreach ($expectedProduction as $key => $expected) {
    $actual = strtolower((string) ($envProduction[$key] ?? ''));
    if ($actual === strtolower($expected)) {
        add_result($ok, ".env.production.example {$key}={$expected}");
    } else {
        add_result($errors, ".env.production.example precisa conter {$key}={$expected}. Valor atual: " . ($envProduction[$key] ?? '[ausente]'));
    }
}

foreach (['APP_KEY', 'ASAAS_API_KEY', 'ASAAS_WEBHOOK_TOKEN', 'ADMIN_PASSWORD'] as $secretKey) {
    $value = (string) ($envProduction[$secretKey] ?? '');
    if ($value === '' || str_starts_with($value, 'trocar_')) {
        add_result($ok, ".env.production.example não expõe segredo real em {$secretKey}");
    } else {
        add_result($errors, ".env.production.example parece expor segredo real em {$secretKey}");
    }
}

$env = $basePath . DIRECTORY_SEPARATOR . '.env';
if (is_file($env)) {
    add_result($warnings, '.env existe na cópia local. Não envie este arquivo em commits/ZIPs de entrega. Use .env.production.example no servidor.');
}

$bootstrap = (string) @file_get_contents($basePath . DIRECTORY_SEPARATOR . 'bootstrap/app.php');
foreach (['BlockUnsafeDebugParameters', 'ApplySecurityHeaders', 'DebugPerformanceMiddleware'] as $middleware) {
    if (str_contains($bootstrap, $middleware)) {
        add_result($ok, "Middleware registrado: {$middleware}");
    } else {
        add_result($errors, "Middleware não registrado no bootstrap/app.php: {$middleware}");
    }
}

$appProvider = (string) @file_get_contents($basePath . DIRECTORY_SEPARATOR . 'app/Providers/AppServiceProvider.php');
if (str_contains($appProvider, 'allow_debug_query_parameters') && str_contains($appProvider, "app()->environment('production')")) {
    add_result($ok, 'Debug SQL bloqueado por padrão em produção no AppServiceProvider');
} else {
    add_result($errors, 'AppServiceProvider não bloqueia debug SQL por padrão em produção');
}

$storageDirs = ['storage', 'storage/app', 'storage/app/private', 'storage/framework', 'storage/logs', 'bootstrap/cache'];
foreach ($storageDirs as $dir) {
    $path = $basePath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $dir);
    if (! is_dir($path)) {
        @mkdir($path, 0775, true);
    }
    if (is_dir($path)) {
        add_result($ok, "Diretório encontrado: {$dir}");
        if (! is_writable($path)) {
            add_result($warnings, "Diretório sem permissão de escrita para o usuário atual: {$dir}");
        }
    } else {
        add_result($errors, "Diretório obrigatório não encontrado: {$dir}");
    }
}

function print_section(string $title, array $items): void
{
    echo "\n{$title}\n";
    echo str_repeat('-', strlen($title)) . "\n";
    if (! $items) {
        echo "Nenhum item.\n";
        return;
    }
    foreach ($items as $item) {
        echo "- {$item}\n";
    }
}

print_section('OK', $ok);
print_section('AVISOS', $warnings);
print_section('ERROS', $errors);

echo "\nResultado Lote 01: ";
if ($errors) {
    echo "REPROVADO\n";
    exit(1);
}

echo 'APROVADO COM ' . count($warnings) . " AVISO(S)\n";
exit(0);
