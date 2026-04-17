// src/navigation/TechnicianStack.js
import React from 'react';
import {createNativeStackNavigator} from '@react-navigation/native-stack';

import AssignedTasksScreen from '../../screens/AssignedTasksScreen';
import FinalQuotationScreen from '../../screens/FinalQuotationScreen';
import QuotationDetailScreen from '../../screens/QuotationDetailScreen';
import RequestDetailsScreen from '../../screens/RequestDetailsScreen';
import ServiceRequestDetailScreen from '../../screens/ServiceRequestDetailScreen';
import ServiceRequestListScreen from '../../screens/ServiceRequestListScreen';
import TechnicianDashboardScreen from '../../screens/TechnicianDashboardScreen';
import TechnicianNotificationsScreen from '../../screens/TechnicianNotificationsScreen';
import TechnicianSettingsScreen from '../../screens/TechnicianSettingsScreen';

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
        name="TechnicianSettings"
        component={TechnicianSettingsScreen}
        options={{title: 'Settings'}}
      />
      <Stack.Screen
        name="TechnicianNotifications"
        component={TechnicianNotificationsScreen}
        options={{title: 'Notifications'}}
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
      <Stack.Screen
        name="TechnicianQuotationDetail"
        component={QuotationDetailScreen}
        options={{title: 'Final Quotation Details'}}
      />
      <Stack.Screen
        name="TechnicianServiceRequestDetail"
        component={ServiceRequestDetailScreen}
        initialParams={{mode: 'technician'}}
        options={{title: 'Service Request Details'}}
      />
    </Stack.Navigator>
  );
}
