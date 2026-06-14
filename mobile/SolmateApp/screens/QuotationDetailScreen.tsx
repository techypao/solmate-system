import React, {useEffect, useState} from 'react';
import {
  ActivityIndicator,
  Alert,
  Pressable,
  SafeAreaView,
  ScrollView,
  StyleSheet,
  Text,
  View,
} from 'react-native';

import CustomerBottomNav from '../src/components/CustomerBottomNav';
import {ApiError, apiGet} from '../src/services/api';
import {formatQuotationCurrency} from '../src/utils/currency';

/* ── types ── */

type QuotationDetail = {
  id: number;
  quotation_type?: string | null;
  status?: string | null;
  monthly_electric_bill?: string | number | null;
  pv_system_type?: string | null;
  monthly_kwh?: string | number | null;
  daily_kwh?: string | number | null;
  pv_kw_raw?: string | number | null;
  pv_kw_safe?: string | number | null;
  panel_quantity?: string | number | null;
  system_kw?: string | number | null;
  battery_required_kwh?: string | number | null;
  battery_required_ah?: string | number | null;
  panel_cost?: string | number | null;
  inverter_cost?: string | number | null;
  battery_cost?: string | number | null;
  bos_cost?: string | number | null;
  materials_subtotal?: string | number | null;
  labor_cost?: string | number | null;
  project_cost?: string | number | null;
  estimated_monthly_savings?: string | number | null;
  estimated_annual_savings?: string | number | null;
  roi_years?: string | number | null;
  remarks?: string | null;
  created_at?: string | null;
  pre_inspection_options?: PreInspectionOption[];
};

type PreInspectionOption = {
  id?: number;
  system_type?: string | null;
  with_battery?: boolean | null;
  system_kw?: string | number | null;
  inverter_capacity_kw?: string | number | null;
  battery_capacity_ah?: string | number | null;
  project_cost?: string | number | null;
  estimated_monthly_savings?: string | number | null;
  roi_years?: string | number | null;
  validation_note?: string | null;
};

/* ── constants ── */

const NAVY = '#1A2B55';
const GOLD = '#F5C000';
const MUTED = '#6B7A99';
const BG = '#C8D8F0';
const CARD = '#ffffff';

/* ── format helpers (preserved) ── */

function fmtVal(value?: string | number | null) {
  if (value === null || value === undefined || value === '') return 'N/A';
  return String(value);
}

function toFiniteNumber(value?: string | number | null) {
  if (value === null || value === undefined || value === '') return null;
  const numericValue =
    typeof value === 'number' ? value : Number(String(value).trim());
  return Number.isFinite(numericValue) ? numericValue : null;
}

function fmtYears(value?: string | number | null) {
  const numericValue = toFiniteNumber(value);
  if (numericValue === null) return 'N/A';
  return numericValue.toFixed(1) + ' yrs';
}

function fmtKw(value?: string | number | null) {
  const numericValue = toFiniteNumber(value);
  if (numericValue === null) return 'N/A';
  return numericValue + ' kWp';
}

function fmtCapacity(value?: string | number | null, suffix = '') {
  const numericValue = toFiniteNumber(value);
  if (numericValue === null) return 'N/A';
  return numericValue.toFixed(2) + suffix;
}

function fmtKwh(value?: string | number | null) {
  const numericValue = toFiniteNumber(value);
  if (numericValue === null) return 'N/A';
  return numericValue + ' kWh';
}

function fmtSystemType(value?: string | null) {
  if (!value) return 'N/A';
  return value.charAt(0).toUpperCase() + value.slice(1).toLowerCase();
}

function getQuotationScreenTitle(quotationType?: string | null) {
  return (quotationType || '').toLowerCase() === 'final'
    ? 'Inspection-Based Quotation'
    : 'Pre-Inspection Estimate';
}

function getQuotationScreenSubtitle(quotationType?: string | null) {
  return (quotationType || '').toLowerCase() === 'final'
    ? 'Based on the technician inspection and finalized system configuration.'
    : 'Based on your monthly bill + default system configuration.';
}

/* ── sub-components ── */

