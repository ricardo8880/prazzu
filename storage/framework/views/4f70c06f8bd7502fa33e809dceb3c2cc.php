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

    <?php
        $metrics = $data['metrics'] ?? [];
        $emptyMessage = $data['emptyMessage'] ?? null;
    ?>
    <div class="fin-suite">
        <section class="fin-hero">
            <div>
                <h1><?php echo e($title); ?></h1>
                <p><?php echo e($subtitle); ?></p>
            </div>
            <div class="fin-hero-badge">Dados reais do banco · sem exemplos estáticos</div>
        </section>

        <section class="fin-toolbar">
            <input type="search" wire:model.live.debounce.350ms="search" placeholder="Buscar cliente, e-mail, plano, assinatura ou ID de pagamento...">
            <div class="fin-selects">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pageType === 'financeiro'): ?>
                    <select wire:model.live="period">
                        <option value="7">Últimos 7 dias</option>
                        <option value="30">Últimos 30 dias</option>
                        <option value="90">Últimos 90 dias</option>
                        <option value="180">Últimos 180 dias</option>
                        <option value="365">Últimos 365 dias</option>
                    </select>
                <?php elseif($pageType === 'cobrancas'): ?>
                    <select wire:model.live="statusFilter">
                        <option value="all">Todas as cobranças</option>
                        <option value="open">Pendentes</option>
                        <option value="overdue">Vencidas</option>
                        <option value="due_soon">Vencem em 7 dias</option>
                        <option value="paid">Recebidas</option>
                    </select>
                <?php elseif($pageType === 'assinaturas'): ?>
                    <select wire:model.live="statusFilter">
                        <option value="all">Todas as assinaturas</option>
                        <option value="active">Ativas</option>
                        <option value="renewal">Renovam em 15 dias</option>
                        <option value="paused">Pausadas</option>
                        <option value="canceled">Canceladas</option>
                    </select>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </section>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($emptyMessage): ?>
            <div class="fin-empty"><?php echo e($emptyMessage); ?></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($metrics)): ?>
            <section class="fin-metrics">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $metrics; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $metric): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <article class="fin-card fin-metric fin-tone-<?php echo e($metric['tone'] ?? 'info'); ?>">
                        <small><?php echo e($metric['label']); ?></small>
                        <strong><?php echo e($metric['value']); ?></strong>
                        <span><?php echo e($metric['hint']); ?></span>
                    </article>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </section>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pageType === 'cobrancas'): ?>
            <section class="fin-grid-2">
                <article class="fin-card">
                    <div class="fin-section-title">
                        <div><h2>Régua de cobranças</h2><p>Abra uma cobrança para ver dados do cliente, link e ações rápidas.</p></div>
                    </div>
                    <table class="fin-table">
                        <thead><tr><th>Cliente</th><th>Plano</th><th>Vencimento</th><th>Status</th><th>Valor</th><th></th></tr></thead>
                        <tbody>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($data['payments'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <tr <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'payment-'.e($payment['id']).''; ?>wire:key="payment-<?php echo e($payment['id']); ?>">
                                    <td><div class="fin-main"><strong><?php echo e($payment['cliente']); ?></strong><span><?php echo e($payment['email'] ?: 'Sem e-mail cadastrado'); ?></span></div></td>
                                    <td><?php echo e($payment['plano']); ?><br><span class="fin-main"><span><?php echo e($payment['ciclo']); ?></span></span></td>
                                    <td><?php echo e($payment['vencimento'] ?: '-'); ?></td>
                                    <td><span class="fin-pill <?php echo e($payment['status_tone']); ?>"><?php echo e($payment['status_label']); ?></span></td>
                                    <td><strong><?php echo e($payment['valor_formatado']); ?></strong></td>
                                    <td><button type="button" class="fin-btn primary" wire:click="abrirCobranca(<?php echo e($payment['id']); ?>)">Abrir</button></td>
                                </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <tr><td colspan="6"><div class="fin-empty">Nenhuma cobrança encontrada.</div></td></tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </article>

                <aside class="fin-card">
                    <div class="fin-section-title"><div><h2>Prioridades de cobrança</h2><p>Use essa lista para saber quem contatar primeiro.</p></div></div>
                    <div class="fin-list">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($data['alerts'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <div class="fin-list-row fin-alert <?php echo e($alert['tone'] ?? 'info'); ?>"><div><strong><?php echo e($alert['title']); ?></strong><span><?php echo e($alert['text']); ?></span></div></div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <div class="fin-empty">Nenhuma cobrança vencida nos filtros atuais.</div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </aside>
            </section>
        <?php elseif($pageType === 'assinaturas'): ?>
            <section class="fin-card">
                <div class="fin-section-title">
                    <div><h2>Carteira de assinaturas</h2><p>Controle plano, recorrência, próxima renovação e status sem telas repetidas.</p></div>
                </div>
                <table class="fin-table">
                    <thead><tr><th>Cliente</th><th>Plano</th><th>Recorrência</th><th>Próximo vencimento</th><th>Status</th><th>Valor</th><th></th></tr></thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($data['subscriptions'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $subscription): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <tr <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'subscription-'.e($subscription['id']).''; ?>wire:key="subscription-<?php echo e($subscription['id']); ?>">
                                <td><div class="fin-main"><strong><?php echo e($subscription['cliente']); ?></strong><span><?php echo e($subscription['email'] ?: 'Sem e-mail cadastrado'); ?></span></div></td>
                                <td><?php echo e($subscription['plano']); ?></td>
                                <td><?php echo e($subscription['ciclo']); ?></td>
                                <td><?php echo e($subscription['proximo_vencimento'] ?: '-'); ?></td>
                                <td><span class="fin-pill <?php echo e($subscription['status_tone']); ?>"><?php echo e($subscription['status_label']); ?></span></td>
                                <td><strong><?php echo e($subscription['valor_formatado']); ?></strong></td>
                                <td><button type="button" class="fin-btn primary" wire:click="abrirAssinatura(<?php echo e($subscription['id']); ?>)">Abrir</button></td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <tr><td colspan="7"><div class="fin-empty">Nenhuma assinatura encontrada.</div></td></tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </section>
        <?php else: ?>
            <section class="fin-grid-2">
                <article class="fin-card">
                    <div class="fin-section-title"><div><h2>Fluxo dos últimos meses</h2><p>Compara valor previsto e recebido para enxergar tendência.</p></div></div>
                    <div class="fin-list">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($data['cashflow'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $month): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <div class="fin-list-row">
                                <div style="width: 100%;">
                                    <div style="display:flex;justify-content:space-between;gap:.75rem;"><strong><?php echo e($month['label']); ?></strong><span>Recebido <?php echo e($month['recebido']); ?> · Previsto <?php echo e($month['previsto']); ?></span></div>
                                    <div class="fin-progress"><i style="width: <?php echo e($month['percent']); ?>%"></i></div>
                                </div>
                            </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <div class="fin-empty">Ainda não há dados suficientes para fluxo financeiro.</div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </article>

                <aside class="fin-card">
                    <div class="fin-section-title"><div><h2>Alertas financeiros</h2><p>O que precisa de atenção agora.</p></div></div>
                    <div class="fin-list">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($data['alerts'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <div class="fin-list-row fin-alert <?php echo e($alert['tone'] ?? 'info'); ?>"><div><strong><?php echo e($alert['title']); ?></strong><span><?php echo e($alert['text']); ?></span></div></div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <div class="fin-empty">Nenhum alerta financeiro nos filtros atuais.</div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </aside>
            </section>

            <section class="fin-grid-2">
                <article class="fin-card">
                    <div class="fin-section-title"><div><h2>Clientes com maior receita recebida</h2><p>Ajuda a priorizar relacionamento e retenção.</p></div></div>
                    <div class="fin-list">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($data['topClients'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $client): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <div class="fin-list-row"><div><strong><?php echo e($client['cliente']); ?></strong><span><?php echo e($client['email'] ?: 'Sem e-mail'); ?> · <?php echo e($client['total_pagamentos']); ?> pagamento(s)</span></div><strong><?php echo e($client['total']); ?></strong></div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <div class="fin-empty">Nenhum recebimento confirmado encontrado.</div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </article>

                <article class="fin-card">
                    <div class="fin-section-title"><div><h2>Últimas cobranças</h2><p>Resumo operacional para não precisar alternar telas.</p></div></div>
                    <div class="fin-list">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($data['recentPayments'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <div class="fin-list-row"><div><strong><?php echo e($payment['cliente']); ?></strong><span><?php echo e($payment['vencimento'] ?: '-'); ?> · <?php echo e($payment['plano']); ?></span></div><div style="text-align:right"><strong><?php echo e($payment['valor_formatado']); ?></strong><br><span class="fin-pill <?php echo e($payment['status_tone']); ?>"><?php echo e($payment['status_label']); ?></span></div></div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <div class="fin-empty">Nenhuma cobrança encontrada.</div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </article>
            </section>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($paymentModalOpen ?? false) && ! empty($selectedPayment)): ?>
        <div class="fin-modal-backdrop" wire:click.self="fecharModal">
            <section class="fin-modal">
                <header>
                    <div><h2 style="margin:0;font-weight:900;">Cobrança #<?php echo e($selectedPayment['id']); ?></h2><p style="margin:.2rem 0 0;color:#64748b;"><?php echo e($selectedPayment['cliente']); ?> · <?php echo e($selectedPayment['valor_formatado']); ?></p></div>
                    <button type="button" class="fin-btn" wire:click="fecharModal">Fechar</button>
                </header>
                <div class="fin-modal-body">
                    <div class="fin-details">
                        <div class="fin-detail"><small>Status</small><span class="fin-pill <?php echo e($selectedPayment['status_tone']); ?>"><?php echo e($selectedPayment['status_label']); ?></span></div>
                        <div class="fin-detail"><small>Vencimento</small><strong><?php echo e($selectedPayment['vencimento'] ?: '-'); ?></strong></div>
                        <div class="fin-detail"><small>Cliente</small><strong><?php echo e($selectedPayment['cliente']); ?></strong></div>
                        <div class="fin-detail"><small>Contato</small><strong><?php echo e($selectedPayment['email'] ?: '-'); ?></strong><br><?php echo e($selectedPayment['telefone'] ?: ''); ?></div>
                        <div class="fin-detail"><small>Plano</small><strong><?php echo e($selectedPayment['plano']); ?></strong><br><?php echo e($selectedPayment['ciclo']); ?></div>
                        <div class="fin-detail"><small>Gateway</small><strong><?php echo e($selectedPayment['gateway_payment_id'] ?: '-'); ?></strong></div>
                    </div>
                    <div class="fin-actions">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $selectedPayment['is_paid']): ?>
                            <button type="button" class="fin-btn success" wire:click="marcarComoRecebida(<?php echo e($selectedPayment['id']); ?>)">Marcar como recebida</button>
                        <?php else: ?>
                            <button type="button" class="fin-btn warning" wire:click="marcarComoPendente(<?php echo e($selectedPayment['id']); ?>)">Voltar para pendente</button>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($selectedPayment['invoice_url'])): ?>
                            <a href="<?php echo e($selectedPayment['invoice_url']); ?>" target="_blank" rel="noopener" class="fin-btn primary">Abrir link de pagamento</a>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </section>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($subscriptionModalOpen ?? false) && ! empty($selectedSubscription)): ?>
        <div class="fin-modal-backdrop" wire:click.self="fecharModal">
            <section class="fin-modal">
                <header>
                    <div><h2 style="margin:0;font-weight:900;">Assinatura #<?php echo e($selectedSubscription['id']); ?></h2><p style="margin:.2rem 0 0;color:#64748b;"><?php echo e($selectedSubscription['cliente']); ?> · <?php echo e($selectedSubscription['plano']); ?></p></div>
                    <button type="button" class="fin-btn" wire:click="fecharModal">Fechar</button>
                </header>
                <div class="fin-modal-body">
                    <div class="fin-details">
                        <div class="fin-detail"><small>Status</small><span class="fin-pill <?php echo e($selectedSubscription['status_tone']); ?>"><?php echo e($selectedSubscription['status_label']); ?></span></div>
                        <div class="fin-detail"><small>Próximo vencimento</small><strong><?php echo e($selectedSubscription['proximo_vencimento'] ?: '-'); ?></strong></div>
                        <div class="fin-detail"><small>Cliente</small><strong><?php echo e($selectedSubscription['cliente']); ?></strong></div>
                        <div class="fin-detail"><small>Contato</small><strong><?php echo e($selectedSubscription['email'] ?: '-'); ?></strong><br><?php echo e($selectedSubscription['telefone'] ?: ''); ?></div>
                        <div class="fin-detail"><small>Plano e ciclo</small><strong><?php echo e($selectedSubscription['plano']); ?></strong><br><?php echo e($selectedSubscription['ciclo']); ?></div>
                        <div class="fin-detail"><small>Valor</small><strong><?php echo e($selectedSubscription['valor_formatado']); ?></strong></div>
                        <div class="fin-detail"><small>Gateway</small><strong><?php echo e($selectedSubscription['gateway']); ?></strong><br><?php echo e($selectedSubscription['gateway_subscription_id'] ?: '-'); ?></div>
                        <div class="fin-detail"><small>Cancelada em</small><strong><?php echo e($selectedSubscription['cancelado_em'] ?: '-'); ?></strong></div>
                    </div>
                    <div class="fin-actions">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $selectedSubscription['is_active']): ?>
                            <button type="button" class="fin-btn success" wire:click="ativarAssinatura(<?php echo e($selectedSubscription['id']); ?>)">Ativar</button>
                        <?php else: ?>
                            <button type="button" class="fin-btn warning" wire:click="pausarAssinatura(<?php echo e($selectedSubscription['id']); ?>)">Pausar</button>
                            <button type="button" class="fin-btn danger" wire:click="cancelarAssinatura(<?php echo e($selectedSubscription['id']); ?>)">Cancelar</button>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
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
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views\filament\pages\financeiro-suite.blade.php ENDPATH**/ ?>