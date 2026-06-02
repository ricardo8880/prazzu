<?php

namespace App\Filament\Pages;

use BackedEnum;
use UnitEnum;

class CentroOperacionalGestao extends CentroOperacional
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-squares-2x2';
    protected static string | UnitEnum | null $navigationGroup = 'Trabalho';
    protected static ?string $navigationLabel = 'Operação Interna';
    protected static ?string $title = 'Operação Interna';
    protected static ?int $navigationSort = 2;
    protected string $view = 'filament.pages.centro-operacional-gestao';

    public string $operationalTab = 'workload';

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
