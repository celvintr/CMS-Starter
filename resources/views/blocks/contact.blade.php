<section id="contacto" class="py-20 md:py-24">
    <div class="max-w-2xl mx-auto px-4">
        <div class="text-center mb-10">
            <span class="eyebrow">Contacto</span>
            <h2 class="section-title mt-4">{{ $data['heading'] ?? 'Contáctanos' }}</h2>
            @if (!empty($data['subheading']))
                <p class="mt-3 text-slate-500 text-lg">{{ $data['subheading'] }}</p>
            @endif
        </div>

        <form action="{{ route('contact.store') }}" method="POST" class="card p-7 md:p-8 space-y-5 hover:!translate-y-0">
            @csrf
            <input type="hidden" name="source_url" value="{{ url()->current() }}">

            @if ($errors->any())
                <div class="rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-2.5">
                    Revisa los campos: {{ $errors->first() }}
                </div>
            @endif

            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Nombre *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:ring-2 focus:ring-brand/40 focus:border-brand outline-none transition">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Teléfono</label>
                    <input type="text" name="phone" value="{{ old('phone') }}"
                           class="w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:ring-2 focus:ring-brand/40 focus:border-brand outline-none transition">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Correo</label>
                <input type="email" name="email" value="{{ old('email') }}"
                       class="w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:ring-2 focus:ring-brand/40 focus:border-brand outline-none transition">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Mensaje *</label>
                <textarea name="message" rows="4" required
                          class="w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:ring-2 focus:ring-brand/40 focus:border-brand outline-none transition">{{ old('message') }}</textarea>
            </div>
            <button type="submit" class="btn-primary w-full">Enviar mensaje</button>
        </form>
    </div>
</section>
