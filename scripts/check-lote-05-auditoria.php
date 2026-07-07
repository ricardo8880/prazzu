<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$service = file_get_contents($root . '/app/Services/AuditoriaDetalhadaService.php') ?: '';
$config = file_get_contents($root . '/config/auditoria.php') ?: '';
$provider = file_get_contents($root . '/bootstrap/providers.php') ?: '';

$requiredEvents = [
    'item_controle.status.changed',
    'item_controle.vencimento.changed',
    'item_controle.responsavel.changed',
    'item_controle.sla.changed',
    'item_controle.aprovacao.status.changed',
    'item_controle.anexo.uploaded',
    'item_controle.anexo.deleted',
    'portal_documento.uploaded',
    'portal_documento.deleted',
    'permissao.changed',
    'financeiro.pagamento.status.changed',
    'financeiro.pagamento.paid_at.changed',
    'financeiro.assinatura.status.changed',
    'financeiro.assinatura.cancelled_at.changed',
];

$requiredFields = [
    'item_controles.status',
    'item_controles.data_vencimento',
    'item_controles.responsavel_id',
    'item_controle_aprovacoes.status',
    'prazzu_permissions.name',
    'prazzu_user_permissions.permission_id',
    'pagamentos.status',
    'pagamentos.pago_em',
    'assinaturas.status',
    'assinaturas.cancelado_em',
];

$missing = [];

foreach ($requiredEvents as $event) {
    if (! str_contains($service, $event) || ! str_contains($config, $event)) {
        $missing[] = "Evento crítico não encontrado no service/config: {$event}";
    }
}

foreach ($requiredFields as $field) {
    if (! str_contains($service, $field)) {
        $missing[] = "Campo crítico sem mapeamento: {$field}";
    }
}

foreach ([
    'registrarEventosCriticosDoModel',
    'eventoCriticoParaCampo',
    'registrarEventoCritico',
    'normalizarSnapshot',
] as $method) {
    if (! str_contains($service, $method)) {
        $missing[] = "Método obrigatório ausente: {$method}";
    }
}

if (! str_contains($provider, 'AuditoriaServiceProvider::class')) {
    $missing[] = 'AuditoriaServiceProvider não está registrado em bootstrap/providers.php';
}

if (! str_contains($provider, 'AuditoriaEventosManuaisServiceProvider::class')) {
    $missing[] = 'AuditoriaEventosManuaisServiceProvider não está registrado em bootstrap/providers.php';
}

$result = [
    'ok' => $missing === [],
    'checked_events' => count($requiredEvents),
    'checked_fields' => count($requiredFields),
    'missing' => $missing,
];

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;

exit($missing === [] ? 0 : 1);
