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


    <div class="prazzu-admin-page lote4-cadastro-page">
        <section class="prazzu-hero lote4-cadastro-hero">
            <div>
                <span><i class="bi bi-building-check"></i> DADOS DO ESCRITÓRIO</span>
                <h1>Cadastro institucional do escritório</h1>
                <p>Mantenha dados cadastrais, contato, responsável, plano e status em um lugar único. Fluxos operacionais continuam nas abas de Operação, Pendências, Documentos e Aprovações.</p>
            </div>
        </section>

        <section class="configuracoes-diretriz">
            <article>
                <i class="bi bi-person-vcard"></i>
                <div>
                    <strong>Propósito desta aba</strong>
                    <p>Guardar a identidade administrativa do escritório usada em documentos, conta, comunicação e permissões.</p>
                </div>
            </article>
            <article>
                <i class="bi bi-diagram-3"></i>
                <div>
                    <strong>Sem mistura operacional</strong>
                    <p>Demandas, pendências, aprovações e vencimentos não devem ser tratados aqui; esta é uma tela de cadastro.</p>
                </div>
            </article>
            <article>
                <i class="bi bi-shield-check"></i>
                <div>
                    <strong>Dados confiáveis</strong>
                    <p>Campos claros e revisáveis reduzem erro cadastral e melhoram a experiência do usuário administrador.</p>
                </div>
            </article>
        </section>

        <section class="prazzu-grid four">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $this->resumo(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <article class="prazzu-card prazzu-stat lote4-stat-card">
                    <span><?php echo e($stat['label']); ?></span>
                    <strong><?php echo e($stat['value']); ?></strong>
                    <small class="prazzu-muted"><?php echo e($stat['hint']); ?></small>
                </article>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                <article class="prazzu-card"><strong>Nenhuma empresa disponível.</strong></article>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </section>

        <section class="prazzu-grid two">
            <article class="prazzu-card lote4-form-card">
                <div class="configuracoes-section-title compact">
                    <i class="bi bi-pencil-square"></i>
                    <div>
                        <h2>Dados principais</h2>
                        <p>Edite apenas informações cadastrais e administrativas do escritório.</p>
                    </div>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()?->isSuperAdmin()): ?>
                    <label class="prazzu-field" style="display:block;margin-top:14px">
                        <span>Empresa administrada</span>
                        <select class="prazzu-select" wire:model.live="empresaId">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->empresasDisponiveis(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $id => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <option value="<?php echo e($id); ?>"><?php echo e($label); ?></option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </select>
                    </label>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <div class="prazzu-form">
                    <label class="prazzu-field"><span>Razão social</span><input class="prazzu-input" wire:model.defer="razao_social"></label>
                    <label class="prazzu-field"><span>Nome fantasia</span><input class="prazzu-input" wire:model.defer="nome_fantasia"></label>
                    <label class="prazzu-field"><span>CNPJ</span><input class="prazzu-input" wire:model.defer="cnpj"></label>
                    <label class="prazzu-field"><span>E-mail institucional</span><input class="prazzu-input" type="email" wire:model.defer="email"></label>
                    <label class="prazzu-field"><span>Telefone / WhatsApp</span><input class="prazzu-input" wire:model.defer="telefone"></label>
                    <label class="prazzu-field"><span>Responsável administrativo</span><input class="prazzu-input" wire:model.defer="responsavel_nome"></label>
                    <label class="prazzu-field"><span>Status da conta</span><input class="prazzu-input" wire:model.defer="status"></label>
                    <label class="prazzu-field"><span>Plano contratado</span><input class="prazzu-input" wire:model.defer="plano"></label>
                </div>

                <div class="prazzu-actions">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->podeEditar()): ?>
                        <button class="prazzu-button" type="button" wire:click="salvarEmpresa"><i class="bi bi-check2-circle"></i> Salvar empresa</button>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->resourceUrl()): ?>
                        <a class="prazzu-link light" href="<?php echo e($this->resourceUrl()); ?>"><i class="bi bi-box-arrow-up-right"></i> Abrir cadastro completo</a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </article>

            <article class="prazzu-card lote4-info-card">
                <div class="configuracoes-section-title compact">
                    <i class="bi bi-info-circle"></i>
                    <div>
                        <h2>Mapa de responsabilidade</h2>
                        <p>Use esta orientação para manter cada conteúdo na aba certa.</p>
                    </div>
                </div>
                <div class="prazzu-info">
                    <div><span>Identidade</span><strong>Razão social, fantasia e CNPJ ficam aqui</strong></div>
                    <div><span>Contato</span><strong>E-mail, telefone e responsável ficam aqui</strong></div>
                    <div><span>Conta</span><strong>Status, plano e dados administrativos ficam aqui</strong></div>
                    <div><span>Operação</span><strong>Pendências, documentos e aprovações ficam nas abas operacionais</strong></div>
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
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views\filament\pages\empresa-administrativa.blade.php ENDPATH**/ ?>