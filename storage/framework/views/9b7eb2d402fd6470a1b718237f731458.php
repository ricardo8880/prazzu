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

    <link rel="stylesheet" href="<?php echo e(asset('css/tarefas-qa-standard.css')); ?>?v=20260513-lote7-visual">

    <div class="tl-page">
        <div class="tp-action-loading" wire:loading.flex wire:target="scheduleSelectedTask,schedulePreset,quickMove,toggleMilestone,updateStatus">
            <span class="tp-spinner"></span>
            <span>Atualizando timeline...</span>
        </div>
        <section class="tl-hero">
            <div class="tl-kicker">TIMELINE · EXECUÇÃO · CAPACIDADE</div>
            <h1>Timeline Operacional</h1>
            <p>Use esta tela para distribuir trabalho no dia a dia: quem faz o quê, quem está sobrecarregado, quais tarefas ainda não foram agendadas e onde existem conflitos de horário.</p>
        </section>

        <section class="tl-grid four">
            <article class="tl-card tl-stat"><span>Tarefas visíveis</span><strong><?php echo e($stats['items']); ?></strong><small>Depois dos filtros</small></article>
            <article class="tl-card tl-stat"><span>Responsáveis</span><strong><?php echo e($stats['responsaveis']); ?></strong><small>Swimlanes ativas</small></article>
            <article class="tl-card tl-stat"><span>Conflitos</span><strong><?php echo e($stats['overlaps']); ?></strong><small>Sobreposições por responsável</small></article>
            <article class="tl-card tl-stat"><span>Atrasadas</span><strong><?php echo e($stats['late'] ?? 0); ?></strong><small>Precisam de ação</small></article>
        </section>

        <section class="tl-card tl-help">
            <strong>Fluxo operacional</strong>
            <div class="tl-flow"><span>1. Veja não agendadas</span><span>2. Agende hoje/amanhã</span><span>3. Confira conflitos em laranja</span><span>4. Ajuste +1/-1 dia</span><span>5. Conclua ou marque como marco</span></div>
        </section>

        <section class="tl-card">
            <div class="tl-filter">
                <input class="tl-input" wire:model.live.debounce.400ms="search" placeholder="Buscar tarefa, cliente, responsável ou descrição...">
                <select class="tl-select" wire:model.live="statusFilter"><option value="todos">Todos</option><option value="abertos">Abertos</option><option value="atrasados">Atrasados</option><option value="concluidos">Concluídos</option></select>
                <select class="tl-select" wire:model.live="responsavelFilter"><option value="">Todos responsáveis</option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $options['responsaveis']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $responsavel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?><option value="<?php echo e($responsavel['id']); ?>"><?php echo e($responsavel['nome']); ?></option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?></select>
                <select class="tl-select" wire:model.live="zoom"><option value="dia">Hoje por hora</option><option value="semana">Semana</option><option value="mes">Mês</option></select>
                <label style="display:flex;gap:8px;align-items:center;font-weight:800"><input type="checkbox" wire:model.live="hideDone"> Ocultar concluídas</label>
            </div>
            <p class="tl-meta">Janela atual: <?php echo e($range['start']); ?> até <?php echo e($range['end']); ?></p>
        </section>

        <section class="tl-grid two">
            <article class="tl-card">
                <h3 class="tl-section-title">Agendamento manual</h3>
                <div class="tl-schedule">
                    <select class="tl-select" wire:model="scheduleItemId" wire:loading.attr="disabled" wire:target="scheduleSelectedTask"><option value="">Selecione uma tarefa</option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $options['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?><option value="<?php echo e($item['id']); ?>">#<?php echo e($item['id']); ?> · <?php echo e($item['titulo']); ?></option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?></select>
                    <input class="tl-input" type="datetime-local" wire:model="scheduleStart" wire:loading.attr="disabled" wire:target="scheduleSelectedTask">
                    <input class="tl-input" type="datetime-local" wire:model="scheduleEnd" wire:loading.attr="disabled" wire:target="scheduleSelectedTask">
                    <button class="tl-btn primary" wire:click="scheduleSelectedTask" wire:loading.attr="disabled" wire:target="scheduleSelectedTask">Agendar</button>
                </div>
                <p class="tl-meta">Grava em <code>item_controles.custom_payload</code> e atualiza vencimento pela data final.</p>
            </article>

            <article class="tl-card">
                <h3 class="tl-section-title">Marcos</h3>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $milestones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $milestone): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <div class="tl-milestone"><span class="tl-diamond"></span><div><strong>#<?php echo e($milestone['id']); ?> · <?php echo e($milestone['titulo']); ?></strong><div class="tl-meta"><?php echo e($milestone['empresa']); ?> · <?php echo e($milestone['timeline_end']); ?></div></div></div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <div class="tl-empty tl-empty-actionable">
                        <strong>Nenhum marco definido</strong>
                        <span>Transforme tarefas importantes em marco para destacar entregas críticas na timeline.</span>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </article>
        </section>

        <section class="tl-card">
            <h3 class="tl-section-title">Tarefas sem agenda</h3>
            <div class="tl-unscheduled">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $unscheduled; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <div class="tl-note">
                        <strong>#<?php echo e($task['id']); ?> · <?php echo e($task['titulo']); ?></strong>
                        <div class="tl-meta"><?php echo e($task['empresa']); ?> · <?php echo e($task['responsavel']); ?> · vencimento <?php echo e($task['gantt_end']); ?></div>
                        <div class="tl-actions">
                            <button class="tl-btn small primary" wire:click="schedulePreset(<?php echo e($task['id']); ?>, 'today')" wire:loading.attr="disabled" wire:target="schedulePreset(<?php echo e($task['id']); ?>, 'today')">Agendar hoje 09:00</button>
                            <button class="tl-btn small" wire:click="schedulePreset(<?php echo e($task['id']); ?>, 'tomorrow')" wire:loading.attr="disabled" wire:target="schedulePreset(<?php echo e($task['id']); ?>, 'tomorrow')">Agendar amanhã</button>
                            <button class="tl-btn small" wire:click="schedulePreset(<?php echo e($task['id']); ?>, 'next_week')" wire:loading.attr="disabled" wire:target="schedulePreset(<?php echo e($task['id']); ?>, 'next_week')">Próxima semana</button>
                        </div>
                    </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <div class="tl-empty tl-empty-positive">
                        <strong>Tudo agendado</strong>
                        <span>Todas as tarefas visíveis já possuem data na timeline.</span>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </section>

        <section class="tl-card">
            <h3 class="tl-section-title">Swimlanes por responsável</h3>
            <div class="tl-grid">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $groups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <div class="tl-lane">
                        <div class="tl-lane-head">
                            <div><strong><?php echo e($group['responsavel']); ?></strong><div class="tl-meta"><?php echo e($group['count']); ?> tarefa(s) · <?php echo e($group['open']); ?> aberta(s) · <?php echo e($group['late']); ?> atrasada(s)</div></div>
                            <div><div class="tl-load"><i style="width:<?php echo e(min(100, $group['load_percent'])); ?>%"></i></div><div class="tl-meta">Carga estimada: <?php echo e($group['load_percent']); ?>%</div></div>
                            <span class="tl-pill <?php echo e($group['overlaps'] > 0 ? 'orange' : 'green'); ?>"><?php echo e($group['overlaps']); ?> conflito(s)</span>
                        </div>
                        <div class="tl-lane-body">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $group['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <div class="tl-task <?php echo e($item['overlapping'] ? 'overlap' : ''); ?> <?php echo e($item['is_late'] ? 'late' : ''); ?> <?php echo e($item['done'] ? 'done' : ''); ?>">
                                    <div>
                                        <div class="tl-title"><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item['is_milestone']): ?><span style="color:#a855f7">◆</span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?> <span>#<?php echo e($item['id']); ?> · <?php echo e($item['titulo']); ?></span></div>
                                        <div class="tl-meta"><?php echo e($item['timeline_start']); ?> → <?php echo e($item['timeline_end']); ?> · <?php echo e($item['empresa']); ?></div>
                                        <div class="tl-tags">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item['overlapping']): ?><span class="tl-pill orange">Sobreposição</span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item['is_late']): ?><span class="tl-pill red">Atrasada</span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item['done']): ?><span class="tl-pill green">Concluída</span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item['is_milestone']): ?><span class="tl-pill purple">Marco</span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            <span class="tl-pill"><?php echo e(ucfirst(str_replace('_',' ', $item['status_normalized']))); ?></span>
                                            <span class="tl-pill"><?php echo e($item['progress']); ?>%</span>
                                        </div>
                                    </div>
                                    <div class="tl-track"><div class="tl-bar <?php echo e($item['overlapping'] ? 'overlap' : ''); ?> <?php echo e($item['done'] ? 'done' : ''); ?>" style="left:<?php echo e($item['timeline_left_percent']); ?>%;width:<?php echo e($item['timeline_width_percent']); ?>%"></div></div>
                                    <div class="tl-actions">
                                        <button class="tl-btn small" wire:click="quickMove(<?php echo e($item['id']); ?>, -1)" wire:loading.attr="disabled" wire:target="quickMove(<?php echo e($item['id']); ?>, -1)">Voltar 1d</button>
                                        <button class="tl-btn small" wire:click="quickMove(<?php echo e($item['id']); ?>, 1)" wire:loading.attr="disabled" wire:target="quickMove(<?php echo e($item['id']); ?>, 1)">Adiantar 1d</button>
                                        <button class="tl-btn small warn" wire:click="toggleMilestone(<?php echo e($item['id']); ?>)" wire:loading.attr="disabled" wire:target="toggleMilestone(<?php echo e($item['id']); ?>)"><?php echo e($item['is_milestone'] ? 'Remover marco' : 'Virar marco'); ?></button>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $item['done']): ?><button class="tl-btn small primary" wire:click="updateStatus(<?php echo e($item['id']); ?>, 'concluido')" wire:loading.attr="disabled" wire:target="updateStatus(<?php echo e($item['id']); ?>, 'concluido')">Concluir</button><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <div class="tl-empty tl-empty-actionable">
                        <strong>Nenhuma tarefa encontrada</strong>
                        <span>Revise os filtros acima ou crie uma nova tarefa para iniciar o planejamento operacional.</span>
                    </div>
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
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views\filament\pages\timeline-operacional.blade.php ENDPATH**/ ?>