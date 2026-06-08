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

    <link rel="stylesheet" href="<?php echo e(asset('css/prazzu-fase2-pages.css')); ?>?v=<?php echo e(filemtime(public_path('css/prazzu-fase2-pages.css'))); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/prazzu-ux-essentials.css')); ?>?v=<?php echo e(file_exists(public_path('css/prazzu-ux-essentials.css')) ? filemtime(public_path('css/prazzu-ux-essentials.css')) : time()); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('css/documentos-hub.css')); ?>?v=<?php echo e(file_exists(public_path('css/documentos-hub.css')) ? filemtime(public_path('css/documentos-hub.css')) : time()); ?>">

    <?php
        $hub = $hub ?? [];
        $atalhos = $atalhos ?? [];
        $acoesInteligentes = $acoesInteligentes ?? [];
        $indicadoresPrioridade = $indicadoresPrioridade ?? [];
        $hubTone = $hub['tom'] ?? 'muted';
        $integracaoEnterprise = $integracaoEnterprise ?? [];
        $clusterDocumentos = $clusterDocumentos ?? 'visao-geral';
        $clusterAtivo = $clusterAtivo ?? [];
        $clustersDocumentos = $clustersDocumentos ?? [];
        $statusResolucaoOptions = $statusResolucaoOptions ?? [];
        $documentosPorCluster = $documentosPorCluster ?? [];
        $documentosClusterAtivo = $documentosPorCluster[$clusterDocumentos] ?? ($documentos ?? []);
        $prioridadeInteligente = $prioridadeInteligente ?? [];
        $prioridadeDocumento = $prioridadeInteligente['documento'] ?? null;
        $fluxoContinuo = $fluxoContinuo ?? [];
        $fluxoProximo = $fluxoContinuo['proximo'] ?? null;
        $fluxoFeedback = $fluxoContinuo['feedback'] ?? null;
    ?>

    <div class="prazzu-page prazzu-docs-page documentos-hub-page documentos-cluster-page">
        <div class="prazzu-hero prazzu-hero-docs documentos-hub-hero">
            <div>
                <span class="prazzu-kicker">DOCUMENTOS</span>
                <h2>Hub de Documentos</h2>
                <p><?php echo e($hub['mensagem'] ?? 'Organize arquivos, vencimentos, liberação para cliente e pendências em uma visão mais operacional.'); ?></p>
                <div class="documentos-hub-status <?php echo e($hubTone); ?>">
                    <strong><?php echo e($hub['status'] ?? 'Base documental'); ?></strong>
                    <span>Próxima ação: <?php echo e($hub['proximaAcao'] ?? 'Revisar fila documental'); ?></span>
                </div>
            </div>
            <div class="documentos-hub-score-card <?php echo e($hubTone); ?>">
                <span>Saúde documental</span>
                <strong><?php echo e((int) ($hub['score'] ?? 0)); ?>%</strong>
                <small><?php echo e(number_format($hub['pendentes'] ?? 0, 0, ',', '.')); ?> pendente(s) • <?php echo e(number_format($hub['regularizados'] ?? 0, 0, ',', '.')); ?> regularizado(s)</small>
            </div>
        </div>


        <section class="documentos-prioridade-inteligente <?php echo e($prioridadeInteligente['tom'] ?? 'success'); ?>" aria-label="Prioridade inteligente de documentos">
            <div class="documentos-prioridade-inteligente__content">
                <span class="pz-ux-kicker">Prioridade inteligente</span>
                <h2><?php echo e($prioridadeInteligente['titulo'] ?? 'Base documental sob controle'); ?></h2>
                <p><?php echo e($prioridadeInteligente['mensagem'] ?? 'Nenhum documento crítico foi identificado na fila atual.'); ?></p>
                <div class="documentos-prioridade-inteligente__meta">
                    <span><strong><?php echo e(number_format($prioridadeInteligente['criticos'] ?? 0, 0, ',', '.')); ?></strong> crítico(s)</span>
                    <span><strong><?php echo e(number_format($prioridadeInteligente['altos'] ?? 0, 0, ',', '.')); ?></strong> alta prioridade</span>
                    <span><strong><?php echo e(number_format($prioridadeInteligente['monitorar'] ?? 0, 0, ',', '.')); ?></strong> em monitoramento</span>
                </div>
            </div>
            <div class="documentos-prioridade-inteligente__action">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($prioridadeDocumento)): ?>
                    <strong><?php echo e(\Illuminate\Support\Str::limit($prioridadeDocumento['titulo'] ?? 'Documento prioritário', 56)); ?></strong>
                    <small><?php echo e($prioridadeDocumento['prioridade_operacional']['prazo'] ?? 'Prioridade calculada pela fila'); ?></small>
                    <?php if (isset($component)) { $__componentOriginal6330f08526bbb3ce2a0da37da512a11f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['type' => 'button','color' => 'danger','icon' => 'heroicon-m-bolt','wire:click' => 'abrirResolucaoDocumento('.e($prioridadeDocumento['id']).')','wire:loading.attr' => 'disabled','wire:target' => 'abrirResolucaoDocumento('.e($prioridadeDocumento['id']).')']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'button','color' => 'danger','icon' => 'heroicon-m-bolt','wire:click' => 'abrirResolucaoDocumento('.e($prioridadeDocumento['id']).')','wire:loading.attr' => 'disabled','wire:target' => 'abrirResolucaoDocumento('.e($prioridadeDocumento['id']).')']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                        <span wire:loading.remove wire:target="abrirResolucaoDocumento(<?php echo e($prioridadeDocumento['id']); ?>)"><?php echo e($prioridadeInteligente['acao'] ?? 'Resolver agora'); ?></span>
                        <span wire:loading wire:target="abrirResolucaoDocumento(<?php echo e($prioridadeDocumento['id']); ?>)">Abrindo...</span>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6330f08526bbb3ce2a0da37da512a11f)): ?>
