<?php

namespace App\Filament\Pages;

use App\Services\PrazzuEnterpriseMaturityService;
use BackedEnum;
use Filament\Pages\Page;
use UnitEnum;

class DashboardExecutivo extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static string | UnitEnum | null $navigationGroup = 'Relatórios';
    protected static ?string $navigationLabel = 'Dashboard Executivo';
    protected static ?string $title = 'Dashboard Executivo';
    protected static ?int $navigationSort = 8;
    protected string $view = 'filament.pages.prazzu-operational-tool-page';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    protected function getViewData(): array
    {
        return ['data' => app(PrazzuEnterpriseMaturityService::class)->page('dashboard-executivo')];
    }
}
