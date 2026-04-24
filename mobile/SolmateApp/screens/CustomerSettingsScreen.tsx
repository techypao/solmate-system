import React, {useContext, useEffect, useState} from 'react';
import {
  Alert,
  Pressable,
  SafeAreaView,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  View,
} from 'react-native';
import {useNavigation} from '@react-navigation/native';

import {AuthContext} from '../src/context/AuthContext';
import {
  updateCustomerAccount,
  updateCustomerPassword,
} from '../src/services/accountApi';

/* \u2500\u2500 design tokens \u2500\u2500 */

const NAVY = '#152a4a';
const GOLD = '#e8a800';
const MUTED = '#7b8699';
const BG = '#e0e8f5';
const CARD = '#ffffff';
const DIVIDER = '#edf1f7';

/* \u2500\u2500 helpers \u2500\u2500 */

function formatRole(role?: string | null) {
  if (!role) return 'Customer';
  return role.charAt(0).toUpperCase() + role.slice(1);
}

function formatProfileValue(value?: string | null) {
  if (!value || !value.trim()) {
    return 'Not provided';
  }

  return value.trim();
}

/* \u2500\u2500 small presentational pieces \u2500\u2500 */

function MenuRow({
  icon,
  label,
  onPress,
  expanded,
}: {
  icon: string;
  label: string;
  onPress: () => void;
  expanded?: boolean;
}) {
  return (
    <Pressable
      onPress={onPress}
      style={({pressed}) => [s.menuRow, pressed && s.pressed]}>
      <View style={s.menuIconWrap}>
        <Text style={s.menuIcon}>{icon}</Text>
      </View>
      <Text style={s.menuLabel}>{label}</Text>
      <Text style={s.menuChevron}>{expanded ? '\u2304' : '\u203A'}</Text>
    </Pressable>
  );
}

function FormLabel({text}: {text: string}) {
  return <Text style={s.formLabel}>{text}</Text>;
}

/* \u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550
   Main screen
   \u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550 */

