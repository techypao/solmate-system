import React, {useState} from 'react';
import {StyleSheet, Text, TextInput, TouchableOpacity, View} from 'react-native';

type LoginScreenProps = {
  navigation?: {
    navigate?: (screen: string) => void;
  };
};

export default function LoginScreen({navigation}: LoginScreenProps) {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [errorMessage, setErrorMessage] = useState('');

  const handleLogin = () => {
    if (!email.trim() || !password.trim()) {
      setErrorMessage('Please enter both email and password.');
      return;
    }

    setErrorMessage('');

    const loginData = {
      email: email.trim(),
      password: password,
    };

    console.log('Login payload:', loginData);
  };

  const handleRegisterPress = () => {
    navigation?.navigate?.('Register');
  };

  return (
    <View style={styles.container}>
      <View style={styles.card}>
        <Text style={styles.title}>Login</Text>

        {errorMessage ? <Text style={styles.errorText}>{errorMessage}</Text> : null}

        <TextInput
          autoCapitalize="none"
          keyboardType="email-address"
          onChangeText={setEmail}
          placeholder="Email"
          placeholderTextColor="#94a3b8"
          style={styles.input}
          value={email}
        />

        <TextInput
          onChangeText={setPassword}
          placeholder="Password"
          placeholderTextColor="#94a3b8"
          secureTextEntry={true}
          style={styles.input}
          value={password}
        />

        <TouchableOpacity activeOpacity={0.85} onPress={handleLogin} style={styles.loginButton}>
          <Text style={styles.loginButtonText}>Login</Text>
        </TouchableOpacity>

        <TouchableOpacity activeOpacity={0.75} onPress={handleRegisterPress} style={styles.registerButton}>
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
  loginButton: {
    marginTop: 8,
    height: 54,
    borderRadius: 16,
    backgroundColor: '#2563eb',
    justifyContent: 'center',
    alignItems: 'center',
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
