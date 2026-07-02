<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Portal do Cliente | Cadastro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v=lote3">
</head>
<body class="portal-cliente-login portal-auth-public">
    <main class="portal-login-shell">
        <section class="portal-login-brand" aria-label="Apresentação do portal">
            <div class="portal-login-logo" aria-hidden="true">💬</div>
            <h1>Área do Cliente</h1>
            <p>Crie seu acesso seguro para conversar com o suporte, abrir atendimentos e acompanhar suas solicitações.</p>

            <div class="portal-login-highlights" aria-label="Recursos do portal">
                <div class="portal-login-highlight">
                    <strong>Empresa vinculada</strong>
                    Seu cadastro será conectado automaticamente à empresa {{ $empresa->nome_fantasia ?: $empresa->razao_social }}.
                </div>
                <div class="portal-login-highlight">
                    <strong>Atendimento direto</strong>
                    Depois do cadastro você poderá falar com o suporte pelo portal.
                </div>
                <div class="portal-login-highlight">
                    <strong>Acesso protegido</strong>
                    Use uma senha individual e não compartilhe seu login.
                </div>
            </div>
        </section>

        <section class="portal-login-card" aria-label="Cadastro do cliente">
            <header class="portal-login-card-header">
                <span>● Convite de cadastro</span>
                <h2>Criar acesso</h2>
                <p>Preencha seus dados para entrar no Portal do Cliente.</p>
            </header>

            <form class="portal-login-form" method="POST" action="{{ route('portal.cliente.cadastro.store', ['token' => $token]) }}" novalidate>
                @csrf
                <input type="text" name="website" value="" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px;opacity:0;pointer-events:none;" aria-hidden="true">

                @if ($errors->any())
                    <div class="portal-login-alert">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="portal-login-field">
                    <label for="nome">Nome completo</label>
                    <input id="nome" type="text" name="nome" value="{{ old('nome') }}" autocomplete="name" required autofocus placeholder="Seu nome">
                    @error('nome')
                        <div class="portal-login-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="portal-login-field">
                    <label for="email">E-mail</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" inputmode="email" required placeholder="cliente@empresa.com.br">
                    @error('email')
                        <div class="portal-login-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="portal-login-field">
                    <label for="telefone">Telefone/WhatsApp</label>
                    <input id="telefone" type="text" name="telefone" value="{{ old('telefone') }}" autocomplete="tel" placeholder="(11) 90000-0000">
                    @error('telefone')
                        <div class="portal-login-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="portal-login-field">
                    <label for="password">Senha</label>
                    <input id="password" type="password" name="password" autocomplete="new-password" required placeholder="Mínimo de 8 caracteres">
                    @error('password')
                        <div class="portal-login-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="portal-login-field">
                    <label for="password_confirmation">Confirmar senha</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" required placeholder="Digite a senha novamente">
                </div>

                <button class="portal-login-button" type="submit">
                    Criar acesso e entrar
                    <span aria-hidden="true">→</span>
                </button>

                <div class="portal-login-footer">
                    Já tem cadastro? <a class="portal-login-inline-link" href="{{ route('portal.cliente.login') }}">Entrar no portal</a>
                </div>
            </form>
        </section>
    </main>
</body>
</html>
