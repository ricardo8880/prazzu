<?php

namespace App\Support;

use App\Filament\Pages\Atendimentos;
use App\Filament\Pages\CentralAprovacoes;
use App\Filament\Pages\Documentos;
use App\Filament\Resources\ItemControles\ItemControleResource;
use App\Models\ItemControle;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class ComplianceModuleData
{
    public static function auditoria(array $filters = []): array
    {
        $startedAt = microtime(true);
        $user = auth()->user();

        self::auditDebug('auditoria:start', [
            'user_id' => $user?->id,
            'empresa_id' => $user?->empresa_id,
            'is_super_admin' => $user ? self::isSuperAdmin($user) : false,
            'filters' => self::compactAuditDebugContext($filters),
            'has_history_focus' => self::hasAuditHistoryFocus($filters),
        ]);

        $payload = [
            'stats' => [
                self::stat('Eventos auditados', self::countAudits($user, $filters), 'Alterações registradas no sistema'),
                self::stat('Ações críticas', self::countCriticalAudits($user, $filters), 'Exclusões, reprovações e mudanças sensíveis'),
                self::stat('Usuários ativos', self::countActiveUsers($user), 'Com acesso recente ou vinculados à operação'),
                self::stat('Itens com evidência', self::countItemsWithEvidence($user), 'Itens com documento, assinatura ou aprovação'),
            ],
            'timeline' => self::auditTimeline($user, self::hasAuditHistoryFocus($filters) ? 120 : 30, $filters),
            'historyContext' => self::auditHistoryContext($user, $filters),
            'byUser' => self::auditsByUser($user, $filters),
            'byEvent' => self::auditsByEvent($user, $filters),
            'byModule' => self::auditsByModule($user, $filters),
            'filterOptions' => self::auditFilterOptions($user),
            'recentApprovals' => self::recentApprovals($user),
        ];

        self::auditDebug('auditoria:end', [
            'elapsed_ms' => round((microtime(true) - $startedAt) * 1000, 2),
            'stats_count' => count($payload['stats'] ?? []),
            'timeline_count' => count($payload['timeline'] ?? []),
            'by_user_count' => count($payload['byUser'] ?? []),
            'by_event_count' => count($payload['byEvent'] ?? []),
            'history_active' => (bool) ($payload['historyContext']['active'] ?? false),
        ]);

        return $payload;
    }


    private static function auditDebug(string $step, array $context = []): void
    {
        if (! self::auditDebugEnabled()) {
            return;
        }

        try {
            $request = request();

            Log::debug('[AUDITORIA_DEBUG] ' . $step, array_merge([
                'url' => $request?->fullUrl(),
                'method' => $request?->method(),
                'route' => $request?->route()?->getName(),
                'request_id' => $request?->headers->get('X-Request-Id'),
                'ip' => $request?->ip(),
                'user_id_auth' => auth()->id(),
            ], self::compactAuditDebugContext($context)));
        } catch (\Throwable $exception) {
            // Evita que o diagnóstico quebre a tela de auditoria.
        }
    }

    private static function auditDebugEnabled(): bool
    {
        return filter_var(env('COMPLIANCE_AUDIT_DEBUG', config('app.debug', false)), FILTER_VALIDATE_BOOLEAN);
    }

    private static function compactAuditDebugContext(array $context): array
    {
        return collect($context)
            ->map(function ($value) {
                if (is_array($value)) {
                    return self::compactAuditDebugContext($value);
                }

                if (is_object($value)) {
                    return method_exists($value, '__toString') ? (string) $value : get_class($value);
                }

                if (is_string($value)) {
                    return self::short($value, 500);
                }

                return $value;
            })
            ->all();
    }


    public static function auditoriaExportHeadings(): array
    {
        return [
            'Data/Hora',
            'Usuário',
            'Empresa',
            'Evento',
            'Criticidade',
            'Módulo',
            'Tipo auditado',
            'ID do registro',
            'Campo',
            'Resumo da alteração',
            'Valor anterior',
            'Valor novo',
            'IP',
            'User Agent',
        ];
    }

    public static function auditoriaExportRows($user = null, array $filters = []): array
    {
        $user ??= auth()->user();

        if (! CachedSchema::hasTable('auditoria_detalhada')) {
            return [];
        }

        $query = DB::table('auditoria_detalhada as a')
            ->leftJoin('users as u', 'u.id', '=', 'a.user_id')
            ->leftJoin('empresas as e', 'e.id', '=', 'a.empresa_id')
            ->select('a.*', 'u.name as user_name', DB::raw('COALESCE(e.nome_fantasia, e.razao_social) as empresa_nome'))
            ->orderByDesc('a.created_at');

        self::scopeCompany($query, $user, 'a.empresa_id');
        self::applyAuditFilters($query, $filters, 'a');

        return $query->get()->map(function ($row): array {
            $diffRows = self::auditDiffRows($row->valor_anterior, $row->valor_novo);
            $summary = self::auditTimelineSummary($diffRows, $row->campo, $row->evento);
            $criticality = self::auditCriticality($row->evento, $row->campo, $row->valor_anterior, $row->valor_novo, $diffRows);

            return [
                'data_hora' => $row->created_at ? Carbon::parse($row->created_at)->format('d/m/Y H:i:s') : '-',
                'usuario' => (string) ($row->user_name ?: 'Sistema'),
                'empresa' => (string) ($row->empresa_nome ?: 'Sem empresa'),
                'evento' => ucfirst(str_replace('_', ' ', (string) ($row->evento ?: 'Evento'))),
                'criticidade' => $criticality['label'],
                'modulo' => class_basename($row->auditable_type ?: 'registro'),
                'tipo_auditado' => (string) ($row->auditable_type ?: '-'),
                'id_registro' => $row->auditable_id ? (string) $row->auditable_id : '-',
                'campo' => (string) ($row->campo ?: '-'),
                'resumo' => (string) ($summary['description'] ?? '-'),
                'valor_anterior' => self::normalizeAuditValue($row->valor_anterior) ?: '-',
                'valor_novo' => self::normalizeAuditValue($row->valor_novo) ?: '-',
                'ip' => (string) ($row->ip ?: '-'),
                'user_agent' => (string) ($row->user_agent ?: '-'),
            ];
        })->values()->all();
    }

    public static function riscos(): array
    {
        $user = auth()->user();
        $risks = self::riskItems($user, 50);

        return [
            'stats' => [
                self::stat('Riscos abertos', $risks->whereNotIn('status', ['concluido', 'cancelado'])->count(), 'Itens com score ou prioridade de risco'),
                self::stat('Críticos', $risks->where('risk_level', 'Crítico')->count(), 'Impacto alto e exige ação rápida'),
                self::stat('Atrasados', $risks->where('is_late', true)->count(), 'Riscos com prazo vencido'),
                self::stat('Score médio', (int) round($risks->avg('score') ?: 0), 'Baseado em probabilidade x impacto'),
            ],
            'risks' => $risks->values()->all(),
            'matrix' => self::riskMatrix($risks),
            'options' => self::formOptions($user),
        ];
    }

    public static function pendencias(): array
    {
        $user = auth()->user();
        $items = self::pendingItems($user, 80);

        return [
            'stats' => [
                self::stat('Pendências abertas', $items->whereNotIn('status', ['concluido', 'cancelado'])->count(), 'Tarefas que ainda precisam de ação'),
                self::stat('Vencidas', $items->where('is_late', true)->count(), 'Prazo expirado'),
                self::stat('Aprovação', $items->where('status', 'em_aprovacao')->count(), 'Esperando decisão'),
                self::stat('Alta prioridade', $items->whereIn('prioridade', ['alta', 'urgente'])->count(), 'Prioridade alta ou urgente'),
            ],
            'items' => $items->values()->all(),
            'byStatus' => self::pendingByStatus($items),
            'options' => self::formOptions($user),
        ];
    }

    public static function engine(): array
    {
        $user = auth()->user();
        $metrics = self::engineMetrics($user);

        $late = $metrics['late'];
        $critical = $metrics['critical'];
        $audits = $metrics['audits'];
        $evidence = $metrics['evidence'];
        $total = max(1, $metrics['total']);

        $score = 100;
        $score -= min(35, $late * 4);
        $score -= min(30, $critical * 6);
        $score += min(15, (int) round(($evidence / $total) * 15));
        $score = max(0, min(100, $score));

        return [
            'score' => $score,
            'stats' => [
                self::stat('Score de compliance', $score . '%', self::scoreHint($score)),
                self::stat('Riscos críticos', $critical, 'Prioridade máxima para mitigação'),
                self::stat('Pendências vencidas', $late, 'Itens que derrubam o score'),
                self::stat('Eventos auditados', $audits, 'Rastro de auditoria disponível'),
            ],
            'trend' => self::engineTrend($user, $score, $critical, $late, $evidence, $total),
            'recommendations' => self::recommendations($score, $critical, $late, $evidence, $total),
            'criticalRisks' => self::criticalRiskItems($user, 8)->values()->all(),
            'latePendings' => self::latePendingItems($user, 8)->values()->all(),
        ];
    }

    private static function engineMetrics($user): array
    {
        return [
            'late' => self::countLateVisibleItems($user),
            'critical' => self::countCriticalRiskItems($user),
            'audits' => self::countAudits($user),
            'evidence' => self::countItemsWithEvidence($user),
            'total' => self::countVisibleItems($user),
        ];
    }

    private static function engineTrend($user, int $score, int $critical, int $late, int $evidence, int $total): array
    {
        $now = Carbon::now();
        $currentStart = $now->copy()->subDays(7)->startOfDay();
        $previousStart = $now->copy()->subDays(14)->startOfDay();
        $previousEnd = $currentStart->copy()->subSecond();

        $criticalCurrent = self::countEngineCriticalRisksCreatedBetween($user, $currentStart, $now);
        $criticalPrevious = self::countEngineCriticalRisksCreatedBetween($user, $previousStart, $previousEnd);
        $lateCurrent = self::countEngineLateItemsDueBetween($user, $currentStart, $now);
        $latePrevious = self::countEngineLateItemsDueBetween($user, $previousStart, $previousEnd);
        $auditsCurrent = self::countAuditsBetween($user, $currentStart, $now);
        $auditsPrevious = self::countAuditsBetween($user, $previousStart, $previousEnd);

        $pressureCurrent = ($lateCurrent * 4) + ($criticalCurrent * 6);
        $pressurePrevious = ($latePrevious * 4) + ($criticalPrevious * 6);
        $pressureDelta = $pressureCurrent - $pressurePrevious;

        $tone = $pressureDelta > 0 ? 'danger' : ($pressureDelta < 0 ? 'ok' : ($late > 0 || $critical > 0 ? 'warning' : 'ok'));
        $label = $pressureDelta > 0 ? 'Piorou nos últimos 7 dias' : ($pressureDelta < 0 ? 'Melhorou nos últimos 7 dias' : 'Estável nos últimos 7 dias');
        $summary = match ($tone) {
            'danger' => 'A entrada recente de riscos ou vencimentos aumentou a pressão operacional. Priorize os novos focos antes que virem acúmulo.',
            'warning' => 'Não houve piora recente relevante, mas ainda existem itens críticos ou vencidos exigindo acompanhamento.',
            default => 'Os sinais recentes estão controlados. Mantenha a rotina de revisão para preservar o score atual.',
        };

        $evidencePercent = $total > 0 ? (int) round(($evidence / max(1, $total)) * 100) : 0;

        return [
            'label' => $label,
            'tone' => $tone,
            'summary' => $summary,
            'period' => 'Últimos 7 dias',
            'comparisonPeriod' => '7 dias anteriores',
            'note' => 'Comparação operacional calculada em tempo real a partir de itens, vencimentos e auditoria; não depende de migration nem de snapshot histórico.',
            'cards' => [
                [
                    'title' => 'Pressão operacional',
                    'value' => $pressureCurrent,
                    'previous' => $pressurePrevious,
                    'delta' => $pressureDelta,
                    'tone' => $tone,
                    'hint' => 'Peso recente de pendências vencidas e riscos críticos.',
                ],
                [
                    'title' => 'Novos riscos críticos',
                    'value' => $criticalCurrent,
                    'previous' => $criticalPrevious,
                    'delta' => $criticalCurrent - $criticalPrevious,
                    'tone' => $criticalCurrent > $criticalPrevious ? 'danger' : ($criticalCurrent < $criticalPrevious ? 'ok' : 'info'),
                    'hint' => 'Itens críticos criados no período atual.',
                ],
                [
                    'title' => 'Vencimentos recentes',
                    'value' => $lateCurrent,
                    'previous' => $latePrevious,
                    'delta' => $lateCurrent - $latePrevious,
                    'tone' => $lateCurrent > $latePrevious ? 'danger' : ($lateCurrent < $latePrevious ? 'ok' : 'info'),
                    'hint' => 'Pendências que venceram no período atual.',
                ],
                [
                    'title' => 'Auditoria recente',
                    'value' => $auditsCurrent,
                    'previous' => $auditsPrevious,
                    'delta' => $auditsCurrent - $auditsPrevious,
                    'tone' => $auditsCurrent >= $auditsPrevious ? 'info' : 'warning',
                    'hint' => 'Eventos registrados e rastreáveis.',
                ],
                [
                    'title' => 'Cobertura de evidências',
                    'value' => $evidencePercent . '%',
                    'previous' => null,
                    'delta' => null,
                    'tone' => $evidencePercent >= 70 ? 'ok' : ($evidencePercent >= 40 ? 'warning' : 'danger'),
                    'hint' => 'Proporção atual de itens com evidência auditável.',
                ],
            ],
        ];
    }

    private static function countEngineCriticalRisksCreatedBetween($user, Carbon $start, Carbon $end): int
    {
        if (! CachedSchema::hasTable('item_controles')) {
            return 0;
        }

        $q = DB::table('item_controles as i')
            ->whereBetween('i.created_at', [$start, $end])
            ->where(function ($w) {
                $w->whereRaw('COALESCE(i.risk_score, i.risco_score, i.risk_probability * i.risk_impact, 0) >= 15')
                    ->orWhere('i.prioridade', 'urgente');
            });

        self::scopeItemVisibility($q, $user, 'i');

        return (int) $q->count();
    }

    private static function countEngineLateItemsDueBetween($user, Carbon $start, Carbon $end): int
    {
        if (! CachedSchema::hasTable('item_controles')) {
            return 0;
        }

        $q = DB::table('item_controles as i')
            ->whereNotIn('i.status', ['concluido', 'cancelado'])
            ->whereBetween('i.data_vencimento', [$start->toDateString(), $end->toDateString()]);

        self::scopeItemVisibility($q, $user, 'i');

        return (int) $q->count();
    }

    private static function countAuditsBetween($user, Carbon $start, Carbon $end): int
    {
        if (! CachedSchema::hasTable('auditoria_detalhada')) {
            return 0;
        }

        $q = DB::table('auditoria_detalhada as a')
            ->whereBetween('a.created_at', [$start, $end]);

        self::scopeCompany($q, $user, 'a.empresa_id');

        return (int) $q->count();
    }

    public static function interno(): array
    {
        $user = auth()->user();

        $approvals = self::internoRowsWithUrgency(self::recentApprovals($user, 12));
        $signatures = self::internoRowsWithUrgency(self::recentSignatures($user, 12));
        $documents = self::internoRowsWithUrgency(self::recentDocuments($user, 12));
        $requests = self::internoRowsWithUrgency(self::recentRequests($user, 12));
        $workflow = self::internoWorkflow($approvals, $signatures, $documents, $requests);
        $myPendings = self::internoMyPendings($workflow['attention']['items'] ?? [], $user);

        return [
            'stats' => [
                self::stat('Aprovações pendentes', self::countApprovals($user, 'pendente'), 'Decisões internas aguardando retorno', 'approval', 'pendente'),
                self::stat('Assinaturas coletadas', self::countSignatures($user), 'Evidências de aceite vinculadas aos itens', 'signature', 'assinado'),
                self::stat('Documentos internos', self::countDocuments($user), 'Documentos, atas, wikis e links', 'document', null),
                self::stat('Solicitações abertas', self::countRequests($user), 'Pedidos internos ou do portal em aberto', 'request', null),
            ],
            'filters' => self::internoFilters(),
            'myPendings' => $myPendings,
            'workflow' => $workflow,
            'approvals' => $approvals,
            'signatures' => $signatures,
            'documents' => $documents,
            'requests' => $requests,
        ];
    }



    private static function internoRowsWithUrgency(array $rows): array
    {
        return collect($rows)
            ->map(function (array $row): array {
                $urgency = ComplianceInternoFormatter::urgency(
                    (string) ($row['type'] ?? 'registro'),
                    $row['rawStatus'] ?? null,
                    $row['rawPriority'] ?? null,
                    $row['workflowWeight'] ?? null
                );

                $row['urgency'] = $urgency;
                $row['urgencyLabel'] = $urgency['label'];
                $row['urgencyTone'] = $urgency['tone'];
                $row['urgencyRank'] = $urgency['rank'];
                $row['urgencyMessage'] = $urgency['message'];

                return $row;
            })
            ->sortBy([
                ['urgencyRank', 'asc'],
                ['workflowWeight', 'asc'],
            ])
            ->values()
            ->all();
    }

    private static function internoFilters(): array
    {
        return [
            'types' => [
                'approval' => 'Aprovações',
                'signature' => 'Assinaturas',
                'document' => 'Documentos',
                'request' => 'Solicitações',
            ],
            'statuses' => [
                'pendente' => 'Pendente',
                'nao_assinado' => 'Pendente de assinatura',
                'aberto' => 'Aberto',
                'em_andamento' => 'Em andamento',
                'em_aprovacao' => 'Em aprovação',
                'aprovado' => 'Aprovado',
                'assinado' => 'Assinado',
                'visivel' => 'Visível para o cliente',
                'interno' => 'Uso interno',
                'reprovado' => 'Reprovado',
                'cancelado' => 'Cancelado',
                'concluido' => 'Concluído',
            ],
            'priorities' => [
                'urgente' => 'Urgente',
                'alta' => 'Alta prioridade',
                'media' => 'Prioridade média',
                'baixa' => 'Baixa prioridade',
            ],
        ];
    }


    private static function internoMyPendings(array $attentionItems, $user): array
    {
        $userId = (int) ($user->id ?? 0);
        $userEmail = mb_strtolower(trim((string) ($user->email ?? '')));
        $userName = mb_strtolower(trim((string) ($user->name ?? '')));

        $items = collect($attentionItems);

        $mine = $items
            ->filter(function (array $row) use ($userId, $userEmail, $userName): bool {
                if ((bool) ($row['isAssignedToCurrentUser'] ?? false)) {
                    return true;
                }

                $assignedUserIds = collect($row['assignedUserIds'] ?? [])
                    ->map(fn ($id) => (int) $id)
                    ->filter()
                    ->values()
                    ->all();

                if ($userId > 0 && in_array($userId, $assignedUserIds, true)) {
                    return true;
                }

                $assignedEmails = collect($row['assignedEmails'] ?? [])
                    ->map(fn ($email) => mb_strtolower(trim((string) $email)))
                    ->filter()
                    ->values()
                    ->all();

                if ($userEmail !== '' && in_array($userEmail, $assignedEmails, true)) {
                    return true;
                }

                $searchable = mb_strtolower((string) ($row['searchable'] ?? ''));

                return ($userEmail !== '' && str_contains($searchable, $userEmail))
                    || ($userName !== '' && str_contains($searchable, $userName));
            })
            ->sortBy([['urgencyRank', 'asc'], ['workflowWeight', 'asc']])
            ->take(6)
            ->values();

        $usingPersonalMatch = $mine->isNotEmpty();
        $visibleItems = ($usingPersonalMatch ? $mine : $items->sortBy([['urgencyRank', 'asc'], ['workflowWeight', 'asc']])->take(6)->values())->all();

        return [
            'title' => $usingPersonalMatch ? 'Pendências' : 'Pendências para resolver primeiro',
            'subtitle' => $usingPersonalMatch
                ? 'Itens que parecem estar vinculados ao usuário logado e precisam de decisão, assinatura ou acompanhamento.'
                : 'Não encontramos pendências diretamente vinculadas ao usuário logado; por segurança, exibimos as prioridades gerais da governança interna.',
            'empty' => 'Nenhuma pendência prioritária encontrada agora.',
            'items' => $visibleItems,
            'count' => count($visibleItems),
            'tone' => count($visibleItems) > 0 ? 'danger' : 'ok',
            'personalized' => $usingPersonalMatch,
        ];
    }

    private static function internoWorkflow(array $approvals, array $signatures, array $documents, array $requests): array
    {
        $attention = collect($approvals)
            ->filter(fn (array $row): bool => in_array($row['rawStatus'] ?? '', ['pendente', 'em_aprovacao', 'aberto'], true))
            ->merge(collect($signatures)->filter(fn (array $row): bool => ! (bool) ($row['signed'] ?? false)))
            ->merge(collect($requests)->filter(fn (array $row): bool => in_array($row['rawPriority'] ?? '', ['alta', 'urgente'], true) || in_array($row['rawStatus'] ?? '', ['pendente', 'aberto'], true)))
            ->sortBy([['urgencyRank', 'asc'], ['workflowWeight', 'asc']])
            ->take(8)
            ->values()
            ->all();

        $inProgress = collect($requests)
            ->filter(fn (array $row): bool => in_array($row['rawStatus'] ?? '', ['em_andamento', 'em_aprovacao'], true))
            ->merge(collect($approvals)->filter(fn (array $row): bool => in_array($row['rawStatus'] ?? '', ['em_andamento'], true)))
            ->sortBy([['urgencyRank', 'asc'], ['workflowWeight', 'asc']])
            ->take(8)
            ->values()
            ->all();

        $reference = collect($documents)
            ->merge(collect($approvals)->filter(fn (array $row): bool => in_array($row['rawStatus'] ?? '', ['aprovado', 'reprovado', 'concluido', 'cancelado'], true)))
            ->merge(collect($signatures)->filter(fn (array $row): bool => (bool) ($row['signed'] ?? false)))
            ->sortBy([['urgencyRank', 'asc'], ['workflowWeight', 'asc']])
            ->take(10)
            ->values()
            ->all();

        return [
            'attention' => [
                'title' => 'Precisa da sua atenção',
                'subtitle' => 'Itens que merecem análise primeiro: aprovações pendentes, assinaturas em aberto e solicitações críticas.',
                'empty' => 'Nenhuma pendência crítica encontrada agora.',
                'items' => $attention,
                'count' => count($attention),
                'tone' => count($attention) > 0 ? 'danger' : 'ok',
            ],
            'inProgress' => [
                'title' => 'Em andamento',
                'subtitle' => 'Processos que já estão em fluxo e precisam apenas de acompanhamento.',
                'empty' => 'Nenhum processo em andamento encontrado.',
                'items' => $inProgress,
                'count' => count($inProgress),
                'tone' => count($inProgress) > 0 ? 'info' : 'ok',
            ],
            'reference' => [
                'title' => 'Consulta e histórico recente',
                'subtitle' => 'Documentos, registros concluídos e evidências recentes para consulta rápida.',
                'empty' => 'Nenhum documento ou histórico recente encontrado.',
                'items' => $reference,
                'count' => count($reference),
                'tone' => 'info',
            ],
        ];
    }

    public static function formOptions($user = null): array
    {
        $user ??= auth()->user();

        $empresas = collect();
        if (CachedSchema::hasTable('empresas')) {
            $q = DB::table('empresas')->select('id', DB::raw('COALESCE(nome_fantasia, razao_social) as nome'))->orderBy('nome');
            if ($user && ! self::isSuperAdmin($user)) {
                $q->where('id', $user->empresa_id ?: 0);
            }
            $empresas = $q->limit(200)->get();
        }

        $responsaveis = collect();
        if (CachedSchema::hasTable('responsaveis')) {
            $q = DB::table('responsaveis')->select('id', 'nome', 'empresa_id')->orderBy('nome');
            if ($user && ! self::isSuperAdmin($user)) {
                $q->where('empresa_id', $user->empresa_id ?: 0);
            }
            $responsaveis = $q->limit(300)->get();
        }

        return [
            'empresas' => $empresas->map(fn ($e) => ['id' => (int) $e->id, 'nome' => $e->nome])->all(),
            'responsaveis' => $responsaveis->map(fn ($r) => ['id' => (int) $r->id, 'nome' => $r->nome, 'empresa_id' => (int) $r->empresa_id])->all(),
        ];
    }

    public static function resolveEmpresaId(?int $empresaId, $user = null): ?int
    {
        $user ??= auth()->user();
        if (! $user) {
            return null;
        }
        if (! self::isSuperAdmin($user)) {
            return $user->empresa_id ? (int) $user->empresa_id : null;
        }
        return $empresaId ?: (CachedSchema::hasTable('empresas') ? (int) DB::table('empresas')->orderBy('id')->value('id') : null);
    }

    public static function resolveResponsavelId(?int $responsavelId, ?int $empresaId, $user = null): ?int
    {
        $user ??= auth()->user();
        if (! CachedSchema::hasTable('responsaveis')) {
            return null;
        }
        if ($responsavelId) {
            $exists = DB::table('responsaveis')->where('id', $responsavelId)->when($empresaId, fn ($q) => $q->where('empresa_id', $empresaId))->exists();
            if ($exists) {
                return $responsavelId;
            }
        }
        if ($user && isset($user->responsavel) && $user->responsavel?->id) {
            return (int) $user->responsavel->id;
        }
        return $empresaId ? (int) DB::table('responsaveis')->where('empresa_id', $empresaId)->orderBy('id')->value('id') : null;
    }

    private static function baseItems($user)
    {
        $q = ItemControle::query()->select(['id','titulo','descricao','tipo','status','prioridade','data_vencimento','data_conclusao','empresa_id','responsavel_id','created_at','updated_at','approval_status','document_status','risk_probability','risk_impact','risk_score','risco_score']);
        return $q->visibleForUser($user)->with(['empresa:id,razao_social,nome_fantasia','responsavel:id,nome']);
    }

    private static function criticalRiskItems($user, int $limit)
    {
        if (! CachedSchema::hasTable('item_controles')) {
            return collect();
        }

        return self::baseItems($user)
            ->where(function ($q) {
                $q->whereRaw('COALESCE(risk_score, risco_score, risk_probability * risk_impact, 0) >= 15')
                    ->orWhere('prioridade', 'urgente');
            })
            ->orderByRaw('COALESCE(risk_score, risco_score, risk_probability * risk_impact, 0) DESC')
            ->orderBy('data_vencimento')
            ->limit($limit)
            ->get()
            ->map(fn ($item) => self::formatItem($item, true));
    }

    private static function latePendingItems($user, int $limit)
    {
        if (! CachedSchema::hasTable('item_controles')) {
            return collect();
        }

        return self::baseItems($user)
            ->whereNotIn('status', ['concluido', 'cancelado'])
            ->whereNotNull('data_vencimento')
            ->whereDate('data_vencimento', '<', Carbon::today())
            ->orderBy('data_vencimento')
            ->orderByRaw("CASE WHEN prioridade = 'urgente' THEN 0 WHEN prioridade = 'alta' THEN 1 ELSE 2 END")
            ->limit($limit)
            ->get()
            ->map(fn ($item) => self::formatItem($item));
    }

    private static function riskItems($user, int $limit)
    {
        if (! CachedSchema::hasTable('item_controles')) {
            return collect();
        }

        return self::baseItems($user)
            ->where(function ($q) {
                $q->whereNotNull('risk_score')
                    ->orWhereNotNull('risco_score')
                    ->orWhereNotNull('risk_probability')
                    ->orWhereNotNull('risk_impact')
                    ->orWhereIn('prioridade', ['alta', 'urgente'])
                    ->orWhere('tipo', 'like', '%risco%');
            })
            ->orderByRaw('COALESCE(risk_score, risco_score, risk_probability * risk_impact, 0) DESC')
            ->orderBy('data_vencimento')
            ->limit($limit)
            ->get()
            ->map(fn ($item) => self::formatItem($item, true));
    }

    private static function pendingItems($user, int $limit)
    {
        if (! CachedSchema::hasTable('item_controles')) {
            return collect();
        }

        return self::baseItems($user)
            ->whereNotIn('status', ['concluido', 'cancelado'])
            ->orderByRaw("CASE WHEN data_vencimento IS NOT NULL AND data_vencimento < CURDATE() THEN 0 WHEN prioridade = 'urgente' THEN 1 WHEN prioridade = 'alta' THEN 2 ELSE 3 END")
            ->orderBy('data_vencimento')
            ->limit($limit)
            ->get()
            ->map(fn ($item) => self::formatItem($item));
    }

    private static function formatItem($item, bool $risk = false): array
    {
        $due = $item->data_vencimento ? Carbon::parse($item->data_vencimento) : null;
        $score = (int) ($item->risk_score ?: $item->risco_score ?: (($item->risk_probability ?: 0) * ($item->risk_impact ?: 0)));
        $late = $due && $due->isPast() && ! in_array($item->status, ['concluido', 'cancelado'], true);

        return [
            'id' => (int) $item->id,
            'titulo' => $item->titulo,
            'descricao' => $item->descricao,
            'empresa' => $item->empresa?->nome_fantasia ?: $item->empresa?->razao_social ?: 'Sem empresa',
            'responsavel' => $item->responsavel?->nome ?: 'Sem responsável',
            'status' => $item->status,
            'prioridade' => $item->prioridade,
            'vencimento' => $due ? $due->format('d/m/Y') : 'Sem prazo',
            'is_late' => $late,
            'score' => $score,
            'risk_level' => self::riskLevel($score, $item->prioridade),
            'tone' => $late || $score >= 15 || $item->prioridade === 'urgente' ? 'danger' : ($score >= 9 || $item->prioridade === 'alta' ? 'warning' : 'ok'),
            'url' => url('/admin/item-controles/' . $item->id . '/edit'),
        ];
    }

    private static function riskLevel(int $score, ?string $prioridade): string
    {
        if ($score >= 15 || $prioridade === 'urgente') return 'Crítico';
        if ($score >= 9 || $prioridade === 'alta') return 'Alto';
        if ($score >= 4) return 'Médio';
        return 'Baixo';
    }

    private static function riskMatrix($risks): array
    {
        return collect(['Crítico','Alto','Médio','Baixo'])->map(fn ($level) => [
            'label' => $level,
            'count' => $risks->where('risk_level', $level)->count(),
        ])->all();
    }

    private static function pendingByStatus($items): array
    {
        return $items->groupBy('status')->map(fn ($rows, $status) => ['label' => ucfirst(str_replace('_', ' ', $status)), 'count' => $rows->count()])->values()->all();
    }

    private static function countLateVisibleItems($user): int
    {
        if (! CachedSchema::hasTable('item_controles')) {
            return 0;
        }

        $q = DB::table('item_controles as i')
            ->whereNotIn('i.status', ['concluido', 'cancelado'])
            ->whereNotNull('i.data_vencimento')
            ->whereDate('i.data_vencimento', '<', Carbon::today());

        self::scopeItemVisibility($q, $user, 'i');

        return (int) $q->count();
    }

    private static function countCriticalRiskItems($user): int
    {
        if (! CachedSchema::hasTable('item_controles')) {
            return 0;
        }

        $q = DB::table('item_controles as i')
            ->where(function ($w) {
                $w->whereRaw('COALESCE(i.risk_score, i.risco_score, i.risk_probability * i.risk_impact, 0) >= 15')
                    ->orWhere('i.prioridade', 'urgente');
            });

        self::scopeItemVisibility($q, $user, 'i');

        return (int) $q->count();
    }

    private static function countVisibleItems($user): int
    {
        return CachedSchema::hasTable('item_controles') ? self::baseItems($user)->count() : 0;
    }

    private static function countAudits($user, array $filters = []): int
    {
        if (CachedSchema::hasTable('auditoria_detalhada')) {
            $q = DB::table('auditoria_detalhada as a');
            self::scopeCompany($q, $user, 'a.empresa_id');
            self::applyAuditFilters($q, $filters, 'a');
            return (int) $q->count();
        }
        if (CachedSchema::hasTable('audit_timeline')) {
            return (int) DB::table('audit_timeline')->count();
        }
        return 0;
    }

    private static function countCriticalAudits($user, array $filters = []): int
    {
        if (! CachedSchema::hasTable('auditoria_detalhada')) return 0;
        $q = DB::table('auditoria_detalhada as a')->where(function ($w) {
            $w->whereIn('a.evento', ['deleted','excluido','reprovado','cancelado'])
                ->orWhere('a.campo', 'like', '%status%')
                ->orWhere('a.campo', 'like', '%valor%')
                ->orWhere('a.campo', 'like', '%permiss%');
        });
        self::scopeCompany($q, $user, 'a.empresa_id');
        self::applyAuditFilters($q, $filters, 'a');
        return (int) $q->count();
    }

    private static function countActiveUsers($user): int
    {
        if (! CachedSchema::hasTable('users')) return 0;
        $q = DB::table('users as u');
        self::scopeCompany($q, $user, 'u.empresa_id');
        return (int) $q->count();
    }

    private static function countItemsWithEvidence($user): int
    {
        if (! CachedSchema::hasTable('item_controles')) {
            return 0;
        }

        $hasApprovals = CachedSchema::hasTable('item_controle_aprovacoes');
        $hasSignatures = CachedSchema::hasTable('item_controle_assinaturas');

        $q = DB::table('item_controles as i')
            ->where(function ($w) use ($hasApprovals, $hasSignatures) {
                $w->whereNotNull('i.arquivo')
                    ->orWhereNotNull('i.document_status');

                if ($hasApprovals) {
                    $w->orWhereExists(fn ($s) => $s->selectRaw('1')->from('item_controle_aprovacoes as ap')->whereColumn('ap.item_controle_id', 'i.id'));
                }

                if ($hasSignatures) {
                    $w->orWhereExists(fn ($s) => $s->selectRaw('1')->from('item_controle_assinaturas as ass')->whereColumn('ass.item_controle_id', 'i.id'));
                }
            });

        self::scopeItemVisibility($q, $user, 'i');

        return (int) $q->count();
    }

    private static function auditTimeline($user, int $limit = 20, array $filters = []): array
    {
        self::auditDebug('auditTimeline:start', [
            'limit' => $limit,
            'filters' => self::compactAuditDebugContext($filters),
            'has_table_auditoria_detalhada' => CachedSchema::hasTable('auditoria_detalhada'),
        ]);

        if (! CachedSchema::hasTable('auditoria_detalhada')) {
            self::auditDebug('auditTimeline:table_missing');
            return [];
        }

        $q = DB::table('auditoria_detalhada as a')
            ->leftJoin('users as u', 'u.id', '=', 'a.user_id')
            ->leftJoin('empresas as e', 'e.id', '=', 'a.empresa_id')
            ->select('a.*', 'u.name as user_name', DB::raw('COALESCE(e.nome_fantasia, e.razao_social) as empresa_nome'))
            ->orderByDesc('a.created_at')
            ->limit($limit);
        self::scopeCompany($q, $user, 'a.empresa_id');
        self::applyAuditFilters($q, $filters, 'a');

        $rows = $q->get();
        self::auditDebug('auditTimeline:query_result', [
            'row_count' => $rows->count(),
            'first_id' => $rows->first()?->id,
            'last_id' => $rows->last()?->id,
            'ids' => $rows->pluck('id')->take(30)->values()->all(),
        ]);

        $events = $rows->map(function ($row): array {
            $module = class_basename($row->auditable_type ?: 'registro');
            $eventLabel = ucfirst(str_replace('_', ' ', (string) ($row->evento ?: 'Evento')));
            $oldValue = self::normalizeAuditValue($row->valor_anterior);
            $newValue = self::normalizeAuditValue($row->valor_novo);
            $diffRows = self::auditDiffRows($row->valor_anterior, $row->valor_novo);
            $timelineSummary = self::auditTimelineSummary($diffRows, $row->campo, $row->evento);
            $criticality = self::auditCriticality($row->evento, $row->campo, $row->valor_anterior, $row->valor_novo, $diffRows);

            return [
                'id' => (int) $row->id,
                'title' => $eventLabel . ' em ' . $module,
                'meta' => ($row->empresa_nome ?: 'Sem empresa') . ' · ' . ($row->user_name ?: 'Sistema'),
                'description' => $timelineSummary['description'],
                'change_summary' => $timelineSummary,
                'primary_change' => $timelineSummary['primary'],
                'date' => $row->created_at ? Carbon::parse($row->created_at)->format('d/m/Y H:i') : '-',
                'date_full' => $row->created_at ? Carbon::parse($row->created_at)->format('d/m/Y H:i:s') : '-',
                'created_at_iso' => $row->created_at ? Carbon::parse($row->created_at)->toIso8601String() : null,
                'tone' => $criticality['tone'],
                'criticality' => $criticality,
                'criticality_key' => $criticality['key'],
                'criticality_label' => $criticality['label'],
                'criticality_hint' => $criticality['hint'],
                'event_label' => $eventLabel,
                'event_raw' => (string) ($row->evento ?: '-'),
                'user_id' => $row->user_id ? (string) $row->user_id : 'sistema',
                'company_id' => $row->empresa_id ? (string) $row->empresa_id : '',
                'module' => $module,
                'auditable_type' => (string) ($row->auditable_type ?: '-'),
                'auditable_type_filter' => (string) ($row->auditable_type ?: ''),
                'auditable_id' => $row->auditable_id ? (string) $row->auditable_id : '-',
                'auditable_id_filter' => $row->auditable_id ? (string) $row->auditable_id : '',
                'field' => (string) ($row->campo ?: '-'),
                'old_value' => $oldValue !== '' ? $oldValue : '-',
                'new_value' => $newValue !== '' ? $newValue : '-',
                'diff_rows' => $diffRows,
                'has_changes' => collect($diffRows)->contains(fn ($diff) => ($diff['status'] ?? 'unchanged') !== 'unchanged'),
                'company' => (string) ($row->empresa_nome ?: 'Sem empresa'),
                'user' => (string) ($row->user_name ?: 'Sistema'),
                'ip' => (string) ($row->ip ?: '-'),
                'user_agent' => self::short((string) ($row->user_agent ?: '-'), 220),
            ];
        })->values();

        $alertedEvents = self::applyAuditAlerts($events)->all();

        self::auditDebug('auditTimeline:end', [
            'events_count' => count($alertedEvents),
            'alerted_count' => collect($alertedEvents)->where('alert', true)->count(),
            'modal_ids_preview' => collect($alertedEvents)->pluck('id')->take(20)->values()->all(),
        ]);

        return $alertedEvents;
    }

    private static function applyAuditAlerts($events)
    {
        return $events->map(function (array $event) use ($events): array {
            $event['alert'] = false;
            $event['alert_type'] = null;
            $event['alert_label'] = null;
            $event['alert_description'] = null;
            $event['alert_tone'] = 'warning';

            $eventDate = self::parseAuditEventDate($event['created_at_iso'] ?? null);
            if (! $eventDate) {
                return $event;
            }

            $userId = (string) ($event['user_id'] ?? 'sistema');
            $field = mb_strtolower(trim((string) ($event['field'] ?? '')));
            $auditableType = (string) ($event['auditable_type_filter'] ?? '');
            $auditableId = (string) ($event['auditable_id_filter'] ?? '');
            $eventRaw = mb_strtolower(trim((string) ($event['event_raw'] ?? '')));

            $sameUserWindow = $events->filter(function (array $candidate) use ($userId, $eventDate): bool {
                if ((string) ($candidate['user_id'] ?? 'sistema') !== $userId) {
                    return false;
                }

                $candidateDate = self::parseAuditEventDate($candidate['created_at_iso'] ?? null);
                return $candidateDate && abs($candidateDate->diffInMinutes($eventDate, false)) <= 10;
            });

            $criticalWindow = $events->filter(function (array $candidate) use ($userId, $eventDate): bool {
                if ((string) ($candidate['user_id'] ?? 'sistema') !== $userId) {
                    return false;
                }

                $candidateDate = self::parseAuditEventDate($candidate['created_at_iso'] ?? null);
                return $candidateDate
                    && abs($candidateDate->diffInMinutes($eventDate, false)) <= 30
                    && (($candidate['criticality_key'] ?? '') === 'alta');
            });

            $destructiveEvents = ['deleted', 'delete', 'destroyed', 'excluido', 'excluida', 'removido', 'removida', 'removed'];
            $destructiveWindow = $events->filter(function (array $candidate) use ($userId, $eventDate, $destructiveEvents): bool {
                if ((string) ($candidate['user_id'] ?? 'sistema') !== $userId) {
                    return false;
                }

                $candidateDate = self::parseAuditEventDate($candidate['created_at_iso'] ?? null);
                $candidateEvent = mb_strtolower(trim((string) ($candidate['event_raw'] ?? '')));

                return $candidateDate
                    && abs($candidateDate->diffInMinutes($eventDate, false)) <= 30
                    && in_array($candidateEvent, $destructiveEvents, true);
            });

            $sameFieldWindow = $events->filter(function (array $candidate) use ($userId, $eventDate, $field, $auditableType, $auditableId): bool {
                if ($field === '' || $field === '-' || $auditableId === '' || $auditableType === '') {
                    return false;
                }

                if ((string) ($candidate['user_id'] ?? 'sistema') !== $userId) {
                    return false;
                }

                if ((string) ($candidate['auditable_type_filter'] ?? '') !== $auditableType || (string) ($candidate['auditable_id_filter'] ?? '') !== $auditableId) {
                    return false;
                }

                if (mb_strtolower(trim((string) ($candidate['field'] ?? ''))) !== $field) {
                    return false;
                }

                $candidateDate = self::parseAuditEventDate($candidate['created_at_iso'] ?? null);
                return $candidateDate && abs($candidateDate->diffInMinutes($eventDate, false)) <= 60;
            });

            if ($sameUserWindow->count() >= 10) {
                return self::markAuditAlert($event, 'volume', 'Alta frequência de ações', 'Este usuário gerou ' . $sameUserWindow->count() . ' eventos em uma janela de até 10 minutos. Verifique se foi uma operação esperada ou um comportamento anormal.');
            }

            if ($criticalWindow->count() >= 3) {
                return self::markAuditAlert($event, 'sequencia_critica', 'Sequência crítica', 'Foram identificados ' . $criticalWindow->count() . ' eventos de alta criticidade do mesmo usuário em até 30 minutos.');
            }

            if (in_array($eventRaw, $destructiveEvents, true) && $destructiveWindow->count() >= 2) {
                return self::markAuditAlert($event, 'exclusao', 'Exclusões em sequência', 'Foram identificados ' . $destructiveWindow->count() . ' eventos destrutivos do mesmo usuário em até 30 minutos.');
            }

            if ($sameFieldWindow->count() >= 3) {
                return self::markAuditAlert($event, 'repeticao', 'Alteração repetida no mesmo campo', 'O mesmo campo foi alterado ' . $sameFieldWindow->count() . ' vezes no mesmo registro em até 60 minutos.');
            }

            return $event;
        })->values();
    }

    private static function parseAuditEventDate($value): ?Carbon
    {
        if (! $value) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable $exception) {
            return null;
        }
    }

    private static function markAuditAlert(array $event, string $type, string $label, string $description): array
    {
        $event['alert'] = true;
        $event['alert_type'] = $type;
        $event['alert_label'] = $label;
        $event['alert_description'] = $description;
        $event['alert_tone'] = 'danger';

        return $event;
    }

    private static function auditsByUser($user, array $filters = []): array
    {
        if (! CachedSchema::hasTable('auditoria_detalhada')) return [];
        $q = DB::table('auditoria_detalhada as a')
            ->leftJoin('users as u', 'u.id', '=', 'a.user_id')
            ->select('a.user_id', DB::raw('COALESCE(u.name, "Sistema") as label'), DB::raw('COUNT(*) as count'))
            ->groupBy('a.user_id', 'label')
            ->orderByDesc('count')
            ->limit(10);
        self::scopeCompany($q, $user, 'a.empresa_id');
        self::applyAuditFilters($q, $filters, 'a');
        return $q->get()->map(fn ($r) => [
            'id' => $r->user_id ? (string) $r->user_id : 'sistema',
            'label' => $r->label,
            'count' => (int) $r->count,
        ])->all();
    }

    private static function auditsByEvent($user, array $filters = []): array
    {
        if (! CachedSchema::hasTable('auditoria_detalhada')) return [];
        $q = DB::table('auditoria_detalhada as a')->select(DB::raw('COALESCE(a.evento, "evento") as label'), DB::raw('COUNT(*) as count'))->groupBy('label')->orderByDesc('count')->limit(10);
        self::scopeCompany($q, $user, 'a.empresa_id');
        self::applyAuditFilters($q, $filters, 'a');
        return $q->get()->map(fn ($r) => [
            'id' => (string) $r->label,
            'label' => ucfirst(str_replace('_', ' ', (string) $r->label)),
            'count' => (int) $r->count,
        ])->all();
    }

    private static function auditsByModule($user, array $filters = []): array
    {
        if (! CachedSchema::hasTable('auditoria_detalhada')) return [];
        $q = DB::table('auditoria_detalhada as a')
            ->select(DB::raw('COALESCE(a.auditable_type, "Módulo não informado") as label'), DB::raw('COUNT(*) as count'))
            ->groupBy('label')
            ->orderByDesc('count')
            ->limit(10);
        self::scopeCompany($q, $user, 'a.empresa_id');
        self::applyAuditFilters($q, $filters, 'a');
        return $q->get()->map(fn ($r) => ['label' => class_basename($r->label), 'count' => (int) $r->count])->all();
    }

    private static function hasAuditHistoryFocus(array $filters): bool
    {
        return trim((string) ($filters['auditableType'] ?? '')) !== '' && trim((string) ($filters['auditableId'] ?? '')) !== '';
    }

    private static function auditHistoryContext($user, array $filters): array
    {
        self::auditDebug('auditHistoryContext:start', [
            'filters' => self::compactAuditDebugContext($filters),
            'has_focus' => self::hasAuditHistoryFocus($filters),
            'has_table_auditoria_detalhada' => CachedSchema::hasTable('auditoria_detalhada'),
        ]);

        if (! self::hasAuditHistoryFocus($filters) || ! CachedSchema::hasTable('auditoria_detalhada')) {
            self::auditDebug('auditHistoryContext:inactive');
            return [
                'active' => false,
                'type' => '',
                'id' => '',
                'module' => '',
                'total' => 0,
                'critical' => 0,
                'users' => 0,
                'first_date' => '-',
                'last_date' => '-',
                'events' => [],
            ];
        }

        $auditableType = trim((string) ($filters['auditableType'] ?? ''));
        $auditableId = trim((string) ($filters['auditableId'] ?? ''));

        $base = DB::table('auditoria_detalhada as a')
            ->where('a.auditable_type', $auditableType)
            ->where('a.auditable_id', $auditableId);

        self::scopeCompany($base, $user, 'a.empresa_id');

        $rows = (clone $base)
            ->select('a.evento', 'a.campo', 'a.valor_anterior', 'a.valor_novo', 'a.user_id', 'a.created_at')
            ->orderBy('a.created_at')
            ->get();

        $critical = $rows->filter(function ($row): bool {
            $diffRows = self::auditDiffRows($row->valor_anterior, $row->valor_novo);
            return self::auditCriticality($row->evento, $row->campo, $row->valor_anterior, $row->valor_novo, $diffRows)['key'] === 'alta';
        })->count();

        $events = $rows
            ->groupBy(fn ($row) => (string) ($row->evento ?: 'evento'))
            ->map(fn ($items, $event) => [
                'label' => ucfirst(str_replace('_', ' ', $event)),
                'count' => $items->count(),
            ])
            ->sortByDesc('count')
            ->values()
            ->take(4)
            ->all();

        $context = [
            'active' => true,
            'type' => $auditableType,
            'id' => $auditableId,
            'module' => class_basename($auditableType ?: 'Registro'),
            'total' => $rows->count(),
            'critical' => $critical,
            'users' => $rows->pluck('user_id')->filter()->unique()->count(),
            'first_date' => $rows->first()?->created_at ? Carbon::parse($rows->first()->created_at)->format('d/m/Y H:i') : '-',
            'last_date' => $rows->last()?->created_at ? Carbon::parse($rows->last()->created_at)->format('d/m/Y H:i') : '-',
            'events' => $events,
        ];

        self::auditDebug('auditHistoryContext:end', [
            'active' => true,
            'type' => $context['type'],
            'id' => $context['id'],
            'total' => $context['total'],
            'critical' => $context['critical'],
            'users' => $context['users'],
        ]);

        return $context;
    }

    private static function auditFilterOptions($user): array
    {
        if (! CachedSchema::hasTable('auditoria_detalhada')) {
            return ['actions' => [], 'users' => [], 'companies' => []];
        }

        $actionsQuery = DB::table('auditoria_detalhada as a')->select('a.evento')->whereNotNull('a.evento')->distinct()->orderBy('a.evento');
        self::scopeCompany($actionsQuery, $user, 'a.empresa_id');

        $usersQuery = DB::table('auditoria_detalhada as a')
            ->leftJoin('users as u', 'u.id', '=', 'a.user_id')
            ->select('a.user_id', DB::raw('COALESCE(u.name, "Sistema") as name'))
            ->distinct()
            ->orderBy('name');
        self::scopeCompany($usersQuery, $user, 'a.empresa_id');

        $companiesQuery = DB::table('auditoria_detalhada as a')
            ->leftJoin('empresas as e', 'e.id', '=', 'a.empresa_id')
            ->select('a.empresa_id', DB::raw('COALESCE(e.nome_fantasia, e.razao_social, CONCAT("Empresa #", a.empresa_id)) as name'))
            ->whereNotNull('a.empresa_id')
            ->distinct()
            ->orderBy('name');
        self::scopeCompany($companiesQuery, $user, 'a.empresa_id');

        return [
            'actions' => $actionsQuery->limit(100)->pluck('evento')->filter()->values()->all(),
            'users' => $usersQuery->limit(100)->get()->map(fn ($row) => ['id' => $row->user_id ?: 'sistema', 'name' => $row->name])->all(),
            'companies' => $companiesQuery->limit(200)->get()->map(fn ($row) => ['id' => (int) $row->empresa_id, 'name' => $row->name])->all(),
        ];
    }

    private static function applyAuditFilters($query, array $filters, string $alias): void
    {
        self::auditDebug('applyAuditFilters:start', [
            'alias' => $alias,
            'filters' => self::compactAuditDebugContext($filters),
        ]);

        $appliedFilters = [];
        $fromDate = trim((string) ($filters['fromDate'] ?? ''));
        $toDate = trim((string) ($filters['toDate'] ?? ''));

        if ($fromDate !== '') {
            $query->whereDate($alias . '.created_at', '>=', $fromDate);
            $appliedFilters['fromDate'] = $fromDate;
        }

        if ($toDate !== '') {
            $query->whereDate($alias . '.created_at', '<=', $toDate);
            $appliedFilters['toDate'] = $toDate;
        }

        if ($fromDate === '' && $toDate === '') {
            $days = (string) ($filters['dateFilter'] ?? '30');
            if ($days !== 'todos' && is_numeric($days)) {
                $query->where($alias . '.created_at', '>=', now()->subDays((int) $days));
                $appliedFilters['dateFilter_days'] = (int) $days;
            }
        }

        $action = (string) ($filters['actionFilter'] ?? 'todas');
        if ($action !== 'todas' && $action !== '') {
            $query->where($alias . '.evento', $action);
            $appliedFilters['actionFilter'] = $action;
        }

        $userId = (string) ($filters['userFilter'] ?? 'todos');
        if ($userId !== 'todos' && $userId !== '') {
            $userId === 'sistema' ? $query->whereNull($alias . '.user_id') : $query->where($alias . '.user_id', (int) $userId);
            $appliedFilters['userFilter'] = $userId;
        }

        $companyId = (string) ($filters['companyFilter'] ?? 'todas');
        if ($companyId !== 'todas' && $companyId !== '') {
            $query->where($alias . '.empresa_id', (int) $companyId);
            $appliedFilters['companyFilter'] = $companyId;
        }

        $auditableType = trim((string) ($filters['auditableType'] ?? ''));
        if ($auditableType !== '') {
            $query->where($alias . '.auditable_type', $auditableType);
            $appliedFilters['auditableType'] = $auditableType;
        }

        $auditableId = trim((string) ($filters['auditableId'] ?? ''));
        if ($auditableId !== '') {
            $query->where($alias . '.auditable_id', $auditableId);
            $appliedFilters['auditableId'] = $auditableId;
        }

        $search = trim((string) ($filters['searchFilter'] ?? ''));
        if ($search !== '') {
            $like = '%' . str_replace(['%', '_'], ['\%', '\_'], $search) . '%';
            $query->where(function ($q) use ($alias, $like) {
                $q->where($alias . '.evento', 'like', $like)
                    ->orWhere($alias . '.auditable_type', 'like', $like)
                    ->orWhere($alias . '.auditable_id', 'like', $like)
                    ->orWhere($alias . '.campo', 'like', $like)
                    ->orWhere($alias . '.valor_anterior', 'like', $like)
                    ->orWhere($alias . '.valor_novo', 'like', $like)
                    ->orWhere($alias . '.ip', 'like', $like);
            });
            $appliedFilters['searchFilter'] = self::short($search, 120);
        }

        self::auditDebug('applyAuditFilters:end', [
            'alias' => $alias,
            'applied' => $appliedFilters,
        ]);
    }

    private static function recentApprovals($user, int $limit = 8): array
    {
        if (! CachedSchema::hasTable('item_controle_aprovacoes')) return [];
        $q = DB::table('item_controle_aprovacoes as ap')
            ->leftJoin('item_controles as i', 'i.id', '=', 'ap.item_controle_id')
            ->leftJoin('empresas as e', 'e.id', '=', 'ap.empresa_id')
            ->leftJoin('users as u', 'u.id', '=', 'ap.aprovador_id')
            ->select('ap.*', 'i.titulo as item_titulo', DB::raw('COALESCE(e.nome_fantasia, e.razao_social) as empresa_nome'), 'u.name as aprovador_nome')
            ->orderByDesc('ap.created_at')
            ->limit($limit);
        self::scopeCompany($q, $user, 'ap.empresa_id');
        return $q->get()->map(fn ($r) => [
            'title' => ComplianceInternoFormatter::title($r->item_titulo, 'Aprovação interna'),
            'recordId' => (int) $r->id,
            'itemId' => $r->item_controle_id ? (int) $r->item_controle_id : null,
            'kind' => 'Aprovação',
            'type' => 'approval',
            'searchable' => ComplianceInternoFormatter::searchable([$r->item_titulo, $r->empresa_nome, $r->aprovador_nome, $r->status, $r->observacao_solicitacao, $r->observacao_resposta]),
            'kindTone' => 'warning',
            'rawStatus' => ComplianceInternoFormatter::key($r->status),
            'rawPriority' => null,
            'assignedUserIds' => array_filter([(int) ($r->aprovador_id ?? 0)]),
            'assignedEmails' => [],
            'isAssignedToCurrentUser' => (int) ($user->id ?? 0) > 0 && (int) ($r->aprovador_id ?? 0) === (int) ($user->id ?? 0),
            'workflowWeight' => ComplianceInternoFormatter::workflowWeight('approval', $r->status, null, $r->created_at),
            'nextStep' => ComplianceInternoFormatter::nextStep('approval', $r->status),
            'meta' => ComplianceInternoFormatter::meta([
                ComplianceInternoFormatter::company($r->empresa_nome),
                'Aprovador: ' . ComplianceInternoFormatter::person($r->aprovador_nome, null, 'Não definido'),
            ]),
            'metaTags' => ComplianceInternoFormatter::metaTags([
                ComplianceInternoFormatter::company($r->empresa_nome),
                'Aprovador: ' . ComplianceInternoFormatter::person($r->aprovador_nome, null, 'Não definido'),
            ]),
            'status' => ComplianceInternoFormatter::status($r->status, 'Pendente'),
            'description' => ComplianceInternoFormatter::description(
                $r->observacao_solicitacao ?: $r->observacao_resposta,
                'Aprovação registrada no fluxo interno.'
            ),
            'date' => ComplianceInternoFormatter::date($r->created_at),
            'tone' => ComplianceInternoFormatter::toneForStatus($r->status, 'ok'),
            'detailCards' => self::internoDetailCards('approval', $r),
            'actions' => self::internoActions('approval', $r->status, $r->item_controle_id ?? null, $r->id ?? null),
        ])->all();
    }

    private static function recentSignatures($user, int $limit): array
    {
        if (! CachedSchema::hasTable('item_controle_assinaturas')) return [];
        $q = DB::table('item_controle_assinaturas as s')
            ->leftJoin('item_controles as i', 'i.id', '=', 's.item_controle_id')
            ->leftJoin('empresas as e', 'e.id', '=', 's.empresa_id')
            ->select('s.*', 'i.titulo as item_titulo', DB::raw('COALESCE(e.nome_fantasia, e.razao_social) as empresa_nome'))
            ->orderByDesc('s.created_at')->limit($limit);
        self::scopeCompany($q, $user, 's.empresa_id');
        return $q->get()->map(fn ($r) => [
            'title' => ComplianceInternoFormatter::title($r->item_titulo, 'Assinatura interna'),
            'recordId' => (int) $r->id,
            'itemId' => $r->item_controle_id ? (int) $r->item_controle_id : null,
            'kind' => 'Assinatura',
            'type' => 'signature',
            'searchable' => ComplianceInternoFormatter::searchable([$r->item_titulo, $r->empresa_nome, $r->nome, $r->email, $r->observacao, $r->assinado_em ? 'assinado' : 'pendente assinatura']),
            'kindTone' => $r->assinado_em ? 'ok' : 'warning',
            'rawStatus' => $r->assinado_em ? 'assinado' : 'nao_assinado',
            'rawPriority' => null,
            'assignedUserIds' => [],
            'assignedEmails' => array_filter([mb_strtolower(trim((string) ($r->email ?? '')))]),
            'isAssignedToCurrentUser' => trim((string) ($user->email ?? '')) !== '' && mb_strtolower(trim((string) ($r->email ?? ''))) === mb_strtolower(trim((string) ($user->email ?? ''))),
            'signed' => (bool) $r->assinado_em,
            'workflowWeight' => ComplianceInternoFormatter::workflowWeight('signature', $r->assinado_em ? 'assinado' : 'nao_assinado', null, $r->assinado_em ?: $r->created_at),
            'nextStep' => ComplianceInternoFormatter::nextStep('signature', $r->assinado_em ? 'assinado' : 'nao_assinado'),
            'meta' => ComplianceInternoFormatter::meta([
                ComplianceInternoFormatter::company($r->empresa_nome),
                'Signatário: ' . ComplianceInternoFormatter::person($r->nome, $r->email, 'Não informado'),
            ]),
            'metaTags' => ComplianceInternoFormatter::metaTags([
                ComplianceInternoFormatter::company($r->empresa_nome),
                'Signatário: ' . ComplianceInternoFormatter::person($r->nome, $r->email, 'Não informado'),
            ]),
            'status' => ComplianceInternoFormatter::signatureStatus((bool) $r->assinado_em),
            'description' => ComplianceInternoFormatter::description($r->observacao, 'Registro de assinatura vinculado ao item.'),
            'date' => ComplianceInternoFormatter::date($r->assinado_em ?: $r->created_at),
            'tone' => $r->assinado_em ? 'ok' : 'warning',
            'detailCards' => self::internoDetailCards('signature', $r),
            'actions' => self::internoActions('signature', $r->assinado_em ? 'assinado' : 'nao_assinado', $r->item_controle_id ?? null, $r->id ?? null),
        ])->all();
    }

    private static function recentDocuments($user, int $limit): array
    {
        if (! CachedSchema::hasTable('portal_documentos')) return [];
        $q = DB::table('portal_documentos as d')->leftJoin('empresas as e', 'e.id', '=', 'd.empresa_id')->select('d.*', DB::raw('COALESCE(e.nome_fantasia, e.razao_social) as empresa_nome'))->orderByDesc('d.created_at')->limit($limit);
        self::scopeCompany($q, $user, 'd.empresa_id');
        return $q->get()->map(fn ($r) => [
            'title' => ComplianceInternoFormatter::title($r->titulo, 'Documento interno'),
            'recordId' => (int) $r->id,
            'itemId' => null,
            'kind' => 'Documento',
            'type' => 'document',
            'searchable' => ComplianceInternoFormatter::searchable([$r->titulo, $r->empresa_nome, $r->tipo, $r->conteudo, $r->url, $r->visivel_cliente ? 'visível cliente' : 'interno']),
            'kindTone' => $r->visivel_cliente ? 'ok' : 'info',
            'rawStatus' => $r->visivel_cliente ? 'visivel' : 'interno',
            'rawPriority' => null,
            'workflowWeight' => ComplianceInternoFormatter::workflowWeight('document', $r->visivel_cliente ? 'visivel' : 'interno', null, $r->created_at),
            'nextStep' => ComplianceInternoFormatter::nextStep('document', $r->visivel_cliente ? 'visivel' : 'interno'),
            'meta' => ComplianceInternoFormatter::meta([
                ComplianceInternoFormatter::company($r->empresa_nome),
                ComplianceInternoFormatter::documentType($r->tipo),
            ]),
            'metaTags' => ComplianceInternoFormatter::metaTags([
                ComplianceInternoFormatter::company($r->empresa_nome),
                ComplianceInternoFormatter::documentType($r->tipo),
            ]),
            'status' => ComplianceInternoFormatter::visibility($r->visivel_cliente),
            'description' => ComplianceInternoFormatter::description(
                $r->conteudo ?: $r->url,
                'Documento registrado.'
            ),
            'date' => ComplianceInternoFormatter::date($r->created_at),
            'tone' => $r->visivel_cliente ? 'ok' : 'info',
            'detailCards' => self::internoDetailCards('document', $r),
            'actions' => self::internoActions('document', $r->visivel_cliente ? 'visivel' : 'interno', null, $r->id ?? null, $r->url ?? null),
        ])->all();
    }

    private static function recentRequests($user, int $limit): array
    {
        if (! CachedSchema::hasTable('portal_solicitacoes')) return [];
        $q = DB::table('portal_solicitacoes as s')->leftJoin('empresas as e', 'e.id', '=', 's.empresa_id')->select('s.*', DB::raw('COALESCE(e.nome_fantasia, e.razao_social) as empresa_nome'))->whereNotIn('s.status', ['concluido','cancelado'])->orderByDesc('s.created_at')->limit($limit);
        self::scopeCompany($q, $user, 's.empresa_id');
        return $q->get()->map(fn ($r) => [
            'title' => ComplianceInternoFormatter::title($r->titulo, 'Solicitação interna'),
            'recordId' => (int) $r->id,
            'itemId' => $r->item_controle_id ? (int) $r->item_controle_id : null,
            'kind' => 'Solicitação',
            'type' => 'request',
            'searchable' => ComplianceInternoFormatter::searchable([$r->titulo, $r->empresa_nome, $r->prioridade, $r->status, $r->descricao]),
            'kindTone' => ComplianceInternoFormatter::toneForPriority($r->prioridade, ComplianceInternoFormatter::toneForStatus($r->status, 'info')),
            'rawStatus' => ComplianceInternoFormatter::key($r->status),
            'rawPriority' => ComplianceInternoFormatter::key($r->prioridade),
            'assignedUserIds' => array_values(array_filter([
                (int) ($r->user_id ?? 0),
                (int) ($r->responsavel_id ?? 0),
                (int) ($r->solicitante_id ?? 0),
            ])),
            'assignedEmails' => [],
            'isAssignedToCurrentUser' => in_array((int) ($user->id ?? 0), array_values(array_filter([
                (int) ($r->user_id ?? 0),
                (int) ($r->responsavel_id ?? 0),
                (int) ($r->solicitante_id ?? 0),
            ])), true),
            'workflowWeight' => ComplianceInternoFormatter::workflowWeight('request', $r->status, $r->prioridade, $r->created_at),
            'nextStep' => ComplianceInternoFormatter::nextStep('request', $r->status, $r->prioridade),
            'meta' => ComplianceInternoFormatter::meta([
                ComplianceInternoFormatter::company($r->empresa_nome),
                ComplianceInternoFormatter::priority($r->prioridade),
            ]),
            'metaTags' => ComplianceInternoFormatter::metaTags([
                ComplianceInternoFormatter::company($r->empresa_nome),
                ComplianceInternoFormatter::priority($r->prioridade),
            ]),
            'status' => ComplianceInternoFormatter::status($r->status, 'Aberto'),
            'description' => ComplianceInternoFormatter::description($r->descricao, 'Solicitação registrada sem descrição.'),
            'date' => ComplianceInternoFormatter::date($r->created_at),
            'tone' => ComplianceInternoFormatter::toneForPriority($r->prioridade, ComplianceInternoFormatter::toneForStatus($r->status, 'info')),
            'detailCards' => self::internoDetailCards('request', $r),
            'actions' => self::internoActions('request', $r->status, $r->item_controle_id ?? null, $r->id ?? null),
        ])->all();
    }


    private static function internoActions(string $kind, ?string $status = null, $itemId = null, $recordId = null, ?string $externalUrl = null): array
    {
        $statusKey = ComplianceInternoFormatter::key($status);
        $actions = [];

        if ($kind === 'approval') {
            $centralUrl = self::safeUrl(fn (): string => CentralAprovacoes::getUrl());

            if ($centralUrl) {
                $actions[] = [
                    'label' => in_array($statusKey, ['pendente', 'em_aprovacao'], true) ? 'Decidir aprovação' : 'Abrir aprovações',
                    'url' => $centralUrl,
                    'style' => in_array($statusKey, ['pendente', 'em_aprovacao'], true) ? 'primary' : 'secondary',
                    'hint' => 'Abre a central de aprovações já existente no sistema.',
                ];
            }
        }

        if ($kind === 'signature') {
            $signaturesUrl = self::safeUrl(fn (): string => ItemControleResource::getUrl('assinaturas'));

            if ($signaturesUrl) {
                $actions[] = [
                    'label' => $statusKey === 'assinado' ? 'Ver assinaturas' : 'Gerenciar assinatura',
                    'url' => $signaturesUrl,
                    'style' => $statusKey === 'assinado' ? 'secondary' : 'primary',
                    'hint' => 'Abre a área de portal e assinaturas dos itens de controle.',
                ];
            }
        }

        if ($kind === 'document') {
            $cleanUrl = trim((string) $externalUrl);

            if ($cleanUrl !== '') {
                $actions[] = [
                    'label' => 'Abrir documento',
                    'url' => $cleanUrl,
                    'style' => 'primary',
                    'external' => true,
                    'hint' => 'Abre o link do documento em uma nova aba.',
                ];
            }

            $documentsUrl = self::safeUrl(fn (): string => Documentos::getUrl());

            if ($documentsUrl) {
                $actions[] = [
                    'label' => 'Ver documentos',
                    'url' => $documentsUrl,
                    'style' => $cleanUrl !== '' ? 'secondary' : 'primary',
                    'hint' => 'Abre a página de documentos existente no sistema.',
                ];
            }
        }

        if ($kind === 'request') {
            $atendimentosUrl = self::safeUrl(fn (): string => Atendimentos::getUrl());

            if ($atendimentosUrl) {
                $actions[] = [
                    'label' => in_array($statusKey, ['aberto', 'pendente', 'em_andamento'], true) ? 'Tratar solicitação' : 'Abrir atendimentos',
                    'url' => $atendimentosUrl,
                    'style' => in_array($statusKey, ['aberto', 'pendente', 'em_andamento'], true) ? 'primary' : 'secondary',
                    'hint' => 'Abre a central de atendimentos para dar continuidade à solicitação.',
                ];
            }
        }

        if (! blank($itemId)) {
            $itemUrl = self::safeUrl(fn (): string => ItemControleResource::getUrl('edit', ['record' => $itemId]));

            if ($itemUrl) {
                $actions[] = [
                    'label' => 'Abrir item',
                    'url' => $itemUrl,
                    'style' => empty($actions) ? 'primary' : 'secondary',
                    'hint' => 'Abre o item de controle relacionado a este registro.',
                ];
            }
        }

        return $actions;
    }

    private static function internoDetailCards(string $kind, $row): array
    {
        if ($kind === 'approval') {
            return [
                ['label' => 'Empresa', 'value' => ComplianceInternoFormatter::company($row->empresa_nome ?? null)],
                ['label' => 'Aprovador', 'value' => ComplianceInternoFormatter::person($row->aprovador_nome ?? null, null, 'Não definido')],
                ['label' => 'Registro', 'value' => '#' . ($row->id ?? '-'), 'hint' => ! blank($row->item_controle_id ?? null) ? 'Item vinculado #' . $row->item_controle_id : 'Sem item vinculado'],
                ['label' => 'Data', 'value' => ComplianceInternoFormatter::date($row->created_at ?? null)],
            ];
        }

        if ($kind === 'signature') {
            return [
                ['label' => 'Empresa', 'value' => ComplianceInternoFormatter::company($row->empresa_nome ?? null)],
                ['label' => 'Signatário', 'value' => ComplianceInternoFormatter::person($row->nome ?? null, $row->email ?? null, 'Não informado')],
                ['label' => 'Registro', 'value' => '#' . ($row->id ?? '-'), 'hint' => ! blank($row->item_controle_id ?? null) ? 'Item vinculado #' . $row->item_controle_id : 'Sem item vinculado'],
                ['label' => 'Data', 'value' => ComplianceInternoFormatter::date(($row->assinado_em ?? null) ?: ($row->created_at ?? null))],
            ];
        }

        if ($kind === 'document') {
            return [
                ['label' => 'Empresa', 'value' => ComplianceInternoFormatter::company($row->empresa_nome ?? null)],
                ['label' => 'Tipo', 'value' => ComplianceInternoFormatter::documentType($row->tipo ?? null)],
                ['label' => 'Visibilidade', 'value' => ComplianceInternoFormatter::visibility($row->visivel_cliente ?? false)],
                ['label' => 'Data', 'value' => ComplianceInternoFormatter::date($row->created_at ?? null)],
            ];
        }

        if ($kind === 'request') {
            return [
                ['label' => 'Empresa', 'value' => ComplianceInternoFormatter::company($row->empresa_nome ?? null)],
                ['label' => 'Prioridade', 'value' => ComplianceInternoFormatter::priority($row->prioridade ?? null)],
                ['label' => 'Registro', 'value' => '#' . ($row->id ?? '-'), 'hint' => ! blank($row->item_controle_id ?? null) ? 'Item vinculado #' . $row->item_controle_id : 'Sem item vinculado'],
                ['label' => 'Data', 'value' => ComplianceInternoFormatter::date($row->created_at ?? null)],
            ];
        }

        return [];
    }

    private static function safeUrl(callable $callback): ?string
    {
        try {
            $url = trim((string) $callback());

            return $url !== '' ? $url : null;
        } catch (\Throwable) {
            return null;
        }
    }

    private static function countApprovals($user, ?string $status = null): int
    {
        if (! CachedSchema::hasTable('item_controle_aprovacoes')) return 0;
        $q = DB::table('item_controle_aprovacoes as ap');
        if ($status) $q->where('ap.status', $status);
        self::scopeCompany($q, $user, 'ap.empresa_id');
        return (int) $q->count();
    }

    private static function countSignatures($user): int
    {
        if (! CachedSchema::hasTable('item_controle_assinaturas')) return 0;
        $q = DB::table('item_controle_assinaturas as s')->whereNotNull('s.assinado_em');
        self::scopeCompany($q, $user, 's.empresa_id');
        return (int) $q->count();
    }

    private static function countDocuments($user): int
    {
        if (! CachedSchema::hasTable('portal_documentos')) return 0;
        $q = DB::table('portal_documentos as d');
        self::scopeCompany($q, $user, 'd.empresa_id');
        return (int) $q->count();
    }

    private static function countRequests($user): int
    {
        if (! CachedSchema::hasTable('portal_solicitacoes')) return 0;
        $q = DB::table('portal_solicitacoes as s')->whereNotIn('s.status', ['concluido','cancelado']);
        self::scopeCompany($q, $user, 's.empresa_id');
        return (int) $q->count();
    }

    private static function recommendations(int $score, int $critical, int $late, int $evidence, int $total): array
    {
        $items = [];
        if ($critical > 0) $items[] = ['title' => 'Mitigar riscos críticos', 'description' => 'Revise os itens com maior score e defina responsável, prazo e próxima ação.', 'tone' => 'danger'];
        if ($late > 0) $items[] = ['title' => 'Regularizar pendências vencidas', 'description' => 'Pendências atrasadas derrubam o score e podem bloquear auditorias.', 'tone' => 'warning'];
        if ($evidence < max(1, (int) round($total * .4))) $items[] = ['title' => 'Anexar evidências nos itens importantes', 'description' => 'Documentos, aprovações e assinaturas deixam o compliance auditável.', 'tone' => 'info'];
        if ($score >= 80) $items[] = ['title' => 'Manter rotina de revisão', 'description' => 'A operação está saudável. Continue acompanhando riscos e auditoria semanalmente.', 'tone' => 'ok'];
        return $items;
    }

    private static function scoreHint(int $score): string
    {
        return $score >= 80 ? 'Operação saudável' : ($score >= 60 ? 'Precisa de atenção' : 'Risco operacional alto');
    }

    private static function stat(string $label, $value, string $hint, ?string $type = null, ?string $status = null, ?string $priority = null): array
    {
        return [
            'label' => $label,
            'value' => $value,
            'hint' => $hint,
            'type' => $type,
            'status' => $status,
            'priority' => $priority,
        ];
    }

    private static function short($value, int $limit = 90): string
    {
        $text = trim((string) $value);
        if ($text === '') return '';
        return mb_strlen($text) > $limit ? mb_substr($text, 0, $limit - 3) . '...' : $text;
    }


    private static function auditTimelineSummary(array $diffRows, $field, $event): array
    {
        $changedRows = collect($diffRows)->filter(fn ($row) => ($row['status'] ?? 'unchanged') !== 'unchanged')->values();
        $primary = $changedRows->first() ?: (collect($diffRows)->first() ?: [
            'field' => $field ?: 'valor',
            'old' => '—',
            'new' => '—',
            'status' => 'unchanged',
        ]);

        $status = (string) ($primary['status'] ?? 'unchanged');
        $fieldLabel = trim((string) ($primary['field'] ?? $field ?: 'valor'));
        $old = self::short((string) ($primary['old'] ?? '—'), 72) ?: '—';
        $new = self::short((string) ($primary['new'] ?? '—'), 72) ?: '—';
        $changedCount = $changedRows->count();

        $statusLabels = [
            'added' => 'Adicionado',
            'removed' => 'Removido',
            'changed' => 'Alterado',
            'unchanged' => 'Sem alteração',
        ];

        $countText = $changedCount > 1
            ? $changedCount . ' campos alterados'
            : ($statusLabels[$status] ?? 'Alteração registrada');

        $description = $status === 'unchanged'
            ? 'Evento registrado sem diferença detectada no payload.'
            : $fieldLabel . ': ' . $old . ' → ' . $new;

        return [
            'field' => $fieldLabel,
            'old' => $old,
            'new' => $new,
            'status' => $status,
            'status_label' => $statusLabels[$status] ?? 'Alteração registrada',
            'count' => $changedCount,
            'count_label' => $countText,
            'description' => $description,
            'primary' => [
                'field' => $fieldLabel,
                'old' => $old,
                'new' => $new,
                'status' => $status,
                'status_label' => $statusLabels[$status] ?? 'Alteração registrada',
            ],
        ];
    }

    private static function auditCriticality($event, $field, $oldValue, $newValue, array $diffRows = []): array
    {
        $eventText = mb_strtolower(trim((string) $event));
        $fieldText = mb_strtolower(trim((string) $field));
        $oldText = mb_strtolower(self::normalizeAuditValue($oldValue));
        $newText = mb_strtolower(self::normalizeAuditValue($newValue));
        $changedFields = collect($diffRows)
            ->filter(fn ($row) => ($row['status'] ?? 'unchanged') !== 'unchanged')
            ->pluck('field')
            ->map(fn ($value) => mb_strtolower((string) $value))
            ->values();

        $destructiveEvents = ['deleted', 'delete', 'destroyed', 'excluido', 'excluida', 'removido', 'removida', 'removed', 'cancelado', 'cancelada', 'canceled', 'cancelled'];
        $approvalEvents = ['reprovado', 'reprovada', 'rejeitado', 'rejeitada', 'rejected', 'denied'];
        $mediumEvents = ['updated', 'update', 'alterado', 'alterada', 'editado', 'editada', 'restored', 'restaurado', 'aprovado', 'aprovada', 'approved'];
        $lowEvents = ['viewed', 'visualizado', 'consultado', 'login', 'logout', 'created', 'create', 'criado', 'criada'];

        $highFieldTokens = [
            'status', 'situacao', 'situação', 'aprovacao', 'aprovação', 'approval', 'permiss', 'role', 'perfil',
            'senha', 'password', 'token', 'secret', 'financeiro', 'valor', 'preco', 'preço', 'custo', 'pagamento',
            'salario', 'salário', 'document_status', 'assinatura', 'cancelamento', 'exclusao', 'exclusão',
        ];

        $mediumFieldTokens = [
            'responsavel', 'responsável', 'empresa', 'prazo', 'vencimento', 'prioridade', 'risco', 'score',
            'titulo', 'título', 'nome', 'email', 'documento', 'descricao', 'descrição', 'observacao', 'observação',
        ];

        $hasChangedSensitiveField = collect($highFieldTokens)->contains(function (string $token) use ($fieldText, $changedFields): bool {
            if ($fieldText !== '' && str_contains($fieldText, $token)) {
                return true;
            }
            return $changedFields->contains(fn ($field) => str_contains($field, $token));
        });

        $hasChangedRelevantField = collect($mediumFieldTokens)->contains(function (string $token) use ($fieldText, $changedFields): bool {
            if ($fieldText !== '' && str_contains($fieldText, $token)) {
                return true;
            }
            return $changedFields->contains(fn ($field) => str_contains($field, $token));
        });

        $negativeTransitions = ['reprovado', 'reprovada', 'cancelado', 'cancelada', 'inativo', 'inativa', 'bloqueado', 'bloqueada', 'excluido', 'excluida', 'removido', 'removida'];
        $positiveToNegative = collect($negativeTransitions)->contains(fn (string $token): bool => str_contains($newText, $token) && $oldText !== $newText);

        if (in_array($eventText, $destructiveEvents, true) || in_array($eventText, $approvalEvents, true) || $hasChangedSensitiveField || $positiveToNegative) {
            return [
                'key' => 'alta',
                'label' => 'Alta',
                'tone' => 'danger',
                'hint' => 'Exige atenção: exclusão, reprovação, cancelamento ou alteração em dado sensível.',
            ];
        }

        if (in_array($eventText, $mediumEvents, true) || $hasChangedRelevantField || collect($diffRows)->contains(fn ($row) => ($row['status'] ?? 'unchanged') !== 'unchanged')) {
            return [
                'key' => 'media',
                'label' => 'Média',
                'tone' => 'warning',
                'hint' => 'Mudança operacional relevante que deve permanecer rastreável.',
            ];
        }

        if (in_array($eventText, $lowEvents, true)) {
            return [
                'key' => 'baixa',
                'label' => 'Baixa',
                'tone' => 'ok',
                'hint' => 'Registro informativo de baixo impacto operacional.',
            ];
        }

        return [
            'key' => 'baixa',
            'label' => 'Baixa',
            'tone' => 'info',
            'hint' => 'Evento registrado sem indício de criticidade elevada.',
        ];
    }

    private static function normalizeAuditValue($value): string
    {
        if ($value === null) {
            return '';
        }

        $text = trim((string) $value);

        if ($text === '') {
            return '';
        }

        $decoded = json_decode($text, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?: $text;
        }

        return preg_replace('/\s+/', ' ', $text) ?: $text;
    }


    private static function auditDiffRows($oldValue, $newValue): array
    {
        $oldDecoded = self::decodeAuditPayload($oldValue);
        $newDecoded = self::decodeAuditPayload($newValue);

        if (is_array($oldDecoded) || is_array($newDecoded)) {
            $oldFlat = is_array($oldDecoded) ? self::flattenAuditArray($oldDecoded) : ['valor' => self::normalizeAuditValue($oldValue)];
            $newFlat = is_array($newDecoded) ? self::flattenAuditArray($newDecoded) : ['valor' => self::normalizeAuditValue($newValue)];
            $keys = collect(array_merge(array_keys($oldFlat), array_keys($newFlat)))->unique()->sort()->values();

            return $keys->map(function ($key) use ($oldFlat, $newFlat): array {
                $oldExists = array_key_exists($key, $oldFlat);
                $newExists = array_key_exists($key, $newFlat);
                $old = $oldExists ? self::stringifyAuditScalar($oldFlat[$key]) : '';
                $new = $newExists ? self::stringifyAuditScalar($newFlat[$key]) : '';

                return [
                    'field' => (string) $key,
                    'old' => $old !== '' ? $old : '—',
                    'new' => $new !== '' ? $new : '—',
                    'status' => ! $oldExists ? 'added' : (! $newExists ? 'removed' : ($old === $new ? 'unchanged' : 'changed')),
                ];
            })->values()->all();
        }

        $old = self::normalizeAuditValue($oldValue);
        $new = self::normalizeAuditValue($newValue);

        return [[
            'field' => 'valor',
            'old' => $old !== '' ? $old : '—',
            'new' => $new !== '' ? $new : '—',
            'status' => $old === $new ? 'unchanged' : ($old === '' ? 'added' : ($new === '' ? 'removed' : 'changed')),
        ]];
    }

    private static function decodeAuditPayload($value)
    {
        if ($value === null) {
            return null;
        }

        $text = trim((string) $value);
        if ($text === '') {
            return null;
        }

        $decoded = json_decode($text, true);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
    }

    private static function flattenAuditArray(array $data, string $prefix = ''): array
    {
        $result = [];

        foreach ($data as $key => $value) {
            $field = $prefix === '' ? (string) $key : $prefix . '.' . $key;

            if (is_array($value) && self::isAssociativeArray($value)) {
                $result += self::flattenAuditArray($value, $field);
                continue;
            }

            $result[$field] = $value;
        }

        return $result;
    }

    private static function isAssociativeArray(array $value): bool
    {
        return array_keys($value) !== range(0, count($value) - 1);
    }

    private static function stringifyAuditScalar($value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '';
        }

        return preg_replace('/\s+/', ' ', trim((string) $value)) ?: '';
    }

    private static function scopeCompany($query, $user, string $column): void
    {
        if (! $user) {
            $query->whereRaw('1 = 0');
            return;
        }
        if (! self::isSuperAdmin($user)) {
            $query->where($column, $user->empresa_id ?: 0);
        }
    }

    private static function scopeItemVisibility($query, $user, string $alias): void
    {
        if (! $user) {
            $query->whereRaw('1 = 0');
            return;
        }
        if (self::isSuperAdmin($user)) {
            return;
        }
        if (method_exists($user, 'isUser') && $user->isUser()) {
            $responsavelId = $user->responsavel?->id ?: 0;
            $query->where($alias . '.responsavel_id', $responsavelId);
            return;
        }
        $query->where($alias . '.empresa_id', $user->empresa_id ?: 0);
    }

    private static function isSuperAdmin($user): bool
    {
        return method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin();
    }
}
