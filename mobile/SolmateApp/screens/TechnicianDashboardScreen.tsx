import React, {useCallback, useContext, useState} from 'react';
import {
  ActivityIndicator,
  Image,
  Pressable,
  SafeAreaView,
  ScrollView,
  StyleSheet,
  Text,
  View,
} from 'react-native';
import {useFocusEffect} from '@react-navigation/native';
import {AuthContext} from '../src/context/AuthContext';
import {getUnreadNotificationCount} from '../src/services/notificationApi';
import {
  getAssignedInspectionRequests,
  TechnicianInspectionRequest,
} from '../src/services/technicianApi';
import {
  getTechnicianServiceRequests,
  ServiceRequest,
} from '../src/services/serviceRequestApi';
import {getProfilePictureUrl, getUserInitial} from '../src/utils/profilePicture';

// ─── colour tokens that mirror the design ────────────────────────────────────
const NAVY   = '#152a4a';
const GOLD   = '#d4a017';
const BG     = '#dde5f4';   // soft lavender-blue page background
const CARD   = '#ffffff';
const MUTED  = '#64748b';
const SHADOW = '#8a9bbd';

// ─── small icon stand-ins (unicode shapes) ───────────────────────────────────
// The design shows a tiny box-chart icon for Pending Reports and a calendar
// grid icon for Today's Schedule. We approximate with View-drawn shapes since
// react-native-vector-icons requires extra native linking steps.
function BellIcon() {
  return (
    <View style={icon.bellWrap}>
      {/* bell body */}
      <View style={icon.bellBody} />
      {/* bell clapper */}
      <View style={icon.bellClapper} />
    </View>
  );
}

function HomeIcon({active}: {active?: boolean}) {
  const c = active ? NAVY : MUTED;
  return (
    <Text style={{fontSize: 20, color: c, lineHeight: 22, textAlign: 'center'}}>{'\u2302'}</Text>
  );
}

function InspectIcon({active}: {active?: boolean}) {
  return (
    <View style={nav.iconWrap}>
      <View style={[nav.listBox, active && nav.activeShape]}>
        <View style={[nav.listLine, active && nav.activeLine]} />
        <View style={[nav.listLine, {width: 12}, active && nav.activeLine]} />
        <View style={[nav.listLine, active && nav.activeLine]} />
      </View>
    </View>
  );
}

function ServicesIcon({active}: {active?: boolean}) {
  return (
    <View style={nav.iconWrap}>
      <View style={[nav.gear, active && nav.activeShape]}>
        <View style={[nav.gearInner, active && nav.activeShape]} />
      </View>
    </View>
  );
}

function ProfileIcon({active}: {active?: boolean}) {
  return (
    <View style={nav.iconWrap}>
      <View style={[nav.profileHead, active && nav.activeShape]} />
      <View style={[nav.profileBody, active && nav.activeShape]} />
    </View>
  );
}

// ─── bottom nav bar ──────────────────────────────────────────────────────────
type Tab = 'Home' | 'Inspections' | 'Services' | 'Profile';

type DashboardTask = {
  id: number;
  kind: 'inspection' | 'service';
  customerName: string;
  address: string;
  taskType: string;
  scheduleLabel: string;
  statusLabel: string;
  statusValue: string;
  shortDetails: string;
  scheduledAt: Date | null;
  rawInspection?: TechnicianInspectionRequest;
  rawService?: ServiceRequest;
};

function startOfDay(date: Date) {
  return new Date(date.getFullYear(), date.getMonth(), date.getDate());
}

function parseTaskDate(value?: string | null) {
  if (!value) {
    return null;
  }

  const parsedDate = new Date(value);

  if (Number.isNaN(parsedDate.getTime())) {
    return null;
  }

  return parsedDate;
}

function isSameDay(a: Date, b: Date) {
  return (
    a.getFullYear() === b.getFullYear() &&
    a.getMonth() === b.getMonth() &&
    a.getDate() === b.getDate()
  );
}

function formatTaskSchedule(value?: string | null) {
  if (!value) {
    return 'Schedule not set';
  }

  const parsedDate = parseTaskDate(value);

  if (!parsedDate) {
    return value;
  }

  return parsedDate.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  });
}

function formatTaskStatus(status?: string | null) {
  switch ((status || '').toLowerCase()) {
    case 'assigned':
      return 'Assigned';
    case 'in_progress':
      return 'In Progress';
    case 'completed':
      return 'Completed';
    case 'pending':
      return 'Pending';
    default:
      return 'Pending';
  }
}

