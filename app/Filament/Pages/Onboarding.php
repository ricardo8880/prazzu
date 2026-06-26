<?php

namespace App\Filament\Pages;


use App\Support\CachedSchema;
use App\Models\Configuracao;
use App\Models\Empresa;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Schema;
use UnitEnum;

class Onboarding extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-rocket-launch';
    protected static string | UnitEnum | null $navigationGroup = 'Conta';
    protected static ?string $navigationLabel = 'Onboarding';
    protected static ?string $title = 'Onboarding';
    protected static ?int $navigationSort = 0;
    protected string $view = 'filament.pages.onboarding';

    public array $data = [];

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public function mount(): void
    {
        $configuracao = $this->getConfiguracaoAtual()->aplicarDefaultsFaltantes();

        $this->data = [
            'empresa_id' => $configuracao->empresa_id,
            'onboarding_progresso' => $configuracao->onboarding_progresso ?? [],
            'onboarding_recursos' => $configuracao->onboarding_recursos ?? [],
            'onboarding_preferencias' => array_merge($this->preferenciasPadrao(), $configuracao->onboarding_preferencias ?? []),
            'modulos_ativos' => $configuracao->modulos_ativos ?? [],
            'templates_estrutura' => $configuracao->templates_estrutura ?? [],
            'workflow_status' => $configuracao->workflow_status ?? [],
            'campos_personalizados' => $configuracao->campos_personalizados ?? [],
            'visualizacao_padrao' => $configuracao->visualizacao_padrao ?: 'lista',
            'finalizado_em' => $configuracao->onboarding_finalizado_em,
        ];
    }

    public function toggleEtapa(string $codigo): void
    {
        $this->exigirPermissaoConfiguracao();

        $progresso = $this->data['onboarding_progresso'] ?? [];
        $progresso[$codigo] = ! (bool) ($progresso[$codigo] ?? false);
        $this->data['onboarding_progresso'] = $progresso;

        $this->persistirOnboarding('Etapa atualizada.');
    }

    public function toggleRecurso(string $codigo): void
    {
        $this->exigirPermissaoConfiguracao();

        $recursos = $this->data['onboarding_recursos'] ?? [];
        $recursos[$codigo] = ! (bool) ($recursos[$codigo] ?? false);
        $this->data['onboarding_recursos'] = $recursos;

        $modulos = $this->data['modulos_ativos'] ?? [];
        if ($recursos[$codigo]) {
            $modulos[] = $codigo;
        } else {
            $modulos = array_values(array_filter($modulos, fn (string $item): bool => $item !== $codigo));
        }

        $this->data['modulos_ativos'] = array_values(array_unique($modulos));
        $this->persistirOnboarding('Recurso atualizado e refletido nos módulos ativos.');
    }

    public function aplicarModelo(string $codigo): void
    {
        $this->exigirPermissaoConfiguracao();

        $modelo = collect($this->modelos())->firstWhere('codigo', $codigo);

        if (! $modelo) {
            return;
        }

        $this->data['templates_estrutura'] = array_values(array_unique(array_merge($this->data['templates_estrutura'] ?? [], [$modelo['titulo']])));
        $this->data['workflow_status'] = $modelo['workflow'];
        $this->data['campos_personalizados'] = $modelo['campos'];
        $this->data['visualizacao_padrao'] = $modelo['visualizacao'];
        $this->data['onboarding_preferencias']['modelo_aplicado'] = $modelo['titulo'];

        $this->persistirOnboarding('Modelo aplicado ao workflow, campos personalizados, templates e visualização padrão.');
    }

    public function habilitarRecursosBase(): void
    {
        $this->exigirPermissaoConfiguracao();

        $codigos = collect($this->recursos())->pluck('codigo')->all();

        $this->data['onboarding_recursos'] = array_fill_keys($codigos, true);
        $this->data['modulos_ativos'] = array_values(array_unique(array_merge($this->data['modulos_ativos'] ?? [], $codigos)));

        $this->persistirOnboarding('Todos os recursos do onboarding foram habilitados.');
    }

    public function salvarPreferencias(): void
    {
        $this->exigirPermissaoConfiguracao();
        $this->persistirOnboarding('Preferências do onboarding salvas.');
    }

    public function finalizarOnboarding(): void
    {
        $this->exigirPermissaoConfiguracao();

        $this->data['finalizado_em'] = now()->toDateTimeString();
        $this->persistirOnboarding('Onboarding marcado como finalizado.');
    }

    public function getEtapasProperty(): array
    {
        $progresso = $this->data['onboarding_progresso'] ?? [];

        return collect($this->etapasBase())->map(function (array $etapa) use ($progresso): array {
            $etapa['feito'] = (bool) ($progresso[$etapa['codigo']] ?? $etapa['feito_auto']);

            return $etapa;
        })->all();
    }



    public function getGuiaPrimeirosPassosProperty(): array
    {
        return [
            [
                'numero' => '1',
                'titulo' => 'Comece pela estrutura',
                'descricao' => 'Defina a hierarquia de trabalho antes de cadastrar muitos itens. Isso evita listas confusas e retrabalho.',
            ],
            [
                'numero' => '2',
                'titulo' => 'Ative só o necessário',
                'descricao' => 'Habilite recursos úteis para a operação atual. O restante pode ser ativado depois, sem poluir a navegação.',
            ],
            [
                'numero' => '3',
                'titulo' => 'Aplique um modelo real',
                'descricao' => 'Use um template para preencher workflow, campos e visualização inicial com padrão consistente.',
            ],
            [
                'numero' => '4',
                'titulo' => 'Finalize e acompanhe',
                'descricao' => 'Quando tudo estiver configurado, marque o onboarding como finalizado e acompanhe os indicadores nas páginas principais.',
            ],
        ];
    }

    public function getDicasPrincipaisProperty(): array
    {
        return [
            'Home' => 'Use a Home para identificar o que precisa de ação hoje antes de abrir relatórios ou telas avançadas.',
            'Pendências' => 'Cadastre pendências com responsável, prioridade e prazo para evitar tarefas soltas.',
            'Documentos' => 'Documentos sem anexo ou vencidos devem ser tratados primeiro, pois impactam portal, aprovação e cobrança.',
            'Portal do cliente' => 'Mantenha mensagens, documentos e solicitações em um fluxo simples para o cliente não se perder.',
        ];
    }

    public function getRecursosProperty(): array
    {
        $recursosAtivos = $this->data['onboarding_recursos'] ?? [];
        $modulosAtivos = $this->data['modulos_ativos'] ?? [];

        return collect($this->recursos())->map(function (array $recurso) use ($recursosAtivos, $modulosAtivos): array {
            $recurso['ativo'] = (bool) ($recursosAtivos[$recurso['codigo']] ?? in_array($recurso['codigo'], $modulosAtivos, true));

            return $recurso;
        })->all();
    }

    public function getResumoProperty(): array
    {
        $etapas = collect($this->etapas);
        $recursos = collect($this->recursos);
        $totalEtapas = max(1, $etapas->count());
        $totalRecursos = max(1, $recursos->count());

        return [
            'progresso' => (int) round(($etapas->where('feito', true)->count() / $totalEtapas) * 100),
            'recursos_ativos' => $recursos->where('ativo', true)->count(),
            'total_recursos' => $recursos->count(),
            'modelo' => $this->data['onboarding_preferencias']['modelo_aplicado'] ?? 'Nenhum modelo aplicado',
            'visualizacao' => strtoupper((string) ($this->data['visualizacao_padrao'] ?? 'lista')),
        ];
    }

    protected function persistirOnboarding(string $mensagem): void
    {
        $configuracao = $this->getConfiguracaoAtual();

        $dados = [
            'onboarding_progresso' => $this->data['onboarding_progresso'] ?? [],
            'onboarding_recursos' => $this->data['onboarding_recursos'] ?? [],
            'onboarding_preferencias' => $this->data['onboarding_preferencias'] ?? [],
            'onboarding_finalizado_em' => $this->data['finalizado_em'] ?? null,
            'modulos_ativos' => array_values(array_unique($this->data['modulos_ativos'] ?? [])),
            'templates_estrutura' => array_values(array_unique($this->data['templates_estrutura'] ?? [])),
            'workflow_status' => array_values($this->data['workflow_status'] ?? []),
            'campos_personalizados' => array_values($this->data['campos_personalizados'] ?? []),
            'visualizacao_padrao' => $this->data['visualizacao_padrao'] ?? 'lista',
        ];

        $configuracao->fill($this->filtrarColunasExistentes($dados));
        $configuracao->save();

        Notification::make()
            ->title($mensagem)
            ->body('As alterações foram gravadas na configuração da empresa e já podem ser consultadas pelos módulos.')
            ->success()
            ->send();
    }

    protected function filtrarColunasExistentes(array $dados): array
    {
        return collect($dados)
            ->filter(fn ($valor, string $coluna): bool => CachedSchema::hasColumn('configuracoes', $coluna))
            ->all();
    }

    protected function getConfiguracaoAtual(): Configuracao
    {
        $user = Filament::auth()->user();

        if (! $user) {
            abort(403, 'Usuário não autenticado.');
        }

        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            $empresa = Empresa::query()->orderBy('id')->first();

            if (! $empresa) {
                abort(404, 'Cadastre uma empresa antes de acessar o onboarding.');
            }

            return Configuracao::forEmpresaId($empresa->id);
        }

        if (method_exists($user, 'hasEmpresaVinculada') && ! $user->hasEmpresaVinculada()) {
            abort(403, 'Seu usuário não possui empresa vinculada.');
        }

        return Configuracao::forEmpresaId((int) $user->empresa_id);
    }

    protected function exigirPermissaoConfiguracao(): void
    {
        $user = Filament::auth()->user();

        if (! $user || ! ((method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) || (method_exists($user, 'isAdminEmpresa') && $user->isAdminEmpresa()))) {
            abort(403, 'Somente administrador pode alterar o onboarding.');
        }
    }

    protected function etapasBase(): array
    {
        return [
            ['codigo' => 'hierarquia', 'titulo' => 'Definir hierarquia de trabalho', 'descricao' => 'Organize Espaços, Pastas, Listas e Tarefas por departamento, cliente ou projeto.', 'feito_auto' => ! empty($this->data['templates_estrutura'])],
            ['codigo' => 'visualizacoes', 'titulo' => 'Escolher visualização padrão', 'descricao' => 'Defina se a operação inicia em Lista, Kanban, Calendário, Gantt ou Linha do Tempo.', 'feito_auto' => filled($this->data['visualizacao_padrao'] ?? null)],
            ['codigo' => 'workflow', 'titulo' => 'Configurar workflow inicial', 'descricao' => 'Status do fluxo que uma tarefa percorre até ser concluída.', 'feito_auto' => ! empty($this->data['workflow_status'])],
            ['codigo' => 'campos', 'titulo' => 'Criar campos personalizados', 'descricao' => 'Campos de preço, data, fórmula, lista, progresso e arquivos.', 'feito_auto' => ! empty($this->data['campos_personalizados'])],
            ['codigo' => 'recursos', 'titulo' => 'Ativar recursos essenciais', 'descricao' => 'Habilite ClickApps úteis sem sobrecarregar a interface do cliente.', 'feito_auto' => count($this->data['modulos_ativos'] ?? []) >= 5],
            ['codigo' => 'time', 'titulo' => 'Preparar equipe e capacidade', 'descricao' => 'Carga de trabalho, responsáveis, metas e notificações para a equipe começar organizada.', 'feito_auto' => false],
        ];
    }

    protected function recursos(): array
    {
        return [
            ['codigo' => 'hierarquia_trabalho', 'titulo' => 'Hierarquia de Trabalho', 'descricao' => 'Espaços, Pastas, Listas e Tarefas por área ou projeto.'],
            ['codigo' => 'multiplas_visualizacoes', 'titulo' => 'Múltiplas Visualizações', 'descricao' => 'Lista, Kanban, Calendário, Gantt e Linha do Tempo.'],
            ['codigo' => 'docs', 'titulo' => 'Docs colaborativos', 'descricao' => 'Documentos vinculados diretamente às tarefas.'],
            ['codigo' => 'automacoes', 'titulo' => 'Automações', 'descricao' => 'Regras internas do tipo SE/ENTÃO.'],
            ['codigo' => 'campos_personalizados', 'titulo' => 'Custom Fields', 'descricao' => 'Colunas de moeda, datas, fórmulas, menus e progresso.'],
            ['codigo' => 'dashboards', 'titulo' => 'Relatórios e Dashboards', 'descricao' => 'Painéis de produtividade e status dos projetos.'],
            ['codigo' => 'time_tracking', 'titulo' => 'Time Tracking', 'descricao' => 'Cronômetro e relatório de horas por tarefa.'],
            ['codigo' => 'formularios', 'titulo' => 'Formulários Nativos', 'descricao' => 'Coleta de dados que vira tarefa automaticamente.'],
            ['codigo' => 'mind_maps', 'titulo' => 'Mapas Mentais', 'descricao' => 'Planejamento visual conectado às tarefas.'],
            ['codigo' => 'goals', 'titulo' => 'Metas', 'descricao' => 'Objetivos numéricos e marcos de longo prazo.'],
            ['codigo' => 'workload', 'titulo' => 'Workload', 'descricao' => 'Visualização de sobrecarga e disponibilidade da equipe.'],
            ['codigo' => 'notificacoes', 'titulo' => 'Central de Notificações', 'descricao' => 'Alertas personalizados por tipo de evento.'],
            ['codigo' => 'task_tray', 'titulo' => 'Task Tray', 'descricao' => 'Bandeja de tarefas para acesso rápido.'],
            ['codigo' => 'comentarios_mencoes', 'titulo' => 'Comentários e Menções', 'descricao' => 'Chat interno e comentários atribuíveis.'],
            ['codigo' => 'ai', 'titulo' => 'Assistente IA', 'descricao' => 'Resumo, escrita de documentos e geração de ideias.'],
            ['codigo' => 'email_tarefa', 'titulo' => 'E-mail na tarefa', 'descricao' => 'Histórico de comunicação centralizado.'],
            ['codigo' => 'templates', 'titulo' => 'Templates', 'descricao' => 'Modelos prontos para RH, Marketing, CRM e rotinas.'],
            ['codigo' => 'everything_view', 'titulo' => 'Everything View', 'descricao' => 'Visão global de todas as tarefas.'],
            ['codigo' => 'dependencias', 'titulo' => 'Relações e Dependências', 'descricao' => 'Tarefas conectadas por bloqueios e sequência.'],
            ['codigo' => 'clip', 'titulo' => 'Clip / Gravação de Tela', 'descricao' => 'Registro rápido de tela para orientar a equipe.'],
        ];
    }

    public function modelos(): array
    {
        return [
            ['codigo' => 'rh', 'titulo' => 'Onboarding RH', 'workflow' => ['Novo colaborador', 'Documentos pendentes', 'Em conferência', 'Treinamento', 'Finalizado'], 'campos' => ['Cargo: texto', 'Data de admissão: data', 'Salário previsto: moeda', 'Kit entregue: lista'], 'visualizacao' => 'lista'],
            ['codigo' => 'cliente', 'titulo' => 'Onboarding de Cliente', 'workflow' => ['Briefing', 'Documentos', 'Implantação', 'Validação', 'Ativo'], 'campos' => ['Plano contratado: lista', 'Responsável do cliente: texto', 'Valor mensal: moeda', 'Risco de implantação: lista'], 'visualizacao' => 'kanban'],
            ['codigo' => 'projeto', 'titulo' => 'Projeto com Gantt', 'workflow' => ['Backlog', 'Planejado', 'Em execução', 'Bloqueado', 'Entregue'], 'campos' => ['Data marco: data', 'Dependência principal: texto', 'Percentual concluído: progresso'], 'visualizacao' => 'gantt'],
        ];
    }

    protected function preferenciasPadrao(): array
    {
        return [
            'modelo_aplicado' => null,
            'responsavel_implantacao' => null,
            'prazo_implantacao' => null,
            'observacoes' => null,
        ];
    }

    public static function canAccess(): bool
    {
        return auth()->check();
    }
}
