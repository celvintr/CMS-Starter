<section class="max-w-3xl mx-auto px-4 py-20 md:py-24">
    <div class="text-center mb-12">
        <span class="eyebrow justify-center">FAQ</span>
        <h2 class="section-title mt-4">{{ $data['heading'] ?? 'Preguntas frecuentes' }}</h2>
    </div>

    <div class="space-y-3">
        @foreach (($data['items'] ?? []) as $item)
            <details class="group card p-0 overflow-hidden hover:!translate-y-0">
                <summary class="flex items-center justify-between gap-4 cursor-pointer list-none px-6 py-4 font-semibold" style="color: var(--brand-ink)">
                    {{ $item['question'] ?? '' }}
                    <svg viewBox="0 0 24 24" class="h-5 w-5 flex-none text-brand transition-transform group-open:rotate-45" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
                </summary>
                <div class="px-6 pb-5 -mt-1 text-slate-600 leading-relaxed">{{ $item['answer'] ?? '' }}</div>
            </details>
        @endforeach
    </div>
</section>
