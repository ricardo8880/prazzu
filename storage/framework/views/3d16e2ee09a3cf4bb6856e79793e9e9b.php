<?php
    $whiteLabel = \App\Support\WhiteLabelSettings::make();
    $brandName = $whiteLabel->displayName();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($brandName); ?> | Plataforma para contabilidades</title>

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

        * { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }
        body {
            min-height: 100vh;
            font-family: 'Instrument Sans', Arial, sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at top left, rgba(245, 158, 11, .18), transparent 34%),
                radial-gradient(circle at top right, rgba(30, 41, 59, .10), transparent 30%),
                var(--bg);
            line-height: 1.6;
        }
        a { color: inherit; text-decoration: none; }
        .container { width: min(1180px, calc(100% - 32px)); margin: 0 auto; }

        .topbar {
            position: sticky;
            top: 0;
            z-index: 20;
            backdrop-filter: blur(18px);
            background: rgba(248, 250, 252, .88);
            border-bottom: 1px solid rgba(226, 232, 240, .85);
        }
        .nav { min-height: 78px; display: flex; align-items: center; justify-content: space-between; gap: 20px; }
        .brand { display: flex; align-items: center; gap: 12px; font-weight: 900; font-size: 22px; letter-spacing: -.04em; }
        .brand-mark {
            width: 44px; height: 44px; display: grid; place-items: center; border-radius: 16px;
            background: linear-gradient(135deg, var(--primary), #fbbf24); color: white;
            box-shadow: 0 16px 30px rgba(245, 158, 11, .28); font-weight: 900;
        }
        .nav-links { display: flex; align-items: center; gap: 24px; color: #475569; font-weight: 800; font-size: 14px; }
        .nav-actions { display: flex; align-items: center; gap: 12px; }
        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            min-height: 48px; padding: 0 22px; border-radius: 999px; font-weight: 900;
            border: 1px solid transparent; transition: .2s ease; cursor: pointer; white-space: nowrap;
        }
        .btn:hover { transform: translateY(-1px); }
        .btn-primary { color: #111827; background: linear-gradient(135deg, #fbbf24, var(--primary)); box-shadow: 0 16px 30px rgba(245, 158, 11, .25); }
        .btn-dark { color: #ffffff; background: var(--secondary); box-shadow: 0 16px 30px rgba(15, 23, 42, .22); }
        .btn-light { color: var(--secondary); background: #ffffff; border-color: var(--border); }

        .hero { padding: 76px 0 54px; }
        .hero-grid { display: grid; grid-template-columns: minmax(0, 1.05fr) minmax(340px, .95fr); gap: 48px; align-items: center; }
        .eyebrow {
            display: inline-flex; align-items: center; gap: 8px; margin-bottom: 18px; padding: 8px 14px;
            border-radius: 999px; background: rgba(245, 158, 11, .14); color: #92400e; font-weight: 900; font-size: 13px;
        }
        .hero h1 { max-width: 800px; font-size: clamp(40px, 6vw, 72px); line-height: .98; letter-spacing: -.07em; margin-bottom: 24px; }
        .hero h1 span { color: var(--primary-dark); }
        .hero p { max-width: 700px; color: var(--muted); font-size: 19px; margin-bottom: 28px; }
        .offer-box { max-width: 700px; display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 28px; }
        .offer-pill {
            display: inline-flex; align-items: center; gap: 8px; padding: 10px 14px; border-radius: 999px;
            background: #ffffff; border: 1px solid var(--border); color: #334155; font-size: 14px; font-weight: 900;
            box-shadow: 0 10px 28px rgba(15, 23, 42, .06);
        }
        .hero-actions { display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 34px; }
        .trust-row { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; max-width: 700px; }
        .trust-item { padding: 16px; border: 1px solid var(--border); border-radius: 20px; background: rgba(255, 255, 255, .78); }
        .trust-item strong { display: block; font-size: 20px; line-height: 1.1; }
        .trust-item small { color: var(--muted); font-weight: 800; }

        .hero-card { position: relative; border-radius: var(--radius); background: #111827; color: white; box-shadow: var(--shadow); overflow: hidden; padding: 28px; }
        .hero-card::before {
            content: ''; position: absolute; inset: -80px -80px auto auto; width: 220px; height: 220px;
            border-radius: 999px; background: rgba(245, 158, 11, .32); filter: blur(12px);
        }
        .hero-card > * { position: relative; z-index: 1; }
        .panel-title { display: flex; align-items: center; justify-content: space-between; margin-bottom: 22px; }
        .panel-title strong { font-size: 20px; }
        .status-pill { padding: 7px 11px; border-radius: 999px; background: rgba(22, 163, 74, .16); color: #86efac; font-size: 12px; font-weight: 900; }
        .metric-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; margin-bottom: 18px; }
        .metric { padding: 18px; border-radius: 20px; background: rgba(255, 255, 255, .08); border: 1px solid rgba(255, 255, 255, .10); }
        .metric span { color: #cbd5e1; font-size: 13px; font-weight: 800; }
        .metric strong { display: block; font-size: 28px; margin-top: 4px; }
        .timeline { margin-top: 18px; display: grid; gap: 12px; }
        .timeline-row { display: flex; align-items: center; gap: 12px; padding: 14px; border-radius: 18px; background: rgba(255, 255, 255, .07); }
        .dot { width: 12px; height: 12px; border-radius: 50%; background: var(--primary); flex: 0 0 auto; }
        .timeline-row span { color: #e2e8f0; font-weight: 800; font-size: 14px; }

        .section { padding: 70px 0; }
        .section-header { max-width: 800px; margin-bottom: 34px; }
        .section-header.center { margin-left: auto; margin-right: auto; text-align: center; }
        .section-header h2 { font-size: clamp(32px, 4vw, 48px); line-height: 1.05; letter-spacing: -.05em; margin-bottom: 14px; }
        .section-header p { color: var(--muted); font-size: 18px; }

        .problem-grid, .features-grid, .flow-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 18px; }
        .problem-card, .feature-card, .flow-card, .plan-card, .cta-box, .faq-card {
            border: 1px solid var(--border); background: rgba(255, 255, 255, .86); border-radius: var(--radius); box-shadow: 0 18px 48px rgba(15, 23, 42, .07);
        }
        .problem-card, .feature-card, .flow-card, .faq-card { padding: 26px; }
        .problem-card strong { display: block; margin-bottom: 8px; color: var(--danger); font-size: 18px; }
        .problem-card p, .feature-card p, .flow-card p, .faq-card p { color: var(--muted); }
        .feature-icon, .flow-step {
            width: 48px; height: 48px; display: grid; place-items: center; margin-bottom: 18px; border-radius: 16px;
            background: rgba(245, 158, 11, .14); color: #92400e; font-size: 22px; font-weight: 900;
        }
        .feature-card h3, .flow-card h3 { font-size: 20px; margin-bottom: 8px; letter-spacing: -.02em; }

        .plans-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(255px, 1fr)); gap: 18px; align-items: stretch; }
        .plan-card { display: flex; flex-direction: column; padding: 28px; position: relative; }
        .plan-card.highlight { border-color: rgba(245, 158, 11, .72); box-shadow: 0 24px 70px rgba(245, 158, 11, .16); transform: translateY(-8px); }
        .tag { display: inline-flex; width: max-content; padding: 7px 12px; border-radius: 999px; background: rgba(22, 163, 74, .12); color: #166534; font-size: 12px; font-weight: 900; margin-bottom: 14px; }
        .plan-card h3 { font-size: 24px; margin-bottom: 10px; letter-spacing: -.03em; }
        .price { font-size: 34px; font-weight: 900; letter-spacing: -.04em; margin-bottom: 6px; }
        .price span { font-size: 15px; color: var(--muted); letter-spacing: normal; }
        .plan-card > p { color: var(--muted); margin-bottom: 18px; }
        .plan-list { display: grid; gap: 10px; margin: 18px 0 20px; color: #334155; font-weight: 800; font-size: 14px; }
        .plan-list div { display: flex; gap: 8px; align-items: flex-start; }
        .plan-list div::before { content: '✓'; color: var(--success); font-weight: 900; }
        .plan-config { display: grid; gap: 12px; margin: 10px 0 20px; }
        .select-row label { display: block; font-size: 12px; color: #475569; font-weight: 900; margin-bottom: 6px; }
        .select-row select {
            width: 100%; min-height: 44px; border-radius: 14px; border: 1px solid var(--border); background: #fff;
            padding: 0 12px; font-weight: 900; color: #0f172a; outline: none;
        }
        .plan-total { margin-top: auto; padding-top: 14px; border-top: 1px solid var(--border); }
        .plan-total small { color: var(--muted); font-weight: 900; }
        .plan-total strong { display: block; font-size: 24px; letter-spacing: -.03em; }
        .plan-card .btn { width: 100%; margin-top: 16px; }

        .cta-box { padding: 46px; background: #111827; color: #fff; overflow: hidden; position: relative; }
        .cta-box h2 { font-size: clamp(32px, 4vw, 50px); line-height: 1.05; letter-spacing: -.05em; margin-bottom: 16px; }
        .cta-box p { color: #cbd5e1; font-size: 18px; max-width: 760px; margin-bottom: 24px; }
        .faq-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px; }
        .faq-card h3 { margin-bottom: 8px; font-size: 18px; }

        @media (max-width: 980px) {
            .hero-grid, .problem-grid, .features-grid, .flow-grid, .faq-grid { grid-template-columns: 1fr; }
            .nav-links { display: none; }
            .plan-card.highlight { transform: none; }
        }
        @media (max-width: 640px) {
            .nav { align-items: flex-start; flex-direction: column; padding: 16px 0; }
            .nav-actions, .hero-actions { width: 100%; }
            .btn { flex: 1; padding: 0 14px; }
            .trust-row, .metric-grid { grid-template-columns: 1fr; }
            .hero { padding-top: 46px; }
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
            <a href="#fluxo">Como funciona</a>
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
                <div class="eyebrow">Feito para escritórios contábeis</div>
                <h1>Centralize <span>documentos, aprovações e assinaturas</span> dos seus clientes.</h1>
                <p>
                    Pare de perder arquivos no WhatsApp, e-mail e planilhas. O <?php echo e($brandName); ?> organiza solicitações,
                    documentos, pendências, aprovações e histórico operacional da sua contabilidade em um único lugar.
                </p>
                <div class="offer-box">
                    <div class="offer-pill">✅ Plano gratuito disponível</div>
                    <div class="offer-pill">✅ Sem cartão de crédito</div>
                    <div class="offer-pill">✅ Foco em contabilidade</div>
                </div>
                <div class="hero-actions">
                    <a class="btn btn-primary" href="<?php echo e(route('empresa.cadastro.create')); ?>">Começar grátis</a>
                    <a class="btn btn-dark" href="#planos">Ver planos</a>
                    <a class="btn btn-light" href="<?php echo e(route('portal.cliente.login')); ?>">Área do Cliente</a>
                </div>
                <div class="trust-row">
                    <div class="trust-item"><strong>Menos</strong><small>retrabalho operacional</small></div>
                    <div class="trust-item"><strong>Mais</strong><small>controle por cliente</small></div>
                    <div class="trust-item"><strong>Tudo</strong><small>com histórico e rastreio</small></div>
                </div>
            </div>

            <aside class="hero-card" aria-label="Resumo visual do sistema">
                <div class="panel-title">
                    <strong>Painel da contabilidade</strong>
                    <span class="status-pill">Operação em dia</span>
                </div>
                <div class="metric-grid">
                    <div class="metric"><span>Documentos recebidos</span><strong>128</strong></div>
                    <div class="metric"><span>Pendências abertas</span><strong>17</strong></div>
                    <div class="metric"><span>Aprovações concluídas</span><strong>92%</strong></div>
                    <div class="metric"><span>Clientes ativos</span><strong>34</strong></div>
                </div>
                <div class="timeline">
                    <div class="timeline-row"><span class="dot"></span><span>Cliente enviou documento solicitado</span></div>
                    <div class="timeline-row"><span class="dot"></span><span>Equipe aprovou a pendência fiscal</span></div>
                    <div class="timeline-row"><span class="dot"></span><span>Contrato pronto para assinatura digital</span></div>
                    <div class="timeline-row"><span class="dot"></span><span>Histórico salvo automaticamente</span></div>
                </div>
            </aside>
        </div>
    </section>

    <section class="section" id="problemas">
        <div class="container">
            <div class="section-header">
                <h2>Sua contabilidade ainda depende de mensagens soltas?</h2>
                <p>Quando documentos, aprovações e cobranças ficam espalhados, sua equipe perde tempo e a operação perde controle.</p>
            </div>
            <div class="problem-grid">
                <div class="problem-card"><strong>Arquivos perdidos</strong><p>Documentos importantes ficam espalhados entre WhatsApp, e-mail e pastas sem padrão.</p></div>
                <div class="problem-card"><strong>Cobrança manual</strong><p>Sua equipe perde tempo lembrando clientes de enviar arquivos e concluir pendências.</p></div>
                <div class="problem-card"><strong>Pouca rastreabilidade</strong><p>Fica difícil saber quem enviou, aprovou, alterou ou deixou uma solicitação pendente.</p></div>
            </div>
        </div>
    </section>

    <section class="section" id="fluxo">
        <div class="container">
            <div class="section-header center">
                <h2>Um fluxo simples para organizar a operação contábil.</h2>
                <p>O cliente envia, sua equipe acompanha, aprova, assina quando necessário e mantém tudo registrado.</p>
            </div>
            <div class="flow-grid">
                <div class="flow-card"><div class="flow-step">1</div><h3>Solicite documentos</h3><p>Crie solicitações por cliente e acompanhe tudo em um painel único.</p></div>
                <div class="flow-card"><div class="flow-step">2</div><h3>Receba e aprove</h3><p>Controle pendências, comentários, anexos, checklist e responsáveis internos.</p></div>
                <div class="flow-card"><div class="flow-step">3</div><h3>Assine e audite</h3><p>Use assinatura digital nos planos pagos e mantenha histórico completo das movimentações.</p></div>
            </div>
        </div>
    </section>

    <section class="section" id="recursos">
        <div class="container">
            <div class="section-header">
                <h2>O que sua contabilidade ganha com o <?php echo e($brandName); ?>.</h2>
                <p>Menos retrabalho, mais organização e uma experiência mais profissional para seus clientes.</p>
            </div>
            <div class="features-grid">
                <div class="feature-card"><div class="feature-icon">📁</div><h3>Documentos centralizados</h3><p>Organize arquivos, anexos e solicitações por cliente, status e responsável.</p></div>
                <div class="feature-card"><div class="feature-icon">✅</div><h3>Aprovações e checklist</h3><p>Padronize processos internos e acompanhe etapas sem depender de planilhas.</p></div>
                <div class="feature-card"><div class="feature-icon">🖊️</div><h3>Assinatura digital paga</h3><p>Clicksign fica fora do gratuito para proteger seu custo e entra como recurso premium.</p></div>
                <div class="feature-card"><div class="feature-icon">🤖</div><h3>IA somente nos pagos</h3><p>Interações de IA são recurso premium, evitando abuso no plano gratuito.</p></div>
                <div class="feature-card"><div class="feature-icon">🧾</div><h3>Histórico e auditoria</h3><p>Tenha rastreabilidade das ações importantes da equipe e dos clientes.</p></div>
                <div class="feature-card"><div class="feature-icon">📊</div><h3>Indicadores operacionais</h3><p>Veja pendências, atrasos, documentos recebidos e evolução da operação.</p></div>
            </div>
        </div>
    </section>

    <section class="section" id="planos">
        <div class="container">
            <div class="section-header center">
                <h2>Planos que não assustam e crescem com sua contabilidade.</h2>
                <p>Comece simples. Conforme sua operação precisar de mais volume, aumente os limites nos planos pagos.</p>
            </div>

            <div class="plans-grid">
                <article class="plan-card" data-plan-base="0">
                    <span class="tag">Comece agora</span>
                    <h3>Free</h3>
                    <div class="price">R$ 0<span>/mês</span></div>
                    <p>Para testar, organizar as primeiras solicitações e validar o uso com sua equipe.</p>
                    <div class="plan-list">
                        <div>1 usuário</div>
                        <div>20 itens por mês</div>
                        <div>20 anexos</div>
                        <div>Checklist básico</div>
                        <div>Timeline básica</div>
                        <div>Sem IA inclusa</div>
                        <div>Sem Clicksign incluso</div>
                    </div>
                    <div class="plan-total"><small>Total mensal</small><strong>R$ 0</strong></div>
                    <a class="btn btn-light" href="<?php echo e(route('empresa.cadastro.create', ['plano' => 'starter'])); ?>">Começar grátis</a>
                </article>

                <article class="plan-card highlight js-plan-card" data-plan-base="39">
                    <span class="tag">Mais fácil de começar</span>
                    <h3>Starter</h3>
                    <div class="price">R$ 39<span>/mês</span></div>
                    <p>Para pequenas contabilidades que querem sair do WhatsApp e organizar a operação.</p>
                    <div class="plan-config">
                        <div class="select-row"><label>Usuários</label><select class="js-plan-select"><option value="0">Até 3 usuários</option><option value="15">Até 5 usuários + R$ 15</option><option value="29">Até 10 usuários + R$ 29</option></select></div>
                        <div class="select-row"><label>Itens por mês</label><select class="js-plan-select"><option value="0">200 itens inclusos</option><option value="19">500 itens + R$ 19</option><option value="39">1000 itens + R$ 39</option></select></div>
                        <div class="select-row"><label>Anexos</label><select class="js-plan-select"><option value="0">100 anexos inclusos</option><option value="15">500 anexos + R$ 15</option><option value="29">1000 anexos + R$ 29</option></select></div>
                    </div>
                    <div class="plan-list"><div>Dashboard operacional</div><div>Notificações essenciais</div><div>Controle de pendências</div><div>Sem IA inclusa</div><div>Sem Clicksign incluso</div></div>
                    <div class="plan-total"><small>Total mensal estimado</small><strong class="js-plan-total">R$ 39</strong></div>
                    <a class="btn btn-primary" href="<?php echo e(route('empresa.cadastro.create', ['plano' => 'profissional'])); ?>">Assinar Starter</a>
                </article>

                <article class="plan-card js-plan-card" data-plan-base="89">
                    <span class="tag">Melhor custo-benefício</span>
                    <h3>Pro</h3>
                    <div class="price">R$ 89<span>/mês</span></div>
                    <p>Para escritórios em crescimento que precisam de mais controle, relatórios e automação.</p>
                    <div class="plan-config">
                        <div class="select-row"><label>Assinaturas Clicksign</label><select class="js-plan-select"><option value="0">Sem assinaturas inclusas</option><option value="29">10 assinaturas + R$ 29</option><option value="79">50 assinaturas + R$ 79</option><option value="199">200 assinaturas + R$ 199</option></select></div>
                        <div class="select-row"><label>Interações de IA</label><select class="js-plan-select"><option value="0">Sem IA inclusa</option><option value="19">100 interações + R$ 19</option><option value="49">500 interações + R$ 49</option><option value="129">2000 interações + R$ 129</option></select></div>
                        <div class="select-row"><label>Volume operacional</label><select class="js-plan-select"><option value="0">1000 itens inclusos</option><option value="39">3000 itens + R$ 39</option><option value="79">7000 itens + R$ 79</option></select></div>
                    </div>
                    <div class="plan-list"><div>Até 10 usuários</div><div>Auditoria</div><div>Relatórios</div><div>Automações</div><div>Suporte prioritário</div></div>
                    <div class="plan-total"><small>Total mensal estimado</small><strong class="js-plan-total">R$ 89</strong></div>
                    <a class="btn btn-light" href="<?php echo e(route('empresa.cadastro.create', ['plano' => 'business'])); ?>">Escolher Pro</a>
                </article>

                <article class="plan-card js-plan-card" data-plan-base="179">
                    <span class="tag">Operações maiores</span>
                    <h3>Business</h3>
                    <div class="price">R$ 179<span>/mês</span></div>
                    <p>Para contabilidades com alto volume, múltiplas empresas e necessidade de escala.</p>
                    <div class="plan-config">
                        <div class="select-row"><label>Usuários</label><select class="js-plan-select"><option value="0">Até 10 usuários</option><option value="49">Até 30 usuários + R$ 49</option><option value="129">Até 100 usuários + R$ 129</option><option value="consulta">Sob consulta</option></select></div>
                        <div class="select-row"><label>Itens por mês</label><select class="js-plan-select"><option value="0">5000 itens inclusos</option><option value="99">10000 itens + R$ 99</option><option value="199">25000 itens + R$ 199</option><option value="consulta">Sob consulta</option></select></div>
                        <div class="select-row"><label>Assinaturas Clicksign</label><select class="js-plan-select"><option value="0">Sem assinaturas inclusas</option><option value="199">200 assinaturas + R$ 199</option><option value="499">500 assinaturas + R$ 499</option><option value="consulta">Sob consulta</option></select></div>
                    </div>
                    <div class="plan-list"><div>Multiempresa</div><div>White label</div><div>Auditoria completa</div><div>Fluxos avançados</div><div>Prioridade operacional</div></div>
                    <div class="plan-total"><small>Total mensal estimado</small><strong class="js-plan-total">R$ 179</strong></div>
                    <a class="btn btn-light" href="<?php echo e(route('empresa.cadastro.create', ['plano' => 'enterprise'])); ?>">Falar com especialista</a>
                </article>
            </div>
        </div>
    </section>

    <section class="section" id="duvidas">
        <div class="container">
            <div class="section-header">
                <h2>Dúvidas comuns antes de começar.</h2>
                <p>O objetivo é sua contabilidade testar sem risco e evoluir conforme a operação crescer.</p>
            </div>
            <div class="faq-grid">
                <div class="faq-card"><h3>O plano gratuito tem IA ou Clicksign?</h3><p>Não. IA e assinatura digital geram custo real e ficam disponíveis apenas nos planos pagos ou como adicionais.</p></div>
                <div class="faq-card"><h3>Posso começar pequeno?</h3><p>Sim. O plano gratuito e o Starter foram pensados para começar sem assustar e validar valor rapidamente.</p></div>
                <div class="faq-card"><h3>Posso aumentar limites depois?</h3><p>Sim. Os planos pagos permitem ampliar usuários, itens, anexos, IA e assinaturas conforme necessidade.</p></div>
                <div class="faq-card"><h3>E se minha contabilidade tiver muito volume?</h3><p>No Business existe a opção sob consulta para montar um pacote adequado à operação.</p></div>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="cta-box">
                <h2>Organize sua contabilidade com mais controle e menos retrabalho.</h2>
                <p>Comece gratuitamente e centralize documentos, aprovações, pendências e histórico operacional dos seus clientes.</p>
                <a class="btn btn-primary" href="<?php echo e(route('empresa.cadastro.create')); ?>">Criar conta grátis</a>
                <a class="btn btn-light" href="#planos">Comparar planos</a>
            </div>
        </div>
    </section>
</main>

<script>
    document.querySelectorAll('.js-plan-card').forEach((card) => {
        const base = Number(card.dataset.planBase || 0);
        const totalEl = card.querySelector('.js-plan-total');
        const selects = card.querySelectorAll('.js-plan-select');

        const updateTotal = () => {
            let total = base;
            let consulta = false;

            selects.forEach((select) => {
                if (select.value === 'consulta') {
                    consulta = true;
                    return;
                }

                total += Number(select.value || 0);
            });

            if (!totalEl) {
                return;
            }

            totalEl.textContent = consulta
                ? 'Sob consulta'
                : total.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL', maximumFractionDigits: 0 });
        };

        selects.forEach((select) => select.addEventListener('change', updateTotal));
        updateTotal();
    });
</script>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\prazzu\resources\views\landing-contabilidade.blade.php ENDPATH**/ ?>