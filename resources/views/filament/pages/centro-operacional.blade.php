<x-filament-panels::page>

    @php
        $loadError = $loadError ?? null;
        $cards = $data['cards'] ?? [];
        $riskCards = $data['risk_cards'] ?? [];
        $alertasInteligentes = $data['alertas_inteligentes'] ?? [];
        $resolverAgora = collect($data['resolver_agora'] ?? [])->take(10)->values()->all();
        $clientesCriticos = $data['clientes_criticos'] ?? [];
        $vencimentos = $data['vencimentos'] ?? ['selected' => 'today', 'periods' => [], 'rows' => [], 'total' => 0];
        $deadlineRows = collect($vencimentos['rows'] ?? [])->take(4)->values();
        $deadlineTotal = (int) ($vencimentos['total'] ?? $deadlineRows->sum('value'));
        $aprovacoes = collect($data['aprovacoes'] ?? [])->take(5)->values()->all();
        $financeiro = collect($data['financeiro'] ?? [])->take(5)->values()->all();
        $financeiroResumo = $data['financeiro_resumo'] ?? ['indicadores' => [], 'impacto_total' => 'R$ 0,00'];
        $workload = collect($data['workload'] ?? [])->take(5)->values()->all();
        $departamentos = $data['departamentos'] ?? [];
        $resultadosMes = $data['resultados_mes'] ?? [];
        $healthScore = $data['health_score'] ?? ['label' => 'Excelente', 'tone' => 'success', 'value' => 100];
        $statusOptions = $data['status_options'] ?? [];
        $departmentOptions = $data['department_options'] ?? [];
        $dateRangeOptions = $data['date_range_options'] ?? [];
        $globalSearchData = $data['global_search'] ?? ['term' => '', 'results' => [], 'minimum_chars' => 2];
        $globalSearchResults = collect($globalSearchData['results'] ?? [])->take(10)->values();
        $globalSearchTerm = (string) ($globalSearchData['term'] ?? '');
        $globalSearchMinimum = (int) ($globalSearchData['minimum_chars'] ?? 2);
        $dateRangeLabel = $dateRangeOptions[$dateRange] ?? 'Hoje';
        $todayLabel = now()->translatedFormat('d \d\e F');
        $defaultIcons = ['bi-exclamation-triangle-fill', 'bi-calendar2-week-fill', 'bi-clock-fill', 'bi-file-earmark-text-fill', 'bi-currency-dollar'];
        $defaultIconClass = ['danger', 'warning', 'danger', 'info', 'success'];
        $departmentColors = ['Fiscal' => 'red', 'Contábil' => 'blue', 'DP' => 'orange', 'Departamento Pessoal' => 'orange', 'Trabalhista' => 'orange', 'Societário' => 'purple', 'Financeiro' => 'green', 'Operacional' => 'blue'];
        $departmentHex = ['red' => '#ef334e', 'blue' => '#2474ff', 'orange' => '#ff9f1c', 'purple' => '#7c3aed', 'green' => '#16a34a'];
        $departmentRows = collect($departamentos)->take(4)->values();
        $departmentTotal = (int) collect($departamentos)->sum('value');
        $acc = 0;
        $segments = [];
        foreach ($departmentRows as $row) {
            $value = (int) ($row['value'] ?? 0);
            if ($departmentTotal <= 0 || $value <= 0) { continue; }
            $label = $row['label'] ?? 'Operacional';
            $dot = $departmentColors[$label] ?? 'blue';
            $start = $acc;
            $acc += round(($value / max(1, $departmentTotal)) * 100, 2);
            $segments[] = ($departmentHex[$dot] ?? '#2474ff') . ' ' . $start . '% ' . min(100, $acc) . '%';
        }
        $donutGradient = count($segments) ? 'conic-gradient(' . implode(', ', $segments) . ')' : 'conic-gradient(#e5e7eb 0 100%)';
        $resultTone = $healthScore['tone'] ?? 'success';
        $resultMessage = match ($resultTone) {
            'danger' => 'Atenção máxima: existem gargalos que precisam ser tratados hoje.',
            'warning' => 'Atenção: priorize os itens vencidos e aprovações paradas.',
            'info' => 'Bom ritmo: ainda existem pontos para melhorar este mês.',
            default => 'Excelente! Seu escritório está no caminho certo. 🚀',
        };
        $cardsByKey = collect($cards)->keyBy('key');
        $riskCard = $cardsByKey->get('risk', ['value' => 0, 'tone' => 'success']);
        $todayCard = $cardsByKey->get('today', ['value' => 0, 'tone' => 'success']);
        $lateCard = $cardsByKey->get('late', ['value' => 0, 'tone' => 'success']);
        $operationalRiskTotal = (int) ($riskCard['value'] ?? 0) + (int) ($todayCard['value'] ?? 0) + (int) ($lateCard['value'] ?? 0);
        $operationalRiskTone = $operationalRiskTotal > 0 ? 'danger' : 'success';
        $criticalClientsCount = collect($clientesCriticos)->count();
        $resolverCollection = collect($resolverAgora);
        $resolverTotal = $resolverCollection->count();
        $resolverDanger = $resolverCollection->where('tone', 'danger')->count();
        $resolverWarning = $resolverCollection->where('tone', 'warning')->count();
        $resolverWithoutOwner = $resolverCollection->filter(fn ($item) => empty($item['responsavel']) || ($item['responsavel'] ?? null) === 'Sem responsável')->count();
        $resolverMainAction = $resolverTotal > 0
            ? ($resolverDanger > 0 ? 'Comece pelos itens vencidos ou com risco de multa.' : ($resolverWarning > 0 ? 'Priorize os vencimentos de hoje e aprovações paradas.' : 'Abra os itens em ordem e conclua o que estiver pronto.'))
            : 'Operação sem risco para resolver neste momento.';
        $clientesCriticosCollection = collect($clientesCriticos)->values();
        $clientesMaiorRisco = $clientesCriticosCollection->take(5)->values();
        $clientesRiscoAlto = $clientesCriticosCollection->filter(fn ($cliente) => in_array(($cliente['tone'] ?? ''), ['danger', 'warning'], true))->count();
        $clientesComItem = $clientesCriticosCollection->filter(fn ($cliente) => ! empty($cliente['item_id']))->count();
        $workloadCollection = collect($workload)->values();
        $responsaveisAtencao = $workloadCollection
            ->filter(fn ($row) => in_array(($row['tone'] ?? ''), ['danger', 'warning', 'attention'], true))
            ->sortByDesc(fn ($row) => (int) ($row['percent'] ?? 0))
            ->take(4)
            ->values();
        $topResponsaveis = $workloadCollection
            ->filter(fn ($row) => ($row['tone'] ?? '') === 'success')
            ->sortBy(fn ($row) => (int) ($row['percent'] ?? 0))
            ->take(3)
            ->values();
        $responsaveisAtencaoTotal = $responsaveisAtencao->count();
        $workloadTotalAberto = $workloadCollection->sum(fn ($row) => (int) ($row['total'] ?? 0));
        $tendenciasOperacionais = collect($data['tendencias_operacionais'] ?? [])->take(4)->values();
        $alertasColecao = collect($alertasInteligentes ?? []);
        $alertasCriticos = $alertasColecao
            ->filter(fn ($group) => in_array(($group['tone'] ?? 'info'), ['danger', 'warning', 'attention'], true))
            ->values();
        $alertasInformativos = $alertasColecao
            ->reject(fn ($group) => in_array(($group['tone'] ?? 'info'), ['danger', 'warning', 'attention'], true))
            ->values();
        $alertasCriticosTotal = $alertasCriticos->sum(fn ($group) => count($group['items'] ?? []));
        $alertasInformativosTotal = $alertasInformativos->sum(fn ($group) => count($group['items'] ?? []));
        $alertasTotal = $alertasCriticosTotal + $alertasInformativosTotal;
    @endphp

    <section class="contabilidade-lote3-scope" aria-label="Propósito da Mesa Operacional">
        <div class="contabilidade-lote3-scope__top">
            <div>
                <span class="contabilidade-lote3-eyebrow"><i class="bi bi-command"></i> Mesa Operacional</span>
                <h2>Visão de comando da operação diária</h2>
                <p>Esta tela organiza prioridades, carga, gargalos e redistribuição. A execução detalhada permanece nas abas donas: Pendências, Documentos, Aprovações, SLA e Timeline.</p>
            </div>
            <div class="contabilidade-lote3-actions">
                <a class="contabilidade-lote3-action primary" href="{{ \App\Filament\Pages\Pendencias::getUrl() }}"><i class="bi bi-list-check"></i> Resolver pendências</a>
                <a class="contabilidade-lote3-action" href="{{ \App\Filament\Pages\Kanban::getUrl() }}"><i class="bi bi-columns-gap"></i> Kanban</a>
                <a class="contabilidade-lote3-action" href="{{ \App\Filament\Pages\TimelineOperacional::getUrl() }}"><i class="bi bi-calendar2-week"></i> Timeline</a>
            </div>
        </div>
        <div class="contabilidade-lote3-note">Regra do Lote 3: Mesa Operacional orienta e prioriza; não deve virar duplicata completa de Pendências ou Aprovações.</div>
    </section>

    <div class="co-page co-model" wire:loading.class="is-loading" x-data="{ searchOpen: false }" @keydown.window.ctrl.k.prevent="$refs.globalSearch.focus(); searchOpen = true" @keydown.window.meta.k.prevent="$refs.globalSearch.focus(); searchOpen = true" @keydown.escape.window="searchOpen = false">
        <section class="co-topbar">
            <div>
                <div class="co-title-row">
                    <h1>Central Operacional</h1>
                    <span class="co-info">i</span>
                </div>
                <p>Mesa principal de execução: priorize, resolva, delegue e acompanhe o trabalho do dia sem sair do fluxo operacional.</p>
            </div>

            <div class="co-top-actions">
                <div class="co-global-search" @click.outside="searchOpen = false">
                    <div class="co-global-search-box" :class="{ 'is-active': searchOpen }">
                        <i class="bi bi-search"></i>
                        <input
                            x-ref="globalSearch"
                            type="search"
                            placeholder="Buscar cliente, tarefa, documento, contrato ou responsável..."
                            wire:model.live.debounce.350ms="globalSearch"
                            @focus="searchOpen = true"
                            @input="searchOpen = true"
                            autocomplete="off"
                        >
                        <kbd>Ctrl K</kbd>
                    </div>

                    <div class="co-global-search-results" x-show="searchOpen" x-transition>
                        @if(mb_strlen($globalSearchTerm) < $globalSearchMinimum)
                            <div class="co-global-search-state">
                                <i class="bi bi-command"></i>
                                <strong>Pesquisa global</strong>
                                <span>Digite pelo menos {{ $globalSearchMinimum }} caracteres para buscar em clientes, tarefas, documentos, contratos e responsáveis.</span>
                            </div>
                        @else
                            <div class="co-global-search-head">
                                <span>Resultados para “{{ $globalSearchTerm }}”</span>
                                <button type="button" wire:click="clearGlobalSearch" @click="searchOpen = false">Limpar</button>
                            </div>

                            <div class="co-global-search-list">
                                @forelse($globalSearchResults as $result)
                                    <a href="{{ $result['url'] }}" class="co-global-search-row {{ $result['tone'] ?? 'info' }}">
                                        <span class="co-global-search-icon {{ $result['priority_tone'] ?? ($result['tone'] ?? 'info') }}">
                                            <i class="bi {{ match($result['match_type'] ?? 'tarefa') {
                                                'cliente' => 'bi-building',
                                                'responsavel' => 'bi-person-badge',
                                                'documento' => 'bi-file-earmark-text',
                                                'contrato' => 'bi-file-earmark-lock',
                                                'tipo' => 'bi-tags',
                                                default => 'bi-check2-square',
                                            } }}"></i>
                                        </span>
                                        <span class="co-global-search-content">
                                            <strong>{{ $result['title'] ?? 'Item operacional' }}</strong>
                                            <small>{{ $result['empresa'] ?? 'Sem cliente' }} • {{ $result['responsavel'] ?? 'Sem responsável' }} • {{ $result['due_human'] ?? 'Sem prazo' }}</small>
                                            <em>{{ $result['match_label'] ?? 'Resultado' }}: {{ $result['search_context'] ?? '-' }}</em>
                                        </span>
                                        <span class="co-global-search-status {{ $result['priority_tone'] ?? 'info' }}">{{ $result['priority'] ?? 'Média' }}</span>
                                    </a>
                                @empty
                                    <div class="co-global-search-state empty">
                                        <i class="bi bi-search"></i>
                                        <strong>Nenhum resultado encontrado.</strong>
                                        <span>Tente buscar por outro cliente, tarefa, documento, contrato ou responsável.</span>
                                    </div>
                                @endforelse
                            </div>
                        @endif
                    </div>
                </div>

                <div class="co-dropdown" x-data="{ open: false }" @click.outside="open = false">
                    <button type="button" class="co-toolbar-btn co-date-btn" @click="open = ! open">
                        <i class="bi bi-calendar3 co-toolbar-icon"></i>
                        <span>{{ $dateRangeLabel }}, {{ $todayLabel }}</span>
                        <i class="bi bi-chevron-down co-chevron" :class="{ 'rotate': open }"></i>
                    </button>
                    <div class="co-menu" x-show="open" x-transition>
                        @foreach ($dateRangeOptions as $value => $label)
                            <button type="button" wire:click="setDateRange('{{ $value }}')" @click="open = false" class="{{ $dateRange === $value ? 'active' : '' }}">{{ $label }}</button>
                        @endforeach
                    </div>
                </div>

                @if($dateRange === 'custom')
                    <div class="co-custom-date-range" aria-label="Período personalizado">
                        <label>
                            <span>Início</span>
                            <input type="date" wire:model.live.debounce.500ms="customStartDate">
                        </label>
                        <label>
                            <span>Fim</span>
                            <input type="date" wire:model.live.debounce.500ms="customEndDate">
                        </label>
                    </div>
                @endif

                <div class="co-dropdown" x-data="{ open: false }" @click.outside="open = false">
                    <button type="button" class="co-toolbar-btn" @click="open = ! open">
                        <i class="bi bi-funnel co-toolbar-icon"></i>
                        <span>Filtros</span>
                    </button>
                    <div class="co-filter-panel" x-show="open" x-transition>
                        <label>
                            <span>Status</span>
                            <select wire:model.live="statusFilter">
                                @foreach ($statusOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label>
                            <span>Departamento</span>
                            <select wire:model.live="departmentFilter">
                                @foreach ($departmentOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        <button type="button" class="co-filter-clear" wire:click="resetOperationalFilters" @click="open = false">Limpar filtros</button>
                    </div>
                </div>

                <button type="button" class="co-refresh-btn" wire:click="refreshDashboard" wire:loading.attr="disabled">
                    <i class="bi bi-arrow-clockwise" wire:loading.class="spin" wire:target="refreshDashboard,setDateRange,setDeadlinePeriod,statusFilter,departmentFilter,customStartDate,customEndDate"></i>
                    <span wire:loading.remove wire:target="refreshDashboard">Atualizar</span>
                    <span wire:loading wire:target="refreshDashboard">Atualizando...</span>
                </button>
            </div>
        </section>



        <nav class="co-page-cluster co-main-cluster" aria-label="Navegação da Central Operacional">
            <a class="co-cluster-item active" href="{{ \App\Filament\Pages\CentroOperacional::getUrl() }}">
                <span class="co-cluster-icon"><i class="bi bi-command"></i></span>
                <span>
                    <strong>Mesa de Execução</strong>
                    <small>Resolver agora, riscos, responsáveis e resultados</small>
                </span>
            </a>
            <button type="button" class="co-cluster-item" wire:click="criarTarefaOperacional" wire:loading.attr="disabled">
                <span class="co-cluster-icon"><i class="bi bi-plus-circle"></i></span>
                <span>
                    <strong>Nova Tarefa</strong>
                    <small>Criar demanda operacional no fluxo central</small>
                </span>
            </button>
            <button type="button" class="co-cluster-item" wire:click="abrirFilaOperacional" wire:loading.attr="disabled">
                <span class="co-cluster-icon"><i class="bi bi-list-check"></i></span>
                <span>
                    <strong>Fila Completa</strong>
                    <small>Abrir lista detalhada de tarefas internas</small>
                </span>
            </button>
            <a class="co-cluster-item" href="{{ \App\Filament\Pages\CentroOperacionalGestao::getUrl() }}?aba=workload">
                <span class="co-cluster-icon"><i class="bi bi-grid-1x2"></i></span>
                <span>
                    <strong>Gestão da Operação</strong>
                    <small>Workload, aprovações e financeiro como apoio interno</small>
                </span>
            </a>
        </nav>

        @if($loadError)
            <section class="co-state-card error" role="alert">
                <span class="co-state-icon"><i class="bi bi-exclamation-octagon"></i></span>
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

        <div class="co-loading-layer" wire:loading.flex wire:target="refreshDashboard,setDateRange,setDeadlinePeriod,statusFilter,departmentFilter,customStartDate,customEndDate,globalSearch,applyStatusShortcut,applyKpiShortcut,clearGlobalSearch">
            <div class="co-loading-card">
                <span class="co-loading-spinner"></span>
                <div>
                    <strong>Atualizando Centro Operacional</strong>
                    <small>Recalculando riscos, prazos e ações prioritárias...</small>
                </div>
            </div>
        </div>

        <section class="co-operational-risk-hero {{ $operationalRiskTone }}" aria-label="Central de Risco Operacional">
            <div class="co-operational-risk-main">
                <span class="co-operational-risk-eyebrow">Central de Risco Operacional</span>
                <h2>O que pode gerar atraso, multa ou retrabalho hoje</h2>
                <p>Prioridade visual para prazos críticos, clientes em risco e ações que precisam ser resolvidas antes de virar prejuízo.</p>
            </div>

            <div class="co-operational-risk-score">
                <span>{{ $operationalRiskTotal > 0 ? 'Atenção hoje' : 'Operação segura' }}</span>
                <strong>{{ number_format($operationalRiskTotal, 0, ',', '.') }}</strong>
                <small>pontos críticos mapeados</small>
            </div>

            <div class="co-operational-risk-metrics">
                <button type="button" class="co-operational-risk-metric {{ $riskCard['tone'] ?? 'success' }}" wire:click="applyKpiShortcut('risk')" wire:loading.attr="disabled">
                    <small>Clientes em risco</small>
                    <strong>{{ number_format((int) ($riskCard['value'] ?? 0), 0, ',', '.') }}</strong>
                    <span>multa, bloqueio ou correção parada</span>
                </button>
                <button type="button" class="co-operational-risk-metric {{ $todayCard['tone'] ?? 'success' }}" wire:click="applyKpiShortcut('all', 'today')" wire:loading.attr="disabled">
                    <small>Vencem hoje</small>
                    <strong>{{ number_format((int) ($todayCard['value'] ?? 0), 0, ',', '.') }}</strong>
                    <span>precisam de ação no dia</span>
                </button>
                <button type="button" class="co-operational-risk-metric {{ $lateCard['tone'] ?? 'success' }}" wire:click="applyKpiShortcut('late')" wire:loading.attr="disabled">
                    <small>Já vencidas</small>
                    <strong>{{ number_format((int) ($lateCard['value'] ?? 0), 0, ',', '.') }}</strong>
                    <span>prioridade máxima</span>
                </button>
                <a class="co-operational-risk-metric warning" href="{{ \App\Filament\Resources\ItemControles\ItemControleResource::getUrl('index') }}">
                    <small>Clientes críticos</small>
                    <strong>{{ number_format($criticalClientsCount, 0, ',', '.') }}</strong>
                    <span>acompanhar ranking</span>
                </a>
            </div>
        </section>

        @if($tendenciasOperacionais->isNotEmpty())
            <section class="co-trend-strip" aria-label="Tendências operacionais">
                <div class="co-trend-strip-head">
                    <span class="co-section-icon blue"><i class="bi bi-graph-up-arrow"></i></span>
                    <div>
                        <strong>Tendência da operação</strong>
                        <small>Comparação rápida para saber se a pressão está aumentando ou diminuindo.</small>
                    </div>
                </div>

                <div class="co-trend-cards">
                    @foreach($tendenciasOperacionais as $trend)
                        <article class="co-trend-card {{ $trend['tone'] ?? 'neutral' }}">
                            <span class="co-trend-label">{{ $trend['label'] ?? 'Indicador' }}</span>
                            <div class="co-trend-value-row">
                                <strong>{{ $trend['current_label'] ?? ($trend['current'] ?? 0) }}</strong>
                                <span class="co-trend-delta {{ $trend['direction'] ?? 'stable' }}">
                                    <i class="bi {{ match($trend['direction'] ?? 'stable') {
                                        'up' => 'bi-arrow-up-short',
                                        'down' => 'bi-arrow-down-short',
                                        default => 'bi-dash-lg',
                                    } }}"></i>
                                    {{ $trend['delta_label'] ?? 'estável' }}
                                </span>
                            </div>
                            <small>{{ $trend['hint'] ?? 'Comparação com o período anterior.' }}</small>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="co-kpi-grid" aria-label="Indicadores operacionais complementares">
            @foreach ($cards as $index => $card)
                @php
                    $tone = $card['tone'] ?? 'info';
                    $iconTone = $defaultIconClass[$index] ?? $tone;
                    $icon = $defaultIcons[$index] ?? 'bi-activity';
                @endphp
                @php
                    $cardShortcut = $card['shortcut'] ?? 'all';
                    $cardDateRange = ($card['key'] ?? null) === 'today' ? 'today' : null;
                    $wireClick = $cardDateRange
                        ? "applyKpiShortcut('{$cardShortcut}', '{$cardDateRange}')"
                        : "applyKpiShortcut('{$cardShortcut}')";
                    $icon = $card['icon'] ?? $icon;
                @endphp
                <button type="button" class="co-kpi-card co-kpi-button {{ $tone }}" wire:click="{{ $wireClick }}" wire:loading.attr="disabled" title="Aplicar filtro: {{ $card['label'] ?? 'Indicador' }}">
                    <div class="co-kpi-content">
                        <span class="co-kpi-label">{{ $card['label'] ?? '-' }}</span>
                        <strong>{{ is_numeric($card['value'] ?? null) ? number_format((int) $card['value'], 0, ',', '.') : ($card['value'] ?? '-') }}</strong>
                        <small>{{ $card['hint'] ?? '' }}</small>
                    </div>
                    <div class="co-kpi-icon {{ $iconTone }}"><i class="bi {{ $icon }}"></i></div>
                </button>
            @endforeach
        </section>

        <section class="co-focus-grid">
            <section class="co-panel co-resolve-panel co-mobile-collapsible" x-data="{ open: true }" :class="{ 'is-open': open }">
                <header class="co-panel-header co-resolve-header-v2">
                    <div class="co-heading-with-icon">
                        <span class="co-section-icon red"><i class="bi bi-lightning-charge-fill"></i></span>
                        <div>
                            <h2>Clientes em Maior Risco <small>Resolver agora</small></h2>
                            <p class="co-panel-subtitle">{{ $resolverMainAction }}</p>
                        </div>
                    </div>
                    <button type="button" class="co-mobile-toggle" @click="open = ! open" :aria-expanded="open.toString()">
                        <i class="bi" :class="open ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                        <span x-text="open ? 'Ocultar' : 'Mostrar'"></span>
                    </button>
                </header>

                <div class="co-resolve-command-strip {{ $resolverDanger > 0 ? 'danger' : ($resolverWarning > 0 ? 'warning' : 'success') }}">
                    <div>
                        <span>Clientes/riscos para resolver</span>
                        <strong>{{ number_format($resolverTotal, 0, ',', '.') }} {{ $resolverTotal === 1 ? 'risco para resolver' : 'riscos para resolver' }}</strong>
                    </div>
                    <div>
                        <span>Vencidas / multa</span>
                        <strong>{{ number_format($resolverDanger, 0, ',', '.') }}</strong>
                    </div>
                    <div>
                        <span>Atenção hoje</span>
                        <strong>{{ number_format($resolverWarning, 0, ',', '.') }}</strong>
                    </div>
                    <div>
                        <span>Sem responsável</span>
                        <strong>{{ number_format($resolverWithoutOwner, 0, ',', '.') }}</strong>
                    </div>
                </div>

                <div class="co-action-list co-action-list-v2 co-recommended-action-list">
                    @forelse ($resolverAgora as $item)
                        @php
                            $actions = $item['actions'] ?? [];
                            $primary = $item['primary_action'] ?? ['key' => 'open', 'label' => 'Abrir', 'icon' => 'bi-box-arrow-up-right'];
                            $canApprove = (bool) ($actions['approve'] ?? false);
                            $canCorrect = (bool) ($actions['correct'] ?? false);
                            $canDelegate = (bool) ($actions['delegate'] ?? false);
                            $recommendationReason = ($item['tone'] ?? null) === 'danger'
                                ? 'Resolver primeiro: risco vencido, multa ou retrabalho.'
                                : ((($item['tone'] ?? null) === 'warning')
                                    ? 'Próxima ação: prazo próximo ou aprovação parada.'
                                    : 'Acompanhar para manter a operação fluindo.');
                        @endphp
                        <article class="co-action-card-v2 co-recommended-action-card {{ $item['tone'] ?? 'info' }}">
                            <div class="co-recommended-rank" aria-label="Ordem de prioridade">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</div>
                            <a class="co-action-card-main" href="{{ $item['url'] }}">
                                <span class="co-action-icon {{ $item['tone'] ?? 'info' }}">
                                    <i class="bi {{ ($item['tone'] ?? '') === 'danger' ? 'bi-exclamation-octagon-fill' : (($item['tone'] ?? '') === 'success' ? 'bi-check-circle-fill' : (($item['tone'] ?? '') === 'warning' ? 'bi-lightning-charge-fill' : 'bi-file-earmark-text-fill')) }}"></i>
                                </span>
                                <div class="co-action-card-content">
                                    <div class="co-action-topline">
                                        <span class="co-action-type">{{ $item['type'] ?? 'Obrigação' }}</span>
                                        <span class="co-priority-badge {{ $item['priority_tone'] ?? 'warning' }}">{{ $item['priority'] ?? 'Alta' }}</span>
                                    </div>
                                    <strong>{{ $item['title'] }}</strong>
                                    <span>{{ $item['empresa'] }}</span>
                                    <p class="co-action-reason"><i class="bi bi-stars"></i>{{ $recommendationReason }}</p>
                                    <div class="co-action-meta">
                                        <small><i class="bi bi-calendar2-event"></i>{{ $item['due_human'] ?? ($item['due'] ?: 'Sem prazo') }}</small>
                                        <small><i class="bi bi-person"></i>{{ $item['responsavel'] ?? 'Sem responsável' }}</small>
                                        <small><i class="bi bi-activity"></i>{{ $item['status'] }}</small>
                                    </div>
                                </div>
                            </a>
                            <div class="co-action-buttons-v2">
                                <button type="button" class="co-action-btn dark" wire:click="openItemDetailModal({{ $item['id'] }}, 'resolver')" wire:loading.attr="disabled" wire:target="openItemDetailModal({{ $item['id'] }}, 'resolver')">
                                    <i class="bi bi-eye"></i>Detalhes
                                </button>

                                @if(($primary['key'] ?? 'open') === 'approve' && $canApprove)
                                    <button type="button" class="co-action-btn success" wire:click="aprovar({{ $item['id'] }})" wire:loading.attr="disabled" wire:target="aprovar({{ $item['id'] }})">
                                        <i class="bi bi-check2-circle"></i>Aprovar
                                    </button>
                                @elseif(($primary['key'] ?? 'open') === 'correct' && $canCorrect)
                                    <button type="button" class="co-action-btn warning" wire:click="enviarParaCorrecao({{ $item['id'] }})" wire:loading.attr="disabled" wire:target="enviarParaCorrecao({{ $item['id'] }})">
                                        <i class="bi bi-tools"></i>Corrigir
                                    </button>
                                @else
                                    <a class="co-action-btn info" href="{{ $item['url'] }}">
                                        <i class="bi bi-box-arrow-up-right"></i>Abrir
                                    </a>
                                @endif

                                @if($canCorrect && ($primary['key'] ?? 'open') !== 'correct')
                                    <button type="button" class="co-action-btn muted" wire:click="enviarParaCorrecao({{ $item['id'] }})" wire:loading.attr="disabled" wire:target="enviarParaCorrecao({{ $item['id'] }})">
                                        <i class="bi bi-arrow-counterclockwise"></i>Correção
                                    </button>
                                @endif

                                @if($canDelegate)
                                    <button type="button" class="co-action-btn purple" wire:click="openDelegateModal({{ $item['id'] }})" wire:loading.attr="disabled" wire:target="openDelegateModal({{ $item['id'] }})">
                                        <i class="bi bi-person-plus"></i>Delegar
                                    </button>
                                @endif
                            </div>
                        </article>
                    @empty
                        <div class="co-empty clean">
                            <strong>Nenhuma risco para resolver agora.</strong>
                            <p>Quando existir risco, vencimento ou aprovação parada, aparecerá aqui.</p>
                        </div>
                    @endforelse
                </div>

                @if(count($resolverAgora) > 0)
                    <a class="co-see-all centered" href="{{ \App\Filament\Resources\ItemControles\ItemControleResource::getUrl('index') }}">Ver todas as ações →</a>
                @endif
            </section>

            <aside class="co-panel co-deadline-panel co-mobile-collapsible" x-data="{ open: false }" :class="{ 'is-open': open }">
                <header class="co-panel-header compact">
                    <div class="co-heading-with-icon">
                        <span class="co-section-icon blue"><i class="bi bi-calendar3"></i></span>
                        <h2>Vencimentos</h2>
                    </div>
                    <button type="button" class="co-mobile-toggle" @click="open = ! open" :aria-expanded="open.toString()">
                        <i class="bi" :class="open ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                        <span x-text="open ? 'Ocultar' : 'Mostrar'"></span>
                    </button>
                </header>

                <div class="co-tabs">
                    @foreach (($vencimentos['periods'] ?? []) as $key => $period)
                        @if(in_array($key, ['today', 'seven_days', 'fifteen_days', 'thirty_days'], true))
                            <button type="button" wire:click="setDeadlinePeriod('{{ $key }}')" class="{{ ($vencimentos['selected'] ?? 'today') === $key ? 'active' : '' }}">
                                {{ $period['label'] }}
                            </button>
                        @endif
                    @endforeach
                </div>

                <div class="co-deadline-list">
                    @forelse ($deadlineRows as $row)
                        @php
                            $label = $row['label'] ?? 'Operacional';
                            $dot = $departmentColors[$label] ?? 'blue';
                        @endphp
                        <div class="co-deadline-row">
                            <span class="co-dot {{ $dot }}"></span>
                            <strong>{{ $label }}</strong>
                            <b>{{ number_format((int) ($row['value'] ?? 0), 0, ',', '.') }}</b>
                        </div>
                    @empty
                        <div class="co-empty clean"><strong>Sem vencimentos neste período.</strong></div>
                    @endforelse
                </div>

                <div class="co-deadline-total">
                    <span>Total</span>
                    <strong>{{ number_format($deadlineTotal, 0, ',', '.') }}</strong>
                </div>
            </aside>
        </section>


        <section class="co-panel co-people-risk-panel co-mobile-collapsible" x-data="{ open: false }" :class="{ 'is-open': open }">
            <header class="co-panel-header">
                <div class="co-heading-with-icon">
                    <span class="co-section-icon purple"><i class="bi bi-people-fill"></i></span>
                    <div>
                        <h2>Responsáveis em Atenção</h2>
                        <p class="co-panel-subtitle">Veja quem está sobrecarregado e quem ainda tem margem para receber demanda.</p>
                    </div>
                </div>
                <div class="co-header-actions-inline">
                    <a class="co-see-all" href="{{ \App\Filament\Pages\CentroOperacionalGestao::getUrl() }}?aba=workload">Ver workload</a>
                    <button type="button" class="co-mobile-toggle" @click="open = ! open" :aria-expanded="open.toString()">
                        <i class="bi" :class="open ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                        <span x-text="open ? 'Ocultar' : 'Mostrar'"></span>
                    </button>
                </div>
            </header>

            <div class="co-people-risk-grid">
                <div class="co-people-risk-column danger">
                    <div class="co-people-risk-column-head">
                        <span><i class="bi bi-exclamation-triangle-fill"></i>Necessita atenção</span>
                        <strong>{{ number_format($responsaveisAtencaoTotal, 0, ',', '.') }}</strong>
                    </div>

                    @forelse ($responsaveisAtencao as $row)
                        <article class="co-person-risk-row {{ $row['tone'] ?? 'warning' }}">
                            <span class="co-person-avatar"><i class="bi bi-person"></i></span>
                            <div class="co-person-info">
                                <strong>{{ $row['name'] ?? 'Responsável' }}</strong>
                                <small>{{ $row['status'] ?? 'Atenção' }} • {{ number_format((int) ($row['total'] ?? 0), 0, ',', '.') }} itens abertos</small>
                                <div class="co-progress"><span style="width: {{ min(100, (int) ($row['percent'] ?? 0)) }}%"></span></div>
                            </div>
                            <b>{{ number_format((int) ($row['percent'] ?? 0), 0, ',', '.') }}%</b>
                            @if(!empty($row['responsavel_id']))
                                <button type="button" class="co-mini-action purple" wire:click="openWorkloadModal({{ (int) $row['responsavel_id'] }})" wire:loading.attr="disabled">
                                    <i class="bi bi-arrow-left-right"></i>Redistribuir
                                </button>
                            @else
                                <a class="co-mini-action" href="{{ $row['open_url'] ?? \App\Filament\Resources\ItemControles\ItemControleResource::getUrl('index') }}"><i class="bi bi-box-arrow-up-right"></i>Abrir</a>
                            @endif
                        </article>
                    @empty
                        <div class="co-empty clean small"><strong>Ninguém sobrecarregado no momento.</strong></div>
                    @endforelse
                </div>

                <div class="co-people-risk-column success">
                    <div class="co-people-risk-column-head">
                        <span><i class="bi bi-check2-circle"></i>Com margem</span>
                        <strong>{{ number_format($topResponsaveis->count(), 0, ',', '.') }}</strong>
                    </div>

                    @forelse ($topResponsaveis as $row)
                        <article class="co-person-risk-row success compact">
                            <span class="co-person-avatar"><i class="bi bi-person-check"></i></span>
                            <div class="co-person-info">
                                <strong>{{ $row['name'] ?? 'Responsável' }}</strong>
                                <small>{{ number_format((int) ($row['total'] ?? 0), 0, ',', '.') }} itens • capacidade saudável</small>
                            </div>
                            <b>{{ number_format((int) ($row['percent'] ?? 0), 0, ',', '.') }}%</b>
                        </article>
                    @empty
                        <div class="co-empty clean small"><strong>Sem responsáveis com folga no filtro atual.</strong></div>
                    @endforelse
                </div>

                <div class="co-people-risk-summary">
                    <small>Total distribuído no filtro atual</small>
                    <strong>{{ number_format($workloadTotalAberto, 0, ',', '.') }}</strong>
                    <span>itens abertos com responsável</span>
                </div>
            </div>
        </section>

        <section class="co-bottom-model-grid">
            <section class="co-panel co-department-panel co-mobile-collapsible" x-data="{ open: false }" :class="{ 'is-open': open }">
                <header class="co-panel-header">
                    <div class="co-heading-with-icon">
                        <span class="co-section-icon muted"><i class="bi bi-diagram-3"></i></span>
                        <h2>Pendências por Departamento</h2>
                    </div>
                    <button type="button" class="co-mobile-toggle" @click="open = ! open" :aria-expanded="open.toString()">
                        <i class="bi" :class="open ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                        <span x-text="open ? 'Ocultar' : 'Mostrar'"></span>
                    </button>
                </header>

                <div class="co-department-content">
                    <div class="co-chart-wrap" role="img" aria-label="Gráfico real de pendências por departamento">
                        @if($departmentTotal > 0 && $departmentRows->isNotEmpty())
                            <svg class="co-donut-chart" viewBox="0 0 160 160" aria-hidden="true">
                                <circle class="co-donut-track" cx="80" cy="80" r="58" pathLength="100"></circle>
                                @php $offset = 25; @endphp
                                @foreach ($departmentRows as $row)
                                    @php
                                        $label = $row['label'] ?? 'Operacional';
                                        $value = (int) ($row['value'] ?? 0);
                                        $percentFloat = $departmentTotal > 0 ? (($value / max(1, $departmentTotal)) * 100) : 0;
                                        $dot = $departmentColors[$label] ?? 'blue';
                                        $stroke = $departmentHex[$dot] ?? '#2474ff';
                                    @endphp
                                    @if($percentFloat > 0)
                                        <circle
                                            class="co-donut-segment"
                                            cx="80"
                                            cy="80"
                                            r="58"
                                            pathLength="100"
                                            stroke="{{ $stroke }}"
                                            stroke-dasharray="{{ number_format($percentFloat, 4, '.', '') }} {{ number_format(100 - $percentFloat, 4, '.', '') }}"
                                            stroke-dashoffset="{{ number_format($offset, 4, '.', '') }}"
                                            data-label="{{ $label }}"
                                            data-value="{{ $value }}"
                                            data-percent="{{ round($percentFloat) }}"
                                        >
                                            <title>{{ $label }}: {{ $value }} pendências ({{ round($percentFloat) }}%)</title>
                                        </circle>
                                        @php $offset -= $percentFloat; @endphp
                                    @endif
                                @endforeach
                            </svg>
                            <div class="co-chart-center">
                                <strong>{{ number_format($departmentTotal, 0, ',', '.') }}</strong>
                                <span>total</span>
                            </div>
                        @else
                            <div class="co-donut-empty"><i class="bi bi-check2-circle"></i></div>
                        @endif
                    </div>
                    <div class="co-department-legend">
                        @forelse ($departmentRows as $row)
                            @php
                                $label = $row['label'] ?? 'Operacional';
                                $value = (int) ($row['value'] ?? 0);
                                $percent = $departmentTotal > 0 ? round(($value / max(1, $departmentTotal)) * 100) : 0;
                                $dot = $departmentColors[$label] ?? 'blue';
                            @endphp
                            <div>
                                <span><i class="co-dot {{ $dot }}"></i>{{ $label }}</span>
                                <strong>{{ $value }} ({{ $percent }}%)</strong>
                            </div>
                        @empty
                            <div class="co-empty clean"><strong>Sem pendências abertas.</strong></div>
                        @endforelse
                    </div>
                </div>

                <div class="co-panel-footer-total">
                    <span>Total</span>
                    <strong>{{ number_format($departmentTotal, 0, ',', '.') }} pendências</strong>
                </div>
            </section>

            <section class="co-panel co-results-panel {{ $resultTone }} co-mobile-collapsible" x-data="{ open: false }" :class="{ 'is-open': open }">
                <header class="co-panel-header">
                    <div class="co-heading-with-icon">
                        <span class="co-section-icon green"><i class="bi bi-trophy-fill"></i></span>
                        <h2>Resultados deste mês</h2>
                    </div>
                    <div class="co-header-actions-inline">
                        <span class="co-party"><i class="bi bi-stars"></i></span>
                        <button type="button" class="co-mobile-toggle" @click="open = ! open" :aria-expanded="open.toString()">
                            <i class="bi" :class="open ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                            <span x-text="open ? 'Ocultar' : 'Mostrar'"></span>
                        </button>
                    </div>
                </header>

                <div class="co-result-grid-model">
                    @foreach ($resultadosMes as $result)
                        <div class="co-result-model-card {{ ($result['label'] ?? '') === 'Multas registradas' && (int) ($result['value'] ?? 0) > 0 ? 'danger' : 'success' }}">
                            <strong>{{ $result['value'] }}</strong>
                            <span>{{ $result['label'] }}</span>
                            <i class="bi {{ ($result['label'] ?? '') === 'Multas registradas' && (int) ($result['value'] ?? 0) > 0 ? 'bi-exclamation-lg' : 'bi-check-lg' }}"></i>
                        </div>
                    @endforeach
                </div>

                <p class="co-success-message {{ $resultTone }}">{{ $resultMessage }}</p>
            </section>
        </section>


        <section class="co-panel co-alerts-panel co-alerts-collapsible" x-data="{ open: false }">
            <button type="button" class="co-alerts-toggle" @click="open = ! open" :aria-expanded="open.toString()">
                <span class="co-alerts-toggle-icon" :class="{ 'is-open': open }">
                    <i class="bi" :class="open ? 'bi-dash-lg' : 'bi-plus-lg'"></i>
                </span>
                <span class="co-alerts-toggle-text">
                    <strong>Alertas Operacionais</strong>
                    <small>Críticos separados dos informativos para evitar ruído e destacar o que pode gerar prejuízo.</small>
                </span>
                <span class="co-alerts-toggle-count {{ $alertasCriticosTotal > 0 ? 'danger' : 'success' }}">
                    {{ number_format($alertasCriticosTotal, 0, ',', '.') }} críticos
                    <small>{{ number_format($alertasInformativosTotal, 0, ',', '.') }} informativos</small>
                </span>
            </button>

            <div class="co-alerts-collapse" x-show="open" x-cloak>
                <header class="co-panel-header compact">
                    <div class="co-heading-with-icon">
                        <span class="co-section-icon red"><i class="bi bi-broadcast-pin"></i></span>
                        <h2>Alertas com prioridade de ação</h2>
                    </div>
                    <span class="co-panel-subtitle">{{ number_format($alertasTotal, 0, ',', '.') }} alerta(s) encontrados</span>
                </header>

                <div class="co-alerts-priority-layout">
                    <section class="co-alerts-priority-block critical">
                        <header>
                            <span><i class="bi bi-exclamation-octagon-fill"></i> Críticos e importantes</span>
                            <b>{{ number_format($alertasCriticosTotal, 0, ',', '.') }}</b>
                        </header>
                        <p>Use esta coluna para resolver primeiro prazos, bloqueios, correções e itens que podem virar multa ou retrabalho.</p>

                        <div class="co-alerts-grid priority">
                            @forelse ($alertasCriticos as $group)
                                @php $items = collect($group['items'] ?? [])->take(4)->values(); @endphp
                                <article class="co-alert-column {{ $group['tone'] ?? 'warning' }}">
                                    <header>
                                        <span><i class="bi {{ $group['icon'] ?? 'bi-info-circle' }}"></i>{{ $group['label'] ?? 'Alerta crítico' }}</span>
                                        <b>{{ $items->count() }}</b>
                                    </header>
                                    <p>{{ $group['description'] ?? '' }}</p>

                                    <div class="co-alert-list">
                                        @forelse ($items as $alert)
                                            <a href="{{ $alert['url'] }}" class="co-alert-row {{ $alert['tone'] ?? ($group['tone'] ?? 'warning') }}">
                                                <i class="bi {{ $alert['icon'] ?? ($group['icon'] ?? 'bi-info-circle') }}"></i>
                                                <span>
                                                    <strong>{{ $alert['summary'] ?? $alert['title'] ?? 'Item operacional' }}</strong>
                                                    <small>{{ $alert['reason'] ?? '' }} • {{ $alert['due_human'] ?? 'Sem prazo' }}</small>
                                                </span>
                                            </a>
                                        @empty
                                            <div class="co-alert-empty">
                                                <i class="bi bi-check2-circle"></i>
                                                <span>Nenhum item nesta camada.</span>
                                            </div>
                                        @endforelse
                                    </div>
                                </article>
                            @empty
                                <div class="co-alert-empty co-alert-empty-wide">
                                    <i class="bi bi-shield-check"></i>
                                    <span>Nenhum alerta crítico agora. A operação não tem bloqueio urgente neste momento.</span>
                                </div>
                            @endforelse
                        </div>
                    </section>

                    <section class="co-alerts-priority-block informative">
                        <header>
                            <span><i class="bi bi-info-circle-fill"></i> Informativos</span>
                            <b>{{ number_format($alertasInformativosTotal, 0, ',', '.') }}</b>
                        </header>
                        <p>Informações úteis para acompanhamento, sem competir visualmente com os riscos de hoje.</p>

                        <div class="co-alerts-grid informative">
                            @forelse ($alertasInformativos as $group)
                                @php $items = collect($group['items'] ?? [])->take(3)->values(); @endphp
                                <article class="co-alert-column {{ $group['tone'] ?? 'info' }}">
                                    <header>
                                        <span><i class="bi {{ $group['icon'] ?? 'bi-info-circle' }}"></i>{{ $group['label'] ?? 'Informativo' }}</span>
                                        <b>{{ $items->count() }}</b>
                                    </header>
                                    <p>{{ $group['description'] ?? '' }}</p>

                                    <div class="co-alert-list">
                                        @forelse ($items as $alert)
                                            <a href="{{ $alert['url'] }}" class="co-alert-row {{ $alert['tone'] ?? ($group['tone'] ?? 'info') }}">
                                                <i class="bi {{ $alert['icon'] ?? ($group['icon'] ?? 'bi-info-circle') }}"></i>
                                                <span>
                                                    <strong>{{ $alert['summary'] ?? $alert['title'] ?? 'Item operacional' }}</strong>
                                                    <small>{{ $alert['reason'] ?? '' }} • {{ $alert['due_human'] ?? 'Sem prazo' }}</small>
                                                </span>
                                            </a>
                                        @empty
                                            <div class="co-alert-empty">
                                                <i class="bi bi-check2-circle"></i>
                                                <span>Nenhum informativo nesta camada.</span>
                                            </div>
                                        @endforelse
                                    </div>
                                </article>
                            @empty
                                <div class="co-alert-empty co-alert-empty-wide">
                                    <i class="bi bi-check2-circle"></i>
                                    <span>Nenhum alerta informativo no momento.</span>
                                </div>
                            @endforelse
                        </div>
                    </section>
                </div>
            </div>
        </section>



        @if($detailModalOpen)
            @php
                $detail = $this->selectedItemDetail();
                $scoreValue = (int) ($detail['urgency_score']['value'] ?? 92);
                $scoreValue = max(0, min(100, $scoreValue));
                $scoreTone = $scoreValue >= 85 ? 'critical' : ($scoreValue >= 65 ? 'warning' : 'info');
                $scoreReasons = collect($detail['urgency_score']['reasons'] ?? [])->take(4)->values();
                $whyHere = collect($detail['why_here'] ?? [])->take(3)->values();
                $impactRows = collect($detail['operational_impact'] ?? [])->take(3)->values();
                $checklistRows = collect($detail['checklist'] ?? [])->take(3)->values();
                $blockerRows = collect($detail['blockers'] ?? [])->take(3)->values();
                $doneRows = collect($detail['done_definition'] ?? [])->take(2)->values();
                $timelineRows = collect($detail['timeline'] ?? [])->take(2)->values();
                $criticalClient = $detail['critical_client'] ?? [];
                $readyMessage = $detail['ready_message'] ?? 'Mensagem não gerada para este item.';
                $primaryAction = $detail['decision_summary']['action'] ?? ($detail['suggestion']['primary_action'] ?? 'Entrar em contato com o cliente agora');
                $actionImpact = $detail['decision_summary']['impact'] ?? ($detail['suggestion']['text'] ?? 'Evita multa, mantém o cliente em dia e reduz retrabalho operacional.');
            @endphp
            <div class="ra-modal" role="dialog" aria-modal="true" aria-labelledby="ra-detail-title">
                <div class="ra-backdrop" wire:click="closeItemDetailModal"></div>

                <article class="ra-shell" @click.stop>
                    <button type="button" class="ra-close" wire:click="closeItemDetailModal" aria-label="Fechar">
                        <i class="bi bi-x-lg"></i>
                    </button>

                    @if($detail)
                        @if(false && $detailModalSource === 'cliente')
                            @php
                                $clientName = $detail['empresa'] ?? 'Cliente não informado';
                                $clientScore = (int) ($detail['critical_client']['risk_score'] ?? $detail['urgency_score']['value'] ?? 0);
                                $clientScore = max(0, min(100, $clientScore));
                                $clientTone = $clientScore >= 80 ? 'RISCO ALTO' : ($clientScore >= 55 ? 'ATENÇÃO' : 'ACOMPANHAR');
                                $timelineRows = collect($detail['timeline'] ?? [])->take(3)->values();
                                $currentItem = [
                                    'titulo' => $detail['title'] ?? 'Item operacional',
                                    'status' => $detail['status'] ?? 'Pendente',
                                    'responsavel' => $detail['responsavel'] ?? 'Sem responsável',
                                    'vencimento' => $detail['vencimento'] ?? 'Sem prazo',
                                    'url' => $detail['url'] ?? '#',
                                    'atual' => true,
                                ];
                                $pendingRows = collect([$currentItem])
                                    ->merge(collect($detail['related_client_items'] ?? [])->map(function ($row) {
                                        $row['atual'] = false;
                                        return $row;
                                    }))
                                    ->take(6)
                                    ->values();
                                $clientOpen = $pendingRows->count();
                                $clientLate = (int) ($detail['critical_client']['late_items'] ?? $detail['critical_client']['pendencias_vencidas'] ?? 0);
                                $reasonRows = collect($detail['why_here'] ?? [])->merge($detail['blockers'] ?? [])->unique()->take(4)->values();
                                $impactRows = collect($detail['operational_impact'] ?? [])->take(4)->values();
                                $doneRows = collect($detail['done_definition'] ?? [])->take(3)->values();
                                $checklistRows = collect($detail['checklist'] ?? [])->take(5)->values();
                                $readyMessage = $detail['ready_message'] ?? 'Mensagem não gerada para este cliente.';
                                $riskSummaryRows = collect($detail['client_risk_summary'] ?? [])->take(5)->values();
                                $relationship = $detail['client_relationship'] ?? [];
                            @endphp
                            <header class="cmr-header cmr-client-resolution">
                                <div>
                                    <div class="cmr-kicker"><i class="bi bi-exclamation-triangle-fill"></i> Central de ação do cliente</div>
                                    <div class="cmr-title-row"><h2>{{ $clientName }}</h2><span>{{ $clientTone }}</span></div>
                                    <div class="cmr-meta">
                                        <span>{{ $clientOpen }} pendência(s) operacional(is) nesta análise</span>
                                        <b>•</b>
                                        <strong>{{ $detail['categoria'] ?? 'Operacional' }}</strong>
                                    </div>
                                </div>
                                <div class="cmr-actions">
                                    <a href="{{ $detail['url'] ?? '#' }}" target="_blank" rel="noopener">Abrir obrigação <i class="bi bi-box-arrow-up-right"></i></a>
                                </div>
                            </header>

                            <div class="cmr-body cmr-client-action-body">
                                <section class="cmr-alert-strip cmr-client-risk-top">
                                    <div class="cmr-alert-left">
                                        <span><i class="bi bi-exclamation-lg"></i></span>
                                        <div>
                                            <strong>Por que este cliente está em risco</strong>
                                            <ul class="cmr-risk-bullets">
                                                @forelse($riskSummaryRows as $riskSummary)
                                                    <li>{{ $riskSummary }}</li>
                                                @empty
                                                    <li>{{ $detail['decision_summary']['impact'] ?? 'Existe risco operacional que precisa de acompanhamento.' }}</li>
                                                @endforelse
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="cmr-risk-score"><span>Risco atual</span><div class="cmr-scorebar"><i style="width: {{ $clientScore }}%"></i></div><strong>{{ $clientScore }} <em>/ 100</em></strong></div>
                                </section>

                                <section class="cmr-card cmr-relationship-card">
                                    <div>
                                        <span>Último contato</span>
                                        <strong>{{ $relationship['last_contact'] ?? 'Sem contato registrado' }}</strong>
                                    </div>
                                    <div>
                                        <span>Situação recente</span>
                                        <strong>{{ $relationship['silence'] ?? 'Sem informação suficiente' }}</strong>
                                    </div>
                                    <div>
                                        <span>Canal registrado</span>
                                        <strong>{{ $relationship['channel'] ?? 'Ainda não registrado' }}</strong>
                                    </div>
                                </section>

                                <section class="cmr-card cmr-actions-card cmr-client-primary-actions">
                                    <h3>Ação recomendada</h3>
                                    <p>{{ $detail['decision_summary']['action'] ?? $detail['suggestion']['primary_action'] ?? 'Entrar em contato com o cliente e encaminhar a pendência.' }}</p>
                                    <div class="cmr-action-buttons">
                                        @if(!empty($detail['portal_cliente_url']))
                                            <a href="{{ $detail['portal_cliente_url'] }}" target="_blank" rel="noopener" class="cmr-primary cmr-secondary" wire:click="registrarContatoPortalCliente({{ $detail['id'] }})">
                                                <i class="bi bi-chat-dots"></i> Conversar pelo Portal do Cliente
                                            </a>
                                        @endif

                                        @if(!empty($detail['whatsapp_url']))
                                            <a href="{{ $detail['whatsapp_url'] }}" target="_blank" rel="noopener" class="cmr-primary" wire:click="registrarContatoCliente({{ $detail['id'] }})">
                                                <i class="bi bi-whatsapp"></i> Solicitar pelo WhatsApp
                                            </a>
                                        @else
                                            <button type="button" class="cmr-primary" wire:click="registrarContatoCliente({{ $detail['id'] }})">
                                                <i class="bi bi-clipboard-check"></i> Registrar contato / copiar mensagem
                                            </button>
                                        @endif

                                        @if(($detail['actions']['execute'] ?? false) && !($detail['is_closed'] ?? false))
                                            <button type="button" class="cmr-plan" wire:click="marcarItemComoResolvido({{ $detail['id'] }})"><i class="bi bi-check2-circle"></i> Concluir tarefa</button>
                                        @endif

                                        @if($detail['actions']['delegate'] ?? false)
                                            <button type="button" class="cmr-plan" wire:click="openDelegateModal({{ $detail['id'] }})"><i class="bi bi-person-plus"></i> Delegar</button>
                                        @endif

                                        @if($detail['actions']['correct'] ?? false)
                                            <button type="button" class="cmr-plan" wire:click="enviarParaCorrecao({{ $detail['id'] }})"><i class="bi bi-arrow-counterclockwise"></i> Enviar para correção</button>
                                        @endif
                                    </div>
                                </section>

                                <div class="cmr-grid cmr-client-grid">
                                    <section class="cmr-card cmr-pending">
                                        <div class="cmr-card-head"><h3>Pendências reais do cliente ({{ $clientOpen }})</h3><a href="{{ $detail['url'] ?? '#' }}" target="_blank" rel="noopener">Abrir principal</a></div>
                                        <div class="cmr-pending-list">
                                            @forelse($pendingRows as $pending)
                                                <article>
                                                    <span class="{{ !empty($pending['atual']) ? 'red' : 'orange' }}"><i class="bi bi-exclamation-triangle"></i></span>
                                                    <div>
                                                        <strong>{{ $pending['titulo'] ?? $pending['title'] ?? 'Item operacional' }}</strong>
                                                        <p>{{ $pending['status'] ?? 'Status não informado' }} • {{ $pending['responsavel'] ?? 'Sem responsável' }} • {{ $pending['vencimento'] ?? 'Sem prazo' }}</p>
                                                    </div>
                                                    <a href="{{ $pending['url'] ?? ($detail['url'] ?? '#') }}" target="_blank" rel="noopener">Abrir</a>
                                                </article>
                                            @empty
                                                <div class="cmr-empty-state">Nenhuma pendência relacionada encontrada para este cliente.</div>
                                            @endforelse
                                        </div>
                                    </section>

                                    <section class="cmr-card cmr-risks">
                                        <h3>Motivos do risco</h3>
                                        <div class="cmr-risk-list">
                                            @forelse($reasonRows as $reason)
                                                <article><span class="red"><i class="bi bi-exclamation-triangle"></i></span><div><strong>{{ $reason }}</strong></div></article>
                                            @empty
                                                <article><span class="blue"><i class="bi bi-info-circle"></i></span><div><strong>Sem motivo adicional registrado.</strong><p>Use as pendências reais para decidir a próxima ação.</p></div></article>
                                            @endforelse
                                        </div>
                                    </section>

                                    <section class="cmr-card cmr-summary">
                                        <h3>Se não agir</h3>
                                        <div class="cmr-info-list">
                                            @forelse($impactRows as $row)
                                                <div><span>{{ $row['label'] ?? 'Impacto' }}</span><strong>{{ $row['value'] ?? '-' }}</strong></div>
                                            @empty
                                                <div><span>Impacto</span><strong>Não informado; tratar pelo risco operacional.</strong></div>
                                            @endforelse
                                        </div>
                                    </section>

                                    <section class="cmr-card cmr-comms">
                                        <div class="cmr-card-head"><h3>Mensagem pronta</h3><button type="button" wire:click="toggleDetailPersonalize">Editar</button></div>
                                        @if($detailPersonalizeOpen)
                                            <textarea class="ra-message-editor" wire:model.defer="detailDraftMessage" rows="5"></textarea>
                                        @else
                                            <div class="cmr-message-box">{{ $readyMessage }}</div>
                                        @endif
                                    </section>

                                    <section class="cmr-card cmr-actions-card cmr-status-actions-card">
                                        <h3>Atualizar situação</h3>
                                        <button type="button" class="cmr-plan success" wire:click="registrarSituacaoCliente({{ $detail['id'] }}, 'respondeu')"><i class="bi bi-reply-fill"></i> Cliente respondeu</button>
                                        <button type="button" class="cmr-plan success" wire:click="registrarSituacaoCliente({{ $detail['id'] }}, 'documentos_recebidos')"><i class="bi bi-file-earmark-check"></i> Documentos recebidos</button>
                                        <button type="button" class="cmr-plan" wire:click="registrarSituacaoCliente({{ $detail['id'] }}, 'aguardando_cliente')"><i class="bi bi-hourglass-split"></i> Aguardando cliente</button>
                                        <button type="button" class="cmr-plan danger" wire:click="registrarSituacaoCliente({{ $detail['id'] }}, 'nao_respondeu')"><i class="bi bi-x-circle"></i> Cliente não respondeu</button>
                                    </section>

                                    <section class="cmr-card cmr-actions-card">
                                        <h3>Registrar impedimento</h3>
                                        <button type="button" class="cmr-plan" wire:click="registrarImpedimentoResolverAgora({{ $detail['id'] }}, 'cliente')">Retorno/documento do cliente pendente</button>
                                        <button type="button" class="cmr-plan" wire:click="registrarImpedimentoResolverAgora({{ $detail['id'] }}, 'documento')">Documento obrigatório pendente</button>
                                        <button type="button" class="cmr-plan" wire:click="registrarImpedimentoResolverAgora({{ $detail['id'] }}, 'governo')">Sistema externo indisponível</button>
                                    </section>

                                    <section class="cmr-card cmr-actions-card">
                                        <h3>Adiar com registro</h3>
                                        <button type="button" class="cmr-plan" wire:click="adiarItemResolverAgora({{ $detail['id'] }}, 1)">+1 dia</button>
                                        <button type="button" class="cmr-plan" wire:click="adiarItemResolverAgora({{ $detail['id'] }}, 3)">+3 dias</button>
                                        <button type="button" class="cmr-plan" wire:click="adiarItemResolverAgora({{ $detail['id'] }}, 7)">+7 dias</button>
                                    </section>

                                    <section class="cmr-card cmr-pending">
                                        <div class="cmr-card-head"><h3>Passos para encerrar</h3></div>
                                        <div class="cmr-pending-list">
                                            @forelse($checklistRows as $step)
                                                <article><span class="{{ !empty($step['concluido']) ? 'green' : 'orange' }}"><i class="bi {{ !empty($step['concluido']) ? 'bi-check2' : 'bi-circle' }}"></i></span><div><strong>{{ $step['titulo'] ?? 'Etapa operacional' }}</strong><p>{{ !empty($step['concluido']) ? 'Concluído' : 'Pendente' }}</p></div></article>
                                            @empty
                                                @forelse($doneRows as $done)
                                                    <article><span class="green"><i class="bi bi-check2"></i></span><div><strong>{{ $done }}</strong></div></article>
                                                @empty
                                                    <div class="cmr-empty-state">Nenhum checklist cadastrado para este item.</div>
                                                @endforelse
                                            @endforelse
                                        </div>
                                    </section>

                                    <section class="cmr-card cmr-comms">
                                        <div class="cmr-card-head"><h3>Últimos eventos reais</h3><a href="{{ $detail['url'] ?? '#' }}" target="_blank" rel="noopener">Ver completo</a></div>
                                        <div class="cmr-comms-list">
                                            @forelse($timelineRows as $event)
                                                <article><span class="blue"><i class="bi bi-clock-history"></i></span><div><strong>{{ $event['titulo'] ?? 'Atualização operacional' }}</strong><p>{{ $event['descricao'] ?? 'Sem detalhe adicional.' }}</p></div><time>{{ $event['data'] ?? '-' }}</time></article>
                                            @empty
                                                <div class="cmr-empty-state">Nenhum evento registrado ainda.</div>
                                            @endforelse
                                        </div>
                                    </section>
                                </div>
                            </div>
                            <footer class="cmr-footer"><div><i class="bi bi-lightbulb"></i><strong>Foco:</strong> tratar pendência real, registrar o contato e tirar o cliente da fila de risco.</div><button type="button" wire:click="closeItemDetailModal">Fechar</button></footer>
                        @else
                        <header class="ra-header">
                            <div class="ra-heading">
                                <span class="ra-kicker">CLIENTES EM MAIOR RISCO</span>
                                <div class="ra-title-row">
                                    <h2 id="ra-detail-title">{{ $detail['title'] }}</h2>
                                    <span class="ra-critical-pill">{{ strtoupper($detail['decision_summary']['tone'] === 'danger' ? 'RISCO ALTO' : ($detail['decision_summary']['tone'] === 'warning' ? 'ATENÇÃO' : $detail['prioridade'])) }}</span>
                                </div>
                                <div class="ra-meta-row">
                                    <span><i class="bi bi-building"></i>Cliente: {{ $detail['empresa'] }}</span>
                                    <span>•</span>
                                    <span><i class="bi bi-calendar-event"></i>{{ $detail['dias_prazo'] }}</span>
                                    <span>•</span>
                                    <span>Status: {{ $detail['status'] }}</span>
                                    <span class="ra-client-chip">{{ $detail['categoria'] }}</span>
                                </div>
                            </div>

                            <div class="ra-header-actions">
                                <a href="{{ $detail['url'] }}" class="ra-icon-only" title="Abrir obrigação completa">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                            </div>
                        </header>

                        <div class="ra-body">
                            <main class="ra-main">
                                <section class="ra-summary-card">
                                    <div class="ra-alert-icon"><i class="bi bi-exclamation-lg"></i></div>
                                    <div>
                                        <h3>RISCO OPERACIONAL</h3>
                                        <p>{{ $detail['decision_summary']['problem'] ?? $detail['executive_summary'] }}</p>
                                        <p class="ra-summary-strong">{{ $detail['decision_summary']['impact'] ?? $actionImpact }}</p>
                                    </div>
                                </section>

                                <section class="ra-card ra-next-card">
                                    <h3>AÇÃO RÁPIDA RECOMENDADA</h3>
                                    <div class="ra-next-alert"><i class="bi bi-lightning-charge"></i><strong>{{ $primaryAction }}</strong></div>
                                    <div class="ra-next-meta"><span>Prazo:</span><strong>{{ $detail['dias_prazo'] }}</strong></div>
                                    <div class="ra-next-meta"><span>Responsável:</span><strong>{{ $detail['responsavel'] }}</strong></div>

                                    <div class="ra-action-dock" aria-label="Ações rápidas da central de resolução">
                                        @if($detail['whatsapp_url'] ?? null)
                                            <a href="{{ $detail['whatsapp_url'] }}" target="_blank" rel="noopener" class="ra-action-btn ra-action-btn-primary" wire:click="registrarContatoCliente({{ $detail['id'] }})">
                                                <i class="bi bi-whatsapp"></i><span>Solicitar pelo WhatsApp</span>
                                            </a>
                                        @else
                                            <button type="button" class="ra-action-btn ra-action-btn-primary" wire:click="registrarContatoCliente({{ $detail['id'] }})">
                                                <i class="bi bi-clipboard-check"></i><span>Registrar contato</span>
                                            </button>
                                        @endif

                                        <a href="{{ $detail['portal_cliente_url'] }}" target="_blank" rel="noopener" class="ra-action-btn ra-action-btn-secondary" wire:click="registrarContatoPortalCliente({{ $detail['id'] }})">
                                            <i class="bi bi-chat-dots"></i><span>Conversar no Portal</span>
                                        </a>

                                        @if(($detail['actions']['execute'] ?? false) && ! $detail['is_closed'])
                                            <button type="button" class="ra-action-btn ra-action-btn-success" wire:click="marcarItemComoResolvido({{ $detail['id'] }})" wire:loading.attr="disabled" wire:target="marcarItemComoResolvido({{ $detail['id'] }})"><i class="bi bi-check2-circle"></i><span>Concluir tarefa</span></button>
                                        @else
                                            <button type="button" class="ra-action-btn ra-action-btn-muted" disabled><i class="bi bi-lock"></i><span>{{ $detail['is_closed'] ? 'Item encerrado' : 'Sem permissão para concluir' }}</span></button>
                                        @endif

                                        @if(($detail['actions']['delegate'] ?? false) && ! $detail['is_closed'])
                                            <button type="button" class="ra-action-btn ra-action-btn-secondary" wire:click="openDelegateModal({{ $detail['id'] }})"><i class="bi bi-person-plus"></i><span>Delegar</span></button>
                                        @endif

                                        @if(($detail['actions']['correct'] ?? false) && ! $detail['is_closed'])
                                            <button type="button" class="ra-action-btn ra-action-btn-secondary" wire:click="enviarParaCorrecao({{ $detail['id'] }})"><i class="bi bi-arrow-counterclockwise"></i><span>Correção</span></button>
                                        @endif

                                        <a href="{{ $detail['url'] }}" class="ra-action-btn ra-action-btn-ghost"><i class="bi bi-box-arrow-up-right"></i><span>Abrir obrigação</span></a>
                                    </div>
                                </section>

                                <div class="ra-top-grid">
                                    <section class="ra-card">
                                        <h3>MOTIVOS</h3>
                                        <div class="ra-reason-list">
                                            @forelse($whyHere as $reason)
                                                <div class="ra-reason-item">
                                                    <span class="ra-round-icon red"><i class="bi bi-exclamation-triangle"></i></span>
                                                    <p>{{ $reason }}</p>
                                                </div>
                                            @empty
                                                <div class="ra-reason-item"><span class="ra-round-icon orange"><i class="bi bi-info-circle"></i></span><p>Revise o item antes de concluir.</p></div>
                                            @endforelse
                                        </div>
                                    </section>

                                    <section class="ra-card">
                                        <h3>SE NÃO RESOLVER</h3>
                                        <div class="ra-impact-list">
                                            @forelse($impactRows as $impact)
                                                <div>
                                                    <span>{{ $impact['label'] ?? 'Impacto' }}</span>
                                                    <strong class="{{ str_contains(strtolower((string)($impact['label'] ?? '')), 'multa') ? 'danger' : '' }}">{{ $impact['value'] ?? '-' }}</strong>
                                                </div>
                                            @empty
                                                <div><span>Cliente impactado</span><strong>{{ $detail['empresa'] }}</strong></div>
                                                <div><span>Responsável atual</span><strong>{{ $detail['responsavel'] }}</strong></div>
                                                <div><span>Risco</span><strong class="danger">Atraso, retrabalho ou cobrança do cliente</strong></div>
                                            @endforelse
                                        </div>
                                    </section>

                                    <section class="ra-card">
                                        <h3>BLOQUEADORES</h3>
                                        <div class="ra-blocker-list">
                                            @forelse($blockerRows as $blocker)
                                                <div><span class="ra-round-icon red"><i class="bi bi-exclamation-triangle"></i></span><p>{{ $blocker }}</p></div>
                                            @empty
                                                <div><span class="ra-round-icon green"><i class="bi bi-check2"></i></span><p>Nenhum bloqueador claro identificado.</p></div>
                                            @endforelse
                                        </div>
                                    </section>
                                </div>

                                <div class="ra-middle-grid ra-middle-grid-balanced">
                                    <section class="ra-card ra-message-card" x-data="{ copied: false }">
                                        <div class="ra-card-header-row">
                                            <h3>MENSAGEM E PASSOS PARA RESOLVER</h3>
                                            <button type="button" class="ra-copy-btn" @click="navigator.clipboard.writeText($refs.raReadyMessage.innerText); copied = true; setTimeout(() => copied = false, 1600)">
                                                <i class="bi bi-clipboard-check"></i><span x-text="copied ? 'Copiado' : 'Copiar'"></span>
                                            </button>
                                        </div>
                                        <div class="ra-message-box" x-ref="raReadyMessage">{{ $detailDraftMessage ?: $readyMessage }}</div>
                                        @if($detailPersonalizeOpen)
                                            <textarea class="ra-message-box" rows="5" wire:model.live.debounce.500ms="detailDraftMessage"></textarea>
                                        @endif
                                        <div class="ra-message-footer-row">
                                            <button type="button" class="ra-personalize" wire:click="toggleDetailPersonalize">
                                                {{ $detailPersonalizeOpen ? 'Ocultar edição' : 'Editar mensagem' }} <i class="bi bi-pencil"></i>
                                            </button>
                                        </div>

                                        <div class="ra-inline-steps">
                                            <h4>PASSOS PARA ENCERRAR</h4>
                                            <ol class="ra-steps ra-steps-compact">
                                                @forelse($checklistRows as $step)
                                                    <li><span>{{ $loop->iteration }}</span><p>{{ $step['titulo'] ?? 'Etapa operacional' }}</p></li>
                                                @empty
                                                    <li><span>1</span><p>Tomar a ação recomendada.</p></li>
                                                    <li><span>2</span><p>Registrar o contato, impedimento ou conclusão.</p></li>
                                                    <li><span>3</span><p>Manter status e responsável atualizados.</p></li>
                                                @endforelse
                                            </ol>
                                        </div>
                                    </section>
                                </div>

                                <div class="ra-bottom-grid ra-bottom-grid-balanced">
                                    <section class="ra-card ra-situation-card">
                                        <h3>ATUALIZAR SITUAÇÃO</h3>
                                        <div class="ra-action-button-grid">
                                            <button type="button" class="ra-personalize" wire:click="registrarSituacaoCliente({{ $detail['id'] }}, 'respondeu')">Cliente respondeu</button>
                                            <button type="button" class="ra-personalize" wire:click="registrarSituacaoCliente({{ $detail['id'] }}, 'documentos_recebidos')">Documentos recebidos</button>
                                            <button type="button" class="ra-personalize" wire:click="registrarSituacaoCliente({{ $detail['id'] }}, 'aguardando_cliente')">Aguardando cliente</button>
                                            <button type="button" class="ra-personalize" wire:click="registrarSituacaoCliente({{ $detail['id'] }}, 'nao_respondeu')">Cliente não respondeu</button>
                                            <button type="button" class="ra-personalize" wire:click="registrarImpedimentoResolverAgora({{ $detail['id'] }}, 'cliente')">Registrar sem resposta</button>
                                            <button type="button" class="ra-personalize" wire:click="registrarImpedimentoResolverAgora({{ $detail['id'] }}, 'documento')">Documento pendente</button>
                                            <button type="button" class="ra-personalize" wire:click="registrarImpedimentoResolverAgora({{ $detail['id'] }}, 'governo')">Sistema indisponível</button>
                                        </div>
                                    </section>

                                    <section class="ra-card ra-postpone-card">
                                        <h3>ADIAR COM REGISTRO</h3>
                                        <div class="ra-action-button-grid ra-action-button-grid-compact">
                                            <button type="button" class="ra-personalize" wire:click="adiarItemResolverAgora({{ $detail['id'] }}, 1)">+1 dia</button>
                                            <button type="button" class="ra-personalize" wire:click="adiarItemResolverAgora({{ $detail['id'] }}, 3)">+3 dias</button>
                                            <button type="button" class="ra-personalize" wire:click="adiarItemResolverAgora({{ $detail['id'] }}, 7)">+7 dias</button>
                                        </div>
                                    </section>
                                </div>
                            </main>

                            <aside class="ra-side">
                                <section class="ra-card ra-timeline-card">
                                    <h3>ÚLTIMOS EVENTOS</h3>
                                    <div class="ra-timeline">
                                        @forelse($timelineRows as $entry)
                                            <article>
                                                <span></span>
                                                <time>{{ $entry['data'] ?? '-' }}</time>
                                                <strong>{{ $entry['titulo'] ?? 'Atualização operacional' }}</strong>
                                                <p>{{ $entry['descricao'] ?? '' }}</p>
                                            </article>
                                        @empty
                                            <article><span></span><time>-</time><strong>Sem histórico recente</strong><p>Use as ações do popup para registrar o próximo movimento.</p></article>
                                        @endforelse
                                    </div>
                                </section>

                                <section class="ra-card">
                                    <h3>SAI DO RESOLVER QUANDO</h3>
                                    <div class="ra-done-list">
                                        @forelse($doneRows as $done)
                                            <div><i class="bi bi-check2"></i><p>{{ $done }}</p></div>
                                        @empty
                                            <div><i class="bi bi-check2"></i><p>Concluído, delegado, corrigido ou documentado com impedimento real.</p></div>
                                        @endforelse
                                    </div>
                                </section>
                            </aside>
                        </div>

                        <footer class="ra-footer">
                            <div><i class="bi bi-lightbulb"></i><strong>Foco:</strong> entender o risco, tomar uma ação e registrar o movimento sem abrir várias telas.</div>
                            <button type="button" wire:click="closeItemDetailModal">Fechar</button>
                        </footer>
                        @endif
                    @else
                        <div class="ra-empty-state">
                            <h2>Item não encontrado</h2>
                            <p>O item pode ter sido atualizado, removido ou estar fora do seu escopo.</p>
                            <button type="button" wire:click="closeItemDetailModal">Fechar</button>
                        </div>
                    @endif
                </article>
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
            <div class="pz-resolution-modal co-home-resolution-modal" role="dialog" aria-modal="true" aria-labelledby="co-delegate-title">
                <div class="pz-resolution-backdrop" wire:click="cancelDelegateModal"></div>
                <article class="pz-resolution-shell pz-resolution-shell-v2 co-detail-home-shell co-delegate-home-shell" @click.stop>
                    <button type="button" class="pz-resolution-x" wire:click="cancelDelegateModal" aria-label="Fechar">×</button>

                    <header class="co-detail-home-header">
                        <span class="co-section-icon purple"><i class="bi bi-person-plus"></i></span>
                        <div>
                            <h3 id="co-delegate-title">Delegar item</h3>
                            <p>Selecione o novo responsável para assumir esta pendência operacional.</p>
                        </div>
                    </header>

                    <div class="co-detail-home-scroll compact">
                        <label class="co-modal-field">
                            <span>Novo responsável</span>
                            <select wire:model.live="delegateResponsavelId">
                                <option value="">Selecione...</option>
                                @foreach ($this->delegateResponsavelOptions() as $responsavelId => $responsavelNome)
                                    <option value="{{ $responsavelId }}">{{ $responsavelNome }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>

                    <footer class="co-detail-footer-actions co-detail-home-footer">
                        <button type="button" class="co-action-btn muted" wire:click="cancelDelegateModal">Cancelar</button>
                        <button type="button" class="co-action-btn purple" wire:click="delegar" wire:loading.attr="disabled" wire:target="delegar">
                            <i class="bi bi-check2"></i>Confirmar delegação
                        </button>
                    </footer>
                </article>
            </div>
        @endif

    </div>
</x-filament-panels::page>
