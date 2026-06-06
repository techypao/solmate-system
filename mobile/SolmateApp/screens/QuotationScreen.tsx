import React, { useState } from 'react';
import {
  Alert,
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
import Icon from 'react-native-vector-icons/MaterialCommunityIcons';

import { ApiError } from '../src/services/api';
import CustomerBottomNav from '../src/components/CustomerBottomNav';
import { createQuotation } from '../src/services/quotationApi';

/* ── constants ───────────────────────────────────── */

const NAVY = '#1A2B55';
const GOLD = '#F5C000';
const MUTED = '#6B7A99';
const BG = '#C8D8F0';
const CARD = '#ffffff';
const BORDER = '#D4E0F2';
const WARNING = '#7A4F00';

const INSPECTION_FACTORS = [
  'Roof type, size, and usable installation area',
  'Roof orientation and house facing direction',
  'Shading from trees, nearby structures, or other obstructions',
  'Roof condition and structural considerations',
  'Available mounting locations and equipment placement',
  'Electrical panel and wiring conditions',
  'Site accessibility and installation complexity',
  'Local environmental and sunlight conditions',
];

/* ── helpers (unchanged) ───────────────────────────── */

function sanitizeNumericInput(value: string) {
  const cleanedValue = value.replace(/[^0-9.]/g, '');
  const parts = cleanedValue.split('.');
  if (parts.length <= 1) return cleanedValue;
  return `${parts[0]}.${parts.slice(1).join('')}`;
}

function toNumberOrUndefined(value: string) {
  const trimmedValue = value.trim();
  if (!trimmedValue) return undefined;
  const parsedValue = Number(trimmedValue);
  if (Number.isNaN(parsedValue)) return undefined;
  return parsedValue;
}

function formatLaravelErrors(error: ApiError) {
  if (!error.errors) return error.message;
  const messages = Object.values(error.errors).flat();
  if (messages.length === 0) return error.message;
  return messages.join('\n');
}

/* ── screen ─────────────────────────────────────── */

export default function QuotationScreen({ navigation }: any) {
  const [monthlyElectricBill, setMonthlyElectricBill] = useState('');
  const [remarks, setRemarks] = useState('');
  const [billError, setBillError] = useState('');
  const [submitting, setSubmitting] = useState(false);

  const resetForm = () => {
    setMonthlyElectricBill('');
    setRemarks('');
    setBillError('');
  };

  const handleMonthlyBillChange = (value: string) => {
    setMonthlyElectricBill(sanitizeNumericInput(value));
    if (billError) setBillError('');
  };

  const validateForm = () => {
    const parsedBill = toNumberOrUndefined(monthlyElectricBill);
    if (parsedBill === undefined) return 'Monthly electric bill is required.';
    if (parsedBill < 0) return 'Monthly electric bill must be at least 0.';
    return '';
  };

  const handleSubmit = async () => {
    if (submitting) return;

    const validationMessage = validateForm();
    const parsedMonthlyElectricBill = toNumberOrUndefined(monthlyElectricBill);

    if (validationMessage) {
      setBillError(validationMessage);
      Alert.alert('Please check the form', validationMessage);
      return;
    }

    const payload = {
      monthly_electric_bill: parsedMonthlyElectricBill as number,
      remarks: remarks.trim() || undefined,
    };

    try {
      setSubmitting(true);
      console.log('Submitting payload:', payload);
      const response = await createQuotation(payload);
      console.log('Quotation response:', response);
      const createdQuotation = response?.data;

      if (createdQuotation?.id) {
        resetForm();
        navigation.replace('QuotationDetail', {
          quotationId: createdQuotation.id,
          initialQuotation: createdQuotation,
        });
        return;
      }

      Alert.alert(
        'Submission saved',
        'The quotation was created, but the detail screen could not be opened automatically.',
      );
      resetForm();
    } catch (error) {
      console.log('Quotation error:', error);
      if (error instanceof ApiError) {
        Alert.alert('Submit failed', formatLaravelErrors(error));
      } else {
        Alert.alert(
          'Submit failed',
          'Something went wrong while submitting the quotation.',
        );
      }
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <SafeAreaView style={s.safe}>
      <KeyboardAvoidingView
        behavior={Platform.OS === 'ios' ? 'padding' : undefined}
        keyboardVerticalOffset={Platform.OS === 'ios' ? 12 : 0}
        style={s.flex}
      >
        <ScrollView
          contentContainerStyle={s.scroll}
          keyboardDismissMode={
            Platform.OS === 'ios' ? 'interactive' : 'on-drag'
          }
          keyboardShouldPersistTaps="handled"
          showsVerticalScrollIndicator={false}
        >
          {/* ── brand header ── */}
          <Text style={s.brand}>
            Sol<Text style={s.brandAccent}>Mate</Text>
          </Text>

          {/* ── back arrow ── */}
          <Pressable
            hitSlop={14}
            onPress={() => navigation.goBack()}
            style={({ pressed }) => [s.backBtn, pressed && s.pressed]}
          >
            <Text style={s.backIcon}>{'‹'}</Text>
          </Pressable>

          {/* ── title ── */}
          <Text style={s.title}>Pre-Inspection Estimate</Text>
          <Text style={s.subtitle}>Input only: Monthly Electricity Bill</Text>

          <View style={s.noticeCard}>
            <View style={s.noticeHeader}>
              <View style={s.noticeIconWrap}>
                <Icon name="information-outline" size={24} color={NAVY} />
              </View>
              <Text style={s.noticeTitle}>Important Notice</Text>
            </View>

            <Text style={s.noticeText}>
              This pre-inspection quotation is an automated estimate based
              primarily on your submitted electricity consumption and current
              system assumptions. The actual system recommendation, pricing,
              projected savings, and return on investment may change after an
              on-site inspection.
            </Text>

            <Text style={s.noticeIntro}>
              During the inspection, our technician will evaluate additional
              factors including, but not limited to:
            </Text>

            <View style={s.noticeList}>
              {INSPECTION_FACTORS.map(factor => (
                <View key={factor} style={s.noticeListItem}>
                  <View style={s.noticeBullet} />
                  <Text style={s.noticeListText}>{factor}</Text>
                </View>
              ))}
            </View>

            <Text style={s.noticeFinal}>
              The final inspection-based quotation prepared after the
              technician's assessment should be considered the most accurate
              recommendation and pricing proposal.
            </Text>
          </View>

          {/* ── input card ── */}
          <View style={s.card}>
            <Text style={s.inputLabel}>{'Monthly Electricity Bill (₱)'}</Text>

            <View style={[s.inputRow, billError ? s.inputRowError : null]}>
              <TextInput
                value={monthlyElectricBill}
                onChangeText={handleMonthlyBillChange}
                placeholder="e.g., 2,500"
                placeholderTextColor="#a0aec0"
                keyboardType="decimal-pad"
                style={s.input}
              />
              <View style={s.pesoBadge}>
                <Text style={s.pesoText}>{'₱'}</Text>
              </View>
            </View>

            {billError ? (
              <Text style={s.errorText}>{billError}</Text>
            ) : (
              <Text style={s.helpText}>
                Enter amount from your latest bill.
              </Text>
            )}
          </View>

          {/* ── buttons ── */}
          <Pressable
            disabled={submitting}
            onPress={handleSubmit}
            style={({ pressed }) => [
              s.primaryBtn,
              submitting && s.btnDisabled,
              pressed && !submitting && s.pressed,
            ]}
          >
            <Text style={s.primaryBtnText}>
              {submitting
                ? 'Generating...'
                : 'Generate Pre-Inspection Estimate & ROI'}
            </Text>
          </Pressable>

          <Pressable
            disabled={submitting}
            onPress={resetForm}
            style={({ pressed }) => [s.secondaryBtn, pressed && s.pressed]}
          >
            <Text style={s.secondaryBtnText}>Clear</Text>
          </Pressable>

          {/* ── spacer before bottom area ── */}
          <View style={s.spacer} />

          {/* ── chatbot shortcut ── */}
          <Pressable
            onPress={() => navigation.navigate('Chatbot')}
            style={({ pressed }) => [s.chatRow, pressed && s.pressed]}
          >
            <Text style={s.chatText}>Chat with SolBot</Text>
            <View style={s.chatBtn}>
              <Text style={s.chatBtnIcon}>{'🤖'}</Text>
            </View>
          </Pressable>

          {/* ── bottom nav ── */}
        </ScrollView>

        <CustomerBottomNav activeTab="Quotations" />
      </KeyboardAvoidingView>
    </SafeAreaView>
  );
}

/* ── styles ──────────────────────────────────────── */

const s = StyleSheet.create({
  safe: { flex: 1, backgroundColor: BG },
  flex: { flex: 1 },
  scroll: { paddingHorizontal: 22, paddingTop: 20, paddingBottom: 90 },
  pressed: { opacity: 0.85 },

  /* brand */
  brand: { fontSize: 22, fontWeight: '800', color: NAVY, marginBottom: 10 },
  brandAccent: { color: GOLD },

  /* back */
  backBtn: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: CARD,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 18,
    shadowColor: '#8a9bbd',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 6,
    elevation: 3,
  },
  backIcon: { fontSize: 28, color: NAVY, fontWeight: '600', marginTop: -2 },

  /* title */
  title: {
    fontSize: 26,
    fontWeight: '900',
    color: NAVY,
    marginBottom: 4,
  },
  subtitle: {
    fontSize: 14,
    color: MUTED,
    marginBottom: 22,
  },

  /* notice card */
  noticeCard: {
    backgroundColor: '#fffdf0',
    borderRadius: 22,
    borderWidth: 1,
    borderColor: '#F3D76B',
    padding: 18,
    marginBottom: 22,
    shadowColor: '#8a9bbd',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.1,
    shadowRadius: 14,
    elevation: 4,
  },
  noticeHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 12,
  },
  noticeIconWrap: {
    width: 42,
    height: 42,
    borderRadius: 14,
    backgroundColor: 'rgba(245, 192, 0, 0.18)',
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: 12,
  },
  noticeTitle: {
    flex: 1,
    fontSize: 17,
    fontWeight: '900',
    color: NAVY,
  },
  noticeText: {
    fontSize: 13,
    color: MUTED,
    lineHeight: 20,
  },
  noticeIntro: {
    fontSize: 13,
    color: NAVY,
    fontWeight: '800',
    lineHeight: 19,
    marginTop: 14,
    marginBottom: 10,
  },
  noticeList: {
    marginBottom: 12,
  },
  noticeListItem: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    marginBottom: 8,
  },
  noticeBullet: {
    width: 6,
    height: 6,
    borderRadius: 3,
    backgroundColor: GOLD,
    marginTop: 7,
    marginRight: 10,
  },
  noticeListText: {
    flex: 1,
    fontSize: 12,
    lineHeight: 18,
    color: MUTED,
  },
  noticeFinal: {
    fontSize: 13,
    color: WARNING,
    lineHeight: 20,
    fontWeight: '800',
  },

  /* input card */
  card: {
    backgroundColor: CARD,
    borderRadius: 22,
    padding: 22,
    marginBottom: 22,
    shadowColor: '#8a9bbd',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.1,
    shadowRadius: 14,
    elevation: 4,
  },
  inputLabel: {
    fontSize: 15,
    fontWeight: '800',
    color: NAVY,
    marginBottom: 14,
  },
  inputRow: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#f4f7fb',
    borderRadius: 28,
    borderWidth: 1,
    borderColor: BORDER,
    paddingHorizontal: 16,
    paddingVertical: 4,
  },
  inputRowError: {
    borderColor: '#ef4444',
  },
  input: {
    flex: 1,
    fontSize: 15,
    color: NAVY,
    paddingVertical: 12,
  },
  pesoBadge: {
    width: 32,
    height: 32,
    borderRadius: 16,
    borderWidth: 1.5,
    borderColor: NAVY,
    alignItems: 'center',
    justifyContent: 'center',
    marginLeft: 8,
  },
  pesoText: {
    fontSize: 14,
    fontWeight: '800',
    color: NAVY,
  },
  helpText: {
    fontSize: 12,
    color: MUTED,
    marginTop: 10,
    lineHeight: 17,
  },
  errorText: {
    fontSize: 12,
    color: '#dc2626',
    marginTop: 10,
    lineHeight: 17,
  },

  /* primary button */
  primaryBtn: {
    backgroundColor: GOLD,
    borderRadius: 28,
    paddingVertical: 16,
    alignItems: 'center',
    marginBottom: 12,
    shadowColor: GOLD,
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.25,
    shadowRadius: 10,
    elevation: 4,
  },
  primaryBtnText: {
    fontSize: 16,
    fontWeight: '900',
    color: CARD,
    letterSpacing: 0.3,
  },
  btnDisabled: { opacity: 0.55 },

  /* secondary button */
  secondaryBtn: {
    backgroundColor: CARD,
    borderRadius: 28,
    paddingVertical: 16,
    alignItems: 'center',
    marginBottom: 10,
    borderWidth: 1,
    borderColor: BORDER,
  },
  secondaryBtnText: {
    fontSize: 16,
    fontWeight: '800',
    color: NAVY,
  },

  /* spacer */
  spacer: { flex: 1, minHeight: 60 },

  /* chat shortcut */
  chatRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'flex-end',
    marginBottom: 22,
    marginTop: 4,
  },
  chatText: { fontSize: 13, color: MUTED, marginRight: 10 },
  chatBtn: {
    width: 48,
    height: 48,
    borderRadius: 24,
    backgroundColor: NAVY,
    alignItems: 'center',
    justifyContent: 'center',
    shadowColor: NAVY,
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.25,
    shadowRadius: 8,
    elevation: 5,
  },
  chatBtnIcon: { fontSize: 22 },

});
