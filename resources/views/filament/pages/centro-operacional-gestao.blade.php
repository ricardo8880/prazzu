<x-filament-panels::page>
    <link rel="stylesheet" href="{{ asset('css/centro-operacional.css') }}?v={{ file_exists(public_path('css/centro-operacional.css')) ? filemtime(public_path('css/centro-operacional.css')) : time() }}">

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
                    <h1>Operação Interna</h1>
                    <span class="co-info">i</span>
                </div>
                <p>Workload, aprovações e pendências financeiras em uma área própria, com espaço para operar sem apertar os cards.</p>
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
                    <strong>Centro Operacional</strong>
                    <small>Riscos, resolver agora e resultados</small>
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

        @if($workloadModalOpen)
            @php $workloadDetail = $this->selectedWorkloadDetail(); @endphp
            <div class="co-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="co-workload-detail-title" wire:click.self="closeWorkloadModal">
                <div class="co-modal-card co-detail-modal-card co-workload-modal-card">
                    @if($workloadDetail['responsavel'])
                        <header>
                            <span class="co-section-icon muted"><i class="bi bi-people"></i></span>
                            <div>
                                <h3 id="co-workload-detail-title">Workload de {{ $workloadDetail['responsavel']->nome }}</h3>
                                <p>{{ $workloadDetail['total'] }} tarefa(s) aberta(s), {{ $workloadDetail['critical'] }} prioridade alta/crítica e {{ $workloadDetail['late'] }} vencida(s).</p>
                            </div>
                        </header>

                        <div class="co-workload-modal-summary">
                            <article><small>Tarefas</small><strong>{{ $workloadDetail['total'] }}</strong></article>
                            <article><small>Alta/Crítica</small><strong>{{ $workloadDetail['critical'] }}</strong></article>
                            <article><small>Vencidas</small><strong>{{ $workloadDetail['late'] }}</strong></article>
                        </div>

                        @if(! empty($workloadDetail['recommendation']))
                            <div class="co-decision-box purple">
                                <div>
                                    <small>Recomendação de redistribuição</small>
                                    <strong>{{ $workloadDetail['recommendation']['title'] }}</strong>
                                    <p>{{ $workloadDetail['recommendation']['text'] }}</p>
                                </div>
                                @if(! empty($workloadDetail['recommendation']['target_id']) && ! empty($workloadDetail['items'][0]['id']))
                                    <button type="button" class="co-mini-action dark" wire:click="preencherSugestaoRedistribuicao({{ (int) $workloadDetail['items'][0]['id'] }}, {{ (int) $workloadDetail['recommendation']['target_id'] }})" wire:loading.attr="disabled">
                                        <i class="bi bi-magic"></i>Usar sugestão
                                    </button>
                                @endif
                            </div>
                        @endif

                        <div class="co-workload-modal-list">
                            @forelse($workloadDetail['items'] as $task)
                                <article class="{{ $task['is_late'] ? 'danger' : '' }}">
                                    <div>
                                        <strong>{{ $task['title'] }}</strong>
                                        <span>{{ $task['empresa'] }} • {{ $task['categoria'] }}</span>
                                        <small>{{ $task['status'] }} • {{ $task['prioridade'] }} • {{ $task['vencimento'] }} • {{ $task['dias_prazo'] ?? '' }}</small>
                                    </div>
                                    <a class="co-mini-action" href="{{ $task['url'] }}"><i class="bi bi-box-arrow-up-right"></i>Abrir</a>
                                </article>
                            @empty
                                <div class="co-empty clean"><strong>Nenhuma tarefa aberta para este responsável.</strong></div>
                            @endforelse
                        </div>

                        <div class="co-redistribution-box">
                            <h4>Redistribuir sem sair da tela</h4>
                            <p>Escolha uma tarefa desse responsável e envie para outra pessoa disponível no seu escopo.</p>
                            <div class="co-redistribution-grid">
                                <label class="co-modal-field">
                                    <span>Tarefa</span>
                                    <select wire:model.live="redistributionItemId">
                                        <option value="">Selecione...</option>
                                        @foreach($workloadDetail['items'] as $task)
                                            <option value="{{ $task['id'] }}">{{ $task['title'] }} — {{ $task['empresa'] }}</option>
                                        @endforeach
                                    </select>
                                </label>
                                <label class="co-modal-field">
                                    <span>Novo responsável</span>
                                    <select wire:model.live="redistributionResponsavelId">
                                        <option value="">Selecione...</option>
                                        @foreach ($this->delegateResponsavelOptions() as $responsavelId => $responsavelNome)
                                            <option value="{{ $responsavelId }}">{{ $responsavelNome }}</option>
                                        @endforeach
                                    </select>
                                </label>
                            </div>
                        </div>

                        <footer class="co-detail-footer-actions">
                            <button type="button" class="co-action-btn muted" wire:click="closeWorkloadModal">Fechar</button>
                            <button type="button" class="co-action-btn purple" wire:click="redistribuirItemSelecionado" wire:loading.attr="disabled"><i class="bi bi-arrow-left-right"></i>Redistribuir selecionada</button>
                        </footer>
                    @else
                        <header>
                            <span class="co-section-icon red"><i class="bi bi-exclamation-triangle"></i></span>
                            <div>
                                <h3 id="co-workload-detail-title">Responsável não encontrado</h3>
                                <p>Não foi possível carregar o workload selecionado.</p>
                            </div>
                        </header>
                        <footer><button type="button" class="co-action-btn muted" wire:click="closeWorkloadModal">Fechar</button></footer>
                    @endif
                </div>
            </div>
        @endif

        @if($delegateModalOpen)
            <div class="co-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="co-delegate-title" wire:click.self="cancelDelegateModal">
                <div class="co-modal-card">
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
