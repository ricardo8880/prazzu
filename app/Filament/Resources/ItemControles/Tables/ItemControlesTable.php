<?php

namespace App\Filament\Resources\ItemControles\Tables;

use App\Models\Empresa;
use App\Models\ItemControleChecklist;
use App\Models\ItemControleTimeline;
use App\Models\Responsavel;
use App\Services\PlanoService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ItemControlesTable
{
    /**
     * Cache simples por request para evitar consultar/plano/features em cada linha da tabela.
     */
    protected static array $empresaFeatureCache = [];

    protected static array $usuarioFeatureCache = [];

    public static function make(Table $table, string $context = 'geral'): Table
    {
        return $table
            ->defaultSort('data_vencimento', 'asc')
            ->defaultPaginationPageOption(5)
            ->paginated([5, 10, 25])
            ->striped()
            ->columns(self::getColumnsForContext($context))
            ->filters(self::getFiltersForContext($context), layout: FiltersLayout::AboveContent)
            ->recordActions([
                ActionGroup::make([
                    Action::make('checklist')
                    ->label('Checklist')
                    ->icon('heroicon-o-check-circle')
                    ->visible(fn (): bool => $context === 'checklists')
                    ->modalHeading(fn ($record): string => 'Checklist - ' . $record->titulo)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Fechar')
                    ->schema(fn ($record): array => self::getChecklistModalSchema($record)),

                Action::make('timeline')
                    ->label('Timeline')
                    ->icon('heroicon-o-clock')
                    ->visible(fn (): bool => $context === 'timelines')
                    ->modalHeading(fn ($record): string => 'Timeline - ' . $record->titulo)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Fechar')
                    ->schema(fn ($record): array => self::getTimelineModalSchema($record)),

                Action::make('concluir')
                    ->label('Concluir')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn ($record): bool => $record && ! in_array((string) $record->status, ['concluido', 'cancelado'], true))
                    ->action(function ($record): void {
                        $record?->update([
                            'status' => 'concluido',
                            'data_conclusao' => now(),
                        ]);

                        if ($record && filled($record->sla_status) && ! $record->sla_concluido_em) {
                            $record->concluirSla();
                        }

                        $record?->registrarTimeline(
                            'atualizacao',
                            'Item concluido',
                            'O item foi marcado como concluido.'
                        );
                    }),

                    Action::make('pdf')
                        ->label('Exportar PDF')
                        ->icon('heroicon-o-document-arrow-down')
                        ->url(fn ($record): string => route('item-controles.pdf', [
                            'itemControle' => $record->id,
                        ]))
                        ->openUrlInNewTab(),

                    Action::make('iniciar_sla')
                        ->label('Iniciar SLA')
                        ->icon('heroicon-o-play')
                        ->color('info')
                        ->visible(fn ($record): bool => $context === 'geral' && $record && self::empresaPossuiFeatureCached($record->empresa, PlanoService::FEATURE_SLA) && blank($record->sla_status))
                        ->requiresConfirmation()
                        ->modalHeading(fn ($record): string => 'Iniciar SLA - ' . $record->titulo)
                        ->action(function ($record): void {
                            $record->iniciarSla();

                            Notification::make()
                                ->title('SLA iniciado com sucesso.')
                                ->success()
                                ->send();
                        }),

                    Action::make('atualizar_sla')
                        ->label('Atualizar SLA')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->visible(fn ($record): bool => $context === 'geral' && $record && self::empresaPossuiFeatureCached($record->empresa, PlanoService::FEATURE_SLA) && filled($record->sla_status) && ! $record->sla_concluido_em)
                        ->action(function ($record): void {
                            $record->atualizarSlaStatus();

                            Notification::make()
                                ->title('Status do SLA atualizado.')
                                ->success()
                                ->send();
                        }),

                    Action::make('finalizar_sla')
                        ->label('Finalizar SLA')
                        ->icon('heroicon-o-stop')
                        ->color('success')
                        ->visible(fn ($record): bool => $context === 'geral' && $record && self::empresaPossuiFeatureCached($record->empresa, PlanoService::FEATURE_SLA) && filled($record->sla_status) && ! $record->sla_concluido_em)
                        ->requiresConfirmation()
                        ->modalHeading(fn ($record): string => 'Finalizar SLA - ' . $record->titulo)
                        ->action(function ($record): void {
                            $record->concluirSla();

                            Notification::make()
                                ->title('SLA finalizado com sucesso.')
                                ->success()
                                ->send();
                        }),

                    Action::make('atualizar_contrato')
                        ->label('Atualizar contrato')
                        ->icon('heroicon-o-document-text')
                        ->color('warning')
                        ->visible(fn ($record): bool => $context === 'geral' && $record && self::empresaPossuiFeatureCached($record->empresa, PlanoService::FEATURE_CONTRATOS) && $record->isContrato())
                        ->action(function ($record): void {
                            $record->atualizarStatusContrato();

                            Notification::make()
                                ->title('Status do contrato atualizado.')
                                ->success()
                                ->send();
                        }),

                    Action::make('portal_ativar')
                        ->label('Ativar portal')
                        ->icon('heroicon-o-link')
                        ->visible(fn ($record): bool => $context === 'assinaturas' && $record && self::usuarioPossuiFeatureCached(Filament::auth()->user(), PlanoService::FEATURE_PORTAL_CLIENTE) && ! $record->portal_ativo)
                        ->action(function ($record): void {
                            $record->ativarPortalCliente();

                            Notification::make()
                                ->title('Portal ativado com sucesso.')
                                ->success()
                                ->send();
                        }),

                    Action::make('portal_link')
                        ->label('Link portal')
                        ->icon('heroicon-o-clipboard-document')
                        ->visible(fn ($record): bool => $context === 'assinaturas' && $record && self::usuarioPossuiFeatureCached(Filament::auth()->user(), PlanoService::FEATURE_PORTAL_CLIENTE) && $record->portal_ativo && filled($record->portal_token))
                        ->modalHeading('Link do Portal do Cliente')
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Fechar')
                        ->schema(fn ($record): array => [
                            Section::make('Link externo')
                                ->description('Copie este link e envie para o cliente acompanhar o item.')
                                ->schema([
                                    TextInput::make('portal_url')
                                        ->label('URL do portal')
                                        ->default($record->getPortalUrl())
                                        ->readOnly()
                                        ->columnSpanFull(),
                                ]),
                        ]),

                    Action::make('portal_desativar')
                        ->label('Desativar portal')
                        ->icon('heroicon-o-lock-closed')
                        ->color('danger')
                        ->visible(fn ($record): bool => $context === 'assinaturas' && $record && self::usuarioPossuiFeatureCached(Filament::auth()->user(), PlanoService::FEATURE_PORTAL_CLIENTE) && $record->portal_ativo)
                        ->requiresConfirmation()
                        ->modalHeading('Desativar portal do cliente')
                        ->modalDescription('O cliente nao conseguira mais acessar este item pelo link externo.')
                        ->action(function ($record): void {
                            $record->desativarPortalCliente();

                            Notification::make()
                                ->title('Portal desativado com sucesso.')
                                ->success()
                                ->send();
                        }),

                    Action::make('solicitar_aprovacao')
                        ->label('Solicitar aprovacao')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('warning')
                        ->visible(fn ($record): bool => $context === 'aprovacoes' && $record && self::empresaPossuiFeatureCached($record->empresa, PlanoService::FEATURE_APROVACOES) && $record->podeSolicitarAprovacao())
                        ->modalHeading(fn ($record): string => 'Solicitar aprovacao - ' . $record->titulo)
                        ->schema([
                            Textarea::make('observacao')
                                ->label('Observacao')
                                ->rows(4)
                                ->maxLength(2000)
                                ->columnSpanFull(),
                        ])
                        ->action(function ($record, array $data): void {
                            $record->solicitarAprovacao(
                                Filament::auth()->user(),
                                $data['observacao'] ?? null
                            );

                            Notification::make()
                                ->title('Aprovacao solicitada com sucesso.')
                                ->success()
                                ->send();
                        }),

                    Action::make('aprovar')
                        ->label('Aprovar')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->visible(fn ($record): bool => $context === 'aprovacoes' && $record
                            && self::empresaPossuiFeatureCached($record->empresa, PlanoService::FEATURE_APROVACOES)
                            && $record->possuiAprovacaoPendente()
                            && $record->canBeApprovedBy(Filament::auth()->user())
                        )
                        ->modalHeading(fn ($record): string => 'Aprovar item - ' . $record->titulo)
                        ->schema([
                            Textarea::make('observacao')
                                ->label('Observacao da aprovacao')
                                ->rows(4)
                                ->maxLength(2000)
                                ->columnSpanFull(),
                        ])
                        ->action(function ($record, array $data): void {
                            $record->aprovar(
                                Filament::auth()->user(),
                                $data['observacao'] ?? null
                            );

                            Notification::make()
                                ->title('Item aprovado com sucesso.')
                                ->success()
                                ->send();
                        }),

                    Action::make('reprovar')
                        ->label('Reprovar')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn ($record): bool => $context === 'aprovacoes' && $record
                            && self::empresaPossuiFeatureCached($record->empresa, PlanoService::FEATURE_APROVACOES)
                            && $record->possuiAprovacaoPendente()
                            && $record->canBeApprovedBy(Filament::auth()->user())
                        )
                        ->modalHeading(fn ($record): string => 'Reprovar item - ' . $record->titulo)
                        ->schema([
                            Textarea::make('observacao')
                                ->label('Motivo da reprovacao')
                                ->required()
                                ->rows(4)
                                ->maxLength(2000)
                                ->columnSpanFull(),
                        ])
                        ->action(function ($record, array $data): void {
                            $record->reprovar(
                                Filament::auth()->user(),
                                $data['observacao'] ?? null
                            );

                            Notification::make()
                                ->title('Item reprovado.')
                                ->success()
                                ->send();
                        }),

                    Action::make('criar_alerta')
                        ->label('Criar alerta')
                        ->icon('heroicon-o-bell-alert')
                        ->color('info')
                        ->visible(fn ($record): bool => $context === 'aprovacoes' && $record && self::empresaPossuiFeatureCached($record->empresa, PlanoService::FEATURE_ALERTAS_MANUAIS))
                        ->modalHeading(fn ($record): string => 'Criar alerta - ' . $record->titulo)
                        ->schema([
                            TextInput::make('titulo')
                                ->label('Titulo')
                                ->required()
                                ->maxLength(255)
                                ->trim(),

                            Textarea::make('mensagem')
                                ->label('Mensagem')
                                ->rows(4)
                                ->maxLength(2000)
                                ->columnSpanFull(),
                        ])
                        ->action(function ($record, array $data): void {
                            $record->gerarNotificacaoInterna(
                                $data['titulo'],
                                $data['mensagem'] ?? null,
                                null,
                                'manual'
                            );

                            Notification::make()
                                ->title('Alerta interno criado com sucesso.')
                                ->success()
                                ->send();
                        }),

                    Action::make('ver_assinatura')
                        ->label('Assinado')
                        ->icon('heroicon-o-document-check')
                        ->visible(fn ($record): bool => $context === 'assinaturas' && $record && $record->foiAssinado())
                        ->modalHeading(fn ($record): string => 'Assinatura - ' . $record->titulo)
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('Fechar')
                        ->schema(fn ($record): array => [
                            Section::make('Dados da assinatura')
                                ->schema([
                                    TextInput::make('assinatura_nome')
                                        ->label('Nome')
                                        ->default($record->ultimaAssinatura?->nome)
                                        ->readOnly(),

                                    TextInput::make('assinatura_email')
                                        ->label('E-mail')
                                        ->default($record->ultimaAssinatura?->email)
                                        ->readOnly(),

                                    TextInput::make('assinatura_documento')
                                        ->label('Documento')
                                        ->default($record->ultimaAssinatura?->documento)
                                        ->readOnly(),

                                    TextInput::make('assinatura_data')
                                        ->label('Assinado em')
                                        ->default($record->ultimaAssinatura?->assinado_em?->format('d/m/Y H:i'))
                                        ->readOnly(),

                                    TextInput::make('assinatura_empresa')
                                        ->label('Empresa')
                                        ->default($record->ultimaAssinatura?->empresa?->razao_social ?? $record->empresa?->razao_social)
                                        ->readOnly(),

                                    TextInput::make('assinatura_usuario')
                                        ->label('Usuário vinculado')
                                        ->default($record->ultimaAssinatura?->user?->name ?? '-')
                                        ->readOnly(),

                                    TextInput::make('assinatura_ip')
                                        ->label('IP')
                                        ->default($record->ultimaAssinatura?->ip)
                                        ->readOnly(),

                                    TextInput::make('assinatura_user_agent')
                                        ->label('Navegador / Dispositivo')
                                        ->default($record->ultimaAssinatura?->user_agent)
                                        ->readOnly()
                                        ->columnSpanFull(),

                                    TextInput::make('assinatura_hash')
                                        ->label('Hash')
                                        ->default($record->ultimaAssinatura?->hash_assinatura)
                                        ->readOnly()
                                        ->columnSpanFull(),

                                    Textarea::make('assinatura_aceite_texto')
                                        ->label('Texto do aceite')
                                        ->default($record->ultimaAssinatura?->aceite_texto)
                                        ->readOnly()
                                        ->rows(4)
                                        ->columnSpanFull(),

                                    Textarea::make('assinatura_observacao')
                                        ->label('Observação')
                                        ->default($record->ultimaAssinatura?->observacao)
                                        ->readOnly()
                                        ->rows(3)
                                        ->visible(fn (): bool => filled($record->ultimaAssinatura?->observacao))
                                        ->columnSpanFull(),
                                ])
                                ->columns(2),
                        ]),

                    Action::make('adicionar_checklist')
                        ->label('Adicionar etapa')
                        ->icon('heroicon-o-plus-circle')
                        ->visible(fn (): bool => $context === 'checklists')
                        ->modalHeading('Adicionar etapa do checklist')
                        ->schema([
                            TextInput::make('titulo')
                                ->label('Etapa')
                                ->required()
                                ->maxLength(255)
                                ->trim(),

                            TextInput::make('ordem')
                                ->label('Ordem')
                                ->numeric()
                                ->default(0)
                                ->minValue(0),
                        ])
                        ->action(function ($record, array $data): void {
                            ItemControleChecklist::query()->create([
                                'item_controle_id' => $record->id,
                                'titulo' => $data['titulo'],
                                'ordem' => (int) ($data['ordem'] ?? 0),
                                'concluido' => false,
                            ]);

                            $record->registrarTimeline(
                                'checklist',
                                'Etapa adicionada ao checklist',
                                $data['titulo']
                            );
                        }),

                    DeleteAction::make(),
                ])
                    ->label('Ações')
                    ->icon('heroicon-o-bars-3-bottom-right')
                    ->color('primary')
                    ->button(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ])
            ->recordClasses(function ($record) {
                if (! $record?->data_vencimento) {
                    return null;
                }

                if (in_array((string) $record->status, ['concluido', 'cancelado'], true)) {
                    return null;
                }

                $dias = now()->startOfDay()->diffInDays(
                    $record->data_vencimento->copy()->startOfDay(),
                    false
                );

                if ($dias < 0) {
                    return 'border-l-4 border-danger-500';
                }

                if ($dias <= 3) {
                    return 'border-l-4 border-warning-500';
                }

                return null;
            });
    }


    protected static function empresaPossuiFeatureCached(?\App\Models\Empresa $empresa, string $feature): bool
    {
        if (! $empresa) {
            return false;
        }

        $key = $empresa->id . ':' . $feature;

        if (! array_key_exists($key, self::$empresaFeatureCache)) {
            self::$empresaFeatureCache[$key] = PlanoService::empresaPossuiFeature($empresa, $feature);
        }

        return self::$empresaFeatureCache[$key];
    }

    protected static function usuarioPossuiFeatureCached(?\App\Models\User $user, string $feature): bool
    {
        if (! $user) {
            return false;
        }

        $key = $user->id . ':' . $feature;

        if (! array_key_exists($key, self::$usuarioFeatureCache)) {
            self::$usuarioFeatureCache[$key] = PlanoService::usuarioPossuiFeature($user, $feature);
        }

        return self::$usuarioFeatureCache[$key];
    }


    protected static function getColumnsForContext(string $context): array
    {
        $baseColumns = [
            IconColumn::make('arquivo')
                ->label('Doc')
                ->boolean()
                ->getStateUsing(fn ($record): bool => filled($record?->arquivo))
                ->tooltip(fn ($record): string => filled($record?->arquivo) ? 'Possui documento anexado' : 'Sem documento'),

            TextColumn::make('titulo')
                ->label('Item')
                ->searchable()
                ->sortable()
                ->weight(FontWeight::SemiBold)
                ->description(fn ($record): ?string => $record?->empresa?->razao_social)
                ->limit(45),

            TextColumn::make('categoria.nome')
                ->label('Categoria')
                ->badge()
                ->sortable()
                ->getStateUsing(fn ($record): string => $record?->getTipoOuCategoria() ?? '-')
                ->toggleable(),

            TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->sortable()
                ->formatStateUsing(fn ($record): string => $record?->getStatusExibicao() ?? '-')
                ->color(fn ($record): string => $record?->getStatusExibicaoColor() ?? 'gray'),

            TextColumn::make('prioridade')
                ->label('Prioridade')
                ->badge()
                ->sortable()
                ->formatStateUsing(fn ($record): string => $record?->getPrioridadeExibicao() ?? 'Media')
                ->color(fn ($record): string => $record?->getPrioridadeColor() ?? 'info'),
        ];

        $contextColumns = match ($context) {
            'checklists' => [
                TextColumn::make('checklist_resumo')
                    ->label('Checklist')
                    ->badge()
                    ->getStateUsing(fn ($record): string => $record?->getChecklistResumo() ?? '-')
                    ->color(fn ($record): string => $record?->getChecklistColor() ?? 'gray'),
            ],

            'timelines' => [
                TextColumn::make('timelines_count')
                    ->label('Eventos')
                    ->badge()
                    ->color('gray'),
            ],

            'assinaturas' => [
                TextColumn::make('portal_status_visual')
                    ->label('Portal')
                    ->badge()
                    ->getStateUsing(fn ($record): string => $record?->portal_ativo ? 'Ativo' : 'Inativo')
                    ->color(fn ($record): string => $record?->portal_ativo ? 'success' : 'gray')
                    ->sortable(query: fn ($query, string $direction) => $query->orderBy('portal_ativo', $direction)),

                TextColumn::make('assinatura')
                    ->label('Assinatura')
                    ->badge()
                    ->getStateUsing(fn ($record): string => $record?->getAssinaturaResumo() ?? 'Nao assinado')
                    ->color(fn ($record): string => $record?->getAssinaturaColor() ?? 'gray'),
            ],

            'aprovacoes' => [
                TextColumn::make('aprovacao')
                    ->label('Aprovacao')
                    ->badge()
                    ->getStateUsing(fn ($record): string => $record?->getAprovacaoResumo() ?? 'Sem aprovacao')
                    ->color(fn ($record): string => $record?->getAprovacaoColor() ?? 'gray'),

                TextColumn::make('notificacoes_internas_count')
                    ->label('Alertas')
                    ->badge()
                    ->color('danger'),
            ],

            'anexos' => [
                TextColumn::make('comentarios_count')
                    ->label('Comentarios')
                    ->badge()
                    ->color('info'),

                TextColumn::make('anexos_count')
                    ->label('Anexos')
                    ->badge()
                    ->color('warning'),
            ],

            default => [
                TextColumn::make('sla_resumo_visual')
                    ->label('SLA')
                    ->badge()
                    ->getStateUsing(function ($record): string {
                        if (! $record || blank($record->sla_status)) {
                            return 'Sem SLA';
                        }

                        $tempo = $record->getSlaTempoRestanteResumo();

                        return filled($tempo)
                            ? $record->getSlaResumo() . ' • ' . $tempo
                            : $record->getSlaResumo();
                    })
                    ->color(fn ($record): string => $record?->getSlaColor() ?? 'gray')
                    ->toggleable(),

                TextColumn::make('contrato_status')
                    ->label('Contrato')
                    ->badge()
                    ->getStateUsing(fn ($record): string => $record?->isContrato() ? $record->getContratoStatusResumo() : '-')
                    ->color(fn ($record): string => $record?->isContrato() ? $record->getContratoStatusColor() : 'gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('contrato_fim_em')
                    ->label('Fim contrato')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ],
        };

        return [
            ...$baseColumns,
            ...$contextColumns,
            TextColumn::make('responsavel.nome')
                ->label('Responsavel')
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('data_vencimento')
                ->label('Vencimento')
                ->date('d/m/Y')
                ->sortable(),

            TextColumn::make('dias_restantes')
                ->label('Prazo')
                ->getStateUsing(fn ($record): string => (string) ($record?->getDiasRestantes() ?? '-'))
                ->badge()
                ->color(fn ($record): string => $record?->getDiasRestantesColor() ?? 'gray'),
        ];
    }

    protected static function getFiltersForContext(string $context): array
    {
        $filters = [
            SelectFilter::make('categoria_id')
                ->label('Categoria')
                ->relationship('categoria', 'nome')
                ->searchable(),

            SelectFilter::make('prioridade')
                ->label('Prioridade')
                ->options([
                    'baixa' => 'Baixa',
                    'media' => 'Media',
                    'alta' => 'Alta',
                    'urgente' => 'Urgente',
                ]),

            SelectFilter::make('status')
                ->label('Status do item')
                ->options([
                    'pendente' => 'Pendente',
                    'em_aprovacao' => 'Em aprovacao',
                    'aprovado' => 'Aprovado',
                    'reprovado' => 'Reprovado',
                    'em_andamento' => 'Em andamento',
                    'assinado' => 'Assinado',
                    'concluido' => 'Concluido',
                    'cancelado' => 'Cancelado',
                    'vencido' => 'Vencido',
                ])
                ->query(function (Builder $query, array $data): Builder {
                    $value = $data['value'] ?? null;

                    if (blank($value)) {
                        return $query;
                    }

                    if ($value === 'assinado') {
                        return $query->where(function (Builder $query): void {
                            $query->where('status', 'assinado')
                                ->orWhereHas('assinaturas');
                        });
                    }

                    return $query->where('status', $value);
                }),
        ];

        if ($context === 'geral') {
            $filters[] = SelectFilter::make('contrato_status')
                ->label('Status do contrato')
                ->options([
                    'rascunho' => 'Rascunho',
                    'ativo' => 'Ativo',
                    'vigente' => 'Vigente',
                    'vencendo' => 'Vencendo',
                    'vencido' => 'Vencido',
                    'encerrado' => 'Encerrado',
                    'cancelado' => 'Cancelado',
                ]);

            $filters[] = SelectFilter::make('sla_status')
                ->label('SLA')
                ->options([
                    'em_andamento' => 'Em andamento',
                    'atrasado' => 'Atrasado',
                    'vencido' => 'Vencido',
                    'concluido_no_prazo' => 'Concluido no prazo',
                    'concluido_atrasado' => 'Concluido com atraso',
                    'sem_sla' => 'Sem SLA',
                ])
                ->query(function (Builder $query, array $data): Builder {
                    $value = $data['value'] ?? null;

                    if (blank($value)) {
                        return $query;
                    }

                    if ($value === 'sem_sla') {
                        return $query->where(function (Builder $query): void {
                            $query->whereNull('sla_status')
                                ->orWhere('sla_status', '');
                        });
                    }

                    return $query->where('sla_status', $value);
                });
        }

        if ($context === 'assinaturas') {
            $filters[] = SelectFilter::make('assinatura_status')
                ->label('Status da assinatura')
                ->options([
                    'assinado' => 'Assinados',
                    'nao_assinado' => 'Nao assinados',
                ])
                ->query(function (Builder $query, array $data): Builder {
                    $value = $data['value'] ?? null;

                    if (blank($value)) {
                        return $query;
                    }

                    return match ($value) {
                        'assinado' => $query->whereHas('assinaturas'),
                        'nao_assinado' => $query->whereDoesntHave('assinaturas'),
                        default => $query,
                    };
                });

            $filters[] = SelectFilter::make('portal_ativo')
                ->label('Portal')
                ->options([
                    1 => 'Ativo',
                    0 => 'Inativo',
                ]);
        }

        if ($context === 'geral') {
            $filters[] = SelectFilter::make('tags')
                ->label('Tag')
                ->relationship('tags', 'nome')
                ->searchable();
        }

        $filters[] = SelectFilter::make('empresa_id')
            ->label('Empresa')
            ->searchable()
            ->getSearchResultsUsing(fn (string $search): array => Empresa::query()
                ->select(['id', 'razao_social'])
                ->where('razao_social', 'like', "%{$search}%")
                ->orderBy('razao_social')
                ->limit(50)
                ->pluck('razao_social', 'id')
                ->toArray()
            )
            ->getOptionLabelUsing(fn ($value): ?string => blank($value)
                ? null
                : Empresa::query()->whereKey($value)->value('razao_social')
            );

        $filters[] = SelectFilter::make('responsavel_id')
            ->label('Responsavel')
            ->searchable()
            ->getSearchResultsUsing(fn (string $search): array => Responsavel::query()
                ->select(['id', 'nome'])
                ->where('nome', 'like', "%{$search}%")
                ->orderBy('nome')
                ->limit(50)
                ->pluck('nome', 'id')
                ->toArray()
            )
            ->getOptionLabelUsing(fn ($value): ?string => blank($value)
                ? null
                : Responsavel::query()->whereKey($value)->value('nome')
            );

        return $filters;
    }

    protected static function getChecklistModalSchema($record): array
    {
        $checklists = $record->checklists()
            ->orderBy('ordem')
            ->orderBy('id')
            ->get();

        if ($checklists->isEmpty()) {
            return [
                Section::make('Checklist')
                    ->description('Este item ainda nao possui etapas cadastradas. Use a acao "Adicionar etapa".')
                    ->schema([]),
            ];
        }

        return [
            Section::make('Etapas do checklist')
                ->description('Marque ou desmarque as etapas conforme o andamento do item.')
                ->schema(
                    $checklists
                        ->map(function (ItemControleChecklist $checklist) {
                            return Grid::make(12)
                                ->schema([
                                    Checkbox::make('checklist_' . $checklist->id)
                                        ->label($checklist->titulo)
                                        ->default((bool) $checklist->concluido)
                                        ->live()
                                        ->afterStateUpdated(function ($state) use ($checklist): void {
                                            $user = Filament::auth()->user();

                                            if ($state) {
                                                $checklist->marcarComoConcluido($user);

                                                $checklist->itemControle?->registrarTimeline(
                                                    'checklist',
                                                    'Etapa concluida',
                                                    $checklist->titulo,
                                                    [
                                                        'checklist_id' => $checklist->id,
                                                    ]
                                                );

                                                return;
                                            }

                                            $checklist->marcarComoPendente();

                                            $checklist->itemControle?->registrarTimeline(
                                                'checklist',
                                                'Etapa reaberta',
                                                $checklist->titulo,
                                                [
                                                    'checklist_id' => $checklist->id,
                                                ]
                                            );
                                        })
                                        ->columnSpan(10),

                                    TextInput::make('ordem_' . $checklist->id)
                                        ->label('Ordem')
                                        ->numeric()
                                        ->default($checklist->ordem)
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function ($state) use ($checklist): void {
                                            $checklist->update([
                                                'ordem' => (int) ($state ?? 0),
                                            ]);
                                        })
                                        ->columnSpan(2),
                                ]);
                        })
                        ->toArray()
                ),
        ];
    }

    protected static function getTimelineModalSchema($record): array
    {
        $timelines = $record->timelines()
            ->with(['user:id,name'])
            ->limit(30)
            ->get();

        if ($timelines->isEmpty()) {
            return [
                Section::make('Timeline')
                    ->description('Nenhum evento registrado na timeline deste item.')
                    ->schema([]),
            ];
        }

        return [
            Section::make('Ultimos eventos')
                ->description('Exibindo os 30 eventos mais recentes deste item.')
                ->schema(
                    $timelines
                        ->map(function (ItemControleTimeline $timeline) {
                            $usuario = $timeline->user?->name ?: 'Sistema';
                            $data = $timeline->created_at?->format('d/m/Y H:i') ?: '-';

                            return Section::make($timeline->titulo)
                                ->description($timeline->getTipoExibicao() . ' - ' . $usuario . ' - ' . $data)
                                ->schema([
                                    Textarea::make('timeline_' . $timeline->id)
                                        ->label('Descricao')
                                        ->default($timeline->descricao ?: '-')
                                        ->rows(2)
                                        ->readOnly()
                                        ->columnSpanFull(),
                                ]);
                        })
                        ->toArray()
                ),
        ];
    }
}
