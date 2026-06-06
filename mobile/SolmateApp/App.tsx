import React, { useContext, useEffect } from 'react';
import {
  ActivityIndicator,
  Alert,
  PermissionsAndroid,
  Platform,
  View,
} from 'react-native';
import { NavigationContainer } from '@react-navigation/native';
import messaging from '@react-native-firebase/messaging';

import { AuthProvider, AuthContext } from './src/context/AuthContext';

import AuthStack from './src/navigation/AuthStack';
import CustomerStack from './src/navigation/CustomerStack';
import TechnicianStack from './src/navigation/TechnicianStack';
import { solmateColors, solmateNavigationTheme } from './src/theme/colors';

function AppNavigator() {
  const { user, token, loading, logout } = useContext(AuthContext);

  useEffect(() => {
    if (user?.role === 'admin') {
      Alert.alert('Access Denied', 'Admin access is available on web only.');
      logout();
    }
  }, [user, logout]);

  useEffect(() => {
    console.log('[Auth] Navigation auth state updated:', {
      loading,
      userId: user?.id ?? null,
      hasAuthToken: Boolean(token),
    });
  }, [loading, token, user?.id]);

  if (loading) {
    return (
      <View
        style={{
          flex: 1,
          justifyContent: 'center',
          alignItems: 'center',
          backgroundColor: solmateColors.background,
        }}
      >
        <ActivityIndicator size="large" color={solmateColors.accentStrong} />
      </View>
    );
  }

  return (
    <NavigationContainer theme={solmateNavigationTheme}>
      {!user ? (
        <AuthStack />
      ) : user.role === 'customer' ? (
        <CustomerStack />
      ) : user.role === 'technician' ? (
        <TechnicianStack />
      ) : (
        <AuthStack />
      )}
    </NavigationContainer>
  );
}

async function requestNotificationPermission() {
  if (Platform.OS === 'android' && Platform.Version >= 33) {
    const permissionStatus = await PermissionsAndroid.request(
      PermissionsAndroid.PERMISSIONS.POST_NOTIFICATIONS,
    );

    if (permissionStatus !== PermissionsAndroid.RESULTS.GRANTED) {
      return false;
    }
  }

  const authorizationStatus = await messaging().requestPermission();

  return (
    authorizationStatus === messaging.AuthorizationStatus.AUTHORIZED ||
    authorizationStatus === messaging.AuthorizationStatus.PROVISIONAL
  );
}

export default function App() {
  useEffect(() => {
    const unsubscribeForegroundMessages = messaging().onMessage(
      async remoteMessage => {
        console.log('[FCM] Foreground message received:', remoteMessage);

        const title =
          remoteMessage.notification?.title ||
          remoteMessage.data?.title?.toString() ||
          'New notification';
        const body =
          remoteMessage.notification?.body ||
          remoteMessage.data?.body?.toString() ||
          remoteMessage.data?.message?.toString() ||
          'You have a new update.';

        Alert.alert(title, body);
      },
    );

    const initializeFirebaseMessaging = async () => {
      try {
        await messaging().registerDeviceForRemoteMessages();

        const permissionGranted = await requestNotificationPermission();

        if (!permissionGranted) {
          console.log('[FCM] Notification permission not granted.');
          return;
        }

        const nextDeviceToken = await messaging().getToken();
        console.log(
          '[FCM] Device token ready:',
          `${nextDeviceToken.slice(0, 12)}...`,
        );
      } catch (error) {
        console.warn('[FCM] Failed to initialize messaging:', error);
      }
    };

    initializeFirebaseMessaging();

    return unsubscribeForegroundMessages;
  }, []);

  return (
    <AuthProvider>
      <AppNavigator />
    </AuthProvider>
  );
}
