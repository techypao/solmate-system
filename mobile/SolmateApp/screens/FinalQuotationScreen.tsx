import React, {useCallback, useState} from 'react';
import {
  ActivityIndicator,
  Alert,
  SafeAreaView,
  ScrollView,
  StyleSheet,
  Text,
  View,
} from 'react-native';
import {useFocusEffect} from '@react-navigation/native';

import {AppButton, AppCard, AppInput} from '../components';
import {ApiError} from '../src/services/api';
import {
  createFinalQuotation,
  getAssignedServiceRequestById,
  TechnicianServiceRequest,
} from '../src/services/technicianApi';
import {
  canCreateFinalQuotation,
  formatDate,
  formatServiceRequestStatus,
  getCustomerName,
} from '../src/utils/technicianRequests';

function getFriendlyErrorMessage(error: unknown) {
  if (error instanceof ApiError) {
    return error.message;
  }

  return 'Could not load the request needed for final quotation submission.';
}

export default function FinalQuotationScreen({navigation, route}: any) {
  const requestId = route?.params?.requestId;
  const initialRequest = route?.params?.serviceRequest as
    | TechnicianServiceRequest
    | undefined;

  const [serviceRequest, setServiceRequest] = useState<
    TechnicianServiceRequest | null
  >(initialRequest || null);
  const [loading, setLoading] = useState(!initialRequest);
  const [submitting, setSubmitting] = useState(false);
  const [errorMessage, setErrorMessage] = useState('');
  const [preferredSystem, setPreferredSystem] = useState('');
  const [remarks, setRemarks] = useState('');

  const loadRequest = useCallback(async (showLoadingState = false) => {
    if (!requestId) {
      setServiceRequest(null);
      setErrorMessage('No service request ID was provided.');
      setLoading(false);
      return;
    }

    try {
      if (showLoadingState) {
        setLoading(true);
      }

      setErrorMessage('');
      const request = await getAssignedServiceRequestById(requestId);

      if (!request) {
        setServiceRequest(null);
        setErrorMessage(
          'This service request was not found in your assigned task list.',
        );
        return;
      }

      setServiceRequest(request);
    } catch (error) {
      setErrorMessage(getFriendlyErrorMessage(error));
    } finally {
      setLoading(false);
    }
  }, [requestId]);

  useFocusEffect(
    useCallback(() => {
      loadRequest(!serviceRequest);
    }, [loadRequest, serviceRequest]),
  );

  const handleSubmit = async () => {
    const trimmedPreferredSystem = preferredSystem.trim();
    const trimmedRemarks = remarks.trim();

    if (!serviceRequest) {
      Alert.alert('Request unavailable', 'No completed request was found.');
      return;
    }

    if (!canCreateFinalQuotation(serviceRequest.status)) {
      Alert.alert(
        'Not allowed yet',
        'Final quotations can only be created after the request is completed.',
      );
      return;
    }

    if (!trimmedPreferredSystem) {
      Alert.alert(
        'Preferred system required',
        'Please enter the recommended preferred system.',
      );
      return;
    }

    try {
      setSubmitting(true);

      await createFinalQuotation({
        service_request_id: serviceRequest.id,
        preferred_system: trimmedPreferredSystem,
        remarks: trimmedRemarks || undefined,
      });

      Alert.alert('Success', 'Final quotation submitted successfully.', [
        {
          text: 'OK',
          onPress: () => navigation.goBack(),
        },
      ]);
    } catch (error) {
      if (error instanceof ApiError) {
        Alert.alert('Submission failed', error.message);
      } else {
        Alert.alert(
          'Submission failed',
          'Could not submit the final quotation.',
        );
      }
    } finally {
      setSubmitting(false);
    }
  };

  if (loading) {
    return (
      <View style={styles.centeredContainer}>
        <ActivityIndicator size="large" color="#2563eb" />
        <Text style={styles.loadingText}>Loading completed request...</Text>
      </View>
    );
  }

  if (errorMessage || !serviceRequest) {
    return (
      <View style={styles.centeredContainer}>
        <Text style={styles.errorTitle}>Final quotation unavailable</Text>
        <Text style={styles.errorText}>
          {errorMessage || 'No service request was found for this form.'}
        </Text>
        <AppButton
          title="Back"
          onPress={() => navigation.goBack()}
          style={styles.errorButton}
        />
      </View>
    );
  }

  const completed = canCreateFinalQuotation(serviceRequest.status);

  return (
    <SafeAreaView style={styles.safeArea}>
      <ScrollView
        contentContainerStyle={styles.contentContainer}
        keyboardShouldPersistTaps="handled"
        showsVerticalScrollIndicator={false}>
        <View style={styles.heroCard}>
          <Text style={styles.eyebrow}>Final quotation</Text>
          <Text style={styles.heroTitle}>
            Service request #{serviceRequest.id}
          </Text>
          <Text style={styles.heroSubtitle}>
            Submit the technician&apos;s recommended system after completing the
            service request.
          </Text>
        </View>

        <AppCard style={styles.sectionCard}>
          <Text style={styles.sectionTitle}>Request summary</Text>
          <Text style={styles.sectionSubtitle}>
            Review the completed request before submitting the final quotation.
          </Text>

          <View style={styles.detailRow}>
            <Text style={styles.detailLabel}>Customer</Text>
            <Text style={styles.detailValue}>{getCustomerName(serviceRequest)}</Text>
          </View>
          <View style={styles.detailRow}>
            <Text style={styles.detailLabel}>Request type</Text>
            <Text style={styles.detailValue}>{serviceRequest.request_type}</Text>
          </View>
          <View style={styles.detailRow}>
            <Text style={styles.detailLabel}>Date needed</Text>
            <Text style={styles.detailValue}>
              {formatDate(serviceRequest.date_needed)}
            </Text>
          </View>
          <View style={styles.detailRow}>
            <Text style={styles.detailLabel}>Current status</Text>
            <Text style={styles.detailValue}>
              {formatServiceRequestStatus(serviceRequest.status)}
            </Text>
          </View>
        </AppCard>

        {!completed ? (
          <AppCard style={styles.warningCard}>
            <Text style={styles.warningTitle}>Request not completed yet</Text>
            <Text style={styles.warningText}>
              This form is only available after the service request has been
              marked as completed.
            </Text>
          </AppCard>
        ) : null}

        <AppCard style={styles.sectionCard}>
          <Text style={styles.sectionTitle}>Quotation details</Text>
          <Text style={styles.sectionSubtitle}>
            Capture the recommended system and any final notes for the customer.
          </Text>

          <AppInput
            label="Preferred system"
            value={preferredSystem}
            onChangeText={setPreferredSystem}
            placeholder="Example: Hybrid Solar System"
            containerStyle={styles.fieldSpacing}
          />

          <AppInput
            label="Remarks"
            value={remarks}
            onChangeText={setRemarks}
            placeholder="Recommended based on the inspection result."
            multiline={true}
            numberOfLines={4}
            style={styles.textArea}
          />
        </AppCard>

        <AppButton
          title={
            submitting
              ? 'Submitting final quotation...'
              : 'Submit Final Quotation'
          }
          disabled={submitting || !completed}
          onPress={handleSubmit}
        />
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safeArea: {
    backgroundColor: '#f5f7fb',
    flex: 1,
  },
  contentContainer: {
    padding: 20,
    paddingBottom: 28,
  },
  centeredContainer: {
    alignItems: 'center',
    backgroundColor: '#f5f7fb',
    flex: 1,
    justifyContent: 'center',
    padding: 24,
  },
  loadingText: {
    color: '#475569',
    fontSize: 14,
    marginTop: 12,
  },
  errorTitle: {
    color: '#0f172a',
    fontSize: 22,
    fontWeight: '800',
    marginBottom: 8,
    textAlign: 'center',
  },
  errorText: {
    color: '#b91c1c',
    fontSize: 14,
    lineHeight: 20,
    textAlign: 'center',
  },
  errorButton: {
    marginTop: 16,
    width: '100%',
  },
  heroCard: {
    backgroundColor: '#dcfce7',
    borderRadius: 28,
    marginBottom: 18,
    padding: 22,
  },
  eyebrow: {
    color: '#15803d',
    fontSize: 12,
    fontWeight: '700',
    letterSpacing: 0.4,
    marginBottom: 8,
    textTransform: 'uppercase',
  },
  heroTitle: {
    color: '#0f172a',
    fontSize: 28,
    fontWeight: '800',
    lineHeight: 34,
    marginBottom: 10,
  },
  heroSubtitle: {
    color: '#334155',
    fontSize: 14,
    lineHeight: 21,
  },
  sectionCard: {
    marginBottom: 18,
  },
  sectionTitle: {
    color: '#0f172a',
    fontSize: 20,
    fontWeight: '700',
    marginBottom: 6,
  },
  sectionSubtitle: {
    color: '#64748b',
    fontSize: 14,
    lineHeight: 20,
    marginBottom: 16,
  },
  detailRow: {
    borderTopColor: '#e2e8f0',
    borderTopWidth: 1,
    paddingVertical: 12,
  },
  detailLabel: {
    color: '#64748b',
    fontSize: 12,
    fontWeight: '700',
    marginBottom: 4,
    textTransform: 'uppercase',
  },
  detailValue: {
    color: '#0f172a',
    fontSize: 15,
    fontWeight: '600',
    lineHeight: 22,
  },
  warningCard: {
    backgroundColor: '#fff7ed',
    borderColor: '#fdba74',
    borderWidth: 1,
    marginBottom: 18,
  },
  warningTitle: {
    color: '#9a3412',
    fontSize: 16,
    fontWeight: '700',
    marginBottom: 6,
  },
  warningText: {
    color: '#9a3412',
    fontSize: 14,
    lineHeight: 20,
  },
  fieldSpacing: {
    marginBottom: 16,
  },
  textArea: {
    minHeight: 110,
    paddingTop: 12,
    textAlignVertical: 'top',
  },
});
