import React, {ReactNode} from 'react';
import {
  StyleProp,
  StyleSheet,
  Text,
  View,
  ViewStyle,
} from 'react-native';
import {solmateColors} from '../src/theme/colors';

type AppCardProps = {
  title?: string;
  children: ReactNode;
  style?: StyleProp<ViewStyle>;
};

export default function AppCard({title, children, style}: AppCardProps) {
  return (
    <View style={[styles.card, style]}>
      {title ? <Text style={styles.title}>{title}</Text> : null}
      {children}
    </View>
  );
}

const styles = StyleSheet.create({
  card: {
    backgroundColor: solmateColors.white,
    borderColor: solmateColors.border,
    borderRadius: 18,
    borderWidth: 1,
    padding: 20,
    shadowColor: solmateColors.shadow,
    shadowOffset: {
      width: 0,
      height: 10,
    },
    shadowOpacity: 0.08,
    shadowRadius: 20,
    elevation: 3,
  },
  title: {
    color: solmateColors.text,
    fontSize: 24,
    fontWeight: '700',
    marginBottom: 20,
    textAlign: 'center',
  },
});
