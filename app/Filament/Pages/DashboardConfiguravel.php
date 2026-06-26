<?php

namespace App\Filament\Pages;

use App\Services\RelatoriosDashboardService;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use UnitEnum;

class DashboardConfiguravel extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-adjustments-horizontal';
    protected static string | UnitEnum | null $navigationGroup = 'Visão Geral Contábil';
    protected static ?string $navigationLabel = 'Painel Configurável';
    protected static ?string $title = 'Painel Configurável';
    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.dashboard-configuravel';

    protected function getViewData(): array
    {
        return [
            'dashboard' => app(RelatoriosDashboardService::class)->configuravel(Filament::auth()->user()),
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->check();
    }
}
