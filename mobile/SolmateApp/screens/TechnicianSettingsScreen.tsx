import React, {useCallback, useContext, useState} from 'react';
import {
  Alert,
  Image,
  Pressable,
  SafeAreaView,
  ScrollView,
  StyleSheet,
  Text,
  View,
} from 'react-native';
import {Asset, launchImageLibrary} from 'react-native-image-picker';
import Icon from 'react-native-vector-icons/MaterialCommunityIcons';
import TechnicianBottomNav from '../src/components/TechnicianBottomNav';

import {AppInput} from '../components';
import {AuthContext} from '../src/context/AuthContext';
import {
  updateTechnicianAccount,
  updateTechnicianPassword,
} from '../src/services/technicianAccountApi';
import {ApiError} from '../src/services/api';
import {uploadProfilePicture} from '../src/services/profilePictureApi';
import {getProfilePictureUrl} from '../src/utils/profilePicture';
import {
  getPasswordValidationError,
  PASSWORD_REQUIREMENTS_TEXT,
} from '../src/utils/passwordValidation';

/* ── design tokens ── */
const NAVY = '#1A2B55';
const GOLD = '#F5C000';
const BG = '#C8D8F0';
const CARD    = '#ffffff';
const MUTED = '#6B7A99';
const DIVIDER = '#D4E0F2';
const RED     = '#dc2626';
const ICON_COLOR = '#1d2f6d';

type LocalProfileImageAsset = {
  uri: string;
  type?: string | null;
  name?: string | null;
};

function normalizePickedProfileAsset(
  assets?: Asset[],
): LocalProfileImageAsset | null {
  const firstAsset = (assets || []).find(asset => !!asset.uri);

  if (!firstAsset?.uri) {
    return null;
  }

  return {
    uri: firstAsset.uri,
    type: firstAsset.type || 'image/jpeg',
    name: firstAsset.fileName || null,
  };
}

function isForceLogoutEmailChanged(error: unknown) {
  return (
    error instanceof ApiError &&
    error.status === 403 &&
    (error.data as any)?.message === 'FORCE_LOGOUT_EMAIL_CHANGED'
  );
}

