jest.mock('@react-navigation/native', () => ({
  DefaultTheme: {
    dark: false,
    colors: {},
  },
}));

import {
  formatDisplayValue,
  formatServiceRequestStatus,
  normalizeRequestStatus,
} from '../src/utils/technicianRequests';

describe('technician request display helpers', () => {
  it('formats nullable and primitive API values safely', () => {
    expect(formatDisplayValue(null, 'Fallback')).toBe('Fallback');
    expect(formatDisplayValue(undefined, 'Fallback')).toBe('Fallback');
    expect(formatDisplayValue('  Solar inspection  ')).toBe('Solar inspection');
    expect(formatDisplayValue(42)).toBe('42');
  });

  it('extracts useful labels from unexpected object responses', () => {
    expect(
      formatDisplayValue(
        {
          formatted_address: '123 Solar St',
          latitude: 14.5,
          longitude: 121,
        },
        'Not provided',
      ),
    ).toBe('123 Solar St');
  });

  it('falls back for unknown object responses instead of rendering objects', () => {
    expect(formatDisplayValue({latitude: 14.5}, 'Not provided')).toBe(
      'Not provided',
    );
  });

  it('normalizes status values used by filters and actions', () => {
    expect(normalizeRequestStatus('In Progress')).toBe('in_progress');
    expect(formatServiceRequestStatus({value: 'completed'})).toBe('Completed');
  });
});
