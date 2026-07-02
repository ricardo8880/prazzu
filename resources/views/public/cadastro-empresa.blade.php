<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastro de Empresa</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v=lote3">
</head>
<body class="cadastro-empresa-public">

<div class="cadastro-container">
    <div class="cadastro-card">

        <div class="cadastro-header">
            <h1>Cadastro de Empresa</h1>
            <p>
                Preencha os dados abaixo para criar sua empresa, escolher seu plano e começar a usar o sistema.
            </p>
        </div>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-error">
                <strong>Verifique os campos abaixo:</strong>

                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('empresa.cadastro.store') }}">
            @csrf

            {{-- Dados da Empresa --}}
            <div class="form-section">
                <h2>Dados da Empresa</h2>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Razão Social *</label>
                        <input
                            type="text"
                            name="razao_social"
                            value="{{ old('razao_social') }}"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label>Nome Fantasia</label>
                        <input
                            type="text"
                            name="nome_fantasia"
                            value="{{ old('nome_fantasia') }}"
                        >
                    </div>

                    <div class="form-group">
                        <label>CNPJ</label>
                        <input
                            type="text"
                            name="cnpj"
                            value="{{ old('cnpj') }}"
                        >
                    </div>

                    <div class="form-group">
                        <label>E-mail da Empresa *</label>
                        <input
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label>Telefone</label>
                        <input
                            type="text"
                            name="telefone"
                            value="{{ old('telefone') }}"
                        >
                    </div>

                    <div class="form-group">
                        <label>Responsável Comercial *</label>
                        <input
                            type="text"
                            name="responsavel_nome"
                            value="{{ old('responsavel_nome') }}"
                            required
                        >
                    </div>
                </div>
            </div>



            {{-- Usuário Administrador --}}
            <div class="form-section">
                <h2>Usuário Administrador</h2>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Nome do Admin *</label>
                        <input
                            type="text"
                            name="admin_nome"
                            value="{{ old('admin_nome') }}"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label>E-mail do Admin *</label>
                        <input
                            type="email"
                            name="admin_email"
                            value="{{ old('admin_email') }}"
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



            {{-- Escolha do Plano --}}
            @php
                use App\Services\PlanoService;

                $planos = PlanoService::planos();
                $planoSelecionado = PlanoService::normalizarPlano(old('plano', request('plano', PlanoService::STARTER)));
            @endphp
            <div class="form-section">
                <h2>Escolha seu Plano</h2>
                <p class="section-description">
                    Selecione o plano conforme o tamanho da operação. Os limites e recursos são os mesmos usados pelo sistema para liberar funcionalidades.
                </p>

                <div class="planos-grid">
                    @foreach($planos as $codigo => $plano)
                        @php
                            $limiteUsuarios = (int) ($plano['limite_usuarios'] ?? 0);
                            $limiteItens = (int) ($plano['limite_itens'] ?? 0);
                            $usuariosTexto = $limiteUsuarios >= 999999 ? 'Usuários sob demanda' : 'Até ' . number_format($limiteUsuarios, 0, ',', '.') . ' usuários';
                            $itensTexto = $limiteItens >= 999999 ? 'Itens sob demanda' : 'Até ' . number_format($limiteItens, 0, ',', '.') . ' itens';
                        @endphp

                        <label class="plano-card {{ ! empty($plano['destaque']) ? 'plano-destaque' : '' }}">
                            <input
                                type="radio"
                                name="plano"
                                value="{{ $codigo }}"
                                {{ $planoSelecionado === $codigo ? 'checked' : '' }}
                            >

                            <strong>{{ $plano['nome_comercial'] ?? $plano['nome'] }}</strong>

                            @if(! empty($plano['tag']))
                                <small class="plano-tag-inline">{{ $plano['tag'] }}</small>
                            @endif

                            <div class="plano-preco">
                                {{ $plano['preco'] ?? PlanoService::preco($codigo) }}
                            </div>

                            <small class="plano-descricao">
                                {{ $plano['descricao'] ?? 'Plano para gestão operacional.' }}
                            </small>

                            <span>{{ $usuariosTexto }}</span>
                            <span>{{ $itensTexto }}</span>
                            @if((int) ($plano['limite_interacoes_ia'] ?? 0) > 0)
                                <span>{{ number_format((int) ($plano['limite_interacoes_ia'] ?? 0), 0, ',', '.') }} interações de IA/mês</span>
                            @else
                                <span>IA disponível apenas em planos pagos selecionados</span>
                            @endif

                            @foreach($plano['itens'] ?? [] as $item)
                                <span>{{ $item }}</span>
                            @endforeach
                        </label>
                    @endforeach
                </div>
            </div>



            {{-- Botões --}}
            <div class="form-actions">
                <a href="{{ url('/') }}" class="btn-voltar">
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
