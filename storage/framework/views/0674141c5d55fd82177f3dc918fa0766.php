<a class="rd-task rd-tone-<?php echo e($item['tone']); ?>" href="<?php echo e($item['url'] ?: '#'); ?>">
    <div class="rd-task-main">
        <strong><?php echo e($item['title']); ?></strong>
        <p><?php echo e($item['description']); ?></p>
        <div class="rd-task-meta">
            <span><?php echo e($item['status']); ?></span>
            <span><?php echo e($item['urgency']); ?></span>
            <span><?php echo e($item['responsavel']); ?></span>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item['due']): ?>
                <span>Vence: <?php echo e($item['due']); ?></span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <span>Parado: <?php echo e($item['stopped_for']); ?></span>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item['value']): ?>
                <span><?php echo e($item['value']); ?></span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item['blocked']): ?>
                <span>Bloqueado</span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</a>
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views/filament/pages/partials/dashboard-task-card.blade.php ENDPATH**/ ?>