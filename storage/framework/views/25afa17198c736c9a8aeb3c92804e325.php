<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
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
    
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: <?php echo e($marcaSecundaria); ?>;
            margin: 20px;
        }

        h1 {
            font-size: 20px;
            margin-bottom: 6px;
        }

        .subtitulo {
            font-size: 11px;
            color: #555;
            margin-bottom: 16px;
        }

        .resumo {
            margin-bottom: 16px;
            font-size: 11px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th, td {
            border: 1px solid #d1d5db;
            padding: 6px;
            vertical-align: top;
            word-wrap: break-word;
        }

        th {
            background: #f3f4f6;
            font-weight: bold;
            text-align: left;
        }

        tr:nth-child(even) {
            background: #fafafa;
        }

        .small {
            font-size: 10px;
        }
    </style>
</head>
<body>

    <div class="brand-header">
        <table class="brand-table">
            <tr>
                <td class="brand-left">
                    <div class="brand-kicker"><?php echo e($marcaNome); ?></div>
                    <h1>Relatório de Itens de Controle</h1>
                    <div class="brand-meta">Gerado em <?php echo e(now()->format('d/m/Y H:i')); ?></div>
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
    <div class="resumo">
        <strong>Total de registros:</strong> <?php echo e($items->count()); ?>

    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 4%;">ID</th>
                <th style="width: 16%;">Título</th>
                <th style="width: 8%;">Tipo</th>
                <th style="width: 9%;">Status</th>
                <th style="width: 14%;">Empresa</th>
                <th style="width: 12%;">Responsável</th>
                <th style="width: 8%;">Vencimento</th>
                <th style="width: 8%;">Conclusão</th>
                <th style="width: 10%;">Situação</th>
                <th style="width: 5%;">Anexo</th>
                <th style="width: 6%;">Anexos</th>
            </tr>
        </thead>
        <tbody>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php
                    $statusExibicao = $item->status;

                    if (
                        $item->data_vencimento &&
                        $item->data_vencimento->copy()->startOfDay()->isPast() &&
                        ! in_array($item->status, ['concluido', 'cancelado', 'vencido'], true)
                    ) {
                        $statusExibicao = 'vencido';
                    }

                    $statusTraduzido = match ($statusExibicao) {
                        'pendente' => 'Pendente',
                        'em_andamento' => 'Em andamento',
                        'concluido' => 'Concluído',
                        'cancelado' => 'Cancelado',
                        'vencido' => 'Vencido',
                        default => (string) $statusExibicao,
                    };

                    $tipoTraduzido = match ($item->tipo) {
                        'contrato' => 'Contrato',
                        'documento' => 'Documento',
                        'licenca' => 'Licença',
                        'acordo' => 'Acordo',
                        default => (string) $item->tipo,
                    };

                    $situacaoPrazo = '-';

                    if ($item->data_vencimento) {
                        if (in_array($item->status, ['concluido', 'cancelado'], true)) {
                            $situacaoPrazo = ucfirst(str_replace('_', ' ', $item->status));
                        } else {
                            $dias = now()->startOfDay()->diffInDays($item->data_vencimento->copy()->startOfDay(), false);

                            $situacaoPrazo = match (true) {
                                $dias < 0 => 'Vencido',
                                $dias === 0 => 'Vence hoje',
                                $dias <= 7 => 'Próximo do vencimento',
                                default => 'No prazo',
                            };
                        }
                    }
                ?>

                <tr>
                    <td><?php echo e($item->id); ?></td>
                    <td><?php echo e($item->titulo); ?></td>
                    <td><?php echo e($tipoTraduzido); ?></td>
                    <td><?php echo e($statusTraduzido); ?></td>
                    <td><?php echo e($item->empresa?->razao_social); ?></td>
                    <td><?php echo e($item->responsavel?->nome); ?></td>
                    <td><?php echo e(optional($item->data_vencimento)?->format('d/m/Y')); ?></td>
                    <td><?php echo e(optional($item->data_conclusao)?->format('d/m/Y')); ?></td>
                    <td><?php echo e($situacaoPrazo); ?></td>
                    <td><?php echo e(filled($item->arquivo) ? 'Sim' : 'Não'); ?></td>
                    <td><?php echo e($item->anexos_count); ?></td>
                </tr>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </tbody>
    </table>
    <div class="brand-footer">Documento gerado por <?php echo e($marcaNome); ?>.</div>
</body>
</html><?php /**PATH C:\xampp\htdocs\prazzu\resources\views\exports\item-controles-pdf.blade.php ENDPATH**/ ?>