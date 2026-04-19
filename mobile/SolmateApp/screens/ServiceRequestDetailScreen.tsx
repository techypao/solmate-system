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

/* \u2500\u2500 design tokens \u2500\u2500 */

const NAVY = '#152a4a';
const GOLD = '#e8a800';
const MUTED = '#7b8699';
const BG = '#e0e8f5';
const CARD = '#ffffff';
const DIVIDER = '#edf1f7';

/* \u2500\u2500 helpers (preserved) \u2500\u2500 */

function formatDate(value?: string | null, fallback = 'Flexible') {
  if (!value) return fallback;
  const parsedDate = new Date(value);
  if (Number.isNaN(parsedDate.getTime())) return value;
  return parsedDate.toLocaleDateString();
}

function formatDateTime(value?: string | null, fallback = 'Not available') {
  if (!value) return fallback;
  const parsedDate = new Date(value);
  if (Number.isNaN(parsedDate.getTime())) return value;
  return parsedDate.toLocaleString();
}

function getFriendlyErrorMessage(error: unknown) {
  if (error instanceof ApiError) return error.message;
  return 'Could not load the service request details.';
}

/* \u2500\u2500 DetailRow \u2500\u2500 */

function DetailRow({
  label,
  value,
  bold,
}: {
  label: string;
  value?: string | null;
  bold?: boolean;
}) {
  return (
    <View style={s.detailRow}>
      <Text style={s.detailLabel}>{label}</Text>
      <Text style={[s.detailValue, bold && s.detailValueBold]}>
        {value || 'Not available'}
      </Text>
    </View>
  );
}

/* \u2500\u2500 technician status actions (preserved) \u2500\u2500 */

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