function getTaskStatusColors(status?: string | null) {
  switch ((status || '').toLowerCase()) {
    case 'assigned':
      return {
        backgroundColor: '#fef3c7',
        textColor: '#b45309',
      };
    case 'in_progress':
      return {
        backgroundColor: '#dbeafe',
        textColor: '#1d4ed8',
      };
    case 'completed':
      return {
        backgroundColor: '#dcfce7',
        textColor: '#166534',
      };
    default:
      return {
        backgroundColor: '#eef2ff',
        textColor: '#4338ca',
      };
  }
}

function getTaskShortDetails(value?: string | null, fallback = 'No details provided.') {
  const trimmedValue = String(value || '').trim();

  if (!trimmedValue) {
    return fallback;
  }

  return trimmedValue.length > 90
    ? `${trimmedValue.slice(0, 87).trimEnd()}...`
    : trimmedValue;
}

function buildInspectionTask(item: TechnicianInspectionRequest): DashboardTask {
  return {
    id: item.id,
    kind: 'inspection',
    customerName: item.customer?.name || 'Unknown customer',
    address: item.address || 'Not provided',
    taskType: 'Inspection Request',
    scheduleLabel: formatTaskSchedule(item.date_needed),
    statusLabel: formatTaskStatus(item.status),
    statusValue: String(item.status || 'pending'),
    shortDetails: getTaskShortDetails(item.details, 'No inspection details provided.'),
    scheduledAt: parseTaskDate(item.date_needed),
    rawInspection: item,
  };
}

function buildServiceTask(item: ServiceRequest): DashboardTask {
  const rawType = String(item.request_type || 'Service').trim();
  const hasInstallation = rawType.toLowerCase().includes('installation');

  return {
    id: item.id,
    kind: 'service',
    customerName: item.customer?.name || 'Unknown customer',
    address: item.address || 'Not provided',
    taskType: hasInstallation ? 'Installation Request' : 'Maintenance Request',
    scheduleLabel: formatTaskSchedule(item.date_needed),
    statusLabel: formatTaskStatus(item.status),
    statusValue: String(item.status || 'pending'),
    shortDetails: getTaskShortDetails(item.details, 'No service details provided.'),
    scheduledAt: parseTaskDate(item.date_needed),
    rawService: item,
  };
}

function sortTasksByNearestDate(tasks: DashboardTask[]) {
  return [...tasks].sort((leftTask, rightTask) => {
    const leftTime = leftTask.scheduledAt?.getTime() ?? Number.MAX_SAFE_INTEGER;
    const rightTime = rightTask.scheduledAt?.getTime() ?? Number.MAX_SAFE_INTEGER;

    if (leftTime !== rightTime) {
      return leftTime - rightTime;
    }

    return leftTask.id - rightTask.id;
  });
}

function TaskCard({
  item,
  onPress,
}: {
  item: DashboardTask;
  onPress: () => void;
}) {
  const statusColors = getTaskStatusColors(item.statusValue);

  return (
    <Pressable
      onPress={onPress}
      style={({pressed}) => [s.taskCard, pressed && s.pressed]}>
      <View style={s.taskTopRow}>
        <View style={s.taskTypePill}>
          <Text style={s.taskTypePillText}>{item.taskType}</Text>
        </View>
        <View
          style={[
            s.taskStatusBadge,
            {backgroundColor: statusColors.backgroundColor},
          ]}>
          <Text
            style={[
              s.taskStatusText,
              {color: statusColors.textColor},
            ]}>
            {item.statusLabel}
          </Text>
        </View>
      </View>

      <Text style={s.taskCustomer}>{item.customerName}</Text>
      <Text style={s.taskAddress}>Address: {item.address}</Text>
      <Text style={s.taskDate}>Scheduled: {item.scheduleLabel}</Text>
      <Text style={s.taskDetails}>{item.shortDetails}</Text>

      <View style={s.taskFooter}>
        <Text style={s.taskFooterText}>View Details</Text>
        <Text style={s.chevron}>›</Text>
      </View>
    </Pressable>
  );
}

function EmptyTaskState({message}: {message: string}) {
  return (
    <View style={s.emptyTaskCard}>
      <Text style={s.emptyTaskText}>{message}</Text>
    </View>
  );
}

