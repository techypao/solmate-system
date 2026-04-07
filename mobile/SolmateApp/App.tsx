import React, {useContext, useEffect} from 'react';
import {ActivityIndicator, Alert, View} from 'react-native';
import {NavigationContainer} from '@react-navigation/native';

import {AuthProvider, AuthContext} from './src/context/AuthContext';

import AuthStack from './src/navigation/AuthStack';
import CustomerStack from './src/navigation/CustomerStack';
import TechnicianStack from './src/navigation/TechnicianStack';

function AppNavigator() {
  const {user, loading, logout} = useContext(AuthContext);

  useEffect(() => {
    if (user?.role === 'admin') {
      Alert.alert('Access Denied', 'Admin access is available on web only.');
      logout();
    }
  }, [user, logout]);

  if (loading) {
    return (
      <View style={{flex: 1, justifyContent: 'center', alignItems: 'center'}}>
        <ActivityIndicator size="large" />
      </View>
    );
  }

  return (
    <NavigationContainer>
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

export default function App() {
  return (
    <AuthProvider>
      <AppNavigator />
    </AuthProvider>
  );
}