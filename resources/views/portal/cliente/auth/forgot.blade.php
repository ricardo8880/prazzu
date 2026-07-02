<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Recuperar senha | Portal do Cliente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v=lote3">
</head>
<body class="portal-cliente-login portal-auth-public">
    <main class="portal-login-shell">
        <section class="portal-login-brand" aria-label="Recuperação de acesso">
            <div class="portal-login-logo" aria-hidden="true">🔐</div>
            <h1>Recuperar acesso</h1>
            <p>Informe o e-mail cadastrado no portal. Se o cadastro existir, enviaremos um link seguro para redefinir sua senha.</p>

            <div class="portal-login-highlights" aria-label="Segurança da recuperação">
                <div class="portal-login-highlight">
                    <strong>Link temporário</strong>
                    O acesso expira automaticamente para proteger sua conta.
                </div>
                <div class="portal-login-highlight">
                    <strong>Uso único</strong>
                    Depois de redefinir a senha, o link não funciona novamente.
                </div>
                <div class="portal-login-highlight">
                    <strong>Conta protegida</strong>
                    Nenhuma senha antiga é exibida ou enviada por e-mail.
                </div>
            </div>
        </section>

        <section class="portal-login-card" aria-label="Solicitar recuperação de senha">
            <header class="portal-login-card-header">
                <span>● Recuperação segura</span>
                <h2>Esqueci minha senha</h2>
                <p>Digite seu e-mail para receber o link de redefinição.</p>
            </header>

            <form class="portal-login-form" method="POST" action="{{ route('portal.cliente.forgot.store') }}" novalidate>
                @csrf
                <input type="text" name="website" value="" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px;opacity:0;pointer-events:none;" aria-hidden="true">

                @if ($errors->any())
                    <div class="portal-login-alert">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="portal-login-field">
                    <label for="email">E-mail cadastrado</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" inputmode="email" required autofocus placeholder="cliente@empresa.com.br">
                    @error('email')
                        <div class="portal-login-error">{{ $message }}</div>
                    @enderror
                </div>

                <button class="portal-login-button" type="submit">
                    Enviar link de recuperação
                    <span aria-hidden="true">→</span>
                </button>

                <div class="portal-login-footer">
                    Lembrou a senha? <a href="{{ route('portal.cliente.login') }}">Voltar para o login</a>
                </div>
            </form>
        </section>
    </main>
</body>
</html>
