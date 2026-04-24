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

import {StatusBadge} from '../components';
import {ApiError} from '../src/services/api';
import {
  getInspectionRequests,
  InspectionRequest,
} from '../src/services/inspectionRequestApi';

/* ── design tokens ── */

const NAVY = '#152a4a';
const GOLD = '#e8a800';
const MUTED = '#7b8699';
const BG = '#e0e8f5';
const CARD = '#ffffff';
const DIVIDER = '#edf1f7';

/* ── helpers (preserved) ── */

function formatDate(value?: string | null, fallback = 'Not provided') {
  if (!value) return fallback;
  const parsedDate = new Date(value);
  if (Number.isNaN(parsedDate.getTime())) return value;
  return parsedDate.toLocaleDateString();
}

function formatDateTime(value?: string) {
  if (!value) return 'Not available';
  const parsedDate = new Date(value);
  if (Number.isNaN(parsedDate.getTime())) return value;
  return parsedDate.toLocaleString();
}

function getFriendlyErrorMessage(error: unknown) {
  if (error instanceof ApiError) {
    if (error.status === 401) {
      return 'Your session has expired. Please log in again.';
    }
    return error.message;
  }
  return 'Could not load your inspection requests right now.';
}

/* ════════════════════════════════════════════
   Main screen
   ════════════════════════════════════════════ */

