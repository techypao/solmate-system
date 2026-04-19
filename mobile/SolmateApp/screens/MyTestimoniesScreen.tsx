import React, {useCallback, useState} from 'react';
import {
  ActivityIndicator,
  Alert,
  FlatList,
  Image,
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
  deleteTestimony,
  getMyTestimonies,
  Testimony,
} from '../src/services/testimonyApi';

/* \u2500\u2500 design tokens \u2500\u2500 */

const NAVY = '#152a4a';
const GOLD = '#e8a800';
const MUTED = '#7b8699';
const BG = '#e0e8f5';
const CARD = '#ffffff';
const DIVIDER = '#edf1f7';

/* \u2500\u2500 helpers (preserved) \u2500\u2500 */

function formatDateTime(value?: string) {
  if (!value) return 'Not available';
  const parsedDate = new Date(value);
  if (Number.isNaN(parsedDate.getTime())) return value;
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

function getStatusColors(status?: string | null) {
  switch ((status || 'pending').toLowerCase()) {
    case 'approved':
      return {bg: '#dcfce7', text: '#166534'};
    case 'rejected':
      return {bg: '#fee2e2', text: '#b91c1c'};
    case 'pending':
    default:
      return {bg: '#fef3c7', text: '#b45309'};
  }
}

function getFriendlyErrorMessage(error: unknown) {
  if (error instanceof ApiError) {
    if (error.status === 401) return 'Your session has expired. Please log in again.';
    return error.message;
  }
  return 'Could not load your testimonies right now.';
}

function getMessagePreview(message?: string | null) {
  const trimmed = (message || '').trim();
  if (!trimmed) return 'No message provided.';
  if (trimmed.length <= 120) return trimmed;
  return trimmed.slice(0, 117).trimEnd() + '...';
}

function getReferenceLabel(testimony: Testimony) {
  if (testimony.serviceRequest) {
    const type = testimony.serviceRequest.request_type || 'Service';
    return 'Service Request #' + testimony.serviceRequest.id + ' - ' + type;
  }
  if (testimony.inspectionRequest) {
    return 'Inspection Request #' + testimony.inspectionRequest.id;
  }
  return 'Linked request unavailable';
}

function getRatingLabel(rating?: number | null) {
  if (!rating || Number.isNaN(rating)) return 'No rating';
  return '\u2605'.repeat(Math.max(0, Math.min(5, Math.floor(rating)))) + ' ' + rating + '/5';
}

function getDisplayTimestamp(testimony: Testimony) {
  return formatDateTime(testimony.updated_at || testimony.created_at);
}

function getImageCountLabel(count: number) {
  return count + ' image' + (count === 1 ? '' : 's');
}

/* \u2500\u2500 TestimonyCard \u2500\u2500 */

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
  const sc = getStatusColors(item.status);
  const firstImage = item.images?.[0];
  const imageCount = item.images?.length || 0;

  return (
    <View style={s.card}>
      {/* header */}
      <View style={s.cardHeader}>
        <View style={s.cardTitleWrap}>
          <Text style={s.cardTitle}>
            {item.title?.trim() || 'Untitled testimony'}
          </Text>
        </View>
        <View style={[s.statusBadge, {backgroundColor: sc.bg}]}>
          <Text style={[s.statusBadgeText, {color: sc.text}]}>
            {formatStatusLabel(item.status)}
          </Text>
        </View>
      </View>

      {/* body */}
      <Text style={s.messagePreview}>{getMessagePreview(item.message)}</Text>

      {/* meta: rating + linked */}
      <View style={s.metaRow}>
        <View style={s.metaCard}>
          <Text style={s.metaLabel}>Rating</Text>
          <Text style={s.metaValue}>{getRatingLabel(item.rating)}</Text>
        </View>
      </View>
      <View style={s.metaRow}>
        <View style={s.metaCard}>
          <Text style={s.metaLabel}>Linked To</Text>
          <Text style={s.metaValue}>{getReferenceLabel(item)}</Text>
        </View>
      </View>

      {/* image preview */}
      <View style={s.imageRow}>
        {firstImage?.image_url ? (
          <Image source={{uri: firstImage.image_url}} style={s.thumbnail} />
        ) : (
          <View style={s.thumbnailPlaceholder}>
            <Text style={s.thumbnailPlaceholderText}>No photo</Text>
          </View>
        )}
        <View style={s.imageInfo}>
          <Text style={s.imageTitle}>
            {imageCount > 0 ? 'Attached images' : 'Images'}
          </Text>
          <Text style={s.imageText}>
            {imageCount > 0
              ? getImageCountLabel(imageCount) + ' attached to this testimony.'
              : 'No images attached yet.'}
          </Text>
        </View>
      </View>

      {/* admin note */}
      {item.status === 'rejected' && item.admin_note ? (
        <View style={s.noteCard}>
          <Text style={s.noteTitle}>Admin Note</Text>
          <Text style={s.noteText}>{item.admin_note}</Text>
        </View>
      ) : null}

      {/* footer: timestamp + actions */}
      <View style={s.divider} />
      <View style={s.footerRow}>
        <View style={s.footerMeta}>
          <Text style={s.footerLabel}>Last updated</Text>
          <Text style={s.footerValue}>{getDisplayTimestamp(item)}</Text>
        </View>
        <View style={s.actionRow}>
          <Pressable
            disabled={deleting}
            onPress={() => onEditPress(item)}
            style={({pressed}) => [s.editBtn, pressed && s.pressed]}>
            <Text style={s.editBtnText}>Edit</Text>
          </Pressable>
          <Pressable
            disabled={deleting}
            onPress={() => onDeletePress(item)}
            style={({pressed}) => [s.deleteBtn, pressed && s.pressed]}>
            <Text style={s.deleteBtnText}>
              {deleting ? 'Deleting\u2026' : 'Delete'}
            </Text>
          </Pressable>
        </View>
      </View>
    </View>
  );
}

