import React, { useMemo, useState } from 'react';
import DateTimePicker, {
  DateTimePickerEvent,
} from '@react-native-community/datetimepicker';
import {
  Modal,
  Platform,
  Pressable,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  View,
} from 'react-native';

import { AppButton, AppCard } from '../components';
import { ApiError } from '../src/services/api';
import { createServiceRequest } from '../src/services/serviceRequestApi';

const REQUEST_TYPE_OPTIONS = [
  'Battery Check',
  'Panel Cleaning',
  'Inverter Issue',
  'System Maintenance',
];

type FieldErrors = {
  requestType?: string;
  details?: string;
};

function getFriendlyErrorMessage(error: unknown) {
  if (error instanceof ApiError) {
    if (error.status === 401) {
      return 'Your session has expired. Please log in again.';
    }

    return error.message;
  }

  return 'Something went wrong while submitting your service request.';
}

function formatDateForApi(date: Date) {
  const year = date.getFullYear();
  const month = `${date.getMonth() + 1}`.padStart(2, '0');
  const day = `${date.getDate()}`.padStart(2, '0');

  return `${year}-${month}-${day}`;
}

function formatDateForDisplay(date: Date) {
  return date.toLocaleDateString(undefined, {
    month: 'long',
    day: 'numeric',
    year: 'numeric',
  });
}

