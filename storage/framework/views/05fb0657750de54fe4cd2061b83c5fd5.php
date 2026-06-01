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

    <link rel="stylesheet" href="<?php echo e(asset('css/inteligencia-produto.css')); ?>">

    <section class="pi-page">
        <header class="pi-card pi-hero">
            <div class="pi-hero__content">
                <span class="pi-eyebrow">Módulo interno exclusivo do super admin</span>
                <h2 class="pi-title">Inteligência do Produto sem IA externa</h2>
                <p class="pi-description">
                    Arquive comentários de concorrentes, gere relatórios heurísticos, veja oportunidades de UX, SEO e roadmap, e exporte um prompt pronto para enviar ao ChatGPT. Nenhuma API de IA é chamada nesta versão.
                </p>
            </div>

            <div class="pi-actions pi-actions--hero">
                <button type="button" wire:click="generateReport" class="pi-btn pi-btn--secondary">Atualizar relatório</button>
                <button type="button" wire:click="exportJson" class="pi-btn pi-btn--info">Baixar JSON</button>
                <button type="button" wire:click="exportPrompt" class="pi-btn pi-btn--success">Baixar prompt</button>
            </div>
        </header>

        <div class="pi-kpi-grid">
            <article class="pi-kpi-card pi-kpi-card--featured">
                <span class="pi-kpi-card__label">Product Health</span>
                <strong class="pi-kpi-card__value"><?php echo e(data_get($report, 'summary.product_health_score', 100)); ?>/100</strong>
            </article>

            <article class="pi-kpi-card">
                <span class="pi-kpi-card__label">Comentários</span>
                <strong class="pi-kpi-card__value"><?php echo e(data_get($report, 'summary.market_comments_total', 0)); ?></strong>
            </article>

            <article class="pi-kpi-card">
                <span class="pi-kpi-card__label">Negativos</span>
                <strong class="pi-kpi-card__value pi-kpi-card__value--danger"><?php echo e(data_get($report, 'summary.negative_comments_total', 0)); ?></strong>
            </article>

            <article class="pi-kpi-card">
                <span class="pi-kpi-card__label">Positivos</span>
                <strong class="pi-kpi-card__value pi-kpi-card__value--success"><?php echo e(data_get($report, 'summary.positive_comments_total', 0)); ?></strong>
            </article>

            <article class="pi-kpi-card">
                <span class="pi-kpi-card__label">Mistos</span>
                <strong class="pi-kpi-card__value pi-kpi-card__value--warning"><?php echo e(data_get($report, 'summary.mixed_comments_total', 0)); ?></strong>
            </article>

            <article class="pi-kpi-card">
                <span class="pi-kpi-card__label">Neutros</span>
                <strong class="pi-kpi-card__value"><?php echo e(data_get($report, 'summary.neutral_comments_total', 0)); ?></strong>
            </article>

            <article class="pi-kpi-card">
                <span class="pi-kpi-card__label">Problemas críticos</span>
                <strong class="pi-kpi-card__value pi-kpi-card__value--danger"><?php echo e(data_get($report, 'summary.critical_problems_total', 0)); ?></strong>
            </article>

            <article class="pi-kpi-card">
                <span class="pi-kpi-card__label">Oportunidades</span>
                <strong class="pi-kpi-card__value pi-kpi-card__value--info"><?php echo e(data_get($report, 'summary.opportunities_total', 0)); ?></strong>
            </article>

            <article class="pi-kpi-card">
                <span class="pi-kpi-card__label">Pontos fortes</span>
                <strong class="pi-kpi-card__value pi-kpi-card__value--success"><?php echo e(data_get($report, 'summary.strength_categories_total', 0)); ?></strong>
            </article>

            <article class="pi-kpi-card">
                <span class="pi-kpi-card__label">Aprendizados</span>
                <strong class="pi-kpi-card__value pi-kpi-card__value--warning"><?php echo e(data_get($report, 'summary.market_learnings_total', 0)); ?></strong>
            </article>
        </div>

        <article class="pi-card pi-executive-card">
            <div class="pi-section-header pi-section-header--row">
                <div>
                    <h3 class="pi-section-title">Resumo executivo</h3>
                    <p class="pi-section-text">A primeira decisão sugerida aparece aqui para o super admin bater o olho e saber onde agir.</p>
                </div>
                <span class="pi-badge pi-badge--info">Período: <?php echo e(data_get($report, 'period_days', $periodDays)); ?> dias</span>
            </div>

            <?php ($firstProblem = data_get($report, 'top_problems.0')); ?>
            <?php ($firstRoadmap = data_get($report, 'recommended_roadmap.0')); ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($firstProblem): ?>
                <div class="pi-executive-grid">
                    <div class="pi-executive-panel">
                        <span class="pi-eyebrow">Maior risco detectado</span>
                        <h4 class="pi-executive-title"><?php echo e($firstProblem['category']); ?></h4>
                        <p class="pi-section-text"><strong>Dor real:</strong> <?php echo e($firstProblem['real_pain'] ?? 'Não classificada'); ?></p>
                    </div>

                    <div class="pi-executive-panel">
                        <span class="pi-eyebrow">Ação recomendada agora</span>
                        <h4 class="pi-executive-title"><?php echo e($firstRoadmap['what_to_do'] ?? ($firstProblem['recommended_action'] ?? 'Analisar comentários importados')); ?></h4>
                        <p class="pi-section-text"><strong>Evitar:</strong> <?php echo e($firstRoadmap['what_not_to_do'] ?? ($firstProblem['what_not_to_do'] ?? 'Não decidir sem evidência')); ?></p>
                    </div>

                    <div class="pi-executive-panel">
                        <span class="pi-eyebrow">Evidência</span>
                        <h4 class="pi-executive-title"><?php echo e($firstProblem['total']); ?> ocorrência(s)</h4>
                        <p class="pi-section-text"><?php echo e($firstProblem['negative_total']); ?> negativa(s), confiança <?php echo e($firstProblem['confidence'] ?? 'baixa'); ?>, score <?php echo e($firstProblem['priority_score'] ?? 0); ?>.</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="pi-empty-state">Importe comentários para o sistema gerar a primeira recomendação executiva.</div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </article>

        <div class="pi-main-grid">
            <article class="pi-card pi-import-card">
                <div class="pi-section-header">
                    <h3 class="pi-section-title">Importar comentários</h3>
                    <p class="pi-section-text">Cole uma avaliação/post por vez ou separe comentários diferentes com --- em uma linha. O sistema arquiva, identifica categoria, sentimento, dor real, insight, aprendizado, ponto forte, impacto e ação recomendada.</p>
                </div>

                <div class="pi-form">
                    <div class="pi-form-grid pi-form-grid--two">
                        <label class="pi-field">
                            <span class="pi-label">Fonte</span>
                            <input type="text" wire:model.defer="sourceName" class="pi-input" placeholder="Reddit, Google Reviews, YouTube...">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['sourceName'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="pi-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </label>

                        <label class="pi-field">
                            <span class="pi-label">Concorrente</span>
                            <input type="text" wire:model.defer="competitorName" class="pi-input" placeholder="ClickUp, Asana, Monday...">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['competitorName'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="pi-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </label>
                    </div>

                    <div class="pi-form-grid pi-form-grid--three">
                        <label class="pi-field">
                            <span class="pi-label">Tipo</span>
                            <select wire:model.defer="sourceType" class="pi-input">
                                <option value="reddit">Reddit</option>
                                <option value="google_reviews">Google Reviews</option>
                                <option value="youtube">YouTube</option>
                                <option value="g2">G2</option>
                                <option value="capterra">Capterra</option>
                                <option value="app_store">App Store</option>
                                <option value="play_store">Play Store</option>
                                <option value="manual">Manual</option>
                            </select>
                        </label>

                        <label class="pi-field">
                            <span class="pi-label">Nota</span>
                            <select wire:model.defer="rating" class="pi-input">
                                <option value="">Sem nota</option>
                                <option value="1">1 estrela</option>
                                <option value="2">2 estrelas</option>
                                <option value="3">3 estrelas</option>
                                <option value="4">4 estrelas</option>
                                <option value="5">5 estrelas</option>
                            </select>
                        </label>

                        <label class="pi-field">
                            <span class="pi-label">Idioma</span>
                            <input type="text" wire:model.defer="language" class="pi-input" placeholder="pt-BR">
                        </label>
                    </div>

                    <label class="pi-field">
                        <span class="pi-label">URL de origem</span>
                        <input type="text" wire:model.defer="sourceUrl" class="pi-input" placeholder="Opcional">
                    </label>

                    <label class="pi-field">
                        <span class="pi-label">Comentários</span>
                        <textarea wire:model.defer="commentsText" rows="12" class="pi-input pi-textarea" placeholder="Cole aqui um post/avaliação completo. Para importar vários comentários de uma vez, separe cada comentário com uma linha contendo apenas ---."></textarea>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['commentsText'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <span class="pi-error"><?php echo e($message); ?></span> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </label>

                    <button type="button" wire:click="importComments" class="pi-btn pi-btn--primary pi-btn--block">Arquivar e classificar comentários</button>
                </div>
            </article>

            <div class="pi-stack">
                <article class="pi-card">
                    <div class="pi-section-header pi-section-header--row">
                        <div>
                            <h3 class="pi-section-title">Principais problemas detectados</h3>
                            <p class="pi-section-text">Ordenado por prioridade calculada: frequência, negatividade, gravidade, impacto e confiança da evidência.</p>
                        </div>

                        <label class="pi-field pi-field--period">
                            <span class="pi-label">Período/dias</span>
                            <input type="number" min="1" max="3650" wire:model.defer="periodDays" class="pi-input">
                        </label>
                    </div>

                    <div class="pi-list">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = data_get($report, 'top_problems', []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $problem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <div class="pi-problem-card">
                                <div class="pi-problem-card__header">
                                    <div class="pi-problem-card__content">
                                        <h4 class="pi-problem-card__title"><?php echo e($problem['category']); ?></h4>
                                        <p class="pi-problem-card__pain"><strong>Dor real:</strong> <?php echo e($problem['real_pain'] ?? 'Não classificada'); ?></p>
                                        <p class="pi-problem-card__text"><strong>Insight:</strong> <?php echo e($problem['insight'] ?? 'Não informado'); ?></p>
                                        <p class="pi-problem-card__text"><strong>Aprendizado:</strong> <?php echo e($problem['market_learning'] ?? 'Não informado'); ?></p>
                                        <p class="pi-problem-card__text"><strong>O que fazer:</strong> <?php echo e($problem['what_to_do'] ?? ($problem['recommended_action'] ?? 'Analisar manualmente')); ?></p>
                                        <p class="pi-problem-card__text"><strong>O que não fazer:</strong> <?php echo e($problem['what_not_to_do'] ?? 'Não informado'); ?></p>
                                        <p class="pi-problem-card__text"><strong>Oportunidade:</strong> <?php echo e($problem['opportunity']); ?></p>
                                    </div>

                                    <div class="pi-badge-group">
                                        <span class="pi-badge"><?php echo e($problem['total']); ?> ocorrência(s)</span>
                                        <span class="pi-badge pi-badge--danger">Score <?php echo e($problem['priority_score']); ?></span>
                                        <span class="pi-badge pi-badge--info">Impacto <?php echo e($problem['impact'] ?? '-'); ?></span>
                                        <span class="pi-badge pi-badge--warning">Confiança <?php echo e($problem['confidence'] ?? 'baixa'); ?></span>
                                    </div>
                                </div>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($problem['source_breakdown'])): ?>
                                    <div class="pi-keyword-list">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $problem['source_breakdown']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $source): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                            <span class="pi-keyword"><?php echo e($source['source_name']); ?>: <?php echo e($source['total']); ?></span>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($problem['seo_keywords'])): ?>
                                    <div class="pi-keyword-list">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $problem['seo_keywords']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $keyword): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                            <span class="pi-keyword"><?php echo e($keyword); ?></span>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <div class="pi-empty-state">Nenhum comentário de mercado arquivado ainda. Importe comentários para gerar o primeiro relatório.</div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </article>

                <article class="pi-card">
                    <div class="pi-section-header">
                        <h3 class="pi-section-title">Roadmap sugerido</h3>
                    </div>

                    <div class="pi-list">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = data_get($report, 'recommended_roadmap', []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <div class="pi-roadmap-card">
                                <div class="pi-roadmap-card__content">
                                    <strong class="pi-roadmap-card__title"><?php echo e($item['problem']); ?></strong>
                                    <p class="pi-roadmap-card__text"><strong>Fazer:</strong> <?php echo e($item['what_to_do'] ?? $item['recommended_action']); ?></p>
                                    <p class="pi-roadmap-card__text"><strong>Não fazer:</strong> <?php echo e($item['what_not_to_do'] ?? 'Não informado'); ?></p>
                                    <small class="pi-roadmap-card__meta"><?php echo e($item['why']); ?></small>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($item['real_pain'])): ?>
                                        <small class="pi-roadmap-card__meta"><strong>Dor real:</strong> <?php echo e($item['real_pain']); ?></small>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($item['market_learning'])): ?>
                                        <small class="pi-roadmap-card__meta"><strong>Aprendizado:</strong> <?php echo e($item['market_learning']); ?></small>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>

                                <div class="pi-badge-group">
                                    <span class="pi-badge pi-badge--warning"><?php echo e($item['priority']); ?></span>
                                    <span class="pi-badge"><?php echo e($item['complexity']); ?></span>
                                    <span class="pi-badge pi-badge--info"><?php echo e($item['confidence'] ?? 'baixa'); ?></span>
                                </div>
                            </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <p class="pi-muted-text">O roadmap aparecerá depois da importação dos primeiros comentários.</p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </article>
            </div>
        </div>

        <div class="pi-bottom-grid pi-bottom-grid--analysis">
            <article class="pi-card">
                <div class="pi-section-header">
                    <h3 class="pi-section-title">Aprendizados do mercado</h3>
                    <p class="pi-section-text">Transforma comentários em conhecimento acionável: insight, o que fazer e o que evitar.</p>
                </div>

                <div class="pi-list">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = data_get($report, 'market_learnings', []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $learning): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="pi-comment-card">
                            <div class="pi-comment-card__meta">
                                <strong><?php echo e($learning['title']); ?></strong>
                                <span>•</span>
                                <span><?php echo e($learning['type']); ?></span>
                                <span>• confiança <?php echo e($learning['confidence']); ?></span>
                            </div>
                            <p class="pi-comment-card__insight"><strong>Aprendizado:</strong> <?php echo e($learning['learning']); ?></p>
                            <p class="pi-comment-card__insight"><strong>Insight:</strong> <?php echo e($learning['insight']); ?></p>
                            <p class="pi-comment-card__insight"><strong>Fazer:</strong> <?php echo e($learning['what_to_do']); ?></p>
                            <p class="pi-comment-card__insight"><strong>Não fazer:</strong> <?php echo e($learning['what_not_to_do']); ?></p>
                            <p class="pi-comment-card__text"><strong>Evidência:</strong> <?php echo e($learning['evidence']); ?></p>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <p class="pi-muted-text">Os aprendizados aparecerão depois da importação dos primeiros comentários.</p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </article>

            <article class="pi-card">
                <div class="pi-section-header">
                    <h3 class="pi-section-title">Pontos fortes detectados</h3>
                    <p class="pi-section-text">Elogios e padrões positivos que mostram o que o mercado valoriza e você não deve perder.</p>
                </div>

                <div class="pi-list">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = data_get($report, 'market_strengths', []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $strength): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="pi-problem-card">
                            <div class="pi-problem-card__header">
                                <div class="pi-problem-card__content">
                                    <h4 class="pi-problem-card__title"><?php echo e($strength['category']); ?></h4>
                                    <p class="pi-problem-card__text"><strong>Insight:</strong> <?php echo e($strength['insight']); ?></p>
                                    <p class="pi-problem-card__text"><strong>Aprendizado:</strong> <?php echo e($strength['market_learning']); ?></p>
                                    <p class="pi-problem-card__text"><strong>Preservar/fazer:</strong> <?php echo e($strength['what_to_do']); ?></p>
                                    <p class="pi-problem-card__text"><strong>Evitar:</strong> <?php echo e($strength['what_not_to_do']); ?></p>
                                </div>
                                <div class="pi-badge-group">
                                    <span class="pi-badge pi-badge--success"><?php echo e($strength['total']); ?> ocorrência(s)</span>
                                    <span class="pi-badge pi-badge--warning">Confiança <?php echo e($strength['confidence']); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <p class="pi-muted-text">Nenhum ponto forte detectado ainda.</p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </article>
        </div>

        <div class="pi-bottom-grid pi-bottom-grid--analysis">
            <article class="pi-card">
                <div class="pi-section-header">
                    <h3 class="pi-section-title">Frequência por fonte</h3>
                    <p class="pi-section-text">Mostra se a reclamação aparece em uma fonte isolada ou se está se repetindo em vários lugares.</p>
                </div>

                <div class="pi-list">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = data_get($report, 'source_frequency', []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $source): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="pi-roadmap-card">
                            <div class="pi-roadmap-card__content">
                                <strong class="pi-roadmap-card__title"><?php echo e($source['source_name']); ?></strong>
                                <p class="pi-roadmap-card__text">Tipo: <?php echo e($source['source_type']); ?></p>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($source['top_categories'])): ?>
                                    <small class="pi-roadmap-card__meta"><strong>Categorias:</strong>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $source['top_categories']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category => $total): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                            <?php echo e($category); ?> (<?php echo e($total); ?>)<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $loop->last): ?>, <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    </small>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <div class="pi-badge-group">
                                <span class="pi-badge"><?php echo e($source['total']); ?> total</span>
                                <span class="pi-badge pi-badge--danger"><?php echo e($source['negative_total']); ?> neg.</span>
                                <span class="pi-badge pi-badge--success"><?php echo e($source['positive_total']); ?> pos.</span>
                            </div>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <p class="pi-muted-text">Nenhuma fonte analisada ainda.</p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </article>

            <article class="pi-card">
                <div class="pi-section-header">
                    <h3 class="pi-section-title">Contradições detectadas</h3>
                    <p class="pi-section-text">Ajuda a evitar decisões erradas quando o mercado elogia e reclama de coisas parecidas.</p>
                </div>

                <div class="pi-list">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = data_get($report, 'contradictions', []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $contradiction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="pi-alert-card">
                            <div class="pi-alert-card__header">
                                <strong><?php echo e($contradiction['title']); ?></strong>
                                <span class="pi-badge pi-badge--warning"><?php echo e($contradiction['confidence']); ?></span>
                            </div>
                            <p class="pi-alert-card__text"><strong>Resumo:</strong> <?php echo e($contradiction['summary']); ?></p>
                            <p class="pi-alert-card__text"><strong>Risco:</strong> <?php echo e($contradiction['risk']); ?></p>
                            <p class="pi-alert-card__text"><strong>Decisão:</strong> <?php echo e($contradiction['decision']); ?></p>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <p class="pi-muted-text">Nenhuma contradição detectada ainda.</p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </article>
        </div>

        <div class="pi-bottom-grid">
            <article class="pi-card">
                <div class="pi-section-header">
                    <h3 class="pi-section-title">Prompt pronto para enviar ao ChatGPT</h3>
                    <p class="pi-section-text">Copie este conteúdo e envie aqui quando quiser que eu analise os resultados.</p>
                </div>

                <textarea readonly rows="12" class="pi-input pi-textarea pi-textarea--report"><?php echo e(data_get($report, 'prompt_for_chatgpt')); ?></textarea>
            </article>

            <article class="pi-card">
                <div class="pi-section-header">
                    <h3 class="pi-section-title">Últimos comentários arquivados</h3>
                </div>

                <div class="pi-list">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $latestComments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="pi-comment-card">
                            <div class="pi-comment-card__meta">
                                <strong><?php echo e($comment['competitor']); ?></strong>
                                <span>•</span>
                                <span><?php echo e($comment['source']); ?></span>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($comment['rating']): ?>
                                    <span>• <?php echo e($comment['rating']); ?> estrela(s)</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <span>• <?php echo e($comment['created_at']); ?></span>
                            </div>

                            <p class="pi-comment-card__text"><?php echo e($comment['text']); ?></p>

                            <div class="pi-badge-group">
                                <span class="pi-badge"><?php echo e($comment['sentiment']); ?></span>
                                <span class="pi-badge pi-badge--info"><?php echo e($comment['category']); ?></span>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($comment['impact'])): ?>
                                    <span class="pi-badge pi-badge--warning">Impacto <?php echo e($comment['impact']); ?></span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($comment['real_pain'])): ?>
                                <p class="pi-comment-card__insight"><strong>Dor real:</strong> <?php echo e($comment['real_pain']); ?></p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($comment['insight'])): ?>
                                <p class="pi-comment-card__insight"><strong>Insight:</strong> <?php echo e($comment['insight']); ?></p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($comment['market_learning'])): ?>
                                <p class="pi-comment-card__insight"><strong>Aprendizado:</strong> <?php echo e($comment['market_learning']); ?></p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($comment['recommended_action'])): ?>
                                <p class="pi-comment-card__insight"><strong>Ação:</strong> <?php echo e($comment['recommended_action']); ?></p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <p class="pi-muted-text">Nenhum comentário importado ainda.</p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </article>
        </div>
    </section>
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
<?php /**PATH C:\xampp\htdocs\sistemrh\resources\views/filament/pages/inteligencia-produto.blade.php ENDPATH**/ ?>