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
