import React from 'react';

import TestimonyForm from '../src/components/TestimonyForm';

export default function CreateTestimonyScreen({navigation}: any) {
  return <TestimonyForm mode="create" navigation={navigation} />;
}
