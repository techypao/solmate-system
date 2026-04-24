import React, {useCallback, useContext, useState} from 'react';
import {
  ActivityIndicator,
  Pressable,
  SafeAreaView,
  ScrollView,
  StyleSheet,
  Text,
  View,
} from 'react-native';
import {useFocusEffect} from '@react-navigation/native';

import {AuthContext} from '../src/context/AuthContext';
import {ApiError} from '../src/services/api';
import {getUnreadNotificationCount} from '../src/services/notificationApi';

const NAVY = '#152a4a';
const GOLD = '#e8a800';
const MUTED = '#7b8699';
const BG = '#e0e8f5';
const CARD = '#ffffff';
const R = 18;

/* ── tiny presentational helpers ────────────────────────────── */

function SummaryCard({
  icon,
  label,
  value,
  onPress,
}: {
  icon: string;
  label: string;
  value: string;
  onPress?: () => void;
}) {
  return (
    <Pressable
      onPress={onPress}
      style={({pressed}) => [s.summaryCard, pressed && onPress && s.pressed]}>
      <Text style={s.summaryIcon}>{icon}</Text>
      <Text style={s.summaryLabel}>{label}</Text>
      <Text style={s.summaryValue}>{value}</Text>
    </Pressable>
  );
}

function InfoCard({
  icon,
  title,
  subtitle,
  onPress,
}: {
  icon: string;
  title: string;
  subtitle: string;
  onPress?: () => void;
}) {
  return (
    <Pressable
      onPress={onPress}
      style={({pressed}) => [s.infoCard, pressed && onPress && s.pressed]}>
      <View style={s.infoIconWrap}>
        <Text style={s.infoIcon}>{icon}</Text>
      </View>
      <View style={s.infoTextWrap}>
        <Text style={s.infoTitle}>{title}</Text>
        <Text style={s.infoSub}>{subtitle}</Text>
      </View>
      <Text style={s.chevron}>{'>'}</Text>
    </Pressable>
  );
}

function ActionCard({
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
      style={({pressed}) => [s.actionCard, pressed && s.pressed]}>
      <View style={s.actionIconWrap}>
        <Text style={s.actionIcon}>{icon}</Text>
      </View>
      <Text style={s.actionTitle}>{title}</Text>
      <Text style={s.actionSub}>{subtitle}</Text>
    </Pressable>
  );
}

/* ── main screen ────────────────────────────────────────────── */

