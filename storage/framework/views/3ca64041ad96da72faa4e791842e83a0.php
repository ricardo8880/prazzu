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


    <div class="configuracoes-prazzu configuracoes-lote4">
        <section class="configuracoes-hero">
            <div>
                <span><i class="bi bi-sliders2-vertical"></i> PARÂMETROS DO ESCRITÓRIO</span>
                <h1>Central de configuração contábil</h1>
                <p>
                    Esta tela concentra apenas regras, preferências e parâmetros internos. Conteúdos operacionais,
                    como resolver pendências, aprovar documentos ou acompanhar prazos, devem permanecer nas abas próprias.
                </p>
            </div>

            <div class="configuracoes-actions">
                <button type="button" wire:click="restaurarPadrao" class="secondary">
                    <i class="bi bi-arrow-counterclockwise"></i> Restaurar padrões
                </button>
                <button type="button" wire:click="salvar" class="primary">
                    <i class="bi bi-check2-circle"></i> Salvar configurações
                </button>
            </div>
        </section>

        <section class="configuracoes-diretriz">
            <article>
                <i class="bi bi-bullseye"></i>
                <div>
                    <strong>Propósito desta aba</strong>
                    <p>Definir parâmetros globais do escritório, notificações, módulos, integrações, permissões e padrões.</p>
                </div>
            </article>
            <article>
                <i class="bi bi-signpost-split"></i>
                <div>
                    <strong>O que não fica aqui</strong>
                    <p>Execução de pendências, documentos aguardando cliente, aprovações e análises devem ficar nas abas operacionais.</p>
                </div>
            </article>
            <article>
                <i class="bi bi-link-45deg"></i>
                <div>
                    <strong>Conexão entre áreas</strong>
                    <p>Quando uma configuração impactar a operação, use orientação e links; não duplique o fluxo operacional.</p>
                </div>
            </article>
        </section>

        <section class="configuracoes-metricas">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->resumoConfiguracoes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <article>
                    <span><?php echo e($label); ?></span>
                    <strong><?php echo e($value); ?></strong>
                </article>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </section>

        <form wire:submit.prevent="salvar" class="configuracoes-form">
            <div class="configuracoes-form-card">
                <div class="configuracoes-section-title">
                    <i class="bi bi-gear-wide-connected"></i>
                    <div>
                        <h2>Parâmetros editáveis</h2>
                        <p>Revise por bloco, salve com intenção e mantenha a operação concentrada nas telas corretas.</p>
                    </div>
                </div>

                <?php echo e($this->form); ?>

            </div>

            <div class="configuracoes-footer-actions">
                <button type="button" wire:click="restaurarPadrao" class="secondary">
                    <i class="bi bi-arrow-counterclockwise"></i> Restaurar padrões
                </button>
                <button type="submit" class="primary">
                    <i class="bi bi-check2-circle"></i> Salvar configurações
                </button>
            </div>
        </form>
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
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views\filament\pages\configuracoes.blade.php ENDPATH**/ ?>