export default function InspectionRequestListScreen({navigation}: any) {
  const [inspectionRequests, setInspectionRequests] = useState<
    InspectionRequest[]
  >([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [errorMessage, setErrorMessage] = useState('');

  const loadInspectionRequests = useCallback(
    async (showLoadingState = false) => {
      try {
        if (showLoadingState) setLoading(true);
        setErrorMessage('');
        const data = await getInspectionRequests();
        setInspectionRequests(Array.isArray(data) ? data : []);
      } catch (error) {
        setInspectionRequests([]);
        setErrorMessage(getFriendlyErrorMessage(error));
      } finally {
        setLoading(false);
        setRefreshing(false);
      }
    },
    [],
  );

  useFocusEffect(
    useCallback(() => {
      loadInspectionRequests(true);
    }, [loadInspectionRequests]),
  );

  const handleRefresh = () => {
    setRefreshing(true);
    loadInspectionRequests(false);
  };

  /* ── card renderer ── */

  const renderInspectionRequest = ({item}: {item: InspectionRequest}) => {
    const canOpenFinalQuotation = item.status === 'completed';

    return (
      <View style={s.card}>
        {/* accent bar */}
        <View style={s.cardAccent} />

        {/* header row: title + status */}
        <View style={s.cardHeader}>
          <View style={s.cardTitleWrap}>
            <Text style={s.cardEyebrow}>Inspection request #{item.id}</Text>
            <Text style={s.cardTitle}>{item.details}</Text>
          </View>

          <StatusBadge status={item.status} />
        </View>

        {/* meta grid */}
        <View style={s.metaGrid}>
          <View style={s.metaCard}>
            <Text style={s.metaLabel}>Date needed</Text>
            <Text style={s.metaValue}>
              {formatDate(item.date_needed, 'Flexible')}
            </Text>
          </View>
          <View style={s.metaCard}>
            <Text style={s.metaLabel}>Address</Text>
            <Text style={s.metaValue}>{item.address || 'Not provided'}</Text>
          </View>
        </View>

        <View style={s.metaGrid}>
          <View style={s.metaCard}>
            <Text style={s.metaLabel}>Submitted</Text>
            <Text style={s.metaValue}>{formatDateTime(item.created_at)}</Text>
          </View>
        </View>

        {/* view details button */}
        <Pressable
          onPress={() =>
            navigation.navigate('InspectionRequestDetail', {
              inspectionRequestId: item.id,
              initialInspectionRequest: item,
            })
          }
          style={({pressed}) => [s.outlineBtn, pressed && s.pressed]}>
          <Text style={s.outlineBtnText}>View Request Details</Text>
        </Pressable>

        {/* final quotation section */}
        <View style={s.quotationSection}>
          <View style={s.quotationDivider} />
          <Text style={s.quotationTitle}>Final Quotation</Text>
          <Text style={s.quotationText}>
            {canOpenFinalQuotation
              ? 'Open the technician-submitted final quotation for this inspection request.'
              : 'The final quotation becomes viewable here after the inspection is completed.'}
          </Text>
          <Pressable
            disabled={!canOpenFinalQuotation}
            onPress={() =>
              navigation.navigate('FinalQuotationView', {
                inspectionRequestId: item.id,
              })
            }
            style={({pressed}) => [
              canOpenFinalQuotation ? s.primaryBtn : s.disabledBtn,
              pressed && canOpenFinalQuotation && s.pressed,
            ]}>
            <Text
              style={[
                canOpenFinalQuotation
                  ? s.primaryBtnText
                  : s.disabledBtnText,
              ]}>
              View Final Quotation
            </Text>
          </Pressable>
        </View>
      </View>
    );
  };

  /* ── loading state ── */

  if (loading) {
    return (
      <SafeAreaView style={s.safe}>
        <View style={s.centered}>
          <ActivityIndicator color={GOLD} size="large" />
          <Text style={s.loadingText}>Loading your inspection requests…</Text>
        </View>
      </SafeAreaView>
    );
  }

  /* ── main render ── */

  return (
    <SafeAreaView style={s.safe}>
      <View style={s.topBar}>
        {/* brand */}
        <Text style={s.brand}>
          Sol<Text style={s.brandAccent}>Mate</Text>
        </Text>

        {/* back */}
        <Pressable
          hitSlop={14}
          onPress={() => navigation.goBack()}
          style={({pressed}) => [s.backBtn, pressed && s.pressed]}>
          <Text style={s.backIcon}>{'\u2039'}</Text>
        </Pressable>

        {/* title block */}
        <Text style={s.title}>My Inspection Requests</Text>
        <Text style={s.subtitle}>
          Review inspection request progress and open the final quotation when
          the technician has completed the visit.
        </Text>
      </View>

      {errorMessage ? (
        <View style={s.errorCard}>
          <Text style={s.errorTitle}>Something went wrong</Text>
          <Text style={s.errorText}>{errorMessage}</Text>
          <Pressable
            onPress={() => loadInspectionRequests(true)}
            style={({pressed}) => [s.retryBtn, pressed && s.pressed]}>
            <Text style={s.retryBtnText}>Try Again</Text>
          </Pressable>
        </View>
      ) : (
        <FlatList
          contentContainerStyle={[
            s.listContent,
            inspectionRequests.length === 0 ? s.emptyListContent : null,
          ]}
          data={inspectionRequests}
          keyExtractor={item => item.id.toString()}
          renderItem={renderInspectionRequest}
          refreshControl={
            <RefreshControl
              onRefresh={handleRefresh}
              refreshing={refreshing}
              tintColor={GOLD}
            />
          }
          showsVerticalScrollIndicator={false}
          ListEmptyComponent={
            <View style={s.emptyState}>
              <View style={s.emptyIcon}>
                <Text style={s.emptyIconText}>{'\uD83D\uDD0D'}</Text>
              </View>
              <Text style={s.emptyTitle}>No inspection requests yet</Text>
              <Text style={s.emptyText}>
                Submit your first request from the customer dashboard and it
                will appear here.
              </Text>
              <Pressable
                onPress={() => navigation.navigate('InspectionRequest')}
                style={({pressed}) => [s.emptyBtn, pressed && s.pressed]}>
                <Text style={s.emptyBtnText}>Request Inspection</Text>
              </Pressable>
            </View>
          }
        />
      )}

      {/* ── bottom nav ── */}
      <View style={s.bottomNav}>
        <Pressable style={s.navItem} onPress={() => navigation.navigate('Home')}>
          <Text style={s.navIcon}>{'\uD83C\uDFE0'}</Text>
          <Text style={s.navLabel}>Home</Text>
        </Pressable>
        <Pressable style={s.navItem} onPress={() => navigation.navigate('QuotationList')}>
          <Text style={s.navIcon}>{'\uD83D\uDCCB'}</Text>
          <Text style={s.navLabel}>Quotation</Text>
        </Pressable>
        <Pressable style={s.navItem} onPress={() => navigation.navigate('ServicesHome')}>
          <Text style={s.navIcon}>{'\u2699\uFE0F'}</Text>
          <Text style={s.navLabel}>Services</Text>
        </Pressable>
        <Pressable style={s.navItem} onPress={() => navigation.navigate('TrackingHub')}>
          <Text style={s.navIconActive}>{'\uD83D\uDCCD'}</Text>
          <Text style={s.navLabelActive}>Tracking</Text>
        </Pressable>
        <Pressable style={s.navItem} onPress={() => navigation.navigate('CustomerSettings')}>
          <Text style={s.navIcon}>{'\uD83D\uDC64'}</Text>
          <Text style={s.navLabel}>Profile</Text>
        </Pressable>
      </View>
    </SafeAreaView>
  );
}

/* ── styles ── */

const s = StyleSheet.create({
  safe: {flex: 1, backgroundColor: BG},
  pressed: {opacity: 0.85},

  /* top bar */
  topBar: {paddingHorizontal: 22, paddingTop: 20},

  /* brand */
  brand: {fontSize: 22, fontWeight: '800', color: NAVY, marginBottom: 10},
  brandAccent: {color: GOLD},

  /* back */
  backBtn: {
    width: 40, height: 40, borderRadius: 20,
    backgroundColor: CARD,
    alignItems: 'center', justifyContent: 'center',
    marginBottom: 18,
    shadowColor: '#8a9bbd', shadowOffset: {width: 0, height: 2},
    shadowOpacity: 0.10, shadowRadius: 6, elevation: 3,
  },
  backIcon: {fontSize: 28, color: NAVY, fontWeight: '600', marginTop: -2},

  /* title */
  title: {fontSize: 26, fontWeight: '900', color: NAVY, marginBottom: 4},
  subtitle: {fontSize: 14, color: MUTED, lineHeight: 20, marginBottom: 16},

  /* centered / loading */
  centered: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    padding: 20,
  },
  loadingText: {color: MUTED, fontSize: 14, marginTop: 14},

  /* error */
  errorCard: {
    backgroundColor: CARD,
    borderRadius: 22,
    borderWidth: 1,
    borderColor: '#fecaca',
    marginHorizontal: 22,
    padding: 20,
    shadowColor: '#8a9bbd',
    shadowOffset: {width: 0, height: 4},
    shadowOpacity: 0.10, shadowRadius: 14, elevation: 4,
  },
  errorTitle: {color: '#b91c1c', fontSize: 18, fontWeight: '800', marginBottom: 8},
  errorText: {color: '#991b1b', fontSize: 14, lineHeight: 20},
  retryBtn: {
    marginTop: 16,
    backgroundColor: GOLD,
    borderRadius: 28,
    paddingVertical: 14,
    alignItems: 'center',
  },
  retryBtnText: {fontSize: 15, fontWeight: '900', color: CARD, letterSpacing: 0.3},

  /* list */
  listContent: {paddingHorizontal: 22, paddingBottom: 12},
  emptyListContent: {flexGrow: 1},

  /* empty state */
  emptyState: {
    alignItems: 'center',
    backgroundColor: CARD,
    borderRadius: 22,
    marginTop: 8,
    padding: 28,
    shadowColor: '#8a9bbd',
    shadowOffset: {width: 0, height: 4},
    shadowOpacity: 0.10, shadowRadius: 14, elevation: 4,
  },
  emptyIcon: {
    backgroundColor: '#f0edff',
    borderRadius: 999,
    height: 56, width: 56,
    alignItems: 'center', justifyContent: 'center',
    marginBottom: 16,
  },
  emptyIconText: {fontSize: 26},
  emptyTitle: {color: NAVY, fontSize: 18, fontWeight: '800', marginBottom: 8},
  emptyText: {color: MUTED, fontSize: 14, lineHeight: 21, textAlign: 'center'},
  emptyBtn: {
    marginTop: 18,
    width: '100%',
    backgroundColor: CARD,
    borderRadius: 28,
    paddingVertical: 14,
    alignItems: 'center',
    borderWidth: 1,
    borderColor: DIVIDER,
  },
  emptyBtnText: {fontSize: 15, fontWeight: '800', color: NAVY},

  /* ── card ── */
  card: {
    backgroundColor: CARD,
    borderRadius: 22,
    marginBottom: 14,
    overflow: 'hidden',
    padding: 18,
    shadowColor: '#8a9bbd',
    shadowOffset: {width: 0, height: 4},
    shadowOpacity: 0.10, shadowRadius: 14, elevation: 4,
  },
  cardAccent: {
    backgroundColor: GOLD,
    borderRadius: 999,
    height: 6,
    marginBottom: 14,
    width: 48,
  },
  cardHeader: {
    alignItems: 'flex-start',
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 14,
  },
  cardTitleWrap: {flex: 1, paddingRight: 14},
  cardEyebrow: {
    color: MUTED,
    fontSize: 12, fontWeight: '700',
    letterSpacing: 0.4, marginBottom: 4,
    textTransform: 'uppercase',
  },
  cardTitle: {
    color: NAVY,
    fontSize: 18, fontWeight: '800', lineHeight: 24,
  },

  /* meta grid */
  metaGrid: {
    flexDirection: 'row',
    gap: 10,
    marginBottom: 14,
  },
  metaCard: {
    backgroundColor: '#f7f9fc',
    borderRadius: 14,
    flex: 1,
    padding: 14,
  },
  metaLabel: {
    color: MUTED,
    fontSize: 11, fontWeight: '700',
    marginBottom: 6,
    textTransform: 'uppercase',
  },
  metaValue: {
    color: NAVY,
    fontSize: 14, fontWeight: '700', lineHeight: 20,
  },

  /* outline button (view details) */
  outlineBtn: {
    backgroundColor: CARD,
    borderRadius: 28,
    paddingVertical: 14,
    alignItems: 'center',
    borderWidth: 1,
    borderColor: DIVIDER,
    marginBottom: 14,
  },
  outlineBtnText: {fontSize: 15, fontWeight: '800', color: NAVY},

  /* quotation section */
  quotationSection: {
    backgroundColor: '#f7f9fc',
    borderRadius: 16,
    borderWidth: 1,
    borderColor: DIVIDER,
    padding: 16,
  },
  quotationDivider: {
    backgroundColor: DIVIDER,
    height: 1,
    marginBottom: 14,
  },
  quotationTitle: {
    fontSize: 15, fontWeight: '800', color: NAVY, marginBottom: 6,
  },
  quotationText: {
    fontSize: 13, color: MUTED, lineHeight: 19, marginBottom: 14,
  },

  /* primary button (final quotation available) */
  primaryBtn: {
    backgroundColor: GOLD,
    borderRadius: 28,
    paddingVertical: 14,
    alignItems: 'center',
    shadowColor: GOLD,
    shadowOffset: {width: 0, height: 4},
    shadowOpacity: 0.25, shadowRadius: 10, elevation: 4,
  },
  primaryBtnText: {fontSize: 15, fontWeight: '900', color: CARD, letterSpacing: 0.3},

  /* disabled button (final quotation not yet available) */
  disabledBtn: {
    backgroundColor: DIVIDER,
    borderRadius: 28,
    paddingVertical: 14,
    alignItems: 'center',
    opacity: 0.6,
  },
  disabledBtnText: {fontSize: 15, fontWeight: '800', color: MUTED},

  /* ── bottom nav ── */
  bottomNav: {
    flexDirection: 'row',
    justifyContent: 'space-around',
    backgroundColor: CARD,
    borderTopLeftRadius: 18,
    borderTopRightRadius: 18,
    paddingVertical: 10,
    paddingBottom: 14,
    shadowColor: '#8a9bbd',
    shadowOffset: {width: 0, height: -2},
    shadowOpacity: 0.08, shadowRadius: 8, elevation: 4,
  },
  navItem: {alignItems: 'center', paddingHorizontal: 6},
  navIcon: {fontSize: 20, marginBottom: 2},
  navIconActive: {fontSize: 20, marginBottom: 2},
  navLabel: {fontSize: 11, color: MUTED, fontWeight: '600'},
  navLabelActive: {fontSize: 11, color: NAVY, fontWeight: '700'},
});
