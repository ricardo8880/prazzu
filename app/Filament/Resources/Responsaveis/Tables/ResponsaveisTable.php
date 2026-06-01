<?php

namespace App\Filament\Resources\Responsaveis\Tables;

use App\Filament\Resources\Responsaveis\ResponsavelResource;
use App\Models\Responsavel;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ResponsaveisTable
{
    public static function make(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->striped()
            ->columns([
                TextColumn::make('nome')
                    ->label('Responsável')
                    ->description(fn (Responsavel $record): ?string => $record->cargo ?: null)
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->wrap(false),

                TextColumn::make('email')
                    ->label('Contato')
                    ->description(fn (Responsavel $record): ?string => $record->telefone ?: null)
                    ->searchable()
                    ->placeholder('-')
                    ->copyable()
                    ->sortable(),

                TextColumn::make('gestor.name')
                    ->label('Gestor')
                    ->placeholder('Sem gestor')
                    ->searchable()
                    ->badge()
                    ->color(fn (?string $state): string => filled($state) ? 'warning' : 'gray'),

                TextColumn::make('item_controles_count')
                    ->label('Itens')
                    ->badge()
                    ->color(fn (int|string|null $state): string => ((int) $state) > 0 ? 'info' : 'gray'),

                TextColumn::make('user.name')
                    ->label('Usuário vinculado')
                    ->placeholder('-')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                Action::make('vincularGestor')
                    ->label('Vincular gestor')
                    ->icon('heroicon-o-user-plus')
                    ->color('warning')
                    ->url(fn (Responsavel $record): string => ResponsavelResource::getUrl('edit', ['record' => $record])),

                ActionGroup::make([
                    Action::make('editar')
                        ->label('Editar')
                        ->icon('heroicon-o-pencil-square')
                        ->color('gray')
                        ->url(fn (Responsavel $record): string => ResponsavelResource::getUrl('edit', ['record' => $record])),

                    DeleteAction::make()
                        ->label('Excluir')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->before(function (Responsavel $record): void {
                            if ($record->itemControles()->limit(1)->exists()) {
                                Notification::make()
                                    ->title('Este responsável possui itens de controle vinculados e não pode ser excluído.')
                                    ->danger()
                                    ->send();

                                $action = app('filament.actions')->getMountedAction();
                                if ($action) {
                                    $action->halt();
                                }
                            }
                        }),
                ])
                    ->label('Mais')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->color('gray')
                    ->button(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make()
                    ->label('Excluir selecionados')
                    ->before(function ($records): void {
                        foreach ($records as $record) {
                            if ($record->itemControles()->limit(1)->exists()) {
                                Notification::make()
                                    ->title('Há responsável(is) com itens vinculados. Remova os itens antes de excluir.')
                                    ->danger()
                                    ->send();

                                $action = app('filament.actions')->getMountedAction();
                                if ($action) {
                                    $action->halt();
                                }

                                return;
                            }
                        }
                    }),
            ]);
    }
}
