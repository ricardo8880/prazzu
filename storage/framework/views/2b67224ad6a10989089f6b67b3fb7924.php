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

    <link rel="stylesheet" href="<?php echo e(asset('css/trabalho-pages.css')); ?>?v=20260504-drop-sla">

    <?php
        $resumo = $this->getResumo();
        $itens = $this->getItensCriticos();
        $selecionado = $this->getItemSelecionado();
    ?>

    <div class="tp-page">
        <div class="tp-hero">
            <div>
                <span class="tp-eyebrow">TRABALHO</span>
                <h2>SLA e Prazos</h2>
                <p>Controle visual de SLA, limites de atendimento e itens críticos com prazo vencendo.</p>
            </div>
        </div>

        <div class="tp-metrics tp-metrics-4">
            <div class="tp-card"><span>Com SLA</span><strong><?php echo e($resumo['com_sla']); ?></strong><small>itens monitorados</small></div>
            <div class="tp-card"><span>Em andamento</span><strong><?php echo e($resumo['em_andamento']); ?></strong><small>dentro do fluxo</small></div>
            <div class="tp-card tp-danger"><span>Vencidos</span><strong><?php echo e($resumo['vencidos']); ?></strong><small>fora do prazo</small></div>
            <div class="tp-card tp-success"><span>Concluídos</span><strong><?php echo e($resumo['concluidos']); ?></strong><small>SLA encerrado</small></div>
        </div>

        <?php if (isset($component)) { $__componentOriginalee08b1367eba38734199cf7829b1d1e9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalee08b1367eba38734199cf7829b1d1e9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.section.index','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::section'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

             <?php $__env->slot('heading', null, []); ?> Fila crítica de SLA <?php $__env->endSlot(); ?>
             <?php $__env->slot('description', null, []); ?> Clique em uma linha para abrir o resumo do item, acompanhar o SLA e acessar o cadastro completo. <?php $__env->endSlot(); ?>

            <div class="tp-table-wrap">
                <table class="tp-table tp-sla-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Status</th>
                            <th>SLA</th>
                            <th>Limite</th>
                            <th>Responsável</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $itens; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <tr wire:click="abrirItem(<?php echo e($item['id']); ?>)" class="tp-table-clickable">
                                <td><button type="button" class="tp-table-link-button"><?php echo e($item['titulo']); ?></button><small><?php echo e($item['empresa']); ?></small></td>
                                <td><span class="tp-badge <?php if($item['status'] === 'Vencido'): ?> tp-badge-danger <?php endif; ?>"><?php echo e($item['status']); ?></span></td>
                                <td><?php echo e($item['sla']); ?></td>
                                <td><?php echo e($item['limite']); ?></td>
                                <td><?php echo e($item['responsavel']); ?></td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <tr><td colspan="5" class="tp-empty">Nenhum item com SLA em aberto.</td></tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalee08b1367eba38734199cf7829b1d1e9)): ?>
