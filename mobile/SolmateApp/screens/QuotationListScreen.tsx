import React, {useCallback, useEffect, useMemo, useState} from 'react';
import {
  ActivityIndicator,
  FlatList,
  Pressable,
  SafeAreaView,
  StyleSheet,
  Text,
  useWindowDimensions,
  View,
} from 'react-native';

import {ApiError} from '../src/services/api';
import CustomerBottomNav from '../src/components/CustomerBottomNav';
import {getQuotations} from '../src/services/quotationApi';
import {formatQuotationCurrency} from '../src/utils/currency';

/* ── design tokens ── */

const NAVY = '#123A5A';
const GOLD = '#F4D000';
const MUTED = '#5E7288';
const BG = '#F8FAFC';
const CARD = '#ffffff';

/* ── types ── */

type Quotation = {
  id: number;
  user_id?: number;
  inspection_request_id?: number | null;
  quotation_type: string;
  status: string;
  monthly_electric_bill?: number | null;
  pv_system_type?: string | null;
  panel_quantity?: number | null;
  system_kw?: number | null;
  project_cost?: number | null;
  roi_years?: number | null;
  created_at?: string;
};

type FilterKey = 'all' | 'initial' | 'final' | 'completed';

/* ── format helpers (preserved) ── */

function formatYears(value?: number | null) {
  if (value === null || value === undefined || Number.isNaN(value)) return 'N/A';
  return value.toFixed(1);
}

function formatDate(value?: string) {
  if (!value) return 'N/A';
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return value;
  const mo = date.toLocaleString('en-US', {month: 'short'});
  const dd = date.getDate();
  const yyyy = date.getFullYear();
  let hh = date.getHours();
  const mm = String(date.getMinutes()).padStart(2, '0');
  const ampm = hh >= 12 ? 'PM' : 'AM';
  hh = hh % 12 || 12;
  return mo + ' ' + dd + ', ' + yyyy + ' \u2022 ' + hh + ':' + mm + ' ' + ampm;
}

function formatTypeLabel(type?: string | null) {
  switch ((type || '').toLowerCase()) {
    case 'final':
      return 'Inspection-Based Quotation';
    case 'initial':
      return 'Pre-Inspection Estimate';
    default:
      return 'Quote';
  }
}

function formatStatusLabel(status?: string | null) {
  const s = (status || 'pending').toLowerCase();
  switch (s) {
    case 'pending':
      return 'Generated';
    case 'approved':
      return 'Confirmed';
    case 'completed':
      return 'Completed';
    case 'rejected':
      return 'Rejected';
    default:
      return s.charAt(0).toUpperCase() + s.slice(1);
  }
}

function getQuoteIdLabel(item: Quotation) {
  const prefix = item.quotation_type === 'final' ? 'INSP' : 'PRE';
  return prefix + '-' + item.id;
}

/* ── badge colours ── */

function getTypeBadgeColors(type?: string | null) {
  if ((type || '').toLowerCase() === 'final') {
    return {bg: '#EAF9FD', text: '#1d4ed8'};
  }
  return {bg: '#ede9fe', text: '#6d28d9'};
}

function getStatusBadgeColors(status?: string | null) {
  switch ((status || '').toLowerCase()) {
    case 'approved':
      return {bg: '#dcfce7', text: '#166534'};
    case 'completed':
      return {bg: '#EAF9FD', text: '#1d4ed8'};
    case 'rejected':
      return {bg: '#fee2e2', text: '#b91c1c'};
    default:
      return {bg: '#FFF7CC', text: '#92400e'};
  }
}

/* ── primary CTA label based on status / type ── */

function getPrimaryActionLabel(item: Quotation) {
  if (item.quotation_type === 'final') {
    return 'View Inspection-Based';
  }
  return 'View Estimate';
}

/* ── filter chips config ── */

const FILTERS: {key: FilterKey; label: string}[] = [
  {key: 'all', label: 'All'},
  {key: 'initial', label: 'Pre-Inspection'},
  {key: 'final', label: 'Inspection-Based'},
];

/* ══════════════════════════════════════════
   Main screen
   ══════════════════════════════════════════ */

