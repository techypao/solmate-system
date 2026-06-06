import {filterChatSuggestions} from '../src/utils/chatSuggestions';

describe('chat suggestion filtering', () => {
  it('removes the current question and previously asked questions', () => {
    const suggestions = filterChatSuggestions(
      [
        'How do I create a quotation?',
        'Can you explain ROI for solar panels?',
        'How do I request an inspection?',
      ],
      ['Can you explain ROI for solar panels?'],
      'How do I create a quotation?',
    );

    expect(suggestions).toEqual(['How do I request an inspection?']);
  });

  it('deduplicates suggestions using normalized question text', () => {
    const suggestions = filterChatSuggestions([
      'What affects solar savings?',
      ' what affects solar savings ',
      'What affects solar savings',
      'Why does inspection matter?',
    ]);

    expect(suggestions).toEqual([
      'What affects solar savings?',
      'Why does inspection matter?',
    ]);
  });

  it('limits rendered suggestions to four unique relevant questions', () => {
    const suggestions = filterChatSuggestions([
      'How do quotations work?',
      'How do I request an inspection?',
      'What do notifications mean?',
      'What can SolMate help with?',
      'How does ROI work?',
    ]);

    expect(suggestions).toHaveLength(4);
    expect(suggestions).toEqual([
      'How do quotations work?',
      'How do I request an inspection?',
      'What do notifications mean?',
      'What can SolMate help with?',
    ]);
  });
});