function BottomNav({active, onPress}: {active: Tab; onPress: (t: Tab) => void}) {
  const tabs: {key: Tab; label: string; Icon: React.FC<{active?: boolean}>}[] = [
    {key: 'Home',        label: 'Home',        Icon: HomeIcon},
    {key: 'Inspections', label: 'Inspections', Icon: InspectIcon},
    {key: 'Services',    label: 'Services',    Icon: ServicesIcon},
    {key: 'Profile',     label: 'Profile',     Icon: ProfileIcon},
  ];

  return (
    <View style={nav.bar}>
      {tabs.map(({key, label, Icon}) => {
        const isActive = active === key;
        return (
          <Pressable
            key={key}
            style={nav.tab}
            onPress={() => onPress(key)}>
            <Icon active={isActive} />
            <Text style={[nav.label, isActive && nav.labelActive]}>{label}</Text>
          </Pressable>
        );
      })}
    </View>
  );
}

// ─── main screen ─────────────────────────────────────────────────────────────
export default function TechnicianDashboardScreen({navigation}: any) {
  const {user} = useContext(AuthContext);
  const technicianName = user?.name || 'Technician';
  const profilePictureUrl = getProfilePictureUrl(user?.profile_picture);

  const [loading, setLoading] = useState(true);
  const [unreadCount, setUnreadCount] = useState(0);
  const [activeTab, setActiveTab] = useState<Tab>('Home');
  const [inspectionTasks, setInspectionTasks] = useState<
    TechnicianInspectionRequest[]
  >([]);
  const [serviceTasks, setServiceTasks] = useState<ServiceRequest[]>([]);
  const [requestCounts, setRequestCounts] = useState({
    total: 0,
    assigned: 0,
    inProgress: 0,
    completed: 0,
  });
  const [serviceTotal, setServiceTotal] = useState<number | null>(null);

  const loadDashboard = useCallback(async () => {
    try {
      setLoading(true);
      const [requests, serviceRequests] = await Promise.all([
        getAssignedInspectionRequests(),
        getTechnicianServiceRequests(),
      ]);
      setInspectionTasks(Array.isArray(requests) ? requests : []);
      setServiceTasks(Array.isArray(serviceRequests) ? serviceRequests : []);
      const assigned   = requests.filter(r => r.status === 'assigned').length;
      const inProgress = requests.filter(r => r.status === 'in_progress').length;
      const completed  = requests.filter(r => r.status === 'completed').length;
      setRequestCounts({total: requests.length, assigned, inProgress, completed});
      setServiceTotal(serviceRequests.length);
    } catch {
      setInspectionTasks([]);
      setServiceTasks([]);
      setRequestCounts({total: 0, assigned: 0, inProgress: 0, completed: 0});
      setServiceTotal(null);
    } finally {
      setLoading(false);
    }
  }, []);

  const loadUnreadCount = useCallback(async () => {
    try {
      const count = await getUnreadNotificationCount();
      setUnreadCount(count);
    } catch {
      setUnreadCount(0);
    }
  }, []);

  useFocusEffect(useCallback(() => { loadDashboard(); }, [loadDashboard]));
  useFocusEffect(useCallback(() => { loadUnreadCount(); }, [loadUnreadCount]));

  const normalizedTasks = sortTasksByNearestDate([
    ...inspectionTasks.map(buildInspectionTask),
    ...serviceTasks.map(buildServiceTask),
  ]);
  const today = startOfDay(new Date());
  const todaysTasks = normalizedTasks.filter(task =>
    task.scheduledAt ? isSameDay(task.scheduledAt, today) : false,
  );
  const upcomingTasks = normalizedTasks.filter(task =>
    task.scheduledAt ? startOfDay(task.scheduledAt).getTime() > today.getTime() : false,
  );

  function handleTabPress(tab: Tab) {
    setActiveTab(tab);
    if (tab === 'Inspections') {navigation.navigate('AssignedInspectionRequests');}
    if (tab === 'Services')    {navigation.navigate('TechnicianServiceRequests');}
    if (tab === 'Profile')     {navigation.navigate('TechnicianSettings');}
  }

  function openTask(task: DashboardTask) {
    if (task.kind === 'inspection' && task.rawInspection) {
      navigation.navigate('InspectionDetails', {
        inspectionRequestId: task.rawInspection.id,
        initialInspectionRequest: task.rawInspection,
      });
      return;
    }

    if (task.kind === 'service' && task.rawService) {
      navigation.navigate('TechnicianServiceRequestDetail', {
        serviceRequestId: task.rawService.id,
        initialServiceRequest: task.rawService,
        mode: 'technician',
      });
    }
  }

  return (
    <View style={s.root}>
      <SafeAreaView style={s.safe}>
        <ScrollView
          contentContainerStyle={s.scroll}
          showsVerticalScrollIndicator={false}>

          {/* ── top header row ── */}
          <View style={s.headerRow}>
            <View style={s.brandRow}>
              <Text style={s.brandSol}>Sol</Text>
              <Text style={s.brandGold}>Mate</Text>
            </View>
            <View style={s.headerActions}>
              {/* bell icon with unread badge */}
              <Pressable
                onPress={() => navigation.navigate('TechnicianNotifications')}
                style={s.bellBtn}>
                <BellIcon />
                {unreadCount > 0 && (
                  <View style={s.badge}>
                    <Text style={s.badgeText}>
                      {unreadCount > 99 ? '99+' : unreadCount}
                    </Text>
                  </View>
                )}
              </Pressable>
              {/* avatar */}
              <Pressable
                onPress={() => navigation.navigate('TechnicianSettings')}
                style={s.avatarBtn}>
                <View style={s.avatarCircle}>
                  {profilePictureUrl ? (
                    <Image source={{uri: profilePictureUrl}} style={s.avatarImage} />
                  ) : (
                    <Text style={s.avatarInitial}>
                      {getUserInitial(user?.name, 'T')}
                    </Text>
                  )}
                </View>
              </Pressable>
            </View>
          </View>

          {/* ── welcome ── */}
          <Text style={s.welcomeTitle}>
            Welcome back,{' '}
            <Text style={s.welcomeName}>{technicianName}</Text>
          </Text>
          <Text style={s.welcomeSub}>Your assigned tasks at a glance.</Text>

          {/* ── Summary section ── */}
          <Text style={s.sectionTitle}>Summary</Text>
          <View style={s.summaryRow}>
            {/* Inspection Requests */}
            <View style={[s.summaryCard, {marginRight: 8}]}>
              <Text style={s.summaryLabel}>Inspection Requests</Text>
              {loading
                ? <ActivityIndicator color={NAVY} size="small" style={{marginTop: 4}} />
                : <Text style={s.summaryCount}>{requestCounts.total}</Text>
              }
            </View>
            <View style={[s.summaryCard, {marginLeft: 8}]}>
              <Text style={s.summaryLabel}>Service Requests</Text>
              {loading
                ? <ActivityIndicator color={NAVY} size="small" style={{marginTop: 4}} />
                : <Text style={s.summaryCount}>{serviceTotal ?? '–'}</Text>
              }
            </View>
          </View>

          {/* ── Notifications card ── */}
          <Pressable
            style={({pressed}) => [s.infoCard, pressed && s.pressed]}
            onPress={() => navigation.navigate('TechnicianNotifications')}>
            <View style={s.infoCardLeft}>
              <View style={[s.iconBox, s.bellIconBox]}>
                <BellIcon />
                {unreadCount > 0 && (
                  <View style={s.inlineBadge}>
                    <Text style={s.inlineBadgeText}>
                      {unreadCount > 99 ? '99+' : unreadCount}
                    </Text>
                  </View>
                )}
              </View>
              <View>
                <Text style={s.infoCardTitle}>Notifications</Text>
                <Text style={s.infoCardSub}>
                  {unreadCount > 0
                    ? `${unreadCount} unread`
                    : "You're all caught up"}
                </Text>
              </View>
            </View>
            <Text style={s.chevron}>›</Text>
          </Pressable>

          {/* ── Quick Actions ── */}
          <Text style={s.sectionTitle}>Quick Actions</Text>

          <Pressable
            style={({pressed}) => [s.actionCard, pressed && s.pressed]}
            onPress={() => navigation.navigate('AssignedInspectionRequests')}>
            <View style={s.actionLeft}>
              <Text style={s.actionTitle}>View Inspections</Text>
              <Text style={s.actionSub}>See assigned inspections</Text>
            </View>
            <Text style={s.chevron}>›</Text>
          </Pressable>

          <Pressable
            style={({pressed}) => [s.actionCard, pressed && s.pressed]}
            onPress={() => navigation.navigate('TechnicianServiceRequests')}>
            <View style={s.actionLeft}>
              <Text style={s.actionTitle}>View Services</Text>
              <Text style={s.actionSub}>See assigned service requests</Text>
            </View>
            <Text style={s.chevron}>›</Text>
          </Pressable>

          <Text style={s.sectionTitle}>Today&apos;s Tasks</Text>
          {loading ? (
            <View style={s.sectionLoadingCard}>
              <ActivityIndicator color={NAVY} size="small" />
              <Text style={s.sectionLoadingText}>Loading today&apos;s tasks...</Text>
            </View>
          ) : todaysTasks.length > 0 ? (
            todaysTasks.map(task => (
              <TaskCard
                key={`today-${task.kind}-${task.id}`}
                item={task}
                onPress={() => openTask(task)}
              />
            ))
          ) : (
            <EmptyTaskState message="No tasks scheduled for today." />
          )}

          <Text style={s.sectionTitle}>Upcoming Tasks</Text>
          {loading ? (
            <View style={s.sectionLoadingCard}>
              <ActivityIndicator color={NAVY} size="small" />
              <Text style={s.sectionLoadingText}>Loading upcoming tasks...</Text>
            </View>
          ) : upcomingTasks.length > 0 ? (
            upcomingTasks.map(task => (
              <TaskCard
                key={`upcoming-${task.kind}-${task.id}`}
                item={task}
                onPress={() => openTask(task)}
              />
            ))
          ) : (
            <EmptyTaskState message="No upcoming tasks." />
          )}


        </ScrollView>
      </SafeAreaView>

      {/* ── bottom nav ── */}
      <BottomNav active={activeTab} onPress={handleTabPress} />
    </View>
  );
}