export default function HomeScreen({navigation}: any) {
  const {user} = useContext(AuthContext);
  const customerName = user?.name || 'Customer';
  const [unreadCount, setUnreadCount] = useState(0);
  const [notificationsLoading, setNotificationsLoading] = useState(true);

  const loadUnreadCount = useCallback(async () => {
    try {
      setNotificationsLoading(true);
      const count = await getUnreadNotificationCount();
      setUnreadCount(count);
    } catch (error) {
      if (__DEV__ && error instanceof ApiError) {
        console.log('Unread notification count error:', error.message);
      }
      setUnreadCount(0);
    } finally {
      setNotificationsLoading(false);
    }
  }, []);

  useFocusEffect(
    useCallback(() => {
      loadUnreadCount();
    }, [loadUnreadCount]),
  );

  const initial = customerName.charAt(0).toUpperCase();

  return (
    <SafeAreaView style={s.safe}>
      <ScrollView
        contentContainerStyle={s.scroll}
        showsVerticalScrollIndicator={false}>
        {/* ── header ─────────────────────────────────── */}
        <View style={s.header}>
          <View>
            <View style={s.brandRow}>
              <Text style={s.brandSol}>Sol</Text>
              <Text style={s.brandMate}>Mate</Text>
            </View>
          </View>
          <Pressable
            onPress={() => navigation.navigate('CustomerSettings')}
            style={s.avatar}>
            <Text style={s.avatarText}>{initial}</Text>
          </Pressable>
        </View>

        {/* ── welcome ────────────────────────────────── */}
        <Text style={s.welcomeTitle}>Welcome back,{' '}
          <Text>{customerName}</Text>
        </Text>
        <Text style={s.welcomeSub}>Your solar overview at a glance.</Text>

        {/* ── summary ────────────────────────────────── */}
        <Text style={s.sectionTitle}>Summary</Text>
        <View style={s.summaryRow}>
          <SummaryCard
            icon={'\ud83d\udccb'}
            label="Latest Quote"
            value="Ready"
            onPress={() => navigation.navigate('QuotationList')}
          />
          <SummaryCard
            icon={'\ud83d\udcca'}
            label="Notifications"
            value={
              notificationsLoading
                ? '...'
                : unreadCount > 0
                ? unreadCount + ' unread'
                : 'All read'
            }
            onPress={() => navigation.navigate('CustomerNotifications')}
          />
        </View>

        {/* ── info cards ─────────────────────────────── */}
        <InfoCard
          icon={'\ud83d\udee0'}
          title="Services"
          subtitle={'Installation \u2022 Maintenance'}
          onPress={() => navigation.navigate('ServicesHome')}
        />
        <InfoCard
          icon={'\u2705'}
          title="Inspection"
          subtitle="Request or view inspections"
          onPress={() => navigation.navigate('InspectionHome')}
        />

        {/* ── quick actions ──────────────────────────── */}
        <Text style={s.sectionTitle}>Quick Actions</Text>
        <View style={s.actionGrid}>
          <ActionCard
            icon={'\ud83d\udcdd'}
            title="Generate"
            subtitle="Quotation"
            onPress={() => navigation.navigate('Quotations')}
          />
          <ActionCard
            icon={'\ud83e\uddf0'}
            title="Request"
            subtitle="Installation"
            onPress={() => navigation.navigate('InstallationRequest')}
          />
          <ActionCard
            icon={'\u2705'}
            title="Request"
            subtitle="Inspection"
            onPress={() => navigation.navigate('InspectionRequest')}
          />
          <ActionCard
            icon={'\ud83d\udee0'}
            title="Request"
            subtitle="Maintenance"
            onPress={() => navigation.navigate('ServiceRequest')}
          />
          <ActionCard
            icon={'\u2b50'}
            title="Create"
            subtitle="Testimony"
            onPress={() => navigation.navigate('CreateTestimony')}
          />
        </View>

        {/* ── more actions ───────────────────────────── */}
        <View style={s.moreRow}>
          <Pressable
            onPress={() => navigation.navigate('MyTestimonies')}
            style={({pressed}) => [s.moreBtn, pressed && s.pressed]}>
            <Text style={s.moreBtnText}>My Testimonies</Text>
          </Pressable>
          <Pressable
            onPress={() => navigation.navigate('QuotationList')}
            style={({pressed}) => [s.moreBtn, pressed && s.pressed]}>
            <Text style={s.moreBtnText}>My Quotations</Text>
          </Pressable>
        </View>

        {/* ── chatbot shortcut ───────────────────────── */}
        <Pressable
          onPress={() => navigation.navigate('Chatbot')}
          style={({pressed}) => [s.chatRow, pressed && s.pressed]}>
          <Text style={s.chatText}>Need help? Chat with SolBot</Text>
          <View style={s.chatBtn}>
            <Text style={s.chatBtnIcon}>{'\ud83e\udd16'}</Text>
          </View>
        </Pressable>

        {/* ── bottom nav row ─────────────────────────── */}
        <View style={s.bottomNav}>
          <Pressable style={s.navItem} onPress={() => {}}>
            <Text style={s.navIconActive}>{'\ud83c\udfe0'}</Text>
            <Text style={s.navLabelActive}>Home</Text>
          </Pressable>
          <Pressable
            style={s.navItem}
            onPress={() => navigation.navigate('QuotationList')}>
            <Text style={s.navIcon}>{'\ud83d\udccb'}</Text>
            <Text style={s.navLabel}>Quotation</Text>
          </Pressable>
          <Pressable
            style={s.navItem}
            onPress={() => navigation.navigate('ServicesHome')}>
            <Text style={s.navIcon}>{'\u2699\ufe0f'}</Text>
            <Text style={s.navLabel}>Services</Text>
          </Pressable>
          <Pressable
            style={s.navItem}
            onPress={() => navigation.navigate('TrackingHub')}>
            <Text style={s.navIcon}>{'\ud83d\udccd'}</Text>
            <Text style={s.navLabel}>Tracking</Text>
          </Pressable>
          <Pressable
            style={s.navItem}
            onPress={() => navigation.navigate('CustomerSettings')}>
            <Text style={s.navIcon}>{'\ud83d\udc64'}</Text>
            <Text style={s.navLabel}>Profile</Text>
          </Pressable>
        </View>
      </ScrollView>
    </SafeAreaView>
  );
}

