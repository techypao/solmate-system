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

const NAVY = '#152a4a';
const GOLD = '#e8a800';
const MUTED = '#7b8699';
const BG = '#e0e8f5';
const CARD = '#ffffff';

const QUICK_HELP = [
  {title: 'FAQ', subtitle: 'Common questions and answers.', prompt: 'What are the frequently asked questions?'},
  {title: 'Guide on Quotation', subtitle: 'How to generate initial/final quotes.', prompt: 'How do I create a quotation?'},
  {title: 'ROI Explanation', subtitle: 'Understand payback and savings.', prompt: 'Can you explain ROI for solar panels?'},
];

const QUICK_PROMPTS = [
  'What is an initial quotation?',
  'How do I request an inspection?',
  'What is the difference between inspection and service request?',
  'Who creates the final quotation?',
  'How do testimonies work?',
  'What do notifications mean?',
];

const WELCOME_TEXT = "Hi! I\u2019m SolBot";
const WELCOME_SUB = 'Ask about quotation, ROI or any solar related.';

const INITIAL_MESSAGES: ChatMessage[] = [];

/* \u2500\u2500 message bubble \u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500 */

function MessageBubble({message}: {message: ChatMessage}) {
  const isUser = message.sender === 'user';
  const isError = message.status === 'error';

  return (
    <View
      style={[
        cs.msgRow,
        isUser ? cs.msgRowUser : cs.msgRowBot,
      ]}>
      {!isUser && (
        <View style={cs.botAvatar}>
          <Text style={cs.botAvatarText}>{'\ud83e\udd16'}</Text>
        </View>
      )}
      <View style={cs.bubbleWrap}>
        <View style={[cs.bubble, isUser ? cs.userBubble : cs.botBubble, isError && cs.errorBubble]}>
          <Text style={[cs.msgSender, isUser ? cs.userSender : cs.botSender]}>
            {isUser ? 'You' : 'SolBot'}
          </Text>
          {isError && <Text style={cs.errorBadge}>Retry available</Text>}
          <Text
            style={[cs.msgText, isUser ? cs.userText : cs.botText, isError && cs.errorText]}>
            {message.text}
          </Text>
          <Text style={[cs.msgTime, isUser ? cs.userTime : cs.botTime]}>
            {formatTimestamp(message.timestamp)}
          </Text>
        </View>
      </View>
    </View>
  );
}

