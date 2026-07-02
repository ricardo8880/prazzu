<x-filament-panels::page>

    @php
        $loadError = $loadError ?? null;
        $aprovacoes = collect($data['aprovacoes'] ?? [])->take(12)->values()->all();
        $financeiro = collect($data['financeiro'] ?? [])->take(12)->values()->all();
        $financeiroResumo = $data['financeiro_resumo'] ?? ['indicadores' => [], 'impacto_total' => 'R$ 0,00'];
        $workload = collect($data['workload'] ?? [])->take(12)->values()->all();
        $activeTab = $operationalTab ?? 'workload';
    @endphp

    <div class="co-page co-model co-operational-detail-page" wire:loading.class="is-loading">
        <section class="co-topbar co-operational-detail-hero">
            <div>
                <div class="co-title-row">
                    <h1>Gestão da Operação</h1>
                    <span class="co-info">i</span>
                </div>
                <p>Área de apoio da Central Operacional para redistribuir carga, tratar aprovações e acompanhar pendências financeiras sem duplicar a mesa de execução.</p>
            </div>

            <div class="co-top-actions">
                <button type="button" class="co-refresh-btn" wire:click="refreshDashboard" wire:loading.attr="disabled">
                    <i class="bi bi-arrow-clockwise"></i>
                    Atualizar
                </button>
            </div>
        </section>



        <nav class="co-page-cluster co-main-cluster co-sticky-cluster" aria-label="Navegação da Operação Interna">
            <a class="co-cluster-item" href="{{ \App\Filament\Pages\CentroOperacional::getUrl() }}">
                <span class="co-cluster-icon"><i class="bi bi-command"></i></span>
                <span>
                    <strong>Central Operacional</strong>
                    <small>Voltar para a mesa de execução</small>
                </span>
            </a>
            <a class="co-cluster-item {{ $activeTab === 'workload' ? 'active' : '' }}" href="{{ \App\Filament\Pages\CentroOperacionalGestao::getUrl() }}?aba=workload">
                <span class="co-cluster-icon"><i class="bi bi-people"></i></span>
                <span>
                    <strong>Workload da Equipe</strong>
                    <small>Carga, capacidade e redistribuição</small>
                </span>
            </a>
            <a class="co-cluster-item {{ $activeTab === 'aprovacoes' ? 'active' : '' }}" href="{{ \App\Filament\Pages\CentroOperacionalGestao::getUrl() }}?aba=aprovacoes">
                <span class="co-cluster-icon"><i class="bi bi-patch-check"></i></span>
                <span>
                    <strong>Aprovações</strong>
                    <small>Aprovar, reprovar e correção</small>
                </span>
            </a>
            <a class="co-cluster-item {{ $activeTab === 'financeiro' ? 'active' : '' }}" href="{{ \App\Filament\Pages\CentroOperacionalGestao::getUrl() }}?aba=financeiro">
                <span class="co-cluster-icon"><i class="bi bi-bank"></i></span>
                <span>
                    <strong>Pendências Financeiras</strong>
                    <small>A vencer, vencido, inadimplente e faturável</small>
                </span>
            </a>
        </nav>

        @if($loadError)
            <section class="co-state-card error">
                <span class="co-state-icon"><i class="bi bi-exclamation-triangle-fill"></i></span>
                <div>
                    <strong>Falha ao carregar dados.</strong>
                    <p>{{ $loadError }}</p>
                </div>
                <button type="button" wire:click="refreshDashboard" wire:loading.attr="disabled">
                    <i class="bi bi-arrow-clockwise"></i>
                    Tentar novamente
                </button>
            </section>
        @endif

        @if($activeTab === 'workload')
            <section class="co-panel co-workload-panel co-detail-panel-large co-mobile-collapsible" x-data="{ open: true }" :class="{ 'is-open': open }">
                <header class="co-panel-header">
                    <div class="co-heading-with-icon">
                        <span class="co-section-icon muted"><i class="bi bi-people"></i></span>
                        <h2>Workload da Equipe</h2>
                    </div>
                    <div class="co-header-actions-inline">
                        <a class="co-see-all" href="{{ \App\Filament\Resources\ItemControles\ItemControleResource::getUrl('index') }}">Ver todos</a>
                        <button type="button" class="co-mobile-toggle" @click="open = ! open" :aria-expanded="open.toString()">
                            <i class="bi" :class="open ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                            <span x-text="open ? 'Ocultar' : 'Mostrar'"></span>
                        </button>
                    </div>
                </header>

                <div class="co-workload-list-model co-workload-v2 co-workload-detail-list">
                    @forelse ($workload as $row)
                        @php $barWidth = min(100, (int) ($row['percent'] ?? 0)); @endphp
                        <div class="co-workload-model-row {{ $row['tone'] ?? 'success' }}">
                            <span class="co-person-avatar">{{ mb_strtoupper(mb_substr($row['name'] ?? 'U', 0, 1)) }}</span>
                            <div class="co-person-info">
                                <strong>{{ $row['name'] }}</strong>
                                <small>{{ $row['total'] }} tarefas de {{ $row['capacity'] ?? 40 }} capacidade • {{ $row['status'] ?? 'Normal' }}</small>
                            </div>
                            <div class="co-progress"><span style="width: {{ $barWidth }}%"></span></div>
                            <b>{{ (int) ($row['percent'] ?? 0) }}%</b>
                            <div class="co-workload-actions">
                                @if(!empty($row['responsavel_id']))
                                    <button type="button" class="co-mini-action dark" wire:click="openWorkloadModal({{ (int) $row['responsavel_id'] }})" wire:loading.attr="disabled"><i class="bi bi-eye"></i>Detalhes</button>
                                    <button type="button" class="co-mini-action" wire:click="openWorkloadModal({{ (int) $row['responsavel_id'] }})" wire:loading.attr="disabled"><i class="bi bi-arrow-left-right"></i>Redistribuir</button>
                                @endif
                                <a class="co-mini-action" href="{{ $row['open_url'] ?? \App\Filament\Resources\ItemControles\ItemControleResource::getUrl('index') }}"><i class="bi bi-list-task"></i>Abrir tarefas</a>
                            </div>
                        </div>
                    @empty
                        <div class="co-empty clean"><strong>Nenhuma carga pendente.</strong></div>
                    @endforelse
                </div>
            </section>
        @elseif($activeTab === 'aprovacoes')
            <section class="co-panel co-approvals-panel co-detail-panel-large co-mobile-collapsible" x-data="{ open: true }" :class="{ 'is-open': open }">
                <header class="co-panel-header">
                    <div class="co-heading-with-icon">
                        <span class="co-section-icon blue"><i class="bi bi-file-earmark-check"></i></span>
                        <h2>Aprovações</h2>
                    </div>
                    <div class="co-header-actions-inline">
                        <a class="co-see-all" href="{{ \App\Filament\Resources\ItemControles\ItemControleResource::getUrl('index') }}">Ver todas</a>
                        <button type="button" class="co-mobile-toggle" @click="open = ! open" :aria-expanded="open.toString()">
                            <i class="bi" :class="open ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                            <span x-text="open ? 'Ocultar' : 'Mostrar'"></span>
                        </button>
                    </div>
                </header>

                <div class="co-small-list-model co-approvals-v2 co-approvals-detail-list">
                    @forelse ($aprovacoes as $item)
                        @php
                            $actions = $item['actions'] ?? [];
                            $canApprove = (bool) ($actions['approve'] ?? false);
                            $canCorrect = (bool) ($actions['correct'] ?? false);
                        @endphp
                        <article class="co-small-model-row co-approval-row">
                            <a class="co-approval-main" href="{{ $item['url'] }}">
                                <span class="co-small-icon {{ $item['tone'] ?? 'info' }}"><i class="bi bi-building"></i></span>
                                <div>
                                    <strong>{{ $item['empresa'] }}</strong>
                                    <span>{{ $item['title'] }}</span>
                                    <small>{{ $item['responsavel'] ?? 'Sem responsável' }} • {{ $item['due_human'] ?? 'Sem prazo' }}</small>
                                </div>
                            </a>
                            <div class="co-approval-actions">
                                <button type="button" class="co-mini-action dark" wire:click="openItemDetailModal({{ $item['id'] }}, 'resolver')" wire:loading.attr="disabled" wire:target="openItemDetailModal({{ $item['id'] }}, 'resolver')">
                                    <i class="bi bi-eye"></i>Detalhes
                                </button>
                                @if($canApprove)
                                    <button type="button" class="co-mini-action success" wire:click="aprovar({{ $item['id'] }})" wire:loading.attr="disabled"><i class="bi bi-check2"></i>Aprovar</button>
                                    <button type="button" class="co-mini-action danger" wire:click="reprovar({{ $item['id'] }})" wire:loading.attr="disabled"><i class="bi bi-x-lg"></i>Reprovar</button>
                                @endif
                                @if($canCorrect)
                                    <button type="button" class="co-mini-action warning" wire:click="enviarParaCorrecao({{ $item['id'] }})" wire:loading.attr="disabled"><i class="bi bi-tools"></i>Correção</button>
                                @endif
                            </div>
                        </article>
                    @empty
                        <div class="co-empty clean"><strong>Nada esperando aprovação.</strong></div>
                    @endforelse
                </div>
            </section>
        @else
            <section class="co-panel co-financial-panel co-detail-panel-large co-mobile-collapsible" x-data="{ open: true }" :class="{ 'is-open': open }">
                <header class="co-panel-header">
                    <div class="co-heading-with-icon">
                        <span class="co-section-icon green"><i class="bi bi-cash-coin"></i></span>
                        <h2>Pendências Financeiras</h2>
                    </div>
                    <div class="co-header-actions-inline">
                        <button type="button" class="co-see-all as-button" wire:click="abrirPendenciasFinanceiras">Filtrar financeiro</button>
                        <button type="button" class="co-mobile-toggle" @click="open = ! open" :aria-expanded="open.toString()">
                            <i class="bi" :class="open ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                            <span x-text="open ? 'Ocultar' : 'Mostrar'"></span>
                        </button>
                    </div>
                </header>

                <div class="co-financial-grid co-financial-detail-grid">
                    @foreach (($financeiroResumo['indicadores'] ?? []) as $indicator)
                        <article class="co-financial-card {{ $indicator['tone'] ?? 'success' }}">
                            <i class="bi {{ $indicator['icon'] ?? 'bi-cash' }}"></i>
                            <div class="co-financial-card-copy">
                                <span>{{ $indicator['label'] ?? '-' }}</span>
                                <strong>{{ number_format((int) ($indicator['quantity'] ?? 0), 0, ',', '.') }}</strong>
                                <small>{{ $indicator['value'] ?? 'R$ 0,00' }}</small>
                                <em>{{ $indicator['impact'] ?? 'Sem impacto imediato' }}</em>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="co-financial-list co-financial-detail-list">
                    @forelse ($financeiro as $item)
                        @php $canFinancial = (bool) (($item['actions']['financial'] ?? false)); @endphp
                        <article class="co-financial-row">
                            <a href="{{ $item['url'] }}">
                                <strong>{{ $item['empresa'] }}</strong>
                                <span>{{ $item['title'] }}</span>
                                <small>{{ $item['value'] ?? 'Sem valor informado' }} • {{ $item['status'] ?? '-' }}</small>
                            </a>
                            @if($canFinancial)
                                <div class="co-financial-actions">
                                    <button type="button" class="co-mini-action success" wire:click="marcarFaturado({{ $item['id'] }})" wire:loading.attr="disabled"><i class="bi bi-receipt"></i>Faturar</button>
                                    <button type="button" class="co-mini-action info" wire:click="marcarPago({{ $item['id'] }})" wire:loading.attr="disabled"><i class="bi bi-check2-circle"></i>Pago</button>
                                </div>
                            @endif
                        </article>
                    @empty
                        <div class="co-empty clean"><strong>Nenhuma pendência financeira operacional.</strong></div>
                    @endforelse
                </div>
            </section>
        @endif

        @if($detailModalOpen)
            @php $detail = $this->selectedItemDetail(); @endphp
            <div class="co-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="co-operational-detail-title" wire:click.self="closeItemDetailModal">
                <div class="co-modal-card co-detail-modal-card">
                    <button type="button" class="co-modal-close-btn" wire:click="closeItemDetailModal" aria-label="Fechar popup">
                        <i class="bi bi-x-lg"></i>
                    </button>
                    @if($detail)
                        <header>
                            <span class="co-section-icon blue"><i class="bi bi-file-earmark-check"></i></span>
                            <div>
                                <h3 id="co-operational-detail-title">Detalhes do item operacional</h3>
                                <p>{{ $detail['empresa'] }} • {{ $detail['categoria'] }}</p>
                            </div>
                        </header>

                        <div class="co-detail-modal-body">
                            <div class="co-detail-main-info">
                                <span class="co-priority-badge warning">{{ $detail['prioridade'] }}</span>
                                <h4>{{ $detail['title'] }}</h4>
                                <p>{{ $detail['descricao'] }}</p>
                            </div>

                            <div class="co-decision-box {{ $detail['suggestion']['tone'] ?? 'info' }}">
                                <div>
                                    <small>Sugestão operacional</small>
                                    <strong>{{ $detail['suggestion']['title'] ?? 'Avaliar item antes de decidir' }}</strong>
                                    <p>{{ $detail['suggestion']['text'] ?? 'Confira status, responsável, vencimento, histórico e checklist antes de aprovar, reprovar ou solicitar correção.' }}</p>
                                </div>
                                <span>{{ $detail['suggestion']['primary_action'] ?? 'Decidir com contexto' }}</span>
                            </div>

                            <div class="co-detail-grid">
                                <div><small>Status</small><strong>{{ $detail['status'] }}</strong></div>
                                <div><small>Responsável</small><strong>{{ $detail['responsavel'] }}</strong></div>
                                <div><small>Vencimento</small><strong>{{ $detail['vencimento'] }}</strong><em>{{ $detail['dias_prazo'] ?? '' }}</em></div>
                                <div><small>Valor/Impacto</small><strong>{{ $detail['valor'] }}</strong></div>
                                <div><small>Conclusão</small><strong>{{ $detail['conclusao'] }}</strong></div>
                                <div><small>Origem</small><strong>Operação Interna</strong></div>
                            </div>

                            <div class="co-detail-insights-grid">
                                <section class="co-detail-insight-card">
                                    <h4><i class="bi bi-clock-history"></i>Últimas movimentações</h4>
                                    @forelse(($detail['timeline'] ?? []) as $entry)
                                        <article>
                                            <strong>{{ $entry['titulo'] }}</strong>
                                            <span>{{ $entry['tipo'] }} • {{ $entry['data'] }}</span>
                                            <p>{{ $entry['descricao'] }}</p>
                                        </article>
                                    @empty
                                        <div class="co-empty clean small"><strong>Sem histórico operacional ainda.</strong></div>
                                    @endforelse
                                </section>

                                <section class="co-detail-insight-card">
                                    <h4><i class="bi bi-check2-square"></i>Checklist / próximas etapas</h4>
                                    @forelse(($detail['checklist'] ?? []) as $check)
                                        <article class="co-checkline {{ $check['concluido'] ? 'done' : '' }}">
                                            <i class="bi {{ $check['concluido'] ? 'bi-check-circle-fill' : 'bi-circle' }}"></i>
                                            <strong>{{ $check['titulo'] }}</strong>
                                        </article>
                                    @empty
                                        <div class="co-empty clean small"><strong>Nenhum checklist cadastrado.</strong></div>
                                    @endforelse
                                </section>
                            </div>
                        </div>

                        <footer class="co-detail-footer-actions">
                            <button type="button" class="co-action-btn muted" wire:click="closeItemDetailModal">Fechar</button>
                            <a class="co-action-btn info" href="{{ $detail['url'] }}"><i class="bi bi-box-arrow-up-right"></i>Abrir cadastro</a>
                            @if(($detail['actions']['approve'] ?? false))
                                <button type="button" class="co-action-btn success" wire:click="aprovar({{ $detail['id'] }})" wire:loading.attr="disabled"><i class="bi bi-check2-circle"></i>Aprovar</button>
                                <button type="button" class="co-action-btn danger" wire:click="reprovar({{ $detail['id'] }})" wire:loading.attr="disabled"><i class="bi bi-x-lg"></i>Reprovar</button>
                            @endif
                            @if(($detail['actions']['correct'] ?? false) && ! $detail['is_closed'])
                                <button type="button" class="co-action-btn warning" wire:click="enviarParaCorrecao({{ $detail['id'] }})" wire:loading.attr="disabled"><i class="bi bi-tools"></i>Solicitar correção</button>
                            @endif
                            @if(($detail['actions']['delegate'] ?? false) && ! $detail['is_closed'])
                                <button type="button" class="co-action-btn purple" wire:click="openDelegateModal({{ $detail['id'] }})" wire:loading.attr="disabled"><i class="bi bi-person-plus"></i>Delegar</button>
                            @endif
                        </footer>
                    @else
                        <header>
                            <span class="co-section-icon red"><i class="bi bi-exclamation-triangle"></i></span>
                            <div>
                                <h3 id="co-operational-detail-title">Item não encontrado</h3>
                                <p>O item pode ter sido atualizado, removido ou estar fora do seu escopo.</p>
                            </div>
                        </header>
                        <footer><button type="button" class="co-action-btn muted" wire:click="closeItemDetailModal">Fechar</button></footer>
                    @endif
                </div>
            </div>
        @endif

        @if($workloadModalOpen)
            @php
                $workloadDetail = $this->selectedWorkloadDetail();
                $workloadTotal = (int) ($workloadDetail['total'] ?? 0);
                $workloadCritical = (int) ($workloadDetail['critical'] ?? 0);
                $workloadLate = (int) ($workloadDetail['late'] ?? 0);
                $workloadOpenSoon = max($workloadTotal - $workloadLate, 0);
                $workloadPercent = min(100, max(12, ($workloadTotal * 8) + ($workloadCritical * 7) + ($workloadLate * 5)));
                $workloadAvailableHours = 34 - ($workloadTotal + ($workloadCritical * 2));
                $workloadMainItem = $workloadDetail['items'][0] ?? null;
                $workloadResponsavelName = $workloadDetail['responsavel']->nome ?? 'Responsável';
                $workloadRole = $workloadDetail['responsavel']->cargo ?? $workloadDetail['responsavel']->funcao ?? 'Responsável operacional';
                $workloadDepartment = $workloadDetail['responsavel']->departamento ?? $workloadDetail['responsavel']->area ?? 'Equipe';
                $workloadSince = $workloadDetail['responsavel']->created_at ?? null;
                $workloadSinceLabel = $workloadSince ? $workloadSince->format('m/Y') : now()->subYear()->format('m/Y');
                $workloadImpactMoney = number_format(max(450, ($workloadLate * 280) + ($workloadCritical * 190)), 2, ',', '.');
                $workloadImpactedClients = collect($workloadDetail['items'] ?? [])->pluck('empresa')->filter()->unique()->count();
                $workloadByCategory = collect($workloadDetail['items'] ?? [])->groupBy('categoria')->map->count()->sortDesc()->take(5);
                $workloadClients = collect($workloadDetail['items'] ?? [])->groupBy('empresa')->map->count()->sortDesc()->take(5);
                $workloadDelegable = collect($workloadDetail['items'] ?? [])->take(4);
                $workloadDays = collect(range(0, 6))->map(fn ($day) => [
                    'label' => now()->addDays($day)->format('d/m'),
                    'value' => min(100, max(25, $workloadPercent - 18 + ($day * 6) - ($day % 2 ? 8 : 0))),
                ]);
            @endphp
            <div class="co-modal-backdrop co-workload-v3-backdrop" role="dialog" aria-modal="true" aria-labelledby="co-workload-detail-title" wire:click.self="closeWorkloadModal">
                <div class="co-modal-card co-workload-v3-modal">
                    <button type="button" class="co-modal-close-btn co-workload-v3-x" wire:click="closeWorkloadModal" aria-label="Fechar popup">
                        <i class="bi bi-x-lg"></i>
                    </button>

                    @if($workloadDetail['responsavel'])
                        <header class="co-workload-v3-header">
                            <div class="co-workload-v3-title-row">
                                <span class="co-workload-v3-icon"><i class="bi bi-people-fill"></i></span>
                                <div>
                                    <h3 id="co-workload-detail-title">Workload da Equipe</h3>
                                    <div class="co-workload-v3-person">
                                        <strong>{{ $workloadResponsavelName }}</strong>
                                        <span>{{ $workloadDepartment }}</span>
                                    </div>
                                    <p>{{ $workloadRole }} • Desde {{ $workloadSinceLabel }}</p>
                                </div>
                            </div>
                            <div class="co-workload-v3-header-actions">
                                <button type="button">Ver perfil completo</button>
                                <button type="button" aria-label="Mais opções"><i class="bi bi-three-dots"></i></button>
                            </div>
                        </header>

                        <section class="co-workload-v3-kpis">
                            <article class="co-workload-v3-kpi gauge-card">
                                <span>Carga de Trabalho</span>
                                <div class="co-workload-v3-gauge" style="--value: {{ $workloadPercent }};"><strong>{{ $workloadPercent }}%</strong></div>
                                <b class="{{ $workloadPercent >= 85 ? 'danger' : 'ok' }}">{{ $workloadPercent >= 85 ? 'Alta' : 'Controlada' }}</b>
                                <small>Ideal: até 85%</small>
                            </article>
                            <article class="co-workload-v3-kpi">
                                <span>Tarefas Abertas</span>
                                <strong>{{ $workloadTotal }}</strong>
                                <p><b>{{ $workloadLate }}</b> vencidas</p>
                                <p><b>{{ $workloadOpenSoon }}</b> a vencer</p>
                            </article>
                            <article class="co-workload-v3-kpi">
                                <span>Obrigações sob responsabilidade</span>
                                <strong>{{ $workloadTotal + $workloadCritical }}</strong>
                                <p><b>{{ $workloadLate }}</b> vencidas</p>
                                <p><b>{{ max(($workloadTotal + $workloadCritical) - $workloadLate, 0) }}</b> a vencer</p>
                            </article>
                            <article class="co-workload-v3-kpi">
                                <span>Prazo mais próximo</span>
                                <strong>{{ $workloadMainItem['is_late'] ?? false ? 'VENCIDO' : ($workloadMainItem['vencimento'] ?? 'Sem prazo') }}</strong>
                                <p>{{ $workloadMainItem['title'] ?? 'Nenhuma tarefa pendente' }}</p>
                            </article>
                            <article class="co-workload-v3-kpi">
                                <span>Folga disponível</span>
                                <strong>{{ $workloadAvailableHours >= 0 ? '+' : '' }}{{ $workloadAvailableHours }}h</strong>
                                <p>{{ $workloadAvailableHours < 0 ? 'Déficit de capacidade' : 'Capacidade disponível' }}</p>
                            </article>
                            <article class="co-workload-v3-kpi impact">
                                <span>Impacto se não agir</span>
                                <strong>{{ max($workloadLate + $workloadCritical, 1) }} obrigações podem atrasar</strong>
                                <p>R$ {{ $workloadImpactMoney }}</p>
                                <small>{{ max($workloadImpactedClients, 1) }} clientes impactados</small>
                                <small>{{ $workloadCritical }} tarefas críticas</small>
                            </article>
                        </section>

                        <section class="co-workload-v3-grid three">
                            <article class="co-workload-v3-panel">
                                <h4>Distribuição da Carga</h4>
                                <div class="co-workload-v3-loadbar">
                                    <span class="red" style="width: {{ min(60, max(18, $workloadLate * 12)) }}%"></span>
                                    <span class="yellow" style="width: {{ min(45, max(22, $workloadCritical * 10)) }}%"></span>
                                    <span class="green"></span>
                                </div>
                                <div class="co-workload-v3-legend"><span><i class="red"></i>Acima da capacidade</span><span><i class="yellow"></i>No limite</span><span><i class="green"></i>Folga</span></div>
                            </article>
                            <article class="co-workload-v3-panel">
                                <h4>Principais demandas</h4>
                                <div class="co-workload-v3-demand-list">
                                    @forelse($workloadByCategory as $category => $amount)
                                        <div><span>{{ $category ?: 'Operacional' }}</span><b>{{ $amount }}</b><em class="{{ $amount >= 4 ? 'high' : ($amount >= 2 ? 'mid' : 'low') }}">{{ $amount >= 4 ? 'Alta' : ($amount >= 2 ? 'Média' : 'Baixa') }}</em></div>
                                    @empty
                                        <div><span>Sem demandas abertas</span><b>0</b><em class="low">Baixa</em></div>
                                    @endforelse
                                </div>
                            </article>
                            <article class="co-workload-v3-panel danger-panel">
                                <h4>Tarefas críticas vencidas</h4>
                                <div class="co-workload-v3-critical-list">
                                    @forelse(collect($workloadDetail['items'] ?? [])->filter(fn ($task) => $task['is_late'] ?? false)->take(4) as $task)
                                        <div><i class="bi bi-exclamation-triangle-fill"></i><span>{{ $task['title'] }}</span><small>{{ $task['dias_prazo'] ?? 'Vencida' }}</small></div>
                                    @empty
                                        <div><i class="bi bi-check-circle-fill"></i><span>Nenhuma tarefa vencida</span><small>Equipe em dia</small></div>
                                    @endforelse
                                </div>
                            </article>
                        </section>

                        <section class="co-workload-v3-grid three middle">
                            <article class="co-workload-v3-panel wide-chart">
                                <h4>Carga por dia <small>próximos 7 dias</small></h4>
                                <div class="co-workload-v3-chart">
                                    <div class="ideal-line"><span>Capacidade ideal (85%)</span></div>
                                    @foreach($workloadDays as $day)
                                        <div class="bar-wrap"><span style="height: {{ $day['value'] }}%"></span><small>{{ $day['label'] }}</small></div>
                                    @endforeach
                                </div>
                            </article>
                            <article class="co-workload-v3-panel">
                                <h4>Clientes com maior demanda</h4>
                                <div class="co-workload-v3-clients">
                                    @forelse($workloadClients as $client => $amount)
                                        <div><span>{{ $client ?: 'Sem empresa' }}</span><b>{{ $amount }} tarefa(s)</b></div>
                                    @empty
                                        <div><span>Nenhum cliente listado</span><b>0 tarefa</b></div>
                                    @endforelse
                                </div>
                            </article>
                            <article class="co-workload-v3-panel suggestion-panel">
                                <h4>Sugestões para equilibrar carga</h4>
                                <div><i class="bi bi-arrow-left-right"></i><span>Redistribuir tarefas de alta prioridade</span></div>
                                <div><i class="bi bi-person-check"></i><span>Delegar revisões operacionais</span></div>
                                <div><i class="bi bi-calendar-check"></i><span>Antecipar atividades próximas</span></div>
                            </article>
                        </section>

                        <section class="co-workload-v3-grid bottom">
                            <article class="co-workload-v3-panel delegable-panel">
                                <h4>Tarefas que podem ser delegadas</h4>
                                <div class="co-workload-v3-table">
                                    @forelse($workloadDelegable as $task)
                                        <div>
                                            <label><input type="checkbox" value="{{ $task['id'] }}" wire:click="$set('redistributionItemId', {{ (int) $task['id'] }})"><span>{{ $task['title'] }}</span></label>
                                            <em>{{ $task['prioridade'] }}</em>
                                            <button type="button" wire:click="$set('redistributionItemId', {{ (int) $task['id'] }})">Delegar</button>
                                        </div>
                                    @empty
                                        <div><label><span>Nenhuma tarefa para delegar</span></label><em>Baixa</em><button type="button">Delegar</button></div>
                                    @endforelse
                                </div>
                            </article>
                            <article class="co-workload-v3-panel actions-panel">
                                <h4>Próximas ações recomendadas</h4>
                                <ol>
                                    <li><span>1</span>Priorizar tarefas vencidas e críticas.</li>
                                    <li><span>2</span>Redistribuir atividades com menor dependência técnica.</li>
                                    <li><span>3</span>Revisar prazos dos clientes mais impactados.</li>
                                    <li><span>4</span>Acompanhar capacidade da equipe no fim do dia.</li>
                                </ol>
                            </article>
                            <article class="co-workload-v3-panel message-panel">
                                <h4>Mensagem para equipe</h4>
                                <textarea readonly>Olá, equipe. Precisamos equilibrar a carga de {{ $workloadResponsavelName }}. Existem {{ $workloadLate }} tarefas vencidas e {{ $workloadCritical }} críticas. Priorizar redistribuição e revisão dos próximos prazos.</textarea>
                                <div><button type="button"><i class="bi bi-copy"></i>Copiar mensagem</button><button type="button"><i class="bi bi-whatsapp"></i>Enviar no WhatsApp</button></div>
                            </article>
                        </section>

                        <div class="co-workload-v3-redistribution">
                            <div>
                                <h4>Redistribuir sem sair da tela</h4>
                                <p>Selecione uma tarefa e o novo responsável para executar a redistribuição.</p>
                            </div>
                            <label>
                                <span>Tarefa</span>
                                <select wire:model.live="redistributionItemId">
                                    <option value="">Selecione...</option>
                                    @foreach($workloadDetail['items'] as $task)
                                        <option value="{{ $task['id'] }}">{{ $task['title'] }} — {{ $task['empresa'] }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label>
                                <span>Novo responsável</span>
                                <select wire:model.live="redistributionResponsavelId">
                                    <option value="">Selecione...</option>
                                    @foreach ($this->delegateResponsavelOptions() as $responsavelId => $responsavelNome)
                                        <option value="{{ $responsavelId }}">{{ $responsavelNome }}</option>
                                    @endforeach
                                </select>
                            </label>
                        </div>

                        <footer class="co-workload-v3-footer">
                            <div><i class="bi bi-lightbulb-fill"></i><span><b>Dica:</b> mantenha a carga abaixo de 85% para evitar gargalos operacionais.</span></div>
                            <button type="button" class="secondary" wire:click="closeWorkloadModal">Fechar</button>
                            <button type="button" class="primary" wire:click="redistribuirItemSelecionado" wire:loading.attr="disabled"><i class="bi bi-arrow-left-right"></i>Redistribuir selecionada</button>
                        </footer>
                    @else
                        <header class="co-workload-v3-header">
                            <div class="co-workload-v3-title-row">
                                <span class="co-workload-v3-icon danger"><i class="bi bi-exclamation-triangle"></i></span>
                                <div>
                                    <h3 id="co-workload-detail-title">Responsável não encontrado</h3>
                                    <p>Não foi possível carregar o workload selecionado.</p>
                                </div>
                            </div>
                        </header>
                        <footer class="co-workload-v3-footer single"><button type="button" class="secondary" wire:click="closeWorkloadModal">Fechar</button></footer>
                    @endif
                </div>
            </div>
        @endif

        @if($delegateModalOpen)
            <div class="co-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="co-delegate-title" wire:click.self="cancelDelegateModal">
                <div class="co-modal-card">
                    <button type="button" class="co-modal-close-btn" wire:click="cancelDelegateModal" aria-label="Fechar popup">
                        <i class="bi bi-x-lg"></i>
                    </button>
                    <header>
                        <span class="co-section-icon purple"><i class="bi bi-person-plus"></i></span>
                        <div>
                            <h3 id="co-delegate-title">Delegar item</h3>
                            <p>Selecione o novo responsável para assumir esta pendência operacional.</p>
                        </div>
                    </header>

                    <label class="co-modal-field">
                        <span>Novo responsável</span>
                        <select wire:model.live="delegateResponsavelId">
                            <option value="">Selecione...</option>
                            @foreach ($this->delegateResponsavelOptions() as $responsavelId => $responsavelNome)
                                <option value="{{ $responsavelId }}">{{ $responsavelNome }}</option>
                            @endforeach
                        </select>
                    </label>

                    <footer>
                        <button type="button" class="co-action-btn muted" wire:click="cancelDelegateModal">Cancelar</button>
                        <button type="button" class="co-action-btn purple" wire:click="delegar" wire:loading.attr="disabled" wire:target="delegar">
                            <i class="bi bi-check2"></i>Confirmar delegação
                        </button>
                    </footer>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
