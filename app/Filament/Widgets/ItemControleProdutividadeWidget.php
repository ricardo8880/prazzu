<?php

namespace App\Filament\Widgets;

use App\Models\ItemControle;
use Filament\Facades\Filament;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ItemControleProdutividadeWidget extends ChartWidget
{
    protected ?string $heading = 'Produtividade por Responsável';

    protected function getData(): array
    {
        $user = Filament::auth()->user();

        $dados = ItemControle::query()
            ->visibleForUser($user)
            ->select([
                'responsavel_id',
                DB::raw('COUNT(item_controles.id) as total'),
            ])
            ->with([
                'responsavel:id,nome',
            ])
            ->whereNotNull('responsavel_id')
            ->groupBy('responsavel_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Itens',
                    'data' => $dados->pluck('total')->map(fn ($total) => (int) $total)->toArray(),
                ],
            ],
            'labels' => $dados
                ->map(fn ($item) => $item->responsavel?->nome ?? 'Sem responsável')
                ->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
