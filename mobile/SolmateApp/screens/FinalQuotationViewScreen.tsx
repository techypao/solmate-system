import React, {useCallback, useState} from 'react';
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
import {useFocusEffect} from '@react-navigation/native';

import {ApiError} from '../src/services/api';
import {
  getCustomerFinalQuotation,
  Quotation,
  QuotationLineItem,
} from '../src/services/quotationApi';
import {formatQuotationCurrency} from '../src/utils/currency';

/* ── design tokens (matches app-wide system) ── */

const NAVY = '#152a4a';
const GOLD = '#e8a800';
const MUTED = '#7b8699';
const BG = '#e0e8f5';
const CARD = '#ffffff';
const R = 18;
const DIVIDER = '#edf1f7';

/* ── format helpers (preserved from original) ── */

function formatValue(value?: string | number | null) {
  if (value === null || value === undefined || value === '') return 'N/A';
  return String(value);
}

function formatReadableText(value?: string | null) {
  if (value === null || value === undefined || value.trim() === '') return 'N/A';
  return value
    .trim()
    .replace(/\s*,\s*/g, ', ')
    .replace(/(?:,\s*){2,}/g, ', ')
    .replace(/,\s*([.!?])/g, '$1')
    .replace(/\s{2,}/g, ' ');
}

function formatBoolean(value?: boolean | null) {
  if (value === null || value === undefined) return 'N/A';
  return value ? 'Yes' : 'No';
}

function formatDate(value?: string | null) {
  if (!value) return 'N/A';
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return value;
  return date.toLocaleString();
}

function formatLineItemMeta(item: QuotationLineItem) {
  const parts = [
    item.category ? String(item.category).toUpperCase() : null,
    item.pricing_item?.brand || null,
    item.pricing_item?.model || null,
  ].filter(Boolean);
  return parts.length === 0 ? 'Custom item' : parts.join(' \u2022 ');
}

function formatQuantityWithUnit(
  quantity?: string | number | null,
  unit?: string | number | null,
) {
  const parts = [quantity, unit]
    .map(part =>
      part === null || part === undefined || part === '' ? null : String(part).trim(),
    )
    .filter(Boolean);
  return parts.length > 0 ? parts.join(' ') : 'N/A';
}

function fmtCurrency(value?: number | null) {
  return formatQuotationCurrency(value, {
    currency: 'PHP',
    fallback: 'PHP 0.00',
    spaceAfterCurrency: true,
  });
}

function fmtKw(value?: number | null) {
  if (value === null || value === undefined) return 'N/A';
  return value + ' kWp';
}

function fmtKwh(value?: number | null) {
  if (value === null || value === undefined) return 'N/A';
  return value + ' kWh';
}

