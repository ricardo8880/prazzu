<?php

namespace App\Filament\Resources\ItemControles;

use App\Filament\Resources\ItemControles\Pages\CentralNotificacoes;
use App\Filament\Resources\ItemControles\Pages\CreateItemControle;
use App\Filament\Resources\ItemControles\Pages\DashboardGraficosItemControles;
use App\Filament\Resources\ItemControles\Pages\DashboardTabelasItemControles;
use App\Filament\Resources\ItemControles\Pages\EditItemControle;
use App\Filament\Resources\ItemControles\Pages\ListItemControles;
use App\Filament\Resources\ItemControles\Pages\ListItemControlesAnexos;
use App\Filament\Resources\ItemControles\Pages\ListItemControlesAprovacoes;
use App\Filament\Resources\ItemControles\Pages\ListItemControlesAssinaturas;
use App\Filament\Resources\ItemControles\Pages\ListItemControlesChecklist;
use App\Filament\Resources\ItemControles\Pages\ListItemControlesTimeline;
use App\Filament\Resources\ItemControles\Schemas\ItemControleForm;
use App\Filament\Resources\ItemControles\Tables\ItemControlesTable;
use App\Models\ItemControle;
use App\Models\Responsavel;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Support\CachedSchema as DatabaseSchema;
use App\Support\PrazzuAccessControl;
use App\Filament\Resources\ItemControles\Pages\CentralContratos;
use App\Filament\Resources\ItemControles\Pages\RelatoriosInternos;



class ItemControleResource extends Resource
{
    protected static ?string $model = ItemControle::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-check-circle';

    protected static ?string $navigationLabel = 'Tarefas';

    protected static string|\UnitEnum|null $navigationGroup = 'Trabalho';

    protected static ?int $navigationSort = 1;

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function form(Schema $schema): Schema
    {
        return ItemControleForm::make($schema);
    }

