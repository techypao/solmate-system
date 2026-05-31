<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class ChatbotReplyService
{
    public const SCOPE_FALLBACK = 'I can currently help with SolMate quotations, inspection requests, service requests, testimonies, notifications, and general app guidance.';

    private const MAX_FOLLOW_UP_SUGGESTIONS = 4;

    private const MIN_FOLLOW_UP_SUGGESTIONS = 2;

    private const SYSTEM_INSTRUCTION = <<<'TEXT'
You are SolMate Assistant for the SolMate customer app and website.

Your role:
- Help customers understand SolMate features and processes.
- Help customers with basic solar education relevant to SolMate.
- Answer only questions related to the SolMate app, website, and customer workflows.
- Give concise, clear, friendly, and practical answers.

Important rules:
- The customer begins with a pre-inspection estimate.
- The pre-inspection estimate is only a guide and may change after actual inspection.
- The inspection-based quotation is created by the technician after inspection and technical assessment.
- Do not claim that you can view live account data.
- Do not invent system features.
- Do not pretend to be an admin, technician, or human support representative.
- If the question is unrelated to SolMate and unrelated to basic solar customer education, reply with exactly: "I can currently help with SolMate quotations, inspection requests, service requests, testimonies, notifications, and general app guidance."

Response style:
- Be concise, clear, and friendly.
- Default to 1 to 3 short sentences.
- Put the main answer first.
TEXT;

    public function send(string $message): array
    {
        $apiKey = trim((string) config('services.gemini.api_key'));
        $model = trim((string) config('services.gemini.model', 'gemini-2.5-flash'));
        $offlineReply = $this->buildOfflineReply($message);

        if ($apiKey === '') {
            return $offlineReply;
        }

        try {
            $response = Http::timeout(20)
                ->acceptJson()
                ->withHeaders([
                    'x-goog-api-key' => $apiKey,
                ])
                ->post(
                    sprintf('https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent', $model),
                    $this->buildRequestBody($message),
                );

            if (! $response->successful()) {
                return $offlineReply['text'] !== ''
                    ? $offlineReply
                    : throw new RuntimeException($response->json('error.message') ?: 'SolBot could not process the request.');
            }

            $reply = $this->extractChatbotReply($response->json(), $message);

            if ($reply['text'] === '') {
                return $offlineReply['text'] !== ''
                    ? $offlineReply
                    : throw new RuntimeException('SolBot returned an empty response.');
            }

            return $reply;
        } catch (Throwable $throwable) {
            if ($offlineReply['text'] !== '') {
                return $offlineReply;
            }

            throw $throwable;
        }
    }

    private function buildRequestBody(string $message): array
    {
        return [
            'systemInstruction' => [
                'parts' => [
                    ['text' => self::SYSTEM_INSTRUCTION],
                ],
            ],
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        [
                            'text' => trim(<<<TEXT
User question:
{$message}

Return valid JSON only in this exact shape:
{"answer":"string","suggestions":["string"]}

Rules:
- "answer" must stay concise.
- "suggestions" must contain 2 to 4 short related follow-up questions.
- If the question is outside scope, set "answer" to exactly "{self::SCOPE_FALLBACK}" and provide 2 to 4 short SolMate-related follow-up questions.
TEXT),
                        ],
                    ],
                ],
            ],
            'generationConfig' => [
                'temperature' => 0.3,
                'maxOutputTokens' => 480,
            ],
            'store' => false,
        ];
    }

    private function extractChatbotReply(array|string|null $payload, string $originalMessage): array
    {
        $rawText = $this->extractGeminiText($payload);
        $parsedReply = $this->parseStructuredReply($rawText);
        $extractedAnswer = $this->extractJsonStringField($rawText, 'answer');
        $parsedAnswer = is_string($parsedReply['answer'] ?? null)
            ? trim((string) $parsedReply['answer'])
            : ($extractedAnswer !== '' ? $extractedAnswer : trim($rawText));
        $rescuedAnswer = $parsedAnswer === self::SCOPE_FALLBACK
            ? $this->getBasicSolarFallbackAnswer($originalMessage)
            : '';
        $answer = $rescuedAnswer !== '' ? $rescuedAnswer : $parsedAnswer;

        return [
            'text' => $answer,
            'suggestions' => $this->buildFollowUpSuggestions(
                $originalMessage,
                $answer,
                $parsedReply['suggestions'] ?? null,
            ),
            'is_fallback' => $answer === self::SCOPE_FALLBACK,
        ];
    }

    private function extractGeminiText(array|string|null $payload): string
    {
        if (! is_array($payload)) {
            return '';
        }

        $parts = $payload['candidates'][0]['content']['parts'] ?? [];

        return collect($parts)
            ->map(fn ($part) => is_string($part['text'] ?? null) ? trim($part['text']) : '')
            ->filter()
            ->implode("\n");
    }

    private function parseStructuredReply(string $value): ?array
    {
        if ($value === '') {
            return null;
        }

        $normalized = $this->stripCodeFences($value);

        try {
            $parsed = json_decode($normalized, true, 512, JSON_THROW_ON_ERROR);

            return is_array($parsed) ? $parsed : null;
        } catch (\JsonException) {
            return null;
        }
    }

    private function stripCodeFences(string $value): string
    {
        return trim((string) preg_replace('/^```(?:json)?\s*|\s*```$/i', '', $value));
    }

    private function extractJsonStringField(string $text, string $fieldName): string
    {
        $pattern = '~"'.preg_quote($fieldName, '~').'"\s*:\s*"((?:\\\\.|[^"\\\\])*)"~s';

        if (! preg_match($pattern, $this->stripCodeFences($text), $matches)) {
            return '';
        }

        try {
            $decoded = json_decode('"'.$matches[1].'"', true, 512, JSON_THROW_ON_ERROR);

            return is_string($decoded) ? trim(preg_replace('/\s+/', ' ', $decoded) ?? '') : '';
        } catch (\JsonException) {
            return trim(preg_replace('/\s+/', ' ', stripcslashes($matches[1])) ?? '');
        }
    }

    private function buildFollowUpSuggestions(string $message, string $answer, mixed $rawSuggestions): array
    {
        $normalizedSuggestions = collect(is_array($rawSuggestions) ? $rawSuggestions : [])
            ->map(fn ($value) => is_string($value) ? trim(preg_replace('/\s+/', ' ', $value) ?? '') : '')
            ->filter()
            ->unique()
            ->take(self::MAX_FOLLOW_UP_SUGGESTIONS)
            ->values()
            ->all();

        if (count($normalizedSuggestions) >= self::MIN_FOLLOW_UP_SUGGESTIONS) {
            return $normalizedSuggestions;
        }

        $fallbackSuggestions = $this->getTopicFallbackSuggestions($message, $answer);

        foreach ($fallbackSuggestions as $suggestion) {
            if (count($normalizedSuggestions) >= self::MAX_FOLLOW_UP_SUGGESTIONS) {
                break;
            }

            if (! in_array($suggestion, $normalizedSuggestions, true)) {
                $normalizedSuggestions[] = $suggestion;
            }
        }

        return array_slice($normalizedSuggestions, 0, self::MAX_FOLLOW_UP_SUGGESTIONS);
    }

    /**
     * @return array{text: string, suggestions: array<int, string>, is_fallback: bool}
     */
    private function buildOfflineReply(string $message): array
    {
        $answer = $this->getSimpleSolMateFallbackAnswer($message);

        if ($answer === '') {
            $answer = $this->getBasicSolarFallbackAnswer($message);
        }

        if ($answer === '') {
            $answer = self::SCOPE_FALLBACK;
        }

        return [
            'text' => $answer,
            'suggestions' => $this->buildFollowUpSuggestions($message, $answer, null),
            'is_fallback' => $answer === self::SCOPE_FALLBACK,
        ];
    }

    private function getTopicFallbackSuggestions(string $message, string $answer): array
    {
        $context = strtolower($message.' '.$answer);

        if (str_contains($context, 'roi') || str_contains($context, 'payback') || str_contains($context, 'savings')) {
            return ['How is ROI calculated?', 'How long is the payback period?', 'What affects solar savings?', 'Is solar worth it?'];
        }

        if (str_contains($context, 'quotation') || str_contains($context, 'estimate')) {
            return ['What is a pre-inspection estimate?', 'Who prepares the final quotation?', 'What can change after inspection?', 'How do I request a quotation?'];
        }

        if (str_contains($context, 'inspection')) {
            return ['How do I request an inspection?', 'Why is inspection important?', 'What happens after inspection?', 'Who visits the site?'];
        }

        if (str_contains($context, 'service request')) {
            return ['When should I use a service request?', 'How is it different from inspection?', 'What concerns can I report?', 'What happens after I submit one?'];
        }

        if (str_contains($context, 'notification')) {
            return ['What do notifications usually mean?', 'Where can I view updates?', 'Do notifications affect my requests?', 'Why am I getting app alerts?'];
        }

        if (str_contains($context, 'testimon')) {
            return ['How do I submit a testimony?', 'Can I edit my testimony?', 'What should I write in it?', 'Who can see my testimony?'];
        }

        return ['How do quotations work?', 'How do I request an inspection?', 'What do notifications mean?', 'What can SolMate help with?'];
    }

    private function getSimpleSolMateFallbackAnswer(string $message): string
    {
        $normalizedMessage = strtolower($message);

        return match (true) {
            str_contains($normalizedMessage, 'awaiting admin approval') || str_contains($normalizedMessage, 'admin approval') => 'Awaiting admin approval means an admin still needs to review or confirm that request before it moves to the next step.',
            str_contains($normalizedMessage, 'quotation') || str_contains($normalizedMessage, 'quote') || str_contains($normalizedMessage, 'estimate') => 'Your pre-inspection estimate is only an early guide. The final quotation is prepared by the technician after inspection and technical assessment.',
            str_contains($normalizedMessage, 'inspection') => 'You can request an inspection from the customer dashboard when you need a site check or technical assessment before final recommendations.',
            str_contains($normalizedMessage, 'service request') => 'A service request is for support concerns or after-service issues, while an inspection request is for site checking and technical evaluation.',
            str_contains($normalizedMessage, 'notification') || str_contains($normalizedMessage, 'alert') => 'Notifications are in-app updates about important account, request, or quotation activity. You can open the notifications section in the app to review them.',
            str_contains($normalizedMessage, 'testimon') || str_contains($normalizedMessage, 'review') || str_contains($normalizedMessage, 'feedback') => 'Testimonies let customers share feedback about their SolMate experience. You can usually submit or edit them from the customer side of the app when that feature is available.',
            str_contains($normalizedMessage, 'faq') || str_contains($normalizedMessage, 'help') || str_contains($normalizedMessage, 'what can solmate help with') => 'I can help explain SolMate quotations, inspection requests, service requests, testimonies, notifications, and basic solar terms.',
            default => '',
        };
    }

    private function getBasicSolarFallbackAnswer(string $message): string
    {
        $normalizedMessage = strtolower($message);

        return match (true) {
            str_contains($normalizedMessage, 'roi') => 'ROI means the value you get back from solar through bill savings compared with the system cost. In simple terms, it shows whether the long-term savings can make the installation worth it.',
            str_contains($normalizedMessage, 'payback') => 'Payback period is the time it takes for solar savings to recover the installation cost. A shorter payback usually means you recover your investment faster.',
            str_contains($normalizedMessage, 'saving') => 'Solar savings depend on your power use, electricity rates, system size, sunlight, and installation cost. Higher usage and good system performance can improve savings.',
            str_contains($normalizedMessage, 'solar panel') => 'Solar panels turn sunlight into electricity for your home or business. They help reduce how much power you need from the grid.',
            str_contains($normalizedMessage, 'inverter') => 'An inverter changes the electricity from solar panels into usable power for your appliances. It is one of the main parts of a solar system.',
            str_contains($normalizedMessage, 'battery') => 'A solar battery stores extra energy for later use, such as at night or during outages. It can help you use more of the solar power you generate.',
            str_contains($normalizedMessage, 'hybrid') => 'A hybrid solar system combines solar panels with battery storage. It gives you solar power during the day and stored energy when needed.',
            str_contains($normalizedMessage, 'on-grid') => 'An on-grid system is connected to the utility grid, while a hybrid system also includes battery storage. Hybrid setups give more backup flexibility, but they are usually more expensive.',
            str_contains($normalizedMessage, 'solar') => 'Solar power uses sunlight to produce electricity that can help lower power bills. The exact benefits depend on your usage, location, and system design.',
            default => '',
        };
    }
}