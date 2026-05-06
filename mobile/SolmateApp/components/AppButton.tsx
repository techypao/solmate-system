import React from 'react';
import {
  Pressable,
  StyleProp,
  StyleSheet,
  Text,
  TextStyle,
  ViewStyle,
} from 'react-native';
import {solmateColors} from '../src/theme/colors';

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
    backgroundColor: solmateColors.primary,
    borderColor: solmateColors.primary,
    shadowColor: solmateColors.primaryHover,
    shadowOffset: {
      width: 0,
      height: 10,
    },
    shadowOpacity: 0.18,
    shadowRadius: 18,
    elevation: 4,
  },
  secondary: {
    backgroundColor: solmateColors.infoSoft,
    borderColor: solmateColors.accentSky,
  },
  outline: {
    backgroundColor: solmateColors.white,
    borderColor: solmateColors.accentStrong,
  },
};

const textVariantStyles: Record<AppButtonVariant, TextStyle> = {
  primary: {
    color: solmateColors.text,
  },
  secondary: {
    color: solmateColors.text,
  },
  outline: {
    color: solmateColors.accentStrong,
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
    borderRadius: 16,
    borderWidth: 1,
    minHeight: 54,
    justifyContent: 'center',
    paddingHorizontal: 18,
    paddingVertical: 14,
  },
  buttonText: {
    fontSize: 16,
    fontWeight: '700',
  },
  disabled: {
    opacity: 0.6,
  },
  pressed: {
    opacity: 0.85,
  },
});
