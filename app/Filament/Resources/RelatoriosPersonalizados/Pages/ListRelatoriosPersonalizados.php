<?php

namespace App\Filament\Resources\RelatoriosPersonalizados\Pages;

use App\Filament\Resources\RelatoriosPersonalizados\RelatorioPersonalizadoResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRelatoriosPersonalizados extends ListRecords
{
    protected static string $resource = RelatorioPersonalizadoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('visualizarRelatorios')
                ->label('Visualizar relatórios')
                ->icon('heroicon-o-presentation-chart-line')
                ->color('success')
                ->url(RelatorioPersonalizadoResource::getUrl('index')),

            CreateAction::make()
                ->label('Novo relatório'),
        ];
    }
}
