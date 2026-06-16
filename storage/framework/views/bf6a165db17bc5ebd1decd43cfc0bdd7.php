<article class="ca-approval-card <?php echo e($item['tom']); ?> <?php echo e(! empty($compacto) ? 'compact' : ''); ?> <?php echo e(! empty($destaque) ? 'featured' : ''); ?>">
    <div class="ca-approval-top">
        <div>
            <h3><?php echo e($item['titulo']); ?></h3>
            <small><?php echo e($item['empresa']); ?> • <?php echo e($item['tipo']); ?></small>
        </div>
        <span><?php echo e($item['status_label']); ?></span>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($item['atrasado']) || ! empty($item['critico'])): ?>
        <div class="ca-alert-line">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($item['atrasado'])): ?>
                <strong>Atrasado</strong>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($item['critico'])): ?>
                <strong>Prioridade alta</strong>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(empty($compacto)): ?>
        <p><?php echo e($item['descricao']); ?></p>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="ca-tags">
        <b class="priority"><?php echo e($item['prioridade']); ?></b>
        <b>Responsável: <?php echo e($item['responsavel']); ?></b>
        <b>Solicitante: <?php echo e($item['solicitante']); ?></b>
        <b>Aprovador: <?php echo e($item['aprovador']); ?></b>
        <b>Solicitado: <?php echo e($item['solicitado_em']); ?></b>
        <b>Aguardando: <?php echo e($item['idade']); ?></b>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($item['decisao_alerta']) && $item['status'] === 'pendente'): ?>
            <b class="decision">Revisão obrigatória</b>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item['vencimento'] !== '-'): ?>
            <b class="<?php echo e($item['atrasado'] ? 'late' : ''); ?>">Vence: <?php echo e($item['vencimento']); ?></b>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($item['resposta']) && empty($compacto)): ?>
        <div class="ca-note"><strong>Histórico da decisão:</strong> <?php echo e($item['resposta']); ?></div>
    <?php elseif(empty($compacto)): ?>
        <div class="ca-note"><strong>Pedido de aprovação:</strong> <?php echo e($item['observacao']); ?></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="ca-card-actions">
        <button type="button" class="details" wire:click="abrirDetalhesItem(<?php echo e($item['id']); ?>)" wire:loading.attr="disabled" wire:target="abrirDetalhesItem(<?php echo e($item['id']); ?>)">
            <span wire:loading.remove wire:target="abrirDetalhesItem(<?php echo e($item['id']); ?>)">Ver detalhes</span>
            <span wire:loading wire:target="abrirDetalhesItem(<?php echo e($item['id']); ?>)">Abrindo...</span>
        </button>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item['status'] === 'pendente'): ?>
            <button type="button" class="approve" wire:click="abrirConfirmacaoAprovacao(<?php echo e($item['id']); ?>)" wire:loading.attr="disabled" wire:target="abrirConfirmacaoAprovacao(<?php echo e($item['id']); ?>)">Aprovar com revisão</button>
            <button type="button" class="reject" wire:click="abrirReprovacao(<?php echo e($item['id']); ?>)" wire:loading.attr="disabled" wire:target="abrirReprovacao(<?php echo e($item['id']); ?>)">Solicitar ajuste</button>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</article>
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views\filament\pages\partials\central-aprovacoes-card.blade.php ENDPATH**/ ?>