import Config from 'react-native-config';

const GEMINI_MODEL = 'gemini-2.5-flash';
const GEMINI_API_URL =
  `https://generativelanguage.googleapis.com/v1beta/models/${GEMINI_MODEL}:generateContent`;

export const SOLMATE_CHATBOT_SCOPE_FALLBACK =
  'I can currently help with SolMate quotations, inspection requests, service requests, testimonies, notifications, and general app guidance.';

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
- Initial quotation guidance
- Final quotation explanation
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
- Why inspection matters before final recommendations
- Why technician assessment is needed before final quotation

Important SolMate rules and knowledge:
- In SolMate, the customer begins with an initial quotation.
- The initial quotation is an early estimate based mainly on the customer's monthly electric bill.
- The customer does not prepare the final quotation.
- The final quotation is created by the technician after inspection and technical assessment.
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
- If the user asks for live or account-specific data, explain the feature generally instead of pretending to access data.
- If the user asks something ambiguous, answer using the most likely SolMate meaning.
- If the user asks about the difference between two SolMate features, clearly compare them.
- If the user seems confused, explain step by step.
- If the user asks something highly technical, site-specific, or requiring actual assessment, give a simple general explanation and then clarify that actual recommendations depend on inspection and technician evaluation.
- If the user asks about solar suitability, final configuration, or exact recommendations, explain that final guidance depends on inspection and technician assessment.
- When answering solar questions, keep the explanation basic, customer-friendly, and connected to the SolMate process when helpful.

Response style:
- Be friendly, professional, and easy to understand.
- Keep answers concise but complete.
- Prefer simple explanations over technical jargon.
- Respond in plain readable text.
- Do not use markdown symbols unless truly necessary.
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
            text: message,
          },
        ],
      },
    ],
    generationConfig: {
      temperature: 0.4,
      maxOutputTokens: 1024,
    },
    store: false,
  };
}

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

export async function sendChatbotMessage(message: string): Promise<string> {
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

    const chatbotReply = extractGeminiText(data);

    if (!chatbotReply) {
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