<?php $attributes = $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f; ?>
<?php unset($__attributesOriginal6330f08526bbb3ce2a0da37da512a11f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6330f08526bbb3ce2a0da37da512a11f)): ?>
<?php $component = $__componentOriginal6330f08526bbb3ce2a0da37da512a11f; ?>
<?php unset($__componentOriginal6330f08526bbb3ce2a0da37da512a11f); ?>
<?php endif; ?>
                    <a href="<?php echo e($prioridadeInteligente['clusterUrl'] ?? '#fila-documentos'); ?>">Ver fila relacionada</a>
                <?php else: ?>
                    <strong>Nenhuma urgência ativa</strong>
                    <small>Continue usando os clusters para acompanhar vencimentos e novos cadastros.</small>
                    <a href="<?php echo e($prioridadeInteligente['clusterUrl'] ?? '#fila-documentos'); ?>">Abrir fila de documentos</a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </section>


        <section class="documentos-fluxo-continuo <?php echo e(! empty($fluxoContinuo['ativo']) ? 'ativo' : 'concluido'); ?>" aria-label="Fluxo contínuo de resolução documental">
            <div class="documentos-fluxo-continuo__content">
                <span class="pz-ux-kicker">Fluxo contínuo</span>
                <h2><?php echo e(! empty($fluxoContinuo['ativo']) ? 'Modo produtividade pronto para uso' : 'Fila prioritária sob controle'); ?></h2>
                <p><?php echo e($fluxoContinuo['mensagem'] ?? 'Resolva documentos sem sair da página e mantenha o foco no próximo item prioritário.'); ?></p>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($fluxoFeedback)): ?>
                    <div class="documentos-fluxo-continuo__feedback <?php echo e($fluxoFeedback['tipo'] ?? 'info'); ?>">
                        <strong><?php echo e($fluxoFeedback['titulo'] ?? 'Atualização concluída'); ?></strong>
                        <span><?php echo e($fluxoFeedback['mensagem'] ?? 'A fila foi atualizada com sucesso.'); ?></span>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div class="documentos-fluxo-continuo__panel">
                <span><?php echo e(number_format($fluxoContinuo['total'] ?? 0, 0, ',', '.')); ?> item(ns) prioritário(s)</span>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($fluxoProximo)): ?>
                    <strong><?php echo e(\Illuminate\Support\Str::limit($fluxoProximo['titulo'] ?? 'Próximo documento', 48)); ?></strong>
                    <small><?php echo e($fluxoProximo['prioridade_operacional']['motivo'] ?? 'Próximo item calculado por prioridade.'); ?></small>
                    <?php if (isset($component)) { $__componentOriginal6330f08526bbb3ce2a0da37da512a11f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.button.index','data' => ['type' => 'button','color' => 'warning','icon' => 'heroicon-m-forward','wire:click' => 'abrirResolucaoDocumento('.e($fluxoProximo['id']).')','wire:loading.attr' => 'disabled','wire:target' => 'abrirResolucaoDocumento('.e($fluxoProximo['id']).')']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::button'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['type' => 'button','color' => 'warning','icon' => 'heroicon-m-forward','wire:click' => 'abrirResolucaoDocumento('.e($fluxoProximo['id']).')','wire:loading.attr' => 'disabled','wire:target' => 'abrirResolucaoDocumento('.e($fluxoProximo['id']).')']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                        <span wire:loading.remove wire:target="abrirResolucaoDocumento(<?php echo e($fluxoProximo['id']); ?>)">Resolver próximo</span>
                        <span wire:loading wire:target="abrirResolucaoDocumento(<?php echo e($fluxoProximo['id']); ?>)">Abrindo...</span>
                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6330f08526bbb3ce2a0da37da512a11f)): ?>
