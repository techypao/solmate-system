import React from 'react';
import {
  Pressable,
  SafeAreaView,
  ScrollView,
  StyleSheet,
  Text,
  View,
} from 'react-native';
import Icon from 'react-native-vector-icons/MaterialCommunityIcons';

import CustomerBottomNav from '../src/components/CustomerBottomNav';

const ICON_COLOR = '#1d2f6d';
const NAVY = '#1A2B55';
const GOLD = '#F5C000';
const MUTED = '#6B7A99';
const BG = '#C8D8F0';
const CARD = '#ffffff';
const BORDER = '#D4E0F2';

type QuotationHubCardProps = {
  icon: string;
  title: string;
  subtitle: string;
  onPress: () => void;
};

function QuotationHubCard({
  icon,
  title,
  subtitle,
  onPress,
}: QuotationHubCardProps) {
  return (
    <Pressable
      onPress={onPress}
      style={({pressed}) => [styles.card, pressed && styles.pressed]}>
      <View style={styles.cardAccent} />
      <View style={styles.cardInner}>
        <View style={styles.iconWrap}>
          <Icon name={icon} size={26} color={ICON_COLOR} />
        </View>
        <View style={styles.cardText}>
          <Text style={styles.cardTitle}>{title}</Text>
          <Text style={styles.cardSub}>{subtitle}</Text>
        </View>
        <Text style={styles.chevron}>{'›'}</Text>
      </View>
    </Pressable>
  );
}

export default function QuotationHubScreen({navigation}: any) {
  return (
    <SafeAreaView style={styles.safe}>
      <ScrollView
        contentContainerStyle={styles.scroll}
        showsVerticalScrollIndicator={false}>
        <View style={styles.topBar}>
          <Text style={styles.brand}>
            Sol<Text style={styles.brandAccent}>Mate</Text>
          </Text>
          <Pressable
            onPress={() => navigation.goBack()}
            style={({pressed}) => [styles.backBtn, pressed && styles.pressed]}>
            <Text style={styles.backIcon}>{'‹'}</Text>
          </Pressable>
        </View>

        <Text style={styles.title}>Quotations</Text>
        <Text style={styles.subtitle}>
          Create a pre-inspection quotation request or review quotations prepared after technician inspections.
        </Text>

        <QuotationHubCard
          icon="file-document-edit-outline"
          title="Pre-Inspection Quotation"
          subtitle="Create a quotation request before scheduling an inspection."
          onPress={() => navigation.navigate('QuotationEstimate')}
        />
        <QuotationHubCard
          icon="clipboard-text-search-outline"
          title="Inspection-Based Quotations"
          subtitle="View quotations prepared by technicians after property inspections."
          onPress={() =>
            navigation.navigate('QuotationList', {
              initialFilter: 'final',
              lockFilter: true,
            })
          }
        />
      </ScrollView>

      <CustomerBottomNav activeTab="Quotations" />
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safe: {flex: 1, backgroundColor: BG},
  scroll: {paddingHorizontal: 22, paddingBottom: 90, paddingTop: 8},
  pressed: {opacity: 0.85},

  topBar: {marginBottom: 10},
  brand: {fontSize: 22, fontWeight: '800', color: NAVY, marginBottom: 10},
  brandAccent: {color: GOLD},
  backBtn: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: CARD,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 18,
    shadowColor: '#8a9bbd',
    shadowOffset: {width: 0, height: 2},
    shadowOpacity: 0.1,
    shadowRadius: 6,
    elevation: 3,
  },
  backIcon: {fontSize: 28, color: NAVY, fontWeight: '600', marginTop: -2},

  title: {fontSize: 26, fontWeight: '900', color: NAVY, marginBottom: 4},
  subtitle: {
    fontSize: 14,
    color: MUTED,
    lineHeight: 20,
    marginBottom: 24,
  },

  card: {
    backgroundColor: CARD,
    borderColor: BORDER,
    borderRadius: 22,
    borderWidth: 1,
    marginBottom: 14,
    overflow: 'hidden',
    shadowColor: '#8a9bbd',
    shadowOffset: {width: 0, height: 4},
    shadowOpacity: 0.1,
    shadowRadius: 14,
    elevation: 4,
  },
  cardAccent: {
    backgroundColor: GOLD,
    height: 4,
    width: '100%',
  },
  cardInner: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 14,
    padding: 18,
  },
  iconWrap: {
    width: 52,
    height: 52,
    borderRadius: 16,
    backgroundColor: '#E2EBF8',
    borderWidth: 1,
    borderColor: '#C4D4EC',
    alignItems: 'center',
    justifyContent: 'center',
    flexShrink: 0,
  },
  cardText: {flex: 1},
  cardTitle: {
    fontSize: 16,
    fontWeight: '800',
    color: NAVY,
    marginBottom: 4,
  },
  cardSub: {fontSize: 13, color: MUTED, lineHeight: 18},
  chevron: {fontSize: 24, color: GOLD, fontWeight: '700'},
});
