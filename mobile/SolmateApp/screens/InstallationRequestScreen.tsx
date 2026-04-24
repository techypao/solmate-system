import React, {useCallback, useEffect, useState} from 'react';
import {useFocusEffect} from '@react-navigation/native';
import {
  ActivityIndicator,
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
import {createServiceRequest} from '../src/services/serviceRequestApi';
import {getQuotations, Quotation} from '../src/services/quotationApi';

const NAVY = '#152a4a';
const GOLD = '#e8a800';
const MUTED = '#7b8699';
const BG = '#e0e8f5';
const CARD = '#ffffff';
const DIVIDER = '#edf1f7';

const INSTALLATION_TYPE_OPTIONS = [
  'Residential rooftop installation',
  'Ground-mounted solar setup',
  'System expansion or additional panels',
  'Installation schedule coordination',
];

const TIME_OPTIONS = [
  'Morning (8:00 AM - 11:00 AM)',
  'Midday (11:00 AM - 1:00 PM)',
  'Afternoon (1:00 PM - 4:00 PM)',
];

type FieldErrors = {
  installationType?: string;
  details?: string;
  contactNumber?: string;
  preferredDate?: string;
};

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

function formatQuotationReference(quotation: Quotation) {
  const prefix = quotation.quotation_type === 'final' ? 'FINAL' : 'INIT';
  const status = quotation.status
    ? quotation.status.charAt(0).toUpperCase() + quotation.status.slice(1)
    : 'Pending';

  return `${prefix}-${quotation.id} • ${status}`;
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
      style={({pressed}) => [
        styles.choiceChip,
        selected && styles.choiceChipSelected,
        pressed && styles.pressed,
      ]}>
      <Text
        style={[
          styles.choiceChipText,
          selected && styles.choiceChipTextSelected,
        ]}>
        {label}
      </Text>
    </Pressable>
  );
}

