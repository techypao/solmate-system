<?php

namespace App\Support;

use App\Rules\ContainsSpecialCharacter;
use App\Rules\ContainsUppercaseLetter;

class PasswordValidation
{
    public static function required(): array
    {
        return array_merge(['required'], self::rules());
    }

    public static function nullable(): array
    {
        return array_merge(['nullable'], self::rules());
    }

    public static function messages(string $field = 'password'): array
    {
        return [
            "{$field}.min" => 'Password must be at least 8 characters.',
            "{$field}.confirmed" => 'Password confirmation does not match.',
        ];
    }

    public static function rules(): array
    {
        return [
            'string',
            'min:8',
            'confirmed',
            new ContainsUppercaseLetter(),
            new ContainsSpecialCharacter(),
        ];
    }
}
