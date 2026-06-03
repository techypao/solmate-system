import React, {useMemo, useState} from 'react';
import {
  ActivityIndicator,
  KeyboardAvoidingView,
  Platform,
  Pressable,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  TouchableOpacity,
  View,
} from 'react-native';
import MaterialCommunityIcons from 'react-native-vector-icons/MaterialCommunityIcons';
import {ApiError, apiPost} from '../src/services/api';
import {
  getPasswordValidationError,
} from '../src/utils/passwordValidation';

// ─── Color tokens (matched from screenshot) ───────────────────────────────────
const C = {
  bgTop:       '#C8D8F0',
  bgMid:       '#C8D8F0',
  bgBot:       '#C8D8F0',
  card:        '#FFFFFF',
  solText:     '#1A2440',
  mateText:    '#F5C000',
  title:       '#1A2440',
  subtitle:    '#7A88A8',
  label:       '#8A96B0',
  placeholder: '#AABCC8',
  inputText:   '#1A2440',
  inputBg:     '#FFFFFF',
  inputBorder: '#D8E2EE',
  eyeIcon:     '#6B7A99',
  button:      '#F5C000',
  buttonText:  '#1A2440',
  footerText:  '#8A96B0',
  footerLink:  '#1A2440',
  danger:      '#D83B3B',
  dangerBg:    '#FFF2F2',
  dangerBdr:   '#F0B4B4',
  success:     '#0C8D4A',
  successBg:   '#F0FAF4',
  successBdr:  '#A7D7BC',
};

// ─── Reusable input ───────────────────────────────────────────────────────────
function Field({
  label,
  placeholder,
  value,
  onChangeText,
  secureTextEntry,
  keyboardType,
  autoCapitalize = 'none',
  textContentType,
  showToggle,
  onToggle,
  editable = true,
}) {
  return (
    <View style={s.field}>
      <Text style={s.fieldLabel}>{label}</Text>
      <View style={s.fieldRow}>
        <TextInput
          style={s.fieldInput}
          value={value}
          onChangeText={onChangeText}
          placeholder={placeholder}
          placeholderTextColor={C.placeholder}
          secureTextEntry={secureTextEntry}
          keyboardType={keyboardType}
          autoCapitalize={autoCapitalize}
          autoCorrect={false}
          textContentType={textContentType}
          editable={editable}
        />
        {showToggle != null ? (
          <Pressable accessibilityRole="button" onPress={onToggle} style={s.eyeBtn}>
            <MaterialCommunityIcons
              name={showToggle ? 'eye-off' : 'eye'}
              size={20}
              color={C.eyeIcon}
            />
          </Pressable>
        ) : null}
      </View>
    </View>
  );
}

function splitName(fullName) {
  const parts = fullName.trim().split(/\s+/);
  if (parts.length === 1) {
    return {firstName: parts[0], lastName: ''};
  }
  const lastName = parts.pop();
  return {firstName: parts.join(' '), lastName};
}

