import React from 'react';
import {StyleSheet, View} from 'react-native';

import {AppButton, AppCard} from '../components';

export default function HomeScreen({ navigation }: any) {
  return (
    <View style={styles.container}>
      <AppCard title="Home">
        <AppButton
          title="Go to Quotations"
          onPress={() => navigation.navigate('Quotations')}
        />

        <AppButton
          style={styles.buttonSpacing}
          title="Go to Requests"
          onPress={() => navigation.navigate('Requests')}
        />

        <AppButton
          style={styles.buttonSpacing}
          title="Logout"
          variant="outline"
          onPress={() => navigation.navigate('Login')}
        />
      </AppCard>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    backgroundColor: '#f3f4f6',
    flex: 1,
    justifyContent: 'center',
    padding: 20,
  },
  buttonSpacing: {
    marginTop: 12,
  },
});
