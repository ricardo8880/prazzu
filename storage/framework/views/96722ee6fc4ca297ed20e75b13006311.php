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

    <link rel="stylesheet" href="<?php echo e(asset('css/trabalho-pages.css')); ?>">

    <?php
        $resumo = $this->getResumo();
        $projetos = $this->getProjetos();
        $recentes = $this->getRecentes();
        $projetoSelecionado = $this->getProjetoSelecionado();
    ?>

    <div class="tp-page tp-projects-page">
        <div class="tp-hero">
            <div>
                <span class="tp-eyebrow">TRABALHO</span>
                <h2>Projetos</h2>
                <p>Hub operacional dos projetos, com progresso, prazos, responsáveis, checklist, comentários, riscos e ações rápidas.</p>
            </div>

            <div class="tp-actions">
                <a href="<?php echo e($this->getUrlNovaTarefa()); ?>" class="tp-btn">Novo item</a>
                <a href="<?php echo e($this->getUrlTarefas()); ?>" class="tp-btn-secondary">Cadastro completo</a>
            </div>
        </div>

        <div class="tp-metrics tp-metrics-5">
            <div class="tp-card">
                <span>Total</span>
                <strong><?php echo e($resumo['total']); ?></strong>
                <small>itens nos filtros</small>
            </div>

            <div class="tp-card">
                <span>Ativos</span>
                <strong><?php echo e($resumo['ativos']); ?></strong>
                <small>em execução</small>
            </div>

            <div class="tp-card tp-danger">
                <span>Atrasados</span>
                <strong><?php echo e($resumo['atrasados']); ?></strong>
                <small>exigem atenção</small>
            </div>

            <div class="tp-card">
                <span>Hoje</span>
                <strong><?php echo e($resumo['hoje']); ?></strong>
                <small>vencem hoje</small>
            </div>

            <div class="tp-card tp-success">
                <span>Concluídos</span>
                <strong><?php echo e($resumo['concluidos']); ?></strong>
                <small>finalizados</small>
            </div>
        </div>

        <div class="tp-project-filter-bar">
            <div class="tp-filter-field tp-filter-search">
                <label>Buscar</label>
                <input type="search" wire:model.live.debounce.400ms="busca" placeholder="Projeto, item, cliente ou responsável">
            </div>

            <div class="tp-filter-field">
                <label>Status</label>
                <select wire:model.live="filtroStatus">
                    <option value="todos">Todos</option>
                    <option value="pendente">Pendente</option>
                    <option value="em_andamento">Em andamento</option>
                    <option value="concluidos">Concluídos</option>
                    <option value="atrasados">Atrasados</option>
                    <option value="cancelado">Cancelado</option>
                </select>
            </div>

            <div class="tp-filter-field">
                <label>Prioridade</label>
                <select wire:model.live="filtroPrioridade">
                    <option value="todos">Todas</option>
                    <option value="baixa">Baixa</option>
                    <option value="media">Média</option>
                    <option value="alta">Alta</option>
                    <option value="urgente">Urgente</option>
                </select>
            </div>

            <button type="button" class="tp-filter-clear" wire:click="limparFiltros">Limpar filtros</button>
        </div>

        <div class="tp-project-board">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $projetos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $projeto): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <button type="button" class="tp-project-card" wire:click="abrirProjeto('<?php echo e(addslashes($projeto['tipo'])); ?>')">
                    <div class="tp-project-card-head">
                        <div>
                            <span>Projeto / modalidade</span>
                            <strong><?php echo e($projeto['nome']); ?></strong>
                        </div>
                        <em><?php echo e($projeto['percentual']); ?>%</em>
                    </div>

                    <div class="tp-progress">
                        <i style="width: <?php echo e($projeto['percentual']); ?>%"></i>
                    </div>

                    <div class="tp-project-kpis">
                        <div>
                            <b><?php echo e($projeto['total']); ?></b>
                            <span>itens</span>
                        </div>

                        <div>
                            <b><?php echo e($projeto['ativos']); ?></b>
                            <span>ativos</span>
                        </div>

                        <div class="<?php echo e($projeto['atrasados'] > 0 ? 'is-danger' : ''); ?>">
                            <b><?php echo e($projeto['atrasados']); ?></b>
                            <span>atrasos</span>
                        </div>

                        <div>
                            <b><?php echo e($projeto['checklist_percentual']); ?>%</b>
                            <span>checklist</span>
                        </div>
                    </div>

                    <div class="tp-project-info-line">
                        <span>Próxima entrega</span>
                        <strong><?php echo e($projeto['proxima_entrega']); ?></strong>
                    </div>

                    <div class="tp-project-info-line">
                        <span>Responsáveis</span>
                        <strong><?php echo e($projeto['responsaveis']); ?></strong>
                    </div>

                    <div class="tp-project-info-line">
                        <span>Clientes</span>
                        <strong><?php echo e($projeto['empresas']); ?></strong>
                    </div>

                    <div class="tp-project-mini-list">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $projeto['itens']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <div class="tp-project-mini-item <?php echo e($item['atrasado'] ? 'is-late' : ''); ?>">
                                <span><?php echo e($item['titulo']); ?></span>
                                <b><?php echo e($item['vencimento']); ?></b>
                            </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>

                    <div class="tp-project-card-footer">
                        <span><?php echo e($projeto['comentarios']); ?> comentários</span>
                        <strong>Abrir painel</strong>
                    </div>
                </button>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <div class="tp-empty tp-empty-large">Nenhum projeto encontrado com os filtros atuais.</div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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

             <?php $__env->slot('heading', null, []); ?> Últimos itens movimentados <?php $__env->endSlot(); ?>

            <div class="tp-list">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $recentes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <button type="button" class="tp-list-row tp-list-button" wire:click="abrirProjeto('<?php echo e(addslashes($item['status_original'] === '' ? 'sem_tipo' : strtolower(str_replace(' ', '_', $item['tipo'])))); ?>')">
                        <div class="tp-list-main">
                            <strong><?php echo e($item['titulo']); ?></strong>
                            <span><?php echo e($item['tipo']); ?> • <?php echo e($item['empresa']); ?> • <?php echo e($item['responsavel']); ?></span>
                        </div>

                        <div class="tp-list-side">
                            <span class="tp-badge <?php echo e($item['atrasado'] ? 'tp-badge-danger' : ''); ?>"><?php echo e($item['status']); ?></span>
                            <b><?php echo e($item['vencimento']); ?></b>
                        </div>
                    </button>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <div class="tp-empty">Nenhum item recente encontrado.</div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($projetoSelecionado): ?>
            <div class="tp-modal-backdrop" wire:click.self="fecharProjeto">
                <div class="tp-project-modal">
                    <div class="tp-project-modal-head">
                        <div>
                            <span>PAINEL DO PROJETO</span>
                            <h3><?php echo e($projetoSelecionado['nome']); ?></h3>
                            <p><?php echo e($projetoSelecionado['total']); ?> itens • <?php echo e($projetoSelecionado['concluidos']); ?> concluídos • <?php echo e($projetoSelecionado['atrasados']); ?> atrasados</p>
                        </div>

                        <button type="button" wire:click="fecharProjeto">×</button>
                    </div>

                    <div class="tp-project-modal-progress">
                        <strong><?php echo e($projetoSelecionado['percentual']); ?>%</strong>
                        <div class="tp-progress">
                            <i style="width: <?php echo e($projetoSelecionado['percentual']); ?>%"></i>
                        </div>
                    </div>

                    <div class="tp-project-modal-grid">
                        <div class="tp-project-modal-items">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $projetoSelecionado['itens']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <button type="button" class="tp-project-task <?php echo e($item['id'] === ($projetoSelecionado['item_selecionado']['id'] ?? null) ? 'is-active' : ''); ?> <?php echo e($item['atrasado'] ? 'is-late' : ''); ?>" wire:click="selecionarItem(<?php echo e($item['id']); ?>)">
                                    <strong><?php echo e($item['titulo']); ?></strong>
                                    <span><?php echo e($item['empresa']); ?> • <?php echo e($item['responsavel']); ?></span>
                                    <small><?php echo e($item['status']); ?> • <?php echo e($item['vencimento']); ?></small>
                                </button>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($projetoSelecionado['item_selecionado']): ?>
                            <?php ($item = $projetoSelecionado['item_selecionado']); ?>

                            <div class="tp-project-detail">
                                <div class="tp-project-detail-head">
                                    <div>
                                        <span class="tp-badge <?php echo e($item['atrasado'] ? 'tp-badge-danger' : ''); ?>"><?php echo e($item['status']); ?></span>
                                        <h4><?php echo e($item['titulo']); ?></h4>
                                        <p><?php echo e($item['empresa']); ?> • <?php echo e($item['responsavel']); ?></p>
                                    </div>

                                    <a href="<?php echo e($item['url']); ?>" class="tp-btn-dark">Abrir cadastro completo</a>
                                </div>

                                <div class="tp-project-detail-grid">
                                    <div>
                                        <span>Prioridade</span>
                                        <strong><?php echo e($item['prioridade']); ?></strong>
                                    </div>

                                    <div>
                                        <span>Vencimento</span>
                                        <strong><?php echo e($item['vencimento']); ?></strong>
                                    </div>

                                    <div>
                                        <span>Conclusão</span>
                                        <strong><?php echo e($item['data_conclusao']); ?></strong>
                                    </div>

                                    <div>
                                        <span>Checklist</span>
                                        <strong><?php echo e($item['checklist_percentual']); ?>%</strong>
                                    </div>
                                </div>

                                <div class="tp-project-description">
                                    <strong>Descrição</strong>
                                    <p><?php echo e($item['descricao_completa']); ?></p>

                                    <strong>Observação</strong>
                                    <p><?php echo e($item['observacao']); ?></p>
                                </div>

                                <div class="tp-project-actions-row">
                                    <button type="button" wire:click="alterarStatusItem(<?php echo e($item['id']); ?>, 'pendente')">Pendente</button>
                                    <button type="button" wire:click="alterarStatusItem(<?php echo e($item['id']); ?>, 'em_andamento')">Em andamento</button>
                                    <button type="button" wire:click="alterarStatusItem(<?php echo e($item['id']); ?>, 'concluido')">Concluir</button>
                                </div>

                                <div class="tp-project-detail-columns">
                                    <div class="tp-project-box">
                                        <div class="tp-project-box-head">
                                            <strong>Checklist</strong>
                                            <span><?php echo e($item['checklist_concluidos']); ?>/<?php echo e($item['checklist_total']); ?></span>
                                        </div>

                                        <div class="tp-project-checks">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $item['checklists']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $checklist): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                                <button type="button" class="tp-project-check <?php echo e($checklist['concluido'] ? 'is-done' : ''); ?>" wire:click="alternarChecklist(<?php echo e($checklist['id']); ?>)">
                                                    <i><?php echo e($checklist['concluido'] ? '✓' : ''); ?></i>
                                                    <span><?php echo e($checklist['titulo']); ?></span>
                                                </button>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                                <div class="tp-empty">Nenhuma etapa cadastrada.</div>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>

                                        <div class="tp-inline-form">
                                            <input type="text" wire:model.defer="novaEtapa" placeholder="Nova etapa do checklist">
                                            <button type="button" wire:click="adicionarEtapa">Adicionar</button>
                                        </div>
                                    </div>

                                    <div class="tp-project-box">
                                        <div class="tp-project-box-head">
                                            <strong>Comentários</strong>
                                            <span><?php echo e($item['comentarios_total']); ?></span>
                                        </div>

                                        <div class="tp-project-comments">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $item['comentarios']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comentario): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                                <div>
                                                    <strong><?php echo e($comentario['autor']); ?></strong>
                                                    <p><?php echo e($comentario['comentario']); ?></p>
                                                    <small><?php echo e($comentario['data']); ?></small>
                                                </div>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                                <div class="tp-empty">Nenhum comentário nesse item.</div>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>

                                        <div class="tp-inline-form tp-inline-form-column">
                                            <textarea wire:model.defer="novoComentario" placeholder="Escreva um comentário rápido"></textarea>
                                            <button type="button" wire:click="adicionarComentario">Comentar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
<?php endif; ?><?php /**PATH C:\xampp\htdocs\prazzu\resources\views\filament\pages\projetos.blade.php ENDPATH**/ ?>