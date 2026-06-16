<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Item de Controle #<?php echo e($item->id); ?></title>

    <?php
        $whiteLabel = $whiteLabel ?? \App\Support\WhiteLabelSettings::make();
        $usarMarcaDocumento = $whiteLabel->documentBrandingEnabled();
        $marcaNome = $whiteLabel->brandName();
        $marcaLogo = $whiteLabel->documentLogoPath();
        $marcaPrimaria = $usarMarcaDocumento ? $whiteLabel->primaryColor() : '#1d4ed8';
        $marcaSecundaria = $usarMarcaDocumento ? $whiteLabel->secondaryColor() : '#111827';
        $marcaDestaque = $usarMarcaDocumento ? $whiteLabel->accentColor() : '#22c55e';
    ?>

    <style>
        @page {
            margin: 22px 24px 28px 24px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #1f2937;
            background: #ffffff;
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            line-height: 1.45;
        }

        .page {
            width: 100%;
        }

        .header {
            width: 100%;
            border-bottom: 3px solid <?php echo e($marcaPrimaria); ?>;
            padding-bottom: 14px;
            margin-bottom: 16px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-left {
            width: 68%;
            vertical-align: top;
        }

        .header-right {
            width: 32%;
            vertical-align: top;
            text-align: right;
        }

        .brand-logo {
            max-width: 150px;
            max-height: 54px;
            margin-bottom: 8px;
        }

        .brand-name {
            color: <?php echo e($marcaSecundaria); ?>;
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .document-label {
            color: <?php echo e($marcaPrimaria); ?>;
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .title {
            color: <?php echo e($marcaSecundaria); ?>;
            font-size: 24px;
            font-weight: bold;
            line-height: 1.15;
            margin: 0 0 7px 0;
        }

        .subtitle {
            color: #6b7280;
            font-size: 11px;
        }

        .code-box {
            display: inline-block;
            min-width: 132px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 8px 10px;
            background: #f9fafb;
            text-align: left;
        }

        .code-label {
            color: #6b7280;
            font-size: 9px;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .code-value {
            color: <?php echo e($marcaSecundaria); ?>;
            font-size: 18px;
            font-weight: bold;
        }

        .summary-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 8px 0;
            margin: 0 -8px 16px -8px;
        }

        .summary-card {
            width: 25%;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 10px;
            background: #f9fafb;
            vertical-align: top;
        }

        .summary-label {
            color: #6b7280;
            font-size: 9px;
            font-weight: bold;
            letter-spacing: .5px;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .summary-value {
            color: <?php echo e($marcaSecundaria); ?>;
            font-size: 12px;
            font-weight: bold;
        }

        .badge {
            display: inline-block;
            border-radius: 999px;
            padding: 4px 9px;
            font-size: 10px;
            font-weight: bold;
            white-space: nowrap;
        }

        .badge-blue {
            color: #1e3a8a;
            background: #dbeafe;
            border: 1px solid #bfdbfe;
        }

        .badge-green {
            color: #166534;
            background: #dcfce7;
            border: 1px solid #bbf7d0;
        }

        .badge-yellow {
            color: #854d0e;
            background: #fef3c7;
            border: 1px solid #fde68a;
        }

        .badge-red {
            color: #991b1b;
            background: #fee2e2;
            border: 1px solid #fecaca;
        }

        .badge-gray {
            color: #374151;
            background: #f3f4f6;
            border: 1px solid #e5e7eb;
        }

        .section {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            margin-bottom: 14px;
            overflow: hidden;
            page-break-inside: avoid;
        }

        .section-header {
            background: #f3f4f6;
            border-bottom: 1px solid #e5e7eb;
            padding: 9px 12px;
        }

        .section-title {
            color: <?php echo e($marcaSecundaria); ?>;
            font-size: 13px;
            font-weight: bold;
            margin: 0;
        }

        .section-body {
            padding: 12px;
        }

        .grid {
            width: 100%;
            border-collapse: collapse;
        }

        .grid td {
            width: 50%;
            vertical-align: top;
            padding: 0 8px 10px 0;
        }

        .field {
            border-bottom: 1px solid #f3f4f6;
            padding-bottom: 7px;
        }

        .field-label {
            color: #6b7280;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .field-value {
            color: <?php echo e($marcaSecundaria); ?>;
            font-size: 11px;
            word-break: break-word;
        }

        .description-box {
            color: #374151;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            background: #ffffff;
            padding: 10px;
            min-height: 38px;
            white-space: pre-line;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th {
            color: #374151;
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
            padding: 8px;
            font-size: 9px;
            text-align: left;
            text-transform: uppercase;
        }

        .table td {
            border-bottom: 1px solid #f3f4f6;
            padding: 8px;
            vertical-align: top;
        }

        .empty {
            color: #6b7280;
            border: 1px dashed #d1d5db;
            border-radius: 10px;
            background: #f9fafb;
            padding: 12px;
            text-align: center;
        }

        .signature-box {
            border: 1px solid <?php echo e($marcaDestaque); ?>;
            border-radius: 12px;
            background: #f0fdf4;
            padding: 12px;
        }

        .signature-title {
            color: <?php echo e($marcaDestaque); ?>;
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .hash {
            color: #374151;
            font-family: DejaVu Sans Mono, monospace;
            font-size: 9px;
            word-break: break-all;
        }

        .footer {
            position: fixed;
            left: 0;
            right: 0;
            bottom: -18px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
            padding-top: 6px;
            font-size: 9px;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }

        .footer-right {
            text-align: right;
        }

        .mt-8 {
            margin-top: 8px;
        }
    </style>
</head>
<body>
<?php
    $assinatura = $item->ultimaAssinatura;
    $aprovacao = $item->ultimaAprovacao;
    $checklistTotal = $item->checklists->count();
    $checklistConcluidos = $item->checklists->where('concluido', true)->count();
    $statusBadge = match ($item->getStatusExibicaoColor()) {
        'success' => 'badge-green',
        'warning' => 'badge-yellow',
        'danger' => 'badge-red',
        'info' => 'badge-blue',
        default => 'badge-gray',
    };
    $prioridadeBadge = match ($item->getPrioridadeColor()) {
        'success' => 'badge-green',
        'warning' => 'badge-yellow',
        'danger' => 'badge-red',
        'info' => 'badge-blue',
        default => 'badge-gray',
    };
?>

<div class="footer">
    <table class="footer-table">
        <tr>
            <td>Documento gerado automaticamente por <?php echo e($marcaNome); ?></td>
            <td class="footer-right">Gerado em <?php echo e($geradoEm?->format('d/m/Y H:i')); ?></td>
        </tr>
    </table>
</div>

<div class="page">
    <div class="header">
        <table class="header-table">
            <tr>
                <td class="header-left">
                    <div class="document-label">Relatório do item de controle</div>
                    <h1 class="title"><?php echo e($item->titulo); ?></h1>
                    <div class="subtitle">
                        <?php echo e($item->empresa?->razao_social ?: 'Empresa não informada'); ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->categoria?->nome): ?>
                            · <?php echo e($item->categoria->nome); ?>

                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </td>
                <td class="header-right">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($usarMarcaDocumento && $marcaLogo): ?>
                        <img src="<?php echo e($marcaLogo); ?>" alt="<?php echo e($marcaNome); ?>" class="brand-logo">
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($usarMarcaDocumento): ?>
                        <div class="brand-name"><?php echo e($marcaNome); ?></div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <div class="code-box">
                        <div class="code-label">Código do item</div>
                        <div class="code-value">#<?php echo e($item->id); ?></div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <table class="summary-table">
        <tr>
            <td class="summary-card">
                <div class="summary-label">Status</div>
                <div class="summary-value"><span class="badge <?php echo e($statusBadge); ?>"><?php echo e($item->getStatusExibicao()); ?></span></div>
            </td>
            <td class="summary-card">
                <div class="summary-label">Prioridade</div>
                <div class="summary-value"><span class="badge <?php echo e($prioridadeBadge); ?>"><?php echo e($item->getPrioridadeExibicao()); ?></span></div>
            </td>
            <td class="summary-card">
                <div class="summary-label">Prazo</div>
                <div class="summary-value"><?php echo e($item->data_vencimento?->format('d/m/Y') ?: 'Sem prazo'); ?></div>
            </td>
            <td class="summary-card">
                <div class="summary-label">Assinatura</div>
                <div class="summary-value"><span class="badge <?php echo e($assinatura ? 'badge-green' : 'badge-gray'); ?>"><?php echo e($assinatura ? 'Assinado' : 'Não assinado'); ?></span></div>
            </td>
        </tr>
    </table>

    <div class="section">
        <div class="section-header">
            <h2 class="section-title">Informações gerais</h2>
        </div>
        <div class="section-body">
            <table class="grid">
                <tr>
                    <td>
                        <div class="field">
                            <div class="field-label">Empresa</div>
                            <div class="field-value"><?php echo e($item->empresa?->razao_social ?: '-'); ?></div>
                        </div>
                    </td>
                    <td>
                        <div class="field">
                            <div class="field-label">Responsável</div>
                            <div class="field-value"><?php echo e($item->responsavel?->nome ?: '-'); ?></div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="field">
                            <div class="field-label">Categoria / Tipo</div>
                            <div class="field-value"><?php echo e($item->getTipoOuCategoria() ?: '-'); ?></div>
                        </div>
                    </td>
                    <td>
                        <div class="field">
                            <div class="field-label">Tags</div>
                            <div class="field-value"><?php echo e($item->getTagsResumo()); ?></div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="field">
                            <div class="field-label">Criado em</div>
                            <div class="field-value"><?php echo e($item->created_at?->format('d/m/Y H:i') ?: '-'); ?></div>
                        </div>
                    </td>
                    <td>
                        <div class="field">
                            <div class="field-label">Atualizado em</div>
                            <div class="field-value"><?php echo e($item->updated_at?->format('d/m/Y H:i') ?: '-'); ?></div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="field">
                            <div class="field-label">Data de conclusão</div>
                            <div class="field-value"><?php echo e($item->data_conclusao?->format('d/m/Y') ?: '-'); ?></div>
                        </div>
                    </td>
                    <td>
                        <div class="field">
                            <div class="field-label">Situação do prazo</div>
                            <div class="field-value"><?php echo e($item->getSituacaoPrazo()); ?></div>
                        </div>
                    </td>
                </tr>
            </table>

            <div class="field-label mt-8">Descrição</div>
            <div class="description-box"><?php echo e(filled($item->descricao) ? $item->descricao : 'Sem descrição cadastrada.'); ?></div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(filled($item->observacao)): ?>
                <div class="field-label mt-8">Observação</div>
                <div class="description-box"><?php echo e($item->observacao); ?></div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->isContrato()): ?>
        <div class="section">
            <div class="section-header">
                <h2 class="section-title">Dados do contrato</h2>
            </div>
            <div class="section-body">
                <table class="grid">
                    <tr>
                        <td>
                            <div class="field">
                                <div class="field-label">Número</div>
                                <div class="field-value"><?php echo e($item->contrato_numero ?: '-'); ?></div>
                            </div>
                        </td>
                        <td>
                            <div class="field">
                                <div class="field-label">Status do contrato</div>
                                <div class="field-value"><?php echo e($item->contrato_status ? ucfirst(str_replace('_', ' ', $item->contrato_status)) : '-'); ?></div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="field">
                                <div class="field-label">Parte</div>
                                <div class="field-value"><?php echo e($item->contrato_parte_nome ?: '-'); ?></div>
                            </div>
                        </td>
                        <td>
                            <div class="field">
                                <div class="field-label">Documento da parte</div>
                                <div class="field-value"><?php echo e($item->contrato_parte_documento ?: '-'); ?></div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="field">
                                <div class="field-label">Valor</div>
                                <div class="field-value"><?php echo e($item->contrato_valor !== null ? 'R$ ' . number_format((float) $item->contrato_valor, 2, ',', '.') : '-'); ?></div>
                            </div>
                        </td>
                        <td>
                            <div class="field">
                                <div class="field-label">Vigência</div>
                                <div class="field-value">
                                    <?php echo e($item->contrato_inicio_em?->format('d/m/Y') ?: '-'); ?> até <?php echo e($item->contrato_fim_em?->format('d/m/Y') ?: '-'); ?>

                                </div>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="section">
        <div class="section-header">
            <h2 class="section-title">Assinatura eletrônica</h2>
        </div>
        <div class="section-body">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($assinatura): ?>
                <div class="signature-box">
                    <div class="signature-title">Documento assinado eletronicamente</div>
                    <table class="grid">
                        <tr>
                            <td>
                                <div class="field">
                                    <div class="field-label">Nome</div>
                                    <div class="field-value"><?php echo e($assinatura->nome ?: '-'); ?></div>
                                </div>
                            </td>
                            <td>
                                <div class="field">
                                    <div class="field-label">E-mail</div>
                                    <div class="field-value"><?php echo e($assinatura->email ?: '-'); ?></div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="field">
                                    <div class="field-label">Documento</div>
                                    <div class="field-value"><?php echo e($assinatura->documento ?: '-'); ?></div>
                                </div>
                            </td>
                            <td>
                                <div class="field">
                                    <div class="field-label">Assinado em</div>
                                    <div class="field-value"><?php echo e($assinatura->assinado_em?->format('d/m/Y H:i') ?: '-'); ?></div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="field">
                                    <div class="field-label">IP</div>
                                    <div class="field-value"><?php echo e($assinatura->ip ?: '-'); ?></div>
                                </div>
                            </td>
                            <td>
                                <div class="field">
                                    <div class="field-label">Usuário vinculado</div>
                                    <div class="field-value"><?php echo e($assinatura->user?->name ?: '-'); ?></div>
                                </div>
                            </td>
                        </tr>
                    </table>

                    <div class="field-label mt-8">Hash da assinatura</div>
                    <div class="description-box hash"><?php echo e($assinatura->hash_assinatura ?: '-'); ?></div>

                    <div class="field-label mt-8">Texto do aceite</div>
                    <div class="description-box"><?php echo e(filled($assinatura->aceite_texto) ? $assinatura->aceite_texto : '-'); ?></div>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(filled($assinatura->observacao)): ?>
                        <div class="field-label mt-8">Observação da assinatura</div>
                        <div class="description-box"><?php echo e($assinatura->observacao); ?></div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            <?php else: ?>
                <div class="empty">Nenhuma assinatura registrada para este item.</div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <div class="section">
        <div class="section-header">
            <h2 class="section-title">Checklist operacional</h2>
        </div>
        <div class="section-body">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($checklistTotal > 0): ?>
                <table class="table">
                    <thead>
                    <tr>
                        <th style="width: 58%;">Item</th>
                        <th style="width: 16%;">Status</th>
                        <th style="width: 26%;">Conclusão</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $item->checklists; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $checklist): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr>
                            <td><?php echo e($checklist->titulo); ?></td>
                            <td>
                                <span class="badge <?php echo e($checklist->concluido ? 'badge-green' : 'badge-yellow'); ?>">
                                    <?php echo e($checklist->concluido ? 'Concluído' : 'Pendente'); ?>

                                </span>
                            </td>
                            <td>
                                <?php echo e($checklist->concluido_em?->format('d/m/Y H:i') ?: '-'); ?>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($checklist->concluidoPor?->name): ?>
                                    <br><?php echo e($checklist->concluidoPor->name); ?>

                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </tbody>
                </table>
                <div class="subtitle mt-8">Progresso: <?php echo e($checklistConcluidos); ?>/<?php echo e($checklistTotal); ?> concluídos.</div>
            <?php else: ?>
                <div class="empty">Nenhum checklist cadastrado para este item.</div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <div class="section">
        <div class="section-header">
            <h2 class="section-title">Aprovação</h2>
        </div>
        <div class="section-body">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($aprovacao): ?>
                <table class="grid">
                    <tr>
                        <td>
                            <div class="field">
                                <div class="field-label">Status</div>
                                <div class="field-value"><?php echo e($aprovacao->getStatusExibicao()); ?></div>
                            </div>
                        </td>
                        <td>
                            <div class="field">
                                <div class="field-label">Solicitado em</div>
                                <div class="field-value"><?php echo e($aprovacao->solicitado_em?->format('d/m/Y H:i') ?: '-'); ?></div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="field">
                                <div class="field-label">Solicitante</div>
                                <div class="field-value"><?php echo e($aprovacao->solicitante?->name ?: '-'); ?></div>
                            </div>
                        </td>
                        <td>
                            <div class="field">
                                <div class="field-label">Aprovador</div>
                                <div class="field-value"><?php echo e($aprovacao->aprovador?->name ?: '-'); ?></div>
                            </div>
                        </td>
                    </tr>
                </table>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(filled($aprovacao->observacao_solicitacao)): ?>
                    <div class="field-label mt-8">Observação da solicitação</div>
                    <div class="description-box"><?php echo e($aprovacao->observacao_solicitacao); ?></div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(filled($aprovacao->observacao_resposta) || filled($aprovacao->motivo_reprovacao)): ?>
                    <div class="field-label mt-8">Resposta</div>
                    <div class="description-box"><?php echo e($aprovacao->motivo_reprovacao ?: $aprovacao->observacao_resposta); ?></div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php else: ?>
                <div class="empty">Nenhuma aprovação registrada para este item.</div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>

    <div class="section">
        <div class="section-header">
            <h2 class="section-title">Timeline</h2>
        </div>
        <div class="section-body">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->timelines->isNotEmpty()): ?>
                <table class="table">
                    <thead>
                    <tr>
                        <th style="width: 18%;">Data</th>
                        <th style="width: 22%;">Tipo</th>
                        <th style="width: 60%;">Descrição</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $item->timelines->take(12); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $timeline): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr>
                            <td><?php echo e($timeline->created_at?->format('d/m/Y H:i') ?: '-'); ?></td>
                            <td><?php echo e($timeline->getTipoExibicao()); ?></td>
                            <td>
                                <strong><?php echo e($timeline->titulo); ?></strong>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(filled($timeline->descricao)): ?>
                                    <br><?php echo e($timeline->descricao); ?>

                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($timeline->user?->name): ?>
                                    <br><span class="subtitle">Usuário: <?php echo e($timeline->user->name); ?></span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </td>
                        </tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty">Nenhum histórico registrado para este item.</div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views\pdf\item-controle.blade.php ENDPATH**/ ?>