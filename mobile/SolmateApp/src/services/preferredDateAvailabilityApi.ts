import {apiGet} from './api';

export type BookingRequestType = 'inspection' | 'installation' | 'maintenance';

type PreferredDateAvailabilityResponse = {
  unavailable_dates?: string[];
};

export async function getUnavailablePreferredDates(
  type: BookingRequestType = 'inspection',
) {
  const response = await apiGet<PreferredDateAvailabilityResponse>(
    `/preferred-date-availability?type=${type}`,
  );

  return Array.isArray(response?.unavailable_dates)
    ? response.unavailable_dates
    : [];
}
