<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ContainsSpecialCharacter implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value) || preg_match('/[^A-Za-z0-9]/', $value) !== 1) {
            $fail('Password must contain at least one special character.');
        }
    }
}
