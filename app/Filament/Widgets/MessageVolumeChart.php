<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\HasDatePeriod;
use App\Models\Message;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class MessageVolumeChart extends ChartWidget
{
    use HasDatePeriod;

    protected static ?string $heading = 'Tráfico de Mensajes';

    protected static string $view = 'filament.widgets.date-period-chart-widget';

    protected static ?int $sort = 5;

    protected static ?string $maxHeight = '300px';

    protected static string $color = 'info';

    protected function getData(): array
    {
        [$start, $end] = $this->getPeriodDateRange();

        $per = match ($this->filter) {
            'week' => 'perDay',
            'month' => 'perDay',
            'year' => 'perMonth',
        };

        $data = Trend::model(Message::class)
            ->between(start: $start, end: $end)
            ->{$per}()
            ->count();

        $dateFormat = match ($this->filter) {
            'week' => 'D',
            'month' => 'd M',
            'year' => 'M',
        };

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
            'labels' => $data->map(fn (TrendValue $value) => Carbon::parse($value->date)->format($dateFormat))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
