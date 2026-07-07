<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class DebugPerformanceMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->shouldDebug($request)) {
            return $next($request);
        }

        $startedAt = microtime(true);
        $startedMemory = memory_get_usage(true);

        /** @var Response $response */
        $response = $next($request);

        $totalMs = round((microtime(true) - $startedAt) * 1000, 2);
        $peakMemoryMb = round(memory_get_peak_usage(true) / 1024 / 1024, 2);
        $memoryIncreaseMb = round((memory_get_usage(true) - $startedMemory) / 1024 / 1024, 2);
        $contentSizeKb = $this->getResponseSizeKb($response);
        $sqlDebug = app()->bound('performance.debug') ? app('performance.debug') : null;
        $sqlTotalMs = $sqlDebug ? round((float) $sqlDebug->queryTotalMs, 2) : 0.0;
        $queryCount = $sqlDebug ? (int) $sqlDebug->queryCount : 0;

        Log::warning('PERFORMANCE REQUEST RESUMO', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'route' => optional($request->route())->getName(),
            'status' => $response->getStatusCode(),
            'is_livewire' => $request->is('livewire/*') || $request->hasHeader('X-Livewire'),
            'is_ajax' => $request->ajax(),
            'tempo_total_ms' => $totalMs,
            'sql_total_ms' => $sqlTotalMs,
            'render_php_livewire_ms_aprox' => max(round($totalMs - $sqlTotalMs, 2), 0),
            'queries_total' => $queryCount,
            'response_kb' => $contentSizeKb,
            'memory_peak_mb' => $peakMemoryMb,
            'memory_increase_mb' => $memoryIncreaseMb,
        ]);

        return $response;
    }

    protected function shouldDebug(Request $request): bool
    {
        if (app()->environment('production') && ! (bool) config('prazzu_security.allow_debug_query_parameters', false)) {
            return false;
        }

        return $request->has('debug_sql')
            || $request->has('debug_performance')
            || $request->has('debug_sql_all');
    }

    protected function getResponseSizeKb(Response $response): ?float
    {
        $length = $response->headers->get('Content-Length');

        if (is_numeric($length)) {
            return round(((float) $length) / 1024, 2);
        }

        if (method_exists($response, 'getContent')) {
            $content = $response->getContent();

            if (is_string($content)) {
                return round(strlen($content) / 1024, 2);
            }
        }

        return null;
    }
}
