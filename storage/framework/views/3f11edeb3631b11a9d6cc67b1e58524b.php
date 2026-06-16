<div class="crm-hub">
    <div class="problema">
        <strong>Problema atual:</strong>
        <span class="badge badge-<?php echo e($problema['tipo']); ?>">
            <?php echo e($problema['label']); ?>

        </span>
    </div>

    <div class="acoes">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($problema['tipo'] === 'documento'): ?>
            <button wire:click="abrirDocumentos">Resolver documento</button>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($problema['tipo'] === 'aprovacao'): ?>
            <button wire:click="abrirAprovacoes">Ir para aprovação</button>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($problema['tipo'] === 'financeiro'): ?>
            <button wire:click="abrirFinanceiro">Ver financeiro</button>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($problema['tipo'] === 'contato'): ?>
            <button wire:click="registrarContatoRapido">Registrar contato</button>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views\filament\components\crm-hub.blade.php ENDPATH**/ ?>