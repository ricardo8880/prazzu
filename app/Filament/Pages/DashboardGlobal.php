<?php

namespace App\Filament\Pages;

use App\Support\PrazzuEnterprisePageData;
use BackedEnum;
use Filament\Pages\Page;
use UnitEnum;

class DashboardGlobal extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected string $view = 'filament.pages.dashboard-global';

    protected static ?string $title = 'Dashboards';

    protected static ?string $navigationLabel = 'Painéis Globais';

    protected static string | UnitEnum | null $navigationGroup = 'Visão Geral Contábil';

    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        return auth()->check();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    protected function getViewData(): array
    {
        return [
            'enterprise' => PrazzuEnterprisePageData::for('dashboards'),
        ];
    }
}
