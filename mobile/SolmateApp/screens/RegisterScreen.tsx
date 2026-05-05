import React, { useEffect, useRef, useState } from 'react';
import {
  KeyboardAvoidingView,
  Platform,
  Pressable,
  ScrollView,
  Text,
  TextInput,
  TouchableOpacity,
  useWindowDimensions,
  View,
} from 'react-native';
import { ApiError, apiPost } from '../src/services/api';
import {
  getPasswordValidationError,
  PASSWORD_REQUIREMENTS_TEXT,
} from '../src/utils/passwordValidation';
import { authColors, authStyles } from './authStyles';

type RegisterScreenProps = {
  navigation?: {
    navigate?: (screen: string) => void;
    replace?: (screen: string) => void;
  };
};

type RegisterResponse = {
  message?: string;
  token?: string;
};

const SUCCESS_REDIRECT_MESSAGE =
  'Account successfully created! Redirecting to login page...';

function sanitizeContactNumber(value: string) {
  return value.replace(/\D/g, '').slice(0, 11);
}

function sanitizeLandlineNumber(value: string) {
  return value.replace(/[^0-9()+\-\s]/g, '').slice(0, 30);
}

export default function RegisterScreen({ navigation }: RegisterScreenProps) {
  const { width, height } = useWindowDimensions();
  const redirectTimeoutRef = useRef<ReturnType<typeof setTimeout> | null>(null);
  const isLandscape = width > height;
  const isCompact = width < 390;

  const [firstName, setFirstName] = useState('');
  const [lastName, setLastName] = useState('');
  const [email, setEmail] = useState('');
  const [address, setAddress] = useState('');
  const [contactNumber, setContactNumber] = useState('');
  const [landlineNumber, setLandlineNumber] = useState('');
  const [password, setPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [errorMessage, setErrorMessage] = useState('');
  const [successMessage, setSuccessMessage] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [showConfirmPassword, setShowConfirmPassword] = useState(false);
  const [submitting, setSubmitting] = useState(false);

  useEffect(() => {
    return () => {
      if (redirectTimeoutRef.current) {
        clearTimeout(redirectTimeoutRef.current);
      }
    };
  }, []);

  useEffect(() => {
    if (!successMessage) {
      return;
    }

    redirectTimeoutRef.current = setTimeout(() => {
      if (navigation?.replace) {
        navigation.replace('Login');
        return;
      }

      navigation?.navigate?.('Login');
    }, 2000);

    return () => {
      if (redirectTimeoutRef.current) {
        clearTimeout(redirectTimeoutRef.current);
      }
    };
  }, [navigation, successMessage]);

  const clearMessages = () => {
    if (errorMessage) {
      setErrorMessage('');
    }

    if (successMessage) {
      setSuccessMessage('');
    }
  };

  const handleRegister = async () => {
    if (submitting) {
      return;
    }

    const trimmedFirstName = firstName.trim();
    const trimmedLastName = lastName.trim();
    const trimmedEmail = email.trim();
    const trimmedAddress = address.trim();
    const sanitizedContactNumber = sanitizeContactNumber(contactNumber);
    const sanitizedLandlineNumber = sanitizeLandlineNumber(landlineNumber).trim();

    if (
      !trimmedFirstName ||
      !trimmedLastName ||
      !trimmedEmail ||
      !trimmedAddress ||
      !sanitizedContactNumber ||
      !password.trim() ||
      !confirmPassword.trim()
    ) {
      setSuccessMessage('');
      setErrorMessage('Please fill in all required fields.');
      return;
    }

    if (!/^\d{11}$/.test(sanitizedContactNumber)) {
      setSuccessMessage('');
      setErrorMessage('Contact number must be exactly 11 digits.');
      return;
    }

    if (password !== confirmPassword) {
      setSuccessMessage('');
      setErrorMessage('Passwords do not match.');
      return;
    }

    const passwordValidationError = getPasswordValidationError(password);
    if (passwordValidationError) {
      setSuccessMessage('');
      setErrorMessage(passwordValidationError);
      return;
    }

    setErrorMessage('');
    setSuccessMessage('');

    const registerData = {
      first_name: trimmedFirstName,
      last_name: trimmedLastName,
      email: trimmedEmail,
      address: trimmedAddress,
      contact_number: sanitizedContactNumber,
      landline_number: sanitizedLandlineNumber,
      password,
      password_confirmation: confirmPassword,
    };

    try {
      setSubmitting(true);
      await apiPost<RegisterResponse>('/register', registerData, false);

      setSuccessMessage(SUCCESS_REDIRECT_MESSAGE);
    } catch (error) {
      console.log('Register error:', error);
      setSuccessMessage('');
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
    if (redirectTimeoutRef.current) {
      clearTimeout(redirectTimeoutRef.current);
    }

    navigation?.navigate?.('Login');
  };

  return (
    <KeyboardAvoidingView
      style={authStyles.screenContainer}
      behavior={Platform.OS === 'ios' ? 'padding' : undefined}
      keyboardVerticalOffset={Platform.OS === 'ios' ? 12 : 0}
    >
      <ScrollView
        contentContainerStyle={[
          authStyles.screenScroll,
          isLandscape ? authStyles.screenScrollLandscape : null,
          isCompact ? authStyles.screenScrollCompact : null,
        ]}
        keyboardDismissMode={Platform.OS === 'ios' ? 'interactive' : 'on-drag'}
        keyboardShouldPersistTaps="handled"
        showsVerticalScrollIndicator={false}
      >
        {successMessage ? (
          <View style={authStyles.successToast} accessibilityRole="alert">
            <Text style={authStyles.successToastBadge}>Success</Text>
            <Text style={authStyles.successToastTitle}>{successMessage}</Text>
            <Text style={authStyles.successToastCopy}>
              You can sign in with your new SolMate account in a moment.
            </Text>
          </View>
        ) : null}

        <View style={authStyles.brandRow}>
          <Text style={authStyles.brandSol}>Sol</Text>
          <Text style={authStyles.brandMate}>Mate</Text>
        </View>

        <Text style={authStyles.pageTitle}>Register</Text>

        <View
          style={[
            authStyles.card,
            isCompact ? authStyles.cardCompact : null,
            isLandscape ? authStyles.cardLandscape : null,
          ]}
        >
          {errorMessage ? (
            <Text style={authStyles.errorText}>{errorMessage}</Text>
          ) : null}

          <Text style={authStyles.label}>First Name</Text>
          <TextInput
            autoCapitalize="words"
            autoCorrect={false}
            editable={!submitting}
            onChangeText={value => {
              clearMessages();
              setFirstName(value);
            }}
            placeholder="Enter your first name"
            placeholderTextColor={authColors.placeholderText}
            style={authStyles.input}
            textContentType="givenName"
            value={firstName}
          />

          <Text style={authStyles.label}>Last Name</Text>
          <TextInput
            autoCapitalize="words"
            autoCorrect={false}
            editable={!submitting}
            onChangeText={value => {
              clearMessages();
              setLastName(value);
            }}
            placeholder="Enter your last name"
            placeholderTextColor={authColors.placeholderText}
            style={authStyles.input}
            textContentType="familyName"
            value={lastName}
          />

          <Text style={authStyles.label}>Email</Text>
          <TextInput
            autoCapitalize="none"
            autoCorrect={false}
            editable={!submitting}
            keyboardType="email-address"
            onChangeText={value => {
              clearMessages();
              setEmail(value);
            }}
            placeholder="you@example.com"
            placeholderTextColor={authColors.placeholderText}
            style={authStyles.input}
            textContentType="emailAddress"
            value={email}
          />

          <Text style={authStyles.label}>Address</Text>
          <TextInput
            autoCapitalize="sentences"
            editable={!submitting}
            onChangeText={value => {
              clearMessages();
              setAddress(value);
            }}
            placeholder="House number, street, barangay, city"
            placeholderTextColor={authColors.placeholderText}
            style={authStyles.input}
            value={address}
          />

          <Text style={authStyles.label}>Contact Number</Text>
          <TextInput
            editable={!submitting}
            keyboardType="phone-pad"
            maxLength={11}
            onChangeText={value => {
              clearMessages();
              setContactNumber(sanitizeContactNumber(value));
            }}
            placeholder="09XXXXXXXXX"
            placeholderTextColor={authColors.placeholderText}
            style={authStyles.input}
            textContentType="telephoneNumber"
            value={contactNumber}
          />
          <Text style={authStyles.label}>Landline Number (Optional)</Text>
          <TextInput
            autoCorrect={false}
            editable={!submitting}
            keyboardType="phone-pad"
            maxLength={30}
            onChangeText={value => {
              clearMessages();
              setLandlineNumber(sanitizeLandlineNumber(value));
            }}
            placeholder="e.g. (02) 8123-4567"
            placeholderTextColor={authColors.placeholderText}
            style={authStyles.input}
            value={landlineNumber}
          />
          <Text style={authStyles.helperText}>
            Optional. You may enter a home or office landline number.
          </Text>

          <Text style={authStyles.label}>Password</Text>
          <View style={authStyles.passwordWrap}>
            <TextInput
              autoCapitalize="none"
              autoCorrect={false}
              editable={!submitting}
              onChangeText={value => {
                clearMessages();
                setPassword(value);
              }}
              placeholder="Create a secure password"
              placeholderTextColor={authColors.placeholderText}
              secureTextEntry={!showPassword}
              style={[authStyles.input, authStyles.passwordInput]}
              textContentType="newPassword"
              value={password}
            />
            <Pressable
              accessibilityRole="button"
              disabled={submitting}
              onPress={() => setShowPassword(c => !c)}
              style={authStyles.eyeBtn}
            >
              <View style={authStyles.eyeShape}>
                <View style={authStyles.eyePupil} />
              </View>
              {!showPassword && <View style={authStyles.eyeSlash} />}
            </Pressable>
          </View>
          <Text style={authStyles.helperText}>
            {PASSWORD_REQUIREMENTS_TEXT}
          </Text>

          <Text style={authStyles.label}>Confirm Password</Text>
          <View style={authStyles.passwordWrap}>
            <TextInput
              autoCapitalize="none"
              autoCorrect={false}
              editable={!submitting}
              onChangeText={value => {
                clearMessages();
                setConfirmPassword(value);
              }}
              placeholder="Re-enter your password"
              placeholderTextColor={authColors.placeholderText}
              secureTextEntry={!showConfirmPassword}
              style={[authStyles.input, authStyles.passwordInput]}
              textContentType="password"
              value={confirmPassword}
            />
            <Pressable
              accessibilityRole="button"
              disabled={submitting}
              onPress={() => setShowConfirmPassword(c => !c)}
              style={authStyles.eyeBtn}
            >
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
            ]}
          >
            <Text style={authStyles.primaryBtnText}>
              {submitting ? 'Registering...' : 'Register'}
            </Text>
          </TouchableOpacity>

          <TouchableOpacity
            activeOpacity={0.75}
            onPress={handleLoginPress}
            style={authStyles.bottomLink}
          >
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
