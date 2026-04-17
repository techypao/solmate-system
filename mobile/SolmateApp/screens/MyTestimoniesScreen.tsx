import React, {useCallback, useState} from 'react';
import {
  ActivityIndicator,
  Alert,
  FlatList,
  Image,
  RefreshControl,
  StyleSheet,
  Text,
  View,
} from 'react-native';
import {useFocusEffect} from '@react-navigation/native';

import {AppButton} from '../components';
import {ApiError} from '../src/services/api';
import {
  deleteTestimony,
  getMyTestimonies,
  Testimony,
} from '../src/services/testimonyApi';

function formatDateTime(value?: string) {
  if (!value) {
    return 'Not available';
  }

  const parsedDate = new Date(value);

  if (Number.isNaN(parsedDate.getTime())) {
    return value;
  }

  return parsedDate.toLocaleString();
}

function formatStatusLabel(status?: string | null) {
  switch ((status || 'pending').toLowerCase()) {
    case 'approved':
      return 'Approved';
    case 'rejected':
      return 'Rejected';
    case 'pending':
    default:
      return 'Pending';
  }
}

function getStatusBadgeStyle(status?: string | null) {
  switch ((status || 'pending').toLowerCase()) {
    case 'approved':
      return {
        backgroundColor: '#dcfce7',
        textColor: '#166534',
      };
    case 'rejected':
      return {
        backgroundColor: '#fee2e2',
        textColor: '#b91c1c',
      };
    case 'pending':
    default:
      return {
        backgroundColor: '#fef3c7',
        textColor: '#b45309',
      };
  }
}

function getFriendlyErrorMessage(error: unknown) {
  if (error instanceof ApiError) {
    if (error.status === 401) {
      return 'Your session has expired. Please log in again.';
    }

    return error.message;
  }

  return 'Could not load your testimonies right now.';
}

function getMessagePreview(message?: string | null) {
  const trimmedMessage = (message || '').trim();

  if (!trimmedMessage) {
    return 'No message provided.';
  }

  if (trimmedMessage.length <= 120) {
    return trimmedMessage;
  }

  return `${trimmedMessage.slice(0, 117).trimEnd()}...`;
}

function getReferenceLabel(testimony: Testimony) {
  if (testimony.serviceRequest) {
    const requestType = testimony.serviceRequest.request_type || 'Service';
    return `Service Request #${testimony.serviceRequest.id} - ${requestType}`;
  }

  if (testimony.inspectionRequest) {
    return `Inspection Request #${testimony.inspectionRequest.id}`;
  }

  return 'Linked request unavailable';
}

function getRatingLabel(rating?: number | null) {
  if (!rating || Number.isNaN(rating)) {
    return 'No rating';
  }

  return `${'★'.repeat(Math.max(0, Math.min(5, Math.floor(rating))))} ${rating}/5`;
}

function getDisplayTimestamp(testimony: Testimony) {
  return formatDateTime(testimony.updated_at || testimony.created_at);
}

function getImageCountLabel(count: number) {
  return `${count} image${count === 1 ? '' : 's'}`;
}

function TestimonyCard({
  item,
  deleting,
  onEditPress,
  onDeletePress,
}: {
  item: Testimony;
  deleting: boolean;
  onEditPress: (testimony: Testimony) => void;
  onDeletePress: (testimony: Testimony) => void;
}) {
  const statusStyle = getStatusBadgeStyle(item.status);
  const firstImage = item.images?.[0];
  const imageCount = item.images?.length || 0;

  return (
    <View style={styles.card}>
      <View style={styles.cardAccent} />

      <View style={styles.cardHeader}>
        <View style={styles.cardTitleWrap}>
          <Text style={styles.cardEyebrow}>Testimony #{item.id}</Text>
          <Text style={styles.cardTitle}>{item.title?.trim() || 'Untitled testimony'}</Text>
        </View>

        <View
          style={[
            styles.statusBadge,
            {backgroundColor: statusStyle.backgroundColor},
          ]}>
          <Text style={[styles.statusBadgeText, {color: statusStyle.textColor}]}>
            {formatStatusLabel(item.status)}
          </Text>
        </View>
      </View>

      <Text style={styles.messagePreview}>{getMessagePreview(item.message)}</Text>

      <View style={styles.metaGrid}>
        <View style={styles.metaCard}>
          <Text style={styles.metaLabel}>Rating</Text>
          <Text style={styles.metaValue}>{getRatingLabel(item.rating)}</Text>
        </View>

        <View style={styles.metaCard}>
          <Text style={styles.metaLabel}>Linked to</Text>
          <Text style={styles.metaValue}>{getReferenceLabel(item)}</Text>
        </View>
      </View>

      <View style={styles.imageRow}>
        {firstImage?.image_url ? (
          <Image source={{uri: firstImage.image_url}} style={styles.thumbnail} />
        ) : (
          <View style={styles.thumbnailPlaceholder}>
            <Text style={styles.thumbnailPlaceholderText}>No photo</Text>
          </View>
        )}

        <View style={styles.imageInfo}>
          <Text style={styles.imageTitle}>
            {imageCount > 0 ? 'Attached images' : 'Images'}
          </Text>
          <Text style={styles.imageText}>
            {imageCount > 0
              ? `${getImageCountLabel(imageCount)} attached to this testimony.`
              : 'No images attached yet.'}
          </Text>
        </View>
      </View>

      {item.status === 'rejected' && item.admin_note ? (
        <View style={styles.noteCard}>
          <Text style={styles.noteTitle}>Admin note</Text>
          <Text style={styles.noteText}>{item.admin_note}</Text>
        </View>
      ) : null}

      <View style={styles.footerRow}>
        <View style={styles.footerMetaWrap}>
          <Text style={styles.footerLabel}>Last updated</Text>
          <Text style={styles.footerValue}>{getDisplayTimestamp(item)}</Text>
        </View>

        <View style={styles.actionRow}>
          <AppButton
            title="Edit"
            disabled={deleting}
            variant="outline"
            style={styles.editButton}
            onPress={() => onEditPress(item)}
          />
          <AppButton
            title={deleting ? 'Deleting...' : 'Delete'}
            disabled={deleting}
            variant="secondary"
            style={styles.deleteButton}
            textStyle={styles.deleteButtonText}
            onPress={() => onDeletePress(item)}
          />
        </View>
      </View>
    </View>
  );
}

