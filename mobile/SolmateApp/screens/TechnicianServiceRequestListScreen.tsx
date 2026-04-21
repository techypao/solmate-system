import React, {useCallback, useState} from 'react';
import {
  ActivityIndicator,
  FlatList,
  Pressable,
  RefreshControl,
  SafeAreaView,
  ScrollView,
  StyleSheet,
  Text,
  View,
} from 'react-native';
import {useFocusEffect} from '@react-navigation/native';

import {ApiError} from '../src/services/api';
import {
  getTechnicianServiceRequests,
  ServiceRequest,
} from '../src/services/serviceRequestApi';

/* ── design tokens ─────────────────────────────────────────── */

const NAVY    = '#152a4a';
const GOLD    = '#e8a800';
const MUTED   = '#7b8699';
const BG      = '#e0e8f5';
const CARD    = '#ffffff';
const DIVIDER = '#edf1f7';

/* ── filter config ─────────────────────────────────────────── */

type FilterKey = 'All' | 'Installation' | 'Maintenance';

const FILTERS: FilterKey[] = [
  'All',
  'Installation',
  'Maintenance',
];

/* ── helpers ────────────────────────────────────────────────── */

function formatSchedule(value?: string | null): string {
  if (!value) return 'Not scheduled';
  const d = new Date(value);
  if (isNaN(d.getTime())) return value;
  return d.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  });
}

function formatStatusLabel(status?: string | null): string {
  switch ((status ?? '').toLowerCase()) {
    case 'assigned':    return 'Assigned';
    case 'in_progress': return 'In Progress';
    case 'completed':   return 'Completed';
    default:            return 'Pending';
  }
}

function getStatusColors(status?: string | null) {
  switch ((status ?? '').toLowerCase()) {
    case 'assigned':    return {bg: '#fef3c7', text: '#b45309'};
    case 'in_progress': return {bg: '#dbeafe', text: '#1d4ed8'};
    case 'completed':   return {bg: '#dcfce7', text: '#166534'};
    default:            return {bg: '#f1f5f9', text: MUTED};
  }
}

function getTypePillColors(type: string) {
  const t = (type ?? '').toLowerCase();
  if (t.includes('maintenance'))  return {bg: '#fffbeb', text: '#b45309', border: '#fde68a'};
  if (t.includes('installation')) return {bg: '#f0f9ff', text: '#0369a1', border: '#bae6fd'};
  return {bg: '#f1f5f9', text: MUTED, border: DIVIDER};
}

function applyFilter(
  items: ServiceRequest[],
  filter: FilterKey,
): ServiceRequest[] {
  switch (filter) {
    case 'Installation':
      return items.filter(i =>
        i.request_type?.toLowerCase().includes('installation'),
      );
    case 'Maintenance':
      return items.filter(i =>
        i.request_type?.toLowerCase().includes('maintenance'),
      );
    default:
      return items;
  }
}

function getFriendlyError(error: unknown): string {
  if (error instanceof ApiError) {
    if (error.status === 401) return 'Session expired. Please log in again.';
    return error.message;
  }
  return 'Could not load service requests right now.';
}

/* ── card component ─────────────────────────────────────────── */

function ServiceRequestCard({
  item,
  navigation,
}: {
  item: ServiceRequest;
  navigation: any;
}) {
  const statusColors = getStatusColors(item.status);
  const typeColors   = getTypePillColors(item.request_type ?? '');
  const customerName = item.customer?.name ?? 'Unknown customer';

  return (
    <Pressable
      style={({pressed}) => [s.card, pressed && s.pressed]}
      onPress={() =>
        navigation.navigate('TechnicianServiceRequestDetail', {
          serviceRequestId: item.id,
          initialServiceRequest: item,
          mode: 'technician',
        })
      }>
      {/* top row: type pill + status badge */}
      <View style={s.cardTopRow}>
        <View
          style={[
            s.typePill,
            {backgroundColor: typeColors.bg, borderColor: typeColors.border},
          ]}>
          <Text style={[s.typePillText, {color: typeColors.text}]}>
            {item.request_type ?? 'Service'}
          </Text>
        </View>
        <View style={[s.statusBadge, {backgroundColor: statusColors.bg}]}>
          <Text style={[s.statusBadgeText, {color: statusColors.text}]}>
            {formatStatusLabel(item.status)}
          </Text>
        </View>
      </View>

      {/* title */}
      <Text style={s.cardTitle}>Service Request ID: SR-{item.id}</Text>

      {/* customer */}
      <Text style={s.cardMeta}>Customer: {customerName}</Text>

      {/* schedule */}
      <Text style={s.cardMeta}>
        Schedule: {formatSchedule(item.date_needed)}
      </Text>

      {/* view details row */}
      <View style={s.viewDetailsRow}>
        <Text style={s.viewDetailsText}>View Details</Text>
        <Text style={s.chevron}>{'›'}</Text>
      </View>
    </Pressable>
  );
}

