import {apiPut} from './api';

export type AccountUser = {
  id: number;
  name?: string | null;
  first_name?: string | null;
  last_name?: string | null;
  email?: string | null;
  role?: string | null;
  address?: string | null;
  contact_number?: string | null;
  landline_number?: string | null;
  profile_picture?: string | null;
};

export type UpdateCustomerAccountPayload = {
  email: string;
  address?: string;
  contact_number?: string;
  landline_number?: string;
};

export type UpdateCustomerPasswordPayload = {
  current_password: string;
  new_password: string;
  new_password_confirmation: string;
};

type UpdateCustomerAccountResponse = {
  message: string;
  user: AccountUser;
};

type UpdateCustomerPasswordResponse = {
  message: string;
};

export function updateCustomerAccount(payload: UpdateCustomerAccountPayload) {
  return apiPut<UpdateCustomerAccountResponse>('/customer/account', payload);
}

export function updateCustomerPassword(payload: UpdateCustomerPasswordPayload) {
  return apiPut<UpdateCustomerPasswordResponse>(
    '/customer/account/password',
    payload,
  );
}
