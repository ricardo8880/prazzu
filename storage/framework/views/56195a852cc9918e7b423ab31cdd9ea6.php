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
        $urls = $dashboard['urls'] ?? [];
        $usuario = explode(' ', $dashboard['usuario'] ?? 'Usuário')[0];
        $resumoHoje = collect($dashboard['resumoHoje'] ?? [])->keyBy('label');
        $minhasPendencias = collect($dashboard['minhasPendencias'] ?? []);
        $vencimentosProximos = collect($dashboard['vencimentosProximos'] ?? []);
        $aprovacoesAguardando = collect($dashboard['aprovacoesAguardando'] ?? []);
        $itensAtrasados = collect($dashboard['itensAtrasados'] ?? []);
        $resumoEmpresas = collect($dashboard['resumoEmpresas'] ?? []);
        $notificacoes = collect($dashboard['notificacoes'] ?? []);
        $documentosVencidos = collect($dashboard['documentosVencidos'] ?? []);
        $documentosVencendo = collect($dashboard['documentosVencendo'] ?? []);
        $atividades = collect($dashboard['atividades'] ?? []);
        $filaPrioridade = collect($dashboard['filaPrioridade']['itens'] ?? [])->take(5);
        $filaPrioridadeTotal = (int) ($dashboard['filaPrioridade']['total'] ?? $filaPrioridade->count());
        $uxNavigation = collect($uxNavigation ?? []);

        $obrigacoesVencidas = $resumoHoje->get('Atrasados', []);
        $vencimentosSemana = $resumoHoje->get('Vencem em 7 dias', []);
        $aprovacoesPendentes = $resumoHoje->get('Aprovações', []);
        $pendenciasResumo = $resumoHoje->get('Pendências', []);
        $clientesEmRisco = $resumoEmpresas->filter(fn ($empresa) => in_array($empresa['tone'] ?? '', ['danger', 'warning'], true))->count();

        $dataReferencia = now()->locale('pt_BR')->translatedFormat('d \\d\\e F \\d\\e Y');
        $diaSemana = ucfirst(now()->locale('pt_BR')->translatedFormat('l'));

        $kpis = [
            ['label' => 'Pendências abertas', 'value' => $pendenciasResumo['value'] ?? $minhasPendencias->count(), 'hint' => 'Resumo da fila operacional', 'icon' => 'bi-list-check', 'tone' => 'warning', 'url' => $urls['minhasPendencias'] ?? '#'],
            ['label' => 'Atrasados', 'value' => $obrigacoesVencidas['value'] ?? $itensAtrasados->count(), 'hint' => 'Itens com risco imediato', 'icon' => 'bi-exclamation-octagon', 'tone' => 'danger', 'url' => $urls['prazos'] ?? '#'],
            ['label' => 'Aprovações', 'value' => $aprovacoesPendentes['value'] ?? $aprovacoesAguardando->count(), 'hint' => 'Decisões aguardando validação', 'icon' => 'bi-patch-check', 'tone' => 'info', 'url' => $urls['centralAprovacoes'] ?? '#'],
            ['label' => 'Documentos críticos', 'value' => $documentosVencidos->count() + $documentosVencendo->count(), 'hint' => 'Vencidos ou perto do vencimento', 'icon' => 'bi-folder2-open', 'tone' => 'purple', 'url' => $urls['documentos'] ?? '#'],
            ['label' => 'Vencem em 7 dias', 'value' => $vencimentosSemana['value'] ?? $vencimentosProximos->count(), 'hint' => 'Prazos próximos', 'icon' => 'bi-calendar-event', 'tone' => 'amber', 'url' => $urls['calendario'] ?? '#'],
            ['label' => 'Clientes em risco', 'value' => $clientesEmRisco, 'hint' => 'Empresas que exigem atenção', 'icon' => 'bi-shield-exclamation', 'tone' => 'slate', 'url' => $urls['clientes'] ?? '#'],
        ];

        $alertas = collect()
            ->merge($itensAtrasados->map(fn ($item) => ['tipo' => 'Atraso', 'titulo' => $item['titulo'] ?? 'Item atrasado', 'detalhe' => $item['empresa'] ?? 'Sem empresa vinculada', 'meta' => $item['tempo'] ?? ($item['data'] ?? 'Atrasado'), 'tone' => 'danger', 'url' => $item['url'] ?? ($urls['prazos'] ?? '#')]))
            ->merge($documentosVencidos->map(fn ($item) => ['tipo' => 'Documento', 'titulo' => $item['titulo'] ?? 'Documento vencido', 'detalhe' => $item['empresa'] ?? 'Sem empresa vinculada', 'meta' => $item['tempo'] ?? ($item['data'] ?? 'Vencido'), 'tone' => 'warning', 'url' => $item['url'] ?? ($urls['documentos'] ?? '#')]))
            ->merge($aprovacoesAguardando->map(fn ($item) => ['tipo' => 'Aprovação', 'titulo' => $item['titulo'] ?? 'Aprovação pendente', 'detalhe' => $item['empresa'] ?? 'Sem empresa vinculada', 'meta' => $item['tempo'] ?? 'Aguardando', 'tone' => 'info', 'url' => $item['url'] ?? ($urls['centralAprovacoes'] ?? '#')]))
            ->take(5);

        $atalhos = [
            ['label' => 'Resolver pendências', 'hint' => 'Abrir fila completa', 'icon' => 'bi-list-check', 'url' => $urls['minhasPendencias'] ?? '#'],
            ['label' => 'Ver documentos', 'hint' => 'Central documental', 'icon' => 'bi-folder2-open', 'url' => $urls['documentos'] ?? '#'],
            ['label' => 'Aprovar itens', 'hint' => 'Central de aprovações', 'icon' => 'bi-patch-check', 'url' => $urls['centralAprovacoes'] ?? '#'],
            ['label' => 'Consultar prazos', 'hint' => 'Calendário e SLA', 'icon' => 'bi-calendar-week', 'url' => $urls['calendario'] ?? ($urls['prazos'] ?? '#')],
            ['label' => 'Clientes', 'hint' => 'Carteira e atendimento', 'icon' => 'bi-buildings', 'url' => $urls['clientes'] ?? '#'],
            ['label' => 'Relatórios', 'hint' => 'Analisar indicadores', 'icon' => 'bi-graph-up-arrow', 'url' => $urls['relatorios'] ?? '#'],
        ];
    ?>


    <div class="pz-home-exec">
        <section class="pz-hero">
            <div class="pz-hero-inner">
                <div>
                    <span class="pz-eyebrow"><i class="bi bi-speedometer2"></i> Home da Contabilidade</span>
                    <h1>Olá, <?php echo e($usuario); ?>. Esta é a visão geral da operação.</h1>
                    <p>A Home agora resume o que importa e direciona cada ação para a aba correta. Pendências, documentos, aprovações e prazos continuam concentrados nas telas responsáveis.</p>
                </div>
                <div class="pz-hero-actions">
                    <a class="pz-btn pz-btn-primary" href="<?php echo e($urls['minhasPendencias'] ?? '#'); ?>"><i class="bi bi-list-check"></i> Abrir pendências</a>
                    <a class="pz-btn pz-btn-ghost" href="<?php echo e(request()->fullUrl()); ?>"><i class="bi bi-arrow-clockwise"></i> Atualizar</a>
                </div>
            </div>
            <div class="pz-hero-inner pz-hero-inner--spaced">
                <span class="pz-eyebrow"><i class="bi bi-calendar3"></i> <?php echo e($dataReferencia); ?></span>
                <span class="pz-eyebrow"><i class="bi bi-clock-history"></i> <?php echo e($diaSemana); ?></span>
            </div>
        </section>

        <section class="pz-kpi-grid" aria-label="Indicadores executivos da contabilidade">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $kpis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kpi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <a href="<?php echo e($kpi['url']); ?>" class="pz-kpi pz-tone-<?php echo e($kpi['tone']); ?>">
                    <div class="pz-kpi-top">
                        <div class="pz-icon-box"><i class="bi <?php echo e($kpi['icon']); ?>"></i></div>
                        <i class="bi bi-arrow-up-right"></i>
                    </div>
                    <strong><?php echo e($kpi['value']); ?></strong>
                    <span><?php echo e($kpi['label']); ?></span>
                    <small><?php echo e($kpi['hint']); ?></small>
                </a>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </section>

        <section class="pz-dashboard-grid">
            <div class="pz-panel">
                <div class="pz-panel-head">
                    <div><h2>Alertas prioritários</h2><p>Resumo dos itens que exigem atenção. A resolução acontece na aba correta.</p></div>
                    <a href="<?php echo e($urls['minhasPendencias'] ?? '#'); ?>">Ver fila completa</a>
                </div>
                <div class="pz-list">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $alertas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alerta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <a href="<?php echo e($alerta['url'] ?? '#'); ?>" class="pz-row">
                            <span class="pz-badge pz-badge-<?php echo e($alerta['tone'] ?? 'info'); ?>"><?php echo e($alerta['tipo'] ?? 'Alerta'); ?></span>
                            <div>
                                <div class="pz-row-title"><?php echo e($alerta['titulo'] ?? 'Item sem título'); ?></div>
                                <div class="pz-row-detail"><?php echo e($alerta['detalhe'] ?? 'Sem empresa vinculada'); ?></div>
                            </div>
                            <div class="pz-row-meta"><?php echo e($alerta['meta'] ?? '-'); ?></div>
                        </a>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="pz-empty"><i class="bi bi-check2-circle"></i> Nenhum alerta crítico encontrado para hoje.</div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            <div class="pz-panel">
                <div class="pz-panel-head">
                    <div><h2>Atalhos orientados</h2><p>Acesso rápido sem duplicar conteúdo operacional.</p></div>
                </div>
                <div class="pz-shortcuts">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $atalhos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $atalho): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <a href="<?php echo e($atalho['url']); ?>" class="pz-shortcut">
                            <i class="bi <?php echo e($atalho['icon']); ?>"></i>
                            <span><strong><?php echo e($atalho['label']); ?></strong><small><?php echo e($atalho['hint']); ?></small></span>
                        </a>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            </div>
        </section>



        <section class="pz-panel pz-ux-map" aria-label="Mapa de navegação do Prazzu">
            <div class="pz-panel-head">
                <div><h2>Mapa rápido do sistema</h2><p>Use estes grupos como caminho oficial. Cada assunto aponta para a tela dona do fluxo.</p></div>
            </div>
            <div class="pz-ux-map-grid">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $uxNavigation; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cluster): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <article class="pz-ux-cluster">
                        <div class="pz-ux-cluster-head">
                            <span class="pz-icon-box"><i class="bi <?php echo e($cluster['icon'] ?? 'bi-grid'); ?>"></i></span>
                            <div>
                                <strong><?php echo e($cluster['label'] ?? 'Grupo'); ?></strong>
                                <small><?php echo e($cluster['hint'] ?? ''); ?></small>
                            </div>
                        </div>
                        <div class="pz-ux-links">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($cluster['items'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <a href="<?php echo e($item['url'] ?? '#'); ?>" class="<?php echo \Illuminate\Support\Arr::toCssClasses(['pz-ux-link', 'pz-ux-link--active' => $item['active'] ?? false]); ?>">
                                    <span><?php echo e($item['label'] ?? 'Acessar'); ?></span>
                                    <small><?php echo e($item['hint'] ?? 'Abrir tela'); ?></small>
                                </a>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    </article>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
        </section>

        <section class="pz-dashboard-grid">
            <div class="pz-panel">
                <div class="pz-panel-head">
                    <div><h2>Próximos vencimentos</h2><p>Somente um resumo. A gestão completa fica em Calendário/Prazos.</p></div>
                    <a href="<?php echo e($urls['calendario'] ?? ($urls['prazos'] ?? '#')); ?>">Abrir calendário</a>
                </div>
                <div class="pz-list">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $vencimentosProximos->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <a href="<?php echo e($item['url'] ?? ($urls['prazos'] ?? '#')); ?>" class="pz-row">
                            <span class="pz-badge pz-badge-warning">Prazo</span>
                            <div><div class="pz-row-title"><?php echo e($item['titulo'] ?? 'Vencimento operacional'); ?></div><div class="pz-row-detail"><?php echo e($item['empresa'] ?? 'Sem empresa vinculada'); ?></div></div>
                            <div class="pz-row-meta"><?php echo e($item['data'] ?? ($item['tempo'] ?? '-')); ?></div>
                        </a>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="pz-empty"><i class="bi bi-calendar-check"></i> Nenhum vencimento próximo encontrado.</div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            <div class="pz-panel">
                <div class="pz-panel-head">
                    <div><h2>Atividade recente</h2><p>Últimos movimentos relevantes da operação.</p></div>
                </div>
                <div class="pz-activity">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $atividades->take(5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $atividade): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <a href="<?php echo e($atividade['url'] ?? '#'); ?>" class="pz-activity-item pz-link-reset">
                            <span class="pz-dot"></span>
                            <span><strong><?php echo e($atividade['titulo'] ?? ($atividade['texto'] ?? 'Atividade registrada')); ?></strong><small><?php echo e($atividade['empresa'] ?? ($atividade['quando'] ?? 'Agora')); ?></small></span>
                        </a>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="pz-empty"><i class="bi bi-clock-history"></i> Nenhuma atividade recente para exibir.</div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </section>

        <section class="pz-footer-note">
            <strong>Regra de UX aplicada no Lote 12:</strong> a Home e o topo usam um mapa único de navegação. A tela inicial orienta, mas cada ação continua na aba dona do assunto.
        </section>
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
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views/filament/pages/home.blade.php ENDPATH**/ ?>