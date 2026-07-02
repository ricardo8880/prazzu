<x-filament-panels::page>
    <div class="rd-page rd-profile-page">
        <section class="rd-hero rd-profile-hero">
            <div>
                <span class="rd-kicker">RELATÓRIOS • DASHBOARDS POR PERFIL</span>
                <h1>{{ $dashboard['header']['title'] }}</h1>
                <p>{{ $dashboard['header']['subtitle'] }}</p>
            </div>

            <div class="rd-actions">
                @if ($dashboard['actions']['nova_demanda'])
                    <a href="{{ $dashboard['actions']['nova_demanda'] }}">+ Nova demanda</a>
                @endif
                @if ($dashboard['actions']['tarefas'])
                    <a href="{{ $dashboard['actions']['tarefas'] }}">Ver tarefas</a>
                @endif
                @if ($dashboard['actions']['configurar'])
                    <a href="{{ $dashboard['actions']['configurar'] }}">Configurar widgets</a>
                @endif
            </div>
        </section>

        <section class="rd-profile-tabs" aria-label="Dashboards por perfil">
            @foreach ($dashboard['profiles'] as $key => $profile)
                <a
                    href="{{ request()->fullUrlWithQuery(['perfil' => $key]) }}"
                    class="rd-profile-tab {{ $dashboard['header']['perfil_atual'] === $key ? 'is-active' : '' }}"
                >
                    <span class="rd-profile-icon">{{ $profile['icon'] }}</span>
                    <span>
                        <strong>{{ $profile['label'] }}</strong>
                        <small>{{ $profile['hint'] }}</small>
                    </span>
                </a>
            @endforeach
        </section>

        <section class="rd-guide rd-tone-info">
            <strong>Como usar:</strong>
            <span>Escolha o perfil acima e veja apenas os indicadores e filas que fazem sentido para aquela função. Os números vêm dos registros reais visíveis para seu usuário.</span>
        </section>

        @if (! empty($dashboard['missing_columns']))
            <section class="rd-guide rd-tone-warning">
                <strong>Campos recomendados ausentes:</strong>
                <span>{{ implode(', ', $dashboard['missing_columns']) }}. A tela continua funcionando com fallback, mas esses campos aumentam a precisão dos dashboards.</span>
            </section>
        @endif

        <section class="rd-cards">
            @foreach ($dashboard['cards'] as $card)
                <article class="rd-card rd-tone-{{ $card['tone'] }} {{ ! empty($card['giant']) ? 'rd-card-giant' : '' }}">
                    <span>{{ $card['label'] }}</span>
                    <strong>{{ $card['value'] }}</strong>
                    <small>{{ $card['hint'] }}</small>
                </article>
            @endforeach
        </section>

        <section class="rd-profile-layout">
            @foreach ($dashboard['layout'] as $panel)
                <article class="rd-panel rd-profile-panel">
                    <div class="rd-panel-title">
                        <div>
                            <h2>{{ $panel['title'] }}</h2>
                            <p>{{ $panel['subtitle'] }}</p>
                        </div>
                        <span>{{ $panel['badge'] }}</span>
                    </div>

                    @if ($panel['type'] === 'tasks')
                        <div class="rd-list">
                            @forelse ($dashboard['sections'][$panel['key']] ?? [] as $item)
                                @include('filament.pages.partials.dashboard-task-card', ['item' => $item])
                            @empty
                                <div class="rd-empty">Nenhum item encontrado para este painel.</div>
                            @endforelse
                        </div>
                    @elseif ($panel['type'] === 'comments')
                        <div class="rd-list">
                            @forelse ($dashboard['sections'][$panel['key']] ?? [] as $comentario)
                                <a class="rd-comment" href="{{ $comentario['url'] ?: '#' }}">
                                    <strong>{{ $comentario['title'] }}</strong>
                                    <p>{{ $comentario['description'] }}</p>
                                    <small>{{ $comentario['author'] }} • {{ $comentario['created_at'] }}</small>
                                </a>
                            @empty
                                <div class="rd-empty">Nenhum comentário atribuído a você.</div>
                            @endforelse
                        </div>
                    @elseif ($panel['type'] === 'audit')
                        <div class="rd-list">
                            @forelse ($dashboard['sections'][$panel['key']] ?? [] as $evento)
                                <div class="rd-audit-row rd-tone-{{ $evento['tone'] }}">
                                    <div>
                                        <strong>{{ $evento['title'] }}</strong>
                                        <p>{{ $evento['description'] }}</p>
                                        <small>{{ $evento['user'] }} • {{ $evento['when'] }}</small>
                                    </div>
                                </div>
                            @empty
                                <div class="rd-empty">Nenhum evento de auditoria encontrado nos últimos 30 dias.</div>
                            @endforelse
                        </div>
                    @elseif ($panel['type'] === 'chart')
                        @php($rows = $dashboard['charts'][$panel['key']] ?? [])
                        <div class="rd-bars rd-profile-chart">
                            @forelse ($rows as $row)
                                <div class="rd-bar-row">
                                    <div>
                                        <strong>{{ $row['label'] }}</strong>
                                        <span>
                                            {{ $row['value'] ?? '' }}
                                            @if (! empty($row['hint']))
                                                • {{ $row['hint'] }}
                                            @elseif (isset($row['percent']))
                                                • {{ $row['percent'] }}%
                                            @endif
                                        </span>
                                    </div>
                                    <div class="rd-bar"><span style="width: {{ $row['percent'] ?? 0 }}%"></span></div>
                                </div>
                            @empty
                                <div class="rd-empty">Sem dados suficientes para montar este gráfico.</div>
                            @endforelse
                        </div>
                    @endif
                </article>
            @endforeach
        </section>
    </div>
</x-filament-panels::page>
