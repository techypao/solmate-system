import React, {useContext, useState} from 'react';
import {
  KeyboardAvoidingView,
  Platform,
  Pressable,
  ScrollView,
  Text,
  TextInput,
  TouchableOpacity,
  View,
} from 'react-native';
import {AuthContext} from '../src/context/AuthContext';
import {ApiError, apiPost} from '../src/services/api';
import {authColors, authStyles} from './authStyles';

type LoginScreenProps = {
  navigation?: {
    navigate?: (screen: string) => void;
  };
};

type LoginResponse = {
  token: string;
};

export default function LoginScreen({navigation}: LoginScreenProps) {
  const {login} = useContext(AuthContext);

  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [errorMessage, setErrorMessage] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [rememberSession, setRememberSession] = useState(true);
  const [submitting, setSubmitting] = useState(false);

  const handleLogin = async () => {
    if (submitting) {
      return;
    }

    if (!email.trim() || !password.trim()) {
      setErrorMessage('Please enter both email and password.');
      return;
    }

    setErrorMessage('');

    const loginData = {
      email: email.trim(),
      password: password,
    };

    try {
      setSubmitting(true);
      const data = await apiPost<LoginResponse>('/login', loginData, false);

      console.log('Login success:', data);

      await login(data.token, {rememberSession});
    } catch (error) {
      console.log('Login error:', error);
      if (error instanceof ApiError) {
        setErrorMessage(error.message);
        return;
      }

      setErrorMessage('Login failed.');
    } finally {
      setSubmitting(false);
    }
  };

  const handleRegisterPress = () => {
    navigation?.navigate?.('Register');
  };

  return (
    <KeyboardAvoidingView
      style={{flex: 1, backgroundColor: authColors.screenBg}}
      behavior={Platform.OS === 'ios' ? 'padding' : undefined}>
      <ScrollView
        contentContainerStyle={authStyles.screenScroll}
        keyboardShouldPersistTaps="handled"
        showsVerticalScrollIndicator={false}>
        <View style={authStyles.brandRow}>
          <Text style={authStyles.brandSol}>Sol</Text>
          <Text style={authStyles.brandMate}>Mate</Text>
        </View>

        <Text style={authStyles.pageTitle}>Login</Text>

        <View style={authStyles.card}>
          {errorMessage ? (
            <Text style={authStyles.errorText}>{errorMessage}</Text>
          ) : null}

          <Text style={authStyles.label}>Email</Text>
          <TextInput
            autoCapitalize="none"
            keyboardType="email-address"
            onChangeText={value => {
              setEmail(value);
              if (errorMessage) {
                setErrorMessage('');
              }
            }}
            placeholderTextColor={authColors.placeholderText}
            style={authStyles.input}
            value={email}
          />

          <Text style={authStyles.label}>Password</Text>
          <View style={authStyles.passwordWrap}>
            <TextInput
              onChangeText={value => {
                setPassword(value);
                if (errorMessage) {
                  setErrorMessage('');
                }
              }}
              placeholderTextColor={authColors.placeholderText}
              secureTextEntry={!showPassword}
              style={[authStyles.input, authStyles.passwordInput]}
              value={password}
            />
            <Pressable
              accessibilityRole="button"
              onPress={() => setShowPassword(c => !c)}
              style={authStyles.eyeBtn}>
              <View style={authStyles.eyeShape}>
                <View style={authStyles.eyePupil} />
              </View>
              {!showPassword && <View style={authStyles.eyeSlash} />}
            </Pressable>
          </View>

          <Pressable
            accessibilityRole="checkbox"
            accessibilityState={{checked: rememberSession}}
            onPress={() => setRememberSession(c => !c)}
            style={authStyles.rememberRow}>
            <View
              style={[
                authStyles.checkbox,
                rememberSession ? authStyles.checkboxChecked : null,
              ]}>
              {rememberSession ? (
                <Text style={authStyles.checkboxMark}>{'✓'}</Text>
              ) : null}
            </View>
            <View style={authStyles.rememberTextWrap}>
              <Text style={authStyles.rememberLabel}>Remember me</Text>
              <Text style={authStyles.rememberHint}>
                Keep this account signed in on this device.
              </Text>
            </View>
          </Pressable>

          <TouchableOpacity
            activeOpacity={0.85}
            disabled={submitting}
            onPress={handleLogin}
            style={[
              authStyles.primaryBtn,
              submitting ? authStyles.primaryBtnDisabled : null,
            ]}>
            <Text style={authStyles.primaryBtnText}>
              {submitting ? 'Logging in...' : 'Login'}
            </Text>
          </TouchableOpacity>

          <TouchableOpacity
            activeOpacity={0.75}
            onPress={handleRegisterPress}
            style={authStyles.bottomLink}>
            <Text style={authStyles.bottomLinkText}>
              Don't have an account?{' '}
              <Text style={authStyles.bottomLinkBold}>Create Account</Text>
            </Text>
          </TouchableOpacity>
        </View>
      </ScrollView>
    </KeyboardAvoidingView>
  );
}
