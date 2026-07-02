<?php
    $assinatura = $assinatura ?? null;
    $item = $item ?? null;
    $tone = $assinatura['tone'] ?? 'gray';
    $portalUrl = $assinatura['portal_url'] ?? null;
?>

<div id="painel-assinaturas" class="signature-panel">
<div class="signature-header">
        <div>
            <div class="signature-eyebrow">Assinatura do documento</div>
            <h2 class="signature-title">Controle operacional de assinatura</h2>
            <p class="signature-subtitle">Acompanhe quem já assinou, quem ainda falta assinar, datas do processo, link do portal e sincronização com Clicksign quando configurada.</p>
        </div>

        <div class="signature-status <?php echo e($tone); ?>">
            <span class="signature-dot"></span>
            <?php echo e($assinatura['label'] ?? 'Não enviada'); ?>

        </div>
    </div>

    <div class="signature-body">
        <div class="signature-grid">
            <div class="signature-metric">
                <span>Assinados</span>
                <strong><?php echo e($assinatura['total_assinados'] ?? 0); ?></strong>
            </div>
            <div class="signature-metric">
                <span>Pendentes</span>
                <strong><?php echo e($assinatura['total_pendentes'] ?? 0); ?></strong>
            </div>
            <div class="signature-metric">
                <span>Enviado em</span>
                <strong><?php echo e(($assinatura['enviado_em'] ?? null)?->format('d/m/Y H:i') ?? 'Ainda não enviado'); ?></strong>
            </div>
            <div class="signature-metric">
                <span>Concluído em</span>
                <strong><?php echo e(($assinatura['concluido_em'] ?? null)?->format('d/m/Y H:i') ?? 'Pendente'); ?></strong>
            </div>
        </div>

        <div class="signature-columns">
            <section class="signature-card">
                <header>
                    <h3>Quem assinou</h3>
                    <small><?php echo e($assinatura['total_assinados'] ?? 0); ?> registro(s)</small>
                </header>
                <div class="signature-list">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($assinatura['assinantes_concluidos'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $assinante): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="signature-person">
                            <strong><?php echo e($assinante['nome']); ?></strong>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($assinante['email'])): ?><span><?php echo e($assinante['email']); ?></span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($assinante['documento'])): ?><span>Documento: <?php echo e($assinante['documento']); ?></span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <small><?php echo e($assinante['origem'] ?? 'Assinatura'); ?> · <?php echo e(($assinante['assinado_em'] ?? null)?->format('d/m/Y H:i') ?? 'Data não registrada'); ?></small>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($assinante['hash'])): ?><small>Hash: <?php echo e($assinante['hash']); ?></small><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="signature-empty">Nenhuma assinatura registrada ainda. Use “Reenviar assinatura” para ativar o portal e compartilhar o link com o assinante.</div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </section>

            <section class="signature-card">
                <header>
                    <h3>Quem falta assinar</h3>
                    <small><?php echo e($assinatura['total_pendentes'] ?? 0); ?> pendente(s)</small>
                </header>
                <div class="signature-list">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($assinatura['assinantes_pendentes'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $assinante): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="signature-person">
                            <strong><?php echo e($assinante['nome']); ?></strong>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($assinante['email'])): ?><span><?php echo e($assinante['email']); ?></span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($assinante['documento'])): ?><span>Documento: <?php echo e($assinante['documento']); ?></span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <small><?php echo e($assinante['origem'] ?? 'Pendente'); ?></small>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="signature-empty">Não há assinantes pendentes para este item.</div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </section>
        </div>

        <div class="signature-clicksign">
            <div>
                <strong>Clicksign</strong>
                <span>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($assinatura['clicksign']['habilitado'] ?? false) && ! empty($assinatura['clicksign']['document_key'])): ?>
                        Integração configurada para este documento. Última sincronização: <?php echo e(($assinatura['clicksign']['ultima_sincronizacao_em'] ?? null)?->format('d/m/Y H:i') ?? 'ainda não sincronizada'); ?>.
                    <?php elseif(($assinatura['clicksign']['habilitado'] ?? false)): ?>
                        Token configurado, mas este item ainda não possui chave de documento Clicksign registrada.
                    <?php else: ?>
                        Token/base URL da Clicksign não configurados no ambiente. O fluxo interno pelo portal continua funcionando normalmente.
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </span>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($assinatura['clicksign']['mensagem'])): ?>
                    <span><?php echo e($assinatura['clicksign']['mensagem']); ?></span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($assinatura['clicksign']['document_key'])): ?>
                <span class="signature-status gray">Doc: <?php echo e($assinatura['clicksign']['document_key']); ?></span>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <div class="signature-footer">
        <div class="signature-note">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($assinatura['ultimo_reenvio_em'] ?? null)): ?>
                Último reenvio em <?php echo e($assinatura['ultimo_reenvio_em']->format('d/m/Y H:i')); ?>.
            <?php elseif(($assinatura['ultima_consulta_em'] ?? null)): ?>
                Última consulta em <?php echo e($assinatura['ultima_consulta_em']->format('d/m/Y H:i')); ?>.
            <?php else: ?>
                Use as ações para manter o acompanhamento da assinatura atualizado.
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <div class="signature-actions">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($portalUrl): ?>
                <a class="signature-button" href="<?php echo e($portalUrl); ?>" target="_blank" rel="noopener">Abrir portal</a>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($podeGerenciar): ?>
                <button type="button" class="signature-button" wire:click="consultarStatus" wire:loading.attr="disabled">Consultar status</button>
                <button type="button" class="signature-button primary" wire:click="reenviarAssinatura" wire:loading.attr="disabled">Reenviar assinatura</button>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views\filament\resources\item-controles\widgets\item-controle-assinaturas-widget.blade.php ENDPATH**/ ?>