import React, {useCallback, useEffect, useState} from 'react';
import {useFocusEffect} from '@react-navigation/native';
import {
  Pressable,
  SafeAreaView,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  View,
} from 'react-native';

import {PreferredDateCalendar} from '../components';
import {ApiError} from '../src/services/api';
import {getUnavailablePreferredDates} from '../src/services/preferredDateAvailabilityApi';
import {createInspectionRequest} from '../src/services/inspectionRequestApi';

/* ── design tokens ── */

const NAVY = '#152a4a';
const GOLD = '#e8a800';
const MUTED = '#7b8699';
const BG = '#e0e8f5';
const CARD = '#ffffff';
const DIVIDER = '#edf1f7';

/* ── constants (preserved) ── */

type FieldErrors = {
  details?: string;
  contactNumber?: string;
  dateNeeded?: string;
};

const RESERVED_DATE_MESSAGE =
  'Selected date is already reserved. Please choose another date.';

/* ── helpers (preserved) ── */

function getFriendlyErrorMessage(error: unknown) {
  if (error instanceof ApiError) {
    if (error.status === 401) {
      return 'Your session has expired. Please log in again.';
    }
    return error.message;
  }
  return 'Something went wrong while submitting your inspection request.';
}

function sanitizeContactNumber(value: string) {
  return value.replace(/[^0-9+()\- ]/g, '');
}

function getFieldValidationMessage(error: unknown, field: string) {
  if (!(error instanceof ApiError)) return null;
  const messages = error.errors?.[field];
  if (Array.isArray(messages) && messages.length > 0) return messages[0];
  return null;
}

/* ════════════════════════════════════════════
   Main screen
   ════════════════════════════════════════════ */

