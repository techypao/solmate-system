import React, {useEffect, useRef, useState} from 'react';
import {
  FlatList,
  KeyboardAvoidingView,
  Platform,
  Pressable,
  SafeAreaView,
  StyleSheet,
  Text,
  TextInput,
  View,
} from 'react-native';

type ChatRole = 'assistant' | 'user';

type ChatMessage = {
  id: string;
  role: ChatRole;
  text: string;
};

const QUICK_PROMPTS = [
  'What is an initial quotation?',
  'How do I request an inspection?',
  'What is the difference between inspection and service request?',
  'Who creates the final quotation?',
  'How do testimonies work?',
  'What do notifications mean?',
];

const WELCOME_MESSAGE =
  'Hi! I’m SolMate Assistant. I can help you with quotations, inspection requests, service requests, testimonies, notifications, and general app guidance.';

const INITIAL_MESSAGES: ChatMessage[] = [
  {
    id: 'assistant-welcome',
    role: 'assistant',
    text: WELCOME_MESSAGE,
  },
];

function MessageBubble({message}: {message: ChatMessage}) {
  const isUser = message.role === 'user';

  return (
    <View
      style={[
        styles.messageRow,
        isUser ? styles.messageRowUser : styles.messageRowAssistant,
      ]}>
      <View
        style={[
          styles.messageBubble,
          isUser ? styles.userBubble : styles.assistantBubble,
        ]}>
        <Text
          style={[
            styles.messageText,
            isUser ? styles.userMessageText : styles.assistantMessageText,
          ]}>
          {message.text}
        </Text>
      </View>
    </View>
  );
}

function PromptChip({
  label,
  onPress,
}: {
  label: string;
  onPress: () => void;
}) {
  return (
    <Pressable
      accessibilityRole="button"
      onPress={onPress}
      style={({pressed}) => [
        styles.promptChip,
        pressed ? styles.promptChipPressed : null,
      ]}>
      <Text style={styles.promptChipText}>{label}</Text>
    </Pressable>
  );
}

