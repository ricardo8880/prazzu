<div class="crm-hub">
    <div class="problema">
        <strong>Problema atual:</strong>
        <span class="badge badge-{{ $problema['tipo'] }}">
            {{ $problema['label'] }}
        </span>
    </div>

    <div class="acoes">
        @if($problema['tipo'] === 'documento')
            <button wire:click="abrirDocumentos">Resolver documento</button>
        @endif

        @if($problema['tipo'] === 'aprovacao')
            <button wire:click="abrirAprovacoes">Ir para aprovação</button>
        @endif

        @if($problema['tipo'] === 'financeiro')
            <button wire:click="abrirFinanceiro">Ver financeiro</button>
        @endif

        @if($problema['tipo'] === 'contato')
            <button wire:click="registrarContatoRapido">Registrar contato</button>
        @endif
    </div>
</div>
