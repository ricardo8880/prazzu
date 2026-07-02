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

    <?php
        $report = $this->report ?? [];
        $summary = $report['summary'] ?? ['ok' => 0, 'warning' => 0, 'error' => 0, 'total' => 0];
        $status = $report['status'] ?? 'unknown';
        $statusLabel = match ($status) {
            'healthy' => 'Saudável',
            'attention' => 'Atenção',
            'critical' => 'Crítico',
            default => 'Não executado',
        };
        $statusClass = match ($status) {
            'healthy' => 'shd-badge--ok',
            'attention' => 'shd-badge--warning',
            'critical' => 'shd-badge--error',
            default => 'shd-badge--neutral',
        };
        $statusClasses = [
            'ok' => 'shd-badge--ok',
            'warning' => 'shd-badge--warning',
            'error' => 'shd-badge--error',
        ];
        $labels = ['ok' => 'OK', 'warning' => 'Avisos', 'error' => 'Erros'];
    ?>

    <div class="shd-panel">
        <section class="shd-hero">
            <div class="shd-hero__content">
                <div class="shd-hero__text">
                    <div class="shd-hero__meta">
                        <span class="shd-badge <?php echo e($statusClass); ?>"><?php echo e($statusLabel); ?></span>
                        <span class="shd-muted">Última verificação: <?php echo e($report['generated_at_human'] ?? 'Ainda não executado'); ?></span>
                    </div>

                    <div>
                        <h2 class="shd-title">Centro de saúde operacional do SistemRH / Prazzu</h2>
                        <p class="shd-description">
                            Monitore ambiente, banco, portal público, financeiro, uploads, comandos, logs e arquivos críticos em uma única tela. O painel é não destrutivo: apenas lê dados e aponta riscos.
                        </p>
                    </div>
                </div>

                <div class="shd-actions">
                    <label class="shd-limit">
                        <span class="shd-limit__label">Limite</span>
                        <input
                            type="number"
                            min="10"
                            max="5000"
                            step="10"
                            wire:model="limit"
                            class="shd-input"
                        />
                    </label>

                    <button
                        type="button"
                        wire:click="runHealthCheck"
                        wire:loading.attr="disabled"
                        class="shd-button shd-button--primary"
                    >
                        <span wire:loading.remove wire:target="runHealthCheck">Executar diagnóstico agora</span>
                        <span wire:loading wire:target="runHealthCheck">Executando...</span>
                    </button>

                    <button
                        type="button"
                        wire:click="exportJson"
                        class="shd-button shd-button--dark"
                    >
                        Exportar JSON
                    </button>
                </div>
            </div>
        </section>

        <section class="shd-summary-grid">
            <div class="shd-card shd-card--summary">
                <p class="shd-card__label">Status geral</p>
                <p class="shd-card__value"><?php echo e($statusLabel); ?></p>
                <p class="shd-card__hint">Duração: <?php echo e($report['duration_ms'] ?? 0); ?>ms</p>
            </div>

            <div class="shd-card shd-card--summary">
                <p class="shd-card__label">OK</p>
                <p class="shd-card__value shd-text-ok"><?php echo e($summary['ok'] ?? 0); ?></p>
                <p class="shd-card__hint">Checks saudáveis</p>
            </div>

            <div class="shd-card shd-card--summary">
                <p class="shd-card__label">Avisos</p>
                <p class="shd-card__value shd-text-warning"><?php echo e($summary['warning'] ?? 0); ?></p>
                <p class="shd-card__hint">Ajustes recomendados</p>
            </div>

            <div class="shd-card shd-card--summary">
                <p class="shd-card__label">Erros</p>
                <p class="shd-card__value shd-text-error"><?php echo e($summary['error'] ?? 0); ?></p>
                <p class="shd-card__hint">Correção prioritária</p>
            </div>
        </section>

        <section class="shd-section-grid">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($report['sections'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php
                    $sectionStatus = $section['status'] ?? 'warning';
                    $sectionClass = $statusClasses[$sectionStatus] ?? $statusClasses['warning'];
                ?>

                <article class="shd-section-card">
                    <header class="shd-section-card__header">
                        <div class="shd-section-card__heading">
                            <span class="shd-section-icon <?php echo e($sectionClass); ?>"><?php echo e(strtoupper(substr($section['name'] ?? 'S', 0, 1))); ?></span>
                            <div>
                                <h3 class="shd-section-title"><?php echo e($section['name'] ?? 'Seção'); ?></h3>
                                <p class="shd-section-description"><?php echo e($section['description'] ?? ''); ?></p>
                            </div>
                        </div>

                        <div class="shd-section-counts">
                            <span class="shd-pill shd-pill--ok"><?php echo e($section['summary']['ok'] ?? 0); ?> OK</span>
                            <span class="shd-pill shd-pill--warning"><?php echo e($section['summary']['warning'] ?? 0); ?> Avisos</span>
                            <span class="shd-pill shd-pill--error"><?php echo e($section['summary']['error'] ?? 0); ?> Erros</span>
                        </div>
                    </header>

                    <div class="shd-check-list">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($section['items'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <?php
                                $itemStatus = $item['status'] ?? 'warning';
                                $itemClass = $statusClasses[$itemStatus] ?? $statusClasses['warning'];
                                $itemLabel = $labels[$itemStatus] ?? 'Aviso';
                            ?>
                            <div class="shd-check-item">
                                <div class="shd-check-item__layout">
                                    <span class="shd-check-status <?php echo e($itemClass); ?>"><?php echo e($itemLabel); ?></span>
                                    <div class="shd-check-item__content">
                                        <p class="shd-check-title"><?php echo e($item['title'] ?? 'Verificação'); ?></p>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($item['detail'])): ?>
                                            <p class="shd-check-detail"><?php echo e($item['detail']); ?></p>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($item['action'])): ?>
                                            <p class="shd-action-note">Ação recomendada: <?php echo e($item['action']); ?></p>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($item['context'])): ?>
                                            <details class="shd-details">
                                                <summary class="shd-details__summary">Ver contexto técnico</summary>
                                                <pre class="shd-code-block"><?php echo e(json_encode($item['context'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)); ?></pre>
                                            </details>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>
                </article>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <div class="shd-empty">
                    <h3 class="shd-empty__title">Nenhum diagnóstico executado ainda.</h3>
                    <p class="shd-empty__text">Clique em “Executar diagnóstico agora” para gerar o primeiro relatório.</p>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views\filament\pages\system-health-dashboard.blade.php ENDPATH**/ ?>