/* \u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550
   Main screen
   \u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550 */

export default function MyTestimoniesScreen({navigation}: any) {
  const [testimonies, setTestimonies] = useState<Testimony[]>([]);
  const [loading, setLoading] = useState(true);
  const [refreshing, setRefreshing] = useState(false);
  const [deletingId, setDeletingId] = useState<number | null>(null);
  const [errorMessage, setErrorMessage] = useState('');

  const loadTestimonies = useCallback(async (showLoadingState = false) => {
    try {
      if (showLoadingState) setLoading(true);
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
      setTestimonies(cur => cur.filter(c => c.id !== testimony.id));
      Alert.alert('Deleted', response.message);
      await loadTestimonies(false);
    } catch (error) {
      console.log('Delete testimony error:', error);
      const msg = getFriendlyErrorMessage(error);
      setErrorMessage(msg);
      Alert.alert('Unable to delete', msg);
    } finally {
      setDeletingId(null);
    }
  };

  const handleDeletePress = (testimony: Testimony) => {
    Alert.alert(
      'Delete testimony?',
      'This will permanently remove your testimony and its uploaded images.',
      [
        {text: 'Cancel', style: 'cancel'},
        {
          text: 'Delete',
          style: 'destructive',
          onPress: () => handleDeleteConfirmed(testimony),
        },
      ],
    );
  };

  /* \u2500\u2500 loading \u2500\u2500 */
  if (loading) {
    return (
      <SafeAreaView style={s.safe}>
        <View style={s.centered}>
          <ActivityIndicator color={GOLD} size="large" />
          <Text style={s.loadingText}>Loading your testimonies\u2026</Text>
        </View>
      </SafeAreaView>
    );
  }

  /* \u2500\u2500 header component for FlatList \u2500\u2500 */
  const ListHeader = (
    <View>
      {/* brand */}
      <Text style={s.brand}>
        Sol<Text style={s.brandAccent}>Mate</Text>
      </Text>

      {/* back */}
      <Pressable
        hitSlop={14}
        onPress={() => navigation.goBack()}
        style={({pressed}) => [s.backBtn, pressed && s.pressed]}>
        <Text style={s.backIcon}>{'\u2039'}</Text>
      </Pressable>

      {/* title */}
      <Text style={s.title}>My Testimonies</Text>
      <Text style={s.subtitle}>
        Review your submitted testimonies, track approval status, and check any
        moderation notes from the admin team.
      </Text>

      {/* add testimony CTA */}
      <Pressable
        onPress={showAddTestimony}
        style={({pressed}) => [s.goldBtn, pressed && s.pressed]}>
        <Text style={s.goldBtnText}>Add Testimony</Text>
      </Pressable>

      {/* error */}
      {errorMessage ? (
        <View style={s.errorCard}>
          <Text style={s.errorTitle}>Something went wrong</Text>
          <Text style={s.errorText}>{errorMessage}</Text>
          <Pressable
            onPress={() => loadTestimonies(true)}
            style={({pressed}) => [s.retryBtn, pressed && s.pressed]}>
            <Text style={s.retryBtnText}>Try Again</Text>
          </Pressable>
        </View>
      ) : null}
    </View>
  );

  return (
    <SafeAreaView style={s.safe}>
      <FlatList
        contentContainerStyle={s.listContent}
        data={errorMessage ? [] : testimonies}
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
            tintColor={GOLD}
          />
        }
        showsVerticalScrollIndicator={false}
        ListHeaderComponent={ListHeader}
        ListEmptyComponent={
          !errorMessage ? (
            <View style={s.emptyState}>
              <View style={s.emptyCircle} />
              <Text style={s.emptyTitle}>No testimonies yet</Text>
              <Text style={s.emptyText}>
                Once you submit testimonies for completed service or inspection
                requests, they will appear here.
              </Text>
              <Pressable
                onPress={showAddTestimony}
                style={({pressed}) => [s.outlineBtn, pressed && s.pressed]}>
                <Text style={s.outlineBtnText}>Add Testimony</Text>
              </Pressable>
            </View>
          ) : null
        }
      />
    </SafeAreaView>
  );
}

