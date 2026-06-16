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

    <link rel="stylesheet" href="<?php echo e(asset('css/compliance-module.css')); ?>?v=<?php echo e(file_exists(public_path('css/compliance-module.css')) ? filemtime(public_path('css/compliance-module.css')) : time()); ?>">

    <?php
        $score = (int)($data['score'] ?? 0);
        $tone = $score >= 80 ? 'ok' : ($score >= 60 ? 'warning' : 'danger');
        $stats = collect($data['stats'] ?? []);
        $criticalRisks = collect($data['criticalRisks'] ?? []);
        $latePendings = collect($data['latePendings'] ?? []);
        $recommendations = collect($data['recommendations'] ?? []);

        $statValue = function (string $label) use ($stats) {
            $stat = $stats->first(fn ($item) => ($item['label'] ?? '') === $label);
            return $stat['value'] ?? 0;
        };

        $criticalCount = (int) $statValue('Riscos críticos');
        $lateCount = (int) $statValue('Pendências vencidas');
        $auditCount = (int) $statValue('Eventos auditados');

        $scoreMeta = match ($tone) {
            'ok' => [
                'label' => 'Operação saudável',
                'headline' => 'A governança está sob controle.',
                'description' => 'O score indica boa aderência operacional. Mantenha a rotina de revisão para evitar acúmulo de riscos e pendências.',
                'instruction' => 'Monitore riscos, auditoria e evidências para manter o padrão atual.',
            ],
            'warning' => [
                'label' => 'Atenção necessária',
                'headline' => 'Existem pontos que precisam de acompanhamento.',
                'description' => 'O compliance ainda está administrável, mas riscos críticos, vencimentos ou falta de evidência podem piorar rapidamente o cenário.',
                'instruction' => 'Priorize riscos críticos e pendências vencidas antes de revisar os demais itens.',
            ],
            default => [
                'label' => 'Risco operacional alto',
                'headline' => 'A situação exige ação imediata.',
                'description' => 'O volume de riscos críticos ou pendências vencidas está impactando diretamente a saúde do compliance.',
                'instruction' => 'Resolva primeiro os itens críticos e vencidos destacados nesta página.',
            ],
        };

        $mainAttention = $lateCount > 0
            ? 'Pendências vencidas estão derrubando o score e devem ser tratadas primeiro.'
            : ($criticalCount > 0
                ? 'Riscos críticos exigem decisão rápida para evitar impacto operacional.'
                : 'Nenhum bloqueio crítico foi identificado neste momento.');

        $statTone = function (string $label, $value) {
            $normalized = mb_strtolower((string) $label);
            $numeric = (int) preg_replace('/[^0-9]/', '', (string) $value);

            if (str_contains($normalized, 'score')) {
                return $numeric >= 80 ? 'ok' : ($numeric >= 60 ? 'warning' : 'danger');
            }

            if (str_contains($normalized, 'crítico') || str_contains($normalized, 'vencida')) {
                return $numeric > 0 ? 'danger' : 'ok';
            }

            return 'info';
        };

        $priorityItems = $latePendings
            ->map(fn ($item) => [
                'type' => 'Pendência vencida',
                'tone' => 'danger',
                'title' => $item['titulo'] ?? 'Pendência sem título',
                'empresa' => $item['empresa'] ?? 'Empresa não informada',
                'responsavel' => $item['responsavel'] ?? 'Responsável não informado',
                'vencimento' => $item['vencimento'] ?? 'Sem vencimento informado',
                'url' => $item['url'] ?? '#',
                'reason' => 'Está vencida e impacta diretamente a saúde do compliance.',
                'action' => 'Resolver agora',
                'order' => 1,
            ])
            ->merge(
                $criticalRisks->map(fn ($risk) => [
                    'type' => 'Risco crítico',
                    'tone' => 'danger',
                    'title' => $risk['titulo'] ?? 'Risco sem título',
                    'empresa' => $risk['empresa'] ?? 'Empresa não informada',
                    'responsavel' => $risk['responsavel'] ?? 'Responsável não informado',
                    'vencimento' => $risk['vencimento'] ?? 'Sem vencimento informado',
                    'url' => $risk['url'] ?? '#',
                    'reason' => 'Possui criticidade alta e pode gerar impacto operacional.',
                    'action' => 'Analisar risco',
                    'order' => 2,
                ])
            )
            ->sortBy('order')
            ->take(6)
            ->values();

        $prioritySummary = $lateCount > 0
            ? 'Comece pelas pendências vencidas: elas representam o bloqueio mais objetivo para recuperar o score.'
            : ($criticalCount > 0
                ? 'Comece pelos riscos críticos: eles concentram o maior potencial de impacto operacional.'
                : 'Nenhuma ação emergencial foi encontrada. Mantenha a revisão preventiva dos indicadores.');

        $safePageUrl = function (string $class, ?string $fallback = null): string {
            if (class_exists($class) && method_exists($class, 'getUrl')) {
                try {
                    return $class::getUrl();
                } catch (Throwable $exception) {
                    return $fallback ?: url('/admin');
                }
            }

            return $fallback ?: url('/admin');
        };

        $safeResourceUrl = function (string $class, string $page = 'index', array $parameters = [], ?string $fallback = null): string {
            if (class_exists($class) && method_exists($class, 'getUrl')) {
                try {
                    return $class::getUrl($page, $parameters);
                } catch (Throwable $exception) {
                    return $fallback ?: url('/admin');
                }
            }

            return $fallback ?: url('/admin');
        };

        $pendenciasUrl = $safePageUrl(\App\Filament\Pages\Pendencias::class, url('/admin/pendencias'));
        $riscosUrl = $safePageUrl(\App\Filament\Pages\Riscos::class, url('/admin/riscos'));
        $auditoriaUrl = $safePageUrl(\App\Filament\Pages\Auditoria::class, url('/admin/auditoria'));
        $itensUrl = $safeResourceUrl(\App\Filament\Resources\ItemControles\ItemControleResource::class, 'index', [], url('/admin/item-controles'));
        $minhasPendenciasUrl = $pendenciasUrl;

        $primaryAction = $lateCount > 0
            ? ['label' => 'Resolver pendências vencidas', 'url' => $pendenciasUrl, 'tone' => 'danger', 'helper' => 'Abrir a página de pendências filtrada pela rotina de compliance.']
            : ($criticalCount > 0
                ? ['label' => 'Analisar riscos críticos', 'url' => $riscosUrl, 'tone' => 'danger', 'helper' => 'Abrir a página de riscos para tratar os itens de maior impacto.']
                : ['label' => 'Revisar itens de controle', 'url' => $itensUrl, 'tone' => 'ok', 'helper' => 'Abrir a listagem geral para manutenção preventiva.']);

        $actionCards = collect([
            [
                'title' => 'Resolver pendências',
                'description' => $lateCount > 0 ? 'Atue nos atrasos que mais prejudicam o score.' : 'Confira pendências abertas antes que virem atraso.',
                'url' => $pendenciasUrl,
                'label' => $lateCount > 0 ? 'Ir para pendências vencidas' : 'Ver pendências',
                'tone' => $lateCount > 0 ? 'danger' : 'info',
                'count' => $lateCount,
            ],
            [
                'title' => 'Analisar riscos',
                'description' => $criticalCount > 0 ? 'Priorize riscos críticos e registre a decisão operacional.' : 'Faça uma revisão preventiva dos riscos cadastrados.',
                'url' => $riscosUrl,
                'label' => $criticalCount > 0 ? 'Ir para riscos críticos' : 'Ver riscos',
                'tone' => $criticalCount > 0 ? 'danger' : 'info',
                'count' => $criticalCount,
            ],
            [
                'title' => 'Abrir minhas tarefas',
                'description' => 'Veja itens direcionados ao usuário logado e reduza o caminho até a execução.',
                'url' => $minhasPendenciasUrl,
                'label' => 'Ver minhas pendências',
                'tone' => 'warning',
                'count' => null,
            ],
            [
                'title' => 'Conferir auditoria',
                'description' => 'Valide rastreabilidade, evidências e movimentações recentes.',
                'url' => $auditoriaUrl,
                'label' => 'Abrir auditoria',
                'tone' => 'info',
                'count' => $auditCount,
            ],
        ]);

        $trend = collect($data['trend'] ?? []);
        $trendCards = collect($trend->get('cards', []));
        $trendTone = $trend->get('tone', 'info');
        $trendDeltaLabel = function ($delta): string {
            if (is_null($delta)) {
                return 'Base atual';
            }

            $numericDelta = (int) $delta;

            if ($numericDelta === 0) {
                return 'Sem variação';
            }

            return ($numericDelta > 0 ? '+' : '') . $numericDelta;
        };
    ?>

    <div class="compliance-page compliance-engine-page">
        <section class="compliance-hero compliance-engine-hero <?php echo e($tone); ?>">
            <div class="compliance-hero-copy">
                <span>Saúde do Compliance</span>
                <h1>Visão clara da governança agora</h1>
                <p><?php echo e($scoreMeta['headline']); ?> <?php echo e($scoreMeta['description']); ?></p>
            </div>

            <aside class="compliance-hero-diagnosis" aria-label="Diagnóstico atual do compliance">
                <small>Diagnóstico atual</small>
                <strong><?php echo e($scoreMeta['label']); ?></strong>
                <div class="compliance-hero-score"><?php echo e($score); ?>%</div>
                <p><?php echo e($scoreMeta['instruction']); ?></p>
            </aside>
        </section>

        <section class="compliance-decision-strip <?php echo e($tone); ?>">
            <div>
                <span>Leitura executiva</span>
                <strong><?php echo e($mainAttention); ?></strong>
            </div>
            <div class="compliance-decision-pills">
                <span class="<?php echo e($criticalCount > 0 ? 'danger' : 'ok'); ?>"><?php echo e($criticalCount); ?> risco(s) crítico(s)</span>
                <span class="<?php echo e($lateCount > 0 ? 'danger' : 'ok'); ?>"><?php echo e($lateCount); ?> pendência(s) vencida(s)</span>
                <span class="info"><?php echo e($auditCount); ?> evento(s) auditado(s)</span>
            </div>
        </section>

        <section class="compliance-action-hub <?php echo e($primaryAction['tone']); ?>" aria-label="Ações diretas do Compliance Engine">
            <div class="compliance-action-hub-main">
                <span>Próxima ação recomendada</span>
                <strong><?php echo e($primaryAction['label']); ?></strong>
                <p><?php echo e($primaryAction['helper']); ?></p>
            </div>
            <a class="compliance-action-hub-button <?php echo e($primaryAction['tone']); ?>" href="<?php echo e($primaryAction['url']); ?>">
                Executar agora
            </a>
        </section>

        <section class="compliance-action-grid" aria-label="Atalhos operacionais">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $actionCards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <article class="compliance-action-card <?php echo e($action['tone']); ?>">
                    <div>
                        <span><?php echo e(is_null($action['count']) ? 'Atalho' : $action['count'] . ' item(ns)'); ?></span>
                        <strong><?php echo e($action['title']); ?></strong>
                        <p><?php echo e($action['description']); ?></p>
                    </div>
                    <a href="<?php echo e($action['url']); ?>"><?php echo e($action['label']); ?></a>
                </article>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </section>

        <section class="compliance-stats compliance-engine-stats">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $stats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php $currentTone = $statTone($stat['label'] ?? '', $stat['value'] ?? 0); ?>
                <article class="compliance-stat compliance-stat-clarity <?php echo e($currentTone); ?>">
                    <span><?php echo e($stat['label']); ?></span>
                    <strong><?php echo e($stat['value']); ?></strong>
                    <small><?php echo e($stat['hint']); ?></small>
                </article>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </section>

        <section class="compliance-card compliance-trend-panel <?php echo e($trendTone); ?>" aria-label="Evolução operacional do Compliance Engine">
            <header>
                <div>
                    <span class="compliance-section-kicker">Evolução operacional</span>
                    <h2><?php echo e($trend->get('label', 'Leitura dos últimos dias')); ?></h2>
                    <p><?php echo e($trend->get('summary', 'Acompanhe sinais recentes de pressão operacional, auditoria e evidências.')); ?></p>
                </div>
                <span class="compliance-badge <?php echo e($trendTone); ?>"><?php echo e($trend->get('period', 'Últimos 7 dias')); ?></span>
            </header>

            <div class="compliance-trend-grid">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $trendCards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $card): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <article class="compliance-trend-card <?php echo e($card['tone'] ?? 'info'); ?>">
                        <span><?php echo e($card['title'] ?? 'Indicador'); ?></span>
                        <strong><?php echo e($card['value'] ?? 0); ?></strong>
                        <div class="compliance-trend-delta <?php echo e($card['tone'] ?? 'info'); ?>">
                            <b><?php echo e($trendDeltaLabel($card['delta'] ?? null)); ?></b>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! is_null($card['previous'] ?? null)): ?>
                                <small>vs <?php echo e($card['previous']); ?> no período anterior</small>
                            <?php else: ?>
                                <small><?php echo e($trend->get('comparisonPeriod', 'Comparativo')); ?></small>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <p><?php echo e($card['hint'] ?? ''); ?></p>
                    </article>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <div class="compliance-empty">Não foi possível calcular sinais recentes para este perfil.</div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <footer class="compliance-trend-note">
                <?php echo e($trend->get('note', 'Indicadores calculados em tempo real com os dados disponíveis.')); ?>

            </footer>
        </section>

        <section class="compliance-grid compliance-engine-main-grid">
            <article class="compliance-card compliance-score compliance-score-clarity <?php echo e($tone); ?>">
                <div>
                    <span class="compliance-muted">Score atual</span>
                    <strong><?php echo e($score); ?>%</strong>
                    <p><?php echo e($scoreMeta['label']); ?></p>
                    <small><?php echo e($scoreMeta['instruction']); ?></small>
                </div>
            </article>

            <article class="compliance-card compliance-score-guide">
                <header>
                    <div>
                        <h2>Como interpretar este painel</h2>
                        <p>Use esta leitura para saber se a operação está saudável, em atenção ou em risco.</p>
                    </div>
                </header>

                <div class="compliance-score-bands" aria-label="Faixas de interpretação do score">
                    <div class="ok">
                        <strong>80% a 100%</strong>
                        <span>Saudável</span>
                        <small>Manter rotina de revisão e evidências.</small>
                    </div>
                    <div class="warning">
                        <strong>60% a 79%</strong>
                        <span>Atenção</span>
                        <small>Priorizar atrasos e riscos com maior impacto.</small>
                    </div>
                    <div class="danger">
                        <strong>0% a 59%</strong>
                        <span>Crítico</span>
                        <small>Atuar imediatamente nos itens destacados.</small>
                    </div>
                </div>
            </article>
        </section>

        <section class="compliance-card compliance-priority-board <?php echo e($priorityItems->isNotEmpty() ? 'has-items' : 'is-clear'); ?>">
            <header>
                <div>
                    <span class="compliance-section-kicker">O que precisa da sua atenção agora</span>
                    <h2>Fila de prioridade operacional</h2>
                    <p><?php echo e($prioritySummary); ?></p>
                </div>
                <span class="compliance-badge <?php echo e($priorityItems->isNotEmpty() ? 'danger' : 'ok'); ?>">
                    <?php echo e($priorityItems->count()); ?> item(ns) prioritário(s)
                </span>
            </header>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($priorityItems->isNotEmpty()): ?>
                <div class="compliance-priority-list">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $priorityItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <article class="compliance-priority-item <?php echo e($item['tone']); ?>">
                            <div class="compliance-priority-rank" aria-label="Prioridade <?php echo e($index + 1); ?>"><?php echo e($index + 1); ?></div>
                            <div class="compliance-priority-content">
                                <div class="compliance-priority-heading">
                                    <span class="compliance-priority-type <?php echo e($item['tone']); ?>"><?php echo e($item['type']); ?></span>
                                    <strong><?php echo e($item['title']); ?></strong>
                                </div>
                                <p><?php echo e($item['reason']); ?></p>
                                <small><?php echo e($item['empresa']); ?> · <?php echo e($item['responsavel']); ?> · <?php echo e($item['vencimento']); ?></small>
                            </div>
                            <a class="compliance-priority-action" href="<?php echo e($item['url']); ?>" aria-label="<?php echo e($item['action']); ?>: <?php echo e($item['title']); ?>"><?php echo e($item['action']); ?></a>
                        </article>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            <?php else: ?>
                <div class="compliance-priority-empty">
                    <strong>Nenhuma urgência crítica no momento.</strong>
                    <span>Use os cards abaixo para manter a rotina de acompanhamento e evitar novos atrasos.</span>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </section>


        <section class="compliance-card compliance-recommendations-focus">
            <header>
                <div>
                    <h2>Prioridade de leitura</h2>
                    <p>O sistema organiza abaixo os pontos que explicam o score e orientam a próxima análise.</p>
                </div>
                <span class="compliance-badge <?php echo e($tone); ?>"><?php echo e($scoreMeta['label']); ?></span>
            </header>

            <div class="compliance-list">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $recommendations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rec): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <div class="compliance-row compliance-row-clarity <?php echo e($rec['tone'] ?? 'info'); ?>">
                        <div>
                            <h3><?php echo e($rec['title']); ?></h3>
                            <p><?php echo e($rec['description']); ?></p>
                        </div>
                        <span class="compliance-badge <?php echo e($rec['tone'] ?? 'info'); ?>">Prioridade</span>
                    </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <div class="compliance-empty">Nenhuma recomendação crítica agora.</div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </section>

        <section class="compliance-grid equal compliance-engine-lists">
            <article class="compliance-card">
                <header>
                    <div>
                        <h2>Riscos críticos</h2>
                        <p>Itens que mais impactam o score e merecem análise antes dos demais.</p>
                    </div>
                    <div class="compliance-card-actions"><span class="compliance-badge <?php echo e($criticalCount > 0 ? 'danger' : 'ok'); ?>"><?php echo e($criticalCount); ?> encontrado(s)</span><a href="<?php echo e($riscosUrl); ?>">Ver todos</a></div>
                </header>

                <div class="compliance-list">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $criticalRisks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $risk): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="compliance-row">
                            <div>
                                <h3><?php echo e($risk['titulo']); ?></h3>
                                <small><?php echo e($risk['empresa']); ?> · <?php echo e($risk['responsavel']); ?> · <?php echo e($risk['vencimento']); ?></small>
                            </div>
                            <a class="compliance-link" href="<?php echo e($risk['url']); ?>">Abrir</a>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="compliance-empty">Nenhum risco crítico encontrado.</div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </article>

            <article class="compliance-card">
                <header>
                    <div>
                        <h2>Pendências vencidas</h2>
                        <p>Atrasos que reduzem a saúde do compliance e precisam de decisão.</p>
                    </div>
                    <div class="compliance-card-actions"><span class="compliance-badge <?php echo e($lateCount > 0 ? 'danger' : 'ok'); ?>"><?php echo e($lateCount); ?> vencida(s)</span><a href="<?php echo e($pendenciasUrl); ?>">Ver todas</a></div>
                </header>

                <div class="compliance-list">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $latePendings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="compliance-row">
                            <div>
                                <h3><?php echo e($item['titulo']); ?></h3>
                                <small><?php echo e($item['empresa']); ?> · <?php echo e($item['responsavel']); ?> · <?php echo e($item['vencimento']); ?></small>
                            </div>
                            <a class="compliance-link" href="<?php echo e($item['url']); ?>">Abrir</a>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="compliance-empty">Nenhuma pendência vencida.</div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </article>
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
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views\filament\pages\compliance-engine.blade.php ENDPATH**/ ?>