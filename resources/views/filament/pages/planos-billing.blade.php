<x-filament-panels::page>
    <style>
        .pb-shell{display:flex;flex-direction:column;gap:1.25rem}.pb-hero{border-radius:1.5rem;padding:1.5rem;background:linear-gradient(135deg,rgba(15,23,42,.96),rgba(30,41,59,.9));color:#fff;box-shadow:0 18px 45px rgba(15,23,42,.18)}.pb-hero h2{font-size:1.55rem;font-weight:800;margin:0}.pb-hero p{margin:.4rem 0 0;color:rgba(255,255,255,.78);max-width:64rem}.pb-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:.75rem}.pb-two{display:grid;grid-template-columns:1fr 1fr;gap:1rem}.pb-three{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:1rem}.pb-card,.pb-panel{border:1px solid rgba(148,163,184,.24);background:rgba(255,255,255,.9);border-radius:1.25rem;padding:1rem;box-shadow:0 12px 30px rgba(15,23,42,.06)}.dark .pb-card,.dark .pb-panel{background:rgba(15,23,42,.78);border-color:rgba(148,163,184,.18)}.pb-muted,.pb-card small{font-size:.78rem;color:rgb(100,116,139)}.dark .pb-muted,.dark .pb-card small{color:rgb(148,163,184)}.pb-kpi strong{display:block;font-size:1.35rem;margin-top:.2rem}.pb-card h3,.pb-row h4{font-weight:800;margin:0}.pb-pill{display:inline-flex;border-radius:999px;padding:.25rem .55rem;font-size:.72rem;font-weight:700;background:rgba(59,130,246,.12);color:rgb(37,99,235)}.pb-pill.success{background:rgba(34,197,94,.14);color:rgb(22,101,52)}.pb-pill.warning{background:rgba(245,158,11,.14);color:rgb(180,83,9)}.pb-pill.danger{background:rgba(239,68,68,.14);color:rgb(185,28,28)}.pb-pill.muted{background:rgba(100,116,139,.12);color:rgb(71,85,105)}.pb-toolbar{display:flex;justify-content:space-between;gap:.75rem;align-items:center}.pb-toolbar input,.pb-toolbar select{border:1px solid rgba(148,163,184,.35);border-radius:.85rem;padding:.55rem .7rem;background:white;min-width:12rem}.dark .pb-toolbar input,.dark .pb-toolbar select{background:rgba(15,23,42,.95)}.pb-row{display:grid;grid-template-columns:minmax(0,1.2fr) auto auto;gap:.75rem;align-items:center;border-top:1px solid rgba(148,163,184,.18);padding-top:.75rem;margin-top:.75rem}.pb-empty{text-align:center;border:1px dashed rgba(148,163,184,.45);border-radius:1rem;padding:1.5rem;color:rgb(100,116,139)}@media(max-width:1100px){.pb-grid,.pb-three,.pb-two{grid-template-columns:1fr 1fr}.pb-row{grid-template-columns:1fr}}@media(max-width:720px){.pb-grid,.pb-three,.pb-two{grid-template-columns:1fr}.pb-toolbar{align-items:stretch;flex-direction:column}.pb-toolbar input,.pb-toolbar select{width:100%}}
    </style>

    <div class="pb-shell">
        <section class="pb-hero">
            <h2>Assinatura</h2>
            <p>Acompanhe o plano atual, pagamentos e limites da conta quando precisar consultar informações comerciais.</p>
        </section>

        @unless($temBilling)
            <section class="pb-panel"><strong>Cobrança não configurada neste ambiente.</strong><p class="pb-muted">As informações de assinatura e pagamentos ainda não estão disponíveis neste ambiente.</p></section>
        @endunless

        <section class="pb-grid">
            <article class="pb-card pb-kpi"><small>Empresas</small><strong>{{ number_format($resumo['empresas'] ?? 0, 0, ',', '.') }}</strong></article>
            <article class="pb-card pb-kpi"><small>MRR previsto</small><strong>{{ 'R$ ' . number_format($resumo['mrr_previsto'] ?? 0, 2, ',', '.') }}</strong></article>
            <article class="pb-card pb-kpi"><small>Pagamentos abertos</small><strong>{{ number_format($resumo['pagamentos_abertos'] ?? 0, 0, ',', '.') }}</strong></article>
            <article class="pb-card pb-kpi"><small>Valor vencido</small><strong>{{ 'R$ ' . number_format($resumo['valor_vencido'] ?? 0, 2, ',', '.') }}</strong></article>
            <article class="pb-card pb-kpi"><small>Assinaturas ativas</small><strong>{{ number_format($resumo['assinaturas_ativas'] ?? 0, 0, ',', '.') }}</strong></article>
            <article class="pb-card pb-kpi"><small>Pagamentos vencidos</small><strong>{{ number_format($resumo['pagamentos_vencidos'] ?? 0, 0, ',', '.') }}</strong></article>
            <article class="pb-card pb-kpi"><small>Bloqueios ativos</small><strong>{{ number_format($resumo['bloqueios_ativos'] ?? 0, 0, ',', '.') }}</strong></article>
            <article class="pb-card pb-kpi"><small>Regras ativas</small><strong>{{ number_format($resumo['regras_ativas'] ?? 0, 0, ',', '.') }}</strong></article>
        </section>

        <section class="pb-panel pb-toolbar">
            <div><h3 style="font-weight:800;margin:0">Filtros</h3><p class="pb-muted" style="margin:.25rem 0 0">Busque empresas, assinaturas ou pagamentos.</p></div>
            <div style="display:flex;gap:.5rem;flex-wrap:wrap">
                <input type="search" wire:model.live.debounce.400ms="search" placeholder="Buscar empresa, gateway ou cobrança">
                <select wire:model.live="planFilter"><option value="todos">Todos os planos</option>@foreach($planOptions as $codigo => $nome)<option value="{{ $codigo }}">{{ $nome }}</option>@endforeach</select>
                <button type="button" class="pb-pill" wire:click="clearFilters">Limpar</button>
            </div>
        </section>

        <section class="pb-three">
            @foreach($planos as $plano)
                <article class="pb-card">
                    <small>{{ $plano['codigo'] }}</small><h3>{{ $plano['nome'] }}</h3><p><strong>{{ $plano['preco'] }}</strong></p>
                    <p class="pb-muted">Usuários: {{ number_format($plano['usuarios'], 0, ',', '.') }} · Itens: {{ number_format($plano['itens'], 0, ',', '.') }} · Armazenamento: {{ $plano['armazenamento'] }} · IA: {{ number_format($plano['ia'], 0, ',', '.') }}</p>
                    <div style="display:flex;gap:.35rem;flex-wrap:wrap">@foreach($plano['features'] as $feature)<span class="pb-pill">{{ str_replace('_', ' ', $feature) }}</span>@endforeach</div>
                </article>
            @endforeach
        </section>

        <section class="pb-panel">
            <h3>Empresas e plano atual</h3>
            @forelse($empresas as $empresa)
                <div class="pb-row"><div><h4>{{ $empresa['nome'] }}</h4><small>{{ $empresa['email'] ?: 'Sem e-mail' }} · Usuários {{ $empresa['usuarios'] }} · Itens {{ $empresa['itens'] }} · Armazenamento {{ $empresa['armazenamento'] }}</small></div><strong>{{ $empresa['plano'] }}</strong><span class="pb-pill {{ $empresa['status_tone'] }}">{{ $empresa['status'] }}</span></div>
            @empty
                <div class="pb-empty">Nenhuma empresa encontrada.</div>
            @endforelse
        </section>

        <section class="pb-two">
            <article class="pb-card"><h3>Assinaturas</h3><p class="pb-muted">Plano/contrato principal por empresa.</p>@forelse($assinaturas as $assinatura)<div class="pb-row"><div><h4>{{ $assinatura['empresa'] }}</h4><small>{{ $assinatura['plano'] }} · {{ $assinatura['ciclo'] }} · {{ $assinatura['gateway'] }}</small></div><div><strong>{{ $assinatura['valor'] }}</strong><br><small>Vence {{ $assinatura['vencimento'] }}</small></div><span class="pb-pill {{ $assinatura['tone'] }}">{{ $assinatura['status'] }}</span></div>@empty<div class="pb-empty">Nenhuma assinatura encontrada.</div>@endforelse</article>
            <article class="pb-card"><h3>Pagamentos recentes</h3><p class="pb-muted">Cobranças abertas, vencidas ou pagas.</p>@forelse($pagamentos as $pagamento)<div class="pb-row"><div><h4>{{ $pagamento['empresa'] }}</h4><small>{{ $pagamento['plano'] }} · {{ $pagamento['tipo'] }} · venc. {{ $pagamento['vencimento'] }}</small></div><div><strong>{{ $pagamento['valor'] }}</strong><br><small>Pago {{ $pagamento['pago_em'] }}</small></div><span class="pb-pill {{ $pagamento['tone'] }}">{{ $pagamento['status'] }}</span></div>@empty<div class="pb-empty">Nenhum pagamento encontrado.</div>@endforelse</article>
        </section>

        <section class="pb-two">
            <article class="pb-card"><h3>Bloqueios de cobrança</h3><p class="pb-muted">Empresas com restrição de acesso por cobrança.</p>@forelse($bloqueios as $bloqueio)<div class="pb-row"><div><h4>{{ $bloqueio['empresa'] }}</h4><small>{{ $bloqueio['reason'] }}</small></div><strong>{{ $bloqueio['locked_at'] }}</strong><span class="pb-pill danger">bloqueado</span></div>@empty<div class="pb-empty">Nenhum bloqueio ativo.</div>@endforelse</article>
            <article class="pb-card"><h3>Regras de cobrança</h3><p class="pb-muted">Ações automáticas após vencimento.</p>@forelse($regras as $regra)<div class="pb-row"><div><h4>{{ $regra['name'] }}</h4><small>{{ $regra['message'] ?: 'Sem mensagem personalizada' }}</small></div><strong>{{ $regra['days'] }} dia(s)</strong><span class="pb-pill {{ $regra['active'] ? 'success' : 'muted' }}">{{ $regra['action'] }}</span></div>@empty<div class="pb-empty">Nenhuma regra cadastrada.</div>@endforelse</article>
        </section>
    </div>
</x-filament-panels::page>