export default function ServiceRequestScreen({ navigation }: any) {
  const [requestType, setRequestType] = useState('');
  const [details, setDetails] = useState('');
  const [dateNeeded, setDateNeeded] = useState('');
  const [selectedDate, setSelectedDate] = useState<Date | null>(null);
  const [pickerDate, setPickerDate] = useState(new Date());
  const [showDatePicker, setShowDatePicker] = useState(false);
  const [fieldErrors, setFieldErrors] = useState<FieldErrors>({});
  const [submitting, setSubmitting] = useState(false);
  const [errorMessage, setErrorMessage] = useState('');
  const [successMessage, setSuccessMessage] = useState('');

  const minimumDate = useMemo(() => new Date(), []);

  const resetForm = () => {
    setRequestType('');
    setDetails('');
    setDateNeeded('');
    setSelectedDate(null);
    setPickerDate(new Date());
    setFieldErrors({});
  };

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

  const openDatePicker = () => {
    clearStatusMessages();
    setPickerDate(selectedDate || new Date());
    setShowDatePicker(true);
  };

  const applySelectedDate = (date: Date) => {
    setSelectedDate(date);
    setPickerDate(date);
    setDateNeeded(formatDateForApi(date));
    clearStatusMessages();
  };

  const clearSelectedDate = () => {
    setSelectedDate(null);
    setDateNeeded('');
    setPickerDate(new Date());
    clearStatusMessages();
  };

  const handleAndroidDateChange = (
    _event: DateTimePickerEvent,
    pickedDate?: Date,
  ) => {
    setShowDatePicker(false);

    if (pickedDate) {
      applySelectedDate(pickedDate);
    }
  };

  const handleIosDateChange = (
    _event: DateTimePickerEvent,
    pickedDate?: Date,
  ) => {
    if (pickedDate) {
      setPickerDate(pickedDate);
    }
  };

  const handleIosCancel = () => {
    setShowDatePicker(false);
  };

  const handleIosConfirm = () => {
    applySelectedDate(pickerDate);
    setShowDatePicker(false);
  };

  const validateForm = () => {
    const trimmedRequestType = requestType.trim();
    const trimmedDetails = details.trim();
    const nextErrors: FieldErrors = {};

    if (!trimmedRequestType) {
      nextErrors.requestType = 'Please choose or enter a request type.';
    }

    if (!trimmedDetails) {
      nextErrors.details = 'Please add details about the service you need.';
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
    if (!validateForm()) {
      return;
    }

    try {
      setSubmitting(true);
      setErrorMessage('');
      setSuccessMessage('');

      const response = await createServiceRequest({
        request_type: requestType.trim(),
        details: details.trim(),
        ...(dateNeeded ? { date_needed: dateNeeded } : {}),
      });

      resetForm();
      setSuccessMessage(
        response.message || 'Service request submitted successfully.',
      );
    } catch (error) {
      setErrorMessage(getFriendlyErrorMessage(error));
    } finally {
      setSubmitting(false);
    }
  };

  const selectedRequestType = requestType.trim();

  return (
    <ScrollView
      contentContainerStyle={styles.container}
      keyboardShouldPersistTaps="handled"
      showsVerticalScrollIndicator={false}
    >
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
                  style={({ pressed }) => [
                    styles.typeChip,
                    isSelected ? styles.typeChipSelected : null,
                    pressed ? styles.pressedChip : null,
                  ]}
                >
                  <Text
                    style={[
                      styles.typeChipText,
                      isSelected ? styles.typeChipTextSelected : null,
                    ]}
                  >
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
            <Text style={styles.fieldLabel}>Preferred date</Text>
            <Text style={styles.optionalText}>Optional</Text>
          </View>

          <Pressable
            onPress={openDatePicker}
            style={({ pressed }) => [
              styles.input,
              styles.dateInput,
              pressed ? styles.pressedInput : null,
            ]}
          >
            <Text
              style={[
                styles.dateInputText,
                !selectedDate ? styles.placeholderText : null,
              ]}
            >
              {selectedDate
                ? formatDateForDisplay(selectedDate)
                : 'Select date'}
            </Text>
          </Pressable>

          <Text style={styles.helpText}>
            Tap to choose a customer-friendly calendar date for your preferred
            service visit.
          </Text>

          {selectedDate ? (
            <Pressable
              onPress={clearSelectedDate}
              style={styles.clearDateButton}
            >
              <Text style={styles.clearDateText}>Clear selected date</Text>
            </Pressable>
          ) : null}
        </View>
      </AppCard>

      <View style={styles.submitCard}>
        <Text style={styles.submitTitle}>Ready to send your request?</Text>
        <Text style={styles.submitSubtitle}>
          After submission, your request will appear in your service request
          history with its current status.
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

      {showDatePicker && Platform.OS === 'android' ? (
        <DateTimePicker
          minimumDate={minimumDate}
          mode="date"
          onChange={handleAndroidDateChange}
          value={pickerDate}
        />
      ) : null}

      <Modal
        animationType="fade"
        onRequestClose={handleIosCancel}
        transparent={true}
        visible={showDatePicker && Platform.OS === 'ios'}
      >
        <View style={styles.modalOverlay}>
          <View style={styles.modalCard}>
            <Text style={styles.modalTitle}>Select date</Text>

            <DateTimePicker
              display="spinner"
              minimumDate={minimumDate}
              mode="date"
              onChange={handleIosDateChange}
              value={pickerDate}
            />

            <View style={styles.modalActions}>
              <AppButton
                onPress={handleIosCancel}
                style={styles.modalButton}
                title="Cancel"
                variant="outline"
              />
              <AppButton
                onPress={handleIosConfirm}
                style={styles.modalButton}
                title="Done"
              />
            </View>
          </View>
        </View>
      </Modal>
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
  optionalText: {
    color: '#64748b',
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
  dateInput: {
    justifyContent: 'center',
    minHeight: 54,
  },
  dateInputText: {
    color: '#0f172a',
    fontSize: 16,
  },
  placeholderText: {
    color: '#94a3b8',
  },
  pressedInput: {
    opacity: 0.88,
  },
  clearDateButton: {
    alignSelf: 'flex-start',
    marginTop: 10,
  },
  clearDateText: {
    color: '#d97706',
    fontSize: 13,
    fontWeight: '700',
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
  modalOverlay: {
    alignItems: 'center',
    backgroundColor: 'rgba(15, 23, 42, 0.35)',
    flex: 1,
    justifyContent: 'center',
    padding: 20,
  },
  modalCard: {
    backgroundColor: '#ffffff',
    borderRadius: 24,
    padding: 20,
    width: '100%',
  },
  modalTitle: {
    color: '#0f172a',
    fontSize: 18,
    fontWeight: '800',
    marginBottom: 12,
    textAlign: 'center',
  },
  modalActions: {
    flexDirection: 'row',
    gap: 12,
    marginTop: 12,
  },
  modalButton: {
    flex: 1,
  },
});