export default function CustomerSettingsScreen() {
  const navigation = useNavigation<any>();
  const {logout, setUser, user} = useContext(AuthContext);

  /* \u2500\u2500 profile form state (preserved) \u2500\u2500 */
  const [name, setName] = useState(user?.name || '');
  const [email, setEmail] = useState(user?.email || '');
  const [address, setAddress] = useState(user?.address || '');
  const [contactNumber, setContactNumber] = useState(user?.contact_number || '');
  const [profileSubmitting, setProfileSubmitting] = useState(false);

  /* \u2500\u2500 password form state (preserved) \u2500\u2500 */
  const [currentPassword, setCurrentPassword] = useState('');
  const [newPassword, setNewPassword] = useState('');
  const [confirmNewPassword, setConfirmNewPassword] = useState('');
  const [passwordSubmitting, setPasswordSubmitting] = useState(false);

  /* \u2500\u2500 UI expansion state \u2500\u2500 */
  const [editExpanded, setEditExpanded] = useState(false);
  const [passwordExpanded, setPasswordExpanded] = useState(false);

  useEffect(() => {
    setName(user?.name || '');
    setEmail(user?.email || '');
    setAddress(user?.address || '');
    setContactNumber(user?.contact_number || '');
  }, [user?.address, user?.contact_number, user?.email, user?.name]);

  /* \u2500\u2500 handlers (100 % preserved) \u2500\u2500 */

  const handleSaveProfile = async () => {
    const trimmedName = name.trim();
    const trimmedEmail = email.trim().toLowerCase();

    if (profileSubmitting) return;

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
      const response = await updateCustomerAccount({
        name: trimmedName,
        email: trimmedEmail,
        address: address.trim(),
        contact_number: contactNumber.trim(),
      });
      setUser((currentUser: typeof user) =>
        currentUser
          ? {...currentUser, ...response.user}
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
    if (passwordSubmitting) return;

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
      const response = await updateCustomerPassword({
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

  /* \u2500\u2500 derived \u2500\u2500 */

  const initial = (user?.name || 'C').charAt(0).toUpperCase();

  /* \u2500\u2500 render \u2500\u2500 */

  return (
    <SafeAreaView style={s.safe}>
      <ScrollView
        contentContainerStyle={s.scroll}
        keyboardShouldPersistTaps="handled"
        showsVerticalScrollIndicator={false}>

        {/* \u2500\u2500 brand \u2500\u2500 */}
        <Text style={s.brand}>
          Sol<Text style={s.brandAccent}>Mate</Text>
        </Text>

        {/* \u2500\u2500 title \u2500\u2500 */}
        <Text style={s.title}>Profile</Text>
        <Text style={s.subtitle}>Manage your account here.</Text>

        {/* \u2500\u2500 profile summary card \u2500\u2500 */}
        <View style={s.profileCard}>
          <View style={s.avatarCircle}>
            <Text style={s.avatarText}>{initial}</Text>
          </View>
          <View style={s.profileInfo}>
            <Text style={s.profileName}>{user?.name || 'Customer'}</Text>
            <Text style={s.profileEmail}>{user?.email || 'Not available'}</Text>
            <Text style={s.profileRole}>{formatRole(user?.role)}</Text>
            <View style={s.profileDetailList}>
              <View style={s.profileDetailRow}>
                <Text style={s.profileDetailLabel}>Address</Text>
                <Text style={s.profileDetailValue}>
                  {formatProfileValue(user?.address)}
                </Text>
              </View>
              <View style={s.profileDetailRow}>
                <Text style={s.profileDetailLabel}>Contact Number</Text>
                <Text style={s.profileDetailValue}>
                  {formatProfileValue(user?.contact_number)}
                </Text>
              </View>
            </View>
          </View>
          <Pressable
            hitSlop={12}
            onPress={() => setEditExpanded(!editExpanded)}
            style={({pressed}) => [pressed && s.pressed]}>
            <Text style={s.editLink}>Edit</Text>
          </Pressable>
        </View>

        {/* \u2500\u2500 inline edit form (expanded) \u2500\u2500 */}
        {editExpanded && (
          <View style={s.expandedCard}>
            <Text style={s.expandedTitle}>Update Account</Text>
            <Text style={s.expandedSub}>
              Edit your account details. Changes are saved directly.
            </Text>

            <FormLabel text="Full Name" />
            <TextInput
              autoCapitalize="words"
              onChangeText={setName}
              placeholder="Enter your full name"
              placeholderTextColor="#a8b4c8"
              style={s.input}
              value={name}
            />

            <FormLabel text="Email Address" />
            <TextInput
              autoCapitalize="none"
              keyboardType="email-address"
              onChangeText={setEmail}
              placeholder="Enter your email"
              placeholderTextColor="#a8b4c8"
              style={s.input}
              value={email}
            />

            <FormLabel text="Address" />
            <TextInput
              onChangeText={setAddress}
              placeholder="Enter your address"
              placeholderTextColor="#a8b4c8"
              style={s.input}
              value={address}
            />

            <FormLabel text="Contact Number" />
            <TextInput
              keyboardType="phone-pad"
              onChangeText={setContactNumber}
              placeholder="Enter your contact number"
              placeholderTextColor="#a8b4c8"
              style={s.input}
              value={contactNumber}
            />

            <Pressable
              onPress={handleSaveProfile}
              style={({pressed}) => [s.goldBtn, pressed && s.pressed]}>
              <Text style={s.goldBtnText}>
                {profileSubmitting ? 'Saving\u2026' : 'Save Account Details'}
              </Text>
            </Pressable>
          </View>
        )}

        {/* \u2500\u2500 menu rows \u2500\u2500 */}
        <MenuRow
          icon={'\ud83d\udc64'}
          label="Personal Information"
          onPress={() => setEditExpanded(!editExpanded)}
          expanded={editExpanded}
        />

        <MenuRow
          icon={'\ud83d\udcc2'}
          label="My Quotations"
          onPress={() => navigation.navigate('QuotationList')}
        />

        <MenuRow
          icon={'\u23f1\ufe0f'}
          label="My Service History"
          onPress={() => navigation.navigate('ServiceRequestList')}
        />

        <MenuRow
          icon={'\ud83d\udd0d'}
          label="My Inspection History"
          onPress={() => navigation.navigate('InspectionRequestList')}
        />

        <MenuRow
          icon={'\u2b50'}
          label="My Testimonies"
          onPress={() => navigation.navigate('MyTestimonies')}
        />

        <MenuRow
          icon={'\ud83d\udd12'}
          label="Change Password"
          onPress={() => setPasswordExpanded(!passwordExpanded)}
          expanded={passwordExpanded}
        />

        {/* \u2500\u2500 inline password form (expanded) \u2500\u2500 */}
        {passwordExpanded && (
          <View style={s.expandedCard}>
            <Text style={s.expandedTitle}>Change Password</Text>
            <Text style={s.expandedSub}>
              Enter your current password before choosing a new one.
            </Text>

            <FormLabel text="Current Password" />
            <TextInput
              onChangeText={setCurrentPassword}
              placeholder="Enter current password"
              placeholderTextColor="#a8b4c8"
              secureTextEntry
              style={s.input}
              value={currentPassword}
            />

            <FormLabel text="New Password" />
            <TextInput
              onChangeText={setNewPassword}
              placeholder="Enter new password"
              placeholderTextColor="#a8b4c8"
              secureTextEntry
              style={s.input}
              value={newPassword}
            />

            <FormLabel text="Confirm New Password" />
            <TextInput
              onChangeText={setConfirmNewPassword}
              placeholder="Confirm new password"
              placeholderTextColor="#a8b4c8"
              secureTextEntry
              style={s.input}
              value={confirmNewPassword}
            />

            <Pressable
              onPress={handleChangePassword}
              style={({pressed}) => [s.goldBtn, pressed && s.pressed]}>
              <Text style={s.goldBtnText}>
                {passwordSubmitting ? 'Updating\u2026' : 'Update Password'}
              </Text>
            </Pressable>
          </View>
        )}

        {/* \u2500\u2500 logout \u2500\u2500 */}
        <Pressable
          onPress={logout}
          style={({pressed}) => [s.logoutBtn, pressed && s.pressed]}>
          <View style={s.logoutDot} />
          <Text style={s.logoutText}>Logout</Text>
        </Pressable>

        {/* \u2500\u2500 chat with SolBot \u2500\u2500 */}
        <Pressable
          onPress={() => navigation.navigate('Chatbot')}
          style={({pressed}) => [s.chatRow, pressed && s.pressed]}>
          <Text style={s.chatLabel}>Chat with SolBot</Text>
          <View style={s.chatFab}>
            <Text style={s.chatFabIcon}>{'\ud83e\udd16'}</Text>
          </View>
        </Pressable>

        {/* \u2500\u2500 bottom nav \u2500\u2500 */}
        <View style={s.bottomNav}>
          <Pressable
            style={s.navItem}
            onPress={() => navigation.navigate('Home')}>
            <Text style={s.navIcon}>{'\ud83c\udfe0'}</Text>
            <Text style={s.navLabel}>Home</Text>
          </Pressable>
          <Pressable
            style={s.navItem}
            onPress={() => navigation.navigate('QuotationList')}>
            <Text style={s.navIcon}>{'\ud83d\udccb'}</Text>
            <Text style={s.navLabel}>Quotation</Text>
          </Pressable>
          <Pressable
            style={s.navItem}
            onPress={() => navigation.navigate('ServiceRequestList')}>
            <Text style={s.navIcon}>{'\u2699\ufe0f'}</Text>
            <Text style={s.navLabel}>Services</Text>
          </Pressable>
          <Pressable
            style={s.navItem}
            onPress={() => navigation.navigate('InspectionRequestList')}>
            <Text style={s.navIcon}>{'\ud83d\udccd'}</Text>
            <Text style={s.navLabel}>Tracking</Text>
          </Pressable>
          <Pressable style={s.navItem} onPress={() => {}}>
            <Text style={s.navIconActive}>{'\ud83d\udc64'}</Text>
            <Text style={s.navLabelActive}>Profile</Text>
          </Pressable>
        </View>
      </ScrollView>
    </SafeAreaView>
  );
}

/* \u2500\u2500 styles \u2500\u2500 */

const s = StyleSheet.create({
  safe: {flex: 1, backgroundColor: BG},
  scroll: {paddingHorizontal: 22, paddingTop: 20, paddingBottom: 30},
  pressed: {opacity: 0.85},

  /* brand */
  brand: {fontSize: 22, fontWeight: '800', color: NAVY, marginBottom: 4},
  brandAccent: {color: GOLD},

  /* title */
  title: {fontSize: 28, fontWeight: '900', color: NAVY, marginBottom: 2},
  subtitle: {fontSize: 14, color: MUTED, marginBottom: 20},

  /* profile summary card */
  profileCard: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: CARD,
    borderRadius: 22,
    padding: 18,
    marginBottom: 18,
    shadowColor: '#8a9bbd',
    shadowOffset: {width: 0, height: 4},
    shadowOpacity: 0.1,
    shadowRadius: 14,
    elevation: 4,
  },
  avatarCircle: {
    width: 54,
    height: 54,
    borderRadius: 27,
    backgroundColor: '#d6dff0',
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: 14,
  },
  avatarText: {fontSize: 24, fontWeight: '800', color: NAVY},
  profileInfo: {flex: 1},
  profileName: {fontSize: 17, fontWeight: '800', color: NAVY, marginBottom: 2},
  profileEmail: {fontSize: 13, color: MUTED, marginBottom: 1},
  profileRole: {fontSize: 12, color: MUTED, opacity: 0.75},
  profileDetailList: {marginTop: 10, gap: 6},
  profileDetailRow: {gap: 2},
  profileDetailLabel: {
    fontSize: 11,
    fontWeight: '800',
    color: MUTED,
    textTransform: 'uppercase',
    letterSpacing: 0.3,
  },
  profileDetailValue: {fontSize: 13, color: NAVY, fontWeight: '600'},
  editLink: {fontSize: 15, fontWeight: '700', color: NAVY},

  /* menu rows */
  menuRow: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: CARD,
    borderRadius: 18,
    paddingVertical: 16,
    paddingHorizontal: 18,
    marginBottom: 10,
    shadowColor: '#8a9bbd',
    shadowOffset: {width: 0, height: 3},
    shadowOpacity: 0.08,
    shadowRadius: 10,
    elevation: 3,
  },
  menuIconWrap: {
    width: 40,
    height: 40,
    borderRadius: 14,
    backgroundColor: '#eaf0fb',
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: 14,
  },
  menuIcon: {fontSize: 18},
  menuLabel: {flex: 1, fontSize: 15, fontWeight: '800', color: NAVY},
  menuChevron: {fontSize: 22, color: '#bcc5d3', fontWeight: '600'},

  /* expanded form card */
  expandedCard: {
    backgroundColor: CARD,
    borderRadius: 22,
    padding: 20,
    marginBottom: 12,
    marginTop: -4,
    shadowColor: '#8a9bbd',
    shadowOffset: {width: 0, height: 2},
    shadowOpacity: 0.06,
    shadowRadius: 8,
    elevation: 2,
  },
  expandedTitle: {
    fontSize: 17,
    fontWeight: '800',
    color: NAVY,
    marginBottom: 4,
  },
  expandedSub: {fontSize: 13, color: MUTED, lineHeight: 19, marginBottom: 16},

  /* form elements */
  formLabel: {
    fontSize: 12,
    fontWeight: '700',
    color: MUTED,
    textTransform: 'uppercase',
    marginBottom: 6,
    marginTop: 4,
  },
  input: {
    backgroundColor: '#f4f7fc',
    borderRadius: 14,
    borderWidth: 1,
    borderColor: DIVIDER,
    paddingHorizontal: 16,
    paddingVertical: 13,
    fontSize: 15,
    color: NAVY,
    marginBottom: 12,
  },

  /* buttons */
  goldBtn: {
    backgroundColor: GOLD,
    borderRadius: 28,
    paddingVertical: 14,
    alignItems: 'center',
    marginTop: 4,
    shadowColor: GOLD,
    shadowOffset: {width: 0, height: 4},
    shadowOpacity: 0.25,
    shadowRadius: 10,
    elevation: 4,
  },
  goldBtnText: {
    fontSize: 15,
    fontWeight: '900',
    color: CARD,
    letterSpacing: 0.3,
  },

  /* logout */
  logoutBtn: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: CARD,
    borderRadius: 18,
    borderWidth: 1.5,
    borderColor: '#e53e3e',
    paddingVertical: 15,
    marginTop: 8,
    marginBottom: 20,
  },
  logoutDot: {
    width: 10,
    height: 10,
    borderRadius: 5,
    backgroundColor: '#e53e3e',
    marginRight: 10,
  },
  logoutText: {fontSize: 16, fontWeight: '800', color: '#e53e3e'},

  /* chat shortcut */
  chatRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'flex-end',
    marginBottom: 22,
  },
  chatLabel: {fontSize: 13, color: MUTED, marginRight: 10},
  chatFab: {
    width: 48,
    height: 48,
    borderRadius: 24,
    backgroundColor: NAVY,
    alignItems: 'center',
    justifyContent: 'center',
    shadowColor: NAVY,
    shadowOffset: {width: 0, height: 4},
    shadowOpacity: 0.25,
    shadowRadius: 8,
    elevation: 5,
  },
  chatFabIcon: {fontSize: 22},

  /* bottom nav */
  bottomNav: {
    flexDirection: 'row',
    justifyContent: 'space-around',
    backgroundColor: CARD,
    borderRadius: 18,
    paddingVertical: 10,
    shadowColor: '#8a9bbd',
    shadowOffset: {width: 0, height: -2},
    shadowOpacity: 0.08,
    shadowRadius: 8,
    elevation: 4,
  },
  navItem: {alignItems: 'center', paddingHorizontal: 6},
  navIcon: {fontSize: 20, marginBottom: 2},
  navIconActive: {fontSize: 20, marginBottom: 2},
  navLabel: {fontSize: 11, color: MUTED, fontWeight: '600'},
  navLabelActive: {fontSize: 11, color: NAVY, fontWeight: '700'},
});
