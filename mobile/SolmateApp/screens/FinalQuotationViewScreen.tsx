import React, {useCallback, useState} from 'react';
import {
  ActivityIndicator,
  SafeAreaView,
  ScrollView,
  StyleSheet,
  Text,
  View,
} from 'react-native';
import {useFocusEffect} from '@react-navigation/native';

import {AppButton, AppCard} from '../components';
import {ApiError} from '../src/services/api';
import {getCustomerFinalQuotation, Quotation} from '../src/services/quotationApi';

function formatValue(value?: string | number | null) {
  if (value === null || value === undefined || value === '') {
    return 'N/A';
  }

  return String(value);
}

function formatCurrency(value?: number | null) {
  if (value === null || value === undefined || Number.isNaN(value)) {
    return 'N/A';
  }

  return `PHP ${value.toFixed(2)}`;
}

function formatBoolean(value?: boolean | null) {
  if (value === null || value === undefined) {
    return 'N/A';
  }

  return value ? 'Yes' : 'No';
}

function formatDate(value?: string | null) {
  if (!value) {
    return 'N/A';
  }

  const date = new Date(value);

  if (Number.isNaN(date.getTime())) {
    return value;
  }

  return date.toLocaleString();
}

function getFriendlyErrorMessage(error: unknown) {
  if (error instanceof ApiError) {
    if (error.status === 404) {
      return 'No final quotation has been submitted for this inspection request yet.';
    }

    return error.message;
  }

  return 'Could not load the final quotation right now.';
}

function DetailRow({
  label,
  value,
}: {
  label: string;
  value: string;
}) {
  return (
    <View style={styles.detailRow}>
      <Text style={styles.detailLabel}>{label}</Text>
      <Text style={styles.detailValue}>{value}</Text>
    </View>
  );
}

function SectionCard({
  title,
  subtitle,
  children,
}: {
  title: string;
  subtitle: string;
  children: React.ReactNode;
}) {
  return (
    <AppCard style={styles.sectionCard}>
      <Text style={styles.sectionTitle}>{title}</Text>
      <Text style={styles.sectionSubtitle}>{subtitle}</Text>
      {children}
    </AppCard>
  );
}

