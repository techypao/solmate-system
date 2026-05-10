import React, {useEffect, useState} from 'react';
import {
  Alert,
  Image,
  Pressable,
  ScrollView,
  StyleSheet,
  Text,
  View,
} from 'react-native';
import {Asset, launchImageLibrary} from 'react-native-image-picker';

import AppButton from './AppButton';
import AppInput from './AppInput';
import {
  CompletionReport,
  LocalCompletionPhoto,
  ServiceCompletionReportPayload,
} from '../src/services/completionReportApi';
import {getSolmateStatusColors, solmateColors} from '../src/theme/colors';

const NAVY = solmateColors.navy;
const GOLD = solmateColors.primary;
const MUTED = solmateColors.muted;
const SOFT = solmateColors.backgroundSoft;

function formatDateTime(value?: string | null) {
  if (!value) {
    return 'Not available';
  }

  const parsedDate = new Date(value);

  if (Number.isNaN(parsedDate.getTime())) {
    return value;
  }

  return parsedDate.toLocaleString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    hour: 'numeric',
    minute: '2-digit',
  });
}

function statusMeta(status?: string | null) {
  if ((status || '').toLowerCase() === 'approved') {
    const approvedColors = getSolmateStatusColors('approved');
    return {
      label: 'Approved',
      backgroundColor: approvedColors.backgroundColor,
      textColor: approvedColors.textColor,
    };
  }

  const pendingColors = getSolmateStatusColors('pending');
  return {
    label: 'Awaiting admin approval',
    backgroundColor: pendingColors.backgroundColor,
    textColor: pendingColors.textColor,
  };
}

function normalizePickedAssets(assets?: Asset[]): LocalCompletionPhoto[] {
  return (assets || [])
    .filter(asset => !!asset.uri)
    .map(asset => ({
      uri: asset.uri as string,
      type: asset.type || 'image/jpeg',
      name: asset.fileName || null,
    }));
}

type Props = {
  title: string;
  subtitle: string;
  report?: CompletionReport | null;
  canSubmit: boolean;
  submitting?: boolean;
  onSubmit: (payload: ServiceCompletionReportPayload) => Promise<void> | void;
};