function TypingBubble() {
  return (
    <View style={[cs.msgRow, cs.msgRowBot]}>
      <View style={cs.botAvatar}>
        <Text style={cs.botAvatarText}>{'\ud83e\udd16'}</Text>
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

/* \u2500\u2500 main screen \u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500 */

export default function ChatbotScreen({navigation}: any) {
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

    setMessages(cur => [...cur, createMessage(trimmedText, 'user')]);
    if (clearDraft) {
      setDraftMessage('');
    }
    setLastFailedMessage('');

    try {
      setIsSending(true);
      const botReply = await sendChatbotMessage(trimmedText);
      console.log('[SolBot] Full response length:', botReply.length, '| Text:', botReply);
      if (!isMountedRef.current) {
        return;
      }
      setMessages(cur => [...cur, createMessage(botReply, 'bot')]);
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
    const t = setTimeout(() => listRef.current?.scrollToEnd({animated: true}), 40);
    return () => clearTimeout(t);
  }, [isSending, messages]);

  useEffect(() => {
    return () => {
      isMountedRef.current = false;
    };
  }, []);

  const hasMessages = messages.length > 0;

  return (
    <SafeAreaView style={cs.safe}>
      <KeyboardAvoidingView
        behavior={Platform.OS === 'ios' ? 'padding' : undefined}
        keyboardVerticalOffset={Platform.OS === 'ios' ? 10 : 0}
        style={cs.flex1}>

        {/* \u2500\u2500 sheet panel \u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500 */}
        <View style={cs.sheet}>
          {/* drag handle */}
          <View style={cs.handleRow}>
            <View style={cs.handle} />
          </View>

          {/* header row */}
          <View style={cs.headerRow}>
            <View style={cs.headerLeft}>
              <View style={cs.headerIcon}>
                <Text style={cs.headerIconText}>{'\ud83e\udd16'}</Text>
              </View>
              <View>
                <Text style={cs.headerTitle}>SolBot</Text>
                <Text style={cs.headerSub}>Help & FAQs</Text>
              </View>
            </View>
            <Pressable onPress={() => navigation.goBack()} style={cs.closeBtn}>
              <Text style={cs.closeBtnText}>{'\u2715'}</Text>
            </Pressable>
          </View>

          {/* \u2500\u2500 quick help + intro (only when no messages) \u2500\u2500 */}
          {!hasMessages && (
            <ScrollView
              style={cs.msgList}
              contentContainerStyle={{paddingBottom: 16}}
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
                <Text style={cs.moreHint}>More options inside chat \u2192</Text>
              </View>

              <View style={cs.introCard}>
                <Text style={cs.introTitle}>{WELCOME_TEXT}</Text>
                <Text style={cs.introSub}>{WELCOME_SUB}</Text>
              </View>
            </ScrollView>
          )}

          {/* \u2500\u2500 message list \u2500\u2500 */}
          {hasMessages && (
            <FlatList
              ref={listRef}
              contentContainerStyle={cs.msgListContent}
              data={messages}
              keyExtractor={item => item.id}
              keyboardShouldPersistTaps="handled"
              onContentSizeChange={() => listRef.current?.scrollToEnd({animated: true})}
              removeClippedSubviews={false}
              ListFooterComponent={isSending ? <TypingBubble /> : <View style={{height: 8}} />}
              renderItem={({item}) => <MessageBubble message={item} />}
              showsVerticalScrollIndicator={false}
              style={cs.msgList}
            />
          )}

          {/* \u2500\u2500 composer \u2500\u2500 */}
          <View style={cs.composerWrap}>
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
                <Text style={cs.sendBtnIcon}>{'\u27A4'}</Text>
              </Pressable>
            </View>

            {lastFailedMessage && !isSending ? (
              <Pressable
                accessibilityRole="button"
                onPress={() => sendMessage(lastFailedMessage, false)}
                style={({pressed}) => [cs.retryCard, pressed && cs.pressed]}>
                <View style={{flex: 1, paddingRight: 12}}>
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

/* \u2500\u2500 helpers \u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500 */

function createMessage(
  text: string,
  sender: ChatSender,
  status: ChatMessage['status'] = 'default',
): ChatMessage {
  return {
    id: sender + '-' + Date.now() + '-' + Math.random().toString(36).slice(2, 8),
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

/* \u2500\u2500 styles \u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500\u2500 */

const cs = StyleSheet.create({
  safe: {flex: 1, backgroundColor: BG},
  flex1: {flex: 1},
  pressed: {opacity: 0.85},

  /* sheet */
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

  /* drag handle */
  handleRow: {alignItems: 'center', paddingTop: 10, paddingBottom: 6},
  handle: {
    width: 40,
    height: 5,
    borderRadius: 3,
    backgroundColor: '#c4cdd8',
  },

  /* header */
  headerRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: 20,
    paddingBottom: 14,
    borderBottomWidth: 1,
    borderBottomColor: '#edf1f7',
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

  /* quick help */
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

  /* intro card */
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

  /* message list */
  msgList: {flex: 1},
  msgListContent: {paddingHorizontal: 20, paddingTop: 12, paddingBottom: 20},
  msgRow: {flexDirection: 'row', alignItems: 'flex-end', marginBottom: 12},
  msgRowBot: {},
  msgRowUser: {justifyContent: 'flex-end'},
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
  userText: {color: '#ffffff'},
  errorText: {color: '#9a3412'},
  msgTime: {fontSize: 10, fontWeight: '600', marginTop: 6, alignSelf: 'flex-start'},
  botTime: {color: '#94a3b8'},
  userTime: {color: '#8fa8d0'},
  typingRow: {flexDirection: 'row', alignItems: 'center'},
  typingText: {color: MUTED, fontSize: 13, marginLeft: 8},

  /* composer */
  composerWrap: {
    paddingHorizontal: 16,
    paddingBottom: 16,
    paddingTop: 10,
    borderTopWidth: 1,
    borderTopColor: '#edf1f7',
    backgroundColor: CARD,
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

  /* retry */
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
  retryTitle: {color: '#9a3412', fontSize: 13, fontWeight: '700', marginBottom: 2},
  retryText: {color: '#c2410c', fontSize: 12, lineHeight: 17},
  retryAction: {color: '#9a3412', fontSize: 13, fontWeight: '800'},
});
