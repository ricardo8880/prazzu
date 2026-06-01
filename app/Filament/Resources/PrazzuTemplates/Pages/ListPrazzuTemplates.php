<?php

namespace App\Filament\Resources\PrazzuTemplates\Pages;

use App\Filament\Resources\PrazzuTemplates\PrazzuTemplateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPrazzuTemplates extends ListRecords
{
    protected static string $resource = PrazzuTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Novo template'),
        ];
    }
}
