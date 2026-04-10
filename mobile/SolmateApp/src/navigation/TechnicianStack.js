// src/navigation/TechnicianStack.js
import React from 'react';
import {createNativeStackNavigator} from '@react-navigation/native-stack';

import AssignedTasksScreen from '../../screens/AssignedTasksScreen';
import FinalQuotationScreen from '../../screens/FinalQuotationScreen';
import RequestDetailsScreen from '../../screens/RequestDetailsScreen';
import TechnicianDashboardScreen from '../../screens/TechnicianDashboardScreen';

const Stack = createNativeStackNavigator();

export default function TechnicianStack() {
  return (
    <Stack.Navigator
      initialRouteName="TechnicianDashboard"
      screenOptions={{
        headerBackTitle: 'Back',
      }}>
      <Stack.Screen
        name="TechnicianDashboard"
        component={TechnicianDashboardScreen}
        options={{title: 'Technician Dashboard'}}
      />
      <Stack.Screen
        name="AssignedTasks"
        component={AssignedTasksScreen}
        options={{title: 'Assigned Tasks'}}
      />
      <Stack.Screen
        name="RequestDetails"
        component={RequestDetailsScreen}
        options={{title: 'Request Details'}}
      />
      <Stack.Screen
        name="FinalQuotation"
        component={FinalQuotationScreen}
        options={{title: 'Final Quotation'}}
      />
    </Stack.Navigator>
  );
}
