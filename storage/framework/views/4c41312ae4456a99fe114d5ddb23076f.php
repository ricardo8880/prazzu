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

    <link rel="stylesheet" href="<?php echo e(asset('css/dashboard-executivo-contabil.css')); ?>?v=<?php echo e(file_exists(public_path('css/dashboard-executivo-contabil.css')) ? filemtime(public_path('css/dashboard-executivo-contabil.css')) : time()); ?>">

    <?php
        $cards = $dashboard['cards'] ?? [];
        $health = $dashboard['health'] ?? ['score' => 0, 'label' => 'Sem dados', 'tone' => 'info', 'message' => 'Dados ainda não encontrados.'];
        $decisionBlocks = $dashboard['decision_blocks'] ?? [];
        $sections = $dashboard['sections'] ?? [];
        $quickActions = $dashboard['quick_actions'] ?? [];
        $updatedAt = $dashboard['updated_at'] ?? now()->format('d/m/Y H:i');
    ?>

    <div class="dec-page" data-executive-accounting-dashboard>
        <section class="dec-hero dec-tone-<?php echo e($health['tone'] ?? 'info'); ?>">
            <div class="dec-hero__content">
                <span class="dec-eyebrow">Gestão do escritório</span>
                <h1>Dashboard Executivo Contábil</h1>
                <p>Uma leitura simples para o sócio ou gestor entender clientes, prazos, documentos, equipe e financeiro sem abrir várias telas.</p>

                <div class="dec-hero__meta">
                    <span>Atualizado em <?php echo e($updatedAt); ?></span>
                    <span><?php echo e(count($cards)); ?> indicadores principais</span>
                </div>
            </div>

            <aside class="dec-health">
                <span>Saúde da operação</span>
                <strong><?php echo e($health['score'] ?? 0); ?>%</strong>
                <b><?php echo e($health['label'] ?? 'Sem dados'); ?></b>
                <p><?php echo e($health['message'] ?? 'Acompanhe os indicadores para tomar decisões.'); ?></p>
            </aside>
        </section>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($quickActions)): ?>
            <nav class="dec-actions" aria-label="Ações rápidas do dashboard">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $quickActions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <a href="<?php echo e($action['url']); ?>"><?php echo e($action['label']); ?></a>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </nav>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <section class="dec-kpis">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $cards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <article class="dec-kpi dec-tone-<?php echo e($card['tone'] ?? 'info'); ?>">
                    <span><?php echo e($card['label'] ?? '-'); ?></span>
                    <strong><?php echo e($card['value'] ?? '0'); ?></strong>
                    <p><?php echo e($card['hint'] ?? 'Indicador do escritório'); ?></p>
                </article>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <article class="dec-empty dec-empty--wide">
                    <strong>Nenhum indicador encontrado.</strong>
                    <p>Quando houver dados no banco, o dashboard vai exibir os principais números do escritório aqui.</p>
                </article>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </section>

        <section class="dec-decision-grid">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $decisionBlocks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $block): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <article class="dec-decision dec-tone-<?php echo e($block['tone'] ?? 'info'); ?>">
                    <div>
                        <span><?php echo e($block['title'] ?? 'Decisão'); ?></span>
                        <strong><?php echo e(number_format((int) ($block['value'] ?? 0), 0, ',', '.')); ?></strong>
                    </div>
                    <p><?php echo e($block['description'] ?? 'Analise os dados antes de decidir.'); ?></p>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($block['url'])): ?>
                        <a href="<?php echo e($block['url']); ?>"><?php echo e($block['action'] ?? 'Abrir tela'); ?></a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </article>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </section>

        <section class="dec-command">
            <div>
                <strong>Encontrar informação</strong>
                <p>Busque por cliente, responsável, status, tarefa ou cobrança dentro deste dashboard.</p>
            </div>
            <div class="dec-command__controls">
                <input type="search" placeholder="Buscar no dashboard..." data-dec-search>
                <select data-dec-tone>
                    <option value="all">Todos os status</option>
                    <option value="danger">Crítico</option>
                    <option value="warning">Atenção</option>
                    <option value="success">Saudável</option>
                    <option value="info">Informativo</option>
                </select>
            </div>
        </section>

        <div class="dec-sections">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <section class="dec-card">
                    <header>
                        <div>
                            <h2><?php echo e($section['title'] ?? 'Seção'); ?></h2>
                            <p><?php echo e($section['description'] ?? 'Dados consolidados do escritório.'); ?></p>
                        </div>
                        <span><?php echo e(count($section['items'] ?? [])); ?></span>
                    </header>

                    <div class="dec-list">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_2 = true; $__currentLoopData = ($section['items'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <article class="dec-item dec-tone-<?php echo e($item['tone'] ?? 'info'); ?>" data-dec-item data-tone="<?php echo e($item['tone'] ?? 'info'); ?>" data-search="<?php echo e(\Illuminate\Support\Str::lower(($item['title'] ?? '') . ' ' . ($item['status'] ?? '') . ' ' . ($item['meta'] ?? '') . ' ' . ($item['description'] ?? ''))); ?>">
                                <div class="dec-item__top">
                                    <h3><?php echo e($item['title'] ?? 'Sem título'); ?></h3>
                                    <span><?php echo e($item['status'] ?? '-'); ?></span>
                                </div>
                                <small><?php echo e($item['meta'] ?? 'Sem contexto'); ?></small>
                                <p><?php echo e($item['description'] ?? 'Sem descrição cadastrada.'); ?></p>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($item['url'])): ?>
                                    <a href="<?php echo e($item['url']); ?>">Abrir origem</a>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </article>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <div class="dec-empty">
                                <strong>Nada crítico nesta fila.</strong>
                                <p>Quando houver risco, atraso ou pendência, os registros aparecerão aqui de forma priorizada.</p>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </section>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <section class="dec-card dec-card--wide">
                    <div class="dec-empty">
                        <strong>Nenhuma seção disponível.</strong>
                        <p>O dashboard está ativo, mas ainda não encontrou dados suficientes no banco.</p>
                    </div>
                </section>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <div class="dec-no-results" data-dec-empty hidden>
            <strong>Nenhum resultado encontrado.</strong>
            <p>Limpe a busca ou altere o status para visualizar os dados do dashboard.</p>
        </div>
    </div>

    <script>
        (() => {
            const root = document.querySelector('[data-executive-accounting-dashboard]');
            if (! root) return;

            const search = root.querySelector('[data-dec-search]');
            const tone = root.querySelector('[data-dec-tone]');
            const items = Array.from(root.querySelectorAll('[data-dec-item]'));
            const empty = root.querySelector('[data-dec-empty]');

            const normalize = (value) => (value || '').toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');

            const apply = () => {
                const term = normalize(search?.value || '');
                const selectedTone = tone?.value || 'all';
                let visible = 0;

                items.forEach((item) => {
                    const text = normalize(item.dataset.search || item.textContent || '');
                    const itemTone = item.dataset.tone || 'info';
                    const show = (! term || text.includes(term)) && (selectedTone === 'all' || itemTone === selectedTone);
                    item.hidden = ! show;
                    if (show) visible++;
                });

                if (empty) empty.hidden = visible > 0 || items.length === 0;
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
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views/filament/pages/dashboard-executivo-contabil.blade.php ENDPATH**/ ?>