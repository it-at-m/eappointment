import { AppointmentDTO } from "@/api/models/AppointmentDTO";
import { AvailableDaysDTO } from "@/api/models/AvailableDaysDTO";
import { AvailableTimeSlotsDTO } from "@/api/models/AvailableTimeSlotsDTO";
import { CaptchaDetailsDTO } from "@/api/models/CaptchaDetailsDTO";
import { ErrorDTO } from "@/api/models/ErrorDTO";
import { OfficesAndServicesDTO } from "@/api/models/OfficesAndServicesDTO";
import { AppointmentHash } from "@/types/AppointmentHashTypes";
import {
  getGeneratedAPIBaseURL,
  VUE_APP_ZMS_API_APPOINTMENT_ENDPOINT,
  VUE_APP_ZMS_API_AVAILABLE_TIME_SLOTS_ENDPOINT,
  VUE_APP_ZMS_API_CALENDAR_ENDPOINT,
  VUE_APP_ZMS_API_CANCEL_APPOINTMENT_ENDPOINT,
  VUE_APP_ZMS_API_CAPTCHA_DETAILS_ENDPOINT,
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
  locationId?: string,
  baseUrl?: string
): Promise<OfficesAndServicesDTO | ErrorDTO> {
  let apiUrl =
    getGeneratedAPIBaseURL(baseUrl, false) +
    VUE_APP_ZMS_API_PROVIDERS_AND_SERVICES_ENDPOINT;

  const params = new URLSearchParams();
  if (serviceId) {
    params.append("serviceId", serviceId);
    if (locationId) {
      params.append("locationId", locationId);
    }
  }
  if (params.toString()) {
    apiUrl += "?" + params.toString();
  }

  return fetch(apiUrl)
    .then((response) => {
      if (response.status >= 400 && response.status < 600) {
        return response
          .json()
          .catch(() => ({}))
          .then((data: any) => {
            if (!data.errors) {
              data.errors = [
                {
                  errorCode:
                    response.status >= 500 ? "serverError" : "internalError",
                  errorMessage: `HTTP ${response.status}`,
                  statusCode: response.status,
                },
              ];
            }
            return data;
          });
      }
      return response.json();
    })
    .catch(() => {
      return {
        errors: [
          {
            errorCode: "networkError",
            errorMessage: "Network error or service unavailable",
            errorType: "error",
            statusCode: 0,
          },
        ],
      };
    });
}

export function fetchAvailableDays(
  providerIds: number[],
  serviceIds: string[],
  serviceCounts: number[],
  baseUrl?: string,
  captchaToken?: string
): Promise<AvailableDaysDTO | ErrorDTO> {
  const params: Record<string, any> = {
    startDate: convertDateToString(TODAY),
    endDate: convertDateToString(MAXDATE),
    officeId: providerIds,
    serviceId: serviceIds,
    serviceCount: serviceCounts,
    ...(captchaToken && { captchaToken }),
  };

  return fetch(
    getGeneratedAPIBaseURL(baseUrl, false) +
      VUE_APP_ZMS_API_CALENDAR_ENDPOINT +
      "?" +
      new URLSearchParams(params).toString()
  ).then((response) => {
    return response.json();
  });
}

export function fetchAvailableTimeSlots(
  date: string,
  providerIds: number[],
  serviceIds: string[],
  serviceCounts: number[],
  baseUrl?: string,
  captchaToken?: string
): Promise<AvailableTimeSlotsDTO | ErrorDTO> {
  const params: Record<string, any> = {
    date: date,
    officeId: providerIds,
    serviceId: serviceIds,
    serviceCount: serviceCounts,
    ...(captchaToken && { captchaToken }),
  };

  return fetch(
    getGeneratedAPIBaseURL(baseUrl, false) +
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
  providerId: string,
  baseUrl?: string,
  captchaToken?: string
): Promise<AppointmentDTO | ErrorDTO> {
  const requestBody = {
    timestamp: timeSlot,
    serviceCount: serviceCount,
    officeId: providerId,
    serviceId: serviceIds,
    ...(captchaToken && { captchaToken }),
  };

  return fetch(
    getGeneratedAPIBaseURL(baseUrl, false) +
      VUE_APP_ZMS_API_RESERVE_APPOINTMENT_ENDPOINT,
    {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(requestBody),
    }
  ).then((response) => {
    return response.json();
  });
}

export function updateAppointment(
  appointment: AppointmentDTO,
  baseUrl?: string
): Promise<AppointmentDTO | ErrorDTO> {
  const requestBody = {
    processId: appointment.processId,
    authKey: appointment.authKey,
    scope: appointment.scope,
    familyName: appointment.familyName,
    email: appointment.email,
    telephone: appointment.telephone,
    customTextfield: appointment.customTextfield,
    customTextfield2: appointment.customTextfield2,
  };

  return fetch(
    getGeneratedAPIBaseURL(baseUrl, false) +
      VUE_APP_ZMS_API_UPDATE_APPOINTMENT_ENDPOINT,
    {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(requestBody),
    }
  ).then((response) => {
    return response.json();
  });
}

export function preconfirmAppointment(
  appointment: AppointmentDTO,
  baseUrl?: string
): Promise<AppointmentDTO | ErrorDTO> {
  const requestBody = {
    processId: appointment.processId,
    authKey: appointment.authKey,
    scope: appointment.scope,
  };

  return fetch(
    getGeneratedAPIBaseURL(baseUrl, false) +
      VUE_APP_ZMS_API_PRECONFIRM_APPOINTMENT_ENDPOINT,
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
  appointment: AppointmentHash,
  baseUrl?: string
): Promise<AppointmentDTO | ErrorDTO> {
  const requestBody = {
    processId: appointment.id,
    authKey: appointment.authKey,
    scope: appointment.scope,
  };

  return fetch(
    getGeneratedAPIBaseURL(baseUrl, false) +
      VUE_APP_ZMS_API_CONFIRM_APPOINTMENT_ENDPOINT,
    {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(requestBody),
    }
  ).then((response) => {
    return response.json();
  });
}

export function fetchAppointment(
  appointment: AppointmentHash,
  baseUrl?: string
): Promise<AppointmentDTO | ErrorDTO> {
  const params: Record<string, any> = {
    processId: appointment.id,
    authKey: appointment.authKey,
    scope: appointment.scope,
  };

  return fetch(
    getGeneratedAPIBaseURL(baseUrl, false) +
      VUE_APP_ZMS_API_APPOINTMENT_ENDPOINT +
      "?" +
      new URLSearchParams(params).toString()
  ).then((response) => {
    return response.json();
  });
}

export function cancelAppointment(
  appointment: AppointmentDTO,
  baseUrl?: string
): Promise<AppointmentDTO | ErrorDTO> {
  const requestBody = {
    processId: appointment.processId,
    authKey: appointment.authKey,
    scope: appointment.scope,
  };

  return fetch(
    getGeneratedAPIBaseURL(baseUrl, false) +
      VUE_APP_ZMS_API_CANCEL_APPOINTMENT_ENDPOINT,
    {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(requestBody),
    }
  ).then((response) => {
    return response.json();
  });
}

export function fetchCaptchaDetails(
  baseUrl?: string
): Promise<CaptchaDetailsDTO | ErrorDTO> {
  return fetch(
    getGeneratedAPIBaseURL(baseUrl, false) +
      VUE_APP_ZMS_API_CAPTCHA_DETAILS_ENDPOINT
  ).then((response) => {
    return response.json();
  });
}