function InfoRow({label, value, bold}: {label: string; value: string; bold?: boolean}) {
  return (
    <View style={s.infoRow}>
      <Text style={s.infoLabel}>{label}</Text>
      <Text style={[s.infoValue, bold && s.infoValueBold]}>{value}</Text>
    </View>
  );
}

function optionTitle(option: PreInspectionOption) {
  return (option.system_type || '').toLowerCase() === 'hybrid'
    ? 'Hybrid'
    : 'On-Grid';
}

function PreInspectionOptionCard({option}: {option: PreInspectionOption}) {
  const batteryValue = option.with_battery
    ? fmtCapacity(option.battery_capacity_ah, ' Ah')
    : 'Not included';

  return (
    <View style={s.optionCard}>
      <View style={s.optionHeader}>
        <Text style={s.optionTitle}>{optionTitle(option)}</Text>
        <View style={s.optionBadge}>
          <Text style={s.optionBadgeText}>
            {option.with_battery ? 'With battery' : 'No battery'}
          </Text>
        </View>
      </View>
      <InfoRow label="System Size" value={fmtCapacity(option.system_kw, ' kW')} bold />
      <InfoRow label="Inverter" value={fmtCapacity(option.inverter_capacity_kw, ' kW')} bold />
      <InfoRow label="Battery" value={batteryValue} bold />
      <InfoRow label="Total Estimate" value={formatQuotationCurrency(option.project_cost)} bold />
      <InfoRow label="Monthly Savings" value={formatQuotationCurrency(option.estimated_monthly_savings)} />
      <InfoRow label="ROI" value={fmtYears(option.roi_years)} />
      {option.validation_note ? (
        <Text style={s.optionNote}>{option.validation_note}</Text>
      ) : null}
    </View>
  );
}

/* ── main screen ── */