export default function MyTestimoniesScreen({navigation}: any) {
  const [testimonies, setTestimonies] = useState<Testimony[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [deletingId, setDeletingId] = useState<number | null>(null);
  const [errorMessage, setErrorMessage] = useState('');

  const loadTestimonies = useCallback(async (showLoadingState = false) => {
    try {
      if (showLoadingState) {
        setLoading(true);
      }

      setErrorMessage('');
      const data = await getMyTestimonies();
      setTestimonies(Array.isArray(data) ? data : []);
    } catch (error) {
      setTestimonies([]);
      setErrorMessage(getFriendlyErrorMessage(error));
    } finally {
      setLoading(false);
      setRefreshing(false);
    }
  }, []);

  useFocusEffect(
    useCallback(() => {
      loadTestimonies(true);
    }, [loadTestimonies]),
  );

  const handleRefresh = () => {
    setRefreshing(true);
    loadTestimonies(false);
  };

  const showAddTestimony = () => {
    navigation.navigate('CreateTestimony');
  };

  const showEditTestimony = (testimony: Testimony) => {
    navigation.navigate('EditTestimony', {testimony});
  };

  const handleDeleteConfirmed = async (testimony: Testimony) => {
    try {
      setDeletingId(testimony.id);
      setErrorMessage('');

      const response = await deleteTestimony(testimony.id);
      setTestimonies(currentItems =>
        currentItems.filter(currentItem => currentItem.id !== testimony.id),
      );

      Alert.alert('Deleted', response.message);
      await loadTestimonies(false);
    } catch (error) {
      console.log('Delete testimony error:', error);
      const friendlyMessage = getFriendlyErrorMessage(error);
      setErrorMessage(friendlyMessage);
      Alert.alert('Unable to delete', friendlyMessage);
    } finally {
      setDeletingId(null);
    }
  };

  const handleDeletePress = (testimony: Testimony) => {
    Alert.alert(
      'Delete testimony?',
      'This will permanently remove your testimony and its uploaded images.',
      [
        {
          text: 'Cancel',
          style: 'cancel',
        },
        {
          text: 'Delete',
          style: 'destructive',
          onPress: () => {
            handleDeleteConfirmed(testimony);
          },
        },
      ],
    );
  };

  if (loading) {
    return (
      <View style={styles.centeredContainer}>
        <ActivityIndicator color="#2563eb" size="large" />
        <Text style={styles.loadingText}>Loading your testimonies...</Text>
      </View>
    );
  }

  return (
    <View style={styles.container}>
      <Text style={styles.title}>My Testimonies</Text>
      <Text style={styles.subtitle}>
        Review your submitted testimonies, track approval status, and check any
        moderation notes from the admin team.
      </Text>

      <AppButton
        title="Add Testimony"
        style={styles.addButton}
        onPress={showAddTestimony}
      />

      {errorMessage ? (
        <View style={styles.errorCard}>
          <Text style={styles.errorTitle}>Something went wrong</Text>
          <Text style={styles.errorText}>{errorMessage}</Text>
          <AppButton
            title="Try again"
            style={styles.retryButton}
            onPress={() => loadTestimonies(true)}
          />
        </View>
      ) : (
        <FlatList
          contentContainerStyle={[
            styles.listContent,
            testimonies.length === 0 ? styles.emptyListContent : null,
          ]}
          data={testimonies}
          keyExtractor={item => item.id.toString()}
          renderItem={({item}) => (
            <TestimonyCard
              deleting={deletingId === item.id}
              item={item}
              onDeletePress={handleDeletePress}
              onEditPress={showEditTestimony}
            />
          )}
          refreshControl={
            <RefreshControl
              refreshing={refreshing}
              onRefresh={handleRefresh}
              tintColor="#2563eb"
            />
          }
          showsVerticalScrollIndicator={false}
          ListEmptyComponent={
            <View style={styles.emptyState}>
              <View style={styles.emptyIcon} />
              <Text style={styles.emptyTitle}>No testimonies yet</Text>
              <Text style={styles.emptyText}>
                Once you submit testimonies for completed service or inspection
                requests, they will appear here.
              </Text>
              <AppButton
                title="Add Testimony"
                variant="outline"
                style={styles.emptyButton}
                onPress={showAddTestimony}
              />
            </View>
          }
        />
      )}
    </View>
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
    marginBottom: 16,
  },
  addButton: {
    marginBottom: 16,
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
  },
  emptyText: {
    color: '#64748b',
    fontSize: 14,
    lineHeight: 21,
    textAlign: 'center',
  },
  emptyButton: {
    marginTop: 16,
    width: '100%',
  },
  card: {
    backgroundColor: '#ffffff',
    borderColor: '#e2e8f0',
    borderRadius: 24,
    borderWidth: 1,
    marginBottom: 16,
    overflow: 'hidden',
    padding: 18,
    position: 'relative',
  },
  cardAccent: {
    backgroundColor: '#93c5fd',
    borderRadius: 999,
    height: 72,
    position: 'absolute',
    right: -14,
    top: -14,
    width: 72,
  },
  cardHeader: {
    alignItems: 'flex-start',
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 12,
  },
  cardTitleWrap: {
    flex: 1,
    paddingRight: 12,
  },
  cardEyebrow: {
    color: '#2563eb',
    fontSize: 12,
    fontWeight: '700',
    letterSpacing: 0.4,
    marginBottom: 4,
    textTransform: 'uppercase',
  },
  cardTitle: {
    color: '#0f172a',
    fontSize: 20,
    fontWeight: '800',
    lineHeight: 25,
  },
  statusBadge: {
    alignSelf: 'flex-start',
    borderRadius: 999,
    paddingHorizontal: 12,
    paddingVertical: 8,
  },
  statusBadgeText: {
    fontSize: 12,
    fontWeight: '700',
    textTransform: 'uppercase',
  },
  messagePreview: {
    color: '#475569',
    fontSize: 14,
    lineHeight: 21,
    marginBottom: 14,
  },
  metaGrid: {
    gap: 10,
    marginBottom: 14,
  },
  metaCard: {
    backgroundColor: '#f8fafc',
    borderRadius: 18,
    padding: 14,
  },
  metaLabel: {
    color: '#64748b',
    fontSize: 12,
    fontWeight: '700',
    marginBottom: 6,
    textTransform: 'uppercase',
  },
  metaValue: {
    color: '#0f172a',
    fontSize: 14,
    fontWeight: '600',
    lineHeight: 20,
  },
  imageRow: {
    alignItems: 'center',
    backgroundColor: '#eff6ff',
    borderRadius: 20,
    flexDirection: 'row',
    marginBottom: 14,
    padding: 12,
  },
  thumbnail: {
    backgroundColor: '#dbeafe',
    borderRadius: 16,
    height: 72,
    marginRight: 12,
    width: 72,
  },
  thumbnailPlaceholder: {
    alignItems: 'center',
    backgroundColor: '#dbeafe',
    borderRadius: 16,
    height: 72,
    justifyContent: 'center',
    marginRight: 12,
    width: 72,
  },
  thumbnailPlaceholderText: {
    color: '#2563eb',
    fontSize: 11,
    fontWeight: '700',
    textAlign: 'center',
  },
  imageInfo: {
    flex: 1,
  },
  imageTitle: {
    color: '#0f172a',
    fontSize: 14,
    fontWeight: '700',
    marginBottom: 4,
  },
  imageText: {
    color: '#475569',
    fontSize: 13,
    lineHeight: 19,
  },
  noteCard: {
    backgroundColor: '#fff1f2',
    borderColor: '#fecdd3',
    borderRadius: 18,
    borderWidth: 1,
    marginBottom: 14,
    padding: 14,
  },
  noteTitle: {
    color: '#be123c',
    fontSize: 13,
    fontWeight: '800',
    marginBottom: 6,
    textTransform: 'uppercase',
  },
  noteText: {
    color: '#9f1239',
    fontSize: 14,
    lineHeight: 20,
  },
  footerRow: {
    alignItems: 'center',
    flexDirection: 'row',
    justifyContent: 'space-between',
  },
  actionRow: {
    flexDirection: 'row',
    gap: 10,
  },
  footerMetaWrap: {
    flex: 1,
    paddingRight: 12,
  },
  footerLabel: {
    color: '#64748b',
    fontSize: 12,
    fontWeight: '700',
    marginBottom: 4,
    textTransform: 'uppercase',
  },
  footerValue: {
    color: '#0f172a',
    fontSize: 13,
    fontWeight: '600',
    lineHeight: 19,
  },
  editButton: {
    minWidth: 104,
  },
  deleteButton: {
    backgroundColor: '#fee2e2',
    borderColor: '#fecaca',
    minWidth: 104,
  },
  deleteButtonText: {
    color: '#b91c1c',
  },
});
