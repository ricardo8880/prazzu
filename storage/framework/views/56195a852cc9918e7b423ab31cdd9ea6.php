<?php if (isset($component)) { $__componentOriginal166a02a7c5ef5a9331faf66fa665c256 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal166a02a7c5ef5a9331faf66fa665c256 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-panels::components.page.index','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament-panels::page'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

    <link rel="stylesheet" href="<?php echo e(asset('css/home-operacional.css')); ?>?v=<?php echo e(file_exists(public_path('css/home-operacional.css')) ? filemtime(public_path('css/home-operacional.css')) : time()); ?>">

    <?php
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
        $centralDiaNotificacoes = (int) ($dashboard['notificacoes_total'] ?? count($notificacoes ?? []));

        $centralDiaCards = [
            ['label' => 'Obrigações vencidas', 'value' => $obrigacoesVencidas['value'] ?? 0, 'hint' => 'Risco de multa', 'tone' => 'danger', 'icon' => 'calendar-alert', 'filter' => 'obrigacoes_vencidas', 'target' => 'priority'],
            ['label' => 'Vencem hoje', 'value' => $vencimentosSemana['value'] ?? 0, 'hint' => 'Vencimento hoje', 'tone' => 'orange', 'icon' => 'calendar-check', 'filter' => 'vencem_hoje', 'target' => 'priority'],
            ['label' => 'Clientes sem enviar documentos', 'value' => count($documentosVencidos) + count($documentosVencendo), 'hint' => 'Podem gerar atraso', 'tone' => 'amber', 'icon' => 'users-alert', 'filter' => 'sem_documentos', 'target' => 'priority'],
            ['label' => 'Pendências paradas há muitos dias', 'value' => count($minhasPendencias), 'hint' => 'Acima de 5 dias', 'tone' => 'purple', 'icon' => 'clock', 'filter' => 'pendencias_paradas', 'target' => 'priority'],
            ['label' => 'Aprovações travadas', 'value' => $aprovacoesPendentes['value'] ?? count($aprovacoesAguardando), 'hint' => 'Aguardando ação', 'tone' => 'blue', 'icon' => 'approval', 'filter' => 'aprovacoes_travadas', 'target' => 'priority'],
            ['label' => 'Tarefas sem responsável', 'value' => collect($minhasPendencias)->filter(fn ($item) => empty($item['responsavel']) || $item['responsavel'] === 'Sem responsável')->count(), 'hint' => 'Risco de não execução', 'tone' => 'teal', 'icon' => 'user-search', 'filter' => 'sem_responsavel', 'target' => 'priority'],
            ['label' => 'Clientes em risco', 'value' => $clientesEmRisco, 'hint' => 'Alto risco de atraso', 'tone' => 'danger', 'icon' => 'shield-alert', 'filter' => 'clientes_risco', 'target' => 'clients'],
        ];

        $filaPrioridade = collect();

        foreach ($itensAtrasados as $item) {
            $filaPrioridade->push(['peso' => 10, 'prioridade' => 'Crítico', 'badge' => 'danger', 'tipo' => 'Obrigação vencida', 'descricao' => $item['titulo'] ?? 'Item atrasado', 'cliente' => $item['empresa'] ?? 'Sem empresa', 'vencimento' => $item['data'] ?? ($item['tempo'] ?? '-'), 'atraso' => $item['tempo'] ?? 'Atrasado', 'url' => $item['url'] ?? ($urls['prazos'] ?? '#'), 'responsavel' => $item['responsavel'] ?? 'Sem responsável atribuído', 'area' => $item['area'] ?? 'Fiscal', 'filtro' => 'obrigacoes_vencidas']);
        }

        foreach ($vencimentosProximos as $item) {
            $filaPrioridade->push(['peso' => 8, 'prioridade' => 'Alto', 'badge' => 'warning', 'tipo' => 'Vence hoje', 'descricao' => $item['titulo'] ?? 'Vencimento próximo', 'cliente' => $item['empresa'] ?? 'Sem empresa', 'vencimento' => $item['data'] ?? '-', 'atraso' => $item['tempo'] ?? 'Hoje', 'url' => $item['url'] ?? ($urls['prazos'] ?? '#'), 'responsavel' => $item['responsavel'] ?? 'Sem responsável atribuído', 'area' => $item['area'] ?? 'Fiscal', 'filtro' => 'vencem_hoje']);
        }

        foreach ($documentosVencidos as $item) {
            $filaPrioridade->push(['peso' => 7, 'prioridade' => 'Alto', 'badge' => 'warning', 'tipo' => 'Sem documentos', 'descricao' => $item['titulo'] ?? 'Documento pendente', 'cliente' => $item['empresa'] ?? 'Sem empresa', 'vencimento' => $item['data'] ?? '-', 'atraso' => $item['tempo'] ?? 'Pendente', 'url' => $item['url'] ?? ($urls['documentos'] ?? '#'), 'responsavel' => $item['responsavel'] ?? 'Sem responsável atribuído', 'area' => $item['area'] ?? 'Documentos', 'filtro' => 'sem_documentos']);
        }

        foreach ($documentosVencendo as $item) {
            $filaPrioridade->push(['peso' => 6, 'prioridade' => 'Médio', 'badge' => 'warning', 'tipo' => 'Sem documentos', 'descricao' => $item['titulo'] ?? 'Documento pendente', 'cliente' => $item['empresa'] ?? 'Sem empresa', 'vencimento' => $item['data'] ?? '-', 'atraso' => $item['tempo'] ?? 'Pendente', 'url' => $item['url'] ?? ($urls['documentos'] ?? '#'), 'responsavel' => $item['responsavel'] ?? 'Sem responsável atribuído', 'area' => $item['area'] ?? 'Documentos', 'filtro' => 'sem_documentos']);
        }

        foreach ($minhasPendencias as $item) {
            $filaPrioridade->push(['peso' => 5, 'prioridade' => ($item['badge'] ?? '') === 'danger' ? 'Crítico' : 'Médio', 'badge' => $item['badge'] ?? 'info', 'tipo' => 'Pendência parada', 'descricao' => $item['titulo'] ?? 'Pendência operacional', 'cliente' => $item['empresa'] ?? 'Sem empresa', 'vencimento' => $item['data'] ?? '-', 'atraso' => $item['status'] ?? ($item['responsavel'] ?? '-'), 'url' => $item['url'] ?? ($urls['minhasPendencias'] ?? '#'), 'responsavel' => $item['responsavel'] ?? 'Responsável não definido', 'area' => $item['area'] ?? 'Operacional', 'filtro' => 'pendencias_paradas']);
        }

        foreach ($aprovacoesAguardando as $aprovacao) {
            $filaPrioridade->push(['peso' => 4, 'prioridade' => 'Médio', 'badge' => 'info', 'tipo' => 'Aprovação travada', 'descricao' => $aprovacao['titulo'] ?? 'Aprovação aguardando', 'cliente' => $aprovacao['empresa'] ?? 'Sem empresa', 'vencimento' => '-', 'atraso' => $aprovacao['tempo'] ?? 'Aguardando', 'url' => $aprovacao['url'] ?? ($urls['centralAprovacoes'] ?? '#'), 'responsavel' => $aprovacao['responsavel'] ?? 'Sem responsável atribuído', 'area' => $aprovacao['area'] ?? 'Aprovações', 'filtro' => 'aprovacoes_travadas']);
        }

        $filaPrioridadeTotal = $filaPrioridade->count();
        $filaPrioridadeCompleta = $filaPrioridade->sortByDesc('peso')->values();
        $filaPrioridade = $filaPrioridadeCompleta->take(7)->values();

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

            return ['score' => $score, 'empresa' => $empresa['empresa'] ?? 'Sem empresa', 'risco' => $riscoLabel, 'tone' => $riscoTone, 'problemas' => implode(', ', $problemas), 'ultima_atividade' => $empresa['ultima_atividade'] ?? ($empresa['atualizado_em'] ?? ($empresa['data'] ?? '-')), 'url' => $empresa['url'] ?? ($urls['tarefas'] ?? '#'), 'atrasados' => $atrasados, 'vencendo' => $vencendo, 'total' => $total, 'responsavel' => $empresa['responsavel'] ?? 'Sem responsável atribuído'];
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
    ?>

    <div data-home-layout="operational" class="pz-central-page" x-data="pzHomeResolutionModal()" x-on:keydown.escape.window="closeResolutionModal()" x-on:keydown.ctrl.enter.window.prevent="resolutionOpen && confirmCompletion()">
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
                <a href="<?php echo e($urls['centralNotificacoes'] ?? '#'); ?>" class="pz-central-bell" aria-label="Ver notificações">
                    <i class="bi bi-bell-fill" aria-hidden="true"></i>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($centralDiaNotificacoes > 0): ?><b><?php echo e($centralDiaNotificacoes > 99 ? '99+' : $centralDiaNotificacoes); ?></b><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </a>
                <div class="pz-central-user">
                    <div class="pz-central-avatar"><?php echo e(strtoupper(mb_substr($usuario ?? 'U', 0, 1))); ?></div>
                    <div><strong><?php echo e($usuario); ?></strong><small>Administrador</small></div>
                </div>
            </div>
        </section>

        <section class="pz-central-date-row">
            <div></div>
            <div class="pz-central-date-actions">
                <div class="pz-central-date-card"><span aria-hidden="true">▣</span><div><strong><?php echo e($centralDiaData); ?></strong><small><?php echo e(ucfirst($centralDiaSemana)); ?></small></div></div>
                <a href="<?php echo e(request()->fullUrl()); ?>" class="pz-refresh-btn">↻ Atualizar</a>
            </div>
        </section>

        <section class="pz-central-day-grid" aria-label="Resumo crítico da Central do Dia">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $centralDiaCards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <button type="button" class="pz-central-day-card pz-tone-<?php echo e($card['tone'] ?? 'slate'); ?>" :class="isActiveSummaryFilter('<?php echo e($card['filter'] ?? ''); ?>', '<?php echo e($card['target'] ?? 'priority'); ?>') ? 'is-filter-active' : ''" @click="applySummaryFilter('<?php echo e($card['filter'] ?? ''); ?>', '<?php echo e($card['target'] ?? 'priority'); ?>', '<?php echo e($card['label'] ?? 'Filtro'); ?>')">
                    <span class="pz-central-day-icon" aria-hidden="true"><i class="pz-icon-<?php echo e($card['icon'] ?? 'dot'); ?>"></i></span>
                    <span class="pz-card-label"><?php echo e($card['label'] ?? '-'); ?></span>
                    <strong><?php echo e($card['value'] ?? 0); ?></strong>
                    <small><?php echo e($card['hint'] ?? 'Acompanhar'); ?></small>
                    <em x-text="isActiveSummaryFilter('<?php echo e($card['filter'] ?? ''); ?>', '<?php echo e($card['target'] ?? 'priority'); ?>') ? 'Filtro aplicado' : 'Filtrar lista →'"></em>
                </button>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </section>

        <section class="pz-content-grid">
            <main class="pz-main-column">
                <section class="pz-panel pz-priority-panel">
                    <div class="pz-section-head">
                        <div>
                            <h2>Fila de Prioridade</h2>
                            <p x-text="priorityFilterLabel ? 'Filtro ativo: ' + priorityFilterLabel : 'O que atacar primeiro hoje (ordenado por criticidade)'"></p>
                        </div>
                        <div class="pz-section-actions">
                            <button type="button" class="pz-clear-filter-btn" x-show="priorityFilter" x-cloak @click="clearSummaryFilter()">Limpar filtro</button>
                            <a href="<?php echo e($urls['minhasPendencias'] ?? '#'); ?>">Ver todas (<?php echo e($filaPrioridadeTotal); ?>)</a>
                        </div>
                    </div>
                    <div class="pz-table-wrap">
                        <table class="pz-priority-table">
                            <thead><tr><th>Prioridade</th><th>Tipo</th><th>Descrição</th><th>Cliente</th><th>Vencimento / Data</th><th>Dias em atraso</th></tr></thead>
                            <tbody>
                                <template x-for="item in visiblePriorityItems()" :key="(item.tipo || '') + '-' + (item.descricao || '') + '-' + (item.cliente || '')">
                                    <tr class="pz-clickable-row" @click="openPriority(item)">
                                        <td><span class="pz-priority-badge" :class="'pz-priority-' + (item.badge || 'info')" x-text="item.prioridade || 'Médio'"></span></td>
                                        <td x-text="item.tipo || '-'"></td>
                                        <td><strong x-text="item.descricao || '-'"></strong></td>
                                        <td x-text="item.cliente || 'Sem empresa'"></td>
                                        <td><strong x-text="item.vencimento || '-'"></strong></td>
                                        <td class="pz-delay-cell" x-text="item.atraso || '-'"></td>
                                    </tr>
                                </template>
                                <tr x-show="visiblePriorityItems().length === 0" x-cloak><td colspan="6" class="pz-empty-cell" x-text="priorityFilter ? 'Nenhum item encontrado para este filtro.' : 'Nenhuma prioridade crítica encontrada para hoje.'"></td></tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="pz-panel pz-risk-clients-panel">
                    <div class="pz-section-head">
                        <div><h2>Clientes em Maior Risco</h2><p x-text="clientFilterLabel ? 'Filtro ativo: ' + clientFilterLabel : 'Risco calculado com base em atrasos, pendências e documentos'"></p></div>
                        <div class="pz-section-actions">
                            <button type="button" class="pz-clear-filter-btn" x-show="clientFilter" x-cloak @click="clearSummaryFilter()">Limpar filtro</button>
                            <a href="<?php echo e($urls['tarefas'] ?? '#'); ?>">Ver todos</a>
                        </div>
                    </div>
                    <div class="pz-table-wrap">
                        <table class="pz-risk-clients-table">
                            <thead><tr><th>Cliente</th><th>Risco</th><th>Principais problemas</th><th>Última atividade</th></tr></thead>
                            <tbody>
                                <template x-for="cliente in visibleClientItems()" :key="cliente.empresa || cliente.url || Math.random()">
                                    <tr class="pz-clickable-row" @click="openClient(cliente)">
                                        <td><strong x-text="cliente.empresa || 'Sem empresa'"></strong></td>
                                        <td><span class="pz-risk-badge" :class="'pz-risk-' + (cliente.tone || 'success')" x-text="cliente.risco || 'Baixo'"></span></td>
                                        <td x-text="cliente.problemas || '-'"></td>
                                        <td x-text="cliente.ultima_atividade || '-'"></td>
                                    </tr>
                                </template>
                                <tr x-show="visibleClientItems().length === 0" x-cloak><td colspan="4" class="pz-empty-cell">Nenhum cliente em risco encontrado.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </section>
            </main>

            <aside class="pz-side-column">
                <section class="pz-panel pz-calendar-panel">
                    <div class="pz-section-head"><div><h2>Calendário de Hoje</h2></div><a href="<?php echo e($urls['prazos'] ?? '#'); ?>">Ver calendário</a></div>
                    <div class="pz-timeline">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $calendarioHoje; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $evento): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <a href="<?php echo e($evento['url'] ?? '#'); ?>" class="pz-timeline-event pz-event-<?php echo e($evento['tone'] ?? 'slate'); ?>">
                                <span class="pz-event-time"><?php echo e($evento['hora'] ?? '--:--'); ?></span><span class="pz-event-line"></span>
                                <span><strong><?php echo e($evento['titulo'] ?? '-'); ?></strong><small><?php echo e($evento['descricao'] ?? 'Operação'); ?></small></span>
                            </a>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <div class="pz-empty-side">Nenhum evento crítico para hoje.</div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <a href="<?php echo e($urls['prazos'] ?? '#'); ?>" class="pz-side-link">Ver todas as obrigações do dia →</a>
                </section>

                <section class="pz-panel pz-summary-panel">
                    <div class="pz-section-head"><div><h2>Resumo do Dia</h2></div></div>
                    <div class="pz-summary-list">
                        <div><span>Total de clientes ativos</span><strong><?php echo e($totalClientesAtivos); ?></strong></div>
                        <div><span>Obrigações este mês</span><strong class="is-green"><?php echo e($totalObrigacoesMes); ?></strong></div>
                        <div><span>Concluídas</span><strong class="is-green"><?php echo e($percentualConcluido); ?>%</strong></div>
                        <div class="pz-progress-track"><span style="width: <?php echo e($percentualConcluido); ?>%"></span></div>
                        <div><span>Em risco de atraso</span><strong class="is-red"><?php echo e($emRiscoAtraso); ?></strong></div>
                    </div>
                    <a href="<?php echo e($urls['relatorios'] ?? ($urls['tarefas'] ?? '#')); ?>" class="pz-side-link">Ver relatório completo →</a>
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
                        <button type="button" class="pz-resolution-primary-btn" @click="confirmCompletion()">✓ Marcar como concluída</button>
                        <button type="button" class="pz-resolution-menu-btn" @click="toggleQuickMenu()">⋮</button>
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
                            <button type="button" class="pz-resolution-primary-btn" @click="startResolution()">⚡ Resolver agora</button>
                        </div>

                        <div class="pz-resolution-quick-card">
                            <h3>Ações rápidas</h3>
                            <div class="pz-resolution-quick-grid">
                                <button type="button" @click="openWhatsAppMessage()"><span>☘</span><b>Cobrar cliente</b><small>WhatsApp</small></button>
                                <button type="button" @click="openEmailMessage()"><span>✉</span><b>Enviar e-mail</b><small>Cobrança</small></button>
                                <button type="button" @click="requestDocument()"><span>▤</span><b>Solicitar documento</b><small>Do cliente</small></button>
                                <button type="button" @click="markReceived()"><span>✓</span><b>Marcar como recebido</b><small>Atualizar checklist</small></button>
                                <button type="button" @click="openActionPanel('delegate')"><span>♙</span><b>Delegar tarefa</b><small>Outro responsável</small></button>
                                <button type="button" @click="openActionPanel('reschedule')"><span>◷</span><b>Reagendar prazo</b><small>Nova data</small></button>
                                <button type="button" @click="confirmCompletion()"><span>✓</span><b>Concluir obrigação</b><small>Finalizar aqui</small></button>
                                <button type="button" @click="openActionPanel('support')"><span>☊</span><b>Abrir chamado</b><small>Suporte</small></button>
                            </div>
                            <div class="pz-resolution-action-panel" x-show="actionPanel" x-cloak>
                                <div class="pz-resolution-action-panel-head">
                                    <strong x-text="actionPanelTitle()"></strong>
                                    <button type="button" @click="actionPanel = ''">×</button>
                                </div>

                                <template x-if="actionPanel === 'delegate'">
                                    <div class="pz-resolution-mini-form">
                                        <label>Responsável<input type="text" x-model="delegateTo" placeholder="Nome do responsável"></label>
                                        <label>Observação<textarea x-model="actionNote" placeholder="Contexto para delegação"></textarea></label>
                                        <button type="button" @click="confirmDelegation()">Confirmar delegação</button>
                                    </div>
                                </template>

                                <template x-if="actionPanel === 'reschedule'">
                                    <div class="pz-resolution-mini-form">
                                        <label>Nova data<input type="date" x-model="newDeadline"></label>
                                        <label>Motivo<textarea x-model="actionNote" placeholder="Motivo do reagendamento"></textarea></label>
                                        <button type="button" @click="confirmReschedule()">Confirmar reagendamento</button>
                                    </div>
                                </template>

                                <template x-if="actionPanel === 'completion'">
                                    <div class="pz-resolution-mini-form">
                                        <label>Evidência / observação<textarea x-model="completionNote" placeholder="Descreva a evidência de conclusão"></textarea></label>
                                        <button type="button" @click="confirmCompletion()">Marcar como concluída</button>
                                    </div>
                                </template>

                                <template x-if="actionPanel === 'support'">
                                    <div class="pz-resolution-mini-form">
                                        <label>Ajuda necessária<textarea x-model="actionNote" placeholder="Explique o bloqueio para suporte/time interno"></textarea></label>
                                        <button type="button" @click="confirmSupport()">Preparar chamado</button>
                                    </div>
                                </template>
                            </div>
                            <small class="pz-resolution-copy-feedback" x-show="copied" x-text="copied"></small>
                        </div>
                    </section>

                    <section class="pz-resolution-tabs">
                        <button type="button" :class="activeTab === 'checklist' ? 'is-active' : ''" @click="setTab('checklist')">Checklist de resolução</button>
                        <button type="button" :class="activeTab === 'documents' ? 'is-active' : ''" @click="setTab('documents')">Documentos</button>
                        <button type="button" :class="activeTab === 'pending' ? 'is-active' : ''" @click="setTab('pending')">Pendências</button>
                        <button type="button" :class="activeTab === 'history' ? 'is-active' : ''" @click="setTab('history')">Histórico</button>
                        <button type="button" :class="activeTab === 'comments' ? 'is-active' : ''" @click="setTab('comments')">Comentários</button>
                    </section>

                    <section class="pz-resolution-content-grid">
                        <div class="pz-resolution-card pz-resolution-checklist-card" x-show="activeTab === 'checklist'">
                            <h3>Checklist de resolução</h3>
                            <p>Execute os passos e conclua com o mínimo de troca de tela.</p>
                            <div class="pz-resolution-checklist-v2">
                                <template x-for="(step, index) in resolutionChecklist()" :key="step.title">
                                    <label :class="index === 0 ? 'is-current' : ''">
                                        <input type="checkbox" :checked="completedSteps.includes(index)" @change="toggleChecklistStep(index)">
                                        <span x-text="index + 1"></span>
                                        <b x-text="step.title"></b>
                                        <small x-text="step.subtitle"></small>
                                        <em x-text="completedSteps.includes(index) ? 'Concluído' : (index === currentChecklistIndex() ? 'Agora' : 'Pendente')"></em>
                                    </label>
                                </template>
                            </div>
                            <button type="button" class="pz-resolution-outline-full" @click="markCurrentStepDone()">✓ Marcar etapa como concluída</button>
                        </div>

                        <div class="pz-resolution-card pz-resolution-finance-card" x-show="activeTab === 'checklist' || activeTab === 'pending'">
                            <h3>Informações financeiras</h3>
                            <div class="pz-resolution-money-list">
                                <div><span>Multa mínima</span><b x-text="minimumPenaltyText()"></b></div>
                                <div><span>Multa estimada</span><b class="is-red" x-text="estimatedPenaltyText()"></b></div>
                                <div><span>Juros diários</span><b x-text="dailyInterestText()"></b></div>
                                <div><span>Risco financeiro total</span><b class="is-red" x-text="totalFinancialRiskText()"></b></div>
                            </div>
                            <div class="pz-resolution-warning-box">⚠ Quanto antes resolver, menor o prejuízo e o retrabalho.</div>
                        </div>

                        <div class="pz-resolution-card pz-resolution-client-card" x-show="activeTab === 'checklist' || activeTab === 'documents' || activeTab === 'pending'">
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

                        <div class="pz-resolution-card pz-resolution-owner-card" x-show="activeTab === 'checklist' || activeTab === 'comments'">
                            <h3>Responsável</h3>
                            <div class="pz-resolution-owner-main">
                                <span x-text="responsibleInitials()"></span>
                                <div><strong x-text="responsibleName()"></strong><small x-text="areaName()"></small></div>
                            </div>
                            <div class="pz-resolution-contact-row">
                                <button type="button" @click="openWhatsAppMessage()" title="Cobrar por WhatsApp">☘</button>
                                <button type="button" @click="openEmailMessage()" title="Enviar e-mail">✉</button>
                                <button type="button" @click="copyResolutionText()" title="Copiar contexto">☷</button>
                                <button type="button" @click="openActionPanel('delegate')" title="Delegar">↗</button>
                            </div>
                            <button type="button" class="pz-resolution-outline-full" @click="openActionPanel('delegate')">Alterar responsável</button>
                        </div>

                        <div class="pz-resolution-card pz-resolution-docs-card" x-show="activeTab === 'documents'">
                            <h3>Documentos necessários</h3>
                            <div class="pz-resolution-list-v2">
                                <template x-for="doc in documentsList()" :key="doc.name">
                                    <div><b x-text="doc.name"></b><span :class="doc.statusClass" x-text="doc.status"></span><button type="button" @click="requestDocument(doc.name)">Solicitar</button></div>
                                </template>
                            </div>
                            <a :href="currentUrl()">Ver todos documentos</a>
                        </div>

                        <div class="pz-resolution-card pz-resolution-pending-card" x-show="activeTab === 'pending'">
                            <h3>Pendências relacionadas</h3>
                            <div class="pz-resolution-list-v2">
                                <template x-for="pending in pendingList()" :key="pending.name">
                                    <div><b x-text="pending.name"></b><span :class="pending.statusClass" x-text="pending.status"></span><button type="button" @click="resolvePending(pending.name)">Resolver</button></div>
                                </template>
                            </div>
                            <a :href="currentUrl()">Ver todas pendências</a>
                        </div>

                        <div class="pz-resolution-card pz-resolution-activities-card" x-show="activeTab === 'history'">
                            <h3>Atividades recentes</h3>
                            <div class="pz-resolution-timeline-v2">
                                <template x-for="activity in activitiesList()" :key="activity.text + activity.time">
                                    <div><span></span><b x-text="activity.time"></b><p x-text="activity.text"></p></div>
                                </template>
                            </div>
                            <a :href="currentUrl()">Ver todas atividades</a>
                        </div>

                        <div class="pz-resolution-card pz-resolution-help-card" x-show="activeTab === 'comments'">
                            <h3>Precisa de ajuda?</h3>
                            <p>Use o resumo abaixo para chamar suporte ou encaminhar internamente com contexto completo.</p>
                            <div class="pz-resolution-help-actions">
                                <button type="button" @click="openActionPanel('support')">Abrir chamado →</button>
                                <button type="button" @click="copyResolutionText()">Copiar contexto →</button>
                            </div>
                        </div>
                    </section>
                </div>

                <footer class="pz-resolution-footer">
                    <span>Atalho rápido: <b>Ctrl + Enter</b> marcar como concluída</span>
                    <div>
                        <button type="button" class="pz-resolution-secondary-btn" @click="saveDraft()">Salvar rascunho</button>
                        <button type="button" class="pz-resolution-primary-btn" @click="confirmCompletion()">✓ Marcar como concluída</button>
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
                    activeTab: 'checklist',
                    actionPanel: '',
                    completedSteps: [],
                    completedDocs: [],
                    draftSaved: false,
                    delegateTo: '',
                    newDeadline: '',
                    actionNote: '',
                    completionNote: '',
                    nowTimestamp: Date.now(),
                    countdownTimer: null,
                    priorityItems: <?php echo \Illuminate\Support\Js::from($filaPrioridadeCompleta->values())->toHtml() ?>,
                    clientItems: <?php echo \Illuminate\Support\Js::from($clientesMaiorRisco->values())->toHtml() ?>,
                    priorityFilter: '',
                    priorityFilterLabel: '',
                    clientFilter: '',
                    clientFilterLabel: '',
                    init() {
                        this.nowTimestamp = Date.now();
                        this.countdownTimer = setInterval(() => {
                            this.nowTimestamp = Date.now();
                        }, 1000);
                    },
                    applySummaryFilter(filter, target, label) {
                        if (!filter) return;
                        if (target === 'clients') {
                            this.priorityFilter = '';
                            this.priorityFilterLabel = '';
                            this.clientFilter = filter;
                            this.clientFilterLabel = label || 'Clientes em risco';
                            this.scrollToPanel('.pz-risk-clients-panel');
                            this.flash('Filtro aplicado em Clientes em Maior Risco.');
                            return;
                        }
                        this.clientFilter = '';
                        this.clientFilterLabel = '';
                        this.priorityFilter = filter;
                        this.priorityFilterLabel = label || 'Filtro';
                        this.scrollToPanel('.pz-priority-panel');
                        this.flash('Filtro aplicado na Fila de Prioridade.');
                    },
                    clearSummaryFilter() {
                        this.priorityFilter = '';
                        this.priorityFilterLabel = '';
                        this.clientFilter = '';
                        this.clientFilterLabel = '';
                        this.flash('Filtro removido.');
                    },
                    isActiveSummaryFilter(filter, target) {
                        return target === 'clients' ? this.clientFilter === filter : this.priorityFilter === filter;
                    },
                    scrollToPanel(selector) {
                        this.$nextTick(() => {
                            const element = document.querySelector(selector);
                            if (element) element.scrollIntoView({behavior: 'smooth', block: 'start'});
                        });
                    },
                    visiblePriorityItems() {
                        const items = this.priorityItems || [];
                        if (!this.priorityFilter) return items.slice(0, 7);
                        return items.filter((item) => {
                            if (this.priorityFilter === 'sem_responsavel') {
                                const responsavel = String(item.responsavel || '').toLowerCase().trim();
                                return !responsavel || responsavel.includes('sem responsável') || responsavel.includes('sem responsavel') || responsavel.includes('não definido') || responsavel.includes('nao definido');
                            }
                            return item.filtro === this.priorityFilter;
                        });
                    },
                    visibleClientItems() {
                        const items = this.clientItems || [];
                        if (!this.clientFilter) return items;
                        return items.filter((cliente) => ['danger', 'warning', 'info'].includes(cliente.tone || '') || (cliente.risco || '').toLowerCase() !== 'baixo');
                    },
                    openPriority(item) {
                        this.selectedPriority = item || {};
                        this.selectedClient = {};
                        this.resolutionType = 'priority';
                        this.resetInteractionState();
                        this.nowTimestamp = Date.now();
                        this.resolutionOpen = true;
                    },
                    openClient(client) {
                        this.selectedClient = client || {};
                        this.selectedPriority = {};
                        this.resolutionType = 'client';
                        this.resetInteractionState();
                        this.nowTimestamp = Date.now();
                        this.resolutionOpen = true;
                    },
                    closeResolutionModal() { this.resolutionOpen = false; },
                    resetInteractionState() {
                        this.copied = '';
                        this.activeTab = 'checklist';
                        this.actionPanel = '';
                        this.completedSteps = [];
                        this.completedDocs = [];
                        this.draftSaved = false;
                        this.delegateTo = '';
                        this.newDeadline = '';
                        this.actionNote = '';
                        this.completionNote = '';
                    },
                    setTab(tab) {
                        this.activeTab = tab;
                        this.actionPanel = '';
                        const names = {checklist: 'Checklist aberto.', documents: 'Documentos abertos.', pending: 'Pendências abertas.', history: 'Histórico aberto.', comments: 'Comentários e ajuda abertos.'};
                        this.flash(names[tab] || 'Aba aberta.');
                    },
                    openActionPanel(panel) {
                        this.actionPanel = this.actionPanel === panel ? '' : panel;
                        this.activeTab = panel === 'support' ? 'comments' : 'checklist';
                        this.flash(this.actionPanel ? `${this.actionPanelTitle()} aberto.` : 'Painel fechado.');
                    },
                    toggleQuickMenu() {
                        this.copyResolutionText();
                    },
                    actionPanelTitle() {
                        if (this.actionPanel === 'delegate') return 'Delegar tarefa';
                        if (this.actionPanel === 'reschedule') return 'Reagendar prazo';
                        if (this.actionPanel === 'completion') return 'Marcar como concluída';
                        if (this.actionPanel === 'support') return 'Abrir chamado';
                        return 'Ação rápida';
                    },
                    currentUrl() { return this.resolutionType === 'client' ? (this.selectedClient.url || '#') : (this.selectedPriority.url || '#'); },
                    modalTitle() { return this.resolutionType === 'client' ? (this.selectedClient.empresa || 'Cliente em risco') : (this.selectedPriority.descricao || 'Item prioritário'); },
                    modalClientName() { return this.resolutionType === 'client' ? (this.selectedClient.empresa || 'Cliente') : (this.selectedPriority.cliente || 'Sem empresa'); },
                    modalType() { return this.resolutionType === 'client' ? 'Cliente em risco' : (this.selectedPriority.tipo || 'Item operacional'); },
                    modalId() { return this.resolutionType === 'client' ? this.slugId(this.selectedClient.empresa || 'cliente') : this.slugId(this.selectedPriority.descricao || 'item'); },
                    areaName() {
                        if (this.resolutionType === 'client') return 'Gestão de carteira';
                        if (this.selectedPriority.area) return this.selectedPriority.area;
                        const type = this.modalType().toLowerCase();
                        if (type.includes('document')) return 'Documentos';
                        if (type.includes('aprovação')) return 'Aprovações';
                        if (type.includes('obrigação') || type.includes('vence')) return 'Fiscal';
                        return 'Operacional';
                    },
                    responsibleName() {
                        if (this.resolutionType === 'client') return this.selectedClient.responsavel || 'Sem responsável atribuído';
                        return this.selectedPriority.responsavel || 'Sem responsável atribuído';
                    },
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
                        if (this.resolutionType === 'client') return this.selectedClient.problemas || 'Cliente possui pendências que podem gerar atraso, multa ou retrabalho.';
                        return `${this.selectedPriority.tipo || 'Item'} · prazo: ${this.selectedPriority.vencimento || 'sem data'} · ação: ${this.nextActionTitle()}`;
                    },
                    financialImpactText() { return this.totalFinancialRiskText(); },
                    financialRiskLevel() { return this.isCritical() ? 'Alto' : 'Médio'; },
                    delayProbability() { return this.isCritical() ? 'Alta' : 'Moderada'; },
                    clientImpactLevel() { return this.isCritical() ? 'Alto' : 'Médio'; },
                    slaText() {
                        if (this.resolutionType === 'client') return `${this.clientDelayedCount()} atraso(s) exigem ação hoje`;
                        const days = this.extractNumber(this.selectedPriority.atraso || '0');
                        if (days > 0) return `${days} dia(s) em atraso`;
                        return this.isCritical() ? 'Vence hoje' : 'Dentro do acompanhamento';
                    },
                    parseBrazilianDate(value) {
                        const match = String(value || '').match(/(\d{1,2})\/(\d{1,2})\/(\d{4})/);
                        if (!match) return null;
                        return new Date(parseInt(match[3], 10), parseInt(match[2], 10) - 1, parseInt(match[1], 10), 17, 0, 0, 0);
                    },
                    deadlineGraceDays() {
                        return 7;
                    },
                    currentDueDate() {
                        if (this.resolutionType !== 'priority') return null;
                        return this.parseBrazilianDate(this.selectedPriority.vencimento || '');
                    },
                    isOverduePriority() {
                        const dueDate = this.currentDueDate();
                        if (!dueDate) return false;
                        return dueDate.getTime() < this.nowTimestamp;
                    },
                    deadlineTargetDate() {
                        const now = new Date(this.nowTimestamp);
                        const fallback = new Date(now);
                        fallback.setHours(17, 0, 0, 0);
                        if (fallback.getTime() <= now.getTime()) {
                            fallback.setDate(fallback.getDate() + 1);
                        }

                        const dueDate = this.currentDueDate();
                        if (!dueDate) return fallback;

                        if (dueDate.getTime() < this.nowTimestamp) {
                            const graceLimit = new Date(dueDate);
                            graceLimit.setDate(graceLimit.getDate() + this.deadlineGraceDays());
                            graceLimit.setHours(17, 0, 0, 0);
                            return graceLimit;
                        }

                        return dueDate;
                    },
                    isDeadlineExpired() {
                        return this.deadlineTargetDate().getTime() <= this.nowTimestamp;
                    },
                    remainingTimeText() {
                        let diff = this.deadlineTargetDate().getTime() - this.nowTimestamp;
                        if (diff <= 0) return '';

                        const days = Math.floor(diff / 86400000);
                        diff %= 86400000;
                        const hours = Math.floor(diff / 3600000);

                        if (days > 1) return `${days} dias`;
                        if (days === 1) return hours > 0 ? `1 dia e ${hours}h` : '1 dia';
                        if (hours > 1) return `${hours} horas`;
                        if (hours === 1) return '1 hora';
                        const minutes = Math.max(1, Math.floor(diff / 60000));
                        return `${minutes} min`;
                    },
                    deadlineRecommendation() {
                        if (this.isDeadlineExpired()) return 'Regularizar agora';

                        const remaining = this.remainingTimeText();
                        const target = this.deadlineTargetDate();
                        const now = new Date(this.nowTimestamp);
                        const sameDay = target.toDateString() === now.toDateString();

                        if (this.isOverduePriority()) {
                            return remaining ? `${remaining} para evitar multa` : 'Regularizar antes da multa';
                        }

                        if (sameDay) return 'Regularizar hoje até 17:00';
                        return remaining ? `${remaining} até o vencimento` : target.toLocaleDateString('pt-BR') + ' até 17:00';
                    },
                    deadlineReason() {
                        if (this.isDeadlineExpired()) return 'limite expirado; regularização imediata para reduzir multa, atraso ou retrabalho';

                        const target = this.deadlineTargetDate().toLocaleDateString('pt-BR');
                        if (this.isOverduePriority()) {
                            return `obrigação já venceu; prazo de tolerância até ${target} às 17:00`;
                        }

                        return `contador acompanha o tempo restante até ${target} às 17:00`;
                    },
                    countdownParts() {
                        let diff = this.deadlineTargetDate().getTime() - this.nowTimestamp;
                        if (diff <= 0) diff = 0;
                        const totalHours = Math.floor(diff / 3600000);
                        const h = String(totalHours).padStart(2, '0');
                        diff %= 3600000;
                        const m = String(Math.floor(diff / 60000)).padStart(2, '0');
                        diff %= 60000;
                        const sec = String(Math.floor(diff / 1000)).padStart(2, '0');
                        return {h, m, s: sec};
                    },
                    progressSteps() {
                        return this.resolutionChecklist().slice(0, 5).map((step) => step.title);
                    },
                    activeStepIndex() {
                        return Math.min(this.completedSteps.length, Math.max(0, this.progressSteps().length - 1));
                    },
                    progressPercent() {
                        const total = this.resolutionChecklist().length || 1;
                        const done = this.completedSteps.filter((step, index, list) => step >= 0 && step < total && list.indexOf(step) === index).length;
                        return Math.max(0, Math.min(100, Math.round((done / total) * 100)));
                    },
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
                            {title: 'Atacar primeiro obrigações vencidas', subtitle: this.selectedClient.problemas || 'Verificar atrasos e pendências'},
                            {title: 'Cobrar documentos pendentes', subtitle: 'Enviar mensagem com contexto completo'},
                            {title: 'Distribuir ações para responsáveis', subtitle: 'Cada pendência com dono e prazo'},
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
                            {title: 'Identificar exatamente o que venceu', subtitle: this.selectedPriority.descricao || 'Obrigação crítica'},
                            {title: 'Separar documentos que bloqueiam a entrega', subtitle: 'Solicitar apenas o que falta ao cliente'},
                            {title: 'Executar a regularização', subtitle: 'Responsável: ' + this.responsibleName()},
                            {title: 'Transmitir ou concluir no sistema', subtitle: 'Evitar multa, atraso e retrabalho'},
                            {title: 'Salvar evidência de conclusão', subtitle: 'Protocolo, recibo ou comprovante'},
                            {title: 'Avisar cliente e encerrar risco', subtitle: 'Registrar conclusão na carteira'}
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
                    estimatedPenaltyValue() {
                        const base = this.resolutionType === 'client' ? Math.max(1, this.clientDelayedCount()) * 450 : Math.max(1, this.extractNumber(this.selectedPriority.atraso || '1')) * 40;
                        return this.isCritical() ? Math.max(200, Math.min(5000, base)) : Math.max(80, Math.min(800, base));
                    },
                    money(value) { return new Intl.NumberFormat('pt-BR', {style: 'currency', currency: 'BRL'}).format(value || 0); },
                    minimumPenaltyText() { return this.money(this.isCritical() ? 200 : 80); },
                    estimatedPenaltyText() { return this.money(this.estimatedPenaltyValue()); },
                    dailyInterestText() { return this.isCritical() ? '0,33%' : '0,10%'; },
                    totalFinancialRiskText() { return this.money(Math.round(this.estimatedPenaltyValue() * (this.isCritical() ? 1.03 : 1.01))); },
                    currentChecklistIndex() {
                        const total = this.resolutionChecklist().length;
                        for (let index = 0; index < total; index++) {
                            if (!this.completedSteps.includes(index)) return index;
                        }
                        return Math.max(0, total - 1);
                    },
                    toggleChecklistStep(index) {
                        if (this.completedSteps.includes(index)) {
                            this.completedSteps = this.completedSteps.filter((item) => item !== index);
                            this.flash('Etapa reaberta.');
                            return;
                        }
                        this.completedSteps = [...this.completedSteps, index].sort((a, b) => a - b);
                        this.flash('Etapa marcada como concluída.');
                    },
                    markCurrentStepDone() {
                        this.toggleChecklistStep(this.currentChecklistIndex());
                    },
                    startResolution() {
                        this.activeTab = 'checklist';
                        this.actionPanel = '';
                        if (!this.completedSteps.includes(0)) this.completedSteps = [0];
                        this.copy(`${this.nextActionTitle()}\n\n${this.nextActionDescription()}\n\n${this.baseMessage()}`, 'Resolução iniciada. Próxima ação copiada.');
                    },
                    requestDocument(doc) {
                        this.activeTab = 'documents';
                        const documentName = doc || this.selectedPriority.descricao || 'Documento necessário';
                        this.copy(`Solicitação de documento\n\nCliente: ${this.modalClientName()}\nDocumento: ${documentName}\nMotivo: ${this.nextActionDescription()}\nPrazo recomendado: ${this.deadlineRecommendation()}.`, 'Solicitação de documento copiada.');
                    },
                    markReceived() {
                        this.activeTab = 'documents';
                        if (!this.completedDocs.includes(0)) this.completedDocs = [...this.completedDocs, 0];
                        if (!this.completedSteps.includes(1)) this.completedSteps = [...this.completedSteps, 1].sort((a, b) => a - b);
                        this.copy(`Documento recebido/validado para ${this.modalClientName()}.\n\n${this.baseMessage()}\n\nPróximo passo: seguir checklist de resolução.`, 'Documento marcado visualmente como recebido.');
                    },
                    resolvePending(name) {
                        this.activeTab = 'checklist';
                        this.actionPanel = '';
                        this.markCurrentStepDone();
                        this.flash(`Pendência tratada no checklist: ${name || this.nextActionTitle()}.`);
                    },
                    prepareCompletion() {
                        this.actionPanel = 'completion';
                        this.activeTab = 'checklist';
                        this.copy(`Conclusão da pendência

${this.baseMessage()}

Checklist: validar evidência, marcar etapas e encerrar risco sem sair da Home.`, 'Painel de conclusão aberto.');
                    },
                    confirmCompletion() {
                        const allSteps = this.resolutionChecklist().map((_, index) => index);
                        this.completedSteps = allSteps;
                        this.actionPanel = '';
                        this.copy(`Pendência concluída

${this.baseMessage()}

Evidência/observação: ${this.completionNote || 'Conclusão registrada pelo popup da Home.'}

Status local: concluído.`, 'Pendência marcada como concluída no popup.');
                    },
                    confirmDelegation() {
                        const target = this.delegateTo || this.responsibleName();
                        if (this.resolutionType === 'client') { this.selectedClient.responsavel = target; }
                        else { this.selectedPriority.responsavel = target; }
                        this.actionPanel = '';
                        this.copy(`Delegação de tarefa\n\n${this.baseMessage()}\n\nNovo responsável: ${target}\nObservação: ${this.actionNote || '-'}\nPrazo recomendado: ${this.deadlineRecommendation()}.`, 'Delegação preparada e copiada.');
                    },
                    confirmReschedule() {
                        if (this.newDeadline) {
                            const [year, month, day] = this.newDeadline.split('-');
                            const formatted = `${day}/${month}/${year}`;
                            if (this.resolutionType === 'client') { this.selectedClient.ultima_atividade = 'Prazo reagendado para ' + formatted; }
                            else { this.selectedPriority.vencimento = formatted; this.selectedPriority.atraso = 'Reagendado'; }
                        }
                        this.actionPanel = '';
                        this.copy(`Reagendamento registrado

${this.baseMessage()}

Nova data: ${this.newDeadline || 'Data não informada'}
Motivo: ${this.actionNote || '-'}

Status local: prazo tratado no popup.`, 'Reagendamento registrado no popup e copiado.');
                    },
                    confirmSupport() {
                        this.copy(`Solicitação de suporte\n\n${this.baseMessage()}\n\nImpacto: ${this.financialRiskLevel()}\nPrazo recomendado: ${this.deadlineRecommendation()}\nAjuda necessária: ${this.actionNote || 'Orientar resolução do bloqueio.'}`, 'Chamado preparado e copiado.');
                    },
                    saveDraft() {
                        this.draftSaved = true;
                        this.copy(`Rascunho salvo localmente\n\n${this.baseMessage()}\n\nPróxima ação: ${this.nextActionTitle()}\nObservação: ${this.actionNote || this.completionNote || '-'}`, 'Rascunho salvo neste popup.');
                    },
                    documentsList() {
                        const type = this.modalType().toLowerCase();
                        const primary = type.includes('document') ? (this.selectedPriority.descricao || 'Documento pendente') : 'Documento que bloqueia a conclusão';
                        const base = [
                            {name: primary, status: type.includes('document') ? 'Pendente' : 'Validar', statusClass: type.includes('document') ? 'is-pending' : 'is-validating'},
                            {name: 'Comprovante de transmissão ou recibo', status: 'Validar', statusClass: 'is-validating'},
                            {name: 'Evidência para arquivamento', status: 'Pendente', statusClass: 'is-pending'},
                        ];
                        return base.map((doc, index) => this.completedDocs.includes(index) ? {...doc, status: 'Recebido', statusClass: 'is-done'} : doc);
                    },
                    openUrl(url) {
                        if (!url || url === '#') {
                            this.flash('Link não disponível para este item.');
                            return;
                        }
                        window.open(url, '_blank', 'noopener');
                    },
                    whatsappText() {
                        return `Olá! Precisamos resolver uma pendência para evitar atraso.\n\n${this.baseMessage()}\n\nPode nos retornar ainda hoje, por favor?`;
                    },
                    emailSubject() { return `Pendência para regularização - ${this.modalClientName()}`; },
                    emailBody() {
                        return `Olá,\n\nIdentificamos uma pendência que precisa de atenção.\n\n${this.baseMessage()}\n\nFicamos no aguardo para concluir o processo.`;
                    },
                    openWhatsAppMessage() {
                        const text = this.whatsappText();
                        this.copy(text, 'WhatsApp aberto e mensagem copiada.');
                        window.open(`https://wa.me/?text=${encodeURIComponent(text)}`, '_blank', 'noopener');
                    },
                    openEmailMessage() {
                        const subject = this.emailSubject();
                        const body = this.emailBody();
                        this.copy(`Assunto: ${subject}\n\n${body}`, 'E-mail aberto e conteúdo copiado.');
                        window.location.href = `mailto:?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
                    },
                    copy(text, feedback) {
                        if (navigator.clipboard && window.isSecureContext) {
                            navigator.clipboard.writeText(text).catch(() => {});
                        }
                        this.flash(feedback || 'Conteúdo copiado.');
                    },
                    flash(feedback) {
                        this.copied = feedback || 'Ação executada.';
                        setTimeout(() => this.copied = '', 2600);
                    },
                    baseMessage() {
                        if (this.resolutionType === 'client') return `Cliente: ${this.modalClientName()}\nRisco: ${this.selectedClient.risco || '-'}\nProblemas: ${this.selectedClient.problemas || '-'}\nAção recomendada: ${this.nextActionTitle()}.`;
                        return `Item: ${this.selectedPriority.descricao || '-'}\nCliente: ${this.modalClientName()}\nTipo: ${this.selectedPriority.tipo || '-'}\nPrazo/Situação: ${this.selectedPriority.vencimento || '-'} · ${this.selectedPriority.atraso || '-'}\nAção recomendada: ${this.nextActionTitle()}.`;
                    },
                    resolutionMessage() { return this.baseMessage(); },
                    copyResolutionText() { this.copy(this.baseMessage(), 'Contexto copiado.'); },
                    copyRecommendedAction() { this.startResolution(); },
                    copyWhatsAppMessage() { this.openWhatsAppMessage(); },
                    copyEmailMessage() { this.openEmailMessage(); },
                    copyDocumentRequest(doc) { this.requestDocument(doc); },
                    copyReceivedMessage() { this.markReceived(); },
                    copyDelegationMessage() { this.openActionPanel('delegate'); },
                    copyRescheduleMessage() { this.openActionPanel('reschedule'); },
                    copyCompletionMessage() { this.prepareCompletion(); },
                    copyChecklistDoneMessage() { this.markCurrentStepDone(); },
                    copyPendingSummary(name) { this.copy(`Pendência: ${name || this.nextActionTitle()}\n\n${this.baseMessage()}\n\nPriorizar resolução ainda hoje.`, 'Pendência copiada.'); },
                    copyActivitySummary() { this.setTab('history'); this.copy(`Histórico/atividade\n\n${this.activitiesList().map((a) => `${a.time}: ${a.text}`).join('\n')}`, 'Histórico copiado.'); },
                    copySupportMessage() { this.openActionPanel('support'); },                }
            }
        </script>

    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal166a02a7c5ef5a9331faf66fa665c256)): ?>
<?php $attributes = $__attributesOriginal166a02a7c5ef5a9331faf66fa665c256; ?>
<?php unset($__attributesOriginal166a02a7c5ef5a9331faf66fa665c256); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal166a02a7c5ef5a9331faf66fa665c256)): ?>
<?php $component = $__componentOriginal166a02a7c5ef5a9331faf66fa665c256; ?>
<?php unset($__componentOriginal166a02a7c5ef5a9331faf66fa665c256); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views/filament/pages/home.blade.php ENDPATH**/ ?>