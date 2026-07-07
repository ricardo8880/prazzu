<?php

declare(strict_types=1);

/**
 * Lote 8 - Homologacao final Prazzu
 *
 * Check estatico e sem bootstrap do Laravel para validar se os lotes 1 a 7
 * ficaram integrados o suficiente para homologacao ponta a ponta.
 * Nao depende de mbstring, banco, queue worker ou .env valido.
 */

$root = dirname(__DIR__);
$errors = [];
$warnings = [];

$read = static function (string $relative) use ($root): string {
    $path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);

    return is_file($path) ? (string) file_get_contents($path) : '';
};

$exists = static function (string $relative) use ($root): bool {
    return is_file($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative));
};

$mustExist = static function (array $files, string $context) use (&$errors, $exists): void {
    foreach ($files as $file) {
        if (! $exists($file)) {
            $errors[] = sprintf('[%s] Arquivo ausente: %s', $context, $file);
        }
    }
};

$mustContain = static function (string $file, array $needles, string $context) use (&$errors, $read): void {
    $content = $read($file);
    if ($content === '') {
        $errors[] = sprintf('[%s] Nao foi possivel ler: %s', $context, $file);
        return;
    }

    foreach ($needles as $needle) {
        if (! str_contains($content, $needle)) {
            $errors[] = sprintf('[%s] %s nao contem marcador obrigatorio: %s', $context, $file, $needle);
        }
    }
};

$mustAnyContain = static function (string $file, array $needles, string $context) use (&$errors, $read): void {
    $content = $read($file);
    if ($content === '') {
        $errors[] = sprintf('[%s] Nao foi possivel ler: %s', $context, $file);
        return;
    }

    foreach ($needles as $needle) {
        if (str_contains($content, $needle)) {
            return;
        }
    }

    $errors[] = sprintf('[%s] %s nao contem nenhum marcador esperado: %s', $context, $file, implode(' | ', $needles));
};

$mustNotContain = static function (string $file, array $needles, string $context) use (&$errors, $read): void {
    $content = $read($file);
    if ($content === '') {
        $errors[] = sprintf('[%s] Nao foi possivel ler: %s', $context, $file);
        return;
    }

    foreach ($needles as $needle) {
        if (str_contains(strtolower($content), strtolower($needle))) {
            $errors[] = sprintf('[%s] %s contem instrucao proibida para homologacao segura: %s', $context, $file, $needle);
        }
    }
};

// Lote 1 - Banco manual.
$mustExist([
    'database/sql/lote_01_alinhamento_schema_manual.sql',
    'database/sql/lote_07_filas_scheduler_notificacoes.sql',
], 'banco');

$mustContain('database/sql/lote_01_alinhamento_schema_manual.sql', [
    'asaas_webhook_events',
    'ai_market_sources',
    'ai_market_comments',
    'ai_product_improvement_resolutions',
    'tamanho_bytes',
], 'banco');

$mustContain('database/sql/lote_07_filas_scheduler_notificacoes.sql', [
    'jobs',
    'failed_jobs',
], 'filas-banco');

foreach ([
    'database/sql/lote_01_alinhamento_schema_manual.sql',
    'database/sql/lote_07_filas_scheduler_notificacoes.sql',
] as $sqlFile) {
    $mustNotContain($sqlFile, ['drop table', 'drop column', 'truncate table'], 'sql-seguro');
}

// Lote 2 - Permissoes Filament contra acesso direto.
$pagesProtected = [
    'app/Filament/Pages/WhiteLabel.php',
    'app/Filament/Pages/GestaoDocumentalEnterprise.php',
    'app/Filament/Pages/SlaPrazos.php',
    'app/Filament/Pages/CentroOperacional.php',
    'app/Filament/Pages/DashboardExecutivoContabil.php',
    'app/Filament/Pages/Kanban.php',
    'app/Filament/Pages/Projetos.php',
    'app/Filament/Pages/Calendario.php',
];
$mustExist($pagesProtected, 'permissoes');
foreach ($pagesProtected as $page) {
    $mustContain($page, ['canAccess'], 'permissoes');
    $mustAnyContain($page, ['PrazzuPermissionService', 'PrazzuAccessControl', 'auth()->user()', 'auth()->check()'], 'permissoes');
}

// Lote 3 - Portal do Cliente.
$mustExist([
    'app/Http/Controllers/PortalClientePublicoController.php',
    'app/Http/Controllers/PortalItemControleController.php',
    'scripts/check-portal-cliente.php',
], 'portal');

$mustContain('routes/web.php', [
    'portal/cliente',
    'PortalClientePublicoController',
    'PortalItemControleController',
], 'portal-rotas');

$mustContain('app/Http/Controllers/PortalClientePublicoController.php', [
    'token',
    'expirado',
    'empresa_id',
], 'portal-token-isolamento');

$mustContain('app/Http/Controllers/PortalItemControleController.php', [
    'empresa_id',
    'store',
], 'portal-upload');
$mustContain('app/Http/Controllers/PortalClientePublicoController.php', [
    'downloadDocumento',
    'DocumentStorage::download',
], 'portal-download');

