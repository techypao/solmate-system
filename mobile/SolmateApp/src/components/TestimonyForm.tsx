import React, {useEffect, useMemo, useState} from 'react';
import {
  ActivityIndicator,
  Alert,
  Image,
  Pressable,
  SafeAreaView,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  View,
} from 'react-native';
import {Asset, launchImageLibrary} from 'react-native-image-picker';

import {ApiError} from '../services/api';
import {
  getInspectionRequests,
  InspectionRequest,
} from '../services/inspectionRequestApi';
import {getServiceRequests, ServiceRequest} from '../services/serviceRequestApi';
import {
  createTestimony,
  Testimony,
  TestimonyFormPayload,
  TestimonyImage,
  updateTestimony,
} from '../services/testimonyApi';

/* \u2500\u2500 design tokens \u2500\u2500 */

const NAVY = '#152a4a';
const GOLD = '#e8a800';
const MUTED = '#7b8699';
const BG = '#e0e8f5';
const CARD = '#ffffff';
const DIVIDER = '#edf1f7';
const MAX_TESTIMONY_IMAGES = 5;

/* \u2500\u2500 types \u2500\u2500 */

type Mode = 'create' | 'edit';

type FieldErrors = {
  linkedRequest?: string;
  rating?: string;
  message?: string;
};

type LocalImageAsset = {
  uri: string;
  type?: string | null;
  name?: string | null;
};

type TestimonyFormProps = {
  mode: Mode;
  navigation: any;
  initialTestimony?: Testimony | null;
};

/* \u2500\u2500 helpers (preserved) \u2500\u2500 */

function getFriendlyErrorMessage(error: unknown) {
  if (error instanceof ApiError) {
    if (error.status === 401) return 'Your session has expired. Please log in again.';
    return error.message;
  }
  return 'Something went wrong while saving your testimony.';
}

function normalizePickedAssets(assets?: Asset[]): LocalImageAsset[] {
  return (assets || [])
    .filter(asset => !!asset.uri)
    .map(asset => ({
      uri: asset.uri as string,
      type: asset.type || 'image/jpeg',
      name: asset.fileName || null,
    }));
}

function formatServiceRequestLabel(sr: ServiceRequest) {
  const type = sr.request_type || 'Service';
  return 'Service Request #' + sr.id + ' - ' + type;
}

function formatInspectionRequestLabel(ir: InspectionRequest) {
  return 'Inspection Request #' + ir.id;
}

function getExistingImageCount(
  existingImages: TestimonyImage[],
  removedImageIds: number[],
) {
  return existingImages.filter(img => !removedImageIds.includes(img.id)).length;
}

function isCompletedStatus(status?: string | null) {
  return (status || '').toLowerCase() === 'completed';
}

/* \u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550
   Main component
   \u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550\u2550 */

