<?php

namespace App\Filament\Pages;

use App\Models\Configuracao;
use App\Models\Empresa;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Arr;
use UnitEnum;

class Configuracoes extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static string | UnitEnum | null $navigationGroup = 'Configurações';
    protected static ?string $navigationLabel = 'Configurações';
    protected static ?string $title = 'Configurações';
    protected static ?int $navigationSort = 2;
    protected string $view = 'filament.pages.configuracoes';

    public ?array $data = [];

    public function mount(): void
    {
        $configuracao = $this->getConfiguracaoAtual()->aplicarDefaultsFaltantes();

        $this->form->fill($this->normalizarParaFormulario($configuracao));
    }

    public function form(Schema $form): Schema
    {
        $user = Filament::auth()->user();

        return $form
            ->schema([
                Section::make('Empresa e notificações de vencimento')
                    ->description('Essas opções já são usadas pelo comando de notificação de vencimentos do sistema.')
                    ->schema([
                        Forms\Components\Select::make('empresa_id')
                            ->label('Empresa')
                            ->required()
                            ->searchable()
                            ->native(false)
                            ->disabled(fn (): bool => $user?->isSuperAdmin() !== true)
                            ->dehydrated(true)
                            ->options(fn (): array => Empresa::query()
                                ->orderBy('razao_social')
                                ->limit(100)
                                ->pluck('razao_social', 'id')
                                ->toArray()),

                        Forms\Components\TextInput::make('dias_alerta')
                            ->label('Dias para alerta antes do vencimento')
                            ->numeric()
                            ->minValue(0)
                            ->required(),

                        Forms\Components\TextInput::make('dias_lembrete')
                            ->label('Intervalo de lembrete após vencido')
                            ->numeric()
                            ->minValue(1)
                            ->required(),

                        Forms\Components\Toggle::make('enviar_email')
                            ->label('Enviar alertas por e-mail'),

                        Forms\Components\Toggle::make('enviar_sistema')
                            ->label('Enviar alertas no sistema'),
                    ])
                    ->columns(2),

                Section::make('ClickApps e módulos ativos')
                    ->description('Controle quais módulos aparecem como recursos disponíveis para a empresa.')
                    ->schema([
                        Forms\Components\CheckboxList::make('modulos_ativos')
                            ->label('Funcionalidades habilitadas')
                            ->options($this->opcoesModulos())
                            ->columns(3)
                            ->bulkToggleable(),
                    ]),

                Section::make('Workflow, campos personalizados e templates')
                    ->description('Configure o comportamento padrão das tarefas, listas e estruturas reaproveitáveis.')
                    ->schema([
                        Forms\Components\Textarea::make('workflow_status_texto')
                            ->label('Status do workflow')
                            ->rows(5)
                            ->helperText('Um status por linha. Exemplo: A fazer, Em andamento, Concluído.'),

                        Forms\Components\Textarea::make('campos_personalizados_texto')
                            ->label('Campos personalizados')
                            ->rows(5)
                            ->helperText('Um campo por linha. Exemplo: Valor previsto: moeda.'),

                        Forms\Components\Textarea::make('templates_estrutura_texto')
                            ->label('Templates de estrutura')
                            ->rows(5)
                            ->helperText('Um template por linha.'),
                    ])
                    ->columns(3),

                Section::make('Central de notificações granular')
                    ->description('Define quando a empresa recebe alerta por e-mail ou pelo sistema.')
                    ->schema([
                        Forms\Components\CheckboxList::make('notificacoes_granulares')
                            ->label('Eventos habilitados')
                            ->options($this->opcoesNotificacoes())
                            ->columns(2)
                            ->bulkToggleable(),
                    ]),

                Section::make('Aparência, layout e visualização padrão')
                    ->description('Preferências visuais usadas como padrão da empresa.')
                    ->schema([
                        Forms\Components\Select::make('tema')
                            ->label('Tema')
                            ->native(false)
                            ->options([
                                'system' => 'Usar padrão do dispositivo',
                                'light' => 'Claro',
                                'dark' => 'Escuro',
                            ])
                            ->required(),

                        Forms\Components\Select::make('cor_tema')
                            ->label('Cor principal')
                            ->native(false)
                            ->options([
                                'blue' => 'Azul',
                                'green' => 'Verde',
                                'purple' => 'Roxo',
                                'orange' => 'Laranja',
                                'gray' => 'Cinza',
                            ])
                            ->required(),

                        Forms\Components\Select::make('tamanho_fonte')
                            ->label('Tamanho da fonte')
                            ->native(false)
                            ->options([
                                'small' => 'Compacta',
                                'normal' => 'Normal',
                                'large' => 'Grande',
                            ])
                            ->required(),

                        Forms\Components\Select::make('layout_sidebar')
                            ->label('Layout da barra lateral')
                            ->native(false)
                            ->options([
                                'expanded' => 'Expandida',
                                'compact' => 'Compacta',
                                'hidden' => 'Oculta por padrão',
                            ])
                            ->required(),

                        Forms\Components\Select::make('visualizacao_padrao')
                            ->label('Visualização padrão')
                            ->native(false)
                            ->options([
                                'lista' => 'Lista',
                                'kanban' => 'Kanban',
                                'calendario' => 'Calendário',
                                'gantt' => 'Gantt',
                                'tabela' => 'Tabela',
                            ])
                            ->required(),
                    ])
                    ->columns(3),

                Section::make('Automações de fluxo')
                    ->description('Ative gatilhos padrões para reduzir ações manuais.')
                    ->schema([
                        Forms\Components\CheckboxList::make('automacoes_fluxo')
                            ->label('Automações ativas')
                            ->options($this->opcoesAutomacoes())
                            ->columns(2)
                            ->bulkToggleable(),
                    ]),

                Section::make('Permissões e níveis de acesso')
                    ->description('Define quem pode atuar em listas, pastas e espaços.')
                    ->schema([
                        Forms\Components\Select::make('permissao_listas')
                            ->label('Listas')
                            ->native(false)
                            ->options($this->opcoesPermissao())
                            ->required(),

                        Forms\Components\Select::make('permissao_pastas')
                            ->label('Pastas')
                            ->native(false)
                            ->options($this->opcoesPermissao())
                            ->required(),

                        Forms\Components\Select::make('permissao_espacos')
                            ->label('Espaços de trabalho')
                            ->native(false)
                            ->options($this->opcoesPermissao())
                            ->required(),
                    ])
                    ->columns(3),

                Section::make('Integrações de terceiros')
                    ->description('Centraliza conexões externas disponíveis para a empresa.')
                    ->schema([
                        Forms\Components\CheckboxList::make('integracoes_terceiros')
                            ->label('Integrações habilitadas')
                            ->options([
                                'slack' => 'Slack',
                                'zoom' => 'Zoom',
                                'google_calendar' => 'Google Calendar',
                                'google_drive' => 'Google Drive',
                                'onedrive' => 'OneDrive',
                                'clicksign' => 'Clicksign',
                                'asaas' => 'Asaas',
                            ])
                            ->columns(3)
                            ->bulkToggleable(),

                        Forms\Components\TextInput::make('sso_provider')
                            ->label('Provedor SSO')
                            ->placeholder('Ex: Google Workspace, Azure AD, Okta'),
                    ]),

                Section::make('Time e carga de trabalho')
                    ->description('Parâmetros usados para capacidade, feriados e planejamento.')
                    ->schema([
                        Forms\Components\TextInput::make('horas_semanais')
                            ->label('Horas semanais por pessoa')
                            ->numeric()
                            ->minValue(1)
                            ->required(),

                        Forms\Components\TextInput::make('limite_capacidade')
                            ->label('Limite de capacidade (%)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(200)
                            ->required(),

                        Forms\Components\Textarea::make('feriados_texto')
                            ->label('Feriados')
                            ->rows(4)
                            ->helperText('Um feriado por linha. Exemplo: 2026-12-25 - Natal.'),
                    ])
                    ->columns(3),

                Section::make('Segurança e autenticação')
                    ->description('Configurações de proteção e rastreabilidade da conta.')
                    ->schema([
                        Forms\Components\Toggle::make('exigir_2fa')
                            ->label('Exigir 2FA para usuários da empresa'),

                        Forms\Components\Toggle::make('registrar_login')
                            ->label('Registrar atividades de login'),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function salvar(): void
    {
        $user = Filament::auth()->user();

        if (! $user || (! $user->isSuperAdmin() && ! $user->isAdminEmpresa())) {
            abort(403, 'Você não tem permissão para alterar configurações.');
        }

        $data = $this->form->getState();
        $empresaId = (int) ($data['empresa_id'] ?? 0);

        if ($user->isAdminEmpresa()) {
            $empresaId = (int) $user->empresa_id;
        }

        if ($empresaId <= 0) {
            Notification::make()
                ->title('Selecione uma empresa antes de salvar.')
                ->danger()
                ->send();

            return;
        }

        $configuracao = Configuracao::forEmpresaId($empresaId);
        $configuracao->fill($this->normalizarParaBanco($data, $empresaId));
        $configuracao->save();

        $this->form->fill($this->normalizarParaFormulario($configuracao->fresh()->aplicarDefaultsFaltantes()));

        Notification::make()
            ->title('Configurações salvas com sucesso.')
            ->body('As preferências foram gravadas e já ficam disponíveis para os módulos consultarem.')
            ->success()
            ->send();
    }

    public function restaurarPadrao(): void
    {
        $data = $this->form->getState();
        $empresaId = (int) ($data['empresa_id'] ?? $this->getConfiguracaoAtual()->empresa_id);

        $defaults = Configuracao::defaults();
        $defaults['empresa_id'] = $empresaId;

        $this->form->fill($this->normalizarParaFormulario(new Configuracao($defaults)));

        Notification::make()
            ->title('Padrões carregados.')
            ->body('Clique em Salvar configurações para gravar esses padrões no banco.')
            ->info()
            ->send();
    }

    public function getResumoConfiguracoesProperty(): array
    {
        $data = $this->data ?? [];

        return [
            'Módulos ativos' => count($data['modulos_ativos'] ?? []),
            'Automações ativas' => count($data['automacoes_fluxo'] ?? []),
            'Integrações ativas' => count($data['integracoes_terceiros'] ?? []),
            'Visualização padrão' => strtoupper((string) ($data['visualizacao_padrao'] ?? 'lista')),
        ];
    }

    protected function getConfiguracaoAtual(): Configuracao
    {
        $user = Filament::auth()->user();

        if (! $user) {
            abort(403, 'Usuário não autenticado.');
        }

        if ($user->isSuperAdmin()) {
            $empresa = Empresa::query()->orderBy('id')->first();

            if (! $empresa) {
                abort(404, 'Cadastre uma empresa antes de acessar as configurações.');
            }

            return Configuracao::forEmpresaId($empresa->id);
        }

        if (! $user->isAdminEmpresa()) {
            abort(403, 'Somente administrador da empresa pode alterar configurações.');
        }

        if (! $user->hasEmpresaVinculada()) {
            abort(403, 'Seu usuário não possui empresa vinculada.');
        }

        return Configuracao::forEmpresaId($user->empresa_id);
    }

    protected function normalizarParaFormulario(Configuracao $configuracao): array
    {
        return [
            'empresa_id' => $configuracao->empresa_id,
            'dias_alerta' => $configuracao->dias_alerta,
            'dias_lembrete' => $configuracao->dias_lembrete,
            'enviar_email' => (bool) $configuracao->enviar_email,
            'enviar_sistema' => (bool) $configuracao->enviar_sistema,
            'modulos_ativos' => $configuracao->modulos_ativos ?? [],
            'workflow_status_texto' => $this->arrayParaTexto($configuracao->workflow_status),
            'campos_personalizados_texto' => $this->arrayParaTexto($configuracao->campos_personalizados),
            'notificacoes_granulares' => $configuracao->notificacoes_granulares ?? [],
            'tema' => $configuracao->tema ?: 'system',
            'cor_tema' => $configuracao->cor_tema ?: 'blue',
            'tamanho_fonte' => $configuracao->tamanho_fonte ?: 'normal',
            'layout_sidebar' => $configuracao->layout_sidebar ?: 'expanded',
            'automacoes_fluxo' => $configuracao->automacoes_fluxo ?? [],
            'permissao_listas' => Arr::get($configuracao->permissoes_acesso ?? [], 'listas', 'admin_gestor'),
            'permissao_pastas' => Arr::get($configuracao->permissoes_acesso ?? [], 'pastas', 'admin_gestor'),
            'permissao_espacos' => Arr::get($configuracao->permissoes_acesso ?? [], 'espacos', 'admin'),
            'integracoes_terceiros' => $configuracao->integracoes_terceiros ?? [],
            'horas_semanais' => $configuracao->horas_semanais ?: 44,
            'feriados_texto' => $this->arrayParaTexto($configuracao->feriados),
            'limite_capacidade' => $configuracao->limite_capacidade ?: 100,
            'templates_estrutura_texto' => $this->arrayParaTexto($configuracao->templates_estrutura),
            'visualizacao_padrao' => $configuracao->visualizacao_padrao ?: 'lista',
            'exigir_2fa' => (bool) $configuracao->exigir_2fa,
            'sso_provider' => $configuracao->sso_provider,
            'registrar_login' => (bool) $configuracao->registrar_login,
        ];
    }

    protected function normalizarParaBanco(array $data, int $empresaId): array
    {
        return [
            'empresa_id' => $empresaId,
            'dias_alerta' => (int) ($data['dias_alerta'] ?? 3),
            'dias_lembrete' => max(1, (int) ($data['dias_lembrete'] ?? 2)),
            'enviar_email' => (bool) ($data['enviar_email'] ?? false),
            'enviar_sistema' => (bool) ($data['enviar_sistema'] ?? false),
            'modulos_ativos' => array_values($data['modulos_ativos'] ?? []),
            'workflow_status' => $this->textoParaArray($data['workflow_status_texto'] ?? ''),
            'campos_personalizados' => $this->textoParaArray($data['campos_personalizados_texto'] ?? ''),
            'notificacoes_granulares' => array_values($data['notificacoes_granulares'] ?? []),
            'tema' => $data['tema'] ?? 'system',
            'cor_tema' => $data['cor_tema'] ?? 'blue',
            'tamanho_fonte' => $data['tamanho_fonte'] ?? 'normal',
            'layout_sidebar' => $data['layout_sidebar'] ?? 'expanded',
            'automacoes_fluxo' => array_values($data['automacoes_fluxo'] ?? []),
            'permissoes_acesso' => [
                'listas' => $data['permissao_listas'] ?? 'admin_gestor',
                'pastas' => $data['permissao_pastas'] ?? 'admin_gestor',
                'espacos' => $data['permissao_espacos'] ?? 'admin',
            ],
            'integracoes_terceiros' => array_values($data['integracoes_terceiros'] ?? []),
            'horas_semanais' => max(1, (int) ($data['horas_semanais'] ?? 44)),
            'feriados' => $this->textoParaArray($data['feriados_texto'] ?? ''),
            'limite_capacidade' => max(1, (int) ($data['limite_capacidade'] ?? 100)),
            'templates_estrutura' => $this->textoParaArray($data['templates_estrutura_texto'] ?? ''),
            'visualizacao_padrao' => $data['visualizacao_padrao'] ?? 'lista',
            'exigir_2fa' => (bool) ($data['exigir_2fa'] ?? false),
            'sso_provider' => filled($data['sso_provider'] ?? null) ? $data['sso_provider'] : null,
            'registrar_login' => (bool) ($data['registrar_login'] ?? true),
        ];
    }

    protected function textoParaArray(?string $texto): array
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $texto))
            ->map(fn (string $linha): string => trim($linha))
            ->filter()
            ->values()
            ->all();
    }

    protected function arrayParaTexto(?array $valores): string
    {
        return implode(PHP_EOL, array_values($valores ?? []));
    }

    protected function opcoesModulos(): array
    {
        return [
            'controle_tempo' => 'Controle de tempo',
            'metas' => 'Metas',
            'prioridades' => 'Prioridades',
            'mapas' => 'Mapas',
            'documentos' => 'Documentos',
            'financeiro' => 'Financeiro',
            'portal_cliente' => 'Portal do cliente',
            'auditoria' => 'Auditoria',
            'relatorios' => 'Relatórios',
        ];
    }

    protected function opcoesNotificacoes(): array
    {
        return [
            'mencoes_email' => 'Menções por e-mail',
            'mencoes_sistema' => 'Menções no sistema',
            'vencimentos_email' => 'Vencimentos por e-mail',
            'vencimentos_sistema' => 'Vencimentos no sistema',
            'comentarios_email' => 'Comentários por e-mail',
            'comentarios_sistema' => 'Comentários no sistema',
            'alteracao_status_email' => 'Mudança de status por e-mail',
            'alteracao_status_sistema' => 'Mudança de status no sistema',
        ];
    }

    protected function opcoesAutomacoes(): array
    {
        return [
            'notificar_status_pronto' => 'Quando status mudar para Pronto, notificar responsável',
            'marcar_risco_sla' => 'Quando SLA vencer, marcar como risco',
            'arquivar_concluidos' => 'Arquivar tarefas concluídas automaticamente',
            'cobrar_documento_vencido' => 'Cobrar documento vencido',
            'bloquear_portal_inadimplente' => 'Bloquear portal em caso de inadimplência',
            'gerar_checklist_template' => 'Gerar checklist ao aplicar template',
        ];
    }

    protected function opcoesPermissao(): array
    {
        return [
            'todos' => 'Todos podem visualizar e editar',
            'admin_gestor' => 'Admin e gestor podem editar',
            'admin' => 'Somente admin pode editar',
            'somente_visualizacao' => 'Usuários comuns somente visualizam',
        ];
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user?->isSuperAdmin() === true || $user?->isAdminEmpresa() === true;
    }
}
