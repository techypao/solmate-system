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
import { getCustomerRequestBlockMessage } from '../src/services/customerRequestEligibility';
import { getUnavailablePreferredDates } from '../src/services/preferredDateAvailabilityApi';
import { getDefaultContactNumber } from '../src/utils/contactNumber';
import { createServiceRequest } from '../src/services/serviceRequestApi';

const NAVY = '#123A5A';
const GOLD = '#F4D000';
const MUTED = '#5E7288';
const BG = '#F8FAFC';
const CARD = '#ffffff';
const DIVIDER = '#DDE7EE';

const INSTALLATION_TYPE_OPTIONS = [
  'Residential rooftop installation',
  'Ground-mounted solar setup',
  'System expansion or additional panels',
  'Installation schedule coordination',
];

type FieldErrors = {
  installationType?: string;
  details?: string;
  contactNumber?: string;
  address?: string;
  addressDetails?: string;
  preferredDate?: string;
};

type AddressNote = {
  message: string;
  tone: 'info' | 'error';
} | null;

const RESERVED_DATE_MESSAGE =
  'Selected date is already reserved. Please choose another date.';

function sanitizeContactNumber(value: string) {
  return value.replace(/[^0-9+()\- ]/g, '');
}

function getFriendlyErrorMessage(error: unknown) {
  if (error instanceof ApiError) {
    if (error.status === 401) {
      return 'Your session has expired. Please log in again.';
    }
    return error.message;
  }

  return 'Something went wrong while submitting your installation request.';
}

function getFieldValidationMessage(error: unknown, field: string) {
  if (!(error instanceof ApiError)) return null;
  const messages = error.errors?.[field];
  if (Array.isArray(messages) && messages.length > 0) return messages[0];
  return null;
}

function ChoiceChip({
  label,
  selected,
  onPress,
}: {
  label: string;
  selected: boolean;
  onPress: () => void;
}) {
  return (
    <Pressable
      onPress={onPress}
      style={({ pressed }) => [
        styles.choiceChip,
        selected && styles.choiceChipSelected,
        pressed && styles.pressed,
      ]}
    >
      <Text
        style={[
          styles.choiceChipText,
          selected && styles.choiceChipTextSelected,
        ]}
      >
        {label}
      </Text>
    </Pressable>
  );
}

