<?php

namespace App\Services;


use App\Support\CachedSchema;
use App\Filament\Resources\ItemControles\ItemControleResource;
use App\Models\ItemControle;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class CentroOperacionalService
{
    public function dashboard(?User $user): array
    {
        $base = $this->baseQuery($user);
        $today = now()->toDateString();
        $closedStatuses = ['concluido', 'aprovado', 'cancelado'];

        $openBase = (clone $base)->whereNotIn('status', $closedStatuses);

        $vencidosQuery = (clone $openBase)
            ->whereNotNull('data_vencimento')
            ->whereDate('data_vencimento', '<', $today);

        $vencemHojeQuery = (clone $openBase)
            ->whereNotNull('data_vencimento')
            ->whereDate('data_vencimento', $today);

        $proximosSeteQuery = (clone $openBase)
            ->whereNotNull('data_vencimento')
            ->whereDate('data_vencimento', '>', $today)
            ->whereDate('data_vencimento', '<=', now()->addDays(7)->toDateString());

        $aprovacoesQuery = (clone $base)
            ->whereIn('status', ['aguardando_aprovacao', 'em_aprovacao']);

        $correcaoQuery = (clone $base)
            ->whereIn('status', ['correcao_necessaria', 'reprovado']);

        $financeiroQuery = (clone $base)
            ->whereIn('status', ['concluido', 'aprovado', 'assinado'])
            ->when($this->hasColumn('faturado_em'), fn (Builder $query): Builder => $query->whereNull('faturado_em'))
            ->when(! $this->hasColumn('faturado_em'), fn (Builder $query): Builder => $query->where(function (Builder $query): void {
                $query->whereNull('contrato_status')->orWhereNotIn('contrato_status', ['faturado', 'pago']);
            }));

        $blockColumns = $this->blockColumns();
        $bloqueadosQuery = empty($blockColumns)
            ? null
            : (clone $openBase)->where(function (Builder $query) use ($blockColumns): void {
                foreach ($blockColumns as $column) {
                    $query->orWhere($column, true);
                }
            });

        $semResponsavelQuery = (clone $openBase)->whereNull('responsavel_id');

        $totalVencidas = (clone $vencidosQuery)->count();
        $totalVencemHoje = (clone $vencemHojeQuery)->count();
        $totalAprovacao = (clone $aprovacoesQuery)->count();
        $totalCorrecao = (clone $correcaoQuery)->count();
        $totalFinanceiro = (clone $financeiroQuery)->count();
        $totalBloqueados = $bloqueadosQuery ? (clone $bloqueadosQuery)->count() : 0;
        $totalSemResponsavel = (clone $semResponsavelQuery)->count();
        $totalAbertas = (clone $openBase)->count();
        $totalRisco = (clone $base)
            ->whereNotIn('status', $closedStatuses)
            ->where(function (Builder $query) use ($today, $blockColumns): void {
                $query->where(function (Builder $query) use ($today): void {
                    $query->whereNotNull('data_vencimento')->whereDate('data_vencimento', '<=', $today);
                })->orWhereIn('status', ['correcao_necessaria', 'reprovado']);

                foreach ($blockColumns as $column) {
                    $query->orWhere($column, true);
                }
            })
            ->distinct('empresa_id')
            ->count('empresa_id');

        $vencidos = (clone $vencidosQuery)
            ->orderBy('data_vencimento')
            ->limit(8)
            ->get()
            ->map(fn (ItemControle $item): array => $this->taskPayload($item, 'danger'))
            ->values()
            ->toArray();

        $vencemHoje = (clone $vencemHojeQuery)
            ->orderByRaw("CASE WHEN prioridade IN ('critica', 'urgente') THEN 1 WHEN prioridade = 'alta' THEN 2 ELSE 3 END")
            ->orderBy('data_vencimento')
            ->limit(8)
            ->get()
            ->map(fn (ItemControle $item): array => $this->taskPayload($item, 'warning'))
            ->values()
            ->toArray();

        $aprovacoes = (clone $aprovacoesQuery)
            ->orderByRaw($this->statusEnteredColumn() ? 'COALESCE(status_operacional_at, updated_at) asc' : 'updated_at asc')
            ->limit(8)
            ->get()
            ->map(fn (ItemControle $item): array => $this->taskPayload($item, 'warning'))
            ->values()
            ->toArray();

        $correcao = (clone $correcaoQuery)
            ->orderByRaw($this->statusEnteredColumn() ? 'COALESCE(status_operacional_at, updated_at) asc' : 'updated_at asc')
            ->limit(8)
            ->get()
            ->map(fn (ItemControle $item): array => $this->taskPayload($item, 'danger'))
            ->values()
            ->toArray();

        $financeiro = (clone $financeiroQuery)
            ->orderByDesc('data_conclusao')
            ->orderByDesc('updated_at')
            ->limit(6)
            ->get()
            ->map(fn (ItemControle $item): array => $this->taskPayload($item, 'success'))
            ->values()
            ->toArray();

        $bloqueados = $bloqueadosQuery
            ? (clone $bloqueadosQuery)
                ->orderByDesc('updated_at')
                ->limit(6)
                ->get()
                ->map(fn (ItemControle $item): array => $this->taskPayload($item, 'warning'))
                ->values()
                ->toArray()
            : [];

        $resolverAgora = collect(array_merge($vencidos, $vencemHoje, $aprovacoes, $correcao, $bloqueados))
            ->unique('id')
            ->sortBy(fn (array $item): int => $this->resolverPriority($item))
            ->take(10)
            ->values()
            ->toArray();

        $clientesCriticos = $this->clientesCriticos($user, $blockColumns);
        $vencimentos = $this->vencimentosResumo($user);
        $departamentos = $this->departamentosResumo($user);
        $workload = $this->workload($user);
        $resultadosMes = $this->resultadosMes($user);
        $healthScore = $this->healthScore($totalAbertas, $totalVencidas, $totalVencemHoje, $totalBloqueados, $totalCorrecao, $totalSemResponsavel);

        return [
            'cards' => [
                [
                    'label' => 'Em Risco de Multa',
                    'value' => $totalRisco,
                    'tone' => $totalRisco > 0 ? 'danger' : 'success',
                    'hint' => 'Clientes com prazo crítico, bloqueio ou correção parada.',
                ],
                [
                    'label' => 'Vencem Hoje',
                    'value' => $totalVencemHoje,
                    'tone' => $totalVencemHoje > 0 ? 'warning' : 'success',
                    'hint' => 'Obrigações que ainda podem ser resolvidas hoje.',
                ],
                [
                    'label' => 'Vencidas',
                    'value' => $totalVencidas,
                    'tone' => $totalVencidas > 0 ? 'danger' : 'success',
                    'hint' => 'Itens fora do prazo e com prioridade máxima.',
                ],
                [
                    'label' => 'Aprovações',
                    'value' => $totalAprovacao,
                    'tone' => $totalAprovacao > 0 ? 'warning' : 'success',
                    'hint' => 'Itens aguardando decisão para seguir o fluxo.',
                ],
                [
                    'label' => 'Pendências Financeiras',
                    'value' => $totalFinanceiro,
                    'tone' => $totalFinanceiro > 0 ? 'warning' : 'success',
                    'hint' => 'Entregas finalizadas ainda sem faturamento/pagamento.',
                ],
            ],
            'resolver_agora' => $resolverAgora,
            'clientes_criticos' => $clientesCriticos,
            'vencimentos' => $vencimentos,
            'aprovacoes' => $aprovacoes,
            'financeiro' => $financeiro,
            'bloqueados' => $bloqueados,
            'workload' => $workload,
            'departamentos' => $departamentos,
            'resultados_mes' => $resultadosMes,
            'health_score' => $healthScore,
            'total_abertas' => $totalAbertas,
            'me_mode' => $this->isMeMode($user),
            'missing_columns' => $this->missingRecommendedColumns(),
        ];
    }

    protected function baseQuery(?User $user): Builder
    {
        return ItemControle::query()
            ->visibleForUser($user)
            ->select($this->safeSelect())
            ->with(['responsavel:id,nome,user_id', 'empresa:id,razao_social', 'categoria:id,nome']);
    }

    protected function safeSelect(): array
    {
        $columns = [
            'id',
            'titulo',
            'descricao',
            'status',
            'prioridade',
            'tipo',
            'categoria_id',
            'data_vencimento',
            'data_conclusao',
            'empresa_id',
            'responsavel_id',
            'contrato_valor',
            'contrato_status',
            'created_at',
            'updated_at',
        ];

        foreach ([
                     'urgencia',
                     'valor_tarefa',
                     'bloqueado',
                     'faturado_em',
                     'pago_em',
                     'status_operacional_at',
                     'custom_payload',
                 ] as $column) {
            if ($this->hasColumn($column)) {
                $columns[] = $column;
            }
        }

        foreach ([
                     'blocked_by_dependency',
                     'bloqueado_por_dependencia',
                 ] as $column) {
            if ($this->hasColumn($column)) {
                $columns[] = $column;
            }
        }

        return array_values(array_unique($columns));
    }

    protected function taskPayload(ItemControle $item, string $defaultTone): array
    {
        $statusAt = $this->statusEnteredAt($item);
        $valor = $this->moneyValue($item);
        $blocked = $this->isBlocked($item);

        return [
            'id' => $item->id,
            'title' => $item->titulo,
            'status' => $this->statusLabel((string) $item->status),
            'urgency' => $this->urgencyLabel($item),
            'tone' => $blocked ? 'warning' : $this->toneFor($item, $defaultTone),
            'responsavel' => $item->responsavel?->nome ?: 'Sem responsável',
            'empresa' => $item->empresa?->razao_social ?: 'Sem empresa',
            'due' => $item->data_vencimento?->format('d/m/Y'),
            'stopped_for' => $statusAt
                ? $statusAt->diffForHumans(now(), ['parts' => 2, 'short' => true])
                : $item->updated_at?->diffForHumans(now(), ['parts' => 2, 'short' => true]),
            'description' => filled($item->descricao)
                ? str($item->descricao)->limit(120)->toString()
                : 'Sem descrição cadastrada.',
            'blocked' => $blocked,
            'value' => $valor > 0 ? 'R$ ' . number_format($valor, 2, ',', '.') : null,
            'url' => ItemControleResource::getUrl('edit', ['record' => $item]),
        ];
    }

    protected function statusEnteredAt(ItemControle $item): ?Carbon
    {
        if ($this->statusEnteredColumn() && $item->status_operacional_at) {
            return Carbon::parse($item->status_operacional_at);
        }

        return $item->updated_at;
    }

    protected function statusEnteredColumn(): bool
    {
        return $this->hasColumn('status_operacional_at');
    }

    protected function moneyValue(ItemControle $item): float
    {
        if ($this->hasColumn('valor_tarefa') && filled($item->valor_tarefa)) {
            return (float) $item->valor_tarefa;
        }

        return (float) ($item->contrato_valor ?? 0);
    }

    protected function blockColumns(): array
    {
        return array_values(array_filter([
            'bloqueado',
            'blocked_by_dependency',
            'bloqueado_por_dependencia',
        ], fn (string $column): bool => $this->hasColumn($column)));
    }

    protected function isBlocked(ItemControle $item): bool
    {
        foreach ($this->blockColumns() as $column) {
            if ((bool) ($item->{$column} ?? false)) {
                return true;
            }
        }

        return false;
    }

    protected function toneFor(ItemControle $item, string $defaultTone): string
    {
        if (
            $item->data_vencimento
            && $item->data_vencimento->isPast()
            && ! in_array($item->status, ['concluido', 'aprovado', 'cancelado'], true)
        ) {
            return 'danger';
        }

        if (in_array($item->status, ['concluido', 'aprovado', 'assinado'], true)) {
            return 'success';
        }

        if (in_array($item->status, ['correcao_necessaria', 'reprovado', 'vencido'], true)) {
            return 'danger';
        }

        return $defaultTone;
    }

    protected function urgencyLabel(ItemControle $item): string
    {
        $value = $this->hasColumn('urgencia') && filled($item->urgencia)
            ? $item->urgencia
            : $item->prioridade;

        return match ((string) $value) {
            'baixa' => 'Baixa',
            'media' => 'Média',
            'alta' => 'Alta',
            'critica', 'urgente' => 'Crítica',
            default => 'Média',
        };
    }

    protected function statusLabel(string $status): string
    {
        return match ($status) {
            'pendente' => 'Pendente',
            'pronto' => 'Pronto',
            'em_revisao' => 'Em Revisão',
            'aguardando_aprovacao', 'em_aprovacao' => 'Aguardando Aprovação',
            'correcao_necessaria', 'reprovado' => 'Correção Necessária',
            'em_andamento' => 'Em andamento',
            'aprovado' => 'Aprovado',
            'assinado' => 'Assinado',
            'concluido' => 'Concluído',
            'cancelado' => 'Cancelado',
            'vencido' => 'Vencido',
            default => ucfirst(str_replace('_', ' ', $status)),
        };
    }

    protected function resolverPriority(array $item): int
    {
        $tone = $item['tone'] ?? 'info';
        $status = $item['status'] ?? '';
        $urgency = $item['urgency'] ?? '';

        if (($item['blocked'] ?? false) || $tone === 'danger' || $urgency === 'Crítica') {
            return 1;
        }

        if (str_contains($status, 'Aprovação') || $tone === 'warning') {
            return 2;
        }

        return 3;
    }

    protected function clientesCriticos(?User $user, array $blockColumns): array
    {
        $today = now()->toDateString();

        return ItemControle::query()
            ->visibleForUser($user)
            ->select($this->safeSelect())
            ->with(['responsavel:id,nome,user_id', 'empresa:id,razao_social', 'categoria:id,nome'])
            ->whereNotIn('status', ['concluido', 'aprovado', 'cancelado'])
            ->where(function (Builder $query) use ($today, $blockColumns): void {
                $query->where(function (Builder $query) use ($today): void {
                    $query->whereNotNull('data_vencimento')->whereDate('data_vencimento', '<=', $today);
                })->orWhereIn('status', ['correcao_necessaria', 'reprovado']);

                foreach ($blockColumns as $column) {
                    $query->orWhere($column, true);
                }
            })
            ->orderBy('data_vencimento')
            ->limit(24)
            ->get()
            ->groupBy(fn (ItemControle $item): string => (string) ($item->empresa_id ?: 'sem_empresa_' . $item->id))
            ->map(function ($items): array {
                /** @var ItemControle $first */
                $first = $items->first();
                $criticalCount = $items->filter(fn (ItemControle $item): bool => $this->toneFor($item, 'warning') === 'danger' || $this->isBlocked($item))->count();
                $risk = $criticalCount >= 2 ? 'Crítico' : ($criticalCount === 1 ? 'Alto' : 'Médio');

                return [
                    'cliente' => $first->empresa?->razao_social ?: 'Sem empresa vinculada',
                    'problema' => $this->clientProblemLabel($first, $items->count()),
                    'responsavel' => $first->responsavel?->nome ?: 'Sem responsável',
                    'dias' => $this->daysRemainingLabel($first),
                    'risco' => $risk,
                    'tone' => $risk === 'Crítico' ? 'danger' : ($risk === 'Alto' ? 'warning' : 'info'),
                    'url' => ItemControleResource::getUrl('edit', ['record' => $first]),
                ];
            })
            ->sortBy(fn (array $row): int => match ($row['risco']) {
                'Crítico' => 1,
                'Alto' => 2,
                default => 3,
            })
            ->take(6)
            ->values()
            ->toArray();
    }

    protected function vencimentosResumo(?User $user): array
    {
        $base = ItemControle::query()
            ->visibleForUser($user)
            ->whereNotIn('status', ['concluido', 'aprovado', 'cancelado'])
            ->whereNotNull('data_vencimento');

        return [
            ['label' => 'Hoje', 'value' => (clone $base)->whereDate('data_vencimento', now()->toDateString())->count(), 'tone' => 'warning'],
            ['label' => '7 dias', 'value' => (clone $base)->whereDate('data_vencimento', '>', now()->toDateString())->whereDate('data_vencimento', '<=', now()->addDays(7)->toDateString())->count(), 'tone' => 'info'],
            ['label' => '15 dias', 'value' => (clone $base)->whereDate('data_vencimento', '>', now()->toDateString())->whereDate('data_vencimento', '<=', now()->addDays(15)->toDateString())->count(), 'tone' => 'info'],
            ['label' => '30 dias', 'value' => (clone $base)->whereDate('data_vencimento', '>', now()->toDateString())->whereDate('data_vencimento', '<=', now()->addDays(30)->toDateString())->count(), 'tone' => 'success'],
        ];
    }

    protected function departamentosResumo(?User $user): array
    {
        return ItemControle::query()
            ->visibleForUser($user)
            ->select($this->safeSelect())
            ->with(['categoria:id,nome'])
            ->whereNotIn('status', ['concluido', 'aprovado', 'cancelado'])
            ->limit(250)
            ->get()
            ->groupBy(fn (ItemControle $item): string => $this->departmentLabel($item))
            ->map(fn ($items, string $label): array => [
                'label' => $label,
                'value' => $items->count(),
                'tone' => $items->where('data_vencimento', '<', now()->startOfDay())->count() > 0 ? 'danger' : 'info',
            ])
            ->sortByDesc('value')
            ->take(5)
            ->values()
            ->toArray();
    }

    protected function workload(?User $user): array
    {
        return ItemControle::query()
            ->visibleForUser($user)
            ->select('responsavel_id')
            ->selectRaw('COUNT(item_controles.id) as total')
            ->whereNotIn('status', ['concluido', 'aprovado', 'cancelado'])
            ->whereNotNull('responsavel_id')
            ->with(['responsavel:id,nome'])
            ->groupBy('responsavel_id')
            ->orderByDesc('total')
            ->limit(8)
            ->get()
            ->map(function (ItemControle $item): array {
                $total = (int) $item->total;
                $percent = min(140, $total * 8);

                return [
                    'name' => $item->responsavel?->nome ?: 'Sem responsável',
                    'total' => $total,
                    'percent' => min(100, $percent),
                    'status' => $percent > 100 ? 'Sobrecarregado' : ($percent >= 80 ? 'Atenção' : 'Normal'),
                    'tone' => $percent > 100 ? 'danger' : ($percent >= 80 ? 'warning' : 'success'),
                ];
            })
            ->values()
            ->toArray();
    }

    protected function resultadosMes(?User $user): array
    {
        $start = now()->startOfMonth()->toDateString();
        $end = now()->endOfMonth()->toDateString();

        $concluidos = ItemControle::query()
            ->visibleForUser($user)
            ->whereIn('status', ['concluido', 'aprovado', 'assinado'])
            ->where(function (Builder $query) use ($start, $end): void {
                $query->whereBetween('data_conclusao', [$start, $end])
                    ->orWhereBetween('updated_at', [now()->startOfMonth(), now()->endOfMonth()]);
            })
            ->count();

        $vencidosMes = ItemControle::query()
            ->visibleForUser($user)
            ->whereNotIn('status', ['concluido', 'aprovado', 'cancelado'])
            ->whereBetween('data_vencimento', [$start, $end])
            ->whereDate('data_vencimento', '<', now()->toDateString())
            ->count();

        $sla = $concluidos + $vencidosMes > 0
            ? max(0, round(($concluidos / max(1, $concluidos + $vencidosMes)) * 100))
            : 100;

        return [
            ['label' => 'Prazos concluídos', 'value' => $concluidos, 'hint' => 'no mês'],
            ['label' => 'Aprovações realizadas', 'value' => ItemControle::query()->visibleForUser($user)->whereIn('status', ['aprovado', 'assinado'])->whereBetween('updated_at', [now()->startOfMonth(), now()->endOfMonth()])->count(), 'hint' => 'no mês'],
            ['label' => 'Multas registradas', 'value' => $vencidosMes, 'hint' => 'risco operacional'],
            ['label' => 'SLA', 'value' => $sla . '%', 'hint' => 'estimado'],
        ];
    }

    protected function healthScore(int $totalAbertas, int $totalVencidas, int $totalHoje, int $totalBloqueados, int $totalCorrecao, int $totalSemResponsavel): array
    {
        $score = 100;
        $score -= min(35, $totalVencidas * 7);
        $score -= min(20, $totalHoje * 2);
        $score -= min(20, $totalBloqueados * 5);
        $score -= min(15, $totalCorrecao * 3);
        $score -= min(10, $totalSemResponsavel * 2);
        $score = max(0, $score);

        return [
            'value' => $score,
            'label' => $score >= 90 ? 'Excelente' : ($score >= 70 ? 'Bom' : ($score >= 50 ? 'Atenção' : 'Crítico')),
            'tone' => $score >= 90 ? 'success' : ($score >= 70 ? 'info' : ($score >= 50 ? 'warning' : 'danger')),
            'hint' => $totalAbertas . ' item(ns) aberto(s) monitorados.',
        ];
    }

    protected function clientProblemLabel(ItemControle $item, int $total): string
    {
        $prefix = $total > 1 ? $total . ' pendências críticas' : $item->titulo;

        if ($this->isBlocked($item)) {
            return $prefix . ' • bloqueio ativo';
        }

        if ($item->data_vencimento && $item->data_vencimento->isPast()) {
            return $prefix . ' • prazo vencido';
        }

        if (in_array($item->status, ['correcao_necessaria', 'reprovado'], true)) {
            return $prefix . ' • precisa de correção';
        }

        return $prefix;
    }

    protected function daysRemainingLabel(ItemControle $item): string
    {
        if (! $item->data_vencimento) {
            return 'Sem prazo';
        }

        $days = now()->startOfDay()->diffInDays($item->data_vencimento->startOfDay(), false);

        if ($days < 0) {
            return abs((int) $days) . 'd atraso';
        }

        if ((int) $days === 0) {
            return 'Hoje';
        }

        return (int) $days . 'd';
    }

    protected function departmentLabel(ItemControle $item): string
    {
        $value = strtolower((string) ($item->categoria?->nome ?: $item->tipo ?: $item->titulo));

        return match (true) {
            str_contains($value, 'fiscal'), str_contains($value, 'nota'), str_contains($value, 'sped'), str_contains($value, 'defis'), str_contains($value, 'reinf') => 'Fiscal',
            str_contains($value, 'folha'), str_contains($value, 'dp'), str_contains($value, 'trabalh') => 'DP',
            str_contains($value, 'contrato'), str_contains($value, 'societ') => 'Societário',
            str_contains($value, 'finance'), str_contains($value, 'cobran'), str_contains($value, 'fatura') => 'Financeiro',
            str_contains($value, 'contáb'), str_contains($value, 'contab') => 'Contábil',
            default => $item->categoria?->nome ?: 'Operacional',
        };
    }

    protected function isMeMode(?User $user): bool
    {
        return $user?->isUser() === true;
    }

    protected function missingRecommendedColumns(): array
    {
        return array_values(array_filter([
            'urgencia',
            'valor_tarefa',
            'bloqueado',
            'faturado_em',
            'pago_em',
            'status_operacional_at',
        ], fn (string $column): bool => ! $this->hasColumn($column)));
    }

    protected function hasColumn(string $column): bool
    {
        static $cache = [];

        return $cache[$column] ??= CachedSchema::hasColumn('item_controles', $column);
    }
}
