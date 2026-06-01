<?php

namespace App\Filament\Resources\ItemControles\Widgets;

use App\Services\ItemControleMetricsService;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ItemControlesOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $user = Filament::auth()->user();

        $data = ItemControleMetricsService::getResumo($user);

        return [
            Stat::make('Total', (string) $data['total'])
                ->description('Total de itens visíveis no painel')
                ->color('gray'),

            Stat::make('Em aberto', (string) $data['pendentes'])
                ->description('Itens ainda não finalizados')
                ->color('warning'),

            Stat::make('Vencidos', (string) $data['vencidos'])
                ->description('Urgência máxima')
                ->color('danger'),

            Stat::make('Vence hoje', (string) $data['vence_hoje'])
                ->description('Atenção imediata')
                ->color('warning'),

            Stat::make('Próx. 7 dias', (string) $data['proximos_7'])
                ->description('Janela curta de vencimento')
                ->color('info'),

            Stat::make('Concluídos', (string) $data['concluidos'])
                ->description('Itens finalizados')
                ->color('success'),
        ];
    }
}
