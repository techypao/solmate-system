import React, {useState} from 'react';
import {StyleSheet, View} from 'react-native';

import {AppButton, AppCard, AppInput} from '../components';

export default function LoginScreen({ navigation }: any) {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');

  return (
    <View style={styles.container}>
      <AppCard title="Login">
        <AppInput
          autoCapitalize="none"
          keyboardType="email-address"
          label="Email"
          onChangeText={setEmail}
          placeholder="Enter your email"
          value={email}
        />

        <AppInput
          containerStyle={styles.inputSpacing}
          label="Password"
          onChangeText={setPassword}
          placeholder="Enter your password"
          secureTextEntry
          value={password}
        />

        <AppButton
          style={styles.buttonSpacing}
          title="Go to Home"
          onPress={() => navigation.navigate('Home')}
        />

        <AppButton
          style={styles.buttonSpacing}
          title="Go to Register"
          variant="outline"
          onPress={() => navigation.navigate('Register')}
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
  inputSpacing: {
    marginTop: 12,
  },
});
