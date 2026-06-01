<x-filament-panels::page>
    @php
        $metrics = $data['metrics'] ?? [];
        $emptyMessage = $data['emptyMessage'] ?? null;
    @endphp

    <style>
        .fin-suite { display: grid; gap: 1rem; }
        .fin-hero { border: 1px solid rgba(148, 163, 184, .28); background: linear-gradient(135deg, rgba(15, 23, 42, .96), rgba(30, 41, 59, .94)); color: #fff; border-radius: 24px; padding: 1.25rem; display: flex; justify-content: space-between; gap: 1rem; align-items: flex-start; box-shadow: 0 18px 45px rgba(15, 23, 42, .12); }
        .fin-hero h1 { font-size: clamp(1.35rem, 2vw, 2.05rem); font-weight: 800; letter-spacing: -.03em; margin: 0 0 .35rem; }
        .fin-hero p { margin: 0; color: rgba(226, 232, 240, .86); max-width: 760px; }
        .fin-hero-badge { border: 1px solid rgba(255,255,255,.16); background: rgba(255,255,255,.08); padding: .55rem .75rem; border-radius: 999px; white-space: nowrap; font-size: .82rem; color: rgba(255,255,255,.9); }
        .fin-toolbar { display: grid; grid-template-columns: minmax(220px, 1fr) auto; gap: .75rem; align-items: center; }
        .fin-toolbar input, .fin-toolbar select { width: 100%; border-radius: 16px; border: 1px solid rgba(148,163,184,.32); background: rgba(255,255,255,.96); padding: .78rem .95rem; outline: none; }
        .dark .fin-toolbar input, .dark .fin-toolbar select { background: rgba(15,23,42,.9); }
        .fin-selects { display: flex; gap: .6rem; align-items: center; }
        .fin-metrics { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: .85rem; }
        .fin-card { border: 1px solid rgba(148,163,184,.24); background: rgba(255,255,255,.94); border-radius: 22px; padding: 1rem; box-shadow: 0 10px 30px rgba(15,23,42,.06); }
        .dark .fin-card { background: rgba(15,23,42,.72); }
        .fin-metric small { display: block; color: #64748b; font-weight: 700; margin-bottom: .4rem; }
        .dark .fin-metric small { color: #94a3b8; }
        .fin-metric strong { display: block; font-size: 1.45rem; letter-spacing: -.03em; }
        .fin-metric span { display: block; color: #64748b; font-size: .82rem; margin-top: .35rem; }
        .dark .fin-metric span { color: #94a3b8; }
        .fin-tone-success { border-top: 4px solid #22c55e; } .fin-tone-warning { border-top: 4px solid #f59e0b; } .fin-tone-danger { border-top: 4px solid #ef4444; } .fin-tone-info { border-top: 4px solid #38bdf8; }
        .fin-grid-2 { display: grid; grid-template-columns: 1.35fr .9fr; gap: 1rem; }
        .fin-grid-3 { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1rem; }
        .fin-section-title { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; margin-bottom: .85rem; }
        .fin-section-title h2 { margin: 0; font-size: 1rem; font-weight: 800; letter-spacing: -.02em; }
        .fin-section-title p { margin: .2rem 0 0; color: #64748b; font-size: .84rem; }
        .dark .fin-section-title p { color: #94a3b8; }
        .fin-table { width: 100%; border-collapse: separate; border-spacing: 0 .55rem; }
        .fin-table th { color: #64748b; font-size: .72rem; text-transform: uppercase; letter-spacing: .05em; text-align: left; padding: 0 .75rem; }
        .fin-table td { background: rgba(248,250,252,.92); padding: .85rem .75rem; vertical-align: middle; border-top: 1px solid rgba(148,163,184,.18); border-bottom: 1px solid rgba(148,163,184,.18); }
        .dark .fin-table td { background: rgba(30,41,59,.72); }
        .fin-table td:first-child { border-left: 1px solid rgba(148,163,184,.18); border-radius: 16px 0 0 16px; }
        .fin-table td:last-child { border-right: 1px solid rgba(148,163,184,.18); border-radius: 0 16px 16px 0; text-align: right; }
        .fin-main { display: grid; gap: .1rem; }
        .fin-main strong { font-weight: 800; }
        .fin-main span { color: #64748b; font-size: .8rem; }
        .dark .fin-main span { color: #94a3b8; }
        .fin-pill { display: inline-flex; align-items: center; border-radius: 999px; padding: .28rem .58rem; font-size: .75rem; font-weight: 800; border: 1px solid transparent; }
        .fin-pill.success { color: #166534; background: #dcfce7; border-color: #bbf7d0; } .fin-pill.warning { color: #92400e; background: #fef3c7; border-color: #fde68a; } .fin-pill.danger { color: #991b1b; background: #fee2e2; border-color: #fecaca; } .fin-pill.info { color: #075985; background: #e0f2fe; border-color: #bae6fd; }
        .fin-btn { display: inline-flex; justify-content: center; align-items: center; gap: .35rem; border: 1px solid rgba(148,163,184,.28); background: #fff; border-radius: 999px; padding: .52rem .78rem; font-size: .82rem; font-weight: 800; cursor: pointer; transition: .15s ease; text-decoration: none; color: inherit; }
        .dark .fin-btn { background: rgba(15,23,42,.8); }
        .fin-btn:hover { transform: translateY(-1px); box-shadow: 0 10px 25px rgba(15,23,42,.1); }
        .fin-btn.primary { background: #2563eb; color: #fff; border-color: #2563eb; } .fin-btn.success { background: #16a34a; color: #fff; border-color: #16a34a; } .fin-btn.danger { background: #dc2626; color: #fff; border-color: #dc2626; } .fin-btn.warning { background: #f59e0b; color: #111827; border-color: #f59e0b; }
        .fin-list { display: grid; gap: .65rem; }
        .fin-list-row { display: flex; justify-content: space-between; gap: .75rem; align-items: center; border: 1px solid rgba(148,163,184,.2); border-radius: 18px; padding: .8rem; background: rgba(248,250,252,.75); }
        .dark .fin-list-row { background: rgba(30,41,59,.55); }
        .fin-list-row strong { display: block; font-weight: 800; } .fin-list-row span { display: block; color: #64748b; font-size: .82rem; margin-top: .15rem; } .dark .fin-list-row span { color: #94a3b8; }
        .fin-progress { height: .65rem; background: rgba(148,163,184,.22); border-radius: 999px; overflow: hidden; margin-top: .55rem; }
        .fin-progress i { display: block; height: 100%; background: linear-gradient(90deg, #2563eb, #22c55e); border-radius: inherit; }
        .fin-alert { border-left: 4px solid #38bdf8; } .fin-alert.danger { border-left-color: #ef4444; } .fin-alert.warning { border-left-color: #f59e0b; } .fin-alert.success { border-left-color: #22c55e; }
        .fin-empty { border: 1px dashed rgba(148,163,184,.45); border-radius: 20px; padding: 1.25rem; color: #64748b; text-align: center; background: rgba(248,250,252,.58); }
        .dark .fin-empty { color: #94a3b8; background: rgba(30,41,59,.45); }
        .fin-modal-backdrop { position: fixed; inset: 0; background: rgba(15,23,42,.62); z-index: 50; display: grid; place-items: center; padding: 1rem; }
        .fin-modal { width: min(760px, 100%); max-height: 88vh; overflow: auto; background: #fff; color: #0f172a; border-radius: 26px; box-shadow: 0 25px 80px rgba(15,23,42,.35); border: 1px solid rgba(148,163,184,.25); }
        .dark .fin-modal { background: #0f172a; color: #e2e8f0; }
        .fin-modal header { padding: 1.1rem 1.2rem; border-bottom: 1px solid rgba(148,163,184,.22); display: flex; justify-content: space-between; gap: 1rem; align-items: flex-start; }
        .fin-modal-body { padding: 1.2rem; display: grid; gap: 1rem; }
        .fin-details { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .75rem; }
        .fin-detail { border: 1px solid rgba(148,163,184,.22); border-radius: 16px; padding: .75rem; }
        .fin-detail small { display: block; color: #64748b; margin-bottom: .25rem; } .dark .fin-detail small { color: #94a3b8; }
        .fin-actions { display: flex; flex-wrap: wrap; gap: .55rem; }
        @media (max-width: 980px) { .fin-metrics, .fin-grid-3 { grid-template-columns: repeat(2, minmax(0,1fr)); } .fin-grid-2 { grid-template-columns: 1fr; } .fin-toolbar { grid-template-columns: 1fr; } }
        @media (max-width: 680px) { .fin-hero { display: grid; } .fin-metrics, .fin-grid-3 { grid-template-columns: 1fr; } .fin-table thead { display: none; } .fin-table, .fin-table tbody, .fin-table tr, .fin-table td { display: block; width: 100%; } .fin-table tr { border: 1px solid rgba(148,163,184,.18); border-radius: 18px; overflow: hidden; margin-bottom: .65rem; } .fin-table td, .fin-table td:first-child, .fin-table td:last-child { border: 0; border-radius: 0; text-align: left; } .fin-details { grid-template-columns: 1fr; } .fin-selects { flex-direction: column; align-items: stretch; } }
    </style>

    <div class="fin-suite">
        <section class="fin-hero">
            <div>
                <h1>{{ $title }}</h1>
                <p>{{ $subtitle }}</p>
            </div>
            <div class="fin-hero-badge">Dados reais do banco · sem exemplos estáticos</div>
        </section>

        <section class="fin-toolbar">
            <input type="search" wire:model.live.debounce.350ms="search" placeholder="Buscar cliente, e-mail, plano, assinatura ou ID de pagamento...">
            <div class="fin-selects">
                @if ($pageType === 'financeiro')
                    <select wire:model.live="period">
                        <option value="7">Últimos 7 dias</option>
                        <option value="30">Últimos 30 dias</option>
                        <option value="90">Últimos 90 dias</option>
                        <option value="180">Últimos 180 dias</option>
                        <option value="365">Últimos 365 dias</option>
                    </select>
                @elseif ($pageType === 'cobrancas')
                    <select wire:model.live="statusFilter">
                        <option value="all">Todas as cobranças</option>
                        <option value="open">Pendentes</option>
                        <option value="overdue">Vencidas</option>
                        <option value="due_soon">Vencem em 7 dias</option>
                        <option value="paid">Recebidas</option>
                    </select>
                @elseif ($pageType === 'assinaturas')
                    <select wire:model.live="statusFilter">
                        <option value="all">Todas as assinaturas</option>
                        <option value="active">Ativas</option>
                        <option value="renewal">Renovam em 15 dias</option>
                        <option value="paused">Pausadas</option>
                        <option value="canceled">Canceladas</option>
                    </select>
                @endif
            </div>
        </section>

        @if ($emptyMessage)
            <div class="fin-empty">{{ $emptyMessage }}</div>
        @endif

        @if (! empty($metrics))
            <section class="fin-metrics">
                @foreach ($metrics as $metric)
                    <article class="fin-card fin-metric fin-tone-{{ $metric['tone'] ?? 'info' }}">
                        <small>{{ $metric['label'] }}</small>
                        <strong>{{ $metric['value'] }}</strong>
                        <span>{{ $metric['hint'] }}</span>
                    </article>
                @endforeach
            </section>
        @endif

        @if ($pageType === 'cobrancas')
            <section class="fin-grid-2">
                <article class="fin-card">
                    <div class="fin-section-title">
                        <div><h2>Régua de cobranças</h2><p>Abra uma cobrança para ver dados do cliente, link e ações rápidas.</p></div>
                    </div>
                    <table class="fin-table">
                        <thead><tr><th>Cliente</th><th>Plano</th><th>Vencimento</th><th>Status</th><th>Valor</th><th></th></tr></thead>
                        <tbody>
                            @forelse (($data['payments'] ?? []) as $payment)
                                <tr wire:key="payment-{{ $payment['id'] }}">
                                    <td><div class="fin-main"><strong>{{ $payment['cliente'] }}</strong><span>{{ $payment['email'] ?: 'Sem e-mail cadastrado' }}</span></div></td>
                                    <td>{{ $payment['plano'] }}<br><span class="fin-main"><span>{{ $payment['ciclo'] }}</span></span></td>
                                    <td>{{ $payment['vencimento'] ?: '-' }}</td>
                                    <td><span class="fin-pill {{ $payment['status_tone'] }}">{{ $payment['status_label'] }}</span></td>
                                    <td><strong>{{ $payment['valor_formatado'] }}</strong></td>
                                    <td><button type="button" class="fin-btn primary" wire:click="abrirCobranca({{ $payment['id'] }})">Abrir</button></td>
                                </tr>
                            @empty
                                <tr><td colspan="6"><div class="fin-empty">Nenhuma cobrança encontrada.</div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </article>

                <aside class="fin-card">
                    <div class="fin-section-title"><div><h2>Prioridades de cobrança</h2><p>Use essa lista para saber quem contatar primeiro.</p></div></div>
                    <div class="fin-list">
                        @forelse (($data['alerts'] ?? []) as $alert)
                            <div class="fin-list-row fin-alert {{ $alert['tone'] ?? 'info' }}"><div><strong>{{ $alert['title'] }}</strong><span>{{ $alert['text'] }}</span></div></div>
                        @empty
                            <div class="fin-empty">Nenhuma cobrança vencida nos filtros atuais.</div>
                        @endforelse
                    </div>
                </aside>
            </section>
        @elseif ($pageType === 'assinaturas')
            <section class="fin-card">
                <div class="fin-section-title">
                    <div><h2>Carteira de assinaturas</h2><p>Controle plano, recorrência, próxima renovação e status sem telas repetidas.</p></div>
                </div>
                <table class="fin-table">
                    <thead><tr><th>Cliente</th><th>Plano</th><th>Recorrência</th><th>Próximo vencimento</th><th>Status</th><th>Valor</th><th></th></tr></thead>
                    <tbody>
                        @forelse (($data['subscriptions'] ?? []) as $subscription)
                            <tr wire:key="subscription-{{ $subscription['id'] }}">
                                <td><div class="fin-main"><strong>{{ $subscription['cliente'] }}</strong><span>{{ $subscription['email'] ?: 'Sem e-mail cadastrado' }}</span></div></td>
                                <td>{{ $subscription['plano'] }}</td>
                                <td>{{ $subscription['ciclo'] }}</td>
                                <td>{{ $subscription['proximo_vencimento'] ?: '-' }}</td>
                                <td><span class="fin-pill {{ $subscription['status_tone'] }}">{{ $subscription['status_label'] }}</span></td>
                                <td><strong>{{ $subscription['valor_formatado'] }}</strong></td>
                                <td><button type="button" class="fin-btn primary" wire:click="abrirAssinatura({{ $subscription['id'] }})">Abrir</button></td>
                            </tr>
                        @empty
                            <tr><td colspan="7"><div class="fin-empty">Nenhuma assinatura encontrada.</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </section>
        @else
            <section class="fin-grid-2">
                <article class="fin-card">
                    <div class="fin-section-title"><div><h2>Fluxo dos últimos meses</h2><p>Compara valor previsto e recebido para enxergar tendência.</p></div></div>
                    <div class="fin-list">
                        @forelse (($data['cashflow'] ?? []) as $month)
                            <div class="fin-list-row">
                                <div style="width: 100%;">
                                    <div style="display:flex;justify-content:space-between;gap:.75rem;"><strong>{{ $month['label'] }}</strong><span>Recebido {{ $month['recebido'] }} · Previsto {{ $month['previsto'] }}</span></div>
                                    <div class="fin-progress"><i style="width: {{ $month['percent'] }}%"></i></div>
                                </div>
                            </div>
                        @empty
                            <div class="fin-empty">Ainda não há dados suficientes para fluxo financeiro.</div>
                        @endforelse
                    </div>
                </article>

                <aside class="fin-card">
                    <div class="fin-section-title"><div><h2>Alertas financeiros</h2><p>O que precisa de atenção agora.</p></div></div>
                    <div class="fin-list">
                        @forelse (($data['alerts'] ?? []) as $alert)
                            <div class="fin-list-row fin-alert {{ $alert['tone'] ?? 'info' }}"><div><strong>{{ $alert['title'] }}</strong><span>{{ $alert['text'] }}</span></div></div>
                        @empty
                            <div class="fin-empty">Nenhum alerta financeiro nos filtros atuais.</div>
                        @endforelse
                    </div>
                </aside>
            </section>

            <section class="fin-grid-2">
                <article class="fin-card">
                    <div class="fin-section-title"><div><h2>Clientes com maior receita recebida</h2><p>Ajuda a priorizar relacionamento e retenção.</p></div></div>
                    <div class="fin-list">
                        @forelse (($data['topClients'] ?? []) as $client)
                            <div class="fin-list-row"><div><strong>{{ $client['cliente'] }}</strong><span>{{ $client['email'] ?: 'Sem e-mail' }} · {{ $client['total_pagamentos'] }} pagamento(s)</span></div><strong>{{ $client['total'] }}</strong></div>
                        @empty
                            <div class="fin-empty">Nenhum recebimento confirmado encontrado.</div>
                        @endforelse
                    </div>
                </article>

                <article class="fin-card">
                    <div class="fin-section-title"><div><h2>Últimas cobranças</h2><p>Resumo operacional para não precisar alternar telas.</p></div></div>
                    <div class="fin-list">
                        @forelse (($data['recentPayments'] ?? []) as $payment)
                            <div class="fin-list-row"><div><strong>{{ $payment['cliente'] }}</strong><span>{{ $payment['vencimento'] ?: '-' }} · {{ $payment['plano'] }}</span></div><div style="text-align:right"><strong>{{ $payment['valor_formatado'] }}</strong><br><span class="fin-pill {{ $payment['status_tone'] }}">{{ $payment['status_label'] }}</span></div></div>
                        @empty
                            <div class="fin-empty">Nenhuma cobrança encontrada.</div>
                        @endforelse
                    </div>
                </article>
            </section>
        @endif
    </div>

    @if (($paymentModalOpen ?? false) && ! empty($selectedPayment))
        <div class="fin-modal-backdrop" wire:click.self="fecharModal">
            <section class="fin-modal">
                <header>
                    <div><h2 style="margin:0;font-weight:900;">Cobrança #{{ $selectedPayment['id'] }}</h2><p style="margin:.2rem 0 0;color:#64748b;">{{ $selectedPayment['cliente'] }} · {{ $selectedPayment['valor_formatado'] }}</p></div>
                    <button type="button" class="fin-btn" wire:click="fecharModal">Fechar</button>
                </header>
                <div class="fin-modal-body">
                    <div class="fin-details">
                        <div class="fin-detail"><small>Status</small><span class="fin-pill {{ $selectedPayment['status_tone'] }}">{{ $selectedPayment['status_label'] }}</span></div>
                        <div class="fin-detail"><small>Vencimento</small><strong>{{ $selectedPayment['vencimento'] ?: '-' }}</strong></div>
                        <div class="fin-detail"><small>Cliente</small><strong>{{ $selectedPayment['cliente'] }}</strong></div>
                        <div class="fin-detail"><small>Contato</small><strong>{{ $selectedPayment['email'] ?: '-' }}</strong><br>{{ $selectedPayment['telefone'] ?: '' }}</div>
                        <div class="fin-detail"><small>Plano</small><strong>{{ $selectedPayment['plano'] }}</strong><br>{{ $selectedPayment['ciclo'] }}</div>
                        <div class="fin-detail"><small>Gateway</small><strong>{{ $selectedPayment['gateway_payment_id'] ?: '-' }}</strong></div>
                    </div>
                    <div class="fin-actions">
                        @if (! $selectedPayment['is_paid'])
                            <button type="button" class="fin-btn success" wire:click="marcarComoRecebida({{ $selectedPayment['id'] }})">Marcar como recebida</button>
                        @else
                            <button type="button" class="fin-btn warning" wire:click="marcarComoPendente({{ $selectedPayment['id'] }})">Voltar para pendente</button>
                        @endif
                        @if (! empty($selectedPayment['invoice_url']))
                            <a href="{{ $selectedPayment['invoice_url'] }}" target="_blank" rel="noopener" class="fin-btn primary">Abrir link de pagamento</a>
                        @endif
                    </div>
                </div>
            </section>
        </div>
    @endif

    @if (($subscriptionModalOpen ?? false) && ! empty($selectedSubscription))
        <div class="fin-modal-backdrop" wire:click.self="fecharModal">
            <section class="fin-modal">
                <header>
                    <div><h2 style="margin:0;font-weight:900;">Assinatura #{{ $selectedSubscription['id'] }}</h2><p style="margin:.2rem 0 0;color:#64748b;">{{ $selectedSubscription['cliente'] }} · {{ $selectedSubscription['plano'] }}</p></div>
                    <button type="button" class="fin-btn" wire:click="fecharModal">Fechar</button>
                </header>
                <div class="fin-modal-body">
                    <div class="fin-details">
                        <div class="fin-detail"><small>Status</small><span class="fin-pill {{ $selectedSubscription['status_tone'] }}">{{ $selectedSubscription['status_label'] }}</span></div>
                        <div class="fin-detail"><small>Próximo vencimento</small><strong>{{ $selectedSubscription['proximo_vencimento'] ?: '-' }}</strong></div>
                        <div class="fin-detail"><small>Cliente</small><strong>{{ $selectedSubscription['cliente'] }}</strong></div>
                        <div class="fin-detail"><small>Contato</small><strong>{{ $selectedSubscription['email'] ?: '-' }}</strong><br>{{ $selectedSubscription['telefone'] ?: '' }}</div>
                        <div class="fin-detail"><small>Plano e ciclo</small><strong>{{ $selectedSubscription['plano'] }}</strong><br>{{ $selectedSubscription['ciclo'] }}</div>
                        <div class="fin-detail"><small>Valor</small><strong>{{ $selectedSubscription['valor_formatado'] }}</strong></div>
                        <div class="fin-detail"><small>Gateway</small><strong>{{ $selectedSubscription['gateway'] }}</strong><br>{{ $selectedSubscription['gateway_subscription_id'] ?: '-' }}</div>
                        <div class="fin-detail"><small>Cancelada em</small><strong>{{ $selectedSubscription['cancelado_em'] ?: '-' }}</strong></div>
                    </div>
                    <div class="fin-actions">
                        @if (! $selectedSubscription['is_active'])
                            <button type="button" class="fin-btn success" wire:click="ativarAssinatura({{ $selectedSubscription['id'] }})">Ativar</button>
                        @else
                            <button type="button" class="fin-btn warning" wire:click="pausarAssinatura({{ $selectedSubscription['id'] }})">Pausar</button>
                            <button type="button" class="fin-btn danger" wire:click="cancelarAssinatura({{ $selectedSubscription['id'] }})">Cancelar</button>
                        @endif
                    </div>
                </div>
            </section>
        </div>
    @endif
</x-filament-panels::page>
