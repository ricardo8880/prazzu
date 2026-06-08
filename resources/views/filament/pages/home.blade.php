<x-filament-panels::page>
    <link rel="stylesheet" href="{{ asset('css/home-operacional.css') }}?v={{ file_exists(public_path('css/home-operacional.css')) ? filemtime(public_path('css/home-operacional.css')) : time() }}">

    @php
        $urls = $dashboard['urls'] ?? [];
        $usuario = explode(' ', $dashboard['usuario'] ?? 'Usuário')[0];
        $resumoHoje = $dashboard['resumoHoje'] ?? [];
        $minhasPendencias = $dashboard['minhasPendencias'] ?? [];
        $vencimentosProximos = $dashboard['vencimentosProximos'] ?? [];
        $aprovacoesAguardando = $dashboard['aprovacoesAguardando'] ?? [];
        $itensAtrasados = $dashboard['itensAtrasados'] ?? [];
        $resumoEmpresas = $dashboard['resumoEmpresas'] ?? [];
        $notificacoes = $dashboard['notificacoes'] ?? [];
        $documentosVencidos = $dashboard['documentosVencidos'] ?? [];
        $documentosVencendo = $dashboard['documentosVencendo'] ?? [];

        $resumoHojePorLabel = collect($resumoHoje)->keyBy('label');
        $obrigacoesVencidas = $resumoHojePorLabel->get('Atrasados', []);
        $vencimentosSemana = $resumoHojePorLabel->get('Vencem em 7 dias', []);
        $aprovacoesPendentes = $resumoHojePorLabel->get('Aprovações', []);
        $pendenciasResumo = $resumoHojePorLabel->get('Pendências', []);
        $clientesEmRisco = collect($resumoEmpresas)->filter(fn ($empresa) => in_array($empresa['tone'] ?? '', ['danger', 'warning'], true))->count();
        $centralDiaData = now()->locale('pt_BR')->translatedFormat('d \d\e F \d\e Y');
        $centralDiaSemana = now()->locale('pt_BR')->translatedFormat('l');
        $centralDiaNotificacoes = count($notificacoes ?? []);

        $centralDiaCards = [
            ['label' => 'Obrigações vencidas', 'value' => $obrigacoesVencidas['value'] ?? 0, 'hint' => 'Risco de multa', 'tone' => 'danger', 'icon' => 'calendar-alert', 'url' => $obrigacoesVencidas['url'] ?? ($urls['prazos'] ?? '#')],
            ['label' => 'Vencem hoje', 'value' => $vencimentosSemana['value'] ?? 0, 'hint' => 'Vencimento hoje', 'tone' => 'orange', 'icon' => 'calendar-check', 'url' => $vencimentosSemana['url'] ?? ($urls['prazos'] ?? '#')],
            ['label' => 'Clientes sem enviar documentos', 'value' => count($documentosVencidos) + count($documentosVencendo), 'hint' => 'Podem gerar atraso', 'tone' => 'amber', 'icon' => 'users-alert', 'url' => $urls['documentos'] ?? '#'],
            ['label' => 'Pendências paradas há muitos dias', 'value' => count($minhasPendencias), 'hint' => 'Acima de 5 dias', 'tone' => 'purple', 'icon' => 'clock', 'url' => $urls['minhasPendencias'] ?? '#'],
            ['label' => 'Aprovações travadas', 'value' => $aprovacoesPendentes['value'] ?? count($aprovacoesAguardando), 'hint' => 'Aguardando ação', 'tone' => 'blue', 'icon' => 'approval', 'url' => $aprovacoesPendentes['url'] ?? ($urls['centralAprovacoes'] ?? '#')],
            ['label' => 'Tarefas sem responsável', 'value' => collect($minhasPendencias)->filter(fn ($item) => empty($item['responsavel']) || $item['responsavel'] === 'Sem responsável')->count(), 'hint' => 'Risco de não execução', 'tone' => 'teal', 'icon' => 'user-search', 'url' => $urls['tarefas'] ?? '#'],
            ['label' => 'Clientes em risco', 'value' => $clientesEmRisco, 'hint' => 'Alto risco de atraso', 'tone' => 'danger', 'icon' => 'shield-alert', 'url' => $urls['tarefas'] ?? '#'],
        ];

        $filaPrioridade = collect();

        foreach ($itensAtrasados as $item) {
            $filaPrioridade->push(['peso' => 10, 'prioridade' => 'Crítico', 'badge' => 'danger', 'tipo' => 'Obrigação vencida', 'descricao' => $item['titulo'] ?? 'Item atrasado', 'cliente' => $item['empresa'] ?? 'Sem empresa', 'vencimento' => $item['data'] ?? ($item['tempo'] ?? '-'), 'atraso' => $item['tempo'] ?? 'Atrasado', 'url' => $item['url'] ?? ($urls['prazos'] ?? '#')]);
        }

        foreach ($vencimentosProximos as $item) {
            $filaPrioridade->push(['peso' => 8, 'prioridade' => 'Alto', 'badge' => 'warning', 'tipo' => 'Vence hoje', 'descricao' => $item['titulo'] ?? 'Vencimento próximo', 'cliente' => $item['empresa'] ?? 'Sem empresa', 'vencimento' => $item['data'] ?? '-', 'atraso' => $item['tempo'] ?? 'Hoje', 'url' => $item['url'] ?? ($urls['prazos'] ?? '#')]);
        }

        foreach ($documentosVencidos as $item) {
            $filaPrioridade->push(['peso' => 7, 'prioridade' => 'Alto', 'badge' => 'warning', 'tipo' => 'Sem documentos', 'descricao' => $item['titulo'] ?? 'Documento pendente', 'cliente' => $item['empresa'] ?? 'Sem empresa', 'vencimento' => $item['data'] ?? '-', 'atraso' => $item['tempo'] ?? 'Pendente', 'url' => $item['url'] ?? ($urls['documentos'] ?? '#')]);
        }

        foreach ($minhasPendencias as $item) {
            $filaPrioridade->push(['peso' => 5, 'prioridade' => ($item['badge'] ?? '') === 'danger' ? 'Crítico' : 'Médio', 'badge' => $item['badge'] ?? 'info', 'tipo' => 'Pendência parada', 'descricao' => $item['titulo'] ?? 'Pendência operacional', 'cliente' => $item['empresa'] ?? 'Sem empresa', 'vencimento' => $item['data'] ?? '-', 'atraso' => $item['status'] ?? ($item['responsavel'] ?? '-'), 'url' => $item['url'] ?? ($urls['minhasPendencias'] ?? '#')]);
        }

        foreach ($aprovacoesAguardando as $aprovacao) {
            $filaPrioridade->push(['peso' => 4, 'prioridade' => 'Médio', 'badge' => 'info', 'tipo' => 'Aprovação travada', 'descricao' => $aprovacao['titulo'] ?? 'Aprovação aguardando', 'cliente' => $aprovacao['empresa'] ?? 'Sem empresa', 'vencimento' => '-', 'atraso' => $aprovacao['tempo'] ?? 'Aguardando', 'url' => $aprovacao['url'] ?? ($urls['centralAprovacoes'] ?? '#')]);
        }

        $filaPrioridadeTotal = $filaPrioridade->count();
        $filaPrioridade = $filaPrioridade->sortByDesc('peso')->take(7)->values();

        $clientesMaiorRisco = collect($resumoEmpresas ?? [])->map(function ($empresa) use ($urls) {
            $atrasados = (int) ($empresa['atrasados'] ?? 0);
            $vencendo = (int) ($empresa['vencendo'] ?? 0);
            $total = (int) ($empresa['total'] ?? 0);
            $score = ($atrasados * 3) + ($vencendo * 2) + $total;
            $riscoLabel = $empresa['risco'] ?? 'Baixo';
            $riscoTone = $empresa['tone'] ?? 'success';

            if ($atrasados >= 5 || $score >= 18) {
                $riscoLabel = 'Muito alto';
                $riscoTone = 'danger';
            } elseif ($atrasados >= 2 || $score >= 10) {
                $riscoLabel = 'Alto';
                $riscoTone = 'warning';
            } elseif ($atrasados >= 1 || $vencendo >= 2 || $score >= 5) {
                $riscoLabel = 'Médio';
                $riscoTone = 'info';
            }

            $problemas = [];
            if ($atrasados > 0) $problemas[] = $atrasados . ' ' . ($atrasados === 1 ? 'item atrasado' : 'itens atrasados');
            if ($vencendo > 0) $problemas[] = $vencendo . ' ' . ($vencendo === 1 ? 'vencendo hoje/próximo' : 'vencendo hoje/próximos');
            if (empty($problemas)) $problemas[] = $total > 0 ? $total . ' ' . ($total === 1 ? 'item aberto' : 'itens abertos') : 'Sem pendências críticas';

            return ['score' => $score, 'empresa' => $empresa['empresa'] ?? 'Sem empresa', 'risco' => $riscoLabel, 'tone' => $riscoTone, 'problemas' => implode(', ', $problemas), 'ultima_atividade' => $empresa['ultima_atividade'] ?? ($empresa['atualizado_em'] ?? ($empresa['data'] ?? '-')), 'url' => $empresa['url'] ?? ($urls['tarefas'] ?? '#')];
        })->sortByDesc('score')->take(5)->values();

        $calendarioHoje = collect();
        foreach ($vencimentosProximos as $item) {
            $calendarioHoje->push(['hora' => $item['hora'] ?? '09:00', 'titulo' => $item['titulo'] ?? 'Vencimento operacional', 'descricao' => $item['empresa'] ?? 'Sem empresa', 'tone' => 'danger', 'url' => $item['url'] ?? ($urls['prazos'] ?? '#')]);
        }
        foreach ($aprovacoesAguardando as $item) {
            $calendarioHoje->push(['hora' => $item['hora'] ?? '14:00', 'titulo' => $item['titulo'] ?? 'Aprovação pendente', 'descricao' => $item['empresa'] ?? 'Sem empresa', 'tone' => 'info', 'url' => $item['url'] ?? ($urls['centralAprovacoes'] ?? '#')]);
        }
        $calendarioHoje = $calendarioHoje->take(4)->values();

        $totalClientesAtivos = collect($resumoEmpresas)->count();
        $totalObrigacoesMes = (int) (($pendenciasResumo['value'] ?? 0) + ($obrigacoesVencidas['value'] ?? 0) + ($vencimentosSemana['value'] ?? 0));
        $totalConcluidasReferencia = max(0, $totalObrigacoesMes - (int) ($obrigacoesVencidas['value'] ?? 0));
        $percentualConcluido = $totalObrigacoesMes > 0 ? min(100, max(0, (int) round(($totalConcluidasReferencia / $totalObrigacoesMes) * 100))) : 0;
        $emRiscoAtraso = count($itensAtrasados) + count($documentosVencidos) + $clientesEmRisco;
    @endphp

    <div data-home-layout="operational" class="pz-central-page" x-data="pzHomeResolutionModal()" x-on:keydown.escape.window="closeResolutionModal()">
        <section class="pz-central-topbar">
            <div class="pz-central-title-group">
                <span class="pz-central-shield" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none"><path d="M12 3l7 3v5c0 4.7-3 8.8-7 10-4-1.2-7-5.3-7-10V6l7-3z" stroke="currentColor" stroke-width="1.9"/><path d="M12 8v5" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/><path d="M12 16h.01" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/></svg>
                </span>
                <div>
                    <h1>Central do Dia</h1>
                    <p>Priorize o que pode gerar atraso, multa ou retrabalho hoje.</p>
                </div>
            </div>

            <div class="pz-central-actions">
                <a href="{{ $urls['centralNotificacoes'] ?? '#' }}" class="pz-central-bell" aria-label="Ver notificações">
                    <svg viewBox="0 0 24 24" fill="none"><path d="M15 17H5a2 2 0 0 1 1.5-1.94V10a5.5 5.5 0 0 1 11 0v5.06A2 2 0 0 1 19 17h-4z" stroke="currentColor" stroke-width="1.8"/><path d="M10 20a2 2 0 0 0 4 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                    @if($centralDiaNotificacoes > 0)<b>{{ $centralDiaNotificacoes > 99 ? '99+' : $centralDiaNotificacoes }}</b>@endif
                </a>
                <div class="pz-central-user">
                    <div class="pz-central-avatar">{{ strtoupper(mb_substr($usuario ?? 'U', 0, 1)) }}</div>
                    <div><strong>{{ $usuario }}</strong><small>Administrador</small></div>
                </div>
            </div>
        </section>

        <section class="pz-central-date-row">
            <div></div>
            <div class="pz-central-date-actions">
                <div class="pz-central-date-card"><span aria-hidden="true">▣</span><div><strong>{{ $centralDiaData }}</strong><small>{{ ucfirst($centralDiaSemana) }}</small></div></div>
                <a href="{{ request()->fullUrl() }}" class="pz-refresh-btn">↻ Atualizar</a>
            </div>
        </section>

        <section class="pz-central-day-grid" aria-label="Resumo crítico da Central do Dia">
            @foreach($centralDiaCards as $card)
                <a href="{{ $card['url'] ?? '#' }}" class="pz-central-day-card pz-tone-{{ $card['tone'] ?? 'slate' }}">
                    <span class="pz-central-day-icon" aria-hidden="true"><i class="pz-icon-{{ $card['icon'] ?? 'dot' }}"></i></span>
                    <span class="pz-card-label">{{ $card['label'] ?? '-' }}</span>
                    <strong>{{ $card['value'] ?? 0 }}</strong>
                    <small>{{ $card['hint'] ?? 'Acompanhar' }}</small>
                    <em>Ver detalhes →</em>
                </a>
            @endforeach
        </section>

        <section class="pz-content-grid">
            <main class="pz-main-column">
                <section class="pz-panel pz-priority-panel">
                    <div class="pz-section-head">
                        <div><h2>Fila de Prioridade</h2><p>O que atacar primeiro hoje (ordenado por criticidade)</p></div>
                        <a href="{{ $urls['minhasPendencias'] ?? '#' }}">Ver todas ({{ $filaPrioridadeTotal }})</a>
                    </div>
                    <div class="pz-table-wrap">
                        <table class="pz-priority-table">
                            <thead><tr><th>Prioridade</th><th>Tipo</th><th>Descrição</th><th>Cliente</th><th>Vencimento / Data</th><th>Dias em atraso</th></tr></thead>
                            <tbody>
                                @forelse($filaPrioridade as $item)
                                    <tr class="pz-clickable-row" @click="openPriority({{ \Illuminate\Support\Js::from($item) }})">
                                        <td><span class="pz-priority-badge pz-priority-{{ $item['badge'] ?? 'info' }}">{{ $item['prioridade'] ?? 'Médio' }}</span></td>
                                        <td>{{ $item['tipo'] ?? '-' }}</td>
                                        <td><strong>{{ $item['descricao'] ?? '-' }}</strong></td>
                                        <td>{{ $item['cliente'] ?? 'Sem empresa' }}</td>
                                        <td><strong>{{ $item['vencimento'] ?? '-' }}</strong></td>
                                        <td class="pz-delay-cell">{{ $item['atraso'] ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="pz-empty-cell">Nenhuma prioridade crítica encontrada para hoje.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="pz-panel pz-risk-clients-panel">
                    <div class="pz-section-head">
                        <div><h2>Clientes em Maior Risco</h2><p>Risco calculado com base em atrasos, pendências e documentos</p></div>
                        <a href="{{ $urls['tarefas'] ?? '#' }}">Ver todos</a>
                    </div>
                    <div class="pz-table-wrap">
                        <table class="pz-risk-clients-table">
                            <thead><tr><th>Cliente</th><th>Risco</th><th>Principais problemas</th><th>Última atividade</th></tr></thead>
                            <tbody>
                                @forelse($clientesMaiorRisco as $cliente)
                                    <tr class="pz-clickable-row" @click="openClient({{ \Illuminate\Support\Js::from($cliente) }})">
                                        <td><strong>{{ $cliente['empresa'] ?? 'Sem empresa' }}</strong></td>
                                        <td><span class="pz-risk-badge pz-risk-{{ $cliente['tone'] ?? 'success' }}">{{ $cliente['risco'] ?? 'Baixo' }}</span></td>
                                        <td>{{ $cliente['problemas'] ?? '-' }}</td>
                                        <td>{{ $cliente['ultima_atividade'] ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="pz-empty-cell">Nenhum cliente em risco encontrado.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </main>

            <aside class="pz-side-column">
                <section class="pz-panel pz-calendar-panel">
                    <div class="pz-section-head"><div><h2>Calendário de Hoje</h2></div><a href="{{ $urls['prazos'] ?? '#' }}">Ver calendário</a></div>
                    <div class="pz-timeline">
                        @forelse($calendarioHoje as $evento)
                            <a href="{{ $evento['url'] ?? '#' }}" class="pz-timeline-event pz-event-{{ $evento['tone'] ?? 'slate' }}">
                                <span class="pz-event-time">{{ $evento['hora'] ?? '--:--' }}</span><span class="pz-event-line"></span>
                                <span><strong>{{ $evento['titulo'] ?? '-' }}</strong><small>{{ $evento['descricao'] ?? 'Operação' }}</small></span>
                            </a>
                        @empty
                            <div class="pz-empty-side">Nenhum evento crítico para hoje.</div>
                        @endforelse
                    </div>
                    <a href="{{ $urls['prazos'] ?? '#' }}" class="pz-side-link">Ver todas as obrigações do dia →</a>
                </section>

                <section class="pz-panel pz-summary-panel">
                    <div class="pz-section-head"><div><h2>Resumo do Dia</h2></div></div>
                    <div class="pz-summary-list">
                        <div><span>Total de clientes ativos</span><strong>{{ $totalClientesAtivos }}</strong></div>
                        <div><span>Obrigações este mês</span><strong class="is-green">{{ $totalObrigacoesMes }}</strong></div>
                        <div><span>Concluídas</span><strong class="is-green">{{ $percentualConcluido }}%</strong></div>
                        <div class="pz-progress-track"><span style="width: {{ $percentualConcluido }}%"></span></div>
                        <div><span>Em risco de atraso</span><strong class="is-red">{{ $emRiscoAtraso }}</strong></div>
                    </div>
                    <a href="{{ $urls['relatorios'] ?? ($urls['tarefas'] ?? '#') }}" class="pz-side-link">Ver relatório completo →</a>
                </section>
            </aside>
        </section>

        <div class="pz-resolution-modal" x-show="resolutionOpen" x-cloak>
            <div class="pz-resolution-backdrop" @click="closeResolutionModal()"></div>
            <article class="pz-resolution-shell pz-resolution-shell-v2" role="dialog" aria-modal="true" aria-labelledby="pz-resolution-title" @click.stop>
                <button type="button" class="pz-resolution-x" @click="closeResolutionModal()" aria-label="Fechar">×</button>

                <header class="pz-resolution-v2-head">
                    <div class="pz-resolution-v2-title-area">
                        <div class="pz-resolution-breadcrumb">
                            <span x-text="resolutionType === 'client' ? 'Clientes em Maior Risco' : 'Fila de Prioridade'"></span>
                            <b>›</b>
                            <span x-text="resolutionType === 'client' ? 'Cliente em risco' : (selectedPriority.tipo || 'Item prioritário')"></span>
                        </div>

                        <div class="pz-resolution-v2-title-row">
                            <span class="pz-resolution-alert-icon" :class="resolutionType === 'client' ? 'is-client' : 'is-danger'" aria-hidden="true">
                                <template x-if="resolutionType === 'client'"><span>◆</span></template>
                                <template x-if="resolutionType !== 'client'"><span>!</span></template>
                            </span>
                            <h2 id="pz-resolution-title" x-text="modalTitle()"></h2>
                            <span class="pz-resolution-severity" :class="severityClass()" x-text="severityLabel()"></span>
                        </div>

                        <div class="pz-resolution-meta-row">
                            <span><b>Cliente:</b> <em x-text="modalClientName()"></em></span>
                            <span><b>Responsável:</b> <em x-text="responsibleName()"></em></span>
                            <span><b>Tipo:</b> <em x-text="modalType()"></em></span>
                            <span><b>Área:</b> <em x-text="areaName()"></em></span>
                            <span><b>ID:</b> <em x-text="modalId()"></em></span>
                        </div>
                    </div>

                    <div class="pz-resolution-v2-actions">
                        <a class="pz-resolution-secondary-btn" :href="currentUrl()">Ir para o cadastro ↗</a>
                        <button type="button" class="pz-resolution-primary-btn" @click="copyCompletionMessage()">✓ Preparar conclusão</button>
                        <button type="button" class="pz-resolution-menu-btn" @click="copyResolutionText()">⋮</button>
                    </div>
                </header>

                <div class="pz-resolution-v2-body">
                    <section class="pz-resolution-top-grid">
                        <div class="pz-resolution-risk-card" :class="severityClass()">
                            <span class="pz-resolution-risk-label" x-text="riskHeadline()"></span>
                            <strong x-text="mainRiskNumber()"></strong>
                            <p x-text="riskDescription()"></p>
                            <div class="pz-resolution-risk-money">
                                <span x-text="resolutionType === 'client' ? 'Risco operacional' : 'Impacto estimado'"></span>
                                <b x-text="financialImpactText()"></b>
                            </div>
                        </div>

                        <div class="pz-resolution-impact-card">
                            <h3>Impacto</h3>
                            <div class="pz-resolution-impact-line"><span>SLA interno</span><b :class="isCritical() ? 'is-red' : ''" x-text="slaText()"></b></div>
                            <div class="pz-resolution-impact-line"><span>Risco financeiro</span><b :class="isCritical() ? 'is-red' : ''" x-text="financialRiskLevel()"></b></div>
                            <div class="pz-resolution-impact-line"><span>Probabilidade de atraso</span><b :class="isCritical() ? 'is-red' : ''" x-text="delayProbability()"></b></div>
                            <div class="pz-resolution-impact-line"><span>Impacto no cliente</span><b :class="isCritical() ? 'is-red' : ''" x-text="clientImpactLevel()"></b></div>
                        </div>

                        <div class="pz-resolution-progress-card">
                            <div class="pz-resolution-progress-head">
                                <h3>Progresso de resolução</h3>
                                <a :href="currentUrl()">Ver dados</a>
                            </div>
                            <div class="pz-resolution-steps" :style="`--pz-progress:${progressPercent()}%`">
                                <template x-for="(step, index) in progressSteps()" :key="step">
                                    <div class="pz-resolution-step" :class="index < activeStepIndex() ? 'is-done' : (index === activeStepIndex() ? 'is-active' : '')">
                                        <span x-text="index + 1"></span>
                                        <b x-text="step"></b>
                                    </div>
                                </template>
                            </div>
                            <small><span x-text="progressPercent()"></span>% concluído</small>
                        </div>

                        <div class="pz-resolution-deadline-card" :class="severityClass()">
                            <h3>Prazo final recomendado</h3>
                            <strong x-text="deadlineRecommendation()"></strong>
                            <p x-text="deadlineReason()"></p>
                            <div class="pz-resolution-countdown">
                                <span><b x-text="countdownParts().h"></b><small>h</small></span>
                                <i>:</i>
                                <span><b x-text="countdownParts().m"></b><small>min</small></span>
                                <i>:</i>
                                <span><b x-text="countdownParts().s"></b><small>seg</small></span>
                            </div>
                        </div>
                    </section>

                    <section class="pz-resolution-action-grid">
                        <div class="pz-resolution-next-action-card">
                            <span>PRÓXIMA AÇÃO RECOMENDADA</span>
                            <h3 x-text="nextActionTitle()"></h3>
                            <p x-text="nextActionDescription()"></p>
                            <div class="pz-resolution-next-action-meta">
                                <div><small>Origem da pendência</small><b x-text="blockOrigin()"></b></div>
                                <div><small>Prioridade</small><b class="is-red" x-text="severityLabel()"></b></div>
                                <div><small>Bloqueia transmissão</small><b class="is-red" x-text="blocksTransmission()"></b></div>
                            </div>
                            <button type="button" class="pz-resolution-primary-btn" @click="copyRecommendedAction()">⚡ Resolver agora</button>
                        </div>

                        <div class="pz-resolution-quick-card">
                            <h3>Ações rápidas</h3>
                            <div class="pz-resolution-quick-grid">
                                <button type="button" @click="copyWhatsAppMessage()"><span>☘</span><b>Cobrar cliente</b><small>WhatsApp</small></button>
                                <button type="button" @click="copyEmailMessage()"><span>✉</span><b>Enviar e-mail</b><small>Cobrança</small></button>
                                <button type="button" @click="copyDocumentRequest()"><span>▤</span><b>Solicitar documento</b><small>Do cliente</small></button>
                                <button type="button" @click="copyReceivedMessage()"><span>✓</span><b>Marcar como recebido</b><small>Mensagem pronta</small></button>
                                <button type="button" @click="copyDelegationMessage()"><span>♙</span><b>Delegar tarefa</b><small>Outro responsável</small></button>
                                <button type="button" @click="copyRescheduleMessage()"><span>◷</span><b>Reagendar prazo</b><small>Nova data</small></button>
                                <button type="button" @click="copyCompletionMessage()"><span>✓</span><b>Concluir obrigação</b><small>Finalizar</small></button>
                                <a :href="currentUrl()"><span>☊</span><b>Abrir cadastro</b><small>Editar dados</small></a>
                            </div>
                            <small class="pz-resolution-copy-feedback" x-show="copied" x-text="copied"></small>
                        </div>
                    </section>

                    <section class="pz-resolution-tabs">
                        <button type="button" class="is-active">Checklist de resolução</button>
                        <button type="button" @click="copyDocumentRequest()">Documentos</button>
                        <button type="button" @click="copyPendingSummary()">Pendências</button>
                        <button type="button" @click="copyActivitySummary()">Histórico</button>
                        <button type="button" @click="copyResolutionText()">Comentários</button>
                    </section>

                    <section class="pz-resolution-content-grid">
                        <div class="pz-resolution-card pz-resolution-checklist-card">
                            <h3>Checklist de resolução</h3>
                            <p>Execute os passos e conclua com o mínimo de troca de tela.</p>
                            <div class="pz-resolution-checklist-v2">
                                <template x-for="(step, index) in resolutionChecklist()" :key="step.title">
                                    <label :class="index === 0 ? 'is-current' : ''">
                                        <input type="checkbox">
                                        <span x-text="index + 1"></span>
                                        <b x-text="step.title"></b>
                                        <small x-text="step.subtitle"></small>
                                        <em x-text="index === 0 ? 'Agora' : 'Pendente'"></em>
                                    </label>
                                </template>
                            </div>
                            <button type="button" class="pz-resolution-outline-full" @click="copyChecklistDoneMessage()">✓ Marcar etapa como concluída</button>
                        </div>

                        <div class="pz-resolution-card pz-resolution-finance-card">
                            <h3>Informações financeiras</h3>
                            <div class="pz-resolution-money-list">
                                <div><span>Multa mínima</span><b x-text="minimumPenaltyText()"></b></div>
                                <div><span>Multa estimada</span><b class="is-red" x-text="estimatedPenaltyText()"></b></div>
                                <div><span>Juros diários</span><b x-text="dailyInterestText()"></b></div>
                                <div><span>Risco financeiro total</span><b class="is-red" x-text="totalFinancialRiskText()"></b></div>
                            </div>
                            <div class="pz-resolution-warning-box">⚠ Quanto antes resolver, menor o prejuízo e o retrabalho.</div>
                        </div>

                        <div class="pz-resolution-card pz-resolution-client-card">
                            <div class="pz-resolution-card-head-link"><h3>Cliente</h3><a :href="currentUrl()">Ver dados</a></div>
                            <div class="pz-resolution-client-main">
                                <span x-text="clientInitials()"></span>
                                <div><strong x-text="modalClientName()"></strong><small x-text="clientStatusText()"></small></div>
                            </div>
                            <div class="pz-resolution-client-kpis">
                                <div><span>Obrigações em atraso</span><b class="is-red" x-text="clientDelayedCount()"></b></div>
                                <div><span>Pendências abertas</span><b class="is-orange" x-text="clientPendingCount()"></b></div>
                                <div><span>Risco do cliente</span><b class="is-red" x-text="clientRiskText()"></b></div>
                            </div>
                        </div>

                        <div class="pz-resolution-card pz-resolution-owner-card">
                            <h3>Responsável</h3>
                            <div class="pz-resolution-owner-main">
                                <span x-text="responsibleInitials()"></span>
                                <div><strong x-text="responsibleName()"></strong><small x-text="areaName()"></small></div>
                            </div>
                            <div class="pz-resolution-contact-row">
                                <button type="button" @click="copyWhatsAppMessage()">☘</button>
                                <button type="button" @click="copyEmailMessage()">✉</button>
                                <button type="button" @click="copyResolutionText()">☷</button>
                                <button type="button" @click="copyDelegationMessage()">↗</button>
                            </div>
                            <button type="button" class="pz-resolution-outline-full" @click="copyDelegationMessage()">Alterar responsável</button>
                        </div>

                        <div class="pz-resolution-card pz-resolution-docs-card">
                            <h3>Documentos necessários</h3>
                            <div class="pz-resolution-list-v2">
                                <template x-for="doc in documentsList()" :key="doc.name">
                                    <div><b x-text="doc.name"></b><span :class="doc.statusClass" x-text="doc.status"></span><button type="button" @click="copyDocumentRequest(doc.name)">Solicitar</button></div>
                                </template>
                            </div>
                            <a :href="currentUrl()">Ver todos documentos</a>
                        </div>

                        <div class="pz-resolution-card pz-resolution-pending-card">
                            <h3>Pendências relacionadas</h3>
                            <div class="pz-resolution-list-v2">
                                <template x-for="pending in pendingList()" :key="pending.name">
                                    <div><b x-text="pending.name"></b><span :class="pending.statusClass" x-text="pending.status"></span><button type="button" @click="copyPendingSummary(pending.name)">Resolver</button></div>
                                </template>
                            </div>
                            <a :href="currentUrl()">Ver todas pendências</a>
                        </div>

                        <div class="pz-resolution-card pz-resolution-activities-card">
                            <h3>Atividades recentes</h3>
                            <div class="pz-resolution-timeline-v2">
                                <template x-for="activity in activitiesList()" :key="activity.text + activity.time">
                                    <div><span></span><b x-text="activity.time"></b><p x-text="activity.text"></p></div>
                                </template>
                            </div>
                            <a :href="currentUrl()">Ver todas atividades</a>
                        </div>

                        <div class="pz-resolution-card pz-resolution-help-card">
                            <h3>Precisa de ajuda?</h3>
                            <p>Use o resumo abaixo para chamar suporte ou encaminhar internamente com contexto completo.</p>
                            <div class="pz-resolution-help-actions">
                                <button type="button" @click="copySupportMessage()">Abrir chamado →</button>
                                <button type="button" @click="copyResolutionText()">Copiar contexto →</button>
                            </div>
                        </div>
                    </section>
                </div>

                <footer class="pz-resolution-footer">
                    <span>Atalho rápido: <b>Ctrl + Enter</b> preparar conclusão</span>
                    <div>
                        <button type="button" class="pz-resolution-secondary-btn" @click="copyResolutionText()">Salvar rascunho</button>
                        <button type="button" class="pz-resolution-primary-btn" @click="copyCompletionMessage()">✓ Preparar conclusão</button>
                    </div>
                </footer>
            </article>
        </div>

        <script>
            function pzHomeResolutionModal() {
                return {
                    resolutionOpen: false,
                    resolutionType: 'priority',
                    selectedPriority: {},
                    selectedClient: {},
                    copied: '',
                    priorityItems: @js($filaPrioridade->values()),
                    openPriority(item) {
                        this.selectedPriority = item || {};
                        this.selectedClient = {};
                        this.resolutionType = 'priority';
                        this.copied = '';
                        this.resolutionOpen = true;
                    },
                    openClient(client) {
                        this.selectedClient = client || {};
                        this.selectedPriority = {};
                        this.resolutionType = 'client';
                        this.copied = '';
                        this.resolutionOpen = true;
                    },
                    closeResolutionModal() { this.resolutionOpen = false; },
                    currentUrl() { return this.resolutionType === 'client' ? (this.selectedClient.url || '#') : (this.selectedPriority.url || '#'); },
                    modalTitle() { return this.resolutionType === 'client' ? (this.selectedClient.empresa || 'Cliente em risco') : (this.selectedPriority.descricao || 'Item prioritário'); },
                    modalClientName() { return this.resolutionType === 'client' ? (this.selectedClient.empresa || 'Cliente') : (this.selectedPriority.cliente || 'Sem empresa'); },
                    modalType() { return this.resolutionType === 'client' ? 'Cliente em risco' : (this.selectedPriority.tipo || 'Item operacional'); },
                    modalId() { return this.resolutionType === 'client' ? this.slugId(this.selectedClient.empresa || 'cliente') : this.slugId(this.selectedPriority.descricao || 'item'); },
                    areaName() {
                        const type = this.modalType().toLowerCase();
                        if (type.includes('document')) return 'Documentos';
                        if (type.includes('aprovação')) return 'Aprovações';
                        if (type.includes('obrigação') || type.includes('vence')) return 'Fiscal';
                        return 'Operacional';
                    },
                    responsibleName() { return 'Responsável interno'; },
                    responsibleInitials() { return this.initials(this.responsibleName()); },
                    clientInitials() { return this.initials(this.modalClientName()); },
                    initials(text) { return (text || 'PR').split(' ').filter(Boolean).slice(0, 2).map((p) => p[0]).join('').toUpperCase(); },
                    slugId(text) { return '#' + (text || 'item').toString().normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/[^a-zA-Z0-9]+/g, '-').replace(/^-|-$/g, '').toUpperCase().slice(0, 18); },
                    severityLabel() {
                        if (this.resolutionType === 'client') return this.selectedClient.risco || 'Risco';
                        return this.selectedPriority.prioridade || 'Prioridade';
                    },
                    severityClass() {
                        const label = this.severityLabel().toLowerCase();
                        if (label.includes('crítico') || label.includes('critico') || label.includes('muito')) return 'is-critical';
                        if (label.includes('alto')) return 'is-high';
                        if (label.includes('médio') || label.includes('medio')) return 'is-medium';
                        return 'is-low';
                    },
                    isCritical() { return ['is-critical', 'is-high'].includes(this.severityClass()); },
                    extractNumber(text) {
                        const found = String(text || '').match(/\d+/);
                        return found ? parseInt(found[0], 10) : 0;
                    },
                    relatedClientItems() {
                        const cliente = (this.selectedClient.empresa || '').toString().toLowerCase();
                        if (!cliente) return [];
                        return (this.priorityItems || []).filter((item) => (item.cliente || '').toString().toLowerCase() === cliente).slice(0, 8);
                    },
                    clientDelayedCount() {
                        if (this.resolutionType !== 'client') return this.extractNumber(this.selectedPriority.atraso || '');
                        const items = this.relatedClientItems();
                        return items.filter((item) => (item.tipo || '').toLowerCase().includes('venc') || (item.atraso || '').toLowerCase().includes('atr')).length || this.extractNumber(this.selectedClient.problemas || '0');
                    },
                    clientPendingCount() {
                        if (this.resolutionType !== 'client') return (this.modalType().toLowerCase().includes('document') || this.modalType().toLowerCase().includes('pend')) ? 1 : 0;
                        const items = this.relatedClientItems();
                        return items.filter((item) => !(item.tipo || '').toLowerCase().includes('venc')).length || this.extractNumber(this.selectedClient.problemas || '0');
                    },
                    clientRiskText() { return this.resolutionType === 'client' ? (this.selectedClient.risco || 'Em análise') : this.severityLabel(); },
                    clientStatusText() { return this.isCritical() ? 'Atenção necessária hoje' : 'Em acompanhamento'; },
                    riskHeadline() { return this.resolutionType === 'client' ? 'RISCO DO CLIENTE' : (this.isCritical() ? 'RISCO CRÍTICO' : 'RISCO OPERACIONAL'); },
                    mainRiskNumber() {
                        if (this.resolutionType === 'client') return this.selectedClient.risco || 'Em risco';
                        const atraso = this.selectedPriority.atraso || this.selectedPriority.vencimento || '-';
                        return atraso;
                    },
                    riskDescription() {
                        if (this.resolutionType === 'client') return this.selectedClient.problemas || 'Cliente possui pendências que podem gerar atraso.';
                        return `${this.selectedPriority.tipo || 'Item'} · ${this.selectedPriority.vencimento || 'Sem data informada'}`;
                    },
                    financialImpactText() { return this.isCritical() ? 'Verificar multa/impacto' : 'Acompanhar'; },
                    financialRiskLevel() { return this.isCritical() ? 'Alto' : 'Médio'; },
                    delayProbability() { return this.isCritical() ? 'Alta' : 'Moderada'; },
                    clientImpactLevel() { return this.isCritical() ? 'Alto' : 'Médio'; },
                    slaText() { return this.isCritical() ? 'Atenção hoje' : 'Dentro do acompanhamento'; },
                    deadlineRecommendation() { return this.isCritical() ? 'Resolver hoje' : 'Resolver na próxima janela'; },
                    deadlineReason() { return this.isCritical() ? 'para evitar multa, atraso ou retrabalho' : 'para manter o fluxo sem acúmulo'; },
                    countdownParts() { return this.isCritical() ? {h: '06', m: '00', s: '00'} : {h: '--', m: '--', s: '--'}; },
                    progressSteps() {
                        if (this.resolutionType === 'client') return ['Mapear', 'Cobrar', 'Executar', 'Validar', 'Concluir'];
                        const type = this.modalType().toLowerCase();
                        if (type.includes('document')) return ['Solicitar', 'Receber', 'Validar', 'Anexar', 'Concluir'];
                        if (type.includes('aprovação')) return ['Abrir', 'Acionar', 'Revisar', 'Aprovar', 'Concluir'];
                        return ['Iniciar', 'Coletar docs', 'Preparar', 'Transmitir', 'Concluir'];
                    },
                    activeStepIndex() {
                        const type = this.modalType().toLowerCase();
                        if (type.includes('document')) return 0;
                        if (type.includes('aprovação')) return 1;
                        if ((this.selectedPriority.atraso || '').toLowerCase().includes('há')) return 2;
                        return 1;
                    },
                    progressPercent() { return Math.max(15, Math.min(85, this.activeStepIndex() * 20 + 15)); },
                    nextActionTitle() {
                        if (this.resolutionType === 'client') return 'Resolver primeiro os itens vencidos deste cliente';
                        const type = this.modalType().toLowerCase();
                        if (type.includes('document')) return 'Solicitar documento ao cliente';
                        if (type.includes('aprovação')) return 'Acionar o aprovador responsável';
                        if (type.includes('obrigação') || type.includes('vence')) return 'Regularizar obrigação e conferir documentos';
                        return 'Definir responsável e próximo passo';
                    },
                    nextActionDescription() {
                        if (this.resolutionType === 'client') return 'Centralize cobrança, execução e validação dos bloqueios do cliente sem sair desta tela.';
                        const type = this.modalType().toLowerCase();
                        if (type.includes('document')) return 'Sem o documento, o fluxo pode ficar bloqueado e virar atraso operacional.';
                        if (type.includes('aprovação')) return 'Sem aprovação, a equipe não consegue avançar para conclusão.';
                        if (type.includes('obrigação') || type.includes('vence')) return 'Priorize a execução para evitar multa, retrabalho ou reclamação do cliente.';
                        return 'Registre contexto, cobre o responsável e acompanhe até destravar.';
                    },
                    blockOrigin() {
                        const type = this.modalType().toLowerCase();
                        if (type.includes('document')) return 'Cliente';
                        if (type.includes('aprovação')) return 'Interno';
                        return this.resolutionType === 'client' ? 'Múltiplas origens' : 'Operação';
                    },
                    blocksTransmission() {
                        const type = this.modalType().toLowerCase();
                        return (type.includes('document') || type.includes('obrigação') || type.includes('vence')) ? 'Sim' : 'Pode bloquear';
                    },
                    resolutionChecklist() {
                        if (this.resolutionType === 'client') return [
                            {title: 'Listar itens críticos do cliente', subtitle: this.selectedClient.problemas || 'Verificar atrasos e pendências'},
                            {title: 'Cobrar documentos pendentes', subtitle: 'Enviar mensagem com contexto completo'},
                            {title: 'Acionar responsáveis internos', subtitle: 'Delegar pendências que dependem da equipe'},
                            {title: 'Resolver obrigações vencidas', subtitle: 'Regularizar o que gera multa primeiro'},
                            {title: 'Confirmar conclusão com o cliente', subtitle: 'Registrar evidência e arquivar'}
                        ];
                        const type = this.modalType().toLowerCase();
                        if (type.includes('document')) return [
                            {title: 'Solicitar documento ao cliente', subtitle: this.selectedPriority.descricao || 'Documento necessário'},
                            {title: 'Aguardar envio do cliente', subtitle: 'Monitorar retorno'},
                            {title: 'Validar documento recebido', subtitle: 'Conferir autenticidade e validade'},
                            {title: 'Anexar documento ao item', subtitle: 'Registrar evidência'},
                            {title: 'Concluir pendência documental', subtitle: 'Liberar fluxo operacional'}
                        ];
                        if (type.includes('aprovação')) return [
                            {title: 'Acionar aprovador', subtitle: this.modalClientName()},
                            {title: 'Enviar contexto da aprovação', subtitle: this.selectedPriority.descricao || 'Aprovação pendente'},
                            {title: 'Acompanhar retorno', subtitle: 'Evitar paralisação do fluxo'},
                            {title: 'Registrar decisão', subtitle: 'Aprovar ou devolver com motivo'},
                            {title: 'Liberar próxima etapa', subtitle: 'Encaminhar para conclusão'}
                        ];
                        return [
                            {title: 'Conferir dados da obrigação', subtitle: this.selectedPriority.descricao || 'Item operacional'},
                            {title: 'Coletar documentos necessários', subtitle: 'Garantir que não há bloqueios'},
                            {title: 'Preparar execução', subtitle: 'Validar informações e responsável'},
                            {title: 'Transmitir ou concluir atividade', subtitle: 'Executar tarefa principal'},
                            {title: 'Confirmar processamento', subtitle: 'Registrar evidência'},
                            {title: 'Notificar cliente', subtitle: 'Informar conclusão e arquivar'}
                        ];
                    },
                    documentsList() {
                        const type = this.modalType().toLowerCase();
                        const primary = type.includes('document') ? (this.selectedPriority.descricao || 'Documento pendente') : 'Documento principal do item';
                        return [
                            {name: primary, status: type.includes('document') ? 'Pendente' : 'Validar', statusClass: type.includes('document') ? 'is-pending' : 'is-validating'},
                            {name: 'Comprovante ou evidência', status: 'Validar', statusClass: 'is-validating'},
                            {name: 'Registro de conclusão', status: 'Pendente', statusClass: 'is-pending'},
                        ];
                    },
                    pendingList() {
                        if (this.resolutionType === 'client') {
                            const related = this.relatedClientItems();
                            if (related.length) return related.slice(0, 3).map((item) => ({name: item.descricao || item.tipo || 'Item relacionado', status: item.prioridade || item.atraso || 'Aberto', statusClass: (item.badge || '') === 'danger' ? 'is-critical' : 'is-pending'}));
                        }
                        return [
                            {name: this.nextActionTitle(), status: this.severityLabel(), statusClass: this.isCritical() ? 'is-critical' : 'is-pending'},
                            {name: 'Registrar andamento no item', status: 'Pendente', statusClass: 'is-pending'},
                        ];
                    },
                    activitiesList() {
                        return [
                            {time: 'Agora', text: 'Item aberto na Central do Dia'},
                            {time: this.selectedPriority.vencimento || this.selectedClient.ultima_atividade || '-', text: this.riskDescription()},
                            {time: '-', text: 'Aguardando atualização operacional'}
                        ];
                    },
                    minimumPenaltyText() { return 'Conforme regra do item'; },
                    estimatedPenaltyText() { return this.isCritical() ? 'Verificar no cadastro' : 'Não identificado'; },
                    dailyInterestText() { return 'Quando aplicável'; },
                    totalFinancialRiskText() { return this.isCritical() ? 'Alto impacto potencial' : 'Em análise'; },
                    copy(text, feedback) {
                        if (navigator.clipboard) navigator.clipboard.writeText(text);
                        this.copied = feedback || 'Conteúdo copiado.';
                        setTimeout(() => this.copied = '', 2400);
                    },
                    baseMessage() {
                        if (this.resolutionType === 'client') return `Cliente: ${this.modalClientName()}\nRisco: ${this.selectedClient.risco || '-'}\nProblemas: ${this.selectedClient.problemas || '-'}\nAção recomendada: ${this.nextActionTitle()}.`;
                        return `Item: ${this.selectedPriority.descricao || '-'}\nCliente: ${this.modalClientName()}\nTipo: ${this.selectedPriority.tipo || '-'}\nPrazo/Situação: ${this.selectedPriority.vencimento || '-'} · ${this.selectedPriority.atraso || '-'}\nAção recomendada: ${this.nextActionTitle()}.`;
                    },
                    resolutionMessage() { return this.baseMessage(); },
                    copyResolutionText() { this.copy(this.baseMessage(), 'Contexto copiado.'); },
                    copyRecommendedAction() { this.copy(`${this.nextActionTitle()}\n\n${this.nextActionDescription()}\n\n${this.baseMessage()}`, 'Ação recomendada copiada.'); },
                    copyWhatsAppMessage() { this.copy(`Olá! Precisamos resolver uma pendência para evitar atraso.\n\n${this.baseMessage()}\n\nPode nos retornar ainda hoje, por favor?`, 'Mensagem para WhatsApp copiada.'); },
                    copyEmailMessage() { this.copy(`Assunto: Pendência para regularização - ${this.modalClientName()}\n\nOlá,\n\nIdentificamos uma pendência que precisa de atenção.\n\n${this.baseMessage()}\n\nFicamos no aguardo para concluir o processo.`, 'E-mail copiado.'); },
                    copyDocumentRequest(doc) { this.copy(`Solicitação de documento\n\nCliente: ${this.modalClientName()}\nDocumento: ${doc || this.selectedPriority.descricao || 'Documento necessário'}\nMotivo: ${this.nextActionDescription()}\nPrazo recomendado: ${this.deadlineRecommendation()}.`, 'Solicitação de documento copiada.'); },
                    copyReceivedMessage() { this.copy(`Documento recebido/validado para ${this.modalClientName()}.\n\n${this.baseMessage()}\n\nPróximo passo: seguir checklist de resolução.`, 'Mensagem de recebimento copiada.'); },
                    copyDelegationMessage() { this.copy(`Delegação de tarefa\n\n${this.baseMessage()}\n\nResponsável sugerido: ${this.responsibleName()}\nPrazo recomendado: ${this.deadlineRecommendation()}.`, 'Delegação copiada.'); },
                    copyRescheduleMessage() { this.copy(`Reagendamento necessário\n\n${this.baseMessage()}\n\nJustificativa: informar motivo e nova data no cadastro do item.`, 'Resumo para reagendamento copiado.'); },
                    copyCompletionMessage() { this.copy(`Preparar conclusão\n\n${this.baseMessage()}\n\nChecklist: validar evidências, registrar andamento e concluir no cadastro do item.`, 'Conclusão preparada.'); },
                    copyChecklistDoneMessage() { this.copy(`Etapa concluída\n\n${this.nextActionTitle()}\nCliente: ${this.modalClientName()}\nItem: ${this.modalTitle()}`, 'Etapa copiada como concluída.'); },
                    copyPendingSummary(name) { this.copy(`Pendência: ${name || this.nextActionTitle()}\n\n${this.baseMessage()}\n\nPriorizar resolução ainda hoje.`, 'Pendência copiada.'); },
                    copyActivitySummary() { this.copy(`Histórico/atividade\n\n${this.activitiesList().map((a) => `${a.time}: ${a.text}`).join('\n')}`, 'Histórico copiado.'); },
                    copySupportMessage() { this.copy(`Solicitação de suporte\n\n${this.baseMessage()}\n\nImpacto: ${this.financialRiskLevel()}\nPrazo recomendado: ${this.deadlineRecommendation()}\nAjuda necessária: orientar resolução do bloqueio.`, 'Mensagem de suporte copiada.'); },
                }
            }
        </script>

    </div>
</x-filament-panels::page>
