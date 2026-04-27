export const PASSWORD_REQUIREMENTS_TEXT =
  'Password must be at least 8 characters, include 1 uppercase letter, and 1 special character.';

export function getPasswordValidationError(password: string) {
  if (password.length < 8) {
    return 'Password must be at least 8 characters.';
  }

  if (!/[A-Z]/.test(password)) {
    return 'Password must contain at least one uppercase letter.';
  }

  if (!/[^A-Za-z0-9]/.test(password)) {
    return 'Password must contain at least one special character.';
  }

  return null;
}
