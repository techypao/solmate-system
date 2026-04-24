import React, {useCallback, useMemo, useState} from 'react';
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

import {AppButton} from '../components';
import {ApiError} from '../src/services/api';
import {
  getServiceRequestById,
  getTechnicianServiceRequestById,
  requestTechnicianServiceCompletion,
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
  return parsedDate.toLocaleDateString('en-US', {
    month: 'short',
    day: '2-digit',
    year: 'numeric',
  });
}

function formatDateTime(value?: string | null, fallback = 'Not available') {
  if (!value) {
    return fallback;
  }
  const parsedDate = new Date(value);
  if (Number.isNaN(parsedDate.getTime())) {
    return value;
  }
  const datePart = parsedDate.toLocaleDateString('en-US', {
    month: 'short',
    day: '2-digit',
    year: 'numeric',
  });
  const timePart = parsedDate.toLocaleTimeString('en-US', {
    hour: '2-digit',
    minute: '2-digit',
  });
  return `${datePart} • ${timePart}`;
}

function getFriendlyErrorMessage(error: unknown) {
  if (error instanceof ApiError) {
    return error.message;
  }
  return 'Could not load the maintenance request details.';
}

function getInstallationType(serviceRequest: ServiceRequest) {
  const details = serviceRequest.details || '';
  const match = details.match(/Installation Type:\s*(.+)/i);
  if (match?.[1]) {
    return match[1].trim();
  }

  return serviceRequest.request_type;
}

function getMaintenanceConcern(serviceRequest: ServiceRequest) {
  const details = serviceRequest.details || '';
  const match = details.match(/Maintenance Concern:\s*(.+)/i);
  if (match?.[1]) {
    return match[1].trim();
  }

  return serviceRequest.request_type;
}

function InlineRow({label, value}: {label: string; value?: string | null}) {
  return (
    <View style={styles.inlineRow}>
      <Text style={styles.inlineLabel}>{label}</Text>
      <Text style={styles.inlineValue}>{value || 'Not available'}</Text>
    </View>
  );
}

function TimelineItem({
  datetime,
  status,
  description,
  isLast,
}: {
  datetime: string;
  status: string;
  description: string;
  isLast: boolean;
}) {
  const colors = getServiceRequestStatusColors(status);
  return (
    <View style={styles.timelineItem}>
      <View style={styles.timelineDotCol}>
        <View style={styles.timelineDot} />
        {!isLast ? <View style={styles.timelineConnector} /> : null}
      </View>
      <View style={styles.timelineBody}>
        <Text style={styles.timelineDatetime}>{datetime}</Text>
        <View style={styles.timelineRow}>
          <View
            style={[
              styles.timelineBadge,
              {backgroundColor: colors.backgroundColor},
            ]}>
            <Text
              style={[styles.timelineBadgeText, {color: colors.textColor}]}>
              {formatServiceRequestStatus(status)}
            </Text>
          </View>
          <Text style={styles.timelineDesc}>{description}</Text>
        </View>
      </View>
    </View>
  );
}

