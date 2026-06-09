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

    <link rel="stylesheet" href="<?php echo e(asset('css/inteligencia-produto.css')); ?>">

    <section class="pi-page">
        <header class="pi-card pi-hero">
            <div class="pi-hero__content">
                <span class="pi-eyebrow">Módulo interno exclusivo do super admin</span>
                <h2 class="pi-title">Inteligência do Produto</h2>
                <p class="pi-description">
                    Importe comentários para o banco, visualize exatamente o texto salvo em cada registro e baixe um arquivo .txt com os comentários armazenados.
                </p>
            </div>

            <div class="pi-actions pi-actions--hero">
                <button type="button" wire:click="exportPrompt" class="pi-btn pi-btn--success">
                    Baixar prompt
                </button>
            </div>
        </header>

        <div class="pi-tab-panel pi-bottom-grid pi-bottom-grid--analysis">
            <article class="pi-card">
                <div class="pi-section-header">
                    <h3 class="pi-section-title">Importação</h3>
                    <p class="pi-section-text">
                        Cole os comentários no campo abaixo e envie para o banco. O texto será salvo sem classificação, sem resumo e sem alteração de conteúdo.
                    </p>
                </div>

                <form wire:submit.prevent="importComments" class="pi-form-grid">
                    <label class="pi-field pi-field--full">
                        <span>Comentários</span>
                        <textarea
                            rows="14"
                            wire:model.defer="commentsText"
                            class="pi-input pi-textarea"
                            placeholder="Cole aqui os comentários que deseja salvar no banco."
                        ></textarea>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['commentsText'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <small class="pi-field-error"><?php echo e($message); ?></small>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </label>

                    <div class="pi-actions pi-actions--form">
                        <button type="submit" class="pi-btn pi-btn--primary">
                            Enviar comentários para o banco
                        </button>
                    </div>
                </form>
            </article>

            <article class="pi-card">
                <div class="pi-section-header pi-section-header--row">
                    <div>
                        <h3 class="pi-section-title">Comentários salvos no banco</h3>
                        <p class="pi-section-text">
                            Exibindo os últimos 200 registros. O botão Baixar prompt exporta todos os comentários armazenados em <strong>ai_market_comments.original_text</strong>.
                        </p>
                    </div>

                    <span class="pi-badge pi-badge--info"><?php echo e($commentsTotal); ?> comentário(s)</span>
                </div>

                <div class="pi-list">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $comments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="pi-comment-card">
                            <div class="pi-comment-card__meta">
                                <strong>#<?php echo e($comment['id']); ?></strong>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($comment['created_at']): ?>
                                    <span>•</span>
                                    <span><?php echo e($comment['created_at']); ?></span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>

                            <pre class="pi-comment-card__text" style="white-space: pre-wrap; font-family: inherit; margin: 0;"><?php echo e($comment['original_text']); ?></pre>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="pi-empty-state">
                            Nenhum comentário salvo ainda. Use o campo Comentários para enviar o primeiro conteúdo ao banco.
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </article>
        </div>
    </section>
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
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views/filament/pages/inteligencia-produto.blade.php ENDPATH**/ ?>