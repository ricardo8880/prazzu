<x-filament-panels::page>
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
