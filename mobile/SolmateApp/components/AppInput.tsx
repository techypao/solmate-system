import React from 'react';
import {
  StyleProp,
  StyleSheet,
  Text,
  TextInput,
  TextInputProps,
  View,
  ViewStyle,
} from 'react-native';
import {solmateColors} from '../src/theme/colors';

type AppInputProps = TextInputProps & {
  label?: string;
  containerStyle?: StyleProp<ViewStyle>;
};

export default function AppInput({
  label,
  containerStyle,
  style,
  placeholderTextColor = solmateColors.mutedSoft,
  ...props
}: AppInputProps) {
  return (
    <View style={[styles.container, containerStyle]}>
      {label ? <Text style={styles.label}>{label}</Text> : null}
      <TextInput
        placeholderTextColor={placeholderTextColor}
        style={[styles.input, style]}
        {...props}
      />
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    width: '100%',
  },
  input: {
    backgroundColor: solmateColors.background,
    borderColor: solmateColors.border,
    borderRadius: 12,
    borderWidth: 1,
    color: solmateColors.text,
    fontSize: 16,
    minHeight: 48,
    paddingHorizontal: 14,
    paddingVertical: 12,
  },
  label: {
    color: solmateColors.navy,
    fontSize: 14,
    fontWeight: '600',
    marginBottom: 8,
  },
});
