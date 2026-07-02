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

    <link rel="stylesheet" href="<?php echo e(asset('css/contabilidade-ux-lote6.css')); ?>?v=<?php echo e(file_exists(public_path('css/contabilidade-ux-lote6.css')) ? filemtime(public_path('css/contabilidade-ux-lote6.css')) : time()); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/contabilidade-operacao-lote3.css')); ?>?v=<?php echo e(file_exists(public_path('css/contabilidade-operacao-lote3.css')) ? filemtime(public_path('css/contabilidade-operacao-lote3.css')) : time()); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/compliance-module.css')); ?>?v=<?php echo e(file_exists(public_path('css/compliance-module.css')) ? filemtime(public_path('css/compliance-module.css')) : time()); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/prazzu-ux-essentials.css')); ?>?v=<?php echo e(file_exists(public_path('css/prazzu-ux-essentials.css')) ? filemtime(public_path('css/prazzu-ux-essentials.css')) : time()); ?>">

    <?php
        $items = collect($data['items'] ?? []);
        $vencidas = $items->where('is_late', true)->values();
        $criticas = $items->filter(fn ($item) => in_array($item['prioridade'] ?? '', ['urgente', 'alta'], true))->values();
        $aprovacao = $items->where('status', 'em_aprovacao')->values();
        $semResponsavel = $items->filter(fn ($item) => ($item['sem_responsavel'] ?? false) || blank($item['responsavel'] ?? null) || ($item['responsavel'] ?? '') === 'Sem responsável')->values();
        $vencemHoje = $items->where('is_due_today', true)->values();
        $proximosPrazos = $items->where('is_due_soon', true)->values();
        $noControle = $items->where('prioridade_operacional_tone', 'ok')->values();
        $filaRecomendada = $items->take(6)->values();
        $workflowLinks = $data['workflowLinks'] ?? [];
        $workflowDecision = $data['workflowDecision'] ?? [];
        $health = $data['health'] ?? [];
        $dashboardStats = $data['dashboardStats'] ?? [];
        $progress = $data['progress'] ?? [];
        $activeCluster = $data['activeCluster'] ?? [];
        $emptyState = $data['emptyState'] ?? [];

        $resumoOperacional = [
            [
                'label' => 'Tratar agora',
                'value' => $vencidas->count(),
                'hint' => 'Pendências vencidas exigem prioridade máxima.',
                'tone' => $vencidas->count() > 0 ? 'danger' : 'ok',
                'anchor' => '#pendencias-vencidas',
            ],
            [
                'label' => 'Alta prioridade',
                'value' => $criticas->count(),
                'hint' => 'Itens urgentes ou de prioridade alta.',
                'tone' => $criticas->count() > 0 ? 'warning' : 'ok',
                'anchor' => '#lista-pendencias',
            ],
            [
                'label' => 'Aguardando decisão',
                'value' => $aprovacao->count(),
                'hint' => 'Itens em aprovação precisam de acompanhamento.',
                'tone' => $aprovacao->count() > 0 ? 'info' : 'ok',
                'anchor' => '#status-pendencias',
            ],
            [
                'label' => 'Sem responsável',
                'value' => $semResponsavel->count(),
                'hint' => 'Itens sem dono tendem a atrasar.',
                'tone' => $semResponsavel->count() > 0 ? 'danger' : 'ok',
                'anchor' => '#lista-pendencias',
            ],
        ];
    ?>

    <div class="compliance-page pendencias-lote1-page pendencias-lote11-page">
        <section class="contabilidade-lote3-scope" aria-label="Propósito da aba Pendências">
            <div class="contabilidade-lote3-scope__top">
                <div>
                    <span class="contabilidade-lote3-eyebrow"><i class="bi bi-list-check"></i> Pendências</span>
                    <h2>Mesa exclusiva para resolver pendências</h2>
                    <p>Esta aba concentra triagem, priorização, conclusão, aprovação e bloqueios das pendências. A Home apenas sinaliza riscos e envia o usuário para cá.</p>
                </div>
                <div class="contabilidade-lote3-actions">
                    <a class="contabilidade-lote3-action primary" href="#lista-pendencias"><i class="bi bi-arrow-down-circle"></i> Ver fila</a>
                    <a class="contabilidade-lote3-action" href="<?php echo e(\App\Filament\Pages\SlaPrazos::getUrl()); ?>"><i class="bi bi-clock-history"></i> Ver SLA</a>
                    <a class="contabilidade-lote3-action" href="<?php echo e(\App\Filament\Pages\CentralAprovacoes::getUrl()); ?>"><i class="bi bi-check2-square"></i> Aprovações</a>
                </div>
            </div>
            <div class="contabilidade-lote3-rules">
                <div class="contabilidade-lote3-rule"><strong><i class="bi bi-bullseye"></i> Propósito</strong><span>Resolver pendências abertas, atrasadas, críticas ou sem responsável.</span></div>
                <div class="contabilidade-lote3-rule"><strong><i class="bi bi-box-arrow-up-right"></i> Vai para outra aba</strong><span>Documentos, aprovações finais e análise histórica ficam nos módulos próprios.</span></div>
                <div class="contabilidade-lote3-rule"><strong><i class="bi bi-link-45deg"></i> Ligação correta</strong><span>Quando a pendência depender de documento, SLA ou aprovação, use o link contextual.</span></div>
            </div>
        </section>

        <div class="pendencias-lote6-livewire-loading" wire:loading.delay.flex wire:target="aplicarFiltroPendencias,limparFiltrosPendencias,abrirPendencia,concluirPendenciaSelecionada,solicitarAprovacaoPendenciaSelecionada,aprovarPendenciaSelecionada,reprovarPendenciaSelecionada,iniciarSlaPendenciaSelecionada,atualizarSlaPendenciaSelecionada,finalizarSlaPendenciaSelecionada,criarPendencia">
            <i class="pz-ux-spinner"></i> Atualizando a central sem sair da tela...
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($lastActionFeedback): ?>
            <div class="pendencias-lote8-feedback <?php echo e($lastActionFeedback['tone'] ?? 'info'); ?>" role="status" aria-live="polite">
                <div>
                    <strong><?php echo e($lastActionFeedback['message']); ?></strong>
                    <span>Atualizado às <?php echo e($lastActionFeedback['time'] ?? now()->format('H:i')); ?>.</span>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <section class="pendencias-lote8-dashboard" aria-label="Resumo inteligente das pendências">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $dashboardStats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <article class="pendencias-lote8-dashboard-card <?php echo e($stat['tone'] ?? 'info'); ?>">
                    <span><?php echo e($stat['label']); ?></span>
                    <strong><?php echo e($stat['value']); ?></strong>
                    <small><?php echo e($stat['hint']); ?></small>
                </article>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </section>

        <section class="pendencias-lote8-progress" aria-label="Progresso operacional da fila filtrada">
            <div>
                <span class="pz-ux-kicker">Progresso da fila atual</span>
                <h2><?php echo e($progress['percentual_controle'] ?? 0); ?>% sob controle</h2>
                <p><?php echo e($progress['mensagem'] ?? 'Acompanhe a evolução da fila conforme resolve os itens prioritários.'); ?></p>
            </div>
            <div class="pendencias-lote8-progress-meter" aria-label="Percentual sob controle">
                <span style="width: <?php echo e($progress['percentual_controle'] ?? 0); ?>%"></span>
            </div>
            <div class="pendencias-lote8-progress-numbers">
                <strong><?php echo e($progress['no_controle'] ?? 0); ?> no controle</strong>
                <span><?php echo e($progress['criticas'] ?? 0); ?> críticas · <?php echo e($progress['total'] ?? 0); ?> no filtro</span>
            </div>
        </section>

        <section class="compliance-hero pendencias-lote1-hero">
            <div>
                <span>COMPLIANCE</span>
                <h1>Pendências</h1>
                <p>Central única para acompanhar pendências por clusters no topo: minhas, todas, atrasadas, aprovações, bloqueios e SLA.</p>
            </div>
            <div class="compliance-hero-actions pendencias-lote1-hero-actions">
                <a href="#pendencias-foco">Ver foco do dia</a>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($workflowLinks['minhas_pendencias'])): ?>
                    <a href="<?php echo e($workflowLinks['minhas_pendencias']); ?>">Minhas Pendências</a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <a href="#nova-pendencia">Criar pendência</a>
            </div>
        </section>

        <section class="pz-ux-toolbar pendencias-lote1-toolbar" aria-label="Atalhos da tela de pendências">
            <div>
                <strong>Fila guiada por prioridade</strong>
                <span>Comece pelas vencidas, acompanhe aprovações e crie novas pendências com responsável, prioridade e prazo.</span>
            </div>
            <div class="pz-ux-actions">
                <a class="pz-ux-action primary" href="#pendencias-foco">Começar pelo foco</a>
                <a class="pz-ux-action subtle" href="#lista-pendencias">Ver lista completa</a>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($workflowLinks['todas_tarefas'])): ?>
                    <a class="pz-ux-action subtle" href="<?php echo e($workflowLinks['todas_tarefas']); ?>">Tabela Filament</a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <a class="pz-ux-action subtle" href="#nova-pendencia">Nova pendência</a>
            </div>
        </section>

        <section class="compliance-card pendencias-lote6-consolidation" aria-label="Resumo consolidado da central de pendências">
            <div class="pendencias-lote6-consolidation-copy">
                <span class="pz-ux-kicker">Central consolidada</span>
                <h2>Use os clusters superiores para separar execução individual, triagem geral e riscos sem abrir outra tela.</h2>
                <p><?php echo e($health['mensagem'] ?? 'Acompanhe a saúde da fila e use os atalhos certos para cada tipo de trabalho.'); ?></p>
            </div>

            <div class="pendencias-lote6-health-grid">
                <div class="pendencias-lote6-health-card danger">
                    <span>Fila crítica</span>
                    <strong><?php echo e($health['percentual_critico'] ?? 0); ?>%</strong>
                    <small>Vermelhas/amarelas no filtro atual</small>
                </div>
                <div class="pendencias-lote6-health-card ok">
                    <span>No controle</span>
                    <strong><?php echo e($health['percentual_saudavel'] ?? 0); ?>%</strong>
                    <small>Sem alerta operacional</small>
                </div>
                <div class="pendencias-lote6-health-card info">
                    <span>SLA ativo</span>
                    <strong><?php echo e($health['sla_ativo'] ?? 0); ?></strong>
                    <small>Itens monitorados por prazo</small>
                </div>
                <div class="pendencias-lote6-health-card warning">
                    <span>Travas</span>
                    <strong><?php echo e(($health['bloqueadas'] ?? 0) + ($health['sem_dono'] ?? 0)); ?></strong>
                    <small>Bloqueadas ou sem responsável</small>
                </div>
            </div>

            <div class="pendencias-lote6-route-guide">
                <div>
                    <strong>Pendências</strong>
                    <span>Visão executiva com clusters no topo para entender risco, prioridade, bloqueio, SLA e criar novas demandas.</span>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($workflowLinks['minhas_pendencias'])): ?>
                    <a href="<?php echo e($workflowLinks['minhas_pendencias']); ?>">Abrir execução diária</a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </section>

        <section class="compliance-card pendencias-lote9-decision" aria-label="Guia de uso entre Pendências e Minhas Pendências">
            <div class="pendencias-lote9-decision-header">
                <div>
                    <span class="pz-ux-kicker">Orientação de uso</span>
                    <h2>Qual tela usar agora?</h2>
                    <p><?php echo e($workflowDecision['decision_message'] ?? 'Use a visão geral para decidir prioridades e a fila diária para executar pendências atribuídas a você.'); ?></p>
                </div>
                <div class="pendencias-lote9-risk-strip">
                    <span><strong><?php echo e($workflowDecision['risk_summary']['criticas'] ?? 0); ?></strong> críticas</span>
                    <span><strong><?php echo e($workflowDecision['risk_summary']['aprovacoes'] ?? 0); ?></strong> aprovações</span>
                    <span><strong><?php echo e($workflowDecision['risk_summary']['bloqueadas'] ?? 0); ?></strong> bloqueadas</span>
                    <span><strong><?php echo e($workflowDecision['risk_summary']['sem_responsavel'] ?? 0); ?></strong> sem dono</span>
                </div>
            </div>

            <div class="pendencias-lote9-decision-grid">
                <?php
                    $currentScope = $workflowDecision['current_scope'] ?? [];
                    $executionScope = $workflowDecision['execution_scope'] ?? [];
                ?>

                <article class="pendencias-lote9-decision-card <?php echo e($currentScope['tone'] ?? 'info'); ?> active">
                    <span>Tela atual</span>
                    <h3><?php echo e($currentScope['label'] ?? 'Painel de Pendências'); ?></h3>
                    <p><?php echo e($currentScope['description'] ?? 'Visão geral para decisão, prioridade e acompanhamento operacional.'); ?></p>
                    <div>
                        <strong><?php echo e($currentScope['count'] ?? 0); ?></strong>
                        <small><?php echo e($currentScope['best_for'] ?? 'Decisão e triagem'); ?></small>
                    </div>
                    <a href="#controle-pendencias">Continuar triagem</a>
                </article>

                <article class="pendencias-lote9-decision-card <?php echo e($executionScope['tone'] ?? 'info'); ?>">
                    <span>Execução diária</span>
                    <h3><?php echo e($executionScope['label'] ?? 'Minhas Pendências'); ?></h3>
                    <p><?php echo e($executionScope['description'] ?? 'Fila individual para executar tarefas atribuídas a você sem perder ritmo.'); ?></p>
                    <div>
                        <strong><?php echo e($executionScope['count'] ?? 0); ?></strong>
                        <small><?php echo e($executionScope['best_for'] ?? 'Execução individual'); ?></small>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($workflowLinks['minhas_pendencias'])): ?>
                        <a href="<?php echo e($workflowLinks['minhas_pendencias']); ?>">Abrir minhas pendências</a>
                    <?php else: ?>
                        <a href="#lista-pendencias">Ver itens filtrados</a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </article>
            </div>
        </section>

        <section class="pendencias-lote1-command" id="pendencias-foco">
            <div class="pendencias-lote1-command-copy">
                <span class="pz-ux-kicker">Foco operacional</span>
                <h2>O que precisa de atenção primeiro?</h2>
                <p>Essa visão evita que todas as pendências pareçam iguais. O usuário abre a tela e já entende onde agir.</p>
            </div>
            <div class="pendencias-lote3-priority-legend" aria-label="Legenda de prioridade operacional">
                <span class="danger">1. Vencidas / sem dono / urgentes</span>
                <span class="warning">2. Vence hoje / alta prioridade</span>
                <span class="info">3. Próximos prazos / aprovação</span>
                <span class="ok">4. No controle</span>
            </div>
            <div class="pendencias-lote1-command-grid">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $resumoOperacional; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <a class="pendencias-lote1-priority-card <?php echo e($card['tone']); ?>" href="<?php echo e($card['anchor']); ?>">
                        <span><?php echo e($card['label']); ?></span>
                        <strong><?php echo e($card['value']); ?></strong>
                        <small><?php echo e($card['hint']); ?></small>
                    </a>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
        </section>

        <section class="compliance-stats pendencias-lote1-stats">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($data['stats'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <article class="compliance-stat">
                    <span><?php echo e($stat['label']); ?></span>
                    <strong><?php echo e($stat['value']); ?></strong>
                    <small><?php echo e($stat['hint']); ?></small>
                </article>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </section>


        <section class="compliance-card pendencias-lote4-filter-card pendencias-lote10-native-cluster-context" id="controle-pendencias">
            <?php
                $activeFilterKey = $data['activeFilter'] ?? 'minhas';
                $activeFilter = ($data['filterOptions'] ?? [])[$activeFilterKey] ?? null;
            ?>

            <div class="pendencias-lote4-filter-header pendencias-lote11-cluster-header">
                <div>
                    <span class="pz-ux-kicker">Cluster ativo</span>
                    <h2><?php echo e($activeCluster['label'] ?? ($activeFilter['label'] ?? 'Minhas Pendências')); ?></h2>
                    <p><?php echo e($activeCluster['description'] ?? 'Use as abas superiores do Filament para alternar o contexto sem sair da central. A lista abaixo respeita o cluster selecionado.'); ?></p>
                </div>
                <div class="pendencias-lote4-filter-result pendencias-lote11-filter-result">
                    <strong><?php echo e($data['totalAfterFilters'] ?? $items->count()); ?></strong>
                    <span>de <?php echo e($data['totalBeforeFilters'] ?? $items->count()); ?> pendências</span>
                </div>
            </div>

            <div class="pendencias-lote10-cluster-summary pendencias-lote11-cluster-summary" aria-label="Resumo do cluster ativo">
                <div class="<?php echo e($activeCluster['tone'] ?? ($activeFilter['tone'] ?? 'info')); ?>">
                    <span><?php echo e($activeCluster['hint'] ?? ($activeFilter['hint'] ?? 'Responsável atual')); ?></span>
                    <strong><?php echo e($activeCluster['count'] ?? ($activeFilter['count'] ?? ($data['totalAfterFilters'] ?? $items->count()))); ?></strong>
                    <small><?php echo e($activeCluster['next_action'] ?? 'Cluster selecionado no topo da página'); ?></small>
                </div>
            </div>

            <div class="pendencias-lote11-cluster-insights" aria-label="Indicadores rápidos do cluster ativo">
                <span class="danger"><strong><?php echo e($activeCluster['critical_count'] ?? 0); ?></strong> críticas</span>
                <span class="warning"><strong><?php echo e($activeCluster['blocked_count'] ?? 0); ?></strong> bloqueadas</span>
                <span class="info"><strong><?php echo e($activeCluster['sla_count'] ?? 0); ?></strong> com SLA ativo</span>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeCluster['has_search'] ?? false): ?>
                    <span class="neutral"><strong>Busca</strong> “<?php echo e($activeCluster['search']); ?>”</span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div class="pendencias-lote4-search-row">
                <label class="pendencias-lote4-search-box">
                    <span>Buscar na fila</span>
                    <input
                        type="search"
                        wire:model.live.debounce.350ms="buscaPendencias"
                        placeholder="Busque por título, empresa, responsável, status ou prioridade"
                    >
                </label>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($data['hasActiveFilters'] ?? false): ?>
                    <button type="button" class="pendencias-lote4-clear" wire:click="limparFiltrosPendencias" wire:loading.attr="disabled" wire:target="limparFiltrosPendencias">
                        Voltar para Minhas Pendências
                    </button>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </section>

        <section class="compliance-grid pendencias-lote1-grid-main">
            <article class="compliance-card pendencias-lote1-focus-card">
                <header>
                    <div>
                        <h2>Próximas ações recomendadas</h2>
                        <p>Fila curta para o usuário agir sem precisar interpretar toda a tabela.</p>
                    </div>
                    <span class="compliance-badge info"><?php echo e($filaRecomendada->count()); ?> itens filtrados</span>
                </header>

                <div class="pendencias-lote1-focus-list">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $filaRecomendada; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php
                            $prioridade = $item['prioridade'] ?? 'media';
                            $status = ucfirst(str_replace('_', ' ', $item['status'] ?? 'pendente'));
                            $tone = $item['prioridade_operacional_tone'] ?? ($item['is_late'] ? 'danger' : (($item['tone'] ?? '') ?: 'ok'));
                            $motivo = $item['prioridade_operacional_label'] ?? ($item['is_late'] ? 'Vencida' : (in_array($prioridade, ['urgente', 'alta'], true) ? 'Prioridade ' . ucfirst($prioridade) : 'Dentro da fila'));
                            $mensagemPrioridade = $item['prioridade_operacional_message'] ?? null;
                        ?>
                        <article class="pendencias-lote1-focus-item <?php echo e($tone); ?>">
                            <div class="pendencias-lote1-focus-top">
                                <div>
                                    <strong><?php echo e($item['titulo']); ?></strong>
                                    <small><?php echo e($item['empresa']); ?> · <?php echo e($item['responsavel']); ?></small>
                                </div>
                                <span class="compliance-badge <?php echo e($tone); ?>"><?php echo e($motivo); ?></span>
                            </div>
                            <p><?php echo e(\Illuminate\Support\Str::limit($item['descricao'] ?: 'Sem descrição cadastrada.', 120)); ?></p>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($mensagemPrioridade): ?>
                                <div class="pendencias-lote3-row-guidance <?php echo e($tone); ?>"><?php echo e($mensagemPrioridade); ?></div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <div class="pendencias-lote5-state-strip">
                                <span class="<?php echo e($item['sla_tone'] ?? 'gray'); ?>">SLA: <?php echo e($item['sla_resumo'] ?? 'Sem SLA iniciado'); ?></span>
                                <span class="<?php echo e(($item['bloqueado_operacional'] ?? false) ? 'danger' : 'ok'); ?>"><?php echo e(($item['bloqueado_operacional'] ?? false) ? 'Bloqueada' : 'Sem bloqueio'); ?></span>
                                <span class="<?php echo e(($item['dependencias_pendentes'] ?? 0) > 0 ? 'warning' : 'ok'); ?>"><?php echo e($item['dependencias_resumo'] ?? 'Sem dependências cadastradas'); ?></span>
                            </div>
                            <div class="pendencias-lote1-focus-meta">
                                <span>Status: <?php echo e($status); ?></span>
                                <span>Prazo: <?php echo e($item['vencimento']); ?></span>
                                <button type="button" class="compliance-link pendencias-lote2-inline-action" wire:click="abrirPendencia(<?php echo e($item['id']); ?>)" wire:loading.attr="disabled" wire:target="abrirPendencia(<?php echo e($item['id']); ?>)">Ver detalhes</button>
                            </div>
                        </article>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="compliance-empty pendencias-lote11-empty-state">
                            <strong><?php echo e($emptyState['title'] ?? 'Nenhuma pendência aberta.'); ?></strong><br>
                            <span><?php echo e($emptyState['message'] ?? 'Quando uma tarefa exigir ação, ela aparecerá aqui com prioridade e responsável.'); ?></span>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($emptyState['action_label']) && ! empty($emptyState['action'])): ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($emptyState['action'] === 'limparFiltrosPendencias'): ?>
                                    <button type="button" wire:click="limparFiltrosPendencias" wire:loading.attr="disabled" wire:target="limparFiltrosPendencias"><?php echo e($emptyState['action_label']); ?></button>
                                <?php else: ?>
                                    <a href="<?php echo e($emptyState['action']); ?>"><?php echo e($emptyState['action_label']); ?></a>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </article>

            <div class="compliance-list pendencias-lote1-side-stack">
                <article id="pendencias-vencidas" class="compliance-card pendencias-lote1-alert-card">
                    <header>
                        <div>
                            <h2>Vencidas</h2>
                            <p>Itens que devem ser tratados antes de qualquer nova demanda.</p>
                        </div>
                        <span class="compliance-badge <?php echo e($vencidas->count() > 0 ? 'danger' : 'ok'); ?>"><?php echo e($vencidas->count()); ?></span>
                    </header>
                    <div class="compliance-list">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $vencidas->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <div class="compliance-row pendencias-lote1-mini-row">
                                <div>
                                    <h3><?php echo e($item['titulo']); ?></h3>
                                    <small><?php echo e($item['responsavel']); ?> · <?php echo e($item['vencimento']); ?></small>
                                </div>
                                <button type="button" class="compliance-link compliance-link-light pendencias-lote2-inline-action" wire:click="abrirPendencia(<?php echo e($item['id']); ?>)" wire:loading.attr="disabled" wire:target="abrirPendencia(<?php echo e($item['id']); ?>)">Ver</button>
                            </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <div class="compliance-empty">Nenhuma pendência vencida no momento.</div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </article>



                <article class="compliance-card pendencias-lote3-radar-card">
                    <header>
                        <div>
                            <h2>Radar de prioridade</h2>
                            <p>Distribuição da fila para decidir o próximo movimento.</p>
                        </div>
                    </header>
                    <div class="pendencias-lote3-radar-list">
                        <div class="danger"><span>Críticas</span><strong><?php echo e($vencidas->count() + $semResponsavel->count()); ?></strong><small>Vencidas ou sem responsável</small></div>
                        <div class="warning"><span>Hoje / alta</span><strong><?php echo e($vencemHoje->count() + $criticas->count()); ?></strong><small>Prazo imediato ou prioridade alta</small></div>
                        <div class="info"><span>Próximos dias</span><strong><?php echo e($proximosPrazos->count() + $aprovacao->count()); ?></strong><small>Prazos próximos ou aprovação</small></div>
                        <div class="ok"><span>No controle</span><strong><?php echo e($noControle->count()); ?></strong><small>Sem alerta operacional</small></div>
                    </div>
                </article>

                <article id="status-pendencias" class="compliance-card">
                    <header>
                        <div>
                            <h2>Por status</h2>
                            <p>Resumo rápido da fila.</p>
                        </div>
                    </header>
                    <div class="compliance-list">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($data['byStatus'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <div class="compliance-row pendencias-lote1-mini-row">
                                <div>
                                    <h3><?php echo e($row['label']); ?></h3>
                                    <small>Pendências nesse status</small>
                                </div>
                                <strong><?php echo e($row['count']); ?></strong>
                            </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <div class="compliance-empty">Sem pendências.</div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </article>
            </div>
        </section>

        <section class="compliance-grid pendencias-lote1-grid-main">
            <article id="lista-pendencias" class="compliance-card pendencias-lote1-table-card">
                <header>
                    <div>
                        <h2>Lista operacional completa</h2>
                        <p>Itens abertos ordenados por atraso, urgência e prazo. Os filtros acima também afetam esta lista.</p>
                    </div>
                    <span class="compliance-badge info"><?php echo e($items->count()); ?> exibidas</span>
                </header>
                <div class="compliance-table-wrap">
                    <table class="compliance-table pendencias-lote1-table">
                        <thead>
                            <tr>
                                <th>Pendência</th>
                                <th>Empresa</th>
                                <th>Responsável</th>
                                <th>Status</th>
                                <th>Prioridade operacional</th>
                                <th>Prioridade</th>
                                <th>Prazo</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <?php
                                    $tone = $item['prioridade_operacional_tone'] ?? ($item['is_late'] ? 'danger' : (($item['tone'] ?? '') ?: 'ok'));
                                    $priorityLabel = $item['prioridade_operacional_label'] ?? ucfirst($item['prioridade'] ?? 'Média');
                                    $priorityMessage = $item['prioridade_operacional_message'] ?? null;
                                ?>
                                <tr class="pendencias-lote1-table-row <?php echo e($tone); ?>">
                                    <td>
                                        <strong><?php echo e($item['titulo']); ?></strong><br>
                                        <small><?php echo e(\Illuminate\Support\Str::limit($item['descricao'] ?: 'Sem descrição', 90)); ?></small>
                                    </td>
                                    <td><?php echo e($item['empresa']); ?></td>
                                    <td><?php echo e($item['responsavel']); ?></td>
                                    <td><span class="compliance-badge <?php echo e($tone); ?>"><?php echo e(ucfirst(str_replace('_', ' ', $item['status']))); ?></span></td>
                                    <td>
                                        <span class="pendencias-lote3-priority-pill <?php echo e($tone); ?>"><?php echo e($priorityLabel); ?></span>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($priorityMessage): ?>
                                            <small class="pendencias-lote3-priority-hint"><?php echo e($priorityMessage); ?></small>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                    <td><?php echo e(ucfirst($item['prioridade'])); ?></td>
                                    <td>
                                        <?php echo e($item['vencimento']); ?>

                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item['is_late']): ?>
                                            <br><span class="compliance-badge danger">Vencida</span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                    <td><button type="button" class="compliance-link pendencias-lote2-inline-action" wire:click="abrirPendencia(<?php echo e($item['id']); ?>)" wire:loading.attr="disabled" wire:target="abrirPendencia(<?php echo e($item['id']); ?>)">Ver detalhes</button></td>
                                </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <tr>
                                    <td colspan="8" class="compliance-empty pendencias-lote11-table-empty">
                                        <strong><?php echo e($emptyState['title'] ?? 'Nenhuma pendência encontrada.'); ?></strong><br>
                                        <span><?php echo e($emptyState['message'] ?? 'Ajuste os filtros ou limpe a busca para voltar a enxergar a fila completa.'); ?></span>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($emptyState['action_label']) && ! empty($emptyState['action'])): ?>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($emptyState['action'] === 'limparFiltrosPendencias'): ?>
                                                <button type="button" wire:click="limparFiltrosPendencias" wire:loading.attr="disabled" wire:target="limparFiltrosPendencias"><?php echo e($emptyState['action_label']); ?></button>
                                            <?php else: ?>
                                                <a href="<?php echo e($emptyState['action']); ?>"><?php echo e($emptyState['action_label']); ?></a>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                </tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </article>

            <article id="nova-pendencia" class="compliance-card pendencias-lote1-form-card">
                <header>
                    <div>
                        <h2>Nova pendência</h2>
                        <p>Crie uma tarefa real de compliance com dono, prioridade e prazo.</p>
                    </div>
                </header>
                <form wire:submit.prevent="criarPendencia" class="compliance-form">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($data['options']['empresas'] ?? []) > 1): ?>
                        <label class="wide">
                            <span>Empresa</span>
                            <select wire:model="empresaId">
                                <option value="">Selecione</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $data['options']['empresas']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <option value="<?php echo e($e['id']); ?>"><?php echo e($e['nome']); ?></option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </select>
                        </label>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <label class="wide">
                        <span>Título</span>
                        <input type="text" wire:model.defer="titulo" placeholder="Ex: Coletar assinatura do contrato">
                    </label>
                    <label class="wide">
                        <span>Descrição</span>
                        <textarea rows="3" wire:model.defer="descricao" placeholder="Descreva o que precisa ser feito"></textarea>
                    </label>
                    <label>
                        <span>Responsável</span>
                        <select wire:model="responsavelId">
                            <option value="">Automático</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $data['options']['responsaveis'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <option value="<?php echo e($r['id']); ?>"><?php echo e($r['nome']); ?></option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </select>
                    </label>
                    <label>
                        <span>Prioridade</span>
                        <select wire:model="prioridade">
                            <option value="baixa">Baixa</option>
                            <option value="media">Média</option>
                            <option value="alta">Alta</option>
                            <option value="urgente">Urgente</option>
                        </select>
                    </label>
                    <label class="wide">
                        <span>Prazo</span>
                        <input type="date" wire:model="dataVencimento">
                    </label>
                    <div class="wide pz-ux-form-actions">
                        <button type="submit" wire:confirm="Deseja criar esta pendência?" wire:loading.attr="disabled" wire:target="criarPendencia">Criar pendência</button>
                        <span wire:loading.delay wire:target="criarPendencia" class="pz-ux-loading"><i class="pz-ux-spinner"></i> Criando pendência...</span>
                    </div>
                </form>
            </article>
        </section>


        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pendenciaSelecionada): ?>
            <div class="pendencias-lote2-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="pendencia-modal-title" <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'pendencia-modal-'.e($pendenciaSelecionada['id']).''; ?>wire:key="pendencia-modal-<?php echo e($pendenciaSelecionada['id']); ?>">
                <div class="pendencias-lote2-modal-shell">
                    <div class="pendencias-lote2-modal-header <?php echo e($pendenciaSelecionada['tone']); ?>">
                        <div>
                            <span class="pz-ux-kicker">Detalhes da pendência</span>
                            <h2 id="pendencia-modal-title"><?php echo e($pendenciaSelecionada['titulo']); ?></h2>
                            <p><?php echo e($pendenciaSelecionada['empresa']); ?> · <?php echo e($pendenciaSelecionada['responsavel']); ?></p>
                        </div>
                        <button type="button" class="pendencias-lote2-modal-close" wire:click="fecharPendencia" aria-label="Fechar detalhes da pendência">×</button>
                    </div>

                    <div class="pendencias-lote2-modal-body">
                        <div class="pendencias-lote2-status-grid">
                            <div>
                                <span>Status</span>
                                <strong><?php echo e($pendenciaSelecionada['status']); ?></strong>
                            </div>
                            <div>
                                <span>Prioridade</span>
                                <strong><?php echo e($pendenciaSelecionada['prioridade']); ?></strong>
                            </div>
                            <div>
                                <span>Prazo</span>
                                <strong><?php echo e($pendenciaSelecionada['vencimento']); ?></strong>
                                <small><?php echo e($pendenciaSelecionada['prazo']); ?></small>
                            </div>
                            <div>
                                <span>Aprovação</span>
                                <strong><?php echo e($pendenciaSelecionada['approval_status']); ?></strong>
                            </div>
                        </div>

                        <div class="pendencias-lote3-modal-priority <?php echo e($pendenciaSelecionada['prioridade_operacional_tone']); ?>">
                            <span>Prioridade operacional</span>
                            <strong><?php echo e($pendenciaSelecionada['prioridade_operacional_label']); ?></strong>
                            <p><?php echo e($pendenciaSelecionada['prioridade_operacional_message']); ?></p>
                        </div>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pendenciaSelecionada['bloqueado']): ?>
                            <div class="pendencias-lote2-warning-box">
                                <?php echo e($pendenciaSelecionada['bloqueio_resumo']); ?>. Revise os detalhes antes de concluir.
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <div class="pendencias-lote5-advanced-panel">
                            <div class="pendencias-lote5-advanced-card <?php echo e($pendenciaSelecionada['sla_tone']); ?>">
                                <div>
                                    <span>SLA operacional</span>
                                    <strong><?php echo e($pendenciaSelecionada['sla_resumo']); ?></strong>
                                    <small>Prazo-alvo: <?php echo e($pendenciaSelecionada['sla_prazo_alvo']); ?> · <?php echo e($pendenciaSelecionada['sla_tempo']); ?></small>
                                </div>
                                <div class="pendencias-lote5-progress" aria-label="Percentual de SLA consumido">
                                    <span style="width: <?php echo e($pendenciaSelecionada['sla_percentual']); ?>%"></span>
                                </div>
                            </div>

                            <div class="pendencias-lote5-advanced-card <?php echo e($pendenciaSelecionada['bloqueado'] ? 'danger' : 'ok'); ?>">
                                <div>
                                    <span>Bloqueio</span>
                                    <strong><?php echo e($pendenciaSelecionada['bloqueado'] ? 'Atenção necessária' : 'Fluxo liberado'); ?></strong>
                                    <small><?php echo e($pendenciaSelecionada['bloqueio_resumo']); ?></small>
                                </div>
                            </div>
                        </div>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($pendenciaSelecionada['dependencias'])): ?>
                            <div class="pendencias-lote2-modal-section pendencias-lote5-dependencies">
                                <h3>Dependências</h3>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $pendenciaSelecionada['dependencias']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dependencia): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <div class="pendencias-lote5-dependency-row <?php echo e($dependencia['resolvida'] ? 'ok' : ($dependencia['bloqueante'] ? 'danger' : 'warning')); ?>">
                                        <div>
                                            <strong><?php echo e($dependencia['titulo']); ?></strong>
                                            <small><?php echo e($dependencia['status']); ?><?php echo e($dependencia['bloqueante'] ? ' · bloqueante' : ''); ?></small>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($dependencia['observacao']): ?>
                                                <p><?php echo e($dependencia['observacao']); ?></p>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                        <span><?php echo e($dependencia['resolvida'] ? 'Resolvida' : 'Pendente'); ?></span>
                                    </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <div class="pendencias-lote2-modal-section">
                            <h3>Descrição</h3>
                            <p><?php echo e($pendenciaSelecionada['descricao']); ?></p>
                        </div>

                        <div class="pendencias-lote2-modal-section pendencias-lote2-meta-section">
                            <div>
                                <span>Tipo/Categoria</span>
                                <strong><?php echo e($pendenciaSelecionada['tipo']); ?></strong>
                            </div>
                            <div>
                                <span>SLA</span>
                                <strong><?php echo e($pendenciaSelecionada['sla_resumo']); ?></strong>
                                <small><?php echo e($pendenciaSelecionada['sla_tempo']); ?> · <?php echo e($pendenciaSelecionada['sla_percentual']); ?>% consumido</small>
                            </div>
                        </div>

                        <label class="pendencias-lote2-observacao">
                            <span>Observação para aprovação/reprovação</span>
                            <textarea rows="3" wire:model.defer="observacaoAcao" placeholder="Use este campo quando for solicitar aprovação, aprovar ou reprovar."></textarea>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['observacaoAcao'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <small class="pendencias-lote2-field-error"><?php echo e($message); ?></small>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </label>
                    </div>

                    <div class="pendencias-lote2-modal-actions">
                        <a class="pendencias-lote2-action secondary" href="<?php echo e($pendenciaSelecionada['edit_url']); ?>">Editar completo</a>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pendenciaSelecionada['can_iniciar_sla']): ?>
                            <button type="button" class="pendencias-lote2-action info" wire:click="iniciarSlaPendenciaSelecionada" wire:confirm="Deseja iniciar o SLA desta pendência?" wire:loading.attr="disabled" wire:target="iniciarSlaPendenciaSelecionada">Iniciar SLA</button>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pendenciaSelecionada['can_atualizar_sla']): ?>
                            <button type="button" class="pendencias-lote2-action warning" wire:click="atualizarSlaPendenciaSelecionada" wire:loading.attr="disabled" wire:target="atualizarSlaPendenciaSelecionada">Atualizar SLA</button>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pendenciaSelecionada['can_finalizar_sla']): ?>
                            <button type="button" class="pendencias-lote2-action success" wire:click="finalizarSlaPendenciaSelecionada" wire:confirm="Deseja finalizar o SLA desta pendência?" wire:loading.attr="disabled" wire:target="finalizarSlaPendenciaSelecionada">Finalizar SLA</button>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pendenciaSelecionada['can_solicitar_aprovacao']): ?>
                            <button type="button" class="pendencias-lote2-action warning" wire:click="solicitarAprovacaoPendenciaSelecionada" wire:loading.attr="disabled" wire:target="solicitarAprovacaoPendenciaSelecionada">Solicitar aprovação</button>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pendenciaSelecionada['can_aprovar']): ?>
                            <button type="button" class="pendencias-lote2-action success" wire:click="aprovarPendenciaSelecionada" wire:loading.attr="disabled" wire:target="aprovarPendenciaSelecionada">Aprovar</button>
                            <button type="button" class="pendencias-lote2-action danger" wire:click="reprovarPendenciaSelecionada" wire:loading.attr="disabled" wire:target="reprovarPendenciaSelecionada">Reprovar</button>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pendenciaSelecionada['can_concluir']): ?>
                            <button type="button" class="pendencias-lote2-action primary" wire:click="concluirPendenciaSelecionada" wire:confirm="Deseja concluir esta pendência?" wire:loading.attr="disabled" wire:target="concluirPendenciaSelecionada">Concluir agora</button>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('pendencias-lote8-feedback', () => {
                const page = document.querySelector('.pendencias-lote1-page');
                if (! page) {
                    return;
                }

                page.classList.add('pendencias-lote8-just-updated');
                window.setTimeout(() => page.classList.remove('pendencias-lote8-just-updated'), 1200);
            });
        });
    </script>

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
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views/filament/pages/compliance-pendencias.blade.php ENDPATH**/ ?>