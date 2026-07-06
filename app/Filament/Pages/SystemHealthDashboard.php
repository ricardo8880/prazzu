<?php

namespace App\Filament\Pages;

use App\Services\SystemHealth\SystemHealthService;
use App\Support\PrazzuAccessControl;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Responsable;
use Symfony\Component\HttpFoundation\StreamedResponse;
use UnitEnum;

class SystemHealthDashboard extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-heart';

    protected static string | UnitEnum | null $navigationGroup = 'Governança';

    protected static ?string $navigationLabel = 'Saúde do Sistema';

    protected static ?string $title = 'Saúde do Sistema';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.system-health-dashboard';

    public array $report = [];

    public int $limit = 500;

    public bool $autoRun = false;

    public static function canAccess(): bool
    {
        return PrazzuAccessControl::can('system_health.view');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public function mount(SystemHealthService $health): void
    {
        $this->report = $health->latestReport();

        if (empty($this->report['sections'])) {
            $this->report = $health->run($this->limit);
        }
    }

    public function runHealthCheck(SystemHealthService $health): void
    {
        $this->limit = max(10, min(5000, (int) $this->limit));
        $this->report = $health->run($this->limit);
    }

    public function refreshCachedReport(SystemHealthService $health): void
    {
        $this->report = $health->latestReport();
    }

    public function exportJson(): StreamedResponse|Responsable
    {
        abort_unless(PrazzuAccessControl::can('system_health.export'), 403);
        $payload = $this->report ?: app(SystemHealthService::class)->latestReport();
        $filename = 'relatorio-saude-sistema-'.now()->format('Ymd-His').'.json';

        return response()->streamDownload(function () use ($payload): void {
            echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }, $filename, [
            'Content-Type' => 'application/json; charset=UTF-8',
        ]);
    }
}
