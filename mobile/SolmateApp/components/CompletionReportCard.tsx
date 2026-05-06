import React, {useEffect, useState} from 'react';
import {StyleSheet, Text, View} from 'react-native';

import AppButton from './AppButton';
import AppInput from './AppInput';
import {CompletionReport, CompletionReportPayload} from '../src/services/completionReportApi';

const NAVY = '#1a2f5e';
const GOLD = '#e8a800';
const MUTED = '#64748b';
const SOFT = '#eef4ff';
const SUCCESS_BG = '#dcfce7';
const SUCCESS_TEXT = '#166534';
const WARNING_BG = '#fef3c7';
const WARNING_TEXT = '#92400e';

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
    return {
      label: 'Approved',
      backgroundColor: SUCCESS_BG,
      textColor: SUCCESS_TEXT,
    };
  }

  return {
    label: 'Awaiting admin approval',
    backgroundColor: WARNING_BG,
    textColor: WARNING_TEXT,
  };
}

type Props = {
  title: string;
  subtitle: string;
  report?: CompletionReport | null;
  canSubmit: boolean;
  submitting?: boolean;
  onSubmit: (payload: CompletionReportPayload) => Promise<void> | void;
};

export default function CompletionReportCard({
  title,
  subtitle,
  report,
  canSubmit,
  submitting = false,
  onSubmit,
}: Props) {
  const [reportText, setReportText] = useState(report?.report_text || '');
  const [findings, setFindings] = useState(report?.findings || '');
  const [recommendations, setRecommendations] = useState(
    report?.recommendations || '',
  );
  const [errorText, setErrorText] = useState('');

  useEffect(() => {
    setReportText(report?.report_text || '');
    setFindings(report?.findings || '');
    setRecommendations(report?.recommendations || '');
    setErrorText('');
  }, [report]);

  const hasSubmittedReport = !!report;

  const handleSubmit = async () => {
    const trimmedReportText = reportText.trim();

    if (!trimmedReportText) {
      setErrorText('Please provide the completion summary before submitting.');
      return;
    }

    setErrorText('');

    await onSubmit({
      report_text: trimmedReportText,
      findings: findings.trim() || undefined,
      recommendations: recommendations.trim() || undefined,
      completed_at: new Date().toISOString(),
    });
  };

  if (hasSubmittedReport) {
    const meta = statusMeta(report?.status);

    return (
      <View style={styles.card}>
        <View style={styles.headerRow}>
          <View style={styles.headerCopy}>
            <Text style={styles.cardTitle}>{title}</Text>
            <Text style={styles.cardSubtitle}>{subtitle}</Text>
          </View>
          <View style={[styles.statusChip, {backgroundColor: meta.backgroundColor}]}>
            <Text style={[styles.statusChipText, {color: meta.textColor}]}>
              {meta.label}
            </Text>
          </View>
        </View>

        <View style={styles.metaRow}>
          <Text style={styles.metaLabel}>Submitted</Text>
          <Text style={styles.metaValue}>{formatDateTime(report?.submitted_at)}</Text>
        </View>
        <View style={styles.metaRow}>
          <Text style={styles.metaLabel}>Completed At</Text>
          <Text style={styles.metaValue}>{formatDateTime(report?.completed_at)}</Text>
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
          <Text style={styles.readOnlyText}>{report?.report_text || 'Not available'}</Text>
        </View>

        {report?.findings ? (
          <View style={styles.readOnlyBlock}>
            <Text style={styles.fieldLabel}>Findings</Text>
            <Text style={styles.readOnlyText}>{report.findings}</Text>
          </View>
        ) : null}

        {report?.recommendations ? (
          <View style={styles.readOnlyBlock}>
            <Text style={styles.fieldLabel}>Recommendations</Text>
            <Text style={styles.readOnlyText}>{report.recommendations}</Text>
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
          : 'Move this task to In Progress before submitting the completion notes.'}
      </Text>

      <AppInput
        label="Completion Summary"
        value={reportText}
        onChangeText={setReportText}
        placeholder="Describe the work completed, outcome, and proof of completion."
        multiline
        numberOfLines={5}
        editable={canSubmit && !submitting}
        style={styles.textArea}
      />

      <AppInput
        label="Findings"
        value={findings}
        onChangeText={setFindings}
        placeholder="Optional: note issues observed, replaced parts, or system condition."
        multiline
        numberOfLines={4}
        editable={canSubmit && !submitting}
        style={styles.textArea}
      />

      <AppInput
        label="Recommendations"
        value={recommendations}
        onChangeText={setRecommendations}
        placeholder="Optional: next steps, follow-up maintenance, or customer reminders."
        multiline
        numberOfLines={4}
        editable={canSubmit && !submitting}
        style={styles.textArea}
      />

      {errorText ? <Text style={styles.errorText}>{errorText}</Text> : null}

      <AppButton
        title={submitting ? 'Submitting...' : 'Submit Completion Notes'}
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
    backgroundColor: '#ffffff',
    borderRadius: 20,
    marginBottom: 16,
    padding: 18,
    shadowColor: '#8a9bbd',
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
    color: '#b91c1c',
    fontSize: 13,
    marginTop: 4,
  },
  metaRow: {
    alignItems: 'center',
    borderTopColor: '#e2e8f0',
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
    color: '#334155',
    fontSize: 14,
    lineHeight: 22,
  },
});
