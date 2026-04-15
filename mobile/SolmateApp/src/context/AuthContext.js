import React, {createContext, useCallback, useEffect, useState} from 'react';
import {
  apiGet,
  getStoredToken,
  removeStoredToken,
  saveStoredToken,
} from '../services/api';

export const AuthContext = createContext();

export const AuthProvider = ({children}) => {
  const [user, setUser] = useState(null);
  const [token, setToken] = useState(null);
  const [loading, setLoading] = useState(true);

  const fetchUser = useCallback(async authToken => {
    try {
      const userData = await apiGet('/user');
      setUser(userData);
      setToken(authToken);
    } catch (error) {
      console.log('Fetch user error:', error?.message || error);
      await removeStoredToken();
      setUser(null);
      setToken(null);
    }
  }, []);

  const checkLoginStatus = useCallback(async () => {
    try {
      const storedToken = await getStoredToken();

      if (storedToken) {
        await fetchUser(storedToken);
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
      if (rememberSession) {
        await saveStoredToken(newToken);
      } else {
        await removeStoredToken();
      }

      await fetchUser(newToken);
    } catch (error) {
      console.log('Login context error:', error);
    }
  };

  const logout = async () => {
    try {
      await removeStoredToken();
      setUser(null);
      setToken(null);
    } catch (error) {
      console.log('Logout error:', error);
    }
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
        login,
        logout,
        setUser,
      }}>
      {children}
    </AuthContext.Provider>
  );
};
