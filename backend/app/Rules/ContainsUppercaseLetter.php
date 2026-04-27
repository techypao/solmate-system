<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ContainsUppercaseLetter implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value) || preg_match('/[A-Z]/', $value) !== 1) {
            $fail('Password must contain at least one uppercase letter.');
        }
    }
}
