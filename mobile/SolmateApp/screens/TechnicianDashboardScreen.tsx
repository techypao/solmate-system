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
import Icon from 'react-native-vector-icons/MaterialCommunityIcons';
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
import {getSolmateStatusColors, solmateColors} from '../src/theme/colors';
import TechnicianBottomNav from '../src/components/TechnicianBottomNav';
import {
  formatDisplayValue,
  formatServiceRequestStatus,
  normalizeRequestStatus,
} from '../src/utils/technicianRequests';

// ─── colour tokens that mirror the design ────────────────────────────────────
const NAVY = solmateColors.navy;
const GOLD = solmateColors.primary;
const BG = solmateColors.background;
const CARD = solmateColors.white;
const MUTED = solmateColors.muted;
const SHADOW = solmateColors.shadow;
const ICON_COLOR = '#1d2f6d';


type DashboardTask = {
  id: number;
  kind: 'inspection' | 'service';
  customerName: string;
  address: string;
  hideAddress?: boolean;
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

function parseTaskDate(value?: unknown) {
  const dateValue = formatDisplayValue(value, '');

  if (!dateValue) {
    return null;
  }

  const parsedDate = new Date(dateValue);

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

function formatTaskSchedule(value?: unknown) {
  const dateValue = formatDisplayValue(value, '');

  if (!dateValue) {
    return 'Schedule not set';
  }

  const parsedDate = parseTaskDate(dateValue);

  if (!parsedDate) {
    return dateValue;
  }

  return parsedDate.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  });
}

function formatTaskStatus(status?: unknown) {
  return formatServiceRequestStatus(status);
}

function getTaskStatusColors(status?: unknown) {
  return getSolmateStatusColors(normalizeRequestStatus(status));
}

function getTaskShortDetails(value?: unknown, fallback = 'No details provided.') {
  const trimmedValue = formatDisplayValue(value, '').trim();

  if (!trimmedValue) {
    return fallback;
  }

  return trimmedValue.length > 90
    ? `${trimmedValue.slice(0, 87).trimEnd()}...`
    : trimmedValue;
}

function buildInspectionTask(item: TechnicianInspectionRequest): DashboardTask {
  const isWalkinInspection = !item.user_id && !!formatDisplayValue(item.customer_name, '');

  return {
    id: item.id,
    kind: 'inspection',
    customerName: isWalkinInspection
      ? 'Walkin Customer'
      : formatDisplayValue(item.customer?.name, 'Unknown customer'),
    address: formatDisplayValue(item.address || item.address_details, 'Not provided'),
    hideAddress: isWalkinInspection,
    taskType: 'Inspection Request',
    scheduleLabel: formatTaskSchedule(item.date_needed),
    statusLabel: formatTaskStatus(item.status),
    statusValue: normalizeRequestStatus(item.status) || 'pending',
    shortDetails: getTaskShortDetails(item.details, 'No inspection details provided.'),
    scheduledAt: parseTaskDate(item.date_needed),
    rawInspection: item,
  };
}

function buildServiceTask(item: ServiceRequest): DashboardTask {
  const rawType = formatDisplayValue(item.request_type, 'Service');
  const hasInstallation = rawType.toLowerCase().includes('installation');
  const isManualInspection = rawType.toLowerCase().includes('manual inspection');

  return {
    id: item.id,
    kind: 'service',
    customerName: isManualInspection
      ? 'Walkin Customer'
      : formatDisplayValue(item.customer?.name, 'Unknown customer'),
    address: formatDisplayValue(item.address, 'Not provided'),
    hideAddress: isManualInspection,
    taskType: isManualInspection
      ? 'Inspection Request'
      : hasInstallation
        ? 'Installation Request'
        : 'Maintenance Request',
    scheduleLabel: formatTaskSchedule(item.date_needed),
    statusLabel: formatTaskStatus(item.status),
    statusValue: normalizeRequestStatus(item.status) || 'pending',
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
      {!item.hideAddress ? (
        <Text style={s.taskAddress}>Address: {item.address}</Text>
      ) : null}
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

// ─── main screen ─────────────────────────────────────────────────────────────
export default function TechnicianDashboardScreen({navigation}: any) {
  const {user} = useContext(AuthContext);
  const technicianName = user?.name || 'Technician';
  const profilePictureUrl = getProfilePictureUrl(user?.profile_picture);

  const [loading, setLoading] = useState(true);
  const [unreadCount, setUnreadCount] = useState(0);
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
      const assignedRequests = Array.isArray(requests) ? requests : [];
      const assignedServiceRequests = Array.isArray(serviceRequests)
        ? serviceRequests
        : [];

      setInspectionTasks(assignedRequests);
      setServiceTasks(assignedServiceRequests);

      const assigned = assignedRequests.filter(
        request => normalizeRequestStatus(request.status) === 'assigned',
      ).length;
      const inProgress = assignedRequests.filter(
        request => normalizeRequestStatus(request.status) === 'in_progress',
      ).length;
      const completed = assignedRequests.filter(
        request => normalizeRequestStatus(request.status) === 'completed',
      ).length;

      setRequestCounts({
        total: assignedRequests.length,
        assigned,
        inProgress,
        completed,
      });
      setServiceTotal(assignedServiceRequests.length);
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
                <Icon name="bell-outline" size={22} color={ICON_COLOR} />
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
                <Icon name="bell-outline" size={22} color={ICON_COLOR} />
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

          <Text style={s.sectionTitle}>Today's Tasks</Text>
          {loading ? (
            <View style={s.sectionLoadingCard}>
              <ActivityIndicator color={NAVY} size="small" />
              <Text style={s.sectionLoadingText}>Loading today's tasks...</Text>
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

      <TechnicianBottomNav activeTab="Home" />
    </View>
  );
}

// ─── icon styles ─────────────────────────────────────────────────────────────;

// ─── bottom nav styles ────────────────────────────────────────────────────────;

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
    paddingBottom: 90,
  },

  // header
  headerRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginBottom: 12,
    paddingHorizontal: 16,
    paddingVertical: 14,
    borderRadius: 22,
    backgroundColor: solmateColors.white,
    borderWidth: 1,
    borderColor: solmateColors.border,
    shadowColor: solmateColors.shadow,
    shadowOffset: {width: 0, height: 8},
    shadowOpacity: 0.08,
    shadowRadius: 18,
    elevation: 3,
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
    backgroundColor: solmateColors.backgroundSoft,
    borderWidth: 1,
    borderColor: 'rgba(32, 167, 201, 0.14)',
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
    borderTopColor: '#D4E0F2',
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