// ─── Screen ───────────────────────────────────────────────────────────────────
export default function SignupScreen({navigation}) {
  const [name, setName]               = useState('');
  const [email, setEmail]             = useState('');
  const [address, setAddress]         = useState('');
  const [contact, setContact]         = useState('');
  const [password, setPassword]       = useState('');
  const [confirmPw, setConfirmPw]     = useState('');
  const [showPw, setShowPw]           = useState(false);
  const [showConfirm, setShowConfirm] = useState(false);
  const [submitting, setSubmitting]   = useState(false);
  const [error, setError]             = useState('');
  const [success, setSuccess]         = useState('');

  const canSubmit = useMemo(
    () =>
      name.trim().length > 0 &&
      email.trim().length > 0 &&
      address.trim().length > 0 &&
      contact.trim().length > 0 &&
      password.trim().length > 0 &&
      confirmPw.trim().length > 0,
    [name, email, address, contact, password, confirmPw],
  );

  const clearError = () => {
    if (error) {
      setError('');
    }
  };

  const handleRegister = async () => {
    if (submitting) {
      return;
    }
    if (!canSubmit) {
      setError('Please fill in all required fields.');
      return;
    }
    const contactDigits = contact.replace(/\D/g, '');
    if (contactDigits.length !== 11) {
      setError('Contact number must be exactly 11 digits.');
      return;
    }
    if (password !== confirmPw) {
      setError('Passwords do not match.');
      return;
    }
    const pwError = getPasswordValidationError(password);
    if (pwError) {
      setError(pwError);
      return;
    }
    setError('');
    const {firstName, lastName} = splitName(name);
    try {
      setSubmitting(true);
      await apiPost(
        '/register',
        {
          first_name: firstName,
          last_name: lastName,
          email: email.trim(),
          address: address.trim(),
          contact_number: contactDigits,
          password,
          password_confirmation: confirmPw,
        },
        false,
      );
      setSuccess('Account created! Redirecting to sign in...');
      setTimeout(() => navigation?.navigate?.('Login'), 900);
    } catch (err) {
      setError(
        err instanceof ApiError ? err.message : 'Registration failed. Please try again.',
      );
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <View style={s.screen}>

      <KeyboardAvoidingView
        style={s.kav}
        behavior={Platform.OS === 'ios' ? 'padding' : undefined}
        keyboardVerticalOffset={Platform.OS === 'ios' ? 10 : 0}>
        <ScrollView
          contentContainerStyle={s.scroll}
          keyboardDismissMode={Platform.OS === 'ios' ? 'interactive' : 'on-drag'}
          keyboardShouldPersistTaps="handled"
          showsVerticalScrollIndicator={false}>

          {/* ── Header (outside card) ── */}
          <View style={s.header}>
            <Text style={s.brand}>
              <Text style={s.brandSol}>Sol</Text>
              <Text style={s.brandMate}>Mate</Text>
            </Text>
            <Text style={s.pageTitle}>Register</Text>
            <Text style={s.pageSubtitle}>Customer Only</Text>
          </View>

          {/* ── Card ── */}
          <View style={s.card}>
            {error ? (
              <View style={s.errorBox}>
                <Text style={s.errorText}>{error}</Text>
              </View>
            ) : null}

            {success ? (
              <View style={s.successBox}>
                <Text style={s.successText}>{success}</Text>
              </View>
            ) : null}

            <Field
              label="Fullname"
              placeholder="Juan Dela Cruz"
              value={name}
              onChangeText={v => { clearError(); setName(v); }}
              autoCapitalize="words"
              textContentType="name"
            />

            <Field
              label="Phone Number"
              placeholder="+63 963 645 6543"
              value={contact}
              onChangeText={v => { clearError(); setContact(v.replace(/[^0-9+\s-]/g, '')); }}
              keyboardType="phone-pad"
              textContentType="telephoneNumber"
            />

            <Field
              label="Address"
              placeholder="Pasig City, Metro Manila"
              value={address}
              onChangeText={v => { clearError(); setAddress(v); }}
              autoCapitalize="words"
              textContentType="streetAddressLine1"
            />

            <Field
              label="Email"
              placeholder="name@email.com"
              value={email}
              onChangeText={v => { clearError(); setEmail(v); }}
              keyboardType="email-address"
              textContentType="emailAddress"
            />

            <Field
              label="Password"
              placeholder="••••••••••"
              value={password}
              onChangeText={v => { clearError(); setPassword(v); }}
              secureTextEntry={!showPw}
              textContentType="newPassword"
              showToggle={showPw}
              onToggle={() => setShowPw(p => !p)}
            />

            <Field
              label="Confirm Password"
              placeholder="••••••••••"
              value={confirmPw}
              onChangeText={v => { clearError(); setConfirmPw(v); }}
              secureTextEntry={!showConfirm}
              textContentType="newPassword"
              showToggle={showConfirm}
              onToggle={() => setShowConfirm(p => !p)}
            />

            {/* Create Account button */}
            <TouchableOpacity
              activeOpacity={0.9}
              disabled={!canSubmit || submitting}
              onPress={handleRegister}
              style={[s.btn, (!canSubmit || submitting) && s.btnOff]}>
              {submitting ? (
                <View style={s.btnRow}>
                  <ActivityIndicator size="small" color={C.buttonText} />
                  <Text style={s.btnText}>Creating...</Text>
                </View>
              ) : (
                <Text style={s.btnText}>Login</Text>
              )}
            </TouchableOpacity>
          </View>

          {/* ── Footer ── */}
          <TouchableOpacity
            activeOpacity={0.8}
            onPress={() => navigation?.navigate?.('Login')}
            style={s.footer}>
            <Text style={s.footerText}>
              {'Have an account? '}
              <Text style={s.footerLink}>Login Here</Text>
            </Text>
          </TouchableOpacity>

        </ScrollView>
      </KeyboardAvoidingView>
    </View>
  );
}

// ─── Styles ───────────────────────────────────────────────────────────────────
const s = StyleSheet.create({
  screen: {
    flex: 1,
    backgroundColor: C.bgMid,
  },
  gradTop: {
    top: 0,
    bottom: '55%',
    backgroundColor: C.bgTop,
    opacity: 0.6,
  },
  gradBot: {
    top: '55%',
    bottom: 0,
    backgroundColor: C.bgBot,
    opacity: 0.5,
  },
  kav: {
    flex: 1,
  },
  scroll: {
    flexGrow: 1,
    justifyContent: 'center',
    paddingHorizontal: 24,
    paddingVertical: 32,
  },

  // Header
  header: {
    marginBottom: 20,
    alignItems: 'center',
  },
  brand: {
    fontSize: 22,
    fontWeight: '800',
    marginBottom: 4,
  },
  brandSol: {
    color: C.solText,
    fontStyle: 'italic',
  },
  brandMate: {
    color: C.mateText,
    fontStyle: 'italic',
  },
  pageTitle: {
    fontSize: 32,
    fontWeight: '800',
    color: C.title,
    marginBottom: 2,
  },
  pageSubtitle: {
    fontSize: 14,
    color: C.subtitle,
    fontWeight: '500',
  },

  // Card
  card: {
    backgroundColor: C.card,
    borderRadius: 20,
    padding: 20,
    paddingBottom: 22,
    shadowColor: '#8898BE',
    shadowOpacity: 0.25,
    shadowRadius: 20,
    shadowOffset: {width: 0, height: 8},
    elevation: 8,
    marginBottom: 16,
  },

  // Error / success
  errorBox: {
    backgroundColor: C.dangerBg,
    borderWidth: 1,
    borderColor: C.dangerBdr,
    borderRadius: 12,
    paddingHorizontal: 12,
    paddingVertical: 9,
    marginBottom: 12,
  },
  errorText: {
    color: C.danger,
    fontSize: 13,
    lineHeight: 18,
  },
  successBox: {
    backgroundColor: C.successBg,
    borderWidth: 1,
    borderColor: C.successBdr,
    borderRadius: 12,
    paddingHorizontal: 12,
    paddingVertical: 9,
    marginBottom: 12,
  },
  successText: {
    color: C.success,
    fontSize: 13,
    lineHeight: 18,
  },

  // Field
  field: {
    marginBottom: 14,
  },
  fieldLabel: {
    color: C.label,
    fontSize: 12,
    fontWeight: '500',
    marginBottom: 5,
  },
  fieldRow: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: C.inputBg,
    borderWidth: 1,
    borderColor: C.inputBorder,
    borderRadius: 24,
    paddingHorizontal: 16,
    minHeight: 46,
  },
  fieldInput: {
    flex: 1,
    color: C.inputText,
    fontSize: 15,
    paddingVertical: 10,
  },
  eyeBtn: {
    padding: 4,
    marginLeft: 8,
  },

  // Button
  btn: {
    minHeight: 50,
    borderRadius: 999,
    backgroundColor: C.button,
    alignItems: 'center',
    justifyContent: 'center',
    marginTop: 4,
    shadowColor: C.button,
    shadowOpacity: 0.4,
    shadowRadius: 10,
    shadowOffset: {width: 0, height: 4},
    elevation: 4,
  },
  btnOff: {
    opacity: 0.6,
  },
  btnText: {
    color: C.buttonText,
    fontSize: 17,
    fontWeight: '800',
    fontStyle: 'italic',
  },
  btnRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
  },

  // Footer
  footer: {
    alignItems: 'center',
  },
  footerText: {
    color: C.footerText,
    fontSize: 13,
  },
  footerLink: {
    color: C.footerLink,
    fontWeight: '700',
  },
});
