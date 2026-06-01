<x-filament-panels::page>
    <link rel="stylesheet" href="{{ asset('css/relatorios-dashboard.css') }}">

    <div class="rd-page">
        <section class="rd-hero">
            <div>
                <span class="rd-kicker">RELATÓRIOS • CONFIGURÁVEL</span>
                <h1>{{ $dashboard['title'] }}</h1>
                <p>{{ $dashboard['subtitle'] }}</p>
            </div>

            <div class="rd-actions">
                @if ($dashboard['actions']['create'])
                    <a href="{{ $dashboard['actions']['create'] }}">+ Criar widget</a>
                @endif
                @if ($dashboard['actions']['manage'])
                    <a href="{{ $dashboard['actions']['manage'] }}">Gerenciar widgets</a>
                @endif
                @if ($dashboard['actions']['dashboards'])
                    <a href="{{ $dashboard['actions']['dashboards'] }}">Ver dashboards</a>
                @endif
            </div>
        </section>

        <section class="rd-guide rd-tone-info">
            <strong>Como usar:</strong>
            <span>Crie widgets por fonte real do sistema. Para usuário comum, o filtro “Eu” é aplicado pela permissão dos itens vinculados ao responsável.</span>
        </section>

        <section class="rd-panel">
            <div class="rd-panel-title">
                <div>
                    <h2>Fontes prontas para widgets</h2>
                    <p>Use essas fontes para montar uma visão por função: aprovação, cobrança, carga, vencidos e bloqueios.</p>
                </div>
            </div>
            <div class="rd-source-grid">
                @foreach ($dashboard['fontes'] as $key => $label)
                    <span><strong>{{ $label }}</strong><small>{{ $key }}</small></span>
                @endforeach
            </div>
        </section>

        <section class="rd-config-grid">
            @forelse ($dashboard['widgets'] as $widget)
                <article class="rd-panel rd-widget rd-widget-{{ str_replace('/', '-', $widget['largura']) }}">
                    <div class="rd-panel-title">
                        <div>
                            <h2>{{ $widget['titulo'] }}</h2>
                            <p>{{ $widget['fonte'] }} • {{ ucfirst($widget['tipo']) }}</p>
                        </div>
                        @if ($widget['edit_url'])
                            <a href="{{ $widget['edit_url'] }}">Editar</a>
                        @endif
                    </div>

                    @if ($widget['tipo'] === 'card')
                        <div class="rd-config-value">{{ $widget['valor'] }}</div>
                    @elseif ($widget['tipo'] === 'tabela')
                        <div class="rd-list rd-compact-list">
                            @forelse ($widget['tabela'] as $row)
                                <div class="rd-table-row">
                                    <strong>{{ $row['titulo'] ?? '-' }}</strong>
                                    <span>{{ $row['status'] ?? '-' }}</span>
                                    <small>{{ $row['data_vencimento'] ?? '-' }}</small>
                                </div>
                            @empty
                                <div class="rd-empty">Sem registros para este widget.</div>
                            @endforelse
                        </div>
                    @else
                        <div class="rd-bars">
                            @forelse ($widget['grafico'] as $row)
                                <div class="rd-bar-row">
                                    <div><strong>{{ $row['label'] ?? '-' }}</strong><span>{{ $row['valor'] ?? 0 }}</span></div>
                                    <div class="rd-bar"><span style="width: {{ min(100, ((int) ($row['valor'] ?? 0)) * 10) }}%"></span></div>
                                </div>
                            @empty
                                <div class="rd-empty">Sem dados para este gráfico.</div>
                            @endforelse
                        </div>
                    @endif
                </article>
            @empty
                <article class="rd-panel">
                    <div class="rd-empty rd-empty-big">
                        Nenhum widget configurado ainda. Clique em “Criar widget” e escolha uma fonte real do sistema.
                    </div>
                </article>
            @endforelse
        </section>
    </div>
</x-filament-panels::page>
