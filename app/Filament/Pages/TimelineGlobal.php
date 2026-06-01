<?php

namespace App\Filament\Pages;

use App\Support\PrazzuAuditTimelineGlobalData;
use BackedEnum;
use Filament\Pages\Page;
use UnitEnum;

class TimelineGlobal extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-clock';
    protected static string | UnitEnum | null $navigationGroup = 'Governança';
    protected static ?string $navigationLabel = 'Timeline Global';
    protected static ?string $title = 'Timeline Global';
    protected static ?int $navigationSort = 9;
    protected string $view = 'filament.pages.timeline-global';

    protected function getViewData(): array
    {
        return ['data' => PrazzuAuditTimelineGlobalData::make(request()->only(['period', 'type', 'risk', 'search']))];
    }
}
