<?php

namespace App\Filament\Resources\ItemControles\Widgets;

use App\Models\ItemControle;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class EmpresasRankingWidget extends TableWidget
{
    protected static ?int $sort = 5;

    protected static bool $isLazy = false;

    protected ?string $pollingInterval = null;

    protected static ?string $heading = 'Empresas com Maior Carga';

    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->defaultSort('em_aberto', 'desc')
            ->paginated([5, 10])
            ->striped()
            ->columns([
                TextColumn::make('empresa.razao_social')
                    ->label('Empresa')
                    ->placeholder('Sem empresa')
                    ->wrap(),

                TextColumn::make('total_itens')
                    ->label('Total')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('em_aberto')
                    ->label('Em aberto')
                    ->badge()
                    ->color('warning'),

                TextColumn::make('vencidos')
                    ->label('Vencidos')
                    ->badge()
                    ->color('danger'),

                TextColumn::make('concluidos')
                    ->label('Concluídos')
                    ->badge()
                    ->color('success'),
            ])
            ->emptyStateHeading('Nenhum dado encontrado')
            ->emptyStateDescription('O ranking aparecerá quando houver itens cadastrados.');
    }

    protected function getTableQuery(): Builder
    {
        $user = Filament::auth()->user();

        return ItemControle::query()
            ->visibleForUser($user)
            ->with(['empresa:id,razao_social'])
            ->select([
                DB::raw('MIN(id) as id'),
                'empresa_id',
                DB::raw('COUNT(*) as total_itens'),
                DB::raw("SUM(CASE WHEN status NOT IN ('concluido','cancelado') THEN 1 ELSE 0 END) as em_aberto"),
                DB::raw("SUM(CASE WHEN status = 'concluido' THEN 1 ELSE 0 END) as concluidos"),
                DB::raw("
                    SUM(
                        CASE
                            WHEN status NOT IN ('concluido','cancelado')
                                 AND data_vencimento IS NOT NULL
                                 AND DATE(data_vencimento) < CURDATE()
                            THEN 1
                            ELSE 0
                        END
                    ) as vencidos
                "),
            ])
            ->groupBy('empresa_id');
    }
}
