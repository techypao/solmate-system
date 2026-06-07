import React, { createContext, useCallback, useEffect, useState } from 'react';
import { PermissionsAndroid, Platform } from 'react-native';
import messaging from '@react-native-firebase/messaging';
import {
  apiGet,
  apiPost,
  getStoredToken,
  removeStoredToken,
  saveStoredToken,
  setSessionToken,
} from '../services/api';
import {
  removeDeviceToken,
  saveDeviceToken,
} from '../services/notificationApi';
import { canUseFirebaseMessaging } from '../services/firebaseApp';

export const AuthContext = createContext();

async function requestNotificationPermission() {
  if (!canUseFirebaseMessaging()) {
    return false;
  }

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

export const AuthProvider = ({ children }) => {
  const [user, setUser] = useState(null);
  const [token, setToken] = useState(null);
  const [loading, setLoading] = useState(true);
  const [authErrorMessage, setAuthErrorMessage] = useState('');

  const syncFcmToken = useCallback(async authToken => {
    if (!canUseFirebaseMessaging()) {
      return;
    }

    try {
      await messaging().registerDeviceForRemoteMessages();

      const permissionGranted = await requestNotificationPermission();

      if (!permissionGranted) {
        console.log('[FCM] Notification permission not granted.');
        return;
      }

      const fcmToken = await messaging().getToken();

      if (!fcmToken) {
        console.log('[FCM] Firebase did not return a device token.');
        return;
      }

      await saveDeviceToken(fcmToken, authToken);
      console.log('[FCM] Device token synced for the authenticated user.');
    } catch (error) {
      console.warn(
        '[FCM] Failed to sync device token:',
        error?.message || error,
      );
    }
  }, []);

  const fetchUser = useCallback(
    async authToken => {
      try {
        setSessionToken(authToken);
        const userData = await apiGet('/user');
        setUser(userData);
        setToken(authToken);
        setAuthErrorMessage('');
        await syncFcmToken(authToken);
      } catch (error) {
        console.log('Fetch user error:', error?.message || error);
        setSessionToken(null);
        await removeStoredToken();
        setUser(null);
        setToken(null);

        if (error?.status === 403 && typeof error?.message === 'string') {
          setAuthErrorMessage(error.message);
        }

        throw error;
      }
    },
    [syncFcmToken],
  );

  const checkLoginStatus = useCallback(async () => {
    try {
      const storedToken = await getStoredToken();

      if (storedToken) {
        await fetchUser(storedToken);
      } else {
        setSessionToken(null);
      }
    } catch (error) {
      console.log('Check login error:', error);
    } finally {
      setLoading(false);
    }
  }, [fetchUser]);

  const login = async (newToken, options = {}) => {
    const { rememberSession = true } = options;

    try {
      setAuthErrorMessage('');

      if (rememberSession) {
        await saveStoredToken(newToken);
      } else {
        await removeStoredToken();
      }

      await fetchUser(newToken);
    } catch (error) {
      console.log('Login context error:', error);
      throw error;
    }
  };

  const refreshUser = useCallback(async () => {
    if (!token) return;
    try {
      const userData = await apiGet('/user');
      setUser(userData);
    } catch (error) {
      console.log('Refresh user error:', error?.message || error);
    }
  }, [token]);

  const logout = async () => {
    const activeToken = token || (await getStoredToken());

    try {
      if (activeToken) {
        setSessionToken(activeToken);
        await removeDeviceToken(activeToken);
        await apiPost('/logout', undefined, true, activeToken);
      }
    } catch (error) {
      console.log('Remote logout cleanup error:', error?.message || error);
    } finally {
      if (canUseFirebaseMessaging()) {
        try {
          await messaging().deleteToken();
        } catch (error) {
          console.log('FCM token delete error:', error?.message || error);
        }
      }

      setSessionToken(null);
      await removeStoredToken();
      setUser(null);
      setToken(null);
      setAuthErrorMessage('');
    }
  };

  const clearAuthError = () => {
    setAuthErrorMessage('');
  };

  useEffect(() => {
    checkLoginStatus();
  }, [checkLoginStatus]);

  useEffect(() => {
    if (!token) {
      return undefined;
    }

    if (!canUseFirebaseMessaging()) {
      return undefined;
    }

    return messaging().onTokenRefresh(async nextFcmToken => {
      try {
        await saveDeviceToken(nextFcmToken, token);
        console.log(
          '[FCM] Refreshed device token synced for the authenticated user.',
        );
      } catch (error) {
        console.warn(
          '[FCM] Failed to sync refreshed device token:',
          error?.message || error,
        );
      }
    });
  }, [token]);

  return (
    <AuthContext.Provider
      value={{
        user,
        token,
        loading,
        authErrorMessage,
        login,
        logout,
        setUser,
        refreshUser,
        clearAuthError,
      }}
    >
      {children}
    </AuthContext.Provider>
  );
};
