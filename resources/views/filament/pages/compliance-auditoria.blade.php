<x-filament-panels::page>

    @php
        $filters = $filters ?? [];
        $filterOptions = $data['filterOptions'] ?? ['actions' => [], 'users' => [], 'companies' => []];
        $historyContext = $data['historyContext'] ?? ['active' => false];
        $dateFilter = (string) ($filters['dateFilter'] ?? '30');
        $fromDate = (string) ($filters['fromDate'] ?? '');
        $toDate = (string) ($filters['toDate'] ?? '');
        $userFilter = (string) ($filters['userFilter'] ?? 'todos');
        $companyFilter = (string) ($filters['companyFilter'] ?? 'todas');
        $actionFilter = (string) ($filters['actionFilter'] ?? 'todas');
        $searchFilter = (string) ($filters['searchFilter'] ?? '');
        $auditableTypeFilter = (string) ($filters['auditableType'] ?? '');
        $auditableIdFilter = (string) ($filters['auditableId'] ?? '');
        $hasActiveFilters = $dateFilter !== '30' || $fromDate !== '' || $toDate !== '' || $userFilter !== 'todos' || $companyFilter !== 'todas' || $actionFilter !== 'todas' || $searchFilter !== '' || $auditableTypeFilter !== '' || $auditableIdFilter !== '';
        $filterUrl = function (array $extra = [], array $forget = []) {
            $query = request()->query();
            foreach ($forget as $key) {
                unset($query[$key]);
            }
            foreach ($extra as $key => $value) {
                if ($value === null || $value === '' || $value === 'todos' || $value === 'todas') {
                    unset($query[$key]);
                    continue;
                }
                $query[$key] = $value;
            }
            return url()->current() . (count($query) ? '?' . http_build_query($query) : '');
        };

        $auditAbas = ['resumo', 'timeline', 'aprovacoes', 'investigacao'];
        $aba = (string) request('aba', 'resumo');
        if (! in_array($aba, $auditAbas, true)) {
            $aba = 'resumo';
        }

        $formatAuditLabel = fn ($value) => \App\Support\AuditoriaFormatter::modulo((string) $value);
        $formatAuditValue = fn ($value, $field = null) => \App\Support\AuditoriaFormatter::valor($value, $field);
        $formatAuditEvent = fn ($value) => \App\Support\AuditoriaFormatter::evento((string) $value);
        $formatAuditRecord = fn ($type, $id) => \App\Support\AuditoriaFormatter::registroCurto((string) $type, $id);
        $historyUrl = function (array $event) use ($filterUrl) {
            return $filterUrl([
                'auditableType' => $event['auditable_type_filter'] ?? '',
                'auditableId' => $event['auditable_id_filter'] ?? '',
                'dateFilter' => 'todos',
            ], ['fromDate', 'toDate', 'searchFilter', 'userFilter', 'companyFilter', 'actionFilter']);
        };

        $detalheMetricas = $detalheMetricas ?? [];
        $detalheRecentes = $detalheRecentes ?? collect();
        $detalheSuspeitas = $detalheSuspeitas ?? collect();
        $detalheUsuarios = $detalheUsuarios ?? collect();
        $detalheModulos = $detalheModulos ?? collect();
        $detalheEmpresas = $detalheEmpresas ?? collect();
        $detalheEventos = $detalheEventos ?? collect();
    @endphp

    <div class="compliance-page">
        <section class="compliance-hero">
            <div>
                <span><i class="bi bi-shield-check"></i> Auditoria e Rastreabilidade</span>
                <h1>Evidência, histórico e investigação</h1>
                <p>Esta aba não resolve pendências nem altera documentos. Ela mostra quem fez, quando fez, o que mudou e onde investigar o registro original.</p>
            </div>
            <div class="compliance-hero-actions compliance-hero-actions-export">
                @if ($this->canExportAuditoria())
                    <button type="button" class="compliance-export-button" wire:click="exportAuditoriaCsv" wire:loading.attr="disabled" wire:target="exportAuditoriaCsv"><i class="bi bi-filetype-csv"></i> Exportar CSV</button>
                    <button type="button" class="compliance-export-button compliance-export-button-primary" wire:click="exportAuditoriaExcel" wire:loading.attr="disabled" wire:target="exportAuditoriaExcel"><i class="bi bi-file-earmark-spreadsheet"></i> Exportar Excel</button>
                @endif
                <a href="{{ $filterUrl(['aba' => 'investigacao']) }}"><i class="bi bi-search"></i> Ver investigação</a>
            </div>
        </section>


        @if ($aba === 'resumo')
            <section class="compliance-stats">
                @foreach (($data['stats'] ?? []) as $stat)
                    <article class="compliance-stat"><span>{{ $stat['label'] }}</span><strong>{{ $stat['value'] }}</strong><small>{{ $stat['hint'] }}</small></article>
                @endforeach
            </section>
        @endif

        <section class="compliance-card compliance-filters">
            <header>
                <div>
                    <h2>Filtrar evidências de auditoria</h2>
                    <p>Use os filtros para localizar evidências. A ação operacional deve continuar na tela de origem do registro.</p>
                </div>
                @if ($hasActiveFilters)
                    <a class="compliance-link compliance-link-light" href="{{ url()->current() }}">Limpar filtros</a>
                @endif
            </header>

            <form method="GET" action="{{ url()->current() }}" class="compliance-filter-grid compliance-filter-grid-advanced">
                <input type="hidden" name="aba" value="{{ $aba }}">
                <label>
                    <span>Período rápido</span>
                    <select name="dateFilter">
                        <option value="7" @selected($dateFilter === '7')>Últimos 7 dias</option>
                        <option value="30" @selected($dateFilter === '30')>Últimos 30 dias</option>
                        <option value="90" @selected($dateFilter === '90')>Últimos 90 dias</option>
                        <option value="180" @selected($dateFilter === '180')>Últimos 180 dias</option>
                        <option value="365" @selected($dateFilter === '365')>Últimos 12 meses</option>
                        <option value="todos" @selected($dateFilter === 'todos')>Todo o histórico</option>
                    </select>
                </label>

                <label>
                    <span>Data inicial</span>
                    <input type="date" name="fromDate" value="{{ $fromDate }}">
                </label>

                <label>
                    <span>Data final</span>
                    <input type="date" name="toDate" value="{{ $toDate }}">
                </label>

                <label>
                    <span>Empresa</span>
                    <select name="companyFilter">
                        <option value="todas">Todas as empresas</option>
                        @foreach (($filterOptions['companies'] ?? []) as $company)
                            <option value="{{ $company['id'] }}" @selected($companyFilter === (string) $company['id'])>{{ $company['name'] }}</option>
                        @endforeach
                    </select>
                </label>

                <label>
                    <span>Usuário</span>
                    <select name="userFilter">
                        <option value="todos">Todos os usuários</option>
                        @foreach (($filterOptions['users'] ?? []) as $user)
                            <option value="{{ $user['id'] }}" @selected($userFilter === (string) $user['id'])>{{ $user['name'] }}</option>
                        @endforeach
                    </select>
                </label>

                <label>
                    <span>Tipo de ação</span>
                    <select name="actionFilter">
                        <option value="todas">Todos os eventos</option>
                        @foreach (($filterOptions['actions'] ?? []) as $action)
                            <option value="{{ $action }}" @selected($actionFilter === (string) $action)>{{ $formatAuditEvent($action) }}</option>
                        @endforeach
                    </select>
                </label>

                <label class="wide">
                    <span>Buscar na auditoria</span>
                    <input type="search" name="searchFilter" value="{{ $searchFilter }}" placeholder="Ex.: nome do usuário, empresa, status, IP, documento ou permissão">
                </label>

                @if ($auditableTypeFilter !== '')
                    <input type="hidden" name="auditableType" value="{{ $auditableTypeFilter }}">
                @endif
                @if ($auditableIdFilter !== '')
                    <input type="hidden" name="auditableId" value="{{ $auditableIdFilter }}">
                @endif

                @if (! empty($historyContext['active']))
                    <div class="compliance-active-scope compliance-history-scope wide">
                        <div>
                            <span>Histórico completo por entidade</span>
                            <strong>{{ $historyContext['record_label'] ?? $formatAuditRecord($auditableTypeFilter, $auditableIdFilter) }}</strong>
                            <small>{{ $historyContext['total'] ?? 0 }} evento(s) no histórico · {{ $historyContext['critical'] ?? 0 }} crítico(s) · {{ $historyContext['users'] ?? 0 }} usuário(s)</small>
                        </div>
                        <a href="{{ $filterUrl([], ['auditableType', 'auditableId']) }}">Remover foco do registro</a>
                    </div>
                @elseif ($auditableTypeFilter !== '' || $auditableIdFilter !== '')
                    <div class="compliance-active-scope wide">
                        <div>
                            <span>Histórico focado</span>
                            <strong>{{ $formatAuditRecord($auditableTypeFilter, $auditableIdFilter) }}</strong>
                        </div>
                        <a href="{{ $filterUrl([], ['auditableType', 'auditableId']) }}">Remover foco do registro</a>
                    </div>
                @endif

                <div class="compliance-filter-actions wide">
                    <button type="submit">Aplicar filtros</button>
                    <a href="{{ $filterUrl(['aba' => 'investigacao']) }}">Abrir visão limpa de investigação</a>
                </div>
            </form>
        </section>

        @if ($aba === 'resumo' && ! empty($historyContext['active']))
            <section class="compliance-card compliance-history-overview">
                <header>
                    <div>
                        <h2>Histórico completo do item</h2>
                        <p>Sequência completa de eventos registrados para {{ $historyContext['record_label'] ?? ($historyContext['module'] ?? 'Registro') }}.</p>
                    </div>
                    <a class="compliance-link compliance-link-light" href="{{ $filterUrl([], ['auditableType', 'auditableId']) }}">Voltar para auditoria geral</a>
                </header>
                <div class="compliance-history-metrics">
                    <article><span>Total de eventos</span><strong>{{ $historyContext['total'] ?? 0 }}</strong><small>Eventos vinculados ao mesmo registro</small></article>
                    <article><span>Eventos críticos</span><strong>{{ $historyContext['critical'] ?? 0 }}</strong><small>Classificados como alta criticidade</small></article>
                    <article><span>Usuários envolvidos</span><strong>{{ $historyContext['users'] ?? 0 }}</strong><small>Usuários que movimentaram o item</small></article>
                    <article><span>Período do histórico</span><strong>{{ $historyContext['first_date'] ?? '-' }}</strong><small>até {{ $historyContext['last_date'] ?? '-' }}</small></article>
                </div>
                @if (! empty($historyContext['events']))
                    <div class="compliance-history-events">
                        @foreach ($historyContext['events'] as $historyEvent)
                            <span>{{ $historyEvent['label'] }} <strong>{{ $historyEvent['count'] }}</strong></span>
                        @endforeach
                    </div>
                @endif
            </section>
        @endif

        @if ($aba === 'timeline')
        <section class="compliance-grid audit-single">
            <article class="compliance-card">
                <header><div><h2>{{ ! empty($historyContext['active']) ? 'Timeline do item selecionado' : 'Timeline de auditoria' }}</h2><p>{{ ! empty($historyContext['active']) ? 'Histórico completo do registro focado, ordenado pelos eventos mais recentes.' : 'Últimos eventos reais registrados no banco' . ($hasActiveFilters ? ' conforme os filtros aplicados' : '') . '.' }}</p></div></header>
                <div class="compliance-list">
                    @forelse (($data['timeline'] ?? []) as $event)
                        <div class="compliance-row compliance-row-actionable {{ ! empty($event['alert']) ? 'is-alerted' : '' }}">
                            <div>
                                <div class="compliance-event-title-row">
                                    <h3>{{ $event['title'] }}</h3>
                                    <span class="compliance-criticality-badge is-{{ $event['criticality_key'] ?? 'baixa' }}">Criticidade {{ $event['criticality_label'] ?? 'Baixa' }}</span>
                                    @if (! empty($event['alert']))
                                        <span class="compliance-alert-badge">⚠ {{ $event['alert_label'] ?? 'Alerta inteligente' }}</span>
                                    @endif
                                </div>
                                <small>{{ $event['meta'] }} · {{ $event['date'] }}</small>
                                <div class="compliance-timeline-change is-{{ $event['primary_change']['status'] ?? 'unchanged' }}">
                                    <div>
                                        <span>{{ $event['change_summary']['count_label'] ?? 'Alteração registrada' }}</span>
                                        <strong>{{ $event['primary_change']['field'] ?? ($event['field'] ?? 'Campo') }}</strong>
                                    </div>
                                    <p>
                                        <code>{{ $event['primary_change']['old'] ?? '—' }}</code>
                                        <b>→</b>
                                        <code>{{ $event['primary_change']['new'] ?? '—' }}</code>
                                    </p>
                                </div>
                                <div class="compliance-row-actions">
                                    @php
                                        $eventModalId = 'audit-event-detail-' . md5((string) ($event['id'] ?? $loop->index));
                                    @endphp
                                    <button
                                        type="button"
                                        class="compliance-detail-trigger"
                                        x-on:click.prevent.stop="$dispatch('open-modal', { id: '{{ $eventModalId }}' })"
                                    >Ver detalhes</button>
                                    @if (! empty($event['auditable_type_filter']) && ! empty($event['auditable_id_filter']))
                                        <a class="compliance-quick-link" href="{{ $historyUrl($event) }}">Histórico deste item</a>
                                    @endif
                                </div>
                            </div>
                            <span class="compliance-badge {{ $event['tone'] ?? 'info' }}">{{ $event['criticality_label'] ?? ($event['tone'] ?? 'info') }}</span>
                        </div>
                    @empty
                        <div class="compliance-empty">Nenhum evento de auditoria encontrado para os filtros informados.</div>
                    @endforelse
                </div>
            </article>
        </section>
        @endif

        @if ($aba === 'resumo')
        <section class="compliance-grid compliance-grid-summary">

            <div class="compliance-list">
                <article class="compliance-card">
                    <header><div><h2>Eventos por usuário</h2><p>Quem mais gerou movimentações.</p></div></header>
                    <div class="compliance-list">
                        @forelse (($data['byUser'] ?? []) as $row)
                            <a class="compliance-row compliance-row-link" href="{{ $filterUrl(['userFilter' => $row['id'] ?? 'sistema']) }}"><div><h3>{{ $row['label'] }}</h3><small>Clique para filtrar por este usuário</small></div><strong>{{ $row['count'] }}</strong></a>
                        @empty
                            <div class="compliance-empty">Sem dados por usuário.</div>
                        @endforelse
                    </div>
                </article>
                <article class="compliance-card">
                    <header><div><h2>Tipos de evento</h2><p>Distribuição dos eventos registrados.</p></div></header>
                    <div class="compliance-list">
                        @forelse (($data['byEvent'] ?? []) as $row)
                            <a class="compliance-row compliance-row-link" href="{{ $filterUrl(['actionFilter' => $row['id'] ?? '']) }}"><div><h3>{{ $row['label'] }}</h3><small>Clique para filtrar por este tipo</small></div><strong>{{ $row['count'] }}</strong></a>
                        @empty
                            <div class="compliance-empty">Sem tipos de evento.</div>
                        @endforelse
                    </div>
                </article>
            </div>
        </section>

        @endif

        @if ($aba === 'aprovacoes')
            <section class="compliance-card">
                <header><div><h2>Aprovações recentes</h2><p>Decisões internas que ajudam a comprovar governança.</p></div></header>
            <div class="compliance-table-wrap"><table class="compliance-table"><thead><tr><th>Item</th><th>Empresa</th><th>Status</th><th>Observação</th><th>Data</th></tr></thead><tbody>
                    @forelse (($data['recentApprovals'] ?? []) as $row)
                        <tr><td><strong>{{ $row['title'] }}</strong><br><small>{{ $row['meta'] }}</small></td><td>{{ explode(' · ', $row['meta'])[0] ?? '-' }}</td><td><span class="compliance-badge {{ $row['tone'] }}">{{ $row['status'] }}</span></td><td>{{ $row['description'] }}</td><td>{{ $row['date'] }}</td></tr>
                    @empty
                        <tr><td colspan="5" class="compliance-empty">Nenhuma aprovação recente encontrada.</td></tr>
                    @endforelse
                    </tbody></table></div>
            </section>
        @endif


        @if ($aba === 'investigacao')
            <section id="auditoria-detalhada" class="ad-unified-shell">
            <div class="ad-unified-hero">
                <div>
                    <span><i class="bi bi-fingerprint"></i> Auditoria detalhada centralizada</span>
                    <h2>Tudo em uma única central</h2>
                    <p>Resumo executivo no topo, investigação logo abaixo e leitura em formato de timeline para o usuário entender rapidamente quem fez, quando fez, onde mexeu e o que mudou.</p>
                </div>
                <div class="ad-unified-actions">
                    @if ($this->canExportAuditoria())
                        <button type="button" class="compliance-export-button compliance-export-button-primary" wire:click="exportAuditoriaExcel" wire:loading.attr="disabled" wire:target="exportAuditoriaExcel"><i class="bi bi-file-earmark-spreadsheet"></i> Exportar recorte</button>
                    @endif
                    @if (! empty($auditoriaDetalhadaManageUrl))
                        <a href="{{ $auditoriaDetalhadaManageUrl }}" class="compliance-link compliance-link-light"><i class="bi bi-table"></i> Tabela avançada</a>
                    @endif
                </div>
            </div>

            <section class="ad-metrics ad-metrics--five">
                <article class="ad-metric-card"><span>Total filtrado</span><strong>{{ number_format((int) ($detalheMetricas['total'] ?? 0), 0, ',', '.') }}</strong><small>Eventos dentro do recorte atual</small></article>
                <article class="ad-metric-card ad-metric-card--info"><span>Hoje</span><strong>{{ number_format((int) ($detalheMetricas['hoje'] ?? 0), 0, ',', '.') }}</strong><small>Movimentações do dia</small></article>
                <article class="ad-metric-card ad-metric-card--warning"><span>Alterações</span><strong>{{ number_format((int) ($detalheMetricas['alteracoes'] ?? 0), 0, ',', '.') }}</strong><small>Campos que mudaram</small></article>
                <article class="ad-metric-card ad-metric-card--danger"><span>Exclusões</span><strong>{{ number_format((int) ($detalheMetricas['exclusoes'] ?? 0), 0, ',', '.') }}</strong><small>Registros removidos</small></article>
                <article class="ad-metric-card ad-metric-card--critical"><span>Revisar</span><strong>{{ number_format((int) ($detalheMetricas['sensiveis'] ?? 0), 0, ',', '.') }}</strong><small>Senha, permissão, status, exportação ou falha</small></article>
            </section>

            <section class="ad-panel ad-panel--large ad-focus-panel">
                <div class="ad-panel-header">
                    <div>
                        <h3>Linha do tempo detalhada</h3>
                        <p>Leitura direta para investigação: usuário, empresa, módulo, registro, IP e comparação antes/depois.</p>
                    </div>
                    <div class="ad-panel-counter">{{ number_format((int) $detalheRecentes->count(), 0, ',', '.') }} recentes</div>
                </div>

                @if($detalheRecentes->isEmpty())
                    <div class="ad-empty">Nenhuma movimentação encontrada para os filtros aplicados.</div>
                @else
                    <div class="ad-timeline">
                        @foreach($detalheRecentes as $registro)
                            <div class="ad-timeline-item ad-timeline-item--ux">
                                <div class="ad-avatar" title="{{ $registro->user?->name ?? 'Sistema' }}">{{ $this->iniciaisUsuario($registro) }}</div>
                                <div class="ad-timeline-content">
                                    <div class="ad-timeline-top">
                                        <div class="ad-timeline-badges">
                                            <span class="ad-badge {{ $this->eventoClasse($registro->evento) }}">{{ $formatAuditEvent($registro->evento) }}</span>
                                            <span class="ad-badge {{ $this->sensivelClasse($registro) }}">{{ $this->sensivelLabel($registro) }}</span>
                                        </div>
                                        <time>{{ $this->dataHumana($registro->created_at) }}</time>
                                    </div>
                                    <h4>{{ $this->resumoAcao($registro) }}</h4>
                                    <div class="ad-timeline-meta">
                                        <span>Usuário: {{ $registro->user?->name ?? 'Sistema' }}</span>
                                        <span>Empresa: {{ $this->nomeEmpresa($registro) }}</span>
                                        <span>Módulo: {{ $formatAuditLabel($registro->auditable_type) }}</span>
                                        <span>Registro: {{ $formatAuditRecord($registro->auditable_type, $registro->auditable_id) }}</span>
                                        <span>IP: {{ $registro->ip ?: '-' }}</span>
                                    </div>
                                    <div class="ad-diff ad-diff--ux">
                                        <div><span>Valor anterior</span><strong>{{ $this->valorRegistro($registro->valor_anterior, $registro->campo) }}</strong></div>
                                        <div><span>Valor novo</span><strong>{{ $this->valorRegistro($registro->valor_novo, $registro->campo) }}</strong></div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="ad-panel">
                <div class="ad-panel-header">
                    <div><h3>Ações que merecem revisão</h3><p>Eventos com maior impacto em segurança, permissões, status, exportações, exclusões e integrações.</p></div>
                    <div class="ad-panel-counter">{{ number_format((int) $detalheSuspeitas->count(), 0, ',', '.') }} recentes</div>
                </div>
                @if($detalheSuspeitas->isEmpty())
                    <div class="ad-empty">Nenhuma ação sensível encontrada no recorte atual.</div>
                @else
                    <div class="ad-sensitive-grid">
                        @foreach($detalheSuspeitas as $registro)
                            <article class="ad-sensitive-card">
                                <div class="ad-sensitive-top"><span class="ad-badge ad-badge--danger">Revisar</span><time>{{ optional($registro->created_at)->format('d/m/Y H:i') ?: '-' }}</time></div>
                                <h4>{{ $formatAuditRecord($registro->auditable_type, $registro->auditable_id) }}</h4>
                                <p>{{ $this->resumoAcao($registro) }}</p>
                                <div class="ad-timeline-meta"><span>{{ $registro->user?->name ?? 'Sistema' }}</span><span>{{ $this->nomeEmpresa($registro) }}</span></div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="ad-layout ad-layout--balanced">
                <article class="ad-panel">
                    <div class="ad-panel-header"><div><h3>Eventos por tipo</h3><p>Distribuição das ações registradas no filtro atual.</p></div></div>
                    @if($detalheEventos->isEmpty())
                        <div class="ad-empty">Nenhum evento encontrado.</div>
                    @else
                        <div class="ad-chart">
                            @foreach($detalheEventos as $evento)
                                <div class="ad-chart-row"><div class="ad-chart-label"><span class="ad-badge {{ $evento['classe'] }}">{{ $evento['label'] }}</span></div><div class="ad-chart-track"><div class="ad-chart-bar" style="width: {{ $evento['percentual'] }}%"></div></div><strong>{{ number_format((int) $evento['valor'], 0, ',', '.') }}</strong></div>
                            @endforeach
                        </div>
                    @endif
                </article>
                <article class="ad-panel">
                    <div class="ad-panel-header"><div><h3>Usuários mais ativos</h3><p>Quem mais gerou movimentações.</p></div></div>
                    @if($detalheUsuarios->isEmpty())
                        <div class="ad-empty">Sem usuários auditados.</div>
                    @else
                        <div class="ad-ranking">
                            @foreach($detalheUsuarios as $usuario)
                                <div class="ad-ranking-row ad-ranking-row--stacked"><span>{{ $usuario['nome'] }}</span><small>Última ação: {{ $usuario['ultima'] }}</small><strong>{{ number_format((int) $usuario['total'], 0, ',', '.') }}</strong></div>
                            @endforeach
                        </div>
                    @endif
                </article>
            </section>

            <section class="ad-layout ad-layout--balanced">
                <article class="ad-panel">
                    <div class="ad-panel-header"><div><h3>Auditoria por empresa</h3><p>Empresas com maior volume de rastreio.</p></div></div>
                    @if($detalheEmpresas->isEmpty())
                        <div class="ad-empty">Nenhuma empresa encontrada.</div>
                    @else
                        <div class="ad-ranking">
                            @foreach($detalheEmpresas as $empresa)
                                <div class="ad-ranking-row"><span>{{ $empresa['nome'] }}</span><strong>{{ number_format((int) $empresa['total'], 0, ',', '.') }}</strong></div>
                            @endforeach
                        </div>
                    @endif
                </article>
                <article class="ad-panel">
                    <div class="ad-panel-header"><div><h3>Áreas mais movimentadas</h3><p>Módulos do sistema com mais alterações.</p></div></div>
                    @if($detalheModulos->isEmpty())
                        <div class="ad-empty">Nenhum módulo encontrado.</div>
                    @else
                        <div class="ad-ranking">
                            @foreach($detalheModulos as $modulo)
                                <div class="ad-ranking-row"><span>{{ $modulo['nome'] }}</span><strong>{{ number_format((int) $modulo['total'], 0, ',', '.') }}</strong></div>
                            @endforeach
                        </div>
                    @endif
                </article>
            </section>
        </section>
        @endif


        @foreach (($data['timeline'] ?? []) as $modalEventIndex => $modalEvent)
            @php
                $eventModalId = 'audit-event-detail-' . md5((string) ($modalEvent['id'] ?? $modalEventIndex));
                $eventDetailHistoryUrl = (! empty($modalEvent['auditable_type_filter']) && ! empty($modalEvent['auditable_id_filter'])) ? $historyUrl($modalEvent) : '#';
                $eventDetailEventFilterUrl = ! empty($modalEvent['event_raw']) ? $filterUrl(['actionFilter' => $modalEvent['event_raw']]) : '#';
                $eventDetailUserFilterUrl = isset($modalEvent['user_id']) ? $filterUrl(['userFilter' => $modalEvent['user_id'] ?: 'sistema']) : '#';
                $eventDetailCompanyFilterUrl = ! empty($modalEvent['company_id']) ? $filterUrl(['companyFilter' => $modalEvent['company_id']]) : '#';
                $eventDetailDiffRows = is_array($modalEvent['diff_rows'] ?? null) ? $modalEvent['diff_rows'] : [];
                $eventDetailStatusLabels = [
                    'added' => 'Adicionado',
                    'removed' => 'Removido',
                    'changed' => 'Alterado',
                    'unchanged' => 'Igual',
                ];
            @endphp

            <x-filament::modal :id="$eventModalId" width="7xl" :close-by-clicking-away="true" :close-by-escaping="true">
                <x-slot name="heading">
                    {{ $formatAuditValue($modalEvent['title'] ?? 'Detalhes do evento') }}
                </x-slot>

                <x-slot name="description">
                    {{ $formatAuditValue($modalEvent['company'] ?? 'Sem empresa') . ' · ' . $formatAuditValue($modalEvent['user'] ?? 'Sistema') . ' · ' . $formatAuditValue($modalEvent['date_full'] ?? ($modalEvent['date'] ?? '-')) }}
                </x-slot>

                <div class="compliance-modal-content-native">
                    @if (! empty($modalEvent['alert']))
                        <div class="compliance-alert-panel">
                            <div>
                                <span>Alerta inteligente</span>
                                <strong>{{ $modalEvent['alert_label'] ?? 'Comportamento suspeito detectado' }}</strong>
                            </div>
                            <p>{{ $modalEvent['alert_description'] ?? 'Este evento combina padrões que merecem revisão pela governança.' }}</p>
                        </div>
                    @endif

                    <div class="compliance-criticality-panel is-{{ $modalEvent['criticality_key'] ?? 'baixa' }}">
                        <div>
                            <span>Criticidade do evento</span>
                            <strong>{{ $modalEvent['criticality_label'] ?? 'Baixa' }}</strong>
                        </div>
                        <p>{{ $modalEvent['criticality_hint'] ?? 'Evento registrado para rastreabilidade.' }}</p>
                    </div>

                    <div class="compliance-detail-grid">
                        <article>
                            <span>Evento</span>
                            <strong>{{ $formatAuditValue($modalEvent['event_label'] ?? '-') }}</strong>
                            <small>
                                @if ($eventDetailEventFilterUrl !== '#')
                                    <a class="compliance-inline-link" href="{{ $eventDetailEventFilterUrl }}">Filtrar por evento</a>
                                @endif
                            </small>
                        </article>
                        <article><span>Módulo</span><strong>{{ $formatAuditLabel($modalEvent['module'] ?? '-') }}</strong><small>{{ $formatAuditLabel($modalEvent['auditable_type'] ?? '-') }}</small></article>
                        <article>
                            <span>Registro</span>
                            <strong>{{ $modalEvent['auditable_id'] ?? '-' }}</strong>
                            <small>
                                @if ($eventDetailHistoryUrl !== '#')
                                    <a class="compliance-inline-link" href="{{ $eventDetailHistoryUrl }}">Ver histórico completo</a>
                                @endif
                            </small>
                        </article>
                        <article><span>Campo</span><strong>{{ $formatAuditLabel($modalEvent['field'] ?? '-') }}</strong><small>Campo auditado</small></article>
                        <article>
                            <span>Usuário</span>
                            <strong>{{ $formatAuditValue($modalEvent['user'] ?? 'Sistema') }}</strong>
                            <small>
                                @if ($eventDetailUserFilterUrl !== '#')
                                    <a class="compliance-inline-link" href="{{ $eventDetailUserFilterUrl }}">Filtrar por usuário</a>
                                @endif
                            </small>
                        </article>
                        <article>
                            <span>Empresa</span>
                            <strong>{{ $formatAuditValue($modalEvent['company'] ?? 'Sem empresa') }}</strong>
                            <small>
                                @if ($eventDetailCompanyFilterUrl !== '#')
                                    <a class="compliance-inline-link" href="{{ $eventDetailCompanyFilterUrl }}">Filtrar por empresa</a>
                                @endif
                            </small>
                        </article>
                        <article><span>IP</span><strong>{{ $formatAuditValue($modalEvent['ip'] ?? '-') }}</strong><small>Origem registrada</small></article>
                        <article><span>Data e hora</span><strong>{{ $formatAuditValue($modalEvent['date_full'] ?? ($modalEvent['date'] ?? '-')) }}</strong><small>Momento exato do evento</small></article>
                    </div>

                    <div class="compliance-detail-values">
                        <article>
                            <header>Valor anterior</header>
                            <pre>{{ $formatAuditValue($modalEvent['old_value'] ?? null) }}</pre>
                        </article>
                        <article>
                            <header>Valor novo</header>
                            <pre>{{ $formatAuditValue($modalEvent['new_value'] ?? null) }}</pre>
                        </article>
                    </div>

                    <div class="compliance-diff-box">
                        <div class="compliance-diff-header">
                            <div>
                                <span>Antes vs Depois</span>
                                <h3>Comparação campo a campo</h3>
                            </div>
                            <strong class="{{ ! empty($modalEvent['has_changes']) ? 'has-change' : 'no-change' }}">{{ ! empty($modalEvent['has_changes']) ? 'Com alteração' : 'Sem alteração detectada' }}</strong>
                        </div>

                        <div class="compliance-diff-table-wrap">
                            <table class="compliance-diff-table">
                                <thead>
                                <tr>
                                    <th>Campo</th>
                                    <th>Antes</th>
                                    <th>Depois</th>
                                    <th>Status</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse ($eventDetailDiffRows as $diff)
                                    @php $diffStatus = $diff['status'] ?? 'unchanged'; @endphp
                                    <tr class="is-{{ $diffStatus }}">
                                        <td><strong>{{ $formatAuditLabel($diff['field'] ?? '-') }}</strong></td>
                                        <td><code>{{ $formatAuditValue($diff['old'] ?? null) }}</code></td>
                                        <td><code>{{ $formatAuditValue($diff['new'] ?? null) }}</code></td>
                                        <td><span>{{ $eventDetailStatusLabels[$diffStatus] ?? 'Igual' }}</span></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="compliance-empty">Nenhum dado disponível para comparação.</td></tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    @if (! empty($modalEvent['user_agent']))
                        <details class="compliance-detail-agent">
                            <summary>Informações técnicas</summary>
                            <div>
                                <span>User agent</span>
                                <p>{{ $modalEvent['user_agent'] }}</p>
                            </div>
                        </details>
                    @endif

                    <div class="compliance-modal-footer-actions">
                        @if ($eventDetailHistoryUrl !== '#')
                            <a class="compliance-modal-primary-action" href="{{ $eventDetailHistoryUrl }}">Ver histórico completo deste item</a>
                        @endif
                        <a class="compliance-modal-secondary-action" href="{{ $filterUrl(['aba' => 'investigacao']) }}">Ir para auditoria detalhada</a>
                    </div>
                </div>
            </x-filament::modal>
        @endforeach
    </div>
</x-filament-panels::page>
