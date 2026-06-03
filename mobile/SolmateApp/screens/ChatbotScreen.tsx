import React, {useCallback, useEffect, useRef, useState} from 'react';
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
import {
  fetchSupportConversation,
  requestSupportAdminTakeover,
  sendSupportMessage,
  SupportChatConversationPayload,
  SupportChatMessage,
} from '../src/services/supportChatService';

type ChatSender = 'user' | 'bot' | 'admin' | 'system';

type ChatMessage = {
  id: string;
  text: string;
  sender: ChatSender;
  timestamp: number;
  status?: 'default' | 'error';
  senderName?: string | null;
  suggestions?: string[];
  suggestionsEnabled?: boolean;
};

const NAVY = '#1A2B55';
const GOLD = '#F5C000';
const MUTED = '#6B7A99';
const BG = '#C8D8F0';
const CARD = '#ffffff';

const QUICK_HELP = [
  {title: 'FAQ', subtitle: 'Common questions and answers.', prompt: 'What are the frequently asked questions?'},
  {title: 'Guide on Quotation', subtitle: 'How to generate initial/final quotes.', prompt: 'How do I create a quotation?'},
  {title: 'ROI Explanation', subtitle: 'Understand payback and savings.', prompt: 'Can you explain ROI for solar panels?'},
];

const WELCOME_TEXT = 'Hi! I\'m SolBot';
const WELCOME_SUB = 'Ask about quotation, ROI or any solar related.';

const INITIAL_MESSAGES: ChatMessage[] = [];

function MessageBubble({
  message,
  isSending,
  onSuggestionPress,
}: {
  message: ChatMessage;
  isSending: boolean;
  onSuggestionPress: (messageId: string, suggestion: string) => void;
}) {
  const isUser = message.sender === 'user';
  const isAdmin = message.sender === 'admin';
  const isSystem = message.sender === 'system';
  const isError = message.status === 'error';
  const showSuggestions =
    !isUser
    && !isAdmin
    && !isSystem
    && message.suggestionsEnabled
    && Array.isArray(message.suggestions)
    && message.suggestions.length > 0;
  const senderLabel = isUser
    ? 'You'
    : isAdmin
      ? (message.senderName || 'Admin')
      : isSystem
        ? 'System'
        : 'SolBot';

  return (
    <View
      style={[
        cs.msgRow,
        isUser ? cs.msgRowUser : isSystem ? cs.msgRowSystem : cs.msgRowBot,
      ]}>
      {!isUser && !isSystem ? (
        <View style={cs.botAvatar}>
          <Text style={cs.botAvatarText}>{isAdmin ? 'A' : '🤖'}</Text>
        </View>
      ) : null}
      <View style={cs.bubbleWrap}>
        <View
          style={[
            cs.bubble,
            isUser
              ? cs.userBubble
              : isAdmin
                ? cs.adminBubble
                : isSystem
                  ? cs.systemBubble
                  : cs.botBubble,
            isError && cs.errorBubble,
          ]}>
          <Text
            style={[
              cs.msgSender,
              isUser
                ? cs.userSender
                : isAdmin
                  ? cs.adminSender
                  : isSystem
                    ? cs.systemSender
                    : cs.botSender,
            ]}>
            {senderLabel}
          </Text>
          {isError ? <Text style={cs.errorBadge}>Retry available</Text> : null}
          <Text
            style={[
              cs.msgText,
              isUser
                ? cs.userText
                : isAdmin
                  ? cs.adminText
                  : isSystem
                    ? cs.systemText
                    : cs.botText,
              isError && cs.errorText,
            ]}>
            {message.text}
          </Text>
          <Text
            style={[
              cs.msgTime,
              isUser
                ? cs.userTime
                : isAdmin
                  ? cs.adminTime
                  : isSystem
                    ? cs.systemTime
                    : cs.botTime,
            ]}>
            {formatTimestamp(message.timestamp)}
          </Text>
          {showSuggestions ? (
            <View style={cs.suggestionWrap}>
              {message.suggestions?.map(suggestion => (
                <Pressable
                  key={`${message.id}-${suggestion}`}
                  accessibilityRole="button"
                  disabled={isSending}
                  onPress={() => onSuggestionPress(message.id, suggestion)}
                  style={({pressed}) => [
                    cs.suggestionChip,
                    isSending && cs.suggestionChipDisabled,
                    pressed && !isSending && cs.pressed,
                  ]}>
                  <Text style={cs.suggestionChipText}>{suggestion}</Text>
                </Pressable>
              ))}
            </View>
          ) : null}
        </View>
      </View>
    </View>
  );
}

