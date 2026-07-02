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

<div class="fincli-page">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $instalado): ?>
            <section class="fincli-alert">
                <strong>Módulo financeiro do cliente ainda não instalado</strong>
                <span>Execute o SQL em <code>sql/financeiro_cliente.sql</code>. Tabelas faltantes: <?php echo e(implode(', ', $faltantes)); ?></span>
            </section>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <section class="fincli-hero">
            <div>
                <span>RECORRÊNCIA</span>
                <h1>Assinaturas dos clientes</h1>
                <p>Gerencie planos recorrentes dos clientes da empresa logada e gere cobranças sem depender de gateway externo.</p>
            </div>
            <div class="fincli-actions">
                <button type="button" class="primary" wire:click="abrirNovaAssinatura">Nova assinatura</button>
            </div>
        </section>

        <section class="fincli-toolbar">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($empresas) > 1): ?>
                <label>
                    <span>Empresa</span>
                    <select wire:model.live="empresaFiltro"><option value="">Todas</option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $empresas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $empresa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?><option value="<?php echo e($empresa['id']); ?>"><?php echo e($empresa['nome']); ?></option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?></select>
                </label>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <label>
                <span>Status</span>
                <select wire:model.live="statusFiltro">
                    <option value="todas">Todas</option>
                    <option value="ativa">Ativas</option>
                    <option value="pausada">Pausadas</option>
                    <option value="cancelada">Canceladas</option>
                </select>
            </label>
            <label class="grow"><span>Busca</span><input type="search" wire:model.live.debounce.400ms="busca" placeholder="Buscar por cliente ou plano"></label>
        </section>

        <section class="fincli-card">
            <header><div><h2>Planos recorrentes</h2><p>Use “Gerar cobrança” para criar a próxima conta a receber e avançar a data automaticamente.</p></div></header>
            <div class="fincli-table-wrap">
                <table class="fincli-table">
                    <thead><tr><th>Cliente</th><th>Plano</th><th>Valor</th><th>Ciclo</th><th>Próxima cobrança</th><th>Status</th><th>Ações</th></tr></thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $assinaturasCliente; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $assinatura): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <tr>
                                <td><strong><?php echo e($assinatura['cliente_nome'] ?? 'Cliente não informado'); ?></strong><br><small><?php echo e($assinatura['cliente_email'] ?? $assinatura['cliente_documento'] ?? '-'); ?></small></td>
                                <td><strong><?php echo e($assinatura['nome']); ?></strong><br><small><?php echo e(\Illuminate\Support\Str::limit($assinatura['descricao'] ?? 'Sem descrição', 70)); ?></small></td>
                                <td><?php echo e($assinatura['valor_formatado']); ?></td>
                                <td><?php echo e($assinatura['ciclo_label']); ?></td>
                                <td><?php echo e($assinatura['proxima_cobranca_formatada']); ?></td>
                                <td><span class="fincli-badge <?php echo e($assinatura['status_tone']); ?>"><?php echo e(ucfirst($assinatura['status'])); ?></span></td>
                                <td class="fincli-row-actions">
                                    <button type="button" wire:click="abrirAssinatura(<?php echo e($assinatura['id']); ?>)">Abrir</button>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($assinatura['status'] ?? '') === 'ativa'): ?>
                                        <button type="button" wire:click="gerarCobranca(<?php echo e($assinatura['id']); ?>)">Gerar cobrança</button>
                                        <button type="button" wire:click="alterarStatus(<?php echo e($assinatura['id']); ?>, 'pausada')">Pausar</button>
                                    <?php elseif(($assinatura['status'] ?? '') === 'pausada'): ?>
                                        <button type="button" wire:click="alterarStatus(<?php echo e($assinatura['id']); ?>, 'ativa')">Reativar</button>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($assinatura['status'] ?? '') !== 'cancelada'): ?>
                                        <button type="button" class="danger" wire:click="alterarStatus(<?php echo e($assinatura['id']); ?>, 'cancelada')">Cancelar</button>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <tr>
                                <td colspan="7" class="fincli-empty fincli-empty-actionable">
                                    <strong>Nenhuma assinatura encontrada</strong>
                                    <span>Crie uma assinatura recorrente somente quando o cliente financeiro já estiver cadastrado. Use os filtros acima para conferir se não há resultados ocultos.</span>
                                    <button type="button" class="primary" wire:click="abrirNovaAssinatura">Nova assinatura</button>
                                </td>
                            </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($modalAssinaturaAberto): ?>
        <div class="fincli-modal-backdrop" wire:click.self="$set('modalAssinaturaAberto', false)">
            <section class="fincli-modal">
                <header><div><span>ASSINATURA</span><h2><?php echo e($assinaturaSelecionada ? 'Editar assinatura' : 'Nova assinatura'); ?></h2></div><button type="button" wire:click="$set('modalAssinaturaAberto', false)">×</button></header>
                <form wire:submit.prevent="salvarAssinatura" class="fincli-form">
                    <label class="wide"><span>Cliente financeiro</span><select wire:model="form.financeiro_cliente_id"><option value="">Selecione</option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $clientes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cliente): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?><option value="<?php echo e($cliente['id']); ?>"><?php echo e($cliente['nome']); ?></option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?></select><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['form.financeiro_cliente_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="error"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></label>
                    <label class="wide"><span>Nome do plano/serviço</span><input type="text" wire:model.defer="form.nome" placeholder="Ex: Plano mensal, consultoria recorrente"><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['form.nome'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="error"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></label>
                    <label><span>Valor</span><input type="number" min="0.01" step="0.01" wire:model.defer="form.valor"><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['form.valor'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="error"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></label>
                    <label><span>Ciclo</span><select wire:model.defer="form.ciclo"><option value="semanal">Semanal</option><option value="quinzenal">Quinzenal</option><option value="mensal">Mensal</option><option value="trimestral">Trimestral</option><option value="semestral">Semestral</option><option value="anual">Anual</option></select></label>
                    <label><span>Próxima cobrança</span><input type="date" wire:model.defer="form.proxima_cobranca_em"><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['form.proxima_cobranca_em'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <small class="error"><?php echo e($message); ?></small> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></label>
                    <label><span>Forma</span><select wire:model.defer="form.forma_pagamento"><option value="manual">Manual</option><option value="pix">Pix</option><option value="boleto">Boleto</option><option value="cartao">Cartão</option><option value="transferencia">Transferência</option></select></label>
                    <label class="wide"><span>Descrição</span><textarea rows="3" wire:model.defer="form.descricao"></textarea></label>
                    <footer><button type="button" wire:click="$set('modalAssinaturaAberto', false)">Fechar</button><button type="submit" class="primary">Salvar assinatura</button></footer>
                </form>
            </section>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views/filament/pages/assinaturas.blade.php ENDPATH**/ ?>