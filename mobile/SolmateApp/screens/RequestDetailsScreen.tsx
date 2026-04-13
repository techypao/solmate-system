import React, {useCallback, useContext, useState} from 'react';
import {
  ActivityIndicator,
  Alert,
  Pressable,
  SafeAreaView,
  ScrollView,
  StyleSheet,
  Text,
  View,
} from 'react-native';
import {useFocusEffect} from '@react-navigation/native';

import {AppButton, AppCard} from '../components';
import StatusBadge from '../components/StatusBadge';
import {AuthContext} from '../src/context/AuthContext';
import {ApiError} from '../src/services/api';
import {
  getAssignedInspectionRequestById,
  TechnicianInspectionRequest,
  TechnicianUpdatableStatus,
  updateInspectionRequestStatus,
} from '../src/services/technicianApi';
import {
  canCreateFinalQuotation,
  formatDate,
  formatDateTime,
  getCustomerEmail,
  getCustomerName,
} from '../src/utils/technicianRequests';

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

function StatusActionButton({
  label,
  selected,
  disabled,
  onPress,
}: {
  label: string;
  selected: boolean;
  disabled: boolean;
  onPress: () => void;
}) {
  return (
    <Pressable
      disabled={disabled}
      onPress={onPress}
      style={({pressed}) => [
        styles.statusButton,
        selected ? styles.statusButtonSelected : null,
        pressed && !disabled ? styles.statusButtonPressed : null,
        disabled ? styles.statusButtonDisabled : null,
      ]}>
      <Text
        style={[
          styles.statusButtonText,
          selected ? styles.statusButtonTextSelected : null,
        ]}>
        {label}
      </Text>
    </Pressable>
  );
}

const STATUS_OPTIONS: Array<{
  label: string;
  value: TechnicianUpdatableStatus;
  successMessage: string;
}> = [
  {
    label: 'Assigned',
    value: 'assigned',
    successMessage: 'The inspection request has been moved back to assigned.',
  },
  {
    label: 'In Progress',
    value: 'in_progress',
    successMessage: 'The inspection request is now in progress.',
  },
  {
    label: 'Completed',
    value: 'completed',
    successMessage: 'The inspection request has been marked as completed.',
  },
];

