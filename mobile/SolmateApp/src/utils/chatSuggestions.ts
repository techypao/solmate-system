const MAX_VISIBLE_CHAT_SUGGESTIONS = 4;

function normalizeQuestion(value: string) {
  return value
    .trim()
    .replace(/\s+/g, ' ')
    .replace(/[?!.\s]+$/g, '')
    .toLowerCase();
}

export function filterChatSuggestions(
  suggestions: unknown,
  askedQuestions: string[] = [],
  currentQuestion = '',
  maxSuggestions = MAX_VISIBLE_CHAT_SUGGESTIONS,
) {
  const blockedQuestions = new Set(
    [...askedQuestions, currentQuestion]
      .map(question => normalizeQuestion(question))
      .filter(Boolean),
  );
  const seenSuggestions = new Set<string>();

  if (!Array.isArray(suggestions)) {
    return [];
  }

  return suggestions
    .map(suggestion => (typeof suggestion === 'string' ? suggestion.trim() : ''))
    .filter(Boolean)
    .filter(suggestion => {
      const normalizedSuggestion = normalizeQuestion(suggestion);

      if (
        !normalizedSuggestion
        || blockedQuestions.has(normalizedSuggestion)
        || seenSuggestions.has(normalizedSuggestion)
      ) {
        return false;
      }

      seenSuggestions.add(normalizedSuggestion);
      return true;
    })
    .slice(0, maxSuggestions);
}
