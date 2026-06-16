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

    <link rel="stylesheet" href="<?php echo e(asset('css/atendimentos.css')); ?>">
    <?php if (! $__env->hasRenderedOnce('23b66a9d-26e8-4587-929b-798be19cfbd0')): $__env->markAsRenderedOnce('23b66a9d-26e8-4587-929b-798be19cfbd0'); ?>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <?php endif; ?>

    <?php
        $ready = (bool) ($ready ?? false);
        $summary = $summary ?? [];
        $statusBoard = $statusBoard ?? [];
        $prioridadeResumo = $prioridadeResumo ?? [];
        $atendimentos = $atendimentos ?? [];
        $empresas = $empresas ?? [];
        $clientes = $clientes ?? [];
        $responsaveis = $responsaveis ?? [];
        $statusOptions = $statusOptions ?? [];
        $prioridadeOptions = $prioridadeOptions ?? [];
        $slaOptions = $slaOptions ?? ['todos' => 'Todos'];
        $clientesDaEmpresa = collect($clientes)->when($novoEmpresaId, fn ($c) => $c->where('empresa_id', (int) $novoEmpresaId))->values();
        $idsAtendimentosVisiveis = collect($atendimentos)->pluck('id')->map(fn ($id) => (string) $id)->values();
        $idsAtendimentosSelecionados = collect($atendimentosSelecionados ?? [])->map(fn ($id) => (string) $id)->values();
        $todosAtendimentosVisiveisSelecionados = $idsAtendimentosVisiveis->isNotEmpty() && $idsAtendimentosVisiveis->diff($idsAtendimentosSelecionados)->isEmpty();
    ?>

    <div class="at-wrap at-reference-layout" x-data="{ criar: <?php if ((object) ('createModalAberto') instanceof \Livewire\WireDirective) : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('createModalAberto'->value()); ?>')<?php echo e('createModalAberto'->hasModifier('live') ? '.live' : ''); ?><?php else : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('createModalAberto'); ?>')<?php endif; ?>.live, detalhe: <?php if ((object) ('detailModalAberto') instanceof \Livewire\WireDirective) : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('detailModalAberto'->value()); ?>')<?php echo e('detailModalAberto'->hasModifier('live') ? '.live' : ''); ?><?php else : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('detailModalAberto'); ?>')<?php endif; ?>.live }" wire:poll.25s="loadData(true)">
        <section class="at-page-head at-reference-head">
            <div class="at-title-block">
                <span class="at-eyebrow">Central de suporte</span>
                <h1>Atendimentos</h1>
                <p>Gerencie e acompanhe todos os atendimentos da sua equipe</p>
            </div>

            <div class="at-head-actions">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($lastRefreshAt): ?>
                    <small class="at-last-sync">Atualizado em <?php echo e($lastRefreshAt); ?></small>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <button type="button" class="at-btn ghost" wire:click="sincronizarPortal" wire:loading.attr="disabled" wire:target="sincronizarPortal"><i class="bi bi-arrow-clockwise at-btn-icon" aria-hidden="true"></i> Atualizar</button>
                <button type="button" class="at-btn" wire:click="abrirCriacao" wire:loading.attr="disabled" wire:target="abrirCriacao"><i class="bi bi-plus-lg at-btn-icon" aria-hidden="true"></i> Novo atendimento</button>
            </div>
        </section>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (! ($ready)): ?>
            <section class="at-alert danger">
                <strong>Módulo aguardando banco de dados.</strong>
                <span>Execute o arquivo <code>sql/lote1_atendimentos_base.sql</code> no seu banco atual para liberar a central de atendimentos.</span>
            </section>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <section class="at-kpis at-kpis-saas at-reference-metrics">
            <button type="button" wire:click="filtrarFilaAtiva" class="at-kpi-button at-kpi-focus">
                <span class="at-kpi-icon danger"><i class="bi bi-inbox-fill" aria-hidden="true"></i></span>
                <strong><?php echo e($summary['abertos'] ?? 0); ?></strong>
                <span>Abertos</span>
                <small>Precisam de atenção</small>
            </button>
            <button type="button" wire:click="filtrarStatus('aguardando_cliente')" class="at-kpi-button at-kpi-focus warning">
                <span class="at-kpi-icon warning"><i class="bi bi-hourglass-split" aria-hidden="true"></i></span>
                <strong><?php echo e($summary['aguardando_cliente'] ?? 0); ?></strong>
                <span>Aguardando cliente</span>
                <small>Resposta pendente</small>
            </button>
            <button type="button" wire:click="filtrarStatus('em_andamento')" class="at-kpi-button at-kpi-focus primary">
                <span class="at-kpi-icon primary"><i class="bi bi-chat-dots-fill" aria-hidden="true"></i></span>
                <strong><?php echo e($summary['em_andamento'] ?? 0); ?></strong>
                <span>Em andamento</span>
                <small>Atendendo agora</small>
            </button>
            <button type="button" wire:click="filtrarStatus('resolvido')" class="at-kpi-button at-kpi-focus success">
                <span class="at-kpi-icon success"><i class="bi bi-check2-circle" aria-hidden="true"></i></span>
                <strong><?php echo e($summary['resolvidos_hoje'] ?? ($summary['resolvidos'] ?? 0)); ?></strong>
                <span>Resolvidos hoje</span>
                <small>Ótimo trabalho!</small>
            </button>
        </section>

        <section class="at-workspace at-reference-workspace">
            <div class="at-main-column at-reference-main">
                <section class="at-filter-bar">
                    <label class="at-search-field">
                        <span>Buscar atendimento</span>
                        <input type="search" wire:model.live.debounce.450ms="search" placeholder="Buscar por cliente, assunto ou protocolo...">
                    </label>

                    <details class="at-filter-drawer">
                        <summary>Filtros <strong><?php echo e((($statusFilter ?? 'todos') !== 'todos' ? 1 : 0) + (($prioridadeFilter ?? 'todos') !== 'todos' ? 1 : 0) + (($slaFilter ?? 'todos') !== 'todos' ? 1 : 0) + (($responsavelFilter ?? 'todos') !== 'todos' ? 1 : 0) + (!empty($empresaFilter) ? 1 : 0) + (($origemFilter ?? 'todos') !== 'todos' ? 1 : 0)); ?></strong></summary>
                        <div class="at-filter-grid">
                            <label><span>Status</span><select wire:model.live="statusFilter"><option value="todos">Todos</option><option value="ativos">Fila ativa</option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $statusOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $meta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?><option value="<?php echo e($key); ?>"><?php echo e($meta['label']); ?></option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?></select></label>
                            <label><span>Prioridade</span><select wire:model.live="prioridadeFilter"><option value="todos">Todas</option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $prioridadeOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $meta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?><option value="<?php echo e($key); ?>"><?php echo e($meta['label']); ?></option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?></select></label>
                            <label><span>SLA</span><select wire:model.live="slaFilter"><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $slaOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?><option value="<?php echo e($key); ?>"><?php echo e($label); ?></option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?></select></label>
                            <label><span>Responsável</span><select wire:model.live="responsavelFilter"><option value="todos">Todos</option><option value="sem_responsavel">Sem responsável</option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $responsaveis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $resp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?><option value="<?php echo e($resp['id']); ?>"><?php echo e($resp['nome']); ?></option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?></select></label>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($empresas) > 1): ?>
                                <label><span>Empresa</span><select wire:model.live="empresaFilter"><option value="">Todas</option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $empresas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $empresa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?><option value="<?php echo e($empresa['id']); ?>"><?php echo e($empresa['nome']); ?></option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?></select></label>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <label><span>Origem</span><select wire:model.live="origemFilter"><option value="todos">Todas</option><option value="manual">Manual</option><option value="portal">Portal</option><option value="whatsapp">WhatsApp</option><option value="email">E-mail</option><option value="telefone">Telefone</option></select></label>
                            <label><span>Ordenar</span><select wire:model.live="sortBy"><option value="recentes">Atualização</option><option value="sla">SLA mais próximo</option><option value="prioridade">Prioridade</option><option value="cliente">Cliente</option></select></label>
                        </div>
                    </details>

                    <button type="button" class="at-clear-link" wire:click="resetarFiltros" wire:loading.attr="disabled" wire:target="resetarFiltros">Limpar filtros</button>
                </section>

                <article class="at-card at-table-card at-queue-card">
                    <header class="at-list-head at-reference-list-head">
                        <div>
                            <h2>Atendimentos recentes</h2>
                            <p><?php echo e(count($atendimentos)); ?> atendimento(s) na fila. Priorize quem precisa de resposta agora.</p>
                        </div>
                        <div class="at-list-pills" aria-label="Resumo rápido da fila">
                            <span><strong><?php echo e($summary['abertos'] ?? 0); ?></strong> abertos</span>
                            <span><strong><?php echo e($summary['em_andamento'] ?? 0); ?></strong> em andamento</span>
                            <span><strong><?php echo e($summary['aguardando_cliente'] ?? 0); ?></strong> aguardando</span>
                        </div>
                    </header>

                    <div class="at-table-wrap at-queue-wrap">
                        <table class="at-table at-queue-table">
                            <colgroup>
                                <col class="at-col-select">
                                <col class="at-col-atendimento">
                                <col class="at-col-cliente">
                                <col class="at-col-prioridade">
                                <col class="at-col-status">
                                <col class="at-col-atualizacao">
                                <col class="at-col-acoes">
                            </colgroup>
                            <thead>
                                <tr>
                                    <th class="at-select-col"><input type="checkbox" class="at-fake-checkbox" aria-label="Selecionar todos os atendimentos visíveis" wire:click="alternarSelecaoVisivel" <?php if($todosAtendimentosVisiveisSelecionados): echo 'checked'; endif; ?>></th>
                                    <th>Atendimento</th>
                                    <th>Cliente</th>
                                    <th>Prioridade</th>
                                    <th>Status</th>
                                    <th>Atualização</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $atendimentos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <tr <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'atendimento-row-'.e($item['id']).''; ?>wire:key="atendimento-row-<?php echo e($item['id']); ?>" class="at-ticket-row <?php echo e($item['sla_vencido'] ? 'is-late' : ''); ?> tone-<?php echo e($item['prioridade_tone'] ?? 'neutral'); ?>">
                                        <td class="at-select-col" data-label=""><input type="checkbox" class="at-fake-checkbox" aria-label="Selecionar atendimento #<?php echo e($item['id']); ?>" value="<?php echo e($item['id']); ?>" wire:model.live="atendimentosSelecionados"></td>
                                        <td class="at-ticket-cell" data-label="Atendimento">
                                            <div class="at-ticket-content">
                                                <span class="at-ticket-icon <?php echo e($item['sla_vencido'] ? 'danger' : ($item['prioridade_tone'] ?? 'neutral')); ?>">
                                                    <i class="bi <?php echo e($item['sla_vencido'] ? 'bi-exclamation-lg' : 'bi-chat-dots-fill'); ?>" aria-hidden="true"></i>
                                                </span>
                                                <div class="at-ticket-main">
                                                    <div class="at-ticket-title-row">
                                                        <strong><span class="at-ticket-id">#<?php echo e($item['id']); ?> - </span><?php echo e($item['titulo']); ?></strong>
                                                    </div>
                                                    <small><?php echo e(\Illuminate\Support\Str::limit($item['descricao'], 92)); ?></small>
                                                    <div class="at-ticket-meta-line">
                                                        <span><?php echo e($item['origem_label'] ?? ucfirst($item['origem'] ?? 'Manual')); ?></span>
                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($item['portal_solicitacao_id']) || !empty($item['portal_mensagem_id'])): ?>
                                                            <span class="at-source-chip">Portal</span>
                                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $item['responsavel_id']): ?>
                                                            <span class="at-source-chip warning">Sem responsável</span>
                                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="at-client-cell" data-label="Cliente">
                                            <div class="at-client-content">
                                                <span class="at-client-avatar" title="<?php echo e($item['empresa_nome'] ?? 'Cliente'); ?>"><?php echo e(mb_strtoupper(mb_substr(trim($item['empresa_nome'] ?? 'Cliente'), 0, 1))); ?></span>
                                                <div class="at-client-main">
                                                    <strong><?php echo e($item['empresa_nome']); ?></strong>
                                                    <small><?php echo e($item['empresa_email'] ?? $item['cliente_email'] ?? 'Sem e-mail'); ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td data-label="Prioridade"><span class="at-badge at-badge-dot <?php echo e($item['prioridade_tone']); ?>"><?php echo e($item['prioridade_label']); ?></span></td>
                                        <td data-label="Status"><span class="at-badge at-badge-soft <?php echo e($item['status_tone']); ?>"><?php echo e($item['status_label']); ?></span></td>
                                        <td class="at-time-cell" data-label="Atualização">
                                            <strong><?php echo e($item['updated_at']); ?></strong>
                                            <small class="<?php echo e($item['sla_vencido'] ? 'danger' : ''); ?>"><?php echo e($item['sla_texto']); ?></small>
                                        </td>
                                        <td class="at-actions-cell" data-label="Ações">
                                            <div class="at-actions at-row-actions">
                                                <button type="button" class="at-open-btn" wire:click="selecionarAtendimento(<?php echo e($item['id']); ?>)">Abrir</button>
                                                <details class="at-more-menu">
                                                    <summary class="at-more-btn" aria-label="Mais ações"><i class="bi bi-three-dots" aria-hidden="true"></i></summary>
                                                    <div class="at-more-menu-panel">
                                                        <button type="button" wire:click="selecionarAtendimento(<?php echo e($item['id']); ?>)"><i class="bi bi-eye" aria-hidden="true"></i> Ver detalhes</button>
                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $item['responsavel_id']): ?>
                                                            <button type="button" wire:click="assumirAtendimento(<?php echo e($item['id']); ?>)"><i class="bi bi-person-check" aria-hidden="true"></i> Assumir</button>
                                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                    </div>
                                                </details>
                                            </div>
                                        </td>
                                    </tr>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    <tr><td colspan="7"><div class="at-empty">Nenhum atendimento encontrado com os filtros atuais.</div></td></tr>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </article>
            </div>

            <aside class="at-side-panel at-reference-aside">
                <article class="at-side-card at-filter-panel-card">
                    <header class="at-filter-panel-head">
                        <div>
                            <h3>Filtros rápidos</h3>
                        </div>
                        <span class="at-filter-icon"><i class="bi bi-sliders2" aria-hidden="true"></i></span>
                    </header>

                    <details class="at-filter-section" open>
                        <summary class="at-filter-section-title">
                            <span>Status</span>
                            <span class="at-section-chevron"><i class="bi bi-chevron-down" aria-hidden="true"></i></span>
                        </summary>
                        <div class="at-filter-section-body">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $statusBoard; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $statusItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <button type="button" class="at-filter-check-row <?php echo e(($statusFilter === $statusItem['key']) ? 'active' : ''); ?>" wire:click="filtrarStatus('<?php echo e($statusItem['key']); ?>')">
                                    <span class="at-check-box"><i class="bi bi-check-lg" aria-hidden="true"></i></span>
                                    <span class="at-dot <?php echo e($statusItem['tone']); ?>"></span>
                                    <span><?php echo e($statusItem['label']); ?></span>
                                    <strong><?php echo e($statusItem['total']); ?></strong>
                                </button>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    </details>

                    <details class="at-filter-section">
                        <summary class="at-filter-section-title">
                            <span>Prioridade</span>
                            <span class="at-section-chevron"><i class="bi bi-chevron-down" aria-hidden="true"></i></span>
                        </summary>
                        <div class="at-filter-section-body">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $prioridadeResumo; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $prioridadeItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <button type="button" class="at-filter-check-row <?php echo e(($prioridadeFilter === $prioridadeItem['key']) ? 'active' : ''); ?>" wire:click="$set('prioridadeFilter', '<?php echo e($prioridadeItem['key']); ?>')">
                                    <span class="at-check-box"><i class="bi bi-check-lg" aria-hidden="true"></i></span>
                                    <span class="at-dot <?php echo e($prioridadeItem['tone'] ?? 'neutral'); ?>"></span>
                                    <span><?php echo e($prioridadeItem['label']); ?></span>
                                    <strong><?php echo e($prioridadeItem['total']); ?></strong>
                                </button>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    </details>

                    <details class="at-filter-section">
                        <summary class="at-filter-section-title">
                            <span>Responsável</span>
                            <span class="at-section-chevron"><i class="bi bi-chevron-down" aria-hidden="true"></i></span>
                        </summary>
                        <div class="at-filter-section-body">
                            <label class="at-side-select"><span>Filtrar responsável</span><select wire:model.live="responsavelFilter"><option value="todos">Todos</option><option value="sem_responsavel">Sem responsável</option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $responsaveis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $resp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?><option value="<?php echo e($resp['id']); ?>"><?php echo e($resp['nome']); ?></option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?></select></label>
                        </div>
                    </details>

                    <details class="at-filter-section">
                        <summary class="at-filter-section-title">
                            <span>Origem</span>
                            <span class="at-section-chevron"><i class="bi bi-chevron-down" aria-hidden="true"></i></span>
                        </summary>
                        <div class="at-filter-section-body">
                            <label class="at-side-select"><span>Canal de entrada</span><select wire:model.live="origemFilter"><option value="todos">Todas</option><option value="manual">Manual</option><option value="portal">Portal</option><option value="whatsapp">WhatsApp</option><option value="email">E-mail</option><option value="telefone">Telefone</option></select></label>
                        </div>
                    </details>

                    <details class="at-filter-section">
                        <summary class="at-filter-section-title">
                            <span>SLA</span>
                            <span class="at-section-chevron"><i class="bi bi-chevron-down" aria-hidden="true"></i></span>
                        </summary>
                        <div class="at-filter-section-body">
                            <label class="at-side-select"><span>Prazo</span><select wire:model.live="slaFilter"><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $slaOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?><option value="<?php echo e($key); ?>"><?php echo e($label); ?></option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?></select></label>
                        </div>
                    </details>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($empresas) > 1): ?>
                        <details class="at-filter-section">
                            <summary class="at-filter-section-title">
                                <span>Empresa</span>
                                <span class="at-section-chevron"><i class="bi bi-chevron-down" aria-hidden="true"></i></span>
                            </summary>
                            <div class="at-filter-section-body">
                                <label class="at-side-select"><span>Empresa</span><select wire:model.live="empresaFilter"><option value="">Todas</option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $empresas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $empresa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?><option value="<?php echo e($empresa['id']); ?>"><?php echo e($empresa['nome']); ?></option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?></select></label>
                            </div>
                        </details>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <button type="button" class="at-clear-filters-wide" wire:click="resetarFiltros" wire:loading.attr="disabled" wire:target="resetarFiltros"><i class="bi bi-x-lg" aria-hidden="true"></i> Limpar todos os filtros</button>
                </article>

                <article class="at-side-card at-tip-card at-reference-tip-card">
                    <strong><i class="bi bi-lightbulb-fill" aria-hidden="true"></i> Dica</strong>
                    <p>Use os filtros para encontrar rapidamente o que precisa.</p>
                </article>
            </aside>
        </section>

        <div class="at-modal" x-show="criar" x-cloak>
            <div class="at-modal-card" @click.outside="criar = false">
                <header><div><h2>Novo atendimento</h2><p>Registre uma demanda interna vinculada ao cliente/empresa.</p></div><button type="button" @click="criar = false">×</button></header>
                <div class="at-form-grid">
                    <label><span>Empresa</span><select wire:model.live="novoEmpresaId"><option value="">Selecione</option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $empresas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $empresa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?><option value="<?php echo e($empresa['id']); ?>"><?php echo e($empresa['nome']); ?></option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?></select></label>
                    <label><span>Cliente CRM</span><select wire:model="novoClienteId"><option value="">Opcional</option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $clientesDaEmpresa; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cliente): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?><option value="<?php echo e($cliente['id']); ?>"><?php echo e($cliente['nome']); ?></option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?></select></label>
                    <label><span>Responsável</span><select wire:model="novoResponsavelId"><option value="">Sem responsável</option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $responsaveis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $resp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?><option value="<?php echo e($resp['id']); ?>"><?php echo e($resp['nome']); ?></option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?></select></label>
                    <label><span>Prioridade</span><select wire:model="novoPrioridade"><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $prioridadeOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $meta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?><option value="<?php echo e($key); ?>"><?php echo e($meta['label']); ?> · SLA <?php echo e($meta['sla'] ?? '-'); ?>h</option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?></select></label>
                    <label><span>Origem</span><select wire:model="novoOrigem"><option value="manual">Manual</option><option value="portal">Portal</option><option value="whatsapp">WhatsApp</option><option value="email">E-mail</option><option value="telefone">Telefone</option></select></label>
                    <label><span>Canal</span><select wire:model="novoCanal"><option value="interno">Interno</option><option value="portal">Portal</option><option value="whatsapp">WhatsApp</option><option value="email">E-mail</option><option value="telefone">Telefone</option></select></label>
                </div>
                <label class="at-full"><span>Título</span><input type="text" wire:model="novoTitulo" maxlength="180" placeholder="Ex.: Cliente solicitou ajuste no contrato"></label>
                <label class="at-full"><span>Descrição</span><textarea wire:model="novoDescricao" rows="5" placeholder="Descreva o contexto, o que precisa ser feito e qualquer detalhe importante."></textarea></label>
                <footer><button type="button" class="at-btn ghost" @click="criar = false">Cancelar</button><button type="button" class="at-btn" wire:click="criarAtendimento" wire:loading.attr="disabled" wire:target="criarAtendimento">Criar atendimento</button></footer>
            </div>
        </div>

        <style>
            .at-ticket-modal-shell {
                width: min(1480px, calc(100vw - 2rem));
                max-height: min(92vh, 960px);
                padding: 0;
                border-radius: 1.2rem;
                overflow: hidden;
                background: #ffffff;
                box-shadow: 0 32px 90px rgba(15, 23, 42, .30);
            }

            .at-ticket-modal-head {
                display: grid;
                grid-template-columns: minmax(0, 1fr) auto;
                gap: 1rem;
                align-items: center;
                padding: 1.05rem 1.35rem;
                border-bottom: 1px solid #e5e7eb;
                background: rgba(255,255,255,.98);
            }

            .at-ticket-modal-title {
                display: grid;
                grid-template-columns: 42px minmax(0, 1fr);
                gap: .75rem;
                align-items: center;
                min-width: 0;
            }

            .at-ticket-modal-title-icon,
            .at-ticket-person-avatar,
            .at-ticket-chat-avatar {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 999px;
                font-weight: 950;
            }

            .at-ticket-modal-title-icon {
                width: 40px;
                height: 40px;
                background: #eef2ff;
                color: #4f46e5;
                font-size: 1.1rem;
            }

            .at-ticket-modal-title h2 {
                display: flex;
                align-items: center;
                gap: .55rem;
                flex-wrap: wrap;
                margin: 0;
                color: #0f172a;
                font-size: 1.16rem;
                line-height: 1.25;
                font-weight: 950;
                letter-spacing: -.025em;
            }

            .at-ticket-modal-title p {
                margin: .12rem 0 0;
                color: #64748b;
                font-size: .82rem;
                font-weight: 750;
            }

            .at-ticket-modal-actions {
                display: flex;
                align-items: center;
                gap: .55rem;
                justify-content: flex-end;
            }

            .at-ticket-control,
            .at-ticket-icon-btn,
            .at-ticket-side-btn,
            .at-ticket-quick-btn,
            .at-ticket-send-btn,
            .at-ticket-attachment-btn {
                border: 1px solid #e2e8f0;
                border-radius: .72rem;
                background: #fff;
                color: #334155;
                font-weight: 900;
                cursor: pointer;
                transition: .16s ease;
            }

            .at-ticket-control:hover,
            .at-ticket-icon-btn:hover,
            .at-ticket-side-btn:hover,
            .at-ticket-quick-btn:hover,
            .at-ticket-attachment-btn:hover {
                border-color: #bfdbfe;
                background: #f8fbff;
                color: #1d4ed8;
            }

            .at-ticket-control {
                position: relative;
            }

            .at-ticket-control summary {
                display: inline-flex;
                align-items: center;
                gap: .45rem;
                min-height: 38px;
                padding: .55rem .75rem;
                list-style: none;
                cursor: pointer;
            }

            .at-ticket-control summary::-webkit-details-marker { display: none; }

            .at-ticket-control-panel {
                position: absolute;
                top: calc(100% + .5rem);
                right: 0;
                z-index: 5;
                display: grid;
                gap: .35rem;
                min-width: 230px;
                padding: .55rem;
                border: 1px solid #e2e8f0;
                border-radius: .85rem;
                background: #fff;
                box-shadow: 0 22px 55px rgba(15, 23, 42, .18);
            }

            .at-ticket-control-panel button {
                display: flex;
                align-items: center;
                gap: .5rem;
                width: 100%;
                border: 0;
                border-radius: .65rem;
                padding: .62rem .7rem;
                background: transparent;
                color: #334155;
                font-weight: 850;
                text-align: left;
                cursor: pointer;
            }

            .at-ticket-control-panel button:hover { background: #f1f5ff; color: #312e81; }
            .at-ticket-control-panel button.danger:hover { background: #fff1f2; color: #dc2626; }

            .at-ticket-icon-btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 38px;
                height: 38px;
                padding: 0;
                font-size: 1.1rem;
            }

            .at-ticket-modal-body {
                display: grid;
                grid-template-columns: 280px minmax(420px, 1fr) 320px;
                gap: 1rem;
                height: calc(min(92vh, 960px) - 75px);
                padding: 1rem;
                overflow: hidden;
                background: #ffffff;
            }

            .at-ticket-left,
            .at-ticket-center,
            .at-ticket-right {
                min-height: 0;
                min-width: 0;
            }

            .at-ticket-left,
            .at-ticket-right {
                display: grid;
                gap: .9rem;
                align-content: start;
                overflow: auto;
                padding-right: .15rem;
            }

            .at-ticket-center {
                display: grid;
                grid-template-rows: auto minmax(0, 1fr) auto;
                border: 1px solid #e5e7eb;
                border-radius: .95rem;
                overflow: hidden;
                background: #fff;
            }

            .at-ticket-panel {
                border: 1px solid #e5e7eb;
                border-radius: .95rem;
                background: #fff;
                overflow: hidden;
            }

            .at-ticket-panel-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: .75rem;
                min-height: 43px;
                padding: .78rem .9rem;
                border-bottom: 1px solid #e5e7eb;
                color: #0f172a;
                font-size: .78rem;
                font-weight: 950;
                text-transform: uppercase;
                letter-spacing: .045em;
            }

            .at-ticket-detail-list,
            .at-ticket-status-list {
                display: grid;
                gap: .1rem;
                padding: .75rem .85rem;
            }

            .at-ticket-detail-row {
                display: grid;
                grid-template-columns: 22px minmax(0, .85fr) minmax(0, 1fr);
                gap: .55rem;
                align-items: center;
                min-height: 35px;
                color: #64748b;
                font-size: .78rem;
                font-weight: 800;
            }

            .at-ticket-detail-row i { color: #64748b; font-size: .9rem; }
            .at-ticket-detail-row strong {
                min-width: 0;
                color: #0f172a;
                font-size: .78rem;
                font-weight: 900;
                text-align: right;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .at-ticket-sla-ok { color: #16a34a !important; }
            .at-ticket-sla-danger { color: #dc2626 !important; }

            .at-ticket-attachments {
                display: grid;
                gap: .55rem;
                padding: .8rem;
            }

            .at-ticket-file-row {
                display: grid;
                grid-template-columns: 34px minmax(0, 1fr) 34px;
                gap: .5rem;
                align-items: center;
                padding: .55rem;
                border: 1px solid #e5e7eb;
                border-radius: .75rem;
                background: #fbfdff;
            }

            .at-ticket-file-row > span:first-child {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 34px;
                height: 34px;
                border-radius: .65rem;
                background: #eef2ff;
                color: #4f46e5;
                font-size: 1.05rem;
            }

            .at-ticket-file-row strong,
            .at-ticket-file-row small {
                display: block;
                min-width: 0;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .at-ticket-file-row strong { color: #0f172a; font-size: .78rem; font-weight: 900; }
            .at-ticket-file-row small { color: #64748b; font-size: .72rem; font-weight: 750; margin-top: .1rem; }

            .at-ticket-download-btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 34px;
                height: 34px;
                border: 1px solid #e2e8f0;
                border-radius: .65rem;
                background: #fff;
                color: #64748b;
                cursor: pointer;
            }

            .at-ticket-conversation-head {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: .75rem;
                padding: .82rem 1rem;
                border-bottom: 1px solid #e5e7eb;
                color: #0f172a;
                font-size: .78rem;
                font-weight: 950;
                text-transform: uppercase;
                letter-spacing: .045em;
            }

            .at-ticket-order-select {
                display: inline-flex;
                align-items: center;
                gap: .45rem;
                color: #64748b;
                font-size: .76rem;
                font-weight: 850;
                text-transform: none;
                letter-spacing: 0;
            }

            .at-ticket-chat-scroll {
                position: relative;
                overflow: auto;
                padding: 1rem 1rem .75rem;
            }

            .at-ticket-chat-stream {
                position: relative;
                display: grid;
                gap: .95rem;
                padding-left: 34px;
            }

            .at-ticket-chat-stream::before {
                content: '';
                position: absolute;
                left: 17px;
                top: .15rem;
                bottom: .15rem;
                width: 1px;
                background: #e2e8f0;
            }

            .at-ticket-message {
                position: relative;
                display: grid;
                gap: .35rem;
            }

            .at-ticket-chat-avatar {
                position: absolute;
                left: -34px;
                top: 0;
                width: 34px;
                height: 34px;
                color: #fff;
                font-size: .78rem;
                box-shadow: 0 0 0 4px #fff;
            }

            .at-ticket-chat-avatar.client { background: #0d6fdc; }
            .at-ticket-chat-avatar.support { background: #f2a900; }
            .at-ticket-chat-avatar.system { background: #64748b; }

            .at-ticket-message-card {
                max-width: 92%;
                padding: .78rem .85rem;
                border-radius: .85rem;
                background: #f4f7ff;
                color: #0f172a;
            }

            .at-ticket-message.client .at-ticket-message-card { background: transparent; padding-top: .1rem; }
            .at-ticket-message.system .at-ticket-message-card { background: #f8fafc; border: 1px dashed #cbd5e1; }

            .at-ticket-message-card strong {
                display: block;
                margin-bottom: .28rem;
                color: #0f172a;
                font-size: .86rem;
                font-weight: 950;
            }

            .at-ticket-message-card strong span { color: #64748b; font-weight: 850; }
            .at-ticket-message-card p {
                margin: 0;
                color: #0f172a;
                font-size: .84rem;
                line-height: 1.45;
                white-space: pre-wrap;
                overflow-wrap: anywhere;
            }

            .at-ticket-message-time {
                display: inline-flex;
                align-items: center;
                gap: .4rem;
                color: #64748b;
                font-size: .76rem;
                font-weight: 750;
            }

            .at-ticket-message-files {
                display: flex;
                flex-wrap: wrap;
                gap: .45rem;
                margin-top: .55rem;
            }

            .at-ticket-attachment-btn {
                display: inline-flex;
                align-items: center;
                gap: .35rem;
                min-height: 34px;
                padding: .4rem .65rem;
                font-size: .74rem;
            }

            .at-ticket-reply-box {
                display: grid;
                gap: .65rem;
                padding: .8rem 1rem;
                border-top: 1px solid #e5e7eb;
                background: #fff;
            }

            .at-ticket-reply-box textarea {
                width: 100%;
                min-height: 86px;
                resize: vertical;
                border: 1px solid #dbe3ef;
                border-radius: .8rem;
                padding: .75rem .85rem;
                color: #0f172a;
                outline: none;
            }

            .at-ticket-reply-box textarea:focus {
                border-color: #818cf8;
                box-shadow: 0 0 0 3px rgba(79, 70, 229, .12);
            }

            .at-ticket-reply-actions {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: .75rem;
            }

            .at-ticket-reply-left,
            .at-ticket-reply-right {
                display: flex;
                align-items: center;
                gap: .45rem;
                flex-wrap: wrap;
            }

            .at-ticket-upload-control {
                position: relative;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 36px;
                height: 36px;
                border: 1px solid #e2e8f0;
                border-radius: .72rem;
                background: #fff;
                color: #64748b;
                cursor: pointer;
                overflow: hidden;
            }

            .at-ticket-upload-control input {
                position: absolute;
                inset: 0;
                opacity: 0;
                cursor: pointer;
            }

            .at-ticket-quick-select {
                min-height: 36px;
                border: 1px solid #e2e8f0;
                border-radius: .72rem;
                padding: 0 .7rem;
                background: #fff;
                color: #334155;
                font-size: .78rem;
                font-weight: 850;
            }

            .at-ticket-send-btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: .45rem;
                min-height: 38px;
                padding: .55rem .9rem;
                border-color: #4f46e5;
                background: linear-gradient(135deg, #2563eb, #4f46e5);
                color: #fff;
                box-shadow: 0 12px 24px rgba(37, 99, 235, .22);
            }

            .at-ticket-send-btn:hover { transform: translateY(-1px); }
            .at-ticket-reply-hint { color: #64748b; font-size: .72rem; font-weight: 750; text-align: center; }

            .at-ticket-person-card {
                display: grid;
                grid-template-columns: 42px minmax(0, 1fr);
                gap: .72rem;
                align-items: center;
                padding: 1rem .9rem .75rem;
            }

            .at-ticket-person-avatar {
                width: 42px;
                height: 42px;
                background: #d1fae5;
                color: #059669;
            }

            .at-ticket-person-avatar.support { background: #fef3c7; color: #d97706; }
            .at-ticket-person-card strong,
            .at-ticket-person-card small {
                display: block;
                min-width: 0;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .at-ticket-person-card strong { color: #0f172a; font-size: .9rem; font-weight: 950; }
            .at-ticket-person-card small { color: #64748b; font-size: .78rem; font-weight: 750; margin-top: .14rem; }

            .at-ticket-side-btn {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: .45rem;
                width: calc(100% - 1.8rem);
                min-height: 38px;
                margin: 0 .9rem .9rem;
                padding: .5rem .75rem;
                font-size: .78rem;
            }

            .at-ticket-quick-list {
                display: grid;
                gap: .45rem;
                padding: .75rem .8rem .8rem;
            }

            .at-ticket-quick-btn {
                display: flex;
                align-items: center;
                gap: .55rem;
                width: 100%;
                min-height: 34px;
                padding: .47rem .65rem;
                text-align: left;
                font-size: .78rem;
            }

            .at-ticket-quick-btn.danger { border-color: #fecaca; color: #dc2626; }
            .at-ticket-quick-btn.danger:hover { background: #fff1f2; color: #dc2626; }

            .at-ticket-status-item {
                display: grid;
                grid-template-columns: 18px minmax(0, 1fr) auto;
                gap: .45rem;
                align-items: center;
                min-height: 27px;
                color: #334155;
                font-size: .78rem;
                font-weight: 800;
            }

            .at-ticket-status-circle {
                width: 11px;
                height: 11px;
                border-radius: 999px;
                border: 2px solid #94a3b8;
                background: #fff;
            }
            .at-ticket-status-circle.info { border-color: #2563eb; background: #2563eb; }
            .at-ticket-status-circle.primary { border-color: #4f46e5; background: #4f46e5; }
            .at-ticket-status-circle.warning { border-color: #f59e0b; background: #f59e0b; }
            .at-ticket-status-circle.success { border-color: #22c55e; background: #22c55e; }
            .at-ticket-status-circle.danger { border-color: #ef4444; background: #ef4444; }

            .at-ticket-status-item small { color: #64748b; font-weight: 750; }

            .at-ticket-finalized {
                margin: .8rem 1rem;
                border-radius: .8rem;
                padding: .85rem;
                background: #f8fafc;
                color: #64748b;
                font-weight: 800;
                text-align: center;
            }

            @media (max-width: 1280px) {
                .at-ticket-modal-body { grid-template-columns: 250px minmax(360px, 1fr) 290px; }
            }

            @media (max-width: 1024px) {
                .at-ticket-modal-shell { max-height: 94vh; }
                .at-ticket-modal-body {
                    grid-template-columns: 1fr;
                    height: auto;
                    max-height: calc(94vh - 75px);
                    overflow: auto;
                }
                .at-ticket-left,
                .at-ticket-right { overflow: visible; }
                .at-ticket-center { min-height: 640px; }
            }

            @media (max-width: 720px) {
                .at-ticket-modal-shell { width: calc(100vw - .75rem); border-radius: 1rem; }
                .at-ticket-modal-head { grid-template-columns: 1fr; padding: .9rem; }
                .at-ticket-modal-actions { justify-content: flex-start; flex-wrap: wrap; }
                .at-ticket-control-panel { right: auto; left: 0; }
                .at-ticket-modal-body { padding: .75rem; }
                .at-ticket-detail-row { grid-template-columns: 20px minmax(0, 1fr); }
                .at-ticket-detail-row strong { grid-column: 2; text-align: left; white-space: normal; }
                .at-ticket-reply-actions { display: grid; }
                .at-ticket-reply-right { justify-content: stretch; }
                .at-ticket-send-btn { width: 100%; }
            }


            /* Lote final - modal Abrir espelhado no modelo aprovado */
            .at-ticket-modal-shell {
                width: min(1460px, calc(100vw - 2rem)) !important;
                max-height: min(94vh, 920px) !important;
                border-radius: 1.1rem !important;
                box-shadow: 0 34px 100px rgba(15, 23, 42, .34) !important;
            }

            .at-ticket-modal-head {
                grid-template-columns: minmax(520px, 1fr) auto !important;
                padding: 1rem 1.25rem !important;
                min-height: 74px !important;
                background: #fff !important;
            }

            .at-ticket-modal-title {
                grid-template-columns: 40px minmax(0, 1fr) !important;
                gap: .75rem !important;
            }

            .at-ticket-modal-title-icon {
                width: 38px !important;
                height: 38px !important;
                background: #eef2ff !important;
                color: #4f46e5 !important;
            }

            .at-ticket-modal-title h2 {
                display: flex !important;
                align-items: center !important;
                gap: .45rem !important;
                margin: 0 !important;
                font-size: 1.18rem !important;
                line-height: 1.18 !important;
                font-weight: 950 !important;
                letter-spacing: -.035em !important;
                white-space: normal !important;
            }

            .at-ticket-modal-title h2 > .at-badge {
                flex: 0 0 auto !important;
                min-height: 26px !important;
                padding: .32rem .62rem !important;
                font-size: .72rem !important;
                border-radius: .48rem !important;
            }

            .at-ticket-modal-title p {
                margin-top: .25rem !important;
                font-size: .79rem !important;
                color: #64748b !important;
            }

            .at-ticket-modal-actions {
                gap: .5rem !important;
                flex-wrap: nowrap !important;
                align-self: start !important;
            }

            .at-ticket-control {
                border-radius: .62rem !important;
                min-width: 142px !important;
                max-width: 172px !important;
            }

            .at-ticket-control summary {
                min-height: 36px !important;
                padding: .5rem .65rem !important;
                gap: .4rem !important;
                font-size: .78rem !important;
                line-height: 1.15 !important;
            }

            .at-ticket-icon-btn {
                width: 36px !important;
                height: 36px !important;
                border-radius: .62rem !important;
            }

            .at-ticket-modal-body {
                grid-template-columns: 292px minmax(520px, 1fr) 330px !important;
                gap: 1rem !important;
                height: calc(min(94vh, 920px) - 74px) !important;
                padding: .95rem 1rem 1rem !important;
                background: #fff !important;
            }

            .at-ticket-left,
            .at-ticket-right {
                gap: .8rem !important;
            }

            .at-ticket-panel {
                border-radius: .88rem !important;
                border-color: #e5e7eb !important;
                background: #fff !important;
            }

            .at-ticket-panel-header,
            .at-ticket-conversation-head {
                min-height: 42px !important;
                padding: .72rem .9rem !important;
                font-size: .75rem !important;
                letter-spacing: .045em !important;
                background: #fff !important;
            }

            .at-ticket-detail-list,
            .at-ticket-status-list {
                padding: .72rem .82rem !important;
                gap: .14rem !important;
            }

            .at-ticket-detail-row {
                grid-template-columns: 20px minmax(0, .82fr) minmax(0, 1fr) !important;
                min-height: 33px !important;
                gap: .5rem !important;
                font-size: .76rem !important;
            }

            .at-ticket-detail-row strong {
                font-size: .76rem !important;
                font-weight: 950 !important;
                color: #111827 !important;
            }

            .at-ticket-attachments {
                padding: .72rem !important;
                gap: .5rem !important;
            }

            .at-ticket-empty-attachments {
                margin: .72rem !important;
                min-height: 90px !important;
                border: 1px dashed #94a3b8 !important;
                border-radius: .9rem !important;
                background: #f8fafc !important;
                color: #64748b !important;
            }

            .at-ticket-center {
                border-radius: .88rem !important;
                grid-template-rows: 42px minmax(0, 1fr) auto !important;
            }

            .at-ticket-chat-scroll {
                padding: .85rem 1rem .65rem !important;
            }

            .at-ticket-chat-stream {
                gap: .85rem !important;
                padding-left: 35px !important;
            }

            .at-ticket-chat-stream::before {
                left: 17px !important;
                background: #e5e7eb !important;
            }

            .at-ticket-chat-avatar {
                left: -35px !important;
                width: 34px !important;
                height: 34px !important;
                box-shadow: 0 0 0 4px #fff !important;
            }

            .at-ticket-message-card {
                max-width: 94% !important;
                border-radius: .85rem !important;
                padding: .78rem .88rem !important;
                background: #f2f5ff !important;
            }

            .at-ticket-message.client .at-ticket-message-card {
                background: transparent !important;
                padding: .08rem .15rem .1rem !important;
            }

            .at-ticket-message.support .at-ticket-message-card {
                background: linear-gradient(180deg, #f2f5ff, #eef3ff) !important;
            }

            .at-ticket-message-card strong {
                font-size: .84rem !important;
                margin-bottom: .25rem !important;
            }

            .at-ticket-message-card p {
                font-size: .82rem !important;
                line-height: 1.42 !important;
            }

            .at-ticket-message-time {
                margin-top: .08rem !important;
                font-size: .74rem !important;
                font-weight: 800 !important;
            }

            .at-ticket-reply-box {
                padding: .78rem 1rem .72rem !important;
                gap: .58rem !important;
                background: #fff !important;
            }

            .at-ticket-reply-box textarea {
                min-height: 84px !important;
                border-radius: .78rem !important;
                font-size: .9rem !important;
            }

            .at-ticket-reply-actions {
                gap: .7rem !important;
            }

            .at-ticket-upload-control,
            .at-ticket-quick-select {
                height: 36px !important;
                border-radius: .65rem !important;
            }

            .at-ticket-quick-select {
                min-width: 190px !important;
                font-size: .76rem !important;
            }

            .at-ticket-send-btn {
                min-width: 138px !important;
                min-height: 40px !important;
                padding: .58rem .9rem !important;
                border-radius: .7rem !important;
                background: linear-gradient(135deg, #2563eb, #4f46e5) !important;
                box-shadow: 0 12px 28px rgba(37, 99, 235, .24) !important;
                color: #fff !important;
                font-size: .82rem !important;
            }

            .at-ticket-reply-hint {
                text-align: center !important;
                color: #64748b !important;
                font-size: .72rem !important;
                font-weight: 800 !important;
            }

            .at-ticket-person-card {
                padding: .95rem .9rem .72rem !important;
                gap: .72rem !important;
            }

            .at-ticket-person-avatar {
                width: 42px !important;
                height: 42px !important;
                background: #bbf7d0 !important;
                color: #059669 !important;
            }

            .at-ticket-person-avatar.support {
                background: #fef3c7 !important;
                color: #d97706 !important;
            }

            .at-ticket-person-card strong {
                font-size: .92rem !important;
                color: #111827 !important;
            }

            .at-ticket-person-card small {
                font-size: .78rem !important;
                color: #64748b !important;
            }

            .at-ticket-side-btn {
                width: calc(100% - 1.6rem) !important;
                min-height: 38px !important;
                margin: 0 .8rem .78rem !important;
                border-radius: .68rem !important;
                font-size: .77rem !important;
            }

            .at-ticket-quick-list {
                padding: .65rem .72rem .72rem !important;
                gap: .44rem !important;
            }

            .at-ticket-quick-btn {
                min-height: 34px !important;
                padding: .47rem .65rem !important;
                border-radius: .65rem !important;
                font-size: .77rem !important;
            }

            .at-ticket-status-item {
                min-height: 27px !important;
                font-size: .76rem !important;
            }

            .at-ticket-left::-webkit-scrollbar,
            .at-ticket-right::-webkit-scrollbar,
            .at-ticket-chat-scroll::-webkit-scrollbar {
                width: 7px;
            }

            .at-ticket-left::-webkit-scrollbar-thumb,
            .at-ticket-right::-webkit-scrollbar-thumb,
            .at-ticket-chat-scroll::-webkit-scrollbar-thumb {
                background: rgba(100,116,139,.26);
                border-radius: 999px;
            }

            @media (max-width: 1280px) {
                .at-ticket-modal-body {
                    grid-template-columns: 276px minmax(430px, 1fr) 316px !important;
                }
                .at-ticket-modal-head {
                    grid-template-columns: minmax(440px, 1fr) auto !important;
                }
                .at-ticket-control { min-width: 132px !important; max-width: 154px !important; }
            }

            @media (max-width: 1024px) {
                .at-ticket-modal-head { grid-template-columns: 1fr !important; }
                .at-ticket-modal-body {
                    grid-template-columns: 1fr !important;
                    height: auto !important;
                    max-height: calc(94vh - 130px) !important;
                    overflow: auto !important;
                }
                .at-ticket-left,
                .at-ticket-right { overflow: visible !important; }
                .at-ticket-center { min-height: 620px !important; }
            }


            /* Ajuste final solicitado - dropdowns compactos e áreas com scroll interno */
            .at-ticket-modal-shell {
                display: flex !important;
                flex-direction: column !important;
                height: min(94vh, 920px) !important;
                max-height: min(94vh, 920px) !important;
                overflow: hidden !important;
            }

            .at-ticket-modal-head {
                flex: 0 0 auto !important;
                overflow: visible !important;
                position: relative !important;
                z-index: 60 !important;
            }

            .at-ticket-modal-body {
                flex: 1 1 auto !important;
                min-height: 0 !important;
                overflow: hidden !important;
            }

            .at-ticket-left,
            .at-ticket-right,
            .at-ticket-center {
                min-height: 0 !important;
                overflow: hidden !important;
            }

            .at-ticket-left,
            .at-ticket-right {
                overflow-y: auto !important;
                padding-right: .12rem !important;
            }

            .at-ticket-panel {
                min-height: 0 !important;
                overflow: hidden !important;
            }

            .at-ticket-detail-list {
                max-height: 348px !important;
                overflow-y: auto !important;
                padding-right: .2rem !important;
            }

            .at-ticket-attachment-list,
            .at-ticket-quick-list,
            .at-ticket-status-list {
                max-height: 220px !important;
                overflow-y: auto !important;
                padding-right: .2rem !important;
            }

            .at-ticket-modal-actions {
                position: relative !important;
                z-index: 80 !important;
            }

            .at-ticket-control {
                position: relative !important;
                flex: 0 0 auto !important;
                width: auto !important;
                min-width: 150px !important;
                max-width: 176px !important;
                overflow: visible !important;
            }

            .at-ticket-control summary {
                display: grid !important;
                grid-template-columns: auto minmax(0, 1fr) auto !important;
                align-items: center !important;
                width: 100% !important;
                white-space: nowrap !important;
                overflow: hidden !important;
                text-overflow: ellipsis !important;
                font-size: .8rem !important;
                line-height: 1 !important;
            }

            .at-ticket-control summary i,
            .at-ticket-control summary svg {
                flex: 0 0 auto !important;
            }

            .at-ticket-control-panel {
                z-index: 120 !important;
                min-width: 238px !important;
                max-width: 280px !important;
                max-height: 290px !important;
                overflow-y: auto !important;
                overscroll-behavior: contain !important;
            }

            .at-ticket-control-panel button {
                min-height: 36px !important;
                font-size: .82rem !important;
                line-height: 1.2 !important;
                white-space: normal !important;
                word-break: normal !important;
            }

            .at-ticket-more-control {
                min-width: 38px !important;
                width: 38px !important;
                max-width: 38px !important;
                border: 0 !important;
                background: transparent !important;
            }

            .at-ticket-more-control .at-ticket-icon-summary {
                display: inline-flex !important;
                width: 38px !important;
                height: 38px !important;
                min-height: 38px !important;
                padding: 0 !important;
                align-items: center !important;
                justify-content: center !important;
                border-radius: .62rem !important;
                background: #f1f5f9 !important;
                color: #334155 !important;
                cursor: pointer !important;
            }

            .at-ticket-more-panel {
                min-width: 220px !important;
            }

            .at-ticket-detail-list::-webkit-scrollbar,
            .at-ticket-attachment-list::-webkit-scrollbar,
            .at-ticket-quick-list::-webkit-scrollbar,
            .at-ticket-status-list::-webkit-scrollbar,
            .at-ticket-control-panel::-webkit-scrollbar {
                width: 7px;
            }

            .at-ticket-detail-list::-webkit-scrollbar-thumb,
            .at-ticket-attachment-list::-webkit-scrollbar-thumb,
            .at-ticket-quick-list::-webkit-scrollbar-thumb,
            .at-ticket-status-list::-webkit-scrollbar-thumb,
            .at-ticket-control-panel::-webkit-scrollbar-thumb {
                background: rgba(100,116,139,.28);
                border-radius: 999px;
            }

            @media (max-width: 1024px) {
                .at-ticket-modal-shell {
                    height: min(94vh, 920px) !important;
                }
                .at-ticket-modal-body {
                    overflow-y: auto !important;
                }
                .at-ticket-left,
                .at-ticket-right,
                .at-ticket-center {
                    overflow: visible !important;
                }
                .at-ticket-detail-list,
                .at-ticket-attachment-list,
                .at-ticket-quick-list,
                .at-ticket-status-list {
                    max-height: none !important;
                    overflow: visible !important;
                }
            }

        </style>

        <div class="at-modal" x-show="detalhe" x-cloak>
            <div class="at-modal-card wide at-ticket-modal-shell" @click.outside="detalhe = false">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedAtendimento): ?>
                    <?php
                        $clienteInicial = mb_strtoupper(mb_substr(trim($selectedAtendimento['empresa_nome'] ?? 'Cliente'), 0, 1));
                        $responsavelNome = $selectedAtendimento['responsavel_nome'] ?? 'Sem responsável';
                        $responsavelInicial = mb_strtoupper(mb_substr(trim($responsavelNome ?: 'S'), 0, 1));
                        $clienteEmail = $selectedAtendimento['empresa_email'] ?? $selectedAtendimento['cliente_email'] ?? 'Sem e-mail';
                        $statusAtual = $selectedAtendimento['status'] ?? 'aberto';
                        $statusFechado = in_array($statusAtual, ['resolvido', 'fechado', 'cancelado'], true);
                        $anexosDoAtendimento = collect($timeline)
                            ->flatMap(fn ($log) => collect($log['anexos'] ?? [])->map(fn ($anexo) => array_merge($anexo, ['log_id' => $log['id'] ?? 0, 'log_data' => $log['created_at'] ?? '-'])))
                            ->values();
                        $primeiroLogCliente = collect($timeline)->first(fn ($log) => in_array(($log['origem'] ?? ''), ['cliente', 'portal', 'publico'], true));
                    ?>

                    <header class="at-ticket-modal-head">
                        <div class="at-ticket-modal-title">
                            <span class="at-ticket-modal-title-icon"><i class="bi bi-chat-dots-fill" aria-hidden="true"></i></span>
                            <div>
                                <h2>
                                    Atendimento #<?php echo e($selectedAtendimento['id']); ?> - <?php echo e($selectedAtendimento['titulo']); ?>

                                    <span class="at-badge at-badge-soft <?php echo e($selectedAtendimento['status_tone'] ?? 'info'); ?>"><?php echo e($selectedAtendimento['status_label'] ?? 'Aberto'); ?></span>
                                    <span class="at-badge at-badge-soft <?php echo e($selectedAtendimento['prioridade_tone'] ?? 'info'); ?>"><?php echo e($selectedAtendimento['prioridade_label'] ?? 'Média'); ?></span>
                                </h2>
                                <p>Criado em <?php echo e($selectedAtendimento['created_at'] ?? '-'); ?></p>
                            </div>
                        </div>

                        <div class="at-ticket-modal-actions">
                            <details class="at-ticket-control">
                                <summary><i class="bi bi-record-circle" aria-hidden="true"></i> Marcar como... <i class="bi bi-chevron-down" aria-hidden="true"></i></summary>
                                <div class="at-ticket-control-panel">
                                    <button type="button" wire:click="mudarStatusRapido(<?php echo e($selectedAtendimento['id']); ?>, 'em_andamento')"><span class="at-dot primary"></span> Em andamento</button>
                                    <button type="button" wire:click="mudarStatusRapido(<?php echo e($selectedAtendimento['id']); ?>, 'aguardando_cliente')"><span class="at-dot warning"></span> Aguardando cliente</button>
                                    <button type="button" wire:click="mudarStatusRapido(<?php echo e($selectedAtendimento['id']); ?>, 'resolvido')"><span class="at-dot success"></span> Resolvido</button>
                                    <button type="button" wire:click="mudarStatusRapido(<?php echo e($selectedAtendimento['id']); ?>, 'fechado')" class="danger"><span class="at-dot danger"></span> Encerrar atendimento</button>
                                </div>
                            </details>

                            <details class="at-ticket-control">
                                <summary><i class="bi bi-person-plus" aria-hidden="true"></i> Atribuir a... <i class="bi bi-chevron-down" aria-hidden="true"></i></summary>
                                <div class="at-ticket-control-panel">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $responsaveis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $resp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <button type="button" wire:click="atribuirResponsavelDetalhe(<?php echo e($resp['id']); ?>)"><i class="bi bi-person" aria-hidden="true"></i> <?php echo e($resp['nome']); ?></button>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(empty($responsaveis)): ?>
                                        <button type="button" disabled><i class="bi bi-info-circle" aria-hidden="true"></i> Nenhum responsável disponível</button>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </details>

                            <details class="at-ticket-control at-ticket-more-control">
                                <summary class="at-ticket-icon-summary" title="Mais opções"><i class="bi bi-three-dots" aria-hidden="true"></i></summary>
                                <div class="at-ticket-control-panel at-ticket-more-panel">
                                    <button type="button" onclick="navigator.clipboard && navigator.clipboard.writeText('#<?php echo e($selectedAtendimento['id']); ?>')"><i class="bi bi-clipboard" aria-hidden="true"></i> Copiar protocolo</button>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $selectedAtendimento['responsavel_id']): ?>
                                        <button type="button" wire:click="assumirAtendimento(<?php echo e($selectedAtendimento['id']); ?>)"><i class="bi bi-person-check" aria-hidden="true"></i> Assumir atendimento</button>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <button type="button" wire:click="reabrirAtendimento(<?php echo e($selectedAtendimento['id']); ?>)"><i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i> Reabrir atendimento</button>
                                    <button type="button" class="danger" wire:click="mudarStatusRapido(<?php echo e($selectedAtendimento['id']); ?>, 'fechado')"><i class="bi bi-x-octagon" aria-hidden="true"></i> Encerrar atendimento</button>
                                </div>
                            </details>
                            <button type="button" class="at-ticket-icon-btn" title="Fechar" @click="detalhe = false"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
                        </div>
                    </header>

                    <div class="at-ticket-modal-body">
                        <aside class="at-ticket-left">
                            <section class="at-ticket-panel">
                                <header class="at-ticket-panel-header">Detalhes do atendimento</header>
                                <div class="at-ticket-detail-list">
                                    <div class="at-ticket-detail-row"><i class="bi bi-file-earmark-text" aria-hidden="true"></i><span>Protocolo</span><strong>#<?php echo e($selectedAtendimento['id']); ?></strong></div>
                                    <div class="at-ticket-detail-row"><i class="bi bi-geo-alt" aria-hidden="true"></i><span>Origem</span><strong><?php echo e($selectedAtendimento['origem_label'] ?? ucfirst($selectedAtendimento['origem'] ?? 'Manual')); ?></strong></div>
                                    <div class="at-ticket-detail-row"><i class="bi bi-list-ul" aria-hidden="true"></i><span>Categoria</span><strong><?php echo e($selectedAtendimento['canal_label'] ?? ucfirst($selectedAtendimento['canal'] ?? 'Interno')); ?></strong></div>
                                    <div class="at-ticket-detail-row"><i class="bi bi-envelope" aria-hidden="true"></i><span>Assunto</span><strong><?php echo e($selectedAtendimento['titulo'] ?? '-'); ?></strong></div>
                                    <div class="at-ticket-detail-row"><i class="bi bi-stars" aria-hidden="true"></i><span>Prioridade</span><strong><?php echo e($selectedAtendimento['prioridade_label'] ?? '-'); ?></strong></div>
                                    <div class="at-ticket-detail-row"><i class="bi bi-arrow-repeat" aria-hidden="true"></i><span>Status</span><strong><?php echo e($selectedAtendimento['status_label'] ?? '-'); ?></strong></div>
                                    <div class="at-ticket-detail-row"><i class="bi bi-clock" aria-hidden="true"></i><span>SLA</span><strong class="<?php echo e(!empty($selectedAtendimento['sla_vencido']) ? 'at-ticket-sla-danger' : 'at-ticket-sla-ok'); ?>"><?php echo e(!empty($selectedAtendimento['sla_vencido']) ? $selectedAtendimento['sla_texto'] : 'Dentro do prazo'); ?></strong></div>
                                    <div class="at-ticket-detail-row"><i class="bi bi-calendar2" aria-hidden="true"></i><span>Criado em</span><strong><?php echo e($selectedAtendimento['created_at'] ?? '-'); ?></strong></div>
                                    <div class="at-ticket-detail-row"><i class="bi bi-clock-history" aria-hidden="true"></i><span>Atualizado em</span><strong><?php echo e($selectedAtendimento['updated_at'] ?? '-'); ?></strong></div>
                                    <div class="at-ticket-detail-row"><i class="bi bi-lightning-charge" aria-hidden="true"></i><span>Primeira resposta</span><strong><?php echo e($selectedAtendimento['primeira_resposta_em'] ?? '-'); ?></strong></div>
                                    <div class="at-ticket-detail-row"><i class="bi bi-check2-square" aria-hidden="true"></i><span>Resolução</span><strong><?php echo e($selectedAtendimento['resolvido_em'] ?? '-'); ?></strong></div>
                                </div>
                            </section>

                            <section class="at-ticket-panel">
                                <header class="at-ticket-panel-header"><span>Anexos</span><small><?php echo e($anexosDoAtendimento->count()); ?></small></header>
                                <div class="at-ticket-attachments">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $anexosDoAtendimento->take(4); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $anexo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <div class="at-ticket-file-row">
                                            <span><i class="bi bi-file-earmark" aria-hidden="true"></i></span>
                                            <div>
                                                <strong><?php echo e($anexo['nome_original'] ?? 'Anexo'); ?></strong>
                                                <small><?php echo e($anexo['tamanho_label'] ?? 'Arquivo'); ?> · <?php echo e($anexo['log_data'] ?? '-'); ?></small>
                                            </div>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($anexo['log_id']) && !empty($anexo['hash'])): ?>
                                                <button type="button" class="at-ticket-download-btn" wire:click="baixarAnexoHistorico(<?php echo e((int) $anexo['log_id']); ?>, '<?php echo e($anexo['hash']); ?>')" title="Baixar anexo"><i class="bi bi-download" aria-hidden="true"></i></button>
                                            <?php else: ?>
                                                <span></span>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                        <div class="at-empty">Nenhum anexo neste atendimento.</div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </section>
                        </aside>

                        <main class="at-ticket-center">
                            <header class="at-ticket-conversation-head">
                                <span>Conversa</span>
                                <span class="at-ticket-order-select">Ordenar: Mais antigos <i class="bi bi-chevron-down" aria-hidden="true"></i></span>
                            </header>

                            <div class="at-ticket-chat-scroll">
                                <div class="at-ticket-chat-stream">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($primeiroLogCliente === null && trim((string) ($selectedAtendimento['descricao'] ?? '')) !== ''): ?>
                                        <article class="at-ticket-message client">
                                            <span class="at-ticket-chat-avatar client"><?php echo e($clienteInicial); ?></span>
                                            <div class="at-ticket-message-card">
                                                <strong><?php echo e($selectedAtendimento['empresa_nome'] ?? 'Cliente'); ?> <span>(Cliente)</span></strong>
                                                <p><?php echo e($selectedAtendimento['descricao']); ?></p>
                                            </div>
                                            <span class="at-ticket-message-time"><?php echo e($selectedAtendimento['created_at'] ?? '-'); ?></span>
                                        </article>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $timeline; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <?php
                                            $origemLog = $log['origem'] ?? 'sistema';
                                            $isCliente = in_array($origemLog, ['cliente', 'portal', 'publico'], true);
                                            $isSuporte = in_array($origemLog, ['suporte', 'interno'], true);
                                            $tipoCard = $isCliente ? 'client' : ($isSuporte ? 'support' : 'system');
                                            $inicialLog = $isCliente ? $clienteInicial : ($isSuporte ? $responsavelInicial : 'S');
                                        ?>
                                        <article <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'ticket-chat-'.e($log['id'] ?? md5(($log['created_at'] ?? '') . ($log['mensagem'] ?? ''))).''; ?>wire:key="ticket-chat-<?php echo e($log['id'] ?? md5(($log['created_at'] ?? '') . ($log['mensagem'] ?? ''))); ?>" class="at-ticket-message <?php echo e($tipoCard); ?>">
                                            <span class="at-ticket-chat-avatar <?php echo e($tipoCard); ?>"><?php echo e($inicialLog); ?></span>
                                            <div class="at-ticket-message-card">
                                                <strong><?php echo e($log['usuario'] ?? ($isCliente ? ($selectedAtendimento['empresa_nome'] ?? 'Cliente') : 'Suporte')); ?> <span><?php echo e($isCliente ? '(Cliente)' : ($isSuporte ? '(Suporte)' : '(Sistema)')); ?></span></strong>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(trim((string) ($log['mensagem'] ?? '')) !== ''): ?>
                                                    <p><?php echo e($log['mensagem']); ?></p>
                                                <?php else: ?>
                                                    <p><?php echo e($log['tipo_label'] ?? 'Registro do sistema'); ?></p>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($log['anexos'])): ?>
                                                    <div class="at-ticket-message-files">
                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $log['anexos']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $anexo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                                            <button type="button" class="at-ticket-attachment-btn" wire:click="baixarAnexoHistorico(<?php echo e($log['id']); ?>, '<?php echo e($anexo['hash']); ?>')">
                                                                <i class="bi bi-file-earmark" aria-hidden="true"></i> <?php echo e($anexo['nome_original']); ?> <i class="bi bi-download" aria-hidden="true"></i>
                                                            </button>
                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                                    </div>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </div>
                                            <span class="at-ticket-message-time"><?php echo e($log['created_at'] ?? '-'); ?> <i class="bi bi-check2-all" aria-hidden="true"></i></span>
                                        </article>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                        <div class="at-empty">Nenhuma interação registrada. A descrição inicial aparece como contexto do atendimento.</div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </div>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $statusFechado): ?>
                                <section class="at-ticket-reply-box">
                                    <textarea wire:model="novaRespostaCliente" placeholder="Digite sua resposta..."></textarea>
                                    <div class="at-ticket-reply-actions">
                                        <div class="at-ticket-reply-left">
                                            <label class="at-ticket-upload-control" title="Anexar arquivo">
                                                <i class="bi bi-paperclip" aria-hidden="true"></i>
                                                <input type="file" wire:model="anexoRespostaCliente" accept=".jpg,.jpeg,.png,.webp,.pdf,.doc,.docx,.xls,.xlsx,.txt,.csv,image/jpeg,image/png,image/webp,application/pdf">
                                            </label>
                                            <button type="button" class="at-ticket-icon-btn" title="Emoji"><i class="bi bi-emoji-smile" aria-hidden="true"></i></button>
                                            <select class="at-ticket-quick-select" wire:change="$set('novaRespostaCliente', $event.target.value)">
                                                <option value="">Respostas rápidas</option>
                                                <option value="Olá! Recebemos sua mensagem e já estamos analisando o problema. Em breve retorno com mais informações.">Recebemos sua mensagem</option>
                                                <option value="Identificamos o ponto informado. Pode nos confirmar mais alguns dados para avançarmos com a solução?">Solicitar confirmação</option>
                                                <option value="Conseguimos resolver a pendência. Por favor, valide novamente e nos avise se precisar de algo mais.">Informar resolução</option>
                                            </select>
                                        </div>
                                        <div class="at-ticket-reply-right">
                                            <button type="button" class="at-ticket-send-btn" wire:click="responderCliente" wire:loading.attr="disabled" wire:target="responderCliente,anexoRespostaCliente">
                                                <i class="bi bi-send" aria-hidden="true"></i>
                                                <span wire:loading.remove wire:target="responderCliente,anexoRespostaCliente">Enviar resposta</span>
                                                <span wire:loading wire:target="responderCliente,anexoRespostaCliente">Enviando...</span>
                                            </button>
                                        </div>
                                    </div>
                                    <small class="at-ticket-reply-hint" wire:loading.remove wire:target="anexoRespostaCliente">Pressione o botão para enviar ao portal do cliente</small>
                                    <small class="at-ticket-reply-hint" wire:loading wire:target="anexoRespostaCliente">Carregando anexo...</small>
                                </section>
                            <?php else: ?>
                                <div class="at-ticket-finalized">Atendimento finalizado. Reabra para responder ao cliente.</div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </main>

                        <aside class="at-ticket-right">
                            <section class="at-ticket-panel">
                                <header class="at-ticket-panel-header">Cliente</header>
                                <div class="at-ticket-person-card">
                                    <span class="at-ticket-person-avatar"><?php echo e($clienteInicial); ?></span>
                                    <div>
                                        <strong><?php echo e($selectedAtendimento['empresa_nome'] ?? 'Cliente'); ?></strong>
                                        <small><?php echo e($clienteEmail); ?></small>
                                    </div>
                                </div>
                                <button type="button" class="at-ticket-side-btn"><i class="bi bi-person" aria-hidden="true"></i> Ver perfil do cliente <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i></button>
                            </section>

                            <section class="at-ticket-panel">
                                <header class="at-ticket-panel-header">Responsável</header>
                                <div class="at-ticket-person-card">
                                    <span class="at-ticket-person-avatar support"><?php echo e($responsavelInicial); ?></span>
                                    <div>
                                        <strong><?php echo e($responsavelNome); ?></strong>
                                        <small><?php echo e($selectedAtendimento['responsavel_email'] ?? 'Sem e-mail'); ?></small>
                                    </div>
                                </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $selectedAtendimento['responsavel_id']): ?>
                                    <button type="button" class="at-ticket-side-btn" wire:click="assumirAtendimento(<?php echo e($selectedAtendimento['id']); ?>)"><i class="bi bi-person-check" aria-hidden="true"></i> Assumir atendimento</button>
                                <?php else: ?>
                                    <button type="button" class="at-ticket-side-btn" wire:click="salvarDetalhe"><i class="bi bi-person-gear" aria-hidden="true"></i> Alterar responsável</button>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </section>

                            <section class="at-ticket-panel">
                                <header class="at-ticket-panel-header">Ações rápidas</header>
                                <div class="at-ticket-quick-list">
                                    <button type="button" class="at-ticket-quick-btn" wire:click="mudarStatusRapido(<?php echo e($selectedAtendimento['id']); ?>, 'em_andamento')"><span class="at-dot primary"></span> Marcar como em andamento</button>
                                    <button type="button" class="at-ticket-quick-btn" wire:click="mudarStatusRapido(<?php echo e($selectedAtendimento['id']); ?>, 'aguardando_cliente')"><span class="at-dot warning"></span> Marcar como aguardando</button>
                                    <button type="button" class="at-ticket-quick-btn" wire:click="mudarStatusRapido(<?php echo e($selectedAtendimento['id']); ?>, 'resolvido')"><span class="at-dot success"></span> Marcar como resolvido</button>
                                    <button type="button" class="at-ticket-quick-btn" wire:click="reabrirAtendimento(<?php echo e($selectedAtendimento['id']); ?>)"><span class="at-dot info"></span> Reabrir atendimento</button>
                                    <button type="button" class="at-ticket-quick-btn danger" wire:click="mudarStatusRapido(<?php echo e($selectedAtendimento['id']); ?>, 'fechado')"><span class="at-dot danger"></span> Encerrar atendimento</button>
                                </div>
                            </section>

                            <section class="at-ticket-panel">
                                <header class="at-ticket-panel-header">Histórico de status</header>
                                <div class="at-ticket-status-list">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $statusOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $statusKey => $statusMeta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <div class="at-ticket-status-item">
                                            <span class="at-ticket-status-circle <?php echo e(($selectedAtendimento['status'] ?? '') === $statusKey ? ($statusMeta['tone'] ?? 'info') : ''); ?>"></span>
                                            <span><?php echo e($statusMeta['label']); ?></span>
                                            <small><?php echo e(($selectedAtendimento['status'] ?? '') === $statusKey ? ($selectedAtendimento['updated_at'] ?? '-') : '-'); ?></small>
                                        </div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </div>
                            </section>
                        </aside>
                    </div>
                <?php else: ?>
                    <header class="at-ticket-modal-head">
                        <div class="at-ticket-modal-title">
                            <span class="at-ticket-modal-title-icon"><i class="bi bi-chat-dots-fill" aria-hidden="true"></i></span>
                            <div><h2>Carregando atendimento...</h2><p>Aguarde enquanto os dados são preparados.</p></div>
                        </div>
                        <button type="button" class="at-ticket-icon-btn" title="Fechar" @click="detalhe = false"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
                    </header>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
        <div class="at-loading-panel" wire:loading.delay.longer wire:target="loadData,sincronizarPortal,criarAtendimento,salvarDetalhe,responderCliente,adicionarInteracao,resolverComResumo">
            Atualizando central de atendimentos...
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
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views\filament\pages\atendimentos.blade.php ENDPATH**/ ?>