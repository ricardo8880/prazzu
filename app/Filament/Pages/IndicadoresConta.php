<?php

namespace App\Filament\Pages;

use App\Services\AccountIndicatorsService;
use App\Support\PrazzuAccessControl;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use UnitEnum;

class IndicadoresConta extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static string | UnitEnum | null $navigationGroup = 'Relatórios';

    protected static ?string $navigationLabel = 'Indicadores da Conta';

    protected static ?string $title = 'Indicadores da Conta';

    protected static ?int $navigationSort = 9;

    protected string $view = 'filament.pages.indicadores-conta';

    public static function canAccess(): bool
    {
        $user = Filament::auth()->user();

        if (! $user || ! PrazzuAccessControl::canUseWorkArea($user)) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        $perfil = $user->perfil_contabil ?: $user::perfilContabilPadraoPorRole($user->role);

        return in_array($perfil, ['socio', 'gestor'], true) || $user->isAdmin() || $user->isGestor();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    protected function getViewData(): array
    {
        return app(AccountIndicatorsService::class)->dashboard(Filament::auth()->user());
    }
}
