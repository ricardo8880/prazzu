<?php

namespace App\Filament\Resources\DashboardConfiguravel\Pages;

use App\Filament\Resources\DashboardConfiguravel\DashboardWidgetConfiguracaoResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDashboardConfiguravel extends EditRecord
{
    protected static string $resource = DashboardWidgetConfiguracaoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return DashboardWidgetConfiguracaoResource::getUrl('index');
    }
}