/* ── main screen ── */
export default function TechnicianSettingsScreen({navigation}: any) {
  const {logout, setUser, user} = useContext(AuthContext);
  const displayName =
    [user?.first_name, user?.last_name]
      .map(value => String(value || '').trim())
      .filter(Boolean)
      .join(' ') ||
    user?.name ||
    'Technician';

  /* form state */
  const [email, setEmail]                       = useState(user?.email || '');
  const [currentPassword, setCurrentPassword]   = useState('');
  const [newPassword, setNewPassword]           = useState('');
  const [confirmNewPassword, setConfirmNewPassword] = useState('');
  const [profileSubmitting, setProfileSubmitting]   = useState(false);
  const [pictureSubmitting, setPictureSubmitting]   = useState(false);
  const [passwordSubmitting, setPasswordSubmitting] = useState(false);

  /* expanded panel state */
  const [expandedPanel, setExpandedPanel] = useState<null | 'info' | 'password'>(null);

  const togglePanel = useCallback((panel: 'info' | 'password') => {
    setExpandedPanel(prev => (prev === panel ? null : panel));
  }, []);

  /* format technician ID from user.id */
  const technicianId = user?.id
    ? `T-${String(user.id).padStart(3, '0')}`
    : 'T-—';
  const profilePictureUrl = getProfilePictureUrl(user?.profile_picture);

  const submitProfileUpdate = async (trimmedEmail: string) => {
    try {
      setProfileSubmitting(true);
      const response = await updateTechnicianAccount({email: trimmedEmail});
      setUser((currentUser: typeof user) =>
        currentUser ? {...currentUser, ...response.user} : response.user,
      );
      Alert.alert('Success', response.message);
      setExpandedPanel(null);
    } catch (error: any) {
      if (isForceLogoutEmailChanged(error)) {
        await logout();
        Alert.alert('Email verification required', error.message, [
          {
            text: 'OK',
            onPress: () => {
              navigation.reset({
                index: 0,
                routes: [{name: 'Login'}],
              });
            },
          },
        ]);
        return;
      }

      Alert.alert('Update failed', error?.message || 'Could not update your account information.');
    } finally {
      setProfileSubmitting(false);
    }
  };

  const handleSaveProfile = async () => {
    const trimmedEmail = email.trim().toLowerCase();

    if (profileSubmitting) {return;}
    if (!trimmedEmail) {
      Alert.alert('Incomplete form', 'Please provide your email address.');
      return;
    }
    const emailPattern = /\S+@\S+\.\S+/;
    if (!emailPattern.test(trimmedEmail)) {
      Alert.alert('Invalid email', 'Please enter a valid email address.');
      return;
    }

    const currentEmail = String(user?.email || '').trim().toLowerCase();

    if (trimmedEmail !== currentEmail) {
      Alert.alert(
        'Confirm Email Change',
        'Changing your email will log you out and require verification. Do you want to continue?',
        [
          {text: 'Cancel', style: 'cancel'},
          {
            text: 'Continue',
            style: 'destructive',
            onPress: () => {
              void submitProfileUpdate(trimmedEmail);
            },
          },
        ],
      );
      return;
    }

    await submitProfileUpdate(trimmedEmail);
  };

  const handleChangePassword = async () => {
    if (passwordSubmitting) {return;}
    if (!currentPassword || !newPassword || !confirmNewPassword) {
      Alert.alert('Incomplete form', 'Please fill in all password fields.');
      return;
    }
    const passwordValidationError = getPasswordValidationError(newPassword);
    if (passwordValidationError) {
      Alert.alert('Weak password', passwordValidationError);
      return;
    }
    if (newPassword !== confirmNewPassword) {
      Alert.alert('Passwords do not match', 'Your new password confirmation does not match.');
      return;
    }
    if (currentPassword === newPassword) {
      Alert.alert('Choose a new password', 'Your new password must be different from your current password.');
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
      setExpandedPanel(null);
    } catch (error: any) {
      Alert.alert('Password update failed', error?.message || 'Could not update your password.');
    } finally {
      setPasswordSubmitting(false);
    }
  };

  const handleUploadProfilePicture = async () => {
    if (pictureSubmitting) {
      return;
    }

    const result = await launchImageLibrary({
      mediaType: 'photo',
      selectionLimit: 1,
      maxWidth: 1200,
      maxHeight: 1200,
      quality: 0.8,
    });

    if (result.didCancel) {
      return;
    }

    if (result.errorMessage) {
      Alert.alert('Image selection failed', result.errorMessage);
      return;
    }

    const pickedAsset = normalizePickedProfileAsset(result.assets);

    if (!pickedAsset) {
      Alert.alert(
        'Image selection failed',
        'Please choose a JPG, JPEG, PNG, or WEBP image.',
      );
      return;
    }

    try {
      setPictureSubmitting(true);
      const response = await uploadProfilePicture(pickedAsset);
      setUser((currentUser: typeof user) =>
        currentUser ? {...currentUser, ...response.user} : response.user,
      );
      Alert.alert('Success', response.message);
    } catch (error: any) {
      Alert.alert(
        'Upload failed',
        error?.message || 'Could not upload your profile picture.',
      );
    } finally {
      setPictureSubmitting(false);
    }
  };



  return (
    <View style={styles.root}>
      <SafeAreaView style={styles.safe}>
        <ScrollView
          contentContainerStyle={styles.scroll}
          keyboardShouldPersistTaps="handled"
          showsVerticalScrollIndicator={false}>

          {/* ── brand header ── */}
          <View style={styles.brandRow}>
            <Text style={styles.brandSol}>Sol</Text>
            <Text style={styles.brandGold}>Mate</Text>
          </View>

          {/* ── page title ── */}
          <Text style={styles.pageTitle}>Profile</Text>
          <Text style={styles.pageSubtitle}>Manage your account here.</Text>

          {/* ── profile summary card ── */}
          <View style={styles.profileCard}>
            <View style={styles.profileTopRow}>
              {/* avatar */}
              <View style={styles.avatarCircle}>
                {profilePictureUrl ? (
                  <Image source={{uri: profilePictureUrl}} style={styles.avatarImage} />
                ) : (
                  <Icon name="account-circle-outline" size={36} color="#6B7A99" />
                )}
              </View>

              {/* name + contact */}
              <View style={styles.profileInfo}>
                <Text style={styles.profileName}>{displayName}</Text>
                <Text style={styles.profileMeta}>{user?.email || 'No email on file'}</Text>
              </View>
            </View>

            <View style={styles.profileDivider} />

            {/* technician ID row */}
            <View style={styles.profileIdRow}>
              <Text style={styles.profileIdLabel}>Technician ID</Text>
              <Text style={styles.profileIdValue}>{technicianId}</Text>
            </View>

            <Pressable
              disabled={pictureSubmitting}
              onPress={handleUploadProfilePicture}
              style={({pressed}) => [
                styles.profilePictureBtn,
                pressed && {opacity: 0.8},
                pictureSubmitting && styles.profilePictureBtnDisabled,
              ]}>
              <Text style={styles.profilePictureBtnText}>
                {pictureSubmitting
                  ? 'Uploading…'
                  : user?.profile_picture
                    ? 'Change Profile Picture'
                    : 'Upload Profile Picture'}
              </Text>
            </Pressable>
          </View>

          {/* ── Personal Information row ── */}
          <Pressable
            style={({pressed}) => [styles.menuCard, pressed && styles.menuCardPressed]}
            onPress={() => togglePanel('info')}>
            <View style={styles.menuLeft}>
              <View style={styles.menuIconBox}>
                <Icon name="account-outline" size={20} color={ICON_COLOR} />
              </View>
              <Text style={styles.menuLabel}>Personal Information</Text>
            </View>
            <Text style={styles.menuChevron}>›</Text>
          </Pressable>

          {/* personal info expanded form */}
          {expandedPanel === 'info' ? (
            <View style={styles.expandedPanel}>
              <AppInput
                autoCapitalize="none"
                containerStyle={styles.inputSpacing}
                keyboardType="email-address"
                label="Email address"
                onChangeText={setEmail}
                placeholder="Enter your email"
                value={email}
              />
              <Pressable
                style={({pressed}) => [styles.saveBtn, pressed && {opacity: 0.8}]}
                onPress={handleSaveProfile}>
                <Text style={styles.saveBtnText}>
                  {profileSubmitting ? 'Saving…' : 'Save Changes'}
                </Text>
              </Pressable>
            </View>
          ) : null}

          {/* ── Change Password row ── */}
          <Pressable
            style={({pressed}) => [styles.menuCard, pressed && styles.menuCardPressed]}
            onPress={() => togglePanel('password')}>
            <View style={styles.menuLeft}>
              <View style={styles.menuIconBox}>
                <Icon name="lock-outline" size={20} color={ICON_COLOR} />
              </View>
              <Text style={styles.menuLabel}>Change Password</Text>
            </View>
            <Text style={styles.menuChevron}>›</Text>
          </Pressable>

          {/* change password expanded form */}
          {expandedPanel === 'password' ? (
            <View style={styles.expandedPanel}>
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
              <Text style={styles.helperText}>{PASSWORD_REQUIREMENTS_TEXT}</Text>
              <AppInput
                containerStyle={styles.inputSpacing}
                label="Confirm new password"
                onChangeText={setConfirmNewPassword}
                placeholder="Confirm new password"
                secureTextEntry={true}
                value={confirmNewPassword}
              />
              <Pressable
                style={({pressed}) => [styles.saveBtn, pressed && {opacity: 0.8}]}
                onPress={handleChangePassword}>
                <Text style={styles.saveBtnText}>
                  {passwordSubmitting ? 'Updating…' : 'Update Password'}
                </Text>
              </Pressable>
            </View>
          ) : null}

          {/* ── spacer ── */}
          <View style={styles.spacer} />

          {/* ── logout ── */}
          <Pressable
            style={({pressed}) => [styles.logoutBtn, pressed && {opacity: 0.75}]}
            onPress={logout}>
            <View style={styles.logoutDot} />
            <Text style={styles.logoutText}>Logout</Text>
          </Pressable>

        </ScrollView>
      </SafeAreaView>

      <TechnicianBottomNav activeTab="Profile" />
    </View>
  );
}
;

