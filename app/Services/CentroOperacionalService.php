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

        $aprovacoes = (clone $base)
            ->whereIn('status', ['aguardando_aprovacao', 'em_aprovacao'])
            ->with(['responsavel:id,nome,user_id', 'empresa:id,razao_social'])
            ->orderByRaw($this->statusEnteredColumn() ? 'COALESCE(status_operacional_at, updated_at) asc' : 'updated_at asc')
            ->limit(8)
            ->get()
            ->map(fn (ItemControle $item): array => $this->taskPayload($item, 'warning'))
            ->values()
            ->toArray();

        $vencidosQuery = (clone $base)
            ->whereNotIn('status', ['concluido', 'aprovado', 'cancelado'])
            ->whereNotNull('data_vencimento')
            ->whereDate('data_vencimento', '<', now()->toDateString());

        $vencidos = (clone $vencidosQuery)
            ->with(['responsavel:id,nome,user_id', 'empresa:id,razao_social'])
            ->orderBy('data_vencimento')
            ->limit(8)
            ->get()
            ->map(fn (ItemControle $item): array => $this->taskPayload($item, 'danger'))
            ->values()
            ->toArray();

        $financeiroQuery = (clone $base)
            ->whereIn('status', ['concluido', 'aprovado', 'assinado'])
            ->when($this->hasColumn('faturado_em'), fn (Builder $query): Builder => $query->whereNull('faturado_em'))
            ->when(! $this->hasColumn('faturado_em'), fn (Builder $query): Builder => $query->where(function (Builder $query): void {
                $query->whereNull('contrato_status')->orWhereNotIn('contrato_status', ['faturado', 'pago']);
            }));

        $totalFinanceiro = (clone $financeiroQuery)->count();

        $financeiro = $financeiroQuery
            ->with(['responsavel:id,nome,user_id', 'empresa:id,razao_social'])
            ->orderByDesc('data_conclusao')
            ->orderByDesc('updated_at')
            ->limit(8)
            ->get()
            ->map(fn (ItemControle $item): array => $this->taskPayload($item, 'success'))
            ->values()
            ->toArray();

        $correcao = (clone $base)
            ->whereIn('status', ['correcao_necessaria', 'reprovado'])
            ->with(['responsavel:id,nome,user_id', 'empresa:id,razao_social'])
            ->orderByRaw($this->statusEnteredColumn() ? 'COALESCE(status_operacional_at, updated_at) asc' : 'updated_at asc')
            ->limit(8)
            ->get()
            ->map(fn (ItemControle $item): array => $this->taskPayload($item, 'danger'))
            ->values()
            ->toArray();

        $blockColumns = $this->blockColumns();

        $bloqueados = empty($blockColumns)
            ? []
            : (clone $base)
                ->where(function (Builder $query) use ($blockColumns): void {
                    foreach ($blockColumns as $column) {
                        $query->orWhere($column, true);
                    }
                })
                ->with(['responsavel:id,nome,user_id', 'empresa:id,razao_social'])
                ->orderByDesc('updated_at')
                ->limit(8)
                ->get()
                ->map(fn (ItemControle $item): array => $this->taskPayload($item, 'warning'))
                ->values()
                ->toArray();

        $workload = ItemControle::query()
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
                return [
                    'name' => $item->responsavel?->nome ?: 'Sem responsável',
                    'total' => (int) $item->total,
                    'percent' => min(100, ((int) $item->total) * 10),
                ];
            })
            ->values()
            ->toArray();

        $totalAbertas = (clone $base)
            ->whereNotIn('status', ['concluido', 'aprovado', 'cancelado'])
            ->count();

        $totalVencidas = (clone $vencidosQuery)->count();

        $totalAprovacao = (clone $base)
            ->whereIn('status', ['aguardando_aprovacao', 'em_aprovacao'])
            ->count();

        $totalCorrecao = (clone $base)
            ->whereIn('status', ['correcao_necessaria', 'reprovado'])
            ->count();

        return [
            'cards' => [
                [
                    'label' => 'Radar de Vencidos',
                    'value' => $totalVencidas,
                    'tone' => $totalVencidas > 0 ? 'danger' : 'success',
                    'hint' => 'Tarefas atrasadas que exigem ação imediata.',
                ],
                [
                    'label' => 'Aprovar Agora',
                    'value' => $totalAprovacao,
                    'tone' => $totalAprovacao > 0 ? 'warning' : 'success',
                    'hint' => 'Itens parados esperando aprovação.',
                ],
                [
                    'label' => 'Precisa de Ajuste',
                    'value' => $totalCorrecao,
                    'tone' => $totalCorrecao > 0 ? 'danger' : 'success',
                    'hint' => 'Itens devolvidos para correção.',
                ],
                [
                    'label' => 'Pendentes de Cobrança',
                    'value' => $totalFinanceiro,
                    'tone' => $totalFinanceiro > 0 ? 'warning' : 'success',
                    'hint' => 'Itens finalizados ainda sem faturamento.',
                ],
            ],
            'sections' => [
                [
                    'title' => '👉 Aprovar Agora',
                    'description' => 'Mostra apenas tarefas com status Aguardando Aprovação / Em aprovação.',
                    'items' => $aprovacoes,
                    'empty' => 'Nada esperando aprovação.',
                ],
                [
                    'title' => '🚨 Radar de Vencidos',
                    'description' => 'O que passou do prazo aparece em vermelho para saltar aos olhos.',
                    'items' => $vencidos,
                    'empty' => 'Nenhuma tarefa vencida.',
                ],
                [
                    'title' => '💰 Pendente de Cobrança',
                    'description' => 'Itens concluídos/aprovados que ainda precisam ser faturados ou pagos.',
                    'items' => $financeiro,
                    'empty' => 'Nada pendente de cobrança.',
                ],
                [
                    'title' => '🛠️ Precisa de Ajuste',
                    'description' => 'Correções que precisam voltar para o fluxo sem se perder.',
                    'items' => $correcao,
                    'empty' => 'Nenhuma correção pendente.',
                ],
            ],
            'bloqueados' => $bloqueados,
            'workload' => $workload,
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
            ->with(['responsavel:id,nome,user_id', 'empresa:id,razao_social']);
    }

    protected function safeSelect(): array
    {
        $columns = [
            'id',
            'titulo',
            'descricao',
            'status',
            'prioridade',
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
