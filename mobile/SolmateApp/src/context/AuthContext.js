import React, {createContext, useEffect, useState} from 'react';
import AsyncStorage from '@react-native-async-storage/async-storage';
import axios from 'axios';

export const AuthContext = createContext();

export const AuthProvider = ({children}) => {
  const [user, setUser] = useState(null);
  const [token, setToken] = useState(null);
  const [loading, setLoading] = useState(true);

  const BASE_URL = 'http://10.0.2.2:8000';

  const fetchUser = async authToken => {
    try {
      const response = await axios.get(`${BASE_URL}/api/user`, {
        headers: {
          Authorization: `Bearer ${authToken}`,
          Accept: 'application/json',
        },
      });

      setUser(response.data);
      setToken(authToken);
    } catch (error) {
      console.log('Fetch user error:', error?.response?.data || error.message);
      await AsyncStorage.removeItem('token');
      setUser(null);
      setToken(null);
    }
  };

  const checkLoginStatus = async () => {
    try {
      const storedToken = await AsyncStorage.getItem('token');

      if (storedToken) {
        await fetchUser(storedToken);
      }
    } catch (error) {
      console.log('Check login error:', error);
    } finally {
      setLoading(false);
    }
  };

  const login = async newToken => {
    try {
      await AsyncStorage.setItem('token', newToken);
      await fetchUser(newToken);
    } catch (error) {
      console.log('Login context error:', error);
    }
  };

  const logout = async () => {
    try {
      await AsyncStorage.removeItem('token');
      setUser(null);
      setToken(null);
    } catch (error) {
      console.log('Logout error:', error);
    }
  };

  useEffect(() => {
    checkLoginStatus();
  }, []);

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