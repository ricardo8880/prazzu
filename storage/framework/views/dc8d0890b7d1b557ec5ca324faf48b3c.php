<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Nova senha | Portal do Cliente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo e(asset('css/style.css')); ?>?v=lote3">
</head>
<body class="portal-cliente-login portal-auth-public">
    <main class="portal-login-shell">
        <section class="portal-login-brand" aria-label="Cadastrar nova senha">
            <div class="portal-login-logo" aria-hidden="true">✅</div>
            <h1>Cadastre uma nova senha</h1>
            <p>Use uma senha forte para proteger suas conversas, anexos e atendimentos no Portal do Cliente.</p>

            <div class="portal-login-highlights" aria-label="Requisitos de senha">
                <div class="portal-login-highlight">
                    <strong>Mínimo 8 caracteres</strong>
                    Evite senhas óbvias ou já usadas em outros sistemas.
                </div>
                <div class="portal-login-highlight">
                    <strong>Letras e números</strong>
                    Misture maiúsculas, minúsculas e números.
                </div>
                <div class="portal-login-highlight">
                    <strong>Confirmação obrigatória</strong>
                    Digite a senha duas vezes para evitar erro de digitação.
                </div>
            </div>
        </section>

        <section class="portal-login-card" aria-label="Redefinir senha">
            <header class="portal-login-card-header">
                <span>● Link validado</span>
                <h2>Definir nova senha</h2>
                <p>Após salvar, você poderá entrar no portal com a nova senha.</p>
            </header>

            <form class="portal-login-form" method="POST" action="<?php echo e(route('portal.cliente.password.update', ['token' => $token])); ?>" novalidate>
                <?php echo csrf_field(); ?>
                <input type="text" name="website" value="" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px;opacity:0;pointer-events:none;" aria-hidden="true">
                <input type="hidden" name="email" value="<?php echo e($email); ?>">

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($errors->any()): ?>
                    <div class="portal-login-alert">
                        <?php echo e($errors->first()); ?>

                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <div class="portal-login-field">
                    <label for="email_visual">E-mail</label>
                    <input id="email_visual" type="email" value="<?php echo e($email); ?>" disabled>
                </div>

                <div class="portal-login-field">
                    <label for="password">Nova senha</label>
                    <input id="password" type="password" name="password" autocomplete="new-password" required autofocus placeholder="Digite a nova senha">
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
                    <label for="password_confirmation">Confirmar nova senha</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" required placeholder="Repita a nova senha">
                </div>

                <button class="portal-login-button" type="submit">
                    Salvar nova senha
                    <span aria-hidden="true">→</span>
                </button>

                <div class="portal-login-footer">
                    Link expirado? <a href="<?php echo e(route('portal.cliente.forgot')); ?>">Solicitar outro link</a>
                </div>
            </form>
        </section>
    </main>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views\portal\cliente\auth\reset.blade.php ENDPATH**/ ?>