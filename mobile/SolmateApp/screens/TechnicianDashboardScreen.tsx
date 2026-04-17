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

import {AppButton, AppCard} from '../components';
import {AuthContext} from '../src/context/AuthContext';
import {ApiError} from '../src/services/api';
import {getUnreadNotificationCount} from '../src/services/notificationApi';
import {getAssignedInspectionRequests} from '../src/services/technicianApi';

type ActionCardProps = {
  title: string;
  subtitle: string;
  accentColor: string;
  onPress: () => void;
};

function ActionCard({
  title,
  subtitle,
  accentColor,
  onPress,
}: ActionCardProps) {
  return (
    <Pressable
      onPress={onPress}
      style={({pressed}) => [styles.actionCard, pressed && styles.pressed]}>
      <View style={styles.actionHeader}>
        <View style={[styles.actionAccent, {backgroundColor: accentColor}]} />
        <Text style={styles.actionPill}>Open</Text>
      </View>
      <Text style={styles.actionTitle}>{title}</Text>
      <Text style={styles.actionSubtitle}>{subtitle}</Text>
      <View style={styles.actionFooter}>
        <Text style={styles.actionFooterText}>Tap to continue</Text>
      </View>
    </Pressable>
  );
}

function getFriendlyErrorMessage(error: unknown) {
  if (error instanceof ApiError) {
    return error.message;
  }

  return 'Could not load technician dashboard data right now.';
}

