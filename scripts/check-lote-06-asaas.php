#!/usr/bin/env php
<?php

$basePath = dirname(__DIR__);
$errors = [];

$requiredFiles = [
    'app/Services/AsaasService.php',
    'app/Http/Controllers/AsaasWebhookController.php',
    'app/Services/Financeiro/AsaasWebhookEventRecorder.php',
    'app/Models/AsaasWebhookEvent.php',
    'app/Models/Assinatura.php',
    'app/Models/Pagamento.php',
    'config/services.php',
    'database/sql/lote_01_alinhamento_schema_manual.sql',
];

foreach ($requiredFiles as $file) {
    if (! is_file($basePath . DIRECTORY_SEPARATOR . $file)) {
        $errors[] = "Arquivo obrigatório ausente: {$file}";
    }
}

$service = @file_get_contents($basePath . '/app/Services/AsaasService.php') ?: '';
$controller = @file_get_contents($basePath . '/app/Http/Controllers/AsaasWebhookController.php') ?: '';
$recorder = @file_get_contents($basePath . '/app/Services/Financeiro/AsaasWebhookEventRecorder.php') ?: '';
$config = @file_get_contents($basePath . '/config/services.php') ?: '';
$sql = @file_get_contents($basePath . '/database/sql/lote_01_alinhamento_schema_manual.sql') ?: '';

$serviceChecks = [
    'validarEventoWebhook',
    'tentarReconciliarAssinaturaDoPagamento',
    'processarWebhookPagamento',
    'processarWebhookAssinatura',
    'salvarPagamento',
    'updateOrCreate',
    'pagamentoConfirmaAcesso',
    'pagamentoBloqueiaAcesso',
    'bloquearEmpresaPorAssinaturaCancelada',
    'reconciliarAssinaturasPendentes',
    'cancelarAssinatura',
    'consultarAssinatura($subscriptionId)',
];

foreach ($serviceChecks as $needle) {
    if (! str_contains($service, $needle)) {
        $errors[] = "AsaasService sem implementação esperada: {$needle}";
    }
}

$controllerChecks = [
    'hash_equals',
    'AsaasWebhookEventRecorder',
    'duplicate_ignored',
    'marcarProcessado',
    'marcarFalha',
    'InvalidArgumentException',
    'HTTP_UNPROCESSABLE_ENTITY',
    'asaas.webhook.rejected',
    'asaas.webhook.failed',
];

foreach ($controllerChecks as $needle) {
    if (! str_contains($controller, $needle)) {
        $errors[] = "Webhook controller sem proteção esperada: {$needle}";
    }
}

$recorderChecks = [
    'payload_hash',
    'firstOrNew',
    'estaProcessado',
    'attempts',
    'marcarProcessado',
    'marcarFalha',
];

foreach ($recorderChecks as $needle) {
    if (! str_contains($recorder, $needle)) {
        $errors[] = "Recorder de idempotência incompleto: {$needle}";
    }
}

$configChecks = [
    'ASAAS_BASE_URL',
    'ASAAS_API_KEY',
    'ASAAS_WEBHOOK_TOKEN',
    'webhook_events',
    'PAYMENT_CONFIRMED',
    'PAYMENT_OVERDUE',
    'SUBSCRIPTION_DELETED',
];

foreach ($configChecks as $needle) {
    if (! str_contains($config, $needle)) {
        $errors[] = "Configuração Asaas incompleta: {$needle}";
    }
}

$sqlChecks = [
    'asaas_webhook_events',
    'payload_hash',
    'gateway_payment_id',
    'gateway_subscription_id',
];

foreach ($sqlChecks as $needle) {
    if (! str_contains($sql, $needle)) {
        $errors[] = "SQL manual base incompleto para Asaas: {$needle}";
    }
}

$modelAssinatura = @file_get_contents($basePath . '/app/Models/Assinatura.php') ?: '';
$modelPagamento = @file_get_contents($basePath . '/app/Models/Pagamento.php') ?: '';

if (! str_contains($modelAssinatura, "protected \$table = 'assinaturas'")) {
    $errors[] = 'Model Assinatura não aponta para tabela assinaturas.';
}

if (! str_contains($modelPagamento, "protected \$table = 'pagamentos'")) {
    $errors[] = 'Model Pagamento não aponta para tabela pagamentos.';
}

$result = [
    'lote' => 6,
    'area' => 'asaas',
    'ok' => empty($errors),
    'errors' => $errors,
    'checked_at' => date(DATE_ATOM),
];

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;

exit(empty($errors) ? 0 : 1);
