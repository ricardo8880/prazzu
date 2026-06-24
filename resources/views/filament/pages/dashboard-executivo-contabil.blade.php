<x-filament-panels::page>
    <link rel="stylesheet" href="{{ asset('css/dashboard-executivo-contabil.css') }}?v={{ file_exists(public_path('css/dashboard-executivo-contabil.css')) ? filemtime(public_path('css/dashboard-executivo-contabil.css')) : time() }}">

    @php
        $cards = $dashboard['cards'] ?? [];
        $health = $dashboard['health'] ?? ['score' => 0, 'label' => 'Sem dados', 'tone' => 'info', 'message' => 'Dados ainda não encontrados.'];
        $decisionBlocks = $dashboard['decision_blocks'] ?? [];
        $sections = $dashboard['sections'] ?? [];
        $quickActions = $dashboard['quick_actions'] ?? [];
        $updatedAt = $dashboard['updated_at'] ?? now()->format('d/m/Y H:i');
    @endphp

    <div class="dec-page" data-executive-accounting-dashboard>
        <section class="dec-hero dec-tone-{{ $health['tone'] ?? 'info' }}">
            <div class="dec-hero__content">
                <span class="dec-eyebrow">Gestão do escritório</span>
                <h1>Dashboard Executivo Contábil</h1>
                <p>Uma leitura simples para o sócio ou gestor entender clientes, prazos, documentos, equipe e financeiro sem abrir várias telas.</p>

                <div class="dec-hero__meta">
                    <span>Atualizado em {{ $updatedAt }}</span>
                    <span>{{ count($cards) }} indicadores principais</span>
                </div>
            </div>

            <aside class="dec-health">
                <span>Saúde da operação</span>
                <strong>{{ $health['score'] ?? 0 }}%</strong>
                <b>{{ $health['label'] ?? 'Sem dados' }}</b>
                <p>{{ $health['message'] ?? 'Acompanhe os indicadores para tomar decisões.' }}</p>
            </aside>
        </section>

        @if (! empty($quickActions))
            <nav class="dec-actions" aria-label="Ações rápidas do dashboard">
                @foreach ($quickActions as $action)
                    <a href="{{ $action['url'] }}">{{ $action['label'] }}</a>
                @endforeach
            </nav>
        @endif

        <section class="dec-kpis">
            @forelse ($cards as $card)
                <article class="dec-kpi dec-tone-{{ $card['tone'] ?? 'info' }}">
                    <span>{{ $card['label'] ?? '-' }}</span>
                    <strong>{{ $card['value'] ?? '0' }}</strong>
                    <p>{{ $card['hint'] ?? 'Indicador do escritório' }}</p>
                </article>
            @empty
                <article class="dec-empty dec-empty--wide">
                    <strong>Nenhum indicador encontrado.</strong>
                    <p>Quando houver dados no banco, o dashboard vai exibir os principais números do escritório aqui.</p>
                </article>
            @endforelse
        </section>

        <section class="dec-decision-grid">
            @foreach ($decisionBlocks as $block)
                <article class="dec-decision dec-tone-{{ $block['tone'] ?? 'info' }}">
                    <div>
                        <span>{{ $block['title'] ?? 'Decisão' }}</span>
                        <strong>{{ number_format((int) ($block['value'] ?? 0), 0, ',', '.') }}</strong>
                    </div>
                    <p>{{ $block['description'] ?? 'Analise os dados antes de decidir.' }}</p>
                    @if (! empty($block['url']))
                        <a href="{{ $block['url'] }}">{{ $block['action'] ?? 'Abrir tela' }}</a>
                    @endif
                </article>
            @endforeach
        </section>

        <section class="dec-command">
            <div>
                <strong>Encontrar informação</strong>
                <p>Busque por cliente, responsável, status, tarefa ou cobrança dentro deste dashboard.</p>
            </div>
            <div class="dec-command__controls">
                <input type="search" placeholder="Buscar no dashboard..." data-dec-search>
                <select data-dec-tone>
                    <option value="all">Todos os status</option>
                    <option value="danger">Crítico</option>
                    <option value="warning">Atenção</option>
                    <option value="success">Saudável</option>
                    <option value="info">Informativo</option>
                </select>
            </div>
        </section>

        <div class="dec-sections">
            @forelse ($sections as $section)
                <section class="dec-card">
                    <header>
                        <div>
                            <h2>{{ $section['title'] ?? 'Seção' }}</h2>
                            <p>{{ $section['description'] ?? 'Dados consolidados do escritório.' }}</p>
                        </div>
                        <span>{{ count($section['items'] ?? []) }}</span>
                    </header>

                    <div class="dec-list">
                        @forelse (($section['items'] ?? []) as $item)
                            <article class="dec-item dec-tone-{{ $item['tone'] ?? 'info' }}" data-dec-item data-tone="{{ $item['tone'] ?? 'info' }}" data-search="{{ \Illuminate\Support\Str::lower(($item['title'] ?? '') . ' ' . ($item['status'] ?? '') . ' ' . ($item['meta'] ?? '') . ' ' . ($item['description'] ?? '')) }}">
                                <div class="dec-item__top">
                                    <h3>{{ $item['title'] ?? 'Sem título' }}</h3>
                                    <span>{{ $item['status'] ?? '-' }}</span>
                                </div>
                                <small>{{ $item['meta'] ?? 'Sem contexto' }}</small>
                                <p>{{ $item['description'] ?? 'Sem descrição cadastrada.' }}</p>
                                @if (! empty($item['url']))
                                    <a href="{{ $item['url'] }}">Abrir origem</a>
                                @endif
                            </article>
                        @empty
                            <div class="dec-empty">
                                <strong>Nada crítico nesta fila.</strong>
                                <p>Quando houver risco, atraso ou pendência, os registros aparecerão aqui de forma priorizada.</p>
                            </div>
                        @endforelse
                    </div>
                </section>
            @empty
                <section class="dec-card dec-card--wide">
                    <div class="dec-empty">
                        <strong>Nenhuma seção disponível.</strong>
                        <p>O dashboard está ativo, mas ainda não encontrou dados suficientes no banco.</p>
                    </div>
                </section>
            @endforelse
        </div>

        <div class="dec-no-results" data-dec-empty hidden>
            <strong>Nenhum resultado encontrado.</strong>
            <p>Limpe a busca ou altere o status para visualizar os dados do dashboard.</p>
        </div>
    </div>

    <script>
        (() => {
            const root = document.querySelector('[data-executive-accounting-dashboard]');
            if (! root) return;

            const search = root.querySelector('[data-dec-search]');
            const tone = root.querySelector('[data-dec-tone]');
            const items = Array.from(root.querySelectorAll('[data-dec-item]'));
            const empty = root.querySelector('[data-dec-empty]');

            const normalize = (value) => (value || '').toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');

            const apply = () => {
                const term = normalize(search?.value || '');
                const selectedTone = tone?.value || 'all';
                let visible = 0;

                items.forEach((item) => {
                    const text = normalize(item.dataset.search || item.textContent || '');
                    const itemTone = item.dataset.tone || 'info';
                    const show = (! term || text.includes(term)) && (selectedTone === 'all' || itemTone === selectedTone);
                    item.hidden = ! show;
                    if (show) visible++;
                });

                if (empty) empty.hidden = visible > 0 || items.length === 0;
            };

            search?.addEventListener('input', apply);
            tone?.addEventListener('change', apply);
        })();
    </script>
</x-filament-panels::page>
