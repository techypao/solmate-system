import React from 'react';
import {
  Pressable,
  SafeAreaView,
  ScrollView,
  StyleSheet,
  Text,
  View,
} from 'react-native';

/* ── design tokens ── */
const NAVY = '#152a4a';
const GOLD = '#e8a800';
const MUTED = '#7b8699';
const BG = '#e0e8f5';
const CARD = '#ffffff';
const DIVIDER = '#edf1f7';

/* ── tracking categories ── */
const CATEGORIES = [
  {
    key: 'inspection',
    icon: '🔍',
    title: 'Inspection Requests',
    subtitle: 'Track site inspection visits and final quotation status.',
    route: 'InspectionRequestList',
    params: undefined,
  },
  {
    key: 'installation',
    icon: '🔧',
    title: 'Installation Requests',
    subtitle: 'Monitor your solar panel installation progress.',
    route: 'ServiceRequestList',
    params: {requestCategory: 'installation'},
  },
  {
    key: 'maintenance',
    icon: '⚙️',
    title: 'Maintenance Requests',
    subtitle: 'Check status updates for maintenance and repair jobs.',
    route: 'ServiceRequestList',
    params: {requestCategory: 'maintenance'},
  },
];

/* ════════════════════════════════════════════
   Main screen
   ════════════════════════════════════════════ */
export default function TrackingHubScreen({navigation}: any) {
  return (
    <SafeAreaView style={s.safe}>
      <ScrollView
        contentContainerStyle={s.scroll}
        showsVerticalScrollIndicator={false}>

        {/* ── top bar ── */}
        <View style={s.topBar}>
          <Text style={s.brand}>
            Sol<Text style={s.brandAccent}>Mate</Text>
          </Text>
          <Pressable
            onPress={() => navigation.goBack()}
            style={({pressed}) => [s.backBtn, pressed && s.pressed]}>
            <Text style={s.backIcon}>‹</Text>
          </Pressable>
        </View>

        {/* ── heading ── */}
        <Text style={s.title}>Tracking</Text>
        <Text style={s.subtitle}>
          Select a category to review your request status and updates.
        </Text>

        {/* ── category cards ── */}
        {CATEGORIES.map(cat => (
          <Pressable
            key={cat.key}
            onPress={() => navigation.navigate(cat.route, cat.params)}
            style={({pressed}) => [s.card, pressed && s.pressed]}>
            <View style={s.cardAccent} />
            <View style={s.cardInner}>
              <View style={s.iconWrap}>
                <Text style={s.iconText}>{cat.icon}</Text>
              </View>
              <View style={s.cardText}>
                <Text style={s.cardTitle}>{cat.title}</Text>
                <Text style={s.cardSub}>{cat.subtitle}</Text>
              </View>
              <Text style={s.chevron}>›</Text>
            </View>
          </Pressable>
        ))}
      </ScrollView>

      {/* ── bottom nav ── */}
      <View style={s.bottomNav}>
        <Pressable
          style={s.navItem}
          onPress={() => navigation.navigate('Home')}>
          <Text style={s.navIcon}>🏠</Text>
          <Text style={s.navLabel}>Home</Text>
        </Pressable>
        <Pressable
          style={s.navItem}
          onPress={() => navigation.navigate('QuotationList')}>
          <Text style={s.navIcon}>📋</Text>
          <Text style={s.navLabel}>Quotation</Text>
        </Pressable>
        <Pressable
          style={s.navItem}
          onPress={() => navigation.navigate('ServicesHome')}>
          <Text style={s.navIcon}>⚙️</Text>
          <Text style={s.navLabel}>Services</Text>
        </Pressable>
        <Pressable style={s.navItem} onPress={() => {}}>
          <Text style={s.navIconActive}>📍</Text>
          <Text style={s.navLabelActive}>Tracking</Text>
        </Pressable>
        <Pressable
          style={s.navItem}
          onPress={() => navigation.navigate('CustomerSettings')}>
          <Text style={s.navIcon}>👤</Text>
          <Text style={s.navLabel}>Profile</Text>
        </Pressable>
      </View>
    </SafeAreaView>
  );
}

/* ── styles ── */
const s = StyleSheet.create({
  safe: {flex: 1, backgroundColor: BG},
  scroll: {paddingHorizontal: 22, paddingBottom: 24, paddingTop: 8},

  /* top bar */
  topBar: {marginBottom: 10},
  brand: {fontSize: 22, fontWeight: '800', color: NAVY, marginBottom: 10},
  brandAccent: {color: GOLD},
  backBtn: {
    width: 40, height: 40, borderRadius: 20,
    backgroundColor: CARD,
    alignItems: 'center', justifyContent: 'center',
    marginBottom: 18,
    shadowColor: '#8a9bbd', shadowOffset: {width: 0, height: 2},
    shadowOpacity: 0.10, shadowRadius: 6, elevation: 3,
  },
  backIcon: {fontSize: 28, color: NAVY, fontWeight: '600', marginTop: -2},

  /* heading */
  title: {fontSize: 26, fontWeight: '900', color: NAVY, marginBottom: 4},
  subtitle: {fontSize: 14, color: MUTED, lineHeight: 20, marginBottom: 24},

  /* cards */
  card: {
    backgroundColor: CARD,
    borderRadius: 22,
    marginBottom: 14,
    overflow: 'hidden',
    shadowColor: '#8a9bbd',
    shadowOffset: {width: 0, height: 4},
    shadowOpacity: 0.10, shadowRadius: 14, elevation: 4,
  },
  cardAccent: {
    backgroundColor: GOLD,
    height: 4,
    width: '100%',
  },
  cardInner: {
    flexDirection: 'row',
    alignItems: 'center',
    padding: 18,
    gap: 14,
  },
  iconWrap: {
    width: 52, height: 52,
    borderRadius: 16,
    backgroundColor: '#f0f4fc',
    alignItems: 'center', justifyContent: 'center',
    flexShrink: 0,
  },
  iconText: {fontSize: 26},
  cardText: {flex: 1},
  cardTitle: {fontSize: 16, fontWeight: '800', color: NAVY, marginBottom: 4},
  cardSub: {fontSize: 13, color: MUTED, lineHeight: 18},
  chevron: {fontSize: 24, color: GOLD, fontWeight: '700'},

  /* bottom nav */
  bottomNav: {
    flexDirection: 'row',
    backgroundColor: CARD,
    borderTopWidth: 1,
    borderTopColor: DIVIDER,
    paddingVertical: 10,
    paddingHorizontal: 8,
  },
  navItem: {flex: 1, alignItems: 'center', gap: 3},
  navIcon: {fontSize: 22},
  navLabel: {fontSize: 10, color: MUTED, fontWeight: '600'},
  navIconActive: {fontSize: 22},
  navLabelActive: {fontSize: 10, color: GOLD, fontWeight: '800'},

  /* misc */
  pressed: {opacity: 0.75},
});
