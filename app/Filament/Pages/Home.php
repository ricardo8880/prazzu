<?php

namespace App\Filament\Pages;

use App\Services\HomeDashboardService;
use App\Support\PrazzuUxNavigation;
use BackedEnum;
use UnitEnum;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;

class Home extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationLabel = 'Home';

    protected static string | UnitEnum | null $navigationGroup = 'Visão Geral';

    protected static ?string $title = 'Home da Contabilidade';

    protected static ?string $slug = '';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.home';

    public function getMaxContentWidth(): Width|string|null
    {
        return Width::Full;
    }

    protected function getViewData(): array
    {
        return [
            'dashboard' => app(HomeDashboardService::class, [
                'user' => Filament::auth()->user(),
            ])->data(),
            'uxNavigation' => PrazzuUxNavigation::homeJourney(),
        ];
    }
}
