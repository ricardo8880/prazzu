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

    <?php if (! $__env->hasRenderedOnce('8d95b383-6ce6-432f-8cc9-02725dd9589c')): $__env->markAsRenderedOnce('8d95b383-6ce6-432f-8cc9-02725dd9589c'); ?>
        <link rel="stylesheet" href="<?php echo e(asset('css/dashboard-executivo-contabil.css')); ?>?v=<?php echo e(file_exists(public_path('css/dashboard-executivo-contabil.css')) ? filemtime(public_path('css/dashboard-executivo-contabil.css')) : time()); ?>">
    <?php endif; ?>

    <?php
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
        $updatedAt = $dashboard['updated_at'] ?? now()->format('d/m/Y H:i');

        $riskScore = max(0, min(100, (int) ($risk['score'] ?? 0)));
    ?>

    <div class="dec-cockpit" data-executive-accounting-dashboard>
        <section class="dec-hero dec-tone-<?php echo e($top['tone'] ?? ($risk['tone'] ?? 'info')); ?>" aria-label="Estado executivo do escritório contábil">
            <div class="dec-hero__content">
                <span class="dec-eyebrow"><?php echo e($top['eyebrow'] ?? 'Cockpit Executivo Contábil'); ?></span>
                <h1><?php echo e($risk['label'] ?? 'Dashboard Executivo Contábil'); ?></h1>
                <p><?php echo e($top['summary'] ?? ($risk['headline'] ?? 'Veja somente o que exige decisão executiva hoje.')); ?></p>

                <div class="dec-hero__meta">
                    <span><?php echo e((int) ($risk['count'] ?? 0)); ?> ponto(s) críticos consolidados</span>
                    <span>Atualizado em <?php echo e($updatedAt); ?></span>
                </div>
            </div>

            <aside class="dec-control" aria-label="Índice de controle operacional">
                <div class="dec-control__head">
                    <span><?php echo e($top['badge'] ?? 'Controle operacional'); ?></span>
                    <strong><?php echo e($riskScore); ?>%</strong>
                </div>
                <div class="dec-control__bar" aria-hidden="true">
                    <i style="width: <?php echo e($riskScore); ?>%"></i>
                </div>
                <p><?php echo e($risk['headline'] ?? 'Índice calculado a partir de obrigações, SLA, documentos e inadimplência com impacto.'); ?></p>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($top['primary_url'])): ?>
                    <a class="dec-primary" href="<?php echo e($top['primary_url']); ?>"><?php echo e($top['primary_action'] ?? 'Abrir origem'); ?></a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </aside>
        </section>

        <section class="dec-decision-grid" aria-label="Quatro decisões executivas">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $decisionCards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <a
                    class="dec-decision dec-tone-<?php echo e($card['tone'] ?? 'info'); ?> <?php echo e(empty($card['url']) ? 'dec-decision--static' : ''); ?>"
                    href="<?php echo e($card['url'] ?: '#'); ?>"
                    <?php if(empty($card['url'])): ?> aria-disabled="true" onclick="return false" <?php endif; ?>
                >
                    <span class="dec-decision__icon" aria-hidden="true"><?php echo e($card['icon'] ?? '•'); ?></span>
                    <span class="dec-decision__label"><?php echo e($card['label'] ?? 'Indicador executivo'); ?></span>
                    <strong><?php echo e($card['value'] ?? '0'); ?></strong>
                    <p><?php echo e($card['hint'] ?? 'Indicador consolidado das telas de origem.'); ?></p>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($card['url'])): ?>
                        <em><?php echo e($card['action_label'] ?? 'Abrir origem'); ?> · <?php echo e($card['source_label'] ?? 'aba de origem'); ?></em>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </a>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <article class="dec-empty dec-empty--wide">
                    <strong>Nenhuma decisão crítica encontrada.</strong>
                    <p>Quando houver dados, esta área mostrará apenas riscos executivos, não métricas genéricas.</p>
                </article>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </section>

        <section class="dec-main-grid" aria-label="Filas executivas acionáveis">
            <article class="dec-panel dec-panel--primary">
                <header class="dec-panel__header">
                    <div>
                        <span class="dec-eyebrow">Resolver primeiro</span>
                        <h2>Resolver agora</h2>
                        <p>No máximo 5 itens que podem gerar multa, atraso, quebra de SLA ou desgaste com cliente.</p>
                    </div>
                    <strong><?php echo e(count($resolveNow)); ?></strong>
                </header>

                <div class="dec-action-list">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $resolveNow; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <article class="dec-action dec-tone-<?php echo e($item['tone'] ?? 'info'); ?>">
                            <div class="dec-action__top">
                                <div>
                                    <h3><?php echo e($item['title'] ?? 'Item crítico'); ?></h3>
                                    <small><?php echo e($item['meta'] ?? 'Origem operacional'); ?></small>
                                </div>
                                <span><?php echo e($item['status'] ?? 'Ação'); ?></span>
                            </div>
                            <p><?php echo e($item['description'] ?? 'Abra a origem para executar sem duplicar gestão nesta tela.'); ?></p>
                            <div class="dec-action__footer">
                                <em><?php echo e(! empty($item['deadline']) ? 'Prazo ' . $item['deadline'] : 'Ação executiva'); ?></em>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($item['url'])): ?>
                                    <a href="<?php echo e($item['url']); ?>"><?php echo e($item['action_label'] ?? 'Abrir origem'); ?></a>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </article>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="dec-empty">
                            <strong>Nada crítico para resolver agora.</strong>
                            <p>Esta fila não lista tarefas comuns. Ela só aparece quando existe risco real de impacto.</p>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
                        <strong><?php echo e(count($blockers)); ?></strong>
                    </header>

                    <div class="dec-blocker-list">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $blockers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <article class="dec-blocker dec-tone-<?php echo e($item['tone'] ?? 'warning'); ?>">
                                <div>
                                    <h3><?php echo e($item['title'] ?? 'Bloqueio operacional'); ?></h3>
                                    <small><?php echo e($item['meta'] ?? ($item['status'] ?? 'Bloqueio')); ?></small>
                                </div>
                                <p><?php echo e($item['description'] ?? 'Bloqueio identificado a partir das telas de origem.'); ?></p>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($item['url'])): ?>
                                    <a href="<?php echo e($item['url']); ?>"><?php echo e($item['action_label'] ?? 'Abrir origem'); ?></a>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </article>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <div class="dec-empty dec-empty--compact">
                                <strong>Nenhum bloqueio relevante.</strong>
                                <p>Documentos e pendências comuns continuam nas abas próprias.</p>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </article>

                <article class="dec-trend dec-tone-<?php echo e($trend['tone'] ?? 'info'); ?>">
                    <span class="dec-eyebrow">Tendência executiva</span>
                    <div class="dec-trend__metric">
                        <strong><?php echo e($trend['value'] ?? ($riskScore . '%')); ?></strong>
                        <span><?php echo e($trend['label'] ?? 'Risco operacional'); ?></span>
                    </div>
                    <p><?php echo e($trend['description'] ?? 'Leitura resumida para saber se a rotina crítica está melhorando ou piorando.'); ?></p>
                    <div class="dec-origin-note">Dados consolidados de SLA, Centro Operacional, Documentos e Cobranças.</div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($trend['evidence'])): ?>
                        <small><?php echo e($trend['evidence']); ?></small>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </article>
            </aside>
        </section>

        <section class="dec-footer-note" aria-label="Critério da dashboard">
            <strong>Critério desta tela:</strong>
            <span>não substituir Clientes, Financeiro, Documentos, SLA ou Centro Operacional; apenas apontar decisões executivas que não podem esperar.</span>
        </section>
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
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views/filament/pages/dashboard-executivo-contabil.blade.php ENDPATH**/ ?>