<?php

namespace App\Filament\Pages;

use App\Services\PrazzuEnterpriseMaturityService;
use BackedEnum;
use Filament\Pages\Page;
use UnitEnum;

class TemplatesEnterprise extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-squares-plus';
    protected static string | UnitEnum | null $navigationGroup = 'Cadastros e Configurações';
    protected static ?string $navigationLabel = 'Templates e Modelos';
    protected static ?string $title = 'Templates e Modelos';
    protected static ?int $navigationSort = 40;
    protected string $view = 'filament.pages.prazzu-operational-tool-page';

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    protected function getViewData(): array
    {
        return ['data' => app(PrazzuEnterpriseMaturityService::class)->page('templates-enterprise')];
    }
}