/* ── empty state ─────────────────────────────────────────────── */

function EmptyState() {
  return (
    <View style={s.empty}>
      <Text style={s.emptyIcon}>{'\uD83D\uDCCB'}</Text>
      <Text style={s.emptyTitle}>No service requests found</Text>
      <Text style={s.emptyText}>
        Assigned service requests will appear here once they are linked to your
        account.
      </Text>
    </View>
  );
}

/* ── bottom nav icons ────────────────────────────────────────── */

type Tab = 'Home' | 'Inspections' | 'Services' | 'Profile';

function HomeIcon({active}: {active?: boolean}) {
  const c = active ? NAVY : MUTED;
  return (
    <Text style={{fontSize: 20, color: c, lineHeight: 22, textAlign: 'center'}}>
      {'\u2302'}
    </Text>
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
  const tabs: {key: Tab; label: string; Icon: React.FC<{active?: boolean}>}[] =
    [
      {key: 'Home',        label: 'Home',        Icon: HomeIcon},
      {key: 'Inspections', label: 'Inspections', Icon: InspectIcon},
      {key: 'Services',    label: 'Services',    Icon: ServicesIcon},
      {key: 'Profile',     label: 'Profile',     Icon: ProfileIcon},
    ];

  return (
    <View style={nav.bar}>
      {tabs.map(({key, label, Icon}) => (
        <Pressable key={key} style={nav.tab} onPress={() => onPress(key)}>
          <Icon active={key === 'Services'} />
          <Text style={[nav.label, key === 'Services' && nav.labelActive]}>
            {label}
          </Text>
        </Pressable>
      ))}
    </View>
  );
}

/* ── main screen ─────────────────────────────────────────────── */

export default function TechnicianServiceRequestListScreen({
  navigation,
}: any) {
  const [items, setItems]           = useState<ServiceRequest[]>([]);
  const [loading, setLoading]       = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [error, setError]           = useState('');
  const [filter, setFilter]         = useState<FilterKey>('All');

  const load = useCallback(async (showSpinner = false) => {
    try {
      if (showSpinner) setLoading(true);
      setError('');
      const data = await getTechnicianServiceRequests();
      setItems(Array.isArray(data) ? data : []);
    } catch (err) {
      setItems([]);
      setError(getFriendlyError(err));
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, []);

  useFocusEffect(useCallback(() => { load(true); }, [load]));

  const handleRefresh = () => {
    setRefreshing(true);
    load(false);
  };

  const handleTabPress = (tab: Tab) => {
    if (tab === 'Home')        navigation.navigate('TechnicianDashboard');
    if (tab === 'Inspections') navigation.navigate('AssignedInspectionRequests');
    if (tab === 'Profile')     navigation.navigate('TechnicianSettings');
  };

  const filtered = applyFilter(items, filter);

  /* ── loading state ── */
  if (loading) {
    return (
      <SafeAreaView style={s.root}>
        <View style={s.centered}>
          <ActivityIndicator color={GOLD} size="large" />
          <Text style={s.loadingText}>Loading service requests…</Text>
        </View>
      </SafeAreaView>
    );
  }

  /* ── main render ── */
  return (
    <SafeAreaView style={s.root}>
      {/* ── header + chips ── */}
      <View style={s.header}>
        <Text style={s.brand}>
          Sol<Text style={s.brandAccent}>Mate</Text>
        </Text>
        <Text style={s.title}>Service Request</Text>
        <Text style={s.subtitle}>Handle installation and maintenance tasks</Text>

        {/* filter chips embedded in header */}
        <ScrollView
          horizontal
          showsHorizontalScrollIndicator={false}
          contentContainerStyle={s.chipRow}
          style={s.chipScroll}>
          {FILTERS.map(f => (
            <Pressable
              key={f}
              onPress={() => setFilter(f)}
              style={[s.chip, filter === f && s.chipActive]}>
              <Text style={[s.chipText, filter === f && s.chipTextActive]}>
                {f}
              </Text>
            </Pressable>
          ))}
        </ScrollView>
      </View>

      {/* ── error banner ── */}
      {error ? (
        <View style={s.errorBanner}>
          <Text style={s.errorMsg}>{error}</Text>
          <Pressable onPress={() => load(true)} style={s.retryBtn}>
            <Text style={s.retryText}>Retry</Text>
          </Pressable>
        </View>
      ) : null}

      {/* ── list ── */}
      <FlatList
        contentContainerStyle={[
          s.listPad,
          filtered.length === 0 && s.listPadGrow,
        ]}
        data={filtered}
        keyExtractor={item => item.id.toString()}
        renderItem={({item}) => (
          <ServiceRequestCard item={item} navigation={navigation} />
        )}
        refreshControl={
          <RefreshControl
            onRefresh={handleRefresh}
            refreshing={refreshing}
            tintColor={GOLD}
          />
        }
        showsVerticalScrollIndicator={false}
        ListEmptyComponent={<EmptyState />}
      />

      {/* ── bottom nav ── */}
      <BottomNav onPress={handleTabPress} />
    </SafeAreaView>
  );
}

/* ── nav styles ──────────────────────────────────────────────── */

const nav = StyleSheet.create({
  bar: {
    flexDirection: 'row',
    backgroundColor: CARD,
    borderTopWidth: 1,
    borderTopColor: DIVIDER,
    paddingBottom: 8,
    paddingTop: 8,
  },
  tab:        {flex: 1, alignItems: 'center', justifyContent: 'center', gap: 3},
  label:      {fontSize: 10, color: MUTED, fontWeight: '500'},
  labelActive:{color: NAVY, fontWeight: '700'},
  iconWrap:   {width: 24, height: 22, alignItems: 'center', justifyContent: 'flex-end'},
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

/* ── screen styles ───────────────────────────────────────────── */

const s = StyleSheet.create({
  root:        {flex: 1, backgroundColor: BG},
  centered:    {flex: 1, alignItems: 'center', justifyContent: 'center'},
  loadingText: {color: MUTED, fontSize: 14, marginTop: 14},
  pressed:     {opacity: 0.85},

  /* header */
  header:      {paddingHorizontal: 22, paddingTop: 12, paddingBottom: 8},
  brand:       {fontSize: 18, fontWeight: '800', color: NAVY, marginBottom: 4},
  brandAccent: {color: GOLD},
  title:       {fontSize: 24, fontWeight: '900', color: NAVY, marginBottom: 2},
  subtitle:    {fontSize: 13, color: MUTED, marginBottom: 10},

  /* filter chips */
  chipScroll: {height: 52},
  chipRow:    {
    paddingVertical: 6,
    gap: 8,
    flexDirection: 'row',
    alignItems: 'center',
  },
  chip: {
    paddingHorizontal: 16,
    paddingVertical: 10,
    borderRadius: 999,
    borderWidth: 1.5,
    borderColor: '#c9d4e8',
    backgroundColor: CARD,
  },
  chipActive:     {backgroundColor: NAVY, borderColor: NAVY},
  chipText:       {fontSize: 14, color: NAVY, fontWeight: '600'},
  chipTextActive: {color: CARD, fontWeight: '700'},

  /* error */
  errorBanner: {
    marginHorizontal: 22,
    marginTop: 4,
    marginBottom: 8,
    backgroundColor: '#fee2e2',
    borderRadius: 14,
    padding: 14,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
  },
  errorMsg:  {color: '#991b1b', fontSize: 13, flex: 1},
  retryBtn:  {
    marginLeft: 12,
    backgroundColor: '#991b1b',
    borderRadius: 10,
    paddingHorizontal: 12,
    paddingVertical: 6,
  },
  retryText: {color: CARD, fontSize: 12, fontWeight: '700'},

  /* list */
  listPad:     {paddingHorizontal: 22, paddingTop: 10, paddingBottom: 20},
  listPadGrow: {flexGrow: 1},

  /* card */
  card: {
    backgroundColor: CARD,
    borderRadius: 20,
    marginBottom: 14,
    padding: 18,
    shadowColor: '#8a9bbd',
    shadowOffset: {width: 0, height: 3},
    shadowOpacity: 0.10,
    shadowRadius: 12,
    elevation: 4,
  },
  cardTopRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 10,
  },
  typePill: {
    paddingHorizontal: 12,
    paddingVertical: 4,
    borderRadius: 999,
    borderWidth: 1.5,
  },
  typePillText:    {fontSize: 12, fontWeight: '700'},
  statusBadge:     {paddingHorizontal: 12, paddingVertical: 4, borderRadius: 999},
  statusBadgeText: {fontSize: 12, fontWeight: '700'},
  cardTitle: {
    fontSize: 16,
    fontWeight: '800',
    color: NAVY,
    marginBottom: 6,
    lineHeight: 22,
  },
  cardMeta:       {fontSize: 13, color: MUTED, marginBottom: 3, lineHeight: 19},
  viewDetailsRow: {
    flexDirection: 'row',
    alignItems: 'center',
    marginTop: 10,
    gap: 4,
  },
  viewDetailsText: {fontSize: 14, fontWeight: '700', color: NAVY},
  chevron:         {fontSize: 20, color: NAVY, marginTop: -1},

  /* empty */
  empty: {
    alignItems: 'center',
    padding: 32,
    backgroundColor: CARD,
    borderRadius: 20,
    shadowColor: '#8a9bbd',
    shadowOffset: {width: 0, height: 3},
    shadowOpacity: 0.08,
    shadowRadius: 12,
    elevation: 3,
  },
  emptyIcon:  {fontSize: 32, marginBottom: 12},
  emptyTitle: {fontSize: 17, fontWeight: '800', color: NAVY, marginBottom: 8},
  emptyText:  {
    fontSize: 14,
    color: MUTED,
    textAlign: 'center',
    lineHeight: 20,
  },
});