export default function ServiceCompletionReportCard({
  title,
  subtitle,
  report,
  canSubmit,
  submitting = false,
  onSubmit,
}: Props) {
  const [reportText, setReportText] = useState(report?.report_text || '');
  const [photos, setPhotos] = useState<LocalCompletionPhoto[]>([]);
  const [errors, setErrors] = useState<{summary?: string; photos?: string}>({});

  useEffect(() => {
    setReportText(report?.report_text || '');
    setPhotos([]);
    setErrors({});
  }, [report]);

  const hasSubmittedReport = !!report;

  const handlePickPhotos = async () => {
    const result = await launchImageLibrary({
      mediaType: 'photo',
      selectionLimit: 0,
      quality: 0.8,
    });

    if (result.didCancel) {
      return;
    }

    if (result.errorMessage) {
      Alert.alert('Photo selection failed', result.errorMessage);
      return;
    }

    const picked = normalizePickedAssets(result.assets);
    if (picked.length === 0) {
      return;
    }

    setPhotos(current => [...current, ...picked]);
    setErrors(current => ({...current, photos: undefined}));
  };

  const handleRemovePhoto = (uri: string) => {
    setPhotos(current => current.filter(p => p.uri !== uri));
  };

  const handleSubmit = async () => {
    const trimmedReportText = reportText.trim();
    const nextErrors: {summary?: string; photos?: string} = {};

    if (!trimmedReportText) {
      nextErrors.summary = 'Completion summary is required.';
    }

    if (photos.length === 0) {
      nextErrors.photos = 'At least one completion photo is required.';
    }

    if (Object.keys(nextErrors).length > 0) {
      setErrors(nextErrors);
      return;
    }

    setErrors({});

    await onSubmit({
      report_text: trimmedReportText,
      completion_photos: photos,
      completed_at: new Date().toISOString(),
    });
  };

  if (hasSubmittedReport) {
    const meta = statusMeta(report?.status);
    const reportPhotos = report?.photos ?? [];

    return (
      <View style={styles.card}>
        <View style={styles.headerRow}>
          <View style={styles.headerCopy}>
            <Text style={styles.cardTitle}>{title}</Text>
            <Text style={styles.cardSubtitle}>{subtitle}</Text>
          </View>
          <View
            style={[styles.statusChip, {backgroundColor: meta.backgroundColor}]}>
            <Text style={[styles.statusChipText, {color: meta.textColor}]}>
              {meta.label}
            </Text>
          </View>
        </View>

        <View style={styles.metaRow}>
          <Text style={styles.metaLabel}>Submitted</Text>
          <Text style={styles.metaValue}>
            {formatDateTime(report?.submitted_at)}
          </Text>
        </View>
        <View style={styles.metaRow}>
          <Text style={styles.metaLabel}>Completed At</Text>
          <Text style={styles.metaValue}>
            {formatDateTime(report?.completed_at)}
          </Text>
        </View>
        <View style={styles.metaRow}>
          <Text style={styles.metaLabel}>Approved At</Text>
          <Text style={styles.metaValue}>
            {(report?.status || '').toLowerCase() === 'approved'
              ? formatDateTime(report?.approved_at)
              : 'Pending admin approval'}
          </Text>
        </View>

        <View style={styles.readOnlyBlock}>
          <Text style={styles.fieldLabel}>Completion Summary</Text>
          <Text style={styles.readOnlyText}>
            {report?.report_text || 'Not available'}
          </Text>
        </View>

        {reportPhotos.length > 0 ? (
          <View style={styles.readOnlyBlock}>
            <Text style={styles.fieldLabel}>
              Completion Photos ({reportPhotos.length})
            </Text>
            <View style={styles.photoGrid}>
              {reportPhotos.map(photo => (
                <Image
                  key={photo.id}
                  source={{uri: photo.image_url ?? undefined}}
                  style={styles.photoPreview}
                  resizeMode="cover"
                />
              ))}
            </View>
          </View>
        ) : null}
      </View>
    );
  }

  return (
    <View style={styles.card}>
      <Text style={styles.cardTitle}>{title}</Text>
      <Text style={styles.cardSubtitle}>
        {canSubmit
          ? subtitle
          : 'Move this task to In Progress before submitting the completion report.'}
      </Text>

      <AppInput
        label="Completion Summary"
        value={reportText}
        onChangeText={text => {
          setReportText(text);
          if (text.trim()) {
            setErrors(current => ({...current, summary: undefined}));
          }
        }}
        placeholder="Describe the work completed, outcome, and proof of completion."
        multiline
        numberOfLines={5}
        editable={canSubmit && !submitting}
        style={styles.textArea}
      />
      {errors.summary ? (
        <Text style={styles.errorText}>{errors.summary}</Text>
      ) : null}

      <View style={styles.photoSection}>
        <Text style={styles.photoSectionLabel}>
          Completion Photos <Text style={styles.requiredTag}>Required</Text>
        </Text>
        <Text style={styles.photoSectionHint}>
          Upload at least one photo as proof of completion.
        </Text>

        <Pressable
          onPress={handlePickPhotos}
          disabled={!canSubmit || submitting}
          style={({pressed}) => [
            styles.photoPickerBtn,
            (!canSubmit || submitting) && styles.photoPickerBtnDisabled,
            pressed && styles.pressed,
          ]}>
          <Text
            style={[
              styles.photoPickerBtnText,
              (!canSubmit || submitting) && styles.photoPickerBtnTextDisabled,
            ]}>
            Add Photos
          </Text>
        </Pressable>

        {errors.photos ? (
          <Text style={styles.errorText}>{errors.photos}</Text>
        ) : null}

        {photos.length > 0 ? (
          <View style={styles.photoGrid}>
            {photos.map(photo => (
              <View key={photo.uri} style={styles.photoCard}>
                <Image
                  source={{uri: photo.uri}}
                  style={styles.photoPreview}
                  resizeMode="cover"
                />
                <Pressable
                  onPress={() => handleRemovePhoto(photo.uri)}
                  disabled={submitting}
                  style={({pressed}) => [
                    styles.photoRemoveBtn,
                    pressed && styles.pressed,
                  ]}>
                  <Text style={styles.photoRemoveBtnText}>Remove</Text>
                </Pressable>
              </View>
            ))}
          </View>
        ) : null}
      </View>

      <AppButton
        title={submitting ? 'Submitting...' : 'Submit Completion Report'}
        disabled={!canSubmit || submitting}
        onPress={handleSubmit}
        style={styles.submitButton}
        textStyle={styles.submitButtonText}
      />
    </View>
  );
}

