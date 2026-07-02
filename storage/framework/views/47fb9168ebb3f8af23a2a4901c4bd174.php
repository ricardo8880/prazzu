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
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views\filament\pages\meus-atalhos.blade.php ENDPATH**/ ?>