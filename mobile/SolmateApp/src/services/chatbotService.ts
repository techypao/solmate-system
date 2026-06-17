import Config from 'react-native-config';

// Legacy direct-Gemini client. The active customer chat uses the backend
// support chat API so SolBot behavior and API keys stay server-owned.
const GEMINI_MODEL = 'gemini-2.5-flash';
const GEMINI_API_URL =
  `https://generativelanguage.googleapis.com/v1beta/models/${GEMINI_MODEL}:generateContent`;

export const SOLMATE_CHATBOT_SCOPE_FALLBACK =
  'I can currently help with SolMate quotations, inspection requests, service requests, testimonies, notifications, and general app guidance.';

const MAX_FOLLOW_UP_SUGGESTIONS = 4;
const MIN_FOLLOW_UP_SUGGESTIONS = 2;

const SOLMATE_CHATBOT_SYSTEM_INSTRUCTION = `
You are SolMate Assistant for the SolMate customer mobile app.

Your role:
- Help customers understand SolMate features and processes.
- Help customers with basic solar education relevant to SolMate.
- Answer only questions related to the SolMate app and its customer workflows.
- You may also answer basic solar-related customer questions that support understanding of the SolMate process.
- Give concise, clear, friendly, and practical answers.
- Stay primarily focused on the SolMate app and customer guidance.

Supported topics:
- FAQs about the SolMate app
- Pre-inspection estimate guidance
- Inspection-based quotation explanation
- Inspection request guidance
- Service request guidance
- Testimonies feature explanation
- Notifications feature explanation
- General customer app and process guidance
- Basic solar education for customers

Allowed basic solar knowledge topics:
- What solar panels are
- How solar energy works in simple terms
- What an inverter is
- What a battery does in a solar setup
- What a hybrid solar system is
- The basic difference between on-grid and hybrid systems
- What ROI means for solar panels
- What payback period means
- What affects solar savings
- Whether solar is generally worth considering
- Why inspection matters before final recommendations
- Why technician assessment is needed before an inspection-based quotation

Important SolMate rules and knowledge:
- In SolMate, the customer begins with a pre-inspection estimate.
- The pre-inspection estimate is an early estimate based mainly on the customer's monthly electric bill.
- The pre-inspection estimate is only a guide and may change after the technician's actual inspection.
- The customer does not prepare the inspection-based quotation.
- The inspection-based quotation is created by the technician after inspection and technical assessment.
- An inspection request is used when the customer wants site checking, assessment, or technical evaluation before finalizing work.
- A service request is used for customer service-related concerns, support needs, or after-service concerns depending on the app flow.
- Notifications are in-app updates that inform the customer about important activity, updates, or actions related to their account or requests.
- Testimonies allow customers to share feedback or reviews about their experience.
- You are only a help assistant for guidance and explanation.
- SolMate Assistant is a customer-facing in-app AI assistant that helps with both SolMate app guidance and basic solar education.

Strict limitations:
- Do not claim that you can view live account data.
- Do not claim that you can check the customer's actual request status, quotation status, unread notifications, or database records.
- Do not invent system features that were not described.
- Do not answer unrelated non-SolMate and non-solar general knowledge questions.
- Do not pretend to be an admin, technician, or human support representative.
- Do not provide legal, financial, electrical engineering, or safety-critical professional advice beyond basic app guidance.
- Do not provide exact technical design recommendations, electrical safety advice, exact pricing promises, or final system suitability judgments without inspection.
- Do not act like a full solar engineer or licensed technical consultant.

Behavior rules:
- If the question is unrelated to SolMate and unrelated to basic solar customer education, politely reply with exactly: "${SOLMATE_CHATBOT_SCOPE_FALLBACK}"
- Do not use the out-of-scope reply for basic solar questions such as ROI, payback period, solar savings, solar panels, inverters, batteries, hybrid systems, or on-grid versus hybrid comparisons.
- If the user asks for live or account-specific data, explain the feature generally instead of pretending to access data.
- If the user asks something ambiguous, answer using the most likely SolMate meaning.
- If the user asks about the difference between two SolMate features, clearly compare them.
- If the user seems confused, explain step by step.
- If the user asks something highly technical, site-specific, or requiring actual assessment, give a simple general explanation and then clarify that actual recommendations depend on inspection and technician evaluation.
- If the user asks about solar suitability, final configuration, or exact recommendations, explain that final guidance depends on inspection and technician assessment.
- When answering solar questions, keep the explanation basic, customer-friendly, and connected to the SolMate process when helpful.
- If the user asks about ROI, payback, or savings, answer the question directly in simple terms before offering any extra context.

Response style:
- Be friendly, professional, and easy to understand.
- Keep answers concise, summarized, and complete.
- Prefer simple explanations over technical jargon.
- Respond in plain readable text.
- Do not use markdown symbols unless truly necessary.
- Put the main answer first.
- Use simple, direct wording.
- Default to 1 to 3 short sentences when possible.
- Avoid long paragraphs unless the question truly needs more detail.
- Remove filler words, repeated ideas, extra examples, and background details that do not change the answer.
- Do not restate the user's question unless needed for clarity.
- If extra context is helpful, add only one short follow-up sentence.
- For comparisons or steps, keep them brief and limited to only the most important points.
`.trim();

