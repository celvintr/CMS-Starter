<x-filament-panels::page>
    @php
        $grupos = collect(\App\Support\PackLibrary::all())->groupBy('category');
    @endphp

    <p class="text-sm text-gray-500 dark:text-gray-400 -mt-2">
        Módulos y plantillas listos para instalar de un clic. Cada instalación crea una copia
        editable en este sitio.
    </p>

    <div class="space-y-8">
        @foreach ($grupos as $categoria => $items)
            <section>
                <h2 class="text-base font-semibold text-gray-950 dark:text-white mb-3">{{ $categoria }}</h2>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($items as $entry)
                        <div class="flex flex-col rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-white/10 dark:bg-white/5">
                            <div class="flex items-center gap-3">
                                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary-50 text-primary-600 dark:bg-primary-500/10 dark:text-primary-400">
                                    <x-filament::icon :icon="$entry['icon']" class="h-5 w-5" />
                                </span>
                                <div class="font-semibold text-gray-950 dark:text-white">{{ $entry['label'] }}</div>
                            </div>

                            <p class="mt-3 flex-1 text-sm text-gray-500 dark:text-gray-400">{{ $entry['description'] }}</p>

                            <div class="mt-4">
                                <x-filament::button
                                    wire:click="importar('{{ $entry['key'] }}')"
                                    wire:loading.attr="disabled"
                                    wire:target="importar('{{ $entry['key'] }}')"
                                    icon="heroicon-o-arrow-down-tray"
                                    size="sm"
                                    class="w-full justify-center"
                                >
                                    Instalar
                                </x-filament::button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>
</x-filament-panels::page>
