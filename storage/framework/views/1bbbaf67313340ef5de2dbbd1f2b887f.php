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
        $loadError = $loadError ?? null;
        $cards = $data['cards'] ?? [];
        $riskCards = $data['risk_cards'] ?? [];
        $alertasInteligentes = $data['alertas_inteligentes'] ?? [];
        $resolverAgora = collect($data['resolver_agora'] ?? [])->take(10)->values()->all();
        $clientesCriticos = $data['clientes_criticos'] ?? [];
        $vencimentos = $data['vencimentos'] ?? ['selected' => 'today', 'periods' => [], 'rows' => [], 'total' => 0];
        $deadlineRows = collect($vencimentos['rows'] ?? [])->take(4)->values();
        $deadlineTotal = (int) ($vencimentos['total'] ?? $deadlineRows->sum('value'));
        $aprovacoes = collect($data['aprovacoes'] ?? [])->take(5)->values()->all();
        $financeiro = collect($data['financeiro'] ?? [])->take(5)->values()->all();
        $financeiroResumo = $data['financeiro_resumo'] ?? ['indicadores' => [], 'impacto_total' => 'R$ 0,00'];
        $workload = collect($data['workload'] ?? [])->take(5)->values()->all();
        $departamentos = $data['departamentos'] ?? [];
        $resultadosMes = $data['resultados_mes'] ?? [];
        $healthScore = $data['health_score'] ?? ['label' => 'Excelente', 'tone' => 'success', 'value' => 100];
        $statusOptions = $data['status_options'] ?? [];
        $departmentOptions = $data['department_options'] ?? [];
        $dateRangeOptions = $data['date_range_options'] ?? [];
        $globalSearchData = $data['global_search'] ?? ['term' => '', 'results' => [], 'minimum_chars' => 2];
        $globalSearchResults = collect($globalSearchData['results'] ?? [])->take(10)->values();
        $globalSearchTerm = (string) ($globalSearchData['term'] ?? '');
        $globalSearchMinimum = (int) ($globalSearchData['minimum_chars'] ?? 2);
        $dateRangeLabel = $dateRangeOptions[$dateRange] ?? 'Hoje';
        $todayLabel = now()->translatedFormat('d \d\e F');
        $defaultIcons = ['bi-exclamation-triangle-fill', 'bi-calendar2-week-fill', 'bi-clock-fill', 'bi-file-earmark-text-fill', 'bi-currency-dollar'];
        $defaultIconClass = ['danger', 'warning', 'danger', 'info', 'success'];
        $departmentColors = ['Fiscal' => 'red', 'Contábil' => 'blue', 'DP' => 'orange', 'Departamento Pessoal' => 'orange', 'Trabalhista' => 'orange', 'Societário' => 'purple', 'Financeiro' => 'green', 'Operacional' => 'blue'];
        $departmentHex = ['red' => '#ef334e', 'blue' => '#2474ff', 'orange' => '#ff9f1c', 'purple' => '#7c3aed', 'green' => '#16a34a'];
        $departmentRows = collect($departamentos)->take(4)->values();
        $departmentTotal = (int) collect($departamentos)->sum('value');
        $acc = 0;
        $segments = [];
        foreach ($departmentRows as $row) {
            $value = (int) ($row['value'] ?? 0);
            if ($departmentTotal <= 0 || $value <= 0) { continue; }
            $label = $row['label'] ?? 'Operacional';
            $dot = $departmentColors[$label] ?? 'blue';
            $start = $acc;
            $acc += round(($value / max(1, $departmentTotal)) * 100, 2);
            $segments[] = ($departmentHex[$dot] ?? '#2474ff') . ' ' . $start . '% ' . min(100, $acc) . '%';
        }
        $donutGradient = count($segments) ? 'conic-gradient(' . implode(', ', $segments) . ')' : 'conic-gradient(#e5e7eb 0 100%)';
        $resultTone = $healthScore['tone'] ?? 'success';
        $resultMessage = match ($resultTone) {
            'danger' => 'Atenção máxima: existem gargalos que precisam ser tratados hoje.',
            'warning' => 'Atenção: priorize os itens vencidos e aprovações paradas.',
            'info' => 'Bom ritmo: ainda existem pontos para melhorar este mês.',
            default => 'Excelente! Seu escritório está no caminho certo. 🚀',
        };
        $cardsByKey = collect($cards)->keyBy('key');
        $riskCard = $cardsByKey->get('risk', ['value' => 0, 'tone' => 'success']);
        $todayCard = $cardsByKey->get('today', ['value' => 0, 'tone' => 'success']);
        $lateCard = $cardsByKey->get('late', ['value' => 0, 'tone' => 'success']);
        $operationalRiskTotal = (int) ($riskCard['value'] ?? 0) + (int) ($todayCard['value'] ?? 0) + (int) ($lateCard['value'] ?? 0);
        $operationalRiskTone = $operationalRiskTotal > 0 ? 'danger' : 'success';
        $criticalClientsCount = collect($clientesCriticos)->count();
        $resolverCollection = collect($resolverAgora);
        $resolverTotal = $resolverCollection->count();
        $resolverDanger = $resolverCollection->where('tone', 'danger')->count();
        $resolverWarning = $resolverCollection->where('tone', 'warning')->count();
        $resolverWithoutOwner = $resolverCollection->filter(fn ($item) => empty($item['responsavel']) || ($item['responsavel'] ?? null) === 'Sem responsável')->count();
        $resolverMainAction = $resolverTotal > 0
            ? ($resolverDanger > 0 ? 'Comece pelos itens vencidos ou com risco de multa.' : ($resolverWarning > 0 ? 'Priorize os vencimentos de hoje e aprovações paradas.' : 'Abra os itens em ordem e conclua o que estiver pronto.'))
            : 'Operação sem risco para resolver neste momento.';
        $clientesCriticosCollection = collect($clientesCriticos)->values();
        $clientesMaiorRisco = $clientesCriticosCollection->take(5)->values();
        $clientesRiscoAlto = $clientesCriticosCollection->filter(fn ($cliente) => in_array(($cliente['tone'] ?? ''), ['danger', 'warning'], true))->count();
        $clientesComItem = $clientesCriticosCollection->filter(fn ($cliente) => ! empty($cliente['item_id']))->count();
        $workloadCollection = collect($workload)->values();
        $responsaveisAtencao = $workloadCollection
            ->filter(fn ($row) => in_array(($row['tone'] ?? ''), ['danger', 'warning', 'attention'], true))
            ->sortByDesc(fn ($row) => (int) ($row['percent'] ?? 0))
            ->take(4)
            ->values();
        $topResponsaveis = $workloadCollection
            ->filter(fn ($row) => ($row['tone'] ?? '') === 'success')
            ->sortBy(fn ($row) => (int) ($row['percent'] ?? 0))
            ->take(3)
            ->values();
        $responsaveisAtencaoTotal = $responsaveisAtencao->count();
        $workloadTotalAberto = $workloadCollection->sum(fn ($row) => (int) ($row['total'] ?? 0));
        $tendenciasOperacionais = collect($data['tendencias_operacionais'] ?? [])->take(4)->values();
        $alertasColecao = collect($alertasInteligentes ?? []);
        $alertasCriticos = $alertasColecao
            ->filter(fn ($group) => in_array(($group['tone'] ?? 'info'), ['danger', 'warning', 'attention'], true))
            ->values();
        $alertasInformativos = $alertasColecao
            ->reject(fn ($group) => in_array(($group['tone'] ?? 'info'), ['danger', 'warning', 'attention'], true))
            ->values();
        $alertasCriticosTotal = $alertasCriticos->sum(fn ($group) => count($group['items'] ?? []));
        $alertasInformativosTotal = $alertasInformativos->sum(fn ($group) => count($group['items'] ?? []));
        $alertasTotal = $alertasCriticosTotal + $alertasInformativosTotal;
    ?>

    <section class="contabilidade-lote3-scope" aria-label="Propósito da Mesa Operacional">
        <div class="contabilidade-lote3-scope__top">
            <div>
                <span class="contabilidade-lote3-eyebrow"><i class="bi bi-command"></i> Mesa Operacional</span>
                <h2>Visão de comando da operação diária</h2>
                <p>Esta tela organiza prioridades, carga, gargalos e redistribuição. A execução detalhada permanece nas abas donas: Pendências, Documentos, Aprovações, SLA e Timeline.</p>
            </div>
            <div class="contabilidade-lote3-actions">
                <a class="contabilidade-lote3-action primary" href="<?php echo e(\App\Filament\Pages\Pendencias::getUrl()); ?>"><i class="bi bi-list-check"></i> Resolver pendências</a>
                <a class="contabilidade-lote3-action" href="<?php echo e(\App\Filament\Pages\Kanban::getUrl()); ?>"><i class="bi bi-columns-gap"></i> Kanban</a>
                <a class="contabilidade-lote3-action" href="<?php echo e(\App\Filament\Pages\TimelineOperacional::getUrl()); ?>"><i class="bi bi-calendar2-week"></i> Timeline</a>
            </div>
        </div>
        <div class="contabilidade-lote3-note">Regra do Lote 3: Mesa Operacional orienta e prioriza; não deve virar duplicata completa de Pendências ou Aprovações.</div>
    </section>

    <div class="co-page co-model" wire:loading.class="is-loading" x-data="{ searchOpen: false }" @keydown.window.ctrl.k.prevent="$refs.globalSearch.focus(); searchOpen = true" @keydown.window.meta.k.prevent="$refs.globalSearch.focus(); searchOpen = true" @keydown.escape.window="searchOpen = false">
        <section class="co-topbar">
            <div>
                <div class="co-title-row">
                    <h1>Central Operacional</h1>
                    <span class="co-info">i</span>
                </div>
                <p>Mesa principal de execução: priorize, resolva, delegue e acompanhe o trabalho do dia sem sair do fluxo operacional.</p>
            </div>

            <div class="co-top-actions">
                <div class="co-global-search" @click.outside="searchOpen = false">
                    <div class="co-global-search-box" :class="{ 'is-active': searchOpen }">
                        <i class="bi bi-search"></i>
                        <input
                            x-ref="globalSearch"
                            type="search"
                            placeholder="Buscar cliente, tarefa, documento, contrato ou responsável..."
                            wire:model.live.debounce.350ms="globalSearch"
                            @focus="searchOpen = true"
                            @input="searchOpen = true"
                            autocomplete="off"
                        >
                        <kbd>Ctrl K</kbd>
                    </div>

                    <div class="co-global-search-results" x-show="searchOpen" x-transition>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(mb_strlen($globalSearchTerm) < $globalSearchMinimum): ?>
                            <div class="co-global-search-state">
                                <i class="bi bi-command"></i>
                                <strong>Pesquisa global</strong>
                                <span>Digite pelo menos <?php echo e($globalSearchMinimum); ?> caracteres para buscar em clientes, tarefas, documentos, contratos e responsáveis.</span>
                            </div>
                        <?php else: ?>
                            <div class="co-global-search-head">
                                <span>Resultados para “<?php echo e($globalSearchTerm); ?>”</span>
                                <button type="button" wire:click="clearGlobalSearch" @click="searchOpen = false">Limpar</button>
                            </div>

                            <div class="co-global-search-list">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $globalSearchResults; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $result): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <a href="<?php echo e($result['url']); ?>" class="co-global-search-row <?php echo e($result['tone'] ?? 'info'); ?>">
                                        <span class="co-global-search-icon <?php echo e($result['priority_tone'] ?? ($result['tone'] ?? 'info')); ?>">
                                            <i class="bi <?php echo e(match($result['match_type'] ?? 'tarefa') {
                                                'cliente' => 'bi-building',
                                                'responsavel' => 'bi-person-badge',
                                                'documento' => 'bi-file-earmark-text',
                                                'contrato' => 'bi-file-earmark-lock',
                                                'tipo' => 'bi-tags',
                                                default => 'bi-check2-square',
                                            }); ?>"></i>
                                        </span>
                                        <span class="co-global-search-content">
                                            <strong><?php echo e($result['title'] ?? 'Item operacional'); ?></strong>
                                            <small><?php echo e($result['empresa'] ?? 'Sem cliente'); ?> • <?php echo e($result['responsavel'] ?? 'Sem responsável'); ?> • <?php echo e($result['due_human'] ?? 'Sem prazo'); ?></small>
                                            <em><?php echo e($result['match_label'] ?? 'Resultado'); ?>: <?php echo e($result['search_context'] ?? '-'); ?></em>
                                        </span>
                                        <span class="co-global-search-status <?php echo e($result['priority_tone'] ?? 'info'); ?>"><?php echo e($result['priority'] ?? 'Média'); ?></span>
                                    </a>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    <div class="co-global-search-state empty">
                                        <i class="bi bi-search"></i>
                                        <strong>Nenhum resultado encontrado.</strong>
                                        <span>Tente buscar por outro cliente, tarefa, documento, contrato ou responsável.</span>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>

                <div class="co-dropdown" x-data="{ open: false }" @click.outside="open = false">
                    <button type="button" class="co-toolbar-btn co-date-btn" @click="open = ! open">
                        <i class="bi bi-calendar3 co-toolbar-icon"></i>
                        <span><?php echo e($dateRangeLabel); ?>, <?php echo e($todayLabel); ?></span>
                        <i class="bi bi-chevron-down co-chevron" :class="{ 'rotate': open }"></i>
                    </button>
                    <div class="co-menu" x-show="open" x-transition>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $dateRangeOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <button type="button" wire:click="setDateRange('<?php echo e($value); ?>')" @click="open = false" class="<?php echo e($dateRange === $value ? 'active' : ''); ?>"><?php echo e($label); ?></button>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($dateRange === 'custom'): ?>
                    <div class="co-custom-date-range" aria-label="Período personalizado">
                        <label>
                            <span>Início</span>
                            <input type="date" wire:model.live.debounce.500ms="customStartDate">
                        </label>
                        <label>
                            <span>Fim</span>
                            <input type="date" wire:model.live.debounce.500ms="customEndDate">
                        </label>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <div class="co-dropdown" x-data="{ open: false }" @click.outside="open = false">
                    <button type="button" class="co-toolbar-btn" @click="open = ! open">
                        <i class="bi bi-funnel co-toolbar-icon"></i>
                        <span>Filtros</span>
                    </button>
                    <div class="co-filter-panel" x-show="open" x-transition>
                        <label>
                            <span>Status</span>
                            <select wire:model.live="statusFilter">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $statusOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <option value="<?php echo e($value); ?>"><?php echo e($label); ?></option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </select>
                        </label>
                        <label>
                            <span>Departamento</span>
                            <select wire:model.live="departmentFilter">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $departmentOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <option value="<?php echo e($value); ?>"><?php echo e($label); ?></option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </select>
                        </label>
                        <button type="button" class="co-filter-clear" wire:click="resetOperationalFilters" @click="open = false">Limpar filtros</button>
                    </div>
                </div>

                <button type="button" class="co-refresh-btn" wire:click="refreshDashboard" wire:loading.attr="disabled">
                    <i class="bi bi-arrow-clockwise" wire:loading.class="spin" wire:target="refreshDashboard,setDateRange,setDeadlinePeriod,statusFilter,departmentFilter,customStartDate,customEndDate"></i>
                    <span wire:loading.remove wire:target="refreshDashboard">Atualizar</span>
                    <span wire:loading wire:target="refreshDashboard">Atualizando...</span>
                </button>
            </div>
        </section>



        <nav class="co-page-cluster co-main-cluster" aria-label="Navegação da Central Operacional">
            <a class="co-cluster-item active" href="<?php echo e(\App\Filament\Pages\CentroOperacional::getUrl()); ?>">
                <span class="co-cluster-icon"><i class="bi bi-command"></i></span>
                <span>
                    <strong>Mesa de Execução</strong>
                    <small>Resolver agora, riscos, responsáveis e resultados</small>
                </span>
            </a>
            <button type="button" class="co-cluster-item" wire:click="criarTarefaOperacional" wire:loading.attr="disabled">
                <span class="co-cluster-icon"><i class="bi bi-plus-circle"></i></span>
                <span>
                    <strong>Nova Tarefa</strong>
                    <small>Criar demanda operacional no fluxo central</small>
                </span>
            </button>
            <button type="button" class="co-cluster-item" wire:click="abrirFilaOperacional" wire:loading.attr="disabled">
                <span class="co-cluster-icon"><i class="bi bi-list-check"></i></span>
                <span>
                    <strong>Fila Completa</strong>
                    <small>Abrir lista detalhada de tarefas internas</small>
                </span>
            </button>
        </nav>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($loadError): ?>
            <section class="co-state-card error" role="alert">
                <span class="co-state-icon"><i class="bi bi-exclamation-octagon"></i></span>
                <div>
                    <strong>Falha ao carregar dados.</strong>
                    <p><?php echo e($loadError); ?></p>
                </div>
                <button type="button" wire:click="refreshDashboard" wire:loading.attr="disabled">
                    <i class="bi bi-arrow-clockwise"></i>
                    Tentar novamente
                </button>
            </section>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="co-loading-layer" wire:loading.flex wire:target="refreshDashboard,setDateRange,setDeadlinePeriod,statusFilter,departmentFilter,customStartDate,customEndDate,globalSearch,applyStatusShortcut,applyKpiShortcut,clearGlobalSearch">
            <div class="co-loading-card">
                <span class="co-loading-spinner"></span>
                <div>
                    <strong>Atualizando Centro Operacional</strong>
                    <small>Recalculando riscos, prazos e ações prioritárias...</small>
                </div>
            </div>
        </div>

        <section class="co-operational-risk-hero <?php echo e($operationalRiskTone); ?>" aria-label="Central de Risco Operacional">
            <div class="co-operational-risk-main">
                <span class="co-operational-risk-eyebrow">Central de Risco Operacional</span>
                <h2>O que pode gerar atraso, multa ou retrabalho hoje</h2>
                <p>Prioridade visual para prazos críticos, clientes em risco e ações que precisam ser resolvidas antes de virar prejuízo.</p>
            </div>

            <div class="co-operational-risk-score">
                <span><?php echo e($operationalRiskTotal > 0 ? 'Atenção hoje' : 'Operação segura'); ?></span>
                <strong><?php echo e(number_format($operationalRiskTotal, 0, ',', '.')); ?></strong>
                <small>pontos críticos mapeados</small>
            </div>

            <div class="co-operational-risk-metrics">
                <button type="button" class="co-operational-risk-metric <?php echo e($riskCard['tone'] ?? 'success'); ?>" wire:click="applyKpiShortcut('risk')" wire:loading.attr="disabled">
                    <small>Clientes em risco</small>
                    <strong><?php echo e(number_format((int) ($riskCard['value'] ?? 0), 0, ',', '.')); ?></strong>
                    <span>multa, bloqueio ou correção parada</span>
                </button>
                <button type="button" class="co-operational-risk-metric <?php echo e($todayCard['tone'] ?? 'success'); ?>" wire:click="applyKpiShortcut('all', 'today')" wire:loading.attr="disabled">
                    <small>Vencem hoje</small>
                    <strong><?php echo e(number_format((int) ($todayCard['value'] ?? 0), 0, ',', '.')); ?></strong>
                    <span>precisam de ação no dia</span>
                </button>
                <button type="button" class="co-operational-risk-metric <?php echo e($lateCard['tone'] ?? 'success'); ?>" wire:click="applyKpiShortcut('late')" wire:loading.attr="disabled">
                    <small>Já vencidas</small>
                    <strong><?php echo e(number_format((int) ($lateCard['value'] ?? 0), 0, ',', '.')); ?></strong>
                    <span>prioridade máxima</span>
                </button>
                <a class="co-operational-risk-metric warning" href="<?php echo e(\App\Filament\Resources\ItemControles\ItemControleResource::getUrl('index')); ?>">
                    <small>Clientes críticos</small>
                    <strong><?php echo e(number_format($criticalClientsCount, 0, ',', '.')); ?></strong>
                    <span>acompanhar ranking</span>
                </a>
            </div>
        </section>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tendenciasOperacionais->isNotEmpty()): ?>
            <section class="co-trend-strip" aria-label="Tendências operacionais">
                <div class="co-trend-strip-head">
                    <span class="co-section-icon blue"><i class="bi bi-graph-up-arrow"></i></span>
                    <div>
                        <strong>Tendência da operação</strong>
                        <small>Comparação rápida para saber se a pressão está aumentando ou diminuindo.</small>
                    </div>
                </div>

                <div class="co-trend-cards">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $tendenciasOperacionais; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $trend): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <article class="co-trend-card <?php echo e($trend['tone'] ?? 'neutral'); ?>">
                            <span class="co-trend-label"><?php echo e($trend['label'] ?? 'Indicador'); ?></span>
                            <div class="co-trend-value-row">
                                <strong><?php echo e($trend['current_label'] ?? ($trend['current'] ?? 0)); ?></strong>
                                <span class="co-trend-delta <?php echo e($trend['direction'] ?? 'stable'); ?>">
                                    <i class="bi <?php echo e(match($trend['direction'] ?? 'stable') {
                                        'up' => 'bi-arrow-up-short',
                                        'down' => 'bi-arrow-down-short',
                                        default => 'bi-dash-lg',
                                    }); ?>"></i>
                                    <?php echo e($trend['delta_label'] ?? 'estável'); ?>

                                </span>
                            </div>
                            <small><?php echo e($trend['hint'] ?? 'Comparação com o período anterior.'); ?></small>
                        </article>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            </section>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <section class="co-kpi-grid" aria-label="Indicadores operacionais complementares">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $cards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php
                    $tone = $card['tone'] ?? 'info';
                    $iconTone = $defaultIconClass[$index] ?? $tone;
                    $icon = $defaultIcons[$index] ?? 'bi-activity';
                ?>
                <?php
                    $cardShortcut = $card['shortcut'] ?? 'all';
                    $cardDateRange = ($card['key'] ?? null) === 'today' ? 'today' : null;
                    $wireClick = $cardDateRange
                        ? "applyKpiShortcut('{$cardShortcut}', '{$cardDateRange}')"
                        : "applyKpiShortcut('{$cardShortcut}')";
                    $icon = $card['icon'] ?? $icon;
                ?>
                <button type="button" class="co-kpi-card co-kpi-button <?php echo e($tone); ?>" wire:click="<?php echo e($wireClick); ?>" wire:loading.attr="disabled" title="Aplicar filtro: <?php echo e($card['label'] ?? 'Indicador'); ?>">
                    <div class="co-kpi-content">
                        <span class="co-kpi-label"><?php echo e($card['label'] ?? '-'); ?></span>
                        <strong><?php echo e(is_numeric($card['value'] ?? null) ? number_format((int) $card['value'], 0, ',', '.') : ($card['value'] ?? '-')); ?></strong>
                        <small><?php echo e($card['hint'] ?? ''); ?></small>
                    </div>
                    <div class="co-kpi-icon <?php echo e($iconTone); ?>"><i class="bi <?php echo e($icon); ?>"></i></div>
                </button>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </section>

        <section class="co-focus-grid">
            <section class="co-panel co-resolve-panel co-mobile-collapsible" x-data="{ open: true }" :class="{ 'is-open': open }">
                <header class="co-panel-header co-resolve-header-v2">
                    <div class="co-heading-with-icon">
                        <span class="co-section-icon red"><i class="bi bi-lightning-charge-fill"></i></span>
                        <div>
                            <h2>Clientes em Maior Risco <small>Resolver agora</small></h2>
                            <p class="co-panel-subtitle"><?php echo e($resolverMainAction); ?></p>
                        </div>
                    </div>
                    <button type="button" class="co-mobile-toggle" @click="open = ! open" :aria-expanded="open.toString()">
                        <i class="bi" :class="open ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                        <span x-text="open ? 'Ocultar' : 'Mostrar'"></span>
                    </button>
                </header>

                <div class="co-resolve-command-strip <?php echo e($resolverDanger > 0 ? 'danger' : ($resolverWarning > 0 ? 'warning' : 'success')); ?>">
                    <div>
                        <span>Clientes/riscos para resolver</span>
                        <strong><?php echo e(number_format($resolverTotal, 0, ',', '.')); ?> <?php echo e($resolverTotal === 1 ? 'risco para resolver' : 'riscos para resolver'); ?></strong>
                    </div>
                    <div>
                        <span>Vencidas / multa</span>
                        <strong><?php echo e(number_format($resolverDanger, 0, ',', '.')); ?></strong>
                    </div>
                    <div>
                        <span>Atenção hoje</span>
                        <strong><?php echo e(number_format($resolverWarning, 0, ',', '.')); ?></strong>
                    </div>
                    <div>
                        <span>Sem responsável</span>
                        <strong><?php echo e(number_format($resolverWithoutOwner, 0, ',', '.')); ?></strong>
                    </div>
                </div>

                <div class="co-action-list co-action-list-v2 co-recommended-action-list">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $resolverAgora; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php
                            $actions = $item['actions'] ?? [];
                            $primary = $item['primary_action'] ?? ['key' => 'open', 'label' => 'Abrir', 'icon' => 'bi-box-arrow-up-right'];
                            $canApprove = (bool) ($actions['approve'] ?? false);
                            $canCorrect = (bool) ($actions['correct'] ?? false);
                            $canDelegate = (bool) ($actions['delegate'] ?? false);
                            $recommendationReason = ($item['tone'] ?? null) === 'danger'
                                ? 'Resolver primeiro: risco vencido, multa ou retrabalho.'
                                : ((($item['tone'] ?? null) === 'warning')
                                    ? 'Próxima ação: prazo próximo ou aprovação parada.'
                                    : 'Acompanhar para manter a operação fluindo.');
                        ?>
                        <article class="co-action-card-v2 co-recommended-action-card <?php echo e($item['tone'] ?? 'info'); ?>">
                            <div class="co-recommended-rank" aria-label="Ordem de prioridade"><?php echo e(str_pad($loop->iteration, 2, '0', STR_PAD_LEFT)); ?></div>
                            <a class="co-action-card-main" href="<?php echo e($item['url']); ?>">
                                <span class="co-action-icon <?php echo e($item['tone'] ?? 'info'); ?>">
                                    <i class="bi <?php echo e(($item['tone'] ?? '') === 'danger' ? 'bi-exclamation-octagon-fill' : (($item['tone'] ?? '') === 'success' ? 'bi-check-circle-fill' : (($item['tone'] ?? '') === 'warning' ? 'bi-lightning-charge-fill' : 'bi-file-earmark-text-fill'))); ?>"></i>
                                </span>
                                <div class="co-action-card-content">
                                    <div class="co-action-topline">
                                        <span class="co-action-type"><?php echo e($item['type'] ?? 'Obrigação'); ?></span>
                                        <span class="co-priority-badge <?php echo e($item['priority_tone'] ?? 'warning'); ?>"><?php echo e($item['priority'] ?? 'Alta'); ?></span>
                                    </div>
                                    <strong><?php echo e($item['title']); ?></strong>
                                    <span><?php echo e($item['empresa']); ?></span>
                                    <p class="co-action-reason"><i class="bi bi-stars"></i><?php echo e($recommendationReason); ?></p>
                                    <div class="co-action-meta">
                                        <small><i class="bi bi-calendar2-event"></i><?php echo e($item['due_human'] ?? ($item['due'] ?: 'Sem prazo')); ?></small>
                                        <small><i class="bi bi-person"></i><?php echo e($item['responsavel'] ?? 'Sem responsável'); ?></small>
                                        <small><i class="bi bi-activity"></i><?php echo e($item['status']); ?></small>
                                    </div>
                                </div>
                            </a>
                            <div class="co-action-buttons-v2">
                                <button type="button" class="co-action-btn dark" wire:click="openItemDetailModal(<?php echo e($item['id']); ?>, 'resolver')" wire:loading.attr="disabled" wire:target="openItemDetailModal(<?php echo e($item['id']); ?>, 'resolver')">
                                    <i class="bi bi-eye"></i>Detalhes
                                </button>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($primary['key'] ?? 'open') === 'approve' && $canApprove): ?>
                                    <button type="button" class="co-action-btn success" wire:click="aprovar(<?php echo e($item['id']); ?>)" wire:loading.attr="disabled" wire:target="aprovar(<?php echo e($item['id']); ?>)">
                                        <i class="bi bi-check2-circle"></i>Aprovar
                                    </button>
                                <?php elseif(($primary['key'] ?? 'open') === 'correct' && $canCorrect): ?>
                                    <button type="button" class="co-action-btn warning" wire:click="enviarParaCorrecao(<?php echo e($item['id']); ?>)" wire:loading.attr="disabled" wire:target="enviarParaCorrecao(<?php echo e($item['id']); ?>)">
                                        <i class="bi bi-tools"></i>Corrigir
                                    </button>
                                <?php else: ?>
                                    <a class="co-action-btn info" href="<?php echo e($item['url']); ?>">
                                        <i class="bi bi-box-arrow-up-right"></i>Abrir
                                    </a>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($canCorrect && ($primary['key'] ?? 'open') !== 'correct'): ?>
                                    <button type="button" class="co-action-btn muted" wire:click="enviarParaCorrecao(<?php echo e($item['id']); ?>)" wire:loading.attr="disabled" wire:target="enviarParaCorrecao(<?php echo e($item['id']); ?>)">
                                        <i class="bi bi-arrow-counterclockwise"></i>Correção
                                    </button>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($canDelegate): ?>
                                    <button type="button" class="co-action-btn purple" wire:click="openDelegateModal(<?php echo e($item['id']); ?>)" wire:loading.attr="disabled" wire:target="openDelegateModal(<?php echo e($item['id']); ?>)">
                                        <i class="bi bi-person-plus"></i>Delegar
                                    </button>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </article>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="co-empty clean">
                            <strong>Nenhuma risco para resolver agora.</strong>
                            <p>Quando existir risco, vencimento ou aprovação parada, aparecerá aqui.</p>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($resolverAgora) > 0): ?>
                    <a class="co-see-all centered" href="<?php echo e(\App\Filament\Resources\ItemControles\ItemControleResource::getUrl('index')); ?>">Ver todas as ações →</a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </section>

            <aside class="co-panel co-deadline-panel co-mobile-collapsible" x-data="{ open: false }" :class="{ 'is-open': open }">
                <header class="co-panel-header compact">
                    <div class="co-heading-with-icon">
                        <span class="co-section-icon blue"><i class="bi bi-calendar3"></i></span>
                        <h2>Vencimentos</h2>
                    </div>
                    <button type="button" class="co-mobile-toggle" @click="open = ! open" :aria-expanded="open.toString()">
                        <i class="bi" :class="open ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                        <span x-text="open ? 'Ocultar' : 'Mostrar'"></span>
                    </button>
                </header>

                <div class="co-tabs">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($vencimentos['periods'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $period): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array($key, ['today', 'seven_days', 'fifteen_days', 'thirty_days'], true)): ?>
                            <button type="button" wire:click="setDeadlinePeriod('<?php echo e($key); ?>')" class="<?php echo e(($vencimentos['selected'] ?? 'today') === $key ? 'active' : ''); ?>">
                                <?php echo e($period['label']); ?>

                            </button>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>

                <div class="co-deadline-list">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $deadlineRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php
                            $label = $row['label'] ?? 'Operacional';
                            $dot = $departmentColors[$label] ?? 'blue';
                        ?>
                        <div class="co-deadline-row">
                            <span class="co-dot <?php echo e($dot); ?>"></span>
                            <strong><?php echo e($label); ?></strong>
                            <b><?php echo e(number_format((int) ($row['value'] ?? 0), 0, ',', '.')); ?></b>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="co-empty clean"><strong>Sem vencimentos neste período.</strong></div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div class="co-deadline-total">
                    <span>Total</span>
                    <strong><?php echo e(number_format($deadlineTotal, 0, ',', '.')); ?></strong>
                </div>
            </aside>
        </section>


        <section class="co-panel co-people-risk-panel co-mobile-collapsible" x-data="{ open: false }" :class="{ 'is-open': open }">
            <header class="co-panel-header">
                <div class="co-heading-with-icon">
                    <span class="co-section-icon purple"><i class="bi bi-people-fill"></i></span>
                    <div>
                        <h2>Responsáveis em Atenção</h2>
                        <p class="co-panel-subtitle">Veja quem está sobrecarregado e quem ainda tem margem para receber demanda.</p>
                    </div>
                </div>
                <div class="co-header-actions-inline">
                    <button type="button" class="co-see-all" wire:click="abrirFilaOperacional" wire:loading.attr="disabled">Ver workload</button>
                    <button type="button" class="co-mobile-toggle" @click="open = ! open" :aria-expanded="open.toString()">
                        <i class="bi" :class="open ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                        <span x-text="open ? 'Ocultar' : 'Mostrar'"></span>
                    </button>
                </div>
            </header>

            <div class="co-people-risk-grid">
                <div class="co-people-risk-column danger">
                    <div class="co-people-risk-column-head">
                        <span><i class="bi bi-exclamation-triangle-fill"></i>Necessita atenção</span>
                        <strong><?php echo e(number_format($responsaveisAtencaoTotal, 0, ',', '.')); ?></strong>
                    </div>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $responsaveisAtencao; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <article class="co-person-risk-row <?php echo e($row['tone'] ?? 'warning'); ?>">
                            <span class="co-person-avatar"><i class="bi bi-person"></i></span>
                            <div class="co-person-info">
                                <strong><?php echo e($row['name'] ?? 'Responsável'); ?></strong>
                                <small><?php echo e($row['status'] ?? 'Atenção'); ?> • <?php echo e(number_format((int) ($row['total'] ?? 0), 0, ',', '.')); ?> itens abertos</small>
                                <div class="co-progress"><span style="width: <?php echo e(min(100, (int) ($row['percent'] ?? 0))); ?>%"></span></div>
                            </div>
                            <b><?php echo e(number_format((int) ($row['percent'] ?? 0), 0, ',', '.')); ?>%</b>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($row['responsavel_id'])): ?>
                                <button type="button" class="co-mini-action purple" wire:click="openWorkloadModal(<?php echo e((int) $row['responsavel_id']); ?>)" wire:loading.attr="disabled">
                                    <i class="bi bi-arrow-left-right"></i>Redistribuir
                                </button>
                            <?php else: ?>
                                <a class="co-mini-action" href="<?php echo e($row['open_url'] ?? \App\Filament\Resources\ItemControles\ItemControleResource::getUrl('index')); ?>"><i class="bi bi-box-arrow-up-right"></i>Abrir</a>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </article>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="co-empty clean small"><strong>Ninguém sobrecarregado no momento.</strong></div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div class="co-people-risk-column success">
                    <div class="co-people-risk-column-head">
                        <span><i class="bi bi-check2-circle"></i>Com margem</span>
                        <strong><?php echo e(number_format($topResponsaveis->count(), 0, ',', '.')); ?></strong>
                    </div>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $topResponsaveis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <article class="co-person-risk-row success compact">
                            <span class="co-person-avatar"><i class="bi bi-person-check"></i></span>
                            <div class="co-person-info">
                                <strong><?php echo e($row['name'] ?? 'Responsável'); ?></strong>
                                <small><?php echo e(number_format((int) ($row['total'] ?? 0), 0, ',', '.')); ?> itens • capacidade saudável</small>
                            </div>
                            <b><?php echo e(number_format((int) ($row['percent'] ?? 0), 0, ',', '.')); ?>%</b>
                        </article>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="co-empty clean small"><strong>Sem responsáveis com folga no filtro atual.</strong></div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div class="co-people-risk-summary">
                    <small>Total distribuído no filtro atual</small>
                    <strong><?php echo e(number_format($workloadTotalAberto, 0, ',', '.')); ?></strong>
                    <span>itens abertos com responsável</span>
                </div>
            </div>
        </section>

        <section class="co-bottom-model-grid">
            <section class="co-panel co-department-panel co-mobile-collapsible" x-data="{ open: false }" :class="{ 'is-open': open }">
                <header class="co-panel-header">
                    <div class="co-heading-with-icon">
                        <span class="co-section-icon muted"><i class="bi bi-diagram-3"></i></span>
                        <h2>Pendências por Departamento</h2>
                    </div>
                    <button type="button" class="co-mobile-toggle" @click="open = ! open" :aria-expanded="open.toString()">
                        <i class="bi" :class="open ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                        <span x-text="open ? 'Ocultar' : 'Mostrar'"></span>
                    </button>
                </header>

                <div class="co-department-content">
                    <div class="co-chart-wrap" role="img" aria-label="Gráfico real de pendências por departamento">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($departmentTotal > 0 && $departmentRows->isNotEmpty()): ?>
                            <svg class="co-donut-chart" viewBox="0 0 160 160" aria-hidden="true">
                                <circle class="co-donut-track" cx="80" cy="80" r="58" pathLength="100"></circle>
                                <?php $offset = 25; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $departmentRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <?php
                                        $label = $row['label'] ?? 'Operacional';
                                        $value = (int) ($row['value'] ?? 0);
                                        $percentFloat = $departmentTotal > 0 ? (($value / max(1, $departmentTotal)) * 100) : 0;
                                        $dot = $departmentColors[$label] ?? 'blue';
                                        $stroke = $departmentHex[$dot] ?? '#2474ff';
                                    ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($percentFloat > 0): ?>
                                        <circle
                                            class="co-donut-segment"
                                            cx="80"
                                            cy="80"
                                            r="58"
                                            pathLength="100"
                                            stroke="<?php echo e($stroke); ?>"
                                            stroke-dasharray="<?php echo e(number_format($percentFloat, 4, '.', '')); ?> <?php echo e(number_format(100 - $percentFloat, 4, '.', '')); ?>"
                                            stroke-dashoffset="<?php echo e(number_format($offset, 4, '.', '')); ?>"
                                            data-label="<?php echo e($label); ?>"
                                            data-value="<?php echo e($value); ?>"
                                            data-percent="<?php echo e(round($percentFloat)); ?>"
                                        >
                                            <title><?php echo e($label); ?>: <?php echo e($value); ?> pendências (<?php echo e(round($percentFloat)); ?>%)</title>
                                        </circle>
                                        <?php $offset -= $percentFloat; ?>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </svg>
                            <div class="co-chart-center">
                                <strong><?php echo e(number_format($departmentTotal, 0, ',', '.')); ?></strong>
                                <span>total</span>
                            </div>
                        <?php else: ?>
                            <div class="co-donut-empty"><i class="bi bi-check2-circle"></i></div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div class="co-department-legend">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $departmentRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <?php
                                $label = $row['label'] ?? 'Operacional';
                                $value = (int) ($row['value'] ?? 0);
                                $percent = $departmentTotal > 0 ? round(($value / max(1, $departmentTotal)) * 100) : 0;
                                $dot = $departmentColors[$label] ?? 'blue';
                            ?>
                            <div>
                                <span><i class="co-dot <?php echo e($dot); ?>"></i><?php echo e($label); ?></span>
                                <strong><?php echo e($value); ?> (<?php echo e($percent); ?>%)</strong>
                            </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <div class="co-empty clean"><strong>Sem pendências abertas.</strong></div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>

                <div class="co-panel-footer-total">
                    <span>Total</span>
                    <strong><?php echo e(number_format($departmentTotal, 0, ',', '.')); ?> pendências</strong>
                </div>
            </section>

            <section class="co-panel co-results-panel <?php echo e($resultTone); ?> co-mobile-collapsible" x-data="{ open: false }" :class="{ 'is-open': open }">
                <header class="co-panel-header">
                    <div class="co-heading-with-icon">
                        <span class="co-section-icon green"><i class="bi bi-trophy-fill"></i></span>
                        <h2>Resultados deste mês</h2>
                    </div>
                    <div class="co-header-actions-inline">
                        <span class="co-party"><i class="bi bi-stars"></i></span>
                        <button type="button" class="co-mobile-toggle" @click="open = ! open" :aria-expanded="open.toString()">
                            <i class="bi" :class="open ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                            <span x-text="open ? 'Ocultar' : 'Mostrar'"></span>
                        </button>
                    </div>
                </header>

                <div class="co-result-grid-model">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $resultadosMes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $result): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="co-result-model-card <?php echo e(($result['label'] ?? '') === 'Multas registradas' && (int) ($result['value'] ?? 0) > 0 ? 'danger' : 'success'); ?>">
                            <strong><?php echo e($result['value']); ?></strong>
                            <span><?php echo e($result['label']); ?></span>
                            <i class="bi <?php echo e(($result['label'] ?? '') === 'Multas registradas' && (int) ($result['value'] ?? 0) > 0 ? 'bi-exclamation-lg' : 'bi-check-lg'); ?>"></i>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>

                <p class="co-success-message <?php echo e($resultTone); ?>"><?php echo e($resultMessage); ?></p>
            </section>
        </section>


        <section class="co-panel co-alerts-panel co-alerts-collapsible" x-data="{ open: false }">
            <button type="button" class="co-alerts-toggle" @click="open = ! open" :aria-expanded="open.toString()">
                <span class="co-alerts-toggle-icon" :class="{ 'is-open': open }">
                    <i class="bi" :class="open ? 'bi-dash-lg' : 'bi-plus-lg'"></i>
                </span>
                <span class="co-alerts-toggle-text">
                    <strong>Alertas Operacionais</strong>
                    <small>Críticos separados dos informativos para evitar ruído e destacar o que pode gerar prejuízo.</small>
                </span>
                <span class="co-alerts-toggle-count <?php echo e($alertasCriticosTotal > 0 ? 'danger' : 'success'); ?>">
                    <?php echo e(number_format($alertasCriticosTotal, 0, ',', '.')); ?> críticos
                    <small><?php echo e(number_format($alertasInformativosTotal, 0, ',', '.')); ?> informativos</small>
                </span>
            </button>

            <div class="co-alerts-collapse" x-show="open" x-cloak>
                <header class="co-panel-header compact">
                    <div class="co-heading-with-icon">
                        <span class="co-section-icon red"><i class="bi bi-broadcast-pin"></i></span>
                        <h2>Alertas com prioridade de ação</h2>
                    </div>
                    <span class="co-panel-subtitle"><?php echo e(number_format($alertasTotal, 0, ',', '.')); ?> alerta(s) encontrados</span>
                </header>

                <div class="co-alerts-priority-layout">
                    <section class="co-alerts-priority-block critical">
                        <header>
                            <span><i class="bi bi-exclamation-octagon-fill"></i> Críticos e importantes</span>
                            <b><?php echo e(number_format($alertasCriticosTotal, 0, ',', '.')); ?></b>
                        </header>
                        <p>Use esta coluna para resolver primeiro prazos, bloqueios, correções e itens que podem virar multa ou retrabalho.</p>

                        <div class="co-alerts-grid priority">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $alertasCriticos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <?php $items = collect($group['items'] ?? [])->take(4)->values(); ?>
                                <article class="co-alert-column <?php echo e($group['tone'] ?? 'warning'); ?>">
                                    <header>
                                        <span><i class="bi <?php echo e($group['icon'] ?? 'bi-info-circle'); ?>"></i><?php echo e($group['label'] ?? 'Alerta crítico'); ?></span>
                                        <b><?php echo e($items->count()); ?></b>
                                    </header>
                                    <p><?php echo e($group['description'] ?? ''); ?></p>

                                    <div class="co-alert-list">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_2 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                            <a href="<?php echo e($alert['url']); ?>" class="co-alert-row <?php echo e($alert['tone'] ?? ($group['tone'] ?? 'warning')); ?>">
                                                <i class="bi <?php echo e($alert['icon'] ?? ($group['icon'] ?? 'bi-info-circle')); ?>"></i>
                                                <span>
                                                    <strong><?php echo e($alert['summary'] ?? $alert['title'] ?? 'Item operacional'); ?></strong>
                                                    <small><?php echo e($alert['reason'] ?? ''); ?> • <?php echo e($alert['due_human'] ?? 'Sem prazo'); ?></small>
                                                </span>
                                            </a>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                            <div class="co-alert-empty">
                                                <i class="bi bi-check2-circle"></i>
                                                <span>Nenhum item nesta camada.</span>
                                            </div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                </article>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <div class="co-alert-empty co-alert-empty-wide">
                                    <i class="bi bi-shield-check"></i>
                                    <span>Nenhum alerta crítico agora. A operação não tem bloqueio urgente neste momento.</span>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </section>

                    <section class="co-alerts-priority-block informative">
                        <header>
                            <span><i class="bi bi-info-circle-fill"></i> Informativos</span>
                            <b><?php echo e(number_format($alertasInformativosTotal, 0, ',', '.')); ?></b>
                        </header>
                        <p>Informações úteis para acompanhamento, sem competir visualmente com os riscos de hoje.</p>

                        <div class="co-alerts-grid informative">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $alertasInformativos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <?php $items = collect($group['items'] ?? [])->take(3)->values(); ?>
                                <article class="co-alert-column <?php echo e($group['tone'] ?? 'info'); ?>">
                                    <header>
                                        <span><i class="bi <?php echo e($group['icon'] ?? 'bi-info-circle'); ?>"></i><?php echo e($group['label'] ?? 'Informativo'); ?></span>
                                        <b><?php echo e($items->count()); ?></b>
                                    </header>
                                    <p><?php echo e($group['description'] ?? ''); ?></p>

                                    <div class="co-alert-list">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_2 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                            <a href="<?php echo e($alert['url']); ?>" class="co-alert-row <?php echo e($alert['tone'] ?? ($group['tone'] ?? 'info')); ?>">
                                                <i class="bi <?php echo e($alert['icon'] ?? ($group['icon'] ?? 'bi-info-circle')); ?>"></i>
                                                <span>
                                                    <strong><?php echo e($alert['summary'] ?? $alert['title'] ?? 'Item operacional'); ?></strong>
                                                    <small><?php echo e($alert['reason'] ?? ''); ?> • <?php echo e($alert['due_human'] ?? 'Sem prazo'); ?></small>
                                                </span>
                                            </a>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                            <div class="co-alert-empty">
                                                <i class="bi bi-check2-circle"></i>
                                                <span>Nenhum informativo nesta camada.</span>
                                            </div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                </article>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <div class="co-alert-empty co-alert-empty-wide">
                                    <i class="bi bi-check2-circle"></i>
                                    <span>Nenhum alerta informativo no momento.</span>
                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </section>
                </div>
            </div>
        </section>



        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($detailModalOpen): ?>
            <?php
                $detail = $this->selectedItemDetail();
                $scoreValue = (int) ($detail['urgency_score']['value'] ?? 92);
                $scoreValue = max(0, min(100, $scoreValue));
                $scoreTone = $scoreValue >= 85 ? 'critical' : ($scoreValue >= 65 ? 'warning' : 'info');
                $scoreReasons = collect($detail['urgency_score']['reasons'] ?? [])->take(4)->values();
                $whyHere = collect($detail['why_here'] ?? [])->take(3)->values();
                $impactRows = collect($detail['operational_impact'] ?? [])->take(3)->values();
                $checklistRows = collect($detail['checklist'] ?? [])->take(3)->values();
                $blockerRows = collect($detail['blockers'] ?? [])->take(3)->values();
                $doneRows = collect($detail['done_definition'] ?? [])->take(2)->values();
                $timelineRows = collect($detail['timeline'] ?? [])->take(2)->values();
                $criticalClient = $detail['critical_client'] ?? [];
                $readyMessage = $detail['ready_message'] ?? 'Mensagem não gerada para este item.';
                $primaryAction = $detail['decision_summary']['action'] ?? ($detail['suggestion']['primary_action'] ?? 'Entrar em contato com o cliente agora');
                $actionImpact = $detail['decision_summary']['impact'] ?? ($detail['suggestion']['text'] ?? 'Evita multa, mantém o cliente em dia e reduz retrabalho operacional.');
            ?>
            <div class="ra-modal" role="dialog" aria-modal="true" aria-labelledby="ra-detail-title">
                <div class="ra-backdrop" wire:click="closeItemDetailModal"></div>

                <article class="ra-shell" @click.stop>
                    <button type="button" class="ra-close" wire:click="closeItemDetailModal" aria-label="Fechar">
                        <i class="bi bi-x-lg"></i>
                    </button>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($detail): ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(false && $detailModalSource === 'cliente'): ?>
                            <?php
                                $clientName = $detail['empresa'] ?? 'Cliente não informado';
                                $clientScore = (int) ($detail['critical_client']['risk_score'] ?? $detail['urgency_score']['value'] ?? 0);
                                $clientScore = max(0, min(100, $clientScore));
                                $clientTone = $clientScore >= 80 ? 'RISCO ALTO' : ($clientScore >= 55 ? 'ATENÇÃO' : 'ACOMPANHAR');
                                $timelineRows = collect($detail['timeline'] ?? [])->take(3)->values();
                                $currentItem = [
                                    'titulo' => $detail['title'] ?? 'Item operacional',
                                    'status' => $detail['status'] ?? 'Pendente',
                                    'responsavel' => $detail['responsavel'] ?? 'Sem responsável',
                                    'vencimento' => $detail['vencimento'] ?? 'Sem prazo',
                                    'url' => $detail['url'] ?? '#',
                                    'atual' => true,
                                ];
                                $pendingRows = collect([$currentItem])
                                    ->merge(collect($detail['related_client_items'] ?? [])->map(function ($row) {
                                        $row['atual'] = false;
                                        return $row;
                                    }))
                                    ->take(6)
                                    ->values();
                                $clientOpen = $pendingRows->count();
                                $clientLate = (int) ($detail['critical_client']['late_items'] ?? $detail['critical_client']['pendencias_vencidas'] ?? 0);
                                $reasonRows = collect($detail['why_here'] ?? [])->merge($detail['blockers'] ?? [])->unique()->take(4)->values();
                                $impactRows = collect($detail['operational_impact'] ?? [])->take(4)->values();
                                $doneRows = collect($detail['done_definition'] ?? [])->take(3)->values();
                                $checklistRows = collect($detail['checklist'] ?? [])->take(5)->values();
                                $readyMessage = $detail['ready_message'] ?? 'Mensagem não gerada para este cliente.';
                                $riskSummaryRows = collect($detail['client_risk_summary'] ?? [])->take(5)->values();
                                $relationship = $detail['client_relationship'] ?? [];
                            ?>
                            <header class="cmr-header cmr-client-resolution">
                                <div>
                                    <div class="cmr-kicker"><i class="bi bi-exclamation-triangle-fill"></i> Central de ação do cliente</div>
                                    <div class="cmr-title-row"><h2><?php echo e($clientName); ?></h2><span><?php echo e($clientTone); ?></span></div>
                                    <div class="cmr-meta">
                                        <span><?php echo e($clientOpen); ?> pendência(s) operacional(is) nesta análise</span>
                                        <b>•</b>
                                        <strong><?php echo e($detail['categoria'] ?? 'Operacional'); ?></strong>
                                    </div>
                                </div>
                                <div class="cmr-actions">
                                    <a href="<?php echo e($detail['url'] ?? '#'); ?>" target="_blank" rel="noopener">Abrir obrigação <i class="bi bi-box-arrow-up-right"></i></a>
                                </div>
                            </header>

                            <div class="cmr-body cmr-client-action-body">
                                <section class="cmr-alert-strip cmr-client-risk-top">
                                    <div class="cmr-alert-left">
                                        <span><i class="bi bi-exclamation-lg"></i></span>
                                        <div>
                                            <strong>Por que este cliente está em risco</strong>
                                            <ul class="cmr-risk-bullets">
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $riskSummaryRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $riskSummary): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                                    <li><?php echo e($riskSummary); ?></li>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                                    <li><?php echo e($detail['decision_summary']['impact'] ?? 'Existe risco operacional que precisa de acompanhamento.'); ?></li>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="cmr-risk-score"><span>Risco atual</span><div class="cmr-scorebar"><i style="width: <?php echo e($clientScore); ?>%"></i></div><strong><?php echo e($clientScore); ?> <em>/ 100</em></strong></div>
                                </section>

                                <section class="cmr-card cmr-relationship-card">
                                    <div>
                                        <span>Último contato</span>
                                        <strong><?php echo e($relationship['last_contact'] ?? 'Sem contato registrado'); ?></strong>
                                    </div>
                                    <div>
                                        <span>Situação recente</span>
                                        <strong><?php echo e($relationship['silence'] ?? 'Sem informação suficiente'); ?></strong>
                                    </div>
                                    <div>
                                        <span>Canal registrado</span>
                                        <strong><?php echo e($relationship['channel'] ?? 'Ainda não registrado'); ?></strong>
                                    </div>
                                </section>

                                <section class="cmr-card cmr-actions-card cmr-client-primary-actions">
                                    <h3>Ação recomendada</h3>
                                    <p><?php echo e($detail['decision_summary']['action'] ?? $detail['suggestion']['primary_action'] ?? 'Entrar em contato com o cliente e encaminhar a pendência.'); ?></p>
                                    <div class="cmr-action-buttons">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($detail['portal_cliente_url'])): ?>
                                            <a href="<?php echo e($detail['portal_cliente_url']); ?>" target="_blank" rel="noopener" class="cmr-primary cmr-secondary" wire:click="registrarContatoPortalCliente(<?php echo e($detail['id']); ?>)">
                                                <i class="bi bi-chat-dots"></i> Conversar pelo Portal do Cliente
                                            </a>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($detail['whatsapp_url'])): ?>
                                            <a href="<?php echo e($detail['whatsapp_url']); ?>" target="_blank" rel="noopener" class="cmr-primary" wire:click="registrarContatoCliente(<?php echo e($detail['id']); ?>)">
                                                <i class="bi bi-whatsapp"></i> Solicitar pelo WhatsApp
                                            </a>
                                        <?php else: ?>
                                            <button type="button" class="cmr-primary" wire:click="registrarContatoCliente(<?php echo e($detail['id']); ?>)">
                                                <i class="bi bi-clipboard-check"></i> Registrar contato / copiar mensagem
                                            </button>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($detail['actions']['execute'] ?? false) && !($detail['is_closed'] ?? false)): ?>
                                            <button type="button" class="cmr-plan" wire:click="marcarItemComoResolvido(<?php echo e($detail['id']); ?>)"><i class="bi bi-check2-circle"></i> Concluir tarefa</button>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($detail['actions']['delegate'] ?? false): ?>
                                            <button type="button" class="cmr-plan" wire:click="openDelegateModal(<?php echo e($detail['id']); ?>)"><i class="bi bi-person-plus"></i> Delegar</button>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($detail['actions']['correct'] ?? false): ?>
                                            <button type="button" class="cmr-plan" wire:click="enviarParaCorrecao(<?php echo e($detail['id']); ?>)"><i class="bi bi-arrow-counterclockwise"></i> Enviar para correção</button>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                </section>

                                <div class="cmr-grid cmr-client-grid">
                                    <section class="cmr-card cmr-pending">
                                        <div class="cmr-card-head"><h3>Pendências reais do cliente (<?php echo e($clientOpen); ?>)</h3><a href="<?php echo e($detail['url'] ?? '#'); ?>" target="_blank" rel="noopener">Abrir principal</a></div>
                                        <div class="cmr-pending-list">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $pendingRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pending): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                                <article>
                                                    <span class="<?php echo e(!empty($pending['atual']) ? 'red' : 'orange'); ?>"><i class="bi bi-exclamation-triangle"></i></span>
                                                    <div>
                                                        <strong><?php echo e($pending['titulo'] ?? $pending['title'] ?? 'Item operacional'); ?></strong>
                                                        <p><?php echo e($pending['status'] ?? 'Status não informado'); ?> • <?php echo e($pending['responsavel'] ?? 'Sem responsável'); ?> • <?php echo e($pending['vencimento'] ?? 'Sem prazo'); ?></p>
                                                    </div>
                                                    <a href="<?php echo e($pending['url'] ?? ($detail['url'] ?? '#')); ?>" target="_blank" rel="noopener">Abrir</a>
                                                </article>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                                <div class="cmr-empty-state">Nenhuma pendência relacionada encontrada para este cliente.</div>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    </section>

                                    <section class="cmr-card cmr-risks">
                                        <h3>Motivos do risco</h3>
                                        <div class="cmr-risk-list">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $reasonRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reason): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                                <article><span class="red"><i class="bi bi-exclamation-triangle"></i></span><div><strong><?php echo e($reason); ?></strong></div></article>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                                <article><span class="blue"><i class="bi bi-info-circle"></i></span><div><strong>Sem motivo adicional registrado.</strong><p>Use as pendências reais para decidir a próxima ação.</p></div></article>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    </section>

                                    <section class="cmr-card cmr-summary">
                                        <h3>Se não agir</h3>
                                        <div class="cmr-info-list">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $impactRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                                <div><span><?php echo e($row['label'] ?? 'Impacto'); ?></span><strong><?php echo e($row['value'] ?? '-'); ?></strong></div>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                                <div><span>Impacto</span><strong>Não informado; tratar pelo risco operacional.</strong></div>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    </section>

                                    <section class="cmr-card cmr-comms">
                                        <div class="cmr-card-head"><h3>Mensagem pronta</h3><button type="button" wire:click="toggleDetailPersonalize">Editar</button></div>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($detailPersonalizeOpen): ?>
                                            <textarea class="ra-message-editor" wire:model.defer="detailDraftMessage" rows="5"></textarea>
                                        <?php else: ?>
                                            <div class="cmr-message-box"><?php echo e($readyMessage); ?></div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </section>

                                    <section class="cmr-card cmr-actions-card cmr-status-actions-card">
                                        <h3>Atualizar situação</h3>
                                        <button type="button" class="cmr-plan success" wire:click="registrarSituacaoCliente(<?php echo e($detail['id']); ?>, 'respondeu')"><i class="bi bi-reply-fill"></i> Cliente respondeu</button>
                                        <button type="button" class="cmr-plan success" wire:click="registrarSituacaoCliente(<?php echo e($detail['id']); ?>, 'documentos_recebidos')"><i class="bi bi-file-earmark-check"></i> Documentos recebidos</button>
                                        <button type="button" class="cmr-plan" wire:click="registrarSituacaoCliente(<?php echo e($detail['id']); ?>, 'aguardando_cliente')"><i class="bi bi-hourglass-split"></i> Aguardando cliente</button>
                                        <button type="button" class="cmr-plan danger" wire:click="registrarSituacaoCliente(<?php echo e($detail['id']); ?>, 'nao_respondeu')"><i class="bi bi-x-circle"></i> Cliente não respondeu</button>
                                    </section>

                                    <section class="cmr-card cmr-actions-card">
                                        <h3>Registrar impedimento</h3>
                                        <button type="button" class="cmr-plan" wire:click="registrarImpedimentoResolverAgora(<?php echo e($detail['id']); ?>, 'cliente')">Retorno/documento do cliente pendente</button>
                                        <button type="button" class="cmr-plan" wire:click="registrarImpedimentoResolverAgora(<?php echo e($detail['id']); ?>, 'documento')">Documento obrigatório pendente</button>
                                        <button type="button" class="cmr-plan" wire:click="registrarImpedimentoResolverAgora(<?php echo e($detail['id']); ?>, 'governo')">Sistema externo indisponível</button>
                                    </section>

                                    <section class="cmr-card cmr-actions-card">
                                        <h3>Adiar com registro</h3>
                                        <button type="button" class="cmr-plan" wire:click="adiarItemResolverAgora(<?php echo e($detail['id']); ?>, 1)">+1 dia</button>
                                        <button type="button" class="cmr-plan" wire:click="adiarItemResolverAgora(<?php echo e($detail['id']); ?>, 3)">+3 dias</button>
                                        <button type="button" class="cmr-plan" wire:click="adiarItemResolverAgora(<?php echo e($detail['id']); ?>, 7)">+7 dias</button>
                                    </section>

                                    <section class="cmr-card cmr-pending">
                                        <div class="cmr-card-head"><h3>Passos para encerrar</h3></div>
                                        <div class="cmr-pending-list">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $checklistRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                                <article><span class="<?php echo e(!empty($step['concluido']) ? 'green' : 'orange'); ?>"><i class="bi <?php echo e(!empty($step['concluido']) ? 'bi-check2' : 'bi-circle'); ?>"></i></span><div><strong><?php echo e($step['titulo'] ?? 'Etapa operacional'); ?></strong><p><?php echo e(!empty($step['concluido']) ? 'Concluído' : 'Pendente'); ?></p></div></article>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $doneRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $done): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                                    <article><span class="green"><i class="bi bi-check2"></i></span><div><strong><?php echo e($done); ?></strong></div></article>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                                    <div class="cmr-empty-state">Nenhum checklist cadastrado para este item.</div>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    </section>

                                    <section class="cmr-card cmr-comms">
                                        <div class="cmr-card-head"><h3>Últimos eventos reais</h3><a href="<?php echo e($detail['url'] ?? '#'); ?>" target="_blank" rel="noopener">Ver completo</a></div>
                                        <div class="cmr-comms-list">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $timelineRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                                <article><span class="blue"><i class="bi bi-clock-history"></i></span><div><strong><?php echo e($event['titulo'] ?? 'Atualização operacional'); ?></strong><p><?php echo e($event['descricao'] ?? 'Sem detalhe adicional.'); ?></p></div><time><?php echo e($event['data'] ?? '-'); ?></time></article>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                                <div class="cmr-empty-state">Nenhum evento registrado ainda.</div>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    </section>
                                </div>
                            </div>
                            <footer class="cmr-footer"><div><i class="bi bi-lightbulb"></i><strong>Foco:</strong> tratar pendência real, registrar o contato e tirar o cliente da fila de risco.</div><button type="button" wire:click="closeItemDetailModal">Fechar</button></footer>
                        <?php else: ?>
                        <header class="ra-header">
                            <div class="ra-heading">
                                <span class="ra-kicker">CLIENTES EM MAIOR RISCO</span>
                                <div class="ra-title-row">
                                    <h2 id="ra-detail-title"><?php echo e($detail['title']); ?></h2>
                                    <span class="ra-critical-pill"><?php echo e(strtoupper($detail['decision_summary']['tone'] === 'danger' ? 'RISCO ALTO' : ($detail['decision_summary']['tone'] === 'warning' ? 'ATENÇÃO' : $detail['prioridade']))); ?></span>
                                </div>
                                <div class="ra-meta-row">
                                    <span><i class="bi bi-building"></i>Cliente: <?php echo e($detail['empresa']); ?></span>
                                    <span>•</span>
                                    <span><i class="bi bi-calendar-event"></i><?php echo e($detail['dias_prazo']); ?></span>
                                    <span>•</span>
                                    <span>Status: <?php echo e($detail['status']); ?></span>
                                    <span class="ra-client-chip"><?php echo e($detail['categoria']); ?></span>
                                </div>
                            </div>

                            <div class="ra-header-actions">
                                <a href="<?php echo e($detail['url']); ?>" class="ra-icon-only" title="Abrir obrigação completa">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                            </div>
                        </header>

                        <div class="ra-body">
                            <main class="ra-main">
                                <section class="ra-summary-card">
                                    <div class="ra-alert-icon"><i class="bi bi-exclamation-lg"></i></div>
                                    <div>
                                        <h3>RISCO OPERACIONAL</h3>
                                        <p><?php echo e($detail['decision_summary']['problem'] ?? $detail['executive_summary']); ?></p>
                                        <p class="ra-summary-strong"><?php echo e($detail['decision_summary']['impact'] ?? $actionImpact); ?></p>
                                    </div>
                                </section>

                                <section class="ra-card ra-next-card">
                                    <h3>AÇÃO RÁPIDA RECOMENDADA</h3>
                                    <div class="ra-next-alert"><i class="bi bi-lightning-charge"></i><strong><?php echo e($primaryAction); ?></strong></div>
                                    <div class="ra-next-meta"><span>Prazo:</span><strong><?php echo e($detail['dias_prazo']); ?></strong></div>
                                    <div class="ra-next-meta"><span>Responsável:</span><strong><?php echo e($detail['responsavel']); ?></strong></div>

                                    <div class="ra-action-dock" aria-label="Ações rápidas da central de resolução">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($detail['whatsapp_url'] ?? null): ?>
                                            <a href="<?php echo e($detail['whatsapp_url']); ?>" target="_blank" rel="noopener" class="ra-action-btn ra-action-btn-primary" wire:click="registrarContatoCliente(<?php echo e($detail['id']); ?>)">
                                                <i class="bi bi-whatsapp"></i><span>Solicitar pelo WhatsApp</span>
                                            </a>
                                        <?php else: ?>
                                            <button type="button" class="ra-action-btn ra-action-btn-primary" wire:click="registrarContatoCliente(<?php echo e($detail['id']); ?>)">
                                                <i class="bi bi-clipboard-check"></i><span>Registrar contato</span>
                                            </button>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                        <a href="<?php echo e($detail['portal_cliente_url']); ?>" target="_blank" rel="noopener" class="ra-action-btn ra-action-btn-secondary" wire:click="registrarContatoPortalCliente(<?php echo e($detail['id']); ?>)">
                                            <i class="bi bi-chat-dots"></i><span>Conversar no Portal</span>
                                        </a>

                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($detail['actions']['execute'] ?? false) && ! $detail['is_closed']): ?>
                                            <button type="button" class="ra-action-btn ra-action-btn-success" wire:click="marcarItemComoResolvido(<?php echo e($detail['id']); ?>)" wire:loading.attr="disabled" wire:target="marcarItemComoResolvido(<?php echo e($detail['id']); ?>)"><i class="bi bi-check2-circle"></i><span>Concluir tarefa</span></button>
                                        <?php else: ?>
                                            <button type="button" class="ra-action-btn ra-action-btn-muted" disabled><i class="bi bi-lock"></i><span><?php echo e($detail['is_closed'] ? 'Item encerrado' : 'Sem permissão para concluir'); ?></span></button>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($detail['actions']['delegate'] ?? false) && ! $detail['is_closed']): ?>
                                            <button type="button" class="ra-action-btn ra-action-btn-secondary" wire:click="openDelegateModal(<?php echo e($detail['id']); ?>)"><i class="bi bi-person-plus"></i><span>Delegar</span></button>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($detail['actions']['correct'] ?? false) && ! $detail['is_closed']): ?>
                                            <button type="button" class="ra-action-btn ra-action-btn-secondary" wire:click="enviarParaCorrecao(<?php echo e($detail['id']); ?>)"><i class="bi bi-arrow-counterclockwise"></i><span>Correção</span></button>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                        <a href="<?php echo e($detail['url']); ?>" class="ra-action-btn ra-action-btn-ghost"><i class="bi bi-box-arrow-up-right"></i><span>Abrir obrigação</span></a>
                                    </div>
                                </section>

                                <div class="ra-top-grid">
                                    <section class="ra-card">
                                        <h3>MOTIVOS</h3>
                                        <div class="ra-reason-list">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $whyHere; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reason): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                                <div class="ra-reason-item">
                                                    <span class="ra-round-icon red"><i class="bi bi-exclamation-triangle"></i></span>
                                                    <p><?php echo e($reason); ?></p>
                                                </div>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                                <div class="ra-reason-item"><span class="ra-round-icon orange"><i class="bi bi-info-circle"></i></span><p>Revise o item antes de concluir.</p></div>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    </section>

                                    <section class="ra-card">
                                        <h3>SE NÃO RESOLVER</h3>
                                        <div class="ra-impact-list">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $impactRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $impact): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                                <div>
                                                    <span><?php echo e($impact['label'] ?? 'Impacto'); ?></span>
                                                    <strong class="<?php echo e(str_contains(strtolower((string)($impact['label'] ?? '')), 'multa') ? 'danger' : ''); ?>"><?php echo e($impact['value'] ?? '-'); ?></strong>
                                                </div>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                                <div><span>Cliente impactado</span><strong><?php echo e($detail['empresa']); ?></strong></div>
                                                <div><span>Responsável atual</span><strong><?php echo e($detail['responsavel']); ?></strong></div>
                                                <div><span>Risco</span><strong class="danger">Atraso, retrabalho ou cobrança do cliente</strong></div>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    </section>

                                    <section class="ra-card">
                                        <h3>BLOQUEADORES</h3>
                                        <div class="ra-blocker-list">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $blockerRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $blocker): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                                <div><span class="ra-round-icon red"><i class="bi bi-exclamation-triangle"></i></span><p><?php echo e($blocker); ?></p></div>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                                <div><span class="ra-round-icon green"><i class="bi bi-check2"></i></span><p>Nenhum bloqueador claro identificado.</p></div>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    </section>
                                </div>

                                <div class="ra-middle-grid ra-middle-grid-balanced">
                                    <section class="ra-card ra-message-card" x-data="{ copied: false }">
                                        <div class="ra-card-header-row">
                                            <h3>MENSAGEM E PASSOS PARA RESOLVER</h3>
                                            <button type="button" class="ra-copy-btn" @click="navigator.clipboard.writeText($refs.raReadyMessage.innerText); copied = true; setTimeout(() => copied = false, 1600)">
                                                <i class="bi bi-clipboard-check"></i><span x-text="copied ? 'Copiado' : 'Copiar'"></span>
                                            </button>
                                        </div>
                                        <div class="ra-message-box" x-ref="raReadyMessage"><?php echo e($detailDraftMessage ?: $readyMessage); ?></div>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($detailPersonalizeOpen): ?>
                                            <textarea class="ra-message-box" rows="5" wire:model.live.debounce.500ms="detailDraftMessage"></textarea>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <div class="ra-message-footer-row">
                                            <button type="button" class="ra-personalize" wire:click="toggleDetailPersonalize">
                                                <?php echo e($detailPersonalizeOpen ? 'Ocultar edição' : 'Editar mensagem'); ?> <i class="bi bi-pencil"></i>
                                            </button>
                                        </div>

                                        <div class="ra-inline-steps">
                                            <h4>PASSOS PARA ENCERRAR</h4>
                                            <ol class="ra-steps ra-steps-compact">
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $checklistRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                                    <li><span><?php echo e($loop->iteration); ?></span><p><?php echo e($step['titulo'] ?? 'Etapa operacional'); ?></p></li>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                                    <li><span>1</span><p>Tomar a ação recomendada.</p></li>
                                                    <li><span>2</span><p>Registrar o contato, impedimento ou conclusão.</p></li>
                                                    <li><span>3</span><p>Manter status e responsável atualizados.</p></li>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </ol>
                                        </div>
                                    </section>
                                </div>

                                <div class="ra-bottom-grid ra-bottom-grid-balanced">
                                    <section class="ra-card ra-situation-card">
                                        <h3>ATUALIZAR SITUAÇÃO</h3>
                                        <div class="ra-action-button-grid">
                                            <button type="button" class="ra-personalize" wire:click="registrarSituacaoCliente(<?php echo e($detail['id']); ?>, 'respondeu')">Cliente respondeu</button>
                                            <button type="button" class="ra-personalize" wire:click="registrarSituacaoCliente(<?php echo e($detail['id']); ?>, 'documentos_recebidos')">Documentos recebidos</button>
                                            <button type="button" class="ra-personalize" wire:click="registrarSituacaoCliente(<?php echo e($detail['id']); ?>, 'aguardando_cliente')">Aguardando cliente</button>
                                            <button type="button" class="ra-personalize" wire:click="registrarSituacaoCliente(<?php echo e($detail['id']); ?>, 'nao_respondeu')">Cliente não respondeu</button>
                                            <button type="button" class="ra-personalize" wire:click="registrarImpedimentoResolverAgora(<?php echo e($detail['id']); ?>, 'cliente')">Registrar sem resposta</button>
                                            <button type="button" class="ra-personalize" wire:click="registrarImpedimentoResolverAgora(<?php echo e($detail['id']); ?>, 'documento')">Documento pendente</button>
                                            <button type="button" class="ra-personalize" wire:click="registrarImpedimentoResolverAgora(<?php echo e($detail['id']); ?>, 'governo')">Sistema indisponível</button>
                                        </div>
                                    </section>

                                    <section class="ra-card ra-postpone-card">
                                        <h3>ADIAR COM REGISTRO</h3>
                                        <div class="ra-action-button-grid ra-action-button-grid-compact">
                                            <button type="button" class="ra-personalize" wire:click="adiarItemResolverAgora(<?php echo e($detail['id']); ?>, 1)">+1 dia</button>
                                            <button type="button" class="ra-personalize" wire:click="adiarItemResolverAgora(<?php echo e($detail['id']); ?>, 3)">+3 dias</button>
                                            <button type="button" class="ra-personalize" wire:click="adiarItemResolverAgora(<?php echo e($detail['id']); ?>, 7)">+7 dias</button>
                                        </div>
                                    </section>
                                </div>
                            </main>

                            <aside class="ra-side">
                                <section class="ra-card ra-timeline-card">
                                    <h3>ÚLTIMOS EVENTOS</h3>
                                    <div class="ra-timeline">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $timelineRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                            <article>
                                                <span></span>
                                                <time><?php echo e($entry['data'] ?? '-'); ?></time>
                                                <strong><?php echo e($entry['titulo'] ?? 'Atualização operacional'); ?></strong>
                                                <p><?php echo e($entry['descricao'] ?? ''); ?></p>
                                            </article>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                            <article><span></span><time>-</time><strong>Sem histórico recente</strong><p>Use as ações do popup para registrar o próximo movimento.</p></article>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                </section>

                                <section class="ra-card">
                                    <h3>SAI DO RESOLVER QUANDO</h3>
                                    <div class="ra-done-list">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $doneRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $done): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                            <div><i class="bi bi-check2"></i><p><?php echo e($done); ?></p></div>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                            <div><i class="bi bi-check2"></i><p>Concluído, delegado, corrigido ou documentado com impedimento real.</p></div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                </section>
                            </aside>
                        </div>

                        <footer class="ra-footer">
                            <div><i class="bi bi-lightbulb"></i><strong>Foco:</strong> entender o risco, tomar uma ação e registrar o movimento sem abrir várias telas.</div>
                            <button type="button" wire:click="closeItemDetailModal">Fechar</button>
                        </footer>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php else: ?>
                        <div class="ra-empty-state">
                            <h2>Item não encontrado</h2>
                            <p>O item pode ter sido atualizado, removido ou estar fora do seu escopo.</p>
                            <button type="button" wire:click="closeItemDetailModal">Fechar</button>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </article>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($workloadModalOpen): ?>
            <?php
                $workloadDetail = $this->selectedWorkloadDetail();
                $workloadTotal = (int) ($workloadDetail['total'] ?? 0);
                $workloadCritical = (int) ($workloadDetail['critical'] ?? 0);
                $workloadLate = (int) ($workloadDetail['late'] ?? 0);
                $workloadOpenSoon = max($workloadTotal - $workloadLate, 0);
                $workloadPercent = min(100, max(12, ($workloadTotal * 8) + ($workloadCritical * 7) + ($workloadLate * 5)));
                $workloadAvailableHours = 34 - ($workloadTotal + ($workloadCritical * 2));
                $workloadMainItem = $workloadDetail['items'][0] ?? null;
                $workloadResponsavelName = $workloadDetail['responsavel']->nome ?? 'Responsável';
                $workloadRole = $workloadDetail['responsavel']->cargo ?? $workloadDetail['responsavel']->funcao ?? 'Responsável operacional';
                $workloadDepartment = $workloadDetail['responsavel']->departamento ?? $workloadDetail['responsavel']->area ?? 'Equipe';
                $workloadSince = $workloadDetail['responsavel']->created_at ?? null;
                $workloadSinceLabel = $workloadSince ? $workloadSince->format('m/Y') : now()->subYear()->format('m/Y');
                $workloadImpactMoney = number_format(max(450, ($workloadLate * 280) + ($workloadCritical * 190)), 2, ',', '.');
                $workloadImpactedClients = collect($workloadDetail['items'] ?? [])->pluck('empresa')->filter()->unique()->count();
                $workloadByCategory = collect($workloadDetail['items'] ?? [])->groupBy('categoria')->map->count()->sortDesc()->take(5);
                $workloadClients = collect($workloadDetail['items'] ?? [])->groupBy('empresa')->map->count()->sortDesc()->take(5);
                $workloadDelegable = collect($workloadDetail['items'] ?? [])->take(4);
                $workloadDays = collect(range(0, 6))->map(fn ($day) => [
                    'label' => now()->addDays($day)->format('d/m'),
                    'value' => min(100, max(25, $workloadPercent - 18 + ($day * 6) - ($day % 2 ? 8 : 0))),
                ]);
            ?>
            <div class="co-modal-backdrop co-workload-v3-backdrop" role="dialog" aria-modal="true" aria-labelledby="co-workload-detail-title" wire:click.self="closeWorkloadModal">
                <div class="co-modal-card co-workload-v3-modal">
                    <button type="button" class="co-modal-close-btn co-workload-v3-x" wire:click="closeWorkloadModal" aria-label="Fechar popup">
                        <i class="bi bi-x-lg"></i>
                    </button>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($workloadDetail['responsavel']): ?>
                        <header class="co-workload-v3-header">
                            <div class="co-workload-v3-title-row">
                                <span class="co-workload-v3-icon"><i class="bi bi-people-fill"></i></span>
                                <div>
                                    <h3 id="co-workload-detail-title">Workload da Equipe</h3>
                                    <div class="co-workload-v3-person">
                                        <strong><?php echo e($workloadResponsavelName); ?></strong>
                                        <span><?php echo e($workloadDepartment); ?></span>
                                    </div>
                                    <p><?php echo e($workloadRole); ?> • Desde <?php echo e($workloadSinceLabel); ?></p>
                                </div>
                            </div>
                            <div class="co-workload-v3-header-actions">
                                <button type="button">Ver perfil completo</button>
                                <button type="button" aria-label="Mais opções"><i class="bi bi-three-dots"></i></button>
                            </div>
                        </header>

                        <section class="co-workload-v3-kpis">
                            <article class="co-workload-v3-kpi gauge-card">
                                <span>Carga de Trabalho</span>
                                <div class="co-workload-v3-gauge" style="--value: <?php echo e($workloadPercent); ?>;"><strong><?php echo e($workloadPercent); ?>%</strong></div>
                                <b class="<?php echo e($workloadPercent >= 85 ? 'danger' : 'ok'); ?>"><?php echo e($workloadPercent >= 85 ? 'Alta' : 'Controlada'); ?></b>
                                <small>Ideal: até 85%</small>
                            </article>
                            <article class="co-workload-v3-kpi">
                                <span>Tarefas Abertas</span>
                                <strong><?php echo e($workloadTotal); ?></strong>
                                <p><b><?php echo e($workloadLate); ?></b> vencidas</p>
                                <p><b><?php echo e($workloadOpenSoon); ?></b> a vencer</p>
                            </article>
                            <article class="co-workload-v3-kpi">
                                <span>Obrigações sob responsabilidade</span>
                                <strong><?php echo e($workloadTotal + $workloadCritical); ?></strong>
                                <p><b><?php echo e($workloadLate); ?></b> vencidas</p>
                                <p><b><?php echo e(max(($workloadTotal + $workloadCritical) - $workloadLate, 0)); ?></b> a vencer</p>
                            </article>
                            <article class="co-workload-v3-kpi">
                                <span>Prazo mais próximo</span>
                                <strong><?php echo e($workloadMainItem['is_late'] ?? false ? 'VENCIDO' : ($workloadMainItem['vencimento'] ?? 'Sem prazo')); ?></strong>
                                <p><?php echo e($workloadMainItem['title'] ?? 'Nenhuma tarefa pendente'); ?></p>
                            </article>
                            <article class="co-workload-v3-kpi">
                                <span>Folga disponível</span>
                                <strong><?php echo e($workloadAvailableHours >= 0 ? '+' : ''); ?><?php echo e($workloadAvailableHours); ?>h</strong>
                                <p><?php echo e($workloadAvailableHours < 0 ? 'Déficit de capacidade' : 'Capacidade disponível'); ?></p>
                            </article>
                            <article class="co-workload-v3-kpi impact">
                                <span>Impacto se não agir</span>
                                <strong><?php echo e(max($workloadLate + $workloadCritical, 1)); ?> obrigações podem atrasar</strong>
                                <p>R$ <?php echo e($workloadImpactMoney); ?></p>
                                <small><?php echo e(max($workloadImpactedClients, 1)); ?> clientes impactados</small>
                                <small><?php echo e($workloadCritical); ?> tarefas críticas</small>
                            </article>
                        </section>

                        <section class="co-workload-v3-grid three">
                            <article class="co-workload-v3-panel">
                                <h4>Distribuição da Carga</h4>
                                <div class="co-workload-v3-loadbar">
                                    <span class="red" style="width: <?php echo e(min(60, max(18, $workloadLate * 12))); ?>%"></span>
                                    <span class="yellow" style="width: <?php echo e(min(45, max(22, $workloadCritical * 10))); ?>%"></span>
                                    <span class="green"></span>
                                </div>
                                <div class="co-workload-v3-legend"><span><i class="red"></i>Acima da capacidade</span><span><i class="yellow"></i>No limite</span><span><i class="green"></i>Folga</span></div>
                            </article>
                            <article class="co-workload-v3-panel">
                                <h4>Principais demandas</h4>
                                <div class="co-workload-v3-demand-list">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $workloadByCategory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category => $amount): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <div><span><?php echo e($category ?: 'Operacional'); ?></span><b><?php echo e($amount); ?></b><em class="<?php echo e($amount >= 4 ? 'high' : ($amount >= 2 ? 'mid' : 'low')); ?>"><?php echo e($amount >= 4 ? 'Alta' : ($amount >= 2 ? 'Média' : 'Baixa')); ?></em></div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                        <div><span>Sem demandas abertas</span><b>0</b><em class="low">Baixa</em></div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </article>
                            <article class="co-workload-v3-panel danger-panel">
                                <h4>Tarefas críticas vencidas</h4>
                                <div class="co-workload-v3-critical-list">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = collect($workloadDetail['items'] ?? [])->filter(fn ($task) => $task['is_late'] ?? false)->take(4); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <div><i class="bi bi-exclamation-triangle-fill"></i><span><?php echo e($task['title']); ?></span><small><?php echo e($task['dias_prazo'] ?? 'Vencida'); ?></small></div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                        <div><i class="bi bi-check-circle-fill"></i><span>Nenhuma tarefa vencida</span><small>Equipe em dia</small></div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </article>
                        </section>

                        <section class="co-workload-v3-grid three middle">
                            <article class="co-workload-v3-panel wide-chart">
                                <h4>Carga por dia <small>próximos 7 dias</small></h4>
                                <div class="co-workload-v3-chart">
                                    <div class="ideal-line"><span>Capacidade ideal (85%)</span></div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $workloadDays; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <div class="bar-wrap"><span style="height: <?php echo e($day['value']); ?>%"></span><small><?php echo e($day['label']); ?></small></div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </div>
                            </article>
                            <article class="co-workload-v3-panel">
                                <h4>Clientes com maior demanda</h4>
                                <div class="co-workload-v3-clients">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $workloadClients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $client => $amount): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <div><span><?php echo e($client ?: 'Sem empresa'); ?></span><b><?php echo e($amount); ?> tarefa(s)</b></div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                        <div><span>Nenhum cliente listado</span><b>0 tarefa</b></div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </article>
                            <article class="co-workload-v3-panel suggestion-panel">
                                <h4>Sugestões para equilibrar carga</h4>
                                <div><i class="bi bi-arrow-left-right"></i><span>Redistribuir tarefas de alta prioridade</span></div>
                                <div><i class="bi bi-person-check"></i><span>Delegar revisões operacionais</span></div>
                                <div><i class="bi bi-calendar-check"></i><span>Antecipar atividades próximas</span></div>
                            </article>
                        </section>

                        <section class="co-workload-v3-grid bottom">
                            <article class="co-workload-v3-panel delegable-panel">
                                <h4>Tarefas que podem ser delegadas</h4>
                                <div class="co-workload-v3-table">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $workloadDelegable; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <div>
                                            <label><input type="checkbox" value="<?php echo e($task['id']); ?>" wire:click="$set('redistributionItemId', <?php echo e((int) $task['id']); ?>)"><span><?php echo e($task['title']); ?></span></label>
                                            <em><?php echo e($task['prioridade']); ?></em>
                                            <button type="button" wire:click="$set('redistributionItemId', <?php echo e((int) $task['id']); ?>)">Delegar</button>
                                        </div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                        <div><label><span>Nenhuma tarefa para delegar</span></label><em>Baixa</em><button type="button">Delegar</button></div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </article>
                            <article class="co-workload-v3-panel actions-panel">
                                <h4>Próximas ações recomendadas</h4>
                                <ol>
                                    <li><span>1</span>Priorizar tarefas vencidas e críticas.</li>
                                    <li><span>2</span>Redistribuir atividades com menor dependência técnica.</li>
                                    <li><span>3</span>Revisar prazos dos clientes mais impactados.</li>
                                    <li><span>4</span>Acompanhar capacidade da equipe no fim do dia.</li>
                                </ol>
                            </article>
                            <article class="co-workload-v3-panel message-panel">
                                <h4>Mensagem para equipe</h4>
                                <textarea readonly>Olá, equipe. Precisamos equilibrar a carga de <?php echo e($workloadResponsavelName); ?>. Existem <?php echo e($workloadLate); ?> tarefas vencidas e <?php echo e($workloadCritical); ?> críticas. Priorizar redistribuição e revisão dos próximos prazos.</textarea>
                                <div><button type="button"><i class="bi bi-copy"></i>Copiar mensagem</button><button type="button"><i class="bi bi-whatsapp"></i>Enviar no WhatsApp</button></div>
                            </article>
                        </section>

                        <div class="co-workload-v3-redistribution">
                            <div>
                                <h4>Redistribuir sem sair da tela</h4>
                                <p>Selecione uma tarefa e o novo responsável para executar a redistribuição.</p>
                            </div>
                            <label>
                                <span>Tarefa</span>
                                <select wire:model.live="redistributionItemId">
                                    <option value="">Selecione...</option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $workloadDetail['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $task): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <option value="<?php echo e($task['id']); ?>"><?php echo e($task['title']); ?> — <?php echo e($task['empresa']); ?></option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </select>
                            </label>
                            <label>
                                <span>Novo responsável</span>
                                <select wire:model.live="redistributionResponsavelId">
                                    <option value="">Selecione...</option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->delegateResponsavelOptions(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $responsavelId => $responsavelNome): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <option value="<?php echo e($responsavelId); ?>"><?php echo e($responsavelNome); ?></option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </select>
                            </label>
                        </div>

                        <footer class="co-workload-v3-footer">
                            <div><i class="bi bi-lightbulb-fill"></i><span><b>Dica:</b> mantenha a carga abaixo de 85% para evitar gargalos operacionais.</span></div>
                            <button type="button" class="secondary" wire:click="closeWorkloadModal">Fechar</button>
                            <button type="button" class="primary" wire:click="redistribuirItemSelecionado" wire:loading.attr="disabled"><i class="bi bi-arrow-left-right"></i>Redistribuir selecionada</button>
                        </footer>
                    <?php else: ?>
                        <header class="co-workload-v3-header">
                            <div class="co-workload-v3-title-row">
                                <span class="co-workload-v3-icon danger"><i class="bi bi-exclamation-triangle"></i></span>
                                <div>
                                    <h3 id="co-workload-detail-title">Responsável não encontrado</h3>
                                    <p>Não foi possível carregar o workload selecionado.</p>
                                </div>
                            </div>
                        </header>
                        <footer class="co-workload-v3-footer single"><button type="button" class="secondary" wire:click="closeWorkloadModal">Fechar</button></footer>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($delegateModalOpen): ?>
            <div class="pz-resolution-modal co-home-resolution-modal" role="dialog" aria-modal="true" aria-labelledby="co-delegate-title">
                <div class="pz-resolution-backdrop" wire:click="cancelDelegateModal"></div>
                <article class="pz-resolution-shell pz-resolution-shell-v2 co-detail-home-shell co-delegate-home-shell" @click.stop>
                    <button type="button" class="pz-resolution-x" wire:click="cancelDelegateModal" aria-label="Fechar">×</button>

                    <header class="co-detail-home-header">
                        <span class="co-section-icon purple"><i class="bi bi-person-plus"></i></span>
                        <div>
                            <h3 id="co-delegate-title">Delegar item</h3>
                            <p>Selecione o novo responsável para assumir esta pendência operacional.</p>
                        </div>
                    </header>

                    <div class="co-detail-home-scroll compact">
                        <label class="co-modal-field">
                            <span>Novo responsável</span>
                            <select wire:model.live="delegateResponsavelId">
                                <option value="">Selecione...</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->delegateResponsavelOptions(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $responsavelId => $responsavelNome): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <option value="<?php echo e($responsavelId); ?>"><?php echo e($responsavelNome); ?></option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </select>
                        </label>
                    </div>

                    <footer class="co-detail-footer-actions co-detail-home-footer">
                        <button type="button" class="co-action-btn muted" wire:click="cancelDelegateModal">Cancelar</button>
                        <button type="button" class="co-action-btn purple" wire:click="delegar" wire:loading.attr="disabled" wire:target="delegar">
                            <i class="bi bi-check2"></i>Confirmar delegação
                        </button>
                    </footer>
                </article>
            </div>
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
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views/filament/pages/centro-operacional.blade.php ENDPATH**/ ?>