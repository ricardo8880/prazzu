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

class ListItemControlesTimeline extends ListRecords
{
    use HasItemControleSubNavigation;
    use DiagnosesItemControlePerformance;

    protected static string $resource = ItemControleResource::class;

    protected static ?string $title = 'Tarefas - Timeline';


    public static function canAccess(array $parameters = []): bool
    {
        return PrazzuAccessControl::canUseTimeline();
    }

    public static function shouldRegisterNavigation(array $parameters = []): bool
    {
        return static::canAccess($parameters);
    }


    protected function getTableQuery(): Builder
    {
        $this->bootItemControlePerformanceDiagnostics('timelines');

        return ItemControleResource::getEloquentQueryForContext('timelines');
    }

    public function table(Table $table): Table
    {
        return ItemControlesTable::make($table, 'timelines');
    }
}
