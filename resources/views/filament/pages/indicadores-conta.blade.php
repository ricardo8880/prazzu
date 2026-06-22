<x-filament-panels::page>
    <link rel="stylesheet" href="{{ asset('css/tarefas-qa-standard.css') }}?v=20260513-lote7-visual">
    <link rel="stylesheet" href="{{ asset('css/indicadores-conta.css') }}?v={{ file_exists(public_path('css/indicadores-conta.css')) ? filemtime(public_path('css/indicadores-conta.css')) : time() }}">

    <div class="account-indicators-page prazzu-docs-page">
        <section class="account-indicators-hero prazzu-hero">
            <div>
                <span class="account-indicators-kicker">Painel gerencial</span>
                <h1>Indicadores da Conta</h1>
                <p>Uma visão simples para sócios e gestores acompanharem o tamanho da operação, a equipe ativa, o volume de documentos e o uso atual da conta.</p>
            </div>

            <div class="account-indicators-hero-card">
                <span>Atualizado em</span>
                <strong>{{ $summary['updated_at'] ?? now()->format('d/m/Y H:i') }}</strong>
                <p>{{ $summary['scope'] ?? 'Visão da conta' }}</p>
                <small>{{ $summary['cache_hint'] ?? 'Atualização automática' }}</small>
            </div>
        </section>

        <section class="account-indicators-cards" aria-label="Indicadores principais da conta">
            @foreach($cards as $card)
                <article class="account-indicators-card {{ $card['tone'] }}">
                    <div class="account-indicators-card-icon">
                        <i class="bi {{ $card['icon'] }}"></i>
                    </div>
                    <div>
                        <span>{{ $card['label'] }}</span>
                        <strong>{{ $card['value'] }}</strong>
                        <p>{{ $card['description'] }}</p>
                    </div>
                </article>
            @endforeach
        </section>

        <section class="account-indicators-usage account-indicators-panel prazzu-card" aria-label="Uso do ambiente">
            <div class="account-indicators-panel-header">
                <div>
                    <span class="account-indicators-kicker prazzu-kicker">Uso do ambiente</span>
                    <h2>Armazenamento e banco</h2>
                </div>
                <strong>{{ $usage['storage_percent'] === null ? 'Local' : number_format((float) $usage['storage_percent'], 1, ',', '.') . '%' }}</strong>
            </div>

            <div class="account-indicators-progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $usage['storage_percent'] ?? 0 }}">
                <span style="width: {{ $usage['storage_bar_width'] ?? 0 }}%"></span>
            </div>

            <div class="account-indicators-usage-grid">
                <div>
                    <span>Espaço utilizado</span>
                    <strong>{{ $usage['storage_used'] ?? '0 B' }}</strong>
                    <p>Limite: {{ $usage['storage_limit'] ?? 'Sem limite configurado' }}</p>
                </div>
                <div>
                    <span>Banco de dados</span>
                    <strong>{{ $usage['database_size'] ?? 'Indisponível' }}</strong>
                    <p>Volume atual utilizado pela conta</p>
                </div>
            </div>
        </section>

        <section class="account-indicators-grid">
            <article class="account-indicators-panel prazzu-card">
                <div class="account-indicators-panel-header">
                    <div>
                        <span class="account-indicators-kicker">Resumo da conta</span>
                        <h2>Visão rápida da operação</h2>
                    </div>
                </div>

                <div class="account-indicators-list">
                    @foreach($summaryItems as $item)
                        <div class="account-indicators-row {{ $item['tone'] }}">
                            <span></span>
                            <div>
                                <strong>{{ $item['title'] }}</strong>
                                <p>{{ $item['description'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </article>

            <aside class="account-indicators-panel account-indicators-panel-muted prazzu-card">
                <span class="account-indicators-kicker">Atenção do gestor</span>
                <h2>O que observar</h2>

                <div class="account-indicators-list">
                    @foreach($managerNotes as $note)
                        <div class="account-indicators-row {{ $note['tone'] }}">
                            <span></span>
                            <div>
                                <strong>{{ $note['title'] }}</strong>
                                <p>{{ $note['description'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </aside>
        </section>
    </div>
</x-filament-panels::page>
