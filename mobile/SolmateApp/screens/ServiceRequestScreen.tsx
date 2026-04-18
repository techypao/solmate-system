import React, {useCallback, useEffect, useState} from 'react';
import {useFocusEffect} from '@react-navigation/native';
import {
  Pressable,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  View,
} from 'react-native';

import {AppButton, AppCard, PreferredDateCalendar} from '../components';
import {ApiError} from '../src/services/api';
import {getUnavailablePreferredDates} from '../src/services/preferredDateAvailabilityApi';
import {createServiceRequest} from '../src/services/serviceRequestApi';

const REQUEST_TYPE_OPTIONS = [
  'Battery Check',
  'Panel Cleaning',
  'Inverter Issue',
  'System Maintenance',
];

type FieldErrors = {
  requestType?: string;
  details?: string;
  contactNumber?: string;
  dateNeeded?: string;
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

  return 'Something went wrong while submitting your service request.';
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

export default function ServiceRequestScreen({navigation}: any) {
  const [requestType, setRequestType] = useState('');
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
    setRequestType('');
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

  const handleRequestTypeChange = (value: string) => {
    setRequestType(value);
    clearStatusMessages();

    if (fieldErrors.requestType) {
      setFieldErrors(currentErrors => ({
        ...currentErrors,
        requestType: undefined,
      }));
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
    const trimmedRequestType = requestType.trim();
    const trimmedDetails = details.trim();
    const trimmedContactNumber = contactNumber.trim();
    const nextErrors: FieldErrors = {};

    if (!trimmedRequestType) {
      nextErrors.requestType = 'Please choose or enter a request type.';
    }

    if (!trimmedDetails) {
      nextErrors.details = 'Please add details about the service you need.';
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

    if (dateNeeded && unavailableDates.includes(dateNeeded)) {
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

      const response = await createServiceRequest({
        request_type: requestType.trim(),
        details: details.trim(),
        ...(contactNumber.trim()
          ? {contact_number: contactNumber.trim()}
          : {}),
        ...(dateNeeded ? {date_needed: dateNeeded} : {}),
      });

      const createdServiceRequest = response?.data;

      if (createdServiceRequest?.id) {
        resetForm();
        navigation.replace('ServiceRequestDetail', {
          serviceRequestId: createdServiceRequest.id,
          initialServiceRequest: createdServiceRequest,
          mode: 'customer',
        });
        return;
      }

      resetForm();
      setSuccessMessage(
        response.message || 'Service request submitted successfully.',
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

  const selectedRequestType = requestType.trim();

  return (
    <ScrollView
      contentContainerStyle={styles.container}
      keyboardShouldPersistTaps="handled"
      showsVerticalScrollIndicator={false}>
      <View style={styles.heroCard}>
        <Text style={styles.eyebrow}>Request service</Text>
        <Text style={styles.title}>Schedule support for your solar system</Text>
        <Text style={styles.subtitle}>
          Choose the kind of help you need, describe the issue clearly, and add
          a preferred date if you already have one in mind.
        </Text>
      </View>

      <AppCard style={styles.sectionCard}>
        <Text style={styles.sectionTitle}>Service request details</Text>
        <Text style={styles.sectionSubtitle}>
          This request is submitted under your customer account and sent
          securely with your saved login session.
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
            <Text style={styles.fieldLabel}>Request type</Text>
            <Text style={styles.requiredText}>Required</Text>
          </View>

          <View style={styles.chipGroup}>
            {REQUEST_TYPE_OPTIONS.map(option => {
              const isSelected = selectedRequestType === option;

              return (
                <Pressable
                  key={option}
                  onPress={() => handleRequestTypeChange(option)}
                  style={({pressed}) => [
                    styles.typeChip,
                    isSelected ? styles.typeChipSelected : null,
                    pressed ? styles.pressedChip : null,
                  ]}>
                  <Text
                    style={[
                      styles.typeChipText,
                      isSelected ? styles.typeChipTextSelected : null,
                    ]}>
                    {option}
                  </Text>
                </Pressable>
              );
            })}
          </View>

          <TextInput
            autoCapitalize="words"
            onChangeText={handleRequestTypeChange}
            placeholder="Select a type above or enter a custom request"
            placeholderTextColor="#94a3b8"
            style={[
              styles.input,
              fieldErrors.requestType ? styles.inputError : null,
            ]}
            value={requestType}
          />

          <Text style={styles.helpText}>
            Common service types are listed above, but you can also enter a
            custom request that matches your concern.
          </Text>
          {fieldErrors.requestType ? (
            <Text style={styles.fieldErrorText}>{fieldErrors.requestType}</Text>
          ) : null}
        </View>

        <View style={styles.fieldGroup}>
          <View style={styles.fieldHeader}>
            <Text style={styles.fieldLabel}>Details</Text>
            <Text style={styles.requiredText}>Required</Text>
          </View>

          <TextInput
            multiline={true}
            numberOfLines={5}
            onChangeText={handleDetailsChange}
            placeholder="Explain what is happening and what kind of help you need"
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
            Example: our battery drains quickly at night, or one inverter is
            showing a warning light.
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
            Add the best number to call or text about this service request.
          </Text>
          {fieldErrors.contactNumber ? (
            <Text style={styles.fieldErrorText}>{fieldErrors.contactNumber}</Text>
          ) : null}
        </View>

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
      </AppCard>

      <View style={styles.submitCard}>
        <Text style={styles.submitTitle}>Ready to send your request?</Text>
        <Text style={styles.submitSubtitle}>
          After submission, you will be taken straight to the request details
          screen with its current status.
        </Text>

        <AppButton
          disabled={submitting}
          onPress={handleSubmit}
          style={styles.submitButton}
          title={
            submitting ? 'Submitting service request...' : 'Submit request'
          }
        />

        <AppButton
          onPress={() => navigation.navigate('ServiceRequestList')}
          style={styles.secondaryButton}
          title="View my service requests"
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
    backgroundColor: '#fef3c7',
    borderRadius: 24,
    marginBottom: 18,
    padding: 22,
  },
  eyebrow: {
    color: '#b45309',
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
  chipGroup: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 10,
    marginBottom: 12,
  },
  typeChip: {
    backgroundColor: '#fff7ed',
    borderColor: '#fdba74',
    borderRadius: 999,
    borderWidth: 1,
    paddingHorizontal: 14,
    paddingVertical: 10,
  },
  typeChipSelected: {
    backgroundColor: '#f59e0b',
    borderColor: '#f59e0b',
  },
  typeChipText: {
    color: '#9a3412',
    fontSize: 13,
    fontWeight: '700',
  },
  typeChipTextSelected: {
    color: '#ffffff',
  },
  pressedChip: {
    opacity: 0.88,
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
    color: '#dc2626',
    fontSize: 13,
    lineHeight: 18,
    marginTop: 6,
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
