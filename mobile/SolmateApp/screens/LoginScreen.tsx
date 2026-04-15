import React, {useContext, useState} from 'react';
import {
  Pressable,
  StyleSheet,
  Text,
  TextInput,
  TouchableOpacity,
  View,
} from 'react-native';
import {AuthContext} from '../src/context/AuthContext';
import {ApiError, apiPost} from '../src/services/api';

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
    <View style={styles.container}>
      <View style={styles.card}>
        <Text style={styles.title}>Login</Text>

        {errorMessage ? (
          <Text style={styles.errorText}>{errorMessage}</Text>
        ) : null}

        <TextInput
          autoCapitalize="none"
          keyboardType="email-address"
          onChangeText={value => {
            setEmail(value);
            if (errorMessage) {
              setErrorMessage('');
            }
          }}
          placeholder="Email"
          placeholderTextColor="#94a3b8"
          style={styles.input}
          value={email}
        />

        <View style={styles.passwordWrap}>
          <TextInput
            onChangeText={value => {
              setPassword(value);
              if (errorMessage) {
                setErrorMessage('');
              }
            }}
            placeholder="Password"
            placeholderTextColor="#94a3b8"
            secureTextEntry={!showPassword}
            style={[styles.input, styles.passwordInput]}
            value={password}
          />

          <Pressable
            accessibilityRole="button"
            onPress={() => setShowPassword(current => !current)}
            style={styles.passwordToggle}>
            <Text style={styles.passwordToggleText}>
              {showPassword ? 'Hide' : 'Show'}
            </Text>
          </Pressable>
        </View>

        <Pressable
          accessibilityRole="checkbox"
          accessibilityState={{checked: rememberSession}}
          onPress={() => setRememberSession(current => !current)}
          style={styles.rememberRow}>
          <View
            style={[
              styles.checkbox,
              rememberSession ? styles.checkboxChecked : null,
            ]}>
            {rememberSession ? <Text style={styles.checkboxMark}>✓</Text> : null}
          </View>
          <View style={styles.rememberTextWrap}>
            <Text style={styles.rememberLabel}>Remember me</Text>
            <Text style={styles.rememberHint}>
              Keep this account signed in on this device.
            </Text>
          </View>
        </Pressable>

        <TouchableOpacity
          activeOpacity={0.85}
          disabled={submitting}
          onPress={handleLogin}
          style={[styles.loginButton, submitting ? styles.loginButtonDisabled : null]}>
          <Text style={styles.loginButtonText}>
            {submitting ? 'Logging in...' : 'Login'}
          </Text>
        </TouchableOpacity>

        <TouchableOpacity
          activeOpacity={0.75}
          onPress={handleRegisterPress}
          style={styles.registerButton}>
          <Text style={styles.registerText}>Don't have an account? Register</Text>
        </TouchableOpacity>
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    justifyContent: 'center',
    paddingHorizontal: 24,
    paddingVertical: 32,
    backgroundColor: '#f8fafc',
  },
  card: {
    width: '100%',
    maxWidth: 380,
    alignSelf: 'center',
    padding: 24,
    borderRadius: 24,
    backgroundColor: '#ffffff',
  },
  title: {
    fontSize: 30,
    fontWeight: '700',
    color: '#0f172a',
    textAlign: 'center',
    marginBottom: 24,
  },
  errorText: {
    color: '#dc2626',
    fontSize: 14,
    marginBottom: 16,
    textAlign: 'center',
  },
  input: {
    height: 54,
    borderRadius: 16,
    backgroundColor: '#f1f5f9',
    paddingHorizontal: 16,
    fontSize: 16,
    color: '#0f172a',
    marginBottom: 14,
  },
  passwordWrap: {
    position: 'relative',
  },
  passwordInput: {
    paddingRight: 72,
  },
  passwordToggle: {
    position: 'absolute',
    right: 16,
    top: 17,
  },
  passwordToggleText: {
    color: '#2563eb',
    fontSize: 14,
    fontWeight: '700',
  },
  rememberRow: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 8,
  },
  checkbox: {
    width: 22,
    height: 22,
    borderRadius: 6,
    borderWidth: 1,
    borderColor: '#cbd5e1',
    backgroundColor: '#ffffff',
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: 12,
  },
  checkboxChecked: {
    backgroundColor: '#2563eb',
    borderColor: '#2563eb',
  },
  checkboxMark: {
    color: '#ffffff',
    fontSize: 13,
    fontWeight: '700',
  },
  rememberTextWrap: {
    flex: 1,
  },
  rememberLabel: {
    color: '#0f172a',
    fontSize: 14,
    fontWeight: '600',
  },
  rememberHint: {
    color: '#64748b',
    fontSize: 12,
    marginTop: 2,
  },
  loginButton: {
    marginTop: 8,
    height: 54,
    borderRadius: 16,
    backgroundColor: '#2563eb',
    justifyContent: 'center',
    alignItems: 'center',
  },
  loginButtonDisabled: {
    opacity: 0.7,
  },
  loginButtonText: {
    color: '#ffffff',
    fontSize: 16,
    fontWeight: '600',
  },
  registerButton: {
    marginTop: 18,
    alignItems: 'center',
    justifyContent: 'center',
  },
  registerText: {
    color: '#475569',
    fontSize: 14,
    fontWeight: '500',
    textAlign: 'center',
  },
});
