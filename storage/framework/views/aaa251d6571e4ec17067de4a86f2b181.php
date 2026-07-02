<?php
    $whiteLabel = \App\Support\WhiteLabelSettings::make();
    $brandName = $whiteLabel->displayName();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($brandName); ?> | Gestão de documentos, prazos e processos</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800,900" rel="stylesheet" />

    <style>
        :root {
            --bg: #f8fafc;
            --card: #ffffff;
            --text: #0f172a;
            --muted: #64748b;
            --primary: #f59e0b;
            --primary-dark: #d97706;
            --secondary: #111827;
            --success: #16a34a;
            --danger: #dc2626;
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
            background: rgba(248, 250, 252, .88);
            border-bottom: 1px solid rgba(226, 232, 240, .85);
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
            font-weight: 900;
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
            font-weight: 800;
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
            min-height: 48px;
            padding: 0 22px;
            border-radius: 999px;
            font-weight: 900;
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
            font-weight: 900;
            font-size: 13px;
        }

        .hero h1 {
            max-width: 780px;
            font-size: clamp(40px, 6vw, 72px);
            line-height: .98;
            letter-spacing: -.07em;
            margin-bottom: 24px;
        }

        .hero h1 span {
            color: var(--primary-dark);
        }

        .hero p {
            max-width: 680px;
            color: var(--muted);
            font-size: 19px;
            margin-bottom: 28px;
        }

        .offer-box {
            max-width: 680px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 28px;
        }

        .offer-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 999px;
            background: #ffffff;
            border: 1px solid var(--border);
            color: #334155;
            font-size: 14px;
            font-weight: 900;
            box-shadow: 0 10px 28px rgba(15, 23, 42, .06);
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
            background: rgba(255, 255, 255, .78);
        }

        .trust-item strong {
            display: block;
            font-size: 20px;
            line-height: 1.1;
        }

        .trust-item small {
            color: var(--muted);
            font-weight: 800;
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
            font-weight: 800;
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
            font-weight: 800;
            font-size: 14px;
        }

        .section {
            padding: 70px 0;
        }

        .section-header {
            max-width: 780px;
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

        .problem-grid,
        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
        }

        .problem-card,
        .feature-card,
        .plan-card,
        .cta-box,
        .faq-card {
            border: 1px solid var(--border);
            background: rgba(255, 255, 255, .86);
            border-radius: var(--radius);
            box-shadow: 0 18px 48px rgba(15, 23, 42, .07);
        }

        .problem-card,
        .feature-card,
        .faq-card {
            padding: 26px;
        }

        .problem-card strong {
            display: block;
            margin-bottom: 8px;
            color: var(--danger);
            font-size: 18px;
        }

        .problem-card p,
        .feature-card p,
        .faq-card p {
            color: var(--muted);
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
            transform: translateY(-8px);
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
            font-weight: 900;
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
            font-weight: 900;
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
            font-weight: 700;
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

        .faq-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        .faq-card h3 {
            margin-bottom: 8px;
            font-size: 19px;
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
            .problem-grid,
            .features-grid,
            .faq-grid,
            .cta-box {
                grid-template-columns: 1fr;
            }

            .nav-links {
                display: none;
            }

            .plan-card.highlight {
                transform: none;
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
        <a href="<?php echo e(url('/')); ?>" class="brand" aria-label="Página inicial">
            <span class="brand-mark">P</span>
            <span><?php echo e($brandName); ?></span>
        </a>

        <nav class="nav-links" aria-label="Navegação principal">
            <a href="#problemas">Problemas</a>
            <a href="#recursos">Recursos</a>
            <a href="#planos">Planos</a>
            <a href="#duvidas">Dúvidas</a>
        </nav>

        <div class="nav-actions">
            <a class="btn btn-light" href="<?php echo e(route('portal.cliente.login')); ?>">Área do Cliente</a>
            <a class="btn btn-light" href="<?php echo e(url('/admin/login')); ?>">Entrar</a>
            <a class="btn btn-primary" href="<?php echo e(route('empresa.cadastro.create')); ?>">Testar grátis</a>
        </div>
    </div>
</header>

<main>
    <section class="hero">
        <div class="container hero-grid">
            <div>
                <div class="eyebrow">Para escritórios contábeis, RH e empresas com muitos controles</div>

                <h1>Evite perder <span>prazos, documentos e processos</span> importantes.</h1>

                <p>
                    O <?php echo e($brandName); ?> centraliza documentos, vencimentos, responsáveis,
                    checklists, aprovações, contratos, portal do cliente, relatórios e assistente virtual
                    em uma única plataforma.
                </p>

                <div class="offer-box">
                    <div class="offer-pill">✅ 7 dias grátis</div>
                    <div class="offer-pill">✅ Implantação gratuita</div>
                    <div class="offer-pill">✅ Sem fidelidade</div>
                </div>

                <div class="hero-actions">
                    <a class="btn btn-primary" href="<?php echo e(route('empresa.cadastro.create')); ?>">Começar teste grátis</a>
                    <a class="btn btn-dark" href="#planos">Ver planos</a>
                    <a class="btn btn-light" href="<?php echo e(route('portal.cliente.login')); ?>">Área do Cliente</a>
                    <a class="btn btn-light" href="<?php echo e(url('/admin/login')); ?>">Área Administrativa</a>
                </div>

                <div class="trust-row">
                    <div class="trust-item">
                        <strong>Alertas</strong>
                        <small>antes dos vencimentos</small>
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
                        <span>Itens monitorados</span>
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
                    <div class="metric">
                        <span>Histórico</span>
                        <strong>Total</strong>
                    </div>
                </div>

                <div class="timeline">
                    <div class="timeline-row"><span class="dot"></span><span>Documento cadastrado e atribuído ao responsável.</span></div>
                    <div class="timeline-row"><span class="dot"></span><span>Alerta automático antes do vencimento.</span></div>
                    <div class="timeline-row"><span class="dot"></span><span>Aprovação, histórico e auditoria centralizados.</span></div>
                </div>
            </aside>
        </div>
    </section>

    <section class="section" id="problemas">
        <div class="container">
            <div class="section-header">
                <h2>Seu escritório ainda controla tudo no improviso?</h2>
                <p>Planilhas, WhatsApp e e-mails soltos funcionam no começo, mas viram risco quando a operação cresce.</p>
            </div>

            <div class="problem-grid">
                <div class="problem-card">
                    <strong>Prazos esquecidos</strong>
                    <p>Documentos e contratos vencem sem aviso, gerando correria, retrabalho e risco operacional.</p>
                </div>

                <div class="problem-card">
                    <strong>Responsáveis sem clareza</strong>
                    <p>A equipe não sabe exatamente quem está cuidando de cada item, aprovação ou pendência.</p>
                </div>

                <div class="problem-card">
                    <strong>Cliente cobrando retorno</strong>
                    <p>Sem portal e histórico centralizado, fica difícil mostrar andamento e provar o que foi feito.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="section" id="recursos">
        <div class="container">
            <div class="section-header">
                <h2>Uma plataforma para tirar sua operação das planilhas.</h2>
                <p>Organize rotinas, prazos, documentos e responsabilidades com rastreabilidade de ponta a ponta.</p>
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
                    <p>Veja métricas por empresa, responsável e situação, com exportações em PDF, Excel e CSV.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">🔐</div>
                    <h3>Portal do cliente</h3>
                    <p>Compartilhe acompanhamento por links seguros, sem expor o painel administrativo.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">🤖</div>
                    <h3>Assistente virtual</h3>
                    <p>Usuários logados contam com apoio do assistente virtual para facilitar o uso do sistema.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon">🧾</div>
                    <h3>Auditoria e histórico</h3>
                    <p>Registre movimentações importantes e mantenha rastreabilidade das ações da equipe.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="section" id="planos">
        <div class="container">
            <div class="section-header">
                <h2>Escolha o plano ideal para sua operação.</h2>
                <p>Planos atualizados conforme os módulos atuais do sistema e a configuração central usada para liberar funcionalidades.</p>
            </div>

            <?php
                $planosComerciais = \App\Services\PlanoService::planos();
            ?>
            <div class="plans-grid">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $planosComerciais; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $codigoPlano => $planoComercial): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php
                        $limiteUsuarios = (int) ($planoComercial['limite_usuarios'] ?? 0);
                        $limiteItens = (int) ($planoComercial['limite_itens'] ?? 0);
                        $usuariosTexto = $limiteUsuarios >= 999999 ? 'Usuários sob demanda' : 'Até ' . number_format($limiteUsuarios, 0, ',', '.') . ' usuários';
                        $itensTexto = $limiteItens >= 999999 ? 'Itens sob demanda' : 'Até ' . number_format($limiteItens, 0, ',', '.') . ' itens';
                        $ctaTexto = $codigoPlano === \App\Services\PlanoService::ENTERPRISE ? 'Solicitar proposta' : 'Começar no ' . ($planoComercial['nome'] ?? 'plano');
                    ?>

                    <article class="plan-card <?php echo e(! empty($planoComercial['destaque']) ? 'highlight' : ''); ?>">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! empty($planoComercial['tag'])): ?>
                            <span class="tag"><?php echo e($planoComercial['tag']); ?></span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <h3><?php echo e($planoComercial['nome_comercial'] ?? $planoComercial['nome']); ?></h3>
                        <div class="price"><?php echo e($planoComercial['preco'] ?? \App\Services\PlanoService::preco($codigoPlano)); ?></div>
                        <p><?php echo e($planoComercial['descricao'] ?? 'Plano para gestão operacional.'); ?></p>

                        <div class="plan-list">
                            <div><?php echo e($usuariosTexto); ?></div>
                            <div><?php echo e($itensTexto); ?></div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if((int) ($planoComercial['limite_interacoes_ia'] ?? 0) > 0): ?>
                                <div><?php echo e(number_format((int) ($planoComercial['limite_interacoes_ia'] ?? 0), 0, ',', '.')); ?> interações de IA/mês</div>
                            <?php else: ?>
                                <div>IA disponível apenas em planos pagos selecionados</div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $planoComercial['itens'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $itemPlano): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <div><?php echo e($itemPlano); ?></div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>

                        <a class="btn <?php echo e(! empty($planoComercial['destaque']) ? 'btn-primary' : 'btn-light'); ?>" href="<?php echo e(route('empresa.cadastro.create', ['plano' => $codigoPlano])); ?>">
                            <?php echo e($ctaTexto); ?>

                        </a>
                    </article>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
        </div>
    </section>

    <section class="section" id="duvidas">
        <div class="container">
            <div class="section-header">
                <h2>Dúvidas comuns antes de começar.</h2>
                <p>O objetivo é você testar com segurança e validar se o sistema encaixa na sua operação.</p>
            </div>

            <div class="faq-grid">
                <div class="faq-card">
                    <h3>O que significa implantação gratuita?</h3>
                    <p>Ajudamos na configuração inicial, como categorias, responsáveis, modelos de checklist e primeiros controles.</p>
                </div>

                <div class="faq-card">
                    <h3>Preciso instalar algo?</h3>
                    <p>Não. O sistema funciona online, em formato SaaS, acessado pelo navegador.</p>
                </div>

                <div class="faq-card">
                    <h3>Posso cancelar?</h3>
                    <p>Sim. Não há fidelidade obrigatória nos planos mensais.</p>
                </div>

                <div class="faq-card">
                    <h3>Serve para escritório contábil?</h3>
                    <p>Sim. É indicado para controlar documentos, vencimentos, aprovações, contratos, clientes e pendências operacionais.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="cta-box">
                <div>
                    <h2>Comece sem risco e organize sua operação hoje.</h2>
                    <p>Teste grátis por 7 dias, receba implantação gratuita e veja se o <?php echo e($brandName); ?> encaixa na sua rotina.</p>
                </div>

                <div class="hero-actions">
                    <a class="btn btn-primary" href="<?php echo e(route('empresa.cadastro.create')); ?>">Começar teste grátis</a>
                    <a class="btn btn-light" href="<?php echo e(route('portal.cliente.login')); ?>">Área do Cliente</a>
                </div>
            </div>
        </div>
    </section>
</main>

<footer class="footer">
    <div class="container">
        © <?php echo e(date('Y')); ?> <?php echo e($brandName); ?>. Gestão de documentos, prazos e processos em formato SaaS.
    </div>
</footer>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views\welcome.blade.php ENDPATH**/ ?>