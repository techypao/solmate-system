// src/navigation/TechnicianStack.js
import React from 'react';
import {createNativeStackNavigator} from '@react-navigation/native-stack';

import AssignedTasksScreen from '../../screens/AssignedTasksScreen';
import FinalQuotationScreen from '../../screens/FinalQuotationScreen';
import RequestDetailsScreen from '../../screens/RequestDetailsScreen';
import ServiceRequestListScreen from '../../screens/ServiceRequestListScreen';
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
        name="AssignedInspectionRequests"
        component={AssignedTasksScreen}
        options={{title: 'Assigned Inspection Requests'}}
      />
      <Stack.Screen
        name="TechnicianServiceRequests"
        component={ServiceRequestListScreen}
        initialParams={{mode: 'technician'}}
        options={{title: 'Service Requests'}}
      />
      <Stack.Screen
        name="InspectionDetails"
        component={RequestDetailsScreen}
        options={{title: 'Inspection Details'}}
      />
      <Stack.Screen
        name="FinalQuotationForm"
        component={FinalQuotationScreen}
        options={{title: 'Final Quotation Form'}}
      />
    </Stack.Navigator>
  );
}
