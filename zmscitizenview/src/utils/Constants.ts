/**
 * Endpoints
 */
export const VUE_APP_ZMS_API_PROVIDERS_AND_SERVICES_ENDPOINT =
  "/api/citizen/offices-and-services/";
export const VUE_APP_ZMS_API_CALENDAR_ENDPOINT = "/api/citizen/available-days/";
export const VUE_APP_ZMS_API_AVAILABLE_TIME_SLOTS_ENDPOINT =
  "/api/citizen/available-appointments/";
export const VUE_APP_ZMS_API_RESERVE_APPOINTMENT_ENDPOINT =
  "/api/citizen/reserve-appointment/";
export const VUE_APP_ZMS_API_APPOINTMENT_ENDPOINT = "/api/citizen/appointment/";
export const VUE_APP_ZMS_API_UPDATE_APPOINTMENT_ENDPOINT =
  "/api/citizen/update-appointment/";
export const VUE_APP_ZMS_API_CONFIRM_APPOINTMENT_ENDPOINT =
  "/api/citizen/confirm-appointment/";
export const VUE_APP_ZMS_API_CANCEL_APPOINTMENT_ENDPOINT =
  "/api/citizen/cancel-appointment/";
export const VUE_APP_ZMS_API_PRECONFIRM_APPOINTMENT_ENDPOINT =
  "/api/citizen/preconfirm-appointment/";
export const VUE_APP_ZMS_API_CAPTCHA_DETAILS_ENDPOINT =
  "/api/citizen/captcha-details/";

export function getServiceBaseURL(): string {
  return import.meta.env.VITE_VUE_APP_SERVICE_BASE_URL;
}

export const MAX_SLOTS = 25;

export const OFTEN_SEARCHED_SERVICES = new Map<string, string>([
  ["1063475", "shortNameResidenceRegistration"],
  ["1063453", "shortNamePassport"],
  ["1063441", "shortNameIdentityCard"],
  ["10295182", "shortNameIdentityCardCollection"],
  ["10176294", "shortNameDrivingLicenseCollection"],
  ["10225119", "shortNameEidPin"],
  ["1064314", "shortNameVehicleReregistration"],
  ["1064305", "shortNameVehicleDeregistration"],
]);

export function getAPIBaseURL(baseUrl: string | undefined): string {
  if (baseUrl) {
    return baseUrl;
  }
  if (import.meta.env.VITE_VUE_APP_API_URL) {
    return import.meta.env.VITE_VUE_APP_API_URL;
  } else {
    return new URL(import.meta.url).origin;
  }
}
