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
                            ->helperText('Data limite para conclusão.')
                            ->columnSpan(['default' => 12, 'lg' => 4]),

                        Select::make('prioridade')
                            ->label('Prioridade')
                            ->required()
                            ->default('media')
                            ->options(self::getPrioridadeOptions())
                            ->native(false)
                            ->helperText('Define a importância deste item.')
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
                            ->visible(fn (): bool => DatabaseSchema::hasColumn('item_controles', 'urgencia'))
                            ->dehydrated(fn (): bool => DatabaseSchema::hasColumn('item_controles', 'urgencia'))
                            ->columnSpan(['default' => 12, 'lg' => 3]),

                        Select::make('risco_multa_visual')
                            ->label('Risco de multa')
                            ->helperText('Qual o risco de multa/penalidade?')
                            ->default('alto')
                            ->options([
                                'baixo' => 'Baixo',
                                'medio' => 'Médio',
                                'alto' => 'Alto',
                            ])
                            ->native(false)
                            ->dehydrated(false)
                            ->columnSpan(['default' => 12, 'lg' => 3]),

                        TextInput::make('valor_tarefa')
                            ->label('Valor estimado da multa')
                            ->numeric()
                            ->prefix('R$')
                            ->minValue(0)
                            ->helperText('Valor aproximado do risco.')
                            ->visible(fn (): bool => DatabaseSchema::hasColumn('item_controles', 'valor_tarefa'))
                            ->dehydrated(fn (): bool => DatabaseSchema::hasColumn('item_controles', 'valor_tarefa'))
                            ->columnSpan(['default' => 12, 'lg' => 3]),

                        Toggle::make('bloqueado')
                            ->label('Bloqueia outros processos?')
                            ->helperText('Se este item atrasar, bloqueia outros?')
                            ->visible(fn (): bool => DatabaseSchema::hasColumn('item_controles', 'bloqueado'))
                            ->dehydrated(fn (): bool => DatabaseSchema::hasColumn('item_controles', 'bloqueado'))
                            ->columnSpan(['default' => 12, 'lg' => 3]),
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
                            ->content(fn (callable $get): string => self::getPrioridadeOptions()[$get('prioridade') ?: 'media'] ?? 'Média'),

                        Placeholder::make('resumo_urgencia')
                            ->label('Urgência')
                            ->content(fn (callable $get): string => self::getUrgenciaOptions()[$get('urgencia') ?: 'media'] ?? '-'),

                        Placeholder::make('resumo_risco')
                            ->label('Risco de multa')
                            ->content(fn (): string => 'Alto'),

                        Placeholder::make('resumo_valor')
                            ->label('Valor estimado')
                            ->content(fn (callable $get): string => 'R$ ' . number_format((float) ($get('valor_tarefa') ?: 0), 2, ',', '.')),

                        Placeholder::make('resumo_status')
                            ->label('Status')
                            ->content(fn (callable $get): HtmlString => new HtmlString('<span class="pz-status-pill">' . e(self::getStatusOptions()[$get('status') ?: 'pendente'] ?? 'Pendente') . '</span><small>Status padrão na criação.</small>')),
                    ])
                    ->columns(1)
                    ->columnSpanFull(),

                            Section::make('Dica')
                    ->extraAttributes(['class' => 'prazzu-tip-section'])
                    ->description('Após salvar, você poderá adicionar checklist, anexos e outras informações.')
                    ->icon('heroicon-o-light-bulb')
                    ->schema([])
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
            'baixa' => 'Baixa',
            'media' => 'Média',
            'alta' => 'Alta',
            'urgente' => 'Urgente',
        ];
    }

    protected static function getUrgenciaOptions(): array
    {
        return [
            'baixa' => 'Baixa',
            'media' => 'Média',
            'alta' => 'Alta',
            'critica' => 'Crítica',
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
