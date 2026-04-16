import React, {useState} from 'react';
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

import {AppButton, AppCard} from '../components';
import {ApiError} from '../src/services/api';
import {createInspectionRequest} from '../src/services/inspectionRequestApi';

type FieldErrors = {
  details?: string;
  contactNumber?: string;
};

function getFriendlyErrorMessage(error: unknown) {
  if (error instanceof ApiError) {
    if (error.status === 401) {
      return 'Your session has expired. Please log in again.';
    }

    return error.message;
  }

  return 'Something went wrong while submitting your inspection request.';
}

function formatDateForApi(date: Date) {
  const year = date.getFullYear();
  const month = `${date.getMonth() + 1}`.padStart(2, '0');
  const day = `${date.getDate()}`.padStart(2, '0');

  return `${year}-${month}-${day}`;
}

function sanitizeContactNumber(value: string) {
  return value.replace(/[^0-9+()\- ]/g, '');
}

export default function InspectionRequestScreen({navigation}: any) {
  const [details, setDetails] = useState('');
  const [contactNumber, setContactNumber] = useState('');
  const [dateNeeded, setDateNeeded] = useState('');
  const [selectedDate, setSelectedDate] = useState<Date | null>(null);
  const [pickerDate, setPickerDate] = useState(new Date());
  const [showDatePicker, setShowDatePicker] = useState(false);
  const [fieldErrors, setFieldErrors] = useState<FieldErrors>({});
  const [submitting, setSubmitting] = useState(false);
  const [errorMessage, setErrorMessage] = useState('');
  const [successMessage, setSuccessMessage] = useState('');

  const resetForm = () => {
    setDetails('');
    setContactNumber('');
    setDateNeeded('');
    setSelectedDate(null);
    setPickerDate(new Date());
    setFieldErrors({});
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

  const clearStatusMessages = () => {
    if (errorMessage) {
      setErrorMessage('');
    }

    if (successMessage) {
      setSuccessMessage('');
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

  const handleAndroidDateChange = (
    _event: DateTimePickerEvent,
    pickedDate?: Date,
  ) => {
    setShowDatePicker(false);

    if (pickedDate) {
      applySelectedDate(pickedDate);
    }
  };

  const handleIosDateChange = (_event: DateTimePickerEvent, pickedDate?: Date) => {
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
    const trimmedDetails = details.trim();
    const trimmedContactNumber = contactNumber.trim();
    const nextErrors: FieldErrors = {};

    if (!trimmedDetails) {
      nextErrors.details = 'Inspection details are required.';
    }

    if (!trimmedContactNumber) {
      nextErrors.contactNumber = 'Contact number is required.';
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
      setErrorMessage(getFriendlyErrorMessage(error));
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

        <View style={styles.fieldGroup}>
          <View style={styles.fieldHeader}>
            <Text style={styles.fieldLabel}>Date needed</Text>
            <Text style={styles.optionalText}>Optional</Text>
          </View>

          <Pressable
            onPress={openDatePicker}
            style={({pressed}) => [
              styles.input,
              styles.dateInput,
              pressed ? styles.pressedInput : null,
            ]}>
            <Text
              style={[
                styles.dateInputText,
                !dateNeeded ? styles.placeholderText : null,
              ]}>
              {dateNeeded || 'Select date'}
            </Text>
          </Pressable>

          <Text style={styles.helpText}>
            Tap the field to choose a date from the calendar picker.
          </Text>
        </View>
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

      {showDatePicker && Platform.OS === 'android' ? (
        <DateTimePicker
          mode="date"
          onChange={handleAndroidDateChange}
          value={pickerDate}
        />
      ) : null}

      <Modal
        animationType="fade"
        onRequestClose={handleIosCancel}
        transparent={true}
        visible={showDatePicker && Platform.OS === 'ios'}>
        <View style={styles.modalOverlay}>
          <View style={styles.modalCard}>
            <Text style={styles.modalTitle}>Select date</Text>

            <DateTimePicker
              display="spinner"
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
  optionalText: {
    color: '#64748b',
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
