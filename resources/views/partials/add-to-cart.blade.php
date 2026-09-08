@php
    /** @var \App\Models\Entry $entry */
    $mod = $entry->module;
    $detailUrl = $mod && $mod->is_public ? route('module.show', [$mod->slug, $entry->slug]) : null;
    $hasVariants = $entry->hasVariants();
    $tracks = $entry->tracksStock();
    $soldOut = $hasVariants ? $entry->allVariantsSoldOut() : ($tracks && (int) $entry->stock <= 0);
    $low = ! $hasVariants && $tracks && (int) $entry->stock > 0 && (int) $entry->stock <= 5;
@endphp
<div class="px-6 pb-6 mt-auto">
    @if ($soldOut)
        <button type="button" disabled
                class="w-full py-2.5 rounded-xl bg-slate-100 text-slate-400 font-semibold text-sm cursor-not-allowed">
            Agotado
        </button>
    @elseif ($hasVariants && $detailUrl)
        {{-- Con variantes se elige la opción en la ficha del producto. --}}
        <a href="{{ $detailUrl }}"
           class="block w-full py-2.5 rounded-xl bg-brand text-white font-semibold text-sm text-center hover:opacity-90 transition">
            Ver opciones
        </a>
    @else
        <form method="POST" action="{{ route('cart.add', $entry->id) }}">
            @csrf
            <button type="submit" class="w-full py-2.5 rounded-xl bg-brand text-white font-semibold text-sm hover:opacity-90 transition">
                Agregar al carrito
            </button>
        </form>
        @if ($low)
            <p class="mt-2 text-xs text-amber-600 text-center font-medium">Solo quedan {{ $entry->stock }}</p>
        @endif
    @endif
</div>
