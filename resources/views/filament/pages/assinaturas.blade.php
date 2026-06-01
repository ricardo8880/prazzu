<x-filament-panels::page>
    <link rel="stylesheet" href="{{ asset('css/financeiro-cliente.css') }}">
    <link rel="stylesheet" href="{{ asset('css/tarefas-qa-standard.css') }}?v=20260513-lote7-visual">

    <div class="fincli-page">
        @if (! $instalado)
            <section class="fincli-alert">
                <strong>Módulo financeiro do cliente ainda não instalado</strong>
                <span>Execute o SQL em <code>sql/financeiro_cliente.sql</code>. Tabelas faltantes: {{ implode(', ', $faltantes) }}</span>
            </section>
        @endif

        <section class="fincli-hero">
            <div>
                <span>RECORRÊNCIA</span>
                <h1>Assinaturas dos clientes</h1>
                <p>Gerencie planos recorrentes dos clientes da empresa logada e gere cobranças sem depender de gateway externo.</p>
            </div>
            <div class="fincli-actions">
                <button type="button" class="primary" wire:click="abrirNovaAssinatura">Nova assinatura</button>
            </div>
        </section>

        <section class="fincli-toolbar">
            @if (count($empresas) > 1)
                <label>
                    <span>Empresa</span>
                    <select wire:model.live="empresaFiltro"><option value="">Todas</option>@foreach ($empresas as $empresa)<option value="{{ $empresa['id'] }}">{{ $empresa['nome'] }}</option>@endforeach</select>
                </label>
            @endif
            <label>
                <span>Status</span>
                <select wire:model.live="statusFiltro">
                    <option value="todas">Todas</option>
                    <option value="ativa">Ativas</option>
                    <option value="pausada">Pausadas</option>
                    <option value="cancelada">Canceladas</option>
                </select>
            </label>
            <label class="grow"><span>Busca</span><input type="search" wire:model.live.debounce.400ms="busca" placeholder="Buscar por cliente ou plano"></label>
        </section>

        <section class="fincli-card">
            <header><div><h2>Planos recorrentes</h2><p>Use “Gerar cobrança” para criar a próxima conta a receber e avançar a data automaticamente.</p></div></header>
            <div class="fincli-table-wrap">
                <table class="fincli-table">
                    <thead><tr><th>Cliente</th><th>Plano</th><th>Valor</th><th>Ciclo</th><th>Próxima cobrança</th><th>Status</th><th>Ações</th></tr></thead>
                    <tbody>
                        @forelse ($assinaturasCliente as $assinatura)
                            <tr>
                                <td><strong>{{ $assinatura['cliente_nome'] ?? 'Cliente não informado' }}</strong><br><small>{{ $assinatura['cliente_email'] ?? $assinatura['cliente_documento'] ?? '-' }}</small></td>
                                <td><strong>{{ $assinatura['nome'] }}</strong><br><small>{{ \Illuminate\Support\Str::limit($assinatura['descricao'] ?? 'Sem descrição', 70) }}</small></td>
                                <td>{{ $assinatura['valor_formatado'] }}</td>
                                <td>{{ $assinatura['ciclo_label'] }}</td>
                                <td>{{ $assinatura['proxima_cobranca_formatada'] }}</td>
                                <td><span class="fincli-badge {{ $assinatura['status_tone'] }}">{{ ucfirst($assinatura['status']) }}</span></td>
                                <td class="fincli-row-actions">
                                    <button type="button" wire:click="abrirAssinatura({{ $assinatura['id'] }})">Abrir</button>
                                    @if (($assinatura['status'] ?? '') === 'ativa')
                                        <button type="button" wire:click="gerarCobranca({{ $assinatura['id'] }})">Gerar cobrança</button>
                                        <button type="button" wire:click="alterarStatus({{ $assinatura['id'] }}, 'pausada')">Pausar</button>
                                    @elseif (($assinatura['status'] ?? '') === 'pausada')
                                        <button type="button" wire:click="alterarStatus({{ $assinatura['id'] }}, 'ativa')">Reativar</button>
                                    @endif
                                    @if (($assinatura['status'] ?? '') !== 'cancelada')
                                        <button type="button" class="danger" wire:click="alterarStatus({{ $assinatura['id'] }}, 'cancelada')">Cancelar</button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="fincli-empty fincli-empty-actionable">
                                    <strong>Nenhuma assinatura encontrada</strong>
                                    <span>Crie uma assinatura recorrente somente quando o cliente financeiro já estiver cadastrado. Use os filtros acima para conferir se não há resultados ocultos.</span>
                                    <button type="button" class="primary" wire:click="abrirNovaAssinatura">Nova assinatura</button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    @if ($modalAssinaturaAberto)
        <div class="fincli-modal-backdrop" wire:click.self="$set('modalAssinaturaAberto', false)">
            <section class="fincli-modal">
                <header><div><span>ASSINATURA</span><h2>{{ $assinaturaSelecionada ? 'Editar assinatura' : 'Nova assinatura' }}</h2></div><button type="button" wire:click="$set('modalAssinaturaAberto', false)">×</button></header>
                <form wire:submit.prevent="salvarAssinatura" class="fincli-form">
                    <label class="wide"><span>Cliente financeiro</span><select wire:model="form.financeiro_cliente_id"><option value="">Selecione</option>@foreach ($clientes as $cliente)<option value="{{ $cliente['id'] }}">{{ $cliente['nome'] }}</option>@endforeach</select>@error('form.financeiro_cliente_id') <small class="error">{{ $message }}</small> @enderror</label>
                    <label class="wide"><span>Nome do plano/serviço</span><input type="text" wire:model.defer="form.nome" placeholder="Ex: Plano mensal, consultoria recorrente">@error('form.nome') <small class="error">{{ $message }}</small> @enderror</label>
                    <label><span>Valor</span><input type="number" min="0.01" step="0.01" wire:model.defer="form.valor">@error('form.valor') <small class="error">{{ $message }}</small> @enderror</label>
                    <label><span>Ciclo</span><select wire:model.defer="form.ciclo"><option value="semanal">Semanal</option><option value="quinzenal">Quinzenal</option><option value="mensal">Mensal</option><option value="trimestral">Trimestral</option><option value="semestral">Semestral</option><option value="anual">Anual</option></select></label>
                    <label><span>Próxima cobrança</span><input type="date" wire:model.defer="form.proxima_cobranca_em">@error('form.proxima_cobranca_em') <small class="error">{{ $message }}</small> @enderror</label>
                    <label><span>Forma</span><select wire:model.defer="form.forma_pagamento"><option value="manual">Manual</option><option value="pix">Pix</option><option value="boleto">Boleto</option><option value="cartao">Cartão</option><option value="transferencia">Transferência</option></select></label>
                    <label class="wide"><span>Descrição</span><textarea rows="3" wire:model.defer="form.descricao"></textarea></label>
                    <footer><button type="button" wire:click="$set('modalAssinaturaAberto', false)">Fechar</button><button type="submit" class="primary">Salvar assinatura</button></footer>
                </form>
            </section>
        </div>
    @endif
</x-filament-panels::page>
