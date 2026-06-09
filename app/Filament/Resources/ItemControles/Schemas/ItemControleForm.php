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

                        Placeholder::make('vencimento_operacional_destaque')
                            ->label('Leitura operacional do prazo')
                            ->content(fn (callable $get): HtmlString => self::renderVencimentoOperacionalDestaque($get))
                            ->columnSpan(['default' => 12, 'lg' => 8]),

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

                        Placeholder::make('alertas_criticos_lote_3')
                            ->label('Alertas críticos')
                            ->content(fn (callable $get): HtmlString => self::renderAlertasCriticosLote3($get))
                            ->columnSpanFull(),


                        Placeholder::make('prevencao_operacional_lote_4')
                            ->label('Prevenção operacional')
                            ->content(fn (callable $get): HtmlString => self::renderPrevencaoOperacionalLote4($get))
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

                            Section::make('Checklist de execução')
                    ->extraAttributes(['class' => 'prazzu-checklist-section prazzu-left-flow-section'])
                    ->description('Etapas de segurança para reduzir erro operacional, retrabalho e esquecimento.')
                    ->icon('heroicon-o-list-bullet')
                    ->schema([
                        Placeholder::make('checklist_automatico_preview')
                            ->label('Checklist automático')
                            ->content(fn (callable $get): HtmlString => self::renderChecklistAutomaticoPreview($get))
                            ->columnSpanFull(),

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
                    ->collapsed(false)
                    ->columnSpanFull()
                    ->visible(fn (): bool => PlanoService::usuarioPossuiFeature($user, PlanoService::FEATURE_CHECKLIST)),

                            Section::make('Evidências e anexos')
                    ->extraAttributes(['class' => 'prazzu-attachments-section prazzu-left-flow-section'])
                    ->description('Guarde comprovantes, documentos e arquivos que sustentam a execução do item.')
                    ->icon('heroicon-o-paper-clip')
                    ->schema([
                        Placeholder::make('anexos_dropzone_preview')
                            ->label('')
                            ->content(fn (): HtmlString => self::renderAnexosDropzonePreview())
                            ->columnSpanFull(),

                        FileUpload::make('arquivo')
                            ->label('Evidência principal')
                            ->directory('comprovantes-prazos')
                            ->disk('public')
                            ->acceptedFileTypes(ItemControleAnexoUploader::ALLOWED_MIME_TYPES)
                            ->maxSize(ItemControleAnexoUploader::MAX_SIZE_KB)
                            ->downloadable()
                            ->openable()
                            ->previewable(true)
                            ->helperText(new HtmlString('<strong>Arraste a evidência aqui</strong> ou clique para selecionar. PDF, Word, Excel, CSV, TXT e imagens com até 10 MB. Evidências extras podem ser adicionadas depois sem substituir este arquivo.')),
                    ])
                    ->columns(1)
                    ->collapsible()
                    ->collapsed(false)
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
                            Section::make('Painel operacional')
                    ->extraAttributes(['class' => 'prazzu-operational-panel-section prazzu-sidebar-flow-section'])
                    ->description('Resumo único de segurança, risco, qualidade e próximos cuidados antes de salvar.')
                    ->icon('heroicon-o-shield-check')
                    ->schema([
                        Placeholder::make('painel_operacional_lote_2')
                            ->label('')
                            ->content(fn (callable $get): HtmlString => self::renderPainelOperacionalLote2($get)),
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

    protected static function calcularDiasAteVencimento($dataVencimento): ?int
    {
        if (! filled($dataVencimento)) {
            return null;
        }

        try {
            $data = \Carbon\Carbon::parse((string) $dataVencimento)->startOfDay();
            return (int) now()->startOfDay()->diffInDays($data, false);
        } catch (\Throwable) {
            return null;
        }
    }

    protected static function calcularRiscoOperacionalVisual(callable $get): array
    {
        $scoreBase = self::calcularRiscoScore($get('prioridade'), $get('urgencia'), $get('risco_multa_visual'), (bool) $get('bloqueado'));
        $dias = self::calcularDiasAteVencimento($get('data_vencimento'));
        $valor = (float) ($get('valor_tarefa') ?: 0);

        $score = $scoreBase;

        if ($dias !== null) {
            if ($dias < 0) {
                $score += 35;
            } elseif ($dias <= 1) {
                $score += 30;
            } elseif ($dias <= 3) {
                $score += 22;
            } elseif ($dias <= 7) {
                $score += 12;
            }
        }

        if ($valor >= 5000) {
            $score += 10;
        }

        $score = min(100, max(0, $score));

        if ($score >= 85) {
            return [
                'score' => $score,
                'nivel' => 'Crítico',
                'classe' => 'danger',
                'icone' => '🚨',
                'mensagem' => 'Risco alto de atraso, multa, bloqueio ou retrabalho. Revise responsável, prazo e evidências antes de salvar.',
            ];
        }

        if ($score >= 65) {
            return [
                'score' => $score,
                'nivel' => 'Alto',
                'classe' => 'warning',
                'icone' => '⚠️',
                'mensagem' => 'Exige acompanhamento próximo para evitar falha operacional.',
            ];
        }

        if ($score >= 40) {
            return [
                'score' => $score,
                'nivel' => 'Moderado',
                'classe' => 'info',
                'icone' => '🟡',
                'mensagem' => 'Controle necessário, mas sem sinal crítico neste momento.',
            ];
        }

        return [
            'score' => $score,
            'nivel' => 'Baixo',
            'classe' => 'success',
            'icone' => '🟢',
            'mensagem' => 'Item com risco controlado, desde que prazo e responsável estejam corretos.',
        ];
    }

    protected static function renderVencimentoOperacionalDestaque(callable $get): HtmlString
    {
        $dias = self::calcularDiasAteVencimento($get('data_vencimento'));

        if ($dias === null) {
            return new HtmlString('<div style="border:1px solid #e5e7eb;border-radius:16px;padding:14px 16px;background:#f9fafb;"><div style="font-size:12px;color:#6b7280;font-weight:700;text-transform:uppercase;letter-spacing:.04em;">Prazo ainda não definido</div><div style="font-size:18px;font-weight:800;color:#111827;margin-top:4px;">Selecione o vencimento</div><div style="font-size:13px;color:#6b7280;margin-top:4px;">O prazo é o principal dado para prevenir atraso, multa e retrabalho.</div></div>');
        }

        if ($dias < 0) {
            $titulo = 'Prazo vencido';
            $texto = 'Vencido há ' . abs($dias) . ' dia' . (abs($dias) === 1 ? '' : 's');
            $cor = '#991b1b';
            $bg = '#fef2f2';
            $border = '#fecaca';
            $status = 'Ação imediata recomendada';
            $icone = '🚨';
        } elseif ($dias === 0) {
            $titulo = 'Vence hoje';
            $texto = 'Prazo crítico';
            $cor = '#991b1b';
            $bg = '#fef2f2';
            $border = '#fecaca';
            $status = 'Priorizar ainda hoje';
            $icone = '🚨';
        } elseif ($dias === 1) {
            $titulo = 'Vence amanhã';
            $texto = 'Falta 1 dia';
            $cor = '#92400e';
            $bg = '#fffbeb';
            $border = '#fde68a';
            $status = 'Atenção ao prazo';
            $icone = '⚠️';
        } elseif ($dias <= 7) {
            $titulo = 'Prazo próximo';
            $texto = 'Faltam ' . $dias . ' dias';
            $cor = '#92400e';
            $bg = '#fffbeb';
            $border = '#fde68a';
            $status = 'Acompanhar de perto';
            $icone = '⚠️';
        } else {
            $titulo = 'Prazo confortável';
            $texto = 'Faltam ' . $dias . ' dias';
            $cor = '#166534';
            $bg = '#f0fdf4';
            $border = '#bbf7d0';
            $status = 'Controle preventivo ativo';
            $icone = '✅';
        }

        return new HtmlString('<div style="border:1px solid ' . e($border) . ';border-radius:16px;padding:14px 16px;background:' . e($bg) . ';"><div style="display:flex;align-items:center;justify-content:space-between;gap:12px;"><div><div style="font-size:12px;color:' . e($cor) . ';font-weight:800;text-transform:uppercase;letter-spacing:.04em;">' . e($icone . ' ' . $titulo) . '</div><div style="font-size:22px;font-weight:900;color:' . e($cor) . ';margin-top:4px;">' . e($texto) . '</div></div><span style="font-size:12px;font-weight:800;color:' . e($cor) . ';background:#ffffff;border:1px solid ' . e($border) . ';border-radius:999px;padding:6px 10px;white-space:nowrap;">' . e($status) . '</span></div><div style="font-size:13px;color:#374151;margin-top:8px;">Este prazo será usado para priorizar alertas, risco operacional e acompanhamento do item.</div></div>');
    }

    protected static function renderAlertasCriticosLote3(callable $get): HtmlString
    {
        $risco = self::calcularRiscoOperacionalVisual($get);
        $dias = self::calcularDiasAteVencimento($get('data_vencimento'));
        $valor = (float) ($get('valor_tarefa') ?: 0);
        $bloqueado = (bool) $get('bloqueado');
        $checklists = $get('checklists');
        $totalChecklist = is_array($checklists) ? count($checklists) : 0;
        $arquivo = $get('arquivo');
        $temEvidencia = is_array($arquivo) ? count($arquivo) > 0 : filled($arquivo);

        $alertas = [];

        if (! filled($get('data_vencimento'))) {
            $alertas[] = [
                'nivel' => 'Crítico',
                'icone' => '🚨',
                'titulo' => 'Prazo não definido',
                'texto' => 'Sem vencimento, o sistema não consegue priorizar alertas nem medir risco de atraso.',
                'classe' => 'danger',
            ];
        } elseif ($dias !== null && $dias < 0) {
            $alertas[] = [
                'nivel' => 'Crítico',
                'icone' => '🚨',
                'titulo' => 'Item já vencido',
                'texto' => 'Revise responsável, checklist e evidências antes de salvar para evitar retrabalho.',
                'classe' => 'danger',
            ];
        } elseif ($dias !== null && $dias <= 1) {
            $alertas[] = [
                'nivel' => 'Crítico',
                'icone' => '🚨',
                'titulo' => 'Prazo imediato',
                'texto' => 'Vence hoje ou amanhã. Este item precisa sair do cadastro já com execução bem definida.',
                'classe' => 'danger',
            ];
        } elseif ($dias !== null && $dias <= 7) {
            $alertas[] = [
                'nivel' => 'Atenção',
                'icone' => '⚠',
                'titulo' => 'Prazo curto',
                'texto' => 'Faltam poucos dias. Use checklist e evidências para diminuir risco operacional.',
                'classe' => 'warning',
            ];
        }

        if (in_array($risco['classe'], ['danger', 'warning'], true)) {
            $alertas[] = [
                'nivel' => $risco['classe'] === 'danger' ? 'Crítico' : 'Atenção',
                'icone' => $risco['classe'] === 'danger' ? '🚨' : '⚠',
                'titulo' => 'Risco operacional ' . mb_strtolower((string) $risco['nivel']),
                'texto' => 'Confirme impacto, responsável e próximos passos antes de criar este controle.',
                'classe' => $risco['classe'],
            ];
        }

        if ($valor > 0 && ! $temEvidencia) {
            $alertas[] = [
                'nivel' => 'Atenção',
                'icone' => '⚠',
                'titulo' => 'Há valor financeiro sem evidência',
                'texto' => 'Para itens com multa ou impacto financeiro, anexar evidência inicial aumenta segurança e rastreabilidade.',
                'classe' => 'warning',
            ];
        }

        if ($bloqueado && $totalChecklist === 0) {
            $alertas[] = [
                'nivel' => 'Atenção',
                'icone' => '⚠',
                'titulo' => 'Item bloqueador sem checklist',
                'texto' => 'Como este item bloqueia outros processos, vale criar etapas mínimas de execução.',
                'classe' => 'warning',
            ];
        }

        if (count($alertas) === 0) {
            $alertas[] = [
                'nivel' => 'Informativo',
                'icone' => 'ℹ',
                'titulo' => 'Nenhum alerta crítico no momento',
                'texto' => 'Os principais dados estão em condição segura. Continue revisando prazo, responsável e evidências.',
                'classe' => 'info',
            ];
        }

        $html = '<div style="display:grid;gap:10px;">';

        foreach (array_slice($alertas, 0, 3) as $alerta) {
            $cor = match ($alerta['classe']) {
                'danger' => '#991b1b',
                'warning' => '#92400e',
                default => '#1d4ed8',
            };
            $bg = match ($alerta['classe']) {
                'danger' => '#fef2f2',
                'warning' => '#fffbeb',
                default => '#eff6ff',
            };
            $border = match ($alerta['classe']) {
                'danger' => '#fecaca',
                'warning' => '#fde68a',
                default => '#bfdbfe',
            };

            $html .= '<div style="border:1px solid ' . e($border) . ';border-radius:16px;padding:12px 14px;background:' . e($bg) . ';">';
            $html .= '<div style="display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:5px;">';
            $html .= '<strong style="font-size:13px;color:' . e($cor) . ';">' . e($alerta['icone'] . ' ' . $alerta['titulo']) . '</strong>';
            $html .= '<span style="font-size:11px;font-weight:900;text-transform:uppercase;letter-spacing:.04em;color:' . e($cor) . ';background:#ffffff;border:1px solid ' . e($border) . ';border-radius:999px;padding:4px 8px;white-space:nowrap;">' . e($alerta['nivel']) . '</span>';
            $html .= '</div>';
            $html .= '<div style="font-size:13px;color:#374151;line-height:1.45;">' . e($alerta['texto']) . '</div>';
            $html .= '</div>';
        }

        $html .= '</div>';

        return new HtmlString($html);
    }

    protected static function avaliarImpactoOperacionalLote4(callable $get): array
    {
        $risco = self::calcularRiscoOperacionalVisual($get);
        $dias = self::calcularDiasAteVencimento($get('data_vencimento'));
        $valor = (float) ($get('valor_tarefa') ?: 0);
        $bloqueado = (bool) $get('bloqueado');
        $categoriaNome = '';

        if (filled($get('categoria_id'))) {
            $categoriaNome = (string) (CategoriaItemControle::query()->whereKey($get('categoria_id'))->value('nome') ?: '');
        }

        $categoriaNormalizada = Str::lower($categoriaNome);
        $fatores = [];
        $pontos = 0;

        if ($dias === null) {
            $fatores[] = 'prazo ainda não definido';
            $pontos += 25;
        } elseif ($dias < 0) {
            $fatores[] = 'item já vencido';
            $pontos += 45;
        } elseif ($dias <= 1) {
            $fatores[] = 'prazo imediato';
            $pontos += 38;
        } elseif ($dias <= 3) {
            $fatores[] = 'prazo muito curto';
            $pontos += 30;
        } elseif ($dias <= 7) {
            $fatores[] = 'prazo próximo';
            $pontos += 18;
        }

        if ($valor >= 5000) {
            $fatores[] = 'impacto financeiro relevante';
            $pontos += 25;
        } elseif ($valor > 0) {
            $fatores[] = 'multa/valor financeiro informado';
            $pontos += 14;
        }

        if ($bloqueado) {
            $fatores[] = 'bloqueia outros processos';
            $pontos += 25;
        }

        if (Str::contains($categoriaNormalizada, ['fiscal', 'tribut', 'imposto', 'dctf', 'sped', 'sefip', 'fgts', 'esocial'])) {
            $fatores[] = 'categoria fiscal/obrigação sensível';
            $pontos += 20;
        } elseif (Str::contains($categoriaNormalizada, ['contrato', 'jurid', 'licença', 'licenca', 'certidão', 'certidao'])) {
            $fatores[] = 'categoria contratual/jurídica';
            $pontos += 16;
        }

        if (in_array($risco['classe'], ['danger', 'warning'], true)) {
            $pontos += $risco['classe'] === 'danger' ? 25 : 12;
        }

        $pontos = min(100, max(0, $pontos));

        if ($pontos >= 80) {
            return [
                'nivel' => 'Crítico',
                'classe' => 'danger',
                'icone' => '🚨',
                'titulo' => 'Impacto operacional crítico',
                'mensagem' => 'Se este item atrasar, há risco real de multa, bloqueio, retrabalho ou cliente afetado.',
                'fatores' => $fatores,
                'score' => $pontos,
            ];
        }

        if ($pontos >= 55) {
            return [
                'nivel' => 'Alto',
                'classe' => 'warning',
                'icone' => '⚠️',
                'titulo' => 'Impacto operacional alto',
                'mensagem' => 'Este item precisa de acompanhamento preventivo para não virar urgência.',
                'fatores' => $fatores,
                'score' => $pontos,
            ];
        }

        if ($pontos >= 30) {
            return [
                'nivel' => 'Moderado',
                'classe' => 'info',
                'icone' => '🟡',
                'titulo' => 'Impacto operacional moderado',
                'mensagem' => 'Existe algum risco, mas ainda há margem para organizar a execução.',
                'fatores' => $fatores,
                'score' => $pontos,
            ];
        }

        return [
            'nivel' => 'Baixo',
            'classe' => 'success',
            'icone' => '✅',
            'titulo' => 'Impacto operacional controlado',
            'mensagem' => 'Nenhum sinal forte de impacto operacional no momento.',
            'fatores' => $fatores,
            'score' => $pontos,
        ];
    }

    protected static function gerarPrevisaoAtrasoLote4(callable $get): array
    {
        $dias = self::calcularDiasAteVencimento($get('data_vencimento'));
        $risco = self::calcularRiscoOperacionalVisual($get);
        $checklists = $get('checklists');
        $totalChecklist = is_array($checklists) ? count($checklists) : 0;
        $arquivo = $get('arquivo');
        $temEvidencia = is_array($arquivo) ? count($arquivo) > 0 : filled($arquivo);
        $responsavelDefinido = filled($get('responsavel_id'));
        $pontos = 0;

        if ($dias === null) {
            $pontos += 35;
        } elseif ($dias < 0) {
            $pontos += 60;
        } elseif ($dias <= 1) {
            $pontos += 48;
        } elseif ($dias <= 3) {
            $pontos += 36;
        } elseif ($dias <= 7) {
            $pontos += 20;
        }

        if (! $responsavelDefinido) {
            $pontos += 25;
        }

        if ($totalChecklist === 0) {
            $pontos += 15;
        }

        if (! $temEvidencia && ((float) ($get('valor_tarefa') ?: 0) > 0 || in_array($get('risco_multa_visual'), ['alto', 'critico'], true))) {
            $pontos += 12;
        }

        if (in_array($risco['classe'], ['danger', 'warning'], true)) {
            $pontos += $risco['classe'] === 'danger' ? 25 : 12;
        }

        $pontos = min(100, max(0, $pontos));

        if ($pontos >= 80) {
            return [
                'nivel' => 'Muito alta',
                'classe' => 'danger',
                'icone' => '🚨',
                'mensagem' => 'Risco forte de virar atraso ou retrabalho se o item for salvo sem reforço operacional.',
                'score' => $pontos,
            ];
        }

        if ($pontos >= 55) {
            return [
                'nivel' => 'Alta',
                'classe' => 'warning',
                'icone' => '⚠️',
                'mensagem' => 'Há sinais de prazo apertado ou controle incompleto. Vale revisar antes de salvar.',
                'score' => $pontos,
            ];
        }

        if ($pontos >= 30) {
            return [
                'nivel' => 'Moderada',
                'classe' => 'info',
                'icone' => '🟡',
                'mensagem' => 'A execução parece viável, mas alguns dados ainda podem aumentar a segurança.',
                'score' => $pontos,
            ];
        }

        return [
            'nivel' => 'Baixa',
            'classe' => 'success',
            'icone' => '✅',
            'mensagem' => 'Prazo e cadastro estão em condição confortável para iniciar o controle.',
            'score' => $pontos,
        ];
    }

    protected static function gerarSugestoesAutomaticasLote4(callable $get): array
    {
        $dias = self::calcularDiasAteVencimento($get('data_vencimento'));
        $valor = (float) ($get('valor_tarefa') ?: 0);
        $bloqueado = (bool) $get('bloqueado');
        $checklists = $get('checklists');
        $totalChecklist = is_array($checklists) ? count($checklists) : 0;
        $arquivo = $get('arquivo');
        $temEvidencia = is_array($arquivo) ? count($arquivo) > 0 : filled($arquivo);
        $sugestoes = [];

        if (! filled($get('responsavel_id'))) {
            $sugestoes[] = 'Defina um responsável antes de salvar para evitar item sem dono.';
        }

        if ($dias === null) {
            $sugestoes[] = 'Informe o vencimento para o sistema conseguir prever atraso e priorizar alertas.';
        } elseif ($dias < 0) {
            $sugestoes[] = 'Como o prazo já venceu, salve com checklist mínimo e evidência para rastrear a regularização.';
        } elseif ($dias <= 1) {
            $sugestoes[] = 'Prazo imediato: confirme se o responsável consegue executar hoje e mantenha este item no topo da fila.';
        } elseif ($dias <= 7) {
            $sugestoes[] = 'Prazo curto: use checklist para quebrar a execução em passos claros.';
        }

        if ($valor > 0 && ! $temEvidencia) {
            $sugestoes[] = 'Existe valor financeiro informado: anexe uma evidência inicial para reduzir discussão e retrabalho.';
        }

        if ($bloqueado && $totalChecklist === 0) {
            $sugestoes[] = 'Como bloqueia outros processos, crie pelo menos 2 ou 3 etapas de controle.';
        }

        if (in_array($get('risco_multa_visual'), ['alto', 'critico'], true) && $totalChecklist === 0) {
            $sugestoes[] = 'Risco alto combina melhor com checklist obrigatório de conferência.';
        }

        if (! filled($get('descricao'))) {
            $sugestoes[] = 'Adicione uma observação curta explicando o que deve ser feito para evitar interpretação errada.';
        }

        if (count($sugestoes) === 0) {
            $sugestoes[] = 'Cadastro bem preparado. Após salvar, acompanhe pelo prazo e pela fila de prioridade.';
        }

        return array_slice($sugestoes, 0, 4);
    }

    protected static function renderPrevencaoOperacionalLote4(callable $get): HtmlString
    {
        $impacto = self::avaliarImpactoOperacionalLote4($get);
        $previsao = self::gerarPrevisaoAtrasoLote4($get);
        $sugestoes = self::gerarSugestoesAutomaticasLote4($get);

        $estilos = [
            'danger' => ['cor' => '#991b1b', 'bg' => '#fef2f2', 'border' => '#fecaca'],
            'warning' => ['cor' => '#92400e', 'bg' => '#fffbeb', 'border' => '#fde68a'],
            'info' => ['cor' => '#1d4ed8', 'bg' => '#eff6ff', 'border' => '#bfdbfe'],
            'success' => ['cor' => '#166534', 'bg' => '#f0fdf4', 'border' => '#bbf7d0'],
        ];

        $impactoEstilo = $estilos[$impacto['classe']] ?? $estilos['info'];
        $previsaoEstilo = $estilos[$previsao['classe']] ?? $estilos['info'];

        $fatores = $impacto['fatores'];
        $fatoresHtml = '';

        if (count($fatores) > 0) {
            $fatoresHtml .= '<div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:10px;">';
            foreach (array_slice($fatores, 0, 5) as $fator) {
                $fatoresHtml .= '<span style="font-size:11px;font-weight:800;color:' . e($impactoEstilo['cor']) . ';background:#ffffff;border:1px solid ' . e($impactoEstilo['border']) . ';border-radius:999px;padding:5px 8px;">' . e($fator) . '</span>';
            }
            $fatoresHtml .= '</div>';
        }

        $sugestoesHtml = '<ul style="margin:0;padding-left:18px;display:grid;gap:6px;">';
        foreach ($sugestoes as $sugestao) {
            $sugestoesHtml .= '<li style="font-size:13px;color:#374151;line-height:1.45;">' . e($sugestao) . '</li>';
        }
        $sugestoesHtml .= '</ul>';

        $html = '<div style="display:grid;gap:12px;">';
        $html .= '<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px;">';

        $html .= '<div style="border:1px solid ' . e($impactoEstilo['border']) . ';border-radius:18px;padding:14px 16px;background:' . e($impactoEstilo['bg']) . ';">';
        $html .= '<div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;"><div><div style="font-size:12px;font-weight:900;text-transform:uppercase;letter-spacing:.04em;color:' . e($impactoEstilo['cor']) . ';">' . e($impacto['icone'] . ' Impacto do atraso') . '</div><div style="font-size:21px;font-weight:950;color:' . e($impactoEstilo['cor']) . ';margin-top:3px;">' . e($impacto['nivel']) . '</div></div><span style="font-size:12px;font-weight:900;color:' . e($impactoEstilo['cor']) . ';background:#fff;border:1px solid ' . e($impactoEstilo['border']) . ';border-radius:999px;padding:5px 8px;white-space:nowrap;">' . e((string) $impacto['score']) . '/100</span></div>';
        $html .= '<p style="font-size:13px;color:#374151;margin:8px 0 0;line-height:1.45;">' . e($impacto['mensagem']) . '</p>' . $fatoresHtml;
        $html .= '</div>';

        $html .= '<div style="border:1px solid ' . e($previsaoEstilo['border']) . ';border-radius:18px;padding:14px 16px;background:' . e($previsaoEstilo['bg']) . ';">';
        $html .= '<div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;"><div><div style="font-size:12px;font-weight:900;text-transform:uppercase;letter-spacing:.04em;color:' . e($previsaoEstilo['cor']) . ';">' . e($previsao['icone'] . ' Previsão de atraso') . '</div><div style="font-size:21px;font-weight:950;color:' . e($previsaoEstilo['cor']) . ';margin-top:3px;">' . e($previsao['nivel']) . '</div></div><span style="font-size:12px;font-weight:900;color:' . e($previsaoEstilo['cor']) . ';background:#fff;border:1px solid ' . e($previsaoEstilo['border']) . ';border-radius:999px;padding:5px 8px;white-space:nowrap;">' . e((string) $previsao['score']) . '/100</span></div>';
        $html .= '<p style="font-size:13px;color:#374151;margin:8px 0 0;line-height:1.45;">' . e($previsao['mensagem']) . '</p>';
        $html .= '</div>';

        $html .= '</div>';
        $html .= '<div style="border:1px solid #e5e7eb;border-radius:18px;padding:14px 16px;background:#ffffff;">';
        $html .= '<div style="font-size:12px;font-weight:900;text-transform:uppercase;letter-spacing:.04em;color:#374151;margin-bottom:8px;">💡 Sugestões automáticas</div>';
        $html .= $sugestoesHtml;
        $html .= '</div>';
        $html .= '</div>';

        return new HtmlString($html);
    }

    protected static function avaliarInteligenciaOperacionalLote5(callable $get): array
    {
        $risco = self::calcularRiscoOperacionalVisual($get);
        $qualidade = self::calcularQualidadeCadastro($get);
        $dias = self::calcularDiasAteVencimento($get('data_vencimento'));
        $valor = (float) ($get('valor_tarefa') ?: 0);
        $bloqueado = (bool) $get('bloqueado');
        $checklists = $get('checklists');
        $totalChecklist = is_array($checklists) ? count($checklists) : 0;
        $arquivo = $get('arquivo');
        $temEvidencia = is_array($arquivo) ? count($arquivo) > 0 : filled($arquivo);
        $cliente = trim((string) ($get('portal_cliente_nome') ?: $get('contrato_parte_nome') ?: ''));

        $motivos = [];
        $acoes = [];

        if (! filled($get('titulo'))) {
            $motivos[] = 'Título ainda não definido.';
            $acoes[] = 'Definir um título claro para facilitar busca e acompanhamento.';
        }

        if (! filled($get('categoria_id'))) {
            $motivos[] = 'Categoria ainda não definida.';
            $acoes[] = 'Selecionar uma categoria para carregar o padrão operacional correto.';
        }

        if (! filled($get('responsavel_id'))) {
            $motivos[] = 'Responsável ainda não definido.';
            $acoes[] = 'Definir quem responde pela execução antes de salvar.';
        }

        if ($dias === null) {
            $motivos[] = 'Prazo ainda não definido.';
            $acoes[] = 'Informar a data de vencimento para o sistema classificar o risco.';
        } elseif ($dias < 0) {
            $motivos[] = 'Prazo vencido.';
            $acoes[] = 'Regularizar o item ou ajustar o vencimento antes de continuar.';
        } elseif ($dias === 0) {
            $motivos[] = 'Vence hoje.';
            $acoes[] = 'Anexar evidência e confirmar responsável imediatamente.';
        } elseif ($dias <= 2) {
            $motivos[] = 'Prazo muito curto.';
            $acoes[] = 'Criar checklist mínimo de conferência e priorizar execução.';
        } elseif ($dias <= 7) {
            $motivos[] = 'Prazo próximo.';
            $acoes[] = 'Conferir se checklist e evidências estão preparados.';
        }

        if (in_array($risco['classe'], ['danger', 'warning'], true)) {
            $motivos[] = 'Risco operacional elevado.';
            $acoes[] = 'Revisar prazo, prioridade, urgência e evidências antes de salvar.';
        }

        if ($valor >= 5000) {
            $motivos[] = 'Impacto financeiro relevante.';
            $acoes[] = 'Registrar evidência principal e manter acompanhamento próximo.';
        } elseif ($valor > 0) {
            $motivos[] = 'Existe valor de multa estimado.';
        }

        if ($bloqueado) {
            $motivos[] = 'Este item bloqueia outros processos.';
            $acoes[] = 'Confirmar dependências antes de iniciar a execução.';
        }

        if ($totalChecklist === 0) {
            $motivos[] = 'Sem checklist definido.';
            $acoes[] = 'Adicionar pelo menos uma etapa de conferência para reduzir retrabalho.';
        }

        if (! $temEvidencia && in_array($risco['classe'], ['danger', 'warning'], true)) {
            $motivos[] = 'Item crítico sem evidência inicial.';
            $acoes[] = 'Anexar comprovante, documento ou referência que sustente a execução.';
        }

        if ($cliente !== '') {
            $motivos[] = 'Cliente/parte impactada: ' . $cliente . '.';
        }

        $motivos = array_values(array_unique($motivos));
        $acoes = array_values(array_unique($acoes));

        if (count($acoes) === 0) {
            if ($qualidade >= 85 && ! in_array($risco['classe'], ['danger', 'warning'], true)) {
                $acoes[] = 'Salvar o item e acompanhar pelo painel operacional.';
            } else {
                $acoes[] = 'Revisar os pontos pendentes antes de salvar.';
            }
        }

        if (count($motivos) === 0) {
            $motivos[] = 'Cadastro sem bloqueios relevantes no momento.';
        }

        $seguranca = max(0, min(100, (int) round(($qualidade * 0.55) + ((100 - (int) $risco['score']) * 0.45))));

        $nivelSeguranca = match (true) {
            $seguranca >= 85 => 'Seguro para iniciar',
            $seguranca >= 65 => 'Seguro com atenção',
            $seguranca >= 45 => 'Precisa de revisão',
            default => 'Cadastro em construção',
        };

        $classe = match (true) {
            $seguranca >= 85 => 'success',
            $seguranca >= 65 => 'info',
            $seguranca >= 45 => 'warning',
            default => in_array($risco['classe'], ['danger'], true) && filled($get('data_vencimento')) ? 'danger' : 'warning',
        };

        $impactoCliente = $cliente !== ''
            ? 'Cliente/parte vinculado: ' . $cliente
            : 'Cliente/parte ainda não vinculado. Se houver impacto externo, informe no contrato ou portal.';

        return [
            'seguranca' => $seguranca,
            'nivel' => $nivelSeguranca,
            'classe' => $classe,
            'motivos' => array_slice($motivos, 0, 5),
            'acoes' => array_slice($acoes, 0, 4),
            'proxima_acao' => $acoes[0],
            'impacto_cliente' => $impactoCliente,
        ];
    }

    protected static function renderCardInteligenciaOperacionalLote5(array $inteligencia): string
    {
        $classe = $inteligencia['classe'];

        $cor = match ($classe) {
            'danger' => '#991b1b',
            'warning' => '#92400e',
            'info' => '#1d4ed8',
            default => '#166534',
        };
        $bg = match ($classe) {
            'danger' => '#fef2f2',
            'warning' => '#fffbeb',
            'info' => '#eff6ff',
            default => '#f0fdf4',
        };
        $border = match ($classe) {
            'danger' => '#fecaca',
            'warning' => '#fde68a',
            'info' => '#bfdbfe',
            default => '#bbf7d0',
        };

        $html = '<div style="border:1px solid ' . e($border) . ';border-radius:18px;padding:14px;background:' . e($bg) . ';">';
        $html .= '<div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;margin-bottom:12px;">';
        $html .= '<div><div style="font-size:12px;font-weight:900;text-transform:uppercase;letter-spacing:.04em;color:' . e($cor) . ';">🧭 Validação operacional</div><div style="font-size:20px;font-weight:950;color:' . e($cor) . ';margin-top:3px;">' . e($inteligencia['nivel']) . '</div></div>';
        $html .= '<div style="text-align:right;"><div style="font-size:12px;font-weight:800;color:' . e($cor) . ';">Segurança</div><div style="font-size:24px;font-weight:950;color:' . e($cor) . ';">' . e((string) $inteligencia['seguranca']) . '%</div></div>';
        $html .= '</div>';

        $html .= '<div style="border:1px solid ' . e($border) . ';border-radius:14px;background:#ffffff;padding:10px;margin-bottom:10px;">';
        $html .= '<strong style="display:block;font-size:13px;color:#111827;margin-bottom:6px;">Por que o sistema classificou assim?</strong>';
        $html .= '<ul style="display:grid;gap:6px;margin:0;padding:0;list-style:none;">';
        foreach ($inteligencia['motivos'] as $motivo) {
            $html .= '<li style="font-size:12px;color:#374151;font-weight:700;display:flex;gap:6px;"><span style="color:' . e($cor) . ';">•</span><span>' . e($motivo) . '</span></li>';
        }
        $html .= '</ul></div>';

        $html .= '<div style="border:1px solid ' . e($border) . ';border-radius:14px;background:#ffffff;padding:10px;margin-bottom:10px;">';
        $html .= '<strong style="display:block;font-size:13px;color:#111827;margin-bottom:6px;">Próxima ação recomendada</strong>';
        $html .= '<ol style="display:grid;gap:6px;margin:0;padding-left:18px;">';
        foreach ($inteligencia['acoes'] as $acao) {
            $html .= '<li style="font-size:12px;color:#374151;font-weight:700;">' . e($acao) . '</li>';
        }
        $html .= '</ol></div>';

        $html .= '<div style="font-size:12px;color:#374151;font-weight:800;border:1px dashed ' . e($border) . ';border-radius:14px;background:#ffffff;padding:9px 10px;">' . e($inteligencia['impacto_cliente']) . '</div>';
        $html .= '</div>';

        return $html;
    }

    protected static function renderPainelOperacionalLote2(callable $get): HtmlString
    {
        $risco = self::calcularRiscoOperacionalVisual($get);
        $qualidade = self::calcularQualidadeCadastro($get);
        $inteligenciaLote5 = self::avaliarInteligenciaOperacionalLote5($get);
        $dias = self::calcularDiasAteVencimento($get('data_vencimento'));
        $valor = (float) ($get('valor_tarefa') ?: 0);
        $bloqueado = (bool) $get('bloqueado');

        $categoria = '-';
        if (filled($get('categoria_id'))) {
            $categoria = (string) (CategoriaItemControle::query()->whereKey($get('categoria_id'))->value('nome') ?: '-');
        }

        $responsavel = '-';
        if (filled($get('responsavel_id'))) {
            $responsavel = (string) (Responsavel::query()->whereKey($get('responsavel_id'))->value('nome') ?: '-');
        }

        $vencimento = filled($get('data_vencimento'))
            ? date('d/m/Y', strtotime((string) $get('data_vencimento')))
            : '-';

        $prazoTexto = $dias === null
            ? 'Prazo não definido'
            : ($dias < 0
                ? 'Vencido há ' . abs($dias) . ' dia' . (abs($dias) === 1 ? '' : 's')
                : ($dias === 0
                    ? 'Vence hoje'
                    : 'Faltam ' . $dias . ' dia' . ($dias === 1 ? '' : 's')));

        $classe = $risco['classe'];
        $cor = match ($classe) {
            'danger' => '#991b1b',
            'warning' => '#92400e',
            'info' => '#1d4ed8',
            default => '#166534',
        };
        $bg = match ($classe) {
            'danger' => '#fef2f2',
            'warning' => '#fffbeb',
            'info' => '#eff6ff',
            default => '#f0fdf4',
        };
        $border = match ($classe) {
            'danger' => '#fecaca',
            'warning' => '#fde68a',
            'info' => '#bfdbfe',
            default => '#bbf7d0',
        };

        $qualidadeClasse = $qualidade >= 85 ? 'Excelente' : ($qualidade >= 60 ? 'Boa' : 'Incompleta');
        $qualidadeCor = $qualidade >= 85 ? '#166534' : ($qualidade >= 60 ? '#92400e' : '#991b1b');
        $qualidadeBg = $qualidade >= 85 ? '#f0fdf4' : ($qualidade >= 60 ? '#fffbeb' : '#fef2f2');
        $qualidadeBorder = $qualidade >= 85 ? '#bbf7d0' : ($qualidade >= 60 ? '#fde68a' : '#fecaca');

        $itens = [
            ['ok' => filled($get('titulo')), 'texto' => 'Título definido'],
            ['ok' => filled($get('categoria_id')), 'texto' => 'Categoria definida'],
            ['ok' => filled($get('responsavel_id')), 'texto' => 'Responsável definido'],
            ['ok' => filled($get('data_vencimento')), 'texto' => 'Prazo definido'],
            ['ok' => filled($get('risco_multa_visual')), 'texto' => 'Risco avaliado'],
            ['ok' => is_array($get('checklists')) && count($get('checklists')) > 0, 'texto' => 'Checklist preparado'],
            ['ok' => (is_array($get('arquivo')) ? count($get('arquivo')) > 0 : filled($get('arquivo'))), 'texto' => 'Evidência inicial anexada'],
        ];

        $pendentes = array_values(array_filter($itens, fn (array $item): bool => ! $item['ok']));
        $valorTexto = $valor > 0 ? 'R$ ' . number_format($valor, 2, ',', '.') : 'Sem multa estimada';
        $bloqueioTexto = $bloqueado ? 'Bloqueia outros processos' : 'Não bloqueia processos';
        $checklists = $get('checklists');
        $totalChecklist = is_array($checklists) ? count($checklists) : 0;
        $arquivo = $get('arquivo');
        $temEvidencia = is_array($arquivo) ? count($arquivo) > 0 : filled($arquivo);
        $checklistTexto = $totalChecklist > 0 ? $totalChecklist . ' etapa' . ($totalChecklist === 1 ? '' : 's') . ' definida' . ($totalChecklist === 1 ? '' : 's') : 'Sem etapas definidas';
        $evidenciaTexto = $temEvidencia ? 'Evidência principal anexada' : 'Sem evidência inicial';

        $sugestao = 'Após salvar, acompanhe o item pelo prazo e mantenha o responsável alinhado.';
        if (count($pendentes) > 0) {
            $sugestao = 'Complete os dados pendentes antes de salvar para reduzir risco de erro operacional.';
        } elseif (in_array($classe, ['danger', 'warning'], true)) {
            $sugestao = 'Revise prazo, responsável e evidências antes de salvar, pois este item exige acompanhamento próximo.';
        } elseif ($qualidade >= 85) {
            $sugestao = 'Cadastro consistente para iniciar o controle operacional com segurança.';
        }

        $html = '<div style="display:grid;gap:14px;">';

        $html .= '<div style="border:1px solid ' . e($border) . ';border-radius:18px;padding:16px;background:' . e($bg) . ';">';
        $html .= '<div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;">';
        $html .= '<div><div style="font-size:12px;font-weight:900;color:' . e($cor) . ';text-transform:uppercase;letter-spacing:.04em;">' . e($risco['icone'] . ' Risco operacional') . '</div><div style="font-size:28px;line-height:1.1;font-weight:950;color:' . e($cor) . ';margin-top:4px;">' . e($risco['nivel']) . '</div></div>';
        $html .= '<div style="text-align:right;"><div style="font-size:12px;font-weight:800;color:' . e($cor) . ';">Score</div><div style="font-size:24px;font-weight:950;color:' . e($cor) . ';">' . e((string) $risco['score']) . '/100</div><div style="font-size:11px;font-weight:800;color:' . e($cor) . ';background:#ffffff;border:1px solid ' . e($border) . ';border-radius:999px;padding:3px 7px;margin-top:4px;display:inline-block;">' . e($risco['nivel']) . '</div></div>';
        $html .= '</div>';
        $html .= '<p style="font-size:13px;color:#374151;margin:10px 0 0;">' . e($risco['mensagem']) . '</p>';
        $html .= '</div>';

        $html .= self::renderCardInteligenciaOperacionalLote5($inteligenciaLote5);

        $html .= '<div style="border:1px solid #e5e7eb;border-radius:18px;padding:14px;background:#ffffff;">';
        $html .= '<div style="display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:10px;">';
        $html .= '<strong style="font-size:14px;color:#111827;">Resumo essencial</strong>';
        $html .= '<span style="font-size:12px;font-weight:800;color:' . e($qualidadeCor) . ';background:' . e($qualidadeBg) . ';border:1px solid ' . e($qualidadeBorder) . ';border-radius:999px;padding:5px 9px;white-space:nowrap;">' . e($qualidade . '% ' . $qualidadeClasse) . '</span>';
        $html .= '</div>';
        $html .= '<div style="display:grid;gap:8px;">';
        $html .= self::renderLinhaPainelLote2('Categoria', $categoria);
        $html .= self::renderLinhaPainelLote2('Responsável', $responsavel);
        $html .= self::renderLinhaPainelLote2('Vencimento', $vencimento . ' · ' . $prazoTexto);
        $html .= self::renderLinhaPainelLote2('Impacto financeiro', $valorTexto);
        $html .= self::renderLinhaPainelLote2('Dependência', $bloqueioTexto);
        $html .= self::renderLinhaPainelLote2('Checklist', $checklistTexto);
        $html .= self::renderLinhaPainelLote2('Evidências', $evidenciaTexto);
        $html .= '</div>';
        $html .= '</div>';

        $html .= '<div style="border:1px solid #e5e7eb;border-radius:18px;padding:14px;background:#ffffff;">';
        $html .= '<strong style="display:block;font-size:14px;color:#111827;margin-bottom:10px;">Checklist de segurança</strong>';
        $html .= '<ul style="display:grid;gap:8px;margin:0;padding:0;list-style:none;">';
        foreach ($itens as $item) {
            $itemCor = $item['ok'] ? '#166534' : '#92400e';
            $itemBg = $item['ok'] ? '#f0fdf4' : '#fffbeb';
            $itemBorder = $item['ok'] ? '#bbf7d0' : '#fde68a';
            $itemIcone = $item['ok'] ? '✓' : '•';
            $html .= '<li style="display:flex;align-items:center;gap:8px;border:1px solid ' . e($itemBorder) . ';border-radius:12px;padding:8px 10px;background:' . e($itemBg) . ';font-size:13px;color:' . e($itemCor) . ';font-weight:700;"><span>' . e($itemIcone) . '</span>' . e($item['texto']) . '</li>';
        }
        $html .= '</ul>';
        $html .= '</div>';

        $html .= '<div style="border:1px solid #bfdbfe;border-radius:18px;padding:13px 14px;background:#eff6ff;color:#1e3a8a;font-size:13px;">';
        $html .= '<strong style="display:block;margin-bottom:4px;">Faça agora</strong>' . e($inteligenciaLote5['proxima_acao']);
        $html .= '</div>';

        $html .= '</div>';

        return new HtmlString($html);
    }

    protected static function renderLinhaPainelLote2(string $label, string $valor): string
    {
        return '<div style="display:flex;align-items:flex-start;justify-content:space-between;gap:10px;border:1px solid #f3f4f6;border-radius:12px;padding:8px 10px;background:#f9fafb;"><span style="font-size:12px;color:#6b7280;font-weight:700;">' . e($label) . '</span><strong style="font-size:13px;color:#111827;text-align:right;">' . e($valor) . '</strong></div>';
    }

    protected static function renderStatusOperacionalLote1(callable $get): HtmlString
    {
        $itens = [
            ['ok' => filled($get('titulo')), 'texto' => 'Título definido'],
            ['ok' => filled($get('categoria_id')), 'texto' => 'Categoria definida'],
            ['ok' => filled($get('responsavel_id')), 'texto' => 'Responsável definido'],
            ['ok' => filled($get('data_vencimento')), 'texto' => 'Prazo definido'],
            ['ok' => filled($get('risco_multa_visual')), 'texto' => 'Risco avaliado'],
        ];

        $pendentes = array_values(array_filter($itens, fn (array $item): bool => ! $item['ok']));
        $risco = self::calcularRiscoOperacionalVisual($get);
        $scoreQualidade = self::calcularQualidadeCadastro($get);

        $html = '<div style="border:1px solid #e5e7eb;border-radius:16px;padding:16px;background:#ffffff;">';
        $html .= '<div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:12px;">';
        $html .= '<div><div style="font-size:12px;color:#6b7280;font-weight:800;text-transform:uppercase;letter-spacing:.04em;">Segurança do cadastro</div><div style="font-size:20px;font-weight:900;color:#111827;">' . e($scoreQualidade . '% completo') . '</div></div>';
        $html .= '<span style="font-size:12px;font-weight:800;border-radius:999px;padding:6px 10px;background:#f3f4f6;color:#374151;white-space:nowrap;">Risco: ' . e($risco['nivel']) . '</span>';
        $html .= '</div><ul style="display:grid;gap:8px;margin:0;padding:0;list-style:none;">';

        foreach ($itens as $item) {
            $icone = $item['ok'] ? '✓' : '•';
            $cor = $item['ok'] ? '#166534' : '#92400e';
            $bg = $item['ok'] ? '#f0fdf4' : '#fffbeb';
            $border = $item['ok'] ? '#bbf7d0' : '#fde68a';
            $html .= '<li style="display:flex;align-items:center;gap:8px;border:1px solid ' . e($border) . ';border-radius:12px;padding:8px 10px;background:' . e($bg) . ';font-size:13px;color:' . e($cor) . ';font-weight:700;"><span>' . e($icone) . '</span>' . e($item['texto']) . '</li>';
        }

        $html .= '</ul>';

        if (count($pendentes) > 0) {
            $html .= '<div style="margin-top:12px;font-size:13px;color:#92400e;background:#fffbeb;border:1px solid #fde68a;border-radius:12px;padding:10px;"><strong>Pendente:</strong> complete os itens destacados para reduzir risco de erro antes de salvar.</div>';
        } else {
            $html .= '<div style="margin-top:12px;font-size:13px;color:#166534;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;padding:10px;"><strong>Pronto:</strong> os dados principais estão definidos para iniciar o controle operacional.</div>';
        }

        $html .= '</div>';

        return new HtmlString($html);
    }

    protected static function renderRiscoOperacionalResumo(callable $get): HtmlString
    {
        $risco = self::calcularRiscoOperacionalVisual($get);
        $dias = self::calcularDiasAteVencimento($get('data_vencimento'));
        $valor = (float) ($get('valor_tarefa') ?: 0);

        $cor = match ($risco['classe']) {
            'danger' => '#991b1b',
            'warning' => '#92400e',
            'info' => '#1d4ed8',
            default => '#166534',
        };

        $bg = match ($risco['classe']) {
            'danger' => '#fef2f2',
            'warning' => '#fffbeb',
            'info' => '#eff6ff',
            default => '#f0fdf4',
        };

        $border = match ($risco['classe']) {
            'danger' => '#fecaca',
            'warning' => '#fde68a',
            'info' => '#bfdbfe',
            default => '#bbf7d0',
        };

        $prazoTexto = $dias === null
            ? 'Prazo não definido'
            : ($dias < 0 ? 'Vencido há ' . abs($dias) . ' dia' . (abs($dias) === 1 ? '' : 's') : ($dias === 0 ? 'Vence hoje' : 'Faltam ' . $dias . ' dia' . ($dias === 1 ? '' : 's')));

        $valorTexto = $valor > 0 ? 'R$ ' . number_format($valor, 2, ',', '.') : 'Sem multa estimada';

        return new HtmlString('<div style="border:1px solid ' . e($border) . ';border-radius:16px;padding:16px;background:' . e($bg) . ';"><div style="display:flex;align-items:center;justify-content:space-between;gap:12px;"><div><div style="font-size:12px;font-weight:800;color:' . e($cor) . ';text-transform:uppercase;letter-spacing:.04em;">' . e($risco['icone'] . ' Nível operacional') . '</div><div style="font-size:26px;font-weight:900;color:' . e($cor) . ';">' . e($risco['nivel']) . '</div></div><div style="text-align:right;"><div style="font-size:12px;color:' . e($cor) . ';font-weight:800;">Score</div><div style="font-size:22px;color:' . e($cor) . ';font-weight:900;">' . e((string) $risco['score']) . '/100</div></div></div><p style="font-size:13px;color:#374151;margin:10px 0 12px;">' . e($risco['mensagem']) . '</p><div style="display:grid;gap:8px;"><span style="font-size:13px;color:#111827;background:#ffffff;border:1px solid ' . e($border) . ';border-radius:12px;padding:8px 10px;"><strong>Prazo:</strong> ' . e($prazoTexto) . '</span><span style="font-size:13px;color:#111827;background:#ffffff;border:1px solid ' . e($border) . ';border-radius:12px;padding:8px 10px;"><strong>Impacto financeiro:</strong> ' . e($valorTexto) . '</span></div></div>');
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

    protected static function renderChecklistAutomaticoPreview(callable $get): HtmlString
    {
        $checklists = $get('checklists');
        $total = is_array($checklists) ? count($checklists) : 0;

        if ($total > 0) {
            $textoEtapas = $total === 1 ? '1 etapa será criada automaticamente' : $total . ' etapas serão criadas automaticamente';

            return new HtmlString('<div class="pz-checklist-preview pz-checklist-preview-success"><strong>✔ ' . e($textoEtapas) . '</strong><span>Você pode revisar, reordenar ou adicionar etapas antes de salvar.</span></div>');
        }

        return new HtmlString('<div class="pz-checklist-preview"><strong>Checklist pronto para receber etapas.</strong><span>Ao escolher uma categoria com modelo, as etapas serão carregadas automaticamente.</span></div>');
    }

    protected static function renderAnexosDropzonePreview(): HtmlString
    {
        return new HtmlString('<div class="pz-attachment-drop-preview"><div class="pz-attachment-drop-icon">📎</div><strong>Arraste arquivos aqui</strong><span>ou clique no campo abaixo para selecionar</span><small>PDF • Excel • Word • CSV • TXT • Imagens</small></div>');
    }

    protected static function renderProntoParaCriar(callable $get): HtmlString
    {
        $itens = [
            ['ok' => filled($get('titulo')), 'texto' => 'Título definido'],
            ['ok' => filled($get('categoria_id')), 'texto' => 'Categoria definida'],
            ['ok' => filled($get('responsavel_id')), 'texto' => 'Responsável definido'],
            ['ok' => filled($get('data_vencimento')), 'texto' => 'Prazo definido'],
            ['ok' => filled($get('risco_multa_visual')), 'texto' => 'Risco avaliado'],
        ];

        $html = '<ul class="pz-readiness-list">';

        foreach ($itens as $item) {
            $classe = $item['ok'] ? 'is-ready' : 'is-pending';
            $icone = $item['ok'] ? '✓' : '•';
            $html .= '<li class="' . e($classe) . '"><span>' . e($icone) . '</span>' . e($item['texto']) . '</li>';
        }

        $html .= '</ul>';

        return new HtmlString($html);
    }

    protected static function renderQualidadeCadastro(callable $get): HtmlString
    {
        $score = self::calcularQualidadeCadastro($get);
        $classe = $score >= 85 ? 'success' : ($score >= 60 ? 'warning' : 'danger');
        $mensagem = $score >= 85
            ? 'Cadastro forte para acompanhamento operacional.'
            : ($score >= 60
                ? 'Cadastro bom, mas ainda pode ganhar contexto.'
                : 'Preencha os campos principais para reduzir risco de erro.');

        return new HtmlString('<div class="pz-quality-card pz-quality-' . e($classe) . '"><div class="pz-quality-head"><strong>' . e((string) $score) . '%</strong><span>' . e($mensagem) . '</span></div><div class="pz-quality-bar"><span style="width: ' . e((string) $score) . '%"></span></div></div>');
    }

    protected static function calcularQualidadeCadastro(callable $get): int
    {
        $score = 0;

        $score += filled($get('titulo')) ? 18 : 0;
        $score += filled($get('categoria_id')) ? 18 : 0;
        $score += filled($get('responsavel_id')) ? 18 : 0;
        $score += filled($get('data_vencimento')) ? 18 : 0;
        $score += filled($get('risco_multa_visual')) ? 12 : 0;
        $score += filled($get('descricao')) ? 8 : 0;
        $score += is_array($get('checklists')) && count($get('checklists')) > 0 ? 8 : 0;

        return min(100, max(0, $score));
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
