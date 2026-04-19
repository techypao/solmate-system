import React, {useContext, useState} from 'react';
import {
  Alert,
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
  const [showPassword, setShowPassword] = useState(false);
  const [showConfirmPassword, setShowConfirmPassword] = useState(false);
  const [submitting, setSubmitting] = useState(false);

  const handleRegister = async () => {
    if (submitting) {
      return;
    }

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
      setSubmitting(true);
      const data = await apiPost<{token: string}>(
        '/register',
        registerData,
        false,
      );

      console.log('Register success:', data);

      await login(data.token);

      Alert.alert('Registration successful');
    } catch (error) {
      console.log('Register error:', error);
      if (error instanceof ApiError) {
        setErrorMessage(error.message);
        return;
      }

      setErrorMessage('Registration failed.');
    } finally {
      setSubmitting(false);
    }
  };

  const handleLoginPress = () => {
    navigation?.navigate?.('Login');
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

        <Text style={authStyles.pageTitle}>Register</Text>

        <View style={authStyles.card}>
          {errorMessage ? (
            <Text style={authStyles.errorText}>{errorMessage}</Text>
          ) : null}

          <Text style={authStyles.label}>Fullname</Text>
          <TextInput
            onChangeText={value => {
              setName(value);
              if (errorMessage) {
                setErrorMessage('');
              }
            }}
            placeholderTextColor={authColors.placeholderText}
            style={authStyles.input}
            value={name}
          />

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

          <Text style={authStyles.label}>Confirm Password</Text>
          <View style={authStyles.passwordWrap}>
            <TextInput
              onChangeText={value => {
                setConfirmPassword(value);
                if (errorMessage) {
                  setErrorMessage('');
                }
              }}
              placeholderTextColor={authColors.placeholderText}
              secureTextEntry={!showConfirmPassword}
              style={[authStyles.input, authStyles.passwordInput]}
              value={confirmPassword}
            />
            <Pressable
              accessibilityRole="button"
              onPress={() => setShowConfirmPassword(c => !c)}
              style={authStyles.eyeBtn}>
              <View style={authStyles.eyeShape}>
                <View style={authStyles.eyePupil} />
              </View>
              {!showConfirmPassword && <View style={authStyles.eyeSlash} />}
            </Pressable>
          </View>

          <TouchableOpacity
            activeOpacity={0.85}
            disabled={submitting}
            onPress={handleRegister}
            style={[
              authStyles.primaryBtn,
              submitting ? authStyles.primaryBtnDisabled : null,
            ]}>
            <Text style={authStyles.primaryBtnText}>
              {submitting ? 'Registering...' : 'Register'}
            </Text>
          </TouchableOpacity>

          <TouchableOpacity
            activeOpacity={0.75}
            onPress={handleLoginPress}
            style={authStyles.bottomLink}>
            <Text style={authStyles.bottomLinkText}>
              Have an account?{' '}
              <Text style={authStyles.bottomLinkBold}>Login Here</Text>
            </Text>
          </TouchableOpacity>
        </View>
      </ScrollView>
    </KeyboardAvoidingView>
  );
}
