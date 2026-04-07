// src/navigation/CustomerStack.js
import React from 'react';
import {createNativeStackNavigator} from '@react-navigation/native-stack';

import HomeScreen from '../../screens/HomeScreen';
import QuotationsScreen from '../../screens/QuotationsScreen';
import RequestsScreen from '../../screens/RequestsScreen';

const Stack = createNativeStackNavigator();

export default function CustomerStack() {
  return (
    <Stack.Navigator initialRouteName="Home">
      <Stack.Screen name="Home" component={HomeScreen} />
      <Stack.Screen name="Quotations" component={QuotationsScreen} />
      <Stack.Screen name="Requests" component={RequestsScreen} />
    </Stack.Navigator>
  );
}