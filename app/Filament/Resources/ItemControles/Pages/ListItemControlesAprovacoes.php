<?php

namespace App\Filament\Resources\ItemControles\Pages;

use App\Filament\Resources\ItemControles\ItemControleResource;
use App\Filament\Resources\ItemControles\Pages\Concerns\HasItemControleSubNavigation;
use App\Filament\Resources\ItemControles\Pages\Concerns\DiagnosesItemControlePerformance;
use App\Filament\Resources\ItemControles\Tables\ItemControlesTable;
use App\Support\PrazzuAccessControl;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ListItemControlesAprovacoes extends ListRecords
{
    use HasItemControleSubNavigation;
    use DiagnosesItemControlePerformance;

    protected static string $resource = ItemControleResource::class;

    protected static ?string $title = 'Tarefas - Aprovações e Alertas';


    public static function canAccess(array $parameters = []): bool
    {
        return PrazzuAccessControl::canUseAprovacoes();
    }

    public static function shouldRegisterNavigation(array $parameters = []): bool
    {
        return static::canAccess($parameters);
    }


    protected function getTableQuery(): Builder
    {
        $this->bootItemControlePerformanceDiagnostics('aprovacoes');

        return ItemControleResource::getEloquentQueryForContext('aprovacoes');
    }

    public function table(Table $table): Table
    {
        return ItemControlesTable::make($table, 'aprovacoes');
    }
}
