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

    <link rel="stylesheet" href="<?php echo e(asset('css/centro-operacional.css')); ?>?v=<?php echo e(file_exists(public_path('css/centro-operacional.css')) ? filemtime(public_path('css/centro-operacional.css')) : time()); ?>">

    <?php
        $loadError = $loadError ?? null;
        $aprovacoes = collect($data['aprovacoes'] ?? [])->take(12)->values()->all();
        $financeiro = collect($data['financeiro'] ?? [])->take(12)->values()->all();
        $financeiroResumo = $data['financeiro_resumo'] ?? ['indicadores' => [], 'impacto_total' => 'R$ 0,00'];
        $workload = collect($data['workload'] ?? [])->take(12)->values()->all();
        $activeTab = $operationalTab ?? 'workload';
    ?>

    <div class="co-page co-model co-operational-detail-page" wire:loading.class="is-loading">
        <section class="co-topbar co-operational-detail-hero">
            <div>
                <div class="co-title-row">
                    <h1>Operação Interna</h1>
                    <span class="co-info">i</span>
                </div>
                <p>Workload, aprovações e pendências financeiras em uma área própria, com espaço para operar sem apertar os cards.</p>
            </div>

            <div class="co-top-actions">
                <button type="button" class="co-refresh-btn" wire:click="refreshDashboard" wire:loading.attr="disabled">
                    <i class="bi bi-arrow-clockwise"></i>
                    Atualizar
                </button>
            </div>
        </section>



        <nav class="co-page-cluster co-main-cluster co-sticky-cluster" aria-label="Navegação da Operação Interna">
            <a class="co-cluster-item" href="<?php echo e(\App\Filament\Pages\CentroOperacional::getUrl()); ?>">
                <span class="co-cluster-icon"><i class="bi bi-command"></i></span>
                <span>
                    <strong>Centro Operacional</strong>
                    <small>Riscos, resolver agora e resultados</small>
                </span>
            </a>
            <a class="co-cluster-item <?php echo e($activeTab === 'workload' ? 'active' : ''); ?>" href="<?php echo e(\App\Filament\Pages\CentroOperacionalGestao::getUrl()); ?>?aba=workload">
                <span class="co-cluster-icon"><i class="bi bi-people"></i></span>
                <span>
                    <strong>Workload da Equipe</strong>
                    <small>Carga, capacidade e redistribuição</small>
                </span>
            </a>
            <a class="co-cluster-item <?php echo e($activeTab === 'aprovacoes' ? 'active' : ''); ?>" href="<?php echo e(\App\Filament\Pages\CentroOperacionalGestao::getUrl()); ?>?aba=aprovacoes">
                <span class="co-cluster-icon"><i class="bi bi-patch-check"></i></span>
                <span>
                    <strong>Aprovações</strong>
                    <small>Aprovar, reprovar e correção</small>
                </span>
            </a>
            <a class="co-cluster-item <?php echo e($activeTab === 'financeiro' ? 'active' : ''); ?>" href="<?php echo e(\App\Filament\Pages\CentroOperacionalGestao::getUrl()); ?>?aba=financeiro">
                <span class="co-cluster-icon"><i class="bi bi-bank"></i></span>
                <span>
                    <strong>Pendências Financeiras</strong>
                    <small>A vencer, vencido, inadimplente e faturável</small>
                </span>
            </a>
        </nav>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($loadError): ?>
            <section class="co-state-card error">
                <span class="co-state-icon"><i class="bi bi-exclamation-triangle-fill"></i></span>
                <div>
                    <strong>Falha ao carregar dados.</strong>
                    <p><?php echo e($loadError); ?></p>
                </div>
                <button type="button" wire:click="refreshDashboard" wire:loading.attr="disabled">
                    <i class="bi bi-arrow-clockwise"></i>
                    Tentar novamente
                </button>
            </section>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'workload'): ?>
            <section class="co-panel co-workload-panel co-detail-panel-large">
                <header class="co-panel-header">
                    <div class="co-heading-with-icon">
                        <span class="co-section-icon muted"><i class="bi bi-people"></i></span>
                        <h2>Workload da Equipe</h2>
                    </div>
                    <a class="co-see-all" href="<?php echo e(\App\Filament\Resources\ItemControles\ItemControleResource::getUrl('index')); ?>">Ver todos</a>
                </header>

                <div class="co-workload-list-model co-workload-v2 co-workload-detail-list">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $workload; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php $barWidth = min(100, (int) ($row['percent'] ?? 0)); ?>
                        <div class="co-workload-model-row <?php echo e($row['tone'] ?? 'success'); ?>">
                            <span class="co-person-avatar"><?php echo e(mb_strtoupper(mb_substr($row['name'] ?? 'U', 0, 1))); ?></span>
                            <div class="co-person-info">
                                <strong><?php echo e($row['name']); ?></strong>
                                <small><?php echo e($row['total']); ?> tarefas de <?php echo e($row['capacity'] ?? 40); ?> capacidade • <?php echo e($row['status'] ?? 'Normal'); ?></small>
                            </div>
                            <div class="co-progress"><span style="width: <?php echo e($barWidth); ?>%"></span></div>
                            <b><?php echo e((int) ($row['percent'] ?? 0)); ?>%</b>
                            <div class="co-workload-actions">
                                <a class="co-mini-action" href="<?php echo e($row['open_url'] ?? \App\Filament\Resources\ItemControles\ItemControleResource::getUrl('index')); ?>"><i class="bi bi-list-task"></i>Abrir tarefas</a>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($row['responsavel_id'])): ?>
                                    <button type="button" class="co-mini-action" wire:click="abrirTarefasResponsavel(<?php echo e((int) $row['responsavel_id']); ?>)"><i class="bi bi-arrow-left-right"></i>Redistribuir</button>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="co-empty clean"><strong>Nenhuma carga pendente.</strong></div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </section>
        <?php elseif($activeTab === 'aprovacoes'): ?>
            <section class="co-panel co-approvals-panel co-detail-panel-large">
                <header class="co-panel-header">
                    <div class="co-heading-with-icon">
                        <span class="co-section-icon blue"><i class="bi bi-file-earmark-check"></i></span>
                        <h2>Aprovações</h2>
                    </div>
                    <a class="co-see-all" href="<?php echo e(\App\Filament\Resources\ItemControles\ItemControleResource::getUrl('index')); ?>">Ver todas</a>
                </header>

                <div class="co-small-list-model co-approvals-v2 co-approvals-detail-list">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $aprovacoes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php
                            $actions = $item['actions'] ?? [];
                            $canApprove = (bool) ($actions['approve'] ?? false);
                            $canCorrect = (bool) ($actions['correct'] ?? false);
                        ?>
                        <article class="co-small-model-row co-approval-row">
                            <a class="co-approval-main" href="<?php echo e($item['url']); ?>">
                                <span class="co-small-icon <?php echo e($item['tone'] ?? 'info'); ?>"><i class="bi bi-building"></i></span>
                                <div>
                                    <strong><?php echo e($item['empresa']); ?></strong>
                                    <span><?php echo e($item['title']); ?></span>
                                    <small><?php echo e($item['responsavel'] ?? 'Sem responsável'); ?> • <?php echo e($item['due_human'] ?? 'Sem prazo'); ?></small>
                                </div>
                            </a>
                            <div class="co-approval-actions">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($canApprove): ?>
                                    <button type="button" class="co-mini-action success" wire:click="aprovar(<?php echo e($item['id']); ?>)" wire:loading.attr="disabled"><i class="bi bi-check2"></i>Aprovar</button>
                                    <button type="button" class="co-mini-action danger" wire:click="reprovar(<?php echo e($item['id']); ?>)" wire:loading.attr="disabled"><i class="bi bi-x-lg"></i>Reprovar</button>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($canCorrect): ?>
                                    <button type="button" class="co-mini-action warning" wire:click="enviarParaCorrecao(<?php echo e($item['id']); ?>)" wire:loading.attr="disabled"><i class="bi bi-tools"></i>Correção</button>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </article>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="co-empty clean"><strong>Nada esperando aprovação.</strong></div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </section>
        <?php else: ?>
            <section class="co-panel co-financial-panel co-detail-panel-large">
                <header class="co-panel-header">
                    <div class="co-heading-with-icon">
                        <span class="co-section-icon green"><i class="bi bi-cash-coin"></i></span>
                        <h2>Pendências Financeiras</h2>
                    </div>
                    <button type="button" class="co-see-all as-button" wire:click="abrirPendenciasFinanceiras">Filtrar financeiro</button>
                </header>

                <div class="co-financial-grid co-financial-detail-grid">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($financeiroResumo['indicadores'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $indicator): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <article class="co-financial-card <?php echo e($indicator['tone'] ?? 'success'); ?>">
                            <i class="bi <?php echo e($indicator['icon'] ?? 'bi-cash'); ?>"></i>
                            <div class="co-financial-card-copy">
                                <span><?php echo e($indicator['label'] ?? '-'); ?></span>
                                <strong><?php echo e(number_format((int) ($indicator['quantity'] ?? 0), 0, ',', '.')); ?></strong>
                                <small><?php echo e($indicator['value'] ?? 'R$ 0,00'); ?></small>
                                <em><?php echo e($indicator['impact'] ?? 'Sem impacto imediato'); ?></em>
                            </div>
                        </article>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>

                <div class="co-financial-list co-financial-detail-list">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $financeiro; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php $canFinancial = (bool) (($item['actions']['financial'] ?? false)); ?>
                        <article class="co-financial-row">
                            <a href="<?php echo e($item['url']); ?>">
                                <strong><?php echo e($item['empresa']); ?></strong>
                                <span><?php echo e($item['title']); ?></span>
                                <small><?php echo e($item['value'] ?? 'Sem valor informado'); ?> • <?php echo e($item['status'] ?? '-'); ?></small>
                            </a>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($canFinancial): ?>
                                <div class="co-financial-actions">
                                    <button type="button" class="co-mini-action success" wire:click="marcarFaturado(<?php echo e($item['id']); ?>)" wire:loading.attr="disabled"><i class="bi bi-receipt"></i>Faturar</button>
                                    <button type="button" class="co-mini-action info" wire:click="marcarPago(<?php echo e($item['id']); ?>)" wire:loading.attr="disabled"><i class="bi bi-check2-circle"></i>Pago</button>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </article>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="co-empty clean"><strong>Nenhuma pendência financeira operacional.</strong></div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </section>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($delegateModalOpen): ?>
            <div class="co-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="co-delegate-title">
                <div class="co-modal-card">
                    <header>
                        <span class="co-section-icon purple"><i class="bi bi-person-plus"></i></span>
                        <div>
                            <h3 id="co-delegate-title">Delegar item</h3>
                            <p>Selecione o novo responsável para assumir esta pendência operacional.</p>
                        </div>
                    </header>

                    <label class="co-modal-field">
                        <span>Novo responsável</span>
                        <select wire:model.live="delegateResponsavelId">
                            <option value="">Selecione...</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->delegateResponsavelOptions(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $responsavelId => $responsavelNome): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <option value="<?php echo e($responsavelId); ?>"><?php echo e($responsavelNome); ?></option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </select>
                    </label>

                    <footer>
                        <button type="button" class="co-action-btn muted" wire:click="cancelDelegateModal">Cancelar</button>
                        <button type="button" class="co-action-btn purple" wire:click="delegar" wire:loading.attr="disabled" wire:target="delegar">
                            <i class="bi bi-check2"></i>Confirmar delegação
                        </button>
                    </footer>
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
<?php /**PATH C:\xampp\htdocs\sistemrh\resources\views/filament/pages/centro-operacional-gestao.blade.php ENDPATH**/ ?>