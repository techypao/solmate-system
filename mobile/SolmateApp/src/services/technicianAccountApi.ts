import {apiPut} from './api';

export type TechnicianAccountUser = {
  id: number;
  name?: string | null;
  email?: string | null;
  role?: string | null;
};

export type UpdateTechnicianAccountPayload = {
  name: string;
  email: string;
};

export type UpdateTechnicianPasswordPayload = {
  current_password: string;
  new_password: string;
  new_password_confirmation: string;
};

type UpdateTechnicianAccountResponse = {
  message: string;
  user: TechnicianAccountUser;
};

type UpdateTechnicianPasswordResponse = {
  message: string;
};

export function updateTechnicianAccount(
  payload: UpdateTechnicianAccountPayload,
) {
  return apiPut<UpdateTechnicianAccountResponse>(
    '/technician/account',
    payload,
  );
}

export function updateTechnicianPassword(
  payload: UpdateTechnicianPasswordPayload,
) {
  return apiPut<UpdateTechnicianPasswordResponse>(
    '/technician/account/password',
    payload,
  );
}
