import React, {useCallback, useState} from 'react';
import {
  ActivityIndicator,
  Alert,
  FlatList,
  Pressable,
  RefreshControl,
  SafeAreaView,
  StyleSheet,
  Text,
  View,
} from 'react-native';
import {useFocusEffect} from '@react-navigation/native';

import {ApiError} from '../src/services/api';
import {
  AppNotification,
  getNotifications,
  markAllNotificationsAsRead,
  markNotificationAsRead,
} from '../src/services/notificationApi';
import {
  CUSTOMER_NOTIFICATION_ROUTE_NAMES,
  getCustomerNotificationNavigationTarget,
} from '../src/utils/notificationNavigation';

/* ── design tokens ── */

const NAVY = '#152a4a';
const GOLD = '#e8a800';
const MUTED = '#7b8699';
const BG = '#e0e8f5';
const CARD = '#ffffff';
const DIVIDER = '#edf1f7';

/* ── helpers (preserved) ── */

function formatNotificationDate(notification: AppNotification) {
  if (notification.created_at_display) {
    return notification.created_at_display;
  }

  if (!notification.created_at) {
    return 'Just now';
  }

  const parsedDate = new Date(notification.created_at);

  if (Number.isNaN(parsedDate.getTime())) {
    return notification.created_at;
  }

  return parsedDate.toLocaleString();
}

function getFriendlyErrorMessage(error: unknown) {
  if (error instanceof ApiError) {
    if (error.status === 401) {
      return 'Your session has expired. Please log in again.';
    }

    return error.message;
  }

  return 'Could not load your notifications right now.';
}

function getOpenNotificationErrorMessage(error: unknown) {
  if (error instanceof ApiError) {
    if (error.status === 401) {
      return 'Your session has expired. Please log in again.';
    }

    if (error.status === 404) {
      return 'This notification could not be updated on the server anymore.';
    }

    return error.message;
  }

  if (error instanceof Error && error.message.trim()) {
    return error.message;
  }

  return 'This notification could not be opened right now.';
}

function getMarkAllReadErrorMessage(error: unknown) {
  if (error instanceof ApiError) {
    if (error.status === 401) {
      return 'Your session has expired. Please log in again.';
    }

    if (error.status === 405) {
      return 'The server rejected the mark-all-read request method.';
    }

    if (error.status === 404) {
      return 'The mark-all-read endpoint could not be found.';
    }

    return error.message || 'Could not mark all notifications as read.';
  }

  if (error instanceof Error && error.message.trim()) {
    return error.message;
  }

  return 'Could not mark all notifications as read.';
}

function formatTypeLabel(type?: string | null) {
  return String(type || 'general')
    .replace(/_/g, ' ')
    .replace(/\b\w/g, c => c.toUpperCase());
}

/* ── component ── */

