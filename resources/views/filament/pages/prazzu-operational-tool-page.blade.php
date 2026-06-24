<x-filament-panels::page>
    <link rel="stylesheet" href="{{ asset('css/prazzu-operational-tool.css') }}?v={{ file_exists(public_path('css/prazzu-operational-tool.css')) ? filemtime(public_path('css/prazzu-operational-tool.css')) : time() }}">

    @php
        $cards = $data['cards'] ?? [];
        $sections = $data['sections'] ?? [];
        $quickActions = $data['quickActions'] ?? [];
        $workflow = $data['workflow'] ?? [];
        $timeline = $data['timeline'] ?? [];
        $totalItems = collect($sections)->sum(fn ($section) => count($section['items'] ?? [])) + count($timeline);
        $criticalItems = collect($sections)->flatMap(fn ($section) => $section['items'] ?? [])->filter(fn ($item) => ($item['tone'] ?? null) === 'danger')->count();
        $warningItems = collect($sections)->flatMap(fn ($section) => $section['items'] ?? [])->filter(fn ($item) => ($item['tone'] ?? null) === 'warning')->count();
        $successItems = collect($sections)->flatMap(fn ($section) => $section['items'] ?? [])->filter(fn ($item) => ($item['tone'] ?? null) === 'success')->count();
        $whiteLabel = \App\Support\WhiteLabelSettings::make();
        $enterpriseLabel = strtoupper($whiteLabel->enterpriseLabel());
    @endphp

    <div class="prazzu-tool-page" data-prazzu-tool-page>
        <section class="prazzu-tool-hero">
            <div class="prazzu-tool-hero__content">
                <span class="prazzu-tool-eyebrow">{{ $data['group'] ?? $enterpriseLabel }}</span>
                <h1>{{ $data['title'] ?? 'Central operacional' }}</h1>
                <p>{{ $data['subtitle'] ?? 'Ferramenta operacional conectada aos dados reais do sistema.' }}</p>

                <div class="prazzu-tool-hero__pulse">
                    <span>{{ number_format($totalItems, 0, ',', '.') }} registro(s) monitorado(s)</span>
                    <span>{{ number_format($criticalItems, 0, ',', '.') }} crítico(s)</span>
                    <span>{{ number_format($warningItems, 0, ',', '.') }} em atenção</span>
                </div>
            </div>

            <div class="prazzu-tool-hero__actions">
                @forelse ($quickActions as $action)
                    @if (! empty($action['url']))
                        <a href="{{ $action['url'] }}">{{ $action['label'] }}</a>
                    @endif
                @empty
                    <span>Nenhuma ação rápida disponível</span>
                @endforelse
            </div>
        </section>

        <section class="prazzu-tool-command">
            <div>
                <strong>Comando rápido da página</strong>
                <p>Filtre os dados desta ferramenta sem sair da tela. A busca funciona em título, empresa, status, descrição e datas.</p>
            </div>
            <div class="prazzu-tool-command__controls">
                <input type="search" placeholder="Buscar nesta ferramenta..." data-tool-search>
                <select data-tool-tone>
                    <option value="all">Todos os status</option>
                    <option value="danger">Crítico</option>
                    <option value="warning">Atenção</option>
                    <option value="success">Resolvido/ativo</option>
                    <option value="info">Informativo</option>
                </select>
            </div>
        </section>

        @if (! empty($cards))
            <section class="prazzu-tool-stats">
                @foreach ($cards as $card)
                    <article class="prazzu-tool-stat {{ $card['tone'] ?? 'info' }}">
                        <span>{{ $card['label'] ?? '-' }}</span>
                        <strong>{{ $card['value'] ?? 0 }}</strong>
                        <small>{{ $card['hint'] ?? 'Indicador operacional' }}</small>
                    </article>
                @endforeach
            </section>
        @endif

        <section class="prazzu-tool-board">
            <article class="prazzu-tool-board__card danger">
                <span>Prioridade agora</span>
                <strong>{{ number_format($criticalItems, 0, ',', '.') }}</strong>
                <p>Itens críticos encontrados nas seções abaixo.</p>
            </article>
            <article class="prazzu-tool-board__card warning">
                <span>Precisa acompanhar</span>
                <strong>{{ number_format($warningItems, 0, ',', '.') }}</strong>
                <p>Registros em atenção para evitar atraso ou retrabalho.</p>
            </article>
            <article class="prazzu-tool-board__card success">
                <span>Base saudável</span>
                <strong>{{ number_format($successItems, 0, ',', '.') }}</strong>
                <p>Registros ativos, concluídos ou bem configurados.</p>
            </article>
            <article class="prazzu-tool-board__card info">
                <span>Atalhos úteis</span>
                <strong>{{ count($quickActions) }}</strong>
                <p>Ações diretas para continuar o trabalho no cadastro correto.</p>
            </article>
        </section>

        @if (! empty($workflow))
            <section class="prazzu-tool-card prazzu-tool-card--wide">
                <div class="prazzu-tool-card__header">
                    <div>
                        <h2>{{ $data['workflowTitle'] ?? 'Workflow operacional' }}</h2>
                        <p>{{ $data['workflowDescription'] ?? 'Etapas calculadas com base nos registros existentes para o usuário saber onde agir.' }}</p>
                    </div>
                </div>

                <div class="prazzu-tool-workflow">
                    @foreach ($workflow as $step)
                        <article data-tool-item data-tone="info" data-search="{{ \Illuminate\Support\Str::lower(($step['step'] ?? '') . ' ' . ($step['description'] ?? '') . ' ' . ($step['count'] ?? '')) }}">
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
            <section class="prazzu-tool-card prazzu-tool-card--wide">
                <div class="prazzu-tool-card__header">
                    <div>
                        <h2>Timeline consolidada</h2>
                        <p>Histórico recente em ordem cronológica para auditoria e acompanhamento operacional.</p>
                    </div>
                </div>

                <div class="prazzu-tool-timeline">
                    @foreach ($timeline as $event)
                        <article class="{{ $event['tone'] ?? 'info' }}" data-tool-item data-tone="{{ $event['tone'] ?? 'info' }}" data-search="{{ \Illuminate\Support\Str::lower(($event['title'] ?? '') . ' ' . ($event['status'] ?? '') . ' ' . ($event['meta'] ?? '') . ' ' . ($event['description'] ?? '') . ' ' . ($event['date'] ?? '')) }}">
                            <div class="prazzu-tool-timeline__dot"></div>
                            <div>
                                <div class="prazzu-tool-item__top">
                                    <h3>{{ $event['title'] ?? '-' }}</h3>
                                    <span>{{ $event['status'] ?? '-' }}</span>
                                </div>
                                <small>{{ $event['meta'] ?? '-' }} @if(!empty($event['date'])) • {{ $event['date'] }} @endif</small>
                                <p>{{ $event['description'] ?? 'Sem descrição.' }}</p>
                                @if (! empty($event['url']))
                                    <a class="prazzu-tool-link" href="{{ $event['url'] }}">Abrir origem</a>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        <div class="prazzu-tool-sections">
            @forelse ($sections as $section)
                <section class="prazzu-tool-card">
                    <div class="prazzu-tool-card__header">
                        <div>
                            <h2>{{ $section['title'] ?? 'Seção' }}</h2>
                            <p>{{ $section['description'] ?? 'Dados consolidados do módulo.' }}</p>
                        </div>
                        <span>{{ count($section['items'] ?? []) }}</span>
                    </div>

                    <div class="prazzu-tool-items">
                        @forelse (($section['items'] ?? []) as $item)
                            <article class="prazzu-tool-item {{ $item['tone'] ?? 'info' }}" data-tool-item data-tone="{{ $item['tone'] ?? 'info' }}" data-search="{{ \Illuminate\Support\Str::lower(($item['title'] ?? '') . ' ' . ($item['status'] ?? '') . ' ' . ($item['meta'] ?? '') . ' ' . ($item['description'] ?? '') . ' ' . ($item['date'] ?? '')) }}">
                                <div class="prazzu-tool-item__top">
                                    <h3>{{ $item['title'] ?? 'Sem título' }}</h3>
                                    <span>{{ $item['status'] ?? '-' }}</span>
                                </div>
                                <small>{{ $item['meta'] ?? '-' }} @if(!empty($item['date'])) • {{ $item['date'] }} @endif</small>
                                <p>{{ $item['description'] ?? 'Sem descrição cadastrada.' }}</p>
                                @if (! empty($item['url']))
                                    <a class="prazzu-tool-link" href="{{ $item['url'] }}">Resolver / abrir item</a>
                                @endif
                            </article>
                        @empty
                            <div class="prazzu-tool-empty">
                                <strong>Nenhum registro encontrado nesta fila.</strong>
                                <p>Quando existirem dados reais nesta categoria, eles aparecerão aqui com prioridade, status e ação direta.</p>
                            </div>
                        @endforelse
                    </div>
                </section>
            @empty
                <section class="prazzu-tool-card prazzu-tool-card--wide">
                    <div class="prazzu-tool-empty">
                        <strong>Nenhum dado para exibir.</strong>
                        <p>A ferramenta está ativa, mas ainda não encontrou registros compatíveis no banco.</p>
                    </div>
                </section>
            @endforelse
        </div>

        <div class="prazzu-tool-no-results" data-tool-empty hidden>
            <strong>Nenhum resultado para o filtro atual.</strong>
            <p>Limpe a busca ou altere o status para visualizar os registros desta ferramenta.</p>
        </div>
    </div>

    <script>
        (() => {
            const root = document.querySelector('[data-prazzu-tool-page]');
            if (! root) return;

            const search = root.querySelector('[data-tool-search]');
            const tone = root.querySelector('[data-tool-tone]');
            const items = Array.from(root.querySelectorAll('[data-tool-item]'));
            const empty = root.querySelector('[data-tool-empty]');

            const normalize = (value) => (value || '').toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');

            const apply = () => {
                const term = normalize(search?.value || '');
                const selectedTone = tone?.value || 'all';
                let visible = 0;

                items.forEach((item) => {
                    const text = normalize(item.dataset.search || item.textContent || '');
                    const itemTone = item.dataset.tone || 'info';
                    const matchesText = ! term || text.includes(term);
                    const matchesTone = selectedTone === 'all' || itemTone === selectedTone;
                    const show = matchesText && matchesTone;
                    item.hidden = ! show;
                    if (show) visible++;
                });

                if (empty) empty.hidden = visible > 0;
            };

            search?.addEventListener('input', apply);
            tone?.addEventListener('change', apply);
        })();
    </script>
</x-filament-panels::page>
