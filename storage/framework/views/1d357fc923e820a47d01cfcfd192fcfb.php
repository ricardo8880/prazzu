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

    <link rel="stylesheet" href="<?php echo e(asset('css/prazzu-fase2-pages.css')); ?>?v=<?php echo e(filemtime(public_path('css/prazzu-fase2-pages.css'))); ?>">

    <div class="prazzu-page prazzu-docs-page">
        <div class="prazzu-hero prazzu-hero-docs">
            <div><span class="prazzu-kicker">DOCUMENTOS</span><h2>Validades</h2><p>Controle de vencimentos com alertas, priorização e acesso rápido ao item.</p></div>
            <div class="prazzu-hero-actions"><a class="prazzu-action primary" href="<?php echo e(\App\Filament\Resources\ItemControles\ItemControleResource::getUrl('create')); ?>">Novo vencimento</a><a class="prazzu-action" href="<?php echo e(\App\Filament\Resources\ItemControles\ItemControleResource::getUrl('dashboard-tabelas')); ?>">Dashboard</a></div>
        </div>

        <div class="prazzu-stats-grid">
            <div class="prazzu-stat-card"><span>Com validade</span><strong><?php echo e(number_format($resumo['total'] ?? 0, 0, ',', '.')); ?></strong><small>Itens com data</small></div>
            <div class="prazzu-stat-card danger"><span>Vencidos</span><strong><?php echo e(number_format($resumo['vencidos'] ?? 0, 0, ',', '.')); ?></strong><small>Ação imediata</small></div>
            <div class="prazzu-stat-card warning"><span>7 dias</span><strong><?php echo e(number_format($resumo['seteDias'] ?? 0, 0, ',', '.')); ?></strong><small>Urgente</small></div>
            <div class="prazzu-stat-card"><span>30 dias</span><strong><?php echo e(number_format($resumo['trintaDias'] ?? 0, 0, ',', '.')); ?></strong><small>Planejamento</small></div>
        </div>

        <div class="prazzu-work-grid"><div class="prazzu-card"><div class="prazzu-card-header compact"><div><h3>Saúde dos vencimentos</h3><p>Dados para organizar a rotina documental.</p></div></div><div class="prazzu-mini-grid"><div class="prazzu-mini-card"><span>Sem data</span><strong><?php echo e(number_format($resumo['semData'] ?? 0, 0, ',', '.')); ?></strong><p>Itens que precisam de validade.</p></div><div class="prazzu-mini-card success"><span>Concluídos</span><strong><?php echo e(number_format($resumo['concluidos'] ?? 0, 0, ',', '.')); ?></strong><p>Encerrados/finalizados.</p></div></div></div><div class="prazzu-card"><div class="prazzu-card-header compact"><div><h3>Ações úteis</h3><p>Atalhos para gestão de vencimentos.</p></div></div><div class="prazzu-actions-list"><a href="<?php echo e(\App\Filament\Resources\ItemControles\ItemControleResource::getUrl('create')); ?>">Cadastrar validade</a><a href="<?php echo e(\App\Filament\Pages\Pendencias::getUrl()); ?>">Pendências</a><a href="<?php echo e(\App\Filament\Resources\ItemControles\ItemControleResource::getUrl('dashboard-graficos')); ?>">Ver gráficos</a></div></div></div>

        <div class="prazzu-card"><div class="prazzu-card-header"><div><h3>Agenda de vencimentos</h3><p>Clique para consultar detalhe, lembretes e ir para o item.</p></div></div><div class="prazzu-table-wrap"><table class="prazzu-table prazzu-click-table"><thead><tr><th>Item</th><th>Empresa</th><th>Tipo</th><th>Prioridade</th><th>Status</th><th>Vencimento</th><th class="prazzu-modal-head"></th></tr></thead><tbody>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $validades; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $validade): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php
                    $vencido = ! empty($validade['data_vencimento']) && \Carbon\Carbon::parse($validade['data_vencimento'])->isPast();
                    $empresa = $validade['nome_fantasia'] ?: ($validade['razao_social'] ?: '-');
                    $dias = ! empty($validade['data_vencimento']) ? now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($validade['data_vencimento'])->startOfDay(), false) : null;
                ?>
                <tr x-data="{ open: false }" @click="open = true"><td><strong><?php echo e($validade['titulo']); ?></strong><small><?php echo e(\Illuminate\Support\Str::limit($validade['descricao'] ?? 'Sem descrição cadastrada', 55)); ?></small></td><td><?php echo e($empresa); ?></td><td><?php echo e(ucfirst(str_replace('_', ' ', $validade['tipo'] ?? '-'))); ?></td><td><span class="prazzu-pill"><?php echo e(ucfirst(str_replace('_', ' ', $validade['prioridade'] ?? '-'))); ?></span></td><td><span class="prazzu-badge <?php echo e($vencido ? 'danger' : ''); ?>"><?php echo e(ucfirst(str_replace('_', ' ', $validade['status'] ?? '-'))); ?></span></td><td><span class="<?php echo e($vencido ? 'prazzu-date-danger' : ''); ?>"><?php echo e(\Carbon\Carbon::parse($validade['data_vencimento'])->format('d/m/Y')); ?></span><small><?php echo e($dias === null ? '' : ($dias < 0 ? abs((int) $dias) . ' dia(s) vencido' : 'faltam ' . (int) $dias . ' dia(s)')); ?></small></td><td class="prazzu-modal-cell" @click.stop><div class="prazzu-modal-backdrop" x-show="open" x-cloak @click.self="open = false" @keydown.escape.window="open = false"><div class="prazzu-modal-panel"><button type="button" class="prazzu-modal-close" @click="open = false">×</button><span class="prazzu-kicker dark-text">VALIDADE</span><h3><?php echo e($validade['titulo']); ?></h3><p><?php echo e($validade['descricao'] ?: 'Sem descrição cadastrada.'); ?></p><div class="prazzu-detail-grid"><div><span>Empresa</span><strong><?php echo e($empresa); ?></strong></div><div><span>Tipo</span><strong><?php echo e(ucfirst(str_replace('_', ' ', $validade['tipo'] ?? '-'))); ?></strong></div><div><span>Status</span><strong><?php echo e(ucfirst(str_replace('_', ' ', $validade['status'] ?? '-'))); ?></strong></div><div><span>Prioridade</span><strong><?php echo e(ucfirst(str_replace('_', ' ', $validade['prioridade'] ?? '-'))); ?></strong></div><div><span>Vencimento</span><strong><?php echo e(\Carbon\Carbon::parse($validade['data_vencimento'])->format('d/m/Y')); ?></strong></div><div><span>Situação</span><strong><?php echo e($dias < 0 ? 'Vencido há ' . abs((int) $dias) . ' dia(s)' : 'Faltam ' . (int) $dias . ' dia(s)'); ?></strong></div><div><span>Lembretes enviados</span><strong><?php echo e(number_format($validade['qtd_lembretes_enviados'] ?? 0, 0, ',', '.')); ?></strong></div><div><span>Último lembrete</span><strong><?php echo e(! empty($validade['ultimo_lembrete_enviado_em']) ? \Carbon\Carbon::parse($validade['ultimo_lembrete_enviado_em'])->format('d/m/Y H:i') : '-'); ?></strong></div></div><div class="prazzu-modal-actions"><a href="<?php echo e($validade['edit_url']); ?>">Ir para o item</a></div></div></div></td></tr>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <tr><td colspan="6" class="prazzu-empty">Nenhuma validade encontrada.</td></tr>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </tbody></table></div></div>
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
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views\filament\pages\validades.blade.php ENDPATH**/ ?>