import React, {useContext, useEffect, useRef, useState} from 'react';
import {
  ActivityIndicator,
  Alert,
  PermissionsAndroid,
  Platform,
  View,
} from 'react-native';
import {NavigationContainer} from '@react-navigation/native';
import messaging from '@react-native-firebase/messaging';

import {AuthProvider, AuthContext} from './src/context/AuthContext';
import {saveDeviceToken} from './src/services/notificationApi';

import AuthStack from './src/navigation/AuthStack';
import CustomerStack from './src/navigation/CustomerStack';
import TechnicianStack from './src/navigation/TechnicianStack';
import {solmateColors, solmateNavigationTheme} from './src/theme/colors';

type AppNavigatorProps = {
  deviceToken: string | null;
};

function AppNavigator({deviceToken}: AppNavigatorProps) {
  const {user, token, loading, logout} = useContext(AuthContext);
  const lastSyncedDeviceTokenRef = useRef<string | null>(null);
  const pendingDeviceTokenRef = useRef<string | null>(null);
  const saveTokenRetryTimeoutRef = useRef<ReturnType<typeof setTimeout> | null>(null);

  useEffect(() => {
    if (user?.role === 'admin') {
      Alert.alert('Access Denied', 'Admin access is available on web only.');
      logout();
    }
  }, [user, logout]);

  useEffect(() => {
    if (!deviceToken) {
      return;
    }

    pendingDeviceTokenRef.current = deviceToken;

    console.log('[FCM] Device token available for backend sync:', {
      tokenPreview: `${deviceToken.slice(0, 12)}...`,
      userId: user?.id ?? null,
      hasAuthToken: Boolean(token),
    });
  }, [deviceToken, token, user?.id]);

  useEffect(() => {
    console.log('[FCM] Auth state updated for device token sync:', {
      loading,
      userId: user?.id ?? null,
      hasAuthToken: Boolean(token),
      hasPendingDeviceToken: Boolean(pendingDeviceTokenRef.current),
    });

    if (pendingDeviceTokenRef.current && user?.id && token) {
      console.log('[FCM] Auth is ready. Pending device token sync will start now.');
    }
  }, [loading, token, user?.id]);

  useEffect(() => {
    const tokenToSync = pendingDeviceTokenRef.current ?? deviceToken;

    if (!tokenToSync) {
      console.log('[FCM] Device token sync is waiting for a device token.');
      return;
    }

    if (!user?.id || !token) {
      console.log('[FCM] Device token sync deferred until auth is ready:', {
        userId: user?.id ?? null,
        hasAuthToken: Boolean(token),
        tokenPreview: `${tokenToSync.slice(0, 12)}...`,
      });

      return;
    }

    const syncKey = `${user.id}:${tokenToSync}`;

    if (lastSyncedDeviceTokenRef.current === syncKey) {
      console.log('[FCM] Device token already synced for this session:', {
        userId: user.id,
      });

      return;
    }

    let isCancelled = false;

    const syncDeviceToken = async (attempt = 1) => {
      try {
        console.log('[FCM] Attempting to save device token:', {
          attempt,
          userId: user.id,
          hasAuthToken: token.length > 0,
          tokenPreview: `${tokenToSync.slice(0, 12)}...`,
        });

        const response = await saveDeviceToken(tokenToSync, token);

        if (isCancelled) {
          return;
        }

        lastSyncedDeviceTokenRef.current = syncKey;
        pendingDeviceTokenRef.current = null;
        console.log('[FCM] Device token saved to backend for user:', user.id, response);
      } catch (error) {
        if (isCancelled) {
          return;
        }

        console.warn('[FCM] Failed to save device token to backend:', error);

        if (attempt >= 3) {
          console.warn('[FCM] Exhausted device token sync retries for this session.', {
            userId: user.id,
          });
          return;
        }

        saveTokenRetryTimeoutRef.current = setTimeout(() => {
          syncDeviceToken(attempt + 1);
        }, attempt * 2000);
      }
    };

    syncDeviceToken();

    return () => {
      isCancelled = true;

      if (saveTokenRetryTimeoutRef.current) {
        clearTimeout(saveTokenRetryTimeoutRef.current);
        saveTokenRetryTimeoutRef.current = null;
      }
    };
  }, [deviceToken, token, user?.id]);

  if (loading) {
    return (
      <View
        style={{
          flex: 1,
          justifyContent: 'center',
          alignItems: 'center',
          backgroundColor: solmateColors.background,
        }}>
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
  const [deviceToken, setDeviceToken] = useState<string | null>(null);

  useEffect(() => {
    const unsubscribeForegroundMessages = messaging().onMessage(
      async remoteMessage => {
        console.log('[FCM] Foreground message received:', remoteMessage);
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
        setDeviceToken(nextDeviceToken);
        console.log('[FCM] Device token:', nextDeviceToken);
      } catch (error) {
        console.warn('[FCM] Failed to initialize messaging:', error);
      }
    };

    initializeFirebaseMessaging();

    return unsubscribeForegroundMessages;
  }, []);

  return (
    <AuthProvider>
      <AppNavigator deviceToken={deviceToken} />
    </AuthProvider>
  );
}