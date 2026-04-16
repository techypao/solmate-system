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
import {
  FinalQuotationComputationDefaults,
  FinalQuotationOption,
  FinalQuotationOptions,
  PricingItemSummary,
  PvSystemType,
  QuotationStatus,
  getFinalQuotationOptions,
  getPricingCatalog,
  replaceQuotationLineItems,
  submitFinalQuotation,
} from '../src/services/quotationApi';
import {formatQuotationCurrency} from '../src/utils/currency';
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

type FinalQuotationFormState = {
  monthly_electric_bill: string;
  rate_per_kwh: string;
  days_in_month: string;
  sun_hours: string;
  pv_safety_factor: string;
  battery_factor: string;
  battery_voltage: string;
  pv_system_type: PvSystemType | '';
  with_battery: boolean;
  inverter_type: string;
  battery_model: string;
  battery_capacity_ah: string;
  panel_watts: string;
  status: QuotationStatus;
  remarks: string;
};

type CatalogQuantityState = Record<number, string>;

type SelectedCatalogLineItem = {
  pricing_item_id: number;
  description: string;
  category: string;
  qty: number;
  unit: string;
  unit_amount: number;
  total_amount: number;
  pricing_item: PricingItemSummary;
};

type ComputedTotals = {
  panelCost: number;
  inverterCost: number;
  batteryCost: number;
  bosCost: number;
  materialsSubtotal: number;
  laborCost: number;
  projectCost: number;
  roiYears: number | null;
};

type SuggestedQuantityState = Record<number, number>;

type SizingPreview = {
  monthlyKwh: number;
  dailyKwh: number;
  requiredSystemKw: number;
  suggestedSystemKw: number;
  panelQuantityBaseline: number;
  requiredBatteryKwh: number;
  requiredBatteryAh: number;
};

const STATUS_OPTIONS: QuotationStatus[] = [
  'pending',
  'approved',
  'rejected',
  'completed',
];

const CATEGORY_ORDER = [
  'panel',
  'inverter',
  'battery',
  'protection',
  'mounting',
  'wiring',
  'grounding',
  'misc',
];

const WIZARD_STEPS = [
  {
    number: 1,
    label: 'Energy Inputs',
    subtitle: 'Enter the customer bill, system type, presets, and any optional overrides.',
  },
  {
    number: 2,
    label: 'Calculated Outputs',
    subtitle: 'Review the computed sizing before building the detailed quotation.',
  },
  {
    number: 3,
    label: 'Cost Breakdown',
    subtitle: 'Pick catalog items, adjust quantities, and review computed totals.',
  },
  {
    number: 4,
    label: 'Review & Submit',
    subtitle: 'Check the full quotation summary, remarks, and submit the final quotation.',
  },
] as const;

const REQUEST_TIMEOUT_MS = 10000;

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

function formatCategoryLabel(category: string) {
  return category
    .split(/[_-]/g)
    .map(part => part.charAt(0).toUpperCase() + part.slice(1))
    .join(' ');
}

function roundToTwo(value: number) {
  return Number(value.toFixed(2));
}

function getPricingItemSearchText(item: PricingItemSummary) {
  return [
    item.name,
    item.brand,
    item.model,
    item.specification,
  ]
    .filter(Boolean)
    .join(' ');
}

function extractNumberByPattern(
  pattern: RegExp,
  value: string,
): number | null {
  const match = value.match(pattern);

  if (!match?.[1]) {
    return null;
  }

  const parsedValue = Number(match[1]);
  return Number.isFinite(parsedValue) ? parsedValue : null;
}

function extractPanelWatts(item: PricingItemSummary): number | null {
  return extractNumberByPattern(
    /(\d+(?:\.\d+)?)\s*w\b/i,
    getPricingItemSearchText(item),
  );
}

function extractBatteryAh(item: PricingItemSummary): number | null {
  return extractNumberByPattern(
    /(\d+(?:\.\d+)?)\s*ah\b/i,
    getPricingItemSearchText(item),
  );
}

function extractBatteryVoltage(item: PricingItemSummary): number | null {
  return extractNumberByPattern(
    /(\d+(?:\.\d+)?)\s*v\b/i,
    getPricingItemSearchText(item),
  );
}

function extractInverterKw(item: PricingItemSummary): number | null {
  return extractNumberByPattern(
    /(\d+(?:\.\d+)?)\s*kw\b/i,
    getPricingItemSearchText(item),
  );
}

function buildSizingPreview(
  form: FinalQuotationFormState,
  defaults: FinalQuotationComputationDefaults,
): SizingPreview | null {
  const monthlyElectricBill = toNumberOrUndefined(form.monthly_electric_bill);

  if (!monthlyElectricBill || monthlyElectricBill <= 0) {
    return null;
  }

  const ratePerKwh = toNumberOrUndefined(form.rate_per_kwh) ?? defaults.rate_per_kwh;
  const daysInMonth =
    toNumberOrUndefined(form.days_in_month) ?? defaults.days_in_month;
  const sunHours = toNumberOrUndefined(form.sun_hours) ?? defaults.sun_hours;
  const pvSafetyFactor =
    toNumberOrUndefined(form.pv_safety_factor) ?? defaults.pv_safety_factor;
  const batteryFactor =
    toNumberOrUndefined(form.battery_factor) ?? defaults.battery_factor;
  const batteryVoltage =
    toNumberOrUndefined(form.battery_voltage) ?? defaults.battery_voltage;
  const panelWatts =
    toNumberOrUndefined(form.panel_watts) ?? defaults.default_panel_watts;
  const withBattery = form.with_battery && form.pv_system_type !== 'on-grid';

  const monthlyKwh = ratePerKwh > 0 ? monthlyElectricBill / ratePerKwh : 0;
  const dailyKwh = daysInMonth > 0 ? monthlyKwh / daysInMonth : 0;
  const pvKwRaw = sunHours > 0 ? dailyKwh / sunHours : 0;
  const pvKwSafe = pvKwRaw * pvSafetyFactor;
  const panelQuantityBaseline =
    panelWatts > 0 ? Math.ceil((pvKwSafe * 1000) / panelWatts) : 0;
  const systemKw = (panelQuantityBaseline * panelWatts) / 1000;
  const batteryRequiredKwh = withBattery ? dailyKwh * batteryFactor : 0;
  const batteryRequiredAh =
    batteryVoltage > 0 ? (batteryRequiredKwh * 1000) / batteryVoltage : 0;

  return {
    monthlyKwh: roundToTwo(monthlyKwh),
    dailyKwh: roundToTwo(dailyKwh),
    requiredSystemKw: roundToTwo(pvKwSafe),
    suggestedSystemKw: roundToTwo(systemKw),
    panelQuantityBaseline,
    requiredBatteryKwh: roundToTwo(batteryRequiredKwh),
    requiredBatteryAh: roundToTwo(batteryRequiredAh),
  };
}

function buildInitialFormState(): FinalQuotationFormState {
  return {
    monthly_electric_bill: '',
    rate_per_kwh: '',
    days_in_month: '',
    sun_hours: '',
    pv_safety_factor: '',
    battery_factor: '',
    battery_voltage: '',
    pv_system_type: '',
    with_battery: true,
    inverter_type: '',
    battery_model: '',
    battery_capacity_ah: '',
    panel_watts: '',
    status: 'pending',
    remarks: '',
  };
}

