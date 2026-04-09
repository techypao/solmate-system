import React, {useCallback, useState} from 'react';
import {
  ActivityIndicator,
  FlatList,
  RefreshControl,
  StyleSheet,
  Text,
  View,
} from 'react-native';
import {useFocusEffect} from '@react-navigation/native';

import {AppButton} from '../components';
import {ApiError} from '../src/services/api';
import {
  getInspectionRequests,
  InspectionRequest,
} from '../src/services/inspectionRequestApi';

function formatDate(value?: string | null, fallback = 'Not provided') {
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

function getFriendlyErrorMessage(error: unknown) {
  if (error instanceof ApiError) {
    if (error.status === 401) {
      return 'Your session has expired. Please log in again.';
    }

    return error.message;
  }

  return 'Could not load your inspection requests right now.';
}

function getStatusBadgeStyle(status?: string | null) {
  switch (status) {
    case 'completed':
      return {
        backgroundColor: '#dbeafe',
        textColor: '#1d4ed8',
      };
    case 'approved':
      return {
        backgroundColor: '#dcfce7',
        textColor: '#166534',
      };
    case 'rejected':
      return {
        backgroundColor: '#fee2e2',
        textColor: '#b91c1c',
      };
    default:
      return {
        backgroundColor: '#fef3c7',
        textColor: '#92400e',
      };
  }
}

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
        if (showLoadingState) {
          setLoading(true);
        }

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

  const renderInspectionRequest = ({item}: {item: InspectionRequest}) => {
    const statusStyle = getStatusBadgeStyle(item.status);

    return (
      <View style={styles.card}>
        <View style={styles.cardAccent} />

        <View style={styles.cardHeader}>
          <View style={styles.cardTitleWrap}>
            <Text style={styles.cardEyebrow}>Inspection request #{item.id}</Text>
            <Text style={styles.cardTitle}>{item.details}</Text>
          </View>

          <View
            style={[
              styles.statusBadge,
              {backgroundColor: statusStyle.backgroundColor},
            ]}>
            <Text style={[styles.statusBadgeText, {color: statusStyle.textColor}]}>
              {item.status || 'pending'}
            </Text>
          </View>
        </View>

        <View style={styles.metaGrid}>
          <View style={styles.metaCard}>
            <Text style={styles.metaLabel}>Date needed</Text>
            <Text style={styles.metaValue}>
              {formatDate(item.date_needed, 'Flexible')}
            </Text>
          </View>

          <View style={styles.metaCard}>
            <Text style={styles.metaLabel}>Submitted</Text>
            <Text style={styles.metaValue}>{formatDateTime(item.created_at)}</Text>
          </View>
        </View>
      </View>
    );
  };

  if (loading) {
    return (
      <View style={styles.centeredContainer}>
        <ActivityIndicator color="#16a34a" size="large" />
        <Text style={styles.loadingText}>Loading your inspection requests...</Text>
      </View>
    );
  }

  return (
    <View style={styles.container}>
      <Text style={styles.title}>My Inspection Requests</Text>
      <Text style={styles.subtitle}>
        View your submitted inspection requests and pull down to refresh the
        latest status.
      </Text>

      {errorMessage ? (
        <View style={styles.errorCard}>
          <Text style={styles.errorTitle}>Something went wrong</Text>
          <Text style={styles.errorText}>{errorMessage}</Text>
          <AppButton
            onPress={() => loadInspectionRequests(true)}
            style={styles.retryButton}
            title="Try again"
          />
        </View>
      ) : (
        <FlatList
          contentContainerStyle={[
            styles.listContent,
            inspectionRequests.length === 0 ? styles.emptyListContent : null,
          ]}
          data={inspectionRequests}
          keyExtractor={item => item.id.toString()}
          renderItem={renderInspectionRequest}
          refreshControl={
            <RefreshControl
              onRefresh={handleRefresh}
              refreshing={refreshing}
              tintColor="#16a34a"
            />
          }
          showsVerticalScrollIndicator={false}
          ListEmptyComponent={
            <View style={styles.emptyState}>
              <View style={styles.emptyIcon} />
              <Text style={styles.emptyTitle}>No inspection requests yet</Text>
              <Text style={styles.emptyText}>
                Submit your first request from the customer dashboard and it
                will appear here.
              </Text>
              <AppButton
                onPress={() => navigation.navigate('InspectionRequest')}
                style={styles.emptyButton}
                title="Request inspection"
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
    backgroundColor: '#dcfce7',
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
    backgroundColor: '#bbf7d0',
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
    paddingRight: 12,
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
    fontSize: 17,
    fontWeight: '700',
    lineHeight: 24,
  },
  statusBadge: {
    borderRadius: 999,
    paddingHorizontal: 12,
    paddingVertical: 8,
  },
  statusBadgeText: {
    fontSize: 11,
    fontWeight: '700',
    letterSpacing: 0.4,
    textTransform: 'uppercase',
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
