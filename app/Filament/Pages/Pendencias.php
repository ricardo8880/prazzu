<?php

namespace App\Filament\Pages;

use App\Filament\Resources\ItemControles\ItemControleResource;
use App\Models\ItemControle;
use App\Services\PlanoService;
use App\Support\CachedSchema;
use App\Support\ComplianceModuleData;
use BackedEnum;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationItem;
use Filament\Notifications\Notification;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Pages\Page;
use Illuminate\Support\Str;
use UnitEnum;

class Pendencias extends Page
{
    protected function getDashboardStats(\Illuminate\Support\Collection $items): array
    {
        $concluidasHoje = ItemControle::query()
            ->visibleForUser(Filament::auth()->user())
            ->where('status', 'concluido')
            ->whereDate('data_conclusao', now()->toDateString())
            ->count();

        return [
            [
                'label' => 'Atrasadas',
                'value' => $items->where('is_late', true)->count(),
                'hint' => 'Exigem ação antes de novas demandas',
                'tone' => 'danger',
            ],
            [
                'label' => 'Urgentes',
                'value' => $items->filter(fn (array $item): bool => in_array((string) ($item['prioridade'] ?? ''), ['urgente', 'alta'], true))->count(),
                'hint' => 'Prioridade alta ou urgente',
                'tone' => 'warning',
            ],
            [
                'label' => 'Aguardando você',
                'value' => $items->filter(fn (array $item): bool => ($item['is_minha'] ?? false) || ($item['status'] ?? null) === 'em_aprovacao')->count(),
                'hint' => 'Itens seus ou aguardando decisão',
                'tone' => 'info',
            ],
            [
                'label' => 'Concluídas hoje',
                'value' => $concluidasHoje,
                'hint' => 'Progresso operacional do dia',
                'tone' => 'ok',
            ],
        ];
    }

    protected function getProgressData(\Illuminate\Support\Collection $items): array
    {
        $total = $items->count();
        $resolvidasOuSeguras = $items->filter(fn (array $item): bool => ($item['prioridade_operacional_tone'] ?? null) === 'ok')->count();
        $criticas = $items->whereIn('prioridade_operacional_tone', ['danger', 'warning'])->count();
        $percentualControle = $total > 0 ? (int) round(($resolvidasOuSeguras / $total) * 100) : 100;

        return [
            'total' => $total,
            'no_controle' => $resolvidasOuSeguras,
            'criticas' => $criticas,
            'percentual_controle' => max(0, min(100, $percentualControle)),
            'mensagem' => $criticas > 0
                ? 'Progresso real acontece quando a fila crítica diminui. Comece pelos itens destacados.'
                : 'Fila saudável no filtro atual. Continue acompanhando os próximos prazos.',
        ];
    }

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static string | UnitEnum | null $navigationGroup = 'Pendências e Alertas';
    protected static ?string $navigationLabel = 'Pendências e Alertas';
    protected static ?string $title = 'Pendências e Alertas';
    protected static ?int $navigationSort = 3;
    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;
    protected string $view = 'filament.pages.compliance-pendencias';

    public $empresaId = null;
    public $responsavelId = null;
    public string $titulo = '';
    public string $descricao = '';
    public string $prioridade = 'media';
    public ?string $dataVencimento = null;

    public ?int $pendenciaSelecionadaId = null;
    public string $observacaoAcao = '';
    public string $filtroPendencias = 'minhas';
    public string $buscaPendencias = '';
    public ?array $lastActionFeedback = null;

    public function mount(): void
    {
        $filtro = request()->query('filtro');

        if (is_string($filtro) && $this->isFiltroPendenciasValido($filtro)) {
            $this->filtroPendencias = $filtro;
        }
    }

    public function getSubNavigation(): array
    {
        return collect($this->getPendenciasSubNavigationItems())
            ->map(fn (array $item): NavigationItem => NavigationItem::make($item['label'])
                ->icon($item['icon'])
                ->url($this->getPendenciasClusterUrl($item['key']))
                ->isActiveWhen(fn (): bool => $this->filtroPendencias === $item['key'])
                ->sort($item['sort']))
            ->all();
    }

    protected function getPendenciasSubNavigationItems(): array
    {
        return [
            [
                'key' => 'minhas',
                'label' => 'Minhas Pendências',
                'icon' => 'heroicon-o-user-circle',
                'sort' => 1,
            ],
            [
                'key' => 'todas',
                'label' => 'Todas as Pendências',
                'icon' => 'heroicon-o-clipboard-document-list',
                'sort' => 2,
            ],
            [
                'key' => 'atrasadas',
                'label' => 'Atrasadas',
                'icon' => 'heroicon-o-exclamation-triangle',
                'sort' => 3,
            ],
            [
                'key' => 'aprovacao',
                'label' => 'Aprovações',
                'icon' => 'heroicon-o-check-badge',
                'sort' => 4,
            ],
            [
                'key' => 'bloqueadas',
                'label' => 'Bloqueadas',
                'icon' => 'heroicon-o-lock-closed',
                'sort' => 5,
            ],
            [
                'key' => 'sla',
                'label' => 'SLA / Riscos',
                'icon' => 'heroicon-o-clock',
                'sort' => 6,
            ],
            [
                'key' => 'sem_responsavel',
                'label' => 'Sem responsável',
                'icon' => 'heroicon-o-user-minus',
                'sort' => 7,
            ],
        ];
    }

    protected function getPendenciasClusterUrl(string $filtro): string
    {
        return static::getUrl() . '?' . http_build_query(['filtro' => $filtro]);
    }

    protected function isFiltroPendenciasValido(string $filtro): bool
    {
        return in_array($filtro, ['todas', 'minhas', 'atrasadas', 'aprovacao', 'bloqueadas', 'sla', 'sem_responsavel'], true);
    }

