@php
    $whiteLabel = \App\Support\WhiteLabelSettings::make();
    $brandName = $whiteLabel->displayName();
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $brandName }} | Gestão de prazos, documentos e controles</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800" rel="stylesheet" />

    <style>
        :root {
            --bg: #f8fafc;
            --card: #ffffff;
            --text: #0f172a;
            --muted: #64748b;
            --primary: #f59e0b;
            --primary-dark: #d97706;
            --secondary: #1e293b;
            --success: #16a34a;
            --border: #e2e8f0;
            --shadow: 0 24px 70px rgba(15, 23, 42, .12);
            --radius: 28px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            min-height: 100vh;
            font-family: 'Instrument Sans', Arial, sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at top left, rgba(245, 158, 11, .20), transparent 34%),
                radial-gradient(circle at top right, rgba(30, 41, 59, .12), transparent 30%),
                var(--bg);
            line-height: 1.6;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        .container {
            width: min(1180px, calc(100% - 32px));
            margin: 0 auto;
        }

        .topbar {
            position: sticky;
            top: 0;
            z-index: 20;
            backdrop-filter: blur(18px);
            background: rgba(248, 250, 252, .86);
            border-bottom: 1px solid rgba(226, 232, 240, .8);
        }

        .nav {
            min-height: 78px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 800;
            font-size: 22px;
            letter-spacing: -.04em;
        }

        .brand-mark {
            width: 44px;
            height: 44px;
            display: grid;
            place-items: center;
            border-radius: 16px;
            background: linear-gradient(135deg, var(--primary), #fbbf24);
            color: white;
            box-shadow: 0 16px 30px rgba(245, 158, 11, .28);
            font-weight: 900;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 24px;
            color: #475569;
            font-weight: 700;
            font-size: 14px;
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 46px;
            padding: 0 20px;
            border-radius: 999px;
            font-weight: 800;
            border: 1px solid transparent;
            transition: .2s ease;
            cursor: pointer;
            white-space: nowrap;
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .btn-primary {
            color: #111827;
            background: linear-gradient(135deg, #fbbf24, var(--primary));
            box-shadow: 0 16px 30px rgba(245, 158, 11, .25);
        }

        .btn-dark {
            color: #ffffff;
            background: var(--secondary);
            box-shadow: 0 16px 30px rgba(15, 23, 42, .22);
        }

        .btn-light {
            color: var(--secondary);
            background: #ffffff;
            border-color: var(--border);
        }

        .hero {
            padding: 76px 0 48px;
        }

        .hero-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.08fr) minmax(340px, .92fr);
            gap: 48px;
            align-items: center;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 18px;
            padding: 8px 14px;
            border-radius: 999px;
            background: rgba(245, 158, 11, .14);
            color: #92400e;
            font-weight: 800;
            font-size: 13px;
        }

        .hero h1 {
            max-width: 760px;
            font-size: clamp(42px, 6vw, 72px);
            line-height: .98;
            letter-spacing: -.07em;
            margin-bottom: 24px;
        }

        .hero h1 span {
            color: var(--primary-dark);
        }

        .hero p {
            max-width: 660px;
            color: var(--muted);
            font-size: 19px;
            margin-bottom: 30px;
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 34px;
        }

        .trust-row {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 14px;
            max-width: 680px;
        }

        .trust-item {
            padding: 16px;
            border: 1px solid var(--border);
            border-radius: 20px;
            background: rgba(255, 255, 255, .74);
        }

        .trust-item strong {
            display: block;
            font-size: 20px;
            line-height: 1.1;
        }

        .trust-item small {
            color: var(--muted);
            font-weight: 700;
        }

        .hero-card {
            position: relative;
            border-radius: var(--radius);
            background: #111827;
            color: white;
            box-shadow: var(--shadow);
            overflow: hidden;
            padding: 28px;
        }

        .hero-card::before {
            content: '';
            position: absolute;
            inset: -80px -80px auto auto;
            width: 220px;
            height: 220px;
            border-radius: 999px;
            background: rgba(245, 158, 11, .32);
            filter: blur(12px);
        }

        .hero-card > * {
            position: relative;
            z-index: 1;
        }

        .panel-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 22px;
        }

        .panel-title strong {
            font-size: 20px;
        }

        .status-pill {
            padding: 7px 11px;
            border-radius: 999px;
            background: rgba(22, 163, 74, .16);
            color: #86efac;
            font-size: 12px;
            font-weight: 900;
        }

        .metric-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
            margin-bottom: 18px;
        }

        .metric {
            padding: 18px;
            border-radius: 20px;
            background: rgba(255, 255, 255, .08);
            border: 1px solid rgba(255, 255, 255, .10);
        }

        .metric span {
            color: #cbd5e1;
            font-size: 13px;
            font-weight: 700;
        }

        .metric strong {
            display: block;
            margin-top: 8px;
            font-size: 28px;
            line-height: 1;
        }

        .timeline {
            display: grid;
            gap: 12px;
            margin-top: 20px;
        }

        .timeline-row {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px;
            border-radius: 18px;
            background: rgba(255, 255, 255, .07);
        }

        .dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--primary);
            flex: 0 0 auto;
        }

        .timeline-row span {
            color: #e2e8f0;
            font-weight: 700;
            font-size: 14px;
        }

        .section {
            padding: 70px 0;
        }

        .section-header {
            max-width: 760px;
            margin-bottom: 34px;
        }

        .section-header h2 {
            font-size: clamp(32px, 4vw, 48px);
            line-height: 1.05;
            letter-spacing: -.05em;
            margin-bottom: 14px;
        }

        .section-header p {
            color: var(--muted);
            font-size: 18px;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
        }

        .feature-card,
        .plan-card,
        .cta-box {
            border: 1px solid var(--border);
            background: rgba(255, 255, 255, .84);
            border-radius: var(--radius);
            box-shadow: 0 18px 48px rgba(15, 23, 42, .07);
        }

        .feature-card {
            padding: 26px;
        }

        .feature-icon {
            width: 48px;
            height: 48px;
            display: grid;
            place-items: center;
            margin-bottom: 18px;
            border-radius: 16px;
            background: rgba(245, 158, 11, .14);
            color: #92400e;
            font-size: 22px;
        }

        .feature-card h3 {
            font-size: 20px;
            margin-bottom: 8px;
            letter-spacing: -.02em;
        }

        .feature-card p {
            color: var(--muted);
        }

        .plans-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(245px, 1fr));
            gap: 18px;
            align-items: stretch;
        }

        .plan-card {
            display: flex;
            flex-direction: column;
            padding: 28px;
            position: relative;
        }

        .plan-card.highlight {
            border-color: rgba(245, 158, 11, .72);
            box-shadow: 0 24px 70px rgba(245, 158, 11, .16);
        }

        .tag {
            display: inline-flex;
            width: max-content;
            padding: 7px 12px;
            border-radius: 999px;
            background: rgba(22, 163, 74, .12);
            color: #166534;
            font-size: 12px;
            font-weight: 900;
            margin-bottom: 14px;
        }

        .plan-card h3 {
            font-size: 24px;
            margin-bottom: 10px;
            letter-spacing: -.03em;
        }

        .old-price {
            color: var(--muted);
            text-decoration: line-through;
            font-size: 14px;
            font-weight: 800;
            opacity: .7;
        }

        .price {
            font-size: 34px;
            font-weight: 900;
            letter-spacing: -.04em;
            margin-bottom: 6px;
        }

        .price span {
            font-size: 15px;
            color: var(--muted);
            font-weight: 800;
        }

        .promo {
            color: var(--success);
            font-weight: 900;
            font-size: 13px;
            margin-bottom: 14px;
        }

        .plan-list {
            display: grid;
            gap: 10px;
            margin: 18px 0 24px;
            color: #475569;
            font-weight: 650;
            font-size: 14px;
        }

        .plan-list div::before {
            content: '✓';
            color: var(--success);
            font-weight: 900;
            margin-right: 8px;
        }

        .plan-card .btn {
            margin-top: auto;
            width: 100%;
        }

        .cta-box {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 24px;
            align-items: center;
            padding: 34px;
            background: linear-gradient(135deg, #111827, #1e293b);
            color: #ffffff;
        }

        .cta-box h2 {
            font-size: clamp(30px, 4vw, 46px);
            line-height: 1.05;
            letter-spacing: -.05em;
            margin-bottom: 10px;
        }

        .cta-box p {
            color: #cbd5e1;
            font-size: 17px;
        }

        .footer {
            padding: 36px 0 48px;
            color: var(--muted);
            font-weight: 700;
            font-size: 14px;
        }

        @media (max-width: 980px) {
            .hero-grid,
            .features-grid,
            .cta-box {
                grid-template-columns: 1fr;
            }

            .nav-links {
                display: none;
            }
        }

        @media (max-width: 640px) {
            .nav {
                align-items: flex-start;
                flex-direction: column;
                padding: 16px 0;
            }

            .nav-actions,
            .hero-actions {
                width: 100%;
            }

            .btn {
                flex: 1;
                padding: 0 14px;
            }

            .trust-row,
            .metric-grid {
                grid-template-columns: 1fr;
            }

            .hero {
                padding-top: 46px;
            }
        }
    </style>
</head>
<body>
<header class="topbar">
    <div class="container nav">
        <a href="{{ url('/') }}" class="brand" aria-label="Página inicial">
            <span class="brand-mark">P</span>
            <span>{{ $brandName }}</span>
        </a>

        <nav class="nav-links" aria-label="Navegação principal">
            <a href="#recursos">Recursos</a>
            <a href="#planos">Planos</a>
            <a href="{{ route('empresa.cadastro.create') }}">Cadastro</a>
        </nav>

        <div class="nav-actions">
            <a class="btn btn-light" href="{{ url('/admin/login') }}">Logar</a>
            <a class="btn btn-primary" href="{{ route('empresa.cadastro.create') }}">Alugar SaaS</a>
        </div>
    </div>
</header>

<main>
    <section class="hero">
        <div class="container hero-grid">
            <div>
                <div class="eyebrow">Gestão operacional para empresas organizadas</div>

                <h1>Controle prazos, documentos e tarefas em um <span>SaaS completo</span>.</h1>

                <p>
                    O {{ $brandName }} centraliza itens de controle, vencimentos, responsáveis,
                    checklist, aprovações, portal do cliente, relatórios e assistente virtual em uma única plataforma.
                </p>

                <div class="hero-actions">
                    <a class="btn btn-primary" href="{{ route('empresa.cadastro.create') }}">Alugar SaaS agora</a>
                    <a class="btn btn-dark" href="{{ url('/admin/login') }}">Já sou cliente</a>
                    <a class="btn btn-light" href="#planos">Ver planos</a>
                </div>

                <div class="trust-row">
                    <div class="trust-item">
                        <strong>Alertas</strong>
                        <small>vencimentos e prazos</small>
                    </div>
                    <div class="trust-item">
                        <strong>Portal</strong>
                        <small>acesso seguro ao cliente</small>
                    </div>
                    <div class="trust-item">
                        <strong>BI</strong>
                        <small>indicadores da operação</small>
                    </div>
                </div>
            </div>

            <aside class="hero-card" aria-label="Resumo visual do sistema">
                <div class="panel-title">
                    <strong>Painel operacional</strong>
                    <span class="status-pill">Online</span>
                </div>

                <div class="metric-grid">
                    <div class="metric">
                        <span>Escalável para</span>
                        <strong>Milhares</strong>
                    </div>
                    <div class="metric">
                        <span>Prazos no radar</span>
                        <strong>24/7</strong>
                    </div>
                    <div class="metric">
                        <span>Checklist</span>
                        <strong>100%</strong>
                    </div>
                    <div class="metric">
                        <span>Relatórios</span>
                        <strong>PDF/Excel</strong>
                    </div>
                </div>

                <div class="timeline">
                    <div class="timeline-row"><span class="dot"></span><span>Documento novo cadastrado e atribuído ao responsável.</span></div>
                    <div class="timeline-row"><span class="dot"></span><span>Alerta automático antes do vencimento.</span></div>
                    <div class="timeline-row"><span class="dot"></span><span>Histórico, aprovação e auditoria centralizados.</span></div>
                </div>
            </aside>
        </div>
    </section>

    <section class="section" id="recursos">
        <div class="container">
            <div class="section-header">
                <h2>Uma plataforma para tirar a operação do improviso.</h2>
                <p>Organize rotinas, prazos e responsabilidades com rastreabilidade de ponta a ponta.</p>
            </div>

            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">📁</div>
                    <h3>Documentos e vencimentos</h3>
                    <p>Cadastre itens de controle, acompanhe status e receba alertas antes que algo vença.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">✅</div>
                    <h3>Checklist e aprovações</h3>
                    <p>Padronize processos com checklist, histórico, comentários, aprovação e reprovação.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">📊</div>
                    <h3>Dashboard e relatórios</h3>
                    <p>Veja métricas por empresa, responsável e situação, com exportações para análise.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">🔐</div>
                    <h3>Portal do cliente</h3>
                    <p>Compartilhe acompanhamento por links seguros, sem expor o painel administrativo.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">🤖</div>
                    <h3>Assistente virtual</h3>
                    <p>O chat fica disponível somente para usuários logados, ajudando no uso do sistema.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">🧾</div>
                    <h3>Auditoria e histórico</h3>
                    <p>Registre movimentações importantes e mantenha rastreabilidade das ações.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="section" id="planos">
        <div class="container">
            <div class="section-header">
                <h2>Planos para cada fase da sua operação.</h2>
                <p>Comece simples e evolua conforme sua empresa ganha volume, equipe e processos.</p>
            </div>

            @php
                $planosComerciais = \App\Services\PlanoService::planos();
            @endphp
            <div class="plans-grid">
                @foreach($planosComerciais as $codigoPlano => $planoComercial)
                    @php
                        $limiteUsuarios = (int) ($planoComercial['limite_usuarios'] ?? 0);
                        $limiteItens = (int) ($planoComercial['limite_itens'] ?? 0);
                        $usuariosTexto = $limiteUsuarios >= 999999 ? 'Usuários sob demanda' : 'Até ' . number_format($limiteUsuarios, 0, ',', '.') . ' usuários';
                        $itensTexto = $limiteItens >= 999999 ? 'Itens sob demanda' : 'Até ' . number_format($limiteItens, 0, ',', '.') . ' itens';
                        $ctaTexto = $codigoPlano === \App\Services\PlanoService::ENTERPRISE ? 'Solicitar proposta' : 'Começar no ' . ($planoComercial['nome'] ?? 'plano');
                    @endphp

                    <article class="plan-card {{ ! empty($planoComercial['destaque']) ? 'highlight' : '' }}">
                        @if(! empty($planoComercial['tag']))
                            <span class="tag">{{ $planoComercial['tag'] }}</span>
                        @endif

                        <h3>{{ $planoComercial['nome_comercial'] ?? $planoComercial['nome'] }}</h3>
                        <div class="price">{{ $planoComercial['preco'] ?? \App\Services\PlanoService::preco($codigoPlano) }}</div>
                        <p>{{ $planoComercial['descricao'] ?? 'Plano para gestão operacional.' }}</p>

                        <div class="plan-list">
                            <div>{{ $usuariosTexto }}</div>
                            <div>{{ $itensTexto }}</div>
                            @if((int) ($planoComercial['limite_interacoes_ia'] ?? 0) > 0)
                                <div>{{ number_format((int) ($planoComercial['limite_interacoes_ia'] ?? 0), 0, ',', '.') }} interações de IA/mês</div>
                            @else
                                <div>IA disponível apenas em planos pagos selecionados</div>
                            @endif
                            @foreach($planoComercial['itens'] ?? [] as $itemPlano)
                                <div>{{ $itemPlano }}</div>
                            @endforeach
                        </div>

                        <a class="btn {{ ! empty($planoComercial['destaque']) ? 'btn-primary' : 'btn-light' }}" href="{{ route('empresa.cadastro.create', ['plano' => $codigoPlano]) }}">
                            {{ $ctaTexto }}
                        </a>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="cta-box">
                <div>
                    <h2>Pronto para apresentar o sistema para seus clientes?</h2>
                    <p>Crie sua conta, escolha o plano e comece a organizar a gestão operacional da empresa.</p>
                </div>

                <div class="hero-actions">
                    <a class="btn btn-primary" href="{{ route('empresa.cadastro.create') }}">Alugar SaaS</a>
                    <a class="btn btn-light" href="{{ url('/admin/login') }}">Logar</a>
                </div>
            </div>
        </div>
    </section>
</main>

<footer class="footer">
    <div class="container">
        © {{ date('Y') }} {{ $brandName }}. Gestão de prazos, documentos e controles em formato SaaS.
    </div>
</footer>
</body>
</html>