/* ── screen styles ── */
const styles = StyleSheet.create({
  root: {
    flex: 1,
    backgroundColor: BG,
  },
  safe: {
    flex: 1,
    backgroundColor: BG,
  },
  scroll: {
    paddingHorizontal: 18,
    paddingTop: 16,
    paddingBottom: 90,
  },

  /* brand */
  brandRow: {
    flexDirection: 'row',
    marginBottom: 14,
    paddingHorizontal: 16,
    paddingVertical: 14,
    borderRadius: 20,
    backgroundColor: CARD,
    borderWidth: 1,
    borderColor: DIVIDER,
    shadowColor: '#8a9bbd',
    shadowOffset: {width: 0, height: 8},
    shadowOpacity: 0.08,
    shadowRadius: 18,
    elevation: 3,
  },
  brandSol: {
    color: NAVY,
    fontSize: 20,
    fontWeight: '800',
  },
  brandGold: {
    color: GOLD,
    fontSize: 20,
    fontWeight: '800',
  },

  /* page title */
  pageTitle: {
    color: NAVY,
    fontSize: 28,
    fontWeight: '800',
    lineHeight: 34,
    marginBottom: 4,
  },
  pageSubtitle: {
    color: MUTED,
    fontSize: 14,
    marginBottom: 20,
  },

  /* profile summary card */
  profileCard: {
    backgroundColor: CARD,
    borderRadius: 20,
    borderWidth: 1,
    borderColor: DIVIDER,
    padding: 18,
    marginBottom: 14,
    shadowColor: '#8a9bbd',
    shadowOffset: {width: 0, height: 3},
    shadowOpacity: 0.10,
    shadowRadius: 10,
    elevation: 3,
  },
  profileTopRow: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 14,
  },
  avatarCircle: {
    width: 56,
    height: 56,
    borderRadius: 28,
    backgroundColor: '#EAF9FD',
    borderWidth: 1,
    borderColor: '#D4E0F2',
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: 14,
    overflow: 'hidden',
  },
  avatarImage: {
    width: '100%',
    height: '100%',
  },
  avatarIconWrap: {
    alignItems: 'center',
  },
  avatarHead: {
    width: 18,
    height: 18,
    borderRadius: 9,
    backgroundColor: NAVY,
    marginBottom: 3,
  },
  avatarBody: {
    width: 28,
    height: 14,
    borderTopLeftRadius: 14,
    borderTopRightRadius: 14,
    backgroundColor: NAVY,
  },
  profileInfo: {
    flex: 1,
  },
  profileName: {
    color: NAVY,
    fontSize: 16,
    fontWeight: '800',
    marginBottom: 3,
  },
  profileMeta: {
    color: MUTED,
    fontSize: 12,
    lineHeight: 18,
  },
  profileDivider: {
    height: 1,
    backgroundColor: DIVIDER,
    marginBottom: 12,
  },
  profileIdRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  profileIdLabel: {
    color: MUTED,
    fontSize: 13,
  },
  profileIdValue: {
    color: NAVY,
    fontSize: 13,
    fontWeight: '800',
  },
  profilePictureBtn: {
    alignSelf: 'flex-start',
    marginTop: 14,
    paddingHorizontal: 14,
    paddingVertical: 10,
    borderRadius: 16,
    borderWidth: 1,
    borderColor: '#d9b24c',
    backgroundColor: '#fff8e3',
  },
  profilePictureBtnDisabled: {
    opacity: 0.7,
  },
  profilePictureBtnText: {
    color: NAVY,
    fontSize: 12,
    fontWeight: '800',
  },

  /* menu rows */
  menuCard: {
    backgroundColor: CARD,
    borderRadius: 16,
    borderWidth: 1,
    borderColor: DIVIDER,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: 16,
    paddingVertical: 16,
    marginBottom: 10,
    shadowColor: '#8a9bbd',
    shadowOffset: {width: 0, height: 2},
    shadowOpacity: 0.07,
    shadowRadius: 6,
    elevation: 2,
  },
  menuCardPressed: {
    opacity: 0.75,
  },
  menuLeft: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 14,
  },
  menuIconBox: {
    width: 40,
    height: 40,
    borderRadius: 14,
    backgroundColor: '#E2EBF8',
    borderWidth: 1,
    borderColor: '#C4D4EC',
    alignItems: 'center',
    justifyContent: 'center',
  },
  menuIconWrap: {
    alignItems: 'center',
  },
  menuPersonHead: {
    width: 10,
    height: 10,
    borderRadius: 5,
    backgroundColor: NAVY,
    marginBottom: 2,
  },
  menuPersonBody: {
    width: 16,
    height: 8,
    borderTopLeftRadius: 8,
    borderTopRightRadius: 8,
    backgroundColor: NAVY,
  },
  lockShackle: {
    width: 10,
    height: 7,
    borderTopLeftRadius: 5,
    borderTopRightRadius: 5,
    borderWidth: 2.5,
    borderColor: NAVY,
    borderBottomWidth: 0,
    marginBottom: 1,
    alignSelf: 'center',
  },
  lockBody: {
    width: 16,
    height: 11,
    borderRadius: 3,
    backgroundColor: NAVY,
  },
  menuLabel: {
    color: NAVY,
    fontSize: 15,
    fontWeight: '700',
  },
  menuChevron: {
    color: MUTED,
    fontSize: 22,
    fontWeight: '400',
  },

  /* expanded inline form panel */
  expandedPanel: {
    backgroundColor: CARD,
    borderRadius: 16,
    borderWidth: 1,
    borderColor: DIVIDER,
    padding: 16,
    marginTop: -6,
    marginBottom: 10,
  },
  inputSpacing: {
    marginBottom: 12,
  },
  helperText: {
    color: MUTED,
    fontSize: 12,
    lineHeight: 18,
    marginTop: -4,
    marginBottom: 12,
  },
  saveBtn: {
    backgroundColor: NAVY,
    borderRadius: 10,
    paddingVertical: 13,
    alignItems: 'center',
    marginTop: 4,
  },
  saveBtnText: {
    color: CARD,
    fontSize: 15,
    fontWeight: '700',
  },

  /* spacer */
  spacer: {
    height: 32,
  },

  /* logout */
  logoutBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    borderWidth: 1.5,
    borderColor: RED,
    borderRadius: 14,
    paddingVertical: 16,
    gap: 10,
  },
  logoutDot: {
    width: 8,
    height: 8,
    borderRadius: 4,
    backgroundColor: RED,
  },
  logoutText: {
    color: RED,
    fontSize: 16,
    fontWeight: '800',
    letterSpacing: 0.5,
  },
});
