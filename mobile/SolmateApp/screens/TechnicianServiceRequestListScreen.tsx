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
import Icon from 'react-native-vector-icons/MaterialCommunityIcons';
import TechnicianBottomNav from '../src/components/TechnicianBottomNav';

import {AppButton} from '../components';
import {ApiError} from '../src/services/api';
import {
  getTechnicianServiceRequests,
  ServiceRequest,
} from '../src/services/serviceRequestApi';

/* ── design tokens ─────────────────────────────────────────── */

const NAVY = '#1A2B55';
const GOLD = '#F5C000';
const MUTED = '#6B7A99';
const BG = '#C8D8F0';
const CARD    = '#ffffff';
const SHADOW  = '#8a9bbd';
const BORDER = '#D4E0F2';
const SOFT_YELLOW = '#FFF7CC';

/* ── filter config ─────────────────────────────────────────── */

type FilterValue = 'all' | 'installation' | 'maintenance';

const FILTERS: {label: string; value: FilterValue}[] = [
  {label: 'All', value: 'all'},
  {label: 'Installation', value: 'installation'},
  {label: 'Maintenance', value: 'maintenance'},
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
    case 'assigned':    return {bg: '#FFF7CC', text: '#b45309'};
    case 'in_progress': return {bg: '#EAF9FD', text: '#1d4ed8'};
    case 'completed':   return {bg: '#dcfce7', text: '#166534'};
    default:            return {bg: '#f1f5f9', text: MUTED};
  }
}

function getTypePillColors(type: string) {
  const t = (type ?? '').toLowerCase();
  if (t.includes('maintenance'))  return {bg: '#fffbeb', text: '#b45309', border: '#fde68a'};
  if (t.includes('installation')) return {bg: '#f0f9ff', text: '#0369a1', border: '#bae6fd'};
  return {bg: '#f1f5f9', text: MUTED, border: BORDER};
}

function applyFilter(
  items: ServiceRequest[],
  filter: FilterValue,
): ServiceRequest[] {
  switch (filter) {
    case 'installation':
      return items.filter(i =>
        i.request_type?.toLowerCase().includes('installation'),
      );
    case 'maintenance':
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
      <Text style={s.cardId}>Service Request ID: SR-{item.id}</Text>

      {/* customer */}
      <Text style={s.cardMeta}>Customer Name: {customerName}</Text>
      <Text style={s.cardMeta}>Address: {item.address || 'Not provided'}</Text>

      {/* schedule */}
      <Text style={s.cardMeta}>
        Schedule: {formatSchedule(item.date_needed)}
      </Text>

      <View style={s.divider} />

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
    <View style={s.emptyState}>
      <Text style={s.emptyTitle}>No service requests</Text>
      <Text style={s.emptyText}>
        No service requests have been assigned to your account yet.
      </Text>
    </View>
  );
}

/* ── bottom nav icons ────────────────────────────────────────── */

export default function TechnicianServiceRequestListScreen({
  navigation,
}: any) {
  const [items, setItems]           = useState<ServiceRequest[]>([]);
  const [loading, setLoading]       = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [error, setError]           = useState('');
  const [filter, setFilter]         = useState<FilterValue>('all');

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


  const filtered = applyFilter(items, filter);

  /* ── loading state ── */
  if (loading) {
    return (
      <View style={s.root}>
        <SafeAreaView style={s.safe}>
        <View style={s.centered}>
          <ActivityIndicator color={NAVY} size="large" />
          <Text style={s.loadingText}>Loading service requests…</Text>
        </View>
        </SafeAreaView>
      </View>
    );
  }

  /* ── main render ── */
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
          <Text style={s.pageTitle}>Service Request</Text>
          <Text style={s.pageSub}>Handle installation and maintenance tasks</Text>
        </View>

        {/* ── filter chips ── */}
        <View style={s.chipRow}>
          {FILTERS.map(f => (
            <Pressable
              key={f.value}
              onPress={() => setFilter(f.value)}
              style={[s.chip, filter === f.value && s.chipActive]}>
              <Text style={[s.chipText, filter === f.value && s.chipTextActive]}>
                {f.label}
              </Text>
            </Pressable>
          ))}
        </View>

        {/* ── content ── */}
        {error ? (
          <View style={s.errorWrap}>
            <View style={s.errorCard}>
              <Text style={s.errorTitle}>Something went wrong</Text>
              <Text style={s.errorText}>{error}</Text>
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
              <ServiceRequestCard item={item} navigation={navigation} />
            )}
            refreshControl={
              <RefreshControl
                onRefresh={handleRefresh}
                refreshing={refreshing}
                tintColor={NAVY}
              />
            }
            showsVerticalScrollIndicator={false}
            ListEmptyComponent={<EmptyState />}
          />
        )}
      </SafeAreaView>

      {/* ── bottom nav ── */}
      <TechnicianBottomNav activeTab="Services" />
    </View>
  );
}

/* ── nav styles ──────────────────────────────────────────────── */
;

/* ── screen styles ───────────────────────────────────────────── */

const s = StyleSheet.create({
  root:  {flex: 1, backgroundColor: BG},
  safe:  {flex: 1, backgroundColor: BG},

  centered: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    paddingBottom: 40,
  },
  loadingText: {color: MUTED, fontSize: 14, marginTop: 10},
  pressed:     {opacity: 0.85},

  /* header */
  header: {
    paddingHorizontal: 18,
    paddingTop: 12,
    paddingBottom: 10,
    marginHorizontal: 18,
    marginTop: 8,
    marginBottom: 6,
    borderRadius: 20,
    backgroundColor: CARD,
    borderWidth: 1,
    borderColor: BORDER,
    shadowColor: SHADOW,
    shadowOffset: {width: 0, height: 8},
    shadowOpacity: 0.08,
    shadowRadius: 18,
    elevation: 3,
  },
  brandRow: {flexDirection: 'row', alignItems: 'center'},
  brandSol:  {fontSize: 22, fontWeight: '800', color: NAVY},
  brandGold: {fontSize: 22, fontWeight: '800', color: GOLD},

  /* title block */
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

  /* filter chips */
  chipRow:    {
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

  /* error */
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

  /* list */
  listContent:      {paddingHorizontal: 18, paddingBottom: 90, paddingTop: 2},
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

  /* card */
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
  cardTopRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginBottom: 8,
  },
  typePill: {
    backgroundColor: '#e8edf7',
    paddingHorizontal: 12,
    paddingVertical: 4,
    borderRadius: 999,
  },
  typePillText:    {fontSize: 12, fontWeight: '600', color: NAVY},
  statusBadge:     {paddingHorizontal: 12, paddingVertical: 4, borderRadius: 999},
  statusBadgeText: {fontSize: 12, fontWeight: '700'},
  cardId: {
    fontSize: 15,
    fontWeight: '800',
    color: NAVY,
    marginBottom: 4,
  },
  cardMeta:       {fontSize: 13, color: MUTED, lineHeight: 20},
  divider: {
    height: 1,
    backgroundColor: '#D4E0F2',
    marginVertical: 10,
  },
  viewDetailsRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
  },
  viewDetailsText: {fontSize: 14, fontWeight: '700', color: NAVY},
  chevron:         {fontSize: 22, color: MUTED, fontWeight: '400', lineHeight: 24},
});
