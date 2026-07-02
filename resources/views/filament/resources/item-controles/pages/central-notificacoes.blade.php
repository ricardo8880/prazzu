<x-filament-panels::page>
    @once
    @endonce

    @php
        $notificacoes = $this->notificacoes;
        $resumo = $this->resumo;

        $tipos = [
            'todos' => ['label' => 'Todas', 'count' => $resumo['total'], 'icon' => 'heroicon-o-inbox-stack'],
            'vencimento' => ['label' => 'Vencimentos', 'count' => $resumo['vencimentos'], 'icon' => 'heroicon-o-calendar-days'],
            'aprovacao' => ['label' => 'Aprovações', 'count' => $resumo['aprovacoes'], 'icon' => 'heroicon-o-check-badge'],
            'comentario' => ['label' => 'Comentários', 'count' => $resumo['comentarios'], 'icon' => 'heroicon-o-chat-bubble-left-right'],
            'documento' => ['label' => 'Documentos', 'count' => $resumo['documentos'], 'icon' => 'heroicon-o-document-text'],
            'cliente' => ['label' => 'Cliente', 'count' => $resumo['cliente'], 'icon' => 'heroicon-o-user-group'],
            'sla' => ['label' => 'SLA', 'count' => $resumo['sla'], 'icon' => 'heroicon-o-bolt'],
        ];

        $grupos = [
            'critica' => [
                'titulo' => 'Críticas',
                'descricao' => 'Resolver primeiro: vencidos, SLA estourado ou risco operacional real.',
                'icon' => 'heroicon-o-fire',
                'items' => $notificacoes->where('criticidade', 'critica'),
            ],
            'alta' => [
                'titulo' => 'Importantes',
                'descricao' => 'Exigem ação rápida: aprovações, mensagens e vencimentos próximos.',
                'icon' => 'heroicon-o-exclamation-triangle',
                'items' => $notificacoes->where('criticidade', 'alta'),
            ],
            'media_baixa' => [
                'titulo' => 'Informativas',
                'descricao' => 'Atualizações úteis para acompanhamento, sem urgência imediata.',
                'icon' => 'heroicon-o-information-circle',
                'items' => $notificacoes->filter(fn ($item) => in_array($item['criticidade'], ['media', 'baixa'], true)),
            ],
        ];
    @endphp

    <div class="central-notificacoes">
        <section class="central-notificacoes__hero" aria-label="Resumo da Central de Notificações">
            <div class="central-notificacoes__hero-content">
                <span class="central-notificacoes__eyebrow">Central única de notificações</span>
                <h2 class="central-notificacoes__hero-title">Tudo que precisa de atenção em uma fila organizada</h2>
                <p class="central-notificacoes__hero-text">
                    Acompanhe notificações reais do sistema agrupadas por criticidade, tipo e ação possível. A prioridade é mostrar primeiro o que precisa ser resolvido, sem encher a tela com alertas decorativos.
                </p>
            </div>

            <div class="central-notificacoes__hero-actions">
                <x-filament::button color="gray" icon="heroicon-m-arrow-path" wire:click="$refresh">
                    Atualizar
                </x-filament::button>

                <x-filament::button color="success" icon="heroicon-m-check-circle" wire:click="marcarTodasComoLidas">
                    Marcar todas como lidas
                </x-filament::button>

                <x-filament::button color="gray" icon="heroicon-m-x-mark" wire:click="limparFiltros">
                    Limpar filtros
                </x-filament::button>
            </div>
        </section>

        <section class="central-notificacoes__resumo" aria-label="Indicadores das notificações">
            <article class="central-notificacoes__card-resumo central-notificacoes__card-resumo--criticas">
                <div>
                    <p class="central-notificacoes__label">Críticas</p>
                    <p class="central-notificacoes__numero">{{ $resumo['criticas'] }}</p>
                    <span class="central-notificacoes__hint">Ações que não devem esperar</span>
                </div>
                <div class="central-notificacoes__icone" aria-hidden="true"><x-filament::icon icon="heroicon-o-fire" /></div>
            </article>

            <article class="central-notificacoes__card-resumo central-notificacoes__card-resumo--pendentes">
                <div>
                    <p class="central-notificacoes__label">Importantes</p>
                    <p class="central-notificacoes__numero">{{ $resumo['importantes'] }}</p>
                    <span class="central-notificacoes__hint">Demandas para acompanhar hoje</span>
                </div>
                <div class="central-notificacoes__icone" aria-hidden="true"><x-filament::icon icon="heroicon-o-exclamation-triangle" /></div>
            </article>

            <article class="central-notificacoes__card-resumo">
                <div>
                    <p class="central-notificacoes__label">Não lidas</p>
                    <p class="central-notificacoes__numero">{{ $resumo['nao_lidas'] }}</p>
                    <span class="central-notificacoes__hint">Novas ou ainda sem leitura</span>
                </div>
                <div class="central-notificacoes__icone" aria-hidden="true"><x-filament::icon icon="heroicon-o-bell-alert" /></div>
            </article>

            <article class="central-notificacoes__card-resumo central-notificacoes__card-resumo--acoes">
                <div>
                    <p class="central-notificacoes__label">Acionáveis</p>
                    <p class="central-notificacoes__numero">{{ $resumo['acionaveis'] }}</p>
                    <span class="central-notificacoes__hint">Possuem link ou leitura direta</span>
                </div>
                <div class="central-notificacoes__icone" aria-hidden="true"><x-filament::icon icon="heroicon-o-cursor-arrow-rays" /></div>
            </article>
        </section>

        <section class="central-notificacoes__painel" aria-label="Filtros da Central de Notificações">
            <div class="central-notificacoes__busca">
                <x-filament::input.wrapper prefix-icon="heroicon-m-magnifying-glass">
                    <x-filament::input
                        type="search"
                        wire:model.live.debounce.350ms="busca"
                        placeholder="Buscar por empresa, item, mensagem, prioridade ou tipo..."
                    />
                </x-filament::input.wrapper>
            </div>

            <div class="central-notificacoes__status-tabs" role="tablist" aria-label="Filtro por status e criticidade">
                @foreach ([
                    'ativos' => 'Ativas',
                    'nao_lidas' => 'Não lidas',
                    'criticas' => 'Críticas',
                    'importantes' => 'Importantes',
                    'informativas' => 'Informativas',
                    'todos' => 'Tudo',
                ] as $status => $label)
                    <button
                        type="button"
                        wire:click="$set('filtroStatus', '{{ $status }}')"
                        class="central-notificacoes__status-tab {{ $filtroStatus === $status ? 'is-active' : '' }}"
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            <div class="central-notificacoes__tipo-grid">
                @foreach ($tipos as $tipo => $config)
                    <button
                        type="button"
                        wire:click="filtrarTipo('{{ $tipo }}')"
                        class="central-notificacoes__tipo-card {{ $filtroTipo === $tipo ? 'is-active' : '' }}"
                    >
                        <span class="central-notificacoes__tipo-icon"><x-filament::icon :icon="$config['icon']" /></span>
                        <span class="central-notificacoes__tipo-label">{{ $config['label'] }}</span>
                        <strong class="central-notificacoes__tipo-count">{{ $config['count'] }}</strong>
                    </button>
                @endforeach
            </div>
        </section>

        <section class="central-notificacoes__lista" aria-label="Lista de notificações unificadas">
            @if ($notificacoes->isNotEmpty())
                @foreach ($grupos as $grupoKey => $grupo)
                    @if ($grupo['items']->isNotEmpty())
                        <div class="central-notificacoes__grupo central-notificacoes__grupo--{{ $grupoKey }}">
                            <header class="central-notificacoes__grupo-header">
                                <div class="central-notificacoes__grupo-icon" aria-hidden="true"><x-filament::icon :icon="$grupo['icon']" /></div>
                                <div>
                                    <h3 class="central-notificacoes__grupo-title">{{ $grupo['titulo'] }}</h3>
                                    <p class="central-notificacoes__grupo-description">{{ $grupo['descricao'] }}</p>
                                </div>
                                <strong class="central-notificacoes__grupo-count">{{ $grupo['items']->count() }}</strong>
                            </header>

                            <div class="central-notificacoes__grupo-lista">
                                @foreach ($grupo['items'] as $notificacao)
                                    <article
                                        wire:key="notificacao-{{ $notificacao['uid'] }}"
                                        class="notificacao-card {{ $notificacao['lida'] ? 'notificacao-card--lida' : 'notificacao-card--nao-lida' }} notificacao-card--{{ $notificacao['criticidade'] }} {{ $notificacao['tipo_classe'] }}"
                                    >
                                        <div class="notificacao-card__conteudo">
                                            <div class="notificacao-card__icone-principal" aria-hidden="true">
                                                <x-filament::icon :icon="$notificacao['tipo_icon']" />
                                            </div>

                                            <div class="notificacao-card__principal">
                                                <div class="notificacao-card__topo">
                                                    <span class="notificacao-card__badge notificacao-card__badge--tipo">
                                                        {{ $notificacao['tipo_label'] }}
                                                    </span>

                                                    <span class="notificacao-card__badge notificacao-card__badge--criticidade notificacao-card__badge--{{ $notificacao['criticidade'] }}">
                                                        {{ $notificacao['criticidade_label'] }}
                                                    </span>

                                                    <span class="notificacao-card__badge {{ $notificacao['lida'] ? 'notificacao-card__badge--lida' : 'notificacao-card__badge--nao-lida' }}">
                                                        <x-filament::icon :icon="$notificacao['lida'] ? 'heroicon-m-check-circle' : 'heroicon-m-sparkles'" />
                                                        {{ $notificacao['lida'] ? 'Lida' : 'Nova' }}
                                                    </span>

                                                    <span class="notificacao-card__data" title="{{ $notificacao['created_at_formatado'] }}">
                                                        {{ $notificacao['created_at_humano'] }}
                                                    </span>
                                                </div>

                                                <h2 class="notificacao-card__titulo">{{ $notificacao['titulo'] }}</h2>

                                                @if ($notificacao['mensagem'])
                                                    <p class="notificacao-card__mensagem">{{ $notificacao['mensagem'] }}</p>
                                                @endif

                                                <div class="notificacao-card__metas">
                                                    @if ($notificacao['empresa'])
                                                        <div class="notificacao-card__meta" title="{{ $notificacao['empresa'] }}">
                                                            <span class="notificacao-card__meta-icon" aria-hidden="true"><x-filament::icon icon="heroicon-o-building-office-2" /></span>
                                                            <span class="notificacao-card__meta-texto">{{ $notificacao['empresa'] }}</span>
                                                        </div>
                                                    @endif

                                                    @if ($notificacao['item_titulo'])
                                                        <div class="notificacao-card__meta" title="{{ $notificacao['item_titulo'] }}">
                                                            <span class="notificacao-card__meta-icon" aria-hidden="true"><x-filament::icon icon="heroicon-o-clipboard-document-list" /></span>
                                                            <span class="notificacao-card__meta-texto">{{ $notificacao['item_titulo'] }}</span>
                                                        </div>
                                                    @endif

                                                    @if ($notificacao['prazo'])
                                                        <div class="notificacao-card__meta" title="Prazo">
                                                            <span class="notificacao-card__meta-icon" aria-hidden="true"><x-filament::icon icon="heroicon-o-clock" /></span>
                                                            <span class="notificacao-card__meta-texto">{{ $notificacao['prazo'] }}</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="notificacao-card__acoes">
                                                @if (! $notificacao['lida'] && $notificacao['marcavel'] && $notificacao['source_id'])
                                                    <x-filament::button
                                                        size="sm"
                                                        color="success"
                                                        icon="heroicon-m-check"
                                                        wire:click="marcarComoLida('{{ $notificacao['source'] }}', {{ $notificacao['source_id'] }})"
                                                    >
                                                        Marcar lida
                                                    </x-filament::button>
                                                @endif

                                                @if ($notificacao['url'])
                                                    <x-filament::button
                                                        size="sm"
                                                        color="primary"
                                                        icon="heroicon-m-arrow-top-right-on-square"
                                                        tag="a"
                                                        href="{{ $notificacao['url'] }}"
                                                    >
                                                        Abrir item
                                                    </x-filament::button>
                                                @endif

                                                @if ($notificacao['source'] === 'notificacao_interna' && $notificacao['source_id'])
                                                    <x-filament::button
                                                        size="sm"
                                                        color="gray"
                                                        icon="heroicon-m-archive-box"
                                                        wire:click="excluirNotificacao({{ $notificacao['source_id'] }})"
                                                        wire:confirm="Tem certeza que deseja arquivar esta notificação interna?"
                                                    >
                                                        Arquivar
                                                    </x-filament::button>
                                                @endif
                                            </div>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            @else
                <div class="central-notificacoes__empty">
                    <div class="central-notificacoes__empty-icon" aria-hidden="true"><x-filament::icon icon="heroicon-o-bell-slash" /></div>
                    <h2 class="central-notificacoes__empty-title">Nenhuma notificação encontrada</h2>
                    <p class="central-notificacoes__empty-text">
                        Não existem alertas reais para os filtros atuais. Quando houver vencimentos, aprovações, mensagens, documentos, comentários ou SLA exigindo atenção, eles aparecerão aqui agrupados por prioridade.
                    </p>
                    <x-filament::button color="gray" icon="heroicon-m-x-mark" wire:click="limparFiltros">
                        Limpar filtros
                    </x-filament::button>
                </div>
            @endif
        </section>
    </div>
</x-filament-panels::page>
