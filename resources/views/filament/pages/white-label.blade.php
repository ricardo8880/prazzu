<x-filament-panels::page>
    <form wire:submit="save" class="space-y-6">
        {{ $this->form }}

        <div class="flex justify-end gap-3">
            <x-filament::button
                type="submit"
                color="warning"
                icon="heroicon-o-check"
            >
                Salvar White Label
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
