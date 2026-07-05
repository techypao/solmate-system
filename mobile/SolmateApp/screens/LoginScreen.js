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
import {AuthContext} from '../src/context/AuthContext';
import {ApiError, apiPost} from '../src/services/api';
import {solmateColors} from '../src/theme/colors';

function Field({
  label,
  value,
  onChangeText,
  placeholder,
  keyboardType,
  autoCapitalize = 'none',
  textContentType,
  secureTextEntry,
}) {
  return (
    <View style={s.field}>
      <Text style={s.label}>{label}</Text>
      <TextInput
        autoCapitalize={autoCapitalize}
        autoCorrect={false}
        keyboardType={keyboardType}
        onChangeText={onChangeText}
        placeholder={placeholder}
        placeholderTextColor={solmateColors.mutedSoft}
        secureTextEntry={secureTextEntry}
        style={s.input}
        textContentType={textContentType}
        value={value}
      />
    </View>
  );
}

function PasswordField({value, onChangeText, visible, onToggleVisible}) {
  return (
    <View style={s.field}>
      <Text style={s.label}>Password</Text>
      <View style={s.passwordRow}>
        <TextInput
          autoCapitalize="none"
          autoCorrect={false}
          onChangeText={onChangeText}
          placeholder="Enter your password"
          placeholderTextColor={solmateColors.mutedSoft}
          secureTextEntry={!visible}
          style={[s.input, s.passwordInput]}
          textContentType="password"
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

export default function LoginScreen({navigation}) {
  const {login, authErrorMessage, clearAuthError} = useContext(AuthContext);

  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [rememberSession, setRememberSession] = useState(true);
  const [showPassword, setShowPassword] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  const [error, setError] = useState('');

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
      const response = await apiPost(
        '/login',
        {
          email: email.trim(),
          password,
        },
        false,
      );

      await login(response.token, {rememberSession});
    } catch (err) {
      setError(
        err instanceof ApiError
          ? err.message
          : 'Unable to sign in. Please try again.',
      );
    } finally {
      setSubmitting(false);
    }
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

        <Text style={s.pageTitle}>Login</Text>

        <View style={s.card}>
          {error ? (
            <View style={s.errorBox}>
              <Text style={s.errorTitle}>
                {isVerificationError ? 'Email not verified' : 'Login failed'}
              </Text>
              <Text style={s.errorText}>{error}</Text>
            </View>
          ) : null}

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

          <PasswordField
            onChangeText={value => {
              clearError();
              setPassword(value);
            }}
            onToggleVisible={() => setShowPassword(current => !current)}
            value={password}
            visible={showPassword}
          />

          <Pressable
            accessibilityRole="checkbox"
            accessibilityState={{checked: rememberSession}}
            onPress={() => setRememberSession(current => !current)}
            style={s.rememberRow}>
            <View style={[s.checkbox, rememberSession && s.checkboxChecked]}>
              {rememberSession ? <Text style={s.checkboxMark}>✓</Text> : null}
            </View>
            <Text style={s.rememberLabel}>Remember me</Text>
          </Pressable>

          <TouchableOpacity
            activeOpacity={0.88}
            disabled={submitting}
            onPress={handleLogin}
            style={[s.primaryBtn, submitting && s.btnDisabled]}>
            {submitting ? (
              <ActivityIndicator color={solmateColors.text} />
            ) : (
              <Text style={s.primaryBtnText}>Login</Text>
            )}
          </TouchableOpacity>

          <TouchableOpacity
            activeOpacity={0.75}
            onPress={() => navigation?.navigate?.('Register')}
            style={s.bottomLink}>
            <Text style={s.bottomLinkText}>
              Don't have an account?{' '}
              <Text style={s.bottomLinkBold}>Create Account</Text>
            </Text>
          </TouchableOpacity>
        </View>
      </ScrollView>
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
  rememberRow: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 16,
    marginTop: -2,
  },
  checkbox: {
    width: 22,
    height: 22,
    borderRadius: 6,
    borderWidth: 1.5,
    borderColor: solmateColors.borderStrong,
    backgroundColor: solmateColors.white,
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: 10,
  },
  checkboxChecked: {
    backgroundColor: solmateColors.primary,
    borderColor: solmateColors.primary,
  },
  checkboxMark: {
    color: solmateColors.text,
    fontSize: 13,
    fontWeight: '800',
  },
  rememberLabel: {
    color: solmateColors.text,
    fontSize: 14,
    fontWeight: '700',
  },
  primaryBtn: {
    height: 52,
    borderRadius: 26,
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: solmateColors.primary,
  },
  primaryBtnText: {
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
  errorTitle: {
    color: solmateColors.danger,
    fontSize: 14,
    fontWeight: '800',
    marginBottom: 2,
  },
  errorText: {
    color: '#8F2D2D',
    fontSize: 13,
    lineHeight: 18,
  },
});
