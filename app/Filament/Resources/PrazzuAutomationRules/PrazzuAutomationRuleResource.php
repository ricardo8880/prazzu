<?php

namespace App\Filament\Resources\PrazzuAutomationRules;

use App\Filament\Resources\PrazzuAutomationRules\Pages\CreatePrazzuAutomationRule;
use App\Filament\Resources\PrazzuAutomationRules\Pages\EditPrazzuAutomationRule;
use App\Filament\Resources\PrazzuAutomationRules\Pages\ListPrazzuAutomationRules;
use App\Models\PrazzuAutomationRule;
use App\Support\CachedSchema;
use App\Support\PrazzuAccessControl;
use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class PrazzuAutomationRuleResource extends Resource
{
    protected static ?string $model = PrazzuAutomationRule::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-bolt';

    protected static string | UnitEnum | null $navigationGroup = 'Configurações';

    protected static ?string $navigationLabel = 'Automações úteis';

    protected static ?string $modelLabel = 'Regra de automação';

    protected static ?string $pluralModelLabel = 'Automações úteis';

    protected static ?int $navigationSort = 8;


    public static function shouldRegisterNavigation(): bool
    {
        return PrazzuAccessControl::canAccessPage('governanca.view') && CachedSchema::hasTable('prazzu_automation_rules');
    }

    public static function canAccess(): bool
    {
        return PrazzuAccessControl::canAccessPage('governanca.view') && CachedSchema::hasTable('prazzu_automation_rules');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Regra real de automação')
                ->description('Configure regras SE/ENTÃO usando dados reais dos itens, documentos, aprovações e assinaturas.')
                ->schema([
                    TextInput::make('name')
                        ->label('Nome da regra')
                        ->required()
                        ->maxLength(255),

                    Select::make('module')
                        ->label('Módulo')
                        ->required()
                        ->native(false)
                        ->default('item_controles')
                        ->options(self::moduleOptions()),

                    Select::make('trigger_type')
                        ->label('Gatilho')
                        ->required()
                        ->native(false)
                        ->default('manual')
                        ->options(self::triggerOptions())
                        ->helperText('Use os gatilhos prontos para vencimento, aprovação, assinatura e cobrança.'),

                    Select::make('condition_field')
                        ->label('Campo analisado')
                        ->required()
                        ->native(false)
                        ->default('status')
                        ->options(self::fieldOptions()),

                    Select::make('condition_operator')
                        ->label('Operador')
                        ->required()
                        ->native(false)
                        ->default('=')
                        ->options(self::operatorOptions()),

                    TextInput::make('condition_value')
                        ->label('Valor da condição')
                        ->maxLength(255)
                        ->helperText('Para vencimento em breve, informe a quantidade de dias. Ex.: 30.'),

                    Select::make('action_type')
                        ->label('Ação')
                        ->required()
                        ->native(false)
                        ->default('notificacao')
                        ->options(self::actionOptions()),

                    TextInput::make('action_value')
                        ->label('Mensagem / valor da ação')
                        ->maxLength(1000)
                        ->helperText('Ex.: Documento vencendo. Regularize antes do prazo.'),

                    Toggle::make('active')
                        ->label('Regra ativa')
                        ->default(true),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->striped()
            ->columns([
                TextColumn::make('name')
                    ->label('Regra')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('trigger_type')
                    ->label('Gatilho')
                    ->formatStateUsing(fn (?string $state): string => self::triggerOptions()[$state] ?? str($state)->headline()->toString())
                    ->badge()
                    ->color('info')
                    ->sortable(),

                TextColumn::make('condition_field')
                    ->label('SE')
                    ->formatStateUsing(fn ($state, PrazzuAutomationRule $record): string => trim(($state ?: 'status') . ' ' . ($record->condition_operator ?: '=') . ' ' . ($record->condition_value ?: '-')))
                    ->wrap(),

                TextColumn::make('action_type')
                    ->label('ENTÃO')
                    ->formatStateUsing(fn (?string $state): string => self::actionOptions()[$state] ?? str($state)->headline()->toString())
                    ->badge()
                    ->color('success'),

                IconColumn::make('active')
                    ->label('Ativa')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Atualizada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('active')
                    ->label('Status')
                    ->options([1 => 'Ativas', 0 => 'Inativas']),
                SelectFilter::make('trigger_type')
                    ->label('Gatilho')
                    ->options(self::triggerOptions()),
            ])
            ->recordActions([
                EditAction::make()->label('Editar')->icon('heroicon-o-pencil-square')->color('gray'),
                ActionGroup::make([
                    DeleteAction::make()->label('Excluir')->icon('heroicon-o-trash')->color('danger'),
                ])->label('Mais')->icon('heroicon-m-ellipsis-vertical')->color('gray')->button(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->select(['id', 'name', 'module', 'trigger_type', 'condition_field', 'condition_operator', 'condition_value', 'action_type', 'action_value', 'active', 'created_at', 'updated_at']);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPrazzuAutomationRules::route('/'),
            'create' => CreatePrazzuAutomationRule::route('/create'),
            'edit' => EditPrazzuAutomationRule::route('/{record}/edit'),
        ];
    }

    public static function triggerOptions(): array
    {
        return [
            'manual' => 'Condição simples',
            'documento_vencendo' => 'Documento vencendo',
            'documento_vencido' => 'Documento vencido',
            'aprovacao_pendente' => 'Aprovação pendente',
            'assinatura_pendente' => 'Assinatura pendente',
        ];
    }

    public static function moduleOptions(): array
    {
        return [
            'item_controles' => 'Itens / documentos',
            'global' => 'Todos os itens de trabalho',
            'pendencias' => 'Pendências operacionais',
        ];
    }

    public static function fieldOptions(): array
    {
        return [
            'status' => 'Status',
            'data_vencimento' => 'Data de vencimento',
            'approval_status' => 'Status de aprovação',
            'signature_status' => 'Status de assinatura',
            'prioridade' => 'Prioridade',
            'document_status' => 'Status documental',
            'sla_status' => 'Status de SLA',
        ];
    }

    public static function operatorOptions(): array
    {
        return [
            '=' => 'Igual a',
            '!=' => 'Diferente de',
            'contains' => 'Contém',
            'empty' => 'Está vazio',
            'not_empty' => 'Está preenchido',
            'date_until' => 'Vence em até X dias',
            'date_overdue' => 'Está vencido',
            'date_before' => 'Data antes de',
            'date_after' => 'Data depois de',
        ];
    }

    public static function actionOptions(): array
    {
        return [
            'notificacao' => 'Criar notificação',
            'cobrar_responsavel' => 'Cobrar responsável',
            'timeline' => 'Registrar no histórico',
            'criar_pendencia' => 'Criar pendência',
            'status' => 'Alterar status da tarefa',
            'prioridade' => 'Alterar prioridade',
            'document_status' => 'Alterar status documental',
            'approval_status' => 'Alterar aprovação',
            'signature_status' => 'Alterar assinatura',
        ];
    }
}
