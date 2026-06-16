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

    <link rel="stylesheet" href="<?php echo e(asset('css/auditoria-detalhada.css')); ?>">

    <?php ($metricas = $this->metricas()); ?>
    <?php ($eventos = $this->eventos()); ?>
    <?php ($usuarios = $this->usuariosMaisAtivos()); ?>
    <?php ($modulos = $this->modulosAuditados()); ?>
    <?php ($empresas = $this->empresasAuditadas()); ?>
    <?php ($suspeitas = $this->acoesSuspeitas()); ?>
    <?php ($recentes = $this->registrosRecentes()); ?>
    <?php ($filtros = $this->filtros()); ?>

    <div class="ad-page">
        <section class="ad-hero">
            <div>
                <div class="ad-eyebrow">Auditoria e rastreabilidade</div>
                <h2 class="ad-title">Auditoria útil para operação, compliance e cliente</h2>
                <p class="ad-subtitle">
                    Acompanhe a trilha por usuário, compare antes/depois das alterações, filtre por módulo e empresa,
                    identifique ações sensíveis e exporte o resultado atual para análise externa.
                </p>
            </div>

            <div class="ad-hero-actions">
                <a href="<?php echo e($this->exportUrl()); ?>" class="ad-button ad-button--primary">
                    Exportar CSV
                </a>
                <a href="<?php echo e(url()->current()); ?>" class="ad-button ad-button--ghost">
                    Limpar filtros
                </a>
            </div>
        </section>

        <form method="GET" action="<?php echo e(url()->current()); ?>" class="ad-filters">
            <div class="ad-filter-field">
                <label for="periodo">Período</label>
                <select id="periodo" name="periodo">
                    <option value="" <?php if(($filtros['periodo'] ?? '') === ''): echo 'selected'; endif; ?>>Todo período</option>
                    <option value="hoje" <?php if(($filtros['periodo'] ?? '') === 'hoje'): echo 'selected'; endif; ?>>Hoje</option>
                    <option value="7" <?php if(($filtros['periodo'] ?? '') === '7'): echo 'selected'; endif; ?>>Últimos 7 dias</option>
                    <option value="30" <?php if(($filtros['periodo'] ?? '') === '30'): echo 'selected'; endif; ?>>Últimos 30 dias</option>
                </select>
            </div>

            <div class="ad-filter-field">
                <label for="evento">Evento</label>
                <select id="evento" name="evento">
                    <option value="">Todos</option>
                    <option value="created" <?php if(($filtros['evento'] ?? '') === 'created'): echo 'selected'; endif; ?>>Criado</option>
                    <option value="updated" <?php if(($filtros['evento'] ?? '') === 'updated'): echo 'selected'; endif; ?>>Alterado</option>
                    <option value="deleted" <?php if(($filtros['evento'] ?? '') === 'deleted'): echo 'selected'; endif; ?>>Excluído</option>
                </select>
            </div>

            <div class="ad-filter-field">
                <label for="user_id">Usuário</label>
                <select id="user_id" name="user_id">
                    <option value="">Todos</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->usuariosFiltro(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $usuario): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <option value="<?php echo e($usuario->id); ?>" <?php if((string) ($filtros['user_id'] ?? '') === (string) $usuario->id): echo 'selected'; endif; ?>><?php echo e($usuario->name); ?></option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </select>
            </div>

            <div class="ad-filter-field">
                <label for="empresa_id">Empresa</label>
                <select id="empresa_id" name="empresa_id">
                    <option value="">Todas</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->empresasFiltro(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $empresa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <option value="<?php echo e($empresa->id); ?>" <?php if((string) ($filtros['empresa_id'] ?? '') === (string) $empresa->id): echo 'selected'; endif; ?>>
                            <?php echo e($empresa->razao_social ?: $empresa->nome_fantasia); ?>

                        </option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </select>
            </div>

            <div class="ad-filter-field">
                <label for="modulo">Módulo</label>
                <select id="modulo" name="modulo">
                    <option value="">Todos</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->modulosFiltro(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $modulo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <option value="<?php echo e($modulo['value']); ?>" <?php if(($filtros['modulo'] ?? '') === $modulo['value']): echo 'selected'; endif; ?>><?php echo e($modulo['label']); ?></option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </select>
            </div>

            <label class="ad-check-filter">
                <input type="checkbox" name="suspeito" value="1" <?php if($filtros['suspeito'] ?? false): echo 'checked'; endif; ?>>
                <span>Somente ações sensíveis</span>
            </label>

            <div class="ad-filter-actions">
                <button type="submit" class="ad-button ad-button--primary">Aplicar filtros</button>
            </div>
        </form>

        <section class="ad-metrics ad-metrics--five">
            <article class="ad-metric-card">
                <span>Total filtrado</span>
                <strong><?php echo e(number_format((int) ($metricas['total'] ?? 0), 0, ',', '.')); ?></strong>
                <small>Registros no recorte atual</small>
            </article>

            <article class="ad-metric-card ad-metric-card--info">
                <span>Hoje</span>
                <strong><?php echo e(number_format((int) ($metricas['hoje'] ?? 0), 0, ',', '.')); ?></strong>
                <small>Movimentações do dia</small>
            </article>

            <article class="ad-metric-card ad-metric-card--warning">
                <span>Alterações</span>
                <strong><?php echo e(number_format((int) ($metricas['alteracoes'] ?? 0), 0, ',', '.')); ?></strong>
                <small>Campos modificados</small>
            </article>

            <article class="ad-metric-card ad-metric-card--danger">
                <span>Exclusões</span>
                <strong><?php echo e(number_format((int) ($metricas['exclusoes'] ?? 0), 0, ',', '.')); ?></strong>
                <small>Registros removidos</small>
            </article>

            <article class="ad-metric-card ad-metric-card--critical">
                <span>Ações sensíveis</span>
                <strong><?php echo e(number_format((int) ($metricas['suspeitas'] ?? 0), 0, ',', '.')); ?></strong>
                <small>Exclusões, permissões, status e senhas</small>
            </article>
        </section>

        <section class="ad-layout ad-layout--balanced">
            <article class="ad-panel">
                <div class="ad-panel-header">
                    <div>
                        <h3>Eventos por tipo</h3>
                        <p>Distribuição das ações registradas no recorte atual.</p>
                    </div>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($eventos->isEmpty()): ?>
                    <div class="ad-empty">Nenhum evento encontrado para os filtros aplicados.</div>
                <?php else: ?>
                    <div class="ad-chart">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $eventos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $evento): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <div class="ad-chart-row">
                                <div class="ad-chart-label">
                                    <span class="ad-badge <?php echo e($evento['classe']); ?>"><?php echo e($evento['label']); ?></span>
                                </div>
                                <div class="ad-chart-track">
                                    <div class="ad-chart-bar" style="width: <?php echo e($evento['percentual']); ?>%"></div>
                                </div>
                                <strong><?php echo e(number_format((int) $evento['valor'], 0, ',', '.')); ?></strong>
                            </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </article>

            <article class="ad-panel">
                <div class="ad-panel-header">
                    <div>
                        <h3>Trilha por usuário</h3>
                        <p>Usuários com maior volume de movimentações.</p>
                    </div>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($usuarios->isEmpty()): ?>
                    <div class="ad-empty">Sem usuários auditados.</div>
                <?php else: ?>
                    <div class="ad-ranking">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $usuarios; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $usuario): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <div class="ad-ranking-row ad-ranking-row--stacked">
                                <span><?php echo e($usuario['nome']); ?></span>
                                <small>Última ação: <?php echo e($usuario['ultima']); ?></small>
                                <strong><?php echo e(number_format((int) $usuario['total'], 0, ',', '.')); ?></strong>
                            </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </article>
        </section>

        <section class="ad-layout ad-layout--balanced">
            <article class="ad-panel">
                <div class="ad-panel-header">
                    <div>
                        <h3>Auditoria por empresa</h3>
                        <p>Empresas com mais movimentações registradas.</p>
                    </div>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($empresas->isEmpty()): ?>
                    <div class="ad-empty">Nenhuma empresa encontrada.</div>
                <?php else: ?>
                    <div class="ad-ranking">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $empresas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $empresa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <div class="ad-ranking-row">
                                <span><?php echo e($empresa['nome']); ?></span>
                                <strong><?php echo e(number_format((int) $empresa['total'], 0, ',', '.')); ?></strong>
                            </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </article>

            <article class="ad-panel">
                <div class="ad-panel-header">
                    <div>
                        <h3>Módulos auditados</h3>
                        <p>Áreas do sistema com maior volume de rastreio.</p>
                    </div>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($modulos->isEmpty()): ?>
                    <div class="ad-empty">Nenhum módulo encontrado.</div>
                <?php else: ?>
                    <div class="ad-ranking">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $modulos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $modulo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <div class="ad-ranking-row">
                                <span><?php echo e($modulo['nome']); ?></span>
                                <strong><?php echo e(number_format((int) $modulo['total'], 0, ',', '.')); ?></strong>
                            </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </article>
        </section>

        <section class="ad-panel">
            <div class="ad-panel-header">
                <div>
                    <h3>Ações sensíveis</h3>
                    <p>Exclusões e alterações em campos críticos aparecem aqui para revisão rápida.</p>
                </div>
                <div class="ad-panel-counter"><?php echo e(number_format((int) $suspeitas->count(), 0, ',', '.')); ?> recentes</div>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($suspeitas->isEmpty()): ?>
                <div class="ad-empty">Nenhuma ação sensível encontrada no recorte atual.</div>
            <?php else: ?>
                <div class="ad-sensitive-grid">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $suspeitas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $registro): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <article class="ad-sensitive-card">
                            <div class="ad-sensitive-top">
                                <span class="ad-badge ad-badge--danger">Atenção</span>
                                <time><?php echo e(optional($registro->created_at)->format('d/m/Y H:i') ?: '-'); ?></time>
                            </div>
                            <h4><?php echo e($this->registroLabel($registro)); ?></h4>
                            <p><?php echo e($this->eventoLabel($registro->evento)); ?> em <?php echo e($this->campoLabel($registro->campo)); ?></p>
                            <div class="ad-timeline-meta">
                                <span><?php echo e($registro->user?->name ?? 'Sistema'); ?></span>
                                <span><?php echo e($registro->empresa?->razao_social ?: $registro->empresa?->nome_fantasia ?: 'Sem empresa'); ?></span>
                            </div>
                        </article>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </section>

        <section class="ad-panel ad-panel--large">
            <div class="ad-panel-header">
                <div>
                    <h3>Trilha recente com antes/depois</h3>
                    <p>Últimas alterações registradas, já com comparação do valor anterior e novo.</p>
                </div>
                <div class="ad-panel-counter"><?php echo e(number_format((int) $recentes->count(), 0, ',', '.')); ?> registros</div>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($recentes->isEmpty()): ?>
                <div class="ad-empty">Nenhuma movimentação recente encontrada.</div>
            <?php else: ?>
                <div class="ad-timeline">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $recentes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $registro): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="ad-timeline-item">
                            <div class="ad-timeline-marker"></div>

                            <div class="ad-timeline-content">
                                <div class="ad-timeline-top">
                                    <div class="ad-timeline-badges">
                                        <span class="ad-badge <?php echo e($this->eventoClasse($registro->evento)); ?>"><?php echo e($this->eventoLabel($registro->evento)); ?></span>
                                        <span class="ad-badge <?php echo e($this->suspeitoClasse($registro)); ?>"><?php echo e($this->suspeitoLabel($registro)); ?></span>
                                    </div>
                                    <time><?php echo e(optional($registro->created_at)->format('d/m/Y H:i:s') ?: '-'); ?></time>
                                </div>

                                <h4><?php echo e($this->registroLabel($registro)); ?></h4>

                                <div class="ad-timeline-meta">
                                    <span>Usuário: <?php echo e($registro->user?->name ?? 'Sistema'); ?></span>
                                    <span>Empresa: <?php echo e($registro->empresa?->razao_social ?: $registro->empresa?->nome_fantasia ?: '-'); ?></span>
                                    <span>Campo: <?php echo e($this->campoLabel($registro->campo)); ?></span>
                                    <span>IP: <?php echo e($registro->ip ?: '-'); ?></span>
                                </div>

                                <div class="ad-diff">
                                    <div>
                                        <span>Antes</span>
                                        <strong><?php echo e($this->valorRegistro($registro->valor_anterior, $registro->campo)); ?></strong>
                                    </div>
                                    <div>
                                        <span>Depois</span>
                                        <strong><?php echo e($this->valorRegistro($registro->valor_novo, $registro->campo)); ?></strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views\filament\resources\auditoria-detalhada\pages\visualizar-auditoria-detalhada.blade.php ENDPATH**/ ?>