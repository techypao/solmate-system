import React, {useCallback, useMemo, useState} from 'react';
import {
  ActivityIndicator,
  Alert,
  Pressable,
  SafeAreaView,
  ScrollView,
  StyleSheet,
  Switch,
  Text,
  View,
} from 'react-native';
import {useFocusEffect} from '@react-navigation/native';

import {AppButton, AppCard, AppInput} from '../components';
import {ApiError} from '../src/services/api';
import {QuotationStatus, submitFinalQuotation} from '../src/services/quotationApi';
import {
  getAssignedInspectionRequestById,
  TechnicianInspectionRequest,
} from '../src/services/technicianApi';
import {
  canCreateFinalQuotation,
  formatDate,
  formatServiceRequestStatus,
  getCustomerName,
} from '../src/utils/technicianRequests';

type PvSystemType = 'hybrid' | 'on-grid' | 'off-grid';

type FinalQuotationFormState = {
  monthly_electric_bill: string;
  rate_per_kwh: string;
  days_in_month: string;
  sun_hours: string;
  pv_safety_factor: string;
  battery_factor: string;
  battery_voltage: string;
  pv_system_type: PvSystemType;
  with_battery: boolean;
  inverter_type: string;
  battery_model: string;
  battery_capacity_ah: string;
  panel_watts: string;
  panel_cost: string;
  inverter_cost: string;
  battery_cost: string;
  bos_cost: string;
  materials_subtotal: string;
  labor_cost: string;
  project_cost: string;
  status: QuotationStatus;
  remarks: string;
};

const PV_SYSTEM_OPTIONS: PvSystemType[] = ['hybrid', 'on-grid', 'off-grid'];
const STATUS_OPTIONS: QuotationStatus[] = [
  'pending',
  'approved',
  'rejected',
  'completed',
];

function sanitizeNumericInput(value: string) {
  const cleanedValue = value.replace(/[^0-9.]/g, '');
  const parts = cleanedValue.split('.');

  if (parts.length <= 1) {
    return cleanedValue;
  }

  return `${parts[0]}.${parts.slice(1).join('')}`;
}

function sanitizeIntegerInput(value: string) {
  return value.replace(/[^0-9]/g, '');
}

function toNumberOrUndefined(value: string) {
  const trimmedValue = value.trim();

  if (!trimmedValue) {
    return undefined;
  }

  const parsedValue = Number(trimmedValue);

  if (Number.isNaN(parsedValue)) {
    return undefined;
  }

  return parsedValue;
}

function formatLaravelErrors(error: ApiError) {
  if (!error.errors) {
    return error.message;
  }

  const messages = Object.values(error.errors).flat();
  return messages.length > 0 ? messages.join('\n') : error.message;
}

function getFriendlyErrorMessage(error: unknown) {
  if (error instanceof ApiError) {
    return error.message;
  }

  return 'Could not load the completed inspection request for final quotation.';
}

function FormSection({
  title,
  subtitle,
  children,
}: {
  title: string;
  subtitle: string;
  children: React.ReactNode;
}) {
  return (
    <AppCard style={styles.sectionCard}>
      <Text style={styles.sectionTitle}>{title}</Text>
      <Text style={styles.sectionSubtitle}>{subtitle}</Text>
      {children}
    </AppCard>
  );
}

function OptionChip({
  label,
  selected,
  onPress,
}: {
  label: string;
  selected: boolean;
  onPress: () => void;
}) {
  return (
    <Pressable
      onPress={onPress}
      style={({pressed}) => [
        styles.optionChip,
        selected ? styles.optionChipSelected : null,
        pressed ? styles.optionChipPressed : null,
      ]}>
      <Text
        style={[
          styles.optionChipText,
          selected ? styles.optionChipTextSelected : null,
        ]}>
        {label}
      </Text>
    </Pressable>
  );
}