export default function RequestDetailsScreen({navigation, route}: any) {
  const {user} = useContext(AuthContext);
  const inspectionRequestId = route?.params?.inspectionRequestId;
  const initialInspectionRequest = route?.params?.initialInspectionRequest as
    | TechnicianInspectionRequest
    | undefined;

  const [inspectionRequest, setInspectionRequest] =
    useState<TechnicianInspectionRequest | null>(initialInspectionRequest || null);
  const [loading, setLoading] = useState(!initialInspectionRequest);
  const [errorMessage, setErrorMessage] = useState('');
  const [actionLoading, setActionLoading] = useState(false);

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
        const request = await getAssignedInspectionRequestById(inspectionRequestId);

        if (!request) {
          setInspectionRequest(null);
          setErrorMessage(
            'This inspection request was not found in your assigned list.',
          );
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

  const handleStatusUpdate = async (
    nextStatus: TechnicianUpdatableStatus,
    successMessage: string,
  ) => {
    if (!inspectionRequest) {
      return;
    }

    try {
      setActionLoading(true);

      const updatedRequest = await updateInspectionRequestStatus(
        inspectionRequest.id,
        nextStatus,
      );

      setInspectionRequest(updatedRequest);
      await loadInspectionRequest(false);

      Alert.alert('Success', successMessage);
    } catch (error) {
      if (error instanceof ApiError) {
        Alert.alert('Update failed', error.message);
      } else {
        Alert.alert(
          'Update failed',
          'Could not update the inspection request status.',
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
        <Text style={styles.loadingText}>Loading inspection details...</Text>
      </View>
    );
  }

  if (errorMessage || !inspectionRequest) {
    return (
      <View style={styles.centeredContainer}>
        <Text style={styles.errorTitle}>Inspection details unavailable</Text>
        <Text style={styles.errorText}>
          {errorMessage || 'No inspection request details were found.'}
        </Text>
        <AppButton
          title="Back to requests"
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
          <Text style={styles.eyebrow}>
            Inspection request #{inspectionRequest.id}
          </Text>
          <Text style={styles.heroTitle}>{getCustomerName(inspectionRequest)}</Text>
          <Text style={styles.heroSubtitle}>
            Review the assigned inspection, update its status, and submit the
            final quotation after the visit is completed.
          </Text>

          <StatusBadge status={inspectionRequest.status} />
        </View>

        <AppCard style={styles.sectionCard}>
          <Text style={styles.sectionTitle}>Customer information</Text>
          <Text style={styles.sectionSubtitle}>
            The customer connected to this assigned inspection request.
          </Text>

          <DetailRow
            label="Customer name"
            value={getCustomerName(inspectionRequest)}
          />
          <DetailRow
            label="Customer email"
            value={getCustomerEmail(inspectionRequest)}
          />
        </AppCard>

        <AppCard style={styles.sectionCard}>
          <Text style={styles.sectionTitle}>Inspection request details</Text>
          <Text style={styles.sectionSubtitle}>
            Core details captured by the customer for this visit.
          </Text>

          <DetailRow label="Inspection request ID" value={`${inspectionRequest.id}`} />
          <DetailRow
            label="Date needed"
            value={formatDate(inspectionRequest.date_needed)}
          />
          <DetailRow
            label="Created"
            value={formatDateTime(inspectionRequest.created_at)}
          />

          <View style={styles.detailsBlock}>
            <Text style={styles.detailLabel}>Details</Text>
            <Text style={styles.detailsText}>{inspectionRequest.details}</Text>
          </View>
        </AppCard>

        <AppCard style={styles.sectionCard}>
          <Text style={styles.sectionTitle}>Technician assignment context</Text>
          <Text style={styles.sectionSubtitle}>
            This inspection request is currently assigned to your account.
          </Text>

          <DetailRow label="Technician name" value={user?.name || 'Technician'} />
          <DetailRow
            label="Technician email"
            value={user?.email || 'No email available'}
          />
          <DetailRow label="Technician role" value={user?.role || 'technician'} />
        </AppCard>

        <AppCard style={styles.sectionCard}>
          <Text style={styles.sectionTitle}>Update status</Text>
          <Text style={styles.sectionSubtitle}>
            Choose the current inspection progress state. Buttons are disabled
            while the update request is being submitted.
          </Text>

          <View style={styles.statusButtonRow}>
            {STATUS_OPTIONS.map(option => (
              <StatusActionButton
                key={option.value}
                label={option.label}
                selected={inspectionRequest.status === option.value}
                disabled={
                  actionLoading || inspectionRequest.status === option.value
                }
                onPress={() =>
                  handleStatusUpdate(option.value, option.successMessage)
                }
              />
            ))}
          </View>
        </AppCard>

        <AppCard style={styles.sectionCard}>
          <Text style={styles.sectionTitle}>Final quotation</Text>
          <Text style={styles.sectionSubtitle}>
            Only completed inspection requests can proceed to final quotation
            submission.
          </Text>

          {canCreateFinalQuotation(inspectionRequest.status) ? (
            <View style={styles.completedStateCard}>
              <Text style={styles.completedStateTitle}>
                Ready for final quotation
              </Text>
              <Text style={styles.completedStateText}>
                The inspection is completed and the quotation form is now
                available.
              </Text>
            </View>
          ) : (
            <View style={styles.waitingStateCard}>
              <Text style={styles.waitingStateTitle}>
                Complete the inspection first
              </Text>
              <Text style={styles.waitingStateText}>
                Mark this request as completed before opening the final
                quotation form.
              </Text>
            </View>
          )}

          <AppButton
            title="Open Final Quotation Form"
            disabled={!canCreateFinalQuotation(inspectionRequest.status)}
            onPress={() =>
              navigation.navigate('FinalQuotationForm', {
                inspectionRequestId: inspectionRequest.id,
                inspectionRequest,
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
    backgroundColor: '#e0f2fe',
    borderRadius: 28,
    marginBottom: 18,
    padding: 22,
  },
  eyebrow: {
    color: '#0369a1',
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
  statusButtonRow: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 10,
  },
  statusButton: {
    backgroundColor: '#eff6ff',
    borderColor: '#bfdbfe',
    borderRadius: 14,
    borderWidth: 1,
    minWidth: 110,
    paddingHorizontal: 14,
    paddingVertical: 12,
  },
  statusButtonSelected: {
    backgroundColor: '#2563eb',
    borderColor: '#2563eb',
  },
  statusButtonPressed: {
    opacity: 0.88,
  },
  statusButtonDisabled: {
    opacity: 0.65,
  },
  statusButtonText: {
    color: '#1d4ed8',
    fontSize: 14,
    fontWeight: '700',
    textAlign: 'center',
  },
  statusButtonTextSelected: {
    color: '#ffffff',
  },
  completedStateCard: {
    backgroundColor: '#dcfce7',
    borderRadius: 16,
    marginBottom: 16,
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
  waitingStateCard: {
    backgroundColor: '#f8fafc',
    borderColor: '#e2e8f0',
    borderRadius: 16,
    borderWidth: 1,
    marginBottom: 16,
    padding: 16,
  },
  waitingStateTitle: {
    color: '#0f172a',
    fontSize: 16,
    fontWeight: '700',
    marginBottom: 6,
  },
  waitingStateText: {
    color: '#475569',
    fontSize: 14,
    lineHeight: 20,
  },
});
