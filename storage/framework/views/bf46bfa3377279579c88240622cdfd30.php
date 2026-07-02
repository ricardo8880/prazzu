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
        $permissoesAvancadas = $advancedPermissions ?? [];
        $configuracao = $config ?? [];
    ?>

    <div class="prazzu80-page">
        <section class="prazzu80-hero">
            <div>
                <span class="prazzu80-kicker"><?php echo e($configuracao['group'] ?? 'CONFIGURAÇÕES'); ?></span>
                <h1><?php echo e($configuracao['title'] ?? 'Permissões Avançadas'); ?></h1>
                <p><?php echo e($configuracao['subtitle'] ?? 'Segurança avançada para cargos personalizados, exclusão, exportação, visibilidade, tags e status.'); ?></p>
            </div>
            <div class="prazzu80-hero-actions">
                <span>Criar cargo personalizado</span>
                <span>Bloquear exclusão</span>
                <span>Bloquear exportação</span>
                <span>Definir visibilidade</span>
                <span>Controlar tags/status</span>
            </div>
        </section>

        <section class="prazzu80-search-card">
            <div>
                <strong>Segurança e governança dos dados</strong>
                <p>Defina quem pode visualizar, criar, editar, excluir, exportar e administrar padrões de workflow dentro da empresa.</p>
                <small>Filtros rápidos: Cargo · Módulo · Ação · Escopo · Exclusão · Exportação · Tags/Status</small>
            </div>
            <div class="prazzu80-search-fake">⌕ Buscar regra</div>
        </section>

        <section class="prazzu80-card">
            <header>
                <div>
                    <h2>Controles avançados</h2>
                    <p>Conteúdo focado em Permissões Avançadas: custom roles, exclusão, exportação, privacidade e workflow.</p>
                </div>
            </header>
            <div class="prazzu80-feature-grid">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($permissoesAvancadas['cards'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <div class="prazzu80-feature <?php echo e(($card['status'] ?? '') === 'Configurado' ? 'ok' : 'todo'); ?>">
                        <strong><?php echo e($card['title']); ?></strong>
                        <span><?php echo e($card['description']); ?></span>
                        <span><b>Status:</b> <?php echo e($card['status']); ?></span>
                    </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
        </section>

        <section class="prazzu80-grid two">
            <article class="prazzu80-card">
                <header>
                    <div>
                        <h2>Cargos personalizados (Custom Roles)</h2>
                        <p>Perfis reutilizáveis para aplicar travas específicas em usuários internos, convidados e visualizadores externos.</p>
                    </div>
                </header>
                <div class="prazzu80-list compact">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($permissoesAvancadas['roles'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="prazzu80-note">
                            <strong><?php echo e($role['name'] ?? 'Cargo'); ?></strong>
                            <p><?php echo e($role['description'] ?? 'Sem descrição cadastrada.'); ?></p>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="prazzu80-empty">Nenhum cargo cadastrado. Execute o SQL enviado para criar prazzu_roles.</div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </article>

            <article class="prazzu80-card">
                <header>
                    <div>
                        <h2>Ações sensíveis</h2>
                        <p>Regras explícitas para exclusão, exportação, visibilidade padrão e gestão de tags/status.</p>
                    </div>
                </header>
                <div class="prazzu80-list compact">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($permissoesAvancadas['permissions'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="prazzu80-list-row">
                            <div>
                                <strong><?php echo e($permission['area'] ?? 'Permissão'); ?></strong>
                                <span><?php echo e($permission['level'] ?? 'Sem escopo definido'); ?></span>
                            </div>
                            <em>ACL</em>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="prazzu80-empty">Nenhuma permissão explícita cadastrada.</div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </article>
        </section>

        <section class="prazzu80-card">
            <header>
                <div>
                    <h2>Matriz de permissões por cargo</h2>
                    <p>Controle por módulo, ação e escopo para evitar exclusões indevidas, exportações indevidas e bagunça no workflow.</p>
                </div>
            </header>
            <div class="prazzu80-table-wrap">
                <table class="prazzu80-table">
                    <thead>
                        <tr>
                            <th>Cargo</th>
                            <th>Módulo</th>
                            <th>Visualizar</th>
                            <th>Criar</th>
                            <th>Editar</th>
                            <th>Excluir</th>
                            <th>Escopo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($permissoesAvancadas['rules'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <tr>
                                <td><strong><?php echo e($rule['role'] ?? '-'); ?></strong></td>
                                <td><?php echo e($rule['module'] ?? '-'); ?></td>
                                <td><?php echo e((bool) ($rule['can_view'] ?? false) ? 'Sim' : 'Não'); ?></td>
                                <td><?php echo e((bool) ($rule['can_create'] ?? false) ? 'Sim' : 'Não'); ?></td>
                                <td><?php echo e((bool) ($rule['can_update'] ?? false) ? 'Sim' : 'Não'); ?></td>
                                <td class="<?php echo e((bool) ($rule['can_delete'] ?? false) ? '' : 'danger'); ?>"><?php echo e((bool) ($rule['can_delete'] ?? false) ? 'Sim' : 'Não'); ?></td>
                                <td><?php echo e($rule['scope'] ?? 'empresa'); ?></td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <tr>
                                <td colspan="7">Nenhuma regra encontrada. Execute o SQL enviado para criar prazzu_permission_rules.</td>
                            </tr>
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
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views\filament\pages\permissoes.blade.php ENDPATH**/ ?>