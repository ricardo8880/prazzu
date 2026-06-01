<?php

namespace App\Filament\Resources\Empresas\Tables;

use App\Services\PlanoService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EmpresasTable
{
    public static function make(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->striped()
            ->columns([
                TextColumn::make('razao_social')
                    ->label('Empresa')
                    ->description(fn ($record): ?string => $record?->nome_fantasia ?: null)
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->wrap(false),

                TextColumn::make('cnpj')
                    ->label('CNPJ')
                    ->searchable()
                    ->placeholder('-')
                    ->copyable()
                    ->toggleable(),

                TextColumn::make('email')
                    ->label('Contato')
                    ->searchable()
                    ->placeholder('-')
                    ->copyable()
                    ->toggleable(),

                TextColumn::make('plano')
                    ->label('Plano')
                    ->badge()
                    ->sortable()
                    ->formatStateUsing(fn (?string $state): string => PlanoService::nome($state))
                    ->color(fn (?string $state): string => match (PlanoService::normalizarPlano($state)) {
                        PlanoService::ENTERPRISE, PlanoService::BUSINESS_PLUS => 'success',
                        PlanoService::BUSINESS => 'warning',
                        PlanoService::PROFISSIONAL => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('limite_usuarios')
                    ->label('Limites')
                    ->state(fn ($record): string => sprintf(
                        '%s usuários · %s itens · %s IA/mês',
                        $record?->limite_usuarios ?? '-',
                        $record?->limite_itens ?? '-',
                        $record?->limite_interacoes_ia ?? '-'
                    ))
                    ->badge()
                    ->color('gray'),

                IconColumn::make('ativo')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('assinaturaAtual.status')
                    ->label('Assinatura')
                    ->badge()
                    ->placeholder('Sem assinatura')
                    ->color(fn (?string $state): string => match ($state) {
                        'ACTIVE', 'RECEIVED', 'CONFIRMED' => 'success',
                        'PENDING' => 'warning',
                        'OVERDUE', 'INACTIVE', 'CANCELLED' => 'danger',
                        default => 'gray',
                    })
                    ->toggleable(),

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
            ->actions([
                EditAction::make()
                    ->label('Editar')
                    ->icon('heroicon-o-pencil-square')
                    ->color('gray'),

                Action::make('abrir_cobranca')
                    ->label('Abrir cobrança')
                    ->icon('heroicon-o-credit-card')
                    ->color('warning')
                    ->url(fn ($record): ?string => $record->assinaturaAtual?->pagamentos()->latest('id')->first()?->invoice_url)
                    ->openUrlInNewTab()
                    ->visible(fn ($record): bool => filled($record->assinaturaAtual?->pagamentos()->latest('id')->first()?->invoice_url)),

                ActionGroup::make([
                    DeleteAction::make()
                        ->label('Excluir')
                        ->icon('heroicon-o-trash')
                        ->color('danger'),
                ])
                    ->label('Mais')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->color('gray')
                    ->button(),
            ]);
    }
}
