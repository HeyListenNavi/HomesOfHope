<?php

namespace App\Filament\Widgets;

use App\Models\Message;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class MessageVolumeChart extends ChartWidget
{
    protected static ?string $heading = 'Tráfico de Mensajes (Últimos 14 días)';
    protected static ?int $sort = 5;
    protected static ?string $maxHeight = '300px';
    protected static string $color = 'info';

    protected function getData(): array
    {
        $data = Trend::model(Message::class)
            ->between(
                start: now()->subDays(13),
                end: now(),
            )
            ->perDay()
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Mensajes Totales',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate)->toArray(),
                    'fill' => 'start',
                    'tension' => 0.4,
                    'pointRadius' => 2,
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => \Carbon\Carbon::parse($value->date)->format('d M'))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