function buildInitialFormState(): FinalQuotationFormState {
  return {
    monthly_electric_bill: '',
    rate_per_kwh: '14',
    days_in_month: '30',
    sun_hours: '4.5',
    pv_safety_factor: '1.8',
    battery_factor: '1',
    battery_voltage: '51.2',
    pv_system_type: 'hybrid',
    with_battery: true,
    inverter_type: '',
    battery_model: '',
    battery_capacity_ah: '',
    panel_watts: '610',
    panel_cost: '',
    inverter_cost: '',
    battery_cost: '',
    bos_cost: '',
    materials_subtotal: '',
    labor_cost: '',
    project_cost: '',
    status: 'pending',
    remarks: '',
  };
}

export default function FinalQuotationScreen({navigation, route}: any) {
  const inspectionRequestId = route?.params?.inspectionRequestId;
  const initialInspectionRequest = route?.params?.inspectionRequest as
    | TechnicianInspectionRequest
    | undefined;

  const [inspectionRequest, setInspectionRequest] =
    useState<TechnicianInspectionRequest | null>(initialInspectionRequest || null);
  const [loading, setLoading] = useState(!initialInspectionRequest);
  const [submitting, setSubmitting] = useState(false);
  const [errorMessage, setErrorMessage] = useState('');
  const [form, setForm] = useState<FinalQuotationFormState>(() =>
    buildInitialFormState(),
  );

  const loadInspectionRequest = useCallback(
    async (showLoadingState = false) => {
      if (!inspectionRequestId) {
        setInspectionRequest(null);
        setErrorMessage('No inspection request ID was provided.');
        setLoading(false);
        return;
      }

      try {
        if (showLoadingState) {
          setLoading(true);
        }

        setErrorMessage('');
        const request = await getAssignedInspectionRequestById(inspectionRequestId);

        if (!request) {
          setInspectionRequest(null);
          setErrorMessage(
            'This inspection request was not found in your assigned list.',
          );
          return;
        }

        setInspectionRequest(request);
      } catch (error) {
        setInspectionRequest(null);
        setErrorMessage(getFriendlyErrorMessage(error));
      } finally {
        setLoading(false);
      }
    },
    [inspectionRequestId],
  );

  useFocusEffect(
    useCallback(() => {
      loadInspectionRequest(!inspectionRequest);
    }, [inspectionRequest, loadInspectionRequest]),
  );

  const completed = canCreateFinalQuotation(inspectionRequest?.status);

  const validationMessage = useMemo(() => {
    const requiredFields: Array<[string, string | boolean | undefined]> = [
      ['Monthly electric bill', form.monthly_electric_bill],
      ['Rate per kWh', form.rate_per_kwh],
      ['Days in month', form.days_in_month],
      ['Sun hours', form.sun_hours],
      ['PV safety factor', form.pv_safety_factor],
      ['Battery factor', form.battery_factor],
      ['Battery voltage', form.battery_voltage],
      ['Inverter type', form.inverter_type.trim()],
      ['Panel watts', form.panel_watts],
      ['Panel cost', form.panel_cost],
      ['Inverter cost', form.inverter_cost],
      ['BOS cost', form.bos_cost],
      ['Materials subtotal', form.materials_subtotal],
      ['Labor cost', form.labor_cost],
      ['Project cost', form.project_cost],
    ];

    for (const [label, value] of requiredFields) {
      if (!value) {
        return `${label} is required.`;
      }
    }

    if (form.with_battery) {
      if (!form.battery_model.trim()) {
        return 'Battery model is required when battery is enabled.';
      }

      if (!form.battery_capacity_ah) {
        return 'Battery capacity Ah is required when battery is enabled.';
      }

      if (!form.battery_cost) {
        return 'Battery cost is required when battery is enabled.';
      }
    }

    return '';
  }, [form]);

  const updateField = <K extends keyof FinalQuotationFormState>(
    key: K,
    value: FinalQuotationFormState[K],
  ) => {
    setForm(current => ({
      ...current,
      [key]: value,
    }));
  };

  const handleSubmit = async () => {
    if (!inspectionRequest || !inspectionRequestId) {
      Alert.alert('Request unavailable', 'No inspection request was found.');
      return;
    }

    if (!completed) {
      Alert.alert(
        'Not allowed yet',
        'Final quotations can only be submitted after the inspection is completed.',
      );
      return;
    }

    if (validationMessage) {
      Alert.alert('Please complete the form', validationMessage);
      return;
    }

    if (submitting) {
      return;
    }

    try {
      setSubmitting(true);

      await submitFinalQuotation({
        inspection_request_id: inspectionRequestId,
        monthly_electric_bill: toNumberOrUndefined(
          form.monthly_electric_bill,
        ) as number,
        rate_per_kwh: toNumberOrUndefined(form.rate_per_kwh),
        days_in_month: toNumberOrUndefined(form.days_in_month),
        sun_hours: toNumberOrUndefined(form.sun_hours),
        pv_safety_factor: toNumberOrUndefined(form.pv_safety_factor),
        battery_factor: toNumberOrUndefined(form.battery_factor),
        battery_voltage: toNumberOrUndefined(form.battery_voltage),
        pv_system_type: form.pv_system_type,
        with_battery: form.with_battery,
        inverter_type: form.inverter_type.trim() || undefined,
        battery_model: form.with_battery
          ? form.battery_model.trim() || undefined
          : undefined,
        battery_capacity_ah: form.with_battery
          ? toNumberOrUndefined(form.battery_capacity_ah)
          : undefined,
        panel_watts: toNumberOrUndefined(form.panel_watts),
        panel_cost: toNumberOrUndefined(form.panel_cost),
        inverter_cost: toNumberOrUndefined(form.inverter_cost),
        battery_cost:
          form.with_battery && form.battery_cost
            ? toNumberOrUndefined(form.battery_cost)
            : undefined,
        bos_cost: toNumberOrUndefined(form.bos_cost),
        materials_subtotal: toNumberOrUndefined(form.materials_subtotal),
        labor_cost: toNumberOrUndefined(form.labor_cost),
        project_cost: toNumberOrUndefined(form.project_cost),
        status: form.status,
        remarks: form.remarks.trim() || undefined,
      });

      Alert.alert('Success', 'Final quotation submitted successfully.', [
        {
          text: 'OK',
          onPress: () => navigation.goBack(),
        },
      ]);
    } catch (error) {
      if (error instanceof ApiError) {
        Alert.alert('Submission failed', formatLaravelErrors(error));
      } else {
        Alert.alert(
          'Submission failed',
          'Could not submit the final quotation.',
        );
      }
    } finally {
      setSubmitting(false);
    }
  };

  if (loading) {
    return (
      <View style={styles.centeredContainer}>
        <ActivityIndicator size="large" color="#2563eb" />
        <Text style={styles.loadingText}>Loading completed inspection...</Text>
      </View>
    );
  }

  if (errorMessage || !inspectionRequest) {
    return (
      <View style={styles.centeredContainer}>
        <Text style={styles.errorTitle}>Final quotation unavailable</Text>
        <Text style={styles.errorText}>
          {errorMessage || 'No inspection request was found for this form.'}
        </Text>
        <AppButton
          title="Back"
          onPress={() => navigation.goBack()}
          style={styles.errorButton}
        />
      </View>
    );
  }

  return (
    <SafeAreaView style={styles.safeArea}>
      <ScrollView
        contentContainerStyle={styles.contentContainer}
        keyboardShouldPersistTaps="handled"
        showsVerticalScrollIndicator={false}>
        <View style={styles.heroCard}>
          <Text style={styles.eyebrow}>Technician final quotation</Text>
          <Text style={styles.heroTitle}>
            Inspection request #{inspectionRequest.id}
          </Text>
          <Text style={styles.heroSubtitle}>
            Submit the final quotation based on the completed site inspection for{' '}
            {getCustomerName(inspectionRequest)}.
          </Text>
        </View>

        <FormSection
          title="Inspection summary"
          subtitle="This final quotation is tied directly to the completed inspection request.">
          <View style={styles.summaryRow}>
            <Text style={styles.summaryLabel}>Inspection request ID</Text>
            <Text style={styles.summaryValue}>{inspectionRequest.id}</Text>
          </View>
          <View style={styles.summaryRow}>
            <Text style={styles.summaryLabel}>Customer</Text>
            <Text style={styles.summaryValue}>
              {getCustomerName(inspectionRequest)}
            </Text>
          </View>
          <View style={styles.summaryRow}>
            <Text style={styles.summaryLabel}>Date needed</Text>
            <Text style={styles.summaryValue}>
              {formatDate(inspectionRequest.date_needed)}
            </Text>
          </View>
          <View style={styles.summaryRow}>
            <Text style={styles.summaryLabel}>Current status</Text>
            <Text style={styles.summaryValue}>
              {formatServiceRequestStatus(inspectionRequest.status)}
            </Text>
          </View>
          <View style={styles.summaryDetails}>
            <Text style={styles.summaryLabel}>Details</Text>
            <Text style={styles.summaryDetailsText}>{inspectionRequest.details}</Text>
          </View>
        </FormSection>

        {!completed ? (
          <AppCard style={styles.warningCard}>
            <Text style={styles.warningTitle}>Inspection not completed yet</Text>
            <Text style={styles.warningText}>
              Mark the inspection request as completed before submitting the
              final quotation.
            </Text>
          </AppCard>
        ) : null}

        {validationMessage ? (
          <AppCard style={styles.infoCard}>
            <Text style={styles.infoTitle}>Before you submit</Text>
            <Text style={styles.infoText}>{validationMessage}</Text>
          </AppCard>
        ) : null}

        <FormSection
          title="Basic inputs"
          subtitle="Customer energy usage and production assumptions used by the backend calculations.">
          <AppInput
            label="Inspection request ID"
            editable={false}
            value={`${inspectionRequest.id}`}
            containerStyle={styles.fieldSpacing}
          />
          <AppInput
            label="Monthly electric bill"
            keyboardType="decimal-pad"
            onChangeText={value =>
              updateField('monthly_electric_bill', sanitizeNumericInput(value))
            }
            value={form.monthly_electric_bill}
            containerStyle={styles.fieldSpacing}
          />
          <AppInput
            label="Rate per kWh"
            keyboardType="decimal-pad"
            onChangeText={value =>
              updateField('rate_per_kwh', sanitizeNumericInput(value))
            }
            value={form.rate_per_kwh}
            containerStyle={styles.fieldSpacing}
          />
          <AppInput
            label="Days in month"
            keyboardType="number-pad"
            onChangeText={value =>
              updateField('days_in_month', sanitizeIntegerInput(value))
            }
            value={form.days_in_month}
            containerStyle={styles.fieldSpacing}
          />
          <AppInput
            label="Sun hours"
            keyboardType="decimal-pad"
            onChangeText={value =>
              updateField('sun_hours', sanitizeNumericInput(value))
            }
            value={form.sun_hours}
            containerStyle={styles.fieldSpacing}
          />
          <AppInput
            label="PV safety factor"
            keyboardType="decimal-pad"
            onChangeText={value =>
              updateField('pv_safety_factor', sanitizeNumericInput(value))
            }
            value={form.pv_safety_factor}
            containerStyle={styles.fieldSpacing}
          />
          <AppInput
            label="Battery factor"
            keyboardType="decimal-pad"
            onChangeText={value =>
              updateField('battery_factor', sanitizeNumericInput(value))
            }
            value={form.battery_factor}
            containerStyle={styles.fieldSpacing}
          />
          <AppInput
            label="Battery voltage"
            keyboardType="decimal-pad"
            onChangeText={value =>
              updateField('battery_voltage', sanitizeNumericInput(value))
            }
            value={form.battery_voltage}
          />
        </FormSection>

        <FormSection
          title="System setup"
          subtitle="Select the PV system configuration and the hardware planned after inspection.">
          <Text style={styles.optionLabel}>PV system type</Text>
          <View style={styles.optionRow}>
            {PV_SYSTEM_OPTIONS.map(option => (
              <OptionChip
                key={option}
                label={option}
                selected={form.pv_system_type === option}
                onPress={() => updateField('pv_system_type', option)}
              />
            ))}
          </View>

          <View style={styles.switchRow}>
            <View style={styles.switchTextWrap}>
              <Text style={styles.switchLabel}>With battery</Text>
              <Text style={styles.switchHint}>
                Toggle off if the final proposal does not include battery
                storage.
              </Text>
            </View>
            <Switch
              trackColor={{false: '#cbd5e1', true: '#93c5fd'}}
              thumbColor={form.with_battery ? '#2563eb' : '#f8fafc'}
              value={form.with_battery}
              onValueChange={value => updateField('with_battery', value)}
            />
          </View>

          <AppInput
            label="Inverter type"
            onChangeText={value => updateField('inverter_type', value)}
            value={form.inverter_type}
            containerStyle={styles.fieldSpacing}
          />
          <AppInput
            label="Battery model"
            editable={form.with_battery}
            onChangeText={value => updateField('battery_model', value)}
            value={form.battery_model}
            containerStyle={styles.fieldSpacing}
          />
          <AppInput
            label="Battery capacity Ah"
            editable={form.with_battery}
            keyboardType="decimal-pad"
            onChangeText={value =>
              updateField('battery_capacity_ah', sanitizeNumericInput(value))
            }
            value={form.battery_capacity_ah}
            containerStyle={styles.fieldSpacing}
          />
          <AppInput
            label="Panel watts"
            keyboardType="decimal-pad"
            onChangeText={value =>
              updateField('panel_watts', sanitizeNumericInput(value))
            }
            value={form.panel_watts}
          />
        </FormSection>

        <FormSection
          title="Costing"
          subtitle="Enter the final pricing inputs that the technician is submitting to the backend.">
          <AppInput
            label="Panel cost"
            keyboardType="decimal-pad"
            onChangeText={value =>
              updateField('panel_cost', sanitizeNumericInput(value))
            }
            value={form.panel_cost}
            containerStyle={styles.fieldSpacing}
          />
          <AppInput
            label="Inverter cost"
            keyboardType="decimal-pad"
            onChangeText={value =>
              updateField('inverter_cost', sanitizeNumericInput(value))
            }
            value={form.inverter_cost}
            containerStyle={styles.fieldSpacing}
          />
          <AppInput
            label="Battery cost"
            editable={form.with_battery}
            keyboardType="decimal-pad"
            onChangeText={value =>
              updateField('battery_cost', sanitizeNumericInput(value))
            }
            value={form.battery_cost}
            containerStyle={styles.fieldSpacing}
          />
          <AppInput
            label="BOS cost"
            keyboardType="decimal-pad"
            onChangeText={value =>
              updateField('bos_cost', sanitizeNumericInput(value))
            }
            value={form.bos_cost}
            containerStyle={styles.fieldSpacing}
          />
          <AppInput
            label="Materials subtotal"
            keyboardType="decimal-pad"
            onChangeText={value =>
              updateField('materials_subtotal', sanitizeNumericInput(value))
            }
            value={form.materials_subtotal}
            containerStyle={styles.fieldSpacing}
          />
          <AppInput
            label="Labor cost"
            keyboardType="decimal-pad"
            onChangeText={value =>
              updateField('labor_cost', sanitizeNumericInput(value))
            }
            value={form.labor_cost}
            containerStyle={styles.fieldSpacing}
          />
          <AppInput
            label="Project cost"
            keyboardType="decimal-pad"
            onChangeText={value =>
              updateField('project_cost', sanitizeNumericInput(value))
            }
            value={form.project_cost}
          />
        </FormSection>

        <FormSection
          title="Notes"
          subtitle="Set the quotation status and add any final technician remarks for the customer.">
          <Text style={styles.optionLabel}>Quotation status</Text>
          <View style={styles.optionRow}>
            {STATUS_OPTIONS.map(option => (
              <OptionChip
                key={option}
                label={option}
                selected={form.status === option}
                onPress={() => updateField('status', option)}
              />
            ))}
          </View>

          <AppInput
            label="Remarks"
            multiline={true}
            numberOfLines={5}
            onChangeText={value => updateField('remarks', value)}
            style={styles.textArea}
            value={form.remarks}
          />
        </FormSection>

        <AppButton
          title={
            submitting
              ? 'Submitting final quotation...'
              : 'Submit Final Quotation'
          }
          disabled={submitting || !completed}
          onPress={handleSubmit}
        />
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
  centeredContainer: {
    alignItems: 'center',
    backgroundColor: '#f5f7fb',
    flex: 1,
    justifyContent: 'center',
    padding: 24,
  },
  loadingText: {
    color: '#475569',
    fontSize: 14,
    marginTop: 12,
  },
  errorTitle: {
    color: '#0f172a',
    fontSize: 22,
    fontWeight: '800',
    marginBottom: 8,
    textAlign: 'center',
  },
  errorText: {
    color: '#b91c1c',
    fontSize: 14,
    lineHeight: 20,
    textAlign: 'center',
  },
  errorButton: {
    marginTop: 16,
    width: '100%',
  },
  heroCard: {
    backgroundColor: '#dcfce7',
    borderRadius: 28,
    marginBottom: 18,
    padding: 22,
  },
  eyebrow: {
    color: '#15803d',
    fontSize: 12,
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
    fontSize: 14,
    lineHeight: 21,
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
  summaryRow: {
    borderTopColor: '#e2e8f0',
    borderTopWidth: 1,
    paddingVertical: 12,
  },
  summaryLabel: {
    color: '#64748b',
    fontSize: 12,
    fontWeight: '700',
    marginBottom: 4,
    textTransform: 'uppercase',
  },
  summaryValue: {
    color: '#0f172a',
    fontSize: 15,
    fontWeight: '600',
    lineHeight: 22,
  },
  summaryDetails: {
    borderTopColor: '#e2e8f0',
    borderTopWidth: 1,
    paddingTop: 12,
  },
  summaryDetailsText: {
    color: '#0f172a',
    fontSize: 15,
    lineHeight: 23,
  },
  warningCard: {
    backgroundColor: '#fff7ed',
    borderColor: '#fdba74',
    borderWidth: 1,
    marginBottom: 18,
  },
  warningTitle: {
    color: '#9a3412',
    fontSize: 18,
    fontWeight: '700',
    marginBottom: 6,
  },
  warningText: {
    color: '#9a3412',
    fontSize: 14,
    lineHeight: 20,
  },
  infoCard: {
    backgroundColor: '#eff6ff',
    borderColor: '#bfdbfe',
    borderWidth: 1,
    marginBottom: 18,
  },
  infoTitle: {
    color: '#1d4ed8',
    fontSize: 18,
    fontWeight: '700',
    marginBottom: 6,
  },
  infoText: {
    color: '#1e3a8a',
    fontSize: 14,
    lineHeight: 20,
  },
  fieldSpacing: {
    marginBottom: 14,
  },
  optionLabel: {
    color: '#374151',
    fontSize: 14,
    fontWeight: '600',
    marginBottom: 10,
  },
  optionRow: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 10,
    marginBottom: 16,
  },
  optionChip: {
    backgroundColor: '#eff6ff',
    borderColor: '#bfdbfe',
    borderRadius: 14,
    borderWidth: 1,
    paddingHorizontal: 14,
    paddingVertical: 11,
  },
  optionChipSelected: {
    backgroundColor: '#2563eb',
    borderColor: '#2563eb',
  },
  optionChipPressed: {
    opacity: 0.88,
  },
  optionChipText: {
    color: '#1d4ed8',
    fontSize: 14,
    fontWeight: '700',
  },
  optionChipTextSelected: {
    color: '#ffffff',
  },
  switchRow: {
    alignItems: 'center',
    backgroundColor: '#f8fafc',
    borderColor: '#e2e8f0',
    borderRadius: 16,
    borderWidth: 1,
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 16,
    padding: 16,
  },
  switchTextWrap: {
    flex: 1,
    paddingRight: 16,
  },
  switchLabel: {
    color: '#0f172a',
    fontSize: 16,
    fontWeight: '700',
    marginBottom: 4,
  },
  switchHint: {
    color: '#64748b',
    fontSize: 13,
    lineHeight: 19,
  },
  textArea: {
    minHeight: 120,
    textAlignVertical: 'top',
  },
});
