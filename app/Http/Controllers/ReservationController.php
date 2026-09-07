<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Support\Features;
use App\Support\Notifier;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReservationController extends Controller
{
    public function store(Request $request)
    {
        if (! Features::enabled('reservas')) {
            return back();
        }

        // Trampa anti-spam.
        if ($request->filled('_gotcha')) {
            return back()->with('sent', '¡Gracias! Tu solicitud fue recibida.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:40'],
            'service' => ['nullable', 'string', 'max:120'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'time' => ['required', 'date_format:H:i'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $startsAt = Carbon::parse($data['date'] . ' ' . $data['time']);

        $reservation = Reservation::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'service' => $data['service'] ?? null,
            'starts_at' => $startsAt,
            'notes' => $data['notes'] ?? null,
            'status' => 'pending',
            'source' => $request->input('source', 'sitio'),
        ]);

        // Aviso al negocio por el canal de formularios (si está configurado).
        Notifier::forms('Reserva de ' . $reservation->name, [
            ['Nombre', $reservation->name],
            ['Correo', $reservation->email],
            ['Teléfono', $reservation->phone],
            ['Servicio', $reservation->service],
            ['Fecha y hora', $startsAt->format('d/m/Y H:i')],
            ['Nota', $reservation->notes],
        ], 'Reservas');

        return back()->with('sent', '¡Gracias! Tu solicitud de reserva fue recibida. Te contactaremos para confirmarla.');
    }
}
