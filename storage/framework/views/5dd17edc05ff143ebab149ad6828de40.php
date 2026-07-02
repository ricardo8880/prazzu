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

    <link rel="stylesheet" href="<?php echo e(asset('css/prazzu-operational-tool.css')); ?>?v=<?php echo e(file_exists(public_path('css/prazzu-operational-tool.css')) ? filemtime(public_path('css/prazzu-operational-tool.css')) : time()); ?>">

    <?php
        $cards = $data['cards'] ?? [];
        $sections = $data['sections'] ?? [];
        $quickActions = $data['quickActions'] ?? [];
        $workflow = $data['workflow'] ?? [];
        $timeline = $data['timeline'] ?? [];
        $totalItems = collect($sections)->sum(fn ($section) => count($section['items'] ?? [])) + count($timeline);
        $criticalItems = collect($sections)->flatMap(fn ($section) => $section['items'] ?? [])->filter(fn ($item) => ($item['tone'] ?? null) === 'danger')->count();
        $warningItems = collect($sections)->flatMap(fn ($section) => $section['items'] ?? [])->filter(fn ($item) => ($item['tone'] ?? null) === 'warning')->count();
        $successItems = collect($sections)->flatMap(fn ($section) => $section['items'] ?? [])->filter(fn ($item) => ($item['tone'] ?? null) === 'success')->count();
        $whiteLabel = \App\Support\WhiteLabelSettings::make();
        $enterpriseLabel = strtoupper($whiteLabel->enterpriseLabel());
    ?>

    <div class="prazzu-tool-page" data-prazzu-tool-page>
        <section class="prazzu-tool-hero">
            <div class="prazzu-tool-hero__content">
                <span class="prazzu-tool-eyebrow"><?php echo e($data['group'] ?? $enterpriseLabel); ?></span>
                <h1><?php echo e($data['title'] ?? 'Central operacional'); ?></h1>
                <p><?php echo e($data['subtitle'] ?? 'Ferramenta operacional conectada aos dados reais do sistema.'); ?></p>

                <div class="prazzu-tool-hero__pulse">
                    <span><?php echo e(number_format($totalItems, 0, ',', '.')); ?> registro(s) monitorado(s)</span>
                    <span><?php echo e(number_format($criticalItems, 0, ',', '.')); ?> crítico(s)</span>
                    <span><?php echo e(number_format($warningItems, 0, ',', '.')); ?> em atenção</span>
                </div>
            </div>

            <div class="prazzu-tool-hero__actions">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $quickActions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($action['url'])): ?>
                        <a href="<?php echo e($action['url']); ?>"><?php echo e($action['label']); ?></a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <span>Nenhuma ação rápida disponível</span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </section>

        <section class="prazzu-tool-command">
            <div>
                <strong>Comando rápido da página</strong>
                <p>Filtre os dados desta ferramenta sem sair da tela. A busca funciona em título, empresa, status, descrição e datas.</p>
            </div>
            <div class="prazzu-tool-command__controls">
                <input type="search" placeholder="Buscar nesta ferramenta..." data-tool-search>
                <select data-tool-tone>
                    <option value="all">Todos os status</option>
                    <option value="danger">Crítico</option>
                    <option value="warning">Atenção</option>
                    <option value="success">Resolvido/ativo</option>
                    <option value="info">Informativo</option>
                </select>
            </div>
        </section>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($cards)): ?>
            <section class="prazzu-tool-stats">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $cards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <article class="prazzu-tool-stat <?php echo e($card['tone'] ?? 'info'); ?>">
                        <span><?php echo e($card['label'] ?? '-'); ?></span>
                        <strong><?php echo e($card['value'] ?? 0); ?></strong>
                        <small><?php echo e($card['hint'] ?? 'Indicador operacional'); ?></small>
                    </article>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </section>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <section class="prazzu-tool-board">
            <article class="prazzu-tool-board__card danger">
                <span>Prioridade agora</span>
                <strong><?php echo e(number_format($criticalItems, 0, ',', '.')); ?></strong>
                <p>Itens críticos encontrados nas seções abaixo.</p>
            </article>
            <article class="prazzu-tool-board__card warning">
                <span>Precisa acompanhar</span>
                <strong><?php echo e(number_format($warningItems, 0, ',', '.')); ?></strong>
                <p>Registros em atenção para evitar atraso ou retrabalho.</p>
            </article>
            <article class="prazzu-tool-board__card success">
                <span>Base saudável</span>
                <strong><?php echo e(number_format($successItems, 0, ',', '.')); ?></strong>
                <p>Registros ativos, concluídos ou bem configurados.</p>
            </article>
            <article class="prazzu-tool-board__card info">
                <span>Atalhos úteis</span>
                <strong><?php echo e(count($quickActions)); ?></strong>
                <p>Ações diretas para continuar o trabalho no cadastro correto.</p>
            </article>
        </section>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($workflow)): ?>
            <section class="prazzu-tool-card prazzu-tool-card--wide">
                <div class="prazzu-tool-card__header">
                    <div>
                        <h2><?php echo e($data['workflowTitle'] ?? 'Workflow operacional'); ?></h2>
                        <p><?php echo e($data['workflowDescription'] ?? 'Etapas calculadas com base nos registros existentes para o usuário saber onde agir.'); ?></p>
                    </div>
                </div>

                <div class="prazzu-tool-workflow">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $workflow; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <article data-tool-item data-tone="info" data-search="<?php echo e(\Illuminate\Support\Str::lower(($step['step'] ?? '') . ' ' . ($step['description'] ?? '') . ' ' . ($step['count'] ?? ''))); ?>">
                            <span><?php echo e($loop->iteration); ?></span>
                            <h3><?php echo e($step['step'] ?? '-'); ?></h3>
                            <strong><?php echo e(number_format($step['count'] ?? 0, 0, ',', '.')); ?></strong>
                            <p><?php echo e($step['description'] ?? ''); ?></p>
                        </article>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            </section>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($timeline)): ?>
            <section class="prazzu-tool-card prazzu-tool-card--wide">
                <div class="prazzu-tool-card__header">
                    <div>
                        <h2>Timeline consolidada</h2>
                        <p>Histórico recente em ordem cronológica para auditoria e acompanhamento operacional.</p>
                    </div>
                </div>

                <div class="prazzu-tool-timeline">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $timeline; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <article class="<?php echo e($event['tone'] ?? 'info'); ?>" data-tool-item data-tone="<?php echo e($event['tone'] ?? 'info'); ?>" data-search="<?php echo e(\Illuminate\Support\Str::lower(($event['title'] ?? '') . ' ' . ($event['status'] ?? '') . ' ' . ($event['meta'] ?? '') . ' ' . ($event['description'] ?? '') . ' ' . ($event['date'] ?? ''))); ?>">
                            <div class="prazzu-tool-timeline__dot"></div>
                            <div>
                                <div class="prazzu-tool-item__top">
                                    <h3><?php echo e($event['title'] ?? '-'); ?></h3>
                                    <span><?php echo e($event['status'] ?? '-'); ?></span>
                                </div>
                                <small><?php echo e($event['meta'] ?? '-'); ?> <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($event['date'])): ?> • <?php echo e($event['date']); ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></small>
                                <p><?php echo e($event['description'] ?? 'Sem descrição.'); ?></p>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($event['url'])): ?>
                                    <a class="prazzu-tool-link" href="<?php echo e($event['url']); ?>">Abrir origem</a>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </article>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            </section>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="prazzu-tool-sections">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <section class="prazzu-tool-card">
                    <div class="prazzu-tool-card__header">
                        <div>
                            <h2><?php echo e($section['title'] ?? 'Seção'); ?></h2>
                            <p><?php echo e($section['description'] ?? 'Dados consolidados do módulo.'); ?></p>
                        </div>
                        <span><?php echo e(count($section['items'] ?? [])); ?></span>
                    </div>

                    <div class="prazzu-tool-items">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_2 = true; $__currentLoopData = ($section['items'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <article class="prazzu-tool-item <?php echo e($item['tone'] ?? 'info'); ?>" data-tool-item data-tone="<?php echo e($item['tone'] ?? 'info'); ?>" data-search="<?php echo e(\Illuminate\Support\Str::lower(($item['title'] ?? '') . ' ' . ($item['status'] ?? '') . ' ' . ($item['meta'] ?? '') . ' ' . ($item['description'] ?? '') . ' ' . ($item['date'] ?? ''))); ?>">
                                <div class="prazzu-tool-item__top">
                                    <h3><?php echo e($item['title'] ?? 'Sem título'); ?></h3>
                                    <span><?php echo e($item['status'] ?? '-'); ?></span>
                                </div>
                                <small><?php echo e($item['meta'] ?? '-'); ?> <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($item['date'])): ?> • <?php echo e($item['date']); ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></small>
                                <p><?php echo e($item['description'] ?? 'Sem descrição cadastrada.'); ?></p>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($item['url'])): ?>
                                    <a class="prazzu-tool-link" href="<?php echo e($item['url']); ?>">Resolver / abrir item</a>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </article>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <div class="prazzu-tool-empty">
                                <strong>Nenhum registro encontrado nesta fila.</strong>
                                <p>Quando existirem dados reais nesta categoria, eles aparecerão aqui com prioridade, status e ação direta.</p>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </section>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <section class="prazzu-tool-card prazzu-tool-card--wide">
                    <div class="prazzu-tool-empty">
                        <strong>Nenhum dado para exibir.</strong>
                        <p>A ferramenta está ativa, mas ainda não encontrou registros compatíveis no banco.</p>
                    </div>
                </section>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <div class="prazzu-tool-no-results" data-tool-empty hidden>
            <strong>Nenhum resultado para o filtro atual.</strong>
            <p>Limpe a busca ou altere o status para visualizar os registros desta ferramenta.</p>
        </div>
    </div>

    <script>
        (() => {
            const root = document.querySelector('[data-prazzu-tool-page]');
            if (! root) return;

            const search = root.querySelector('[data-tool-search]');
            const tone = root.querySelector('[data-tool-tone]');
            const items = Array.from(root.querySelectorAll('[data-tool-item]'));
            const empty = root.querySelector('[data-tool-empty]');

            const normalize = (value) => (value || '').toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');

            const apply = () => {
                const term = normalize(search?.value || '');
                const selectedTone = tone?.value || 'all';
                let visible = 0;

                items.forEach((item) => {
                    const text = normalize(item.dataset.search || item.textContent || '');
                    const itemTone = item.dataset.tone || 'info';
                    const matchesText = ! term || text.includes(term);
                    const matchesTone = selectedTone === 'all' || itemTone === selectedTone;
                    const show = matchesText && matchesTone;
                    item.hidden = ! show;
                    if (show) visible++;
                });

                if (empty) empty.hidden = visible > 0;
            };

            search?.addEventListener('input', apply);
            tone?.addEventListener('change', apply);
        })();
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
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views/filament/pages/prazzu-operational-tool-page.blade.php ENDPATH**/ ?>