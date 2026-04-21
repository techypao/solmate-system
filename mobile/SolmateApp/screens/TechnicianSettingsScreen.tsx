import React, {useCallback, useContext, useState} from 'react';
import {
  Alert,
  Pressable,
  SafeAreaView,
  ScrollView,
  StyleSheet,
  Text,
  View,
} from 'react-native';

import {AppInput} from '../components';
import {AuthContext} from '../src/context/AuthContext';
import {
  updateTechnicianAccount,
  updateTechnicianPassword,
} from '../src/services/technicianAccountApi';

/* ── design tokens ── */
const NAVY    = '#152a4a';
const GOLD    = '#e8a800';
const BG      = '#dde5f4';
const CARD    = '#ffffff';
const MUTED   = '#7b8699';
const DIVIDER = '#e4eaf5';
const RED     = '#dc2626';

/* ── icons ── */
function AvatarIcon() {
  return (
    <View style={styles.avatarIconWrap}>
      <View style={styles.avatarHead} />
      <View style={styles.avatarBody} />
    </View>
  );
}

function PersonIcon() {
  return (
    <View style={styles.menuIconWrap}>
      <View style={styles.menuPersonHead} />
      <View style={styles.menuPersonBody} />
    </View>
  );
}

function LockIcon() {
  return (
    <View style={styles.menuIconWrap}>
      <View style={styles.lockShackle} />
      <View style={styles.lockBody} />
    </View>
  );
}

function HomeIcon({active}: {active?: boolean}) {
  const c = active ? NAVY : MUTED;
  return (
    <Text style={{fontSize: 20, color: c, lineHeight: 22, textAlign: 'center'}}>{'\u2302'}</Text>
  );
}

function InspectIcon({active}: {active?: boolean}) {
  return (
    <View style={nav.iconWrap}>
      <View style={[nav.listBox, active && nav.activeShape]}>
        <View style={[nav.listLine, active && nav.activeLine]} />
        <View style={[nav.listLine, {width: 12}, active && nav.activeLine]} />
        <View style={[nav.listLine, active && nav.activeLine]} />
      </View>
    </View>
  );
}

function ServicesIcon({active}: {active?: boolean}) {
  return (
    <View style={nav.iconWrap}>
      <View style={[nav.gear, active && nav.activeShape]}>
        <View style={[nav.gearInner, active && nav.activeShape]} />
      </View>
    </View>
  );
}

function ProfileNavIcon({active}: {active?: boolean}) {
  return (
    <View style={nav.iconWrap}>
      <View style={[nav.profileHead, active && nav.activeShape]} />
      <View style={[nav.profileBody, active && nav.activeShape]} />
    </View>
  );
}

type Tab = 'Home' | 'Inspections' | 'Services' | 'Profile';

function BottomNav({active, onPress}: {active: Tab; onPress: (t: Tab) => void}) {
  const tabs: {key: Tab; label: string; Icon: React.FC<{active?: boolean}>}[] = [
    {key: 'Home',        label: 'Home',        Icon: HomeIcon},
    {key: 'Inspections', label: 'Inspections', Icon: InspectIcon},
    {key: 'Services',    label: 'Services',    Icon: ServicesIcon},
    {key: 'Profile',     label: 'Profile',     Icon: ProfileNavIcon},
  ];
  return (
    <View style={nav.bar}>
      {tabs.map(({key, label, Icon}) => {
        const isActive = active === key;
        return (
          <Pressable key={key} style={nav.tab} onPress={() => onPress(key)}>
            <Icon active={isActive} />
            <Text style={[nav.label, isActive && nav.labelActive]}>{label}</Text>
          </Pressable>
        );
      })}
    </View>
  );
}

