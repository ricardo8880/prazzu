<?php

namespace App\Filament\Pages;

use App\Services\PrazzuEnterpriseMaturityService;
use App\Support\AccountingProfileNavigation;
use BackedEnum;
use Filament\Pages\Page;
use UnitEnum;

class DashboardExecutivo extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static string | UnitEnum | null $navigationGroup = 'Visão Geral Contábil';
    protected static ?string $navigationLabel = 'Dashboard Executivo';
    protected static ?string $title = 'Dashboard Executivo';
    protected static ?int $navigationSort = 2;
    protected string $view = 'filament.pages.prazzu-operational-tool-page';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && AccountingProfileNavigation::canAccessLabel(auth()->user(), 'Dashboard Executivo');
    }

    public static function canAccess(): bool
    {
        return auth()->check() && AccountingProfileNavigation::canAccessLabel(auth()->user(), 'Dashboard Executivo');
    }

    protected function getViewData(): array
    {
        return ['data' => app(PrazzuEnterpriseMaturityService::class)->page('dashboard-executivo')];
    }
}