function fmtYears(value?: number | null) {
  if (value === null || value === undefined || Number.isNaN(value)) return 'N/A';
  return value.toFixed(1) + ' yrs';
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

/* ── sub-components ── */

function InfoRow({
  label,
  value,
  bold,
  highlight,
}: {
  label: string;
  value: string;
  bold?: boolean;
  highlight?: boolean;
}) {
  return (
    <View style={s.infoRow}>
      <Text style={s.infoLabel}>{label}</Text>
      <Text
        style={[
          s.infoValue,
          bold && s.infoValueBold,
          highlight && s.infoValueHighlight,
        ]}>
        {value}
      </Text>
    </View>
  );
}

function SectionCard({
  title,
  children,
}: {
  title: string;
  children: React.ReactNode;
}) {
  return (
    <View style={s.card}>
      <Text style={s.cardTitle}>{title}</Text>
      {children}
    </View>
  );
}

function StatusBadge({status}: {status: string}) {
  const normalized = (status || '').toLowerCase();
  const isApproved = normalized === 'approved' || normalized === 'completed';
  return (
    <View style={[s.badge, isApproved ? s.badgeGreen : s.badgeDefault]}>
      <Text style={[s.badgeText, isApproved && s.badgeTextGreen]}>
        {status || 'N/A'}
      </Text>
    </View>
  );
}

/* ── main screen ── */

export default function FinalQuotationViewScreen({navigation, route}: any) {
  const inspectionRequestId = route?.params?.inspectionRequestId;

  const [quotation, setQuotation] = useState<Quotation | null>(null);
  const [loading, setLoading] = useState(true);
  const [errorMessage, setErrorMessage] = useState('');

  const loadQuotation = useCallback(
    async (showLoadingState = false) => {
      if (!inspectionRequestId) {
        setQuotation(null);
        setErrorMessage('No inspection request ID was provided.');
        setLoading(false);
        return;
      }
      try {
        if (showLoadingState) setLoading(true);
        setErrorMessage('');
        const data = await getCustomerFinalQuotation(inspectionRequestId);
        setQuotation(data);
      } catch (error) {
        setQuotation(null);
        setErrorMessage(getFriendlyErrorMessage(error));
      } finally {
        setLoading(false);
      }
    },
    [inspectionRequestId],
  );

  useFocusEffect(
    useCallback(() => {
      loadQuotation(true);
    }, [loadQuotation]),
  );

  /* ── loading state ── */

  if (loading) {
    return (
      <SafeAreaView style={s.safe}>
        <View style={s.center}>
          <ActivityIndicator size="large" color={GOLD} />
          <Text style={s.loadingText}>Loading final quotation...</Text>
        </View>
      </SafeAreaView>
    );
  }

  /* ── error / empty state ── */

  if (errorMessage || !quotation) {
    return (
      <SafeAreaView style={s.safe}>
        <View style={s.center}>
          <Text style={s.errTitle}>Final quotation unavailable</Text>
          <Text style={s.errText}>
            {errorMessage || 'No final quotation was returned for this request.'}
          </Text>
          <Pressable
            onPress={() => navigation.goBack()}
            style={({pressed}) => [s.primaryBtn, pressed && s.pressed]}>
            <Text style={s.primaryBtnText}>Go Back</Text>
          </Pressable>
        </View>
      </SafeAreaView>
    );
  }

  /* ── handlers ── */

  const handleConfirm = () => {
    Alert.alert(
      'Confirm Final Quotation',
      'Once confirmed, a service request can be created to begin installation.',
      [
        {text: 'Cancel', style: 'cancel'},
        {
          text: 'Confirm',
          onPress: () => {
            Alert.alert('Confirmed', 'Your final quotation has been confirmed.');
          },
        },
      ],
    );
  };

  const handleCreateService = () => {
    navigation.navigate('InstallationRequest');
  };

  const handleSave = () => {
    Alert.alert('Saved', 'Quotation saved to your history.');
  };

  /* ── render ── */

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
        <Text style={s.title}>Final Quotation</Text>
        <Text style={s.subtitle}>
          Detailed post-inspection quotation submitted by the assigned technician.
        </Text>

        {/* ── 1. Inspection Summary ── */}
        <SectionCard title="Inspection Summary">
          <InfoRow
            label="Inspection Request"
            value={'#' + inspectionRequestId}
            bold
          />
          <InfoRow
            label="Quotation Type"
            value={formatValue(quotation.quotation_type)}
          />
          <View style={s.infoRow}>
            <Text style={s.infoLabel}>Status</Text>
            <StatusBadge status={formatValue(quotation.status)} />
          </View>
          <InfoRow label="Created" value={formatDate(quotation.created_at)} />
          {quotation.updated_at && (
            <InfoRow label="Last Updated" value={formatDate(quotation.updated_at)} />
          )}
        </SectionCard>

        {/* ── 2. Energy Profile ── */}
        <SectionCard title="Energy Profile">
          <InfoRow
            label="Monthly Electric Bill"
            value={fmtCurrency(quotation.monthly_electric_bill)}
            bold
          />
          <InfoRow
            label="Rate per kWh"
            value={formatValue(quotation.rate_per_kwh)}
          />
          <InfoRow
            label="Days in Month"
            value={formatValue(quotation.days_in_month)}
          />
          <InfoRow
            label="Sun Hours / Day"
            value={formatValue(quotation.sun_hours)}
          />
          <InfoRow
            label="Monthly kWh"
            value={fmtKwh(quotation.monthly_kwh)}
            bold
          />
          <InfoRow
            label="Daily kWh"
            value={fmtKwh(quotation.daily_kwh)}
          />
        </SectionCard>

        {/* ── 3. Recommended Final System ── */}
        <SectionCard title="Recommended Final System">
          <InfoRow
            label="PV System Type"
            value={formatValue(quotation.pv_system_type)}
            bold
          />
          <InfoRow
            label="System Size"
            value={fmtKw(quotation.system_kw)}
            bold
          />
          <InfoRow
            label="PV kW (raw)"
            value={formatValue(quotation.pv_kw_raw)}
          />
          <InfoRow
            label="PV kW (w/ safety)"
            value={formatValue(quotation.pv_kw_safe)}
          />
          <InfoRow
            label="PV Safety Factor"
            value={formatValue(quotation.pv_safety_factor)}
          />
          <InfoRow
            label="Panel Watts"
            value={formatValue(quotation.panel_watts)}
          />
          <InfoRow
            label="Panel Quantity"
            value={formatValue(quotation.panel_quantity)}
            bold
          />
          <InfoRow
            label="Inverter Type"
            value={formatValue(quotation.inverter_type)}
          />
          <InfoRow
            label="With Battery"
            value={formatBoolean(quotation.with_battery)}
          />
        </SectionCard>

        {/* ── 4. Computed Battery Details ── */}
        <SectionCard title="Computed Battery Details">
          <InfoRow
            label="Battery Model"
            value={formatValue(quotation.battery_model)}
            bold
          />
          <InfoRow
            label="Battery Capacity"
            value={formatValue(quotation.battery_capacity_ah) + ' Ah'}
          />
          <InfoRow
            label="Battery Voltage"
            value={formatValue(quotation.battery_voltage) + ' V'}
          />
          <InfoRow
            label="Battery Factor"
            value={formatValue(quotation.battery_factor)}
          />
          <InfoRow
            label="Required Battery kWh"
            value={fmtKwh(quotation.battery_required_kwh)}
            bold
          />
          <InfoRow
            label="Required Battery Ah"
            value={formatValue(quotation.battery_required_ah) + ' Ah'}
          />
        </SectionCard>

        {/* ── 5. Final Cost, Savings & ROI ── */}
        <SectionCard title="Final Cost, Savings & ROI">
          <InfoRow label="Panel Cost" value={fmtCurrency(quotation.panel_cost)} />
          <InfoRow label="Inverter Cost" value={fmtCurrency(quotation.inverter_cost)} />
          <InfoRow label="Battery Cost" value={fmtCurrency(quotation.battery_cost)} />
          <InfoRow label="BOS Cost" value={fmtCurrency(quotation.bos_cost)} />
          <InfoRow
            label="Materials Subtotal"
            value={fmtCurrency(quotation.materials_subtotal)}
            bold
          />
          <InfoRow label="Labor Cost" value={fmtCurrency(quotation.labor_cost)} />
          <InfoRow
            label="Total Project Cost"
            value={fmtCurrency(quotation.project_cost)}
            bold
            highlight
          />

          {/* divider before savings */}
          <View style={s.sectionDivider} />

          <InfoRow
            label="Est. Monthly Savings"
            value={fmtCurrency(quotation.estimated_monthly_savings)}
            bold
          />
          <InfoRow
            label="Est. Annual Savings"
            value={fmtCurrency(quotation.estimated_annual_savings)}
            bold
          />
          <InfoRow label="ROI / Payback Period" value={fmtYears(quotation.roi_years)} bold />
        </SectionCard>

        {/* ── 6. Itemized Line Items ── */}
        <SectionCard title="Itemized Line Items">
          {quotation.line_items && quotation.line_items.length > 0 ? (
            quotation.line_items.map(item => (
              <View key={item.id} style={s.lineItem}>
                <Text style={s.lineItemTitle}>
                  {formatValue(item.description)}
                </Text>
                <Text style={s.lineItemMeta}>{formatLineItemMeta(item)}</Text>
                <InfoRow
                  label="Quantity"
                  value={formatQuantityWithUnit(item.qty, item.unit)}
                />
                <InfoRow label="Unit Price" value={fmtCurrency(item.unit_amount)} />
                <InfoRow
                  label="Total"
                  value={fmtCurrency(item.total_amount)}
                  bold
                />
              </View>
            ))
          ) : (
            <Text style={s.emptyText}>
              No itemized line items attached to this quotation.
            </Text>
          )}
        </SectionCard>

        {/* ── 7. Findings & Recommendations ── */}
        <SectionCard title="Findings & Recommendations">
          <Text style={s.remarksText}>
            {formatReadableText(quotation.remarks)}
          </Text>
        </SectionCard>

        {/* ── action buttons ── */}
        <Pressable
          onPress={handleConfirm}
          style={({pressed}) => [s.primaryBtn, pressed && s.pressed]}>
          <Text style={s.primaryBtnText}>Confirm Final Quotation</Text>
        </Pressable>

        <Pressable
          onPress={handleCreateService}
          style={({pressed}) => [s.secondaryBtn, pressed && s.pressed]}>
          <Text style={s.secondaryBtnText}>Create Installation Request</Text>
        </Pressable>

        <Pressable
          onPress={handleSave}
          style={({pressed}) => [s.secondaryBtn, pressed && s.pressed]}>
          <Text style={s.secondaryBtnText}>Save to History</Text>
        </Pressable>

        <Pressable
          hitSlop={10}
          onPress={() => navigation.goBack()}
          style={({pressed}) => [s.backLink, pressed && s.pressed]}>
          <Text style={s.backLinkText}>{'\u2039 Back to Inspection Details'}</Text>
        </Pressable>

        {/* ── spacer ── */}
        <View style={s.spacer} />

        {/* ── chatbot shortcut ── */}
        <Pressable
          onPress={() => navigation.navigate('Chatbot')}
          style={({pressed}) => [s.chatRow, pressed && s.pressed]}>
          <Text style={s.chatText}>Chat with SolBot</Text>
          <View style={s.chatBtn}>
            <Text style={s.chatBtnIcon}>{'\uD83E\uDD16'}</Text>
          </View>
        </Pressable>

        {/* ── bottom nav ── */}
        <View style={s.bottomNav}>
          <Pressable style={s.navItem} onPress={() => navigation.navigate('Home')}>
            <Text style={s.navIcon}>{'\uD83C\uDFE0'}</Text>
            <Text style={s.navLabel}>Home</Text>
          </Pressable>
          <Pressable
            style={s.navItem}
            onPress={() => navigation.navigate('QuotationList')}>
            <Text style={s.navIcon}>{'\uD83D\uDCCB'}</Text>
            <Text style={s.navLabel}>Quotation</Text>
          </Pressable>
          <Pressable
            style={s.navItem}
            onPress={() => navigation.navigate('ServicesHome')}>
            <Text style={s.navIcon}>{'\u2699\uFE0F'}</Text>
            <Text style={s.navLabel}>Services</Text>
          </Pressable>
          <Pressable
            style={s.navItem}
            onPress={() => navigation.navigate('TrackingHub')}>
            <Text style={s.navIconActive}>{'\uD83D\uDCCD'}</Text>
            <Text style={s.navLabelActive}>Tracking</Text>
          </Pressable>
          <Pressable
            style={s.navItem}
            onPress={() => navigation.navigate('CustomerSettings')}>
            <Text style={s.navIcon}>{'\uD83D\uDC64'}</Text>
            <Text style={s.navLabel}>Profile</Text>
          </Pressable>
        </View>
      </ScrollView>
    </SafeAreaView>
  );
}

