<?php

namespace App\Filament\Resources\ItemControles\Widgets;

use App\Services\ItemControleMetricsService;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ItemControlesIndicadoresGerenciais extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    protected function getStats(): array
    {
        $query = ItemControleMetricsService::baseQuery(Filament::auth()->user());

        $slaMedio = (clone $query)
            ->where('status', 'concluido')
            ->whereNotNull('data_conclusao')
            ->selectRaw('ROUND(AVG(DATEDIFF(data_conclusao, created_at)), 1) as sla_medio')
            ->value('sla_medio');

        $atrasoMedio = (clone $query)
            ->whereNotIn('status', ['concluido', 'cancelado'])
            ->whereNotNull('data_vencimento')
            ->whereDate('data_vencimento', '<', now()->toDateString())
            ->selectRaw('ROUND(AVG(DATEDIFF(CURDATE(), DATE(data_vencimento))), 1) as atraso_medio')
            ->value('atraso_medio');

        $empresasAtivas = (clone $query)
            ->whereNotNull('empresa_id')
            ->distinct('empresa_id')
            ->count('empresa_id');

        $responsaveisAtivos = (clone $query)
            ->whereNotNull('responsavel_id')
            ->distinct('responsavel_id')
            ->count('responsavel_id');

        return [
            Stat::make('SLA médio', number_format((float) ($slaMedio ?? 0), 1, ',', '.') . ' dia(s)')
                ->description('Tempo médio para concluir')
                ->color('success'),

            Stat::make('Atraso médio', number_format((float) ($atrasoMedio ?? 0), 1, ',', '.') . ' dia(s)')
                ->description('Média de atraso dos vencidos em aberto')
                ->color('danger'),

            Stat::make('Empresas ativas', (string) $empresasAtivas)
                ->description('Empresas com itens visíveis')
                ->color('info'),

            Stat::make('Responsáveis ativos', (string) $responsaveisAtivos)
                ->description('Responsáveis com itens visíveis')
                ->color('warning'),
        ];
    }
}
