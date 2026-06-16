<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <?php
    $whiteLabel = $whiteLabel ?? \App\Support\WhiteLabelSettings::make();
    $usarMarcaDocumento = $whiteLabel->documentBrandingEnabled();
    $marcaNome = $whiteLabel->brandName();
    $marcaLogo = $whiteLabel->documentLogoPath();
    $marcaPrimaria = $usarMarcaDocumento ? $whiteLabel->primaryColor() : '#111827';
    $marcaSecundaria = $usarMarcaDocumento ? $whiteLabel->secondaryColor() : '#111827';
    $marcaDestaque = $usarMarcaDocumento ? $whiteLabel->accentColor() : '#22c55e';
?>
<title>Relatório de Itens de Controle</title>
    <style>
        .brand-header { width: 100%; border-bottom: 3px solid <?php echo e($marcaPrimaria); ?>; padding-bottom: 12px; margin-bottom: 14px; }
        .brand-table { width: 100%; border-collapse: collapse; }
        .brand-left { vertical-align: top; }
        .brand-right { width: 190px; vertical-align: top; text-align: right; }
        .brand-logo { max-width: 150px; max-height: 54px; margin-bottom: 6px; }
        .brand-name { font-size: 10px; font-weight: bold; color: <?php echo e($marcaSecundaria); ?>; }
        .brand-kicker { color: <?php echo e($marcaPrimaria); ?>; font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px; }
        .brand-meta { color: #6b7280; font-size: 10px; }
        .brand-footer { color: #6b7280; font-size: 9px; margin-top: 14px; border-top: 1px solid #e5e7eb; padding-top: 6px; }
        .brand-accent { color: <?php echo e($marcaPrimaria); ?>; }
    
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: <?php echo e($marcaSecundaria); ?>; }
        h1 { font-size: 18px; margin-bottom: 8px; }
        .meta { margin-bottom: 12px; }
        .meta div { margin-bottom: 2px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 6px; vertical-align: top; }
        th { background: #f3f4f6; text-align: left; }
    </style>
</head>
<body>

    <div class="brand-header">
        <table class="brand-table">
            <tr>
                <td class="brand-left">
                    <div class="brand-kicker"><?php echo e($marcaNome); ?></div>
                    <h1>Relatório de Itens de Controle</h1>
                    <div class="brand-meta">Gerado em <?php echo e($geradoEm->format('d/m/Y H:i')); ?></div>
                </td>
                <td class="brand-right">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($usarMarcaDocumento && $marcaLogo): ?>
                        <img src="<?php echo e($marcaLogo); ?>" alt="<?php echo e($marcaNome); ?>" class="brand-logo">
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($usarMarcaDocumento): ?>
                        <div class="brand-name"><?php echo e($marcaNome); ?></div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </td>
            </tr>
        </table>
    </div>
    <div class="meta">
        <div><strong>Total de registros:</strong> <?php echo e($registros->count()); ?></div>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Título</th>
                <th>Tipo</th>
                <th>Status</th>
                <th>Empresa</th>
                <th>Responsável</th>
                <th>Vencimento</th>
                <th>Conclusão</th>
                <th>Situação</th>
            </tr>
        </thead>
        <tbody>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $registros; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <tr>
                    <td><?php echo e($item->id); ?></td>
                    <td><?php echo e($item->titulo); ?></td>
                    <td><?php echo e(\App\Support\ItemControleRelatorioExportador::traduzirTipo($item->tipo)); ?></td>
                    <td><?php echo e(\App\Support\ItemControleRelatorioExportador::traduzirStatus($item->status)); ?></td>
                    <td><?php echo e($item->empresa?->razao_social); ?></td>
                    <td><?php echo e($item->responsavel?->nome); ?></td>
                    <td><?php echo e(optional($item->data_vencimento)?->format('d/m/Y')); ?></td>
                    <td><?php echo e(optional($item->data_conclusao)?->format('d/m/Y')); ?></td>
                    <td><?php echo e(\App\Support\ItemControleRelatorioExportador::situacaoPrazo($item)); ?></td>
                </tr>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <tr>
                    <td colspan="9">Nenhum registro encontrado.</td>
                </tr>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </tbody>
    </table>
    <div class="brand-footer">Documento gerado por <?php echo e($marcaNome); ?>.</div>
</body>
</html><?php /**PATH C:\xampp\htdocs\prazzu\resources\views\relatorios\item-controles-pdf.blade.php ENDPATH**/ ?>