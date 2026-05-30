import React, {useCallback, useContext, useMemo, useState} from 'react';
import {
  ActivityIndicator,
  Alert,
  Modal,
  Pressable,
  SafeAreaView,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  View,
} from 'react-native';
import {useFocusEffect} from '@react-navigation/native';

import {StatusBadge} from '../components';
import {ApiError} from '../src/services/api';
import {
  cancelInspectionRequestByCustomer,
  getInspectionRequestById,
  InspectionRequest,
} from '../src/services/inspectionRequestApi';
import {AuthContext} from '../src/context/AuthContext';
import {getSolmateStatusColors, solmateColors} from '../src/theme/colors';

const NAVY = solmateColors.navy;
const GOLD = solmateColors.primary;
const MUTED = solmateColors.muted;
const BG = solmateColors.background;
const CARD = solmateColors.white;
const DIVIDER = solmateColors.border;

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

function formatInspectionStatus(status?: string | null) {
  switch ((status || '').toLowerCase()) {
    case 'pending':
      return 'Pending';
    case 'assigned':
      return 'Assigned';
    case 'in_progress':
      return 'In Progress';
    case 'completed':
      return 'Completed';
    case 'cancelled':
      return 'Cancelled';
    case 'declined':
      return 'Declined';
    default:
      return 'Pending';
  }
}

function getFriendlyErrorMessage(error: unknown) {
  if (error instanceof ApiError) return error.message;
  return 'Could not load the inspection request details.';
}

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
          <View style={[s.timelineBadge, {backgroundColor: colors.backgroundColor}]}>
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

