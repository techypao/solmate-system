import React, {useEffect, useState} from 'react';
import {
  ActivityIndicator,
  ScrollView,
  StyleSheet,
  Text,
  View,
} from 'react-native';

import {ApiError, apiGet} from '../src/services/api';
import {formatQuotationCurrency} from '../src/utils/currency';

type QuotationDetail = {
  id: number;
  quotation_type?: string | null;
  status?: string | null;
  monthly_electric_bill?: number | null;
  pv_system_type?: string | null;
  monthly_kwh?: number | null;
  daily_kwh?: number | null;
  pv_kw_raw?: number | null;
  pv_kw_safe?: number | null;
  panel_quantity?: number | null;
  system_kw?: number | null;
  battery_required_kwh?: number | null;
  battery_required_ah?: number | null;
  panel_cost?: number | null;
  inverter_cost?: number | null;
  battery_cost?: number | null;
  bos_cost?: number | null;
  materials_subtotal?: number | null;
  labor_cost?: number | null;
  project_cost?: number | null;
  estimated_monthly_savings?: number | null;
  estimated_annual_savings?: number | null;
  roi_years?: number | null;
  remarks?: string | null;
  created_at?: string | null;
};

function formatValue(value?: string | number | null) {
  if (value === null || value === undefined || value === '') {
    return 'N/A';
  }

  return String(value);
}

function formatReadableText(value?: string | null) {
  if (value === null || value === undefined || value.trim() === '') {
    return 'N/A';
  }

  return value
    .trim()
    .replace(/\s*,\s*/g, ', ')
    .replace(/(?:,\s*){2,}/g, ', ')
    .replace(/,\s*([.!?])/g, '$1')
    .replace(/\s{2,}/g, ' ');
}

function formatYears(value?: number | null) {
  if (value === null || value === undefined || Number.isNaN(value)) {
    return 'N/A';
  }

  return `${value.toFixed(2)} years`;
}

