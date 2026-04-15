import React, {useState} from 'react';
import {
  Alert,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  View,
} from 'react-native';

import {AppButton, AppCard} from '../components';
import {ApiError} from '../src/services/api';
import {createQuotation} from '../src/services/quotationApi';

function sanitizeNumericInput(value: string) {
  const cleanedValue = value.replace(/[^0-9.]/g, '');
  const parts = cleanedValue.split('.');

  if (parts.length <= 1) {
    return cleanedValue;
  }

  return `${parts[0]}.${parts.slice(1).join('')}`;
}

function toNumberOrUndefined(value: string) {
  const trimmedValue = value.trim();

  if (!trimmedValue) {
    return undefined;
  }

  const parsedValue = Number(trimmedValue);

  if (Number.isNaN(parsedValue)) {
    return undefined;
  }

  return parsedValue;
}

function formatLaravelErrors(error: ApiError) {
  if (!error.errors) {
    return error.message;
  }

  const messages = Object.values(error.errors).flat();

  if (messages.length === 0) {
    return error.message;
  }

  return messages.join('\n');
}

export default function QuotationScreen({navigation}: any) {
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

    if (billError) {
      setBillError('');
    }
  };

  const validateForm = () => {
    const parsedBill = toNumberOrUndefined(monthlyElectricBill);

    if (parsedBill === undefined) {
      return 'Monthly electric bill is required.';
    }

    if (parsedBill < 0) {
      return 'Monthly electric bill must be at least 0.';
    }

    return '';
  };

  const handleSubmit = async () => {
    if (submitting) {
      return;
    }

    const validationMessage = validateForm();
    const parsedMonthlyElectricBill = toNumberOrUndefined(monthlyElectricBill);

    if (validationMessage) {
      setBillError(validationMessage);
      Alert.alert('Please check the form', validationMessage);
      return;
    }

    // Customers only fill in the bill and remarks.
    // The app automatically sends the default backend values below.
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
    <ScrollView
      contentContainerStyle={styles.container}
      keyboardShouldPersistTaps="handled"
      showsVerticalScrollIndicator={false}>
      <View style={styles.heroCard}>
        <Text style={styles.eyebrow}>Create initial quotation</Text>
        <Text style={styles.title}>Quick customer quotation</Text>
        <Text style={styles.subtitle}>
          Enter your monthly electric bill and any optional notes. Customers can
          only create the initial quotation here. The technician handles the
          final quotation later in the workflow.
        </Text>
      </View>

      <AppCard style={styles.sectionCard}>
        <Text style={styles.sectionTitle}>Customer details</Text>
        <Text style={styles.sectionSubtitle}>
          Only the customer-facing inputs are shown here. Technical settings are
          handled automatically in the background.
        </Text>

        {billError ? (
          <View style={styles.errorBanner}>
            <Text style={styles.errorBannerTitle}>Please fix the form</Text>
            <Text style={styles.errorBannerText}>{billError}</Text>
          </View>
        ) : null}

        <View style={styles.fieldGroup}>
          <View style={styles.fieldHeader}>
            <Text style={styles.fieldLabel}>Monthly electric bill</Text>
            <Text style={styles.requiredText}>Required</Text>
          </View>

          <TextInput
            value={monthlyElectricBill}
            onChangeText={handleMonthlyBillChange}
            placeholder="Example: 3500"
            placeholderTextColor="#94a3b8"
            keyboardType="decimal-pad"
            style={[styles.input, billError ? styles.inputError : null]}
          />

          {billError ? <Text style={styles.errorText}>{billError}</Text> : null}
          {!billError ? (
            <Text style={styles.helpText}>
              This value is used to generate your initial quotation estimate.
            </Text>
          ) : null}
        </View>

        <View style={styles.fieldGroup}>
          <View style={styles.fieldHeader}>
            <Text style={styles.fieldLabel}>Remarks</Text>
            <Text style={styles.optionalText}>Optional</Text>
          </View>

          <TextInput
            value={remarks}
            onChangeText={setRemarks}
            placeholder="Add any extra notes or preferences"
            placeholderTextColor="#94a3b8"
            multiline={true}
            numberOfLines={4}
            style={[styles.input, styles.textArea]}
          />

          <Text style={styles.helpText}>
            You can mention preferences, roof conditions, or anything else that
            may help during review.
          </Text>
        </View>
      </AppCard>

      <View style={styles.infoCard}>
        <Text style={styles.infoTitle}>Auto-filled by the app</Text>
        <Text style={styles.infoText}>quotation_type: initial</Text>
        <Text style={styles.infoText}>other technical values: handled by backend</Text>
      </View>

      <View style={styles.submitCard}>
        <Text style={styles.submitTitle}>Ready to create your quotation?</Text>
        <Text style={styles.submitSubtitle}>
          Your request will be saved as an initial hybrid quotation for technician review.
        </Text>
        <AppButton
          title={submitting ? 'Submitting quotation...' : 'Create quotation'}
          onPress={handleSubmit}
          disabled={submitting}
          style={styles.submitButton}
          textStyle={styles.submitButtonText}
        />
      </View>
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: {
    backgroundColor: '#f5f7fb',
    padding: 20,
    paddingBottom: 32,
  },
  heroCard: {
    backgroundColor: '#dbeafe',
    borderRadius: 24,
    marginBottom: 18,
    padding: 22,
  },
  eyebrow: {
    color: '#1d4ed8',
    fontSize: 12,
    fontWeight: '700',
    letterSpacing: 0.6,
    marginBottom: 8,
    textTransform: 'uppercase',
  },
  title: {
    color: '#0f172a',
    fontSize: 28,
    fontWeight: '800',
    marginBottom: 10,
  },
  subtitle: {
    color: '#334155',
    fontSize: 15,
    lineHeight: 22,
  },
  sectionCard: {
    marginBottom: 16,
  },
  errorBanner: {
    backgroundColor: '#fef2f2',
    borderColor: '#fecaca',
    borderRadius: 16,
    borderWidth: 1,
    marginBottom: 16,
    padding: 14,
  },
  errorBannerTitle: {
    color: '#b91c1c',
    fontSize: 14,
    fontWeight: '700',
    marginBottom: 4,
  },
  errorBannerText: {
    color: '#991b1b',
    fontSize: 13,
    lineHeight: 18,
  },
  sectionTitle: {
    color: '#0f172a',
    fontSize: 22,
    fontWeight: '700',
    marginBottom: 6,
  },
  sectionSubtitle: {
    color: '#64748b',
    fontSize: 14,
    lineHeight: 20,
    marginBottom: 18,
  },
  fieldGroup: {
    marginBottom: 18,
  },
  fieldHeader: {
    alignItems: 'center',
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 8,
  },
  fieldLabel: {
    color: '#0f172a',
    fontSize: 16,
    fontWeight: '700',
  },
  requiredText: {
    color: '#b91c1c',
    fontSize: 12,
    fontWeight: '700',
    textTransform: 'uppercase',
  },
  optionalText: {
    color: '#64748b',
    fontSize: 12,
    fontWeight: '700',
    textTransform: 'uppercase',
  },
  input: {
    backgroundColor: '#f8fafc',
    borderColor: '#cbd5e1',
    borderRadius: 16,
    borderWidth: 1,
    color: '#0f172a',
    fontSize: 16,
    paddingHorizontal: 16,
    paddingVertical: 15,
  },
  inputError: {
    borderColor: '#ef4444',
  },
  textArea: {
    minHeight: 110,
    textAlignVertical: 'top',
  },
  helpText: {
    color: '#64748b',
    fontSize: 13,
    lineHeight: 18,
    marginTop: 6,
  },
  errorText: {
    color: '#dc2626',
    fontSize: 13,
    lineHeight: 18,
    marginTop: 6,
  },
  infoCard: {
    backgroundColor: '#eff6ff',
    borderRadius: 18,
    marginBottom: 14,
    padding: 16,
  },
  infoTitle: {
    color: '#1d4ed8',
    fontSize: 15,
    fontWeight: '700',
    marginBottom: 6,
  },
  infoText: {
    color: '#334155',
    fontSize: 14,
    lineHeight: 20,
  },
  submitCard: {
    backgroundColor: '#ffffff',
    borderColor: '#dbe4f0',
    borderRadius: 22,
    borderWidth: 1,
    padding: 18,
    shadowColor: '#0f172a',
    shadowOffset: {
      width: 0,
      height: 10,
    },
    shadowOpacity: 0.08,
    shadowRadius: 18,
    elevation: 3,
  },
  submitTitle: {
    color: '#0f172a',
    fontSize: 18,
    fontWeight: '800',
    marginBottom: 6,
  },
  submitSubtitle: {
    color: '#64748b',
    fontSize: 14,
    lineHeight: 20,
    marginBottom: 14,
  },
  submitButton: {
    minHeight: 56,
  },
  submitButtonText: {
    fontSize: 17,
  },
});
