<?php

namespace App\Filament\Resources\ItemControles\Widgets;

use App\Services\ItemControleMetricsService;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Cache;

class ItemControlesTipoChart extends ChartWidget
{
    protected static ?int $sort = 4;

    protected static bool $isLazy = false;

    protected ?string $pollingInterval = null;

    protected ?string $heading = 'Distribuição por Tipo';

    protected int|string|array $columnSpan = 1;

    protected function getData(): array
    {
        $user = Filament::auth()->user();
        $userId = $user?->id ?? 0;

        $data = Cache::remember(
            "dashboard:item-controles:tipo-chart:user:{$userId}",
            now()->addMinutes(10),
            fn () => ItemControleMetricsService::getDistribuicaoPorTipo($user)
        );

        return [
            'datasets' => [
                [
                    'label' => 'Itens',
                    'data' => $data['data'],
                    'backgroundColor' => $data['backgroundColor'],
                    'borderColor' => $data['borderColor'],
                    'borderWidth' => 2,
                    'borderRadius' => 8,
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
                    'display' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'precision' => 0,
                    ],
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