export default function ChatbotScreen() {
  const [draftMessage, setDraftMessage] = useState('');
  const [messages, setMessages] = useState<ChatMessage[]>(INITIAL_MESSAGES);
  const [isAssistantThinking] = useState(false);
  const listRef = useRef<FlatList<ChatMessage>>(null);

  const appendUserMessage = (rawText: string) => {
    const trimmedText = rawText.trim();

    if (!trimmedText) {
      return;
    }

    const nextMessage: ChatMessage = {
      id: `${Date.now()}-${trimmedText.length}`,
      role: 'user',
      text: trimmedText,
    };

    setMessages(currentMessages => [...currentMessages, nextMessage]);
    setDraftMessage('');
  };

  useEffect(() => {
    const timeoutId = setTimeout(() => {
      listRef.current?.scrollToEnd({animated: true});
    }, 40);

    return () => clearTimeout(timeoutId);
  }, [messages]);

  return (
    <SafeAreaView style={styles.safeArea}>
      <KeyboardAvoidingView
        behavior={Platform.OS === 'ios' ? 'padding' : undefined}
        keyboardVerticalOffset={Platform.OS === 'ios' ? 10 : 0}
        style={styles.keyboardAvoidingView}>
        <View style={styles.screenHeader}>
          <Text style={styles.screenEyebrow}>Customer support</Text>
          <Text style={styles.screenTitle}>SolMate Assistant</Text>
          <Text style={styles.screenSubtitle}>
            Ask common questions, review guidance, and prepare for future
            in-app assistant replies.
          </Text>
        </View>

        <FlatList
          ref={listRef}
          contentContainerStyle={styles.messageListContent}
          data={messages}
          keyExtractor={item => item.id}
          keyboardShouldPersistTaps="handled"
          ListHeaderComponent={
            <View>
              <View style={styles.welcomeCard}>
                <View style={styles.welcomeBadge}>
                  <Text style={styles.welcomeBadgeText}>Assistant ready</Text>
                </View>
                <Text style={styles.welcomeTitle}>Start with a quick prompt</Text>
                <Text style={styles.welcomeText}>
                  Tap one of the suggested prompts below or type your own
                  question. Messages are stored locally for now while the AI
                  integration is being prepared.
                </Text>
              </View>

              <View style={styles.promptSection}>
                <Text style={styles.promptSectionTitle}>Suggested questions</Text>
                <View style={styles.promptWrap}>
                  {QUICK_PROMPTS.map(prompt => (
                    <PromptChip
                      key={prompt}
                      label={prompt}
                      onPress={() => appendUserMessage(prompt)}
                    />
                  ))}
                </View>
              </View>

              <Text style={styles.chatSectionLabel}>Conversation</Text>
            </View>
          }
          renderItem={({item}) => <MessageBubble message={item} />}
          showsVerticalScrollIndicator={false}
        />

        {isAssistantThinking ? (
          <View style={styles.typingContainer}>
            <Text style={styles.typingText}>SolMate Assistant is preparing a reply...</Text>
          </View>
        ) : null}

        <View style={styles.composerWrap}>
          <View style={styles.composerCard}>
            <TextInput
              multiline={true}
              onChangeText={setDraftMessage}
              placeholder="Type your message here..."
              placeholderTextColor="#94a3b8"
              style={styles.input}
              value={draftMessage}
            />
            <Pressable
              accessibilityRole="button"
              disabled={!draftMessage.trim()}
              onPress={() => appendUserMessage(draftMessage)}
              style={({pressed}) => [
                styles.sendButton,
                !draftMessage.trim() ? styles.sendButtonDisabled : null,
                pressed && draftMessage.trim() ? styles.sendButtonPressed : null,
              ]}>
              <Text style={styles.sendButtonText}>Send</Text>
            </Pressable>
          </View>
          <Text style={styles.helperText}>
            UI-only mode: assistant responses will be connected in a later step.
          </Text>
        </View>
      </KeyboardAvoidingView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safeArea: {
    backgroundColor: '#f4f7fb',
    flex: 1,
  },
  keyboardAvoidingView: {
    flex: 1,
  },
  screenHeader: {
    paddingHorizontal: 20,
    paddingTop: 18,
  },
  screenEyebrow: {
    color: '#2563eb',
    fontSize: 12,
    fontWeight: '700',
    letterSpacing: 0.5,
    marginBottom: 8,
    textTransform: 'uppercase',
  },
  screenTitle: {
    color: '#0f172a',
    fontSize: 28,
    fontWeight: '800',
    marginBottom: 8,
  },
  screenSubtitle: {
    color: '#475569',
    fontSize: 14,
    lineHeight: 20,
  },
  messageListContent: {
    paddingBottom: 20,
    paddingHorizontal: 20,
    paddingTop: 18,
  },
  welcomeCard: {
    backgroundColor: '#e0f2fe',
    borderColor: '#bae6fd',
    borderRadius: 24,
    borderWidth: 1,
    marginBottom: 18,
    padding: 18,
  },
  welcomeBadge: {
    alignSelf: 'flex-start',
    backgroundColor: '#ffffff',
    borderRadius: 999,
    marginBottom: 12,
    paddingHorizontal: 12,
    paddingVertical: 6,
  },
  welcomeBadgeText: {
    color: '#0369a1',
    fontSize: 11,
    fontWeight: '700',
    textTransform: 'uppercase',
  },
  welcomeTitle: {
    color: '#0f172a',
    fontSize: 18,
    fontWeight: '700',
    marginBottom: 8,
  },
  welcomeText: {
    color: '#334155',
    fontSize: 14,
    lineHeight: 20,
  },
  promptSection: {
    marginBottom: 20,
  },
  promptSectionTitle: {
    color: '#0f172a',
    fontSize: 16,
    fontWeight: '700',
    marginBottom: 12,
  },
  promptWrap: {
    flexDirection: 'row',
    flexWrap: 'wrap',
  },
  promptChip: {
    backgroundColor: '#ffffff',
    borderColor: '#dbeafe',
    borderRadius: 999,
    borderWidth: 1,
    marginBottom: 10,
    marginRight: 10,
    paddingHorizontal: 14,
    paddingVertical: 10,
  },
  promptChipPressed: {
    opacity: 0.84,
  },
  promptChipText: {
    color: '#1d4ed8',
    fontSize: 13,
    fontWeight: '600',
    lineHeight: 18,
  },
  chatSectionLabel: {
    color: '#64748b',
    fontSize: 12,
    fontWeight: '700',
    letterSpacing: 0.5,
    marginBottom: 12,
    textTransform: 'uppercase',
  },
  messageRow: {
    marginBottom: 12,
  },
  messageRowAssistant: {
    alignItems: 'flex-start',
  },
  messageRowUser: {
    alignItems: 'flex-end',
  },
  messageBubble: {
    borderRadius: 22,
    maxWidth: '88%',
    paddingHorizontal: 16,
    paddingVertical: 12,
  },
  assistantBubble: {
    backgroundColor: '#ffffff',
    borderColor: '#e2e8f0',
    borderWidth: 1,
  },
  userBubble: {
    backgroundColor: '#2563eb',
  },
  messageText: {
    fontSize: 15,
    lineHeight: 22,
  },
  assistantMessageText: {
    color: '#0f172a',
  },
  userMessageText: {
    color: '#ffffff',
  },
  typingContainer: {
    marginHorizontal: 20,
    marginTop: 4,
  },
  typingText: {
    color: '#64748b',
    fontSize: 13,
  },
  composerWrap: {
    backgroundColor: '#f8fafc',
    borderTopColor: '#e2e8f0',
    borderTopWidth: 1,
    paddingHorizontal: 20,
    paddingBottom: 16,
    paddingTop: 12,
  },
  composerCard: {
    alignItems: 'flex-end',
    backgroundColor: '#ffffff',
    borderColor: '#dbeafe',
    borderRadius: 24,
    borderWidth: 1,
    flexDirection: 'row',
    padding: 8,
  },
  input: {
    color: '#0f172a',
    flex: 1,
    fontSize: 15,
    maxHeight: 120,
    minHeight: 48,
    paddingHorizontal: 10,
    paddingVertical: 12,
    textAlignVertical: 'top',
  },
  sendButton: {
    alignItems: 'center',
    backgroundColor: '#2563eb',
    borderRadius: 18,
    justifyContent: 'center',
    minHeight: 48,
    minWidth: 72,
    paddingHorizontal: 16,
  },
  sendButtonDisabled: {
    backgroundColor: '#bfdbfe',
  },
  sendButtonPressed: {
    opacity: 0.86,
  },
  sendButtonText: {
    color: '#ffffff',
    fontSize: 15,
    fontWeight: '700',
  },
  helperText: {
    color: '#64748b',
    fontSize: 12,
    lineHeight: 18,
    marginTop: 10,
    textAlign: 'center',
  },
});
