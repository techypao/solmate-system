type UserContactSource = {
  contact_number?: string | null;
  landline_number?: string | null;
};

function normalizeContactNumber(value?: string | null) {
  const trimmedValue = typeof value === 'string' ? value.trim() : '';
  return trimmedValue !== '' ? trimmedValue : '';
}

export function getDefaultContactNumber(user?: UserContactSource | null) {
  return (
    normalizeContactNumber(user?.contact_number) ||
    normalizeContactNumber(user?.landline_number) ||
    ''
  );
}