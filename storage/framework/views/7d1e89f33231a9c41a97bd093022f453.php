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


    <div class="onboarding-prazzu">
        <section class="onboarding-hero">
            <div>
                <span>CONFIGURAÇÃO GUIADA</span>
                <h1>Onboarding funcional da empresa</h1>
                <p>Prepare a estrutura inicial, ative recursos e aplique modelos reais que alimentam as configurações usadas pelos módulos.</p>
            </div>

            <div class="onboarding-score">
                <strong><?php echo e($this->resumo['progresso']); ?>%</strong>
                <small>implantação concluída</small>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($data['finalizado_em'])): ?>
                    <em>Finalizado em <?php echo e(\Carbon\Carbon::parse($data['finalizado_em'])->format('d/m/Y H:i')); ?></em>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </section>

        <section class="onboarding-metrics">
            <article><span>Recursos ativos</span><strong><?php echo e($this->resumo['recursos_ativos']); ?>/<?php echo e($this->resumo['total_recursos']); ?></strong></article>
            <article><span>Modelo aplicado</span><strong><?php echo e($this->resumo['modelo']); ?></strong></article>
            <article><span>Visualização padrão</span><strong><?php echo e($this->resumo['visualizacao']); ?></strong></article>
        </section>

        <section class="pz-ux-block soft">
            <div class="pz-ux-head">
                <div>
                    <span class="pz-ux-kicker">Primeiros passos</span>
                    <h2>Guia inicial para não se perder</h2>
                    <p>Ordem recomendada para implantar a operação com segurança, usando dados reais salvos na configuração da empresa.</p>
                </div>
                <div class="pz-ux-actions">
                    <a class="pz-ux-action primary" href="#checklist-onboarding">Abrir checklist</a>
                    <a class="pz-ux-action subtle" href="#templates-onboarding">Escolher modelo</a>
                </div>
            </div>

            <div class="pz-ux-grid four">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->guiaPrimeirosPassos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $passo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <article class="pz-ux-guide-card">
                        <span class="pz-ux-guide-icon"><?php echo e($passo['numero']); ?></span>
                        <div>
                            <strong><?php echo e($passo['titulo']); ?></strong>
                            <span><?php echo e($passo['descricao']); ?></span>
                        </div>
                    </article>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
        </section>

        <div wire:loading.delay class="pz-ux-block">
            <div class="pz-ux-loading is-visible">
                <span class="pz-ux-spinner"></span>
                <span>Processando alteração do onboarding...</span>
            </div>
            <div class="pz-ux-skeleton" style="margin-top: 12px;">
                <i></i><i></i><i></i>
            </div>
        </div>

        <section class="onboarding-grid two">
            <article id="checklist-onboarding" class="onboarding-card">
                <header>
                    <div>
                        <h2>Checklist inicial</h2>
                        <p>Essas etapas ficam salvas no banco e indicam o progresso da implantação.</p>
                    </div>
                </header>

                <div class="onboarding-steps">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->etapas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $etapa): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <button type="button" wire:loading.attr="disabled" wire:click="toggleEtapa('<?php echo e($etapa['codigo']); ?>')" class="step <?php echo e($etapa['feito'] ? 'done' : ''); ?>">
                            <b><?php echo e($etapa['feito'] ? '✓' : '•'); ?></b>
                            <span>
                                <strong><?php echo e($etapa['titulo']); ?></strong>
                                <small><?php echo e($etapa['descricao']); ?></small>
                            </span>
                        </button>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            </article>

            <article class="onboarding-card">
                <header>
                    <div>
                        <h2>Preferências de implantação</h2>
                        <p>Dados operacionais do onboarding, também persistidos em configuração.</p>
                    </div>
                </header>

                <div class="onboarding-form">
                    <label>
                        <span>Responsável pela implantação</span>
                        <input type="text" wire:model.defer="data.onboarding_preferencias.responsavel_implantacao" placeholder="Nome do responsável">
                    </label>

                    <label>
                        <span>Prazo alvo</span>
                        <input type="date" wire:model.defer="data.onboarding_preferencias.prazo_implantacao">
                    </label>

                    <label class="full">
                        <span>Observações</span>
                        <textarea wire:model.defer="data.onboarding_preferencias.observacoes" rows="4" placeholder="Ex: cliente inicia por RH, depois financeiro e documentos..."></textarea>
                    </label>

                    <div class="actions full">
                        <button type="button" wire:loading.attr="disabled" wire:target="salvarPreferencias" wire:click="salvarPreferencias">Salvar preferências</button>
                        <button type="button" wire:loading.attr="disabled" wire:target="finalizarOnboarding" wire:click="finalizarOnboarding" class="secondary" onclick="return confirm('Deseja marcar o onboarding como finalizado?')">Finalizar onboarding</button>
                    </div>
                </div>
            </article>
        </section>

        <section class="onboarding-card">
            <header>
                <div>
                    <h2>Recursos ativáveis</h2>
                    <p>Ao ativar/desativar, o recurso é gravado em <strong>onboarding_recursos</strong> e também sincronizado com <strong>modulos_ativos</strong>.</p>
                </div>
                <button type="button" wire:loading.attr="disabled" wire:click="habilitarRecursosBase" onclick="return confirm('Deseja habilitar todos os recursos base?')">Habilitar todos</button>
            </header>

            <div class="feature-grid">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->recursos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $recurso): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <button type="button" wire:loading.attr="disabled" wire:click="toggleRecurso('<?php echo e($recurso['codigo']); ?>')" class="feature <?php echo e($recurso['ativo'] ? 'active' : ''); ?>">
                        <strong><?php echo e($recurso['titulo']); ?></strong>
                        <span><?php echo e($recurso['descricao']); ?></span>
                        <em><?php echo e($recurso['ativo'] ? 'Ativo' : 'Inativo'); ?></em>
                    </button>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
        </section>

        <section id="templates-onboarding" class="onboarding-card">
            <header>
                <div>
                    <h2>Templates aplicáveis</h2>
                    <p>Aplicar um modelo grava workflow, campos personalizados, template e visualização padrão.</p>
                </div>
            </header>

            <div class="template-grid">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->modelos(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $modelo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <article>
                        <h3><?php echo e($modelo['titulo']); ?></h3>
                        <p><strong>Workflow:</strong> <?php echo e(implode(' → ', $modelo['workflow'])); ?></p>
                        <p><strong>Campos:</strong> <?php echo e(implode(', ', $modelo['campos'])); ?></p>
                        <button type="button" wire:loading.attr="disabled" onclick="return confirm('Aplicar este modelo vai atualizar workflow, campos e visualização padrão. Continuar?')" wire:click="aplicarModelo('<?php echo e($modelo['codigo']); ?>')">Aplicar modelo</button>
                    </article>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
        </section>

        <section class="pz-ux-block">
            <div class="pz-ux-head">
                <div>
                    <span class="pz-ux-kicker">Dicas nas páginas principais</span>
                    <h2>O que fazer primeiro em cada área</h2>
                    <p>Orientações curtas para reduzir dúvida operacional e manter a navegação limpa.</p>
                </div>
            </div>
            <div class="pz-ux-grid two">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->dicasPrincipais; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pagina => $dica): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <div class="pz-ux-tip"><b>?</b><div><strong><?php echo e($pagina); ?></strong><br><?php echo e($dica); ?></div></div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
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
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views\filament\pages\onboarding.blade.php ENDPATH**/ ?>