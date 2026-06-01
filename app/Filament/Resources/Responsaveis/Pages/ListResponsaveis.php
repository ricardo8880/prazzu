<?php

namespace App\Filament\Resources\Responsaveis\Pages;

use App\Filament\Resources\Responsaveis\ResponsavelResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListResponsaveis extends ListRecords
{
    protected static string $resource = ResponsavelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Novo responsável'),
        ];
    }
}