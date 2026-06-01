<x-filament-panels::page>
    @php
        $whiteLabel = \App\Support\WhiteLabelSettings::make();
        $brandName = $whiteLabel->displayName();
        $enterpriseLabel = $whiteLabel->enterpriseLabel();
    @endphp

    <link rel="stylesheet" href="{{ asset('css/prazzu-enterprise.css') }}">

    <div class="prazzu-enterprise-page">
        <section class="prazzu-enterprise-hero">
            <div>
                <span class="prazzu-enterprise-kicker">{{ strtoupper(str_replace('-', ' ', $enterprise['key'] ?? $brandName)) }}</span>
                <h1>{{ $enterprise['title'] ?? $enterpriseLabel }}</h1>
                <p>{{ $enterprise['subtitle'] ?? 'Gestão operacional, documental, compliance, portal cliente e cobrança inteligente.' }}</p>
            </div>
            <div class="prazzu-enterprise-actions">
                @foreach (($enterprise['actions'] ?? []) as $action)
                    <button type="button">{{ $action }}</button>
                @endforeach
            </div>
        </section>

        <section class="prazzu-enterprise-metrics">
            @foreach (($enterprise['metrics'] ?? []) as $metric)
                <article class="metric {{ $metric['tone'] ?? 'info' }}">
                    <span>{{ $metric['label'] }}</span>
                    <strong>{{ $metric['value'] }}</strong>
                </article>
            @endforeach
        </section>

        <section class="prazzu-enterprise-grid two">
            <article class="panel">
                <div class="panel-title">
                    <div><h2>Visões de trabalho</h2><p>Não é só tabela: o módulo já fica preparado para Lista, Kanban, Calendário, Timeline, Gantt e Tabela.</p></div>
                </div>
                <div class="view-pills">
                    @foreach (($enterprise['views'] ?? []) as $view)
                        <span>{{ $view }}</span>
                    @endforeach
                </div>
                <div class="kanban-preview">
                    @forelse (($enterprise['kanban'] ?? []) as $column)
                        <div class="kanban-column">
                            <b>{{ ucfirst(str_replace('_', ' ', $column['status'] ?? 'Sem status')) }}</b>
                            <strong>{{ $column['total'] ?? 0 }}</strong>
                            <small>cards</small>
                        </div>
                    @empty
                        <div class="empty-box">Sem dados para montar o Kanban ainda.</div>
                    @endforelse
                </div>
            </article>

            <article class="panel">
                <div class="panel-title">
                    <div><h2>Funcionalidades do roadmap</h2><p>Componentes necessários para competir com operação enterprise.</p></div>
                </div>
                <div class="feature-grid">
                    @foreach (($enterprise['features'] ?? []) as $feature)
                        <span>{{ $feature }}</span>
                    @endforeach
                </div>
            </article>
        </section>

        <section class="prazzu-enterprise-grid three">
            <article class="panel compact">
                <h2>KPIs</h2>
                @foreach (($enterprise['kpis'] ?? []) as $kpi)
                    <div class="kpi-line"><span>{{ $kpi['label'] }}</span><strong>{{ $kpi['value'] }}</strong></div>
                @endforeach
            </article>
            <article class="panel compact">
                <h2>IA operacional</h2>
                @foreach (($enterprise['ai'] ?? []) as $question)
                    <div class="ai-question">{{ $question }}</div>
                @endforeach
            </article>
            <article class="panel compact">
                <h2>Automação</h2>
                <div class="automation-list">
                    <span>Se status mudar → notificar responsável</span>
                    <span>Se vencer SLA → marcar risco</span>
                    <span>Se cobrança vencer → bloquear portal</span>
                    <span>Se documento aprovado → liberar cliente</span>
                </div>
            </article>
        </section>

        <section class="prazzu-enterprise-grid two">
            <article class="panel">
                <div class="panel-title"><div><h2>Registros prioritários</h2><p>Itens principais para ação, acompanhamento ou decisão.</p></div></div>
                <div class="records-list">
                    @forelse (($enterprise['rows'] ?? []) as $row)
                        <div class="record-item">
                            <div>
                                <strong>{{ $row['titulo'] ?? $row['nome'] ?? $row['name'] ?? $row['email'] ?? $row['description'] ?? $row['acao'] ?? $row['action'] ?? $row['status'] ?? 'Registro' }}</strong>
                                <p>{{ $row['detalhe'] ?? $row['comentario'] ?? $row['comment'] ?? $row['tipo'] ?? $row['cargo'] ?? $row['razao_social'] ?? $row['nome_fantasia'] ?? '-' }}</p>
                            </div>
                            <span>{{ $row['status'] ?? $row['prioridade'] ?? $row['valor'] ?? $row['created_at'] ?? '-' }}</span>
                        </div>
                    @empty
                        <div class="empty-box">Nenhum registro encontrado. Execute o SQL manual do pacote para habilitar as novas estruturas.</div>
                    @endforelse
                </div>
            </article>

            <article class="panel">
                <div class="panel-title"><div><h2>Timeline de auditoria</h2><p>Histórico para compliance, aprovações, documentos e segurança.</p></div></div>
                <div class="timeline-list">
                    @forelse (($enterprise['timeline'] ?? []) as $event)
                        <div class="timeline-item">
                            <span></span>
                            <div><strong>{{ $event['titulo'] ?? $event['description'] ?? $event['acao'] ?? $event['action'] ?? 'Evento registrado' }}</strong><small>{{ $event['created_at'] ?? '-' }}</small></div>
                        </div>
                    @empty
                        <div class="empty-box">Timeline sem eventos recentes.</div>
                    @endforelse
                </div>
            </article>
        </section>
    </div>
</x-filament-panels::page>
