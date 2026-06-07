import firebase from '@react-native-firebase/app';

let hasLoggedMissingDefaultApp = false;

export function hasDefaultFirebaseApp() {
  return firebase.apps.some(app => app.name === '[DEFAULT]');
}

export function canUseFirebaseMessaging() {
  if (hasDefaultFirebaseApp()) {
    return true;
  }

  if (!hasLoggedMissingDefaultApp) {
    console.warn(
      '[Firebase] Default app is not initialized. Add GoogleService-Info.plist to the iOS SolmateApp target before using Firebase Messaging.',
    );
    hasLoggedMissingDefaultApp = true;
  }

  return false;
}
