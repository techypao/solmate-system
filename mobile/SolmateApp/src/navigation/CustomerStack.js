// src/navigation/CustomerStack.js
import React from 'react';
import {createNativeStackNavigator} from '@react-navigation/native-stack';

import HomeScreen from '../../screens/HomeScreen';
import QuotationDetailScreen from '../../screens/QuotationDetailScreen';
import QuotationListScreen from '../../screens/QuotationListScreen';
import QuotationScreen from '../../screens/QuotationScreen';
import RequestsScreen from '../../screens/RequestsScreen';

const Stack = createNativeStackNavigator();

export default function CustomerStack() {
  return (
    // A stack navigator is a good fit here because each screen pushes forward
    // to the next one and React Navigation automatically gives us a back button.
    <Stack.Navigator
      initialRouteName="Home"
      screenOptions={{
        headerBackTitle: 'Back',
      }}>
      {/* Main customer entry screen */}
      <Stack.Screen
        name="Home"
        component={HomeScreen}
        options={{title: 'Customer Dashboard'}}
      />

      {/* Quotation creation form */}
      <Stack.Screen
        name="Quotations"
        component={QuotationScreen}
        options={{title: 'Create Quotation'}}
      />

      {/* Customer's list of submitted quotations */}
      <Stack.Screen
        name="QuotationList"
        component={QuotationListScreen}
        options={{title: 'My Quotations'}}
      />

      {/* Single quotation detail page */}
      <Stack.Screen
        name="QuotationDetail"
        component={QuotationDetailScreen}
        options={{title: 'Quotation Details'}}
      />

      <Stack.Screen name="Requests" component={RequestsScreen} />
    </Stack.Navigator>
  );
}