export default function CustomerNotificationsScreen({navigation}: any) {
  const [notifications, setNotifications] = useState<AppNotification[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [errorMessage, setErrorMessage] = useState('');
  const [markingAllRead, setMarkingAllRead] = useState(false);

  const loadNotifications = useCallback(async (showLoadingState = false) => {
    try {
      if (showLoadingState) {
        setLoading(true);
      }

      setErrorMessage('');
      const data = await getNotifications();
      setNotifications(Array.isArray(data) ? data : []);
    } catch (error) {
      setNotifications([]);
      setErrorMessage(getFriendlyErrorMessage(error));
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, []);

  useFocusEffect(
    useCallback(() => {
      loadNotifications(true);
    }, [loadNotifications]),
  );

  const handleRefresh = () => {
    setRefreshing(true);
    loadNotifications(false);
  };

  const isNotificationRead = useCallback((notification: AppNotification) => {
    return notification.is_read === true || !!notification.read_at;
  }, []);

  const navigateToNotificationTarget = useCallback(
    (notification: AppNotification) => {
      const target = getCustomerNotificationNavigationTarget(notification);

      if (!target) {
        navigation.navigate('Home');
        return;
      }

      const availableRouteNames = navigation.getState?.()?.routeNames;
      const allowedRouteNames = Array.isArray(availableRouteNames)
        ? availableRouteNames
        : CUSTOMER_NOTIFICATION_ROUTE_NAMES;

      if (!allowedRouteNames.includes(target.routeName)) {
        navigation.navigate('Home');
        return;
      }

      navigation.navigate(target.routeName, target.params);
    },
    [navigation],
  );

  const handleNotificationPress = async (notification: AppNotification) => {
    try {
      let notificationToOpen = notification;

      if (!isNotificationRead(notification)) {
        const updatedNotification = await markNotificationAsRead(notification.id);

        if (!updatedNotification) {
          throw new Error('The server did not return the updated notification.');
        }

        notificationToOpen = {
          ...notification,
          ...updatedNotification,
          is_read: true,
          read_at:
            updatedNotification.read_at ||
            notification.read_at ||
            new Date().toISOString(),
        };

        setNotifications(currentNotifications =>
          currentNotifications.map(currentNotification =>
            currentNotification.id === notification.id
              ? notificationToOpen
              : currentNotification,
          ),
        );
      }

      navigateToNotificationTarget(notificationToOpen);
    } catch (error) {
      Alert.alert(
        'Could not open notification',
        getOpenNotificationErrorMessage(error),
      );
    }
  };

  const handleMarkAllRead = async () => {
    try {
      setMarkingAllRead(true);
      await markAllNotificationsAsRead();
      await loadNotifications(false);
    } catch (error) {
      Alert.alert(
        'Mark all as read failed',
        getMarkAllReadErrorMessage(error),
      );
    } finally {
      setMarkingAllRead(false);
    }
  };

  const unreadCount = notifications.filter(
    notification => !isNotificationRead(notification),
  ).length;

  /* ── notification card ── */
  const renderNotification = ({item}: {item: AppNotification}) => {
    const isRead = isNotificationRead(item);

    return (
      <Pressable
        onPress={() => handleNotificationPress(item)}
        style={({pressed}) => [
          s.card,
          !isRead && s.cardUnread,
          pressed && s.cardPressed,
        ]}>
        {/* header row */}
        <View style={s.cardHeader}>
          <View style={s.cardTitleWrap}>
            {!isRead && <View style={s.unreadDot} />}
            <Text style={[s.cardTitle, !isRead && s.cardTitleUnread]} numberOfLines={2}>
              {item.title || 'Notification'}
            </Text>
          </View>
          <Text style={s.cardDate}>{formatNotificationDate(item)}</Text>
        </View>

        {/* body */}
        <Text style={s.cardMessage} numberOfLines={3}>
          {item.message || 'Open this notification to view more details.'}
        </Text>

        {/* footer */}
        <View style={s.cardDivider} />
        <View style={s.cardFooter}>
          <View
            style={[
              s.typeBadge,
              {backgroundColor: isRead ? DIVIDER : '#dce6f8'},
            ]}>
            <Text style={[s.typeBadgeText, {color: isRead ? MUTED : NAVY}]}>
              {formatTypeLabel(item.type)}
            </Text>
          </View>
          <Text style={[s.readLabel, isRead ? s.readLabelRead : s.readLabelUnread]}>
            {isRead ? 'Read' : 'Unread'}
          </Text>
        </View>
      </Pressable>
    );
  };

  /* ── loading state ── */
  if (loading) {
    return (
      <SafeAreaView style={s.loadingContainer}>
        <View style={s.loadingBody}>
          <ActivityIndicator color={GOLD} size="large" />
          <Text style={s.loadingText}>Loading notifications\u2026</Text>
        </View>
      </SafeAreaView>
    );
  }

  /* ── main render ── */
  return (
    <SafeAreaView style={s.safe}>
      {/* back button */}
      <View style={s.topRow}>
        <Pressable
          onPress={() => navigation.goBack()}
          hitSlop={12}
          style={({pressed}) => [{opacity: pressed ? 0.55 : 1}]}>
          <Text style={s.backBtn}>{'\u2039'} Back</Text>
        </Pressable>
      </View>

      {/* page intro */}
      <View style={s.introSection}>
        <Text style={s.pageTitle}>Notifications</Text>
        <Text style={s.pageSubtitle}>
          Stay updated on your requests, schedules, and final quotations.
        </Text>
      </View>

      {/* summary / action card */}
      <View style={s.summaryCard}>
        <View style={s.summaryLeft}>
          <Text style={s.summaryLabel}>Unread</Text>
          <Text style={s.summaryValue}>{unreadCount}</Text>
        </View>
        <Pressable
          onPress={handleMarkAllRead}
          disabled={markingAllRead || unreadCount === 0}
          style={({pressed}) => [
            s.markAllBtn,
            (markingAllRead || unreadCount === 0) && s.markAllBtnDisabled,
            pressed && {opacity: 0.7},
          ]}>
          {markingAllRead ? (
            <ActivityIndicator color={GOLD} size="small" />
          ) : (
            <Text
              style={[
                s.markAllBtnText,
                (markingAllRead || unreadCount === 0) && s.markAllBtnTextDisabled,
              ]}>
              Mark all as read
            </Text>
          )}
        </Pressable>
      </View>

      {/* error state */}
      {errorMessage ? (
        <View style={s.errorCard}>
          <Text style={s.errorTitle}>Something went wrong</Text>
          <Text style={s.errorText}>{errorMessage}</Text>
          <Pressable
            onPress={() => loadNotifications(true)}
            style={({pressed}) => [s.retryBtn, pressed && {opacity: 0.7}]}>
            <Text style={s.retryBtnText}>Try again</Text>
          </Pressable>
        </View>
      ) : (
        <FlatList
          contentContainerStyle={[
            s.listContent,
            notifications.length === 0 && s.emptyListContent,
          ]}
          data={notifications}
          keyExtractor={item => item.id}
          renderItem={renderNotification}
          refreshControl={
            <RefreshControl
              refreshing={refreshing}
              onRefresh={handleRefresh}
              tintColor={GOLD}
              colors={[GOLD]}
            />
          }
          showsVerticalScrollIndicator={false}
          ListEmptyComponent={
            <View style={s.emptyState}>
              <View style={s.emptyCircle}>
                <Text style={s.emptyIcon}>{'\uD83D\uDD14'}</Text>
              </View>
              <Text style={s.emptyTitle}>No notifications yet</Text>
              <Text style={s.emptyText}>
                Updates about your requests and quotations will appear here.
              </Text>
            </View>
          }
        />
      )}
    </SafeAreaView>
  );
}

/* ── styles ── */

const SHADOW = {
  shadowColor: '#000',
  shadowOffset: {width: 0, height: 2},
  shadowOpacity: 0.06,
  shadowRadius: 8,
  elevation: 3,
};

const s = StyleSheet.create({
  safe: {
    flex: 1,
    backgroundColor: BG,
  },
  loadingContainer: {
    flex: 1,
    backgroundColor: BG,
  },
  loadingBody: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 24,
  },
  loadingText: {
    marginTop: 14,
    color: MUTED,
    fontSize: 14,
  },

  /* top row / back button */
  topRow: {
    paddingHorizontal: 20,
    paddingTop: 14,
    paddingBottom: 2,
  },
  backBtn: {
    color: NAVY,
    fontSize: 16,
    fontWeight: '600',
  },

  /* page intro */
  introSection: {
    paddingHorizontal: 20,
    paddingTop: 10,
    paddingBottom: 14,
  },
  pageTitle: {
    color: NAVY,
    fontSize: 26,
    fontWeight: '800',
    marginBottom: 4,
  },
  pageSubtitle: {
    color: MUTED,
    fontSize: 14,
    lineHeight: 20,
  },

  /* summary card */
  summaryCard: {
    backgroundColor: CARD,
    marginHorizontal: 20,
    marginBottom: 14,
    borderRadius: 18,
    padding: 18,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    ...SHADOW,
  },
  summaryLeft: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
  },
  summaryLabel: {
    color: MUTED,
    fontSize: 13,
    fontWeight: '700',
    textTransform: 'uppercase',
    letterSpacing: 0.4,
  },
  summaryValue: {
    color: NAVY,
    fontSize: 26,
    fontWeight: '800',
  },
  markAllBtn: {
    borderWidth: 1.5,
    borderColor: GOLD,
    borderRadius: 12,
    paddingVertical: 10,
    paddingHorizontal: 16,
    minWidth: 130,
    alignItems: 'center',
    justifyContent: 'center',
  },
  markAllBtnDisabled: {
    borderColor: DIVIDER,
  },
  markAllBtnText: {
    color: GOLD,
    fontSize: 13,
    fontWeight: '700',
  },
  markAllBtnTextDisabled: {
    color: MUTED,
  },

  /* error card */
  errorCard: {
    backgroundColor: CARD,
    marginHorizontal: 20,
    borderRadius: 18,
    borderWidth: 1,
    borderColor: '#fecaca',
    padding: 22,
    ...SHADOW,
  },
  errorTitle: {
    color: '#991b1b',
    fontSize: 17,
    fontWeight: '700',
    marginBottom: 8,
  },
  errorText: {
    color: '#7f1d1d',
    fontSize: 14,
    lineHeight: 20,
    marginBottom: 16,
  },
  retryBtn: {
    backgroundColor: GOLD,
    borderRadius: 12,
    paddingVertical: 12,
    alignItems: 'center',
  },
  retryBtnText: {
    color: '#fff',
    fontSize: 14,
    fontWeight: '700',
  },

  /* list */
  listContent: {
    paddingHorizontal: 20,
    paddingBottom: 32,
    paddingTop: 2,
  },
  emptyListContent: {
    flexGrow: 1,
    justifyContent: 'center',
  },

  /* empty state */
  emptyState: {
    alignItems: 'center',
    backgroundColor: CARD,
    borderRadius: 20,
    padding: 32,
    ...SHADOW,
  },
  emptyCircle: {
    width: 64,
    height: 64,
    borderRadius: 32,
    backgroundColor: '#dce6f8',
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 16,
  },
  emptyIcon: {
    fontSize: 28,
  },
  emptyTitle: {
    color: NAVY,
    fontSize: 19,
    fontWeight: '800',
    marginBottom: 6,
  },
  emptyText: {
    color: MUTED,
    fontSize: 14,
    lineHeight: 20,
    textAlign: 'center',
  },

  /* notification card */
  card: {
    backgroundColor: CARD,
    borderRadius: 18,
    padding: 18,
    marginBottom: 12,
    ...SHADOW,
  },
  cardUnread: {
    borderLeftWidth: 4,
    borderLeftColor: GOLD,
  },
  cardPressed: {
    opacity: 0.88,
  },
  cardHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    marginBottom: 10,
  },
  cardTitleWrap: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    paddingRight: 10,
  },
  unreadDot: {
    width: 8,
    height: 8,
    borderRadius: 4,
    backgroundColor: GOLD,
    marginRight: 8,
    marginTop: 2,
  },
  cardTitle: {
    color: NAVY,
    fontSize: 16,
    fontWeight: '600',
    flex: 1,
  },
  cardTitleUnread: {
    fontWeight: '700',
  },
  cardDate: {
    color: MUTED,
    fontSize: 11,
    fontWeight: '500',
    marginTop: 2,
  },
  cardMessage: {
    color: '#4a5568',
    fontSize: 14,
    lineHeight: 21,
    marginBottom: 12,
  },
  cardDivider: {
    height: 1,
    backgroundColor: DIVIDER,
    marginBottom: 10,
  },
  cardFooter: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  typeBadge: {
    borderRadius: 8,
    paddingVertical: 4,
    paddingHorizontal: 10,
  },
  typeBadgeText: {
    fontSize: 11,
    fontWeight: '700',
    textTransform: 'uppercase',
    letterSpacing: 0.3,
  },
  readLabel: {
    fontSize: 11,
    fontWeight: '700',
  },
  readLabelRead: {
    color: MUTED,
  },
  readLabelUnread: {
    color: GOLD,
  },
});
