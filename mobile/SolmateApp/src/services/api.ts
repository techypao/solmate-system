import AsyncStorage from '@react-native-async-storage/async-storage';

// Change this in one place when your Laravel API URL changes.
export const API_BASE_URL = 'http://10.0.2.2:8000/api';
export const TOKEN_STORAGE_KEY = 'token';

type HttpMethod = 'GET' | 'POST' | 'PUT';

type ValidationErrors = Record<string, string[]>;

type ApiRequestOptions = {
  method?: HttpMethod;
  body?: unknown;
  requiresAuth?: boolean;
};

export class ApiError extends Error {
  status: number;
  data?: unknown;
  errors?: ValidationErrors;

  constructor(
    message: string,
    status = 0,
    data?: unknown,
    errors?: ValidationErrors,
  ) {
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

export async function saveStoredToken(token: string) {
  return AsyncStorage.setItem(TOKEN_STORAGE_KEY, token);
}

export async function removeStoredToken() {
  return AsyncStorage.removeItem(TOKEN_STORAGE_KEY);
}

function buildUrl(endpoint: string) {
  const cleanEndpoint = endpoint.replace(/^\/+/, '');
  return `${API_BASE_URL}/${cleanEndpoint}`;
}

function getFirstValidationError(errors?: ValidationErrors) {
  if (!errors) {
    return null;
  }

  for (const key of Object.keys(errors)) {
    const messages = errors[key];

    if (Array.isArray(messages) && messages.length > 0) {
      return messages[0];
    }
  }

  return null;
}

function getErrorMessage(data: any, fallbackMessage: string) {
  const validationMessage = getFirstValidationError(data?.errors);

  if (validationMessage) {
    return validationMessage;
  }

  if (typeof data?.message === 'string' && data.message.trim()) {
    return data.message;
  }

  return fallbackMessage;
}

async function parseResponse(response: Response) {
  const responseText = await response.text();

  if (!responseText) {
    return null;
  }

  try {
    return JSON.parse(responseText);
  } catch {
    return responseText;
  }
}

async function apiRequest<T>(
  endpoint: string,
  options: ApiRequestOptions = {},
): Promise<T> {
  const {method = 'GET', body, requiresAuth = true} = options;

  const headers: Record<string, string> = {
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
        data?.errors,
      );
    }

    return data as T;
  } catch (error) {
    if (error instanceof ApiError) {
      throw error;
    }

    throw new ApiError(
      'Could not connect to the server. Please check your API URL and network connection.',
    );
  }
}

export function apiGet<T>(endpoint: string, requiresAuth = true) {
  return apiRequest<T>(endpoint, {
    method: 'GET',
    requiresAuth,
  });
}

export function apiPost<T>(
  endpoint: string,
  body?: unknown,
  requiresAuth = true,
) {
  return apiRequest<T>(endpoint, {
    method: 'POST',
    body,
    requiresAuth,
  });
}

export function apiPut<T>(endpoint: string, body?: unknown, requiresAuth = true) {
  return apiRequest<T>(endpoint, {
    method: 'PUT',
    body,
    requiresAuth,
  });
}