export default function FinalQuotationViewScreen({navigation, route}: any) {
  const inspectionRequestId = route?.params?.inspectionRequestId;

  const [quotation, setQuotation] = useState<Quotation | null>(null);
  const [loading, setLoading] = useState(true);
  const [errorMessage, setErrorMessage] = useState('');

  const loadQuotation = useCallback(async (showLoadingState = false) => {
    if (!inspectionRequestId) {
      setQuotation(null);
      setErrorMessage('No inspection request ID was provided.');
      setLoading(false);
      return;
    }

    try {
      if (showLoadingState) {
        setLoading(true);
      }

      setErrorMessage('');
      const data = await getCustomerFinalQuotation(inspectionRequestId);
      setQuotation(data);
    } catch (error) {
      setQuotation(null);
      setErrorMessage(getFriendlyErrorMessage(error));
    } finally {
      setLoading(false);
    }
  }, [inspectionRequestId]);

  useFocusEffect(
    useCallback(() => {
      loadQuotation(true);
    }, [loadQuotation]),
  );

  if (loading) {
    return (
      <View style={styles.centeredContainer}>
        <ActivityIndicator size="large" color="#2563eb" />
        <Text style={styles.loadingText}>Loading final quotation...</Text>
      </View>
    );
  }

  if (errorMessage || !quotation) {
    return (
      <View style={styles.centeredContainer}>
        <Text style={styles.errorTitle}>Final quotation unavailable</Text>
        <Text style={styles.errorText}>
          {errorMessage || 'No final quotation was returned for this request.'}
        </Text>
        <AppButton
          title="Go back"
          onPress={() => navigation.goBack()}
          style={styles.errorButton}
        />
      </View>
    );
  }

  return (
    <SafeAreaView style={styles.safeArea}>
      <ScrollView
        contentContainerStyle={styles.container}
        showsVerticalScrollIndicator={false}>
        <View style={styles.heroCard}>
          <Text style={styles.heroEyebrow}>Customer final quotation</Text>
          <Text style={styles.heroTitle}>
            Inspection request #{inspectionRequestId}
          </Text>
          <Text style={styles.heroSubtitle}>
            This screen is view-only and shows the final quotation submitted by
            the assigned technician.
          </Text>
          <Text style={styles.heroMeta}>Created {formatDate(quotation.created_at)}</Text>
        </View>

        <SectionCard
          title="Quotation overview"
          subtitle="Top-level quotation identifiers and status.">
          <DetailRow
            label="Quotation type"
            value={formatValue(quotation.quotation_type)}
          />
          <DetailRow label="Status" value={formatValue(quotation.status)} />
          <DetailRow
            label="Remarks"
            value={formatValue(quotation.remarks)}
          />
        </SectionCard>

        <SectionCard
          title="Energy inputs"
          subtitle="Customer usage inputs and solar sizing assumptions used by the backend.">
          <DetailRow
            label="Monthly electric bill"
            value={formatCurrency(quotation.monthly_electric_bill)}
          />
          <DetailRow
            label="Rate per kWh"
            value={formatValue(quotation.rate_per_kwh)}
          />
          <DetailRow
            label="Days in month"
            value={formatValue(quotation.days_in_month)}
          />
          <DetailRow
            label="Sun hours"
            value={formatValue(quotation.sun_hours)}
          />
          <DetailRow
            label="PV safety factor"
            value={formatValue(quotation.pv_safety_factor)}
          />
          <DetailRow
            label="Battery factor"
            value={formatValue(quotation.battery_factor)}
          />
          <DetailRow
            label="Battery voltage"
            value={formatValue(quotation.battery_voltage)}
          />
          <DetailRow
            label="PV system type"
            value={formatValue(quotation.pv_system_type)}
          />
          <DetailRow
            label="With battery"
            value={formatBoolean(quotation.with_battery)}
          />
          <DetailRow
            label="Inverter type"
            value={formatValue(quotation.inverter_type)}
          />
          <DetailRow
            label="Battery model"
            value={formatValue(quotation.battery_model)}
          />
          <DetailRow
            label="Battery capacity Ah"
            value={formatValue(quotation.battery_capacity_ah)}
          />
          <DetailRow
            label="Panel watts"
            value={formatValue(quotation.panel_watts)}
          />
        </SectionCard>

        <SectionCard
          title="Calculated system outputs"
          subtitle="The computed values returned by the backend for production and sizing.">
          <DetailRow
            label="Monthly kWh"
            value={formatValue(quotation.monthly_kwh)}
          />
          <DetailRow
            label="Daily kWh"
            value={formatValue(quotation.daily_kwh)}
          />
          <DetailRow
            label="pv_kw_raw"
            value={formatValue(quotation.pv_kw_raw)}
          />
          <DetailRow
            label="pv_kw_safe"
            value={formatValue(quotation.pv_kw_safe)}
          />
          <DetailRow
            label="Panel quantity"
            value={formatValue(quotation.panel_quantity)}
          />
          <DetailRow
            label="System kW"
            value={formatValue(quotation.system_kw)}
          />
          <DetailRow
            label="Battery required kWh"
            value={formatValue(quotation.battery_required_kwh)}
          />
          <DetailRow
            label="Battery required Ah"
            value={formatValue(quotation.battery_required_ah)}
          />
        </SectionCard>

        <SectionCard
          title="Cost breakdown"
          subtitle="Material, labor, and project pricing submitted by the technician.">
          <DetailRow
            label="Panel cost"
            value={formatCurrency(quotation.panel_cost)}
          />
          <DetailRow
            label="Inverter cost"
            value={formatCurrency(quotation.inverter_cost)}
          />
          <DetailRow
            label="Battery cost"
            value={formatCurrency(quotation.battery_cost)}
          />
          <DetailRow
            label="BOS cost"
            value={formatCurrency(quotation.bos_cost)}
          />
          <DetailRow
            label="Materials subtotal"
            value={formatCurrency(quotation.materials_subtotal)}
          />
          <DetailRow
            label="Labor cost"
            value={formatCurrency(quotation.labor_cost)}
          />
          <DetailRow
            label="Project cost"
            value={formatCurrency(quotation.project_cost)}
          />
        </SectionCard>

        <SectionCard
          title="Savings and ROI"
          subtitle="Projected savings and estimated payback returned by the backend.">
          <DetailRow
            label="Estimated monthly savings"
            value={formatCurrency(quotation.estimated_monthly_savings)}
          />
          <DetailRow
            label="Estimated annual savings"
            value={formatCurrency(quotation.estimated_annual_savings)}
          />
          <DetailRow label="ROI years" value={formatValue(quotation.roi_years)} />
        </SectionCard>
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safeArea: {
    backgroundColor: '#f5f7fb',
    flex: 1,
  },
  container: {
    padding: 20,
    paddingBottom: 28,
  },
  centeredContainer: {
    alignItems: 'center',
    backgroundColor: '#f5f7fb',
    flex: 1,
    justifyContent: 'center',
    padding: 24,
  },
  loadingText: {
    color: '#475569',
    fontSize: 14,
    marginTop: 12,
  },
  errorTitle: {
    color: '#0f172a',
    fontSize: 22,
    fontWeight: '800',
    marginBottom: 8,
    textAlign: 'center',
  },
  errorText: {
    color: '#b91c1c',
    fontSize: 14,
    lineHeight: 20,
    textAlign: 'center',
  },
  errorButton: {
    marginTop: 16,
    width: '100%',
  },
  heroCard: {
    backgroundColor: '#dbeafe',
    borderRadius: 28,
    marginBottom: 18,
    padding: 22,
  },
  heroEyebrow: {
    color: '#1d4ed8',
    fontSize: 12,
    fontWeight: '700',
    letterSpacing: 0.4,
    marginBottom: 8,
    textTransform: 'uppercase',
  },
  heroTitle: {
    color: '#0f172a',
    fontSize: 28,
    fontWeight: '800',
    lineHeight: 34,
    marginBottom: 10,
  },
  heroSubtitle: {
    color: '#334155',
    fontSize: 14,
    lineHeight: 21,
    marginBottom: 12,
  },
  heroMeta: {
    color: '#1e3a8a',
    fontSize: 13,
    fontWeight: '600',
  },
  sectionCard: {
    marginBottom: 18,
  },
  sectionTitle: {
    color: '#0f172a',
    fontSize: 20,
    fontWeight: '700',
    marginBottom: 6,
  },
  sectionSubtitle: {
    color: '#64748b',
    fontSize: 14,
    lineHeight: 20,
    marginBottom: 16,
  },
  detailRow: {
    borderTopColor: '#e2e8f0',
    borderTopWidth: 1,
    paddingVertical: 12,
  },
  detailLabel: {
    color: '#64748b',
    fontSize: 12,
    fontWeight: '700',
    marginBottom: 4,
    textTransform: 'uppercase',
  },
  detailValue: {
    color: '#0f172a',
    fontSize: 15,
    fontWeight: '600',
    lineHeight: 22,
  },
});
