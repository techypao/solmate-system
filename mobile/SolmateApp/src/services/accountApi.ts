import {apiPut} from './api';

export type AccountUser = {
  id: number;
  name?: string | null;
  email?: string | null;
  role?: string | null;
  address?: string | null;
  contact_number?: string | null;
};

export type UpdateCustomerAccountPayload = {
  name: string;
  email: string;
  address?: string;
  contact_number?: string;
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
