import React, {useCallback, useState} from 'react';
import {
  ActivityIndicator,
  FlatList,
  Pressable,
  RefreshControl,
  SafeAreaView,
  StyleSheet,
  Text,
  View,
} from 'react-native';
import {useFocusEffect} from '@react-navigation/native';

import {AppButton} from '../components';
import StatusBadge from '../components/StatusBadge';
import {ApiError} from '../src/services/api';
import {
  getAssignedInspectionRequests,
  TechnicianInspectionRequest,
} from '../src/services/technicianApi';
import {formatDate} from '../src/utils/technicianRequests';

// ─── colour tokens (match dashboard) ─────────────────────────────────────────
const NAVY   = '#152a4a';
const GOLD   = '#d4a017';
const BG     = '#dde5f4';
const CARD   = '#ffffff';
const MUTED  = '#64748b';
const SHADOW = '#8a9bbd';

// ─── filter chips ─────────────────────────────────────────────────────────────
type FilterValue = 'all' | 'pending' | 'assigned' | 'completed';
const FILTERS: {label: string; value: FilterValue}[] = [
  {label: 'All',       value: 'all'},
  {label: 'Pending',   value: 'pending'},
  {label: 'Assigned',  value: 'assigned'},
  {label: 'Completed', value: 'completed'},
];

// ─── helpers ─────────────────────────────────────────────────────────────────
function getFriendlyErrorMessage(error: unknown) {
  if (error instanceof ApiError) {
    if (error.status === 401) {return 'Your session has expired. Please log in again.';}
    return error.message;
  }
  return 'Could not load inspection requests right now.';
}

function formatIRQId(id: number) {
  // zero-pad to at least 4 digits to produce IRQ-1024 style IDs
  return `IRQ-${String(id).padStart(4, '0')}`;
}

function formatSchedule(dateNeeded?: string | null) {
  if (!dateNeeded) {return 'Schedule not set';}
  const d = new Date(dateNeeded);
  if (isNaN(d.getTime())) {return dateNeeded;}
  return d.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  }) + ' • ' + d.toLocaleTimeString('en-US', {
    hour: 'numeric',
    minute: '2-digit',
  });
}

function getCustomerName(item: TechnicianInspectionRequest) {
  return item.customer?.name ?? 'Unknown customer';
}

// ─── bottom nav icons ─────────────────────────────────────────────────────────
type Tab = 'Home' | 'Inspections' | 'Services' | 'Profile';

