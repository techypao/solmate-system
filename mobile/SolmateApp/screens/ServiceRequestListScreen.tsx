import React, { useCallback, useState } from 'react';
import {
  ActivityIndicator,
  FlatList,
  RefreshControl,
  StyleSheet,
  Text,
  View,
} from 'react-native';
import { useFocusEffect } from '@react-navigation/native';

import { AppButton } from '../components';
import { ApiError } from '../src/services/api';
import {
  getServiceRequests,
  ServiceRequest,
} from '../src/services/serviceRequestApi';

function formatDate(value?: string | null, fallback = 'Not specified') {
  if (!value) {
    return fallback;
  }

  const parsedDate = new Date(value);

  if (Number.isNaN(parsedDate.getTime())) {
    return value;
  }

  return parsedDate.toLocaleDateString();
}

function formatDateTime(value?: string) {
  if (!value) {
    return 'Not available';
  }

  const parsedDate = new Date(value);

  if (Number.isNaN(parsedDate.getTime())) {
    return value;
  }

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
    case 'pending':
    default:
      return 'Pending';
  }
}

function getFriendlyErrorMessage(error: unknown) {
  if (error instanceof ApiError) {
    if (error.status === 401) {
      return 'Your session has expired. Please log in again.';
    }

    return error.message;
  }

  return 'Could not load your service requests right now.';
}

function getStatusBadgeStyle(status?: string | null) {
  switch ((status || 'pending').toLowerCase()) {
    case 'assigned':
      return {
        backgroundColor: '#fef3c7',
        textColor: '#b45309',
      };
    case 'in_progress':
      return {
        backgroundColor: '#dbeafe',
        textColor: '#1d4ed8',
      };
    case 'completed':
      return {
        backgroundColor: '#dcfce7',
        textColor: '#166534',
      };
    case 'pending':
    default:
      return {
        backgroundColor: '#e2e8f0',
        textColor: '#475569',
      };
  }
}

function normalizeServiceRequest(item: ServiceRequest): ServiceRequest {
  return {
    ...item,
    status: item.status || 'pending',
  };
}

