<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Fluxo de anexos
        </x-slot>

        <x-slot name="description">
            Acompanhe o arquivo principal, os anexos extras e o que ainda precisa de ação.
        </x-slot>

        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-2xl border {{ $temPrincipal ? 'border-emerald-200 bg-emerald-50 dark:border-emerald-500/20 dark:bg-emerald-500/10' : 'border-rose-200 bg-rose-50 dark:border-rose-500/20 dark:bg-rose-500/10' }} p-5">
                <div class="flex items-start gap-3">
                    <div class="rounded-xl {{ $temPrincipal ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300' : 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-300' }} p-2">
                        @if ($temPrincipal)
                            <x-heroicon-o-document-check class="h-5 w-5" />
                        @else
                            <x-heroicon-o-exclamation-triangle class="h-5 w-5" />
                        @endif
                    </div>

                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-semibold uppercase tracking-wide {{ $temPrincipal ? 'text-emerald-700 dark:text-emerald-300' : 'text-rose-700 dark:text-rose-300' }}">
                            Anexo principal
                        </p>

                        @if ($temPrincipal)
                            <p class="mt-1 truncate text-sm font-semibold text-gray-950 dark:text-white" title="{{ $principalNome }}">
                                {{ $principalNome }}
                            </p>

                            <div class="mt-3 flex flex-wrap gap-2">
                                <a href="{{ $principalUrl }}" target="_blank" class="inline-flex items-center gap-1 rounded-lg bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm ring-1 ring-gray-200 hover:bg-gray-50 dark:bg-white/10 dark:text-gray-100 dark:ring-white/10">
                                    <x-heroicon-o-arrow-top-right-on-square class="h-4 w-4" />
                                    Abrir
                                </a>

                                @if ($principalPreview)
                                    <span class="inline-flex items-center rounded-lg bg-emerald-100 px-3 py-1.5 text-xs font-semibold text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300">
                                        Preview disponível
                                    </span>
                                @endif
                            </div>
                        @else
                            <p class="mt-1 text-sm font-semibold text-gray-950 dark:text-white">
                                Falta anexar o arquivo principal.
                            </p>
                            <p class="mt-1 text-xs text-gray-600 dark:text-gray-300">
                                Use o campo “Anexo principal” no formulário acima ou o upload rápido de complementares quando for apenas evidência extra.
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900">
                <div class="flex items-start gap-3">
                    <div class="rounded-xl bg-amber-100 p-2 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300">
                        <x-heroicon-o-paper-clip class="h-5 w-5" />
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Anexos extras</p>
                        <p class="mt-1 text-2xl font-bold text-gray-950 dark:text-white">{{ $anexosCount }}</p>
                        <p class="mt-1 text-xs text-gray-600 dark:text-gray-300">Evidências, versões auxiliares e arquivos de apoio ficam separados do principal.</p>
                    </div>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-gray-900">
                <div class="flex items-start gap-3">
                    <div class="rounded-xl bg-primary-100 p-2 text-primary-700 dark:bg-primary-500/20 dark:text-primary-300">
                        <x-heroicon-o-folder-open class="h-5 w-5" />
                    </div>

                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Total de arquivos</p>
                        <p class="mt-1 text-2xl font-bold text-gray-950 dark:text-white">{{ $totalArquivos }}</p>
                        <p class="mt-1 text-xs text-gray-600 dark:text-gray-300">A lista abaixo mostra os anexos complementares com ações rápidas de abertura e preview.</p>
                    </div>
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
