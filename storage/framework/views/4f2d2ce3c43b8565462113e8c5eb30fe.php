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

<div class="pz-page">
        <section class="pz-hero">
            <div>
                <div class="pz-kicker">GANTT · ESTRATÉGIA · INTERDEPENDÊNCIA</div>
                <h1>Cronograma Gantt</h1>
                <p>Visualização de planejamento da operação: mostra dependências, bloqueios, linha de base e impacto no prazo final. A execução do trabalho permanece na Central Operacional.</p>
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
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views/filament/pages/gantt-enterprise.blade.php ENDPATH**/ ?>