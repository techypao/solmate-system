import {API_BASE_URL} from '../services/api';

const STORAGE_BASE_URL = API_BASE_URL.replace(/\/api\/?$/, '');

export function getProfilePictureUrl(path?: string | null) {
  const cleanPath = String(path || '').replace(/^\/+/, '');

  if (!cleanPath) {
    return null;
  }

  return `${STORAGE_BASE_URL}/storage/${cleanPath}`;
}

export function getUserInitial(name?: string | null, fallback = '?') {
  const trimmedName = String(name || '').trim();

  if (!trimmedName) {
    return fallback;
  }

  return trimmedName.charAt(0).toUpperCase();
}
