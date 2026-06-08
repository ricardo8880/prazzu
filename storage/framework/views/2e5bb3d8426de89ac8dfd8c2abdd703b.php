<?php
    $empresa = $documento['nome_fantasia'] ?: ($documento['razao_social'] ?: '-');
    $prioridadeOperacional = $documento['prioridade_operacional'] ?? ['label' => 'Estável', 'motivo' => 'Atualize o documento sem sair desta página.', 'tom' => 'success', 'prazo' => '-'];
?>

<div class="documentos-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="documentos-resolver-titulo">
    <div class="documentos-modal-card documentos-modal-card--wide">
        <form wire:submit.prevent="resolverDocumentoRapido(<?php echo e($documento['id']); ?>)" class="documentos-resolver-form documentos-resolver-form--dialog">
            <div class="documentos-modal-header">
                <div>
                    <span class="pz-ux-kicker">Resolução rápida</span>
                    <h2 id="documentos-resolver-titulo"><?php echo e($documento['titulo']); ?></h2>
                    <p>Atualize status, prazo, portal, observação e arquivo sem sair da página Documentos.</p>
                </div>
                <button type="button" class="documentos-modal-close" wire:click="fecharResolucaoDocumento" wire:loading.attr="disabled" aria-label="Fechar resolução">×</button>
            </div>

            <div class="documentos-resolver-header <?php echo e($prioridadeOperacional['tom'] ?? 'success'); ?>">
                <div>
                    <span>Contexto operacional</span>
                    <h3><?php echo e($prioridadeOperacional['motivo'] ?? 'Atualize o documento sem sair desta página.'); ?></h3>
                    <p>Esse popup segue o padrão de abertura global da Central de Aprovações para não ficar preso dentro do card.</p>
                </div>
                <strong><?php echo e($prioridadeOperacional['label'] ?? 'Estável'); ?></strong>
            </div>

            <div class="documentos-resolver-summary">
                <div><span>Empresa</span><strong><?php echo e($empresa); ?></strong></div>
                <div><span>Tipo</span><strong><?php echo e(ucfirst(str_replace('_', ' ', $documento['tipo'] ?? '-'))); ?></strong></div>
                <div><span>Status atual</span><strong><?php echo e(ucfirst(str_replace('_', ' ', $documento['status'] ?? '-'))); ?></strong></div>
                <div><span>Vencimento</span><strong><?php echo e(! empty($documento['data_vencimento']) ? \Carbon\Carbon::parse($documento['data_vencimento'])->format('d/m/Y') : '-'); ?></strong></div>
            </div>

            <div class="documentos-resolver-grid">
                <label class="documentos-resolver-field">
                    <span>Status</span>
                    <select wire:model.defer="resolverStatus.<?php echo e($documento['id']); ?>">
                        <option value="">Manter status atual</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($documento['status_resolucao_options'] ?? $statusResolucaoOptions); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <option value="<?php echo e($value); ?>"><?php echo e($label); ?></option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['resolverStatus.' . $documento['id']];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="documentos-field-error"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </label>

                <label class="documentos-resolver-field">
                    <span>Data de vencimento</span>
                    <input type="date" wire:model.defer="resolverDataVencimento.<?php echo e($documento['id']); ?>">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['resolverDataVencimento.' . $documento['id']];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="documentos-field-error"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </label>

                <label class="documentos-resolver-field documentos-resolver-field--file">
                    <span>Arquivo principal</span>
                    <input type="file" wire:model="resolverArquivos.<?php echo e($documento['id']); ?>">
                    <small><?php echo e(! empty($documento['arquivo_url']) ? 'Enviar um novo arquivo substitui a referência principal.' : 'Anexe o arquivo para regularizar o item.'); ?></small>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['resolverArquivos.' . $documento['id']];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="documentos-field-error"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </label>

                <label class="documentos-resolver-toggle">
                    <input type="checkbox" wire:model.defer="resolverPortalAtivo.<?php echo e($documento['id']); ?>">
                    <span>Liberado no portal do cliente</span>
                </label>
            </div>

            <label class="documentos-resolver-field documentos-resolver-field--full">
                <span>Observação da resolução</span>
                <textarea rows="4" wire:model.defer="resolverObservacao.<?php echo e($documento['id']); ?>" placeholder="Registre o que foi ajustado, recebido ou conferido neste documento."></textarea>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['resolverObservacao.' . $documento['id']];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="documentos-field-error"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </label>

            <div class="documentos-resolver-footer documentos-resolver-footer--dialog">
                <div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($documento['arquivo_url'])): ?>
                        <a href="<?php echo e($documento['arquivo_url']); ?>" target="_blank" rel="noopener noreferrer">Abrir arquivo atual</a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <a href="<?php echo e($documento['enterprise_url'] ?? $documento['edit_url']); ?>">Ver na Enterprise</a>
                </div>
                <div class="documentos-modal-actions">
                    <?php if (isset($component)) { $__componentOriginal6330f08526bbb3ce2a0da37da512a11f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['type' => 'button','color' => 'gray','wire:click' => 'fecharResolucaoDocumento','wire:loading.attr' => 'disabled']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'button','color' => 'gray','wire:click' => 'fecharResolucaoDocumento','wire:loading.attr' => 'disabled']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                        Fechar
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
                    <?php if (isset($component)) { $__componentOriginal6330f08526bbb3ce2a0da37da512a11f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['type' => 'submit','color' => 'primary','icon' => 'heroicon-m-check-circle','wire:loading.attr' => 'disabled','wire:target' => 'resolverDocumentoRapido('.e($documento['id']).'),resolverArquivos.'.e($documento['id']).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'submit','color' => 'primary','icon' => 'heroicon-m-check-circle','wire:loading.attr' => 'disabled','wire:target' => 'resolverDocumentoRapido('.e($documento['id']).'),resolverArquivos.'.e($documento['id']).'']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                        Salvar resolução
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
            </div>
        </form>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views/filament/pages/partials/documento-resolver-dialog.blade.php ENDPATH**/ ?>