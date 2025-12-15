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
export const VUE_APP_ZMS_API_MYAPPOINTMENTS_ENDPOINT = "/my-appointments/";

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

export const QUERY_PARAM_APPOINTMENT_ID = "ap-id";
export const QUERY_PARAM_APPOINTMENT_DISPLAY_NUMBER = "ap-display";

export const LOCALSTORAGE_PARAM_APPOINTMENT_DATA = "lhm-appointment-data";

export enum APPOINTMENT_ACTION_TYPE {
  RESCHEDULE = "reschedule",
  CANCEL = "cancel",
}

export const API_BASE_URL_EXTENSION = "/api/citizen";
export const API_BASE_URL_AUTHENTICATED_EXTENSION =
  "/authenticated/api/citizen";

function getRawApiBaseURL(baseUrl: string | undefined): string {
  if (baseUrl) {
    return baseUrl;
  }
  if (import.meta.env.VITE_VUE_APP_API_URL) {
    return import.meta.env.VITE_VUE_APP_API_URL;
  } else {
    return new URL(import.meta.url).origin;
  }
}

export const VARIANTS_WITH_HINTS = [1, 2, 3] as const;
export const getVariantHint = (
  variantId: number,
  t: (key: string) => string
) => {
  return VARIANTS_WITH_HINTS.includes(variantId)
    ? t(`locationVariantText.${variantId}`)
    : undefined;
};

export function getAPIBaseURL(
  baseUrl: string | undefined,
  authenticated: boolean
): string {
  let url = getRawApiBaseURL(baseUrl);

  // Can be deleted if the configurations have been adjusted on all environments.
  if (url.endsWith("/api/citizen")) {
    url = url.slice(0, -"/api/citizen".length);
  } else if (url.endsWith("/api/citizen/")) {
    url = url.slice(0, -"/api/citizen/".length);
  }

  if (authenticated) {
    return url + API_BASE_URL_AUTHENTICATED_EXTENSION;
  } else {
    return url + API_BASE_URL_EXTENSION;
  }
}

/**
 * UI thresholds and limits
 */
// ZMSKVR-110: UX rule for view mode
// Few appointments → group by am/pm (≤ 18 per day); many appointments → hourly (> 18)
// Rationale: accessibility and layout density tradeoff documented in the ticket.
export const APPOINTMENTS_THRESHOLD_FOR_HOURLY_VIEW = 18;
