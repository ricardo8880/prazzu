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

    <link rel="stylesheet" href="<?php echo e(asset('css/central-aprovacoes.css')); ?>?v=<?php echo e(file_exists(public_path('css/central-aprovacoes.css')) ? filemtime(public_path('css/central-aprovacoes.css')) : time()); ?>">

    <div class="ca-page">
        <section class="ca-hero <?php echo e($diagnostico['tom'] ?? 'info'); ?>">
            <div>
                <span>GOVERNANÇA / APROVAÇÕES</span>
                <h1><?php echo e($diagnostico['titulo'] ?? 'Central de Aprovações'); ?></h1>
                <p><?php echo e($diagnostico['descricao'] ?? 'Fila diária para aprovar, reprovar com comentário e acompanhar decisões com rastreabilidade.'); ?></p>
                <strong><?php echo e($diagnostico['acao'] ?? 'Revise a fila priorizada'); ?></strong>
            </div>

            <div class="ca-hero-actions">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $atalhos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $atalho): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <a class="<?php echo e(! empty($atalho['primary']) ? 'primary' : ''); ?>" href="<?php echo e($atalho['url']); ?>"><?php echo e($atalho['label']); ?></a>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
        </section>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $temTabelaAprovacoes): ?>
            <section class="ca-empty ca-empty-main">
                <strong>Central pronta, mas a tabela item_controle_aprovacoes não existe no banco.</strong>
                <p>Crie a estrutura de aprovações do projeto para que a fila seja carregada automaticamente com dados reais.</p>
            </section>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <section class="ca-stats">
            <article><span>Total</span><strong><?php echo e(number_format($resumo['total'] ?? 0, 0, ',', '.')); ?></strong><small>Solicitações no seu escopo</small></article>
            <article class="warning"><span>Pendentes</span><strong><?php echo e(number_format($resumo['pendentes'] ?? 0, 0, ',', '.')); ?></strong><small>Aguardando decisão</small></article>
            <article class="danger"><span>Atrasadas</span><strong><?php echo e(number_format($resumo['atrasadas'] ?? 0, 0, ',', '.')); ?></strong><small>Devem ser tratadas primeiro</small></article>
            <article class="critical"><span>Críticas</span><strong><?php echo e(number_format($resumo['criticas'] ?? 0, 0, ',', '.')); ?></strong><small>Alta prioridade aberta</small></article>
            <article class="success"><span>Resolvidas</span><strong><?php echo e(number_format(($resumo['aprovadas'] ?? 0) + ($resumo['reprovadas'] ?? 0), 0, ',', '.')); ?></strong><small><?php echo e($resumo['taxaResolucao'] ?? 0); ?>% da fila total</small></article>
            <article><span>Tempo médio</span><strong><?php echo e($resumo['tempoMedio'] ?? '0h'); ?></strong><small>Resposta das decisões</small></article>
        </section>

        <section class="ca-focus-panel">
            <div class="ca-section-header">
                <div>
                    <span class="ca-kicker">Prioridade operacional</span>
                    <h2>O que precisa da sua atenção agora</h2>
                    <p>Itens atrasados e críticos aparecem aqui para o usuário decidir sem perder tempo analisando toda a fila.</p>
                </div>
                <strong><?php echo e(count($atencaoAgora)); ?> prioridade(s)</strong>
            </div>

            <div class="ca-focus-grid">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $atencaoAgora; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php echo $__env->make('filament.pages.partials.central-aprovacoes-card', ['item' => $item, 'compacto' => false, 'destaque' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <div class="ca-empty ca-empty-main"><strong>Nenhum alerta urgente.</strong><p>Não há aprovação atrasada ou crítica no seu escopo atual.</p></div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </section>

        <section class="ca-toolbar">
            <div>
                <label>Buscar</label>
                <input type="search" wire:model.live.debounce.400ms="busca" placeholder="Item, empresa ou descrição">
            </div>
            <div>
                <label>Status</label>
                <select wire:model.live="statusFiltro">
                    <option value="todos">Todos</option>
                    <option value="pendente">Pendentes</option>
                    <option value="aprovado">Aprovadas</option>
                    <option value="reprovado">Ajuste solicitado</option>
                </select>
            </div>
            <div>
                <label>Prioridade</label>
                <select wire:model.live="prioridadeFiltro">
                    <option value="todas">Todas</option>
                    <option value="critica">Crítica</option>
                    <option value="crítica">Crítica acentuada</option>
                    <option value="alta">Alta</option>
                    <option value="media">Média</option>
                    <option value="baixa">Baixa</option>
                </select>
            </div>
            <button type="button" wire:click="limparFiltros">Limpar filtros</button>
        </section>

        <section class="ca-layout">
            <div class="ca-main-card">
                <div class="ca-section-header">
                    <div>
                        <span class="ca-kicker">Fila principal</span>
                        <h2>Fila priorizada</h2>
                        <p>Ordenada por atraso, prioridade, vencimento e tempo aguardando. Todas as ações validam permissão antes de decidir.</p>
                    </div>
                    <span><?php echo e(count($fila)); ?> item(ns)</span>
                </div>

                <div class="ca-list">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $fila; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php echo $__env->make('filament.pages.partials.central-aprovacoes-card', ['item' => $item, 'compacto' => false, 'destaque' => false], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="ca-empty ca-empty-main"><strong>Nenhuma aprovação encontrada.</strong><p>Ajuste os filtros ou cadastre uma solicitação para alimentar a central.</p></div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            <aside class="ca-side">
                <section class="ca-main-card">
                    <div class="ca-section-header compact"><div><span class="ca-kicker">Visão rápida</span><h2>Kanban resumido</h2><p>Amostra das aprovações por status.</p></div></div>
                    <div class="ca-mini-kanban">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $kanban; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $coluna): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <article class="<?php echo e($coluna['tom']); ?>">
                                <header><strong><?php echo e($coluna['titulo']); ?></strong><span><?php echo e(count($coluna['items'])); ?></span></header>
                                <p><?php echo e($coluna['descricao']); ?></p>
                            </article>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>
                </section>

                <section class="ca-main-card">
                    <div class="ca-section-header compact"><div><span class="ca-kicker">Gargalos</span><h2>Por responsável</h2><p>Pendências abertas por pessoa.</p></div></div>
                    <div class="ca-ranking">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $responsaveis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $responsavel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <div><span><?php echo e($responsavel['nome']); ?></span><strong><?php echo e($responsavel['total']); ?></strong></div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <div class="ca-empty"><strong>Sem gargalo.</strong><p>Nenhuma aprovação pendente por responsável.</p></div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </section>

                <section class="ca-main-card">
                    <div class="ca-section-header compact"><div><span class="ca-kicker">Rastreabilidade</span><h2>Histórico</h2><p>Últimas respostas registradas.</p></div></div>
                    <div class="ca-mini-timeline">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $historico; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <article class="<?php echo e($item['tom']); ?>">
                                <strong><?php echo e($item['titulo']); ?></strong>
                                <span><?php echo e($item['status_label']); ?> • <?php echo e($item['respondido_em']); ?> • <?php echo e($item['aprovador']); ?></span>
                            </article>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <div class="ca-empty"><strong>Nenhuma decisão ainda.</strong><p>Ao aprovar ou solicitar ajuste, o histórico aparece aqui.</p></div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </section>
            </aside>
        </section>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($detalhesEmVisualizacao): ?>
        <div class="ca-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="ca-detalhes-titulo">
            <div class="ca-modal-card ca-modal-card-wide ca-detail-modal">
                <div class="ca-modal-head">
                    <div>
                        <span class="ca-kicker">Detalhes da aprovação</span>
                        <h2 id="ca-detalhes-titulo"><?php echo e($detalhesEmVisualizacao['titulo']); ?></h2>
                        <p>Revise contexto, prazos, risco e histórico sem sair da Central de Aprovações.</p>
                    </div>
                    <button type="button" class="ca-icon-button" wire:click="fecharDetalhesItem" aria-label="Fechar detalhes">×</button>
                </div>

                <div class="ca-modal-warning <?php echo e($detalhesEmVisualizacao['tom']); ?>">
                    <strong><?php echo e($detalhesEmVisualizacao['status_label']); ?></strong>
                    <p><?php echo e($detalhesEmVisualizacao['decisao_alerta']); ?></p>
                </div>

                <div class="ca-detail-grid">
                    <div><span>Empresa</span><strong><?php echo e($detalhesEmVisualizacao['empresa']); ?></strong></div>
                    <div><span>Tipo</span><strong><?php echo e($detalhesEmVisualizacao['tipo']); ?></strong></div>
                    <div><span>Prioridade</span><strong><?php echo e($detalhesEmVisualizacao['prioridade']); ?></strong></div>
                    <div><span>Vencimento</span><strong class="<?php echo e($detalhesEmVisualizacao['atrasado'] ? 'ca-text-danger' : ''); ?>"><?php echo e($detalhesEmVisualizacao['vencimento']); ?></strong></div>
                    <div><span>Status do item</span><strong><?php echo e($detalhesEmVisualizacao['item_status']); ?></strong></div>
                    <div><span>Status da aprovação</span><strong><?php echo e($detalhesEmVisualizacao['approval_status']); ?></strong></div>
                    <div><span>Documento</span><strong><?php echo e($detalhesEmVisualizacao['document_status']); ?></strong></div>
                    <div><span>Assinatura</span><strong><?php echo e($detalhesEmVisualizacao['signature_status']); ?></strong></div>
                </div>

                <div class="ca-detail-columns">
                    <section>
                        <h3>Descrição do item</h3>
                        <p><?php echo e($detalhesEmVisualizacao['descricao_completa']); ?></p>
                    </section>
                    <section>
                        <h3>Observação operacional</h3>
                        <p><?php echo e($detalhesEmVisualizacao['item_observacao']); ?></p>
                    </section>
                    <section>
                        <h3>Pedido de aprovação</h3>
                        <p><?php echo e($detalhesEmVisualizacao['observacao']); ?></p>
                    </section>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($detalhesEmVisualizacao['resposta'])): ?>
                        <section>
                            <h3>Última resposta</h3>
                            <p><?php echo e($detalhesEmVisualizacao['resposta']); ?></p>
                        </section>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div class="ca-detail-grid ca-detail-grid-compact">
                    <div><span>Responsável</span><strong><?php echo e($detalhesEmVisualizacao['responsavel']); ?></strong></div>
                    <div><span>Solicitante</span><strong><?php echo e($detalhesEmVisualizacao['solicitante']); ?></strong></div>
                    <div><span>Aprovador</span><strong><?php echo e($detalhesEmVisualizacao['aprovador']); ?></strong></div>
                    <div><span>Aguardando</span><strong><?php echo e($detalhesEmVisualizacao['idade']); ?></strong></div>
                    <div><span>Solicitado em</span><strong><?php echo e($detalhesEmVisualizacao['solicitado_em']); ?></strong></div>
                    <div><span>Respondido em</span><strong><?php echo e($detalhesEmVisualizacao['respondido_em']); ?></strong></div>
                    <div><span>Criado em</span><strong><?php echo e($detalhesEmVisualizacao['criado_em']); ?></strong></div>
                    <div><span>Atualizado em</span><strong><?php echo e($detalhesEmVisualizacao['atualizado_em']); ?></strong></div>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($detalhesEmVisualizacao['risk_probability'] || $detalhesEmVisualizacao['risk_impact'] || $detalhesEmVisualizacao['risk_score']): ?>
                    <div class="ca-risk-strip">
                        <div><span>Probabilidade</span><strong><?php echo e($detalhesEmVisualizacao['risk_probability'] ?? '-'); ?></strong></div>
                        <div><span>Impacto</span><strong><?php echo e($detalhesEmVisualizacao['risk_impact'] ?? '-'); ?></strong></div>
                        <div><span>Score de risco</span><strong><?php echo e($detalhesEmVisualizacao['risk_score'] ?? '-'); ?></strong></div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <div class="ca-decision-checklist">
                    <strong>Checklist antes da decisão</strong>
                    <ul>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $detalhesEmVisualizacao['decisao_checklist']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $check): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <li><?php echo e($check); ?></li>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </ul>
                </div>

                <div class="ca-modal-actions ca-modal-actions-split">
                    <button type="button" class="ghost" wire:click="fecharDetalhesItem" wire:loading.attr="disabled">Fechar</button>
                    <div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($detalhesEmVisualizacao['url'])): ?>
                            <a class="ca-secondary-link" href="<?php echo e($detalhesEmVisualizacao['url']); ?>">Abrir cadastro completo</a>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($detalhesEmVisualizacao['status'] === 'pendente'): ?>
                            <button type="button" class="reject" wire:click="abrirReprovacao(<?php echo e($detalhesEmVisualizacao['id']); ?>)" wire:loading.attr="disabled">Solicitar ajuste</button>
                            <button type="button" class="approve" wire:click="abrirConfirmacaoAprovacao(<?php echo e($detalhesEmVisualizacao['id']); ?>)" wire:loading.attr="disabled">Aprovar com revisão</button>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($aprovacaoEmConfirmacao): ?>
        <div class="ca-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="ca-aprovar-titulo">
            <div class="ca-modal-card ca-modal-card-wide">
                <div class="ca-section-header compact">
                    <div>
                        <span class="ca-kicker">Confirmação obrigatória</span>
                        <h2 id="ca-aprovar-titulo">Aprovar com revisão final?</h2>
                        <p>Antes de confirmar, confira os dados críticos abaixo. A decisão será registrada no histórico e sincronizada com o item de controle.</p>
                    </div>
                </div>

                <div class="ca-modal-warning <?php echo e($aprovacaoEmConfirmacao['tom']); ?>">
                    <strong>Antes de aprovar</strong>
                    <p><?php echo e($aprovacaoEmConfirmacao['decisao_alerta']); ?></p>
                </div>

                <div class="ca-modal-summary ca-modal-summary-strong">
                    <strong><?php echo e($aprovacaoEmConfirmacao['titulo']); ?></strong>
                    <span><?php echo e($aprovacaoEmConfirmacao['empresa']); ?> • <?php echo e($aprovacaoEmConfirmacao['prioridade']); ?> • Vence: <?php echo e($aprovacaoEmConfirmacao['vencimento']); ?></span>
                    <p><?php echo e($aprovacaoEmConfirmacao['descricao_completa']); ?></p>
                </div>

                <div class="ca-decision-grid">
                    <div><span>Responsável</span><strong><?php echo e($aprovacaoEmConfirmacao['responsavel']); ?></strong></div>
                    <div><span>Solicitante</span><strong><?php echo e($aprovacaoEmConfirmacao['solicitante']); ?></strong></div>
                    <div><span>Aguardando</span><strong><?php echo e($aprovacaoEmConfirmacao['idade']); ?></strong></div>
                    <div><span>Status atual</span><strong><?php echo e($aprovacaoEmConfirmacao['status_label']); ?></strong></div>
                </div>

                <div class="ca-decision-checklist">
                    <strong>Checklist de decisão</strong>
                    <ul>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $aprovacaoEmConfirmacao['decisao_checklist']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $check): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <li><?php echo e($check); ?></li>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </ul>
                </div>

                <label class="ca-confirm-check">
                    <input type="checkbox" wire:model.live="aprovacaoRevisada">
                    <span>Confirmei os dados acima e estou ciente de que esta aprovação altera o status do item.</span>
                </label>

                <div class="ca-modal-actions">
                    <button type="button" class="ghost" wire:click="cancelarConfirmacaoAprovacao" wire:loading.attr="disabled">Cancelar</button>
                    <button type="button" class="approve" wire:click="confirmarAprovacao" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="confirmarAprovacao">Confirmar aprovação</span>
                        <span wire:loading wire:target="confirmarAprovacao">Registrando...</span>
                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($reprovacaoEmEdicao): ?>
        <div class="ca-modal-backdrop" role="dialog" aria-modal="true">
            <div class="ca-modal-card">
                <div class="ca-section-header compact">
                    <div>
                        <span class="ca-kicker">Ajuste necessário</span>
                        <h2>Reprovar aprovação</h2>
                        <p>Informe o motivo para orientar a correção e manter histórico claro da decisão.</p>
                    </div>
                </div>

                <div class="ca-modal-warning danger">
                    <strong>Oriente a correção</strong>
                    <p>A reprovação deve explicar exatamente o que precisa ser ajustado para evitar retrabalho e manter a rastreabilidade da decisão.</p>
                </div>

                <div class="ca-modal-summary ca-modal-summary-strong">
                    <strong><?php echo e($reprovacaoEmEdicao['titulo']); ?></strong>
                    <span><?php echo e($reprovacaoEmEdicao['empresa']); ?> • <?php echo e($reprovacaoEmEdicao['prioridade']); ?> • Responsável: <?php echo e($reprovacaoEmEdicao['responsavel']); ?></span>
                    <p><?php echo e($reprovacaoEmEdicao['descricao_completa']); ?></p>
                </div>

                <div class="ca-decision-grid">
                    <div><span>Solicitante</span><strong><?php echo e($reprovacaoEmEdicao['solicitante']); ?></strong></div>
                    <div><span>Aguardando</span><strong><?php echo e($reprovacaoEmEdicao['idade']); ?></strong></div>
                    <div><span>Vencimento</span><strong><?php echo e($reprovacaoEmEdicao['vencimento']); ?></strong></div>
                    <div><span>Status atual</span><strong><?php echo e($reprovacaoEmEdicao['status_label']); ?></strong></div>
                </div>

                <label class="ca-modal-label" for="motivo-reprovacao">Comentário obrigatório</label>
                <textarea id="motivo-reprovacao" wire:model.defer="motivoReprovacao" rows="5" placeholder="Ex.: Documento sem assinatura do responsável ou anexo incorreto. Informe uma orientação clara para correção."></textarea>

                <div class="ca-modal-actions">
                    <button type="button" class="ghost" wire:click="cancelarReprovacao" wire:loading.attr="disabled">Cancelar</button>
                    <button type="button" class="reject" wire:click="reprovarComComentario" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="reprovarComComentario">Confirmar reprovação</span>
                        <span wire:loading wire:target="reprovarComComentario">Registrando...</span>
                    </button>
                </div>
            </div>
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
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views/filament/pages/central-aprovacoes.blade.php ENDPATH**/ ?>