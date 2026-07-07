#!/usr/bin/env php
<?php

/**
 * Lote 5 - Portal do Cliente + Financeiro/Asaas.
 * Valida hardening de upload/download, webhook Asaas e configs críticas.
 */

$basePath = dirname(__DIR__);
$errors = [];
$warnings = [];
$ok = [];

function read_file_checked(string $relative, array &$errors, string $basePath): string
{
    $path = $basePath . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relative);

    if (! is_file($path)) {
        $errors[] = "Arquivo obrigatório ausente: {$relative}";
        return '';
    }

    return (string) file_get_contents($path);
}

function contains_all(string $content, array $needles, string $label, array &$ok, array &$errors): void
{
    foreach ($needles as $needle) {
        if (str_contains($content, $needle)) {
            $ok[] = "{$label}: encontrado {$needle}";
        } else {
            $errors[] = "{$label}: ausente {$needle}";
        }
    }
}

$documentStorage = read_file_checked('app/Support/DocumentStorage.php', $errors, $basePath);
contains_all($documentStorage, [
    'BLOCKED_EXTENSIONS',
    'assertSafeOriginalName',
    '$size <= 0',
    'safeDownloadName',
], 'DocumentStorage', $ok, $errors);

$portalController = read_file_checked('app/Http/Controllers/PortalClientePublicoController.php', $errors, $basePath);
contains_all($portalController, [
    'PortalClienteSecurity::documentoAutorizadoParaToken',
    'DocumentStorage::download',
    '$arquivo->getMimeType() ?: $arquivo->getClientMimeType()',
], 'PortalClientePublicoController', $ok, $errors);

$webhookController = read_file_checked('app/Http/Controllers/AsaasWebhookController.php', $errors, $basePath);
contains_all($webhookController, [
    'recusarPayloadGrande',
    'Cache::lock',
    'webhook_allow_token_input',
    'HTTP_CONFLICT',
], 'AsaasWebhookController', $ok, $errors);

$services = read_file_checked('config/services.php', $errors, $basePath);
contains_all($services, [
    'webhook_max_payload_kb',
    'webhook_allow_token_input',
    'ASAAS_WEBHOOK_MAX_PAYLOAD_KB',
    'ASAAS_WEBHOOK_ALLOW_TOKEN_INPUT',
], 'config/services.php', $ok, $errors);

$asaasService = read_file_checked('app/Services/AsaasService.php', $errors, $basePath);
contains_all($asaasService, [
    'validarEventoWebhook',
    'resolverAssinaturaDoPagamento',
    'sincronizarEmpresaPorPagamento',
    'pagamentoBloqueiaAcesso',
], 'AsaasService', $ok, $errors);

$phpFiles = [
    'app/Support/DocumentStorage.php',
    'app/Http/Controllers/PortalClientePublicoController.php',
    'app/Http/Controllers/AsaasWebhookController.php',
    'app/Services/AsaasService.php',
    'config/services.php',
];

foreach ($phpFiles as $file) {
    $cmd = 'php -l ' . escapeshellarg($basePath . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $file)) . ' 2>&1';
    exec($cmd, $output, $exitCode);
    if ($exitCode === 0) {
        $ok[] = "Sintaxe PHP válida: {$file}";
    } else {
        $errors[] = "Erro de sintaxe em {$file}: " . implode(' ', $output);
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

exit($errors ? 1 : 0);
