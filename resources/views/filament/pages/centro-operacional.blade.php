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
    @endphp

    <div class="co-page co-model" wire:loading.class="is-loading" x-data="{ searchOpen: false }" @keydown.window.ctrl.k.prevent="$refs.globalSearch.focus(); searchOpen = true" @keydown.window.meta.k.prevent="$refs.globalSearch.focus(); searchOpen = true" @keydown.escape.window="searchOpen = false">
        <section class="co-topbar">
            <div>
                <div class="co-title-row">
                    <h1>Centro Operacional</h1>
                    <span class="co-info">i</span>
                </div>
                <p>Tenha visão clara do que precisa de ação hoje.</p>
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

        <section class="co-kpi-grid">
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
                <header class="co-panel-header">
                    <div class="co-heading-with-icon">
                        <span class="co-section-icon red"><i class="bi bi-lightning-charge-fill"></i></span>
                        <h2>Resolver Agora <small>(prioridade máxima)</small></h2>
                    </div>
                    <button type="button" class="co-mobile-toggle" @click="open = ! open" :aria-expanded="open.toString()">
                        <i class="bi" :class="open ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                        <span x-text="open ? 'Ocultar' : 'Mostrar'"></span>
                    </button>
                </header>

                <div class="co-action-list co-action-list-v2">
                    @forelse ($resolverAgora as $item)
                        @php
                            $actions = $item['actions'] ?? [];
                            $primary = $item['primary_action'] ?? ['key' => 'open', 'label' => 'Abrir', 'icon' => 'bi-box-arrow-up-right'];
                            $canApprove = (bool) ($actions['approve'] ?? false);
                            $canCorrect = (bool) ($actions['correct'] ?? false);
                            $canDelegate = (bool) ($actions['delegate'] ?? false);
                        @endphp
                        <article class="co-action-card-v2 {{ $item['tone'] ?? 'info' }}">
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

            <section class="co-panel co-clients-panel co-mobile-collapsible" x-data="{ open: false }" :class="{ 'is-open': open }">
                <header class="co-panel-header">
                    <div class="co-heading-with-icon">
                        <span class="co-section-icon orange"><i class="bi bi-exclamation-triangle"></i></span>
                        <h2>Clientes Críticos</h2>
                    </div>
                    <div class="co-header-actions-inline">
                        <a class="co-see-all" href="{{ \App\Filament\Resources\ItemControles\ItemControleResource::getUrl('index') }}">Ver todos</a>
                        <button type="button" class="co-mobile-toggle" @click="open = ! open" :aria-expanded="open.toString()">
                            <i class="bi" :class="open ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                            <span x-text="open ? 'Ocultar' : 'Mostrar'"></span>
                        </button>
                    </div>
                </header>

                <div class="co-client-list-model">
                    @forelse ($clientesCriticos as $cliente)
                        <article class="co-client-model-row co-client-row-with-actions">
                            <a class="co-client-row-link" href="{{ $cliente['url'] }}">
                                <span class="co-client-avatar"><i class="bi bi-building"></i></span>
                                <div class="co-client-main">
                                    <strong>{{ $cliente['cliente'] }}</strong>
                                    <span>{{ $cliente['problema'] }}</span>
                                </div>
                                <span class="co-risk-badge {{ $cliente['tone'] ?? 'warning' }}">Risco {{ $cliente['risco'] }}</span>
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
                    <strong>Alertas Inteligentes</strong>
                    <small>Clique para visualizar alertas críticos, importantes, atenção e informativos.</small>
                </span>
                <span class="co-alerts-toggle-count">
                    {{ number_format(collect($alertasInteligentes ?? [])->sum(fn ($group) => count($group['items'] ?? [])), 0, ',', '.') }} alertas
                </span>
            </button>

            <div class="co-alerts-collapse" x-show="open" x-cloak>
                <header class="co-panel-header compact">
                    <div class="co-heading-with-icon">
                        <span class="co-section-icon red"><i class="bi bi-broadcast-pin"></i></span>
                        <h2>Alertas Inteligentes</h2>
                    </div>
                    <span class="co-panel-subtitle">Crítico, importante, atenção e informativo</span>
                </header>

                <div class="co-alerts-grid">
                    @foreach ($alertasInteligentes as $alertKey => $group)
                        @php $items = collect($group['items'] ?? [])->take(4)->values(); @endphp
                        <article class="co-alert-column {{ $group['tone'] ?? 'info' }}">
                            <header>
                                <span><i class="bi {{ $group['icon'] ?? 'bi-info-circle' }}"></i>{{ $group['label'] ?? 'Alerta' }}</span>
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
                                        <span>Nenhum alerta nesta camada.</span>
                                    </div>
                                @endforelse
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>



        @if($detailModalOpen)
            @php $detail = $this->selectedItemDetail(); @endphp
            <div class="co-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="co-detail-title" wire:click.self="closeItemDetailModal">
                <div class="co-modal-card co-detail-modal-card">
                    @if($detail)
                        <header>
                            <span class="co-section-icon {{ $detailModalSource === 'cliente' ? 'orange' : 'red' }}"><i class="bi {{ $detailModalSource === 'cliente' ? 'bi-building-exclamation' : 'bi-lightning-charge-fill' }}"></i></span>
                            <div>
                                <h3 id="co-detail-title">{{ $detailModalSource === 'cliente' ? 'Detalhes do Cliente Crítico' : 'Detalhes para Resolver Agora' }}</h3>
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
                                    <strong>{{ $detail['suggestion']['title'] ?? 'Avaliar item' }}</strong>
                                    <p>{{ $detail['suggestion']['text'] ?? 'Use os dados abaixo para decidir a próxima ação.' }}</p>
                                </div>
                                <span>{{ $detail['suggestion']['primary_action'] ?? 'Decidir agora' }}</span>
                            </div>

                            <div class="co-detail-grid">
                                <div><small>Status</small><strong>{{ $detail['status'] }}</strong></div>
                                <div><small>Responsável</small><strong>{{ $detail['responsavel'] }}</strong></div>
                                <div><small>Vencimento</small><strong>{{ $detail['vencimento'] }}</strong><em>{{ $detail['dias_prazo'] ?? '' }}</em></div>
                                <div><small>Valor/Impacto</small><strong>{{ $detail['valor'] }}</strong></div>
                                <div><small>Conclusão</small><strong>{{ $detail['conclusao'] }}</strong></div>
                                <div><small>Origem</small><strong>{{ $detailModalSource === 'cliente' ? 'Clientes Críticos' : 'Resolver Agora' }}</strong></div>
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

                            @if($detailModalSource === 'cliente')
                                <section class="co-detail-insight-card co-related-client-card">
                                    <h4><i class="bi bi-building"></i>Outros itens recentes do cliente</h4>
                                    @forelse(($detail['related_client_items'] ?? []) as $related)
                                        <article>
                                            <div>
                                                <strong>{{ $related['titulo'] }}</strong>
                                                <span>{{ $related['status'] }} • {{ $related['responsavel'] }} • {{ $related['vencimento'] }}</span>
                                            </div>
                                            <a class="co-mini-action" href="{{ $related['url'] }}"><i class="bi bi-box-arrow-up-right"></i>Abrir</a>
                                        </article>
                                    @empty
                                        <div class="co-empty clean small"><strong>Nenhum outro item recente desse cliente.</strong></div>
                                    @endforelse
                                </section>
                            @endif
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
                                <h3 id="co-detail-title">Item não encontrado</h3>
                                <p>O item pode ter sido atualizado, removido ou estar fora do seu escopo.</p>
                            </div>
                        </header>
                        <footer><button type="button" class="co-action-btn muted" wire:click="closeItemDetailModal">Fechar</button></footer>
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