    protected function getViewData(): array
    {
        return [
            'data' => $this->getPendenciasData(),
            'pendenciaSelecionada' => $this->getPendenciaSelecionadaData(),
        ];
    }

    protected function getPendenciasData(): array
    {
        $data = ComplianceModuleData::pendencias();
        $rawItems = collect($data['items'] ?? []);
        $itemIds = $rawItems->pluck('id')->filter()->map(fn ($id) => (int) $id)->all();
        $recordsById = $this->getPendenciasRecordsById($itemIds);
        $responsavelAtualId = Filament::auth()->user()?->responsavel?->id;

        $items = $rawItems
            ->map(function (array $item) use ($recordsById, $responsavelAtualId): array {
                $itemId = (int) ($item['id'] ?? 0);
                $record = $recordsById[$itemId] ?? null;

                $item['responsavel_id'] = $record?->responsavel_id ? (int) $record->responsavel_id : null;
                $item['is_minha'] = $responsavelAtualId && (int) ($item['responsavel_id'] ?? 0) === (int) $responsavelAtualId;
                $item = array_merge($item, $record ? $this->buildAdvancedStateForRecord($record) : $this->getDefaultAdvancedState());

                return $this->enrichPendenciaPriorityData($item);
            })
            ->sortBy([
                ['urgency_order', 'asc'],
                ['due_sort', 'asc'],
                ['titulo', 'asc'],
            ])
            ->values();

        $data['filterOptions'] = $this->getPendenciasFilterOptions($items);
        $data['activeFilter'] = $this->filtroPendencias;
        $data['search'] = trim($this->buscaPendencias);
        $data['totalBeforeFilters'] = $items->count();
        $data['dashboardStats'] = $this->getDashboardStats($items);

        $filteredItems = $this->applyPendenciasFilters($items)->values();

        $data['items'] = $filteredItems->all();
        $data['totalAfterFilters'] = $filteredItems->count();
        $data['progress'] = $this->getProgressData($filteredItems);
        $data['hasActiveFilters'] = $this->filtroPendencias !== 'minhas' || filled(trim($this->buscaPendencias));
        $data['workflowLinks'] = $this->getWorkflowLinks();
        $data['workflowDecision'] = $this->getWorkflowDecisionData($items, $filteredItems);
        $data['health'] = $this->getPendenciasHealthData($items);
        $data['activeCluster'] = $this->getActivePendenciasClusterData($items, $filteredItems);
        $data['emptyState'] = $this->getPendenciasEmptyState($items, $filteredItems);

        return $data;
    }

    protected function getPendenciasRecordsById(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $with = [];

        if (CachedSchema::hasTable('prazzu_dependencies')) {
            $with[] = 'dependencies.dependsOnItem:id,titulo,status';
        }

        return ItemControle::query()
            ->with($with)
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id')
            ->all();
    }

    protected function getWorkflowLinks(): array
    {
        return [
            'minhas_pendencias' => $this->getPendenciasClusterUrl('minhas'),
            'todas_tarefas' => ItemControleResource::getUrl('list'),
            'nova_tarefa' => ItemControleResource::getUrl('create'),
        ];
    }

    protected function getActivePendenciasClusterData(\Illuminate\Support\Collection $items, \Illuminate\Support\Collection $filteredItems): array
    {
        $options = $this->getPendenciasFilterOptions($items);
        $active = $options[$this->filtroPendencias] ?? $options['minhas'];
        $search = trim($this->buscaPendencias);
        $criticalCount = $filteredItems->whereIn('prioridade_operacional_tone', ['danger', 'warning'])->count();
        $blockedCount = $filteredItems->where('bloqueado_operacional', true)->count();
        $slaCount = $filteredItems->where('sla_em_andamento', true)->count();

        return [
            'key' => $this->filtroPendencias,
            'label' => $active['label'] ?? 'Minhas Pendências',
            'hint' => $active['hint'] ?? 'Responsável atual',
            'tone' => $active['tone'] ?? 'info',
            'count' => $filteredItems->count(),
            'total' => $items->count(),
            'critical_count' => $criticalCount,
            'blocked_count' => $blockedCount,
            'sla_count' => $slaCount,
            'has_search' => filled($search),
            'search' => $search,
            'description' => match ($this->filtroPendencias) {
                'minhas' => 'Sua fila individual aparece primeiro para execução rápida, sem misturar demandas de outros responsáveis.',
                'todas' => 'Visão completa para triagem, acompanhamento de responsáveis e conferência operacional geral.',
                'atrasadas' => 'Tudo que passou do prazo fica concentrado aqui para ataque imediato e redução de risco.',
                'aprovacao' => 'Itens aguardando decisão aparecem juntos para aprovar, reprovar ou devolver sem procurar na lista geral.',
                'bloqueadas' => 'Pendências com trava operacional ou dependência ficam separadas para remover impedimentos primeiro.',
                'sla' => 'Pendências com SLA ativo ficam agrupadas para acompanhar tempo, risco e evolução do atendimento.',
                'sem_responsavel' => 'Itens sem dono aparecem destacados para atribuição rápida e prevenção de esquecimento.',
                default => 'Use este cluster para encontrar o contexto correto sem sair da central.',
            },
            'next_action' => match ($this->filtroPendencias) {
                'minhas' => $filteredItems->isEmpty() ? 'Sua fila está limpa neste contexto.' : 'Comece pelo primeiro item recomendado e avance em sequência.',
                'todas' => 'Use a busca ou abra os itens críticos para decidir responsáveis e próximos passos.',
                'atrasadas' => $filteredItems->isEmpty() ? 'Nenhum prazo vencido neste momento.' : 'Resolva ou reprograme os prazos vencidos antes de abrir novas demandas.',
                'aprovacao' => $filteredItems->isEmpty() ? 'Nenhum item aguardando aprovação.' : 'Abra cada item e registre a decisão diretamente no modal.',
                'bloqueadas' => $filteredItems->isEmpty() ? 'Nenhuma trava operacional detectada.' : 'Remova dependências e bloqueios para liberar a execução.',
                'sla' => $filteredItems->isEmpty() ? 'Nenhum SLA ativo neste cluster.' : 'Monitore o tempo restante e atualize o SLA dos itens em andamento.',
                'sem_responsavel' => $filteredItems->isEmpty() ? 'Todos os itens possuem responsável.' : 'Defina responsáveis para impedir que demandas fiquem sem dono.',
                default => 'Revise a lista filtrada e execute a próxima ação recomendada.',
            },
        ];
    }

