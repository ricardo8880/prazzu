<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class FinanceiroModuleData
{
    public const PAID_STATUSES = ['RECEIVED', 'CONFIRMED', 'RECEIVED_IN_CASH', 'PAID'];
    public const OPEN_STATUSES = ['PENDING', 'CREATED', 'PAYMENT_CREATED', 'UNPAID'];
    public const OVERDUE_STATUSES = ['OVERDUE', 'PAYMENT_OVERDUE'];
    public const ACTIVE_SUBSCRIPTION_STATUSES = ['ACTIVE', 'RECEIVED', 'CONFIRMED'];

    public static function dashboard(?string $search = null, ?string $period = '30'): array
    {
        if (! self::hasTable('pagamentos')) {
            return self::emptyState('A tabela pagamentos ainda não existe.');
        }

        $days = max(7, min((int) ($period ?: 30), 365));
        $start = now()->subDays($days)->startOfDay();
        $end = now()->endOfDay();

        $base = self::paymentsQuery($search);
        $periodQuery = self::paymentsQuery($search)->whereBetween('pagamentos.vencimento', [$start->toDateString(), $end->toDateString()]);

        $totalReceived = (float) (clone $periodQuery)->whereIn('pagamentos.status', self::PAID_STATUSES)->sum('pagamentos.valor');
        $totalOpen = (float) (clone $periodQuery)->whereIn('pagamentos.status', array_merge(self::OPEN_STATUSES, self::OVERDUE_STATUSES))->sum('pagamentos.valor');
        $overdue = (float) self::paymentsQuery($search)
            ->where(function ($query) {
                $query->whereIn('pagamentos.status', self::OVERDUE_STATUSES)
                    ->orWhere(function ($q) {
                        $q->whereNotIn('pagamentos.status', self::PAID_STATUSES)
                            ->whereDate('pagamentos.vencimento', '<', now()->toDateString());
                    });
            })
            ->sum('pagamentos.valor');

        $mrr = self::hasTable('assinaturas')
            ? (float) self::subscriptionsQuery($search)->whereIn('assinaturas.status', self::ACTIVE_SUBSCRIPTION_STATUSES)->sum('assinaturas.valor')
            : 0.0;

        return [
            'metrics' => [
                ['label' => 'Recebido no período', 'value' => self::money($totalReceived), 'hint' => 'Pagamentos confirmados nos últimos '.$days.' dias', 'tone' => 'success'],
                ['label' => 'Em aberto', 'value' => self::money($totalOpen), 'hint' => 'Cobranças pendentes ou vencidas no período', 'tone' => 'warning'],
                ['label' => 'Vencido', 'value' => self::money($overdue), 'hint' => 'Valor que precisa de ação de cobrança', 'tone' => 'danger'],
                ['label' => 'Receita recorrente', 'value' => self::money($mrr), 'hint' => 'Soma das assinaturas ativas', 'tone' => 'info'],
            ],
            'cashflow' => self::cashflow($search, 6),
            'topClients' => self::topClients($search),
            'alerts' => self::financialAlerts($search),
            'recentPayments' => self::payments($search, 'all', 8),
            'emptyMessage' => null,
        ];
    }

    public static function charges(?string $search = null, string $status = 'all', int $limit = 50): array
    {
        if (! self::hasTable('pagamentos')) {
            return self::emptyState('A tabela pagamentos ainda não existe.');
        }

        $payments = self::payments($search, $status, $limit);

        $openValue = (float) self::paymentsQuery($search)
            ->whereIn('pagamentos.status', array_merge(self::OPEN_STATUSES, self::OVERDUE_STATUSES))
            ->sum('pagamentos.valor');
        $overdueCount = self::paymentsQuery($search)
            ->where(function ($query) {
                $query->whereIn('pagamentos.status', self::OVERDUE_STATUSES)
                    ->orWhere(function ($q) {
                        $q->whereNotIn('pagamentos.status', self::PAID_STATUSES)
                            ->whereDate('pagamentos.vencimento', '<', now()->toDateString());
                    });
            })->count();
        $dueSoon = self::paymentsQuery($search)
            ->whereNotIn('pagamentos.status', self::PAID_STATUSES)
            ->whereBetween('pagamentos.vencimento', [now()->toDateString(), now()->addDays(7)->toDateString()])
            ->count();
        $receivedMonth = (float) self::paymentsQuery($search)
            ->whereIn('pagamentos.status', self::PAID_STATUSES)
            ->whereBetween('pagamentos.pago_em', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('pagamentos.valor');

        return [
            'metrics' => [
                ['label' => 'Em aberto', 'value' => self::money($openValue), 'hint' => 'Pendentes e vencidas', 'tone' => 'warning'],
                ['label' => 'Vencidas', 'value' => (string) $overdueCount, 'hint' => 'Cobranças que precisam de follow-up', 'tone' => 'danger'],
                ['label' => 'Vencem em 7 dias', 'value' => (string) $dueSoon, 'hint' => 'Acompanhar antes de atrasar', 'tone' => 'info'],
                ['label' => 'Recebido no mês', 'value' => self::money($receivedMonth), 'hint' => 'Entradas confirmadas', 'tone' => 'success'],
            ],
            'payments' => $payments,
            'alerts' => self::chargeAlerts($search),
            'emptyMessage' => $payments->isEmpty() ? 'Nenhuma cobrança encontrada com os filtros atuais.' : null,
        ];
    }

    public static function subscriptions(?string $search = null, string $status = 'all', int $limit = 50): array
    {
        if (! self::hasTable('assinaturas')) {
            return self::emptyState('A tabela assinaturas ainda não existe.');
        }

        $query = self::subscriptionsQuery($search);

        if ($status === 'active') {
            $query->whereIn('assinaturas.status', self::ACTIVE_SUBSCRIPTION_STATUSES);
        } elseif ($status === 'paused') {
            $query->whereIn('assinaturas.status', ['PAUSED', 'SUSPENDED']);
        } elseif ($status === 'canceled') {
            $query->whereIn('assinaturas.status', ['CANCELED', 'CANCELLED', 'INACTIVE']);
        } elseif ($status === 'renewal') {
            $query->whereBetween('assinaturas.proximo_vencimento', [now()->toDateString(), now()->addDays(15)->toDateString()]);
        }

        $subscriptions = $query
            ->orderByRaw('assinaturas.proximo_vencimento IS NULL')
            ->orderBy('assinaturas.proximo_vencimento')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => self::formatSubscription($row));

        $active = self::subscriptionsQuery($search)->whereIn('assinaturas.status', self::ACTIVE_SUBSCRIPTION_STATUSES);
        $activeCount = (clone $active)->count();
        $mrr = (float) (clone $active)->sum('assinaturas.valor');
        $renewals = self::subscriptionsQuery($search)->whereBetween('assinaturas.proximo_vencimento', [now()->toDateString(), now()->addDays(15)->toDateString()])->count();
        $canceled = self::subscriptionsQuery($search)->whereIn('assinaturas.status', ['CANCELED', 'CANCELLED', 'INACTIVE'])->count();

        return [
            'metrics' => [
                ['label' => 'Assinaturas ativas', 'value' => (string) $activeCount, 'hint' => 'Clientes com recorrência ativa', 'tone' => 'success'],
                ['label' => 'Receita recorrente', 'value' => self::money($mrr), 'hint' => 'Soma mensal dos planos ativos', 'tone' => 'info'],
                ['label' => 'Renovam em 15 dias', 'value' => (string) $renewals, 'hint' => 'Próximos vencimentos', 'tone' => 'warning'],
                ['label' => 'Canceladas', 'value' => (string) $canceled, 'hint' => 'Histórico de cancelamentos', 'tone' => 'danger'],
            ],
            'subscriptions' => $subscriptions,
            'emptyMessage' => $subscriptions->isEmpty() ? 'Nenhuma assinatura encontrada com os filtros atuais.' : null,
        ];
    }

    public static function payment(int $id): ?array
    {
        if (! self::hasTable('pagamentos')) {
            return null;
        }

        $row = self::paymentsQuery()->where('pagamentos.id', $id)->first();

        return $row ? self::formatPayment($row) : null;
    }

    public static function subscription(int $id): ?array
    {
        if (! self::hasTable('assinaturas')) {
            return null;
        }

        $row = self::subscriptionsQuery()->where('assinaturas.id', $id)->first();

        return $row ? self::formatSubscription($row) : null;
    }

    public static function markPaymentAsPaid(int $id): bool
    {
        if (! self::hasTable('pagamentos')) {
            return false;
        }

        return DB::table('pagamentos')->where('id', $id)->update([
            'status' => 'RECEIVED',
            'pago_em' => now(),
            'updated_at' => now(),
        ]) > 0;
    }

    public static function markPaymentAsPending(int $id): bool
    {
        if (! self::hasTable('pagamentos')) {
            return false;
        }

        return DB::table('pagamentos')->where('id', $id)->update([
            'status' => 'PENDING',
            'pago_em' => null,
            'updated_at' => now(),
        ]) > 0;
    }

    public static function updateSubscriptionStatus(int $id, string $status): bool
    {
        if (! self::hasTable('assinaturas')) {
            return false;
        }

        $payload = ['status' => $status, 'updated_at' => now()];

        if (in_array($status, ['CANCELED', 'CANCELLED', 'INACTIVE'], true) && self::hasColumn('assinaturas', 'cancelado_em')) {
            $payload['cancelado_em'] = now();
        }

        if (in_array($status, self::ACTIVE_SUBSCRIPTION_STATUSES, true) && self::hasColumn('assinaturas', 'cancelado_em')) {
            $payload['cancelado_em'] = null;
        }

        return DB::table('assinaturas')->where('id', $id)->update($payload) > 0;
    }

    private static function payments(?string $search = null, string $status = 'all', int $limit = 50)
    {
        $query = self::paymentsQuery($search);

        if ($status === 'open') {
            $query->whereIn('pagamentos.status', self::OPEN_STATUSES);
        } elseif ($status === 'paid') {
            $query->whereIn('pagamentos.status', self::PAID_STATUSES);
        } elseif ($status === 'overdue') {
            $query->where(function ($query) {
                $query->whereIn('pagamentos.status', self::OVERDUE_STATUSES)
                    ->orWhere(function ($q) {
                        $q->whereNotIn('pagamentos.status', self::PAID_STATUSES)
                            ->whereDate('pagamentos.vencimento', '<', now()->toDateString());
                    });
            });
        } elseif ($status === 'due_soon') {
            $query->whereNotIn('pagamentos.status', self::PAID_STATUSES)
                ->whereBetween('pagamentos.vencimento', [now()->toDateString(), now()->addDays(7)->toDateString()]);
        }

        return $query
            ->orderByRaw('pagamentos.vencimento IS NULL')
            ->orderBy('pagamentos.vencimento')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => self::formatPayment($row));
    }

    private static function paymentsQuery(?string $search = null): Builder
    {
        $query = DB::table('pagamentos')
            ->leftJoin('empresas', 'empresas.id', '=', 'pagamentos.empresa_id')
            ->leftJoin('assinaturas', 'assinaturas.id', '=', 'pagamentos.assinatura_id')
            ->select([
                'pagamentos.*',
                'empresas.razao_social',
                'empresas.nome_fantasia',
                'empresas.email as empresa_email',
                'empresas.telefone as empresa_telefone',
                'assinaturas.plano as assinatura_plano',
                'assinaturas.ciclo as assinatura_ciclo',
            ]);

        self::applyTenantScope($query, 'pagamentos.empresa_id');

        if (filled($search)) {
            $term = '%'.Str::lower(trim($search)).'%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(COALESCE(empresas.razao_social, "")) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(COALESCE(empresas.nome_fantasia, "")) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(COALESCE(empresas.email, "")) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(COALESCE(pagamentos.gateway_payment_id, "")) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(COALESCE(assinaturas.plano, "")) LIKE ?', [$term]);
            });
        }

        return $query;
    }

    private static function subscriptionsQuery(?string $search = null): Builder
    {
        $query = DB::table('assinaturas')
            ->leftJoin('empresas', 'empresas.id', '=', 'assinaturas.empresa_id')
            ->select([
                'assinaturas.*',
                'empresas.razao_social',
                'empresas.nome_fantasia',
                'empresas.email as empresa_email',
                'empresas.telefone as empresa_telefone',
                'empresas.plano as empresa_plano',
            ]);

        self::applyTenantScope($query, 'assinaturas.empresa_id');

        if (filled($search)) {
            $term = '%'.Str::lower(trim($search)).'%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(COALESCE(empresas.razao_social, "")) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(COALESCE(empresas.nome_fantasia, "")) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(COALESCE(empresas.email, "")) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(COALESCE(assinaturas.plano, "")) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(COALESCE(assinaturas.gateway_subscription_id, "")) LIKE ?', [$term]);
            });
        }

        return $query;
    }

    private static function applyTenantScope(Builder $query, string $column): void
    {
        $user = auth()->user();

        if (! $user || blank($user->empresa_id ?? null)) {
            return;
        }

        if (($user->role ?? null) === 'super_admin') {
            return;
        }

        $query->where($column, $user->empresa_id);
    }

    private static function formatPayment(object $row): array
    {
        $due = filled($row->vencimento ?? null) ? Carbon::parse($row->vencimento) : null;
        $paid = filled($row->pago_em ?? null) ? Carbon::parse($row->pago_em) : null;
        $status = strtoupper((string) ($row->status ?? 'PENDING'));
        $isPaid = in_array($status, self::PAID_STATUSES, true);
        $isOverdue = ! $isPaid && $due && $due->isPast() && ! $due->isToday();

        return [
            'id' => (int) $row->id,
            'empresa_id' => (int) ($row->empresa_id ?? 0),
            'cliente' => $row->nome_fantasia ?: ($row->razao_social ?: 'Cliente não informado'),
            'email' => $row->empresa_email ?? null,
            'telefone' => $row->empresa_telefone ?? null,
            'plano' => self::planLabel($row->assinatura_plano ?? null),
            'ciclo' => self::cycleLabel($row->assinatura_ciclo ?? null),
            'valor' => (float) ($row->valor ?? 0),
            'valor_formatado' => self::money((float) ($row->valor ?? 0)),
            'status' => $status,
            'status_label' => self::paymentStatusLabel($status, $isOverdue),
            'status_tone' => $isPaid ? 'success' : ($isOverdue ? 'danger' : 'warning'),
            'vencimento' => $due?->format('d/m/Y'),
            'vencimento_raw' => $row->vencimento ?? null,
            'pago_em' => $paid?->format('d/m/Y H:i'),
            'dias' => $due ? now()->startOfDay()->diffInDays($due->copy()->startOfDay(), false) : null,
            'invoice_url' => $row->invoice_url ?? null,
            'gateway_payment_id' => $row->gateway_payment_id ?? null,
            'billing_type' => $row->billing_type ?? null,
            'is_paid' => $isPaid,
            'is_overdue' => $isOverdue,
        ];
    }

    private static function formatSubscription(object $row): array
    {
        $due = filled($row->proximo_vencimento ?? null) ? Carbon::parse($row->proximo_vencimento) : null;
        $canceledAt = filled($row->cancelado_em ?? null) ? Carbon::parse($row->cancelado_em) : null;
        $status = strtoupper((string) ($row->status ?? 'PENDING'));
        $isActive = in_array($status, self::ACTIVE_SUBSCRIPTION_STATUSES, true);
        $renewsSoon = $isActive && $due && $due->betweenIncluded(now()->startOfDay(), now()->addDays(15)->endOfDay());

        return [
            'id' => (int) $row->id,
            'empresa_id' => (int) ($row->empresa_id ?? 0),
            'cliente' => $row->nome_fantasia ?: ($row->razao_social ?: 'Cliente não informado'),
            'email' => $row->empresa_email ?? null,
            'telefone' => $row->empresa_telefone ?? null,
            'plano' => self::planLabel($row->plano ?? null),
            'valor' => (float) ($row->valor ?? 0),
            'valor_formatado' => self::money((float) ($row->valor ?? 0)),
            'ciclo' => self::cycleLabel($row->ciclo ?? null),
            'status' => $status,
            'status_label' => self::subscriptionStatusLabel($status),
            'status_tone' => $isActive ? 'success' : (in_array($status, ['CANCELED', 'CANCELLED', 'INACTIVE'], true) ? 'danger' : 'warning'),
            'proximo_vencimento' => $due?->format('d/m/Y'),
            'dias_para_vencer' => $due ? now()->startOfDay()->diffInDays($due->copy()->startOfDay(), false) : null,
            'cancelado_em' => $canceledAt?->format('d/m/Y H:i'),
            'gateway' => $row->gateway ?? 'asaas',
            'gateway_subscription_id' => $row->gateway_subscription_id ?? null,
            'is_active' => $isActive,
            'renews_soon' => $renewsSoon,
        ];
    }

    private static function cashflow(?string $search, int $months): array
    {
        $items = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $start = $date->copy()->startOfMonth()->toDateString();
            $end = $date->copy()->endOfMonth()->toDateString();

            $received = (float) self::paymentsQuery($search)
                ->whereIn('pagamentos.status', self::PAID_STATUSES)
                ->whereBetween('pagamentos.pago_em', [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()])
                ->sum('pagamentos.valor');
            $expected = (float) self::paymentsQuery($search)
                ->whereBetween('pagamentos.vencimento', [$start, $end])
                ->sum('pagamentos.valor');

            $items[] = [
                'label' => $date->translatedFormat('M/y'),
                'recebido' => self::money($received),
                'previsto' => self::money($expected),
                'percent' => $expected > 0 ? min(100, round(($received / $expected) * 100)) : 0,
            ];
        }

        return $items;
    }

    private static function topClients(?string $search)
    {
        return self::paymentsQuery($search)
            ->whereIn('pagamentos.status', self::PAID_STATUSES)
            ->select([
                'pagamentos.empresa_id',
                'empresas.razao_social',
                'empresas.nome_fantasia',
                'empresas.email as empresa_email',
            ])
            ->selectRaw('SUM(pagamentos.valor) as total, COUNT(pagamentos.id) as total_pagamentos')
            ->groupBy('pagamentos.empresa_id', 'empresas.razao_social', 'empresas.nome_fantasia', 'empresas.email')
            ->orderByDesc('total')
            ->limit(6)
            ->get()
            ->map(fn ($row) => [
                'cliente' => $row->nome_fantasia ?: ($row->razao_social ?: 'Cliente não informado'),
                'email' => $row->empresa_email,
                'total' => self::money((float) $row->total),
                'total_pagamentos' => (int) $row->total_pagamentos,
            ]);
    }

    private static function financialAlerts(?string $search): array
    {
        $alerts = [];
        $overdue = self::charges($search, 'overdue', 3)['payments'] ?? collect();
        foreach ($overdue as $payment) {
            $alerts[] = ['tone' => 'danger', 'title' => 'Cobrança vencida', 'text' => $payment['cliente'].' · '.$payment['valor_formatado'].' · venc. '.$payment['vencimento']];
        }

        $renewals = self::subscriptions($search, 'renewal', 3)['subscriptions'] ?? collect();
        foreach ($renewals as $subscription) {
            $alerts[] = ['tone' => 'warning', 'title' => 'Renovação próxima', 'text' => $subscription['cliente'].' · '.$subscription['plano'].' · '.$subscription['proximo_vencimento']];
        }

        return $alerts;
    }

    private static function chargeAlerts(?string $search): array
    {
        return self::payments($search, 'overdue', 5)
            ->map(fn ($payment) => ['tone' => 'danger', 'title' => $payment['cliente'], 'text' => $payment['valor_formatado'].' vencido em '.$payment['vencimento']])
            ->values()
            ->all();
    }

    private static function emptyState(string $message): array
    {
        return [
            'metrics' => [],
            'payments' => collect(),
            'subscriptions' => collect(),
            'cashflow' => [],
            'topClients' => collect(),
            'alerts' => [],
            'recentPayments' => collect(),
            'emptyMessage' => $message,
        ];
    }

    public static function money(float $value): string
    {
        return 'R$ '.number_format($value, 2, ',', '.');
    }

    private static function paymentStatusLabel(string $status, bool $overdue = false): string
    {
        if ($overdue) {
            return 'Vencida';
        }

        return match ($status) {
            'RECEIVED', 'CONFIRMED', 'RECEIVED_IN_CASH', 'PAID' => 'Recebida',
            'OVERDUE', 'PAYMENT_OVERDUE' => 'Vencida',
            'REFUNDED' => 'Reembolsada',
            'CANCELED', 'CANCELLED' => 'Cancelada',
            default => 'Pendente',
        };
    }

    private static function subscriptionStatusLabel(string $status): string
    {
        return match ($status) {
            'ACTIVE', 'RECEIVED', 'CONFIRMED' => 'Ativa',
            'PAUSED', 'SUSPENDED' => 'Pausada',
            'CANCELED', 'CANCELLED', 'INACTIVE' => 'Cancelada',
            default => 'Pendente',
        };
    }

    private static function planLabel(?string $plan): string
    {
        return Str::of($plan ?: 'Sem plano')->replace('_', ' ')->title()->toString();
    }

    private static function cycleLabel(?string $cycle): string
    {
        return match (strtoupper((string) $cycle)) {
            'MONTHLY' => 'Mensal',
            'YEARLY', 'ANNUAL' => 'Anual',
            'WEEKLY' => 'Semanal',
            default => $cycle ? Str::of($cycle)->replace('_', ' ')->title()->toString() : 'Sem ciclo',
        };
    }

    private static function hasTable(string $table): bool
    {
        try {
            return CachedSchema::hasTable($table);
        } catch (\Throwable) {
            return false;
        }
    }

    private static function hasColumn(string $table, string $column): bool
    {
        try {
            return CachedSchema::hasColumn($table, $column);
        } catch (\Throwable) {
            return false;
        }
    }
}
