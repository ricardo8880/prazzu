<x-filament-panels::page>
    <link rel="stylesheet" href="{{ asset('css/home-classica.css') }}?v=20260520-sidebar-logo-final">
    <link rel="stylesheet" href="{{ asset('css/home-operacional.css') }}?v=20260520-sidebar-logo-final">
    <link rel="stylesheet" href="{{ asset('css/trabalho-pages.css') }}?v=20260507-home-kanban">
    <link rel="stylesheet" href="{{ asset('css/prazzu-ux-essentials.css') }}?v={{ file_exists(public_path('css/prazzu-ux-essentials.css')) ? filemtime(public_path('css/prazzu-ux-essentials.css')) : time() }}">

    @php
        $trabalhoCssPath = public_path('css/trabalho-pages.css');

        $kpis = $dashboard['kpis'] ?? [];
        $tarefas = $dashboard['tarefas'] ?? ['tabs' => [], 'itens' => []];
        $kanban = $dashboard['kanban'] ?? [];
        $prazos = $dashboard['prazos'] ?? [];
        $sla = $dashboard['sla'] ?? [];
        $documentos = $dashboard['documentos'] ?? [];
        $financeiro = $dashboard['financeiro'] ?? [];
        $portal = $dashboard['portal'] ?? [];
        $compliance = $dashboard['compliance'] ?? [];
        $atividades = $dashboard['atividades'] ?? [];
        $assistente = $dashboard['assistente'] ?? [];
        $urls = $dashboard['urls'] ?? [];
        $usuario = explode(' ', $dashboard['usuario'] ?? 'Usuário')[0];

        $resumoHoje = $dashboard['resumoHoje'] ?? [];
        $minhasPendencias = $dashboard['minhasPendencias'] ?? [];
        $vencimentosProximos = $dashboard['vencimentosProximos'] ?? [];
        $aprovacoesAguardando = $dashboard['aprovacoesAguardando'] ?? [];
        $itensAtrasados = $dashboard['itensAtrasados'] ?? [];
        $ultimosComentarios = $dashboard['ultimosComentarios'] ?? [];
        $atalhosRapidos = $dashboard['atalhosRapidos'] ?? [];
        $resumoEmpresas = $dashboard['resumoEmpresas'] ?? [];
        $fluxoOperacional = $dashboard['fluxoOperacional'] ?? [];
        $notificacoes = $dashboard['notificacoes'] ?? [];
        $whiteLabel = \App\Support\WhiteLabelSettings::make();
        $assistantName = $whiteLabel->assistantName();
        $brandName = $whiteLabel->displayName();
    @endphp


    @if(file_exists($trabalhoCssPath))
        <style>
            {!! file_get_contents($trabalhoCssPath) !!}
        </style>
    @endif

    <style>
        .pz-home-kanban-section {
            margin-top: 24px;
            width: 100%;
        }

        .pz-home-kanban-card {
            overflow: hidden;
        }

        .pz-home-kanban-wrap {
            margin-top: 16px;
            overflow-x: auto;
            padding-bottom: 4px;
        }

        .pz-home-tp-kanban {
            grid-template-columns: repeat(3, minmax(260px, 1fr));
            gap: 16px;
            min-width: 820px;
        }

        .pz-home-tp-kanban .tp-kanban-column {
            min-height: 320px;
        }

        .pz-home-tp-kanban .tp-kanban-card {
            min-height: auto;
            text-decoration: none;
        }

        .pz-home-tp-kanban .tp-kanban-card-top strong {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .pz-home-kanban-link {
            margin-top: 14px;
            display: flex;
            justify-content: center;
        }

        .pz-home-kanban-link a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            padding: 10px 16px;
            background: #f4f0ff;
            color: #5b2fbf;
            font-size: 13px;
            font-weight: 800;
            text-decoration: none;
        }

        @media (max-width: 920px) {
            .pz-home-tp-kanban {
                min-width: 760px;
            }
        }
    </style>


    <section class="pz-ux-toolbar">
        <div>
            <strong>Comece por aqui</strong>
            <span>Revise pendências críticas, documentos e aprovações antes de abrir telas detalhadas.</span>
        </div>
        <div class="pz-ux-actions">
            <a class="pz-ux-action primary" href="{{ $urls['novaTarefa'] ?? '#' }}">Nova tarefa</a>
            <a class="pz-ux-action" href="{{ $urls['enviarDocumento'] ?? '#' }}">Enviar documento</a>
            <a class="pz-ux-action subtle" href="{{ $urls['prazos'] ?? '#' }}">Ver prazos</a>
        </div>
    </section>

    <section class="pz-ux-block soft">
        <div class="pz-ux-head">
            <div>
                <span class="pz-ux-kicker">Orientação rápida</span>
                <h2>O que fazer primeiro hoje</h2>
                <p>Use os atalhos abaixo para trabalhar em ordem de impacto e reduzir cliques desnecessários.</p>
            </div>
        </div>
        <div class="pz-ux-grid three">
            <article class="pz-ux-guide-card"><span class="pz-ux-guide-icon">1</span><div><strong>Resolver atrasos</strong><span>Priorize itens vencidos e aprovações travadas antes das tarefas novas.</span></div></article>
            <article class="pz-ux-guide-card"><span class="pz-ux-guide-icon">2</span><div><strong>Concluir pendências</strong><span>Abra sua fila operacional e finalize o que depende de responsável interno.</span></div></article>
            <article class="pz-ux-guide-card"><span class="pz-ux-guide-icon">3</span><div><strong>Atualizar documentos</strong><span>Envie anexos faltantes e revise documentos que vão aparecer no portal.</span></div></article>
        </div>
    </section>

    <section class="pz-layout-switcher" data-home-layout-switcher>
        <div>
            <strong data-home-layout-title>Home clássica</strong>
            <small data-home-layout-description>Você está vendo a Home antiga, com todos os indicadores originais preservados.</small>
        </div>
        <button type="button" class="pz-layout-toggle-btn" data-home-layout-toggle>Ver Home operacional</button>
    </section>

    <div data-home-layout="classic">
    <div class="pz-home-shell">
        <main class="pz-main-column">
            <section class="pz-hero-row">
                <div>
                    <h1>Olá, {{ $usuario }}! <span>👋</span></h1>
                    <p>Aqui está o resumo da sua operação hoje.</p>
                </div>

                <div class="pz-quick-actions">
                    <a href="{{ $urls['novaTarefa'] ?? '#' }}" class="pz-btn pz-btn-primary">＋ Nova Tarefa</a>
                    <a href="{{ $urls['enviarDocumento'] ?? '#' }}" class="pz-btn">↥ Enviar Documento</a>
                    <a href="{{ $urls['novoCliente'] ?? '#' }}" class="pz-btn">♙ Novo Cliente</a>
                </div>
            </section>

            <section class="pz-kpi-grid">
                @foreach($kpis as $kpi)
                    <article class="pz-card pz-kpi-card pz-tone-{{ $kpi['tone'] ?? 'purple' }}">
                        <div class="pz-kpi-top">
                            <span>{{ $kpi['label'] ?? '-' }}</span>
                            <b>{{ $kpi['icon'] ?? '•' }}</b>
                        </div>

                        <strong>{{ $kpi['value'] ?? '-' }}</strong>
                        <small>{{ $kpi['trend'] ?? '-' }}</small>

                        @if(! empty($kpi['spark']))
                            <div class="pz-sparkline">
                                @foreach($kpi['spark'] as $point)
                                    <i style="height: {{ max(18, min(94, (int) $point * 3)) }}%"></i>
                                @endforeach
                            </div>
                        @else
                            <div class="pz-risk-pill">{{ $kpi['trend'] ?? 'Acompanhar' }}</div>
                        @endif
                    </article>
                @endforeach
            </section>

            <section class="pz-grid-top">
                <article class="pz-card pz-tasks-card">
                    <div class="pz-card-head">
                        <h2>Minhas tarefas</h2>
                        <a href="{{ $urls['tarefas'] ?? '#' }}">Ver todas</a>
                    </div>

                    <div class="pz-tabs">
                        <span class="is-active">Pendentes <b>{{ $tarefas['tabs']['pendentes'] ?? 0 }}</b></span>
                        <span>Em andamento <b>{{ $tarefas['tabs']['em_andamento'] ?? 0 }}</b></span>
                        <span>Concluídas <b>{{ $tarefas['tabs']['concluidas'] ?? 0 }}</b></span>
                    </div>

                    <div class="pz-task-list">
                        @forelse($tarefas['itens'] ?? [] as $item)
                            <a href="{{ $item['url'] ?? '#' }}" class="pz-task-row">
                                <span class="pz-checkbox"></span>
                                <strong>{{ $item['titulo'] ?? '-' }}</strong>
                                <em class="pz-badge pz-badge-{{ $item['prioridade']['class'] ?? 'warning' }}">
                                    {{ $item['prioridade']['label'] ?? 'Média' }}
                                </em>
                                <small>{{ $item['data'] ?? '-' }}</small>
                            </a>
                        @empty
                            <div class="pz-empty"><strong>Nenhuma tarefa pendente.</strong><br>Quando houver algo para você executar, aparecerá aqui com prioridade e prazo.</div>
                        @endforelse
                    </div>

                    <a href="{{ $urls['novaTarefa'] ?? '#' }}" class="pz-add-link">＋ Adicionar tarefa</a>
                </article>

                <article class="pz-card pz-deadlines-card">
                    <div class="pz-card-head">
                        <h2>Próximos prazos</h2>
                        <a href="{{ $urls['prazos'] ?? '#' }}">Ver todos</a>
                    </div>

                    <div class="pz-deadline-list">
                        @forelse($prazos as $prazo)
                            <a href="{{ $prazo['url'] ?? '#' }}" class="pz-deadline-row">
                                <span>▣</span>
                                <div>
                                    <strong>{{ $prazo['titulo'] ?? '-' }}</strong>
                                    <small>{{ $prazo['empresa'] ?? '-' }} • {{ $prazo['data'] ?? '-' }}</small>
                                </div>
                                <em>{{ $prazo['status'] ?? '-' }}</em>
                            </a>
                        @empty
                            <div class="pz-empty"><strong>Nenhum prazo próximo.</strong><br>Documentos e tarefas com vencimento serão destacados automaticamente.</div>
                        @endforelse
                    </div>
                </article>
            
            </section>

            <section class="pz-kanban-full pz-home-kanban-section">
                <article class="pz-card pz-kanban-card pz-home-kanban-card">
                    <div class="pz-card-head">
                        <h2>Visão Kanban</h2>
                        <a href="{{ $urls['kanban'] ?? '#' }}">Ver quadro completo</a>
                    </div>

                    <div class="pz-home-kanban-wrap">
                        <div class="tp-kanban pz-home-tp-kanban">
                            @foreach($kanban as $coluna)
                                <div class="tp-kanban-column tp-kanban-{{ $coluna['key'] ?? 'pendente' }}">
                                    <div class="tp-kanban-header">
                                        <div>
                                            <strong>{{ $coluna['label'] ?? '-' }}</strong>
                                            <small>{{ $coluna['total'] ?? 0 }} item(ns)</small>
                                        </div>
                                        <span>{{ $coluna['total'] ?? 0 }}</span>
                                    </div>

                                    <div class="tp-kanban-cards">
                                        @forelse(($coluna['itens'] ?? []) as $item)
                                            @php
                                                $prioridadeClasse = $item['prioridade']['class'] ?? 'warning';
                                                $prioridadeBadge = match ($prioridadeClasse) {
                                                    'danger' => 'tp-mini-danger',
                                                    'success' => 'tp-mini-success',
                                                    default => '',
                                                };
                                            @endphp

                                            <a href="{{ $item['url'] ?? '#' }}" class="tp-kanban-card">
                                                <div class="tp-kanban-card-top">
                                                    <strong>{{ $item['titulo'] ?? '-' }}</strong>
                                                    <span class="tp-mini-badge {{ $prioridadeBadge }}">
                                                        {{ $item['prioridade']['label'] ?? 'Média' }}
                                                    </span>
                                                </div>

                                                <span>{{ $item['empresa'] ?? 'Sem empresa' }}</span>

                                                <div class="tp-kanban-meta">
                                                    <span>Resumo da Home</span>
                                                    <span>Abrir item</span>
                                                </div>
                                            </a>
                                        @empty
                                            <div class="tp-empty">Nenhum item nesta coluna.</div>
                                        @endforelse
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </article>
            </section>

            <section class="pz-grid-bottom">
                <article class="pz-card pz-sla-card">
                    <div class="pz-card-head">
                        <h2>SLA e Prazos</h2>
                        <a href="{{ $urls['prazos'] ?? '#' }}">Ver todos</a>
                    </div>

                    <div class="pz-sla-content">
                        <div class="pz-donut">
                            <b>{{ $sla['total'] ?? 0 }}</b>
                            <span>Total</span>
                        </div>

                        <div class="pz-sla-legend">
                            <p><i class="ok"></i> No prazo <strong>{{ $sla['noPrazo'] ?? 0 }} ({{ $sla['percentuais']['noPrazo'] ?? 0 }}%)</strong></p>
                            <p><i class="warn"></i> Atenção <strong>{{ $sla['atencao'] ?? 0 }} ({{ $sla['percentuais']['atencao'] ?? 0 }}%)</strong></p>
                            <p><i class="late"></i> Vencidos <strong>{{ $sla['vencidos'] ?? 0 }} ({{ $sla['percentuais']['vencidos'] ?? 0 }}%)</strong></p>
                        </div>
                    </div>
                </article>

                <article class="pz-card pz-docs-card">
                    <div class="pz-card-head">
                        <h2>Documentos recentes</h2>
                        <a href="{{ $urls['documentos'] ?? '#' }}">Ver todos</a>
                    </div>

                    <div class="pz-doc-list">
                        @forelse($documentos as $doc)
                            <a href="{{ $doc['url'] ?? '#' }}" class="pz-doc-row">
                                <span>▧</span>
                                <div>
                                    <strong>{{ $doc['titulo'] ?? '-' }}</strong>
                                    <small>{{ $doc['meta'] ?? '-' }}</small>
                                </div>
                                <em class="pz-badge pz-badge-{{ $doc['status']['class'] ?? 'success' }}">
                                    {{ $doc['status']['label'] ?? 'Válido' }}
                                </em>
                            </a>
                        @empty
                            <div class="pz-empty">Nenhum documento recente.</div>
                        @endforelse
                    </div>
                </article>

                <article class="pz-card pz-finance-card">
                    <div class="pz-card-head">
                        <h2>Faturamento</h2>
                        <a href="{{ $urls['financeiro'] ?? '#' }}">Este mês⌄</a>
                    </div>

                    <strong class="pz-money">R$ {{ number_format($financeiro['total'] ?? 0, 2, ',', '.') }}</strong>
                    <small class="pz-positive">+32% vs mês anterior</small>

                    <div class="pz-finance-line">
                        @foreach(($financeiro['series'] ?? []) as $point)
                            <i style="height: {{ max(18, min(95, (int) $point * 3)) }}%"></i>
                        @endforeach
                    </div>

                    <div class="pz-finance-boxes">
                        <div>
                            <span>Recebido</span>
                            <strong>R$ {{ number_format($financeiro['recebido'] ?? 0, 2, ',', '.') }}</strong>
                        </div>
                        <div>
                            <span>A receber</span>
                            <strong>R$ {{ number_format($financeiro['aReceber'] ?? 0, 2, ',', '.') }}</strong>
                        </div>
                    </div>
                </article>
            </section>

            <section class="pz-footer-grid">
                <article class="pz-card pz-portal-card">
                    <div class="pz-card-head">
                        <h2>Portal do Cliente</h2>
                        <a href="{{ $urls['clientes'] ?? '#' }}">Ver portal</a>
                    </div>

                    <div class="pz-portal-items">
                        @foreach($portal as $item)
                            <a href="{{ $item['url'] ?? '#' }}">
                                <b>◈</b>
                                <strong>{{ $item['label'] ?? '-' }}</strong>
                                <small>{{ $item['value'] ?? 0 }} {{ $item['hint'] ?? '' }}</small>
                            </a>
                        @endforeach
                    </div>
                </article>

                <article class="pz-card pz-compliance-card">
                    <div class="pz-card-head">
                        <h2>Compliance</h2>
                        <a href="{{ $urls['prazos'] ?? '#' }}">Ver painel</a>
                    </div>

                    <div class="pz-compliance-items">
                        @foreach($compliance as $item)
                            <div>
                                <span>{{ $item['label'] ?? '-' }}</span>
                                <strong>{{ $item['value'] ?? 0 }}</strong>
                                <small>{{ $item['hint'] ?? '' }}</small>
                            </div>
                        @endforeach
                    </div>
                </article>
            </section>
        </main>

        <aside class="pz-right-column">
            <section class="pz-card pz-ai-card">
                <div class="pz-ai-head">
                    <span>✧</span>
                    <strong>{{ $assistantName }} <b>BETA</b></strong>
                    <em>×</em>
                </div>

                <h3>Olá, {{ $usuario }}! 👋</h3>
                <p>Como posso ajudar hoje?</p>

                <div class="pz-ai-actions">
                    @foreach($assistente as $acao)
                        <a href="{{ $acao['url'] ?? '#' }}">{{ $acao['texto'] ?? '-' }}</a>
                    @endforeach
                </div>
            </section>

            <section class="pz-card pz-activity-card">
                <div class="pz-card-head">
                    <h2>Atividades recentes</h2>
                    <a href="{{ $urls['kanban'] ?? '#' }}">Ver todas</a>
                </div>

                <div class="pz-activity-list">
                    @forelse($atividades as $atividade)
                        <div class="pz-activity-row">
                            <span>{{ strtoupper(mb_substr($atividade['usuario'] ?? 'S', 0, 1)) }}</span>
                            <div>
                                <strong>{{ $atividade['titulo'] ?? '-' }}</strong>
                                <p>{{ $atividade['descricao'] ?? '-' }}</p>
                                <small>{{ $atividade['quando'] ?? '-' }}</small>
                            </div>
                        </div>
                    @empty
                        <div class="pz-empty">Nenhuma atividade recente.</div>
                    @endforelse
                </div>
            </section>

            <section class="pz-card pz-upgrade-card">
                <div class="pz-crown">♛</div>
                <h2>Desbloqueie o poder do {{ $brandName }}</h2>
                <p>Recursos avançados de IA, automações, relatórios personalizados e muito mais.</p>
                <a href="{{ $urls['financeiro'] ?? '#' }}">Upgrade do plano</a>
            </section>
        </aside>
    </div>

    </div>

    <div data-home-layout="operational" class="is-hidden">
        <div class="pz-home-shell">
                <section class="pz-home-hero pz-panel">
                    <div class="pz-hero-copy">
                        <span class="pz-eyebrow">Painel operacional</span>
                        <h1>Olá, {{ $usuario }}. Veja o que precisa de atenção hoje.</h1>
                        <p>Centralize pendências, vencimentos, aprovações, comentários e atalhos sem precisar navegar por várias telas.</p>
                    </div>
        
                    <div class="pz-hero-actions">
                        <a href="{{ $urls['novaTarefa'] ?? '#' }}" class="pz-action-primary">＋ Nova tarefa</a>
                        <a href="{{ $urls['minhasPendencias'] ?? '#' }}" class="pz-action-secondary">✓ Pendências</a>
                        <a href="{{ $urls['centralNotificacoes'] ?? '#' }}" class="pz-action-secondary">◉ Notificações</a>
                    </div>
                </section>
        
                <section class="pz-summary-grid">
                    @forelse($resumoHoje as $card)
                        <a href="{{ $card['url'] ?? '#' }}" class="pz-summary-card pz-tone-{{ $card['tone'] ?? 'slate' }}">
                            <div>
                                <span>{{ $card['label'] ?? '-' }}</span>
                                <strong>{{ $card['value'] ?? 0 }}</strong>
                            </div>
                            <small>{{ $card['hint'] ?? 'Acompanhar' }}</small>
                        </a>
                    @empty
                        <div class="pz-empty pz-empty-wide">Ainda não existem dados operacionais para exibir na Home.</div>
                    @endforelse
                </section>
        
                <section class="pz-main-grid">
                    <main class="pz-left-column">
                        <section class="pz-panel pz-focus-panel">
                            <div class="pz-section-head">
                                <div>
                                    <span class="pz-eyebrow">Prioridade do dia</span>
                                    <h2>Pendências</h2>
                                </div>
                                <a href="{{ $urls['minhasPendencias'] ?? '#' }}">Ver todas</a>
                            </div>
        
                            <div class="pz-task-list">
                                @forelse($minhasPendencias as $item)
                                    <a href="{{ $item['url'] ?? '#' }}" class="pz-task-row pz-row-{{ $item['badge'] ?? 'info' }}">
                                        <span class="pz-row-status"></span>
                                        <div class="pz-row-main">
                                            <strong>{{ $item['titulo'] ?? '-' }}</strong>
                                            <small>{{ $item['empresa'] ?? 'Sem empresa' }} • {{ $item['responsavel'] ?? 'Sem responsável' }}</small>
                                        </div>
                                        <div class="pz-row-meta">
                                            <em>{{ $item['status'] ?? '-' }}</em>
                                            <span>{{ $item['data'] ?? '-' }}</span>
                                        </div>
                                    </a>
                                @empty
                                    <div class="pz-empty">Nenhuma pendência crítica encontrada.</div>
                                @endforelse
                            </div>
                        </section>
        
                        <section class="pz-two-columns">
                            <article class="pz-panel">
                                <div class="pz-section-head">
                                    <div>
                                        <span class="pz-eyebrow">Calendário operacional</span>
                                        <h2>Vencimentos próximos</h2>
                                    </div>
                                    <a href="{{ $urls['prazos'] ?? '#' }}">Ver prazos</a>
                                </div>
        
                                <div class="pz-compact-list">
                                    @forelse($vencimentosProximos as $item)
                                        <a href="{{ $item['url'] ?? '#' }}" class="pz-compact-row">
                                            <b class="pz-date-pill">{{ $item['data'] ?? '-' }}</b>
                                            <div>
                                                <strong>{{ $item['titulo'] ?? '-' }}</strong>
                                                <small>{{ $item['empresa'] ?? 'Sem empresa' }} • {{ $item['tempo'] ?? '-' }}</small>
                                            </div>
                                        </a>
                                    @empty
                                        <div class="pz-empty">Nenhum vencimento nos próximos dias.</div>
                                    @endforelse
                                </div>
                            </article>
        
                            <article class="pz-panel">
                                <div class="pz-section-head">
                                    <div>
                                        <span class="pz-eyebrow">Risco imediato</span>
                                        <h2>Itens atrasados</h2>
                                    </div>
                                    <a href="{{ $urls['prazos'] ?? '#' }}">Corrigir</a>
                                </div>
        
                                <div class="pz-compact-list">
                                    @forelse($itensAtrasados as $item)
                                        <a href="{{ $item['url'] ?? '#' }}" class="pz-compact-row pz-danger-row">
                                            <b class="pz-alert-pill">!</b>
                                            <div>
                                                <strong>{{ $item['titulo'] ?? '-' }}</strong>
                                                <small>{{ $item['empresa'] ?? 'Sem empresa' }} • venceu {{ $item['tempo'] ?? '-' }}</small>
                                            </div>
                                        </a>
                                    @empty
                                        <div class="pz-empty">Nenhum item atrasado.</div>
                                    @endforelse
                                </div>
                            </article>
                        </section>
        
                        <section class="pz-panel">
                            <div class="pz-section-head">
                                <div>
                                    <span class="pz-eyebrow">Empresas / projetos</span>
                                    <h2>Resumo operacional por empresa</h2>
                                </div>
                                <a href="{{ $urls['tarefas'] ?? '#' }}">Abrir tarefas</a>
                            </div>
        
                            <div class="pz-company-grid">
                                @forelse($resumoEmpresas as $empresa)
                                    <a href="{{ $empresa['url'] ?? '#' }}" class="pz-company-card pz-company-{{ $empresa['tone'] ?? 'success' }}">
                                        <div class="pz-company-top">
                                            <strong>{{ $empresa['empresa'] ?? 'Sem empresa' }}</strong>
                                            <em>{{ $empresa['risco'] ?? 'Saudável' }}</em>
                                        </div>
                                        <div class="pz-company-progress"><span style="width: {{ max(0, min(100, (int) ($empresa['progresso'] ?? 0))) }}%"></span></div>
                                        <div class="pz-company-metrics">
                                            <span><b>{{ $empresa['total'] ?? 0 }}</b> abertos</span>
                                            <span><b>{{ $empresa['atrasados'] ?? 0 }}</b> atrasados</span>
                                            <span><b>{{ $empresa['vencendo'] ?? 0 }}</b> vencendo</span>
                                        </div>
                                    </a>
                                @empty
                                    <div class="pz-empty pz-empty-wide">Nenhuma empresa com itens abertos.</div>
                                @endforelse
                            </div>
                        </section>
                    </main>
        
                    <aside class="pz-right-column">
                        <section class="pz-panel pz-shortcuts-panel">
                            <div class="pz-section-head">
                                <div>
                                    <span class="pz-eyebrow">Acesso rápido</span>
                                    <h2>Atalhos rápidos</h2>
                                </div>
                            </div>
        
                            <div class="pz-shortcuts-grid">
                                @foreach($atalhosRapidos as $atalho)
                                    <a href="{{ $atalho['url'] ?? '#' }}" class="pz-shortcut pz-shortcut-{{ $atalho['tone'] ?? 'slate' }}">
                                        <span>{{ $atalho['icon'] ?? '•' }}</span>
                                        <div>
                                            <strong>{{ $atalho['label'] ?? '-' }}</strong>
                                            <small>{{ $atalho['hint'] ?? '' }}</small>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </section>
        
                        <section class="pz-panel">
                            <div class="pz-section-head">
                                <div>
                                    <span class="pz-eyebrow">Decisões</span>
                                    <h2>Aprovações aguardando</h2>
                                </div>
                                <a href="{{ $urls['centralAprovacoes'] ?? '#' }}">Central</a>
                            </div>
        
                            <div class="pz-approval-list">
                                @forelse($aprovacoesAguardando as $aprovacao)
                                    <a href="{{ $aprovacao['url'] ?? '#' }}" class="pz-approval-row">
                                        <span>☑</span>
                                        <div>
                                            <strong>{{ $aprovacao['titulo'] ?? '-' }}</strong>
                                            <small>{{ $aprovacao['empresa'] ?? 'Sem empresa' }} • {{ $aprovacao['tempo'] ?? '-' }}</small>
                                        </div>
                                    </a>
                                @empty
                                    <div class="pz-empty">Nenhuma aprovação aguardando.</div>
                                @endforelse
                            </div>
                        </section>
        
                        <section class="pz-panel">
                            <div class="pz-section-head">
                                <div>
                                    <span class="pz-eyebrow">Conversas recentes</span>
                                    <h2>Últimos comentários</h2>
                                </div>
                            </div>
        
                            <div class="pz-comment-list">
                                @forelse($ultimosComentarios as $comentario)
                                    <a href="{{ $comentario['url'] ?? '#' }}" class="pz-comment-row">
                                        <span>{{ strtoupper(mb_substr($comentario['usuario'] ?? 'U', 0, 1)) }}</span>
                                        <div>
                                            <strong>{{ $comentario['titulo'] ?? '-' }}</strong>
                                            <p>{{ $comentario['comentario'] ?? '-' }}</p>
                                            <small>{{ $comentario['empresa'] ?? 'Operação' }} • {{ $comentario['quando'] ?? '-' }}</small>
                                        </div>
                                    </a>
                                @empty
                                    <div class="pz-empty">Nenhum comentário recente.</div>
                                @endforelse
                            </div>
                        </section>
        
                        <section class="pz-panel">
                            <div class="pz-section-head">
                                <div>
                                    <span class="pz-eyebrow">Fluxo</span>
                                    <h2>Saúde da operação</h2>
                                </div>
                                <a href="{{ $urls['kanban'] ?? '#' }}">Kanban</a>
                            </div>
        
                            <div class="pz-flow-list">
                                @forelse($fluxoOperacional as $etapa)
                                    <div class="pz-flow-row pz-flow-{{ $etapa['tone'] ?? 'slate' }}">
                                        <span>{{ $etapa['label'] ?? '-' }}</span>
                                        <strong>{{ $etapa['value'] ?? 0 }}</strong>
                                    </div>
                                @empty
                                    <div class="pz-empty">Sem dados do fluxo operacional.</div>
                                @endforelse
                            </div>
                        </section>
        
                        <section class="pz-panel pz-finance-panel">
                            <div class="pz-section-head">
                                <div>
                                    <span class="pz-eyebrow">Financeiro</span>
                                    <h2>Resumo do mês</h2>
                                </div>
                                <a href="{{ $urls['financeiro'] ?? '#' }}">Abrir</a>
                            </div>
        
                            <div class="pz-finance-total">R$ {{ number_format($financeiro['total'] ?? 0, 2, ',', '.') }}</div>
                            <div class="pz-finance-split">
                                <span>Recebido <b>R$ {{ number_format($financeiro['recebido'] ?? 0, 2, ',', '.') }}</b></span>
                                <span>A receber <b>R$ {{ number_format($financeiro['aReceber'] ?? 0, 2, ',', '.') }}</b></span>
                            </div>
                        </section>
                    </aside>
                </section>
            </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const storageKey = 'prazzu.home.layout';
            const classic = document.querySelector('[data-home-layout="classic"]');
            const operational = document.querySelector('[data-home-layout="operational"]');
            const button = document.querySelector('[data-home-layout-toggle]');
            const title = document.querySelector('[data-home-layout-title]');
            const description = document.querySelector('[data-home-layout-description]');

            if (!classic || !operational || !button) {
                return;
            }

            function applyLayout(layout) {
                const isOperational = layout === 'operational';

                classic.classList.toggle('is-hidden', isOperational);
                operational.classList.toggle('is-hidden', !isOperational);

                if (title) {
                    title.textContent = isOperational ? 'Home operacional' : 'Home clássica';
                }

                if (description) {
                    description.textContent = isOperational
                        ? 'Modo ClickUp com pendências, vencimentos, aprovações, comentários e atalhos.'
                        : 'Você está vendo a Home antiga, com todos os indicadores originais preservados.';
                }

                button.textContent = isOperational ? 'Voltar para Home clássica' : 'Ver Home operacional';
                localStorage.setItem(storageKey, isOperational ? 'operational' : 'classic');
            }

            applyLayout(localStorage.getItem(storageKey) === 'operational' ? 'operational' : 'classic');

            button.addEventListener('click', function () {
                const current = localStorage.getItem(storageKey) === 'operational' ? 'operational' : 'classic';
                applyLayout(current === 'operational' ? 'classic' : 'operational');
            });
        });
    </script>
</x-filament-panels::page>