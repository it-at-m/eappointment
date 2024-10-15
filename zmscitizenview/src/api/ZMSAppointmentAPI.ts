import moment from "moment";

import { AvailableDaysDTO } from "@/api/models/AvailableDaysDTO";
import { AvailableTimeSlotsDTO } from "@/api/models/AvailableTimeSlotsDTO";
import { ErrorDTO } from "@/api/models/ErrorDTO";
import { OfficesAndServicesDTO } from "@/api/models/OfficesAndServicesDTO";
import { Provider } from "@/api/models/Provider";
import {
  getAPIBaseURL,
  VUE_APP_ZMS_API_AVAILABLE_TIME_SLOTS_ENDPOINT,
  VUE_APP_ZMS_API_CALENDAR_ENDPOINT,
  VUE_APP_ZMS_API_PROVIDERS_AND_SERVICES_ENDPOINT,
} from "@/utils/Constants";

export function fetchServicesAndProviders(
  serviceId?: string,
  locationId?: string
): Promise<OfficesAndServicesDTO> {
  let apiUrl =
    getAPIBaseURL() + VUE_APP_ZMS_API_PROVIDERS_AND_SERVICES_ENDPOINT;

  if (serviceId && locationId) {
    apiUrl += `?serviceId=${serviceId}&locationId=${locationId}`;
  }

  return fetch(apiUrl).then((response) => {
    return response.json();
  });
}

export function fetchAvailableDays(
  provider: Provider,
  serviceIds: Array<string>,
  serviceCounts: Array<number>
): Promise<AvailableDaysDTO | ErrorDTO> {
  const dateIn6Months = moment().add(6, "M");
  const params: Record<string, any> = {
    startDate: moment().format("YYYY-MM-DD"),
    endDate: dateIn6Months.format("YYYY-MM-DD"),
    officeId: provider.id,
    serviceId: serviceIds,
    serviceCount: serviceCounts,
  };

  return fetch(
    getAPIBaseURL() +
      VUE_APP_ZMS_API_CALENDAR_ENDPOINT +
      +"?" +
      new URLSearchParams(params).toString()
  ).then((response) => {
    return response.json();
  });
}

export function fetchAvailableTimeSlots(
  date: string,
  provider: Provider,
  serviceIds: Array<string>,
  serviceCounts: Array<number>
): Promise<AvailableTimeSlotsDTO | ErrorDTO> {
  const params: Record<string, any> = {
    date: moment(date).format("YYYY-MM-DD"),
    officeId: provider.id,
    serviceId: serviceIds,
    serviceCount: serviceCounts,
  };

  return fetch(
    getAPIBaseURL() +
      VUE_APP_ZMS_API_AVAILABLE_TIME_SLOTS_ENDPOINT +
      +"?" +
      new URLSearchParams(params).toString()
  ).then((response) => {
    return response.json();
  });
}
