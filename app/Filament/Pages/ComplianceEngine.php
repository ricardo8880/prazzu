<?php

namespace App\Filament\Pages;

use App\Support\ComplianceModuleData;
use BackedEnum;
use Filament\Pages\Page;
use UnitEnum;

class ComplianceEngine extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-cpu-chip';
    protected static string | UnitEnum | null $navigationGroup = 'Governança';
    protected static ?string $navigationLabel = 'Compliance Engine';
    protected static ?string $title = 'Compliance Engine';
    protected static ?int $navigationSort = 4;
    protected string $view = 'filament.pages.compliance-engine';

    protected function getViewData(): array
    {
        return ['data' => ComplianceModuleData::engine()];
    }

    public static function canAccess(): bool
    {
        return auth()->check();
    }
}
