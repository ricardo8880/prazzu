<?php

namespace App\Filament\Resources\SugestaoMelhorias\Pages;

use App\Filament\Resources\SugestaoMelhorias\SugestaoMelhoriaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSugestaoMelhorias extends ListRecords
{
    protected static string $resource = SugestaoMelhoriaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nova sugestão'),
        ];
    }
}