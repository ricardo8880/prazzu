<x-filament-panels::page>
    <div class="space-y-8">

        <x-filament::section>
            <x-slot name="heading">Vencimentos próximos</x-slot>

            @foreach($this->vencimentos as $item)
                <div class="py-2 border-b">
                    {{ $item->titulo }} — {{ $item->data_vencimento?->format('d/m/Y') }}
                </div>
            @endforeach
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">SLA atrasados</x-slot>

            @foreach($this->slaAtrasados as $item)
                <div class="py-2 border-b">
                    {{ $item->titulo }}
                </div>
            @endforeach
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Aprovações pendentes</x-slot>

            @foreach($this->aprovacoesPendentes as $item)
                <div class="py-2 border-b">
                    {{ $item->titulo }}
                </div>
            @endforeach
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Produtividade</x-slot>

            @foreach($this->produtividade as $item)
                <div class="py-2 border-b">
                    {{ $item->responsavel?->nome }} —
                    {{ $item->concluidos }}/{{ $item->total }}
                </div>
            @endforeach
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Contratos vencendo</x-slot>

            @foreach($this->contratosVencendo as $item)
                <div class="py-2 border-b">
                    {{ $item->titulo }} —
                    {{ $item->contrato_fim_em?->format('d/m/Y') }}
                </div>
            @endforeach
        </x-filament::section>

    </div>
</x-filament-panels::page>
