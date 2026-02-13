import { AppointmentDTO } from "@/api/models/AppointmentDTO";
import { AvailableDaysDTO } from "@/api/models/AvailableDaysDTO";
import { AvailableTimeSlotsDTO } from "@/api/models/AvailableTimeSlotsDTO";
import { CaptchaDetailsDTO } from "@/api/models/CaptchaDetailsDTO";
import { ErrorDTO } from "@/api/models/ErrorDTO";
import { OfficesAndServicesDTO } from "@/api/models/OfficesAndServicesDTO";
import { AppointmentHash } from "@/types/AppointmentHashTypes";
import { GlobalState } from "@/types/GlobalState";
import {
  getAPIBaseURL,
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

interface Request {
  path: string;
  params?: string[][] | Record<string, string>;
  forceAuth?: boolean;
  globalState: GlobalState;
}

type GetRequest = Request & {
  method: "GET";
};

type PostRequest = Request & {
  method: "POST";
  data: any;
};

export function request<TResponse>(
  request: GetRequest | PostRequest
): Promise<TResponse> {
  let baseUrl = request.globalState?.baseUrl;
  if (!baseUrl) {
    baseUrl = "";
  }
  let suffix = "";
  if (request.params) {
    suffix = "?" + new URLSearchParams(request.params).toString();
  }
  const headers: Record<string, string> = {};
  if (request.globalState?.accessToken) {
    headers["Authorization"] = `Bearer ${request.globalState.accessToken}`;
  }
  const requestInit: RequestInit = {
    method: request.method,
    credentials: "include",
  };
  if (request.method === "POST") {
    headers["Content-Type"] = "application/json";
    requestInit.body = JSON.stringify(request.data);
  }
  requestInit.headers = headers;
  return fetch(
    getAPIBaseURL(
      baseUrl,
      !!request.globalState?.accessToken || !!request.forceAuth || false
    ) +
      request.path +
      suffix,
    requestInit
  ).then((response) => {
    return response.json();
  });
}

export function fetchServicesAndProviders(
  serviceId?: string,
  locationId?: string,
  baseUrl?: string
): Promise<OfficesAndServicesDTO | ErrorDTO> {
  let apiUrl =
    getAPIBaseURL(baseUrl, false) +
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

  return fetch(apiUrl, { credentials: "include" })
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
  globalState: GlobalState,
  providerIds: number[],
  serviceIds: string[],
  serviceCounts: number[],
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

  return request({
    globalState,
    method: "GET",
    path: VUE_APP_ZMS_API_CALENDAR_ENDPOINT,
    params,
  });
}

export function fetchAvailableTimeSlots(
  globalState: GlobalState,
  date: string,
  providerIds: number[],
  serviceIds: string[],
  serviceCounts: number[],
  captchaToken?: string
): Promise<AvailableTimeSlotsDTO | ErrorDTO> {
  const params: Record<string, any> = {
    date: date,
    officeId: providerIds,
    serviceId: serviceIds,
    serviceCount: serviceCounts,
    ...(captchaToken && { captchaToken }),
  };

  return request({
    globalState,
    method: "GET",
    path: VUE_APP_ZMS_API_AVAILABLE_TIME_SLOTS_ENDPOINT,
    params,
  });
}

const convertDateToString = (date: Date) => {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const day = String(date.getDate()).padStart(2, "0");
  return `${year}-${month}-${day}`;
};

export function reserveAppointment(
  globalState: GlobalState,
  timeSlot: number,
  serviceIds: string[],
  serviceCount: number[],
  providerId: string,
  captchaToken?: string
): Promise<AppointmentDTO | ErrorDTO> {
  const requestBody = {
    timestamp: timeSlot,
    serviceCount: serviceCount,
    officeId: providerId,
    serviceId: serviceIds,
    ...(captchaToken && { captchaToken }),
  };

  return request({
    globalState,
    method: "POST",
    path: VUE_APP_ZMS_API_RESERVE_APPOINTMENT_ENDPOINT,
    data: requestBody,
  });
}

export function updateAppointment(
  globalState: GlobalState,
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
    customTextfield2: appointment.customTextfield2,
  };

  return request({
    globalState,
    method: "POST",
    path: VUE_APP_ZMS_API_UPDATE_APPOINTMENT_ENDPOINT,
    data: requestBody,
  });
}

export function preconfirmAppointment(
  globalState: GlobalState,
  appointment: AppointmentDTO
): Promise<AppointmentDTO | ErrorDTO> {
  const requestBody = {
    processId: appointment.processId,
    authKey: appointment.authKey,
    scope: appointment.scope,
  };

  return request({
    globalState,
    method: "POST",
    path: VUE_APP_ZMS_API_PRECONFIRM_APPOINTMENT_ENDPOINT,
    data: requestBody,
  });
}

export function confirmAppointment(
  globalState: GlobalState,
  appointment: AppointmentHash
): Promise<AppointmentDTO | ErrorDTO> {
  const requestBody = {
    processId: appointment.id,
    authKey: appointment.authKey,
    scope: appointment.scope,
  };

  return request({
    globalState,
    method: "POST",
    path: VUE_APP_ZMS_API_CONFIRM_APPOINTMENT_ENDPOINT,
    data: requestBody,
  });
}

export function fetchAppointment(
  globalState: GlobalState,
  appointment: AppointmentHash
): Promise<AppointmentDTO | ErrorDTO> {
  const params: Record<string, string> = {
    processId: appointment.id,
    authKey: appointment.authKey,
  };
  if (appointment.scope) {
    params["scope"] = appointment.scope.id;
  }

  return request({
    globalState,
    method: "GET",
    path: VUE_APP_ZMS_API_APPOINTMENT_ENDPOINT,
    params,
  });
}

export function cancelAppointment(
  globalState: GlobalState,
  appointment: AppointmentDTO
): Promise<AppointmentDTO | ErrorDTO> {
  const requestBody = {
    processId: appointment.processId,
    authKey: appointment.authKey,
    scope: appointment.scope,
  };

  return request({
    globalState,
    method: "POST",
    path: VUE_APP_ZMS_API_CANCEL_APPOINTMENT_ENDPOINT,
    data: requestBody,
  });
}

export function fetchCaptchaDetails(
  globalState: GlobalState
): Promise<CaptchaDetailsDTO | ErrorDTO> {
  return request({
    globalState,
    method: "GET",
    path: VUE_APP_ZMS_API_CAPTCHA_DETAILS_ENDPOINT,
  });
}
