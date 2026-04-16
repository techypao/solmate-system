// src/navigation/CustomerStack.js
import React from 'react';
import { createNativeStackNavigator } from '@react-navigation/native-stack';

import HomeScreen from '../../screens/HomeScreen';
import CustomerSettingsScreen from '../../screens/CustomerSettingsScreen';
import FinalQuotationViewScreen from '../../screens/FinalQuotationViewScreen';
import InspectionRequestDetailScreen from '../../screens/InspectionRequestDetailScreen';
import InspectionRequestListScreen from '../../screens/InspectionRequestListScreen';
import InspectionRequestScreen from '../../screens/InspectionRequestScreen';
import QuotationDetailScreen from '../../screens/QuotationDetailScreen';
import QuotationListScreen from '../../screens/QuotationListScreen';
import QuotationScreen from '../../screens/QuotationScreen';
import ServiceRequestDetailScreen from '../../screens/ServiceRequestDetailScreen';
import ServiceRequestListScreen from '../../screens/ServiceRequestListScreen';
import ServiceRequestScreen from '../../screens/ServiceRequestScreen';

const Stack = createNativeStackNavigator();

export default function CustomerStack() {
  return (
    // A stack navigator is a good fit here because each screen pushes forward
    // to the next one and React Navigation automatically gives us a back button.
    <Stack.Navigator
      initialRouteName="Home"
      screenOptions={{
        headerBackTitle: 'Back',
      }}
    >
      {/* Main customer entry screen */}
      <Stack.Screen
        name="Home"
        component={HomeScreen}
        options={{ title: 'Customer Dashboard' }}
      />

      <Stack.Screen
        name="CustomerSettings"
        component={CustomerSettingsScreen}
        options={{ title: 'Settings' }}
      />

      {/* Quotation creation form */}
      <Stack.Screen
        name="Quotations"
        component={QuotationScreen}
        options={{ title: 'Create Initial Quotation' }}
      />

      {/* Customer's list of submitted quotations */}
      <Stack.Screen
        name="QuotationList"
        component={QuotationListScreen}
        options={{ title: 'My Quotations' }}
      />

      <Stack.Screen
        name="InspectionRequest"
        component={InspectionRequestScreen}
        options={{ title: 'Request Inspection' }}
      />

      <Stack.Screen
        name="InspectionRequestList"
        component={InspectionRequestListScreen}
        options={{ title: 'My Inspection Requests' }}
      />

      <Stack.Screen
        name="InspectionRequestDetail"
        component={InspectionRequestDetailScreen}
        options={{ title: 'Inspection Request Details' }}
      />

      <Stack.Screen
        name="FinalQuotationView"
        component={FinalQuotationViewScreen}
        options={{ title: 'Final Quotation' }}
      />

      <Stack.Screen
        name="ServiceRequest"
        component={ServiceRequestScreen}
        options={{ title: 'Request Service' }}
      />

      <Stack.Screen
        name="ServiceRequestList"
        component={ServiceRequestListScreen}
        options={{ title: 'My Service Requests' }}
      />

      <Stack.Screen
        name="ServiceRequestDetail"
        component={ServiceRequestDetailScreen}
        initialParams={{ mode: 'customer' }}
        options={{ title: 'Service Request Details' }}
      />

      {/* Single quotation detail page */}
      <Stack.Screen
        name="QuotationDetail"
        component={QuotationDetailScreen}
        options={{ title: 'Quotation Details' }}
      />
    </Stack.Navigator>
  );
}
