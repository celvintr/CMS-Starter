<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:40'],
            'message' => ['required', 'string', 'max:3000'],
        ]);

        $data['source_url'] = $request->input('source_url', url()->previous());

        ContactMessage::create($data);

        return back()->with('sent', '¡Gracias! Tu mensaje fue enviado. Te contactaremos pronto.');
    }
}
