import React, {useCallback, useState} from 'react';
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

import {CompletionReportCard, StatusBadge} from '../components';
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
  formatDateTime,
  getCustomerName,
} from '../src/utils/technicianRequests';

// ─── colour tokens ────────────────────────────────────────────────────────────
const NAVY   = '#123A5A';
const GOLD   = '#F4D000';
const BG     = '#F8FAFC';
const CARD   = '#ffffff';
const MUTED  = '#5E7288';
const SHADOW = '#8a9bbd';
const BORDER = '#DDE7EE';
const SOFT_YELLOW = '#FFF7CC';

// ─── helpers ─────────────────────────────────────────────────────────────────
function getFriendlyErrorMessage(error: unknown) {
  if (error instanceof ApiError) {return error.message;}
  return 'Could not load the inspection request details.';
}

function formatIRQId(id: number) {
  return `IRQ-${String(id).padStart(4, '0')}`;
}

function formatSchedule(dateNeeded?: string | null) {
  if (!dateNeeded) {return 'Not specified';}
  const d = new Date(dateNeeded);
  if (isNaN(d.getTime())) {return dateNeeded;}
  return (
    d.toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric'}) +
    ' • ' +
    d.toLocaleTimeString('en-US', {hour: 'numeric', minute: '2-digit'})
  );
}

// ─── InfoRow ──────────────────────────────────────────────────────────────────
function InfoRow({label, value}: {label: string; value?: string | null}) {
  return (
    <View style={s.infoRow}>
      <Text style={s.infoLabel}>{label}</Text>
      <Text style={s.infoValue}>{value || 'Not available'}</Text>
    </View>
  );
}

// ─── bottom nav icons ─────────────────────────────────────────────────────────
type Tab = 'Home' | 'Inspections' | 'Services' | 'Profile';

function HomeIcon({active}: {active?: boolean}) {
  const c = active ? NAVY : MUTED;
  return (
    <Text style={{fontSize: 20, color: c, lineHeight: 22, textAlign: 'center'}}>{'\u2302'}</Text>
  );
}
function InspectIcon({active}: {active?: boolean}) {
  const c = active ? NAVY : MUTED;
  return (
    <View style={nav.iconWrap}>
      <View style={[nav.listBox, {backgroundColor: c}]}>
        <View style={nav.listLine} />
        <View style={[nav.listLine, {width: 12}]} />
        <View style={nav.listLine} />
      </View>
    </View>
  );
}
function ServicesIcon({active}: {active?: boolean}) {
  const c = active ? NAVY : MUTED;
  return (
    <View style={nav.iconWrap}>
      <View style={[nav.gear, {borderColor: c}]}>
        <View style={[nav.gearInner, {backgroundColor: c}]} />
      </View>
    </View>
  );
}
function ProfileIcon({active}: {active?: boolean}) {
  const c = active ? NAVY : MUTED;
  return (
    <View style={nav.iconWrap}>
      <View style={[nav.profileHead, {backgroundColor: c}]} />
      <View style={[nav.profileBody, {backgroundColor: c}]} />
    </View>
  );
}
function BottomNav({onPress, activeTab}: {onPress: (t: Tab) => void; activeTab: Tab}) {
  const tabs: {key: Tab; label: string; Icon: React.FC<{active?: boolean}>}[] = [
    {key: 'Home',        label: 'Home',        Icon: HomeIcon},
    {key: 'Inspections', label: 'Inspections', Icon: InspectIcon},
    {key: 'Services',    label: 'Services',    Icon: ServicesIcon},
    {key: 'Profile',     label: 'Profile',     Icon: ProfileIcon},
  ];
  return (
    <View style={nav.bar}>
      {tabs.map(({key, label, Icon}) => (
        <Pressable key={key} style={[nav.tab, key === activeTab && nav.tabActive]} onPress={() => onPress(key)}>
          <Icon active={key === activeTab} />
          <Text style={[nav.label, key === activeTab && nav.labelActive]}>
            {label}
          </Text>
        </Pressable>
      ))}
    </View>
  );
}

// ─── status options ───────────────────────────────────────────────────────────
const STATUS_OPTIONS: {label: string; value: TechnicianUpdatableStatus}[] = [
  {label: 'Assigned',    value: 'assigned'},
  {label: 'In Progress', value: 'in_progress'},
];

// ─── shared header skeleton ───────────────────────────────────────────────────
function ScreenHeader({onBack}: {onBack: () => void}) {
  return (
    <View style={s.headerRow}>
      <Pressable style={s.backBtn} onPress={onBack} hitSlop={10}>
        <Text style={s.backArrow}>‹</Text>
      </Pressable>
      <Text style={s.headerTitle}>Inspection Details</Text>
      {/* spacer keeps title centered */}
      <View style={s.backBtn} />
    </View>
  );
}