    protected function getPendenciasEmptyState(\Illuminate\Support\Collection $items, \Illuminate\Support\Collection $filteredItems): array
    {
        $search = trim($this->buscaPendencias);

        if ($filteredItems->isNotEmpty()) {
            return [
                'title' => null,
                'message' => null,
                'action_label' => null,
                'action' => null,
            ];
        }

        if (filled($search)) {
            return [
                'title' => 'Nenhum resultado para a busca atual.',
                'message' => 'A busca é aplicada somente dentro do cluster selecionado. Limpe a busca ou troque de cluster no topo da página.',
                'action_label' => 'Limpar busca e voltar para Minhas Pendências',
                'action' => 'limparFiltrosPendencias',
            ];
        }

        return match ($this->filtroPendencias) {
            'minhas' => [
                'title' => 'Sua fila individual está limpa.',
                'message' => 'Não há pendências atribuídas a você neste momento. Use Todas para fazer triagem geral ou crie uma nova pendência quando necessário.',
                'action_label' => 'Ver todas as pendências',
                'action' => $this->getPendenciasClusterUrl('todas'),
            ],
            'atrasadas' => [
                'title' => 'Nenhuma pendência atrasada.',
                'message' => 'Ótimo sinal operacional. Continue acompanhando as urgentes e os próximos prazos.',
                'action_label' => 'Ver Minhas Pendências',
                'action' => $this->getPendenciasClusterUrl('minhas'),
            ],
            'aprovacao' => [
                'title' => 'Nenhuma aprovação pendente.',
                'message' => 'Quando houver itens aguardando decisão, eles aparecerão aqui para aprovação ou reprovação rápida.',
                'action_label' => 'Ver Minhas Pendências',
                'action' => $this->getPendenciasClusterUrl('minhas'),
            ],
            'bloqueadas' => [
                'title' => 'Nenhuma pendência bloqueada.',
                'message' => 'Não foram encontradas travas operacionais ou dependências bloqueantes neste momento.',
                'action_label' => 'Ver SLA / Riscos',
                'action' => $this->getPendenciasClusterUrl('sla'),
            ],
            'sla' => [
                'title' => 'Nenhum SLA ativo.',
                'message' => 'Os itens com SLA iniciado aparecerão aqui para acompanhamento de tempo, risco e conclusão.',
                'action_label' => 'Ver todas',
                'action' => $this->getPendenciasClusterUrl('todas'),
            ],
            'sem_responsavel' => [
                'title' => 'Nenhuma pendência sem responsável.',
                'message' => 'Todos os itens encontrados possuem dono definido, o que reduz risco de esquecimento.',
                'action_label' => 'Ver todas',
                'action' => $this->getPendenciasClusterUrl('todas'),
            ],
            default => [
                'title' => 'Nenhuma pendência encontrada.',
                'message' => $items->isEmpty()
                    ? 'Quando uma pendência for criada, ela aparecerá aqui com prioridade, responsável e prazo.'
                    : 'Troque de cluster no topo da página ou ajuste a busca para ampliar os resultados.',
                'action_label' => 'Criar pendência',
                'action' => '#nova-pendencia',
            ],
        };
    }


    protected function getWorkflowDecisionData(\Illuminate\Support\Collection $items, \Illuminate\Support\Collection $filteredItems): array
    {
        $minhas = $items->where('is_minha', true)->count();
        $criticas = $items->whereIn('prioridade_operacional_tone', ['danger', 'warning'])->count();
        $aprovacoes = $items->where('status', 'em_aprovacao')->count();
        $bloqueadas = $items->where('bloqueado_operacional', true)->count();
        $semResponsavel = $items->where('sem_responsavel', true)->count();
        $execucaoImediata = $filteredItems->filter(fn (array $item): bool => ($item['is_minha'] ?? false) && in_array(($item['prioridade_operacional_tone'] ?? null), ['danger', 'warning'], true))->count();

        return [
            'current_scope' => [
                'label' => 'Painel de Pendências',
                'description' => 'Use esta tela para enxergar risco, prioridade, SLA, bloqueios, responsáveis e criar novas demandas com contexto.',
                'best_for' => 'Decisão, triagem e acompanhamento geral',
                'count' => $items->count(),
                'tone' => $criticas > 0 ? 'warning' : 'ok',
            ],
            'execution_scope' => [
                'label' => 'Minhas Pendências',
                'description' => 'Use a execução diária quando quiser trabalhar somente no que está atribuído a você, em ritmo de fila operacional.',
                'best_for' => 'Execução individual e limpeza da fila pessoal',
                'count' => $minhas,
                'tone' => $execucaoImediata > 0 ? 'danger' : ($minhas > 0 ? 'info' : 'ok'),
            ],
            'decision_message' => $criticas > 0
                ? 'Existem itens críticos na visão geral. Faça a triagem aqui e envie a execução individual para a fila diária quando necessário.'
                : 'A visão geral está controlada. Para produzir em sequência, siga para Minhas Pendências.',
            'risk_summary' => [
                'criticas' => $criticas,
                'aprovacoes' => $aprovacoes,
                'bloqueadas' => $bloqueadas,
                'sem_responsavel' => $semResponsavel,
                'execucao_imediata' => $execucaoImediata,
            ],
        ];
    }

