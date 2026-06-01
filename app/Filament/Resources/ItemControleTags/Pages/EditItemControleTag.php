<?php

namespace App\Filament\Resources\ItemControleTags\Pages;

use App\Filament\Resources\ItemControleTags\ItemControleTagResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditItemControleTag extends EditRecord
{
    protected static string $resource = ItemControleTagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
