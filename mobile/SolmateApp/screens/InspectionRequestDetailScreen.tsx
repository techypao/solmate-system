import React, {useCallback, useState} from 'react';
import {
  ActivityIndicator,
  SafeAreaView,
  ScrollView,
  StyleSheet,
  Text,
  View,
} from 'react-native';
import {useFocusEffect} from '@react-navigation/native';

import {AppButton, AppCard, StatusBadge} from '../components';
import {ApiError} from '../src/services/api';
import {
  getInspectionRequestById,
  InspectionRequest,
} from '../src/services/inspectionRequestApi';

function formatDate(value?: string | null, fallback = 'Flexible') {
  if (!value) {
    return fallback;
  }

  const parsedDate = new Date(value);

  if (Number.isNaN(parsedDate.getTime())) {
    return value;
  }

  return parsedDate.toLocaleDateString();
}

function formatDateTime(value?: string | null, fallback = 'Not available') {
  if (!value) {
    return fallback;
  }

  const parsedDate = new Date(value);

  if (Number.isNaN(parsedDate.getTime())) {
    return value;
  }

  return parsedDate.toLocaleString();
}

function getFriendlyErrorMessage(error: unknown) {
  if (error instanceof ApiError) {
    return error.message;
  }

  return 'Could not load the inspection request details.';
}

function DetailRow({
  label,
  value,
}: {
  label: string;
  value?: string | null;
}) {
  return (
    <View style={styles.detailRow}>
      <Text style={styles.detailLabel}>{label}</Text>
      <Text style={styles.detailValue}>{value || 'Not available'}</Text>
    </View>
  );
}

export default function InspectionRequestDetailScreen({navigation, route}: any) {
  const inspectionRequestId = route?.params?.inspectionRequestId;
  const initialInspectionRequest = route?.params?.initialInspectionRequest as
    | InspectionRequest
    | undefined;

  const [inspectionRequest, setInspectionRequest] = useState<InspectionRequest | null>(
    initialInspectionRequest || null,
  );
  const [loading, setLoading] = useState(!initialInspectionRequest);
  const [errorMessage, setErrorMessage] = useState('');

  const loadInspectionRequest = useCallback(
    async (showLoadingState = false) => {
      if (!inspectionRequestId) {
        setInspectionRequest(null);
        setErrorMessage('No inspection request ID was provided.');
        setLoading(false);
        return;
      }

      try {
        if (showLoadingState) {
          setLoading(true);
        }

        setErrorMessage('');
        const request = await getInspectionRequestById(inspectionRequestId);

        if (!request) {
          setInspectionRequest(null);
          setErrorMessage('This inspection request could not be found.');
          return;
        }

        setInspectionRequest(request);
      } catch (error) {
        setInspectionRequest(null);
        setErrorMessage(getFriendlyErrorMessage(error));
      } finally {
        setLoading(false);
      }
    },
    [inspectionRequestId],
  );

  useFocusEffect(
    useCallback(() => {
      loadInspectionRequest(!inspectionRequest);
    }, [inspectionRequest, loadInspectionRequest]),
  );

  if (loading) {
    return (
      <View style={styles.centeredContainer}>
        <ActivityIndicator size="large" color="#16a34a" />
        <Text style={styles.loadingText}>Loading inspection request...</Text>
      </View>
    );
  }

  if (errorMessage || !inspectionRequest) {
    return (
      <View style={styles.centeredContainer}>
        <Text style={styles.errorTitle}>Inspection request unavailable</Text>
        <Text style={styles.errorText}>
          {errorMessage || 'No inspection request details were found.'}
        </Text>
        <AppButton
          title="Try again"
          onPress={() => loadInspectionRequest(true)}
          style={styles.actionButton}
        />
        <AppButton
          title="Back to requests"
          variant="outline"
          onPress={() => navigation.navigate('InspectionRequestList')}
          style={styles.secondaryButton}
        />
      </View>
    );
  }

  const canOpenFinalQuotation = inspectionRequest.status === 'completed';

  return (
    <SafeAreaView style={styles.safeArea}>
      <ScrollView
        contentContainerStyle={styles.contentContainer}
        showsVerticalScrollIndicator={false}>
        <View style={styles.heroCard}>
          <Text style={styles.eyebrow}>Inspection request #{inspectionRequest.id}</Text>
          <Text style={styles.heroTitle}>Site visit request</Text>
          <Text style={styles.heroSubtitle}>
            Review the request details, current progress, and technician
            assignment for this inspection.
          </Text>

          <StatusBadge status={inspectionRequest.status} />
        </View>

        <AppCard style={styles.sectionCard}>
          <Text style={styles.sectionTitle}>Request details</Text>
          <Text style={styles.sectionSubtitle}>
            These are the details submitted from your customer account.
          </Text>

          <DetailRow label="Inspection request ID" value={`${inspectionRequest.id}`} />
          <DetailRow
            label="Contact number"
            value={inspectionRequest.contact_number || 'Not provided'}
          />
          <DetailRow
            label="Preferred date"
            value={formatDate(inspectionRequest.date_needed)}
          />
          <DetailRow
            label="Submitted"
            value={formatDateTime(inspectionRequest.created_at)}
          />

          <View style={styles.detailsBlock}>
            <Text style={styles.detailLabel}>Details</Text>
            <Text style={styles.detailsText}>{inspectionRequest.details}</Text>
          </View>
        </AppCard>

        <AppCard style={styles.sectionCard}>
          <Text style={styles.sectionTitle}>Assignment</Text>
          <Text style={styles.sectionSubtitle}>
            Technician assignment appears here once your request is picked up.
          </Text>

          <DetailRow
            label="Technician"
            value={inspectionRequest.technician?.name || 'Pending assignment'}
          />
          <DetailRow
            label="Technician email"
            value={inspectionRequest.technician?.email || 'Not available yet'}
          />
        </AppCard>

        <AppCard style={styles.sectionCard}>
          <Text style={styles.sectionTitle}>Final quotation</Text>
          <Text style={styles.sectionSubtitle}>
            The final quotation becomes available after the inspection is marked
            as completed by the assigned technician.
          </Text>

          <AppButton
            title="View Final Quotation"
            disabled={!canOpenFinalQuotation}
            onPress={() =>
              navigation.navigate('FinalQuotationView', {
                inspectionRequestId: inspectionRequest.id,
              })
            }
          />
        </AppCard>
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
  actionButton: {
    marginTop: 16,
    width: '100%',
  },
  secondaryButton: {
    marginTop: 12,
    width: '100%',
  },
  heroCard: {
    backgroundColor: '#dcfce7',
    borderRadius: 28,
    marginBottom: 18,
    padding: 22,
  },
  eyebrow: {
    color: '#166534',
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
    marginBottom: 10,
  },
  heroSubtitle: {
    color: '#334155',
    fontSize: 14,
    lineHeight: 21,
    marginBottom: 16,
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
  detailsBlock: {
    borderTopColor: '#e2e8f0',
    borderTopWidth: 1,
    paddingTop: 12,
  },
  detailsText: {
    color: '#0f172a',
    fontSize: 15,
    lineHeight: 23,
  },
});