export default function TestimonyForm({
  mode,
  navigation,
  initialTestimony,
}: TestimonyFormProps) {
  /* \u2500\u2500 initial IDs (preserved) \u2500\u2500 */
  const rawInitialServiceRequestId =
    initialTestimony?.service_request_id ??
    initialTestimony?.serviceRequest?.id ??
    null;
  const rawInitialInspectionRequestId =
    initialTestimony?.inspection_request_id ??
    initialTestimony?.inspectionRequest?.id ??
    null;
  const initialServiceRequestId = rawInitialServiceRequestId;
  const initialInspectionRequestId = rawInitialServiceRequestId
    ? null
    : rawInitialInspectionRequestId;

  /* \u2500\u2500 state (all preserved) \u2500\u2500 */
  const [serviceRequests, setServiceRequests] = useState<ServiceRequest[]>([]);
  const [inspectionRequests, setInspectionRequests] = useState<InspectionRequest[]>([]);
  const [selectedServiceRequestId, setSelectedServiceRequestId] = useState<number | null>(
    initialServiceRequestId,
  );
  const [selectedInspectionRequestId, setSelectedInspectionRequestId] = useState<number | null>(
    initialInspectionRequestId,
  );
  const [rating, setRating] = useState<number>(initialTestimony?.rating || 0);
  const [title, setTitle] = useState(initialTestimony?.title || '');
  const [message, setMessage] = useState(initialTestimony?.message || '');
  const [newImages, setNewImages] = useState<LocalImageAsset[]>([]);
  const [removedImageIds, setRemovedImageIds] = useState<number[]>([]);
  const [fieldErrors, setFieldErrors] = useState<FieldErrors>({});
  const [loadingOptions, setLoadingOptions] = useState(true);
  const [submitting, setSubmitting] = useState(false);
  const [errorMessage, setErrorMessage] = useState('');

  /* \u2500\u2500 load eligible requests (preserved) \u2500\u2500 */
  useEffect(() => {
    let isMounted = true;

    async function loadEligibleRequests() {
      try {
        setLoadingOptions(true);
        setErrorMessage('');

        const [serviceData, inspectionData] = await Promise.all([
          getServiceRequests(),
          getInspectionRequests(),
        ]);

        if (!isMounted) return;

        setServiceRequests(
          (Array.isArray(serviceData) ? serviceData : []).filter(request =>
            isCompletedStatus(request.status),
          ),
        );
        setInspectionRequests(
          (Array.isArray(inspectionData) ? inspectionData : []).filter(request =>
            isCompletedStatus(request.status),
          ),
        );
      } catch (error) {
        if (isMounted) {
          console.log('Load testimony form options error:', error);
          setErrorMessage(getFriendlyErrorMessage(error));
          setServiceRequests([]);
          setInspectionRequests([]);
        }
      } finally {
        if (isMounted) setLoadingOptions(false);
      }
    }

    loadEligibleRequests();
    return () => { isMounted = false; };
  }, []);

  /* \u2500\u2500 merged lists (preserved) \u2500\u2500 */
  const mergedServiceRequests = useMemo(() => {
    const list = [...serviceRequests];
    const init = initialTestimony?.serviceRequest;
    if (init?.id && !list.some(sr => sr.id === init.id)) {
      list.unshift({
        id: init.id,
        user_id: init.user_id,
        technician_id: init.technician_id,
        request_type: init.request_type || 'Service',
        details: init.request_type || 'Completed service request',
        date_needed: init.date_needed,
        status: init.status || 'completed',
      });
    }
    return list;
  }, [initialTestimony?.serviceRequest, serviceRequests]);

  const mergedInspectionRequests = useMemo(() => {
    const list = [...inspectionRequests];
    const init = initialTestimony?.inspectionRequest;
    if (init?.id && !list.some(ir => ir.id === init.id)) {
      list.unshift({
        id: init.id,
        user_id: init.user_id,
        technician_id: init.technician_id,
        details: 'Completed inspection request',
        date_needed: init.date_needed,
        status: init.status || 'completed',
      });
    }
    return list;
  }, [initialTestimony?.inspectionRequest, inspectionRequests]);

  /* \u2500\u2500 derived (preserved) \u2500\u2500 */
  const existingImages = initialTestimony?.images || [];
  const activeExistingImageCount = getExistingImageCount(existingImages, removedImageIds);
  const remainingImageSlots = Math.max(
    0,
    MAX_TESTIMONY_IMAGES - activeExistingImageCount - newImages.length,
  );

  /* \u2500\u2500 helpers (preserved) \u2500\u2500 */
  const clearError = (key?: keyof FieldErrors) => {
    if (errorMessage) setErrorMessage('');
    if (key && fieldErrors[key]) {
      setFieldErrors(cur => ({...cur, [key]: undefined}));
    }
  };

  const toggleExistingImageRemoval = (imageId: number) => {
    clearError();
    setRemovedImageIds(cur =>
      cur.includes(imageId) ? cur.filter(id => id !== imageId) : [...cur, imageId],
    );
  };

  const removeNewImage = (uri: string) => {
    clearError();
    setNewImages(cur => cur.filter(img => img.uri !== uri));
  };

  const handlePickImages = async () => {
    clearError();
    if (remainingImageSlots <= 0) {
      Alert.alert('Image limit reached', 'You can upload up to ' + MAX_TESTIMONY_IMAGES + ' images per testimony.');
      return;
    }
    const result = await launchImageLibrary({
      mediaType: 'photo',
      selectionLimit: remainingImageSlots,
      quality: 0.8,
    });
    if (result.didCancel) return;
    if (result.errorMessage) {
      Alert.alert('Image selection failed', result.errorMessage);
      return;
    }
    const picked = normalizePickedAssets(result.assets);
    if (picked.length === 0) return;
    setNewImages(cur => [...cur, ...picked]);
  };

  /* \u2500\u2500 validation (preserved) \u2500\u2500 */
  const validateForm = () => {
    const nextErrors: FieldErrors = {};
    if (!selectedServiceRequestId && !selectedInspectionRequestId) {
      nextErrors.linkedRequest =
        'Please select at least one completed service or inspection request.';
    }
    if (!rating || rating < 1 || rating > 5) {
      nextErrors.rating = 'Please choose a rating from 1 to 5.';
    }
    if (!message.trim()) {
      nextErrors.message = 'Please enter your testimony message.';
    }
    setFieldErrors(nextErrors);
    if (Object.keys(nextErrors).length > 0) {
      setErrorMessage('Please complete the required fields before submitting.');
      return false;
    }
    return true;
  };

  /* \u2500\u2500 submit (preserved) \u2500\u2500 */
  const handleSubmit = async () => {
    if (submitting || !validateForm()) return;

    const effectiveServiceRequestId =
      selectedServiceRequestId ?? initialServiceRequestId;
    const effectiveInspectionRequestId =
      selectedInspectionRequestId ?? initialInspectionRequestId;

    const payload: TestimonyFormPayload = {
      serviceRequestId: effectiveServiceRequestId,
      inspectionRequestId: effectiveInspectionRequestId,
      rating,
      title,
      message,
      newImages,
      removeImageIds: removedImageIds,
    };

    try {
      setSubmitting(true);
      setErrorMessage('');

      console.log('Save testimony payload summary:', {
        mode,
        testimony_id: initialTestimony?.id ?? null,
        service_request_id: payload.serviceRequestId ?? null,
        inspection_request_id: payload.inspectionRequestId ?? null,
        rating: payload.rating,
        title: typeof payload.title === 'string' ? payload.title.trim() : null,
        message_length:
          typeof payload.message === 'string' ? payload.message.trim().length : 0,
        new_images_count: Array.isArray(payload.newImages) ? payload.newImages.length : 0,
        remove_image_ids_count: Array.isArray(payload.removeImageIds)
          ? payload.removeImageIds.length
          : 0,
      });

      const response =
        mode === 'edit' && initialTestimony?.id
          ? await updateTestimony(initialTestimony.id, payload)
          : await createTestimony(payload);

      Alert.alert('Success', response.message, [
        {text: 'OK', onPress: () => navigation.goBack()},
      ]);
    } catch (error) {
      if (error instanceof ApiError) {
        console.log('Save testimony API error:', {
          status: error.status,
          message: error.message,
          errors: error.errors,
          data: error.data,
        });
      } else {
        console.log('Save testimony unexpected error:', error);
      }
      setErrorMessage(getFriendlyErrorMessage(error));
    } finally {
      setSubmitting(false);
    }
  };

  const noEligibleRequests =
    mergedServiceRequests.length === 0 && mergedInspectionRequests.length === 0;

  /* \u2500\u2500 loading \u2500\u2500 */
  if (loadingOptions) {
    return (
      <SafeAreaView style={st.safe}>
        <View style={st.centered}>
          <ActivityIndicator color={GOLD} size="large" />
          <Text style={st.loadingText}>Loading completed requests\u2026</Text>
        </View>
      </SafeAreaView>
    );
  }

  /* \u2500\u2500 render \u2500\u2500 */
  return (
    <SafeAreaView style={st.safe}>
      <ScrollView
        contentContainerStyle={st.scroll}
        keyboardShouldPersistTaps="handled"
        showsVerticalScrollIndicator={false}>

        {/* brand */}
        <Text style={st.brand}>
          Sol<Text style={st.brandAccent}>Mate</Text>
        </Text>

        {/* back */}
        <Pressable
          hitSlop={14}
          onPress={() => navigation.goBack()}
          style={({pressed}) => [st.backBtn, pressed && st.pressed]}>
          <Text style={st.backIcon}>{'\u2039'}</Text>
        </Pressable>

        {/* hero */}
        <Text style={st.title}>
          {mode === 'create' ? 'Share Your Experience' : 'Update Your Testimony'}
        </Text>
        <Text style={st.subtitle}>
          Select the completed request you want to review, add your rating, and
          attach photos if they help tell the story.
        </Text>

        {/* \u2500\u2500 main card \u2500\u2500 */}
        <View style={st.card}>
          <Text style={st.cardTitle}>Testimony Details</Text>
          <Text style={st.cardSubtitle}>
            Your submission is sent securely using your saved customer login session.
          </Text>

          {/* banners (preserved) */}
          {mode === 'edit' && initialTestimony?.status === 'approved' ? (
            <View style={st.infoBanner}>
              <Text style={st.bannerLabel}>Heads up</Text>
              <Text style={st.bannerText}>
                Editing an approved testimony will send it back for admin review.
              </Text>
            </View>
          ) : null}

          {mode === 'edit' &&
          initialTestimony?.status === 'rejected' &&
          initialTestimony.admin_note ? (
            <View style={st.warningBanner}>
              <Text style={st.bannerLabel}>Admin note</Text>
              <Text style={st.bannerText}>{initialTestimony.admin_note}</Text>
            </View>
          ) : null}

          {errorMessage ? (
            <View style={st.errorBanner}>
              <Text style={st.bannerLabel}>Unable to save</Text>
              <Text style={st.bannerText}>{errorMessage}</Text>
            </View>
          ) : null}

          {noEligibleRequests ? (
            <View style={st.emptyNotice}>
              <Text style={st.emptyNoticeTitle}>No completed requests available</Text>
              <Text style={st.emptyNoticeText}>
                You can submit a testimony after one of your service or inspection
                requests reaches completed status.
              </Text>
            </View>
          ) : null}

          {/* \u2500 service requests \u2500 */}
          <View style={st.fieldGroup}>
            <View style={st.fieldHeader}>
              <Text style={st.fieldLabel}>Completed Service Requests</Text>
              <Text style={st.optionalTag}>Optional</Text>
            </View>

            {mergedServiceRequests.length === 0 ? (
              <Text style={st.helpText}>
                No completed service requests are available yet.
              </Text>
            ) : (
              <View style={st.selectorGroup}>
                {mergedServiceRequests.map(sr => {
                  const selected = selectedServiceRequestId === sr.id;
                  return (
                    <Pressable
                      key={'service-' + sr.id}
                      onPress={() => {
                        clearError('linkedRequest');
                        setSelectedServiceRequestId(cur => {
                          const next = cur === sr.id ? null : sr.id;
                          setSelectedInspectionRequestId(null);
                          return next;
                        });
                      }}
                      style={({pressed}) => [
                        st.selectorCard,
                        selected && st.selectorSelected,
                        pressed && st.pressed,
                      ]}>
                      <Text style={st.selectorTitle}>
                        {formatServiceRequestLabel(sr)}
                      </Text>
                      <Text style={st.selectorSub}>
                        Status: {(sr.status || 'completed').toUpperCase()}
                      </Text>
                    </Pressable>
                  );
                })}
              </View>
            )}
          </View>

          {/* \u2500 inspection requests \u2500 */}
          <View style={st.fieldGroup}>
            <View style={st.fieldHeader}>
              <Text style={st.fieldLabel}>Completed Inspection Requests</Text>
              <Text style={st.optionalTag}>Optional</Text>
            </View>

            {mergedInspectionRequests.length === 0 ? (
              <Text style={st.helpText}>
                No completed inspection requests are available yet.
              </Text>
            ) : (
              <View style={st.selectorGroup}>
                {mergedInspectionRequests.map(ir => {
                  const selected = selectedInspectionRequestId === ir.id;
                  return (
                    <Pressable
                      key={'inspection-' + ir.id}
                      onPress={() => {
                        clearError('linkedRequest');
                        setSelectedInspectionRequestId(cur => {
                          const next = cur === ir.id ? null : ir.id;
                          setSelectedServiceRequestId(null);
                          return next;
                        });
                      }}
                      style={({pressed}) => [
                        st.selectorCard,
                        selected && st.selectorSelected,
                        pressed && st.pressed,
                      ]}>
                      <Text style={st.selectorTitle}>
                        {formatInspectionRequestLabel(ir)}
                      </Text>
                      <Text style={st.selectorSub}>
                        Status: {(ir.status || 'completed').toUpperCase()}
                      </Text>
                    </Pressable>
                  );
                })}
              </View>
            )}

            <Text style={st.helpText}>
              Select at least one completed request that this testimony is about.
            </Text>
            {fieldErrors.linkedRequest ? (
              <Text style={st.fieldError}>{fieldErrors.linkedRequest}</Text>
            ) : null}
          </View>

          {/* \u2500 rating \u2500 */}
          <View style={st.fieldGroup}>
            <View style={st.fieldHeader}>
              <Text style={st.fieldLabel}>Rating</Text>
              <Text style={st.requiredTag}>Required</Text>
            </View>

            <View style={st.ratingRow}>
              {[1, 2, 3, 4, 5].map(value => {
                const isSelected = value <= rating;
                return (
                  <Pressable
                    key={value}
                    onPress={() => {
                      clearError('rating');
                      setRating(value);
                    }}
                    style={({pressed}) => [
                      st.ratingChip,
                      isSelected && st.ratingChipSelected,
                      pressed && st.pressed,
                    ]}>
                    <Text
                      style={[
                        st.ratingChipText,
                        isSelected && st.ratingChipTextSelected,
                      ]}>
                      {'\u2605'.repeat(value)}
                    </Text>
                  </Pressable>
                );
              })}
            </View>
            {fieldErrors.rating ? (
              <Text style={st.fieldError}>{fieldErrors.rating}</Text>
            ) : null}
          </View>

          {/* \u2500 title \u2500 */}
          <View style={st.fieldGroup}>
            <View style={st.fieldHeader}>
              <Text style={st.fieldLabel}>Title</Text>
              <Text style={st.optionalTag}>Optional</Text>
            </View>
            <TextInput
              autoCapitalize="sentences"
              onChangeText={v => { clearError(); setTitle(v); }}
              placeholder="Optional title for your testimony"
              placeholderTextColor="#a8b4c8"
              style={st.input}
              value={title}
            />
          </View>

          {/* \u2500 message \u2500 */}
          <View style={st.fieldGroup}>
            <View style={st.fieldHeader}>
              <Text style={st.fieldLabel}>Message</Text>
              <Text style={st.requiredTag}>Required</Text>
            </View>
            <TextInput
              multiline
              numberOfLines={6}
              onChangeText={v => { clearError('message'); setMessage(v); }}
              placeholder="Share what went well, what stood out, or what others should know."
              placeholderTextColor="#a8b4c8"
              style={[
                st.input,
                st.textArea,
                fieldErrors.message && st.inputError,
              ]}
              textAlignVertical="top"
              value={message}
            />
            {fieldErrors.message ? (
              <Text style={st.fieldError}>{fieldErrors.message}</Text>
            ) : null}
          </View>

          {/* \u2500 images \u2500 */}
          <View style={st.fieldGroup}>
            <View style={st.fieldHeader}>
              <Text style={st.fieldLabel}>Images</Text>
              <Text style={st.optionalTag}>Up to {MAX_TESTIMONY_IMAGES}</Text>
            </View>

            <Text style={st.helpText}>
              Add photos that support your testimony. Existing images stay unless
              you remove them.
            </Text>

            <Pressable
              disabled={remainingImageSlots <= 0}
              onPress={handlePickImages}
              style={({pressed}) => [
                st.outlineBtn,
                {marginTop: 10},
                remainingImageSlots <= 0 && st.outlineBtnDisabled,
                pressed && st.pressed,
              ]}>
              <Text
                style={[
                  st.outlineBtnText,
                  remainingImageSlots <= 0 && st.outlineBtnTextDisabled,
                ]}>
                {remainingImageSlots > 0 ? 'Choose Images' : 'Image Limit Reached'}
              </Text>
            </Pressable>

            {/* existing images */}
            {existingImages.length > 0 ? (
              <View style={st.imgSection}>
                <Text style={st.imgSectionTitle}>Existing images</Text>
                <View style={st.imgGrid}>
                  {existingImages.map(img => {
                    const removed = removedImageIds.includes(img.id);
                    return (
                      <View
                        key={'existing-' + img.id}
                        style={[st.imgCard, removed && st.imgCardMuted]}>
                        {img.image_url ? (
                          <Image source={{uri: img.image_url}} style={st.imgPreview} />
                        ) : (
                          <View style={st.imgPlaceholder}>
                            <Text style={st.imgPlaceholderText}>Image</Text>
                          </View>
                        )}
                        <Pressable
                          onPress={() => toggleExistingImageRemoval(img.id)}
                          style={({pressed}) => [st.imgActionBtn, pressed && st.pressed]}>
                          <Text style={st.imgActionBtnText}>
                            {removed ? 'Undo Remove' : 'Remove'}
                          </Text>
                        </Pressable>
                      </View>
                    );
                  })}
                </View>
              </View>
            ) : null}

            {/* new images */}
            {newImages.length > 0 ? (
              <View style={st.imgSection}>
                <Text style={st.imgSectionTitle}>New images</Text>
                <View style={st.imgGrid}>
                  {newImages.map(img => (
                    <View key={img.uri} style={st.imgCard}>
                      <Image source={{uri: img.uri}} style={st.imgPreview} />
                      <Pressable
                        onPress={() => removeNewImage(img.uri)}
                        style={({pressed}) => [st.imgActionBtn, pressed && st.pressed]}>
                        <Text style={st.imgActionBtnText}>Remove</Text>
                      </Pressable>
                    </View>
                  ))}
                </View>
              </View>
            ) : null}
          </View>

          {/* \u2500 submit \u2500 */}
          <Pressable
            disabled={submitting || (mode === 'create' && noEligibleRequests)}
            onPress={handleSubmit}
            style={({pressed}) => [
              st.goldBtn,
              (submitting || (mode === 'create' && noEligibleRequests)) && st.goldBtnDisabled,
              pressed && st.pressed,
            ]}>
            <Text style={st.goldBtnText}>
              {submitting
                ? mode === 'create'
                  ? 'Submitting\u2026'
                  : 'Saving\u2026'
                : mode === 'create'
                  ? 'Submit Testimony'
                  : 'Save Changes'}
            </Text>
          </Pressable>
        </View>

        <View style={st.spacer} />
      </ScrollView>
    </SafeAreaView>
  );
}