// Lote 4 - SLA, pendencias e responsaveis.
$mustExist([
    'app/Services/ItemControleOperationalService.php',
    'app/Filament/Pages/Pendencias.php',
    'scripts/check-lote-04-operacional.php',
], 'operacional');

$mustContain('app/Services/ItemControleOperationalService.php', [
    'alterarResponsavel',
    'alterarStatus',
    'alterarPrazo',
], 'operacional-service');

$mustContain('app/Filament/Pages/Pendencias.php', [
    'ItemControleOperationalService',
    'responsavel',
    'vencimento',
], 'pendencias-page');

// Lote 5 - Auditoria.
$mustExist([
    'app/Services/AuditoriaDetalhadaService.php',
    'config/auditoria.php',
    'scripts/check-lote-05-auditoria.php',
], 'auditoria');

$mustContain('config/auditoria.php', [
    'item_controle.status.changed',
    'item_controle.vencimento.changed',
    'item_controle.responsavel.changed',
    'portal_documento.uploaded',
    'permissao.changed',
    'financeiro.pagamento.status.changed',
], 'auditoria-eventos');

$mustContain('app/Services/AuditoriaDetalhadaService.php', [
    'registrar',
    'evento',
    'snapshot',
], 'auditoria-service');

// Lote 6 - Asaas.
$mustExist([
    'app/Services/AsaasService.php',
    'app/Http/Controllers/AsaasWebhookController.php',
    'scripts/check-lote-06-asaas.php',
], 'asaas');

$mustContain('app/Services/AsaasService.php', [
    'criarAssinatura',
    'reconciliarAssinatura',
    'bloquearEmpresaPorAssinaturaCancelada',
    'ativarEmpresaPorAssinatura',
], 'asaas-service');

$mustContain('app/Http/Controllers/AsaasWebhookController.php', [
    'AsaasWebhookEvent',
    'event_id',
    'hash',
], 'asaas-webhook-idempotencia');

// Lote 7 - Scheduler, filas e notificacoes.
$mustExist([
    'app/Notifications/ItemControleVencimentoNotification.php',
    'app/Console/Commands/MonitorarFilasPrazzu.php',
    'app/Console/Commands/ReprocessarFilasPrazzu.php',
    'scripts/check-lote-07-scheduler-filas.php',
], 'scheduler-filas');

$mustContain('app/Notifications/ItemControleVencimentoNotification.php', [
    'ShouldQueue',
    'Queueable',
], 'notificacao-queue');

$mustContain('routes/console.php', [
    'item-controle:notificar-vencimentos',
    'prazzu:filas-monitorar',
    'prazzu:filas-reprocessar',
], 'scheduler-rotas');

// Lote 8 - Artefatos de homologacao final.
$mustExist([
    'database/sql/lote_08_homologacao_validacao.sql',
    'docs/lotes/lote_08_homologacao_final.md',
], 'homologacao-final');

$mustContain('database/sql/lote_08_homologacao_validacao.sql', [
    'VALIDACAO_LOTE_01_BANCO',
    'VALIDACAO_LOTE_03_PORTAL',
    'VALIDACAO_LOTE_06_ASAAS',
    'VALIDACAO_LOTE_07_FILAS',
], 'sql-validacao-final');

$mustContain('docs/lotes/lote_08_homologacao_final.md', [
    'Ponta a ponta',
    'Multiempresa',
    'Permissoes',
    'Portal do Cliente',
    'Asaas',
    'Criterio de aceite final',
], 'doc-homologacao-final');

// Sintaxe PHP dos arquivos alterados nos lotes 3 a 8, sem depender do Laravel.
$phpFilesToLint = [
    'scripts/check-lote-08-homologacao-final.php',
    'app/Http/Controllers/PortalClientePublicoController.php',
    'app/Http/Controllers/PortalItemControleController.php',
    'app/Services/ItemControleOperationalService.php',
    'app/Services/AuditoriaDetalhadaService.php',
    'app/Services/AsaasService.php',
    'app/Http/Controllers/AsaasWebhookController.php',
    'app/Notifications/ItemControleVencimentoNotification.php',
    'app/Console/Commands/MonitorarFilasPrazzu.php',
    'app/Console/Commands/ReprocessarFilasPrazzu.php',
];

foreach ($phpFilesToLint as $relative) {
    if (! $exists($relative)) {
        continue;
    }

    $cmd = sprintf('php -l %s 2>&1', escapeshellarg($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative)));
    $output = [];
    $exitCode = 0;
    exec($cmd, $output, $exitCode);
    if ($exitCode !== 0) {
        $errors[] = sprintf('[php-lint] %s: %s', $relative, implode(' ', $output));
    }
}

$result = [
    'ok' => $errors === [],
    'errors' => $errors,
    'warnings' => $warnings,
    'checked_at' => date(DATE_ATOM),
    'scope' => [
        'banco_manual_sem_migrations',
        'permissoes_filament',
        'portal_cliente',
        'sla_pendencias_responsaveis',
        'auditoria',
        'asaas',
        'scheduler_filas_notificacoes',
        'homologacao_final',
    ],
];

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL;

exit($result['ok'] ? 0 : 1);