    protected function getPendenciasHealthData(\Illuminate\Support\Collection $items): array
    {
        $total = max(1, $items->count());
        $criticas = $items->whereIn('prioridade_operacional_tone', ['danger', 'warning'])->count();
        $bloqueadas = $items->where('bloqueado_operacional', true)->count();
        $semDono = $items->where('sem_responsavel', true)->count();
        $slaAtivo = $items->where('sla_em_andamento', true)->count();
        $saudaveis = $items->where('prioridade_operacional_tone', 'ok')->count();

        return [
            'percentual_critico' => (int) round(($criticas / $total) * 100),
            'percentual_saudavel' => (int) round(($saudaveis / $total) * 100),
            'bloqueadas' => $bloqueadas,
            'sem_dono' => $semDono,
            'sla_ativo' => $slaAtivo,
            'mensagem' => $criticas > 0
                ? 'Comece pelas pendências em vermelho/amarelo antes de assumir novas demandas.'
                : 'Fila em bom estado operacional. Mantenha o acompanhamento dos próximos prazos.',
        ];
    }

    protected function getPendenciasResponsavelIds(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        return ItemControle::query()
            ->whereIn('id', $ids)
            ->pluck('responsavel_id', 'id')
            ->map(fn ($responsavelId) => $responsavelId ? (int) $responsavelId : null)
            ->all();
    }


    protected function getPendenciasAdvancedStateById(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $with = [];

        if (CachedSchema::hasTable('prazzu_dependencies')) {
            $with[] = 'dependencies.dependsOnItem:id,titulo,status';
        }

        return ItemControle::query()
            ->with($with)
            ->whereIn('id', $ids)
            ->get()
            ->mapWithKeys(fn (ItemControle $record): array => [
                $record->id => $this->buildAdvancedStateForRecord($record),
            ])
            ->all();
    }

    protected function getDefaultAdvancedState(): array
    {
        return [
            'sla_status_key' => null,
            'sla_resumo' => 'Sem SLA iniciado',
            'sla_tempo' => '-',
            'sla_percentual' => 0,
            'sla_tone' => 'gray',
            'sla_em_andamento' => false,
            'sla_atrasado' => false,
            'tem_sla' => false,
            'bloqueado_operacional' => false,
            'dependencias_total' => 0,
            'dependencias_bloqueantes' => 0,
            'dependencias_resolvidas' => 0,
            'dependencias_pendentes' => 0,
            'dependencias_resumo' => 'Sem dependências cadastradas',
        ];
    }

    protected function buildAdvancedStateForRecord(ItemControle $record): array
    {
        $dependencies = CachedSchema::hasTable('prazzu_dependencies')
            ? $record->dependencies
            : collect();

        $blockingDependencies = $dependencies->filter(function ($dependency): bool {
            return (bool) ($dependency->blocked_until_resolved ?? false)
                && ! in_array((string) ($dependency->dependsOnItem?->status ?? ''), ['concluido', 'cancelado', 'aprovado'], true);
        });

        $resolvedDependencies = $dependencies->filter(fn ($dependency): bool => in_array((string) ($dependency->dependsOnItem?->status ?? ''), ['concluido', 'cancelado', 'aprovado'], true));
        $pendingDependencies = max(0, $dependencies->count() - $resolvedDependencies->count());
        $slaStatus = (string) ($record->sla_status ?? '');
        $slaTone = match ($slaStatus) {
            'atrasado', 'concluido_atrasado' => 'danger',
            'em_andamento' => 'info',
            'concluido_no_prazo' => 'ok',
            default => 'gray',
        };

        return [
            'sla_status_key' => $record->sla_status,
            'sla_resumo' => filled($record->sla_status) ? $record->getSlaResumo() : 'Sem SLA iniciado',
            'sla_tempo' => filled($record->sla_status) ? $record->getSlaTempoRestanteResumo() : '-',
            'sla_percentual' => filled($record->sla_status) ? $record->getSlaPercentualConsumido() : 0,
            'sla_tone' => $slaTone,
            'sla_em_andamento' => filled($record->sla_status) && ! $record->sla_concluido_em,
            'sla_atrasado' => $slaStatus === 'atrasado',
            'tem_sla' => filled($record->sla_status),
            'bloqueado_operacional' => $record->estaBloqueadoOperacionalmente() || $blockingDependencies->isNotEmpty(),
            'dependencias_total' => $dependencies->count(),
            'dependencias_bloqueantes' => $blockingDependencies->count(),
            'dependencias_resolvidas' => $resolvedDependencies->count(),
            'dependencias_pendentes' => $pendingDependencies,
            'dependencias_resumo' => $dependencies->isEmpty()
                ? 'Sem dependências cadastradas'
                : $resolvedDependencies->count() . '/' . $dependencies->count() . ' dependência(s) resolvida(s)',
        ];
    }

