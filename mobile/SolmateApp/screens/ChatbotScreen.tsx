import React, {useEffect, useRef, useState} from 'react';
import {
  ActivityIndicator,
  FlatList,
  KeyboardAvoidingView,
  Platform,
  Pressable,
  SafeAreaView,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  View,
} from 'react-native';
import {sendChatbotMessage} from '../src/services/chatbotService';

type ChatSender = 'user' | 'bot';

type ChatMessage = {
  id: string;
  text: string;
  sender: ChatSender;
  timestamp: number;
  status?: 'default' | 'error';
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

const INITIAL_MESSAGES: ChatMessage[] = [];

function MessageBubble({message}: {message: ChatMessage}) {
  const isUser = message.sender === 'user';
  const isError = message.status === 'error';

  return (
    <View
      style={[
        styles.messageRow,
        isUser ? styles.messageRowUser : styles.messageRowAssistant,
      ]}>
      {!isUser ? (
        <View style={[styles.messageAvatar, styles.assistantAvatar]}>
          <Text style={[styles.messageAvatarText, styles.assistantAvatarText]}>
            AI
          </Text>
        </View>
      ) : null}

      <View
        style={[
          styles.messageBubbleWrap,
          isUser ? styles.messageBubbleWrapUser : styles.messageBubbleWrapAssistant,
        ]}>
        <View
          style={[
            styles.messageBubble,
            isUser ? styles.userBubble : styles.assistantBubble,
            isError ? styles.errorBubble : null,
          ]}>
          <Text
            style={[
              styles.messageSenderLabel,
              isUser ? styles.userSenderLabel : styles.assistantSenderLabel,
            ]}>
            {isUser ? 'You' : 'SolMate Assistant'}
          </Text>
          {isError ? <Text style={styles.errorBadge}>Retry available</Text> : null}
          <Text
            android_hyphenationFrequency="normal"
            style={[
              styles.messageText,
              isUser ? styles.userMessageText : styles.assistantMessageText,
              isError ? styles.errorMessageText : null,
            ]}
            textBreakStrategy="highQuality">
            {message.text}
          </Text>
          <Text
            style={[
              styles.messageTimestamp,
              isUser ? styles.userTimestamp : styles.assistantTimestamp,
            ]}>
            {formatTimestamp(message.timestamp)}
          </Text>
        </View>
      </View>

      {isUser ? (
        <View style={[styles.messageAvatar, styles.userAvatar]}>
          <Text style={[styles.messageAvatarText, styles.userAvatarText]}>
            You
          </Text>
        </View>
      ) : null}
    </View>
  );
}

function TypingBubble() {
  return (
    <View style={[styles.messageRow, styles.messageRowAssistant]}>
      <View style={[styles.messageAvatar, styles.assistantAvatar]}>
        <Text style={[styles.messageAvatarText, styles.assistantAvatarText]}>AI</Text>
      </View>
      <View style={[styles.messageBubbleWrap, styles.messageBubbleWrapAssistant]}>
        <View
          style={[
            styles.messageBubble,
            styles.assistantBubble,
            styles.typingBubble,
          ]}>
          <Text style={styles.typingLabel}>SolMate Assistant</Text>
          <View style={styles.typingIndicator}>
            <ActivityIndicator color="#2563eb" size="small" />
            <Text style={styles.typingText}>
              Reviewing your SolMate question...
            </Text>
          </View>
        </View>
      </View>
    </View>
  );
}

function PromptChip({
  label,
  onPress,
  disabled = false,
}: {
  label: string;
  onPress: () => void;
  disabled?: boolean;
}) {
  return (
    <Pressable
      accessibilityRole="button"
      disabled={disabled}
      onPress={onPress}
      style={({pressed}) => [
        styles.promptChip,
        disabled ? styles.promptChipDisabled : null,
        pressed ? styles.promptChipPressed : null,
      ]}>
      <Text style={styles.promptChipText}>{label}</Text>
    </Pressable>
  );
}

export default function ChatbotScreen() {
  const [draftMessage, setDraftMessage] = useState('');
  const [messages, setMessages] = useState<ChatMessage[]>(INITIAL_MESSAGES);
  const [isSending, setIsSending] = useState(false);
  const [lastFailedMessage, setLastFailedMessage] = useState('');
  const listRef = useRef<FlatList<ChatMessage>>(null);
  const isMountedRef = useRef(true);

  const sendMessage = async (rawText: string, clearDraft = true) => {
    const trimmedText = rawText.trim();

    if (!trimmedText || isSending) {
      return;
    }

    setMessages(currentMessages => [
      ...currentMessages,
      createMessage(trimmedText, 'user'),
    ]);
    if (clearDraft) {
      setDraftMessage('');
    }
    setLastFailedMessage('');

    try {
      setIsSending(true);
      const botReply = await sendChatbotMessage(trimmedText);

      if (!isMountedRef.current) {
        return;
      }

      setMessages(currentMessages => [
        ...currentMessages,
        createMessage(botReply, 'bot'),
      ]);
    } catch (error: any) {
      if (!isMountedRef.current) {
        return;
      }

      const errorMessage =
        typeof error?.message === 'string' && error.message.trim()
          ? error.message.trim()
          : 'I ran into a problem while responding. Please try again in a moment.';

      setMessages(currentMessages => [
        ...currentMessages,
        createMessage(
          `${errorMessage}\n\nYou can tap Retry below to send your last SolMate question again.`,
          'bot',
          'error',
        ),
      ]);
      setLastFailedMessage(trimmedText);
    } finally {
      if (isMountedRef.current) {
        setIsSending(false);
      }
    }
  };

  useEffect(() => {
    const timeoutId = setTimeout(() => {
      listRef.current?.scrollToEnd({animated: true});
    }, 40);

    return () => clearTimeout(timeoutId);
  }, [isSending, messages]);

  useEffect(() => {
    return () => {
      isMountedRef.current = false;
    };
  }, []);

  return (
    <SafeAreaView style={styles.safeArea}>
      <KeyboardAvoidingView
        behavior={Platform.OS === 'ios' ? 'padding' : undefined}
        keyboardVerticalOffset={Platform.OS === 'ios' ? 10 : 0}
        style={styles.keyboardAvoidingView}>
        <View style={styles.screenHeader}>
          <Text style={styles.screenEyebrow}>Customer support</Text>
          <Text style={styles.screenTitle}>SolMate Assistant</Text>
          {messages.length === 0 && (
            <Text style={styles.screenSubtitle}>
              Ask common questions and get concise SolMate guidance right inside
              the app.
            </Text>
          )}
        </View>

        {messages.length === 0 && (
          <View style={styles.introSection}>
            <View style={styles.welcomeCard}>
              <View style={styles.welcomeTopRow}>
                <View style={styles.welcomeBadge}>
                  <Text style={styles.welcomeBadgeText}>SolMate help</Text>
                </View>
                <View style={styles.welcomeStatusPill}>
                  <View style={styles.welcomeStatusDot} />
                  <Text style={styles.welcomeStatusText}>Gemini ready</Text>
                </View>
              </View>
              <Text style={styles.welcomeText}>{WELCOME_MESSAGE}</Text>
            </View>
          </View>
        )}

        <View style={styles.promptBar}>
          <ScrollView
            horizontal
            showsHorizontalScrollIndicator={false}
            contentContainerStyle={styles.promptScrollContent}
            keyboardShouldPersistTaps="handled">
            {QUICK_PROMPTS.map(prompt => (
              <PromptChip
                disabled={isSending}
                key={prompt}
                label={prompt}
                onPress={() => sendMessage(prompt)}
              />
            ))}
          </ScrollView>
        </View>

        <View style={styles.chatSection}>
          <FlatList
            ref={listRef}
            contentContainerStyle={styles.messageListContent}
            data={messages}
            keyExtractor={item => item.id}
            keyboardShouldPersistTaps="handled"
            onContentSizeChange={() => listRef.current?.scrollToEnd({animated: true})}
            removeClippedSubviews={false}
            ListEmptyComponent={
              <View style={styles.emptyState}>
                <Text style={styles.emptyStateTitle}>No messages yet</Text>
                <Text style={styles.emptyStateText}>
                  Start the conversation with one of the prompts above or type
                  your own SolMate question below.
                </Text>
              </View>
            }
            ListFooterComponent={
              isSending ? <TypingBubble /> : <View style={styles.listFooterSpacer} />
            }
            renderItem={({item}) => <MessageBubble message={item} />}
            showsVerticalScrollIndicator={false}
            style={styles.messageList}
          />
        </View>

        <View style={styles.composerWrap}>
          <View style={styles.composerCard}>
            <TextInput
              autoCapitalize="sentences"
              blurOnSubmit={false}
              editable={!isSending}
              enablesReturnKeyAutomatically={true}
              multiline={true}
              onChangeText={setDraftMessage}
              placeholder="Ask SolMate Assistant a question..."
              placeholderTextColor="#94a3b8"
              returnKeyType="send"
              style={styles.input}
              value={draftMessage}
            />
            <Pressable
              accessibilityRole="button"
              disabled={isSending || !draftMessage.trim()}
              onPress={() => sendMessage(draftMessage)}
              style={({pressed}) => [
                styles.sendButton,
                isSending || !draftMessage.trim()
                  ? styles.sendButtonDisabled
                  : null,
                pressed && draftMessage.trim() && !isSending
                  ? styles.sendButtonPressed
                  : null,
              ]}>
              <Text style={styles.sendButtonText}>
                {isSending ? 'Sending' : 'Send'}
              </Text>
            </Pressable>
          </View>
          {lastFailedMessage && !isSending ? (
            <Pressable
              accessibilityRole="button"
              onPress={() => sendMessage(lastFailedMessage, false)}
              style={({pressed}) => [
                styles.retryCard,
                pressed ? styles.retryCardPressed : null,
              ]}>
              <View style={styles.retryTextWrap}>
                <Text style={styles.retryTitle}>Message not delivered</Text>
                <Text style={styles.retryText}>
                  Tap to retry your last SolMate question.
                </Text>
              </View>
              <Text style={styles.retryAction}>Retry</Text>
            </Pressable>
          ) : null}
          <Text style={styles.helperText}>
            SolMate Assistant answers are limited to SolMate-related guidance
            and cannot access live account-specific records.
          </Text>
        </View>
      </KeyboardAvoidingView>
    </SafeAreaView>
  );
}

function createMessage(
  text: string,
  sender: ChatSender,
  status: ChatMessage['status'] = 'default',
): ChatMessage {
  return {
    id: `${sender}-${Date.now()}-${Math.random().toString(36).slice(2, 8)}`,
    text,
    sender,
    status,
    timestamp: Date.now(),
  };
}

function formatTimestamp(timestamp: number) {
  return new Date(timestamp).toLocaleTimeString([], {
    hour: 'numeric',
    minute: '2-digit',
  });
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
    paddingTop: 12,
    paddingBottom: 4,
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
    fontSize: 22,
    fontWeight: '800',
    marginBottom: 4,
  },
  screenSubtitle: {
    color: '#475569',
    fontSize: 14,
    lineHeight: 20,
  },
  introSection: {
    paddingHorizontal: 20,
    paddingTop: 10,
  },
  welcomeCard: {
    backgroundColor: '#eff6ff',
    borderColor: '#bfdbfe',
    borderRadius: 16,
    borderWidth: 1,
    padding: 14,
  },
  welcomeTopRow: {
    alignItems: 'center',
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 8,
  },
  welcomeBadge: {
    alignSelf: 'flex-start',
    backgroundColor: '#ffffff',
    borderRadius: 999,
    paddingHorizontal: 12,
    paddingVertical: 6,
  },
  welcomeBadgeText: {
    color: '#1d4ed8',
    fontSize: 11,
    fontWeight: '700',
    textTransform: 'uppercase',
  },
  welcomeStatusPill: {
    alignItems: 'center',
    backgroundColor: '#dbeafe',
    borderRadius: 999,
    flexDirection: 'row',
    paddingHorizontal: 10,
    paddingVertical: 6,
  },
  welcomeStatusDot: {
    backgroundColor: '#16a34a',
    borderRadius: 999,
    height: 8,
    marginRight: 6,
    width: 8,
  },
  welcomeStatusText: {
    color: '#1e40af',
    fontSize: 11,
    fontWeight: '700',
    textTransform: 'uppercase',
  },
  welcomeText: {
    color: '#334155',
    fontSize: 13,
    lineHeight: 18,
  },
  promptBar: {
    borderBottomColor: '#e2e8f0',
    borderBottomWidth: 1,
    paddingVertical: 8,
  },
  promptScrollContent: {
    paddingHorizontal: 20,
  },
  promptChip: {
    backgroundColor: '#ffffff',
    borderColor: '#cbd5e1',
    borderRadius: 999,
    borderWidth: 1,
    marginRight: 8,
    paddingHorizontal: 12,
    paddingVertical: 8,
  },
  promptChipPressed: {
    backgroundColor: '#eff6ff',
    borderColor: '#93c5fd',
    opacity: 0.94,
  },
  promptChipDisabled: {
    opacity: 0.55,
  },
  promptChipText: {
    color: '#334155',
    fontSize: 13,
    fontWeight: '600',
    lineHeight: 18,
  },
  chatSection: {
    flex: 1,
    paddingHorizontal: 20,
    paddingTop: 4,
  },
  messageList: {
    flex: 1,
  },
  messageListContent: {
    paddingBottom: 24,
    paddingTop: 4,
  },
  emptyState: {
    backgroundColor: '#f8fafc',
    borderColor: '#e2e8f0',
    borderRadius: 20,
    borderStyle: 'dashed',
    borderWidth: 1,
    padding: 18,
  },
  emptyStateTitle: {
    color: '#0f172a',
    fontSize: 16,
    fontWeight: '700',
    marginBottom: 8,
  },
  emptyStateText: {
    color: '#64748b',
    fontSize: 14,
    lineHeight: 20,
  },
  messageRow: {
    alignItems: 'flex-end',
    flexDirection: 'row',
    marginBottom: 12,
  },
  messageRowAssistant: {},
  messageRowUser: {
    justifyContent: 'flex-end',
  },
  messageAvatar: {
    alignItems: 'center',
    borderRadius: 999,
    height: 34,
    justifyContent: 'center',
    marginTop: 4,
    width: 34,
  },
  assistantAvatar: {
    backgroundColor: '#dbeafe',
    marginRight: 10,
  },
  userAvatar: {
    backgroundColor: '#dbeafe',
    marginLeft: 10,
  },
  messageAvatarText: {
    fontSize: 10,
    fontWeight: '800',
    textTransform: 'uppercase',
  },
  assistantAvatarText: {
    color: '#1d4ed8',
  },
  userAvatarText: {
    color: '#1e40af',
  },
  messageBubbleWrap: {
    flexShrink: 1,
    maxWidth: '82%',
    minWidth: 0,
  },
  messageBubbleWrapAssistant: {
    alignItems: 'flex-start',
  },
  messageBubbleWrapUser: {
    alignItems: 'flex-end',
  },
  messageBubble: {
    borderRadius: 22,
    paddingHorizontal: 16,
    paddingVertical: 14,
  },
  assistantBubble: {
    backgroundColor: '#ffffff',
    borderColor: '#e2e8f0',
    borderWidth: 1,
    shadowColor: '#0f172a',
    shadowOffset: {
      width: 0,
      height: 4,
    },
    shadowOpacity: 0.06,
    shadowRadius: 10,
    elevation: 1,
  },
  userBubble: {
    backgroundColor: '#1d4ed8',
  },
  errorBubble: {
    backgroundColor: '#fff7ed',
    borderColor: '#fdba74',
  },
  messageSenderLabel: {
    fontSize: 11,
    fontWeight: '800',
    letterSpacing: 0.3,
    marginBottom: 8,
    textTransform: 'uppercase',
  },
  assistantSenderLabel: {
    color: '#2563eb',
  },
  userSenderLabel: {
    color: '#dbeafe',
  },
  errorBadge: {
    alignSelf: 'flex-start',
    color: '#c2410c',
    fontSize: 11,
    fontWeight: '700',
    marginBottom: 8,
  },
  messageText: {
    fontSize: 15,
    lineHeight: 22,
  },
  messageTimestamp: {
    alignSelf: 'flex-start',
    fontSize: 11,
    fontWeight: '600',
    marginTop: 8,
  },
  assistantMessageText: {
    color: '#0f172a',
  },
  assistantTimestamp: {
    color: '#64748b',
  },
  errorMessageText: {
    color: '#9a3412',
  },
  userMessageText: {
    color: '#ffffff',
  },
  userTimestamp: {
    color: '#dbeafe',
  },
  typingBubble: {
    paddingVertical: 14,
  },
  typingLabel: {
    color: '#2563eb',
    fontSize: 11,
    fontWeight: '800',
    letterSpacing: 0.3,
    marginBottom: 8,
    textTransform: 'uppercase',
  },
  typingIndicator: {
    alignItems: 'center',
    flexDirection: 'row',
  },
  typingText: {
    color: '#475569',
    fontSize: 13,
    marginLeft: 10,
  },
  listFooterSpacer: {
    height: 8,
  },
  composerWrap: {
    backgroundColor: '#ffffff',
    borderTopColor: '#e2e8f0',
    borderTopWidth: 1,
    paddingHorizontal: 20,
    paddingBottom: 18,
    paddingTop: 14,
  },
  composerCard: {
    alignItems: 'flex-end',
    backgroundColor: '#ffffff',
    borderColor: '#cbd5e1',
    borderRadius: 26,
    borderWidth: 1,
    flexDirection: 'row',
    padding: 8,
    shadowColor: '#0f172a',
    shadowOffset: {
      width: 0,
      height: 8,
    },
    shadowOpacity: 0.05,
    shadowRadius: 16,
    elevation: 2,
  },
  input: {
    color: '#0f172a',
    flex: 1,
    fontSize: 15,
    maxHeight: 132,
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
  retryCard: {
    alignItems: 'center',
    backgroundColor: '#fff7ed',
    borderColor: '#fdba74',
    borderRadius: 18,
    borderWidth: 1,
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginTop: 12,
    paddingHorizontal: 16,
    paddingVertical: 14,
  },
  retryCardPressed: {
    opacity: 0.9,
  },
  retryTextWrap: {
    flex: 1,
    paddingRight: 12,
  },
  retryTitle: {
    color: '#9a3412',
    fontSize: 14,
    fontWeight: '700',
    marginBottom: 2,
  },
  retryText: {
    color: '#c2410c',
    fontSize: 13,
    lineHeight: 18,
  },
  retryAction: {
    color: '#9a3412',
    fontSize: 14,
    fontWeight: '800',
  },
  helperText: {
    color: '#64748b',
    fontSize: 12,
    lineHeight: 18,
    marginTop: 10,
    textAlign: 'center',
  },
});