export default function TechnicianDashboardScreen({navigation}: any) {
  const {logout, user} = useContext(AuthContext);
  const technicianName = user?.name || 'Technician';

  const [loading, setLoading] = useState(true);
  const [errorMessage, setErrorMessage] = useState('');
  const [notificationsLoading, setNotificationsLoading] = useState(true);
  const [unreadCount, setUnreadCount] = useState(0);
  const [requestCounts, setRequestCounts] = useState({
    total: 0,
    assigned: 0,
    inProgress: 0,
    completed: 0,
  });

  const loadDashboard = useCallback(async () => {
    try {
      setLoading(true);
      setErrorMessage('');

      const requests = await getAssignedInspectionRequests();
      const assigned = requests.filter(item => item.status === 'assigned').length;
      const inProgress = requests.filter(
        item => item.status === 'in_progress',
      ).length;
      const completed = requests.filter(item => item.status === 'completed').length;

      setRequestCounts({
        total: requests.length,
        assigned,
        inProgress,
        completed,
      });
    } catch (error) {
      setRequestCounts({
        total: 0,
        assigned: 0,
        inProgress: 0,
        completed: 0,
      });
      setErrorMessage(getFriendlyErrorMessage(error));
    } finally {
      setLoading(false);
    }
  }, []);

  const loadUnreadCount = useCallback(async () => {
    try {
      setNotificationsLoading(true);
      const count = await getUnreadNotificationCount();
      setUnreadCount(count);
    } catch (error) {
      if (__DEV__ && error instanceof ApiError) {
        console.log('Technician unread notification count error:', error.message);
      }
      setUnreadCount(0);
    } finally {
      setNotificationsLoading(false);
    }
  }, []);

  useFocusEffect(
    useCallback(() => {
      loadDashboard();
    }, [loadDashboard]),
  );

  useFocusEffect(
    useCallback(() => {
      loadUnreadCount();
    }, [loadUnreadCount]),
  );

  return (
    <SafeAreaView style={styles.safeArea}>
      <ScrollView
        contentContainerStyle={styles.contentContainer}
        showsVerticalScrollIndicator={false}>
        <View style={styles.heroCard}>
          <Text style={styles.eyebrow}>Technician dashboard</Text>
          <Text style={styles.heroTitle}>Welcome back, {technicianName}</Text>
          <Text style={styles.heroSubtitle}>
            Review assigned inspection requests, move them through their status
            flow, and submit final quotations after site visits are completed.
          </Text>

          <View style={styles.heroMetaRow}>
            <View style={styles.heroMetaCard}>
              <Text style={styles.heroMetaLabel}>Email</Text>
              <Text style={styles.heroMetaValue}>
                {user?.email || 'No email available'}
              </Text>
            </View>
            <View style={styles.heroMetaCard}>
              <Text style={styles.heroMetaLabel}>Role</Text>
              <Text style={styles.heroMetaValue}>{user?.role || 'technician'}</Text>
            </View>
          </View>
        </View>

        <Pressable
          onPress={() => navigation.navigate('TechnicianNotifications')}
          style={({pressed}) => [
            styles.notificationCard,
            pressed ? styles.pressed : null,
          ]}>
          <View style={styles.notificationCardTextWrap}>
            <Text style={styles.notificationEyebrow}>Notifications</Text>
            <Text style={styles.notificationTitle}>Open your latest updates</Text>
            <Text style={styles.notificationSubtitle}>
              Review new assignments, schedule changes, and request updates for
              your technician account.
            </Text>
          </View>

          <View style={styles.notificationBadge}>
            {notificationsLoading ? (
              <ActivityIndicator color="#0369a1" size="small" />
            ) : (
              <>
                <Text style={styles.notificationBadgeValue}>{unreadCount}</Text>
                <Text style={styles.notificationBadgeLabel}>Unread</Text>
              </>
            )}
          </View>
        </Pressable>

        <AppCard style={styles.sectionCard}>
          <Text style={styles.sectionTitle}>Inspection workload snapshot</Text>
          <Text style={styles.sectionSubtitle}>
            A live summary of the inspection requests assigned to your account.
          </Text>

          {loading ? (
            <View style={styles.loadingCard}>
              <ActivityIndicator size="small" color="#2563eb" />
              <Text style={styles.loadingText}>Refreshing dashboard data...</Text>
            </View>
          ) : null}

          {errorMessage ? (
            <View style={styles.errorCard}>
              <Text style={styles.errorTitle}>Dashboard data unavailable</Text>
              <Text style={styles.errorText}>{errorMessage}</Text>
              <AppButton
                title="Try again"
                onPress={loadDashboard}
                style={styles.retryButton}
              />
            </View>
          ) : null}

          <View style={styles.summaryGrid}>
            <View style={[styles.summaryCard, styles.summaryCardPurple]}>
              <Text style={styles.summaryLabel}>Assigned</Text>
              <Text style={styles.summaryValue}>{requestCounts.assigned}</Text>
              <Text style={styles.summaryNote}>
                Ready for technician review and the first status update.
              </Text>
            </View>

            <View style={[styles.summaryCard, styles.summaryCardBlue]}>
              <Text style={styles.summaryLabel}>In progress</Text>
              <Text style={styles.summaryValue}>{requestCounts.inProgress}</Text>
              <Text style={styles.summaryNote}>
                Site visits already started and still being worked on.
              </Text>
            </View>

            <View style={[styles.summaryCard, styles.summaryCardGreen]}>
              <Text style={styles.summaryLabel}>Completed</Text>
              <Text style={styles.summaryValue}>{requestCounts.completed}</Text>
              <Text style={styles.summaryNote}>
                Ready for final quotation submission.
              </Text>
            </View>

            <View style={[styles.summaryCard, styles.summaryCardSlate]}>
              <Text style={styles.summaryLabel}>Total assigned</Text>
              <Text style={styles.summaryValue}>{requestCounts.total}</Text>
              <Text style={styles.summaryNote}>
                Everything currently assigned to your technician account.
              </Text>
            </View>
          </View>
        </AppCard>

        <AppCard style={styles.sectionCard}>
          <Text style={styles.sectionTitle}>Quick actions</Text>
          <Text style={styles.sectionSubtitle}>
            Jump straight into assigned inspection requests, service requests,
            and completed jobs ready for quotation.
          </Text>

          <ActionCard
            title="Assigned Inspection Requests"
            subtitle="Open your full technician inspection queue and review each request."
            accentColor="#c4b5fd"
            onPress={() => navigation.navigate('AssignedInspectionRequests')}
          />

          <ActionCard
            title="Service Requests"
            subtitle="Open the technician service request list and review assigned service jobs."
            accentColor="#fdba74"
            onPress={() => navigation.navigate('TechnicianServiceRequests')}
          />

          <ActionCard
            title="Submit Final Quotations"
            subtitle="Open inspection requests and continue into the final quotation form once completed."
            accentColor="#86efac"
            onPress={() => navigation.navigate('AssignedInspectionRequests')}
          />

          <View style={styles.buttonStack}>
            <AppButton
              title="View Assigned Inspection Requests"
              onPress={() => navigation.navigate('AssignedInspectionRequests')}
            />
            <AppButton
              title="Service Requests"
              variant="secondary"
              style={styles.buttonSpacing}
              onPress={() => navigation.navigate('TechnicianServiceRequests')}
            />
            <AppButton
              title="Open Settings"
              variant="secondary"
              style={styles.buttonSpacing}
              onPress={() => navigation.navigate('TechnicianSettings')}
            />
            <AppButton
              title="Logout"
              variant="outline"
              style={styles.buttonSpacing}
              onPress={logout}
            />
          </View>
        </AppCard>
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safeArea: {
    backgroundColor: '#f5f7fb',
    flex: 1,
  },
  contentContainer: {
    padding: 20,
    paddingBottom: 28,
  },
  heroCard: {
    backgroundColor: '#e0f2fe',
    borderRadius: 28,
    marginBottom: 18,
    padding: 22,
  },
  eyebrow: {
    color: '#0369a1',
    fontSize: 12,
    fontWeight: '700',
    letterSpacing: 0.4,
    marginBottom: 8,
    textTransform: 'uppercase',
  },
  heroTitle: {
    color: '#0f172a',
    fontSize: 28,
    fontWeight: '800',
    lineHeight: 34,
    marginBottom: 10,
  },
  heroSubtitle: {
    color: '#334155',
    fontSize: 14,
    lineHeight: 21,
    marginBottom: 16,
  },
  heroMetaRow: {
    flexDirection: 'row',
    gap: 12,
  },
  heroMetaCard: {
    backgroundColor: '#ffffff',
    borderRadius: 18,
    flex: 1,
    padding: 14,
  },
  heroMetaLabel: {
    color: '#64748b',
    fontSize: 12,
    fontWeight: '700',
    marginBottom: 6,
    textTransform: 'uppercase',
  },
  heroMetaValue: {
    color: '#0f172a',
    fontSize: 14,
    fontWeight: '600',
  },
  sectionCard: {
    marginBottom: 18,
  },
  notificationCard: {
    backgroundColor: '#ffffff',
    borderColor: '#bae6fd',
    borderRadius: 24,
    borderWidth: 1,
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 18,
    padding: 20,
  },
  notificationCardTextWrap: {
    flex: 1,
    paddingRight: 16,
  },
  notificationEyebrow: {
    color: '#0369a1',
    fontSize: 12,
    fontWeight: '700',
    letterSpacing: 0.4,
    marginBottom: 8,
    textTransform: 'uppercase',
  },
  notificationTitle: {
    color: '#0f172a',
    fontSize: 20,
    fontWeight: '800',
    marginBottom: 8,
  },
  notificationSubtitle: {
    color: '#475569',
    fontSize: 14,
    lineHeight: 20,
  },
  notificationBadge: {
    alignItems: 'center',
    backgroundColor: '#e0f2fe',
    borderRadius: 18,
    justifyContent: 'center',
    minHeight: 78,
    minWidth: 86,
    paddingHorizontal: 14,
    paddingVertical: 12,
  },
  notificationBadgeValue: {
    color: '#0369a1',
    fontSize: 26,
    fontWeight: '800',
  },
  notificationBadgeLabel: {
    color: '#075985',
    fontSize: 11,
    fontWeight: '700',
    marginTop: 2,
    textTransform: 'uppercase',
  },
  sectionTitle: {
    color: '#0f172a',
    fontSize: 20,
    fontWeight: '700',
    marginBottom: 6,
  },
  sectionSubtitle: {
    color: '#64748b',
    fontSize: 14,
    lineHeight: 20,
    marginBottom: 16,
  },
  loadingCard: {
    alignItems: 'center',
    flexDirection: 'row',
    marginBottom: 16,
  },
  loadingText: {
    color: '#475569',
    fontSize: 14,
    marginLeft: 10,
  },
  errorCard: {
    backgroundColor: '#fff1f2',
    borderColor: '#fecdd3',
    borderRadius: 18,
    borderWidth: 1,
    marginBottom: 16,
    padding: 16,
  },
  errorTitle: {
    color: '#be123c',
    fontSize: 16,
    fontWeight: '700',
    marginBottom: 6,
  },
  errorText: {
    color: '#9f1239',
    fontSize: 14,
    lineHeight: 20,
  },
  retryButton: {
    marginTop: 14,
  },
  summaryGrid: {
    gap: 12,
  },
  summaryCard: {
    borderRadius: 18,
    padding: 16,
  },
  summaryCardPurple: {
    backgroundColor: '#f5f3ff',
  },
  summaryCardBlue: {
    backgroundColor: '#eff6ff',
  },
  summaryCardGreen: {
    backgroundColor: '#ecfdf5',
  },
  summaryCardSlate: {
    backgroundColor: '#f8fafc',
  },
  summaryLabel: {
    color: '#475569',
    fontSize: 12,
    fontWeight: '700',
    marginBottom: 6,
    textTransform: 'uppercase',
  },
  summaryValue: {
    color: '#0f172a',
    fontSize: 28,
    fontWeight: '800',
    marginBottom: 8,
  },
  summaryNote: {
    color: '#475569',
    fontSize: 14,
    lineHeight: 20,
  },
  actionCard: {
    backgroundColor: '#ffffff',
    borderColor: '#e2e8f0',
    borderRadius: 20,
    borderWidth: 1,
    marginBottom: 14,
    padding: 18,
  },
  pressed: {
    opacity: 0.88,
  },
  actionHeader: {
    alignItems: 'center',
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 12,
  },
  actionAccent: {
    borderRadius: 999,
    height: 10,
    width: 56,
  },
  actionPill: {
    color: '#64748b',
    fontSize: 12,
    fontWeight: '700',
    textTransform: 'uppercase',
  },
  actionTitle: {
    color: '#0f172a',
    fontSize: 18,
    fontWeight: '800',
    marginBottom: 8,
  },
  actionSubtitle: {
    color: '#475569',
    fontSize: 14,
    lineHeight: 20,
  },
  actionFooter: {
    borderTopColor: '#e2e8f0',
    borderTopWidth: 1,
    marginTop: 14,
    paddingTop: 12,
  },
  actionFooterText: {
    color: '#2563eb',
    fontSize: 13,
    fontWeight: '700',
  },
  buttonStack: {
    marginTop: 4,
  },
  buttonSpacing: {
    marginTop: 12,
  },
});
