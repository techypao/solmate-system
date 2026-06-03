import React from 'react';
import {
  Pressable,
  SafeAreaView,
  ScrollView,
  StyleSheet,
  Text,
  View,
} from 'react-native';

import CustomerBottomNav from '../src/components/CustomerBottomNav';
import Icon from 'react-native-vector-icons/MaterialCommunityIcons';

const ICON_COLOR = '#1d2f6d';
const NAVY = '#1A2B55';
const GOLD = '#F5C000';
const MUTED = '#6B7A99';
const BG = '#C8D8F0';
const CARD = '#ffffff';
const DIVIDER = '#D4E0F2';

function InspectionCard({
  icon,
  title,
  subtitle,
  onPress,
}: {
  icon: string;
  title: string;
  subtitle: string;
  onPress: () => void;
}) {
  return (
    <Pressable
      onPress={onPress}
      style={({pressed}) => [styles.serviceCard, pressed && styles.pressed]}>
      <View style={styles.serviceCardTop}>
        <View style={styles.serviceIconWrap}>
          <Icon name={icon} size={22} color={ICON_COLOR} />
        </View>
      </View>
      <Text style={styles.serviceTitle}>{title}</Text>
      <Text style={styles.serviceSubtitle}>{subtitle}</Text>
      <Text style={styles.serviceLink}>Open</Text>
    </Pressable>
  );
}

function ShortcutCard({
  label,
  subtitle,
  onPress,
}: {
  label: string;
  subtitle: string;
  onPress: () => void;
}) {
  return (
    <Pressable
      onPress={onPress}
      style={({pressed}) => [styles.shortcutCard, pressed && styles.pressed]}>
      <View>
        <Text style={styles.shortcutLabel}>{label}</Text>
        <Text style={styles.shortcutSubtitle}>{subtitle}</Text>
      </View>
      <Text style={styles.shortcutChevron}>{'>'}</Text>
    </Pressable>
  );
}

export default function InspectionHubScreen({navigation}: any) {
  return (
    <SafeAreaView style={styles.safe}>
      <ScrollView
        style={styles.scrollView}
        contentContainerStyle={styles.scroll}
        showsVerticalScrollIndicator={false}>
        <Text style={styles.brand}>
          Sol<Text style={styles.brandAccent}>Mate</Text>
        </Text>

        <Text style={styles.title}>Inspection</Text>

        <InspectionCard
          icon="clipboard-check-outline"
          title="Request Inspection"
          subtitle="Schedule a site inspection with your details, contact number, and preferred date."
          onPress={() => navigation.navigate('InspectionRequest')}
        />

        <Text style={styles.sectionTitle}>Request History</Text>
        <ShortcutCard
          label="My Inspection Requests"
          subtitle="Review your submitted inspection requests."
          onPress={() => navigation.navigate('InspectionRequestList')}
        />
      </ScrollView>

      <View style={styles.bottomNavShell}>
        <CustomerBottomNav activeTab="Tracking" />
      </View>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safe: {flex: 1, backgroundColor: BG},
  scrollView: {flex: 1},
  scroll: {paddingHorizontal: 20, paddingTop: 18, paddingBottom: 90},
  pressed: {opacity: 0.85},

  brand: {fontSize: 22, fontWeight: '800', color: NAVY, marginBottom: 14},
  brandAccent: {color: GOLD},

  title: {fontSize: 28, fontWeight: '900', color: NAVY, marginBottom: 20},

  serviceCard: {
    backgroundColor: CARD,
    borderRadius: 22,
    borderWidth: 1,
    borderColor: DIVIDER,
    padding: 18,
    marginBottom: 12,
    shadowColor: '#8a9bbd',
    shadowOffset: {width: 0, height: 3},
    shadowOpacity: 0.08,
    shadowRadius: 10,
    elevation: 3,
  },
  serviceCardTop: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 12,
  },
  serviceIconWrap: {
    width: 44,
    height: 44,
    borderRadius: 14,
    backgroundColor: '#E2EBF8',
    borderWidth: 1,
    borderColor: '#C4D4EC',
    alignItems: 'center',
    justifyContent: 'center',
  },
  serviceIcon: {},
  serviceTitle: {
    fontSize: 17,
    fontWeight: '900',
    color: NAVY,
    marginBottom: 5,
  },
  serviceSubtitle: {fontSize: 13, color: MUTED, lineHeight: 19},
  serviceLink: {marginTop: 12, fontSize: 13, fontWeight: '800', color: NAVY},

  sectionTitle: {
    fontSize: 18,
    fontWeight: '800',
    color: NAVY,
    marginBottom: 10,
    marginTop: 4,
  },
  shortcutCard: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    backgroundColor: CARD,
    borderRadius: 18,
    paddingHorizontal: 18,
    paddingVertical: 16,
    marginBottom: 10,
    shadowColor: '#8a9bbd',
    shadowOffset: {width: 0, height: 2},
    shadowOpacity: 0.06,
    shadowRadius: 8,
    elevation: 2,
  },
  shortcutLabel: {
    fontSize: 15,
    fontWeight: '800',
    color: NAVY,
    marginBottom: 2,
  },
  shortcutSubtitle: {fontSize: 13, color: MUTED},
  shortcutChevron: {fontSize: 18, fontWeight: '700', color: '#bcc5d3'},

  bottomNavShell: {
    backgroundColor: BG,
    paddingHorizontal: 20,
    paddingTop: 8,
    paddingBottom: 16,
  },
});
