<?php

namespace App\Filament\Resources\Configuracoes\Schemas;

use App\Models\Empresa;
use Filament\Facades\Filament;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ConfiguracaoForm
{
    public static function make(Schema $schema): Schema
    {
        $user = Filament::auth()->user();

        return $schema
            ->components([
                Section::make('Empresa e Notificações')
                    ->description('Parâmetros utilizados pelas rotinas automáticas de vencimento e lembretes.')
                    ->schema([
                        Select::make('empresa_id')
                            ->label('Empresa')
                            ->required()
                            ->searchable()
                            ->native(false)
                            ->disabled(fn (): bool => $user?->isSuperAdmin() !== true)
                            ->dehydrated(true)
                            ->getSearchResultsUsing(function (string $search) use ($user): array {
                                $query = Empresa::query()
                                    ->select(['id', 'razao_social', 'nome_fantasia', 'cnpj'])
                                    ->where(function ($builder) use ($search): void {
                                        $builder->where('razao_social', 'like', "%{$search}%")
                                            ->orWhere('nome_fantasia', 'like', "%{$search}%")
                                            ->orWhere('cnpj', 'like', "%{$search}%");
                                    });

                                if ($user?->isSuperAdmin()) {
                                    return $query
                                        ->orderBy('razao_social')
                                        ->limit(50)
                                        ->pluck('razao_social', 'id')
                                        ->toArray();
                                }

                                if (! $user?->hasEmpresaVinculada()) {
                                    return [];
                                }

                                return $query
                                    ->whereKey($user->empresa_id)
                                    ->orderBy('razao_social')
                                    ->limit(1)
                                    ->pluck('razao_social', 'id')
                                    ->toArray();
                            })
                            ->getOptionLabelUsing(fn ($value): ?string => blank($value) ? null : Empresa::find($value)?->razao_social),

                        TextInput::make('dias_alerta')
                            ->label('Dias para alerta antes do vencimento')
                            ->numeric()
                            ->minValue(0)
                            ->required(),

                        TextInput::make('dias_lembrete')
                            ->label('Intervalo de lembrete após vencido')
                            ->numeric()
                            ->minValue(1)
                            ->required(),

                        Toggle::make('enviar_email')
                            ->label('Enviar por e-mail')
                            ->default(true),

                        Toggle::make('enviar_sistema')
                            ->label('Enviar notificação no sistema')
                            ->default(true),
                    ])
                    ->columns(2),

                Section::make('Configurações avançadas')
                    ->description('Campos reais gravados na tabela configuracoes para os módulos consultarem.')
                    ->schema([
                        CheckboxList::make('modulos_ativos')
                            ->label('Módulos ativos')
                            ->options([
                                'controle_tempo' => 'Controle de tempo',
                                'metas' => 'Metas',
                                'prioridades' => 'Prioridades',
                                'mapas' => 'Mapas',
                                'documentos' => 'Documentos',
                                'financeiro' => 'Financeiro',
                                'portal_cliente' => 'Portal do cliente',
                                'auditoria' => 'Auditoria',
                                'relatorios' => 'Relatórios',
                            ])
                            ->columns(3),

                        CheckboxList::make('notificacoes_granulares')
                            ->label('Notificações granulares')
                            ->options([
                                'mencoes_email' => 'Menções por e-mail',
                                'mencoes_sistema' => 'Menções no sistema',
                                'vencimentos_email' => 'Vencimentos por e-mail',
                                'vencimentos_sistema' => 'Vencimentos no sistema',
                                'comentarios_email' => 'Comentários por e-mail',
                                'comentarios_sistema' => 'Comentários no sistema',
                                'alteracao_status_email' => 'Mudança de status por e-mail',
                                'alteracao_status_sistema' => 'Mudança de status no sistema',
                            ])
                            ->columns(2),

                        Select::make('tema')
                            ->label('Tema')
                            ->native(false)
                            ->options([
                                'system' => 'Usar padrão do dispositivo',
                                'light' => 'Claro',
                                'dark' => 'Escuro',
                            ]),

                        Select::make('visualizacao_padrao')
                            ->label('Visualização padrão')
                            ->native(false)
                            ->options([
                                'lista' => 'Lista',
                                'kanban' => 'Kanban',
                                'calendario' => 'Calendário',
                                'gantt' => 'Gantt',
                                'tabela' => 'Tabela',
                            ]),

                        TextInput::make('horas_semanais')
                            ->label('Horas semanais por pessoa')
                            ->numeric()
                            ->minValue(1),

                        TextInput::make('limite_capacidade')
                            ->label('Limite de capacidade (%)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(200),

                        Toggle::make('exigir_2fa')
                            ->label('Exigir 2FA'),

                        Toggle::make('registrar_login')
                            ->label('Registrar login'),
                    ])
                    ->columns(2),
            ]);
    }
}
