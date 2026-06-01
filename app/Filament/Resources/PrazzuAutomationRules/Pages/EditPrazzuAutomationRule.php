<?php

namespace App\Filament\Resources\PrazzuAutomationRules\Pages;

use App\Filament\Resources\PrazzuAutomationRules\PrazzuAutomationRuleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPrazzuAutomationRule extends EditRecord
{
    protected static string $resource = PrazzuAutomationRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
