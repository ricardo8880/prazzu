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

    <div class="prazzu-shortcuts-page">
        <section class="prazzu-shortcuts-hero" aria-labelledby="prazzu-shortcuts-title">
            <div class="prazzu-shortcuts-hero__content">
                <div class="prazzu-shortcuts-eyebrow">
                    <i class="bi bi-star-fill" aria-hidden="true"></i>
                    <span>Favoritos da sidebar</span>
                </div>

                <div>
                    <h2 id="prazzu-shortcuts-title">Deixe o menu com a sua rotina na frente</h2>
                    <p>
                        Escolha as páginas que você mais usa e organize a ordem dos favoritos. Eles aparecem no topo da coluna lateral
                        para reduzir cliques e acelerar o atendimento diário.
                    </p>
                </div>
            </div>

            <div class="prazzu-shortcuts-metrics" aria-label="Resumo dos atalhos">
                <div class="prazzu-shortcuts-metric">
                    <strong><?php echo e($this->favoriteCount()); ?></strong>
                    <span>atalhos ativos</span>
                </div>

                <div class="prazzu-shortcuts-metric">
                    <strong><?php echo e($this->availableCount()); ?></strong>
                    <span>páginas disponíveis</span>
                </div>
            </div>
        </section>

        <div class="prazzu-shortcuts-layout">
            <main class="prazzu-shortcuts-main">
                <form wire:submit.prevent="salvar" class="prazzu-shortcuts-card">
                    <div class="prazzu-shortcuts-card__header">
                        <div>
                            <h3>Seus atalhos favoritos</h3>
                            <p>Adicione, remova e reorganize sem preencher posição manualmente.</p>
                        </div>

                        <div class="prazzu-shortcuts-order-badge">
                            <i class="bi bi-grip-vertical" aria-hidden="true"></i>
                            <span>arraste ou use as setas</span>
                        </div>
                    </div>

                    <div class="prazzu-shortcuts-form">
                        <?php echo e($this->form); ?>

                    </div>

                    <div class="prazzu-shortcuts-actions">
                        <?php if (isset($component)) { $__componentOriginal6330f08526bbb3ce2a0da37da512a11f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['type' => 'submit','icon' => 'heroicon-o-check','size' => 'lg']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit','icon' => 'heroicon-o-check','size' => 'lg']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                            Salvar atalhos
                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6330f08526bbb3ce2a0da37da512a11f)): ?>
