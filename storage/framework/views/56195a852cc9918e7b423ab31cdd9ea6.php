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

    <link rel="stylesheet" href="<?php echo e(asset('css/home-classica.css')); ?>?v=20260520-sidebar-logo-final">
    <link rel="stylesheet" href="<?php echo e(asset('css/home-operacional.css')); ?>?v=20260520-sidebar-logo-final">
    <link rel="stylesheet" href="<?php echo e(asset('css/trabalho-pages.css')); ?>?v=20260507-home-kanban">
    <link rel="stylesheet" href="<?php echo e(asset('css/prazzu-ux-essentials.css')); ?>?v=<?php echo e(file_exists(public_path('css/prazzu-ux-essentials.css')) ? filemtime(public_path('css/prazzu-ux-essentials.css')) : time()); ?>">

    <?php
        $trabalhoCssPath = public_path('css/trabalho-pages.css');

        $kpis = $dashboard['kpis'] ?? [];
        $tarefas = $dashboard['tarefas'] ?? ['tabs' => [], 'itens' => []];
        $kanban = $dashboard['kanban'] ?? [];
        $prazos = $dashboard['prazos'] ?? [];
        $sla = $dashboard['sla'] ?? [];
        $documentos = $dashboard['documentos'] ?? [];
        $financeiro = $dashboard['financeiro'] ?? [];
        $portal = $dashboard['portal'] ?? [];
        $compliance = $dashboard['compliance'] ?? [];
        $atividades = $dashboard['atividades'] ?? [];
        $assistente = $dashboard['assistente'] ?? [];
        $urls = $dashboard['urls'] ?? [];
        $usuario = explode(' ', $dashboard['usuario'] ?? 'Usuário')[0];

        $resumoHoje = $dashboard['resumoHoje'] ?? [];
        $minhasPendencias = $dashboard['minhasPendencias'] ?? [];
        $vencimentosProximos = $dashboard['vencimentosProximos'] ?? [];
        $aprovacoesAguardando = $dashboard['aprovacoesAguardando'] ?? [];
        $itensAtrasados = $dashboard['itensAtrasados'] ?? [];
        $ultimosComentarios = $dashboard['ultimosComentarios'] ?? [];
        $atalhosRapidos = $dashboard['atalhosRapidos'] ?? [];
        $resumoEmpresas = $dashboard['resumoEmpresas'] ?? [];
        $fluxoOperacional = $dashboard['fluxoOperacional'] ?? [];
        $notificacoes = $dashboard['notificacoes'] ?? [];
        $whiteLabel = \App\Support\WhiteLabelSettings::make();
        $assistantName = $whiteLabel->assistantName();
        $brandName = $whiteLabel->displayName();
    ?>


    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(file_exists($trabalhoCssPath)): ?>
        <style>
            <?php echo file_get_contents($trabalhoCssPath); ?>

        </style>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <style>
        .pz-home-kanban-section {
            margin-top: 24px;
            width: 100%;
        }

        .pz-home-kanban-card {
            overflow: hidden;
        }

        .pz-home-kanban-wrap {
            margin-top: 16px;
            overflow-x: auto;
            padding-bottom: 4px;
        }

        .pz-home-tp-kanban {
            grid-template-columns: repeat(3, minmax(260px, 1fr));
            gap: 16px;
            min-width: 820px;
        }

        .pz-home-tp-kanban .tp-kanban-column {
            min-height: 320px;
        }

        .pz-home-tp-kanban .tp-kanban-card {
            min-height: auto;
            text-decoration: none;
        }

        .pz-home-tp-kanban .tp-kanban-card-top strong {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .pz-home-kanban-link {
            margin-top: 14px;
            display: flex;
            justify-content: center;
        }

        .pz-home-kanban-link a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            padding: 10px 16px;
            background: #f4f0ff;
            color: #5b2fbf;
            font-size: 13px;
            font-weight: 800;
            text-decoration: none;
        }

        @media (max-width: 920px) {
            .pz-home-tp-kanban {
                min-width: 760px;
            }
        }
    </style>


    <section class="pz-ux-toolbar">
        <div>
            <strong>Comece por aqui</strong>
            <span>Revise pendências críticas, documentos e aprovações antes de abrir telas detalhadas.</span>
        </div>
        <div class="pz-ux-actions">
            <a class="pz-ux-action primary" href="<?php echo e($urls['novaTarefa'] ?? '#'); ?>">Nova tarefa</a>
            <a class="pz-ux-action" href="<?php echo e($urls['enviarDocumento'] ?? '#'); ?>">Enviar documento</a>
            <a class="pz-ux-action subtle" href="<?php echo e($urls['prazos'] ?? '#'); ?>">Ver prazos</a>
        </div>
    </section>

    <section class="pz-ux-block soft">
        <div class="pz-ux-head">
            <div>
                <span class="pz-ux-kicker">Orientação rápida</span>
                <h2>O que fazer primeiro hoje</h2>
                <p>Use os atalhos abaixo para trabalhar em ordem de impacto e reduzir cliques desnecessários.</p>
            </div>
        </div>
        <div class="pz-ux-grid three">
            <article class="pz-ux-guide-card"><span class="pz-ux-guide-icon">1</span><div><strong>Resolver atrasos</strong><span>Priorize itens vencidos e aprovações travadas antes das tarefas novas.</span></div></article>
            <article class="pz-ux-guide-card"><span class="pz-ux-guide-icon">2</span><div><strong>Concluir pendências</strong><span>Abra sua fila operacional e finalize o que depende de responsável interno.</span></div></article>
            <article class="pz-ux-guide-card"><span class="pz-ux-guide-icon">3</span><div><strong>Atualizar documentos</strong><span>Envie anexos faltantes e revise documentos que vão aparecer no portal.</span></div></article>
        </div>
    </section>

    <section class="pz-layout-switcher" data-home-layout-switcher>
        <div>
            <strong data-home-layout-title>Home clássica</strong>
            <small data-home-layout-description>Você está vendo a Home antiga, com todos os indicadores originais preservados.</small>
        </div>
        <button type="button" class="pz-layout-toggle-btn" data-home-layout-toggle>Ver Home operacional</button>
    </section>

    <div data-home-layout="classic">
    <div class="pz-home-shell">
        <main class="pz-main-column">
            <section class="pz-hero-row">
                <div>
                    <h1>Olá, <?php echo e($usuario); ?>! <span>👋</span></h1>
                    <p>Aqui está o resumo da sua operação hoje.</p>
                </div>

                <div class="pz-quick-actions">
                    <a href="<?php echo e($urls['novaTarefa'] ?? '#'); ?>" class="pz-btn pz-btn-primary">＋ Nova Tarefa</a>
                    <a href="<?php echo e($urls['enviarDocumento'] ?? '#'); ?>" class="pz-btn">↥ Enviar Documento</a>
                    <a href="<?php echo e($urls['novoCliente'] ?? '#'); ?>" class="pz-btn">♙ Novo Cliente</a>
                </div>
            </section>

            <section class="pz-kpi-grid">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $kpis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kpi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <article class="pz-card pz-kpi-card pz-tone-<?php echo e($kpi['tone'] ?? 'purple'); ?>">
                        <div class="pz-kpi-top">
                            <span><?php echo e($kpi['label'] ?? '-'); ?></span>
                            <b><?php echo e($kpi['icon'] ?? '•'); ?></b>
                        </div>

                        <strong><?php echo e($kpi['value'] ?? '-'); ?></strong>
                        <small><?php echo e($kpi['trend'] ?? '-'); ?></small>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($kpi['spark'])): ?>
                            <div class="pz-sparkline">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $kpi['spark']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $point): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <i style="height: <?php echo e(max(18, min(94, (int) $point * 3))); ?>%"></i>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="pz-risk-pill"><?php echo e($kpi['trend'] ?? 'Acompanhar'); ?></div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </article>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </section>

            <section class="pz-grid-top">
                <article class="pz-card pz-tasks-card">
                    <div class="pz-card-head">
                        <h2>Minhas tarefas</h2>
                        <a href="<?php echo e($urls['tarefas'] ?? '#'); ?>">Ver todas</a>
                    </div>

                    <div class="pz-tabs">
                        <span class="is-active">Pendentes <b><?php echo e($tarefas['tabs']['pendentes'] ?? 0); ?></b></span>
                        <span>Em andamento <b><?php echo e($tarefas['tabs']['em_andamento'] ?? 0); ?></b></span>
                        <span>Concluídas <b><?php echo e($tarefas['tabs']['concluidas'] ?? 0); ?></b></span>
                    </div>

                    <div class="pz-task-list">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $tarefas['itens'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <a href="<?php echo e($item['url'] ?? '#'); ?>" class="pz-task-row">
                                <span class="pz-checkbox"></span>
                                <strong><?php echo e($item['titulo'] ?? '-'); ?></strong>
                                <em class="pz-badge pz-badge-<?php echo e($item['prioridade']['class'] ?? 'warning'); ?>">
                                    <?php echo e($item['prioridade']['label'] ?? 'Média'); ?>

                                </em>
                                <small><?php echo e($item['data'] ?? '-'); ?></small>
                            </a>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <div class="pz-empty"><strong>Nenhuma tarefa pendente.</strong><br>Quando houver algo para você executar, aparecerá aqui com prioridade e prazo.</div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <a href="<?php echo e($urls['novaTarefa'] ?? '#'); ?>" class="pz-add-link">＋ Adicionar tarefa</a>
                </article>

                <article class="pz-card pz-deadlines-card">
                    <div class="pz-card-head">
                        <h2>Próximos prazos</h2>
                        <a href="<?php echo e($urls['prazos'] ?? '#'); ?>">Ver todos</a>
                    </div>

                    <div class="pz-deadline-list">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $prazos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $prazo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <a href="<?php echo e($prazo['url'] ?? '#'); ?>" class="pz-deadline-row">
                                <span>▣</span>
                                <div>
                                    <strong><?php echo e($prazo['titulo'] ?? '-'); ?></strong>
                                    <small><?php echo e($prazo['empresa'] ?? '-'); ?> • <?php echo e($prazo['data'] ?? '-'); ?></small>
                                </div>
                                <em><?php echo e($prazo['status'] ?? '-'); ?></em>
                            </a>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <div class="pz-empty"><strong>Nenhum prazo próximo.</strong><br>Documentos e tarefas com vencimento serão destacados automaticamente.</div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </article>
            
            </section>

            <section class="pz-kanban-full pz-home-kanban-section">
                <article class="pz-card pz-kanban-card pz-home-kanban-card">
                    <div class="pz-card-head">
                        <h2>Visão Kanban</h2>
                        <a href="<?php echo e($urls['kanban'] ?? '#'); ?>">Ver quadro completo</a>
                    </div>

                    <div class="pz-home-kanban-wrap">
                        <div class="tp-kanban pz-home-tp-kanban">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $kanban; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $coluna): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <div class="tp-kanban-column tp-kanban-<?php echo e($coluna['key'] ?? 'pendente'); ?>">
                                    <div class="tp-kanban-header">
                                        <div>
                                            <strong><?php echo e($coluna['label'] ?? '-'); ?></strong>
                                            <small><?php echo e($coluna['total'] ?? 0); ?> item(ns)</small>
                                        </div>
                                        <span><?php echo e($coluna['total'] ?? 0); ?></span>
                                    </div>

                                    <div class="tp-kanban-cards">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($coluna['itens'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                            <?php
                                                $prioridadeClasse = $item['prioridade']['class'] ?? 'warning';
                                                $prioridadeBadge = match ($prioridadeClasse) {
                                                    'danger' => 'tp-mini-danger',
                                                    'success' => 'tp-mini-success',
                                                    default => '',
                                                };
                                            ?>

                                            <a href="<?php echo e($item['url'] ?? '#'); ?>" class="tp-kanban-card">
                                                <div class="tp-kanban-card-top">
                                                    <strong><?php echo e($item['titulo'] ?? '-'); ?></strong>
                                                    <span class="tp-mini-badge <?php echo e($prioridadeBadge); ?>">
                                                        <?php echo e($item['prioridade']['label'] ?? 'Média'); ?>

                                                    </span>
                                                </div>

                                                <span><?php echo e($item['empresa'] ?? 'Sem empresa'); ?></span>

                                                <div class="tp-kanban-meta">
                                                    <span>Resumo da Home</span>
                                                    <span>Abrir item</span>
                                                </div>
                                            </a>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                            <div class="tp-empty">Nenhum item nesta coluna.</div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    </div>
                </article>
            </section>

            <section class="pz-grid-bottom">
                <article class="pz-card pz-sla-card">
                    <div class="pz-card-head">
                        <h2>SLA e Prazos</h2>
                        <a href="<?php echo e($urls['prazos'] ?? '#'); ?>">Ver todos</a>
                    </div>

                    <div class="pz-sla-content">
                        <div class="pz-donut">
                            <b><?php echo e($sla['total'] ?? 0); ?></b>
                            <span>Total</span>
                        </div>

                        <div class="pz-sla-legend">
                            <p><i class="ok"></i> No prazo <strong><?php echo e($sla['noPrazo'] ?? 0); ?> (<?php echo e($sla['percentuais']['noPrazo'] ?? 0); ?>%)</strong></p>
                            <p><i class="warn"></i> Atenção <strong><?php echo e($sla['atencao'] ?? 0); ?> (<?php echo e($sla['percentuais']['atencao'] ?? 0); ?>%)</strong></p>
                            <p><i class="late"></i> Vencidos <strong><?php echo e($sla['vencidos'] ?? 0); ?> (<?php echo e($sla['percentuais']['vencidos'] ?? 0); ?>%)</strong></p>
                        </div>
                    </div>
                </article>

                <article class="pz-card pz-docs-card">
                    <div class="pz-card-head">
                        <h2>Documentos recentes</h2>
                        <a href="<?php echo e($urls['documentos'] ?? '#'); ?>">Ver todos</a>
                    </div>

                    <div class="pz-doc-list">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $documentos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <a href="<?php echo e($doc['url'] ?? '#'); ?>" class="pz-doc-row">
                                <span>▧</span>
                                <div>
                                    <strong><?php echo e($doc['titulo'] ?? '-'); ?></strong>
                                    <small><?php echo e($doc['meta'] ?? '-'); ?></small>
                                </div>
                                <em class="pz-badge pz-badge-<?php echo e($doc['status']['class'] ?? 'success'); ?>">
                                    <?php echo e($doc['status']['label'] ?? 'Válido'); ?>

                                </em>
                            </a>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <div class="pz-empty">Nenhum documento recente.</div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </article>

                <article class="pz-card pz-finance-card">
                    <div class="pz-card-head">
                        <h2>Faturamento</h2>
                        <a href="<?php echo e($urls['financeiro'] ?? '#'); ?>">Este mês⌄</a>
                    </div>

                    <strong class="pz-money">R$ <?php echo e(number_format($financeiro['total'] ?? 0, 2, ',', '.')); ?></strong>
                    <small class="pz-positive">+32% vs mês anterior</small>

                    <div class="pz-finance-line">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($financeiro['series'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $point): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <i style="height: <?php echo e(max(18, min(95, (int) $point * 3))); ?>%"></i>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>

                    <div class="pz-finance-boxes">
                        <div>
                            <span>Recebido</span>
                            <strong>R$ <?php echo e(number_format($financeiro['recebido'] ?? 0, 2, ',', '.')); ?></strong>
                        </div>
                        <div>
                            <span>A receber</span>
                            <strong>R$ <?php echo e(number_format($financeiro['aReceber'] ?? 0, 2, ',', '.')); ?></strong>
                        </div>
                    </div>
                </article>
            </section>

            <section class="pz-footer-grid">
                <article class="pz-card pz-portal-card">
                    <div class="pz-card-head">
                        <h2>Portal do Cliente</h2>
                        <a href="<?php echo e($urls['clientes'] ?? '#'); ?>">Ver portal</a>
                    </div>

                    <div class="pz-portal-items">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $portal; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <a href="<?php echo e($item['url'] ?? '#'); ?>">
                                <b>◈</b>
                                <strong><?php echo e($item['label'] ?? '-'); ?></strong>
                                <small><?php echo e($item['value'] ?? 0); ?> <?php echo e($item['hint'] ?? ''); ?></small>
                            </a>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>
                </article>

                <article class="pz-card pz-compliance-card">
                    <div class="pz-card-head">
                        <h2>Compliance</h2>
                        <a href="<?php echo e($urls['prazos'] ?? '#'); ?>">Ver painel</a>
                    </div>

                    <div class="pz-compliance-items">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $compliance; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <div>
                                <span><?php echo e($item['label'] ?? '-'); ?></span>
                                <strong><?php echo e($item['value'] ?? 0); ?></strong>
                                <small><?php echo e($item['hint'] ?? ''); ?></small>
                            </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>
                </article>
            </section>
        </main>

        <aside class="pz-right-column">
            <section class="pz-card pz-ai-card">
                <div class="pz-ai-head">
                    <span>✧</span>
                    <strong><?php echo e($assistantName); ?> <b>BETA</b></strong>
                    <em>×</em>
                </div>

                <h3>Olá, <?php echo e($usuario); ?>! 👋</h3>
                <p>Como posso ajudar hoje?</p>

                <div class="pz-ai-actions">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $assistente; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $acao): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <a href="<?php echo e($acao['url'] ?? '#'); ?>"><?php echo e($acao['texto'] ?? '-'); ?></a>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            </section>

            <section class="pz-card pz-activity-card">
                <div class="pz-card-head">
                    <h2>Atividades recentes</h2>
                    <a href="<?php echo e($urls['kanban'] ?? '#'); ?>">Ver todas</a>
                </div>

                <div class="pz-activity-list">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $atividades; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $atividade): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="pz-activity-row">
                            <span><?php echo e(strtoupper(mb_substr($atividade['usuario'] ?? 'S', 0, 1))); ?></span>
                            <div>
                                <strong><?php echo e($atividade['titulo'] ?? '-'); ?></strong>
                                <p><?php echo e($atividade['descricao'] ?? '-'); ?></p>
                                <small><?php echo e($atividade['quando'] ?? '-'); ?></small>
                            </div>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="pz-empty">Nenhuma atividade recente.</div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </section>

            <section class="pz-card pz-upgrade-card">
                <div class="pz-crown">♛</div>
                <h2>Desbloqueie o poder do <?php echo e($brandName); ?></h2>
                <p>Recursos avançados de IA, automações, relatórios personalizados e muito mais.</p>
                <a href="<?php echo e($urls['financeiro'] ?? '#'); ?>">Upgrade do plano</a>
            </section>
        </aside>
    </div>

    </div>

    <div data-home-layout="operational" class="is-hidden">
        <div class="pz-home-shell">
                <section class="pz-home-hero pz-panel">
                    <div class="pz-hero-copy">
                        <span class="pz-eyebrow">Painel operacional</span>
                        <h1>Olá, <?php echo e($usuario); ?>. Veja o que precisa de atenção hoje.</h1>
                        <p>Centralize pendências, vencimentos, aprovações, comentários e atalhos sem precisar navegar por várias telas.</p>
                    </div>
        
                    <div class="pz-hero-actions">
                        <a href="<?php echo e($urls['novaTarefa'] ?? '#'); ?>" class="pz-action-primary">＋ Nova tarefa</a>
                        <a href="<?php echo e($urls['minhasPendencias'] ?? '#'); ?>" class="pz-action-secondary">✓ Pendências</a>
                        <a href="<?php echo e($urls['centralNotificacoes'] ?? '#'); ?>" class="pz-action-secondary">◉ Notificações</a>
                    </div>
                </section>
        
                <section class="pz-summary-grid">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $resumoHoje; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <a href="<?php echo e($card['url'] ?? '#'); ?>" class="pz-summary-card pz-tone-<?php echo e($card['tone'] ?? 'slate'); ?>">
                            <div>
                                <span><?php echo e($card['label'] ?? '-'); ?></span>
                                <strong><?php echo e($card['value'] ?? 0); ?></strong>
                            </div>
                            <small><?php echo e($card['hint'] ?? 'Acompanhar'); ?></small>
                        </a>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="pz-empty pz-empty-wide">Ainda não existem dados operacionais para exibir na Home.</div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </section>
        
                <section class="pz-main-grid">
                    <main class="pz-left-column">
                        <section class="pz-panel pz-focus-panel">
                            <div class="pz-section-head">
                                <div>
                                    <span class="pz-eyebrow">Prioridade do dia</span>
                                    <h2>Pendências</h2>
                                </div>
                                <a href="<?php echo e($urls['minhasPendencias'] ?? '#'); ?>">Ver todas</a>
                            </div>
        
                            <div class="pz-task-list">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $minhasPendencias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <a href="<?php echo e($item['url'] ?? '#'); ?>" class="pz-task-row pz-row-<?php echo e($item['badge'] ?? 'info'); ?>">
                                        <span class="pz-row-status"></span>
                                        <div class="pz-row-main">
                                            <strong><?php echo e($item['titulo'] ?? '-'); ?></strong>
                                            <small><?php echo e($item['empresa'] ?? 'Sem empresa'); ?> • <?php echo e($item['responsavel'] ?? 'Sem responsável'); ?></small>
                                        </div>
                                        <div class="pz-row-meta">
                                            <em><?php echo e($item['status'] ?? '-'); ?></em>
                                            <span><?php echo e($item['data'] ?? '-'); ?></span>
                                        </div>
                                    </a>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    <div class="pz-empty">Nenhuma pendência crítica encontrada.</div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </section>
        
                        <section class="pz-two-columns">
                            <article class="pz-panel">
                                <div class="pz-section-head">
                                    <div>
                                        <span class="pz-eyebrow">Calendário operacional</span>
                                        <h2>Vencimentos próximos</h2>
                                    </div>
                                    <a href="<?php echo e($urls['prazos'] ?? '#'); ?>">Ver prazos</a>
                                </div>
        
                                <div class="pz-compact-list">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $vencimentosProximos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <a href="<?php echo e($item['url'] ?? '#'); ?>" class="pz-compact-row">
                                            <b class="pz-date-pill"><?php echo e($item['data'] ?? '-'); ?></b>
                                            <div>
                                                <strong><?php echo e($item['titulo'] ?? '-'); ?></strong>
                                                <small><?php echo e($item['empresa'] ?? 'Sem empresa'); ?> • <?php echo e($item['tempo'] ?? '-'); ?></small>
                                            </div>
                                        </a>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                        <div class="pz-empty">Nenhum vencimento nos próximos dias.</div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </article>
        
                            <article class="pz-panel">
                                <div class="pz-section-head">
                                    <div>
                                        <span class="pz-eyebrow">Risco imediato</span>
                                        <h2>Itens atrasados</h2>
                                    </div>
                                    <a href="<?php echo e($urls['prazos'] ?? '#'); ?>">Corrigir</a>
                                </div>
        
                                <div class="pz-compact-list">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $itensAtrasados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <a href="<?php echo e($item['url'] ?? '#'); ?>" class="pz-compact-row pz-danger-row">
                                            <b class="pz-alert-pill">!</b>
                                            <div>
                                                <strong><?php echo e($item['titulo'] ?? '-'); ?></strong>
                                                <small><?php echo e($item['empresa'] ?? 'Sem empresa'); ?> • venceu <?php echo e($item['tempo'] ?? '-'); ?></small>
                                            </div>
                                        </a>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                        <div class="pz-empty">Nenhum item atrasado.</div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </article>
                        </section>
        
                        <section class="pz-panel">
                            <div class="pz-section-head">
                                <div>
                                    <span class="pz-eyebrow">Empresas / projetos</span>
                                    <h2>Resumo operacional por empresa</h2>
                                </div>
                                <a href="<?php echo e($urls['tarefas'] ?? '#'); ?>">Abrir tarefas</a>
                            </div>
        
                            <div class="pz-company-grid">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $resumoEmpresas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $empresa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <a href="<?php echo e($empresa['url'] ?? '#'); ?>" class="pz-company-card pz-company-<?php echo e($empresa['tone'] ?? 'success'); ?>">
                                        <div class="pz-company-top">
                                            <strong><?php echo e($empresa['empresa'] ?? 'Sem empresa'); ?></strong>
                                            <em><?php echo e($empresa['risco'] ?? 'Saudável'); ?></em>
                                        </div>
                                        <div class="pz-company-progress"><span style="width: <?php echo e(max(0, min(100, (int) ($empresa['progresso'] ?? 0)))); ?>%"></span></div>
                                        <div class="pz-company-metrics">
                                            <span><b><?php echo e($empresa['total'] ?? 0); ?></b> abertos</span>
                                            <span><b><?php echo e($empresa['atrasados'] ?? 0); ?></b> atrasados</span>
                                            <span><b><?php echo e($empresa['vencendo'] ?? 0); ?></b> vencendo</span>
                                        </div>
                                    </a>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    <div class="pz-empty pz-empty-wide">Nenhuma empresa com itens abertos.</div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </section>
                    </main>
        
                    <aside class="pz-right-column">
                        <section class="pz-panel pz-shortcuts-panel">
                            <div class="pz-section-head">
                                <div>
                                    <span class="pz-eyebrow">Acesso rápido</span>
                                    <h2>Atalhos rápidos</h2>
                                </div>
                            </div>
        
                            <div class="pz-shortcuts-grid">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $atalhosRapidos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $atalho): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <a href="<?php echo e($atalho['url'] ?? '#'); ?>" class="pz-shortcut pz-shortcut-<?php echo e($atalho['tone'] ?? 'slate'); ?>">
                                        <span><?php echo e($atalho['icon'] ?? '•'); ?></span>
                                        <div>
                                            <strong><?php echo e($atalho['label'] ?? '-'); ?></strong>
                                            <small><?php echo e($atalho['hint'] ?? ''); ?></small>
                                        </div>
                                    </a>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </div>
                        </section>
        
                        <section class="pz-panel">
                            <div class="pz-section-head">
                                <div>
                                    <span class="pz-eyebrow">Decisões</span>
                                    <h2>Aprovações aguardando</h2>
                                </div>
                                <a href="<?php echo e($urls['centralAprovacoes'] ?? '#'); ?>">Central</a>
                            </div>
        
                            <div class="pz-approval-list">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $aprovacoesAguardando; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $aprovacao): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <a href="<?php echo e($aprovacao['url'] ?? '#'); ?>" class="pz-approval-row">
                                        <span>☑</span>
                                        <div>
                                            <strong><?php echo e($aprovacao['titulo'] ?? '-'); ?></strong>
                                            <small><?php echo e($aprovacao['empresa'] ?? 'Sem empresa'); ?> • <?php echo e($aprovacao['tempo'] ?? '-'); ?></small>
                                        </div>
                                    </a>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    <div class="pz-empty">Nenhuma aprovação aguardando.</div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </section>
        
                        <section class="pz-panel">
                            <div class="pz-section-head">
                                <div>
                                    <span class="pz-eyebrow">Conversas recentes</span>
                                    <h2>Últimos comentários</h2>
                                </div>
                            </div>
        
                            <div class="pz-comment-list">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $ultimosComentarios; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comentario): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <a href="<?php echo e($comentario['url'] ?? '#'); ?>" class="pz-comment-row">
                                        <span><?php echo e(strtoupper(mb_substr($comentario['usuario'] ?? 'U', 0, 1))); ?></span>
                                        <div>
                                            <strong><?php echo e($comentario['titulo'] ?? '-'); ?></strong>
                                            <p><?php echo e($comentario['comentario'] ?? '-'); ?></p>
                                            <small><?php echo e($comentario['empresa'] ?? 'Operação'); ?> • <?php echo e($comentario['quando'] ?? '-'); ?></small>
                                        </div>
                                    </a>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    <div class="pz-empty">Nenhum comentário recente.</div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </section>
        
                        <section class="pz-panel">
                            <div class="pz-section-head">
                                <div>
                                    <span class="pz-eyebrow">Fluxo</span>
                                    <h2>Saúde da operação</h2>
                                </div>
                                <a href="<?php echo e($urls['kanban'] ?? '#'); ?>">Kanban</a>
                            </div>
        
                            <div class="pz-flow-list">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $fluxoOperacional; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $etapa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <div class="pz-flow-row pz-flow-<?php echo e($etapa['tone'] ?? 'slate'); ?>">
                                        <span><?php echo e($etapa['label'] ?? '-'); ?></span>
                                        <strong><?php echo e($etapa['value'] ?? 0); ?></strong>
                                    </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    <div class="pz-empty">Sem dados do fluxo operacional.</div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </section>
        
                        <section class="pz-panel pz-finance-panel">
                            <div class="pz-section-head">
                                <div>
                                    <span class="pz-eyebrow">Financeiro</span>
                                    <h2>Resumo do mês</h2>
                                </div>
                                <a href="<?php echo e($urls['financeiro'] ?? '#'); ?>">Abrir</a>
                            </div>
        
                            <div class="pz-finance-total">R$ <?php echo e(number_format($financeiro['total'] ?? 0, 2, ',', '.')); ?></div>
                            <div class="pz-finance-split">
                                <span>Recebido <b>R$ <?php echo e(number_format($financeiro['recebido'] ?? 0, 2, ',', '.')); ?></b></span>
                                <span>A receber <b>R$ <?php echo e(number_format($financeiro['aReceber'] ?? 0, 2, ',', '.')); ?></b></span>
                            </div>
                        </section>
                    </aside>
                </section>
            </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const storageKey = 'prazzu.home.layout';
            const classic = document.querySelector('[data-home-layout="classic"]');
            const operational = document.querySelector('[data-home-layout="operational"]');
            const button = document.querySelector('[data-home-layout-toggle]');
            const title = document.querySelector('[data-home-layout-title]');
            const description = document.querySelector('[data-home-layout-description]');

            if (!classic || !operational || !button) {
                return;
            }

            function applyLayout(layout) {
                const isOperational = layout === 'operational';

                classic.classList.toggle('is-hidden', isOperational);
                operational.classList.toggle('is-hidden', !isOperational);

                if (title) {
                    title.textContent = isOperational ? 'Home operacional' : 'Home clássica';
                }

                if (description) {
                    description.textContent = isOperational
                        ? 'Modo ClickUp com pendências, vencimentos, aprovações, comentários e atalhos.'
                        : 'Você está vendo a Home antiga, com todos os indicadores originais preservados.';
                }

                button.textContent = isOperational ? 'Voltar para Home clássica' : 'Ver Home operacional';
                localStorage.setItem(storageKey, isOperational ? 'operational' : 'classic');
            }

            applyLayout(localStorage.getItem(storageKey) === 'operational' ? 'operational' : 'classic');

            button.addEventListener('click', function () {
                const current = localStorage.getItem(storageKey) === 'operational' ? 'operational' : 'classic';
                applyLayout(current === 'operational' ? 'classic' : 'operational');
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
<?php endif; ?><?php /**PATH C:\xampp\htdocs\prazzu\resources\views/filament/pages/home.blade.php ENDPATH**/ ?>