function HomeIcon({active}: {active?: boolean}) {
  const c = active ? NAVY : MUTED;
  return (
    <View style={nav.iconWrap}>
      <View style={[nav.roof, {borderBottomColor: c}]} />
      <View style={[nav.houseBody, {backgroundColor: c}]} />
    </View>
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

function BottomNav({onPress}: {onPress: (t: Tab) => void}) {
  const tabs: {key: Tab; label: string; Icon: React.FC<{active?: boolean}>}[] = [
    {key: 'Home',        label: 'Home',        Icon: HomeIcon},
    {key: 'Inspections', label: 'Inspections', Icon: InspectIcon},
    {key: 'Services',    label: 'Services',    Icon: ServicesIcon},
    {key: 'Profile',     label: 'Profile',     Icon: ProfileIcon},
  ];
  return (
    <View style={nav.bar}>
      {tabs.map(({key, label, Icon}) => (
        <Pressable key={key} style={nav.tab} onPress={() => onPress(key)}>
          <Icon active={key === 'Inspections'} />
          <Text style={[nav.label, key === 'Inspections' && nav.labelActive]}>
            {label}
          </Text>
        </Pressable>
      ))}
    </View>
  );
}

// ─── inspection request card ──────────────────────────────────────────────────
function InspectionCard({
  item,
  onPress,
}: {
  item: TechnicianInspectionRequest;
  onPress: () => void;
}) {
  return (
    <Pressable
      style={({pressed}) => [s.card, pressed && s.pressed]}
      onPress={onPress}>
      {/* top row: type pill + status badge */}
      <View style={s.cardTopRow}>
        <View style={s.typePill}>
          <Text style={s.typePillText}>Inspection</Text>
        </View>
        <StatusBadge status={item.status} />
      </View>

      {/* ID */}
      <Text style={s.cardId}>
        Inspection Request ID: {formatIRQId(item.id)}
      </Text>

      {/* customer */}
      <Text style={s.cardMeta}>Customer Name: {getCustomerName(item)}</Text>

      {/* schedule */}
      <Text style={s.cardMeta}>
        Schedule: {formatSchedule(item.date_needed)}
      </Text>

      <View style={s.divider} />

      {/* view details row */}
      <View style={s.viewDetailsRow}>
        <Text style={s.viewDetailsText}>View Details</Text>
        <Text style={s.chevron}>›</Text>
      </View>
    </Pressable>
  );
}

// ─── main screen ──────────────────────────────────────────────────────────────
export default function AssignedTasksScreen({navigation}: any) {
  const [all, setAll] = useState<TechnicianInspectionRequest[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [errorMessage, setErrorMessage] = useState('');
  const [filter, setFilter] = useState<FilterValue>('all');

  const load = useCallback(async (showSpinner = false) => {
    try {
      if (showSpinner) {setLoading(true);}
      setErrorMessage('');
      const data = await getAssignedInspectionRequests();
      setAll(Array.isArray(data) ? data : []);
    } catch (error) {
      setAll([]);
      setErrorMessage(getFriendlyErrorMessage(error));
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, []);

  useFocusEffect(useCallback(() => { load(true); }, [load]));

  const filtered = filter === 'all'
    ? all
    : all.filter(r => (r.status ?? '').toLowerCase() === filter);

  function handleTabPress(tab: Tab) {
    if (tab === 'Home')      {navigation.navigate('TechnicianDashboard');}
    if (tab === 'Services')  {navigation.navigate('TechnicianServiceRequests');}
    if (tab === 'Profile')   {navigation.navigate('TechnicianSettings');}
  }

  function openDetails(item: TechnicianInspectionRequest) {
    navigation.navigate('InspectionDetails', {
      inspectionRequestId: item.id,
      initialInspectionRequest: item,
    });
  }

  return (
    <View style={s.root}>
      <SafeAreaView style={s.safe}>
        {/* ── header ── */}
        <View style={s.header}>
          <View style={s.brandRow}>
            <Text style={s.brandSol}>Sol</Text>
            <Text style={s.brandGold}>Mate</Text>
          </View>
        </View>

        {/* ── page title ── */}
        <View style={s.titleBlock}>
          <Text style={s.pageTitle}>Inspection Request</Text>
          <Text style={s.pageSub}>Manage scheduled inspections.</Text>
        </View>

        {/* ── filter chips ── */}
        <View style={s.chipRow}>
          {FILTERS.map(f => (
            <Pressable
              key={f.value}
              style={[s.chip, filter === f.value && s.chipActive]}
              onPress={() => setFilter(f.value)}>
              <Text style={[s.chipText, filter === f.value && s.chipTextActive]}>
                {f.label}
              </Text>
            </Pressable>
          ))}
        </View>

        {/* ── content ── */}
        {loading ? (
          <View style={s.centered}>
            <ActivityIndicator size="large" color={NAVY} />
            <Text style={s.loadingText}>Loading inspection requests…</Text>
          </View>
        ) : errorMessage ? (
          <View style={s.errorWrap}>
            <View style={s.errorCard}>
              <Text style={s.errorTitle}>Something went wrong</Text>
              <Text style={s.errorText}>{errorMessage}</Text>
              <AppButton
                title="Try again"
                onPress={() => load(true)}
                style={s.retryBtn}
              />
            </View>
          </View>
        ) : (
          <FlatList
            contentContainerStyle={[
              s.listContent,
              filtered.length === 0 && s.emptyListContent,
            ]}
            data={filtered}
            keyExtractor={item => item.id.toString()}
            renderItem={({item}) => (
              <InspectionCard item={item} onPress={() => openDetails(item)} />
            )}
            refreshControl={
              <RefreshControl
                refreshing={refreshing}
                onRefresh={() => { setRefreshing(true); load(false); }}
                tintColor={NAVY}
              />
            }
            showsVerticalScrollIndicator={false}
            ListEmptyComponent={
              <View style={s.emptyState}>
                <Text style={s.emptyTitle}>No inspection requests</Text>
                <Text style={s.emptyText}>
                  {filter === 'all'
                    ? 'No inspection requests have been assigned to your account yet.'
                    : `No ${filter} inspection requests found.`}
                </Text>
              </View>
            }
          />
        )}
      </SafeAreaView>

      {/* ── bottom nav ── */}
      <BottomNav onPress={handleTabPress} />
    </View>
  );
}

// ─── nav styles ────────────────────────────────────────────────────────────────
const nav = StyleSheet.create({
  bar: {
    flexDirection: 'row',
    backgroundColor: '#f2f4f8',
    borderTopWidth: 1,
    borderTopColor: '#dde2ec',
    paddingBottom: 8,
    paddingTop: 8,
  },
  tab:        {flex: 1, alignItems: 'center', justifyContent: 'center', gap: 3},
  label:      {fontSize: 10, color: MUTED, fontWeight: '500'},
  labelActive:{color: NAVY, fontWeight: '700'},
  iconWrap:   {width: 24, height: 22, alignItems: 'center', justifyContent: 'flex-end'},
  roof: {
    width: 0, height: 0,
    borderLeftWidth: 10, borderRightWidth: 10, borderBottomWidth: 8,
    borderLeftColor: 'transparent', borderRightColor: 'transparent',
    borderBottomColor: MUTED,
  },
  houseBody:   {width: 14, height: 9, backgroundColor: MUTED, borderRadius: 1},
  listBox: {
    width: 18, height: 20, borderRadius: 3,
    alignItems: 'flex-start', justifyContent: 'center',
    paddingHorizontal: 3, gap: 3,
  },
  listLine:    {height: 2, width: 10, backgroundColor: '#f2f4f8', borderRadius: 1},
  gear: {
    width: 20, height: 20, borderRadius: 10,
    borderWidth: 3, alignItems: 'center', justifyContent: 'center',
  },
  gearInner:   {width: 8, height: 8, borderRadius: 4},
  profileHead: {width: 10, height: 10, borderRadius: 5, marginBottom: 2},
  profileBody: {width: 16, height: 8, borderTopLeftRadius: 8, borderTopRightRadius: 8},
});

// ─── screen styles ─────────────────────────────────────────────────────────────
const s = StyleSheet.create({
  root:  {flex: 1, backgroundColor: BG},
  safe:  {flex: 1, backgroundColor: BG},

  // header
  header: {
    paddingHorizontal: 18,
    paddingTop: 10,
    paddingBottom: 6,
  },
  brandRow: {flexDirection: 'row', alignItems: 'center'},
  brandSol:  {fontSize: 22, fontWeight: '800', color: NAVY},
  brandGold: {fontSize: 22, fontWeight: '800', color: GOLD},

  // title block
  titleBlock: {
    paddingHorizontal: 18,
    marginBottom: 12,
  },
  pageTitle: {
    fontSize: 26,
    fontWeight: '800',
    color: NAVY,
    marginBottom: 2,
  },
  pageSub: {
    fontSize: 13,
    color: MUTED,
  },

  // filter chips
  chipRow: {
    flexDirection: 'row',
    paddingHorizontal: 18,
    gap: 8,
    marginBottom: 14,
  },
  chip: {
    borderRadius: 999,
    borderWidth: 1,
    borderColor: '#b8c4d8',
    paddingVertical: 5,
    paddingHorizontal: 14,
    backgroundColor: CARD,
  },
  chipActive: {
    backgroundColor: NAVY,
    borderColor: NAVY,
  },
  chipText:       {fontSize: 13, color: NAVY, fontWeight: '600'},
  chipTextActive: {color: CARD},

  // loading / error
  centered: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    paddingBottom: 40,
  },
  loadingText: {color: MUTED, fontSize: 14, marginTop: 10},
  errorWrap:   {flex: 1, padding: 18},
  errorCard: {
    backgroundColor: CARD,
    borderColor: '#fecaca',
    borderRadius: 20,
    borderWidth: 1,
    padding: 20,
  },
  errorTitle: {color: '#b91c1c', fontSize: 17, fontWeight: '700', marginBottom: 6},
  errorText:  {color: '#991b1b', fontSize: 14, lineHeight: 20},
  retryBtn:   {marginTop: 16},

  // list
  listContent:      {paddingHorizontal: 18, paddingBottom: 24, paddingTop: 2},
  emptyListContent: {flexGrow: 1},
  emptyState: {
    alignItems: 'center',
    backgroundColor: CARD,
    borderRadius: 20,
    padding: 32,
    marginTop: 8,
  },
  emptyTitle: {
    color: NAVY,
    fontSize: 17,
    fontWeight: '700',
    marginBottom: 8,
    textAlign: 'center',
  },
  emptyText: {color: MUTED, fontSize: 14, lineHeight: 20, textAlign: 'center'},

  // card
  card: {
    backgroundColor: CARD,
    borderRadius: 20,
    marginBottom: 14,
    paddingHorizontal: 16,
    paddingVertical: 14,
    shadowColor: SHADOW,
    shadowOffset: {width: 0, height: 4},
    shadowOpacity: 0.10,
    shadowRadius: 10,
    elevation: 2,
  },
  pressed: {opacity: 0.85},

  cardTopRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginBottom: 8,
  },
  typePill: {
    backgroundColor: '#e8edf7',
    borderRadius: 999,
    paddingHorizontal: 12,
    paddingVertical: 4,
  },
  typePillText: {
    color: NAVY,
    fontSize: 12,
    fontWeight: '600',
  },

  cardId: {
    color: NAVY,
    fontSize: 15,
    fontWeight: '800',
    marginBottom: 4,
  },
  cardMeta: {
    color: MUTED,
    fontSize: 13,
    lineHeight: 20,
  },

  divider: {
    height: 1,
    backgroundColor: '#e2e8f0',
    marginVertical: 10,
  },

  viewDetailsRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
  },
  viewDetailsText: {
    color: NAVY,
    fontSize: 14,
    fontWeight: '700',
  },
  chevron: {
    color: MUTED,
    fontSize: 22,
    fontWeight: '400',
    lineHeight: 24,
  },
});
