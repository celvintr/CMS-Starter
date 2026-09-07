<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use App\Support\Features;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    /**
     * Baja de la lista mediante enlace firmado (incluido en cada campaña).
     */
    public function unsubscribe(Subscriber $subscriber)
    {
        $subscriber->update(['is_active' => false]);

        return view('newsletter.baja', compact('subscriber'));
    }

    public function subscribe(Request $request)
    {
        if (! Features::enabled('newsletter')) {
            return back();
        }

        // Trampa anti-spam: si el campo oculto viene lleno, fingimos éxito.
        if ($request->filled('_gotcha')) {
            return back()->with('sent', '¡Gracias por suscribirte!');
        }

        $data = $request->validate([
            'email' => ['required', 'email', 'max:150'],
            'name' => ['nullable', 'string', 'max:120'],
        ]);

        Subscriber::updateOrCreate(
            ['email' => mb_strtolower(trim($data['email']))],
            [
                'name' => $data['name'] ?? null,
                'is_active' => true,
                'source' => $request->input('source', 'sitio'),
            ],
        );

        return back()->with('sent', '¡Gracias por suscribirte!');
    }
}
