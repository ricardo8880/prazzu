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
            <section class="co-panel co-workload-panel co-detail-panel-large co-mobile-collapsible" x-data="{ open: true }" :class="{ 'is-open': open }">
                <header class="co-panel-header">
                    <div class="co-heading-with-icon">
                        <span class="co-section-icon muted"><i class="bi bi-people"></i></span>
                        <h2>Workload da Equipe</h2>
                    </div>
                    <div class="co-header-actions-inline">
                        <a class="co-see-all" href="<?php echo e(\App\Filament\Resources\ItemControles\ItemControleResource::getUrl('index')); ?>">Ver todos</a>
                        <button type="button" class="co-mobile-toggle" @click="open = ! open" :aria-expanded="open.toString()">
                            <i class="bi" :class="open ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                            <span x-text="open ? 'Ocultar' : 'Mostrar'"></span>
                        </button>
                    </div>
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
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($row['responsavel_id'])): ?>
                                    <button type="button" class="co-mini-action dark" wire:click="openWorkloadModal(<?php echo e((int) $row['responsavel_id']); ?>)" wire:loading.attr="disabled"><i class="bi bi-eye"></i>Detalhes</button>
                                    <button type="button" class="co-mini-action" wire:click="openWorkloadModal(<?php echo e((int) $row['responsavel_id']); ?>)" wire:loading.attr="disabled"><i class="bi bi-arrow-left-right"></i>Redistribuir</button>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <a class="co-mini-action" href="<?php echo e($row['open_url'] ?? \App\Filament\Resources\ItemControles\ItemControleResource::getUrl('index')); ?>"><i class="bi bi-list-task"></i>Abrir tarefas</a>
                            </div>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="co-empty clean"><strong>Nenhuma carga pendente.</strong></div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </section>
        <?php elseif($activeTab === 'aprovacoes'): ?>
            <section class="co-panel co-approvals-panel co-detail-panel-large co-mobile-collapsible" x-data="{ open: true }" :class="{ 'is-open': open }">
                <header class="co-panel-header">
                    <div class="co-heading-with-icon">
                        <span class="co-section-icon blue"><i class="bi bi-file-earmark-check"></i></span>
                        <h2>Aprovações</h2>
                    </div>
                    <div class="co-header-actions-inline">
                        <a class="co-see-all" href="<?php echo e(\App\Filament\Resources\ItemControles\ItemControleResource::getUrl('index')); ?>">Ver todas</a>
                        <button type="button" class="co-mobile-toggle" @click="open = ! open" :aria-expanded="open.toString()">
                            <i class="bi" :class="open ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                            <span x-text="open ? 'Ocultar' : 'Mostrar'"></span>
                        </button>
                    </div>
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
                                <button type="button" class="co-mini-action dark" wire:click="openItemDetailModal(<?php echo e($item['id']); ?>, 'resolver')" wire:loading.attr="disabled" wire:target="openItemDetailModal(<?php echo e($item['id']); ?>, 'resolver')">
                                    <i class="bi bi-eye"></i>Detalhes
                                </button>
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
            <section class="co-panel co-financial-panel co-detail-panel-large co-mobile-collapsible" x-data="{ open: true }" :class="{ 'is-open': open }">
                <header class="co-panel-header">
                    <div class="co-heading-with-icon">
                        <span class="co-section-icon green"><i class="bi bi-cash-coin"></i></span>
                        <h2>Pendências Financeiras</h2>
                    </div>
                    <div class="co-header-actions-inline">
                        <button type="button" class="co-see-all as-button" wire:click="abrirPendenciasFinanceiras">Filtrar financeiro</button>
                        <button type="button" class="co-mobile-toggle" @click="open = ! open" :aria-expanded="open.toString()">
                            <i class="bi" :class="open ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                            <span x-text="open ? 'Ocultar' : 'Mostrar'"></span>
                        </button>
                    </div>
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

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($detailModalOpen): ?>
            <?php $detail = $this->selectedItemDetail(); ?>
            <div class="co-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="co-operational-detail-title" wire:click.self="closeItemDetailModal">
                <div class="co-modal-card co-detail-modal-card">
                    <button type="button" class="co-modal-close-btn" wire:click="closeItemDetailModal" aria-label="Fechar popup">
                        <i class="bi bi-x-lg"></i>
                    </button>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($detail): ?>
                        <header>
                            <span class="co-section-icon blue"><i class="bi bi-file-earmark-check"></i></span>
                            <div>
                                <h3 id="co-operational-detail-title">Detalhes do item operacional</h3>
                                <p><?php echo e($detail['empresa']); ?> • <?php echo e($detail['categoria']); ?></p>
                            </div>
                        </header>

                        <div class="co-detail-modal-body">
                            <div class="co-detail-main-info">
                                <span class="co-priority-badge warning"><?php echo e($detail['prioridade']); ?></span>
                                <h4><?php echo e($detail['title']); ?></h4>
                                <p><?php echo e($detail['descricao']); ?></p>
                            </div>

                            <div class="co-decision-box <?php echo e($detail['suggestion']['tone'] ?? 'info'); ?>">
                                <div>
                                    <small>Sugestão operacional</small>
                                    <strong><?php echo e($detail['suggestion']['title'] ?? 'Avaliar item antes de decidir'); ?></strong>
                                    <p><?php echo e($detail['suggestion']['text'] ?? 'Confira status, responsável, vencimento, histórico e checklist antes de aprovar, reprovar ou solicitar correção.'); ?></p>
                                </div>
                                <span><?php echo e($detail['suggestion']['primary_action'] ?? 'Decidir com contexto'); ?></span>
                            </div>

                            <div class="co-detail-grid">
                                <div><small>Status</small><strong><?php echo e($detail['status']); ?></strong></div>
                                <div><small>Responsável</small><strong><?php echo e($detail['responsavel']); ?></strong></div>
                                <div><small>Vencimento</small><strong><?php echo e($detail['vencimento']); ?></strong><em><?php echo e($detail['dias_prazo'] ?? ''); ?></em></div>
                                <div><small>Valor/Impacto</small><strong><?php echo e($detail['valor']); ?></strong></div>
                                <div><small>Conclusão</small><strong><?php echo e($detail['conclusao']); ?></strong></div>
                                <div><small>Origem</small><strong>Operação Interna</strong></div>
                            </div>

                            <div class="co-detail-insights-grid">
                                <section class="co-detail-insight-card">
                                    <h4><i class="bi bi-clock-history"></i>Últimas movimentações</h4>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($detail['timeline'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <article>
                                            <strong><?php echo e($entry['titulo']); ?></strong>
                                            <span><?php echo e($entry['tipo']); ?> • <?php echo e($entry['data']); ?></span>
                                            <p><?php echo e($entry['descricao']); ?></p>
                                        </article>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                        <div class="co-empty clean small"><strong>Sem histórico operacional ainda.</strong></div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </section>

                                <section class="co-detail-insight-card">
                                    <h4><i class="bi bi-check2-square"></i>Checklist / próximas etapas</h4>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($detail['checklist'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $check): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <article class="co-checkline <?php echo e($check['concluido'] ? 'done' : ''); ?>">
                                            <i class="bi <?php echo e($check['concluido'] ? 'bi-check-circle-fill' : 'bi-circle'); ?>"></i>
                                            <strong><?php echo e($check['titulo']); ?></strong>
                                        </article>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                        <div class="co-empty clean small"><strong>Nenhum checklist cadastrado.</strong></div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </section>
                            </div>
                        </div>

                        <footer class="co-detail-footer-actions">
                            <button type="button" class="co-action-btn muted" wire:click="closeItemDetailModal">Fechar</button>
                            <a class="co-action-btn info" href="<?php echo e($detail['url']); ?>"><i class="bi bi-box-arrow-up-right"></i>Abrir cadastro</a>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($detail['actions']['approve'] ?? false)): ?>
                                <button type="button" class="co-action-btn success" wire:click="aprovar(<?php echo e($detail['id']); ?>)" wire:loading.attr="disabled"><i class="bi bi-check2-circle"></i>Aprovar</button>
                                <button type="button" class="co-action-btn danger" wire:click="reprovar(<?php echo e($detail['id']); ?>)" wire:loading.attr="disabled"><i class="bi bi-x-lg"></i>Reprovar</button>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($detail['actions']['correct'] ?? false) && ! $detail['is_closed']): ?>
                                <button type="button" class="co-action-btn warning" wire:click="enviarParaCorrecao(<?php echo e($detail['id']); ?>)" wire:loading.attr="disabled"><i class="bi bi-tools"></i>Solicitar correção</button>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($detail['actions']['delegate'] ?? false) && ! $detail['is_closed']): ?>
                                <button type="button" class="co-action-btn purple" wire:click="openDelegateModal(<?php echo e($detail['id']); ?>)" wire:loading.attr="disabled"><i class="bi bi-person-plus"></i>Delegar</button>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </footer>
                    <?php else: ?>
                        <header>
                            <span class="co-section-icon red"><i class="bi bi-exclamation-triangle"></i></span>
                            <div>
                                <h3 id="co-operational-detail-title">Item não encontrado</h3>
                                <p>O item pode ter sido atualizado, removido ou estar fora do seu escopo.</p>
                            </div>
                        </header>
                        <footer><button type="button" class="co-action-btn muted" wire:click="closeItemDetailModal">Fechar</button></footer>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($workloadModalOpen): ?>
            <?php
                $workloadDetail = $this->selectedWorkloadDetail();
                $workloadTotal = (int) ($workloadDetail['total'] ?? 0);
                $workloadCritical = (int) ($workloadDetail['critical'] ?? 0);
                $workloadLate = (int) ($workloadDetail['late'] ?? 0);
                $workloadOpenSoon = max($workloadTotal - $workloadLate, 0);
                $workloadPercent = min(100, max(12, ($workloadTotal * 8) + ($workloadCritical * 7) + ($workloadLate * 5)));
                $workloadAvailableHours = 34 - ($workloadTotal + ($workloadCritical * 2));
                $workloadMainItem = $workloadDetail['items'][0] ?? null;
                $workloadResponsavelName = $workloadDetail['responsavel']->nome ?? 'Responsável';
                $workloadRole = $workloadDetail['responsavel']->cargo ?? $workloadDetail['responsavel']->funcao ?? 'Responsável operacional';
                $workloadDepartment = $workloadDetail['responsavel']->departamento ?? $workloadDetail['responsavel']->area ?? 'Equipe';
                $workloadSince = $workloadDetail['responsavel']->created_at ?? null;
                $workloadSinceLabel = $workloadSince ? $workloadSince->format('m/Y') : now()->subYear()->format('m/Y');
                $workloadImpactMoney = number_format(max(450, ($workloadLate * 280) + ($workloadCritical * 190)), 2, ',', '.');
                $workloadImpactedClients = collect($workloadDetail['items'] ?? [])->pluck('empresa')->filter()->unique()->count();
                $workloadByCategory = collect($workloadDetail['items'] ?? [])->groupBy('categoria')->map->count()->sortDesc()->take(5);
                $workloadClients = collect($workloadDetail['items'] ?? [])->groupBy('empresa')->map->count()->sortDesc()->take(5);
                $workloadDelegable = collect($workloadDetail['items'] ?? [])->take(4);
                $workloadDays = collect(range(0, 6))->map(fn ($day) => [
                    'label' => now()->addDays($day)->format('d/m'),
                    'value' => min(100, max(25, $workloadPercent - 18 + ($day * 6) - ($day % 2 ? 8 : 0))),
                ]);
            ?>
            <div class="co-modal-backdrop co-workload-v3-backdrop" role="dialog" aria-modal="true" aria-labelledby="co-workload-detail-title" wire:click.self="closeWorkloadModal">
                <div class="co-modal-card co-workload-v3-modal">
                    <button type="button" class="co-modal-close-btn co-workload-v3-x" wire:click="closeWorkloadModal" aria-label="Fechar popup">
                        <i class="bi bi-x-lg"></i>
                    </button>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($workloadDetail['responsavel']): ?>
                        <header class="co-workload-v3-header">
                            <div class="co-workload-v3-title-row">
                                <span class="co-workload-v3-icon"><i class="bi bi-people-fill"></i></span>
                                <div>
                                    <h3 id="co-workload-detail-title">Workload da Equipe</h3>
                                    <div class="co-workload-v3-person">
                                        <strong><?php echo e($workloadResponsavelName); ?></strong>
                                        <span><?php echo e($workloadDepartment); ?></span>
                                    </div>
                                    <p><?php echo e($workloadRole); ?> • Desde <?php echo e($workloadSinceLabel); ?></p>
                                </div>
                            </div>
                            <div class="co-workload-v3-header-actions">
                                <button type="button">Ver perfil completo</button>
                                <button type="button" aria-label="Mais opções"><i class="bi bi-three-dots"></i></button>
                            </div>
                        </header>

                        <section class="co-workload-v3-kpis">
                            <article class="co-workload-v3-kpi gauge-card">
                                <span>Carga de Trabalho</span>
                                <div class="co-workload-v3-gauge" style="--value: <?php echo e($workloadPercent); ?>;"><strong><?php echo e($workloadPercent); ?>%</strong></div>
                                <b class="<?php echo e($workloadPercent >= 85 ? 'danger' : 'ok'); ?>"><?php echo e($workloadPercent >= 85 ? 'Alta' : 'Controlada'); ?></b>
                                <small>Ideal: até 85%</small>
                            </article>
                            <article class="co-workload-v3-kpi">
                                <span>Tarefas Abertas</span>
                                <strong><?php echo e($workloadTotal); ?></strong>
                                <p><b><?php echo e($workloadLate); ?></b> vencidas</p>
                                <p><b><?php echo e($workloadOpenSoon); ?></b> a vencer</p>
                            </article>
                            <article class="co-workload-v3-kpi">
                                <span>Obrigações sob responsabilidade</span>
                                <strong><?php echo e($workloadTotal + $workloadCritical); ?></strong>
                                <p><b><?php echo e($workloadLate); ?></b> vencidas</p>
                                <p><b><?php echo e(max(($workloadTotal + $workloadCritical) - $workloadLate, 0)); ?></b> a vencer</p>
                            </article>
                            <article class="co-workload-v3-kpi">
                                <span>Prazo mais próximo</span>
                                <strong><?php echo e($workloadMainItem['is_late'] ?? false ? 'VENCIDO' : ($workloadMainItem['vencimento'] ?? 'Sem prazo')); ?></strong>
                                <p><?php echo e($workloadMainItem['title'] ?? 'Nenhuma tarefa pendente'); ?></p>
                            </article>
                            <article class="co-workload-v3-kpi">
                                <span>Folga disponível</span>
                                <strong><?php echo e($workloadAvailableHours >= 0 ? '+' : ''); ?><?php echo e($workloadAvailableHours); ?>h</strong>
                                <p><?php echo e($workloadAvailableHours < 0 ? 'Déficit de capacidade' : 'Capacidade disponível'); ?></p>
                            </article>
                            <article class="co-workload-v3-kpi impact">
                                <span>Impacto se não agir</span>
                                <strong><?php echo e(max($workloadLate + $workloadCritical, 1)); ?> obrigações podem atrasar</strong>
                                <p>R$ <?php echo e($workloadImpactMoney); ?></p>
                                <small><?php echo e(max($workloadImpactedClients, 1)); ?> clientes impactados</small>
                                <small><?php echo e($workloadCritical); ?> tarefas críticas</small>
                            </article>
                        </section>

                        <section class="co-workload-v3-grid three">
                            <article class="co-workload-v3-panel">
                                <h4>Distribuição da Carga</h4>
                                <div class="co-workload-v3-loadbar">
                                    <span class="red" style="width: <?php echo e(min(60, max(18, $workloadLate * 12))); ?>%"></span>
                                    <span class="yellow" style="width: <?php echo e(min(45, max(22, $workloadCritical * 10))); ?>%"></span>
                                    <span class="green"></span>
                                </div>
                                <div class="co-workload-v3-legend"><span><i class="red"></i>Acima da capacidade</span><span><i class="yellow"></i>No limite</span><span><i class="green"></i>Folga</span></div>
                            </article>
                            <article class="co-workload-v3-panel">
                                <h4>Principais demandas</h4>
                                <div class="co-workload-v3-demand-list">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $workloadByCategory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category => $amount): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <div><span><?php echo e($category ?: 'Operacional'); ?></span><b><?php echo e($amount); ?></b><em class="<?php echo e($amount >= 4 ? 'high' : ($amount >= 2 ? 'mid' : 'low')); ?>"><?php echo e($amount >= 4 ? 'Alta' : ($amount >= 2 ? 'Média' : 'Baixa')); ?></em></div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                        <div><span>Sem demandas abertas</span><b>0</b><em class="low">Baixa</em></div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </article>
                            <article class="co-workload-v3-panel danger-panel">
                                <h4>Tarefas críticas vencidas</h4>
                                <div class="co-workload-v3-critical-list">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = collect($workloadDetail['items'] ?? [])->filter(fn ($task) => $task['is_late'] ?? false)->take(4); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <div><i class="bi bi-exclamation-triangle-fill"></i><span><?php echo e($task['title']); ?></span><small><?php echo e($task['dias_prazo'] ?? 'Vencida'); ?></small></div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                        <div><i class="bi bi-check-circle-fill"></i><span>Nenhuma tarefa vencida</span><small>Equipe em dia</small></div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </article>
                        </section>

                        <section class="co-workload-v3-grid three middle">
                            <article class="co-workload-v3-panel wide-chart">
                                <h4>Carga por dia <small>próximos 7 dias</small></h4>
                                <div class="co-workload-v3-chart">
                                    <div class="ideal-line"><span>Capacidade ideal (85%)</span></div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $workloadDays; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <div class="bar-wrap"><span style="height: <?php echo e($day['value']); ?>%"></span><small><?php echo e($day['label']); ?></small></div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </div>
                            </article>
                            <article class="co-workload-v3-panel">
                                <h4>Clientes com maior demanda</h4>
                                <div class="co-workload-v3-clients">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $workloadClients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $client => $amount): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <div><span><?php echo e($client ?: 'Sem empresa'); ?></span><b><?php echo e($amount); ?> tarefa(s)</b></div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                        <div><span>Nenhum cliente listado</span><b>0 tarefa</b></div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </article>
                            <article class="co-workload-v3-panel suggestion-panel">
                                <h4>Sugestões para equilibrar carga</h4>
                                <div><i class="bi bi-arrow-left-right"></i><span>Redistribuir tarefas de alta prioridade</span></div>
                                <div><i class="bi bi-person-check"></i><span>Delegar revisões operacionais</span></div>
                                <div><i class="bi bi-calendar-check"></i><span>Antecipar atividades próximas</span></div>
                            </article>
                        </section>

                        <section class="co-workload-v3-grid bottom">
                            <article class="co-workload-v3-panel delegable-panel">
                                <h4>Tarefas que podem ser delegadas</h4>
                                <div class="co-workload-v3-table">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $workloadDelegable; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <div>
                                            <label><input type="checkbox" value="<?php echo e($task['id']); ?>" wire:click="$set('redistributionItemId', <?php echo e((int) $task['id']); ?>)"><span><?php echo e($task['title']); ?></span></label>
                                            <em><?php echo e($task['prioridade']); ?></em>
                                            <button type="button" wire:click="$set('redistributionItemId', <?php echo e((int) $task['id']); ?>)">Delegar</button>
                                        </div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                        <div><label><span>Nenhuma tarefa para delegar</span></label><em>Baixa</em><button type="button">Delegar</button></div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </article>
                            <article class="co-workload-v3-panel actions-panel">
                                <h4>Próximas ações recomendadas</h4>
                                <ol>
                                    <li><span>1</span>Priorizar tarefas vencidas e críticas.</li>
                                    <li><span>2</span>Redistribuir atividades com menor dependência técnica.</li>
                                    <li><span>3</span>Revisar prazos dos clientes mais impactados.</li>
                                    <li><span>4</span>Acompanhar capacidade da equipe no fim do dia.</li>
                                </ol>
                            </article>
                            <article class="co-workload-v3-panel message-panel">
                                <h4>Mensagem para equipe</h4>
                                <textarea readonly>Olá, equipe. Precisamos equilibrar a carga de <?php echo e($workloadResponsavelName); ?>. Existem <?php echo e($workloadLate); ?> tarefas vencidas e <?php echo e($workloadCritical); ?> críticas. Priorizar redistribuição e revisão dos próximos prazos.</textarea>
                                <div><button type="button"><i class="bi bi-copy"></i>Copiar mensagem</button><button type="button"><i class="bi bi-whatsapp"></i>Enviar no WhatsApp</button></div>
                            </article>
                        </section>

                        <div class="co-workload-v3-redistribution">
                            <div>
                                <h4>Redistribuir sem sair da tela</h4>
                                <p>Selecione uma tarefa e o novo responsável para executar a redistribuição.</p>
                            </div>
                            <label>
                                <span>Tarefa</span>
                                <select wire:model.live="redistributionItemId">
                                    <option value="">Selecione...</option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $workloadDetail['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <option value="<?php echo e($task['id']); ?>"><?php echo e($task['title']); ?> — <?php echo e($task['empresa']); ?></option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </select>
                            </label>
                            <label>
                                <span>Novo responsável</span>
                                <select wire:model.live="redistributionResponsavelId">
                                    <option value="">Selecione...</option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->delegateResponsavelOptions(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $responsavelId => $responsavelNome): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <option value="<?php echo e($responsavelId); ?>"><?php echo e($responsavelNome); ?></option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </select>
                            </label>
                        </div>

                        <footer class="co-workload-v3-footer">
                            <div><i class="bi bi-lightbulb-fill"></i><span><b>Dica:</b> mantenha a carga abaixo de 85% para evitar gargalos operacionais.</span></div>
                            <button type="button" class="secondary" wire:click="closeWorkloadModal">Fechar</button>
                            <button type="button" class="primary" wire:click="redistribuirItemSelecionado" wire:loading.attr="disabled"><i class="bi bi-arrow-left-right"></i>Redistribuir selecionada</button>
                        </footer>
                    <?php else: ?>
                        <header class="co-workload-v3-header">
                            <div class="co-workload-v3-title-row">
                                <span class="co-workload-v3-icon danger"><i class="bi bi-exclamation-triangle"></i></span>
                                <div>
                                    <h3 id="co-workload-detail-title">Responsável não encontrado</h3>
                                    <p>Não foi possível carregar o workload selecionado.</p>
                                </div>
                            </div>
                        </header>
                        <footer class="co-workload-v3-footer single"><button type="button" class="secondary" wire:click="closeWorkloadModal">Fechar</button></footer>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($delegateModalOpen): ?>
            <div class="co-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="co-delegate-title" wire:click.self="cancelDelegateModal">
                <div class="co-modal-card">
                    <button type="button" class="co-modal-close-btn" wire:click="cancelDelegateModal" aria-label="Fechar popup">
                        <i class="bi bi-x-lg"></i>
                    </button>
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
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views/filament/pages/centro-operacional-gestao.blade.php ENDPATH**/ ?>