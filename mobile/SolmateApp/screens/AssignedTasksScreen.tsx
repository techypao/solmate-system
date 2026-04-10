import React, {useCallback, useState} from 'react';
import {
  ActivityIndicator,
  FlatList,
  RefreshControl,
  SafeAreaView,
  StyleSheet,
  Text,
  View,
} from 'react-native';
import {useFocusEffect} from '@react-navigation/native';

import {AppButton} from '../components';
import TechnicianTaskCard from '../components/TechnicianTaskCard';
import {ApiError} from '../src/services/api';
import {
  getAssignedServiceRequests,
  TechnicianServiceRequest,
} from '../src/services/technicianApi';

function getFriendlyErrorMessage(error: unknown) {
  if (error instanceof ApiError) {
    if (error.status === 401) {
      return 'Your session has expired. Please log in again.';
    }

    return error.message;
  }

  return 'Could not load your assigned service requests right now.';
}

export default function AssignedTasksScreen({navigation, route}: any) {
  const statusFilter = route?.params?.statusFilter;

  const [tasks, setTasks] = useState<TechnicianServiceRequest[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [errorMessage, setErrorMessage] = useState('');

  const loadTasks = useCallback(async (showLoadingState = false) => {
    try {
      if (showLoadingState) {
        setLoading(true);
      }

      setErrorMessage('');
      const data = await getAssignedServiceRequests();
      setTasks(Array.isArray(data) ? data : []);
    } catch (error) {
      setTasks([]);
      setErrorMessage(getFriendlyErrorMessage(error));
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, []);

  useFocusEffect(
    useCallback(() => {
      loadTasks(true);
    }, [loadTasks]),
  );

  const filteredTasks = statusFilter
    ? tasks.filter(item => item.status === statusFilter)
    : tasks;

  const title =
    statusFilter === 'completed' ? 'Completed Tasks' : 'Assigned Tasks';
  const subtitle =
    statusFilter === 'completed'
      ? 'Completed requests are ready for final quotation submission.'
      : 'Review your assigned service requests and pull down to refresh.';

  if (loading) {
    return (
      <View style={styles.centeredContainer}>
        <ActivityIndicator size="large" color="#2563eb" />
        <Text style={styles.loadingText}>Loading assigned tasks...</Text>
      </View>
    );
  }

  return (
    <SafeAreaView style={styles.container}>
      <Text style={styles.title}>{title}</Text>
      <Text style={styles.subtitle}>{subtitle}</Text>

      {errorMessage ? (
        <View style={styles.errorCard}>
          <Text style={styles.errorTitle}>Something went wrong</Text>
          <Text style={styles.errorText}>{errorMessage}</Text>
          <AppButton
            title="Try again"
            onPress={() => loadTasks(true)}
            style={styles.retryButton}
          />
        </View>
      ) : (
        <FlatList
          contentContainerStyle={[
            styles.listContent,
            filteredTasks.length === 0 ? styles.emptyListContent : null,
          ]}
          data={filteredTasks}
          keyExtractor={item => item.id.toString()}
          renderItem={({item}) => (
            <TechnicianTaskCard
              serviceRequest={item}
              onPress={() =>
                navigation.navigate('RequestDetails', {
                  requestId: item.id,
                  initialRequest: item,
                })
              }
            />
          )}
          refreshControl={
            <RefreshControl
              refreshing={refreshing}
              onRefresh={() => {
                setRefreshing(true);
                loadTasks(false);
              }}
              tintColor="#2563eb"
            />
          }
          showsVerticalScrollIndicator={false}
          ListEmptyComponent={
            <View style={styles.emptyState}>
              <View style={styles.emptyIcon} />
              <Text style={styles.emptyTitle}>
                {statusFilter === 'completed'
                  ? 'No completed tasks yet'
                  : 'No assigned tasks right now'}
              </Text>
              <Text style={styles.emptyText}>
                {statusFilter === 'completed'
                  ? 'Complete one of your assigned requests and it will appear here for final quotation submission.'
                  : 'Assigned service requests will appear here as soon as they are linked to your technician account.'}
              </Text>
            </View>
          }
        />
      )}
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  container: {
    backgroundColor: '#f5f7fb',
    flex: 1,
    padding: 20,
  },
  centeredContainer: {
    alignItems: 'center',
    backgroundColor: '#f5f7fb',
    flex: 1,
    justifyContent: 'center',
    padding: 20,
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
  loadingText: {
    color: '#475569',
    fontSize: 14,
    marginTop: 12,
  },
  errorCard: {
    backgroundColor: '#ffffff',
    borderColor: '#fecaca',
    borderRadius: 22,
    borderWidth: 1,
    padding: 18,
  },
  errorTitle: {
    color: '#b91c1c',
    fontSize: 18,
    fontWeight: '700',
    marginBottom: 8,
  },
  errorText: {
    color: '#991b1b',
    fontSize: 14,
    lineHeight: 20,
  },
  retryButton: {
    marginTop: 16,
  },
  listContent: {
    paddingBottom: 24,
  },
  emptyListContent: {
    flexGrow: 1,
  },
  emptyState: {
    alignItems: 'center',
    backgroundColor: '#ffffff',
    borderColor: '#e2e8f0',
    borderRadius: 24,
    borderWidth: 1,
    marginTop: 8,
    padding: 28,
  },
  emptyIcon: {
    backgroundColor: '#dbeafe',
    borderRadius: 999,
    height: 56,
    marginBottom: 16,
    width: 56,
  },
  emptyTitle: {
    color: '#0f172a',
    fontSize: 20,
    fontWeight: '700',
    marginBottom: 8,
    textAlign: 'center',
  },
  emptyText: {
    color: '#64748b',
    fontSize: 14,
    lineHeight: 21,
    textAlign: 'center',
  },
});
