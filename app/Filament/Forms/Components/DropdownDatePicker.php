<?php

namespace App\Filament\Forms\Components;

use Carbon\Carbon;
use Closure;
use Filament\Forms\Components\Concerns\CanBeReadOnly;
use Filament\Forms\Components\Concerns\HasAffixes;
use Filament\Forms\Components\Concerns\HasExtraInputAttributes;
use Filament\Forms\Components\Concerns\HasPlaceholder;
use Filament\Forms\Components\Field;
use Filament\Support\Concerns\HasExtraAlpineAttributes;

class DropdownDatePicker extends Field
{
    use CanBeReadOnly;
    use HasAffixes;
    use HasExtraAlpineAttributes;
    use HasExtraInputAttributes;
    use HasPlaceholder;

    protected string $view = 'filament.forms.components.dropdown-date-picker';

    protected ?int $minYear = null;

    protected ?int $maxYear = null;

    protected int $yearsBack = 100;

    protected bool $showAge = false;

    protected string|Closure|null $format = 'Y-m-d';

    protected string|Closure|null $displayFormat = 'Y-m-d';

    protected array|Closure|null $years = null;

    protected array|Closure|null $months = null;

    protected bool|Closure $isNative = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->default(static fn (): string => now()->format('Y-m-d'));
    }

    public function minYear(?int $year): static
    {
        $this->minYear = $year;

        return $this;
    }

    public function maxYear(?int $year): static
    {
        $this->maxYear = $year;

        return $this;
    }

    public function yearsBack(int $years): static
    {
        $this->yearsBack = $years;

        return $this;
    }

    public function showAge(bool $show = true): static
    {
        $this->showAge = $show;

        return $this;
    }

    public function format(string|Closure|null $format): static
    {
        $this->format = $format;

        return $this;
    }

    public function displayFormat(string|Closure|null $format): static
    {
        $this->displayFormat = $format;

        return $this;
    }

    public function years(array|Closure|null $years): static
    {
        $this->years = $years;

        return $this;
    }

    public function months(array|Closure|null $months): static
    {
        $this->months = $months;

        return $this;
    }

    public function native(bool|Closure $condition = true): static
    {
        $this->isNative = $condition;

        return $this;
    }

    public function getMinYear(): int
    {
        $currentYear = (int) date('Y');

        return $this->minYear ?? ($currentYear - $this->yearsBack);
    }

    public function getMaxYear(): int
    {
        $currentYear = (int) date('Y');

        return $this->maxYear ?? ($currentYear + 10);
    }

    public function shouldShowAge(): bool
    {
        return $this->showAge;
    }

    public function getFormat(): string
    {
        return $this->evaluate($this->format) ?? 'Y-m-d';
    }

    public function getDisplayFormat(): string
    {
        return $this->evaluate($this->displayFormat) ?? 'Y-m-d';
    }

    public function getYears(): array
    {
        if ($this->years !== null) {
            return $this->evaluate($this->years);
        }

        $minYear = $this->getMinYear();
        $maxYear = $this->getMaxYear();

        $years = range($maxYear, $minYear);

        return array_combine($years, $years);
    }

    public function getMonths(): array
    {
        if ($this->months !== null) {
            return $this->evaluate($this->months);
        }

        $locale = app()->getLocale();
        $months = [];

        for ($m = 1; $m <= 12; $m++) {
            $key = str_pad((string) $m, 2, '0', STR_PAD_LEFT);
            $date = Carbon::create(2000, $m, 1);
            $months[$key] = ucfirst($date->locale($locale)->translatedFormat('F'));
        }

        return $months;
    }

    public function isNative(): bool
    {
        return (bool) $this->evaluate($this->isNative);
    }
}