/* \u2500\u2500 styles \u2500\u2500 */

const s = StyleSheet.create({
  safe: {flex: 1, backgroundColor: BG},
  pressed: {opacity: 0.85},

  /* centered / loading */
  centered: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    padding: 24,
  },
  loadingText: {color: MUTED, fontSize: 14, marginTop: 14},

  /* list */
  listContent: {paddingHorizontal: 22, paddingTop: 20, paddingBottom: 30},

  /* brand */
  brand: {fontSize: 22, fontWeight: '800', color: NAVY, marginBottom: 10},
  brandAccent: {color: GOLD},

  /* back */
  backBtn: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: CARD,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 18,
    shadowColor: '#8a9bbd',
    shadowOffset: {width: 0, height: 2},
    shadowOpacity: 0.1,
    shadowRadius: 6,
    elevation: 3,
  },
  backIcon: {fontSize: 28, color: NAVY, fontWeight: '600', marginTop: -2},

  /* title */
  title: {fontSize: 26, fontWeight: '900', color: NAVY, marginBottom: 4},
  subtitle: {fontSize: 14, color: MUTED, lineHeight: 20, marginBottom: 18},

  /* gold CTA */
  goldBtn: {
    backgroundColor: GOLD,
    borderRadius: 28,
    paddingVertical: 14,
    alignItems: 'center',
    marginBottom: 18,
    shadowColor: GOLD,
    shadowOffset: {width: 0, height: 4},
    shadowOpacity: 0.25,
    shadowRadius: 10,
    elevation: 4,
  },
  goldBtnText: {
    fontSize: 15,
    fontWeight: '900',
    color: CARD,
    letterSpacing: 0.3,
  },

  /* error */
  errorCard: {
    backgroundColor: CARD,
    borderRadius: 22,
    borderWidth: 1,
    borderColor: '#fecaca',
    padding: 18,
    marginBottom: 16,
  },
  errorTitle: {
    color: '#b91c1c',
    fontSize: 18,
    fontWeight: '800',
    marginBottom: 6,
  },
  errorText: {
    color: '#991b1b',
    fontSize: 14,
    lineHeight: 20,
    marginBottom: 12,
  },
  retryBtn: {
    backgroundColor: GOLD,
    borderRadius: 24,
    paddingVertical: 12,
    alignItems: 'center',
  },
  retryBtnText: {fontSize: 14, fontWeight: '800', color: CARD},

  /* empty state */
  emptyState: {
    alignItems: 'center',
    backgroundColor: CARD,
    borderRadius: 22,
    padding: 28,
    marginTop: 4,
    shadowColor: '#8a9bbd',
    shadowOffset: {width: 0, height: 4},
    shadowOpacity: 0.1,
    shadowRadius: 14,
    elevation: 4,
  },
  emptyCircle: {
    width: 56,
    height: 56,
    borderRadius: 28,
    backgroundColor: '#dae3f3',
    marginBottom: 16,
  },
  emptyTitle: {
    color: NAVY,
    fontSize: 20,
    fontWeight: '800',
    marginBottom: 8,
  },
  emptyText: {
    color: MUTED,
    fontSize: 14,
    lineHeight: 21,
    textAlign: 'center',
    marginBottom: 16,
  },

  /* outline button */
  outlineBtn: {
    backgroundColor: CARD,
    borderRadius: 28,
    borderWidth: 1,
    borderColor: DIVIDER,
    paddingVertical: 13,
    paddingHorizontal: 28,
    alignItems: 'center',
  },
  outlineBtnText: {fontSize: 14, fontWeight: '800', color: NAVY},

  /* \u2500\u2500 card \u2500\u2500 */
  card: {
    backgroundColor: CARD,
    borderRadius: 22,
    padding: 20,
    marginBottom: 16,
    shadowColor: '#8a9bbd',
    shadowOffset: {width: 0, height: 4},
    shadowOpacity: 0.1,
    shadowRadius: 14,
    elevation: 4,
  },

  /* card header */
  cardHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    marginBottom: 12,
  },
  cardTitleWrap: {flex: 1, paddingRight: 12},
  cardEyebrow: {
    color: GOLD,
    fontSize: 12,
    fontWeight: '700',
    letterSpacing: 0.4,
    textTransform: 'uppercase',
    marginBottom: 4,
  },
  cardTitle: {
    color: NAVY,
    fontSize: 19,
    fontWeight: '900',
    lineHeight: 24,
  },

  /* status badge */
  statusBadge: {
    borderRadius: 20,
    paddingHorizontal: 12,
    paddingVertical: 6,
    alignSelf: 'flex-start',
  },
  statusBadgeText: {fontSize: 11, fontWeight: '700', textTransform: 'uppercase'},

  /* body */
  messagePreview: {
    color: NAVY,
    fontSize: 14,
    lineHeight: 21,
    opacity: 0.8,
    marginBottom: 14,
  },

  /* meta */
  metaRow: {marginBottom: 8},
  metaCard: {
    backgroundColor: '#f4f7fc',
    borderRadius: 16,
    padding: 14,
  },
  metaLabel: {
    color: MUTED,
    fontSize: 11,
    fontWeight: '700',
    textTransform: 'uppercase',
    marginBottom: 4,
  },
  metaValue: {color: NAVY, fontSize: 14, fontWeight: '700', lineHeight: 20},

  /* image row */
  imageRow: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: '#edf2fb',
    borderRadius: 18,
    padding: 12,
    marginBottom: 14,
    marginTop: 4,
  },
  thumbnail: {
    width: 68,
    height: 68,
    borderRadius: 14,
    backgroundColor: '#d6dff0',
    marginRight: 12,
  },
  thumbnailPlaceholder: {
    width: 68,
    height: 68,
    borderRadius: 14,
    backgroundColor: '#d6dff0',
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: 12,
  },
  thumbnailPlaceholderText: {
    color: MUTED,
    fontSize: 11,
    fontWeight: '700',
    textAlign: 'center',
  },
  imageInfo: {flex: 1},
  imageTitle: {color: NAVY, fontSize: 14, fontWeight: '800', marginBottom: 3},
  imageText: {color: MUTED, fontSize: 13, lineHeight: 19},

  /* admin note */
  noteCard: {
    backgroundColor: '#fff1f2',
    borderColor: '#fecdd3',
    borderRadius: 16,
    borderWidth: 1,
    padding: 14,
    marginBottom: 14,
  },
  noteTitle: {
    color: '#be123c',
    fontSize: 12,
    fontWeight: '800',
    textTransform: 'uppercase',
    marginBottom: 6,
  },
  noteText: {color: '#9f1239', fontSize: 14, lineHeight: 20},

  /* divider */
  divider: {height: 1, backgroundColor: DIVIDER, marginBottom: 14},

  /* footer */
  footerRow: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  footerMeta: {flex: 1, paddingRight: 12},
  footerLabel: {
    color: MUTED,
    fontSize: 11,
    fontWeight: '700',
    textTransform: 'uppercase',
    marginBottom: 3,
  },
  footerValue: {color: NAVY, fontSize: 13, fontWeight: '600'},

  /* action buttons */
  actionRow: {flexDirection: 'row', gap: 8},
  editBtn: {
    backgroundColor: CARD,
    borderRadius: 22,
    borderWidth: 1,
    borderColor: DIVIDER,
    paddingVertical: 9,
    paddingHorizontal: 20,
  },
  editBtnText: {fontSize: 13, fontWeight: '800', color: NAVY},
  deleteBtn: {
    backgroundColor: '#fee2e2',
    borderRadius: 22,
    paddingVertical: 9,
    paddingHorizontal: 20,
  },
  deleteBtnText: {fontSize: 13, fontWeight: '800', color: '#b91c1c'},
});
