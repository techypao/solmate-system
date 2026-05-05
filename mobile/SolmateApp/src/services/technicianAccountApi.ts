import {apiPut} from './api';

export type TechnicianAccountUser = {
  id: number;
  name?: string | null;
  first_name?: string | null;
  last_name?: string | null;
  email?: string | null;
  role?: string | null;
  profile_picture?: string | null;
};

export type UpdateTechnicianAccountPayload = {
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