    public static function table(Table $table): Table
    {
        return ItemControlesTable::make($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return static::getEloquentQueryForContext('geral');
    }

    public static function getEloquentQueryForContext(string $context = 'geral'): Builder
    {
        $user = Filament::auth()->user();

        $columns = [
            'id',
            'empresa_id',
            'responsavel_id',
            'titulo',
            'descricao',
            'tipo',
            'categoria_id',
            'status',
            'prioridade',
            'data_vencimento',
            'data_conclusao',
            'arquivo',
            'observacao',
            'portal_ativo',
            'portal_token',
            'portal_cliente_nome',
            'portal_cliente_email',
            'portal_expira_em',
            'created_at',
            'updated_at',
            'sla_horas',
            'sla_inicio_em',
            'sla_limite_em',
            'sla_concluido_em',
            'sla_status',
            'contrato_numero',
            'contrato_parte_nome',
            'contrato_parte_documento',
            'contrato_valor',
            'contrato_inicio_em',
            'contrato_fim_em',
            'contrato_status',
        ];

        foreach (['urgencia', 'valor_tarefa', 'bloqueado', 'faturado_em', 'pago_em', 'status_operacional_at'] as $column) {
            if (DatabaseSchema::hasColumn('item_controles', $column)) {
                $columns[] = $column;
            }
        }

        $with = [
            'empresa:id,razao_social',
            'responsavel:id,nome,user_id,gestor_user_id,empresa_id',
            'categoria:id,empresa_id,nome,cor,ativo',
        ];

        if ($context === 'geral') {
            $with[] = 'tags:id,empresa_id,nome,cor,ativo';
        }

        $withCount = match ($context) {
            'checklists' => [
                'checklists',
                'checklists as checklists_concluidos_count' => fn (Builder $query): Builder => $query->where('concluido', true),
            ],
            'timelines' => [
                'timelines',
            ],
            'assinaturas' => [
                'assinaturas',
            ],
            'aprovacoes' => [
                'aprovacoes',
                'notificacoesInternas',
            ],
            'anexos' => [
                'comentarios',
                'anexos',
            ],
            default => [],
        };

        if ($context === 'assinaturas') {
            $with[] = 'ultimaAssinatura';
        }

        if ($context === 'aprovacoes') {
            $with[] = 'ultimaAprovacao';
        }

        return parent::getEloquentQuery()
            ->select($columns)
            ->with($with)
            ->withCount($withCount)
            ->visibleForUser($user);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canViewAny();
    }

    public static function canViewAny(): bool
    {
        return PrazzuAccessControl::canUseWorkArea() && PrazzuAccessControl::can('tarefas.view');
    }

    public static function canCreate(): bool
    {
        $user = Filament::auth()->user();

        if (! $user || ! PrazzuAccessControl::can('tarefas.create', $user)) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        $empresa = $user->empresa;

        if (! $empresa || ! $empresa->isAtivo()) {
            return false;
        }

        if ($empresa->atingiuLimiteItens()) {
            return false;
        }

        return true;
    }

    public static function canEdit(Model $record): bool
    {
        $user = Filament::auth()->user();

        if (! $user || ! PrazzuAccessControl::can('tarefas.edit', $user)) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        $empresa = $user->empresa;

        if (! $empresa || ! $empresa->isAtivo()) {
            return false;
        }

        return $record instanceof ItemControle
            ? $record->canBeModifiedBy($user)
            : false;
    }

    public static function canDelete(Model $record): bool
    {
        $user = Filament::auth()->user();

        if (! $user || ! PrazzuAccessControl::can('tarefas.delete', $user)) {
            return false;
        }

        return $record instanceof ItemControle
            ? $record->canBeDeletedBy($user)
            : false;
    }

    public static function getDefaultEmpresaIdForUser(?User $user): ?int
    {
        if (! $user) {
            return null;
        }

        if ($user->isSuperAdmin()) {
            return null;
        }

        return $user->empresa_id;
    }

    public static function getDefaultResponsavelIdForUser(?User $user): ?int
    {
        if (! $user) {
            return null;
        }

        if ($user->isSuperAdmin()) {
            return null;
        }

        return $user->responsavel?->id;
    }

    public static function getResponsavelOptionsForUser(?User $user): array
    {
        if (! $user) {
            return [];
        }

        $query = Responsavel::query()
            ->select(['id', 'nome'])
            ->orderBy('nome')
            ->limit(50);

        if (! $user->isSuperAdmin()) {
            $query->where('empresa_id', $user->empresa_id);
        }

        return $query->pluck('nome', 'id')->toArray();
    }

    public static function canUserAssignResponsavel(?User $user, ?int $responsavelId): bool
    {
        if (! $user || ! $responsavelId) {
            return false;
        }

        $responsavel = Responsavel::query()
            ->select(['id', 'empresa_id', 'gestor_user_id'])
            ->find($responsavelId);

        if (! $responsavel) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        if (! $user->hasEmpresaVinculada()) {
            return false;
        }

        if ((int) $responsavel->empresa_id !== (int) $user->empresa_id) {
            return false;
        }

        if ($user->isAdminEmpresa()) {
            return true;
        }

        if ($user->isGestor()) {
            return (int) $responsavel->gestor_user_id === (int) $user->id;
        }

        if ($user->isUser()) {
            return (int) $responsavel->id === (int) $user->responsavel?->id;
        }

        return false;
    }

    public static function getNavigationUrl(): string
    {
        return static::getUrl('index');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListItemControles::route('/'),
            'checklists' => ListItemControlesChecklist::route('/checklists'),
            'timelines' => ListItemControlesTimeline::route('/timelines'),
            'assinaturas' => ListItemControlesAssinaturas::route('/assinaturas'),
            'aprovacoes' => ListItemControlesAprovacoes::route('/aprovacoes'),
            'anexos' => ListItemControlesAnexos::route('/anexos-comentarios'),
            'dashboard-graficos' => DashboardGraficosItemControles::route('/dashboard/graficos'),
            'dashboard-tabelas' => DashboardTabelasItemControles::route('/dashboard/tabelas'),
            'list' => ListItemControles::route('/list'),
            'create' => CreateItemControle::route('/create'),
            'edit' => EditItemControle::route('/{record}/edit'),
            'central-notificacoes' => CentralNotificacoes::route('/central-notificacoes'),
            'central-contratos' => CentralContratos::route('/central-contratos'),
            'relatorios-internos' => RelatoriosInternos::route('/relatorios-internos'),
        ];
    }
}
