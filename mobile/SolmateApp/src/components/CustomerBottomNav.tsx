import React from 'react';
import {
  Pressable,
  StyleSheet,
  Text,
  View,
} from 'react-native';
import {useNavigation} from '@react-navigation/native';
import Icon from 'react-native-vector-icons/MaterialCommunityIcons';

import {solmateColors} from '../theme/colors';

const ICON_COLOR = '#1d2f6d';

export type CustomerBottomNavTab =
  | 'Home'
  | 'Quotations'
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
  {key: 'Home', label: 'Home', icon: 'home-outline', routeName: 'Home'},
  {
    key: 'Quotations',
    label: 'Quotations',
    icon: 'file-document-outline',
    routeName: 'QuotationHub',
  },
  {
    key: 'Services',
    label: 'Services',
    icon: 'hammer-wrench',
    routeName: 'ServicesHome',
  },
  {
    key: 'Tracking',
    label: 'Tracking',
    icon: 'map-marker-outline',
    routeName: 'TrackingHub',
  },
  {
    key: 'Profile',
    label: 'Profile',
    icon: 'account-outline',
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
            <Icon
              name={item.icon}
              size={22}
              color={isActive ? ICON_COLOR : '#9AAABE'}
              style={styles.navIcon}
            />
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
    position: 'absolute',
    bottom: 16,
    left: 16,
    right: 16,
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
    flex: 1,
    alignItems: 'center',
    paddingHorizontal: 4,
    paddingVertical: 8,
    borderRadius: 16,
  },
  navItemActive: {
    backgroundColor: solmateColors.warningSoft,
    borderWidth: 1,
    borderColor: 'rgba(245, 192, 0, 0.38)',
  },
  navIcon: {
    marginBottom: 2,
  },
  navIconActive: {},
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
