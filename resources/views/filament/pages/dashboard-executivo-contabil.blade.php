<x-filament-panels::page>
    @php
        $dashboard = $dashboard ?? ($this->dashboardData ?? []);
        $risk = $dashboard['risk'] ?? [
            'label' => 'Sem dados suficientes',
            'headline' => 'Ainda não há dados suficientes para calcular o risco executivo contábil.',
            'tone' => 'info',
            'score' => 0,
            'count' => 0,
        ];
        $top = $dashboard['top'] ?? [];
        $decisionCards = collect($dashboard['decision_cards'] ?? ($dashboard['metrics'] ?? []))->sortBy('priority')->values()->all();
        $resolveNow = $dashboard['resolve_now'] ?? [];
        $blockers = $dashboard['blockers'] ?? [];
        $trend = $dashboard['trend'] ?? null;
        $templatesContabeis = $dashboard['templates_contabeis'] ?? [];
        $templateRiskRows = $dashboard['template_risk_rows'] ?? [];
        $updatedAt = $dashboard['updated_at'] ?? now()->format('d/m/Y H:i');

        $riskScore = max(0, min(100, (int) ($risk['score'] ?? 0)));
    @endphp

    <div class="dec-cockpit" data-executive-accounting-dashboard>
        <section class="dec-hero dec-tone-{{ $top['tone'] ?? ($risk['tone'] ?? 'info') }}" aria-label="Estado executivo do escritório contábil">
            <div class="dec-hero__content">
                <span class="dec-eyebrow">{{ $top['eyebrow'] ?? 'Cockpit Executivo Contábil' }}</span>
                <h1>{{ $risk['label'] ?? 'Dashboard Executivo Contábil' }}</h1>
                <p>{{ $top['summary'] ?? ($risk['headline'] ?? 'Veja somente o que exige decisão executiva hoje.') }}</p>

                <div class="dec-hero__meta">
                    <span>{{ (int) ($risk['count'] ?? 0) }} ponto(s) críticos consolidados</span>
                    <span>Atualizado em {{ $updatedAt }}</span>
                </div>
            </div>

            <aside class="dec-control" aria-label="Índice de controle operacional">
                <div class="dec-control__head">
                    <span>{{ $top['badge'] ?? 'Controle operacional' }}</span>
                    <strong>{{ $riskScore }}%</strong>
                </div>
                <div class="dec-control__bar" aria-hidden="true">
                    <i style="width: {{ $riskScore }}%"></i>
                </div>
                <p>{{ $risk['headline'] ?? 'Índice calculado a partir de obrigações, SLA, documentos e inadimplência com impacto.' }}</p>

                @if (! empty($top['primary_url']))
                    <a class="dec-primary" href="{{ $top['primary_url'] }}">{{ $top['primary_action'] ?? 'Abrir origem' }}</a>
                @endif
            </aside>
        </section>

        <section class="dec-decision-grid" aria-label="Quatro decisões executivas">
            @forelse ($decisionCards as $card)
                <a
                    class="dec-decision dec-tone-{{ $card['tone'] ?? 'info' }} {{ empty($card['url']) ? 'dec-decision--static' : '' }}"
                    href="{{ $card['url'] ?: '#' }}"
                    @if (empty($card['url'])) aria-disabled="true" onclick="return false" @endif
                >
                    <span class="dec-decision__icon" aria-hidden="true">{{ $card['icon'] ?? '•' }}</span>
                    <span class="dec-decision__label">{{ $card['label'] ?? 'Indicador executivo' }}</span>
                    <strong>{{ $card['value'] ?? '0' }}</strong>
                    <p>{{ $card['hint'] ?? 'Indicador consolidado das telas de origem.' }}</p>
                    @if (! empty($card['url']))
                        <em>{{ $card['action_label'] ?? 'Abrir origem' }} · {{ $card['source_label'] ?? 'aba de origem' }}</em>
                    @endif
                </a>
            @empty
                <article class="dec-empty dec-empty--wide">
                    <strong>Nenhuma decisão crítica encontrada.</strong>
                    <p>Quando houver dados, esta área mostrará apenas riscos executivos, não métricas genéricas.</p>
                </article>
            @endforelse
        </section>

        <section class="dec-main-grid" aria-label="Filas executivas acionáveis">
            <article class="dec-panel dec-panel--primary">
                <header class="dec-panel__header">
                    <div>
                        <span class="dec-eyebrow">Resolver primeiro</span>
                        <h2>Resolver agora</h2>
                        <p>No máximo 5 itens que podem gerar multa, atraso, quebra de SLA ou desgaste com cliente.</p>
                    </div>
                    <strong>{{ count($resolveNow) }}</strong>
                </header>

                <div class="dec-action-list">
                    @forelse ($resolveNow as $item)
                        <article class="dec-action dec-tone-{{ $item['tone'] ?? 'info' }}">
                            <div class="dec-action__top">
                                <div>
                                    <h3>{{ $item['title'] ?? 'Item crítico' }}</h3>
                                    <small>{{ $item['meta'] ?? 'Origem operacional' }}</small>
                                </div>
                                <span>{{ $item['status'] ?? 'Ação' }}</span>
                            </div>
                            <p>{{ $item['description'] ?? 'Abra a origem para executar sem duplicar gestão nesta tela.' }}</p>
                            <div class="dec-action__footer">
                                <em>{{ ! empty($item['deadline']) ? 'Prazo ' . $item['deadline'] : 'Ação executiva' }}</em>
                                @if (! empty($item['url']))
                                    <a href="{{ $item['url'] }}">{{ $item['action_label'] ?? 'Abrir origem' }}</a>
                                @endif
                            </div>
                        </article>
                    @empty
                        <div class="dec-empty">
                            <strong>Nada crítico para resolver agora.</strong>
                            <p>Esta fila não lista tarefas comuns. Ela só aparece quando existe risco real de impacto.</p>
                        </div>
                    @endforelse
                </div>
            </article>

            <aside class="dec-side-stack">
                <article class="dec-panel">
                    <header class="dec-panel__header dec-panel__header--compact">
                        <div>
                            <span class="dec-eyebrow">Gargalos</span>
                            <h2>Bloqueios que travam entrega</h2>
                            <p>Clientes ou fluxos que impedem entrega, obrigação, aprovação ou cobrança.</p>
                        </div>
                        <strong>{{ count($blockers) }}</strong>
                    </header>

                    <div class="dec-blocker-list">
                        @forelse ($blockers as $item)
                            <article class="dec-blocker dec-tone-{{ $item['tone'] ?? 'warning' }}">
                                <div>
                                    <h3>{{ $item['title'] ?? 'Bloqueio operacional' }}</h3>
                                    <small>{{ $item['meta'] ?? ($item['status'] ?? 'Bloqueio') }}</small>
                                </div>
                                <p>{{ $item['description'] ?? 'Bloqueio identificado a partir das telas de origem.' }}</p>
                                @if (! empty($item['url']))
                                    <a href="{{ $item['url'] }}">{{ $item['action_label'] ?? 'Abrir origem' }}</a>
                                @endif
                            </article>
                        @empty
                            <div class="dec-empty dec-empty--compact">
                                <strong>Nenhum bloqueio relevante.</strong>
                                <p>Documentos e pendências comuns continuam nas abas próprias.</p>
                            </div>
                        @endforelse
                    </div>
                </article>

                <article class="dec-trend dec-tone-{{ $trend['tone'] ?? 'info' }}">
                    <span class="dec-eyebrow">Tendência executiva</span>
                    <div class="dec-trend__metric">
                        <strong>{{ $trend['value'] ?? ($riskScore . '%') }}</strong>
                        <span>{{ $trend['label'] ?? 'Risco operacional' }}</span>
                    </div>
                    <p>{{ $trend['description'] ?? 'Leitura resumida para saber se a rotina crítica está melhorando ou piorando.' }}</p>
                    <div class="dec-origin-note">Dados consolidados de SLA, Centro Operacional, Documentos e Cobranças.</div>
                    @if (! empty($trend['evidence']))
                        <small>{{ $trend['evidence'] }}</small>
                    @endif
                </article>
            </aside>
        </section>



        @if (! empty($templatesContabeis) && ((int) ($templatesContabeis['tasks_open'] ?? 0) > 0 || (int) ($templatesContabeis['templates_active'] ?? 0) > 0))
            <section class="dec-panel" aria-label="Integração dos templates contábeis">
                <header class="dec-panel__header">
                    <div>
                        <span class="dec-eyebrow">Templates Contábeis</span>
                        <h2>Execução integrada ao Escritório Contábil</h2>
                        <p>Itens aplicados por template aparecem naturalmente na Central Operacional, Checklist, Documentos, SLA, Kanban, Timeline, Gantt, Relatórios e Auditoria.</p>
                    </div>
                    <strong>{{ (int) ($templatesContabeis['tasks_open'] ?? 0) }}</strong>
                </header>

                <div class="dec-decision-grid dec-spaced-top">
                    <article class="dec-decision dec-decision--static dec-tone-info">
                        <span class="dec-decision__label">Templates ativos</span>
                        <strong>{{ (int) ($templatesContabeis['templates_active'] ?? 0) }}</strong>
                        <p>Catálogo oficial disponível para aplicação.</p>
                    </article>
                    <article class="dec-decision dec-decision--static dec-tone-warning">
                        <span class="dec-decision__label">Processos abertos</span>
                        <strong>{{ (int) ($templatesContabeis['processes_open'] ?? 0) }}</strong>
                        <p>Instâncias criadas a partir de templates.</p>
                    </article>
                    <article class="dec-decision dec-decision--static dec-tone-danger">
                        <span class="dec-decision__label">Atrasadas</span>
                        <strong>{{ (int) ($templatesContabeis['tasks_late'] ?? 0) }}</strong>
                        <p>Tarefas de templates fora do prazo.</p>
                    </article>
                    <article class="dec-decision dec-decision--static dec-tone-warning">
                        <span class="dec-decision__label">Bloqueios</span>
                        <strong>{{ (int) ($templatesContabeis['blocked'] ?? 0) }}</strong>
                        <p>Dependências, documentos ou aprovações pendentes.</p>
                    </article>
                </div>

                @if (! empty($templateRiskRows))
                    <div class="dec-action-list dec-spaced-top">
                        @foreach ($templateRiskRows as $item)
                            <article class="dec-action dec-tone-{{ $item['tone'] ?? 'warning' }}">
                                <div class="dec-action__top">
                                    <div>
                                        <h3>{{ $item['title'] ?? 'Tarefa de template' }}</h3>
                                        <small>{{ $item['meta'] ?? 'Template contábil' }}</small>
                                    </div>
                                    <span>{{ $item['status'] ?? 'Atenção' }}</span>
                                </div>
                                <p>{{ $item['description'] ?? 'Tarefa integrada ao ecossistema operacional.' }}</p>
                                <div class="dec-action__footer">
                                    <em>{{ ! empty($item['deadline']) ? 'Prazo ' . $item['deadline'] : 'Sem prazo' }}</em>
                                    @if (! empty($item['url']))
                                        <a href="{{ $item['url'] }}">{{ $item['action_label'] ?? 'Abrir tarefa' }}</a>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>
        @endif

        <section class="dec-footer-note" aria-label="Critério da dashboard">
            <strong>Critério desta tela:</strong>
            <span>não substituir Clientes, Financeiro, Documentos, SLA ou Centro Operacional; apenas apontar decisões executivas que não podem esperar.</span>
        </section>
    </div>
</x-filament-panels::page>
