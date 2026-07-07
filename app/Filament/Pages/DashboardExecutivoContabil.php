<?php

namespace App\Filament\Pages;

use App\Support\DashboardExecutivoContabilData;
use App\Support\PrazzuAccessControl;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use UnitEnum;

class DashboardExecutivoContabil extends Page
{
    public array $dashboardData = [];

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-chart-pie';

    protected static string | UnitEnum | null $navigationGroup = 'Visão Geral';

    protected static ?string $navigationLabel = 'Resumo Executivo';

    protected static ?string $title = 'Resumo Executivo Contábil';

    protected static ?string $slug = 'dashboard-executivo-contabil';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.dashboard-executivo-contabil';

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public static function canAccess(): bool
    {
        return PrazzuAccessControl::canAccessPage(['relatorios.view', 'tarefas.view']);
    }

    public function getMaxContentWidth(): Width|string|null
    {
        return Width::Full;
    }

    public function mount(): void
    {
        $this->dashboardData = app(DashboardExecutivoContabilData::class)->data();
    }

    protected function getViewData(): array
    {
        return [
            'dashboard' => $this->dashboardData ?: app(DashboardExecutivoContabilData::class)->data(),
        ];
    }
}
