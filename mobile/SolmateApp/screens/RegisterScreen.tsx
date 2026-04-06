import React, {useState} from 'react';
import {StyleSheet, View} from 'react-native';

import {AppButton, AppCard, AppInput} from '../components';

export default function RegisterScreen({ navigation }: any) {
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');

  return (
    <View style={styles.container}>
      <AppCard title="Register">
        <AppInput
          label="Full Name"
          onChangeText={setName}
          placeholder="Enter your full name"
          value={name}
        />

        <AppInput
          autoCapitalize="none"
          containerStyle={styles.inputSpacing}
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
          placeholder="Create a password"
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
          title="Back to Login"
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
  inputSpacing: {
    marginTop: 12,
  },
});
