<x-filament-panels::page>
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
            <div><h3 class="pb-filter-title">Filtros</h3><p class="pb-muted pb-filter-subtitle">Busque empresas, assinaturas ou pagamentos.</p></div>
            <div class="pb-filter-controls">
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
                    <div class="pb-feature-list">@foreach($plano['features'] as $feature)<span class="pb-pill">{{ str_replace('_', ' ', $feature) }}</span>@endforeach</div>
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
