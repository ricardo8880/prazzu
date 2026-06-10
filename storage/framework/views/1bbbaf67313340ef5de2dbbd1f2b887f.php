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

    <link rel="stylesheet" href="<?php echo e(asset('css/centro-operacional.css')); ?>?v=<?php echo e(file_exists(public_path('css/centro-operacional.css')) ? filemtime(public_path('css/centro-operacional.css')) : time()); ?>">

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
            : 'Operação sem ação crítica neste momento.';
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

    <div class="co-page co-model" wire:loading.class="is-loading" x-data="{ searchOpen: false }" @keydown.window.ctrl.k.prevent="$refs.globalSearch.focus(); searchOpen = true" @keydown.window.meta.k.prevent="$refs.globalSearch.focus(); searchOpen = true" @keydown.escape.window="searchOpen = false">
        <section class="co-topbar">
            <div>
                <div class="co-title-row">
                    <h1>Centro Operacional</h1>
                    <span class="co-info">i</span>
                </div>
                <p>Veja primeiro o que pode gerar multa, atraso ou retrabalho hoje.</p>
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



        <nav class="co-page-cluster co-main-cluster" aria-label="Navegação do Centro Operacional">
            <a class="co-cluster-item active" href="<?php echo e(\App\Filament\Pages\CentroOperacional::getUrl()); ?>">
                <span class="co-cluster-icon"><i class="bi bi-command"></i></span>
                <span>
                    <strong>Centro Operacional</strong>
                    <small>Riscos, resolver agora e resultados</small>
                </span>
            </a>
            <a class="co-cluster-item" href="<?php echo e(\App\Filament\Pages\CentroOperacionalGestao::getUrl()); ?>?aba=workload">
                <span class="co-cluster-icon"><i class="bi bi-grid-1x2"></i></span>
                <span>
                    <strong>Operação Interna</strong>
                    <small>Workload, aprovações e financeiro</small>
                </span>
            </a>
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
                            <h2>Ação Recomendada <small>Resolver Agora</small></h2>
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
                        <span>Fila única de prioridade</span>
                        <strong><?php echo e(number_format($resolverTotal, 0, ',', '.')); ?> <?php echo e($resolverTotal === 1 ? 'ação crítica' : 'ações críticas'); ?></strong>
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
                            <strong>Nenhuma ação crítica agora.</strong>
                            <p>Quando existir risco, vencimento ou aprovação parada, aparecerá aqui.</p>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($resolverAgora) > 0): ?>
                    <a class="co-see-all centered" href="<?php echo e(\App\Filament\Resources\ItemControles\ItemControleResource::getUrl('index')); ?>">Ver todas as ações →</a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </section>

            <section class="co-panel co-clients-panel co-client-risk-panel co-mobile-collapsible" x-data="{ open: false }" :class="{ 'is-open': open }">
                <header class="co-panel-header">
                    <div class="co-heading-with-icon">
                        <span class="co-section-icon orange"><i class="bi bi-building-exclamation"></i></span>
                        <div>
                            <h2>Clientes em Maior Risco</h2>
                            <p class="co-panel-subtitle">Ranking para atacar primeiro quem pode gerar atraso, multa ou retrabalho.</p>
                        </div>
                    </div>
                    <div class="co-header-actions-inline">
                        <a class="co-see-all" href="<?php echo e(\App\Filament\Resources\ItemControles\ItemControleResource::getUrl('index')); ?>">Ver todos</a>
                        <button type="button" class="co-mobile-toggle" @click="open = ! open" :aria-expanded="open.toString()">
                            <i class="bi" :class="open ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                            <span x-text="open ? 'Ocultar' : 'Mostrar'"></span>
                        </button>
                    </div>
                </header>

                <div class="co-risk-summary-strip">
                    <article>
                        <small>Clientes críticos</small>
                        <strong><?php echo e(number_format($clientesCriticosCollection->count(), 0, ',', '.')); ?></strong>
                    </article>
                    <article>
                        <small>Risco alto</small>
                        <strong><?php echo e(number_format($clientesRiscoAlto, 0, ',', '.')); ?></strong>
                    </article>
                    <article>
                        <small>Com ação rápida</small>
                        <strong><?php echo e(number_format($clientesComItem, 0, ',', '.')); ?></strong>
                    </article>
                </div>

                <div class="co-client-list-model co-client-risk-list">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $clientesMaiorRisco; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cliente): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php
                            $clientTone = $cliente['tone'] ?? 'warning';
                            $riskLabel = $cliente['risco'] ?? 'alto';
                        ?>
                        <article class="co-client-model-row co-client-risk-row <?php echo e($clientTone); ?>">
                            <a class="co-client-row-link" href="<?php echo e($cliente['url']); ?>">
                                <span class="co-client-rank"><?php echo e(str_pad($loop->iteration, 2, '0', STR_PAD_LEFT)); ?></span>
                                <span class="co-client-avatar"><i class="bi bi-building"></i></span>
                                <div class="co-client-main">
                                    <strong><?php echo e($cliente['cliente']); ?></strong>
                                    <span><?php echo e($cliente['problema']); ?></span>
                                    <small><i class="bi bi-lightning-charge-fill"></i> Prioridade: resolver antes de virar prejuízo operacional.</small>
                                </div>
                                <span class="co-risk-badge <?php echo e($clientTone); ?>">Risco <?php echo e($riskLabel); ?></span>
                            </a>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($cliente['item_id'])): ?>
                                <button type="button" class="co-mini-action dark" wire:click="openItemDetailModal(<?php echo e((int) $cliente['item_id']); ?>, 'cliente')" wire:loading.attr="disabled">
                                    <i class="bi bi-eye"></i>Detalhes
                                </button>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </article>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="co-empty clean">
                            <strong>Nenhum cliente crítico.</strong>
                            <p>Sem clientes em risco neste momento.</p>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
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
                    <a class="co-see-all" href="<?php echo e(\App\Filament\Pages\CentroOperacionalGestao::getUrl()); ?>?aba=workload">Ver workload</a>
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
                $whyHere = collect($detail['why_here'] ?? [])->take(4)->values();
                $impactRows = collect($detail['operational_impact'] ?? [])->take(4)->values();
                $checklistRows = collect($detail['checklist'] ?? [])->take(5)->values();
                $blockerRows = collect($detail['blockers'] ?? [])->take(3)->values();
                $doneRows = collect($detail['done_definition'] ?? [])->take(4)->values();
                $timelineRows = collect($detail['timeline'] ?? [])->take(4)->values();
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
                        <header class="ra-header">
                            <div class="ra-heading">
                                <span class="ra-kicker">AÇÃO RECOMENDADA</span>
                                <div class="ra-title-row">
                                    <h2 id="ra-detail-title"><?php echo e($detail['title']); ?></h2>
                                    <span class="ra-critical-pill"><?php echo e(strtoupper($detail['prioridade'] ?? 'CRÍTICA')); ?></span>
                                </div>
                                <div class="ra-meta-row">
                                    <span><i class="bi bi-clipboard2-check"></i><?php echo e($detail['categoria']); ?></span>
                                    <span>•</span>
                                    <span><?php echo e($detail['categoria']); ?></span>
                                    <span>•</span>
                                    <span>Ref. <?php echo e(now()->format('m/Y')); ?></span>
                                    <span class="ra-client-chip">Cliente: <?php echo e($detail['empresa']); ?></span>
                                </div>
                            </div>

                            <div class="ra-header-actions">
                                <button type="button"><i class="bi bi-star"></i>Favoritar</button>
                                <button type="button" class="ra-icon-only"><i class="bi bi-three-dots"></i></button>
                            </div>
                        </header>

                        <div class="ra-body">
                            <main class="ra-main">
                                <section class="ra-summary-card">
                                    <div class="ra-alert-icon"><i class="bi bi-exclamation-lg"></i></div>
                                    <div>
                                        <h3>RESUMO EXECUTIVO</h3>
                                        <p><?php echo e($detail['executive_summary'] ?? 'Esta obrigação vence hoje e ainda não foi concluída.'); ?></p>
                                        <p class="ra-summary-strong"><?php echo e($actionImpact); ?></p>
                                    </div>
                                </section>

                                <div class="ra-top-grid">
                                    <section class="ra-card">
                                        <h3>POR QUE ESTÁ AQUI?</h3>
                                        <div class="ra-reason-list">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $whyHere; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reason): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                                <div class="ra-reason-item">
                                                    <span class="ra-round-icon red"><i class="bi bi-exclamation-triangle"></i></span>
                                                    <p><?php echo e($reason); ?></p>
                                                </div>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                                <div class="ra-reason-item"><span class="ra-round-icon red"><i class="bi bi-clock"></i></span><p>Vence em menos de 24 horas</p></div>
                                                <div class="ra-reason-item"><span class="ra-round-icon orange"><i class="bi bi-hourglass-split"></i></span><p>Está parado há alguns dias</p></div>
                                                <div class="ra-reason-item"><span class="ra-round-icon red"><i class="bi bi-file-earmark-lock"></i></span><p>Documento obrigatório pendente</p></div>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    </section>

                                    <section class="ra-card">
                                        <h3>IMPACTO SE NÃO RESOLVER</h3>
                                        <div class="ra-impact-list">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $impactRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $impact): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                                <div>
                                                    <span><?php echo e($impact['label'] ?? 'Impacto'); ?></span>
                                                    <strong class="<?php echo e(str_contains(strtolower((string)($impact['label'] ?? '')), 'multa') ? 'danger' : ''); ?>"><?php echo e($impact['value'] ?? '-'); ?></strong>
                                                </div>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                                <div><span>Risco de multa</span><strong class="danger"><?php echo e($detail['valor']); ?></strong></div>
                                                <div><span>Cliente impactado</span><strong><?php echo e($detail['empresa']); ?></strong></div>
                                                <div><span>Departamento</span><strong><?php echo e($detail['categoria']); ?></strong></div>
                                                <div><span>Tipo de impacto</span><strong><em>Financeiro e Fiscal</em></strong></div>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    </section>

                                    <section class="ra-card">
                                        <h3>TEMPO PARADO</h3>
                                        <div class="ra-stalled-list">
                                            <div><i class="bi bi-clock-history"></i><span>Última atualização</span><strong><?php echo e($detail['stalled_info']['last_update'] ?? '-'); ?></strong></div>
                                            <div><i class="bi bi-clock"></i><span>Parado há</span><strong class="danger"><?php echo e($detail['stalled_info']['days'] ?? 'Sem histórico'); ?></strong></div>
                                            <div><i class="bi bi-person-badge"></i><span>Responsável atual</span><strong><?php echo e($detail['responsavel']); ?></strong></div>
                                        </div>
                                    </section>
                                </div>

                                <div class="ra-middle-grid">
                                    <section class="ra-card ra-action-card">
                                        <h3>AÇÃO RECOMENDADA – O QUE FAZER AGORA</h3>
                                        <div class="ra-action-content">
                                            <ol class="ra-steps">
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $checklistRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                                    <li><span><?php echo e($loop->iteration); ?></span><p><?php echo e($step['titulo'] ?? 'Etapa operacional'); ?></p></li>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                                    <li><span>1</span><p>Abrir obrigação referente ao período atual</p></li>
                                                    <li><span>2</span><p>Validar informações e débitos</p></li>
                                                    <li><span>3</span><p>Anexar/validar documentos necessários</p></li>
                                                    <li><span>4</span><p>Transmitir obrigação</p></li>
                                                    <li><span>5</span><p>Confirmar transmissão e gerar recibo</p></li>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </ol>

                                            <aside class="ra-action-note">
                                                <div><i class="bi bi-stopwatch"></i><span>Tempo estimado</span><strong>15 minutos</strong></div>
                                                <div><i class="bi bi-shield-check"></i><span>Impacto da ação</span><p><?php echo e($actionImpact); ?></p></div>
                                            </aside>
                                        </div>
                                    </section>

                                    <section class="ra-card ra-message-card" x-data="{ copied: false }">
                                        <div class="ra-card-header-row">
                                            <h3>MENSAGEM PRONTA PARA O CLIENTE</h3>
                                            <button type="button" class="ra-copy-btn" @click="navigator.clipboard.writeText($refs.raReadyMessage.innerText); copied = true; setTimeout(() => copied = false, 1600)">
                                                <i class="bi bi-clipboard-check"></i><span x-text="copied ? 'Copiado' : 'Copiar mensagem'"></span>
                                            </button>
                                        </div>
                                        <div class="ra-message-box" x-ref="raReadyMessage"><?php echo e($readyMessage); ?></div>
                                        <button type="button" class="ra-personalize">Personalizar antes de enviar <i class="bi bi-pencil"></i></button>
                                    </section>
                                </div>

                                <div class="ra-bottom-grid">
                                    <section class="ra-card">
                                        <h3>BLOQUEADORES IDENTIFICADOS</h3>
                                        <div class="ra-blocker-list">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $blockerRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $blocker): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                                <div><span class="ra-round-icon red"><i class="bi bi-exclamation-triangle"></i></span><p><?php echo e($blocker); ?></p></div>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                                <div><span class="ra-round-icon green"><i class="bi bi-check2"></i></span><p>Nenhuma aprovação pendente</p></div>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    </section>

                                    <section class="ra-card">
                                        <h3>QUANDO ESSE ITEM DEIXA DE APARECER AQUI?</h3>
                                        <div class="ra-done-list">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $doneRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $done): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                                <div><i class="bi bi-check2"></i><p><?php echo e($done); ?></p></div>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                                <div><i class="bi bi-check2"></i><p>Obrigação concluída com sucesso</p></div>
                                                <div><i class="bi bi-check2"></i><p>Recibo de entrega gerado</p></div>
                                                <div><i class="bi bi-check2"></i><p>Não há pendências relacionadas</p></div>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                        <footer>O item será removido automaticamente após a conclusão.</footer>
                                    </section>

                                    <section class="ra-card ra-next-card">
                                        <h3>PRÓXIMA AÇÃO SUGERIDA</h3>
                                        <div class="ra-next-alert"><i class="bi bi-exclamation-triangle"></i><strong><?php echo e($primaryAction); ?></strong></div>
                                        <div class="ra-next-meta"><span>Canal sugerido:</span><strong><i class="bi bi-whatsapp"></i> WhatsApp</strong></div>
                                        <div class="ra-next-meta"><span>Prioridade:</span><strong><b></b> Alta</strong></div>
                                        <div class="ra-next-meta"><span>Melhor horário:</span><strong>Agora</strong></div>
                                        <button type="button" class="ra-primary-btn"><i class="bi bi-whatsapp"></i>Iniciar contato com o cliente</button>
                                    </section>
                                </div>
                            </main>

                            <aside class="ra-side">
                                <section class="ra-card ra-score-card">
                                    <h3>SCORE DE URGÊNCIA</h3>
                                    <div class="ra-score-wrap">
                                        <div class="ra-score-ring" style="--score: <?php echo e($scoreValue); ?>;"><strong><?php echo e($scoreValue); ?></strong><span>/100</span></div>
                                        <div class="ra-score-reasons">
                                            <h4>Por que esse score?</h4>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $scoreReasons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reason): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                                <div><span><?php echo e($reason); ?></span><strong>+<?php echo e(max(7, 40 - (($loop->iteration - 1) * 10))); ?></strong></div>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                                <div><span>Vence hoje</span><strong>+40</strong></div>
                                                <div><span>Parado há 4 dias</span><strong>+30</strong></div>
                                                <div><span>Obrigação fiscal</span><strong>+15</strong></div>
                                                <div><span>Cliente crítico</span><strong>+7</strong></div>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                    </div>
                                </section>

                                <section class="ra-card ra-client-card">
                                    <div class="ra-card-header-row">
                                        <h3>CLIENTE CRÍTICO</h3>
                                        <span class="ra-risk-pill">ALTO RISCO</span>
                                    </div>
                                    <div class="ra-client-metrics">
                                        <div><span>Pendências abertas</span><strong><?php echo e($criticalClient['open_items'] ?? $criticalClient['pendencias_abertas'] ?? $criticalClient['open'] ?? count($detail['related_client_items'] ?? [])); ?></strong></div>
                                        <div><span>Pendências vencidas</span><strong><?php echo e($criticalClient['late_items'] ?? $criticalClient['pendencias_vencidas'] ?? '-'); ?></strong></div>
                                        <div><span>Faturamento (12m)</span><strong><?php echo e($criticalClient['revenue_12m'] ?? $criticalClient['faturamento_12m'] ?? $detail['valor']); ?></strong></div>
                                    </div>
                                    <a href="<?php echo e($detail['url']); ?>" class="ra-outline-link">Ver dashboard do cliente <i class="bi bi-box-arrow-up-right"></i></a>
                                </section>

                                <section class="ra-card ra-timeline-card">
                                    <h3>LINHA DO TEMPO</h3>
                                    <div class="ra-timeline">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $timelineRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                            <article>
                                                <span></span>
                                                <time><?php echo e($entry['data'] ?? '-'); ?></time>
                                                <strong><?php echo e($entry['titulo'] ?? 'Atualização operacional'); ?></strong>
                                                <p><?php echo e($entry['descricao'] ?? ''); ?></p>
                                            </article>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                            <article><span></span><time><?php echo e(now()->format('d/m/Y H:i')); ?></time><strong>Item identificado</strong><p>Ação recomendada criada automaticamente.</p></article>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                </section>

                                <section class="ra-quick-actions">
                                    <h3>AÇÕES RÁPIDAS</h3>
                                    <div>
                                        <a href="<?php echo e($detail['url']); ?>">Abrir obrigação <i class="bi bi-box-arrow-up-right"></i></a>
                                        <a href="<?php echo e($detail['url']); ?>">Ver cliente <i class="bi bi-box-arrow-up-right"></i></a>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($detail['actions']['delegate'] ?? false) && ! $detail['is_closed']): ?>
                                            <button type="button" wire:click="openDelegateModal(<?php echo e($detail['id']); ?>)"><i class="bi bi-person-plus"></i>Delegar tarefa</button>
                                        <?php else: ?>
                                            <button type="button"><i class="bi bi-person-plus"></i>Delegar tarefa</button>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <button type="button" class="success" wire:click="closeItemDetailModal"><i class="bi bi-check2"></i>Marcar como resolvido</button>
                                    </div>
                                </section>
                            </aside>
                        </div>

                        <footer class="ra-footer">
                            <div><i class="bi bi-lightbulb"></i><strong>Dica:</strong> Resolva agora para evitar multas, retrabalho e manter a confiança do cliente.</div>
                            <label><input type="checkbox">Não mostrar novamente</label>
                            <button type="button" wire:click="closeItemDetailModal">Fechar</button>
                        </footer>
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
            <?php $workloadDetail = $this->selectedWorkloadDetail(); ?>
            <div class="pz-resolution-modal co-home-resolution-modal" role="dialog" aria-modal="true" aria-labelledby="co-workload-title">
                <div class="pz-resolution-backdrop" wire:click="closeWorkloadModal"></div>
                <article class="pz-resolution-shell pz-resolution-shell-v2 co-detail-home-shell" @click.stop>
                    <button type="button" class="pz-resolution-x" wire:click="closeWorkloadModal" aria-label="Fechar">×</button>

                    <header class="co-detail-home-header">
                        <span class="co-section-icon purple"><i class="bi bi-people-fill"></i></span>
                        <div>
                            <div class="pz-resolution-breadcrumb">
                                <span>Workload da Equipe</span>
                                <b>›</b>
                                <span>Detalhes do responsável</span>
                            </div>
                            <h3 id="co-workload-title"><?php echo e($workloadDetail['responsavel']?->nome ?? 'Responsável'); ?></h3>
                            <p><?php echo e(number_format((int) ($workloadDetail['total'] ?? 0), 0, ',', '.')); ?> itens abertos • <?php echo e(number_format((int) ($workloadDetail['late'] ?? 0), 0, ',', '.')); ?> atrasados</p>
                        </div>
                    </header>

                    <div class="co-detail-home-scroll">
                        <div class="co-detail-modal-body">
                            <div class="co-detail-grid">
                                <div><small>Total aberto</small><strong><?php echo e(number_format((int) ($workloadDetail['total'] ?? 0), 0, ',', '.')); ?></strong></div>
                                <div><small>Críticos</small><strong><?php echo e(number_format((int) ($workloadDetail['critical'] ?? 0), 0, ',', '.')); ?></strong></div>
                                <div><small>Atrasados</small><strong><?php echo e(number_format((int) ($workloadDetail['late'] ?? 0), 0, ',', '.')); ?></strong></div>
                                <div><small>Decisão sugerida</small><strong><?php echo e($workloadDetail['bottleneck_summary']['action'] ?? (!empty($workloadDetail['recommendation']) ? 'Redistribuir' : 'Monitorar')); ?></strong></div>
                            </div>

                            <div class="co-decision-box <?php echo e($workloadDetail['bottleneck_summary']['tone'] ?? 'warning'); ?>">
                                <div>
                                    <small>Leitura operacional da carga</small>
                                    <strong><?php echo e($workloadDetail['bottleneck_summary']['title'] ?? ($workloadDetail['recommendation']['title'] ?? 'Avaliar redistribuição')); ?></strong>
                                    <p><?php echo e($workloadDetail['bottleneck_summary']['text'] ?? ($workloadDetail['recommendation']['text'] ?? 'Analise os itens abaixo e redistribua apenas o que estiver travando a operação.')); ?></p>
                                </div>
                                <span><?php echo e($workloadDetail['bottleneck_summary']['action'] ?? 'Rebalancear carga'); ?></span>
                            </div>

                            <section class="co-detail-insight-card">
                                <h4><i class="bi bi-activity"></i>Sinais de gargalo</h4>
                                <div class="co-detail-grid">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($workloadDetail['workload_signals'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $signal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <div>
                                            <small><?php echo e($signal['label']); ?></small>
                                            <strong><?php echo e($signal['value']); ?></strong>
                                            <em><?php echo e($signal['text']); ?></em>
                                        </div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </div>
                            </section>

                            <div class="co-detail-insights-grid">
                                <section class="co-detail-insight-card">
                                    <h4><i class="bi bi-list-check"></i>Itens que mais pesam na fila</h4>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($workloadDetail['items'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <article>
                                            <div>
                                                <strong><?php echo e($item['title']); ?></strong>
                                                <span><?php echo e($item['empresa']); ?> • <?php echo e($item['status']); ?> • <?php echo e($item['vencimento']); ?> • <?php echo e($item['dias_prazo']); ?></span>
                                            </div>
                                            <a class="co-mini-action" href="<?php echo e($item['url']); ?>"><i class="bi bi-box-arrow-up-right"></i>Abrir</a>
                                        </article>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                        <div class="co-empty clean small"><strong>Nenhum item aberto para este responsável.</strong></div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </section>

                                <section class="co-detail-insight-card">
                                    <h4><i class="bi bi-arrow-left-right"></i>Redistribuição</h4>
                                    <label class="co-modal-field">
                                        <span>Item</span>
                                        <select wire:model.live="redistributionItemId">
                                            <option value="">Selecione...</option>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($workloadDetail['items'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                                <option value="<?php echo e($item['id']); ?>"><?php echo e($item['title']); ?> — <?php echo e($item['empresa']); ?></option>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                        </select>
                                    </label>

                                    <label class="co-modal-field">
                                        <span>Novo responsável</span>
                                        <select wire:model.live="redistributionResponsavelId">
                                            <option value="">Selecione...</option>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->delegateResponsavelOptions(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $responsavelId => $responsavelNome): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                                <option value="<?php echo e($responsavelId); ?>"><?php echo e($responsavelNome); ?></option>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                        </select>
                                    </label>
                                </section>
                            </div>
                        </div>
                    </div>

                    <footer class="co-detail-footer-actions co-detail-home-footer">
                        <button type="button" class="co-action-btn muted" wire:click="closeWorkloadModal">Fechar</button>
                        <button type="button" class="co-action-btn purple" wire:click="redistribuirItemSelecionado" wire:loading.attr="disabled" wire:target="redistribuirItemSelecionado">
                            <i class="bi bi-check2"></i>Confirmar redistribuição
                        </button>
                    </footer>
                </article>
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