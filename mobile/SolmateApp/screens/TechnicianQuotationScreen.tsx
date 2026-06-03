import React, { useEffect, useState } from 'react';
import {
  ActivityIndicator,
  Alert,
  KeyboardAvoidingView,
  Platform,
  SafeAreaView,
  ScrollView,
  StyleSheet,
  Text,
  TextInput,
  View,
} from 'react-native';

import { AppButton, AppCard } from '../components';
import { ApiError, apiGet, apiPut } from '../src/services/api';

type QuotationDetail = {
  id: number;
  quotation_type?: string | null;
  status?: string | null;
  monthly_electric_bill?: number | null;
  monthly_kwh?: number | null;
  system_kw?: number | null;
  panel_watts?: number | null;
  inverter_type?: string | null;
  battery_model?: string | null;
  battery_capacity_ah?: number | null;
  panel_cost?: number | null;
  inverter_cost?: number | null;
  battery_cost?: number | null;
  bos_cost?: number | null;
  materials_subtotal?: number | null;
  labor_cost?: number | null;
  project_cost?: number | null;
  remarks?: string | null;
  created_at?: string | null;
};

type UpdateQuotationResponse = {
  message: string;
  data: QuotationDetail;
};

function sanitizeNumericInput(value: string) {
  const cleanedValue = value.replace(/[^0-9.]/g, '');
  const parts = cleanedValue.split('.');

  if (parts.length <= 1) {
    return cleanedValue;
  }

  return `${parts[0]}.${parts.slice(1).join('')}`;
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

function formatValue(value?: string | number | null) {
  if (value === null || value === undefined || value === '') {
    return 'N/A';
  }

  return String(value);
}

function FormField({
  label,
  value,
  onChangeText,
  placeholder,
  keyboardType = 'default',
  helpText,
}: {
  label: string;
  value: string;
  onChangeText: (value: string) => void;
  placeholder: string;
  keyboardType?: 'default' | 'numeric' | 'decimal-pad';
  helpText?: string;
}) {
  return (
    <View style={styles.fieldGroup}>
      <Text style={styles.fieldLabel}>{label}</Text>
      <TextInput
        value={value}
        onChangeText={onChangeText}
        placeholder={placeholder}
        placeholderTextColor="#7F92A3"
        keyboardType={keyboardType}
        style={styles.input}
      />
      {helpText ? <Text style={styles.helpText}>{helpText}</Text> : null}
    </View>
  );
}

export default function TechnicianQuotationScreen({ route }: any) {
  const quotationId = route?.params?.quotationId;

  const [quotation, setQuotation] = useState<QuotationDetail | null>(null);
  const [loading, setLoading] = useState(true);
  const [submitting, setSubmitting] = useState(false);
  const [errorMessage, setErrorMessage] = useState('');

  const [panelWatts, setPanelWatts] = useState('');
  const [inverterType, setInverterType] = useState('');
  const [batteryModel, setBatteryModel] = useState('');
  const [batteryCapacityAh, setBatteryCapacityAh] = useState('');
  const [panelCost, setPanelCost] = useState('');
  const [inverterCost, setInverterCost] = useState('');
  const [batteryCost, setBatteryCost] = useState('');
  const [bosCost, setBosCost] = useState('');
  const [materialsSubtotal, setMaterialsSubtotal] = useState('');
  const [laborCost, setLaborCost] = useState('');
  const [projectCost, setProjectCost] = useState('');
  const [remarks, setRemarks] = useState('');

  const hydrateForm = (data: QuotationDetail) => {
    setPanelWatts(data.panel_watts ? String(data.panel_watts) : '');
    setInverterType(data.inverter_type || '');
    setBatteryModel(data.battery_model || '');
    setBatteryCapacityAh(
      data.battery_capacity_ah ? String(data.battery_capacity_ah) : '',
    );
    setPanelCost(data.panel_cost ? String(data.panel_cost) : '');
    setInverterCost(data.inverter_cost ? String(data.inverter_cost) : '');
    setBatteryCost(data.battery_cost ? String(data.battery_cost) : '');
    setBosCost(data.bos_cost ? String(data.bos_cost) : '');
    setMaterialsSubtotal(
      data.materials_subtotal ? String(data.materials_subtotal) : '',
    );
    setLaborCost(data.labor_cost ? String(data.labor_cost) : '');
    setProjectCost(data.project_cost ? String(data.project_cost) : '');
    setRemarks(data.remarks || '');
  };

  useEffect(() => {
    const fetchQuotation = async () => {
      if (!quotationId) {
        setErrorMessage('No quotation ID was provided for this screen.');
        setLoading(false);
        return;
      }

      try {
        setLoading(true);
        setErrorMessage('');

        const response = await apiGet<QuotationDetail>(
          `/quotations/${quotationId}`,
        );
        setQuotation(response);
        hydrateForm(response);
      } catch (error) {
        if (error instanceof ApiError) {
          setErrorMessage(error.message);
        } else {
          setErrorMessage('Could not load the quotation.');
        }
      } finally {
        setLoading(false);
      }
    };

    fetchQuotation();
  }, [quotationId]);

  const validateForm = () => {
    const parsedPanelWatts = toNumberOrUndefined(panelWatts);

    if (parsedPanelWatts === undefined) {
      return 'Panel wattage is required before finalizing the quotation.';
    }

    if (parsedPanelWatts < 1) {
      return 'Panel wattage must be at least 1.';
    }

    return '';
  };

  const handleSubmit = async () => {
    const validationMessage = validateForm();

    if (validationMessage) {
      Alert.alert('Please complete the form', validationMessage);
      return;
    }

    // The technician finalizes the quotation by forcing quotation_type to final
    // while updating the editable technical and cost fields.
    const payload = {
      quotation_type: 'final',
      panel_watts: toNumberOrUndefined(panelWatts),
      inverter_type: inverterType.trim() || undefined,
      battery_model: batteryModel.trim() || undefined,
      battery_capacity_ah: toNumberOrUndefined(batteryCapacityAh),
      panel_cost: toNumberOrUndefined(panelCost),
      inverter_cost: toNumberOrUndefined(inverterCost),
      battery_cost: toNumberOrUndefined(batteryCost),
      bos_cost: toNumberOrUndefined(bosCost),
      materials_subtotal: toNumberOrUndefined(materialsSubtotal),
      labor_cost: toNumberOrUndefined(laborCost),
      project_cost: toNumberOrUndefined(projectCost),
      remarks: remarks.trim() || undefined,
    };

    try {
      setSubmitting(true);
      const response = await apiPut<UpdateQuotationResponse>(
        `/quotations/${quotationId}`,
        payload,
      );
      const updatedQuotation = response?.data ?? response;

      setQuotation(updatedQuotation);
      hydrateForm(updatedQuotation);

      Alert.alert('Success', 'Inspection-based quotation submitted successfully.');
    } catch (error) {
      if (error instanceof ApiError) {
        Alert.alert('Submit failed', error.message);
      } else {
        Alert.alert('Submit failed', 'Could not submit the inspection-based quotation.');
      }
    } finally {
      setSubmitting(false);
    }
  };

  if (loading) {
    return (
      <View style={styles.centeredContainer}>
        <ActivityIndicator size="large" color="#2563eb" />
        <Text style={styles.loadingText}>Loading quotation...</Text>
      </View>
    );
  }

  if (errorMessage || !quotation) {
    return (
      <View style={styles.centeredContainer}>
        <Text style={styles.errorTitle}>Technician quotation screen</Text>
        <Text style={styles.errorText}>
          {errorMessage || 'No quotation data was found.'}
        </Text>
      </View>
    );
  }

  return (
    <SafeAreaView style={styles.safe}>
      <KeyboardAvoidingView
        behavior={Platform.OS === 'ios' ? 'padding' : undefined}
        keyboardVerticalOffset={Platform.OS === 'ios' ? 12 : 0}
        style={styles.flex}
      >
        <ScrollView
          contentContainerStyle={styles.container}
          keyboardDismissMode={
            Platform.OS === 'ios' ? 'interactive' : 'on-drag'
          }
          keyboardShouldPersistTaps="handled"
          showsVerticalScrollIndicator={false}
        >
          <View style={styles.heroCard}>
            <Text style={styles.eyebrow}>Technician review</Text>
            <Text style={styles.title}>Finalize quotation #{quotation.id}</Text>
            <Text style={styles.subtitle}>
              Review the customer's pre-inspection estimate, then fill in the
              technical and cost fields needed for the inspection-based quotation.
            </Text>
          </View>

          <AppCard style={styles.sectionCard}>
            <Text style={styles.sectionTitle}>Initial customer request</Text>
            <Text style={styles.sectionSubtitle}>
              These values came from the original quotation submitted by the
              customer.
            </Text>

            <View style={styles.readOnlyRow}>
              <Text style={styles.readOnlyLabel}>Quotation type</Text>
              <Text style={styles.readOnlyValue}>
                {formatValue(quotation.quotation_type)}
              </Text>
            </View>
            <View style={styles.readOnlyRow}>
              <Text style={styles.readOnlyLabel}>Status</Text>
              <Text style={styles.readOnlyValue}>
                {formatValue(quotation.status)}
              </Text>
            </View>
            <View style={styles.readOnlyRow}>
              <Text style={styles.readOnlyLabel}>Monthly electric bill</Text>
              <Text style={styles.readOnlyValue}>
                {formatValue(quotation.monthly_electric_bill)}
              </Text>
            </View>
            <View style={styles.readOnlyRow}>
              <Text style={styles.readOnlyLabel}>Monthly kWh</Text>
              <Text style={styles.readOnlyValue}>
                {formatValue(quotation.monthly_kwh)}
              </Text>
            </View>
            <View style={styles.readOnlyRow}>
              <Text style={styles.readOnlyLabel}>System kW</Text>
              <Text style={styles.readOnlyValue}>
                {formatValue(quotation.system_kw)}
              </Text>
            </View>
            <View style={styles.readOnlyRow}>
              <Text style={styles.readOnlyLabel}>Customer remarks</Text>
              <Text style={styles.readOnlyValue}>
                {formatValue(quotation.remarks)}
              </Text>
            </View>
          </AppCard>

          <AppCard style={styles.sectionCard}>
            <Text style={styles.sectionTitle}>Technical fields</Text>
            <Text style={styles.sectionSubtitle}>
              Update the system details that convert the quotation into an inspection-based
              version.
            </Text>

            <FormField
              label="Panel wattage"
              value={panelWatts}
              onChangeText={value => setPanelWatts(sanitizeNumericInput(value))}
              placeholder="Example: 610"
              keyboardType="decimal-pad"
              helpText="Required for finalization."
            />

            <FormField
              label="Inverter type"
              value={inverterType}
              onChangeText={setInverterType}
              placeholder="Example: Hybrid inverter"
            />

            <FormField
              label="Battery model"
              value={batteryModel}
              onChangeText={setBatteryModel}
              placeholder="Example: LiFePO4 51.2V"
            />

            <FormField
              label="Battery capacity (Ah)"
              value={batteryCapacityAh}
              onChangeText={value =>
                setBatteryCapacityAh(sanitizeNumericInput(value))
              }
              placeholder="Example: 200"
              keyboardType="decimal-pad"
            />
          </AppCard>

          <AppCard style={styles.sectionCard}>
            <Text style={styles.sectionTitle}>Costing and remarks</Text>
            <Text style={styles.sectionSubtitle}>
              Fill in the quotation costs and add any final technician notes.
            </Text>

            <FormField
              label="Panel cost"
              value={panelCost}
              onChangeText={value => setPanelCost(sanitizeNumericInput(value))}
              placeholder="Example: 120000"
              keyboardType="decimal-pad"
            />

            <FormField
              label="Inverter cost"
              value={inverterCost}
              onChangeText={value =>
                setInverterCost(sanitizeNumericInput(value))
              }
              placeholder="Example: 45000"
              keyboardType="decimal-pad"
            />

            <FormField
              label="Battery cost"
              value={batteryCost}
              onChangeText={value =>
                setBatteryCost(sanitizeNumericInput(value))
              }
              placeholder="Example: 80000"
              keyboardType="decimal-pad"
            />

            <FormField
              label="BOS cost"
              value={bosCost}
              onChangeText={value => setBosCost(sanitizeNumericInput(value))}
              placeholder="Example: 15000"
              keyboardType="decimal-pad"
            />

            <FormField
              label="Materials subtotal"
              value={materialsSubtotal}
              onChangeText={value =>
                setMaterialsSubtotal(sanitizeNumericInput(value))
              }
              placeholder="Example: 260000"
              keyboardType="decimal-pad"
            />

            <FormField
              label="Labor cost"
              value={laborCost}
              onChangeText={value => setLaborCost(sanitizeNumericInput(value))}
              placeholder="Example: 30000"
              keyboardType="decimal-pad"
            />

            <FormField
              label="Project cost"
              value={projectCost}
              onChangeText={value =>
                setProjectCost(sanitizeNumericInput(value))
              }
              placeholder="Example: 290000"
              keyboardType="decimal-pad"
              helpText="This is often the final total shown to the customer."
            />

            <View style={styles.fieldGroup}>
              <Text style={styles.fieldLabel}>Technician remarks</Text>
              <TextInput
                value={remarks}
                onChangeText={setRemarks}
                placeholder="Add inspection-based quotation notes"
                placeholderTextColor="#7F92A3"
                multiline={true}
                numberOfLines={4}
                style={[styles.input, styles.textArea]}
              />
            </View>
          </AppCard>

          <View style={styles.infoCard}>
            <Text style={styles.infoTitle}>Submission behavior</Text>
            <Text style={styles.infoText}>
              quotation_type will be sent as final.
            </Text>
            <Text style={styles.infoText}>
              Only the technician-editable fields above will be updated.
            </Text>
            <Text style={styles.infoText}>
              This quotation is based on the technician's actual inspection and assessment.
            </Text>
          </View>

          <AppButton
            title={
              submitting
                ? 'Submitting inspection-based quotation...'
                : 'Submit inspection-based quotation'
            }
            onPress={handleSubmit}
            disabled={submitting}
          />
        </ScrollView>
      </KeyboardAvoidingView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safe: {
    backgroundColor: '#C8D8F0',
    flex: 1,
  },
  flex: {
    flex: 1,
  },
  container: {
    backgroundColor: '#C8D8F0',
    padding: 20,
    paddingBottom: 32,
  },
  centeredContainer: {
    alignItems: 'center',
    backgroundColor: '#C8D8F0',
    flex: 1,
    justifyContent: 'center',
    padding: 24,
  },
  loadingText: {
    color: '#6B7A99',
    fontSize: 14,
    marginTop: 12,
  },
  errorTitle: {
    color: '#0f172a',
    fontSize: 20,
    fontWeight: '700',
    marginBottom: 8,
    textAlign: 'center',
  },
  errorText: {
    color: '#dc2626',
    fontSize: 14,
    lineHeight: 20,
    textAlign: 'center',
  },
  heroCard: {
    backgroundColor: '#eaf2ff',
    borderRadius: 24,
    marginBottom: 16,
    padding: 22,
  },
  eyebrow: {
    color: '#2563eb',
    fontSize: 12,
    fontWeight: '700',
    letterSpacing: 0.5,
    marginBottom: 8,
    textTransform: 'uppercase',
  },
  title: {
    color: '#0f172a',
    fontSize: 26,
    fontWeight: '800',
    marginBottom: 10,
  },
  subtitle: {
    color: '#6B7A99',
    fontSize: 14,
    lineHeight: 21,
  },
  sectionCard: {
    marginBottom: 16,
  },
  sectionTitle: {
    color: '#0f172a',
    fontSize: 20,
    fontWeight: '700',
    marginBottom: 6,
  },
  sectionSubtitle: {
    color: '#6B7A99',
    fontSize: 14,
    lineHeight: 20,
    marginBottom: 16,
  },
  readOnlyRow: {
    borderTopColor: '#D4E0F2',
    borderTopWidth: 1,
    paddingVertical: 12,
  },
  readOnlyLabel: {
    color: '#6B7A99',
    fontSize: 12,
    fontWeight: '700',
    marginBottom: 4,
    textTransform: 'uppercase',
  },
  readOnlyValue: {
    color: '#0f172a',
    fontSize: 15,
    fontWeight: '600',
    lineHeight: 22,
  },
  fieldGroup: {
    marginBottom: 16,
  },
  fieldLabel: {
    color: '#0f172a',
    fontSize: 15,
    fontWeight: '700',
    marginBottom: 8,
  },
  input: {
    backgroundColor: '#EEF4FC',
    borderColor: '#D4E0F2',
    borderRadius: 14,
    borderWidth: 1,
    color: '#0f172a',
    fontSize: 15,
    paddingHorizontal: 14,
    paddingVertical: 13,
  },
  textArea: {
    minHeight: 110,
    textAlignVertical: 'top',
  },
  helpText: {
    color: '#6B7A99',
    fontSize: 13,
    lineHeight: 18,
    marginTop: 6,
  },
  infoCard: {
    backgroundColor: '#EAF9FD',
    borderRadius: 18,
    marginBottom: 14,
    padding: 16,
  },
  infoTitle: {
    color: '#1d4ed8',
    fontSize: 15,
    fontWeight: '700',
    marginBottom: 6,
  },
  infoText: {
    color: '#334155',
    fontSize: 14,
    lineHeight: 20,
  },
});