const TECHNICIAN_STATUS_ACTIONS: Array<{
  label: string;
  value: TechnicianServiceRequestStatus | 'notify_admin_done';
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
    label: 'Notify Admin Service Done',
    value: 'notify_admin_done',
    currentStatuses: ['in_progress'],
    successMessage: 'The admin has been notified that the service is done.',
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
  const customerRequestCategory =
    (serviceRequest?.request_type || route?.params?.requestCategory || '').toLowerCase() ===
    'installation'
      ? 'installation'
      : 'maintenance';
  const customerTitle =
    customerRequestCategory === 'installation'
      ? 'Installation Details'
      : 'Maintenance Details';
  const customerCardTitle =
    customerRequestCategory === 'installation'
      ? 'Installation Information'
      : 'Maintenance Information';
  const customerRequestIdLabel =
    customerRequestCategory === 'installation'
      ? 'Installation Request ID'
      : 'Maintenance Request ID';
  const customerTypeLabel =
    customerRequestCategory === 'installation'
      ? 'Installation Service Type'
      : 'Maintenance Service Type';

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
    const alreadyRequestedCompletion = !!serviceRequest?.technician_marked_done_at;

    if (mode !== 'technician') {
      return [];
    }

    return TECHNICIAN_STATUS_ACTIONS.filter(action => {
      if (!action.currentStatuses.includes(currentStatus)) {
        return false;
      }

      if (action.value === 'notify_admin_done' && alreadyRequestedCompletion) {
        return false;
      }

      return true;
    });
  }, [mode, serviceRequest?.status, serviceRequest?.technician_marked_done_at]);

  const handleStatusUpdate = async (
    nextStatus: TechnicianServiceRequestStatus | 'notify_admin_done',
    successMessage: string,
  ) => {
    if (!serviceRequest || actionLoading) {
      return;
    }

    try {
      setActionLoading(true);

      const updatedServiceRequest =
        nextStatus === 'notify_admin_done'
          ? await requestTechnicianServiceCompletion(serviceRequest.id)
          : await updateTechnicianServiceRequestStatus(
              serviceRequest.id,
              nextStatus,
            );

      const nextRequest =
        updatedServiceRequest?.id !== undefined
          ? updatedServiceRequest
          : {
              ...serviceRequest,
              ...(nextStatus === 'notify_admin_done'
                ? {technician_marked_done_at: new Date().toISOString()}
                : {status: nextStatus}),
            };

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
          'Could not update the service request.',
        );
      }
    } finally {
      setActionLoading(false);
    }
  };

  const timelineEvents = useMemo(() => {
    if (!serviceRequest) {
      return [];
    }
    const events: {datetime: string; status: string; description: string}[] =
      [];
    if (serviceRequest.created_at) {
      events.push({
        datetime: formatDateTime(serviceRequest.created_at),
        status: 'pending',
        description: 'Request submitted',
      });
    }
    const s = (serviceRequest.status || '').toLowerCase();
    if (['assigned', 'in_progress', 'completed'].includes(s)) {
      events.push({
        datetime: formatDateTime(serviceRequest.updated_at),
        status: 'assigned',
        description: 'Assigned to technician',
      });
    }
    if (['in_progress', 'completed'].includes(s)) {
      events.push({
        datetime: formatDateTime(serviceRequest.updated_at),
        status: 'in_progress',
        description: 'Job started',
      });
    }
    if (serviceRequest.technician_marked_done_at) {
      events.push({
        datetime: formatDateTime(serviceRequest.technician_marked_done_at),
        status: 'in_progress',
        description: 'Technician marked done',
      });
    }
    if (s === 'completed') {
      events.push({
        datetime: formatDateTime(serviceRequest.updated_at),
        status: 'completed',
        description: 'Service completed',
      });
    }
    return events;
  }, [serviceRequest]);

  if (loading) {
    return (
      <SafeAreaView style={styles.safeArea}>
        <View style={styles.centeredContainer}>
          <ActivityIndicator size="large" color={NAVY} />
          <Text style={styles.loadingText}>Loading service request...</Text>
        </View>
      </SafeAreaView>
    );
  }

  if (errorMessage || !serviceRequest) {
    return (
      <SafeAreaView style={styles.safeArea}>
        <View style={styles.header}>
          <Pressable
            hitSlop={12}
            onPress={() => navigation.goBack()}
            style={({pressed}) => [styles.backBtn, pressed && styles.backBtnPressed]}>
            <Text style={styles.backIcon}>{'‹'}</Text>
          </Pressable>
          <Text style={styles.headerTitle}>
            {mode === 'technician' ? 'Service Details' : customerTitle}
          </Text>
          <View style={styles.headerSpacer} />
        </View>
        <View style={styles.centeredContainer}>
          <Text style={styles.errorTitle}>
            {mode === 'technician'
              ? 'Service request unavailable'
              : customerRequestCategory === 'installation'
                ? 'Installation request unavailable'
                : 'Maintenance request unavailable'}
          </Text>
          <Text style={styles.errorText}>
            {errorMessage ||
              (mode === 'technician'
                ? 'No service request details were found.'
                : customerRequestCategory === 'installation'
                  ? 'No installation request details were found.'
                  : 'No maintenance request details were found.')}
          </Text>
          <AppButton
            title="Try again"
            onPress={() => loadServiceRequest(true)}
            style={styles.actionBtn}
          />
          <AppButton
            title={
              mode === 'technician'
                ? 'Back to requests'
                : customerRequestCategory === 'installation'
                  ? 'Back to installation'
                  : 'Back to maintenance'
            }
            variant="outline"
            onPress={() =>
              navigation.navigate(
                mode === 'technician'
                  ? 'TechnicianServiceRequests'
                  : 'ServiceRequestList',
                mode === 'technician'
                  ? undefined
                  : {requestCategory: customerRequestCategory},
              )
            }
            style={[styles.actionBtn, {marginTop: 10}]}
          />
        </View>
      </SafeAreaView>
    );
  }

  const statusColors = getServiceRequestStatusColors(serviceRequest.status);
  const displayType =
    mode === 'customer' && customerRequestCategory === 'maintenance'
      ? getMaintenanceConcern(serviceRequest)
      : mode === 'customer' && customerRequestCategory === 'installation'
        ? getInstallationType(serviceRequest)
        : serviceRequest.request_type;

  return (
    <SafeAreaView style={styles.safeArea}>
      {/* ── header ── */}
      <View style={styles.header}>
        <Pressable
          hitSlop={12}
          onPress={() => navigation.goBack()}
          style={({pressed}) => [
            styles.backBtn,
            pressed && styles.backBtnPressed,
          ]}>
          <Text style={styles.backIcon}>{'‹'}</Text>
        </Pressable>
        <Text style={styles.headerTitle}>
          {mode === 'technician' ? 'Service Details' : customerTitle}
        </Text>
        <View
          style={[
            styles.statusPill,
            {backgroundColor: statusColors.backgroundColor},
          ]}>
          <Text
            style={[styles.statusPillText, {color: statusColors.textColor}]}>
            {formatServiceRequestStatus(serviceRequest.status)}
          </Text>
        </View>
      </View>

      <ScrollView
        contentContainerStyle={styles.content}
        showsVerticalScrollIndicator={false}>

        {/* ── Service Information ── */}
        <View style={styles.card}>
          <Text style={styles.cardTitle}>
            {mode === 'technician' ? 'Service Information' : customerCardTitle}
          </Text>
          <InlineRow
            label={mode === 'technician' ? 'Service Request ID' : customerRequestIdLabel}
            value={`SR-${serviceRequest.id}`}
          />
          <InlineRow
            label={mode === 'technician' ? 'Type' : customerTypeLabel}
            value={displayType}
          />
          <InlineRow
            label="Status"
            value={formatServiceRequestStatus(serviceRequest.status)}
          />
          <InlineRow
            label="Created At"
            value={formatDateTime(serviceRequest.created_at)}
          />
          <InlineRow
            label="Scheduled Date"
            value={formatDate(serviceRequest.date_needed)}
          />
          <InlineRow
            label="Address"
            value={serviceRequest.address || 'Not provided'}
          />
          <InlineRow
            label="Technician Assigned"
            value={serviceRequest.technician?.name || 'Not assigned'}
          />
        </View>

        {/* ── Updates Timeline ── */}
        <View style={styles.card}>
          <Text style={styles.cardTitle}>Updates Timeline</Text>
          {timelineEvents.length === 0 ? (
            <Text style={styles.emptyText}>No updates yet.</Text>
          ) : (
            timelineEvents.map((event, index) => (
              <TimelineItem
                key={index}
                datetime={event.datetime}
                status={event.status}
                description={event.description}
                isLast={index === timelineEvents.length - 1}
              />
            ))
          )}
        </View>

        {/* ── Action Buttons ── */}
        <View style={styles.actionsBlock}>
          {mode === 'technician'
            ? availableActions.map((action, index) => (
                <AppButton
                  key={action.value}
                  title={actionLoading ? 'Saving...' : action.label}
                  disabled={actionLoading}
                  onPress={() =>
                    handleStatusUpdate(action.value, action.successMessage)
                  }
                  style={[
                    styles.actionBtn,
                    index === 0
                      ? {backgroundColor: GOLD, borderColor: GOLD}
                      : {
                          backgroundColor: CARD,
                          borderColor: NAVY,
                          borderWidth: 1.5,
                        },
                  ]}
                  textStyle={
                    index === 0 ? {color: NAVY} : {color: NAVY}
                  }
                />
              ))
            : null}
          <AppButton
            title="Back"
            variant="outline"
            onPress={() => navigation.goBack()}
            style={[
              styles.actionBtn,
              {backgroundColor: CARD, borderColor: NAVY, borderWidth: 1.5},
            ]}
            textStyle={{color: NAVY}}
          />
        </View>
      </ScrollView>
    </SafeAreaView>
  );
}

