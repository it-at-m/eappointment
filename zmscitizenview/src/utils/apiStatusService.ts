import { ref, Ref } from "vue";

import { fetchServicesAndProviders } from "@/api/ZMSAppointmentAPI";

export type ApiStatusType = "normal" | "maintenance" | "systemFailure";

export interface ApiStatusState {
  status: Ref<ApiStatusType>;
  lastCheckTime: Ref<number | null>;
  checkInterval: Ref<number | null>;
  baseUrl: Ref<string | undefined>;
}

// Global API status state
const apiStatusState: ApiStatusState = {
  status: ref<ApiStatusType>("normal"),
  lastCheckTime: ref(null),
  checkInterval: ref(null),
  baseUrl: ref(undefined),
};

// Check interval in milliseconds (30 seconds)
const CHECK_INTERVAL_MS = 30000;

/**
 * Checks if the offices-and-services API is available and determines the appropriate status
 * @param baseUrl - Optional base URL for the API
 * @returns Promise<ApiStatusType> - The current API status
 */
export async function checkApiStatus(baseUrl?: string): Promise<ApiStatusType> {
  try {
    // Make a simple request to the offices-and-services endpoint
    const response = await fetchServicesAndProviders(
      undefined,
      undefined,
      baseUrl
    );

    // If we get a response without errors, the API is available
    if (response && !(response as any).errors) {
      return "normal";
    }

    // Check if the error is a rate limit error (which should not trigger any special mode)
    const firstError = (response as any)?.errors?.[0];
    if (firstError?.errorCode === "rateLimitExceeded") {
      return "normal"; // Rate limit is handled by normal error handling
    }

    // Determine status based on error type
    if (firstError?.statusCode === 503) {
      return "maintenance"; // 503 Service Unavailable is specifically for maintenance
    }

    // Check for maintenance mode via error code (API gateway converts 503 to 400)
    if (firstError?.errorCode === "serviceUnavailable") {
      return "maintenance"; // Maintenance mode via error code
    }

    if (firstError?.statusCode >= 500 || firstError?.statusCode === 0) {
      return "systemFailure"; // Other 500+ errors or network failures
    }

    if (firstError?.statusCode >= 400 && firstError?.statusCode < 500) {
      return "maintenance"; // 400-499 errors
    }

    return "normal";
  } catch (error) {
    // Network errors, timeouts, etc. indicate system failure
    console.warn("API status check failed:", error);
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

  // Clear any existing interval
  if (apiStatusState.checkInterval.value) {
    clearInterval(apiStatusState.checkInterval.value);
    apiStatusState.checkInterval.value = null;
  }

  // Start checking for recovery if not in normal status
  if (status !== "normal") {
    apiStatusState.checkInterval.value = setInterval(async () => {
      const currentStatus = await checkApiStatus(baseUrl);
      if (currentStatus === "normal") {
        setApiStatus("normal");
      } else if (currentStatus !== status) {
        // Status changed (e.g., from maintenance to system failure)
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
 * Checks if the application is in any error state (maintenance or system failure)
 * @returns boolean - true if in any error state
 */
export function isInErrorState(): boolean {
  return apiStatusState.status.value !== "normal";
}

/**
 * Handles API response and determines the appropriate status
 * @param response - The API response
 * @param baseUrl - Optional base URL for the API
 * @returns boolean - true if status was changed from normal
 */
export function handleApiResponse(response: any, baseUrl?: string): boolean {
  // If response is null/undefined or not an object, activate system failure
  if (!response || typeof response !== "object" || Array.isArray(response)) {
    setApiStatus("systemFailure", baseUrl);
    return true;
  }

  // Check for errors in the response
  if (
    response.errors &&
    Array.isArray(response.errors) &&
    response.errors.length > 0
  ) {
    const firstError = response.errors[0];

    // Don't change status for rate limit errors
    if (firstError.errorCode === "rateLimitExceeded") {
      return false;
    }

    // Determine appropriate status based on error type
    if (firstError.statusCode === 503) {
      setApiStatus("maintenance", baseUrl);
      return true;
    }

    // Check for maintenance mode via error code (API gateway converts 503 to 400)
    if (firstError.errorCode === "serviceUnavailable") {
      setApiStatus("maintenance", baseUrl);
      return true;
    }

    if (firstError.statusCode >= 500 || firstError.statusCode === 0) {
      setApiStatus("systemFailure", baseUrl);
      return true;
    }

    if (firstError.statusCode >= 400 && firstError.statusCode < 500) {
      setApiStatus("maintenance", baseUrl);
      return true;
    }
  }

  // No errors, API is working normally
  return false;
}

/**
 * Handles fetch errors and determines the appropriate status
 * @param error - The fetch error
 * @param baseUrl - Optional base URL for the API
 * @returns boolean - true if status was changed from normal
 */
export function handleFetchError(error: any, baseUrl?: string): boolean {
  // Any fetch error (network, timeout, etc.) should activate system failure mode
  setApiStatus("systemFailure", baseUrl);
  return true;
}

// Legacy compatibility functions for existing code
export const getMaintenanceState = () => ({
  isInMaintenance: apiStatusState.status,
  lastCheckTime: apiStatusState.lastCheckTime,
  checkInterval: apiStatusState.checkInterval,
});

export const getSystemFailureState = () => ({
  isInSystemFailure: ref(apiStatusState.status.value === "systemFailure"),
  lastCheckTime: apiStatusState.lastCheckTime,
  checkInterval: apiStatusState.checkInterval,
});

export const handleApiResponseForMaintenance = (
  response: any,
  baseUrl?: string
) => {
  const wasChanged = handleApiResponse(response, baseUrl);
  return wasChanged && isInMaintenanceMode();
};

export const handleApiResponseForSystemFailure = (
  response: any,
  baseUrl?: string
) => {
  const wasChanged = handleApiResponse(response, baseUrl);
  return wasChanged && isInSystemFailureMode();
};
