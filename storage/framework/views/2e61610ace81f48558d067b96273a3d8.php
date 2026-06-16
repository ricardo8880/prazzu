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

    <?php
        $percent = (int) ($progress['percent'] ?? 0);
        $empresaNome = $empresa['nome_fantasia'] ?? $empresa['razao_social'] ?? 'Portal do Cliente';
    ?>

    <style>
        .portal-wrap {
            display: grid;
            gap: 1.25rem;
        }

        .portal-hero {
            border-radius: 1.5rem;
            padding: 1.5rem;
            background: linear-gradient(135deg, rgba(15, 23, 42, .96), rgba(30, 64, 175, .90));
            color: white;
            box-shadow: 0 24px 50px rgba(15, 23, 42, .18);
            overflow: hidden;
            position: relative;
        }

        .portal-hero:after {
            content: "";
            position: absolute;
            width: 260px;
            height: 260px;
            right: -90px;
            top: -110px;
            background: rgba(255, 255, 255, .12);
            border-radius: 999px;
        }

        .portal-hero h1 {
            font-size: 1.65rem;
            font-weight: 800;
            margin: 0;
        }

        .portal-hero p {
            margin-top: .35rem;
            color: rgba(255, 255, 255, .82);
            max-width: 740px;
        }

        .portal-grid {
            display: grid;
            gap: 1rem;
        }

        .portal-grid.two {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .portal-grid.three {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .portal-card {
            border-radius: 1.25rem;
            background: white;
            border: 1px solid rgba(148, 163, 184, .25);
            padding: 1.15rem;
            box-shadow: 0 18px 40px rgba(15, 23, 42, .06);
        }

        .dark .portal-card {
            background: rgba(15, 23, 42, .72);
            border-color: rgba(148, 163, 184, .18);
        }

        .portal-card header {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .portal-card h2 {
            font-size: 1rem;
            font-weight: 800;
            margin: 0;
        }

        .portal-card p {
            color: rgb(100, 116, 139);
            font-size: .875rem;
            margin-top: .25rem;
        }

        .dark .portal-card p {
            color: rgb(203, 213, 225);
        }

        .portal-battery-wrap {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 1rem;
            align-items: center;
        }

        .portal-battery {
            height: 2.1rem;
            border-radius: 999px;
            border: 2px solid rgba(15, 23, 42, .16);
            padding: .22rem;
            background: rgba(241, 245, 249, .95);
            position: relative;
        }

        .portal-battery:after {
            content: "";
            width: .45rem;
            height: 1rem;
            background: rgba(15, 23, 42, .28);
            position: absolute;
            right: -.55rem;
            top: 50%;
            transform: translateY(-50%);
            border-radius: 0 .35rem .35rem 0;
        }

        .portal-battery i {
            display: block;
            height: 100%;
            border-radius: 999px;
            background: linear-gradient(90deg, #22c55e, #84cc16);
        }

        .portal-percent {
            font-size: 2rem;
            font-weight: 900;
            color: rgb(22, 101, 52);
        }

        .portal-list {
            display: grid;
            gap: .75rem;
        }

        .portal-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: .85rem;
            border-radius: 1rem;
            background: rgba(248, 250, 252, .95);
            border: 1px solid rgba(226, 232, 240, .9);
        }

        .dark .portal-row {
            background: rgba(30, 41, 59, .72);
            border-color: rgba(148, 163, 184, .18);
        }

        .portal-row strong {
            display: block;
            font-weight: 800;
            font-size: .92rem;
        }

        .portal-row span {
            display: block;
            font-size: .78rem;
            color: rgb(100, 116, 139);
            margin-top: .15rem;
        }

        .dark .portal-row span {
            color: rgb(203, 213, 225);
        }

        .portal-badge {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: .25rem .6rem;
            font-size: .75rem;
            font-weight: 800;
            background: rgba(59, 130, 246, .12);
            color: rgb(37, 99, 235);
            white-space: nowrap;
        }

        .portal-badge.danger {
            background: rgba(239, 68, 68, .12);
            color: rgb(220, 38, 38);
        }

        .portal-badge.ok {
            background: rgba(34, 197, 94, .12);
            color: rgb(22, 163, 74);
        }

        .portal-empty {
            border-radius: 1rem;
            padding: 1rem;
            background: rgba(248, 250, 252, .95);
            border: 1px dashed rgba(148, 163, 184, .6);
            color: rgb(100, 116, 139);
            font-size: .875rem;
        }

        .dark .portal-empty {
            background: rgba(30, 41, 59, .5);
            color: rgb(203, 213, 225);
        }

        .portal-form {
            display: grid;
            gap: .75rem;
        }

        .portal-input,
        .portal-select,
        .portal-textarea {
            width: 100%;
            border-radius: .9rem;
            border: 1px solid rgba(148, 163, 184, .5);
            padding: .75rem .85rem;
            background: white;
            color: rgb(15, 23, 42);
            outline: none;
        }

        .dark .portal-input,
        .dark .portal-select,
        .dark .portal-textarea {
            background: rgba(15, 23, 42, .7);
            color: white;
            border-color: rgba(148, 163, 184, .35);
        }

        .portal-textarea {
            min-height: 110px;
            resize: vertical;
        }

        .portal-btn {
            border-radius: .9rem;
            padding: .75rem 1rem;
            background: rgb(37, 99, 235);
            color: white;
            font-weight: 800;
            border: none;
            cursor: pointer;
        }

        .portal-btn:hover {
            background: rgb(29, 78, 216);
        }

        .portal-note {
            border-left: 4px solid rgba(59, 130, 246, .65);
            padding: .75rem .85rem;
            border-radius: .75rem;
            background: rgba(239, 246, 255, .75);
        }

        .dark .portal-note {
            background: rgba(30, 64, 175, .16);
        }

        .portal-note strong {
            display: block;
            font-weight: 800;
        }

        .portal-note small {
            display: block;
            color: rgb(100, 116, 139);
            margin-top: .2rem;
        }

        .dark .portal-note small {
            color: rgb(203, 213, 225);
        }

        @media (max-width: 1024px) {
            .portal-grid.two,
            .portal-grid.three {
                grid-template-columns: 1fr;
            }

            .portal-battery-wrap {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="portal-wrap">
        <section class="portal-hero">
            <h1><?php echo e($empresaNome); ?></h1>
            <p>
                Acompanhe o andamento do projeto, revise entregas, consulte documentos, veja prazos,
                abra solicitações de suporte e converse com a equipe em um só lugar.
            </p>
        </section>

        <section class="portal-card">
            <header>
                <div>
                    <h2>Progresso do projeto</h2>
                    <p>Battery chart calculado com base nas tarefas reais vinculadas à empresa.</p>
                </div>
                <span class="portal-badge ok"><?php echo e($progress['done'] ?? 0); ?> concluídas</span>
            </header>

            <div class="portal-battery-wrap">
                <div>
                    <div class="portal-battery">
                        <i style="width: <?php echo e($percent); ?>%"></i>
                    </div>

                    <p>
                        <?php echo e($progress['done'] ?? 0); ?> concluída(s),
                        <?php echo e($progress['pending'] ?? 0); ?> pendente(s),
                        <?php echo e($progress['review'] ?? 0); ?> em revisão/aprovação.
                    </p>
                </div>

                <div class="portal-percent"><?php echo e($percent); ?>%</div>
            </div>
        </section>

        
        <section class="portal-grid three">
            <article class="portal-card">
                <header><div><h2>Próxima entrega</h2><p>O próximo prazo importante do projeto.</p></div></header>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($nextDelivery): ?>
                    <div class="portal-note">
                        <strong><?php echo e($nextDelivery['titulo'] ?? 'Entrega'); ?></strong>
                        <small><?php echo e(!empty($nextDelivery['data_vencimento']) ? \Carbon\Carbon::parse($nextDelivery['data_vencimento'])->format('d/m/Y') : '-'); ?></small>
                    </div>
                <?php else: ?>
                    <div class="portal-empty">Nenhuma próxima entrega definida.</div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </article>
            <article class="portal-card">
                <header><div><h2>Timeline do projeto</h2><p>Etapas principais do projeto.</p></div></header>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $timeline; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <div class="portal-row"><strong><?php echo e($step['label']); ?></strong><span class="portal-badge <?php echo e($step['done'] ? 'ok' : ''); ?>"><?php echo e($step['done'] ? 'Concluído' : 'Pendente'); ?></span></div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </article>
            <article class="portal-card">
                <header><div><h2>Histórico de aprovações</h2><p>Itens já concluídos/aprovados.</p></div></header>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $approvalHistory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $approval): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <div class="portal-note"><strong><?php echo e($approval['titulo'] ?? 'Item'); ?></strong></div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <div class="portal-empty">Nenhuma aprovação registrada.</div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </article>
        </section>

        <section class="portal-grid two">
            <article class="portal-card">
                <header>
                    <div>
                        <h2>Pronto para revisão / aprovação</h2>
                        <p>O cliente vê apenas itens liberados para ele, sem tarefas internas da equipe.</p>
                    </div>
                </header>

                <div class="portal-list">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $visibleItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="portal-row">
                            <div>
                                <strong><?php echo e($item['titulo'] ?? 'Item sem título'); ?></strong>
                                <span><?php echo e($item['status_label'] ?? 'Sem status'); ?></span>
                            </div>

                            <span class="portal-badge"><?php echo e($item['progress'] ?? 0); ?>%</span>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="portal-empty">
                            Nenhum item liberado para revisão ou aprovação no momento.
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </article>

            <article class="portal-card">
                <header>
                    <div>
                        <h2>Calendário de entregas</h2>
                        <p>Datas reais de vencimento das entregas vinculadas ao cliente.</p>
                    </div>
                </header>

                <div class="portal-list">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $calendar; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="portal-row">
                            <div>
                                <strong><?php echo e($item['titulo'] ?? 'Entrega'); ?></strong>
                                <span><?php echo e($item['status_label'] ?? 'Sem status'); ?></span>
                            </div>

                            <span class="portal-badge <?php echo e(($item['is_late'] ?? false) ? 'danger' : ''); ?>">
                                <?php echo e(! empty($item['data_vencimento']) ? \Carbon\Carbon::parse($item['data_vencimento'])->format('d/m/Y') : '-'); ?>

                            </span>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="portal-empty">
                            Nenhuma entrega com prazo cadastrado.
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </article>
        </section>

        <section class="portal-grid three">
            <article class="portal-card">
                <header>
                    <div>
                        <h2>Wiki / documentos</h2>
                        <p>Regras do contrato, manuais, arquivos e links úteis.</p>
                    </div>
                </header>

                <div class="portal-list">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $documents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="portal-note">
                            <strong><?php echo e($doc['titulo']); ?></strong>
                            <small><?php echo e(strtoupper($doc['tipo'])); ?></small>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($doc['conteudo'])): ?>
                                <p><?php echo e(\Illuminate\Support\Str::limit(strip_tags($doc['conteudo']), 140)); ?></p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($doc['url'])): ?>
                                <a href="<?php echo e($doc['url']); ?>" target="_blank" rel="noopener noreferrer">Abrir link</a>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="portal-empty">
                            Nenhum documento visível para o cliente.
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </article>

            <article class="portal-card">
                <header>
                    <div>
                        <h2>Atas de reunião</h2>
                        <p>Histórico das decisões tomadas em calls e alinhamentos.</p>
                    </div>
                </header>

                <div class="portal-list">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $meetingNotes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $note): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="portal-note">
                            <strong><?php echo e($note['titulo']); ?></strong>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($note['created_at'])): ?>
                                <small><?php echo e(\Carbon\Carbon::parse($note['created_at'])->format('d/m/Y H:i')); ?></small>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            <p><?php echo e(\Illuminate\Support\Str::limit(strip_tags($note['conteudo'] ?? ''), 150)); ?></p>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="portal-empty">
                            Nenhuma ata de reunião publicada.
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </article>

            <article class="portal-card">
                <header>
                    <div>
                        <h2>Solicitações recentes</h2>
                        <p>Pedidos enviados pelo cliente e acompanhados pela equipe.</p>
                    </div>
                </header>

                <div class="portal-list">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $supportQueue; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $solicitacao): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="portal-row">
                            <div>
                                <strong><?php echo e($solicitacao['titulo']); ?></strong>
                                <span><?php echo e(ucfirst(str_replace('_', ' ', $solicitacao['prioridade']))); ?></span>
                            </div>

                            <span class="portal-badge">
                                <?php echo e(ucfirst(str_replace('_', ' ', $solicitacao['status']))); ?>

                            </span>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="portal-empty">
                            Nenhuma solicitação aberta ainda.
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </article>
        </section>

        <section class="portal-grid two">
            <article class="portal-card">
                <header>
                    <div>
                        <h2>Formulário de suporte / solicitação</h2>
                        <p>O pedido cai direto na fila de trabalho da equipe.</p>
                    </div>
                </header>

                <form wire:submit.prevent="criarSolicitacao" class="portal-form">
                    <div>
                        <input
                            type="text"
                            wire:model.defer="solicitacaoTitulo"
                            class="portal-input"
                            placeholder="Título da solicitação"
                        >
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['solicitacaoTitulo'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <small style="color: rgb(220, 38, 38);"><?php echo e($message); ?></small>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <div>
                        <select wire:model.defer="solicitacaoPrioridade" class="portal-select">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($supportForm['prioridades'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <option value="<?php echo e($value); ?>"><?php echo e($label); ?></option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </select>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['solicitacaoPrioridade'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <small style="color: rgb(220, 38, 38);"><?php echo e($message); ?></small>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <div>
                        <textarea
                            wire:model.defer="solicitacaoDescricao"
                            class="portal-textarea"
                            placeholder="Descreva o pedido de alteração, dúvida ou suporte..."
                        ></textarea>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['solicitacaoDescricao'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <small style="color: rgb(220, 38, 38);"><?php echo e($message); ?></small>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <button type="submit" class="portal-btn" wire:loading.attr="disabled">
                        Enviar solicitação
                    </button>
                </form>
            </article>

            <article class="portal-card">
                <header>
                    <div>
                        <h2>Chat do projeto</h2>
                        <p>Mensagens centralizadas no portal, evitando conversas perdidas no WhatsApp.</p>
                    </div>
                </header>

                <div class="portal-list" style="margin-bottom: 1rem;">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $chat; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="portal-note">
                            <strong><?php echo e($message['nome'] ?? 'Cliente'); ?></strong>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($message['created_at'])): ?>
                                <small><?php echo e(\Carbon\Carbon::parse($message['created_at'])->format('d/m/Y H:i')); ?></small>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            <p><?php echo e($message['mensagem']); ?></p>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="portal-empty">
                            Nenhuma mensagem enviada ainda.
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <form wire:submit.prevent="enviarMensagem" class="portal-form">
                    <textarea
                        wire:model.defer="chatMensagem"
                        class="portal-textarea"
                        placeholder="Escreva uma mensagem para a equipe..."
                    ></textarea>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['chatMensagem'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                    <small style="color: rgb(220, 38, 38);"><?php echo e($message); ?></small>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <button type="submit" class="portal-btn" wire:loading.attr="disabled">
                        Enviar mensagem
                    </button>
                </form>
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
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views\filament\resources\item-controles\pages\portal-cliente.blade.php ENDPATH**/ ?>