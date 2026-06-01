<?php

namespace App\Filament\Resources\AuditoriaDetalhada\Pages;

use App\Filament\Resources\AuditoriaDetalhada\AuditoriaDetalhadaResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListAuditoriaDetalhada extends ListRecords
{
    protected static string $resource = AuditoriaDetalhadaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('visualizarAuditoria')
                ->label('Visualizar auditoria')
                ->icon('heroicon-o-presentation-chart-line')
                ->color('success')
                ->url(AuditoriaDetalhadaResource::getUrl('index')),
        ];
    }
}
