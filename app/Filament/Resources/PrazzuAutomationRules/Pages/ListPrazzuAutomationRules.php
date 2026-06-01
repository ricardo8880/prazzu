<?php

namespace App\Filament\Resources\PrazzuAutomationRules\Pages;

use App\Filament\Resources\PrazzuAutomationRules\PrazzuAutomationRuleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPrazzuAutomationRules extends ListRecords
{
    protected static string $resource = PrazzuAutomationRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Nova automação útil'),
        ];
    }
}
