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

    <div class="plans-shell">
        <section class="plans-hero">
            <h2>Planos internos, limites e recursos liberados</h2>
            <p>Controle Starter, Professional e Enterprise em um único lugar, sincronizando usuários, armazenamento, itens operacionais, IA e recursos habilitados por plano.</p>
        </section>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (! ($temColunaArmazenamento)): ?>
            <div class="plans-panel">
                <strong>Banco incompleto para armazenamento.</strong>
                <p class="plans-muted">Execute o SQL manual entregue no pacote para garantir a coluna <code>empresas.limite_armazenamento_mb</code>.</p>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <section class="plans-kpis">
            <article class="plans-kpi"><span>Empresas</span><strong><?php echo e(number_format($resumo['empresas'] ?? 0, 0, ',', '.')); ?></strong></article>
            <article class="plans-kpi"><span>Starter</span><strong><?php echo e(number_format($resumo['starter'] ?? 0, 0, ',', '.')); ?></strong></article>
            <article class="plans-kpi"><span>Professional</span><strong><?php echo e(number_format($resumo['profissional'] ?? 0, 0, ',', '.')); ?></strong></article>
            <article class="plans-kpi"><span>Enterprise</span><strong><?php echo e(number_format($resumo['enterprise'] ?? 0, 0, ',', '.')); ?></strong></article>
        </section>

        <section class="plans-grid">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $planos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plano): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <article class="plan-card">
                    <div>
                        <span><?php echo e($plano['codigo']); ?></span>
                        <h3><?php echo e($plano['nome']); ?></h3>
                        <p class="plans-muted"><?php echo e($plano['descricao']); ?></p>
                    </div>
                    <div class="plan-limits">
                        <div class="plan-limit"><span>Usuários</span><b><?php echo e(number_format($plano['usuarios'], 0, ',', '.')); ?></b></div>
                        <div class="plan-limit"><span>Armazenamento</span><b><?php echo e($plano['armazenamento']); ?></b></div>
                        <div class="plan-limit"><span>Itens</span><b><?php echo e(number_format($plano['itens'], 0, ',', '.')); ?></b></div>
                        <div class="plan-limit"><span>IA</span><b><?php echo e(number_format($plano['ia'], 0, ',', '.')); ?></b></div>
                    </div>
                    <div class="plan-features">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = array_slice($plano['features'], 0, 8); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feature): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <span class="plans-pill"><?php echo e(str_replace('_', ' ', $feature)); ?></span>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($plano['features']) > 8): ?>
                            <span class="plans-pill warning">+<?php echo e(count($plano['features']) - 8); ?> recursos</span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </article>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </section>

        <section class="plans-panel">
            <div class="plans-toolbar">
                <div>
                    <h3 style="font-weight:800;margin:0">Empresas e plano atual</h3>
                    <p class="plans-muted" style="margin:.25rem 0 0">Alterar o plano aqui sincroniza os limites da empresa e a assinatura atual, quando existir.</p>
                </div>
                <div style="display:flex;gap:.5rem;flex-wrap:wrap">
                    <input type="search" wire:model.live.debounce.400ms="search" placeholder="Buscar empresa, e-mail ou CNPJ">
                    <select wire:model.live="planFilter">
                        <option value="todos">Todos os planos</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $planOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $codigo => $nome): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <option value="<?php echo e($codigo); ?>"><?php echo e($nome); ?></option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                    <button type="button" class="plans-pill" wire:click="clearFilters">Limpar</button>
                </div>
            </div>

            <div class="companies-list" style="margin-top:1rem">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $empresas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $empresa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php
                        $storageTone = $empresa['storage_percentual'] >= 90 ? 'danger' : ($empresa['storage_percentual'] >= 75 ? 'warn' : 'good');
                    ?>
                    <article class="company-card">
                        <div>
                            <h3><?php echo e($empresa['nome']); ?></h3>
                            <small><?php echo e($empresa['email'] ?: 'Sem e-mail cadastrado'); ?> · Assinatura: <?php echo e($empresa['assinatura_status'] ?: 'sem assinatura'); ?></small>
                            <div style="margin-top:.4rem"><span class="plans-pill success"><?php echo e($empresa['plano_nome']); ?></span></div>
                        </div>
                        <div class="company-metrics">
                            <small>Usuários: <?php echo e($empresa['usuarios_usados']); ?> de <?php echo e(number_format($empresa['usuarios_limite'], 0, ',', '.')); ?></small>
                            <small>Itens: <?php echo e($empresa['itens_usados']); ?> de <?php echo e(number_format($empresa['itens_limite'], 0, ',', '.')); ?></small>
                            <small>IA mensal: <?php echo e(number_format($empresa['ia_limite'], 0, ',', '.')); ?></small>
                        </div>
                        <div class="company-metrics">
                            <small>Armazenamento: <?php echo e($empresa['storage_usado']); ?> de <?php echo e($empresa['storage_limite']); ?> · <?php echo e($empresa['storage_percentual']); ?>%</small>
                            <div class="progress <?php echo e($storageTone); ?>"><span style="width: <?php echo e(min(100, $empresa['storage_percentual'])); ?>%"></span></div>
                        </div>
                        <div>
                            <select wire:change="updateEmpresaPlano(<?php echo e($empresa['id']); ?>, $event.target.value)">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $planOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $codigo => $nome): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <option value="<?php echo e($codigo); ?>" <?php if($empresa['plano'] === $codigo): echo 'selected'; endif; ?>><?php echo e($nome); ?></option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </select>
                        </div>
                    </article>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <div class="plans-empty">Nenhuma empresa encontrada com os filtros atuais.</div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
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
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views\filament\pages\gestao-planos.blade.php ENDPATH**/ ?>