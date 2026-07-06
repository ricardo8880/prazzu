#!/usr/bin/env php
<?php

$basePath = dirname(__DIR__);
$requiredFiles = [
    'app/Services/AsaasService.php',
    'app/Http/Controllers/AsaasWebhookController.php',
    'app/Services/Financeiro/AsaasWebhookEventRecorder.php',
    'app/Models/AsaasWebhookEvent.php',
    'app/Models/Assinatura.php',
    'app/Models/Pagamento.php',
    'database/sql/lote_10_financeiro_asaas.sql',
];

$requiredEnvKeys = [
    'ASAAS_BASE_URL',
    'ASAAS_API_KEY',
    'ASAAS_WEBHOOK_TOKEN',
];

$errors = [];
$warnings = [];

foreach ($requiredFiles as $file) {
    if (! is_file($basePath . DIRECTORY_SEPARATOR . $file)) {
        $errors[] = "Arquivo obrigatório ausente: {$file}";
    }
}

$controller = @file_get_contents($basePath . '/app/Http/Controllers/AsaasWebhookController.php') ?: '';
foreach (['hash_equals', 'AsaasWebhookEventRecorder', 'duplicate_ignored', 'marcarProcessado', 'marcarFalha'] as $needle) {
    if (! str_contains($controller, $needle)) {
        $errors[] = "Webhook Asaas sem proteção esperada: {$needle}";
    }
}

$service = @file_get_contents($basePath . '/app/Services/AsaasService.php') ?: '';
foreach (['salvarPagamento', 'processarWebhook', 'reconciliarAssinaturasPendentes', 'cancelarAssinatura'] as $needle) {
    if (! str_contains($service, $needle)) {
        $errors[] = "AsaasService não contém método/fluxo esperado: {$needle}";
    }
}

$sql = @file_get_contents($basePath . '/database/sql/lote_10_financeiro_asaas.sql') ?: '';
foreach (['asaas_webhook_events', 'payload_hash', 'UNIQUE KEY', 'gateway_payment_id', 'gateway_subscription_id'] as $needle) {
    if (! str_contains($sql, $needle)) {
        $errors[] = "SQL manual do lote 10 incompleto: {$needle}";
    }
}

$envExample = @file_get_contents($basePath . '/.env.example') ?: '';
if ($envExample !== '') {
    foreach ($requiredEnvKeys as $key) {
        if (! str_contains($envExample, $key)) {
            $warnings[] = ".env.example não documenta {$key}.";
        }
    }
}

$result = [
    'lote' => 10,
    'area' => 'financeiro_asaas',
    'status' => empty($errors) ? 'ok' : 'erro',
    'errors' => $errors,
    'warnings' => $warnings,
    'checked_at' => date(DATE_ATOM),
];

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;

exit(empty($errors) ? 0 : 1);
