<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title><?php echo e($item->titulo); ?> - Portal do Cliente</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo e(asset('css/style.css')); ?>?v=lote3">

</head>
<body class="portal-item-controle-public">
<div class="container">
    <div class="header">
        <div class="header-top">
            <div>
                <div class="label">Portal do Cliente</div>
                <h1><?php echo e($item->titulo); ?></h1>

                <div class="empresa">
                    <?php echo e($item->empresa?->nome_fantasia ?: $item->empresa?->razao_social); ?>

                </div>
            </div>

            <div>
                <span class="badge <?php echo e($item->getStatusExibicaoColor()); ?>">
                    <?php echo e($item->getStatusExibicao()); ?>

                </span>
            </div>
        </div>

        <div class="badge-row">
            <span class="badge info">Categoria: <?php echo e($item->getTipoOuCategoria()); ?></span>

            <span class="badge <?php echo e($item->getPrioridadeColor()); ?>">
                Prioridade: <?php echo e($item->getPrioridadeExibicao()); ?>

            </span>

            <span class="badge <?php echo e($item->getSituacaoPrazoColor()); ?>">
                <?php echo e($item->getSituacaoPrazo()); ?>

            </span>

            <span class="badge <?php echo e($item->getAssinaturaColor()); ?>">
                <?php echo e($item->getAssinaturaResumo()); ?>

            </span>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $item->tags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <span class="badge <?php echo e($tag->cor ?: 'gray'); ?>">#<?php echo e($tag->nome); ?></span>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
        <div class="alert-success">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
        <div class="alert-error">
            <strong>Verifique os campos:</strong>
            <ul>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $erro): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <li><?php echo e($erro); ?></li>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </ul>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="grid">
        <main>
            <div class="card">
                <h2>Descrição</h2>
                <div class="text">
                    <?php echo e(filled($item->descricao) ? $item->descricao : 'Nenhuma descrição informada.'); ?>

                </div>
            </div>

            <div class="card">
                <h2>Checklist</h2>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->checklists->isEmpty()): ?>
                    <p class="text">Nenhuma etapa cadastrada.</p>
                <?php else: ?>
                    <div class="label"><?php echo e($item->getChecklistResumo()); ?> concluído — <?php echo e($item->getChecklistPercentual()); ?>%</div>
                    <div class="progress-wrap"><div class="progress-bar" style="width: <?php echo e($item->getChecklistPercentual()); ?>%"></div></div>

                    <ul class="checklist">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $item->checklists; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $checklist): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <li class="checklist-item">
                                <span class="check <?php echo e($checklist->concluido ? 'done' : ''); ?>">
                                    <?php echo e($checklist->concluido ? '✓' : '•'); ?>

                                </span>

                                <div>
                                    <strong><?php echo e($checklist->titulo); ?></strong>

                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($checklist->concluido && $checklist->concluido_em): ?>
                                        <div class="label">
                                            Concluído em <?php echo e($checklist->concluido_em->format('d/m/Y H:i')); ?>

                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </li>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </ul>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>


            <div class="card">
                <h2>Etapas operacionais</h2>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->etapasOperacionais->isEmpty()): ?>
                    <p class="text">Nenhuma etapa operacional cadastrada.</p>
                <?php else: ?>
                    <ul class="checklist">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $item->etapasOperacionais; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $etapa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <li class="checklist-item">
                                <span class="check <?php echo e($etapa->status === 'concluida' ? 'done' : ''); ?>">
                                    <?php echo e($etapa->status === 'concluida' ? '✓' : $etapa->ordem); ?>

                                </span>
                                <div>
                                    <strong><?php echo e($etapa->titulo); ?></strong>
                                    <div class="label">
                                        <?php echo e($etapa->getStatusExibicao()); ?> | Tempo: <?php echo e($etapa->getTempoResumo()); ?>

                                    </div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($etapa->descricao): ?>
                                        <div class="text"><?php echo e($etapa->descricao); ?></div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </li>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </ul>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div class="card">
                <h2>Timeline</h2>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->timelines->isEmpty()): ?>
                    <p class="text">Nenhum evento registrado.</p>
                <?php else: ?>
                    <ul class="timeline">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $item->timelines->take(20); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $timeline): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <li>
                                <strong><?php echo e($timeline->getTipoIcone()); ?> <?php echo e($timeline->titulo); ?></strong>
                                <div class="label">
                                    <?php echo e($timeline->getTipoExibicao()); ?> | <?php echo e($timeline->created_at?->format('d/m/Y H:i')); ?> | <?php echo e($timeline->user?->name ?: 'Sistema'); ?>

                                </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($timeline->descricao): ?>
                                    <div class="text"><?php echo e($timeline->descricao); ?></div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </li>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </ul>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div class="card">
                <h2>Anexos</h2>

                <div class="attachments">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(filled($item->arquivo)): ?>
                        <a class="attachment" href="<?php echo e(asset('storage/' . $item->arquivo)); ?>" target="_blank">
                            Abrir anexo principal
                        </a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $item->anexos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $anexo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php
                            $caminho = $anexo->caminho ?? $anexo->arquivo ?? null;
                            $nome = $anexo->nome_original ?? $anexo->nome ?? basename((string) $caminho);
                        ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($caminho): ?>
                            <a class="attachment" href="<?php echo e(asset('storage/' . $caminho)); ?>" target="_blank">
                                <?php echo e($nome); ?>

                            </a>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(blank($item->arquivo) && $item->anexos->isEmpty()): ?>
                        <p class="text">Nenhum anexo disponível.</p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            <div class="card">
                <h2>Enviar documento</h2>
                <p class="text">Use este espaço para enviar arquivos, comprovantes, documentos e evidências diretamente para a equipe.</p><div class="helper-box"><strong>i</strong><span>Após o upload, a equipe será avisada e o arquivo ficará vinculado ao histórico deste item.</span></div>

                <form method="POST" action="<?php echo e(route('portal.item-controles.documentos', ['token' => $item->portal_token])); ?>" enctype="multipart/form-data" class="js-feedback-form" data-confirm="Deseja enviar este documento para a equipe?" data-processing="Enviando documento...">
                    <?php echo csrf_field(); ?>
                    <div class="hp-field" aria-hidden="true"><label>Website</label><input type="text" name="website" tabindex="-1" autocomplete="off"></div>
                    <div class="form-grid">
                        <div class="field">
                            <label for="doc_client_name">Nome *</label>
                            <input type="text" id="doc_client_name" name="client_name" value="<?php echo e(old('client_name', $item->portal_cliente_nome)); ?>" required aria-invalid="<?php echo e($errors->has('client_name') ? 'true' : 'false'); ?>">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['client_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="field-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <div class="field">
                            <label for="doc_client_email">E-mail</label>
                            <input type="email" id="doc_client_email" name="client_email" value="<?php echo e(old('client_email', $item->portal_cliente_email)); ?>" aria-invalid="<?php echo e($errors->has('client_email') ? 'true' : 'false'); ?>">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['client_email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="field-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <div class="field">
                            <label for="documento">Arquivo *</label>
                            <input type="file" id="documento" name="documento" required aria-invalid="<?php echo e($errors->has('documento') ? 'true' : 'false'); ?>">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['documento'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="field-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <div class="field">
                            <label for="observacao">Observação</label>
                            <textarea id="observacao" name="observacao" rows="4" aria-invalid="<?php echo e($errors->has('observacao') ? 'true' : 'false'); ?>"><?php echo e(old('observacao')); ?></textarea>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['observacao'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="field-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <div class="form-feedback"><span class="spinner"></span><span>Enviando documento...</span></div><button class="button" type="submit">Enviar documento</button>
                    </div>
                </form>
            </div>

            <div class="card">
                <h2>Mensagens</h2>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->clientPortalMessages->isNotEmpty()): ?>
                    <ul class="timeline">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $item->clientPortalMessages->take(10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <li>
                                <strong><?php echo e($message->client_name ?: 'Cliente'); ?></strong>
                                <div class="label"><?php echo e($message->created_at?->format('d/m/Y H:i')); ?> <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($message->client_email): ?> | <?php echo e($message->client_email); ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></div>
                                <div class="text"><?php echo e($message->message); ?></div>
                            </li>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </ul>
                <?php else: ?>
                    <p class="text">Nenhuma mensagem enviada pelo portal ainda.</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <form method="POST" action="<?php echo e(route('portal.item-controles.mensagem', ['token' => $item->portal_token])); ?>" style="margin-top: 18px;" class="js-feedback-form" data-confirm="Deseja enviar esta mensagem?" data-processing="Enviando mensagem...">
                    <?php echo csrf_field(); ?>
                    <div class="hp-field" aria-hidden="true"><label>Website</label><input type="text" name="website" tabindex="-1" autocomplete="off"></div>
                    <div class="form-grid">
                        <div class="field">
                            <label for="msg_client_name">Nome *</label>
                            <input type="text" id="msg_client_name" name="client_name" value="<?php echo e(old('client_name', $item->portal_cliente_nome)); ?>" required aria-invalid="<?php echo e($errors->has('client_name') ? 'true' : 'false'); ?>">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['client_name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="field-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <div class="field">
                            <label for="msg_client_email">E-mail</label>
                            <input type="email" id="msg_client_email" name="client_email" value="<?php echo e(old('client_email', $item->portal_cliente_email)); ?>" aria-invalid="<?php echo e($errors->has('client_email') ? 'true' : 'false'); ?>">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['client_email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="field-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <div class="field">
                            <label for="message">Mensagem *</label>
                            <textarea id="message" name="message" rows="5" required aria-invalid="<?php echo e($errors->has('message') ? 'true' : 'false'); ?>"><?php echo e(old('message')); ?></textarea>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['message'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="field-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <div class="form-feedback"><span class="spinner"></span><span>Enviando mensagem...</span></div><button class="button" type="submit">Enviar mensagem</button>
                    </div>
                </form>
            </div>

            <div class="card">
                <h2>Assinatura interna</h2>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->assinaturas->isNotEmpty()): ?>
                    <div class="signature-box">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $item->assinaturas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $assinatura): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <div>
                                <strong><?php echo e($assinatura->nome); ?></strong>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($assinatura->email): ?>
                                    — <?php echo e($assinatura->email); ?>

                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>

                            <div class="label">
                                Assinado em <?php echo e($assinatura->assinado_em?->format('d/m/Y H:i')); ?>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($assinatura->ip): ?>
                                    | IP: <?php echo e($assinatura->ip); ?>

                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>

                            <div class="hash">
                                Hash: <?php echo e($assinatura->hash_assinatura); ?>

                            </div>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $loop->last): ?>
                                <hr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>
                <?php else: ?>
                    <p class="text">
                        Preencha os dados abaixo para registrar sua assinatura eletrônica interna.
                    </p>

                    <form method="POST" action="<?php echo e(route('portal.item-controles.assinar', ['token' => $item->portal_token])); ?>" class="js-feedback-form" data-confirm="Confirma o registro da assinatura eletrônica?" data-processing="Registrando assinatura...">
                        <?php echo csrf_field(); ?>
                        <div class="hp-field" aria-hidden="true"><label>Website</label><input type="text" name="website" tabindex="-1" autocomplete="off"></div>

                        <div class="form-grid">
                            <div class="field">
                                <label for="nome">Nome completo *</label>
                                <input
                                    type="text"
                                    id="nome"
                                    name="nome"
                                    value="<?php echo e(old('nome', $item->portal_cliente_nome)); ?>"
                                    required
                                    aria-invalid="<?php echo e($errors->has('nome') ? 'true' : 'false'); ?>"
                                >
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['nome'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="field-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>

                            <div class="field">
                                <label for="email">E-mail</label>
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    value="<?php echo e(old('email', $item->portal_cliente_email)); ?>"
                                    aria-invalid="<?php echo e($errors->has('email') ? 'true' : 'false'); ?>"
                                >
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="field-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>

                            <div class="field">
                                <label for="assinatura_documento">Documento/CPF/CNPJ</label>
                                <input
                                    type="text"
                                    id="assinatura_documento"
                                    name="documento"
                                    value="<?php echo e(old('documento')); ?>"
                                    aria-invalid="<?php echo e($errors->has('documento') ? 'true' : 'false'); ?>"
                                >
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['documento'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="field-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>

                            <label class="checkbox-row">
                                <input type="checkbox" name="aceite" value="1" required aria-invalid="<?php echo e($errors->has('aceite') ? 'true' : 'false'); ?>">
                                <span>
                                    Declaro que li e concordo com as informações apresentadas neste item/documento,
                                    registrando minha assinatura eletrônica interna.
                                </span>
                            </label>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['aceite'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="field-error"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            <div class="form-feedback"><span class="spinner"></span><span>Registrando assinatura...</span></div>
                            <button class="button" type="submit">
                                Registrar assinatura
                            </button>
                        </div>
                    </form>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </main>

        <aside>
            <div class="card">
                <h2>Informações</h2>

                <div class="info-list">
                    <div class="info-item">
                        <div class="info-title">Vencimento</div>
                        <div class="info-value">
                            <?php echo e($item->data_vencimento ? $item->data_vencimento->format('d/m/Y') : 'Sem prazo'); ?>

                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-title">Conclusão</div>
                        <div class="info-value">
                            <?php echo e($item->data_conclusao ? $item->data_conclusao->format('d/m/Y') : 'Não concluído'); ?>

                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-title">Responsável</div>
                        <div class="info-value">
                            <?php echo e($item->responsavel?->nome ?: 'Não informado'); ?>

                        </div>
                    </div>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->responsavel?->email): ?>
                        <div class="info-item">
                            <div class="info-title">E-mail do responsável</div>
                            <div class="info-value"><?php echo e($item->responsavel->email); ?></div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->portal_cliente_nome): ?>
                        <div class="info-item">
                            <div class="info-title">Cliente</div>
                            <div class="info-value"><?php echo e($item->portal_cliente_nome); ?></div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->portal_expira_em): ?>
                        <div class="info-item">
                            <div class="info-title">Link válido até</div>
                            <div class="info-value"><?php echo e($item->portal_expira_em->format('d/m/Y H:i')); ?></div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            <div class="card">
                <h2>Observação</h2>
                <div class="text">
                    <?php echo e(filled($item->observacao) ? $item->observacao : 'Nenhuma observação informada.'); ?>

                </div>
            </div>
        </aside>
    </div>

    <div class="footer">
        Este portal é um acesso externo seguro gerado para acompanhamento e assinatura deste item.
    </div>
</div>
<script>
    document.querySelectorAll('.js-feedback-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            var confirmText = form.getAttribute('data-confirm');
            if (confirmText && ! window.confirm(confirmText)) {
                event.preventDefault();
                return false;
            }

            form.classList.add('is-processing');
            form.querySelectorAll('button[type="submit"]').forEach(function (button) {
                button.disabled = true;
                button.textContent = form.getAttribute('data-processing') || 'Processando...';
            });
        });
    });
</script>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views\portal\item-controle-show.blade.php ENDPATH**/ ?>