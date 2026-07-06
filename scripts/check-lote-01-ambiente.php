<?php
/**
 * Lote 01 - Ambiente e Segredos
 * Executa validações básicas de ambiente sem depender do Laravel subir.
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

$requiredExtensions = [
    'ctype', 'curl', 'dom', 'fileinfo', 'filter', 'hash', 'mbstring',
    'openssl', 'pcre', 'pdo', 'pdo_mysql', 'session', 'tokenizer', 'xml', 'zip',
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
        $setup = $composer['scripts']['setup'] ?? [];
        $setupText = implode("\n", is_array($setup) ? $setup : [$setup]);
        if (stripos($setupText, 'migrate') === false) {
            add_result($ok, 'composer setup não executa migrations automaticamente');
        } else {
            add_result($errors, 'composer setup ainda contém migrate. O projeto usa banco SQL oficial, não migrations.');
        }
    } else {
        add_result($errors, 'composer.json inválido: ' . json_last_error_msg());
    }
} else {
    add_result($errors, 'composer.json não encontrado');
}

$env = $basePath . DIRECTORY_SEPARATOR . '.env';
$envExample = $basePath . DIRECTORY_SEPARATOR . '.env.example';
$envProdExample = $basePath . DIRECTORY_SEPARATOR . '.env.production.example';

if (is_file($envExample)) {
    add_result($ok, '.env.example encontrado');
} else {
    add_result($warnings, '.env.example não encontrado');
}

if (is_file($envProdExample)) {
    add_result($ok, '.env.production.example encontrado');
} else {
    add_result($warnings, '.env.production.example não encontrado');
}

if (is_file($env)) {
    $envContent = (string) file_get_contents($env);
    if (preg_match('/APP_ENV\s*=\s*production/i', $envContent) && preg_match('/APP_DEBUG\s*=\s*false/i', $envContent)) {
        add_result($ok, '.env aparenta estar configurado para produção');
    } else {
        add_result($warnings, '.env existe, mas não aparenta estar pronto para produção. Confira APP_ENV=production e APP_DEBUG=false.');
    }

    $secretPatterns = [
        '/ASAAS_API_KEY\s*=\s*$/m' => 'ASAAS_API_KEY vazio',
        '/ASAAS_WEBHOOK_TOKEN\s*=\s*$/m' => 'ASAAS_WEBHOOK_TOKEN vazio',
        '/APP_KEY\s*=\s*$/m' => 'APP_KEY vazio',
    ];
    foreach ($secretPatterns as $pattern => $message) {
        if (preg_match($pattern, $envContent)) {
            add_result($warnings, $message);
        }
    }
} else {
    add_result($warnings, '.env não encontrado. Crie a partir de .env.production.example no servidor.');
}

$storageDirs = ['storage', 'storage/app', 'storage/framework', 'storage/logs', 'bootstrap/cache'];
foreach ($storageDirs as $dir) {
    $path = $basePath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $dir);
    if (is_dir($path)) {
        add_result($ok, "Diretório encontrado: {$dir}");
        if (!is_writable($path)) {
            add_result($warnings, "Diretório sem permissão de escrita para o usuário atual: {$dir}");
        }
    } else {
        add_result($warnings, "Diretório não encontrado: {$dir}");
    }
}

function print_section(string $title, array $items): void
{
    echo "\n{$title}\n";
    echo str_repeat('-', strlen($title)) . "\n";
    if (!$items) {
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

echo "APROVADO COM " . count($warnings) . " AVISO(S)\n";
exit(0);
