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
        $filters = $filters ?? [];
        $filterOptions = $data['filterOptions'] ?? ['actions' => [], 'users' => [], 'companies' => []];
        $historyContext = $data['historyContext'] ?? ['active' => false];
        $dateFilter = (string) ($filters['dateFilter'] ?? '30');
        $fromDate = (string) ($filters['fromDate'] ?? '');
        $toDate = (string) ($filters['toDate'] ?? '');
        $userFilter = (string) ($filters['userFilter'] ?? 'todos');
        $companyFilter = (string) ($filters['companyFilter'] ?? 'todas');
        $actionFilter = (string) ($filters['actionFilter'] ?? 'todas');
        $searchFilter = (string) ($filters['searchFilter'] ?? '');
        $auditableTypeFilter = (string) ($filters['auditableType'] ?? '');
        $auditableIdFilter = (string) ($filters['auditableId'] ?? '');
        $hasActiveFilters = $dateFilter !== '30' || $fromDate !== '' || $toDate !== '' || $userFilter !== 'todos' || $companyFilter !== 'todas' || $actionFilter !== 'todas' || $searchFilter !== '' || $auditableTypeFilter !== '' || $auditableIdFilter !== '';
        $filterUrl = function (array $extra = [], array $forget = []) {
            $query = request()->query();
            foreach ($forget as $key) {
                unset($query[$key]);
            }
            foreach ($extra as $key => $value) {
                if ($value === null || $value === '' || $value === 'todos' || $value === 'todas') {
                    unset($query[$key]);
                    continue;
                }
                $query[$key] = $value;
            }
            return url()->current() . (count($query) ? '?' . http_build_query($query) : '');
        };

        $auditAbas = ['resumo', 'timeline', 'aprovacoes', 'investigacao'];
        $aba = (string) request('aba', 'resumo');
        if (! in_array($aba, $auditAbas, true)) {
            $aba = 'resumo';
        }

        $formatAuditLabel = fn ($value) => \App\Support\AuditoriaFormatter::modulo((string) $value);
        $formatAuditValue = fn ($value, $field = null) => \App\Support\AuditoriaFormatter::valor($value, $field);
        $formatAuditEvent = fn ($value) => \App\Support\AuditoriaFormatter::evento((string) $value);
        $formatAuditRecord = fn ($type, $id) => \App\Support\AuditoriaFormatter::registroCurto((string) $type, $id);
        $historyUrl = function (array $event) use ($filterUrl) {
            return $filterUrl([
                'auditableType' => $event['auditable_type_filter'] ?? '',
                'auditableId' => $event['auditable_id_filter'] ?? '',
                'dateFilter' => 'todos',
            ], ['fromDate', 'toDate', 'searchFilter', 'userFilter', 'companyFilter', 'actionFilter']);
        };

        $detalheMetricas = $detalheMetricas ?? [];
        $detalheRecentes = $detalheRecentes ?? collect();
        $detalheSuspeitas = $detalheSuspeitas ?? collect();
        $detalheUsuarios = $detalheUsuarios ?? collect();
        $detalheModulos = $detalheModulos ?? collect();
        $detalheEmpresas = $detalheEmpresas ?? collect();
        $detalheEventos = $detalheEventos ?? collect();
    ?>

    <div class="compliance-page">
        <section class="compliance-hero">
            <div>
                <span><i class="bi bi-shield-check"></i> Auditoria e Rastreabilidade</span>
                <h1>Evidência, histórico e investigação</h1>
                <p>Esta aba não resolve pendências nem altera documentos. Ela mostra quem fez, quando fez, o que mudou e onde investigar o registro original.</p>
            </div>
            <div class="compliance-hero-actions compliance-hero-actions-export">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->canExportAuditoria()): ?>
                    <button type="button" class="compliance-export-button" wire:click="exportAuditoriaCsv" wire:loading.attr="disabled" wire:target="exportAuditoriaCsv"><i class="bi bi-filetype-csv"></i> Exportar CSV</button>
                    <button type="button" class="compliance-export-button compliance-export-button-primary" wire:click="exportAuditoriaExcel" wire:loading.attr="disabled" wire:target="exportAuditoriaExcel"><i class="bi bi-file-earmark-spreadsheet"></i> Exportar Excel</button>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <a href="<?php echo e($filterUrl(['aba' => 'investigacao'])); ?>"><i class="bi bi-search"></i> Ver investigação</a>
            </div>
        </section>


        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($aba === 'resumo'): ?>
            <section class="compliance-stats">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($data['stats'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <article class="compliance-stat"><span><?php echo e($stat['label']); ?></span><strong><?php echo e($stat['value']); ?></strong><small><?php echo e($stat['hint']); ?></small></article>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </section>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <section class="compliance-card compliance-filters">
            <header>
                <div>
                    <h2>Filtrar evidências de auditoria</h2>
                    <p>Use os filtros para localizar evidências. A ação operacional deve continuar na tela de origem do registro.</p>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasActiveFilters): ?>
                    <a class="compliance-link compliance-link-light" href="<?php echo e(url()->current()); ?>">Limpar filtros</a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </header>

            <form method="GET" action="<?php echo e(url()->current()); ?>" class="compliance-filter-grid compliance-filter-grid-advanced">
                <input type="hidden" name="aba" value="<?php echo e($aba); ?>">
                <label>
                    <span>Período rápido</span>
                    <select name="dateFilter">
                        <option value="7" <?php if($dateFilter === '7'): echo 'selected'; endif; ?>>Últimos 7 dias</option>
                        <option value="30" <?php if($dateFilter === '30'): echo 'selected'; endif; ?>>Últimos 30 dias</option>
                        <option value="90" <?php if($dateFilter === '90'): echo 'selected'; endif; ?>>Últimos 90 dias</option>
                        <option value="180" <?php if($dateFilter === '180'): echo 'selected'; endif; ?>>Últimos 180 dias</option>
                        <option value="365" <?php if($dateFilter === '365'): echo 'selected'; endif; ?>>Últimos 12 meses</option>
                        <option value="todos" <?php if($dateFilter === 'todos'): echo 'selected'; endif; ?>>Todo o histórico</option>
                    </select>
                </label>

                <label>
                    <span>Data inicial</span>
                    <input type="date" name="fromDate" value="<?php echo e($fromDate); ?>">
                </label>

                <label>
                    <span>Data final</span>
                    <input type="date" name="toDate" value="<?php echo e($toDate); ?>">
                </label>

                <label>
                    <span>Empresa</span>
                    <select name="companyFilter">
                        <option value="todas">Todas as empresas</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($filterOptions['companies'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $company): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <option value="<?php echo e($company['id']); ?>" <?php if($companyFilter === (string) $company['id']): echo 'selected'; endif; ?>><?php echo e($company['name']); ?></option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                </label>

                <label>
                    <span>Usuário</span>
                    <select name="userFilter">
                        <option value="todos">Todos os usuários</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($filterOptions['users'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <option value="<?php echo e($user['id']); ?>" <?php if($userFilter === (string) $user['id']): echo 'selected'; endif; ?>><?php echo e($user['name']); ?></option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                </label>

                <label>
                    <span>Tipo de ação</span>
                    <select name="actionFilter">
                        <option value="todas">Todos os eventos</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($filterOptions['actions'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <option value="<?php echo e($action); ?>" <?php if($actionFilter === (string) $action): echo 'selected'; endif; ?>><?php echo e($formatAuditEvent($action)); ?></option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </select>
                </label>

                <label class="wide">
                    <span>Buscar na auditoria</span>
                    <input type="search" name="searchFilter" value="<?php echo e($searchFilter); ?>" placeholder="Ex.: nome do usuário, empresa, status, IP, documento ou permissão">
                </label>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($auditableTypeFilter !== ''): ?>
                    <input type="hidden" name="auditableType" value="<?php echo e($auditableTypeFilter); ?>">
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($auditableIdFilter !== ''): ?>
                    <input type="hidden" name="auditableId" value="<?php echo e($auditableIdFilter); ?>">
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($historyContext['active'])): ?>
                    <div class="compliance-active-scope compliance-history-scope wide">
                        <div>
                            <span>Histórico completo por entidade</span>
                            <strong><?php echo e($historyContext['record_label'] ?? $formatAuditRecord($auditableTypeFilter, $auditableIdFilter)); ?></strong>
                            <small><?php echo e($historyContext['total'] ?? 0); ?> evento(s) no histórico · <?php echo e($historyContext['critical'] ?? 0); ?> crítico(s) · <?php echo e($historyContext['users'] ?? 0); ?> usuário(s)</small>
                        </div>
                        <a href="<?php echo e($filterUrl([], ['auditableType', 'auditableId'])); ?>">Remover foco do registro</a>
                    </div>
                <?php elseif($auditableTypeFilter !== '' || $auditableIdFilter !== ''): ?>
                    <div class="compliance-active-scope wide">
                        <div>
                            <span>Histórico focado</span>
                            <strong><?php echo e($formatAuditRecord($auditableTypeFilter, $auditableIdFilter)); ?></strong>
                        </div>
                        <a href="<?php echo e($filterUrl([], ['auditableType', 'auditableId'])); ?>">Remover foco do registro</a>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <div class="compliance-filter-actions wide">
                    <button type="submit">Aplicar filtros</button>
                    <a href="<?php echo e($filterUrl(['aba' => 'investigacao'])); ?>">Abrir visão limpa de investigação</a>
                </div>
            </form>
        </section>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($aba === 'resumo' && ! empty($historyContext['active'])): ?>
            <section class="compliance-card compliance-history-overview">
                <header>
                    <div>
                        <h2>Histórico completo do item</h2>
                        <p>Sequência completa de eventos registrados para <?php echo e($historyContext['record_label'] ?? ($historyContext['module'] ?? 'Registro')); ?>.</p>
                    </div>
                    <a class="compliance-link compliance-link-light" href="<?php echo e($filterUrl([], ['auditableType', 'auditableId'])); ?>">Voltar para auditoria geral</a>
                </header>
                <div class="compliance-history-metrics">
                    <article><span>Total de eventos</span><strong><?php echo e($historyContext['total'] ?? 0); ?></strong><small>Eventos vinculados ao mesmo registro</small></article>
                    <article><span>Eventos críticos</span><strong><?php echo e($historyContext['critical'] ?? 0); ?></strong><small>Classificados como alta criticidade</small></article>
                    <article><span>Usuários envolvidos</span><strong><?php echo e($historyContext['users'] ?? 0); ?></strong><small>Usuários que movimentaram o item</small></article>
                    <article><span>Período do histórico</span><strong><?php echo e($historyContext['first_date'] ?? '-'); ?></strong><small>até <?php echo e($historyContext['last_date'] ?? '-'); ?></small></article>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($historyContext['events'])): ?>
                    <div class="compliance-history-events">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $historyContext['events']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $historyEvent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <span><?php echo e($historyEvent['label']); ?> <strong><?php echo e($historyEvent['count']); ?></strong></span>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </section>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($aba === 'timeline'): ?>
        <section class="compliance-grid audit-single">
            <article class="compliance-card">
                <header><div><h2><?php echo e(! empty($historyContext['active']) ? 'Timeline do item selecionado' : 'Timeline de auditoria'); ?></h2><p><?php echo e(! empty($historyContext['active']) ? 'Histórico completo do registro focado, ordenado pelos eventos mais recentes.' : 'Últimos eventos reais registrados no banco' . ($hasActiveFilters ? ' conforme os filtros aplicados' : '') . '.'); ?></p></div></header>
                <div class="compliance-list">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($data['timeline'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="compliance-row compliance-row-actionable <?php echo e(! empty($event['alert']) ? 'is-alerted' : ''); ?>">
                            <div>
                                <div class="compliance-event-title-row">
                                    <h3><?php echo e($event['title']); ?></h3>
                                    <span class="compliance-criticality-badge is-<?php echo e($event['criticality_key'] ?? 'baixa'); ?>">Criticidade <?php echo e($event['criticality_label'] ?? 'Baixa'); ?></span>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($event['alert'])): ?>
                                        <span class="compliance-alert-badge">⚠ <?php echo e($event['alert_label'] ?? 'Alerta inteligente'); ?></span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                                <small><?php echo e($event['meta']); ?> · <?php echo e($event['date']); ?></small>
                                <div class="compliance-timeline-change is-<?php echo e($event['primary_change']['status'] ?? 'unchanged'); ?>">
                                    <div>
                                        <span><?php echo e($event['change_summary']['count_label'] ?? 'Alteração registrada'); ?></span>
                                        <strong><?php echo e($event['primary_change']['field'] ?? ($event['field'] ?? 'Campo')); ?></strong>
                                    </div>
                                    <p>
                                        <code><?php echo e($event['primary_change']['old'] ?? '—'); ?></code>
                                        <b>→</b>
                                        <code><?php echo e($event['primary_change']['new'] ?? '—'); ?></code>
                                    </p>
                                </div>
                                <div class="compliance-row-actions">
                                    <?php
                                        $eventModalId = 'audit-event-detail-' . md5((string) ($event['id'] ?? $loop->index));
                                    ?>
                                    <button
                                        type="button"
                                        class="compliance-detail-trigger"
                                        x-on:click.prevent.stop="$dispatch('open-modal', { id: '<?php echo e($eventModalId); ?>' })"
                                    >Ver detalhes</button>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($event['auditable_type_filter']) && ! empty($event['auditable_id_filter'])): ?>
                                        <a class="compliance-quick-link" href="<?php echo e($historyUrl($event)); ?>">Histórico deste item</a>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </div>
                            <span class="compliance-badge <?php echo e($event['tone'] ?? 'info'); ?>"><?php echo e($event['criticality_label'] ?? ($event['tone'] ?? 'info')); ?></span>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="compliance-empty">Nenhum evento de auditoria encontrado para os filtros informados.</div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </article>
        </section>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($aba === 'resumo'): ?>
        <section class="compliance-grid compliance-grid-summary">

            <div class="compliance-list">
                <article class="compliance-card">
                    <header><div><h2>Eventos por usuário</h2><p>Quem mais gerou movimentações.</p></div></header>
                    <div class="compliance-list">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($data['byUser'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <a class="compliance-row compliance-row-link" href="<?php echo e($filterUrl(['userFilter' => $row['id'] ?? 'sistema'])); ?>"><div><h3><?php echo e($row['label']); ?></h3><small>Clique para filtrar por este usuário</small></div><strong><?php echo e($row['count']); ?></strong></a>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <div class="compliance-empty">Sem dados por usuário.</div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </article>
                <article class="compliance-card">
                    <header><div><h2>Tipos de evento</h2><p>Distribuição dos eventos registrados.</p></div></header>
                    <div class="compliance-list">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($data['byEvent'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <a class="compliance-row compliance-row-link" href="<?php echo e($filterUrl(['actionFilter' => $row['id'] ?? ''])); ?>"><div><h3><?php echo e($row['label']); ?></h3><small>Clique para filtrar por este tipo</small></div><strong><?php echo e($row['count']); ?></strong></a>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <div class="compliance-empty">Sem tipos de evento.</div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </article>
            </div>
        </section>

        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($aba === 'aprovacoes'): ?>
            <section class="compliance-card">
                <header><div><h2>Aprovações recentes</h2><p>Decisões internas que ajudam a comprovar governança.</p></div></header>
            <div class="compliance-table-wrap"><table class="compliance-table"><thead><tr><th>Item</th><th>Empresa</th><th>Status</th><th>Observação</th><th>Data</th></tr></thead><tbody>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($data['recentApprovals'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <tr><td><strong><?php echo e($row['title']); ?></strong><br><small><?php echo e($row['meta']); ?></small></td><td><?php echo e(explode(' · ', $row['meta'])[0] ?? '-'); ?></td><td><span class="compliance-badge <?php echo e($row['tone']); ?>"><?php echo e($row['status']); ?></span></td><td><?php echo e($row['description']); ?></td><td><?php echo e($row['date']); ?></td></tr>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <tr><td colspan="5" class="compliance-empty">Nenhuma aprovação recente encontrada.</td></tr>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody></table></div>
            </section>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>


        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($aba === 'investigacao'): ?>
            <section id="auditoria-detalhada" class="ad-unified-shell">
            <div class="ad-unified-hero">
                <div>
                    <span><i class="bi bi-fingerprint"></i> Auditoria detalhada centralizada</span>
                    <h2>Tudo em uma única central</h2>
                    <p>Resumo executivo no topo, investigação logo abaixo e leitura em formato de timeline para o usuário entender rapidamente quem fez, quando fez, onde mexeu e o que mudou.</p>
                </div>
                <div class="ad-unified-actions">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->canExportAuditoria()): ?>
                        <button type="button" class="compliance-export-button compliance-export-button-primary" wire:click="exportAuditoriaExcel" wire:loading.attr="disabled" wire:target="exportAuditoriaExcel"><i class="bi bi-file-earmark-spreadsheet"></i> Exportar recorte</button>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($auditoriaDetalhadaManageUrl)): ?>
                        <a href="<?php echo e($auditoriaDetalhadaManageUrl); ?>" class="compliance-link compliance-link-light"><i class="bi bi-table"></i> Tabela avançada</a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            <section class="ad-metrics ad-metrics--five">
                <article class="ad-metric-card"><span>Total filtrado</span><strong><?php echo e(number_format((int) ($detalheMetricas['total'] ?? 0), 0, ',', '.')); ?></strong><small>Eventos dentro do recorte atual</small></article>
                <article class="ad-metric-card ad-metric-card--info"><span>Hoje</span><strong><?php echo e(number_format((int) ($detalheMetricas['hoje'] ?? 0), 0, ',', '.')); ?></strong><small>Movimentações do dia</small></article>
                <article class="ad-metric-card ad-metric-card--warning"><span>Alterações</span><strong><?php echo e(number_format((int) ($detalheMetricas['alteracoes'] ?? 0), 0, ',', '.')); ?></strong><small>Campos que mudaram</small></article>
                <article class="ad-metric-card ad-metric-card--danger"><span>Exclusões</span><strong><?php echo e(number_format((int) ($detalheMetricas['exclusoes'] ?? 0), 0, ',', '.')); ?></strong><small>Registros removidos</small></article>
                <article class="ad-metric-card ad-metric-card--critical"><span>Revisar</span><strong><?php echo e(number_format((int) ($detalheMetricas['sensiveis'] ?? 0), 0, ',', '.')); ?></strong><small>Senha, permissão, status, exportação ou falha</small></article>
            </section>

            <section class="ad-panel ad-panel--large ad-focus-panel">
                <div class="ad-panel-header">
                    <div>
                        <h3>Linha do tempo detalhada</h3>
                        <p>Leitura direta para investigação: usuário, empresa, módulo, registro, IP e comparação antes/depois.</p>
                    </div>
                    <div class="ad-panel-counter"><?php echo e(number_format((int) $detalheRecentes->count(), 0, ',', '.')); ?> recentes</div>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($detalheRecentes->isEmpty()): ?>
                    <div class="ad-empty">Nenhuma movimentação encontrada para os filtros aplicados.</div>
                <?php else: ?>
                    <div class="ad-timeline">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $detalheRecentes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $registro): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <div class="ad-timeline-item ad-timeline-item--ux">
                                <div class="ad-avatar" title="<?php echo e($registro->user?->name ?? 'Sistema'); ?>"><?php echo e($this->iniciaisUsuario($registro)); ?></div>
                                <div class="ad-timeline-content">
                                    <div class="ad-timeline-top">
                                        <div class="ad-timeline-badges">
                                            <span class="ad-badge <?php echo e($this->eventoClasse($registro->evento)); ?>"><?php echo e($formatAuditEvent($registro->evento)); ?></span>
                                            <span class="ad-badge <?php echo e($this->sensivelClasse($registro)); ?>"><?php echo e($this->sensivelLabel($registro)); ?></span>
                                        </div>
                                        <time><?php echo e($this->dataHumana($registro->created_at)); ?></time>
                                    </div>
                                    <h4><?php echo e($this->resumoAcao($registro)); ?></h4>
                                    <div class="ad-timeline-meta">
                                        <span>Usuário: <?php echo e($registro->user?->name ?? 'Sistema'); ?></span>
                                        <span>Empresa: <?php echo e($this->nomeEmpresa($registro)); ?></span>
                                        <span>Módulo: <?php echo e($formatAuditLabel($registro->auditable_type)); ?></span>
                                        <span>Registro: <?php echo e($formatAuditRecord($registro->auditable_type, $registro->auditable_id)); ?></span>
                                        <span>IP: <?php echo e($registro->ip ?: '-'); ?></span>
                                    </div>
                                    <div class="ad-diff ad-diff--ux">
                                        <div><span>Valor anterior</span><strong><?php echo e($this->valorRegistro($registro->valor_anterior, $registro->campo)); ?></strong></div>
                                        <div><span>Valor novo</span><strong><?php echo e($this->valorRegistro($registro->valor_novo, $registro->campo)); ?></strong></div>
                                    </div>
                                </div>
                            </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </section>

            <section class="ad-panel">
                <div class="ad-panel-header">
                    <div><h3>Ações que merecem revisão</h3><p>Eventos com maior impacto em segurança, permissões, status, exportações, exclusões e integrações.</p></div>
                    <div class="ad-panel-counter"><?php echo e(number_format((int) $detalheSuspeitas->count(), 0, ',', '.')); ?> recentes</div>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($detalheSuspeitas->isEmpty()): ?>
                    <div class="ad-empty">Nenhuma ação sensível encontrada no recorte atual.</div>
                <?php else: ?>
                    <div class="ad-sensitive-grid">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $detalheSuspeitas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $registro): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <article class="ad-sensitive-card">
                                <div class="ad-sensitive-top"><span class="ad-badge ad-badge--danger">Revisar</span><time><?php echo e(optional($registro->created_at)->format('d/m/Y H:i') ?: '-'); ?></time></div>
                                <h4><?php echo e($formatAuditRecord($registro->auditable_type, $registro->auditable_id)); ?></h4>
                                <p><?php echo e($this->resumoAcao($registro)); ?></p>
                                <div class="ad-timeline-meta"><span><?php echo e($registro->user?->name ?? 'Sistema'); ?></span><span><?php echo e($this->nomeEmpresa($registro)); ?></span></div>
                            </article>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </section>

            <section class="ad-layout ad-layout--balanced">
                <article class="ad-panel">
                    <div class="ad-panel-header"><div><h3>Eventos por tipo</h3><p>Distribuição das ações registradas no filtro atual.</p></div></div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($detalheEventos->isEmpty()): ?>
                        <div class="ad-empty">Nenhum evento encontrado.</div>
                    <?php else: ?>
                        <div class="ad-chart">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $detalheEventos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $evento): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <div class="ad-chart-row"><div class="ad-chart-label"><span class="ad-badge <?php echo e($evento['classe']); ?>"><?php echo e($evento['label']); ?></span></div><div class="ad-chart-track"><div class="ad-chart-bar" style="width: <?php echo e($evento['percentual']); ?>%"></div></div><strong><?php echo e(number_format((int) $evento['valor'], 0, ',', '.')); ?></strong></div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </article>
                <article class="ad-panel">
                    <div class="ad-panel-header"><div><h3>Usuários mais ativos</h3><p>Quem mais gerou movimentações.</p></div></div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($detalheUsuarios->isEmpty()): ?>
                        <div class="ad-empty">Sem usuários auditados.</div>
                    <?php else: ?>
                        <div class="ad-ranking">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $detalheUsuarios; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $usuario): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <div class="ad-ranking-row ad-ranking-row--stacked"><span><?php echo e($usuario['nome']); ?></span><small>Última ação: <?php echo e($usuario['ultima']); ?></small><strong><?php echo e(number_format((int) $usuario['total'], 0, ',', '.')); ?></strong></div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </article>
            </section>

            <section class="ad-layout ad-layout--balanced">
                <article class="ad-panel">
                    <div class="ad-panel-header"><div><h3>Auditoria por empresa</h3><p>Empresas com maior volume de rastreio.</p></div></div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($detalheEmpresas->isEmpty()): ?>
                        <div class="ad-empty">Nenhuma empresa encontrada.</div>
                    <?php else: ?>
                        <div class="ad-ranking">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $detalheEmpresas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $empresa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <div class="ad-ranking-row"><span><?php echo e($empresa['nome']); ?></span><strong><?php echo e(number_format((int) $empresa['total'], 0, ',', '.')); ?></strong></div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </article>
                <article class="ad-panel">
                    <div class="ad-panel-header"><div><h3>Áreas mais movimentadas</h3><p>Módulos do sistema com mais alterações.</p></div></div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($detalheModulos->isEmpty()): ?>
                        <div class="ad-empty">Nenhum módulo encontrado.</div>
                    <?php else: ?>
                        <div class="ad-ranking">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $detalheModulos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $modulo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <div class="ad-ranking-row"><span><?php echo e($modulo['nome']); ?></span><strong><?php echo e(number_format((int) $modulo['total'], 0, ',', '.')); ?></strong></div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </article>
            </section>
        </section>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>


        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($data['timeline'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $modalEventIndex => $modalEvent): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <?php
                $eventModalId = 'audit-event-detail-' . md5((string) ($modalEvent['id'] ?? $modalEventIndex));
                $eventDetailHistoryUrl = (! empty($modalEvent['auditable_type_filter']) && ! empty($modalEvent['auditable_id_filter'])) ? $historyUrl($modalEvent) : '#';
                $eventDetailEventFilterUrl = ! empty($modalEvent['event_raw']) ? $filterUrl(['actionFilter' => $modalEvent['event_raw']]) : '#';
                $eventDetailUserFilterUrl = isset($modalEvent['user_id']) ? $filterUrl(['userFilter' => $modalEvent['user_id'] ?: 'sistema']) : '#';
                $eventDetailCompanyFilterUrl = ! empty($modalEvent['company_id']) ? $filterUrl(['companyFilter' => $modalEvent['company_id']]) : '#';
                $eventDetailDiffRows = is_array($modalEvent['diff_rows'] ?? null) ? $modalEvent['diff_rows'] : [];
                $eventDetailStatusLabels = [
                    'added' => 'Adicionado',
                    'removed' => 'Removido',
                    'changed' => 'Alterado',
                    'unchanged' => 'Igual',
                ];
            ?>

            <?php if (isset($component)) { $__componentOriginal0942a211c37469064369f887ae8d1cef = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal0942a211c37469064369f887ae8d1cef = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament::components.modal.index','data' => ['id' => $eventModalId,'width' => '7xl','closeByClickingAway' => true,'closeByEscaping' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament::modal'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['id' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($eventModalId),'width' => '7xl','close-by-clicking-away' => true,'close-by-escaping' => true]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                 <?php $__env->slot('heading', null, []); ?> 
                    <?php echo e($formatAuditValue($modalEvent['title'] ?? 'Detalhes do evento')); ?>

                 <?php $__env->endSlot(); ?>

                 <?php $__env->slot('description', null, []); ?> 
                    <?php echo e($formatAuditValue($modalEvent['company'] ?? 'Sem empresa') . ' · ' . $formatAuditValue($modalEvent['user'] ?? 'Sistema') . ' · ' . $formatAuditValue($modalEvent['date_full'] ?? ($modalEvent['date'] ?? '-'))); ?>

                 <?php $__env->endSlot(); ?>

                <div class="compliance-modal-content-native">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($modalEvent['alert'])): ?>
                        <div class="compliance-alert-panel">
                            <div>
                                <span>Alerta inteligente</span>
                                <strong><?php echo e($modalEvent['alert_label'] ?? 'Comportamento suspeito detectado'); ?></strong>
                            </div>
                            <p><?php echo e($modalEvent['alert_description'] ?? 'Este evento combina padrões que merecem revisão pela governança.'); ?></p>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <div class="compliance-criticality-panel is-<?php echo e($modalEvent['criticality_key'] ?? 'baixa'); ?>">
                        <div>
                            <span>Criticidade do evento</span>
                            <strong><?php echo e($modalEvent['criticality_label'] ?? 'Baixa'); ?></strong>
                        </div>
                        <p><?php echo e($modalEvent['criticality_hint'] ?? 'Evento registrado para rastreabilidade.'); ?></p>
                    </div>

                    <div class="compliance-detail-grid">
                        <article>
                            <span>Evento</span>
                            <strong><?php echo e($formatAuditValue($modalEvent['event_label'] ?? '-')); ?></strong>
                            <small>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($eventDetailEventFilterUrl !== '#'): ?>
                                    <a class="compliance-inline-link" href="<?php echo e($eventDetailEventFilterUrl); ?>">Filtrar por evento</a>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </small>
                        </article>
                        <article><span>Módulo</span><strong><?php echo e($formatAuditLabel($modalEvent['module'] ?? '-')); ?></strong><small><?php echo e($formatAuditLabel($modalEvent['auditable_type'] ?? '-')); ?></small></article>
                        <article>
                            <span>Registro</span>
                            <strong><?php echo e($modalEvent['auditable_id'] ?? '-'); ?></strong>
                            <small>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($eventDetailHistoryUrl !== '#'): ?>
                                    <a class="compliance-inline-link" href="<?php echo e($eventDetailHistoryUrl); ?>">Ver histórico completo</a>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </small>
                        </article>
                        <article><span>Campo</span><strong><?php echo e($formatAuditLabel($modalEvent['field'] ?? '-')); ?></strong><small>Campo auditado</small></article>
                        <article>
                            <span>Usuário</span>
                            <strong><?php echo e($formatAuditValue($modalEvent['user'] ?? 'Sistema')); ?></strong>
                            <small>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($eventDetailUserFilterUrl !== '#'): ?>
                                    <a class="compliance-inline-link" href="<?php echo e($eventDetailUserFilterUrl); ?>">Filtrar por usuário</a>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </small>
                        </article>
                        <article>
                            <span>Empresa</span>
                            <strong><?php echo e($formatAuditValue($modalEvent['company'] ?? 'Sem empresa')); ?></strong>
                            <small>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($eventDetailCompanyFilterUrl !== '#'): ?>
                                    <a class="compliance-inline-link" href="<?php echo e($eventDetailCompanyFilterUrl); ?>">Filtrar por empresa</a>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </small>
                        </article>
                        <article><span>IP</span><strong><?php echo e($formatAuditValue($modalEvent['ip'] ?? '-')); ?></strong><small>Origem registrada</small></article>
                        <article><span>Data e hora</span><strong><?php echo e($formatAuditValue($modalEvent['date_full'] ?? ($modalEvent['date'] ?? '-'))); ?></strong><small>Momento exato do evento</small></article>
                    </div>

                    <div class="compliance-detail-values">
                        <article>
                            <header>Valor anterior</header>
                            <pre><?php echo e($formatAuditValue($modalEvent['old_value'] ?? null)); ?></pre>
                        </article>
                        <article>
                            <header>Valor novo</header>
                            <pre><?php echo e($formatAuditValue($modalEvent['new_value'] ?? null)); ?></pre>
                        </article>
                    </div>

                    <div class="compliance-diff-box">
                        <div class="compliance-diff-header">
                            <div>
                                <span>Antes vs Depois</span>
                                <h3>Comparação campo a campo</h3>
                            </div>
                            <strong class="<?php echo e(! empty($modalEvent['has_changes']) ? 'has-change' : 'no-change'); ?>"><?php echo e(! empty($modalEvent['has_changes']) ? 'Com alteração' : 'Sem alteração detectada'); ?></strong>
                        </div>

                        <div class="compliance-diff-table-wrap">
                            <table class="compliance-diff-table">
                                <thead>
                                <tr>
                                    <th>Campo</th>
                                    <th>Antes</th>
                                    <th>Depois</th>
                                    <th>Status</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $eventDetailDiffRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $diff): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <?php $diffStatus = $diff['status'] ?? 'unchanged'; ?>
                                    <tr class="is-<?php echo e($diffStatus); ?>">
                                        <td><strong><?php echo e($formatAuditLabel($diff['field'] ?? '-')); ?></strong></td>
                                        <td><code><?php echo e($formatAuditValue($diff['old'] ?? null)); ?></code></td>
                                        <td><code><?php echo e($formatAuditValue($diff['new'] ?? null)); ?></code></td>
                                        <td><span><?php echo e($eventDetailStatusLabels[$diffStatus] ?? 'Igual'); ?></span></td>
                                    </tr>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    <tr><td colspan="4" class="compliance-empty">Nenhum dado disponível para comparação.</td></tr>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($modalEvent['user_agent'])): ?>
                        <details class="compliance-detail-agent">
                            <summary>Informações técnicas</summary>
                            <div>
                                <span>User agent</span>
                                <p><?php echo e($modalEvent['user_agent']); ?></p>
                            </div>
                        </details>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <div class="compliance-modal-footer-actions">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($eventDetailHistoryUrl !== '#'): ?>
                            <a class="compliance-modal-primary-action" href="<?php echo e($eventDetailHistoryUrl); ?>">Ver histórico completo deste item</a>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <a class="compliance-modal-secondary-action" href="<?php echo e($filterUrl(['aba' => 'investigacao'])); ?>">Ir para auditoria detalhada</a>
                    </div>
                </div>
             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal0942a211c37469064369f887ae8d1cef)): ?>
<?php $attributes = $__attributesOriginal0942a211c37469064369f887ae8d1cef; ?>
<?php unset($__attributesOriginal0942a211c37469064369f887ae8d1cef); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal0942a211c37469064369f887ae8d1cef)): ?>
<?php $component = $__componentOriginal0942a211c37469064369f887ae8d1cef; ?>
<?php unset($__componentOriginal0942a211c37469064369f887ae8d1cef); ?>
<?php endif; ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
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
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views/filament/pages/compliance-auditoria.blade.php ENDPATH**/ ?>