import type { CalloutType } from "@/utils/callout";

import { Ref, ref } from "vue";

import { toCalloutType } from "@/utils/callout";

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
    "apiErrorSessionTimeout",
    "apiErrorSourceNotFound",
    "apiErrorTelephoneIsRequired",
    "apiErrorTooManyAppointmentsWithSameMail",
    "apiErrorTooManySlotsPerAppointment",
    "apiErrorUnknownError",
    "apiErrorZmsClientCommunicationError",
    "apiErrorInvalidJumpinLink",
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
  errorType?: CalloutType;
}

export interface ApiErrorData {
  errorCode: string;
  errorType: CalloutType;
  errorMessage: string;
  statusCode: number;
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
    // Current error data for type information
    currentErrorData: ref<ApiErrorData | null>(null),
  };
}

export const clearContextErrors = (errorStateMap: ErrorStateMap) => {
  Object.values(errorStateMap).forEach((errorState) => {
    errorState.value = false;
  });
};

/**
 * Centralized error handler that maps API error codes to reactive error state variables
 * @param errorCode - The error code from the API response
 * @param errorStates - Object containing all reactive error state variables
 * @returns void - Sets the appropriate error state to true
 */
export function handleApiError(
  errorCode: string,
  errorStates: ErrorStateMap,
  currentErrorData?: Ref<ApiErrorData | null>,
  errorType?: CalloutType
): void {
  // Reset all error states first
  Object.values(errorStates).forEach((errorState) => {
    errorState.value = false;
  });

  // Dynamic error state mapping
  const errorStateKey = `apiError${errorCode.charAt(0).toUpperCase() + errorCode.slice(1)}`;

  if (errorStates[errorStateKey]) {
    errorStates[errorStateKey].value = true;

    // Set the current error data with the error type from the API
    if (currentErrorData) {
      currentErrorData.value = {
        errorCode: errorCode,
        errorType: toCalloutType(errorType),
        errorMessage: "",
        statusCode: 400,
      };
    }
  } else {
    errorStates.apiErrorGenericFallback.value = true;

    if (currentErrorData) {
      currentErrorData.value = {
        errorCode: "genericFallback",
        errorType: "error",
        errorMessage: "An unknown error occurred.",
        statusCode: 520,
      };
    }
  }
}

export function getApiErrorTranslation(
  errorStates: ErrorStateMap,
  currentErrorData?: Ref<ApiErrorData | null>
): ApiErrorTranslation {
  // Find the first active error state
  const activeErrorState = Object.entries(errorStates).find(
    ([_, isActive]) => isActive.value
  );

  if (!activeErrorState) {
    return {
      headerKey: "apiErrorGenericFallbackHeader",
      textKey: "apiErrorGenericFallbackText",
      errorType: "error",
    };
  }

  const [errorStateName] = activeErrorState;

  // Use the stored error type if available, otherwise default to "error"
  const errorType =
    currentErrorData?.value?.errorType || ("error" as CalloutType);

  // Dynamic translation key generation
  return {
    headerKey: `${errorStateName}Header`,
    textKey: `${errorStateName}Text`,
    errorType,
  };
}

export function handleApiResponse(
  data: any,
  errorStates: ErrorStateMap,
  currentErrorData?: Ref<ApiErrorData | null>
): void {
  if (!data || typeof data !== "object" || Array.isArray(data)) {
    errorStates.apiErrorGenericFallback.value = true;
    if (currentErrorData) {
      currentErrorData.value = {
        errorCode: "genericFallback",
        errorType: "error",
        errorMessage: "An unknown error occurred.",
        statusCode: 520,
      };
    }
    return;
  }

  const firstError = data?.errors?.[0];
  if (firstError && firstError.errorCode) {
    handleApiError(
      firstError.errorCode,
      errorStates,
      currentErrorData,
      toCalloutType(firstError.errorType)
    );
    // Store the error data for type information
    if (currentErrorData) {
      currentErrorData.value = {
        errorCode: firstError.errorCode,
        errorType: toCalloutType(firstError.errorType),
        errorMessage: firstError.errorMessage || "",
        statusCode: firstError.statusCode || 400,
      };
    }
  } else if (data.errors && data.errors.length > 0) {
    errorStates.apiErrorGenericFallback.value = true;
    if (currentErrorData) {
      currentErrorData.value = {
        errorCode: "genericFallback",
        errorType: "error",
        errorMessage: "An unknown error occurred.",
        statusCode: 520,
      };
    }
  } else if (data.errors && data.errors.length === 0) {
    // Handle case where errors array exists but is empty
    errorStates.apiErrorGenericFallback.value = true;
    if (currentErrorData) {
      currentErrorData.value = {
        errorCode: "genericFallback",
        errorType: "error",
        errorMessage: "An unknown error occurred.",
        statusCode: 520,
      };
    }
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
