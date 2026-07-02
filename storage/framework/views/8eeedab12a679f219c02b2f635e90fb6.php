<!doctype html>
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
<title><?php echo e($titulo); ?></title>
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
    
        body { font-family: DejaVu Sans, sans-serif; color: <?php echo e($marcaSecundaria); ?>; font-size: 11px; }
        h1 { margin: 0 0 4px; font-size: 20px; }
        p { margin: 0; color: #4b5563; }
        .header { border-bottom: 2px solid <?php echo e($marcaPrimaria); ?>; padding-bottom: 12px; margin-bottom: 14px; }
        .cards { width: 100%; margin-bottom: 14px; }
        .cards td { border: 1px solid #e5e7eb; border-radius: 8px; padding: 9px; }
        .cards strong { display: block; font-size: 18px; color: <?php echo e($marcaSecundaria); ?>; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; background: #f3f4f6; font-size: 10px; text-transform: uppercase; color: #374151; }
        th, td { border: 1px solid #e5e7eb; padding: 7px; vertical-align: top; }
        .muted { color: #6b7280; font-size: 10px; }
    </style>
</head>
<body>

    <div class="brand-header">
        <table class="brand-table">
            <tr>
                <td class="brand-left">
                    <div class="brand-kicker"><?php echo e($marcaNome); ?></div>
                    <h1><?php echo e($titulo); ?></h1>
                    <div class="brand-meta">Gerado em <?php echo e($geradoEm); ?></div>
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
    <table class="cards">
        <tr>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($data['cards'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <td>
                    <span><?php echo e($card['label']); ?></span>
                    <strong><?php echo e($card['value']); ?></strong>
                    <small><?php echo e($card['hint']); ?></small>
                </td>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </tr>
    </table>

    <table>
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
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($data['linhas'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <tr>
                    <td><?php echo e($row['cliente']); ?></td>
                    <td><?php echo e($row['titulo']); ?><br><span class="muted"><?php echo e($row['observacao']); ?></span></td>
                    <td><?php echo e($row['status']); ?></td>
                    <td><?php echo e($row['responsavel']); ?></td>
                    <td><?php echo e($row['vencimento']); ?></td>
                    <td><?php echo e($row['indicador']); ?></td>
                    <td><?php echo e($row['prioridade']); ?></td>
                </tr>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <tr><td colspan="7">Nenhum registro encontrado.</td></tr>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </tbody>
    </table>
    <div class="brand-footer">Documento gerado por <?php echo e($marcaNome); ?>.</div>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views\exports\prazzu-relatorio-operacional-pdf.blade.php ENDPATH**/ ?>