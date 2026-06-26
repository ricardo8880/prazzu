<?php

namespace App\Support;

use App\Filament\Pages\CentroOperacional;
use App\Filament\Pages\ControleCobrancas;
use App\Filament\Pages\Documentos;
use App\Filament\Pages\SlaPrazos;
use App\Filament\Resources\ItemControles\ItemControleResource;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class DashboardExecutivoContabilData
{
    private const DONE_STATUSES = ['concluido', 'concluído', 'finalizado', 'finalizada', 'aprovado', 'aprovada', 'pago', 'paid', 'resolvido', 'fechado', 'cancelado'];
    private const BILLING_PAID_STATUSES = ['pago', 'paid', 'recebido', 'confirmed', 'received', 'liquidado', 'cancelado'];

    public function data(): array
    {
        $decisionCards = $this->decisionCards();
        $risk = $this->riskSummary($decisionCards);

        return [
            'updated_at' => now()->format('d/m/Y H:i'),
            'risk' => $risk,
            'top' => $this->top($decisionCards, $risk),

            // Compatibilidade com a view atual. No lote 2 a Blade será simplificada para usar decision_cards.
            'metrics' => $decisionCards,
            'focus' => $this->executiveFocus($decisionCards),
            'sections' => $this->sections(),
            'insights' => [],
            'principles' => $this->principles(),

            // Nova estrutura executiva que será consumida pela interface reformulada nos próximos lotes.
            'decision_cards' => $decisionCards,
            'resolve_now' => $this->resolveNowRows(),
            'blockers' => $this->blockerRows(),
            'trend' => $this->riskTrend($risk),
        ];
    }

    private function decisionCards(): array
    {
        $obrigacoesCriticas = $this->criticalMonthlyObligationsCount();
        $slaEmRisco = $this->slaRiskCount();
        $documentosBloqueando = $this->documentBlockerCount();
        $inadimplenciaComImpacto = $this->delinquencyWithOperationalImpactCount();
        $valorVencidoComImpacto = $this->overdueBillingValueWithOperationalImpact();

        return [
            [
                'key' => 'obrigacoes_criticas',
                'label' => 'Obrigações críticas',
                'value' => $this->formatNumber($obrigacoesCriticas),
                'raw' => $obrigacoesCriticas,
                'hint' => 'Vencidas, vencendo nos próximos 7 dias ou com alto risco no mês atual',
                'icon' => '📌',
                'action_label' => 'Abrir origem',
                'source_label' => 'Centro Operacional',
                'priority' => 1,
                'tone' => $obrigacoesCriticas > 0 ? 'danger' : 'success',
                'url' => $this->safeUrl(CentroOperacional::class) ?: $this->safeUrl(ItemControleResource::class),
            ],
            [
                'key' => 'sla_em_risco',
                'label' => 'SLA em risco',
                'value' => $this->formatNumber($slaEmRisco),
                'raw' => $slaEmRisco,
                'hint' => 'SLA vencido ou estourando nas próximas 12 horas',
                'icon' => '🚨',
                'action_label' => 'Atacar SLA',
                'source_label' => 'SLA e Prazos',
                'priority' => 2,
                'tone' => $slaEmRisco > 0 ? 'danger' : 'success',
                'url' => $this->safeUrl(SlaPrazos::class) ?: $this->safeUrl(CentroOperacional::class),
            ],
            [
                'key' => 'documentos_bloqueando',
                'label' => 'Documentos bloqueando entrega',
                'value' => $this->formatNumber($documentosBloqueando),
                'raw' => $documentosBloqueando,
                'hint' => 'Pendências documentais que impedem obrigação, aprovação, assinatura ou entrega próxima',
                'icon' => '📄',
                'action_label' => 'Destravar',
                'source_label' => 'Documentos',
                'priority' => 3,
                'tone' => $documentosBloqueando > 0 ? 'warning' : 'success',
                'url' => $this->safeUrl(Documentos::class) ?: $this->safeUrl(ItemControleResource::class),
            ],
            [
                'key' => 'inadimplencia_com_impacto',
                'label' => 'Inadimplência com impacto',
                'value' => $this->formatNumber($inadimplenciaComImpacto),
                'raw' => $inadimplenciaComImpacto,
                'hint' => $valorVencidoComImpacto > 0
                    ? 'R$ ' . number_format($valorVencidoComImpacto, 2, ',', '.') . ' vencidos em clientes com operação aberta'
                    : 'Sem cliente inadimplente bloqueando decisão operacional',
                'icon' => '💸',
                'action_label' => 'Decidir cobrança',
                'source_label' => 'Cobranças',
                'priority' => 4,
                'tone' => $inadimplenciaComImpacto > 0 ? 'danger' : 'success',
                'url' => $this->safeUrl(ControleCobrancas::class),
            ],
        ];
    }

    private function top(array $cards, array $risk): array
    {
        $bottleneck = $this->mainBottleneck($cards);

        if ($bottleneck['raw'] > 0) {
            return [
                'eyebrow' => 'Cockpit Executivo Contábil',
                'primary_action' => $bottleneck['action_label'] ?? 'Abrir origem',
                'primary_url' => $bottleneck['url'] ?? null,
                'summary' => 'O maior risco agora é: ' . mb_strtolower($bottleneck['label']) . '.',
                'next_step' => 'Resolva primeiro o gargalo principal. Detalhamento, execução e relatórios continuam nas abas próprias.',
                'badge' => 'Gargalo: ' . $bottleneck['label'],
                'tone' => $bottleneck['tone'] ?? $risk['tone'],
            ];
        }

        return [
            'eyebrow' => 'Cockpit Executivo Contábil',
            'primary_action' => 'Ver operação',
            'primary_url' => $this->safeUrl(CentroOperacional::class) ?: $this->safeUrl(ItemControleResource::class),
            'summary' => 'Nenhum risco executivo crítico localizado agora.',
            'next_step' => 'Use esta tela para decisão rápida; use as abas específicas para gestão detalhada.',
            'badge' => 'Sem gargalo crítico',
            'tone' => 'success',
        ];
    }

    private function riskSummary(array $cards): array
    {
        $raw = collect($cards)->keyBy('key')->map(fn (array $card): int => (int) ($card['raw'] ?? 0));

        $critical = ($raw->get('obrigacoes_criticas', 0) * 3)
            + ($raw->get('sla_em_risco', 0) * 3)
            + ($raw->get('documentos_bloqueando', 0) * 2)
            + ($raw->get('inadimplencia_com_impacto', 0) * 2);

        $score = max(0, min(100, 100 - min(100, $critical * 6)));
        $count = $raw->sum();

        if ($score < 55 || $raw->get('obrigacoes_criticas', 0) + $raw->get('sla_em_risco', 0) >= 5) {
            return [
                'label' => 'Alto risco operacional',
                'headline' => 'Há risco real de atraso, multa, quebra de SLA ou desgaste com cliente.',
                'tone' => 'danger',
                'score' => $score,
                'count' => $count,
            ];
        }

        if ($count > 0 || $score < 90) {
            return [
                'label' => 'Atenção necessária hoje',
                'headline' => 'A rotina está ativa, mas existe decisão operacional que não deve esperar relatório.',
                'tone' => 'warning',
                'score' => $score,
                'count' => $count,
            ];
        }

        return [
            'label' => 'Escritório sob controle',
            'headline' => 'Nenhum risco executivo crítico localizado neste momento.',
            'tone' => 'success',
            'score' => 100,
            'count' => 0,
        ];
    }

    private function executiveFocus(array $cards): array
    {
        $bottleneck = $this->mainBottleneck($cards);
        $nextPenalty = $this->nextPenaltyRow();
        $nextComplaint = $this->nextComplaintRiskRow();

        return array_values(array_filter([
            [
                'title' => 'Gargalo do escritório',
                'value' => (int) ($bottleneck['raw'] ?? 0),
                'tone' => $bottleneck['raw'] > 0 ? ($bottleneck['tone'] ?? 'warning') : 'success',
                'description' => $bottleneck['raw'] > 0
                    ? 'O maior foco executivo agora é: ' . mb_strtolower($bottleneck['label']) . '.'
                    : 'Nenhum gargalo crítico localizado agora.',
                'action' => $bottleneck['action_label'] ?? 'Abrir origem',
                'url' => $bottleneck['url'] ?? null,
            ],
            $nextPenalty ? [
                'title' => 'Próxima multa possível',
                'value' => 1,
                'tone' => $nextPenalty['tone'] ?? 'danger',
                'description' => ($nextPenalty['title'] ?? 'Obrigação crítica') . ' • ' . ($nextPenalty['description'] ?? 'Vencimento crítico próximo.'),
                'action' => $nextPenalty['action_label'] ?? 'Abrir item',
                'url' => $nextPenalty['url'] ?? null,
            ] : null,
            $nextComplaint ? [
                'title' => 'Próximo cliente que pode reclamar',
                'value' => 1,
                'tone' => $nextComplaint['tone'] ?? 'warning',
                'description' => ($nextComplaint['title'] ?? 'Cliente em risco') . ' • ' . ($nextComplaint['meta'] ?? 'risco operacional'),
                'action' => $nextComplaint['action_label'] ?? 'Abrir origem',
                'url' => $nextComplaint['url'] ?? null,
            ] : null,
        ]));
    }

    private function sections(): array
    {
        return [
            [
                'key' => 'resolver_agora',
                'title' => 'Resolver agora',
                'description' => 'No máximo 5 itens que podem gerar multa, atraso, quebra de SLA ou desgaste com cliente se forem ignorados hoje.',
                'empty_title' => 'Nada crítico para resolver agora.',
                'empty_description' => 'Não encontrei vencidos, SLA crítico ou obrigação de alto risco para hoje.',
                'items' => $this->resolveNowRows(),
            ],
            [
                'key' => 'bloqueios_operacionais',
                'title' => 'Bloqueios que travam entrega',
                'description' => 'Itens que dependem de documento, aprovação, assinatura, cliente ou desbloqueio de fluxo. O dashboard aponta; a aba de origem resolve.',
                'empty_title' => 'Nenhum bloqueio acionável.',
                'empty_description' => 'Não encontrei documento, aprovação, assinatura ou dependência travando entrega próxima.',
                'items' => $this->blockerRows(),
            ],
            [
                'key' => 'obrigacoes_criticas_mes',
                'title' => 'Obrigações críticas do mês',
                'description' => 'Resumo executivo dos próximos vencimentos críticos do mês. Não substitui calendário, checklist ou Centro Operacional.',
                'empty_title' => 'Nenhuma obrigação crítica no mês.',
                'empty_description' => 'Não encontrei obrigação aberta de alto risco, vencida ou vencendo nos próximos dias.',
                'items' => $this->criticalMonthlyObligationRows(),
            ],
        ];
    }

    private function principles(): array
    {
        return [
            'Não repetir Clientes, Financeiro, Documentos, Calendário, Kanban, Gantt, Centro Operacional ou Relatórios.',
            'Cada número precisa gerar decisão rápida: agir agora, cobrar, destravar ou acompanhar na aba correta.',
            'Volume genérico fica fora; entram somente riscos com impacto executivo.',
            'O dashboard é cockpit, não tela de gestão detalhada.',
        ];
    }

    private function mainBottleneck(array $cards): array
    {
        $weights = [
            'obrigacoes_criticas' => 4,
            'sla_em_risco' => 4,
            'documentos_bloqueando' => 3,
            'inadimplencia_com_impacto' => 2,
        ];

        return collect($cards)
            ->map(function (array $card) use ($weights): array {
                $card['weighted_score'] = ((int) ($card['raw'] ?? 0)) * ($weights[$card['key']] ?? 1);
                return $card;
            })
            ->sortByDesc('weighted_score')
            ->first() ?? ['raw' => 0, 'label' => 'Sem gargalo', 'tone' => 'success', 'url' => null];
    }

    private function riskTrend(array $risk): array
    {
        $currentCritical = 100 - (int) ($risk['score'] ?? 100);
        $previousCritical = $this->previousPeriodCriticalScore();
        $delta = $previousCritical > 0 ? $currentCritical - $previousCritical : 0;
        $score = (int) ($risk['score'] ?? 100);

        return [
            'label' => 'Controle operacional',
            'value' => $score . '%',
            'current_score' => $score,
            'delta' => $delta,
            'tone' => $delta > 0 ? 'danger' : ($delta < 0 ? 'success' : 'info'),
            'description' => $previousCritical > 0
                ? ($delta > 0 ? 'Risco aumentou em relação ao período anterior.' : ($delta < 0 ? 'Risco caiu em relação ao período anterior.' : 'Risco está estável.'))
                : 'Ainda não há base histórica suficiente para comparar tendência.',
            'evidence' => $previousCritical > 0
                ? 'Comparado com a janela anterior de 15 dias.'
                : 'A tendência aparece quando houver histórico operacional suficiente.',
        ];
    }

    private function previousPeriodCriticalScore(): int
    {
        if (! $this->hasTable('item_controles')) {
            return 0;
        }

        $start = now()->subDays(30)->startOfDay();
        $end = now()->subDays(15)->endOfDay();

        return (int) $this->itemsBase()
            ->whereBetween('item_controles.updated_at', [$start, $end])
            ->where(function (Builder $query): void {
                $query->whereNotNull('item_controles.data_vencimento')
                    ->whereDate('item_controles.data_vencimento', '<', now()->subDays(15)->toDateString());

                if ($this->hasColumn('item_controles', 'sla_limite_em')) {
                    $query->orWhere('item_controles.sla_limite_em', '<', now()->subDays(15));
                }
            })
            ->whereNotIn('item_controles.status', self::DONE_STATUSES)
            ->count() * 10;
    }

    private function resolveNowRows(): array
    {
        if (! $this->hasTable('item_controles')) {
            return [];
        }

        return $this->itemsBase()
            ->whereNotIn('item_controles.status', self::DONE_STATUSES)
            ->where(function (Builder $query): void {
                $query->where(function (Builder $dateQuery): void {
                    $dateQuery->whereNotNull('item_controles.data_vencimento')
                        ->whereDate('item_controles.data_vencimento', '<=', now()->toDateString());
                });

                if ($this->hasColumn('item_controles', 'sla_limite_em')) {
                    $query->orWhere(function (Builder $slaQuery): void {
                        $slaQuery->whereNotNull('item_controles.sla_limite_em')
                            ->where('item_controles.sla_limite_em', '<=', now()->addHours(12))
                            ->where(function (Builder $doneQuery): void {
                                $doneQuery->whereNull('item_controles.sla_concluido_em')
                                    ->orWhere('item_controles.sla_concluido_em', '');
                            });
                    });
                }

                $this->orWhereHighRisk($query);
            })
            ->orderByRaw('CASE WHEN item_controles.data_vencimento IS NULL THEN 1 ELSE 0 END')
            ->orderBy('item_controles.data_vencimento')
            ->limit(5)
            ->get()
            ->map(fn (object $item): array => $this->itemRow($item, null, 'Resolver agora'))
            ->all();
    }

    private function blockerRows(): array
    {
        if (! $this->hasTable('item_controles')) {
            return [];
        }

        return $this->itemsBase()
            ->whereNotIn('item_controles.status', self::DONE_STATUSES)
            ->where(function (Builder $query): void {
                $this->whereBlockerCondition($query);
            })
            ->where(function (Builder $query): void {
                $query->whereNull('item_controles.data_vencimento')
                    ->orWhereDate('item_controles.data_vencimento', '<=', now()->addDays(15)->toDateString());
                $this->orWhereHighRisk($query);
            })
            ->orderByRaw('CASE WHEN item_controles.data_vencimento IS NULL THEN 1 ELSE 0 END')
            ->orderBy('item_controles.data_vencimento')
            ->limit(5)
            ->get()
            ->map(fn (object $item): array => $this->itemRow($item, 'Bloqueio', 'Abrir origem'))
            ->all();
    }

    private function criticalMonthlyObligationRows(): array
    {
        if (! $this->hasTable('item_controles')) {
            return [];
        }

        return $this->criticalMonthlyObligationsQuery()
            ->orderBy('item_controles.data_vencimento')
            ->limit(5)
            ->get()
            ->map(fn (object $item): array => $this->itemRow($item, 'Obrigação', 'Abrir obrigação'))
            ->all();
    }

    private function nextPenaltyRow(): ?array
    {
        $rows = $this->criticalMonthlyObligationRows();

        return $rows[0] ?? null;
    }

    private function nextComplaintRiskRow(): ?array
    {
        $rows = $this->clientRiskRows();

        return $rows[0] ?? null;
    }

    private function clientRiskRows(): array
    {
        if (! $this->hasTable('empresas')) {
            return [];
        }

        $rows = DB::table('empresas')
            ->select('id', 'razao_social', 'nome_fantasia')
            ->when($this->hasColumn('empresas', 'ativo'), fn (Builder $query) => $query->where('ativo', 1))
            ->when($this->empresaId(), fn (Builder $query, int $empresaId) => $query->where('id', $empresaId))
            ->orderByDesc('updated_at')
            ->limit(40)
            ->get();

        return $rows->map(function (object $empresa): array {
            $criticalObligations = $this->criticalMonthlyObligationsCount($empresa->id);
            $sla = $this->slaRiskCount($empresa->id);
            $blockers = $this->documentBlockerCount($empresa->id);
            $billing = $this->overdueBillingCount($empresa->id);
            $score = ($criticalObligations * 5) + ($sla * 4) + ($blockers * 3) + ($billing * 2);

            $reasons = array_values(array_filter([
                $criticalObligations > 0 ? $criticalObligations . ' obrigação(ões) crítica(s)' : null,
                $sla > 0 ? $sla . ' SLA em risco' : null,
                $blockers > 0 ? $blockers . ' bloqueio(s) documental(is)' : null,
                $billing > 0 ? $billing . ' cobrança(s) vencida(s)' : null,
            ]));

            return [
                'title' => $empresa->nome_fantasia ?: $empresa->razao_social,
                'status' => $score >= 8 ? 'Crítico' : 'Atenção',
                'meta' => implode(' • ', $reasons),
                'description' => 'Cliente aparece aqui porque combina risco operacional, documento, SLA ou inadimplência. O detalhe deve ser aberto na aba de origem.',
                'tone' => $score >= 8 ? 'danger' : 'warning',
                'url' => $this->safeUrl(CentroOperacional::class) ?: $this->safeUrl(ItemControleResource::class),
                'action_label' => $billing > 0 ? 'Decidir cobrança' : ($blockers > 0 ? 'Destravar entrega' : 'Abrir operação'),
                'score' => $score,
            ];
        })
            ->filter(fn (array $row): bool => ($row['score'] ?? 0) > 0)
            ->sortByDesc('score')
            ->values()
            ->take(5)
            ->map(function (array $row): array {
                unset($row['score']);
                return $row;
            })
            ->all();
    }

    private function criticalMonthlyObligationsCount(?int $empresaId = null): int
    {
        if (! $this->hasTable('item_controles')) {
            return 0;
        }

        return $this->criticalMonthlyObligationsQuery($empresaId)->count();
    }

    private function criticalMonthlyObligationsQuery(?int $empresaId = null): Builder
    {
        return $this->itemsBase($empresaId)
            ->whereNotNull('item_controles.data_vencimento')
            ->whereBetween('item_controles.data_vencimento', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
            ->whereNotIn('item_controles.status', self::DONE_STATUSES)
            ->where(function (Builder $query): void {
                $query->whereDate('item_controles.data_vencimento', '<=', now()->addDays(7)->toDateString());
                $this->orWhereHighRisk($query);
            });
    }

    private function slaRiskCount(?int $empresaId = null): int
    {
        if (! $this->hasTable('item_controles') || ! $this->hasColumn('item_controles', 'sla_limite_em')) {
            return 0;
        }

        return $this->itemsBase($empresaId)
            ->whereNotNull('item_controles.sla_limite_em')
            ->where(function (Builder $query): void {
                $query->whereNull('item_controles.sla_concluido_em')
                    ->orWhere('item_controles.sla_concluido_em', '');
            })
            ->where('item_controles.sla_limite_em', '<=', now()->addHours(12))
            ->whereNotIn('item_controles.status', self::DONE_STATUSES)
            ->count();
    }

    private function documentBlockerCount(?int $empresaId = null): int
    {
        if (! $this->hasTable('item_controles')) {
            return 0;
        }

        return $this->itemsBase($empresaId)
            ->whereNotIn('item_controles.status', self::DONE_STATUSES)
            ->where(function (Builder $query): void {
                $this->whereBlockerCondition($query);
            })
            ->where(function (Builder $query): void {
                $query->whereNull('item_controles.data_vencimento')
                    ->orWhereDate('item_controles.data_vencimento', '<=', now()->addDays(15)->toDateString());
                $this->orWhereHighRisk($query);
            })
            ->count();
    }

    private function delinquencyWithOperationalImpactCount(): int
    {
        $clientIds = $this->overdueBillingClientIds();

        if (empty($clientIds)) {
            return 0;
        }

        return collect($clientIds)
            ->filter(fn (int $empresaId): bool => $this->criticalMonthlyObligationsCount($empresaId) > 0 || $this->slaRiskCount($empresaId) > 0 || $this->documentBlockerCount($empresaId) > 0)
            ->count();
    }

    private function overdueBillingValueWithOperationalImpact(): float
    {
        $table = $this->billingTable();
        $clientIds = collect($this->overdueBillingClientIds())
            ->filter(fn (int $empresaId): bool => $this->criticalMonthlyObligationsCount($empresaId) > 0 || $this->slaRiskCount($empresaId) > 0 || $this->documentBlockerCount($empresaId) > 0)
            ->values()
            ->all();

        if (! $table || empty($clientIds)) {
            return 0.0;
        }

        return (float) DB::table($table)
            ->whereIn('empresa_id', $clientIds)
            ->whereNotNull('vencimento')
            ->whereDate('vencimento', '<', now()->toDateString())
            ->whereNotIn('status', self::BILLING_PAID_STATUSES)
            ->sum('valor');
    }

    private function overdueBillingCount(?int $empresaId = null): int
    {
        $table = $this->billingTable();

        if (! $table) {
            return 0;
        }

        return DB::table($table)
            ->when($empresaId, fn (Builder $query, int $id) => $query->where('empresa_id', $id))
            ->when(! $empresaId && $this->empresaId(), fn (Builder $query, int $id) => $query->where('empresa_id', $id))
            ->whereNotNull('vencimento')
            ->whereDate('vencimento', '<', now()->toDateString())
            ->whereNotIn('status', self::BILLING_PAID_STATUSES)
            ->count();
    }

    private function overdueBillingClientIds(): array
    {
        $table = $this->billingTable();

        if (! $table) {
            return [];
        }

        return DB::table($table)
            ->when($this->empresaId(), fn (Builder $query, int $id) => $query->where('empresa_id', $id))
            ->whereNotNull('vencimento')
            ->whereDate('vencimento', '<', now()->toDateString())
            ->whereNotIn('status', self::BILLING_PAID_STATUSES)
            ->whereNotNull('empresa_id')
            ->distinct()
            ->pluck('empresa_id')
            ->map(fn ($id): int => (int) $id)
            ->all();
    }

    private function billingTable(): ?string
    {
        foreach (['financeiro_cobrancas', 'pagamentos', 'cobrancas'] as $table) {
            if ($this->hasTable($table) && $this->hasColumn($table, 'vencimento') && $this->hasColumn($table, 'status') && $this->hasColumn($table, 'empresa_id')) {
                return $table;
            }
        }

        return null;
    }

    private function itemRow(object $item, ?string $forcedStatus = null, ?string $actionLabel = null): array
    {
        $due = ! empty($item->data_vencimento) ? Carbon::parse($item->data_vencimento) : null;
        $late = $due && $due->lt(now()->startOfDay());
        $today = $due && $due->isToday();
        $slaCritical = ! empty($item->sla_limite_em) && empty($item->sla_concluido_em) && Carbon::parse($item->sla_limite_em)->lte(now()->addHours(12));
        $riskScore = (int) ($item->risk_score ?? $item->risco_score ?? 0);

        $status = $forcedStatus ?: ($late ? 'Vencido' : ($today ? 'Hoje' : ($slaCritical ? 'SLA' : 'Risco')));
        $tone = ($late || $slaCritical || $riskScore >= 70) ? 'danger' : ($today || $riskScore >= 40 ? 'warning' : 'info');

        return [
            'title' => $item->titulo ?? 'Item sem título',
            'status' => $status,
            'meta' => trim(($item->empresa_nome ?? 'Empresa') . ' • ' . ($item->responsavel_nome ?? 'Sem responsável')),
            'description' => $this->itemRiskDescription($item, $due, $slaCritical, $riskScore),
            'tone' => $tone,
            'url' => $this->safeUrl(ItemControleResource::class) ?: $this->safeUrl(CentroOperacional::class),
            'action_label' => $actionLabel ?: ($late || $slaCritical ? 'Resolver agora' : 'Abrir origem'),
            'deadline' => $due ? $due->format('d/m') : null,
        ];
    }

    private function itemRiskDescription(object $item, ?Carbon $due, bool $slaCritical, int $riskScore): string
    {
        $parts = array_values(array_filter([
            'Status: ' . ($item->status ?? '-'),
            $due ? 'Vence em ' . $due->format('d/m/Y') : null,
            $slaCritical ? 'SLA em risco nas próximas 12h' : null,
            $riskScore > 0 ? 'Risco ' . $riskScore . '/100' : null,
            ! empty($item->prioridade) ? 'Prioridade ' . $item->prioridade : null,
        ]));

        return implode(' • ', $parts);
    }

    private function whereBlockerCondition(Builder $query): void
    {
        $hasCondition = false;

        foreach (['bloqueado', 'bloqueado_por_dependencia', 'blocked_by_dependency'] as $column) {
            if ($this->hasColumn('item_controles', $column)) {
                $query->orWhere('item_controles.' . $column, 1);
                $hasCondition = true;
            }
        }

        if ($this->hasColumn('item_controles', 'document_status')) {
            $query->orWhereIn('item_controles.document_status', ['pendente', 'aguardando', 'aguardando_cliente', 'em_revisao', 'revisao']);
            $hasCondition = true;
        }

        if ($this->hasColumn('item_controles', 'signature_status')) {
            $query->orWhereIn('item_controles.signature_status', ['pendente', 'aguardando', 'enviado']);
            $hasCondition = true;
        }

        if ($this->hasColumn('item_controles', 'approval_status')) {
            $query->orWhereIn('item_controles.approval_status', ['pendente', 'aguardando', 'em_aprovacao']);
            $hasCondition = true;
        }

        if ($this->hasColumn('item_controles', 'portal_status')) {
            $query->orWhereIn('item_controles.portal_status', ['aguardando_cliente', 'pendente', 'enviado']);
            $hasCondition = true;
        }

        if (! $hasCondition) {
            $query->whereRaw('1 = 0');
        }
    }

    private function orWhereHighRisk(Builder $query): void
    {
        if ($this->hasColumn('item_controles', 'risk_score')) {
            $query->orWhere('item_controles.risk_score', '>=', 70);
        }

        if ($this->hasColumn('item_controles', 'risco_score')) {
            $query->orWhere('item_controles.risco_score', '>=', 70);
        }

        if ($this->hasColumn('item_controles', 'urgencia')) {
            $query->orWhereIn('item_controles.urgencia', ['critica', 'crítica', 'alta']);
        }
    }

    private function itemsBase(?int $empresaId = null): Builder
    {
        $query = DB::table('item_controles');

        if ($this->hasTable('empresas')) {
            $query->leftJoin('empresas', 'empresas.id', '=', 'item_controles.empresa_id');
        }

        if ($this->hasTable('responsaveis')) {
            $query->leftJoin('responsaveis', 'responsaveis.id', '=', 'item_controles.responsavel_id');
        }

        $query->select('item_controles.*');

        if ($this->hasTable('empresas')) {
            $query->addSelect(DB::raw('COALESCE(empresas.nome_fantasia, empresas.razao_social) as empresa_nome'));
        }

        if ($this->hasTable('responsaveis')) {
            $query->addSelect('responsaveis.nome as responsavel_nome');
        }

        if ($empresaId || $this->empresaId()) {
            $query->where('item_controles.empresa_id', $empresaId ?: $this->empresaId());
        }

        return $query;
    }

    private function safeUrl(string $class): ?string
    {
        try {
            if (method_exists($class, 'canAccess') && ! $class::canAccess()) {
                return null;
            }

            return $class::getUrl();
        } catch (Throwable) {
            return null;
        }
    }

    private function empresaId(): ?int
    {
        $user = Filament::auth()->user();

        if (! $user || (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin())) {
            return null;
        }

        return $user->empresa_id ? (int) $user->empresa_id : null;
    }

    private function hasTable(string $table): bool
    {
        try {
            return Schema::hasTable($table);
        } catch (Throwable) {
            return false;
        }
    }

    private function hasColumn(string $table, string $column): bool
    {
        try {
            return Schema::hasColumn($table, $column);
        } catch (Throwable) {
            return false;
        }
    }

    private function formatNumber(int|float $value): string
    {
        return number_format($value, 0, ',', '.');
    }
}
