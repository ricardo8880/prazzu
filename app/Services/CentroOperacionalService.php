<?php

namespace App\Services;


use App\Support\CachedSchema;
use App\Support\CentroOperacionalAccess;
use App\Filament\Resources\ItemControles\ItemControleResource;
use App\Models\ItemControle;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class CentroOperacionalService
{
    public function dashboard(?User $user, array $filters = []): array
    {
        $base = $this->baseQuery($user, $filters);
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
            ->map(fn (ItemControle $item): array => $this->taskPayload($item, 'danger', $user))
            ->values()
            ->toArray();

        $vencemHoje = (clone $vencemHojeQuery)
            ->orderByRaw("CASE WHEN prioridade IN ('critica', 'urgente') THEN 1 WHEN prioridade = 'alta' THEN 2 ELSE 3 END")
            ->orderBy('data_vencimento')
            ->limit(8)
            ->get()
            ->map(fn (ItemControle $item): array => $this->taskPayload($item, 'warning', $user))
            ->values()
            ->toArray();

        $aprovacoes = (clone $aprovacoesQuery)
            ->orderByRaw($this->statusEnteredColumn() ? 'COALESCE(status_operacional_at, updated_at) asc' : 'updated_at asc')
            ->limit(8)
            ->get()
            ->map(fn (ItemControle $item): array => $this->taskPayload($item, 'warning', $user))
            ->values()
            ->toArray();

        $correcao = (clone $correcaoQuery)
            ->orderByRaw($this->statusEnteredColumn() ? 'COALESCE(status_operacional_at, updated_at) asc' : 'updated_at asc')
            ->limit(8)
            ->get()
            ->map(fn (ItemControle $item): array => $this->taskPayload($item, 'danger', $user))
            ->values()
            ->toArray();

        $financeiro = (clone $financeiroQuery)
            ->orderByDesc('data_conclusao')
            ->orderByDesc('updated_at')
            ->limit(6)
            ->get()
            ->map(fn (ItemControle $item): array => $this->taskPayload($item, 'success', $user))
            ->values()
            ->toArray();

        $bloqueados = $bloqueadosQuery
            ? (clone $bloqueadosQuery)
                ->orderByDesc('updated_at')
                ->limit(6)
                ->get()
                ->map(fn (ItemControle $item): array => $this->taskPayload($item, 'warning', $user))
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
        $vencimentos = $this->vencimentosResumo($user, (string) ($filters['deadline_period'] ?? 'today'), $filters);
        $departamentos = $this->departamentosResumo($user, $filters);
        $workload = $this->workload($user, $filters);
        $financeiroResumo = $this->financeiroResumo($user, $filters);
        $resultadosMes = $this->resultadosMes($user, $filters);
        $globalSearchTerm = trim((string) ($filters['global_search'] ?? ''));
        $globalSearchResults = $this->globalSearch($user, $globalSearchTerm);

        $healthScore = $this->healthScore($totalAbertas, $totalVencidas, $totalVencemHoje, $totalBloqueados, $totalCorrecao, $totalSemResponsavel);
        $tendenciasOperacionais = $this->tendenciasOperacionais($user, $filters, $blockColumns);
        $riskCards = $this->riskCards($totalRisco, $totalVencemHoje, $totalSemResponsavel, $totalBloqueados);
        $alertasInteligentes = $this->alertasInteligentes(
            user: $user,
            vencidosQuery: clone $vencidosQuery,
            vencemHojeQuery: clone $vencemHojeQuery,
            proximosSeteQuery: clone $proximosSeteQuery,
            aprovacoesQuery: clone $aprovacoesQuery,
            correcaoQuery: clone $correcaoQuery,
            bloqueadosQuery: $bloqueadosQuery ? clone $bloqueadosQuery : null,
            semResponsavelQuery: clone $semResponsavelQuery,
        );

        return [
            'risk_cards' => $riskCards,
            'global_search' => [
                'term' => $globalSearchTerm,
                'results' => $globalSearchResults,
                'minimum_chars' => 2,
            ],
            'alertas_inteligentes' => $alertasInteligentes,
            'cards' => [
                [
                    'key' => 'risk',
                    'label' => 'Em Risco de Multa',
                    'value' => $totalRisco,
                    'tone' => $totalRisco > 0 ? 'danger' : 'success',
                    'icon' => 'bi-exclamation-octagon-fill',
                    'shortcut' => 'risk',
                    'hint' => 'Clientes com prazo crítico, bloqueio ou correção parada.'
                ],
                [
                    'key' => 'today',
                    'label' => 'Vencem Hoje',
                    'value' => $totalVencemHoje,
                    'tone' => $totalVencemHoje > 0 ? 'warning' : 'success',
                    'icon' => 'bi-calendar2-event-fill',
                    'shortcut' => 'all',
                    'deadline_period' => 'today',
                    'hint' => 'Obrigações que ainda podem ser resolvidas hoje.',
                ],
                [
                    'key' => 'late',
                    'label' => 'Vencidas',
                    'value' => $totalVencidas,
                    'tone' => $totalVencidas > 0 ? 'danger' : 'success',
                    'icon' => 'bi-alarm-fill',
                    'shortcut' => 'late',
                    'hint' => 'Itens fora do prazo e com prioridade máxima.',
                ],
                [
                    'key' => 'approval',
                    'label' => 'Aprovações',
                    'value' => $totalAprovacao,
                    'tone' => $totalAprovacao > 0 ? 'warning' : 'success',
                    'icon' => 'bi-file-earmark-check-fill',
                    'shortcut' => 'approval',
                    'hint' => 'Itens aguardando decisão para seguir o fluxo.',
                ],
                [
                    'key' => 'financial',
                    'label' => 'Pendências Financeiras',
                    'value' => $totalFinanceiro,
                    'tone' => $totalFinanceiro > 0 ? 'warning' : 'success',
                    'icon' => 'bi-cash-coin',
                    'shortcut' => 'financial',
                    'hint' => 'Entregas finalizadas ainda sem faturamento/pagamento.',
                ],
            ],
            'resolver_agora' => $resolverAgora,
            'clientes_criticos' => $clientesCriticos,
            'vencimentos' => $vencimentos,
            'aprovacoes' => $aprovacoes,
            'financeiro' => $financeiro,
            'financeiro_resumo' => $financeiroResumo,
            'bloqueados' => $bloqueados,
            'sem_responsavel' => (clone $semResponsavelQuery)
                ->orderByRaw("CASE WHEN prioridade IN ('critica', 'urgente') THEN 1 WHEN prioridade = 'alta' THEN 2 ELSE 3 END")
                ->orderBy('data_vencimento')
                ->limit(6)
                ->get()
                ->map(fn (ItemControle $item): array => $this->taskPayload($item, 'warning', $user))
                ->values()
                ->toArray(),
            'workload' => $workload,
            'departamentos' => $departamentos,
            'resultados_mes' => $resultadosMes,
            'health_score' => $healthScore,
            'tendencias_operacionais' => $tendenciasOperacionais,
            'status_options' => $this->statusOptions(),
            'department_options' => $this->departmentOptions(),
            'date_range_options' => $this->dateRangeOptions(),
            'total_abertas' => $totalAbertas,
            'me_mode' => $this->isMeMode($user),
            'missing_columns' => $this->missingRecommendedColumns(),
        ];
    }

    protected function globalSearch(?User $user, string $term): array
    {
        $term = trim($term);

        if (mb_strlen($term) < 2) {
            return [];
        }

        $normalizedTerm = mb_strtolower($term);
        $like = '%' . str_replace(['%', '_'], ['\%', '\_'], $normalizedTerm) . '%';

        return ItemControle::query()
            ->visibleForUser($user)
            ->select($this->safeSelect())
            ->with(['responsavel:id,nome,user_id', 'empresa:id,razao_social,nome_fantasia,cnpj', 'categoria:id,nome'])
            ->where(function (Builder $query) use ($like): void {
                foreach ($this->globalSearchColumns() as $column) {
                    $query->orWhereRaw('LOWER(' . $column . ') LIKE ?', [$like]);
                }

                $query->orWhereHas('empresa', function (Builder $query) use ($like): Builder {
                    return $query
                        ->whereRaw('LOWER(razao_social) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(nome_fantasia) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(cnpj) LIKE ?', [$like]);
                })->orWhereHas('responsavel', function (Builder $query) use ($like): Builder {
                    return $query
                        ->whereRaw('LOWER(nome) LIKE ?', [$like])
                        ->orWhereRaw('LOWER(email) LIKE ?', [$like]);
                })->orWhereHas('categoria', fn (Builder $query): Builder => $query->whereRaw('LOWER(nome) LIKE ?', [$like]));
            })
            ->orderByRaw("CASE
                WHEN LOWER(titulo) LIKE ? THEN 1
                WHEN LOWER(tipo) LIKE ? THEN 2
                ELSE 3
            END", [$like, $like])
            ->orderByRaw("CASE WHEN status IN ('concluido', 'aprovado', 'cancelado') THEN 2 ELSE 1 END")
            ->orderByRaw("CASE WHEN data_vencimento IS NULL THEN 2 ELSE 1 END")
            ->orderBy('data_vencimento')
            ->limit(10)
            ->get()
            ->map(fn (ItemControle $item): array => $this->globalSearchPayload($item, $term, $user))
            ->values()
            ->toArray();
    }

    protected function globalSearchColumns(): array
    {
        return array_values(array_filter([
            'titulo',
            'descricao',
            'tipo',
            $this->hasColumn('arquivo') ? 'arquivo' : null,
            $this->hasColumn('contrato_numero') ? 'contrato_numero' : null,
            $this->hasColumn('contrato_parte_nome') ? 'contrato_parte_nome' : null,
            $this->hasColumn('contrato_parte_documento') ? 'contrato_parte_documento' : null,
            $this->hasColumn('document_status') ? 'document_status' : null,
            $this->hasColumn('approval_status') ? 'approval_status' : null,
        ]));
    }

    protected function globalSearchPayload(ItemControle $item, string $term, ?User $user): array
    {
        $payload = $this->taskPayload($item, $this->toneFor($item, 'info'), $user);
        $matchType = $this->globalSearchMatchType($item, $term);

        return array_merge($payload, [
            'match_type' => $matchType['type'],
            'match_label' => $matchType['label'],
            'search_context' => $matchType['context'],
        ]);
    }

    protected function globalSearchMatchType(ItemControle $item, string $term): array
    {
        $needle = mb_strtolower(trim($term));
        $contains = static fn (?string $value): bool => filled($value) && str_contains(mb_strtolower($value), $needle);

        if ($contains($item->empresa?->razao_social) || $contains($item->empresa?->nome_fantasia) || $contains($item->empresa?->cnpj)) {
            return ['type' => 'cliente', 'label' => 'Cliente', 'context' => $item->empresa?->razao_social ?: 'Cliente vinculado'];
        }

        if ($contains($item->responsavel?->nome) || $contains($item->responsavel?->email)) {
            return ['type' => 'responsavel', 'label' => 'Responsável', 'context' => $item->responsavel?->nome ?: 'Responsável vinculado'];
        }

        if ($contains((string) ($item->contrato_numero ?? null)) || $contains((string) ($item->contrato_parte_nome ?? null)) || $contains((string) ($item->contrato_parte_documento ?? null))) {
            return ['type' => 'contrato', 'label' => 'Contrato', 'context' => $item->contrato_numero ? 'Contrato ' . $item->contrato_numero : ($item->contrato_parte_nome ?: 'Dados contratuais')];
        }

        if ($contains((string) ($item->arquivo ?? null)) || $contains((string) ($item->document_status ?? null))) {
            return ['type' => 'documento', 'label' => 'Documento', 'context' => $item->arquivo ?: $this->statusLabel((string) ($item->document_status ?? 'documento'))];
        }

        if ($contains($item->categoria?->nome) || $contains($item->tipo)) {
            return ['type' => 'tipo', 'label' => 'Tipo', 'context' => $item->categoria?->nome ?: $item->tipo ?: 'Obrigação operacional'];
        }

        return ['type' => 'tarefa', 'label' => 'Tarefa', 'context' => $item->titulo ?: 'Item operacional'];
    }

    protected function baseQuery(?User $user, array $filters = []): Builder
    {
        return $this->applyDashboardFilters(
            ItemControle::query()
                ->visibleForUser($user)
                ->select($this->safeSelect())
                ->with(['responsavel:id,nome,user_id', 'empresa:id,razao_social', 'categoria:id,nome']),
            $filters
        );
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
                     'arquivo',
                     'contrato_numero',
                     'contrato_parte_nome',
                     'contrato_parte_documento',
                     'document_status',
                     'approval_status',
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

    protected function taskPayload(ItemControle $item, string $defaultTone, ?User $user = null): array
    {
        $statusAt = $this->statusEnteredAt($item);
        $valor = $this->moneyValue($item);
        $blocked = $this->isBlocked($item);
        $status = (string) $item->status;
        $tone = $blocked ? 'warning' : $this->toneFor($item, $defaultTone);
        $operationalAction = $this->operationalActionFor($item, $blocked, $tone);

        return [
            'id' => $item->id,
            'type' => $item->categoria?->nome ?: $item->getTipoOuCategoria(),
            'title' => $item->titulo,
            'status_key' => $status,
            'status' => $this->statusLabel($status),
            'urgency' => $this->urgencyLabel($item),
            'priority' => $this->priorityLabelForResolver($item, $blocked, $tone),
            'priority_tone' => $this->priorityToneForResolver($item, $blocked, $tone),
            'tone' => $tone,
            'responsavel' => $item->responsavel?->nome ?: 'Sem responsável',
            'empresa' => $item->empresa?->razao_social ?: 'Sem empresa',
            'due' => $item->data_vencimento?->format('d/m/Y'),
            'due_human' => $this->deadlineHumanLabel($item),
            'stopped_for' => $statusAt
                ? $statusAt->diffForHumans(now(), ['parts' => 2, 'short' => true])
                : $item->updated_at?->diffForHumans(now(), ['parts' => 2, 'short' => true]),
            'description' => filled($item->descricao)
                ? str($item->descricao)->limit(120)->toString()
                : 'Sem descrição cadastrada.',
            'blocked' => $blocked,
            'value' => $valor > 0 ? 'R$ ' . number_format($valor, 2, ',', '.') : null,
            'url' => ItemControleResource::getUrl('edit', ['record' => $item]),
            'primary_action' => $operationalAction,
            'actions' => CentroOperacionalAccess::actionPermissions($user, $item),
        ];
    }


    protected function operationalActionFor(ItemControle $item, bool $blocked, string $tone): array
    {
        $status = (string) $item->status;

        if (in_array($status, ['aguardando_aprovacao', 'em_aprovacao', 'reprovado'], true)) {
            return ['key' => 'approve', 'label' => 'Aprovar', 'icon' => 'bi-check2-circle'];
        }

        if (in_array($status, ['correcao_necessaria', 'reprovado'], true) || $blocked || $tone === 'danger') {
            return ['key' => 'correct', 'label' => 'Corrigir', 'icon' => 'bi-tools'];
        }

        return ['key' => 'open', 'label' => 'Abrir', 'icon' => 'bi-box-arrow-up-right'];
    }

    protected function priorityLabelForResolver(ItemControle $item, bool $blocked, string $tone): string
    {
        if ($blocked || $tone === 'danger' || in_array((string) $item->prioridade, ['critica', 'urgente'], true)) {
            return 'Crítica';
        }

        if ($item->data_vencimento?->isToday() || in_array((string) $item->prioridade, ['alta'], true)) {
            return 'Alta';
        }

        if ($item->data_vencimento && $item->data_vencimento->copy()->startOfDay()->between(now()->startOfDay(), now()->addDays(3)->startOfDay())) {
            return 'Média';
        }

        return 'Baixa';
    }

    protected function priorityToneForResolver(ItemControle $item, bool $blocked, string $tone): string
    {
        return match ($this->priorityLabelForResolver($item, $blocked, $tone)) {
            'Crítica' => 'danger',
            'Alta' => 'warning',
            'Média' => 'attention',
            default => 'success',
        };
    }

    protected function deadlineHumanLabel(ItemControle $item): string
    {
        if (! $item->data_vencimento) {
            return 'Sem prazo';
        }

        if ($item->data_vencimento->isToday()) {
            return 'Hoje';
        }

        if ($item->data_vencimento->isTomorrow()) {
            return 'Amanhã';
        }

        if ($item->data_vencimento->copy()->startOfDay()->lessThan(now()->startOfDay())) {
            return 'Vencido há ' . $item->data_vencimento->diffInDays(now()) . ' dia(s)';
        }

        return 'Em ' . $item->data_vencimento->diffInDays(now()) . ' dia(s)';
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
            && $item->data_vencimento->copy()->startOfDay()->lessThan(now()->startOfDay())
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
        $dueHuman = $item['due_human'] ?? '';

        if (($item['blocked'] ?? false) || $urgency === 'Crítica') {
            return 10;
        }

        if ($tone === 'danger' || str_contains($dueHuman, 'Vencido')) {
            return 20;
        }

        if (str_contains($status, 'Aprovação')) {
            return 30;
        }

        if ($dueHuman === 'Hoje' || $tone === 'warning') {
            return 40;
        }

        if ($dueHuman === 'Amanhã') {
            return 50;
        }

        return 90;
    }

    protected function riskCards(int $totalRisco, int $totalVencemHoje, int $totalSemResponsavel, int $totalBloqueados): array
    {
        return [
            [
                'key' => 'risk',
                'label' => 'Clientes em risco',
                'value' => $totalRisco,
                'tone' => $totalRisco > 0 ? 'danger' : 'success',
                'icon' => 'bi-exclamation-octagon-fill',
                'shortcut' => 'risk',
                'hint' => 'Obrigação vencida, bloqueio, correção ou aprovação parada.'
            ],
            [
                'key' => 'today',
                'label' => 'Vencem Hoje',
                'value' => $totalVencemHoje,
                'tone' => $totalVencemHoje > 0 ? 'warning' : 'success',
                'icon' => 'bi-calendar2-event-fill',
                'shortcut' => 'all',
                'hint' => 'Prazos que precisam ser concluídos ainda hoje.',
            ],
            [
                'key' => 'no_owner',
                'label' => 'Sem Responsável',
                'value' => $totalSemResponsavel,
                'tone' => $totalSemResponsavel > 0 ? 'attention' : 'success',
                'icon' => 'bi-person-x-fill',
                'shortcut' => 'no_owner',
                'hint' => 'Itens abertos sem dono operacional definido.',
            ],
            [
                'key' => 'blocked',
                'label' => 'Clientes Bloqueados',
                'value' => $totalBloqueados,
                'tone' => $totalBloqueados > 0 ? 'danger' : 'success',
                'icon' => 'bi-shield-lock-fill',
                'shortcut' => 'blocked',
                'hint' => 'Bloqueios operacionais ou dependências impeditivas.',
            ],
        ];
    }

    protected function alertasInteligentes(
        ?User $user,
        Builder $vencidosQuery,
        Builder $vencemHojeQuery,
        Builder $proximosSeteQuery,
        Builder $aprovacoesQuery,
        Builder $correcaoQuery,
        ?Builder $bloqueadosQuery,
        Builder $semResponsavelQuery,
    ): array {
        $criticos = collect();

        if ($bloqueadosQuery) {
            $criticos = $criticos->merge(
                (clone $bloqueadosQuery)->orderBy('data_vencimento')->limit(4)->get()
                    ->map(fn (ItemControle $item): array => $this->alertPayload($item, 'Crítico', 'Bloqueio operacional ativo', 'danger', 'bi-shield-lock-fill', $user))
            );
        }

        $criticos = $criticos->merge(
            (clone $vencidosQuery)->orderBy('data_vencimento')->limit(4)->get()
                ->map(fn (ItemControle $item): array => $this->alertPayload($item, 'Crítico', 'Prazo vencido com risco de multa', 'danger', 'bi-exclamation-octagon-fill', $user))
        )->merge(
            (clone $correcaoQuery)->orderByRaw($this->statusEnteredColumn() ? 'COALESCE(status_operacional_at, updated_at) asc' : 'updated_at asc')->limit(3)->get()
                ->map(fn (ItemControle $item): array => $this->alertPayload($item, 'Crítico', 'Correção parada bloqueando o fluxo', 'danger', 'bi-tools', $user))
        );

        $importantes = collect()
            ->merge(
                (clone $vencemHojeQuery)->orderBy('data_vencimento')->limit(5)->get()
                    ->map(fn (ItemControle $item): array => $this->alertPayload($item, 'Importante', 'Vence hoje', 'warning', 'bi-lightning-charge-fill', $user))
            )
            ->merge(
                (clone $aprovacoesQuery)->orderByRaw($this->statusEnteredColumn() ? 'COALESCE(status_operacional_at, updated_at) asc' : 'updated_at asc')->limit(4)->get()
                    ->map(fn (ItemControle $item): array => $this->alertPayload($item, 'Importante', 'Aprovação aguardando decisão', 'warning', 'bi-file-earmark-check-fill', $user))
            );

        $atencao = collect()
            ->merge(
                (clone $proximosSeteQuery)->orderBy('data_vencimento')->limit(5)->get()
                    ->map(fn (ItemControle $item): array => $this->alertPayload($item, 'Atenção', 'Prazo nos próximos 7 dias', 'attention', 'bi-calendar-week-fill', $user))
            )
            ->merge(
                (clone $semResponsavelQuery)->orderBy('data_vencimento')->limit(4)->get()
                    ->map(fn (ItemControle $item): array => $this->alertPayload($item, 'Atenção', 'Item crítico sem responsável', 'attention', 'bi-person-x-fill', $user))
            );

        $informativos = $this->informativeAlerts($user);

        return [
            'critical' => [
                'label' => 'Crítico',
                'tone' => 'danger',
                'icon' => 'bi-exclamation-octagon-fill',
                'description' => 'Pode gerar multa, bloqueio ou retrabalho imediato.',
                'items' => $criticos->unique('id')->take(6)->values()->toArray(),
            ],
            'important' => [
                'label' => 'Importante',
                'tone' => 'warning',
                'icon' => 'bi-lightning-charge-fill',
                'description' => 'Precisa ser resolvido hoje para não virar atraso.',
                'items' => $importantes->unique('id')->take(6)->values()->toArray(),
            ],
            'attention' => [
                'label' => 'Atenção',
                'tone' => 'attention',
                'icon' => 'bi-exclamation-triangle-fill',
                'description' => 'Próximos 7 dias ou pendências sem dono.',
                'items' => $atencao->unique('id')->take(6)->values()->toArray(),
            ],
            'info' => [
                'label' => 'Informativo',
                'tone' => 'info',
                'icon' => 'bi-info-circle-fill',
                'description' => 'Atualizações operacionais recentes.',
                'items' => $informativos,
            ],
        ];
    }

    protected function alertPayload(ItemControle $item, string $layer, string $reason, string $tone, string $icon, ?User $user = null): array
    {
        $payload = $this->taskPayload($item, $tone, $user);

        return array_merge($payload, [
            'layer' => $layer,
            'reason' => $reason,
            'icon' => $icon,
            'summary' => trim(($payload['empresa'] ?? 'Sem empresa') . ' • ' . ($payload['title'] ?? 'Item operacional')),
        ]);
    }

    protected function informativeAlerts(?User $user): array
    {
        return ItemControle::query()
            ->visibleForUser($user)
            ->select($this->safeSelect())
            ->with(['responsavel:id,nome,user_id', 'empresa:id,razao_social', 'categoria:id,nome'])
            ->whereIn('status', ['concluido', 'aprovado', 'assinado'])
            ->where('updated_at', '>=', now()->subDays(7))
            ->orderByDesc('updated_at')
            ->limit(4)
            ->get()
            ->map(fn (ItemControle $item): array => $this->alertPayload($item, 'Informativo', 'Atualizado nos últimos 7 dias', 'info', 'bi-info-circle-fill', $user))
            ->values()
            ->toArray();
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
                    'id' => $first->id,
                    'item_id' => $first->id,
                    'empresa_id' => $first->empresa_id ? (int) $first->empresa_id : null,
                    'cliente' => $first->empresa?->razao_social ?: 'Sem empresa vinculada',
                    'problema' => $this->clientProblemLabel($first, $items->count()),
                    'responsavel' => $first->responsavel?->nome ?: 'Sem responsável',
                    'dias' => $this->daysRemainingLabel($first),
                    'risco' => $risk,
                    'tone' => $risk === 'Crítico' ? 'danger' : ($risk === 'Alto' ? 'warning' : 'info'),
                    'total_itens' => $items->count(),
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

    protected function vencimentosResumo(?User $user, string $period = 'today', array $filters = []): array
    {
        $base = $this->applyDashboardFilters(
            ItemControle::query()
                ->visibleForUser($user)
                ->select($this->safeSelect())
                ->with(['categoria:id,nome'])
                ->whereNotIn('status', ['concluido', 'aprovado', 'cancelado'])
                ->whereNotNull('data_vencimento'),
            array_merge($filters, ['date_range' => 'all'])
        );

        $periods = [
            'today' => [
                'label' => 'Hoje',
                'value' => (clone $base)->whereDate('data_vencimento', now()->toDateString())->count(),
                'tone' => 'warning',
            ],
            'seven_days' => [
                'label' => '7 dias',
                'value' => (clone $base)->whereDate('data_vencimento', '>', now()->toDateString())->whereDate('data_vencimento', '<=', now()->addDays(7)->toDateString())->count(),
                'tone' => 'info',
            ],
            'fifteen_days' => [
                'label' => '15 dias',
                'value' => (clone $base)->whereDate('data_vencimento', '>', now()->toDateString())->whereDate('data_vencimento', '<=', now()->addDays(15)->toDateString())->count(),
                'tone' => 'info',
            ],
            'thirty_days' => [
                'label' => '30 dias',
                'value' => (clone $base)->whereDate('data_vencimento', '>', now()->toDateString())->whereDate('data_vencimento', '<=', now()->addDays(30)->toDateString())->count(),
                'tone' => 'info',
            ],
        ];

        $period = array_key_exists($period, $periods) ? $period : 'today';
        $selectedQuery = clone $base;

        if ($period === 'today') {
            $selectedQuery->whereDate('data_vencimento', now()->toDateString());
        } elseif ($period === 'seven_days') {
            $selectedQuery->whereDate('data_vencimento', '>', now()->toDateString())
                ->whereDate('data_vencimento', '<=', now()->addDays(7)->toDateString());
        } elseif ($period === 'fifteen_days') {
            $selectedQuery->whereDate('data_vencimento', '>', now()->toDateString())
                ->whereDate('data_vencimento', '<=', now()->addDays(15)->toDateString());
        } else {
            $selectedQuery->whereDate('data_vencimento', '>', now()->toDateString())
                ->whereDate('data_vencimento', '<=', now()->addDays(30)->toDateString());
        }

        $rows = $selectedQuery
            ->orderBy('data_vencimento')
            ->limit(250)
            ->get()
            ->groupBy(fn (ItemControle $item): string => $this->departmentLabel($item))
            ->map(fn ($items, string $label): array => [
                'label' => $label,
                'value' => $items->count(),
                'tone' => $items->where('data_vencimento', '<', now()->startOfDay())->count() > 0 ? 'danger' : 'info',
            ])
            ->sortByDesc('value')
            ->take(4)
            ->values()
            ->toArray();

        return [
            'selected' => $period,
            'periods' => $periods,
            'rows' => $rows,
            'total' => (int) ($periods[$period]['value'] ?? array_sum(array_column($rows, 'value'))),
        ];
    }

    protected function departamentosResumo(?User $user, array $filters = []): array
    {
        return $this->applyDashboardFilters(
            ItemControle::query()
                ->visibleForUser($user)
                ->select($this->safeSelect()),
            $filters
        )
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
            ->take(10)
            ->values()
            ->toArray();
    }

    protected function workload(?User $user, array $filters = []): array
    {
        $rows = $this->applyDashboardFilters(
            ItemControle::query()
                ->visibleForUser($user)
                ->select('responsavel_id'),
            $filters
        )
            ->selectRaw('COUNT(item_controles.id) as total')
            ->whereNotIn('status', ['concluido', 'aprovado', 'cancelado'])
            ->whereNotNull('responsavel_id')
            ->with(['responsavel:id,nome'])
            ->groupBy('responsavel_id')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        return $rows
            ->map(function (ItemControle $item): array {
                $total = (int) $item->total;
                $capacity = 40;
                $percent = (int) min(160, max(0, round(($total / max(1, $capacity)) * 100)));

                return [
                    'responsavel_id' => $item->responsavel_id ? (int) $item->responsavel_id : null,
                    'name' => $item->responsavel?->nome ?: 'Sem responsável',
                    'total' => $total,
                    'capacity' => $capacity,
                    'percent' => $percent,
                    'status' => $percent > 100 ? 'Sobrecarregado' : ($percent >= 91 ? 'No limite' : ($percent >= 71 ? 'Atenção' : 'Normal')),
                    'tone' => $percent > 100 ? 'danger' : ($percent >= 91 ? 'warning' : ($percent >= 71 ? 'attention' : 'success')),
                    'open_url' => ItemControleResource::getUrl('index'),
                ];
            })
            ->values()
            ->toArray();
    }

    protected function financeiroResumo(?User $user, array $filters = []): array
    {
        $base = $this->applyDashboardFilters(
            ItemControle::query()
                ->visibleForUser($user)
                ->select($this->safeSelect())
                ->whereIn('status', ['concluido', 'aprovado', 'assinado']),
            array_merge($filters, ['date_range' => 'all'])
        );

        $faturavelQuery = (clone $base)
            ->when($this->hasColumn('faturado_em'), fn (Builder $query): Builder => $query->whereNull('faturado_em'))
            ->when(! $this->hasColumn('faturado_em'), fn (Builder $query): Builder => $query->where(function (Builder $query): void {
                $query->whereNull('contrato_status')->orWhereNotIn('contrato_status', ['faturado', 'pago']);
            }));

        $aVencerQuery = (clone $base)
            ->whereNotNull('data_vencimento')
            ->whereDate('data_vencimento', '>=', now()->toDateString())
            ->whereDate('data_vencimento', '<=', now()->addDays(7)->toDateString());

        $vencidoQuery = (clone $base)
            ->whereNotNull('data_vencimento')
            ->whereDate('data_vencimento', '<', now()->toDateString())
            ->when($this->hasColumn('pago_em'), fn (Builder $query): Builder => $query->whereNull('pago_em'));

        $inadimplenteQuery = (clone $base)
            ->where(function (Builder $query): void {
                $query->where('contrato_status', 'inadimplente')
                    ->orWhere('contrato_status', 'vencido')
                    ->orWhere('contrato_status', 'em_atraso');
            });

        $indicadores = [
            $this->financialIndicator('A vencer', clone $aVencerQuery, 'warning', 'bi-calendar2-event'),
            $this->financialIndicator('Vencido', clone $vencidoQuery, 'danger', 'bi-exclamation-octagon'),
            $this->financialIndicator('Inadimplente', clone $inadimplenteQuery, 'danger', 'bi-person-x'),
            $this->financialIndicator('Faturável', clone $faturavelQuery, 'success', 'bi-cash-coin'),
        ];

        $impactValue = collect($indicadores)->sum('raw_value');

        return [
            'indicadores' => $indicadores,
            'impacto_total' => 'R$ ' . number_format((float) $impactValue, 2, ',', '.'),
        ];
    }

    protected function financialIndicator(string $label, Builder $query, string $tone, string $icon): array
    {
        $items = $query->limit(250)->get();
        $rawValue = $items->sum(fn (ItemControle $item): float => $this->moneyValue($item));

        return [
            'label' => $label,
            'quantity' => $items->count(),
            'value' => 'R$ ' . number_format((float) $rawValue, 2, ',', '.'),
            'raw_value' => (float) $rawValue,
            'tone' => $items->count() > 0 ? $tone : 'success',
            'icon' => $icon,
            'impact' => $items->count() > 0 ? $this->financialImpactLabel($label, $items->count(), (float) $rawValue) : 'Sem impacto imediato',
        ];
    }

    protected function financialImpactLabel(string $label, int $quantity, float $rawValue): string
    {
        if ($rawValue > 0) {
            return $quantity . ' item(ns) somando R$ ' . number_format($rawValue, 2, ',', '.');
        }

        return match ($label) {
            'Vencido', 'Inadimplente' => $quantity . ' item(ns) exigem cobrança ativa',
            'Faturável' => $quantity . ' entrega(s) prontas para faturar',
            default => $quantity . ' item(ns) no radar financeiro',
        };
    }

    protected function resultadosMes(?User $user, array $filters = []): array
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
            ['label' => 'Clientes atendidos', 'value' => ItemControle::query()->visibleForUser($user)->whereIn('status', ['concluido', 'aprovado', 'assinado'])->whereBetween('updated_at', [now()->startOfMonth(), now()->endOfMonth()])->distinct('empresa_id')->count('empresa_id'), 'hint' => 'no mês'],
            ['label' => 'SLA', 'value' => $sla . '%', 'hint' => 'estimado'],
        ];
    }

    protected function tendenciasOperacionais(?User $user, array $filters = [], array $blockColumns = []): array
    {
        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        $openBase = $this->applyDashboardFilters(
            ItemControle::query()->visibleForUser($user),
            array_merge($filters, ['date_range' => 'all', 'status' => 'all'])
        )->whereNotIn('status', ['concluido', 'aprovado', 'cancelado']);

        $lateToday = (clone $openBase)
            ->whereNotNull('data_vencimento')
            ->whereDate('data_vencimento', '<', $today)
            ->count();

        $lateYesterdayReference = (clone $openBase)
            ->whereNotNull('data_vencimento')
            ->whereDate('data_vencimento', '<', $yesterday)
            ->count();

        $dueToday = (clone $openBase)
            ->whereNotNull('data_vencimento')
            ->whereDate('data_vencimento', $today)
            ->count();

        $dueTomorrow = (clone $openBase)
            ->whereNotNull('data_vencimento')
            ->whereDate('data_vencimento', now()->addDay()->toDateString())
            ->count();

        $completedToday = $this->applyDashboardFilters(
            ItemControle::query()->visibleForUser($user),
            array_merge($filters, ['date_range' => 'all', 'status' => 'all'])
        )
            ->whereIn('status', ['concluido', 'aprovado', 'assinado'])
            ->where(function (Builder $query) use ($today): void {
                $query->whereDate('data_conclusao', $today)
                    ->orWhereDate('updated_at', $today);
            })
            ->count();

        $completedYesterday = $this->applyDashboardFilters(
            ItemControle::query()->visibleForUser($user),
            array_merge($filters, ['date_range' => 'all', 'status' => 'all'])
        )
            ->whereIn('status', ['concluido', 'aprovado', 'assinado'])
            ->where(function (Builder $query) use ($yesterday): void {
                $query->whereDate('data_conclusao', $yesterday)
                    ->orWhereDate('updated_at', $yesterday);
            })
            ->count();

        $riskNow = (clone $openBase)
            ->where(function (Builder $query) use ($today, $blockColumns): void {
                $query->where(function (Builder $query) use ($today): void {
                    $query->whereNotNull('data_vencimento')
                        ->whereDate('data_vencimento', '<=', $today);
                })->orWhereIn('status', ['correcao_necessaria', 'reprovado']);

                foreach ($blockColumns as $column) {
                    $query->orWhere($column, true);
                }
            })
            ->count();

        $riskReference = max(0, $lateYesterdayReference + $dueTomorrow);

        return [
            $this->trendPayload('Vencidas', $lateToday, $lateYesterdayReference, 'down', 'Itens atrasados comparados com a referência de ontem.'),
            $this->trendPayload('Vencem hoje', $dueToday, $dueTomorrow, 'down', 'Pressão de hoje contra o próximo dia útil monitorado.'),
            $this->trendPayload('Concluídas hoje', $completedToday, $completedYesterday, 'up', 'Entregas concluídas hoje comparadas com ontem.'),
            $this->trendPayload('Risco operacional', $riskNow, $riskReference, 'down', 'Soma estimada de prazos críticos, correções e bloqueios.'),
        ];
    }

    protected function trendPayload(string $label, int $current, int $previous, string $goodWhen, string $hint): array
    {
        $delta = $current - $previous;
        $direction = $delta > 0 ? 'up' : ($delta < 0 ? 'down' : 'stable');
        $isGood = $direction === 'stable' || $direction === $goodWhen;
        $absDelta = abs($delta);

        return [
            'label' => $label,
            'current' => $current,
            'current_label' => number_format($current, 0, ',', '.'),
            'previous' => $previous,
            'delta' => $delta,
            'direction' => $direction,
            'tone' => $isGood ? 'success' : 'danger',
            'delta_label' => $direction === 'stable'
                ? 'estável'
                : ($direction === 'up' ? '+' : '-') . number_format($absDelta, 0, ',', '.'),
            'hint' => $hint,
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

        if ($item->data_vencimento && $item->data_vencimento->copy()->startOfDay()->lessThan(now()->startOfDay())) {
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

    protected function applyDashboardFilters(Builder $query, array $filters = []): Builder
    {
        $dateRange = (string) ($filters['date_range'] ?? 'today');
        $status = (string) ($filters['status'] ?? 'all');
        $department = (string) ($filters['department'] ?? 'all');

        if ($dateRange !== 'all') {
            $query->where(function (Builder $query) use ($dateRange, $filters): void {
                $query->whereNull('data_vencimento');

                if ($dateRange === 'today') {
                    $query->orWhereDate('data_vencimento', '<=', now()->toDateString());
                } elseif ($dateRange === 'yesterday') {
                    $query->orWhereDate('data_vencimento', now()->subDay()->toDateString());
                } elseif ($dateRange === 'last_7_days') {
                    $query->orWhereBetween('data_vencimento', [now()->subDays(6)->toDateString(), now()->toDateString()]);
                } elseif ($dateRange === 'last_30_days') {
                    $query->orWhereBetween('data_vencimento', [now()->subDays(29)->toDateString(), now()->toDateString()]);
                } elseif ($dateRange === 'custom') {
                    $startDate = filled($filters['custom_start_date'] ?? null)
                        ? Carbon::parse($filters['custom_start_date'])->toDateString()
                        : now()->subDays(7)->toDateString();
                    $endDate = filled($filters['custom_end_date'] ?? null)
                        ? Carbon::parse($filters['custom_end_date'])->toDateString()
                        : now()->toDateString();

                    if ($startDate > $endDate) {
                        [$startDate, $endDate] = [$endDate, $startDate];
                    }

                    $query->orWhereBetween('data_vencimento', [$startDate, $endDate]);
                }
            });
        }

        if ($status !== 'all') {
            if ($status === 'risk') {
                $blockColumns = $this->blockColumns();
                $query->whereNotIn('status', ['concluido', 'aprovado', 'cancelado'])
                    ->where(function (Builder $query) use ($blockColumns): void {
                        $query->where(function (Builder $query): void {
                            $query->whereNotNull('data_vencimento')
                                ->whereDate('data_vencimento', '<=', now()->toDateString());
                        })->orWhereIn('status', ['correcao_necessaria', 'reprovado']);

                        foreach ($blockColumns as $column) {
                            $query->orWhere($column, true);
                        }
                    });
            } elseif ($status === 'late') {
                $query->whereNotIn('status', ['concluido', 'aprovado', 'cancelado'])
                    ->whereNotNull('data_vencimento')
                    ->whereDate('data_vencimento', '<', now()->toDateString());
            } elseif ($status === 'approval') {
                $query->whereIn('status', ['aguardando_aprovacao', 'em_aprovacao']);
            } elseif ($status === 'correction') {
                $query->whereIn('status', ['correcao_necessaria', 'reprovado']);
            } elseif ($status === 'financial') {
                $query->whereIn('status', ['concluido', 'aprovado', 'assinado'])
                    ->when($this->hasColumn('faturado_em'), fn (Builder $query): Builder => $query->whereNull('faturado_em'))
                    ->when(! $this->hasColumn('faturado_em'), fn (Builder $query): Builder => $query->where(function (Builder $query): void {
                        $query->whereNull('contrato_status')->orWhereNotIn('contrato_status', ['faturado', 'pago']);
                    }));
            } elseif ($status === 'no_owner') {
                $query->whereNotIn('status', ['concluido', 'aprovado', 'cancelado'])
                    ->whereNull('responsavel_id');
            } elseif ($status === 'blocked') {
                $blockColumns = $this->blockColumns();
                $query->whereNotIn('status', ['concluido', 'aprovado', 'cancelado'])
                    ->when(empty($blockColumns), fn (Builder $query): Builder => $query->whereRaw('1 = 0'))
                    ->when(! empty($blockColumns), fn (Builder $query): Builder => $query->where(function (Builder $query) use ($blockColumns): void {
                        foreach ($blockColumns as $column) {
                            $query->orWhere($column, true);
                        }
                    }));
            } else {
                $query->where('status', $status);
            }
        }

        if ($department !== 'all') {
            $terms = $this->departmentTerms($department);
            $query->where(function (Builder $query) use ($terms): void {
                foreach ($terms as $term) {
                    $query->orWhereRaw('LOWER(tipo) LIKE ?', ['%' . $term . '%'])
                        ->orWhereRaw('LOWER(titulo) LIKE ?', ['%' . $term . '%'])
                        ->orWhereHas('categoria', fn (Builder $query): Builder => $query->whereRaw('LOWER(nome) LIKE ?', ['%' . $term . '%']));
                }
            });
        }

        return $query;
    }

    protected function departmentTerms(string $department): array
    {
        return match ($department) {
            'Fiscal' => ['fiscal', 'nota', 'sped', 'defis', 'reinf'],
            'Contábil' => ['contáb', 'contab'],
            'DP' => ['folha', 'dp', 'trabalh'],
            'Societário' => ['contrato', 'societ'],
            'Financeiro' => ['finance', 'cobran', 'fatura'],
            default => [strtolower($department)],
        };
    }

    protected function statusOptions(): array
    {
        return [
            'all' => 'Todos os status',
            'risk' => 'Risco operacional',
            'late' => 'Vencidas',
            'approval' => 'Aprovações',
            'correction' => 'Correção necessária',
            'financial' => 'Pendências financeiras',
            'no_owner' => 'Sem responsável',
            'blocked' => 'Clientes bloqueados',
            'pendente' => 'Pendentes',
            'em_andamento' => 'Em andamento',
        ];
    }

    protected function departmentOptions(): array
    {
        return [
            'all' => 'Todos os departamentos',
            'Fiscal' => 'Fiscal',
            'Contábil' => 'Contábil',
            'DP' => 'Departamento Pessoal',
            'Societário' => 'Societário',
            'Financeiro' => 'Financeiro',
            'Operacional' => 'Operacional',
        ];
    }

    protected function dateRangeOptions(): array
    {
        return [
            'all' => 'Todos',
            'today' => 'Hoje',
            'yesterday' => 'Ontem',
            'last_7_days' => 'Últimos 7 dias',
            'last_30_days' => 'Últimos 30 dias',
            'custom' => 'Personalizado',
        ];
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
