<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PrazzuEnterpriseTimelineData
{
    private const DONE_STATUSES = ['concluido', 'concluida', 'concluído', 'finalizado', 'finalizada', 'cancelado', 'cancelada'];

    public static function make(): array
    {
        $tasks = self::tasks();
        $events = self::events();
        $lanes = self::swimlanes($tasks);
        $milestones = self::milestones($tasks);
        $unscheduled = self::unscheduledTasks();
        $overlaps = self::overlaps($lanes);

        return [
            'config' => [
                'group' => 'TRABALHO',
                'title' => 'Timeline Operacional',
                'subtitle' => 'Execução diária por responsável: agenda, tarefas sobrepostas, marcos, filtros dinâmicos, tarefas não agendadas e zoom operacional.',
            ],
            'stats' => [
                ['label' => 'Responsáveis', 'value' => count($lanes), 'hint' => 'Swimlanes ativas'],
                ['label' => 'Tarefas abertas', 'value' => collect($tasks)->where('done', false)->count(), 'hint' => 'Filtro aberto/revisão'],
                ['label' => 'Sobreposições', 'value' => count($overlaps), 'hint' => 'Conflitos no mesmo horário'],
                ['label' => 'Não agendadas', 'value' => count($unscheduled), 'hint' => 'Arraste para a agenda'],
            ],
            'lanes' => $lanes,
            'events' => $events,
            'milestones' => $milestones,
            'unscheduled' => $unscheduled,
            'overlaps' => $overlaps,
            'filters' => [
                ['label' => 'Em aberto', 'active' => true],
                ['label' => 'Em revisão', 'active' => true],
                ['label' => 'Concluídas ocultas', 'active' => true],
                ['label' => 'SLA em risco', 'active' => false],
            ],
            'zoom' => [
                ['label' => 'Dia', 'active' => true],
                ['label' => 'Semana', 'active' => false],
                ['label' => 'Mês', 'active' => false],
            ],
        ];
    }

    private static function tasks(int $limit = 48): array
    {
        if (! self::hasTable('item_controles')) {
            return [];
        }

        $select = [
            'item_controles.id', 'item_controles.titulo', 'item_controles.tipo', 'item_controles.status', 'item_controles.prioridade',
            'item_controles.data_vencimento', 'item_controles.created_at', 'item_controles.updated_at', 'item_controles.sla_limite_em',
            'item_controles.sla_concluido_em', 'item_controles.responsavel_id', 'item_controles.empresa_id',
        ];

        foreach (['estimated_minutes', 'actual_minutes', 'blocked_by_dependency', 'bloqueado_por_dependencia'] as $column) {
            if (self::hasColumn('item_controles', $column)) {
                $select[] = 'item_controles.'.$column;
            }
        }

        return DB::table('item_controles')
            ->leftJoin('responsaveis', 'responsaveis.id', '=', 'item_controles.responsavel_id')
            ->leftJoin('empresas', 'empresas.id', '=', 'item_controles.empresa_id')
            ->select(array_merge($select, ['responsaveis.nome as responsavel_nome', 'empresas.nome_fantasia', 'empresas.razao_social']))
            ->orderByRaw('CASE WHEN item_controles.data_vencimento IS NULL THEN 1 ELSE 0 END')
            ->orderBy('item_controles.data_vencimento')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => self::decorate((array) $row))
            ->all();
    }

    private static function decorate(array $task): array
    {
        $start = ! empty($task['created_at']) ? Carbon::parse($task['created_at']) : now()->startOfDay()->addHours(9);
        $due = ! empty($task['data_vencimento']) ? Carbon::parse($task['data_vencimento']) : null;
        $end = $due ?: $start->copy()->addHours(3);
        if ($end->lessThanOrEqualTo($start)) {
            $end = $start->copy()->addHours(max(1, min(8, (int) ceil(($task['estimated_minutes'] ?? 120) / 60))));
        }

        $done = in_array(Str::lower((string) ($task['status'] ?? '')), self::DONE_STATUSES, true);
        $durationHours = max(1, min(12, $start->diffInHours($end) ?: (int) ceil(($task['estimated_minutes'] ?? 120) / 60)));
        $hour = (int) $start->format('H');
        $left = max(0, min(92, (int) round((($hour - 7) / 14) * 100)));
        $width = max(8, min(100 - $left, (int) round(($durationHours / 14) * 100)));

        return [
            'id' => (int) $task['id'],
            'title' => $task['titulo'] ?? 'Sem título',
            'owner' => $task['responsavel_nome'] ?? 'Sem responsável',
            'project' => ($task['nome_fantasia'] ?? null) ?: (($task['razao_social'] ?? null) ?: 'Sem empresa'),
            'type' => $task['tipo'] ?? 'tarefa',
            'status' => $task['status'] ?? 'pendente',
            'priority' => $task['prioridade'] ?? 'media',
            'start' => $start,
            'end' => $end,
            'start_label' => $start->format('d/m H:i'),
            'end_label' => $end->format('d/m H:i'),
            'due_label' => $due?->format('d/m/Y') ?? 'Sem prazo',
            'done' => $done,
            'is_late' => ! $done && $end->isPast(),
            'is_blocked' => (bool) (($task['blocked_by_dependency'] ?? false) || ($task['bloqueado_por_dependencia'] ?? false)),
            'left' => $left,
            'width' => $width,
        ];
    }

    private static function swimlanes(array $tasks): array
    {
        return collect($tasks)
            ->reject(fn ($task) => $task['done'])
            ->groupBy('owner')
            ->map(function ($items, $owner) {
                $load = min(100, $items->count() * 22);
                return [
                    'owner' => $owner,
                    'load' => $load,
                    'state' => $load >= 80 ? 'Sobrecarregado' : ($load <= 25 ? 'Ocioso' : 'Equilibrado'),
                    'tasks' => $items->values()->all(),
                ];
            })
            ->sortByDesc('load')
            ->values()
            ->all();
    }

    private static function events(): array
    {
        $rows = [];
        if (self::hasTable('item_controle_timeline')) {
            $rows = array_merge($rows, DB::table('item_controle_timeline')
                ->leftJoin('item_controles', 'item_controles.id', '=', 'item_controle_timeline.item_controle_id')
                ->leftJoin('responsaveis', 'responsaveis.id', '=', 'item_controles.responsavel_id')
                ->select('item_controle_timeline.titulo', 'item_controle_timeline.descricao', 'item_controle_timeline.tipo', 'item_controle_timeline.created_at', 'item_controles.titulo as item_titulo', 'responsaveis.nome as responsavel_nome')
                ->orderByDesc('item_controle_timeline.created_at')
                ->limit(18)
                ->get()
                ->map(fn ($row) => self::eventRow((array) $row))
                ->all());
        }

        if (self::hasTable('activity_log')) {
            $rows = array_merge($rows, DB::table('activity_log')
                ->selectRaw("description as titulo, event as descricao, 'log' as tipo, created_at, subject_type as item_titulo, null as responsavel_nome")
                ->orderByDesc('created_at')
                ->limit(12)
                ->get()
                ->map(fn ($row) => self::eventRow((array) $row))
                ->all());
        }

        usort($rows, fn ($a, $b) => strcmp((string) $b['created_at'], (string) $a['created_at']));
        return array_slice($rows, 0, 24);
    }

    private static function eventRow(array $row): array
    {
        return [
            'title' => $row['titulo'] ?? 'Evento',
            'description' => Str::limit($row['descricao'] ?? ($row['item_titulo'] ?? '-'), 120),
            'type' => $row['tipo'] ?? 'timeline',
            'created_at' => $row['created_at'] ?? null,
            'created_label' => ! empty($row['created_at']) ? Carbon::parse($row['created_at'])->format('d/m/Y H:i') : '-',
            'owner' => $row['responsavel_nome'] ?? 'Sistema',
        ];
    }

    private static function milestones(array $tasks): array
    {
        return collect($tasks)
            ->filter(fn ($task) => in_array($task['priority'], ['alta', 'critica', 'crítica'], true) || Str::contains(Str::lower($task['type']), ['contrato', 'documento', 'projeto']))
            ->take(10)
            ->map(fn ($task) => [
                'title' => $task['title'],
                'date' => $task['due_label'],
                'project' => $task['project'],
                'owner' => $task['owner'],
            ])
            ->values()
            ->all();
    }

    private static function unscheduledTasks(): array
    {
        if (! self::hasTable('item_controles')) {
            return [];
        }

        return DB::table('item_controles')
            ->leftJoin('empresas', 'empresas.id', '=', 'item_controles.empresa_id')
            ->whereNull('item_controles.data_vencimento')
            ->select('item_controles.id', 'item_controles.titulo', 'item_controles.prioridade', 'empresas.nome_fantasia', 'empresas.razao_social')
            ->limit(10)
            ->get()
            ->map(fn ($row) => [
                'id' => (int) $row->id,
                'title' => $row->titulo,
                'priority' => $row->prioridade,
                'project' => $row->nome_fantasia ?: ($row->razao_social ?: 'Sem empresa'),
            ])
            ->all();
    }

    private static function overlaps(array $lanes): array
    {
        $overlaps = [];
        foreach ($lanes as $lane) {
            $tasks = $lane['tasks'];
            for ($i = 0; $i < count($tasks); $i++) {
                for ($j = $i + 1; $j < count($tasks); $j++) {
                    if ($tasks[$i]['start']->lessThan($tasks[$j]['end']) && $tasks[$j]['start']->lessThan($tasks[$i]['end'])) {
                        $overlaps[] = [
                            'owner' => $lane['owner'],
                            'first' => $tasks[$i]['title'],
                            'second' => $tasks[$j]['title'],
                            'period' => $tasks[$i]['start_label'].' / '.$tasks[$j]['start_label'],
                        ];
                    }
                }
            }
        }

        return array_slice($overlaps, 0, 10);
    }

    private static function hasTable(string $table): bool
    {
        try { return CachedSchema::hasTable($table); } catch (\Throwable) { return false; }
    }

    private static function hasColumn(string $table, string $column): bool
    {
        try { return CachedSchema::hasColumn($table, $column); } catch (\Throwable) { return false; }
    }
}
