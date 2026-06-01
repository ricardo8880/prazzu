<?php

namespace App\Filament\Pages;

use App\Services\HomeDashboardService;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;

class Home extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-home';

    protected static ?string $navigationLabel = 'Home';

    protected static ?string $title = 'Home';

    protected static ?string $slug = '';

    protected static ?int $navigationSort = -100;

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
        ];
    }
}
