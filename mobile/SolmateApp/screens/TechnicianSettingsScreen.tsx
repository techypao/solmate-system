import React, {useContext, useEffect, useState} from 'react';
import {
  Alert,
  SafeAreaView,
  ScrollView,
  StyleSheet,
  Text,
  View,
} from 'react-native';

import {AppButton, AppCard, AppInput} from '../components';
import {AuthContext} from '../src/context/AuthContext';
import {
  updateTechnicianAccount,
  updateTechnicianPassword,
} from '../src/services/technicianAccountApi';

function formatRole(role?: string | null) {
  if (!role) {
    return 'Technician';
  }

  return role.charAt(0).toUpperCase() + role.slice(1);
}

function InfoRow({
  label,
  value,
}: {
  label: string;
  value: string;
}) {
  return (
    <View style={styles.infoRow}>
      <Text style={styles.infoLabel}>{label}</Text>
      <Text style={styles.infoValue}>{value}</Text>
    </View>
  );
}

export default function TechnicianSettingsScreen() {
  const {logout, setUser, user} = useContext(AuthContext);
  const [name, setName] = useState(user?.name || '');
  const [email, setEmail] = useState(user?.email || '');
  const [currentPassword, setCurrentPassword] = useState('');
  const [newPassword, setNewPassword] = useState('');
  const [confirmNewPassword, setConfirmNewPassword] = useState('');
  const [profileSubmitting, setProfileSubmitting] = useState(false);
  const [passwordSubmitting, setPasswordSubmitting] = useState(false);

  useEffect(() => {
    setName(user?.name || '');
    setEmail(user?.email || '');
  }, [user?.email, user?.name]);

  const handleSaveProfile = async () => {
    const trimmedName = name.trim();
    const trimmedEmail = email.trim().toLowerCase();

    if (profileSubmitting) {
      return;
    }

    if (!trimmedName || !trimmedEmail) {
      Alert.alert('Incomplete form', 'Please provide both name and email.');
      return;
    }

    const emailPattern = /\S+@\S+\.\S+/;

    if (!emailPattern.test(trimmedEmail)) {
      Alert.alert('Invalid email', 'Please enter a valid email address.');
      return;
    }

    try {
      setProfileSubmitting(true);

      const response = await updateTechnicianAccount({
        name: trimmedName,
        email: trimmedEmail,
      });

      setUser((currentUser: typeof user) =>
        currentUser
          ? {
              ...currentUser,
              ...response.user,
            }
          : response.user,
      );

      Alert.alert('Success', response.message);
    } catch (error: any) {
      Alert.alert(
        'Update failed',
        error?.message || 'Could not update your account information.',
      );
    } finally {
      setProfileSubmitting(false);
    }
  };

  const handleChangePassword = async () => {
    if (passwordSubmitting) {
      return;
    }

    if (!currentPassword || !newPassword || !confirmNewPassword) {
      Alert.alert(
        'Incomplete form',
        'Please enter your current password and your new password twice.',
      );
      return;
    }

    if (newPassword.length < 8) {
      Alert.alert(
        'Weak password',
        'Your new password must be at least 8 characters long.',
      );
      return;
    }

    if (newPassword !== confirmNewPassword) {
      Alert.alert(
        'Passwords do not match',
        'Your new password confirmation does not match.',
      );
      return;
    }

    if (currentPassword === newPassword) {
      Alert.alert(
        'Choose a new password',
        'Your new password must be different from your current password.',
      );
      return;
    }

    try {
      setPasswordSubmitting(true);

      const response = await updateTechnicianPassword({
        current_password: currentPassword,
        new_password: newPassword,
        new_password_confirmation: confirmNewPassword,
      });

      setCurrentPassword('');
      setNewPassword('');
      setConfirmNewPassword('');

      Alert.alert('Success', response.message);
    } catch (error: any) {
      Alert.alert(
        'Password update failed',
        error?.message || 'Could not update your password.',
      );
    } finally {
      setPasswordSubmitting(false);
    }
  };

  return (
    <SafeAreaView style={styles.safeArea}>
      <ScrollView
        contentContainerStyle={styles.contentContainer}
        keyboardShouldPersistTaps="handled"
        showsVerticalScrollIndicator={false}>
        <View style={styles.heroCard}>
          <Text style={styles.eyebrow}>Technician settings</Text>
          <Text style={styles.heroTitle}>{user?.name || 'Technician'}</Text>
          <Text style={styles.heroSubtitle}>
            Review your technician account details and manage your login
            credentials in one place.
          </Text>
        </View>

        <AppCard style={styles.sectionCard}>
          <Text style={styles.sectionTitle}>Account overview</Text>
          <Text style={styles.sectionSubtitle}>
            Details below come from your current signed-in technician account.
          </Text>

          <View style={styles.infoCard}>
            <InfoRow label="Name" value={user?.name || 'Not available'} />
            <InfoRow label="Email" value={user?.email || 'Not available'} />
            <InfoRow label="Role" value={formatRole(user?.role)} />
            <InfoRow label="Status" value="Signed in" />
          </View>
        </AppCard>

        <AppCard style={styles.sectionCard}>
          <Text style={styles.sectionTitle}>Update account</Text>
          <Text style={styles.sectionSubtitle}>
            Edit your technician name and email here. Changes are saved
            directly to your account.
          </Text>

          <AppInput
            autoCapitalize="words"
            containerStyle={styles.inputSpacing}
            label="Full name"
            onChangeText={setName}
            placeholder="Enter your full name"
            value={name}
          />
          <AppInput
            autoCapitalize="none"
            containerStyle={styles.inputSpacing}
            keyboardType="email-address"
            label="Email address"
            onChangeText={setEmail}
            placeholder="Enter your email"
            value={email}
          />
          <AppButton
            style={styles.buttonSpacing}
            title={profileSubmitting ? 'Saving...' : 'Save account details'}
            onPress={handleSaveProfile}
          />
        </AppCard>

        <AppCard style={styles.sectionCard}>
          <Text style={styles.sectionTitle}>Change password</Text>
          <Text style={styles.sectionSubtitle}>
            For security, enter your current password before choosing a new one.
          </Text>

          <AppInput
            containerStyle={styles.inputSpacing}
            label="Current password"
            onChangeText={setCurrentPassword}
            placeholder="Enter current password"
            secureTextEntry={true}
            value={currentPassword}
          />
          <AppInput
            containerStyle={styles.inputSpacing}
            label="New password"
            onChangeText={setNewPassword}
            placeholder="Enter new password"
            secureTextEntry={true}
            value={newPassword}
          />
          <AppInput
            containerStyle={styles.inputSpacing}
            label="Confirm new password"
            onChangeText={setConfirmNewPassword}
            placeholder="Confirm new password"
            secureTextEntry={true}
            value={confirmNewPassword}
          />
          <AppButton
            style={styles.buttonSpacing}
            title={passwordSubmitting ? 'Updating...' : 'Update password'}
            onPress={handleChangePassword}
          />
        </AppCard>

        <AppCard style={styles.sectionCard}>
          <Text style={styles.sectionTitle}>Session</Text>
          <Text style={styles.sectionSubtitle}>
            Sign out when you are done using the technician mobile app.
          </Text>

          <AppButton title="Logout" variant="outline" onPress={logout} />
        </AppCard>
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safeArea: {
    backgroundColor: '#f5f7fb',
    flex: 1,
  },
  contentContainer: {
    padding: 20,
    paddingBottom: 28,
  },
  heroCard: {
    backgroundColor: '#e0f2fe',
    borderRadius: 28,
    marginBottom: 18,
    padding: 22,
  },
  eyebrow: {
    color: '#0369a1',
    fontSize: 13,
    fontWeight: '700',
    letterSpacing: 0.4,
    marginBottom: 8,
    textTransform: 'uppercase',
  },
  heroTitle: {
    color: '#0f172a',
    fontSize: 28,
    fontWeight: '800',
    lineHeight: 34,
    marginBottom: 10,
  },
  heroSubtitle: {
    color: '#334155',
    fontSize: 15,
    lineHeight: 22,
  },
  sectionCard: {
    marginBottom: 18,
  },
  sectionTitle: {
    color: '#0f172a',
    fontSize: 20,
    fontWeight: '700',
    marginBottom: 6,
  },
  sectionSubtitle: {
    color: '#64748b',
    fontSize: 14,
    lineHeight: 20,
    marginBottom: 16,
  },
  infoCard: {
    backgroundColor: '#f8fafc',
    borderColor: '#e2e8f0',
    borderRadius: 20,
    borderWidth: 1,
    paddingHorizontal: 16,
    paddingVertical: 6,
  },
  infoRow: {
    borderBottomColor: '#e2e8f0',
    borderBottomWidth: 1,
    paddingVertical: 14,
  },
  infoLabel: {
    color: '#64748b',
    fontSize: 12,
    fontWeight: '700',
    marginBottom: 4,
    textTransform: 'uppercase',
  },
  infoValue: {
    color: '#0f172a',
    fontSize: 16,
    fontWeight: '600',
  },
  inputSpacing: {
    marginBottom: 14,
  },
  buttonSpacing: {
    marginTop: 4,
  },
});
