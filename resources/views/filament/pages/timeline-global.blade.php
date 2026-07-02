<x-filament-panels::page>

    @php
        $filters = $data['filters'] ?? [];
        $filterOptions = $data['filterOptions'] ?? [];
        $stats = $data['stats'] ?? [];
        $groups = $data['groups'] ?? [];
        $criticalEvents = $data['criticalEvents'] ?? [];
        $pendingApprovals = $data['pendingApprovals'] ?? [];
        $withoutEvidence = $data['withoutEvidence'] ?? [];
        $integrityAlerts = $data['integrityAlerts'] ?? [];
        $sourceSummary = $data['sourceSummary'] ?? [];
        $userSummary = $data['userSummary'] ?? [];
        $emptySources = $data['emptySources'] ?? [];
    @endphp

    <div class="prazzu-audit-timeline">
        <section class="prazzu-audit-hero">
            <div>
                <span>AUDITORIA</span>
                <h1>Timeline Global</h1>
                <p>Trilha única com auditoria, aprovações, comentários, anexos e movimentações operacionais para o cliente enxergar o que aconteceu, quem mexeu e o que precisa de ação.</p>
            </div>
            <div class="prazzu-audit-hero-actions">
                <a href="{{ request()->fullUrlWithQuery(['risk' => 'high']) }}">Ver riscos altos</a>
                <a href="{{ request()->fullUrlWithQuery(['type' => 'aprovacao']) }}">Ver aprovações</a>
                <a href="{{ request()->url() }}">Limpar filtros</a>
            </div>
        </section>

        <form method="GET" class="prazzu-audit-filters">
            <label>
                <span>Período</span>
                <select name="period">
                    @foreach (($filterOptions['periods'] ?? []) as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['period'] ?? '7') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                <span>Origem</span>
                <select name="type">
                    @foreach (($filterOptions['types'] ?? []) as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['type'] ?? 'all') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                <span>Risco</span>
                <select name="risk">
                    @foreach (($filterOptions['risks'] ?? []) as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['risk'] ?? 'all') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label class="prazzu-audit-search">
                <span>Busca global</span>
                <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Buscar por usuário, item, empresa, ação ou descrição">
            </label>
            <button type="submit">Aplicar filtros</button>
        </form>

        <section class="prazzu-audit-stats">
            @foreach ($stats as $stat)
                <article class="{{ $stat['tone'] ?? 'info' }}">
                    <span>{{ $stat['label'] ?? '-' }}</span>
                    <strong>{{ $stat['value'] ?? 0 }}</strong>
                    <small>{{ $stat['hint'] ?? '' }}</small>
                </article>
            @endforeach
        </section>

        @if (! empty($integrityAlerts))
            <section class="prazzu-audit-alerts">
                @foreach ($integrityAlerts as $alert)
                    <article class="{{ $alert['tone'] ?? 'info' }}">
                        <strong>{{ $alert['title'] ?? 'Alerta' }}</strong>
                        <p>{{ $alert['description'] ?? '' }}</p>
                    </article>
                @endforeach
            </section>
        @endif

        <div class="prazzu-audit-layout">
            <main class="prazzu-audit-main">
                <section class="prazzu-audit-card">
                    <div class="prazzu-audit-card-header">
                        <div>
                            <h2>Eventos filtrados</h2>
                            <p>Feed consolidado e ordenado por data, usando somente dados reais já gravados no sistema.</p>
                        </div>
                    </div>

                    @forelse ($groups as $group)
                        <div class="prazzu-audit-day">
                            <h3>{{ $group['label'] ?? 'Sem data' }}</h3>
                            <div class="prazzu-audit-feed">
                                @foreach (($group['items'] ?? []) as $event)
                                    <article class="{{ $event['tone'] ?? 'info' }}">
                                        <div class="prazzu-audit-dot"></div>
                                        <div class="prazzu-audit-event-body">
                                            <div class="prazzu-audit-event-top">
                                                <div>
                                                    <h4>{{ $event['title'] ?? 'Evento' }}</h4>
                                                    <small>{{ $event['source'] ?? 'Sistema' }} • {{ $event['meta'] ?? 'Sem contexto' }}</small>
                                                </div>
                                                <div class="prazzu-audit-badges">
                                                    <span>{{ $event['status'] ?? 'Evento' }}</span>
                                                    <span class="risk">Risco {{ $event['risk_label'] ?? 'Baixo' }}</span>
                                                </div>
                                            </div>
                                            <p>{{ $event['description'] ?? 'Sem descrição.' }}</p>
                                            <div class="prazzu-audit-event-footer">
                                                <span>Responsável: {{ $event['actor'] ?? 'Sistema' }}</span>
                                                <span>{{ $event['date_label'] ?? '-' }} • {{ $event['relative_date'] ?? '-' }}</span>
                                                @if (! empty($event['ip']))
                                                    <span>IP: {{ $event['ip'] }}</span>
                                                @endif
                                            </div>
                                            @if (! empty($event['url']))
                                                <a class="prazzu-audit-link" href="{{ $event['url'] }}">Abrir origem</a>
                                            @endif
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div class="prazzu-audit-empty">
                            <strong>Nenhum evento encontrado com os filtros atuais.</strong>
                            <p>Altere período, origem, risco ou busca para ampliar a consulta.</p>
                        </div>
                    @endforelse
                </section>
            </main>

            <aside class="prazzu-audit-side">
                <section class="prazzu-audit-card">
                    <div class="prazzu-audit-card-header compact">
                        <div>
                            <h2>Prioridade de auditoria</h2>
                            <p>O que merece conferência primeiro.</p>
                        </div>
                    </div>
                    <div class="prazzu-audit-mini-list">
                        @forelse ($criticalEvents as $event)
                            <article class="danger">
                                <strong>{{ $event['title'] ?? 'Evento crítico' }}</strong>
                                <span>{{ $event['source'] ?? 'Origem' }} • {{ $event['date_label'] ?? '-' }}</span>
                                <p>{{ $event['description'] ?? '' }}</p>
                                @if (! empty($event['url']))
                                    <a href="{{ $event['url'] }}">Abrir</a>
                                @endif
                            </article>
                        @empty
                            <div class="prazzu-audit-empty small">Nenhum risco alto no período.</div>
                        @endforelse
                    </div>
                </section>

                <section class="prazzu-audit-card">
                    <div class="prazzu-audit-card-header compact">
                        <div>
                            <h2>Aprovações pendentes</h2>
                            <p>Decisões abertas que travam a operação.</p>
                        </div>
                    </div>
                    <div class="prazzu-audit-mini-list">
                        @forelse ($pendingApprovals as $item)
                            <article class="warning">
                                <strong>{{ $item['title'] ?? 'Aprovação' }}</strong>
                                <span>{{ $item['meta'] ?? 'Sem empresa' }} • {{ $item['relative_date'] ?? '-' }}</span>
                                @if (! empty($item['url']))
                                    <a href="{{ $item['url'] }}">Decidir</a>
                                @endif
                            </article>
                        @empty
                            <div class="prazzu-audit-empty small">Nenhuma aprovação pendente.</div>
                        @endforelse
                    </div>
                </section>

                <section class="prazzu-audit-card">
                    <div class="prazzu-audit-card-header compact">
                        <div>
                            <h2>Sem evidência recente</h2>
                            <p>Itens abertos sem movimentação auditável recente.</p>
                        </div>
                    </div>
                    <div class="prazzu-audit-mini-list">
                        @forelse ($withoutEvidence as $item)
                            <article class="info">
                                <strong>{{ $item['title'] ?? 'Item' }}</strong>
                                <span>{{ $item['meta'] ?? 'Sem empresa' }} • {{ $item['date_label'] ?? '-' }}</span>
                                @if (! empty($item['url']))
                                    <a href="{{ $item['url'] }}">Abrir</a>
                                @endif
                            </article>
                        @empty
                            <div class="prazzu-audit-empty small">Nenhum item sem evidência recente.</div>
                        @endforelse
                    </div>
                </section>

                <section class="prazzu-audit-card">
                    <div class="prazzu-audit-card-header compact">
                        <div>
                            <h2>Origem dos eventos</h2>
                            <p>Ajuda a identificar se a auditoria está cobrindo tudo.</p>
                        </div>
                    </div>
                    <div class="prazzu-audit-summary">
                        @forelse ($sourceSummary as $source)
                            <div>
                                <span>{{ $source['label'] ?? '-' }}</span>
                                <strong>{{ $source['value'] ?? 0 }}</strong>
                                <small>{{ $source['risk'] ?? 0 }} risco alto</small>
                            </div>
                        @empty
                            <div class="prazzu-audit-empty small">Sem origem registrada.</div>
                        @endforelse
                    </div>
                </section>

                <section class="prazzu-audit-card">
                    <div class="prazzu-audit-card-header compact">
                        <div>
                            <h2>Usuários mais ativos</h2>
                            <p>Volume de ações por responsável/usuário.</p>
                        </div>
                    </div>
                    <div class="prazzu-audit-summary">
                        @forelse ($userSummary as $user)
                            <div>
                                <span>{{ $user['label'] ?? '-' }}</span>
                                <strong>{{ $user['value'] ?? 0 }}</strong>
                                <small>Último: {{ $user['last'] ?? '-' }}</small>
                            </div>
                        @empty
                            <div class="prazzu-audit-empty small">Sem usuários no período.</div>
                        @endforelse
                    </div>
                </section>

                @if (! empty($emptySources))
                    <section class="prazzu-audit-card">
                        <div class="prazzu-audit-card-header compact">
                            <div>
                                <h2>Fontes ausentes</h2>
                                <p>Tabelas não encontradas no banco atual.</p>
                            </div>
                        </div>
                        <div class="prazzu-audit-tags">
                            @foreach ($emptySources as $source)
                                <span>{{ $source }}</span>
                            @endforeach
                        </div>
                    </section>
                @endif
            </aside>
        </div>
    </div>
</x-filament-panels::page>
