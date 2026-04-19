import React, {useCallback, useState} from 'react';
import {
  ActivityIndicator,
  Pressable,
  SafeAreaView,
  ScrollView,
  StyleSheet,
  Text,
  View,
} from 'react-native';
import {useFocusEffect} from '@react-navigation/native';

import {StatusBadge} from '../components';
import {ApiError} from '../src/services/api';
import {
  getInspectionRequestById,
  InspectionRequest,
} from '../src/services/inspectionRequestApi';

/* \u2500\u2500 design tokens \u2500\u2500 */

const NAVY = '#152a4a';
const GOLD = '#e8a800';
const MUTED = '#7b8699';
const BG = '#e0e8f5';
const CARD = '#ffffff';
const DIVIDER = '#edf1f7';

/* \u2500\u2500 helpers (preserved) \u2500\u2500 */

function formatDate(value?: string | null, fallback = 'Flexible') {
  if (!value) return fallback;
  const parsedDate = new Date(value);
  if (Number.isNaN(parsedDate.getTime())) return value;
  return parsedDate.toLocaleDateString();
}

function formatDateTime(value?: string | null, fallback = 'Not available') {
  if (!value) return fallback;
  const parsedDate = new Date(value);
  if (Number.isNaN(parsedDate.getTime())) return value;
  return parsedDate.toLocaleString();
}

function getFriendlyErrorMessage(error: unknown) {
  if (error instanceof ApiError) return error.message;
  return 'Could not load the inspection request details.';
}

/* \u2500\u2500 DetailRow \u2500\u2500 */

function DetailRow({
  label,
  value,
  bold,
}: {
  label: string;
  value?: string | null;
  bold?: boolean;
}) {
  return (
    <View style={s.detailRow}>
      <Text style={s.detailLabel}>{label}</Text>
      <Text style={[s.detailValue, bold && s.detailValueBold]}>
        {value || 'Not available'}
      </Text>
    </View>
  );
}

/* \u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550
   Main screen
   \u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550 */

