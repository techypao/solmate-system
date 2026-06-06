// src/navigation/CustomerStack.js
import React from 'react';
import { createNativeStackNavigator } from '@react-navigation/native-stack';

import HomeScreen from '../../screens/HomeScreen';
import CustomerSettingsScreen from '../../screens/CustomerSettingsScreen';
import CreateTestimonyScreen from '../../screens/CreateTestimonyScreen';
import CustomerNotificationsScreen from '../../screens/CustomerNotificationsScreen';
import EditTestimonyScreen from '../../screens/EditTestimonyScreen';
import ChatbotScreen from '../../screens/ChatbotScreen';
import InspectionRequestDetailScreen from '../../screens/InspectionRequestDetailScreen';
import InspectionHubScreen from '../../screens/InspectionHubScreen';
import InspectionRequestListScreen from '../../screens/InspectionRequestListScreen';
import InspectionRequestScreen from '../../screens/InspectionRequestScreen';
import InstallationRequestScreen from '../../screens/InstallationRequestScreen';
import MyTestimoniesScreen from '../../screens/MyTestimoniesScreen';
import ServicesHubScreen from '../../screens/ServicesHubScreen';
import ServiceRequestDetailScreen from '../../screens/ServiceRequestDetailScreen';
import ServiceRequestListScreen from '../../screens/ServiceRequestListScreen';
import ServiceRequestScreen from '../../screens/ServiceRequestScreen';
import TrackingHubScreen from '../../screens/TrackingHubScreen';
import QuotationHubScreen from '../../screens/QuotationHubScreen';
import QuotationScreen from '../../screens/QuotationScreen';
import QuotationListScreen from '../../screens/QuotationListScreen';
import QuotationDetailScreen from '../../screens/QuotationDetailScreen';
import FinalQuotationViewScreen from '../../screens/FinalQuotationViewScreen';
import {solmateColors} from '../theme/colors';

const Stack = createNativeStackNavigator();

export default function CustomerStack() {
  return (
    // A stack navigator is a good fit here because each screen pushes forward
    // to the next one and React Navigation automatically gives us a back button.
    <Stack.Navigator
      initialRouteName="Home"
      screenOptions={{
        headerBackTitle: 'Back',
        headerTitleAlign: 'center',
        headerShadowVisible: false,
        headerStyle: {
          backgroundColor: solmateColors.white,
        },
        headerTintColor: solmateColors.navy,
        headerTitleStyle: {
          color: solmateColors.text,
          fontWeight: '700',
        },
        contentStyle: {
          backgroundColor: solmateColors.background,
        },
      }}
    >
      {/* Main customer entry screen */}
      <Stack.Screen
        name="Home"
        component={HomeScreen}
        options={{ headerShown: false }}
      />

      <Stack.Screen
        name="CustomerSettings"
        component={CustomerSettingsScreen}
        options={{ headerShown: false }}
      />

      <Stack.Screen
        name="CustomerNotifications"
        component={CustomerNotificationsScreen}
        options={{ headerShown: false }}
      />

      <Stack.Screen
        name="Chatbot"
        component={ChatbotScreen}
        options={{ headerShown: false }}
      />

      <Stack.Screen
        name="InspectionRequest"
        component={InspectionRequestScreen}
        options={{ headerShown: false }}
      />

      <Stack.Screen
        name="InspectionHome"
        component={InspectionHubScreen}
        options={{ headerShown: false }}
      />

      <Stack.Screen
        name="InstallationRequest"
        component={InstallationRequestScreen}
        options={{ headerShown: false }}
      />

      <Stack.Screen
        name="ServicesHome"
        component={ServicesHubScreen}
        options={{ headerShown: false }}
      />

      <Stack.Screen
        name="InspectionRequestList"
        component={InspectionRequestListScreen}
        options={{ headerShown: false }}
      />

      <Stack.Screen
        name="InspectionRequestDetail"
        component={InspectionRequestDetailScreen}
        options={{ headerShown: false }}
      />

      <Stack.Screen
        name="ServiceRequest"
        component={ServiceRequestScreen}
        options={{ headerShown: false }}
      />

      <Stack.Screen
        name="ServiceRequestList"
        component={ServiceRequestListScreen}
        options={{ headerShown: false }}
      />

      <Stack.Screen
        name="TrackingHub"
        component={TrackingHubScreen}
        options={{ headerShown: false }}
      />

      <Stack.Screen
        name="MyTestimonies"
        component={MyTestimoniesScreen}
        options={{ headerShown: false }}
      />

      <Stack.Screen
        name="CreateTestimony"
        component={CreateTestimonyScreen}
        options={{ headerShown: false }}
      />

      <Stack.Screen
        name="EditTestimony"
        component={EditTestimonyScreen}
        options={{ headerShown: false }}
      />

      <Stack.Screen
        name="ServiceRequestDetail"
        component={ServiceRequestDetailScreen}
        initialParams={{ mode: 'customer' }}
        options={{ headerShown: false }}
      />

      <Stack.Screen
        name="QuotationHub"
        component={QuotationHubScreen}
        options={{ headerShown: false }}
      />

      <Stack.Screen
        name="QuotationEstimate"
        component={QuotationScreen}
        options={{ headerShown: false }}
      />

      <Stack.Screen
        name="QuotationList"
        component={QuotationListScreen}
        options={{ headerShown: false }}
      />

      <Stack.Screen
        name="QuotationDetail"
        component={QuotationDetailScreen}
        options={{ headerShown: false }}
      />

      <Stack.Screen
        name="FinalQuotationView"
        component={FinalQuotationViewScreen}
        options={{ headerShown: false }}
      />
    </Stack.Navigator>
  );
}