// ─── icon styles ─────────────────────────────────────────────────────────────
const icon = StyleSheet.create({
  bellWrap: {
    width: 20,
    height: 22,
    alignItems: 'center',
  },
  bellBody: {
    width: 16,
    height: 14,
    borderRadius: 8,
    borderWidth: 2.5,
    borderColor: NAVY,
    marginTop: 2,
  },
  bellClapper: {
    width: 6,
    height: 3,
    borderBottomLeftRadius: 4,
    borderBottomRightRadius: 4,
    backgroundColor: NAVY,
    marginTop: 1,
  },
  wrap: {
    flexDirection: 'row',
    alignItems: 'flex-end',
    height: 20,
    width: 20,
    gap: 2,
  },
  bar: {
    width: 5,
    backgroundColor: NAVY,
    borderRadius: 2,
  },
  calWrap: {
    width: 20,
    height: 20,
    borderRadius: 4,
    borderWidth: 1.5,
    borderColor: NAVY,
    overflow: 'hidden',
  },
  calHeader: {
    height: 5,
    backgroundColor: NAVY,
  },
  calGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    padding: 2,
    gap: 2,
  },
  calDot: {
    width: 4,
    height: 4,
    borderRadius: 1,
    backgroundColor: NAVY,
    opacity: 0.55,
  },
});