export default function ServiceRequestListScreen({ navigation }: any) {
  const [serviceRequests, setServiceRequests] = useState<ServiceRequest[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [errorMessage, setErrorMessage] = useState('');

  const loadServiceRequests = useCallback(async (showLoadingState = false) => {
    try {
      if (showLoadingState) {
        setLoading(true);
      }

      setErrorMessage('');
      const data = await getServiceRequests();
      setServiceRequests(
        Array.isArray(data) ? data.map(normalizeServiceRequest) : [],
      );
    } catch (error) {
      setServiceRequests([]);
      setErrorMessage(getFriendlyErrorMessage(error));
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, []);

  useFocusEffect(
    useCallback(() => {
      loadServiceRequests(true);
    }, [loadServiceRequests]),
  );

  const handleRefresh = () => {
    setRefreshing(true);
    loadServiceRequests(false);
  };

  const renderServiceRequest = ({ item }: { item: ServiceRequest }) => {
    const statusStyle = getStatusBadgeStyle(item.status);

    return (
      <View style={styles.card}>
        <View style={styles.cardAccent} />

        <View style={styles.cardHeader}>
          <View style={styles.cardTitleWrap}>
            <Text style={styles.cardEyebrow}>Service request #{item.id}</Text>
            <Text style={styles.cardTitle}>{item.request_type}</Text>
          </View>

          <View style={styles.statusWrap}>
            <Text style={styles.statusLabel}>Status</Text>
            <View
              style={[
                styles.statusBadge,
                { backgroundColor: statusStyle.backgroundColor },
              ]}
            >
              <Text
                style={[
                  styles.statusBadgeText,
                  { color: statusStyle.textColor },
                ]}
              >
                {formatStatusLabel(item.status)}
              </Text>
            </View>
          </View>
        </View>

        <View style={styles.detailsCard}>
          <Text style={styles.detailsLabel}>Details</Text>
          <Text style={styles.detailsText}>{item.details}</Text>
        </View>

        <View style={styles.metaGrid}>
          <View style={styles.metaCard}>
            <Text style={styles.metaLabel}>Date needed</Text>
            <Text style={styles.metaValue}>
              {formatDate(item.date_needed, 'Not specified')}
            </Text>
          </View>

          <View style={styles.metaCard}>
            <Text style={styles.metaLabel}>Submitted</Text>
            <Text style={styles.metaValue}>
              {formatDateTime(item.created_at)}
            </Text>
          </View>
        </View>
      </View>
    );
  };

  if (loading) {
    return (
      <View style={styles.centeredContainer}>
        <ActivityIndicator color="#d97706" size="large" />
        <Text style={styles.loadingText}>Loading your service requests...</Text>
      </View>
    );
  }

  return (
    <View style={styles.container}>
      <Text style={styles.title}>My Service Requests</Text>
      <Text style={styles.subtitle}>
        Review your submitted service requests and pull down to refresh their
        latest status.
      </Text>

      {errorMessage ? (
        <View style={styles.errorCard}>
          <Text style={styles.errorTitle}>Something went wrong</Text>
          <Text style={styles.errorText}>{errorMessage}</Text>
          <AppButton
            onPress={() => loadServiceRequests(true)}
            style={styles.retryButton}
            title="Try again"
          />
        </View>
      ) : (
        <FlatList
          contentContainerStyle={[
            styles.listContent,
            serviceRequests.length === 0 ? styles.emptyListContent : null,
          ]}
          data={serviceRequests}
          keyExtractor={item => item.id.toString()}
          renderItem={renderServiceRequest}
          refreshControl={
            <RefreshControl
              onRefresh={handleRefresh}
              refreshing={refreshing}
              tintColor="#d97706"
            />
          }
          showsVerticalScrollIndicator={false}
          ListEmptyComponent={
            <View style={styles.emptyState}>
              <View style={styles.emptyIcon} />
              <Text style={styles.emptyTitle}>No service requests yet.</Text>
              <Text style={styles.emptyText}>
                Submit your first request from the customer dashboard and it
                will appear here.
              </Text>
              <AppButton
                onPress={() => navigation.navigate('ServiceRequest')}
                style={styles.emptyButton}
                title="Request service"
                variant="outline"
              />
            </View>
          }
        />
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    backgroundColor: '#f5f7fb',
    flex: 1,
    padding: 20,
  },
  centeredContainer: {
    alignItems: 'center',
    backgroundColor: '#f5f7fb',
    flex: 1,
    justifyContent: 'center',
    padding: 20,
  },
  title: {
    color: '#0f172a',
    fontSize: 28,
    fontWeight: '800',
    marginBottom: 8,
  },
  subtitle: {
    color: '#64748b',
    fontSize: 14,
    lineHeight: 20,
    marginBottom: 18,
  },
  loadingText: {
    color: '#475569',
    fontSize: 14,
    marginTop: 12,
  },
  errorCard: {
    backgroundColor: '#ffffff',
    borderColor: '#fecaca',
    borderRadius: 22,
    borderWidth: 1,
    padding: 18,
  },
  errorTitle: {
    color: '#b91c1c',
    fontSize: 18,
    fontWeight: '700',
    marginBottom: 8,
  },
  errorText: {
    color: '#991b1b',
    fontSize: 14,
    lineHeight: 20,
  },
  retryButton: {
    marginTop: 16,
  },
  listContent: {
    paddingBottom: 24,
  },
  emptyListContent: {
    flexGrow: 1,
  },
  emptyState: {
    alignItems: 'center',
    backgroundColor: '#ffffff',
    borderColor: '#e2e8f0',
    borderRadius: 24,
    borderWidth: 1,
    marginTop: 8,
    padding: 28,
  },
  emptyIcon: {
    backgroundColor: '#fef3c7',
    borderRadius: 999,
    height: 56,
    marginBottom: 16,
    width: 56,
  },
  emptyTitle: {
    color: '#0f172a',
    fontSize: 20,
    fontWeight: '700',
    marginBottom: 8,
  },
  emptyText: {
    color: '#64748b',
    fontSize: 14,
    lineHeight: 21,
    textAlign: 'center',
  },
  emptyButton: {
    marginTop: 16,
    width: '100%',
  },
  card: {
    backgroundColor: '#ffffff',
    borderColor: '#e2e8f0',
    borderRadius: 22,
    borderWidth: 1,
    marginBottom: 14,
    overflow: 'hidden',
    padding: 18,
    shadowColor: '#0f172a',
    shadowOffset: {
      width: 0,
      height: 10,
    },
    shadowOpacity: 0.06,
    shadowRadius: 16,
    elevation: 2,
  },
  cardAccent: {
    backgroundColor: '#fed7aa',
    borderRadius: 999,
    height: 8,
    marginBottom: 14,
    width: 64,
  },
  cardHeader: {
    alignItems: 'flex-start',
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 14,
  },
  cardTitleWrap: {
    flex: 1,
    paddingRight: 16,
  },
  cardEyebrow: {
    color: '#94a3b8',
    fontSize: 12,
    fontWeight: '700',
    letterSpacing: 0.4,
    marginBottom: 4,
    textTransform: 'uppercase',
  },
  cardTitle: {
    color: '#0f172a',
    fontSize: 18,
    fontWeight: '800',
    lineHeight: 24,
  },
  statusWrap: {
    alignItems: 'flex-end',
    flexShrink: 0,
  },
  statusLabel: {
    color: '#94a3b8',
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
    backgroundColor: '#fffaf0',
    borderColor: '#ffedd5',
    borderRadius: 16,
    borderWidth: 1,
    marginBottom: 14,
    padding: 14,
  },
  detailsLabel: {
    color: '#9a3412',
    fontSize: 12,
    fontWeight: '700',
    marginBottom: 6,
    textTransform: 'uppercase',
  },
  detailsText: {
    color: '#334155',
    fontSize: 14,
    lineHeight: 21,
  },
  metaGrid: {
    flexDirection: 'row',
    gap: 12,
  },
  metaCard: {
    backgroundColor: '#f8fafc',
    borderRadius: 16,
    flex: 1,
    padding: 14,
  },
  metaLabel: {
    color: '#64748b',
    fontSize: 12,
    fontWeight: '700',
    marginBottom: 6,
    textTransform: 'uppercase',
  },
  metaValue: {
    color: '#0f172a',
    fontSize: 15,
    fontWeight: '600',
    lineHeight: 21,
  },
});
