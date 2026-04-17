import React, {useEffect, useMemo, useState} from 'react';
import {
  ActivityIndicator,
  Alert,
  Image,
  Pressable,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  View,
} from 'react-native';
import {Asset, launchImageLibrary} from 'react-native-image-picker';

import {AppButton, AppCard, AppInput} from '../../components';
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

const MAX_TESTIMONY_IMAGES = 5;

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

function getFriendlyErrorMessage(error: unknown) {
  if (error instanceof ApiError) {
    if (error.status === 401) {
      return 'Your session has expired. Please log in again.';
    }

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

function formatServiceRequestLabel(serviceRequest: ServiceRequest) {
  const requestType = serviceRequest.request_type || 'Service';
  return `Service Request #${serviceRequest.id} - ${requestType}`;
}

function formatInspectionRequestLabel(inspectionRequest: InspectionRequest) {
  return `Inspection Request #${inspectionRequest.id}`;
}

function getExistingImageCount(
  existingImages: TestimonyImage[],
  removedImageIds: number[],
) {
  return existingImages.filter(image => !removedImageIds.includes(image.id)).length;
}

function isCompletedStatus(status?: string | null) {
  return (status || '').toLowerCase() === 'completed';
}

export default function TestimonyForm({
  mode,
  navigation,
  initialTestimony,
}: TestimonyFormProps) {
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

        if (!isMounted) {
          return;
        }

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
        if (isMounted) {
          setLoadingOptions(false);
        }
      }
    }

    loadEligibleRequests();

    return () => {
      isMounted = false;
    };
  }, []);

  const mergedServiceRequests = useMemo(() => {
    const list = [...serviceRequests];
    const initialServiceRequest = initialTestimony?.serviceRequest;

    if (
      initialServiceRequest?.id &&
      !list.some(serviceRequest => serviceRequest.id === initialServiceRequest.id)
    ) {
      list.unshift({
        id: initialServiceRequest.id,
        user_id: initialServiceRequest.user_id,
        technician_id: initialServiceRequest.technician_id,
        request_type: initialServiceRequest.request_type || 'Service',
        details: initialServiceRequest.request_type || 'Completed service request',
        date_needed: initialServiceRequest.date_needed,
        status: initialServiceRequest.status || 'completed',
      });
    }

    return list;
  }, [initialTestimony?.serviceRequest, serviceRequests]);

  const mergedInspectionRequests = useMemo(() => {
    const list = [...inspectionRequests];
    const initialInspectionRequest = initialTestimony?.inspectionRequest;

    if (
      initialInspectionRequest?.id &&
      !list.some(
        inspectionRequest => inspectionRequest.id === initialInspectionRequest.id,
      )
    ) {
      list.unshift({
        id: initialInspectionRequest.id,
        user_id: initialInspectionRequest.user_id,
        technician_id: initialInspectionRequest.technician_id,
        details: 'Completed inspection request',
        date_needed: initialInspectionRequest.date_needed,
        status: initialInspectionRequest.status || 'completed',
      });
    }

    return list;
  }, [initialTestimony?.inspectionRequest, inspectionRequests]);

  const existingImages = initialTestimony?.images || [];
  const activeExistingImageCount = getExistingImageCount(existingImages, removedImageIds);
  const remainingImageSlots = Math.max(
    0,
    MAX_TESTIMONY_IMAGES - activeExistingImageCount - newImages.length,
  );

  const clearError = (key?: keyof FieldErrors) => {
    if (errorMessage) {
      setErrorMessage('');
    }

    if (key && fieldErrors[key]) {
      setFieldErrors(currentErrors => ({
        ...currentErrors,
        [key]: undefined,
      }));
    }
  };

  const toggleExistingImageRemoval = (imageId: number) => {
    clearError();
    setRemovedImageIds(currentIds =>
      currentIds.includes(imageId)
        ? currentIds.filter(currentId => currentId !== imageId)
        : [...currentIds, imageId],
    );
  };

  const removeNewImage = (uri: string) => {
    clearError();
    setNewImages(currentImages =>
      currentImages.filter(image => image.uri !== uri),
    );
  };

  const handlePickImages = async () => {
    clearError();

    if (remainingImageSlots <= 0) {
      Alert.alert(
        'Image limit reached',
        `You can upload up to ${MAX_TESTIMONY_IMAGES} images per testimony.`,
      );
      return;
    }

    const result = await launchImageLibrary({
      mediaType: 'photo',
      selectionLimit: remainingImageSlots,
      quality: 0.8,
    });

    if (result.didCancel) {
      return;
    }

    if (result.errorMessage) {
      Alert.alert('Image selection failed', result.errorMessage);
      return;
    }

    const pickedImages = normalizePickedAssets(result.assets);

    if (pickedImages.length === 0) {
      return;
    }

    setNewImages(currentImages => [...currentImages, ...pickedImages]);
  };

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

  const handleSubmit = async () => {
    if (submitting || !validateForm()) {
      return;
    }

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
        {
          text: 'OK',
          onPress: () => navigation.goBack(),
        },
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

  if (loadingOptions) {
    return (
      <View style={styles.centeredContainer}>
        <ActivityIndicator color="#2563eb" size="large" />
        <Text style={styles.loadingText}>Loading completed requests...</Text>
      </View>
    );
  }

  return (
    <ScrollView
      contentContainerStyle={styles.container}
      keyboardShouldPersistTaps="handled"
      showsVerticalScrollIndicator={false}>
      <View style={styles.heroCard}>
        <Text style={styles.eyebrow}>
          {mode === 'create' ? 'Add testimony' : 'Edit testimony'}
        </Text>
        <Text style={styles.title}>
          {mode === 'create'
            ? 'Share your experience'
            : 'Update your testimony'}
        </Text>
        <Text style={styles.subtitle}>
          Select the completed request you want to review, add your rating, and
          attach photos if they help tell the story.
        </Text>
      </View>

      <AppCard style={styles.sectionCard}>
        <Text style={styles.sectionTitle}>Testimony details</Text>
        <Text style={styles.sectionSubtitle}>
          Your submission is sent securely using your saved customer login
          session.
        </Text>

        {mode === 'edit' && initialTestimony?.status === 'approved' ? (
          <View style={styles.infoBanner}>
            <Text style={styles.bannerTitle}>Heads up</Text>
            <Text style={styles.bannerText}>
              Editing an approved testimony will send it back for admin review.
            </Text>
          </View>
        ) : null}

        {mode === 'edit' &&
        initialTestimony?.status === 'rejected' &&
        initialTestimony.admin_note ? (
          <View style={styles.warningBanner}>
            <Text style={styles.bannerTitle}>Admin note</Text>
            <Text style={styles.bannerText}>{initialTestimony.admin_note}</Text>
          </View>
        ) : null}

        {errorMessage ? (
          <View style={styles.errorBanner}>
            <Text style={styles.bannerTitle}>Unable to save</Text>
            <Text style={styles.bannerText}>{errorMessage}</Text>
          </View>
        ) : null}

        {noEligibleRequests ? (
          <View style={styles.emptyCard}>
            <Text style={styles.emptyTitle}>No completed requests available</Text>
            <Text style={styles.emptyText}>
              You can submit a testimony after one of your service or inspection
              requests reaches completed status.
            </Text>
          </View>
        ) : null}

        <View style={styles.fieldGroup}>
          <View style={styles.fieldHeader}>
            <Text style={styles.fieldLabel}>Completed service requests</Text>
            <Text style={styles.optionalText}>Optional</Text>
          </View>

          {mergedServiceRequests.length === 0 ? (
            <Text style={styles.helpText}>
              No completed service requests are available yet.
            </Text>
          ) : (
            <View style={styles.selectorGroup}>
              {mergedServiceRequests.map(serviceRequest => {
                const isSelected =
                  selectedServiceRequestId === serviceRequest.id;

                return (
                  <Pressable
                    key={`service-${serviceRequest.id}`}
                    onPress={() => {
                      clearError('linkedRequest');
                      setSelectedServiceRequestId(currentId => {
                        const nextServiceRequestId =
                          currentId === serviceRequest.id
                            ? null
                            : serviceRequest.id;

                        setSelectedInspectionRequestId(null);

                        return nextServiceRequestId;
                      });
                    }}
                    style={({pressed}) => [
                      styles.selectorCard,
                      isSelected ? styles.selectorCardSelected : null,
                      pressed ? styles.selectorCardPressed : null,
                    ]}>
                    <Text style={styles.selectorTitle}>
                      {formatServiceRequestLabel(serviceRequest)}
                    </Text>
                    <Text style={styles.selectorSubtitle}>
                      Status: {(serviceRequest.status || 'completed').toUpperCase()}
                    </Text>
                  </Pressable>
                );
              })}
            </View>
          )}
        </View>

        <View style={styles.fieldGroup}>
          <View style={styles.fieldHeader}>
            <Text style={styles.fieldLabel}>Completed inspection requests</Text>
            <Text style={styles.optionalText}>Optional</Text>
          </View>

          {mergedInspectionRequests.length === 0 ? (
            <Text style={styles.helpText}>
              No completed inspection requests are available yet.
            </Text>
          ) : (
            <View style={styles.selectorGroup}>
              {mergedInspectionRequests.map(inspectionRequest => {
                const isSelected =
                  selectedInspectionRequestId === inspectionRequest.id;

                return (
                  <Pressable
                    key={`inspection-${inspectionRequest.id}`}
                    onPress={() => {
                      clearError('linkedRequest');
                      setSelectedInspectionRequestId(currentId => {
                        const nextInspectionRequestId =
                          currentId === inspectionRequest.id
                            ? null
                            : inspectionRequest.id;

                        setSelectedServiceRequestId(null);

                        return nextInspectionRequestId;
                      });
                    }}
                    style={({pressed}) => [
                      styles.selectorCard,
                      isSelected ? styles.selectorCardSelected : null,
                      pressed ? styles.selectorCardPressed : null,
                    ]}>
                    <Text style={styles.selectorTitle}>
                      {formatInspectionRequestLabel(inspectionRequest)}
                    </Text>
                    <Text style={styles.selectorSubtitle}>
                      Status: {(inspectionRequest.status || 'completed').toUpperCase()}
                    </Text>
                  </Pressable>
                );
              })}
            </View>
          )}

          <Text style={styles.helpText}>
            Select at least one completed request that this testimony is about.
          </Text>
          {fieldErrors.linkedRequest ? (
            <Text style={styles.fieldErrorText}>{fieldErrors.linkedRequest}</Text>
          ) : null}
        </View>

        <View style={styles.fieldGroup}>
          <View style={styles.fieldHeader}>
            <Text style={styles.fieldLabel}>Rating</Text>
            <Text style={styles.requiredText}>Required</Text>
          </View>

          <View style={styles.ratingRow}>
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
                    styles.ratingChip,
                    isSelected ? styles.ratingChipSelected : null,
                    pressed ? styles.selectorCardPressed : null,
                  ]}>
                  <Text
                    style={[
                      styles.ratingChipText,
                      isSelected ? styles.ratingChipTextSelected : null,
                    ]}>
                    {'★'.repeat(value)}
                  </Text>
                </Pressable>
              );
            })}
          </View>
          {fieldErrors.rating ? (
            <Text style={styles.fieldErrorText}>{fieldErrors.rating}</Text>
          ) : null}
        </View>

        <AppInput
          containerStyle={styles.fieldGroup}
          label="Title"
          onChangeText={value => {
            clearError();
            setTitle(value);
          }}
          placeholder="Optional title for your testimony"
          value={title}
        />

        <View style={styles.fieldGroup}>
          <View style={styles.fieldHeader}>
            <Text style={styles.fieldLabel}>Message</Text>
            <Text style={styles.requiredText}>Required</Text>
          </View>

          <TextInput
            multiline={true}
            numberOfLines={6}
            onChangeText={value => {
              clearError('message');
              setMessage(value);
            }}
            placeholder="Share what went well, what stood out, or what others should know."
            placeholderTextColor="#94a3b8"
            style={[
              styles.input,
              styles.textArea,
              fieldErrors.message ? styles.inputError : null,
            ]}
            textAlignVertical="top"
            value={message}
          />
          {fieldErrors.message ? (
            <Text style={styles.fieldErrorText}>{fieldErrors.message}</Text>
          ) : null}
        </View>

        <View style={styles.fieldGroup}>
          <View style={styles.fieldHeader}>
            <Text style={styles.fieldLabel}>Images</Text>
            <Text style={styles.optionalText}>
              Up to {MAX_TESTIMONY_IMAGES}
            </Text>
          </View>

          <Text style={styles.helpText}>
            Add photos that support your testimony. Existing images stay unless
            you remove them.
          </Text>

          <AppButton
            title={
              remainingImageSlots > 0
                ? 'Choose images'
                : 'Image limit reached'
            }
            variant="outline"
            disabled={remainingImageSlots <= 0}
            style={styles.imagePickerButton}
            onPress={handlePickImages}
          />

          {existingImages.length > 0 ? (
            <View style={styles.imageSection}>
              <Text style={styles.imageSectionTitle}>Existing images</Text>
              <View style={styles.imageGrid}>
                {existingImages.map(image => {
                  const markedForRemoval = removedImageIds.includes(image.id);

                  return (
                    <View
                      key={`existing-${image.id}`}
                      style={[
                        styles.imageCard,
                        markedForRemoval ? styles.imageCardMuted : null,
                      ]}>
                      {image.image_url ? (
                        <Image
                          source={{uri: image.image_url}}
                          style={styles.imagePreview}
                        />
                      ) : (
                        <View style={styles.imagePlaceholder}>
                          <Text style={styles.imagePlaceholderText}>Image</Text>
                        </View>
                      )}
                      <AppButton
                        title={markedForRemoval ? 'Undo remove' : 'Remove'}
                        variant="outline"
                        style={styles.imageActionButton}
                        onPress={() => toggleExistingImageRemoval(image.id)}
                      />
                    </View>
                  );
                })}
              </View>
            </View>
          ) : null}

          {newImages.length > 0 ? (
            <View style={styles.imageSection}>
              <Text style={styles.imageSectionTitle}>New images</Text>
              <View style={styles.imageGrid}>
                {newImages.map(image => (
                  <View key={image.uri} style={styles.imageCard}>
                    <Image source={{uri: image.uri}} style={styles.imagePreview} />
                    <AppButton
                      title="Remove"
                      variant="outline"
                      style={styles.imageActionButton}
                      onPress={() => removeNewImage(image.uri)}
                    />
                  </View>
                ))}
              </View>
            </View>
          ) : null}
        </View>

        <AppButton
          title={
            submitting
              ? mode === 'create'
                ? 'Submitting...'
                : 'Saving...'
              : mode === 'create'
                ? 'Submit testimony'
                : 'Save changes'
          }
          disabled={submitting || (mode === 'create' && noEligibleRequests)}
          onPress={handleSubmit}
        />
      </AppCard>
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: {
    backgroundColor: '#f5f7fb',
    padding: 20,
    paddingBottom: 28,
  },
  centeredContainer: {
    alignItems: 'center',
    backgroundColor: '#f5f7fb',
    flex: 1,
    justifyContent: 'center',
    padding: 20,
  },
  loadingText: {
    color: '#475569',
    fontSize: 14,
    marginTop: 12,
  },
  heroCard: {
    backgroundColor: '#dbeafe',
    borderRadius: 28,
    marginBottom: 18,
    padding: 22,
  },
  eyebrow: {
    color: '#1d4ed8',
    fontSize: 12,
    fontWeight: '800',
    letterSpacing: 0.6,
    marginBottom: 8,
    textTransform: 'uppercase',
  },
  title: {
    color: '#0f172a',
    fontSize: 28,
    fontWeight: '800',
    lineHeight: 34,
    marginBottom: 10,
  },
  subtitle: {
    color: '#475569',
    fontSize: 14,
    lineHeight: 21,
  },
  sectionCard: {
    borderRadius: 24,
  },
  sectionTitle: {
    color: '#0f172a',
    fontSize: 22,
    fontWeight: '800',
    marginBottom: 8,
  },
  sectionSubtitle: {
    color: '#64748b',
    fontSize: 14,
    lineHeight: 20,
    marginBottom: 18,
  },
  fieldGroup: {
    marginBottom: 18,
  },
  fieldHeader: {
    alignItems: 'center',
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 10,
  },
  fieldLabel: {
    color: '#0f172a',
    fontSize: 15,
    fontWeight: '700',
  },
  requiredText: {
    color: '#dc2626',
    fontSize: 12,
    fontWeight: '700',
    textTransform: 'uppercase',
  },
  optionalText: {
    color: '#64748b',
    fontSize: 12,
    fontWeight: '700',
    textTransform: 'uppercase',
  },
  selectorGroup: {
    gap: 10,
  },
  selectorCard: {
    backgroundColor: '#f8fafc',
    borderColor: '#dbeafe',
    borderRadius: 18,
    borderWidth: 1,
    padding: 14,
  },
  selectorCardSelected: {
    backgroundColor: '#dbeafe',
    borderColor: '#2563eb',
  },
  selectorCardPressed: {
    opacity: 0.88,
  },
  selectorTitle: {
    color: '#0f172a',
    fontSize: 14,
    fontWeight: '700',
    lineHeight: 20,
    marginBottom: 4,
  },
  selectorSubtitle: {
    color: '#475569',
    fontSize: 12,
    fontWeight: '600',
  },
  helpText: {
    color: '#64748b',
    fontSize: 13,
    lineHeight: 19,
    marginTop: 8,
  },
  ratingRow: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 10,
  },
  ratingChip: {
    backgroundColor: '#f8fafc',
    borderColor: '#cbd5e1',
    borderRadius: 16,
    borderWidth: 1,
    paddingHorizontal: 14,
    paddingVertical: 12,
  },
  ratingChipSelected: {
    backgroundColor: '#fef3c7',
    borderColor: '#f59e0b',
  },
  ratingChipText: {
    color: '#475569',
    fontSize: 14,
    fontWeight: '700',
  },
  ratingChipTextSelected: {
    color: '#b45309',
  },
  input: {
    backgroundColor: '#f9fafb',
    borderColor: '#d1d5db',
    borderRadius: 12,
    borderWidth: 1,
    color: '#111827',
    fontSize: 16,
    minHeight: 48,
    paddingHorizontal: 14,
    paddingVertical: 12,
  },
  textArea: {
    minHeight: 128,
  },
  inputError: {
    borderColor: '#ef4444',
  },
  fieldErrorText: {
    color: '#b91c1c',
    fontSize: 13,
    fontWeight: '600',
    marginTop: 8,
  },
  errorBanner: {
    backgroundColor: '#fef2f2',
    borderColor: '#fecaca',
    borderRadius: 18,
    borderWidth: 1,
    marginBottom: 18,
    padding: 14,
  },
  warningBanner: {
    backgroundColor: '#fff1f2',
    borderColor: '#fecdd3',
    borderRadius: 18,
    borderWidth: 1,
    marginBottom: 18,
    padding: 14,
  },
  infoBanner: {
    backgroundColor: '#eff6ff',
    borderColor: '#bfdbfe',
    borderRadius: 18,
    borderWidth: 1,
    marginBottom: 18,
    padding: 14,
  },
  bannerTitle: {
    color: '#0f172a',
    fontSize: 13,
    fontWeight: '800',
    marginBottom: 6,
    textTransform: 'uppercase',
  },
  bannerText: {
    color: '#475569',
    fontSize: 14,
    lineHeight: 20,
  },
  emptyCard: {
    backgroundColor: '#ffffff',
    borderColor: '#e2e8f0',
    borderRadius: 18,
    borderWidth: 1,
    marginBottom: 18,
    padding: 16,
  },
  emptyTitle: {
    color: '#0f172a',
    fontSize: 16,
    fontWeight: '700',
    marginBottom: 8,
  },
  emptyText: {
    color: '#64748b',
    fontSize: 14,
    lineHeight: 20,
  },
  imagePickerButton: {
    marginTop: 6,
  },
  imageSection: {
    marginTop: 14,
  },
  imageSectionTitle: {
    color: '#0f172a',
    fontSize: 14,
    fontWeight: '700',
    marginBottom: 10,
  },
  imageGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 12,
  },
  imageCard: {
    width: '47%',
  },
  imageCardMuted: {
    opacity: 0.5,
  },
  imagePreview: {
    backgroundColor: '#dbeafe',
    borderRadius: 18,
    height: 120,
    marginBottom: 8,
    width: '100%',
  },
  imagePlaceholder: {
    alignItems: 'center',
    backgroundColor: '#dbeafe',
    borderRadius: 18,
    height: 120,
    justifyContent: 'center',
    marginBottom: 8,
    width: '100%',
  },
  imagePlaceholderText: {
    color: '#2563eb',
    fontSize: 13,
    fontWeight: '700',
  },
  imageActionButton: {
    minHeight: 42,
  },
});
