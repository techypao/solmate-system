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
import TechnicianBottomNav from '../src/components/TechnicianBottomNav';

import {AppButton, StatusBadge} from '../components';
import ServiceCompletionReportCard from '../components/ServiceCompletionReportCard';
import {ApiError} from '../src/services/api';
import {
  getAssignedInspectionRequestById,
  submitInspectionCompletionReport,
  TechnicianInspectionRequest,
  TechnicianUpdatableStatus,
  updateInspectionRequestStatus,
} from '../src/services/technicianApi';
import {
  canCreateFinalQuotation,
  formatDisplayValue,
  formatDateTime,
  getCustomerName,
  formatServiceRequestStatus,
  normalizeRequestStatus,
} from '../src/utils/technicianRequests';
import {getSolmateStatusColors} from '../src/theme/colors';

// ─── colour tokens ────────────────────────────────────────────────────────────
const NAVY = '#1A2B55';
const GOLD = '#F5C000';
const BG = '#C8D8F0';
const CARD   = '#ffffff';
const MUTED = '#6B7A99';
const SHADOW = '#8a9bbd';
const BORDER = '#D4E0F2';

// ─── helpers ─────────────────────────────────────────────────────────────────
function getFriendlyErrorMessage(error: unknown) {
  if (error instanceof ApiError) {return error.message;}
  return 'Could not load the inspection request details.';
}

function formatIRQId(id: unknown) {
  return `IRQ-${formatDisplayValue(id, '0').padStart(4, '0')}`;
}

function formatSchedule(dateNeeded?: unknown) {
  const dateValue = formatDisplayValue(dateNeeded, '');
  if (!dateValue) {return 'Not specified';}
  const d = new Date(dateValue);
  if (isNaN(d.getTime())) {return dateValue;}
  return (
    d.toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric'}) +
    ' • ' +
    d.toLocaleTimeString('en-US', {hour: 'numeric', minute: '2-digit'})
  );
}

function isScheduledForToday(dateNeeded?: unknown) {
  const dateValue = formatDisplayValue(dateNeeded, '');

  if (!dateValue) {
    return false;
  }

  const scheduledDate = new Date(dateValue);
  if (isNaN(scheduledDate.getTime())) {
    return false;
  }

  const today = new Date();
  return (
    scheduledDate.getFullYear() === today.getFullYear() &&
    scheduledDate.getMonth() === today.getMonth() &&
    scheduledDate.getDate() === today.getDate()
  );
}

function formatInspectionStatus(status?: string | null) {
  return formatServiceRequestStatus(status);
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
  const colors = getSolmateStatusColors(status);

  return (
    <View style={s.timelineItem}>
      <View style={s.timelineDotCol}>
        <View style={s.timelineDot} />
        {!isLast ? <View style={s.timelineConnector} /> : null}
      </View>
      <View style={s.timelineBody}>
        <Text style={s.timelineDatetime}>{datetime}</Text>
        <View style={s.timelineRow}>
          <View
            style={[
              s.timelineBadge,
              {backgroundColor: colors.backgroundColor},
            ]}>
            <Text style={[s.timelineBadgeText, {color: colors.textColor}]}>
              {formatInspectionStatus(status)}
            </Text>
          </View>
          <Text style={s.timelineDesc}>{description}</Text>
        </View>
      </View>
    </View>
  );
}

const STATUS_OPTIONS: Array<{
  label: string;
  value: TechnicianUpdatableStatus;
  currentStatuses: string[];
  successMessage: string;
}> = [
  {
    label: 'Mark In Progress',
    value: 'in_progress',
    currentStatuses: ['assigned'],
    successMessage: 'The inspection request is now in progress.',
  },
];

// ─── InfoRow ──────────────────────────────────────────────────────────────────
function InfoRow({label, value}: {label: string; value?: unknown}) {
  return (
    <View style={s.infoRow}>
      <Text style={s.infoLabel}>{label}</Text>
      <Text style={s.infoValue}>
        {formatDisplayValue(value, 'Not available')}
      </Text>
    </View>
  );
}

// ─── bottom nav icons ─────────────────────────────────────────────────────────
function ScreenHeader({onBack}: {onBack: () => void}) {
  return (
    <View style={s.headerRow}>
      <Pressable style={s.backBtn} onPress={onBack} hitSlop={10}>
        <Text style={s.backArrow}>{'‹'}</Text>
      </Pressable>
      <Text style={s.headerTitle}>Inspection Details</Text>
      {/* spacer keeps title centered */}
      <View style={s.backBtn} />
    </View>
  );
}

