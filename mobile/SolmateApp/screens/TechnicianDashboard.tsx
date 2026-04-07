import React, {useContext} from 'react';
import {
  Alert,
  Pressable,
  SafeAreaView,
  ScrollView,
  StyleSheet,
  Text,
  View,
} from 'react-native';

import {AppButton, AppCard} from '../components';
import {AuthContext} from '../src/context/AuthContext';

type StatCardProps = {
  label: string;
  value: string;
  note: string;
  toneStyle: object;
};

type ScheduleItemProps = {
  time: string;
  title: string;
  detail: string;
};

function StatCard({label, value, note, toneStyle}: StatCardProps) {
  return (
    <View style={[styles.statCard, toneStyle]}>
      <Text style={styles.statLabel}>{label}</Text>
      <Text style={styles.statValue}>{value}</Text>
      <Text style={styles.statNote}>{note}</Text>
    </View>
  );
}

function ScheduleItem({time, title, detail}: ScheduleItemProps) {
  return (
    <View style={styles.scheduleRow}>
      <View style={styles.scheduleTimePill}>
        <Text style={styles.scheduleTime}>{time}</Text>
      </View>

      <View style={styles.scheduleTextWrap}>
        <Text style={styles.scheduleTitle}>{title}</Text>
        <Text style={styles.scheduleDetail}>{detail}</Text>
      </View>
    </View>
  );
}

