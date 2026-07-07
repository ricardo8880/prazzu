<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = [];
$warnings = [];

function read_file_or_fail(string $path, array &$errors): string
{
    if (! is_file($path)) {
        $errors[] = "Arquivo obrigatório não encontrado: {$path}";
        return '';
    }

    return (string) file_get_contents($path);
}

function assert_contains(string $haystack, string $needle, string $label, array &$errors): void
{
    if (! str_contains($haystack, $needle)) {
        $errors[] = "Validação falhou em {$label}: trecho esperado não encontrado: {$needle}";
    }
}

$accessControl = read_file_or_fail($root . '/app/Support/PrazzuAccessControl.php', $errors);
assert_contains($accessControl, 'canAccessCompanyRecord', 'PrazzuAccessControl', $errors);
assert_contains($accessControl, 'abortUnlessCompanyRecord', 'PrazzuAccessControl', $errors);
assert_contains($accessControl, 'requirePermission', 'PrazzuAccessControl', $errors);
assert_contains($accessControl, 'whereRaw(\'1 = 0\')', 'PrazzuAccessControl::applyEmpresaScope', $errors);

$permissionService = read_file_or_fail($root . '/app/Services/PrazzuPermissionService.php', $errors);
assert_contains($permissionService, "if (\$module === 'system_health')", 'PrazzuPermissionService fallback', $errors);
assert_contains($permissionService, "['governanca', 'configuracoes', 'auditoria', 'system_health', 'financeiro']", 'PrazzuPermissionService fallback sensível', $errors);

$policies = [
    'app/Policies/ItemControlePolicy.php' => ['tarefas.view', 'tarefas.create', 'tarefas.edit', 'tarefas.delete'],
    'app/Policies/UserPolicy.php' => ['governanca.view', 'governanca.create', 'governanca.edit', 'governanca.delete'],
    'app/Policies/EmpresaPolicy.php' => ['configuracoes.view', 'configuracoes.create', 'configuracoes.edit', 'configuracoes.delete'],
];

foreach ($policies as $relative => $needles) {
    $content = read_file_or_fail($root . '/' . $relative, $errors);
    foreach ($needles as $needle) {
        assert_contains($content, $needle, $relative, $errors);
    }
}

$filesToScan = [];
foreach (['app/Filament/Pages', 'app/Filament/Resources'] as $dir) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/' . $dir));
    foreach ($iterator as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $filesToScan[] = $file->getPathname();
        }
    }
}

foreach ($filesToScan as $file) {
    $content = (string) file_get_contents($file);
    $relative = str_replace($root . '/', '', $file);

    if (preg_match('/function\s+canAccess\s*\([^)]*\)\s*:\s*bool\s*\{\s*return\s+(auth\(\)|Filament::auth\(\))->check\(\)\s*;\s*\}/s', $content)) {
        $warnings[] = "canAccess com autenticação simples encontrado em {$relative}";
    }

    if (preg_match('/function\s+shouldRegisterNavigation\s*\([^)]*\)\s*:\s*bool\s*\{\s*return\s+(auth\(\)|Filament::auth\(\))->check\(\)\s*;\s*\}/s', $content)) {
        $warnings[] = "shouldRegisterNavigation com autenticação simples encontrado em {$relative}";
    }
}

if ($errors) {
    echo "LOTE 03: FALHOU\n";
    foreach ($errors as $error) {
        echo "[ERRO] {$error}\n";
    }
    exit(1);
}

echo "LOTE 03: OK\n";
echo "Arquivos auditados: " . count($filesToScan) . "\n";

if ($warnings) {
    echo "Avisos para revisão manual:\n";
    foreach ($warnings as $warning) {
        echo "[AVISO] {$warning}\n";
    }
    exit(2);
}

echo "Nenhum canAccess/shouldRegisterNavigation crítico com autenticação simples foi encontrado.\n";
