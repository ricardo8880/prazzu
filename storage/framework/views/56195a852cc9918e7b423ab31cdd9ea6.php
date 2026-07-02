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

    <style>
        .pz-home-exec{--pz-bg:#f8fafc;--pz-card:#fff;--pz-soft:#f1f5f9;--pz-text:#0f172a;--pz-muted:#64748b;--pz-border:rgba(148,163,184,.28);--pz-shadow:0 18px 45px rgba(15,23,42,.08);display:grid;gap:1.25rem;color:var(--pz-text)}
        .dark .pz-home-exec{--pz-bg:#020617;--pz-card:#0f172a;--pz-soft:#111827;--pz-text:#e5e7eb;--pz-muted:#94a3b8;--pz-border:rgba(148,163,184,.18);--pz-shadow:0 18px 45px rgba(0,0,0,.35)}
        .pz-hero{position:relative;overflow:hidden;border:1px solid var(--pz-border);border-radius:28px;background:linear-gradient(135deg,#0f172a,#1e3a8a 58%,#0369a1);padding:1.35rem;box-shadow:var(--pz-shadow);color:#fff}
        .pz-hero:after{content:"";position:absolute;inset:auto -8% -45% auto;width:360px;height:360px;border-radius:999px;background:rgba(255,255,255,.12);filter:blur(2px)}
        .pz-hero-inner{position:relative;z-index:1;display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap}.pz-eyebrow{display:inline-flex;gap:.5rem;align-items:center;border:1px solid rgba(255,255,255,.2);background:rgba(255,255,255,.12);border-radius:999px;padding:.35rem .65rem;font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em}.pz-hero h1{margin:.75rem 0 .35rem;font-size:clamp(1.75rem,3vw,2.65rem);font-weight:850;letter-spacing:-.04em}.pz-hero p{margin:0;color:rgba(255,255,255,.78);max-width:720px}.pz-hero-actions{display:flex;gap:.65rem;flex-wrap:wrap}.pz-btn{display:inline-flex;align-items:center;gap:.5rem;border-radius:14px;padding:.75rem 1rem;font-weight:800;text-decoration:none;transition:.18s ease}.pz-btn-primary{background:#fff;color:#0f172a}.pz-btn-ghost{border:1px solid rgba(255,255,255,.22);color:#fff;background:rgba(255,255,255,.08)}.pz-btn:hover{transform:translateY(-1px)}
        .pz-kpi-grid{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:.85rem}.pz-kpi{border:1px solid var(--pz-border);border-radius:22px;background:var(--pz-card);padding:1rem;text-decoration:none;color:inherit;box-shadow:0 10px 28px rgba(15,23,42,.05);transition:.18s ease}.pz-kpi:hover{transform:translateY(-2px);box-shadow:var(--pz-shadow)}.pz-kpi-top{display:flex;align-items:center;justify-content:space-between;gap:.75rem}.pz-icon-box{width:42px;height:42px;border-radius:15px;display:grid;place-items:center;background:var(--pz-soft);color:#2563eb}.pz-kpi strong{display:block;margin-top:.9rem;font-size:2rem;line-height:1;font-weight:900;letter-spacing:-.05em}.pz-kpi span{display:block;margin-top:.45rem;font-size:.82rem;font-weight:800}.pz-kpi small{display:block;margin-top:.25rem;color:var(--pz-muted);font-size:.76rem}.pz-tone-danger .pz-icon-box{color:#dc2626;background:#fee2e2}.pz-tone-warning .pz-icon-box,.pz-tone-amber .pz-icon-box{color:#d97706;background:#fef3c7}.pz-tone-info .pz-icon-box{color:#2563eb;background:#dbeafe}.pz-tone-purple .pz-icon-box{color:#7c3aed;background:#ede9fe}.dark .pz-tone-danger .pz-icon-box{background:rgba(220,38,38,.16)}.dark .pz-tone-warning .pz-icon-box,.dark .pz-tone-amber .pz-icon-box{background:rgba(217,119,6,.16)}.dark .pz-tone-info .pz-icon-box{background:rgba(37,99,235,.16)}.dark .pz-tone-purple .pz-icon-box{background:rgba(124,58,237,.16)}
        .pz-dashboard-grid{display:grid;grid-template-columns:minmax(0,1.5fr) minmax(320px,.85fr);gap:1rem}.pz-panel{border:1px solid var(--pz-border);border-radius:24px;background:var(--pz-card);box-shadow:0 10px 28px rgba(15,23,42,.05);overflow:hidden}.pz-panel-head{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;padding:1rem 1.1rem;border-bottom:1px solid var(--pz-border)}.pz-panel-head h2{margin:0;font-size:1rem;font-weight:900}.pz-panel-head p{margin:.2rem 0 0;color:var(--pz-muted);font-size:.84rem}.pz-panel-head a{font-size:.82rem;font-weight:800;text-decoration:none;color:#2563eb}.pz-list{display:grid}.pz-row{display:grid;grid-template-columns:auto 1fr auto;align-items:center;gap:.85rem;padding:.9rem 1.1rem;border-bottom:1px solid var(--pz-border);text-decoration:none;color:inherit}.pz-row:last-child{border-bottom:0}.pz-row:hover{background:var(--pz-soft)}.pz-badge{display:inline-flex;align-items:center;border-radius:999px;padding:.28rem .55rem;font-size:.7rem;font-weight:900}.pz-badge-danger{background:#fee2e2;color:#991b1b}.pz-badge-warning{background:#fef3c7;color:#92400e}.pz-badge-info{background:#dbeafe;color:#1e40af}.dark .pz-badge-danger{background:rgba(220,38,38,.16);color:#fca5a5}.dark .pz-badge-warning{background:rgba(217,119,6,.16);color:#fcd34d}.dark .pz-badge-info{background:rgba(37,99,235,.16);color:#93c5fd}.pz-row-title{font-weight:850}.pz-row-detail,.pz-row-meta{color:var(--pz-muted);font-size:.8rem}.pz-empty{padding:1.25rem;text-align:center;color:var(--pz-muted);background:var(--pz-soft);margin:1rem;border-radius:18px}.pz-shortcuts{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.75rem;padding:1rem}.pz-shortcut{border:1px solid var(--pz-border);border-radius:18px;padding:.9rem;text-decoration:none;color:inherit;background:linear-gradient(180deg,var(--pz-card),var(--pz-soft));display:flex;gap:.75rem;align-items:flex-start}.pz-shortcut i{font-size:1.1rem;color:#2563eb}.pz-shortcut strong{display:block;font-size:.86rem}.pz-shortcut small{display:block;margin-top:.18rem;color:var(--pz-muted);font-size:.76rem}.pz-activity{padding:1rem;display:grid;gap:.8rem}.pz-activity-item{display:flex;gap:.7rem;align-items:flex-start}.pz-dot{width:10px;height:10px;border-radius:999px;background:#2563eb;margin-top:.35rem;box-shadow:0 0 0 4px rgba(37,99,235,.12)}.pz-activity strong{display:block;font-size:.86rem}.pz-activity small{display:block;color:var(--pz-muted);font-size:.78rem;margin-top:.15rem}.pz-footer-note{border:1px dashed var(--pz-border);border-radius:20px;padding:1rem;color:var(--pz-muted);background:var(--pz-soft);font-size:.88rem}.pz-footer-note strong{color:var(--pz-text)}
        @media (max-width:1280px){.pz-kpi-grid{grid-template-columns:repeat(3,minmax(0,1fr))}.pz-dashboard-grid{grid-template-columns:1fr}}@media (max-width:760px){.pz-kpi-grid,.pz-shortcuts{grid-template-columns:1fr}.pz-row{grid-template-columns:1fr}.pz-hero{border-radius:22px;padding:1rem}}
    </style>

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
            <div class="pz-hero-inner" style="margin-top:1.15rem">
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
                        <a href="<?php echo e($atividade['url'] ?? '#'); ?>" class="pz-activity-item" style="text-decoration:none;color:inherit">
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
            <strong>Regra de conteúdo aplicada no Lote 2:</strong> a Home não ensina, não resolve e não duplica fluxos. Ela mostra o estado da operação e leva o usuário para a aba dona do assunto.
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