/* \u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550
   Main screen
   \u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550 */

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
        if (showLoadingState) setLoading(true);
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
    const alreadyRequestedCompletion =
      !!serviceRequest?.technician_marked_done_at;

    if (mode !== 'technician') return [];

    return TECHNICIAN_STATUS_ACTIONS.filter(action => {
      if (!action.currentStatuses.includes(currentStatus)) return false;
      if (action.value === 'notify_admin_done' && alreadyRequestedCompletion)
        return false;
      return true;
    });
  }, [mode, serviceRequest?.status, serviceRequest?.technician_marked_done_at]);

  const handleStatusUpdate = async (
    nextStatus: TechnicianServiceRequestStatus | 'notify_admin_done',
    successMessage: string,
  ) => {
    if (!serviceRequest || actionLoading) return;

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

  /* \u2500\u2500 loading \u2500\u2500 */

  if (loading) {
    return (
      <SafeAreaView style={s.safe}>
        <View style={s.centered}>
          <ActivityIndicator size="large" color={GOLD} />
          <Text style={s.loadingText}>Loading service request\u2026</Text>
        </View>
      </SafeAreaView>
    );
  }

  /* \u2500\u2500 error / missing \u2500\u2500 */

  if (errorMessage || !serviceRequest) {
    return (
      <SafeAreaView style={s.safe}>
        <View style={s.centered}>
          <Text style={s.errorTitle}>Service request unavailable</Text>
          <Text style={s.errorText}>
            {errorMessage || 'No service request details were found.'}
          </Text>
          <Pressable
            onPress={() => loadServiceRequest(true)}
            style={({pressed}) => [s.goldBtn, pressed && s.pressed]}>
            <Text style={s.goldBtnText}>Try Again</Text>
          </Pressable>
          <Pressable
            onPress={() =>
              navigation.navigate(
                mode === 'technician'
                  ? 'TechnicianServiceRequests'
                  : 'ServiceRequestList',
              )
            }
            style={({pressed}) => [s.outlineBtn, pressed && s.pressed]}>
            <Text style={s.outlineBtnText}>Back to Requests</Text>
          </Pressable>
        </View>
      </SafeAreaView>
    );
  }

  /* \u2500\u2500 main \u2500\u2500 */

  const statusColors = getServiceRequestStatusColors(serviceRequest.status);
  const awaitingAdminConfirmation =
    !!serviceRequest.technician_marked_done_at &&
    serviceRequest.status !== 'completed';
  const adminConfirmedCompletion = serviceRequest.status === 'completed';

  return (
    <SafeAreaView style={s.safe}>
      <ScrollView
        contentContainerStyle={s.scroll}
        showsVerticalScrollIndicator={false}>

        {/* \u2500\u2500 brand \u2500\u2500 */}
        <Text style={s.brand}>
          Sol<Text style={s.brandAccent}>Mate</Text>
        </Text>

        {/* \u2500\u2500 back \u2500\u2500 */}
        <Pressable
          hitSlop={14}
          onPress={() => navigation.goBack()}
          style={({pressed}) => [s.backBtn, pressed && s.pressed]}>
          <Text style={s.backIcon}>{'\u2039'}</Text>
        </Pressable>

        {/* \u2500\u2500 title \u2500\u2500 */}
        <Text style={s.title}>Service Details</Text>
        <Text style={s.subtitle}>
          {mode === 'technician'
            ? 'Review the assigned service request, update its work progress, and notify the admin when the job is done.'
            : 'Review the current official status and details of your submitted service request.'}
        </Text>

        {/* \u2500\u2500 badges row \u2500\u2500 */}
        <View style={s.badgeRow}>
          <View style={s.typeBadge}>
            <Text style={s.typeBadgeText}>{serviceRequest.request_type}</Text>
          </View>
          <View
            style={[
              s.statusBadge,
              {backgroundColor: statusColors.backgroundColor},
            ]}>
            <Text
              style={[s.statusBadgeText, {color: statusColors.textColor}]}>
              {formatServiceRequestStatus(serviceRequest.status)}
            </Text>
          </View>
        </View>

        {/* \u2500\u2500 Service Information \u2500\u2500 */}
        <View style={s.card}>
          <Text style={s.cardTitle}>Service Information</Text>

          <DetailRow
            label="Service Request ID"
            value={'SR-' + serviceRequest.id}
            bold
          />
          <DetailRow label="Type" value={serviceRequest.request_type} />
          <DetailRow
            label="Status"
            value={formatServiceRequestStatus(serviceRequest.status)}
            bold
          />
          <DetailRow
            label="Created At"
            value={formatDateTime(serviceRequest.created_at)}
          />
          <DetailRow
            label="Schedule Date"
            value={formatDate(serviceRequest.date_needed)}
          />
          <DetailRow
            label={mode === 'technician' ? 'Customer' : 'Technician Assigned'}
            value={
              mode === 'technician'
                ? serviceRequest.customer?.name || 'Customer not available'
                : serviceRequest.technician?.name || 'Pending assignment'
            }
            bold
          />
        </View>

        {/* \u2500\u2500 Request Details \u2500\u2500 */}
        <View style={s.card}>
          <Text style={s.cardTitle}>Request Details</Text>

          <DetailRow
            label={
              mode === 'technician' ? 'Customer email' : 'Technician email'
            }
            value={
              mode === 'technician'
                ? serviceRequest.customer?.email || 'No email available'
                : serviceRequest.technician?.email || 'Not available yet'
            }
          />
          <DetailRow
            label="Contact Number"
            value={serviceRequest.contact_number || 'Not provided'}
          />
          {mode === 'technician' ? (
            <DetailRow
              label="Customer contact number"
              value={serviceRequest.contact_number || 'Not provided'}
            />
          ) : null}

          <View style={s.descBlock}>
            <Text style={s.descLabel}>Problem Description</Text>
            <Text style={s.descText}>{serviceRequest.details}</Text>
          </View>
        </View>

        {/* \u2500\u2500 Completion Report \u2500\u2500 */}
        <View style={s.card}>
          <Text style={s.cardTitle}>Completion Report</Text>

          {awaitingAdminConfirmation ? (
            <View style={s.infoCard}>
              <Text style={s.infoTitle}>Awaiting admin confirmation</Text>
              <Text style={s.infoText}>
                {mode === 'technician'
                  ? 'You already marked this service as done. Wait for the admin to review and finalize the official status.'
                  : 'The technician reported that the service work is done. The admin still needs to confirm the final official status.'}
              </Text>
              <View style={s.infoMeta}>
                <Text style={s.infoMetaLabel}>Technician marked done</Text>
                <Text style={s.infoMetaValue}>
                  {formatDateTime(serviceRequest.technician_marked_done_at)}
                </Text>
              </View>
            </View>
          ) : adminConfirmedCompletion ? (
            <View style={s.successCard}>
              <Text style={s.successTitle}>Admin confirmed completion</Text>
              <Text style={s.successText}>
                This service request is officially completed.
              </Text>
              {serviceRequest.technician_marked_done_at ? (
                <View style={s.successMeta}>
                  <Text style={s.successMetaLabel}>Technician marked done</Text>
                  <Text style={s.successMetaValue}>
                    {formatDateTime(serviceRequest.technician_marked_done_at)}
                  </Text>
                </View>
              ) : null}
            </View>
          ) : (
            <View style={s.infoCard}>
              <Text style={s.infoTitle}>No completion request yet</Text>
              <Text style={s.infoText}>
                {mode === 'technician'
                  ? 'When the job is finished, use the button below to notify the admin for final review.'
                  : 'The completed status will appear here after the technician finishes the work and the admin confirms it.'}
              </Text>
            </View>
          )}
        </View>

        {/* \u2500\u2500 Progress Updates (technician only) \u2500\u2500 */}
        {mode === 'technician' ? (
          <View style={s.card}>
            <Text style={s.cardTitle}>Progress Updates</Text>
            <Text style={s.cardSubtitle}>
              Move the service into progress, then notify the admin when the
              work is done.
            </Text>

            {availableActions.length === 0 ? (
              <View style={s.infoCard}>
                <Text style={s.infoTitle}>
                  No further technician updates available
                </Text>
                <Text style={s.infoText}>
                  {awaitingAdminConfirmation
                    ? 'This request is waiting for admin review.'
                    : 'This request is already in its latest technician-managed state.'}
                </Text>
              </View>
            ) : null}

            {availableActions.map(action => (
              <Pressable
                key={action.value}
                disabled={actionLoading}
                onPress={() =>
                  handleStatusUpdate(action.value, action.successMessage)
                }
                style={({pressed}) => [
                  s.goldBtn,
                  actionLoading && s.btnDisabled,
                  pressed && s.pressed,
                ]}>
                <Text style={s.goldBtnText}>
                  {actionLoading ? 'Saving update\u2026' : action.label}
                </Text>
              </Pressable>
            ))}
          </View>
        ) : null}

        {/* \u2500\u2500 bottom button \u2500\u2500 */}
        <Pressable
          onPress={() => navigation.goBack()}
          style={({pressed}) => [s.outlineBtn, pressed && s.pressed]}>
          <Text style={s.outlineBtnText}>Back</Text>
        </Pressable>

        <View style={s.spacer} />
      </ScrollView>
    </SafeAreaView>
  );
}

