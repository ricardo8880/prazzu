<?php

namespace App\Filament\Resources\ItemControles\Widgets;

use App\Services\ItemControleMetricsService;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;

class ItemControlesStatusChart extends ChartWidget
{
    protected static ?int $sort = 3;

    protected static bool $isLazy = false;

    protected ?string $pollingInterval = null;

    protected ?string $heading = 'Mapa Estratégico por Status';

    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $user = Filament::auth()->user();
        $userId = $user?->id ?? 0;

        $data = Cache::remember(
            "dashboard:item-controles:status-chart:user:{$userId}",
            now()->addMinutes(10),
            fn () => ItemControleMetricsService::getStatusDistribuicaoExclusiva($user)
        );

        return [
            'datasets' => [
                [
                    'label' => 'Itens',
                    'data' => $data['data'],
                    'backgroundColor' => $data['backgroundColor'],
                    'borderColor' => $data['borderColor'],
                    'borderWidth' => 2,
                    'hoverOffset' => 8,
                ],
            ],
            'labels' => $data['labels'],
        ];
    }

    protected function getOptions(): array
    {
        return [
            'animation' => false,
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                    'labels' => [
                        'boxWidth' => 14,
                        'padding' => 16,
                    ],
                ],
                'tooltip' => [
                    'enabled' => true,
                ],
            ],
            'cutout' => '58%',
            'elements' => [
                'arc' => [
                    'borderWidth' => 2,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