function TypingBubble() {
  return (
    <View style={[cs.msgRow, cs.msgRowBot]}>
      <View style={cs.botAvatar}>
        <Text style={cs.botAvatarText}>🤖</Text>
      </View>
      <View style={[cs.bubble, cs.botBubble]}>
        <Text style={cs.botSender}>SolBot</Text>
        <View style={cs.typingRow}>
          <ActivityIndicator color={GOLD} size="small" />
          <Text style={cs.typingText}>Thinking...</Text>
        </View>
      </View>
    </View>
  );
}

export default function ChatbotScreen({navigation}: any) {
  const [draftMessage, setDraftMessage] = useState('');
  const [messages, setMessages] = useState<ChatMessage[]>(INITIAL_MESSAGES);
  const [conversation, setConversation] = useState<SupportChatConversationPayload['conversation'] | null>(null);
  const [isSending, setIsSending] = useState(false);
  const [lastFailedMessage, setLastFailedMessage] = useState('');
  const listRef = useRef<FlatList<ChatMessage>>(null);
  const isMountedRef = useRef(true);

  const deactivateSuggestionChips = useCallback(
    (items: ChatMessage[]) =>
      items.map(item =>
        item.suggestionsEnabled ? {...item, suggestionsEnabled: false} : item,
      ),
    [],
  );

  const applyConversationPayload = useCallback((payload: SupportChatConversationPayload) => {
    setConversation(payload.conversation);
    setMessages(payload.messages.map(mapServerMessage));
  }, []);

  const loadConversation = useCallback(async () => {
    try {
      const payload = await fetchSupportConversation();

      if (!isMountedRef.current) {
        return;
      }

      applyConversationPayload(payload);
    } catch (error) {
      console.log('Load support conversation error:', error);
    }
  }, [applyConversationPayload]);

  const sendMessage = useCallback(async (rawText: string, clearDraft = true) => {
    const trimmedText = rawText.trim();

    if (!trimmedText || isSending) {
      return;
    }

    setMessages(cur => [
      ...deactivateSuggestionChips(cur),
      createMessage(trimmedText, 'user'),
    ]);

    if (clearDraft) {
      setDraftMessage('');
    }

    setLastFailedMessage('');

    try {
      setIsSending(true);
      const payload = await sendSupportMessage(trimmedText);

      if (!isMountedRef.current) {
        return;
      }

      applyConversationPayload(payload);
    } catch (error: any) {
      if (!isMountedRef.current) {
        return;
      }

      const errorMessage =
        typeof error?.message === 'string' && error.message.trim()
          ? error.message.trim()
          : 'I ran into a problem while responding. Please try again in a moment.';

      setMessages(cur => [
        ...cur,
        createMessage(
          errorMessage + '\n\nYou can tap Retry below to send your last question again.',
          'system',
          'error',
        ),
      ]);
      setLastFailedMessage(trimmedText);
    } finally {
      if (isMountedRef.current) {
        setIsSending(false);
      }
    }
  }, [applyConversationPayload, deactivateSuggestionChips, isSending]);

  const handleSuggestionPress = useCallback((messageId: string, suggestion: string) => {
    setMessages(cur =>
      cur.map(item =>
        item.id === messageId || item.suggestionsEnabled
          ? {...item, suggestionsEnabled: false}
          : item,
      ),
    );
    sendMessage(suggestion);
  }, [sendMessage]);

  const requestAdminTakeover = useCallback(async () => {
    if (isSending || conversation?.is_admin_active) {
      return;
    }

    try {
      setIsSending(true);
      const payload = await requestSupportAdminTakeover();

      if (!isMountedRef.current) {
        return;
      }

      applyConversationPayload(payload);
    } catch (error) {
      console.log('Admin takeover request error:', error);
    } finally {
      if (isMountedRef.current) {
        setIsSending(false);
      }
    }
  }, [applyConversationPayload, conversation?.is_admin_active, isSending]);

  useEffect(() => {
    const timer = setTimeout(() => listRef.current?.scrollToEnd({animated: true}), 40);
    return () => clearTimeout(timer);
  }, [isSending, messages]);

  useEffect(() => {
    loadConversation();
    const intervalId = setInterval(loadConversation, 5000);

    return () => {
      isMountedRef.current = false;
      clearInterval(intervalId);
    };
  }, [loadConversation]);

  const hasMessages = messages.length > 0;
  const isAdminActive = !!conversation?.is_admin_active;
  const isAwaitingAdmin = !!conversation?.is_awaiting_admin;
  const headerSubtitle = isAdminActive
    ? `Live admin support${conversation?.admin?.name ? ` · ${conversation.admin.name}` : ''}`
    : isAwaitingAdmin
      ? 'Waiting for admin takeover'
      : 'Help & FAQs';
  const statusBanner = isAdminActive
    ? `You are now chatting with ${conversation?.admin?.name || 'a real admin'}. SolBot is paused.`
    : isAwaitingAdmin
      ? 'A real admin has been requested. SolBot is paused until an admin joins this thread.'
      : '';

  return (
    <SafeAreaView style={cs.safe}>
      <KeyboardAvoidingView
        behavior={Platform.OS === 'ios' ? 'padding' : undefined}
        keyboardVerticalOffset={Platform.OS === 'ios' ? 10 : 0}
        style={cs.flex1}>
        <View style={cs.sheet}>
          <View style={cs.handleRow}>
            <View style={cs.handle} />
          </View>

          <View style={cs.headerRow}>
            <View style={cs.headerLeft}>
              <View style={cs.headerIcon}>
                <Text style={cs.headerIconText}>🤖</Text>
              </View>
              <View>
                <Text style={cs.headerTitle}>SolBot</Text>
                <Text style={cs.headerSub}>{headerSubtitle}</Text>
              </View>
            </View>
            <Pressable onPress={() => navigation.goBack()} style={cs.closeBtn}>
              <Text style={cs.closeBtnText}>✕</Text>
            </Pressable>
          </View>

          {statusBanner ? (
            <View style={[cs.statusBanner, isAdminActive ? cs.statusBannerAdmin : cs.statusBannerWaiting]}>
              <Text style={[cs.statusBannerText, isAdminActive ? cs.statusBannerTextAdmin : cs.statusBannerTextWaiting]}>
                {statusBanner}
              </Text>
            </View>
          ) : null}

          {!hasMessages ? (
            <ScrollView
              style={cs.msgList}
              contentContainerStyle={cs.quickScrollContent}
              showsVerticalScrollIndicator={false}
              keyboardShouldPersistTaps="handled">
              <View style={cs.quickSection}>
                <Text style={cs.quickTitle}>Quick Help</Text>
                {QUICK_HELP.map(item => (
                  <Pressable
                    key={item.title}
                    disabled={isSending}
                    onPress={() => sendMessage(item.prompt)}
                    style={({pressed}) => [cs.quickCard, pressed && cs.pressed]}>
                    <View style={cs.quickCardInner}>
                      <Text style={cs.quickCardTitle}>{item.title}</Text>
                      <Text style={cs.quickCardSub}>{item.subtitle}</Text>
                    </View>
                  </Pressable>
                ))}
                <Text style={cs.moreHint}>More options inside chat →</Text>
              </View>

              <View style={cs.introCard}>
                <Text style={cs.introTitle}>{WELCOME_TEXT}</Text>
                <Text style={cs.introSub}>{WELCOME_SUB}</Text>
              </View>
            </ScrollView>
          ) : (
            <FlatList
              ref={listRef}
              contentContainerStyle={cs.msgListContent}
              data={messages}
              keyExtractor={item => item.id}
              keyboardShouldPersistTaps="handled"
              onContentSizeChange={() => listRef.current?.scrollToEnd({animated: true})}
              removeClippedSubviews={false}
              ListFooterComponent={!isAdminActive && isSending ? <TypingBubble /> : <View style={cs.listFooterSpacer} />}
              renderItem={({item}) => (
                <MessageBubble
                  isSending={isSending}
                  message={item}
                  onSuggestionPress={handleSuggestionPress}
                />
              )}
              showsVerticalScrollIndicator={false}
              style={cs.msgList}
            />
          )}

          <View style={cs.composerWrap}>
            {!isAdminActive ? (
              <Pressable
                accessibilityRole="button"
                disabled={isSending || isAwaitingAdmin}
                onPress={requestAdminTakeover}
                style={({pressed}) => [
                  cs.escalateBtn,
                  (isSending || isAwaitingAdmin) && cs.escalateBtnDisabled,
                  pressed && !isSending && !isAwaitingAdmin && cs.pressed,
                ]}>
                <Text style={cs.escalateBtnText}>
                  {isAwaitingAdmin ? 'Admin Requested' : 'Talk to a Real Admin'}
                </Text>
              </Pressable>
            ) : null}

            <View style={cs.composer}>
              <TextInput
                autoCapitalize="sentences"
                blurOnSubmit={false}
                editable={!isSending}
                enablesReturnKeyAutomatically
                multiline
                onChangeText={setDraftMessage}
                placeholder="Type a message..."
                placeholderTextColor="#a0aec0"
                returnKeyType="send"
                style={cs.input}
                value={draftMessage}
              />
              <Pressable
                accessibilityRole="button"
                disabled={isSending || !draftMessage.trim()}
                onPress={() => sendMessage(draftMessage)}
                style={({pressed}) => [
                  cs.sendBtn,
                  (isSending || !draftMessage.trim()) && cs.sendBtnDisabled,
                  pressed && draftMessage.trim() && !isSending && cs.sendBtnPressed,
                ]}>
                <Text style={cs.sendBtnIcon}>➤</Text>
              </Pressable>
            </View>

            {lastFailedMessage && !isSending ? (
              <Pressable
                accessibilityRole="button"
                onPress={() => sendMessage(lastFailedMessage, false)}
                style={({pressed}) => [cs.retryCard, pressed && cs.pressed]}>
                <View style={cs.retryTextWrap}>
                  <Text style={cs.retryTitle}>Message not delivered</Text>
                  <Text style={cs.retryText}>Tap to retry your last question.</Text>
                </View>
                <Text style={cs.retryAction}>Retry</Text>
              </Pressable>
            ) : null}
          </View>
        </View>
      </KeyboardAvoidingView>
    </SafeAreaView>
  );
}

