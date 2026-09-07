<?php

namespace App\Http\Controllers;

use App\Models\Entry;
use App\Models\Module;
use Illuminate\Http\Request;

class SubmissionController extends Controller
{
    public function store(Request $request, Module $module)
    {
        abort_unless($module->isForm(), 404);

        // Reglas de validación dinámicas según los campos del formulario.
        $rules = [];
        foreach ($module->fieldList() as $field) {
            $key = $field['key'];
            $rule = ! empty($field['required']) ? ['required'] : ['nullable'];

            match ($field['type'] ?? 'text') {
                'email' => $rule[] = 'email',
                'number' => $rule[] = 'numeric',
                default => null,
            };

            $rules['data.' . $key] = $rule;
        }

        $request->validate($rules);

        $data = $request->input('data', []);

        // El título del registro será el primer campo de texto/correo (para identificarlo en el panel).
        $titleField = $module->fieldList()->first(fn ($f) => in_array($f['type'] ?? '', ['text', 'email']));
        $title = $titleField ? ($data[$titleField['key']] ?? null) : null;

        Entry::create([
            'module_id' => $module->id,
            'title' => is_string($title) && $title !== '' ? $title : 'Envío de formulario',
            'data' => $data,
            'is_published' => true,
        ]);

        return back()->with('sent', '¡Gracias! Tu información fue enviada correctamente.');
    }
}
