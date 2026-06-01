<x-filament-panels::page>
    <div class="space-y-4">
        @forelse ($this->contratos as $contrato)
            <div class="rounded-xl border bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <div class="flex items-start justify-between gap-4">
                    <div class="space-y-2">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-sm font-semibold text-gray-950 dark:text-white">
                                {{ $contrato->titulo }}
                            </span>

                            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                                {{ $contrato->getContratoStatusResumo() }}
                            </span>
                        </div>

                        <div class="grid gap-2 text-sm text-gray-600 dark:text-gray-300 md:grid-cols-2">
                            <div>
                                <strong>Número:</strong>
                                {{ $contrato->contrato_numero ?: '-' }}
                            </div>

                            <div>
                                <strong>Parte:</strong>
                                {{ $contrato->contrato_parte_nome ?: '-' }}
                            </div>

                            <div>
                                <strong>Documento:</strong>
                                {{ $contrato->contrato_parte_documento ?: '-' }}
                            </div>

                            <div>
                                <strong>Valor:</strong>
                                {{ $contrato->contrato_valor !== null ? 'R$ ' . number_format((float) $contrato->contrato_valor, 2, ',', '.') : '-' }}
                            </div>

                            <div>
                                <strong>Início:</strong>
                                {{ $contrato->contrato_inicio_em?->format('d/m/Y') ?? '-' }}
                            </div>

                            <div>
                                <strong>Fim:</strong>
                                {{ $contrato->contrato_fim_em?->format('d/m/Y') ?? '-' }}
                            </div>

                            <div>
                                <strong>Empresa:</strong>
                                {{ $contrato->empresa?->razao_social ?? '-' }}
                            </div>

                            <div>
                                <strong>Responsável:</strong>
                                {{ $contrato->responsavel?->nome ?? '-' }}
                            </div>
                        </div>
                    </div>

                    <div class="flex shrink-0 flex-col gap-2">
                        <x-filament::button
                            size="sm"
                            color="warning"
                            wire:click="atualizarStatus({{ $contrato->id }})"
                        >
                            Atualizar status
                        </x-filament::button>

                        <x-filament::button
                            size="sm"
                            color="gray"
                            tag="a"
                            href="{{ \App\Filament\Resources\ItemControles\ItemControleResource::getUrl('edit', ['record' => $contrato->id]) }}"
                        >
                            Abrir contrato
                        </x-filament::button>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-dashed p-8 text-center dark:border-gray-700">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Nenhum contrato encontrado.
                </p>
            </div>
        @endforelse
    </div>
</x-filament-panels::page>
