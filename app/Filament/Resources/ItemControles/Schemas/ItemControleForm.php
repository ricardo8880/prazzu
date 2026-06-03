<?php

namespace App\Filament\Resources\ItemControles\Schemas;

use App\Filament\Resources\ItemControles\ItemControleResource;
use App\Models\CategoriaItemControle;
use App\Models\Empresa;
use App\Models\ItemControleTag;
use App\Models\Responsavel;
use App\Support\ItemControleAnexoUploader;
use App\Services\PlanoService;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use App\Support\CachedSchema as DatabaseSchema;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class ItemControleForm
{
    public static function make(Schema $schema): Schema
    {
        $user = Filament::auth()->user();
        $responsavelIdDoUsuario = $user?->responsavel?->id;

        return $schema
            ->components([
                Grid::make(12)
                    ->extraAttributes(['class' => 'prazzu-create-main-layout'])
                    ->schema([
                        Group::make([
                            Section::make('Informações principais')
                    ->extraAttributes(['class' => 'prazzu-main-info-section'])
                    ->description('Preencha apenas o essencial para começar. Você pode complementar depois.')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->schema([
                        TextInput::make('titulo')
                            ->label('Título do Item')
                            ->placeholder('Ex.: Envio da DCTFWeb - Mensal')
                            ->required()
                            ->maxLength(255)
                            ->trim()
                            ->live(onBlur: true)
                            ->columnSpan(['default' => 12, 'lg' => 6]),

                        Select::make('categoria_id')
                            ->label('Categoria')
                            ->placeholder('Selecione uma categoria')
                            ->required()
                            ->searchable()
                            ->native(false)
                            ->live()
                            ->getSearchResultsUsing(function (string $search) use ($user): array {
                                return CategoriaItemControle::query()
                                    ->visibleForUser($user)
                                    ->where('ativo', true)
                                    ->where('nome', 'like', "%{$search}%")
                                    ->orderBy('nome')
                                    ->limit(50)
                                    ->pluck('nome', 'id')
                                    ->toArray();
                            })
                            ->getOptionLabelUsing(fn ($value): ?string => blank($value)
                                ? null
                                : CategoriaItemControle::query()
                                    ->whereKey($value)
                                    ->value('nome')
                            )
                            ->afterStateUpdated(function ($state, callable $set, callable $get) use ($user): void {
                                if (! filled($state)) {
                                    return;
                                }

                                $categoria = CategoriaItemControle::query()
                                    ->visibleForUser($user)
                                    ->select(['id', 'nome'])
                                    ->find($state);

                                if ($categoria && mb_strtolower($categoria->nome) === 'contrato') {
                                    $set('tipo', 'contrato');
                                } else {
                                    $set('tipo', 'documento');
                                }

                                if ($categoria) {
                                    self::aplicarSugestaoOperacionalPorCategoria($categoria->nome, $set, $get);
                                }

                                $checklistsAtuais = $get('checklists');

                                if (is_array($checklistsAtuais) && count($checklistsAtuais) > 0) {
                                    return;
                                }

                                $categoriaComTemplates = CategoriaItemControle::query()
                                    ->visibleForUser($user)
                                    ->with(['checklistTemplatesAtivos'])
                                    ->find($state);

                                if (! $categoriaComTemplates) {
                                    return;
                                }

                                $templates = $categoriaComTemplates->checklistTemplatesAtivos
                                    ->map(fn ($template): array => [
                                        'titulo' => $template->titulo,
                                        'ordem' => (int) $template->ordem,
                                        'concluido' => false,
                                        'concluido_em' => null,
                                        'concluido_por' => null,
                                    ])
                                    ->values()
                                    ->toArray();

                                if (count($templates) > 0) {
                                    $set('checklists', $templates);
                                }
                            })
                            ->helperText('A categoria define o checklist e os prazos.')
                            ->columnSpan(['default' => 12, 'lg' => 6]),

                        Select::make('responsavel_id')
                            ->label('Responsável')
                            ->placeholder('Selecione o responsável')
                            ->required()
                            ->searchable()
                            ->native(false)
                            ->getSearchResultsUsing(function (string $search, callable $get) use ($user): array {
                                return self::getResponsavelSearchResults($search, $user, null);
                            })
                            ->getOptionLabelUsing(fn ($value): ?string => blank($value)
                                ? null
                                : Responsavel::query()->whereKey($value)->value('nome')
                            )
                            ->default(fn (): ?int => ItemControleResource::getDefaultResponsavelIdForUser($user))
                            ->disabled(fn (): bool => $user?->isUser() === true)
                            ->dehydrated(true)
                            ->helperText('Quem será responsável por executar este item.')
                            ->rules([
                                function () use ($user) {
                                    return function (string $attribute, $value, \Closure $fail) use ($user): void {
                                        if (! $user) {
                                            $fail('Usuário não autenticado.');
                                            return;
                                        }

                                        if (! filled($value)) {
                                            $fail('O responsável é obrigatório.');
                                            return;
                                        }

                                        if (! ItemControleResource::canUserAssignResponsavel($user, (int) $value)) {
                                            if ($user->isGestor()) {
                                                $fail('Você só pode vincular o item a responsáveis da sua equipe.');
                                                return;
                                            }

                                            if ($user->isUser()) {
                                                $fail('Você só pode vincular o item ao seu próprio responsável.');
                                                return;
                                            }

                                            $fail('Você não tem permissão para vincular este responsável.');
                                        }
                                    };
                                },
                            ])
                            ->columnSpan(['default' => 12, 'lg' => 6]),

                        DatePicker::make('data_vencimento')
                            ->label('Data de vencimento')
                            ->placeholder('dd/mm/aaaa')
                            ->required()
                            ->native(false)
                            ->live()
                            ->helperText(fn (callable $get): HtmlString => self::renderPrazoVisual($get('data_vencimento')))
                            ->columnSpan(['default' => 12, 'lg' => 4]),

                        Select::make('prioridade')
                            ->label('Prioridade')
                            ->required()
                            ->default('media')
                            ->options(self::getPrioridadeOptions())
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set, callable $get): void {
                                self::sincronizarRiscoOperacional($state, $get('urgencia'), $get('risco_multa_visual'), $get('bloqueado'), $set);
                            })
                            ->helperText(fn (callable $get): HtmlString => self::renderPrioridadeHelper($get('prioridade')))
                            ->columnSpan(['default' => 12, 'lg' => 2]),

                        Textarea::make('descricao')
                            ->label('Descrição / Observações')
                            ->placeholder('Descreva os detalhes importantes, instruções ou observações...')
                            ->helperText('Informações adicionais que ajudam na execução.')
                            ->rows(4)
                            ->columnSpanFull(),

                        Hidden::make('tipo')
                            ->default('documento')
                            ->dehydrated(true),

                        Hidden::make('empresa_id')
                            ->default(fn (): ?int => ItemControleResource::getDefaultEmpresaIdForUser($user))
                            ->dehydrated(true),
                    ])
                    ->columns(12)
                    ->columnSpanFull(),

                            Section::make('Prazo, risco e impacto')
                    ->description('Essas informações ajudam o sistema a te alertar e priorizar o que realmente importa.')
                    ->icon('heroicon-o-shield-exclamation')
                    ->extraAttributes(['class' => 'prazzu-risk-section prazzu-left-flow-section'])
                    ->schema([
                        Select::make('urgencia')
                            ->label('Urgência')
                            ->helperText('Quão urgente é a execução?')
                            ->default('media')
                            ->options(self::getUrgenciaOptions())
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set, callable $get): void {
                                self::sincronizarRiscoOperacional($get('prioridade'), $state, $get('risco_multa_visual'), $get('bloqueado'), $set);
                            })
                            ->helperText(fn (callable $get): HtmlString => self::renderSugestaoOperacionalHelper($get))
                            ->visible(fn (): bool => DatabaseSchema::hasColumn('item_controles', 'urgencia'))
                            ->dehydrated(fn (): bool => DatabaseSchema::hasColumn('item_controles', 'urgencia'))
                            ->columnSpan(['default' => 12, 'lg' => 3]),

                        Select::make('risco_multa_visual')
                            ->label('Risco de multa')
                            ->helperText(fn (callable $get): HtmlString => self::renderRiscoHelper($get('risco_multa_visual') ?: 'alto'))
                            ->default('alto')
                            ->options(self::getRiscoMultaOptions())
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set, callable $get): void {
                                self::aplicarSugestaoPorRisco($state, $set, $get);
                                self::sincronizarRiscoOperacional($get('prioridade'), $get('urgencia'), $state, $get('bloqueado'), $set);
                            })
                            ->dehydrated(false)
                            ->columnSpan(['default' => 12, 'lg' => 3]),

                        Hidden::make('risco_score')
                            ->default(70)
                            ->dehydrated(fn (): bool => DatabaseSchema::hasColumn('item_controles', 'risco_score')),

                        TextInput::make('valor_tarefa')
                            ->label('Valor estimado da multa')
                            ->numeric()
                            ->prefix('R$')
                            ->minValue(0)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set, callable $get): void {
                                if ((float) ($state ?: 0) >= 5000 && in_array($get('risco_multa_visual'), [null, 'nenhum', 'baixo', 'medio'], true)) {
                                    $set('risco_multa_visual', 'alto');
                                }

                                self::sincronizarRiscoOperacional($get('prioridade'), $get('urgencia'), $get('risco_multa_visual'), $get('bloqueado'), $set);
                            })
                            ->helperText(fn (callable $get): HtmlString => self::renderValorMultaHelper($get('valor_tarefa')))
                            ->visible(fn (): bool => DatabaseSchema::hasColumn('item_controles', 'valor_tarefa'))
                            ->dehydrated(fn (): bool => DatabaseSchema::hasColumn('item_controles', 'valor_tarefa'))
                            ->columnSpan(['default' => 12, 'lg' => 3]),

                        Toggle::make('bloqueado')
                            ->label('Bloqueia outros processos?')
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set, callable $get): void {
                                if ($state && ! in_array($get('urgencia'), ['alta', 'critica'], true)) {
                                    $set('urgencia', 'alta');
                                }

                                self::sincronizarRiscoOperacional($get('prioridade'), $get('urgencia'), $get('risco_multa_visual'), $state, $set);
                            })
                            ->helperText(fn (callable $get): HtmlString => self::renderBloqueioHelper((bool) $get('bloqueado')))
                            ->visible(fn (): bool => DatabaseSchema::hasColumn('item_controles', 'bloqueado'))
                            ->dehydrated(fn (): bool => DatabaseSchema::hasColumn('item_controles', 'bloqueado'))
                            ->columnSpan(['default' => 12, 'lg' => 3]),

                        Placeholder::make('inteligencia_operacional')
                            ->label('Inteligência operacional')
                            ->content(fn (callable $get): HtmlString => self::renderInteligenciaOperacional($get))
                            ->columnSpanFull(),
                    ])
                    ->columns(12)
                    ->columnSpanFull(),

                            Section::make('Detalhes do contrato (opcional)')
                    ->extraAttributes(['class' => 'prazzu-contract-section prazzu-left-flow-section'])
                    ->description('Vincule a um contrato, se necessário.')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        TextInput::make('contrato_numero')
                            ->label('Número do contrato')
                            ->maxLength(100)
                            ->trim(),

                        TextInput::make('contrato_parte_nome')
                            ->label('Parte / Cliente')
                            ->maxLength(255)
                            ->trim(),

                        TextInput::make('contrato_parte_documento')
                            ->label('Documento da parte')
                            ->maxLength(100)
                            ->trim(),

                        TextInput::make('contrato_valor')
                            ->label('Valor')
                            ->numeric()
                            ->prefix('R$')
                            ->minValue(0),

                        DatePicker::make('contrato_inicio_em')
                            ->label('Início')
                            ->native(false),

                        DatePicker::make('contrato_fim_em')
                            ->label('Fim / Vencimento')
                            ->native(false),

                        Select::make('contrato_status')
                            ->label('Status do contrato')
                            ->native(false)
                            ->options(self::getContratoStatusOptions())
                            ->default('rascunho'),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed()
                    ->columnSpanFull()
                    ->visible(fn (): bool => PlanoService::usuarioPossuiFeature($user, PlanoService::FEATURE_CONTRATOS)),

                            Section::make('Checklist (opcional)')
                    ->extraAttributes(['class' => 'prazzu-checklist-section prazzu-left-flow-section'])
                    ->description('Etapas sugeridas serão carregadas automaticamente.')
                    ->icon('heroicon-o-list-bullet')
                    ->schema([
                        Repeater::make('checklists')
                            ->label('Etapas')
                            ->relationship('checklists')
                            ->schema([
                                TextInput::make('titulo')
                                    ->label('Etapa')
                                    ->required()
                                    ->maxLength(255)
                                    ->trim()
                                    ->columnSpan(8),

                                TextInput::make('ordem')
                                    ->label('Ordem')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->columnSpan(2),

                                Toggle::make('concluido')
                                    ->label('Concluído')
                                    ->default(false)
                                    ->live()
                                    ->afterStateUpdated(function ($state, callable $set): void {
                                        if ($state) {
                                            $set('concluido_em', now()->format('Y-m-d H:i:s'));
                                            $set('concluido_por', Filament::auth()->id());
                                            return;
                                        }

                                        $set('concluido_em', null);
                                        $set('concluido_por', null);
                                    })
                                    ->columnSpan(2),

                                Hidden::make('concluido_em'),
                                Hidden::make('concluido_por'),
                            ])
                            ->columns(12)
                            ->defaultItems(0)
                            ->addActionLabel('Adicionar etapa')
                            ->reorderable()
                            ->orderColumn('ordem')
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['titulo'] ?? 'Nova etapa'),
                    ])
                    ->columns(1)
                    ->collapsible()
                    ->collapsed()
                    ->columnSpanFull()
                    ->visible(fn (): bool => PlanoService::usuarioPossuiFeature($user, PlanoService::FEATURE_CHECKLIST)),

                            Section::make('Anexos (opcional)')
                    ->extraAttributes(['class' => 'prazzu-attachments-section prazzu-left-flow-section'])
                    ->description('Adicione documentos ou arquivos relacionados.')
                    ->icon('heroicon-o-paper-clip')
                    ->schema([
                        FileUpload::make('arquivo')
                            ->label('Anexo principal')
                            ->directory('comprovantes-prazos')
                            ->disk('public')
                            ->acceptedFileTypes(ItemControleAnexoUploader::ALLOWED_MIME_TYPES)
                            ->maxSize(ItemControleAnexoUploader::MAX_SIZE_KB)
                            ->downloadable()
                            ->openable()
                            ->previewable(true)
                            ->helperText('Arquivo principal do item. Envie PDF, Word, Excel, CSV, TXT ou imagem com até 10 MB. Anexos extras podem ser adicionados depois sem substituir este arquivo.'),
                    ])
                    ->columns(1)
                    ->collapsible()
                    ->collapsed()
                    ->columnSpanFull(),

                            Section::make('Configurações avançadas (opcional)')
                    ->extraAttributes(['class' => 'prazzu-advanced-section prazzu-left-flow-section'])
                    ->description('Tags, portal do cliente e outras configurações.')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->schema([
                        Select::make('status')
                            ->label('Status')
                            ->required()
                            ->default('pendente')
                            ->options(self::getStatusOptions())
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set, callable $get): void {
                                if ($state === 'concluido') {
                                    if (blank($get('data_conclusao'))) {
                                        $set('data_conclusao', now()->format('Y-m-d'));
                                    }

                                    return;
                                }

                                if ($get('data_conclusao') && $state !== 'concluido') {
                                    $set('data_conclusao', null);
                                }
                            }),

                        DatePicker::make('data_conclusao')
                            ->label('Data de conclusão')
                            ->native(false)
                            ->visible(fn (callable $get): bool => $get('status') === 'concluido'),

                        Select::make('tags')
                            ->label('Tags')
                            ->relationship('tags', 'nome')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->getSearchResultsUsing(function (string $search) use ($user): array {
                                return ItemControleTag::query()
                                    ->visibleForUser($user)
                                    ->where('ativo', true)
                                    ->where('nome', 'like', "%{$search}%")
                                    ->orderBy('nome')
                                    ->limit(50)
                                    ->pluck('nome', 'id')
                                    ->toArray();
                            })
                            ->getOptionLabelsUsing(function (array $values): array {
                                return ItemControleTag::query()
                                    ->whereIn('id', $values)
                                    ->pluck('nome', 'id')
                                    ->toArray();
                            }),

                        Textarea::make('observacao')
                            ->label('Observação interna')
                            ->rows(4)
                            ->columnSpanFull(),

                        Toggle::make('portal_ativo')
                            ->label('Ativar portal do cliente')
                            ->default(false)
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set, callable $get): void {
                                if (! $state) {
                                    return;
                                }

                                if (blank($get('portal_token'))) {
                                    $set('portal_token', Str::random(64));
                                }
                            })
                            ->columnSpanFull()
                            ->visible(fn (): bool => PlanoService::usuarioPossuiFeature($user, PlanoService::FEATURE_PORTAL_CLIENTE)),

                        TextInput::make('portal_cliente_nome')
                            ->label('Nome do cliente')
                            ->maxLength(255)
                            ->trim()
                            ->visible(fn (): bool => PlanoService::usuarioPossuiFeature($user, PlanoService::FEATURE_PORTAL_CLIENTE)),

                        TextInput::make('portal_cliente_email')
                            ->label('E-mail do cliente')
                            ->email()
                            ->maxLength(255)
                            ->trim()
                            ->visible(fn (): bool => PlanoService::usuarioPossuiFeature($user, PlanoService::FEATURE_PORTAL_CLIENTE)),

                        DateTimePicker::make('portal_expira_em')
                            ->label('Link expira em')
                            ->native(false)
                            ->seconds(false)
                            ->helperText('Deixe vazio para não expirar.')
                            ->visible(fn (): bool => PlanoService::usuarioPossuiFeature($user, PlanoService::FEATURE_PORTAL_CLIENTE)),

                        Hidden::make('portal_token')
                            ->default(null),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed()
                    ->columnSpanFull()
                        ])
                            ->extraAttributes(['class' => 'prazzu-left-column'])
                            ->columns(1)
                            ->columnSpan(['default' => 12, 'lg' => 8]),

                        Group::make([
                            Section::make('Resumo do item')
                    ->extraAttributes(['class' => 'prazzu-summary-section'])
                    ->description('Veja como seu item será registrado.')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->schema([
                        Placeholder::make('resumo_titulo')
                            ->label('Título')
                            ->content(fn (callable $get): string => filled($get('titulo')) ? (string) $get('titulo') : '-'),

                        Placeholder::make('resumo_categoria')
                            ->label('Categoria')
                            ->content(function (callable $get): string {
                                $categoriaId = $get('categoria_id');

                                if (! filled($categoriaId)) {
                                    return '-';
                                }

                                return (string) (CategoriaItemControle::query()->whereKey($categoriaId)->value('nome') ?: '-');
                            }),

                        Placeholder::make('resumo_responsavel')
                            ->label('Responsável')
                            ->content(function (callable $get): string {
                                $responsavelId = $get('responsavel_id');

                                if (! filled($responsavelId)) {
                                    return '-';
                                }

                                return (string) (Responsavel::query()->whereKey($responsavelId)->value('nome') ?: '-');
                            }),

                        Placeholder::make('resumo_vencimento')
                            ->label('Vencimento')
                            ->content(fn (callable $get): string => filled($get('data_vencimento')) ? date('d/m/Y', strtotime((string) $get('data_vencimento'))) : '-'),

                        Placeholder::make('resumo_prioridade')
                            ->label('Prioridade')
                            ->content(fn (callable $get): HtmlString => self::renderBadgeResumo($get('prioridade') ?: 'media', self::getPrioridadeOptions()[$get('prioridade') ?: 'media'] ?? 'Média', 'prioridade')),

                        Placeholder::make('resumo_urgencia')
                            ->label('Urgência')
                            ->content(fn (callable $get): HtmlString => self::renderBadgeResumo($get('urgencia') ?: 'media', self::getUrgenciaOptions()[$get('urgencia') ?: 'media'] ?? 'Média', 'urgencia')),

                        Placeholder::make('resumo_risco')
                            ->label('Risco de multa')
                            ->content(fn (callable $get): HtmlString => self::renderBadgeResumo($get('risco_multa_visual') ?: 'alto', self::getRiscoMultaOptions()[$get('risco_multa_visual') ?: 'alto'] ?? 'Alto', 'risco')),

                        Placeholder::make('resumo_valor')
                            ->label('Valor estimado')
                            ->content(fn (callable $get): HtmlString => self::renderValorResumo($get('valor_tarefa'))),

                        Placeholder::make('resumo_bloqueio')
                            ->label('Bloqueia processos')
                            ->content(fn (callable $get): HtmlString => self::renderBloqueioResumo((bool) $get('bloqueado'))),

                        Placeholder::make('resumo_status')
                            ->label('Status')
                            ->content(fn (callable $get): HtmlString => new HtmlString('<span class="pz-status-pill">' . e(self::getStatusOptions()[$get('status') ?: 'pendente'] ?? 'Pendente') . '</span><small>Status padrão na criação.</small>')),
                    ])
                    ->columns(1)
                    ->columnSpanFull(),

                            Section::make('Dica inteligente')
                    ->extraAttributes(['class' => 'prazzu-tip-section'])
                    ->description('Orientação automática conforme prazo, prioridade e risco.')
                    ->icon('heroicon-o-light-bulb')
                    ->schema([
                        Placeholder::make('dica_inteligente')
                            ->label('')
                            ->content(fn (callable $get): HtmlString => self::renderDicaInteligente($get)),
                    ])
                    ->columnSpanFull(),

                            Section::make('Dicas rápidas')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->extraAttributes(['class' => 'prazzu-quick-tips-section prazzu-sidebar-flow-section'])
                    ->schema([
                        Placeholder::make('dicas_rapidas')
                            ->label('')
                            ->content(fn (): HtmlString => new HtmlString('<ul class="prazzu-quick-tips"><li>Use um título claro e objetivo</li><li>Defina a data de vencimento corretamente</li><li>Priorize itens com maior risco de multa</li><li>Checklist será sugerido automaticamente</li><li>Você pode editar tudo depois</li></ul>')),
                    ])
                    ->columnSpanFull()
                        ])
                            ->extraAttributes(['class' => 'prazzu-sidebar-column'])
                            ->columns(1)
                            ->columnSpan(['default' => 12, 'lg' => 4]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    protected static function deveMostrarContrato(callable $get): bool
    {
        if ($get('tipo') === 'contrato') {
            return true;
        }

        $categoriaId = $get('categoria_id');

        if (! filled($categoriaId)) {
            return false;
        }

        $nome = CategoriaItemControle::query()
            ->whereKey($categoriaId)
            ->value('nome');

        return mb_strtolower((string) $nome) === 'contrato';
    }

    protected static function getEmpresaSearchResults(string $search, $user): array
    {
        if (! $user) {
            return [];
        }

        $query = Empresa::query()
            ->select(['id', 'razao_social'])
            ->where(function ($builder) use ($search): void {
                $builder->where('razao_social', 'like', "%{$search}%")
                    ->orWhere('nome_fantasia', 'like', "%{$search}%")
                    ->orWhere('cnpj', 'like', "%{$search}%");
            });

        if ($user->isSuperAdmin()) {
            return $query
                ->orderBy('razao_social')
                ->limit(50)
                ->pluck('razao_social', 'id')
                ->toArray();
        }

        if (! $user->hasEmpresaVinculada()) {
            return [];
        }

        return $query
            ->whereKey($user->empresa_id)
            ->limit(1)
            ->pluck('razao_social', 'id')
            ->toArray();
    }

    protected static function getResponsavelSearchResults(
        string $search,
               $user,
        ?int $empresaId = null
    ): array {
        if (! $user) {
            return [];
        }

        $query = Responsavel::query()
            ->select(['id', 'nome'])
            ->whereNotNull('user_id')
            ->where(function ($builder) use ($search): void {
                $builder->where('nome', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('cargo', 'like', "%{$search}%");
            });

        if ($user->isSuperAdmin()) {
            if ($empresaId) {
                $query->where('empresa_id', $empresaId);
            }

            return $query
                ->orderBy('nome')
                ->limit(50)
                ->pluck('nome', 'id')
                ->toArray();
        }

        if (! $user->hasEmpresaVinculada()) {
            return [];
        }

        $query->where('empresa_id', $user->empresa_id);

        if ($user->isAdminEmpresa()) {
            return $query
                ->orderBy('nome')
                ->limit(50)
                ->pluck('nome', 'id')
                ->toArray();
        }

        if ($user->isGestor()) {
            return $query
                ->where('gestor_user_id', $user->id)
                ->orderBy('nome')
                ->limit(50)
                ->pluck('nome', 'id')
                ->toArray();
        }

        $responsavelIdDoUsuario = $user->responsavel?->id;

        if (! $responsavelIdDoUsuario) {
            return [];
        }

        return $query
            ->whereKey($responsavelIdDoUsuario)
            ->limit(1)
            ->pluck('nome', 'id')
            ->toArray();
    }


    protected static function aplicarSugestaoOperacionalPorCategoria(string $categoriaNome, callable $set, callable $get): void
    {
        $nome = Str::lower($categoriaNome);
        $defaults = [
            'risco' => 'medio',
            'urgencia' => 'media',
            'prioridade' => $get('prioridade') ?: 'media',
        ];

        if (Str::contains($nome, ['fiscal', 'tribut', 'imposto', 'dctf', 'sped', 'sefip', 'fgts', 'esocial'])) {
            $defaults = ['risco' => 'alto', 'urgencia' => 'alta', 'prioridade' => 'alta'];
        } elseif (Str::contains($nome, ['contrato', 'jurid', 'licença', 'licenca', 'certidão', 'certidao'])) {
            $defaults = ['risco' => 'alto', 'urgencia' => 'media', 'prioridade' => 'alta'];
        } elseif (Str::contains($nome, ['financeiro', 'boleto', 'pagamento', 'cobrança', 'cobranca'])) {
            $defaults = ['risco' => 'medio', 'urgencia' => 'media', 'prioridade' => 'media'];
        } elseif (Str::contains($nome, ['rh', 'folha', 'admiss', 'rescis', 'ponto'])) {
            $defaults = ['risco' => 'medio', 'urgencia' => 'media', 'prioridade' => 'media'];
        }

        if (blank($get('risco_multa_visual')) || in_array($get('risco_multa_visual'), ['nenhum', 'baixo', 'medio'], true)) {
            $set('risco_multa_visual', $defaults['risco']);
        }

        if (blank($get('urgencia')) || $get('urgencia') === 'media') {
            $set('urgencia', $defaults['urgencia']);
        }

        if (blank($get('prioridade')) || $get('prioridade') === 'media') {
            $set('prioridade', $defaults['prioridade']);
        }

        self::sincronizarRiscoOperacional($get('prioridade') ?: $defaults['prioridade'], $get('urgencia') ?: $defaults['urgencia'], $get('risco_multa_visual') ?: $defaults['risco'], $get('bloqueado'), $set);
    }

    protected static function aplicarSugestaoPorRisco($risco, callable $set, callable $get): void
    {
        $risco = (string) ($risco ?: 'medio');

        if ($risco === 'critico') {
            if (! in_array($get('urgencia'), ['alta', 'critica'], true)) {
                $set('urgencia', 'critica');
            }

            if (! in_array($get('prioridade'), ['alta', 'urgente'], true)) {
                $set('prioridade', 'urgente');
            }

            return;
        }

        if ($risco === 'alto') {
            if (! in_array($get('urgencia'), ['alta', 'critica'], true)) {
                $set('urgencia', 'alta');
            }

            if (! in_array($get('prioridade'), ['alta', 'urgente'], true)) {
                $set('prioridade', 'alta');
            }
        }
    }

    protected static function sincronizarRiscoOperacional($prioridade, $urgencia, $risco, $bloqueado, callable $set): void
    {
        $score = self::calcularRiscoScore($prioridade, $urgencia, $risco, (bool) $bloqueado);
        $set('risco_score', $score);
    }

    protected static function calcularRiscoScore($prioridade, $urgencia, $risco, bool $bloqueado = false): int
    {
        $score = 0;

        $score += match ((string) ($prioridade ?: 'media')) {
            'baixa' => 10,
            'alta' => 30,
            'urgente' => 40,
            default => 20,
        };

        $score += match ((string) ($urgencia ?: 'media')) {
            'baixa' => 10,
            'alta' => 25,
            'critica' => 35,
            default => 18,
        };

        $score += match ((string) ($risco ?: 'medio')) {
            'nenhum' => 0,
            'baixo' => 10,
            'alto' => 30,
            'critico' => 40,
            default => 20,
        };

        if ($bloqueado) {
            $score += 15;
        }

        return min(100, max(0, $score));
    }

    protected static function renderSugestaoOperacionalHelper(callable $get): HtmlString
    {
        $risco = (string) ($get('risco_multa_visual') ?: 'medio');
        $bloqueado = (bool) $get('bloqueado');

        if ($bloqueado) {
            return new HtmlString('<span class="pz-operational-helper pz-operational-helper-danger">Processos bloqueados elevam a urgência automaticamente.</span>');
        }

        if (in_array($risco, ['alto', 'critico'], true)) {
            return new HtmlString('<span class="pz-operational-helper pz-operational-helper-warning">Risco alto pede acompanhamento mais próximo.</span>');
        }

        return new HtmlString('<span class="pz-operational-helper">Define posição na fila e alertas internos.</span>');
    }

    protected static function renderRiscoHelper(string $risco): HtmlString
    {
        $score = self::calcularRiscoScore(null, null, $risco);
        $classe = in_array($risco, ['alto', 'critico'], true) ? 'danger' : (in_array($risco, ['medio'], true) ? 'warning' : 'success');

        return new HtmlString('<span class="pz-operational-helper pz-operational-helper-' . e($classe) . '">Impacta alertas, prioridade no dashboard e score operacional base: ' . e((string) $score) . '.</span>');
    }

    protected static function renderValorMultaHelper($valor): HtmlString
    {
        $valorFloat = (float) ($valor ?: 0);

        if ($valorFloat <= 0) {
            return new HtmlString('<span class="pz-operational-helper">Opcional. Use quando houver risco financeiro estimado.</span>');
        }

        $classe = $valorFloat >= 5000 ? 'danger' : 'warning';

        return new HtmlString('<span class="pz-operational-helper pz-operational-helper-' . e($classe) . '">Peso financeiro informado: R$ ' . e(number_format($valorFloat, 2, ',', '.')) . '.</span>');
    }

    protected static function renderBloqueioHelper(bool $bloqueado): HtmlString
    {
        if ($bloqueado) {
            return new HtmlString('<span class="pz-operational-helper pz-operational-helper-danger">SIM: este item será tratado como dependência crítica.</span>');
        }

        return new HtmlString('<span class="pz-operational-helper">NÃO: este item não bloqueia outros fluxos.</span>');
    }

    protected static function renderInteligenciaOperacional(callable $get): HtmlString
    {
        $score = self::calcularRiscoScore($get('prioridade'), $get('urgencia'), $get('risco_multa_visual'), (bool) $get('bloqueado'));
        $valor = (float) ($get('valor_tarefa') ?: 0);
        $classe = $score >= 80 ? 'danger' : ($score >= 55 ? 'warning' : 'success');
        $titulo = $score >= 80 ? 'Atenção máxima recomendada' : ($score >= 55 ? 'Acompanhamento recomendado' : 'Risco operacional controlado');
        $mensagem = $score >= 80
            ? 'Este item combina risco, urgência ou dependência. Priorize a execução e acompanhe no dashboard.'
            : ($score >= 55
                ? 'O item merece acompanhamento, principalmente se houver dependências ou valor financeiro envolvido.'
                : 'O item pode seguir o fluxo normal, mantendo responsável e vencimento claros.');

        $valorHtml = $valor > 0
            ? '<span class="pz-operational-card-value">Impacto financeiro: R$ ' . e(number_format($valor, 2, ',', '.')) . '</span>'
            : '<span class="pz-operational-card-value">Sem valor financeiro informado.</span>';

        return new HtmlString('<div class="pz-operational-card pz-operational-card-' . e($classe) . '"><div><strong>' . e($titulo) . '</strong><span>Score operacional: ' . e((string) $score) . '/100</span></div><p>' . e($mensagem) . '</p>' . $valorHtml . '</div>');
    }

    protected static function renderValorResumo($valor): HtmlString
    {
        $valorFloat = (float) ($valor ?: 0);

        if ($valorFloat <= 0) {
            return new HtmlString('<span class="pz-muted-summary">Não informado</span>');
        }

        $classe = $valorFloat >= 5000 ? 'critico' : 'alto';

        return new HtmlString('<span class="pz-smart-badge pz-smart-badge-' . e($classe) . '">R$ ' . e(number_format($valorFloat, 2, ',', '.')) . '</span>');
    }

    protected static function renderBloqueioResumo(bool $bloqueado): HtmlString
    {
        return new HtmlString($bloqueado
            ? '<span class="pz-smart-badge pz-smart-badge-critico">Sim</span>'
            : '<span class="pz-smart-badge pz-smart-badge-nenhum">Não</span>');
    }


    protected static function renderPrazoVisual($dataVencimento): HtmlString
    {
        if (! filled($dataVencimento)) {
            return new HtmlString('<span class="pz-deadline-hint pz-deadline-muted">📅 Selecione a data para visualizar o prazo.</span>');
        }

        try {
            $data = \Carbon\Carbon::parse((string) $dataVencimento)->startOfDay();
            $hoje = now()->startOfDay();
            $dias = $hoje->diffInDays($data, false);
        } catch (\Throwable) {
            return new HtmlString('<span class="pz-deadline-hint pz-deadline-muted">📅 Data selecionada.</span>');
        }

        if ($dias < 0) {
            $texto = '🚨 Prazo vencido há ' . abs((int) $dias) . ' dia' . (abs((int) $dias) === 1 ? '' : 's');
            $classe = 'danger';
        } elseif ($dias === 0) {
            $texto = '🚨 Vence hoje — prazo crítico';
            $classe = 'danger';
        } elseif ($dias === 1) {
            $texto = '⚠️ Vence amanhã';
            $classe = 'warning';
        } elseif ($dias <= 7) {
            $texto = '⚠️ Faltam ' . (int) $dias . ' dias — atenção ao prazo';
            $classe = 'warning';
        } else {
            $texto = '📅 Faltam ' . (int) $dias . ' dias';
            $classe = 'success';
        }

        return new HtmlString('<span class="pz-deadline-hint pz-deadline-' . e($classe) . '">' . e($texto) . '</span>');
    }

    protected static function renderPrioridadeHelper($prioridade): HtmlString
    {
        $prioridade = $prioridade ?: 'media';
        $labels = self::getPrioridadeOptions();

        return self::renderBadgeResumo($prioridade, $labels[$prioridade] ?? 'Média', 'prioridade');
    }

    protected static function renderBadgeResumo(string $valor, string $label, string $tipo): HtmlString
    {
        $valorSeguro = preg_replace('/[^a-z0-9_-]/i', '', $valor) ?: 'neutro';

        return new HtmlString('<span class="pz-smart-badge pz-smart-badge-' . e($tipo) . ' pz-smart-badge-' . e($valorSeguro) . '">' . e($label) . '</span>');
    }

    protected static function renderDicaInteligente(callable $get): HtmlString
    {
        $risco = (string) ($get('risco_multa_visual') ?: 'alto');
        $prioridade = (string) ($get('prioridade') ?: 'media');
        $urgencia = (string) ($get('urgencia') ?: 'media');
        $bloqueado = (bool) $get('bloqueado');
        $valor = (float) ($get('valor_tarefa') ?: 0);
        $dataVencimento = $get('data_vencimento');

        $dias = null;

        if (filled($dataVencimento)) {
            try {
                $dias = now()->startOfDay()->diffInDays(\Carbon\Carbon::parse((string) $dataVencimento)->startOfDay(), false);
            } catch (\Throwable) {
                $dias = null;
            }
        }

        if ($bloqueado) {
            return new HtmlString('<div class="pz-smart-tip pz-smart-tip-danger"><strong>Dependência crítica.</strong><span>Como este item bloqueia outros processos, o sistema elevou a atenção operacional para evitar efeito cascata.</span></div>');
        }

        if ($valor >= 5000) {
            return new HtmlString('<div class="pz-smart-tip pz-smart-tip-danger"><strong>Impacto financeiro relevante.</strong><span>Existe valor de multa estimado. Considere acompanhar antes do vencimento e manter evidências anexadas.</span></div>');
        }

        if (in_array($risco, ['alto', 'critico'], true)) {
            return new HtmlString('<div class="pz-smart-tip pz-smart-tip-danger"><strong>Risco importante de multa.</strong><span>Considere definir uma data interna anterior ao vencimento e acompanhar este item com prioridade.</span></div>');
        }

        if ($dias !== null && $dias <= 1) {
            return new HtmlString('<div class="pz-smart-tip pz-smart-tip-danger"><strong>Prazo crítico.</strong><span>Este item vence hoje ou amanhã. Vale revisar responsável, anexos e checklist antes de salvar.</span></div>');
        }

        if ($dias !== null && $dias <= 7) {
            return new HtmlString('<div class="pz-smart-tip pz-smart-tip-warning"><strong>Prazo curto.</strong><span>Faltam poucos dias. Se houver dependências, mantenha a urgência alta.</span></div>');
        }

        if (in_array($prioridade, ['alta', 'urgente'], true) || in_array($urgencia, ['alta', 'critica'], true)) {
            return new HtmlString('<div class="pz-smart-tip pz-smart-tip-info"><strong>Item sensível.</strong><span>Boa escolha destacar prioridade e urgência. O resumo ao lado ajuda a validar antes de salvar.</span></div>');
        }

        return new HtmlString('<div class="pz-smart-tip pz-smart-tip-success"><strong>Cadastro objetivo.</strong><span>Após salvar, você poderá complementar com checklist, anexos e detalhes adicionais.</span></div>');
    }

    protected static function getRiscoMultaOptions(): array
    {
        return [
            'nenhum' => '⚪ Nenhum',
            'baixo' => '🟢 Baixo',
            'medio' => '🟡 Médio',
            'alto' => '🟠 Alto',
            'critico' => '🔴 Crítico',
        ];
    }

    protected static function getStatusOptions(): array
    {
        return [
            'pendente' => 'Pendente',
            'em_andamento' => 'Em andamento',
            'pronto' => 'Pronto para revisão',
            'em_revisao' => 'Em Revisão',
            'aguardando_aprovacao' => 'Aguardando Aprovação',
            'em_aprovacao' => 'Em aprovação',
            'correcao_necessaria' => 'Correção Necessária',
            'aprovado' => 'Aprovado',
            'reprovado' => 'Reprovado',
            'assinado' => 'Assinado',
            'concluido' => 'Concluído',
            'cancelado' => 'Cancelado',
            'vencido' => 'Vencido',
        ];
    }

    protected static function getPrioridadeOptions(): array
    {
        return [
            'baixa' => '🟢 Baixa',
            'media' => '🟡 Média',
            'alta' => '🟠 Alta',
            'urgente' => '🔴 Crítica',
        ];
    }

    protected static function getUrgenciaOptions(): array
    {
        return [
            'baixa' => '🟢 Baixa',
            'media' => '🟡 Média',
            'alta' => '🟠 Alta',
            'critica' => '🔴 Crítica',
        ];
    }

    protected static function getContratoStatusOptions(): array
    {
        return [
            'rascunho' => 'Rascunho',
            'vigente' => 'Vigente',
            'vencendo' => 'Vencendo',
            'vencido' => 'Vencido',
            'encerrado' => 'Encerrado',
            'faturado' => 'Faturado',
            'pago' => 'Pago',
            'cancelado' => 'Cancelado',
        ];
    }
}
