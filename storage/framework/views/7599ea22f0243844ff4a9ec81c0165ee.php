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

    <style>
        .storage-page { display: grid; gap: 1.25rem; }
        .storage-hero { position: relative; overflow: hidden; border: 1px solid rgba(148, 163, 184, .24); border-radius: 28px; padding: 1.5rem; background: linear-gradient(135deg, rgba(124, 58, 237, .12), rgba(14, 165, 233, .08)), var(--filament-panels-color-gray-50, #f8fafc); }
        .dark .storage-hero { background: linear-gradient(135deg, rgba(124, 58, 237, .18), rgba(14, 165, 233, .10)), rgba(15, 23, 42, .72); border-color: rgba(148, 163, 184, .18); }
        .storage-hero__grid { display: grid; grid-template-columns: minmax(0, 1.4fr) minmax(280px, .8fr); gap: 1rem; align-items: stretch; }
        .storage-kicker { display: inline-flex; align-items: center; gap: .4rem; font-size: .72rem; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; color: rgb(124, 58, 237); }
        .storage-hero h1 { margin: .35rem 0 .35rem; font-size: clamp(1.8rem, 4vw, 3rem); line-height: 1; font-weight: 900; letter-spacing: -.04em; color: rgb(15, 23, 42); }
        .dark .storage-hero h1 { color: #fff; }
        .storage-hero p { max-width: 720px; color: rgb(71, 85, 105); font-size: .98rem; line-height: 1.65; }
        .dark .storage-hero p { color: rgb(203, 213, 225); }
        .storage-hero__panel { border-radius: 24px; padding: 1rem; background: rgba(255, 255, 255, .76); border: 1px solid rgba(148, 163, 184, .24); box-shadow: 0 18px 40px rgba(15, 23, 42, .08); }
        .dark .storage-hero__panel { background: rgba(15, 23, 42, .58); }
        .storage-hero__panel strong { display: block; font-size: 2.15rem; line-height: 1; font-weight: 900; color: rgb(15, 23, 42); }
        .dark .storage-hero__panel strong { color: #fff; }
        .storage-hero__panel span { display: block; margin-top: .35rem; color: rgb(100, 116, 139); font-size: .85rem; }
        .storage-cards { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: .9rem; }
        .storage-card { display: block; text-decoration: none; border-radius: 22px; padding: 1rem; border: 1px solid rgba(148, 163, 184, .22); background: white; box-shadow: 0 12px 30px rgba(15, 23, 42, .05); transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease; }
        .storage-card:hover { transform: translateY(-2px); border-color: rgba(124, 58, 237, .35); box-shadow: 0 18px 38px rgba(15, 23, 42, .08); }
        .dark .storage-card { background: rgba(15, 23, 42, .78); border-color: rgba(148, 163, 184, .16); }
        .storage-card span { display: block; color: rgb(100, 116, 139); font-size: .78rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; }
        .storage-card strong { display: block; margin-top: .45rem; font-size: 1.7rem; font-weight: 900; color: rgb(15, 23, 42); }
        .dark .storage-card strong { color: white; }
        .storage-card small { display: block; margin-top: .25rem; color: rgb(100, 116, 139); }
        .storage-card .storage-progress { margin-top: .7rem; height: .5rem; }
        .storage-mini-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .75rem; padding: 1rem 1.1rem; border-bottom: 1px solid rgba(148, 163, 184, .14); }
        .storage-mini-card { display: block; text-decoration: none; border-radius: 18px; padding: .85rem; background: rgba(248, 250, 252, .82); border: 1px solid rgba(148, 163, 184, .18); }
        .dark .storage-mini-card { background: rgba(30, 41, 59, .66); }
        .storage-mini-card strong { display: block; margin-top: .2rem; color: rgb(15, 23, 42); font-size: 1.15rem; font-weight: 900; }
        .dark .storage-mini-card strong { color: white; }
        .storage-mini-card p { margin: .3rem 0 0; color: rgb(100, 116, 139); font-size: .78rem; line-height: 1.45; }
        .storage-alert-list { display: grid; gap: .55rem; padding: 1rem 1.1rem; border-bottom: 1px solid rgba(148, 163, 184, .14); }
        .storage-alert-item { display: grid; grid-template-columns: auto 1fr auto; gap: .65rem; align-items: start; border-radius: 16px; padding: .75rem; background: rgba(248, 250, 252, .82); border: 1px solid rgba(148, 163, 184, .16); text-decoration: none; transition: transform .18s ease, border-color .18s ease; }
        .storage-alert-item:hover { transform: translateX(2px); border-color: currentColor; }
        .dark .storage-alert-item { background: rgba(30, 41, 59, .66); }
        .storage-alert-dot { width: .7rem; height: .7rem; border-radius: 999px; margin-top: .24rem; background: currentColor; }
        .storage-alert-item.success { color: rgb(34, 197, 94); }
        .storage-alert-item.warning { color: rgb(245, 158, 11); }
        .storage-alert-item.danger { color: rgb(239, 68, 68); }
        .storage-alert-item.primary { color: rgb(124, 58, 237); }
        .storage-alert-item strong { display: block; color: rgb(15, 23, 42); font-weight: 850; }
        .dark .storage-alert-item strong { color: white; }
        .storage-alert-item p { margin: .18rem 0 0; color: rgb(100, 116, 139); font-size: .8rem; line-height: 1.45; }
        .storage-grid { display: grid; grid-template-columns: minmax(0, 1fr) 360px; gap: 1rem; align-items: start; }
        .storage-section { border-radius: 24px; border: 1px solid rgba(148, 163, 184, .22); background: white; overflow: hidden; box-shadow: 0 12px 30px rgba(15, 23, 42, .04); }
        .dark .storage-section { background: rgba(15, 23, 42, .78); border-color: rgba(148, 163, 184, .16); }
        .storage-section__header { display: flex; justify-content: space-between; gap: 1rem; align-items: start; padding: 1rem 1.1rem; border-bottom: 1px solid rgba(148, 163, 184, .18); }
        .storage-section__header h2 { margin: .15rem 0; font-size: 1.05rem; font-weight: 850; color: rgb(15, 23, 42); }
        .dark .storage-section__header h2 { color: #fff; }
        .storage-section__header p { margin: 0; color: rgb(100, 116, 139); font-size: .88rem; }
        .storage-list { display: grid; }
        .storage-row { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 1rem; padding: .95rem 1.1rem; border-bottom: 1px solid rgba(148, 163, 184, .14); }
        .storage-row--action { align-items: center; }
        .storage-row:last-child { border-bottom: 0; }
        .storage-row h3 { margin: 0; font-size: .95rem; font-weight: 800; color: rgb(15, 23, 42); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .dark .storage-row h3 { color: white; }
        .storage-row p { margin: .25rem 0 0; color: rgb(100, 116, 139); font-size: .82rem; }
        .storage-meta { display: flex; flex-wrap: wrap; gap: .45rem; margin-top: .55rem; }
        .storage-pill { display: inline-flex; align-items: center; border-radius: 999px; padding: .28rem .55rem; font-size: .72rem; font-weight: 800; background: rgba(148, 163, 184, .14); color: rgb(71, 85, 105); }
        .storage-pill.success { background: rgba(34, 197, 94, .12); color: rgb(21, 128, 61); }
        .storage-pill.warning { background: rgba(245, 158, 11, .14); color: rgb(180, 83, 9); }
        .storage-pill.danger { background: rgba(239, 68, 68, .13); color: rgb(185, 28, 28); }
        .storage-pill.primary { background: rgba(124, 58, 237, .12); color: rgb(109, 40, 217); }
        .storage-action-link { display: inline-flex; align-items: center; justify-content: center; border-radius: 999px; padding: .42rem .75rem; font-size: .75rem; font-weight: 850; text-decoration: none; background: rgba(124, 58, 237, .10); color: rgb(109, 40, 217); border: 1px solid rgba(124, 58, 237, .18); white-space: nowrap; cursor: pointer; }
        .storage-action-link:hover { background: rgba(124, 58, 237, .16); }
        button.storage-action-link { font-family: inherit; }
        button.storage-action-link:disabled { opacity: .65; cursor: wait; }
        .storage-action-stack { display: grid; gap: .55rem; justify-items: end; }
        .storage-checklist { display: grid; gap: .5rem; padding: 1rem 1.1rem; border-top: 1px solid rgba(148, 163, 184, .14); background: rgba(248, 250, 252, .62); }
        .dark .storage-checklist { background: rgba(30, 41, 59, .38); }
        .storage-checklist strong { color: rgb(15, 23, 42); font-weight: 850; }
        .dark .storage-checklist strong { color: white; }
        .storage-checklist ol { margin: .2rem 0 0 1.1rem; color: rgb(100, 116, 139); font-size: .84rem; line-height: 1.55; }
        .storage-size { text-align: right; font-weight: 900; color: rgb(15, 23, 42); white-space: nowrap; }
        .dark .storage-size { color: white; }
        .storage-progress { height: .65rem; border-radius: 999px; background: rgba(148, 163, 184, .20); overflow: hidden; margin-top: .75rem; }
        .storage-progress span { display: block; height: 100%; border-radius: inherit; background: currentColor; max-width: 100%; }
        .storage-progress.success { color: rgb(34, 197, 94); }
        .storage-progress.warning { color: rgb(245, 158, 11); }
        .storage-progress.danger { color: rgb(239, 68, 68); }
        .storage-insights { display: grid; gap: .75rem; }
        .storage-insight { border-radius: 20px; padding: .9rem; background: rgba(248, 250, 252, .88); border: 1px solid rgba(148, 163, 184, .18); }
        .dark .storage-insight { background: rgba(30, 41, 59, .72); }
        .storage-insight strong { display: block; color: rgb(15, 23, 42); font-weight: 850; }
        .dark .storage-insight strong { color: white; }
        .storage-insight p { margin: .25rem 0 0; color: rgb(100, 116, 139); font-size: .84rem; line-height: 1.5; }
        .storage-empty { padding: 2rem; text-align: center; color: rgb(100, 116, 139); }
        .storage-alert { border-radius: 20px; padding: .9rem 1rem; border: 1px solid rgba(245, 158, 11, .32); background: rgba(245, 158, 11, .10); color: rgb(120, 53, 15); font-size: .88rem; }
        .dark .storage-alert { color: rgb(253, 230, 138); }
        @media (max-width: 1100px) { .storage-hero__grid, .storage-grid { grid-template-columns: 1fr; } .storage-cards { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 700px) { .storage-cards, .storage-mini-grid { grid-template-columns: 1fr; } .storage-row { grid-template-columns: 1fr; } .storage-size { text-align: left; } .storage-action-stack { justify-items: start; } .storage-alert-item { grid-template-columns: auto 1fr; } .storage-alert-item .storage-action-link { grid-column: 2; width: fit-content; } }
    </style>

    <div class="storage-page">
        <section class="storage-hero">
            <div class="storage-hero__grid">
                <div>
                    <span class="storage-kicker">Governança documental</span>
                    <h1>Armazenamento</h1>
                    <p>Controle espaço usado por empresa, limites de plano, arquivos pesados e documentos expirados sem misturar operação documental com gestão de capacidade.</p>
                </div>
                <div class="storage-hero__panel">
                    <span>Uso geral identificado</span>
                    <strong><?php echo e($resumo['percentual_global']); ?>%</strong>
                    <div class="storage-progress <?php echo e($resumo['tom_global']); ?>"><span style="width: <?php echo e(min(100, $resumo['percentual_global'])); ?>%"></span></div>
                    <span><?php echo e($resumo['total_formatado']); ?> usados de <?php echo e($resumo['total_limite_formatado']); ?></span>
                </div>
            </div>
        </section>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (! ($temColunaLimite)): ?>
            <div class="storage-alert">
                <strong>Limite funcionando por padrão de plano.</strong>
                Para limites manuais por empresa, execute o SQL enviado no pacote: <code>database/sql/2026_06_19_armazenamento_limites.sql</code>.
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <section class="storage-cards" aria-label="Resumo de armazenamento">
            <a class="storage-card" href="<?php echo e(\App\Filament\Pages\Armazenamento::getUrl(['aba' => 'limites'])); ?>"><span>Uso geral</span><strong><?php echo e($resumo['percentual_global']); ?>%</strong><div class="storage-progress <?php echo e($resumo['tom_global']); ?>"><span style="width: <?php echo e(min(100, $resumo['percentual_global'])); ?>%"></span></div><small><?php echo e($resumo['total_formatado']); ?> de <?php echo e($resumo['total_limite_formatado']); ?> · abrir limites</small></a>
            <a class="storage-card" href="<?php echo e(\App\Filament\Pages\Armazenamento::getUrl(['aba' => 'expirados'])); ?>"><span>Espaço recuperável</span><strong><?php echo e($resumo['recuperavel_formatado']); ?></strong><small>Estimativa com expirados/antigos · revisar limpeza</small></a>
            <a class="storage-card" href="<?php echo e(\App\Filament\Pages\Armazenamento::getUrl(['aba' => 'por-empresa'])); ?>"><span>Clientes/Empresas</span><strong><?php echo e(number_format($resumo['empresas'], 0, ',', '.')); ?></strong><small>Com arquivos vinculados · ver ranking</small></a>
            <a class="storage-card" href="<?php echo e(\App\Filament\Pages\Armazenamento::getUrl(['aba' => 'arquivos-pesados'])); ?>"><span>Alertas</span><strong><?php echo e(count($alertas)); ?></strong><small>Itens que pedem atenção operacional · agir agora</small></a>
        </section>

        <div class="storage-grid">
            <main class="storage-section">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($aba === 'visao-geral'): ?>
                    <div class="storage-section__header"><div><span class="storage-kicker">Painel executivo</span><h2>Saúde do armazenamento</h2><p>Alertas, espaço recuperável e os maiores consumidores em uma leitura rápida.</p></div></div>
                    <div class="storage-mini-grid">
                        <a class="storage-mini-card" href="<?php echo e(\App\Filament\Pages\Armazenamento::getUrl(['aba' => 'expirados'])); ?>"><span class="storage-kicker">Recuperável</span><strong><?php echo e($resumo['recuperavel_formatado']); ?></strong><p>Baseado em arquivos expirados ou antigos encontrados. Clique para revisar.</p></a>
                        <a class="storage-mini-card" href="<?php echo e(\App\Filament\Pages\Armazenamento::getUrl(['aba' => 'arquivos-pesados'])); ?>"><span class="storage-kicker">Arquivos</span><strong><?php echo e(number_format($resumo['total_arquivos'], 0, ',', '.')); ?></strong><p>Total localizado em anexos, documentos e portal. Clique para auditar.</p></a>
                    </div>
                    <div class="storage-alert-list">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $alertas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alerta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <a class="storage-alert-item <?php echo e($alerta['tom']); ?>" href="<?php echo e(\App\Filament\Pages\Armazenamento::getUrl(['aba' => $alerta['aba'] ?? 'visao-geral'])); ?>">
                                <span class="storage-alert-dot"></span>
                                <div><strong><?php echo e($alerta['titulo']); ?></strong><p><?php echo e($alerta['texto']); ?></p></div>
                                <span class="storage-action-link"><?php echo e($alerta['acao'] ?? 'Abrir'); ?></span>
                            </a>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>
                    <div class="storage-section__header"><div><span class="storage-kicker">Top 5</span><h2>Maiores consumidores</h2><p>Clientes/empresas que mais ocupam espaço agora.</p></div></div>
                    <div class="storage-list">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $topConsumidores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $empresa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <article class="storage-row storage-row--action" id="empresa-<?php echo e($empresa['empresa_id'] ?? 'sem-empresa'); ?>">
                                <div>
                                    <h3><?php echo e($empresa['empresa_nome']); ?></h3>
                                    <p><?php echo e($empresa['arquivos']); ?> arquivo(s) · Plano <?php echo e($empresa['plano']); ?></p>
                                    <div class="storage-progress <?php echo e($empresa['tom']); ?>"><span style="width: <?php echo e(min(100, $empresa['percentual'])); ?>%"></span></div>
                                    <div class="storage-meta"><span class="storage-pill <?php echo e($empresa['tom']); ?>"><?php echo e($empresa['percentual']); ?>% do limite</span><span class="storage-pill">Limite <?php echo e($empresa['limite_formatado']); ?></span><span class="storage-pill warning"><?php echo e($empresa['expirados']); ?> expirado(s)</span></div>
                                </div>
                                <div class="storage-action-stack">
                                    <div class="storage-size"><?php echo e($empresa['total_formatado']); ?></div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($empresa['empresa_id'])): ?>
                                        <button type="button" class="storage-action-link" wire:click='mountAction("verCliente", <?php echo json_encode(["empresaId" => (int) $empresa["empresa_id"]], 15, 512) ?>)' wire:loading.attr="disabled" wire:target='mountAction("verCliente", <?php echo json_encode(["empresaId" => (int) $empresa["empresa_id"]], 15, 512) ?>)'>Ver cliente</button>
                                    <?php else: ?>
                                        <span class="storage-pill warning">Sem vínculo</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </article>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <div class="storage-empty">Nenhum arquivo encontrado para análise.</div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php elseif($aba === 'por-empresa'): ?>
                    <div class="storage-section__header"><div><span class="storage-kicker">Empresas</span><h2>Uso de armazenamento por cliente/empresa</h2><p>Controle limite, percentual usado e acúmulo por cliente/empresa.</p></div><strong><?php echo e(count($porEmpresa)); ?></strong></div>
                    <div class="storage-list">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $porEmpresa; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $empresa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <article class="storage-row storage-row--action" id="empresa-<?php echo e($empresa['empresa_id'] ?? 'sem-empresa'); ?>">
                                <div>
                                    <h3><?php echo e($empresa['empresa_nome']); ?></h3>
                                    <p>Maior arquivo: <?php echo e($empresa['maior_arquivo']['nome'] ?? 'Não identificado'); ?></p>
                                    <div class="storage-progress <?php echo e($empresa['tom']); ?>"><span style="width: <?php echo e(min(100, $empresa['percentual'])); ?>%"></span></div>
                                    <div class="storage-meta"><span class="storage-pill <?php echo e($empresa['tom']); ?>"><?php echo e($empresa['percentual']); ?>%</span><span class="storage-pill primary"><?php echo e($empresa['arquivos']); ?> arquivo(s)</span><span class="storage-pill"><?php echo e($empresa['limite_formatado']); ?> de limite</span></div>
                                </div>
                                <div class="storage-action-stack">
                                    <div class="storage-size"><?php echo e($empresa['total_formatado']); ?></div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($empresa['empresa_id'])): ?>
                                        <button type="button" class="storage-action-link" wire:click='mountAction("verCliente", <?php echo json_encode(["empresaId" => (int) $empresa["empresa_id"]], 15, 512) ?>)' wire:loading.attr="disabled" wire:target='mountAction("verCliente", <?php echo json_encode(["empresaId" => (int) $empresa["empresa_id"]], 15, 512) ?>)'>Ver cliente</button>
                                    <?php else: ?>
                                        <span class="storage-pill warning">Sem vínculo</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </article>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <div class="storage-empty">Nenhuma empresa com arquivos.</div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php elseif($aba === 'arquivos-pesados'): ?>
                    <div class="storage-section__header"><div><span class="storage-kicker">Peso</span><h2>Arquivos mais pesados</h2><p>Arquivos que mais impactam custo e limite.</p></div><strong><?php echo e(count($arquivosPesados)); ?></strong></div>
                    <div class="storage-list">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $arquivosPesados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $arquivo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <article class="storage-row">
                                <div><h3 title="<?php echo e($arquivo['nome']); ?>"><?php echo e($arquivo['nome']); ?></h3><p><?php echo e($arquivo['empresa_nome']); ?> · <?php echo e($arquivo['item_titulo']); ?></p><div class="storage-meta"><span class="storage-pill primary"><?php echo e($arquivo['origem']); ?></span><span class="storage-pill"><?php echo e($arquivo['mime_type'] ?: 'Tipo não informado'); ?></span><span class="storage-pill <?php echo e($arquivo['expirado'] ? 'warning' : 'success'); ?>"><?php echo e($arquivo['expirado'] ? 'Expirado/antigo' : 'Ativo'); ?></span></div></div>
                                <div class="storage-action-stack">
                                    <div class="storage-size"><?php echo e($arquivo['tamanho_formatado']); ?></div>
                                    <a class="storage-action-link" href="<?php echo e(\App\Filament\Pages\Documentos::getUrl(['cluster' => 'fila'])); ?>">Revisar</a>
                                </div>
                            </article>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <div class="storage-empty">Nenhum arquivo pesado encontrado.</div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php elseif($aba === 'expirados'): ?>
                    <div class="storage-section__header"><div><span class="storage-kicker">Limpeza</span><h2>Arquivos expirados ou antigos</h2><p>Itens candidatos a revisão, arquivamento ou exclusão controlada.</p></div><strong><?php echo e(count($arquivosExpirados)); ?></strong></div>
                    <div class="storage-list">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $arquivosExpirados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $arquivo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <article class="storage-row">
                                <div><h3 title="<?php echo e($arquivo['nome']); ?>"><?php echo e($arquivo['nome']); ?></h3><p><?php echo e($arquivo['empresa_nome']); ?> · <?php echo e($arquivo['item_titulo']); ?></p><div class="storage-meta"><span class="storage-pill warning"><?php echo e($arquivo['idade_dias']); ?> dia(s)</span><span class="storage-pill"><?php echo e($arquivo['data_vencimento'] ? 'Venceu em ' . \Carbon\Carbon::parse($arquivo['data_vencimento'])->format('d/m/Y') : 'Arquivo antigo'); ?></span><span class="storage-pill primary"><?php echo e($arquivo['origem']); ?></span></div></div>
                                <div class="storage-action-stack">
                                    <div class="storage-size"><?php echo e($arquivo['tamanho_formatado']); ?></div>
                                    <a class="storage-action-link" href="<?php echo e(\App\Filament\Pages\Documentos::getUrl(['cluster' => 'fila'])); ?>">Revisar</a>
                                </div>
                            </article>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <div class="storage-empty">Nenhum arquivo expirado ou antigo encontrado.</div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div class="storage-checklist">
                        <strong>Fluxo recomendado de ação</strong>
                        <ol>
                            <li>Conferir se o documento ainda precisa ser retido por obrigação legal.</li>
                            <li>Registrar aprovação interna antes de excluir ou arquivar.</li>
                            <li>Remover somente arquivos sem pendência operacional e com rastreabilidade.</li>
                        </ol>
                    </div>
                <?php elseif($aba === 'limites'): ?>
                    <div class="storage-section__header"><div><span class="storage-kicker">Capacidade</span><h2>Limites de armazenamento</h2><p>Ranking de empresas mais próximas do limite.</p></div><strong><?php echo e(count($limites)); ?></strong></div>
                    <div class="storage-list">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $limites; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $empresa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <article class="storage-row">
                                <div><h3><?php echo e($empresa['empresa_nome']); ?></h3><p><?php echo e($empresa['total_formatado']); ?> usados de <?php echo e($empresa['limite_formatado']); ?></p><div class="storage-progress <?php echo e($empresa['tom']); ?>"><span style="width: <?php echo e(min(100, $empresa['percentual'])); ?>%"></span></div><div class="storage-meta"><span class="storage-pill <?php echo e($empresa['tom']); ?>"><?php echo e($empresa['percentual']); ?>% usado</span><span class="storage-pill">Plano <?php echo e($empresa['plano']); ?></span></div></div>
                                <div class="storage-action-stack">
                                    <div class="storage-size"><?php echo e($empresa['limite_formatado']); ?></div>
                                    <a class="storage-action-link" href="<?php echo e(\App\Filament\Pages\Armazenamento::getUrl(['aba' => 'expirados'])); ?>">Limpar</a>
                                </div>
                            </article>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <div class="storage-empty">Nenhum limite para exibir.</div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </main>

            <aside class="storage-insights" aria-label="Insights de armazenamento">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $insights; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $insight): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <article class="storage-insight">
                        <span class="storage-pill <?php echo e($insight['tom']); ?>"><?php echo e(ucfirst($insight['tom'])); ?></span>
                        <strong><?php echo e($insight['titulo']); ?></strong>
                        <p><?php echo e($insight['texto']); ?></p>
                    </article>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>

                <article class="storage-insight">
                    <strong>Como usar esta página</strong>
                    <p>Comece pelos limites, depois revise arquivos pesados e finalize com expirados. A exclusão deve ser feita com regra de retenção e auditoria.</p>
                </article>
            </aside>
        </div>
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
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views/filament/pages/armazenamento.blade.php ENDPATH**/ ?>