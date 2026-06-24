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

    <style>
        .prazzu-admin-page{display:grid;gap:20px}.prazzu-hero{border-radius:24px;padding:24px;background:linear-gradient(135deg,#111827,#1f2937);color:#fff;display:flex;justify-content:space-between;gap:20px;align-items:flex-start}.prazzu-hero h1{font-size:28px;font-weight:900;margin:0}.prazzu-hero p{margin:8px 0 0;color:#d1d5db;max-width:860px}.prazzu-grid{display:grid;gap:16px}.prazzu-grid.two{grid-template-columns:repeat(2,minmax(0,1fr))}.prazzu-grid.four{grid-template-columns:repeat(4,minmax(0,1fr))}.prazzu-card{border:1px solid #e5e7eb;border-radius:20px;background:#fff;padding:18px;box-shadow:0 8px 24px rgba(15,23,42,.06)}.prazzu-card h2{font-size:18px;font-weight:900;margin:0;color:#111827}.prazzu-card p{color:#64748b;margin:6px 0 0}.prazzu-stat span{display:block;color:#64748b;font-size:13px}.prazzu-stat strong{display:block;font-size:24px;margin-top:6px;color:#111827}.prazzu-form{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;margin-top:16px}.prazzu-field span{display:block;color:#64748b;font-size:12px;font-weight:800;margin-bottom:6px}.prazzu-input,.prazzu-select{width:100%;border:1px solid #d1d5db;border-radius:12px;padding:10px 12px;background:#fff}.prazzu-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:16px}.prazzu-button,.prazzu-link{display:inline-flex;align-items:center;justify-content:center;border:0;border-radius:12px;padding:10px 14px;background:#111827;color:#fff;font-weight:800;text-decoration:none;cursor:pointer}.prazzu-link.light{background:#f3f4f6;color:#111827}.prazzu-muted{color:#64748b;font-size:12px}.prazzu-info{display:grid;gap:8px;margin-top:12px}.prazzu-info div{display:flex;justify-content:space-between;gap:12px;border-bottom:1px solid #f1f5f9;padding:8px 0}.prazzu-info strong{color:#111827}@media(max-width:960px){.prazzu-grid.two,.prazzu-grid.four,.prazzu-form{grid-template-columns:1fr}.prazzu-hero{display:block}}
    </style>

    <div class="prazzu-admin-page">
        <section class="prazzu-hero">
            <div>
                <h1>Empresa</h1>
                <p>Centraliza os dados cadastrais e administrativos da conta. Esta tela é o ponto único para conferir identidade do escritório, responsável, contato e plano, sem procurar em resources espalhados.</p>
            </div>
        </section>

        <section class="prazzu-grid four">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $this->resumo(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <article class="prazzu-card prazzu-stat">
                    <span><?php echo e($stat['label']); ?></span>
                    <strong><?php echo e($stat['value']); ?></strong>
                    <small class="prazzu-muted"><?php echo e($stat['hint']); ?></small>
                </article>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <article class="prazzu-card"><strong>Nenhuma empresa disponível.</strong></article>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </section>

        <section class="prazzu-grid two">
            <article class="prazzu-card">
                <h2>Dados principais</h2>
                <p>Edite os dados que o administrador mais procura no dia a dia.</p>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()?->isSuperAdmin()): ?>
                    <label class="prazzu-field" style="display:block;margin-top:14px">
                        <span>Empresa administrada</span>
                        <select class="prazzu-select" wire:model.live="empresaId">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->empresasDisponiveis(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <option value="<?php echo e($id); ?>"><?php echo e($label); ?></option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </select>
                    </label>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <div class="prazzu-form">
                    <label class="prazzu-field"><span>Razão social</span><input class="prazzu-input" wire:model.defer="razao_social"></label>
                    <label class="prazzu-field"><span>Nome fantasia</span><input class="prazzu-input" wire:model.defer="nome_fantasia"></label>
                    <label class="prazzu-field"><span>CNPJ</span><input class="prazzu-input" wire:model.defer="cnpj"></label>
                    <label class="prazzu-field"><span>E-mail</span><input class="prazzu-input" type="email" wire:model.defer="email"></label>
                    <label class="prazzu-field"><span>Telefone / WhatsApp</span><input class="prazzu-input" wire:model.defer="telefone"></label>
                    <label class="prazzu-field"><span>Responsável</span><input class="prazzu-input" wire:model.defer="responsavel_nome"></label>
                    <label class="prazzu-field"><span>Status</span><input class="prazzu-input" wire:model.defer="status"></label>
                    <label class="prazzu-field"><span>Plano</span><input class="prazzu-input" wire:model.defer="plano"></label>
                </div>

                <div class="prazzu-actions">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->podeEditar()): ?>
                        <button class="prazzu-button" type="button" wire:click="salvarEmpresa">Salvar empresa</button>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->resourceUrl()): ?>
                        <a class="prazzu-link light" href="<?php echo e($this->resourceUrl()); ?>">Abrir cadastro completo</a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </article>

            <article class="prazzu-card">
                <h2>O que foi centralizado neste lote</h2>
                <p>A empresa deixa de depender apenas do resource escondido e passa a ter uma porta clara dentro da Central Administrativa.</p>
                <div class="prazzu-info">
                    <div><span>Identidade</span><strong>Razão social, nome fantasia e CNPJ</strong></div>
                    <div><span>Contato</span><strong>E-mail, telefone e responsável</strong></div>
                    <div><span>Conta</span><strong>Status e plano</strong></div>
                    <div><span>Compatibilidade</span><strong>Resource antigo preservado</strong></div>
                </div>
            </article>
        </section>
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
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views/filament/pages/empresa-administrativa.blade.php ENDPATH**/ ?>