type GeminiPart = {
  text?: string | null;
};

type GeminiCandidate = {
  content?: {
    parts?: GeminiPart[] | null;
  } | null;
};

type GeminiErrorPayload = {
  error?: {
    message?: string;
  };
  candidates?: GeminiCandidate[] | null;
  promptFeedback?: {
    blockReason?: string;
    blockReasonMessage?: string;
  } | null;
};

export class ChatbotServiceError extends Error {
  status: number;
  data?: unknown;

  constructor(message: string, status = 0, data?: unknown) {
    super(message);
    this.name = 'ChatbotServiceError';
    this.status = status;
    this.data = data;
  }
}

function getGeminiApiKey() {
  const apiKey = Config.GEMINI_API_KEY?.trim();

  if (!apiKey) {
    throw new ChatbotServiceError(
      'Gemini API key is not configured for the mobile app.',
    );
  }

  return apiKey;
}

function buildRequestBody(message: string) {
  const wrappedMessage = `
User question:
${message}

Return valid JSON only in this exact shape:
{"answer":"string","suggestions":["string"]}

Rules:
- "answer" must follow all system instruction rules and stay concise for mobile.
- Put the main answer first and keep it brief.
- "suggestions" must contain 2 to 4 short related follow-up questions.
- Keep each suggestion short, clear, and easy to tap on mobile.
- Suggestions must stay within SolMate and basic solar guidance scope.
- Questions about ROI, payback period, solar savings, solar panels, inverters, batteries, hybrid systems, and on-grid vs hybrid are in scope and should be answered directly.
- If the question is outside scope, set "answer" to exactly "${SOLMATE_CHATBOT_SCOPE_FALLBACK}" and provide 2 to 4 short SolMate-related follow-up questions.
`.trim();

  return {
    systemInstruction: {
      parts: [
        {
          text: SOLMATE_CHATBOT_SYSTEM_INSTRUCTION,
        },
      ],
    },
    contents: [
      {
        role: 'user',
        parts: [
          {
            text: wrappedMessage,
          },
        ],
      },
    ],
    generationConfig: {
      temperature: 0.3,
      maxOutputTokens: 480,
    },
    store: false,
  };
}

export type ChatbotReply = {
  text: string;
  suggestions: string[];
};

type ParsedChatbotReply = {
  answer?: unknown;
  suggestions?: unknown;
};

async function parseGeminiResponse(response: Response) {
  const responseText = await response.text();

  if (!responseText) {
    return null;
  }

  try {
    return JSON.parse(responseText) as GeminiErrorPayload;
  } catch {
    return responseText;
  }
}

function extractGeminiText(payload: GeminiErrorPayload | string | null) {
  if (!payload || typeof payload === 'string') {
    return '';
  }

  const parts = payload.candidates?.[0]?.content?.parts ?? [];

  return parts
    .map(part => (typeof part?.text === 'string' ? part.text.trim() : ''))
    .filter(Boolean)
    .join('\n')
    .trim();
}

function stripCodeFences(value: string) {
  return value.replace(/^```(?:json)?\s*/i, '').replace(/\s*```$/, '').trim();
}

function parseStructuredReply(value: string): ParsedChatbotReply | null {
  if (!value) {
    return null;
  }

  const normalized = stripCodeFences(value);

  try {
    const parsed = JSON.parse(normalized) as ParsedChatbotReply;
    return parsed && typeof parsed === 'object' ? parsed : null;
  } catch {
    return null;
  }
}