function formatSystemSummary(quotation: QuotationDetail) {
  const systemType = (quotation.pv_system_type || 'hybrid').toUpperCase();
  const parts = [`${systemType} system`];

  if (quotation.system_kw !== null && quotation.system_kw !== undefined) {
    parts.push(`${quotation.system_kw} kW`);
  }

  if (
    quotation.panel_quantity !== null &&
    quotation.panel_quantity !== undefined
  ) {
    parts.push(`${quotation.panel_quantity} panels`);
  }

  return parts.join(', ');
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

function getStatusBadgeStyle(status?: string | null) {
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

function SectionCard({
  title,
  subtitle,
  children,
}: {
  title: string;
  subtitle?: string;
  children: React.ReactNode;
}) {
  return (
    <View style={styles.sectionCard}>
      <Text style={styles.sectionTitle}>{title}</Text>
      {subtitle ? <Text style={styles.sectionSubtitle}>{subtitle}</Text> : null}
      {children}
    </View>
  );
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

function CostCard({
  label,
  value,
}: {
  label: string;
  value: string;
}) {
  return (
    <View style={styles.costCard}>
      <Text style={styles.costLabel}>{label}</Text>
      <Text style={styles.costValue}>{value}</Text>
    </View>
  );
}

export default function QuotationDetailScreen({route}: any) {
  const {quotationId} = route.params;
  const initialQuotation = route?.params?.initialQuotation as
    | QuotationDetail
    | undefined;

  const [quotation, setQuotation] = useState<QuotationDetail | null>(
    initialQuotation || null,
  );
  const [loading, setLoading] = useState(!initialQuotation);
  const [errorMessage, setErrorMessage] = useState('');

  useEffect(() => {
    const fetchQuotationDetail = async () => {
      try {
        setLoading(true);
        setErrorMessage('');

        const response = await apiGet<QuotationDetail>(
          `/quotations/${quotationId}`,
        );
        setQuotation(response);
      } catch (error) {
        if (error instanceof ApiError) {
          setErrorMessage(error.message);
        } else {
          setErrorMessage('Could not load quotation details.');
        }
      } finally {
        setLoading(false);
      }
    };

    fetchQuotationDetail();
  }, [quotationId]);

  if (loading) {
    return (
      <View style={styles.centeredContainer}>
        <ActivityIndicator size="large" color="#2563eb" />
        <Text style={styles.loadingText}>Loading quotation details...</Text>
      </View>
    );
  }

  if (errorMessage) {
    return (
      <View style={styles.centeredContainer}>
        <Text style={styles.errorTitle}>Could not load quotation</Text>
        <Text style={styles.errorText}>{errorMessage}</Text>
      </View>
    );
  }

  if (!quotation) {
    return (
      <View style={styles.centeredContainer}>
        <Text style={styles.errorTitle}>Quotation not found</Text>
        <Text style={styles.errorText}>
          No quotation details were returned for this item.
        </Text>
      </View>
    );
  }

  const statusStyle = getStatusBadgeStyle(quotation.status);
  const isInitialQuotation = quotation.quotation_type === 'initial';

  return (
    <ScrollView
      contentContainerStyle={styles.container}
      showsVerticalScrollIndicator={false}>
      <View style={styles.heroCard}>
        <View style={styles.heroTopRow}>
          <View style={styles.heroTextWrap}>
            <Text style={styles.heroEyebrow}>Quotation #{quotation.id}</Text>
            <Text style={styles.heroTitle}>
              {formatValue(quotation.quotation_type)}
            </Text>
          </View>

          <View
            style={[
              styles.statusBadge,
              {backgroundColor: statusStyle.backgroundColor},
            ]}>
            <Text style={[styles.statusBadgeText, {color: statusStyle.textColor}]}>
              {formatValue(quotation.status)}
            </Text>
          </View>
        </View>

        <Text style={styles.heroSubtitle}>
          Created {formatDate(quotation.created_at)}
        </Text>
      </View>

      <View style={styles.summaryStrip}>
        <View style={styles.summaryStripCard}>
          <Text style={styles.summaryStripLabel}>Monthly Bill</Text>
          <Text style={styles.summaryStripValue}>
            {formatQuotationCurrency(quotation.monthly_electric_bill)}
          </Text>
        </View>
        <View style={styles.summaryStripCard}>
          <Text style={styles.summaryStripLabel}>
            {isInitialQuotation ? 'Estimated Total' : 'Project Cost'}
          </Text>
          <Text style={styles.summaryStripValue}>
            {formatQuotationCurrency(quotation.project_cost)}
          </Text>
        </View>
      </View>

      <SectionCard
        title="Return on investment"
        subtitle={
          isInitialQuotation
            ? 'Estimated payback for the initial hybrid recommendation.'
            : 'Estimated savings and payback period based on the latest quotation values.'
        }>
        <View style={styles.roiCard}>
          {quotation.roi_years !== null && quotation.roi_years !== undefined ? (
            <>
              <View style={styles.roiHeader}>
                <Text style={styles.roiTitle}>ROI Overview</Text>
                <Text style={styles.roiValue}>
                  {formatYears(quotation.roi_years)}
                </Text>
              </View>

              {!isInitialQuotation ? (
                <View style={styles.roiMetricsRow}>
                  <View style={styles.roiMetricCard}>
                    <Text style={styles.roiMetricLabel}>
                      Estimated Monthly Savings
                    </Text>
                    <Text style={styles.roiMetricValue}>
                      {formatQuotationCurrency(quotation.estimated_monthly_savings)}
                    </Text>
                  </View>

                  <View style={styles.roiMetricCard}>
                    <Text style={styles.roiMetricLabel}>
                      Estimated Annual Savings
                    </Text>
                    <Text style={styles.roiMetricValue}>
                      {formatQuotationCurrency(quotation.estimated_annual_savings)}
                    </Text>
                  </View>
                </View>
              ) : null}
            </>
          ) : (
            <Text style={styles.roiUnavailableText}>ROI not available yet</Text>
          )}
        </View>
      </SectionCard>

      {isInitialQuotation ? (
        <>
          <SectionCard
            title="Recommended system summary"
            subtitle="This initial quotation keeps the recommendation high-level and package-based.">
            <DetailRow
              label="Recommended setup"
              value={formatSystemSummary(quotation)}
            />
            <DetailRow
              label="Monthly electric bill"
              value={formatQuotationCurrency(quotation.monthly_electric_bill)}
            />
          </SectionCard>

          <SectionCard
            title="Estimate notice"
            subtitle="What this initial quotation means for the customer.">
            <Text style={styles.noticeTitle}>Estimate only</Text>
            <Text style={styles.noticeText}>
              This initial quotation is a simplified estimate based on your
              monthly bill and the default hybrid recommendation. Final pricing,
              component selection, and installation scope are still subject to
              site inspection and technician confirmation.
            </Text>
          </SectionCard>
        </>
      ) : (
        <>
          <SectionCard
            title="Overview"
            subtitle="High-level request information and customer usage input.">
            <DetailRow
              label="Monthly electric bill"
              value={formatQuotationCurrency(quotation.monthly_electric_bill)}
            />
            <DetailRow label="Monthly kWh" value={formatValue(quotation.monthly_kwh)} />
            <DetailRow label="Daily kWh" value={formatValue(quotation.daily_kwh)} />
          </SectionCard>

          <SectionCard
            title="Solar sizing"
            subtitle="Calculated production and system sizing details.">
            <DetailRow label="PV kW raw" value={formatValue(quotation.pv_kw_raw)} />
            <DetailRow label="PV kW safe" value={formatValue(quotation.pv_kw_safe)} />
            <DetailRow
              label="Panel quantity"
              value={formatValue(quotation.panel_quantity)}
            />
            <DetailRow label="System kW" value={formatValue(quotation.system_kw)} />
          </SectionCard>

          <SectionCard
            title="Battery summary"
            subtitle="Battery sizing values returned by the backend.">
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
            title="Cost summary"
            subtitle="All cost values are shown exactly as returned by the backend.">
            <View style={styles.costGrid}>
              <CostCard
                label="Panel cost"
                value={formatQuotationCurrency(quotation.panel_cost)}
              />
              <CostCard
                label="Inverter cost"
                value={formatQuotationCurrency(quotation.inverter_cost)}
              />
              <CostCard
                label="Battery cost"
                value={formatQuotationCurrency(quotation.battery_cost)}
              />
              <CostCard
                label="BOS cost"
                value={formatQuotationCurrency(quotation.bos_cost)}
              />
              <CostCard
                label="Materials subtotal"
                value={formatQuotationCurrency(quotation.materials_subtotal)}
              />
              <CostCard
                label="Labor cost"
                value={formatQuotationCurrency(quotation.labor_cost)}
              />
            </View>

            <View style={styles.totalCard}>
              <Text style={styles.totalLabel}>Project cost</Text>
              <Text style={styles.totalValue}>
                {formatQuotationCurrency(quotation.project_cost)}
              </Text>
            </View>
          </SectionCard>
        </>
      )}

      <SectionCard title="Notes">
        <DetailRow label="Remarks" value={formatReadableText(quotation.remarks)} />
      </SectionCard>
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: {
    backgroundColor: '#f5f7fb',
    flexGrow: 1,
    padding: 20,
    paddingBottom: 32,
  },
  centeredContainer: {
    alignItems: 'center',
    backgroundColor: '#f5f7fb',
    flex: 1,
    justifyContent: 'center',
    padding: 24,
  },
  heroCard: {
    backgroundColor: '#ffffff',
    borderColor: '#e2e8f0',
    borderRadius: 24,
    borderWidth: 1,
    marginBottom: 16,
    padding: 20,
    shadowColor: '#0f172a',
    shadowOffset: {
      width: 0,
      height: 10,
    },
    shadowOpacity: 0.06,
    shadowRadius: 16,
    elevation: 2,
  },
  heroTopRow: {
    alignItems: 'flex-start',
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 10,
  },
  heroTextWrap: {
    flex: 1,
    paddingRight: 12,
  },
  heroEyebrow: {
    color: '#94a3b8',
    fontSize: 12,
    fontWeight: '700',
    letterSpacing: 0.5,
    marginBottom: 4,
    textTransform: 'uppercase',
  },
  heroTitle: {
    color: '#0f172a',
    fontSize: 28,
    fontWeight: '800',
    textTransform: 'capitalize',
  },
  heroSubtitle: {
    color: '#64748b',
    fontSize: 14,
    lineHeight: 20,
  },
  statusBadge: {
    borderRadius: 999,
    paddingHorizontal: 12,
    paddingVertical: 8,
  },
  statusBadgeText: {
    fontSize: 12,
    fontWeight: '700',
    textTransform: 'uppercase',
  },
  summaryStrip: {
    flexDirection: 'row',
    gap: 12,
    marginBottom: 16,
  },
  summaryStripCard: {
    backgroundColor: '#ffffff',
    borderColor: '#e2e8f0',
    borderRadius: 20,
    borderWidth: 1,
    flex: 1,
    padding: 16,
  },
  summaryStripLabel: {
    color: '#64748b',
    fontSize: 12,
    fontWeight: '700',
    marginBottom: 6,
    textTransform: 'uppercase',
  },
  summaryStripValue: {
    color: '#0f172a',
    fontSize: 20,
    fontWeight: '800',
  },
  roiCard: {
    backgroundColor: '#eff6ff',
    borderRadius: 20,
    padding: 18,
  },
  roiHeader: {
    alignItems: 'flex-start',
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 14,
  },
  roiTitle: {
    color: '#1e3a8a',
    fontSize: 18,
    fontWeight: '800',
  },
  roiValue: {
    color: '#0f172a',
    fontSize: 18,
    fontWeight: '800',
    textAlign: 'right',
  },
  roiMetricsRow: {
    gap: 12,
  },
  roiMetricCard: {
    backgroundColor: '#ffffff',
    borderRadius: 16,
    padding: 14,
  },
  roiMetricLabel: {
    color: '#64748b',
    fontSize: 12,
    fontWeight: '700',
    marginBottom: 6,
    textTransform: 'uppercase',
  },
  roiMetricValue: {
    color: '#0f172a',
    fontSize: 20,
    fontWeight: '800',
  },
  roiUnavailableText: {
    color: '#475569',
    fontSize: 16,
    fontWeight: '600',
    lineHeight: 22,
  },
  noticeTitle: {
    color: '#9a3412',
    fontSize: 18,
    fontWeight: '800',
    marginBottom: 8,
  },
  noticeText: {
    color: '#7c2d12',
    fontSize: 14,
    lineHeight: 22,
  },
  sectionCard: {
    backgroundColor: '#ffffff',
    borderColor: '#e2e8f0',
    borderRadius: 22,
    borderWidth: 1,
    marginBottom: 16,
    padding: 18,
    shadowColor: '#0f172a',
    shadowOffset: {
      width: 0,
      height: 8,
    },
    shadowOpacity: 0.04,
    shadowRadius: 14,
    elevation: 1,
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
    marginBottom: 14,
  },
  loadingText: {
    color: '#475569',
    fontSize: 14,
    marginTop: 12,
  },
  errorTitle: {
    color: '#0f172a',
    fontSize: 20,
    fontWeight: '700',
    marginBottom: 8,
    textAlign: 'center',
  },
  errorText: {
    color: '#dc2626',
    fontSize: 14,
    lineHeight: 20,
    textAlign: 'center',
  },
  detailRow: {
    borderTopColor: '#eef2f7',
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
    fontSize: 16,
    fontWeight: '600',
    lineHeight: 22,
  },
  costGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 12,
  },
  costCard: {
    backgroundColor: '#f8fafc',
    borderRadius: 18,
    minWidth: '47%',
    padding: 14,
  },
  costLabel: {
    color: '#64748b',
    fontSize: 12,
    fontWeight: '700',
    marginBottom: 6,
    textTransform: 'uppercase',
  },
  costValue: {
    color: '#0f172a',
    fontSize: 17,
    fontWeight: '700',
  },
  totalCard: {
    backgroundColor: '#eff6ff',
    borderRadius: 20,
    marginTop: 14,
    padding: 16,
  },
  totalLabel: {
    color: '#1d4ed8',
    fontSize: 12,
    fontWeight: '700',
    marginBottom: 6,
    textTransform: 'uppercase',
  },
  totalValue: {
    color: '#0f172a',
    fontSize: 24,
    fontWeight: '800',
  },
});
