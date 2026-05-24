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

import { ApiError } from '../src/services/api';
import CustomerBottomNav from '../src/components/CustomerBottomNav';
import { createQuotation } from '../src/services/quotationApi';

/* ── constants ───────────────────────────────────── */

const NAVY = '#123A5A';
const GOLD = '#F4D000';
const MUTED = '#5E7288';
const BG = '#F8FAFC';
const CARD = '#ffffff';

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

          <Text style={s.footerHint}>
            The pre-inspection estimate is only a guide and may change after the technician's actual inspection.
          </Text>

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
          <CustomerBottomNav activeTab="Quotation" />
        </ScrollView>
      </KeyboardAvoidingView>
    </SafeAreaView>
  );
}

/* ── styles ──────────────────────────────────────── */

const s = StyleSheet.create({
  safe: { flex: 1, backgroundColor: BG },
  flex: { flex: 1 },
  scroll: { paddingHorizontal: 22, paddingTop: 20, paddingBottom: 30 },
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
    borderColor: '#dfe6f0',
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
    borderColor: '#dfe6f0',
  },
  secondaryBtnText: {
    fontSize: 16,
    fontWeight: '800',
    color: NAVY,
  },

  /* footer hint */
  footerHint: {
    fontSize: 12,
    color: MUTED,
    fontStyle: 'italic',
    marginBottom: 8,
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
