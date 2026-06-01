<?php

namespace App\Filament\Resources\ItemControleTags\Pages;

use App\Filament\Resources\ItemControleTags\ItemControleTagResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListItemControleTags extends ListRecords
{
    protected static string $resource = ItemControleTagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
