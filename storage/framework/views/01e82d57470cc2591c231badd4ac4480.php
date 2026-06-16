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

    <style>
        .pz-page{display:flex;flex-direction:column;gap:18px}.pz-hero{border-radius:24px;padding:22px;background:linear-gradient(135deg,#0f172a,#1e293b);color:white;display:grid;grid-template-columns:1fr auto;gap:16px;align-items:start}.pz-hero h1{font-size:28px;font-weight:900;margin:2px 0}.pz-hero p{opacity:.82;margin:0;max-width:850px}.pz-kicker{font-size:12px;font-weight:900;letter-spacing:.14em;opacity:.72}.pz-actions{display:flex;gap:8px;flex-wrap:wrap}.pz-btn{border:0;border-radius:12px;padding:9px 12px;font-weight:800;cursor:pointer;background:#e2e8f0;color:#0f172a}.pz-btn:hover{filter:brightness(.96)}.pz-btn.primary{background:#22c55e;color:#052e16}.pz-btn.warn{background:#fed7aa;color:#7c2d12}.pz-btn.danger{background:#fee2e2;color:#991b1b}.pz-btn.small{padding:6px 8px;font-size:12px}.pz-card{border:1px solid rgba(148,163,184,.25);border-radius:20px;background:rgba(255,255,255,.86);padding:16px;box-shadow:0 12px 28px rgba(15,23,42,.06)}.dark .pz-card{background:rgba(15,23,42,.72)}.pz-grid{display:grid;gap:14px}.pz-grid.four{grid-template-columns:repeat(4,minmax(0,1fr))}.pz-grid.three{grid-template-columns:repeat(3,minmax(0,1fr))}.pz-grid.two{grid-template-columns:repeat(2,minmax(0,1fr))}.pz-stat span{font-size:12px;opacity:.68}.pz-stat strong{display:block;font-size:28px;line-height:1.1}.pz-stat small{opacity:.7}.pz-filter{display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:10px}.pz-input,.pz-select{width:100%;border:1px solid rgba(148,163,184,.35);border-radius:12px;padding:9px 11px;background:transparent}.pz-help{border-radius:18px;padding:14px;background:rgba(59,130,246,.08);border:1px solid rgba(59,130,246,.18)}.pz-help strong{display:block;margin-bottom:4px}.pz-space{display:grid;grid-template-columns:210px 1fr 50px;align-items:center;gap:10px;margin-bottom:10px}.pz-spacebar{height:10px;border-radius:99px;background:rgba(148,163,184,.22);overflow:hidden}.pz-spacebar i{display:block;height:100%;background:#22c55e}.pz-row{display:grid;grid-template-columns:330px 1fr 176px;gap:12px;align-items:center;padding:11px;border:1px solid rgba(148,163,184,.24);border-radius:18px;position:relative}.pz-row.critical{border-color:#ef4444;background:rgba(239,68,68,.055)}.pz-row.blocked{box-shadow:inset 5px 0 0 #f59e0b}.pz-task-title{font-weight:900;display:flex;gap:8px;align-items:center}.pz-task-title .diamond{color:#a855f7}.pz-meta{font-size:12px;opacity:.72;margin-top:4px}.pz-track{height:42px;border-radius:14px;background:repeating-linear-gradient(90deg,rgba(148,163,184,.14) 0,rgba(148,163,184,.14) 1px,transparent 1px,transparent 8%);position:relative;overflow:hidden}.pz-baseline{position:absolute;height:8px;top:4px;border-radius:99px;background:rgba(15,23,42,.34)}.dark .pz-baseline{background:rgba(255,255,255,.42)}.pz-bar{position:absolute;top:16px;height:21px;border-radius:99px;background:#2563eb;min-width:24px;box-shadow:0 7px 16px rgba(37,99,235,.28)}.pz-bar.critical{background:#ef4444;box-shadow:0 7px 16px rgba(239,68,68,.28)}.pz-bar.done{background:#22c55e;box-shadow:0 7px 16px rgba(34,197,94,.22)}.pz-progress{height:100%;border-radius:99px;background:rgba(255,255,255,.42)}.pz-pills{display:flex;gap:6px;flex-wrap:wrap;margin-top:8px}.pz-pill{font-size:11px;border-radius:999px;padding:3px 7px;background:rgba(148,163,184,.22);font-weight:700}.pz-pill.red{background:#fee2e2;color:#991b1b}.pz-pill.orange{background:#ffedd5;color:#9a3412}.pz-pill.green{background:#dcfce7;color:#166534}.pz-pill.purple{background:#f3e8ff;color:#6b21a8}.pz-dep{display:grid;grid-template-columns:1fr auto;gap:10px;padding:10px 0;border-bottom:1px solid rgba(148,163,184,.18)}.pz-empty{padding:20px;text-align:center;opacity:.68;border:1px dashed rgba(148,163,184,.45);border-radius:16px}.pz-section-title{font-weight:900;margin:0 0 10px}.pz-flow{display:flex;gap:8px;align-items:center;flex-wrap:wrap}.pz-flow span{font-size:12px;font-weight:800;border-radius:999px;padding:4px 8px;background:rgba(148,163,184,.18)}@media(max-width:1150px){.pz-hero,.pz-grid.four,.pz-grid.three,.pz-grid.two,.pz-filter,.pz-row,.pz-space{grid-template-columns:1fr}.pz-actions{justify-content:flex-start}}
    </style>

    <div class="pz-page">
        <section class="pz-hero">
            <div>
                <div class="pz-kicker">GANTT · ESTRATÉGIA · INTERDEPENDÊNCIA</div>
                <h1>Gantt Enterprise</h1>
                <p>Use esta tela para responder três perguntas de gestão: quais tarefas atrasam a entrega final, quais tarefas estão bloqueadas por dependência e quanto o cronograma real se afastou da linha de base.</p>
            </div>
            <div class="pz-actions">
                <button class="pz-btn primary" wire:click="saveBaseline" wire:loading.attr="disabled">Salvar baseline atual</button>
                <button class="pz-btn" wire:click="syncBlocked" wire:loading.attr="disabled">Recalcular bloqueios</button>
            </div>
        </section>

        <section class="pz-grid four">
            <article class="pz-card pz-stat"><span>Itens no cronograma</span><strong><?php echo e($stats['items']); ?></strong><small><?php echo e($range['start']); ?> até <?php echo e($range['end']); ?></small></article>
            <article class="pz-card pz-stat"><span>Caminho crítico</span><strong><?php echo e($stats['critical']); ?></strong><small>Vermelho = afeta a entrega final</small></article>
            <article class="pz-card pz-stat"><span>Bloqueados</span><strong><?php echo e($stats['blocked']); ?></strong><small>Dependência aberta ou predecessora pendente</small></article>
            <article class="pz-card pz-stat"><span>Progresso geral</span><strong><?php echo e($stats['progress']); ?>%</strong><small>Média dos itens carregados</small></article>
        </section>

        <section class="pz-card pz-help">
            <strong>Como usar sem treinamento</strong>
            <div class="pz-flow">
                <span>1. Filtre projeto/responsável</span><span>2. Veja vermelho/laranja</span><span>3. Ajuste período ou mova dias</span><span>4. Crie dependência</span><span>5. Salve baseline antes de iniciar</span>
            </div>
        </section>

        <section class="pz-card">
            <div class="pz-filter">
                <input class="pz-input" wire:model.live.debounce.400ms="search" placeholder="Buscar tarefa, empresa, responsável ou descrição...">
                <select class="pz-select" wire:model.live="statusFilter"><option value="todos">Todos</option><option value="abertos">Abertos</option><option value="atrasados">Atrasados</option><option value="concluidos">Concluídos</option></select>
                <select class="pz-select" wire:model.live="empresaFilter"><option value="">Todas as empresas</option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $options['empresas']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $empresa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?><option value="<?php echo e($empresa['id']); ?>"><?php echo e($empresa['nome']); ?></option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?></select>
                <select class="pz-select" wire:model.live="responsavelFilter"><option value="">Todos responsáveis</option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $options['responsaveis']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $responsavel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?><option value="<?php echo e($responsavel['id']); ?>"><?php echo e($responsavel['nome']); ?></option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?></select>
            </div>
        </section>

        <section class="pz-grid two">
            <article class="pz-card">
                <h3 class="pz-section-title">Multi-projeto / empresa</h3>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $spaces; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $space): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <div class="pz-space">
                        <div><strong><?php echo e($space['name']); ?></strong><div class="pz-meta"><?php echo e($space['total']); ?> itens · <?php echo e($space['late']); ?> atrasados · <?php echo e($space['critical']); ?> críticos</div></div>
                        <div class="pz-spacebar"><i style="width:<?php echo e($space['progress']); ?>%"></i></div>
                        <strong><?php echo e($space['progress']); ?>%</strong>
                    </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <div class="pz-empty">Nenhuma empresa encontrada para os filtros atuais.</div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </article>

            <article class="pz-card">
                <h3 class="pz-section-title">Alterar período de uma tarefa</h3>
                <div class="pz-grid two">
                    <select class="pz-select" wire:model="windowItemId"><option value="">Tarefa</option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $options['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?><option value="<?php echo e($item['id']); ?>">#<?php echo e($item['id']); ?> · <?php echo e($item['titulo']); ?></option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?></select>
                    <div></div>
                    <input class="pz-input" type="date" wire:model="windowStart">
                    <input class="pz-input" type="date" wire:model="windowEnd">
                </div>
                <div class="pz-actions" style="margin-top:10px"><button class="pz-btn primary" wire:click="updateWindow">Aplicar período</button></div>
                <p class="pz-meta">Atualiza início/fim, vencimento e empurra dependentes se necessário.</p>
            </article>
        </section>

        <section class="pz-card">
            <h3 class="pz-section-title">Criar dependência entre tarefas</h3>
            <div class="pz-filter">
                <select class="pz-select" wire:model="dependencyItemId"><option value="">Tarefa bloqueada</option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $options['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?><option value="<?php echo e($item['id']); ?>">#<?php echo e($item['id']); ?> · <?php echo e($item['titulo']); ?></option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?></select>
                <select class="pz-select" wire:model="dependencyDependsOnId"><option value="">Depende de</option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $options['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?><option value="<?php echo e($item['id']); ?>">#<?php echo e($item['id']); ?> · <?php echo e($item['titulo']); ?></option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?></select>
                <select class="pz-select" wire:model="dependencyType"><option value="finish_to_start">Fim → início</option><option value="start_to_start">Início → início</option><option value="finish_to_finish">Fim → fim</option><option value="bloqueia">Bloqueia</option></select>
                <input class="pz-input" wire:model="dependencyNotes" placeholder="Observação">
            </div>
            <div class="pz-actions" style="margin-top:10px"><button class="pz-btn primary" wire:click="createDependency">Criar dependência funcional</button></div>
        </section>

        <section class="pz-card">
            <h3 class="pz-section-title">Cronograma</h3>
            <p class="pz-meta" style="margin-bottom:12px">Barra cinza fina = baseline. Barra colorida = cronograma atual. Vermelho = caminho crítico. Laranja lateral = bloqueio por dependência.</p>
            <div class="pz-grid">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <div class="pz-row <?php echo e($row['critical'] ? 'critical' : ''); ?> <?php echo e($row['is_blocked'] ? 'blocked' : ''); ?>">
                        <div>
                            <div class="pz-task-title"><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($row['is_milestone']): ?><span class="diamond">◆</span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?> <span>#<?php echo e($row['id']); ?> · <?php echo e($row['titulo']); ?></span></div>
                            <div class="pz-meta"><?php echo e($row['empresa']); ?> · <?php echo e($row['responsavel']); ?> · <?php echo e($row['gantt_start']); ?> → <?php echo e($row['gantt_end']); ?></div>
                            <div class="pz-pills">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($row['critical']): ?><span class="pz-pill red">Caminho crítico</span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($row['is_blocked']): ?><span class="pz-pill orange">Bloqueado</span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($row['done']): ?><span class="pz-pill green">Concluído</span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($row['is_milestone']): ?><span class="pz-pill purple">Marco</span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <span class="pz-pill">Folga: <?php echo e($row['slack_days']); ?>d</span>
                                <span class="pz-pill"><?php echo e($row['progress']); ?>%</span>
                            </div>
                        </div>
                        <div class="pz-track" title="<?php echo e($row['gantt_start']); ?> até <?php echo e($row['gantt_end']); ?>">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! is_null($row['baseline_left_percent'])): ?><div class="pz-baseline" style="left:<?php echo e($row['baseline_left_percent']); ?>%;width:<?php echo e($row['baseline_width_percent']); ?>%"></div><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <div class="pz-bar <?php echo e($row['critical'] ? 'critical' : ''); ?> <?php echo e($row['done'] ? 'done' : ''); ?>" style="left:<?php echo e($row['left_percent']); ?>%;width:<?php echo e($row['width_percent']); ?>%"><div class="pz-progress" style="width:<?php echo e($row['progress']); ?>%"></div></div>
                        </div>
                        <div class="pz-actions">
                            <button class="pz-btn small" wire:click="moveTask(<?php echo e($row['id']); ?>, -1)">Voltar 1d</button>
                            <button class="pz-btn small" wire:click="moveTask(<?php echo e($row['id']); ?>, 1)">Adiantar 1d</button>
                            <button class="pz-btn small warn" wire:click="moveTask(<?php echo e($row['id']); ?>, 7)">Empurrar 7d</button>
                            <button class="pz-btn small" wire:click="toggleMilestone(<?php echo e($row['id']); ?>)"><?php echo e($row['is_milestone'] ? 'Remover marco' : 'Virar marco'); ?></button>
                        </div>
                    </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <div class="pz-empty">Nenhuma tarefa encontrada.</div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </section>

        <section class="pz-card">
            <h3 class="pz-section-title">Dependências ativas</h3>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $dependencies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dependency): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <div class="pz-dep">
                    <div><strong>#<?php echo e($dependency['item_controle_id']); ?> <?php echo e($dependency['atual']); ?></strong><div class="pz-meta">depende de #<?php echo e($dependency['depends_on_item_controle_id']); ?> <?php echo e($dependency['depende']); ?> · <?php echo e($dependency['type']); ?></div></div>
                    <button class="pz-btn small danger" wire:click="removeDependency(<?php echo e($dependency['id']); ?>)">Remover</button>
                </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <div class="pz-empty">Ainda não existem dependências cadastradas.</div>
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
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views\filament\pages\gantt-enterprise.blade.php ENDPATH**/ ?>