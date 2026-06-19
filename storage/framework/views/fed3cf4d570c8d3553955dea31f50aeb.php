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
        .prazzu-admin-page{display:grid;gap:20px}.prazzu-hero{border-radius:24px;padding:24px;background:linear-gradient(135deg,#111827,#1f2937);color:#fff}.prazzu-hero h1{font-size:28px;font-weight:800;margin:0}.prazzu-hero p{margin:8px 0 0;color:#d1d5db;max-width:920px}.prazzu-grid{display:grid;gap:16px}.prazzu-grid.two{grid-template-columns:repeat(2,minmax(0,1fr))}.prazzu-grid.four{grid-template-columns:repeat(4,minmax(0,1fr))}.prazzu-card{border:1px solid #e5e7eb;border-radius:20px;background:#fff;padding:18px;box-shadow:0 8px 24px rgba(15,23,42,.06)}.prazzu-card h2{font-size:18px;font-weight:800;margin:0}.prazzu-card p{color:#64748b;margin:6px 0 0}.prazzu-input,.prazzu-select,.prazzu-textarea{width:100%;border:1px solid #d1d5db;border-radius:12px;padding:10px 12px;background:#fff}.prazzu-textarea{min-height:86px}.prazzu-form-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}.prazzu-checks{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin-top:12px}.prazzu-check{border:1px solid #e5e7eb;border-radius:14px;padding:12px}.prazzu-button{border:0;border-radius:12px;padding:10px 14px;background:#111827;color:#fff;font-weight:700;cursor:pointer}.prazzu-button.on{background:#16a34a}.prazzu-button.off{background:#b91c1c}.prazzu-rule{display:flex;justify-content:space-between;gap:12px;align-items:flex-start}.prazzu-muted{color:#64748b;font-size:12px}.prazzu-badge{display:inline-flex;border-radius:999px;background:#eef2ff;color:#3730a3;padding:4px 10px;font-size:12px;font-weight:700}.prazzu-table-wrap{overflow:auto}.prazzu-table{width:100%;border-collapse:collapse}.prazzu-table th,.prazzu-table td{padding:12px;border-bottom:1px solid #e5e7eb;text-align:left;vertical-align:top}.prazzu-table th{font-size:12px;text-transform:uppercase;color:#64748b}.prazzu-empty{padding:18px;text-align:center;color:#64748b}.prazzu-permission-checks{display:grid;gap:10px;margin-top:14px}.prazzu-permission-checks div{position:relative;border:1px solid #e5e7eb;border-radius:16px;padding:12px 88px 12px 12px;background:#f8fafc}.prazzu-permission-checks div.ok{border-color:#bbf7d0;background:#f0fdf4}.prazzu-permission-checks div.warn{border-color:#fde68a;background:#fffbeb}.prazzu-permission-checks strong{display:block;color:#0f172a}.prazzu-permission-checks span{display:block;margin-top:4px;color:#64748b;font-size:12px}.prazzu-permission-checks em{position:absolute;right:12px;top:12px;border-radius:999px;padding:4px 8px;background:#111827;color:#fff;font-size:11px;font-style:normal;font-weight:800}.prazzu-form-grid{grid-template-columns:repeat(3,minmax(0,1fr))}@media(max-width:1100px){.prazzu-grid.two,.prazzu-grid.four,.prazzu-form-grid,.prazzu-checks{grid-template-columns:1fr}.prazzu-rule{display:block}.prazzu-rule button{margin-top:12px}}
    </style>

    <div class="prazzu-admin-page">
        <section class="prazzu-hero">
            <h1>Permissões Avançadas</h1>
            <p>Controle funcional de cargos personalizados, exclusão, exportação, visibilidade Private/Public e gestão centralizada de tags/status. Esta aba grava regras reais nas tabelas prazzu_roles, prazzu_permission_rules e prazzu_permissions.</p>
        </section>

        <section class="prazzu-card">
            <h2>Criar cargo personalizado</h2>
            <p>Crie perfis como Estagiário, Visualizador Externo, Cliente, Auditor ou Freelancer com travas específicas.</p>

            <form wire:submit.prevent="createCustomRole" style="margin-top:14px">
                <div class="prazzu-form-grid">
                    <label>
                        <span class="prazzu-muted">Nome do cargo</span>
                        <input class="prazzu-input" type="text" wire:model.defer="roleName" placeholder="Ex.: Estagiário">
                    </label>
                    <label>
                        <span class="prazzu-muted">Módulo</span>
                        <select class="prazzu-select" wire:model.defer="roleModule">
                            <option value="Operação">Operação</option>
                            <option value="Documentos">Documentos</option>
                            <option value="CRM">CRM</option>
                            <option value="Auditoria">Auditoria</option>
                            <option value="Permissões">Permissões</option>
                            <option value="Anexos">Anexos</option>
                        </select>
                    </label>
                    <label>
                        <span class="prazzu-muted">Escopo</span>
                        <select class="prazzu-select" wire:model.defer="roleScope">
                            <option value="empresa">Empresa inteira</option>
                            <option value="equipe">Somente equipe</option>
                            <option value="responsável/equipe">Responsável/equipe</option>
                            <option value="compartilhado">Somente compartilhados</option>
                            <option value="somente_leitura">Somente leitura</option>
                        </select>
                    </label>
                </div>
                <label style="display:block;margin-top:12px">
                    <span class="prazzu-muted">Descrição</span>
                    <textarea class="prazzu-textarea" wire:model.defer="roleDescription" placeholder="Descreva quando este cargo deve ser usado."></textarea>
                </label>
                <div class="prazzu-checks">
                    <label class="prazzu-check"><input type="checkbox" wire:model.defer="canView"> Visualizar</label>
                    <label class="prazzu-check"><input type="checkbox" wire:model.defer="canCreate"> Criar</label>
                    <label class="prazzu-check"><input type="checkbox" wire:model.defer="canUpdate"> Editar</label>
                    <label class="prazzu-check"><input type="checkbox" wire:model.defer="canDelete"> Excluir</label>
                </div>
                <button class="prazzu-button" type="submit" style="margin-top:14px">Salvar cargo e permissões</button>
            </form>
        </section>

        <section class="prazzu-grid four">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $cards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <article class="prazzu-card prazzu-rule" <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'security-card-'.e($key).''; ?>wire:key="security-card-<?php echo e($key); ?>">
                    <div>
                        <h2><?php echo e($card['title']); ?></h2>
                        <p><?php echo e($card['description']); ?></p>
                        <span class="prazzu-badge" style="margin-top:10px"><?php echo e($securityRules[$key] ? $card['on'] : $card['off']); ?></span>
                    </div>
                    <button class="prazzu-button <?php echo e($securityRules[$key] ? 'on' : 'off'); ?>" type="button" wire:click="toggleSecurityRule('<?php echo e($key); ?>')">
                        <?php echo e($securityRules[$key] ? 'Ativo' : 'Inativo'); ?>

                    </button>
                </article>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </section>

        <section class="prazzu-grid two">
            <article class="prazzu-card">
                <h2>Validação de segurança</h2>
                <p>Resumo prático para garantir menu, ações, rotas, upload e tenant cobertos por regras reais.</p>
                <div class="prazzu-permission-checks">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $permissionChecklist; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $check): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="<?php echo e($check['ok'] ? 'ok' : 'warn'); ?>">
                            <strong><?php echo e($check['label']); ?></strong>
                            <span><?php echo e($check['hint']); ?></span>
                            <em><?php echo e($check['ok'] ? 'Configurado' : 'Revisar'); ?></em>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            </article>

            <article class="prazzu-card">
                <h2>Permissões por módulo</h2>
                <p>Mapa operacional para enxergar onde cada módulo já possui regra de acesso.</p>
                <div class="prazzu-table-wrap" style="margin-top:14px">
                    <table class="prazzu-table">
                        <thead><tr><th>Módulo</th><th>Ver</th><th>Criar</th><th>Editar</th><th>Excluir</th></tr></thead>
                        <tbody>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $moduleSummary; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <tr>
                                    <td><strong><?php echo e($module->module); ?></strong></td>
                                    <td><?php echo e((int) $module->view_total); ?></td>
                                    <td><?php echo e((int) $module->create_total); ?></td>
                                    <td><?php echo e((int) $module->update_total); ?></td>
                                    <td><?php echo e((int) $module->delete_total); ?></td>
                                </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <tr><td colspan="5" class="prazzu-empty">Nenhuma regra por módulo cadastrada.</td></tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </article>
        </section>

        <section class="prazzu-grid two">
            <article class="prazzu-card">
                <h2>Cargos personalizados cadastrados</h2>
                <p>Lista real de perfis disponíveis para governança de acesso.</p>
                <div class="prazzu-table-wrap" style="margin-top:14px">
                    <table class="prazzu-table">
                        <thead><tr><th>Cargo</th><th>Descrição</th><th>Status</th></tr></thead>
                        <tbody>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <tr>
                                    <td><strong><?php echo e($role->name); ?></strong></td>
                                    <td><?php echo e($role->description ?? '-'); ?></td>
                                    <td><span class="prazzu-badge"><?php echo e($role->active ? 'Ativo' : 'Inativo'); ?></span></td>
                                </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <tr><td colspan="3" class="prazzu-empty">Nenhum cargo cadastrado.</td></tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </article>

            <article class="prazzu-card">
                <h2>Matriz de permissões</h2>
                <p>Regras de visualização, criação, edição e exclusão por perfil.</p>
                <div class="prazzu-table-wrap" style="margin-top:14px">
                    <table class="prazzu-table">
                        <thead><tr><th>Perfil</th><th>Módulo</th><th>Permissões</th><th>Escopo</th></tr></thead>
                        <tbody>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $permissionRules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <tr>
                                    <td><strong><?php echo e($rule->role); ?></strong></td>
                                    <td><?php echo e($rule->module); ?></td>
                                    <td>
                                        <span class="prazzu-badge">Ver: <?php echo e($rule->can_view ? 'sim' : 'não'); ?></span>
                                        <span class="prazzu-badge">Criar: <?php echo e($rule->can_create ? 'sim' : 'não'); ?></span>
                                        <span class="prazzu-badge">Editar: <?php echo e($rule->can_update ? 'sim' : 'não'); ?></span>
                                        <span class="prazzu-badge">Excluir: <?php echo e($rule->can_delete ? 'sim' : 'não'); ?></span>
                                    </td>
                                    <td><?php echo e($rule->scope ?? '-'); ?></td>
                                </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <tr><td colspan="4" class="prazzu-empty">Nenhuma regra cadastrada.</td></tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </article>
        </section>

        <section class="prazzu-card">
            <h2>Permissões sensíveis gravadas</h2>
            <p>Estas regras são usadas para travar ações críticas: exclusão, exportação, visibilidade e gestão de workflow.</p>
            <div class="prazzu-table-wrap" style="margin-top:14px">
                <table class="prazzu-table">
                    <thead><tr><th>Módulo</th><th>Ação</th><th>Escopo</th><th>Descrição</th></tr></thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $sensitivePermissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <tr>
                                <td><?php echo e($permission->module); ?></td>
                                <td><span class="prazzu-badge"><?php echo e($permission->action); ?></span></td>
                                <td><?php echo e($permission->scope ?? '-'); ?></td>
                                <td><?php echo e($permission->name ?? '-'); ?></td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <tr><td colspan="4" class="prazzu-empty">Nenhuma permissão sensível ativa.</td></tr>
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