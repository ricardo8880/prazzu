<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PrazzuWorkPlanningData
{
    private const DONE_STATUSES = ['concluido', 'concluida', 'concluído', 'finalizado', 'finalizada', 'cancelado', 'cancelada'];

    public static function gantt(array $filters = []): array
    {
        $items = self::items($filters, 240);
        $dependencies = self::dependencies();
        $itemsById = collect($items)->keyBy('id')->all();
        $criticalIds = self::criticalPathIds($items, $dependencies);

        $starts = collect($items)->pluck('gantt_start_carbon')->filter();
        $ends = collect($items)->pluck('gantt_end_carbon')->filter();
        $timelineStart = $starts->isNotEmpty() ? $starts->sortBy(fn ($date) => $date->timestamp)->first()->copy()->startOfDay() : now()->subDays(7)->startOfDay();
        $timelineEnd = $ends->isNotEmpty() ? $ends->sortByDesc(fn ($date) => $date->timestamp)->first()->copy()->endOfDay() : now()->addDays(30)->endOfDay();
        if ($timelineStart->equalTo($timelineEnd)) {
            $timelineEnd = $timelineStart->copy()->addDay();
        }

        $totalDays = max(1, $timelineStart->diffInDays($timelineEnd));
        $rows = collect($items)->map(function (array $item) use ($criticalIds, $timelineStart, $totalDays) {
            $start = $item['gantt_start_carbon'];
            $end = $item['gantt_end_carbon'];
            $duration = max(1, $start->diffInDays($end) + 1);
            $offset = max(0, $timelineStart->diffInDays($start));
            $slack = self::slackDays($item);

            return array_merge($item, [
                'left_percent' => min(96, max(0, round(($offset / $totalDays) * 100, 2))),
                'width_percent' => min(100, max(3, round(($duration / $totalDays) * 100, 2))),
                'critical' => in_array((int) $item['id'], $criticalIds, true),
                'slack_days' => $slack,
                'baseline_left_percent' => self::baselineLeft($item, $timelineStart, $totalDays),
                'baseline_width_percent' => self::baselineWidth($item, $totalDays),
            ]);
        })->values()->all();

        $spaces = collect($rows)->groupBy(fn ($item) => $item['empresa'] ?: 'Sem empresa')->map(function ($group, $empresa) {
            $total = max(1, $group->count());
            $doneWeight = $group->sum(fn ($item) => $item['progress']);
            return [
                'name' => $empresa,
                'total' => $total,
                'progress' => (int) round($doneWeight / $total),
                'late' => $group->where('is_late', true)->count(),
                'critical' => $group->where('critical', true)->count(),
            ];
        })->values()->all();

        return [
            'rows' => $rows,
            'dependencies' => $dependencies,
            'stats' => self::ganttStats($rows),
            'spaces' => $spaces,
            'range' => [
                'start' => $timelineStart->format('d/m/Y'),
                'end' => $timelineEnd->format('d/m/Y'),
                'days' => $totalDays,
            ],
            'options' => self::options(),
        ];
    }

    public static function timeline(array $filters = []): array
    {
        $items = self::items($filters, 240);
        $visible = collect($items);

        if (($filters['hide_done'] ?? true) === true) {
            $visible = $visible->reject(fn ($item) => $item['done']);
        }

        $rangeStart = self::timelineRangeStart((string) ($filters['zoom'] ?? 'semana'));
        $rangeEnd = self::timelineRangeEnd((string) ($filters['zoom'] ?? 'semana'), $rangeStart);
        $rangeMinutes = max(60, $rangeStart->diffInMinutes($rangeEnd));

        $groupsCollection = $visible->groupBy(fn ($item) => $item['responsavel'] ?: 'Sem responsável')->map(function ($group, $responsavel) use ($rangeStart, $rangeMinutes) {
            $ordered = $group->sortBy('timeline_start_sort')->values();
            $overlapIds = self::overlapIds($ordered->all());

            $items = $ordered->map(function ($item) use ($overlapIds, $rangeStart, $rangeMinutes) {
                return array_merge($item, [
                    'overlapping' => isset($overlapIds[(int) $item['id']]),
                    'timeline_left_percent' => self::timelineLeft($item, $rangeStart, $rangeMinutes),
                    'timeline_width_percent' => self::timelineWidth($item, $rangeMinutes),
                ]);
            })->values();

            $allocatedMinutes = $items->sum(fn ($item) => self::itemMinutes($item));
            $capacityMinutes = max(1, 8 * 60);

            return [
                'responsavel' => $responsavel,
                'count' => $ordered->count(),
                'open' => $ordered->where('done', false)->count(),
                'late' => $ordered->where('is_late', true)->count(),
                'overlaps' => count($overlapIds),
                'load_percent' => min(250, (int) round(($allocatedMinutes / $capacityMinutes) * 100)),
                'items' => $items->all(),
            ];
        });

        $groups = $groupsCollection->values()->all();
        $unscheduled = $visible->filter(fn ($item) => empty($item['timeline_start_raw']) && empty($item['data_vencimento']))->values()->all();
        $milestones = $visible->filter(fn ($item) => $item['is_milestone'] || Str::contains(Str::lower($item['tipo'] ?? ''), ['marco', 'milestone']))->values()->all();

        return [
            'groups' => $groups,
            'unscheduled' => $unscheduled,
            'milestones' => $milestones,
            'stats' => [
                'items' => $visible->count(),
                'responsaveis' => $groupsCollection->count(),
                'overlaps' => $groupsCollection->sum('overlaps'),
                'milestones' => count($milestones),
                'late' => $visible->where('is_late', true)->count(),
            ],
            'range' => [
                'start' => $rangeStart->format('d/m/Y H:i'),
                'end' => $rangeEnd->format('d/m/Y H:i'),
                'zoom' => (string) ($filters['zoom'] ?? 'semana'),
            ],
            'options' => self::options(),
        ];
    }

    public static function createDependency(int $itemId, int $dependsOnId, string $type = 'finish_to_start', ?string $notes = null): void
    {
        $table = self::dependencyTable();
        if (! $table || $itemId <= 0 || $dependsOnId <= 0 || $itemId === $dependsOnId) {
            return;
        }

        $typeColumn = self::hasColumn($table, 'dependency_type') ? 'dependency_type' : 'type';
        $exists = DB::table($table)->where('item_controle_id', $itemId)->where('depends_on_item_controle_id', $dependsOnId)->exists();
        if ($exists) {
            return;
        }

        $payload = [
            'item_controle_id' => $itemId,
            'depends_on_item_controle_id' => $dependsOnId,
            $typeColumn => $type ?: 'finish_to_start',
        ];
        if (self::hasColumn($table, 'notes')) $payload['notes'] = $notes;
        if (self::hasColumn($table, 'blocked_until_resolved')) $payload['blocked_until_resolved'] = 1;
        if (self::hasColumn($table, 'created_at')) $payload['created_at'] = now();
        if (self::hasColumn($table, 'updated_at')) $payload['updated_at'] = now();

        DB::table($table)->insert($payload);
        self::syncBlockedFlags();
    }

    public static function deleteDependency(int $id): void
    {
        $table = self::dependencyTable();
        if ($table) {
            DB::table($table)->where('id', $id)->delete();
            self::syncBlockedFlags();
        }
    }

    public static function moveTask(int $id, int $days): void
    {
        $item = self::rawItem($id);
        if (! $item) return;

        $payload = self::payload($item);
        $start = self::startDate((array) $item, $payload)->addDays($days);
        $end = self::endDate((array) $item, $payload)->addDays($days);
        $payload['gantt_start'] = $start->toDateString();
        $payload['timeline_start'] = ($payload['timeline_start'] ?? null) ? Carbon::parse($payload['timeline_start'])->addDays($days)->format('Y-m-d\TH:i') : $start->copy()->startOfDay()->format('Y-m-d\TH:i');
        $payload['timeline_end'] = ($payload['timeline_end'] ?? null) ? Carbon::parse($payload['timeline_end'])->addDays($days)->format('Y-m-d\TH:i') : $end->copy()->endOfDay()->format('Y-m-d\TH:i');

        $update = [];
        if (self::hasColumn('item_controles', 'custom_payload')) $update['custom_payload'] = json_encode($payload, JSON_UNESCAPED_UNICODE);
        if (self::hasColumn('item_controles', 'data_vencimento')) $update['data_vencimento'] = $end->toDateString();
        if (self::hasColumn('item_controles', 'updated_at')) $update['updated_at'] = now();
        DB::table('item_controles')->where('id', $id)->update($update);

        self::pushDependents($id, $end);
    }

    public static function scheduleTask(int $id, string $start, ?string $end = null): void
    {
        $item = self::rawItem($id);
        if (! $item || trim($start) === '') return;

        $startAt = Carbon::parse($start);
        $endAt = $end ? Carbon::parse($end) : $startAt->copy()->addHour();
        if ($endAt->lessThanOrEqualTo($startAt)) {
            $endAt = $startAt->copy()->addHour();
        }

        $payload = self::payload($item);
        $payload['timeline_start'] = $startAt->format('Y-m-d\TH:i');
        $payload['timeline_end'] = $endAt->format('Y-m-d\TH:i');
        $payload['gantt_start'] = $startAt->toDateString();

        $update = [];
        if (self::hasColumn('item_controles', 'custom_payload')) $update['custom_payload'] = json_encode($payload, JSON_UNESCAPED_UNICODE);
        if (self::hasColumn('item_controles', 'data_vencimento')) $update['data_vencimento'] = $endAt->toDateString();
        if (self::hasColumn('item_controles', 'updated_at')) $update['updated_at'] = now();
        DB::table('item_controles')->where('id', $id)->update($update);
    }

    public static function schedulePreset(int $id, string $preset = 'today'): void
    {
        $base = match ($preset) {
            'tomorrow' => now()->addDay()->setTime(9, 0),
            'next_week' => now()->addWeek()->startOfWeek()->setTime(9, 0),
            default => now()->setTime(9, 0),
        };

        self::scheduleTask($id, $base->format('Y-m-d\TH:i'), $base->copy()->addHours(2)->format('Y-m-d\TH:i'));
    }

    public static function setTaskWindow(int $id, string $startDate, string $endDate): void
    {
        if (trim($startDate) === '' || trim($endDate) === '') return;

        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();
        if ($end->lessThan($start)) $end = $start->copy()->endOfDay();

        $item = self::rawItem($id);
        if (! $item) return;

        $payload = self::payload($item);
        $payload['gantt_start'] = $start->toDateString();
        $payload['timeline_start'] = $start->format('Y-m-d\TH:i');
        $payload['timeline_end'] = $end->format('Y-m-d\TH:i');

        $update = [];
        if (self::hasColumn('item_controles', 'custom_payload')) $update['custom_payload'] = json_encode($payload, JSON_UNESCAPED_UNICODE);
        if (self::hasColumn('item_controles', 'data_vencimento')) $update['data_vencimento'] = $end->toDateString();
        if (self::hasColumn('item_controles', 'updated_at')) $update['updated_at'] = now();
        if ($update) DB::table('item_controles')->where('id', $id)->update($update);

        self::pushDependents($id, $end);
    }

    public static function saveBaseline(?array $ids = null): int
    {
        $query = DB::table('item_controles');
        if ($ids) $query->whereIn('id', $ids);
        $count = 0;
        foreach ($query->get() as $item) {
            $payload = self::payload($item);
            $payload['baseline_start'] = self::startDate((array) $item, $payload)->toDateString();
            $payload['baseline_end'] = self::endDate((array) $item, $payload)->toDateString();
            $payload['baseline_saved_at'] = now()->toDateTimeString();
            if (self::hasColumn('item_controles', 'custom_payload')) {
                DB::table('item_controles')->where('id', $item->id)->update(['custom_payload' => json_encode($payload, JSON_UNESCAPED_UNICODE)]);
                $count++;
            }
        }
        return $count;
    }

    public static function toggleMilestone(int $id): void
    {
        $item = self::rawItem($id);
        if (! $item) return;
        $payload = self::payload($item);
        $payload['is_milestone'] = ! (bool) ($payload['is_milestone'] ?? false);
        if (self::hasColumn('item_controles', 'custom_payload')) {
            DB::table('item_controles')->where('id', $id)->update(['custom_payload' => json_encode($payload, JSON_UNESCAPED_UNICODE)]);
        }
    }

    public static function updateStatus(int $id, string $status): void
    {
        if (! self::hasColumn('item_controles', 'status')) return;
        $update = ['status' => $status];
        if (self::hasColumn('item_controles', 'data_conclusao') && in_array(self::normalizeStatus($status), ['concluido'], true)) {
            $update['data_conclusao'] = now()->toDateString();
        }
        if (self::hasColumn('item_controles', 'updated_at')) $update['updated_at'] = now();
        DB::table('item_controles')->where('id', $id)->update($update);
        self::syncBlockedFlags();
    }

    public static function syncBlockedFlags(): void
    {
        if (! self::hasTable('item_controles')) return;
        $table = self::dependencyTable();
        if (! $table) return;

        $blockedIds = DB::table($table)
            ->join('item_controles as blocker', 'blocker.id', '=', $table.'.depends_on_item_controle_id')
            ->whereNotIn('blocker.status', self::DONE_STATUSES)
            ->pluck($table.'.item_controle_id')
            ->unique()
            ->values()
            ->all();

        $flagColumn = self::hasColumn('item_controles', 'blocked_by_dependency') ? 'blocked_by_dependency' : (self::hasColumn('item_controles', 'bloqueado_por_dependencia') ? 'bloqueado_por_dependencia' : null);
        if (! $flagColumn) return;

        DB::table('item_controles')->update([$flagColumn => 0]);
        if ($blockedIds) DB::table('item_controles')->whereIn('id', $blockedIds)->update([$flagColumn => 1]);
    }

    private static function items(array $filters = [], int $limit = 120): array
    {
        if (! self::hasTable('item_controles')) return [];

        $select = self::safeSelect('item_controles', ['id', 'titulo', 'descricao', 'tipo', 'status', 'prioridade', 'data_vencimento', 'data_conclusao', 'created_at', 'updated_at', 'sla_limite_em', 'sla_concluido_em', 'sla_status', 'blocked_by_dependency', 'bloqueado_por_dependencia', 'estimated_minutes', 'actual_minutes', 'custom_payload', 'empresa_id', 'responsavel_id'], 'item_controles');
        $select = array_merge($select, ['empresas.nome_fantasia', 'empresas.razao_social', 'responsaveis.nome as responsavel_nome']);

        $query = DB::table('item_controles')
            ->leftJoin('empresas', 'empresas.id', '=', 'item_controles.empresa_id')
            ->leftJoin('responsaveis', 'responsaveis.id', '=', 'item_controles.responsavel_id')
            ->select($select);

        if (! empty($filters['search'])) {
            $search = '%'.str_replace(' ', '%', trim($filters['search'])).'%';
            $query->where(function ($q) use ($search) {
                $q->where('item_controles.titulo', 'like', $search)
                    ->orWhere('item_controles.descricao', 'like', $search)
                    ->orWhere('empresas.nome_fantasia', 'like', $search)
                    ->orWhere('empresas.razao_social', 'like', $search)
                    ->orWhere('responsaveis.nome', 'like', $search);
            });
        }

        if (! empty($filters['empresa_id'])) $query->where('item_controles.empresa_id', $filters['empresa_id']);
        if (! empty($filters['responsavel_id'])) $query->where('item_controles.responsavel_id', $filters['responsavel_id']);
        if (($filters['status'] ?? 'todos') === 'abertos') $query->whereNotIn('item_controles.status', self::DONE_STATUSES);
        if (($filters['status'] ?? 'todos') === 'concluidos') $query->whereIn('item_controles.status', self::DONE_STATUSES);
        if (($filters['status'] ?? 'todos') === 'atrasados') $query->whereNotIn('item_controles.status', self::DONE_STATUSES)->whereDate('item_controles.data_vencimento', '<', now()->toDateString());

        return $query
            ->orderByRaw('CASE WHEN item_controles.data_vencimento IS NULL THEN 1 ELSE 0 END')
            ->orderBy('item_controles.data_vencimento')
            ->orderByDesc('item_controles.updated_at')
            ->limit($limit)
            ->get()
            ->map(fn ($item) => self::decorate((array) $item))
            ->all();
    }

    private static function decorate(array $item): array
    {
        $payload = self::payload($item);
        $start = self::startDate($item, $payload);
        $end = self::endDate($item, $payload);
        $status = self::normalizeStatus($item['status'] ?? 'pendente');
        $done = in_array($item['status'] ?? '', self::DONE_STATUSES, true) || $status === 'concluido';
        $timelineStart = $payload['timeline_start'] ?? null;
        $timelineEnd = $payload['timeline_end'] ?? null;

        $timelineStartCarbon = $timelineStart ? Carbon::parse($timelineStart) : $start->copy()->startOfDay();
        $timelineEndCarbon = $timelineEnd ? Carbon::parse($timelineEnd) : $end->copy()->endOfDay();

        return array_merge($item, [
            'empresa' => ($item['nome_fantasia'] ?? null) ?: (($item['razao_social'] ?? null) ?: 'Sem empresa'),
            'responsavel' => $item['responsavel_nome'] ?? 'Sem responsável',
            'status_normalized' => $status,
            'done' => $done,
            'is_late' => ! $done && ! empty($item['data_vencimento']) && Carbon::parse($item['data_vencimento'])->isPast(),
            'is_blocked' => (bool) (($item['blocked_by_dependency'] ?? false) || ($item['bloqueado_por_dependencia'] ?? false)),
            'progress' => self::progress($item, $status),
            'gantt_start_carbon' => $start,
            'gantt_end_carbon' => $end,
            'gantt_start' => $start->format('d/m/Y'),
            'gantt_end' => $end->format('d/m/Y'),
            'timeline_start_raw' => $timelineStart,
            'timeline_end_raw' => $timelineEnd,
            'timeline_start' => $timelineStartCarbon->format('d/m/Y H:i'),
            'timeline_end' => $timelineEndCarbon->format('d/m/Y H:i'),
            'timeline_start_sort' => $timelineStartCarbon->timestamp,
            'timeline_start_ts' => $timelineStartCarbon->timestamp,
            'timeline_end_ts' => $timelineEndCarbon->timestamp,
            'is_milestone' => (bool) ($payload['is_milestone'] ?? false),
            'baseline_start' => $payload['baseline_start'] ?? null,
            'baseline_end' => $payload['baseline_end'] ?? null,
            'baseline_saved_at' => $payload['baseline_saved_at'] ?? null,
        ]);
    }

    private static function dependencies(): array
    {
        $table = self::dependencyTable();
        if (! $table) return [];
        $typeColumn = self::hasColumn($table, 'dependency_type') ? 'dependency_type' : 'type';

        return DB::table($table)
            ->leftJoin('item_controles as atual', 'atual.id', '=', $table.'.item_controle_id')
            ->leftJoin('item_controles as depende', 'depende.id', '=', $table.'.depends_on_item_controle_id')
            ->select($table.'.id', $table.'.item_controle_id', $table.'.depends_on_item_controle_id', $table.'.notes', $table.'.created_at', $table.'.'.$typeColumn.' as type', 'atual.titulo as atual', 'atual.status as atual_status', 'depende.titulo as depende', 'depende.status as depende_status')
            ->orderByDesc($table.'.created_at')
            ->limit(200)
            ->get()
            ->map(fn ($row) => (array) $row)
            ->all();
    }

    private static function criticalPathIds(array $items, array $dependencies): array
    {
        $itemsById = collect($items)->keyBy('id');
        $dependents = [];
        foreach ($dependencies as $dep) {
            $dependents[(int) $dep['depends_on_item_controle_id']][] = (int) $dep['item_controle_id'];
        }

        $memo = [];
        $score = function (int $id) use (&$score, &$memo, $itemsById, $dependents): int {
            if (isset($memo[$id])) return $memo[$id];
            $item = $itemsById->get($id);
            if (! $item) return 0;
            $duration = max(1, $item['gantt_start_carbon']->diffInDays($item['gantt_end_carbon']) + 1);
            $childMax = 0;
            foreach ($dependents[$id] ?? [] as $childId) {
                $childMax = max($childMax, $score($childId));
            }
            return $memo[$id] = $duration + $childMax;
        };

        $maxScore = 0;
        foreach ($items as $item) $maxScore = max($maxScore, $score((int) $item['id']));
        if ($maxScore <= 0) return [];

        return collect($items)
            ->filter(fn ($item) => $score((int) $item['id']) >= max(1, $maxScore - 1) || ((int) ($item['slack_days'] ?? 0) === 0 && ! $item['done']))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    private static function pushDependents(int $parentId, Carbon $parentEnd): void
    {
        $table = self::dependencyTable();
        if (! $table) return;
        $deps = DB::table($table)->where('depends_on_item_controle_id', $parentId)->pluck('item_controle_id')->all();
        foreach ($deps as $childId) {
            $child = self::rawItem((int) $childId);
            if (! $child) continue;
            $payload = self::payload($child);
            $childStart = self::startDate((array) $child, $payload);
            if ($childStart->lessThanOrEqualTo($parentEnd)) {
                $diff = $childStart->diffInDays($parentEnd->copy()->addDay(), false);
                self::moveTask((int) $childId, max(1, $diff));
            }
        }
    }

    private static function options(): array
    {
        return [
            'items' => self::hasTable('item_controles') ? DB::table('item_controles')->select('id', 'titulo')->orderBy('titulo')->limit(500)->get()->map(fn ($i) => ['id' => $i->id, 'titulo' => $i->titulo])->all() : [],
            'empresas' => self::hasTable('empresas') ? DB::table('empresas')->select('id', 'nome_fantasia', 'razao_social')->orderBy('nome_fantasia')->limit(200)->get()->map(fn ($e) => ['id' => $e->id, 'nome' => $e->nome_fantasia ?: $e->razao_social])->all() : [],
            'responsaveis' => self::hasTable('responsaveis') ? DB::table('responsaveis')->select('id', 'nome')->orderBy('nome')->limit(200)->get()->map(fn ($r) => ['id' => $r->id, 'nome' => $r->nome])->all() : [],
        ];
    }

    private static function ganttStats(array $rows): array
    {
        $count = max(1, count($rows));
        return [
            'items' => count($rows),
            'critical' => collect($rows)->where('critical', true)->count(),
            'blocked' => collect($rows)->where('is_blocked', true)->count(),
            'late' => collect($rows)->where('is_late', true)->count(),
            'progress' => (int) round(collect($rows)->sum('progress') / $count),
        ];
    }

    private static function rawItem(int $id): ?object
    {
        return self::hasTable('item_controles') ? DB::table('item_controles')->where('id', $id)->first() : null;
    }

    private static function payload(array|object $item): array
    {
        $raw = is_array($item) ? ($item['custom_payload'] ?? null) : ($item->custom_payload ?? null);
        if (! $raw) return [];
        $decoded = json_decode((string) $raw, true);
        return is_array($decoded) ? $decoded : [];
    }

    private static function startDate(array $item, array $payload): Carbon
    {
        return ! empty($payload['gantt_start']) ? Carbon::parse($payload['gantt_start']) : (! empty($payload['timeline_start']) ? Carbon::parse($payload['timeline_start']) : (! empty($item['created_at']) ? Carbon::parse($item['created_at']) : now()->startOfDay()));
    }

    private static function endDate(array $item, array $payload): Carbon
    {
        return ! empty($item['data_vencimento']) ? Carbon::parse($item['data_vencimento']) : (! empty($payload['timeline_end']) ? Carbon::parse($payload['timeline_end']) : self::startDate($item, $payload)->copy()->addDays(7));
    }

    private static function progress(array $item, string $status): int
    {
        if (! empty($item['actual_minutes']) && ! empty($item['estimated_minutes'])) {
            return min(100, (int) round((((int) $item['actual_minutes']) / max(1, (int) $item['estimated_minutes'])) * 100));
        }
        return match ($status) {
            'concluido' => 100,
            'em_aprovacao' => 75,
            'em_andamento' => 45,
            default => 15,
        };
    }

    private static function slackDays(array $item): int
    {
        if ($item['done'] ?? false) return 0;
        $end = $item['gantt_end_carbon'];
        if ($item['is_late'] ?? false) return 0;
        return max(0, now()->startOfDay()->diffInDays($end, false));
    }

    private static function baselineLeft(array $item, Carbon $rangeStart, int $totalDays): ?float
    {
        if (empty($item['baseline_start'])) return null;
        $offset = max(0, $rangeStart->diffInDays(Carbon::parse($item['baseline_start'])));
        return min(96, max(0, round(($offset / max(1, $totalDays)) * 100, 2)));
    }

    private static function baselineWidth(array $item, int $totalDays): ?float
    {
        if (empty($item['baseline_start']) || empty($item['baseline_end'])) return null;
        $duration = max(1, Carbon::parse($item['baseline_start'])->diffInDays(Carbon::parse($item['baseline_end'])) + 1);
        return min(100, max(3, round(($duration / max(1, $totalDays)) * 100, 2)));
    }

    private static function overlapIds(array $items): array
    {
        $overlapIds = [];
        $ordered = collect($items)->sortBy('timeline_start_ts')->values()->all();
        $active = [];

        foreach ($ordered as $item) {
            $start = (int) ($item['timeline_start_ts'] ?? 0);
            $end = (int) ($item['timeline_end_ts'] ?? $start);

            $active = array_values(array_filter($active, fn ($activeItem): bool => (int) ($activeItem['timeline_end_ts'] ?? 0) > $start));

            if ($active !== []) {
                $overlapIds[(int) $item['id']] = true;

                foreach ($active as $activeItem) {
                    $overlapIds[(int) $activeItem['id']] = true;
                }
            }

            $active[] = array_merge($item, ['timeline_end_ts' => max($end, $start + 1800)]);
        }

        return $overlapIds;
    }

    private static function timelineRangeStart(string $zoom): Carbon
    {
        return match ($zoom) {
            'dia' => now()->startOfDay(),
            'mes' => now()->startOfMonth(),
            default => now()->startOfWeek(),
        };
    }

    private static function timelineRangeEnd(string $zoom, Carbon $start): Carbon
    {
        return match ($zoom) {
            'dia' => $start->copy()->endOfDay(),
            'mes' => $start->copy()->endOfMonth(),
            default => $start->copy()->endOfWeek(),
        };
    }

    private static function itemMinutes(array $item): int
    {
        $start = (int) ($item['timeline_start_ts'] ?? 0);
        $end = (int) ($item['timeline_end_ts'] ?? $start);

        return max(30, (int) ceil(max(0, $end - $start) / 60));
    }

    private static function timelineLeft(array $item, Carbon $rangeStart, int $rangeMinutes): float
    {
        $offset = ((int) ($item['timeline_start_ts'] ?? $rangeStart->timestamp) - $rangeStart->timestamp) / 60;

        return min(96, max(0, round(($offset / max(1, $rangeMinutes)) * 100, 2)));
    }

    private static function timelineWidth(array $item, int $rangeMinutes): float
    {
        return min(100, max(2, round((self::itemMinutes($item) / max(1, $rangeMinutes)) * 100, 2)));
    }

    private static function dependencyTable(): ?string
    {
        if (self::hasTable('prazzu_dependencies')) return 'prazzu_dependencies';
        if (self::hasTable('prazzu_task_dependencies')) return 'prazzu_task_dependencies';
        return null;
    }

    private static function normalizeStatus(string $status): string
    {
        $status = Str::of($status)->lower()->ascii()->replace('-', '_')->replace(' ', '_')->toString();
        return match ($status) {
            'andamento', 'em_execucao', 'em_progresso' => 'em_andamento',
            'aprovacao', 'em_analise' => 'em_aprovacao',
            'concluida', 'concluido', 'finalizado', 'finalizada', 'cancelado', 'cancelada' => 'concluido',
            default => $status ?: 'pendente',
        };
    }

    private static function safeSelect(string $table, array $columns, string $prefix): array
    {
        return collect($columns)->filter(fn ($column) => self::hasColumn($table, $column))->map(fn ($column) => $prefix.'.'.$column)->values()->all();
    }

    private static function hasTable(string $table): bool { return CachedSchema::hasTable($table); }
    private static function hasColumn(string $table, string $column): bool { return CachedSchema::hasTable($table) && CachedSchema::hasColumn($table, $column); }
}