    protected function getPendenciasFilterOptions(\Illuminate\Support\Collection $items): array
    {
        $options = [
            'todas' => [
                'label' => 'Todas as Pendências',
                'hint' => 'Visão geral',
                'count' => $items->count(),
                'tone' => 'info',
            ],
            'minhas' => [
                'label' => 'Minhas Pendências',
                'hint' => 'Sua fila de trabalho',
                'count' => $items->where('is_minha', true)->count(),
                'tone' => 'ok',
            ],
            'atrasadas' => [
                'label' => 'Atrasadas',
                'hint' => 'Prazo vencido',
                'count' => $items->where('is_late', true)->count(),
                'tone' => 'danger',
            ],
            'aprovacao' => [
                'label' => 'Aprovações',
                'hint' => 'Aguardando decisão',
                'count' => $items->where('status', 'em_aprovacao')->count(),
                'tone' => 'info',
            ],
            'sem_responsavel' => [
                'label' => 'Sem responsável',
                'hint' => 'Precisa de dono',
                'count' => $items->where('sem_responsavel', true)->count(),
                'tone' => 'danger',
            ],
            'sla' => [
                'label' => 'SLA / Riscos',
                'hint' => 'Prazo monitorado',
                'count' => $items->where('sla_em_andamento', true)->count(),
                'tone' => 'info',
            ],
            'bloqueadas' => [
                'label' => 'Bloqueadas',
                'hint' => 'Dependência/trava',
                'count' => $items->where('bloqueado_operacional', true)->count(),
                'tone' => 'danger',
            ],
        ];

        if (! array_key_exists($this->filtroPendencias, $options)) {
            $this->filtroPendencias = 'minhas';
        }

        return $options;
    }

    protected function applyPendenciasFilters(\Illuminate\Support\Collection $items): \Illuminate\Support\Collection
    {
        $filtered = match ($this->filtroPendencias) {
            'minhas' => $items->where('is_minha', true),
            'atrasadas' => $items->where('is_late', true),
            'aprovacao' => $items->where('status', 'em_aprovacao'),
            'sem_responsavel' => $items->where('sem_responsavel', true),
            'sla' => $items->where('sla_em_andamento', true),
            'bloqueadas' => $items->where('bloqueado_operacional', true),
            default => $items,
        };

        $search = Str::of($this->buscaPendencias)->trim()->lower()->toString();

        if ($search === '') {
            return $filtered;
        }

        return $filtered->filter(function (array $item) use ($search): bool {
            $haystack = Str::of(implode(' ', [
                $item['titulo'] ?? '',
                $item['descricao'] ?? '',
                $item['empresa'] ?? '',
                $item['responsavel'] ?? '',
                $item['status'] ?? '',
                $item['prioridade'] ?? '',
                $item['prioridade_operacional_label'] ?? '',
                $item['sla_resumo'] ?? '',
                $item['dependencias_resumo'] ?? '',
                ($item['bloqueado_operacional'] ?? false) ? 'bloqueada bloqueio dependência' : '',
            ]))->lower()->toString();

            return str_contains($haystack, $search);
        });
    }

    public function aplicarFiltroPendencias(string $filtro): void
    {
        if (! $this->isFiltroPendenciasValido($filtro)) {
            $filtro = 'minhas';
        }

        $this->filtroPendencias = $filtro;
        $this->registrarFeedbackPendencias('Filtro aplicado. A fila foi reorganizada sem sair da tela.', 'info');
    }

    public function limparFiltrosPendencias(): void
    {
        $this->filtroPendencias = 'minhas';
        $this->buscaPendencias = '';
        $this->registrarFeedbackPendencias('Filtros limpos. A visão de Minhas Pendências foi restaurada.', 'info');
    }

    protected function enrichPendenciaPriorityData(array $item): array
    {
        $prioridade = (string) ($item['prioridade'] ?? 'media');
        $status = (string) ($item['status'] ?? 'pendente');
        $responsavel = trim((string) ($item['responsavel'] ?? ''));
        $isLate = (bool) ($item['is_late'] ?? false);
        $dueDate = $this->parsePendenciaDueDate($item['vencimento'] ?? null);
        $isDueToday = $dueDate?->isToday() ?? false;
        $isDueSoon = $dueDate && ! $isLate && ! $isDueToday && $dueDate->betweenIncluded(now()->startOfDay(), now()->addDays(3)->endOfDay());
        $semResponsavel = blank($responsavel) || $responsavel === 'Sem responsável';
        $emAprovacao = $status === 'em_aprovacao';

        $slaAtrasado = (bool) ($item['sla_atrasado'] ?? false);
        $bloqueadoOperacional = (bool) ($item['bloqueado_operacional'] ?? false);

        if ($isLate || $slaAtrasado) {
            $label = $slaAtrasado ? 'SLA atrasado' : 'Tratar agora';
            $message = $slaAtrasado ? 'SLA estourado. Atualize ou finalize antes de novas demandas.' : 'Prazo vencido. Resolver antes das novas demandas.';
            $tone = 'danger';
            $order = 10;
        } elseif ($bloqueadoOperacional) {
            $label = 'Bloqueada';
            $message = 'Existe bloqueio operacional ou dependência impedindo avanço.';
            $tone = 'danger';
            $order = 15;
        } elseif ($semResponsavel) {
            $label = 'Sem dono';
            $message = 'Defina um responsável para evitar perda de prazo.';
            $tone = 'danger';
            $order = 20;
        } elseif ($prioridade === 'urgente') {
            $label = 'Urgente';
            $message = 'Alta sensibilidade operacional. Atue o quanto antes.';
            $tone = 'danger';
            $order = 30;
        } elseif ($isDueToday) {
            $label = 'Vence hoje';
            $message = 'Precisa de ação ainda hoje.';
            $tone = 'warning';
            $order = 40;
        } elseif ($prioridade === 'alta') {
            $label = 'Alta prioridade';
            $message = 'Importante acompanhar sem deixar entrar em atraso.';
            $tone = 'warning';
            $order = 50;
        } elseif ($isDueSoon) {
            $label = 'Próximo prazo';
            $message = 'Prazo se aproxima nos próximos dias.';
            $tone = 'info';
            $order = 60;
        } elseif ($emAprovacao) {
            $label = 'Aguardando decisão';
            $message = 'Acompanhe a aprovação para destravar o fluxo.';
            $tone = 'info';
            $order = 70;
        } else {
            $label = 'No controle';
            $message = 'Sem alerta crítico no momento.';
            $tone = ($item['tone'] ?? 'ok') === 'warning' ? 'warning' : 'ok';
            $order = 90;
        }

        $item['prioridade_operacional_label'] = $label;
        $item['prioridade_operacional_message'] = $message;
        $item['prioridade_operacional_tone'] = $tone;
        $item['urgency_order'] = $order;
        $item['due_sort'] = $dueDate?->format('Y-m-d') ?: '9999-12-31';
        $item['is_due_today'] = $isDueToday;
        $item['is_due_soon'] = $isDueSoon;
        $item['sem_responsavel'] = $semResponsavel;

        return $item;
    }

