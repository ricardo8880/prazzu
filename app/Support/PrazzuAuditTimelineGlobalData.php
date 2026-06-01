<?php

namespace App\Support;

use App\Filament\Resources\ItemControles\ItemControleResource;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PrazzuAuditTimelineGlobalData
{
    private const CLOSED_STATUSES = ['aprovado', 'aprovada', 'reprovado', 'reprovada', 'concluido', 'concluída', 'concluida', 'finalizado', 'finalizada', 'cancelado', 'cancelada'];

    public static function make(array $filters = []): array
    {
        $filters = self::normalizeFilters($filters);
        $events = self::filteredEvents($filters, 160);
        $allPeriodEvents = self::events($filters, 300, false);
        $criticalEvents = collect($allPeriodEvents)->filter(fn (array $event) => $event['risk'] >= 70)->values()->all();
        $todayEvents = collect($allPeriodEvents)->filter(fn (array $event) => self::sameDay($event['sort_date'] ?? null, now()))->count();
        $pendingApprovals = self::pendingApprovals();
        $withoutEvidence = self::itemsWithoutRecentEvidence();
        $integrityAlerts = self::integrityAlerts($allPeriodEvents, $pendingApprovals, $withoutEvidence);

        return [
            'filters' => $filters,
            'filterOptions' => self::filterOptions(),
            'stats' => [
                ['label' => 'Eventos no período', 'value' => count($allPeriodEvents), 'tone' => 'info', 'hint' => 'Auditoria + operações'],
                ['label' => 'Hoje', 'value' => $todayEvents, 'tone' => 'success', 'hint' => 'Movimentações do dia'],
                ['label' => 'Risco alto', 'value' => count($criticalEvents), 'tone' => count($criticalEvents) > 0 ? 'danger' : 'success', 'hint' => 'Exigem conferência'],
                ['label' => 'Aprovações pendentes', 'value' => count($pendingApprovals), 'tone' => count($pendingApprovals) > 0 ? 'warning' : 'success', 'hint' => 'Decisões abertas'],
            ],
            'events' => $events,
            'groups' => self::groupEvents($events),
            'criticalEvents' => array_slice($criticalEvents, 0, 8),
            'pendingApprovals' => $pendingApprovals,
            'withoutEvidence' => $withoutEvidence,
            'integrityAlerts' => $integrityAlerts,
            'sourceSummary' => self::sourceSummary($allPeriodEvents),
            'userSummary' => self::userSummary($allPeriodEvents),
            'emptySources' => self::emptySources(),
        ];
    }

    private static function normalizeFilters(array $filters): array
    {
        $periodInput = (string) ($filters['period'] ?? '7');
        $typeInput = (string) ($filters['type'] ?? 'all');
        $riskInput = (string) ($filters['risk'] ?? 'all');

        $period = in_array($periodInput, ['today', '7', '30', '90', 'all'], true) ? $periodInput : '7';
        $type = in_array($typeInput, ['all', 'auditoria', 'timeline', 'comentario', 'anexo', 'aprovacao', 'sistema'], true) ? $typeInput : 'all';
        $risk = in_array($riskInput, ['all', 'high', 'medium', 'low'], true) ? $riskInput : 'all';

        return [
            'period' => $period,
            'type' => $type,
            'risk' => $risk,
            'search' => trim((string) ($filters['search'] ?? '')),
        ];
    }

    private static function filterOptions(): array
    {
        return [
            'periods' => [
                'today' => 'Hoje',
                '7' => '7 dias',
                '30' => '30 dias',
                '90' => '90 dias',
                'all' => 'Tudo',
            ],
            'types' => [
                'all' => 'Todos',
                'auditoria' => 'Auditoria',
                'timeline' => 'Timeline',
                'comentario' => 'Comentários',
                'anexo' => 'Anexos',
                'aprovacao' => 'Aprovações',
                'sistema' => 'Sistema',
            ],
            'risks' => [
                'all' => 'Todos os riscos',
                'high' => 'Risco alto',
                'medium' => 'Risco médio',
                'low' => 'Risco baixo',
            ],
        ];
    }

    private static function filteredEvents(array $filters, int $limit): array
    {
        return array_slice(self::events($filters, $limit, true), 0, $limit);
    }

    private static function events(array $filters, int $limit = 160, bool $applyFineFilters = true): array
    {
        $rows = array_merge(
            self::auditRows($limit),
            self::activityRows($limit),
            self::timelineRows($limit),
            self::commentRows($limit),
            self::attachmentRows($limit),
            self::approvalRows($limit)
        );

        $rows = collect($rows)
            ->filter(fn (array $row) => self::matchesPeriod($row['sort_date'] ?? null, $filters['period']))
            ->when($applyFineFilters, function ($collection) use ($filters) {
                return $collection
                    ->filter(fn (array $row) => $filters['type'] === 'all' || ($row['type'] ?? '') === $filters['type'])
                    ->filter(fn (array $row) => self::matchesRisk($row['risk'] ?? 0, $filters['risk']))
                    ->filter(fn (array $row) => self::matchesSearch($row, $filters['search']));
            })
            ->sortByDesc(fn (array $row) => (string) ($row['sort_date'] ?? ''))
            ->values()
            ->all();

        return array_slice($rows, 0, $limit);
    }

    private static function auditRows(int $limit): array
    {
        if (! self::hasTable('auditoria_detalhada')) {
            return [];
        }

        $select = self::selectExisting('auditoria_detalhada', [
            'id', 'acao', 'action', 'descricao', 'description', 'nivel', 'level', 'tabela', 'table_name', 'registro_id', 'record_id', 'user_id', 'usuario_id', 'ip', 'ip_address', 'created_at', 'updated_at',
        ], 'auditoria_detalhada');

        return DB::table('auditoria_detalhada')
            ->select($select)
            ->orderByDesc(self::dateColumn('auditoria_detalhada'))
            ->limit($limit)
            ->get()
            ->map(function ($row) {
                $row = (array) $row;
                $title = self::firstFilled($row, ['acao', 'action', 'descricao', 'description'], 'Evento de auditoria');
                $description = self::firstFilled($row, ['descricao', 'description'], 'Registro de auditoria capturado pelo sistema.');
                $table = self::firstFilled($row, ['tabela', 'table_name'], 'Origem não informada');
                $level = Str::lower(self::firstFilled($row, ['nivel', 'level'], 'normal'));
                $date = self::firstFilled($row, ['created_at', 'updated_at'], null);
                $risk = Str::contains($level, ['crit', 'alto', 'erro', 'falha']) ? 90 : 60;

                return self::eventRow([
                    'title' => $title,
                    'description' => $description,
                    'type' => 'auditoria',
                    'source' => 'Auditoria detalhada',
                    'status' => ucfirst($level ?: 'Normal'),
                    'tone' => $risk >= 80 ? 'danger' : 'warning',
                    'date' => $date,
                    'meta' => $table,
                    'actor' => self::actorFromRow($row),
                    'ip' => self::firstFilled($row, ['ip', 'ip_address'], null),
                    'risk' => $risk,
                ]);
            })
            ->all();
    }

    private static function activityRows(int $limit): array
    {
        if (! self::hasTable('activity_log')) {
            return [];
        }

        $select = self::selectExisting('activity_log', [
            'id', 'log_name', 'description', 'event', 'subject_type', 'subject_id', 'causer_type', 'causer_id', 'properties', 'created_at', 'updated_at',
        ], 'activity_log');

        return DB::table('activity_log')
            ->select($select)
            ->orderByDesc(self::dateColumn('activity_log'))
            ->limit($limit)
            ->get()
            ->map(function ($row) {
                $row = (array) $row;
                $event = self::firstFilled($row, ['event'], 'log');
                $date = self::firstFilled($row, ['created_at', 'updated_at'], null);
                $risk = Str::contains(Str::lower($event.' '.($row['description'] ?? '')), ['delete', 'deleted', 'excluir', 'remove', 'erro', 'falha']) ? 85 : 45;

                return self::eventRow([
                    'title' => self::firstFilled($row, ['description'], 'Atividade do sistema'),
                    'description' => 'Registro técnico: '.self::firstFilled($row, ['subject_type'], 'sem assunto vinculado'),
                    'type' => 'sistema',
                    'source' => 'Activity log',
                    'status' => ucfirst((string) $event),
                    'tone' => $risk >= 80 ? 'danger' : 'info',
                    'date' => $date,
                    'meta' => self::firstFilled($row, ['log_name'], 'Sistema'),
                    'actor' => self::actorFromRow($row),
                    'risk' => $risk,
                ]);
            })
            ->all();
    }

    private static function timelineRows(int $limit): array
    {
        if (! self::hasTable('item_controle_timeline')) {
            return [];
        }

        return DB::table('item_controle_timeline')
            ->leftJoin('item_controles', 'item_controles.id', '=', 'item_controle_timeline.item_controle_id')
            ->leftJoin('responsaveis', 'responsaveis.id', '=', 'item_controles.responsavel_id')
            ->leftJoin('empresas', 'empresas.id', '=', 'item_controles.empresa_id')
            ->select([
                'item_controle_timeline.id', 'item_controle_timeline.titulo', 'item_controle_timeline.descricao', 'item_controle_timeline.tipo', 'item_controle_timeline.created_at',
                'item_controles.id as item_id', 'item_controles.titulo as item_titulo', 'item_controles.status as item_status', 'item_controles.prioridade',
                'responsaveis.nome as responsavel_nome', 'empresas.nome_fantasia', 'empresas.razao_social',
            ])
            ->orderByDesc('item_controle_timeline.created_at')
            ->limit($limit)
            ->get()
            ->map(function ($row) {
                $row = (array) $row;
                $priority = Str::lower((string) ($row['prioridade'] ?? ''));
                $risk = Str::contains($priority, ['crit', 'alta']) ? 80 : 40;
                $itemTitle = $row['item_titulo'] ?: 'Item não identificado';

                return self::eventRow([
                    'title' => $row['titulo'] ?: 'Movimentação no item',
                    'description' => $row['descricao'] ?: 'Evento registrado na linha do tempo do item.',
                    'type' => 'timeline',
                    'source' => 'Timeline operacional',
                    'status' => ucfirst((string) ($row['tipo'] ?: 'Evento')),
                    'tone' => $risk >= 70 ? 'warning' : 'info',
                    'date' => $row['created_at'] ?? null,
                    'meta' => $itemTitle.' • '.self::companyName($row),
                    'actor' => $row['responsavel_nome'] ?: 'Sistema',
                    'risk' => $risk,
                    'url' => self::itemUrl($row['item_id'] ?? null),
                ]);
            })
            ->all();
    }

    private static function commentRows(int $limit): array
    {
        if (! self::hasTable('item_controle_comentarios')) {
            return [];
        }

        $commentColumn = self::hasColumn('item_controle_comentarios', 'comentario') ? 'comentario' : (self::hasColumn('item_controle_comentarios', 'comment') ? 'comment' : 'descricao');

        return DB::table('item_controle_comentarios')
            ->leftJoin('item_controles', 'item_controles.id', '=', 'item_controle_comentarios.item_controle_id')
            ->leftJoin('responsaveis', 'responsaveis.id', '=', 'item_controles.responsavel_id')
            ->leftJoin('empresas', 'empresas.id', '=', 'item_controles.empresa_id')
            ->select([
                'item_controle_comentarios.id', 'item_controle_comentarios.created_at', 'item_controle_comentarios.'.$commentColumn.' as comentario',
                'item_controles.id as item_id', 'item_controles.titulo as item_titulo', 'responsaveis.nome as responsavel_nome', 'empresas.nome_fantasia', 'empresas.razao_social',
            ])
            ->orderByDesc('item_controle_comentarios.created_at')
            ->limit($limit)
            ->get()
            ->map(function ($row) {
                $row = (array) $row;
                $text = (string) ($row['comentario'] ?? '');
                $risk = Str::contains(Str::lower($text), ['urgente', 'atras', 'erro', 'problema', 'bloque', '@']) ? 65 : 30;

                return self::eventRow([
                    'title' => Str::contains($text, '@') ? 'Comentário com menção' : 'Comentário registrado',
                    'description' => $text ?: 'Comentário sem texto.',
                    'type' => 'comentario',
                    'source' => 'Comentários',
                    'status' => Str::contains($text, '@') ? 'Menção' : 'Interação',
                    'tone' => Str::contains($text, '@') ? 'warning' : 'success',
                    'date' => $row['created_at'] ?? null,
                    'meta' => ($row['item_titulo'] ?: 'Sem item').' • '.self::companyName($row),
                    'actor' => $row['responsavel_nome'] ?: 'Usuário',
                    'risk' => $risk,
                    'url' => self::itemUrl($row['item_id'] ?? null),
                ]);
            })
            ->all();
    }

    private static function attachmentRows(int $limit): array
    {
        if (! self::hasTable('item_controle_anexos')) {
            return [];
        }

        $nameColumn = self::hasColumn('item_controle_anexos', 'nome_original') ? 'nome_original' : (self::hasColumn('item_controle_anexos', 'nome') ? 'nome' : 'id');
        $dateColumn = self::dateColumn('item_controle_anexos');

        return DB::table('item_controle_anexos')
            ->leftJoin('item_controles', 'item_controles.id', '=', 'item_controle_anexos.item_controle_id')
            ->leftJoin('empresas', 'empresas.id', '=', 'item_controles.empresa_id')
            ->select([
                'item_controle_anexos.id', 'item_controle_anexos.'.$nameColumn.' as nome_anexo', 'item_controle_anexos.'.$dateColumn.' as data_evento',
                'item_controles.id as item_id', 'item_controles.titulo as item_titulo', 'empresas.nome_fantasia', 'empresas.razao_social',
            ])
            ->orderByDesc('item_controle_anexos.'.$dateColumn)
            ->limit($limit)
            ->get()
            ->map(function ($row) {
                $row = (array) $row;

                return self::eventRow([
                    'title' => 'Evidência anexada',
                    'description' => 'Arquivo: '.($row['nome_anexo'] ?: 'Anexo sem nome'),
                    'type' => 'anexo',
                    'source' => 'Evidências',
                    'status' => 'Anexo',
                    'tone' => 'success',
                    'date' => $row['data_evento'] ?? null,
                    'meta' => ($row['item_titulo'] ?: 'Sem item').' • '.self::companyName($row),
                    'actor' => 'Usuário',
                    'risk' => 20,
                    'url' => self::itemUrl($row['item_id'] ?? null),
                ]);
            })
            ->all();
    }

    private static function approvalRows(int $limit): array
    {
        if (! self::hasTable('item_controle_aprovacoes')) {
            return [];
        }

        return DB::table('item_controle_aprovacoes')
            ->leftJoin('item_controles', 'item_controles.id', '=', 'item_controle_aprovacoes.item_controle_id')
            ->leftJoin('empresas', 'empresas.id', '=', 'item_controle_aprovacoes.empresa_id')
            ->select([
                'item_controle_aprovacoes.id', 'item_controle_aprovacoes.item_controle_id', 'item_controle_aprovacoes.status', 'item_controle_aprovacoes.observacao_solicitacao', 'item_controle_aprovacoes.observacao_resposta', 'item_controle_aprovacoes.motivo_reprovacao', 'item_controle_aprovacoes.solicitado_em', 'item_controle_aprovacoes.respondido_em',
                'item_controles.titulo as item_titulo', 'item_controles.prioridade', 'empresas.nome_fantasia', 'empresas.razao_social',
            ])
            ->orderByRaw("CASE WHEN item_controle_aprovacoes.status = 'pendente' THEN 0 ELSE 1 END")
            ->orderByDesc('item_controle_aprovacoes.solicitado_em')
            ->limit($limit)
            ->get()
            ->map(function ($row) {
                $row = (array) $row;
                $status = Str::lower((string) ($row['status'] ?? 'pendente'));
                $date = $row['respondido_em'] ?: $row['solicitado_em'];
                $pending = $status === 'pendente';
                $rejected = Str::contains($status, ['reprov']);
                $risk = $pending ? 75 : ($rejected ? 85 : 35);

                return self::eventRow([
                    'title' => $pending ? 'Aprovação pendente' : ($rejected ? 'Aprovação reprovada' : 'Aprovação concluída'),
                    'description' => $row['motivo_reprovacao'] ?: ($row['observacao_resposta'] ?: ($row['observacao_solicitacao'] ?: 'Solicitação de aprovação registrada.')),
                    'type' => 'aprovacao',
                    'source' => 'Aprovações',
                    'status' => ucfirst($status ?: 'Pendente'),
                    'tone' => $rejected ? 'danger' : ($pending ? 'warning' : 'success'),
                    'date' => $date,
                    'meta' => ($row['item_titulo'] ?: 'Sem item').' • '.self::companyName($row),
                    'actor' => 'Fluxo de aprovação',
                    'risk' => $risk,
                    'url' => self::itemUrl($row['item_controle_id'] ?? null),
                ]);
            })
            ->all();
    }

    private static function pendingApprovals(): array
    {
        if (! self::hasTable('item_controle_aprovacoes')) {
            return [];
        }

        return DB::table('item_controle_aprovacoes')
            ->leftJoin('item_controles', 'item_controles.id', '=', 'item_controle_aprovacoes.item_controle_id')
            ->leftJoin('empresas', 'empresas.id', '=', 'item_controle_aprovacoes.empresa_id')
            ->where('item_controle_aprovacoes.status', 'pendente')
            ->select('item_controle_aprovacoes.item_controle_id', 'item_controle_aprovacoes.solicitado_em', 'item_controles.titulo as item_titulo', 'item_controles.prioridade', 'empresas.nome_fantasia', 'empresas.razao_social')
            ->orderBy('item_controle_aprovacoes.solicitado_em')
            ->limit(8)
            ->get()
            ->map(fn ($row) => self::sidebarRow((array) $row, 'Aguardando decisão', $row->solicitado_em, self::itemUrl($row->item_controle_id ?? null)))
            ->all();
    }

    private static function itemsWithoutRecentEvidence(): array
    {
        if (! self::hasTable('item_controles')) {
            return [];
        }

        $query = DB::table('item_controles')
            ->leftJoin('empresas', 'empresas.id', '=', 'item_controles.empresa_id')
            ->select('item_controles.id', 'item_controles.titulo', 'item_controles.status', 'item_controles.updated_at', 'empresas.nome_fantasia', 'empresas.razao_social')
            ->whereNotIn('item_controles.status', self::CLOSED_STATUSES)
            ->orderBy('item_controles.updated_at')
            ->limit(12);

        if (self::hasTable('item_controle_timeline')) {
            $query->whereNotExists(fn ($sub) => $sub->selectRaw('1')->from('item_controle_timeline')->whereColumn('item_controle_timeline.item_controle_id', 'item_controles.id')->where('item_controle_timeline.created_at', '>=', now()->subDays(15)));
        }

        return $query->get()
            ->map(fn ($row) => self::sidebarRow((array) $row, 'Sem evidência recente', $row->updated_at ?? null, self::itemUrl($row->id ?? null)))
            ->all();
    }

    private static function integrityAlerts(array $events, array $pendingApprovals, array $withoutEvidence): array
    {
        $alerts = [];
        $critical = collect($events)->where('risk', '>=', 70)->count();
        if ($critical > 0) {
            $alerts[] = ['title' => 'Revisar eventos críticos', 'description' => $critical.' evento(s) com risco alto no período selecionado.', 'tone' => 'danger'];
        }
        if (count($pendingApprovals) > 0) {
            $alerts[] = ['title' => 'Decisões paradas', 'description' => count($pendingApprovals).' aprovação(ões) pendente(s) aguardando ação.', 'tone' => 'warning'];
        }
        if (count($withoutEvidence) > 0) {
            $alerts[] = ['title' => 'Itens sem trilha recente', 'description' => count($withoutEvidence).' item(ns) aberto(s) sem evidência nos últimos 15 dias.', 'tone' => 'info'];
        }

        return $alerts;
    }

    private static function sourceSummary(array $events): array
    {
        return collect($events)
            ->groupBy('source')
            ->map(fn ($items, $source) => ['label' => (string) $source, 'value' => $items->count(), 'risk' => $items->where('risk', '>=', 70)->count()])
            ->sortByDesc('value')
            ->values()
            ->all();
    }

    private static function userSummary(array $events): array
    {
        return collect($events)
            ->groupBy(fn (array $event) => $event['actor'] ?: 'Sistema')
            ->map(fn ($items, $actor) => ['label' => (string) $actor, 'value' => $items->count(), 'last' => $items->sortByDesc('sort_date')->first()['date_label'] ?? '-'])
            ->sortByDesc('value')
            ->take(8)
            ->values()
            ->all();
    }

    private static function groupEvents(array $events): array
    {
        return collect($events)
            ->groupBy(fn (array $event) => self::groupLabel($event['sort_date'] ?? null))
            ->map(fn ($items, $label) => ['label' => $label, 'items' => $items->values()->all()])
            ->values()
            ->all();
    }

    private static function eventRow(array $data): array
    {
        $date = $data['date'] ?? null;
        $risk = (int) ($data['risk'] ?? 0);

        return [
            'title' => Str::limit((string) ($data['title'] ?? 'Evento'), 95),
            'description' => Str::limit(strip_tags((string) ($data['description'] ?? 'Sem descrição.')), 260),
            'type' => $data['type'] ?? 'timeline',
            'source' => $data['source'] ?? 'Sistema',
            'status' => $data['status'] ?? 'Evento',
            'tone' => $data['tone'] ?? self::toneByRisk($risk),
            'meta' => $data['meta'] ?? 'Sem contexto',
            'actor' => $data['actor'] ?? 'Sistema',
            'ip' => $data['ip'] ?? null,
            'risk' => $risk,
            'risk_label' => $risk >= 70 ? 'Alto' : ($risk >= 40 ? 'Médio' : 'Baixo'),
            'date_label' => self::formatDate($date),
            'relative_date' => self::relativeDate($date),
            'sort_date' => $date,
            'url' => $data['url'] ?? null,
        ];
    }

    private static function sidebarRow(array $row, string $status, mixed $date, ?string $url): array
    {
        return [
            'title' => $row['item_titulo'] ?? $row['titulo'] ?? 'Item sem título',
            'meta' => self::companyName($row),
            'status' => $status,
            'date_label' => self::formatDate($date),
            'relative_date' => self::relativeDate($date),
            'url' => $url,
        ];
    }

    private static function emptySources(): array
    {
        return collect([
            'Auditoria detalhada' => 'auditoria_detalhada',
            'Activity log' => 'activity_log',
            'Timeline operacional' => 'item_controle_timeline',
            'Comentários' => 'item_controle_comentarios',
            'Evidências' => 'item_controle_anexos',
            'Aprovações' => 'item_controle_aprovacoes',
        ])->reject(fn ($table) => self::hasTable($table))->keys()->values()->all();
    }

    private static function matchesPeriod(mixed $date, string $period): bool
    {
        if ($period === 'all') {
            return true;
        }

        if (empty($date)) {
            return false;
        }

        $eventDate = Carbon::parse($date);
        return match ($period) {
            'today' => $eventDate->isSameDay(now()),
            '7' => $eventDate->greaterThanOrEqualTo(now()->subDays(7)),
            '30' => $eventDate->greaterThanOrEqualTo(now()->subDays(30)),
            '90' => $eventDate->greaterThanOrEqualTo(now()->subDays(90)),
            default => true,
        };
    }

    private static function matchesRisk(int $risk, string $filter): bool
    {
        return match ($filter) {
            'high' => $risk >= 70,
            'medium' => $risk >= 40 && $risk < 70,
            'low' => $risk < 40,
            default => true,
        };
    }

    private static function matchesSearch(array $row, string $search): bool
    {
        if ($search === '') {
            return true;
        }

        $haystack = Str::lower(implode(' ', array_filter([
            $row['title'] ?? '', $row['description'] ?? '', $row['meta'] ?? '', $row['actor'] ?? '', $row['source'] ?? '', $row['status'] ?? '',
        ])));

        return Str::contains($haystack, Str::lower($search));
    }

    private static function groupLabel(mixed $date): string
    {
        if (empty($date)) {
            return 'Sem data';
        }

        $carbon = Carbon::parse($date);
        if ($carbon->isToday()) {
            return 'Hoje';
        }
        if ($carbon->isYesterday()) {
            return 'Ontem';
        }

        return $carbon->translatedFormat('d/m/Y');
    }

    private static function sameDay(mixed $date, Carbon $compare): bool
    {
        return ! empty($date) && Carbon::parse($date)->isSameDay($compare);
    }

    private static function formatDate(mixed $date): string
    {
        return empty($date) ? '-' : Carbon::parse($date)->format('d/m/Y H:i');
    }

    private static function relativeDate(mixed $date): string
    {
        return empty($date) ? '-' : Carbon::parse($date)->diffForHumans();
    }

    private static function toneByRisk(int $risk): string
    {
        return $risk >= 70 ? 'danger' : ($risk >= 40 ? 'warning' : 'success');
    }

    private static function companyName(array $row): string
    {
        return $row['nome_fantasia'] ?? $row['razao_social'] ?? 'Sem empresa';
    }

    private static function actorFromRow(array $row): string
    {
        foreach (['user_name', 'usuario_nome', 'causer_id', 'user_id', 'usuario_id'] as $column) {
            if (! empty($row[$column])) {
                return in_array($column, ['causer_id', 'user_id', 'usuario_id'], true) ? 'Usuário #'.$row[$column] : (string) $row[$column];
            }
        }

        return 'Sistema';
    }

    private static function firstFilled(array $row, array $columns, mixed $fallback = null): mixed
    {
        foreach ($columns as $column) {
            if (isset($row[$column]) && $row[$column] !== '') {
                return $row[$column];
            }
        }

        return $fallback;
    }

    private static function selectExisting(string $table, array $columns, string $prefix): array
    {
        return collect($columns)
            ->filter(fn ($column) => self::hasColumn($table, $column))
            ->map(fn ($column) => $prefix.'.'.$column)
            ->values()
            ->all() ?: [$prefix.'.'.self::dateColumn($table)];
    }

    private static function dateColumn(string $table): string
    {
        foreach (['created_at', 'updated_at', 'data', 'date'] as $column) {
            if (self::hasColumn($table, $column)) {
                return $column;
            }
        }

        return 'id';
    }

    private static function itemUrl(mixed $itemId): ?string
    {
        if (empty($itemId)) {
            return null;
        }

        try {
            return ItemControleResource::getUrl('edit', ['record' => $itemId]);
        } catch (\Throwable) {
            return null;
        }
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
