import React, {useCallback, useEffect, useState} from 'react';
import {useFocusEffect} from '@react-navigation/native';
import {ScrollView, StyleSheet, Text, TextInput, View} from 'react-native';

import {AppButton, AppCard, PreferredDateCalendar} from '../components';
import {ApiError} from '../src/services/api';
import {getUnavailablePreferredDates} from '../src/services/preferredDateAvailabilityApi';
import {createInspectionRequest} from '../src/services/inspectionRequestApi';

type FieldErrors = {
  details?: string;
  contactNumber?: string;
  dateNeeded?: string;
};

const RESERVED_DATE_MESSAGE =
  'Selected date is already reserved. Please choose another date.';

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
  if (!(error instanceof ApiError)) {
    return null;
  }

  const messages = error.errors?.[field];

  if (Array.isArray(messages) && messages.length > 0) {
    return messages[0];
  }

  return null;
}

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
    if (errorMessage) {
      setErrorMessage('');
    }

    if (successMessage) {
      setSuccessMessage('');
    }
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
    if (submitting) {
      return;
    }

    if (!validateForm()) {
      return;
    }

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
    <ScrollView
      contentContainerStyle={styles.container}
      keyboardShouldPersistTaps="handled"
      showsVerticalScrollIndicator={false}>
      <View style={styles.heroCard}>
        <Text style={styles.eyebrow}>Request inspection</Text>
        <Text style={styles.title}>Book a site inspection</Text>
        <Text style={styles.subtitle}>
          Tell us what you need checked and add a preferred date if you already
          have one in mind.
        </Text>
      </View>

      <AppCard style={styles.sectionCard}>
        <Text style={styles.sectionTitle}>Inspection details</Text>
        <Text style={styles.sectionSubtitle}>
          This request is submitted under your customer account and sent
          directly to the backend using your saved login token.
        </Text>

        {errorMessage ? (
          <View style={styles.errorBanner}>
            <Text style={styles.bannerTitle}>Unable to submit</Text>
            <Text style={styles.bannerText}>{errorMessage}</Text>
          </View>
        ) : null}

        {successMessage ? (
          <View style={styles.successBanner}>
            <Text style={styles.successTitle}>Request submitted</Text>
            <Text style={styles.successText}>{successMessage}</Text>
          </View>
        ) : null}

        <View style={styles.fieldGroup}>
          <View style={styles.fieldHeader}>
            <Text style={styles.fieldLabel}>Details</Text>
            <Text style={styles.requiredText}>Required</Text>
          </View>

          <TextInput
            multiline={true}
            numberOfLines={5}
            onChangeText={handleDetailsChange}
            placeholder="Describe the inspection you need"
            placeholderTextColor="#94a3b8"
            style={[
              styles.input,
              styles.textArea,
              fieldErrors.details ? styles.inputError : null,
            ]}
            textAlignVertical="top"
            value={details}
          />

          <Text style={styles.helpText}>
            Example: roof check, panel placement review, or site condition
            concerns.
          </Text>
          {fieldErrors.details ? (
            <Text style={styles.fieldErrorText}>{fieldErrors.details}</Text>
          ) : null}
        </View>

        <View style={styles.fieldGroup}>
          <View style={styles.fieldHeader}>
            <Text style={styles.fieldLabel}>Contact number</Text>
            <Text style={styles.requiredText}>Required</Text>
          </View>

          <TextInput
            keyboardType="phone-pad"
            onChangeText={handleContactNumberChange}
            placeholder="Enter a phone number we can reach"
            placeholderTextColor="#94a3b8"
            style={[
              styles.input,
              fieldErrors.contactNumber ? styles.inputError : null,
            ]}
            value={contactNumber}
          />

          <Text style={styles.helpText}>
            Add the best number to call or text about this inspection request.
          </Text>
          {fieldErrors.contactNumber ? (
            <Text style={styles.fieldErrorText}>{fieldErrors.contactNumber}</Text>
          ) : null}
        </View>

        <PreferredDateCalendar
          availabilityMessage={availabilityMessage}
          errorText={fieldErrors.dateNeeded}
          helperText="Some dates may already be reserved by other active requests. The backend will always confirm availability when you submit."
          label="Date needed"
          onClearDate={clearSelectedDate}
          onSelectDate={handleDateSelect}
          reservedDateMessage={RESERVED_DATE_MESSAGE}
          selectedDate={dateNeeded}
          unavailableDates={unavailableDates}
        />
      </AppCard>

      <View style={styles.submitCard}>
        <Text style={styles.submitTitle}>Ready to send your request?</Text>
        <Text style={styles.submitSubtitle}>
          After submission, you will be taken straight to the request details
          screen so you can review its status.
        </Text>

        <AppButton
          disabled={submitting}
          onPress={handleSubmit}
          style={styles.submitButton}
          title={
            submitting ? 'Submitting inspection request...' : 'Submit request'
          }
        />

        <AppButton
          onPress={() => navigation.navigate('InspectionRequestList')}
          style={styles.secondaryButton}
          title="View my inspection requests"
          variant="outline"
        />
      </View>
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: {
    backgroundColor: '#f5f7fb',
    padding: 20,
    paddingBottom: 32,
  },
  heroCard: {
    backgroundColor: '#dcfce7',
    borderRadius: 24,
    marginBottom: 18,
    padding: 22,
  },
  eyebrow: {
    color: '#15803d',
    fontSize: 12,
    fontWeight: '700',
    letterSpacing: 0.6,
    marginBottom: 8,
    textTransform: 'uppercase',
  },
  title: {
    color: '#0f172a',
    fontSize: 28,
    fontWeight: '800',
    marginBottom: 10,
  },
  subtitle: {
    color: '#334155',
    fontSize: 15,
    lineHeight: 22,
  },
  sectionCard: {
    marginBottom: 16,
  },
  sectionTitle: {
    color: '#0f172a',
    fontSize: 22,
    fontWeight: '700',
    marginBottom: 6,
  },
  sectionSubtitle: {
    color: '#64748b',
    fontSize: 14,
    lineHeight: 20,
    marginBottom: 18,
  },
  errorBanner: {
    backgroundColor: '#fef2f2',
    borderColor: '#fecaca',
    borderRadius: 16,
    borderWidth: 1,
    marginBottom: 16,
    padding: 14,
  },
  successBanner: {
    backgroundColor: '#f0fdf4',
    borderColor: '#bbf7d0',
    borderRadius: 16,
    borderWidth: 1,
    marginBottom: 16,
    padding: 14,
  },
  bannerTitle: {
    color: '#b91c1c',
    fontSize: 14,
    fontWeight: '700',
    marginBottom: 4,
  },
  bannerText: {
    color: '#991b1b',
    fontSize: 13,
    lineHeight: 18,
  },
  successTitle: {
    color: '#166534',
    fontSize: 14,
    fontWeight: '700',
    marginBottom: 4,
  },
  successText: {
    color: '#166534',
    fontSize: 13,
    lineHeight: 18,
  },
  fieldGroup: {
    marginBottom: 18,
  },
  fieldHeader: {
    alignItems: 'center',
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 8,
  },
  fieldLabel: {
    color: '#0f172a',
    fontSize: 16,
    fontWeight: '700',
  },
  requiredText: {
    color: '#b91c1c',
    fontSize: 12,
    fontWeight: '700',
    textTransform: 'uppercase',
  },
  input: {
    backgroundColor: '#f8fafc',
    borderColor: '#cbd5e1',
    borderRadius: 16,
    borderWidth: 1,
    color: '#0f172a',
    fontSize: 16,
    paddingHorizontal: 16,
    paddingVertical: 15,
  },
  inputError: {
    borderColor: '#ef4444',
  },
  textArea: {
    minHeight: 120,
  },
  helpText: {
    color: '#64748b',
    fontSize: 13,
    lineHeight: 18,
    marginTop: 6,
  },
  fieldErrorText: {
    color: '#b91c1c',
    fontSize: 13,
    fontWeight: '600',
    marginTop: 8,
  },
  submitCard: {
    backgroundColor: '#ffffff',
    borderColor: '#dbe4f0',
    borderRadius: 22,
    borderWidth: 1,
    padding: 18,
    shadowColor: '#0f172a',
    shadowOffset: {
      width: 0,
      height: 10,
    },
    shadowOpacity: 0.08,
    shadowRadius: 18,
    elevation: 3,
  },
  submitTitle: {
    color: '#0f172a',
    fontSize: 18,
    fontWeight: '800',
    marginBottom: 6,
  },
  submitSubtitle: {
    color: '#64748b',
    fontSize: 14,
    lineHeight: 20,
    marginBottom: 14,
  },
  submitButton: {
    minHeight: 56,
  },
  secondaryButton: {
    marginTop: 12,
  },
});
