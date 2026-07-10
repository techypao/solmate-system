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

import CustomerBottomNav from '../src/components/CustomerBottomNav';
import {ApiError} from '../src/services/api';
import {
  getServiceRequests,
  getTechnicianServiceRequests,
  ServiceRequest,
} from '../src/services/serviceRequestApi';

/* ── design tokens ── */

const NAVY = '#1A2B55';
const GOLD = '#F5C000';
const MUTED = '#6B7A99';
const BG = '#C8D8F0';
const CARD = '#ffffff';
const DIVIDER = '#D4E0F2';

/* ── helpers (preserved) ── */

function formatDate(value?: string | null, fallback = 'Not specified') {
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

function formatStatusLabel(status?: string | null) {
  switch ((status || 'pending').toLowerCase()) {
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
    case 'pending':
    default:
      return 'Pending';
  }
}

function getFriendlyErrorMessage(error: unknown, requestCategory = 'maintenance') {
  if (error instanceof ApiError) {
    if (error.status === 401) {
      return 'Your session has expired. Please log in again.';
    }
    return error.message;
  }
  return requestCategory === 'installation'
    ? 'Could not load your installation requests right now.'
    : 'Could not load your maintenance requests right now.';
}

function getStatusBadgeStyle(status?: string | null) {
  switch ((status || 'pending').toLowerCase()) {
    case 'assigned':
      return {backgroundColor: '#FFF7CC', textColor: '#b45309'};
    case 'in_progress':
      return {backgroundColor: '#EAF9FD', textColor: '#1d4ed8'};
    case 'completed':
      return {backgroundColor: '#dcfce7', textColor: '#166534'};
    case 'cancelled':
    case 'declined':
      return {backgroundColor: '#fee2e2', textColor: '#b91c1c'};
    case 'pending':
    default:
      return {backgroundColor: '#e8ecf4', textColor: MUTED};
  }
}

function normalizeServiceRequest(item: ServiceRequest): ServiceRequest {
  return {
    ...item,
    status: item.status || 'pending',
    technician_marked_done_at: item.technician_marked_done_at || null,
  };
}

function getInstallationType(item: ServiceRequest) {
  if (item.service_request_option_label) {
    return item.service_request_option_label;
  }

  if (item.service_request_option?.label) {
    return item.service_request_option.label;
  }

  const details = item.details || '';
  const match = details.match(/Installation Type:\s*(.+)/i);
  if (match?.[1]) {
    return match[1].trim();
  }

  return item.request_type;
}

function getMaintenanceConcern(item: ServiceRequest) {
  if (item.service_request_option_label) {
    return item.service_request_option_label;
  }

  if (item.service_request_option?.label) {
    return item.service_request_option.label;
  }

  const details = item.details || '';
  const match = details.match(/Maintenance Concern:\s*(.+)/i);
  if (match?.[1]) {
    return match[1].trim();
  }

  return item.request_type;
}

/* ════════════════════════════════════════════
   Main screen
   ════════════════════════════════════════════ */

export default function ServiceRequestListScreen({navigation, route}: any) {
  const mode = route?.params?.mode === 'technician' ? 'technician' : 'customer';
  const requestCategory =
    route?.params?.requestCategory === 'installation'
      ? 'installation'
      : 'maintenance';
  const [serviceRequests, setServiceRequests] = useState<ServiceRequest[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [errorMessage, setErrorMessage] = useState('');

  const loadServiceRequests = useCallback(
    async (showLoadingState = false) => {
      try {
        if (showLoadingState) setLoading(true);
        setErrorMessage('');
        const data =
          mode === 'technician'
            ? await getTechnicianServiceRequests()
            : await getServiceRequests();
        const normalizedData = Array.isArray(data)
          ? data.map(normalizeServiceRequest)
          : [];

        setServiceRequests(
          mode === 'customer'
            ? normalizedData.filter(
                item =>
                  (item.request_type || '').toLowerCase() === requestCategory,
              )
            : normalizedData,
        );
      } catch (error) {
        setServiceRequests([]);
        setErrorMessage(getFriendlyErrorMessage(error, requestCategory));
      } finally {
        setLoading(false);
        setRefreshing(false);
      }
    },
    [mode, requestCategory],
  );

  useFocusEffect(
    useCallback(() => {
      loadServiceRequests(true);
    }, [loadServiceRequests]),
  );

  const handleRefresh = () => {
    setRefreshing(true);
    loadServiceRequests(false);
  };

  /* ── card renderer ── */

  const renderServiceRequest = ({item}: {item: ServiceRequest}) => {
    const statusStyle = getStatusBadgeStyle(item.status);
    const customerName = item.customer?.name || 'Customer not available';
    const awaitingAdminReview =
      !!item.technician_marked_done_at && item.status !== 'completed';
    const title =
      mode === 'customer' && (item.request_type || '').toLowerCase() === 'maintenance'
        ? getMaintenanceConcern(item)
        : mode === 'customer' &&
            (item.request_type || '').toLowerCase() === 'installation'
          ? getInstallationType(item)
        : item.request_type;
    const isInstallationCustomerView =
      mode === 'customer' && requestCategory === 'installation';
    const requestLabel = isInstallationCustomerView
      ? 'Installation request'
      : 'Maintenance request';
    const actionLabel = isInstallationCustomerView
      ? 'View Installation Details'
      : 'View Maintenance Details';

    return (
      <View style={s.card}>
        {/* accent bar */}
        <View style={s.cardAccent} />

        {/* header row: title + status */}
        <View style={s.cardHeader}>
          <View style={s.cardTitleWrap}>
            <Text style={s.cardEyebrow}>
              {mode === 'technician'
                ? `Service request #${item.id}`
                : `${requestLabel} #${item.id}`}
            </Text>
            <Text style={s.cardTitle}>{title}</Text>
            {mode === 'technician' ? (
              <Text style={s.cardSubTitle}>{customerName}</Text>
            ) : null}
          </View>

          <View style={s.statusWrap}>
            <Text style={s.statusLabel}>Status</Text>
            <View
              style={[
                s.statusBadge,
                {backgroundColor: statusStyle.backgroundColor},
              ]}>
              <Text
                style={[
                  s.statusBadgeText,
                  {color: statusStyle.textColor},
                ]}>
                {formatStatusLabel(item.status)}
              </Text>
            </View>
          </View>
        </View>

        {/* details */}
        <View style={s.detailsCard}>
          <Text style={s.detailsLabel}>Details</Text>
          <Text style={s.detailsText}>{item.details}</Text>
        </View>

        {/* admin review notice */}
        {awaitingAdminReview ? (
          <View style={s.reviewCard}>
            <Text style={s.reviewTitle}>Awaiting admin confirmation</Text>
            <Text style={s.reviewText}>
              {mode === 'technician'
                ? 'You already marked this service as done. The admin will confirm the final official status.'
                : 'The technician reported the work as done. The admin still needs to confirm the final official status.'}
            </Text>
          </View>
        ) : null}

        {/* meta grid */}
        <View style={s.metaGrid}>
          <View style={s.metaCard}>
            <Text style={s.metaLabel}>Date needed</Text>
            <Text style={s.metaValue}>
              {formatDate(item.date_needed, 'Not specified')}
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
            <Text style={s.metaValue}>
              {formatDateTime(item.created_at)}
            </Text>
          </View>
        </View>

        {/* action button */}
        <Pressable
          onPress={() =>
            navigation.navigate(
              mode === 'technician'
                ? 'TechnicianServiceRequestDetail'
                : 'ServiceRequestDetail',
              {
                serviceRequestId: item.id,
                initialServiceRequest: item,
                mode,
                requestCategory,
              },
            )
          }
          style={({pressed}) => [s.cardBtn, pressed && s.pressed]}>
          <Text style={s.cardBtnText}>
            {mode === 'technician'
              ? 'Open Service Request'
              : actionLabel}
          </Text>
        </Pressable>
      </View>
    );
  };

  /* ── loading state ── */

  if (loading) {
    return (
      <SafeAreaView style={s.safe}>
        <View style={s.centered}>
          <ActivityIndicator color={GOLD} size="large" />
          <Text style={s.loadingText}>
            {requestCategory === 'installation'
              ? 'Loading your installation requests…'
              : 'Loading your maintenance requests…'}
          </Text>
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
        <Text style={s.title}>
          {mode === 'technician'
            ? 'Service Requests'
            : requestCategory === 'installation'
              ? 'My Installation Requests'
              : 'My Maintenance Requests'}
        </Text>
        <Text style={s.subtitle}>
          {mode === 'technician'
            ? 'Review service requests assigned to your technician account and pull down to refresh their latest status.'
            : requestCategory === 'installation'
              ? 'Review your submitted installation requests and pull down to refresh their latest status.'
              : 'Review your submitted maintenance requests and pull down to refresh their latest status.'}
        </Text>
      </View>

      {errorMessage ? (
        <View style={s.errorCard}>
          <Text style={s.errorTitle}>Something went wrong</Text>
          <Text style={s.errorText}>{errorMessage}</Text>
          <Pressable
            onPress={() => loadServiceRequests(true)}
            style={({pressed}) => [s.retryBtn, pressed && s.pressed]}>
            <Text style={s.retryBtnText}>Try Again</Text>
          </Pressable>
        </View>
      ) : (
        <FlatList
          contentContainerStyle={[
            s.listContent,
            serviceRequests.length === 0 ? s.emptyListContent : null,
          ]}
          data={serviceRequests}
          keyExtractor={item => item.id.toString()}
          renderItem={renderServiceRequest}
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
                <Text style={s.emptyIconText}>{'\uD83D\uDCCB'}</Text>
              </View>
              <Text style={s.emptyTitle}>
                {mode === 'technician'
                  ? 'No assigned service requests yet.'
                  : requestCategory === 'installation'
                    ? 'No installation requests yet.'
                    : 'No maintenance requests yet.'}
              </Text>
              <Text style={s.emptyText}>
                {mode === 'technician'
                  ? 'Assigned service requests will appear here once they are linked to your technician account.'
                  : requestCategory === 'installation'
                    ? 'Submit your first installation request from Services and it will appear here.'
                    : 'Submit your first maintenance request from Services and it will appear here.'}
              </Text>
              {mode === 'customer' ? (
                <Pressable
                  onPress={() =>
                    navigation.navigate(
                      requestCategory === 'installation'
                        ? 'InstallationRequest'
                        : 'ServiceRequest',
                    )
                  }
                  style={({pressed}) => [s.emptyBtn, pressed && s.pressed]}>
                  <Text style={s.emptyBtnText}>
                    {requestCategory === 'installation'
                      ? 'Request Installation'
                      : 'Request Maintenance'}
                  </Text>
                </Pressable>
              ) : null}
            </View>
          }
        />
      )}

      <CustomerBottomNav activeTab="Services" />
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
  loadingText: {
    color: MUTED,
    fontSize: 14,
    marginTop: 14,
  },

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
    shadowOpacity: 0.10,
    shadowRadius: 14,
    elevation: 4,
  },
  errorTitle: {
    color: '#b91c1c', fontSize: 18, fontWeight: '800', marginBottom: 8,
  },
  errorText: {
    color: '#991b1b', fontSize: 14, lineHeight: 20,
  },
  retryBtn: {
    marginTop: 16,
    backgroundColor: GOLD,
    borderRadius: 28,
    paddingVertical: 14,
    alignItems: 'center',
  },
  retryBtnText: {
    fontSize: 15, fontWeight: '900', color: CARD, letterSpacing: 0.3,
  },

  /* list */
  listContent: {
    paddingHorizontal: 22,
    paddingBottom: 90,
  },
  emptyListContent: {
    flexGrow: 1,
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
    shadowOpacity: 0.10,
    shadowRadius: 14,
    elevation: 4,
  },
  emptyIcon: {
    backgroundColor: '#f0edff',
    borderRadius: 999,
    height: 56,
    width: 56,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 16,
  },
  emptyIconText: {fontSize: 26},
  emptyTitle: {
    color: NAVY, fontSize: 18, fontWeight: '800', marginBottom: 8,
  },
  emptyText: {
    color: MUTED, fontSize: 14, lineHeight: 21, textAlign: 'center',
  },
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
    shadowOpacity: 0.10,
    shadowRadius: 14,
    elevation: 4,
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
  cardTitleWrap: {
    flex: 1,
    paddingRight: 14,
  },
  cardEyebrow: {
    color: MUTED,
    fontSize: 12,
    fontWeight: '700',
    letterSpacing: 0.4,
    marginBottom: 4,
    textTransform: 'uppercase',
  },
  cardTitle: {
    color: NAVY,
    fontSize: 18,
    fontWeight: '800',
    lineHeight: 24,
  },
  cardSubTitle: {
    color: MUTED,
    fontSize: 13,
    fontWeight: '600',
    lineHeight: 18,
    marginTop: 6,
  },
  statusWrap: {
    alignItems: 'flex-end',
    flexShrink: 0,
  },
  statusLabel: {
    color: MUTED,
    fontSize: 11,
    fontWeight: '700',
    letterSpacing: 0.4,
    marginBottom: 6,
    textTransform: 'uppercase',
  },
  statusBadge: {
    borderRadius: 999,
    minWidth: 96,
    paddingHorizontal: 12,
    paddingVertical: 7,
  },
  statusBadgeText: {
    fontSize: 12,
    fontWeight: '700',
    letterSpacing: 0.4,
    textAlign: 'center',
  },
  detailsCard: {
    backgroundColor: '#f7f9fc',
    borderColor: DIVIDER,
    borderRadius: 16,
    borderWidth: 1,
    marginBottom: 14,
    padding: 14,
  },
  detailsLabel: {
    color: NAVY,
    fontSize: 12,
    fontWeight: '700',
    marginBottom: 6,
    textTransform: 'uppercase',
  },
  detailsText: {
    color: NAVY,
    fontSize: 14,
    lineHeight: 21,
    opacity: 0.85,
  },
  reviewCard: {
    backgroundColor: '#fff7ed',
    borderColor: '#fed7aa',
    borderRadius: 16,
    borderWidth: 1,
    marginBottom: 14,
    padding: 14,
  },
  reviewTitle: {
    color: '#9a3412',
    fontSize: 14,
    fontWeight: '700',
    marginBottom: 4,
  },
  reviewText: {
    color: '#9a3412',
    fontSize: 13,
    lineHeight: 19,
  },
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
    fontSize: 11,
    fontWeight: '700',
    marginBottom: 6,
    textTransform: 'uppercase',
  },
  metaValue: {
    color: NAVY,
    fontSize: 14,
    fontWeight: '700',
    lineHeight: 20,
  },
  cardBtn: {
    backgroundColor: CARD,
    borderRadius: 28,
    paddingVertical: 14,
    alignItems: 'center',
    borderWidth: 1,
    borderColor: DIVIDER,
  },
  cardBtnText: {
    fontSize: 15,
    fontWeight: '800',
    color: NAVY,
  },

});