    protected function parsePendenciaDueDate(?string $date): ?Carbon
    {
        if (blank($date) || $date === 'Sem prazo') {
            return null;
        }

        try {
            return Carbon::createFromFormat('d/m/Y', $date)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    public function abrirPendencia(int $id): void
    {
        $record = $this->findVisiblePendencia($id);

        if (! $record) {
            Notification::make()
                ->title('Pendência não encontrada ou sem permissão de acesso')
                ->danger()
                ->send();

            return;
        }

        $this->pendenciaSelecionadaId = $record->id;
        $this->observacaoAcao = '';
    }

    public function fecharPendencia(): void
    {
        $this->pendenciaSelecionadaId = null;
        $this->observacaoAcao = '';
    }

    public function concluirPendenciaSelecionada(): void
    {
        $record = $this->getPendenciaSelecionadaRecord();

        if (! $record) {
            $this->notifyModalRecordUnavailable();
            return;
        }

        if (! $record->canBeModifiedBy(Filament::auth()->user())) {
            Notification::make()
                ->title('Você não tem permissão para concluir esta pendência')
                ->danger()
                ->send();

            return;
        }

        if (in_array((string) $record->status, ['concluido', 'cancelado'], true)) {
            Notification::make()
                ->title('Esta pendência já está encerrada')
                ->warning()
                ->send();

            $this->fecharPendencia();
            return;
        }

        $record->update([
            'status' => 'concluido',
            'data_conclusao' => now(),
        ]);

        if (filled($record->sla_status) && ! $record->sla_concluido_em) {
            $record->concluirSla();
        }

        $record->registrarTimeline(
            'atualizacao',
            'Pendência concluída',
            'O item foi concluído pela central de Pendências da Governança.'
        );

        Notification::make()
            ->title('Pendência concluída com sucesso')
            ->success()
            ->send();

        $this->registrarFeedbackPendencias('Pendência concluída. A fila foi atualizada e seu progresso do dia avançou.', 'success');
        $this->fecharPendencia();
    }

    public function solicitarAprovacaoPendenciaSelecionada(): void
    {
        $record = $this->getPendenciaSelecionadaRecord();

        if (! $record) {
            $this->notifyModalRecordUnavailable();
            return;
        }

        if (! $this->canSolicitarAprovacao($record)) {
            Notification::make()
                ->title('Esta pendência não está disponível para solicitação de aprovação')
                ->warning()
                ->send();

            return;
        }

        $record->solicitarAprovacao(Filament::auth()->user(), $this->observacaoAcao ?: null);
        $this->observacaoAcao = '';

        Notification::make()
            ->title('Aprovação solicitada com sucesso')
            ->success()
            ->send();

        $this->registrarFeedbackPendencias('Aprovação solicitada. O item continua visível para acompanhamento.', 'success');
    }

    public function aprovarPendenciaSelecionada(): void
    {
        $record = $this->getPendenciaSelecionadaRecord();

        if (! $record) {
            $this->notifyModalRecordUnavailable();
            return;
        }

        if (! $this->canAprovarOuReprovar($record)) {
            Notification::make()
                ->title('Você não tem permissão para aprovar esta pendência')
                ->danger()
                ->send();

            return;
        }

        $record->aprovar(Filament::auth()->user(), $this->observacaoAcao ?: null);
        $this->observacaoAcao = '';

        Notification::make()
            ->title('Pendência aprovada com sucesso')
            ->success()
            ->send();

        $this->registrarFeedbackPendencias('Pendência aprovada. A central refletiu a decisão imediatamente.', 'success');
    }

    public function reprovarPendenciaSelecionada(): void
    {
        $this->validate([
            'observacaoAcao' => ['required', 'string', 'max:2000'],
        ], [
            'observacaoAcao.required' => 'Informe o motivo da reprovação.',
        ]);

        $record = $this->getPendenciaSelecionadaRecord();

        if (! $record) {
            $this->notifyModalRecordUnavailable();
            return;
        }

        if (! $this->canAprovarOuReprovar($record)) {
            Notification::make()
                ->title('Você não tem permissão para reprovar esta pendência')
                ->danger()
                ->send();

            return;
        }

        $record->reprovar(Filament::auth()->user(), $this->observacaoAcao);
        $this->observacaoAcao = '';

        Notification::make()
            ->title('Pendência reprovada')
            ->success()
            ->send();

        $this->registrarFeedbackPendencias('Pendência reprovada com motivo registrado. O fluxo foi atualizado.', 'success');
    }

    public function criarPendencia(): void
    {
        $this->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'descricao' => ['nullable', 'string', 'max:5000'],
            'prioridade' => ['required', 'in:baixa,media,alta,urgente'],
            'dataVencimento' => ['nullable', 'date'],
        ]);

        $empresaId = ComplianceModuleData::resolveEmpresaId($this->empresaId);
        $responsavelId = ComplianceModuleData::resolveResponsavelId($this->responsavelId, $empresaId);

        if (! $empresaId || ! $responsavelId) {
            Notification::make()->title('Não foi possível criar a pendência')->body('Cadastre uma empresa e um responsável antes de criar itens de compliance.')->danger()->send();
            return;
        }

        ItemControle::query()->create([
            'titulo' => $this->titulo,
            'descricao' => $this->descricao,
            'tipo' => 'pendencia_compliance',
            'status' => 'pendente',
            'prioridade' => $this->prioridade,
            'empresa_id' => $empresaId,
            'responsavel_id' => $responsavelId,
            'data_vencimento' => $this->dataVencimento ?: null,
        ]);

        $this->reset(['empresaId', 'responsavelId', 'titulo', 'descricao', 'dataVencimento']);
        $this->prioridade = 'media';

        Notification::make()->title('Pendência criada com sucesso')->success()->send();
        $this->registrarFeedbackPendencias('Nova pendência criada e adicionada à fila operacional.', 'success');
    }

