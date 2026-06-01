<x-filament::page>

    {{-- tabela padrão --}}
    {{ $this->table }}

    {{-- espaço --}}
    <div class="mt-10"></div>

    {{-- planos --}}
    @include('planos')

</x-filament::page>