export default function InstallationRequestScreen({ navigation }: any) {
  const { user } = useContext(AuthContext);
  const defaultContactNumber = getDefaultContactNumber(user);
  const [installationType, setInstallationType] = useState('');
  const [details, setDetails] = useState('');
  const [contactNumber, setContactNumber] = useState(defaultContactNumber);
  const [address, setAddress] = useState(user?.address || '');
  const [addressDetails, setAddressDetails] = useState('');
  const [latitude, setLatitude] = useState<number | null>(null);
  const [longitude, setLongitude] = useState<number | null>(null);
  const [preferredDate, setPreferredDate] = useState('');
  const [extraNotes, setExtraNotes] = useState('');
  const [unavailableDates, setUnavailableDates] = useState<string[]>([]);
  const [availabilityMessage, setAvailabilityMessage] = useState('');
  const [fieldErrors, setFieldErrors] = useState<FieldErrors>({});
  const [addressNote, setAddressNote] = useState<AddressNote>(null);
  const [isMapModalVisible, setIsMapModalVisible] = useState(false);
  const [checkingEligibility, setCheckingEligibility] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  const [requestBlockMessage, setRequestBlockMessage] = useState('');
  const [errorMessage, setErrorMessage] = useState('');
  const [successMessage, setSuccessMessage] = useState('');

  const resetForm = () => {
    setInstallationType('');
    setDetails('');
    setContactNumber(defaultContactNumber);
    setAddress(user?.address || '');
    setAddressDetails('');
    setLatitude(null);
    setLongitude(null);
    setPreferredDate('');
    setExtraNotes('');
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
      const dates = await getUnavailablePreferredDates('installation');
      setUnavailableDates(dates);
      setAvailabilityMessage('');
    } catch {
      setAvailabilityMessage(
        'Schedule availability could not be refreshed right now. You can still review the installation request flow.',
      );
    }
  }, []);

  const refreshRequestEligibility = useCallback(async () => {
    try {
      setCheckingEligibility(true);

      const blockMessage = await getCustomerRequestBlockMessage();
      setRequestBlockMessage(blockMessage || '');

      return blockMessage;
    } catch {
      setRequestBlockMessage('');
      return null;
    } finally {
      setCheckingEligibility(false);
    }
  }, []);

  useFocusEffect(
    useCallback(() => {
      loadUnavailableDates();
      refreshRequestEligibility();
    }, [loadUnavailableDates, refreshRequestEligibility]),
  );

  useEffect(() => {
    const isReserved =
      Boolean(preferredDate) && unavailableDates.includes(preferredDate);

    setFieldErrors(currentErrors => {
      if (isReserved && currentErrors.preferredDate !== RESERVED_DATE_MESSAGE) {
        return { ...currentErrors, preferredDate: RESERVED_DATE_MESSAGE };
      }

      if (
        !isReserved &&
        currentErrors.preferredDate === RESERVED_DATE_MESSAGE
      ) {
        return { ...currentErrors, preferredDate: undefined };
      }

      return currentErrors;
    });
  }, [preferredDate, unavailableDates]);

  const clearFieldError = (field: keyof FieldErrors) => {
    if (fieldErrors[field]) {
      setFieldErrors(current => ({ ...current, [field]: undefined }));
    }
  };

  const clearStatusMessages = () => {
    if (errorMessage) setErrorMessage('');
    if (successMessage) setSuccessMessage('');
  };

  const validateForm = () => {
    const nextErrors: FieldErrors = {};

    if (!installationType.trim()) {
      nextErrors.installationType = 'Please choose an installation type.';
    }

    if (!details.trim()) {
      nextErrors.details = 'Please add installation details or site notes.';
    }

    if (!contactNumber.trim()) {
      nextErrors.contactNumber = 'Contact number is required.';
    }

    if (!address.trim()) {
      nextErrors.address = 'Address is required.';
    }

    if (!preferredDate.trim()) {
      nextErrors.preferredDate = 'Please choose your preferred schedule date.';
    } else if (unavailableDates.includes(preferredDate)) {
      nextErrors.preferredDate = RESERVED_DATE_MESSAGE;
    }

    setFieldErrors(nextErrors);
    if (Object.keys(nextErrors).length > 0) {
      setErrorMessage('Please complete the required fields before submitting.');
      setSuccessMessage('');
      return false;
    }

    return Object.keys(nextErrors).length === 0;
  };

  const handleSubmit = async () => {
    if (submitting || checkingEligibility) return;

    const blockMessage = await refreshRequestEligibility();
    if (blockMessage) {
      setErrorMessage('');
      setSuccessMessage('');
      return;
    }

    if (!validateForm()) return;

    const trimmedDetails = details.trim();
    const trimmedContactNumber = contactNumber.trim();
    const trimmedAddress = address.trim();
    const trimmedAddressDetails = addressDetails.trim();
    const trimmedPreferredDate = preferredDate.trim();
    const trimmedExtraNotes = extraNotes.trim();

    if (
      trimmedPreferredDate &&
      unavailableDates.includes(trimmedPreferredDate)
    ) {
      setFieldErrors(current => ({
        ...current,
        preferredDate: RESERVED_DATE_MESSAGE,
      }));
      setErrorMessage(RESERVED_DATE_MESSAGE);
      setSuccessMessage('');
      return;
    }

    const detailLines = [];
    detailLines.push(`Installation Type: ${installationType.trim()}`);
    detailLines.push(`Installation Notes: ${trimmedDetails}`);
    if (trimmedExtraNotes) {
      detailLines.push(`Additional Notes: ${trimmedExtraNotes}`);
    }

    try {
      setSubmitting(true);
      setErrorMessage('');
      setSuccessMessage('');

      const response = await createServiceRequest({
        request_type: 'installation',
        details: detailLines.join('\n'),
        contact_number: trimmedContactNumber,
        address: trimmedAddress,
        address_details: trimmedAddressDetails || null,
        latitude,
        longitude,
        date_needed: trimmedPreferredDate,
      });

      const createdServiceRequest = response?.data;
      if (createdServiceRequest?.id) {
        resetForm();
        navigation.replace('ServiceRequestDetail', {
          serviceRequestId: createdServiceRequest.id,
          initialServiceRequest: createdServiceRequest,
          mode: 'customer',
          requestCategory: 'installation',
        });
        return;
      }

      resetForm();
      setSuccessMessage(
        response.message ||
          'Your installation request has been submitted successfully.',
      );
    } catch (error) {
      const contactFieldMessage = getFieldValidationMessage(
        error,
        'contact_number',
      );
      const addressFieldMessage = getFieldValidationMessage(error, 'address');
      const addressDetailsFieldMessage = getFieldValidationMessage(
        error,
        'address_details',
      );
      const dateFieldMessage = getFieldValidationMessage(error, 'date_needed');
      const detailsFieldMessage = getFieldValidationMessage(error, 'details');

      setFieldErrors(current => ({
        ...current,
        ...(contactFieldMessage ? { contactNumber: contactFieldMessage } : {}),
        ...(addressFieldMessage ? { address: addressFieldMessage } : {}),
        ...(addressDetailsFieldMessage
          ? { addressDetails: addressDetailsFieldMessage }
          : {}),
        ...(dateFieldMessage ? { preferredDate: dateFieldMessage } : {}),
        ...(detailsFieldMessage ? { details: detailsFieldMessage } : {}),
      }));

      if (dateFieldMessage) {
        loadUnavailableDates();
      }

      setErrorMessage(
        contactFieldMessage ||
          addressFieldMessage ||
          addressDetailsFieldMessage ||
          dateFieldMessage ||
          detailsFieldMessage ||
          getFriendlyErrorMessage(error),
      );
      setSuccessMessage('');
    } finally {
      setSubmitting(false);
    }
  };

  const submitDisabled =
    submitting || checkingEligibility || Boolean(requestBlockMessage);

  const submitLabel = checkingEligibility
    ? 'Checking...'
    : submitting
      ? 'Submitting...'
      : requestBlockMessage
        ? 'Request Unavailable'
        : 'Submit Installation Request';

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
        setFieldErrors(current => ({
          ...current,
          address: undefined,
        }));
      }
    } else if (reverseGeocodeFailed) {
      setAddressNote({
        message: 'Coordinates saved. Please review or type the address manually.',
        tone: 'info',
      });
    }

    clearStatusMessages();
    setIsMapModalVisible(false);
  };

  const handleViewActiveRequests = () => {
    navigation.navigate('ServiceRequestList', {
      requestCategory: 'installation',
    });
  };

  return (
    <SafeAreaView style={styles.safe}>
      <KeyboardAvoidingView
        behavior={Platform.OS === 'ios' ? 'padding' : undefined}
        keyboardVerticalOffset={Platform.OS === 'ios' ? 12 : 0}
        style={styles.flex}
      >
        <ScrollView
          contentContainerStyle={styles.scroll}
          keyboardDismissMode={
            Platform.OS === 'ios' ? 'interactive' : 'on-drag'
          }
          keyboardShouldPersistTaps="handled"
          showsVerticalScrollIndicator={false}
        >
          <Text style={styles.brand}>
            Sol<Text style={styles.brandAccent}>Mate</Text>
          </Text>

          <Pressable
            hitSlop={14}
            onPress={() => navigation.goBack()}
            style={({ pressed }) => [styles.backBtn, pressed && styles.pressed]}
          >
            <Text style={styles.backIcon}>{'‹'}</Text>
          </Pressable>

          <Text style={styles.title}>Installation Request</Text>
          <Text style={styles.subtitle}>
            Explore the mobile installation flow with installation details and
            your preferred date in the app’s existing soft-card style.
          </Text>

          {requestBlockMessage ? (
            <View style={styles.errorBanner}>
              <Text style={styles.errorBannerTitle}>New request unavailable</Text>
              <Text style={styles.errorBannerText}>{requestBlockMessage}</Text>
              <Pressable
                hitSlop={10}
                onPress={handleViewActiveRequests}
                style={({ pressed }) => [
                  {alignSelf: 'flex-start', marginTop: 10, paddingVertical: 4},
                  pressed && styles.pressed,
                ]}
              >
                <Text style={{fontSize: 13, fontWeight: '800', color: NAVY}}>
                  View My Active Installation Requests
                </Text>
              </Pressable>
            </View>
          ) : null}

          {errorMessage ? (
            <View style={styles.errorBanner}>
              <Text style={styles.errorBannerTitle}>Unable to submit</Text>
              <Text style={styles.errorBannerText}>{errorMessage}</Text>
            </View>
          ) : null}

          {successMessage ? (
            <View style={styles.successBanner}>
              <Text style={styles.successBannerTitle}>Request submitted</Text>
              <Text style={styles.successBannerText}>{successMessage}</Text>
            </View>
          ) : null}

          <View style={styles.card}>
            <Text style={styles.cardTitle}>Installation Details</Text>
            <Text style={styles.cardSubtitle}>
              Choose the setup type and share any site access or coordination
              instructions.
            </Text>
            <View style={styles.choiceList}>
              {INSTALLATION_TYPE_OPTIONS.map(option => (
                <ChoiceChip
                  key={option}
                  label={option}
                  onPress={() => {
                    setInstallationType(option);
                    clearStatusMessages();
                    clearFieldError('installationType');
                  }}
                  selected={installationType === option}
                />
              ))}
            </View>
            {fieldErrors.installationType ? (
              <Text style={styles.fieldError}>
                {fieldErrors.installationType}
              </Text>
            ) : null}

            <Text style={styles.fieldLabel}>Site Notes</Text>
            <TextInput
              multiline
              onChangeText={value => {
                setDetails(value);
                clearStatusMessages();
                clearFieldError('details');
              }}
              placeholder="Add roof access reminders, gate entry details, or preparation notes."
              placeholderTextColor="#a8b4c8"
              style={[styles.input, styles.textArea]}
              textAlignVertical="top"
              value={details}
            />
            {fieldErrors.details ? (
              <Text style={styles.fieldError}>{fieldErrors.details}</Text>
            ) : null}
          </View>

          <View style={styles.card}>
            <Text style={styles.cardTitle}>Preferred Schedule</Text>
            <Text style={styles.cardSubtitle}>
              Pick your preferred contact details and appointment window.
            </Text>

            <View style={styles.fieldHeader}>
              <Text style={styles.fieldLabel}>Contact Number</Text>
              <Text style={styles.requiredTag}>Required</Text>
            </View>
            <TextInput
              keyboardType="phone-pad"
              onChangeText={value => {
                setContactNumber(sanitizeContactNumber(value));
                clearStatusMessages();
                clearFieldError('contactNumber');
              }}
              placeholder="e.g. 09171234567"
              placeholderTextColor="#a8b4c8"
              style={styles.input}
              value={contactNumber}
            />
            <Text style={styles.helperText}>
              Use 11 digits, starting with 09.
            </Text>
            {fieldErrors.contactNumber ? (
              <Text style={styles.fieldError}>{fieldErrors.contactNumber}</Text>
            ) : null}

            <View style={styles.fieldHeader}>
              <Text style={styles.fieldLabel}>Address</Text>
              <Text style={styles.requiredTag}>Required</Text>
            </View>
            <View style={styles.addressActionRow}>
              <Text style={styles.helperText}>
                You may type your address manually or pin your exact
                installation location on the map.
              </Text>
              <Pressable
                onPress={() => {
                  clearStatusMessages();
                  setIsMapModalVisible(true);
                }}
                style={({ pressed }) => [
                  styles.mapPinButton,
                  pressed && styles.pressed,
                ]}
              >
                <Text style={styles.mapPinButtonText}>Pin Location on Map</Text>
              </Pressable>
            </View>
            <TextInput
              onChangeText={value => {
                setAddress(value);
                clearStatusMessages();
                if (addressNote) {
                  setAddressNote(null);
                }
                clearFieldError('address');
              }}
              placeholder="Enter the installation address"
              placeholderTextColor="#a8b4c8"
              style={[styles.input, fieldErrors.address && styles.inputError]}
              value={address}
            />
            <Text style={styles.helperText}>
              This is pre-filled from your profile when available, and you can
              still edit it.
            </Text>
            {addressNote ? (
              <View
                style={[
                  styles.addressNote,
                  addressNote.tone === 'error'
                    ? styles.addressNoteError
                    : styles.addressNoteInfo,
                ]}
              >
                <Text style={styles.addressNoteText}>{addressNote.message}</Text>
              </View>
            ) : null}
            {fieldErrors.address ? (
              <Text style={styles.fieldError}>{fieldErrors.address}</Text>
            ) : null}

            <View style={styles.fieldHeader}>
              <Text style={styles.fieldLabel}>Address Additional Details</Text>
            </View>
            <TextInput
              onChangeText={value => {
                setAddressDetails(value);
                clearStatusMessages();
                clearFieldError('addressDetails');
              }}
              placeholder="Unit, floor, landmark, gate code, or nearby reference"
              placeholderTextColor="#a8b4c8"
              style={[
                styles.input,
                fieldErrors.addressDetails && styles.inputError,
              ]}
              value={addressDetails}
            />
            <Text style={styles.helperText}>
              Add landmark or access details to help the team locate your exact
              installation spot.
            </Text>
            {fieldErrors.addressDetails ? (
              <Text style={styles.fieldError}>{fieldErrors.addressDetails}</Text>
            ) : null}

            <PreferredDateCalendar
              availabilityMessage={availabilityMessage}
              errorText={fieldErrors.preferredDate}
              helperText="Reserved dates are shown for planning only. Installation submission is still frontend-only for now."
              label="Preferred date"
              onClearDate={() => {
                setPreferredDate('');
                clearStatusMessages();
                clearFieldError('preferredDate');
              }}
              onSelectDate={(value: string) => {
                setPreferredDate(value);
                clearStatusMessages();
                clearFieldError('preferredDate');
              }}
              reservedDateMessage={RESERVED_DATE_MESSAGE}
              selectedDate={preferredDate}
              unavailableDates={unavailableDates}
            />

            <Text style={styles.fieldLabel}>Extra Notes</Text>
            <TextInput
              onChangeText={value => {
                setExtraNotes(value);
                clearStatusMessages();
              }}
              placeholder="Scheduling or access note"
              placeholderTextColor="#a8b4c8"
              style={styles.input}
              value={extraNotes}
            />
          </View>

          <View style={styles.card}>
            <Text style={styles.submitTitle}>Ready to send your request?</Text>
            <Text style={styles.submitSubtitle}>
              Your installation request will be saved to the shared service
              request backend and reviewed with your preferred schedule.
            </Text>
            <Pressable
              disabled={submitDisabled}
              onPress={handleSubmit}
              style={({ pressed }) => [
                styles.primaryBtn,
                submitDisabled && styles.btnDisabled,
                pressed && styles.pressed,
              ]}
            >
              <Text style={styles.primaryBtnText}>{submitLabel}</Text>
            </Pressable>
            <Pressable
              onPress={() =>
                navigation.navigate('ServiceRequestList', {
                  requestCategory: 'installation',
                })
              }
              style={({ pressed }) => [
                styles.secondaryBtn,
                pressed && styles.pressed,
              ]}
            >
              <Text style={styles.secondaryBtnText}>
                View My Installation Requests
              </Text>
            </Pressable>
          </View>

          <CustomerBottomNav activeTab="Services" />
        </ScrollView>
      </KeyboardAvoidingView>

      <MapLocationPickerModal
        initialLatitude={latitude}
        initialLongitude={longitude}
        onCancel={() => setIsMapModalVisible(false)}
        onConfirm={handleMapLocationConfirm}
        permissionMessage="SolMate needs your location so you can pin your installation spot on the map."
        subtitle="Search or move the pin to your exact installation spot, then confirm to fill the form."
        title="Pin Installation Location"
        visible={isMapModalVisible}
      />
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: BG },
  flex: { flex: 1 },
  scroll: { paddingHorizontal: 22, paddingTop: 20, paddingBottom: 30 },
  pressed: { opacity: 0.85 },

  brand: { fontSize: 22, fontWeight: '800', color: NAVY, marginBottom: 10 },
  brandAccent: { color: GOLD },
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

  title: { fontSize: 26, fontWeight: '900', color: NAVY, marginBottom: 4 },
  subtitle: { fontSize: 14, color: MUTED, lineHeight: 20, marginBottom: 22 },

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
  cardTitle: { fontSize: 17, fontWeight: '800', color: NAVY, marginBottom: 4 },
  cardSubtitle: {
    fontSize: 13,
    color: MUTED,
    lineHeight: 19,
    marginBottom: 14,
  },
  choiceList: { gap: 10 },
  choiceChip: {
    borderRadius: 16,
    borderWidth: 1,
    borderColor: DIVIDER,
    backgroundColor: '#f4f7fc',
    paddingHorizontal: 14,
    paddingVertical: 14,
  },
  choiceChipSelected: {
    backgroundColor: '#fff4cf',
    borderColor: '#f2cd59',
  },
  choiceChipText: { fontSize: 13, color: NAVY, fontWeight: '600' },
  choiceChipTextSelected: { color: NAVY, fontWeight: '800' },
  fieldHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginTop: 16,
    marginBottom: 8,
  },
  fieldLabel: {
    fontSize: 12,
    fontWeight: '700',
    color: MUTED,
    textTransform: 'uppercase',
  },
  requiredTag: {
    fontSize: 11,
    fontWeight: '800',
    color: '#b45309',
    textTransform: 'uppercase',
  },
  input: {
    backgroundColor: '#f4f7fc',
    borderRadius: 14,
    borderWidth: 1,
    borderColor: DIVIDER,
    paddingHorizontal: 16,
    paddingVertical: 13,
    fontSize: 15,
    color: NAVY,
  },
  inputError: {
    borderColor: '#ef4444',
  },
  textArea: { minHeight: 110 },
  helperText: { fontSize: 12, color: MUTED, lineHeight: 18, marginTop: 10 },
  addressActionRow: {
    gap: 10,
    marginBottom: 10,
  },
  mapPinButton: {
    alignSelf: 'flex-start',
    backgroundColor: '#fff4cf',
    borderRadius: 14,
    borderWidth: 1,
    borderColor: '#f2cd59',
    paddingHorizontal: 14,
    paddingVertical: 12,
  },
  mapPinButtonText: {
    color: NAVY,
    fontSize: 13,
    fontWeight: '800',
  },
  addressNote: {
    borderRadius: 14,
    marginTop: 10,
    paddingHorizontal: 14,
    paddingVertical: 12,
  },
  addressNoteInfo: {
    backgroundColor: '#EAF9FD',
  },
  addressNoteError: {
    backgroundColor: '#fef2f2',
  },
  addressNoteText: {
    color: NAVY,
    fontSize: 12,
    lineHeight: 18,
    fontWeight: '600',
  },
  referenceBox: {
    backgroundColor: '#f4f7fc',
    borderRadius: 14,
    borderWidth: 1,
    borderColor: DIVIDER,
    paddingHorizontal: 16,
    paddingVertical: 14,
  },
  dropdownTrigger: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
  },
  referenceText: { fontSize: 14, color: NAVY, fontWeight: '700' },
  referencePlaceholder: { color: '#9aa7bb', fontWeight: '600' },
  dropdownChevron: {
    fontSize: 12,
    color: MUTED,
    fontWeight: '800',
    marginLeft: 12,
  },
  dropdownMenu: {
    marginTop: 10,
    borderRadius: 16,
    borderWidth: 1,
    borderColor: DIVIDER,
    backgroundColor: '#f8fbff',
    overflow: 'hidden',
  },
  dropdownOption: {
    paddingHorizontal: 16,
    paddingVertical: 14,
    borderBottomWidth: 1,
    borderBottomColor: DIVIDER,
  },
  dropdownOptionLast: {
    borderBottomWidth: 0,
  },
  dropdownOptionSelected: {
    backgroundColor: '#fff4cf',
  },
  dropdownOptionText: {
    fontSize: 14,
    color: NAVY,
    fontWeight: '700',
  },
  dropdownOptionTextSelected: {
    fontWeight: '800',
  },
  loadingRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
    marginTop: 14,
  },
  loadingText: { fontSize: 13, color: MUTED },
  clearSelectionBtn: {
    marginTop: 12,
    alignSelf: 'flex-start',
    borderRadius: 999,
    backgroundColor: '#eef3fb',
    paddingHorizontal: 12,
    paddingVertical: 8,
  },
  clearSelectionText: { fontSize: 12, fontWeight: '700', color: NAVY },
  fieldError: { fontSize: 12, color: '#b91c1c', marginTop: 8 },
  submitTitle: {
    fontSize: 16,
    fontWeight: '800',
    color: NAVY,
    marginBottom: 4,
  },
  submitSubtitle: {
    fontSize: 13,
    color: MUTED,
    lineHeight: 19,
    marginBottom: 16,
  },
  errorBanner: {
    backgroundColor: '#fef2f2',
    borderRadius: 18,
    borderWidth: 1,
    borderColor: '#fecaca',
    padding: 16,
    marginBottom: 16,
  },
  errorBannerTitle: {
    fontSize: 15,
    fontWeight: '800',
    color: '#991b1b',
    marginBottom: 4,
  },
  errorBannerText: { fontSize: 13, color: '#b91c1c', lineHeight: 19 },
  successBanner: {
    backgroundColor: '#ecfdf5',
    borderRadius: 18,
    borderWidth: 1,
    borderColor: '#bbf7d0',
    padding: 16,
    marginBottom: 16,
  },
  successBannerTitle: {
    fontSize: 15,
    fontWeight: '800',
    color: '#166534',
    marginBottom: 4,
  },
  successBannerText: { fontSize: 13, color: '#166534', lineHeight: 19 },
  primaryBtn: {
    backgroundColor: GOLD,
    borderRadius: 26,
    paddingVertical: 14,
    alignItems: 'center',
  },
  btnDisabled: { opacity: 0.7 },
  primaryBtnText: { fontSize: 15, fontWeight: '900', color: CARD },
  secondaryBtn: {
    marginTop: 10,
    borderRadius: 26,
    borderWidth: 1,
    borderColor: DIVIDER,
    paddingVertical: 14,
    alignItems: 'center',
    backgroundColor: '#f8fafc',
  },
  secondaryBtnText: { fontSize: 14, fontWeight: '700', color: NAVY },

});
