import React, {useCallback, useContext, useState} from 'react';
import {
  FlatList,
  Image,
  Pressable,
  SafeAreaView,
  ScrollView,
  StyleSheet,
  Text,
  useWindowDimensions,
  View,
} from 'react-native';
import {useFocusEffect} from '@react-navigation/native';

import {AuthContext} from '../src/context/AuthContext';
import CustomerBottomNav from '../src/components/CustomerBottomNav';
import {ApiError} from '../src/services/api';
import {getUnreadNotificationCount} from '../src/services/notificationApi';
import {
  getActivePromotions,
  type Promotion,
} from '../src/services/promotionApi';
import {getProfilePictureUrl, getUserInitial} from '../src/utils/profilePicture';
import Icon from 'react-native-vector-icons/MaterialCommunityIcons';

const NAVY = '#1A2B55';
const GOLD = '#F5C000';
const MUTED = '#6B7A99';
const BG = '#C8D8F0';
const CARD = '#ffffff';
const BORDER = '#D4E0F2';
const CYAN = '#3B8ED4';
const ICON_BG = '#E2EBF8';
const ICON_COLOR = '#1d2f6d';
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
      <Icon name={icon} size={22} color={ICON_COLOR} style={s.summaryIcon} />
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
        <Icon name={icon} size={22} color={ICON_COLOR} />
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
        <Icon name={icon} size={22} color={ICON_COLOR} />
      </View>
      <Text style={s.actionTitle}>{title}</Text>
      <Text style={s.actionSub}>{subtitle}</Text>
    </Pressable>
  );
}

function PromotionCard({promo, width}: {promo: Promotion; width?: number}) {
  return (
    <View style={[s.promoCard, width !== undefined ? {width} : undefined]}>
      {promo.image_url ? (
        <Image source={{uri: promo.image_url}} style={s.promoBanner} />
      ) : (
        <View style={s.promoBannerPlaceholder}>
          <Text style={s.promoBannerPlaceholderIcon}>{'⭐'}</Text>
        </View>
      )}
      <View style={s.promoCardBody}>
        <View style={s.promoTagRow}>
          <Text style={s.promoTag}>
            {promo.end_date
              ? `Ends ${new Date(promo.end_date).toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric'})}`
              : 'Special Offer'}
          </Text>
        </View>
        <Text style={s.promoTitle}>{promo.title}</Text>
        {promo.description ? (
          <Text style={s.promoDesc}>{promo.description}</Text>
        ) : null}
      </View>
    </View>
  );
}

/* ── main screen ────────────────────────────────────────────── */

