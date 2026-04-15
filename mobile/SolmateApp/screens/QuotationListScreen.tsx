import React, {useCallback, useEffect, useState} from 'react';
import {
  ActivityIndicator,
  FlatList,
  Pressable,
  StyleSheet,
  Text,
  View,
} from 'react-native';

import {ApiError} from '../src/services/api';
import {getQuotations} from '../src/services/quotationApi';

type Quotation = {
  id: number;
  user_id?: number;
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

function formatValue(value?: number | null) {
  if (value === null || value === undefined) {
    return 'N/A';
  }

  return String(value);
}

function formatCurrency(value?: number | null) {
  if (value === null || value === undefined || Number.isNaN(value)) {
    return 'N/A';
  }

  return `₱${value.toFixed(2)}`;
}

function formatYears(value?: number | null) {
  if (value === null || value === undefined || Number.isNaN(value)) {
    return 'N/A';
  }

  return `${value.toFixed(2)} years`;
}

function formatDate(value?: string) {
  if (!value) {
    return 'N/A';
  }

  const date = new Date(value);

  if (Number.isNaN(date.getTime())) {
    return value;
  }

  return date.toLocaleString();
}

function getStatusBadgeStyle(status?: string) {
  switch (status) {
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
    case 'completed':
      return {
        backgroundColor: '#dbeafe',
        textColor: '#1d4ed8',
      };
    default:
      return {
        backgroundColor: '#fef3c7',
        textColor: '#92400e',
      };
  }
}

export default function QuotationListScreen({navigation}: any) {
  const [quotations, setQuotations] = useState<Quotation[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [errorMessage, setErrorMessage] = useState('');

  const fetchQuotations = useCallback(
    async (showLoadingState = false) => {
      try {
        if (showLoadingState) {
          setLoading(true);
        }

        setErrorMessage('');
        const data = await getQuotations();
        const initialQuotations = Array.isArray(data)
          ? data.filter(item => item.quotation_type === 'initial')
          : [];

        setQuotations(initialQuotations);
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

  const renderQuotationItem = ({item}: {item: Quotation}) => {
    const statusStyle = getStatusBadgeStyle(item.status);

    return (
      <Pressable
        onPress={() =>
          navigation.navigate('QuotationDetail', {quotationId: item.id})
        }
        style={({pressed}) => [pressed && styles.pressed]}>
        <View style={styles.card}>
          <View style={styles.cardAccent} />
          <View style={styles.cardHeader}>
            <View style={styles.cardTitleWrap}>
              <Text style={styles.cardEyebrow}>Quotation #{item.id}</Text>
              <Text style={styles.cardTitle}>
                {item.quotation_type?.toUpperCase() || 'QUOTATION'}
              </Text>
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

          <View style={styles.metricRow}>
            <View style={styles.metricCard}>
              <Text style={styles.metricLabel}>Recommended</Text>
              <Text style={styles.metricValue}>
                {(item.pv_system_type || 'hybrid').toUpperCase()}
              </Text>
            </View>
            <View style={styles.metricCard}>
              <Text style={styles.metricLabel}>System summary</Text>
              <Text style={styles.metricValue}>{formatValue(item.system_kw)}</Text>
              <Text style={styles.metricHint}>
                {formatValue(item.panel_quantity)} panels
              </Text>
            </View>
          </View>

          <View style={styles.footerRow}>
            <View>
              <Text style={styles.footerLabel}>Estimated total</Text>
              <Text style={styles.footerValue}>
                {formatCurrency(item.project_cost)}
              </Text>
              <Text style={styles.footerSubValue}>
                ROI {formatYears(item.roi_years)}
              </Text>
            </View>
            <View style={styles.footerMetaWrap}>
              <Text style={styles.estimateTag}>Estimate only</Text>
              <Text style={styles.footerMeta}>{formatDate(item.created_at)}</Text>
              <Text style={styles.tapHint}>Tap for details</Text>
            </View>
          </View>
        </View>
      </Pressable>
    );
  };

  if (loading) {
    return (
      <View style={styles.centeredContainer}>
        <ActivityIndicator size="large" color="#2563eb" />
        <Text style={styles.loadingText}>Loading quotations...</Text>
      </View>
    );
  }

  return (
    <View style={styles.container}>
      <Text style={styles.title}>My Quotations</Text>
      <Text style={styles.subtitle}>
        Review the initial quotations you submitted and pull down to refresh the
        list.
      </Text>

      {errorMessage ? <Text style={styles.errorText}>{errorMessage}</Text> : null}

      {!errorMessage && quotations.length === 0 ? (
        <View style={styles.emptyState}>
          <View style={styles.emptyIcon} />
          <Text style={styles.emptyTitle}>No quotations yet</Text>
          <Text style={styles.emptyText}>
            Your quotation requests will appear here once you create them from
            the dashboard.
          </Text>
        </View>
      ) : null}

      {!errorMessage && quotations.length > 0 ? (
        <FlatList
          data={quotations}
          keyExtractor={item => item.id.toString()}
          renderItem={renderQuotationItem}
          contentContainerStyle={styles.listContent}
          showsVerticalScrollIndicator={false}
          refreshing={refreshing}
          onRefresh={handleRefresh}
        />
      ) : null}
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
  errorText: {
    color: '#dc2626',
    fontSize: 14,
    marginBottom: 16,
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
    backgroundColor: '#dbeafe',
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
  listContent: {
    paddingBottom: 24,
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
    backgroundColor: '#dbeafe',
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
    fontSize: 18,
    fontWeight: '800',
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
  metricRow: {
    flexDirection: 'row',
    gap: 12,
    marginBottom: 14,
  },
  metricCard: {
    backgroundColor: '#f8fafc',
    borderRadius: 16,
    flex: 1,
    padding: 14,
  },
  metricLabel: {
    color: '#64748b',
    fontSize: 12,
    fontWeight: '700',
    marginBottom: 6,
    textTransform: 'uppercase',
  },
  metricValue: {
    color: '#0f172a',
    fontSize: 18,
    fontWeight: '700',
  },
  metricHint: {
    color: '#64748b',
    fontSize: 12,
    marginTop: 4,
  },
  footerRow: {
    alignItems: 'flex-end',
    borderTopColor: '#e2e8f0',
    borderTopWidth: 1,
    flexDirection: 'row',
    justifyContent: 'space-between',
    paddingTop: 14,
  },
  footerLabel: {
    color: '#64748b',
    fontSize: 12,
    fontWeight: '700',
    marginBottom: 4,
    textTransform: 'uppercase',
  },
  footerValue: {
    color: '#0f172a',
    fontSize: 18,
    fontWeight: '700',
  },
  footerSubValue: {
    color: '#475569',
    fontSize: 12,
    fontWeight: '600',
    marginTop: 4,
  },
  footerMetaWrap: {
    alignItems: 'flex-end',
    marginLeft: 12,
  },
  estimateTag: {
    color: '#9a3412',
    fontSize: 12,
    fontWeight: '700',
    marginBottom: 6,
  },
  footerMeta: {
    color: '#64748b',
    fontSize: 12,
    marginBottom: 4,
    textAlign: 'right',
  },
  tapHint: {
    color: '#2563eb',
    fontSize: 13,
    fontWeight: '700',
  },
  pressed: {
    opacity: 0.88,
  },
});
