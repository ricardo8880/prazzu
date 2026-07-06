<?php

/**
 * Gate local de qualidade do Prazzu.
 *
 * Não depende do Artisan para conseguir rodar mesmo quando o ambiente ainda
 * estiver parcialmente quebrado. Use antes de empacotar novos lotes.
 */

$basePath = dirname(__DIR__);
$errors = [];
$warnings = [];
$ok = [];

function quality_add_result(bool $condition, string $success, string $failure, array &$ok, array &$errors): void
{
    if ($condition) {
        $ok[] = $success;
        return;
    }

    $errors[] = $failure;
}

function quality_warn_result(bool $condition, string $success, string $failure, array &$ok, array &$warnings): void
{
    if ($condition) {
        $ok[] = $success;
        return;
    }

    $warnings[] = $failure;
}

function quality_files(string $basePath, array $directories, array $extensions): array
{
    $files = [];

    foreach ($directories as $directory) {
        $path = $basePath . '/' . $directory;
        if (! is_dir($path)) {
            continue;
        }

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if (! $file->isFile()) {
                continue;
            }

            if (in_array(strtolower($file->getExtension()), $extensions, true)) {
                $files[] = $file->getPathname();
            }
        }
    }

    sort($files);

    return $files;
}

$requiredFiles = [
    'phpunit.xml',
    'tests/TestCase.php',
    'tests/Unit/Services/PrazzuSlaEngineTest.php',
    'tests/Unit/Support/PrazzuPerformanceTest.php',
    'scripts/check-environment.php',
    'scripts/check-database.php',
    'scripts/check-performance.php',
    'database/sql/lote_14_qualidade_testes.sql',
];

foreach ($requiredFiles as $file) {
    quality_add_result(is_file($basePath . '/' . $file), "Arquivo de qualidade encontrado: {$file}", "Arquivo de qualidade ausente: {$file}", $ok, $errors);
}

$composer = json_decode(@file_get_contents($basePath . '/composer.json') ?: '{}', true);
$scripts = $composer['scripts'] ?? [];
quality_add_result(isset($scripts['quality:check']), 'Script Composer quality:check registrado.', 'composer.json não possui o script quality:check.', $ok, $errors);
quality_add_result(isset($scripts['test:unit-core']), 'Script Composer test:unit-core registrado.', 'composer.json não possui o script test:unit-core.', $ok, $errors);
quality_warn_result(isset($scripts['env:check']), 'Script env:check disponível.', 'Script env:check ausente.', $ok, $warnings);
quality_warn_result(isset($scripts['db:check']), 'Script db:check disponível.', 'Script db:check ausente.', $ok, $warnings);
quality_warn_result(isset($scripts['performance:check']), 'Script performance:check disponível.', 'Script performance:check ausente.', $ok, $warnings);

$phpFiles = quality_files($basePath, ['app', 'routes', 'config', 'tests', 'scripts'], ['php']);
$syntaxErrors = [];
foreach ($phpFiles as $file) {
    $output = [];
    $code = 0;
    @exec(PHP_BINARY . ' -l ' . escapeshellarg($file) . ' 2>&1', $output, $code);
    if ($code !== 0) {
        $syntaxErrors[] = str_replace($basePath . '/', '', $file) . ': ' . implode(' ', $output);
    }
}

quality_add_result($syntaxErrors === [], 'Todos os arquivos PHP verificados passaram no php -l.', 'Há erros de sintaxe PHP: ' . implode(' | ', $syntaxErrors), $ok, $errors);

$forbiddenMigrationFiles = quality_files($basePath, ['database/migrations'], ['php']);
quality_warn_result($forbiddenMigrationFiles === [], 'Nenhuma migration nova detectada no pacote atual.', 'Existem migrations no projeto; manter padrão do cliente usando SQL manual.', $ok, $warnings);

$sqlFiles = quality_files($basePath, ['database/sql'], ['sql']);
quality_add_result($sqlFiles !== [], 'Diretório database/sql contém scripts manuais.', 'Nenhum SQL manual encontrado em database/sql.', $ok, $errors);

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