function createMessage(
  text: string,
  sender: ChatSender,
  status: ChatMessage['status'] = 'default',
  extras: Pick<ChatMessage, 'suggestions' | 'suggestionsEnabled' | 'senderName'> = {},
): ChatMessage {
  return {
    id: `${sender}-${Date.now()}-${Math.random().toString(36).slice(2, 8)}`,
    text,
    sender,
    status,
    timestamp: Date.now(),
    senderName: extras.senderName,
    suggestions: extras.suggestions,
    suggestionsEnabled: extras.suggestionsEnabled,
  };
}

function mapServerMessage(message: SupportChatMessage): ChatMessage {
  const suggestions = Array.isArray(message.metadata?.suggestions)
    ? message.metadata.suggestions
    : [];

  return createMessage(
    message.body,
    message.sender_type,
    message.metadata?.status === 'error' ? 'error' : 'default',
    {
      senderName: message.sender_name ?? null,
      suggestions,
      suggestionsEnabled: message.sender_type === 'bot' && suggestions.length > 0,
    },
  );
}

function formatTimestamp(timestamp: number) {
  return new Date(timestamp).toLocaleTimeString([], {
    hour: 'numeric',
    minute: '2-digit',
  });
}

const cs = StyleSheet.create({
  safe: {flex: 1, backgroundColor: BG},
  flex1: {flex: 1},
  pressed: {opacity: 0.85},
  sheet: {
    flex: 1,
    backgroundColor: CARD,
    borderTopLeftRadius: 28,
    borderTopRightRadius: 28,
    marginTop: 8,
    shadowColor: '#3a4f73',
    shadowOffset: {width: 0, height: -4},
    shadowOpacity: 0.12,
    shadowRadius: 16,
    elevation: 8,
  },
  handleRow: {alignItems: 'center', paddingTop: 10, paddingBottom: 6},
  handle: {
    width: 40,
    height: 5,
    borderRadius: 3,
    backgroundColor: '#c4cdd8',
  },
  headerRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: 20,
    paddingBottom: 14,
    borderBottomWidth: 1,
    borderBottomColor: '#D4E0F2',
  },
  headerLeft: {flexDirection: 'row', alignItems: 'center'},
  headerIcon: {
    width: 38,
    height: 38,
    borderRadius: 12,
    backgroundColor: '#eaf0fb',
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: 10,
  },
  headerIconText: {fontSize: 20},
  headerTitle: {fontSize: 17, fontWeight: '800', color: NAVY},
  headerSub: {fontSize: 12, color: MUTED},
  closeBtn: {
    width: 34,
    height: 34,
    borderRadius: 17,
    backgroundColor: '#f1f4f9',
    alignItems: 'center',
    justifyContent: 'center',
  },
  closeBtnText: {fontSize: 16, color: NAVY, fontWeight: '700'},
  statusBanner: {
    marginHorizontal: 20,
    marginTop: 14,
    borderRadius: 14,
    paddingHorizontal: 14,
    paddingVertical: 12,
  },
  statusBannerWaiting: {
    backgroundColor: '#fff7ed',
    borderWidth: 1,
    borderColor: '#fdba74',
  },
  statusBannerAdmin: {
    backgroundColor: '#ecfdf3',
    borderWidth: 1,
    borderColor: '#86efac',
  },
  statusBannerText: {
    fontSize: 13,
    fontWeight: '700',
    lineHeight: 18,
  },
  statusBannerTextWaiting: {color: '#9a3412'},
  statusBannerTextAdmin: {color: '#166534'},
  quickScrollContent: {paddingBottom: 16},
  quickSection: {paddingHorizontal: 20, paddingTop: 16},
  quickTitle: {fontSize: 16, fontWeight: '800', color: NAVY, marginBottom: 10},
  quickCard: {
    backgroundColor: '#edf2fa',
    borderRadius: 16,
    paddingHorizontal: 18,
    paddingVertical: 14,
    marginBottom: 8,
  },
  quickCardInner: {},
  quickCardTitle: {fontSize: 15, fontWeight: '800', color: NAVY, marginBottom: 3},
  quickCardSub: {fontSize: 13, color: MUTED},
  moreHint: {fontSize: 12, color: MUTED, marginTop: 4, marginBottom: 8},
  introCard: {
    marginHorizontal: 20,
    marginTop: 6,
    backgroundColor: '#edf2fa',
    borderRadius: 16,
    paddingHorizontal: 18,
    paddingVertical: 16,
    marginBottom: 12,
  },
  introTitle: {fontSize: 17, fontWeight: '800', color: NAVY, marginBottom: 4},
  introSub: {fontSize: 13, color: MUTED, lineHeight: 19},
  msgList: {flex: 1},
  msgListContent: {paddingHorizontal: 20, paddingTop: 12, paddingBottom: 20},
  listFooterSpacer: {height: 8},
  msgRow: {flexDirection: 'row', alignItems: 'flex-end', marginBottom: 12},
  msgRowBot: {},
  msgRowUser: {justifyContent: 'flex-end'},
  msgRowSystem: {justifyContent: 'center'},
  botAvatar: {
    width: 30,
    height: 30,
    borderRadius: 15,
    backgroundColor: '#eaf0fb',
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: 8,
    marginBottom: 2,
  },
  botAvatarText: {fontSize: 14},
  bubbleWrap: {
    maxWidth: '80%',
    flexShrink: 1,
  },
  bubble: {
    borderRadius: 20,
    paddingHorizontal: 14,
    paddingVertical: 12,
  },
  botBubble: {
    backgroundColor: '#f4f7fb',
    borderWidth: 1,
    borderColor: '#e4eaf3',
  },
  adminBubble: {
    backgroundColor: '#fff4cc',
    borderWidth: 1,
    borderColor: '#fde68a',
  },
  systemBubble: {
    backgroundColor: '#eef2ff',
    borderWidth: 1,
    borderColor: '#c7d2fe',
  },
  userBubble: {
    backgroundColor: NAVY,
  },
  errorBubble: {
    backgroundColor: '#fff7ed',
    borderColor: '#fdba74',
  },
  msgSender: {
    fontSize: 11,
    fontWeight: '800',
    letterSpacing: 0.3,
    marginBottom: 6,
    textTransform: 'uppercase',
  },
  botSender: {color: GOLD},
  adminSender: {color: '#b45309'},
  systemSender: {color: '#4338ca'},
  userSender: {color: '#8fa8d0'},
  errorBadge: {
    alignSelf: 'flex-start',
    color: '#c2410c',
    fontSize: 11,
    fontWeight: '700',
    marginBottom: 6,
  },
  msgText: {fontSize: 14, lineHeight: 21},
  botText: {color: '#1e293b'},
  adminText: {color: '#92400e'},
  systemText: {color: '#3730a3'},
  userText: {color: '#ffffff'},
  errorText: {color: '#9a3412'},
  msgTime: {fontSize: 10, fontWeight: '600', marginTop: 6, alignSelf: 'flex-start'},
  botTime: {color: '#7F92A3'},
  adminTime: {color: '#b45309'},
  systemTime: {color: '#6366f1'},
  userTime: {color: '#8fa8d0'},
  suggestionWrap: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    marginTop: 10,
  },
  suggestionChip: {
    backgroundColor: '#ffffff',
    borderWidth: 1,
    borderColor: '#d8e3f0',
    borderRadius: 999,
    paddingHorizontal: 12,
    paddingVertical: 8,
    marginRight: 8,
    marginBottom: 8,
  },
  suggestionChipDisabled: {
    opacity: 0.55,
  },
  suggestionChipText: {
    color: NAVY,
    fontSize: 12,
    fontWeight: '700',
  },
  typingRow: {flexDirection: 'row', alignItems: 'center'},
  typingText: {color: MUTED, fontSize: 13, marginLeft: 8},
  composerWrap: {
    paddingHorizontal: 16,
    paddingBottom: 16,
    paddingTop: 10,
    borderTopWidth: 1,
    borderTopColor: '#D4E0F2',
    backgroundColor: CARD,
  },
  escalateBtn: {
    alignSelf: 'flex-start',
    backgroundColor: '#fff7ed',
    borderWidth: 1,
    borderColor: '#fdba74',
    borderRadius: 999,
    paddingHorizontal: 14,
    paddingVertical: 10,
    marginBottom: 10,
  },
  escalateBtnDisabled: {
    opacity: 0.6,
  },
  escalateBtnText: {
    color: '#9a3412',
    fontSize: 12,
    fontWeight: '800',
    letterSpacing: 0.3,
    textTransform: 'uppercase',
  },
  composer: {
    flexDirection: 'row',
    alignItems: 'flex-end',
    backgroundColor: '#f4f7fb',
    borderRadius: 24,
    borderWidth: 1,
    borderColor: '#dfe6f0',
    paddingLeft: 14,
    paddingRight: 6,
    paddingVertical: 4,
  },
  input: {
    flex: 1,
    fontSize: 14,
    color: '#1e293b',
    maxHeight: 110,
    minHeight: 42,
    paddingVertical: 10,
    textAlignVertical: 'top',
  },
  sendBtn: {
    width: 42,
    height: 42,
    borderRadius: 21,
    backgroundColor: GOLD,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 2,
  },
  sendBtnDisabled: {backgroundColor: '#dde3ec'},
  sendBtnPressed: {opacity: 0.85},
  sendBtnIcon: {fontSize: 18, color: '#fff'},
  retryCard: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    backgroundColor: '#fff7ed',
    borderColor: '#fdba74',
    borderRadius: 14,
    borderWidth: 1,
    marginTop: 10,
    paddingHorizontal: 14,
    paddingVertical: 12,
  },
  retryTextWrap: {flex: 1, paddingRight: 12},
  retryTitle: {color: '#9a3412', fontSize: 13, fontWeight: '700', marginBottom: 2},
  retryText: {color: '#c2410c', fontSize: 12, lineHeight: 17},
  retryAction: {color: '#9a3412', fontSize: 13, fontWeight: '800'},
});