<?php $attributes = $__attributesOriginalee08b1367eba38734199cf7829b1d1e9; ?>
<?php unset($__attributesOriginalee08b1367eba38734199cf7829b1d1e9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalee08b1367eba38734199cf7829b1d1e9)): ?>
<?php $component = $__componentOriginalee08b1367eba38734199cf7829b1d1e9; ?>
<?php unset($__componentOriginalee08b1367eba38734199cf7829b1d1e9); ?>
<?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($modalAberto && $selecionado): ?>
            <div class="tp-modal-backdrop" wire:click="fecharModal">
                <div class="tp-modal tp-sla-modal" wire:click.stop>
                    <div class="tp-modal-header">
                        <div>
                            <span class="tp-eyebrow"><?php echo e($selecionado['tipo']); ?> • <?php echo e($selecionado['prioridade']); ?></span>
                            <h3><?php echo e($selecionado['titulo']); ?></h3>
                            <p><?php echo e($selecionado['empresa']); ?> • <?php echo e($selecionado['responsavel']); ?></p>
                        </div>
                        <button type="button" wire:click="fecharModal" class="tp-modal-close">×</button>
                    </div>

                    <div class="tp-modal-body">
                        <div class="tp-modal-grid">
                            <div class="tp-info-card <?php if($selecionado['vencido']): ?> danger <?php endif; ?>">
                                <span>Status do SLA</span>
                                <strong><?php echo e($selecionado['status']); ?></strong>
                                <small><?php echo e($selecionado['tempo_restante']); ?></small>
                            </div>
                            <div class="tp-info-card">
                                <span>Limite</span>
                                <strong><?php echo e($selecionado['limite']); ?></strong>
                                <small>SLA contratado: <?php echo e($selecionado['sla']); ?></small>
                            </div>
                            <div class="tp-info-card">
                                <span>Checklist</span>
                                <strong><?php echo e($selecionado['checklists_concluidos']); ?>/<?php echo e($selecionado['checklists_total']); ?></strong>
                                <small><?php echo e($selecionado['checklists_percentual']); ?>% concluído</small>
                            </div>
                        </div>

                        <div class="tp-detail-card">
                            <div class="tp-detail-title">
                                <strong>Andamento do checklist</strong>
                                <span><?php echo e($selecionado['checklists_percentual']); ?>%</span>
                            </div>
                            <div class="tp-progress tp-progress-large"><i style="width: <?php echo e($selecionado['checklists_percentual']); ?>%"></i></div>

                            <div class="tp-check-mini-list">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $selecionado['checklists']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $checklist): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <div class="tp-check-mini <?php if($checklist['concluido']): ?> done <?php endif; ?>">
                                        <span><?php echo e($checklist['concluido'] ? '✓' : '•'); ?></span>
                                        <p><?php echo e($checklist['titulo']); ?></p>
                                    </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    <div class="tp-empty">Nenhum checklist cadastrado nesse item.</div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>

                        <div class="tp-sla-modal-columns">
                            <div class="tp-detail-card">
                                <div class="tp-detail-title">
                                    <strong>Detalhes principais</strong>
                                </div>
                                <div class="tp-info-list">
                                    <p><span>Categoria</span><b><?php echo e($selecionado['categoria']); ?></b></p>
                                    <p><span>Status interno</span><b><?php echo e($selecionado['sla_status']); ?></b></p>
                                    <p><span>Vencimento</span><b><?php echo e($selecionado['data_vencimento']); ?></b></p>
                                    <p><span>Concluído em</span><b><?php echo e($selecionado['sla_concluido_em']); ?></b></p>
                                    <p><span>Criado em</span><b><?php echo e($selecionado['criado_em']); ?></b></p>
                                    <p><span>Atualizado em</span><b><?php echo e($selecionado['atualizado_em']); ?></b></p>
                                </div>
                            </div>

                            <div class="tp-detail-card">
                                <div class="tp-detail-title">
                                    <strong>Descrição e observação</strong>
                                </div>
                                <div class="tp-text-block">
                                    <p><?php echo nl2br(e($selecionado['descricao'])); ?></p>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selecionado['observacao']): ?>
                                        <p><strong>Observação:</strong> <?php echo nl2br(e($selecionado['observacao'])); ?></p>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tp-modal-footer">
                        <?php if (isset($component)) { $__componentOriginal6330f08526bbb3ce2a0da37da512a11f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['color' => 'gray','wire:click' => 'fecharModal']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['color' => 'gray','wire:click' => 'fecharModal']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['tag' => 'a','href' => ''.e($selecionado['url']).'','color' => 'warning','icon' => 'heroicon-o-arrow-top-right-on-square']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['tag' => 'a','href' => ''.e($selecionado['url']).'','color' => 'warning','icon' => 'heroicon-o-arrow-top-right-on-square']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                            Ir para o produto
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
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views\filament\pages\sla-prazos.blade.php ENDPATH**/ ?>