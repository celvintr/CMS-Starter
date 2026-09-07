<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Support\Notifier;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        // Honeypot: si un bot llenó el campo oculto, fingimos éxito y no guardamos.
        if ($request->filled('_gotcha')) {
            return back()->with('sent', '¡Gracias! Tu mensaje fue enviado.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:40'],
            'message' => ['required', 'string', 'max:3000'],
        ]);

        $data['source_url'] = $request->input('source_url', url()->previous());

        ContactMessage::create($data);

        Notifier::forms('Contacto — ' . $data['name'], [
            ['Nombre', $data['name']],
            ['Teléfono', $data['phone'] ?? null],
            ['Correo', $data['email'] ?? null],
            ['Mensaje', $data['message']],
        ], $data['source_url']);

        return back()->with('sent', '¡Gracias! Tu mensaje fue enviado. Te contactaremos pronto.');
    }
}
