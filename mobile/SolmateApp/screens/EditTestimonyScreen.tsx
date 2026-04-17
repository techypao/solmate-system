import React from 'react';
import {StyleSheet, Text, View} from 'react-native';

import TestimonyForm from '../src/components/TestimonyForm';

export default function EditTestimonyScreen({navigation, route}: any) {
  const testimony = route?.params?.testimony || null;

  if (!testimony) {
    return (
      <View style={styles.emptyState}>
        <Text style={styles.emptyText}>
          No testimony data was provided for editing.
        </Text>
      </View>
    );
  }

  return (
    <TestimonyForm
      initialTestimony={testimony}
      mode="edit"
      navigation={navigation}
    />
  );
}

const styles = StyleSheet.create({
  emptyState: {
    alignItems: 'center',
    backgroundColor: '#f5f7fb',
    flex: 1,
    justifyContent: 'center',
    padding: 24,
  },
  emptyText: {
    color: '#0f172a',
    fontSize: 16,
    fontWeight: '600',
    textAlign: 'center',
  },
});
