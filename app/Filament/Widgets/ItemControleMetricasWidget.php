<?php

namespace App\Filament\Widgets;

use App\Models\ItemControle;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class ItemControleMetricasWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $user = Filament::auth()->user();

        $cacheKey = 'dashboard_item_controles_metricas_' . ($user?->id ?? 'guest');

        $dados = Cache::remember(
            $cacheKey,
            now()->addMinutes(5),
            function () use ($user) {
                $query = ItemControle::query()
                    ->visibleForUser($user);

                return [
                    'total' => (clone $query)->count(),

                    'pendentes' => (clone $query)
                        ->where('status', 'pendente')
                        ->count(),

                    'concluidos' => (clone $query)
                        ->where('status', 'concluido')
                        ->count(),

                    'vencidos' => (clone $query)
                        ->whereDate('data_vencimento', '<', now())
                        ->whereNotIn('status', ['concluido', 'cancelado'])
                        ->count(),

                    'aprovacoes_pendentes' => (clone $query)
                        ->where('status', 'em_aprovacao')
                        ->count(),

                    'sla_atrasado' => (clone $query)
                        ->where('sla_status', 'atrasado')
                        ->count(),

                    'sla_no_prazo' => (clone $query)
                        ->where('sla_status', 'concluido_no_prazo')
                        ->count(),
                ];
            }
        );

        return [
            Stat::make('Total de Itens', $dados['total'])
                ->description('Todos os itens cadastrados'),

            Stat::make('Pendentes', $dados['pendentes'])
                ->description('Aguardando andamento')
                ->color('warning'),

            Stat::make('Concluídos', $dados['concluidos'])
                ->description('Finalizados')
                ->color('success'),

            Stat::make('Vencidos', $dados['vencidos'])
                ->description('Prazo vencido')
                ->color('danger'),

            Stat::make('Aprovações Pendentes', $dados['aprovacoes_pendentes'])
                ->description('Em fluxo de aprovação')
                ->color('warning'),

            Stat::make('SLA Atrasado', $dados['sla_atrasado'])
                ->description('Itens atrasados no SLA')
                ->color('danger'),

            Stat::make('SLA no Prazo', $dados['sla_no_prazo'])
                ->description('Concluídos corretamente')
                ->color('success'),
        ];
    }
}