export default function InspectionRequestDetailScreen({navigation, route}: any) {
  const inspectionRequestId = route?.params?.inspectionRequestId;
  const initialInspectionRequest = route?.params?.initialInspectionRequest as
    | InspectionRequest
    | undefined;

  const {user, setUser} = useContext(AuthContext) as any;

  const [inspectionRequest, setInspectionRequest] =
    useState<InspectionRequest | null>(initialInspectionRequest || null);
  const [loading, setLoading] = useState(!initialInspectionRequest);
  const [errorMessage, setErrorMessage] = useState('');
  const [actionLoading, setActionLoading] = useState(false);
  const [showCancelModal, setShowCancelModal] = useState(false);
  const [cancellationNote, setCancellationNote] = useState('');

  const customerCanCancel =
    !['completed', 'cancelled', 'declined'].includes(
      (inspectionRequest?.status || '').toLowerCase(),
    ) && inspectionRequest?.cancellation_note == null;

  const loadInspectionRequest = useCallback(
    async (showLoadingState = false) => {
      if (!inspectionRequestId) {
        setInspectionRequest(null);
        setErrorMessage('No inspection request ID was provided.');
        setLoading(false);
        return;
      }

      try {
        if (showLoadingState) setLoading(true);
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

  const handleCustomerCancellation = async () => {
    if (!inspectionRequest || actionLoading) {
      return;
    }

    const trimmedNote = cancellationNote.trim();
    if (trimmedNote.length < 5) {
      Alert.alert(
        'Notes required',
        'Please provide at least 5 characters to explain why you want to cancel this request.',
      );
      return;
    }

    try {
      setActionLoading(true);
      const response = await cancelInspectionRequestByCustomer(
        inspectionRequest.id,
        trimmedNote,
      );

      const nextRequest =
        response?.inspection_request?.id !== undefined
          ? response.inspection_request
          : {
              ...inspectionRequest,
              cancellation_note: trimmedNote,
            };

      setInspectionRequest(nextRequest);
      setShowCancelModal(false);
      setCancellationNote('');

      if (response?.cancellation_count !== undefined) {
        setUser((prev: any) => ({
          ...prev,
          cancellation_count: response.cancellation_count,
        }));
      }

      navigation.replace(route.name, {
        inspectionRequestId: nextRequest.id,
        initialInspectionRequest: nextRequest,
      });

      if (response?.account_archived) {
        Alert.alert(
          'Account Locked',
          'Your account has been locked due to repeated cancellations. Please contact admin to regain access.',
        );
      } else {
        Alert.alert(
          'Cancellation submitted',
          'Your inspection request cancellation was submitted and the admin has been notified.',
        );
      }
    } catch (error) {
      if (error instanceof ApiError) {
        Alert.alert('Cancellation failed', error.message);
      } else {
        Alert.alert(
          'Cancellation failed',
          'Could not submit your cancellation request right now.',
        );
      }
    } finally {
      setActionLoading(false);
    }
  };

  const openCancelModal = () => {
    const currentCount = (user?.cancellation_count ?? 0) as number;
    if (currentCount >= 2) {
      Alert.alert(
        'Final Cancellation Warning',
        'This is your 3rd cancellation. Proceeding will permanently lock your account and you will need to contact admin to regain access. Are you sure you want to continue?',
        [
          {text: 'Go Back', style: 'cancel'},
          {
            text: 'Proceed Anyway',
            style: 'destructive',
            onPress: () => setShowCancelModal(true),
          },
        ],
      );
    } else {
      setShowCancelModal(true);
    }
  };

  if (loading) {
    return (
      <SafeAreaView style={s.safe}>
        <View style={s.centered}>
          <ActivityIndicator size="large" color={GOLD} />
          <Text style={s.loadingText}>Loading inspection request...</Text>
        </View>
      </SafeAreaView>
    );
  }

  if (errorMessage || !inspectionRequest) {
    return (
      <SafeAreaView style={s.safe}>
        <View style={s.centered}>
          <Text style={s.errorTitle}>Inspection request unavailable</Text>
          <Text style={s.errorText}>
            {errorMessage || 'No inspection request details were found.'}
          </Text>
          <Pressable
            onPress={() => loadInspectionRequest(true)}
            style={({pressed}) => [s.goldBtn, pressed && s.pressed]}>
            <Text style={s.goldBtnText}>Try Again</Text>
          </Pressable>
          <Pressable
            onPress={() => navigation.navigate('InspectionRequestList')}
            style={({pressed}) => [s.outlineBtn, pressed && s.pressed]}>
            <Text style={s.outlineBtnText}>Back to Requests</Text>
          </Pressable>
        </View>
      </SafeAreaView>
    );
  }

  const timelineEvents = useMemo(() => {
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

    const normalizedStatus = (inspectionRequest.status || '').toLowerCase();

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

    if ((inspectionRequest.completion_report?.status || '').toLowerCase() === 'approved') {
      events.push({
        datetime: formatDateTime(inspectionRequest.completion_report.approved_at),
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

  return (
    <SafeAreaView style={s.safe}>
      <ScrollView contentContainerStyle={s.scroll} showsVerticalScrollIndicator={false}>
        <Text style={s.brand}>
          Sol<Text style={s.brandAccent}>Mate</Text>
        </Text>

        <Pressable
          hitSlop={14}
          onPress={() => navigation.goBack()}
          style={({pressed}) => [s.backBtn, pressed && s.pressed]}>
          <Text style={s.backIcon}>{'‹'}</Text>
        </Pressable>

        <Text style={s.title}>Inspection Details</Text>
        <Text style={s.subtitle}>
          Review the request details, current progress, and technician assignment for this inspection.
        </Text>

        <View style={s.badgeRow}>
          <View style={s.typeBadge}>
            <Text style={s.typeBadgeText}>Inspection</Text>
          </View>
          <StatusBadge status={inspectionRequest.status} />
        </View>

        <View style={s.card}>
          <Text style={s.cardTitle}>Inspection Information</Text>
          <DetailRow label="Inspection Request ID" value={`IR-${inspectionRequest.id}`} bold />
          <DetailRow label="Status" value={formatInspectionStatus(inspectionRequest.status)} bold />
          <DetailRow label="Created At" value={formatDateTime(inspectionRequest.created_at)} />
          <DetailRow label="Schedule Date" value={formatDate(inspectionRequest.date_needed)} />
          <DetailRow label="Technician Assigned" value={inspectionRequest.technician?.name || 'Pending assignment'} bold />
        </View>

        <View style={s.card}>
          <Text style={s.cardTitle}>Request Details</Text>
          <DetailRow label="Contact Number" value={inspectionRequest.contact_number || 'Not provided'} />
          <DetailRow label="Address" value={inspectionRequest.address || 'Not provided'} />
          <DetailRow label="Address Additional Details" value={inspectionRequest.address_details || 'Not provided'} />

          <View style={s.descBlock}>
            <Text style={s.descLabel}>Problem Description</Text>
            <Text style={s.descText}>{inspectionRequest.details}</Text>
          </View>
        </View>

        <View style={s.card}>
          <Text style={s.cardTitle}>Updates Timeline</Text>
          {timelineEvents.length === 0 ? (
            <Text style={s.cardSubtitle}>No updates yet.</Text>
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

        <Pressable
          onPress={() => navigation.goBack()}
          style={({pressed}) => [s.outlineBtn, pressed && s.pressed]}>
          <Text style={s.outlineBtnText}>Back</Text>
        </Pressable>

        {customerCanCancel ? (
          <Pressable
            onPress={openCancelModal}
            disabled={actionLoading}
            style={({pressed}) => [
              s.cancelBtn,
              (pressed || actionLoading) && s.pressed,
            ]}>
            <Text style={s.cancelBtnText}>
              {actionLoading ? 'Submitting...' : 'Cancel Inspection Request'}
            </Text>
          </Pressable>
        ) : null}

        <View style={s.spacer} />
      </ScrollView>

      <Modal
        visible={showCancelModal}
        transparent
        animationType="fade"
        onRequestClose={() => {
          if (!actionLoading) {
            setShowCancelModal(false);
          }
        }}>
        <View style={s.modalBackdrop}>
          <View style={s.modalCard}>
            <Text style={s.modalTitle}>Cancel Inspection Request</Text>
            <Text style={s.modalSubtitle}>
              Tell us why you want to cancel. Admin will review this note.
            </Text>
            <TextInput
              value={cancellationNote}
              onChangeText={setCancellationNote}
              editable={!actionLoading}
              placeholder="Please enter your reason for cancellation"
              placeholderTextColor={MUTED}
              multiline
              numberOfLines={4}
              textAlignVertical="top"
              style={s.modalInput}
              maxLength={1000}
            />
            <View style={s.modalActions}>
              <Pressable
                onPress={() => setShowCancelModal(false)}
                disabled={actionLoading}
                style={({pressed}) => [
                  s.modalSecondaryBtn,
                  pressed && !actionLoading && s.pressed,
                ]}>
                <Text style={s.modalSecondaryBtnText}>Close</Text>
              </Pressable>
              <Pressable
                onPress={handleCustomerCancellation}
                disabled={actionLoading}
                style={({pressed}) => [
                  s.modalPrimaryBtn,
                  (pressed || actionLoading) && s.pressed,
                ]}>
                <Text style={s.modalPrimaryBtnText}>
                  {actionLoading ? 'Submitting...' : 'Submit'}
                </Text>
              </Pressable>
            </View>
          </View>
        </View>
      </Modal>
    </SafeAreaView>
  );
}

const s = StyleSheet.create({
  safe: {flex: 1, backgroundColor: BG},
  scroll: {paddingHorizontal: 22, paddingTop: 20, paddingBottom: 30},
  pressed: {opacity: 0.85},

  brand: {fontSize: 22, fontWeight: '800', color: NAVY, marginBottom: 10},
  brandAccent: {color: GOLD},

  backBtn: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: CARD,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 18,
    shadowColor: solmateColors.shadow,
    shadowOffset: {width: 0, height: 2},
    shadowOpacity: 0.1,
    shadowRadius: 6,
    elevation: 3,
  },
  backIcon: {fontSize: 28, color: NAVY, fontWeight: '600', marginTop: -2},

  title: {fontSize: 26, fontWeight: '900', color: NAVY, marginBottom: 4},
  subtitle: {fontSize: 14, color: MUTED, lineHeight: 20, marginBottom: 18},

  centered: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    padding: 24,
  },
  loadingText: {color: MUTED, fontSize: 14, marginTop: 14},

  errorTitle: {
    color: NAVY,
    fontSize: 22,
    fontWeight: '800',
    marginBottom: 8,
    textAlign: 'center',
  },
  errorText: {
    color: solmateColors.danger,
    fontSize: 14,
    lineHeight: 20,
    textAlign: 'center',
    marginBottom: 16,
  },

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

  descBlock: {paddingTop: 14, borderTopColor: DIVIDER, borderTopWidth: 1},
  descLabel: {
    color: MUTED,
    fontSize: 12,
    fontWeight: '700',
    textTransform: 'uppercase',
    marginBottom: 8,
  },
  descText: {color: NAVY, fontSize: 14, lineHeight: 22, opacity: 0.85},

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

  goldBtn: {
    backgroundColor: GOLD,
    borderRadius: 28,
    paddingVertical: 14,
    alignItems: 'center',
    marginTop: 4,
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
  disabledBtn: {
    backgroundColor: DIVIDER,
    borderRadius: 28,
    paddingVertical: 14,
    alignItems: 'center',
    marginTop: 4,
    opacity: 0.6,
  },
  disabledBtnText: {fontSize: 15, fontWeight: '800', color: MUTED},

  spacer: {minHeight: 20},

  cancelBtn: {
    backgroundColor: solmateColors.dangerSoft,
    borderRadius: 28,
    paddingVertical: 14,
    alignItems: 'center',
    marginTop: 12,
    borderWidth: 1,
    borderColor: solmateColors.danger,
  },
  cancelBtnText: {fontSize: 15, fontWeight: '800', color: solmateColors.danger},

  modalBackdrop: {
    flex: 1,
    backgroundColor: 'rgba(0,0,0,0.45)',
    justifyContent: 'center',
    alignItems: 'center',
    padding: 24,
  },
  modalCard: {
    backgroundColor: CARD,
    borderRadius: 22,
    padding: 24,
    width: '100%',
    maxWidth: 420,
    shadowColor: '#000',
    shadowOffset: {width: 0, height: 8},
    shadowOpacity: 0.18,
    shadowRadius: 18,
    elevation: 10,
  },
  modalTitle: {fontSize: 20, fontWeight: '900', color: NAVY, marginBottom: 8},
  modalSubtitle: {fontSize: 14, color: MUTED, lineHeight: 20, marginBottom: 16},
  modalInput: {
    borderWidth: 1.5,
    borderColor: solmateColors.border,
    borderRadius: 14,
    padding: 12,
    fontSize: 14,
    color: NAVY,
    minHeight: 100,
    backgroundColor: BG,
    marginBottom: 16,
  },
  modalActions: {
    flexDirection: 'row',
    justifyContent: 'flex-end',
    gap: 10,
  },
  modalSecondaryBtn: {
    paddingHorizontal: 18,
    paddingVertical: 11,
    borderRadius: 22,
    borderWidth: 1.5,
    borderColor: solmateColors.border,
  },
  modalSecondaryBtnText: {fontSize: 14, fontWeight: '700', color: NAVY},
  modalPrimaryBtn: {
    paddingHorizontal: 22,
    paddingVertical: 11,
    borderRadius: 22,
    backgroundColor: solmateColors.danger,
  },
  modalPrimaryBtnText: {fontSize: 14, fontWeight: '800', color: CARD},
});
