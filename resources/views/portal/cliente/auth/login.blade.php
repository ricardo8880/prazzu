<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Portal do Cliente | Login</title>
    <link rel="stylesheet" href="{{ asset('css/prazzu-global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/prazzu-theme.css') }}">

</head>
<body class="portal-cliente-login">
    <main class="portal-login-shell">
        <section class="portal-login-brand" aria-label="Apresentação do portal">
            <div class="portal-login-logo" aria-hidden="true">💬</div>
            <h1>Portal do Cliente</h1>
            <p>Acesse sua área segura para abrir atendimentos, acompanhar conversas com a equipe, responder pendências e consultar o andamento das suas solicitações.</p>

            <div class="portal-login-highlights" aria-label="Recursos do portal">
                <div class="portal-login-highlight">
                    <strong>Atendimento centralizado</strong>
                    Converse com o suporte em um único lugar.
                </div>
                <div class="portal-login-highlight">
                    <strong>Acompanhamento claro</strong>
                    Veja status, protocolo e próximas ações.
                </div>
                <div class="portal-login-highlight">
                    <strong>Acesso protegido</strong>
                    Login individual com sessão segura.
                </div>
            </div>
        </section>

        <section class="portal-login-card" aria-label="Login do cliente">
            <header class="portal-login-card-header">
                <span>● Ambiente seguro</span>
                <h2>Entrar no portal</h2>
                <p>Informe o e-mail e a senha cadastrados para acessar seus atendimentos.</p>
            </header>

            <form class="portal-login-form" method="POST" action="{{ route('portal.cliente.login.store') }}" novalidate>
                @csrf
                <input type="text" name="website" value="" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px;opacity:0;pointer-events:none;" aria-hidden="true">

                @if (session('success'))
                    <div class="portal-login-success">{{ session('success') }}</div>
                @endif

                @if ($errors->any())
                    <div class="portal-login-alert">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="portal-login-field">
                    <label for="email">E-mail</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" inputmode="email" required autofocus placeholder="cliente@empresa.com.br">
                    @error('email')
                        <div class="portal-login-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="portal-login-field">
                    <label for="password">Senha</label>
                    <input id="password" type="password" name="password" autocomplete="current-password" required placeholder="Digite sua senha">
                    @error('password')
                        <div class="portal-login-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="portal-login-options">
                    <label>
                        <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                        Manter conectado
                    </label>
                    <a class="portal-login-inline-link" href="{{ route('portal.cliente.forgot') }}">Esqueci minha senha</a>
                </div>

                <button class="portal-login-button" type="submit">
                    Entrar no Portal
                    <span aria-hidden="true">→</span>
                </button>

                <div class="portal-login-footer">
                    Acesso exclusivo para clientes cadastrados. <strong>Não compartilhe sua senha.</strong>
                </div>
            </form>
        </section>
    </main>
</body>
</html>
