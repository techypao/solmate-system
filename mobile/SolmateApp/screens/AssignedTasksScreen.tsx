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
import StatusBadge from '../components/StatusBadge';
import {ApiError} from '../src/services/api';
import {
  getAssignedInspectionRequests,
  TechnicianInspectionRequest,
} from '../src/services/technicianApi';

// ─── colour tokens (match dashboard) ─────────────────────────────────────────
const NAVY = '#1A2B55';
const GOLD = '#F5C000';
const BG = '#C8D8F0';
const CARD   = '#ffffff';
const MUTED = '#6B7A99';
const SHADOW = '#8a9bbd';
const BORDER = '#D4E0F2';
const SOFT_YELLOW = '#FFF7CC';

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
      <TechnicianBottomNav activeTab="Inspections" />
    </View>
  );
}

// ─── nav styles ────────────────────────────────────────────────────────────────;

// ─── screen styles ─────────────────────────────────────────────────────────────
const s = StyleSheet.create({
  root:  {flex: 1, backgroundColor: BG},
  safe:  {flex: 1, backgroundColor: BG},

  // header
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
    backgroundColor: '#D4E0F2',
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
