<x-filament-panels::page>
    @php
        $urls = $dashboard['urls'] ?? [];
        $usuario = explode(' ', $dashboard['usuario'] ?? 'Usuário')[0];
        $resumoHoje = collect($dashboard['resumoHoje'] ?? [])->keyBy('label');
        $minhasPendencias = collect($dashboard['minhasPendencias'] ?? []);
        $vencimentosProximos = collect($dashboard['vencimentosProximos'] ?? []);
        $aprovacoesAguardando = collect($dashboard['aprovacoesAguardando'] ?? []);
        $itensAtrasados = collect($dashboard['itensAtrasados'] ?? []);
        $resumoEmpresas = collect($dashboard['resumoEmpresas'] ?? []);
        $notificacoes = collect($dashboard['notificacoes'] ?? []);
        $documentosVencidos = collect($dashboard['documentosVencidos'] ?? []);
        $documentosVencendo = collect($dashboard['documentosVencendo'] ?? []);
        $atividades = collect($dashboard['atividades'] ?? []);
        $filaPrioridade = collect($dashboard['filaPrioridade']['itens'] ?? [])->take(5);
        $filaPrioridadeTotal = (int) ($dashboard['filaPrioridade']['total'] ?? $filaPrioridade->count());
        $uxNavigation = collect($uxNavigation ?? []);

        $obrigacoesVencidas = $resumoHoje->get('Atrasados', []);
        $vencimentosSemana = $resumoHoje->get('Vencem em 7 dias', []);
        $aprovacoesPendentes = $resumoHoje->get('Aprovações', []);
        $pendenciasResumo = $resumoHoje->get('Pendências', []);
        $clientesEmRisco = $resumoEmpresas->filter(fn ($empresa) => in_array($empresa['tone'] ?? '', ['danger', 'warning'], true))->count();

        $dataReferencia = now()->locale('pt_BR')->translatedFormat('d \\d\\e F \\d\\e Y');
        $diaSemana = ucfirst(now()->locale('pt_BR')->translatedFormat('l'));

        $kpis = [
            ['label' => 'Pendências abertas', 'value' => $pendenciasResumo['value'] ?? $minhasPendencias->count(), 'hint' => 'Resumo da fila operacional', 'icon' => 'bi-list-check', 'tone' => 'warning', 'url' => $urls['minhasPendencias'] ?? '#'],
            ['label' => 'Atrasados', 'value' => $obrigacoesVencidas['value'] ?? $itensAtrasados->count(), 'hint' => 'Itens com risco imediato', 'icon' => 'bi-exclamation-octagon', 'tone' => 'danger', 'url' => $urls['prazos'] ?? '#'],
            ['label' => 'Aprovações', 'value' => $aprovacoesPendentes['value'] ?? $aprovacoesAguardando->count(), 'hint' => 'Decisões aguardando validação', 'icon' => 'bi-patch-check', 'tone' => 'info', 'url' => $urls['centralAprovacoes'] ?? '#'],
            ['label' => 'Documentos críticos', 'value' => $documentosVencidos->count() + $documentosVencendo->count(), 'hint' => 'Vencidos ou perto do vencimento', 'icon' => 'bi-folder2-open', 'tone' => 'purple', 'url' => $urls['documentos'] ?? '#'],
            ['label' => 'Vencem em 7 dias', 'value' => $vencimentosSemana['value'] ?? $vencimentosProximos->count(), 'hint' => 'Prazos próximos', 'icon' => 'bi-calendar-event', 'tone' => 'amber', 'url' => $urls['calendario'] ?? '#'],
            ['label' => 'Clientes em risco', 'value' => $clientesEmRisco, 'hint' => 'Empresas que exigem atenção', 'icon' => 'bi-shield-exclamation', 'tone' => 'slate', 'url' => $urls['clientes'] ?? '#'],
        ];

        $alertas = collect()
            ->merge($itensAtrasados->map(fn ($item) => ['tipo' => 'Atraso', 'titulo' => $item['titulo'] ?? 'Item atrasado', 'detalhe' => $item['empresa'] ?? 'Sem empresa vinculada', 'meta' => $item['tempo'] ?? ($item['data'] ?? 'Atrasado'), 'tone' => 'danger', 'url' => $item['url'] ?? ($urls['prazos'] ?? '#')]))
            ->merge($documentosVencidos->map(fn ($item) => ['tipo' => 'Documento', 'titulo' => $item['titulo'] ?? 'Documento vencido', 'detalhe' => $item['empresa'] ?? 'Sem empresa vinculada', 'meta' => $item['tempo'] ?? ($item['data'] ?? 'Vencido'), 'tone' => 'warning', 'url' => $item['url'] ?? ($urls['documentos'] ?? '#')]))
            ->merge($aprovacoesAguardando->map(fn ($item) => ['tipo' => 'Aprovação', 'titulo' => $item['titulo'] ?? 'Aprovação pendente', 'detalhe' => $item['empresa'] ?? 'Sem empresa vinculada', 'meta' => $item['tempo'] ?? 'Aguardando', 'tone' => 'info', 'url' => $item['url'] ?? ($urls['centralAprovacoes'] ?? '#')]))
            ->take(5);

        $atalhos = [
            ['label' => 'Resolver pendências', 'hint' => 'Abrir fila completa', 'icon' => 'bi-list-check', 'url' => $urls['minhasPendencias'] ?? '#'],
            ['label' => 'Ver documentos', 'hint' => 'Central documental', 'icon' => 'bi-folder2-open', 'url' => $urls['documentos'] ?? '#'],
            ['label' => 'Aprovar itens', 'hint' => 'Central de aprovações', 'icon' => 'bi-patch-check', 'url' => $urls['centralAprovacoes'] ?? '#'],
            ['label' => 'Consultar prazos', 'hint' => 'Calendário e SLA', 'icon' => 'bi-calendar-week', 'url' => $urls['calendario'] ?? ($urls['prazos'] ?? '#')],
            ['label' => 'Clientes', 'hint' => 'Carteira e atendimento', 'icon' => 'bi-buildings', 'url' => $urls['clientes'] ?? '#'],
            ['label' => 'Relatórios', 'hint' => 'Analisar indicadores', 'icon' => 'bi-graph-up-arrow', 'url' => $urls['relatorios'] ?? '#'],
        ];
    @endphp


    <div class="pz-home-exec">
        <section class="pz-hero">
            <div class="pz-hero-inner">
                <div>
                    <span class="pz-eyebrow"><i class="bi bi-speedometer2"></i> Home da Contabilidade</span>
                    <h1>Olá, {{ $usuario }}. Esta é a visão geral da operação.</h1>
                    <p>A Home agora resume o que importa e direciona cada ação para a aba correta. Pendências, documentos, aprovações e prazos continuam concentrados nas telas responsáveis.</p>
                </div>
                <div class="pz-hero-actions">
                    <a class="pz-btn pz-btn-primary" href="{{ $urls['minhasPendencias'] ?? '#' }}"><i class="bi bi-list-check"></i> Abrir pendências</a>
                    <a class="pz-btn pz-btn-ghost" href="{{ request()->fullUrl() }}"><i class="bi bi-arrow-clockwise"></i> Atualizar</a>
                </div>
            </div>
            <div class="pz-hero-inner pz-hero-inner--spaced">
                <span class="pz-eyebrow"><i class="bi bi-calendar3"></i> {{ $dataReferencia }}</span>
                <span class="pz-eyebrow"><i class="bi bi-clock-history"></i> {{ $diaSemana }}</span>
            </div>
        </section>

        <section class="pz-kpi-grid" aria-label="Indicadores executivos da contabilidade">
            @foreach($kpis as $kpi)
                <a href="{{ $kpi['url'] }}" class="pz-kpi pz-tone-{{ $kpi['tone'] }}">
                    <div class="pz-kpi-top">
                        <div class="pz-icon-box"><i class="bi {{ $kpi['icon'] }}"></i></div>
                        <i class="bi bi-arrow-up-right"></i>
                    </div>
                    <strong>{{ $kpi['value'] }}</strong>
                    <span>{{ $kpi['label'] }}</span>
                    <small>{{ $kpi['hint'] }}</small>
                </a>
            @endforeach
        </section>

        <section class="pz-dashboard-grid">
            <div class="pz-panel">
                <div class="pz-panel-head">
                    <div><h2>Alertas prioritários</h2><p>Resumo dos itens que exigem atenção. A resolução acontece na aba correta.</p></div>
                    <a href="{{ $urls['minhasPendencias'] ?? '#' }}">Ver fila completa</a>
                </div>
                <div class="pz-list">
                    @forelse($alertas as $alerta)
                        <a href="{{ $alerta['url'] ?? '#' }}" class="pz-row">
                            <span class="pz-badge pz-badge-{{ $alerta['tone'] ?? 'info' }}">{{ $alerta['tipo'] ?? 'Alerta' }}</span>
                            <div>
                                <div class="pz-row-title">{{ $alerta['titulo'] ?? 'Item sem título' }}</div>
                                <div class="pz-row-detail">{{ $alerta['detalhe'] ?? 'Sem empresa vinculada' }}</div>
                            </div>
                            <div class="pz-row-meta">{{ $alerta['meta'] ?? '-' }}</div>
                        </a>
                    @empty
                        <div class="pz-empty"><i class="bi bi-check2-circle"></i> Nenhum alerta crítico encontrado para hoje.</div>
                    @endforelse
                </div>
            </div>

            <div class="pz-panel">
                <div class="pz-panel-head">
                    <div><h2>Atalhos orientados</h2><p>Acesso rápido sem duplicar conteúdo operacional.</p></div>
                </div>
                <div class="pz-shortcuts">
                    @foreach($atalhos as $atalho)
                        <a href="{{ $atalho['url'] }}" class="pz-shortcut">
                            <i class="bi {{ $atalho['icon'] }}"></i>
                            <span><strong>{{ $atalho['label'] }}</strong><small>{{ $atalho['hint'] }}</small></span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>



        <section class="pz-panel pz-ux-map" aria-label="Mapa de navegação do Prazzu">
            <div class="pz-panel-head">
                <div><h2>Mapa rápido do sistema</h2><p>Use estes grupos como caminho oficial. Cada assunto aponta para a tela dona do fluxo.</p></div>
            </div>
            <div class="pz-ux-map-grid">
                @foreach($uxNavigation as $cluster)
                    <article class="pz-ux-cluster">
                        <div class="pz-ux-cluster-head">
                            <span class="pz-icon-box"><i class="bi {{ $cluster['icon'] ?? 'bi-grid' }}"></i></span>
                            <div>
                                <strong>{{ $cluster['label'] ?? 'Grupo' }}</strong>
                                <small>{{ $cluster['hint'] ?? '' }}</small>
                            </div>
                        </div>
                        <div class="pz-ux-links">
                            @foreach(($cluster['items'] ?? []) as $item)
                                <a href="{{ $item['url'] ?? '#' }}" @class(['pz-ux-link', 'pz-ux-link--active' => $item['active'] ?? false])>
                                    <span>{{ $item['label'] ?? 'Acessar' }}</span>
                                    <small>{{ $item['hint'] ?? 'Abrir tela' }}</small>
                                </a>
                            @endforeach
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="pz-dashboard-grid">
            <div class="pz-panel">
                <div class="pz-panel-head">
                    <div><h2>Próximos vencimentos</h2><p>Somente um resumo. A gestão completa fica em Calendário/Prazos.</p></div>
                    <a href="{{ $urls['calendario'] ?? ($urls['prazos'] ?? '#') }}">Abrir calendário</a>
                </div>
                <div class="pz-list">
                    @forelse($vencimentosProximos->take(5) as $item)
                        <a href="{{ $item['url'] ?? ($urls['prazos'] ?? '#') }}" class="pz-row">
                            <span class="pz-badge pz-badge-warning">Prazo</span>
                            <div><div class="pz-row-title">{{ $item['titulo'] ?? 'Vencimento operacional' }}</div><div class="pz-row-detail">{{ $item['empresa'] ?? 'Sem empresa vinculada' }}</div></div>
                            <div class="pz-row-meta">{{ $item['data'] ?? ($item['tempo'] ?? '-') }}</div>
                        </a>
                    @empty
                        <div class="pz-empty"><i class="bi bi-calendar-check"></i> Nenhum vencimento próximo encontrado.</div>
                    @endforelse
                </div>
            </div>

            <div class="pz-panel">
                <div class="pz-panel-head">
                    <div><h2>Atividade recente</h2><p>Últimos movimentos relevantes da operação.</p></div>
                </div>
                <div class="pz-activity">
                    @forelse($atividades->take(5) as $atividade)
                        <a href="{{ $atividade['url'] ?? '#' }}" class="pz-activity-item pz-link-reset">
                            <span class="pz-dot"></span>
                            <span><strong>{{ $atividade['titulo'] ?? ($atividade['texto'] ?? 'Atividade registrada') }}</strong><small>{{ $atividade['empresa'] ?? ($atividade['quando'] ?? 'Agora') }}</small></span>
                        </a>
                    @empty
                        <div class="pz-empty"><i class="bi bi-clock-history"></i> Nenhuma atividade recente para exibir.</div>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="pz-footer-note">
            <strong>Regra de UX aplicada no Lote 12:</strong> a Home e o topo usam um mapa único de navegação. A tela inicial orienta, mas cada ação continua na aba dona do assunto.
        </section>
    </div>
</x-filament-panels::page>
