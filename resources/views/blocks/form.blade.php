@php
    $mod = \App\Models\Module::firstWhere('slug', $data['module'] ?? null);
@endphp

@if ($mod && $mod->type === 'formulario')
    <section id="formulario-{{ $mod->slug }}" class="py-16 bg-slate-50">
        <div class="max-w-2xl mx-auto px-4">
            <div class="text-center mb-8">
                <h2 class="text-3xl font-extrabold" style="color: var(--brand-ink)">{{ $data['heading'] ?? $mod->pluralLabel() }}</h2>
                @if (!empty($data['subheading']))
                    <p class="mt-2 text-slate-600">{{ $data['subheading'] }}</p>
                @endif
            </div>

            <form method="POST" action="{{ route('form.submit', $mod->slug) }}"
                  class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 space-y-4">
                @csrf

                @if ($errors->any())
                    <div class="rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-2">
                        Revisa los campos: {{ $errors->first() }}
                    </div>
                @endif

                @foreach ($mod->fieldList() as $field)
                    @php
                        $key = $field['key'];
                        $name = 'data[' . $key . ']';
                        $old = old('data.' . $key);
                        $req = ! empty($field['required']);
                        $inputClass = 'w-full rounded-lg border border-slate-300 px-3 py-2 focus:ring-2 focus:ring-brand focus:border-brand outline-none';
                    @endphp

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            {{ $field['label'] ?? $key }}@if ($req) <span class="text-red-500">*</span>@endif
                        </label>

                        @switch($field['type'] ?? 'text')
                            @case('textarea')
                            @case('richtext')
                                <textarea name="{{ $name }}" rows="4" @if($req) required @endif class="{{ $inputClass }}">{{ $old }}</textarea>
                                @break

                            @case('select')
                                <select name="{{ $name }}" @if($req) required @endif class="{{ $inputClass }}">
                                    <option value="">Seleccionar…</option>
                                    @foreach (\App\Filament\Resources\EntryResource::optionsFor($field) as $opt)
                                        <option value="{{ $opt }}" @selected($old === $opt)>{{ $opt }}</option>
                                    @endforeach
                                </select>
                                @break

                            @case('email')
                                <input type="email" name="{{ $name }}" value="{{ $old }}" @if($req) required @endif class="{{ $inputClass }}">
                                @break

                            @case('number')
                                <input type="number" step="any" name="{{ $name }}" value="{{ $old }}" @if($req) required @endif class="{{ $inputClass }}">
                                @break

                            @case('date')
                                <input type="date" name="{{ $name }}" value="{{ $old }}" @if($req) required @endif class="{{ $inputClass }}">
                                @break

                            @case('boolean')
                                <label class="inline-flex items-center gap-2">
                                    <input type="checkbox" name="{{ $name }}" value="1" @checked($old) class="rounded border-slate-300">
                                    <span class="text-sm text-slate-600">Sí</span>
                                </label>
                                @break

                            @default
                                <input type="text" name="{{ $name }}" value="{{ $old }}" @if($req) required @endif class="{{ $inputClass }}">
                        @endswitch
                    </div>
                @endforeach

                <button type="submit" class="w-full py-3 rounded-lg text-white font-semibold hover:opacity-90 transition" style="background: var(--brand)">
                    {{ $data['button_text'] ?? 'Enviar' }}
                </button>
            </form>
        </div>
    </section>
@endif