    protected function getPendenciaSelecionadaData(): ?array
    {
        $record = $this->getPendenciaSelecionadaRecord();

        if (! $record) {
            return null;
        }

        $record->loadMissing(['empresa', 'responsavel', 'categoria', 'ultimaAprovacao', 'dependencies.dependsOnItem']);

        $isLate = $record->isVencido();
        $tone = $isLate || in_array((string) $record->prioridade, ['urgente', 'critica'], true)
            ? 'danger'
            : ((string) $record->prioridade === 'alta' ? 'warning' : 'ok');

        $priorityPreview = $this->enrichPendenciaPriorityData([
            'titulo' => (string) $record->titulo,
            'descricao' => (string) $record->descricao,
            'responsavel' => $record->responsavel?->nome ?: 'Sem responsável',
            'status' => (string) $record->status,
            'prioridade' => (string) $record->prioridade,
            'vencimento' => $record->data_vencimento?->format('d/m/Y') ?: 'Sem prazo',
            'is_late' => $isLate,
            'tone' => $tone,
        ]);

        return [
            'id' => $record->id,
            'titulo' => $record->titulo,
            'descricao' => filled($record->descricao) ? $record->descricao : 'Sem descrição cadastrada.',
            'empresa' => $record->empresa?->nome_fantasia ?: $record->empresa?->razao_social ?: 'Sem empresa',
            'responsavel' => $record->responsavel?->nome ?: 'Sem responsável',
            'tipo' => $record->getTipoOuCategoria(),
            'status' => $record->getStatusExibicao(),
            'status_key' => $record->status,
            'prioridade' => $record->getPrioridadeExibicao(),
            'prioridade_key' => $record->prioridade,
            'vencimento' => $record->data_vencimento?->format('d/m/Y') ?: 'Sem prazo',
            'prazo' => $record->getSituacaoPrazo(),
            'is_late' => $isLate,
            'tone' => $tone,
            'edit_url' => ItemControleResource::getUrl('edit', ['record' => $record]),
            'can_concluir' => $record->canBeModifiedBy(Filament::auth()->user()) && ! in_array((string) $record->status, ['concluido', 'cancelado'], true),
            'can_solicitar_aprovacao' => $this->canSolicitarAprovacao($record),
            'can_aprovar' => $this->canAprovarOuReprovar($record),
            'can_iniciar_sla' => $this->canIniciarSla($record),
            'can_atualizar_sla' => $this->canAtualizarSla($record),
            'can_finalizar_sla' => $this->canFinalizarSla($record),
            'approval_status' => $record->getAprovacaoResumo(),
            'sla_resumo' => filled($record->sla_status) ? $record->getSlaResumo() : 'Sem SLA iniciado',
            'sla_tempo' => filled($record->sla_status) ? $record->getSlaTempoRestanteResumo() : '-',
            'sla_percentual' => filled($record->sla_status) ? $record->getSlaPercentualConsumido() : 0,
            'sla_prazo_alvo' => $record->getSlaPrazoAlvo() ?: 'Sem prazo-alvo',
            'sla_tone' => filled($record->sla_status) ? $this->getSlaTone($record) : 'gray',
            'bloqueado' => $record->estaBloqueadoOperacionalmente() || $this->recordHasBlockingDependencies($record),
            'bloqueio_resumo' => $this->getBloqueioResumo($record),
            'dependencias' => $this->getDependenciasResumo($record),
            'descricao_curta' => Str::limit(strip_tags((string) $record->descricao), 180),
            'prioridade_operacional_label' => $priorityPreview['prioridade_operacional_label'],
            'prioridade_operacional_message' => $priorityPreview['prioridade_operacional_message'],
            'prioridade_operacional_tone' => $priorityPreview['prioridade_operacional_tone'],
        ];
    }

    protected function getPendenciaSelecionadaRecord(): ?ItemControle
    {
        if (! $this->pendenciaSelecionadaId) {
            return null;
        }

        return $this->findVisiblePendencia($this->pendenciaSelecionadaId);
    }

    protected function findVisiblePendencia(int $id): ?ItemControle
    {
        return ItemControle::query()
            ->with(['empresa', 'responsavel', 'categoria', 'ultimaAprovacao'])
            ->whereKey($id)
            ->visibleForUser(Filament::auth()->user())
            ->first();
    }


