<?php

namespace App\Filament\Resources\CategoriaItemControles\Pages;

use App\Filament\Resources\CategoriaItemControles\CategoriaItemControleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCategoriaItemControles extends ListRecords
{
    protected static string $resource = CategoriaItemControleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
