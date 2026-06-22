<x-filament-panels::page>
    <style>
        .plans-shell{display:flex;flex-direction:column;gap:1.25rem}.plans-hero{border-radius:1.5rem;padding:1.5rem;background:linear-gradient(135deg,rgba(15,23,42,.96),rgba(30,41,59,.9));color:white;box-shadow:0 18px 45px rgba(15,23,42,.18)}.plans-hero h2{font-size:1.55rem;font-weight:800;margin:0}.plans-hero p{margin:.4rem 0 0;color:rgba(255,255,255,.78);max-width:58rem}.plans-kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:.75rem}.plans-kpi,.plan-card,.company-card,.plans-panel{border:1px solid rgba(148,163,184,.24);background:rgba(255,255,255,.88);border-radius:1.25rem;padding:1rem;box-shadow:0 12px 30px rgba(15,23,42,.06)}.dark .plans-kpi,.dark .plan-card,.dark .company-card,.dark .plans-panel{background:rgba(15,23,42,.78);border-color:rgba(148,163,184,.18)}.plans-kpi span,.plan-card span,.company-card small,.plans-muted{font-size:.78rem;color:rgb(100,116,139)}.dark .plans-kpi span,.dark .plan-card span,.dark .company-card small,.dark .plans-muted{color:rgb(148,163,184)}.plans-kpi strong{display:block;font-size:1.5rem;margin-top:.2rem}.plans-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:1rem}.plan-card{display:flex;flex-direction:column;gap:.75rem}.plan-card h3{font-size:1.1rem;font-weight:800;margin:0}.plan-limits{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.5rem}.plan-limit{border-radius:.9rem;background:rgba(148,163,184,.12);padding:.65rem}.plan-limit b{display:block;font-size:.95rem}.plan-features{display:flex;flex-wrap:wrap;gap:.35rem}.plans-pill{display:inline-flex;align-items:center;border-radius:999px;padding:.25rem .55rem;font-size:.72rem;font-weight:700;background:rgba(59,130,246,.12);color:rgb(37,99,235)}.plans-pill.warning{background:rgba(245,158,11,.14);color:rgb(180,83,9)}.plans-pill.success{background:rgba(34,197,94,.14);color:rgb(22,101,52)}.plans-toolbar{display:flex;gap:.75rem;align-items:center;justify-content:space-between}.plans-toolbar input,.plans-toolbar select,.company-card select{border:1px solid rgba(148,163,184,.35);border-radius:.85rem;padding:.55rem .7rem;background:white;min-width:12rem}.dark .plans-toolbar input,.dark .plans-toolbar select,.dark .company-card select{background:rgba(15,23,42,.95)}.companies-list{display:flex;flex-direction:column;gap:.75rem}.company-card{display:grid;grid-template-columns:minmax(0,1.6fr) minmax(0,1.1fr) minmax(0,1.1fr) auto;gap:1rem;align-items:center}.company-card h3{font-weight:800;margin:0}.company-metrics{display:flex;flex-direction:column;gap:.35rem}.progress{height:.5rem;background:rgba(148,163,184,.18);border-radius:999px;overflow:hidden}.progress span{display:block;height:100%;background:currentColor}.progress.good{color:rgb(34,197,94)}.progress.warn{color:rgb(245,158,11)}.progress.danger{color:rgb(239,68,68)}.plans-empty{text-align:center;border:1px dashed rgba(148,163,184,.45);border-radius:1rem;padding:2rem;color:rgb(100,116,139)}@media(max-width:1000px){.plans-kpis,.plans-grid{grid-template-columns:1fr 1fr}.company-card{grid-template-columns:1fr}}@media(max-width:640px){.plans-kpis,.plans-grid{grid-template-columns:1fr}.plans-toolbar{align-items:stretch;flex-direction:column}.plans-toolbar input,.plans-toolbar select{width:100%}}
    </style>

    <div class="plans-shell">
        <section class="plans-hero">
            <h2>Planos internos, limites e recursos liberados</h2>
            <p>Controle Starter, Professional e Enterprise em um único lugar, sincronizando usuários, armazenamento, itens operacionais, IA e recursos habilitados por plano.</p>
        </section>

        @unless($temColunaArmazenamento)
            <div class="plans-panel">
                <strong>Banco incompleto para armazenamento.</strong>
                <p class="plans-muted">Execute o SQL manual entregue no pacote para garantir a coluna <code>empresas.limite_armazenamento_mb</code>.</p>
            </div>
        @endunless

        <section class="plans-kpis">
            <article class="plans-kpi"><span>Empresas</span><strong>{{ number_format($resumo['empresas'] ?? 0, 0, ',', '.') }}</strong></article>
            <article class="plans-kpi"><span>Starter</span><strong>{{ number_format($resumo['starter'] ?? 0, 0, ',', '.') }}</strong></article>
            <article class="plans-kpi"><span>Professional</span><strong>{{ number_format($resumo['profissional'] ?? 0, 0, ',', '.') }}</strong></article>
            <article class="plans-kpi"><span>Enterprise</span><strong>{{ number_format($resumo['enterprise'] ?? 0, 0, ',', '.') }}</strong></article>
        </section>

        <section class="plans-grid">
            @foreach($planos as $plano)
                <article class="plan-card">
                    <div>
                        <span>{{ $plano['codigo'] }}</span>
                        <h3>{{ $plano['nome'] }}</h3>
                        <p class="plans-muted">{{ $plano['descricao'] }}</p>
                    </div>
                    <div class="plan-limits">
                        <div class="plan-limit"><span>Usuários</span><b>{{ number_format($plano['usuarios'], 0, ',', '.') }}</b></div>
                        <div class="plan-limit"><span>Armazenamento</span><b>{{ $plano['armazenamento'] }}</b></div>
                        <div class="plan-limit"><span>Itens</span><b>{{ number_format($plano['itens'], 0, ',', '.') }}</b></div>
                        <div class="plan-limit"><span>IA</span><b>{{ number_format($plano['ia'], 0, ',', '.') }}</b></div>
                    </div>
                    <div class="plan-features">
                        @foreach(array_slice($plano['features'], 0, 8) as $feature)
                            <span class="plans-pill">{{ str_replace('_', ' ', $feature) }}</span>
                        @endforeach
                        @if(count($plano['features']) > 8)
                            <span class="plans-pill warning">+{{ count($plano['features']) - 8 }} recursos</span>
                        @endif
                    </div>
                </article>
            @endforeach
        </section>

        <section class="plans-panel">
            <div class="plans-toolbar">
                <div>
                    <h3 style="font-weight:800;margin:0">Empresas e plano atual</h3>
                    <p class="plans-muted" style="margin:.25rem 0 0">Alterar o plano aqui sincroniza os limites da empresa e a assinatura atual, quando existir.</p>
                </div>
                <div style="display:flex;gap:.5rem;flex-wrap:wrap">
                    <input type="search" wire:model.live.debounce.400ms="search" placeholder="Buscar empresa, e-mail ou CNPJ">
                    <select wire:model.live="planFilter">
                        <option value="todos">Todos os planos</option>
                        @foreach($planOptions as $codigo => $nome)
                            <option value="{{ $codigo }}">{{ $nome }}</option>
                        @endforeach
                    </select>
                    <button type="button" class="plans-pill" wire:click="clearFilters">Limpar</button>
                </div>
            </div>

            <div class="companies-list" style="margin-top:1rem">
                @forelse($empresas as $empresa)
                    @php
                        $storageTone = $empresa['storage_percentual'] >= 90 ? 'danger' : ($empresa['storage_percentual'] >= 75 ? 'warn' : 'good');
                    @endphp
                    <article class="company-card">
                        <div>
                            <h3>{{ $empresa['nome'] }}</h3>
                            <small>{{ $empresa['email'] ?: 'Sem e-mail cadastrado' }} · Assinatura: {{ $empresa['assinatura_status'] ?: 'sem assinatura' }}</small>
                            <div style="margin-top:.4rem"><span class="plans-pill success">{{ $empresa['plano_nome'] }}</span></div>
                        </div>
                        <div class="company-metrics">
                            <small>Usuários: {{ $empresa['usuarios_usados'] }} de {{ number_format($empresa['usuarios_limite'], 0, ',', '.') }}</small>
                            <small>Itens: {{ $empresa['itens_usados'] }} de {{ number_format($empresa['itens_limite'], 0, ',', '.') }}</small>
                            <small>IA mensal: {{ number_format($empresa['ia_limite'], 0, ',', '.') }}</small>
                        </div>
                        <div class="company-metrics">
                            <small>Armazenamento: {{ $empresa['storage_usado'] }} de {{ $empresa['storage_limite'] }} · {{ $empresa['storage_percentual'] }}%</small>
                            <div class="progress {{ $storageTone }}"><span style="width: {{ min(100, $empresa['storage_percentual']) }}%"></span></div>
                        </div>
                        <div>
                            <select wire:change="updateEmpresaPlano({{ $empresa['id'] }}, $event.target.value)">
                                @foreach($planOptions as $codigo => $nome)
                                    <option value="{{ $codigo }}" @selected($empresa['plano'] === $codigo)>{{ $nome }}</option>
                                @endforeach
                            </select>
                        </div>
                    </article>
                @empty
                    <div class="plans-empty">Nenhuma empresa encontrada com os filtros atuais.</div>
                @endforelse
            </div>
        </section>
    </div>
</x-filament-panels::page>
