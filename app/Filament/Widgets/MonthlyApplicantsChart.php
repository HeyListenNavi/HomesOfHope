<?php

namespace App\Filament\Widgets;

use App\Models\Applicant;
use Filament\Widgets\ChartWidget;

class MonthlyApplicantsChart extends ChartWidget
{
    protected static ?int $sort = 2;

    protected static ?string $maxHeight = '300px';

    protected static ?string $heading = 'Distribución de Estatus';

    protected function getData(): array
    {
        // 1. Obtenemos los conteos de la BD en una sola consulta agrupada
        $data = Applicant::query()
            ->selectRaw('process_status, count(*) as count')
            ->groupBy('process_status')
            ->pluck('count', 'process_status')
            ->toArray();

        // 2. Definimos la configuración visual para cada estado posible
        $statuses = [
            'staff_approved'    => ['label' => 'Staff: Aprobado', 'color' => '#15803d'], // Verde Oscuro
            'approved'          => ['label' => 'IA: Aprobado',    'color' => '#4ade80'], // Verde Claro
            'in_progress'       => ['label' => 'En Progreso',     'color' => '#3b82f6'], // Azul
            'requires_revision' => ['label' => 'Revisión Manual', 'color' => '#f59e0b'], // Naranja
            'rejected'          => ['label' => 'IA: Rechazado',   'color' => '#f87171'], // Rojo Claro
            'staff_rejected'    => ['label' => 'Staff: Rechazado','color' => '#b91c1c'], // Rojo Oscuro
            'canceled'          => ['label' => 'Cancelado',       'color' => '#9ca3af'], // Gris
        ];

        // 3. Construimos los arrays finales asegurando el orden
        $labels = [];
        $counts = [];
        $colors = [];

        foreach ($statuses as $key => $config) {
            // Solo mostramos en la gráfica si hay al menos 1 caso (opcional)
            // Si quieres mostrar ceros, quita el 'if'
            $count = $data[$key] ?? 0;

            if ($count > 0 || in_array($key, ['in_progress', 'requires_revision'])) {
                $labels[] = $config['label'];
                $counts[] = $count;
                $colors[] = $config['color'];
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Solicitantes',
                    'data' => $counts,
                    'backgroundColor' => $colors,
                    'hoverOffset' => 4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'right', // 'right' suele verse mejor en pies pequeños
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut'; // 'doughnut' suele verse más moderno que 'pie', pero puedes regresar a 'pie'
    }
}
