<?php

namespace App\Filament\Resources\PrazzuTemplates;

use App\Filament\Resources\PrazzuTemplates\Pages\CreatePrazzuTemplate;
use App\Filament\Resources\PrazzuTemplates\Pages\EditPrazzuTemplate;
use App\Filament\Resources\PrazzuTemplates\Pages\ListPrazzuTemplates;
use App\Models\Empresa;
use App\Models\PrazzuTemplate;
use App\Models\Responsavel;
use App\Services\PlanoService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class PrazzuTemplateResource extends Resource
{
    protected static ?string $model = PrazzuTemplate::class;
    protected static ?string $slug = 'templates-enterprise/modelos';

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-squares-plus';
    protected static string | UnitEnum | null $navigationGroup = 'Documentos';
    protected static ?string $navigationLabel = 'Modelos Enterprise';
    protected static ?string $modelLabel = 'Template Enterprise';
    protected static ?string $pluralModelLabel = 'Templates Enterprise';
    protected static ?int $navigationSort = 11;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Identificação do template')
                ->description('Monte um modelo reutilizável com tarefas, checklists, campos personalizados, visões, automações e regras de recorrência.')
                ->schema([
                    TextInput::make('name')
                        ->label('Nome do template')
                        ->placeholder('Ex: Fechamento mensal contábil')
                        ->required()
                        ->maxLength(180),

                    Select::make('module')
                        ->label('Módulo / nicho')
                        ->required()
                        ->native(false)
                        ->searchable()
                        ->options([
                            'contabil' => 'Contábil',
                            'rh' => 'RH',
                            'juridico' => 'Jurídico',
                            'compliance' => 'Compliance',
                            'financeiro' => 'Financeiro',
                            'bpo' => 'BPO',
                            'documentos' => 'Documentos',
                            'contratos' => 'Contratos',
                            'tarefas' => 'Tarefas gerais',
                        ]),

                    Select::make('active')
                        ->label('Disponível para uso')
                        ->native(false)
                        ->default(1)
                        ->options([1 => 'Sim', 0 => 'Não']),

                    Textarea::make('description')
                        ->label('Descrição')
                        ->rows(4)
                        ->columnSpanFull()
                        ->placeholder('Explique quando usar esse template e qual processo ele resolve.')
                        ->maxLength(2000),
                ])
                ->columns(['default' => 1, 'md' => 3]),

            Section::make('Tarefas que serão criadas')
                ->description('Cada linha vira um Item de Controle real quando o usuário aplicar o template. Não são dados ilustrativos.')
                ->schema([
                    Repeater::make('payload.tasks')
                        ->label('Tarefas do template')
                        ->schema([
                            TextInput::make('title')
                                ->label('Título')
                                ->required()
                                ->maxLength(255),

                            Select::make('type')
                                ->label('Tipo')
                                ->native(false)
                                ->default('tarefa')
                                ->options([
                                    'tarefa' => 'Tarefa',
                                    'documento' => 'Documento',
                                    'contrato' => 'Contrato',
                                    'compliance' => 'Compliance',
                                    'financeiro' => 'Financeiro',
                                    'rh' => 'RH',
                                ]),

                            Select::make('priority')
                                ->label('Prioridade')
                                ->native(false)
                                ->default('media')
                                ->options([
                                    'baixa' => 'Baixa',
                                    'media' => 'Média',
                                    'alta' => 'Alta',
                                    'urgente' => 'Urgente',
                                ]),

                            TextInput::make('days_after_start')
                                ->label('Dias após início')
                                ->numeric()
                                ->default(0),

                            TextInput::make('sla_hours')
                                ->label('SLA em horas')
                                ->numeric(),

                            TextInput::make('estimated_minutes')
                                ->label('Tempo estimado min.')
                                ->numeric(),

                            Select::make('approval_required')
                                ->label('Exige aprovação')
                                ->native(false)
                                ->default(0)
                                ->options([1 => 'Sim', 0 => 'Não']),

                            Select::make('recurrence')
                                ->label('Recorrência')
                                ->native(false)
                                ->placeholder('Sem recorrência')
                                ->options([
                                    'daily' => 'Diária',
                                    'weekly' => 'Semanal',
                                    'monthly' => 'Mensal',
                                    'quarterly' => 'Trimestral',
                                    'yearly' => 'Anual',
                                ]),

                            Textarea::make('description')
                                ->label('Descrição')
                                ->rows(3)
                                ->columnSpanFull(),

                            Repeater::make('checklist')
                                ->label('Checklist da tarefa')
                                ->simple(TextInput::make('titulo')->label('Item do checklist')->required()->maxLength(255))
                                ->defaultItems(0)
                                ->reorderable()
                                ->columnSpanFull(),
                        ])
                        ->columns(['default' => 1, 'lg' => 2, '2xl' => 4])
                        ->defaultItems(1)
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['title'] ?? 'Nova tarefa')
                        ->columnSpanFull(),
                ]),

            Section::make('Campos personalizados')
                ->description('Funciona como Custom Fields: moeda, fórmula, etiqueta, menu, data ou texto. Os campos serão salvos no payload das tarefas criadas.')
                ->schema([
                    Repeater::make('payload.custom_fields')
                        ->label('Campos')
                        ->schema([
                            TextInput::make('name')->label('Nome')->required()->maxLength(120),
                            Select::make('type')
                                ->label('Tipo')
                                ->native(false)
                                ->default('text')
                                ->options([
                                    'text' => 'Texto',
                                    'currency' => 'Moeda',
                                    'number' => 'Número',
                                    'formula' => 'Fórmula',
                                    'label' => 'Etiqueta',
                                    'select' => 'Menu suspenso',
                                    'date' => 'Data',
                                    'person' => 'Pessoa',
                                ]),
                            TextInput::make('default')->label('Valor padrão')->maxLength(255),
                            TextInput::make('options')->label('Opções / fórmula')->placeholder('Separar opções por vírgula ou informar a fórmula')->maxLength(500),
                        ])
                        ->columns(['default' => 1, 'md' => 2, 'xl' => 4])
                        ->defaultItems(0)
                        ->collapsible()
                        ->reorderable()
                        ->columnSpanFull(),
                ]),

            Section::make('Visões, automações e colaboração')
                ->description('Configure o template para aparecer de forma organizada: Dashboard, Everything View, Kanban, calendário, comentários, aprovações, documentos e mapas mentais.')
                ->schema([
                    Repeater::make('payload.views')
                        ->label('Visões')
                        ->schema([
                            TextInput::make('name')->label('Nome')->required()->maxLength(120),
                            Select::make('type')
                                ->label('Tipo')
                                ->native(false)
                                ->default('list')
                                ->options([
                                    'dashboard' => 'Dashboard',
                                    'everything' => 'Everything View',
                                    'kanban' => 'Kanban',
                                    'calendar' => 'Calendário',
                                    'gantt' => 'Gantt',
                                    'list' => 'Lista',
                                    'lineup' => 'LineUp',
                                    'me_mode' => 'Me Mode',
                                ]),
                            TextInput::make('filter')->label('Filtro')->maxLength(255),
                        ])
                        ->columns(['default' => 1, 'md' => 3])
                        ->defaultItems(0)
                        ->collapsible(),

                    Repeater::make('payload.automations')
                        ->label('Automações')
                        ->schema([
                            TextInput::make('trigger')->label('Quando')->required()->placeholder('Ex: status mudar para Aprovado')->maxLength(255),
                            TextInput::make('action')->label('Faça')->required()->placeholder('Ex: atribuir ao responsável financeiro')->maxLength(255),
                        ])
                        ->columns(['default' => 1, 'md' => 2])
                        ->defaultItems(0)
                        ->collapsible(),

                    Repeater::make('payload.docs')
                        ->label('Documentos internos')
                        ->schema([
                            TextInput::make('title')->label('Título')->required()->maxLength(180),
                            Textarea::make('content')->label('Conteúdo base')->rows(4)->columnSpanFull(),
                        ])
                        ->columns(['default' => 1])
                        ->defaultItems(0)
                        ->collapsible(),

                    Repeater::make('payload.mind_map')
                        ->label('Mapa mental')
                        ->schema([
                            TextInput::make('node')->label('Nó')->required()->maxLength(180),
                            TextInput::make('parent')->label('Nó pai')->maxLength(180),
                        ])
                        ->columns(['default' => 1, 'md' => 2])
                        ->defaultItems(0)
                        ->collapsible(),
                ])
                ->columns(['default' => 1, 'xl' => 2]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->striped()
            ->columns([
                TextColumn::make('name')->label('Template')->searchable()->sortable()->description(fn (PrazzuTemplate $record): string => $record->description ? \Illuminate\Support\Str::limit($record->description, 80) : 'Sem descrição'),
                TextColumn::make('module')->label('Módulo')->badge()->searchable()->sortable(),
                TextColumn::make('tasks_count')->label('Tarefas')->badge()->color('info'),
                TextColumn::make('custom_fields_count')->label('Campos')->badge()->color('gray'),
                TextColumn::make('automations_count')->label('Automações')->badge()->color('warning'),
                TextColumn::make('views_count')->label('Visões')->badge()->color('success'),
                IconColumn::make('active')->label('Ativo')->boolean(),
                TextColumn::make('updated_at')->label('Atualizado')->dateTime('d/m/Y H:i')->sortable()->toggleable(),
            ])
            ->filters([
                SelectFilter::make('module')
                    ->label('Módulo')
                    ->options([
                        'contabil' => 'Contábil',
                        'rh' => 'RH',
                        'juridico' => 'Jurídico',
                        'compliance' => 'Compliance',
                        'financeiro' => 'Financeiro',
                        'bpo' => 'BPO',
                        'documentos' => 'Documentos',
                        'contratos' => 'Contratos',
                        'tarefas' => 'Tarefas gerais',
                    ]),
            ])
            ->recordActions([
                Action::make('aplicar')
                    ->label('Aplicar')
                    ->icon('heroicon-o-bolt')
                    ->color('primary')
                    ->visible(fn (PrazzuTemplate $record): bool => $record->active)
                    ->form([
                        Select::make('empresa_id')
                            ->label('Empresa')
                            ->required()
                            ->native(false)
                            ->searchable()
                            ->default(fn () => Filament::auth()->user()?->isSuperAdmin() ? null : Filament::auth()->user()?->empresa_id)
                            ->disabled(fn () => Filament::auth()->user()?->isSuperAdmin() !== true)
                            ->dehydrated(true)
                            ->options(fn () => Filament::auth()->user()?->isSuperAdmin()
                                ? Empresa::query()->orderBy('razao_social')->limit(150)->pluck('razao_social', 'id')->toArray()
                                : Empresa::query()->whereKey(Filament::auth()->user()?->empresa_id)->pluck('razao_social', 'id')->toArray()),

                        Select::make('responsavel_id')
                            ->label('Responsável padrão')
                            ->native(false)
                            ->searchable()
                            ->preload()
                            ->options(fn () => Responsavel::query()
                                ->when(! Filament::auth()->user()?->isSuperAdmin(), fn (Builder $query) => $query->where('empresa_id', Filament::auth()->user()?->empresa_id))
                                ->orderBy('nome')
                                ->limit(150)
                                ->pluck('nome', 'id')
                                ->toArray()),

                        DatePicker::make('data_inicio')
                            ->label('Data inicial')
                            ->default(now())
                            ->required()
                            ->native(false),
                    ])
                    ->action(function (PrazzuTemplate $record, array $data): void {
                        $created = $record->instantiateFor(
                            (int) $data['empresa_id'],
                            filled($data['responsavel_id'] ?? null) ? (int) $data['responsavel_id'] : null,
                            \Illuminate\Support\Carbon::parse($data['data_inicio']),
                            Filament::auth()->id(),
                        );

                        Notification::make()
                            ->title('Template aplicado com sucesso')
                            ->body($created . ' item(ns) criado(s) no controle operacional.')
                            ->success()
                            ->send();
                    }),

                EditAction::make()->label('Editar'),
                DeleteAction::make()->label('Excluir'),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canViewAny();
    }

    public static function canViewAny(): bool
    {
        return true;
    }

    public static function canCreate(): bool
    {
        return Filament::auth()->user()?->isAdmin() === true
            || Filament::auth()->user()?->isGestor() === true;
    }

    public static function canEdit(Model $record): bool
    {
        return static::canCreate();
    }

    public static function canDelete(Model $record): bool
    {
        return static::canCreate();
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPrazzuTemplates::route('/'),
            'create' => CreatePrazzuTemplate::route('/create'),
            'edit' => EditPrazzuTemplate::route('/{record}/edit'),
        ];
    }
}
