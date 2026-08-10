<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

class UniqueFamilyCurp implements ValidationRule
{
    public function __construct(protected array $members, protected int $currentIndex) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (empty($value)) {
            return;
        }

        $normalizedValue = Str::upper(trim((string) $value));

        foreach ($this->members as $index => $member) {
            if ($index === $this->currentIndex) {
                continue;
            }

            $memberCurp = Str::upper(trim($member['curp'] ?? ''));
            if ($memberCurp !== '' && $memberCurp === $normalizedValue) {
                $fail('Este CURP está repetido en otro familiar de la lista.');

                return;
            }
        }
    }
}
