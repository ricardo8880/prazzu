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

    <link rel="stylesheet" href="<?php echo e(asset('css/clientes-crm.css')); ?>">

    <?php
        $summary = $crm['summary'] ?? [];
        $clients = $clients ?? ($crm['clients'] ?? []);
        $allClients = $allClients ?? ($clientes ?? ($crm['clients'] ?? $clients));
        $actionSummary = $crm['actionSummary'] ?? [];
        $selectedClient = $selectedClient ?? (count($clients) ? $clients[0] : null);
        $selectedIdForView = (int) ($selectedClient['id'] ?? 0);
        $selectedPendencias = collect($pendencias ?? [])->where('client_id', $selectedIdForView)->take(4)->values();
        $selectedHistoricos = collect($historicos ?? [])->where('client_id', $selectedIdForView)->take(5)->values();
        $selectedDocumentos = collect($documentos ?? [])->where('client_id', $selectedIdForView)->take(5)->values();
        $criticalCount = (int) (($actionSummary['criticos']['count'] ?? ($summary['risk'] ?? 0)));
        $attentionCount = (int) (($actionSummary['atencao']['count'] ?? 0));
        $inactiveCount = (int) (($actionSummary['sem_contato']['count'] ?? 0));
        $pendingCount = (int) (($actionSummary['pendencias']['count'] ?? 0));
        $inactiveClientCount = collect($allClients)->filter(fn ($client) => (($client['contract_status_key'] ?? '') === 'cancelado') || (($client['action_status'] ?? '') === 'sem_contato'))->count();
        $activeTabKey = ($statusFilter ?? 'todos') !== 'todos' ? ($statusFilter ?? 'todos') : ($actionFilter ?? 'todos');
        $novoClienteUrl = \App\Filament\Resources\Empresas\EmpresaResource::getUrl('create');
        $onboardingCount = (int) (($summary['onboarding'] ?? count($crm['onboarding'] ?? [])));
        $ltvTotal = (float) ($summary['ltv'] ?? 0);
        $activeCount = (int) ($summary['active'] ?? 0);
        $totalCount = (int) ($summary['total'] ?? count($allClients));
        $attentionCards = collect($allClients)->sortByDesc(fn ($client) => (int) ($client['open_items'] ?? 0) + (int) ($client['late_items'] ?? 0))->take(5)->values();
        $healthScore = (int) ($selectedClient['health_score'] ?? 0);
        $healthLabel = $selectedClient['health_label'] ?? 'Sem score';
        $onboardingProgress = (int) ($selectedClient['onboarding_progress'] ?? $selectedClient['onboarding_percent'] ?? 0);
        $onboardingItems = $selectedClient['onboarding_steps'] ?? [
            ['label' => 'Diagnóstico inicial', 'status' => $onboardingProgress > 10 ? 'Concluído' : 'Pendente'],
            ['label' => 'Documentos e acessos', 'status' => $onboardingProgress > 35 ? 'Concluído' : 'Pendente'],
            ['label' => 'Configurações iniciais', 'status' => $onboardingProgress > 65 ? 'Em andamento' : 'Pendente'],
            ['label' => 'Treinamento da equipe', 'status' => $onboardingProgress > 85 ? 'Concluído' : 'Pendente'],
            ['label' => 'Go-live', 'status' => $onboardingProgress >= 100 ? 'Concluído' : 'Pendente'],
        ];
        $firstItem = ($currentPage - 1) * $perPage + 1;
        $lastItem = min($totalClientesFiltrados, $currentPage * $perPage);
    ?>

    <div class="clientes-crm-page" x-data x-on:keydown.window.ctrl.k.prevent="$refs.clientSearch?.focus()" x-on:keydown.window.meta.k.prevent="$refs.clientSearch?.focus()">
        <section class="crm-top-shell">
            <div class="crm-title-block">
                <h1>Clientes</h1>
                <p>Gerencie seus clientes, acompanhe contratos, pendências e relacionamento.</p>
            </div>

            <div class="crm-search-box">
                <i class="bi bi-search"></i>
                <input x-ref="clientSearch" type="search" wire:model.live.debounce.450ms="search" placeholder="Buscar clientes, CNPJ, responsáveis...">
                <kbd>⌘ K</kbd>
            </div>

            <div class="crm-top-actions">
                <button type="button" class="crm-btn ghost" wire:click="abrirImportacaoClientes"><i class="bi bi-upload"></i><span>Importar</span></button>
                <a href="<?php echo e($novoClienteUrl); ?>" class="crm-btn primary"><i class="bi bi-plus-lg"></i><span>Novo cliente</span><i class="bi bi-chevron-down"></i></a>
            </div>
        </section>

        <section class="crm-kpi-row" aria-label="Resumo de clientes">
            <article class="crm-kpi"><div><span>Total de clientes</span><strong><?php echo e($totalCount); ?></strong><small>+12 este mês</small></div><i class="kpi-icon purple bi bi-people"></i></article>
            <article class="crm-kpi"><div><span>Clientes ativos</span><strong><?php echo e($activeCount); ?></strong><small><?php echo e($totalCount > 0 ? round(($activeCount / max(1, $totalCount)) * 100) : 0); ?>% da base</small></div><i class="kpi-icon green bi bi-person-check"></i></article>
            <article class="crm-kpi"><div><span>Com pendências</span><strong><?php echo e($pendingCount); ?></strong><small>Alta prioridade: <b><?php echo e($criticalCount); ?></b></small></div><i class="kpi-icon amber bi bi-exclamation-triangle"></i></article>
            <article class="crm-kpi"><div><span>Onboardings ativos</span><strong><?php echo e($onboardingCount); ?></strong><small>Em andamento</small></div><i class="kpi-icon blue bi bi-rocket-takeoff"></i></article>
            <article class="crm-kpi"><div><span>Receita (LTV)</span><strong>R$ <?php echo e($ltvTotal >= 1000000 ? number_format($ltvTotal / 1000000, 2, ',', '.') . 'M' : number_format($ltvTotal, 2, ',', '.')); ?></strong><small>+18% vs mês anterior</small></div><i class="kpi-icon violet bi bi-rocket-takeoff"></i></article>
        </section>

        <section class="crm-attention-strip">
            <header>
                <div><span class="pulse"><i class="bi bi-exclamation-lg"></i></span><strong>ATENÇÃO NECESSÁRIA</strong><small><?php echo e($criticalCount + $attentionCount + $inactiveCount + $pendingCount); ?> itens exigem ação imediata</small></div>
                <button type="button" wire:click="filtrarCentralAcao('criticos')">Ver todos (<?php echo e($criticalCount + $attentionCount + $inactiveCount + $pendingCount); ?>) <i class="bi bi-arrow-right"></i></button>
            </header>
            <div class="crm-attention-list">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $attentionCards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $client): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <button type="button" wire:click="selectClient(<?php echo e((int) $client['id']); ?>)" class="crm-attention-card tone-<?php echo e($client['action_tone'] ?? 'neutral'); ?>">
                        <span><i class="bi <?php echo e(($client['action_tone'] ?? 'neutral') === 'danger' ? 'bi-calendar-x' : (($client['action_tone'] ?? 'neutral') === 'warning' ? 'bi-exclamation-triangle' : 'bi-bell')); ?>"></i></span>
                        <div><strong><?php echo e($client['name'] ?? 'Cliente'); ?></strong><small><?php echo e($client['primary_problem'] ?? $client['action_reason'] ?? $client['next_action'] ?? 'Acompanhar cliente'); ?></small></div>
                    </button>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <div class="crm-empty-inline">Nenhum alerta crítico encontrado.</div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </section>

        <section class="crm-workspace">
            <main class="crm-main-panel">
                <div class="crm-table-toolbar">
                    <nav class="crm-tabs" aria-label="Filtros rápidos">
                        <button type="button" class="<?php echo e($activeTabKey === 'todos' ? 'active' : ''); ?>" wire:click="filtrarVisaoClientes('todos')">Todos <b><?php echo e($totalCount); ?></b></button>
                        <button type="button" class="<?php echo e($activeTabKey === 'Operando bem' ? 'active' : ''); ?>" wire:click="filtrarVisaoClientes('ativos')">Ativos <b><?php echo e($activeCount); ?></b></button>
                        <button type="button" class="<?php echo e($activeTabKey === 'Em implementação' ? 'active' : ''); ?>" wire:click="filtrarVisaoClientes('onboarding')">Em onboarding <b><?php echo e($onboardingCount); ?></b></button>
                        <button type="button" class="<?php echo e($activeTabKey === 'pendencias' ? 'active' : ''); ?>" wire:click="filtrarVisaoClientes('pendencias')">Com pendências <b><?php echo e($pendingCount); ?></b></button>
                        <button type="button" class="<?php echo e($activeTabKey === 'Cancelado' || $activeTabKey === 'sem_contato' ? 'active' : ''); ?>" wire:click="filtrarVisaoClientes('inativos')">Inativos <b><?php echo e($inactiveClientCount); ?></b></button>
                    </nav>

                    <div class="crm-toolbar-actions">
                        <details class="crm-menu filter-menu">
                            <summary aria-label="Filtros"><i class="bi bi-funnel"></i><span>Filtros</span></summary>
                            <div class="crm-menu-panel right filter-panel">
                                <label><span>Status</span><select wire:model.live="statusFilter"><option value="todos">Todos</option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $statusOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?><option value="<?php echo e($status); ?>"><?php echo e($status); ?></option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?></select></label>
                                <label><span>Saúde</span><select wire:model.live="healthFilter"><option value="todos">Todos</option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $healthOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $health): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?><option value="<?php echo e($health); ?>"><?php echo e($health); ?></option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?></select></label>
                                <label><span>Ordenar</span><select wire:model.live="sortBy"><option value="updated_at">Atualização</option><option value="action_priority">Urgência</option><option value="health_score">Health Score</option><option value="open_items">Pendências</option><option value="ltv">LTV</option><option value="name">Cliente</option></select></label>
                                <button type="button" wire:click="resetarFiltros"><i class="bi bi-arrow-counterclockwise"></i> Limpar filtros</button>
                            </div>
                        </details>
                        <button type="button" class="crm-view-toggle" aria-label="Alternar visualização"><i class="bi bi-list-ul"></i></button>
                    </div>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($quickContatoAberto): ?>
                    <form wire:submit.prevent="registrarContatoRapido" class="crm-quick-form">
                        <strong>Registrar contato rápido</strong>
                        <select wire:model.defer="quickContatoTipo"><option value="contato">Contato</option><option value="reuniao">Reunião</option><option value="email">E-mail</option><option value="whatsapp">WhatsApp</option><option value="ligacao">Ligação</option></select>
                        <input type="text" wire:model.defer="quickContatoResumo" placeholder="Resumo objetivo do contato">
                        <label><input type="checkbox" wire:model.defer="quickConcluirPendenciaDepois"> Concluir próxima pendência</label>
                        <button type="submit">Salvar</button>
                        <button type="button" wire:click="cancelarContatoRapido">Cancelar</button>
                    </form>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <div class="crm-table-wrap">
                    <table class="crm-table">
                        <colgroup><col class="col-client"><col class="col-health"><col class="col-pending"><col class="col-onboarding"><col class="col-contract"><col class="col-ltv"><col class="col-next"><col class="col-actions"></colgroup>
                        <thead><tr><th>Cliente</th><th>Saúde</th><th>Pendências</th><th>Onboarding</th><th>Contrato</th><th>LTV</th><th>Próxima ação</th><th></th></tr></thead>
                        <tbody>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $clients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $client): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <?php
                                    $score = max(0, min(100, (int) ($client['health_score'] ?? 0)));
                                    $progress = max(0, min(100, (int) ($client['onboarding_progress'] ?? $client['onboarding_percent'] ?? 0)));
                                ?>
                                <tr class="tone-<?php echo e($client['action_tone'] ?? 'neutral'); ?>">
                                    <td class="client-cell"><button type="button" wire:click="selectClient(<?php echo e((int) $client['id']); ?>)" class="client-identity"><span><?php echo e(strtoupper(mb_substr($client['name'] ?? 'C', 0, 1))); ?></span><div><strong><?php echo e($client['name'] ?? 'Cliente'); ?></strong><small><?php echo e($client['document'] ?: 'Sem documento'); ?></small><small><?php echo e($client['contact_name'] ?? 'Sem decisor'); ?></small></div></button></td>
                                    <td><span class="status-pill tone-<?php echo e($client['health_tone'] ?? 'neutral'); ?>"><?php echo e($client['health_label'] ?? 'Sem score'); ?></span><div class="mini-bar"><i style="width: <?php echo e($score); ?>%"></i></div></td>
                                    <td><strong class="<?php echo e(((int)($client['open_items'] ?? 0) > 0) ? 'danger-text' : 'success-text'); ?>"><?php echo e((int)($client['open_items'] ?? 0)); ?> aberta(s)</strong><small><?php echo e((int)($client['late_items'] ?? 0)); ?> atrasada(s)</small></td>
                                    <td><strong><?php echo e($progress); ?>%</strong><div class="mini-bar purple"><i style="width: <?php echo e($progress); ?>%"></i></div><small><?php echo e($client['onboarding_status'] ?? $client['operation_label'] ?? 'Acompanhar'); ?></small></td>
                                    <td><strong><?php echo e($client['contract_status'] ?? '-'); ?></strong><small><?php echo e($client['contract_due_label'] ?? $client['updated_at_label'] ?? ''); ?></small></td>
                                    <td><strong>R$ <?php echo e(number_format((float)($client['ltv'] ?? 0), 2, ',', '.')); ?></strong><small><?php echo e($client['ltv_growth_label'] ?? 'LTV'); ?></small></td>
                                    <td class="next-action"><strong><?php echo e($client['recommended_next_step'] ?? $client['next_action'] ?? 'Acompanhar cliente'); ?></strong><small><?php echo e($client['primary_problem'] ?? $client['action_reason'] ?? ''); ?></small></td>
                                    <td class="actions-cell">
                                        <details class="crm-menu row-menu" data-crm-row-menu>
                                            <summary aria-label="Mais opções do cliente">
                                                <i class="bi bi-three-dots-vertical" aria-hidden="true"></i>
                                            </summary>
                                            <div class="crm-menu-panel right" role="menu">
                                                <button type="button" role="menuitem" wire:click="editarCliente(<?php echo e((int) $client['id']); ?>)"><i class="bi bi-window-sidebar" aria-hidden="true"></i> Abrir 360°</button>
                                                <button type="button" role="menuitem" wire:click="abrirContatoRapido(<?php echo e((int) $client['id']); ?>)"><i class="bi bi-chat-dots" aria-hidden="true"></i> Iniciar atendimento</button>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if((int)($client['open_items'] ?? 0) > 0): ?>
                                                    <button type="button" role="menuitem" wire:click="concluirProximaPendencia(<?php echo e((int) $client['id']); ?>)" wire:confirm="Concluir a próxima pendência aberta deste cliente?"><i class="bi bi-check2-circle" aria-hidden="true"></i> Concluir pendência</button>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                <button type="button" role="menuitem" wire:click="criarOnboarding(<?php echo e((int) $client['id']); ?>)"><i class="bi bi-rocket-takeoff" aria-hidden="true"></i> Criar onboarding</button>
                                            </div>
                                        </details>
                                    </td>
                                </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <tr><td colspan="8" class="crm-empty-cell">Nenhum cliente encontrado com os filtros atuais.</td></tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="crm-mobile-client-list" aria-label="Clientes em visualização mobile">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $clients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $client): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php
                            $score = max(0, min(100, (int) ($client['health_score'] ?? 0)));
                            $progress = max(0, min(100, (int) ($client['onboarding_progress'] ?? $client['onboarding_percent'] ?? 0)));
                            $openItems = (int)($client['open_items'] ?? 0);
                            $lateItems = (int)($client['late_items'] ?? 0);
                            $hasPending = $openItems > 0;
                        ?>
                        <article class="crm-mobile-client-card tone-<?php echo e($client['action_tone'] ?? 'neutral'); ?>">
                            <button type="button" class="mobile-client-summary" wire:click="selectClient(<?php echo e((int) $client['id']); ?>)">
                                <span class="mobile-client-avatar"><?php echo e(strtoupper(mb_substr($client['name'] ?? 'C', 0, 1))); ?></span>
                                <span class="mobile-client-title">
                                    <strong><?php echo e($client['name'] ?? 'Cliente'); ?></strong>
                                    <small><?php echo e($client['document'] ?: 'Sem documento'); ?></small>
                                </span>
                                <span class="mobile-client-score tone-<?php echo e($client['health_tone'] ?? 'neutral'); ?>"><?php echo e($client['health_label'] ?? 'Sem score'); ?></span>
                            </button>

                            <div class="mobile-client-essentials">
                                <div>
                                    <span>Pendências</span>
                                    <strong class="<?php echo e($hasPending ? 'danger-text' : 'success-text'); ?>"><?php echo e($openItems); ?> aberta(s)</strong>
                                    <small><?php echo e($lateItems); ?> atrasada(s)</small>
                                </div>
                                <div>
                                    <span>Próxima ação</span>
                                    <strong><?php echo e($client['recommended_next_step'] ?? $client['next_action'] ?? 'Acompanhar cliente'); ?></strong>
                                    <small><?php echo e($client['primary_problem'] ?? $client['action_reason'] ?? ''); ?></small>
                                </div>
                            </div>

                            <details class="mobile-client-details">
                                <summary>Ver detalhes <i class="bi bi-chevron-down" aria-hidden="true"></i></summary>
                                <div class="mobile-details-grid">
                                    <div><span>Responsável</span><strong><?php echo e($client['contact_name'] ?? 'Sem decisor'); ?></strong></div>
                                    <div><span>Onboarding</span><strong><?php echo e($progress); ?>%</strong><small><?php echo e($client['onboarding_status'] ?? $client['operation_label'] ?? 'Acompanhar'); ?></small></div>
                                    <div><span>Contrato</span><strong><?php echo e($client['contract_status'] ?? '-'); ?></strong><small><?php echo e($client['contract_due_label'] ?? $client['updated_at_label'] ?? ''); ?></small></div>
                                    <div><span>LTV</span><strong>R$ <?php echo e(number_format((float)($client['ltv'] ?? 0), 2, ',', '.')); ?></strong><small><?php echo e($client['ltv_growth_label'] ?? 'LTV'); ?></small></div>
                                </div>
                                <div class="mobile-progress-block">
                                    <span>Progresso do onboarding</span>
                                    <div class="mini-bar purple"><i style="width: <?php echo e($progress); ?>%"></i></div>
                                </div>
                                <div class="mobile-actions-row">
                                    <button type="button" wire:click="editarCliente(<?php echo e((int) $client['id']); ?>)"><i class="bi bi-window-sidebar" aria-hidden="true"></i> 360°</button>
                                    <button type="button" wire:click="abrirContatoRapido(<?php echo e((int) $client['id']); ?>)"><i class="bi bi-chat-dots" aria-hidden="true"></i> Atendimento</button>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasPending): ?>
                                        <button type="button" wire:click="concluirProximaPendencia(<?php echo e((int) $client['id']); ?>)" wire:confirm="Concluir a próxima pendência aberta deste cliente?"><i class="bi bi-check2-circle" aria-hidden="true"></i> Concluir</button>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <button type="button" wire:click="criarOnboarding(<?php echo e((int) $client['id']); ?>)"><i class="bi bi-rocket-takeoff" aria-hidden="true"></i> Onboarding</button>
                                </div>
                            </details>
                        </article>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="crm-mobile-empty">Nenhum cliente encontrado com os filtros atuais.</div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <footer class="crm-pagination">
                    <span>Mostrando <?php echo e($totalClientesFiltrados > 0 ? $firstItem : 0); ?> a <?php echo e($lastItem); ?> de <?php echo e($totalClientesFiltrados); ?> clientes</span>
                    <div class="page-buttons">
                        <button type="button" wire:click="previousPage" <?php if($currentPage <= 1): echo 'disabled'; endif; ?>><i class="bi bi-chevron-left"></i></button>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php for($page = max(1, $currentPage - 1); $page <= min($totalPages, $currentPage + 1); $page++): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <button type="button" class="<?php echo e($page === $currentPage ? 'active' : ''); ?>" wire:click="goToPage(<?php echo e($page); ?>)"><?php echo e($page); ?></button>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($currentPage + 1 < $totalPages): ?><span>...</span><button type="button" wire:click="goToPage(<?php echo e($totalPages); ?>)"><?php echo e($totalPages); ?></button><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <button type="button" wire:click="nextPage" <?php if($currentPage >= $totalPages): echo 'disabled'; endif; ?>><i class="bi bi-chevron-right"></i></button>
                    </div>
                    <label><select wire:model.live="perPage"><option value="6">6 por página</option><option value="10">10 por página</option><option value="15">15 por página</option><option value="25">25 por página</option></select></label>
                </footer>
            </main>

            <aside class="crm-right-panel">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selectedClient): ?>
                    <article class="side-card client-profile-card">
                        <div class="profile-head">
                            <span><?php echo e(strtoupper(mb_substr($selectedClient['name'] ?? 'C', 0, 1))); ?></span>
                            <div><strong><?php echo e($selectedClient['name']); ?></strong><small><?php echo e($selectedClient['document'] ?: 'Sem documento'); ?></small><small><?php echo e($selectedClient['contact_name'] ?? 'Sem decisor'); ?> · <?php echo e($selectedClient['contact_email'] ?? 'Sem e-mail'); ?></small><span class="client-active-badge">Ativo</span></div>
                            <button type="button" class="panel-close" wire:click="resetarFiltros" title="Limpar filtros"><i class="bi bi-x-lg"></i></button>
                        </div>
                        <nav>
                            <button type="button" class="<?php echo e($clientPanelTab === 'overview' ? 'active' : ''); ?>" wire:click="setClientPanelTab('overview')">Visão geral</button>
                            <button type="button" class="<?php echo e($clientPanelTab === 'relationship' ? 'active' : ''); ?>" wire:click="setClientPanelTab('relationship')">Relacionamento</button>
                            <button type="button" class="<?php echo e($clientPanelTab === 'history' ? 'active' : ''); ?>" wire:click="setClientPanelTab('history')">Histórico</button>
                            <button type="button" class="<?php echo e($clientPanelTab === 'documents' ? 'active' : ''); ?>" wire:click="setClientPanelTab('documents')">Documentos</button>
                        </nav>
                    </article>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($clientPanelTab === 'overview'): ?>
                        <article class="side-card health-card"><header><strong>Saúde do cliente</strong><small>Atualizado há 2h</small></header><div class="health-content"><div class="health-ring" style="--score: <?php echo e(max(0, min(100, $healthScore))); ?>"><strong><?php echo e($healthScore); ?></strong><span>/100</span></div><div><b><?php echo e($healthLabel); ?></b><p><?php echo e($selectedClient['primary_problem'] ?? 'Cliente acompanhado pela central operacional.'); ?></p><button type="button" wire:click="editarCliente(<?php echo e((int) $selectedClient['id']); ?>)">Ver análise completa</button></div></div></article>
                        <article class="side-card"><header><strong>Próximos passos</strong><button type="button" wire:click="filtrarCentralAcao('pendencias')">Ver todos</button></header><div class="side-list"><div class="step-item danger"><span><i class="bi bi-calendar2-x"></i></span><div><strong><?php echo e($selectedClient['recommended_next_step'] ?? $selectedClient['next_action'] ?? 'Concluir pendência atrasada'); ?></strong></div><em>Hoje</em></div><div class="step-item neutral"><span><i class="bi bi-calendar3"></i></span><div><strong>Reunião de alinhamento</strong></div><em>Amanhã</em></div><div class="step-item neutral"><span><i class="bi bi-calendar-check"></i></span><div><strong><?php echo e($selectedClient['contract_status'] ?? 'Acompanhar contrato'); ?></strong></div><em>Status</em></div><button type="button" class="add-next-step" wire:click="abrirContatoRapido(<?php echo e((int) $selectedClient['id']); ?>)"><i class="bi bi-plus-lg"></i> Adicionar próximo passo</button></div></article>
                        <article class="side-card"><header><strong>Pendências em aberto</strong><button type="button" wire:click="filtrarCentralAcao('pendencias')">Ver todas (<?php echo e((int)($selectedClient['open_items'] ?? 0)); ?>)</button></header><div class="side-list compact"><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $selectedPendencias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pendencia): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?><div class="pendency-line"><span><i class="bi bi-exclamation-triangle"></i></span><strong><?php echo e($pendencia['titulo'] ?? $pendencia['title'] ?? 'Pendência aberta'); ?></strong><em><?php echo e($pendencia['status'] ?? 'Aberta'); ?></em></div><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?><div class="empty-dashed">Nenhuma pendência aberta para este cliente.</div><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></div></article>
                        <article class="side-card onboarding-card"><header><strong>Onboarding</strong><b><?php echo e($onboardingProgress); ?>%</b></header><div class="big-progress"><i style="width: <?php echo e($onboardingProgress); ?>%"></i></div><div class="onboarding-steps"><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $onboardingItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?><?php $status = is_array($item) ? ($item['status'] ?? 'Pendente') : 'Pendente'; $label = is_array($item) ? ($item['label'] ?? 'Etapa') : (string) $item; ?><div class="<?php echo e($status === 'Concluído' ? 'done' : ($status === 'Em andamento' ? 'current' : '')); ?>"><span></span><strong><?php echo e($label); ?></strong><em><?php echo e($status); ?></em></div><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?></div></article>
                        <article class="side-card quick-actions-card"><header><strong>Ações rápidas</strong></header><div class="quick-grid"><button type="button" wire:click="editarCliente(<?php echo e((int) $selectedClient['id']); ?>)"><i class="bi bi-window-sidebar"></i><span>Abrir portal</span></button><button type="button" wire:click="abrirContatoRapido(<?php echo e((int) $selectedClient['id']); ?>)"><i class="bi bi-chat-dots"></i><span>Iniciar atendimento</span></button><button type="button" wire:click="criarOnboarding(<?php echo e((int) $selectedClient['id']); ?>)"><i class="bi bi-rocket-takeoff"></i><span>Nova pendência</span></button><button type="button" wire:click="abrirContatoRapido(<?php echo e((int) $selectedClient['id']); ?>)"><i class="bi bi-envelope"></i><span>Enviar mensagem</span></button></div></article>
                    <?php elseif($clientPanelTab === 'relationship'): ?>
                        <article class="side-card quick-actions-card"><header><strong>Ações rápidas</strong></header><div class="quick-grid"><button type="button" wire:click="editarCliente(<?php echo e((int) $selectedClient['id']); ?>)"><i class="bi bi-window-sidebar"></i><span>Abrir 360°</span></button><button type="button" wire:click="abrirContatoRapido(<?php echo e((int) $selectedClient['id']); ?>)"><i class="bi bi-chat-dots"></i><span>Iniciar atendimento</span></button><button type="button" wire:click="criarOnboarding(<?php echo e((int) $selectedClient['id']); ?>)"><i class="bi bi-rocket-takeoff"></i><span>Novo onboarding</span></button><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if((int)($selectedClient['open_items'] ?? 0) > 0): ?><button type="button" wire:click="concluirProximaPendencia(<?php echo e((int) $selectedClient['id']); ?>)" wire:confirm="Concluir a próxima pendência aberta deste cliente?"><i class="bi bi-check2-circle"></i><span>Concluir pendência</span></button><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></div></article>
                    <?php elseif($clientPanelTab === 'history'): ?>
                        <article class="side-card timeline-card"><header><strong>Contexto recente</strong><button type="button" wire:click="editarCliente(<?php echo e((int) $selectedClient['id']); ?>)">Abrir 360°</button></header><div class="timeline-list"><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $selectedHistoricos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $historico): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?><div><span></span><strong><?php echo e($historico['tipo'] ?? $historico['title'] ?? 'Atualização'); ?></strong><small><?php echo e($historico['descricao'] ?? $historico['description'] ?? 'Registro do relacionamento.'); ?></small></div><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?><div><span></span><strong>Sem histórico recente</strong><small>Registre contatos para criar contexto operacional.</small></div><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></div></article>
                    <?php else: ?>
                        <article class="side-card"><header><strong>Documentos</strong><button type="button" wire:click="editarCliente(<?php echo e((int) $selectedClient['id']); ?>)">Gerenciar</button></header><div class="side-list compact"><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $selectedDocumentos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $documento): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?><div class="pendency-line"><span><i class="bi bi-file-earmark-text"></i></span><strong><?php echo e($documento['titulo'] ?? $documento['nome'] ?? $documento['title'] ?? 'Documento'); ?></strong><em><?php echo e($documento['status'] ?? 'Atual'); ?></em></div><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?><div class="empty-dashed">Nenhum documento encontrado para este cliente.</div><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></div></article>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php else: ?>
                    <article class="side-card"><strong>Nenhum cliente selecionado</strong><p>Selecione um cliente na tabela para abrir o painel lateral.</p></article>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </aside>
        </section>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($clienteModalAberto && $selectedClient): ?>
            <div class="crm-modal-backdrop" role="dialog" aria-modal="true">
                <form wire:submit.prevent="salvarClienteCrm" class="crm-modal-card">
                    <header><div><span>Cliente 360°</span><strong><?php echo e($selectedClient['name']); ?></strong><small><?php echo e($selectedClient['document'] ?: 'Sem documento'); ?></small></div><button type="button" wire:click="fecharClienteModal"><i class="bi bi-x-lg"></i></button></header>
                    <div class="crm-modal-grid">
                        <label><span>Status do contrato</span><select wire:model.defer="editStatusContrato"><option value="">Manter atual</option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $statusOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?><option value="<?php echo e($status); ?>"><?php echo e($status); ?></option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?></select></label>
                        <label><span>Saúde manual</span><select wire:model.defer="editHealthManual"><option value="">Automática</option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $healthOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $health): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?><option value="<?php echo e($health); ?>"><?php echo e($health); ?></option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?></select></label>
                        <label><span>Responsável</span><input type="text" wire:model.defer="editContatoNome" placeholder="Nome do contato"></label>
                        <label><span>E-mail</span><input type="email" wire:model.defer="editContatoEmail" placeholder="email@cliente.com"></label>
                        <label><span>WhatsApp</span><input type="text" wire:model.defer="editContatoWhatsapp" placeholder="(00) 00000-0000"></label>
                        <label class="full"><span>Observações / próxima ação</span><textarea rows="4" wire:model.defer="editObservacoes" placeholder="Resumo, risco, próximos passos e observações do relacionamento"></textarea></label>
                    </div>
                    <footer><button type="button" wire:click="fecharClienteModal">Cancelar</button><button type="submit">Salvar CRM</button></footer>
                </form>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <script>
            (() => {
                const bindClientesMenus = () => {
                    if (window.__clientesCrmMenusBound) {
                        return;
                    }

                    window.__clientesCrmMenusBound = true;

                    const closeMenus = (except = null) => {
                        document.querySelectorAll('.crm-menu[open]').forEach((menu) => {
                            if (menu !== except) {
                                menu.removeAttribute('open');
                            }
                        });
                    };

                    document.addEventListener('click', (event) => {
                        const summary = event.target.closest('.crm-menu > summary');

                        if (summary) {
                            const currentMenu = summary.closest('.crm-menu');

                            window.requestAnimationFrame(() => {
                                if (currentMenu?.hasAttribute('open')) {
                                    closeMenus(currentMenu);
                                }
                            });

                            return;
                        }

                        if (! event.target.closest('.crm-menu')) {
                            closeMenus();
                        }
                    }, true);

                    document.addEventListener('keydown', (event) => {
                        if (event.key === 'Escape') {
                            closeMenus();
                        }
                    });

                    document.addEventListener('click', (event) => {
                        if (event.target.closest('.crm-menu-panel button')) {
                            window.requestAnimationFrame(() => closeMenus());
                        }
                    });
                };

                bindClientesMenus();
            })();
        </script>
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
<?php /**PATH C:\xampp\htdocs\sistemrh\resources\views/filament/pages/clientes.blade.php ENDPATH**/ ?>