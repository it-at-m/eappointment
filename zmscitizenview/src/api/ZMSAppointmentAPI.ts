import { AppointmentDTO } from "@/api/models/AppointmentDTO";
import { AvailableDaysDTO } from "@/api/models/AvailableDaysDTO";
import { AvailableTimeSlotsDTO } from "@/api/models/AvailableTimeSlotsDTO";
import { ErrorDTO } from "@/api/models/ErrorDTO";
import { OfficesAndServicesDTO } from "@/api/models/OfficesAndServicesDTO";
import { AppointmentHash } from "@/types/AppointmentHashTypes";
import { OfficeImpl } from "@/types/OfficeImpl";
import {
  getAPIBaseURL,
  VUE_APP_ZMS_API_APPOINTMENT_ENDPOINT,
  VUE_APP_ZMS_API_AVAILABLE_TIME_SLOTS_ENDPOINT,
  VUE_APP_ZMS_API_CALENDAR_ENDPOINT, VUE_APP_ZMS_API_CANCEL_APPOINTMENT_ENDPOINT,
  VUE_APP_ZMS_API_CONFIRM_APPOINTMENT_ENDPOINT,
  VUE_APP_ZMS_API_PRECONFIRM_APPOINTMENT_ENDPOINT,
  VUE_APP_ZMS_API_PROVIDERS_AND_SERVICES_ENDPOINT,
  VUE_APP_ZMS_API_RESERVE_APPOINTMENT_ENDPOINT,
  VUE_APP_ZMS_API_UPDATE_APPOINTMENT_ENDPOINT,
} from "@/utils/Constants";

const TODAY = new Date();
const MAXDATE = new Date(
  TODAY.getFullYear(),
  TODAY.getMonth() + 6,
  TODAY.getDate()
);

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
  provider: OfficeImpl,
  serviceIds: string[],
  serviceCounts: number[]
): Promise<AvailableDaysDTO | ErrorDTO> {
  const params: Record<string, any> = {
    startDate: convertDateToString(TODAY),
    endDate: convertDateToString(MAXDATE),
    officeId: provider.id,
    serviceId: serviceIds,
    serviceCount: serviceCounts,
  };

  return fetch(
    getAPIBaseURL() +
      VUE_APP_ZMS_API_CALENDAR_ENDPOINT +
      "?" +
      new URLSearchParams(params).toString()
  ).then((response) => {
    return response.json();
  });
}

export function fetchAvailableTimeSlots(
  date: string,
  provider: OfficeImpl,
  serviceIds: string[],
  serviceCounts: number[]
): Promise<AvailableTimeSlotsDTO | ErrorDTO> {
  const params: Record<string, any> = {
    date: date,
    officeId: provider.id,
    serviceId: serviceIds,
    serviceCount: serviceCounts,
  };

  return fetch(
    getAPIBaseURL() +
      VUE_APP_ZMS_API_AVAILABLE_TIME_SLOTS_ENDPOINT +
      "?" +
      new URLSearchParams(params).toString()
  ).then((response) => {
    return response.json();
  });
}

const convertDateToString = (date: Date) => {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const day = String(date.getDate()).padStart(2, "0");
  return `${year}-${month}-${day}`;
};

export function reserveAppointment(
  timeSlot: number,
  serviceIds: string[],
  serviceCount: number[],
  providerId: string
): Promise<AppointmentDTO | ErrorDTO> {
  const requestBody = {
    timestamp: timeSlot,
    serviceCount: serviceCount,
    officeId: providerId,
    serviceId: serviceIds,
    captchaSolution: null,
  };

  return fetch(getAPIBaseURL() + VUE_APP_ZMS_API_RESERVE_APPOINTMENT_ENDPOINT, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(requestBody),
  }).then((response) => {
    return response.json();
  });
}

export function updateAppointment(
  appointment: AppointmentDTO
): Promise<AppointmentDTO | ErrorDTO> {
  const requestBody = {
    processId: appointment.processId,
    authKey: appointment.authKey,
    scope: appointment.scope,
    familyName: appointment.familyName,
    email: appointment.email,
    telephone: appointment.telephone,
    customTextfield: appointment.customTextfield,
  };

  return fetch(getAPIBaseURL() + VUE_APP_ZMS_API_UPDATE_APPOINTMENT_ENDPOINT, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(requestBody),
  }).then((response) => {
    return response.json();
  });
}

export function preconfirmAppointment(
  appointment: AppointmentDTO
): Promise<AppointmentDTO | ErrorDTO> {
  const requestBody = {
    processId: appointment.processId,
    authKey: appointment.authKey,
    scope: appointment.scope,
    captchaSolution: null,
  };

  return fetch(
    getAPIBaseURL() + VUE_APP_ZMS_API_PRECONFIRM_APPOINTMENT_ENDPOINT,
    {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(requestBody),
    }
  ).then((response) => {
    return response.json();
  });
}

export function confirmAppointment(
  appointment: AppointmentHash
): Promise<AppointmentDTO | ErrorDTO> {
  const requestBody = {
    processId: appointment.id,
    authKey: appointment.authKey,
    scope: appointment.scope,
  };

  return fetch(getAPIBaseURL() + VUE_APP_ZMS_API_CONFIRM_APPOINTMENT_ENDPOINT, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(requestBody),
  }).then((response) => {
    return response.json();
  });
}

export function fetchAppointment(
  appointment: AppointmentHash
): Promise<AppointmentDTO | ErrorDTO> {
  const params: Record<string, any> = {
    processId: appointment.id,
    authKey: appointment.authKey,
    scope: appointment.scope,
  };

  return fetch(
    getAPIBaseURL() +
      VUE_APP_ZMS_API_APPOINTMENT_ENDPOINT +
      "?" +
      new URLSearchParams(params).toString()
  ).then((response) => {
    return response.json();
  });
}

export function cancelAppointment(
  appointment: AppointmentDTO
): Promise<AppointmentDTO | ErrorDTO> {
  const requestBody = {
    processId: appointment.processId,
    authKey: appointment.authKey,
    scope: appointment.scope,
  };

  return fetch(
    getAPIBaseURL() + VUE_APP_ZMS_API_CANCEL_APPOINTMENT_ENDPOINT,
    {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(requestBody),
    }
  ).then((response) => {
    return response.json();
  });
}
