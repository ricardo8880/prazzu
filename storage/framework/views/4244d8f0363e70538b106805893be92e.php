<?php
    $row = $row ?? [];
    $actions = collect($row['actions'] ?? [])->filter(fn ($action) => filled($action['url'] ?? null))->values();
    $detailCards = $row['detailCards'] ?? [];
?>

<div class="compliance-interno-filament-modal">
    <div class="compliance-interno-detail-flow">
        <article>
            <span>Status atual</span>
            <strong><?php echo e($row['status'] ?? 'Indisponível'); ?></strong>
            <small><?php echo e($row['nextStep'] ?? 'Verifique o registro para definir a próxima ação.'); ?></small>
        </article>

        <article class="compliance-detail-urgency <?php echo e($row['urgencyTone'] ?? 'info'); ?>">
            <span>Urgência</span>
            <strong><?php echo e($row['urgencyLabel'] ?? 'Acompanhar'); ?></strong>
            <small><?php echo e($row['urgencyMessage'] ?? 'Prioridade operacional do registro.'); ?></small>
        </article>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $detailCards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <article>
                <span><?php echo e($card['label'] ?? 'Informação'); ?></span>
                <strong><?php echo e($card['value'] ?? 'Não informado'); ?></strong>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($card['hint'])): ?>
                    <small><?php echo e($card['hint']); ?></small>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </article>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </div>

    <div class="compliance-interno-detail-actions">
        <div>
            <span>Próximas ações</span>
            <p>Use os botões abaixo para continuar no fluxo correto, sem precisar procurar a tela manualmente no menu.</p>
        </div>

        <div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $actions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <a
                    href="<?php echo e($action['url']); ?>"
                    class="compliance-action-button <?php echo e($action['style'] ?? 'secondary'); ?>"
                    data-interno-flow-action
                    data-interno-action-label="<?php echo e($action['label'] ?? 'Abrir'); ?>"
                    data-interno-record-title="<?php echo e($row['title'] ?? 'registro interno'); ?>"
                    <?php if(! empty($action['external'])): ?> target="_blank" rel="noopener noreferrer" <?php endif; ?>
                    <?php if(! empty($action['hint'])): ?> title="<?php echo e($action['hint']); ?>" <?php endif; ?>
                >
                    <span><?php echo e($action['label'] ?? 'Abrir'); ?></span>
                </a>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <span class="compliance-action-unavailable">Este registro é apenas informativo nesta tela.</span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\sistemrh\resources\views/filament/pages/partials/compliance-interno-detail-modal.blade.php ENDPATH**/ ?>