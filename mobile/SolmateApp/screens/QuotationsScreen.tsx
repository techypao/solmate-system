import React, {useCallback, useEffect, useState} from 'react';
import {Alert, ScrollView, StyleSheet, Text, View} from 'react-native';

import {AppButton, AppCard} from '../components';
import {ApiError} from '../src/services/api';
import {
  createQuotation,
  getQuotations,
  Quotation,
  updateQuotation,
} from '../src/services/quotationApi';

export default function QuotationsScreen({navigation}: any) {
  const [quotations, setQuotations] = useState<Quotation[]>([]);
  const [loading, setLoading] = useState(true);
  const [submitting, setSubmitting] = useState(false);
  const [errorMessage, setErrorMessage] = useState('');

  const loadQuotations = useCallback(async () => {
    try {
      setLoading(true);
      setErrorMessage('');

      const data = await getQuotations();
      setQuotations(data);
    } catch (error) {
      if (error instanceof ApiError) {
        setErrorMessage(error.message);
        return;
      }

      setErrorMessage('Could not load quotations.');
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    loadQuotations();
  }, [loadQuotations]);

  const handleCreateSampleQuotation = async () => {
    try {
      setSubmitting(true);

      await createQuotation({
        quotation_type: 'initial',
        monthly_electric_bill: 3500,
        pv_system_type: 'hybrid',
        with_battery: true,
        remarks: 'Created from the React Native quotations screen.',
      });

      Alert.alert('Success', 'Sample quotation created.');
      await loadQuotations();
    } catch (error) {
      if (error instanceof ApiError) {
        Alert.alert('Error', error.message);
        return;
      }

      Alert.alert('Error', 'Could not create quotation.');
    } finally {
      setSubmitting(false);
    }
  };

  const handleAdminExampleUpdate = async () => {
    if (quotations.length === 0) {
      Alert.alert('No quotations', 'Create or load a quotation first.');
      return;
    }

    try {
      setSubmitting(true);

      await updateQuotation(quotations[0].id, {
        status: 'approved',
        remarks: 'Updated from the React Native admin example.',
      });

      Alert.alert('Success', 'Quotation updated.');
      await loadQuotations();
    } catch (error) {
      if (error instanceof ApiError) {
        Alert.alert('Update failed', error.message);
        return;
      }

      Alert.alert('Update failed', 'Could not update quotation.');
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <ScrollView contentContainerStyle={styles.container}>
      <AppCard style={styles.card}>
        <Text style={styles.title}>Quotations</Text>
        <Text style={styles.subtitle}>
          This screen uses the shared API helpers for protected Laravel routes.
        </Text>

        {loading ? <Text style={styles.infoText}>Loading quotations...</Text> : null}

        {errorMessage ? <Text style={styles.errorText}>{errorMessage}</Text> : null}

        {!loading && quotations.length === 0 ? (
          <View style={styles.emptyState}>
            <Text style={styles.emptyTitle}>No quotations yet</Text>
            <Text style={styles.emptyText}>
              Tap the sample button below to send a protected POST request.
            </Text>
          </View>
        ) : null}

        {quotations.map(quotation => (
          <View key={quotation.id} style={styles.quotationCard}>
            <Text style={styles.quotationTitle}>
              Quotation #{quotation.id} • {quotation.quotation_type}
            </Text>
            <Text style={styles.quotationMeta}>Status: {quotation.status}</Text>
            <Text style={styles.quotationMeta}>
              Monthly bill: {quotation.monthly_electric_bill ?? 'N/A'}
            </Text>
            <Text style={styles.quotationMeta}>
              Remarks: {quotation.remarks || 'No remarks'}
            </Text>
          </View>
        ))}

        <AppButton
          disabled={submitting}
          title={submitting ? 'Submitting...' : 'Create Sample Quotation'}
          onPress={handleCreateSampleQuotation}
        />

        <AppButton
          disabled={submitting}
          style={styles.buttonSpacing}
          title="Admin PUT Example"
          variant="secondary"
          onPress={handleAdminExampleUpdate}
        />

        <AppButton
          style={styles.buttonSpacing}
          title="Refresh Quotations"
          variant="outline"
          onPress={loadQuotations}
        />

        <AppButton
          style={styles.buttonSpacing}
          title="Back to Home"
          variant="outline"
          onPress={() => navigation.navigate('Home')}
        />
      </AppCard>
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: {
    backgroundColor: '#f3f4f6',
    flexGrow: 1,
    justifyContent: 'center',
    padding: 20,
  },
  card: {
    paddingVertical: 20,
  },
  title: {
    color: '#0f172a',
    fontSize: 24,
    fontWeight: '700',
    marginBottom: 8,
  },
  subtitle: {
    color: '#475569',
    fontSize: 14,
    lineHeight: 20,
    marginBottom: 16,
  },
  infoText: {
    color: '#475569',
    fontSize: 14,
    marginBottom: 12,
  },
  errorText: {
    color: '#dc2626',
    fontSize: 14,
    marginBottom: 12,
  },
  emptyState: {
    backgroundColor: '#f8fafc',
    borderColor: '#e2e8f0',
    borderRadius: 16,
    borderWidth: 1,
    marginBottom: 16,
    padding: 16,
  },
  emptyTitle: {
    color: '#0f172a',
    fontSize: 16,
    fontWeight: '700',
    marginBottom: 8,
  },
  emptyText: {
    color: '#64748b',
    fontSize: 14,
    lineHeight: 20,
  },
  quotationCard: {
    backgroundColor: '#f8fafc',
    borderColor: '#e2e8f0',
    borderRadius: 16,
    borderWidth: 1,
    marginBottom: 12,
    padding: 14,
  },
  quotationTitle: {
    color: '#0f172a',
    fontSize: 16,
    fontWeight: '700',
    marginBottom: 6,
  },
  quotationMeta: {
    color: '#475569',
    fontSize: 14,
    lineHeight: 20,
  },
  buttonSpacing: {
    marginTop: 12,
  },
});