export default function InspectionRequestScreen({navigation}: any) {
  const [details, setDetails] = useState('');
  const [contactNumber, setContactNumber] = useState('');
  const [dateNeeded, setDateNeeded] = useState('');
  const [unavailableDates, setUnavailableDates] = useState<string[]>([]);
  const [availabilityMessage, setAvailabilityMessage] = useState('');
  const [fieldErrors, setFieldErrors] = useState<FieldErrors>({});
  const [submitting, setSubmitting] = useState(false);
  const [errorMessage, setErrorMessage] = useState('');
  const [successMessage, setSuccessMessage] = useState('');

  const resetForm = () => {
    setDetails('');
    setContactNumber('');
    setDateNeeded('');
    setFieldErrors({});
  };

  const loadUnavailableDates = useCallback(async () => {
    try {
      const dates = await getUnavailablePreferredDates();
      setUnavailableDates(dates);
      setAvailabilityMessage('');
    } catch {
      setAvailabilityMessage(
        'Live reserved-date updates could not be loaded right now. The backend will still verify your preferred date when you submit.',
      );
    }
  }, []);

  useFocusEffect(
    useCallback(() => {
      loadUnavailableDates();
    }, [loadUnavailableDates]),
  );

  useEffect(() => {
    const isReserved = Boolean(dateNeeded && unavailableDates.includes(dateNeeded));

    setFieldErrors(currentErrors => {
      if (isReserved && currentErrors.dateNeeded !== RESERVED_DATE_MESSAGE) {
        return {
          ...currentErrors,
          dateNeeded: RESERVED_DATE_MESSAGE,
        };
      }

      if (!isReserved && currentErrors.dateNeeded === RESERVED_DATE_MESSAGE) {
        return {
          ...currentErrors,
          dateNeeded: undefined,
        };
      }

      return currentErrors;
    });
  }, [dateNeeded, unavailableDates]);

  const clearStatusMessages = () => {
    if (errorMessage) setErrorMessage('');
    if (successMessage) setSuccessMessage('');
  };

  const handleDetailsChange = (value: string) => {
    setDetails(value);
    clearStatusMessages();
    if (fieldErrors.details) {
      setFieldErrors(currentErrors => ({
        ...currentErrors,
        details: undefined,
      }));
    }
  };

  const handleContactNumberChange = (value: string) => {
    setContactNumber(sanitizeContactNumber(value));
    clearStatusMessages();
    if (fieldErrors.contactNumber) {
      setFieldErrors(currentErrors => ({
        ...currentErrors,
        contactNumber: undefined,
      }));
    }
  };

  const handleDateSelect = (value: string) => {
    setDateNeeded(value);
    clearStatusMessages();
    setFieldErrors(currentErrors => ({
      ...currentErrors,
      dateNeeded: undefined,
    }));
  };

  const clearSelectedDate = () => {
    setDateNeeded('');
    clearStatusMessages();
    setFieldErrors(currentErrors => ({
      ...currentErrors,
      dateNeeded: undefined,
    }));
  };

  const validateForm = () => {
    const trimmedDetails = details.trim();
    const trimmedContactNumber = contactNumber.trim();
    const nextErrors: FieldErrors = {};

    if (!trimmedDetails) {
      nextErrors.details = 'Inspection details are required.';
    }

    if (!trimmedContactNumber) {
      nextErrors.contactNumber = 'Contact number is required.';
    }

    if (dateNeeded && unavailableDates.includes(dateNeeded)) {
      nextErrors.dateNeeded = RESERVED_DATE_MESSAGE;
    }

    setFieldErrors(nextErrors);

    if (Object.keys(nextErrors).length > 0) {
      setErrorMessage('Please complete the required fields before submitting.');
      setSuccessMessage('');
      return false;
    }

    return true;
  };

  const handleSubmit = async () => {
    if (submitting) return;
    if (!validateForm()) return;

    const trimmedDetails = details.trim();
    const trimmedContactNumber = contactNumber.trim();
    const trimmedDateNeeded = dateNeeded.trim();

    if (trimmedDateNeeded && unavailableDates.includes(trimmedDateNeeded)) {
      setFieldErrors(currentErrors => ({
        ...currentErrors,
        dateNeeded: RESERVED_DATE_MESSAGE,
      }));
      setErrorMessage(RESERVED_DATE_MESSAGE);
      setSuccessMessage('');
      return;
    }

    try {
      setSubmitting(true);
      setErrorMessage('');
      setSuccessMessage('');

      const response = await createInspectionRequest({
        details: trimmedDetails,
        ...(trimmedContactNumber
          ? {contact_number: trimmedContactNumber}
          : {}),
        ...(trimmedDateNeeded ? {date_needed: trimmedDateNeeded} : {}),
      });

      const createdInspectionRequest = response?.data;

      if (createdInspectionRequest?.id) {
        resetForm();
        navigation.replace('InspectionRequestDetail', {
          inspectionRequestId: createdInspectionRequest.id,
          initialInspectionRequest: createdInspectionRequest,
        });
        return;
      }

      resetForm();
      setSuccessMessage(
        response.message || 'Inspection request submitted successfully.',
      );
    } catch (error) {
      const dateFieldMessage = getFieldValidationMessage(error, 'date_needed');

      if (dateFieldMessage) {
        setFieldErrors(currentErrors => ({
          ...currentErrors,
          dateNeeded: dateFieldMessage,
        }));
        loadUnavailableDates();
      }

      setErrorMessage(dateFieldMessage || getFriendlyErrorMessage(error));
    } finally {
      setSubmitting(false);
    }
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
        <Text style={s.title}>Request Inspection</Text>
        <Text style={s.subtitle}>
          Book a site inspection for your solar system. Tell us what you need
          checked and add a preferred date if you have one in mind.
        </Text>

        {/* ── banners ── */}
        {errorMessage ? (
          <View style={s.errorBanner}>
            <Text style={s.errorBannerTitle}>Unable to submit</Text>
            <Text style={s.errorBannerText}>{errorMessage}</Text>
          </View>
        ) : null}

        {successMessage ? (
          <View style={s.successBanner}>
            <Text style={s.successBannerTitle}>Request submitted</Text>
            <Text style={s.successBannerText}>{successMessage}</Text>
          </View>
        ) : null}

        {/* ── form card ── */}
        <View style={s.card}>

          {/* A. Details */}
          <View style={s.fieldGroup}>
            <View style={s.fieldHeader}>
              <Text style={s.fieldLabel}>Inspection Details</Text>
              <Text style={s.requiredTag}>Required</Text>
            </View>
            <TextInput
              multiline
              numberOfLines={5}
              onChangeText={handleDetailsChange}
              placeholder="Describe the inspection you need"
              placeholderTextColor={MUTED}
              style={[s.input, s.textArea, fieldErrors.details && s.inputError]}
              textAlignVertical="top"
              value={details}
            />
            <Text style={s.helpText}>
              Example: roof check, panel placement review, or site condition
              concerns.
            </Text>
            {fieldErrors.details ? (
              <Text style={s.fieldErrorText}>{fieldErrors.details}</Text>
            ) : null}
          </View>

          {/* B. Contact number */}
          <View style={s.fieldGroup}>
            <View style={s.fieldHeader}>
              <Text style={s.fieldLabel}>Contact Number</Text>
              <Text style={s.requiredTag}>Required</Text>
            </View>
            <TextInput
              keyboardType="phone-pad"
              onChangeText={handleContactNumberChange}
              placeholder="e.g. 09171234567"
              placeholderTextColor={MUTED}
              style={[s.input, fieldErrors.contactNumber && s.inputError]}
              value={contactNumber}
            />
            <Text style={s.helpText}>
              Use 11 digits, starting with 09.
            </Text>
            {fieldErrors.contactNumber ? (
              <Text style={s.fieldErrorText}>{fieldErrors.contactNumber}</Text>
            ) : null}
          </View>

          {/* C. Calendar (PreferredDateCalendar component) */}
          <PreferredDateCalendar
            availabilityMessage={availabilityMessage}
            errorText={fieldErrors.dateNeeded}
            helperText="Some dates may already be reserved by other active requests. The backend will always confirm availability when you submit."
            label="Preferred date"
            onClearDate={clearSelectedDate}
            onSelectDate={handleDateSelect}
            reservedDateMessage={RESERVED_DATE_MESSAGE}
            selectedDate={dateNeeded}
            unavailableDates={unavailableDates}
          />
        </View>

        {/* ── submit card ── */}
        <View style={s.card}>
          <Text style={s.submitTitle}>Ready to send your request?</Text>
          <Text style={s.submitSubtitle}>
            After submission, you will be taken to the request details screen
            so you can review its status.
          </Text>

          <Pressable
            disabled={submitting}
            onPress={handleSubmit}
            style={({pressed}) => [
              s.primaryBtn,
              submitting && s.btnDisabled,
              pressed && s.pressed,
            ]}>
            <Text style={s.primaryBtnText}>
              {submitting ? 'Submitting...' : 'Submit Request'}
            </Text>
          </Pressable>

          <Pressable
            onPress={() => navigation.navigate('InspectionRequestList')}
            style={({pressed}) => [s.secondaryBtn, pressed && s.pressed]}>
            <Text style={s.secondaryBtnText}>View My Inspection Requests</Text>
          </Pressable>
        </View>

        {/* ── spacer ── */}
        <View style={s.spacer} />

        {/* ── bottom nav ── */}
        <View style={s.bottomNav}>
          <Pressable style={s.navItem} onPress={() => navigation.navigate('Home')}>
            <Text style={s.navIcon}>{'\uD83C\uDFE0'}</Text>
            <Text style={s.navLabel}>Home</Text>
          </Pressable>
          <Pressable style={s.navItem} onPress={() => navigation.navigate('QuotationList')}>
            <Text style={s.navIcon}>{'\uD83D\uDCCB'}</Text>
            <Text style={s.navLabel}>Quotation</Text>
          </Pressable>
          <Pressable style={s.navItem} onPress={() => navigation.navigate('ServicesHome')}>
            <Text style={s.navIcon}>{'\u2699\uFE0F'}</Text>
            <Text style={s.navLabel}>Services</Text>
          </Pressable>
          <Pressable style={s.navItem} onPress={() => navigation.navigate('TrackingHub')}>
            <Text style={s.navIconActive}>{'\uD83D\uDCCD'}</Text>
            <Text style={s.navLabelActive}>Tracking</Text>
          </Pressable>
          <Pressable style={s.navItem} onPress={() => navigation.navigate('CustomerSettings')}>
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

  /* banners */
  errorBanner: {
    backgroundColor: '#fef2f2',
    borderRadius: 16,
    borderWidth: 1,
    borderColor: '#fecaca',
    padding: 14,
    marginBottom: 16,
  },
  errorBannerTitle: {color: '#b91c1c', fontSize: 14, fontWeight: '700', marginBottom: 4},
  errorBannerText: {color: '#991b1b', fontSize: 13, lineHeight: 18},
  successBanner: {
    backgroundColor: '#f0fdf4',
    borderRadius: 16,
    borderWidth: 1,
    borderColor: '#bbf7d0',
    padding: 14,
    marginBottom: 16,
  },
  successBannerTitle: {color: '#166534', fontSize: 14, fontWeight: '700', marginBottom: 4},
  successBannerText: {color: '#166534', fontSize: 13, lineHeight: 18},

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

  /* field groups */
  fieldGroup: {marginBottom: 20},
  fieldHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 8,
  },
  fieldLabel: {fontSize: 15, fontWeight: '800', color: NAVY},
  requiredTag: {
    fontSize: 11, fontWeight: '700', color: '#dc2626',
    textTransform: 'uppercase',
  },

  /* inputs */
  input: {
    backgroundColor: '#f7f9fc',
    borderColor: DIVIDER,
    borderRadius: 16,
    borderWidth: 1,
    color: NAVY,
    fontSize: 15,
    paddingHorizontal: 16,
    paddingVertical: 14,
  },
  inputError: {borderColor: '#ef4444'},
  textArea: {minHeight: 120},

  /* help / error text */
  helpText: {color: MUTED, fontSize: 13, lineHeight: 18, marginTop: 6},
  fieldErrorText: {color: '#dc2626', fontSize: 13, lineHeight: 18, marginTop: 6},

  /* submit card */
  submitTitle: {fontSize: 16, fontWeight: '900', color: NAVY, marginBottom: 4},
  submitSubtitle: {fontSize: 14, color: MUTED, lineHeight: 20, marginBottom: 16},

  /* buttons */
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
  primaryBtnText: {fontSize: 16, fontWeight: '900', color: CARD, letterSpacing: 0.3},
  secondaryBtn: {
    backgroundColor: CARD,
    borderRadius: 28,
    paddingVertical: 16,
    alignItems: 'center',
    borderWidth: 1,
    borderColor: '#dfe6f0',
  },
  secondaryBtnText: {fontSize: 16, fontWeight: '800', color: NAVY},
  btnDisabled: {opacity: 0.5},

  /* spacer */
  spacer: {minHeight: 30},

  /* bottom nav */
  bottomNav: {
    flexDirection: 'row', justifyContent: 'space-around',
    backgroundColor: CARD, borderRadius: 18, paddingVertical: 10,
    shadowColor: '#8a9bbd', shadowOffset: {width: 0, height: -2},
    shadowOpacity: 0.08, shadowRadius: 8, elevation: 4,
  },
  navItem: {alignItems: 'center', paddingHorizontal: 6},
  navIcon: {fontSize: 20, marginBottom: 2},
  navIconActive: {fontSize: 20, marginBottom: 2},
  navLabel: {fontSize: 11, color: MUTED, fontWeight: '600'},
  navLabelActive: {fontSize: 11, color: NAVY, fontWeight: '700'},
});
