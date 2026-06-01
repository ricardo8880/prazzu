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
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use App\Support\CachedSchema as DatabaseSchema;
use Illuminate\Support\Str;

class ItemControleForm
{
    public static function make(Schema $schema): Schema
    {
        $user = Filament::auth()->user();
        $responsavelIdDoUsuario = $user?->responsavel?->id;

        return $schema
            ->components([
                Section::make('Dados do Item')
                    ->schema([
                        TextInput::make('titulo')
                            ->label('Titulo')
                            ->required()
                            ->maxLength(255)
                            ->trim(),

                        Textarea::make('descricao')
                            ->label('Descricao')
                            ->rows(4)
                            ->columnSpanFull(),

                        Select::make('categoria_id')
                            ->label('Categoria')
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
                            ->helperText('Se a categoria tiver template, o checklist sera carregado automaticamente.'),

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

                        Hidden::make('tipo')
                            ->default('documento')
                            ->dehydrated(true),

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

                        Select::make('prioridade')
                            ->label('Prioridade')
                            ->required()
                            ->default('media')
                            ->options(self::getPrioridadeOptions())
                            ->native(false),

                        Select::make('urgencia')
                            ->label('Urgência operacional')
                            ->helperText('Campo usado pelo Centro Operacional para ordenar o que precisa atenção primeiro.')
                            ->default('media')
                            ->options(self::getUrgenciaOptions())
                            ->native(false)
                            ->visible(fn (): bool => DatabaseSchema::hasColumn('item_controles', 'urgencia'))
                            ->dehydrated(fn (): bool => DatabaseSchema::hasColumn('item_controles', 'urgencia')),

                        TextInput::make('valor_tarefa')
                            ->label('Valor da tarefa')
                            ->numeric()
                            ->prefix('R$')
                            ->minValue(0)
                            ->helperText('Valor usado na fila 💰 Pendente de Cobrança.')
                            ->visible(fn (): bool => DatabaseSchema::hasColumn('item_controles', 'valor_tarefa'))
                            ->dehydrated(fn (): bool => DatabaseSchema::hasColumn('item_controles', 'valor_tarefa')),

                        Toggle::make('bloqueado')
                            ->label('Bloqueado por dependência externa')
                            ->helperText('Quando ativo, o item aparece na lista de bloqueados do Centro Operacional.')
                            ->visible(fn (): bool => DatabaseSchema::hasColumn('item_controles', 'bloqueado'))
                            ->dehydrated(fn (): bool => DatabaseSchema::hasColumn('item_controles', 'bloqueado')),

                        DatePicker::make('data_vencimento')
                            ->label('Data de Vencimento')
                            ->required()
                            ->native(false),

                        DatePicker::make('data_conclusao')
                            ->label('Data de Conclusao')
                            ->native(false)
                            ->visible(fn (callable $get): bool => $get('status') === 'concluido'),

                        Select::make('empresa_id')
                            ->label('Empresa')
                            ->required()
                            ->searchable()
                            ->native(false)
                            ->live()
                            ->default(fn (): ?int => ItemControleResource::getDefaultEmpresaIdForUser($user))
                            ->disabled(fn (): bool => $user?->isSuperAdmin() !== true)
                            ->dehydrated(true)
                            ->getSearchResultsUsing(fn (string $search): array => self::getEmpresaSearchResults($search, $user))
                            ->getOptionLabelUsing(fn ($value): ?string => blank($value)
                                ? null
                                : Empresa::query()->whereKey($value)->value('razao_social')
                            )
                            ->afterStateUpdated(function ($state, callable $set) use ($user): void {
                                if ($user?->isSuperAdmin()) {
                                    $set('responsavel_id', null);
                                    $set('categoria_id', null);
                                    $set('checklists', []);
                                    $set('tags', []);
                                }
                            }),

                        Select::make('responsavel_id')
                            ->label('Responsavel')
                            ->required()
                            ->searchable()
                            ->native(false)
                            ->getSearchResultsUsing(function (string $search, callable $get) use ($user): array {
                                $empresaId = filled($get('empresa_id'))
                                    ? (int) $get('empresa_id')
                                    : null;

                                return self::getResponsavelSearchResults($search, $user, $empresaId);
                            })
                            ->getOptionLabelUsing(fn ($value): ?string => blank($value)
                                ? null
                                : Responsavel::query()->whereKey($value)->value('nome')
                            )
                            ->default(fn (): ?int => ItemControleResource::getDefaultResponsavelIdForUser($user))
                            ->disabled(fn (): bool => $user?->isUser() === true)
                            ->dehydrated(true)
                            ->helperText(function () use ($user, $responsavelIdDoUsuario): ?string {
                                if (! $user) {
                                    return null;
                                }

                                if ($user->isSuperAdmin()) {
                                    return 'O super admin pode vincular o item a responsaveis de qualquer empresa.';
                                }

                                if ($user->isAdminEmpresa()) {
                                    return 'O administrador pode vincular o item a qualquer responsavel da propria empresa.';
                                }

                                if ($user->isGestor()) {
                                    return 'O gestor pode vincular o item apenas aos responsaveis da equipe dele.';
                                }

                                if (! $responsavelIdDoUsuario) {
                                    return 'Seu usuario ainda nao esta vinculado a um responsavel. Vincule o user_id na tabela responsaveis para poder criar itens.';
                                }

                                return 'Usuario comum so pode criar e editar itens vinculados a si mesmo.';
                            })
                            ->rules([
                                function () use ($user) {
                                    return function (string $attribute, $value, \Closure $fail) use ($user): void {
                                        if (! $user) {
                                            $fail('Usuario nao autenticado.');
                                            return;
                                        }

                                        if (! filled($value)) {
                                            $fail('O responsavel e obrigatorio.');
                                            return;
                                        }

                                        if (! ItemControleResource::canUserAssignResponsavel($user, (int) $value)) {
                                            if ($user->isGestor()) {
                                                $fail('Voce so pode vincular o item a responsaveis da sua equipe.');
                                                return;
                                            }

                                            if ($user->isUser()) {
                                                $fail('Voce so pode vincular o item ao seu proprio responsavel.');
                                                return;
                                            }

                                            $fail('Voce nao tem permissao para vincular este responsavel.');
                                        }
                                    };
                                },
                            ]),

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

                        Textarea::make('observacao')
                            ->label('Observacao')
                            ->rows(4)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Dados do Contrato')
                    ->description('Preencha estas informacoes quando o item for um contrato.')
                    ->schema([
                        TextInput::make('contrato_numero')
                            ->label('Numero do contrato')
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
                            ->label('Inicio')
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
                    ->visible(fn (callable $get): bool => PlanoService::usuarioPossuiFeature($user, PlanoService::FEATURE_CONTRATOS) && self::deveMostrarContrato($get)),

                Section::make('Portal do Cliente')
                    ->description('Gere um link externo para o cliente acompanhar este item sem acessar o painel interno.')
                    ->schema([
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
                            ->columnSpanFull(),

                        TextInput::make('portal_cliente_nome')
                            ->label('Nome do cliente')
                            ->maxLength(255)
                            ->trim(),

                        TextInput::make('portal_cliente_email')
                            ->label('E-mail do cliente')
                            ->email()
                            ->maxLength(255)
                            ->trim(),

                        DateTimePicker::make('portal_expira_em')
                            ->label('Link expira em')
                            ->native(false)
                            ->seconds(false)
                            ->helperText('Deixe vazio para nao expirar.'),

                        Hidden::make('portal_token')
                            ->default(fn (): string => Str::random(64)),
                    ])
                    ->columns(2)
                    ->visible(fn (): bool => PlanoService::usuarioPossuiFeature($user, PlanoService::FEATURE_PORTAL_CLIENTE)),

                Section::make('Checklist do Item')
                    ->description('Cadastre etapas internas para acompanhar a execucao deste item.')
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
                                    ->label('Concluido')
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
                    ->visible(fn (): bool => PlanoService::usuarioPossuiFeature($user, PlanoService::FEATURE_CHECKLIST)),
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
