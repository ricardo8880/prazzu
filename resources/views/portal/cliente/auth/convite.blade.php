<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ativar acesso | Portal do Cliente</title>
    <link rel="stylesheet" href="{{ asset('css/prazzu-global.css') }}">
    <link rel="stylesheet" href="{{ asset('css/prazzu-theme.css') }}">
</head>
<body class="portal-cliente-login">
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
                <p>{{ $cliente?->nome ? 'Olá, ' . $cliente->nome . '. ' : '' }}Crie sua senha para entrar no portal.</p>
            </header>

            <form class="portal-login-form" method="POST" action="{{ route('portal.cliente.convite.aceitar', ['token' => $token]) }}" novalidate>
                @csrf
                <input type="text" name="website" value="" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px;opacity:0;pointer-events:none;" aria-hidden="true">
                <input type="hidden" name="email" value="{{ $email }}">

                @if ($errors->any())
                    <div class="portal-login-alert">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div class="portal-login-field">
                    <label for="email_visual">E-mail do convite</label>
                    <input id="email_visual" type="email" value="{{ $email }}" disabled>
                </div>

                <div class="portal-login-field">
                    <label for="password">Senha de acesso</label>
                    <input id="password" type="password" name="password" autocomplete="new-password" required autofocus placeholder="Digite uma senha segura">
                    @error('password')
                        <div class="portal-login-error">{{ $message }}</div>
                    @enderror
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
                    Já ativou o acesso? <a href="{{ route('portal.cliente.login') }}">Voltar para o login</a>
                </div>
            </form>
        </section>
    </main>
</body>
</html>
