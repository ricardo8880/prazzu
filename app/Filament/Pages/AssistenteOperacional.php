<?php

namespace App\Filament\Pages;

use App\Services\PrazzuEnterpriseMaturityService;
use BackedEnum;
use Filament\Pages\Page;
use UnitEnum;

class AssistenteOperacional extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static string | UnitEnum | null $navigationGroup = 'Trabalho';
    protected static ?string $navigationLabel = 'Assistente Operacional';
    protected static ?string $title = 'Assistente Operacional';
    protected static ?int $navigationSort = 11;
    protected string $view = 'filament.pages.prazzu-operational-tool-page';

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    protected function getViewData(): array
    {
        return ['data' => app(PrazzuEnterpriseMaturityService::class)->page('assistente-operacional')];
    }
}
