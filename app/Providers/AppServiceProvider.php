<?php

namespace App\Providers;

use App\Models\ItemControle;
use App\Observers\ItemControleObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use stdClass;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        ItemControle::observe(ItemControleObserver::class);

        RateLimiter::for('portal-publico', function (Request $request) {
            $token = (string) $request->route('token', 'sem-token');
            $key = sha1($request->ip() . '|' . $token . '|' . $request->route()?->getName());

            if ($request->isMethod('post')) {
                return [
                    Limit::perMinute(12)->by('post:' . $key),
                    Limit::perHour(120)->by('post-hour:' . $request->ip()),
                ];
            }

            return [
                Limit::perMinute(60)->by('get:' . $key),
                Limit::perHour(600)->by('get-hour:' . $request->ip()),
            ];
        });

        $this->registerPerformanceDebug();

        JsonResponse::macro('safeData', function ($data) {
            return $this->setData($this->utf8ize($data));
        });

        JsonResponse::macro('utf8ize', function ($data) {
            if (is_array($data)) {
                foreach ($data as $key => $value) {
                    $data[$key] = $this->utf8ize($value);
                }

                return $data;
            }

            if (is_object($data)) {
                foreach ($data as $key => $value) {
                    $data->{$key} = $this->utf8ize($value);
                }

                return $data;
            }

            if (is_string($data)) {
                return mb_convert_encoding(
                    $data,
                    'UTF-8',
                    'UTF-8, ISO-8859-1, Windows-1252'
                );
            }

            return $data;
        });
    }

    protected function registerPerformanceDebug(): void
    {
        if ($this->app->runningInConsole()) {
            return;
        }

        if (! request()->has('debug_sql') && ! request()->has('debug_performance')) {
            return;
        }

        $debug = new stdClass();
        $debug->queryCount = 0;
        $debug->queryTotalMs = 0.0;
        $debug->slowQueries = [];
        $debug->duplicatedQueries = [];
        $debug->allQueries = [];

        $this->app->instance('performance.debug', $debug);

        DB::listen(function ($query) use ($debug): void {
            $time = (float) $query->time;
            $sql = (string) $query->sql;
            $normalizedSql = preg_replace('/\s+/', ' ', trim($sql));

            $debug->queryCount++;
            $debug->queryTotalMs += $time;
            $debug->duplicatedQueries[$normalizedSql] = ($debug->duplicatedQueries[$normalizedSql] ?? 0) + 1;

            if (request()->has('debug_sql_all')) {
                $debug->allQueries[] = [
                    'time_ms' => round($time, 2),
                    'sql' => $sql,
                    'bindings' => $query->bindings,
                ];
            }

            if ($time >= 50) {
                $debug->slowQueries[] = [
                    'time_ms' => round($time, 2),
                    'sql' => $sql,
                    'bindings' => $query->bindings,
                ];
            }
        });

        app()->terminating(function () use ($debug): void {
            $duplicated = collect($debug->duplicatedQueries)
                ->filter(fn (int $total): bool => $total > 1)
                ->sortDesc()
                ->take(10)
                ->map(fn (int $total, string $sql): array => [
                    'vezes' => $total,
                    'sql' => $sql,
                ])
                ->values()
                ->all();

            Log::warning('PERFORMANCE SQL RESUMO', [
                'url' => request()->fullUrl(),
                'queries_total' => $debug->queryCount,
                'sql_total_ms' => round($debug->queryTotalMs, 2),
                'queries_lentas_total' => count($debug->slowQueries),
                'queries_duplicadas_top' => $duplicated,
            ]);

            if (! empty($debug->slowQueries)) {
                Log::warning('PERFORMANCE SQL LENTAS', [
                    'url' => request()->fullUrl(),
                    'queries' => collect($debug->slowQueries)
                        ->sortByDesc('time_ms')
                        ->take(10)
                        ->values()
                        ->all(),
                ]);
            }

            if (request()->has('debug_sql_all')) {
                Log::warning('PERFORMANCE SQL TODAS', [
                    'url' => request()->fullUrl(),
                    'queries' => $debug->allQueries,
                ]);
            }
        });
    }
}
