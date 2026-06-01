<?php

namespace App\Services\SystemHealth;

use App\Services\SystemHealth\Checks\ApplicationHealthCheck;
use App\Services\SystemHealth\Checks\DatabaseHealthCheck;
use App\Services\SystemHealth\Checks\EnvironmentHealthCheck;
use App\Services\SystemHealth\Checks\FinanceHealthCheck;
use App\Services\SystemHealth\Checks\LogsHealthCheck;
use App\Services\SystemHealth\Checks\PortalHealthCheck;
use App\Services\SystemHealth\Checks\SchedulerHealthCheck;
use App\Services\SystemHealth\Checks\StorageHealthCheck;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class SystemHealthService
{
    /**
     * @return array<int, class-string<HealthCheckContract>>
     */
    public function defaultChecks(): array
    {
        return [
            EnvironmentHealthCheck::class,
            DatabaseHealthCheck::class,
            PortalHealthCheck::class,
            FinanceHealthCheck::class,
            StorageHealthCheck::class,
            SchedulerHealthCheck::class,
            LogsHealthCheck::class,
            ApplicationHealthCheck::class,
        ];
    }

    public function latestReport(): array
    {
        return Cache::get('system_health.latest_report') ?: $this->emptyReport();
    }

    public function run(int $limit = 500): array
    {
        $startedAt = now();
        $limit = max(10, min(5000, $limit));
        $sections = [];
        $summary = [
            'ok' => 0,
            'warning' => 0,
            'error' => 0,
            'total' => 0,
        ];

        foreach ($this->defaultChecks() as $checkClass) {
            /** @var HealthCheckContract $check */
            $check = app($checkClass);

            try {
                $items = $check->run($limit);
            } catch (Throwable $exception) {
                Log::channel('stack')->error('Falha ao executar health check do painel administrativo.', [
                    'check' => $checkClass,
                    'exception' => $exception,
                ]);

                $items = [[
                    'status' => 'error',
                    'title' => 'Falha ao executar verificação.',
                    'detail' => $exception->getMessage(),
                    'context' => ['check' => $checkClass],
                    'action' => 'Revise o log da aplicação e corrija a verificação para manter o painel confiável.',
                ]];
            }

            $sectionSummary = $this->summarizeItems($items);
            foreach (['ok', 'warning', 'error', 'total'] as $key) {
                $summary[$key] += $sectionSummary[$key];
            }

            $sections[] = [
                'key' => $check->key(),
                'name' => $check->name(),
                'description' => $check->description(),
                'status' => $this->resolveSectionStatus($sectionSummary),
                'summary' => $sectionSummary,
                'items' => $items,
            ];
        }

        $finishedAt = now();
        $report = [
            'generated_at' => $finishedAt->toDateTimeString(),
            'generated_at_human' => $finishedAt->format('d/m/Y H:i:s'),
            'duration_ms' => max(1, (int) round($startedAt->diffInMilliseconds($finishedAt))),
            'limit' => $limit,
            'status' => $this->resolveGlobalStatus($summary),
            'summary' => $summary,
            'sections' => $sections,
        ];

        Cache::put('system_health.latest_report', $report, now()->addHours(12));
        Log::channel('stack')->info('Painel de saúde do sistema executado.', [
            'status' => $report['status'],
            'summary' => $summary,
            'duration_ms' => $report['duration_ms'],
        ]);

        return $report;
    }

    private function summarizeItems(array $items): array
    {
        $summary = ['ok' => 0, 'warning' => 0, 'error' => 0, 'total' => count($items)];

        foreach ($items as $item) {
            $status = $item['status'] ?? 'warning';
            if (! array_key_exists($status, $summary)) {
                $status = 'warning';
            }
            $summary[$status]++;
        }

        return $summary;
    }

    private function resolveSectionStatus(array $summary): string
    {
        if (($summary['error'] ?? 0) > 0) {
            return 'error';
        }

        if (($summary['warning'] ?? 0) > 0) {
            return 'warning';
        }

        return 'ok';
    }

    private function resolveGlobalStatus(array $summary): string
    {
        if (($summary['error'] ?? 0) > 0) {
            return 'critical';
        }

        if (($summary['warning'] ?? 0) > 0) {
            return 'attention';
        }

        return 'healthy';
    }

    private function emptyReport(): array
    {
        return [
            'generated_at' => null,
            'generated_at_human' => 'Ainda não executado',
            'duration_ms' => 0,
            'limit' => 500,
            'status' => 'unknown',
            'summary' => ['ok' => 0, 'warning' => 0, 'error' => 0, 'total' => 0],
            'sections' => [],
        ];
    }
}