export default function HomeScreen({navigation}: any) {
  const {user} = useContext(AuthContext);
  const customerName = user?.name || 'Customer';
  const profilePictureUrl = getProfilePictureUrl(user?.profile_picture);
  const {width: SCREEN_W} = useWindowDimensions();
  const PROMO_W = SCREEN_W - 40;
  const [unreadCount, setUnreadCount] = useState(0);
  const [notificationsLoading, setNotificationsLoading] = useState(true);
  const [promotions, setPromotions] = useState<Promotion[]>([]);
  const [promoIndex, setPromoIndex] = useState(0);

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

  const loadPromotions = useCallback(async () => {
    try {
      const data = await getActivePromotions();
      setPromotions(data);
    } catch {
      setPromotions([]);
    }
  }, []);

  useFocusEffect(
    useCallback(() => {
      loadUnreadCount();
      loadPromotions();
    }, [loadUnreadCount, loadPromotions]),
  );

  const initial = getUserInitial(customerName, 'C');

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
            {profilePictureUrl ? (
              <Image source={{uri: profilePictureUrl}} style={s.avatarImage} />
            ) : (
              <Text style={s.avatarText}>{initial}</Text>
            )}
          </Pressable>
        </View>

        {/* ── welcome ────────────────────────────────── */}
        <Text style={s.welcomeTitle}>Welcome back,{' '}
          <Text>{customerName}</Text>
        </Text>
        <Text style={s.welcomeSub}>Your solar overview at a glance.</Text>

        {/* ── promotions ─────────────────────────────── */}
        {promotions.length > 0 && (
          <>
            <View style={s.promoHeaderRow}>
              <Text style={s.sectionTitle}>Special Offers</Text>
              {promotions.length > 1 && (
                <Text style={s.promoCounter}>
                  {promoIndex + 1} / {promotions.length}
                </Text>
              )}
            </View>
            <FlatList
              data={promotions}
              keyExtractor={item => String(item.id)}
              renderItem={({item}) => (
                <PromotionCard promo={item} width={PROMO_W} />
              )}
              horizontal
              pagingEnabled
              showsHorizontalScrollIndicator={false}
              onMomentumScrollEnd={e => {
                const idx = Math.round(
                  e.nativeEvent.contentOffset.x / PROMO_W,
                );
                setPromoIndex(idx);
              }}
              style={s.promoList}
            />
            {promotions.length > 1 && (
              <View style={s.promoDots}>
                {promotions.map((_, i) => (
                  <View
                    key={i}
                    style={[
                      s.promoDot,
                      i === promoIndex && s.promoDotActive,
                    ]}
                  />
                ))}
              </View>
            )}
          </>
        )}

        {/* ── summary ────────────────────────────────── */}
        <Text style={s.sectionTitle}>Summary</Text>
        <View style={s.summaryRow}>
          <SummaryCard
            icon="clipboard-list-outline"
            label="Requests"
            value="Track"
            onPress={() => navigation.navigate('TrackingHub')}
          />
          <SummaryCard
            icon="bell-outline"
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
          icon="hammer-wrench"
          title="Services"
          subtitle={'Installation \u2022 Maintenance'}
          onPress={() => navigation.navigate('ServicesHome')}
        />
        <InfoCard
          icon="clipboard-check-outline"
          title="Inspection"
          subtitle="Request or view inspections"
          onPress={() => navigation.navigate('InspectionHome')}
        />
        <InfoCard
          icon="file-document-outline"
          title="Quotations"
          subtitle={'Pre-Inspection Estimates \u2022 Inspection-Based'}
          onPress={() => navigation.navigate('QuotationList')}
        />

        {/* ── quick actions ──────────────────────────── */}
        <Text style={s.sectionTitle}>Quick Actions</Text>
        <View style={s.actionGrid}>
          <ActionCard
            icon="home-plus-outline"
            title="Request"
            subtitle="Installation"
            onPress={() => navigation.navigate('InstallationRequest')}
          />
          <ActionCard
            icon="clipboard-search-outline"
            title="Request"
            subtitle="Inspection"
            onPress={() => navigation.navigate('InspectionRequest')}
          />
          <ActionCard
            icon="wrench-outline"
            title="Request"
            subtitle="Maintenance"
            onPress={() => navigation.navigate('ServiceRequest')}
          />
          <ActionCard
            icon="star-outline"
            title="Create"
            subtitle="Testimony"
            onPress={() => navigation.navigate('CreateTestimony')}
          />
          <ActionCard
            icon="calculator-variant-outline"
            title="Get"
            subtitle="Estimate"
            onPress={() => navigation.navigate('QuotationEstimate')}
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
            onPress={() => navigation.navigate('TrackingHub')}
            style={({pressed}) => [s.moreBtn, pressed && s.pressed]}>
            <Text style={s.moreBtnText}>My Requests</Text>
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
            <Icon name="robot-outline" size={24} color="#FFFFFF" />
          </View>
        </Pressable>

        {/* ── bottom nav row ─────────────────────────── */}
      </ScrollView>

      <CustomerBottomNav activeTab="Home" />
    </SafeAreaView>
  );
}

/* ── styles ─────────────────────────────────────────────────── */