// ─── bottom nav styles ────────────────────────────────────────────────────────
const nav = StyleSheet.create({
  bar: {
    flexDirection: 'row',
    backgroundColor: '#f2f4f8',
    borderTopWidth: 1,
    borderTopColor: '#dde2ec',
    paddingBottom: 8,
    paddingTop: 8,
  },
  tab: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    gap: 3,
  },
  label: {
    fontSize: 10,
    color: MUTED,
    fontWeight: '500',
  },
  labelActive: {
    color: NAVY,
    fontWeight: '700',
  },
  iconWrap: {
    width: 24,
    height: 22,
    alignItems: 'center',
    justifyContent: 'flex-end',
  },
  // house
  roof: {
    width: 0,
    height: 0,
    borderLeftWidth: 8,
    borderRightWidth: 8,
    borderBottomWidth: 10,
    borderLeftColor: 'transparent',
    borderRightColor: 'transparent',
    borderBottomColor: MUTED,
    marginBottom: 0,
  },
  houseBody: {
    width: 14,
    height: 9,
    backgroundColor: MUTED,
    borderRadius: 1,
  },
  // list
  listBox: {
    width: 18,
    height: 20,
    backgroundColor: MUTED,
    borderRadius: 3,
    alignItems: 'flex-start',
    justifyContent: 'center',
    paddingHorizontal: 3,
    gap: 3,
  },
  listLine: {
    height: 2,
    width: 10,
    backgroundColor: '#f2f4f8',
    borderRadius: 1,
  },
  // gear
  gear: {
    width: 20,
    height: 20,
    borderRadius: 10,
    borderWidth: 3,
    borderColor: MUTED,
    alignItems: 'center',
    justifyContent: 'center',
  },
  gearInner: {
    width: 8,
    height: 8,
    borderRadius: 4,
    backgroundColor: MUTED,
  },
  // profile
  profileHead: {
    width: 10,
    height: 10,
    borderRadius: 5,
    backgroundColor: MUTED,
    marginBottom: 2,
  },
  profileBody: {
    width: 16,
    height: 8,
    borderTopLeftRadius: 8,
    borderTopRightRadius: 8,
    backgroundColor: MUTED,
  },
  activeShape: {
    borderColor: NAVY,
    backgroundColor: NAVY,
    borderBottomColor: NAVY,
  },
  activeLine: {
    backgroundColor: '#f2f4f8',
  },
});