<?php $attributes = $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f; ?>
<?php unset($__attributesOriginal6330f08526bbb3ce2a0da37da512a11f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6330f08526bbb3ce2a0da37da512a11f)): ?>
<?php $component = $__componentOriginal6330f08526bbb3ce2a0da37da512a11f; ?>
<?php unset($__componentOriginal6330f08526bbb3ce2a0da37da512a11f); ?>
<?php endif; ?>
                    </div>
                </form>
            </main>

            <aside class="prazzu-shortcuts-preview" aria-label="Prévia da sidebar">
                <div class="prazzu-shortcuts-preview__header">
                    <div>
                        <h3>Prévia da sidebar</h3>
                        <p>Veja como a lista ficará no menu.</p>
                    </div>

                    <span class="prazzu-shortcuts-preview__icon" aria-hidden="true">
                        <i class="bi bi-star-fill"></i>
                    </span>
                </div>

                <div class="prazzu-sidebar-mock">
                    <div class="prazzu-sidebar-mock__group">
                        <div class="prazzu-sidebar-mock__title">
                            <i class="bi bi-star-fill" aria-hidden="true"></i>
                            <span>Favoritos</span>
                        </div>

                        <div class="prazzu-sidebar-mock__items">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $this->favoritePreviewItems(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <div class="prazzu-sidebar-mock__item is-favorite">
                                    <span class="prazzu-sidebar-mock__number"><?php echo e($index + 1); ?></span>
                                    <span class="prazzu-sidebar-mock__text">
                                        <strong><?php echo e($item['label']); ?></strong>
                                        <small><?php echo e($item['group']); ?></small>
                                    </span>
                                </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <div class="prazzu-sidebar-mock__empty">
                                    <i class="bi bi-stars" aria-hidden="true"></i>
                                    <span>Adicione suas páginas principais para montar a prévia.</span>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>

                    <div class="prazzu-sidebar-mock__group is-muted">
                        <div class="prazzu-sidebar-mock__title">Escritório Contábil / Contabilidade</div>
                        <div class="prazzu-sidebar-mock__items">
                            <div class="prazzu-sidebar-mock__item">Home</div>
                            <div class="prazzu-sidebar-mock__item">Pendências</div>
                            <div class="prazzu-sidebar-mock__item">Clientes</div>
                            <div class="prazzu-sidebar-mock__item">Financeiro</div>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>

    <style>
        .prazzu-shortcuts-page,
        .prazzu-shortcuts-page * {
            box-sizing: border-box;
        }

        .prazzu-shortcuts-page {
            display: grid;
            gap: 1.25rem;
        }

        .prazzu-shortcuts-hero,
        .prazzu-shortcuts-card,
        .prazzu-shortcuts-preview {
            border: 1px solid rgba(226, 232, 240, .9);
            border-radius: 28px;
            background: #ffffff;
            box-shadow: 0 18px 50px rgba(15, 23, 42, .07);
        }

        .prazzu-shortcuts-hero {
            position: relative;
            overflow: hidden;
            display: flex;
            justify-content: space-between;
            gap: 1.5rem;
            padding: 1.75rem;
        }

        .prazzu-shortcuts-hero::before {
            content: '';
            position: absolute;
            inset: 0 0 auto;
            height: 5px;
            background: linear-gradient(90deg, rgb(var(--primary-500)), #f59e0b, rgb(var(--primary-300)));
        }

        .prazzu-shortcuts-hero__content {
            display: grid;
            gap: .85rem;
            max-width: 780px;
        }

        .prazzu-shortcuts-eyebrow,
        .prazzu-shortcuts-order-badge {
            display: inline-flex;
            width: fit-content;
            align-items: center;
            gap: .5rem;
            border-radius: 999px;
            font-size: .75rem;
            font-weight: 800;
        }

        .prazzu-shortcuts-eyebrow {
            background: rgba(var(--primary-500), .10);
            color: rgb(var(--primary-700));
            padding: .45rem .75rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            box-shadow: inset 0 0 0 1px rgba(var(--primary-600), .12);
        }

        .prazzu-shortcuts-eyebrow i,
        .prazzu-shortcuts-preview__icon i,
        .prazzu-sidebar-mock__title i,
        .prazzu-sidebar-mock__empty i {
            color: #f59e0b;
        }

        .prazzu-shortcuts-hero h2,
        .prazzu-shortcuts-card h3,
        .prazzu-shortcuts-preview h3 {
            margin: 0;
            color: #0f172a;
            font-weight: 900;
            letter-spacing: -.03em;
        }

        .prazzu-shortcuts-hero h2 {
            font-size: clamp(1.55rem, 2.4vw, 2.35rem);
            line-height: 1.05;
        }

        .prazzu-shortcuts-hero p,
        .prazzu-shortcuts-card p,
        .prazzu-shortcuts-preview p {
            margin: .45rem 0 0;
            color: #64748b;
            line-height: 1.65;
        }

        .prazzu-shortcuts-metrics {
            display: grid;
            grid-template-columns: repeat(2, minmax(120px, 1fr));
            gap: .75rem;
            align-self: start;
            min-width: 280px;
        }

        .prazzu-shortcuts-metric {
            border-radius: 22px;
            background: #f8fafc;
            padding: 1rem;
            text-align: center;
            box-shadow: inset 0 0 0 1px rgba(15, 23, 42, .05);
        }

        .prazzu-shortcuts-metric strong {
            display: block;
            color: #0f172a;
            font-size: 1.75rem;
            font-weight: 900;
            line-height: 1;
        }

        .prazzu-shortcuts-metric span {
            display: block;
            margin-top: .35rem;
            color: #64748b;
            font-size: .78rem;
            font-weight: 700;
        }

        .prazzu-shortcuts-layout {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 380px;
            gap: 1.25rem;
            align-items: start;
        }

        .prazzu-shortcuts-card {
            padding: 1.25rem;
        }

        .prazzu-shortcuts-card__header,
        .prazzu-shortcuts-preview__header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .prazzu-shortcuts-order-badge {
            flex: none;
            background: #f8fafc;
            color: #475569;
            padding: .45rem .75rem;
            box-shadow: inset 0 0 0 1px rgba(15, 23, 42, .06);
        }

        .prazzu-shortcuts-form {
            border-radius: 22px;
            background: #f8fafc;
            padding: 1rem;
            box-shadow: inset 0 0 0 1px rgba(15, 23, 42, .05);
        }

        .prazzu-shortcuts-form .fi-section,
        .prazzu-shortcuts-form .fi-section-content,
        .prazzu-shortcuts-form .fi-fo-repeater,
        .prazzu-shortcuts-form .fi-fo-repeater-item {
            background: transparent;
            box-shadow: none;
        }

        .prazzu-shortcuts-form .fi-section {
            border: 0;
        }

        .prazzu-shortcuts-form .fi-section-header {
            display: none;
        }

        .prazzu-shortcuts-form .fi-fo-repeater-item {
            overflow: hidden;
            border: 1px solid rgba(203, 213, 225, .85);
            border-radius: 18px;
            background: #ffffff;
        }

        .prazzu-shortcuts-form .fi-fo-repeater-item-header {
            background: #ffffff;
            border-bottom: 1px solid rgba(226, 232, 240, .95);
        }

        .prazzu-shortcuts-form .fi-fo-repeater-item-content {
            padding: 1rem;
        }

        .prazzu-shortcuts-actions {
            display: flex;
            justify-content: flex-end;
            padding-top: 1rem;
        }

        .prazzu-shortcuts-preview {
            position: sticky;
            top: 1.5rem;
            overflow: hidden;
        }

        .prazzu-shortcuts-preview__header {
            margin: 0;
            padding: 1.25rem;
            border-bottom: 1px solid rgba(226, 232, 240, .9);
        }

        .prazzu-shortcuts-preview__icon {
            display: grid;
            width: 44px;
            height: 44px;
            flex: none;
            place-items: center;
            border-radius: 16px;
            background: #fffbeb;
            box-shadow: inset 0 0 0 1px rgba(245, 158, 11, .18);
        }

        .prazzu-sidebar-mock {
            display: grid;
            gap: 1.15rem;
            padding: 1.25rem;
        }

        .prazzu-sidebar-mock__group {
            display: grid;
            gap: .7rem;
        }

        .prazzu-sidebar-mock__title {
            display: flex;
            align-items: center;
            gap: .45rem;
            color: #64748b;
            font-size: .72rem;
            font-weight: 900;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .prazzu-sidebar-mock__items {
            display: grid;
            gap: .5rem;
        }

        .prazzu-sidebar-mock__item {
            display: flex;
            align-items: center;
            gap: .75rem;
            min-width: 0;
            border-radius: 16px;
            padding: .75rem;
            color: #475569;
        }

        .prazzu-sidebar-mock__item.is-favorite {
            background: linear-gradient(180deg, #ffffff, #f8fafc);
            box-shadow: inset 0 0 0 1px rgba(226, 232, 240, .95), 0 8px 20px rgba(15, 23, 42, .04);
        }

        .prazzu-sidebar-mock__number {
            display: grid;
            width: 34px;
            height: 34px;
            flex: none;
            place-items: center;
            border-radius: 12px;
            background: #eef2ff;
            color: rgb(var(--primary-700));
            font-size: .8rem;
            font-weight: 900;
        }

        .prazzu-sidebar-mock__text {
            min-width: 0;
        }

        .prazzu-sidebar-mock__text strong,
        .prazzu-sidebar-mock__text small {
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .prazzu-sidebar-mock__text strong {
            color: #0f172a;
            font-size: .9rem;
            font-weight: 850;
        }

        .prazzu-sidebar-mock__text small {
            margin-top: .15rem;
            color: #64748b;
            font-size: .72rem;
        }

        .prazzu-sidebar-mock__empty {
            display: grid;
            gap: .5rem;
            justify-items: center;
            border: 1px dashed #cbd5e1;
            border-radius: 18px;
            background: #f8fafc;
            padding: 1.25rem;
            color: #64748b;
            text-align: center;
            font-size: .9rem;
        }

        .prazzu-sidebar-mock__group.is-muted .prazzu-sidebar-mock__item {
            background: transparent;
            box-shadow: none;
            font-size: .9rem;
        }

        .dark .prazzu-shortcuts-hero,
        .dark .prazzu-shortcuts-card,
        .dark .prazzu-shortcuts-preview {
            border-color: rgba(51, 65, 85, .9);
            background: #111827;
            box-shadow: 0 18px 50px rgba(0, 0, 0, .20);
        }

        .dark .prazzu-shortcuts-hero h2,
        .dark .prazzu-shortcuts-card h3,
        .dark .prazzu-shortcuts-preview h3,
        .dark .prazzu-shortcuts-metric strong,
        .dark .prazzu-sidebar-mock__text strong {
            color: #f8fafc;
        }

        .dark .prazzu-shortcuts-hero p,
        .dark .prazzu-shortcuts-card p,
        .dark .prazzu-shortcuts-preview p,
        .dark .prazzu-shortcuts-metric span,
        .dark .prazzu-sidebar-mock__title,
        .dark .prazzu-sidebar-mock__text small,
        .dark .prazzu-sidebar-mock__item,
        .dark .prazzu-sidebar-mock__empty {
            color: #cbd5e1;
        }

        .dark .prazzu-shortcuts-metric,
        .dark .prazzu-shortcuts-order-badge,
        .dark .prazzu-shortcuts-form,
        .dark .prazzu-sidebar-mock__empty {
            background: rgba(255, 255, 255, .045);
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .06);
        }

        .dark .prazzu-shortcuts-form .fi-fo-repeater-item,
        .dark .prazzu-shortcuts-form .fi-fo-repeater-item-header,
        .dark .prazzu-sidebar-mock__item.is-favorite {
            border-color: rgba(51, 65, 85, .95);
            background: rgba(255, 255, 255, .04);
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, .05);
        }

        .dark .prazzu-shortcuts-preview__header {
            border-color: rgba(51, 65, 85, .95);
        }

        @media (max-width: 1180px) {
            .prazzu-shortcuts-layout {
                grid-template-columns: 1fr;
            }

            .prazzu-shortcuts-preview {
                position: static;
            }
        }

        @media (max-width: 760px) {
            .prazzu-shortcuts-hero,
            .prazzu-shortcuts-card__header,
            .prazzu-shortcuts-preview__header {
                display: grid;
            }

            .prazzu-shortcuts-metrics {
                grid-template-columns: 1fr 1fr;
                min-width: 0;
                width: 100%;
            }

            .prazzu-shortcuts-card,
            .prazzu-shortcuts-hero,
            .prazzu-shortcuts-preview__header,
            .prazzu-sidebar-mock {
                padding: 1rem;
            }

            .prazzu-shortcuts-form {
                padding: .75rem;
            }
        }
    </style>
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
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views/filament/pages/meus-atalhos.blade.php ENDPATH**/ ?>