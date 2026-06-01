<?php

namespace App\Support;

use Carbon\Carbon;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ClientesCrmData
{
    public static function get(array $filters = []): array
    {
        $user = Filament::auth()->user();

        if (! $user) {
            return self::empty();
        }

        $clients = self::clients($filters, $user);
        $summary = self::summary($clients);

        return [
            'summary' => $summary,
            'cards' => [
                'total_clientes' => $summary['total'],
                'em_risco' => $summary['risk'],
                'aguardando_cliente' => collect($clients)->where('contract_status_key', 'aguardando_cliente')->count(),
                'followups_hoje' => collect($clients)->where('followup_due', true)->count(),
                'total_ltv' => $summary['ltv'],
                'valor_mensal' => collect($clients)->sum('monthly_value'),
            ],
            'clients' => $clients,
            'clientes' => $clients,
            'empresas' => collect($clients)->map(fn (array $client): array => [
                'id' => $client['id'],
                'crm_cliente_id' => $client['id'],
                'empresa_id' => $client['empresa_id'],
                'name' => $client['name'],
                'nome' => $client['name'],
            ])->values()->all(),
            'statusSummary' => self::groupSummary($clients, 'contract_status', 'status_tone'),
            'healthSummary' => self::groupSummary($clients, 'health_label', 'health_tone'),
            'actionSummary' => self::actionSummary($clients),
            'onboarding' => collect($clients)
                ->filter(fn (array $client): bool => (int) $client['onboarding_items'] > 0 || (int) $client['open_items'] > 0)
                ->sortByDesc('late_items')
                ->take(8)
                ->values()
                ->all(),
            'emailHistory' => self::emailHistory($clients),
            'pendencias' => self::pendencias(collect($clients)->pluck('id')->all()),
            'historicos' => self::historicos(collect($clients)->pluck('id')->all()),
            'documentos' => self::documentos(collect($clients)->pluck('empresa_id')->filter()->all()),
            'proximosContatos' => self::proximosContatos($clients),
            'insights' => self::insights($clients),
        ];
    }

    public static function statusOptions(): array
    {
        return [
            'Operando bem',
            'Aguardando cliente',
            'Em implementação',
            'Renovação',
            'Em risco',
            'Cancelado',
        ];
    }

    public static function healthOptions(): array
    {
        return ['Saudável', 'Atenção', 'Crítico'];
    }

    public static function normalizeStatus(?string $status): string
    {
        $value = Str::of((string) $status)->lower()->ascii()->replace([' ', '-'], '_')->toString();

        return match ($value) {
            'ativo', 'operando_bem', 'operando' => 'operando_bem',
            'aguardando_cliente', 'aguardando' => 'aguardando_cliente',
            'implementacao', 'em_implementacao', 'implantacao', 'onboarding' => 'em_implementacao',
            'renovacao' => 'renovacao',
            'risco', 'em_risco' => 'em_risco',
            'churn', 'cancelado', 'inativo' => 'cancelado',
            default => 'operando_bem',
        };
    }

    public static function statusLabel(?string $status): string
    {
        return match (self::normalizeStatus($status)) {
            'aguardando_cliente' => 'Aguardando cliente',
            'em_implementacao' => 'Em implementação',
            'renovacao' => 'Renovação',
            'em_risco' => 'Em risco',
            'cancelado' => 'Cancelado',
            default => 'Operando bem',
        };
    }

    public static function normalizeHealth(?string $health): ?string
    {
        if (! $health) {
            return null;
        }

        $value = Str::of($health)->lower()->ascii()->replace([' ', '-'], '_')->toString();

        return match ($value) {
            'saudavel', 'saude', 'ok', 'baixo' => 'saudavel',
            'atencao', 'medio', 'media' => 'atencao',
            'critico', 'critica', 'alto' => 'critico',
            default => null,
        };
    }

    public static function healthLabel(?string $health): string
    {
        return match (self::normalizeHealth($health)) {
            'atencao' => 'Atenção',
            'critico' => 'Crítico',
            default => 'Saudável',
        };
    }

    protected static function empty(): array
    {
        return [
            'summary' => ['total' => 0, 'active' => 0, 'risk' => 0, 'ltv' => 0],
            'cards' => ['total_clientes' => 0, 'em_risco' => 0, 'aguardando_cliente' => 0, 'followups_hoje' => 0, 'total_ltv' => 0, 'valor_mensal' => 0],
            'clients' => [],
            'clientes' => [],
            'empresas' => [],
            'statusSummary' => [],
            'healthSummary' => [],
            'actionSummary' => [
                'criticos' => ['count' => 0, 'label' => 'Críticos'],
                'atencao' => ['count' => 0, 'label' => 'Atenção'],
                'sem_contato' => ['count' => 0, 'label' => 'Sem contato'],
                'followups' => ['count' => 0, 'label' => 'Follow-ups hoje'],
                'pendencias' => ['count' => 0, 'label' => 'Com pendências'],
            ],
            'onboarding' => [],
            'emailHistory' => [],
            'pendencias' => [],
            'historicos' => [],
            'documentos' => [],
            'proximosContatos' => [],
            'insights' => [],
        ];
    }

    protected static function clients(array $filters, object $user): array
    {
        if (! CachedSchema::hasTable('crm_clientes') && ! CachedSchema::hasTable('empresas')) {
            return [];
        }

        $rows = CachedSchema::hasTable('crm_clientes') && DB::table('crm_clientes')->exists()
            ? self::crmRows($user)
            : self::empresaRows($user);

        $stats = self::preloadStats($rows);
        $clients = collect($rows)->map(fn (object $row): array => self::formatClient($row, $stats))->values();

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $needle = Str::lower(Str::ascii($search));
            $clients = $clients->filter(function (array $client) use ($needle): bool {
                $haystack = Str::lower(Str::ascii(implode(' ', array_filter([
                    $client['name'],
                    $client['document'],
                    $client['contact_name'],
                    $client['contact_email'],
                    $client['contact_whatsapp'],
                    $client['next_action'],
                    $client['contract_status'],
                    $client['health_label'],
                    $client['action_label'] ?? '',
                    $client['action_reason'] ?? '',
                ]))));

                return Str::contains($haystack, $needle);
            });
        }

        $status = (string) ($filters['statusFilter'] ?? 'todos');
        if ($status !== 'todos' && $status !== '') {
            $statusKey = self::normalizeStatus($status);
            $clients = $clients->where('contract_status_key', $statusKey);
        }

        $health = (string) ($filters['healthFilter'] ?? 'todos');
        if ($health !== 'todos' && $health !== '') {
            $healthKey = self::normalizeHealth($health);
            if ($healthKey) {
                $clients = $clients->where('health_key', $healthKey);
            }
        }

        $actionFilter = (string) ($filters['actionFilter'] ?? 'todos');
        if ($actionFilter !== 'todos' && $actionFilter !== '') {
            $clients = match ($actionFilter) {
                'criticos' => $clients->filter(fn (array $client): bool => ($client['action_status'] ?? '') === 'critico'),
                'atencao' => $clients->filter(fn (array $client): bool => ($client['action_status'] ?? '') === 'atencao'),
                'sem_contato' => $clients->filter(fn (array $client): bool => ($client['action_status'] ?? '') === 'sem_contato'),
                'followups' => $clients->filter(fn (array $client): bool => (bool) ($client['followup_due'] ?? false)),
                'pendencias' => $clients->filter(fn (array $client): bool => (int) ($client['open_items'] ?? 0) > 0),
                default => $clients,
            };
        }

        $sortBy = (string) ($filters['sortBy'] ?? 'updated_at');
        $clients = match ($sortBy) {
            'name' => $clients->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE),
            'ltv' => $clients->sortByDesc('ltv'),
            'health_score' => $clients->sortByDesc('health_score'),
            'late_items' => $clients->sortByDesc('late_items'),
            'open_items' => $clients->sortByDesc('open_items'),
            'contract_status' => $clients->sortBy('contract_status'),
            'action_priority' => $clients->sortByDesc('action_priority'),
            default => $clients->sortByDesc('updated_at_sort'),
        };

        return $clients->values()->all();
    }

    protected static function crmRows(object $user): array
    {
        $select = [
            'crm.id as id',
            self::selectColumn('crm_clientes', 'empresa_id', 'crm', 'empresa_id', '0'),
            self::selectColumn('crm_clientes', 'situacao', 'crm', 'situacao'),
            self::selectColumn('crm_clientes', 'proxima_acao', 'crm', 'proxima_acao'),
            self::selectColumn('crm_clientes', 'ultimo_contato_em', 'crm', 'ultimo_contato_em'),
            self::selectColumn('crm_clientes', 'proximo_followup_em', 'crm', 'proximo_followup_em'),
            self::selectColumn('crm_clientes', 'risco_churn', 'crm', 'risco_churn'),
            self::selectColumn('crm_clientes', 'responsavel_user_id', 'crm', 'responsavel_user_id'),
            self::selectColumn('crm_clientes', 'valor_contrato', 'crm', 'valor_contrato', '0'),
            self::selectColumn('crm_clientes', 'valor_mensal', 'crm', 'valor_mensal', '0'),
            self::selectColumn('crm_clientes', 'proxima_entrega_em', 'crm', 'proxima_entrega_em'),
            self::selectColumn('crm_clientes', 'created_at', 'crm', 'created_at'),
            self::selectColumn('crm_clientes', 'updated_at', 'crm', 'updated_at'),
            self::selectColumn('empresas', 'razao_social', 'e', 'razao_social'),
            self::selectColumn('empresas', 'nome_fantasia', 'e', 'nome_fantasia'),
            self::selectColumn('empresas', 'cnpj', 'e', 'cnpj'),
            self::selectColumn('empresas', 'email', 'e', 'email'),
            self::selectColumn('empresas', 'telefone', 'e', 'telefone'),
            self::selectColumn('empresas', 'responsavel_nome', 'e', 'responsavel_nome'),
            self::selectColumn('empresas', 'crm_status_contrato', 'e', 'crm_status_contrato'),
            self::selectColumn('empresas', 'crm_contato_nome', 'e', 'crm_contato_nome'),
            self::selectColumn('empresas', 'crm_contato_email', 'e', 'crm_contato_email'),
            self::selectColumn('empresas', 'crm_contato_whatsapp', 'e', 'crm_contato_whatsapp'),
            self::selectColumn('empresas', 'crm_health_manual', 'e', 'crm_health_manual'),
            self::selectColumn('empresas', 'crm_observacoes', 'e', 'crm_observacoes'),
            self::selectColumn('empresas', 'crm_ultima_reuniao_em', 'e', 'crm_ultima_reuniao_em'),
            self::selectColumn('users', 'name', 'u', 'owner_name'),
        ];

        $query = DB::table('crm_clientes as crm')
            ->leftJoin('empresas as e', 'e.id', '=', 'crm.empresa_id')
            ->leftJoin('users as u', 'u.id', '=', 'crm.responsavel_user_id')
            ->select($select);

        if (! method_exists($user, 'isSuperAdmin') || ! $user->isSuperAdmin()) {
            $query->where('crm.empresa_id', $user->empresa_id ?: 0);
        }

        return $query->orderByDesc('crm.updated_at')->orderByDesc('crm.id')->get()->all();
    }

    protected static function empresaRows(object $user): array
    {
        $query = DB::table('empresas as e')
            ->select([
                'e.id as id',
                'e.id as empresa_id',
                self::selectColumn('empresas', 'status', 'e', 'situacao'),
                self::selectColumn('empresas', 'crm_observacoes', 'e', 'proxima_acao'),
                self::selectColumn('empresas', 'crm_ultima_reuniao_em', 'e', 'ultimo_contato_em'),
                self::selectColumn('empresas', 'updated_at', 'e', 'proximo_followup_em'),
                self::selectColumn('empresas', 'crm_health_manual', 'e', 'risco_churn'),
                DB::raw('NULL as responsavel_user_id'),
                DB::raw('0 as valor_contrato'),
                DB::raw('0 as valor_mensal'),
                self::selectColumn('empresas', 'updated_at', 'e', 'proxima_entrega_em'),
                self::selectColumn('empresas', 'created_at', 'e', 'created_at'),
                self::selectColumn('empresas', 'updated_at', 'e', 'updated_at'),
                self::selectColumn('empresas', 'razao_social', 'e', 'razao_social'),
                self::selectColumn('empresas', 'nome_fantasia', 'e', 'nome_fantasia'),
                self::selectColumn('empresas', 'cnpj', 'e', 'cnpj'),
                self::selectColumn('empresas', 'email', 'e', 'email'),
                self::selectColumn('empresas', 'telefone', 'e', 'telefone'),
                self::selectColumn('empresas', 'responsavel_nome', 'e', 'responsavel_nome'),
                self::selectColumn('empresas', 'crm_status_contrato', 'e', 'crm_status_contrato'),
                self::selectColumn('empresas', 'crm_contato_nome', 'e', 'crm_contato_nome'),
                self::selectColumn('empresas', 'crm_contato_email', 'e', 'crm_contato_email'),
                self::selectColumn('empresas', 'crm_contato_whatsapp', 'e', 'crm_contato_whatsapp'),
                self::selectColumn('empresas', 'crm_health_manual', 'e', 'crm_health_manual'),
                self::selectColumn('empresas', 'crm_observacoes', 'e', 'crm_observacoes'),
                self::selectColumn('empresas', 'crm_ultima_reuniao_em', 'e', 'crm_ultima_reuniao_em'),
                DB::raw('NULL as owner_name'),
            ]);

        if (! method_exists($user, 'isSuperAdmin') || ! $user->isSuperAdmin()) {
            $query->where('e.id', $user->empresa_id ?: 0);
        }

        return $query->orderByDesc('e.updated_at')->get()->all();
    }


    protected static function selectColumn(string $table, string $column, string $alias, string $as, string $fallback = 'NULL'): mixed
    {
        if (CachedSchema::hasColumn($table, $column)) {
            return DB::raw($alias . '.' . $column . ' as ' . $as);
        }

        return DB::raw($fallback . ' as ' . $as);
    }


    protected static function preloadStats(array $rows): array
    {
        $empresaIds = collect($rows)
            ->pluck('empresa_id')
            ->map(fn ($id): int => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        $crmIds = collect($rows)
            ->pluck('id')
            ->map(fn ($id): int => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        return [
            'items' => self::itemStatsForEmpresas($empresaIds),
            'payments' => self::paymentStatsForEmpresas($empresaIds),
            'portal' => self::portalStatsForEmpresas($empresaIds),
            'pendencias' => self::pendenciaStatsForClientes($crmIds),
        ];
    }

    protected static function emptyItemStats(): array
    {
        return ['open_items' => 0, 'late_items' => 0, 'review_items' => 0, 'onboarding_items' => 0];
    }

    protected static function emptyPortalStats(): array
    {
        return ['documents_count' => 0, 'messages_count' => 0, 'requests_count' => 0];
    }

    protected static function formatClient(object $row, array $stats = []): array
    {
        $empresaId = (int) ($row->empresa_id ?? 0);
        $crmId = (int) ($row->id ?? $empresaId);

        $itemStats = $stats['items'][$empresaId] ?? self::emptyItemStats();
        $paymentStats = $stats['payments'][$empresaId] ?? ['paid_total' => 0];
        $portalStats = $stats['portal'][$empresaId] ?? self::emptyPortalStats();
        $pendenciaStats = $stats['pendencias'][$crmId] ?? ['open_items' => 0];

        $statusKey = self::normalizeStatus($row->crm_status_contrato ?: $row->situacao ?: null);
        $statusLabel = self::statusLabel($statusKey);
        $healthKey = self::normalizeHealth($row->crm_health_manual ?: $row->risco_churn ?: null);

        $lastContact = self::dateValue($row->crm_ultima_reuniao_em ?: $row->ultimo_contato_em ?: null);
        $lateItems = (int) ($itemStats['late_items'] ?? 0);
        $openItems = (int) ($itemStats['open_items'] ?? 0) + (int) ($pendenciaStats['open_items'] ?? 0);
        $reviewItems = (int) ($itemStats['review_items'] ?? 0);

        if (! $healthKey) {
            $daysWithoutContact = $lastContact ? $lastContact->diffInDays(now()) : 999;
            $healthKey = $lateItems > 0 || $statusKey === 'em_risco' || $daysWithoutContact > 30 ? 'critico' : ($openItems > 0 || $daysWithoutContact > 14 ? 'atencao' : 'saudavel');
        }

        $healthScore = match ($healthKey) {
            'critico' => max(12, 45 - ($lateItems * 8)),
            'atencao' => max(46, 72 - ($lateItems * 5)),
            default => min(98, 88 + max(0, 5 - $openItems)),
        };

        $healthDetails = self::healthDetails($healthScore, $healthKey, $lateItems, $openItems, $reviewItems, $lastContact ? $lastContact->diffInDays(now()) : null, $statusKey);

        $name = $row->nome_fantasia ?: $row->razao_social ?: ('Cliente #' . $empresaId);
        $contactName = $row->crm_contato_nome ?: $row->responsavel_nome ?: $name;
        $contactEmail = $row->crm_contato_email ?: $row->email;
        $contactPhone = $row->crm_contato_whatsapp ?: $row->telefone;
        $nextAction = $row->proxima_acao ?: ($openItems > 0 ? 'Revisar pendências abertas do cliente.' : 'Manter acompanhamento preventivo.');
        $daysWithoutContact = $lastContact ? $lastContact->diffInDays(now()) : null;
        $action = self::actionForClient($statusKey, $healthKey, $lateItems, $openItems, $reviewItems, $daysWithoutContact, self::isDue($row->proximo_followup_em ?? null));
        $operation = self::operationState($action, $healthScore, $daysWithoutContact);

        return [
            'id' => $crmId,
            'empresa_id' => $empresaId,
            'name' => $name,
            'nome' => $name,
            'document' => $row->cnpj ?: '',
            'contact_name' => $contactName,
            'contact_email' => $contactEmail ?: '',
            'contact_whatsapp' => $contactPhone ?: '',
            'contract_status' => $statusLabel,
            'contract_status_key' => $statusKey,
            'status_tone' => self::statusTone($statusKey),
            'ltv' => (float) ($paymentStats['paid_total'] ?: $row->valor_contrato ?: 0),
            'monthly_value' => (float) ($row->valor_mensal ?: 0),
            'last_meeting' => $lastContact ? $lastContact->format('d/m/Y') : 'Sem reunião registrada',
            'last_meeting_at' => $lastContact?->toDateTimeString(),
            'last_meeting_days' => $daysWithoutContact,
            'health_key' => $healthKey,
            'health_label' => self::healthLabel($healthKey),
            'health_tone' => self::healthTone($healthKey),
            'health_score' => (int) $healthScore,
            'open_items' => $openItems,
            'late_items' => $lateItems,
            'review_items' => $reviewItems,
            'onboarding_items' => (int) ($itemStats['onboarding_items'] ?? 0),
            'documents_count' => (int) ($portalStats['documents_count'] ?? 0),
            'messages_count' => (int) ($portalStats['messages_count'] ?? 0),
            'requests_count' => (int) ($portalStats['requests_count'] ?? 0),
            'next_action' => $nextAction,
            'owner_name' => $row->owner_name ?: 'Sem responsável',
            'observacoes' => $row->crm_observacoes ?: '',
            'next_followup_at' => $row->proximo_followup_em ?? null,
            'next_followup_label' => self::dateValue($row->proximo_followup_em ?? null)?->format('d/m/Y H:i') ?: 'Sem follow-up definido',
            'followup_due' => self::isDue($row->proximo_followup_em ?? null),
            'action_status' => $action['status'],
            'action_label' => $action['label'],
            'action_tone' => $action['tone'],
            'action_reason' => $action['reason'],
            'action_priority' => $action['priority'],
            'primary_problem' => $action['reason'],
            'recommended_next_step' => $action['next_step'],
            'health_breakdown' => $healthDetails['breakdown'],
            'health_score_caption' => $healthDetails['caption'],
            'health_score_trend' => $healthDetails['trend'],
            'health_score_tone' => $healthDetails['tone'],
            'stagnation_status' => $operation['stagnation_status'],
            'operation_state' => $operation['state'],
            'operation_label' => $operation['label'],
            'operation_tone' => $operation['tone'],
            'updated_at' => $row->updated_at ?? null,
            'updated_at_sort' => $row->updated_at ? strtotime((string) $row->updated_at) : 0,
        ];
    }


    protected static function actionForClient(string $statusKey, string $healthKey, int $lateItems, int $openItems, int $reviewItems, ?int $daysWithoutContact, bool $followupDue): array
    {
        if ($lateItems > 0) {
            return [
                'status' => 'critico',
                'label' => 'Resolver atraso',
                'tone' => 'danger',
                'reason' => $lateItems === 1 ? 'Existe 1 item atrasado bloqueando o cliente.' : "Existem {$lateItems} itens atrasados bloqueando o cliente.",
                'next_step' => 'Concluir a pendência atrasada ou registrar contato com combinado claro.',
                'priority' => 500 + $lateItems,
            ];
        }

        if ($statusKey === 'em_risco' || $healthKey === 'critico') {
            return [
                'status' => 'critico',
                'label' => 'Atuar em risco',
                'tone' => 'danger',
                'reason' => 'Cliente em risco ou com saúde crítica. Priorize contato e plano de recuperação.',
                'next_step' => 'Registrar contato e definir plano de recuperação com prazo.',
                'priority' => 460,
            ];
        }

        if ($followupDue) {
            return [
                'status' => 'atencao',
                'label' => 'Fazer follow-up',
                'tone' => 'warning',
                'reason' => 'Há follow-up vencendo ou vencido para hoje.',
                'next_step' => 'Fazer follow-up e atualizar o próximo passo no histórico.',
                'priority' => 390,
            ];
        }

        if ($openItems > 0) {
            return [
                'status' => 'atencao',
                'label' => 'Revisar pendências',
                'tone' => 'warning',
                'reason' => $openItems === 1 ? 'Existe 1 pendência aberta para tratar.' : "Existem {$openItems} pendências abertas para tratar.",
                'next_step' => 'Concluir a próxima pendência ou registrar motivo do bloqueio.',
                'priority' => 320 + $openItems,
            ];
        }

        if ($reviewItems > 0) {
            return [
                'status' => 'atencao',
                'label' => 'Revisar aprovação',
                'tone' => 'warning',
                'reason' => 'Cliente possui item em revisão ou aprovação.',
                'next_step' => 'Revisar aprovação pendente e atualizar o responsável.',
                'priority' => 280 + $reviewItems,
            ];
        }

        if ($daysWithoutContact === null || $daysWithoutContact > 30) {
            return [
                'status' => 'sem_contato',
                'label' => 'Retomar contato',
                'tone' => 'info',
                'reason' => $daysWithoutContact === null ? 'Cliente ainda não possui contato registrado.' : "Último contato há {$daysWithoutContact} dias.",
                'next_step' => 'Registrar um contato rápido para tirar o cliente da fila parada.',
                'priority' => 230,
            ];
        }

        if ($daysWithoutContact > 14) {
            return [
                'status' => 'sem_contato',
                'label' => 'Agendar contato',
                'tone' => 'info',
                'reason' => "Último contato há {$daysWithoutContact} dias. Vale fazer acompanhamento preventivo.",
                'next_step' => 'Agendar ou registrar contato preventivo.',
                'priority' => 180,
            ];
        }

        return [
            'status' => 'normal',
            'label' => 'Acompanhar',
            'tone' => 'ok',
            'reason' => 'Cliente sem ação crítica no momento.',
            'next_step' => 'Manter acompanhamento normal.',
            'priority' => 60,
        ];
    }

    protected static function actionSummary(array $clients): array
    {
        $collection = collect($clients);

        return [
            'criticos' => [
                'count' => $collection->where('action_status', 'critico')->count(),
                'label' => 'Críticos',
                'description' => 'Atraso, risco ou saúde crítica',
                'filter' => 'criticos',
                'tone' => 'danger',
            ],
            'atencao' => [
                'count' => $collection->where('action_status', 'atencao')->count(),
                'label' => 'Atenção',
                'description' => 'Follow-up, pendência ou revisão',
                'filter' => 'atencao',
                'tone' => 'warning',
            ],
            'sem_contato' => [
                'count' => $collection->where('action_status', 'sem_contato')->count(),
                'label' => 'Sem contato',
                'description' => 'Clientes parados ou sem registro',
                'filter' => 'sem_contato',
                'tone' => 'info',
            ],
            'followups' => [
                'count' => $collection->where('followup_due', true)->count(),
                'label' => 'Follow-ups hoje',
                'description' => 'Contatos que vencem hoje',
                'filter' => 'followups',
                'tone' => 'warning',
            ],
            'pendencias' => [
                'count' => $collection->filter(fn (array $client): bool => (int) ($client['open_items'] ?? 0) > 0)->count(),
                'label' => 'Com pendências',
                'description' => 'Clientes com itens abertos',
                'filter' => 'pendencias',
                'tone' => 'neutral',
            ],
        ];
    }


    protected static function healthDetails(int $score, string $healthKey, int $lateItems, int $openItems, int $reviewItems, ?int $daysWithoutContact, string $statusKey): array
    {
        $breakdown = [];

        if ($lateItems > 0) {
            $breakdown[] = $lateItems === 1 ? '1 atraso aberto' : "{$lateItems} atrasos abertos";
        }

        if ($openItems > 0) {
            $breakdown[] = $openItems === 1 ? '1 pendência aberta' : "{$openItems} pendências abertas";
        }

        if ($reviewItems > 0) {
            $breakdown[] = $reviewItems === 1 ? '1 item em revisão' : "{$reviewItems} itens em revisão";
        }

        if ($daysWithoutContact === null) {
            $breakdown[] = 'sem contato registrado';
        } elseif ($daysWithoutContact > 14) {
            $breakdown[] = "{$daysWithoutContact} dias sem contato";
        }

        if ($statusKey === 'em_risco') {
            $breakdown[] = 'contrato em risco';
        }

        if ($breakdown === []) {
            $breakdown[] = 'carteira estável';
        }

        $caption = match (true) {
            $score <= 45 => 'Risco alto: precisa de ação objetiva hoje.',
            $score <= 72 => 'Atenção: existe algo para acompanhar de perto.',
            default => 'Estável: manter rotina de acompanhamento.',
        };

        return [
            'breakdown' => $breakdown,
            'caption' => $caption,
            'trend' => $healthKey === 'critico' ? 'queda' : ($healthKey === 'atencao' ? 'atenção' : 'estável'),
            'tone' => $score <= 45 ? 'danger' : ($score <= 72 ? 'warning' : 'ok'),
        ];
    }

    protected static function operationState(array $action, int $healthScore, ?int $daysWithoutContact): array
    {
        if (($action['status'] ?? '') === 'critico' || $healthScore <= 45) {
            return [
                'state' => 'bloqueado',
                'label' => 'Ação obrigatória',
                'tone' => 'danger',
                'stagnation_status' => $daysWithoutContact === null ? 'Sem histórico de contato' : "{$daysWithoutContact} dia(s) sem contato",
            ];
        }

        if (($action['status'] ?? '') === 'atencao' || $healthScore <= 72) {
            return [
                'state' => 'em_andamento',
                'label' => 'Em acompanhamento',
                'tone' => 'warning',
                'stagnation_status' => $daysWithoutContact === null ? 'Sem contato registrado' : "Último contato há {$daysWithoutContact} dia(s)",
            ];
        }

        if (($action['status'] ?? '') === 'sem_contato') {
            return [
                'state' => 'parado',
                'label' => 'Cliente parado',
                'tone' => 'info',
                'stagnation_status' => $daysWithoutContact === null ? 'Nunca contatado' : "{$daysWithoutContact} dia(s) sem contato",
            ];
        }

        return [
            'state' => 'ok',
            'label' => 'Sem bloqueio',
            'tone' => 'ok',
            'stagnation_status' => $daysWithoutContact === null ? 'Sem contato registrado' : "Último contato há {$daysWithoutContact} dia(s)",
        ];
    }

    protected static function insights(array $clients): array
    {
        $collection = collect($clients);
        $critical = $collection->where('operation_state', 'bloqueado')->count();
        $stalled = $collection->where('operation_state', 'parado')->count();
        $withoutHistory = $collection->filter(fn (array $client): bool => is_null($client['last_meeting_days'] ?? null))->count();
        $best = $collection->sortByDesc('health_score')->first();
        $worst = $collection->sortBy('health_score')->first();

        $insights = [];

        if ($critical > 0) {
            $insights[] = [
                'title' => 'Prioridade do dia',
                'text' => $critical === 1 ? 'Existe 1 cliente bloqueado para tratar hoje.' : "Existem {$critical} clientes bloqueados para tratar hoje.",
                'tone' => 'danger',
            ];
        }

        if ($stalled > 0) {
            $insights[] = [
                'title' => 'Carteira parada',
                'text' => $stalled === 1 ? 'Existe 1 cliente parado por falta de contato recente.' : "Existem {$stalled} clientes parados por falta de contato recente.",
                'tone' => 'info',
            ];
        }

        if ($withoutHistory > 0) {
            $insights[] = [
                'title' => 'Sem histórico',
                'text' => $withoutHistory === 1 ? '1 cliente ainda não tem contato registrado.' : "{$withoutHistory} clientes ainda não têm contato registrado.",
                'tone' => 'warning',
            ];
        }

        if (is_array($worst)) {
            $insights[] = [
                'title' => 'Menor health score',
                'text' => ($worst['name'] ?? 'Cliente') . ' está com ' . (int) ($worst['health_score'] ?? 0) . '%. Próximo passo: ' . ($worst['recommended_next_step'] ?? 'revisar relacionamento.'),
                'tone' => $worst['health_score_tone'] ?? 'warning',
            ];
        }

        if ($insights === [] && is_array($best)) {
            $insights[] = [
                'title' => 'Carteira estável',
                'text' => 'Nenhum alerta crítico encontrado. Mantenha a rotina de acompanhamento preventivo.',
                'tone' => 'ok',
            ];
        }

        return array_slice($insights, 0, 4);
    }

    protected static function itemStatsForEmpresas(array $empresaIds): array
    {
        $empresaIds = collect($empresaIds)->map(fn ($id): int => (int) $id)->filter()->unique()->values()->all();

        if (! $empresaIds || ! CachedSchema::hasTable('item_controles') || ! CachedSchema::hasColumn('item_controles', 'empresa_id')) {
            return [];
        }

        $today = now()->toDateString();
        $closedStatuses = ['concluido', 'concluído', 'finalizado', 'cancelado'];
        $stats = collect($empresaIds)->mapWithKeys(fn (int $empresaId): array => [$empresaId => self::emptyItemStats()])->all();

        DB::table('item_controles')
            ->whereIn('empresa_id', $empresaIds)
            ->select(['empresa_id', 'status', 'tipo', 'data_vencimento', 'titulo'])
            ->orderBy('empresa_id')
            ->chunk(1000, function ($rows) use (&$stats, $today, $closedStatuses): void {
                foreach ($rows as $row) {
                    $empresaId = (int) ($row->empresa_id ?? 0);

                    if (! $empresaId) {
                        continue;
                    }

                    $stats[$empresaId] ??= self::emptyItemStats();

                    $status = (string) ($row->status ?? '');
                    $statusLower = Str::lower($status);
                    $isClosed = in_array($status, $closedStatuses, true);

                    if (! $isClosed) {
                        $stats[$empresaId]['open_items']++;
                    }

                    if (! $isClosed && ! empty($row->data_vencimento) && (string) $row->data_vencimento < $today) {
                        $stats[$empresaId]['late_items']++;
                    }

                    if (Str::contains($statusLower, ['revis', 'aprov'])) {
                        $stats[$empresaId]['review_items']++;
                    }

                    $onboardingText = Str::lower((string) ($row->titulo ?? '') . ' ' . (string) ($row->tipo ?? ''));
                    if (Str::contains($onboardingText, ['onboarding', 'implant', 'implement'])) {
                        $stats[$empresaId]['onboarding_items']++;
                    }
                }
            });

        return $stats;
    }

    protected static function paymentStatsForEmpresas(array $empresaIds): array
    {
        $empresaIds = collect($empresaIds)->map(fn ($id): int => (int) $id)->filter()->unique()->values()->all();

        if (! $empresaIds || ! CachedSchema::hasTable('pagamentos') || ! CachedSchema::hasColumn('pagamentos', 'empresa_id')) {
            return [];
        }

        $stats = collect($empresaIds)->mapWithKeys(fn (int $empresaId): array => [$empresaId => ['paid_total' => 0]])->all();
        $query = DB::table('pagamentos')
            ->whereIn('empresa_id', $empresaIds)
            ->select('empresa_id', DB::raw('COALESCE(SUM(valor), 0) as paid_total'));

        if (CachedSchema::hasColumn('pagamentos', 'status')) {
            $query->whereIn('status', ['RECEIVED', 'CONFIRMED', 'RECEIVED_IN_CASH', 'pago', 'paid']);
        }

        $query->groupBy('empresa_id')->get()->each(function ($row) use (&$stats): void {
            $stats[(int) $row->empresa_id] = ['paid_total' => (float) $row->paid_total];
        });

        return $stats;
    }

    protected static function portalStatsForEmpresas(array $empresaIds): array
    {
        $empresaIds = collect($empresaIds)->map(fn ($id): int => (int) $id)->filter()->unique()->values()->all();

        if (! $empresaIds) {
            return [];
        }

        $stats = collect($empresaIds)->mapWithKeys(fn (int $empresaId): array => [$empresaId => self::emptyPortalStats()])->all();

        foreach ([
            'portal_documentos' => 'documents_count',
            'portal_mensagens' => 'messages_count',
            'portal_solicitacoes' => 'requests_count',
        ] as $table => $key) {
            if (! CachedSchema::hasTable($table) || ! CachedSchema::hasColumn($table, 'empresa_id')) {
                continue;
            }

            DB::table($table)
                ->whereIn('empresa_id', $empresaIds)
                ->select('empresa_id', DB::raw('COUNT(*) as total'))
                ->groupBy('empresa_id')
                ->get()
                ->each(function ($row) use (&$stats, $key): void {
                    $empresaId = (int) $row->empresa_id;
                    $stats[$empresaId] ??= self::emptyPortalStats();
                    $stats[$empresaId][$key] = (int) $row->total;
                });
        }

        return $stats;
    }

    protected static function pendenciaStatsForClientes(array $clienteIds): array
    {
        $clienteIds = collect($clienteIds)->map(fn ($id): int => (int) $id)->filter()->unique()->values()->all();

        if (! $clienteIds || ! CachedSchema::hasTable('crm_pendencias') || ! CachedSchema::hasColumn('crm_pendencias', 'crm_cliente_id')) {
            return [];
        }

        $stats = collect($clienteIds)->mapWithKeys(fn (int $clienteId): array => [$clienteId => ['open_items' => 0]])->all();

        DB::table('crm_pendencias')
            ->whereIn('crm_cliente_id', $clienteIds)
            ->where(function ($query): void {
                $query->whereNull('status')->orWhereNotIn('status', ['concluido', 'concluído', 'finalizado', 'cancelado']);
            })
            ->select('crm_cliente_id', DB::raw('COUNT(*) as open_items'))
            ->groupBy('crm_cliente_id')
            ->get()
            ->each(function ($row) use (&$stats): void {
                $stats[(int) $row->crm_cliente_id] = ['open_items' => (int) $row->open_items];
            });

        return $stats;
    }

    protected static function summary(array $clients): array
    {
        $collection = collect($clients);

        return [
            'total' => $collection->count(),
            'active' => $collection->whereIn('contract_status_key', ['operando_bem', 'em_implementacao', 'renovacao'])->count(),
            'risk' => $collection->filter(fn (array $client): bool => $client['contract_status_key'] === 'em_risco' || $client['health_key'] === 'critico' || (int) $client['late_items'] > 0)->count(),
            'ltv' => (float) $collection->sum('ltv'),
        ];
    }

    protected static function groupSummary(array $clients, string $labelKey, string $toneKey): array
    {
        return collect($clients)
            ->groupBy($labelKey)
            ->map(fn ($items, string $label): array => ['label' => $label, 'count' => $items->count(), 'tone' => $items->first()[$toneKey] ?? 'neutral'])
            ->sortByDesc('count')
            ->values()
            ->all();
    }

    protected static function pendencias(array $clienteIds): array
    {
        if (! $clienteIds || ! CachedSchema::hasTable('crm_pendencias')) {
            return [];
        }

        return DB::table('crm_pendencias')
            ->whereIn('crm_cliente_id', $clienteIds)
            ->orderByRaw("CASE WHEN status = 'pendente' OR status IS NULL THEN 1 ELSE 2 END")
            ->orderByDesc('created_at')
            ->limit(80)
            ->get()
            ->map(fn ($row): array => [
                'id' => (int) $row->id,
                'crm_cliente_id' => (int) $row->crm_cliente_id,
                'client_id' => (int) $row->crm_cliente_id,
                'titulo' => $row->titulo ?: 'Pendência',
                'title' => $row->titulo ?: 'Pendência',
                'status' => $row->status ?: 'pendente',
                'created_at' => $row->created_at ?? null,
            ])->values()->all();
    }

    protected static function historicos(array $clienteIds): array
    {
        if (! $clienteIds || ! CachedSchema::hasTable('crm_historicos')) {
            return [];
        }

        return DB::table('crm_historicos')
            ->whereIn('crm_cliente_id', $clienteIds)
            ->orderByDesc('created_at')
            ->limit(80)
            ->get()
            ->map(fn ($row): array => [
                'id' => (int) $row->id,
                'crm_cliente_id' => (int) $row->crm_cliente_id,
                'client_id' => (int) $row->crm_cliente_id,
                'tipo' => $row->tipo ?: 'registro',
                'type' => $row->tipo ?: 'registro',
                'descricao' => $row->descricao ?: '',
                'description' => $row->descricao ?: '',
                'created_at' => $row->created_at ?? null,
            ])->values()->all();
    }


    protected static function documentos(array $empresaIds): array
    {
        $empresaIds = collect($empresaIds)->filter()->unique()->values()->all();

        if (! $empresaIds || ! CachedSchema::hasTable('portal_documentos')) {
            return [];
        }

        return DB::table('portal_documentos')
            ->whereIn('empresa_id', $empresaIds)
            ->orderByDesc('updated_at')
            ->orderByDesc('created_at')
            ->limit(120)
            ->get()
            ->map(fn ($row): array => [
                'id' => (int) $row->id,
                'empresa_id' => (int) $row->empresa_id,
                'titulo' => $row->titulo ?: 'Documento',
                'tipo' => $row->tipo ?: 'documento',
                'status' => isset($row->status) ? (string) $row->status : ((isset($row->visivel_cliente) && $row->visivel_cliente) ? 'visível' : 'interno'),
                'url' => $row->url ?? null,
                'created_at' => $row->created_at ?? null,
                'updated_at' => $row->updated_at ?? null,
            ])->values()->all();
    }

    protected static function proximosContatos(array $clients): array
    {
        return collect($clients)
            ->filter(fn (array $client): bool => ! empty($client['next_followup_at']) || ! empty($client['followup_due']))
            ->sortBy(fn (array $client): int => ! empty($client['next_followup_at']) ? strtotime((string) $client['next_followup_at']) : PHP_INT_MAX)
            ->take(12)
            ->map(fn (array $client): array => [
                'id' => (int) $client['id'],
                'empresa_id' => (int) $client['empresa_id'],
                'name' => $client['name'],
                'next_action' => $client['next_action'],
                'next_followup_label' => $client['next_followup_label'] ?? 'Sem data definida',
                'tone' => ! empty($client['followup_due']) ? 'danger' : ($client['health_tone'] ?? 'info'),
            ])->values()->all();
    }

    protected static function emailHistory(array $clients): array
    {
        $ids = collect($clients)->pluck('id')->filter()->all();
        if (! $ids || ! CachedSchema::hasTable('crm_historicos')) {
            return [];
        }

        $names = collect($clients)->keyBy('id');

        return DB::table('crm_historicos')
            ->whereIn('crm_cliente_id', $ids)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get()
            ->map(function ($row) use ($names): array {
                $client = $names->get((int) $row->crm_cliente_id, []);

                return [
                    'id' => (int) $row->id,
                    'crm_cliente_id' => (int) $row->crm_cliente_id,
                    'nome_fantasia' => $client['name'] ?? null,
                    'razao_social' => $client['name'] ?? null,
                    'titulo' => ucfirst((string) ($row->tipo ?: 'histórico')),
                    'mensagem' => $row->descricao ?: '',
                    'created_at' => $row->created_at ?? null,
                ];
            })->values()->all();
    }

    protected static function dateValue(mixed $value): ?Carbon
    {
        if (! $value) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    protected static function isDue(mixed $value): bool
    {
        $date = self::dateValue($value);

        return $date ? $date->lte(now()) : false;
    }

    protected static function statusTone(string $status): string
    {
        return match ($status) {
            'operando_bem' => 'ok',
            'aguardando_cliente', 'renovacao', 'em_implementacao' => 'warning',
            'em_risco', 'cancelado' => 'danger',
            default => 'neutral',
        };
    }

    protected static function healthTone(string $health): string
    {
        return match ($health) {
            'saudavel' => 'ok',
            'atencao' => 'warning',
            'critico' => 'danger',
            default => 'neutral',
        };
    }
}
