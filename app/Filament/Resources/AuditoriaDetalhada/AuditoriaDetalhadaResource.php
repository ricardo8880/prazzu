<?php

namespace App\Filament\Resources\AuditoriaDetalhada;

use App\Filament\Resources\AuditoriaDetalhada\Pages\ListAuditoriaDetalhada;
use App\Filament\Resources\AuditoriaDetalhada\Pages\VisualizarAuditoriaDetalhada;
use App\Models\AuditoriaDetalhada;
use App\Models\Empresa;
use App\Models\User;
use App\Services\AuditoriaAccessService;
use App\Support\AuditoriaFormatter;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class AuditoriaDetalhadaResource extends Resource
{
    protected static ?string $model = AuditoriaDetalhada::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationLabel = 'Auditoria Detalhada';

    protected static string | UnitEnum | null $navigationGroup = 'Relatórios e Auditoria';

    protected static ?int $navigationSort = 9;

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return 'Relatórios e Auditoria';
    }

    public static function canAccess(): bool
    {
        return app(AuditoriaAccessService::class)->canView(Filament::auth()->user());
    }

    public static function canViewAny(): bool
    {
        return static::canAccess();
    }

    public static function canView($record): bool
    {
        return $record instanceof AuditoriaDetalhada
            ? app(AuditoriaAccessService::class)->canViewRecord($record, Filament::auth()->user())
            : static::canAccess();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->columns([
                TextColumn::make('created_at')
                    ->label('Quando')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),

                TextColumn::make('empresa.razao_social')
                    ->label('Empresa')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('user.name')
                    ->label('Quem alterou')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('evento')
                    ->label('Tipo de ação')
                    ->badge()
                    ->formatStateUsing(fn ($state) => AuditoriaFormatter::evento($state))
                    ->color(fn ($state) => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('risco_operacional')
                    ->label('Risco')
                    ->state(fn (AuditoriaDetalhada $record): string => self::isSuspeito($record) ? 'Atenção' : 'Normal')
                    ->badge()
                    ->color(fn (string $state): string => $state === 'Atenção' ? 'danger' : 'gray'),

                TextColumn::make('auditable_type')
                    ->label('Área do sistema')
                    ->formatStateUsing(fn ($state) => AuditoriaFormatter::modulo($state))
                    ->searchable(),

                TextColumn::make('auditable_id')
                    ->label('Item alterado')
                    ->state(fn (AuditoriaDetalhada $record): string => AuditoriaFormatter::registroCurto($record->auditable_type, $record->auditable_id))
                    ->sortable(),

                TextColumn::make('campo')
                    ->label('Campo alterado')
                    ->formatStateUsing(fn ($state) => AuditoriaFormatter::campo($state))
                    ->searchable(),

                TextColumn::make('valor_anterior')
                    ->label('Valor anterior')
                    ->state(fn (AuditoriaDetalhada $record): string => AuditoriaFormatter::valor($record->valor_anterior, $record->campo, 45))
                    ->toggleable(),

                TextColumn::make('valor_novo')
                    ->label('Valor novo')
                    ->state(fn (AuditoriaDetalhada $record): string => AuditoriaFormatter::valor($record->valor_novo, $record->campo, 45))
                    ->toggleable(),

                TextColumn::make('ip')
                    ->label('IP')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('evento')
                    ->label('Tipo de ação')
                    ->options([
                        'created' => 'Criado',
                        'updated' => 'Alterado',
                        'deleted' => 'Excluído',
                        'login.success' => 'Login realizado',
                        'login.failed' => 'Falha de login',
                        'logout' => 'Logout',
                        'password.reset' => 'Senha redefinida',
                        'auditoria.exported' => 'Auditoria exportada',
                        'asaas.webhook.received' => 'Webhook Asaas recebido',
                        'asaas.webhook.processed' => 'Webhook Asaas processado',
                        'asaas.webhook.rejected' => 'Webhook Asaas rejeitado',
                        'asaas.webhook.failed' => 'Falha no webhook Asaas',
                    ]),

                SelectFilter::make('user_id')
                    ->label('Quem alterou')
                    ->options(fn (): array => User::query()
                        ->whereIn('id', self::getEloquentQuery()->whereNotNull('user_id')->distinct()->pluck('user_id'))
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->searchable(),

                SelectFilter::make('empresa_id')
                    ->label('Empresa')
                    ->options(fn (): array => Empresa::query()
                        ->whereIn('id', self::getEloquentQuery()->whereNotNull('empresa_id')->distinct()->pluck('empresa_id'))
                        ->orderBy('razao_social')
                        ->get(['id', 'razao_social', 'nome_fantasia'])
                        ->mapWithKeys(fn (Empresa $empresa): array => [
                            $empresa->id => $empresa->razao_social ?: $empresa->nome_fantasia ?: 'Empresa #' . $empresa->id,
                        ])
                        ->all())
                    ->searchable(),

                SelectFilter::make('auditable_type')
                    ->label('Área do sistema')
                    ->options(fn (): array => self::getEloquentQuery()
                        ->whereNotNull('auditable_type')
                        ->select('auditable_type')
                        ->distinct()
                        ->orderBy('auditable_type')
                        ->pluck('auditable_type')
                        ->mapWithKeys(fn ($tipo): array => [$tipo => AuditoriaFormatter::modulo((string) $tipo)])
                        ->all())
                    ->searchable(),

                Filter::make('acoes_sensiveis')
                    ->label('Ações sensíveis')
                    ->query(fn (Builder $query): Builder => $query->where(function (Builder $subQuery): void {
                        $subQuery
                            ->where('evento', 'deleted')
                            ->orWhere('campo', 'like', '%password%')
                            ->orWhere('campo', 'like', '%senha%')
                            ->orWhere('campo', 'like', '%role%')
                            ->orWhere('campo', 'like', '%permiss%')
                            ->orWhere('campo', 'like', '%status%');
                    })),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $user = Filament::auth()->user();

        return parent::getEloquentQuery()
            ->with([
                'empresa:id,razao_social,nome_fantasia',
                'user:id,name',
            ])
            ->visibleForUser($user);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => VisualizarAuditoriaDetalhada::route('/'),
            'gerenciar' => ListAuditoriaDetalhada::route('/gerenciar'),
        ];
    }

    private static function eventoLabel(?string $evento): string
    {
        return AuditoriaFormatter::evento($evento);
    }

    private static function isSuspeito(AuditoriaDetalhada $record): bool
    {
        $campo = mb_strtolower((string) $record->campo);

        return AuditoriaFormatter::isSuspeito($record);
    }
}
