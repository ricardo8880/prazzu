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
        $whiteLabel = \App\Support\WhiteLabelSettings::make();
        $brandName = $whiteLabel->displayName();
        $enterpriseLabel = $whiteLabel->enterpriseLabel();
    ?>


    <div class="prazzu80-page" data-prazzu80-page>
        <section class="prazzu80-hero">
            <div>
                <span class="prazzu80-kicker"><?php echo e($config['group'] ?? strtoupper($brandName)); ?></span>
                <h1><?php echo e($config['title'] ?? $enterpriseLabel); ?></h1>
                <p><?php echo e($config['subtitle'] ?? 'Operação, compliance, documentos, clientes e cobrança em uma única plataforma.'); ?></p>
            </div>
            <div class="prazzu80-hero-actions">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($quickActions ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <span><?php echo e($action); ?></span>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
        </section>

        <section class="prazzu80-search-card">
            <div>
                <strong>Busca global inteligente</strong>
                <p><?php echo e($searchPlaceholder ?? 'Buscar...'); ?></p>
                <small>Filtre os registros reais exibidos nesta página sem sair do módulo.</small>
            </div>
            <div class="prazzu80-search-controls">
                <input type="search" placeholder="Buscar por título, empresa, status, responsável..." data-prazzu80-search aria-label="Buscar nesta página">
                <select data-prazzu80-status aria-label="Filtrar por situação">
                    <option value="all">Todas as situações</option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($globalSearch['filters'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $filter): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <option value="<?php echo e(\Illuminate\Support\Str::lower($filter)); ?>"><?php echo e($filter); ?></option>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </select>
                <button type="button" data-prazzu80-clear>Limpar</button>
            </div>
        </section>

        <section class="prazzu80-no-results" data-prazzu80-empty hidden>
            <strong>Nenhum resultado encontrado.</strong>
            <p>Altere a busca ou limpe os filtros para voltar a visualizar os registros desta página.</p>
        </section>

        <section class="prazzu80-stats">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($stats ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <article>
                    <span><?php echo e($stat['label']); ?></span>
                    <strong><?php echo e($stat['value']); ?></strong>
                    <small><?php echo e($stat['hint']); ?></small>
                </article>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </section>


        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($module ?? null) === 'clientes'): ?>
            <section class="prazzu80-grid three">
                <article class="prazzu80-card">
                    <header><div><h2>Status de contrato</h2><p>Carteira interna separada por Ativo, Implementação, risco ou churn.</p></div></header>
                    <div class="prazzu80-list compact">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($clientCrm['statusSummary'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <div class="prazzu80-list-row"><div><strong><?php echo e($row['label']); ?></strong><span>Clientes nesse estágio</span></div><em><?php echo e($row['count']); ?></em></div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <div class="prazzu80-empty">Nenhum cliente cadastrado.</div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </article>

                <article class="prazzu80-card">
                    <header><div><h2>Health Score</h2><p>Classificação calculada usando atrasos, pendências, aprovações e contato recente.</p></div></header>
                    <div class="prazzu80-list compact">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($clientCrm['healthSummary'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <div class="prazzu80-list-row"><div><strong><?php echo e($row['label']); ?></strong><span>Saúde da carteira</span></div><em class="<?php echo e(($row['tone'] ?? '') === 'danger' ? 'danger' : ''); ?>"><?php echo e($row['count']); ?></em></div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <div class="prazzu80-empty">Sem dados suficientes para calcular saúde.</div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </article>

                <article class="prazzu80-card">
                    <header><div><h2>Onboarding automático</h2><p>Clientes fechados ou ativos com tarefas pendentes para implantação.</p></div></header>
                    <div class="prazzu80-list compact">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($clientCrm['onboarding'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <div class="prazzu80-note"><strong><?php echo e($row['client']); ?></strong><p><?php echo e($row['status']); ?> · <?php echo e($row['tasks']); ?> tarefa(s) abertas · Saúde: <?php echo e($row['health']); ?></p></div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <div class="prazzu80-empty">Nenhum onboarding pendente.</div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </article>
            </section>

            <section class="prazzu80-card">
                <header><div><h2>Clientes — visão de tabela CRM</h2><p>Planilha operacional com campos personalizados, LTV, decisor, WhatsApp, última reunião e saúde.</p></div></header>
                <div class="prazzu80-table-wrap">
                    <table class="prazzu80-table">
                        <thead>
                            <tr>
                                <th>Cliente</th><th>Status de contrato</th><th>LTV</th><th>Decisor / contato</th><th>E-mail</th><th>WhatsApp</th><th>Última reunião</th><th>Health Score</th><th>Pendências</th><th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($clientCrm['clients'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $client): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <tr>
                                    <td><strong><?php echo e($client['name']); ?></strong><br><small><?php echo e($client['document']); ?></small></td>
                                    <td><span class="prazzu80-badge <?php echo e(($client['contract_status'] ?? '') === 'Em risco' ? 'vencido' : 'ok'); ?>"><?php echo e($client['contract_status']); ?></span></td>
                                    <td>R$ <?php echo e(number_format((float) ($client['ltv'] ?? 0), 2, ',', '.')); ?></td>
                                    <td><?php echo e($client['contact_name'] ?? '-'); ?></td>
                                    <td><?php echo e($client['contact_email'] ?? '-'); ?></td>
                                    <td><?php echo e($client['contact_whatsapp'] ?? '-'); ?></td>
                                    <td><?php echo e($client['last_meeting'] ?? 'Sem registro'); ?></td>
                                    <td><span class="prazzu80-badge <?php echo e($client['health_tone'] ?? ''); ?>"><?php echo e($client['health_label']); ?> · <?php echo e($client['health_score']); ?>%</span></td>
                                    <td><?php echo e($client['open_items']); ?> abertas · <?php echo e($client['late_items']); ?> atrasadas</td>
                                    <td>
                                        <button type="button" class="prazzu80-mini-button" wire:click="criarOnboarding(<?php echo e((int) ($client['id'] ?? 0)); ?>)">Criar onboarding</button>
                                    </td>
                                </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <tr><td colspan="10" class="prazzu80-empty">Nenhum cliente encontrado no banco.</td></tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="prazzu80-card">
                <header><div><h2>Registrar reunião / ata do cliente</h2><p>Atualiza a data da última reunião usando a timeline real do cliente, sem dado fictício.</p></div></header>
                <form wire:submit.prevent="registrarReuniao" class="prazzu80-form">
                    <label>
                        <span>Cliente</span>
                        <select wire:model="meetingEmpresaId">
                            <option value="">Selecione</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($clientFormOptions['empresas'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $empresa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <option value="<?php echo e($empresa['id']); ?>"><?php echo e($empresa['name']); ?></option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </select>
                    </label>
                    <label>
                        <span>Título</span>
                        <input type="text" wire:model.defer="meetingTitulo" placeholder="Ex: Reunião de alinhamento">
                    </label>
                    <label class="wide">
                        <span>Ata / decisão</span>
                        <textarea wire:model.defer="meetingDescricao" rows="3" placeholder="Registre o que foi decidido na call"></textarea>
                    </label>
                    <button type="submit">Salvar reunião</button>
                </form>
            </section>

            <section class="prazzu80-grid two">
                <article class="prazzu80-card">
                    <header><div><h2>Integração de e-mail / histórico</h2><p>Mensagens relacionadas aos clientes ficam próximas da tarefa e do contrato.</p></div></header>
                    <div class="prazzu80-list compact">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($clientCrm['emailHistory'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <div class="prazzu80-note"><strong><?php echo e($mail['nome_fantasia'] ?? $mail['razao_social'] ?? $mail['titulo'] ?? 'Histórico'); ?></strong><p><?php echo e(\Illuminate\Support\Str::limit($mail['mensagem'] ?? '-', 130)); ?></p></div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <div class="prazzu80-empty">Nenhum histórico de e-mail encontrado nos comentários.</div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </article>

                <article class="prazzu80-card">
                    <header><div><h2>Campos personalizados usados</h2><p>CRM pronto para operação sem dados estáticos: tudo vem de empresas, pagamentos e itens de controle.</p></div></header>
                    <div class="prazzu80-feature-grid">
                        <div class="prazzu80-feature ok"><strong>Status de contrato</strong><span>Calculado por status, ativo, atrasos e tarefas.</span></div>
                        <div class="prazzu80-feature ok"><strong>LTV</strong><span>Soma dos pagamentos recebidos do cliente.</span></div>
                        <div class="prazzu80-feature ok"><strong>Ponto de contato</strong><span>Responsável, e-mail e telefone da empresa.</span></div>
                        <div class="prazzu80-feature ok"><strong>Última reunião</strong><span>Busca em timeline por reunião, ata ou call.</span></div>
                        <div class="prazzu80-feature ok"><strong>Health Score</strong><span>Calculado por risco, atraso, aprovação e relacionamento.</span></div>
                        <div class="prazzu80-feature ok"><strong>Onboarding</strong><span>Fila baseada nas tarefas abertas por cliente.</span></div>
                    </div>
                </article>
            </section>
        <?php elseif(($module ?? null) === 'portal-cliente'): ?>
            <section class="prazzu80-card">
                <header><div><h2>Progresso do projeto</h2><p>Battery chart para o cliente entender rapidamente o quanto já foi concluído.</p></div></header>
                <div class="prazzu80-battery-wrap">
                    <div class="prazzu80-battery"><i style="width: <?php echo e($portalExperience['progress']['percent'] ?? 0); ?>%"></i></div>
                    <strong><?php echo e($portalExperience['progress']['percent'] ?? 0); ?>%</strong>
                    <span><?php echo e($portalExperience['progress']['done'] ?? 0); ?> concluído(s), <?php echo e($portalExperience['progress']['pending'] ?? 0); ?> pendente(s), <?php echo e($portalExperience['progress']['review'] ?? 0); ?> em revisão.</span>
                </div>
            </section>

            <section class="prazzu80-grid two">
                <article class="prazzu80-card">
                    <header><div><h2>Pronto para revisão / aprovação</h2><p>Lista filtrada para o cliente ver só o que precisa da atenção dele.</p></div></header>
                    <div class="prazzu80-list compact">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($portalExperience['visibleItems'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <div class="prazzu80-list-row">
                                <div><strong><?php echo e($item['titulo'] ?? 'Item'); ?></strong><span><?php echo e($item['empresa'] ?? '-'); ?> · <?php echo e(ucfirst(str_replace('_', ' ', $item['status'] ?? '-'))); ?></span></div>
                                <em><?php echo e($item['progress'] ?? 0); ?>%</em>
                            </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <div class="prazzu80-empty">Nenhum item liberado para o portal.</div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </article>

                <article class="prazzu80-card">
                    <header><div><h2>Calendário de entregas</h2><p>Deadlines visíveis para reduzir perguntas sobre prazo.</p></div></header>
                    <div class="prazzu80-list compact">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($portalExperience['calendar'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <div class="prazzu80-list-row">
                                <div><strong><?php echo e($item['titulo']); ?></strong><span><?php echo e($item['empresa'] ?? '-'); ?></span></div>
                                <em class="<?php echo e(($item['is_late'] ?? false) ? 'danger' : ''); ?>"><?php echo e(!empty($item['data_vencimento']) ? \Carbon\Carbon::parse($item['data_vencimento'])->format('d/m/Y') : '-'); ?></em>
                            </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <div class="prazzu80-empty">Nenhuma entrega com vencimento.</div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </article>
            </section>

            <section class="prazzu80-grid three">
                <article class="prazzu80-card">
                    <header><div><h2>Wiki / documentos</h2><p>Documentos, manuais, contratos e links úteis do projeto.</p></div></header>
                    <div class="prazzu80-list compact">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($portalExperience['documents'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <div class="prazzu80-note"><strong><?php echo e($doc['nome_original'] ?? $doc['nome'] ?? $doc['titulo'] ?? 'Documento'); ?></strong><p><?php echo e($doc['titulo'] ?? 'Documento do portal'); ?></p></div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <div class="prazzu80-empty">Nenhum documento disponível.</div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </article>

                <article class="prazzu80-card">
                    <header><div><h2>Atas de reunião</h2><p>Decisões de calls e alinhamentos compartilhados com o cliente.</p></div></header>
                    <div class="prazzu80-list compact">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($portalExperience['meetingNotes'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $note): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <div class="prazzu80-note"><strong><?php echo e($note['titulo'] ?? 'Ata'); ?></strong><p><?php echo e(\Illuminate\Support\Str::limit($note['descricao'] ?? ($note['item_titulo'] ?? '-'), 120)); ?></p></div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <div class="prazzu80-empty">Nenhuma ata registrada.</div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </article>

                <article class="prazzu80-card">
                    <header><div><h2>Suporte / solicitações</h2><p>Pedidos do cliente caem na fila de trabalho sem se perder no WhatsApp.</p></div></header>
                    <form wire:submit.prevent="criarSolicitacaoSuporte" class="prazzu80-form single">
                        <label>
                            <span>Cliente</span>
                            <select wire:model="supportEmpresaId">
                                <option value="">Selecione</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($portalFormOptions['empresas'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $empresa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <option value="<?php echo e($empresa['id']); ?>"><?php echo e($empresa['name']); ?></option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </select>
                        </label>
                        <label>
                            <span>Título da solicitação</span>
                            <input type="text" wire:model.defer="supportTitulo" placeholder="Ex: Solicitar ajuste no projeto">
                        </label>
                        <label>
                            <span>Prazo desejado</span>
                            <input type="date" wire:model.defer="supportDataVencimento">
                        </label>
                        <label>
                            <span>Descrição</span>
                            <textarea wire:model.defer="supportDescricao" rows="3" placeholder="Explique a solicitação do cliente"></textarea>
                        </label>
                        <button type="submit">Enviar solicitação</button>
                    </form>
                    <div class="prazzu80-list compact">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($portalExperience['supportQueue'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <div class="prazzu80-note"><strong><?php echo e($item['titulo'] ?? 'Solicitação'); ?></strong><p><?php echo e(ucfirst($item['status'] ?? '-')); ?> · <?php echo e($item['is_late'] ?? false ? 'Atrasado' : 'Dentro do fluxo'); ?></p></div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <div class="prazzu80-empty">Nenhuma solicitação de suporte.</div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </article>
            </section>

            <section class="prazzu80-grid two">
                <article class="prazzu80-card">
                    <header><div><h2>Chat do projeto</h2><p>Conversas recentes centralizadas no item, evitando mensagens soltas.</p></div></header>
                    <form wire:submit.prevent="enviarMensagemChat" class="prazzu80-form single">
                        <label>
                            <span>Item do portal</span>
                            <select wire:model="chatItemId">
                                <option value="">Selecione</option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($portalFormOptions['portalItems'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $itemOption): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <option value="<?php echo e($itemOption['id']); ?>"><?php echo e($itemOption['name']); ?></option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </select>
                        </label>
                        <label>
                            <span>Mensagem</span>
                            <textarea wire:model.defer="chatMensagem" rows="3" placeholder="Digite uma mensagem para o histórico do projeto"></textarea>
                        </label>
                        <button type="submit">Enviar mensagem</button>
                    </form>
                    <div class="prazzu80-list compact">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($portalExperience['chat'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <div class="prazzu80-note"><strong><?php echo e($message['titulo'] ?? 'Mensagem'); ?></strong><p><?php echo e(\Illuminate\Support\Str::limit($message['comentario'] ?? '-', 140)); ?></p></div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <div class="prazzu80-empty">Nenhuma conversa registrada.</div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </article>

                <article class="prazzu80-card">
                    <header><div><h2>O que o cliente enxerga</h2><p>Portal limpo, sem tarefas internas da equipe.</p></div></header>
                    <div class="prazzu80-feature-grid">
                        <div class="prazzu80-feature ok"><strong>Progresso</strong><span>Porcentagem geral do projeto.</span></div>
                        <div class="prazzu80-feature ok"><strong>Revisão</strong><span>Somente itens liberados ou em aprovação.</span></div>
                        <div class="prazzu80-feature ok"><strong>Documentos</strong><span>Anexos e wiki do projeto.</span></div>
                        <div class="prazzu80-feature ok"><strong>Atas</strong><span>Histórico de decisões.</span></div>
                        <div class="prazzu80-feature ok"><strong>Calendário</strong><span>Datas de entrega e vencimentos.</span></div>
                        <div class="prazzu80-feature ok"><strong>Suporte e chat</strong><span>Solicitações e mensagens centralizadas.</span></div>
                    </div>
                </article>
            </section>
        <?php else: ?>

        <section class="prazzu80-card">
            <header><div><h2>Primeiros passos / Empty state guiado</h2><p>O usuário sempre sabe o que fazer para deixar o módulo pronto.</p></div></header>
            <div class="prazzu80-onboarding">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($onboarding ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <div class="<?php echo e(($step['done'] ?? false) ? 'done' : 'todo'); ?>">
                        <strong><?php echo e(($step['done'] ?? false) ? '✓' : '•'); ?> <?php echo e($step['title']); ?></strong>
                        <span><?php echo e($step['hint']); ?></span>
                    </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
        </section>

        <section class="prazzu80-grid two">
            <article class="prazzu80-card">
                <header><div><h2>Funcionalidades enterprise</h2><p>Checklist do roadmap interno, sem depender de API externa.</p></div></header>
                <div class="prazzu80-feature-grid">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($features ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feature): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="prazzu80-feature <?php echo e(($feature['status'] ?? '') === 'ativo' ? 'ok' : 'todo'); ?>">
                            <strong><?php echo e($feature['name']); ?></strong>
                            <span><?php echo e(($feature['status'] ?? '') === 'ativo' ? 'Ativo' : 'Pendente de tabela/SQL'); ?></span>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            </article>

            <article class="prazzu80-card">
                <header><div><h2>Permissões e governança</h2><p>Controle por perfil, cliente, módulo, ação e área sensível.</p></div></header>
                <div class="prazzu80-permissions">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($permissions ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $permission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div><strong><?php echo e($permission['area']); ?></strong><span><?php echo e($permission['level']); ?></span></div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            </article>
        </section>

        <section class="prazzu80-grid two">
            <article class="prazzu80-card">
                <header><div><h2>KPIs executivos</h2><p>Indicadores de operação, SLA, documentos, produtividade e cobrança.</p></div></header>
                <div class="prazzu80-kpi-grid">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($kpis ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kpi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div><span><?php echo e($kpi['label']); ?></span><strong><?php echo e($kpi['value']); ?></strong></div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            </article>

            <article class="prazzu80-card">
                <header><div><h2>Compliance engine</h2><p>Alertas internos de vencimento, SLA, contrato, responsável e risco.</p></div></header>
                <div class="prazzu80-list compact">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($compliance ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="prazzu80-list-row">
                            <div><strong><?php echo e($row['label']); ?></strong><span><?php echo e(ucfirst($row['state'] ?? 'neutral')); ?></span></div>
                            <em class="<?php echo e(($row['state'] ?? '') === 'danger' ? 'danger' : ''); ?>"><?php echo e($row['value']); ?></em>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            </article>
        </section>

        <section class="prazzu80-card">
            <header><div><h2>Kanban operacional</h2><p>Fluxo com status, prioridade, SLA, vencimento, bloqueio e responsável.</p></div></header>
            <div class="prazzu80-kanban">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($kanban ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $column): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <div class="prazzu80-kanban-column">
                        <div class="prazzu80-kanban-title"><strong><?php echo e($column['label']); ?></strong><span><?php echo e(count($column['items'] ?? [])); ?></span></div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($column['items'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <div class="prazzu80-task-card <?php echo e(($item['is_blocked'] ?? false) ? 'blocked' : ''); ?>">
                                <strong><?php echo e($item['titulo'] ?? 'Sem título'); ?></strong>
                                <p><?php echo e($item['empresa'] ?? '-'); ?></p>
                                <div class="prazzu80-task-meta">
                                    <span class="priority"><?php echo e(ucfirst($item['prioridade'] ?? 'normal')); ?></span>
                                    <span class="sla <?php echo e($item['sla_state'] ?? 'sem_sla'); ?>">SLA <?php echo e(str_replace('_', ' ', $item['sla_state'] ?? 'sem SLA')); ?></span>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($item['is_blocked'] ?? false)): ?><span class="blocked">Bloqueado</span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                                <div class="prazzu80-progress"><i style="width: <?php echo e($item['progress'] ?? 0); ?>%"></i></div>
                            </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <div class="prazzu80-empty-small">Nenhum item neste status.</div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
        </section>

        <section class="prazzu80-grid two">
            <article class="prazzu80-card">
                <header><div><h2>Calendário e vencimentos</h2><p>Datas críticas, contratos, documentos, SLA e cobranças.</p></div></header>
                <div class="prazzu80-list">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($calendar ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="prazzu80-list-row">
                            <div><strong><?php echo e($item['titulo']); ?></strong><span><?php echo e($item['empresa'] ?? '-'); ?></span></div>
                            <em class="<?php echo e(($item['is_late'] ?? false) ? 'danger' : ''); ?>"><?php echo e(!empty($item['data_vencimento']) ? \Carbon\Carbon::parse($item['data_vencimento'])->format('d/m/Y') : '-'); ?></em>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="prazzu80-empty">Nenhum vencimento encontrado.</div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </article>

            <article class="prazzu80-card">
                <header><div><h2>Gantt real / planejamento</h2><p>Barras por prazo, progresso, bloqueios e dependências.</p></div></header>
                <div class="prazzu80-gantt">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($gantt ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="prazzu80-gantt-row">
                            <div><strong><?php echo e($row['title']); ?></strong><span><?php echo e($row['start']); ?> → <?php echo e($row['end']); ?> · <?php echo e(ucfirst(str_replace('_', ' ', $row['status'] ?? ''))); ?></span></div>
                            <div><div class="prazzu80-gantt-bar"><i style="width: <?php echo e($row['progress'] ?? 0); ?>%"></i></div><small><?php echo e($row['empresa'] ?? '-'); ?> <?php echo e(($row['is_blocked'] ?? false) ? '· bloqueado' : ''); ?></small></div>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="prazzu80-empty">Nenhum item para Gantt.</div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </article>
        </section>

        <section class="prazzu80-grid two">
            <article class="prazzu80-card">
                <header><div><h2>Timeline operacional / auditoria</h2><p>Alterações, comentários, documentos, aprovações e evidências.</p></div></header>
                <div class="prazzu80-timeline">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($timeline ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div>
                            <b><?php echo e($event['titulo'] ?? 'Evento'); ?></b>
                            <p><?php echo e(\Illuminate\Support\Str::limit($event['descricao'] ?? ($event['item_titulo'] ?? '-'), 140)); ?></p>
                            <small><?php echo e($event['tipo'] ?? 'timeline'); ?> · <?php echo e(!empty($event['created_at']) ? \Carbon\Carbon::parse($event['created_at'])->format('d/m/Y H:i') : '-'); ?></small>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="prazzu80-empty">Nenhum evento registrado.</div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </article>

            <article class="prazzu80-card">
                <header><div><h2>Dependências visuais</h2><p>O usuário entende o que bloqueia cada entrega.</p></div></header>
                <div class="prazzu80-list compact">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($dependencies ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dep): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="prazzu80-note"><strong><?php echo e($dep['atual'] ?? 'Item'); ?></strong><p>Depende de: <?php echo e($dep['depende'] ?? '-'); ?> · <?php echo e($dep['type'] ?? 'finish_to_start'); ?></p></div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="prazzu80-empty">Nenhuma dependência cadastrada.</div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </article>
        </section>

        <section class="prazzu80-grid three">
            <article class="prazzu80-card">
                <header><div><h2>Central de aprovações</h2><p>Fila única de documentos, contratos e processos.</p></div></header>
                <div class="prazzu80-list compact">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($approvals ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $approval): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="prazzu80-note"><strong><?php echo e($approval['titulo'] ?? 'Aprovação'); ?></strong><p><?php echo e(ucfirst($approval['status'] ?? 'pendente')); ?> · <?php echo e($approval['nome_fantasia'] ?? $approval['razao_social'] ?? '-'); ?></p></div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="prazzu80-empty">Nenhuma aprovação pendente.</div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </article>

            <article class="prazzu80-card">
                <header><div><h2>Gestão documental</h2><p>Arquivos, versões, aprovações e histórico.</p></div></header>
                <div class="prazzu80-list compact">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($documents ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="prazzu80-note"><strong><?php echo e($doc['nome_original'] ?? $doc['nome'] ?? $doc['titulo'] ?? 'Documento'); ?></strong><p><?php echo e($doc['titulo'] ?? 'Sem vínculo'); ?></p></div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="prazzu80-empty">Nenhum documento.</div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </article>

            <article class="prazzu80-card">
                <header><div><h2>Workflow documental</h2><p>Rascunho, aprovação, validade e vencimento.</p></div></header>
                <div class="prazzu80-list compact">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($documentWorkflow ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wf): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="prazzu80-list-row"><div><strong><?php echo e($wf['label']); ?></strong><span>Documentos/processos</span></div><em><?php echo e($wf['count']); ?></em></div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            </article>
        </section>

        <section class="prazzu80-grid three">
            <article class="prazzu80-card">
                <header><div><h2>Comentários e menções</h2><p>Colaboração operacional por item.</p></div></header>
                <div class="prazzu80-list compact">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($comments ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="prazzu80-note"><strong><?php echo e($comment['titulo'] ?? 'Comentário'); ?></strong><p><?php echo e(\Illuminate\Support\Str::limit($comment['comentario'] ?? '-', 120)); ?></p></div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="prazzu80-empty">Nenhum comentário.</div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </article>

            <article class="prazzu80-card">
                <header><div><h2>Notificações internas</h2><p>Badge/polling visual sem websocket externo.</p></div></header>
                <div class="prazzu80-list compact">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($notifications ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="prazzu80-note"><strong><?php echo e($notification['titulo'] ?? $notification['type'] ?? 'Notificação'); ?></strong><p><?php echo e(\Illuminate\Support\Str::limit($notification['mensagem'] ?? $notification['data'] ?? '-', 120)); ?></p></div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="prazzu80-empty">Nenhuma notificação.</div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </article>

            <article class="prazzu80-card">
                <header><div><h2>Time tracking</h2><p>Horas registradas e produtividade.</p></div></header>
                <div class="prazzu80-list compact">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($timeTracking ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $time): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="prazzu80-note"><strong><?php echo e($time['titulo'] ?? 'Registro de tempo'); ?></strong><p><?php echo e(number_format(($time['total_seconds'] ?? 0) / 60, 0, ',', '.')); ?> min · <?php echo e($time['notes'] ?? '-'); ?></p></div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="prazzu80-empty">Nenhum tempo registrado.</div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </article>
        </section>

        <section class="prazzu80-grid two">
            <article class="prazzu80-card">
                <header><div><h2>Builder de automação visual</h2><p>Regras SE/ENTÃO usando somente dados internos.</p></div></header>
                <div class="prazzu80-list compact">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($automationBuilder ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="prazzu80-note"><strong>SE <?php echo e($rule['if']); ?></strong><p>ENTÃO <?php echo e($rule['then']); ?> · <?php echo e(($rule['active'] ?? false) ? 'ativo' : 'inativo'); ?></p></div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            </article>

            <article class="prazzu80-card">
                <header><div><h2>Cobrança inteligente interna</h2><p>Status financeiro, vencimento, régua, bloqueio e recuperação.</p></div></header>
                <div class="prazzu80-list compact">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($billing ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pay): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="prazzu80-list-row"><div><strong><?php echo e($pay['nome_fantasia'] ?? $pay['razao_social'] ?? 'Cliente'); ?></strong><span><?php echo e($pay['status'] ?? '-'); ?> · <?php echo e(!empty($pay['vencimento']) ? \Carbon\Carbon::parse($pay['vencimento'])->format('d/m/Y') : '-'); ?></span></div><em>R$ <?php echo e(number_format((float)($pay['valor'] ?? 0), 2, ',', '.')); ?></em></div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <div class="prazzu80-empty">Nenhuma cobrança encontrada.</div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </article>
        </section>

        <section class="prazzu80-grid two">
            <article class="prazzu80-card">
                <header><div><h2>Relatórios executivos</h2><p>Modelos gerenciais prontos para uso.</p></div></header>
                <div class="prazzu80-list compact">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($reports ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $report): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="prazzu80-note"><strong><?php echo e($report['title']); ?></strong><p><?php echo e($report['description']); ?></p></div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            </article>

            <article class="prazzu80-card">
                <header><div><h2>White label</h2><p>Identidade e limites por empresa/tenant usando dados internos.</p></div></header>
                <div class="prazzu80-list compact">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ($whiteLabel ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <div class="prazzu80-list-row"><div><strong><?php echo e($wl['label']); ?></strong><span>Configuração atual</span></div><em><?php echo e($wl['value']); ?></em></div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            </article>
        </section>

        <section class="prazzu80-card">
            <header><div><h2>Tabela executiva</h2><p>Visão consolidada para gestão, auditoria e operação.</p></div></header>
            <div class="prazzu80-table-wrap">
                <table class="prazzu80-table">
                    <thead><tr><th>Item</th><th>Empresa</th><th>Tipo</th><th>Status</th><th>Prioridade</th><th>Responsável</th><th>Vencimento</th><th>SLA</th><th>Bloqueio</th></tr></thead>
                    <tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = ($items ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <tr>
                                <td><strong><?php echo e($item['titulo']); ?></strong></td>
                                <td><?php echo e($item['empresa'] ?? '-'); ?></td>
                                <td><?php echo e(ucfirst($item['tipo'] ?? '-')); ?></td>
                                <td><span class="prazzu80-badge"><?php echo e(ucfirst(str_replace('_', ' ', $item['status'] ?? '-'))); ?></span></td>
                                <td><?php echo e(ucfirst($item['prioridade'] ?? '-')); ?></td>
                                <td><?php echo e($item['responsavel_nome'] ?? '-'); ?></td>
                                <td class="<?php echo e(($item['is_late'] ?? false) ? 'danger' : ''); ?>"><?php echo e(!empty($item['data_vencimento']) ? \Carbon\Carbon::parse($item['data_vencimento'])->format('d/m/Y') : '-'); ?></td>
                                <td><span class="prazzu80-badge <?php echo e($item['sla_state'] ?? ''); ?>"><?php echo e(str_replace('_', ' ', $item['sla_state'] ?? 'sem SLA')); ?></span></td>
                                <td><?php echo e(($item['is_blocked'] ?? false) ? 'Bloqueado' : 'Livre'); ?></td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <tr><td colspan="9" class="prazzu80-empty">Nenhum registro encontrado. Use as ações rápidas acima para criar o primeiro item.</td></tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <script>
        (() => {
            const root = document.querySelector('[data-prazzu80-page]');

            if (! root) {
                return;
            }

            const search = root.querySelector('[data-prazzu80-search]');
            const status = root.querySelector('[data-prazzu80-status]');
            const clear = root.querySelector('[data-prazzu80-clear]');
            const empty = root.querySelector('[data-prazzu80-empty]');
            const searchableSelectors = [
                '.prazzu80-card',
                '.prazzu80-stats article',
                '.prazzu80-kanban-column',
                '.prazzu80-task-card',
                '.prazzu80-list-row',
                '.prazzu80-note',
                '.prazzu80-feature',
                '.prazzu80-table tbody tr',
            ].join(',');

            const normalize = (value) => (value || '')
                .toString()
                .toLowerCase()
                .normalize('NFD')
                .replace(/[̀-ͯ]/g, '');

            const items = Array.from(root.querySelectorAll(searchableSelectors))
                .filter((item) => ! item.closest('[data-prazzu80-empty]'));

            const applyFilters = () => {
                const term = normalize(search?.value || '');
                const selected = normalize(status?.value || 'all');
                let visible = 0;

                items.forEach((item) => {
                    const text = normalize(item.textContent || '');
                    const matchesText = term === '' || text.includes(term);
                    const matchesStatus = selected === 'all' || text.includes(selected);
                    const show = matchesText && matchesStatus;

                    item.hidden = ! show;

                    if (show) {
                        visible++;
                    }
                });

                if (empty) {
                    empty.hidden = visible > 0 || items.length === 0;
                }
            };

            search?.addEventListener('input', applyFilters);
            status?.addEventListener('change', applyFilters);
            clear?.addEventListener('click', () => {
                if (search) {
                    search.value = '';
                }

                if (status) {
                    status.value = 'all';
                }

                applyFilters();
                search?.focus();
            });
        })();
    </script>

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
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views\filament\pages\prazzu-enterprise-module.blade.php ENDPATH**/ ?>