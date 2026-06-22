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
                    ->label('Contribuição')
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
                        'bug' => 'Dor ou problema',
                        'melhoria' => 'Melhoria',
                        'funcionalidade' => 'Ideia de funcionalidade',
                        'duvida' => 'Dúvida de uso',
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
                        'aberta' => 'Recebida',
                        'em_analise' => 'Em análise',
                        'aceita' => 'Planejada',
                        'recusada' => 'Não seguirá agora',
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
                        'bug' => 'Dor ou problema',
                        'melhoria' => 'Melhoria',
                        'funcionalidade' => 'Ideia de funcionalidade',
                        'duvida' => 'Dúvida de uso',
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
                        'aberta' => 'Recebida',
                        'em_analise' => 'Em análise',
                        'aceita' => 'Planejada',
                        'recusada' => 'Não seguirá agora',
                        'implementada' => 'Implementada',
                    ]),
            ], layout: FiltersLayout::AboveContent)
            ->recordActions([
                Action::make('visualizar')
                    ->label('Ler contribuição')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->modalHeading(fn (SugestaoMelhoria $record): string => $record->titulo)
                    ->modalDescription('Detalhes completos da contribuição enviada pelo cliente.')
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
                        ->modalHeading('Deletar contribuição')
                        ->modalDescription('Tem certeza que deseja deletar esta contribuição? Esta ação não poderá ser desfeita.'),
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
        $statusKey = e((string) $record->status);
        $statusHint = e(self::getStatusHint((string) $record->status));
        $timeline = self::renderStatusTimeline((string) $record->status);

        $html = <<<HTML
<link rel="stylesheet" href="/css/style.css">

<div class="sugestao-modal">
    <div class="sugestao-modal__hero">
        <div>
            <p class="sugestao-modal__eyebrow">Central de Evolução</p>
            <h3 class="sugestao-modal__title">{$status}</h3>
            <p class="sugestao-modal__subtitle">{$statusHint}</p>
        </div>
        <span class="sugestao-modal__status-pill sugestao-modal__status-pill--{$statusKey}">{$status}</span>
    </div>

    {$timeline}

    <div class="sugestao-modal__grid sugestao-modal__grid--compact">
        <div class="sugestao-modal__card">
            <p class="sugestao-modal__label">Empresa</p>
            <p class="sugestao-modal__value">{$empresa}</p>
        </div>

        <div class="sugestao-modal__card">
            <p class="sugestao-modal__label">Enviado por</p>
            <p class="sugestao-modal__value">{$usuario}</p>
        </div>

        <div class="sugestao-modal__card">
            <p class="sugestao-modal__label">Tipo</p>
            <p class="sugestao-modal__value">
                <span class="sugestao-modal__badge sugestao-modal__badge--tipo">{$tipo}</span>
            </p>
        </div>

        <div class="sugestao-modal__card">
            <p class="sugestao-modal__label">Impacto informado</p>
            <p class="sugestao-modal__value">
                <span class="sugestao-modal__badge sugestao-modal__badge--prioridade">{$prioridade}</span>
            </p>
        </div>

        <div class="sugestao-modal__card">
            <p class="sugestao-modal__label">Enviado em</p>
            <p class="sugestao-modal__value">{$criadoEm}</p>
        </div>
    </div>

    <div class="sugestao-modal__box sugestao-modal__box--main">
        <p class="sugestao-modal__box-title">O que foi compartilhado</p>
        <p class="sugestao-modal__text">{$descricao}</p>
    </div>
HTML;

        if (filled($record->resposta_admin)) {
            $html .= <<<HTML
    <div class="sugestao-modal__box sugestao-modal__box--answer">
        <p class="sugestao-modal__box-title">Resposta do Prazzu</p>
        <p class="sugestao-modal__text">{$resposta}</p>

        <div class="sugestao-modal__meta">
            Atualizado por <strong>{$analisador}</strong> em <strong>{$analisadoEm}</strong>.
        </div>
    </div>
HTML;
        } else {
            $html .= <<<HTML
    <div class="sugestao-modal__box sugestao-modal__box--empty-answer">
        <p class="sugestao-modal__box-title">Acompanhamento</p>
        <p class="sugestao-modal__text">Sua contribuição foi recebida. Quando houver uma decisão ou resposta da equipe do Prazzu, ela aparecerá aqui.</p>
    </div>
HTML;
        }

        $html .= '</div>';

        return $html;
    }


    protected static function renderStatusTimeline(string $status): string
    {
        if ($status === 'recusada') {
            return <<<HTML
    <div class="sugestao-modal__timeline" aria-label="Status da evolução">
        <div class="sugestao-modal__step sugestao-modal__step--done">Recebida</div>
        <div class="sugestao-modal__step sugestao-modal__step--done">Analisada</div>
        <div class="sugestao-modal__step sugestao-modal__step--blocked">Não seguirá agora</div>
    </div>
HTML;
        }

        $steps = [
            'aberta' => 'Recebida',
            'em_analise' => 'Em análise',
            'aceita' => 'Planejada',
            'implementada' => 'Implementada',
        ];

        $order = array_keys($steps);
        $currentIndex = array_search($status, $order, true);

        if ($currentIndex === false) {
            $currentIndex = 0;
        }

        $items = '';

        foreach ($steps as $key => $label) {
            $index = array_search($key, $order, true);
            $class = 'sugestao-modal__step';

            if ($index < $currentIndex) {
                $class .= ' sugestao-modal__step--done';
            } elseif ($index === $currentIndex) {
                $class .= ' sugestao-modal__step--active';
            }

            $items .= '<div class="'.$class.'">'.e($label).'</div>';
        }

        return '<div class="sugestao-modal__timeline" aria-label="Status da evolução">'.$items.'</div>';
    }

    protected static function getStatusHint(string $status): string
    {
        return match ($status) {
            'aberta' => 'Sua contribuição foi recebida e entrou na fila de análise do produto.',
            'em_analise' => 'A equipe está avaliando impacto, recorrência e possibilidade de uma solução escalável.',
            'aceita' => 'A contribuição foi considerada relevante e entrou no radar de evolução.',
            'recusada' => 'A ideia foi analisada, mas não seguirá neste momento por prioridade, escopo ou escalabilidade.',
            'implementada' => 'Essa evolução já foi implementada ou resolvida no Prazzu.',
            default => 'Acompanhe aqui o andamento desta contribuição.',
        };
    }
}
