import React, {useEffect, useMemo, useState} from 'react';
import {
  ActivityIndicator,
  KeyboardAvoidingView,
  Modal,
  Platform,
  Pressable,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  TouchableOpacity,
  View,
} from 'react-native';
import {ApiError, apiPost} from '../src/services/api';
import {solmateColors} from '../src/theme/colors';
import {getPasswordValidationError} from '../src/utils/passwordValidation';

const LOCATION_API_BASE_URL = 'https://psgc.gitlab.io/api';
const NCR_LOCATION = {
  code: '130000000',
  name: 'Metro Manila / NCR',
  kind: 'region',
};

function sanitizeNameInput(value) {
  return value.replace(/[^A-Za-zÀ-ÖØ-öø-ÿÑñ\s.'-]/g, '');
}

function normalizeName(value) {
  return sanitizeNameInput(value)
    .trim()
    .replace(/\s+/g, ' ')
    .toLocaleLowerCase('en-PH')
    .replace(/(^|[\s.'-])([A-Za-zÀ-ÖØ-öø-ÿÑñ])/g, (match, separator, letter) =>
      `${separator}${letter.toLocaleUpperCase('en-PH')}`,
    );
}

function sanitizeContactNumber(value) {
  return value.replace(/\D/g, '').slice(0, 11);
}

function sanitizeLandlineNumber(value) {
  return value.replace(/[^0-9()+\-\s]/g, '').slice(0, 30);
}

function buildAddress({houseNumber, streetName, barangay, city, province}) {
  return [
    houseNumber.trim(),
    streetName.trim(),
    barangay.trim(),
    city?.name || '',
    province?.name || '',
  ]
    .filter(Boolean)
    .join(', ');
}

function passwordRuleState(password) {
  return {
    length: password.length >= 8,
    uppercase: /[A-Z]/.test(password),
    special: /[^A-Za-z0-9]/.test(password),
  };
}

function Field({
  label,
  value,
  onChangeText,
  placeholder,
  keyboardType,
  autoCapitalize = 'none',
  textContentType,
  secureTextEntry,
  editable = true,
  onBlur,
}) {
  return (
    <View style={s.field}>
      <Text style={s.label}>{label}</Text>
      <TextInput
        autoCapitalize={autoCapitalize}
        autoCorrect={false}
        editable={editable}
        keyboardType={keyboardType}
        onBlur={onBlur}
        onChangeText={onChangeText}
        placeholder={placeholder}
        placeholderTextColor={solmateColors.mutedSoft}
        secureTextEntry={secureTextEntry}
        style={[s.input, !editable && s.inputDisabled]}
        textContentType={textContentType}
        value={value}
      />
    </View>
  );
}

function PasswordField({
  label,
  value,
  onChangeText,
  placeholder,
  visible,
  onToggleVisible,
}) {
  return (
    <View style={s.field}>
      <Text style={s.label}>{label}</Text>
      <View style={s.passwordRow}>
        <TextInput
          autoCapitalize="none"
          autoCorrect={false}
          onChangeText={onChangeText}
          placeholder={placeholder}
          placeholderTextColor={solmateColors.mutedSoft}
          secureTextEntry={!visible}
          style={[s.input, s.passwordInput]}
          textContentType="newPassword"
          value={value}
        />
        <TouchableOpacity
          activeOpacity={0.82}
          onPress={onToggleVisible}
          style={s.passwordToggle}>
          <Text style={s.passwordToggleText}>{visible ? 'Hide' : 'Show'}</Text>
        </TouchableOpacity>
      </View>
    </View>
  );
}

function SelectField({label, value, placeholder, disabled, onPress}) {
  return (
    <View style={s.field}>
      <Text style={s.label}>{label}</Text>
      <TouchableOpacity
        activeOpacity={0.82}
        disabled={disabled}
        onPress={onPress}
        style={[s.selectBtn, disabled && s.inputDisabled]}>
        <Text style={[s.selectText, !value && s.selectPlaceholder]}>
          {value || placeholder}
        </Text>
        <Text style={s.selectChevron}>⌄</Text>
      </TouchableOpacity>
    </View>
  );
}

function LocationPickerModal({
  visible,
  title,
  items,
  loading,
  emptyText,
  onClose,
  onSelect,
}) {
  const [query, setQuery] = useState('');

  useEffect(() => {
    if (visible) {
      setQuery('');
    }
  }, [visible]);

  const filteredItems = useMemo(() => {
    const cleanQuery = query.trim().toLocaleLowerCase('en-PH');

    if (!cleanQuery) {
      return items;
    }

    return items.filter(item =>
      item.name.toLocaleLowerCase('en-PH').includes(cleanQuery),
    );
  }, [items, query]);

  return (
    <Modal
      animationType="slide"
      onRequestClose={onClose}
      transparent
      visible={visible}>
      <View style={s.modalBackdrop}>
        <View style={s.modalCard}>
          <View style={s.modalHeader}>
            <Text style={s.modalTitle}>{title}</Text>
            <Pressable onPress={onClose} style={s.modalCloseBtn}>
              <Text style={s.modalCloseText}>Close</Text>
            </Pressable>
          </View>

          <TextInput
            autoCapitalize="words"
            onChangeText={setQuery}
            placeholder="Search"
            placeholderTextColor={solmateColors.mutedSoft}
            style={s.searchInput}
            value={query}
          />

          <ScrollView style={s.modalList} keyboardShouldPersistTaps="handled">
            {loading ? (
              <View style={s.modalState}>
                <ActivityIndicator color={solmateColors.accentStrong} />
                <Text style={s.modalStateText}>Loading...</Text>
              </View>
            ) : filteredItems.length === 0 ? (
              <View style={s.modalState}>
                <Text style={s.modalStateText}>{emptyText}</Text>
              </View>
            ) : (
              filteredItems.map(item => (
                <Pressable
                  key={item.code}
                  onPress={() => onSelect(item)}
                  style={({pressed}) => [
                    s.optionRow,
                    pressed && s.optionRowPressed,
                  ]}>
                  <Text style={s.optionText}>{item.name}</Text>
                </Pressable>
              ))
            )}
          </ScrollView>
        </View>
      </View>
    </Modal>
  );
}

export default function SignupScreen({navigation}) {
  const [step, setStep] = useState(0);
  const [firstName, setFirstName] = useState('');
  const [lastName, setLastName] = useState('');
  const [email, setEmail] = useState('');
  const [contact, setContact] = useState('');
  const [landline, setLandline] = useState('');
  const [houseNumber, setHouseNumber] = useState('');
  const [streetName, setStreetName] = useState('');
  const [barangay, setBarangay] = useState('');
  const [province, setProvince] = useState(null);
  const [city, setCity] = useState(null);
  const [password, setPassword] = useState('');
  const [confirmPw, setConfirmPw] = useState('');
  const [showPw, setShowPw] = useState(false);
  const [showConfirm, setShowConfirm] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const [provinces, setProvinces] = useState([]);
  const [cities, setCities] = useState([]);
  const [locationsLoading, setLocationsLoading] = useState(false);
  const [citiesLoading, setCitiesLoading] = useState(false);
  const [locationError, setLocationError] = useState('');
  const [pickerMode, setPickerMode] = useState(null);

  const currentAddress = useMemo(
    () => buildAddress({houseNumber, streetName, barangay, city, province}),
    [barangay, city, houseNumber, province, streetName],
  );

  const pwRules = passwordRuleState(password);

  const clearError = () => {
    if (error) {
      setError('');
    }
  };

  useEffect(() => {
    let cancelled = false;

    async function loadProvinces() {
      setLocationsLoading(true);
      setLocationError('');

      try {
        const response = await fetch(`${LOCATION_API_BASE_URL}/provinces/`, {
          headers: {Accept: 'application/json'},
        });

        if (!response.ok) {
          throw new Error(`Request failed with status ${response.status}`);
        }

        const data = await response.json();
        const nextProvinces = data
          .map(item => ({
            code: item.code,
            name: item.name,
            kind: 'province',
          }))
          .sort((a, b) => a.name.localeCompare(b.name));

        if (!cancelled) {
          setProvinces([NCR_LOCATION, ...nextProvinces]);
        }
      } catch (loadError) {
        if (!cancelled) {
          setLocationError('Unable to load Philippine locations.');
        }
      } finally {
        if (!cancelled) {
          setLocationsLoading(false);
        }
      }
    }

    loadProvinces();

    return () => {
      cancelled = true;
    };
  }, []);

  const loadCitiesForProvince = async nextProvince => {
    if (!nextProvince) {
      setCities([]);
      return;
    }

    setCitiesLoading(true);
    setLocationError('');

    try {
      const endpoint =
        nextProvince.kind === 'region'
          ? `${LOCATION_API_BASE_URL}/regions/${nextProvince.code}/cities-municipalities/`
          : `${LOCATION_API_BASE_URL}/provinces/${nextProvince.code}/cities-municipalities/`;
      const response = await fetch(endpoint, {headers: {Accept: 'application/json'}});

      if (!response.ok) {
        throw new Error(`Request failed with status ${response.status}`);
      }

      const data = await response.json();
      setCities(
        data
          .map(item => ({
            code: item.code,
            name: item.name,
          }))
          .sort((a, b) => a.name.localeCompare(b.name)),
      );
    } catch (loadError) {
      setCities([]);
      setLocationError('Unable to load cities and municipalities.');
    } finally {
      setCitiesLoading(false);
    }
  };

  const validateStep = stepIndex => {
    setError('');

    if (stepIndex === 0) {
      const normalizedFirst = normalizeName(firstName);
      const normalizedLast = normalizeName(lastName);

      if (!normalizedFirst || !normalizedLast || !email.trim() || !contact.trim()) {
        setError('Please fill in all required fields.');
        return false;
      }

      if (/\d/.test(firstName) || /\d/.test(lastName)) {
        setError('Names cannot contain numbers.');
        return false;
      }

      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.trim())) {
        setError('Please enter a valid email address.');
        return false;
      }

      if (sanitizeContactNumber(contact).length !== 11) {
        setError('Contact number must be exactly 11 digits.');
        return false;
      }

      setFirstName(normalizedFirst);
      setLastName(normalizedLast);
      return true;
    }

    if (stepIndex === 1) {
      if (!houseNumber.trim() || !province || !city) {
        setError('Please complete all required address fields.');
        return false;
      }

      if (locationError) {
        setError(locationError);
        return false;
      }

      if (!currentAddress.trim()) {
        setError('Address is required.');
        return false;
      }

      return true;
    }

    if (stepIndex === 2) {
      if (!password.trim() || !confirmPw.trim()) {
        setError('Please enter and confirm your password.');
        return false;
      }

      if (password !== confirmPw) {
        setError('Passwords do not match.');
        return false;
      }

      const pwError = getPasswordValidationError(password);

      if (pwError) {
        setError(pwError);
        return false;
      }
    }

    return true;
  };

  const goNext = () => {
    if (validateStep(step)) {
      setStep(current => Math.min(current + 1, 2));
    }
  };

  const goBack = () => {
    setError('');
    setStep(current => Math.max(current - 1, 0));
  };

  const handleRegister = async () => {
    if (submitting || !validateStep(2)) {
      return;
    }

    const contactDigits = sanitizeContactNumber(contact);
    const trimmedLandline = sanitizeLandlineNumber(landline).trim();

    try {
      setSubmitting(true);
      setError('');
      await apiPost(
        '/register',
        {
          first_name: normalizeName(firstName),
          last_name: normalizeName(lastName),
          email: email.trim(),
          address: currentAddress,
          contact_number: contactDigits,
          landline_number: trimmedLandline || null,
          password,
          password_confirmation: confirmPw,
        },
        false,
      );
      setSuccess('Account created! Please verify your email before logging in.');
      setTimeout(() => navigation?.navigate?.('Login'), 1200);
    } catch (err) {
      setError(
        err instanceof ApiError
          ? err.message
          : 'Registration failed. Please try again.',
      );
    } finally {
      setSubmitting(false);
    }
  };

  const selectProvince = item => {
    setProvince(item);
    setCity(null);
    setCities([]);
    setPickerMode(null);
    clearError();
    loadCitiesForProvince(item);
  };

  const selectCity = item => {
    setCity(item);
    setPickerMode(null);
    clearError();
  };

  const renderStep = () => {
    if (step === 0) {
      return (
        <>
          <Field
            autoCapitalize="words"
            label="First Name"
            onBlur={() => setFirstName(value => normalizeName(value))}
            onChangeText={value => {
              clearError();
              setFirstName(sanitizeNameInput(value));
            }}
            placeholder="Juan"
            textContentType="givenName"
            value={firstName}
          />
          <Field
            autoCapitalize="words"
            label="Last Name"
            onBlur={() => setLastName(value => normalizeName(value))}
            onChangeText={value => {
              clearError();
              setLastName(sanitizeNameInput(value));
            }}
            placeholder="Dela Cruz"
            textContentType="familyName"
            value={lastName}
          />
          <Field
            autoCapitalize="none"
            keyboardType="email-address"
            label="Email"
            onChangeText={value => {
              clearError();
              setEmail(value);
            }}
            placeholder="name@email.com"
            textContentType="emailAddress"
            value={email}
          />
          <Field
            keyboardType="phone-pad"
            label="Contact Number"
            onChangeText={value => {
              clearError();
              setContact(sanitizeContactNumber(value));
            }}
            placeholder="09XXXXXXXXX"
            textContentType="telephoneNumber"
            value={contact}
          />
          <Field
            keyboardType="phone-pad"
            label="Landline Number (Optional)"
            onChangeText={value => {
              clearError();
              setLandline(sanitizeLandlineNumber(value));
            }}
            placeholder="(02) 8123-4567"
            textContentType="telephoneNumber"
            value={landline}
          />
        </>
      );
    }

    if (step === 1) {
      return (
        <>
          <Field
            autoCapitalize="words"
            label="House / Unit / Block / Lot"
            onChangeText={value => {
              clearError();
              setHouseNumber(value);
            }}
            placeholder="e.g. Unit 4B, Block 8 Lot 12"
            textContentType="streetAddressLine1"
            value={houseNumber}
          />
          <Field
            autoCapitalize="words"
            label="Street Name (Optional)"
            onChangeText={value => {
              clearError();
              setStreetName(value);
            }}
            placeholder="e.g. Mabini Street"
            textContentType="streetAddressLine2"
            value={streetName}
          />
          <Field
            autoCapitalize="words"
            label="Barangay (Optional)"
            onChangeText={value => {
              clearError();
              setBarangay(value);
            }}
            placeholder="e.g. Barangay San Antonio"
            value={barangay}
          />
          <SelectField
            disabled={locationsLoading}
            label="Province / NCR"
            onPress={() => setPickerMode('province')}
            placeholder={locationsLoading ? 'Loading...' : 'Select province or NCR'}
            value={province?.name}
          />
          <SelectField
            disabled={!province || citiesLoading}
            label="City / Municipality"
            onPress={() => setPickerMode('city')}
            placeholder={
              !province
                ? 'Select province first'
                : citiesLoading
                  ? 'Loading...'
                  : 'Select city or municipality'
            }
            value={city?.name}
          />
        </>
      );
    }

    return (
      <>
        <PasswordField
          label="Password"
          onChangeText={value => {
            clearError();
            setPassword(value);
          }}
          onToggleVisible={() => setShowPw(current => !current)}
          placeholder="Create a secure password"
          value={password}
          visible={showPw}
        />
        <View style={s.passwordChecks}>
          <View style={s.passwordCheckRow}>
            <Text style={[s.checkMark, pwRules.length && s.checkMarkMet]}>
              {pwRules.length ? '✓' : ''}
            </Text>
            <Text style={[s.checkText, pwRules.length && s.checkTextMet]}>
              At least 8 characters
            </Text>
          </View>
          <View style={s.passwordCheckRow}>
            <Text style={[s.checkMark, pwRules.uppercase && s.checkMarkMet]}>
              {pwRules.uppercase ? '✓' : ''}
            </Text>
            <Text style={[s.checkText, pwRules.uppercase && s.checkTextMet]}>
              One uppercase letter
            </Text>
          </View>
          <View style={s.passwordCheckRow}>
            <Text style={[s.checkMark, pwRules.special && s.checkMarkMet]}>
              {pwRules.special ? '✓' : ''}
            </Text>
            <Text style={[s.checkText, pwRules.special && s.checkTextMet]}>
              One special character
            </Text>
          </View>
        </View>
        <PasswordField
          label="Confirm Password"
          onChangeText={value => {
            clearError();
            setConfirmPw(value);
          }}
          onToggleVisible={() => setShowConfirm(current => !current)}
          placeholder="Re-enter your password"
          value={confirmPw}
          visible={showConfirm}
        />
      </>
    );
  };

  return (
    <KeyboardAvoidingView
      behavior={Platform.OS === 'ios' ? 'padding' : undefined}
      keyboardVerticalOffset={Platform.OS === 'ios' ? 10 : 0}
      style={s.screen}>
      <ScrollView
        contentContainerStyle={s.scroll}
        keyboardDismissMode={Platform.OS === 'ios' ? 'interactive' : 'on-drag'}
        keyboardShouldPersistTaps="handled"
        showsVerticalScrollIndicator={false}>
        <View style={s.brandRow}>
          <Text style={s.brandSol}>Sol</Text>
          <Text style={s.brandMate}>Mate</Text>
        </View>

        <Text style={s.pageTitle}>Create Account</Text>

        <View style={s.card}>
          {success ? (
            <View style={s.successBox}>
              <Text style={s.successText}>{success}</Text>
            </View>
          ) : null}

          {error ? (
            <View style={s.errorBox}>
              <Text style={s.errorText}>{error}</Text>
            </View>
          ) : null}

          <View style={s.stepHeader}>
            {[0, 1, 2].map(index => (
              <View
                key={index}
                style={[
                  s.stepDot,
                  index <= step && s.stepDotActive,
                  index === step && s.stepDotCurrent,
                ]}>
                <Text
                  style={[
                    s.stepDotText,
                    index <= step && s.stepDotTextActive,
                  ]}>
                  {index + 1}
                </Text>
              </View>
            ))}
          </View>

          <Text style={s.stepTitle}>
            {step === 0
              ? 'Personal Details'
              : step === 1
                ? 'Address Details'
                : 'Secure Account'}
          </Text>

          {renderStep()}

          <View style={s.actionRow}>
            {step > 0 ? (
              <TouchableOpacity
                activeOpacity={0.84}
                disabled={submitting}
                onPress={goBack}
                style={s.backBtn}>
                <Text style={s.backBtnText}>Back</Text>
              </TouchableOpacity>
            ) : null}

            {step < 2 ? (
              <TouchableOpacity
                activeOpacity={0.88}
                onPress={goNext}
                style={[s.nextBtn, step === 0 && s.fullWidthBtn]}>
                <Text style={s.nextBtnText}>Next</Text>
              </TouchableOpacity>
            ) : (
              <TouchableOpacity
                activeOpacity={0.88}
                disabled={submitting}
                onPress={handleRegister}
                style={[s.nextBtn, submitting && s.btnDisabled]}>
                {submitting ? (
                  <ActivityIndicator color={solmateColors.text} />
                ) : (
                  <Text style={s.nextBtnText}>Create Account</Text>
                )}
              </TouchableOpacity>
            )}
          </View>

          <TouchableOpacity
            activeOpacity={0.75}
            onPress={() => navigation?.navigate?.('Login')}
            style={s.bottomLink}>
            <Text style={s.bottomLinkText}>
              Have an account? <Text style={s.bottomLinkBold}>Login Here</Text>
            </Text>
          </TouchableOpacity>
        </View>
      </ScrollView>

      <LocationPickerModal
        emptyText="No province found."
        items={provinces}
        loading={locationsLoading}
        onClose={() => setPickerMode(null)}
        onSelect={selectProvince}
        title="Province / NCR"
        visible={pickerMode === 'province'}
      />
      <LocationPickerModal
        emptyText="No city or municipality found."
        items={cities}
        loading={citiesLoading}
        onClose={() => setPickerMode(null)}
        onSelect={selectCity}
        title="City / Municipality"
        visible={pickerMode === 'city'}
      />
    </KeyboardAvoidingView>
  );
}

const s = StyleSheet.create({
  screen: {
    flex: 1,
    backgroundColor: solmateColors.background,
  },
  scroll: {
    flexGrow: 1,
    justifyContent: 'center',
    paddingHorizontal: 22,
    paddingVertical: 30,
  },
  brandRow: {
    flexDirection: 'row',
    justifyContent: 'center',
    marginBottom: 6,
  },
  brandSol: {
    color: solmateColors.navy,
    fontSize: 28,
    fontWeight: '800',
  },
  brandMate: {
    color: solmateColors.primary,
    fontSize: 28,
    fontWeight: '800',
  },
  pageTitle: {
    color: solmateColors.text,
    fontSize: 30,
    fontWeight: '800',
    textAlign: 'center',
    marginBottom: 18,
  },
  card: {
    width: '100%',
    maxWidth: 430,
    alignSelf: 'center',
    backgroundColor: solmateColors.white,
    borderRadius: 22,
    paddingHorizontal: 20,
    paddingVertical: 24,
    shadowColor: solmateColors.shadow,
    shadowOffset: {width: 0, height: 8},
    shadowOpacity: 0.14,
    shadowRadius: 16,
    elevation: 7,
  },
  stepHeader: {
    flexDirection: 'row',
    justifyContent: 'center',
    gap: 10,
    marginBottom: 16,
  },
  stepDot: {
    width: 34,
    height: 34,
    borderRadius: 17,
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: solmateColors.backgroundSoft,
    borderWidth: 1,
    borderColor: solmateColors.border,
  },
  stepDotActive: {
    backgroundColor: '#FFF4B8',
    borderColor: solmateColors.primary,
  },
  stepDotCurrent: {
    backgroundColor: solmateColors.primary,
  },
  stepDotText: {
    color: solmateColors.muted,
    fontSize: 13,
    fontWeight: '800',
  },
  stepDotTextActive: {
    color: solmateColors.text,
  },
  stepTitle: {
    color: solmateColors.text,
    fontSize: 18,
    fontWeight: '800',
    marginBottom: 16,
    textAlign: 'center',
  },
  field: {
    marginBottom: 14,
  },
  label: {
    color: solmateColors.navy,
    fontSize: 13,
    fontWeight: '700',
    marginBottom: 6,
  },
  input: {
    minHeight: 52,
    borderWidth: 1,
    borderColor: solmateColors.border,
    borderRadius: 14,
    backgroundColor: solmateColors.backgroundSoft,
    color: solmateColors.text,
    fontSize: 15,
    paddingHorizontal: 15,
  },
  inputDisabled: {
    opacity: 0.65,
  },
  passwordRow: {
    flexDirection: 'row',
    gap: 10,
  },
  passwordInput: {
    flex: 1,
  },
  passwordToggle: {
    width: 74,
    minHeight: 52,
    alignItems: 'center',
    justifyContent: 'center',
    borderRadius: 14,
    borderWidth: 1,
    borderColor: solmateColors.border,
    backgroundColor: solmateColors.backgroundSoft,
  },
  passwordToggleText: {
    color: solmateColors.navy,
    fontSize: 13,
    fontWeight: '800',
  },
  selectBtn: {
    minHeight: 52,
    borderWidth: 1,
    borderColor: solmateColors.border,
    borderRadius: 14,
    backgroundColor: solmateColors.backgroundSoft,
    paddingHorizontal: 15,
    flexDirection: 'row',
    alignItems: 'center',
  },
  selectText: {
    flex: 1,
    color: solmateColors.text,
    fontSize: 15,
  },
  selectPlaceholder: {
    color: solmateColors.mutedSoft,
  },
  selectChevron: {
    color: solmateColors.muted,
    fontSize: 20,
    fontWeight: '800',
  },
  passwordChecks: {
    gap: 8,
    marginTop: -4,
    marginBottom: 16,
  },
  passwordCheckRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 9,
  },
  checkMark: {
    width: 20,
    height: 20,
    borderRadius: 10,
    borderWidth: 1,
    borderColor: solmateColors.borderStrong,
    color: solmateColors.white,
    textAlign: 'center',
    lineHeight: 18,
    fontSize: 12,
    fontWeight: '800',
  },
  checkMarkMet: {
    backgroundColor: '#0C8D4A',
    borderColor: '#0C8D4A',
  },
  checkText: {
    color: solmateColors.muted,
    fontSize: 13,
  },
  checkTextMet: {
    color: '#0C6B3A',
    fontWeight: '700',
  },
  actionRow: {
    flexDirection: 'row',
    gap: 12,
    marginTop: 4,
  },
  backBtn: {
    flex: 1,
    height: 52,
    borderRadius: 26,
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: solmateColors.white,
    borderWidth: 1.5,
    borderColor: solmateColors.borderStrong,
  },
  backBtnText: {
    color: solmateColors.navy,
    fontSize: 16,
    fontWeight: '800',
  },
  nextBtn: {
    flex: 1.4,
    height: 52,
    borderRadius: 26,
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: solmateColors.primary,
  },
  fullWidthBtn: {
    flex: 1,
  },
  nextBtnText: {
    color: solmateColors.text,
    fontSize: 16,
    fontWeight: '800',
  },
  btnDisabled: {
    opacity: 0.65,
  },
  bottomLink: {
    marginTop: 18,
    alignItems: 'center',
  },
  bottomLinkText: {
    color: solmateColors.accentStrong,
    fontSize: 14,
    fontWeight: '600',
    textAlign: 'center',
  },
  bottomLinkBold: {
    color: solmateColors.text,
    fontWeight: '800',
  },
  errorBox: {
    borderWidth: 1,
    borderColor: solmateColors.danger,
    backgroundColor: solmateColors.dangerSoft,
    borderRadius: 14,
    paddingHorizontal: 13,
    paddingVertical: 10,
    marginBottom: 14,
  },
  errorText: {
    color: '#8F2D2D',
    fontSize: 13,
    lineHeight: 18,
  },
  successBox: {
    borderWidth: 1,
    borderColor: solmateColors.accentSky,
    backgroundColor: solmateColors.navy,
    borderRadius: 14,
    paddingHorizontal: 13,
    paddingVertical: 10,
    marginBottom: 14,
  },
  successText: {
    color: solmateColors.white,
    fontSize: 13,
    lineHeight: 18,
    fontWeight: '700',
  },
  modalBackdrop: {
    flex: 1,
    justifyContent: 'flex-end',
    backgroundColor: 'rgba(15, 28, 52, 0.35)',
  },
  modalCard: {
    maxHeight: '78%',
    backgroundColor: solmateColors.white,
    borderTopLeftRadius: 24,
    borderTopRightRadius: 24,
    paddingHorizontal: 18,
    paddingTop: 18,
    paddingBottom: 26,
  },
  modalHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginBottom: 12,
  },
  modalTitle: {
    color: solmateColors.text,
    fontSize: 19,
    fontWeight: '800',
  },
  modalCloseBtn: {
    paddingHorizontal: 12,
    paddingVertical: 8,
  },
  modalCloseText: {
    color: solmateColors.accentStrong,
    fontSize: 14,
    fontWeight: '800',
  },
  searchInput: {
    height: 48,
    borderWidth: 1,
    borderColor: solmateColors.border,
    borderRadius: 14,
    backgroundColor: solmateColors.backgroundSoft,
    color: solmateColors.text,
    fontSize: 15,
    paddingHorizontal: 14,
    marginBottom: 12,
  },
  modalList: {
    maxHeight: 420,
  },
  modalState: {
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 28,
    gap: 10,
  },
  modalStateText: {
    color: solmateColors.muted,
    fontSize: 14,
  },
  optionRow: {
    paddingVertical: 14,
    borderBottomWidth: 1,
    borderBottomColor: solmateColors.border,
  },
  optionRowPressed: {
    opacity: 0.75,
  },
  optionText: {
    color: solmateColors.text,
    fontSize: 15,
    fontWeight: '600',
  },
});
