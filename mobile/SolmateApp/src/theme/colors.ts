export const solmateColors = {
  primary: '#F4D000',
  primaryHover: '#E6C200',
  accentSky: '#7DDFF2',
  accentStrong: '#20A7C9',
  navy: '#123A5A',
  text: '#0F2F4A',
  white: '#FFFFFF',
  background: '#F8FAFC',
  backgroundSoft: '#F2FAFD',
  border: '#DDE7EE',
  borderStrong: '#C8D9E4',
  muted: '#5E7288',
  mutedSoft: '#7F92A3',
  shadow: '#95AABD',
  disabled: '#D7E1E8',
  danger: '#D14343',
  dangerSoft: '#FFF1F1',
  warningText: '#946F00',
  warningSoft: '#FFF7CC',
  infoSoft: '#EAF9FD',
  successSoft: '#E8F8FC',
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
  dark: false,
  colors: {
    primary: solmateColors.accentStrong,
    background: solmateColors.background,
    card: solmateColors.white,
    text: solmateColors.text,
    border: solmateColors.border,
    notification: solmateColors.primary,
  },
};