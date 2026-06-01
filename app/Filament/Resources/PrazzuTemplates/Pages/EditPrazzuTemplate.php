<?php

namespace App\Filament\Resources\PrazzuTemplates\Pages;

use App\Filament\Resources\PrazzuTemplates\PrazzuTemplateResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPrazzuTemplate extends EditRecord
{
    protected static string $resource = PrazzuTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->label('Excluir'),
        ];
    }
}
