<?php

namespace App\Filament\Resources\Empresas\Pages;

use App\Filament\Resources\Empresas\EmpresaResource;
use Filament\Actions\CreateAction;
use Filament\Facades\Filament;
use Filament\Resources\Pages\ListRecords;

class ListEmpresas extends ListRecords
{
    protected static string $resource = EmpresaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nova empresa')
                ->visible(fn (): bool => Filament::auth()->user()?->isSuperAdmin() === true),
        ];
    }

    public function getView(): string
    {
        return 'filament.resources.empresas.pages.list-empresas';
    }
}