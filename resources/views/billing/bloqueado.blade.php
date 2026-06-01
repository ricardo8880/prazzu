<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Acesso bloqueado</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{{ asset('css/style-cadastro-empresa.css') }}">
</head>
<body>
<div class="cadastro-container">
    <div class="cadastro-card cadastro-card-status">
        <div class="cadastro-status-icon is-warning">!</div>

        <div class="cadastro-header cadastro-header-centered">
            <h1>Acesso bloqueado por pagamento</h1>
            <p>
                Encontramos uma pendência financeira vinculada à empresa. Regularize a cobrança para liberar novamente o acesso ao sistema.
            </p>
        </div>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif

        <div class="cadastro-status-panel">
            <div>
                <small>Empresa</small>
                <strong>{{ $empresa?->razao_social ?? $empresa?->nome_fantasia ?? '-' }}</strong>
            </div>
            <div>
                <small>Status da assinatura</small>
                <strong>{{ $assinatura?->status ?? 'Pendente' }}</strong>
            </div>
            <div>
                <small>Vencimento</small>
                <strong>{{ $pagamento?->vencimento?->format('d/m/Y') ?? '-' }}</strong>
            </div>
        </div>

        @if ($pagamento?->invoice_url && $empresa)
            <div class="alert alert-warning-soft">
                Abra a cobrança abaixo e conclua o pagamento. A liberação ocorrerá automaticamente após a confirmação do gateway.
            </div>

            <div class="form-actions form-actions-centered">
                <a href="{{ route('billing.pagar', $empresa) }}" class="btn-submit">Abrir cobrança</a>
                <a href="{{ url('/') }}" class="btn-voltar">Voltar ao início</a>
            </div>
        @else
            <div class="alert alert-error">
                Não foi encontrada uma cobrança aberta para regularização automática. Entre em contato com o suporte informando a empresa e o status acima.
            </div>
        @endif
    </div>
</div>
</body>
</html>
