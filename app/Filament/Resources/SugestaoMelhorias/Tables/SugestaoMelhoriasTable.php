<?php

namespace App\Filament\Resources\SugestaoMelhorias\Tables;

use App\Filament\Resources\SugestaoMelhorias\SugestaoMelhoriaResource;
use App\Models\SugestaoMelhoria;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Facades\Filament;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class SugestaoMelhoriasTable
{
    public static function make(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->columns([
                TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->width('150px')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('titulo')
                    ->label('Sugestão')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::SemiBold)
                    ->limit(45)
                    ->tooltip(fn (SugestaoMelhoria $record): string => $record->titulo)
                    ->wrap(false)
                    ->description(fn (SugestaoMelhoria $record): string => $record->created_at?->format('d/m/Y H:i') ?? '-'),

                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => match ($state) {
                        'bug' => 'Bug',
                        'melhoria' => 'Melhoria',
                        'funcionalidade' => 'Nova funcionalidade',
                        'duvida' => 'Dúvida',
                        'outro' => 'Outro',
                        default => (string) $state,
                    })
                    ->color(fn ($state): string => match ($state) {
                        'bug' => 'danger',
                        'melhoria' => 'info',
                        'funcionalidade' => 'success',
                        'duvida' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('prioridade')
                    ->label('Prioridade')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => match ($state) {
                        'baixa' => 'Baixa',
                        'media' => 'Média',
                        'alta' => 'Alta',
                        default => (string) $state,
                    })
                    ->color(fn ($state): string => match ($state) {
                        'baixa' => 'gray',
                        'media' => 'warning',
                        'alta' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => match ($state) {
                        'aberta' => 'Aberta',
                        'em_analise' => 'Em análise',
                        'aceita' => 'Aceita',
                        'recusada' => 'Recusada',
                        'implementada' => 'Implementada',
                        default => (string) $state,
                    })
                    ->color(fn ($state): string => match ($state) {
                        'aberta' => 'warning',
                        'em_analise' => 'info',
                        'aceita' => 'success',
                        'recusada' => 'danger',
                        'implementada' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('empresa.razao_social')
                    ->label('Empresa')
                    ->limit(30)
                    ->tooltip(fn (SugestaoMelhoria $record): ?string => $record->empresa?->razao_social)
                    ->toggleable(),

                TextColumn::make('usuario.name')
                    ->label('Usuário')
                    ->limit(25)
                    ->tooltip(fn (SugestaoMelhoria $record): ?string => $record->usuario?->name)
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('tipo')
                    ->label('Tipo')
                    ->options([
                        'bug' => 'Bug',
                        'melhoria' => 'Melhoria',
                        'funcionalidade' => 'Nova funcionalidade',
                        'duvida' => 'Dúvida',
                        'outro' => 'Outro',
                    ]),

                SelectFilter::make('prioridade')
                    ->label('Prioridade')
                    ->options([
                        'baixa' => 'Baixa',
                        'media' => 'Média',
                        'alta' => 'Alta',
                    ]),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'aberta' => 'Aberta',
                        'em_analise' => 'Em análise',
                        'aceita' => 'Aceita',
                        'recusada' => 'Recusada',
                        'implementada' => 'Implementada',
                    ]),
            ], layout: FiltersLayout::AboveContent)
            ->recordActions([
                Action::make('visualizar')
                    ->label('Ler proposta')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->modalHeading(fn (SugestaoMelhoria $record): string => $record->titulo)
                    ->modalDescription('Detalhes completos da sugestão enviada.')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Fechar')
                    ->modalWidth('4xl')
                    ->modalContent(fn (SugestaoMelhoria $record): HtmlString => new HtmlString(self::getModalContent($record))),

                ActionGroup::make([
                    Action::make('editar')
                        ->label(fn (): string => Filament::auth()->user()?->isSuperAdmin() === true ? 'Responder' : 'Editar')
                        ->icon('heroicon-o-pencil-square')
                        ->color('warning')
                        ->url(fn (SugestaoMelhoria $record): string => SugestaoMelhoriaResource::getUrl('edit', [
                            'record' => $record,
                        ])),

                    DeleteAction::make()
                        ->label('Deletar')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Deletar sugestão')
                        ->modalDescription('Tem certeza que deseja deletar esta sugestão? Esta ação não poderá ser desfeita.'),
                ])
                    ->label('Mais')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->color('gray')
                    ->button(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make()
                    ->label('Deletar selecionadas')
                    ->requiresConfirmation(),
            ]);
    }

    protected static function getModalContent(SugestaoMelhoria $record): string
    {
        $empresa = e($record->empresa?->razao_social ?? '-');
        $usuario = e($record->usuario?->name ?? '-');
        $tipo = e($record->getTipoFormatado());
        $prioridade = e($record->getPrioridadeFormatada());
        $status = e($record->getStatusFormatado());
        $criadoEm = e($record->created_at?->format('d/m/Y H:i') ?? '-');
        $descricao = nl2br(e($record->descricao));
        $resposta = nl2br(e($record->resposta_admin ?? ''));
        $analisador = e($record->analisador?->name ?? '-');
        $analisadoEm = e($record->analisado_em?->format('d/m/Y H:i') ?? '-');

        $html = <<<HTML
<link rel="stylesheet" href="/css/style.css">

<div class="sugestao-modal">
    <div class="sugestao-modal__grid">
        <div class="sugestao-modal__card">
            <p class="sugestao-modal__label">Empresa</p>
            <p class="sugestao-modal__value">{$empresa}</p>
        </div>

        <div class="sugestao-modal__card">
            <p class="sugestao-modal__label">Usuário</p>
            <p class="sugestao-modal__value">{$usuario}</p>
        </div>

        <div class="sugestao-modal__card">
            <p class="sugestao-modal__label">Tipo</p>
            <p class="sugestao-modal__value">
                <span class="sugestao-modal__badge sugestao-modal__badge--tipo">{$tipo}</span>
            </p>
        </div>

        <div class="sugestao-modal__card">
            <p class="sugestao-modal__label">Prioridade</p>
            <p class="sugestao-modal__value">
                <span class="sugestao-modal__badge sugestao-modal__badge--prioridade">{$prioridade}</span>
            </p>
        </div>

        <div class="sugestao-modal__card">
            <p class="sugestao-modal__label">Status</p>
            <p class="sugestao-modal__value">
                <span class="sugestao-modal__badge sugestao-modal__badge--status">{$status}</span>
            </p>
        </div>

        <div class="sugestao-modal__card">
            <p class="sugestao-modal__label">Enviado em</p>
            <p class="sugestao-modal__value">{$criadoEm}</p>
        </div>
    </div>

    <div class="sugestao-modal__box">
        <p class="sugestao-modal__box-title">Proposta enviada</p>
        <p class="sugestao-modal__text">{$descricao}</p>
    </div>
HTML;

        if (filled($record->resposta_admin)) {
            $html .= <<<HTML
    <div class="sugestao-modal__box sugestao-modal__box--answer">
        <p class="sugestao-modal__box-title">Resposta do super admin</p>
        <p class="sugestao-modal__text">{$resposta}</p>

        <div class="sugestao-modal__meta">
            Respondido por <strong>{$analisador}</strong> em <strong>{$analisadoEm}</strong>.
        </div>
    </div>
HTML;
        }

        $html .= '</div>';

        return $html;
    }
}