/* ── main screen ── */
export default function TechnicianSettingsScreen({navigation}: any) {
  const {logout, setUser, user} = useContext(AuthContext);

  /* form state */
  const [name, setName]                         = useState(user?.name || '');
  const [email, setEmail]                       = useState(user?.email || '');
  const [currentPassword, setCurrentPassword]   = useState('');
  const [newPassword, setNewPassword]           = useState('');
  const [confirmNewPassword, setConfirmNewPassword] = useState('');
  const [profileSubmitting, setProfileSubmitting]   = useState(false);
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

  const handleSaveProfile = async () => {
    const trimmedName  = name.trim();
    const trimmedEmail = email.trim().toLowerCase();

    if (profileSubmitting) {return;}
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
      const response = await updateTechnicianAccount({name: trimmedName, email: trimmedEmail});
      setUser((currentUser: typeof user) =>
        currentUser ? {...currentUser, ...response.user} : response.user,
      );
      Alert.alert('Success', response.message);
      setExpandedPanel(null);
    } catch (error: any) {
      Alert.alert('Update failed', error?.message || 'Could not update your account information.');
    } finally {
      setProfileSubmitting(false);
    }
  };

  const handleChangePassword = async () => {
    if (passwordSubmitting) {return;}
    if (!currentPassword || !newPassword || !confirmNewPassword) {
      Alert.alert('Incomplete form', 'Please fill in all password fields.');
      return;
    }
    if (newPassword.length < 8) {
      Alert.alert('Weak password', 'Your new password must be at least 8 characters long.');
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

  function handleTabPress(tab: Tab) {
    if (tab === 'Home')        {navigation.navigate('TechnicianDashboard');}
    if (tab === 'Inspections') {navigation.navigate('AssignedInspectionRequests');}
    if (tab === 'Services')    {navigation.navigate('TechnicianServiceRequests');}
  }

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
                <AvatarIcon />
              </View>

              {/* name + contact */}
              <View style={styles.profileInfo}>
                <Text style={styles.profileName}>{user?.name || 'Technician'}</Text>
                <Text style={styles.profileMeta}>{user?.email || 'No email on file'}</Text>
              </View>

              {/* edit button */}
              <Pressable
                hitSlop={10}
                onPress={() => togglePanel('info')}
                style={({pressed}) => [styles.editBtn, pressed && {opacity: 0.6}]}>
                <Text style={styles.editBtnText}>Edit</Text>
              </Pressable>
            </View>

            <View style={styles.profileDivider} />

            {/* technician ID row */}
            <View style={styles.profileIdRow}>
              <Text style={styles.profileIdLabel}>Technician ID</Text>
              <Text style={styles.profileIdValue}>{technicianId}</Text>
            </View>
          </View>

          {/* ── Personal Information row ── */}
          <Pressable
            style={({pressed}) => [styles.menuCard, pressed && styles.menuCardPressed]}
            onPress={() => togglePanel('info')}>
            <View style={styles.menuLeft}>
              <View style={styles.menuIconBox}>
                <PersonIcon />
              </View>
              <Text style={styles.menuLabel}>Personal Information</Text>
            </View>
            <Text style={styles.menuChevron}>›</Text>
          </Pressable>

          {/* personal info expanded form */}
          {expandedPanel === 'info' ? (
            <View style={styles.expandedPanel}>
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
                <LockIcon />
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

      {/* ── bottom nav ── */}
      <BottomNav active="Profile" onPress={handleTabPress} />
    </View>
  );
}

/* ── bottom nav styles ── */
const nav = StyleSheet.create({
  bar: {
    flexDirection: 'row',
    backgroundColor: CARD,
    borderTopWidth: 1,
    borderTopColor: DIVIDER,
    paddingBottom: 6,
    paddingTop: 8,
  },
  tab: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: 4,
  },
  label: {
    fontSize: 11,
    color: MUTED,
    marginTop: 3,
    fontWeight: '500',
  },
  labelActive: {
    color: NAVY,
    fontWeight: '700',
  },
  iconWrap: {
    width: 24,
    height: 22,
    alignItems: 'center',
    justifyContent: 'flex-end',
  },
  listBox: {
    width: 18,
    height: 20,
    backgroundColor: MUTED,
    borderRadius: 3,
    alignItems: 'flex-start',
    justifyContent: 'center',
    paddingHorizontal: 3,
    gap: 3,
  },
  listLine: {
    height: 2,
    width: 10,
    backgroundColor: '#f2f4f8',
    borderRadius: 1,
  },
  gear: {
    width: 20,
    height: 20,
    borderRadius: 10,
    borderWidth: 3,
    borderColor: MUTED,
    alignItems: 'center',
    justifyContent: 'center',
  },
  gearInner: {
    width: 8,
    height: 8,
    borderRadius: 4,
    backgroundColor: MUTED,
  },
  profileHead: {
    width: 10,
    height: 10,
    borderRadius: 5,
    backgroundColor: MUTED,
    marginBottom: 2,
  },
  profileBody: {
    width: 16,
    height: 8,
    borderTopLeftRadius: 8,
    borderTopRightRadius: 8,
    backgroundColor: MUTED,
  },
  activeShape: {
    borderColor: NAVY,
    backgroundColor: NAVY,
    borderBottomColor: NAVY,
  },
  activeLine: {
    backgroundColor: '#f2f4f8',
  },
});

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
    paddingBottom: 24,
  },

  /* brand */
  brandRow: {
    flexDirection: 'row',
    marginBottom: 14,
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
    backgroundColor: '#dde5f4',
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: 14,
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
  editBtn: {
    paddingHorizontal: 4,
    paddingVertical: 2,
  },
  editBtnText: {
    color: GOLD,
    fontSize: 14,
    fontWeight: '700',
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
    width: 36,
    height: 36,
    borderRadius: 10,
    backgroundColor: '#eef2fa',
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
