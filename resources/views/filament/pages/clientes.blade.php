<x-filament-panels::page>
    <link rel="stylesheet" href="{{ asset('css/clientes-crm.css') }}">

    @php
        $summary = $crm['summary'] ?? [];
        $clients = $clients ?? ($crm['clients'] ?? []);
        $allClients = $allClients ?? ($clientes ?? ($crm['clients'] ?? $clients));
        $actionSummary = $crm['actionSummary'] ?? [];
        $selectedClient = $selectedClient ?? (count($clients) ? $clients[0] : null);
        $selectedIdForView = (int) ($selectedClient['id'] ?? 0);
        $selectedPendencias = collect($pendencias ?? [])->where('client_id', $selectedIdForView)->take(4)->values();
        $selectedHistoricos = collect($historicos ?? [])->where('client_id', $selectedIdForView)->take(5)->values();
        $selectedDocumentos = collect($documentos ?? [])->where('client_id', $selectedIdForView)->take(5)->values();
        $criticalCount = (int) (($actionSummary['criticos']['count'] ?? ($summary['risk'] ?? 0)));
        $attentionCount = (int) (($actionSummary['atencao']['count'] ?? 0));
        $inactiveCount = (int) (($actionSummary['sem_contato']['count'] ?? 0));
        $pendingCount = (int) (($actionSummary['pendencias']['count'] ?? 0));
        $inactiveClientCount = collect($allClients)->filter(fn ($client) => (($client['contract_status_key'] ?? '') === 'cancelado') || (($client['action_status'] ?? '') === 'sem_contato'))->count();
        $activeTabKey = ($statusFilter ?? 'todos') !== 'todos' ? ($statusFilter ?? 'todos') : ($actionFilter ?? 'todos');
        $novoClienteUrl = \App\Filament\Resources\Empresas\EmpresaResource::getUrl('create');
        $onboardingCount = (int) (($summary['onboarding'] ?? count($crm['onboarding'] ?? [])));
        $ltvTotal = (float) ($summary['ltv'] ?? 0);
        $activeCount = (int) ($summary['active'] ?? 0);
        $totalCount = (int) ($summary['total'] ?? count($allClients));
        $attentionCards = collect($allClients)->sortByDesc(fn ($client) => (int) ($client['open_items'] ?? 0) + (int) ($client['late_items'] ?? 0))->take(5)->values();
        $healthScore = (int) ($selectedClient['health_score'] ?? 0);
        $healthLabel = $selectedClient['health_label'] ?? 'Sem score';
        $onboardingProgress = (int) ($selectedClient['onboarding_progress'] ?? $selectedClient['onboarding_percent'] ?? 0);
        $onboardingItems = $selectedClient['onboarding_steps'] ?? [
            ['label' => 'Diagnóstico inicial', 'status' => $onboardingProgress > 10 ? 'Concluído' : 'Pendente'],
            ['label' => 'Documentos e acessos', 'status' => $onboardingProgress > 35 ? 'Concluído' : 'Pendente'],
            ['label' => 'Configurações iniciais', 'status' => $onboardingProgress > 65 ? 'Em andamento' : 'Pendente'],
            ['label' => 'Treinamento da equipe', 'status' => $onboardingProgress > 85 ? 'Concluído' : 'Pendente'],
            ['label' => 'Go-live', 'status' => $onboardingProgress >= 100 ? 'Concluído' : 'Pendente'],
        ];
        $firstItem = ($currentPage - 1) * $perPage + 1;
        $lastItem = min($totalClientesFiltrados, $currentPage * $perPage);
    @endphp

    <div class="clientes-crm-page" x-data x-on:keydown.window.ctrl.k.prevent="$refs.clientSearch?.focus()" x-on:keydown.window.meta.k.prevent="$refs.clientSearch?.focus()">
        <section class="crm-top-shell">
            <div class="crm-title-block">
                <h1>Clientes</h1>
                <p>Gerencie seus clientes, acompanhe contratos, pendências e relacionamento.</p>
            </div>

            <div class="crm-search-box">
                <i class="bi bi-search"></i>
                <input x-ref="clientSearch" type="search" wire:model.live.debounce.450ms="search" placeholder="Buscar clientes, CNPJ, responsáveis...">
                <kbd>⌘ K</kbd>
            </div>

            <div class="crm-top-actions">
                <button type="button" class="crm-btn ghost" wire:click="abrirImportacaoClientes"><i class="bi bi-upload"></i><span>Importar</span></button>
                <a href="{{ $novoClienteUrl }}" class="crm-btn primary"><i class="bi bi-plus-lg"></i><span>Novo cliente</span><i class="bi bi-chevron-down"></i></a>
            </div>
        </section>

        <section class="crm-kpi-row" aria-label="Resumo de clientes">
            <article class="crm-kpi"><div><span>Total de clientes</span><strong>{{ $totalCount }}</strong><small>+12 este mês</small></div><i class="kpi-icon purple bi bi-people"></i></article>
            <article class="crm-kpi"><div><span>Clientes ativos</span><strong>{{ $activeCount }}</strong><small>{{ $totalCount > 0 ? round(($activeCount / max(1, $totalCount)) * 100) : 0 }}% da base</small></div><i class="kpi-icon green bi bi-person-check"></i></article>
            <article class="crm-kpi"><div><span>Com pendências</span><strong>{{ $pendingCount }}</strong><small>Alta prioridade: <b>{{ $criticalCount }}</b></small></div><i class="kpi-icon amber bi bi-exclamation-triangle"></i></article>
            <article class="crm-kpi"><div><span>Onboardings ativos</span><strong>{{ $onboardingCount }}</strong><small>Em andamento</small></div><i class="kpi-icon blue bi bi-rocket-takeoff"></i></article>
            <article class="crm-kpi"><div><span>Receita (LTV)</span><strong>R$ {{ $ltvTotal >= 1000000 ? number_format($ltvTotal / 1000000, 2, ',', '.') . 'M' : number_format($ltvTotal, 2, ',', '.') }}</strong><small>+18% vs mês anterior</small></div><i class="kpi-icon violet bi bi-rocket-takeoff"></i></article>
        </section>

        <section class="crm-attention-strip">
            <header>
                <div><span class="pulse"><i class="bi bi-exclamation-lg"></i></span><strong>ATENÇÃO NECESSÁRIA</strong><small>{{ $criticalCount + $attentionCount + $inactiveCount + $pendingCount }} itens exigem ação imediata</small></div>
                <button type="button" wire:click="filtrarCentralAcao('criticos')">Ver todos ({{ $criticalCount + $attentionCount + $inactiveCount + $pendingCount }}) <i class="bi bi-arrow-right"></i></button>
            </header>
            <div class="crm-attention-list">
                @forelse($attentionCards as $client)
                    <button type="button" wire:click="selectClient({{ (int) $client['id'] }})" class="crm-attention-card tone-{{ $client['action_tone'] ?? 'neutral' }}">
                        <span><i class="bi {{ ($client['action_tone'] ?? 'neutral') === 'danger' ? 'bi-calendar-x' : (($client['action_tone'] ?? 'neutral') === 'warning' ? 'bi-exclamation-triangle' : 'bi-bell') }}"></i></span>
                        <div><strong>{{ $client['name'] ?? 'Cliente' }}</strong><small>{{ $client['primary_problem'] ?? $client['action_reason'] ?? $client['next_action'] ?? 'Acompanhar cliente' }}</small></div>
                    </button>
                @empty
                    <div class="crm-empty-inline">Nenhum alerta crítico encontrado.</div>
                @endforelse
            </div>
        </section>

        <section class="crm-workspace">
            <main class="crm-main-panel">
                <div class="crm-table-toolbar">
                    <nav class="crm-tabs" aria-label="Filtros rápidos">
                        <button type="button" class="{{ $activeTabKey === 'todos' ? 'active' : '' }}" wire:click="filtrarVisaoClientes('todos')">Todos <b>{{ $totalCount }}</b></button>
                        <button type="button" class="{{ $activeTabKey === 'Operando bem' ? 'active' : '' }}" wire:click="filtrarVisaoClientes('ativos')">Ativos <b>{{ $activeCount }}</b></button>
                        <button type="button" class="{{ $activeTabKey === 'Em implementação' ? 'active' : '' }}" wire:click="filtrarVisaoClientes('onboarding')">Em onboarding <b>{{ $onboardingCount }}</b></button>
                        <button type="button" class="{{ $activeTabKey === 'pendencias' ? 'active' : '' }}" wire:click="filtrarVisaoClientes('pendencias')">Com pendências <b>{{ $pendingCount }}</b></button>
                        <button type="button" class="{{ $activeTabKey === 'Cancelado' || $activeTabKey === 'sem_contato' ? 'active' : '' }}" wire:click="filtrarVisaoClientes('inativos')">Inativos <b>{{ $inactiveClientCount }}</b></button>
                    </nav>

                    <div class="crm-toolbar-actions">
                        <details class="crm-menu filter-menu">
                            <summary aria-label="Filtros"><i class="bi bi-funnel"></i><span>Filtros</span></summary>
                            <div class="crm-menu-panel right filter-panel">
                                <label><span>Status</span><select wire:model.live="statusFilter"><option value="todos">Todos</option>@foreach($statusOptions as $status)<option value="{{ $status }}">{{ $status }}</option>@endforeach</select></label>
                                <label><span>Saúde</span><select wire:model.live="healthFilter"><option value="todos">Todos</option>@foreach($healthOptions as $health)<option value="{{ $health }}">{{ $health }}</option>@endforeach</select></label>
                                <label><span>Ordenar</span><select wire:model.live="sortBy"><option value="updated_at">Atualização</option><option value="action_priority">Urgência</option><option value="health_score">Health Score</option><option value="open_items">Pendências</option><option value="ltv">LTV</option><option value="name">Cliente</option></select></label>
                                <button type="button" wire:click="resetarFiltros"><i class="bi bi-arrow-counterclockwise"></i> Limpar filtros</button>
                            </div>
                        </details>
                        <button type="button" class="crm-view-toggle" aria-label="Alternar visualização"><i class="bi bi-list-ul"></i></button>
                    </div>
                </div>

                @if($quickContatoAberto)
                    <form wire:submit.prevent="registrarContatoRapido" class="crm-quick-form">
                        <strong>Registrar contato rápido</strong>
                        <select wire:model.defer="quickContatoTipo"><option value="contato">Contato</option><option value="reuniao">Reunião</option><option value="email">E-mail</option><option value="whatsapp">WhatsApp</option><option value="ligacao">Ligação</option></select>
                        <input type="text" wire:model.defer="quickContatoResumo" placeholder="Resumo objetivo do contato">
                        <label><input type="checkbox" wire:model.defer="quickConcluirPendenciaDepois"> Concluir próxima pendência</label>
                        <button type="submit">Salvar</button>
                        <button type="button" wire:click="cancelarContatoRapido">Cancelar</button>
                    </form>
                @endif

                <div class="crm-table-wrap">
                    <table class="crm-table">
                        <colgroup><col class="col-client"><col class="col-health"><col class="col-pending"><col class="col-onboarding"><col class="col-contract"><col class="col-ltv"><col class="col-next"><col class="col-actions"></colgroup>
                        <thead><tr><th>Cliente</th><th>Saúde</th><th>Pendências</th><th>Onboarding</th><th>Contrato</th><th>LTV</th><th>Próxima ação</th><th></th></tr></thead>
                        <tbody>
                            @forelse($clients as $client)
                                @php
                                    $score = max(0, min(100, (int) ($client['health_score'] ?? 0)));
                                    $progress = max(0, min(100, (int) ($client['onboarding_progress'] ?? $client['onboarding_percent'] ?? 0)));
                                @endphp
                                <tr class="tone-{{ $client['action_tone'] ?? 'neutral' }}">
                                    <td class="client-cell"><button type="button" wire:click="selectClient({{ (int) $client['id'] }})" class="client-identity"><span>{{ strtoupper(mb_substr($client['name'] ?? 'C', 0, 1)) }}</span><div><strong>{{ $client['name'] ?? 'Cliente' }}</strong><small>{{ $client['document'] ?: 'Sem documento' }}</small><small>{{ $client['contact_name'] ?? 'Sem decisor' }}</small></div></button></td>
                                    <td><span class="status-pill tone-{{ $client['health_tone'] ?? 'neutral' }}">{{ $client['health_label'] ?? 'Sem score' }}</span><div class="mini-bar"><i style="width: {{ $score }}%"></i></div></td>
                                    <td><strong class="{{ ((int)($client['open_items'] ?? 0) > 0) ? 'danger-text' : 'success-text' }}">{{ (int)($client['open_items'] ?? 0) }} aberta(s)</strong><small>{{ (int)($client['late_items'] ?? 0) }} atrasada(s)</small></td>
                                    <td><strong>{{ $progress }}%</strong><div class="mini-bar purple"><i style="width: {{ $progress }}%"></i></div><small>{{ $client['onboarding_status'] ?? $client['operation_label'] ?? 'Acompanhar' }}</small></td>
                                    <td><strong>{{ $client['contract_status'] ?? '-' }}</strong><small>{{ $client['contract_due_label'] ?? $client['updated_at_label'] ?? '' }}</small></td>
                                    <td><strong>R$ {{ number_format((float)($client['ltv'] ?? 0), 2, ',', '.') }}</strong><small>{{ $client['ltv_growth_label'] ?? 'LTV' }}</small></td>
                                    <td class="next-action"><strong>{{ $client['recommended_next_step'] ?? $client['next_action'] ?? 'Acompanhar cliente' }}</strong><small>{{ $client['primary_problem'] ?? $client['action_reason'] ?? '' }}</small></td>
                                    <td class="actions-cell">
                                        <details class="crm-menu row-menu" data-crm-row-menu>
                                            <summary aria-label="Mais opções do cliente">
                                                <i class="bi bi-three-dots-vertical" aria-hidden="true"></i>
                                            </summary>
                                            <div class="crm-menu-panel right" role="menu">
                                                <button type="button" role="menuitem" wire:click="editarCliente({{ (int) $client['id'] }})"><i class="bi bi-window-sidebar" aria-hidden="true"></i> Abrir 360°</button>
                                                <button type="button" role="menuitem" wire:click="abrirContatoRapido({{ (int) $client['id'] }})"><i class="bi bi-chat-dots" aria-hidden="true"></i> Iniciar atendimento</button>
                                                @if((int)($client['open_items'] ?? 0) > 0)
                                                    <button type="button" role="menuitem" wire:click="concluirProximaPendencia({{ (int) $client['id'] }})" wire:confirm="Concluir a próxima pendência aberta deste cliente?"><i class="bi bi-check2-circle" aria-hidden="true"></i> Concluir pendência</button>
                                                @endif
                                                <button type="button" role="menuitem" wire:click="criarOnboarding({{ (int) $client['id'] }})"><i class="bi bi-rocket-takeoff" aria-hidden="true"></i> Criar onboarding</button>
                                            </div>
                                        </details>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="crm-empty-cell">Nenhum cliente encontrado com os filtros atuais.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="crm-mobile-client-list" aria-label="Clientes em visualização mobile">
                    @forelse($clients as $client)
                        @php
                            $score = max(0, min(100, (int) ($client['health_score'] ?? 0)));
                            $progress = max(0, min(100, (int) ($client['onboarding_progress'] ?? $client['onboarding_percent'] ?? 0)));
                            $openItems = (int)($client['open_items'] ?? 0);
                            $lateItems = (int)($client['late_items'] ?? 0);
                            $hasPending = $openItems > 0;
                        @endphp
                        <article class="crm-mobile-client-card tone-{{ $client['action_tone'] ?? 'neutral' }}">
                            <button type="button" class="mobile-client-summary" wire:click="selectClient({{ (int) $client['id'] }})">
                                <span class="mobile-client-avatar">{{ strtoupper(mb_substr($client['name'] ?? 'C', 0, 1)) }}</span>
                                <span class="mobile-client-title">
                                    <strong>{{ $client['name'] ?? 'Cliente' }}</strong>
                                    <small>{{ $client['document'] ?: 'Sem documento' }}</small>
                                </span>
                                <span class="mobile-client-score tone-{{ $client['health_tone'] ?? 'neutral' }}">{{ $client['health_label'] ?? 'Sem score' }}</span>
                            </button>

                            <div class="mobile-client-essentials">
                                <div>
                                    <span>Pendências</span>
                                    <strong class="{{ $hasPending ? 'danger-text' : 'success-text' }}">{{ $openItems }} aberta(s)</strong>
                                    <small>{{ $lateItems }} atrasada(s)</small>
                                </div>
                                <div>
                                    <span>Próxima ação</span>
                                    <strong>{{ $client['recommended_next_step'] ?? $client['next_action'] ?? 'Acompanhar cliente' }}</strong>
                                    <small>{{ $client['primary_problem'] ?? $client['action_reason'] ?? '' }}</small>
                                </div>
                            </div>

                            <details class="mobile-client-details">
                                <summary>Ver detalhes <i class="bi bi-chevron-down" aria-hidden="true"></i></summary>
                                <div class="mobile-details-grid">
                                    <div><span>Responsável</span><strong>{{ $client['contact_name'] ?? 'Sem decisor' }}</strong></div>
                                    <div><span>Onboarding</span><strong>{{ $progress }}%</strong><small>{{ $client['onboarding_status'] ?? $client['operation_label'] ?? 'Acompanhar' }}</small></div>
                                    <div><span>Contrato</span><strong>{{ $client['contract_status'] ?? '-' }}</strong><small>{{ $client['contract_due_label'] ?? $client['updated_at_label'] ?? '' }}</small></div>
                                    <div><span>LTV</span><strong>R$ {{ number_format((float)($client['ltv'] ?? 0), 2, ',', '.') }}</strong><small>{{ $client['ltv_growth_label'] ?? 'LTV' }}</small></div>
                                </div>
                                <div class="mobile-progress-block">
                                    <span>Progresso do onboarding</span>
                                    <div class="mini-bar purple"><i style="width: {{ $progress }}%"></i></div>
                                </div>
                                <div class="mobile-actions-row">
                                    <button type="button" wire:click="editarCliente({{ (int) $client['id'] }})"><i class="bi bi-window-sidebar" aria-hidden="true"></i> 360°</button>
                                    <button type="button" wire:click="abrirContatoRapido({{ (int) $client['id'] }})"><i class="bi bi-chat-dots" aria-hidden="true"></i> Atendimento</button>
                                    @if($hasPending)
                                        <button type="button" wire:click="concluirProximaPendencia({{ (int) $client['id'] }})" wire:confirm="Concluir a próxima pendência aberta deste cliente?"><i class="bi bi-check2-circle" aria-hidden="true"></i> Concluir</button>
                                    @endif
                                    <button type="button" wire:click="criarOnboarding({{ (int) $client['id'] }})"><i class="bi bi-rocket-takeoff" aria-hidden="true"></i> Onboarding</button>
                                </div>
                            </details>
                        </article>
                    @empty
                        <div class="crm-mobile-empty">Nenhum cliente encontrado com os filtros atuais.</div>
                    @endforelse
                </div>

                <footer class="crm-pagination">
                    <span>Mostrando {{ $totalClientesFiltrados > 0 ? $firstItem : 0 }} a {{ $lastItem }} de {{ $totalClientesFiltrados }} clientes</span>
                    <div class="page-buttons">
                        <button type="button" wire:click="previousPage" @disabled($currentPage <= 1)><i class="bi bi-chevron-left"></i></button>
                        @for($page = max(1, $currentPage - 1); $page <= min($totalPages, $currentPage + 1); $page++)
                            <button type="button" class="{{ $page === $currentPage ? 'active' : '' }}" wire:click="goToPage({{ $page }})">{{ $page }}</button>
                        @endfor
                        @if($currentPage + 1 < $totalPages)<span>...</span><button type="button" wire:click="goToPage({{ $totalPages }})">{{ $totalPages }}</button>@endif
                        <button type="button" wire:click="nextPage" @disabled($currentPage >= $totalPages)><i class="bi bi-chevron-right"></i></button>
                    </div>
                    <label><select wire:model.live="perPage"><option value="6">6 por página</option><option value="10">10 por página</option><option value="15">15 por página</option><option value="25">25 por página</option></select></label>
                </footer>
            </main>

            <aside class="crm-right-panel">
                @if($selectedClient)
                    <article class="side-card client-profile-card">
                        <div class="profile-head">
                            <span>{{ strtoupper(mb_substr($selectedClient['name'] ?? 'C', 0, 1)) }}</span>
                            <div><strong>{{ $selectedClient['name'] }}</strong><small>{{ $selectedClient['document'] ?: 'Sem documento' }}</small><small>{{ $selectedClient['contact_name'] ?? 'Sem decisor' }} · {{ $selectedClient['contact_email'] ?? 'Sem e-mail' }}</small><span class="client-active-badge">Ativo</span></div>
                            <button type="button" class="panel-close" wire:click="resetarFiltros" title="Limpar filtros"><i class="bi bi-x-lg"></i></button>
                        </div>
                        <nav>
                            <button type="button" class="{{ $clientPanelTab === 'overview' ? 'active' : '' }}" wire:click="setClientPanelTab('overview')">Visão geral</button>
                            <button type="button" class="{{ $clientPanelTab === 'relationship' ? 'active' : '' }}" wire:click="setClientPanelTab('relationship')">Relacionamento</button>
                            <button type="button" class="{{ $clientPanelTab === 'history' ? 'active' : '' }}" wire:click="setClientPanelTab('history')">Histórico</button>
                            <button type="button" class="{{ $clientPanelTab === 'documents' ? 'active' : '' }}" wire:click="setClientPanelTab('documents')">Documentos</button>
                        </nav>
                    </article>

                    @if($clientPanelTab === 'overview')
                        <article class="side-card health-card"><header><strong>Saúde do cliente</strong><small>Atualizado há 2h</small></header><div class="health-content"><div class="health-ring" style="--score: {{ max(0, min(100, $healthScore)) }}"><strong>{{ $healthScore }}</strong><span>/100</span></div><div><b>{{ $healthLabel }}</b><p>{{ $selectedClient['primary_problem'] ?? 'Cliente acompanhado pela central operacional.' }}</p><button type="button" wire:click="editarCliente({{ (int) $selectedClient['id'] }})">Ver análise completa</button></div></div></article>
                        <article class="side-card"><header><strong>Próximos passos</strong><button type="button" wire:click="filtrarCentralAcao('pendencias')">Ver todos</button></header><div class="side-list"><div class="step-item danger"><span><i class="bi bi-calendar2-x"></i></span><div><strong>{{ $selectedClient['recommended_next_step'] ?? $selectedClient['next_action'] ?? 'Concluir pendência atrasada' }}</strong></div><em>Hoje</em></div><div class="step-item neutral"><span><i class="bi bi-calendar3"></i></span><div><strong>Reunião de alinhamento</strong></div><em>Amanhã</em></div><div class="step-item neutral"><span><i class="bi bi-calendar-check"></i></span><div><strong>{{ $selectedClient['contract_status'] ?? 'Acompanhar contrato' }}</strong></div><em>Status</em></div><button type="button" class="add-next-step" wire:click="abrirContatoRapido({{ (int) $selectedClient['id'] }})"><i class="bi bi-plus-lg"></i> Adicionar próximo passo</button></div></article>
                        <article class="side-card"><header><strong>Pendências em aberto</strong><button type="button" wire:click="filtrarCentralAcao('pendencias')">Ver todas ({{ (int)($selectedClient['open_items'] ?? 0) }})</button></header><div class="side-list compact">@forelse($selectedPendencias as $pendencia)<div class="pendency-line"><span><i class="bi bi-exclamation-triangle"></i></span><strong>{{ $pendencia['titulo'] ?? $pendencia['title'] ?? 'Pendência aberta' }}</strong><em>{{ $pendencia['status'] ?? 'Aberta' }}</em></div>@empty<div class="empty-dashed">Nenhuma pendência aberta para este cliente.</div>@endforelse</div></article>
                        <article class="side-card onboarding-card"><header><strong>Onboarding</strong><b>{{ $onboardingProgress }}%</b></header><div class="big-progress"><i style="width: {{ $onboardingProgress }}%"></i></div><div class="onboarding-steps">@foreach($onboardingItems as $item)@php $status = is_array($item) ? ($item['status'] ?? 'Pendente') : 'Pendente'; $label = is_array($item) ? ($item['label'] ?? 'Etapa') : (string) $item; @endphp<div class="{{ $status === 'Concluído' ? 'done' : ($status === 'Em andamento' ? 'current' : '') }}"><span></span><strong>{{ $label }}</strong><em>{{ $status }}</em></div>@endforeach</div></article>
                        <article class="side-card quick-actions-card"><header><strong>Ações rápidas</strong></header><div class="quick-grid"><button type="button" wire:click="editarCliente({{ (int) $selectedClient['id'] }})"><i class="bi bi-window-sidebar"></i><span>Abrir portal</span></button><button type="button" wire:click="abrirContatoRapido({{ (int) $selectedClient['id'] }})"><i class="bi bi-chat-dots"></i><span>Iniciar atendimento</span></button><button type="button" wire:click="criarOnboarding({{ (int) $selectedClient['id'] }})"><i class="bi bi-rocket-takeoff"></i><span>Nova pendência</span></button><button type="button" wire:click="abrirContatoRapido({{ (int) $selectedClient['id'] }})"><i class="bi bi-envelope"></i><span>Enviar mensagem</span></button></div></article>
                    @elseif($clientPanelTab === 'relationship')
                        <article class="side-card quick-actions-card"><header><strong>Ações rápidas</strong></header><div class="quick-grid"><button type="button" wire:click="editarCliente({{ (int) $selectedClient['id'] }})"><i class="bi bi-window-sidebar"></i><span>Abrir 360°</span></button><button type="button" wire:click="abrirContatoRapido({{ (int) $selectedClient['id'] }})"><i class="bi bi-chat-dots"></i><span>Iniciar atendimento</span></button><button type="button" wire:click="criarOnboarding({{ (int) $selectedClient['id'] }})"><i class="bi bi-rocket-takeoff"></i><span>Novo onboarding</span></button>@if((int)($selectedClient['open_items'] ?? 0) > 0)<button type="button" wire:click="concluirProximaPendencia({{ (int) $selectedClient['id'] }})" wire:confirm="Concluir a próxima pendência aberta deste cliente?"><i class="bi bi-check2-circle"></i><span>Concluir pendência</span></button>@endif</div></article>
                    @elseif($clientPanelTab === 'history')
                        <article class="side-card timeline-card"><header><strong>Contexto recente</strong><button type="button" wire:click="editarCliente({{ (int) $selectedClient['id'] }})">Abrir 360°</button></header><div class="timeline-list">@forelse($selectedHistoricos as $historico)<div><span></span><strong>{{ $historico['tipo'] ?? $historico['title'] ?? 'Atualização' }}</strong><small>{{ $historico['descricao'] ?? $historico['description'] ?? 'Registro do relacionamento.' }}</small></div>@empty<div><span></span><strong>Sem histórico recente</strong><small>Registre contatos para criar contexto operacional.</small></div>@endforelse</div></article>
                    @else
                        <article class="side-card"><header><strong>Documentos</strong><button type="button" wire:click="editarCliente({{ (int) $selectedClient['id'] }})">Gerenciar</button></header><div class="side-list compact">@forelse($selectedDocumentos as $documento)<div class="pendency-line"><span><i class="bi bi-file-earmark-text"></i></span><strong>{{ $documento['titulo'] ?? $documento['nome'] ?? $documento['title'] ?? 'Documento' }}</strong><em>{{ $documento['status'] ?? 'Atual' }}</em></div>@empty<div class="empty-dashed">Nenhum documento encontrado para este cliente.</div>@endforelse</div></article>
                    @endif
                @else
                    <article class="side-card"><strong>Nenhum cliente selecionado</strong><p>Selecione um cliente na tabela para abrir o painel lateral.</p></article>
                @endif
            </aside>
        </section>

        @if($clienteModalAberto && $selectedClient)
            <div class="crm-modal-backdrop" role="dialog" aria-modal="true">
                <form wire:submit.prevent="salvarClienteCrm" class="crm-modal-card">
                    <header><div><span>Cliente 360°</span><strong>{{ $selectedClient['name'] }}</strong><small>{{ $selectedClient['document'] ?: 'Sem documento' }}</small></div><button type="button" wire:click="fecharClienteModal"><i class="bi bi-x-lg"></i></button></header>
                    <div class="crm-modal-grid">
                        <label><span>Status do contrato</span><select wire:model.defer="editStatusContrato"><option value="">Manter atual</option>@foreach($statusOptions as $status)<option value="{{ $status }}">{{ $status }}</option>@endforeach</select></label>
                        <label><span>Saúde manual</span><select wire:model.defer="editHealthManual"><option value="">Automática</option>@foreach($healthOptions as $health)<option value="{{ $health }}">{{ $health }}</option>@endforeach</select></label>
                        <label><span>Responsável</span><input type="text" wire:model.defer="editContatoNome" placeholder="Nome do contato"></label>
                        <label><span>E-mail</span><input type="email" wire:model.defer="editContatoEmail" placeholder="email@cliente.com"></label>
                        <label><span>WhatsApp</span><input type="text" wire:model.defer="editContatoWhatsapp" placeholder="(00) 00000-0000"></label>
                        <label class="full"><span>Observações / próxima ação</span><textarea rows="4" wire:model.defer="editObservacoes" placeholder="Resumo, risco, próximos passos e observações do relacionamento"></textarea></label>
                    </div>
                    <footer><button type="button" wire:click="fecharClienteModal">Cancelar</button><button type="submit">Salvar CRM</button></footer>
                </form>
            </div>
        @endif

        <script>
            (() => {
                const bindClientesMenus = () => {
                    if (window.__clientesCrmMenusBound) {
                        return;
                    }

                    window.__clientesCrmMenusBound = true;

                    const closeMenus = (except = null) => {
                        document.querySelectorAll('.crm-menu[open]').forEach((menu) => {
                            if (menu !== except) {
                                menu.removeAttribute('open');
                            }
                        });
                    };

                    document.addEventListener('click', (event) => {
                        const summary = event.target.closest('.crm-menu > summary');

                        if (summary) {
                            const currentMenu = summary.closest('.crm-menu');

                            window.requestAnimationFrame(() => {
                                if (currentMenu?.hasAttribute('open')) {
                                    closeMenus(currentMenu);
                                }
                            });

                            return;
                        }

                        if (! event.target.closest('.crm-menu')) {
                            closeMenus();
                        }
                    }, true);

                    document.addEventListener('keydown', (event) => {
                        if (event.key === 'Escape') {
                            closeMenus();
                        }
                    });

                    document.addEventListener('click', (event) => {
                        if (event.target.closest('.crm-menu-panel button')) {
                            window.requestAnimationFrame(() => closeMenus());
                        }
                    });
                };

                bindClientesMenus();
            })();
        </script>
    </div>
</x-filament-panels::page>
