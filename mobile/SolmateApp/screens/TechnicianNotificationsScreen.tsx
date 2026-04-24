import React, {useCallback, useState} from 'react';
import {
  ActivityIndicator,
  Alert,
  FlatList,
  Pressable,
  RefreshControl,
  StyleSheet,
  Text,
  View,
} from 'react-native';
import {useFocusEffect} from '@react-navigation/native';

import {AppButton} from '../components';
import {ApiError} from '../src/services/api';
import {
  AppNotification,
  deleteAllNotifications,
  deleteNotification,
  getNotifications,
  markAllNotificationsAsRead,
  markNotificationAsRead,
} from '../src/services/notificationApi';
import {
  TECHNICIAN_NOTIFICATION_ROUTE_NAMES,
  getTechnicianNotificationNavigationTarget,
} from '../src/utils/notificationNavigation';

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

function getDeleteNotificationErrorMessage(error: unknown) {
  if (error instanceof ApiError) {
    if (error.status === 401) {
      return 'Your session has expired. Please log in again.';
    }

    if (error.status === 404) {
      return 'This notification no longer exists.';
    }

    return error.message || 'Could not delete this notification.';
  }

  if (error instanceof Error && error.message.trim()) {
    return error.message;
  }

  return 'Could not delete this notification.';
}

