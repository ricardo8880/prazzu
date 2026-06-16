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

    <link rel="stylesheet" href="<?php echo e(asset('css/prazzu-enterprise-80.css')); ?>">

    <style>
        .prazzu-timeline-op { display:grid; gap:1rem; }
        .prazzu-timeline-hero { display:flex; justify-content:space-between; gap:1rem; padding:1.25rem; border-radius:1.25rem; background:linear-gradient(135deg, rgba(17,24,39,.96), rgba(49,46,129,.9)); color:white; }
        .prazzu-timeline-kicker { font-size:.72rem; letter-spacing:.16em; text-transform:uppercase; opacity:.72; font-weight:900; }
        .prazzu-timeline-hero h1 { margin:.25rem 0; font-size:1.75rem; font-weight:900; }
        .prazzu-timeline-hero p { max-width:58rem; margin:0; opacity:.86; }
        .prazzu-timeline-actions { display:flex; flex-wrap:wrap; gap:.5rem; align-content:flex-start; justify-content:flex-end; }
        .prazzu-timeline-actions span { border:1px solid rgba(199,210,254,.34); background:rgba(255,255,255,.1); padding:.5rem .7rem; border-radius:999px; font-size:.78rem; font-weight:800; white-space:nowrap; }
        .prazzu-timeline-stats { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:.8rem; }
        .prazzu-timeline-stat, .prazzu-timeline-card { background:white; border:1px solid rgba(148,163,184,.28); border-radius:1.1rem; box-shadow:0 12px 30px rgba(15,23,42,.06); padding:1rem; }
        .prazzu-timeline-stat span { color:#64748b; font-size:.78rem; font-weight:900; text-transform:uppercase; }
        .prazzu-timeline-stat strong { display:block; font-size:1.7rem; margin:.3rem 0; line-height:1.15; }
        .prazzu-timeline-stat small { color:#64748b; }
        .prazzu-timeline-grid { display:grid; grid-template-columns:2fr 1fr; gap:1rem; }
        .prazzu-timeline-card header { display:flex; justify-content:space-between; gap:1rem; margin-bottom:.9rem; }
        .prazzu-timeline-card h2 { margin:0; font-size:1.05rem; font-weight:900; }
        .prazzu-timeline-card p { margin:.2rem 0 0; color:#64748b; font-size:.86rem; }
        .prazzu-timeline-controls { display:flex; gap:.45rem; flex-wrap:wrap; }
        .prazzu-timeline-controls span { border:1px solid #e2e8f0; border-radius:.75rem; padding:.35rem .55rem; font-size:.74rem; font-weight:800; color:#334155; }
        .prazzu-timeline-controls span.active { background:#312e81; color:white; border-color:#312e81; }
        .prazzu-timeline-board { overflow-x:auto; display:grid; gap:.7rem; }
        .prazzu-timeline-scale { display:grid; grid-template-columns:180px repeat(7, minmax(80px, 1fr)); min-width:820px; gap:.5rem; color:#64748b; font-size:.72rem; font-weight:900; text-transform:uppercase; }
        .prazzu-timeline-lane { display:grid; grid-template-columns:180px 1fr; gap:.7rem; align-items:stretch; min-width:820px; }
        .prazzu-timeline-owner { border:1px solid #e2e8f0; background:#f8fafc; border-radius:.9rem; padding:.7rem; }
        .prazzu-timeline-owner strong { display:block; color:#0f172a; }
        .prazzu-timeline-owner span { display:block; color:#64748b; font-size:.75rem; margin-top:.15rem; }
        .prazzu-timeline-load { height:7px; background:#e2e8f0; border-radius:999px; overflow:hidden; margin-top:.45rem; }
        .prazzu-timeline-load i { display:block; height:100%; background:#4f46e5; }
        .prazzu-timeline-track { position:relative; min-height:76px; border:1px solid #edf2f7; border-radius:.95rem; background:repeating-linear-gradient(90deg,#f8fafc 0,#f8fafc 13.7%,#eef2f7 14.2%); overflow:hidden; }
        .prazzu-timeline-task { position:absolute; top:12px; min-width:68px; height:48px; border-radius:.85rem; background:#4f46e5; color:white; padding:.45rem .55rem; box-shadow:0 10px 20px rgba(79,70,229,.22); overflow:hidden; }
        .prazzu-timeline-task.late { background:#dc2626; }
        .prazzu-timeline-task.blocked { background:#7c3aed; }
        .prazzu-timeline-task strong { display:block; font-size:.72rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .prazzu-timeline-task span { display:block; font-size:.65rem; opacity:.88; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .prazzu-timeline-columns { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:1rem; }
        .prazzu-timeline-list { display:grid; gap:.65rem; }
        .prazzu-timeline-note { border:1px solid #e2e8f0; background:#f8fafc; border-radius:.9rem; padding:.75rem; }
        .prazzu-timeline-note strong { display:block; color:#0f172a; }
        .prazzu-timeline-note p { margin:.2rem 0 0; color:#64748b; font-size:.8rem; }
        .prazzu-timeline-drop { border:1px dashed #a5b4fc; background:#eef2ff; border-radius:.9rem; padding:.7rem; color:#3730a3; font-weight:800; }
        .prazzu-milestone { display:grid; grid-template-columns:auto 1fr; gap:.65rem; align-items:start; }
        .prazzu-milestone .diamond { width:16px; height:16px; background:#f59e0b; transform:rotate(45deg); border-radius:.2rem; margin-top:.25rem; }
        .prazzu-timeline-feed { position:relative; display:grid; gap:.7rem; padding-left:1rem; }
        .prazzu-timeline-feed:before { content:""; position:absolute; left:.25rem; top:.25rem; bottom:.25rem; width:2px; background:#e2e8f0; }
        .prazzu-timeline-feed .prazzu-timeline-note { position:relative; }
        .prazzu-timeline-feed .prazzu-timeline-note:before { content:""; position:absolute; left:-1.08rem; top:.95rem; width:.55rem; height:.55rem; border-radius:999px; background:#4f46e5; }
        .prazzu-timeline-empty { padding:1rem; border:1px dashed #cbd5e1; border-radius:.9rem; color:#64748b; text-align:center; }
        @media (max-width:1024px) { .prazzu-timeline-stats, .prazzu-timeline-grid, .prazzu-timeline-columns { grid-template-columns:1fr; } .prazzu-timeline-hero { flex-direction:column; } }
    </style>

    <div class="prazzu-timeline-op">
        <section class="prazzu-timeline-hero">
            <div>
                <span class="prazzu-timeline-kicker"><?php echo e($config['group'] ?? 'TRABALHO'); ?></span>
                <h1><?php echo e($config['title'] ?? 'Timeline Operacional'); ?></h1>
                <p><?php echo e($config['subtitle'] ?? ''); ?></p>
            </div>
            <div class="prazzu-timeline-actions">
                <span>Swimlanes por pessoa</span>
                <span>Tarefas não agendadas</span>
                <span>Marcos</span>
                <span>Zoom dia/semana</span>
            </div>
        </section>

        <section class="prazzu-timeline-stats">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($stats ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <article class="prazzu-timeline-stat">
                    <span><?php echo e($stat['label']); ?></span>
                    <strong><?php echo e($stat['value']); ?></strong>
                    <small><?php echo e($stat['hint']); ?></small>
                </article>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </section>

        <section class="prazzu-timeline-grid">
            <article class="prazzu-timeline-card">
                <header>
                    <div><h2>Agenda operacional por responsável</h2><p>Swimlanes para enxergar quem está sobrecarregado, ocioso ou com sobreposição.</p></div>
                    <div class="prazzu-timeline-controls">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($zoom ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?><span class="<?php echo e(($item['active'] ?? false) ? 'active' : ''); ?>"><?php echo e($item['label']); ?></span><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>
                </header>

                <div class="prazzu-timeline-board">
                    <div class="prazzu-timeline-scale"><span>Responsável</span><span>07h</span><span>09h</span><span>11h</span><span>13h</span><span>15h</span><span>17h</span><span>19h</span></div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($lanes ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lane): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="prazzu-timeline-lane">
                            <div class="prazzu-timeline-owner">
                                <strong><?php echo e($lane['owner']); ?></strong>
                                <span><?php echo e($lane['state']); ?> · <?php echo e(count($lane['tasks'] ?? [])); ?> tarefas</span>
                                <div class="prazzu-timeline-load"><i style="width: <?php echo e($lane['load']); ?>%;"></i></div>
                            </div>
                            <div class="prazzu-timeline-track">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($lane['tasks'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <div class="prazzu-timeline-task <?php echo e($task['is_late'] ? 'late' : ''); ?> <?php echo e($task['is_blocked'] ? 'blocked' : ''); ?>" style="left: <?php echo e($task['left']); ?>%; width: <?php echo e($task['width']); ?>%;" title="<?php echo e($task['start_label']); ?> até <?php echo e($task['end_label']); ?>">
                                        <strong><?php echo e($task['title']); ?></strong>
                                        <span><?php echo e($task['project']); ?> · <?php echo e($task['start_label']); ?></span>
                                    </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </div>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="prazzu-timeline-empty">Nenhuma tarefa aberta para montar a timeline.</div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </article>

            <aside class="prazzu-timeline-card">
                <header><div><h2>Filtros dinâmicos</h2><p>Foco no que está aberto, em revisão ou em risco.</p></div></header>
                <div class="prazzu-timeline-controls">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($filters ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $filter): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?><span class="<?php echo e(($filter['active'] ?? false) ? 'active' : ''); ?>"><?php echo e($filter['label']); ?></span><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
                <br>
                <div class="prazzu-timeline-list">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($overlaps ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $overlap): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="prazzu-timeline-note">
                            <strong><?php echo e($overlap['owner']); ?></strong>
                            <p>Sobreposição: <?php echo e($overlap['first']); ?> + <?php echo e($overlap['second']); ?></p>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="prazzu-timeline-empty">Nenhuma sobreposição crítica encontrada.</div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </aside>
        </section>

        <section class="prazzu-timeline-columns">
            <article class="prazzu-timeline-card">
                <header><div><h2>Scheduling</h2><p>Tarefas não agendadas prontas para arrastar para alguém.</p></div></header>
                <div class="prazzu-timeline-list">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($unscheduled ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="prazzu-timeline-drop">↳ <?php echo e($task['title']); ?> · <?php echo e(ucfirst($task['priority'])); ?> · <?php echo e($task['project']); ?></div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="prazzu-timeline-empty">Nenhuma tarefa sem agendamento.</div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </article>

            <article class="prazzu-timeline-card">
                <header><div><h2>Marcos</h2><p>Datas críticas destacadas como diamantes.</p></div></header>
                <div class="prazzu-timeline-list">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($milestones ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $milestone): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="prazzu-timeline-note prazzu-milestone">
                            <span class="diamond"></span>
                            <div>
                                <strong><?php echo e($milestone['title']); ?></strong>
                                <p><?php echo e($milestone['date']); ?> · <?php echo e($milestone['project']); ?> · <?php echo e($milestone['owner']); ?></p>
                            </div>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="prazzu-timeline-empty">Nenhum marco encontrado.</div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </article>

            <article class="prazzu-timeline-card">
                <header><div><h2>Execução do dia</h2><p>Timeline real de comentários, auditoria, documentos e SLA.</p></div></header>
                <div class="prazzu-timeline-feed">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = array_slice(($events ?? []), 0, 8); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="prazzu-timeline-note">
                            <strong><?php echo e($event['title']); ?></strong>
                            <p><?php echo e($event['description']); ?></p>
                            <p><?php echo e($event['type']); ?> · <?php echo e($event['owner']); ?> · <?php echo e($event['created_label']); ?></p>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="prazzu-timeline-empty">Nenhum evento registrado.</div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views\filament\pages\timeline-operacional-enterprise.blade.php ENDPATH**/ ?>