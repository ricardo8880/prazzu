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

class ListItemControlesAnexos extends ListRecords
{
    use HasItemControleSubNavigation;
    use DiagnosesItemControlePerformance;

    protected static string $resource = ItemControleResource::class;

    protected static ?string $title = 'Tarefas - Anexos e Comentários';


    public static function canAccess(array $parameters = []): bool
    {
        return PrazzuAccessControl::canUseComentarios() || PrazzuAccessControl::canUseAnexos();
    }

    public static function shouldRegisterNavigation(array $parameters = []): bool
    {
        return static::canAccess($parameters);
    }

    protected function getTableQuery(): Builder
    {
        $this->bootItemControlePerformanceDiagnostics('anexos');

        return ItemControleResource::getEloquentQueryForContext('anexos');
    }

    public function table(Table $table): Table
    {
        return ItemControlesTable::make($table, 'anexos');
    }
}
