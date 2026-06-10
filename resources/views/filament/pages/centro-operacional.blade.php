<x-filament-panels::page>
    <link rel="stylesheet" href="{{ asset('css/centro-operacional.css') }}?v={{ file_exists(public_path('css/centro-operacional.css')) ? filemtime(public_path('css/centro-operacional.css')) : time() }}">

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
            : 'Operação sem ação crítica neste momento.';
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

    <div class="co-page co-model" wire:loading.class="is-loading" x-data="{ searchOpen: false }" @keydown.window.ctrl.k.prevent="$refs.globalSearch.focus(); searchOpen = true" @keydown.window.meta.k.prevent="$refs.globalSearch.focus(); searchOpen = true" @keydown.escape.window="searchOpen = false">
        <section class="co-topbar">
            <div>
                <div class="co-title-row">
                    <h1>Centro Operacional</h1>
                    <span class="co-info">i</span>
                </div>
                <p>Veja primeiro o que pode gerar multa, atraso ou retrabalho hoje.</p>
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



        <nav class="co-page-cluster co-main-cluster" aria-label="Navegação do Centro Operacional">
            <a class="co-cluster-item active" href="{{ \App\Filament\Pages\CentroOperacional::getUrl() }}">
                <span class="co-cluster-icon"><i class="bi bi-command"></i></span>
                <span>
                    <strong>Centro Operacional</strong>
                    <small>Riscos, resolver agora e resultados</small>
                </span>
            </a>
            <a class="co-cluster-item" href="{{ \App\Filament\Pages\CentroOperacionalGestao::getUrl() }}?aba=workload">
                <span class="co-cluster-icon"><i class="bi bi-grid-1x2"></i></span>
                <span>
                    <strong>Operação Interna</strong>
                    <small>Workload, aprovações e financeiro</small>
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
                            <h2>Ação Recomendada <small>Resolver Agora</small></h2>
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
                        <span>Fila única de prioridade</span>
                        <strong>{{ number_format($resolverTotal, 0, ',', '.') }} {{ $resolverTotal === 1 ? 'ação crítica' : 'ações críticas' }}</strong>
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
                            <strong>Nenhuma ação crítica agora.</strong>
                            <p>Quando existir risco, vencimento ou aprovação parada, aparecerá aqui.</p>
                        </div>
                    @endforelse
                </div>

                @if(count($resolverAgora) > 0)
                    <a class="co-see-all centered" href="{{ \App\Filament\Resources\ItemControles\ItemControleResource::getUrl('index') }}">Ver todas as ações →</a>
                @endif
            </section>

            <section class="co-panel co-clients-panel co-client-risk-panel co-mobile-collapsible" x-data="{ open: false }" :class="{ 'is-open': open }">
                <header class="co-panel-header">
                    <div class="co-heading-with-icon">
                        <span class="co-section-icon orange"><i class="bi bi-building-exclamation"></i></span>
                        <div>
                            <h2>Clientes em Maior Risco</h2>
                            <p class="co-panel-subtitle">Ranking para atacar primeiro quem pode gerar atraso, multa ou retrabalho.</p>
                        </div>
                    </div>
                    <div class="co-header-actions-inline">
                        <a class="co-see-all" href="{{ \App\Filament\Resources\ItemControles\ItemControleResource::getUrl('index') }}">Ver todos</a>
                        <button type="button" class="co-mobile-toggle" @click="open = ! open" :aria-expanded="open.toString()">
                            <i class="bi" :class="open ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                            <span x-text="open ? 'Ocultar' : 'Mostrar'"></span>
                        </button>
                    </div>
                </header>

                <div class="co-risk-summary-strip">
                    <article>
                        <small>Clientes críticos</small>
                        <strong>{{ number_format($clientesCriticosCollection->count(), 0, ',', '.') }}</strong>
                    </article>
                    <article>
                        <small>Risco alto</small>
                        <strong>{{ number_format($clientesRiscoAlto, 0, ',', '.') }}</strong>
                    </article>
                    <article>
                        <small>Com ação rápida</small>
                        <strong>{{ number_format($clientesComItem, 0, ',', '.') }}</strong>
                    </article>
                </div>

                <div class="co-client-list-model co-client-risk-list">
                    @forelse ($clientesMaiorRisco as $cliente)
                        @php
                            $clientTone = $cliente['tone'] ?? 'warning';
                            $riskLabel = $cliente['risco'] ?? 'alto';
                        @endphp
                        <article class="co-client-model-row co-client-risk-row {{ $clientTone }}">
                            <a class="co-client-row-link" href="{{ $cliente['url'] }}">
                                <span class="co-client-rank">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                <span class="co-client-avatar"><i class="bi bi-building"></i></span>
                                <div class="co-client-main">
                                    <strong>{{ $cliente['cliente'] }}</strong>
                                    <span>{{ $cliente['problema'] }}</span>
                                    <small><i class="bi bi-lightning-charge-fill"></i> Prioridade: resolver antes de virar prejuízo operacional.</small>
                                </div>
                                <span class="co-risk-badge {{ $clientTone }}">Risco {{ $riskLabel }}</span>
                            </a>
                            @if(!empty($cliente['item_id']))
                                <button type="button" class="co-mini-action dark" wire:click="openItemDetailModal({{ (int) $cliente['item_id'] }}, 'cliente')" wire:loading.attr="disabled">
                                    <i class="bi bi-eye"></i>Detalhes
                                </button>
                            @endif
                        </article>
                    @empty
                        <div class="co-empty clean">
                            <strong>Nenhum cliente crítico.</strong>
                            <p>Sem clientes em risco neste momento.</p>
                        </div>
                    @endforelse
                </div>
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
                $whyHere = collect($detail['why_here'] ?? [])->take(4)->values();
                $impactRows = collect($detail['operational_impact'] ?? [])->take(4)->values();
                $checklistRows = collect($detail['checklist'] ?? [])->take(5)->values();
                $blockerRows = collect($detail['blockers'] ?? [])->take(3)->values();
                $doneRows = collect($detail['done_definition'] ?? [])->take(4)->values();
                $timelineRows = collect($detail['timeline'] ?? [])->take(4)->values();
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
                        <header class="ra-header">
                            <div class="ra-heading">
                                <span class="ra-kicker">AÇÃO RECOMENDADA</span>
                                <div class="ra-title-row">
                                    <h2 id="ra-detail-title">{{ $detail['title'] }}</h2>
                                    <span class="ra-critical-pill">{{ strtoupper($detail['prioridade'] ?? 'CRÍTICA') }}</span>
                                </div>
                                <div class="ra-meta-row">
                                    <span><i class="bi bi-clipboard2-check"></i>{{ $detail['categoria'] }}</span>
                                    <span>•</span>
                                    <span>{{ $detail['categoria'] }}</span>
                                    <span>•</span>
                                    <span>Ref. {{ now()->format('m/Y') }}</span>
                                    <span class="ra-client-chip">Cliente: {{ $detail['empresa'] }}</span>
                                </div>
                            </div>

                            <div class="ra-header-actions">
                                <button type="button"><i class="bi bi-star"></i>Favoritar</button>
                                <button type="button" class="ra-icon-only"><i class="bi bi-three-dots"></i></button>
                            </div>
                        </header>

                        <div class="ra-body">
                            <main class="ra-main">
                                <section class="ra-summary-card">
                                    <div class="ra-alert-icon"><i class="bi bi-exclamation-lg"></i></div>
                                    <div>
                                        <h3>RESUMO EXECUTIVO</h3>
                                        <p>{{ $detail['executive_summary'] ?? 'Esta obrigação vence hoje e ainda não foi concluída.' }}</p>
                                        <p class="ra-summary-strong">{{ $actionImpact }}</p>
                                    </div>
                                </section>

                                <div class="ra-top-grid">
                                    <section class="ra-card">
                                        <h3>POR QUE ESTÁ AQUI?</h3>
                                        <div class="ra-reason-list">
                                            @forelse($whyHere as $reason)
                                                <div class="ra-reason-item">
                                                    <span class="ra-round-icon red"><i class="bi bi-exclamation-triangle"></i></span>
                                                    <p>{{ $reason }}</p>
                                                </div>
                                            @empty
                                                <div class="ra-reason-item"><span class="ra-round-icon red"><i class="bi bi-clock"></i></span><p>Vence em menos de 24 horas</p></div>
                                                <div class="ra-reason-item"><span class="ra-round-icon orange"><i class="bi bi-hourglass-split"></i></span><p>Está parado há alguns dias</p></div>
                                                <div class="ra-reason-item"><span class="ra-round-icon red"><i class="bi bi-file-earmark-lock"></i></span><p>Documento obrigatório pendente</p></div>
                                            @endforelse
                                        </div>
                                    </section>

                                    <section class="ra-card">
                                        <h3>IMPACTO SE NÃO RESOLVER</h3>
                                        <div class="ra-impact-list">
                                            @forelse($impactRows as $impact)
                                                <div>
                                                    <span>{{ $impact['label'] ?? 'Impacto' }}</span>
                                                    <strong class="{{ str_contains(strtolower((string)($impact['label'] ?? '')), 'multa') ? 'danger' : '' }}">{{ $impact['value'] ?? '-' }}</strong>
                                                </div>
                                            @empty
                                                <div><span>Risco de multa</span><strong class="danger">{{ $detail['valor'] }}</strong></div>
                                                <div><span>Cliente impactado</span><strong>{{ $detail['empresa'] }}</strong></div>
                                                <div><span>Departamento</span><strong>{{ $detail['categoria'] }}</strong></div>
                                                <div><span>Tipo de impacto</span><strong><em>Financeiro e Fiscal</em></strong></div>
                                            @endforelse
                                        </div>
                                    </section>

                                    <section class="ra-card">
                                        <h3>TEMPO PARADO</h3>
                                        <div class="ra-stalled-list">
                                            <div><i class="bi bi-clock-history"></i><span>Última atualização</span><strong>{{ $detail['stalled_info']['last_update'] ?? '-' }}</strong></div>
                                            <div><i class="bi bi-clock"></i><span>Parado há</span><strong class="danger">{{ $detail['stalled_info']['days'] ?? 'Sem histórico' }}</strong></div>
                                            <div><i class="bi bi-person-badge"></i><span>Responsável atual</span><strong>{{ $detail['responsavel'] }}</strong></div>
                                        </div>
                                    </section>
                                </div>

                                <div class="ra-middle-grid">
                                    <section class="ra-card ra-action-card">
                                        <h3>AÇÃO RECOMENDADA – O QUE FAZER AGORA</h3>
                                        <div class="ra-action-content">
                                            <ol class="ra-steps">
                                                @forelse($checklistRows as $step)
                                                    <li><span>{{ $loop->iteration }}</span><p>{{ $step['titulo'] ?? 'Etapa operacional' }}</p></li>
                                                @empty
                                                    <li><span>1</span><p>Abrir obrigação referente ao período atual</p></li>
                                                    <li><span>2</span><p>Validar informações e débitos</p></li>
                                                    <li><span>3</span><p>Anexar/validar documentos necessários</p></li>
                                                    <li><span>4</span><p>Transmitir obrigação</p></li>
                                                    <li><span>5</span><p>Confirmar transmissão e gerar recibo</p></li>
                                                @endforelse
                                            </ol>

                                            <aside class="ra-action-note">
                                                <div><i class="bi bi-stopwatch"></i><span>Tempo estimado</span><strong>15 minutos</strong></div>
                                                <div><i class="bi bi-shield-check"></i><span>Impacto da ação</span><p>{{ $actionImpact }}</p></div>
                                            </aside>
                                        </div>
                                    </section>

                                    <section class="ra-card ra-message-card" x-data="{ copied: false }">
                                        <div class="ra-card-header-row">
                                            <h3>MENSAGEM PRONTA PARA O CLIENTE</h3>
                                            <button type="button" class="ra-copy-btn" @click="navigator.clipboard.writeText($refs.raReadyMessage.innerText); copied = true; setTimeout(() => copied = false, 1600)">
                                                <i class="bi bi-clipboard-check"></i><span x-text="copied ? 'Copiado' : 'Copiar mensagem'"></span>
                                            </button>
                                        </div>
                                        <div class="ra-message-box" x-ref="raReadyMessage">{{ $readyMessage }}</div>
                                        <button type="button" class="ra-personalize">Personalizar antes de enviar <i class="bi bi-pencil"></i></button>
                                    </section>
                                </div>

                                <div class="ra-bottom-grid">
                                    <section class="ra-card">
                                        <h3>BLOQUEADORES IDENTIFICADOS</h3>
                                        <div class="ra-blocker-list">
                                            @forelse($blockerRows as $blocker)
                                                <div><span class="ra-round-icon red"><i class="bi bi-exclamation-triangle"></i></span><p>{{ $blocker }}</p></div>
                                            @empty
                                                <div><span class="ra-round-icon green"><i class="bi bi-check2"></i></span><p>Nenhuma aprovação pendente</p></div>
                                            @endforelse
                                        </div>
                                    </section>

                                    <section class="ra-card">
                                        <h3>QUANDO ESSE ITEM DEIXA DE APARECER AQUI?</h3>
                                        <div class="ra-done-list">
                                            @forelse($doneRows as $done)
                                                <div><i class="bi bi-check2"></i><p>{{ $done }}</p></div>
                                            @empty
                                                <div><i class="bi bi-check2"></i><p>Obrigação concluída com sucesso</p></div>
                                                <div><i class="bi bi-check2"></i><p>Recibo de entrega gerado</p></div>
                                                <div><i class="bi bi-check2"></i><p>Não há pendências relacionadas</p></div>
                                            @endforelse
                                        </div>
                                        <footer>O item será removido automaticamente após a conclusão.</footer>
                                    </section>

                                    <section class="ra-card ra-next-card">
                                        <h3>PRÓXIMA AÇÃO SUGERIDA</h3>
                                        <div class="ra-next-alert"><i class="bi bi-exclamation-triangle"></i><strong>{{ $primaryAction }}</strong></div>
                                        <div class="ra-next-meta"><span>Canal sugerido:</span><strong><i class="bi bi-whatsapp"></i> WhatsApp</strong></div>
                                        <div class="ra-next-meta"><span>Prioridade:</span><strong><b></b> Alta</strong></div>
                                        <div class="ra-next-meta"><span>Melhor horário:</span><strong>Agora</strong></div>
                                        <button type="button" class="ra-primary-btn"><i class="bi bi-whatsapp"></i>Iniciar contato com o cliente</button>
                                    </section>
                                </div>
                            </main>

                            <aside class="ra-side">
                                <section class="ra-card ra-score-card">
                                    <h3>SCORE DE URGÊNCIA</h3>
                                    <div class="ra-score-wrap">
                                        <div class="ra-score-ring" style="--score: {{ $scoreValue }};"><strong>{{ $scoreValue }}</strong><span>/100</span></div>
                                        <div class="ra-score-reasons">
                                            <h4>Por que esse score?</h4>
                                            @forelse($scoreReasons as $reason)
                                                <div><span>{{ $reason }}</span><strong>+{{ max(7, 40 - (($loop->iteration - 1) * 10)) }}</strong></div>
                                            @empty
                                                <div><span>Vence hoje</span><strong>+40</strong></div>
                                                <div><span>Parado há 4 dias</span><strong>+30</strong></div>
                                                <div><span>Obrigação fiscal</span><strong>+15</strong></div>
                                                <div><span>Cliente crítico</span><strong>+7</strong></div>
                                            @endforelse
                                        </div>
                                    </div>
                                </section>

                                <section class="ra-card ra-client-card">
                                    <div class="ra-card-header-row">
                                        <h3>CLIENTE CRÍTICO</h3>
                                        <span class="ra-risk-pill">ALTO RISCO</span>
                                    </div>
                                    <div class="ra-client-metrics">
                                        <div><span>Pendências abertas</span><strong>{{ $criticalClient['open_items'] ?? $criticalClient['pendencias_abertas'] ?? $criticalClient['open'] ?? count($detail['related_client_items'] ?? []) }}</strong></div>
                                        <div><span>Pendências vencidas</span><strong>{{ $criticalClient['late_items'] ?? $criticalClient['pendencias_vencidas'] ?? '-'  }}</strong></div>
                                        <div><span>Faturamento (12m)</span><strong>{{ $criticalClient['revenue_12m'] ?? $criticalClient['faturamento_12m'] ?? $detail['valor'] }}</strong></div>
                                    </div>
                                    <a href="{{ $detail['url'] }}" class="ra-outline-link">Ver dashboard do cliente <i class="bi bi-box-arrow-up-right"></i></a>
                                </section>

                                <section class="ra-card ra-timeline-card">
                                    <h3>LINHA DO TEMPO</h3>
                                    <div class="ra-timeline">
                                        @forelse($timelineRows as $entry)
                                            <article>
                                                <span></span>
                                                <time>{{ $entry['data'] ?? '-' }}</time>
                                                <strong>{{ $entry['titulo'] ?? 'Atualização operacional' }}</strong>
                                                <p>{{ $entry['descricao'] ?? '' }}</p>
                                            </article>
                                        @empty
                                            <article><span></span><time>{{ now()->format('d/m/Y H:i') }}</time><strong>Item identificado</strong><p>Ação recomendada criada automaticamente.</p></article>
                                        @endforelse
                                    </div>
                                </section>

                                <section class="ra-quick-actions">
                                    <h3>AÇÕES RÁPIDAS</h3>
                                    <div>
                                        <a href="{{ $detail['url'] }}">Abrir obrigação <i class="bi bi-box-arrow-up-right"></i></a>
                                        <a href="{{ $detail['url'] }}">Ver cliente <i class="bi bi-box-arrow-up-right"></i></a>
                                        @if(($detail['actions']['delegate'] ?? false) && ! $detail['is_closed'])
                                            <button type="button" wire:click="openDelegateModal({{ $detail['id'] }})"><i class="bi bi-person-plus"></i>Delegar tarefa</button>
                                        @else
                                            <button type="button"><i class="bi bi-person-plus"></i>Delegar tarefa</button>
                                        @endif
                                        <button type="button" class="success" wire:click="closeItemDetailModal"><i class="bi bi-check2"></i>Marcar como resolvido</button>
                                    </div>
                                </section>
                            </aside>
                        </div>

                        <footer class="ra-footer">
                            <div><i class="bi bi-lightbulb"></i><strong>Dica:</strong> Resolva agora para evitar multas, retrabalho e manter a confiança do cliente.</div>
                            <label><input type="checkbox">Não mostrar novamente</label>
                            <button type="button" wire:click="closeItemDetailModal">Fechar</button>
                        </footer>
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
            @php $workloadDetail = $this->selectedWorkloadDetail(); @endphp
            <div class="pz-resolution-modal co-home-resolution-modal" role="dialog" aria-modal="true" aria-labelledby="co-workload-title">
                <div class="pz-resolution-backdrop" wire:click="closeWorkloadModal"></div>
                <article class="pz-resolution-shell pz-resolution-shell-v2 co-detail-home-shell" @click.stop>
                    <button type="button" class="pz-resolution-x" wire:click="closeWorkloadModal" aria-label="Fechar">×</button>

                    <header class="co-detail-home-header">
                        <span class="co-section-icon purple"><i class="bi bi-people-fill"></i></span>
                        <div>
                            <div class="pz-resolution-breadcrumb">
                                <span>Workload da Equipe</span>
                                <b>›</b>
                                <span>Detalhes do responsável</span>
                            </div>
                            <h3 id="co-workload-title">{{ $workloadDetail['responsavel']?->nome ?? 'Responsável' }}</h3>
                            <p>{{ number_format((int) ($workloadDetail['total'] ?? 0), 0, ',', '.') }} itens abertos • {{ number_format((int) ($workloadDetail['late'] ?? 0), 0, ',', '.') }} atrasados</p>
                        </div>
                    </header>

                    <div class="co-detail-home-scroll">
                        <div class="co-detail-modal-body">
                            <div class="co-detail-grid">
                                <div><small>Total aberto</small><strong>{{ number_format((int) ($workloadDetail['total'] ?? 0), 0, ',', '.') }}</strong></div>
                                <div><small>Críticos</small><strong>{{ number_format((int) ($workloadDetail['critical'] ?? 0), 0, ',', '.') }}</strong></div>
                                <div><small>Atrasados</small><strong>{{ number_format((int) ($workloadDetail['late'] ?? 0), 0, ',', '.') }}</strong></div>
                                <div><small>Decisão sugerida</small><strong>{{ $workloadDetail['bottleneck_summary']['action'] ?? (!empty($workloadDetail['recommendation']) ? 'Redistribuir' : 'Monitorar') }}</strong></div>
                            </div>

                            <div class="co-decision-box {{ $workloadDetail['bottleneck_summary']['tone'] ?? 'warning' }}">
                                <div>
                                    <small>Leitura operacional da carga</small>
                                    <strong>{{ $workloadDetail['bottleneck_summary']['title'] ?? ($workloadDetail['recommendation']['title'] ?? 'Avaliar redistribuição') }}</strong>
                                    <p>{{ $workloadDetail['bottleneck_summary']['text'] ?? ($workloadDetail['recommendation']['text'] ?? 'Analise os itens abaixo e redistribua apenas o que estiver travando a operação.') }}</p>
                                </div>
                                <span>{{ $workloadDetail['bottleneck_summary']['action'] ?? 'Rebalancear carga' }}</span>
                            </div>

                            <section class="co-detail-insight-card">
                                <h4><i class="bi bi-activity"></i>Sinais de gargalo</h4>
                                <div class="co-detail-grid">
                                    @foreach(($workloadDetail['workload_signals'] ?? []) as $signal)
                                        <div>
                                            <small>{{ $signal['label'] }}</small>
                                            <strong>{{ $signal['value'] }}</strong>
                                            <em>{{ $signal['text'] }}</em>
                                        </div>
                                    @endforeach
                                </div>
                            </section>

                            <div class="co-detail-insights-grid">
                                <section class="co-detail-insight-card">
                                    <h4><i class="bi bi-list-check"></i>Itens que mais pesam na fila</h4>
                                    @forelse(($workloadDetail['items'] ?? []) as $item)
                                        <article>
                                            <div>
                                                <strong>{{ $item['title'] }}</strong>
                                                <span>{{ $item['empresa'] }} • {{ $item['status'] }} • {{ $item['vencimento'] }} • {{ $item['dias_prazo'] }}</span>
                                            </div>
                                            <a class="co-mini-action" href="{{ $item['url'] }}"><i class="bi bi-box-arrow-up-right"></i>Abrir</a>
                                        </article>
                                    @empty
                                        <div class="co-empty clean small"><strong>Nenhum item aberto para este responsável.</strong></div>
                                    @endforelse
                                </section>

                                <section class="co-detail-insight-card">
                                    <h4><i class="bi bi-arrow-left-right"></i>Redistribuição</h4>
                                    <label class="co-modal-field">
                                        <span>Item</span>
                                        <select wire:model.live="redistributionItemId">
                                            <option value="">Selecione...</option>
                                            @foreach (($workloadDetail['items'] ?? []) as $item)
                                                <option value="{{ $item['id'] }}">{{ $item['title'] }} — {{ $item['empresa'] }}</option>
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
                                </section>
                            </div>
                        </div>
                    </div>

                    <footer class="co-detail-footer-actions co-detail-home-footer">
                        <button type="button" class="co-action-btn muted" wire:click="closeWorkloadModal">Fechar</button>
                        <button type="button" class="co-action-btn purple" wire:click="redistribuirItemSelecionado" wire:loading.attr="disabled" wire:target="redistribuirItemSelecionado">
                            <i class="bi bi-check2"></i>Confirmar redistribuição
                        </button>
                    </footer>
                </article>
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
