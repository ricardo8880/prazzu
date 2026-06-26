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

    <link rel="stylesheet" href="<?php echo e(asset('css/dashboard-configuravel.css')); ?>">

    <?php ($grupos = $this->widgetsPorEmpresa()); ?>

    <div class="dc-page">
        <div class="dc-hero">
            <div>
                <div class="dc-eyebrow">Plano Business</div>
                <h2 class="dc-title">Dashboard Configurável</h2>
                <p class="dc-subtitle">
                    Acompanhe os indicadores configurados respeitando o tipo de cada widget: card, gráfico ou tabela.
                </p>
            </div>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($grupos->isEmpty()): ?>
            <div class="dc-empty-card">
                <div class="dc-empty-icon">▦</div>
                <h3>Nenhum widget ativo configurado</h3>
                <p>Crie um widget para começar a visualizar o dashboard.</p>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(\App\Filament\Resources\DashboardConfiguravel\DashboardWidgetConfiguracaoResource::canCreate()): ?>
                    <a class="dc-button" href="<?php echo e(\App\Filament\Resources\DashboardConfiguravel\DashboardWidgetConfiguracaoResource::getUrl('create')); ?>">
                        Criar primeiro widget
                    </a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        <?php else: ?>
            <div class="dc-groups">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $grupos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $empresaNome => $widgets): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <section class="dc-group">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(Filament\Facades\Filament::auth()->user()?->isSuperAdmin()): ?>
                            <div class="dc-group-header">
                                <h3><?php echo e($empresaNome); ?></h3>
                                <p>Widgets ativos dessa empresa.</p>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <div class="dc-grid">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $widgets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $widget): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <article class="dc-widget <?php echo e($this->larguraWidget($widget)); ?> <?php echo e($this->corWidget($widget)); ?> <?php echo e($this->classeTipoWidget($widget)); ?>">
                                    <div class="dc-widget-top">
                                        <div>
                                            <span class="dc-widget-source"><?php echo e($this->labelFonte($widget)); ?></span>
                                            <h4><?php echo e($widget->titulo); ?></h4>
                                        </div>

                                        <span class="dc-widget-type"><?php echo e($this->labelTipo($widget)); ?></span>
                                    </div>

                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php switch($widget->tipo):
                                        case ('grafico'): ?>
                                            <?php ($dadosGrafico = $this->dadosGrafico($widget)); ?>
                                            <?php ($maiorValorGrafico = $this->maiorValorGrafico($widget)); ?>

                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($dadosGrafico->isEmpty()): ?>
                                                <div class="dc-widget-empty-content">Sem dados para montar o gráfico.</div>
                                            <?php else: ?>
                                                <div class="dc-chart" aria-label="Gráfico do widget <?php echo e($widget->titulo); ?>">
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $dadosGrafico; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $linha): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                                        <?php ($valorGrafico = (float) ($linha['valor'] ?? 0)); ?>
                                                        <?php ($percentualGrafico = max(4, min(100, ($valorGrafico / $maiorValorGrafico) * 100))); ?>

                                                        <div class="dc-chart-row">
                                                            <div class="dc-chart-label" title="<?php echo e($linha['label'] ?? 'Sem label'); ?>">
                                                                <?php echo e($linha['label'] ?? 'Sem label'); ?>

                                                            </div>
                                                            <div class="dc-chart-track">
                                                                <div class="dc-chart-bar" style="width: <?php echo e($percentualGrafico); ?>%"></div>
                                                            </div>
                                                            <div class="dc-chart-value"><?php echo e(number_format($valorGrafico, 0, ',', '.')); ?></div>
                                                        </div>
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                                </div>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            <?php break; ?>

                                        <?php case ('tabela'): ?>
                                            <?php ($dadosTabela = $this->dadosTabela($widget)); ?>

                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($dadosTabela->isEmpty()): ?>
                                                <div class="dc-widget-empty-content">Sem registros para exibir na tabela.</div>
                                            <?php else: ?>
                                                <div class="dc-table-wrap">
                                                    <table class="dc-table">
                                                        <thead>
                                                        <tr>
                                                            <th>Título</th>
                                                            <th>Status</th>
                                                            <th>Tipo</th>
                                                            <th>Vencimento</th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $dadosTabela; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $linha): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                                            <tr>
                                                                <td title="<?php echo e($linha['titulo'] ?? '-'); ?>"><?php echo e($linha['titulo'] ?? '-'); ?></td>
                                                                <td><span class="dc-status-pill"><?php echo e($linha['status'] ?? '-'); ?></span></td>
                                                                <td><?php echo e($linha['tipo'] ?? '-'); ?></td>
                                                                <td><?php echo e($linha['data_vencimento'] ?? '-'); ?></td>
                                                            </tr>
                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            <?php break; ?>

                                        <?php default: ?>
                                            <div class="dc-widget-value">
                                                <?php echo e($this->valorFormatado($widget)); ?>

                                            </div>
                                    <?php endswitch; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                    <div class="dc-widget-footer">
                                        <span>Ordem <?php echo e($widget->ordem ?? 1); ?></span>
                                        <span><?php echo e($empresaNome); ?></span>
                                    </div>
                                </article>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    </section>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
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
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views/filament/resources/dashboard-configuravel/pages/visualizar-dashboard-configuravel.blade.php ENDPATH**/ ?>