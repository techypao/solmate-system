import React from 'react';
import {
  ScrollView,
  StyleSheet,
  Text,
  TouchableOpacity,
  View,
} from 'react-native';
import {solmateColors} from '../src/theme/colors';

type WelcomeScreenProps = {
  navigation?: {
    navigate?: (screen: string) => void;
  };
};

export default function WelcomeScreen({navigation}: WelcomeScreenProps) {
  return (
    <ScrollView
      contentContainerStyle={styles.scroll}
      showsVerticalScrollIndicator={false}>
      <View style={styles.brandRow}>
        <Text style={styles.brandSol}>Sol</Text>
        <Text style={styles.brandMate}>Mate</Text>
      </View>

      <View style={styles.heroCard}>
        <View style={styles.sunMark}>
          <View style={styles.sunCore} />
        </View>

        <Text style={styles.title}>Welcome to SolMate</Text>
        <Text style={styles.copy}>
          Manage your solar service requests, quotations, and updates in one
          customer account.
        </Text>

        <View style={styles.actions}>
          <TouchableOpacity
            activeOpacity={0.86}
            onPress={() => navigation?.navigate?.('Login')}
            style={styles.primaryBtn}>
            <Text style={styles.primaryBtnText}>Login</Text>
          </TouchableOpacity>

          <TouchableOpacity
            activeOpacity={0.86}
            onPress={() => navigation?.navigate?.('Register')}
            style={styles.secondaryBtn}>
            <Text style={styles.secondaryBtnText}>Create Account</Text>
          </TouchableOpacity>
        </View>
      </View>
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  scroll: {
    flexGrow: 1,
    justifyContent: 'center',
    backgroundColor: solmateColors.background,
    paddingHorizontal: 24,
    paddingVertical: 36,
  },
  brandRow: {
    flexDirection: 'row',
    justifyContent: 'center',
    marginBottom: 18,
  },
  brandSol: {
    color: solmateColors.navy,
    fontSize: 32,
    fontWeight: '800',
  },
  brandMate: {
    color: solmateColors.primary,
    fontSize: 32,
    fontWeight: '800',
  },
  heroCard: {
    width: '100%',
    maxWidth: 420,
    alignSelf: 'center',
    alignItems: 'center',
    backgroundColor: solmateColors.white,
    borderRadius: 24,
    paddingHorizontal: 24,
    paddingVertical: 34,
    shadowColor: solmateColors.shadow,
    shadowOffset: {width: 0, height: 8},
    shadowOpacity: 0.14,
    shadowRadius: 18,
    elevation: 7,
  },
  sunMark: {
    width: 88,
    height: 88,
    borderRadius: 44,
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#FFF6C9',
    marginBottom: 22,
  },
  sunCore: {
    width: 48,
    height: 48,
    borderRadius: 24,
    backgroundColor: solmateColors.primary,
  },
  title: {
    color: solmateColors.text,
    fontSize: 30,
    fontWeight: '800',
    textAlign: 'center',
    marginBottom: 10,
  },
  copy: {
    color: solmateColors.muted,
    fontSize: 15,
    lineHeight: 22,
    textAlign: 'center',
    marginBottom: 26,
  },
  actions: {
    width: '100%',
    gap: 12,
  },
  primaryBtn: {
    height: 54,
    borderRadius: 27,
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: solmateColors.primary,
  },
  primaryBtnText: {
    color: solmateColors.text,
    fontSize: 17,
    fontWeight: '800',
  },
  secondaryBtn: {
    height: 54,
    borderRadius: 27,
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: solmateColors.white,
    borderWidth: 1.5,
    borderColor: solmateColors.borderStrong,
  },
  secondaryBtnText: {
    color: solmateColors.navy,
    fontSize: 16,
    fontWeight: '800',
  },
});
