<?php

namespace App\Filament\Pages;

use App\Services\PrazzuEnterpriseMaturityService;
use BackedEnum;
use Filament\Pages\Page;
use UnitEnum;

class RelatoriosExportaveis extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-arrow-down-tray';
    protected static string | UnitEnum | null $navigationGroup = 'Relatórios';
    protected static ?string $navigationLabel = 'Relatórios Exportáveis';
    protected static ?string $title = 'Relatórios Exportáveis';
    protected static ?int $navigationSort = 7;
    protected string $view = 'filament.pages.prazzu-operational-tool-page';

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    protected function getViewData(): array
    {
        return ['data' => app(PrazzuEnterpriseMaturityService::class)->page('relatorios-exportaveis')];
    }
}