/* ── styles ─────────────────────────────────────────────────── */

const s = StyleSheet.create({
  safe: {flex: 1, backgroundColor: BG},
  scroll: {paddingHorizontal: 20, paddingTop: 18, paddingBottom: 30},
  pressed: {opacity: 0.85},

  /* header */
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 10,
  },
  brandRow: {flexDirection: 'row'},
  brandSol: {fontSize: 22, fontWeight: '800', color: NAVY},
  brandMate: {fontSize: 22, fontWeight: '800', color: GOLD},
  avatar: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: NAVY,
    alignItems: 'center',
    justifyContent: 'center',
  },
  avatarText: {color: '#fff', fontSize: 17, fontWeight: '700'},

  /* welcome */
  welcomeTitle: {
    fontSize: 26,
    fontWeight: '800',
    color: NAVY,
    marginBottom: 4,
  },
  welcomeSub: {fontSize: 14, color: MUTED, marginBottom: 18},

  /* section */
  sectionTitle: {
    fontSize: 18,
    fontWeight: '800',
    color: NAVY,
    marginBottom: 12,
    marginTop: 6,
  },

  /* summary */
  summaryRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 14,
  },
  summaryCard: {
    flex: 1,
    backgroundColor: CARD,
    borderRadius: R,
    padding: 16,
    marginHorizontal: 4,
    shadowColor: '#8a9bbd',
    shadowOffset: {width: 0, height: 4},
    shadowOpacity: 0.1,
    shadowRadius: 10,
    elevation: 4,
  },
  summaryIcon: {fontSize: 18, marginBottom: 8},
  summaryLabel: {fontSize: 13, color: MUTED, marginBottom: 4},
  summaryValue: {fontSize: 16, fontWeight: '800', color: NAVY},

  /* info card */
  infoCard: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: CARD,
    borderRadius: R,
    padding: 16,
    marginBottom: 10,
    shadowColor: '#8a9bbd',
    shadowOffset: {width: 0, height: 4},
    shadowOpacity: 0.08,
    shadowRadius: 10,
    elevation: 3,
  },
  infoIconWrap: {
    width: 42,
    height: 42,
    borderRadius: 14,
    backgroundColor: '#eaf0fb',
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: 14,
  },
  infoIcon: {fontSize: 20},
  infoTextWrap: {flex: 1},
  infoTitle: {fontSize: 15, fontWeight: '800', color: NAVY, marginBottom: 3},
  infoSub: {fontSize: 13, color: MUTED},
  chevron: {fontSize: 18, color: '#bcc5d3', fontWeight: '600'},

  /* action grid */
  actionGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    justifyContent: 'space-between',
    marginBottom: 14,
  },
  actionCard: {
    width: '48%',
    backgroundColor: CARD,
    borderRadius: R,
    padding: 16,
    marginBottom: 12,
    shadowColor: '#8a9bbd',
    shadowOffset: {width: 0, height: 3},
    shadowOpacity: 0.08,
    shadowRadius: 8,
    elevation: 3,
  },
  actionIconWrap: {
    width: 38,
    height: 38,
    borderRadius: 12,
    backgroundColor: '#eaf0fb',
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 10,
  },
  actionIcon: {fontSize: 18},
  actionTitle: {fontSize: 15, fontWeight: '800', color: NAVY, marginBottom: 2},
  actionSub: {fontSize: 13, color: MUTED},

  /* more actions */
  moreRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 14,
  },
  moreBtn: {
    flex: 1,
    backgroundColor: CARD,
    borderRadius: R,
    paddingVertical: 14,
    marginHorizontal: 4,
    alignItems: 'center',
    shadowColor: '#8a9bbd',
    shadowOffset: {width: 0, height: 2},
    shadowOpacity: 0.06,
    shadowRadius: 6,
    elevation: 2,
  },
  moreBtnText: {fontSize: 13, fontWeight: '700', color: NAVY},

  /* chat shortcut */
  chatRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'flex-end',
    marginBottom: 22,
    marginTop: 4,
  },
  chatText: {fontSize: 13, color: MUTED, marginRight: 10},
  chatBtn: {
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
  chatBtnIcon: {fontSize: 22},

  /* bottom nav */
  bottomNav: {
    flexDirection: 'row',
    justifyContent: 'space-around',
    backgroundColor: CARD,
    borderRadius: R,
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
