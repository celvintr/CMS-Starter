<x-filament-widgets::widget>
    <x-filament::section>
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap">
            <div>
                <h2 style="margin:0;font-size:18px;font-weight:700;color:var(--gray-950,#0f172a)">👋 Primeros pasos</h2>
                <p style="margin:4px 0 0;font-size:13px;color:#64748b">Configura tu sitio en unos minutos. Esta guía desaparece cuando termines.</p>
            </div>
            <div style="text-align:right;min-width:120px">
                <div style="font-size:13px;color:#64748b;margin-bottom:4px">{{ $doneCount }} de {{ $total }} listos</div>
                <div style="width:120px;height:8px;background:#e2e8f0;border-radius:999px;overflow:hidden">
                    <div style="height:100%;width:{{ $total ? round($doneCount / $total * 100) : 0 }}%;background:#16a34a;border-radius:999px"></div>
                </div>
            </div>
        </div>

        <div style="margin-top:18px;display:grid;gap:10px">
            @foreach ($steps as $step)
                <a href="{{ $step['url'] }}"
                   style="display:flex;align-items:center;gap:14px;padding:12px 14px;border:1px solid #e2e8f0;border-radius:12px;text-decoration:none;transition:border-color .15s;{{ $step['done'] ? 'background:#f8fafc' : 'background:#fff' }}">
                    <span style="flex-shrink:0;width:34px;height:34px;border-radius:10px;display:flex;align-items:center;justify-content:center;{{ $step['done'] ? 'background:#dcfce7;color:#16a34a' : 'background:color-mix(in srgb,var(--primary-500,#2563eb) 12%,#fff);color:var(--primary-600,#2563eb)' }}">
                        @if ($step['done'])
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7"/></svg>
                        @else
                            <x-filament::icon :icon="$step['icon']" style="width:20px;height:20px" />
                        @endif
                    </span>
                    <span style="flex:1;min-width:0">
                        <span style="display:block;font-size:14px;font-weight:600;color:{{ $step['done'] ? '#94a3b8' : '#0f172a' }};{{ $step['done'] ? 'text-decoration:line-through' : '' }}">{{ $step['label'] }}</span>
                        <span style="display:block;font-size:12px;color:#94a3b8">{{ $step['description'] }}</span>
                    </span>
                    @unless ($step['done'])
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="#cbd5e1" stroke-width="2" style="flex-shrink:0"><path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7"/></svg>
                    @endunless
                </a>
            @endforeach
        </div>

        <div style="margin-top:14px;text-align:right">
            <button type="button" wire:click="dismiss" style="background:none;border:0;color:#94a3b8;font-size:13px;cursor:pointer;text-decoration:underline">No mostrar de nuevo</button>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
