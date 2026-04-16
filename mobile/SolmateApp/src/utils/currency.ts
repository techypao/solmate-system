type FormatQuotationCurrencyOptions = {
  currency?: string;
  fallback?: string;
  spaceAfterCurrency?: boolean;
};

function formatNumberWithThousandsSeparators(value: number) {
  const [whole, fraction] = value.toFixed(2).split('.');
  const withSeparators = whole.replace(/\B(?=(\d{3})+(?!\d))/g, ',');

  return `${withSeparators}.${fraction}`;
}

export function formatQuotationCurrency(
  value: unknown,
  options: FormatQuotationCurrencyOptions = {},
) {
  const {
    currency = '₱',
    fallback = 'N/A',
    spaceAfterCurrency = false,
  } = options;

  if (value === null || value === undefined || value === '') {
    return fallback;
  }

  const numericValue =
    typeof value === 'number' ? value : Number(String(value).trim());

  if (!Number.isFinite(numericValue)) {
    return fallback;
  }

  const separator = spaceAfterCurrency ? ' ' : '';

  return `${currency}${separator}${formatNumberWithThousandsSeparators(
    numericValue,
  )}`;
}