export default function TechnicianNotificationsScreen({navigation}: any) {
  const [notifications, setNotifications] = useState<AppNotification[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [errorMessage, setErrorMessage] = useState('');
  const [markingAllRead, setMarkingAllRead] = useState(false);
  const [deletingAll, setDeletingAll] = useState(false);
  const [deletingNotificationId, setDeletingNotificationId] = useState<string | null>(null);

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
      const target = getTechnicianNotificationNavigationTarget(notification);

      if (!target) {
        navigation.navigate('TechnicianDashboard');
        return;
      }

      const availableRouteNames = navigation.getState?.()?.routeNames;
      const allowedRouteNames = Array.isArray(availableRouteNames)
        ? availableRouteNames
        : TECHNICIAN_NOTIFICATION_ROUTE_NAMES;

      if (!allowedRouteNames.includes(target.routeName)) {
        navigation.navigate('TechnicianDashboard');
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

  const handleDeleteNotification = useCallback(
    (notification: AppNotification) => {
      Alert.alert(
        'Delete this notification?',
        '',
        [
          {
            text: 'Cancel',
            style: 'cancel',
          },
          {
            text: 'Delete',
            style: 'destructive',
            onPress: async () => {
              try {
                setDeletingNotificationId(notification.id);
                await deleteNotification(notification.id);
                setNotifications(currentNotifications =>
                  currentNotifications.filter(
                    currentNotification => currentNotification.id !== notification.id,
                  ),
                );
              } catch (error) {
                Alert.alert(
                  'Delete failed',
                  getDeleteNotificationErrorMessage(error),
                );
              } finally {
                setDeletingNotificationId(currentNotificationId =>
                  currentNotificationId === notification.id
                    ? null
                    : currentNotificationId,
                );
              }
            },
          },
        ],
      );
    },
    [],
  );

  const handleDeleteAllNotifications = useCallback(() => {
    Alert.alert(
      'Delete all notifications?',
      '',
      [
        {
          text: 'Cancel',
          style: 'cancel',
        },
        {
          text: 'Delete all',
          style: 'destructive',
          onPress: async () => {
            try {
              setDeletingAll(true);
              await deleteAllNotifications();
              setNotifications([]);
            } catch (error) {
              Alert.alert(
                'Delete all failed',
                getDeleteNotificationErrorMessage(error),
              );
            } finally {
              setDeletingAll(false);
            }
          },
        },
      ],
    );
  }, []);

  const unreadCount = notifications.filter(
    notification => !isNotificationRead(notification),
  ).length;

  const renderNotification = ({item}: {item: AppNotification}) => {
    const isRead = isNotificationRead(item);
    const isDeleting = deletingNotificationId === item.id;

    return (
      <View
        style={[
          styles.notificationCard,
          !isRead ? styles.notificationCardUnread : null,
          isDeleting ? styles.notificationCardDisabled : null,
        ]}>
        <Pressable
          disabled={isDeleting}
          onPress={() => handleNotificationPress(item)}
          style={({pressed}) => [
            pressed && !isDeleting ? styles.notificationCardPressed : null,
          ]}>
          <View style={styles.notificationHeader}>
            <View style={styles.notificationTitleWrap}>
              <Text style={styles.notificationTitle}>
                {item.title || 'Notification'}
              </Text>
              <Text style={styles.notificationDate}>
                {formatNotificationDate(item)}
              </Text>
            </View>

            {!isRead ? <View style={styles.unreadDot} /> : null}
          </View>

          <Text style={styles.notificationMessage}>
            {item.message || 'Open this notification to view more details.'}
          </Text>
        </Pressable>

        <View style={styles.notificationFooter}>
          <View style={styles.notificationFooterMeta}>
            <Text
              style={[
                styles.notificationState,
                isRead ? styles.readState : styles.unreadState,
              ]}>
              {isRead ? 'Read' : 'Unread'}
            </Text>
            <Text style={styles.notificationType}>
              {String(item.type || 'general')
                .replace(/_/g, ' ')
                .replace(/\b\w/g, character => character.toUpperCase())}
            </Text>
          </View>
          <Pressable
            accessibilityRole="button"
            disabled={isDeleting}
            hitSlop={10}
            onPress={() => handleDeleteNotification(item)}
            style={({pressed}) => [
              styles.deleteButton,
              isDeleting ? styles.deleteButtonDisabled : null,
              pressed && !isDeleting ? styles.deleteButtonPressed : null,
            ]}>
            <Text
              style={[
                styles.deleteButtonText,
                isDeleting ? styles.deleteButtonTextDisabled : null,
              ]}>
              {isDeleting ? 'Deleting...' : 'Delete'}
            </Text>
          </Pressable>
        </View>
      </View>
    );
  };

  if (loading) {
    return (
      <View style={styles.centeredContainer}>
        <ActivityIndicator color="#0891b2" size="large" />
        <Text style={styles.loadingText}>Loading notifications...</Text>
      </View>
    );
  }

  return (
    <View style={styles.container}>
      <Text style={styles.title}>Notifications</Text>
      <Text style={styles.subtitle}>
        Stay updated on assigned inspection and service requests, including
        new assignments and schedule changes.
      </Text>

      <View style={styles.summaryCard}>
        <View>
          <Text style={styles.summaryLabel}>Unread notifications</Text>
          <Text style={styles.summaryValue}>{unreadCount}</Text>
        </View>

        <AppButton
          title={markingAllRead ? 'Marking...' : 'Mark all as read'}
          variant="outline"
          disabled={markingAllRead || deletingAll || unreadCount === 0}
          onPress={handleMarkAllRead}
          style={styles.markAllButton}
        />
      </View>

      <AppButton
        title={deletingAll ? 'Deleting...' : 'Delete all'}
        variant="outline"
        disabled={deletingAll || notifications.length === 0}
        onPress={handleDeleteAllNotifications}
        style={styles.deleteAllButton}
        textStyle={
          deletingAll || notifications.length === 0
            ? styles.deleteAllButtonTextDisabled
            : styles.deleteAllButtonText
        }
      />

      {errorMessage ? (
        <View style={styles.errorCard}>
          <Text style={styles.errorTitle}>Something went wrong</Text>
          <Text style={styles.errorText}>{errorMessage}</Text>
          <AppButton title="Try again" onPress={() => loadNotifications(true)} />
        </View>
      ) : (
        <FlatList
          contentContainerStyle={[
            styles.listContent,
            notifications.length === 0 ? styles.emptyListContent : null,
          ]}
          data={notifications}
          keyExtractor={item => item.id}
          renderItem={renderNotification}
          refreshControl={
            <RefreshControl
              refreshing={refreshing}
              onRefresh={handleRefresh}
              tintColor="#0891b2"
            />
          }
          showsVerticalScrollIndicator={false}
          ListEmptyComponent={
            <View style={styles.emptyState}>
              <View style={styles.emptyIcon} />
              <Text style={styles.emptyTitle}>No notifications yet</Text>
              <Text style={styles.emptyText}>
                Assignment updates and schedule changes will appear here.
              </Text>
            </View>
          }
        />
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#f5f7fb',
    padding: 20,
  },
  centeredContainer: {
    flex: 1,
    backgroundColor: '#f5f7fb',
    justifyContent: 'center',
    alignItems: 'center',
    padding: 24,
  },
  loadingText: {
    marginTop: 12,
    color: '#475569',
    fontSize: 14,
  },
  title: {
    color: '#0f172a',
    fontSize: 28,
    fontWeight: '800',
    marginBottom: 8,
  },
  subtitle: {
    color: '#64748b',
    fontSize: 14,
    lineHeight: 20,
    marginBottom: 18,
  },
  summaryCard: {
    backgroundColor: '#ffffff',
    borderRadius: 18,
    borderWidth: 1,
    borderColor: '#bae6fd',
    padding: 18,
    marginBottom: 18,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
  },
  summaryLabel: {
    color: '#64748b',
    fontSize: 13,
    fontWeight: '600',
    marginBottom: 4,
    textTransform: 'uppercase',
  },
  summaryValue: {
    color: '#0f172a',
    fontSize: 28,
    fontWeight: '800',
  },
  markAllButton: {
    minHeight: 46,
    paddingHorizontal: 14,
  },
  deleteAllButton: {
    minHeight: 46,
    marginBottom: 18,
    borderColor: '#fecaca',
    backgroundColor: '#fff7ed',
  },
  deleteAllButtonText: {
    color: '#b91c1c',
  },
  deleteAllButtonTextDisabled: {
    color: '#64748b',
  },
  errorCard: {
    backgroundColor: '#ffffff',
    borderRadius: 18,
    borderWidth: 1,
    borderColor: '#fecaca',
    padding: 20,
  },
  errorTitle: {
    color: '#991b1b',
    fontSize: 18,
    fontWeight: '700',
    marginBottom: 8,
  },
  errorText: {
    color: '#7f1d1d',
    fontSize: 14,
    lineHeight: 20,
    marginBottom: 14,
  },
  listContent: {
    paddingBottom: 28,
  },
  emptyListContent: {
    flexGrow: 1,
    justifyContent: 'center',
  },
  emptyState: {
    alignItems: 'center',
    backgroundColor: '#ffffff',
    borderRadius: 20,
    borderWidth: 1,
    borderColor: '#e5e7eb',
    padding: 28,
  },
  emptyIcon: {
    width: 52,
    height: 52,
    borderRadius: 26,
    backgroundColor: '#cffafe',
    marginBottom: 16,
  },
  emptyTitle: {
    color: '#0f172a',
    fontSize: 20,
    fontWeight: '800',
    marginBottom: 8,
  },
  emptyText: {
    color: '#64748b',
    fontSize: 14,
    lineHeight: 20,
    textAlign: 'center',
  },
  notificationCard: {
    backgroundColor: '#ffffff',
    borderRadius: 18,
    borderWidth: 1,
    borderColor: '#e5e7eb',
    padding: 18,
    marginBottom: 12,
  },
  notificationCardUnread: {
    borderColor: '#67e8f9',
    backgroundColor: '#f0fdff',
  },
  notificationCardPressed: {
    opacity: 0.9,
  },
  notificationCardDisabled: {
    opacity: 0.72,
  },
  notificationHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    marginBottom: 10,
  },
  notificationTitleWrap: {
    flex: 1,
    paddingRight: 12,
  },
  notificationTitle: {
    color: '#0f172a',
    fontSize: 17,
    fontWeight: '700',
    marginBottom: 4,
  },
  notificationDate: {
    color: '#64748b',
    fontSize: 12,
    fontWeight: '500',
  },
  unreadDot: {
    width: 10,
    height: 10,
    borderRadius: 5,
    backgroundColor: '#0891b2',
    marginTop: 6,
  },
  notificationMessage: {
    color: '#334155',
    fontSize: 14,
    lineHeight: 21,
    marginBottom: 14,
  },
  notificationFooter: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    gap: 12,
  },
  notificationFooterMeta: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
    flexWrap: 'wrap',
  },
  notificationState: {
    fontSize: 12,
    fontWeight: '700',
  },
  unreadState: {
    color: '#0f766e',
  },
  readState: {
    color: '#64748b',
  },
  notificationType: {
    color: '#64748b',
    fontSize: 12,
    fontWeight: '600',
  },
  deleteButton: {
    borderWidth: 1,
    borderColor: '#fecaca',
    backgroundColor: '#fff7ed',
    borderRadius: 10,
    paddingHorizontal: 12,
    paddingVertical: 8,
    alignItems: 'center',
    justifyContent: 'center',
  },
  deleteButtonDisabled: {
    borderColor: '#e2e8f0',
    backgroundColor: '#f8fafc',
  },
  deleteButtonPressed: {
    opacity: 0.72,
  },
  deleteButtonText: {
    color: '#b91c1c',
    fontSize: 12,
    fontWeight: '700',
  },
  deleteButtonTextDisabled: {
    color: '#64748b',
  },
});
