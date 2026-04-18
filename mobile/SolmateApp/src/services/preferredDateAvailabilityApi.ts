import {apiGet} from './api';

type PreferredDateAvailabilityResponse = {
  unavailable_dates?: string[];
};

export async function getUnavailablePreferredDates() {
  const response = await apiGet<PreferredDateAvailabilityResponse>(
    '/preferred-date-availability',
  );

  return Array.isArray(response?.unavailable_dates)
    ? response.unavailable_dates
    : [];
}