/* ── design tokens ── */
const NAVY = '#1a2f5e';
const GOLD = '#e8a800';
const BG   = '#edf2fb';
const CARD = '#ffffff';
const MUTED = '#8a9ab5';
const DIVIDER = '#e4eaf5';

const styles = StyleSheet.create({
  safeArea: {
    backgroundColor: BG,
    flex: 1,
  },

  /* ── header ── */
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 16,
    paddingVertical: 12,
    backgroundColor: BG,
  },
  backBtn: {
    width: 36,
    height: 36,
    alignItems: 'center',
    justifyContent: 'center',
  },
  backBtnPressed: {
    opacity: 0.55,
  },
  backIcon: {
    fontSize: 30,
    color: NAVY,
    lineHeight: 34,
    fontWeight: '400',
  },
  headerTitle: {
    flex: 1,
    textAlign: 'center',
    fontSize: 18,
    fontWeight: '700',
    color: NAVY,
  },
  statusPill: {
    borderRadius: 999,
    paddingHorizontal: 12,
    paddingVertical: 5,
  },
  statusPillText: {
    fontSize: 12,
    fontWeight: '700',
  },
  headerSpacer: {
    width: 36,
  },

  /* ── scroll content ── */
  content: {
    paddingHorizontal: 16,
    paddingTop: 4,
    paddingBottom: 32,
  },

  /* ── cards ── */
  card: {
    backgroundColor: CARD,
    borderRadius: 16,
    borderWidth: 1,
    borderColor: DIVIDER,
    padding: 16,
    marginBottom: 12,
    shadowColor: '#8a9bbd',
    shadowOffset: {width: 0, height: 2},
    shadowOpacity: 0.08,
    shadowRadius: 8,
    elevation: 2,
  },
  cardTitle: {
    color: NAVY,
    fontSize: 15,
    fontWeight: '800',
    marginBottom: 4,
  },

  /* ── inline rows (label left, value right) ── */
  inlineRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    paddingVertical: 9,
    borderTopWidth: 1,
    borderTopColor: DIVIDER,
  },
  inlineLabel: {
    color: MUTED,
    fontSize: 12,
    flex: 1,
  },
  inlineValue: {
    color: NAVY,
    fontSize: 13,
    fontWeight: '700',
    textAlign: 'right',
    flex: 1,
    flexShrink: 1,
  },

  /* ── timeline ── */
  timelineItem: {
    flexDirection: 'row',
    marginTop: 12,
  },
  timelineDotCol: {
    alignItems: 'center',
    width: 18,
    marginRight: 10,
  },
  timelineDot: {
    width: 10,
    height: 10,
    borderRadius: 5,
    backgroundColor: NAVY,
    zIndex: 1,
  },
  timelineConnector: {
    flex: 1,
    width: 2,
    backgroundColor: DIVIDER,
    marginTop: 3,
    marginBottom: -6,
  },
  timelineBody: {
    flex: 1,
    paddingBottom: 10,
  },
  timelineDatetime: {
    color: MUTED,
    fontSize: 12,
    marginBottom: 5,
  },
  timelineRow: {
    flexDirection: 'row',
    alignItems: 'center',
    flexWrap: 'wrap',
    gap: 8,
  },
  timelineBadge: {
    borderRadius: 999,
    paddingHorizontal: 9,
    paddingVertical: 3,
  },
  timelineBadgeText: {
    fontSize: 11,
    fontWeight: '700',
  },
  timelineDesc: {
    color: NAVY,
    fontSize: 13,
    flex: 1,
  },
  emptyText: {
    color: MUTED,
    fontSize: 13,
    fontStyle: 'italic',
    marginTop: 8,
  },

  /* ── action buttons ── */
  actionsBlock: {
    gap: 10,
    marginTop: 4,
  },
  actionBtn: {
    borderRadius: 12,
  },

  /* ── loading / error ── */
  centeredContainer: {
    alignItems: 'center',
    flex: 1,
    justifyContent: 'center',
    padding: 24,
  },
  loadingText: {
    color: MUTED,
    fontSize: 14,
    marginTop: 12,
  },
  errorTitle: {
    color: NAVY,
    fontSize: 20,
    fontWeight: '800',
    marginBottom: 8,
    textAlign: 'center',
  },
  errorText: {
    color: '#b91c1c',
    fontSize: 14,
    lineHeight: 20,
    textAlign: 'center',
    marginBottom: 16,
  },
});
