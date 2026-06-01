<?php

namespace App\Filament\Resources\ItemControles\Widgets;

use App\Models\ItemControle;
use App\Models\ItemControleComentario;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class ItemControleComentariosWidget extends TableWidget
{
    public ?ItemControle $record = null;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Comentários e Acompanhamento';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->defaultSort('created_at', 'desc')
            ->paginated([5, 10, 25])
            ->striped()
            ->emptyStateHeading('Nenhum comentário encontrado')
            ->emptyStateDescription('Os comentários e acompanhamentos do item aparecerão aqui.')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Usuário')
                    ->placeholder('Sistema')
                    ->sortable(),

                TextColumn::make('comentario')
                    ->label('Comentário')
                    ->wrap()
                    ->searchable(),
            ]);
    }

    protected function getTableQuery(): Builder
    {
        $itemId = $this->record?->id;

        if (! $itemId) {
            return ItemControleComentario::query()->whereRaw('1 = 0');
        }

        return ItemControleComentario::query()
            ->with('user')
            ->where('item_controle_id', $itemId);
    }
}