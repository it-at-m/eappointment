import { Ref, ref } from "vue";

export type ErrorStateMap = Record<string, Ref<boolean>>;

// Helper function to create error states with type safety
export function createErrorStateMap(): ErrorStateMap {
  const errorStateMap: ErrorStateMap = {};

  // Define all error state names
  const errorStateNames = [
    "apiErrorAppointmentCanNotBeCanceled",
    "apiErrorAppointmentNotAvailable",
    "apiErrorAppointmentNotFound",
    "apiErrorAuthKeyMismatch",
    "apiErrorCaptchaExpired",
    "apiErrorCaptchaInvalid",
    "apiErrorCaptchaMissing",
    "apiErrorCaptchaVerificationError",
    "apiErrorCaptchaVerificationFailed",
    "apiErrorDepartmentNotFound",
    "apiErrorEmailIsRequired",
    "apiErrorEmptyServiceArrays",
    "apiErrorInternalError",
    "apiErrorInvalidApiClient",
    "apiErrorInvalidAuthKey",
    "apiErrorInvalidCustomTextfield",
    "apiErrorInvalidDate",
    "apiErrorInvalidEmail",
    "apiErrorInvalidEndDate",
    "apiErrorInvalidFamilyName",
    "apiErrorInvalidLocationAndServiceCombination",
    "apiErrorInvalidOfficeId",
    "apiErrorInvalidProcessId",
    "apiErrorInvalidRequest",
    "apiErrorInvalidSchema",
    "apiErrorInvalidScopeId",
    "apiErrorInvalidServiceCount",
    "apiErrorInvalidServiceId",
    "apiErrorInvalidStartDate",
    "apiErrorInvalidTelephone",
    "apiErrorInvalidTimestamp",
    "apiErrorIpBlacklisted",
    "apiErrorMailNotFound",
    "apiErrorMismatchedArrays",
    "apiErrorNoAppointmentForThisDay",
    "apiErrorNoAppointmentForThisScope",
    "apiErrorNotFound",
    "apiErrorNotImplemented",
    "apiErrorOrganisationNotFound",
    "apiErrorPreconfirmationExpired",
    "apiErrorProcessAlreadyCalled",
    "apiErrorProcessAlreadyExists",
    "apiErrorProcessDeleteFailed",
    "apiErrorProcessInvalid",
    "apiErrorProcessNotPreconfirmedAnymore",
    "apiErrorProcessNotReservedAnymore",
    "apiErrorProviderNotFound",
    "apiErrorRateLimitExceeded",
    "apiErrorRequestDataTooLarge",
    "apiErrorRequestMethodNotAllowed",
    "apiErrorRequestNotFound",
    "apiErrorScopeNotFound",
    "apiErrorScopesNotFound",
    "apiErrorServiceUnavailable",
    "apiErrorSourceNotFound",
    "apiErrorTelephoneIsRequired",
    "apiErrorTooManyAppointmentsWithSameMail",
    "apiErrorUnknownError",
    "apiErrorZmsClientCommunicationError",
    "apiErrorGenericFallback",
  ] as const;

  // Create refs for each error state
  errorStateNames.forEach((name) => {
    errorStateMap[name] = ref<boolean>(false);
  });

  return errorStateMap;
}

export interface ApiErrorTranslation {
  headerKey: string;
  textKey: string;
}

// Centralized error state management
export function createErrorStates() {
  // Create error state map using helper function
  const errorStateMap = createErrorStateMap();

  return {
    // Individual error refs for direct access when needed
    ...errorStateMap,
    // Error state map for centralized operations
    errorStateMap,
  };
}

/**
 * Centralized error handler that maps API error codes to reactive error state variables
 * @param errorCode - The error code from the API response
 * @param errorStates - Object containing all reactive error state variables
 * @returns void - Sets the appropriate error state to true
 */
export function handleApiError(
  errorCode: string,
  errorStates: ErrorStateMap
): void {
  // Reset all error states first
  Object.values(errorStates).forEach((errorState) => {
    errorState.value = false;
  });

  // Dynamic error state mapping
  const errorStateKey = `apiError${errorCode.charAt(0).toUpperCase() + errorCode.slice(1)}`;

  if (errorStates[errorStateKey]) {
    errorStates[errorStateKey].value = true;
  } else {
    errorStates.apiErrorGenericFallback.value = true;
  }
}

export function getApiErrorTranslation(
  errorStates: ErrorStateMap
): ApiErrorTranslation {
  // Find the first active error state
  const activeErrorState = Object.entries(errorStates).find(
    ([_, isActive]) => isActive.value
  );

  if (!activeErrorState) {
    return {
      headerKey: "apiErrorGenericFallbackHeader",
      textKey: "apiErrorGenericFallbackText",
    };
  }

  const [errorStateName] = activeErrorState;

  // Dynamic translation key generation
  return {
    headerKey: `${errorStateName}Header`,
    textKey: `${errorStateName}Text`,
  };
}

export function handleApiResponse(data: any, errorStates: ErrorStateMap): void {
  if (!data || typeof data !== "object" || Array.isArray(data)) {
    errorStates.apiErrorGenericFallback.value = true;
    return;
  }

  const firstErrorCode = data?.errors?.[0]?.errorCode ?? "";
  if (firstErrorCode) {
    handleApiError(firstErrorCode, errorStates);
  } else if (data.errors && data.errors.length > 0) {
    errorStates.apiErrorGenericFallback.value = true;
  } else if (data.errors && data.errors.length === 0) {
    // Handle case where errors array exists but is empty
    errorStates.apiErrorGenericFallback.value = true;
  }
}

export function hasAnyApiError(errorStates: ErrorStateMap): boolean {
  return Object.values(errorStates).some((errorState) => errorState.value);
}

// Context-specific error detection methods
// Each method shows any error that occurs in that specific context
// The component determines which context is active based on the current state
export function hasContextError(
  errorStates: ErrorStateMap,
  context: string,
  activeContext?: string
): boolean {
  return hasAnyApiError(errorStates) && activeContext === context;
}

export const hasUpdateContextError = (
  errorStates: ErrorStateMap,
  activeContext?: string
) => hasContextError(errorStates, "update", activeContext);
export const hasConfirmContextError = (
  errorStates: ErrorStateMap,
  activeContext?: string
) => hasContextError(errorStates, "confirm", activeContext);
export const hasPreconfirmContextError = (
  errorStates: ErrorStateMap,
  activeContext?: string
) => hasContextError(errorStates, "preconfirm", activeContext);
export const hasCancelContextError = (
  errorStates: ErrorStateMap,
  activeContext?: string
) => hasContextError(errorStates, "cancel", activeContext);
export const hasInitializationContextError = (
  errorStates: ErrorStateMap,
  activeContext?: string
) => hasContextError(errorStates, "initialization", activeContext);
