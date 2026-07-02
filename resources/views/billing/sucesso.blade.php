<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastro recebido</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v=20260702-css-migration-lote10">
</head>
<body>
<div class="cadastro-container">
    <div class="cadastro-card cadastro-card-status">
        <div class="cadastro-status-icon is-success">✓</div>

        <div class="cadastro-header cadastro-header-centered">
            <h1>Cadastro recebido</h1>
            <p>
                Sua empresa foi criada com acesso pendente. Assim que o pagamento for confirmado pelo Asaas,
                o sistema libera automaticamente o ambiente administrativo.
            </p>
        </div>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="cadastro-next-steps">
            <div>
                <strong>1</strong>
                <span>Finalize o pagamento pela cobrança gerada.</span>
            </div>
            <div>
                <strong>2</strong>
                <span>Aguarde a confirmação automática do Asaas.</span>
            </div>
            <div>
                <strong>3</strong>
                <span>Depois disso, acesse o login com o usuário administrador cadastrado.</span>
            </div>
        </div>

        <div class="form-actions form-actions-centered">
            <a href="{{ route('filament.admin.auth.login') }}" class="btn-submit">Ir para o login</a>
            <a href="{{ route('planos') }}" class="btn-voltar">Ver planos</a>
        </div>
    </div>
</div>
</body>
</html>
