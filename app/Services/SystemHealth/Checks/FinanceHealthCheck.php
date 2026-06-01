<?php

namespace App\Services\SystemHealth\Checks;

use App\Services\SystemHealth\Concerns\BuildsHealthItems;
use App\Services\SystemHealth\HealthCheckContract;
use App\Support\CachedSchema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class FinanceHealthCheck implements HealthCheckContract
{
    use BuildsHealthItems;

    public function key(): string { return 'finance'; }
    public function name(): string { return 'Financeiro / Asaas'; }
    public function description(): string { return 'Configuração Asaas, webhook, assinaturas, pagamentos e empresas usando sem assinatura ativa.'; }

    public function run(int $limit = 500): array
    {
        $items = [];
        $items[] = config('services.asaas.api_key') || env('ASAAS_API_KEY')
            ? $this->ok('ASAAS_API_KEY configurada.')
            : $this->error('ASAAS_API_KEY ausente.', null, [], 'Configure a chave do Asaas no .env/serviços.');

        $items[] = config('services.asaas.webhook_token') || env('ASAAS_WEBHOOK_TOKEN')
            ? $this->ok('ASAAS_WEBHOOK_TOKEN configurado.')
            : $this->warning('ASAAS_WEBHOOK_TOKEN ausente.', null, [], 'Configure o token para validar webhooks recebidos.');

        foreach (['asaas.webhook', 'billing.cancelar'] as $routeName) {
            $items[] = Route::has($routeName)
                ? $this->ok("Rota {$routeName} registrada.")
                : $this->error("Rota {$routeName} ausente.", null, [], 'Registre a rota para fechar o ciclo financeiro.');
        }

        if (CachedSchema::hasTable('assinaturas')) {
            $activeWithoutGateway = $this->countActiveSubscriptionsWithoutGateway();
            $items[] = $activeWithoutGateway > 0
                ? $this->error('Assinaturas ativas sem gateway_subscription_id.', "Total encontrado: {$activeWithoutGateway}.", ['count' => $activeWithoutGateway], 'Reconcilie com o Asaas ou corrija o status local.')
                : $this->ok('Assinaturas ativas possuem gateway_subscription_id.');
        }

        if (CachedSchema::hasTable('pagamentos')) {
            $paidWithoutDate = $this->countPaidPaymentsWithoutDate();
            $items[] = $paidWithoutDate > 0
                ? $this->warning('Pagamentos confirmados sem pago_em.', "Total encontrado: {$paidWithoutDate}.", ['count' => $paidWithoutDate], 'Atualize pago_em com base no evento do Asaas para auditoria financeira.')
                : $this->ok('Pagamentos confirmados possuem data de pagamento.');
        }

        if (CachedSchema::hasTable('empresas') && CachedSchema::hasTable('assinaturas')) {
            $activeCompaniesWithoutSubscription = $this->countActiveCompaniesWithoutConfirmedSubscription();
            $items[] = $activeCompaniesWithoutSubscription > 0
                ? $this->warning('Empresas ativas sem assinatura ativa/confirmada.', "Total encontrado: {$activeCompaniesWithoutSubscription}.", ['count' => $activeCompaniesWithoutSubscription], 'Revise trial, cortesia ou inadimplência antes de bloquear automaticamente.')
                : $this->ok('Empresas ativas possuem assinatura ativa/confirmada.');
        }

        return $items;
    }

    private function countActiveSubscriptionsWithoutGateway(): int
    {
        if (! CachedSchema::hasColumn('assinaturas', 'status') || ! CachedSchema::hasColumn('assinaturas', 'gateway_subscription_id')) {
            return 0;
        }

        return (int) DB::table('assinaturas')
            ->whereIn('status', ['ativa', 'ativo', 'active', 'confirmed'])
            ->where(fn ($query) => $query->whereNull('gateway_subscription_id')->orWhere('gateway_subscription_id', ''))
            ->count();
    }

    private function countPaidPaymentsWithoutDate(): int
    {
        if (! CachedSchema::hasColumn('pagamentos', 'status') || ! CachedSchema::hasColumn('pagamentos', 'pago_em')) {
            return 0;
        }

        return (int) DB::table('pagamentos')
            ->whereIn('status', ['confirmed', 'received', 'pago', 'paid'])
            ->whereNull('pago_em')
            ->count();
    }

    private function countActiveCompaniesWithoutConfirmedSubscription(): int
    {
        return (int) DB::table('empresas as e')
            ->where('e.ativo', 1)
            ->whereNotExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('assinaturas as a')
                    ->whereColumn('a.empresa_id', 'e.id')
                    ->whereIn('a.status', ['ativa', 'ativo', 'active', 'confirmed']);
            })
            ->count();
    }
}