export default function InstallationRequestScreen({navigation}: any) {
  const [quotations, setQuotations] = useState<Quotation[]>([]);
  const [quotationsLoading, setQuotationsLoading] = useState(true);
  const [quotationMessage, setQuotationMessage] = useState('');
  const [isQuotationDropdownOpen, setIsQuotationDropdownOpen] = useState(false);
  const [selectedQuotationId, setSelectedQuotationId] = useState<number | null>(
    null,
  );
  const [installationType, setInstallationType] = useState('');
  const [details, setDetails] = useState('');
  const [contactNumber, setContactNumber] = useState('');
  const [preferredDate, setPreferredDate] = useState('');
  const [preferredTime, setPreferredTime] = useState('');
  const [extraNotes, setExtraNotes] = useState('');
  const [unavailableDates, setUnavailableDates] = useState<string[]>([]);
  const [availabilityMessage, setAvailabilityMessage] = useState('');
  const [fieldErrors, setFieldErrors] = useState<FieldErrors>({});
  const [submitting, setSubmitting] = useState(false);
  const [errorMessage, setErrorMessage] = useState('');
  const [successMessage, setSuccessMessage] = useState('');

  const selectedQuotation =
    quotations.find(item => item.id === selectedQuotationId) ?? null;

  const resetForm = () => {
    setSelectedQuotationId(null);
    setInstallationType('');
    setDetails('');
    setContactNumber('');
    setPreferredDate('');
    setPreferredTime('');
    setExtraNotes('');
    setFieldErrors({});
  };

  const loadUnavailableDates = useCallback(async () => {
    try {
      const dates = await getUnavailablePreferredDates();
      setUnavailableDates(dates);
      setAvailabilityMessage('');
    } catch {
      setAvailabilityMessage(
        'Schedule availability could not be refreshed right now. You can still review the installation request flow.',
      );
    }
  }, []);

  const loadQuotations = useCallback(async () => {
    try {
      setQuotationsLoading(true);
      setQuotationMessage('');
      const data = await getQuotations();
      setQuotations(Array.isArray(data) ? data : []);
      setIsQuotationDropdownOpen(false);
    } catch (error) {
      setQuotations([]);
      if (error instanceof ApiError) {
        setQuotationMessage(error.message);
      } else {
        setQuotationMessage(
          'Select a quotation (optional)',
        );
      }
    } finally {
      setQuotationsLoading(false);
    }
  }, []);

  useFocusEffect(
    useCallback(() => {
      loadUnavailableDates();
      loadQuotations();
    }, [loadQuotations, loadUnavailableDates]),
  );

  useEffect(() => {
    const isReserved =
      Boolean(preferredDate) && unavailableDates.includes(preferredDate);

    setFieldErrors(currentErrors => {
      if (isReserved && currentErrors.preferredDate !== RESERVED_DATE_MESSAGE) {
        return {...currentErrors, preferredDate: RESERVED_DATE_MESSAGE};
      }

      if (
        !isReserved &&
        currentErrors.preferredDate === RESERVED_DATE_MESSAGE
      ) {
        return {...currentErrors, preferredDate: undefined};
      }

      return currentErrors;
    });
  }, [preferredDate, unavailableDates]);

  const clearFieldError = (field: keyof FieldErrors) => {
    if (fieldErrors[field]) {
      setFieldErrors(current => ({...current, [field]: undefined}));
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
    if (submitting) return;
    if (!validateForm()) return;

    const trimmedDetails = details.trim();
    const trimmedContactNumber = contactNumber.trim();
    const trimmedPreferredDate = preferredDate.trim();
    const trimmedPreferredTime = preferredTime.trim();
    const trimmedExtraNotes = extraNotes.trim();

    if (trimmedPreferredDate && unavailableDates.includes(trimmedPreferredDate)) {
      setFieldErrors(current => ({
        ...current,
        preferredDate: RESERVED_DATE_MESSAGE,
      }));
      setErrorMessage(RESERVED_DATE_MESSAGE);
      setSuccessMessage('');
      return;
    }

    const detailLines = [];
    if (selectedQuotationId) {
      detailLines.push(`Quotation Reference: Quote #${selectedQuotationId}`);
    }
    detailLines.push(`Installation Type: ${installationType.trim()}`);
    if (trimmedPreferredTime) {
      detailLines.push(`Preferred Time: ${trimmedPreferredTime}`);
    }
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
        date_needed: trimmedPreferredDate,
      });

      resetForm();
      setIsQuotationDropdownOpen(false);
      setSuccessMessage(
        response.message ||
          'Your installation request has been submitted successfully.',
      );
    } catch (error) {
      const contactFieldMessage = getFieldValidationMessage(
        error,
        'contact_number',
      );
      const dateFieldMessage = getFieldValidationMessage(error, 'date_needed');
      const detailsFieldMessage = getFieldValidationMessage(error, 'details');

      setFieldErrors(current => ({
        ...current,
        ...(contactFieldMessage
          ? {contactNumber: contactFieldMessage}
          : {}),
        ...(dateFieldMessage ? {preferredDate: dateFieldMessage} : {}),
        ...(detailsFieldMessage ? {details: detailsFieldMessage} : {}),
      }));

      if (dateFieldMessage) {
        loadUnavailableDates();
      }

      setErrorMessage(
        contactFieldMessage ||
          dateFieldMessage ||
          detailsFieldMessage ||
          getFriendlyErrorMessage(error),
      );
      setSuccessMessage('');
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <SafeAreaView style={styles.safe}>
      <ScrollView
        contentContainerStyle={styles.scroll}
        keyboardShouldPersistTaps="handled"
        showsVerticalScrollIndicator={false}>
        <Text style={styles.brand}>
          Sol<Text style={styles.brandAccent}>Mate</Text>
        </Text>

        <Pressable
          hitSlop={14}
          onPress={() => navigation.goBack()}
          style={({pressed}) => [styles.backBtn, pressed && styles.pressed]}>
          <Text style={styles.backIcon}>{'‹'}</Text>
        </Pressable>

        <Text style={styles.title}>Installation Request</Text>
        <Text style={styles.subtitle}>
          Explore the mobile installation flow with quotation reference,
          installation details, and preferred scheduling in the app’s existing
          soft-card style.
        </Text>

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
          <Text style={styles.cardTitle}>Quotation Reference</Text>
          <Text style={styles.cardSubtitle}>
            Optionally link this installation request to one of your
            quotations.
          </Text>

          <Text style={styles.fieldLabel}>Selected Quotation</Text>
          <Pressable
            disabled={quotationsLoading}
            onPress={() =>
              quotations.length > 0 &&
              setIsQuotationDropdownOpen(current => !current)
            }
            style={({pressed}) => [
              styles.referenceBox,
              styles.dropdownTrigger,
              pressed && !quotationsLoading && !submitting && styles.pressed,
            ]}>
            <Text
              style={[
                styles.referenceText,
                !selectedQuotation && styles.referencePlaceholder,
              ]}>
              {selectedQuotation
                ? formatQuotationReference(selectedQuotation)
                : 'Select a quotation (optional)'}
            </Text>
            <Text style={styles.dropdownChevron}>
              {isQuotationDropdownOpen ? '▲' : '▼'}
            </Text>
          </Pressable>

          {quotationsLoading ? (
            <View style={styles.loadingRow}>
              <ActivityIndicator color={GOLD} size="small" />
              <Text style={styles.loadingText}>Loading your quotations…</Text>
            </View>
          ) : quotations.length > 0 && isQuotationDropdownOpen ? (
            <View style={styles.dropdownMenu}>
              <Pressable
                onPress={() => {
                  setSelectedQuotationId(null);
                  setIsQuotationDropdownOpen(false);
                }}
                style={({pressed}) => [
                  styles.dropdownOption,
                  pressed && styles.pressed,
                ]}>
                <Text style={[styles.dropdownOptionText, styles.referencePlaceholder]}>
                  Select a quotation (optional)
                </Text>
              </Pressable>

              {quotations.map((item, index) => (
                <Pressable
                  key={item.id}
                  onPress={() => {
                    setSelectedQuotationId(item.id);
                    setIsQuotationDropdownOpen(false);
                    clearStatusMessages();
                  }}
                  style={({pressed}) => [
                    styles.dropdownOption,
                    selectedQuotationId === item.id && styles.dropdownOptionSelected,
                    index === quotations.length - 1 && styles.dropdownOptionLast,
                    pressed && styles.pressed,
                  ]}>
                  <Text
                    style={[
                      styles.dropdownOptionText,
                      selectedQuotationId === item.id &&
                        styles.dropdownOptionTextSelected,
                    ]}>
                    {formatQuotationReference(item)}
                  </Text>
                </Pressable>
              ))}
            </View>
          ) : (
            <Text style={styles.helperText}>
              {quotationMessage || 'Select a quotation (optional)'}
            </Text>
          )}

          {selectedQuotation ? (
            <Pressable
              onPress={() => setSelectedQuotationId(null)}
              style={({pressed}) => [
                styles.clearSelectionBtn,
                pressed && styles.pressed,
              ]}>
              <Text style={styles.clearSelectionText}>Clear selection</Text>
            </Pressable>
          ) : null}
        </View>

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
            <Text style={styles.fieldError}>{fieldErrors.installationType}</Text>
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

          <Text style={styles.fieldLabel}>Preferred Time</Text>
          <View style={styles.choiceList}>
            {TIME_OPTIONS.map(option => (
              <ChoiceChip
                key={option}
                label={option}
                onPress={() => {
                  setPreferredTime(option);
                  clearStatusMessages();
                }}
                selected={preferredTime === option}
              />
            ))}
          </View>

          <Text style={styles.fieldLabel}>Extra Notes</Text>
          <TextInput
            onChangeText={value => {
              setExtraNotes(value);
              clearStatusMessages();
            }}
            placeholder="Optional scheduling or access note"
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
            disabled={submitting}
            onPress={handleSubmit}
            style={({pressed}) => [
              styles.primaryBtn,
              submitting && styles.btnDisabled,
              pressed && styles.pressed,
            ]}>
            <Text style={styles.primaryBtnText}>
              {submitting ? 'Submitting...' : 'Submit Installation Request'}
            </Text>
          </Pressable>
          <Pressable
            onPress={() =>
              navigation.navigate('ServiceRequestList', {
                requestCategory: 'installation',
              })
            }
            style={({pressed}) => [styles.secondaryBtn, pressed && styles.pressed]}>
            <Text style={styles.secondaryBtnText}>View My Installation Requests</Text>
          </Pressable>
        </View>

        <View style={styles.bottomNav}>
          <Pressable
            style={styles.navItem}
            onPress={() => navigation.navigate('Home')}>
            <Text style={styles.navIcon}>{'🏠'}</Text>
            <Text style={styles.navLabel}>Home</Text>
          </Pressable>
          <Pressable
            style={styles.navItem}
            onPress={() => navigation.navigate('QuotationList')}>
            <Text style={styles.navIcon}>{'📋'}</Text>
            <Text style={styles.navLabel}>Quotation</Text>
          </Pressable>
          <Pressable
            style={styles.navItem}
            onPress={() => navigation.navigate('ServicesHome')}>
            <Text style={styles.navIconActive}>{'⚙️'}</Text>
            <Text style={styles.navLabelActive}>Services</Text>
          </Pressable>
          <Pressable
            style={styles.navItem}
            onPress={() => navigation.navigate('TrackingHub')}>
            <Text style={styles.navIcon}>{'📍'}</Text>
            <Text style={styles.navLabel}>Tracking</Text>
          </Pressable>
          <Pressable
            style={styles.navItem}
            onPress={() => navigation.navigate('CustomerSettings')}>
            <Text style={styles.navIcon}>{'👤'}</Text>
            <Text style={styles.navLabel}>Profile</Text>
          </Pressable>
        </View>
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safe: {flex: 1, backgroundColor: BG},
  scroll: {paddingHorizontal: 22, paddingTop: 20, paddingBottom: 30},
  pressed: {opacity: 0.85},

  brand: {fontSize: 22, fontWeight: '800', color: NAVY, marginBottom: 10},
  brandAccent: {color: GOLD},
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

  title: {fontSize: 26, fontWeight: '900', color: NAVY, marginBottom: 4},
  subtitle: {fontSize: 14, color: MUTED, lineHeight: 20, marginBottom: 22},

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
  cardTitle: {fontSize: 17, fontWeight: '800', color: NAVY, marginBottom: 4},
  cardSubtitle: {fontSize: 13, color: MUTED, lineHeight: 19, marginBottom: 14},
  choiceList: {gap: 10},
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
  choiceChipText: {fontSize: 13, color: NAVY, fontWeight: '600'},
  choiceChipTextSelected: {color: NAVY, fontWeight: '800'},
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
  textArea: {minHeight: 110},
  helperText: {fontSize: 12, color: MUTED, lineHeight: 18, marginTop: 10},
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
  referenceText: {fontSize: 14, color: NAVY, fontWeight: '700'},
  referencePlaceholder: {color: '#9aa7bb', fontWeight: '600'},
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
  loadingText: {fontSize: 13, color: MUTED},
  clearSelectionBtn: {
    marginTop: 12,
    alignSelf: 'flex-start',
    borderRadius: 999,
    backgroundColor: '#eef3fb',
    paddingHorizontal: 12,
    paddingVertical: 8,
  },
  clearSelectionText: {fontSize: 12, fontWeight: '700', color: NAVY},
  fieldError: {fontSize: 12, color: '#b91c1c', marginTop: 8},
  submitTitle: {fontSize: 16, fontWeight: '800', color: NAVY, marginBottom: 4},
  submitSubtitle: {fontSize: 13, color: MUTED, lineHeight: 19, marginBottom: 16},
  errorBanner: {
    backgroundColor: '#fef2f2',
    borderRadius: 18,
    borderWidth: 1,
    borderColor: '#fecaca',
    padding: 16,
    marginBottom: 16,
  },
  errorBannerTitle: {fontSize: 15, fontWeight: '800', color: '#991b1b', marginBottom: 4},
  errorBannerText: {fontSize: 13, color: '#b91c1c', lineHeight: 19},
  successBanner: {
    backgroundColor: '#ecfdf5',
    borderRadius: 18,
    borderWidth: 1,
    borderColor: '#bbf7d0',
    padding: 16,
    marginBottom: 16,
  },
  successBannerTitle: {fontSize: 15, fontWeight: '800', color: '#166534', marginBottom: 4},
  successBannerText: {fontSize: 13, color: '#166534', lineHeight: 19},
  primaryBtn: {
    backgroundColor: GOLD,
    borderRadius: 26,
    paddingVertical: 14,
    alignItems: 'center',
  },
  btnDisabled: {opacity: 0.7},
  primaryBtnText: {fontSize: 15, fontWeight: '900', color: CARD},
  secondaryBtn: {
    marginTop: 10,
    borderRadius: 26,
    borderWidth: 1,
    borderColor: DIVIDER,
    paddingVertical: 14,
    alignItems: 'center',
    backgroundColor: '#f8fafc',
  },
  secondaryBtnText: {fontSize: 14, fontWeight: '700', color: NAVY},

  bottomNav: {
    flexDirection: 'row',
    justifyContent: 'space-around',
    backgroundColor: CARD,
    borderRadius: 18,
    paddingVertical: 12,
    marginTop: 8,
    shadowColor: '#8a9bbd',
    shadowOffset: {width: 0, height: 4},
    shadowOpacity: 0.08,
    shadowRadius: 10,
    elevation: 3,
  },
  navItem: {alignItems: 'center'},
  navIcon: {fontSize: 18, color: MUTED, marginBottom: 4},
  navIconActive: {fontSize: 18, color: NAVY, marginBottom: 4},
  navLabel: {fontSize: 12, color: MUTED, fontWeight: '600'},
  navLabelActive: {fontSize: 12, color: NAVY, fontWeight: '800'},
});
