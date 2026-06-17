import {apiGet, apiPost} from './api';

export type SupportChatMessage = {
  id: number;
  sender_type: 'user' | 'bot' | 'admin' | 'system';
  sender_name?: string | null;
  body: string;
  metadata?: {
    suggestions?: string[];
    status?: string;
    event?: string;
    reason?: string;
  } | null;
  created_at?: string | null;
};

export type SupportChatConversationPayload = {
  conversation: {
    id: number;
    status: 'bot' | 'admin';
    is_admin_active: boolean;
    is_awaiting_admin: boolean;
    bot_fallback_count: number;
    customer?: {id: number; name: string} | null;
    admin?: {id: number; name: string} | null;
    escalated_at?: string | null;
    escalation_reason?: string | null;
    escalation_reason_label?: string | null;
    admin_joined_at?: string | null;
    last_message_at?: string | null;
  };
  messages: SupportChatMessage[];
};

export function fetchSupportConversation() {
  return apiGet<SupportChatConversationPayload>('/chat/conversation');
}

export function sendSupportMessage(message: string) {
  return apiPost<SupportChatConversationPayload>('/chat/conversation/messages', {
    message,
  });
}

export function requestSupportAdminTakeover(reason = 'manual_escalation') {
  return apiPost<SupportChatConversationPayload>('/chat/conversation/escalate', {
    reason,
  });
}