export default function InspectionRequestDetailScreen({navigation, route}: any) {
  const inspectionRequestId = route?.params?.inspectionRequestId;
  const initialInspectionRequest = route?.params?.initialInspectionRequest as
    | InspectionRequest
    | undefined;

  const [inspectionRequest, setInspectionRequest] =
    useState<InspectionRequest | null>(initialInspectionRequest || null);
  const [loading, setLoading] = useState(!initialInspectionRequest);
  const [errorMessage, setErrorMessage] = useState('');

  const loadInspectionRequest = useCallback(
    async (showLoadingState = false) => {
      if (!inspectionRequestId) {
        setInspectionRequest(null);
        setErrorMessage('No inspection request ID was provided.');
        setLoading(false);
        return;
      }

      try {
        if (showLoadingState) setLoading(true);
        setErrorMessage('');
        const request = await getInspectionRequestById(inspectionRequestId);

        if (!request) {
          setInspectionRequest(null);
          setErrorMessage('This inspection request could not be found.');
          return;
        }

        setInspectionRequest(request);
      } catch (error) {
        setInspectionRequest(null);
        setErrorMessage(getFriendlyErrorMessage(error));
      } finally {
        setLoading(false);
      }
    },
    [inspectionRequestId],
  );

  useFocusEffect(
    useCallback(() => {
      loadInspectionRequest(!inspectionRequest);
    }, [inspectionRequest, loadInspectionRequest]),
  );

  /* \u2500\u2500 loading \u2500\u2500 */

  if (loading) {
    return (
      <SafeAreaView style={s.safe}>
        <View style={s.centered}>
          <ActivityIndicator size="large" color={GOLD} />
          <Text style={s.loadingText}>Loading inspection request\u2026</Text>
        </View>
      </SafeAreaView>
    );
  }

  /* \u2500\u2500 error / missing \u2500\u2500 */

  if (errorMessage || !inspectionRequest) {
    return (
      <SafeAreaView style={s.safe}>
        <View style={s.centered}>
          <Text style={s.errorTitle}>Inspection request unavailable</Text>
          <Text style={s.errorText}>
            {errorMessage || 'No inspection request details were found.'}
          </Text>
          <Pressable
            onPress={() => loadInspectionRequest(true)}
            style={({pressed}) => [s.goldBtn, pressed && s.pressed]}>
            <Text style={s.goldBtnText}>Try Again</Text>
          </Pressable>
          <Pressable
            onPress={() => navigation.navigate('InspectionRequestList')}
            style={({pressed}) => [s.outlineBtn, pressed && s.pressed]}>
            <Text style={s.outlineBtnText}>Back to Requests</Text>
          </Pressable>
        </View>
      </SafeAreaView>
    );
  }

  /* \u2500\u2500 main \u2500\u2500 */

  const canOpenFinalQuotation = inspectionRequest.status === 'completed';

  return (
    <SafeAreaView style={s.safe}>
      <ScrollView
        contentContainerStyle={s.scroll}
        showsVerticalScrollIndicator={false}>

        {/* \u2500\u2500 brand \u2500\u2500 */}
        <Text style={s.brand}>
          Sol<Text style={s.brandAccent}>Mate</Text>
        </Text>

        {/* \u2500\u2500 back \u2500\u2500 */}
        <Pressable
          hitSlop={14}
          onPress={() => navigation.goBack()}
          style={({pressed}) => [s.backBtn, pressed && s.pressed]}>
          <Text style={s.backIcon}>{'\u2039'}</Text>
        </Pressable>

        {/* \u2500\u2500 title \u2500\u2500 */}
        <Text style={s.title}>Inspection Details</Text>
        <Text style={s.subtitle}>
          Review the request details, current progress, and technician
          assignment for this inspection.
        </Text>

        {/* \u2500\u2500 badges row \u2500\u2500 */}
        <View style={s.badgeRow}>
          <View style={s.typeBadge}>
            <Text style={s.typeBadgeText}>Inspection</Text>
          </View>
          <StatusBadge status={inspectionRequest.status} />
        </View>

        {/* \u2500\u2500 Inspection Information \u2500\u2500 */}
        <View style={s.card}>
          <Text style={s.cardTitle}>Inspection Information</Text>

          <DetailRow
            label="Inspection Request ID"
            value={'IR-' + inspectionRequest.id}
            bold
          />
          <DetailRow
            label="Status"
            value={inspectionRequest.status
              ? inspectionRequest.status.charAt(0).toUpperCase() +
                inspectionRequest.status.slice(1).replace(/_/g, ' ')
              : 'Pending'}
            bold
          />
          <DetailRow
            label="Created At"
            value={formatDateTime(inspectionRequest.created_at)}
          />
          <DetailRow
            label="Schedule Date"
            value={formatDate(inspectionRequest.date_needed)}
          />
          <DetailRow
            label="Technician Assigned"
            value={inspectionRequest.technician?.name || 'Pending assignment'}
            bold
          />
        </View>

        {/* \u2500\u2500 Request Details \u2500\u2500 */}
        <View style={s.card}>
          <Text style={s.cardTitle}>Request Details</Text>

          <DetailRow
            label="Contact Number"
            value={inspectionRequest.contact_number || 'Not provided'}
          />

          <View style={s.descBlock}>
            <Text style={s.descLabel}>Problem Description</Text>
            <Text style={s.descText}>{inspectionRequest.details}</Text>
          </View>
        </View>

        {/* \u2500\u2500 Final Quotation \u2500\u2500 */}
        <View style={s.card}>
          <Text style={s.cardTitle}>Final Quotation</Text>
          <Text style={s.cardSubtitle}>
            {canOpenFinalQuotation
              ? 'The inspection is completed. You can now view the technician-submitted final quotation.'
              : 'The final quotation becomes available after the inspection is marked as completed by the assigned technician.'}
          </Text>

          <Pressable
            disabled={!canOpenFinalQuotation}
            onPress={() =>
              navigation.navigate('FinalQuotationView', {
                inspectionRequestId: inspectionRequest.id,
              })
            }
            style={({pressed}) => [
              canOpenFinalQuotation ? s.goldBtn : s.disabledBtn,
              pressed && canOpenFinalQuotation && s.pressed,
            ]}>
            <Text
              style={
                canOpenFinalQuotation ? s.goldBtnText : s.disabledBtnText
              }>
              View Final Quotation
            </Text>
          </Pressable>
        </View>

        {/* \u2500\u2500 bottom button \u2500\u2500 */}
        <Pressable
          onPress={() => navigation.goBack()}
          style={({pressed}) => [s.outlineBtn, pressed && s.pressed]}>
          <Text style={s.outlineBtnText}>Back</Text>
        </Pressable>

        <View style={s.spacer} />
      </ScrollView>
    </SafeAreaView>
  );
}