export default function RequestDetailsScreen({navigation, route}: any) {
  const inspectionRequestId = route?.params?.inspectionRequestId;
  const initialInspectionRequest = route?.params?.initialInspectionRequest as
    | TechnicianInspectionRequest
    | undefined;

  const [inspectionRequest, setInspectionRequest] =
    useState<TechnicianInspectionRequest | null>(initialInspectionRequest ?? null);
  const [loading, setLoading] = useState(!initialInspectionRequest);
  const [errorMessage, setErrorMessage] = useState('');
  const [actionLoading, setActionLoading] = useState(false);
  const [showCompletionReportForm, setShowCompletionReportForm] = useState(
    !!initialInspectionRequest?.completion_report,
  );

  const loadInspectionRequest = useCallback(
    async (showLoadingState = false) => {
      if (!inspectionRequestId) {
        setInspectionRequest(null);
        setErrorMessage('No inspection request ID was provided.');
        setLoading(false);
        return;
      }
      try {
        if (showLoadingState) {setLoading(true);}
        setErrorMessage('');
        const request = await getAssignedInspectionRequestById(inspectionRequestId);
        if (!request) {
          setInspectionRequest(null);
          setErrorMessage('This inspection request was not found in your assigned list.');
          return;
        }
        setInspectionRequest(request);
        if (request.completion_report) {
          setShowCompletionReportForm(true);
        }
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
    if (!inspectionRequest || actionLoading) {
      return;
    }

    if (!isScheduledForToday(inspectionRequest.date_needed)) {
      Alert.alert(
        'Status update unavailable',
        'Task status can only be updated for tasks scheduled today.',
      );
      return;
    }

    try {
      setActionLoading(true);
      const updated = await updateInspectionRequestStatus(
        inspectionRequest.id,
        nextStatus,
      );

      const nextRequest =
        updated?.id !== undefined
          ? updated
          : {
              ...inspectionRequest,
              status: nextStatus,
            };

      setInspectionRequest(nextRequest);
      if (nextStatus !== 'in_progress') {
        setShowCompletionReportForm(false);
      }
      navigation.replace(route.name, {
        inspectionRequestId: nextRequest.id,
        initialInspectionRequest: nextRequest,
      });
      Alert.alert('Success', successMessage);
    } catch (error) {
      Alert.alert(
        'Update failed',
        error instanceof ApiError ? error.message : 'Could not update status.',
      );
    } finally {
      setActionLoading(false);
    }
  };

  const handleCompletionReportSubmit = async (payload: {
    report_text: string;
    completion_photos?: Array<{uri: string; type: string; name: string | null}>;
    completed_at: string;
  }) => {
    if (!inspectionRequest || actionLoading) {
      return;
    }

    if (!payload.completion_photos || payload.completion_photos.length === 0) {
      Alert.alert(
        'Submission failed',
        'At least one completion photo is required.',
      );
      return;
    }

    if (!inspectionRequest.has_final_quotation) {
      Alert.alert(
        'Quotation required',
        'Create the inspection-based quotation before notifying admin that this inspection is done.',
      );
      return;
    }

    try {
      setActionLoading(true);
      const updated = await submitInspectionCompletionReport(
        inspectionRequest.id,
        {
          report_text: payload.report_text,
          completion_photos: payload.completion_photos,
          completed_at: payload.completed_at,
        },
      );

      const nextRequest =
        updated?.id !== undefined
          ? updated
          : {
              ...inspectionRequest,
            };

      setInspectionRequest(nextRequest);
      setShowCompletionReportForm(true);
      navigation.replace(route.name, {
        inspectionRequestId: nextRequest.id,
        initialInspectionRequest: nextRequest,
      });
      Alert.alert(
        'Report submitted',
        'Completion report submitted. Waiting for admin review.',
      );
    } catch (error) {
        Alert.alert(
          'Submission failed',
          error instanceof ApiError
            ? error.message
          : 'Could not submit the completion report.',
        );
    } finally {
      setActionLoading(false);
    }
  };

  const timelineEvents = useMemo(() => {
    if (!inspectionRequest) {
      return [];
    }

    const events: Array<{
      datetime: string;
      status: string;
      description: string;
    }> = [];

    if (inspectionRequest.created_at) {
      events.push({
        datetime: formatDateTime(inspectionRequest.created_at),
        status: 'pending',
        description: 'Request submitted',
      });
    }

    const normalizedStatus = normalizeRequestStatus(inspectionRequest.status);

    if (['assigned', 'in_progress', 'completed'].includes(normalizedStatus)) {
      events.push({
        datetime: formatDateTime(inspectionRequest.updated_at),
        status: 'assigned',
        description: 'Assigned to technician',
      });
    }

    if (['in_progress', 'completed'].includes(normalizedStatus)) {
      events.push({
        datetime: formatDateTime(inspectionRequest.updated_at),
        status: 'in_progress',
        description: 'Inspection started',
      });
    }

    if (inspectionRequest.completion_report?.submitted_at) {
      events.push({
        datetime: formatDateTime(inspectionRequest.completion_report.submitted_at),
        status: 'in_progress',
        description: 'Completion report submitted',
      });
    }

    if (
      normalizeRequestStatus(inspectionRequest.completion_report?.status) ===
      'approved'
    ) {
      events.push({
        datetime: formatDateTime(inspectionRequest.completion_report?.approved_at),
        status: 'approved',
        description: 'Completion report approved',
      });
    }

    if (normalizedStatus === 'completed') {
      events.push({
        datetime: formatDateTime(inspectionRequest.updated_at),
        status: 'completed',
        description: 'Inspection completed',
      });
    }

    return events;
  }, [inspectionRequest]);

  // ── loading state ─────────────────────────────────────────────────────────
  if (loading) {
    return (
      <View style={s.root}>
        <SafeAreaView style={s.safe}>
          <ScreenHeader onBack={() => navigation.goBack()} />
          <View style={s.centered}>
            <ActivityIndicator size="large" color={NAVY} />
            <Text style={s.loadingText}>Loading inspection details…</Text>
          </View>
        </SafeAreaView>
        <TechnicianBottomNav activeTab="Inspections" />
      </View>
    );
  }

  // ── error state ───────────────────────────────────────────────────────────
  if (errorMessage || !inspectionRequest) {
    return (
      <View style={s.root}>
        <SafeAreaView style={s.safe}>
          <ScreenHeader onBack={() => navigation.goBack()} />
          <View style={s.centered}>
            <Text style={s.errorTitle}>Details unavailable</Text>
            <Text style={s.errorText}>
              {errorMessage || 'No inspection request details were found.'}
            </Text>
            <Pressable
              style={s.retryBtn}
              onPress={() => loadInspectionRequest(true)}>
              <Text style={s.retryBtnText}>Try Again</Text>
            </Pressable>
            <Pressable
              style={[s.retryBtn, s.retryBtnOutline]}
              onPress={() => navigation.goBack()}>
              <Text style={[s.retryBtnText, {color: NAVY}]}>Go Back</Text>
            </Pressable>
          </View>
        </SafeAreaView>
        <TechnicianBottomNav activeTab="Inspections" />
      </View>
    );
  }

  const canCreateQuote = canCreateFinalQuotation(inspectionRequest.status);
  const hasFinalQuotation = !!inspectionRequest.has_final_quotation;
  const normalizedStatus = normalizeRequestStatus(inspectionRequest.status);
  const canNotifyAdminDone =
    hasFinalQuotation &&
    normalizedStatus === 'in_progress' &&
    !inspectionRequest.completion_report &&
    !showCompletionReportForm;
  const pendingAdminReview =
    !!inspectionRequest.completion_report &&
    normalizeRequestStatus(inspectionRequest.completion_report.status) !==
      'approved' &&
    normalizedStatus !== 'completed';
  const displayStatusLabel = pendingAdminReview
    ? 'Pending Admin Review'
    : formatInspectionStatus(inspectionRequest.status);
  const displayStatusColors = pendingAdminReview
    ? {
        backgroundColor: '#FFF7CC',
        textColor: '#92400e',
      }
    : null;
  const canUpdateStatusToday = isScheduledForToday(inspectionRequest.date_needed);
  const availableActions = canUpdateStatusToday
    ? STATUS_OPTIONS.filter(option =>
        option.currentStatuses.includes(normalizedStatus),
      )
    : [];

  return (
    <View style={s.root}>
      <SafeAreaView style={s.safe}>
        {/* ── custom header ── */}
        <ScreenHeader onBack={() => navigation.goBack()} />

        <ScrollView
          contentContainerStyle={s.scroll}
          showsVerticalScrollIndicator={false}>

          {/* ── status badge (top-right) ── */}
          <View style={s.badgeRow}>
            <StatusBadge
              status={inspectionRequest.status}
              label={displayStatusLabel}
              colors={displayStatusColors}
            />
          </View>

          {/* ── Customer Information ── */}
          <View style={s.card}>
            <Text style={s.cardTitle}>Customer Information</Text>
            <InfoRow label="Name"       value={getCustomerName(inspectionRequest)} />
            <InfoRow label="Contact No." value={inspectionRequest.contact_number} />
            <InfoRow
              label="Address"
              value={formatDisplayValue(inspectionRequest.address, 'Not provided')}
            />
          </View>

          {/* ── Request Information ── */}
          <View style={s.card}>
            <Text style={s.cardTitle}>Request Information</Text>
            <InfoRow
              label="Inspection Request ID"
              value={formatIRQId(inspectionRequest.id)}
            />
            <InfoRow
              label="Status"
              value={displayStatusLabel}
            />
            <InfoRow
              label="Schedule Date/Time"
              value={formatSchedule(inspectionRequest.date_needed)}
            />
            <InfoRow
              label="Created At"
              value={formatDateTime(inspectionRequest.created_at)}
            />
          </View>

          {/* ── Notes ── */}
          <View style={s.card}>
            <Text style={s.cardTitle}>Notes</Text>
            <Text style={s.notesText}>
              {formatDisplayValue(
                inspectionRequest.details,
                'No notes provided for this inspection request.',
              )}
            </Text>
          </View>

          <View style={s.card}>
            <Text style={s.cardTitle}>Updates Timeline</Text>
            {timelineEvents.length === 0 ? (
              <Text style={s.emptyText}>No updates yet.</Text>
            ) : (
              timelineEvents.map((event, index) => (
                <TimelineItem
                  key={`${event.status}-${event.datetime}-${index}`}
                  datetime={event.datetime}
                  status={event.status}
                  description={event.description}
                  isLast={index === timelineEvents.length - 1}
                />
              ))
            )}
          </View>

          {!hasFinalQuotation &&
          normalizedStatus === 'in_progress' &&
          !inspectionRequest.completion_report ? (
            <View style={s.card}>
              <Text style={s.cardTitle}>Quotation Required</Text>
              <Text style={s.emptyText}>
                Create the inspection-based quotation first before notifying admin that this inspection is done.
              </Text>
            </View>
          ) : null}

          {canNotifyAdminDone ? (
            <View style={s.actionsBlock}>
              <AppButton
                title="Notify Admin Inspection Done"
                onPress={() => setShowCompletionReportForm(true)}
                disabled={actionLoading}
                style={[s.actionBtn, s.actionBtnPrimary]}
                textStyle={s.actionBtnPrimaryText}
              />
            </View>
          ) : null}

          {showCompletionReportForm || inspectionRequest.completion_report ? (
            <ServiceCompletionReportCard
              title="Completion Notes"
              subtitle="Submit the completion report after finishing the on-site work. Admin approval is required before this request becomes completed."
              report={inspectionRequest.completion_report}
              canSubmit={
                hasFinalQuotation &&
                normalizedStatus === 'in_progress' &&
                !inspectionRequest.completion_report
              }
              blockedSubtitle={
                hasFinalQuotation
                  ? 'Move this task to In Progress before submitting the completion report.'
                  : 'Create the inspection-based quotation before notifying admin that this inspection is done.'
              }
              submitting={actionLoading}
              onSubmit={handleCompletionReportSubmit}
            />
          ) : null}

          {/* ── Action Buttons ── */}
          <View style={s.actionsBlock}>
            {availableActions.map(action => (
              <AppButton
                key={action.value}
                title={actionLoading ? 'Saving...' : action.label}
                disabled={actionLoading}
                onPress={() =>
                  handleStatusUpdate(action.value, action.successMessage)
                }
                style={[s.actionBtn, s.actionBtnPrimary]}
                textStyle={s.actionBtnPrimaryText}
              />
            ))}

            {canCreateQuote ? (
              <AppButton
                title="Create Inspection-Based Quotation"
                onPress={() => {
                  navigation.navigate('FinalQuotationForm', {
                    inspectionRequestId: inspectionRequest.id,
                    inspectionRequest,
                  });
                }}
                style={[s.actionBtn, s.actionBtnPrimary]}
                textStyle={s.actionBtnPrimaryText}
              />
            ) : null}

            <AppButton
              title="Back"
              variant="outline"
              onPress={() => navigation.goBack()}
              style={[s.actionBtn, s.actionBtnSecondary]}
              textStyle={s.actionBtnSecondaryText}
            />
          </View>

        </ScrollView>
      </SafeAreaView>

      {/* ── bottom nav ── */}
      <TechnicianBottomNav activeTab="Inspections" />
    </View>
  );
}

// ─── nav styles ───────────────────────────────────────────────────────────────;

// ─── screen styles ────────────────────────────────────────────────────────────
const s = StyleSheet.create({
  root: {flex: 1, backgroundColor: BG},
  safe: {flex: 1, backgroundColor: BG},

  // header
  headerRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
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
  backArrow: {
    fontSize: 28,
    color: NAVY,
    fontWeight: '600',
    lineHeight: 32,
  },
  headerTitle: {
    fontSize: 18,
    fontWeight: '800',
    color: NAVY,
  },

  // scroll
  scroll: {
    paddingHorizontal: 18,
    paddingBottom: 90,
    paddingTop: 4,
  },

  // status badge row
  badgeRow: {
    alignItems: 'flex-end',
    marginBottom: 12,
  },

  // cards
  card: {
    backgroundColor: CARD,
    borderRadius: 20,
    marginBottom: 14,
    paddingHorizontal: 16,
    paddingTop: 14,
    paddingBottom: 6,
    shadowColor: SHADOW,
    shadowOffset: {width: 0, height: 4},
    shadowOpacity: 0.10,
    shadowRadius: 10,
    elevation: 2,
  },
  cardTitle: {
    fontSize: 16,
    fontWeight: '800',
    color: NAVY,
    marginBottom: 10,
  },

  // info rows
  infoRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingVertical: 10,
    borderTopWidth: 1,
    borderTopColor: '#D4E0F2',
  },
  infoLabel: {
    fontSize: 13,
    color: MUTED,
    flex: 1,
  },
  infoValue: {
    fontSize: 13,
    fontWeight: '700',
    color: NAVY,
    flex: 1,
    textAlign: 'right',
  },

  // notes
  notesText: {
    fontSize: 14,
    color: '#334155',
    lineHeight: 22,
    paddingVertical: 10,
    borderTopWidth: 1,
    borderTopColor: '#D4E0F2',
  },

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
    backgroundColor: BORDER,
    marginTop: 3,
    marginBottom: -6,
  },
  timelineBody: {
    flex: 1,
    paddingBottom: 10,
  },
  timelineDatetime: {
    fontSize: 13,
    color: MUTED,
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

  // action buttons
  actionsBlock: {
    marginBottom: 10,
  },
  actionBtn: {
    marginBottom: 10,
  },
  actionBtnPrimary: {
    backgroundColor: GOLD,
    borderColor: GOLD,
  },
  actionBtnPrimaryText: {
    color: NAVY,
    fontWeight: '800',
  },
  actionBtnSecondary: {
    backgroundColor: CARD,
    borderColor: NAVY,
    borderWidth: 1.5,
  },
  actionBtnSecondaryText: {
    color: NAVY,
    fontWeight: '700',
  },
  btnDisabled: {
    opacity: 0.45,
  },
  pressed: {opacity: 0.80},

  // loading / error
  centered: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: 24,
    paddingBottom: 40,
  },
  loadingText:  {color: MUTED, fontSize: 14, marginTop: 10},
  errorTitle:   {color: NAVY, fontSize: 20, fontWeight: '800', marginBottom: 8, textAlign: 'center'},
  errorText:    {color: '#b91c1c', fontSize: 14, lineHeight: 20, textAlign: 'center', marginBottom: 16},
  retryBtn: {
    backgroundColor: NAVY,
    borderRadius: 12,
    paddingVertical: 12,
    paddingHorizontal: 28,
    marginBottom: 8,
  },
  retryBtnOutline: {
    backgroundColor: 'transparent',
    borderWidth: 1.5,
    borderColor: NAVY,
  },
  retryBtnText: {color: CARD, fontSize: 14, fontWeight: '700'},
});
