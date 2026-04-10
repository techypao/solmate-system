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

import {AppButton, AppCard} from '../components';
import StatusBadge from '../components/StatusBadge';
import {ApiError} from '../src/services/api';
import {
  getAssignedServiceRequestById,
  TechnicianServiceRequest,
  updateTechnicianServiceRequestStatus,
} from '../src/services/technicianApi';
import {
  canCompleteServiceRequest,
  canCreateFinalQuotation,
  canStartServiceRequest,
  formatDate,
  formatDateTime,
  getCustomerEmail,
  getCustomerName,
  getTechnicianEmail,
  getTechnicianName,
} from '../src/utils/technicianRequests';

function getFriendlyErrorMessage(error: unknown) {
  if (error instanceof ApiError) {
    return error.message;
  }

  return 'Could not load the service request details.';
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

export default function RequestDetailsScreen({navigation, route}: any) {
  const requestId = route?.params?.requestId;
  const initialRequest = route?.params?.initialRequest as
    | TechnicianServiceRequest
    | undefined;

  const [serviceRequest, setServiceRequest] = useState<
    TechnicianServiceRequest | null
  >(initialRequest || null);
  const [loading, setLoading] = useState(!initialRequest);
  const [errorMessage, setErrorMessage] = useState('');
  const [actionLoading, setActionLoading] = useState(false);

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

  const handleStatusUpdate = async (
    nextStatus: 'in_progress' | 'completed',
    successMessage: string,
  ) => {
    if (!serviceRequest) {
      return;
    }

    try {
      setActionLoading(true);

      const updatedRequest = await updateTechnicianServiceRequestStatus(
        serviceRequest.id,
        nextStatus,
      );

      setServiceRequest(updatedRequest);
      await loadRequest(false);

      Alert.alert('Success', successMessage);
    } catch (error) {
      if (error instanceof ApiError) {
        Alert.alert('Update failed', error.message);
      } else {
        Alert.alert(
          'Update failed',
          'Could not update the service request status.',
        );
      }
    } finally {
      setActionLoading(false);
    }
  };

  if (loading) {
    return (
      <View style={styles.centeredContainer}>
        <ActivityIndicator size="large" color="#2563eb" />
        <Text style={styles.loadingText}>Loading request details...</Text>
      </View>
    );
  }

  if (errorMessage || !serviceRequest) {
    return (
      <View style={styles.centeredContainer}>
        <Text style={styles.errorTitle}>Request details unavailable</Text>
        <Text style={styles.errorText}>
          {errorMessage || 'No request details were found.'}
        </Text>
        <AppButton
          title="Back to tasks"
          onPress={() => navigation.goBack()}
          style={styles.errorButton}
        />
      </View>
    );
  }

  return (
    <SafeAreaView style={styles.safeArea}>
      <ScrollView
        contentContainerStyle={styles.contentContainer}
        showsVerticalScrollIndicator={false}>
        <View style={styles.heroCard}>
          <Text style={styles.eyebrow}>Service request #{serviceRequest.id}</Text>
          <Text style={styles.heroTitle}>{serviceRequest.request_type}</Text>
          <Text style={styles.heroSubtitle}>
            Review customer information, update the task status, and create a
            final quotation after the request is completed.
          </Text>

          <StatusBadge status={serviceRequest.status} />
        </View>

        <AppCard style={styles.sectionCard}>
          <Text style={styles.sectionTitle}>Customer information</Text>
          <Text style={styles.sectionSubtitle}>
            The customer assigned to this service request.
          </Text>

          <DetailRow label="Customer name" value={getCustomerName(serviceRequest)} />
          <DetailRow
            label="Customer email"
            value={getCustomerEmail(serviceRequest)}
          />
        </AppCard>

        <AppCard style={styles.sectionCard}>
          <Text style={styles.sectionTitle}>Request details</Text>
          <Text style={styles.sectionSubtitle}>
            Core service request information from the assigned job.
          </Text>

          <DetailRow
            label="Request type"
            value={serviceRequest.request_type}
          />
          <DetailRow
            label="Date needed"
            value={formatDate(serviceRequest.date_needed)}
          />
          <DetailRow
            label="Created"
            value={formatDateTime(serviceRequest.created_at)}
          />

          <View style={styles.detailsBlock}>
            <Text style={styles.detailLabel}>Details</Text>
            <Text style={styles.detailsText}>{serviceRequest.details}</Text>
          </View>
        </AppCard>

        <AppCard style={styles.sectionCard}>
          <Text style={styles.sectionTitle}>Technician assignment</Text>
          <Text style={styles.sectionSubtitle}>
            This request is tied to your technician account.
          </Text>

          <DetailRow
            label="Technician name"
            value={getTechnicianName(serviceRequest)}
          />
          <DetailRow
            label="Technician email"
            value={getTechnicianEmail(serviceRequest)}
          />
        </AppCard>

        <AppCard style={styles.sectionCard}>
          <Text style={styles.sectionTitle}>Task actions</Text>
          <Text style={styles.sectionSubtitle}>
            Actions change automatically based on the current service status.
          </Text>

          {canStartServiceRequest(serviceRequest.status) ? (
            <AppButton
              title={actionLoading ? 'Starting task...' : 'Start Task'}
              disabled={actionLoading}
              onPress={() =>
                handleStatusUpdate(
                  'in_progress',
                  'The service request is now in progress.',
                )
              }
            />
          ) : null}

          {canCompleteServiceRequest(serviceRequest.status) ? (
            <AppButton
              title={actionLoading ? 'Completing task...' : 'Complete Task'}
              disabled={actionLoading}
              onPress={() =>
                handleStatusUpdate(
                  'completed',
                  'The service request has been marked as completed.',
                )
              }
            />
          ) : null}

          {canCreateFinalQuotation(serviceRequest.status) ? (
            <View style={styles.completedStateCard}>
              <Text style={styles.completedStateTitle}>Task completed</Text>
              <Text style={styles.completedStateText}>
                This request is ready for final quotation submission.
              </Text>
            </View>
          ) : null}
        </AppCard>

        <AppCard style={styles.sectionCard}>
          <Text style={styles.sectionTitle}>Final quotation</Text>
          <Text style={styles.sectionSubtitle}>
            Final quotations can only be created for completed service requests.
          </Text>

          <AppButton
            title="Create Final Quotation"
            disabled={!canCreateFinalQuotation(serviceRequest.status)}
            onPress={() =>
              navigation.navigate('FinalQuotation', {
                requestId: serviceRequest.id,
                serviceRequest,
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
  errorButton: {
    marginTop: 16,
    width: '100%',
  },
  heroCard: {
    backgroundColor: '#dbeafe',
    borderRadius: 28,
    marginBottom: 18,
    padding: 22,
  },
  eyebrow: {
    color: '#1d4ed8',
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
    marginBottom: 18,
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
    backgroundColor: '#f8fafc',
    borderColor: '#e2e8f0',
    borderRadius: 16,
    borderWidth: 1,
    marginTop: 4,
    padding: 14,
  },
  detailsText: {
    color: '#334155',
    fontSize: 14,
    lineHeight: 21,
  },
  completedStateCard: {
    backgroundColor: '#f0fdf4',
    borderRadius: 16,
    marginTop: 2,
    padding: 16,
  },
  completedStateTitle: {
    color: '#166534',
    fontSize: 16,
    fontWeight: '700',
    marginBottom: 6,
  },
  completedStateText: {
    color: '#166534',
    fontSize: 14,
    lineHeight: 20,
  },
});