export default function QuotationDetailScreen({route, navigation}: any) {
  const {quotationId} = route.params;
  const initialQuotation = route?.params?.initialQuotation as QuotationDetail | undefined;

  const [quotation, setQuotation] = useState<QuotationDetail | null>(initialQuotation || null);
  const [loading, setLoading] = useState(!initialQuotation);
  const [errorMessage, setErrorMessage] = useState('');

  useEffect(() => {
    const fetchQuotationDetail = async () => {
      try {
        setLoading(true);
        setErrorMessage('');
        const response = await apiGet<QuotationDetail>(`/quotations/${quotationId}`);
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

  /* ── loading / error / empty states ── */

  if (loading) {
    return (
      <SafeAreaView style={s.safe}>
        <View style={s.center}>
          <ActivityIndicator size="large" color={GOLD} />
          <Text style={s.loadingText}>Loading quotation details…</Text>
        </View>
      </SafeAreaView>
    );
  }

  if (errorMessage) {
    return (
      <SafeAreaView style={s.safe}>
        <View style={s.center}>
          <Text style={s.errTitle}>Could not load quotation</Text>
          <Text style={s.errText}>{errorMessage}</Text>
        </View>
      </SafeAreaView>
    );
  }

  if (!quotation) {
    return (
      <SafeAreaView style={s.safe}>
        <View style={s.center}>
          <Text style={s.errTitle}>Quotation not found</Text>
          <Text style={s.errText}>No quotation details were returned for this item.</Text>
        </View>
      </SafeAreaView>
    );
  }

  /* ── derived values ── */

  const systemSize = fmtKw(quotation.system_kw);
  const roiDisplay = fmtYears(quotation.roi_years);
  const monthlyBill = formatQuotationCurrency(quotation.monthly_electric_bill);
  const monthlyKwh = fmtKwh(quotation.monthly_kwh);
  const systemType = fmtSystemType(quotation.pv_system_type);
  const panelQty = fmtVal(quotation.panel_quantity);
  const screenTitle = getQuotationScreenTitle(quotation.quotation_type);
  const screenSubtitle = getQuotationScreenSubtitle(quotation.quotation_type);
  const showEstimateDisclaimer =
    (quotation.quotation_type || '').toLowerCase() !== 'final';
  const preInspectionOptions = Array.isArray(quotation.pre_inspection_options)
    ? quotation.pre_inspection_options
    : [];

  /* ── handlers ── */

  const handleRequestInspection = () => {
    navigation.navigate('InspectionRequest');
  };

  const handleSaveHistory = () => {
    Alert.alert('Saved', 'Quotation saved to your history.');
  };

  return (
    <SafeAreaView style={s.safe}>
      <ScrollView
        contentContainerStyle={s.scroll}
        keyboardShouldPersistTaps="handled"
        showsVerticalScrollIndicator={false}>

        {/* ── brand ── */}
        <Text style={s.brand}>
          Sol<Text style={s.brandAccent}>Mate</Text>
        </Text>

        {/* ── back ── */}
        <Pressable
          hitSlop={14}
          onPress={() => navigation.goBack()}
          style={({pressed}) => [s.backBtn, pressed && s.pressed]}>
          <Text style={s.backIcon}>{'\u2039'}</Text>
        </Pressable>

        {/* ── title ── */}
        <Text style={s.title}>{screenTitle}</Text>
        <Text style={s.subtitle}>{screenSubtitle}</Text>
        {showEstimateDisclaimer ? (
          <View style={s.disclaimerCard}>
            <Text style={s.disclaimerTitle}>Disclaimer</Text>
            <Text style={s.disclaimerText}>
              Taxes are not yet included. This is only a pre-inspection estimate and may change after a site inspection.
            </Text>
          </View>
        ) : null}

        {/* ── Quick Summary ── */}
        <View style={s.card}>
          <Text style={s.cardTitle}>Quick Summary</Text>
          <View style={s.summaryRow}>
            <View style={s.summaryCol}>
              <Text style={s.summaryLabel}>System Size</Text>
              <Text style={s.summaryValue}>{systemSize}</Text>
            </View>
            <View style={s.summaryCol}>
              <Text style={s.summaryLabel}>Payback / ROI</Text>
              <Text style={s.summaryValue}>{roiDisplay}</Text>
            </View>
          </View>
        </View>

        {/* ── Bill Breakdown ── */}
        <View style={s.card}>
          <Text style={s.cardTitle}>Bill Breakdown</Text>
          <InfoRow label="Monthly Bill" value={monthlyBill} bold />
          <InfoRow label="Computed Monthly kWh" value={monthlyKwh} bold />
        </View>

        {/* ── Recommended System ── */}
        <View style={s.card}>
          <Text style={s.cardTitle}>Recommended System</Text>
          <InfoRow label="System Type" value={systemType} bold />
          <InfoRow label="System Size" value={systemSize} bold />
          <InfoRow label="Panel Qty" value={panelQty} bold />
        </View>

        {showEstimateDisclaimer && preInspectionOptions.length > 0 ? (
          <View style={s.card}>
            <Text style={s.cardTitle}>Estimate Options</Text>
            {preInspectionOptions.map((option, index) => (
              <PreInspectionOptionCard
                key={option.id ?? `${option.system_type || 'option'}-${index}`}
                option={option}
              />
            ))}
          </View>
        ) : null}

        {/* ── action buttons ── */}
        <Pressable
          onPress={handleRequestInspection}
          style={({pressed}) => [s.secondaryBtn, pressed && s.pressed]}>
          <Text style={s.secondaryBtnText}>Request Site Inspection</Text>
        </Pressable>

        <Pressable
          onPress={handleSaveHistory}
          style={({pressed}) => [s.secondaryBtn, pressed && s.pressed]}>
          <Text style={s.secondaryBtnText}>Save to History (optional)</Text>
        </Pressable>

        {/* ── back to edit link ── */}
        <Pressable
          hitSlop={10}
          onPress={() => navigation.goBack()}
          style={({pressed}) => [s.backLink, pressed && s.pressed]}>
          <Text style={s.backLinkText}>{'\u2039 Back to Quotations'}</Text>
        </Pressable>

        {/* ── spacer ── */}
        <View style={s.spacer} />

        {/* ── bottom nav ── */}
      </ScrollView>

      <CustomerBottomNav activeTab="Quotations" />
    </SafeAreaView>
  );
}

/* ── styles ── */

const s = StyleSheet.create({
  safe: {flex: 1, backgroundColor: BG},
  scroll: {paddingHorizontal: 22, paddingTop: 20, paddingBottom: 90},
  pressed: {opacity: 0.85},
  center: {flex: 1, alignItems: 'center', justifyContent: 'center', padding: 24, backgroundColor: BG},

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
  subtitle: {fontSize: 14, color: MUTED, lineHeight: 20, marginBottom: 22},
  disclaimerCard: {
    backgroundColor: '#fff7e0',
    borderColor: '#f2d48a',
    borderRadius: 16,
    borderWidth: 1,
    marginBottom: 20,
    paddingHorizontal: 16,
    paddingVertical: 14,
  },
  disclaimerTitle: {
    fontSize: 14,
    fontWeight: '800',
    color: NAVY,
    marginBottom: 4,
  },
  disclaimerText: {
    fontSize: 13,
    lineHeight: 20,
    color: '#6f5a1a',
  },

  /* card */
  card: {
    backgroundColor: CARD,
    borderRadius: 22,
    padding: 20,
    marginBottom: 16,
    shadowColor: '#8a9bbd',
    shadowOffset: {width: 0, height: 4},
    shadowOpacity: 0.10,
    shadowRadius: 14,
    elevation: 4,
  },
  cardTitle: {
    fontSize: 16, fontWeight: '900', color: NAVY, marginBottom: 14,
  },

  /* quick summary */
  summaryRow: {flexDirection: 'row'},
  summaryCol: {flex: 1},
  summaryLabel: {fontSize: 12, color: MUTED, marginBottom: 4},
  summaryValue: {fontSize: 20, fontWeight: '900', color: NAVY},

  /* info rows */
  infoRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    borderTopWidth: 1,
    borderTopColor: '#D4E0F2',
    paddingVertical: 12,
  },
  infoLabel: {fontSize: 14, color: MUTED},
  infoValue: {fontSize: 14, color: NAVY},
  infoValueBold: {fontWeight: '800'},
  optionCard: {
    borderTopWidth: 1,
    borderTopColor: '#D4E0F2',
    paddingTop: 14,
    marginTop: 4,
    marginBottom: 14,
  },
  optionHeader: {
    alignItems: 'center',
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 4,
  },
  optionTitle: {fontSize: 15, fontWeight: '900', color: NAVY},
  optionBadge: {
    backgroundColor: '#EAF9FD',
    borderRadius: 999,
    paddingHorizontal: 10,
    paddingVertical: 5,
  },
  optionBadgeText: {color: '#1d4ed8', fontSize: 11, fontWeight: '800'},
  optionNote: {
    color: '#7A4F00',
    fontSize: 12,
    lineHeight: 18,
    marginTop: 8,
  },

  /* primary button */
  primaryBtn: {
    backgroundColor: GOLD,
    borderRadius: 28,
    paddingVertical: 16,
    alignItems: 'center',
    marginBottom: 12,
    shadowColor: GOLD,
    shadowOffset: {width: 0, height: 4},
    shadowOpacity: 0.25,
    shadowRadius: 10,
    elevation: 4,
  },
  primaryBtnText: {
    fontSize: 16, fontWeight: '900', color: CARD, letterSpacing: 0.3,
  },

  /* secondary button */
  secondaryBtn: {
    backgroundColor: CARD,
    borderRadius: 28,
    paddingVertical: 16,
    alignItems: 'center',
    marginBottom: 12,
    borderWidth: 1,
    borderColor: '#dfe6f0',
  },
  secondaryBtnText: {
    fontSize: 16, fontWeight: '800', color: NAVY,
  },

  /* back link */
  backLink: {marginTop: 4, marginBottom: 8, alignSelf: 'flex-start'},
  backLinkText: {fontSize: 14, color: MUTED, fontWeight: '600'},

  /* spacer */
  spacer: {minHeight: 40},

  /* loading / error */
  loadingText: {color: MUTED, fontSize: 14, marginTop: 12},
  errTitle: {color: NAVY, fontSize: 20, fontWeight: '700', marginBottom: 8, textAlign: 'center'},
  errText: {color: '#dc2626', fontSize: 14, lineHeight: 20, textAlign: 'center'},

});
