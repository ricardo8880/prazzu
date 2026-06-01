<?php

namespace App\Filament\Resources\RelatoriosPersonalizados\Pages;

use App\Filament\Resources\RelatoriosPersonalizados\RelatorioPersonalizadoResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRelatorioPersonalizado extends EditRecord
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

            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return RelatorioPersonalizadoResource::getUrl('index');
    }
}
