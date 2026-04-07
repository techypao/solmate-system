import React, {useContext, useState} from 'react';
import {
  Alert,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  TouchableOpacity,
  View,
} from 'react-native';
import {AuthContext} from '../src/context/AuthContext';

type RegisterScreenProps = {
  navigation?: {
    navigate?: (screen: string) => void;
  };
};

export default function RegisterScreen({navigation}: RegisterScreenProps) {
  const {login} = useContext(AuthContext);

  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [errorMessage, setErrorMessage] = useState('');

  const handleRegister = async () => {
    if (
      !name.trim() ||
      !email.trim() ||
      !password.trim() ||
      !confirmPassword.trim()
    ) {
      setErrorMessage('Please fill in all fields.');
      return;
    }

    if (password !== confirmPassword) {
      setErrorMessage('Passwords do not match.');
      return;
    }

    setErrorMessage('');

    const registerData = {
      name: name.trim(),
      email: email.trim(),
      password: password,
      password_confirmation: confirmPassword,
    };

    try {
      const response = await fetch('http://10.0.2.2:8000/api/register', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
        },
        body: JSON.stringify(registerData),
      });

      const data = await response.json();

      if (!response.ok) {
        if (data.errors) {
          const firstError = Object.values(data.errors).flat()[0] as string;
          setErrorMessage(firstError || 'Registration failed.');
        } else {
          setErrorMessage(data.message || 'Registration failed.');
        }
        return;
      }

      console.log('Register success:', data);

      await login(data.token);

      Alert.alert('Registration successful');
    } catch (error) {
      console.log('Register error:', error);
      setErrorMessage('Could not connect to server.');
    }
  };

  const handleLoginPress = () => {
    navigation?.navigate?.('Login');
  };

  return (
    <ScrollView
      contentContainerStyle={styles.scrollContent}
      keyboardShouldPersistTaps="handled"
      showsVerticalScrollIndicator={false}>
      <View style={styles.card}>
        <Text style={styles.title}>Create Account</Text>

        {errorMessage ? (
          <Text style={styles.errorText}>{errorMessage}</Text>
        ) : null}

        <TextInput
          onChangeText={setName}
          placeholder="Full Name"
          placeholderTextColor="#94a3b8"
          style={styles.input}
          value={name}
        />

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

        <TextInput
          onChangeText={setConfirmPassword}
          placeholder="Confirm Password"
          placeholderTextColor="#94a3b8"
          secureTextEntry={true}
          style={styles.input}
          value={confirmPassword}
        />

        <TouchableOpacity
          activeOpacity={0.85}
          onPress={handleRegister}
          style={styles.registerButton}>
          <Text style={styles.registerButtonText}>Register</Text>
        </TouchableOpacity>

        <TouchableOpacity
          activeOpacity={0.75}
          onPress={handleLoginPress}
          style={styles.loginButton}>
          <Text style={styles.loginText}>Already have an account? Login</Text>
        </TouchableOpacity>
      </View>
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  scrollContent: {
    flexGrow: 1,
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
  registerButton: {
    marginTop: 8,
    height: 54,
    borderRadius: 16,
    backgroundColor: '#2563eb',
    justifyContent: 'center',
    alignItems: 'center',
  },
  registerButtonText: {
    color: '#ffffff',
    fontSize: 16,
    fontWeight: '600',
  },
  loginButton: {
    marginTop: 18,
    alignItems: 'center',
    justifyContent: 'center',
  },
  loginText: {
    color: '#475569',
    fontSize: 14,
    fontWeight: '500',
    textAlign: 'center',
  },
});