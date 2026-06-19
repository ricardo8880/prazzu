@php
    $status = strtolower((string) ($documento['status'] ?? ''));
    $vencido = ! empty($documento['data_vencimento'])
        && \Carbon\Carbon::parse($documento['data_vencimento'])->isPast()
        && ! in_array($status, ['concluido', 'concluído', 'finalizado'], true);
    $semArquivo = empty($documento['arquivo']);
    $label = $vencido ? 'Regularizar vencido' : ($semArquivo ? 'Anexar arquivo' : 'Ajustar documento');
@endphp

<x-filament::button
    type="button"
    size="sm"
    color="primary"
    icon="heroicon-m-wrench-screwdriver"
    wire:click="abrirResolucaoDocumento({{ $documento['id'] }})"
    wire:loading.attr="disabled"
    wire:target="abrirResolucaoDocumento({{ $documento['id'] }})"
>
    <span wire:loading.remove wire:target="abrirResolucaoDocumento({{ $documento['id'] }})">{{ $label }}</span>
    <span wire:loading wire:target="abrirResolucaoDocumento({{ $documento['id'] }})">Abrindo...</span>
</x-filament::button>
