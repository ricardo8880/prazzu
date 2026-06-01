<?php

namespace App\Filament\Resources\CategoriaItemControles\Pages;

use App\Filament\Resources\CategoriaItemControles\CategoriaItemControleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCategoriaItemControle extends EditRecord
{
    protected static string $resource = CategoriaItemControleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
