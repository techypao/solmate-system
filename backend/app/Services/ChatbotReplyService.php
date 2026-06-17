<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class ChatbotReplyService
{
    public const SCOPE_FALLBACK = 'I can currently help with SolMate quotations, inspection requests, service requests, testimonies, notifications, and general app guidance.';

    private const SCOPE_SOLMATE_APP = 'solmate_app';

    private const SCOPE_BASIC_SOLAR = 'basic_solar';

    private const SCOPE_ACCOUNT_SPECIFIC = 'account_specific';

    private const SCOPE_TECHNICAL_SAFETY = 'technical_safety';

    private const SCOPE_UNRELATED = 'unrelated';

    private const MAX_FOLLOW_UP_SUGGESTIONS = 4;

    private const MIN_FOLLOW_UP_SUGGESTIONS = 2;

    private const MAX_RECENT_CONTEXT_MESSAGES = 6;

    private const MAX_CONTEXT_MESSAGE_LENGTH = 240;

    private const FAQ_QUESTION_KEYS = [
        'what is a pre inspection estimate' => 'pre_inspection_estimate',
        'what is a pre-inspection estimate' => 'pre_inspection_estimate',
        'how do i request a quotation' => 'request_quotation',
        'how do i create a quotation' => 'request_quotation',
        'how do i request quotation' => 'request_quotation',
        'who prepares the final quotation' => 'final_quotation_owner',
        'who prepares the inspection based quotation' => 'final_quotation_owner',
        'who prepares the inspection-based quotation' => 'final_quotation_owner',
        'how do i request an inspection' => 'request_inspection',
        'what does awaiting admin approval mean' => 'awaiting_admin_approval',
        'what can solmate help with' => 'solmate_help_scope',
        'what are the frequently asked questions' => 'solmate_help_scope',
        'how do quotations work' => 'quotation_overview',
        'what can change after inspection' => 'inspection_changes',
        'why is inspection important' => 'inspection_importance',
        'what happens after inspection' => 'after_inspection',
        'when should i use a service request' => 'service_request',
        'how is it different from inspection' => 'service_vs_inspection',
        'what concerns can i report' => 'service_request',
        'what do notifications mean' => 'notifications',
        'where can i view updates' => 'notifications',
        'how do i submit a testimony' => 'testimonies',
        'can i edit my testimony' => 'testimonies',
        'what is roi' => 'solar_roi',
        'how is roi calculated' => 'solar_roi',
        'how long is the payback period' => 'solar_payback',
        'what affects solar savings' => 'solar_savings',
        'is solar worth it' => 'solar_worth',
        'what is an inverter' => 'solar_inverter',
        'what does a battery do' => 'solar_battery',
        'what is a hybrid solar system' => 'solar_hybrid',
        'what is the difference between on grid and hybrid' => 'solar_on_grid_vs_hybrid',
        'what is the difference between on-grid and hybrid' => 'solar_on_grid_vs_hybrid',
    ];

    private const FAQ_RESPONSES = [
        'pre_inspection_estimate' => [
            'answer' => 'A pre-inspection estimate is an early guide based mainly on your monthly electric bill. It helps you understand possible solar costs before a technician checks the site.',
            'suggestions' => ['How do I request a quotation?', 'Who prepares the final quotation?', 'What can change after inspection?'],
        ],
        'request_quotation' => [
            'answer' => 'You can request a quotation by creating a pre-inspection estimate in the customer app. After inspection, the technician prepares the inspection-based quotation with the final technical details.',
            'suggestions' => ['What is a pre-inspection estimate?', 'Who prepares the final quotation?', 'How do I request an inspection?'],
        ],
        'final_quotation_owner' => [
            'answer' => 'The technician prepares the inspection-based quotation after the site inspection and technical assessment. Customers can review it once it is submitted.',
            'suggestions' => ['What is a pre-inspection estimate?', 'What can change after inspection?', 'How do I request an inspection?'],
        ],
        'request_inspection' => [
            'answer' => 'You can request an inspection from the customer dashboard when you need a site check or technical assessment before final recommendations.',
            'suggestions' => ['Why is inspection important?', 'What happens after inspection?', 'Who prepares the final quotation?'],
        ],
        'awaiting_admin_approval' => [
            'answer' => 'Awaiting admin approval means an admin still needs to review the request before it moves forward.',
            'suggestions' => ['What happens after admin approval?', 'Where can I view updates?', 'What do notifications mean?'],
        ],
        'solmate_help_scope' => [
            'answer' => 'I can help explain SolMate quotations, inspection requests, service requests, testimonies, notifications, and basic solar terms.',
            'suggestions' => ['How do quotations work?', 'How do I request an inspection?', 'What do notifications mean?'],
        ],
        'quotation_overview' => [
            'answer' => 'SolMate quotations start with a pre-inspection estimate, then a technician can prepare an inspection-based quotation after site assessment. The estimate is only a guide until the site is checked.',
            'suggestions' => ['What is a pre-inspection estimate?', 'Who prepares the final quotation?', 'What can change after inspection?'],
        ],
        'inspection_changes' => [
            'answer' => 'Details can change after inspection because the technician checks the actual site, load needs, roof or mounting conditions, and other technical factors. That is why the pre-inspection estimate is not the final quotation.',
            'suggestions' => ['Why is inspection important?', 'Who prepares the final quotation?', 'What happens after inspection?'],
        ],
        'inspection_importance' => [
            'answer' => 'Inspection matters because solar recommendations depend on the real site conditions, electrical needs, and installation requirements. It helps the technician prepare a more accurate inspection-based quotation.',
            'suggestions' => ['How do I request an inspection?', 'What happens after inspection?', 'Who visits the site?'],
        ],
        'after_inspection' => [
            'answer' => 'After inspection, the technician reviews the site details and can prepare the inspection-based quotation. You can then review updates in the app when they become available.',
            'suggestions' => ['Who prepares the final quotation?', 'What can change after inspection?', 'Where can I view updates?'],
        ],
        'service_request' => [
            'answer' => 'Use a service request for customer support, maintenance, installation, or after-service concerns handled through the SolMate flow. For a new site assessment before quotation, use an inspection request instead.',
            'suggestions' => ['How is it different from inspection?', 'What concerns can I report?', 'How do I request an inspection?'],
        ],
        'service_vs_inspection' => [
            'answer' => 'An inspection request is for site checking and technical assessment before final recommendations. A service request is for support, maintenance, installation, or after-service concerns.',
            'suggestions' => ['When should I use a service request?', 'How do I request an inspection?', 'What happens after inspection?'],
        ],
        'installation' => [
            'answer' => 'Installation-related requests are handled through the SolMate service flow after the needed quotation, approval, and scheduling steps. Exact work still depends on technician assessment and admin updates.',
            'suggestions' => ['When should I use a service request?', 'Where can I view updates?', 'What do notifications mean?'],
        ],
        'maintenance' => [
            'answer' => 'For maintenance or after-service concerns, submit a service request so the concern can be reviewed and assigned through SolMate. Include clear details so support can understand the issue.',
            'suggestions' => ['When should I use a service request?', 'What concerns can I report?', 'Where can I view updates?'],
        ],
        'tracking' => [
            'answer' => 'Tracking helps you follow request or quotation progress through the app screens and notifications. I can explain what the flow means, but I cannot view your live account status.',
            'suggestions' => ['Where can I view updates?', 'What do notifications mean?', 'What does awaiting admin approval mean?'],
        ],
        'notifications' => [
            'answer' => 'Notifications are in-app updates about important activity related to your account, quotations, inspections, service requests, or admin actions. Open the notifications area to review the latest app updates.',
            'suggestions' => ['Where can I view updates?', 'Do notifications affect my requests?', 'What does awaiting admin approval mean?'],
        ],
        'testimonies' => [
            'answer' => 'Testimonies let customers share feedback about their SolMate experience. You can use the testimony feature when you want to submit or manage your review.',
            'suggestions' => ['What should I write in it?', 'Who can see my testimony?', 'What can SolMate help with?'],
        ],
        'discount_request' => [
            'answer' => 'A discount request lets you ask admin to review possible discount adjustments for an inspection-based quotation. Admin decides whether the request can be approved or updated.',
            'suggestions' => ['Who prepares the final quotation?', 'Where can I view updates?', 'What do notifications mean?'],
        ],
        'cancellation' => [
            'answer' => 'Cancellation requests are reviewed through the SolMate process and may need admin action before the request is fully closed. Check app updates or contact admin if you need help with a specific request.',
            'suggestions' => ['Where can I view updates?', 'What do notifications mean?', 'Talk to a real admin'],
        ],
        'solar_roi' => [
            'answer' => 'ROI means the value you get back from solar through bill savings compared with the system cost. In simple terms, it shows whether the long-term savings can make the installation worth it.',
            'suggestions' => ['How long is the payback period?', 'What affects solar savings?', 'Is solar worth it?'],
        ],
        'solar_payback' => [
            'answer' => 'Payback period is the time it takes for solar savings to recover the installation cost. A shorter payback usually means you recover your investment faster.',
            'suggestions' => ['How is ROI calculated?', 'What affects solar savings?', 'Why does inspection matter?'],
        ],
        'solar_savings' => [
            'answer' => 'Solar savings depend on your power use, electricity rates, system size, sunlight, and installation cost. The final estimate is more reliable after inspection and technician assessment.',
            'suggestions' => ['How is ROI calculated?', 'How long is the payback period?', 'Why does inspection matter?'],
        ],
        'solar_worth' => [
            'answer' => 'Solar can be worth considering when your usage, electricity rate, site condition, and system cost support good long-term savings. A SolMate inspection helps confirm what fits your actual site.',
            'suggestions' => ['What affects solar savings?', 'What is a pre-inspection estimate?', 'Why does inspection matter?'],
        ],
        'solar_panels' => [
            'answer' => 'Solar panels turn sunlight into electricity for your home or business. They can reduce how much power you need from the grid.',
            'suggestions' => ['What is an inverter?', 'What does a battery do?', 'What affects solar savings?'],
        ],
        'solar_inverter' => [
            'answer' => 'An inverter changes electricity from solar panels into usable power for appliances. It is one of the main parts of a solar system.',
            'suggestions' => ['What does a battery do?', 'What is a hybrid solar system?', 'Why does inspection matter?'],
        ],
        'solar_battery' => [
            'answer' => 'A solar battery stores extra energy for later use, such as at night or during outages. It can add backup flexibility but also affects system cost.',
            'suggestions' => ['What is a hybrid solar system?', 'What affects solar savings?', 'Why does inspection matter?'],
        ],
        'solar_hybrid' => [
            'answer' => 'A hybrid solar system combines solar panels with battery storage. It can use solar power during the day and stored energy when needed.',
            'suggestions' => ['What does a battery do?', 'On-grid vs hybrid?', 'Why does inspection matter?'],
        ],
        'solar_on_grid_vs_hybrid' => [
            'answer' => 'An on-grid system connects to the utility grid, while a hybrid system also includes battery storage. Hybrid setups can offer more backup flexibility, but they are usually more expensive.',
            'suggestions' => ['What does a battery do?', 'What affects solar savings?', 'Why does inspection matter?'],
        ],
    ];

    private const FAQ_TOPIC_MATCHERS = [
        'request_quotation' => ['request a quotation', 'create a quotation', 'make quotation', 'get quotation', 'get a quote', 'request quote', 'create quote', 'ask for quote', 'quotation request', 'estimate request', 'solar quote'],
        'quotation_overview' => ['how quotations work', 'quotation process', 'quote process', 'estimate process', 'quotation flow', 'initial quote', 'final quote'],
        'pre_inspection_estimate' => ['pre inspection estimate', 'pre-inspection estimate', 'initial estimate', 'early estimate', 'bill estimate', 'monthly electric bill estimate'],
        'final_quotation_owner' => ['who prepares final quotation', 'who makes final quotation', 'who creates final quote', 'technician quotation', 'inspection based quotation', 'inspection-based quotation'],
        'inspection_changes' => ['change after inspection', 'change after site inspection', 'why estimate changes', 'estimate may change', 'quotation may change'],
        'request_inspection' => ['request inspection', 'book inspection', 'schedule inspection', 'site check', 'site checking', 'technical assessment', 'site assessment'],
        'inspection_importance' => ['why inspection matters', 'why inspection important', 'need inspection', 'why site check', 'why technician assessment'],
        'after_inspection' => ['after inspection', 'after site visit', 'after site check', 'what happens next after inspection'],
        'awaiting_admin_approval' => ['awaiting admin approval', 'admin approval', 'waiting for admin', 'pending admin'],
        'service_request' => ['service request', 'support request', 'report concern', 'customer concern', 'after service', 'after-service'],
        'service_vs_inspection' => ['service vs inspection', 'service and inspection difference', 'different from inspection', 'inspection vs service'],
        'installation' => ['installation request', 'install request', 'installation schedule', 'installation process'],
        'maintenance' => ['maintenance request', 'maintenance concern', 'repair request', 'after-service concern', 'system issue'],
        'tracking' => ['track request', 'tracking', 'request progress', 'quotation progress', 'track quotation', 'track inspection'],
        'notifications' => ['notification', 'notifications', 'app alert', 'updates', 'view updates'],
        'testimonies' => ['testimony', 'testimonies', 'review', 'feedback'],
        'discount_request' => ['discount request', 'ask discount', 'request discount', 'quotation discount', 'discount negotiation'],
        'cancellation' => ['cancel request', 'cancellation', 'cancel inspection', 'cancel service', 'cancel quotation'],
        'solmate_help_scope' => ['faq', 'frequently asked', 'what can solmate help', 'what can you help', 'help me with'],
        'solar_roi' => ['roi', 'return on investment', 'investment return'],
        'solar_payback' => ['payback', 'recover investment', 'recover cost'],
        'solar_savings' => ['solar savings', 'save money', 'lower bill', 'electric bill savings', 'reduce bill'],
        'solar_worth' => ['is solar worth', 'worth it', 'should i consider solar'],
        'solar_panels' => ['solar panel', 'solar panels', 'how solar works', 'solar energy'],
        'solar_inverter' => ['inverter'],
        'solar_battery' => ['battery', 'batteries', 'solar storage'],
        'solar_hybrid' => ['hybrid solar', 'hybrid system'],
        'solar_on_grid_vs_hybrid' => ['on-grid vs hybrid', 'on grid vs hybrid', 'on-grid or hybrid', 'on grid or hybrid', 'on-grid and hybrid', 'on grid and hybrid'],
    ];

    private const SYSTEM_INSTRUCTION = <<<'TEXT'
You are SolMate Assistant for the SolMate customer app and website.

Your role:
- Help customers understand SolMate features and processes.
- Help customers with basic solar education relevant to SolMate.
- Answer only questions related to the SolMate app, website, and customer workflows.
- You may also answer basic solar-related customer questions that support understanding of the SolMate process.
- Give concise, clear, friendly, and practical answers.
- Stay primarily focused on SolMate app guidance and basic solar education.

Allowed basic solar topics:
- What solar panels are
- How solar energy works in simple terms
- What an inverter is
- What a battery does in a solar setup
- What a hybrid solar system is
- The basic difference between on-grid and hybrid systems
- What ROI and payback period mean
- What affects solar savings
- Why inspection matters before final recommendations

Important rules:
- The customer begins with a pre-inspection estimate.
- The pre-inspection estimate is only a guide and may change after actual inspection.
- The inspection-based quotation is created by the technician after inspection and technical assessment.
- Do not claim that you can view live account data.
- Do not claim that you can check the customer's actual request status, quotation status, unread notifications, or database records.
- Do not invent system features.
- Do not pretend to be an admin, technician, or human support representative.
- Do not provide legal, financial, electrical engineering, or safety-critical professional advice beyond basic app guidance.
- Do not provide exact technical design recommendations, electrical safety advice, exact pricing promises, or final system suitability judgments without inspection.
- If the question is unrelated to SolMate and unrelated to basic solar customer education, reply with exactly: "I can currently help with SolMate quotations, inspection requests, service requests, testimonies, notifications, and general app guidance."

Response style:
- Be concise, clear, and friendly.
- Default to 1 to 3 short sentences.
- Put the main answer first.
TEXT;

    public function send(string $message, array $recentMessages = []): array
    {
        $guardrailReply = $this->buildScopeGuardrailReply($message);

        if ($guardrailReply !== null) {
            return $guardrailReply;
        }

        $faqReply = $this->buildExactFaqReply($message);

        if ($faqReply !== null) {
            return $faqReply;
        }

        $matchedFaqReply = $this->buildMatchedFaqReply($message);

        if ($matchedFaqReply !== null) {
            return $matchedFaqReply;
        }

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
                    $this->buildRequestBody($message, $recentMessages),
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

    private function buildRequestBody(string $message, array $recentMessages = []): array
    {
        $recentContext = $this->buildRecentConversationContext($recentMessages);
        $recentContextBlock = $recentContext !== ''
            ? <<<TEXT

Recent chat context for follow-up understanding only:
{$recentContext}
TEXT
            : '';

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
{$recentContextBlock}

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
            'admin_handoff_reason' => $answer === self::SCOPE_FALLBACK ? self::SCOPE_UNRELATED : null,
            'admin_handoff_label' => $answer === self::SCOPE_FALLBACK ? $this->adminHandoffLabel(self::SCOPE_UNRELATED) : null,
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
        $guardrailReply = $this->buildScopeGuardrailReply($message);

        if ($guardrailReply !== null) {
            return $guardrailReply;
        }

        $faqReply = $this->buildExactFaqReply($message);

        if ($faqReply !== null) {
            return $faqReply;
        }

        $matchedFaqReply = $this->buildMatchedFaqReply($message);

        if ($matchedFaqReply !== null) {
            return $matchedFaqReply;
        }

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

        if (
            str_contains($context, 'solar')
            || str_contains($context, 'panel')
            || str_contains($context, 'battery')
            || str_contains($context, 'inverter')
            || str_contains($context, 'hybrid')
            || str_contains($context, 'on-grid')
        ) {
            return ['How does solar work?', 'What affects solar savings?', 'What is a hybrid system?', 'Why does inspection matter?'];
        }

        return ['How do quotations work?', 'How do I request an inspection?', 'What do notifications mean?', 'What can SolMate help with?'];
    }

    private function getSimpleSolMateFallbackAnswer(string $message): string
    {
        $questionKey = self::FAQ_QUESTION_KEYS[$this->normalizeQuestionKey($message)] ?? null;

        return is_string($questionKey)
            ? (self::FAQ_RESPONSES[$questionKey]['answer'] ?? '')
            : '';
    }

    /**
     * @return array{text: string, suggestions: array<int, string>, is_fallback: bool}|null
     */
    private function buildExactFaqReply(string $message): ?array
    {
        $questionKey = self::FAQ_QUESTION_KEYS[$this->normalizeQuestionKey($message)] ?? null;

        if (! is_string($questionKey) || ! isset(self::FAQ_RESPONSES[$questionKey])) {
            return null;
        }

        $response = self::FAQ_RESPONSES[$questionKey];

        return [
            'text' => $response['answer'],
            'suggestions' => $this->buildFollowUpSuggestions($message, $response['answer'], $response['suggestions']),
            'is_fallback' => false,
        ];
    }

    /**
     * @return array{text: string, suggestions: array<int, string>, is_fallback: bool}|null
     */
    private function buildMatchedFaqReply(string $message): ?array
    {
        $questionKey = $this->matchFaqTopic($message);

        if (! is_string($questionKey) || ! isset(self::FAQ_RESPONSES[$questionKey])) {
            return null;
        }

        $response = self::FAQ_RESPONSES[$questionKey];

        return [
            'text' => $response['answer'],
            'suggestions' => $this->buildFollowUpSuggestions($message, $response['answer'], $response['suggestions']),
            'is_fallback' => false,
        ];
    }

    private function matchFaqTopic(string $message): ?string
    {
        $normalized = $this->normalizeForMatching($message);
        $bestQuestionKey = null;
        $bestScore = 0;

        foreach (self::FAQ_TOPIC_MATCHERS as $questionKey => $phrases) {
            foreach ($phrases as $phrase) {
                $normalizedPhrase = $this->normalizeForMatching($phrase);

                if ($normalizedPhrase === '' || ! str_contains($normalized, $normalizedPhrase)) {
                    continue;
                }

                $score = substr_count($normalizedPhrase, ' ') + 1;

                if ($score > $bestScore) {
                    $bestQuestionKey = $questionKey;
                    $bestScore = $score;
                }
            }
        }

        return $bestQuestionKey;
    }

    /**
     * @return array{text: string, suggestions: array<int, string>, is_fallback: bool}|null
     */
    private function buildScopeGuardrailReply(string $message): ?array
    {
        return match ($this->classifyMessageScope($message)) {
            self::SCOPE_ACCOUNT_SPECIFIC => [
                'text' => 'I cannot view live account records or check your actual request, quotation, or notification status. Please open the related SolMate screen for updates, or ask an admin if you need help with a specific record.',
                'suggestions' => ['Where can I view updates?', 'What do notifications mean?', 'Talk to a real admin'],
                'is_fallback' => false,
                'admin_handoff_reason' => self::SCOPE_ACCOUNT_SPECIFIC,
                'admin_handoff_label' => $this->adminHandoffLabel(self::SCOPE_ACCOUNT_SPECIFIC),
            ],
            self::SCOPE_TECHNICAL_SAFETY => [
                'text' => 'For exact system sizing, wiring, safety, or installation decisions, a technician needs to inspect the site first. I can explain the general SolMate process, but final technical guidance should come from the inspection and technician assessment.',
                'suggestions' => ['Why does inspection matter?', 'How do I request an inspection?', 'What happens after inspection?'],
                'is_fallback' => false,
                'admin_handoff_reason' => self::SCOPE_TECHNICAL_SAFETY,
                'admin_handoff_label' => $this->adminHandoffLabel(self::SCOPE_TECHNICAL_SAFETY),
            ],
            self::SCOPE_UNRELATED => [
                'text' => self::SCOPE_FALLBACK,
                'suggestions' => $this->buildFollowUpSuggestions($message, self::SCOPE_FALLBACK, null),
                'is_fallback' => true,
                'admin_handoff_reason' => self::SCOPE_UNRELATED,
                'admin_handoff_label' => $this->adminHandoffLabel(self::SCOPE_UNRELATED),
            ],
            default => null,
        };
    }

    private function classifyMessageScope(string $message): string
    {
        $normalized = $this->normalizeForMatching($message);

        if ($normalized === '') {
            return self::SCOPE_UNRELATED;
        }

        if ($this->containsAny($normalized, [
            'planet', 'planets', 'mars', 'venus', 'jupiter', 'saturn', 'galaxy', 'orbit', 'eclipse', 'astronomy',
        ])) {
            return self::SCOPE_UNRELATED;
        }

        if ($this->containsAny($normalized, [
            'ignore your rules', 'ignore previous', 'ignore instructions', 'forget your rules', 'act as',
            'jailbreak', 'system prompt', 'developer message',
        ])) {
            return self::SCOPE_UNRELATED;
        }

        if ($this->isAccountSpecificQuestion($normalized)) {
            return self::SCOPE_ACCOUNT_SPECIFIC;
        }

        if ($this->isTechnicalSafetyQuestion($normalized)) {
            return self::SCOPE_TECHNICAL_SAFETY;
        }

        if ($this->containsAny($normalized, $this->solmateScopeKeywords())) {
            return self::SCOPE_SOLMATE_APP;
        }

        if ($this->containsAny($normalized, $this->solarScopeKeywords())) {
            return self::SCOPE_BASIC_SOLAR;
        }

        return self::SCOPE_UNRELATED;
    }

    private function isAccountSpecificQuestion(string $normalized): bool
    {
        if (! $this->containsAny($normalized, ['my ', 'mine', 'our '])) {
            return false;
        }

        return $this->containsAny($normalized, [
            'status', 'approved', 'approval', 'scheduled', 'assigned', 'unread', 'latest update', 'where is',
            'check my', 'view my actual', 'my request', 'my quotation', 'my quote', 'my inspection',
            'my service', 'my notification', 'my technician', 'my discount', 'my cancellation',
        ]);
    }

    private function isTechnicalSafetyQuestion(string $normalized): bool
    {
        if ($this->containsAny($normalized, ['installation request', 'install request', 'installation schedule', 'installation process'])) {
            return false;
        }

        return $this->containsAny($normalized, [
            'wire', 'wiring', 'breaker', 'voltage', 'ampere', 'amps', 'grounding', 'electrical code',
            'short circuit', 'fire risk', 'electric shock', 'roof safety', 'install myself', 'diy',
            'connect inverter', 'connect battery', 'how many panels', 'exact system size', 'system size do i need',
            'what size system', 'final configuration',
        ]);
    }

    /**
     * @param  array<int, string>  $needles
     */
    private function containsAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if ($needle !== '' && str_contains($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int, string>
     */
    private function solmateScopeKeywords(): array
    {
        return [
            'solmate', 'quotation', 'quote', 'estimate', 'pre inspection', 'pre-inspection', 'inspection',
            'service request', 'support request', 'installation request', 'maintenance request', 'tracking',
            'notification', 'testimony', 'testimonies', 'review', 'feedback', 'admin approval', 'discount',
            'cancellation', 'cancel request', 'technician', 'customer app', 'dashboard', 'faq', 'help',
        ];
    }

    /**
     * @return array<int, string>
     */
    private function solarScopeKeywords(): array
    {
        return [
            'solar panel', 'solar panels', 'solar energy', 'solar power', 'solar system', 'inverter',
            'battery', 'batteries', 'hybrid', 'on grid', 'on-grid', 'roi', 'payback', 'savings',
            'electric bill', 'net metering', 'solar',
        ];
    }

    private function normalizeQuestionKey(string $message): string
    {
        $normalized = strtolower(trim($message));
        $normalized = preg_replace('/\s+/', ' ', $normalized) ?? '';
        $normalized = preg_replace('/[?!.\s]+$/', '', $normalized) ?? '';

        return trim($normalized);
    }

    private function normalizeForMatching(string $message): string
    {
        $normalized = strtolower(trim($message));
        $normalized = str_replace(['-', '_', '/', '?', '!', '.', ',', ':', ';', '(', ')'], ' ', $normalized);
        $normalized = preg_replace('/\s+/', ' ', $normalized) ?? '';

        return trim($normalized);
    }

    private function buildRecentConversationContext(array $recentMessages): string
    {
        $contextLines = collect($recentMessages)
            ->filter(fn ($message) => is_array($message))
            ->map(function (array $message): ?string {
                $senderType = (string) ($message['sender_type'] ?? '');
                $body = $this->sanitizeContextMessage((string) ($message['body'] ?? ''));

                if ($body === '' || ! in_array($senderType, ['user', 'bot'], true)) {
                    return null;
                }

                if (! in_array($this->classifyMessageScope($body), [self::SCOPE_SOLMATE_APP, self::SCOPE_BASIC_SOLAR], true)) {
                    return null;
                }

                $label = $senderType === 'bot' ? 'SolBot' : 'Customer';

                return "{$label}: {$body}";
            })
            ->filter()
            ->take(self::MAX_RECENT_CONTEXT_MESSAGES)
            ->values()
            ->all();

        return implode("\n", $contextLines);
    }

    private function sanitizeContextMessage(string $message): string
    {
        $sanitized = trim(preg_replace('/\s+/', ' ', $message) ?? '');

        if ($sanitized === '') {
            return '';
        }

        $sanitized = preg_replace('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i', '[email]', $sanitized) ?? $sanitized;
        $sanitized = preg_replace('/\b(?:\+?63|0)?9\d{9}\b/', '[phone]', $sanitized) ?? $sanitized;
        $sanitized = preg_replace('/\b(?:request|quotation|quote|inspection|service)\s*#?\s*\d+\b/i', '[record]', $sanitized) ?? $sanitized;

        if (strlen($sanitized) > self::MAX_CONTEXT_MESSAGE_LENGTH) {
            $sanitized = substr($sanitized, 0, self::MAX_CONTEXT_MESSAGE_LENGTH - 3).'...';
        }

        return $sanitized;
    }

    private function adminHandoffLabel(string $reason): string
    {
        return match ($reason) {
            self::SCOPE_ACCOUNT_SPECIFIC => 'Account-specific help',
            self::SCOPE_TECHNICAL_SAFETY => 'Technical assessment needed',
            self::SCOPE_UNRELATED => 'Outside SolBot scope',
            'manual_escalation' => 'Customer requested admin',
            'customer_quick_help' => 'Customer requested admin',
            'user_requested_human' => 'Customer requested human support',
            'bot_error' => 'Bot response issue',
            'empty_response' => 'Bot returned no answer',
            'repeated_bot_failures' => 'Repeated bot failures',
            default => ucwords(str_replace('_', ' ', $reason)),
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