/* \u2500\u2500 styles \u2500\u2500 */

const st = StyleSheet.create({
  safe: {flex: 1, backgroundColor: BG},
  scroll: {paddingHorizontal: 22, paddingTop: 20, paddingBottom: 30},
  pressed: {opacity: 0.85},

  centered: {
    flex: 1,
    alignItems: 'center',
    justifyContent: 'center',
    padding: 24,
  },
  loadingText: {color: MUTED, fontSize: 14, marginTop: 14},

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

  /* hero */
  title: {fontSize: 26, fontWeight: '900', color: NAVY, marginBottom: 4},
  subtitle: {fontSize: 14, color: MUTED, lineHeight: 20, marginBottom: 18},

  /* card */
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
  cardTitle: {fontSize: 20, fontWeight: '900', color: NAVY, marginBottom: 6},
  cardSubtitle: {
    fontSize: 14,
    color: MUTED,
    lineHeight: 20,
    marginBottom: 18,
  },

  /* banners */
  infoBanner: {
    backgroundColor: '#edf2fb',
    borderRadius: 16,
    padding: 14,
    marginBottom: 16,
  },
  warningBanner: {
    backgroundColor: '#fff1f2',
    borderColor: '#fecdd3',
    borderWidth: 1,
    borderRadius: 16,
    padding: 14,
    marginBottom: 16,
  },
  errorBanner: {
    backgroundColor: '#fef2f2',
    borderColor: '#fecaca',
    borderWidth: 1,
    borderRadius: 16,
    padding: 14,
    marginBottom: 16,
  },
  bannerLabel: {
    fontSize: 12,
    fontWeight: '800',
    color: NAVY,
    textTransform: 'uppercase',
    marginBottom: 4,
  },
  bannerText: {fontSize: 14, color: NAVY, opacity: 0.75, lineHeight: 20},

  /* empty notice */
  emptyNotice: {
    backgroundColor: '#f4f7fc',
    borderRadius: 16,
    padding: 16,
    marginBottom: 16,
  },
  emptyNoticeTitle: {
    fontSize: 15,
    fontWeight: '800',
    color: NAVY,
    marginBottom: 6,
  },
  emptyNoticeText: {fontSize: 14, color: MUTED, lineHeight: 20},

  /* field group */
  fieldGroup: {marginBottom: 18},
  fieldHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 10,
  },
  fieldLabel: {fontSize: 14, fontWeight: '800', color: NAVY},
  optionalTag: {
    fontSize: 11,
    fontWeight: '700',
    color: MUTED,
    textTransform: 'uppercase',
  },
  requiredTag: {
    fontSize: 11,
    fontWeight: '700',
    color: '#dc2626',
    textTransform: 'uppercase',
  },
  helpText: {fontSize: 13, color: MUTED, lineHeight: 19, marginTop: 6},
  fieldError: {
    color: '#b91c1c',
    fontSize: 13,
    fontWeight: '600',
    marginTop: 8,
  },

  /* selectors */
  selectorGroup: {gap: 10},
  selectorCard: {
    backgroundColor: '#f4f7fc',
    borderWidth: 1.5,
    borderColor: DIVIDER,
    borderRadius: 16,
    padding: 14,
  },
  selectorSelected: {
    backgroundColor: '#fef8e8',
    borderColor: GOLD,
  },
  selectorTitle: {
    fontSize: 14,
    fontWeight: '700',
    color: NAVY,
    lineHeight: 20,
    marginBottom: 3,
  },
  selectorSub: {fontSize: 12, fontWeight: '600', color: MUTED},

  /* rating */
  ratingRow: {flexDirection: 'row', flexWrap: 'wrap', gap: 8},
  ratingChip: {
    backgroundColor: '#f4f7fc',
    borderWidth: 1.5,
    borderColor: DIVIDER,
    borderRadius: 14,
    paddingHorizontal: 14,
    paddingVertical: 11,
  },
  ratingChipSelected: {
    backgroundColor: '#fef8e8',
    borderColor: GOLD,
  },
  ratingChipText: {fontSize: 14, fontWeight: '700', color: MUTED},
  ratingChipTextSelected: {color: '#b45309'},

  /* inputs */
  input: {
    backgroundColor: '#f4f7fc',
    borderWidth: 1,
    borderColor: DIVIDER,
    borderRadius: 14,
    paddingHorizontal: 16,
    paddingVertical: 13,
    fontSize: 15,
    color: NAVY,
    minHeight: 48,
  },
  textArea: {minHeight: 128},
  inputError: {borderColor: '#ef4444'},

  /* outline button */
  outlineBtn: {
    backgroundColor: CARD,
    borderWidth: 1,
    borderColor: DIVIDER,
    borderRadius: 24,
    paddingVertical: 13,
    alignItems: 'center',
  },
  outlineBtnDisabled: {opacity: 0.5},
  outlineBtnText: {fontSize: 14, fontWeight: '800', color: NAVY},
  outlineBtnTextDisabled: {color: MUTED},

  /* images */
  imgSection: {marginTop: 14},
  imgSectionTitle: {
    fontSize: 14,
    fontWeight: '800',
    color: NAVY,
    marginBottom: 10,
  },
  imgGrid: {flexDirection: 'row', flexWrap: 'wrap', gap: 12},
  imgCard: {width: '47%'},
  imgCardMuted: {opacity: 0.45},
  imgPreview: {
    width: '100%',
    height: 120,
    borderRadius: 16,
    backgroundColor: '#d6dff0',
    marginBottom: 8,
  },
  imgPlaceholder: {
    width: '100%',
    height: 120,
    borderRadius: 16,
    backgroundColor: '#d6dff0',
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 8,
  },
  imgPlaceholderText: {color: MUTED, fontSize: 13, fontWeight: '700'},
  imgActionBtn: {
    backgroundColor: CARD,
    borderWidth: 1,
    borderColor: DIVIDER,
    borderRadius: 14,
    paddingVertical: 10,
    alignItems: 'center',
  },
  imgActionBtnText: {fontSize: 13, fontWeight: '700', color: NAVY},

  /* gold submit */
  goldBtn: {
    backgroundColor: GOLD,
    borderRadius: 28,
    paddingVertical: 15,
    alignItems: 'center',
    marginTop: 4,
    shadowColor: GOLD,
    shadowOffset: {width: 0, height: 4},
    shadowOpacity: 0.25,
    shadowRadius: 10,
    elevation: 4,
  },
  goldBtnDisabled: {opacity: 0.5},
  goldBtnText: {
    fontSize: 16,
    fontWeight: '900',
    color: CARD,
    letterSpacing: 0.3,
  },

  spacer: {minHeight: 20},
});
