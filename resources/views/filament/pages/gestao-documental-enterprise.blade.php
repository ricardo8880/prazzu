<x-filament-panels::page>
    <link rel="stylesheet" href="{{ asset('css/gestao-documental-enterprise.css') }}?v={{ file_exists(public_path('css/gestao-documental-enterprise.css')) ? filemtime(public_path('css/gestao-documental-enterprise.css')) : time() }}">

    @php
        $resumo = $resumo ?? [];
        $opcoes = $opcoes ?? [];
        $filtros = $filtros ?? [];
        $documentos = collect($documentos ?? []);
        $acaoRapida = collect($acaoRapida ?? []);
        $porPrioridade = collect($porPrioridade ?? []);
        $porEmpresa = collect($porEmpresa ?? []);
        $scoreGeral = $scoreGeral ?? 100;
        $hasFiltros = collect($filtros)->filter(fn ($value) => filled($value))->isNotEmpty();
    @endphp

    <div class="gd-page">
        <section class="gd-hero">
            <div class="gd-hero-content">
                <div class="gd-eyebrow">Visão operacional real</div>
                <h1 class="gd-title">Gestão documental</h1>
                <p class="gd-subtitle">
                    Priorize vencidos, próximos vencimentos, documentos sem anexo, sem responsável, pendências de aprovação e itens que precisam de uma ação agora.
                </p>
            </div>

            <div class="gd-hero-actions">
                <a href="{{ $novoDocumentoUrl }}" class="gd-btn gd-btn-primary">Novo documento</a>
                <a href="{{ $listaDocumentosUrl }}" class="gd-btn">Lista completa</a>
            </div>
        </section>

        <section class="gd-command-center">
            <div class="gd-score-card">
                <div>
                    <div class="gd-score-label">Saúde documental</div>
                    <div class="gd-score-value">{{ $scoreGeral }}%</div>
                    <div class="gd-score-help">Calculado com dados reais: prazo, responsável, anexo, assinatura, aprovação e versionamento.</div>
                </div>
                <div class="gd-score-bar" aria-label="Saúde documental">
                    <span style="width: {{ max(0, min(100, (int) $scoreGeral)) }}%"></span>
                </div>
            </div>

            <div class="gd-today-card">
                <span>Fila de ação</span>
                <strong>{{ number_format((int) (($resumo['vencidos'] ?? 0) + ($resumo['semResponsavel'] ?? 0) + ($resumo['semArquivo'] ?? 0) + ($resumo['aprovacaoPendente'] ?? 0)), 0, ',', '.') }}</strong>
                <small>Problemas que podem travar a rotina documental.</small>
            </div>
        </section>

        <section class="gd-kpis">
            <a class="gd-kpi" href="{{ request()->fullUrlWithQuery(['situacao' => null]) }}">
                <span class="gd-kpi-label">Documentos</span>
                <strong>{{ number_format((int) ($resumo['total'] ?? 0), 0, ',', '.') }}</strong>
                <small>Total visível para seu usuário.</small>
            </a>

            <a class="gd-kpi gd-kpi-danger" href="{{ request()->fullUrlWithQuery(['situacao' => 'vencido']) }}">
                <span class="gd-kpi-label">Vencidos</span>
                <strong>{{ number_format((int) ($resumo['vencidos'] ?? 0), 0, ',', '.') }}</strong>
                <small>Regularização imediata.</small>
            </a>

            <a class="gd-kpi gd-kpi-warning" href="{{ request()->fullUrlWithQuery(['situacao' => 'vence_7']) }}">
                <span class="gd-kpi-label">Vencem em 7 dias</span>
                <strong>{{ number_format((int) ($resumo['vencem7'] ?? 0), 0, ',', '.') }}</strong>
                <small>Acompanhar esta semana.</small>
            </a>

            <a class="gd-kpi gd-kpi-warning" href="{{ request()->fullUrlWithQuery(['situacao' => 'vence_30']) }}">
                <span class="gd-kpi-label">Vencem em 30 dias</span>
                <strong>{{ number_format((int) ($resumo['vencem30'] ?? 0), 0, ',', '.') }}</strong>
                <small>Prevenção de atraso.</small>
            </a>

            <a class="gd-kpi gd-kpi-danger" href="{{ request()->fullUrlWithQuery(['situacao' => 'sem_arquivo']) }}">
                <span class="gd-kpi-label">Sem anexo</span>
                <strong>{{ number_format((int) ($resumo['semArquivo'] ?? 0), 0, ',', '.') }}</strong>
                <small>Documento sem evidência.</small>
            </a>

            <a class="gd-kpi gd-kpi-danger" href="{{ request()->fullUrlWithQuery(['situacao' => 'sem_responsavel']) }}">
                <span class="gd-kpi-label">Sem responsável</span>
                <strong>{{ number_format((int) ($resumo['semResponsavel'] ?? 0), 0, ',', '.') }}</strong>
                <small>Sem dono operacional.</small>
            </a>

            <a class="gd-kpi gd-kpi-warning" href="{{ request()->fullUrlWithQuery(['situacao' => 'aprovacao_pendente']) }}">
                <span class="gd-kpi-label">Aprovação pendente</span>
                <strong>{{ number_format((int) ($resumo['aprovacaoPendente'] ?? 0), 0, ',', '.') }}</strong>
                <small>Aguardando decisão.</small>
            </a>

            <a class="gd-kpi gd-kpi-success" href="{{ request()->fullUrlWithQuery(['situacao' => null]) }}">
                <span class="gd-kpi-label">Com assinatura</span>
                <strong>{{ number_format((int) ($resumo['assinados'] ?? 0), 0, ',', '.') }}</strong>
                <small>Com registro de assinatura.</small>
            </a>
        </section>

        <form class="gd-card gd-filters" method="GET">
            <div class="gd-field gd-field-search">
                <label for="busca">Buscar</label>
                <input id="busca" name="busca" value="{{ $filtros['busca'] ?? '' }}" type="search" placeholder="Título, contrato, cliente, status, responsável...">
            </div>

            <div class="gd-field">
                <label for="empresa_id">Empresa</label>
                <select id="empresa_id" name="empresa_id">
                    <option value="">Todas</option>
                    @foreach(($opcoes['empresas'] ?? []) as $id => $nome)
                        <option value="{{ $id }}" @selected((string) ($filtros['empresa_id'] ?? '') === (string) $id)>{{ $nome }}</option>
                    @endforeach
                </select>
            </div>

            <div class="gd-field">
                <label for="responsavel_id">Responsável</label>
                <select id="responsavel_id" name="responsavel_id">
                    <option value="">Todos</option>
                    @foreach(($opcoes['responsaveis'] ?? []) as $id => $nome)
                        <option value="{{ $id }}" @selected((string) ($filtros['responsavel_id'] ?? '') === (string) $id)>{{ $nome }}</option>
                    @endforeach
                </select>
            </div>

            <div class="gd-field">
                <label for="tipo">Tipo</label>
                <select id="tipo" name="tipo">
                    <option value="">Todos</option>
                    @foreach(($opcoes['tipos'] ?? []) as $id => $nome)
                        <option value="{{ $id }}" @selected((string) ($filtros['tipo'] ?? '') === (string) $id)>{{ $nome }}</option>
                    @endforeach
                </select>
            </div>

            <div class="gd-field">
                <label for="prioridade">Prioridade</label>
                <select id="prioridade" name="prioridade">
                    <option value="">Todas</option>
                    @foreach(($opcoes['prioridades'] ?? []) as $id => $nome)
                        <option value="{{ $id }}" @selected((string) ($filtros['prioridade'] ?? '') === (string) $id)>{{ $nome }}</option>
                    @endforeach
                </select>
            </div>

            <div class="gd-field">
                <label for="situacao">Situação</label>
                <select id="situacao" name="situacao">
                    <option value="">Todas</option>
                    @foreach(($opcoes['situacoes'] ?? []) as $id => $nome)
                        <option value="{{ $id }}" @selected((string) ($filtros['situacao'] ?? '') === (string) $id)>{{ $nome }}</option>
                    @endforeach
                </select>
            </div>

            <div class="gd-field">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="">Todos</option>
                    @foreach(($opcoes['status'] ?? []) as $id => $nome)
                        <option value="{{ $id }}" @selected((string) ($filtros['status'] ?? '') === (string) $id)>{{ $nome }}</option>
                    @endforeach
                </select>
            </div>

            <div class="gd-field">
                <label for="ordenacao">Ordenar</label>
                <select id="ordenacao" name="ordenacao">
                    @foreach(($opcoes['ordenacoes'] ?? []) as $id => $nome)
                        <option value="{{ $id }}" @selected((string) ($filtros['ordenacao'] ?? 'prioridade') === (string) $id)>{{ $nome }}</option>
                    @endforeach
                </select>
            </div>

            <div class="gd-filter-actions">
                <button class="gd-btn gd-btn-primary" type="submit">Filtrar</button>
                @if($hasFiltros)
                    <a class="gd-btn" href="{{ url()->current() }}">Limpar</a>
                @endif
            </div>
        </form>

        @if($acaoRapida->isNotEmpty())
            <section class="gd-card">
                <div class="gd-section-head">
                    <div>
                        <h2 class="gd-section-title">Ações rápidas</h2>
                        <p class="gd-section-subtitle">Fila automática com os documentos que mais precisam de atenção agora.</p>
                    </div>
                </div>

                <div class="gd-action-grid">
                    @foreach($acaoRapida as $item)
                        <a class="gd-action-card gd-border-{{ $item['tom'] }}" href="{{ $item['edit_url'] }}">
                            <span>{{ $item['status_documental'] }}</span>
                            <strong>{{ $item['titulo'] }}</strong>
                            <small>{{ $item['empresa_nome'] }} · {{ $item['situacao_prazo'] }}</small>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        @if($porPrioridade->isNotEmpty())
            <section class="gd-card gd-priority-section">
                <div class="gd-section-head">
                    <div>
                        <h2 class="gd-section-title">Documentos por prioridade</h2>
                        <p class="gd-section-subtitle">Use esta visão para atacar primeiro o que está atrasado, sem anexo ou sem responsável.</p>
                    </div>
                </div>

                <div class="gd-priority-grid">
                    @foreach($porPrioridade as $grupo)
                        <div class="gd-priority-lane">
                            <div class="gd-priority-head">
                                <strong>{{ $grupo['prioridade'] }}</strong>
                                <span>{{ $grupo['total'] }} item(ns)</span>
                            </div>
                            <div class="gd-priority-stats">
                                <span>{{ $grupo['criticos'] }} crítico(s)</span>
                                <span>{{ $grupo['sem_anexo'] }} sem anexo</span>
                                <span>{{ $grupo['sem_responsavel'] }} sem responsável</span>
                            </div>
                            <div class="gd-priority-items">
                                @foreach($grupo['itens'] as $item)
                                    <a href="{{ $item['edit_url'] }}" class="gd-priority-item gd-border-{{ $item['tom'] }}">
                                        <b>{{ $item['titulo'] }}</b>
                                        <small>{{ $item['situacao_prazo'] }} · {{ $item['status_documental'] }}</small>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <section class="gd-grid-main">
            <div class="gd-left">
                <div class="gd-section-head gd-card gd-section-card">
                    <div>
                        <h2 class="gd-section-title">Listagem operacional</h2>
                        <p class="gd-section-subtitle">{{ $documentos->count() }} documento(s) encontrados. Vencidos e críticos aparecem primeiro.</p>
                    </div>
                </div>

                <div class="gd-list">
                    @forelse($documentos as $documento)
                        <article class="gd-doc gd-border-{{ $documento['tom'] }}">
                            <div class="gd-doc-main">
                                <div class="gd-doc-content">
                                    <div class="gd-badges">
                                        <span class="gd-badge gd-badge-{{ $documento['tom'] }}">{{ $documento['status_documental'] }}</span>
                                        <span class="gd-badge gd-badge-muted">{{ $documento['situacao_prazo'] }}</span>
                                        <span class="gd-badge {{ $documento['tem_arquivo'] ? 'gd-badge-success' : 'gd-badge-danger' }}">{{ $documento['anexos_count'] }} anexo(s)</span>
                                        <span class="gd-badge {{ $documento['sem_responsavel'] ? 'gd-badge-danger' : 'gd-badge-muted' }}">{{ $documento['responsavel_nome'] }}</span>
                                        <span class="gd-badge {{ $documento['assinatura'] === 'Assinado' ? 'gd-badge-success' : 'gd-badge-warning' }}">{{ $documento['assinatura'] }}</span>
                                        <span class="gd-badge gd-badge-muted">{{ $documento['aprovacao'] }}</span>
                                    </div>

                                    <h3 class="gd-doc-title">{{ $documento['titulo'] }}</h3>
                                    <p class="gd-doc-desc">{{ $documento['descricao'] }}</p>

                                    <div class="gd-meta">
                                        <div>
                                            <span>Empresa</span>
                                            <strong>{{ $documento['empresa_nome'] }}</strong>
                                        </div>
                                        <div>
                                            <span>Tipo</span>
                                            <strong>{{ $documento['tipo'] }}</strong>
                                        </div>
                                        <div>
                                            <span>Vencimento</span>
                                            <strong>{{ $documento['vencimento'] }}</strong>
                                        </div>
                                        <div>
                                            <span>Prioridade</span>
                                            <strong>{{ $documento['prioridade'] }}</strong>
                                        </div>
                                    </div>

                                    <div class="gd-workflow" aria-label="Fluxo documental">
                                        @foreach($documento['workflow'] as $etapa)
                                            <span class="{{ $etapa['ok'] ? 'is-ok' : 'is-pending' }}">{{ $etapa['label'] }}</span>
                                        @endforeach
                                    </div>

                                    @if(! empty($documento['pendencias']))
                                        <div class="gd-pendencias">
                                            @foreach($documento['pendencias'] as $pendencia)
                                                <span>{{ $pendencia }}</span>
                                            @endforeach
                                        </div>
                                    @endif

                                    @if(! empty($documento['timeline']))
                                        <div class="gd-timeline">
                                            @foreach($documento['timeline'] as $evento)
                                                <div class="gd-timeline-item">
                                                    <strong>{{ $evento['titulo'] ?: $evento['tipo'] }}</strong>
                                                    <span>{{ $evento['data'] }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                <aside class="gd-doc-side">
                                    <div class="gd-mini-score">
                                        <span>Score</span>
                                        <strong>{{ $documento['score'] }}%</strong>
                                    </div>

                                    <a href="{{ $documento['edit_url'] }}" class="gd-btn gd-btn-primary">Abrir / resolver</a>

                                    @if($documento['arquivo_url'])
                                        <a href="{{ $documento['arquivo_url'] }}" target="_blank" rel="noopener" class="gd-btn">Ver arquivo</a>
                                    @endif

                                    @if($documento['portal_url'])
                                        <a href="{{ $documento['portal_url'] }}" target="_blank" rel="noopener" class="gd-btn">Portal</a>
                                    @endif
                                </aside>
                            </div>
                        </article>
                    @empty
                        <div class="gd-empty">
                            <h3>{{ $hasFiltros ? 'Nenhum documento para estes filtros' : 'Nenhum documento cadastrado' }}</h3>
                            <p>
                                {{ $hasFiltros ? 'Tente remover algum filtro ou usar uma busca mais ampla.' : 'Cadastre o primeiro documento para começar a acompanhar vencimentos, responsáveis, anexos e aprovações.' }}
                            </p>
                            <div class="gd-empty-actions">
                                @if($hasFiltros)
                                    <a href="{{ url()->current() }}" class="gd-btn">Limpar filtros</a>
                                @endif
                                <a href="{{ $novoDocumentoUrl }}" class="gd-btn gd-btn-primary">Cadastrar documento</a>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>

            <aside class="gd-right">
                <section class="gd-card">
                    <h2 class="gd-section-title">Resumo por empresa</h2>
                    <p class="gd-section-subtitle">Empresas com menor score aparecem primeiro.</p>

                    <div class="gd-company-list">
                        @forelse($porEmpresa as $empresa)
                            <div class="gd-company">
                                <div>
                                    <strong>{{ $empresa['empresa'] }}</strong>
                                    <span>{{ $empresa['total'] }} documento(s) · {{ $empresa['criticos'] }} crítico(s)</span>
                                </div>
                                <b>{{ $empresa['score'] }}%</b>
                            </div>
                        @empty
                            <div class="gd-muted-box">Nenhuma empresa encontrada nos filtros atuais.</div>
                        @endforelse
                    </div>
                </section>

                <section class="gd-card">
                    <h2 class="gd-section-title">Pendências críticas</h2>
                    <div class="gd-critical-list">
                        <a href="{{ request()->fullUrlWithQuery(['situacao' => 'vencido']) }}"><span>Vencidos</span><strong>{{ $resumo['vencidos'] ?? 0 }}</strong></a>
                        <a href="{{ request()->fullUrlWithQuery(['situacao' => 'vence_7']) }}"><span>Vencem em 7 dias</span><strong>{{ $resumo['vencem7'] ?? 0 }}</strong></a>
                        <a href="{{ request()->fullUrlWithQuery(['situacao' => 'sem_responsavel']) }}"><span>Sem responsável</span><strong>{{ $resumo['semResponsavel'] ?? 0 }}</strong></a>
                        <a href="{{ request()->fullUrlWithQuery(['situacao' => 'sem_arquivo']) }}"><span>Sem anexo</span><strong>{{ $resumo['semArquivo'] ?? 0 }}</strong></a>
                        <a href="{{ request()->fullUrlWithQuery(['situacao' => 'aprovacao_pendente']) }}"><span>Aprovação pendente</span><strong>{{ $resumo['aprovacaoPendente'] ?? 0 }}</strong></a>
                    </div>
                </section>
            </aside>
        </section>
    </div>
</x-filament-panels::page>
