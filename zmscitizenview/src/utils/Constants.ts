/**
 * Endpoints
 */
export const VUE_APP_ZMS_API_PROVIDERS_AND_SERVICES_ENDPOINT =
  "/offices-and-services/";
export const VUE_APP_ZMS_API_CALENDAR_ENDPOINT = "/available-days-by-office/";
export const VUE_APP_ZMS_API_AVAILABLE_TIME_SLOTS_ENDPOINT =
  "/available-appointments-by-office/";
export const VUE_APP_ZMS_API_RESERVE_APPOINTMENT_ENDPOINT =
  "/reserve-appointment/";
export const VUE_APP_ZMS_API_APPOINTMENT_ENDPOINT = "/appointment/";
export const VUE_APP_ZMS_API_UPDATE_APPOINTMENT_ENDPOINT =
  "/update-appointment/";
export const VUE_APP_ZMS_API_CONFIRM_APPOINTMENT_ENDPOINT =
  "/confirm-appointment/";
export const VUE_APP_ZMS_API_CANCEL_APPOINTMENT_ENDPOINT =
  "/cancel-appointment/";
export const VUE_APP_ZMS_API_PRECONFIRM_APPOINTMENT_ENDPOINT =
  "/preconfirm-appointment/";
export const VUE_APP_ZMS_API_CAPTCHA_DETAILS_ENDPOINT = "/captcha-details/";
export const VUE_APP_ZMS_API_CAPTCHA_CHALLENGE_ENDPOINT = "/captcha-challenge/";
export const VUE_APP_ZMS_API_CAPTCHA_VERIFY_ENDPOINT = "/captcha-verify/";

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
