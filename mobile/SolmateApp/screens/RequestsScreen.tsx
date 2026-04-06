import React from 'react';
import {StyleSheet, View} from 'react-native';

import {AppButton, AppCard} from '../components';

export default function RequestsScreen({ navigation }: any) {
  return (
    <View style={styles.container}>
      <AppCard title="Requests">
        <AppButton
          title="Back to Home"
          variant="outline"
          onPress={() => navigation.navigate('Home')}
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
});
