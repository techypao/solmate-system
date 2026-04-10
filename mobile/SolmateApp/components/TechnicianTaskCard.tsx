import React from 'react';
import {Pressable, StyleSheet, Text, View} from 'react-native';

import {TechnicianServiceRequest} from '../src/services/technicianApi';
import {
  formatDate,
  formatDateTime,
  getCustomerName,
} from '../src/utils/technicianRequests';
import StatusBadge from './StatusBadge';

type TechnicianTaskCardProps = {
  serviceRequest: TechnicianServiceRequest;
  onPress?: () => void;
};

export default function TechnicianTaskCard({
  serviceRequest,
  onPress,
}: TechnicianTaskCardProps) {
  return (
    <Pressable
      disabled={!onPress}
      onPress={onPress}
      style={({pressed}) => [
        styles.card,
        pressed && onPress ? styles.pressed : null,
      ]}>
      <View style={styles.cardAccent} />

      <View style={styles.headerRow}>
        <View style={styles.titleWrap}>
          <Text style={styles.eyebrow}>
            {getCustomerName(serviceRequest)}
          </Text>
          <Text style={styles.title}>{serviceRequest.request_type}</Text>
        </View>

        <StatusBadge status={serviceRequest.status} />
      </View>

      <View style={styles.detailsCard}>
        <Text style={styles.detailsLabel}>Request details</Text>
        <Text style={styles.detailsText}>{serviceRequest.details}</Text>
      </View>

      <View style={styles.metaRow}>
        <View style={styles.metaCard}>
          <Text style={styles.metaLabel}>Date needed</Text>
          <Text style={styles.metaValue}>
            {formatDate(serviceRequest.date_needed)}
          </Text>
        </View>

        <View style={styles.metaCard}>
          <Text style={styles.metaLabel}>Assigned on</Text>
          <Text style={styles.metaValue}>
            {formatDateTime(serviceRequest.created_at)}
          </Text>
        </View>
      </View>

      {onPress ? (
        <View style={styles.footer}>
          <Text style={styles.footerText}>Tap to open request details</Text>
        </View>
      ) : null}
    </Pressable>
  );
}

const styles = StyleSheet.create({
  card: {
    backgroundColor: '#ffffff',
    borderColor: '#e2e8f0',
    borderRadius: 22,
    borderWidth: 1,
    marginBottom: 14,
    overflow: 'hidden',
    padding: 18,
    shadowColor: '#0f172a',
    shadowOffset: {
      width: 0,
      height: 10,
    },
    shadowOpacity: 0.06,
    shadowRadius: 16,
    elevation: 2,
  },
  pressed: {
    opacity: 0.88,
  },
  cardAccent: {
    backgroundColor: '#93c5fd',
    borderRadius: 999,
    height: 8,
    marginBottom: 14,
    width: 64,
  },
  headerRow: {
    alignItems: 'flex-start',
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 14,
  },
  titleWrap: {
    flex: 1,
    paddingRight: 14,
  },
  eyebrow: {
    color: '#64748b',
    fontSize: 12,
    fontWeight: '700',
    letterSpacing: 0.4,
    marginBottom: 4,
    textTransform: 'uppercase',
  },
  title: {
    color: '#0f172a',
    fontSize: 18,
    fontWeight: '800',
    lineHeight: 24,
  },
  detailsCard: {
    backgroundColor: '#f8fafc',
    borderColor: '#e2e8f0',
    borderRadius: 16,
    borderWidth: 1,
    marginBottom: 14,
    padding: 14,
  },
  detailsLabel: {
    color: '#475569',
    fontSize: 12,
    fontWeight: '700',
    marginBottom: 6,
    textTransform: 'uppercase',
  },
  detailsText: {
    color: '#334155',
    fontSize: 14,
    lineHeight: 21,
  },
  metaRow: {
    flexDirection: 'row',
    gap: 12,
  },
  metaCard: {
    backgroundColor: '#eff6ff',
    borderRadius: 16,
    flex: 1,
    padding: 14,
  },
  metaLabel: {
    color: '#64748b',
    fontSize: 12,
    fontWeight: '700',
    marginBottom: 6,
    textTransform: 'uppercase',
  },
  metaValue: {
    color: '#0f172a',
    fontSize: 14,
    fontWeight: '600',
    lineHeight: 20,
  },
  footer: {
    borderTopColor: '#e2e8f0',
    borderTopWidth: 1,
    marginTop: 14,
    paddingTop: 12,
  },
  footerText: {
    color: '#2563eb',
    fontSize: 13,
    fontWeight: '700',
  },
});