    public function iniciarSlaPendenciaSelecionada(): void
    {
        $record = $this->getPendenciaSelecionadaRecord();

        if (! $record) {
            $this->notifyModalRecordUnavailable();
            return;
        }

        if (! $this->canIniciarSla($record)) {
            Notification::make()->title('SLA não pode ser iniciado para esta pendência')->warning()->send();
            return;
        }

        $record->iniciarSla();

        Notification::make()->title('SLA iniciado com sucesso')->success()->send();
        $this->registrarFeedbackPendencias('SLA iniciado. O acompanhamento de prazo foi ativado na central.', 'success');
    }

    public function atualizarSlaPendenciaSelecionada(): void
    {
        $record = $this->getPendenciaSelecionadaRecord();

        if (! $record) {
            $this->notifyModalRecordUnavailable();
            return;
        }

        if (! $this->canAtualizarSla($record)) {
            Notification::make()->title('SLA não está disponível para atualização')->warning()->send();
            return;
        }

        $record->atualizarSlaStatus();

        Notification::make()->title('Status do SLA atualizado')->success()->send();
        $this->registrarFeedbackPendencias('SLA atualizado. O painel já considera o novo status.', 'success');
    }

    public function finalizarSlaPendenciaSelecionada(): void
    {
        $record = $this->getPendenciaSelecionadaRecord();

        if (! $record) {
            $this->notifyModalRecordUnavailable();
            return;
        }

        if (! $this->canFinalizarSla($record)) {
            Notification::make()->title('SLA não pode ser finalizado para esta pendência')->warning()->send();
            return;
        }

        $record->concluirSla();

        Notification::make()->title('SLA finalizado com sucesso')->success()->send();
        $this->registrarFeedbackPendencias('SLA finalizado. O item saiu do acompanhamento ativo de prazo.', 'success');
    }

    protected function canIniciarSla(ItemControle $record): bool
    {
        return PlanoService::empresaPossuiFeature($record->empresa, PlanoService::FEATURE_SLA)
            && $record->canBeModifiedBy(Filament::auth()->user())
            && blank($record->sla_status)
            && ! in_array((string) $record->status, ['concluido', 'cancelado'], true);
    }

    protected function canAtualizarSla(ItemControle $record): bool
    {
        return PlanoService::empresaPossuiFeature($record->empresa, PlanoService::FEATURE_SLA)
            && $record->canBeModifiedBy(Filament::auth()->user())
            && filled($record->sla_status)
            && ! $record->sla_concluido_em;
    }

    protected function canFinalizarSla(ItemControle $record): bool
    {
        return $this->canAtualizarSla($record);
    }

    protected function getSlaTone(ItemControle $record): string
    {
        return match ((string) $record->sla_status) {
            'atrasado', 'concluido_atrasado' => 'danger',
            'em_andamento' => 'info',
            'concluido_no_prazo' => 'ok',
            default => 'gray',
        };
    }

    protected function recordHasBlockingDependencies(ItemControle $record): bool
    {
        if (! CachedSchema::hasTable('prazzu_dependencies')) {
            return false;
        }

        return $record->dependencies->contains(function ($dependency): bool {
            return (bool) ($dependency->blocked_until_resolved ?? false)
                && ! in_array((string) ($dependency->dependsOnItem?->status ?? ''), ['concluido', 'cancelado', 'aprovado'], true);
        });
    }

    protected function getBloqueioResumo(ItemControle $record): string
    {
        if (! $record->estaBloqueadoOperacionalmente() && ! $this->recordHasBlockingDependencies($record)) {
            return 'Sem bloqueio operacional ativo';
        }

        if ($this->recordHasBlockingDependencies($record)) {
            return 'Bloqueada por dependência pendente';
        }

        return 'Bloqueio operacional marcado no item';
    }

    protected function getDependenciasResumo(ItemControle $record): array
    {
        if (! CachedSchema::hasTable('prazzu_dependencies')) {
            return [];
        }

        return $record->dependencies
            ->map(fn ($dependency): array => [
                'titulo' => $dependency->dependsOnItem?->titulo ?: 'Dependência sem item vinculado',
                'status' => $dependency->dependsOnItem?->getStatusExibicao() ?: 'Sem status',
                'bloqueante' => (bool) ($dependency->blocked_until_resolved ?? false),
                'resolvida' => in_array((string) ($dependency->dependsOnItem?->status ?? ''), ['concluido', 'cancelado', 'aprovado'], true),
                'observacao' => $dependency->notes ?: null,
            ])
            ->values()
            ->all();
    }

    protected function canSolicitarAprovacao(ItemControle $record): bool
    {
        return PlanoService::empresaPossuiFeature($record->empresa, PlanoService::FEATURE_APROVACOES)
            && $record->canBeModifiedBy(Filament::auth()->user())
            && $record->podeSolicitarAprovacao();
    }

    protected function canAprovarOuReprovar(ItemControle $record): bool
    {
        return PlanoService::empresaPossuiFeature($record->empresa, PlanoService::FEATURE_APROVACOES)
            && $record->possuiAprovacaoPendente()
            && $record->canBeApprovedBy(Filament::auth()->user());
    }

    protected function notifyModalRecordUnavailable(): void
    {
        Notification::make()
            ->title('Pendência não encontrada ou sem permissão de acesso')
            ->danger()
            ->send();

        $this->fecharPendencia();
    }

    protected function registrarFeedbackPendencias(string $message, string $tone = 'info'): void
    {
        $this->lastActionFeedback = [
            'message' => $message,
            'tone' => in_array($tone, ['success', 'info', 'warning', 'danger'], true) ? $tone : 'info',
            'time' => now()->format('H:i'),
        ];

        $this->dispatch('pendencias-lote8-feedback', message: $message, tone: $this->lastActionFeedback['tone']);
    }

    public static function canAccess(): bool
    {
        return auth()->check();
    }
}