function decodeJsonString(value: string) {
  try {
    return JSON.parse(`"${value}"`) as string;
  } catch {
    return value
      .replace(/\\"/g, '"')
      .replace(/\\\\/g, '\\')
      .replace(/\\n/g, ' ')
      .replace(/\\r/g, ' ')
      .replace(/\\t/g, ' ')
      .replace(/\\\//g, '/');
  }
}

function extractJsonStringField(text: string, fieldName: string) {
  const normalized = stripCodeFences(text);
  const fieldIndex = normalized.indexOf(`"${fieldName}"`);

  if (fieldIndex === -1) {
    return '';
  }

  const colonIndex = normalized.indexOf(':', fieldIndex);

  if (colonIndex === -1) {
    return '';
  }

  const openingQuoteIndex = normalized.indexOf('"', colonIndex);

  if (openingQuoteIndex === -1) {
    return '';
  }

  let isEscaped = false;
  let extracted = '';

  for (let index = openingQuoteIndex + 1; index < normalized.length; index += 1) {
    const char = normalized[index];

    if (isEscaped) {
      extracted += char;
      isEscaped = false;
      continue;
    }

    if (char === '\\') {
      extracted += char;
      isEscaped = true;
      continue;
    }

    if (char === '"') {
      return decodeJsonString(extracted).replace(/\s+/g, ' ').trim();
    }

    extracted += char;
  }

  return decodeJsonString(extracted).replace(/\s+/g, ' ').trim();
}

function normalizeSuggestion(value: unknown) {
  if (typeof value !== 'string') {
    return '';
  }

  return value.replace(/\s+/g, ' ').trim();
}

function getTopicFallbackSuggestions(message: string, answer: string) {
  const context = `${message} ${answer}`.toLowerCase();

  if (context.includes('roi') || context.includes('payback') || context.includes('savings')) {
    return [
      'How is ROI calculated?',
      'How long is the payback period?',
      'What affects solar savings?',
      'Is solar worth it?',
    ];
  }

  if (context.includes('quotation') || context.includes('quote') || context.includes('estimate')) {
    return [
      'What is a pre-inspection estimate?',
      'Who prepares the final quotation?',
      'What can change after inspection?',
      'How do I request a quotation?',
    ];
  }

  if (context.includes('inspection')) {
    return [
      'How do I request an inspection?',
      'Why is inspection important?',
      'What happens after inspection?',
      'Who visits the site?',
    ];
  }

  if (context.includes('service request')) {
    return [
      'When should I use a service request?',
      'How is it different from inspection?',
      'What concerns can I report?',
      'What happens after I submit one?',
    ];
  }

  if (context.includes('notification')) {
    return [
      'What do notifications usually mean?',
      'Where can I view updates?',
      'Do notifications affect my requests?',
      'Why am I getting app alerts?',
    ];
  }

  if (context.includes('testimon')) {
    return [
      'How do I submit a testimony?',
      'Can I edit my testimony?',
      'What should I write in it?',
      'Who can see my testimony?',
    ];
  }

  if (
    context.includes('solar')
    || context.includes('panel')
    || context.includes('battery')
    || context.includes('inverter')
    || context.includes('hybrid')
    || context.includes('on-grid')
  ) {
    return [
      'How does it work?',
      'What are the main benefits?',
      'What affects solar savings?',
      'Why does inspection matter?',
    ];
  }

  return [
    'How do quotations work?',
    'How do I request an inspection?',
    'What do notifications mean?',
    'What can SolMate help with?',
  ];
}

function getBasicSolarFallbackAnswer(message: string) {
  const normalizedMessage = message.toLowerCase();

  if (normalizedMessage.includes('roi')) {
    return 'ROI means the value you get back from solar through bill savings compared with the system cost. In simple terms, it shows whether the long-term savings can make the installation worth it.';
  }

  if (normalizedMessage.includes('payback')) {
    return 'Payback period is the time it takes for solar savings to recover the installation cost. A shorter payback usually means you recover your investment faster.';
  }

  if (normalizedMessage.includes('saving')) {
    return 'Solar savings depend on your power use, electricity rates, system size, sunlight, and installation cost. Higher usage and good system performance can improve savings.';
  }

  if (normalizedMessage.includes('solar panel')) {
    return 'Solar panels turn sunlight into electricity for your home or business. They help reduce how much power you need from the grid.';
  }

  if (normalizedMessage.includes('inverter')) {
    return 'An inverter changes the electricity from solar panels into usable power for your appliances. It is one of the main parts of a solar system.';
  }

  if (normalizedMessage.includes('battery')) {
    return 'A solar battery stores extra energy for later use, such as at night or during outages. It can help you use more of the solar power you generate.';
  }

  if (normalizedMessage.includes('hybrid')) {
    return 'A hybrid solar system combines solar panels with battery storage. It gives you solar power during the day and stored energy when needed.';
  }

  if (normalizedMessage.includes('on-grid')) {
    return 'An on-grid system is connected to the utility grid, while a hybrid system also includes battery storage. Hybrid setups give more backup flexibility, but they are usually more expensive.';
  }

  if (normalizedMessage.includes('solar')) {
    return 'Solar power uses sunlight to produce electricity that can help lower power bills. The exact benefits depend on your usage, location, and system design.';
  }

  return '';
}

function buildFollowUpSuggestions(message: string, answer: string, rawSuggestions: unknown) {
  const normalizedSuggestions = Array.isArray(rawSuggestions)
    ? rawSuggestions
        .map(normalizeSuggestion)
        .filter(Boolean)
        .filter((item, index, items) => items.indexOf(item) === index)
        .slice(0, MAX_FOLLOW_UP_SUGGESTIONS)
    : [];

  if (normalizedSuggestions.length >= MIN_FOLLOW_UP_SUGGESTIONS) {
    return normalizedSuggestions;
  }

  const fallbackSuggestions = getTopicFallbackSuggestions(message, answer);
  const mergedSuggestions = [...normalizedSuggestions];

  fallbackSuggestions.forEach(suggestion => {
    if (
      mergedSuggestions.length < MAX_FOLLOW_UP_SUGGESTIONS
      && !mergedSuggestions.includes(suggestion)
    ) {
      mergedSuggestions.push(suggestion);
    }
  });

  return mergedSuggestions.slice(0, MAX_FOLLOW_UP_SUGGESTIONS);
}

function extractChatbotReply(
  payload: GeminiErrorPayload | string | null,
  originalMessage: string,
): ChatbotReply {
  const rawText = extractGeminiText(payload);
  const parsedReply = parseStructuredReply(rawText);
  const extractedAnswer = extractJsonStringField(rawText, 'answer');
  const parsedAnswer =
    typeof parsedReply?.answer === 'string'
      ? parsedReply.answer.trim()
      : extractedAnswer || rawText.trim();
  const rescuedAnswer =
    parsedAnswer === SOLMATE_CHATBOT_SCOPE_FALLBACK
      ? getBasicSolarFallbackAnswer(originalMessage)
      : '';
  const answer = rescuedAnswer || parsedAnswer;

  return {
    text: answer,
    suggestions: buildFollowUpSuggestions(
      originalMessage,
      answer,
      parsedReply?.suggestions,
    ),
  };
}

function getGeminiErrorMessage(
  payload: GeminiErrorPayload | string | null,
  status: number,
) {
  if (typeof payload === 'string' && payload.trim()) {
    return payload;
  }

  if (!payload || typeof payload === 'string') {
    return status >= 500
      ? 'SolMate Assistant is unavailable right now. Please try again in a moment.'
      : 'SolMate Assistant could not process that request.';
  }

  const apiMessage = payload?.error?.message?.trim();

  if (apiMessage) {
    return apiMessage;
  }

  const blockReasonMessage = payload?.promptFeedback?.blockReasonMessage?.trim();

  if (blockReasonMessage) {
    return blockReasonMessage;
  }

  const blockReason = payload?.promptFeedback?.blockReason?.trim();

  if (blockReason) {
    return `Gemini request was blocked: ${blockReason}.`;
  }

  return status >= 500
    ? 'SolMate Assistant is unavailable right now. Please try again in a moment.'
    : 'SolMate Assistant could not process that request.';
}

export async function sendChatbotMessage(message: string): Promise<ChatbotReply> {
  const trimmedMessage = message.trim();

  if (!trimmedMessage) {
    throw new ChatbotServiceError(
      'Please enter a message before sending.',
      400,
    );
  }

  const apiKey = getGeminiApiKey();

  try {
    const response = await fetch(GEMINI_API_URL, {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'x-goog-api-key': apiKey,
      },
      body: JSON.stringify(buildRequestBody(trimmedMessage)),
    });

    const data = await parseGeminiResponse(response);

    if (!response.ok) {
      throw new ChatbotServiceError(
        getGeminiErrorMessage(data, response.status),
        response.status,
        data,
      );
    }

    const chatbotReply = extractChatbotReply(data, trimmedMessage);

    if (!chatbotReply.text) {
      throw new ChatbotServiceError(
        'SolMate Assistant returned an empty response.',
        502,
        data,
      );
    }

    return chatbotReply;
  } catch (error) {
    if (error instanceof ChatbotServiceError) {
      throw error;
    }

    throw new ChatbotServiceError(
      'Could not reach SolMate Assistant. Please check your internet connection and try again.',
    );
  }
}
