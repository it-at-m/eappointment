/**
 * Endpoints
 */
export const VUE_APP_ZMS_API_PROVIDERS_AND_SERVICES_ENDPOINT =
  "/api/backend/offices-and-services";
export const VUE_APP_ZMS_API_CALENDAR_ENDPOINT = "/api/backend/available-days";
export const VUE_APP_ZMS_API_AVAILABLE_TIME_SLOTS_ENDPOINT =
  "/api/backend/available-appointments";
export const VUE_APP_ZMS_API_RESERVE_APPOINTMENT_ENDPOINT =
  "/api/backend/reserve-appointment";
export const VUE_APP_ZMS_API_APPOINTMENT_ENDPOINT = "/api/backend/appointment";
export const VUE_APP_ZMS_API_UPDATE_APPOINTMENT_ENDPOINT =
  "/api/backend/update-appointment";
export const VUE_APP_ZMS_API_CONFIRM_RESERVATION_ENDPOINT =
  "/api/backend/confirm-appointment";
export const VUE_APP_ZMS_API_CANCEL_APPOINTMENT_ENDPOINT =
  "/api/backend/cancel-appointment";
export const VUE_APP_ZMS_API_PRECONFIRM_RESERVATION_ENDPOINT =
  "/api/backend/preconfirm-appointment";
export const VUE_APP_ZMS_API_CAPTCHA_DETAILS_ENDPOINT =
  "/api/backend/captcha-details";

export const MAX_SLOTS = 25;

export function getAPIBaseURL(): string {
  console.log("#getAPIBaseURL", import.meta);
  if (import.meta.env.VITE_VUE_APP_API_URL) {
    return import.meta.env.VITE_VUE_APP_API_URL;
  } else {
    return new URL(import.meta.url).origin;
  }
}
