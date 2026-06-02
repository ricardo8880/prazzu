<x-filament-panels::page>
    <link rel="stylesheet" href="{{ asset('css/centro-operacional.css') }}?v={{ file_exists(public_path('css/centro-operacional.css')) ? filemtime(public_path('css/centro-operacional.css')) : time() }}">

    @php
        $cards = $data['cards'] ?? [];
        $resolverAgora = collect($data['resolver_agora'] ?? [])->take(5)->values()->all();
        $clientesCriticos = $data['clientes_criticos'] ?? [];
        $vencimentos = $data['vencimentos'] ?? ['selected' => 'today', 'periods' => [], 'rows' => [], 'total' => 0];
        $deadlineRows = collect($vencimentos['rows'] ?? [])->take(4)->values();
        $deadlineTotal = (int) ($vencimentos['total'] ?? $deadlineRows->sum('value'));
        $aprovacoes = collect($data['aprovacoes'] ?? [])->take(3)->values()->all();
        $workload = collect($data['workload'] ?? [])->take(5)->values()->all();
        $departamentos = $data['departamentos'] ?? [];
        $resultadosMes = $data['resultados_mes'] ?? [];
        $healthScore = $data['health_score'] ?? ['label' => 'Excelente', 'tone' => 'success', 'value' => 100];
        $statusOptions = $data['status_options'] ?? [];
        $departmentOptions = $data['department_options'] ?? [];
        $dateRangeOptions = $data['date_range_options'] ?? [];
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

    <div class="co-page co-model" wire:loading.class="is-loading">
        <section class="co-topbar">
            <div>
                <div class="co-title-row">
                    <h1>Centro Operacional</h1>
                    <span class="co-info">i</span>
                </div>
                <p>Tenha visão clara do que precisa de ação hoje.</p>
            </div>

            <div class="co-top-actions">
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
                    <i class="bi bi-arrow-clockwise" wire:loading.class="spin" wire:target="refreshDashboard,setDateRange,setDeadlinePeriod,statusFilter,departmentFilter"></i>
                    <span wire:loading.remove wire:target="refreshDashboard">Atualizar</span>
                    <span wire:loading wire:target="refreshDashboard">Atualizando...</span>
                </button>
            </div>
        </section>

        <section class="co-kpi-grid">
            @foreach ($cards as $index => $card)
                @php
                    $tone = $card['tone'] ?? 'info';
                    $iconTone = $defaultIconClass[$index] ?? $tone;
                    $icon = $defaultIcons[$index] ?? 'bi-activity';
                @endphp
                <article class="co-kpi-card {{ $tone }}">
                    <div class="co-kpi-content">
                        <span class="co-kpi-label">{{ $card['label'] ?? '-' }}</span>
                        <strong>{{ is_numeric($card['value'] ?? null) ? number_format((int) $card['value'], 0, ',', '.') : ($card['value'] ?? '-') }}</strong>
                        <small>{{ $card['hint'] ?? '' }}</small>
                    </div>
                    <div class="co-kpi-icon {{ $iconTone }}"><i class="bi {{ $icon }}"></i></div>
                </article>
            @endforeach
        </section>

        <section class="co-focus-grid">
            <section class="co-panel co-resolve-panel">
                <header class="co-panel-header">
                    <div class="co-heading-with-icon">
                        <span class="co-section-icon red"><i class="bi bi-lightning-charge-fill"></i></span>
                        <h2>Resolver Agora <small>(prioridade máxima)</small></h2>
                    </div>
                </header>

                <div class="co-action-list">
                    @forelse ($resolverAgora as $item)
                        <a class="co-action-row" href="{{ $item['url'] }}">
                            <span class="co-action-icon {{ $item['tone'] ?? 'info' }}"><i class="bi {{ ($item['tone'] ?? '') === 'danger' ? 'bi-file-earmark-pdf-fill' : (($item['tone'] ?? '') === 'success' ? 'bi-file-earmark-check-fill' : (($item['tone'] ?? '') === 'warning' ? 'bi-receipt-cutoff' : 'bi-file-earmark-text-fill')) }}"></i></span>
                            <div class="co-action-main">
                                <strong>{{ $item['title'] }}</strong>
                                <span>{{ $item['empresa'] }}</span>
                            </div>
                            <span class="co-pill {{ $item['tone'] ?? 'info' }}">{{ $item['status'] }}</span>
                            <span class="co-time {{ $item['tone'] ?? 'info' }}">{{ $item['due'] ? 'Prazo ' . $item['due'] : ($item['stopped_for'] ?? '-') }}</span>
                            <span class="co-row-arrow">›</span>
                        </a>
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

            <section class="co-panel co-clients-panel">
                <header class="co-panel-header">
                    <div class="co-heading-with-icon">
                        <span class="co-section-icon orange"><i class="bi bi-exclamation-triangle"></i></span>
                        <h2>Clientes Críticos</h2>
                    </div>
                    <a class="co-see-all" href="{{ \App\Filament\Resources\ItemControles\ItemControleResource::getUrl('index') }}">Ver todos</a>
                </header>

                <div class="co-client-list-model">
                    @forelse ($clientesCriticos as $cliente)
                        <a class="co-client-model-row" href="{{ $cliente['url'] }}">
                            <span class="co-client-avatar"><i class="bi bi-building"></i></span>
                            <div class="co-client-main">
                                <strong>{{ $cliente['cliente'] }}</strong>
                                <span>{{ $cliente['problema'] }}</span>
                            </div>
                            <span class="co-risk-badge {{ $cliente['tone'] ?? 'warning' }}">Risco {{ $cliente['risco'] }}</span>
                        </a>
                    @empty
                        <div class="co-empty clean">
                            <strong>Nenhum cliente crítico.</strong>
                            <p>Sem clientes em risco neste momento.</p>
                        </div>
                    @endforelse
                </div>
            </section>

            <aside class="co-panel co-deadline-panel">
                <header class="co-panel-header compact">
                    <div class="co-heading-with-icon">
                        <span class="co-section-icon blue"><i class="bi bi-calendar3"></i></span>
                        <h2>Vencimentos</h2>
                    </div>
                </header>

                <div class="co-tabs">
                    @foreach (($vencimentos['periods'] ?? []) as $key => $period)
                        @if(in_array($key, ['today', 'seven_days', 'fifteen_days'], true))
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
            <section class="co-panel co-workload-panel">
                <header class="co-panel-header">
                    <div class="co-heading-with-icon">
                        <span class="co-section-icon muted"><i class="bi bi-people"></i></span>
                        <h2>Workload da Equipe</h2>
                    </div>
                    <a class="co-see-all" href="{{ \App\Filament\Resources\ItemControles\ItemControleResource::getUrl('index') }}">Ver todos</a>
                </header>

                <div class="co-workload-list-model">
                    @forelse ($workload as $row)
                        <div class="co-workload-model-row {{ $row['tone'] ?? 'success' }}">
                            <span class="co-person-avatar">{{ mb_strtoupper(mb_substr($row['name'] ?? 'U', 0, 1)) }}</span>
                            <div class="co-person-info">
                                <strong>{{ $row['name'] }}</strong>
                                <small>{{ $row['total'] }} tarefas • {{ $row['status'] ?? 'Normal' }}</small>
                            </div>
                            <div class="co-progress"><span style="width: {{ (int) ($row['percent'] ?? 0) }}%"></span></div>
                            <b>{{ (int) ($row['percent'] ?? 0) }}%</b>
                        </div>
                    @empty
                        <div class="co-empty clean"><strong>Nenhuma carga pendente.</strong></div>
                    @endforelse
                </div>
            </section>

            <section class="co-panel co-department-panel">
                <header class="co-panel-header">
                    <div class="co-heading-with-icon">
                        <span class="co-section-icon muted"><i class="bi bi-diagram-3"></i></span>
                        <h2>Pendências por Departamento</h2>
                    </div>
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

            <section class="co-panel co-approvals-panel">
                <header class="co-panel-header">
                    <div class="co-heading-with-icon">
                        <span class="co-section-icon blue"><i class="bi bi-file-earmark-check"></i></span>
                        <h2>Aprovações</h2>
                    </div>
                    <a class="co-see-all" href="{{ \App\Filament\Resources\ItemControles\ItemControleResource::getUrl('index') }}">Ver todas</a>
                </header>

                <div class="co-small-list-model">
                    @forelse ($aprovacoes as $item)
                        <a class="co-small-model-row" href="{{ $item['url'] }}">
                            <span class="co-small-icon {{ $item['tone'] ?? 'info' }}"><i class="bi bi-building"></i></span>
                            <div>
                                <strong>{{ $item['empresa'] }}</strong>
                                <span>{{ $item['title'] }}</span>
                            </div>
                            <span class="co-mini-pill {{ $item['due'] ? 'warning' : 'muted' }}">{{ $item['due'] ? 'Hoje' : 'Aguardando' }}</span>
                        </a>
                    @empty
                        <div class="co-empty clean"><strong>Nada esperando aprovação.</strong></div>
                    @endforelse
                </div>
            </section>

            <section class="co-panel co-results-panel {{ $resultTone }}">
                <header class="co-panel-header">
                    <div class="co-heading-with-icon">
                        <span class="co-section-icon green"><i class="bi bi-trophy-fill"></i></span>
                        <h2>Resultados deste mês</h2>
                    </div>
                    <span class="co-party"><i class="bi bi-stars"></i></span>
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
    </div>
</x-filament-panels::page>
