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
        $resumo = $resumo ?? [];
        $opcoes = $opcoes ?? [];
        $filtros = $filtros ?? [];
        $documentos = collect($documentos ?? []);
        $acaoRapida = collect($acaoRapida ?? []);
        $porPrioridade = collect($porPrioridade ?? []);
        $porEmpresa = collect($porEmpresa ?? []);
        $scoreGeral = $scoreGeral ?? 100;
        $hasFiltros = collect($filtros)->filter(fn ($value) => filled($value))->isNotEmpty();
    ?>

    <div class="gd-page">
        <section class="gd-hero">
            <div class="gd-hero-content">
                <div class="gd-eyebrow">Visão operacional real</div>
                <h1 class="gd-title">Gestão documental</h1>
                <p class="gd-subtitle">
                    Priorize vencidos, próximos vencimentos, documentos sem anexo, sem responsável, pendências de aprovação e itens que precisam de uma ação agora.
                </p>
            </div>

            <div class="gd-hero-actions">
                <a href="<?php echo e($novoDocumentoUrl); ?>" class="gd-btn gd-btn-primary">Novo documento</a>
                <a href="<?php echo e($listaDocumentosUrl); ?>" class="gd-btn">Lista completa</a>
            </div>
        </section>

        <section class="gd-command-center">
            <div class="gd-score-card">
                <div>
                    <div class="gd-score-label">Saúde documental</div>
                    <div class="gd-score-value"><?php echo e($scoreGeral); ?>%</div>
                    <div class="gd-score-help">Calculado com dados reais: prazo, responsável, anexo, assinatura, aprovação e versionamento.</div>
                </div>
                <div class="gd-score-bar" aria-label="Saúde documental">
                    <span style="width: <?php echo e(max(0, min(100, (int) $scoreGeral))); ?>%"></span>
                </div>
            </div>

            <div class="gd-today-card">
                <span>Fila de ação</span>
                <strong><?php echo e(number_format((int) (($resumo['vencidos'] ?? 0) + ($resumo['semResponsavel'] ?? 0) + ($resumo['semArquivo'] ?? 0) + ($resumo['aprovacaoPendente'] ?? 0)), 0, ',', '.')); ?></strong>
                <small>Problemas que podem travar a rotina documental.</small>
            </div>
        </section>

        <section class="gd-kpis">
            <a class="gd-kpi" href="<?php echo e(request()->fullUrlWithQuery(['situacao' => null])); ?>">
                <span class="gd-kpi-label">Documentos</span>
                <strong><?php echo e(number_format((int) ($resumo['total'] ?? 0), 0, ',', '.')); ?></strong>
                <small>Total visível para seu usuário.</small>
            </a>

            <a class="gd-kpi gd-kpi-danger" href="<?php echo e(request()->fullUrlWithQuery(['situacao' => 'vencido'])); ?>">
                <span class="gd-kpi-label">Vencidos</span>
                <strong><?php echo e(number_format((int) ($resumo['vencidos'] ?? 0), 0, ',', '.')); ?></strong>
                <small>Regularização imediata.</small>
            </a>

            <a class="gd-kpi gd-kpi-warning" href="<?php echo e(request()->fullUrlWithQuery(['situacao' => 'vence_7'])); ?>">
                <span class="gd-kpi-label">Vencem em 7 dias</span>
                <strong><?php echo e(number_format((int) ($resumo['vencem7'] ?? 0), 0, ',', '.')); ?></strong>
                <small>Acompanhar esta semana.</small>
            </a>

            <a class="gd-kpi gd-kpi-warning" href="<?php echo e(request()->fullUrlWithQuery(['situacao' => 'vence_30'])); ?>">
                <span class="gd-kpi-label">Vencem em 30 dias</span>
                <strong><?php echo e(number_format((int) ($resumo['vencem30'] ?? 0), 0, ',', '.')); ?></strong>
                <small>Prevenção de atraso.</small>
            </a>

            <a class="gd-kpi gd-kpi-danger" href="<?php echo e(request()->fullUrlWithQuery(['situacao' => 'sem_arquivo'])); ?>">
                <span class="gd-kpi-label">Sem anexo</span>
                <strong><?php echo e(number_format((int) ($resumo['semArquivo'] ?? 0), 0, ',', '.')); ?></strong>
                <small>Documento sem evidência.</small>
            </a>

            <a class="gd-kpi gd-kpi-danger" href="<?php echo e(request()->fullUrlWithQuery(['situacao' => 'sem_responsavel'])); ?>">
                <span class="gd-kpi-label">Sem responsável</span>
                <strong><?php echo e(number_format((int) ($resumo['semResponsavel'] ?? 0), 0, ',', '.')); ?></strong>
                <small>Sem dono operacional.</small>
            </a>

            <a class="gd-kpi gd-kpi-warning" href="<?php echo e(request()->fullUrlWithQuery(['situacao' => 'aprovacao_pendente'])); ?>">
                <span class="gd-kpi-label">Aprovação pendente</span>
                <strong><?php echo e(number_format((int) ($resumo['aprovacaoPendente'] ?? 0), 0, ',', '.')); ?></strong>
                <small>Aguardando decisão.</small>
            </a>

            <a class="gd-kpi gd-kpi-success" href="<?php echo e(request()->fullUrlWithQuery(['situacao' => null])); ?>">
                <span class="gd-kpi-label">Com assinatura</span>
                <strong><?php echo e(number_format((int) ($resumo['assinados'] ?? 0), 0, ',', '.')); ?></strong>
                <small>Com registro de assinatura.</small>
            </a>
        </section>

        <form class="gd-card gd-filters" method="GET">
            <div class="gd-field gd-field-search">
                <label for="busca">Buscar</label>
                <input id="busca" name="busca" value="<?php echo e($filtros['busca'] ?? ''); ?>" type="search" placeholder="Título, contrato, cliente, status, responsável...">
            </div>

            <div class="gd-field">
                <label for="empresa_id">Empresa</label>
                <select id="empresa_id" name="empresa_id">
                    <option value="">Todas</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($opcoes['empresas'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $nome): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <option value="<?php echo e($id); ?>" <?php if((string) ($filtros['empresa_id'] ?? '') === (string) $id): echo 'selected'; endif; ?>><?php echo e($nome); ?></option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </select>
            </div>

            <div class="gd-field">
                <label for="responsavel_id">Responsável</label>
                <select id="responsavel_id" name="responsavel_id">
                    <option value="">Todos</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($opcoes['responsaveis'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $nome): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <option value="<?php echo e($id); ?>" <?php if((string) ($filtros['responsavel_id'] ?? '') === (string) $id): echo 'selected'; endif; ?>><?php echo e($nome); ?></option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </select>
            </div>

            <div class="gd-field">
                <label for="tipo">Tipo</label>
                <select id="tipo" name="tipo">
                    <option value="">Todos</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($opcoes['tipos'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $nome): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <option value="<?php echo e($id); ?>" <?php if((string) ($filtros['tipo'] ?? '') === (string) $id): echo 'selected'; endif; ?>><?php echo e($nome); ?></option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </select>
            </div>

            <div class="gd-field">
                <label for="prioridade">Prioridade</label>
                <select id="prioridade" name="prioridade">
                    <option value="">Todas</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($opcoes['prioridades'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $nome): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <option value="<?php echo e($id); ?>" <?php if((string) ($filtros['prioridade'] ?? '') === (string) $id): echo 'selected'; endif; ?>><?php echo e($nome); ?></option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </select>
            </div>

            <div class="gd-field">
                <label for="situacao">Situação</label>
                <select id="situacao" name="situacao">
                    <option value="">Todas</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($opcoes['situacoes'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $nome): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <option value="<?php echo e($id); ?>" <?php if((string) ($filtros['situacao'] ?? '') === (string) $id): echo 'selected'; endif; ?>><?php echo e($nome); ?></option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </select>
            </div>

            <div class="gd-field">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="">Todos</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($opcoes['status'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $nome): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <option value="<?php echo e($id); ?>" <?php if((string) ($filtros['status'] ?? '') === (string) $id): echo 'selected'; endif; ?>><?php echo e($nome); ?></option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </select>
            </div>

            <div class="gd-field">
                <label for="ordenacao">Ordenar</label>
                <select id="ordenacao" name="ordenacao">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($opcoes['ordenacoes'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $nome): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <option value="<?php echo e($id); ?>" <?php if((string) ($filtros['ordenacao'] ?? 'prioridade') === (string) $id): echo 'selected'; endif; ?>><?php echo e($nome); ?></option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </select>
            </div>

            <div class="gd-filter-actions">
                <button class="gd-btn gd-btn-primary" type="submit">Filtrar</button>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasFiltros): ?>
                    <a class="gd-btn" href="<?php echo e(url()->current()); ?>">Limpar</a>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </form>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($acaoRapida->isNotEmpty()): ?>
            <section class="gd-card">
                <div class="gd-section-head">
                    <div>
                        <h2 class="gd-section-title">Ações rápidas</h2>
                        <p class="gd-section-subtitle">Fila automática com os documentos que mais precisam de atenção agora.</p>
                    </div>
                </div>

                <div class="gd-action-grid">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $acaoRapida; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <a class="gd-action-card gd-border-<?php echo e($item['tom']); ?>" href="<?php echo e($item['edit_url']); ?>">
                            <span><?php echo e($item['status_documental']); ?></span>
                            <strong><?php echo e($item['titulo']); ?></strong>
                            <small><?php echo e($item['empresa_nome']); ?> · <?php echo e($item['situacao_prazo']); ?></small>
                        </a>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            </section>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($porPrioridade->isNotEmpty()): ?>
            <section class="gd-card gd-priority-section">
                <div class="gd-section-head">
                    <div>
                        <h2 class="gd-section-title">Documentos por prioridade</h2>
                        <p class="gd-section-subtitle">Use esta visão para atacar primeiro o que está atrasado, sem anexo ou sem responsável.</p>
                    </div>
                </div>

                <div class="gd-priority-grid">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $porPrioridade; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grupo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="gd-priority-lane">
                            <div class="gd-priority-head">
                                <strong><?php echo e($grupo['prioridade']); ?></strong>
                                <span><?php echo e($grupo['total']); ?> item(ns)</span>
                            </div>
                            <div class="gd-priority-stats">
                                <span><?php echo e($grupo['criticos']); ?> crítico(s)</span>
                                <span><?php echo e($grupo['sem_anexo']); ?> sem anexo</span>
                                <span><?php echo e($grupo['sem_responsavel']); ?> sem responsável</span>
                            </div>
                            <div class="gd-priority-items">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $grupo['itens']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <a href="<?php echo e($item['edit_url']); ?>" class="gd-priority-item gd-border-<?php echo e($item['tom']); ?>">
                                        <b><?php echo e($item['titulo']); ?></b>
                                        <small><?php echo e($item['situacao_prazo']); ?> · <?php echo e($item['status_documental']); ?></small>
                                    </a>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </div>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            </section>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <section class="gd-grid-main">
            <div class="gd-left">
                <div class="gd-section-head gd-card gd-section-card">
                    <div>
                        <h2 class="gd-section-title">Listagem operacional</h2>
                        <p class="gd-section-subtitle"><?php echo e($documentos->count()); ?> documento(s) encontrados. Vencidos e críticos aparecem primeiro.</p>
                    </div>
                </div>

                <div class="gd-list">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $documentos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $documento): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <article class="gd-doc gd-border-<?php echo e($documento['tom']); ?>">
                            <div class="gd-doc-main">
                                <div class="gd-doc-content">
                                    <div class="gd-badges">
                                        <span class="gd-badge gd-badge-<?php echo e($documento['tom']); ?>"><?php echo e($documento['status_documental']); ?></span>
                                        <span class="gd-badge gd-badge-muted"><?php echo e($documento['situacao_prazo']); ?></span>
                                        <span class="gd-badge <?php echo e($documento['tem_arquivo'] ? 'gd-badge-success' : 'gd-badge-danger'); ?>"><?php echo e($documento['anexos_count']); ?> anexo(s)</span>
                                        <span class="gd-badge <?php echo e($documento['sem_responsavel'] ? 'gd-badge-danger' : 'gd-badge-muted'); ?>"><?php echo e($documento['responsavel_nome']); ?></span>
                                        <span class="gd-badge <?php echo e($documento['assinatura'] === 'Assinado' ? 'gd-badge-success' : 'gd-badge-warning'); ?>"><?php echo e($documento['assinatura']); ?></span>
                                        <span class="gd-badge gd-badge-muted"><?php echo e($documento['aprovacao']); ?></span>
                                    </div>

                                    <h3 class="gd-doc-title"><?php echo e($documento['titulo']); ?></h3>
                                    <p class="gd-doc-desc"><?php echo e($documento['descricao']); ?></p>

                                    <div class="gd-meta">
                                        <div>
                                            <span>Empresa</span>
                                            <strong><?php echo e($documento['empresa_nome']); ?></strong>
                                        </div>
                                        <div>
                                            <span>Tipo</span>
                                            <strong><?php echo e($documento['tipo']); ?></strong>
                                        </div>
                                        <div>
                                            <span>Vencimento</span>
                                            <strong><?php echo e($documento['vencimento']); ?></strong>
                                        </div>
                                        <div>
                                            <span>Prioridade</span>
                                            <strong><?php echo e($documento['prioridade']); ?></strong>
                                        </div>
                                    </div>

                                    <div class="gd-workflow" aria-label="Fluxo documental">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $documento['workflow']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $etapa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                            <span class="<?php echo e($etapa['ok'] ? 'is-ok' : 'is-pending'); ?>"><?php echo e($etapa['label']); ?></span>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    </div>

                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($documento['pendencias'])): ?>
                                        <div class="gd-pendencias">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $documento['pendencias']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pendencia): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                                <span><?php echo e($pendencia); ?></span>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($documento['timeline'])): ?>
                                        <div class="gd-timeline">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $documento['timeline']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $evento): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                                <div class="gd-timeline-item">
                                                    <strong><?php echo e($evento['titulo'] ?: $evento['tipo']); ?></strong>
                                                    <span><?php echo e($evento['data']); ?></span>
                                                </div>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>

                                <aside class="gd-doc-side">
                                    <div class="gd-mini-score">
                                        <span>Score</span>
                                        <strong><?php echo e($documento['score']); ?>%</strong>
                                    </div>

                                    <a href="<?php echo e($documento['edit_url']); ?>" class="gd-btn gd-btn-primary">Abrir / resolver</a>

                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($documento['arquivo_url']): ?>
                                        <a href="<?php echo e($documento['arquivo_url']); ?>" target="_blank" rel="noopener" class="gd-btn">Ver arquivo</a>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($documento['portal_url']): ?>
                                        <a href="<?php echo e($documento['portal_url']); ?>" target="_blank" rel="noopener" class="gd-btn">Portal</a>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </aside>
                            </div>
                        </article>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="gd-empty">
                            <h3><?php echo e($hasFiltros ? 'Nenhum documento para estes filtros' : 'Nenhum documento cadastrado'); ?></h3>
                            <p>
                                <?php echo e($hasFiltros ? 'Tente remover algum filtro ou usar uma busca mais ampla.' : 'Cadastre o primeiro documento para começar a acompanhar vencimentos, responsáveis, anexos e aprovações.'); ?>

                            </p>
                            <div class="gd-empty-actions">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasFiltros): ?>
                                    <a href="<?php echo e(url()->current()); ?>" class="gd-btn">Limpar filtros</a>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <a href="<?php echo e($novoDocumentoUrl); ?>" class="gd-btn gd-btn-primary">Cadastrar documento</a>
                            </div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            <aside class="gd-right">
                <section class="gd-card">
                    <h2 class="gd-section-title">Resumo por empresa</h2>
                    <p class="gd-section-subtitle">Empresas com menor score aparecem primeiro.</p>

                    <div class="gd-company-list">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $porEmpresa; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $empresa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <div class="gd-company">
                                <div>
                                    <strong><?php echo e($empresa['empresa']); ?></strong>
                                    <span><?php echo e($empresa['total']); ?> documento(s) · <?php echo e($empresa['criticos']); ?> crítico(s)</span>
                                </div>
                                <b><?php echo e($empresa['score']); ?>%</b>
                            </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <div class="gd-muted-box">Nenhuma empresa encontrada nos filtros atuais.</div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </section>

                <section class="gd-card">
                    <h2 class="gd-section-title">Pendências críticas</h2>
                    <div class="gd-critical-list">
                        <a href="<?php echo e(request()->fullUrlWithQuery(['situacao' => 'vencido'])); ?>"><span>Vencidos</span><strong><?php echo e($resumo['vencidos'] ?? 0); ?></strong></a>
                        <a href="<?php echo e(request()->fullUrlWithQuery(['situacao' => 'vence_7'])); ?>"><span>Vencem em 7 dias</span><strong><?php echo e($resumo['vencem7'] ?? 0); ?></strong></a>
                        <a href="<?php echo e(request()->fullUrlWithQuery(['situacao' => 'sem_responsavel'])); ?>"><span>Sem responsável</span><strong><?php echo e($resumo['semResponsavel'] ?? 0); ?></strong></a>
                        <a href="<?php echo e(request()->fullUrlWithQuery(['situacao' => 'sem_arquivo'])); ?>"><span>Sem anexo</span><strong><?php echo e($resumo['semArquivo'] ?? 0); ?></strong></a>
                        <a href="<?php echo e(request()->fullUrlWithQuery(['situacao' => 'aprovacao_pendente'])); ?>"><span>Aprovação pendente</span><strong><?php echo e($resumo['aprovacaoPendente'] ?? 0); ?></strong></a>
                    </div>
                </section>
            </aside>
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
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views\filament\pages\gestao-documental-enterprise.blade.php ENDPATH**/ ?>