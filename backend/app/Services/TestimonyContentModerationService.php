<?php

namespace App\Services;

use Illuminate\Support\Str;

class TestimonyContentModerationService
{
    public const PROFANITY_NOTE = 'Auto-rejected: inappropriate language detected.';

    public const GIBBERISH_NOTE = 'Auto-rejected: unreadable or gibberish content detected.';

    /**
     * @var array<int, string>
     */
    private const WHOLE_WORD_PROFANITY = [
        'fuck',
        'fucking',
        'shit',
        'bullshit',
        'bitch',
        'asshole',
        'bastard',
        'damn',
        'puta',
        'pota',
        'gago',
        'gaga',
        'bobo',
        'ulol',
        'tarantado',
        'leche',
        'lintik',
        'bwisit',
    ];

    /**
     * @var array<int, string>
     */
    private const COMPACT_PROFANITY = [
        'fuck',
        'fucking',
        'shit',
        'bullshit',
        'asshole',
        'putangina',
        'tangina',
        'pakyu',
        'punyeta',
    ];

    /**
     * @var array<int, string>
     */
    private const COMMON_GIBBERISH_FRAGMENTS = [
        'asdf',
        'qwer',
        'zxcv',
        'hjkl',
        'jkl;',
        'loremipsum',
    ];

    public function rejectionNote(?string $title, string $message): ?string
    {
        $text = trim(implode(' ', array_filter([
            $title,
            $message,
        ], fn ($value) => is_string($value) && trim($value) !== '')));

        if ($text === '') {
            return self::GIBBERISH_NOTE;
        }

        if ($this->containsProfanity($text)) {
            return self::PROFANITY_NOTE;
        }

        if ($this->looksLikeGibberish($text)) {
            return self::GIBBERISH_NOTE;
        }

        return null;
    }

    private function containsProfanity(string $text): bool
    {
        $normalized = $this->normalize($text);

        foreach (self::WHOLE_WORD_PROFANITY as $word) {
            if (preg_match('/(?<![a-z0-9])'.preg_quote($word, '/').'(?![a-z0-9])/i', $normalized) === 1) {
                return true;
            }
        }

        $compact = preg_replace('/[^a-z0-9]+/', '', $normalized) ?? '';

        foreach (self::COMPACT_PROFANITY as $word) {
            if (str_contains($compact, $word)) {
                return true;
            }
        }

        return false;
    }

    private function looksLikeGibberish(string $text): bool
    {
        $normalized = $this->normalize($text);
        $lettersOnly = preg_replace('/[^a-z]+/', '', $normalized) ?? '';

        if (strlen($lettersOnly) < 8) {
            return false;
        }

        if (preg_match('/([a-z])\1{5,}/', $lettersOnly) === 1) {
            return true;
        }

        $compact = preg_replace('/[^a-z0-9;]+/', '', $normalized) ?? '';

        foreach (self::COMMON_GIBBERISH_FRAGMENTS as $fragment) {
            if (str_contains($compact, $fragment)) {
                return true;
            }
        }

        $tokens = array_values(array_filter(
            preg_split('/[^a-z]+/', $normalized) ?: [],
            fn (string $token) => strlen($token) >= 6
        ));

        if ($tokens === []) {
            return false;
        }

        $suspiciousTokens = collect($tokens)->filter(function (string $token): bool {
            $length = strlen($token);
            $vowelCount = preg_match_all('/[aeiou]/', $token);
            $vowelRatio = $length > 0 ? $vowelCount / $length : 0;

            return $vowelRatio < 0.18 || preg_match('/[bcdfghjklmnpqrstvwxyz]{6,}/', $token) === 1;
        })->count();

        return $suspiciousTokens >= 2
            || ($suspiciousTokens === 1 && count($tokens) === 1 && strlen($tokens[0]) >= 10);
    }

    private function normalize(string $text): string
    {
        $normalized = Str::lower(Str::ascii($text));

        return strtr($normalized, [
            '@' => 'a',
            '4' => 'a',
            '!' => 'i',
            '1' => 'i',
            '0' => 'o',
            '$' => 's',
            '5' => 's',
            '7' => 't',
            '3' => 'e',
        ]);
    }
}
