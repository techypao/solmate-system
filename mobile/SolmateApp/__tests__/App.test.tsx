/**
 * @format
 */

import React from 'react';
import ReactTestRenderer from 'react-test-renderer';

jest.mock('@react-navigation/native', () => {
  const React = require('react');

  return {
    NavigationContainer: ({children}: any) =>
      React.createElement(React.Fragment, null, children),
  };
});

jest.mock('@react-navigation/native-stack', () => {
  const React = require('react');

  return {
    createNativeStackNavigator: () => ({
      Navigator: ({children}: any) =>
        React.createElement(React.Fragment, null, children),
      Screen: () => null,
    }),
  };
});

import App from '../App';

test('renders correctly', async () => {
  await ReactTestRenderer.act(() => {
    ReactTestRenderer.create(<App />);
  });
});
