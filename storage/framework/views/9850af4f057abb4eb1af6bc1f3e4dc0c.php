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

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    <link rel="stylesheet" href="<?php echo e(asset('css/trabalho-pages.css')); ?>?v=20260513-lote5-empty-states">
    <link rel="stylesheet" href="<?php echo e(asset('css/tarefas-qa-standard.css')); ?>?v=20260513-lote7-visual">

    <?php
        $colunas = $this->getColunas();
        $itemSelecionado = $this->getItemSelecionado();
        $totalItensKanban = collect($colunas)->sum('total');
    ?>

    <div class="tp-page">
        <div class="tp-action-loading" wire:loading.flex wire:target="abrirItem,fecharItem,atualizarStatus,moverItemKanban,alternarChecklist,adicionarChecklist,adicionarComentario">
            <span class="tp-spinner"></span>
            <span>Processando alteração...</span>
        </div>
        <div class="tp-hero">
            <div>
                <span class="tp-eyebrow">TRABALHO</span>
                <h2>Kanban Operacional</h2>
                <p>Quadro de execução com modal de detalhes, checklist rápido, comentários e mudança de status sem precisar sair da tela.</p>
            </div>

            <div class="tp-actions">
                <a href="<?php echo e($this->getUrlNovaTarefa()); ?>" class="tp-btn">Novo item</a>
            </div>
        </div>

        <div class="tp-kanban-toolbar">
            <div>
                <strong>Fluxo rápido</strong>
                <span>Arraste os cards entre colunas para mudar o status. Clique no card para abrir o painel do item.</span>
            </div>
            <div class="tp-kanban-legend">
                <span><i class="tp-dot tp-dot-danger"></i> Vencido</span>
                <span><i class="tp-dot tp-dot-warning"></i> Checklist pendente</span>
                <span><i class="tp-dot tp-dot-success"></i> Concluído</span>
            </div>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($totalItensKanban === 0): ?>
            <div class="tp-empty tp-empty-large tp-empty-actionable">
                <div class="tp-empty-icon">▦</div>
                <strong>Nenhum item no Kanban</strong>
                <p>Assim que uma tarefa for criada, ela aparecerá aqui separada por status. Use o botão abaixo para começar o fluxo operacional.</p>
                <a href="<?php echo e($this->getUrlNovaTarefa()); ?>" class="tp-empty-link">Criar primeiro item</a>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="tp-kanban">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $colunas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $coluna): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <div class="tp-kanban-column tp-kanban-<?php echo e($coluna['status']); ?>" data-kanban-column="<?php echo e($coluna['status']); ?>">
                    <div class="tp-kanban-header">
                        <div>
                            <strong><?php echo e($coluna['label']); ?></strong>
                            <small><?php echo e($coluna['total']); ?> item(ns)</small>
                        </div>
                        <span><?php echo e($coluna['total']); ?></span>
                    </div>

                    <div class="tp-kanban-cards" data-kanban-list="<?php echo e($coluna['status']); ?>">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $coluna['itens']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <button type="button" wire:click="abrirItem(<?php echo e($item['id']); ?>)" wire:loading.attr="disabled" wire:target="abrirItem(<?php echo e($item['id']); ?>)" data-kanban-card="<?php echo e($item['id']); ?>" draggable="true" class="tp-kanban-card <?php if($item['vencido']): ?> is-late <?php endif; ?>">
                                <div class="tp-kanban-card-top">
                                    <strong><?php echo e($item['titulo']); ?></strong>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item['vencido']): ?>
                                        <span class="tp-mini-badge tp-mini-danger">Vencido</span>
                                    <?php elseif(($item['status_normalized'] ?? $item['status_raw']) === 'concluido'): ?>
                                        <span class="tp-mini-badge tp-mini-success">Concluído</span>
                                    <?php else: ?>
                                        <span class="tp-mini-badge"><?php echo e($item['prioridade'] ?: 'Normal'); ?></span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>

                                <span><?php echo e($item['tipo']); ?> • <?php echo e($item['empresa']); ?></span>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item['descricao']): ?>
                                    <small><?php echo e($item['descricao']); ?></small>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                <div class="tp-kanban-progress">
                                    <div>
                                        <small>Checklist</small>
                                        <b><?php echo e($item['checklists_concluidos']); ?>/<?php echo e($item['checklists_total']); ?></b>
                                    </div>
                                    <div class="tp-progress"><i style="width: <?php echo e($item['checklists_percentual']); ?>%"></i></div>
                                </div>

                                <div class="tp-kanban-foot">
                                    <em><?php echo e($item['responsavel']); ?></em>
                                    <b><?php echo e($item['vencimento']); ?></b>
                                </div>

                                <div class="tp-kanban-meta">
                                    <span><?php echo e($item['comentarios']); ?> comentário(s)</span>
                                    <span>Ver painel</span>
                                </div>
                            </button>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <div class="tp-empty tp-empty-column">
                                <strong>Coluna sem itens</strong>
                                <span>Arraste um card para cá ou altere o status pelo painel do item.</span>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($itemSelecionado): ?>
        <div class="tp-modal-backdrop" <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'kanban-modal-'.e($itemSelecionado['id']).''; ?>wire:key="kanban-modal-<?php echo e($itemSelecionado['id']); ?>">
            <div class="tp-modal">
                <div class="tp-modal-head">
                    <div>
                        <span class="tp-eyebrow-dark">ITEM DO KANBAN</span>
                        <h3><?php echo e($itemSelecionado['titulo']); ?></h3>
                        <p><?php echo e($itemSelecionado['empresa']); ?> • <?php echo e($itemSelecionado['responsavel']); ?></p>
                    </div>
                    <button type="button" wire:click="fecharItem" wire:loading.attr="disabled" wire:target="fecharItem" class="tp-modal-close">×</button>
                </div>

                <div class="tp-modal-grid">
                    <div class="tp-modal-main">
                        <div class="tp-detail-card">
                            <div class="tp-detail-title">
                                <strong>Resumo</strong>
                                <span class="tp-badge <?php if($itemSelecionado['vencido']): ?> tp-badge-danger <?php endif; ?>"><?php echo e($itemSelecionado['status']); ?></span>
                            </div>

                            <div class="tp-detail-text">
                                <?php echo nl2br(e($itemSelecionado['descricao_completa'] ?? 'Sem descrição cadastrada.')); ?>

                            </div>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($itemSelecionado['observacao']): ?>
                                <div class="tp-note">
                                    <strong>Observação</strong>
                                    <span><?php echo nl2br(e($itemSelecionado['observacao'])); ?></span>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <div class="tp-detail-card">
                            <div class="tp-detail-title">
                                <strong>Checklist rápido</strong>
                                <span><?php echo e($itemSelecionado['checklists_concluidos']); ?>/<?php echo e($itemSelecionado['checklists_total']); ?></span>
                            </div>

                            <div class="tp-progress tp-progress-large"><i style="width: <?php echo e($itemSelecionado['checklists_percentual']); ?>%"></i></div>

                            <div class="tp-checklist-box">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $itemSelecionado['checklists']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $checklist): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <button type="button" wire:click="alternarChecklist(<?php echo e($checklist['id']); ?>)" wire:loading.attr="disabled" wire:target="alternarChecklist(<?php echo e($checklist['id']); ?>)" class="tp-checkline <?php if($checklist['concluido']): ?> is-done <?php endif; ?>">
                                        <i><?php echo e($checklist['concluido'] ? '✓' : '○'); ?></i>
                                        <span><?php echo e($checklist['titulo']); ?></span>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($checklist['concluido_em']): ?>
                                            <small><?php echo e($checklist['concluido_em']); ?></small>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </button>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    <div class="tp-empty tp-empty-small">
                                        <strong>Checklist ainda vazio</strong>
                                        <span>Adicione a primeira etapa no campo abaixo para acompanhar a execução.</span>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>

                            <form wire:submit.prevent="adicionarChecklist" class="tp-inline-form">
                                <input type="text" wire:model.defer="novoChecklistTitulo" wire:loading.attr="disabled" wire:target="adicionarChecklist" placeholder="Nova etapa do checklist">
                                <button type="submit" wire:loading.attr="disabled" wire:target="adicionarChecklist">
                                    <span wire:loading.remove wire:target="adicionarChecklist">Adicionar</span>
                                    <span wire:loading.inline-flex wire:target="adicionarChecklist" class="tp-inline-loading"><i class="tp-spinner"></i> Salvando</span>
                                </button>
                            </form>
                        </div>

                        <div class="tp-detail-card">
                            <div class="tp-detail-title">
                                <strong>Comentários</strong>
                                <span><?php echo e($itemSelecionado['comentarios']); ?></span>
                            </div>

                            <div class="tp-comments-box">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $itemSelecionado['comentarios_lista']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comentario): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <div class="tp-comment">
                                        <strong><?php echo e($comentario['autor']); ?></strong>
                                        <p><?php echo e($comentario['comentario']); ?></p>
                                        <small><?php echo e($comentario['data']); ?></small>
                                    </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    <div class="tp-empty tp-empty-small">
                                        <strong>Sem comentários</strong>
                                        <span>Registre uma observação para manter o histórico claro para a equipe.</span>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>

                            <form wire:submit.prevent="adicionarComentario" class="tp-comment-form">
                                <textarea wire:model.defer="novoComentario" wire:loading.attr="disabled" wire:target="adicionarComentario" rows="3" placeholder="Escreva um comentário rápido"></textarea>
                                <button type="submit" wire:loading.attr="disabled" wire:target="adicionarComentario">
                                    <span wire:loading.remove wire:target="adicionarComentario">Comentar</span>
                                    <span wire:loading.inline-flex wire:target="adicionarComentario" class="tp-inline-loading"><i class="tp-spinner"></i> Salvando</span>
                                </button>
                            </form>
                        </div>
                    </div>

                    <aside class="tp-modal-side">
                        <div class="tp-detail-card">
                            <strong class="tp-side-title">Ações rápidas</strong>
                            <div class="tp-action-stack">
                                <button type="button" wire:click="atualizarStatus(<?php echo e($itemSelecionado['id']); ?>, 'pendente')" wire:loading.attr="disabled" wire:target="atualizarStatus(<?php echo e($itemSelecionado['id']); ?>, 'pendente')">Marcar como pendente</button>
                                <button type="button" wire:click="atualizarStatus(<?php echo e($itemSelecionado['id']); ?>, 'em_andamento')" wire:loading.attr="disabled" wire:target="atualizarStatus(<?php echo e($itemSelecionado['id']); ?>, 'em_andamento')">Mover para andamento</button>
                                <button type="button" wire:click="atualizarStatus(<?php echo e($itemSelecionado['id']); ?>, 'concluido')" wire:loading.attr="disabled" wire:target="atualizarStatus(<?php echo e($itemSelecionado['id']); ?>, 'concluido')">Concluir item</button>
                            </div>
                        </div>

                        <div class="tp-detail-card tp-info-list">
                            <strong class="tp-side-title">Informações</strong>
                            <p><span>Tipo</span><b><?php echo e($itemSelecionado['tipo']); ?></b></p>
                            <p><span>Prioridade</span><b><?php echo e($itemSelecionado['prioridade'] ?: '-'); ?></b></p>
                            <p><span>Vencimento</span><b><?php echo e($itemSelecionado['vencimento']); ?></b></p>
                            <p><span>SLA</span><b><?php echo e($itemSelecionado['sla']); ?></b></p>
                            <p><span>Criado em</span><b><?php echo e($itemSelecionado['data_criacao']); ?></b></p>
                            <p><span>Atualizado em</span><b><?php echo e($itemSelecionado['data_atualizacao']); ?></b></p>
                        </div>

                        <a href="<?php echo e($itemSelecionado['url']); ?>" class="tp-open-record">Abrir cadastro completo</a>
                    </aside>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <script>
        document.addEventListener('livewire:navigated', iniciarKanbanArrastarSoltar);
        document.addEventListener('livewire:initialized', iniciarKanbanArrastarSoltar);
        document.addEventListener('DOMContentLoaded', iniciarKanbanArrastarSoltar);

        let tpKanbanUltimoPonto = { x: 0, y: 0 };

        document.addEventListener('dragover', function (event) {
            tpKanbanUltimoPonto = { x: event.clientX, y: event.clientY };
        }, true);

        document.addEventListener('mousemove', function (event) {
            if (document.body.classList.contains('tp-kanban-is-dragging')) {
                tpKanbanUltimoPonto = { x: event.clientX, y: event.clientY };
            }
        }, true);

        function iniciarKanbanArrastarSoltar() {
            if (typeof Sortable === 'undefined') {
                return;
            }

            document.querySelectorAll('[data-kanban-list]').forEach((lista) => {
                if (lista.dataset.sortableAtivo === '1') {
                    return;
                }

                lista.dataset.sortableAtivo = '1';

                new Sortable(lista, {
                    group: 'prazzu-kanban',
                    animation: 150,
                    draggable: '[data-kanban-card]',
                    ghostClass: 'tp-kanban-card-ghost',
                    chosenClass: 'tp-kanban-card-chosen',
                    dragClass: 'tp-kanban-card-drag',
                    forceFallback: true,
                    fallbackOnBody: true,
                    fallbackTolerance: 1,
                    touchStartThreshold: 1,
                    swapThreshold: 0.18,
                    invertedSwapThreshold: 0.85,
                    emptyInsertThreshold: 140,
                    filter: '.tp-empty',
                    onStart: function () {
                        document.body.classList.add('tp-kanban-is-dragging');
                    },
                    onMove: function (event, originalEvent) {
                        if (originalEvent) {
                            tpKanbanUltimoPonto = { x: originalEvent.clientX, y: originalEvent.clientY };
                        }

                        return true;
                    },
                    onEnd: function (event) {
                        document.body.classList.remove('tp-kanban-is-dragging');

                        const card = event.item;
                        const itemId = Number(card.dataset.kanbanCard || 0);

                        let destino = event.to;
                        const elementoNoPonto = document.elementFromPoint(tpKanbanUltimoPonto.x, tpKanbanUltimoPonto.y);
                        const colunaNoPonto = elementoNoPonto ? elementoNoPonto.closest('[data-kanban-list]') : null;

                        if (colunaNoPonto) {
                            destino = colunaNoPonto;

                            if (card.parentElement !== destino) {
                                destino.appendChild(card);
                            }
                        }

                        const novoStatus = destino.dataset.kanbanList;

                        if (! itemId || ! novoStatus) {
                            return;
                        }

                        if (window.Livewire && window.Livewire.find('<?php echo e($_instance->getId()); ?>')) {
                            window.Livewire.find('<?php echo e($_instance->getId()); ?>').call('moverItemKanban', itemId, novoStatus);
                        }
                    },
                });
            });
        }
    </script>
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
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views\filament\pages\kanban.blade.php ENDPATH**/ ?>