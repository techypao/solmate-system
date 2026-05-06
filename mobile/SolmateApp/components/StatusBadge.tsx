import React from 'react';
import {StyleSheet, Text, View} from 'react-native';

import {
  formatServiceRequestStatus,
  getServiceRequestStatusColors,
} from '../src/utils/technicianRequests';

type StatusBadgeProps = {
  status?: string | null;
  label?: string | null;
  colors?: {
    backgroundColor: string;
    textColor: string;
  } | null;
};

export default function StatusBadge({status, label, colors}: StatusBadgeProps) {
  const resolvedColors = colors ?? getServiceRequestStatusColors(status);
  const resolvedLabel = label ?? formatServiceRequestStatus(status);

  return (
    <View style={[styles.badge, {backgroundColor: resolvedColors.backgroundColor}]}>
      <Text style={[styles.badgeText, {color: resolvedColors.textColor}]}>
        {resolvedLabel}
      </Text>
    </View>
  );
}

const styles = StyleSheet.create({
  badge: {
    alignSelf: 'flex-start',
    borderRadius: 999,
    minWidth: 96,
    paddingHorizontal: 12,
    paddingVertical: 7,
  },
  badgeText: {
    fontSize: 12,
    fontWeight: '700',
    letterSpacing: 0.4,
    textAlign: 'center',
  },
});
