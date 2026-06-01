<?php

namespace App\Filament\Resources\DashboardConfiguravel\Pages;

use App\Filament\Resources\DashboardConfiguravel\DashboardWidgetConfiguracaoResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDashboardConfiguravel extends ListRecords
{
    protected static string $resource = DashboardWidgetConfiguracaoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('visualizarDashboard')
                ->label('Visualizar dashboard')
                ->icon('heroicon-o-presentation-chart-bar')
                ->color('success')
                ->url(DashboardWidgetConfiguracaoResource::getUrl('index')),

            CreateAction::make()
                ->label('Novo widget'),
        ];
    }
}
