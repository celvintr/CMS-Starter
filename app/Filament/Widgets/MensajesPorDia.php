<?php

namespace App\Filament\Widgets;

use App\Models\ContactMessage;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class MensajesPorDia extends ChartWidget
{
    protected static ?string $heading = 'Mensajes recibidos (últimos 14 días)';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $dias = collect(range(13, 0))->map(fn ($i) => Carbon::today()->subDays($i));

        return [
            'datasets' => [
                [
                    'label' => 'Mensajes',
                    'data' => $dias->map(fn ($d) => ContactMessage::whereDate('created_at', $d)->count())->all(),
                    'backgroundColor' => 'rgba(37, 99, 235, 0.45)',
                    'borderColor' => 'rgb(37, 99, 235)',
                    'borderRadius' => 6,
                ],
            ],
            'labels' => $dias->map(fn ($d) => $d->format('d/m'))->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
