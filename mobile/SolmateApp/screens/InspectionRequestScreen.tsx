import React, { useCallback, useContext, useEffect, useState } from 'react';
import { useFocusEffect } from '@react-navigation/native';
import {
  KeyboardAvoidingView,
  Platform,
  Pressable,
  SafeAreaView,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  View,
} from 'react-native';

import { MapLocationPickerModal, PreferredDateCalendar } from '../components';
import CustomerBottomNav from '../src/components/CustomerBottomNav';
import { AuthContext } from '../src/context/AuthContext';
import { ApiError } from '../src/services/api';
import { getUnavailablePreferredDates } from '../src/services/preferredDateAvailabilityApi';
import { getDefaultContactNumber } from '../src/utils/contactNumber';
import { createInspectionRequest } from '../src/services/inspectionRequestApi';

/* ── design tokens ── */

const NAVY = '#123A5A';
const GOLD = '#F4D000';
const MUTED = '#5E7288';
const BG = '#F8FAFC';
const CARD = '#ffffff';
const DIVIDER = '#DDE7EE';

/* ── constants (preserved) ── */

type FieldErrors = {
  details?: string;
  contactNumber?: string;
  address?: string;
  addressDetails?: string;
  dateNeeded?: string;
};

type AddressNote = {
  message: string;
  tone: 'info' | 'error';
} | null;

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

