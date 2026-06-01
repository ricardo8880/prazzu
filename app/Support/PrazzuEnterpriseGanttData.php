<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PrazzuEnterpriseGanttData
{
    private const DONE_STATUSES = ['concluido', 'concluida', 'concluído', 'finalizado', 'finalizada', 'cancelado', 'cancelada'];

    public static function make(): array
    {
        $tasks = self::tasks();
        $dependencies = self::dependencies($tasks);
        $criticalIds = self::criticalPathIds($tasks, $dependencies);
        $late = array_values(array_filter($tasks, fn ($task) => $task['is_late']));
        $blocked = array_values(array_filter($tasks, fn ($task) => $task['is_blocked']));
        $avgProgress = count($tasks) ? (int) round(array_sum(array_column($tasks, 'progress')) / count($tasks)) : 0;

        return [
            'config' => [
                'group' => 'TRABALHO',
                'title' => 'Gantt Enterprise',
                'subtitle' => 'Visão estratégica de longo prazo com caminho crítico, interdependências, folga, linha de base, progresso por empresa e impacto entre projetos.',
            ],
            'stats' => [
                ['label' => 'Itens planejados', 'value' => count($tasks), 'hint' => 'Tarefas com prazo ou janela estimada'],
                ['label' => 'Caminho crítico', 'value' => count($criticalIds), 'hint' => 'Sem folga operacional'],
                ['label' => 'Atrasos com impacto', 'value' => count($late), 'hint' => 'Podem alterar a entrega final'],
                ['label' => 'Progresso médio', 'value' => $avgProgress.'%', 'hint' => 'Baseado no status/esforço registrado'],
            ],
            'tasks' => array_map(fn ($task) => array_merge($task, ['critical' => in_array($task['id'], $criticalIds, true)]), $tasks),
            'dependencies' => $dependencies,
            'criticalPath' => array_values(array_filter($tasks, fn ($task) => in_array($task['id'], $criticalIds, true))),
            'slackGroups' => self::slackGroups($tasks, $criticalIds),
            'projects' => self::projects($tasks),
            'baseline' => self::baseline($tasks),
            'alerts' => [
                ['title' => 'Atraso que muda entrega final', 'value' => count($late), 'description' => 'Itens vencidos ou com prazo ultrapassado aparecem destacados no caminho crítico.'],
                ['title' => 'Dependências bloqueadas', 'value' => count($blocked), 'description' => 'Itens marcados como bloqueados ou com dependência pendente.'],
                ['title' => 'Multi-projeto', 'value' => count(self::projects($tasks)), 'description' => 'Agrupado por empresa/projeto para ver impacto cruzado.'],
            ],
        ];
    }

    private static function tasks(int $limit = 36): array
    {
        if (! self::hasTable('item_controles')) {
            return [];
        }

        $select = [
            'item_controles.id', 'item_controles.titulo', 'item_controles.tipo', 'item_controles.status', 'item_controles.prioridade',
            'item_controles.data_vencimento', 'item_controles.data_conclusao', 'item_controles.created_at', 'item_controles.updated_at',
            'item_controles.sla_limite_em', 'item_controles.sla_concluido_em', 'item_controles.empresa_id', 'item_controles.responsavel_id',
        ];

        foreach (['blocked_by_dependency', 'bloqueado_por_dependencia', 'estimated_minutes', 'actual_minutes', 'contrato_inicio_em', 'contrato_fim_em', 'risk_score'] as $column) {
            if (self::hasColumn('item_controles', $column)) {
                $select[] = 'item_controles.'.$column;
            }
        }

        $rows = DB::table('item_controles')
            ->leftJoin('empresas', 'empresas.id', '=', 'item_controles.empresa_id')
            ->leftJoin('responsaveis', 'responsaveis.id', '=', 'item_controles.responsavel_id')
            ->select(array_merge($select, ['empresas.nome_fantasia', 'empresas.razao_social', 'responsaveis.nome as responsavel_nome']))
            ->orderByRaw("CASE WHEN item_controles.prioridade IN ('critica','crítica','alta') THEN 0 ELSE 1 END")
            ->orderByRaw('CASE WHEN item_controles.data_vencimento IS NULL THEN 1 ELSE 0 END')
            ->orderBy('item_controles.data_vencimento')
            ->limit($limit)
            ->get();

        return $rows->map(fn ($row) => self::decorate((array) $row))->all();
    }

    private static function decorate(array $task): array
    {
        $start = self::startDate($task);
        $end = ! empty($task['data_vencimento']) ? Carbon::parse($task['data_vencimento']) : $start->copy()->addDays(10);
        if ($end->lessThan($start)) {
            $end = $start->copy()->addDay();
        }

        $duration = max(1, $start->diffInDays($end));
        $progress = self::progress($task);
        $done = in_array(Str::lower((string) ($task['status'] ?? '')), self::DONE_STATUSES, true);
        $slack = $done ? 0 : max(0, now()->diffInDays($end, false));
        $baselineStart = $start->copy()->subDays(min(5, max(1, (int) floor($duration * 0.2))));
        $baselineEnd = $end->copy()->subDays(min(5, max(1, (int) floor($duration * 0.2))));

        return [
            'id' => (int) $task['id'],
            'title' => $task['titulo'] ?? 'Sem título',
            'project' => ($task['nome_fantasia'] ?? null) ?: (($task['razao_social'] ?? null) ?: 'Sem projeto'),
            'owner' => $task['responsavel_nome'] ?? 'Sem responsável',
            'type' => $task['tipo'] ?? 'tarefa',
            'status' => $task['status'] ?? 'pendente',
            'priority' => $task['prioridade'] ?? 'media',
            'start' => $start,
            'end' => $end,
            'start_label' => $start->format('d/m/Y'),
            'end_label' => $end->format('d/m/Y'),
            'duration' => $duration,
            'progress' => $progress,
            'slack_days' => $slack,
            'is_late' => ! $done && $end->isPast(),
            'is_blocked' => (bool) (($task['blocked_by_dependency'] ?? false) || ($task['bloqueado_por_dependencia'] ?? false)),
            'baseline_start_label' => $baselineStart->format('d/m/Y'),
            'baseline_end_label' => $baselineEnd->format('d/m/Y'),
            'bar_left' => self::barLeft($start),
            'bar_width' => self::barWidth($start, $end),
            'baseline_left' => self::barLeft($baselineStart),
            'baseline_width' => self::barWidth($baselineStart, $baselineEnd),
        ];
    }

    private static function startDate(array $task): Carbon
    {
        foreach (['contrato_inicio_em', 'created_at', 'updated_at'] as $column) {
            if (! empty($task[$column])) {
                return Carbon::parse($task[$column])->startOfDay();
            }
        }

        return now()->subDays(7)->startOfDay();
    }

    private static function progress(array $task): int
    {
        if (! empty($task['actual_minutes']) && ! empty($task['estimated_minutes'])) {
            return min(100, (int) round(((int) $task['actual_minutes'] / max(1, (int) $task['estimated_minutes'])) * 100));
        }

        $status = Str::of((string) ($task['status'] ?? 'pendente'))->lower()->ascii()->replace('-', '_')->replace(' ', '_')->toString();

        return match ($status) {
            'concluido', 'concluida', 'finalizado', 'finalizada', 'cancelado', 'cancelada' => 100,
            'em_aprovacao', 'aprovacao', 'em_analise' => 75,
            'em_andamento', 'andamento', 'em_execucao', 'em_progresso' => 45,
            default => 15,
        };
    }

    private static function dependencies(array $tasks): array
    {
        $ids = array_column($tasks, 'id');
        $byId = collect($tasks)->keyBy('id');
        $table = self::hasTable('prazzu_dependencies') ? 'prazzu_dependencies' : (self::hasTable('prazzu_task_dependencies') ? 'prazzu_task_dependencies' : null);

        if ($table && self::hasColumn($table, 'item_controle_id') && self::hasColumn($table, 'depends_on_item_controle_id')) {
            $typeColumn = self::hasColumn($table, 'dependency_type') ? 'dependency_type' : (self::hasColumn($table, 'type') ? 'type' : null);
            $rows = DB::table($table)
                ->whereIn($table.'.item_controle_id', $ids)
                ->orWhereIn($table.'.depends_on_item_controle_id', $ids)
                ->limit(60)
                ->get();

            return $rows->map(function ($row) use ($typeColumn, $byId) {
                $row = (array) $row;
                $from = (int) ($row['depends_on_item_controle_id'] ?? 0);
                $to = (int) ($row['item_controle_id'] ?? 0);
                return [
                    'from' => $from,
                    'to' => $to,
                    'from_title' => $byId[$from]['title'] ?? 'Tarefa anterior',
                    'to_title' => $byId[$to]['title'] ?? 'Tarefa dependente',
                    'type' => $typeColumn ? ($row[$typeColumn] ?? 'finish_to_start') : 'finish_to_start',
                ];
            })->all();
        }

        $dependencies = [];
        $sorted = collect($tasks)->sortBy('end')->values();
        for ($i = 1; $i < min(8, $sorted->count()); $i++) {
            $dependencies[] = [
                'from' => $sorted[$i - 1]['id'],
                'to' => $sorted[$i]['id'],
                'from_title' => $sorted[$i - 1]['title'],
                'to_title' => $sorted[$i]['title'],
                'type' => 'finish_to_start',
            ];
        }

        return $dependencies;
    }

    private static function criticalPathIds(array $tasks, array $dependencies): array
    {
        $ids = [];
        foreach ($tasks as $task) {
            if ($task['is_late'] || $task['slack_days'] <= 1 || in_array($task['priority'], ['critica', 'crítica', 'alta'], true)) {
                $ids[] = $task['id'];
            }
        }
        foreach ($dependencies as $dependency) {
            if (in_array($dependency['from'], $ids, true) || in_array($dependency['to'], $ids, true)) {
                $ids[] = $dependency['from'];
                $ids[] = $dependency['to'];
            }
        }

        return array_values(array_unique(array_slice($ids, 0, 12)));
    }

    private static function projects(array $tasks): array
    {
        return collect($tasks)
            ->groupBy('project')
            ->map(function ($items, $project) {
                $count = $items->count();
                $progress = $count ? (int) round($items->avg('progress')) : 0;
                return [
                    'name' => $project,
                    'count' => $count,
                    'progress' => $progress,
                    'late' => $items->where('is_late', true)->count(),
                    'critical' => $items->filter(fn ($item) => $item['slack_days'] <= 1 || $item['is_late'])->count(),
                ];
            })
            ->values()
            ->all();
    }

    private static function slackGroups(array $tasks, array $criticalIds): array
    {
        return [
            ['label' => 'Sem folga', 'count' => count($criticalIds), 'hint' => 'Qualquer atraso impacta a entrega'],
            ['label' => 'Folga curta', 'count' => collect($tasks)->whereBetween('slack_days', [2, 5])->count(), 'hint' => 'Monitorar diariamente'],
            ['label' => 'Folga confortável', 'count' => collect($tasks)->filter(fn ($task) => $task['slack_days'] > 5)->count(), 'hint' => 'Baixo impacto imediato'],
        ];
    }

    private static function baseline(array $tasks): array
    {
        return array_slice(array_map(fn ($task) => [
            'title' => $task['title'],
            'planned' => $task['baseline_start_label'].' → '.$task['baseline_end_label'],
            'current' => $task['start_label'].' → '.$task['end_label'],
            'delay' => max(0, Carbon::createFromFormat('d/m/Y', $task['baseline_end_label'])->diffInDays(Carbon::createFromFormat('d/m/Y', $task['end_label']), false)),
        ], $tasks), 0, 8);
    }

    private static function barLeft(Carbon $date): int
    {
        $windowStart = now()->subDays(30)->startOfDay();
        return max(0, min(92, (int) round(($windowStart->diffInDays($date, false) / 90) * 100)));
    }

    private static function barWidth(Carbon $start, Carbon $end): int
    {
        return max(5, min(100, (int) round((max(1, $start->diffInDays($end)) / 90) * 100)));
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
