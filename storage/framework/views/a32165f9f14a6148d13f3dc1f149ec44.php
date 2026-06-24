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
        .prazzu-central-admin{display:grid;gap:20px}.prazzu-central-admin *{box-sizing:border-box}.prazzu-ca-hero{border-radius:26px;padding:28px;background:linear-gradient(135deg,#111827,#1f2937);color:#fff;display:flex;justify-content:space-between;gap:24px;align-items:flex-start}.prazzu-ca-eyebrow{display:inline-flex;align-items:center;gap:8px;border-radius:999px;background:rgba(255,255,255,.10);padding:6px 10px;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.08em}.prazzu-ca-hero h1{font-size:30px;line-height:1.1;font-weight:900;margin:12px 0 0}.prazzu-ca-hero p{margin:10px 0 0;color:#d1d5db;max-width:760px}.prazzu-ca-note{border:1px solid rgba(255,255,255,.16);background:rgba(255,255,255,.08);border-radius:18px;padding:16px;min-width:260px}.prazzu-ca-note strong{display:block;font-size:24px}.prazzu-ca-note span,.prazzu-ca-note p{color:#d1d5db}.prazzu-ca-note p{margin:6px 0 0}.prazzu-ca-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px}.prazzu-ca-stat,.prazzu-ca-card,.prazzu-ca-section{border:1px solid #e5e7eb;border-radius:22px;background:#fff;padding:18px;box-shadow:0 8px 24px rgba(15,23,42,.05)}.prazzu-ca-stat{display:flex;align-items:center;gap:12px}.prazzu-ca-stat-icon,.prazzu-ca-icon,.prazzu-ca-action-icon,.prazzu-ca-status-icon{display:grid;place-items:center;flex:none}.prazzu-ca-stat-icon{width:42px;height:42px;border-radius:15px;background:#f3f4f6;color:#111827;font-size:20px}.prazzu-ca-stat span{display:block;color:#64748b;font-size:13px}.prazzu-ca-stat strong{display:block;margin-top:3px;font-size:24px;font-weight:900;color:#111827}.prazzu-ca-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px}.prazzu-ca-card{display:flex;flex-direction:column;gap:14px;min-height:220px}.prazzu-ca-card-top{display:flex;gap:12px;align-items:flex-start}.prazzu-ca-icon{width:46px;height:46px;border-radius:16px;background:#f3f4f6;color:#111827;font-size:22px}.prazzu-ca-card h2,.prazzu-ca-section h2{font-size:18px;font-weight:900;margin:0;color:#111827}.prazzu-ca-card p,.prazzu-ca-section p{margin:5px 0 0;color:#64748b;line-height:1.45}.prazzu-ca-shortcuts{display:flex;flex-wrap:wrap;gap:8px;margin-top:2px}.prazzu-ca-shortcut{display:inline-flex;align-items:center;border:1px solid #e5e7eb;background:#f8fafc;color:#111827;border-radius:999px;padding:6px 10px;font-size:12px;font-weight:800;text-decoration:none}.prazzu-ca-shortcut:hover{background:#eef2ff;color:#111827}.prazzu-ca-card-footer{margin-top:auto}.prazzu-ca-link{display:inline-flex;align-items:center;gap:8px;justify-content:center;border-radius:12px;padding:10px 14px;background:#111827;color:#fff;font-weight:800;text-decoration:none}.prazzu-ca-link:hover{color:#fff}.prazzu-ca-disabled{display:inline-flex;align-items:center;justify-content:center;border-radius:12px;padding:10px 14px;background:#f3f4f6;color:#64748b;font-weight:800}.prazzu-ca-two-columns{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:16px}.prazzu-ca-list{margin:14px 0 0;padding:0;display:grid;gap:10px;list-style:none}.prazzu-ca-list li{display:flex;gap:10px;align-items:flex-start;color:#374151}.prazzu-ca-status-icon{width:28px;height:28px;border-radius:999px;background:#f0fdf4;color:#15803d;margin-top:-2px}.prazzu-ca-status-icon.warning{background:#fffbeb;color:#b45309}.prazzu-ca-actions{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;margin-top:14px}.prazzu-ca-action{display:flex;gap:10px;align-items:center;border:1px solid #e5e7eb;border-radius:16px;padding:12px;background:#f8fafc;color:#111827;text-decoration:none;font-weight:800}.prazzu-ca-action:hover{background:#eef2ff;color:#111827}.prazzu-ca-action-icon{width:34px;height:34px;border-radius:12px;background:#fff;color:#111827}.prazzu-ca-activity{margin:14px 0 0;display:grid;gap:10px}.prazzu-ca-activity-item{border:1px solid #e5e7eb;background:#f8fafc;border-radius:16px;padding:12px}.prazzu-ca-activity-item strong{display:block;color:#111827}.prazzu-ca-activity-item span{display:block;color:#64748b;font-size:13px;margin-top:3px}@media(max-width:1100px){.prazzu-ca-grid,.prazzu-ca-stats{grid-template-columns:repeat(2,minmax(0,1fr))}.prazzu-ca-hero,.prazzu-ca-two-columns{display:block}.prazzu-ca-note,.prazzu-ca-two-columns .prazzu-ca-section+.prazzu-ca-section{margin-top:16px}}@media(max-width:760px){.prazzu-ca-grid,.prazzu-ca-stats,.prazzu-ca-actions{grid-template-columns:1fr}.prazzu-ca-hero h1{font-size:24px}}
    </style>

    <div class="prazzu-central-admin">
        <section class="prazzu-ca-hero">
            <div>
                <span class="prazzu-ca-eyebrow"><i class="bi bi-gear"></i> Configurações</span>
                <h1>Central Administrativa</h1>
                <p>
                    Ajuste os dados da empresa, controle usuários e revise permissões em um só lugar.
                    Use esta área apenas quando precisar configurar o escritório ou revisar acessos.
                </p>
            </div>
            <aside class="prazzu-ca-note">
                <span>Resumo da conta</span>
                <strong><?php echo e(collect($this->resumoConta())->sum('value')); ?></strong>
                <p>registros principais acompanhados nesta tela.</p>
            </aside>
        </section>

        <section class="prazzu-ca-stats" aria-label="Resumo da conta">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->resumoConta(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <article class="prazzu-ca-stat">
                    <div class="prazzu-ca-stat-icon"><i class="bi <?php echo e($stat['icon']); ?>"></i></div>
                    <div>
                        <span><?php echo e($stat['label']); ?></span>
                        <strong><?php echo e($stat['value']); ?></strong>
                    </div>
                </article>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </section>

        <section class="prazzu-ca-grid" aria-label="Áreas de configuração">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->modulos(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $modulo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <article class="prazzu-ca-card" <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'central-admin-'.e($modulo['key']).''; ?>wire:key="central-admin-<?php echo e($modulo['key']); ?>">
                    <div class="prazzu-ca-card-top">
                        <div class="prazzu-ca-icon"><i class="bi <?php echo e($modulo['icone']); ?>"></i></div>
                        <div>
                            <h2><?php echo e($modulo['titulo']); ?></h2>
                            <p><?php echo e($modulo['descricao']); ?></p>
                        </div>
                    </div>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($modulo['disponivel'] && ! empty($modulo['atalhos'])): ?>
                        <div class="prazzu-ca-shortcuts">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $modulo['atalhos']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $atalho): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(filled($atalho['url'] ?? null)): ?>
                                    <a class="prazzu-ca-shortcut" href="<?php echo e($atalho['url']); ?>"><?php echo e($atalho['label']); ?></a>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <div class="prazzu-ca-card-footer">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($modulo['disponivel'] && filled($modulo['url'])): ?>
                            <a class="prazzu-ca-link" href="<?php echo e($modulo['url']); ?>">
                                <?php echo e($modulo['acao']); ?> <i class="bi bi-arrow-right-short"></i>
                            </a>
                        <?php else: ?>
                            <span class="prazzu-ca-disabled">Sem acesso para este perfil</span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </article>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </section>

        <section class="prazzu-ca-two-columns">
            <article class="prazzu-ca-section">
                <h2>Saúde da conta</h2>
                <p>Veja rapidamente se o básico da administração está pronto para uso.</p>
                <ul class="prazzu-ca-list">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->saudeConta(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <li>
                            <span class="prazzu-ca-status-icon <?php echo e($item['ok'] ? '' : 'warning'); ?>">
                                <i class="bi <?php echo e($item['ok'] ? 'bi-check2' : 'bi-exclamation-triangle'); ?>"></i>
                            </span>
                            <span>
                                <strong><?php echo e($item['label']); ?></strong><br>
                                <?php echo e($item['texto']); ?>

                            </span>
                        </li>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </ul>
            </article>

            <article class="prazzu-ca-section">
                <h2>Ações rápidas</h2>
                <p>Atalhos para as configurações mais usadas pelo gestor do escritório.</p>
                <div class="prazzu-ca-actions">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->acoesRapidas(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $acao): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <a class="prazzu-ca-action" href="<?php echo e($acao['url']); ?>">
                            <span class="prazzu-ca-action-icon"><i class="bi <?php echo e($acao['icon']); ?>"></i></span>
                            <span><?php echo e($acao['label']); ?></span>
                        </a>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            </article>
        </section>

        <section class="prazzu-ca-section">
            <h2>Atividade recente</h2>
            <p>Últimas alterações administrativas registradas no sistema.</p>
            <div class="prazzu-ca-activity">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->atividadeRecente(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $atividade): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <div class="prazzu-ca-activity-item">
                        <strong><?php echo e($atividade['titulo']); ?></strong>
                        <span><?php echo e($atividade['descricao']); ?> · <?php echo e($atividade['quando']); ?></span>
                    </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
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
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views/filament/pages/central-administrativa.blade.php ENDPATH**/ ?>