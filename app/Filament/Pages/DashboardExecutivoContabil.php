<?php

namespace App\Filament\Pages;

use App\Support\AccountingProfileNavigation;
use App\Support\DashboardExecutivoContabilData;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use UnitEnum;

class DashboardExecutivoContabil extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-chart-pie';

    protected static string | UnitEnum | null $navigationGroup = 'Relatórios';

    protected static ?string $navigationLabel = 'Dashboard Executivo Contábil';

    protected static ?string $title = 'Dashboard Executivo Contábil';

    protected static ?string $slug = 'dashboard-executivo-contabil';

    protected static ?int $navigationSort = 0;

    protected string $view = 'filament.pages.dashboard-executivo-contabil';

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public static function canAccess(): bool
    {
        $user = Filament::auth()->user();

        if (! $user) {
            return false;
        }

        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return true;
        }

        return AccountingProfileNavigation::canAccessLabel($user, 'Dashboard Executivo Contábil');
    }

    public function getMaxContentWidth(): Width|string|null
    {
        return Width::Full;
    }

    protected function getViewData(): array
    {
        return [
            'dashboard' => app(DashboardExecutivoContabilData::class)->data(),
        ];
    }
}
