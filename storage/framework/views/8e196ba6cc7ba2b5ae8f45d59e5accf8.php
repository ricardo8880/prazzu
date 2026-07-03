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
        $planosComerciais = \App\Services\PlanoService::planos();
        $cardsServicos = [
            'Documentos e solicitações',
            'Itens de controle',
            'Checklist por item',
            'Comentários internos',
            'Timeline e histórico',
            'Anexos centralizados',
            'Kanban e calendário',
            'Portal do cliente',
            'Aprovações internas',
            'Controle de contratos',
            'SLA e alertas',
            'Relatórios e exportações',
            'Auditoria',
            'BI e produtividade',
            'Fluxos operacionais',
            'White label',
        ];
    ?>

    <div style="max-width: 1440px; margin: 0 auto; padding: 24px; color: #111827;">
        <div style="text-align: center; margin-bottom: 48px;">
            <span style="display: inline-flex; align-items: center; padding: 8px 14px; border-radius: 999px; background: #ecfdf5; color: #047857; font-size: 13px; font-weight: 800; letter-spacing: .02em;">
                Planos para contabilidades
            </span>

            <h1 style="margin: 18px auto 0; max-width: 980px; font-size: clamp(32px, 5vw, 56px); line-height: 1.05; font-weight: 900;">
                Escolha um plano que acompanha o crescimento da sua operação contábil.
            </h1>

            <p style="margin: 18px auto 0; max-width: 860px; color: #6b7280; font-size: 18px; line-height: 1.7;">
                Centralize documentos, aprovações, anexos, prazos, relatórios e atendimento ao cliente em um único lugar.
                IA e Clicksign ficam fora do gratuito para evitar custo invisível, mas podem ser contratados nos planos pagos.
            </p>
        </div>

        <div style="margin-bottom: 42px; padding: 28px; border-radius: 28px; background: linear-gradient(135deg, #f8fafc, #ecfdf5); border: 1px solid #d1fae5;">
            <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 24px; flex-wrap: wrap;">
                <div style="max-width: 760px;">
                    <h2 style="font-size: 28px; font-weight: 900; margin: 0;">Tudo que sua contabilidade consegue organizar com a Prazzu</h2>
                    <p style="margin: 10px 0 0; color: #4b5563; line-height: 1.7;">
                        Os cards abaixo não mostram só limites. Eles mostram o ecossistema do produto: operação, documentos, equipe, cliente e gestão.
                    </p>
                </div>

                <div style="padding: 14px 18px; border-radius: 18px; background: white; border: 1px solid #d1d5db; color: #374151; font-weight: 700;">
                    Sem fidelidade obrigatória
                </div>
            </div>

            <div style="margin-top: 26px; display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 12px;">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $cardsServicos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $servico): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <div style="background: rgba(255,255,255,.86); border: 1px solid #e5e7eb; border-radius: 16px; padding: 14px 16px; font-weight: 700; color: #374151;">
                        ✓ <?php echo e($servico); ?>

                    </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 22px; align-items: stretch;">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $planosComerciais; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $codigoPlano => $planoComercial): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <?php
                    $isDestaque = ! empty($planoComercial['destaque']);
                    $isEnterprise = $codigoPlano === \App\Services\PlanoService::ENTERPRISE;
                    $configuraveis = $planoComercial['configuraveis'] ?? [];
                    $servicos = $planoComercial['servicos'] ?? [];
                    $naoIncluso = $planoComercial['nao_incluso'] ?? [];
                ?>

                <div style="position: relative; display: flex; flex-direction: column; background: <?php echo e($isDestaque ? '#ffffff' : ($isEnterprise ? '#111827' : '#ffffff')); ?>; color: <?php echo e($isEnterprise ? '#ffffff' : '#111827'); ?>; border: <?php echo e($isDestaque ? '2px solid #10b981' : '1px solid #e5e7eb'); ?>; border-radius: 28px; padding: 28px; box-shadow: <?php echo e($isDestaque ? '0 24px 60px rgba(16, 185, 129, .18)' : '0 14px 34px rgba(15, 23, 42, .06)'); ?>;">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($planoComercial['tag'])): ?>
                        <div style="display: inline-flex; align-self: flex-start; padding: 7px 12px; border-radius: 999px; background: <?php echo e($isDestaque ? '#10b981' : ($isEnterprise ? '#374151' : '#f3f4f6')); ?>; color: <?php echo e($isDestaque || $isEnterprise ? '#ffffff' : '#374151'); ?>; font-size: 12px; font-weight: 900; text-transform: uppercase; letter-spacing: .04em;">
                            <?php echo e($planoComercial['tag']); ?>

                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <h2 style="margin: 18px 0 0; font-size: 30px; font-weight: 900;"><?php echo e($planoComercial['nome']); ?></h2>

                    <div style="margin-top: 12px; display: flex; align-items: baseline; gap: 6px;">
                        <strong style="font-size: 42px; line-height: 1; font-weight: 950;"><?php echo e($planoComercial['preco']); ?></strong>
                    </div>

                    <p style="margin: 16px 0 0; color: <?php echo e($isEnterprise ? '#d1d5db' : '#6b7280'); ?>; line-height: 1.65; min-height: 78px;">
                        <?php echo e($planoComercial['descricao']); ?>

                    </p>

                    <div style="margin-top: 22px; padding-top: 22px; border-top: 1px solid <?php echo e($isEnterprise ? '#374151' : '#e5e7eb'); ?>;">
                        <h3 style="margin: 0 0 14px; font-size: 15px; font-weight: 900; text-transform: uppercase; letter-spacing: .05em; color: <?php echo e($isEnterprise ? '#d1d5db' : '#374151'); ?>;">
                            O que está incluso
                        </h3>

                        <div style="display: flex; flex-direction: column; gap: 11px;">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $planoComercial['itens'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $itemPlano): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <div style="display: flex; gap: 9px; align-items: flex-start; color: <?php echo e($isEnterprise ? '#f9fafb' : '#374151'); ?>; line-height: 1.45;">
                                    <span style="color: #10b981; font-weight: 900;">✓</span>
                                    <span><?php echo e($itemPlano); ?></span>
                                </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    </div>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($servicos)): ?>
                        <div style="margin-top: 22px; padding: 16px; border-radius: 18px; background: <?php echo e($isEnterprise ? '#1f2937' : '#f9fafb'); ?>; border: 1px solid <?php echo e($isEnterprise ? '#374151' : '#e5e7eb'); ?>;">
                            <strong style="display: block; margin-bottom: 10px; font-size: 14px;">Serviços do plano</strong>
                            <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $servicos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $servico): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <span style="display: inline-flex; padding: 6px 9px; border-radius: 999px; background: <?php echo e($isEnterprise ? '#111827' : '#ffffff'); ?>; border: 1px solid <?php echo e($isEnterprise ? '#4b5563' : '#e5e7eb'); ?>; font-size: 12px; font-weight: 700; color: <?php echo e($isEnterprise ? '#e5e7eb' : '#4b5563'); ?>;">
                                        <?php echo e($servico); ?>

                                    </span>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($configuraveis)): ?>
                        <div style="margin-top: 22px; display: flex; flex-direction: column; gap: 14px;">
                            <h3 style="margin: 0; font-size: 15px; font-weight: 900; text-transform: uppercase; letter-spacing: .05em; color: <?php echo e($isEnterprise ? '#d1d5db' : '#374151'); ?>;">
                                Ajuste conforme sua necessidade
                            </h3>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $configuraveis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $nomeConfiguravel => $opcoes): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <label style="display: block;">
                                    <span style="display: block; margin-bottom: 7px; font-size: 13px; font-weight: 800; text-transform: capitalize; color: <?php echo e($isEnterprise ? '#e5e7eb' : '#4b5563'); ?>;">
                                        <?php echo e(str_replace('_', ' ', $nomeConfiguravel)); ?>

                                    </span>
                                    <select style="width: 100%; padding: 12px 13px; border-radius: 14px; border: 1px solid <?php echo e($isEnterprise ? '#4b5563' : '#d1d5db'); ?>; background: <?php echo e($isEnterprise ? '#111827' : '#ffffff'); ?>; color: <?php echo e($isEnterprise ? '#ffffff' : '#111827'); ?>; font-weight: 700;">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $opcoes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $opcao): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                            <option>
                                                <?php echo e($opcao['label']); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($opcao['valor']) && $opcao['valor'] !== null && $opcao['valor'] > 0): ?> — +R$ <?php echo e(number_format($opcao['valor'], 0, ',', '.')); ?>/mês <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </option>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    </select>
                                </label>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($naoIncluso)): ?>
                        <div style="margin-top: 22px; padding: 14px; border-radius: 16px; background: #fff7ed; border: 1px solid #fed7aa; color: #9a3412; font-size: 13px; line-height: 1.55;">
                            <strong>Não incluso no Free:</strong> <?php echo e(implode(', ', $naoIncluso)); ?>.
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <a href="<?php echo e($isEnterprise ? route('login') : route('empresa.cadastro.create', ['plano' => $codigoPlano])); ?>"
                       style="margin-top: auto; display: block; text-align: center; padding: 15px 18px; border-radius: 16px; background: <?php echo e($isDestaque ? '#10b981' : ($isEnterprise ? '#ffffff' : '#111827')); ?>; color: <?php echo e($isEnterprise ? '#111827' : '#ffffff'); ?>; font-weight: 900; text-decoration: none;">
                        <?php echo e($isEnterprise ? 'Falar com especialista' : 'Começar neste plano'); ?>

                    </a>
                </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </div>

        <div style="margin-top: 34px; text-align: center; color: #6b7280; line-height: 1.7;">
            <p style="margin: 0;">IA e Clicksign não entram no plano gratuito porque geram custo operacional real. Nos planos pagos, eles aparecem como adicionais para o cliente contratar apenas quando precisar.</p>
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
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views/planos.blade.php ENDPATH**/ ?>