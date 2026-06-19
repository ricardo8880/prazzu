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
        .prazzu-perm-page{display:grid;gap:20px}.prazzu-hero{border-radius:24px;padding:24px;background:linear-gradient(135deg,#0f172a,#1e293b);color:#fff}.prazzu-hero h1{margin:0;font-size:28px;font-weight:900}.prazzu-hero p{margin:8px 0 0;color:#cbd5e1;max-width:980px}.prazzu-grid{display:grid;gap:16px}.prazzu-grid.two{grid-template-columns:1.1fr .9fr}.prazzu-grid.three{grid-template-columns:repeat(3,minmax(0,1fr))}.prazzu-card{border:1px solid #e5e7eb;border-radius:20px;background:#fff;padding:18px;box-shadow:0 8px 24px rgba(15,23,42,.06)}.prazzu-card h2{font-size:18px;font-weight:850;margin:0}.prazzu-card p{color:#64748b;margin:6px 0 0}.prazzu-input,.prazzu-select,.prazzu-textarea{width:100%;border:1px solid #d1d5db;border-radius:12px;padding:10px 12px;background:#fff}.prazzu-textarea{min-height:78px}.prazzu-form-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}.prazzu-button{border:0;border-radius:12px;padding:10px 14px;background:#111827;color:#fff;font-weight:800;cursor:pointer}.prazzu-button.secondary{background:#334155}.prazzu-button.danger{background:#b91c1c}.prazzu-muted{color:#64748b;font-size:12px}.prazzu-badge{display:inline-flex;align-items:center;gap:4px;border-radius:999px;background:#eef2ff;color:#3730a3;padding:4px 10px;font-size:12px;font-weight:800}.prazzu-badge.green{background:#dcfce7;color:#166534}.prazzu-badge.red{background:#fee2e2;color:#991b1b}.prazzu-table-wrap{overflow:auto}.prazzu-table{width:100%;border-collapse:collapse}.prazzu-table th,.prazzu-table td{padding:12px;border-bottom:1px solid #e5e7eb;text-align:left;vertical-align:middle}.prazzu-table th{font-size:12px;text-transform:uppercase;color:#64748b}.prazzu-empty{padding:18px;text-align:center;color:#64748b}.prazzu-role-list{display:grid;gap:10px}.prazzu-role-item{display:grid;grid-template-columns:1fr auto;gap:10px;border:1px solid #e5e7eb;border-radius:16px;padding:12px;background:#f8fafc}.prazzu-role-item.active{border-color:#bfdbfe;background:#eff6ff}.prazzu-progress{height:8px;background:#e5e7eb;border-radius:999px;overflow:hidden;margin-top:8px}.prazzu-progress span{display:block;height:100%;background:#111827}.prazzu-matrix{width:100%;border-collapse:separate;border-spacing:0}.prazzu-matrix th,.prazzu-matrix td{border-bottom:1px solid #e5e7eb;padding:11px;text-align:center}.prazzu-matrix th:first-child,.prazzu-matrix td:first-child{text-align:left;position:sticky;left:0;background:#fff}.prazzu-check-cell label{display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;border:1px solid #d1d5db;border-radius:10px;background:#f8fafc}.prazzu-check-cell input{width:16px;height:16px}.prazzu-readiness{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:10px}.prazzu-readiness div{border:1px solid #e5e7eb;border-radius:14px;padding:12px;background:#f8fafc}.prazzu-readiness .ok{border-color:#bbf7d0;background:#f0fdf4}.prazzu-readiness .warn{border-color:#fde68a;background:#fffbeb}@media(max-width:1100px){.prazzu-grid.two,.prazzu-grid.three,.prazzu-form-grid,.prazzu-readiness{grid-template-columns:1fr}.prazzu-matrix th:first-child,.prazzu-matrix td:first-child{position:static}}
    </style>

    <div class="prazzu-perm-page">
        <section class="prazzu-hero">
            <h1>Perfis e Permissões</h1>
            <p>Controle avançado além do perfil simples: defina o que cada perfil pode ver, criar, editar, excluir, aprovar, cancelar, responder, encerrar, exportar e reatribuir. Este lote cria a interface de configuração; a aplicação prática nas telas vem no lote 3.</p>
        </section>

        <section class="prazzu-readiness">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $readiness; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <div class="<?php echo e($item['ok'] ? 'ok' : 'warn'); ?>">
                    <strong><?php echo e($item['label']); ?></strong><br>
                    <span class="prazzu-muted"><?php echo e($item['ok'] ? 'Pronto' : 'Pendente'); ?></span>
                </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </section>

        <section class="prazzu-grid two">
            <article class="prazzu-card">
                <h2>Criar ou atualizar perfil de acesso</h2>
                <p>Use para perfis como Gerente, Supervisor, Analista, Assistente, Cliente, Auditor ou qualquer perfil interno do escritório.</p>
                <form wire:submit.prevent="createRole" style="margin-top:14px">
                    <div class="prazzu-form-grid">
                        <label>
                            <span class="prazzu-muted">Nome do perfil</span>
                            <input class="prazzu-input" type="text" wire:model.defer="roleName" placeholder="Ex.: Supervisor Fiscal">
                        </label>
                        <label>
                            <span class="prazzu-muted">Status</span>
                            <select class="prazzu-select" wire:model.defer="roleActive">
                                <option value="1">Ativo</option>
                                <option value="0">Inativo</option>
                            </select>
                        </label>
                    </div>
                    <label style="display:block;margin-top:12px">
                        <span class="prazzu-muted">Descrição</span>
                        <textarea class="prazzu-textarea" wire:model.defer="roleDescription" placeholder="Explique quando esse perfil deve ser usado."></textarea>
                    </label>
                    <button class="prazzu-button" type="submit" style="margin-top:14px">Salvar perfil</button>
                </form>
            </article>

            <article class="prazzu-card">
                <h2>Perfis cadastrados</h2>
                <p>Selecione um perfil para editar a matriz de permissões.</p>
                <div class="prazzu-role-list" style="margin-top:14px">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php ($stats = $roleStats[$role->id] ?? ['active' => 0, 'total' => 0, 'percent' => 0]); ?>
                        <div class="prazzu-role-item <?php echo e((int) $selectedRoleId === (int) $role->id ? 'active' : ''); ?>" <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'role-'.e($role->id).''; ?>wire:key="role-<?php echo e($role->id); ?>">
                            <label style="cursor:pointer">
                                <input type="radio" wire:model.live="selectedRoleId" value="<?php echo e($role->id); ?>">
                                <strong><?php echo e($role->name); ?></strong>
                                <span class="prazzu-badge <?php echo e($role->active ? 'green' : 'red'); ?>"><?php echo e($role->active ? 'Ativo' : 'Inativo'); ?></span>
                                <p><?php echo e($role->description ?: 'Sem descrição.'); ?></p>
                                <div class="prazzu-progress"><span style="width: <?php echo e($stats['percent']); ?>%"></span></div>
                                <span class="prazzu-muted"><?php echo e($stats['active']); ?> de <?php echo e($stats['total']); ?> permissões marcadas</span>
                            </label>
                            <button class="prazzu-button secondary" type="button" wire:click="toggleRoleStatus(<?php echo e($role->id); ?>)">
                                <?php echo e($role->active ? 'Desativar' : 'Ativar'); ?>

                            </button>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="prazzu-empty">Nenhum perfil cadastrado.</div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </article>
        </section>

        <section class="prazzu-card">
            <h2>Matriz de permissões por perfil</h2>
            <p>Perfil selecionado: <strong><?php echo e($selectedRole?->name ?? 'nenhum'); ?></strong>. Marque somente o que esse perfil pode fazer.</p>
            <div class="prazzu-table-wrap" style="margin-top:14px">
                <table class="prazzu-matrix">
                    <thead>
                        <tr>
                            <th>Módulo</th>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $actions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $actionKey => $actionLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <th><?php echo e($actionLabel); ?></th>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $matrix; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $moduleKey => $availableActions): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <tr <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'matrix-row-'.e($moduleKey).''; ?>wire:key="matrix-row-<?php echo e($moduleKey); ?>">
                                <td><strong><?php echo e($modules[$moduleKey] ?? $moduleKey); ?></strong><br><span class="prazzu-muted"><?php echo e($moduleKey); ?></span></td>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $actions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $actionKey => $actionLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <td class="prazzu-check-cell">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array($actionKey, $availableActions, true)): ?>
                                            <label title="<?php echo e($modules[$moduleKey] ?? $moduleKey); ?>: <?php echo e($actionLabel); ?>">
                                                <input type="checkbox" wire:model.defer="rolePermissions.<?php echo e($moduleKey); ?>.<?php echo e($actionKey); ?>">
                                            </label>
                                        <?php else: ?>
                                            <span class="prazzu-muted">—</span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </tbody>
                </table>
            </div>
            <button class="prazzu-button" type="button" wire:click="saveRolePermissions" style="margin-top:14px">Salvar matriz do perfil</button>
        </section>

        <section class="prazzu-grid two">
            <article class="prazzu-card">
                <h2>Vincular perfil ao usuário</h2>
                <p>Permite dizer que um usuário usa um ou mais perfis avançados.</p>
                <div class="prazzu-form-grid" style="margin-top:14px">
                    <label>
                        <span class="prazzu-muted">Usuário</span>
                        <select class="prazzu-select" wire:model.live="selectedUserId">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <option value="<?php echo e($user->id); ?>"><?php echo e($user->name); ?> — <?php echo e($user->email); ?></option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </select>
                    </label>
                    <label>
                        <span class="prazzu-muted">Perfil</span>
                        <select class="prazzu-select" wire:model.defer="assignRoleId">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <option value="<?php echo e($role->id); ?>"><?php echo e($role->name); ?></option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </select>
                    </label>
                </div>
                <button class="prazzu-button" type="button" wire:click="assignRoleToUser" style="margin-top:14px">Vincular perfil</button>

                <div class="prazzu-table-wrap" style="margin-top:14px">
                    <table class="prazzu-table">
                        <thead><tr><th>Perfil vinculado</th><th>Status</th><th>Ação</th></tr></thead>
                        <tbody>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $userRoles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $userRole): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <tr>
                                    <td><strong><?php echo e($userRole->role?->name ?? 'Perfil removido'); ?></strong></td>
                                    <td><span class="prazzu-badge <?php echo e($userRole->role?->active ? 'green' : 'red'); ?>"><?php echo e($userRole->role?->active ? 'Ativo' : 'Inativo'); ?></span></td>
                                    <td><button class="prazzu-button danger" type="button" wire:click="removeUserRole(<?php echo e($userRole->id); ?>)">Remover</button></td>
                                </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <tr><td colspan="3" class="prazzu-empty">Usuário sem perfil avançado vinculado.</td></tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </article>

            <article class="prazzu-card">
                <h2>Exceção individual</h2>
                <p>Use para liberar ou bloquear uma ação específica para um usuário, mesmo que o perfil diga outra coisa.</p>
                <div class="prazzu-form-grid" style="margin-top:14px">
                    <label>
                        <span class="prazzu-muted">Módulo</span>
                        <select class="prazzu-select" wire:model.live="overrideModule">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $modules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $moduleKey => $moduleLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <option value="<?php echo e($moduleKey); ?>"><?php echo e($moduleLabel); ?></option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </select>
                    </label>
                    <label>
                        <span class="prazzu-muted">Ação</span>
                        <select class="prazzu-select" wire:model.defer="overrideAction">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $actions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $actionKey => $actionLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <option value="<?php echo e($actionKey); ?>"><?php echo e($actionLabel); ?></option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </select>
                    </label>
                    <label>
                        <span class="prazzu-muted">Escopo</span>
                        <select class="prazzu-select" wire:model.defer="overrideScope">
                            <option value="empresa">Empresa</option>
                            <option value="all">Global</option>
                            <option value="proprio">Próprio usuário</option>
                            <option value="equipe">Equipe</option>
                        </select>
                    </label>
                    <label>
                        <span class="prazzu-muted">Tipo</span>
                        <select class="prazzu-select" wire:model.defer="overrideAllowed">
                            <option value="1">Permitir</option>
                            <option value="0">Bloquear</option>
                        </select>
                    </label>
                </div>
                <label style="display:block;margin-top:12px">
                    <span class="prazzu-muted">Motivo</span>
                    <input class="prazzu-input" type="text" wire:model.defer="overrideReason" placeholder="Ex.: liberação temporária para fechamento mensal">
                </label>
                <button class="prazzu-button" type="button" wire:click="saveUserOverride" style="margin-top:14px">Salvar exceção</button>

                <div class="prazzu-table-wrap" style="margin-top:14px">
                    <table class="prazzu-table">
                        <thead><tr><th>Permissão</th><th>Tipo</th><th>Motivo</th><th>Ação</th></tr></thead>
                        <tbody>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $userOverrides; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $override): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <tr>
                                    <td><strong><?php echo e($modules[$override->module] ?? $override->module); ?></strong><br><span class="prazzu-muted"><?php echo e($override->module); ?>.<?php echo e($override->action); ?> / <?php echo e($override->scope); ?></span></td>
                                    <td><span class="prazzu-badge <?php echo e($override->allowed ? 'green' : 'red'); ?>"><?php echo e($override->allowed ? 'Permitir' : 'Bloquear'); ?></span></td>
                                    <td><?php echo e($override->reason ?? '-'); ?></td>
                                    <td><button class="prazzu-button danger" type="button" wire:click="removeUserOverride(<?php echo e($override->id); ?>)">Remover</button></td>
                                </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <tr><td colspan="4" class="prazzu-empty">Nenhuma exceção individual para o usuário selecionado.</td></tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </article>
        </section>


        <section class="prazzu-card">
            <h2>Relatório efetivo do usuário</h2>
            <p>Mostra o resultado final após somar perfil avançado, exceções individuais e fallback do sistema.</p>
            <div class="prazzu-table-wrap" style="margin-top:14px">
                <table class="prazzu-table">
                    <thead><tr><th>Módulo</th><th>Ação</th><th>Resultado</th><th>Origem</th><th>Perfis</th></tr></thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $effectivePermissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <tr>
                                <td><strong><?php echo e($modules[$row['module']] ?? $row['module']); ?></strong><br><span class="prazzu-muted"><?php echo e($row['module']); ?></span></td>
                                <td><?php echo e($actions[$row['action']] ?? $row['action']); ?></td>
                                <td><span class="prazzu-badge <?php echo e($row['allowed'] ? 'green' : 'red'); ?>"><?php echo e($row['allowed'] ? 'Permitido' : 'Bloqueado'); ?></span></td>
                                <td><?php echo e($row['source']); ?></td>
                                <td><?php echo e($row['roles']); ?></td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <tr><td colspan="5" class="prazzu-empty">Selecione um usuário para ver o relatório efetivo.</td></tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="prazzu-card">
            <h2>Auditoria de permissões</h2>
            <p>Registra quem criou perfil, alterou matriz, vinculou perfil e criou/removeu exceções individuais.</p>
            <div class="prazzu-table-wrap" style="margin-top:14px">
                <table class="prazzu-table">
                    <thead><tr><th>Data</th><th>Evento</th><th>Autor</th><th>Usuário alvo</th><th>Perfil</th><th>Permissão</th><th>Motivo</th></tr></thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $permissionAudits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $audit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <tr>
                                <td><?php echo e($audit->created_at?->format('d/m/Y H:i')); ?></td>
                                <td><strong><?php echo e($audit->event_label); ?></strong><br><span class="prazzu-muted"><?php echo e($audit->event); ?></span></td>
                                <td><?php echo e($audit->actor?->name ?? '-'); ?></td>
                                <td><?php echo e($audit->targetUser?->name ?? '-'); ?></td>
                                <td><?php echo e($audit->role?->name ?? '-'); ?></td>
                                <td><?php echo e($audit->module ? $audit->module . '.' . $audit->action : '-'); ?></td>
                                <td><?php echo e($audit->reason ?? '-'); ?></td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <tr><td colspan="7" class="prazzu-empty">Nenhum evento de permissão registrado ainda.</td></tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="prazzu-card">
            <h2>Regras antigas sincronizadas</h2>
            <p>Compatibilidade com <code>prazzu_permission_rules</code>. A matriz nova salva também atualiza Ver, Criar, Editar e Excluir nessa tabela quando existir.</p>
            <div class="prazzu-table-wrap" style="margin-top:14px">
                <table class="prazzu-table">
                    <thead><tr><th>Perfil</th><th>Módulo</th><th>Ver</th><th>Criar</th><th>Editar</th><th>Excluir</th><th>Escopo</th></tr></thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $legacyRules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <tr>
                                <td><strong><?php echo e($rule->role); ?></strong></td>
                                <td><?php echo e($rule->module); ?></td>
                                <td><?php echo e($rule->can_view ? 'Sim' : 'Não'); ?></td>
                                <td><?php echo e($rule->can_create ? 'Sim' : 'Não'); ?></td>
                                <td><?php echo e($rule->can_update ? 'Sim' : 'Não'); ?></td>
                                <td><?php echo e($rule->can_delete ? 'Sim' : 'Não'); ?></td>
                                <td><?php echo e($rule->scope); ?></td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <tr><td colspan="7" class="prazzu-empty">Nenhuma regra antiga encontrada.</td></tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
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
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views/filament/pages/permissoes-management.blade.php ENDPATH**/ ?>