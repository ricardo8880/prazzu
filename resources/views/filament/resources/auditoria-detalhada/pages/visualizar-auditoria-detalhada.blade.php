<x-filament-panels::page>
    <link rel="stylesheet" href="{{ asset('css/auditoria-detalhada.css') }}">

    @php($metricas = $this->metricas())
    @php($eventos = $this->eventos())
    @php($usuarios = $this->usuariosMaisAtivos())
    @php($modulos = $this->modulosAuditados())
    @php($empresas = $this->empresasAuditadas())
    @php($suspeitas = $this->acoesSuspeitas())
    @php($recentes = $this->registrosRecentes())
    @php($filtros = $this->filtros())
    @php($filtrosAtivos = $this->filtrosAtivos())

    <div class="ad-page">
        <section class="ad-hero ad-hero--clean">
            <div>
                <div class="ad-eyebrow">Auditoria completa</div>
                <h2 class="ad-title">Entenda quem mexeu, quando mexeu e o que mudou</h2>
                <p class="ad-subtitle">
                    Esta tela transforma o log técnico em uma leitura operacional: usuário, empresa, módulo,
                    campo alterado, valor anterior, valor novo, IP e eventos sensíveis em um só lugar.
                </p>

                <div class="ad-help-grid" aria-label="Como ler a auditoria">
                    <span><strong>1</strong> Quem fez</span>
                    <span><strong>2</strong> Quando fez</span>
                    <span><strong>3</strong> Onde alterou</span>
                    <span><strong>4</strong> Antes e depois</span>
                </div>
            </div>

            <div class="ad-hero-actions">
                @if ($this->canExportAuditoria())
                    <a href="{{ $this->exportUrl() }}" class="ad-button ad-button--primary">
                        Exportar recorte atual
                    </a>
                @endif
                <a href="{{ url()->current() }}" class="ad-button ad-button--ghost">
                    Limpar filtros
                </a>
            </div>
        </section>

        <form method="GET" action="{{ url()->current() }}" class="ad-filters ad-filters--ux">
            <div class="ad-filter-field ad-filter-field--wide">
                <label for="busca">Buscar na auditoria</label>
                <input id="busca" name="busca" type="search" value="{{ $filtros['busca'] ?? '' }}" placeholder="Usuário, empresa, campo, valor, IP, módulo ou evento">
            </div>

            <div class="ad-filter-field">
                <label for="periodo">Período</label>
                <select id="periodo" name="periodo">
                    <option value="" @selected(($filtros['periodo'] ?? '') === '')>Todo período</option>
                    <option value="hoje" @selected(($filtros['periodo'] ?? '') === 'hoje')>Hoje</option>
                    <option value="7" @selected(($filtros['periodo'] ?? '') === '7')>Últimos 7 dias</option>
                    <option value="30" @selected(($filtros['periodo'] ?? '') === '30')>Últimos 30 dias</option>
                </select>
            </div>

            <div class="ad-filter-field">
                <label for="evento">Tipo de ação</label>
                <select id="evento" name="evento">
                    <option value="">Todas</option>
                    <option value="created" @selected(($filtros['evento'] ?? '') === 'created')>Criado</option>
                    <option value="updated" @selected(($filtros['evento'] ?? '') === 'updated')>Alterado</option>
                    <option value="deleted" @selected(($filtros['evento'] ?? '') === 'deleted')>Excluído</option>
                    <option value="login.success" @selected(($filtros['evento'] ?? '') === 'login.success')>Login realizado</option>
                    <option value="login.failed" @selected(($filtros['evento'] ?? '') === 'login.failed')>Falha de login</option>
                    <option value="auditoria.exported" @selected(($filtros['evento'] ?? '') === 'auditoria.exported')>Auditoria exportada</option>
                </select>
            </div>

            <div class="ad-filter-field">
                <label for="user_id">Usuário</label>
                <select id="user_id" name="user_id">
                    <option value="">Todos</option>
                    @foreach($this->usuariosFiltro() as $usuario)
                        <option value="{{ $usuario->id }}" @selected((string) ($filtros['user_id'] ?? '') === (string) $usuario->id)>{{ $usuario->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="ad-filter-field">
                <label for="empresa_id">Empresa</label>
                <select id="empresa_id" name="empresa_id">
                    <option value="">Todas</option>
                    @foreach($this->empresasFiltro() as $empresa)
                        <option value="{{ $empresa->id }}" @selected((string) ($filtros['empresa_id'] ?? '') === (string) $empresa->id)>
                            {{ $empresa->razao_social ?: $empresa->nome_fantasia }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="ad-filter-field">
                <label for="modulo">Área do sistema</label>
                <select id="modulo" name="modulo">
                    <option value="">Todas</option>
                    @foreach($this->modulosFiltro() as $modulo)
                        <option value="{{ $modulo['value'] }}" @selected(($filtros['modulo'] ?? '') === $modulo['value'])>{{ $modulo['label'] }}</option>
                    @endforeach
                </select>
            </div>

            <label class="ad-check-filter">
                <input type="checkbox" name="suspeito" value="1" @checked($filtros['suspeito'] ?? false)>
                <span>Somente ações sensíveis</span>
            </label>

            <div class="ad-filter-actions">
                <button type="submit" class="ad-button ad-button--primary">Aplicar</button>
            </div>

            @if(! empty($filtrosAtivos))
                <div class="ad-active-filters">
                    <strong>Filtros ativos:</strong>
                    @foreach($filtrosAtivos as $filtroAtivo)
                        <span>{{ $filtroAtivo }}</span>
                    @endforeach
                </div>
            @endif
        </form>

        <section class="ad-metrics ad-metrics--five">
            <article class="ad-metric-card"><span>Total no filtro</span><strong>{{ number_format((int) ($metricas['total'] ?? 0), 0, ',', '.') }}</strong><small>Tudo que corresponde ao recorte atual</small></article>
            <article class="ad-metric-card ad-metric-card--info"><span>Hoje</span><strong>{{ number_format((int) ($metricas['hoje'] ?? 0), 0, ',', '.') }}</strong><small>Movimentações do dia</small></article>
            <article class="ad-metric-card ad-metric-card--warning"><span>Alterações</span><strong>{{ number_format((int) ($metricas['alteracoes'] ?? 0), 0, ',', '.') }}</strong><small>Campos que mudaram de valor</small></article>
            <article class="ad-metric-card ad-metric-card--danger"><span>Exclusões</span><strong>{{ number_format((int) ($metricas['exclusoes'] ?? 0), 0, ',', '.') }}</strong><small>Registros removidos</small></article>
            <article class="ad-metric-card ad-metric-card--critical"><span>Revisar</span><strong>{{ number_format((int) ($metricas['suspeitas'] ?? 0), 0, ',', '.') }}</strong><small>Senhas, permissões, status, exportações e falhas</small></article>
        </section>

        <section class="ad-panel ad-panel--large ad-focus-panel">
            <div class="ad-panel-header">
                <div>
                    <h3>Linha do tempo das alterações</h3>
                    <p>Leitura direta: quem fez, em qual empresa, qual campo mudou e comparação antes/depois.</p>
                </div>
                <div class="ad-panel-counter">{{ number_format((int) $recentes->count(), 0, ',', '.') }} recentes</div>
            </div>

            @if($recentes->isEmpty())
                <div class="ad-empty">Nenhuma movimentação encontrada para os filtros aplicados.</div>
            @else
                <div class="ad-timeline">
                    @foreach($recentes as $registro)
                        <div class="ad-timeline-item ad-timeline-item--ux">
                            <div class="ad-avatar" title="{{ $registro->user?->name ?? 'Sistema' }}">{{ $this->iniciaisUsuario($registro) }}</div>

                            <div class="ad-timeline-content">
                                <div class="ad-timeline-top">
                                    <div class="ad-timeline-badges">
                                        <span class="ad-badge {{ $this->eventoClasse($registro->evento) }}">{{ $this->eventoLabel($registro->evento) }}</span>
                                        <span class="ad-badge {{ $this->suspeitoClasse($registro) }}">{{ $this->suspeitoLabel($registro) }}</span>
                                    </div>
                                    <time>{{ $this->dataHumana($registro->created_at) }}</time>
                                </div>

                                <h4>{{ $this->resumoAcao($registro) }}</h4>

                                <div class="ad-timeline-meta">
                                    <span>Usuário: {{ $registro->user?->name ?? 'Sistema' }}</span>
                                    <span>Empresa: {{ $this->nomeEmpresa($registro) }}</span>
                                    <span>Módulo: {{ $this->moduloLabel($registro->auditable_type) }}</span>
                                    <span>Registro: {{ $this->registroLabel($registro) }}</span>
                                    <span>IP: {{ $registro->ip ?: '-' }}</span>
                                </div>

                                <div class="ad-diff ad-diff--ux">
                                    <div>
                                        <span>Valor anterior</span>
                                        <strong>{{ $this->valorRegistro($registro->valor_anterior, $registro->campo) }}</strong>
                                    </div>
                                    <div>
                                        <span>Valor novo</span>
                                        <strong>{{ $this->valorRegistro($registro->valor_novo, $registro->campo) }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="ad-panel">
            <div class="ad-panel-header">
                <div>
                    <h3>Ações que merecem revisão</h3>
                    <p>Eventos que podem impactar segurança, permissão, status, senha, exportação ou integração.</p>
                </div>
                <div class="ad-panel-counter">{{ number_format((int) $suspeitas->count(), 0, ',', '.') }} recentes</div>
            </div>

            @if($suspeitas->isEmpty())
                <div class="ad-empty">Nenhuma ação sensível encontrada no recorte atual.</div>
            @else
                <div class="ad-sensitive-grid">
                    @foreach($suspeitas as $registro)
                        <article class="ad-sensitive-card">
                            <div class="ad-sensitive-top">
                                <span class="ad-badge ad-badge--danger">Revisar</span>
                                <time>{{ optional($registro->created_at)->format('d/m/Y H:i') ?: '-' }}</time>
                            </div>
                            <h4>{{ $this->registroLabel($registro) }}</h4>
                            <p>{{ $this->resumoAcao($registro) }}</p>
                            <div class="ad-timeline-meta">
                                <span>{{ $registro->user?->name ?? 'Sistema' }}</span>
                                <span>{{ $this->nomeEmpresa($registro) }}</span>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="ad-layout ad-layout--balanced">
            <article class="ad-panel">
                <div class="ad-panel-header"><div><h3>Eventos por tipo</h3><p>Ajuda a entender se o recorte tem mais criações, alterações, exclusões ou eventos manuais.</p></div></div>
                @if($eventos->isEmpty())
                    <div class="ad-empty">Nenhum evento encontrado para os filtros aplicados.</div>
                @else
                    <div class="ad-chart">
                        @foreach($eventos as $evento)
                            <div class="ad-chart-row">
                                <div class="ad-chart-label"><span class="ad-badge {{ $evento['classe'] }}">{{ $evento['label'] }}</span></div>
                                <div class="ad-chart-track"><div class="ad-chart-bar" style="width: {{ $evento['percentual'] }}%"></div></div>
                                <strong>{{ number_format((int) $evento['valor'], 0, ',', '.') }}</strong>
                            </div>
                        @endforeach
                    </div>
                @endif
            </article>

            <article class="ad-panel">
                <div class="ad-panel-header"><div><h3>Usuários mais ativos</h3><p>Quem mais gerou movimentações no período filtrado.</p></div></div>
                @if($usuarios->isEmpty())
                    <div class="ad-empty">Sem usuários auditados.</div>
                @else
                    <div class="ad-ranking">
                        @foreach($usuarios as $usuario)
                            <div class="ad-ranking-row ad-ranking-row--stacked"><span>{{ $usuario['nome'] }}</span><small>Última ação: {{ $usuario['ultima'] }}</small><strong>{{ number_format((int) $usuario['total'], 0, ',', '.') }}</strong></div>
                        @endforeach
                    </div>
                @endif
            </article>
        </section>

        <section class="ad-layout ad-layout--balanced">
            <article class="ad-panel">
                <div class="ad-panel-header"><div><h3>Auditoria por empresa</h3><p>Empresas com mais movimentações registradas.</p></div></div>
                @if($empresas->isEmpty())
                    <div class="ad-empty">Nenhuma empresa encontrada.</div>
                @else
                    <div class="ad-ranking">
                        @foreach($empresas as $empresa)
                            <div class="ad-ranking-row"><span>{{ $empresa['nome'] }}</span><strong>{{ number_format((int) $empresa['total'], 0, ',', '.') }}</strong></div>
                        @endforeach
                    </div>
                @endif
            </article>

            <article class="ad-panel">
                <div class="ad-panel-header"><div><h3>Áreas mais movimentadas</h3><p>Módulos do sistema com maior volume de rastreio.</p></div></div>
                @if($modulos->isEmpty())
                    <div class="ad-empty">Nenhum módulo encontrado.</div>
                @else
                    <div class="ad-ranking">
                        @foreach($modulos as $modulo)
                            <div class="ad-ranking-row"><span>{{ $modulo['nome'] }}</span><strong>{{ number_format((int) $modulo['total'], 0, ',', '.') }}</strong></div>
                        @endforeach
                    </div>
                @endif
            </article>
        </section>
    </div>
</x-filament-panels::page>