// ─── main styles ─────────────────────────────────────────────────────────────
const s = StyleSheet.create({
  root: {
    flex: 1,
    backgroundColor: BG,
  },
  safe: {
    flex: 1,
    backgroundColor: BG,
  },
  scroll: {
    paddingHorizontal: 18,
    paddingTop: 10,
    paddingBottom: 20,
  },

  // header
  headerRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginBottom: 10,
  },
  brandRow: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  brandSol: {
    fontSize: 22,
    fontWeight: '800',
    color: NAVY,
  },
  brandGold: {
    fontSize: 22,
    fontWeight: '800',
    color: GOLD,
  },
  headerActions: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
  },
  bellBtn: {
    width: 38,
    height: 38,
    borderRadius: 19,
    backgroundColor: '#e8edf7',
    alignItems: 'center',
    justifyContent: 'center',
  },
  badge: {
    position: 'absolute',
    top: -2,
    right: -2,
    minWidth: 18,
    height: 18,
    borderRadius: 9,
    backgroundColor: '#e53e3e',
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: 3,
    borderWidth: 1.5,
    borderColor: BG,
  },
  badgeText: {
    color: '#fff',
    fontSize: 10,
    fontWeight: '800',
    lineHeight: 12,
  },
  bellIconBox: {
    position: 'relative',
  },
  inlineBadge: {
    position: 'absolute',
    top: -4,
    right: -4,
    minWidth: 17,
    height: 17,
    borderRadius: 9,
    backgroundColor: '#e53e3e',
    alignItems: 'center',
    justifyContent: 'center',
    paddingHorizontal: 3,
    borderWidth: 1.5,
    borderColor: CARD,
  },
  inlineBadgeText: {
    color: '#fff',
    fontSize: 9,
    fontWeight: '800',
    lineHeight: 11,
  },
  avatarBtn: {
    padding: 2,
  },
  avatarCircle: {
    width: 38,
    height: 38,
    borderRadius: 19,
    backgroundColor: NAVY,
    alignItems: 'center',
    justifyContent: 'center',
    overflow: 'hidden',
  },
  avatarImage: {
    width: '100%',
    height: '100%',
    borderRadius: 19,
  },
  avatarInitial: {
    color: '#fff',
    fontSize: 16,
    fontWeight: '700',
  },

  // welcome
  welcomeTitle: {
    fontSize: 24,
    fontWeight: '800',
    color: NAVY,
    lineHeight: 30,
  },
  welcomeName: {
    color: NAVY,
    fontWeight: '800',
  },
  welcomeSub: {
    fontSize: 13,
    color: MUTED,
    marginTop: 2,
    marginBottom: 16,
  },

  // section headings
  sectionTitle: {
    fontSize: 17,
    fontWeight: '800',
    color: NAVY,
    marginBottom: 10,
    marginTop: 4,
  },

  // summary cards row
  summaryRow: {
    flexDirection: 'row',
    marginBottom: 14,
  },
  summaryCard: {
    flex: 1,
    backgroundColor: CARD,
    borderRadius: 16,
    paddingVertical: 14,
    paddingHorizontal: 14,
    shadowColor: SHADOW,
    shadowOffset: {width: 0, height: 4},
    shadowOpacity: 0.10,
    shadowRadius: 8,
    elevation: 2,
  },
  summaryLabel: {
    fontSize: 12,
    color: MUTED,
    fontWeight: '500',
    marginBottom: 6,
  },
  summaryCount: {
    fontSize: 30,
    fontWeight: '800',
    color: NAVY,
    lineHeight: 34,
  },

  // info cards (Pending Reports / Today's Schedule)
  infoCard: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    backgroundColor: CARD,
    borderRadius: 16,
    paddingVertical: 14,
    paddingHorizontal: 16,
    marginBottom: 10,
    shadowColor: SHADOW,
    shadowOffset: {width: 0, height: 3},
    shadowOpacity: 0.09,
    shadowRadius: 7,
    elevation: 2,
  },
  infoCardLeft: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
  },
  iconBox: {
    width: 40,
    height: 40,
    borderRadius: 10,
    backgroundColor: '#eef2fb',
    alignItems: 'center',
    justifyContent: 'center',
  },
  infoCardTitle: {
    fontSize: 15,
    fontWeight: '700',
    color: NAVY,
    marginBottom: 2,
  },
  infoCardSub: {
    fontSize: 13,
    color: MUTED,
  },

  // chevron
  chevron: {
    fontSize: 22,
    color: MUTED,
    fontWeight: '400',
    lineHeight: 24,
  },

  // action cards (Quick Actions)
  actionCard: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    backgroundColor: CARD,
    borderRadius: 16,
    paddingVertical: 14,
    paddingHorizontal: 16,
    marginBottom: 10,
    shadowColor: SHADOW,
    shadowOffset: {width: 0, height: 3},
    shadowOpacity: 0.09,
    shadowRadius: 7,
    elevation: 2,
  },
  actionLeft: {
    flex: 1,
    marginRight: 8,
  },
  actionTitle: {
    fontSize: 15,
    fontWeight: '700',
    color: NAVY,
    marginBottom: 2,
  },
  actionSub: {
    fontSize: 13,
    color: MUTED,
  },
  taskCard: {
    backgroundColor: CARD,
    borderRadius: 16,
    paddingVertical: 14,
    paddingHorizontal: 16,
    marginBottom: 10,
    shadowColor: SHADOW,
    shadowOffset: {width: 0, height: 3},
    shadowOpacity: 0.09,
    shadowRadius: 7,
    elevation: 2,
  },
  taskTopRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    gap: 10,
    marginBottom: 10,
  },
  taskTypePill: {
    backgroundColor: '#eef2fb',
    borderRadius: 999,
    paddingHorizontal: 10,
    paddingVertical: 5,
  },
  taskTypePillText: {
    color: NAVY,
    fontSize: 11,
    fontWeight: '700',
  },
  taskStatusBadge: {
    borderRadius: 999,
    paddingHorizontal: 10,
    paddingVertical: 5,
  },
  taskStatusText: {
    fontSize: 11,
    fontWeight: '700',
  },
  taskCustomer: {
    fontSize: 16,
    fontWeight: '700',
    color: NAVY,
    marginBottom: 4,
  },
  taskDate: {
    fontSize: 13,
    color: MUTED,
    marginBottom: 4,
  },
  taskAddress: {
    fontSize: 13,
    color: NAVY,
    fontWeight: '600',
    marginBottom: 6,
  },
  taskDetails: {
    fontSize: 13,
    color: MUTED,
    lineHeight: 19,
  },
  taskFooter: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginTop: 12,
    paddingTop: 12,
    borderTopWidth: 1,
    borderTopColor: '#edf1f7',
  },
  taskFooterText: {
    color: NAVY,
    fontSize: 13,
    fontWeight: '700',
  },
  emptyTaskCard: {
    backgroundColor: '#edf3fb',
    borderRadius: 16,
    paddingVertical: 18,
    paddingHorizontal: 16,
    marginBottom: 10,
    borderWidth: 1,
    borderColor: '#d8e4f4',
  },
  emptyTaskText: {
    color: MUTED,
    fontSize: 13,
    lineHeight: 19,
  },
  sectionLoadingCard: {
    backgroundColor: CARD,
    borderRadius: 16,
    paddingVertical: 18,
    paddingHorizontal: 16,
    marginBottom: 10,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
    shadowColor: SHADOW,
    shadowOffset: {width: 0, height: 3},
    shadowOpacity: 0.09,
    shadowRadius: 7,
    elevation: 2,
  },
  sectionLoadingText: {
    color: MUTED,
    fontSize: 13,
    fontWeight: '500',
  },

  pressed: {
    opacity: 0.82,
  },
});
