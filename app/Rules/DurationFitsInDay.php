<?php

namespace App\Rules;

use App\Support\ScheduleTimes;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\ValidatorAwareRule;
use Illuminate\Validation\Validator;

class DurationFitsInDay implements ValidationRule, ValidatorAwareRule
{
    private Validator $validator;

    public function setValidator(Validator $validator): static
    {
        $this->validator = $validator;

        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_numeric($value)) {
            return;
        }

        $start = $this->startTimeFor($attribute);

        if (! is_string($start) || $start === '') {
            return;
        }

        if (! ScheduleTimes::endsWithinDay($start, (int) $value)) {
            $fail('La actividad no puede terminar al día siguiente. Adelanta la hora de inicio o reduce la duración.');
        }
    }

    private function startTimeFor(string $attribute): mixed
    {
        if (preg_match('/^(items\.\d+)\.duration_minutes$/', $attribute, $matches) === 1) {
            return data_get($this->validator->getData(), $matches[1].'.start_time');
        }

        return $this->validator->getData()['start_time'] ?? null;
    }
}
