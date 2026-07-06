<?php

$root = realpath(__DIR__ . '/..') ?: getcwd();
$errors = [];
$warnings = [];
$ok = [];

function relpath(string $path, string $root): string
{
    return ltrim(str_replace($root, '', $path), DIRECTORY_SEPARATOR);
}

function requireFile(string $file, array &$errors, string $root): ?string
{
    $path = $root . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $file);

    if (! is_file($path)) {
        $errors[] = "Arquivo obrigatório ausente: {$file}";
        return null;
    }

    return file_get_contents($path) ?: '';
}

$accessControl = requireFile('app/Support/PrazzuAccessControl.php', $errors, $root);

if ($accessControl !== null) {
    if (str_contains($accessControl, 'function canAccessPage(')) {
        $ok[] = 'Gate central PrazzuAccessControl::canAccessPage() encontrado.';
    } else {
        $errors[] = 'Gate central PrazzuAccessControl::canAccessPage() não encontrado.';
    }

    foreach (['canUseWorkArea', 'canAny', 'isSuperAdmin'] as $needle) {
        if (! str_contains($accessControl, $needle)) {
            $errors[] = "PrazzuAccessControl não contém verificação esperada: {$needle}";
        }
    }
}

$pages = [
    'app/Filament/Pages/ComplianceInterno.php' => 'governanca.view',
    'app/Filament/Pages/Riscos.php' => 'governanca.view',
    'app/Filament/Pages/Gantt.php' => 'tarefas.view',
    'app/Filament/Pages/Pendencias.php' => 'tarefas.view',
    'app/Filament/Pages/Assinaturas.php' => 'contratos.view',
];

foreach ($pages as $file => $permission) {
    $content = requireFile($file, $errors, $root);

    if ($content === null) {
        continue;
    }

    if (preg_match('/function\s+canAccess\s*\([^)]*\)\s*:\s*bool\s*\{\s*return\s+auth\(\)->check\(\);\s*\}/s', $content)) {
        $errors[] = "{$file} ainda usa auth()->check() como regra única de acesso.";
    }

    if (! str_contains($content, 'PrazzuAccessControl::canAccessPage')) {
        $errors[] = "{$file} não usa o gate central canAccessPage().";
    }

    if (! str_contains($content, $permission)) {
        $errors[] = "{$file} não referencia a permissão esperada {$permission}.";
    }

    if (str_contains($content, 'function shouldRegisterNavigation')) {
        $ok[] = "{$file}: navegação protegida por canAccess().";
    } else {
        $warnings[] = "{$file}: shouldRegisterNavigation() não foi encontrado; confirme se o Filament oculta navegação automaticamente pela versão instalada.";
    }
}

$result = [
    'status' => $errors ? 'fail' : 'ok',
    'checked_at' => date(DATE_ATOM),
    'ok' => $ok,
    'warnings' => $warnings,
    'errors' => $errors,
];

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;

exit($errors ? 1 : 0);
