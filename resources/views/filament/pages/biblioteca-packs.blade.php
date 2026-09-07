<x-filament-panels::page>
    @php
        $grupos = collect(\App\Support\PackLibrary::all())->groupBy('category');
    @endphp

    <p style="font-size:.875rem;color:rgb(113 113 122);margin-top:-.5rem">
        Módulos y plantillas listos para instalar de un clic. Cada instalación crea una copia editable en este sitio.
    </p>

    @foreach ($grupos as $categoria => $items)
        <section style="margin-top:1.5rem">
            <h2 style="font-size:1rem;font-weight:600;margin-bottom:.75rem">{{ $categoria }}</h2>

            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:1rem">
                @foreach ($items as $entry)
                    <div style="display:flex;flex-direction:column;gap:.75rem;border:1px solid rgba(128,128,128,.2);border-radius:.75rem;padding:1.25rem;box-shadow:0 1px 2px rgba(0,0,0,.04)">
                        <div style="display:flex;align-items:center;gap:.75rem">
                            <span style="display:flex;height:2.5rem;width:2.5rem;flex:none;align-items:center;justify-content:center;border-radius:.625rem;background:rgba(59,130,246,.12);color:#2563eb">
                                <x-filament::icon :icon="$entry['icon']" style="height:1.25rem;width:1.25rem" />
                            </span>
                            <span style="font-weight:600;line-height:1.2">{{ $entry['label'] }}</span>
                        </div>

                        <p style="font-size:.8125rem;color:rgb(113 113 122);flex:1;margin:0;line-height:1.4">{{ $entry['description'] }}</p>

                        <div style="margin-top:auto">
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
</x-filament-panels::page>
