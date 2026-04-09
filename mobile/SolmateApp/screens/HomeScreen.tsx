import React, {useContext} from 'react';
import {
  Pressable,
  SafeAreaView,
  ScrollView,
  StyleSheet,
  Text,
  View,
} from 'react-native';

import {AppButton, AppCard} from '../components';
import {AuthContext} from '../src/context/AuthContext';

type QuickActionProps = {
  title: string;
  subtitle: string;
  accentColor: string;
  onPress: () => void;
};

function QuickActionCard({
  title,
  subtitle,
  accentColor,
  onPress,
}: QuickActionProps) {
  return (
    <Pressable onPress={onPress} style={({pressed}) => [pressed && styles.pressed]}>
      <View style={styles.quickActionCard}>
        <View style={styles.quickActionHeader}>
          <View style={[styles.quickActionAccent, {backgroundColor: accentColor}]} />
          <Text style={styles.quickActionBadge}>Open</Text>
        </View>
        <Text style={styles.quickActionTitle}>{title}</Text>
        <Text style={styles.quickActionSubtitle}>{subtitle}</Text>
        <View style={styles.quickActionFooter}>
          <Text style={styles.quickActionFooterText}>Tap to continue</Text>
        </View>
      </View>
    </Pressable>
  );
}

export default function HomeScreen({navigation}: any) {
  const {logout, user} = useContext(AuthContext);
  const customerName = user?.name || 'Customer';

  return (
    <SafeAreaView style={styles.safeArea}>
      <ScrollView
        contentContainerStyle={styles.contentContainer}
        showsVerticalScrollIndicator={false}>
        <View style={styles.heroCard}>
          <View style={styles.heroTextWrap}>
            <Text style={styles.eyebrow}>Customer dashboard</Text>
            <Text style={styles.heroTitle}>Welcome back, {customerName}</Text>
            <Text style={styles.heroSubtitle}>
              Manage your quotations, request inspections, and stay updated in
              one place.
            </Text>
          </View>

          <View style={styles.heroBadge}>
            <Text style={styles.heroBadgeLabel}>Account</Text>
            <Text style={styles.heroBadgeValue}>Active</Text>
          </View>
        </View>

        <AppCard style={styles.sectionCard}>
          <Text style={styles.sectionTitle}>Quick actions</Text>
          <Text style={styles.sectionSubtitle}>
            Jump into the tasks customers use most often.
          </Text>

          <View style={styles.highlightStrip}>
            <View>
              <Text style={styles.highlightTitle}>Customer tools</Text>
              <Text style={styles.highlightText}>
                Create quotations, submit inspection requests, and review
                everything tied to your account.
              </Text>
            </View>
          </View>

          <View style={styles.quickActionGrid}>
            <QuickActionCard
              title="Create Quotation"
              subtitle="Start a new quotation request for your account."
              accentColor="#93c5fd"
              // Use the stack navigator to open the quotation form screen.
              onPress={() => navigation.navigate('Quotations')}
            />
            <QuickActionCard
              title="My Quotations"
              subtitle="View the quotations already submitted on your account."
              accentColor="#fdba74"
              // Open the list screen that shows all quotations for this user.
              onPress={() => navigation.navigate('QuotationList')}
            />
            <QuickActionCard
              title="Request Inspection"
              subtitle="Submit a new inspection request for your property."
              accentColor="#86efac"
              onPress={() => navigation.navigate('InspectionRequest')}
            />
            <QuickActionCard
              title="My Inspection Requests"
              subtitle="Review the inspection requests already on your account."
              accentColor="#4ade80"
              onPress={() => navigation.navigate('InspectionRequestList')}
            />
          </View>

          <View style={styles.buttonStack}>
            {/* These buttons mirror the same navigation actions as the cards. */}
            <AppButton
              title="Create Quotation"
              onPress={() => navigation.navigate('Quotations')}
            />
            <AppButton
              style={styles.buttonSpacing}
              title="My Quotations"
              variant="secondary"
              onPress={() => navigation.navigate('QuotationList')}
            />
            <AppButton
              style={styles.buttonSpacing}
              title="Request Inspection"
              variant="outline"
              onPress={() => navigation.navigate('InspectionRequest')}
            />
            <AppButton
              style={styles.buttonSpacing}
              title="My Inspection Requests"
              variant="secondary"
              onPress={() => navigation.navigate('InspectionRequestList')}
            />
          </View>
        </AppCard>

        <AppCard style={styles.sectionCard}>
          <Text style={styles.sectionTitle}>Service summary</Text>
          <Text style={styles.sectionSubtitle}>
            A simple overview of what you can manage from your dashboard.
          </Text>

          <View style={styles.summaryGrid}>
            <View style={[styles.summaryCard, styles.summaryCardBlue]}>
              <Text style={styles.summaryLabel}>Quotations</Text>
              <Text style={styles.summaryValue}>Ready to review</Text>
              <Text style={styles.summaryNote}>
                Check pricing details and next steps.
              </Text>
            </View>

            <View style={[styles.summaryCard, styles.summaryCardGreen]}>
              <Text style={styles.summaryLabel}>Inspections</Text>
              <Text style={styles.summaryValue}>Ready to request</Text>
              <Text style={styles.summaryNote}>
                Submit site inspections and follow their current status.
              </Text>
            </View>

            <View style={[styles.summaryCard, styles.summaryCardCream]}>
              <Text style={styles.summaryLabel}>Support</Text>
              <Text style={styles.summaryValue}>Customer account active</Text>
              <Text style={styles.summaryNote}>
                Your dashboard is ready whenever you need it.
              </Text>
            </View>
          </View>
        </AppCard>

        <AppCard style={styles.sectionCard}>
          <Text style={styles.sectionTitle}>Recent activity</Text>
          <Text style={styles.sectionSubtitle}>
            Activity will appear here as you submit requests and receive
            quotations.
          </Text>

          <View style={styles.activityPlaceholder}>
            <Text style={styles.activityTitle}>No recent activity yet</Text>
            <Text style={styles.activityText}>
              Start by submitting an inspection request or checking your
              quotations to build your dashboard history.
            </Text>
          </View>
        </AppCard>

        <AppCard style={styles.logoutCard}>
          <Text style={styles.sectionTitle}>Account actions</Text>
          <Text style={styles.sectionSubtitle}>
            Use this when you are ready to end your session.
          </Text>

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
    backgroundColor: '#f5f7fb',
    flex: 1,
  },
  contentContainer: {
    padding: 20,
    paddingBottom: 28,
  },
  heroCard: {
    backgroundColor: '#dbeafe',
    borderRadius: 28,
    marginBottom: 18,
    overflow: 'hidden',
    padding: 22,
  },
  heroTextWrap: {
    marginBottom: 18,
    paddingRight: 28,
  },
  eyebrow: {
    color: '#1d4ed8',
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
  },
  heroBadge: {
    alignSelf: 'flex-start',
    backgroundColor: '#ffffff',
    borderRadius: 18,
    paddingHorizontal: 16,
    paddingVertical: 12,
  },
  heroBadgeLabel: {
    color: '#64748b',
    fontSize: 12,
    fontWeight: '600',
    marginBottom: 2,
    textTransform: 'uppercase',
  },
  heroBadgeValue: {
    color: '#0f172a',
    fontSize: 18,
    fontWeight: '700',
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
  quickActionGrid: {
    marginBottom: 18,
  },
  quickActionCard: {
    backgroundColor: '#f8fafc',
    borderColor: '#e2e8f0',
    borderRadius: 20,
    borderWidth: 1,
    marginBottom: 12,
    padding: 16,
  },
  quickActionHeader: {
    alignItems: 'center',
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 14,
  },
  quickActionAccent: {
    borderRadius: 999,
    height: 10,
    width: 56,
  },
  quickActionBadge: {
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
  quickActionTitle: {
    color: '#0f172a',
    fontSize: 17,
    fontWeight: '700',
    marginBottom: 6,
  },
  quickActionSubtitle: {
    color: '#475569',
    fontSize: 14,
    lineHeight: 20,
  },
  quickActionFooter: {
    borderTopColor: '#e2e8f0',
    borderTopWidth: 1,
    marginTop: 14,
    paddingTop: 12,
  },
  quickActionFooterText: {
    color: '#2563eb',
    fontSize: 13,
    fontWeight: '700',
  },
  buttonStack: {
    marginTop: 2,
  },
  buttonSpacing: {
    marginTop: 12,
  },
  highlightStrip: {
    backgroundColor: '#eff6ff',
    borderRadius: 18,
    marginBottom: 16,
    padding: 16,
  },
  highlightTitle: {
    color: '#1d4ed8',
    fontSize: 15,
    fontWeight: '700',
    marginBottom: 4,
  },
  highlightText: {
    color: '#334155',
    fontSize: 14,
    lineHeight: 20,
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
  summaryCardGreen: {
    backgroundColor: '#f0fdf4',
  },
  summaryCardCream: {
    backgroundColor: '#fff7ed',
  },
  summaryLabel: {
    color: '#64748b',
    fontSize: 13,
    fontWeight: '700',
    marginBottom: 8,
    textTransform: 'uppercase',
  },
  summaryValue: {
    color: '#0f172a',
    fontSize: 18,
    fontWeight: '700',
    marginBottom: 6,
  },
  summaryNote: {
    color: '#475569',
    fontSize: 14,
    lineHeight: 20,
  },
  activityPlaceholder: {
    backgroundColor: '#f8fafc',
    borderColor: '#e2e8f0',
    borderRadius: 20,
    borderStyle: 'dashed',
    borderWidth: 1,
    padding: 18,
  },
  activityTitle: {
    color: '#0f172a',
    fontSize: 16,
    fontWeight: '700',
    marginBottom: 8,
  },
  activityText: {
    color: '#64748b',
    fontSize: 14,
    lineHeight: 20,
  },
  logoutCard: {
    marginBottom: 8,
  },
  pressed: {
    opacity: 0.88,
  },
});
