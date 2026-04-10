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
import {getAssignedServiceRequests} from '../src/services/technicianApi';

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
  const [taskCounts, setTaskCounts] = useState({
    total: 0,
    assigned: 0,
    inProgress: 0,
    completed: 0,
  });

  const loadDashboard = useCallback(async () => {
    try {
      setLoading(true);
      setErrorMessage('');

      const requests = await getAssignedServiceRequests();
      const assigned = requests.filter(item => item.status === 'assigned').length;
      const inProgress = requests.filter(
        item => item.status === 'in_progress',
      ).length;
      const completed = requests.filter(item => item.status === 'completed').length;

      setTaskCounts({
        total: requests.length,
        assigned,
        inProgress,
        completed,
      });
    } catch (error) {
      setTaskCounts({
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

  useFocusEffect(
    useCallback(() => {
      loadDashboard();
    }, [loadDashboard]),
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
            Track assigned field work, move requests through their status flow,
            and prepare final quotations after completing service visits.
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

        <AppCard style={styles.sectionCard}>
          <Text style={styles.sectionTitle}>Workload snapshot</Text>
          <Text style={styles.sectionSubtitle}>
            A live summary of the tasks currently assigned to your account.
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
            <View style={[styles.summaryCard, styles.summaryCardBlue]}>
              <Text style={styles.summaryLabel}>Assigned tasks</Text>
              <Text style={styles.summaryValue}>{taskCounts.assigned}</Text>
              <Text style={styles.summaryNote}>
                Jobs waiting for the first status update.
              </Text>
            </View>

            <View style={[styles.summaryCard, styles.summaryCardAmber]}>
              <Text style={styles.summaryLabel}>In progress</Text>
              <Text style={styles.summaryValue}>{taskCounts.inProgress}</Text>
              <Text style={styles.summaryNote}>
                Work already started and still active.
              </Text>
            </View>

            <View style={[styles.summaryCard, styles.summaryCardGreen]}>
              <Text style={styles.summaryLabel}>Completed</Text>
              <Text style={styles.summaryValue}>{taskCounts.completed}</Text>
              <Text style={styles.summaryNote}>
                Ready for final quotation submission.
              </Text>
            </View>

            <View style={[styles.summaryCard, styles.summaryCardSlate]}>
              <Text style={styles.summaryLabel}>Total tasks</Text>
              <Text style={styles.summaryValue}>{taskCounts.total}</Text>
              <Text style={styles.summaryNote}>
                Everything currently assigned to you.
              </Text>
            </View>
          </View>
        </AppCard>

        <AppCard style={styles.sectionCard}>
          <Text style={styles.sectionTitle}>Quick actions</Text>
          <Text style={styles.sectionSubtitle}>
            Jump straight to task handling and quotation-ready requests.
          </Text>

          <ActionCard
            title="Assigned Tasks"
            subtitle="Open your full technician task list and review each request."
            accentColor="#93c5fd"
            onPress={() => navigation.navigate('AssignedTasks')}
          />

          <ActionCard
            title="Final Quotations"
            subtitle="Open completed service requests that are ready for quotation."
            accentColor="#86efac"
            onPress={() =>
              navigation.navigate('AssignedTasks', {
                statusFilter: 'completed',
              })
            }
          />

          <View style={styles.buttonStack}>
            <AppButton
              title="View Assigned Tasks"
              onPress={() => navigation.navigate('AssignedTasks')}
            />
            <AppButton
              title="Open Completed Tasks"
              variant="secondary"
              style={styles.buttonSpacing}
              onPress={() =>
                navigation.navigate('AssignedTasks', {
                  statusFilter: 'completed',
                })
              }
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
    fontSize: 13,
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
    fontSize: 15,
    lineHeight: 22,
    marginBottom: 18,
  },
  heroMetaRow: {
    gap: 10,
  },
  heroMetaCard: {
    backgroundColor: 'rgba(255, 255, 255, 0.84)',
    borderRadius: 18,
    paddingHorizontal: 16,
    paddingVertical: 14,
  },
  heroMetaLabel: {
    color: '#64748b',
    fontSize: 12,
    fontWeight: '700',
    marginBottom: 4,
    textTransform: 'uppercase',
  },
  heroMetaValue: {
    color: '#0f172a',
    fontSize: 15,
    fontWeight: '600',
  },
  sectionCard: {
    marginBottom: 18,
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
    backgroundColor: '#f8fafc',
    borderRadius: 18,
    flexDirection: 'row',
    marginBottom: 16,
    padding: 14,
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
    borderRadius: 20,
    padding: 16,
  },
  summaryCardBlue: {
    backgroundColor: '#eff6ff',
  },
  summaryCardAmber: {
    backgroundColor: '#fff7ed',
  },
  summaryCardGreen: {
    backgroundColor: '#f0fdf4',
  },
  summaryCardSlate: {
    backgroundColor: '#f8fafc',
  },
  summaryLabel: {
    color: '#64748b',
    fontSize: 12,
    fontWeight: '700',
    marginBottom: 8,
    textTransform: 'uppercase',
  },
  summaryValue: {
    color: '#0f172a',
    fontSize: 24,
    fontWeight: '800',
    marginBottom: 6,
  },
  summaryNote: {
    color: '#475569',
    fontSize: 14,
    lineHeight: 20,
  },
  actionCard: {
    backgroundColor: '#f8fafc',
    borderColor: '#e2e8f0',
    borderRadius: 20,
    borderWidth: 1,
    marginBottom: 12,
    padding: 16,
  },
  actionHeader: {
    alignItems: 'center',
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 14,
  },
  actionAccent: {
    borderRadius: 999,
    height: 10,
    width: 56,
  },
  actionPill: {
    backgroundColor: '#ffffff',
    borderRadius: 999,
    color: '#64748b',
    fontSize: 11,
    fontWeight: '700',
    overflow: 'hidden',
    paddingHorizontal: 10,
    paddingVertical: 5,
    textTransform: 'uppercase',
  },
  actionTitle: {
    color: '#0f172a',
    fontSize: 17,
    fontWeight: '700',
    marginBottom: 6,
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
  pressed: {
    opacity: 0.88,
  },
});
