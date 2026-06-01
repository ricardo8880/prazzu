<article class="ca-approval-card {{ $item['tom'] }} {{ ! empty($compacto) ? 'compact' : '' }} {{ ! empty($destaque) ? 'featured' : '' }}">
    <div class="ca-approval-top">
        <div>
            <h3>{{ $item['titulo'] }}</h3>
            <small>{{ $item['empresa'] }} • {{ $item['tipo'] }}</small>
        </div>
        <span>{{ $item['status_label'] }}</span>
    </div>

    @if (! empty($item['atrasado']) || ! empty($item['critico']))
        <div class="ca-alert-line">
            @if (! empty($item['atrasado']))
                <strong>Atrasado</strong>
            @endif
            @if (! empty($item['critico']))
                <strong>Prioridade alta</strong>
            @endif
        </div>
    @endif

    @if (empty($compacto))
        <p>{{ $item['descricao'] }}</p>
    @endif

    <div class="ca-tags">
        <b class="priority">{{ $item['prioridade'] }}</b>
        <b>Responsável: {{ $item['responsavel'] }}</b>
        <b>Solicitante: {{ $item['solicitante'] }}</b>
        <b>Aprovador: {{ $item['aprovador'] }}</b>
        <b>Solicitado: {{ $item['solicitado_em'] }}</b>
        <b>Aguardando: {{ $item['idade'] }}</b>
        @if (! empty($item['decisao_alerta']) && $item['status'] === 'pendente')
            <b class="decision">Revisão obrigatória</b>
        @endif
        @if ($item['vencimento'] !== '-')
            <b class="{{ $item['atrasado'] ? 'late' : '' }}">Vence: {{ $item['vencimento'] }}</b>
        @endif
    </div>

    @if (! empty($item['resposta']) && empty($compacto))
        <div class="ca-note"><strong>Histórico da decisão:</strong> {{ $item['resposta'] }}</div>
    @elseif (empty($compacto))
        <div class="ca-note"><strong>Pedido de aprovação:</strong> {{ $item['observacao'] }}</div>
    @endif

    <div class="ca-card-actions">
        <button type="button" class="details" wire:click="abrirDetalhesItem({{ $item['id'] }})" wire:loading.attr="disabled" wire:target="abrirDetalhesItem({{ $item['id'] }})">
            <span wire:loading.remove wire:target="abrirDetalhesItem({{ $item['id'] }})">Ver detalhes</span>
            <span wire:loading wire:target="abrirDetalhesItem({{ $item['id'] }})">Abrindo...</span>
        </button>

        @if ($item['status'] === 'pendente')
            <button type="button" class="approve" wire:click="abrirConfirmacaoAprovacao({{ $item['id'] }})" wire:loading.attr="disabled" wire:target="abrirConfirmacaoAprovacao({{ $item['id'] }})">Aprovar com revisão</button>
            <button type="button" class="reject" wire:click="abrirReprovacao({{ $item['id'] }})" wire:loading.attr="disabled" wire:target="abrirReprovacao({{ $item['id'] }})">Solicitar ajuste</button>
        @endif
    </div>
</article>
