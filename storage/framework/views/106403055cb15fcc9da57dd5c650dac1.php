<?php if (isset($component)) { $__componentOriginal6330f08526bbb3ce2a0da37da512a11f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['type' => 'button','size' => 'sm','color' => 'primary','icon' => 'heroicon-m-wrench-screwdriver','wire:click' => 'abrirResolucaoDocumento('.e($documento['id']).')','wire:loading.attr' => 'disabled','wire:target' => 'abrirResolucaoDocumento('.e($documento['id']).')']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'button','size' => 'sm','color' => 'primary','icon' => 'heroicon-m-wrench-screwdriver','wire:click' => 'abrirResolucaoDocumento('.e($documento['id']).')','wire:loading.attr' => 'disabled','wire:target' => 'abrirResolucaoDocumento('.e($documento['id']).')']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

    <span wire:loading.remove wire:target="abrirResolucaoDocumento(<?php echo e($documento['id']); ?>)">Resolver</span>
    <span wire:loading wire:target="abrirResolucaoDocumento(<?php echo e($documento['id']); ?>)">Abrindo...</span>
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
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views\filament\pages\partials\documento-resolver-modal.blade.php ENDPATH**/ ?>