<x-filament::button
    type="button"
    size="sm"
    color="primary"
    icon="heroicon-m-wrench-screwdriver"
    wire:click="abrirResolucaoDocumento({{ $documento['id'] }})"
    wire:loading.attr="disabled"
    wire:target="abrirResolucaoDocumento({{ $documento['id'] }})"
>
    <span wire:loading.remove wire:target="abrirResolucaoDocumento({{ $documento['id'] }})">Resolver</span>
    <span wire:loading wire:target="abrirResolucaoDocumento({{ $documento['id'] }})">Abrindo...</span>
</x-filament::button>
