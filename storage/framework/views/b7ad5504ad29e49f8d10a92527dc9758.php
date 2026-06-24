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
        .prazzu-admin-page{display:grid;gap:20px}.prazzu-hero{border-radius:24px;padding:24px;background:linear-gradient(135deg,#111827,#1f2937);color:#fff}.prazzu-hero h1{font-size:28px;font-weight:900;margin:0}.prazzu-hero p{margin:8px 0 0;color:#d1d5db;max-width:900px}.prazzu-grid{display:grid;gap:16px}.prazzu-grid.two{grid-template-columns:repeat(2,minmax(0,1fr))}.prazzu-card{border:1px solid #e5e7eb;border-radius:20px;background:#fff;padding:18px;box-shadow:0 8px 24px rgba(15,23,42,.06)}.prazzu-card h2{font-size:18px;font-weight:900;margin:0;color:#111827}.prazzu-card p{color:#64748b;margin:6px 0 0}.prazzu-form{display:grid;gap:12px;margin-top:14px}.prazzu-field span{display:block;color:#64748b;font-size:12px;font-weight:800;margin-bottom:6px}.prazzu-input,.prazzu-select{width:100%;border:1px solid #d1d5db;border-radius:12px;padding:10px 12px;background:#fff}.prazzu-button{display:inline-flex;align-items:center;justify-content:center;border:0;border-radius:12px;padding:10px 14px;background:#111827;color:#fff;font-weight:800;cursor:pointer}.prazzu-button.light{background:#f3f4f6;color:#111827}.prazzu-table-wrap{overflow:auto}.prazzu-table{width:100%;border-collapse:collapse}.prazzu-table th,.prazzu-table td{padding:12px;border-bottom:1px solid #e5e7eb;text-align:left;vertical-align:top}.prazzu-table th{font-size:12px;text-transform:uppercase;color:#64748b}.prazzu-badge{display:inline-flex;border-radius:999px;background:#eef2ff;color:#3730a3;padding:4px 10px;font-size:12px;font-weight:800}.prazzu-badge.off{background:#f3f4f6;color:#64748b}.prazzu-muted{color:#64748b;font-size:12px}.prazzu-members{display:flex;gap:8px;flex-wrap:wrap}.prazzu-member{display:inline-flex;gap:8px;align-items:center;border:1px solid #e5e7eb;border-radius:999px;padding:6px 8px}.prazzu-remove{border:0;background:transparent;color:#b91c1c;font-weight:900;cursor:pointer}@media(max-width:960px){.prazzu-grid.two{grid-template-columns:1fr}.prazzu-table{min-width:760px}}
    </style>

    <div class="prazzu-admin-page">
        <section class="prazzu-hero">
            <h1>Equipes</h1>
            <p>Centraliza os grupos internos do escritório e o vínculo entre usuários e equipes. Isso prepara permissões por escopo de equipe e facilita separar operação, financeiro, atendimento e gestão.</p>
        </section>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $this->tabelasDisponiveis()): ?>
            <section class="prazzu-card">
                <h2>Estrutura de equipes indisponível</h2>
                <p>As tabelas <strong>prazzu_teams</strong> e <strong>prazzu_team_user</strong> não foram encontradas neste banco.</p>
            </section>
        <?php else: ?>
            <section class="prazzu-grid two">
                <article class="prazzu-card">
                    <h2>Criar equipe</h2>
                    <p>Use para organizar pessoas por área ou responsabilidade.</p>
                    <div class="prazzu-form">
                        <label class="prazzu-field"><span>Nome</span><input class="prazzu-input" wire:model.defer="name" placeholder="Ex: Fiscal, Departamento Pessoal, Financeiro"></label>
                        <label class="prazzu-field"><span>Descrição</span><input class="prazzu-input" wire:model.defer="description" placeholder="Resumo da função da equipe"></label>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->podeEditar()): ?>
                            <button class="prazzu-button" type="button" wire:click="criarEquipe">Criar equipe</button>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </article>

                <article class="prazzu-card">
                    <h2>Vincular usuário</h2>
                    <p>Adicione uma pessoa em uma equipe sem sair da Central Administrativa.</p>
                    <div class="prazzu-form">
                        <label class="prazzu-field">
                            <span>Equipe</span>
                            <select class="prazzu-select" wire:model.defer="teamId">
                                <option value="">Selecione</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->equipes(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $team): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <option value="<?php echo e($team['id']); ?>"><?php echo e($team['name']); ?></option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </select>
                        </label>
                        <label class="prazzu-field">
                            <span>Usuário</span>
                            <select class="prazzu-select" wire:model.defer="userId">
                                <option value="">Selecione</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->usuariosDisponiveis(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <option value="<?php echo e($id); ?>"><?php echo e($label); ?></option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </select>
                        </label>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->podeEditar()): ?>
                            <button class="prazzu-button" type="button" wire:click="vincularUsuario">Vincular usuário</button>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </article>
            </section>

            <?php $membrosPorEquipe = $this->membrosPorEquipe(); ?>
            <section class="prazzu-card">
                <h2>Equipes cadastradas</h2>
                <p>Visão única de equipes, status e membros vinculados.</p>
                <div class="prazzu-table-wrap" style="margin-top:14px">
                    <table class="prazzu-table">
                        <thead>
                            <tr>
                                <th>Equipe</th>
                                <th>Status</th>
                                <th>Membros</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $this->equipes(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $team): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <tr <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'team-row-'.e($team['id']).''; ?>wire:key="team-row-<?php echo e($team['id']); ?>">
                                    <td><strong><?php echo e($team['name']); ?></strong><br><span class="prazzu-muted"><?php echo e($team['description'] ?: 'Sem descrição'); ?></span></td>
                                    <td><span class="prazzu-badge <?php echo e($team['active'] ? '' : 'off'); ?>"><?php echo e($team['active'] ? 'Ativa' : 'Inativa'); ?></span><br><span class="prazzu-muted"><?php echo e($team['users_count']); ?> membro(s)</span></td>
                                    <td>
                                        <div class="prazzu-members">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_2 = true; $__currentLoopData = ($membrosPorEquipe[$team['id']] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                                <span class="prazzu-member">
                                                    <?php echo e($member['name']); ?>

                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->podeEditar()): ?>
                                                        <button class="prazzu-remove" type="button" wire:click="removerVinculo(<?php echo e($team['id']); ?>, <?php echo e($member['id']); ?>)" title="Remover vínculo">×</button>
                                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                </span>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                                <span class="prazzu-muted">Nenhum usuário vinculado</span>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->podeEditar()): ?>
                                            <button class="prazzu-button light" type="button" wire:click="alternarEquipe(<?php echo e($team['id']); ?>)"><?php echo e($team['active'] ? 'Inativar' : 'Ativar'); ?></button>
                                        <?php else: ?>
                                            <span class="prazzu-muted">Somente leitura</span>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <tr><td colspan="4" class="prazzu-muted">Nenhuma equipe cadastrada.</td></tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
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
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views/filament/pages/equipes.blade.php ENDPATH**/ ?>