<?php $attributes = $__attributesOriginal6330f08526bbb3ce2a0da37da512a11f; ?>
<?php unset($__attributesOriginal6330f08526bbb3ce2a0da37da512a11f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6330f08526bbb3ce2a0da37da512a11f)): ?>
<?php $component = $__componentOriginal6330f08526bbb3ce2a0da37da512a11f; ?>
<?php unset($__componentOriginal6330f08526bbb3ce2a0da37da512a11f); ?>
<?php endif; ?>
                <?php else: ?>
                    <strong>Nenhum próximo item crítico</strong>
                    <small>Continue acompanhando os clusters para novos vencimentos e pendências.</small>
                    <a href="<?php echo e($integracaoEnterprise['url'] ?? \App\Filament\Pages\GestaoDocumentalEnterprise::getUrl()); ?>">Abrir Enterprise</a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </section>

        <section class="documentos-cluster-context" aria-label="Resumo do cluster ativo de documentos">
            <div class="documentos-cluster-context__header">
                <div>
                    <span class="pz-ux-kicker">Cluster ativo</span>
                    <h2><?php echo e($clusterAtivo['label'] ?? 'Visão Geral'); ?></h2>
                    <p><?php echo e($clusterAtivo['description'] ?? 'Use as abas superiores do Filament para alternar o contexto sem sair da página.'); ?></p>
                </div>
                <div class="documentos-cluster-context__result <?php echo e($clusterAtivo['tone'] ?? 'primary'); ?>">
                    <strong><?php echo e(number_format($clusterAtivo['count'] ?? 0, 0, ',', '.')); ?></strong>
                    <span><?php echo e($clusterAtivo['hint'] ?? 'itens no contexto'); ?></span>
                </div>
            </div>
            <div class="documentos-cluster-insights" aria-label="Indicadores rápidos do cluster ativo">
                <span class="<?php echo e($hubTone); ?>"><strong><?php echo e((int) ($hub['score'] ?? 0)); ?>%</strong> saúde documental</span>
                <span class="danger"><strong><?php echo e(number_format($hub['criticos'] ?? 0, 0, ',', '.')); ?></strong> críticos</span>
                <span class="warning"><strong><?php echo e(number_format($resumo['vencem30'] ?? 0, 0, ',', '.')); ?></strong> vencem em 30 dias</span>
                <span class="neutral"><strong>Ação</strong> <?php echo e($clusterAtivo['next_action'] ?? 'Revisar fila documental'); ?></span>
            </div>
        </section>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($clusterDocumentos === 'visao-geral'): ?>
            <section class="documentos-ux-guide" aria-label="Guia rápido da rotina documental">
                <article>
                    <span>1</span>
                    <div>
                        <strong>Entenda a situação</strong>
                        <small>Use a saúde documental e os indicadores para saber se existe risco imediato.</small>
                    </div>
                </article>
                <article>
                    <span>2</span>
                    <div>
                        <strong>Priorize o que importa</strong>
                        <small>Resolva primeiro vencidos, sem arquivo e prazos próximos.</small>
                    </div>
                </article>
                <article>
                    <span>3</span>
                    <div>
                        <strong>Execute na tela certa</strong>
                        <small>Avance para Enterprise, Validades, Contratos ou Pendências sem procurar no menu.</small>
                    </div>
                </article>
            </section>

            <section class="documentos-hub-actions" aria-label="Atalhos principais de documentos">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $atalhos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $atalho): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <a class="documentos-hub-action <?php echo e($atalho['tom'] ?? 'neutral'); ?>" href="<?php echo e($atalho['url']); ?>">
                        <strong><?php echo e($atalho['label']); ?></strong>
                        <span><?php echo e($atalho['descricao']); ?></span>
                    </a>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </section>

            <section class="documentos-hub-command documentos-hub-command--actions">
                <div>
                    <span class="pz-ux-kicker">Comando rápido</span>
                    <h2>O que merece atenção agora</h2>
                    <p>As ações abaixo mudam conforme os dados reais: vencidos, itens sem arquivo, prazos próximos e gestão completa.</p>
                </div>
                <div class="documentos-smart-actions" aria-label="Ações inteligentes de documentos">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $acoesInteligentes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $acao): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <article class="documentos-smart-action <?php echo e($acao['tom'] ?? 'primary'); ?>">
                            <span><?php echo e($acao['prioridade']); ?></span>
                            <strong><?php echo e($acao['titulo']); ?></strong>
                            <p><?php echo e($acao['descricao']); ?></p>
                            <a href="<?php echo e($acao['url']); ?>"><?php echo e($acao['botao']); ?></a>
                        </article>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            </section>

            <section class="documentos-hub-command documentos-hub-command--metrics">
                <div>
                    <span class="pz-ux-kicker">Leitura rápida</span>
                    <h2>Indicadores para decidir sem procurar informação</h2>
                    <p>Use estes sinais para saber se o problema está em prazo, arquivo ou liberação no portal.</p>
                </div>
                <div class="documentos-hub-command-grid">
                    <article>
                        <span>Críticos</span>
                        <strong><?php echo e(number_format($hub['criticos'] ?? 0, 0, ',', '.')); ?></strong>
                        <small>Vencidos ou sem arquivo principal.</small>
                    </article>
                    <article>
                        <span>Arquivados</span>
                        <strong><?php echo e((int) ($hub['comArquivoPercentual'] ?? 0)); ?>%</strong>
                        <small>Itens com arquivo anexado.</small>
                    </article>
                    <article>
                        <span>Portal</span>
                        <strong><?php echo e((int) ($hub['portalPercentual'] ?? 0)); ?>%</strong>
                        <small>Itens liberados para consulta externa.</small>
                    </article>
                </div>
            </section>
        <?php elseif($clusterDocumentos === 'pendencias'): ?>
            <section class="documentos-priority-panel" aria-label="Priorização operacional de documentos">
                <div class="documentos-priority-panel__intro">
                    <span class="pz-ux-kicker">Priorização</span>
                    <h2>Fila ordenada por criticidade real</h2>
                    <p>O hub destaca primeiro o que pode gerar risco: vencidos, itens sem arquivo, prazos curtos e prioridades altas.</p>
                </div>
                <div class="documentos-priority-grid">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $indicadoresPrioridade; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $indicador): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <article class="documentos-priority-card <?php echo e($indicador['tom'] ?? 'primary'); ?>">
                            <span><?php echo e($indicador['label']); ?></span>
                            <strong><?php echo e(number_format($indicador['total'] ?? 0, 0, ',', '.')); ?></strong>
                            <small><?php echo e($indicador['descricao']); ?></small>
                        </article>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            </section>

            <div class="prazzu-stats-grid">
                <div class="prazzu-stat-card"><span>Total de itens</span><strong><?php echo e(number_format($resumo['total'] ?? 0, 0, ',', '.')); ?></strong><small>Documentos cadastrados</small></div>
                <div class="prazzu-stat-card success"><span>Com arquivo</span><strong><?php echo e(number_format($resumo['comArquivo'] ?? 0, 0, ',', '.')); ?></strong><small>Prontos para consulta</small></div>
                <div class="prazzu-stat-card warning"><span>Vencem em 30 dias</span><strong><?php echo e(number_format($resumo['vencem30'] ?? 0, 0, ',', '.')); ?></strong><small>Exigem acompanhamento</small></div>
                <div class="prazzu-stat-card danger"><span>Vencidos</span><strong><?php echo e(number_format($resumo['vencidos'] ?? 0, 0, ',', '.')); ?></strong><small>Regularização necessária</small></div>
            </div>

            <div class="prazzu-work-grid">
                <div class="prazzu-card">
                    <div class="prazzu-card-header">
                        <div><h3>Fila inteligente</h3><p>Priorize documentos sem arquivo, vencidos e liberados no portal.</p></div>
                    </div>
                    <div class="prazzu-mini-grid">
                        <div class="prazzu-mini-card"><span>Sem arquivo</span><strong><?php echo e(number_format($resumo['semArquivo'] ?? 0, 0, ',', '.')); ?></strong><p>Itens que precisam de anexo.</p></div>
                        <div class="prazzu-mini-card"><span>No portal</span><strong><?php echo e(number_format($resumo['portal'] ?? 0, 0, ',', '.')); ?></strong><p>Liberados para cliente.</p></div>
                    </div>
                </div>
                <div class="prazzu-card">
                    <div class="prazzu-card-header compact"><div><h3>Ações úteis</h3><p>Atalhos para resolver sem sair procurando telas no menu lateral.</p></div></div>
                    <div class="prazzu-actions-list documentos-secondary-actions">
                        <a href="<?php echo e(\App\Filament\Resources\ItemControles\ItemControleResource::getUrl('create')); ?>">Cadastrar novo documento</a>
                        <a href="<?php echo e(\App\Filament\Resources\ItemControles\ItemControleResource::getUrl('index')); ?>">Abrir lista completa</a>
                        <a href="<?php echo e($integracaoEnterprise['url'] ?? \App\Filament\Pages\GestaoDocumentalEnterprise::getUrl()); ?>">Gestão documental Enterprise</a>
                        <a href="<?php echo e($integracaoEnterprise['fluxos'][2]['url'] ?? \App\Filament\Pages\Validades::getUrl()); ?>">Controlar validades</a>
                        <a href="<?php echo e(\App\Filament\Pages\Contratos::getUrl()); ?>">Acompanhar contratos</a>
                        <a href="<?php echo e(\App\Filament\Pages\Pendencias::getUrl()); ?>">Resolver pendências</a>
                    </div>
                </div>
            </div>


            <section class="documentos-cluster-list" aria-label="Documentos pendentes para resolução rápida">
                <div class="documentos-cluster-list__header">
                    <div>
                        <span class="pz-ux-kicker">Resolver sem sair da página</span>
                        <h3>Pendências operacionais</h3>
                        <p>Mostra apenas os itens críticos e de alta prioridade para o usuário focar no que precisa de ação.</p>
                    </div>
                    <strong><?php echo e(number_format(count($documentosPorCluster['pendencias'] ?? []), 0, ',', '.')); ?> item(ns)</strong>
                </div>
                <div class="documentos-cluster-card-grid">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($documentosPorCluster['pendencias'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $documento): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php
                            $empresa = $documento['nome_fantasia'] ?: ($documento['razao_social'] ?: '-');
                            $prioridadeOperacional = $documento['prioridade_operacional'] ?? ['label' => 'Estável', 'motivo' => 'Sem sinal crítico.', 'tom' => 'success', 'prazo' => '-'];
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
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($documento['arquivo_url'])): ?>
                                    <a href="<?php echo e($documento['arquivo_url']); ?>" target="_blank" rel="noopener noreferrer">Arquivo</a>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php echo $__env->make('filament.pages.partials.documento-resolver-modal', ['documento' => $documento, 'empresa' => $empresa, 'prioridadeOperacional' => $prioridadeOperacional, 'statusResolucaoOptions' => $statusResolucaoOptions], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            </div>
                        </article>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="documentos-cluster-empty"><strong>Nenhuma pendência crítica no momento.</strong><span>Quando houver itens vencidos, sem arquivo ou com prioridade alta, eles aparecerão aqui.</span></div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </section>

        <?php elseif($clusterDocumentos === 'vencimentos'): ?>
            <section class="documentos-enterprise-sync documentos-enterprise-sync--compact" aria-label="Vencimentos e prazos documentais">
                <div class="documentos-enterprise-sync__content">
                    <span class="pz-ux-kicker">Vencimentos</span>
                    <h2>Prazos críticos sem poluir a página inteira</h2>
                    <p>Este cluster concentra vencidos e próximos prazos. Para operar com filtros avançados, avance para a Enterprise já no fluxo correto.</p>
                    <a href="<?php echo e($integracaoEnterprise['fluxos'][0]['url'] ?? ($integracaoEnterprise['url'] ?? \App\Filament\Pages\GestaoDocumentalEnterprise::getUrl())); ?>">Abrir vencidos na Enterprise</a>
                </div>
                <div class="documentos-enterprise-sync__flows">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($integracaoEnterprise['fluxos'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fluxo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array($fluxo['tom'] ?? '', ['danger', 'warning'], true) || str_contains(strtolower($fluxo['titulo'] ?? ''), 'venc')): ?>
                            <a class="documentos-enterprise-flow <?php echo e($fluxo['tom'] ?? 'primary'); ?>" href="<?php echo e($fluxo['url']); ?>">
                                <span><?php echo e($fluxo['titulo']); ?></span>
                                <strong><?php echo e(number_format($fluxo['total'] ?? 0, 0, ',', '.')); ?></strong>
                                <small><?php echo e($fluxo['descricao']); ?></small>
                            </a>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            </section>

            <div class="prazzu-stats-grid">
                <div class="prazzu-stat-card danger"><span>Vencidos</span><strong><?php echo e(number_format($resumo['vencidos'] ?? 0, 0, ',', '.')); ?></strong><small>Regularização necessária</small></div>
                <div class="prazzu-stat-card warning"><span>Vencem em 30 dias</span><strong><?php echo e(number_format($resumo['vencem30'] ?? 0, 0, ',', '.')); ?></strong><small>Exigem acompanhamento</small></div>
                <div class="prazzu-stat-card"><span>Total monitorado</span><strong><?php echo e(number_format(($resumo['vencidos'] ?? 0) + ($resumo['vencem30'] ?? 0), 0, ',', '.')); ?></strong><small>Itens no radar de prazo</small></div>
                <div class="prazzu-stat-card success"><span>Com arquivo</span><strong><?php echo e(number_format($resumo['comArquivo'] ?? 0, 0, ',', '.')); ?></strong><small>Itens com evidência anexada</small></div>
            </div>


            <section class="documentos-cluster-list" aria-label="Documentos ordenados por vencimento">
                <div class="documentos-cluster-list__header">
                    <div>
                        <span class="pz-ux-kicker">Prazos em ordem</span>
                        <h3>Vencimentos que precisam de acompanhamento</h3>
                        <p>Lista curta com os prazos mais relevantes para evitar que a tela vire uma rolagem longa.</p>
                    </div>
                    <strong><?php echo e(number_format(count($documentosPorCluster['vencimentos'] ?? []), 0, ',', '.')); ?> item(ns)</strong>
                </div>
                <div class="documentos-cluster-card-grid documentos-cluster-card-grid--timeline">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($documentosPorCluster['vencimentos'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $documento): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php
                            $empresa = $documento['nome_fantasia'] ?: ($documento['razao_social'] ?: '-');
                            $prioridadeOperacional = $documento['prioridade_operacional'] ?? ['label' => 'Estável', 'motivo' => 'Sem sinal crítico.', 'tom' => 'success', 'prazo' => '-'];
                        ?>
                        <article class="documentos-cluster-card <?php echo e($prioridadeOperacional['tom'] ?? 'success'); ?>">
                            <div>
                                <span class="documentos-priority-badge <?php echo e($prioridadeOperacional['tom'] ?? 'success'); ?>"><?php echo e($prioridadeOperacional['prazo'] ?? '-'); ?></span>
                                <h4><?php echo e($documento['titulo']); ?></h4>
                                <p><?php echo e(! empty($documento['data_vencimento']) ? 'Vencimento em ' . \Carbon\Carbon::parse($documento['data_vencimento'])->format('d/m/Y') : 'Sem data de vencimento cadastrada.'); ?></p>
                            </div>
                            <dl>
                                <div><dt>Empresa</dt><dd><?php echo e($empresa); ?></dd></div>
                                <div><dt>Status</dt><dd><?php echo e(ucfirst(str_replace('_', ' ', $documento['status'] ?? '-'))); ?></dd></div>
                                <div><dt>Prioridade</dt><dd><?php echo e($prioridadeOperacional['label'] ?? 'Estável'); ?></dd></div>
                            </dl>
                            <div class="documentos-cluster-card__actions">
                                <a href="<?php echo e($documento['enterprise_url'] ?? $documento['edit_url']); ?>">Enterprise</a>
                                <?php echo $__env->make('filament.pages.partials.documento-resolver-modal', ['documento' => $documento, 'empresa' => $empresa, 'prioridadeOperacional' => $prioridadeOperacional, 'statusResolucaoOptions' => $statusResolucaoOptions], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            </div>
                        </article>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="documentos-cluster-empty"><strong>Nenhum vencimento no radar.</strong><span>Quando existirem documentos com data de vencimento, eles serão organizados aqui por prazo.</span></div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </section>

        <?php elseif($clusterDocumentos === 'enterprise'): ?>
            <section class="documentos-enterprise-sync" aria-label="Integração com Gestão Documental Enterprise">
                <div class="documentos-enterprise-sync__content">
                    <span class="pz-ux-kicker">Integração Enterprise</span>
                    <h2>Hub para decidir, Enterprise para operar</h2>
                    <p><?php echo e($integracaoEnterprise['descricao'] ?? 'Use esta tela para enxergar a prioridade e avance para a Gestão Documental Enterprise quando precisar filtrar, tratar e acompanhar documentos em detalhe.'); ?></p>
                    <a href="<?php echo e($integracaoEnterprise['url'] ?? \App\Filament\Pages\GestaoDocumentalEnterprise::getUrl()); ?>">Abrir Gestão Documental Enterprise</a>
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

            <section class="documentos-hub-actions" aria-label="Atalhos principais de documentos">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $atalhos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $atalho): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <a class="documentos-hub-action <?php echo e($atalho['tom'] ?? 'neutral'); ?>" href="<?php echo e($atalho['url']); ?>">
                        <strong><?php echo e($atalho['label']); ?></strong>
                        <span><?php echo e($atalho['descricao']); ?></span>
                    </a>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </section>
        <?php else: ?>
            <div id="fila-documentos" class="prazzu-card documentos-cluster-table-card">
                <div class="prazzu-card-header">
                    <div><h3>Documentos recentes e próximos vencimentos</h3><p>Clique em qualquer linha para abrir detalhes, arquivo, portal e edição.</p></div>
                </div>
                <div class="prazzu-table-wrap">
                    <table class="prazzu-table prazzu-click-table documentos-premium-table">
                        <thead><tr><th>Documento</th><th>Prioridade</th><th>Empresa</th><th>Tipo</th><th>Status</th><th>Vencimento</th><th>Arquivo</th><th>Portal</th><th>Ações</th><th class="prazzu-modal-head"></th></tr></thead>
                        <tbody>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($documentosPorCluster['fila'] ?? $documentos); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $documento): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <?php
                                    $vencido = ! empty($documento['data_vencimento']) && \Carbon\Carbon::parse($documento['data_vencimento'])->isPast() && ! in_array($documento['status'] ?? '', ['concluido', 'concluído', 'finalizado'], true);
                                    $empresa = $documento['nome_fantasia'] ?: ($documento['razao_social'] ?: '-');
                                    $prioridadeOperacional = $documento['prioridade_operacional'] ?? ['label' => 'Estável', 'motivo' => 'Sem sinal crítico.', 'tom' => 'success', 'prazo' => '-'];
                                ?>
                                <tr class="documentos-priority-row <?php echo e($prioridadeOperacional['tom'] ?? 'success'); ?>">
                                    <td><strong><?php echo e($documento['titulo']); ?></strong><small><?php echo e(\Illuminate\Support\Str::limit($documento['descricao'] ?? 'Sem descrição cadastrada', 60)); ?></small></td>
                                    <td>
                                        <span class="documentos-priority-badge <?php echo e($prioridadeOperacional['tom'] ?? 'success'); ?>"><?php echo e($prioridadeOperacional['label'] ?? 'Estável'); ?></span>
                                        <small class="documentos-priority-reason"><?php echo e($prioridadeOperacional['motivo'] ?? 'Sem sinal crítico.'); ?></small>
                                    </td>
                                    <td><?php echo e($empresa); ?></td>
                                    <td><?php echo e(ucfirst(str_replace('_', ' ', $documento['tipo'] ?? '-'))); ?></td>
                                    <td><span class="prazzu-badge <?php echo e($vencido ? 'danger' : ''); ?>"><?php echo e(ucfirst(str_replace('_', ' ', $documento['status'] ?? '-'))); ?></span></td>
                                    <td><span class="<?php echo e($vencido ? 'prazzu-date-danger' : ''); ?>"><?php echo e(! empty($documento['data_vencimento']) ? \Carbon\Carbon::parse($documento['data_vencimento'])->format('d/m/Y') : '-'); ?></span><small class="documentos-priority-reason"><?php echo e($prioridadeOperacional['prazo'] ?? '-'); ?></small></td>
                                    <td><span class="prazzu-pill <?php echo e(! empty($documento['arquivo']) ? 'ok' : 'muted'); ?>"><?php echo e(! empty($documento['arquivo']) ? 'Com arquivo' : 'Sem arquivo'); ?></span></td>
                                    <td><span class="prazzu-pill <?php echo e(! empty($documento['portal_ativo']) ? 'ok' : 'muted'); ?>"><?php echo e(! empty($documento['portal_ativo']) ? 'Ativo' : 'Inativo'); ?></span></td>
                                    <td class="documentos-row-actions documentos-row-actions--filament">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($documento['arquivo_url'])): ?>
                                            <a href="<?php echo e($documento['arquivo_url']); ?>" target="_blank" rel="noopener noreferrer">Arquivo</a>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <a href="<?php echo e($documento['enterprise_url'] ?? $documento['edit_url']); ?>">Enterprise</a>
                                    </td>
                                    <td class="prazzu-modal-cell documentos-filament-modal-cell">
                                        <?php echo $__env->make('filament.pages.partials.documento-resolver-modal', ['documento' => $documento, 'empresa' => $empresa, 'prioridadeOperacional' => $prioridadeOperacional, 'statusResolucaoOptions' => $statusResolucaoOptions], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                    </td>
                                </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <tr><td colspan="10" class="prazzu-empty"><strong>Nenhum documento encontrado.</strong><br>Cadastre o primeiro documento usando o botão principal acima. Quando houver registros, eles aparecerão aqui com status, vencimento e portal.</td></tr>
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
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views/filament/pages/documentos.blade.php ENDPATH**/ ?>