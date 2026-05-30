import React, {createContext, useCallback, useEffect, useState} from 'react';
import {
  apiGet,
  getStoredToken,
  removeStoredToken,
  saveStoredToken,
  setSessionToken,
} from '../services/api';

export const AuthContext = createContext();

export const AuthProvider = ({children}) => {
  const [user, setUser] = useState(null);
  const [token, setToken] = useState(null);
  const [loading, setLoading] = useState(true);
  const [authErrorMessage, setAuthErrorMessage] = useState('');

  const fetchUser = useCallback(async authToken => {
    try {
      setSessionToken(authToken);
      const userData = await apiGet('/user');
      setUser(userData);
      setToken(authToken);
      setAuthErrorMessage('');
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
  }, []);

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
    const {rememberSession = true} = options;

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
    try {
      setSessionToken(null);
      await removeStoredToken();
      setUser(null);
      setToken(null);
      setAuthErrorMessage('');
    } catch (error) {
      console.log('Logout error:', error);
    }
  };

  const clearAuthError = () => {
    setAuthErrorMessage('');
  };

  useEffect(() => {
    checkLoginStatus();
  }, [checkLoginStatus]);

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
      }}>
      {children}
    </AuthContext.Provider>
  );
};