const styles = StyleSheet.create({
  card: {
    backgroundColor: solmateColors.white,
    borderColor: solmateColors.border,
    borderWidth: 1,
    borderRadius: 20,
    marginBottom: 16,
    padding: 18,
    shadowColor: solmateColors.shadow,
    shadowOffset: {width: 0, height: 4},
    shadowOpacity: 0.1,
    shadowRadius: 12,
    elevation: 3,
  },
  headerRow: {
    flexDirection: 'row',
    gap: 10,
    justifyContent: 'space-between',
    marginBottom: 12,
  },
  headerCopy: {
    flex: 1,
  },
  cardTitle: {
    color: NAVY,
    fontSize: 17,
    fontWeight: '800',
    marginBottom: 6,
  },
  cardSubtitle: {
    color: MUTED,
    fontSize: 13,
    lineHeight: 20,
    marginBottom: 14,
  },
  statusChip: {
    alignSelf: 'flex-start',
    borderRadius: 999,
    paddingHorizontal: 10,
    paddingVertical: 6,
  },
  statusChipText: {
    fontSize: 11,
    fontWeight: '700',
  },
  textArea: {
    minHeight: 96,
    textAlignVertical: 'top',
  },
  submitButton: {
    backgroundColor: GOLD,
    borderColor: GOLD,
    marginTop: 10,
  },
  submitButtonText: {
    color: NAVY,
    fontWeight: '800',
  },
  errorText: {
    color: solmateColors.danger,
    fontSize: 13,
    marginTop: 4,
    marginBottom: 4,
  },
  metaRow: {
    alignItems: 'center',
    borderTopColor: solmateColors.border,
    borderTopWidth: 1,
    flexDirection: 'row',
    justifyContent: 'space-between',
    paddingVertical: 10,
  },
  metaLabel: {
    color: MUTED,
    flex: 1,
    fontSize: 13,
  },
  metaValue: {
    color: NAVY,
    flex: 1,
    fontSize: 13,
    fontWeight: '700',
    textAlign: 'right',
  },
  readOnlyBlock: {
    backgroundColor: SOFT,
    borderRadius: 14,
    marginTop: 12,
    padding: 14,
  },
  fieldLabel: {
    color: NAVY,
    fontSize: 12,
    fontWeight: '800',
    marginBottom: 8,
    textTransform: 'uppercase',
  },
  readOnlyText: {
    color: solmateColors.text,
    fontSize: 14,
    lineHeight: 22,
  },
  photoSection: {
    marginTop: 16,
  },
  photoSectionLabel: {
    color: NAVY,
    fontSize: 14,
    fontWeight: '700',
    marginBottom: 4,
  },
  requiredTag: {
    color: solmateColors.danger,
    fontSize: 12,
    fontWeight: '600',
  },
  photoSectionHint: {
    color: MUTED,
    fontSize: 13,
    lineHeight: 18,
    marginBottom: 10,
  },
  photoPickerBtn: {
    alignItems: 'center',
    borderColor: NAVY,
    borderRadius: 12,
    borderWidth: 1.5,
    paddingVertical: 10,
    paddingHorizontal: 16,
  },
  photoPickerBtnDisabled: {
    borderColor: solmateColors.border,
    opacity: 0.5,
  },
  photoPickerBtnText: {
    color: NAVY,
    fontSize: 14,
    fontWeight: '700',
  },
  photoPickerBtnTextDisabled: {
    color: MUTED,
  },
  photoGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 10,
    marginTop: 12,
  },
  photoCard: {
    alignItems: 'center',
    width: '47%',
  },
  photoPreview: {
    borderRadius: 10,
    height: 120,
    width: '100%',
  },
  photoRemoveBtn: {
    marginTop: 6,
    paddingVertical: 4,
    paddingHorizontal: 12,
    borderRadius: 8,
    backgroundColor: solmateColors.danger + '18',
  },
  photoRemoveBtnText: {
    color: solmateColors.danger,
    fontSize: 12,
    fontWeight: '700',
  },
  pressed: {
    opacity: 0.7,
  },
});
