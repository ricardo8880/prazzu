<?php

namespace App\Filament\Pages;

use App\Services\PrazzuEnterpriseMaturityService;
use BackedEnum;
use Filament\Pages\Page;
use UnitEnum;

class NavegacaoContextual extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-building-office-2';
    protected static string | UnitEnum | null $navigationGroup = 'Clientes';
    protected static ?string $navigationLabel = 'Navegação Contextual';
    protected static ?string $title = 'Navegação Contextual';
    protected static ?int $navigationSort = 12;
    protected string $view = 'filament.pages.prazzu-operational-tool-page';

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    protected function getViewData(): array
    {
        return ['data' => app(PrazzuEnterpriseMaturityService::class)->page('navegacao-contextual')];
    }
}