const s = StyleSheet.create({
  safe: {flex: 1, backgroundColor: BG},
  scroll: {paddingHorizontal: 20, paddingTop: 18, paddingBottom: 90, backgroundColor: BG},
  pressed: {opacity: 0.85},

  /* header */
  header: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 12,
    paddingHorizontal: 16,
    paddingVertical: 14,
    borderRadius: 20,
    backgroundColor: CARD,
    borderWidth: 1,
    borderColor: BORDER,
    shadowColor: '#8a9bbd',
    shadowOffset: {width: 0, height: 8},
    shadowOpacity: 0.08,
    shadowRadius: 18,
    elevation: 3,
  },
  brandRow: {flexDirection: 'row'},
  brandSol: {fontSize: 22, fontWeight: '800', color: NAVY},
  brandMate: {fontSize: 22, fontWeight: '800', color: GOLD},
  avatar: {
    width: 42,
    height: 42,
    borderRadius: 21,
    backgroundColor: NAVY,
    borderWidth: 2,
    borderColor: CYAN,
    alignItems: 'center',
    justifyContent: 'center',
    overflow: 'hidden',
    shadowColor: NAVY,
    shadowOffset: {width: 0, height: 3},
    shadowOpacity: 0.25,
    shadowRadius: 6,
    elevation: 4,
  },
  avatarImage: {
    width: '100%',
    height: '100%',
    borderRadius: 21,
  },
  avatarText: {color: '#FFFFFF', fontSize: 17, fontWeight: '700'},

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
    borderLeftWidth: 3,
    borderLeftColor: NAVY,
    shadowColor: '#7A9BBD',
    shadowOffset: {width: 0, height: 5},
    shadowOpacity: 0.18,
    shadowRadius: 12,
    elevation: 5,
  },
  summaryIcon: {marginBottom: 8},
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
    width: 44,
    height: 44,
    borderRadius: 14,
    backgroundColor: ICON_BG,
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: 14,
    borderWidth: 1,
    borderColor: '#C4D4EC',
  },
  infoIcon: {},
  infoTextWrap: {flex: 1},
  infoTitle: {fontSize: 15, fontWeight: '800', color: NAVY, marginBottom: 3},
  infoSub: {fontSize: 13, color: MUTED},
  chevron: {fontSize: 18, color: CYAN, fontWeight: '700'},

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
    width: 40,
    height: 40,
    borderRadius: 12,
    backgroundColor: ICON_BG,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 10,
    borderWidth: 1,
    borderColor: '#C4D4EC',
  },
  actionIcon: {},
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
    width: 50,
    height: 50,
    borderRadius: 25,
    backgroundColor: NAVY,
    alignItems: 'center',
    justifyContent: 'center',
    borderWidth: 2,
    borderColor: CYAN,
    shadowColor: NAVY,
    shadowOffset: {width: 0, height: 5},
    shadowOpacity: 0.35,
    shadowRadius: 10,
    elevation: 7,
  },
  chatBtnIcon: {},

  /* promotion cards */
  promoHeaderRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginBottom: 12,
    marginTop: 6,
  },
  promoCounter: {
    fontSize: 13,
    fontWeight: '600',
    color: MUTED,
  },
  promoList: {
    marginBottom: 10,
  },
  promoDots: {
    flexDirection: 'row',
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 14,
    gap: 6,
  },
  promoDot: {
    width: 7,
    height: 7,
    borderRadius: 999,
    backgroundColor: BORDER,
  },
  promoDotActive: {
    backgroundColor: NAVY,
    width: 18,
  },
  promoCard: {
    backgroundColor: CARD,
    borderRadius: R,
    overflow: 'hidden',
    marginBottom: 4,
    shadowColor: '#8a9bbd',
    shadowOffset: {width: 0, height: 6},
    shadowOpacity: 0.1,
    shadowRadius: 14,
    elevation: 4,
    borderWidth: 1,
    borderColor: BORDER,
  },
  promoBanner: {
    width: '100%',
    aspectRatio: 16 / 7,
    backgroundColor: '#D4E0F2',
  },
  promoBannerPlaceholder: {
    width: '100%',
    aspectRatio: 16 / 7,
    backgroundColor: NAVY,
    alignItems: 'center',
    justifyContent: 'center',
  },
  promoBannerPlaceholderIcon: {fontSize: 28},
  promoCardBody: {padding: 16, gap: 8},
  promoTagRow: {flexDirection: 'row'},
  promoTag: {
    fontSize: 11,
    fontWeight: '700',
    color: '#7A4F00',
    backgroundColor: '#FFF0A0',
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 999,
    overflow: 'hidden',
    textTransform: 'uppercase',
    letterSpacing: 0.5,
  },
  promoTitle: {fontSize: 17, fontWeight: '800', color: NAVY, lineHeight: 22},
  promoDesc: {fontSize: 13, color: MUTED, lineHeight: 19},
});
