<x-filament-panels::page>

    @php
        $cards = $data['cards'] ?? [];
        $sections = $data['sections'] ?? [];
        $quickActions = $data['quickActions'] ?? [];
        $workflow = $data['workflow'] ?? [];
        $timeline = $data['timeline'] ?? [];
        $whiteLabel = \App\Support\WhiteLabelSettings::make();
        $brandName = $whiteLabel->displayName();
        $enterpriseLabel = strtoupper($whiteLabel->enterpriseLabel());
    @endphp

    <div class="prazzu-maturity-page">
        <section class="prazzu-maturity-hero">
            <div>
                <span>{{ $data['group'] ?? $enterpriseLabel }}</span>
                <h1>{{ $data['title'] ?? 'Módulo Enterprise' }}</h1>
                <p>{{ $data['subtitle'] ?? 'Central operacional criada para completar a maturidade interna do ' . $brandName . '.' }}</p>
            </div>

            @if (! empty($quickActions))
                <div class="prazzu-maturity-actions">
                    @foreach ($quickActions as $action)
                        @if (! empty($action['url']))
                            <a href="{{ $action['url'] }}">{{ $action['label'] }}</a>
                        @endif
                    @endforeach
                </div>
            @endif
        </section>

        @if (! empty($cards))
            <section class="prazzu-maturity-stats">
                @foreach ($cards as $card)
                    <article class="prazzu-maturity-stat {{ $card['tone'] ?? 'info' }}">
                        <span>{{ $card['label'] ?? '-' }}</span>
                        <strong>{{ $card['value'] ?? 0 }}</strong>
                        <small>{{ $card['hint'] ?? 'Indicador operacional' }}</small>
                    </article>
                @endforeach
            </section>
        @endif

        @if (! empty($workflow))
            <section class="prazzu-maturity-card">
                <div class="prazzu-maturity-card-header">
                    <div>
                        <h2>Workflow documental</h2>
                        <p>Etapas internas para transformar documento solto em governança completa.</p>
                    </div>
                </div>

                <div class="prazzu-workflow-line">
                    @foreach ($workflow as $step)
                        <article>
                            <span>{{ $loop->iteration }}</span>
                            <h3>{{ $step['step'] ?? '-' }}</h3>
                            <strong>{{ number_format($step['count'] ?? 0, 0, ',', '.') }}</strong>
                            <p>{{ $step['description'] ?? '' }}</p>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        @if (! empty($timeline))
            <section class="prazzu-maturity-card">
                <div class="prazzu-maturity-card-header">
                    <div>
                        <h2>Timeline consolidada</h2>
                        <p>Eventos, comentários, aprovações e evidências em ordem cronológica.</p>
                    </div>
                </div>

                <div class="prazzu-timeline-list">
                    @foreach ($timeline as $event)
                        <article class="{{ $event['tone'] ?? 'info' }}">
                            <div class="prazzu-timeline-dot"></div>
                            <div>
                                <div class="prazzu-maturity-item-top">
                                    <h3>{{ $event['title'] ?? '-' }}</h3>
                                    <span>{{ $event['status'] ?? '-' }}</span>
                                </div>
                                <small>{{ $event['meta'] ?? '-' }} @if(!empty($event['date'])) • {{ $event['date'] }} @endif</small>
                                <p>{{ $event['description'] ?? 'Sem descrição.' }}</p>
                                @if (! empty($event['url']))
                                    <a class="prazzu-open-link" href="{{ $event['url'] }}">Abrir origem</a>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        <div class="prazzu-maturity-sections">
            @forelse ($sections as $section)
                <section class="prazzu-maturity-card">
                    <div class="prazzu-maturity-card-header">
                        <div>
                            <h2>{{ $section['title'] ?? 'Seção' }}</h2>
                            <p>{{ $section['description'] ?? 'Dados consolidados do módulo.' }}</p>
                        </div>
                    </div>

                    <div class="prazzu-maturity-items">
                        @forelse (($section['items'] ?? []) as $item)
                            <article class="prazzu-maturity-item {{ $item['tone'] ?? 'info' }}">
                                <div class="prazzu-maturity-item-top">
                                    <h3>{{ $item['title'] ?? 'Sem título' }}</h3>
                                    <span>{{ $item['status'] ?? '-' }}</span>
                                </div>
                                <small>{{ $item['meta'] ?? '-' }} @if(!empty($item['date'])) • {{ $item['date'] }} @endif</small>
                                <p>{{ $item['description'] ?? 'Sem descrição cadastrada.' }}</p>
                                @if (! empty($item['url']))
                                    <a class="prazzu-open-link" href="{{ $item['url'] }}">Abrir item</a>
                                @endif
                            </article>
                        @empty
                            <div class="prazzu-maturity-empty">
                                <strong>Nenhum registro crítico encontrado.</strong>
                                <p>Quando houver dados nesta categoria, eles aparecerão aqui com prioridade, status e ação direta.</p>
                            </div>
                        @endforelse
                    </div>
                </section>
            @empty
                <section class="prazzu-maturity-card">
                    <div class="prazzu-maturity-empty">
                        <strong>Nenhum dado para exibir.</strong>
                        <p>O módulo está pronto, mas ainda não encontrou registros no banco.</p>
                    </div>
                </section>
            @endforelse
        </div>
    </div>
</x-filament-panels::page>
