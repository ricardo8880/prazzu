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

<div class="storage-page">
        <section class="storage-hero">
            <div class="storage-hero__grid">
                <div>
                    <span class="storage-kicker">Governança documental</span>
                    <h1>Armazenamento</h1>
                    <p>Controle espaço usado por empresa, limites de plano, arquivos pesados e documentos expirados sem misturar operação documental com gestão de capacidade.</p>
                </div>
                <div class="storage-hero__panel">
                    <span>Uso geral identificado</span>
                    <strong><?php echo e($resumo['percentual_global']); ?>%</strong>
                    <div class="storage-progress <?php echo e($resumo['tom_global']); ?>"><span style="width: <?php echo e(min(100, $resumo['percentual_global'])); ?>%"></span></div>
                    <span><?php echo e($resumo['total_formatado']); ?> usados de <?php echo e($resumo['total_limite_formatado']); ?></span>
                </div>
            </div>
        </section>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if (! ($temColunaLimite)): ?>
            <div class="storage-alert">
                <strong>Limite funcionando por padrão de plano.</strong>
                Para limites manuais por empresa, execute o SQL enviado no pacote: <code>database/sql/2026_06_19_armazenamento_limites.sql</code>.
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <section class="storage-cards" aria-label="Resumo de armazenamento">
            <a class="storage-card" href="<?php echo e(\App\Filament\Pages\Armazenamento::getUrl(['aba' => 'limites'])); ?>"><span>Uso geral</span><strong><?php echo e($resumo['percentual_global']); ?>%</strong><div class="storage-progress <?php echo e($resumo['tom_global']); ?>"><span style="width: <?php echo e(min(100, $resumo['percentual_global'])); ?>%"></span></div><small><?php echo e($resumo['total_formatado']); ?> de <?php echo e($resumo['total_limite_formatado']); ?> · abrir limites</small></a>
            <a class="storage-card" href="<?php echo e(\App\Filament\Pages\Armazenamento::getUrl(['aba' => 'expirados'])); ?>"><span>Espaço recuperável</span><strong><?php echo e($resumo['recuperavel_formatado']); ?></strong><small>Estimativa com expirados/antigos · revisar limpeza</small></a>
            <a class="storage-card" href="<?php echo e(\App\Filament\Pages\Armazenamento::getUrl(['aba' => 'por-empresa'])); ?>"><span>Clientes/Empresas</span><strong><?php echo e(number_format($resumo['empresas'], 0, ',', '.')); ?></strong><small>Com arquivos vinculados · ver ranking</small></a>
            <a class="storage-card" href="<?php echo e(\App\Filament\Pages\Armazenamento::getUrl(['aba' => 'arquivos-pesados'])); ?>"><span>Alertas</span><strong><?php echo e(count($alertas)); ?></strong><small>Itens que pedem atenção operacional · agir agora</small></a>
            <a class="storage-card" href="<?php echo e(\App\Filament\Pages\Armazenamento::getUrl(['aba' => 'retencao'])); ?>"><span>Retenção</span><strong><?php echo e($retencao['counts']['policies'] ?? 0); ?></strong><small>Políticas ativas · arquivar, excluir ou manter</small></a>
        </section>

        <div class="storage-grid">
            <main class="storage-section">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($aba === 'visao-geral'): ?>
                    <div class="storage-section__header"><div><span class="storage-kicker">Painel executivo</span><h2>Saúde do armazenamento</h2><p>Alertas, espaço recuperável e os maiores consumidores em uma leitura rápida.</p></div></div>
                    <div class="storage-mini-grid">
                        <a class="storage-mini-card" href="<?php echo e(\App\Filament\Pages\Armazenamento::getUrl(['aba' => 'expirados'])); ?>"><span class="storage-kicker">Recuperável</span><strong><?php echo e($resumo['recuperavel_formatado']); ?></strong><p>Baseado em arquivos expirados ou antigos encontrados. Clique para revisar.</p></a>
                        <a class="storage-mini-card" href="<?php echo e(\App\Filament\Pages\Armazenamento::getUrl(['aba' => 'arquivos-pesados'])); ?>"><span class="storage-kicker">Arquivos</span><strong><?php echo e(number_format($resumo['total_arquivos'], 0, ',', '.')); ?></strong><p>Total localizado em anexos, documentos e portal. Clique para auditar.</p></a>
                    </div>
                    <div class="storage-alert-list">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $alertas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alerta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <a class="storage-alert-item <?php echo e($alerta['tom']); ?>" href="<?php echo e(\App\Filament\Pages\Armazenamento::getUrl(['aba' => $alerta['aba'] ?? 'visao-geral'])); ?>">
                                <span class="storage-alert-dot"></span>
                                <div><strong><?php echo e($alerta['titulo']); ?></strong><p><?php echo e($alerta['texto']); ?></p></div>
                                <span class="storage-action-link"><?php echo e($alerta['acao'] ?? 'Abrir'); ?></span>
                            </a>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>
                    <div class="storage-section__header"><div><span class="storage-kicker">Top 5</span><h2>Maiores consumidores</h2><p>Clientes/empresas que mais ocupam espaço agora.</p></div></div>
                    <div class="storage-list">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $topConsumidores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $empresa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <article class="storage-row storage-row--action" id="empresa-<?php echo e($empresa['empresa_id'] ?? 'sem-empresa'); ?>">
                                <div>
                                    <h3><?php echo e($empresa['empresa_nome']); ?></h3>
                                    <p><?php echo e($empresa['arquivos']); ?> arquivo(s) · Plano <?php echo e($empresa['plano']); ?></p>
                                    <div class="storage-progress <?php echo e($empresa['tom']); ?>"><span style="width: <?php echo e(min(100, $empresa['percentual'])); ?>%"></span></div>
                                    <div class="storage-meta"><span class="storage-pill <?php echo e($empresa['tom']); ?>"><?php echo e($empresa['percentual']); ?>% do limite</span><span class="storage-pill">Limite <?php echo e($empresa['limite_formatado']); ?></span><span class="storage-pill warning"><?php echo e($empresa['expirados']); ?> expirado(s)</span></div>
                                </div>
                                <div class="storage-action-stack">
                                    <div class="storage-size"><?php echo e($empresa['total_formatado']); ?></div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($empresa['empresa_id'])): ?>
                                        <button type="button" class="storage-action-link" wire:click='mountAction("verCliente", <?php echo json_encode(["empresaId" => (int) $empresa["empresa_id"]], 15, 512) ?>)' wire:loading.attr="disabled" wire:target='mountAction("verCliente", <?php echo json_encode(["empresaId" => (int) $empresa["empresa_id"]], 15, 512) ?>)'>Ver cliente</button>
                                    <?php else: ?>
                                        <span class="storage-pill warning">Sem vínculo</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </article>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <div class="storage-empty">Nenhum arquivo encontrado para análise.</div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php elseif($aba === 'por-empresa'): ?>
                    <div class="storage-section__header"><div><span class="storage-kicker">Empresas</span><h2>Uso de armazenamento por cliente/empresa</h2><p>Controle limite, percentual usado e acúmulo por cliente/empresa.</p></div><strong><?php echo e(count($porEmpresa)); ?></strong></div>
                    <div class="storage-list">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $porEmpresa; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $empresa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <article class="storage-row storage-row--action" id="empresa-<?php echo e($empresa['empresa_id'] ?? 'sem-empresa'); ?>">
                                <div>
                                    <h3><?php echo e($empresa['empresa_nome']); ?></h3>
                                    <p>Maior arquivo: <?php echo e($empresa['maior_arquivo']['nome'] ?? 'Não identificado'); ?></p>
                                    <div class="storage-progress <?php echo e($empresa['tom']); ?>"><span style="width: <?php echo e(min(100, $empresa['percentual'])); ?>%"></span></div>
                                    <div class="storage-meta"><span class="storage-pill <?php echo e($empresa['tom']); ?>"><?php echo e($empresa['percentual']); ?>%</span><span class="storage-pill primary"><?php echo e($empresa['arquivos']); ?> arquivo(s)</span><span class="storage-pill"><?php echo e($empresa['limite_formatado']); ?> de limite</span></div>
                                </div>
                                <div class="storage-action-stack">
                                    <div class="storage-size"><?php echo e($empresa['total_formatado']); ?></div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($empresa['empresa_id'])): ?>
                                        <button type="button" class="storage-action-link" wire:click='mountAction("verCliente", <?php echo json_encode(["empresaId" => (int) $empresa["empresa_id"]], 15, 512) ?>)' wire:loading.attr="disabled" wire:target='mountAction("verCliente", <?php echo json_encode(["empresaId" => (int) $empresa["empresa_id"]], 15, 512) ?>)'>Ver cliente</button>
                                    <?php else: ?>
                                        <span class="storage-pill warning">Sem vínculo</span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </article>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <div class="storage-empty">Nenhuma empresa com arquivos.</div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php elseif($aba === 'arquivos-pesados'): ?>
                    <div class="storage-section__header"><div><span class="storage-kicker">Peso</span><h2>Arquivos mais pesados</h2><p>Arquivos que mais impactam custo e limite.</p></div><strong><?php echo e(count($arquivosPesados)); ?></strong></div>
                    <div class="storage-list">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $arquivosPesados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $arquivo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <article class="storage-row">
                                <div><h3 title="<?php echo e($arquivo['nome']); ?>"><?php echo e($arquivo['nome']); ?></h3><p><?php echo e($arquivo['empresa_nome']); ?> · <?php echo e($arquivo['item_titulo']); ?></p><div class="storage-meta"><span class="storage-pill primary"><?php echo e($arquivo['origem']); ?></span><span class="storage-pill"><?php echo e($arquivo['mime_type'] ?: 'Tipo não informado'); ?></span><span class="storage-pill <?php echo e($arquivo['expirado'] ? 'warning' : 'success'); ?>"><?php echo e($arquivo['expirado'] ? 'Expirado/antigo' : 'Ativo'); ?></span></div></div>
                                <div class="storage-action-stack">
                                    <div class="storage-size"><?php echo e($arquivo['tamanho_formatado']); ?></div>
                                    <a class="storage-action-link" href="<?php echo e(\App\Filament\Pages\Documentos::getUrl(['cluster' => 'fila'])); ?>">Revisar</a>
                                </div>
                            </article>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <div class="storage-empty">Nenhum arquivo pesado encontrado.</div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php elseif($aba === 'expirados'): ?>
                    <div class="storage-section__header"><div><span class="storage-kicker">Limpeza</span><h2>Arquivos expirados ou antigos</h2><p>Itens candidatos a revisão, arquivamento ou exclusão controlada.</p></div><strong><?php echo e(count($arquivosExpirados)); ?></strong></div>
                    <div class="storage-list">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $arquivosExpirados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $arquivo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <article class="storage-row">
                                <div><h3 title="<?php echo e($arquivo['nome']); ?>"><?php echo e($arquivo['nome']); ?></h3><p><?php echo e($arquivo['empresa_nome']); ?> · <?php echo e($arquivo['item_titulo']); ?></p><div class="storage-meta"><span class="storage-pill warning"><?php echo e($arquivo['idade_dias']); ?> dia(s)</span><span class="storage-pill"><?php echo e($arquivo['data_vencimento'] ? 'Venceu em ' . \Carbon\Carbon::parse($arquivo['data_vencimento'])->format('d/m/Y') : 'Arquivo antigo'); ?></span><span class="storage-pill primary"><?php echo e($arquivo['origem']); ?></span></div></div>
                                <div class="storage-action-stack">
                                    <div class="storage-size"><?php echo e($arquivo['tamanho_formatado']); ?></div>
                                    <a class="storage-action-link" href="<?php echo e(\App\Filament\Pages\Documentos::getUrl(['cluster' => 'fila'])); ?>">Revisar</a>
                                </div>
                            </article>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <div class="storage-empty">Nenhum arquivo expirado ou antigo encontrado.</div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <div class="storage-checklist">
                        <strong>Fluxo recomendado de ação</strong>
                        <ol>
                            <li>Conferir se o documento ainda precisa ser retido por obrigação legal.</li>
                            <li>Registrar aprovação interna antes de excluir ou arquivar.</li>
                            <li>Remover somente arquivos sem pendência operacional e com rastreabilidade.</li>
                        </ol>
                    </div>
                <?php elseif($aba === 'retencao'): ?>
                    <div class="storage-section__header">
                        <div><span class="storage-kicker">Governança de arquivos</span><h2>Política de retenção</h2><p>Defina se arquivos são temporários, permanentes, arquivados ou excluídos automaticamente.</p></div>
                        <button type="button" class="storage-action-link" wire:click="processarRetencaoAgora" wire:loading.attr="disabled" wire:target="processarRetencaoAgora">Processar agora</button>
                    </div>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! ($retencao['ready'] ?? false)): ?>
                        <div class="storage-alert" style="margin:1rem">As tabelas de retenção ainda não existem. Execute <strong>php artisan migrate</strong> para ativar cadastro, histórico e processamento automático.</div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <div class="storage-retention-summary">
                        <div class="storage-retention-box"><span>Políticas ativas</span><strong><?php echo e($retencao['counts']['policies'] ?? 0); ?></strong></div>
                        <div class="storage-retention-box"><span>Arquivar agora</span><strong><?php echo e($retencao['counts']['due_archive'] ?? 0); ?></strong></div>
                        <div class="storage-retention-box"><span>Excluir agora</span><strong><?php echo e($retencao['counts']['due_delete'] ?? 0); ?></strong></div>
                        <div class="storage-retention-box"><span>Espaço elegível</span><strong><?php echo e($retencao['counts']['space'] ?? '0 B'); ?></strong></div>
                    </div>

                    <form class="storage-form-grid" wire:submit.prevent="salvarPoliticaRetencao">
                        <div class="storage-field"><label>Nome da política</label><input class="storage-input" type="text" wire:model="retentionForm.name" placeholder="Ex: Temporários 7 dias"></div>
                        <div class="storage-field"><label>Escopo</label><select class="storage-input" wire:model.live="retentionForm.scope_type"><option value="global">Todos os arquivos</option><option value="empresa">Cliente específico</option><option value="origem">Origem do arquivo</option></select></div>
                        <div class="storage-field"><label>Tipo</label><select class="storage-input" wire:model="retentionForm.storage_type"><option value="temporario">Arquivo temporário</option><option value="permanente">Arquivo permanente</option></select></div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($retentionForm['scope_type'] ?? 'global') === 'empresa'): ?>
                            <div class="storage-field"><label>Cliente</label><select class="storage-input" wire:model="retentionForm.empresa_id"><option value="">Selecione</option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $empresasOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $empresaId => $empresaNome): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?><option value="<?php echo e($empresaId); ?>"><?php echo e($empresaNome); ?></option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?></select></div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($retentionForm['scope_type'] ?? 'global') === 'origem'): ?>
                            <div class="storage-field"><label>Origem</label><select class="storage-input" wire:model="retentionForm.origin"><option value="Anexo">Anexos</option><option value="Documento">Documentos</option><option value="Portal">Portal do cliente</option></select></div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <div class="storage-field"><label>Ação automática</label><select class="storage-input" wire:model.live="retentionForm.action"><option value="arquivar">Arquivar após prazo</option><option value="excluir">Excluir após prazo</option><option value="manter">Nunca excluir</option></select></div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($retentionForm['action'] ?? 'arquivar') !== 'manter'): ?>
                            <div class="storage-field"><label>Prazo</label><select class="storage-input" wire:model="retentionForm.retention_days"><option value="7">7 dias</option><option value="30">30 dias</option><option value="90">90 dias</option><option value="365">1 ano</option></select></div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <div class="storage-field storage-field--wide"><label>Observação</label><input class="storage-input" type="text" wire:model="retentionForm.notes" placeholder="Ex: usar para arquivos enviados temporariamente pelo cliente."></div>
                        <div class="storage-field"><label>&nbsp;</label><button class="storage-action-link" type="submit">Salvar política</button></div>
                    </form>

                    <div class="storage-section__header"><div><span class="storage-kicker">Regras cadastradas</span><h2>Políticas em uso</h2><p>A regra mais específica vence: cliente, origem e depois global.</p></div><strong><?php echo e(count($retencao['all_policies'] ?? [])); ?></strong></div>
                    <div class="storage-list">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $retencao['all_policies'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $policy): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <article class="storage-row">
                                <div><h3><?php echo e($policy['name']); ?></h3><p><?php echo e($policy['scope_label']); ?> · <?php echo e(ucfirst($policy['storage_type'])); ?> · <?php echo e($policy['retention_label']); ?></p><div class="storage-meta"><span class="storage-pill <?php echo e($policy['is_active'] ? 'success' : 'warning'); ?>"><?php echo e($policy['is_active'] ? 'Ativa' : 'Pausada'); ?></span><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($policy['notes'])): ?><span class="storage-pill"><?php echo e($policy['notes']); ?></span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></div></div>
                                <div class="storage-action-stack"><button type="button" class="storage-action-link" wire:click="alternarPoliticaRetencao(<?php echo e((int) $policy['id']); ?>)"><?php echo e($policy['is_active'] ? 'Pausar' : 'Ativar'); ?></button></div>
                            </article>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <div class="storage-empty">Nenhuma política cadastrada. Crie a primeira regra acima.</div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <div class="storage-section__header"><div><span class="storage-kicker">Prévia automática</span><h2>Arquivos que entram na próxima execução</h2><p>Estes são os candidatos calculados agora pelas políticas ativas.</p></div><strong><?php echo e(count($retencao['candidates'] ?? [])); ?></strong></div>
                    <div class="storage-list">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $retencao['candidates'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $arquivo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <article class="storage-row"><div><h3 title="<?php echo e($arquivo['nome']); ?>"><?php echo e($arquivo['nome']); ?></h3><p><?php echo e($arquivo['empresa_nome']); ?> · <?php echo e($arquivo['policy_name']); ?></p><div class="storage-meta"><span class="storage-pill <?php echo e($arquivo['action'] === 'excluir' ? 'danger' : 'warning'); ?>"><?php echo e($arquivo['action'] === 'excluir' ? 'Excluir' : 'Arquivar'); ?></span><span class="storage-pill">Venceu em <?php echo e($arquivo['due_at']); ?></span><span class="storage-pill primary"><?php echo e($arquivo['origem']); ?></span></div></div><div class="storage-size"><?php echo e($arquivo['tamanho_formatado']); ?></div></article>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <div class="storage-empty">Nenhum arquivo elegível para arquivar ou excluir agora.</div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <div class="storage-section__header"><div><span class="storage-kicker">Histórico</span><h2>Últimas execuções</h2><p>Rastro de auditoria para saber o que foi feito pela rotina.</p></div></div>
                    <div class="storage-list">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $retencao['recent_events'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <article class="storage-row"><div><h3><?php echo e($event['file_name'] ?? 'Arquivo'); ?></h3><p><?php echo e($event['policy_name'] ?? 'Política removida'); ?> · <?php echo e($event['message'] ?? ''); ?></p><div class="storage-meta"><span class="storage-pill <?php echo e(($event['status'] ?? '') === 'processado' ? 'success' : 'danger'); ?>"><?php echo e($event['status'] ?? 'registro'); ?></span><span class="storage-pill"><?php echo e($event['action'] ?? '-'); ?></span></div></div><div class="storage-size"><?php echo e(\Carbon\Carbon::parse($event['created_at'])->format('d/m/Y H:i')); ?></div></article>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <div class="storage-empty">Ainda não existe histórico de processamento.</div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php elseif($aba === 'limites'): ?>
                    <div class="storage-section__header"><div><span class="storage-kicker">Capacidade</span><h2>Limites de armazenamento</h2><p>Ranking de empresas mais próximas do limite.</p></div><strong><?php echo e(count($limites)); ?></strong></div>
                    <div class="storage-list">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $limites; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $empresa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <article class="storage-row">
                                <div><h3><?php echo e($empresa['empresa_nome']); ?></h3><p><?php echo e($empresa['total_formatado']); ?> usados de <?php echo e($empresa['limite_formatado']); ?></p><div class="storage-progress <?php echo e($empresa['tom']); ?>"><span style="width: <?php echo e(min(100, $empresa['percentual'])); ?>%"></span></div><div class="storage-meta"><span class="storage-pill <?php echo e($empresa['tom']); ?>"><?php echo e($empresa['percentual']); ?>% usado</span><span class="storage-pill">Plano <?php echo e($empresa['plano']); ?></span></div></div>
                                <div class="storage-action-stack">
                                    <div class="storage-size"><?php echo e($empresa['limite_formatado']); ?></div>
                                    <a class="storage-action-link" href="<?php echo e(\App\Filament\Pages\Armazenamento::getUrl(['aba' => 'expirados'])); ?>">Limpar</a>
                                </div>
                            </article>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <div class="storage-empty">Nenhum limite para exibir.</div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </main>

            <aside class="storage-insights" aria-label="Insights de armazenamento">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $insights; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $insight): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <article class="storage-insight">
                        <span class="storage-pill <?php echo e($insight['tom']); ?>"><?php echo e(ucfirst($insight['tom'])); ?></span>
                        <strong><?php echo e($insight['titulo']); ?></strong>
                        <p><?php echo e($insight['texto']); ?></p>
                    </article>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>

                <article class="storage-insight">
                    <strong>Como usar esta página</strong>
                    <p>Comece pelos limites, revise arquivos pesados e configure Política de Retenção para arquivar, excluir ou manter arquivos com auditoria.</p>
                </article>
            </aside>
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
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views\filament\pages\armazenamento.blade.php ENDPATH**/ ?>