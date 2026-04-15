import React, {useCallback, useMemo, useState} from 'react';
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
import {ApiError} from '../src/services/api';
import {
  getServiceRequestById,
  getTechnicianServiceRequestById,
  ServiceRequest,
  TechnicianServiceRequestStatus,
  updateTechnicianServiceRequestStatus,
} from '../src/services/serviceRequestApi';
import {
  formatServiceRequestStatus,
  getServiceRequestStatusColors,
} from '../src/utils/technicianRequests';

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

const TECHNICIAN_STATUS_ACTIONS: Array<{
  label: string;
  value: TechnicianServiceRequestStatus;
  currentStatuses: string[];
  successMessage: string;
}> = [
  {
    label: 'Mark In Progress',
    value: 'in_progress',
    currentStatuses: ['assigned'],
    successMessage: 'The service request is now in progress.',
  },
  {
    label: 'Mark Completed',
    value: 'completed',
    currentStatuses: ['in_progress'],
    successMessage: 'The service request has been completed.',
  },
];

export default function ServiceRequestDetailScreen({navigation, route}: any) {
  const serviceRequestId = route?.params?.serviceRequestId;
  const mode = route?.params?.mode === 'technician' ? 'technician' : 'customer';
  const initialServiceRequest = route?.params?.initialServiceRequest as
    | ServiceRequest
    | undefined;

  const [serviceRequest, setServiceRequest] = useState<ServiceRequest | null>(
    initialServiceRequest || null,
  );
  const [loading, setLoading] = useState(!initialServiceRequest);
  const [errorMessage, setErrorMessage] = useState('');
  const [actionLoading, setActionLoading] = useState(false);

  const loadServiceRequest = useCallback(
    async (showLoadingState = false) => {
      if (!serviceRequestId) {
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
        const request =
          mode === 'technician'
            ? await getTechnicianServiceRequestById(serviceRequestId)
            : await getServiceRequestById(serviceRequestId);

        if (!request) {
          setServiceRequest(null);
          setErrorMessage('This service request could not be found.');
          return;
        }

        setServiceRequest(request);
      } catch (error) {
        setServiceRequest(null);
        setErrorMessage(getFriendlyErrorMessage(error));
      } finally {
        setLoading(false);
      }
    },
    [mode, serviceRequestId],
  );

  useFocusEffect(
    useCallback(() => {
      loadServiceRequest(!serviceRequest);
    }, [loadServiceRequest, serviceRequest]),
  );

  const availableActions = useMemo(() => {
    const currentStatus = (serviceRequest?.status || '').toLowerCase();

    if (mode !== 'technician') {
      return [];
    }

    return TECHNICIAN_STATUS_ACTIONS.filter(action =>
      action.currentStatuses.includes(currentStatus),
    );
  }, [mode, serviceRequest?.status]);

  const handleStatusUpdate = async (
    nextStatus: TechnicianServiceRequestStatus,
    successMessage: string,
  ) => {
    if (!serviceRequest || actionLoading) {
      return;
    }

    try {
      setActionLoading(true);

      const updatedServiceRequest = await updateTechnicianServiceRequestStatus(
        serviceRequest.id,
        nextStatus,
      );

      const nextRequest =
        updatedServiceRequest?.id !== undefined
          ? updatedServiceRequest
          : {...serviceRequest, status: nextStatus};

      setServiceRequest(nextRequest);
      navigation.replace(route.name, {
        serviceRequestId: nextRequest.id,
        initialServiceRequest: nextRequest,
        mode,
      });
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
        <ActivityIndicator size="large" color="#d97706" />
        <Text style={styles.loadingText}>Loading service request...</Text>
      </View>
    );
  }

  if (errorMessage || !serviceRequest) {
    return (
      <View style={styles.centeredContainer}>
        <Text style={styles.errorTitle}>Service request unavailable</Text>
        <Text style={styles.errorText}>
          {errorMessage || 'No service request details were found.'}
        </Text>
        <AppButton
          title="Try again"
          onPress={() => loadServiceRequest(true)}
          style={styles.actionButton}
        />
        <AppButton
          title="Back to requests"
          variant="outline"
          onPress={() =>
            navigation.navigate(
              mode === 'technician'
                ? 'TechnicianServiceRequests'
                : 'ServiceRequestList',
            )
          }
          style={styles.secondaryButton}
        />
      </View>
    );
  }

  const statusColors = getServiceRequestStatusColors(serviceRequest.status);

  return (
    <SafeAreaView style={styles.safeArea}>
      <ScrollView
        contentContainerStyle={styles.contentContainer}
        showsVerticalScrollIndicator={false}>
        <View style={styles.heroCard}>
          <Text style={styles.eyebrow}>Service request #{serviceRequest.id}</Text>
          <Text style={styles.heroTitle}>{serviceRequest.request_type}</Text>
          <Text style={styles.heroSubtitle}>
            {mode === 'technician'
              ? 'Review the assigned service request and update its current progress.'
              : 'Review the current status and details of your submitted service request.'}
          </Text>

          <View
            style={[
              styles.statusBadge,
              {backgroundColor: statusColors.backgroundColor},
            ]}>
            <Text
              style={[
                styles.statusBadgeText,
                {color: statusColors.textColor},
              ]}>
              {formatServiceRequestStatus(serviceRequest.status)}
            </Text>
          </View>
        </View>

        <AppCard style={styles.sectionCard}>
          <Text style={styles.sectionTitle}>Request details</Text>
          <Text style={styles.sectionSubtitle}>
            Core information saved for this service request.
          </Text>

          <DetailRow label="Service request ID" value={`${serviceRequest.id}`} />
          <DetailRow label="Request type" value={serviceRequest.request_type} />
          <DetailRow
            label="Preferred date"
            value={formatDate(serviceRequest.date_needed)}
          />
          <DetailRow
            label="Submitted"
            value={formatDateTime(serviceRequest.created_at)}
          />

          <View style={styles.detailsBlock}>
            <Text style={styles.detailLabel}>Details</Text>
            <Text style={styles.detailsText}>{serviceRequest.details}</Text>
          </View>
        </AppCard>

        <AppCard style={styles.sectionCard}>
          <Text style={styles.sectionTitle}>
            {mode === 'technician' ? 'Customer information' : 'Assignment'}
          </Text>
          <Text style={styles.sectionSubtitle}>
            {mode === 'technician'
              ? 'Customer information for the assigned request.'
              : 'Technician assignment appears here once your request is picked up.'}
          </Text>

          <DetailRow
            label={mode === 'technician' ? 'Customer' : 'Technician'}
            value={
              mode === 'technician'
                ? serviceRequest.customer?.name || 'Customer not available'
                : serviceRequest.technician?.name || 'Pending assignment'
            }
          />
          <DetailRow
            label={mode === 'technician' ? 'Customer email' : 'Technician email'}
            value={
              mode === 'technician'
                ? serviceRequest.customer?.email || 'No email available'
                : serviceRequest.technician?.email || 'Not available yet'
            }
          />
        </AppCard>

        {mode === 'technician' ? (
          <AppCard style={styles.sectionCard}>
            <Text style={styles.sectionTitle}>Update status</Text>
            <Text style={styles.sectionSubtitle}>
              Status transitions follow the current backend rules and are only
              available when valid for this request.
            </Text>

            {availableActions.length === 0 ? (
              <View style={styles.infoCard}>
                <Text style={styles.infoTitle}>No further updates available</Text>
                <Text style={styles.infoText}>
                  This request is already in its latest allowed state.
                </Text>
              </View>
            ) : null}

            {availableActions.map(action => (
              <AppButton
                key={action.value}
                title={actionLoading ? 'Updating status...' : action.label}
                disabled={actionLoading}
                onPress={() =>
                  handleStatusUpdate(action.value, action.successMessage)
                }
                style={styles.statusActionButton}
              />
            ))}
          </AppCard>
        ) : null}
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
    backgroundColor: '#fef3c7',
    borderRadius: 28,
    marginBottom: 18,
    padding: 22,
  },
  eyebrow: {
    color: '#b45309',
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
  statusBadge: {
    alignSelf: 'flex-start',
    borderRadius: 999,
    paddingHorizontal: 14,
    paddingVertical: 8,
  },
  statusBadgeText: {
    fontSize: 13,
    fontWeight: '700',
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
  infoCard: {
    backgroundColor: '#fff7ed',
    borderRadius: 16,
    marginBottom: 16,
    padding: 16,
  },
  infoTitle: {
    color: '#9a3412',
    fontSize: 16,
    fontWeight: '700',
    marginBottom: 6,
  },
  infoText: {
    color: '#9a3412',
    fontSize: 14,
    lineHeight: 20,
  },
  statusActionButton: {
    marginTop: 12,
  },
});
