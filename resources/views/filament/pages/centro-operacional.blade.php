<x-filament-panels::page>
    <link rel="stylesheet" href="{{ asset('css/centro-operacional.css') }}?v={{ file_exists(public_path('css/centro-operacional.css')) ? filemtime(public_path('css/centro-operacional.css')) : time() }}">

    @php
        $cards = $data['cards'] ?? [];
        $resolverAgora = $data['resolver_agora'] ?? [];
        $clientesCriticos = $data['clientes_criticos'] ?? [];
        $vencimentos = $data['vencimentos'] ?? [];
        $aprovacoes = $data['aprovacoes'] ?? [];
        $financeiro = $data['financeiro'] ?? [];
        $workload = $data['workload'] ?? [];
        $departamentos = $data['departamentos'] ?? [];
        $resultadosMes = $data['resultados_mes'] ?? [];
        $todayLabel = now()->translatedFormat('d \\d\\e F');
        $defaultIcons = ['bi-exclamation-triangle-fill', 'bi-calendar2-week-fill', 'bi-clock-fill', 'bi-file-earmark-text-fill', 'bi-currency-dollar'];
        $defaultIconClass = ['danger', 'warning', 'danger', 'info', 'success'];
        $departmentColors = ['Fiscal' => 'green', 'Contábil' => 'blue', 'DP' => 'orange', 'Departamento Pessoal' => 'orange', 'Trabalhista' => 'orange', 'Societário' => 'purple'];
        $departmentRows = collect($departamentos)->take(4)->values();
        $departmentTotal = (int) collect($departamentos)->sum('value');
        $resultMap = collect($resultadosMes)->keyBy('label');
    @endphp

    <div class="co-page co-model">
        <section class="co-topbar">
            <div>
                <div class="co-title-row">
                    <h1>Centro Operacional</h1>
                    <span class="co-info">i</span>
                </div>
                <p>Tenha visão clara do que precisa de ação hoje.</p>
            </div>

            <div class="co-top-actions">
                <button type="button" class="co-toolbar-btn co-date-btn">
                    <i class="bi bi-calendar3 co-toolbar-icon"></i>
                    <span>Hoje, {{ $todayLabel }}</span>
                    <span class="co-chevron">⌄</span>
                </button>
                <button type="button" class="co-toolbar-btn">
                    <i class="bi bi-funnel co-toolbar-icon"></i>
                    <span>Filtros</span>
                </button>
                <button type="button" class="co-refresh-btn" onclick="window.location.reload()">
                    <i class="bi bi-arrow-clockwise"></i>
                    <span>Atualizar</span>
                    <span class="co-chevron">⌄</span>
                </button>
            </div>
        </section>

        <section class="co-kpi-grid">
            @foreach ($cards as $index => $card)
                @php
                    $tone = $card['tone'] ?? 'info';
                    $iconTone = $defaultIconClass[$index] ?? $tone;
                    $icon = $defaultIcons[$index] ?? '●';
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
                        <div>
                            <h2>Resolver Agora <small>(prioridade máxima)</small></h2>
                        </div>
                    </div>
                </header>

                <div class="co-action-list">
                    @forelse ($resolverAgora as $item)
                        <article class="co-action-row">
                            <span class="co-action-icon {{ $item['tone'] ?? 'info' }}"><i class="bi {{ ($item['tone'] ?? '') === 'danger' ? 'bi-file-earmark-pdf-fill' : (($item['tone'] ?? '') === 'success' ? 'bi-file-earmark-check-fill' : (($item['tone'] ?? '') === 'warning' ? 'bi-receipt-cutoff' : 'bi-file-earmark-text-fill')) }}"></i></span>
                            <div class="co-action-main">
                                <strong>{{ $item['title'] }}</strong>
                                <span>{{ $item['empresa'] }}</span>
                            </div>
                            <span class="co-pill {{ $item['tone'] ?? 'info' }}">{{ $item['status'] }}</span>
                            <span class="co-time {{ $item['tone'] ?? 'info' }}">{{ $item['due'] ? 'Hoje ' . $item['due'] : ($item['stopped_for'] ?? '-') }}</span>
                            <a class="co-row-arrow" href="{{ $item['url'] }}" aria-label="Abrir item">›</a>
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
                    <button type="button" class="active">Hoje</button>
                    <button type="button">7 dias</button>
                    <button type="button">15 dias</button>
                </div>

                <div class="co-deadline-list">
                    @forelse ($departmentRows as $row)
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
                        <div class="co-deadline-row">
                            <span class="co-dot green"></span>
                            <strong>Fiscais</strong>
                            <b>0</b>
                        </div>
                        <div class="co-deadline-row">
                            <span class="co-dot blue"></span>
                            <strong>Contábeis</strong>
                            <b>0</b>
                        </div>
                        <div class="co-deadline-row">
                            <span class="co-dot orange"></span>
                            <strong>Trabalhistas</strong>
                            <b>0</b>
                        </div>
                        <div class="co-deadline-row">
                            <span class="co-dot purple"></span>
                            <strong>Societárias</strong>
                            <b>0</b>
                        </div>
                    @endforelse
                </div>

                <div class="co-deadline-total">
                    <span>Total</span>
                    <strong>{{ number_format($departmentTotal, 0, ',', '.') }}</strong>
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
                                <small>{{ $row['total'] }} tarefas</small>
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
                    <div class="co-donut" aria-hidden="true"></div>
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
                            <div><span><i class="co-dot green"></i>Fiscal</span><strong>0 (0%)</strong></div>
                            <div><span><i class="co-dot blue"></i>Contábil</span><strong>0 (0%)</strong></div>
                            <div><span><i class="co-dot orange"></i>Departamento Pessoal</span><strong>0 (0%)</strong></div>
                            <div><span><i class="co-dot purple"></i>Societário</span><strong>0 (0%)</strong></div>
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
                        <article class="co-small-model-row">
                            <span class="co-small-icon danger"><i class="bi bi-building"></i></span>
                            <div>
                                <strong>{{ $item['empresa'] }}</strong>
                                <span>{{ $item['title'] }}</span>
                            </div>
                            <span class="co-mini-pill {{ $item['due'] ? 'warning' : 'muted' }}">{{ $item['due'] ? 'Hoje' : 'Aguardando' }}</span>
                        </article>
                    @empty
                        <div class="co-empty clean"><strong>Nada esperando aprovação.</strong></div>
                    @endforelse
                </div>
            </section>

            <section class="co-panel co-results-panel">
                <header class="co-panel-header">
                    <div class="co-heading-with-icon">
                        <span class="co-section-icon green"><i class="bi bi-trophy-fill"></i></span>
                        <h2>Resultados deste mês</h2>
                    </div>
                    <span class="co-party"><i class="bi bi-stars"></i></span>
                </header>

                <div class="co-result-grid-model">
                    @foreach ($resultadosMes as $result)
                        <div class="co-result-model-card">
                            <strong>{{ $result['value'] }}</strong>
                            <span>{{ $result['label'] }}</span>
                            <i class="bi bi-check-lg"></i>
                        </div>
                    @endforeach
                </div>

                <p class="co-success-message">Excelente! Seu escritório está no caminho certo. 🚀</p>
            </section>
        </section>
    </div>
</x-filament-panels::page>
