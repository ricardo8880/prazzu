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

    <div class="rel-premium-page">
        <section class="rel-hero">
            <div>
                <span class="rel-kicker"><i class="bi bi-bar-chart-line"></i> Relatórios Operacionais</span>
                <h1>Análise consolidada, não execução</h1>
                <p>Esta tela concentra indicadores, comparativos e exportações. Pendências, documentos e aprovações continuam sendo resolvidos nas abas operacionais corretas.</p>
            </div>

            <div class="rel-actions">
                <button type="button" wire:click="exportarCsv" wire:loading.attr="disabled" wire:target="exportarCsv"><i class="bi bi-filetype-csv"></i> CSV</button>
                <button type="button" wire:click="exportarExcel" wire:loading.attr="disabled" wire:target="exportarExcel"><i class="bi bi-file-earmark-spreadsheet"></i> Excel</button>
                <button type="button" wire:click="exportarPdf" wire:loading.attr="disabled" wire:target="exportarPdf"><i class="bi bi-filetype-pdf"></i> PDF</button>
            </div>
        </section>

        <div class="rel-loading" wire:loading.flex>
            <span></span>
            <strong>Processando relatório...</strong>
        </div>

        <section class="rel-purpose-grid" aria-label="Propósito da seção">
            <article class="rel-purpose-card">
                <i class="bi bi-eye"></i>
                <div>
                    <strong>Ver</strong>
                    <span>Resumo consolidado por cliente, prazo, prioridade, status e responsável.</span>
                </div>
            </article>
            <article class="rel-purpose-card">
                <i class="bi bi-funnel"></i>
                <div>
                    <strong>Analisar</strong>
                    <span>Comparar gargalos, risco de atraso, produtividade e tendência sem alterar registros.</span>
                </div>
            </article>
            <article class="rel-purpose-card">
                <i class="bi bi-box-arrow-down"></i>
                <div>
                    <strong>Exportar</strong>
                    <span>Gerar CSV, Excel ou PDF para reunião, auditoria, controle interno ou prestação de contas.</span>
                </div>
            </article>
        </section>

        <section class="rel-summary-grid">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($resumo ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <article class="rel-stat rel-stat--<?php echo e($card['tone'] ?? 'info'); ?>">
                    <span><?php echo e($card['label']); ?></span>
                    <strong><?php echo e($card['value']); ?></strong>
                    <small><?php echo e($card['hint']); ?></small>
                </article>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </section>

        <section class="rel-tabs" aria-label="Tipos de relatório">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($tipos ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tipo => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <button
                    type="button"
                    wire:click="selecionarRelatorio('<?php echo e($tipo); ?>')"
                    class="<?php echo e(($tipoAtual ?? '') === $tipo ? 'active' : ''); ?>"
                >
                    <?php echo e($label); ?>

                </button>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </section>

        <section class="rel-summary-grid compact">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($cards ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <article class="rel-stat rel-stat--<?php echo e($card['tone'] ?? 'info'); ?>">
                    <span><?php echo e($card['label']); ?></span>
                    <strong><?php echo e($card['value']); ?></strong>
                    <small><?php echo e($card['hint']); ?></small>
                </article>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </section>

        <section class="rel-grid two">
            <article class="rel-card large">
                <header class="rel-card-header">
                    <div>
                        <h2><?php echo e($tipos[$tipoAtual] ?? 'Relatório operacional'); ?></h2>
                        <p>Os itens mais urgentes aparecem primeiro. Para resolver um item, use a aba dona do fluxo: Pendências, Documentos, Aprovações ou SLA.</p>
                    </div>
                    <span class="rel-pill"><?php echo e(count($linhas ?? [])); ?> registro(s)</span>
                </header>

                <div class="rel-table-wrap">
                    <table class="rel-table">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Título</th>
                                <th>Status</th>
                                <th>Responsável</th>
                                <th>Prazo</th>
                                <th>Indicador</th>
                                <th>Prioridade</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($linhas ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <tr>
                                    <td><strong><?php echo e($row['cliente']); ?></strong><small><?php echo e($row['relatorio']); ?></small></td>
                                    <td><?php echo e($row['titulo']); ?><small><?php echo e($row['observacao']); ?></small></td>
                                    <td><span class="rel-badge"><?php echo e($row['status']); ?></span></td>
                                    <td><?php echo e($row['responsavel']); ?></td>
                                    <td><?php echo e($row['vencimento']); ?><small><?php echo e($row['dias'] !== '-' ? $row['dias'].' dia(s)' : 'Sem data'); ?></small></td>
                                    <td><?php echo e($row['indicador']); ?></td>
                                    <td><span class="rel-priority rel-priority--<?php echo e(str_replace('í', 'i', strtolower($row['prioridade'] ?? 'media'))); ?>"><?php echo e(ucfirst($row['prioridade'] ?? 'media')); ?></span></td>
                                </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <tr>
                                    <td colspan="7">
                                        <div class="rel-empty">
                                            <strong>Nenhum registro encontrado</strong>
                                            <p>Esse é um bom sinal para este relatório. Troque o tipo acima ou gere outra visão operacional.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </article>

            <aside class="rel-side-stack">
                <article class="rel-card">
                    <header class="rel-card-header simple">
                        <h2>Clientes com maior impacto</h2>
                    </header>
                    <div class="rel-list">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($clientesCriticos ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cliente): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <div class="rel-list-row">
                                <div>
                                    <strong><?php echo e($cliente['cliente']); ?></strong>
                                    <span><?php echo e($cliente['indicador']); ?></span>
                                </div>
                                <em><?php echo e($cliente['status']); ?></em>
                            </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <div class="rel-empty small">Nenhum cliente crítico encontrado agora.</div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </article>

                <article class="rel-card">
                    <header class="rel-card-header simple">
                        <h2>Qualidade da base</h2>
                        <span class="rel-score"><?php echo e($seguranca['score'] ?? 0); ?>%</span>
                    </header>
                    <div class="rel-checklist">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($seguranca['checks'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $check): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <div class="rel-check <?php echo e($check['ok'] ? 'ok' : 'warn'); ?>">
                                <span><?php echo e($check['ok'] ? '✓' : '!'); ?></span>
                                <div>
                                    <strong><?php echo e($check['title']); ?></strong>
                                    <p><?php echo e($check['description']); ?></p>
                                    <small><?php echo e($check['action']); ?></small>
                                </div>
                            </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>
                </article>
            </aside>
        </section>

        <section class="rel-card">
            <header class="rel-card-header">
                <div>
                    <h2>Checklist de leitura do relatório</h2>
                    <p>Use este bloco para validar se a leitura do relatório está confiável antes de exportar ou apresentar os dados.</p>
                </div>
                <span class="rel-score"><?php echo e($validacao['score'] ?? 0); ?>%</span>
            </header>

            <div class="rel-validation-grid">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($validacao['items'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <div class="rel-validation <?php echo e($item['ok'] ? 'ok' : 'warn'); ?>">
                        <strong><?php echo e($item['title']); ?></strong>
                        <p><?php echo e($item['description']); ?></p>
                    </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
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
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views/filament/pages/relatorios.blade.php ENDPATH**/ ?>