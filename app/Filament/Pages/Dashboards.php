<?php

namespace App\Filament\Pages;

use App\Services\RelatoriosDashboardService;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use UnitEnum;

class Dashboards extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static string | UnitEnum | null $navigationGroup = 'Visão Geral Contábil';
    protected static ?string $navigationLabel = 'Painéis';
    protected static ?string $title = 'Painéis';
    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.dashboards';

    protected function getViewData(): array
    {
        return [
            'dashboard' => app(RelatoriosDashboardService::class)->dashboards(Filament::auth()->user()),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->check();
    }
}
