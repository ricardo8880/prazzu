<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastro de Empresa</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="<?php echo e(asset('css/style-cadastro-empresa.css')); ?>">
</head>
<body>

<div class="cadastro-container">
    <div class="cadastro-card">

        <div class="cadastro-header">
            <h1>Cadastro de Empresa</h1>
            <p>
                Preencha os dados abaixo para criar sua empresa, escolher seu plano e começar a usar o sistema.
            </p>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('success')): ?>
            <div class="alert alert-success">
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
            <div class="alert alert-error">
                <strong>Verifique os campos abaixo:</strong>

                <ul>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <li><?php echo e($error); ?></li>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </ul>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <form method="POST" action="<?php echo e(route('empresa.cadastro.store')); ?>">
            <?php echo csrf_field(); ?>

            
            <div class="form-section">
                <h2>Dados da Empresa</h2>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Razão Social *</label>
                        <input
                            type="text"
                            name="razao_social"
                            value="<?php echo e(old('razao_social')); ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label>Nome Fantasia</label>
                        <input
                            type="text"
                            name="nome_fantasia"
                            value="<?php echo e(old('nome_fantasia')); ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label>CNPJ</label>
                        <input
                            type="text"
                            name="cnpj"
                            value="<?php echo e(old('cnpj')); ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label>E-mail da Empresa *</label>
                        <input
                            type="email"
                            name="email"
                            value="<?php echo e(old('email')); ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label>Telefone</label>
                        <input
                            type="text"
                            name="telefone"
                            value="<?php echo e(old('telefone')); ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label>Responsável Comercial *</label>
                        <input
                            type="text"
                            name="responsavel_nome"
                            value="<?php echo e(old('responsavel_nome')); ?>"
                            required
                        >
                    </div>
                </div>
            </div>



            
            <div class="form-section">
                <h2>Usuário Administrador</h2>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Nome do Admin *</label>
                        <input
                            type="text"
                            name="admin_nome"
                            value="<?php echo e(old('admin_nome')); ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label>E-mail do Admin *</label>
                        <input
                            type="email"
                            name="admin_email"
                            value="<?php echo e(old('admin_email')); ?>"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label>Senha *</label>
                        <input
                            type="password"
                            name="admin_password"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label>Confirmar Senha *</label>
                        <input
                            type="password"
                            name="admin_password_confirmation"
                            required
                        >
                    </div>
                </div>
            </div>



            
            <?php
                use App\Services\PlanoService;

                $planos = PlanoService::planos();
                $planoSelecionado = PlanoService::normalizarPlano(old('plano', request('plano', PlanoService::STARTER)));
            ?>
            <div class="form-section">
                <h2>Escolha seu Plano</h2>
                <p class="section-description">
                    Selecione o plano conforme o tamanho da operação. Os limites e recursos são os mesmos usados pelo sistema para liberar funcionalidades.
                </p>

                <div class="planos-grid">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $planos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $codigo => $plano): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php
                            $limiteUsuarios = (int) ($plano['limite_usuarios'] ?? 0);
                            $limiteItens = (int) ($plano['limite_itens'] ?? 0);
                            $usuariosTexto = $limiteUsuarios >= 999999 ? 'Usuários sob demanda' : 'Até ' . number_format($limiteUsuarios, 0, ',', '.') . ' usuários';
                            $itensTexto = $limiteItens >= 999999 ? 'Itens sob demanda' : 'Até ' . number_format($limiteItens, 0, ',', '.') . ' itens';
                        ?>

                        <label class="plano-card <?php echo e(! empty($plano['destaque']) ? 'plano-destaque' : ''); ?>">
                            <input
                                type="radio"
                                name="plano"
                                value="<?php echo e($codigo); ?>"
                                <?php echo e($planoSelecionado === $codigo ? 'checked' : ''); ?>

                            >

                            <strong><?php echo e($plano['nome_comercial'] ?? $plano['nome']); ?></strong>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($plano['tag'])): ?>
                                <small class="plano-tag-inline"><?php echo e($plano['tag']); ?></small>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            <div class="plano-preco">
                                <?php echo e($plano['preco'] ?? PlanoService::preco($codigo)); ?>

                            </div>

                            <small class="plano-descricao">
                                <?php echo e($plano['descricao'] ?? 'Plano para gestão operacional.'); ?>

                            </small>

                            <span><?php echo e($usuariosTexto); ?></span>
                            <span><?php echo e($itensTexto); ?></span>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if((int) ($plano['limite_interacoes_ia'] ?? 0) > 0): ?>
                                <span><?php echo e(number_format((int) ($plano['limite_interacoes_ia'] ?? 0), 0, ',', '.')); ?> interações de IA/mês</span>
                            <?php else: ?>
                                <span>IA disponível apenas em planos pagos selecionados</span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $plano['itens'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <span><?php echo e($item); ?></span>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </label>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            </div>



            
            <div class="form-actions">
                <a href="<?php echo e(url('/')); ?>" class="btn-voltar">
                    Voltar
                </a>

                <button type="submit" class="btn-submit">
                    Criar empresa e gerar cobrança
                </button>
            </div>

        </form>
    </div>
</div>

</body>
</html>
<?php /**PATH C:\xampp\htdocs\sistemrh\resources\views/public/cadastro-empresa.blade.php ENDPATH**/ ?>