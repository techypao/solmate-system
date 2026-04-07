// src/navigation/TechnicianStack.js
import React from 'react';
import {createNativeStackNavigator} from '@react-navigation/native-stack';

import TechnicianDashboard from '../../screens/TechnicianDashboard';

const Stack = createNativeStackNavigator();

export default function TechnicianStack() {
  return (
    <Stack.Navigator initialRouteName="TechnicianDashboard">
      <Stack.Screen
        name="TechnicianDashboard"
        component={TechnicianDashboard}
      />
    </Stack.Navigator>
  );
}