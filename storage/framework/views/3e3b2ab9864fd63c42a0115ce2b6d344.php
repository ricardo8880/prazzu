<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Ativar acesso | Portal do Cliente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo e(asset('css/style.css')); ?>?v=lote3">
</head>
<body class="portal-cliente-login portal-auth-public">
    <main class="portal-login-shell">
        <section class="portal-login-brand" aria-label="Ativação de convite">
            <div class="portal-login-logo" aria-hidden="true">👋</div>
            <h1>Bem-vindo ao Portal</h1>
            <p>Seu convite foi localizado. Cadastre uma senha para ativar o acesso e acompanhar os atendimentos da sua empresa.</p>

            <div class="portal-login-highlights" aria-label="Recursos liberados após o convite">
                <div class="portal-login-highlight">
                    <strong>Abrir atendimentos</strong>
                    Solicite suporte sem depender de links avulsos.
                </div>
                <div class="portal-login-highlight">
                    <strong>Acompanhar histórico</strong>
                    Consulte mensagens, status e protocolos.
                </div>
                <div class="portal-login-highlight">
                    <strong>Enviar anexos</strong>
                    Compartilhe documentos diretamente na conversa.
                </div>
            </div>
        </section>

        <section class="portal-login-card" aria-label="Ativar convite do cliente">
            <header class="portal-login-card-header">
                <span>● Convite válido</span>
                <h2>Ativar acesso</h2>
                <p><?php echo e($cliente?->nome ? 'Olá, ' . $cliente->nome . '. ' : ''); ?>Crie sua senha para entrar no portal.</p>
            </header>

            <form class="portal-login-form" method="POST" action="<?php echo e(route('portal.cliente.convite.aceitar', ['token' => $token])); ?>" novalidate>
                <?php echo csrf_field(); ?>
                <input type="text" name="website" value="" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px;opacity:0;pointer-events:none;" aria-hidden="true">
                <input type="hidden" name="email" value="<?php echo e($email); ?>">

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
                    <div class="portal-login-alert">
                        <?php echo e($errors->first()); ?>

                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <div class="portal-login-field">
                    <label for="email_visual">E-mail do convite</label>
                    <input id="email_visual" type="email" value="<?php echo e($email); ?>" disabled>
                </div>

                <div class="portal-login-field">
                    <label for="password">Senha de acesso</label>
                    <input id="password" type="password" name="password" autocomplete="new-password" required autofocus placeholder="Digite uma senha segura">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                        <div class="portal-login-error"><?php echo e($message); ?></div>
                    <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div class="portal-login-field">
                    <label for="password_confirmation">Confirmar senha</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" required placeholder="Repita a senha">
                </div>

                <button class="portal-login-button" type="submit">
                    Ativar meu acesso
                    <span aria-hidden="true">→</span>
                </button>

                <div class="portal-login-footer">
                    Já ativou o acesso? <a href="<?php echo e(route('portal.cliente.login')); ?>">Voltar para o login</a>
                </div>
            </form>
        </section>
    </main>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views\portal\cliente\auth\convite.blade.php ENDPATH**/ ?>