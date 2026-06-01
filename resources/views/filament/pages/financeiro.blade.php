<x-filament-panels::page>
    <link rel="stylesheet" href="{{ asset('css/financeiro-cliente.css') }}">

    <div class="fincli-page">
        @if (! $instalado)
            <section class="fincli-alert">
                <strong>Módulo financeiro do cliente ainda não instalado</strong>
                <span>Execute o SQL em <code>sql/financeiro_cliente.sql</code>. Tabelas faltantes: {{ implode(', ', $faltantes) }}</span>
            </section>
        @endif

        <section class="fincli-hero">
            <div>
                <span>DASHBOARD</span>
                <h1>Financeiro do cliente</h1>
                <p>Visão simples do contas a receber da empresa, com estrutura pronta para gateway próprio por cliente.</p>
            </div>
            <div class="fincli-actions">
                <button type="button" class="primary" wire:click="abrirGateway">Configurar gateway</button>
            </div>
        </section>

        @if (count($empresas) > 1)
            <section class="fincli-toolbar compact">
                <label><span>Empresa</span><select wire:model.live="empresaFiltro"><option value="">Todas</option>@foreach ($empresas as $empresa)<option value="{{ $empresa['id'] }}">{{ $empresa['nome'] }}</option>@endforeach</select></label>
            </section>
        @endif

        <section class="fincli-stats">
            @foreach (($dashboard['stats'] ?? []) as $stat)
                <article class="{{ $stat['tone'] ?? '' }}"><span>{{ $stat['label'] }}</span><strong>{{ $stat['value'] }}</strong><small>{{ $stat['hint'] }}</small></article>
            @endforeach
        </section>

        <section class="fincli-grid two">
            <article class="fincli-card">
                <header><div><h2>Próximos 30 dias</h2><p>Previsão de entrada baseada nas cobranças abertas.</p></div></header>
                <div class="fincli-list">
                    @forelse (($dashboard['fluxo'] ?? []) as $dia)
                        <div class="fincli-list-row"><div><strong>{{ $dia['dia'] }}</strong><span>{{ $dia['quantidade'] }} cobrança(s)</span></div><em>{{ $dia['total'] }}</em></div>
                    @empty
                        <div class="fincli-empty">Nenhuma cobrança aberta para os próximos 30 dias.</div>
                    @endforelse
                </div>
            </article>

            <article class="fincli-card">
                <header><div><h2>Integrações de gateway</h2><p>Cada empresa pode ter o próprio gateway. Token salvo criptografado.</p></div></header>
                <div class="fincli-list">
                    @forelse (($dashboard['integracoes'] ?? []) as $integracao)
                        <div class="fincli-list-row">
                            <div><strong>{{ $integracao['nome'] ?: strtoupper($integracao['gateway']) }}</strong><span>{{ strtoupper($integracao['gateway']) }} · {{ $integracao['ambiente'] }} · {{ $integracao['status'] }}</span></div>
                            <button type="button" class="danger" wire:click="desativarGateway({{ $integracao['id'] }})">Desativar</button>
                        </div>
                    @empty
                        <div class="fincli-empty">Nenhuma integração configurada. O financeiro manual continua funcionando.</div>
                    @endforelse
                </div>
            </article>
        </section>

        <section class="fincli-grid two">
            <article class="fincli-card">
                <header><div><h2>Cobranças que precisam de atenção</h2><p>Lista priorizada por vencidas e abertas.</p></div></header>
                <div class="fincli-list">
                    @forelse (($dashboard['vencimentos'] ?? []) as $cobranca)
                        <div class="fincli-list-row">
                            <div><strong>{{ $cobranca['cliente_nome'] ?? 'Cliente' }}</strong><span>{{ $cobranca['descricao'] }} · vence {{ $cobranca['vencimento_formatado'] }}</span></div>
                            <em class="{{ $cobranca['status_tone'] ?? '' }}">{{ $cobranca['valor_formatado'] }}</em>
                        </div>
                    @empty
                        <div class="fincli-empty">Nenhuma cobrança pendente.</div>
                    @endforelse
                </div>
            </article>

            <article class="fincli-card">
                <header><div><h2>Como usar</h2><p>Fluxo pensado para o cliente não se perder.</p></div></header>
                <div class="fincli-steps">
                    <div><strong>1</strong><span>Cadastre o cliente financeiro na aba Cobranças.</span></div>
                    <div><strong>2</strong><span>Crie uma cobrança avulsa ou uma assinatura recorrente.</span></div>
                    <div><strong>3</strong><span>Registre o recebimento manualmente ou conecte o gateway da empresa.</span></div>
                    <div><strong>4</strong><span>Acompanhe vencidos, abertos e recebidos neste dashboard.</span></div>
                </div>
            </article>
        </section>
    </div>

    @if ($modalGatewayAberto)
        <div class="fincli-modal-backdrop" wire:click.self="$set('modalGatewayAberto', false)">
            <section class="fincli-modal small">
                <header><div><span>GATEWAY POR EMPRESA</span><h2>Configurar integração</h2></div><button type="button" wire:click="$set('modalGatewayAberto', false)">×</button></header>
                <form wire:submit.prevent="salvarGateway" class="fincli-form">
                    <label><span>Gateway</span><select wire:model.defer="gatewayForm.gateway"><option value="manual">Manual</option><option value="asaas">Asaas</option><option value="mercado_pago">Mercado Pago</option><option value="stripe">Stripe</option></select></label>
                    <label><span>Ambiente</span><select wire:model.defer="gatewayForm.ambiente"><option value="sandbox">Sandbox</option><option value="producao">Produção</option></select></label>
                    <label class="wide"><span>Nome interno</span><input type="text" wire:model.defer="gatewayForm.nome" placeholder="Ex: Asaas da empresa"></label>
                    <label class="wide"><span>Token/API Key</span><input type="password" wire:model.defer="gatewayForm.api_token" placeholder="Será salvo criptografado"></label>
                    <label class="wide"><span>Webhook secret</span><input type="password" wire:model.defer="gatewayForm.webhook_secret" placeholder="Opcional"></label>
                    <div class="fincli-note wide">Esta tela deixa a estrutura pronta. A criação automática de cobrança via API deve ser ligada no serviço do gateway escolhido.</div>
                    <footer><button type="button" wire:click="$set('modalGatewayAberto', false)">Fechar</button><button type="submit" class="primary">Salvar integração</button></footer>
                </form>
            </section>
        </div>
    @endif
</x-filament-panels::page>
