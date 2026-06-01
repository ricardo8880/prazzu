<?php

namespace App\Filament\Resources\ItemControles\Pages\Concerns;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait DiagnosesItemControlePerformance
{
    protected static array $itemControlePerformanceDiagnosticsBooted = [];

    protected function bootItemControlePerformanceDiagnostics(string $context): void
    {
        if (! $this->shouldLogItemControlePerformance()) {
            return;
        }

        $class = static::class;
        $key = $class . ':' . $context . ':' . spl_object_id($this);

        if (isset(self::$itemControlePerformanceDiagnosticsBooted[$key])) {
            return;
        }

        self::$itemControlePerformanceDiagnosticsBooted[$key] = true;

        $startedAt = microtime(true);
        $queryCount = 0;
        $queryTimeMs = 0.0;
        $slowQueries = [];

        DB::listen(function (QueryExecuted $query) use ($context, &$queryCount, &$queryTimeMs, &$slowQueries): void {
            $queryCount++;
            $queryTimeMs += (float) $query->time;

            if ($query->time < 80) {
                return;
            }

            $slowQueries[] = [
                'context' => $context,
                'time_ms' => round((float) $query->time, 2),
                'sql' => $query->sql,
                'bindings' => $query->bindings,
            ];
        });

        register_shutdown_function(function () use ($context, $startedAt, &$queryCount, &$queryTimeMs, &$slowQueries): void {
            Log::info('ItemControle performance diagnostics', [
                'context' => $context,
                'url' => request()?->fullUrl(),
                'total_request_ms' => round((microtime(true) - $startedAt) * 1000, 2),
                'query_count' => $queryCount,
                'query_time_ms' => round($queryTimeMs, 2),
                'slow_queries' => array_slice($slowQueries, 0, 20),
            ]);
        });
    }

    protected function shouldLogItemControlePerformance(): bool
    {
        return request()?->boolean('debug_performance') === true
            || filter_var(env('ITEM_CONTROLES_PERFORMANCE_LOG', false), FILTER_VALIDATE_BOOLEAN);
    }
}
