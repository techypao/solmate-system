import React, {useContext, useEffect, useMemo, useState} from 'react';
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
import {AuthContext} from '../src/context/AuthContext';
import {ApiError, apiPost} from '../src/services/api';

// ─── Color tokens (matched from screenshot) ───────────────────────────────────
const C = {
  bgTop:       '#C8D8F0',
  bgMid:       '#C8D8F0',
  bgBot:       '#C8D8F0',
  card:        '#FFFFFF',
  solText:     '#1A2440',   // "Sol" — dark navy
  mateText:    '#F5C000',   // "Mate" — amber/yellow
  title:       '#1A2440',   // "Login" / "Register"
  subtitle:    '#7A88A8',   // "Customer Access" / "Customer Only"
  label:       '#8A96B0',   // input labels
  placeholder: '#AABCC8',
  inputText:   '#1A2440',
  inputBg:     '#FFFFFF',
  inputBorder: '#D8E2EE',
  eyeIcon:     '#6B7A99',
  button:      '#F5C000',   // yellow CTA
  buttonText:  '#1A2440',   // dark navy on yellow
  footerText:  '#8A96B0',
  footerLink:  '#1A2440',
  forgot:      '#8A96B0',
  danger:      '#D83B3B',
  dangerBg:    '#FFF2F2',
  dangerBdr:   '#F0B4B4',
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

// ─── Screen ───────────────────────────────────────────────────────────────────
export default function LoginScreen({navigation}) {
  const {login, authErrorMessage, clearAuthError} = useContext(AuthContext);

  const [email, setEmail]              = useState('');
  const [password, setPassword]        = useState('');
  const [rememberSession, setRemember] = useState(true);
  const [showPw, setShowPw]            = useState(false);
  const [submitting, setSubmitting]    = useState(false);
  const [error, setError]              = useState('');

  useEffect(() => {
    if (authErrorMessage) {
      setError(authErrorMessage);
    }
  }, [authErrorMessage]);

  const canSubmit = useMemo(
    () => email.trim().length > 0 && password.trim().length > 0,
    [email, password],
  );

  const clearError = () => {
    clearAuthError();
    if (error) {
      setError('');
    }
  };

  const isVerificationError =
    error === 'Please verify your email before logging in.';

  const handleLogin = async () => {
    if (submitting) {
      return;
    }
    if (!canSubmit) {
      setError('Please enter your email and password.');
      return;
    }
    clearError();
    try {
      setSubmitting(true);
      const res = await apiPost('/login', {email: email.trim(), password}, false);
      await login(res.token, {rememberSession});
    } catch (err) {
      setError(
        err instanceof ApiError ? err.message : 'Unable to sign in. Please try again.',
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
            <Text style={s.pageTitle}>Login</Text>
            <Text style={s.pageSubtitle}>Customer Access</Text>
          </View>

          {/* ── Card ── */}
          <View style={s.card}>
            {error ? (
              <View style={s.errorBox}>
                <View style={s.errorIcon}>
                  <MaterialCommunityIcons
                    name="email-alert-outline"
                    size={20}
                    color={C.danger}
                  />
                </View>
                <View style={s.errorCopy}>
                  <Text style={s.errorTitle}>
                    {isVerificationError ? 'Email not verified' : 'Login failed'}
                  </Text>
                  <Text style={s.errorText}>{error}</Text>
                </View>
              </View>
            ) : null}

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
              textContentType="password"
              showToggle={showPw}
              onToggle={() => setShowPw(p => !p)}
            />

            <TouchableOpacity activeOpacity={0.7} style={s.forgotWrap}>
              <Text style={s.forgotText}>Forgot Password?</Text>
            </TouchableOpacity>

            {/* Login button */}
            <TouchableOpacity
              activeOpacity={0.9}
              disabled={!canSubmit || submitting}
              onPress={handleLogin}
              style={[s.btn, (!canSubmit || submitting) && s.btnOff]}>
              {submitting ? (
                <View style={s.btnRow}>
                  <ActivityIndicator size="small" color={C.buttonText} />
                  <Text style={s.btnText}>Signing In...</Text>
                </View>
              ) : (
                <Text style={s.btnText}>Login</Text>
              )}
            </TouchableOpacity>
          </View>

          {/* ── Footer ── */}
          <TouchableOpacity
            activeOpacity={0.8}
            onPress={() => navigation?.navigate?.('Signup')}
            style={s.footer}>
            <Text style={s.footerText}>
              {'Dont have an account? '}
              <Text style={s.footerLink}>Create Account</Text>
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

  // Error
  errorBox: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    backgroundColor: '#FFF7F7',
    borderWidth: 1,
    borderColor: C.dangerBdr,
    borderRadius: 16,
    paddingHorizontal: 14,
    paddingVertical: 12,
    marginBottom: 16,
  },
  errorIcon: {
    width: 34,
    height: 34,
    borderRadius: 17,
    backgroundColor: '#FFE6E6',
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: 10,
  },
  errorCopy: {
    flex: 1,
    paddingTop: 1,
  },
  errorTitle: {
    color: C.danger,
    fontSize: 14,
    fontWeight: '800',
    marginBottom: 2,
  },
  errorText: {
    color: '#8F2D2D',
    fontSize: 12,
    lineHeight: 17,
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

  // Forgot
  forgotWrap: {
    alignItems: 'flex-end',
    marginTop: -4,
    marginBottom: 14,
  },
  forgotText: {
    color: C.forgot,
    fontSize: 12,
  },

  // Button
  btn: {
    minHeight: 50,
    borderRadius: 999,
    backgroundColor: C.button,
    alignItems: 'center',
    justifyContent: 'center',
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
