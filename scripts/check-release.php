<?php

/**
 * Gate de homologação final do Prazzu.
 *
 * Este script não depende do Artisan para poder rodar mesmo antes da aplicação
 * estar totalmente configurada. Ele valida se os lotes estruturais foram
 * aplicados e aponta pendências objetivas antes de piloto/produção.
 */

$basePath = dirname(__DIR__);
$errors = [];
$warnings = [];
$ok = [];

function release_ok(bool $condition, string $success, string $failure, array &$ok, array &$errors): void
{
    if ($condition) {
        $ok[] = $success;
        return;
    }

    $errors[] = $failure;
}

function release_warn(bool $condition, string $success, string $failure, array &$ok, array &$warnings): void
{
    if ($condition) {
        $ok[] = $success;
        return;
    }

    $warnings[] = $failure;
}

function release_read_json(string $path): array
{
    if (! is_file($path)) {
        return [];
    }

    $decoded = json_decode((string) file_get_contents($path), true);

    return is_array($decoded) ? $decoded : [];
}

$requiredScripts = [
    'scripts/check-environment.php',
    'scripts/check-database.php',
    'scripts/check-performance.php',
    'scripts/check-quality.php',
    'scripts/check-release.php',
];

foreach ($requiredScripts as $file) {
    release_ok(is_file($basePath . '/' . $file), "Script encontrado: {$file}", "Script ausente: {$file}", $ok, $errors);
}

$requiredDocs = [
    'docs/lotes/lote_01_ambiente.md',
    'docs/lotes/lote_02_banco_de_dados.md',
    'docs/lotes/lote_03_seguranca.md',
    'docs/lotes/lote_04_nucleo_item_controle.md',
    'docs/lotes/lote_05_fluxo_operacional.md',
    'docs/lotes/lote_06_sla_prazos.md',
    'docs/lotes/lote_07_notificacoes_scheduler.md',
    'docs/lotes/lote_08_portal_cliente.md',
    'docs/lotes/lote_09_documentos_storage.md',
    'docs/lotes/lote_10_financeiro_asaas.md',
    'docs/lotes/lote_11_auditoria.md',
    'docs/lotes/lote_12_ux_navegacao.md',
    'docs/lotes/lote_13_performance.md',
    'docs/lotes/lote_14_qualidade_testes.md',
    'docs/lotes/lote_15_homologacao_final.md',
];

foreach ($requiredDocs as $file) {
    release_warn(is_file($basePath . '/' . $file), "Documento de lote encontrado: {$file}", "Documento de lote não encontrado: {$file}", $ok, $warnings);
}

$requiredSqlFiles = [
    'database/sql/lote_02_banco_de_dados.sql',
    'database/sql/lote_03_seguranca.sql',
    'database/sql/lote_04_nucleo_item_controle.sql',
    'database/sql/lote_05_fluxo_operacional.sql',
    'database/sql/lote_06_sla_prazos.sql',
    'database/sql/lote_07_notificacoes_scheduler.sql',
    'database/sql/lote_08_portal_cliente.sql',
    'database/sql/lote_09_documentos_storage.sql',
    'database/sql/lote_10_financeiro_asaas.sql',
    'database/sql/lote_11_auditoria.sql',
    'database/sql/lote_12_ux_navegacao.sql',
    'database/sql/lote_13_performance.sql',
    'database/sql/lote_14_qualidade_testes.sql',
    'database/sql/lote_15_homologacao_final.sql',
];

foreach ($requiredSqlFiles as $file) {
    release_warn(is_file($basePath . '/' . $file), "SQL manual encontrado: {$file}", "SQL manual não encontrado: {$file}", $ok, $warnings);
}

$composer = release_read_json($basePath . '/composer.json');
$scripts = $composer['scripts'] ?? [];

foreach (['env:check', 'db:check', 'performance:check', 'quality:check', 'release:check'] as $script) {
    release_ok(isset($scripts[$script]), "Script Composer registrado: {$script}", "composer.json não possui o script {$script}.", $ok, $errors);
}

$envExample = $basePath . '/.env.example';
release_warn(is_file($envExample), '.env.example encontrado para conferência de ambiente.', '.env.example não encontrado.', $ok, $warnings);

if (is_file($envExample)) {
    $env = (string) file_get_contents($envExample);
    foreach (['APP_KEY=', 'DB_CONNECTION=', 'QUEUE_CONNECTION=', 'FILESYSTEM_DISK='] as $needle) {
        release_warn(str_contains($env, $needle), ".env.example contém {$needle}", ".env.example não contém {$needle}", $ok, $warnings);
    }
}

$routes = [
    'routes/web.php',
    'routes/console.php',
];

foreach ($routes as $file) {
    release_ok(is_file($basePath . '/' . $file), "Arquivo de rota encontrado: {$file}", "Arquivo de rota ausente: {$file}", $ok, $errors);
}

$storagePath = $basePath . '/storage/app';
release_warn(is_dir($storagePath) && is_writable($storagePath), 'storage/app existe e está gravável.', 'storage/app não existe ou não está gravável.', $ok, $warnings);

$publicStorage = $basePath . '/public/storage';
release_warn(is_link($publicStorage) || is_dir($publicStorage), 'public/storage existe como link ou diretório.', 'public/storage ainda não existe. Rode php artisan storage:link no ambiente final.', $ok, $warnings);

echo "\nPrazzu — Gate de homologação final\n";
echo str_repeat('=', 39) . "\n\n";

foreach ($ok as $message) {
    echo "[OK] {$message}\n";
}

if ($warnings !== []) {
    echo "\nAvisos:\n";
    foreach ($warnings as $message) {
        echo "[AVISO] {$message}\n";
    }
}

if ($errors !== []) {
    echo "\nErros:\n";
    foreach ($errors as $message) {
        echo "[ERRO] {$message}\n";
    }
    echo "\nHomologação bloqueada. Corrija os erros acima antes do piloto.\n";
    exit(1);
}

echo "\nGate de homologação aprovado. Revise os avisos antes de produção.\n";
exit(0);
