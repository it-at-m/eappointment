import { ref, Ref } from "vue";

import { fetchServicesAndProviders } from "@/api/ZMSAppointmentAPI";

export type ApiStatusType = "normal" | "maintenance" | "systemFailure";

export interface ApiStatusState {
  status: Ref<ApiStatusType>;
  lastCheckTime: Ref<number | null>;
  checkInterval: Ref<number | null>;
  baseUrl: Ref<string | undefined>;
}

const apiStatusState: ApiStatusState = {
  status: ref<ApiStatusType>("normal"),
  lastCheckTime: ref(null),
  checkInterval: ref(null),
  baseUrl: ref(undefined),
};

const CHECK_INTERVAL_MS = 30000;

/**
 * Determines the API status based on error information
 * @param firstError - The first error from the response
 * @returns ApiStatusType - The determined status
 */
function determineStatusFromError(firstError: any): ApiStatusType {
  if (firstError?.errorCode === "rateLimitExceeded") {
    return "normal";
  }

  if (firstError?.statusCode === 503) {
    return "maintenance";
  }

  if (firstError?.errorCode === "serviceUnavailable") {
    return "maintenance";
  }

  if (firstError?.statusCode >= 500 || firstError?.statusCode === 0) {
    return "systemFailure";
  }

  if (firstError?.statusCode >= 400 && firstError?.statusCode < 500) {
    return "maintenance";
  }

  return "normal";
}

/**
 * Checks if the offices-and-services API is available and determines the appropriate status
 * @param baseUrl - Optional base URL for the API
 * @returns Promise<ApiStatusType> - The current API status
 */
export async function checkApiStatus(baseUrl?: string): Promise<ApiStatusType> {
  try {
    const response = await fetchServicesAndProviders(
      undefined,
      undefined,
      baseUrl
    );

    if (response && !(response as any).errors) {
      return "normal";
    }

    const firstError = (response as any)?.errors?.[0];
    return determineStatusFromError(firstError);
  } catch (error) {
    return "systemFailure";
  }
}

/**
 * Sets the API status and starts automatic recovery checking if needed
 * @param status - The new API status
 * @param baseUrl - Optional base URL for the API
 */
export function setApiStatus(status: ApiStatusType, baseUrl?: string): void {
  apiStatusState.status.value = status;
  apiStatusState.lastCheckTime.value = Date.now();
  apiStatusState.baseUrl.value = baseUrl;

  if (apiStatusState.checkInterval.value) {
    clearInterval(apiStatusState.checkInterval.value);
    apiStatusState.checkInterval.value = null;
  }

  if (status !== "normal") {
    apiStatusState.checkInterval.value = setInterval(async () => {
      const currentStatus = await checkApiStatus(baseUrl);
      if (currentStatus === "normal") {
        setApiStatus("normal", baseUrl);
      } else if (currentStatus !== status) {
        setApiStatus(currentStatus, baseUrl);
      }
    }, CHECK_INTERVAL_MS) as any;
  }
}

/**
 * Gets the current API status state
 * @returns ApiStatusState - The current API status state
 */
export function getApiStatusState(): ApiStatusState {
  return apiStatusState;
}

/**
 * Checks if the application is currently in maintenance mode
 * @returns boolean - true if in maintenance mode
 */
export function isInMaintenanceMode(): boolean {
  return apiStatusState.status.value === "maintenance";
}

/**
 * Checks if the application is currently in system failure mode
 * @returns boolean - true if in system failure mode
 */
export function isInSystemFailureMode(): boolean {
  return apiStatusState.status.value === "systemFailure";
}

/**
 * Handles API response and determines the appropriate status
 * @param response - The API response
 * @param baseUrl - Optional base URL for the API
 * @returns boolean - true if status was changed from normal
 */
export function handleApiResponseForDownTime(
  response: any,
  baseUrl?: string
): boolean {
  if (!response || typeof response !== "object" || Array.isArray(response)) {
    setApiStatus("systemFailure", baseUrl);
    return true;
  }

  if (
    response.errors &&
    Array.isArray(response.errors) &&
    response.errors.length > 0
  ) {
    const firstError = response.errors[0];
    const status = determineStatusFromError(firstError);

    if (status === "normal") {
      return false;
    }

    setApiStatus(status, baseUrl);
    return true;
  }

  return false;
}