/* \u2500\u2500 styles \u2500\u2500 */

const s = StyleSheet.create({
  safe: {flex: 1, backgroundColor: BG},
  scroll: {paddingHorizontal: 22, paddingTop: 20, paddingBottom: 30},
  pressed: {opacity: 0.85},

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
  subtitle: {fontSize: 14, color: MUTED, lineHeight: 20, marginBottom: 18},

  /* centered / loading */
  centered: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    padding: 24,
  },
  loadingText: {color: MUTED, fontSize: 14, marginTop: 14},

  /* error */
  errorTitle: {
    color: NAVY,
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
    marginBottom: 16,
  },

  /* badges row */
  badgeRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
    marginBottom: 18,
  },
  typeBadge: {
    backgroundColor: '#e8ecf4',
    borderRadius: 20,
    paddingHorizontal: 14,
    paddingVertical: 7,
  },
  typeBadgeText: {color: NAVY, fontSize: 12, fontWeight: '700'},

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
  cardTitle: {fontSize: 18, fontWeight: '900', color: NAVY, marginBottom: 14},
  cardSubtitle: {
    fontSize: 14,
    color: MUTED,
    lineHeight: 20,
    marginBottom: 14,
  },

  /* detail row (horizontal label-value) */
  detailRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    paddingVertical: 11,
    borderTopColor: DIVIDER,
    borderTopWidth: 1,
  },
  detailLabel: {color: MUTED, fontSize: 13, fontWeight: '600', flex: 1},
  detailValue: {
    color: NAVY,
    fontSize: 14,
    fontWeight: '600',
    flex: 1,
    textAlign: 'right',
  },
  detailValueBold: {fontWeight: '800'},

  /* description block */
  descBlock: {paddingTop: 14, borderTopColor: DIVIDER, borderTopWidth: 1},
  descLabel: {
    color: MUTED,
    fontSize: 12,
    fontWeight: '700',
    textTransform: 'uppercase',
    marginBottom: 8,
  },
  descText: {color: NAVY, fontSize: 14, lineHeight: 22, opacity: 0.85},

  /* buttons */
  goldBtn: {
    backgroundColor: GOLD,
    borderRadius: 28,
    paddingVertical: 14,
    alignItems: 'center',
    marginTop: 4,
    shadowColor: GOLD,
    shadowOffset: {width: 0, height: 4},
    shadowOpacity: 0.25,
    shadowRadius: 10,
    elevation: 4,
  },
  goldBtnText: {
    fontSize: 15,
    fontWeight: '900',
    color: CARD,
    letterSpacing: 0.3,
  },
  outlineBtn: {
    backgroundColor: CARD,
    borderRadius: 28,
    paddingVertical: 14,
    alignItems: 'center',
    borderWidth: 1,
    borderColor: DIVIDER,
    marginTop: 12,
  },
  outlineBtnText: {fontSize: 15, fontWeight: '800', color: NAVY},
  disabledBtn: {
    backgroundColor: DIVIDER,
    borderRadius: 28,
    paddingVertical: 14,
    alignItems: 'center',
    marginTop: 4,
    opacity: 0.6,
  },
  disabledBtnText: {fontSize: 15, fontWeight: '800', color: MUTED},

  /* spacer */
  spacer: {minHeight: 20},
});
