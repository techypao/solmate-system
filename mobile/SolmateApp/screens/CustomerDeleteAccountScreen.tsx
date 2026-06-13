import React from 'react';
import {
  Alert,
  Linking,
  SafeAreaView,
  ScrollView,
  StyleSheet,
  Text,
  Pressable,
  View,
} from 'react-native';
import { useNavigation } from '@react-navigation/native';
import Icon from 'react-native-vector-icons/MaterialCommunityIcons';

const DELETE_ACCOUNT_URL = 'https://solmatebyrdy.com/delete-account';

const NAVY = '#1A2B55';
const GOLD = '#F5C000';
const MUTED = '#6B7A99';
const BG = '#C8D8F0';
const CARD = '#ffffff';
const DANGER = '#dc2626';

export default function CustomerDeleteAccountScreen() {
  const navigation = useNavigation<any>();

  const openDeletionPage = async () => {
    try {
      const canOpen = await Linking.canOpenURL(DELETE_ACCOUNT_URL);

      if (!canOpen) {
        Alert.alert(
          'Unable to open page',
          'Please visit https://solmatebyrdy.com/delete-account in your browser.',
        );
        return;
      }

      await Linking.openURL(DELETE_ACCOUNT_URL);
    } catch {
      Alert.alert(
        'Unable to open page',
        'Please visit https://solmatebyrdy.com/delete-account in your browser.',
      );
    }
  };

  return (
    <SafeAreaView style={s.safe}>
      <ScrollView contentContainerStyle={s.scroll}>
        <Pressable
          onPress={() => navigation.goBack()}
          style={({ pressed }) => [s.backButton, pressed && s.pressed]}
        >
          <Icon name="chevron-left" size={24} color={NAVY} />
          <Text style={s.backText}>Back</Text>
        </Pressable>

        <Text style={s.brand}>
          Sol<Text style={s.brandAccent}>Mate</Text>
        </Text>

        <View style={s.card}>
          <View style={s.iconWrap}>
            <Icon name="account-remove-outline" size={34} color={DANGER} />
          </View>

          <Text style={s.eyebrow}>Account Deletion</Text>
          <Text style={s.title}>Delete your SolMate account</Text>
          <Text style={s.copy}>
            You may permanently delete your SolMate account and associated
            personal data by submitting an account deletion request on the
            SolMate website.
          </Text>

          <View style={s.warningBox}>
            <Text style={s.warningTitle}>Before you continue</Text>
            <Text style={s.warningText}>
              Account deletion is permanent and may remove your access to
              quotations, service history, appointments, inspections,
              testimonies, profile details, and other account-related
              information.
            </Text>
          </View>

          <View style={s.stepList}>
            <View style={s.stepRow}>
              <View style={s.stepDot} />
              <Text style={s.stepText}>
                The next button opens the SolMate account deletion page in your
                device browser.
              </Text>
            </View>
            <View style={s.stepRow}>
              <View style={s.stepDot} />
              <Text style={s.stepText}>
                Enter your SolMate account email and an optional reason.
              </Text>
            </View>
            <View style={s.stepRow}>
              <View style={s.stepDot} />
              <Text style={s.stepText}>
                Submit the request and wait for the website confirmation.
              </Text>
            </View>
          </View>

          <Pressable
            onPress={openDeletionPage}
            style={({ pressed }) => [s.primaryButton, pressed && s.pressed]}
          >
            <Text style={s.primaryButtonText}>
              Proceed to Account Deletion
            </Text>
          </Pressable>
        </View>
      </ScrollView>
    </SafeAreaView>
  );
}

const s = StyleSheet.create({
  safe: { flex: 1, backgroundColor: BG },
  scroll: { paddingHorizontal: 22, paddingTop: 18, paddingBottom: 42 },
  pressed: { opacity: 0.84 },
  backButton: {
    alignSelf: 'flex-start',
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    marginBottom: 12,
  },
  backText: { color: NAVY, fontSize: 15, fontWeight: '800' },
  brand: { fontSize: 22, fontWeight: '800', color: NAVY, marginBottom: 18 },
  brandAccent: { color: GOLD },
  card: {
    backgroundColor: CARD,
    borderRadius: 22,
    padding: 22,
    shadowColor: '#8a9bbd',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.1,
    shadowRadius: 14,
    elevation: 4,
  },
  iconWrap: {
    width: 60,
    height: 60,
    borderRadius: 20,
    backgroundColor: '#fee2e2',
    borderWidth: 1,
    borderColor: '#fecaca',
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 18,
  },
  eyebrow: {
    color: DANGER,
    fontSize: 12,
    fontWeight: '900',
    letterSpacing: 1.1,
    textTransform: 'uppercase',
    marginBottom: 8,
  },
  title: {
    color: NAVY,
    fontSize: 26,
    fontWeight: '900',
    lineHeight: 32,
    marginBottom: 10,
  },
  copy: {
    color: MUTED,
    fontSize: 14,
    lineHeight: 22,
    marginBottom: 18,
  },
  warningBox: {
    backgroundColor: '#fff1f2',
    borderWidth: 1,
    borderColor: '#fecaca',
    borderRadius: 18,
    padding: 16,
    marginBottom: 18,
  },
  warningTitle: {
    color: '#991b1b',
    fontSize: 14,
    fontWeight: '900',
    marginBottom: 6,
  },
  warningText: {
    color: '#991b1b',
    fontSize: 13,
    lineHeight: 20,
  },
  stepList: { gap: 12, marginBottom: 22 },
  stepRow: { flexDirection: 'row', gap: 10, alignItems: 'flex-start' },
  stepDot: {
    width: 8,
    height: 8,
    borderRadius: 4,
    backgroundColor: GOLD,
    marginTop: 7,
  },
  stepText: { flex: 1, color: NAVY, fontSize: 13, lineHeight: 20 },
  primaryButton: {
    backgroundColor: GOLD,
    borderRadius: 28,
    paddingVertical: 15,
    paddingHorizontal: 18,
    alignItems: 'center',
    shadowColor: GOLD,
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.22,
    shadowRadius: 10,
    elevation: 4,
  },
  primaryButtonText: {
    color: CARD,
    fontSize: 15,
    fontWeight: '900',
    textAlign: 'center',
  },
});
