<?php
    $context = $context ?? 'row';
    $actions = collect($row['actions'] ?? [])->filter(fn ($action) => filled($action['url'] ?? null))->values();
    $detailArguments = [
        'type' => $row['type'] ?? null,
        'recordId' => $row['recordId'] ?? null,
        'itemId' => $row['itemId'] ?? null,
        'context' => $context,
    ];
?>

<div
    class="compliance-row compliance-row-readable compliance-row-filterable compliance-row-actionable <?php echo e(in_array(($row['tone'] ?? 'info'), ['danger', 'warning'], true) ? 'is-priority' : ''); ?>"
    data-interno-row
    data-type="<?php echo e($row['type'] ?? ''); ?>"
    data-status="<?php echo e($row['rawStatus'] ?? ''); ?>"
    data-priority="<?php echo e($row['rawPriority'] ?? ''); ?>"
    data-urgency="<?php echo e($row['urgencyRank'] ?? '80'); ?>"
    data-search="<?php echo e(e($row['searchable'] ?? (($row['title'] ?? '') . ' ' . ($row['description'] ?? '') . ' ' . ($row['meta'] ?? '')))); ?>"
>
    <div>
        <div class="compliance-row-heading">
            <span class="compliance-kind-pill <?php echo e($row['kindTone'] ?? $defaultKindTone ?? 'info'); ?>"><?php echo e($row['kind'] ?? $defaultKind ?? 'Registro'); ?></span>
            <span class="compliance-urgency-pill <?php echo e($row['urgencyTone'] ?? 'info'); ?>" title="<?php echo e($row['urgencyMessage'] ?? 'Prioridade operacional do registro.'); ?>"><?php echo e($row['urgencyLabel'] ?? 'Acompanhar'); ?></span>
            <h3><?php echo e($row['title']); ?></h3>
        </div>

        <div class="compliance-meta-tags" aria-label="Informações do registro">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($row['metaTags'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <span><?php echo e($tag); ?></span>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <small><?php echo e($row['meta'] ?? 'Informações não disponíveis'); ?></small>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <span><?php echo e($row['date']); ?></span>
        </div>

        <p><?php echo e($row['description']); ?></p>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($row['urgencyMessage'])): ?>
            <small class="compliance-urgency-message <?php echo e($row['urgencyTone'] ?? 'info'); ?>"><?php echo e($row['urgencyMessage']); ?></small>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($showNextStep ?? false) && ! empty($row['nextStep'])): ?>
            <small class="compliance-next-step"><?php echo e($row['nextStep']); ?></small>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="compliance-row-actions" aria-label="Ações disponíveis para <?php echo e($row['title']); ?>">
            <button
                type="button"
                class="compliance-detail-trigger"
                wire:click='mountAction("viewInternoDetails", <?php echo json_encode($detailArguments, 15, 512) ?>)'
                wire:loading.attr="disabled"
                wire:target='mountAction("viewInternoDetails", <?php echo json_encode($detailArguments, 15, 512) ?>)'
                data-interno-detail-action
                data-interno-action-label="Ver detalhes"
            >
                <span>Ver detalhes</span>
            </button>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $actions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
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
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </div>
    </div>

    <span class="compliance-badge <?php echo e($row['tone'] ?? 'info'); ?>"><?php echo e($row['status']); ?></span>
</div>
<?php /**PATH C:\xampp\htdocs\sistemrh\resources\views/filament/pages/partials/compliance-interno-row.blade.php ENDPATH**/ ?>