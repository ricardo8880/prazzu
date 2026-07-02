<?php

namespace App\Filament\Pages;

use BackedEnum;
use UnitEnum;

class CentroOperacionalGestao extends CentroOperacional
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-squares-2x2';
    protected static string | UnitEnum | null $navigationGroup = 'Central Operacional';
    protected static ?string $navigationLabel = 'Gestão da Operação';
    protected static ?string $title = 'Gestão da Operação';
    protected static ?int $navigationSort = 2;
    protected string $view = 'filament.pages.centro-operacional-gestao';

    public string $operationalTab = 'workload';


    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public function mount(): void
    {
        $tab = request()->query('aba');

        if (is_string($tab) && $this->isOperationalTabValid($tab)) {
            $this->operationalTab = $tab;
        }
    }

    public function getHeading(): string
    {
        return '';
    }

    protected function isOperationalTabValid(string $tab): bool
    {
        return in_array($tab, ['workload', 'aprovacoes', 'financeiro'], true);
    }
}