function withTimeout<T>(promise: Promise<T>, label: string, timeoutMs = REQUEST_TIMEOUT_MS) {
  return Promise.race<T>([
    promise,
    new Promise<T>((_, reject) => {
      setTimeout(() => {
        reject(new Error(`${label} request timed out.`));
      }, timeoutMs);
    }),
  ]);
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

export default function FinalQuotationScreen({navigation, route}: any) {
  const inspectionRequestId = route?.params?.inspectionRequestId;
  const initialInspectionRequest = route?.params?.inspectionRequest as
    | TechnicianInspectionRequest
    | undefined;

  const [inspectionRequest, setInspectionRequest] =
    useState<TechnicianInspectionRequest | null>(initialInspectionRequest || null);
  const [loading, setLoading] = useState(!initialInspectionRequest);
  const [optionsLoading, setOptionsLoading] = useState(true);
  const [catalogLoading, setCatalogLoading] = useState(true);
  const [submitting, setSubmitting] = useState(false);
  const [errorMessage, setErrorMessage] = useState('');
  const [optionsError, setOptionsError] = useState('');
  const [catalogError, setCatalogError] = useState('');
  const [submitError, setSubmitError] = useState('');
  const [finalQuotationOptions, setFinalQuotationOptions] =
    useState<FinalQuotationOptions | null>(null);
  const [pricingCatalog, setPricingCatalog] = useState<PricingItemSummary[]>([]);
  const [catalogQuantities, setCatalogQuantities] = useState<CatalogQuantityState>(
    {},
  );
  const [form, setForm] = useState<FinalQuotationFormState>(() =>
    buildInitialFormState(),
  );
  const [currentStep, setCurrentStep] = useState(1);

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
        if (initialInspectionRequest && !showLoadingState) {
          setInspectionRequest(initialInspectionRequest);
          return;
        }

        const request = await withTimeout(
          getAssignedInspectionRequestById(inspectionRequestId),
          'Assigned inspection request',
        );

        if (!request) {
          setInspectionRequest(null);
          setErrorMessage(
            'This inspection request was not found in your assigned list.',
          );
          return;
        }

        setInspectionRequest(request);
      } catch (error) {
        console.warn('FinalQuotationScreen: loadInspectionRequest failed', error);
        setInspectionRequest(null);
        setErrorMessage(getFriendlyErrorMessage(error));
      } finally {
        setLoading(false);
      }
    },
    [initialInspectionRequest, inspectionRequestId],
  );

  const loadOptions = useCallback(
    async (showLoadingState = false) => {
      try {
        if (showLoadingState) {
          setOptionsLoading(true);
        }

        setOptionsError('');
        const options = await withTimeout(
          getFinalQuotationOptions(),
          'Final quotation options',
        );
        setFinalQuotationOptions(options);
        setForm(current => {
          const availablePvSystemTypes = options.system_types.map(
            option => option.value,
          );
          const nextPvSystemType = availablePvSystemTypes.includes(
            current.pv_system_type,
          )
            ? current.pv_system_type
            : ((availablePvSystemTypes[0] ?? '') as PvSystemType | '');

          return {
            ...current,
            pv_system_type: nextPvSystemType,
          };
        });
      } catch (error) {
        console.warn('FinalQuotationScreen: loadOptions failed', error);
        if (error instanceof ApiError) {
          setOptionsError(error.message);
        } else {
          setOptionsError('Could not load final quotation form options.');
        }
      } finally {
        setOptionsLoading(false);
      }
    },
    [],
  );

  const loadPricingCatalog = useCallback(
    async (showLoadingState = false) => {
      try {
        if (showLoadingState) {
          setCatalogLoading(true);
        }

        setCatalogError('');
        const items = await withTimeout(
          getPricingCatalog(),
          'Pricing catalog',
        );
        setPricingCatalog(items);
      } catch (error) {
        console.warn('FinalQuotationScreen: loadPricingCatalog failed', error);
        if (error instanceof ApiError) {
          setCatalogError(error.message);
        } else {
          setCatalogError('Could not load the pricing catalog.');
        }
      } finally {
        setCatalogLoading(false);
      }
    },
    [],
  );

  useFocusEffect(
    useCallback(() => {
      if (!inspectionRequest) {
        loadInspectionRequest(true);
      }
      if (!finalQuotationOptions) {
        loadOptions(true);
      }
      if (pricingCatalog.length === 0) {
        loadPricingCatalog(true);
      }
    }, [
      finalQuotationOptions,
      inspectionRequest,
      loadInspectionRequest,
      loadOptions,
      loadPricingCatalog,
      pricingCatalog.length,
    ]),
  );

  const completed = canCreateFinalQuotation(inspectionRequest?.status);
  const computationDefaults = useMemo(
    () =>
      finalQuotationOptions?.computation_defaults ?? {
        rate_per_kwh: 14,
        days_in_month: 30,
        sun_hours: 4.5,
        pv_safety_factor: 1.8,
        battery_factor: 1,
        battery_voltage: 51.2,
        default_panel_watts: 610,
      },
    [finalQuotationOptions?.computation_defaults],
  );
  const supportsBatteryFlow =
    form.with_battery && form.pv_system_type !== 'on-grid';
  const sizingPreview = useMemo(
    () => buildSizingPreview(form, computationDefaults),
    [computationDefaults, form],
  );

  const groupedPricingCatalog = useMemo(() => {
    return CATEGORY_ORDER.map(category => ({
      category,
      items: pricingCatalog.filter(item => {
        if (item.category !== category) {
          return false;
        }

        if (category === 'battery' && !supportsBatteryFlow) {
          return false;
        }

        return true;
      }),
    })).filter(group => group.items.length > 0);
  }, [pricingCatalog, supportsBatteryFlow]);

  const suggestedQuantities = useMemo<SuggestedQuantityState>(() => {
    if (!sizingPreview) {
      return {};
    }

    const suggestions: SuggestedQuantityState = {};

    const panelItems = pricingCatalog
      .filter(item => item.category === 'panel')
      .map(item => ({
        item,
        watts: extractPanelWatts(item),
      }))
      .filter(
        (entry): entry is {item: PricingItemSummary; watts: number} =>
          entry.watts !== null && entry.watts > 0,
      );

    if (panelItems.length > 0 && sizingPreview.requiredSystemKw > 0) {
      const targetPanelWatts =
        toNumberOrUndefined(form.panel_watts) ?? computationDefaults.default_panel_watts;
      const suggestedPanel = [...panelItems].sort((a, b) => {
        return Math.abs(a.watts - targetPanelWatts) - Math.abs(b.watts - targetPanelWatts);
      })[0];

      suggestions[suggestedPanel.item.id] = Math.max(
        1,
        Math.ceil(sizingPreview.requiredSystemKw / (suggestedPanel.watts / 1000)),
      );
    }

    const inverterItems = pricingCatalog
      .filter(item => item.category === 'inverter')
      .map(item => ({
        item,
        kw: extractInverterKw(item),
      }))
      .filter(
        (entry): entry is {item: PricingItemSummary; kw: number} =>
          entry.kw !== null && entry.kw > 0,
      );

    if (inverterItems.length > 0 && sizingPreview.requiredSystemKw > 0) {
      const suitableItems = inverterItems
        .filter(entry => entry.kw >= sizingPreview.requiredSystemKw)
        .sort((a, b) => a.kw - b.kw);
      const sortedBySize = [...inverterItems].sort((a, b) => a.kw - b.kw);
      const suggestedInverter =
        suitableItems[0] ?? sortedBySize[sortedBySize.length - 1];

      suggestions[suggestedInverter.item.id] = 1;
    }

    const batteryItems = pricingCatalog
      .filter(item => item.category === 'battery')
      .map(item => ({
        item,
        ah: extractBatteryAh(item),
        voltage: extractBatteryVoltage(item),
      }))
      .filter(
        (
          entry,
        ): entry is {item: PricingItemSummary; ah: number; voltage: number | null} =>
          entry.ah !== null && entry.ah > 0,
      );

    if (supportsBatteryFlow && batteryItems.length > 0 && sizingPreview.requiredBatteryAh > 0) {
      const preferredBatteryAh = toNumberOrUndefined(form.battery_capacity_ah);
      const preferredBatteryModel = form.battery_model.trim().toLowerCase();

      let suggestedBattery:
        | {item: PricingItemSummary; ah: number; voltage: number | null}
        | undefined;

      if (preferredBatteryModel || preferredBatteryAh) {
        suggestedBattery = [...batteryItems].sort((a, b) => {
          const aText = getPricingItemSearchText(a.item).toLowerCase();
          const bText = getPricingItemSearchText(b.item).toLowerCase();
          const aModelScore = preferredBatteryModel && aText.includes(preferredBatteryModel) ? 0 : 1;
          const bModelScore = preferredBatteryModel && bText.includes(preferredBatteryModel) ? 0 : 1;
          const aCapacityScore = preferredBatteryAh ? Math.abs(a.ah - preferredBatteryAh) : 0;
          const bCapacityScore = preferredBatteryAh ? Math.abs(b.ah - preferredBatteryAh) : 0;

          return aModelScore - bModelScore || aCapacityScore - bCapacityScore;
        })[0];
      } else if (batteryItems.length === 1) {
        suggestedBattery = batteryItems[0];
      }

      if (suggestedBattery) {
        const selectedBatteryCapacity =
          suggestedBattery.voltage && suggestedBattery.voltage > 0
            ? (suggestedBattery.voltage * suggestedBattery.ah) / 1000
            : null;

        suggestions[suggestedBattery.item.id] = Math.max(
          1,
          Math.ceil(
            selectedBatteryCapacity && selectedBatteryCapacity > 0
              ? sizingPreview.requiredBatteryKwh / selectedBatteryCapacity
              : sizingPreview.requiredBatteryAh / suggestedBattery.ah,
          ),
        );
      }
    }

    return suggestions;
  }, [
    computationDefaults.default_panel_watts,
    form.battery_capacity_ah,
    form.battery_model,
    form.panel_watts,
    pricingCatalog,
    sizingPreview,
    supportsBatteryFlow,
  ]);

  const effectiveCatalogQuantities = useMemo<CatalogQuantityState>(() => {
    const quantities: CatalogQuantityState = {};

    for (const item of pricingCatalog) {
      if (Object.prototype.hasOwnProperty.call(catalogQuantities, item.id)) {
        quantities[item.id] = catalogQuantities[item.id];
        continue;
      }

      if (suggestedQuantities[item.id] !== undefined) {
        quantities[item.id] = String(suggestedQuantities[item.id]);
      }
    }

    return quantities;
  }, [catalogQuantities, pricingCatalog, suggestedQuantities]);

  const selectedLineItems = useMemo<SelectedCatalogLineItem[]>(() => {
    return pricingCatalog
      .map(item => {
        const qty = Number(effectiveCatalogQuantities[item.id] || '');
        const unitAmount = Number(item.default_unit_price || 0);

        if (!Number.isFinite(qty) || qty <= 0) {
          return null;
        }

        if (!supportsBatteryFlow && item.category === 'battery') {
          return null;
        }

        return {
          pricing_item_id: item.id,
          description: item.name || 'Unnamed item',
          category: item.category || 'misc',
          qty: Number(qty.toFixed(2)),
          unit: item.unit || 'pc',
          unit_amount: unitAmount,
          total_amount: Number((qty * unitAmount).toFixed(2)),
          pricing_item: item,
        };
      })
      .filter((item): item is SelectedCatalogLineItem => item !== null);
  }, [effectiveCatalogQuantities, pricingCatalog, supportsBatteryFlow]);

  const computedTotals = useMemo<ComputedTotals>(() => {
    const totals = {
      panelCost: 0,
      inverterCost: 0,
      batteryCost: 0,
      bosCost: 0,
      materialsSubtotal: 0,
      laborCost: 0,
      projectCost: 0,
      roiYears: null as number | null,
    };

    for (const item of selectedLineItems) {
      totals.materialsSubtotal += item.total_amount;

      switch (item.category) {
        case 'panel':
          totals.panelCost += item.total_amount;
          break;
        case 'inverter':
          totals.inverterCost += item.total_amount;
          break;
        case 'battery':
          totals.batteryCost += item.total_amount;
          break;
        default:
          totals.bosCost += item.total_amount;
          break;
      }
    }

    totals.panelCost = Number(totals.panelCost.toFixed(2));
    totals.inverterCost = Number(totals.inverterCost.toFixed(2));
    totals.batteryCost = Number(totals.batteryCost.toFixed(2));
    totals.bosCost = Number(totals.bosCost.toFixed(2));
    totals.materialsSubtotal = Number(totals.materialsSubtotal.toFixed(2));
    totals.projectCost = Number(
      (totals.materialsSubtotal + totals.laborCost).toFixed(2),
    );

    const monthlyBill = toNumberOrUndefined(form.monthly_electric_bill);
    if (monthlyBill && monthlyBill > 0 && totals.projectCost > 0) {
      totals.roiYears = Number(
        ((totals.projectCost / monthlyBill) / 12).toFixed(2),
      );
    }

    return totals;
  }, [form.monthly_electric_bill, selectedLineItems]);

  const validationMessage = useMemo(() => {
    if (!form.monthly_electric_bill) {
      return 'Monthly electric bill is required.';
    }

    if (!form.pv_system_type) {
      return 'PV system type is required.';
    }

    if (pricingCatalog.length === 0) {
      return 'No active pricing items are available yet. Ask the admin to seed or enable the pricing catalog.';
    }

    if (selectedLineItems.length === 0) {
      return 'Add at least one pricing item with a quantity before submitting.';
    }

    return '';
  }, [
    form.monthly_electric_bill,
    form.pv_system_type,
    pricingCatalog.length,
    selectedLineItems.length,
  ]);

  const stepValidationMessage = useMemo(() => {
    if (currentStep === 1) {
      if (!form.monthly_electric_bill) {
        return 'Enter the monthly electric bill before continuing.';
      }

      if (!form.pv_system_type) {
        return 'Select a PV system type before continuing.';
      }
    }

    if (currentStep === 3) {
      if (pricingCatalog.length === 0) {
        return 'No active pricing items are available yet. Ask the admin to seed or enable the pricing catalog.';
      }

      if (selectedLineItems.length === 0) {
        return 'Add at least one pricing item with a quantity before continuing.';
      }
    }

    return '';
  }, [
    currentStep,
    form.monthly_electric_bill,
    form.pv_system_type,
    pricingCatalog.length,
    selectedLineItems.length,
  ]);

  const activeStep = WIZARD_STEPS[currentStep - 1];
  const suggestedInverterItem = useMemo(
    () =>
      pricingCatalog.find(
        item =>
          item.category === 'inverter' &&
          suggestedQuantities[item.id] !== undefined,
      ) ?? null,
    [pricingCatalog, suggestedQuantities],
  );
  const displayValidationMessage =
    currentStep === WIZARD_STEPS.length ? validationMessage : stepValidationMessage;

  const selectedPvSystemLabel =
    finalQuotationOptions?.system_types.find(
      option => option.value === form.pv_system_type,
    )?.label ?? 'Not selected';
  const selectedPanelPresetLabel = form.panel_watts
    ? `${form.panel_watts} W`
    : 'Admin default';
  const selectedBatteryPresetLabel = supportsBatteryFlow
    ? form.battery_model.trim() ||
      (form.battery_capacity_ah
        ? `${form.battery_capacity_ah} Ah`
        : 'No preset')
    : 'Not included';
  const selectedInverterLabel =
    form.inverter_type.trim() ||
    suggestedInverterItem?.name ||
    'Select from catalog';

  const updateField = <K extends keyof FinalQuotationFormState>(
    key: K,
    value: FinalQuotationFormState[K],
  ) => {
    setForm(current => ({
      ...current,
      [key]: value,
    }));
  };

  const applyBatteryPreset = (option: FinalQuotationOption<string> | null) => {
    if (!option) {
      setForm(current => ({
        ...current,
        battery_model: '',
        battery_capacity_ah: '',
        battery_voltage: '',
      }));
      return;
    }

    setForm(current => ({
      ...current,
      battery_model: option.value,
      battery_capacity_ah:
        option.battery_capacity_ah !== undefined
          ? String(option.battery_capacity_ah)
          : current.battery_capacity_ah,
      battery_voltage:
        option.battery_voltage !== undefined
          ? String(option.battery_voltage)
          : current.battery_voltage,
    }));
  };

  const updateCatalogQuantity = (itemId: number, value: string) => {
    const sanitizedValue = sanitizeNumericInput(value);

    setCatalogQuantities(current => ({
      ...current,
      [itemId]: sanitizedValue,
    }));
  };

  const clearCatalogItem = (itemId: number) => {
    setCatalogQuantities(current => ({
      ...current,
      [itemId]: '',
    }));
  };

  const clearBatteryQuantities = () => {
    setCatalogQuantities(current => {
      const nextQuantities = {...current};

      for (const item of pricingCatalog) {
        if (item.category === 'battery') {
          nextQuantities[item.id] = '';
        }
      }

      return nextQuantities;
    });
  };

  const handlePvSystemTypeChange = (value: PvSystemType) => {
    if (value === 'on-grid') {
      setForm(current => ({
        ...current,
        pv_system_type: value,
        with_battery: false,
        battery_model: '',
        battery_capacity_ah: '',
        battery_voltage: '',
      }));
      clearBatteryQuantities();
      return;
    }

    updateField('pv_system_type', value);
  };

  const handleBatteryToggle = (value: boolean) => {
    if (form.pv_system_type === 'on-grid') {
      return;
    }

    updateField('with_battery', value);

    if (!value) {
      setForm(current => ({
        ...current,
        with_battery: false,
        battery_model: '',
        battery_capacity_ah: '',
        battery_voltage: '',
      }));
      clearBatteryQuantities();
    }
  };

  const retryLoad = () => {
    loadInspectionRequest(true);
    loadOptions(true);
    loadPricingCatalog(true);
  };

  const handleNextStep = () => {
    if (stepValidationMessage) {
      Alert.alert('Before you continue', stepValidationMessage);
      return;
    }

    setCurrentStep(current => Math.min(current + 1, WIZARD_STEPS.length));
  };

  const handlePreviousStep = () => {
    setCurrentStep(current => Math.max(current - 1, 1));
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

    let createdQuotationId: number | null = null;

    try {
      setSubmitting(true);
      setSubmitError('');

      const createdQuotation = await submitFinalQuotation({
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
        pv_system_type: form.pv_system_type as PvSystemType,
        with_battery: form.with_battery,
        inverter_type: form.inverter_type.trim() || undefined,
        battery_model: form.with_battery
          ? form.battery_model.trim() || undefined
          : undefined,
        battery_capacity_ah: form.with_battery
          ? toNumberOrUndefined(form.battery_capacity_ah)
          : undefined,
        panel_watts: toNumberOrUndefined(form.panel_watts),
        status: form.status,
        remarks: form.remarks.trim() || undefined,
      });

      if (!createdQuotation?.id) {
        Alert.alert(
          'Submission saved',
          'The final quotation was created, but the detail screen could not be opened automatically.',
        );
        return;
      }

      createdQuotationId = createdQuotation.id;

      const syncedQuotation = await replaceQuotationLineItems(createdQuotation.id, {
        line_items: selectedLineItems.map(item => ({
          pricing_item_id: item.pricing_item_id,
          description: item.description,
          category: item.category,
          qty: item.qty,
          unit: item.unit,
          unit_amount: item.unit_amount,
        })),
      });

      navigation.replace('TechnicianQuotationDetail', {
        quotationId: syncedQuotation.id || createdQuotation.id,
        initialQuotation: syncedQuotation.id ? syncedQuotation : createdQuotation,
      });
    } catch (error) {
      if (createdQuotationId) {
        const syncErrorMessage =
          error instanceof ApiError
            ? formatLaravelErrors(error)
            : 'The line items could not be saved automatically.';

        setSubmitError(
          `Final quotation #${createdQuotationId} was created, but the itemized pricing sync did not complete. ${syncErrorMessage}`,
        );

        Alert.alert(
          'Quotation created with sync issue',
          `Final quotation #${createdQuotationId} was created, but the itemized pricing sync did not complete.\n\n${syncErrorMessage}`,
        );

        navigation.replace('TechnicianQuotationDetail', {
          quotationId: createdQuotationId,
        });
      } else if (error instanceof ApiError) {
        const message = formatLaravelErrors(error);
        setSubmitError(message);
        Alert.alert('Submission failed', message);
      } else {
        setSubmitError('Could not submit the final quotation.');
        Alert.alert('Submission failed', 'Could not submit the final quotation.');
      }
    } finally {
      setSubmitting(false);
    }
  };

  if (loading || optionsLoading || catalogLoading) {
    return (
      <View style={styles.centeredContainer}>
        <ActivityIndicator size="large" color="#2563eb" />
        <Text style={styles.loadingText}>Loading final quotation form...</Text>
      </View>
    );
  }

  if (
    errorMessage ||
    optionsError ||
    catalogError ||
    !inspectionRequest ||
    !finalQuotationOptions
  ) {
    return (
      <View style={styles.centeredContainer}>
        <Text style={styles.errorTitle}>Final quotation unavailable</Text>
        <Text style={styles.errorText}>
          {errorMessage ||
            optionsError ||
            catalogError ||
            'The final quotation form could not be loaded.'}
        </Text>
        <AppButton
          title="Retry"
          onPress={retryLoad}
          style={styles.errorButton}
        />
        <AppButton
          title="Back"
          variant="outline"
          onPress={() => navigation.goBack()}
          style={styles.secondaryButton}
        />
      </View>
    );
  }

  const basicInputsSection = (
    <FormSection
      title="Energy inputs"
      subtitle="Enter the monthly bill and optional computation overrides. These values drive the live requirement summary.">
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
        placeholder="Leave blank to use admin default"
        keyboardType="decimal-pad"
        onChangeText={value =>
          updateField('rate_per_kwh', sanitizeNumericInput(value))
        }
        value={form.rate_per_kwh}
        containerStyle={styles.fieldSpacing}
      />
      <AppInput
        label="Days in month"
        placeholder="Leave blank to use admin default"
        keyboardType="number-pad"
        onChangeText={value =>
          updateField('days_in_month', sanitizeIntegerInput(value))
        }
        value={form.days_in_month}
        containerStyle={styles.fieldSpacing}
      />
      <AppInput
        label="Sun hours"
        placeholder="Leave blank to use admin default"
        keyboardType="decimal-pad"
        onChangeText={value =>
          updateField('sun_hours', sanitizeNumericInput(value))
        }
        value={form.sun_hours}
        containerStyle={styles.fieldSpacing}
      />
      <AppInput
        label="PV safety factor"
        placeholder="Leave blank to use admin default"
        keyboardType="decimal-pad"
        onChangeText={value =>
          updateField('pv_safety_factor', sanitizeNumericInput(value))
        }
        value={form.pv_safety_factor}
        containerStyle={styles.fieldSpacing}
      />
      <AppInput
        label="Battery factor"
        placeholder="Leave blank to use admin default"
        keyboardType="decimal-pad"
        onChangeText={value =>
          updateField('battery_factor', sanitizeNumericInput(value))
        }
        value={form.battery_factor}
        containerStyle={styles.fieldSpacing}
      />
      <AppInput
        label="Battery voltage"
        placeholder="Leave blank to use admin default"
        keyboardType="decimal-pad"
        onChangeText={value =>
          updateField('battery_voltage', sanitizeNumericInput(value))
        }
        value={form.battery_voltage}
      />
    </FormSection>
  );

  const systemSetupSection = (
    <FormSection
      title="System setup"
      subtitle="Use backend-provided options where available, or type optional custom values if needed.">
      <Text style={styles.optionLabel}>PV system type</Text>
      <View style={styles.optionRow}>
        {finalQuotationOptions.system_types.map(option => (
          <OptionChip
            key={option.value}
            label={option.label}
            selected={form.pv_system_type === option.value}
            onPress={() => handlePvSystemTypeChange(option.value as PvSystemType)}
          />
        ))}
      </View>

      <Text style={styles.optionLabel}>Panel watt preset</Text>
      <View style={styles.optionRow}>
        <OptionChip
          label="Use admin default"
          selected={!form.panel_watts}
          onPress={() => updateField('panel_watts', '')}
        />
        {finalQuotationOptions.panel_options.map(option => (
          <OptionChip
            key={option.value}
            label={option.label}
            selected={form.panel_watts === String(option.value)}
            onPress={() => updateField('panel_watts', String(option.value))}
          />
        ))}
      </View>

      <View style={styles.switchRow}>
        <View style={styles.switchTextWrap}>
          <Text style={styles.switchLabel}>With battery</Text>
          <Text style={styles.switchHint}>
            {form.pv_system_type === 'on-grid'
              ? 'Battery is disabled for on-grid quotations.'
              : 'Toggle off if the final proposal does not include battery storage.'}
          </Text>
        </View>
        <Switch
          disabled={form.pv_system_type === 'on-grid'}
          trackColor={{false: '#cbd5e1', true: '#93c5fd'}}
          thumbColor={form.with_battery ? '#2563eb' : '#f8fafc'}
          value={form.with_battery}
          onValueChange={handleBatteryToggle}
        />
      </View>

      <Text style={styles.optionLabel}>Inverter option</Text>
      <View style={styles.optionRow}>
        <OptionChip
          label="No preset"
          selected={!form.inverter_type.trim()}
          onPress={() => updateField('inverter_type', '')}
        />
        {finalQuotationOptions.inverter_options.map(option => (
          <OptionChip
            key={option.value}
            label={option.label}
            selected={form.inverter_type === option.value}
            onPress={() => updateField('inverter_type', option.value)}
          />
        ))}
      </View>

      {supportsBatteryFlow ? (
        <>
          <Text style={styles.optionLabel}>Battery preset</Text>
          <View style={styles.optionRow}>
            <OptionChip
              label="No preset"
              selected={
                !form.battery_model.trim() &&
                !form.battery_capacity_ah &&
                !form.battery_voltage
              }
              onPress={() => applyBatteryPreset(null)}
            />
            {finalQuotationOptions.battery_options.map(option => (
              <OptionChip
                key={option.value}
                label={option.label}
                selected={form.battery_model === option.value}
                onPress={() => applyBatteryPreset(option)}
              />
            ))}
          </View>
        </>
      ) : null}

      <AppInput
        label="Inverter type"
        placeholder="Leave blank or use a preset above"
        onChangeText={value => updateField('inverter_type', value)}
        value={form.inverter_type}
        containerStyle={styles.fieldSpacing}
      />
      <AppInput
        label="Battery model"
        editable={supportsBatteryFlow}
        placeholder="Leave blank or use a preset above"
        onChangeText={value => updateField('battery_model', value)}
        value={form.battery_model}
        containerStyle={styles.fieldSpacing}
      />
      <AppInput
        label="Battery capacity Ah"
        editable={supportsBatteryFlow}
        placeholder="Optional"
        keyboardType="decimal-pad"
        onChangeText={value =>
          updateField('battery_capacity_ah', sanitizeNumericInput(value))
        }
        value={form.battery_capacity_ah}
        containerStyle={styles.fieldSpacing}
      />
      <AppInput
        label="Panel watts"
        placeholder="Leave blank to use admin default"
        keyboardType="decimal-pad"
        onChangeText={value =>
          updateField('panel_watts', sanitizeNumericInput(value))
        }
        value={form.panel_watts}
      />
    </FormSection>
  );

  const computedRequirementSection = (
    <FormSection
      title="Calculated system outputs"
      subtitle="These values mirror the current backend sizing formula and update automatically when Step 1 changes.">
      <View style={styles.totalsGrid}>
        <View style={styles.totalCard}>
          <Text style={styles.totalLabel}>Required PV size</Text>
          <Text style={styles.totalValue}>
            {sizingPreview
              ? `${sizingPreview.requiredSystemKw.toFixed(2)} kW`
              : 'Enter bill'}
          </Text>
        </View>
        <View style={styles.totalCard}>
          <Text style={styles.totalLabel}>Projected system size</Text>
          <Text style={styles.totalValue}>
            {sizingPreview
              ? `${sizingPreview.suggestedSystemKw.toFixed(2)} kW`
              : 'Enter bill'}
          </Text>
        </View>
        <View style={styles.totalCard}>
          <Text style={styles.totalLabel}>Baseline panel qty</Text>
          <Text style={styles.totalValue}>
            {sizingPreview ? String(sizingPreview.panelQuantityBaseline) : '0'}
          </Text>
        </View>
        <View style={styles.totalCard}>
          <Text style={styles.totalLabel}>Required battery</Text>
          <Text style={styles.totalValue}>
            {supportsBatteryFlow && sizingPreview
              ? `${sizingPreview.requiredBatteryKwh.toFixed(2)} kWh`
              : 'N/A'}
          </Text>
          <Text style={styles.totalSubValue}>
            {supportsBatteryFlow && sizingPreview
              ? `${sizingPreview.requiredBatteryAh.toFixed(2)} Ah`
              : 'Battery hidden for on-grid'}
          </Text>
        </View>
        <View style={styles.totalCard}>
          <Text style={styles.totalLabel}>Suggested inverter</Text>
          <Text style={styles.totalValue}>
            {suggestedInverterItem?.name || form.inverter_type.trim() || 'Select from catalog'}
          </Text>
          <Text style={styles.totalSubValue}>
            {suggestedInverterItem
              ? 'Nearest suitable inverter from catalog'
              : 'Updates after catalog loads and bill is entered'}
          </Text>
        </View>
      </View>
    </FormSection>
  );

  const selectedLineItemsSection = (
    <FormSection
      title="Selected line items"
      subtitle="Anything with a quantity greater than zero will be saved as an itemized final quotation line item.">
      {selectedLineItems.length > 0 ? (
        selectedLineItems.map(item => (
          <View key={item.pricing_item_id} style={styles.selectedItemCard}>
            <View style={styles.selectedItemHeader}>
              <View style={styles.selectedItemTextWrap}>
                <Text style={styles.selectedItemName}>{item.description}</Text>
                <Text style={styles.selectedItemMeta}>
                  {formatCategoryLabel(item.category)} • {item.unit} •{' '}
                  {formatQuotationCurrency(item.unit_amount, {
                    currency: 'PHP',
                    fallback: 'PHP 0.00',
                    spaceAfterCurrency: true,
                  })} each
                </Text>
              </View>
              <AppButton
                title="Remove"
                variant="outline"
                onPress={() => clearCatalogItem(item.pricing_item_id)}
                style={styles.removeButton}
                textStyle={styles.removeButtonText}
              />
            </View>

            <View style={styles.selectedItemTotalsRow}>
              <Text style={styles.selectedItemTotalsLabel}>
                Qty {item.qty.toFixed(2).replace(/\.00$/, '')}
              </Text>
              <Text style={styles.selectedItemTotalsValue}>
                {formatQuotationCurrency(item.total_amount, {
                  currency: 'PHP',
                  fallback: 'PHP 0.00',
                  spaceAfterCurrency: true,
                })}
              </Text>
            </View>
          </View>
        ))
      ) : (
        <Text style={styles.emptyCatalogText}>
          No pricing items selected yet. Add quantities from the catalog
          below.
        </Text>
      )}
    </FormSection>
  );

  const pricingCatalogSection = (
    <FormSection
      title="Pricing catalog"
      subtitle="Use the admin-managed catalog to add itemized components and materials.">
      {groupedPricingCatalog.map(group => (
        <View key={group.category} style={styles.catalogGroup}>
          <Text style={styles.catalogGroupTitle}>
            {formatCategoryLabel(group.category)}
          </Text>

          {group.items.map(item => (
            <View key={item.id} style={styles.catalogItemCard}>
              <View style={styles.catalogItemInfo}>
                <Text style={styles.catalogItemName}>
                  {item.name || 'Unnamed item'}
                </Text>
                <Text style={styles.catalogItemMeta}>
                  {item.unit || 'pc'} •{' '}
                  {formatQuotationCurrency(Number(item.default_unit_price || 0), {
                    currency: 'PHP',
                    fallback: 'PHP 0.00',
                    spaceAfterCurrency: true,
                  })}
                </Text>
                {suggestedQuantities[item.id] !== undefined ? (
                  <Text style={styles.catalogSuggestionText}>
                    {item.category === 'panel'
                      ? `Suggested qty ${suggestedQuantities[item.id]} from required PV size.`
                      : item.category === 'battery'
                        ? `Suggested qty ${suggestedQuantities[item.id]} from required battery capacity.`
                        : `Suggested qty ${suggestedQuantities[item.id]} as the nearest suitable inverter selection.`}
                  </Text>
                ) : null}
              </View>

              <View style={styles.catalogItemActions}>
                <AppInput
                  label="Qty"
                  keyboardType="decimal-pad"
                  onChangeText={value => updateCatalogQuantity(item.id, value)}
                  value={effectiveCatalogQuantities[item.id] || ''}
                  containerStyle={styles.quantityInputContainer}
                />
                {suggestedQuantities[item.id] !== undefined &&
                catalogQuantities[item.id] !== undefined &&
                catalogQuantities[item.id] !==
                  String(suggestedQuantities[item.id]) ? (
                  <AppButton
                    title="Use Suggested"
                    variant="secondary"
                    onPress={() =>
                      updateCatalogQuantity(
                        item.id,
                        String(suggestedQuantities[item.id]),
                      )
                    }
                    style={styles.suggestButton}
                    textStyle={styles.suggestButtonText}
                  />
                ) : null}
                {(effectiveCatalogQuantities[item.id] || '').trim() ? (
                  <AppButton
                    title="Clear"
                    variant="outline"
                    onPress={() => clearCatalogItem(item.id)}
                    style={styles.clearButton}
                    textStyle={styles.clearButtonText}
                  />
                ) : null}
              </View>
            </View>
          ))}
        </View>
      ))}
    </FormSection>
  );

  const computedTotalsSection = (
    <FormSection
      title="Computed totals"
      subtitle="These totals are previewed from the selected catalog items and will be recomputed by the backend after save.">
      <View style={styles.totalsGrid}>
        <View style={styles.totalCard}>
          <Text style={styles.totalLabel}>Panel cost</Text>
          <Text style={styles.totalValue}>
            {formatQuotationCurrency(computedTotals.panelCost, {
              currency: 'PHP',
              fallback: 'PHP 0.00',
              spaceAfterCurrency: true,
            })}
          </Text>
        </View>
        <View style={styles.totalCard}>
          <Text style={styles.totalLabel}>Inverter cost</Text>
          <Text style={styles.totalValue}>
            {formatQuotationCurrency(computedTotals.inverterCost, {
              currency: 'PHP',
              fallback: 'PHP 0.00',
              spaceAfterCurrency: true,
            })}
          </Text>
        </View>
        <View style={styles.totalCard}>
          <Text style={styles.totalLabel}>Battery cost</Text>
          <Text style={styles.totalValue}>
            {formatQuotationCurrency(computedTotals.batteryCost, {
              currency: 'PHP',
              fallback: 'PHP 0.00',
              spaceAfterCurrency: true,
            })}
          </Text>
        </View>
        <View style={styles.totalCard}>
          <Text style={styles.totalLabel}>BOS cost</Text>
          <Text style={styles.totalValue}>
            {formatQuotationCurrency(computedTotals.bosCost, {
              currency: 'PHP',
              fallback: 'PHP 0.00',
              spaceAfterCurrency: true,
            })}
          </Text>
        </View>
        <View style={styles.totalCard}>
          <Text style={styles.totalLabel}>Materials subtotal</Text>
          <Text style={styles.totalValue}>
            {formatQuotationCurrency(computedTotals.materialsSubtotal, {
              currency: 'PHP',
              fallback: 'PHP 0.00',
              spaceAfterCurrency: true,
            })}
          </Text>
        </View>
        <View style={styles.totalCard}>
          <Text style={styles.totalLabel}>Labor cost</Text>
          <Text style={styles.totalValue}>
            {formatQuotationCurrency(computedTotals.laborCost, {
              currency: 'PHP',
              fallback: 'PHP 0.00',
              spaceAfterCurrency: true,
            })}
          </Text>
        </View>
      </View>

      <View style={styles.projectTotalCard}>
        <Text style={styles.projectTotalLabel}>Estimated project cost</Text>
        <Text style={styles.projectTotalValue}>
          {formatQuotationCurrency(computedTotals.projectCost, {
            currency: 'PHP',
            fallback: 'PHP 0.00',
            spaceAfterCurrency: true,
          })}
        </Text>
        <Text style={styles.projectTotalHint}>
          ROI preview:{' '}
          {computedTotals.roiYears !== null
            ? `${computedTotals.roiYears.toFixed(2)} years`
            : 'available after bill + items are entered'}
        </Text>
      </View>
    </FormSection>
  );

  const notesSection = (
    <FormSection
      title="Remarks"
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
  );

  const reviewSection = (
    <>
      <FormSection
        title="System choices"
        subtitle="Review the technician inputs that drive the final quotation.">
        <View style={styles.summaryRow}>
          <Text style={styles.summaryLabel}>Monthly bill</Text>
          <Text style={styles.summaryValue}>
            {formatQuotationCurrency(
              toNumberOrUndefined(form.monthly_electric_bill),
              {
                currency: 'PHP',
                fallback: 'PHP 0.00',
                spaceAfterCurrency: true,
              },
            )}
          </Text>
        </View>
        <View style={styles.summaryRow}>
          <Text style={styles.summaryLabel}>PV system type</Text>
          <Text style={styles.summaryValue}>{selectedPvSystemLabel}</Text>
        </View>
        <View style={styles.summaryRow}>
          <Text style={styles.summaryLabel}>With battery</Text>
          <Text style={styles.summaryValue}>
            {supportsBatteryFlow ? 'Yes' : 'No'}
          </Text>
        </View>
        <View style={styles.summaryRow}>
          <Text style={styles.summaryLabel}>Panel preset</Text>
          <Text style={styles.summaryValue}>{selectedPanelPresetLabel}</Text>
        </View>
        <View style={styles.summaryRow}>
          <Text style={styles.summaryLabel}>Suggested inverter</Text>
          <Text style={styles.summaryValue}>{selectedInverterLabel}</Text>
        </View>
        <View style={styles.summaryRow}>
          <Text style={styles.summaryLabel}>Battery preset</Text>
          <Text style={styles.summaryValue}>{selectedBatteryPresetLabel}</Text>
        </View>
      </FormSection>

      {computedRequirementSection}
      {selectedLineItemsSection}
      {computedTotalsSection}
      {notesSection}
    </>
  );

  const renderStepContent = () => {
    switch (currentStep) {
      case 1:
        return (
          <>
            {basicInputsSection}
            {systemSetupSection}
          </>
        );
      case 2:
        return (
          <>
            <AppCard style={styles.infoCard}>
              <Text style={styles.infoTitle}>Live sizing preview</Text>
              <Text style={styles.infoText}>
                Step 2 updates automatically from the values in Step 1, so you
                can go back anytime and refine the system inputs without losing
                your work.
              </Text>
            </AppCard>
            {computedRequirementSection}
          </>
        );
      case 3:
        return (
          <>
            <AppCard style={styles.infoCard}>
              <Text style={styles.infoTitle}>Catalog-based pricing</Text>
              <Text style={styles.infoText}>
                Pricing comes from the admin-managed catalog. Suggested
                quantities stay editable, and the totals below are computed
                automatically before the quotation is saved.
              </Text>
            </AppCard>
            {selectedLineItemsSection}
            {pricingCatalogSection}
            {computedTotalsSection}
          </>
        );
      case 4:
      default:
        return reviewSection;
    }
  };

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

        <AppCard style={styles.stepperCard}>
          <Text style={styles.stepHeaderEyebrow}>
            Step {activeStep.number} of {WIZARD_STEPS.length}
          </Text>
          <Text style={styles.stepHeaderTitle}>{activeStep.label}</Text>
          <Text style={styles.stepHeaderSubtitle}>{activeStep.subtitle}</Text>

          <View style={styles.stepPillGrid}>
            {WIZARD_STEPS.map(step => (
              <View
                key={step.number}
                style={[
                  styles.stepPill,
                  step.number === currentStep ? styles.stepPillActive : null,
                  step.number < currentStep ? styles.stepPillComplete : null,
                ]}>
                <Text
                  style={[
                    styles.stepPillNumber,
                    step.number === currentStep || step.number < currentStep
                      ? styles.stepPillNumberActive
                      : null,
                  ]}>
                  {step.number}
                </Text>
                <Text
                  style={[
                    styles.stepPillLabel,
                    step.number === currentStep || step.number < currentStep
                      ? styles.stepPillLabelActive
                      : null,
                  ]}>
                  {step.label}
                </Text>
              </View>
            ))}
          </View>
        </AppCard>

        <FormSection
          title="Inspection summary"
          subtitle="This final quotation stays tied directly to the completed inspection request.">
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

        {submitError ? (
          <AppCard style={styles.errorCard}>
            <Text style={styles.errorCardTitle}>Submission error</Text>
            <Text style={styles.errorCardText}>{submitError}</Text>
          </AppCard>
        ) : null}

        {displayValidationMessage ? (
          <AppCard style={styles.infoCard}>
            <Text style={styles.infoTitle}>
              {currentStep === WIZARD_STEPS.length
                ? 'Before you submit'
                : 'Before you continue'}
            </Text>
            <Text style={styles.infoText}>{displayValidationMessage}</Text>
          </AppCard>
        ) : null}

        {renderStepContent()}

        <View style={styles.footerActions}>
          {currentStep > 1 ? (
            <AppButton
              title="Back"
              variant="outline"
              onPress={handlePreviousStep}
              style={styles.footerActionButton}
            />
          ) : null}

          {currentStep < WIZARD_STEPS.length ? (
            <AppButton
              title="Next"
              onPress={handleNextStep}
              style={styles.footerActionButton}
            />
          ) : (
            <AppButton
              title={
                submitting
                  ? 'Submitting final quotation...'
                  : 'Submit Final Quotation'
              }
              disabled={submitting || !completed}
              onPress={handleSubmit}
              style={styles.footerActionButton}
            />
          )}
        </View>
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
  secondaryButton: {
    marginTop: 12,
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
  stepperCard: {
    marginBottom: 18,
  },
  stepHeaderEyebrow: {
    color: '#2563eb',
    fontSize: 12,
    fontWeight: '700',
    letterSpacing: 0.3,
    marginBottom: 8,
    textTransform: 'uppercase',
  },
  stepHeaderTitle: {
    color: '#0f172a',
    fontSize: 24,
    fontWeight: '800',
    marginBottom: 6,
  },
  stepHeaderSubtitle: {
    color: '#64748b',
    fontSize: 14,
    lineHeight: 20,
    marginBottom: 16,
  },
  stepPillGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 10,
  },
  stepPill: {
    backgroundColor: '#f8fafc',
    borderColor: '#cbd5e1',
    borderRadius: 18,
    borderWidth: 1,
    minWidth: '47%',
    paddingHorizontal: 14,
    paddingVertical: 12,
  },
  stepPillActive: {
    backgroundColor: '#dbeafe',
    borderColor: '#2563eb',
  },
  stepPillComplete: {
    backgroundColor: '#dcfce7',
    borderColor: '#22c55e',
  },
  stepPillNumber: {
    color: '#64748b',
    fontSize: 12,
    fontWeight: '800',
    marginBottom: 4,
  },
  stepPillNumberActive: {
    color: '#1d4ed8',
  },
  stepPillLabel: {
    color: '#334155',
    fontSize: 14,
    fontWeight: '700',
  },
  stepPillLabelActive: {
    color: '#0f172a',
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
    alignItems: 'center',
    borderTopColor: '#e2e8f0',
    borderTopWidth: 1,
    flexDirection: 'row',
    justifyContent: 'space-between',
    paddingVertical: 12,
  },
  summaryLabel: {
    color: '#64748b',
    fontSize: 12,
    fontWeight: '700',
    textTransform: 'uppercase',
  },
  summaryValue: {
    color: '#0f172a',
    fontSize: 15,
    fontWeight: '600',
    maxWidth: '55%',
    textAlign: 'right',
  },
  summaryDetails: {
    borderTopColor: '#e2e8f0',
    borderTopWidth: 1,
    paddingTop: 12,
  },
  summaryDetailsText: {
    color: '#0f172a',
    fontSize: 15,
    lineHeight: 22,
    marginTop: 6,
  },
  warningCard: {
    backgroundColor: '#fff7ed',
    borderColor: '#fdba74',
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
    lineHeight: 21,
  },
  infoCard: {
    backgroundColor: '#eff6ff',
    borderColor: '#bfdbfe',
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
    lineHeight: 21,
  },
  errorCard: {
    backgroundColor: '#fef2f2',
    borderColor: '#fecaca',
    marginBottom: 18,
  },
  errorCardTitle: {
    color: '#b91c1c',
    fontSize: 18,
    fontWeight: '700',
    marginBottom: 6,
  },
  errorCardText: {
    color: '#991b1b',
    fontSize: 14,
    lineHeight: 21,
  },
  fieldSpacing: {
    marginBottom: 14,
  },
  optionLabel: {
    color: '#475569',
    fontSize: 12,
    fontWeight: '700',
    marginBottom: 10,
    textTransform: 'uppercase',
  },
  optionRow: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 10,
    marginBottom: 16,
  },
  optionChip: {
    backgroundColor: '#ffffff',
    borderColor: '#cbd5e1',
    borderRadius: 999,
    borderWidth: 1,
    paddingHorizontal: 14,
    paddingVertical: 10,
  },
  optionChipSelected: {
    backgroundColor: '#dbeafe',
    borderColor: '#2563eb',
  },
  optionChipPressed: {
    opacity: 0.85,
  },
  optionChipText: {
    color: '#334155',
    fontSize: 14,
    fontWeight: '600',
  },
  optionChipTextSelected: {
    color: '#1d4ed8',
  },
  switchRow: {
    alignItems: 'center',
    borderColor: '#e2e8f0',
    borderRadius: 18,
    borderWidth: 1,
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 16,
    paddingHorizontal: 16,
    paddingVertical: 14,
  },
  switchTextWrap: {
    flex: 1,
    paddingRight: 14,
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
  selectedItemCard: {
    backgroundColor: '#f8fafc',
    borderColor: '#e2e8f0',
    borderRadius: 18,
    borderWidth: 1,
    marginBottom: 12,
    padding: 14,
  },
  selectedItemHeader: {
    alignItems: 'flex-start',
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 12,
  },
  selectedItemTextWrap: {
    flex: 1,
    paddingRight: 12,
  },
  selectedItemName: {
    color: '#0f172a',
    fontSize: 16,
    fontWeight: '700',
    marginBottom: 4,
  },
  selectedItemMeta: {
    color: '#64748b',
    fontSize: 13,
    lineHeight: 18,
  },
  selectedItemTotalsRow: {
    alignItems: 'center',
    borderTopColor: '#e2e8f0',
    borderTopWidth: 1,
    flexDirection: 'row',
    justifyContent: 'space-between',
    paddingTop: 12,
  },
  selectedItemTotalsLabel: {
    color: '#475569',
    fontSize: 13,
    fontWeight: '600',
  },
  selectedItemTotalsValue: {
    color: '#0f172a',
    fontSize: 16,
    fontWeight: '800',
  },
  removeButton: {
    minHeight: 42,
    minWidth: 92,
    paddingHorizontal: 12,
    paddingVertical: 10,
  },
  removeButtonText: {
    fontSize: 13,
  },
  emptyCatalogText: {
    color: '#64748b',
    fontSize: 14,
    lineHeight: 21,
  },
  catalogGroup: {
    marginBottom: 18,
  },
  catalogGroupTitle: {
    color: '#0f172a',
    fontSize: 16,
    fontWeight: '700',
    marginBottom: 10,
  },
  catalogItemCard: {
    backgroundColor: '#f8fafc',
    borderColor: '#e2e8f0',
    borderRadius: 18,
    borderWidth: 1,
    marginBottom: 10,
    padding: 14,
  },
  catalogItemInfo: {
    marginBottom: 10,
  },
  catalogItemName: {
    color: '#0f172a',
    fontSize: 15,
    fontWeight: '700',
    marginBottom: 4,
  },
  catalogItemMeta: {
    color: '#64748b',
    fontSize: 13,
    lineHeight: 18,
  },
  catalogSuggestionText: {
    color: '#1d4ed8',
    fontSize: 12,
    fontWeight: '600',
    lineHeight: 18,
    marginTop: 6,
  },
  catalogItemActions: {
    alignItems: 'flex-end',
    flexDirection: 'row',
    gap: 10,
  },
  quantityInputContainer: {
    flex: 1,
  },
  suggestButton: {
    minHeight: 48,
    minWidth: 108,
    paddingHorizontal: 12,
    paddingVertical: 10,
  },
  suggestButtonText: {
    fontSize: 12,
  },
  clearButton: {
    minHeight: 48,
    minWidth: 84,
    paddingHorizontal: 12,
    paddingVertical: 10,
  },
  clearButtonText: {
    fontSize: 13,
  },
  totalsGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 12,
  },
  totalCard: {
    backgroundColor: '#f8fafc',
    borderColor: '#e2e8f0',
    borderRadius: 18,
    borderWidth: 1,
    minWidth: '47%',
    padding: 14,
  },
  totalLabel: {
    color: '#64748b',
    fontSize: 12,
    fontWeight: '700',
    marginBottom: 6,
    textTransform: 'uppercase',
  },
  totalValue: {
    color: '#0f172a',
    fontSize: 16,
    fontWeight: '800',
  },
  totalSubValue: {
    color: '#64748b',
    fontSize: 12,
    fontWeight: '600',
    marginTop: 6,
  },
  projectTotalCard: {
    backgroundColor: '#dcfce7',
    borderColor: '#86efac',
    borderRadius: 22,
    borderWidth: 1,
    marginTop: 16,
    padding: 18,
  },
  projectTotalLabel: {
    color: '#166534',
    fontSize: 12,
    fontWeight: '700',
    marginBottom: 6,
    textTransform: 'uppercase',
  },
  projectTotalValue: {
    color: '#14532d',
    fontSize: 28,
    fontWeight: '800',
    marginBottom: 6,
  },
  projectTotalHint: {
    color: '#166534',
    fontSize: 14,
    lineHeight: 20,
  },
  textArea: {
    minHeight: 120,
    textAlignVertical: 'top',
  },
  footerActions: {
    flexDirection: 'row',
    gap: 12,
  },
  footerActionButton: {
    flex: 1,
  },
});
