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
                <span>DASHBOARD</span>
                <h1>Financeiro do cliente</h1>
                <p>Visão simples do contas a receber da empresa, com estrutura pronta para gateway próprio por cliente.</p>
            </div>
            <div class="fincli-actions">
                <button type="button" class="primary" wire:click="abrirGateway">Configurar gateway</button>
            </div>
        </section>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($empresas) > 1): ?>
            <section class="fincli-toolbar compact">
                <label><span>Empresa</span><select wire:model.live="empresaFiltro"><option value="">Todas</option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $empresas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $empresa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?><option value="<?php echo e($empresa['id']); ?>"><?php echo e($empresa['nome']); ?></option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?></select></label>
            </section>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <section class="fincli-stats">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($dashboard['stats'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <article class="<?php echo e($stat['tone'] ?? ''); ?>"><span><?php echo e($stat['label']); ?></span><strong><?php echo e($stat['value']); ?></strong><small><?php echo e($stat['hint']); ?></small></article>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </section>

        <section class="fincli-grid two">
            <article class="fincli-card">
                <header><div><h2>Próximos 30 dias</h2><p>Previsão de entrada baseada nas cobranças abertas.</p></div></header>
                <div class="fincli-list">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($dashboard['fluxo'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dia): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="fincli-list-row"><div><strong><?php echo e($dia['dia']); ?></strong><span><?php echo e($dia['quantidade']); ?> cobrança(s)</span></div><em><?php echo e($dia['total']); ?></em></div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="fincli-empty">Nenhuma cobrança aberta para os próximos 30 dias.</div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </article>

            <article class="fincli-card">
                <header><div><h2>Integrações de gateway</h2><p>Cada empresa pode ter o próprio gateway. Token salvo criptografado.</p></div></header>
                <div class="fincli-list">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($dashboard['integracoes'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $integracao): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="fincli-list-row">
                            <div><strong><?php echo e($integracao['nome'] ?: strtoupper($integracao['gateway'])); ?></strong><span><?php echo e(strtoupper($integracao['gateway'])); ?> · <?php echo e($integracao['ambiente']); ?> · <?php echo e($integracao['status']); ?></span></div>
                            <button type="button" class="danger" wire:click="desativarGateway(<?php echo e($integracao['id']); ?>)">Desativar</button>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="fincli-empty">Nenhuma integração configurada. O financeiro manual continua funcionando.</div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </article>
        </section>

        <section class="fincli-grid two">
            <article class="fincli-card">
                <header><div><h2>Cobranças que precisam de atenção</h2><p>Lista priorizada por vencidas e abertas.</p></div></header>
                <div class="fincli-list">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($dashboard['vencimentos'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cobranca): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="fincli-list-row">
                            <div><strong><?php echo e($cobranca['cliente_nome'] ?? 'Cliente'); ?></strong><span><?php echo e($cobranca['descricao']); ?> · vence <?php echo e($cobranca['vencimento_formatado']); ?></span></div>
                            <em class="<?php echo e($cobranca['status_tone'] ?? ''); ?>"><?php echo e($cobranca['valor_formatado']); ?></em>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="fincli-empty">Nenhuma cobrança pendente.</div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </article>

            <article class="fincli-card">
                <header><div><h2>Como usar</h2><p>Fluxo pensado para o cliente não se perder.</p></div></header>
                <div class="fincli-steps">
                    <div><strong>1</strong><span>Cadastre o cliente financeiro na aba Cobranças.</span></div>
                    <div><strong>2</strong><span>Crie uma cobrança avulsa ou uma assinatura recorrente.</span></div>
                    <div><strong>3</strong><span>Registre o recebimento manualmente ou conecte o gateway da empresa.</span></div>
                    <div><strong>4</strong><span>Acompanhe vencidos, abertos e recebidos neste dashboard.</span></div>
                </div>
            </article>
        </section>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($modalGatewayAberto): ?>
        <div class="fincli-modal-backdrop" wire:click.self="$set('modalGatewayAberto', false)">
            <section class="fincli-modal small">
                <header><div><span>GATEWAY POR EMPRESA</span><h2>Configurar integração</h2></div><button type="button" wire:click="$set('modalGatewayAberto', false)">×</button></header>
                <form wire:submit.prevent="salvarGateway" class="fincli-form">
                    <label><span>Gateway</span><select wire:model.defer="gatewayForm.gateway"><option value="manual">Manual</option><option value="asaas">Asaas</option><option value="mercado_pago">Mercado Pago</option><option value="stripe">Stripe</option></select></label>
                    <label><span>Ambiente</span><select wire:model.defer="gatewayForm.ambiente"><option value="sandbox">Sandbox</option><option value="producao">Produção</option></select></label>
                    <label class="wide"><span>Nome interno</span><input type="text" wire:model.defer="gatewayForm.nome" placeholder="Ex: Asaas da empresa"></label>
                    <label class="wide"><span>Token/API Key</span><input type="password" wire:model.defer="gatewayForm.api_token" placeholder="Será salvo criptografado"></label>
                    <label class="wide"><span>Webhook secret</span><input type="password" wire:model.defer="gatewayForm.webhook_secret" placeholder="Opcional"></label>
                    <div class="fincli-note wide">Esta tela deixa a estrutura pronta. A criação automática de cobrança via API deve ser ligada no serviço do gateway escolhido.</div>
                    <footer><button type="button" wire:click="$set('modalGatewayAberto', false)">Fechar</button><button type="submit" class="primary">Salvar integração</button></footer>
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
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views\filament\pages\financeiro.blade.php ENDPATH**/ ?>