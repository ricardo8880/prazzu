<x-filament-panels::page>
    <link rel="stylesheet" href="{{ asset('css/compliance-module.css') }}?v={{ file_exists(public_path('css/compliance-module.css')) ? filemtime(public_path('css/compliance-module.css')) : time() }}">

    @php
        $score = (int)($data['score'] ?? 0);
        $tone = $score >= 80 ? 'ok' : ($score >= 60 ? 'warning' : 'danger');
        $stats = collect($data['stats'] ?? []);
        $criticalRisks = collect($data['criticalRisks'] ?? []);
        $latePendings = collect($data['latePendings'] ?? []);
        $recommendations = collect($data['recommendations'] ?? []);

        $statValue = function (string $label) use ($stats) {
            $stat = $stats->first(fn ($item) => ($item['label'] ?? '') === $label);
            return $stat['value'] ?? 0;
        };

        $criticalCount = (int) $statValue('Riscos críticos');
        $lateCount = (int) $statValue('Pendências vencidas');
        $auditCount = (int) $statValue('Eventos auditados');

        $scoreMeta = match ($tone) {
            'ok' => [
                'label' => 'Operação saudável',
                'headline' => 'A governança está sob controle.',
                'description' => 'O score indica boa aderência operacional. Mantenha a rotina de revisão para evitar acúmulo de riscos e pendências.',
                'instruction' => 'Monitore riscos, auditoria e evidências para manter o padrão atual.',
            ],
            'warning' => [
                'label' => 'Atenção necessária',
                'headline' => 'Existem pontos que precisam de acompanhamento.',
                'description' => 'O compliance ainda está administrável, mas riscos críticos, vencimentos ou falta de evidência podem piorar rapidamente o cenário.',
                'instruction' => 'Priorize riscos críticos e pendências vencidas antes de revisar os demais itens.',
            ],
            default => [
                'label' => 'Risco operacional alto',
                'headline' => 'A situação exige ação imediata.',
                'description' => 'O volume de riscos críticos ou pendências vencidas está impactando diretamente a saúde do compliance.',
                'instruction' => 'Resolva primeiro os itens críticos e vencidos destacados nesta página.',
            ],
        };

        $mainAttention = $lateCount > 0
            ? 'Pendências vencidas estão derrubando o score e devem ser tratadas primeiro.'
            : ($criticalCount > 0
                ? 'Riscos críticos exigem decisão rápida para evitar impacto operacional.'
                : 'Nenhum bloqueio crítico foi identificado neste momento.');

        $statTone = function (string $label, $value) {
            $normalized = mb_strtolower((string) $label);
            $numeric = (int) preg_replace('/[^0-9]/', '', (string) $value);

            if (str_contains($normalized, 'score')) {
                return $numeric >= 80 ? 'ok' : ($numeric >= 60 ? 'warning' : 'danger');
            }

            if (str_contains($normalized, 'crítico') || str_contains($normalized, 'vencida')) {
                return $numeric > 0 ? 'danger' : 'ok';
            }

            return 'info';
        };

        $priorityItems = $latePendings
            ->map(fn ($item) => [
                'type' => 'Pendência vencida',
                'tone' => 'danger',
                'title' => $item['titulo'] ?? 'Pendência sem título',
                'empresa' => $item['empresa'] ?? 'Empresa não informada',
                'responsavel' => $item['responsavel'] ?? 'Responsável não informado',
                'vencimento' => $item['vencimento'] ?? 'Sem vencimento informado',
                'url' => $item['url'] ?? '#',
                'reason' => 'Está vencida e impacta diretamente a saúde do compliance.',
                'action' => 'Resolver agora',
                'order' => 1,
            ])
            ->merge(
                $criticalRisks->map(fn ($risk) => [
                    'type' => 'Risco crítico',
                    'tone' => 'danger',
                    'title' => $risk['titulo'] ?? 'Risco sem título',
                    'empresa' => $risk['empresa'] ?? 'Empresa não informada',
                    'responsavel' => $risk['responsavel'] ?? 'Responsável não informado',
                    'vencimento' => $risk['vencimento'] ?? 'Sem vencimento informado',
                    'url' => $risk['url'] ?? '#',
                    'reason' => 'Possui criticidade alta e pode gerar impacto operacional.',
                    'action' => 'Analisar risco',
                    'order' => 2,
                ])
            )
            ->sortBy('order')
            ->take(6)
            ->values();

        $prioritySummary = $lateCount > 0
            ? 'Comece pelas pendências vencidas: elas representam o bloqueio mais objetivo para recuperar o score.'
            : ($criticalCount > 0
                ? 'Comece pelos riscos críticos: eles concentram o maior potencial de impacto operacional.'
                : 'Nenhuma ação emergencial foi encontrada. Mantenha a revisão preventiva dos indicadores.');

        $safePageUrl = function (string $class, ?string $fallback = null): string {
            if (class_exists($class) && method_exists($class, 'getUrl')) {
                try {
                    return $class::getUrl();
                } catch (Throwable $exception) {
                    return $fallback ?: url('/admin');
                }
            }

            return $fallback ?: url('/admin');
        };

        $safeResourceUrl = function (string $class, string $page = 'index', array $parameters = [], ?string $fallback = null): string {
            if (class_exists($class) && method_exists($class, 'getUrl')) {
                try {
                    return $class::getUrl($page, $parameters);
                } catch (Throwable $exception) {
                    return $fallback ?: url('/admin');
                }
            }

            return $fallback ?: url('/admin');
        };

        $pendenciasUrl = $safePageUrl(\App\Filament\Pages\Pendencias::class, url('/admin/pendencias'));
        $riscosUrl = $safePageUrl(\App\Filament\Pages\Riscos::class, url('/admin/riscos'));
        $auditoriaUrl = $safePageUrl(\App\Filament\Pages\Auditoria::class, url('/admin/auditoria'));
        $itensUrl = $safeResourceUrl(\App\Filament\Resources\ItemControles\ItemControleResource::class, 'index', [], url('/admin/item-controles'));
        $minhasPendenciasUrl = $pendenciasUrl;

        $primaryAction = $lateCount > 0
            ? ['label' => 'Resolver pendências vencidas', 'url' => $pendenciasUrl, 'tone' => 'danger', 'helper' => 'Abrir a página de pendências filtrada pela rotina de compliance.']
            : ($criticalCount > 0
                ? ['label' => 'Analisar riscos críticos', 'url' => $riscosUrl, 'tone' => 'danger', 'helper' => 'Abrir a página de riscos para tratar os itens de maior impacto.']
                : ['label' => 'Revisar itens de controle', 'url' => $itensUrl, 'tone' => 'ok', 'helper' => 'Abrir a listagem geral para manutenção preventiva.']);

        $actionCards = collect([
            [
                'title' => 'Resolver pendências',
                'description' => $lateCount > 0 ? 'Atue nos atrasos que mais prejudicam o score.' : 'Confira pendências abertas antes que virem atraso.',
                'url' => $pendenciasUrl,
                'label' => $lateCount > 0 ? 'Ir para pendências vencidas' : 'Ver pendências',
                'tone' => $lateCount > 0 ? 'danger' : 'info',
                'count' => $lateCount,
            ],
            [
                'title' => 'Analisar riscos',
                'description' => $criticalCount > 0 ? 'Priorize riscos críticos e registre a decisão operacional.' : 'Faça uma revisão preventiva dos riscos cadastrados.',
                'url' => $riscosUrl,
                'label' => $criticalCount > 0 ? 'Ir para riscos críticos' : 'Ver riscos',
                'tone' => $criticalCount > 0 ? 'danger' : 'info',
                'count' => $criticalCount,
            ],
            [
                'title' => 'Abrir minhas tarefas',
                'description' => 'Veja itens direcionados ao usuário logado e reduza o caminho até a execução.',
                'url' => $minhasPendenciasUrl,
                'label' => 'Ver minhas pendências',
                'tone' => 'warning',
                'count' => null,
            ],
            [
                'title' => 'Conferir auditoria',
                'description' => 'Valide rastreabilidade, evidências e movimentações recentes.',
                'url' => $auditoriaUrl,
                'label' => 'Abrir auditoria',
                'tone' => 'info',
                'count' => $auditCount,
            ],
        ]);

        $trend = collect($data['trend'] ?? []);
        $trendCards = collect($trend->get('cards', []));
        $trendTone = $trend->get('tone', 'info');
        $trendDeltaLabel = function ($delta): string {
            if (is_null($delta)) {
                return 'Base atual';
            }

            $numericDelta = (int) $delta;

            if ($numericDelta === 0) {
                return 'Sem variação';
            }

            return ($numericDelta > 0 ? '+' : '') . $numericDelta;
        };
    @endphp

    <div class="compliance-page compliance-engine-page">
        <section class="compliance-hero compliance-engine-hero {{ $tone }}">
            <div class="compliance-hero-copy">
                <span>Saúde do Compliance</span>
                <h1>Visão clara da governança agora</h1>
                <p>{{ $scoreMeta['headline'] }} {{ $scoreMeta['description'] }}</p>
            </div>

            <aside class="compliance-hero-diagnosis" aria-label="Diagnóstico atual do compliance">
                <small>Diagnóstico atual</small>
                <strong>{{ $scoreMeta['label'] }}</strong>
                <div class="compliance-hero-score">{{ $score }}%</div>
                <p>{{ $scoreMeta['instruction'] }}</p>
            </aside>
        </section>

        <section class="compliance-decision-strip {{ $tone }}">
            <div>
                <span>Leitura executiva</span>
                <strong>{{ $mainAttention }}</strong>
            </div>
            <div class="compliance-decision-pills">
                <span class="{{ $criticalCount > 0 ? 'danger' : 'ok' }}">{{ $criticalCount }} risco(s) crítico(s)</span>
                <span class="{{ $lateCount > 0 ? 'danger' : 'ok' }}">{{ $lateCount }} pendência(s) vencida(s)</span>
                <span class="info">{{ $auditCount }} evento(s) auditado(s)</span>
            </div>
        </section>

        <section class="compliance-action-hub {{ $primaryAction['tone'] }}" aria-label="Ações diretas do Compliance Engine">
            <div class="compliance-action-hub-main">
                <span>Próxima ação recomendada</span>
                <strong>{{ $primaryAction['label'] }}</strong>
                <p>{{ $primaryAction['helper'] }}</p>
            </div>
            <a class="compliance-action-hub-button {{ $primaryAction['tone'] }}" href="{{ $primaryAction['url'] }}">
                Executar agora
            </a>
        </section>

        <section class="compliance-action-grid" aria-label="Atalhos operacionais">
            @foreach ($actionCards as $action)
                <article class="compliance-action-card {{ $action['tone'] }}">
                    <div>
                        <span>{{ is_null($action['count']) ? 'Atalho' : $action['count'] . ' item(ns)' }}</span>
                        <strong>{{ $action['title'] }}</strong>
                        <p>{{ $action['description'] }}</p>
                    </div>
                    <a href="{{ $action['url'] }}">{{ $action['label'] }}</a>
                </article>
            @endforeach
        </section>

        <section class="compliance-stats compliance-engine-stats">
            @foreach ($stats as $stat)
                @php $currentTone = $statTone($stat['label'] ?? '', $stat['value'] ?? 0); @endphp
                <article class="compliance-stat compliance-stat-clarity {{ $currentTone }}">
                    <span>{{ $stat['label'] }}</span>
                    <strong>{{ $stat['value'] }}</strong>
                    <small>{{ $stat['hint'] }}</small>
                </article>
            @endforeach
        </section>

        <section class="compliance-card compliance-trend-panel {{ $trendTone }}" aria-label="Evolução operacional do Compliance Engine">
            <header>
                <div>
                    <span class="compliance-section-kicker">Evolução operacional</span>
                    <h2>{{ $trend->get('label', 'Leitura dos últimos dias') }}</h2>
                    <p>{{ $trend->get('summary', 'Acompanhe sinais recentes de pressão operacional, auditoria e evidências.') }}</p>
                </div>
                <span class="compliance-badge {{ $trendTone }}">{{ $trend->get('period', 'Últimos 7 dias') }}</span>
            </header>

            <div class="compliance-trend-grid">
                @forelse ($trendCards as $card)
                    <article class="compliance-trend-card {{ $card['tone'] ?? 'info' }}">
                        <span>{{ $card['title'] ?? 'Indicador' }}</span>
                        <strong>{{ $card['value'] ?? 0 }}</strong>
                        <div class="compliance-trend-delta {{ $card['tone'] ?? 'info' }}">
                            <b>{{ $trendDeltaLabel($card['delta'] ?? null) }}</b>
                            @if (! is_null($card['previous'] ?? null))
                                <small>vs {{ $card['previous'] }} no período anterior</small>
                            @else
                                <small>{{ $trend->get('comparisonPeriod', 'Comparativo') }}</small>
                            @endif
                        </div>
                        <p>{{ $card['hint'] ?? '' }}</p>
                    </article>
                @empty
                    <div class="compliance-empty">Não foi possível calcular sinais recentes para este perfil.</div>
                @endforelse
            </div>

            <footer class="compliance-trend-note">
                {{ $trend->get('note', 'Indicadores calculados em tempo real com os dados disponíveis.') }}
            </footer>
        </section>

        <section class="compliance-grid compliance-engine-main-grid">
            <article class="compliance-card compliance-score compliance-score-clarity {{ $tone }}">
                <div>
                    <span class="compliance-muted">Score atual</span>
                    <strong>{{ $score }}%</strong>
                    <p>{{ $scoreMeta['label'] }}</p>
                    <small>{{ $scoreMeta['instruction'] }}</small>
                </div>
            </article>

            <article class="compliance-card compliance-score-guide">
                <header>
                    <div>
                        <h2>Como interpretar este painel</h2>
                        <p>Use esta leitura para saber se a operação está saudável, em atenção ou em risco.</p>
                    </div>
                </header>

                <div class="compliance-score-bands" aria-label="Faixas de interpretação do score">
                    <div class="ok">
                        <strong>80% a 100%</strong>
                        <span>Saudável</span>
                        <small>Manter rotina de revisão e evidências.</small>
                    </div>
                    <div class="warning">
                        <strong>60% a 79%</strong>
                        <span>Atenção</span>
                        <small>Priorizar atrasos e riscos com maior impacto.</small>
                    </div>
                    <div class="danger">
                        <strong>0% a 59%</strong>
                        <span>Crítico</span>
                        <small>Atuar imediatamente nos itens destacados.</small>
                    </div>
                </div>
            </article>
        </section>

        <section class="compliance-card compliance-priority-board {{ $priorityItems->isNotEmpty() ? 'has-items' : 'is-clear' }}">
            <header>
                <div>
                    <span class="compliance-section-kicker">O que precisa da sua atenção agora</span>
                    <h2>Fila de prioridade operacional</h2>
                    <p>{{ $prioritySummary }}</p>
                </div>
                <span class="compliance-badge {{ $priorityItems->isNotEmpty() ? 'danger' : 'ok' }}">
                    {{ $priorityItems->count() }} item(ns) prioritário(s)
                </span>
            </header>

            @if ($priorityItems->isNotEmpty())
                <div class="compliance-priority-list">
                    @foreach ($priorityItems as $index => $item)
                        <article class="compliance-priority-item {{ $item['tone'] }}">
                            <div class="compliance-priority-rank" aria-label="Prioridade {{ $index + 1 }}">{{ $index + 1 }}</div>
                            <div class="compliance-priority-content">
                                <div class="compliance-priority-heading">
                                    <span class="compliance-priority-type {{ $item['tone'] }}">{{ $item['type'] }}</span>
                                    <strong>{{ $item['title'] }}</strong>
                                </div>
                                <p>{{ $item['reason'] }}</p>
                                <small>{{ $item['empresa'] }} · {{ $item['responsavel'] }} · {{ $item['vencimento'] }}</small>
                            </div>
                            <a class="compliance-priority-action" href="{{ $item['url'] }}" aria-label="{{ $item['action'] }}: {{ $item['title'] }}">{{ $item['action'] }}</a>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="compliance-priority-empty">
                    <strong>Nenhuma urgência crítica no momento.</strong>
                    <span>Use os cards abaixo para manter a rotina de acompanhamento e evitar novos atrasos.</span>
                </div>
            @endif
        </section>


        <section class="compliance-card compliance-recommendations-focus">
            <header>
                <div>
                    <h2>Prioridade de leitura</h2>
                    <p>O sistema organiza abaixo os pontos que explicam o score e orientam a próxima análise.</p>
                </div>
                <span class="compliance-badge {{ $tone }}">{{ $scoreMeta['label'] }}</span>
            </header>

            <div class="compliance-list">
                @forelse ($recommendations as $rec)
                    <div class="compliance-row compliance-row-clarity {{ $rec['tone'] ?? 'info' }}">
                        <div>
                            <h3>{{ $rec['title'] }}</h3>
                            <p>{{ $rec['description'] }}</p>
                        </div>
                        <span class="compliance-badge {{ $rec['tone'] ?? 'info' }}">Prioridade</span>
                    </div>
                @empty
                    <div class="compliance-empty">Nenhuma recomendação crítica agora.</div>
                @endforelse
            </div>
        </section>

        <section class="compliance-grid equal compliance-engine-lists">
            <article class="compliance-card">
                <header>
                    <div>
                        <h2>Riscos críticos</h2>
                        <p>Itens que mais impactam o score e merecem análise antes dos demais.</p>
                    </div>
                    <div class="compliance-card-actions"><span class="compliance-badge {{ $criticalCount > 0 ? 'danger' : 'ok' }}">{{ $criticalCount }} encontrado(s)</span><a href="{{ $riscosUrl }}">Ver todos</a></div>
                </header>

                <div class="compliance-list">
                    @forelse ($criticalRisks as $risk)
                        <div class="compliance-row">
                            <div>
                                <h3>{{ $risk['titulo'] }}</h3>
                                <small>{{ $risk['empresa'] }} · {{ $risk['responsavel'] }} · {{ $risk['vencimento'] }}</small>
                            </div>
                            <a class="compliance-link" href="{{ $risk['url'] }}">Abrir</a>
                        </div>
                    @empty
                        <div class="compliance-empty">Nenhum risco crítico encontrado.</div>
                    @endforelse
                </div>
            </article>

            <article class="compliance-card">
                <header>
                    <div>
                        <h2>Pendências vencidas</h2>
                        <p>Atrasos que reduzem a saúde do compliance e precisam de decisão.</p>
                    </div>
                    <div class="compliance-card-actions"><span class="compliance-badge {{ $lateCount > 0 ? 'danger' : 'ok' }}">{{ $lateCount }} vencida(s)</span><a href="{{ $pendenciasUrl }}">Ver todas</a></div>
                </header>

                <div class="compliance-list">
                    @forelse ($latePendings as $item)
                        <div class="compliance-row">
                            <div>
                                <h3>{{ $item['titulo'] }}</h3>
                                <small>{{ $item['empresa'] }} · {{ $item['responsavel'] }} · {{ $item['vencimento'] }}</small>
                            </div>
                            <a class="compliance-link" href="{{ $item['url'] }}">Abrir</a>
                        </div>
                    @empty
                        <div class="compliance-empty">Nenhuma pendência vencida.</div>
                    @endforelse
                </div>
            </article>
        </section>
    </div>
</x-filament-panels::page>
