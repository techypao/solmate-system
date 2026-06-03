import {DefaultTheme} from '@react-navigation/native';

export const solmateColors = {
  primary: '#F5C000',
  primaryHover: '#E0AD00',
  accentSky: '#7DDFF2',
  accentStrong: '#3B8ED4',
  navy: '#1A2B55',
  text: '#1A2B55',
  white: '#FFFFFF',
  background: '#C8D8F0',
  backgroundSoft: '#EEF4FC',
  border: '#D4E0F2',
  borderStrong: '#B8CCE8',
  muted: '#6B7A99',
  mutedSoft: '#8A96B0',
  shadow: '#7A9BBD',
  disabled: '#D4E0F2',
  danger: '#D14343',
  dangerSoft: '#FFF1F1',
  warningText: '#7A4F00',
  warningSoft: '#FFF0A0',
  infoSoft: '#E2EBF8',
  successSoft: '#E0F4F8',
} as const;

export function getSolmateStatusColors(status?: string | null) {
  switch ((status || '').toLowerCase()) {
    case 'pending':
      return {
        backgroundColor: solmateColors.warningSoft,
        textColor: solmateColors.warningText,
      };
    case 'assigned':
      return {
        backgroundColor: '#EEF8FC',
        textColor: solmateColors.navy,
      };
    case 'in_progress':
      return {
        backgroundColor: solmateColors.accentSky,
        textColor: solmateColors.text,
      };
    case 'completed':
    case 'approved':
      return {
        backgroundColor: solmateColors.successSoft,
        textColor: solmateColors.accentStrong,
      };
    case 'cancelled':
    case 'rejected':
      return {
        backgroundColor: solmateColors.dangerSoft,
        textColor: solmateColors.danger,
      };
    default:
      return {
        backgroundColor: solmateColors.background,
        textColor: solmateColors.muted,
      };
  }
}

export const solmateNavigationTheme = {
  ...DefaultTheme,
  dark: false,
  colors: {
    ...DefaultTheme.colors,
    primary: solmateColors.accentStrong,
    background: solmateColors.background,
    card: solmateColors.white,
    text: solmateColors.text,
    border: solmateColors.border,
    notification: solmateColors.primary,
  },
};