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


    <div class="prazzu-page prazzu-docs-page">
        <div class="prazzu-hero prazzu-hero-docs">
            <div><span class="prazzu-kicker">DOCUMENTOS</span><h2>Contratos</h2><p>Acompanhe carteira, vigência, valor, vencimentos e acesso rápido ao contrato.</p></div>
            <div class="prazzu-hero-actions"><a class="prazzu-action primary" href="<?php echo e(\App\Filament\Resources\ItemControles\ItemControleResource::getUrl('create')); ?>">Novo contrato</a><a class="prazzu-action" href="<?php echo e(\App\Filament\Resources\ItemControles\ItemControleResource::getUrl('central-contratos')); ?>">Central</a></div>
        </div>

        <div class="prazzu-stats-grid">
            <div class="prazzu-stat-card"><span>Total</span><strong><?php echo e(number_format($resumo['total'] ?? 0, 0, ',', '.')); ?></strong><small>Contratos encontrados</small></div>
            <div class="prazzu-stat-card success"><span>Ativos</span><strong><?php echo e(number_format($resumo['ativos'] ?? 0, 0, ',', '.')); ?></strong><small>Vigentes/em vigor</small></div>
            <div class="prazzu-stat-card warning"><span>Vencem em 30 dias</span><strong><?php echo e(number_format($resumo['vencendo'] ?? 0, 0, ',', '.')); ?></strong><small>Renovação próxima</small></div>
            <div class="prazzu-stat-card"><span>Valor total</span><strong>R$ <?php echo e(number_format($resumo['valor'] ?? 0, 2, ',', '.')); ?></strong><small>Somatório cadastrado</small></div>
        </div>

        <div class="prazzu-work-grid">
            <div class="prazzu-card"><div class="prazzu-card-header compact"><div><h3>Alertas da carteira</h3><p>Visão rápida para gestão de risco contratual.</p></div></div><div class="prazzu-mini-grid"><div class="prazzu-mini-card danger"><span>Vencidos</span><strong><?php echo e(number_format($resumo['vencidos'] ?? 0, 0, ',', '.')); ?></strong><p>Contratos com vigência encerrada.</p></div><div class="prazzu-mini-card"><span>Sem vigência final</span><strong><?php echo e(number_format($resumo['semVigencia'] ?? 0, 0, ',', '.')); ?></strong><p>Precisam de conferência cadastral.</p></div></div></div>
            <div class="prazzu-card"><div class="prazzu-card-header compact"><div><h3>Ações úteis</h3><p>Atalhos para operar contratos.</p></div></div><div class="prazzu-actions-list"><a href="<?php echo e(\App\Filament\Resources\ItemControles\ItemControleResource::getUrl('create')); ?>">Cadastrar contrato</a><a href="<?php echo e(\App\Filament\Resources\ItemControles\ItemControleResource::getUrl('central-contratos')); ?>">Abrir central de contratos</a><a href="<?php echo e(\App\Filament\Resources\ItemControles\ItemControleResource::getUrl('relatorios-internos')); ?>">Relatórios internos</a></div></div>
        </div>

        <div class="prazzu-card">
            <div class="prazzu-card-header"><div><h3>Carteira de contratos</h3><p>Clique na linha para ver resumo, partes, vigência, arquivo e botão para editar.</p></div></div>
            <div class="prazzu-table-wrap">
                <table class="prazzu-table prazzu-click-table">
                    <thead><tr><th>Contrato</th><th>Empresa</th><th>Parte</th><th>Valor</th><th>Vigência</th><th>Status</th><th class="prazzu-modal-head"></th></tr></thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $contratos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $contrato): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <?php
                                $empresa = $contrato['nome_fantasia'] ?: ($contrato['razao_social'] ?: '-');
                                $vencido = ! empty($contrato['contrato_fim_em']) && \Carbon\Carbon::parse($contrato['contrato_fim_em'])->isPast();
                            ?>
                            <tr x-data="{ open: false }" @click="open = true">
                                <td><strong><?php echo e($contrato['titulo']); ?></strong><small><?php echo e($contrato['contrato_numero'] ?: 'Sem número'); ?></small></td>
                                <td><?php echo e($empresa); ?></td>
                                <td><?php echo e($contrato['contrato_parte_nome'] ?: '-'); ?></td>
                                <td>R$ <?php echo e(number_format($contrato['contrato_valor'] ?? 0, 2, ',', '.')); ?></td>
                                <td><span class="<?php echo e($vencido ? 'prazzu-date-danger' : ''); ?>"><?php echo e(! empty($contrato['contrato_inicio_em']) ? \Carbon\Carbon::parse($contrato['contrato_inicio_em'])->format('d/m/Y') : '-'); ?> até <?php echo e(! empty($contrato['contrato_fim_em']) ? \Carbon\Carbon::parse($contrato['contrato_fim_em'])->format('d/m/Y') : '-'); ?></span></td>
                                <td><span class="prazzu-badge <?php echo e($vencido ? 'danger' : ''); ?>"><?php echo e(ucfirst(str_replace('_', ' ', $contrato['contrato_status'] ?? 'não informado'))); ?></span></td>
                                <td class="prazzu-modal-cell" @click.stop><div class="prazzu-modal-backdrop" x-show="open" x-cloak @click.self="open = false" @keydown.escape.window="open = false"><div class="prazzu-modal-panel"><button type="button" class="prazzu-modal-close" @click="open = false">×</button><span class="prazzu-kicker dark-text">CONTRATO</span><h3><?php echo e($contrato['titulo']); ?></h3><p><?php echo e($contrato['descricao'] ?: 'Sem descrição cadastrada.'); ?></p><div class="prazzu-detail-grid"><div><span>Empresa</span><strong><?php echo e($empresa); ?></strong></div><div><span>Número</span><strong><?php echo e($contrato['contrato_numero'] ?: '-'); ?></strong></div><div><span>Parte</span><strong><?php echo e($contrato['contrato_parte_nome'] ?: '-'); ?></strong></div><div><span>Documento da parte</span><strong><?php echo e($contrato['contrato_parte_documento'] ?: '-'); ?></strong></div><div><span>Valor</span><strong>R$ <?php echo e(number_format($contrato['contrato_valor'] ?? 0, 2, ',', '.')); ?></strong></div><div><span>Status</span><strong><?php echo e(ucfirst(str_replace('_', ' ', $contrato['contrato_status'] ?? '-'))); ?></strong></div><div><span>Início</span><strong><?php echo e(! empty($contrato['contrato_inicio_em']) ? \Carbon\Carbon::parse($contrato['contrato_inicio_em'])->format('d/m/Y') : '-'); ?></strong></div><div><span>Fim</span><strong><?php echo e(! empty($contrato['contrato_fim_em']) ? \Carbon\Carbon::parse($contrato['contrato_fim_em'])->format('d/m/Y') : '-'); ?></strong></div></div><div class="prazzu-modal-actions"><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($contrato['arquivo_url'])): ?><a href="<?php echo e($contrato['arquivo_url']); ?>" target="_blank">Abrir arquivo</a><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?><a href="<?php echo e($contrato['edit_url']); ?>">Ir para o contrato</a></div></div></div></td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <tr><td colspan="6" class="prazzu-empty">Nenhum contrato encontrado.</td></tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
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
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views\filament\pages\contratos.blade.php ENDPATH**/ ?>