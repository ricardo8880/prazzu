@php
    $empresa = $detail['empresa'] ?? [];
    $storage = $detail['armazenamento'] ?? [];
    $tarefas = $detail['tarefas'] ?? [];
    $atendimentos = $detail['atendimentos'] ?? [];
    $portal = $detail['portal'] ?? [];
    $financeiro = $detail['financeiro'] ?? [];
    $contratos = $detail['contratos'] ?? [];
    $validade = $detail['validade'] ?? [];
    $governanca = $detail['governanca'] ?? [];
    $acoes = collect($detail['acoes'] ?? [])->filter(fn ($action) => filled($action['url'] ?? null));
    $recomendacoes = $detail['recomendacoes'] ?? [];
    $pesados = $storage['pesados'] ?? [];
@endphp

<style>
    .storage-client-modal { display: grid; gap: 1rem; }
    .storage-client-hero { display: grid; grid-template-columns: minmax(0, 1.2fr) minmax(260px, .8fr); gap: 1rem; align-items: stretch; }
    .storage-client-card, .storage-client-section, .storage-client-kpi { border: 1px solid rgba(148, 163, 184, .22); border-radius: 20px; padding: 1rem; background: rgba(248, 250, 252, .76); }
    .dark .storage-client-card, .dark .storage-client-section, .dark .storage-client-kpi { background: rgba(15, 23, 42, .58); border-color: rgba(148, 163, 184, .16); }
    .storage-client-card h3, .storage-client-section h4 { margin: 0; font-weight: 900; color: rgb(15, 23, 42); }
    .dark .storage-client-card h3, .dark .storage-client-section h4 { color: white; }
    .storage-client-card p { margin: .35rem 0 0; color: rgb(100, 116, 139); font-size: .86rem; line-height: 1.5; }
    .storage-client-kpis { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: .75rem; }
    .storage-client-kpi span { display: block; font-size: .7rem; text-transform: uppercase; letter-spacing: .08em; font-weight: 850; color: rgb(100, 116, 139); }
    .storage-client-kpi strong { display: block; margin-top: .3rem; font-size: 1.25rem; font-weight: 950; color: rgb(15, 23, 42); }
    .dark .storage-client-kpi strong { color: white; }
    .storage-client-progress { height: .65rem; border-radius: 999px; background: rgba(148, 163, 184, .20); overflow: hidden; margin-top: .75rem; color: rgb(34, 197, 94); }
    .storage-client-progress.warning { color: rgb(245, 158, 11); }
    .storage-client-progress.danger { color: rgb(239, 68, 68); }
    .storage-client-progress span { display:block; height:100%; border-radius: inherit; max-width:100%; background: currentColor; }
    .storage-client-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: .75rem; }
    .storage-client-section h4 { margin-bottom: .65rem; }
    .storage-client-line { display: flex; justify-content: space-between; gap: .75rem; padding: .38rem 0; border-bottom: 1px solid rgba(148, 163, 184, .12); font-size: .84rem; color: rgb(100, 116, 139); }
    .storage-client-line:last-child { border-bottom: 0; }
    .storage-client-line strong { color: rgb(15, 23, 42); font-weight: 900; text-align: right; }
    .dark .storage-client-line strong { color: white; }
    .storage-client-recos { display: grid; gap: .5rem; }
    .storage-client-reco { border-radius: 14px; padding: .65rem .75rem; font-size: .84rem; font-weight: 700; background: rgba(148, 163, 184, .13); color: rgb(71, 85, 105); }
    .storage-client-reco.success { background: rgba(34, 197, 94, .12); color: rgb(21, 128, 61); }
    .storage-client-reco.warning { background: rgba(245, 158, 11, .14); color: rgb(180, 83, 9); }
    .storage-client-reco.danger { background: rgba(239, 68, 68, .13); color: rgb(185, 28, 28); }
    .storage-client-actions { display: flex; flex-wrap: wrap; gap: .5rem; }
    .storage-client-action { display: inline-flex; align-items: center; justify-content: center; border-radius: 999px; padding: .5rem .8rem; font-size: .78rem; font-weight: 900; text-decoration: none; border: 1px solid rgba(124, 58, 237, .20); background: rgba(124, 58, 237, .10); color: rgb(109, 40, 217); }
    .storage-client-action.primary { background: rgb(124, 58, 237); color: white; }
    .storage-client-files { display: grid; gap: .45rem; }
    .storage-client-file { display: flex; justify-content: space-between; gap: .75rem; border-radius: 14px; padding: .6rem .7rem; background: rgba(248, 250, 252, .85); font-size: .82rem; color: rgb(100, 116, 139); }
    .dark .storage-client-file { background: rgba(15, 23, 42, .48); }
    .storage-client-file strong { color: rgb(15, 23, 42); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .dark .storage-client-file strong { color: white; }
    @media (max-width: 900px) { .storage-client-hero, .storage-client-grid { grid-template-columns: 1fr; } .storage-client-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
</style>

<div class="storage-client-modal">
    <section class="storage-client-hero">
        <article class="storage-client-card">
            <h3>{{ $empresa['nome'] ?? 'Cliente' }}</h3>
            <p>{{ $empresa['razao_social'] ?? 'Razão social não informada' }} @if(! empty($empresa['cnpj'])) · CNPJ {{ $empresa['cnpj'] }} @endif</p>
            <p>Status: <strong>{{ ucfirst((string) ($empresa['status'] ?? 'não informado')) }}</strong> · Plano: <strong>{{ $empresa['plano'] ?? 'sem plano' }}</strong> · Desde: <strong>{{ $empresa['desde'] ?? 'Não informado' }}</strong></p>
            <p>Responsável: <strong>{{ $empresa['responsavel'] ?: 'Não informado' }}</strong> · Portal: <strong>{{ ($empresa['portal_ativo'] ?? false) ? 'Ativo' : 'Inativo' }}</strong></p>
        </article>
        <article class="storage-client-card">
            <h3>{{ $storage['total_formatado'] ?? '0 B' }} usados</h3>
            <p>Limite: {{ $storage['limite_formatado'] ?? '0 B' }} · {{ $storage['percentual'] ?? 0 }}% utilizado</p>
            <div class="storage-client-progress {{ $storage['tom'] ?? 'success' }}"><span style="width: {{ min(100, (int) ($storage['percentual'] ?? 0)) }}%"></span></div>
            <p>Espaço recuperável estimado: <strong>{{ $storage['recuperavel_formatado'] ?? '0 B' }}</strong></p>
        </article>
    </section>

    <section class="storage-client-kpis">
        <article class="storage-client-kpi"><span>Arquivos</span><strong>{{ number_format((int) ($storage['arquivos'] ?? 0), 0, ',', '.') }}</strong></article>
        <article class="storage-client-kpi"><span>Expirados</span><strong>{{ number_format((int) ($storage['expirados'] ?? 0), 0, ',', '.') }}</strong></article>
        <article class="storage-client-kpi"><span>Tarefas abertas</span><strong>{{ number_format((int) ($tarefas['abertas'] ?? 0), 0, ',', '.') }}</strong></article>
        <article class="storage-client-kpi"><span>Atendimentos abertos</span><strong>{{ number_format((int) ($atendimentos['abertos'] ?? 0), 0, ',', '.') }}</strong></article>
    </section>

    <section class="storage-client-grid">
        <article class="storage-client-section"><h4>Documentos e armazenamento</h4><div class="storage-client-line"><span>Maior arquivo</span><strong>{{ $storage['maior_arquivo']['nome'] ?? 'Não identificado' }}</strong></div><div class="storage-client-line"><span>Tamanho do maior</span><strong>{{ $storage['maior_arquivo']['tamanho_formatado'] ?? '0 B' }}</strong></div><div class="storage-client-line"><span>Vencidos/antigos</span><strong>{{ number_format((int) ($storage['expirados'] ?? 0), 0, ',', '.') }}</strong></div><div class="storage-client-line"><span>Portal documentos</span><strong>{{ number_format((int) ($portal['documentos'] ?? 0), 0, ',', '.') }}</strong></div></article>
        <article class="storage-client-section"><h4>Tarefas, SLA e prazos</h4><div class="storage-client-line"><span>Abertas</span><strong>{{ number_format((int) ($tarefas['abertas'] ?? 0), 0, ',', '.') }}</strong></div><div class="storage-client-line"><span>Atrasadas</span><strong>{{ number_format((int) ($tarefas['atrasadas'] ?? 0), 0, ',', '.') }}</strong></div><div class="storage-client-line"><span>Críticas</span><strong>{{ number_format((int) ($tarefas['criticas'] ?? 0), 0, ',', '.') }}</strong></div><div class="storage-client-line"><span>SLA vencido</span><strong>{{ number_format((int) ($tarefas['slaVencido'] ?? 0), 0, ',', '.') }}</strong></div></article>
        <article class="storage-client-section"><h4>Atendimentos e portal</h4><div class="storage-client-line"><span>Atendimentos críticos</span><strong>{{ number_format((int) ($atendimentos['criticos'] ?? 0), 0, ',', '.') }}</strong></div><div class="storage-client-line"><span>Aguardando cliente</span><strong>{{ number_format((int) ($atendimentos['aguardando_cliente'] ?? 0), 0, ',', '.') }}</strong></div><div class="storage-client-line"><span>Solicitações portal</span><strong>{{ number_format((int) ($portal['solicitacoes_abertas'] ?? 0), 0, ',', '.') }}</strong></div><div class="storage-client-line"><span>Último contato</span><strong>{{ $atendimentos['ultimo_contato'] ?? $portal['ultima_mensagem'] ?? 'Não informado' }}</strong></div></article>
        <article class="storage-client-section"><h4>Financeiro e cobranças</h4><div class="storage-client-line"><span>Cobranças abertas</span><strong>{{ number_format((int) ($financeiro['abertas'] ?? 0), 0, ',', '.') }}</strong></div><div class="storage-client-line"><span>Cobranças vencidas</span><strong>{{ number_format((int) ($financeiro['vencidas'] ?? 0), 0, ',', '.') }}</strong></div><div class="storage-client-line"><span>Valor vencido</span><strong>{{ $financeiro['vencido_formatado'] ?? 'R$ 0,00' }}</strong></div><div class="storage-client-line"><span>Próximo vencimento</span><strong>{{ $financeiro['proximo_vencimento'] ?? 'Sem vencimento' }}</strong></div></article>
        <article class="storage-client-section"><h4>Contratos, validades e governança</h4><div class="storage-client-line"><span>Contratos ativos</span><strong>{{ number_format((int) ($contratos['ativos'] ?? 0), 0, ',', '.') }}</strong></div><div class="storage-client-line"><span>Contratos vencendo</span><strong>{{ number_format((int) ($contratos['vencendo'] ?? 0), 0, ',', '.') }}</strong></div><div class="storage-client-line"><span>Validades vencidas</span><strong>{{ number_format((int) ($validade['vencidos'] ?? 0), 0, ',', '.') }}</strong></div><div class="storage-client-line"><span>Aprovações pendentes</span><strong>{{ number_format((int) ($governanca['aprovacoes_pendentes'] ?? 0), 0, ',', '.') }}</strong></div></article>
        <article class="storage-client-section"><h4>Recomendação operacional</h4><div class="storage-client-recos">@foreach($recomendacoes as $recomendacao)<div class="storage-client-reco {{ $recomendacao['tom'] ?? 'success' }}">{{ $recomendacao['texto'] ?? '' }}</div>@endforeach</div></article>
    </section>

    @if(count($pesados) > 0)
        <section class="storage-client-section"><h4>Arquivos que explicam o consumo</h4><div class="storage-client-files">@foreach($pesados as $arquivo)<div class="storage-client-file"><strong title="{{ $arquivo['nome'] ?? 'Arquivo' }}">{{ $arquivo['nome'] ?? 'Arquivo' }}</strong><span>{{ $arquivo['tamanho_formatado'] ?? '0 B' }} · {{ $arquivo['origem'] ?? 'Origem' }}</span></div>@endforeach</div></section>
    @endif

    @if($acoes->isNotEmpty())
        <section class="storage-client-section"><h4>Continuar análise</h4><div class="storage-client-actions">@foreach($acoes as $acao)<a href="{{ $acao['url'] }}" class="storage-client-action {{ $acao['style'] ?? 'secondary' }}">{{ $acao['label'] ?? 'Abrir' }}</a>@endforeach</div></section>
    @endif
</div>
