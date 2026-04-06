import React from 'react';
import {
  Pressable,
  StyleProp,
  StyleSheet,
  Text,
  TextStyle,
  ViewStyle,
} from 'react-native';

type AppButtonVariant = 'primary' | 'secondary' | 'outline';

type AppButtonProps = {
  title: string;
  onPress?: () => void;
  variant?: AppButtonVariant;
  disabled?: boolean;
  style?: StyleProp<ViewStyle>;
  textStyle?: StyleProp<TextStyle>;
};

const buttonVariantStyles: Record<AppButtonVariant, ViewStyle> = {
  primary: {
    backgroundColor: '#2563eb',
    borderColor: '#2563eb',
  },
  secondary: {
    backgroundColor: '#e5e7eb',
    borderColor: '#e5e7eb',
  },
  outline: {
    backgroundColor: '#ffffff',
    borderColor: '#2563eb',
  },
};

const textVariantStyles: Record<AppButtonVariant, TextStyle> = {
  primary: {
    color: '#ffffff',
  },
  secondary: {
    color: '#111827',
  },
  outline: {
    color: '#2563eb',
  },
};

export default function AppButton({
  title,
  onPress,
  variant = 'primary',
  disabled = false,
  style,
  textStyle,
}: AppButtonProps) {
  return (
    <Pressable
      accessibilityRole="button"
      disabled={disabled}
      onPress={onPress}
      style={({pressed}) => [
        styles.button,
        buttonVariantStyles[variant],
        pressed && !disabled ? styles.pressed : null,
        disabled ? styles.disabled : null,
        style,
      ]}>
      <Text style={[styles.buttonText, textVariantStyles[variant], textStyle]}>
        {title}
      </Text>
    </Pressable>
  );
}

const styles = StyleSheet.create({
  button: {
    alignItems: 'center',
    borderRadius: 12,
    borderWidth: 1,
    minHeight: 48,
    justifyContent: 'center',
    paddingHorizontal: 16,
    paddingVertical: 12,
  },
  buttonText: {
    fontSize: 16,
    fontWeight: '600',
  },
  disabled: {
    opacity: 0.6,
  },
  pressed: {
    opacity: 0.85,
  },
});
