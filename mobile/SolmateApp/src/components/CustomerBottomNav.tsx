import React from 'react';
import {
  Pressable,
  StyleSheet,
  Text,
  View,
} from 'react-native';
import {useNavigation} from '@react-navigation/native';

import {solmateColors} from '../theme/colors';

export type CustomerBottomNavTab =
  | 'Home'
  | 'Quotation'
  | 'Services'
  | 'Tracking'
  | 'Profile';

type NavItem = {
  key: CustomerBottomNavTab;
  label: string;
  icon: string;
  routeName: string;
};

const NAV_ITEMS: NavItem[] = [
  {key: 'Home', label: 'Home', icon: '\uD83C\uDFE0', routeName: 'Home'},
  {
    key: 'Quotation',
    label: 'Quotation',
    icon: '\uD83D\uDCCB',
    routeName: 'QuotationList',
  },
  {
    key: 'Services',
    label: 'Services',
    icon: '\u2699\uFE0F',
    routeName: 'ServicesHome',
  },
  {
    key: 'Tracking',
    label: 'Tracking',
    icon: '\uD83D\uDCCD',
    routeName: 'TrackingHub',
  },
  {
    key: 'Profile',
    label: 'Profile',
    icon: '\uD83D\uDC64',
    routeName: 'CustomerSettings',
  },
];

type CustomerBottomNavProps = {
  activeTab: CustomerBottomNavTab;
};

export default function CustomerBottomNav({
  activeTab,
}: CustomerBottomNavProps) {
  const navigation = useNavigation<any>();

  return (
    <View style={styles.bottomNav}>
      {NAV_ITEMS.map(item => {
        const isActive = item.key === activeTab;

        return (
          <Pressable
            key={item.key}
            onPress={() => {
              if (!isActive) {
                navigation.navigate(item.routeName);
              }
            }}
            style={[styles.navItem, isActive && styles.navItemActive]}>
            <Text style={isActive ? styles.navIconActive : styles.navIcon}>
              {item.icon}
            </Text>
            <Text style={isActive ? styles.navLabelActive : styles.navLabel}>
              {item.label}
            </Text>
          </Pressable>
        );
      })}
    </View>
  );
}

const styles = StyleSheet.create({
  bottomNav: {
    flexDirection: 'row',
    justifyContent: 'space-around',
    backgroundColor: solmateColors.white,
    borderRadius: 22,
    paddingVertical: 8,
    paddingHorizontal: 6,
    borderWidth: 1,
    borderColor: solmateColors.border,
    shadowColor: '#8a9bbd',
    shadowOffset: {width: 0, height: -2},
    shadowOpacity: 0.08,
    shadowRadius: 8,
    elevation: 4,
  },
  navItem: {
    alignItems: 'center',
    paddingHorizontal: 10,
    paddingVertical: 8,
    borderRadius: 16,
  },
  navItemActive: {
    backgroundColor: solmateColors.warningSoft,
    borderWidth: 1,
    borderColor: 'rgba(244, 208, 0, 0.38)',
  },
  navIcon: {
    fontSize: 20,
    marginBottom: 2,
  },
  navIconActive: {
    fontSize: 20,
    marginBottom: 2,
    color: solmateColors.accentStrong,
  },
  navLabel: {
    fontSize: 11,
    color: solmateColors.muted,
    fontWeight: '600',
  },
  navLabelActive: {
    fontSize: 11,
    color: solmateColors.navy,
    fontWeight: '700',
  },
});