/* \u2500\u2500 styles \u2500\u2500 */

const s = StyleSheet.create({
  safe: {flex: 1, backgroundColor: BG},
  scroll: {paddingHorizontal: 22, paddingTop: 20, paddingBottom: 30},
  pressed: {opacity: 0.85},

  /* brand */
  brand: {fontSize: 22, fontWeight: '800', color: NAVY, marginBottom: 10},
  brandAccent: {color: GOLD},

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
    shadowOffset: {width: 0, height: 2},
    shadowOpacity: 0.1,
    shadowRadius: 6,
    elevation: 3,
  },
  backIcon: {fontSize: 28, color: NAVY, fontWeight: '600', marginTop: -2},

  /* title */
  title: {fontSize: 26, fontWeight: '900', color: NAVY, marginBottom: 4},
  subtitle: {fontSize: 14, color: MUTED, lineHeight: 20, marginBottom: 18},

  /* centered / loading */
  centered: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    padding: 24,
  },
  loadingText: {color: MUTED, fontSize: 14, marginTop: 14},

  /* error */
  errorTitle: {
    color: NAVY,
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
    marginBottom: 16,
  },

  /* badges row */
  badgeRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
    marginBottom: 18,
  },
  typeBadge: {
    backgroundColor: '#e8ecf4',
    borderRadius: 20,
    paddingHorizontal: 14,
    paddingVertical: 7,
  },
  typeBadgeText: {color: NAVY, fontSize: 12, fontWeight: '700'},
  statusBadge: {
    borderRadius: 20,
    paddingHorizontal: 14,
    paddingVertical: 7,
  },
  statusBadgeText: {fontSize: 12, fontWeight: '700'},

  /* card */
  card: {
    backgroundColor: CARD,
    borderRadius: 22,
    padding: 20,
    marginBottom: 16,
    shadowColor: '#8a9bbd',
    shadowOffset: {width: 0, height: 4},
    shadowOpacity: 0.1,
    shadowRadius: 14,
    elevation: 4,
  },
  cardTitle: {fontSize: 18, fontWeight: '900', color: NAVY, marginBottom: 14},
  cardSubtitle: {
    fontSize: 14,
    color: MUTED,
    lineHeight: 20,
    marginBottom: 14,
  },

  /* detail row (horizontal label-value) */
  detailRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    paddingVertical: 11,
    borderTopColor: DIVIDER,
    borderTopWidth: 1,
  },
  detailLabel: {color: MUTED, fontSize: 13, fontWeight: '600', flex: 1},
  detailValue: {
    color: NAVY,
    fontSize: 14,
    fontWeight: '600',
    flex: 1,
    textAlign: 'right',
  },
  detailValueBold: {fontWeight: '800'},

  /* description block */
  descBlock: {paddingTop: 14, borderTopColor: DIVIDER, borderTopWidth: 1},
  descLabel: {
    color: MUTED,
    fontSize: 12,
    fontWeight: '700',
    textTransform: 'uppercase',
    marginBottom: 8,
  },
  descText: {color: NAVY, fontSize: 14, lineHeight: 22, opacity: 0.85},

  /* info card (awaiting / pending) */
  infoCard: {
    backgroundColor: '#fffbeb',
    borderRadius: 16,
    borderWidth: 1,
    borderColor: '#fde68a',
    padding: 16,
    marginBottom: 8,
  },
  infoTitle: {color: '#92400e', fontSize: 15, fontWeight: '700', marginBottom: 6},
  infoText: {color: '#a16207', fontSize: 13, lineHeight: 19},
  infoMeta: {marginTop: 12},
  infoMetaLabel: {
    color: '#a16207',
    fontSize: 12,
    fontWeight: '700',
    textTransform: 'uppercase',
    marginBottom: 4,
  },
  infoMetaValue: {color: '#92400e', fontSize: 14, fontWeight: '700'},

  /* success card */
  successCard: {
    backgroundColor: '#f0fdf4',
    borderRadius: 16,
    borderWidth: 1,
    borderColor: '#bbf7d0',
    padding: 16,
    marginBottom: 8,
  },
  successTitle: {
    color: '#166534',
    fontSize: 15,
    fontWeight: '700',
    marginBottom: 6,
  },
  successText: {color: '#166534', fontSize: 13, lineHeight: 19},
  successMeta: {marginTop: 12},
  successMetaLabel: {
    color: '#166534',
    fontSize: 12,
    fontWeight: '700',
    textTransform: 'uppercase',
    marginBottom: 4,
  },
  successMetaValue: {color: '#166534', fontSize: 14, fontWeight: '700'},

  /* buttons */
  goldBtn: {
    backgroundColor: GOLD,
    borderRadius: 28,
    paddingVertical: 14,
    alignItems: 'center',
    marginTop: 12,
    shadowColor: GOLD,
    shadowOffset: {width: 0, height: 4},
    shadowOpacity: 0.25,
    shadowRadius: 10,
    elevation: 4,
  },
  goldBtnText: {
    fontSize: 15,
    fontWeight: '900',
    color: CARD,
    letterSpacing: 0.3,
  },
  outlineBtn: {
    backgroundColor: CARD,
    borderRadius: 28,
    paddingVertical: 14,
    alignItems: 'center',
    borderWidth: 1,
    borderColor: DIVIDER,
    marginTop: 12,
  },
  outlineBtnText: {fontSize: 15, fontWeight: '800', color: NAVY},
  btnDisabled: {opacity: 0.5},

  /* spacer */
  spacer: {minHeight: 20},
});
