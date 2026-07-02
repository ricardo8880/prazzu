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
        $hub = $hub ?? [];
        $atalhos = $atalhos ?? [];
        $acoesInteligentes = $acoesInteligentes ?? [];
        $acoesRapidasDocumentais = $acoesRapidasDocumentais ?? [];
        $indicadoresPrioridade = $indicadoresPrioridade ?? [];
        $saudeDocumental = $saudeDocumental ?? [];
        $inteligenciaDocumental = $inteligenciaDocumental ?? [];
        $hubTone = $hub['tom'] ?? 'muted';
        $integracaoEnterprise = $integracaoEnterprise ?? [];
        $clusterDocumentos = $clusterDocumentos ?? 'visao-geral';
        $clusterAtivo = $clusterAtivo ?? [];
        $statusResolucaoOptions = $statusResolucaoOptions ?? [];
        $documentosPorCluster = $documentosPorCluster ?? [];
        $documentosClusterAtivo = $documentosPorCluster[$clusterDocumentos] ?? ($documentos ?? []);
        $principalFoco = $inteligenciaDocumental['principalFoco'] ?? null;
    ?>

    <section class="contabilidade-lote3-scope" aria-label="Propósito da aba Documentos">
        <div class="contabilidade-lote3-scope__top">
            <div>
                <span class="contabilidade-lote3-eyebrow"><i class="bi bi-folder2-open"></i> Documentos</span>
                <h2>Central exclusiva para documentos, anexos e irregularidades documentais</h2>
                <p>Todos os vencidos, vencendo, sem arquivo, solicitações de anexo e saúde documental ficam aqui. A Home e Pendências apenas apontam para este módulo quando o problema é documental.</p>
            </div>
            <div class="contabilidade-lote3-actions">
                <a class="contabilidade-lote3-action primary" href="#documentos-prioritarios"><i class="bi bi-folder-check"></i> Tratar documentos</a>
                <a class="contabilidade-lote3-action" href="<?php echo e(\App\Filament\Pages\Pendencias::getUrl()); ?>"><i class="bi bi-list-check"></i> Pendências</a>
            </div>
        </div>
        <div class="contabilidade-lote3-rules">
            <div class="contabilidade-lote3-rule"><strong><i class="bi bi-bullseye"></i> Propósito</strong><span>Gerenciar documentos e corrigir irregularidades documentais.</span></div>
            <div class="contabilidade-lote3-rule"><strong><i class="bi bi-files"></i> Conteúdo dono</strong><span>Vencidos, anexos, pendências documentais e solicitações ficam nesta aba.</span></div>
            <div class="contabilidade-lote3-rule"><strong><i class="bi bi-link-45deg"></i> Integração</strong><span>Se um documento gerar tarefa, a execução vai para Pendências com vínculo.</span></div>
        </div>
    </section>

    <div class="prazzu-page prazzu-docs-page documentos-hub-page documentos-cluster-page">
        <div class="prazzu-hero prazzu-hero-docs documentos-hub-hero">
            <div>
                <span class="prazzu-kicker">DOCUMENTOS</span>
                <h2>Saúde Documental</h2>
                <p><?php echo e($hub['mensagem'] ?? 'Veja se há documentos faltando, vencidos ou próximos do vencimento e saiba por onde começar.'); ?></p>
                <div class="documentos-hub-status <?php echo e($hubTone); ?>">
                    <strong><?php echo e($hub['status'] ?? 'Base documental'); ?></strong>
                    <span><?php echo e($hub['proximaAcao'] ?? 'Ver o que precisa de atenção'); ?></span>
                </div>
            </div>
            <div class="documentos-hub-score-card <?php echo e($hubTone); ?>">
                <span>Saúde documental</span>
                <strong><?php echo e((int) ($hub['score'] ?? 0)); ?>%</strong>
                <small><?php echo e(number_format($hub['pendentes'] ?? 0, 0, ',', '.')); ?> pendente(s) • <?php echo e(number_format($hub['regularizados'] ?? 0, 0, ',', '.')); ?> regularizado(s)</small>
            </div>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($clusterDocumentos === 'visao-geral'): ?>
            <section class="documentos-hub-command documentos-hub-command--metrics" aria-label="Indicadores essenciais de saúde documental">
                <div>
                    <span class="pz-ux-kicker">Resumo</span>
                    <h2>Situação dos documentos</h2>
                    <p>Veja rapidamente se existem documentos vencidos, faltando arquivo ou próximos do vencimento.</p>
                </div>
                <div class="documentos-hub-command-grid">
                    <article>
                        <span>Clientes monitorados</span>
                        <strong><?php echo e(number_format($saudeDocumental['clientesMonitorados'] ?? 0, 0, ',', '.')); ?></strong>
                        <small>Clientes com documentos na base.</small>
                    </article>
                    <article class="danger">
                        <span>Críticos</span>
                        <strong><?php echo e(number_format($hub['criticos'] ?? 0, 0, ',', '.')); ?></strong>
                        <small>Vencidos ou sem arquivo principal.</small>
                    </article>
                    <article class="warning">
                        <span>Vencem em 30 dias</span>
                        <strong><?php echo e(number_format($resumo['vencem30'] ?? 0, 0, ',', '.')); ?></strong>
                        <small>Prazos que exigem acompanhamento.</small>
                    </article>
                    <article>
                        <span>Com arquivo</span>
                        <strong><?php echo e((int) ($hub['comArquivoPercentual'] ?? 0)); ?>%</strong>
                        <small>Documentos com arquivo anexado.</small>
                    </article>
                </div>
            </section>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($principalFoco): ?>
                <section class="documentos-enterprise-sync documentos-enterprise-sync--compact" aria-label="Próxima ação documental">
                    <div class="documentos-enterprise-sync__content">
                        <span class="pz-ux-kicker">Próxima ação</span>
                        <h2><?php echo e($principalFoco['titulo']); ?></h2>
                        <p><?php echo e($principalFoco['descricao']); ?></p>
                        <a href="<?php echo e($principalFoco['url']); ?>"><?php echo e($principalFoco['botao']); ?></a>
                    </div>
                    <div class="documentos-enterprise-sync__flows">
                        <div class="documentos-enterprise-flow <?php echo e($inteligenciaDocumental['tom'] ?? 'primary'); ?>">
                            <span>Risco documental</span>
                            <strong><?php echo e((int) ($inteligenciaDocumental['scoreRisco'] ?? 0)); ?>%</strong>
                            <small><?php echo e($inteligenciaDocumental['recomendacao'] ?? 'Manter acompanhamento documental.'); ?></small>
                        </div>
                    </div>
                </section>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <section class="documentos-cluster-list" aria-label="Clientes com atenção documental">
                <div class="documentos-cluster-list__header">
                    <div>
                        <span class="pz-ux-kicker">Clientes com pendência</span>
                        <h3>Clientes que precisam de revisão</h3>
                        <p>Priorize os clientes com documentos vencidos, sem arquivo ou próximos do vencimento.</p>
                    </div>
                    <strong><?php echo e(number_format($saudeDocumental['clientesComProblema'] ?? 0, 0, ',', '.')); ?> cliente(s)</strong>
                </div>
                <div class="documentos-cluster-card-grid">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($saudeDocumental['principaisClientes'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cliente): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <article class="documentos-cluster-card <?php echo e($cliente['tom'] ?? 'success'); ?>">
                            <div>
                                <span class="documentos-priority-badge <?php echo e($cliente['tom'] ?? 'success'); ?>"><?php echo e((int) ($cliente['score'] ?? 0)); ?>% saudável</span>
                                <h4><?php echo e($cliente['nome']); ?></h4>
                                <p><?php echo e($cliente['motivo']); ?></p>
                            </div>
                            <dl>
                                <div><dt>Total</dt><dd><?php echo e(number_format($cliente['total'] ?? 0, 0, ',', '.')); ?></dd></div>
                                <div><dt>Problemas</dt><dd><?php echo e(number_format($cliente['problemas'] ?? 0, 0, ',', '.')); ?></dd></div>
                                <div><dt>Críticos</dt><dd><?php echo e(number_format($cliente['criticos'] ?? 0, 0, ',', '.')); ?></dd></div>
                            </dl>
                            <div class="documentos-cluster-card__actions">
                                <a href="<?php echo e($cliente['url']); ?>">Ver documentos do cliente</a>
                            </div>
                        </article>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="documentos-cluster-empty"><strong>Nenhum cliente no radar documental.</strong><span>Quando houver vencidos, sem arquivo ou prazos próximos, eles aparecerão aqui.</span></div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </section>
        <?php elseif($clusterDocumentos === 'pendencias'): ?>
            <section id="documentos-prioritarios" class="documentos-priority-panel" aria-label="Irregularidades documentais">
                <div class="documentos-priority-panel__intro">
                    <span class="pz-ux-kicker">Irregularidades</span>
                    <h2>Documentos que comprometem a saúde da base</h2>
                    <p>Recorte curto de vencidos, sem arquivo e itens críticos.</p>
                </div>
                <div class="documentos-priority-grid">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = array_slice($indicadoresPrioridade, 0, 3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $indicador): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <article class="documentos-priority-card <?php echo e($indicador['tom'] ?? 'primary'); ?>">
                            <span><?php echo e($indicador['label']); ?></span>
                            <strong><?php echo e(number_format($indicador['total'] ?? 0, 0, ',', '.')); ?></strong>
                            <small><?php echo e($indicador['descricao']); ?></small>
                        </article>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            </section>

            <section class="documentos-cluster-list" aria-label="Documentos com irregularidade">
                <div class="documentos-cluster-list__header">
                    <div>
                        <span class="pz-ux-kicker">Lista curta</span>
                        <h3>Irregularidades documentais</h3>
                        <p>Itens que precisam de correção documental.</p>
                    </div>
                    <strong><?php echo e(number_format(count($documentosPorCluster['pendencias'] ?? []), 0, ',', '.')); ?> item(ns)</strong>
                </div>
                <div class="documentos-cluster-card-grid">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($documentosPorCluster['pendencias'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $documento): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php
                            $empresa = $documento['nome_fantasia'] ?: ($documento['razao_social'] ?: '-');
                            $prioridadeOperacional = $documento['prioridade_operacional'] ?? ['label' => 'Estável', 'motivo' => 'Sem sinal crítico.', 'tom' => 'success', 'prazo' => '-'];
                            $acaoRapida = $documento['acao_rapida'] ?? null;
                        ?>
                        <article class="documentos-cluster-card <?php echo e($prioridadeOperacional['tom'] ?? 'success'); ?>">
                            <div>
                                <span class="documentos-priority-badge <?php echo e($prioridadeOperacional['tom'] ?? 'success'); ?>"><?php echo e($prioridadeOperacional['label'] ?? 'Estável'); ?></span>
                                <h4><?php echo e($documento['titulo']); ?></h4>
                                <p><?php echo e($prioridadeOperacional['motivo'] ?? 'Sem sinal crítico.'); ?></p>
                            </div>
                            <dl>
                                <div><dt>Empresa</dt><dd><?php echo e($empresa); ?></dd></div>
                                <div><dt>Prazo</dt><dd><?php echo e($prioridadeOperacional['prazo'] ?? '-'); ?></dd></div>
                                <div><dt>Arquivo</dt><dd><?php echo e(! empty($documento['arquivo']) ? 'Com arquivo' : 'Sem arquivo'); ?></dd></div>
                            </dl>
                            <div class="documentos-cluster-card__actions">
                                <?php echo $__env->make('filament.pages.partials.documento-resolver-modal', ['documento' => $documento, 'empresa' => $empresa, 'prioridadeOperacional' => $prioridadeOperacional, 'statusResolucaoOptions' => $statusResolucaoOptions], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            </div>
                        </article>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="documentos-cluster-empty"><strong>Nenhuma irregularidade documental.</strong><span>Documentos críticos aparecerão aqui quando existirem.</span></div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </section>
        <?php elseif($clusterDocumentos === 'vencimentos'): ?>
            <section class="documentos-cluster-list" aria-label="Vencimentos documentais">
                <div class="documentos-cluster-list__header">
                    <div>
                        <span class="pz-ux-kicker">Prazos</span>
                        <h3>Vencimentos no radar</h3>
                        <p>Documentos que merecem acompanhamento antes de vencer.</p>
                    </div>
                    <strong><?php echo e(number_format(count($documentosPorCluster['vencimentos'] ?? []), 0, ',', '.')); ?> item(ns)</strong>
                </div>
                <div class="documentos-cluster-card-grid documentos-cluster-card-grid--timeline">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($documentosPorCluster['vencimentos'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $documento): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php
                            $empresa = $documento['nome_fantasia'] ?: ($documento['razao_social'] ?: '-');
                            $prioridadeOperacional = $documento['prioridade_operacional'] ?? ['label' => 'Estável', 'motivo' => 'Sem sinal crítico.', 'tom' => 'success', 'prazo' => '-'];
                            $acaoRapida = $documento['acao_rapida'] ?? null;
                        ?>
                        <article class="documentos-cluster-card <?php echo e($prioridadeOperacional['tom'] ?? 'success'); ?>">
                            <div>
                                <span class="documentos-priority-badge <?php echo e($prioridadeOperacional['tom'] ?? 'success'); ?>"><?php echo e($prioridadeOperacional['prazo'] ?? '-'); ?></span>
                                <h4><?php echo e($documento['titulo']); ?></h4>
                                <p><?php echo e(! empty($documento['data_vencimento']) ? 'Vencimento em ' . \Carbon\Carbon::parse($documento['data_vencimento'])->format('d/m/Y') : 'Sem vencimento cadastrado.'); ?></p>
                            </div>
                            <dl>
                                <div><dt>Empresa</dt><dd><?php echo e($empresa); ?></dd></div>
                                <div><dt>Status</dt><dd><?php echo e(ucfirst(str_replace('_', ' ', $documento['status'] ?? '-'))); ?></dd></div>
                                <div><dt>Prioridade</dt><dd><?php echo e($prioridadeOperacional['label'] ?? 'Estável'); ?></dd></div>
                            </dl>
                            <div class="documentos-cluster-card__actions">
                                <?php echo $__env->make('filament.pages.partials.documento-resolver-modal', ['documento' => $documento, 'empresa' => $empresa, 'prioridadeOperacional' => $prioridadeOperacional, 'statusResolucaoOptions' => $statusResolucaoOptions], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            </div>
                        </article>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="documentos-cluster-empty"><strong>Nenhum vencimento no radar.</strong><span>Quando houver documentos com vencimento, eles aparecerão aqui por prazo.</span></div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </section>
        <?php elseif($clusterDocumentos === 'enterprise'): ?>
            <section class="documentos-enterprise-sync" aria-label="Integração com Gestão Documental Enterprise">
                <div class="documentos-enterprise-sync__content">
                    <span class="pz-ux-kicker">Detalhes</span>
                    <h2>Ver documentos em detalhe</h2>
                    <p><?php echo e($integracaoEnterprise['descricao'] ?? 'Abra a visão detalhada quando precisar filtrar, revisar ou corrigir documentos específicos.'); ?></p>
                    <a href="<?php echo e($integracaoEnterprise['url'] ?? \App\Filament\Pages\GestaoDocumentalEnterprise::getUrl()); ?>">Abrir detalhes dos documentos</a>
                </div>
                <div class="documentos-enterprise-sync__flows">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($integracaoEnterprise['fluxos'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fluxo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <a class="documentos-enterprise-flow <?php echo e($fluxo['tom'] ?? 'primary'); ?>" href="<?php echo e($fluxo['url']); ?>">
                            <span><?php echo e($fluxo['titulo']); ?></span>
                            <strong><?php echo e(number_format($fluxo['total'] ?? 0, 0, ',', '.')); ?></strong>
                            <small><?php echo e($fluxo['descricao']); ?></small>
                        </a>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            </section>
        <?php else: ?>
            <div id="fila-documentos" class="prazzu-card documentos-cluster-table-card">
                <div class="prazzu-card-header">
                    <div><h3>Documentos para revisar</h3><p>Lista objetiva para consultar documentos e corrigir pendências.</p></div>
                </div>
                <div class="prazzu-table-wrap">
                    <table class="prazzu-table prazzu-click-table documentos-premium-table">
                        <thead><tr><th>Documento</th><th>Prioridade</th><th>Empresa</th><th>Status</th><th>Vencimento</th><th>Arquivo</th><th>Portal</th><th>Ações</th></tr></thead>
                        <tbody>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($documentosPorCluster['fila'] ?? $documentosClusterAtivo); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $documento): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <?php
                                    $vencido = ! empty($documento['data_vencimento']) && \Carbon\Carbon::parse($documento['data_vencimento'])->isPast() && ! in_array($documento['status'] ?? '', ['concluido', 'concluído', 'finalizado'], true);
                                    $empresa = $documento['nome_fantasia'] ?: ($documento['razao_social'] ?: '-');
                                    $prioridadeOperacional = $documento['prioridade_operacional'] ?? ['label' => 'Estável', 'motivo' => 'Sem sinal crítico.', 'tom' => 'success', 'prazo' => '-'];
                                    $acaoRapida = $documento['acao_rapida'] ?? null;
                                ?>
                                <tr class="documentos-priority-row <?php echo e($prioridadeOperacional['tom'] ?? 'success'); ?>">
                                    <td><strong><?php echo e($documento['titulo']); ?></strong><small><?php echo e(\Illuminate\Support\Str::limit($documento['descricao'] ?? 'Sem descrição cadastrada', 60)); ?></small></td>
                                    <td><span class="documentos-priority-badge <?php echo e($prioridadeOperacional['tom'] ?? 'success'); ?>"><?php echo e($prioridadeOperacional['label'] ?? 'Estável'); ?></span></td>
                                    <td><?php echo e($empresa); ?></td>
                                    <td><span class="prazzu-badge <?php echo e($vencido ? 'danger' : ''); ?>"><?php echo e(ucfirst(str_replace('_', ' ', $documento['status'] ?? '-'))); ?></span></td>
                                    <td><span class="<?php echo e($vencido ? 'prazzu-date-danger' : ''); ?>"><?php echo e(! empty($documento['data_vencimento']) ? \Carbon\Carbon::parse($documento['data_vencimento'])->format('d/m/Y') : '-'); ?></span></td>
                                    <td><span class="prazzu-pill <?php echo e(! empty($documento['arquivo']) ? 'ok' : 'muted'); ?>"><?php echo e(! empty($documento['arquivo']) ? 'Com arquivo' : 'Sem arquivo'); ?></span></td>
                                    <td><span class="prazzu-pill <?php echo e(! empty($documento['portal_ativo']) ? 'ok' : 'muted'); ?>"><?php echo e(! empty($documento['portal_ativo']) ? 'Ativo' : 'Inativo'); ?></span></td>
                                    <td class="documentos-row-actions documentos-row-actions--filament">
                                        <?php echo $__env->make('filament.pages.partials.documento-resolver-modal', ['documento' => $documento, 'empresa' => $empresa, 'prioridadeOperacional' => $prioridadeOperacional, 'statusResolucaoOptions' => $statusResolucaoOptions], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                    </td>
                                </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <tr><td colspan="8" class="prazzu-empty"><strong>Nenhum documento encontrado.</strong><br>Cadastre o primeiro documento para começar a medir a saúde documental.</td></tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($documentoResolucaoEmEdicao): ?>
            <?php echo $__env->make('filament.pages.partials.documento-resolver-dialog', [
                'documento' => $documentoResolucaoEmEdicao,
                'statusResolucaoOptions' => $statusResolucaoOptions,
            ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views\filament\pages\documentos.blade.php ENDPATH**/ ?>