export default function TechnicianDashboard() {
  const {user, logout} = useContext(AuthContext);
  const technicianName = user?.name || 'Technician';

  const showPlaceholderMessage = (title: string) => {
    Alert.alert(title, 'This dashboard action is ready for the next workflow.');
  };

  return (
    <SafeAreaView style={styles.safeArea}>
      <ScrollView
        contentContainerStyle={styles.contentContainer}
        showsVerticalScrollIndicator={false}>
        <View style={styles.heroCard}>
          <View style={styles.heroTopRow}>
            <View style={styles.heroTextWrap}>
              <Text style={styles.heroEyebrow}>Technician workspace</Text>
              <Text style={styles.heroTitle}>Welcome, {technicianName}</Text>
              <Text style={styles.heroSubtitle}>
                Review assigned jobs, check progress, and stay ready for the
                day&apos;s service schedule.
              </Text>
            </View>

            <View style={styles.statusBadge}>
              <Text style={styles.statusBadgeLabel}>Status</Text>
              <Text style={styles.statusBadgeValue}>On duty</Text>
            </View>
          </View>

          <View style={styles.identityRow}>
            <View style={styles.identityChip}>
              <Text style={styles.identityLabel}>Email</Text>
              <Text style={styles.identityValue}>
                {user?.email || 'No email available'}
              </Text>
            </View>
            <View style={styles.identityChip}>
              <Text style={styles.identityLabel}>Role</Text>
              <Text style={styles.identityValue}>{user?.role || 'technician'}</Text>
            </View>
          </View>
        </View>

        <AppCard style={styles.sectionCard}>
          <Text style={styles.sectionTitle}>Assigned jobs summary</Text>
          <Text style={styles.sectionSubtitle}>
            Keep a quick view of workload, progress, and focus areas for today.
          </Text>

          <View style={styles.summaryHighlight}>
            <Text style={styles.summaryHighlightLabel}>Today&apos;s workload</Text>
            <Text style={styles.summaryHighlightValue}>4 assigned service jobs</Text>
            <Text style={styles.summaryHighlightText}>
              Prioritize active visits, follow up on pending tasks, and close
              completed work before end of shift.
            </Text>
          </View>
        </AppCard>

        <AppCard style={styles.sectionCard}>
          <Text style={styles.sectionTitle}>Job stats</Text>
          <Text style={styles.sectionSubtitle}>
            A simple operational snapshot for pending and completed work.
          </Text>

          <View style={styles.statGrid}>
            <StatCard
              label="Pending"
              value="2 jobs"
              note="Waiting for visit completion or customer update."
              toneStyle={styles.pendingTone}
            />
            <StatCard
              label="Completed"
              value="2 jobs"
              note="Finished and ready for final confirmation."
              toneStyle={styles.completedTone}
            />
          </View>
        </AppCard>

        <AppCard style={styles.sectionCard}>
          <Text style={styles.sectionTitle}>Today&apos;s schedule</Text>
          <Text style={styles.sectionSubtitle}>
            Placeholder schedule cards can later be connected to live job data.
          </Text>

          <ScheduleItem
            time="09:00"
            title="Morning site visit"
            detail="Initial inspection and diagnostics for a customer request."
          />
          <ScheduleItem
            time="13:30"
            title="Follow-up maintenance"
            detail="Check service progress and confirm remaining action items."
          />
          <ScheduleItem
            time="16:00"
            title="Wrap-up window"
            detail="Update job notes, upload results, and prepare next tasks."
          />
        </AppCard>

        <AppCard style={styles.sectionCard}>
          <Text style={styles.sectionTitle}>Actions</Text>
          <Text style={styles.sectionSubtitle}>
            Quick technician tools for common daily tasks.
          </Text>

          <View style={styles.actionCardRow}>
            <Pressable
              onPress={() => showPlaceholderMessage('Assigned jobs')}
              style={({pressed}) => [styles.actionCard, pressed && styles.pressed]}>
              <Text style={styles.actionCardTitle}>View assigned jobs</Text>
              <Text style={styles.actionCardText}>
                Open your current workload overview.
              </Text>
            </Pressable>

            <Pressable
              onPress={() => showPlaceholderMessage('Availability updated')}
              style={({pressed}) => [styles.actionCard, pressed && styles.pressed]}>
              <Text style={styles.actionCardTitle}>Update availability</Text>
              <Text style={styles.actionCardText}>
                Keep your service status and readiness current.
              </Text>
            </Pressable>
          </View>

          <AppButton
            title="Refresh Dashboard"
            onPress={() => showPlaceholderMessage('Dashboard refreshed')}
          />
          <AppButton
            style={styles.buttonSpacing}
            title="Logout"
            variant="outline"
            onPress={logout}
          />
        </AppCard>
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safeArea: {
    backgroundColor: '#f4f7fb',
    flex: 1,
  },
  contentContainer: {
    padding: 20,
    paddingBottom: 28,
  },
  heroCard: {
    backgroundColor: '#eaf2ff',
    borderRadius: 28,
    marginBottom: 18,
    padding: 22,
  },
  heroTopRow: {
    marginBottom: 18,
  },
  heroTextWrap: {
    marginBottom: 16,
    paddingRight: 12,
  },
  heroEyebrow: {
    color: '#2563eb',
    fontSize: 13,
    fontWeight: '700',
    letterSpacing: 0.3,
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
    color: '#475569',
    fontSize: 15,
    lineHeight: 22,
  },
  statusBadge: {
    alignSelf: 'flex-start',
    backgroundColor: '#ffffff',
    borderRadius: 18,
    paddingHorizontal: 16,
    paddingVertical: 12,
  },
  statusBadgeLabel: {
    color: '#64748b',
    fontSize: 12,
    fontWeight: '700',
    marginBottom: 2,
    textTransform: 'uppercase',
  },
  statusBadgeValue: {
    color: '#0f172a',
    fontSize: 18,
    fontWeight: '700',
  },
  identityRow: {
    marginTop: 4,
  },
  identityChip: {
    backgroundColor: 'rgba(255, 255, 255, 0.72)',
    borderRadius: 18,
    marginBottom: 10,
    paddingHorizontal: 16,
    paddingVertical: 14,
  },
  identityLabel: {
    color: '#64748b',
    fontSize: 12,
    fontWeight: '700',
    marginBottom: 4,
    textTransform: 'uppercase',
  },
  identityValue: {
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
  summaryHighlight: {
    backgroundColor: '#f8fafc',
    borderColor: '#e2e8f0',
    borderRadius: 22,
    borderWidth: 1,
    padding: 18,
  },
  summaryHighlightLabel: {
    color: '#64748b',
    fontSize: 12,
    fontWeight: '700',
    marginBottom: 8,
    textTransform: 'uppercase',
  },
  summaryHighlightValue: {
    color: '#0f172a',
    fontSize: 22,
    fontWeight: '800',
    marginBottom: 8,
  },
  summaryHighlightText: {
    color: '#475569',
    fontSize: 14,
    lineHeight: 20,
  },
  statGrid: {
    marginTop: 2,
  },
  statCard: {
    borderRadius: 20,
    marginBottom: 12,
    padding: 16,
  },
  pendingTone: {
    backgroundColor: '#fff7ed',
  },
  completedTone: {
    backgroundColor: '#f0fdf4',
  },
  statLabel: {
    color: '#64748b',
    fontSize: 12,
    fontWeight: '700',
    marginBottom: 8,
    textTransform: 'uppercase',
  },
  statValue: {
    color: '#0f172a',
    fontSize: 22,
    fontWeight: '800',
    marginBottom: 6,
  },
  statNote: {
    color: '#475569',
    fontSize: 14,
    lineHeight: 20,
  },
  scheduleRow: {
    alignItems: 'center',
    backgroundColor: '#f8fafc',
    borderColor: '#e2e8f0',
    borderRadius: 20,
    borderWidth: 1,
    flexDirection: 'row',
    marginBottom: 12,
    padding: 14,
  },
  scheduleTimePill: {
    backgroundColor: '#dbeafe',
    borderRadius: 16,
    marginRight: 14,
    paddingHorizontal: 12,
    paddingVertical: 10,
  },
  scheduleTime: {
    color: '#1d4ed8',
    fontSize: 14,
    fontWeight: '700',
  },
  scheduleTextWrap: {
    flex: 1,
  },
  scheduleTitle: {
    color: '#0f172a',
    fontSize: 16,
    fontWeight: '700',
    marginBottom: 4,
  },
  scheduleDetail: {
    color: '#64748b',
    fontSize: 14,
    lineHeight: 20,
  },
  actionCardRow: {
    marginBottom: 16,
  },
  actionCard: {
    backgroundColor: '#f8fafc',
    borderColor: '#e2e8f0',
    borderRadius: 20,
    borderWidth: 1,
    marginBottom: 12,
    padding: 16,
  },
  actionCardTitle: {
    color: '#0f172a',
    fontSize: 16,
    fontWeight: '700',
    marginBottom: 6,
  },
  actionCardText: {
    color: '#64748b',
    fontSize: 14,
    lineHeight: 20,
  },
  buttonSpacing: {
    marginTop: 12,
  },
  pressed: {
    opacity: 0.88,
  },
});