export default function InspectionRequestScreen({ navigation }: any) {
  const { user } = useContext(AuthContext);
  const defaultContactNumber = getDefaultContactNumber(user);
  const [details, setDetails] = useState('');
  const [contactNumber, setContactNumber] = useState(defaultContactNumber);
  const [address, setAddress] = useState(user?.address || '');
  const [addressDetails, setAddressDetails] = useState('');
  const [latitude, setLatitude] = useState<number | null>(null);
  const [longitude, setLongitude] = useState<number | null>(null);
  const [dateNeeded, setDateNeeded] = useState('');
  const [unavailableDates, setUnavailableDates] = useState<string[]>([]);
  const [availabilityMessage, setAvailabilityMessage] = useState('');
  const [fieldErrors, setFieldErrors] = useState<FieldErrors>({});
  const [addressNote, setAddressNote] = useState<AddressNote>(null);
  const [isMapModalVisible, setIsMapModalVisible] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  const [errorMessage, setErrorMessage] = useState('');
  const [successMessage, setSuccessMessage] = useState('');

  const resetForm = () => {
    setDetails('');
    setContactNumber(defaultContactNumber);
    setAddress(user?.address || '');
    setAddressDetails('');
    setLatitude(null);
    setLongitude(null);
    setDateNeeded('');
    setFieldErrors({});
    setAddressNote(null);
  };

  useEffect(() => {
    setAddress(user?.address || '');
  }, [user?.address]);

  useEffect(() => {
    if (!contactNumber.trim() && defaultContactNumber) {
      setContactNumber(defaultContactNumber);
    }
  }, [contactNumber, defaultContactNumber]);

  const loadUnavailableDates = useCallback(async () => {
    try {
      const dates = await getUnavailablePreferredDates('inspection');
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
    const isReserved = Boolean(
      dateNeeded && unavailableDates.includes(dateNeeded),
    );

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

  const handleAddressChange = (value: string) => {
    setAddress(value);
    clearStatusMessages();
    if (addressNote) {
      setAddressNote(null);
    }
    if (fieldErrors.address) {
      setFieldErrors(currentErrors => ({
        ...currentErrors,
        address: undefined,
      }));
    }
  };

  const handleAddressDetailsChange = (value: string) => {
    setAddressDetails(value);
    clearStatusMessages();
    if (fieldErrors.addressDetails) {
      setFieldErrors(currentErrors => ({
        ...currentErrors,
        addressDetails: undefined,
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
    const trimmedAddress = address.trim();
    const nextErrors: FieldErrors = {};

    if (!trimmedDetails) {
      nextErrors.details = 'Inspection details are required.';
    }

    if (!trimmedContactNumber) {
      nextErrors.contactNumber = 'Contact number is required.';
    }

    if (!trimmedAddress) {
      nextErrors.address = 'Address is required.';
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
    const trimmedAddress = address.trim();
    const trimmedAddressDetails = addressDetails.trim();
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
          ? { contact_number: trimmedContactNumber }
          : {}),
        address: trimmedAddress,
        address_details: trimmedAddressDetails || null,
        latitude,
        longitude,
        ...(trimmedDateNeeded ? { date_needed: trimmedDateNeeded } : {}),
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
      const addressFieldMessage = getFieldValidationMessage(error, 'address');
      const addressDetailsFieldMessage = getFieldValidationMessage(
        error,
        'address_details',
      );
      const dateFieldMessage = getFieldValidationMessage(error, 'date_needed');

      if (addressFieldMessage) {
        setFieldErrors(currentErrors => ({
          ...currentErrors,
          address: addressFieldMessage,
        }));
      }

      if (addressDetailsFieldMessage) {
        setFieldErrors(currentErrors => ({
          ...currentErrors,
          addressDetails: addressDetailsFieldMessage,
        }));
      }

      if (dateFieldMessage) {
        setFieldErrors(currentErrors => ({
          ...currentErrors,
          dateNeeded: dateFieldMessage,
        }));
        loadUnavailableDates();
      }

      setErrorMessage(
        addressFieldMessage ||
          addressDetailsFieldMessage ||
          dateFieldMessage ||
          getFriendlyErrorMessage(error),
      );
    } finally {
      setSubmitting(false);
    }
  };

  const handleMapLocationConfirm = ({
    latitude: selectedLatitude,
    longitude: selectedLongitude,
    resolvedAddress,
    reverseGeocodeFailed,
  }: {
    latitude: number;
    longitude: number;
    resolvedAddress: string | null;
    reverseGeocodeFailed: boolean;
  }) => {
    setLatitude(selectedLatitude);
    setLongitude(selectedLongitude);

    if (resolvedAddress) {
      setAddress(resolvedAddress);
      setAddressNote(null);
      if (fieldErrors.address) {
        setFieldErrors(currentErrors => ({
          ...currentErrors,
          address: undefined,
        }));
      }
    } else if (reverseGeocodeFailed) {
      setAddressNote({
        message:
          'Coordinates saved. Please review or type the address manually.',
        tone: 'info',
      });
    }

    clearStatusMessages();
    setIsMapModalVisible(false);
  };

  return (
    <SafeAreaView style={s.safe}>
      <KeyboardAvoidingView
        behavior={Platform.OS === 'ios' ? 'padding' : undefined}
        keyboardVerticalOffset={Platform.OS === 'ios' ? 12 : 0}
        style={s.flex}
      >
        <ScrollView
          contentContainerStyle={s.scroll}
          keyboardDismissMode={
            Platform.OS === 'ios' ? 'interactive' : 'on-drag'
          }
          keyboardShouldPersistTaps="handled"
          showsVerticalScrollIndicator={false}
        >
          {/* ── brand ── */}
          <Text style={s.brand}>
            Sol<Text style={s.brandAccent}>Mate</Text>
          </Text>

          {/* ── back ── */}
          <Pressable
            hitSlop={14}
            onPress={() => navigation.goBack()}
            style={({ pressed }) => [s.backBtn, pressed && s.pressed]}
          >
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
                style={[
                  s.input,
                  s.textArea,
                  fieldErrors.details && s.inputError,
                ]}
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
              <Text style={s.helpText}>Use 11 digits, starting with 09.</Text>
              {fieldErrors.contactNumber ? (
                <Text style={s.fieldErrorText}>
                  {fieldErrors.contactNumber}
                </Text>
              ) : null}
            </View>

            {/* C. Address */}
            <View style={s.fieldGroup}>
              <View style={s.fieldHeader}>
                <Text style={s.fieldLabel}>Address</Text>
                <Text style={s.requiredTag}>Required</Text>
              </View>
              <View style={s.addressActionRow}>
                <Text style={s.helpText}>
                  You may type your address manually or pin your exact
                  inspection location on the map.
                </Text>
                <Pressable
                  onPress={() => {
                    clearStatusMessages();
                    setIsMapModalVisible(true);
                  }}
                  style={({ pressed }) => [
                    s.mapPinButton,
                    pressed && s.pressed,
                  ]}
                >
                  <Text style={s.mapPinButtonText}>Pin Location on Map</Text>
                </Pressable>
              </View>
              <TextInput
                onChangeText={handleAddressChange}
                placeholder="Enter the service address"
                placeholderTextColor={MUTED}
                style={[s.input, fieldErrors.address && s.inputError]}
                value={address}
              />
              <Text style={s.helpText}>
                This is pre-filled from your profile when available, and you can
                still edit it.
              </Text>
              {addressNote ? (
                <Text
                  style={[
                    s.addressNote,
                    addressNote.tone === 'error'
                      ? s.addressNoteError
                      : s.addressNoteInfo,
                  ]}
                >
                  {addressNote.message}
                </Text>
              ) : null}
              {fieldErrors.address ? (
                <Text style={s.fieldErrorText}>{fieldErrors.address}</Text>
              ) : null}
            </View>

            <View style={s.fieldGroup}>
              <View style={s.fieldHeader}>
                <Text style={s.fieldLabel}>Address Additional Details</Text>
              </View>
              <TextInput
                onChangeText={handleAddressDetailsChange}
                placeholder="Unit, floor, landmark, gate code, or nearby reference"
                placeholderTextColor={MUTED}
                style={[s.input, fieldErrors.addressDetails && s.inputError]}
                value={addressDetails}
              />
              <Text style={s.helpText}>
                Add landmark or access details to help the team locate your
                exact inspection spot.
              </Text>
              {fieldErrors.addressDetails ? (
                <Text style={s.fieldErrorText}>
                  {fieldErrors.addressDetails}
                </Text>
              ) : null}
            </View>

            {/* D. Calendar (PreferredDateCalendar component) */}
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
              style={({ pressed }) => [
                s.primaryBtn,
                submitting && s.btnDisabled,
                pressed && s.pressed,
              ]}
            >
              <Text style={s.primaryBtnText}>
                {submitting ? 'Submitting...' : 'Submit Request'}
              </Text>
            </Pressable>

            <Pressable
              onPress={() => navigation.navigate('InspectionRequestList')}
              style={({ pressed }) => [s.secondaryBtn, pressed && s.pressed]}
            >
              <Text style={s.secondaryBtnText}>
                View My Inspection Requests
              </Text>
            </Pressable>
          </View>

          {/* ── spacer ── */}
          <View style={s.spacer} />

          {/* ── bottom nav ── */}
          <CustomerBottomNav activeTab="Tracking" />
        </ScrollView>
      </KeyboardAvoidingView>

      <MapLocationPickerModal
        initialLatitude={latitude}
        initialLongitude={longitude}
        onCancel={() => setIsMapModalVisible(false)}
        onConfirm={handleMapLocationConfirm}
        visible={isMapModalVisible}
      />
    </SafeAreaView>
  );
}

/* ── styles ── */

const s = StyleSheet.create({
  safe: { flex: 1, backgroundColor: BG },
  flex: { flex: 1 },
  scroll: { paddingHorizontal: 22, paddingTop: 20, paddingBottom: 30 },
  pressed: { opacity: 0.85 },

  /* brand */
  brand: { fontSize: 22, fontWeight: '800', color: NAVY, marginBottom: 10 },
  brandAccent: { color: GOLD },

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
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 6,
    elevation: 3,
  },
  backIcon: { fontSize: 28, color: NAVY, fontWeight: '600', marginTop: -2 },

  /* title */
  title: { fontSize: 26, fontWeight: '900', color: NAVY, marginBottom: 4 },
  subtitle: { fontSize: 14, color: MUTED, lineHeight: 20, marginBottom: 22 },

  /* banners */
  errorBanner: {
    backgroundColor: '#fef2f2',
    borderRadius: 16,
    borderWidth: 1,
    borderColor: '#fecaca',
    padding: 14,
    marginBottom: 16,
  },
  errorBannerTitle: {
    color: '#b91c1c',
    fontSize: 14,
    fontWeight: '700',
    marginBottom: 4,
  },
  errorBannerText: { color: '#991b1b', fontSize: 13, lineHeight: 18 },
  successBanner: {
    backgroundColor: '#f0fdf4',
    borderRadius: 16,
    borderWidth: 1,
    borderColor: '#bbf7d0',
    padding: 14,
    marginBottom: 16,
  },
  successBannerTitle: {
    color: '#166534',
    fontSize: 14,
    fontWeight: '700',
    marginBottom: 4,
  },
  successBannerText: { color: '#166534', fontSize: 13, lineHeight: 18 },

  /* card */
  card: {
    backgroundColor: CARD,
    borderRadius: 22,
    padding: 20,
    marginBottom: 16,
    shadowColor: '#8a9bbd',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.1,
    shadowRadius: 14,
    elevation: 4,
  },

  /* field groups */
  fieldGroup: { marginBottom: 20 },
  fieldHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 8,
  },
  fieldLabel: { fontSize: 15, fontWeight: '800', color: NAVY },
  requiredTag: {
    fontSize: 11,
    fontWeight: '700',
    color: '#dc2626',
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
  inputError: { borderColor: '#ef4444' },
  textArea: { minHeight: 120 },

  /* help / error text */
  helpText: { color: MUTED, fontSize: 13, lineHeight: 18, marginTop: 6 },
  addressActionRow: {
    marginBottom: 10,
  },
  mapPinButton: {
    alignItems: 'center',
    alignSelf: 'flex-start',
    backgroundColor: '#fce7a8',
    borderRadius: 18,
    marginTop: 10,
    paddingHorizontal: 16,
    paddingVertical: 12,
  },
  mapPinButtonText: {
    color: NAVY,
    fontSize: 14,
    fontWeight: '800',
  },
  addressNote: {
    fontSize: 13,
    lineHeight: 18,
    marginTop: 6,
  },
  addressNoteInfo: {
    color: '#1d4ed8',
  },
  addressNoteError: {
    color: '#b91c1c',
  },
  fieldErrorText: {
    color: '#dc2626',
    fontSize: 13,
    lineHeight: 18,
    marginTop: 6,
  },

  /* submit card */
  submitTitle: {
    fontSize: 16,
    fontWeight: '900',
    color: NAVY,
    marginBottom: 4,
  },
  submitSubtitle: {
    fontSize: 14,
    color: MUTED,
    lineHeight: 20,
    marginBottom: 16,
  },

  /* buttons */
  primaryBtn: {
    backgroundColor: GOLD,
    borderRadius: 28,
    paddingVertical: 16,
    alignItems: 'center',
    marginBottom: 12,
    shadowColor: GOLD,
    shadowOffset: { width: 0, height: 4 },
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
  secondaryBtn: {
    backgroundColor: CARD,
    borderRadius: 28,
    paddingVertical: 16,
    alignItems: 'center',
    borderWidth: 1,
    borderColor: '#dfe6f0',
  },
  secondaryBtnText: { fontSize: 16, fontWeight: '800', color: NAVY },
  btnDisabled: { opacity: 0.5 },

  /* spacer */
  spacer: { minHeight: 30 },

});
