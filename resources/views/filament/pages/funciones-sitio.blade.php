<x-filament-panels::page>
    <p style="font-size:.875rem;color:rgb(113 113 122);margin-top:-.5rem">
        Activa o desactiva funciones del sitio. Al activar una, se desbloquea su funcionalidad en el panel y en el sitio.
    </p>

    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:1rem">
        @foreach (\App\Support\Features::all() as $f)
            <div style="display:flex;gap:1rem;border:1px solid rgba(128,128,128,.2);border-radius:.75rem;padding:1.25rem;box-shadow:0 1px 2px rgba(0,0,0,.04)">
                <span style="display:flex;height:2.75rem;width:2.75rem;flex:none;align-items:center;justify-content:center;border-radius:.625rem;background:rgba(59,130,246,.12);color:#2563eb">
                    <x-filament::icon :icon="$f['icon']" style="height:1.375rem;width:1.375rem" />
                </span>
                <div style="flex:1;min-width:0">
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:.5rem">
                        <span style="font-weight:600">{{ $f['name'] }}</span>
                        <button type="button" wire:click="toggle('{{ $f['key'] }}')" wire:loading.attr="disabled"
                                title="{{ $f['enabled'] ? 'Desactivar' : 'Activar' }}"
                                style="position:relative;flex:none;height:1.5rem;width:2.75rem;border-radius:9999px;border:0;cursor:pointer;transition:background .2s;background:{{ $f['enabled'] ? '#22c55e' : 'rgb(203 213 225)' }}">
                            <span style="position:absolute;top:.185rem;left:{{ $f['enabled'] ? '1.435rem' : '.185rem' }};height:1.125rem;width:1.125rem;border-radius:9999px;background:#fff;transition:left .2s;box-shadow:0 1px 2px rgba(0,0,0,.2)"></span>
                        </button>
                    </div>
                    <p style="margin:.4rem 0 0;font-size:.8125rem;color:rgb(113 113 122);line-height:1.4">{{ $f['description'] }}</p>
                    <span style="display:inline-block;margin-top:.6rem;font-size:.7rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:{{ $f['enabled'] ? '#16a34a' : 'rgb(148 163 184)' }}">
                        {{ $f['enabled'] ? 'Activada' : 'Desactivada' }}
                    </span>
                </div>
            </div>
        @endforeach
    </div>
</x-filament-panels::page>