/* ── styles ── */

const s = StyleSheet.create({
  safe: {flex: 1, backgroundColor: BG},
  scroll: {paddingHorizontal: 22, paddingTop: 20, paddingBottom: 30},
  pressed: {opacity: 0.85},
  center: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    padding: 24,
    backgroundColor: BG,
  },

  /* brand */
  brand: {fontSize: 22, fontWeight: '800', color: NAVY, marginBottom: 10},
  brandAccent: {color: GOLD},

  /* back */
  backBtn: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: CARD,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 18,
    shadowColor: '#8a9bbd',
    shadowOffset: {width: 0, height: 2},
    shadowOpacity: 0.1,
    shadowRadius: 6,
    elevation: 3,
  },
  backIcon: {fontSize: 28, color: NAVY, fontWeight: '600', marginTop: -2},

  /* title */
  title: {fontSize: 26, fontWeight: '900', color: NAVY, marginBottom: 4},
  subtitle: {fontSize: 14, color: MUTED, lineHeight: 20, marginBottom: 22},

  /* card */
  card: {
    backgroundColor: CARD,
    borderRadius: 22,
    padding: 20,
    marginBottom: 16,
    shadowColor: '#8a9bbd',
    shadowOffset: {width: 0, height: 4},
    shadowOpacity: 0.1,
    shadowRadius: 14,
    elevation: 4,
  },
  cardTitle: {
    fontSize: 16,
    fontWeight: '900',
    color: NAVY,
    marginBottom: 14,
  },

  /* info rows */
  infoRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    borderTopWidth: 1,
    borderTopColor: DIVIDER,
    paddingVertical: 12,
  },
  infoLabel: {fontSize: 13, color: MUTED, flex: 1},
  infoValue: {fontSize: 14, color: NAVY, textAlign: 'right', flexShrink: 1, marginLeft: 12},
  infoValueBold: {fontWeight: '800'},
  infoValueHighlight: {color: GOLD, fontSize: 16, fontWeight: '900'},

  /* section divider (within a card) */
  sectionDivider: {
    height: 2,
    backgroundColor: DIVIDER,
    marginVertical: 6,
    borderRadius: 1,
  },

  /* status badge */
  badge: {
    paddingHorizontal: 12,
    paddingVertical: 4,
    borderRadius: 12,
    backgroundColor: '#edf1f7',
  },
  badgeGreen: {backgroundColor: '#dcfce7'},
  badgeText: {fontSize: 12, fontWeight: '700', color: MUTED, textTransform: 'capitalize'},
  badgeTextGreen: {color: '#16a34a'},
  badgeDefault: {},

  /* line items */
  lineItem: {
    backgroundColor: '#f7f9fc',
    borderRadius: 16,
    borderWidth: 1,
    borderColor: DIVIDER,
    padding: 14,
    marginBottom: 12,
  },
  lineItemTitle: {
    fontSize: 15,
    fontWeight: '700',
    color: NAVY,
    marginBottom: 2,
  },
  lineItemMeta: {
    fontSize: 12,
    fontWeight: '600',
    color: MUTED,
    marginBottom: 8,
  },

  /* empty text */
  emptyText: {color: MUTED, fontSize: 14, lineHeight: 21},

  /* remarks */
  remarksText: {
    color: NAVY,
    fontSize: 14,
    lineHeight: 22,
  },

  /* primary button */
  primaryBtn: {
    backgroundColor: GOLD,
    borderRadius: 28,
    paddingVertical: 16,
    alignItems: 'center',
    marginBottom: 12,
    marginTop: 6,
    shadowColor: GOLD,
    shadowOffset: {width: 0, height: 4},
    shadowOpacity: 0.25,
    shadowRadius: 10,
    elevation: 4,
  },
  primaryBtnText: {
    fontSize: 16,
    fontWeight: '900',
    color: CARD,
    letterSpacing: 0.3,
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
    fontSize: 16,
    fontWeight: '800',
    color: NAVY,
  },

  /* back link */
  backLink: {marginTop: 4, marginBottom: 8, alignSelf: 'flex-start'},
  backLinkText: {fontSize: 14, color: MUTED, fontWeight: '600'},

  /* spacer */
  spacer: {minHeight: 40},

  /* loading / error */
  loadingText: {color: MUTED, fontSize: 14, marginTop: 12},
  errTitle: {
    color: NAVY,
    fontSize: 20,
    fontWeight: '700',
    marginBottom: 8,
    textAlign: 'center',
  },
  errText: {
    color: '#dc2626',
    fontSize: 14,
    lineHeight: 20,
    textAlign: 'center',
    marginBottom: 16,
  },

  /* chat shortcut */
  chatRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'flex-end',
    marginBottom: 22,
    marginTop: 4,
  },
  chatText: {fontSize: 13, color: MUTED, marginRight: 10},
  chatBtn: {
    width: 48,
    height: 48,
    borderRadius: 24,
    backgroundColor: NAVY,
    alignItems: 'center',
    justifyContent: 'center',
    shadowColor: NAVY,
    shadowOffset: {width: 0, height: 4},
    shadowOpacity: 0.25,
    shadowRadius: 8,
    elevation: 5,
  },
  chatBtnIcon: {fontSize: 22},

  /* bottom nav */
  bottomNav: {
    flexDirection: 'row',
    justifyContent: 'space-around',
    backgroundColor: CARD,
    borderRadius: R,
    paddingVertical: 10,
    shadowColor: '#8a9bbd',
    shadowOffset: {width: 0, height: -2},
    shadowOpacity: 0.08,
    shadowRadius: 8,
    elevation: 4,
  },
  navItem: {alignItems: 'center', paddingHorizontal: 6},
  navIcon: {fontSize: 20, marginBottom: 2},
  navIconActive: {fontSize: 20, marginBottom: 2},
  navLabel: {fontSize: 11, color: MUTED, fontWeight: '600'},
  navLabelActive: {fontSize: 11, color: NAVY, fontWeight: '700'},
});
