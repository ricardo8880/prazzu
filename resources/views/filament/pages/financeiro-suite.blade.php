<x-filament-panels::page>
    @php
        $metrics = $data['metrics'] ?? [];
        $emptyMessage = $data['emptyMessage'] ?? null;
    @endphp
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
