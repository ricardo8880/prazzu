<x-filament-panels::page>
    @php
        $whiteLabel = \App\Support\WhiteLabelSettings::make();
        $brandName = $whiteLabel->displayName();
    @endphp
<div class="fincli-page">
        @if (! $instalado)
            <section class="fincli-alert">
                <strong>Módulo financeiro do cliente ainda não instalado</strong>
                <span>Execute o SQL em <code>sql/financeiro_cliente.sql</code>. Tabelas faltantes: {{ implode(', ', $faltantes) }}</span>
            </section>
        @endif

        <section class="fincli-hero">
            <div>
                <span>FINANCEIRO DO CLIENTE</span>
                <h1>Cobranças</h1>
                <p>Controle contas a receber, vencimentos e baixas manuais sem misturar com os pagamentos da assinatura do {{ $brandName }}.</p>
            </div>
            <div class="fincli-actions">
                <button type="button" wire:click="abrirNovoCliente">Novo cliente financeiro</button>
                <button type="button" class="primary" wire:click="abrirNovaCobranca">Nova cobrança</button>
            </div>
        </section>

        <section class="fincli-stats">
            @foreach (($dashboard['stats'] ?? []) as $stat)
                <article class="{{ $stat['tone'] ?? '' }}">
                    <span>{{ $stat['label'] }}</span>
                    <strong>{{ $stat['value'] }}</strong>
                    <small>{{ $stat['hint'] }}</small>
                </article>
            @endforeach
        </section>

        <section class="fincli-toolbar">
            @if (count($empresas) > 1)
                <label>
                    <span>Empresa</span>
                    <select wire:model.live="empresaFiltro">
                        <option value="">Todas</option>
                        @foreach ($empresas as $empresa)
                            <option value="{{ $empresa['id'] }}">{{ $empresa['nome'] }}</option>
                        @endforeach
                    </select>
                </label>
            @endif
            <label>
                <span>Status</span>
                <select wire:model.live="statusFiltro">
                    <option value="todos">Todos</option>
                    <option value="aberta">Abertas</option>
                    <option value="vencida">Vencidas</option>
                    <option value="paga">Pagas</option>
                    <option value="cancelada">Canceladas</option>
                </select>
            </label>
            <label class="grow">
                <span>Busca</span>
                <input type="search" wire:model.live.debounce.400ms="busca" placeholder="Buscar por cliente, referência ou descrição">
            </label>
        </section>

        <section class="fincli-card">
            <header>
                <div>
                    <h2>Visão de cobrança</h2>
                    <p>Ações simples para o cliente: abrir, registrar pagamento ou cancelar.</p>
                </div>
            </header>
            <div class="fincli-table-wrap">
                <table class="fincli-table">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Cobrança</th>
                            <th>Valor</th>
                            <th>Vencimento</th>
                            <th>Status</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($cobrancas as $cobranca)
                            <tr>
                                <td><strong>{{ $cobranca['cliente_nome'] ?? 'Cliente não informado' }}</strong><br><small>{{ $cobranca['cliente_documento'] ?? $cobranca['cliente_email'] ?? '-' }}</small></td>
                                <td><strong>{{ $cobranca['descricao'] }}</strong><br><small>{{ $cobranca['referencia'] ?? 'Sem referência' }}</small></td>
                                <td>{{ $cobranca['valor_formatado'] }}</td>
                                <td>{{ $cobranca['vencimento_formatado'] }}</td>
                                <td><span class="fincli-badge {{ $cobranca['status_tone'] }}">{{ $cobranca['status_label'] }}</span></td>
                                <td class="fincli-row-actions">
                                    <button type="button" wire:click="abrirCobranca({{ $cobranca['id'] }})">Abrir</button>
                                    @if (($cobranca['status'] ?? '') !== 'paga' && ($cobranca['status'] ?? '') !== 'cancelada')
                                        <button type="button" wire:click="registrarPagamento({{ $cobranca['id'] }})">Receber</button>
                                        <button type="button" class="danger" wire:click="cancelarCobranca({{ $cobranca['id'] }})">Cancelar</button>
                                    @endif
                                    @if (! empty($cobranca['link_pagamento']))
                                        <a href="{{ $cobranca['link_pagamento'] }}" target="_blank" rel="noopener">Link</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="fincli-empty">Nenhuma cobrança encontrada. Crie uma cobrança manual ou gere uma pela aba Assinaturas.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    @if ($modalCobrancaAberto)
        <div class="fincli-modal-backdrop" wire:click.self="$set('modalCobrancaAberto', false)">
            <section class="fincli-modal">
                <header>
                    <div>
                        <span>COBRANÇA</span>
                        <h2>{{ $cobrancaSelecionada ? 'Editar cobrança' : 'Nova cobrança' }}</h2>
                    </div>
                    <button type="button" wire:click="$set('modalCobrancaAberto', false)">×</button>
                </header>
                <form wire:submit.prevent="salvarCobranca" class="fincli-form">
                    <label class="wide">
                        <span>Cliente financeiro</span>
                        <select wire:model="form.financeiro_cliente_id">
                            <option value="">Selecione</option>
                            @foreach ($clientes as $cliente)
                                <option value="{{ $cliente['id'] }}">{{ $cliente['nome'] }}</option>
                            @endforeach
                        </select>
                        @error('form.financeiro_cliente_id') <small class="error">{{ $message }}</small> @enderror
                    </label>
                    <label class="wide">
                        <span>Descrição</span>
                        <input type="text" wire:model.defer="form.descricao" placeholder="Ex: Mensalidade, implantação, consultoria">
                        @error('form.descricao') <small class="error">{{ $message }}</small> @enderror
                    </label>
                    <label>
                        <span>Referência</span>
                        <input type="text" wire:model.defer="form.referencia" placeholder="Ex: MAI/2026">
                    </label>
                    <label>
                        <span>Valor</span>
                        <input type="number" step="0.01" min="0.01" wire:model.defer="form.valor">
                        @error('form.valor') <small class="error">{{ $message }}</small> @enderror
                    </label>
                    <label>
                        <span>Vencimento</span>
                        <input type="date" wire:model.defer="form.vencimento">
                        @error('form.vencimento') <small class="error">{{ $message }}</small> @enderror
                    </label>
                    <label>
                        <span>Forma</span>
                        <select wire:model.defer="form.forma_pagamento">
                            <option value="manual">Manual</option>
                            <option value="pix">Pix</option>
                            <option value="boleto">Boleto</option>
                            <option value="cartao">Cartão</option>
                            <option value="transferencia">Transferência</option>
                        </select>
                    </label>
                    <label class="wide">
                        <span>Observações</span>
                        <textarea rows="3" wire:model.defer="form.observacoes" placeholder="Informação interna para acompanhamento"></textarea>
                    </label>
                    <footer>
                        <button type="button" wire:click="$set('modalCobrancaAberto', false)">Fechar</button>
                        <button type="submit" class="primary">Salvar cobrança</button>
                    </footer>
                </form>
            </section>
        </div>
    @endif

    @if ($modalClienteAberto)
        <div class="fincli-modal-backdrop" wire:click.self="$set('modalClienteAberto', false)">
            <section class="fincli-modal small">
                <header>
                    <div><span>CLIENTE FINANCEIRO</span><h2>Novo cliente</h2></div>
                    <button type="button" wire:click="$set('modalClienteAberto', false)">×</button>
                </header>
                <form wire:submit.prevent="salvarCliente" class="fincli-form">
                    <label class="wide"><span>Nome</span><input type="text" wire:model.defer="clienteForm.nome">@error('clienteForm.nome') <small class="error">{{ $message }}</small> @enderror</label>
                    <label><span>Documento</span><input type="text" wire:model.defer="clienteForm.documento"></label>
                    <label><span>E-mail</span><input type="email" wire:model.defer="clienteForm.email"></label>
                    <label><span>Telefone</span><input type="text" wire:model.defer="clienteForm.telefone"></label>
                    <label class="wide"><span>Observações</span><textarea rows="3" wire:model.defer="clienteForm.observacoes"></textarea></label>
                    <footer><button type="button" wire:click="$set('modalClienteAberto', false)">Fechar</button><button type="submit" class="primary">Salvar cliente</button></footer>
                </form>
            </section>
        </div>
    @endif
</x-filament-panels::page>
