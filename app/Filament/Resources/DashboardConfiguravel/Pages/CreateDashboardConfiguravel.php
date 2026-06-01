<?php

namespace App\Filament\Resources\DashboardConfiguravel\Pages;

use App\Filament\Resources\DashboardConfiguravel\DashboardWidgetConfiguracaoResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateDashboardConfiguravel extends CreateRecord
{
    protected static string $resource = DashboardWidgetConfiguracaoResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Filament::auth()->id();

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return DashboardWidgetConfiguracaoResource::getUrl('index');
    }
}