// ─── main screen ──────────────────────────────────────────────────────────────
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
  const [selectedStatus, setSelectedStatus] =
    useState<TechnicianUpdatableStatus | null>(null);

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

  // Save = apply the selected status pill (if changed), then stay on screen
  const handleSave = async () => {
    if (!inspectionRequest) {navigation.goBack(); return;}
    const statusToSave = selectedStatus;
    if (!statusToSave || statusToSave === inspectionRequest.status) {
      Alert.alert('No changes', 'Select a new status before saving.');
      return;
    }
    try {
      setActionLoading(true);
      const updated = await updateInspectionRequestStatus(
        inspectionRequest.id,
        statusToSave,
      );
      setInspectionRequest(updated);
      if (statusToSave !== 'in_progress') {
        setShowCompletionReportForm(false);
      }
      setSelectedStatus(null);
      Alert.alert('Saved', 'Status updated successfully.');
    } catch (error) {
      Alert.alert(
        'Save failed',
        error instanceof ApiError ? error.message : 'Could not update status.',
      );
    } finally {
      setActionLoading(false);
    }
  };

  const handleCompletionReportSubmit = async (payload: {
    report_text: string;
    findings?: string;
    recommendations?: string;
    completed_at: string;
  }) => {
    if (!inspectionRequest || actionLoading) {
      return;
    }

    try {
      setActionLoading(true);
      const updated = await submitInspectionCompletionReport(
        inspectionRequest.id,
        payload,
      );
      setInspectionRequest(updated);
      setShowCompletionReportForm(true);
      Alert.alert(
        'Report submitted',
        'Completion report submitted. Waiting for admin review.',
      );
    } catch (error) {
        Alert.alert(
          'Submission failed',
          error instanceof ApiError
            ? error.message
          : 'Could not submit the completion notes.',
        );
    } finally {
      setActionLoading(false);
    }
  };

  function handleTabPress(tab: Tab) {
    if (tab === 'Home')        {navigation.navigate('TechnicianDashboard');}
    if (tab === 'Inspections') {navigation.navigate('AssignedInspectionRequests');}
    if (tab === 'Services')    {navigation.navigate('TechnicianServiceRequests');}
    if (tab === 'Profile')     {navigation.navigate('TechnicianSettings');}
  }

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
        <BottomNav onPress={handleTabPress} activeTab="Inspections" />
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
        <BottomNav onPress={handleTabPress} activeTab="Inspections" />
      </View>
    );
  }

  const activeStatus = selectedStatus ?? (inspectionRequest.status as TechnicianUpdatableStatus);
  const canCreateQuote = canCreateFinalQuotation(inspectionRequest.status);
  const pendingAdminReview =
    !!inspectionRequest.completion_report &&
    (inspectionRequest.completion_report.status || '').toLowerCase() !==
      'approved' &&
    (inspectionRequest.status || '').toLowerCase() !== 'completed';
  const displayStatusLabel = pendingAdminReview
    ? 'Pending Admin Review'
    : ((inspectionRequest.status || '').charAt(0).toUpperCase() +
        (inspectionRequest.status || '').slice(1).replace(/_/g, ' '));
  const displayStatusColors = pendingAdminReview
    ? {
        backgroundColor: '#FFF7CC',
        textColor: '#92400e',
      }
    : null;

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
            <InfoRow label="Address"    value={inspectionRequest.address || 'Not provided'} />
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
              {inspectionRequest.details ||
                'No notes provided for this inspection request.'}
            </Text>
          </View>

          {/* ── Update Status ── */}
          <View style={s.card}>
            <Text style={s.cardTitle}>Update Status</Text>
            <View style={s.statusRow}>
              {STATUS_OPTIONS.map(opt => {
                const isActive = activeStatus === opt.value;
                return (
                  <Pressable
                    key={opt.value}
                    style={[s.statusPill, isActive && s.statusPillActive]}
                    onPress={() => setSelectedStatus(opt.value)}
                    disabled={actionLoading}>
                    <Text
                      style={[s.statusPillText, isActive && s.statusPillTextActive]}>
                      {opt.label}
                    </Text>
                  </Pressable>
                );
              })}
            </View>
          </View>

          {(inspectionRequest.status || '').toLowerCase() === 'in_progress' &&
          !inspectionRequest.completion_report &&
          !showCompletionReportForm ? (
            <Pressable
              style={({pressed}) => [s.btnSecondary, pressed && s.pressed]}
              onPress={() => setShowCompletionReportForm(true)}
              disabled={actionLoading}>
              <Text style={s.btnSecondaryText}>Notify Admin Inspection Done</Text>
            </Pressable>
          ) : null}

          {showCompletionReportForm || inspectionRequest.completion_report ? (
            <CompletionReportCard
              title="Inspection Completion Notes"
              subtitle="Submit the technician completion notes after the site inspection is finished. Admin approval is required before this inspection becomes completed."
              report={inspectionRequest.completion_report}
              canSubmit={
                (inspectionRequest.status || '').toLowerCase() === 'in_progress' &&
                !inspectionRequest.completion_report
              }
              submitting={actionLoading}
              onSubmit={handleCompletionReportSubmit}
            />
          ) : null}

          {/* ── Action Buttons ── */}
          {canCreateQuote ? (
            <Pressable
              style={({pressed}) => [s.btnPrimary, pressed && s.pressed]}
              onPress={() => {
                navigation.navigate('FinalQuotationForm', {
                  inspectionRequestId: inspectionRequest.id,
                  inspectionRequest,
                });
              }}>
              <Text style={s.btnPrimaryText}>Create Inspection-Based Quotation</Text>
            </Pressable>
          ) : null}

          <Pressable
            style={({pressed}) => [
              s.btnSecondary,
              actionLoading && s.btnDisabled,
              pressed && s.pressed,
            ]}
            onPress={handleSave}
            disabled={actionLoading}>
            <Text style={s.btnSecondaryText}>
              {actionLoading ? 'Saving…' : 'Save'}
            </Text>
          </Pressable>

        </ScrollView>
      </SafeAreaView>

      {/* ── bottom nav ── */}
      <BottomNav onPress={handleTabPress} activeTab="Inspections" />
    </View>
  );
}

