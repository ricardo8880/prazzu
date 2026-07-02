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


    <?php ($etapas = $this->etapas()); ?>
    <?php ($itensRecentes = $this->itensRecentes()); ?>

    <div class="fo-page">
        <section class="fo-hero">
            <div class="fo-hero-content">
                <div class="fo-eyebrow">Fluxo operacional</div>
                <h2 class="fo-title"><?php echo e($record->nome); ?></h2>
                <p class="fo-subtitle">
                    <?php echo e($record->descricao ?: 'Fluxo criado para padronizar as etapas operacionais dos itens de controle.'); ?>

                </p>

                <div class="fo-tags">
                    <span><?php echo e($this->tipoItemLabel()); ?></span>
                    <span><?php echo e($record->padrao ? 'Fluxo padrão' : 'Fluxo personalizado'); ?></span>
                    <span><?php echo e($record->ativo ? 'Ativo' : 'Inativo'); ?></span>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->usuarioEhSuperAdmin()): ?>
                        <span><?php echo e($record->empresa?->razao_social ?? 'Sem empresa'); ?></span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </section>

        <section class="fo-metrics">
            <article class="fo-metric-card">
                <span>Etapas cadastradas</span>
                <strong><?php echo e($etapas->count()); ?></strong>
                <small><?php echo e($this->totalEtapasAtivas()); ?> etapas ativas</small>
            </article>

            <article class="fo-metric-card fo-metric-card--info">
                <span>Itens vinculados</span>
                <strong><?php echo e($this->totalItens()); ?></strong>
                <small>Itens usando este fluxo</small>
            </article>

            <article class="fo-metric-card fo-metric-card--warning">
                <span>Pendentes</span>
                <strong><?php echo e($this->itensPendentes()); ?></strong>
                <small>Ainda precisam avançar</small>
            </article>

            <article class="fo-metric-card fo-metric-card--success">
                <span>Concluídos</span>
                <strong><?php echo e($this->itensConcluidos()); ?></strong>
                <small>Finalizados nesse fluxo</small>
            </article>
        </section>

        <section class="fo-layout">
            <article class="fo-panel fo-panel--timeline">
                <div class="fo-panel-header">
                    <div>
                        <h3>Etapas do fluxo</h3>
                        <p>Ordem operacional que o cliente consegue entender sem depender apenas da tabela.</p>
                    </div>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($etapas->isEmpty()): ?>
                    <div class="fo-empty">
                        <strong>Nenhuma etapa cadastrada</strong>
                        <span>Edite o fluxo e adicione as etapas para montar a jornada operacional.</span>
                    </div>
                <?php else: ?>
                    <div class="fo-timeline">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $etapas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $etapa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <div class="fo-step <?php echo e($etapa->ativo ? '' : 'fo-step--inactive'); ?>">
                                <div class="fo-step-number"><?php echo e($etapa->ordem ?: $loop->iteration); ?></div>

                                <div class="fo-step-body">
                                    <div class="fo-step-top">
                                        <h4><?php echo e($etapa->nome); ?></h4>
                                        <span><?php echo e($etapa->ativo ? 'Ativa' : 'Inativa'); ?></span>
                                    </div>

                                    <p><?php echo e($etapa->descricao ?: 'Sem descrição cadastrada para esta etapa.'); ?></p>

                                    <div class="fo-step-meta">
                                        <span>Prazo: <?php echo e($etapa->prazo_horas ? $etapa->prazo_horas . 'h' : 'Não definido'); ?></span>
                                        <span>Aprovação: <?php echo e($etapa->exige_aprovacao ? 'Sim' : 'Não'); ?></span>
                                        <span>Responsável: <?php echo e($etapa->responsavelPadrao?->nome ?? 'Não definido'); ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </article>

            <aside class="fo-panel fo-panel--side">
                <div class="fo-panel-header">
                    <div>
                        <h3>Resumo do fluxo</h3>
                        <p>Informações principais da configuração.</p>
                    </div>
                </div>

                <div class="fo-summary-list">
                    <div>
                        <span>Empresa</span>
                        <strong><?php echo e($record->empresa?->razao_social ?? '-'); ?></strong>
                    </div>
                    <div>
                        <span>Tipo de item</span>
                        <strong><?php echo e($this->tipoItemLabel()); ?></strong>
                    </div>
                    <div>
                        <span>Padrão</span>
                        <strong><?php echo e($record->padrao ? 'Sim' : 'Não'); ?></strong>
                    </div>
                    <div>
                        <span>Status</span>
                        <strong><?php echo e($record->ativo ? 'Ativo' : 'Inativo'); ?></strong>
                    </div>
                    <div>
                        <span>Criado em</span>
                        <strong><?php echo e(optional($record->created_at)->format('d/m/Y H:i') ?: '-'); ?></strong>
                    </div>
                    <div>
                        <span>Atualizado em</span>
                        <strong><?php echo e(optional($record->updated_at)->format('d/m/Y H:i') ?: '-'); ?></strong>
                    </div>
                </div>
            </aside>
        </section>

        <section class="fo-panel">
            <div class="fo-panel-header">
                <div>
                    <h3>Itens recentes neste fluxo</h3>
                    <p>Últimos itens de controle vinculados a esta configuração operacional.</p>
                </div>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($itensRecentes->isEmpty()): ?>
                <div class="fo-empty">
                    <strong>Nenhum item vinculado ainda</strong>
                    <span>Quando um item usar este fluxo, ele aparecerá aqui.</span>
                </div>
            <?php else: ?>
                <div class="fo-table-wrap">
                    <table class="fo-table">
                        <thead>
                        <tr>
                            <th>Item</th>
                            <th>Status</th>
                            <th>Responsável</th>
                            <th>Vencimento</th>
                            <th>Atualizado</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $itensRecentes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <tr>
                                <td title="<?php echo e($item->titulo); ?>"><?php echo e($item->titulo); ?></td>
                                <td><span class="fo-status <?php echo e($this->statusClasse($item->status)); ?>"><?php echo e($this->statusLabel($item->status)); ?></span></td>
                                <td><?php echo e($item->responsavel?->nome ?? '-'); ?></td>
                                <td><?php echo e(optional($item->data_vencimento)->format('d/m/Y') ?: '-'); ?></td>
                                <td><?php echo e(optional($item->updated_at)->format('d/m/Y H:i') ?: '-'); ?></td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views\filament\resources\fluxos-operacionais\pages\ver-fluxo-operacional.blade.php ENDPATH**/ ?>