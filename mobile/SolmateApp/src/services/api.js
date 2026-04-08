import AsyncStorage from '@react-native-async-storage/async-storage';

export const BASE_URL = 'http://10.0.2.2:8000/api';
export const TOKEN_STORAGE_KEY = 'token';

export class ApiError extends Error {
  constructor(message, status = 0, data = null, errors = null) {
    super(message);
    this.name = 'ApiError';
    this.status = status;
    this.data = data;
    this.errors = errors;
  }
}

export async function getStoredToken() {
  return AsyncStorage.getItem(TOKEN_STORAGE_KEY);
}

export async function saveStoredToken(token) {
  return AsyncStorage.setItem(TOKEN_STORAGE_KEY, token);
}

export async function removeStoredToken() {
  return AsyncStorage.removeItem(TOKEN_STORAGE_KEY);
}

function buildUrl(endpoint) {
  const cleanEndpoint = endpoint.replace(/^\/+/, '');
  return `${BASE_URL}/${cleanEndpoint}`;
}

function getFirstValidationError(errors) {
  if (!errors) {
    return null;
  }

  const keys = Object.keys(errors);

  for (const key of keys) {
    const messages = errors[key];

    if (Array.isArray(messages) && messages.length > 0) {
      return messages[0];
    }
  }

  return null;
}

function getErrorMessage(data, fallbackMessage) {
  const validationMessage = getFirstValidationError(data?.errors);

  if (validationMessage) {
    return validationMessage;
  }

  if (typeof data?.message === 'string' && data.message.trim()) {
    return data.message;
  }

  return fallbackMessage;
}

async function parseResponse(response) {
  const text = await response.text();

  if (!text) {
    return null;
  }

  try {
    return JSON.parse(text);
  } catch (error) {
    return text;
  }
}

async function apiRequest(endpoint, options = {}) {
  const {
    method = 'GET',
    body,
    requiresAuth = true,
  } = options;

  const headers = {
    Accept: 'application/json',
  };

  if (body !== undefined) {
    headers['Content-Type'] = 'application/json';
  }

  if (requiresAuth) {
    const token = await getStoredToken();

    if (!token) {
      throw new ApiError('No login token found. Please log in again.', 401);
    }

    headers.Authorization = `Bearer ${token}`;
  }

  try {
    const response = await fetch(buildUrl(endpoint), {
      method,
      headers,
      body: body !== undefined ? JSON.stringify(body) : undefined,
    });

    const data = await parseResponse(response);

    if (!response.ok) {
      throw new ApiError(
        getErrorMessage(
          data,
          `Request failed with status code ${response.status}.`,
        ),
        response.status,
        data,
        data?.errors || null,
      );
    }

    return data;
  } catch (error) {
    if (error instanceof ApiError) {
      throw error;
    }

    throw new ApiError(
      'Could not connect to the server. Please check your API URL and network connection.',
    );
  }
}

export async function apiGet(endpoint, requiresAuth = true) {
  return apiRequest(endpoint, {
    method: 'GET',
    requiresAuth,
  });
}

export async function apiPost(endpoint, body = undefined, requiresAuth = true) {
  return apiRequest(endpoint, {
    method: 'POST',
    body,
    requiresAuth,
  });
}

export async function apiPut(endpoint, body = undefined, requiresAuth = true) {
  return apiRequest(endpoint, {
    method: 'PUT',
    body,
    requiresAuth,
  });
}