export default function QuotationListScreen({navigation}: any) {
  const {width} = useWindowDimensions();
  const [quotations, setQuotations] = useState<Quotation[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [errorMessage, setErrorMessage] = useState('');
  const [activeFilter, setActiveFilter] = useState<FilterKey>('all');
  const isCompactLayout = width < 390;

  /* ── fetch (preserved) ── */

  const fetchQuotations = useCallback(
    async (showLoadingState = false) => {
      try {
        if (showLoadingState) setLoading(true);
        setErrorMessage('');
        const data = await getQuotations();
        setQuotations(Array.isArray(data) ? data : []);
      } catch (error) {
        if (error instanceof ApiError) {
          setErrorMessage(error.message);
        } else {
          console.log('Quotation list error:', error);
          setErrorMessage('Could not connect to the server.');
        }
        setQuotations([]);
      } finally {
        setLoading(false);
        setRefreshing(false);
      }
    },
    [],
  );

  useEffect(() => {
    fetchQuotations(true);
  }, [fetchQuotations]);

  const handleRefresh = () => {
    setRefreshing(true);
    fetchQuotations(false);
  };

  /* ── filtered list ── */

  const filteredQuotations = useMemo(() => {
    if (activeFilter === 'all') return quotations;
    if (activeFilter === 'completed') {
      return quotations.filter(
        q => (q.status || '').toLowerCase() === 'completed',
      );
    }
    return quotations.filter(
      q => (q.quotation_type || '').toLowerCase() === activeFilter,
    );
  }, [quotations, activeFilter]);

  /* ── navigation helpers (preserved) ── */

  const handleViewDetails = (item: Quotation) => {
    if (item.quotation_type === 'final' && item.inspection_request_id) {
      navigation.navigate('FinalQuotationView', {
        inspectionRequestId: item.inspection_request_id,
      });
    } else {
      navigation.navigate('QuotationDetail', {quotationId: item.id});
    }
  };

  const handlePrimaryAction = (item: Quotation) => {
    if (item.quotation_type === 'final') {
      handleViewDetails(item);
      return;
    }

    handleViewDetails(item);
  };

  /* ── render card ── */

  const renderQuotationItem = ({item}: {item: Quotation}) => {
    const isFinalQuotation = (item.quotation_type || '').toLowerCase() === 'final';
    const typeColors = getTypeBadgeColors(item.quotation_type);
    const statusColors = isFinalQuotation
      ? getStatusBadgeColors(item.status)
      : null;

    return (
      <View style={s.card}>
        {/* badges row */}
        <View style={s.badgeRow}>
          <View style={[s.badge, {backgroundColor: typeColors.bg}]}>
            <Text style={[s.badgeText, {color: typeColors.text}]}>
              {formatTypeLabel(item.quotation_type)}
            </Text>
          </View>
          {isFinalQuotation && statusColors ? (
            <View style={[s.badge, {backgroundColor: statusColors.bg}]}>
              <Text style={[s.badgeText, {color: statusColors.text}]}>
                {formatStatusLabel(item.status)}
              </Text>
            </View>
          ) : null}
        </View>

        {/* quote info */}
        <Text style={s.quoteId}>
          Quote ID: {getQuoteIdLabel(item)}
        </Text>
        <Text style={s.dateLine}>
          Generated: {formatDate(item.created_at)}
        </Text>

        <Text style={s.costLine}>
          {item.quotation_type === 'final' ? 'Project Cost' : 'Estimated Cost'}:{' '}
          <Text style={s.costValue}>
            {formatQuotationCurrency(item.project_cost)}
          </Text>
        </Text>
        <Text style={s.roiLine}>
          ROI Years: {formatYears(item.roi_years)}
        </Text>

        {/* action buttons */}
        <View style={s.actionRow}>
          <Pressable
            onPress={() => handleViewDetails(item)}
            style={({pressed}) => [s.btnSecondary, pressed && s.pressed]}>
            <Text style={s.btnSecondaryText}>View Details</Text>
          </Pressable>

          {isFinalQuotation ? (
            <Pressable
              onPress={() => handlePrimaryAction(item)}
              style={({pressed}) => [s.btnPrimary, pressed && s.pressed]}>
              <Text style={s.btnPrimaryText}>
                {getPrimaryActionLabel(item)}
              </Text>
            </Pressable>
          ) : null}
        </View>
      </View>
    );
  };

  /* ── loading state ── */

  if (loading) {
    return (
      <SafeAreaView style={s.safe}>
        <View style={s.center}>
          <ActivityIndicator size="large" color={GOLD} />
          <Text style={s.loadingText}>Loading quotations...</Text>
        </View>
      </SafeAreaView>
    );
  }

  /* ── main render ── */

  return (
    <SafeAreaView style={s.safe}>
      <View style={s.headerBar}>
        {/* back */}
        <Pressable
          hitSlop={14}
          onPress={() => navigation.goBack()}
          style={({pressed}) => [s.backBtn, pressed && s.pressed]}>
          <Text style={s.backIcon}>{'\u2039'}</Text>
        </Pressable>

        <Text style={s.headerTitle}>My Quotations</Text>

        {/* placeholder for symmetry */}
        <View style={s.headerSpacer} />
      </View>

      {/* content area */}
      <View style={s.contentArea}>
        {/* filter chips */}
        <View style={s.filterSection}>
          <View style={s.filterRow}>
            {FILTERS.map(f => {
              const active = activeFilter === f.key;
              return (
                <Pressable
                  key={f.key}
                  onPress={() => setActiveFilter(f.key)}
                  style={({pressed}) => [
                    s.chip,
                    isCompactLayout && s.chipCompact,
                    active && s.chipActive,
                    pressed && s.pressed,
                  ]}>
                  <Text
                    style={[
                      s.chipText,
                      isCompactLayout && s.chipTextCompact,
                      active && s.chipTextActive,
                    ]}>
                    {f.label}
                  </Text>
                </Pressable>
              );
            })}
          </View>
        </View>

        {/* error */}
        {errorMessage ? (
          <Text style={s.errorText}>{errorMessage}</Text>
        ) : null}

        {/* empty */}
        {!errorMessage && filteredQuotations.length === 0 ? (
          <View style={s.emptyState}>
            <View style={s.emptyIcon} />
            <Text style={s.emptyTitle}>No quotations yet</Text>
            <Text style={s.emptyText}>
              Your pre-inspection estimates and inspection-based quotations will appear here once they are
              created.
            </Text>
          </View>
        ) : null}

        {/* list */}
        {!errorMessage && filteredQuotations.length > 0 ? (
          <FlatList
            data={filteredQuotations}
            keyExtractor={item => item.id.toString()}
            renderItem={renderQuotationItem}
            contentContainerStyle={s.listContent}
            showsVerticalScrollIndicator={false}
            refreshing={refreshing}
            onRefresh={handleRefresh}
          />
        ) : null}
      </View>

      {/* ── bottom nav ── */}
      <CustomerBottomNav activeTab="Services" />
    </SafeAreaView>
  );
}

/* ── styles ── */

const s = StyleSheet.create({
  safe: {flex: 1, backgroundColor: CARD},
  pressed: {opacity: 0.85},
  center: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    padding: 24,
    backgroundColor: BG,
  },

  /* header bar */
  headerBar: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingHorizontal: 20,
    paddingTop: 14,
    paddingBottom: 12,
    backgroundColor: CARD,
  },
  backBtn: {
    width: 36,
    height: 36,
    borderRadius: 18,
    alignItems: 'center',
    justifyContent: 'center',
  },
  backIcon: {fontSize: 30, color: NAVY, fontWeight: '600', marginTop: -2},
  headerTitle: {
    flex: 1,
    textAlign: 'center',
    fontSize: 20,
    fontWeight: '900',
    color: NAVY,
    letterSpacing: 0.2,
  },
  headerSpacer: {width: 36},

  /* content area */
  contentArea: {
    flex: 1,
    backgroundColor: BG,
    borderTopLeftRadius: 26,
    borderTopRightRadius: 26,
    paddingTop: 16,
    paddingHorizontal: 18,
  },

  /* filter row */
  filterSection: {
    gap: 12,
    marginBottom: 16,
  },
  filterRow: {
    flexDirection: 'row',
    alignItems: 'stretch',
    flexWrap: 'wrap',
    gap: 8,
  },
  chip: {
    minHeight: 44,
    maxWidth: '100%',
    paddingHorizontal: 14,
    paddingVertical: 10,
    borderRadius: 22,
    backgroundColor: CARD,
    borderWidth: 1,
    borderColor: '#c9d5e6',
    justifyContent: 'center',
    alignItems: 'center',
  },
  chipCompact: {
    width: '100%',
  },
  chipActive: {
    backgroundColor: NAVY,
    borderColor: NAVY,
  },
  chipText: {
    fontSize: 13,
    fontWeight: '700',
    lineHeight: 18,
    color: NAVY,
    textAlign: 'center',
    flexShrink: 1,
  },
  chipTextCompact: {
    fontSize: 12.5,
  },
  chipTextActive: {
    color: CARD,
  },

  /* errors */
  errorText: {
    color: '#dc2626',
    fontSize: 14,
    marginBottom: 16,
  },

  /* empty state */
  emptyState: {
    alignItems: 'center',
    backgroundColor: CARD,
    borderRadius: 22,
    marginTop: 8,
    padding: 28,
    shadowColor: '#8a9bbd',
    shadowOffset: {width: 0, height: 4},
    shadowOpacity: 0.08,
    shadowRadius: 12,
    elevation: 3,
  },
  emptyIcon: {
    backgroundColor: BG,
    borderRadius: 999,
    height: 52,
    marginBottom: 14,
    width: 52,
  },
  emptyTitle: {
    color: NAVY,
    fontSize: 18,
    fontWeight: '800',
    marginBottom: 6,
  },
  emptyText: {
    color: MUTED,
    fontSize: 14,
    lineHeight: 21,
    textAlign: 'center',
  },

  /* list */
  listContent: {
    paddingBottom: 16,
  },

  /* card */
  card: {
    backgroundColor: CARD,
    borderRadius: 20,
    padding: 18,
    marginBottom: 14,
    shadowColor: '#8a9bbd',
    shadowOffset: {width: 0, height: 4},
    shadowOpacity: 0.10,
    shadowRadius: 14,
    elevation: 4,
  },

  /* badges */
  badgeRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 12,
  },
  badge: {
    paddingHorizontal: 12,
    paddingVertical: 4,
    borderRadius: 12,
  },
  badgeText: {
    fontSize: 12,
    fontWeight: '700',
  },

  /* card info */
  quoteId: {
    fontSize: 16,
    fontWeight: '900',
    color: NAVY,
    marginBottom: 2,
  },
  dateLine: {
    fontSize: 13,
    color: MUTED,
    marginBottom: 10,
  },
  costLine: {
    fontSize: 14,
    color: MUTED,
    marginBottom: 2,
  },
  costValue: {
    fontWeight: '800',
    color: NAVY,
  },
  roiLine: {
    fontSize: 14,
    color: MUTED,
    marginBottom: 14,
  },

  /* action buttons */
  actionRow: {
    flexDirection: 'row',
    gap: 10,
  },
  btnSecondary: {
    flex: 1,
    paddingVertical: 12,
    borderRadius: 24,
    borderWidth: 1.5,
    borderColor: NAVY,
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: CARD,
  },
  btnSecondaryText: {
    fontSize: 14,
    fontWeight: '800',
    color: NAVY,
  },
  btnPrimary: {
    flex: 1,
    paddingVertical: 12,
    borderRadius: 24,
    backgroundColor: GOLD,
    alignItems: 'center',
    justifyContent: 'center',
    shadowColor: GOLD,
    shadowOffset: {width: 0, height: 3},
    shadowOpacity: 0.25,
    shadowRadius: 8,
    elevation: 3,
  },
  btnPrimaryText: {
    fontSize: 14,
    fontWeight: '900',
    color: NAVY,
  },

  /* loading */
  loadingText: {color: MUTED, fontSize: 14, marginTop: 12},

});