// ─── nav styles ───────────────────────────────────────────────────────────────
const nav = StyleSheet.create({
  bar: {
    flexDirection: 'row',
    backgroundColor: CARD,
    borderTopWidth: 1,
    borderTopColor: BORDER,
    paddingBottom: 8,
    paddingTop: 8,
    paddingHorizontal: 6,
  },
  tab:        {flex: 1, alignItems: 'center', justifyContent: 'center', gap: 3, paddingVertical: 8, borderRadius: 16},
  tabActive:  {backgroundColor: SOFT_YELLOW, borderWidth: 1, borderColor: 'rgba(244, 208, 0, 0.34)'},
  label:      {fontSize: 10, color: MUTED, fontWeight: '500'},
  labelActive:{color: NAVY, fontWeight: '700'},
  iconWrap:   {width: 24, height: 22, alignItems: 'center', justifyContent: 'flex-end'},
  roof: {
    width: 0, height: 0,
    borderLeftWidth: 8, borderRightWidth: 8, borderBottomWidth: 10,
    borderLeftColor: 'transparent', borderRightColor: 'transparent',
    borderBottomColor: MUTED,
  },
  houseBody:   {width: 14, height: 9, borderRadius: 1},
  listBox: {
    width: 18, height: 20, borderRadius: 3,
    alignItems: 'flex-start', justifyContent: 'center',
    paddingHorizontal: 3, gap: 3,
  },
  listLine:    {height: 2, width: 10, backgroundColor: CARD, borderRadius: 1},
  gear: {
    width: 20, height: 20, borderRadius: 10,
    borderWidth: 3, alignItems: 'center', justifyContent: 'center',
  },
  gearInner:   {width: 8, height: 8, borderRadius: 4},
  profileHead: {width: 10, height: 10, borderRadius: 5, marginBottom: 2},
  profileBody: {width: 16, height: 8, borderTopLeftRadius: 8, borderTopRightRadius: 8},
});

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
    paddingBottom: 28,
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
    borderTopColor: '#DDE7EE',
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
    borderTopColor: '#DDE7EE',
  },

  // status pills
  statusRow: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 8,
    paddingVertical: 10,
    borderTopWidth: 1,
    borderTopColor: '#DDE7EE',
  },
  statusPill: {
    borderRadius: 999,
    borderWidth: 1,
    borderColor: '#b8c4d8',
    paddingVertical: 6,
    paddingHorizontal: 16,
    backgroundColor: CARD,
  },
  statusPillActive: {
    backgroundColor: NAVY,
    borderColor: NAVY,
  },
  statusPillText:       {fontSize: 13, color: NAVY, fontWeight: '600'},
  statusPillTextActive: {color: CARD},

  // action buttons
  btnPrimary: {
    backgroundColor: GOLD,
    borderRadius: 14,
    paddingVertical: 16,
    alignItems: 'center',
    marginBottom: 10,
    shadowColor: GOLD,
    shadowOffset: {width: 0, height: 4},
    shadowOpacity: 0.30,
    shadowRadius: 8,
    elevation: 3,
  },
  btnPrimaryText: {
    color: CARD,
    fontSize: 16,
    fontWeight: '800',
  },
  btnSecondary: {
    backgroundColor: CARD,
    borderRadius: 14,
    borderWidth: 1.5,
    borderColor: '#b8c4d8',
    paddingVertical: 14,
    alignItems: 'center',
    marginBottom: 10,
  },
  btnSecondaryText: {
    color: NAVY,
    fontSize: 15,
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
