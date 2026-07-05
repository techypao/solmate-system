<?php

namespace App\Support;

class NameValidation
{
    private const REGEX_RULE = "regex:/^(?=.*\\p{L})[\\p{L}\\s.'-]+$/u";

    public static function rules(): array
    {
        return [
            'required',
            'string',
            'max:255',
            self::REGEX_RULE,
        ];
    }

    public static function messages(): array
    {
        return [
            'first_name.regex' => 'First name may only contain letters, spaces, periods, apostrophes, and hyphens.',
            'last_name.regex' => 'Last name may only contain letters, spaces, periods, apostrophes, and hyphens.',
        ];
    }

    public static function normalize(string $value): string
    {
        $value = trim(preg_replace('/\s+/u', ' ', $value) ?? $value);

        if (function_exists('mb_convert_case')) {
            return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
        }

        return ucwords(strtolower($value));
    }
}
