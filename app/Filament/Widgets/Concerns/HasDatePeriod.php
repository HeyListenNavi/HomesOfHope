<?php

namespace App\Filament\Widgets\Concerns;

trait HasDatePeriod
{
    public int $offset = 0;

    public function previous(): void
    {
        $this->offset--;
    }

    public function next(): void
    {
        if ($this->offset < 0) {
            $this->offset++;
        }
    }

    public function updatedFilter(): void
    {
        $this->offset = 0;
    }

    public function isAtCurrentPeriod(): bool
    {
        return $this->offset >= 0;
    }

    protected function getPeriodDateRange(): array
    {
        $now = now();

        return match ($this->filter) {
            'week' => [
                $now->copy()->addWeeks($this->offset)->startOfWeek(),
                $now->copy()->addWeeks($this->offset)->endOfWeek(),
            ],
            'month' => [
                $now->copy()->addMonths($this->offset)->startOfMonth(),
                $now->copy()->addMonths($this->offset)->endOfMonth(),
            ],
            'year' => [
                $now->copy()->addYears($this->offset)->startOfYear(),
                $now->copy()->addYears($this->offset)->endOfYear(),
            ],
            default => [
                $now->copy()->addMonths($this->offset)->startOfMonth(),
                $now->copy()->addMonths($this->offset)->endOfMonth(),
            ],
        };
    }

    protected function getPeriodLabel(): string
    {
        $date = match ($this->filter) {
            'week' => now()->addWeeks($this->offset),
            'month' => now()->addMonths($this->offset),
            'year' => now()->addYears($this->offset),
            default => now()->addMonths($this->offset),
        };

        return match ($this->filter) {
            'week' => 'Semana del '.$date->startOfWeek()->isoFormat('D MMM'),
            'month' => ucfirst($date->isoFormat('MMMM YYYY')),
            'year' => (string) $date->year,
            default => ucfirst($date->isoFormat('MMMM YYYY')),
        };
    }

    protected function getFilters(): ?array
    {
        return [
            'week' => 'Semanal',
            'month' => 'Mensual',
            'year' => 'Anual',
        ];
    }
}
