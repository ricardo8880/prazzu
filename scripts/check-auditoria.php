<?php

$root = dirname(__DIR__);

$requiredFiles = [
    'app/Services/AuditoriaManualService.php',
    'app/Services/AuditoriaTrailService.php',
    'app/Services/AuditoriaDetalhadaService.php',
    'app/Observers/AuditoriaGlobalObserver.php',
    'app/Providers/AuditoriaServiceProvider.php',
    'config/auditoria.php',
];

$missingFiles = [];
foreach ($requiredFiles as $file) {
    if (! is_file($root . DIRECTORY_SEPARATOR . $file)) {
        $missingFiles[] = $file;
    }
}

$config = is_file($root . '/config/auditoria.php') ? file_get_contents($root . '/config/auditoria.php') : '';
$service = is_file($root . '/app/Services/AuditoriaManualService.php') ? file_get_contents($root . '/app/Services/AuditoriaManualService.php') : '';
$trail = is_file($root . '/app/Services/AuditoriaTrailService.php') ? file_get_contents($root . '/app/Services/AuditoriaTrailService.php') : '';

$eventosEsperados = [
    'auditoria.exported',
    'login.success',
    'login.failed',
    'logout',
    'password.reset',
    'portal_cliente.login.failed',
    'portal_cliente.logout',
    'portal_cliente.password.reset_requested',
    'portal_cliente.password.reset_success',
    'portal_cliente.invite.accepted',
    'portal_cliente.documento.download',
    'portal_cliente.documento.link_externo_acessado',
    'portal_item.assinatura.registrada',
    'portal_item.mensagem.enviada',
    'portal_item.documento.enviado',
    'asaas.webhook.rejected',
    'asaas.webhook.duplicate_ignored',
    'asaas.webhook.received',
    'asaas.webhook.processed',
    'asaas.webhook.failed',
];

$allPhp = '';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/app'));
foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $allPhp .= "\n" . file_get_contents($file->getPathname());
    }
}

$missingConfigEvents = [];
$missingCodeEvents = [];
foreach ($eventosEsperados as $evento) {
    if (! str_contains($config, "'{$evento}'")) {
        $missingConfigEvents[] = $evento;
    }

    if (! str_contains($allPhp, "'{$evento}'")) {
        $missingCodeEvents[] = $evento;
    }
}

$checks = [
    'arquivos_obrigatorios_presentes' => $missingFiles === [],
    'catalogo_eventos_configurado' => $missingConfigEvents === [],
    'eventos_criticos_referenciados_no_codigo' => $missingCodeEvents === [],
    'mascara_campos_sensiveis' => str_contains($service, 'mascararSensivel') && str_contains($config, 'sensitive_fields'),
    'contexto_http_enriquecido' => str_contains($service, 'enriquecerContexto') && str_contains($service, 'request_id'),
    'servico_trilha_centralizado' => str_contains($trail, 'portalCliente') && str_contains($trail, 'financeiro') && str_contains($trail, 'documento'),
    'asaas_webhook_excluido_auditoria_global' => str_contains($config, 'AsaasWebhookEvent::class') && str_contains($config, "'asaas_webhook_events'"),
];

$result = [
    'ok' => ! in_array(false, $checks, true),
    'checks' => $checks,
    'missing_files' => $missingFiles,
    'missing_config_events' => $missingConfigEvents,
    'missing_code_events' => $missingCodeEvents,
    'observacao' => 'Este check é estático e não depende do Artisan, útil enquanto o ambiente sem ext-mbstring não executa comandos Laravel.',
];

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;

exit($result['ok'] ? 0 : 1);
