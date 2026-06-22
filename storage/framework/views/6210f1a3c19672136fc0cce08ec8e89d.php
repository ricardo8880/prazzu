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

    <link rel="stylesheet" href="<?php echo e(asset('css/tarefas-qa-standard.css')); ?>?v=20260513-lote7-visual">
    <link rel="stylesheet" href="<?php echo e(asset('css/indicadores-conta.css')); ?>?v=<?php echo e(file_exists(public_path('css/indicadores-conta.css')) ? filemtime(public_path('css/indicadores-conta.css')) : time()); ?>">

    <div class="account-indicators-page prazzu-docs-page">
        <section class="account-indicators-hero prazzu-hero">
            <div>
                <span class="account-indicators-kicker">Painel gerencial</span>
                <h1>Indicadores da Conta</h1>
                <p>Uma visão simples para sócios e gestores acompanharem o tamanho da operação, a equipe ativa, o volume de documentos e o uso atual da conta.</p>
            </div>

            <div class="account-indicators-hero-card">
                <span>Atualizado em</span>
                <strong><?php echo e($summary['updated_at'] ?? now()->format('d/m/Y H:i')); ?></strong>
                <p><?php echo e($summary['scope'] ?? 'Visão da conta'); ?></p>
                <small><?php echo e($summary['cache_hint'] ?? 'Atualização automática'); ?></small>
            </div>
        </section>

        <section class="account-indicators-cards" aria-label="Indicadores principais da conta">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $cards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <article class="account-indicators-card <?php echo e($card['tone']); ?>">
                    <div class="account-indicators-card-icon">
                        <i class="bi <?php echo e($card['icon']); ?>"></i>
                    </div>
                    <div>
                        <span><?php echo e($card['label']); ?></span>
                        <strong><?php echo e($card['value']); ?></strong>
                        <p><?php echo e($card['description']); ?></p>
                    </div>
                </article>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </section>

        <section class="account-indicators-usage account-indicators-panel prazzu-card" aria-label="Uso do ambiente">
            <div class="account-indicators-panel-header">
                <div>
                    <span class="account-indicators-kicker prazzu-kicker">Uso do ambiente</span>
                    <h2>Armazenamento e banco</h2>
                </div>
                <strong><?php echo e($usage['storage_percent'] === null ? 'Local' : number_format((float) $usage['storage_percent'], 1, ',', '.') . '%'); ?></strong>
            </div>

            <div class="account-indicators-progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?php echo e($usage['storage_percent'] ?? 0); ?>">
                <span style="width: <?php echo e($usage['storage_bar_width'] ?? 0); ?>%"></span>
            </div>

            <div class="account-indicators-usage-grid">
                <div>
                    <span>Espaço utilizado</span>
                    <strong><?php echo e($usage['storage_used'] ?? '0 B'); ?></strong>
                    <p>Limite: <?php echo e($usage['storage_limit'] ?? 'Sem limite configurado'); ?></p>
                </div>
                <div>
                    <span>Banco de dados</span>
                    <strong><?php echo e($usage['database_size'] ?? 'Indisponível'); ?></strong>
                    <p>Volume atual utilizado pela conta</p>
                </div>
            </div>
        </section>

        <section class="account-indicators-grid">
            <article class="account-indicators-panel prazzu-card">
                <div class="account-indicators-panel-header">
                    <div>
                        <span class="account-indicators-kicker">Resumo da conta</span>
                        <h2>Visão rápida da operação</h2>
                    </div>
                </div>

                <div class="account-indicators-list">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $summaryItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="account-indicators-row <?php echo e($item['tone']); ?>">
                            <span></span>
                            <div>
                                <strong><?php echo e($item['title']); ?></strong>
                                <p><?php echo e($item['description']); ?></p>
                            </div>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            </article>

            <aside class="account-indicators-panel account-indicators-panel-muted prazzu-card">
                <span class="account-indicators-kicker">Atenção do gestor</span>
                <h2>O que observar</h2>

                <div class="account-indicators-list">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $managerNotes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $note): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="account-indicators-row <?php echo e($note['tone']); ?>">
                            <span></span>
                            <div>
                                <strong><?php echo e($note['title']); ?></strong>
                                <p><?php echo e($note['description']); ?></p>
                            </div>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            </aside>
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
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views/filament/pages/indicadores-conta.blade.php ENDPATH**/ ?>