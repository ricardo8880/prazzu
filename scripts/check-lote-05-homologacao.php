<?php

declare(strict_types=1);

/**
 * Lote 05 — Homologação final / Go-live Prazzu.
 *
 * Este script NÃO usa migrations e NÃO altera dados.
 * Ele valida pré-requisitos de produção, reexecuta checks dos lotes anteriores
 * quando existirem e aponta bloqueadores antes de colocar o SaaS no ar.
 *
 * Uso:
 *   php scripts/check-lote-05-homologacao.php          # homologação técnica local
 *   php scripts/check-lote-05-homologacao.php --strict # pré-go-live real
 */

$root = dirname(__DIR__);
$strict = in_array('--strict', $argv, true);

$errors = [];
$warnings = [];
$ok = [];

function pass(array &$ok, string $message): void { $ok[] = $message; }
function warn(array &$warnings, string $message): void { $warnings[] = $message; }
function fail(array &$errors, string $message): void { $errors[] = $message; }
function rel(string $root, string $path): string { return str_replace($root . DIRECTORY_SEPARATOR, '', $path); }

function loadEnvFile(string $path): array
{
    if (! is_file($path)) {
        return [];
    }

    $env = [];
    foreach (file($path, FILE_IGNORE_NEW_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || ! str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        if ((str_starts_with($value, '"') && str_ends_with($value, '"')) || (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
            $value = substr($value, 1, -1);
        }
        $env[$key] = $value;
    }
    return $env;
}

function runPhpScript(string $script): array
{
    $cmd = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($script) . ' 2>&1';
    $output = [];
    $code = 0;
    exec($cmd, $output, $code);
    return [$code, implode(PHP_EOL, $output)];
}

function mysqlValue(array $env, string $sql): array
{
    $host = $env['DB_HOST'] ?? '127.0.0.1';
    $port = (int) ($env['DB_PORT'] ?? 3306);
    $database = $env['DB_DATABASE'] ?? '';
    $username = $env['DB_USERNAME'] ?? '';
    $password = $env['DB_PASSWORD'] ?? '';

    if ($database === '' || $username === '') {
        return [false, 'DB_DATABASE/DB_USERNAME ausentes no .env'];
    }

    try {
        $pdo = new PDO("mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4", $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        return [true, $pdo->query($sql)->fetchColumn()];
    } catch (Throwable $e) {
        return [false, $e->getMessage()];
    }
}

// 1) Estrutura mínima do projeto.
$requiredFiles = [
    'artisan',
    'composer.json',
    'app/Models/ItemControle.php',
    'database/sql/prazzu_schema_oficial.sql',
    'scripts/check-lote-01-ambiente.php',
    'scripts/check-lote-02-banco.php',
    'scripts/check-lote-03-seguranca.php',
    'scripts/check-lote-04-fluxo-operacional.php',
];

foreach ($requiredFiles as $relative) {
    $file = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
    is_file($file) ? pass($ok, "Arquivo presente: {$relative}") : fail($errors, "Arquivo obrigatório ausente: {$relative}");
}

// 2) Extensões PHP necessárias para Laravel/produção.
$requiredExtensions = ['mbstring', 'openssl', 'pdo', 'pdo_mysql', 'tokenizer', 'xml', 'ctype', 'json', 'fileinfo', 'curl', 'zip'];
foreach ($requiredExtensions as $extension) {
    extension_loaded($extension) ? pass($ok, "Extensão PHP ativa: {$extension}") : fail($errors, "Extensão PHP ausente: {$extension}");
}

// 3) .env e postura de produção.
$envPath = $root . DIRECTORY_SEPARATOR . '.env';
$env = loadEnvFile($envPath);

if (! is_file($envPath)) {
    fail($errors, '.env ausente no ambiente de homologação/produção. Crie a partir do modelo seguro e preencha segredos reais fora do Git.');
} else {
    pass($ok, '.env encontrado no ambiente atual.');

    $requiredEnvKeys = ['APP_NAME', 'APP_ENV', 'APP_KEY', 'APP_DEBUG', 'APP_URL', 'DB_CONNECTION', 'DB_HOST', 'DB_DATABASE', 'DB_USERNAME'];
    foreach ($requiredEnvKeys as $key) {
        isset($env[$key]) && trim((string) $env[$key]) !== '' ? pass($ok, "Variável definida: {$key}") : fail($errors, "Variável obrigatória ausente/vazia no .env: {$key}");
    }

    if (($env['APP_ENV'] ?? '') !== 'production') {
        ($strict ? fail($errors, 'APP_ENV deve estar como production para go-live.') : warn($warnings, 'APP_ENV ainda não está production; aceitável em teste local, obrigatório no --strict.'));
    }
    if (strtolower((string) ($env['APP_DEBUG'] ?? '')) !== 'false') {
        ($strict ? fail($errors, 'APP_DEBUG deve estar como false para go-live.') : warn($warnings, 'APP_DEBUG ainda não está false; aceitável em teste local, obrigatório no --strict.'));
    }
    if (isset($env['APP_URL']) && preg_match('/localhost|127\.0\.0\.1|\.test|\.local/i', $env['APP_URL'])) {
        ($strict ? fail($errors, 'APP_URL ainda aponta para ambiente local/teste.') : warn($warnings, 'APP_URL aponta para ambiente local/teste; aceitável em teste local, obrigatório no --strict.'));
    }
    if (isset($env['APP_KEY']) && ! str_starts_with((string) $env['APP_KEY'], 'base64:')) {
        warn($warnings, 'APP_KEY não parece estar no formato base64: padrão do Laravel. Confirme se foi gerada com php artisan key:generate.');
    }

    $secretLikeKeys = ['ASAAS_API_KEY', 'ASAAS_WEBHOOK_TOKEN', 'MAIL_PASSWORD', 'AWS_SECRET_ACCESS_KEY'];
    foreach ($secretLikeKeys as $key) {
        if (isset($env[$key]) && preg_match('/changeme|preencher|exemplo|example|sandbox_token|secret_here/i', (string) $env[$key])) {
            fail($errors, "{$key} parece conter placeholder e deve ser substituída por segredo real.");
        }
    }
}

// 4) Pastas graváveis.
$writableDirs = ['storage', 'storage/logs', 'bootstrap/cache'];
foreach ($writableDirs as $relative) {
    $dir = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
    if (! is_dir($dir)) {
        fail($errors, "Diretório obrigatório ausente: {$relative}");
    } elseif (! is_writable($dir)) {
        fail($errors, "Diretório sem permissão de escrita: {$relative}");
    } else {
        pass($ok, "Diretório gravável: {$relative}");
    }
}

// 5) Banco oficial carregado.
if (($env['DB_CONNECTION'] ?? null) === 'mysql' && extension_loaded('pdo_mysql')) {
    [$success, $value] = mysqlValue($env, "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE()");
    if ($success) {
        $tableCount = (int) $value;
        $tableCount >= 100 ? pass($ok, "Banco conectado com {$tableCount} tabelas.") : fail($errors, "Banco conectado, mas possui apenas {$tableCount} tabelas. Esperado: schema oficial carregado.");
    } else {
        fail($errors, "Não foi possível conectar ao banco: {$value}");
    }
} else {
    warn($warnings, 'Conexão MySQL não testada porque DB_CONNECTION não é mysql ou pdo_mysql está ausente.');
}

// 6) Checks anteriores: falha em qualquer lote anterior bloqueia go-live.
$previousChecks = [
    'scripts/check-lote-01-ambiente.php',
    'scripts/check-lote-02-banco.php',
    'scripts/check-lote-03-seguranca.php',
    'scripts/check-lote-04-fluxo-operacional.php',
    'scripts/check-operational-workflow.php',
];

foreach ($previousChecks as $relative) {
    $script = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
    if (! is_file($script)) {
        fail($errors, "Check anterior ausente: {$relative}");
        continue;
    }
    [$code, $output] = runPhpScript($script);
    if ($code === 0) {
        pass($ok, "Check aprovado: {$relative}");
    } else {
        fail($errors, "Check falhou: {$relative}" . ($output !== '' ? PHP_EOL . $output : ''));
    }
}

// 7) Sanidade Artisan sem depender de migrations.
$artisan = $root . DIRECTORY_SEPARATOR . 'artisan';
if (is_file($artisan) && extension_loaded('mbstring')) {
    $commands = [
        'about --only=environment',
        'route:list --except-vendor',
    ];
    foreach ($commands as $command) {
        $cmd = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($artisan) . ' ' . $command . ' 2>&1';
        $output = [];
        $code = 0;
        exec($cmd, $output, $code);
        $code === 0 ? pass($ok, "Artisan OK: {$command}") : fail($errors, "Artisan falhou em '{$command}': " . implode(PHP_EOL, array_slice($output, -20)));
    }
}

// 8) Sinalizar que homologação manual ainda é obrigatória.
$manualChecklist = $root . DIRECTORY_SEPARATOR . 'docs/deploy/checklist_homologacao_final.md';
is_file($manualChecklist) ? pass($ok, 'Checklist manual de homologação presente.') : fail($errors, 'Checklist manual de homologação ausente: docs/deploy/checklist_homologacao_final.md');

if ($strict && $warnings) {
    foreach ($warnings as $warning) {
        fail($errors, "STRICT: {$warning}");
    }
    $warnings = [];
}

echo PHP_EOL . '=== LOTE 05 — HOMOLOGAÇÃO FINAL ===' . PHP_EOL;
echo 'OK: ' . count($ok) . PHP_EOL;
echo 'Avisos: ' . count($warnings) . PHP_EOL;
echo 'Erros: ' . count($errors) . PHP_EOL . PHP_EOL;

foreach ($ok as $message) {
    echo '[OK] ' . $message . PHP_EOL;
}
foreach ($warnings as $message) {
    echo '[AVISO] ' . $message . PHP_EOL;
}
foreach ($errors as $message) {
    echo '[ERRO] ' . $message . PHP_EOL;
}

echo PHP_EOL;
if ($errors) {
    echo 'STATUS: FALHOU — NÃO liberar produção antes de corrigir os erros acima.' . PHP_EOL;
    exit(1);
}

echo 'STATUS: OK — checks automatizados aprovados. Agora conclua e assine o checklist manual antes do go-live.' . PHP_EOL;
exit(0);
