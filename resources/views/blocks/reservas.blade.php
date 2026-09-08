@php $data = $data ?? []; @endphp
@if (\App\Support\Features::enabled('reservas'))
    <section class="max-w-3xl mx-auto px-4 py-16">
        <div class="text-center mb-8">
            <h2 class="font-display text-3xl md:text-4xl font-extrabold" style="color: var(--brand-ink)">
                {{ $data['heading'] ?? 'Reserva tu cita' }}
            </h2>
            @if (! empty($data['subheading']))
                <p class="mt-3 text-slate-500 max-w-xl mx-auto leading-relaxed">{{ $data['subheading'] }}</p>
            @endif
        </div>

        <form method="POST" action="{{ route('reservation.store') }}" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 space-y-4">
            @csrf
            <input type="hidden" name="source" value="bloque">
            {{-- Trampa anti-spam --}}
            <input type="text" name="_gotcha" class="hidden" tabindex="-1" autocomplete="off" aria-hidden="true">

            @if (isset($errors) && $errors->any())
                <div class="rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-2.5">{{ $errors->first() }}</div>
            @endif

            <div class="grid gap-4 md:grid-cols-2">
                <input type="text" name="name" placeholder="Tu nombre *" required value="{{ old('name') }}"
                       class="w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:ring-2 focus:ring-brand/40 focus:border-brand outline-none">
                <input type="email" name="email" placeholder="Tu correo *" required value="{{ old('email') }}"
                       class="w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:ring-2 focus:ring-brand/40 focus:border-brand outline-none">
                <input type="text" name="phone" placeholder="Teléfono" value="{{ old('phone') }}"
                       class="w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:ring-2 focus:ring-brand/40 focus:border-brand outline-none">
                @if (! empty($data['services']))
                    <select name="service" class="w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:ring-2 focus:ring-brand/40 focus:border-brand outline-none">
                        <option value="">Servicio…</option>
                        @foreach (preg_split('/\r\n|\r|\n/', $data['services']) as $svc)
                            @php $svc = trim($svc); @endphp
                            @if ($svc !== '')<option value="{{ $svc }}" @selected(old('service') === $svc)>{{ $svc }}</option>@endif
                        @endforeach
                    </select>
                @else
                    <input type="text" name="service" placeholder="Servicio (opcional)" value="{{ old('service') }}"
                           class="w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:ring-2 focus:ring-brand/40 focus:border-brand outline-none">
                @endif
                <input type="date" name="date" required value="{{ old('date') }}" min="{{ now()->format('Y-m-d') }}"
                       data-res-date
                       class="w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:ring-2 focus:ring-brand/40 focus:border-brand outline-none">
                <select name="time" required data-res-time
                        class="w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:ring-2 focus:ring-brand/40 focus:border-brand outline-none">
                    <option value="">Elige una fecha primero…</option>
                </select>
            </div>
            <textarea name="notes" rows="3" placeholder="¿Algo que debamos saber? (opcional)"
                      class="w-full rounded-xl border border-slate-300 px-4 py-2.5 focus:ring-2 focus:ring-brand/40 focus:border-brand outline-none">{{ old('notes') }}</textarea>

            <button type="submit" class="btn-primary w-full">
                {{ $data['button_text'] ?? 'Solicitar reserva' }}
            </button>
            <p class="text-xs text-slate-400 text-center">Recibirás confirmación por correo o teléfono.</p>
        </form>

        <script>
            (function () {
                var dateEl = document.querySelector('[data-res-date]');
                var timeEl = document.querySelector('[data-res-time]');
                if (!dateEl || !timeEl) return;

                function loadSlots() {
                    var d = dateEl.value;
                    timeEl.innerHTML = '<option value="">Cargando…</option>';
                    if (!d) { timeEl.innerHTML = '<option value="">Elige una fecha primero…</option>'; return; }

                    fetch('{{ route('reservation.slots') }}?date=' + encodeURIComponent(d), { headers: { 'Accept': 'application/json' } })
                        .then(function (r) { return r.json(); })
                        .then(function (data) {
                            var slots = (data && data.slots) || [];
                            if (!slots.length) {
                                timeEl.innerHTML = '<option value="">Sin horarios disponibles</option>';
                                return;
                            }
                            timeEl.innerHTML = '<option value="">Elige un horario…</option>' +
                                slots.map(function (s) { return '<option value="' + s + '">' + s + '</option>'; }).join('');
                        })
                        .catch(function () { timeEl.innerHTML = '<option value="">No se pudieron cargar los horarios</option>'; });
                }

                dateEl.addEventListener('change', loadSlots);
                if (dateEl.value) loadSlots();
            })();
        </script>
    </section>
@endif
