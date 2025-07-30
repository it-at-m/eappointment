import { Ref, ref } from "vue";

export interface ErrorStateMap {
  apiErrorAppointmentCanNotBeCanceled: Ref<boolean>;
  apiErrorAppointmentNotAvailable: Ref<boolean>;
  apiErrorAppointmentNotFound: Ref<boolean>;
  apiErrorAuthKeyMismatch: Ref<boolean>;
  apiErrorCaptchaVerificationError: Ref<boolean>;
  apiErrorCaptchaVerificationFailed: Ref<boolean>;
  apiErrorDepartmentNotFound: Ref<boolean>;
  apiErrorEmailIsRequired: Ref<boolean>;
  apiErrorEmptyServiceArrays: Ref<boolean>;
  apiErrorInternalError: Ref<boolean>;
  apiErrorInvalidApiClient: Ref<boolean>;
  apiErrorInvalidAuthKey: Ref<boolean>;
  apiErrorInvalidCustomTextfield: Ref<boolean>;
  apiErrorInvalidDate: Ref<boolean>;
  apiErrorInvalidEmail: Ref<boolean>;
  apiErrorInvalidEndDate: Ref<boolean>;
  apiErrorInvalidFamilyName: Ref<boolean>;
  apiErrorInvalidLocationAndServiceCombination: Ref<boolean>;
  apiErrorInvalidOfficeId: Ref<boolean>;
  apiErrorInvalidProcessId: Ref<boolean>;
  apiErrorInvalidRequest: Ref<boolean>;
  apiErrorInvalidSchema: Ref<boolean>;
  apiErrorInvalidScopeId: Ref<boolean>;
  apiErrorInvalidServiceCount: Ref<boolean>;
  apiErrorInvalidServiceId: Ref<boolean>;
  apiErrorInvalidStartDate: Ref<boolean>;
  apiErrorInvalidTelephone: Ref<boolean>;
  apiErrorInvalidTimestamp: Ref<boolean>;
  apiErrorIpBlacklisted: Ref<boolean>;
  apiErrorMailNotFound: Ref<boolean>;
  apiErrorMismatchedArrays: Ref<boolean>;
  apiErrorNoAppointmentForThisDay: Ref<boolean>;
  apiErrorNoAppointmentForThisScope: Ref<boolean>;
  apiErrorNotFound: Ref<boolean>;
  apiErrorNotImplemented: Ref<boolean>;
  apiErrorOrganisationNotFound: Ref<boolean>;
  apiErrorPreconfirmationExpired: Ref<boolean>;
  apiErrorProcessAlreadyCalled: Ref<boolean>;
  apiErrorProcessAlreadyExists: Ref<boolean>;
  apiErrorProcessDeleteFailed: Ref<boolean>;
  apiErrorProcessInvalid: Ref<boolean>;
  apiErrorProcessNotPreconfirmedAnymore: Ref<boolean>;
  apiErrorProcessNotReservedAnymore: Ref<boolean>;
  apiErrorProviderNotFound: Ref<boolean>;
  apiErrorRateLimitExceeded: Ref<boolean>;
  apiErrorRequestDataTooLarge: Ref<boolean>;
  apiErrorRequestMethodNotAllowed: Ref<boolean>;
  apiErrorRequestNotFound: Ref<boolean>;
  apiErrorScopeNotFound: Ref<boolean>;
  apiErrorScopesNotFound: Ref<boolean>;
  apiErrorServiceUnavailable: Ref<boolean>;
  apiErrorSourceNotFound: Ref<boolean>;
  apiErrorTelephoneIsRequired: Ref<boolean>;
  apiErrorTooManyAppointmentsWithSameMail: Ref<boolean>;
  apiErrorUnknownError: Ref<boolean>;
  apiErrorZmsClientCommunicationError: Ref<boolean>;
  apiErrorGenericFallback: Ref<boolean>;
}

export interface ApiErrorTranslation {
  headerKey: string;
  textKey: string;
}

// Centralized error state management
export function createErrorStates() {
  // Create all error refs
  const apiErrorAppointmentCanNotBeCanceled = ref<boolean>(false);
  const apiErrorAppointmentNotAvailable = ref<boolean>(false);
  const apiErrorAppointmentNotFound = ref<boolean>(false);
  const apiErrorAuthKeyMismatch = ref<boolean>(false);
  const apiErrorCaptchaVerificationError = ref<boolean>(false);
  const apiErrorCaptchaVerificationFailed = ref<boolean>(false);
  const apiErrorDepartmentNotFound = ref<boolean>(false);
  const apiErrorEmailIsRequired = ref<boolean>(false);
  const apiErrorEmptyServiceArrays = ref<boolean>(false);
  const apiErrorInternalError = ref<boolean>(false);
  const apiErrorInvalidApiClient = ref<boolean>(false);
  const apiErrorInvalidAuthKey = ref<boolean>(false);
  const apiErrorInvalidCustomTextfield = ref<boolean>(false);
  const apiErrorInvalidDate = ref<boolean>(false);
  const apiErrorInvalidEmail = ref<boolean>(false);
  const apiErrorInvalidEndDate = ref<boolean>(false);
  const apiErrorInvalidFamilyName = ref<boolean>(false);
  const apiErrorInvalidLocationAndServiceCombination = ref<boolean>(false);
  const apiErrorInvalidOfficeId = ref<boolean>(false);
  const apiErrorInvalidProcessId = ref<boolean>(false);
  const apiErrorInvalidRequest = ref<boolean>(false);
  const apiErrorInvalidSchema = ref<boolean>(false);
  const apiErrorInvalidScopeId = ref<boolean>(false);
  const apiErrorInvalidServiceCount = ref<boolean>(false);
  const apiErrorInvalidServiceId = ref<boolean>(false);
  const apiErrorInvalidStartDate = ref<boolean>(false);
  const apiErrorInvalidTelephone = ref<boolean>(false);
  const apiErrorInvalidTimestamp = ref<boolean>(false);
  const apiErrorIpBlacklisted = ref<boolean>(false);
  const apiErrorMailNotFound = ref<boolean>(false);
  const apiErrorMismatchedArrays = ref<boolean>(false);
  const apiErrorNoAppointmentForThisDay = ref<boolean>(false);
  const apiErrorNoAppointmentForThisScope = ref<boolean>(false);
  const apiErrorNotFound = ref<boolean>(false);
  const apiErrorNotImplemented = ref<boolean>(false);
  const apiErrorOrganisationNotFound = ref<boolean>(false);
  const apiErrorPreconfirmationExpired = ref<boolean>(false);
  const apiErrorProcessAlreadyCalled = ref<boolean>(false);
  const apiErrorProcessAlreadyExists = ref<boolean>(false);
  const apiErrorProcessDeleteFailed = ref<boolean>(false);
  const apiErrorProcessInvalid = ref<boolean>(false);
  const apiErrorProcessNotPreconfirmedAnymore = ref<boolean>(false);
  const apiErrorProcessNotReservedAnymore = ref<boolean>(false);
  const apiErrorProviderNotFound = ref<boolean>(false);
  const apiErrorRateLimitExceeded = ref<boolean>(false);
  const apiErrorRequestDataTooLarge = ref<boolean>(false);
  const apiErrorRequestMethodNotAllowed = ref<boolean>(false);
  const apiErrorRequestNotFound = ref<boolean>(false);
  const apiErrorScopeNotFound = ref<boolean>(false);
  const apiErrorScopesNotFound = ref<boolean>(false);
  const apiErrorServiceUnavailable = ref<boolean>(false);
  const apiErrorSourceNotFound = ref<boolean>(false);
  const apiErrorTelephoneIsRequired = ref<boolean>(false);
  const apiErrorTooManyAppointmentsWithSameMail = ref<boolean>(false);
  const apiErrorUnknownError = ref<boolean>(false);
  const apiErrorZmsClientCommunicationError = ref<boolean>(false);
  const apiErrorGenericFallback = ref<boolean>(false);

  // Create error state map
  const errorStateMap: ErrorStateMap = {
    apiErrorAppointmentCanNotBeCanceled,
    apiErrorAppointmentNotAvailable,
    apiErrorAppointmentNotFound,
    apiErrorAuthKeyMismatch,
    apiErrorCaptchaVerificationError,
    apiErrorCaptchaVerificationFailed,
    apiErrorDepartmentNotFound,
    apiErrorEmailIsRequired,
    apiErrorEmptyServiceArrays,
    apiErrorInternalError,
    apiErrorInvalidApiClient,
    apiErrorInvalidAuthKey,
    apiErrorInvalidCustomTextfield,
    apiErrorInvalidDate,
    apiErrorInvalidEmail,
    apiErrorInvalidEndDate,
    apiErrorInvalidFamilyName,
    apiErrorInvalidLocationAndServiceCombination,
    apiErrorInvalidOfficeId,
    apiErrorInvalidProcessId,
    apiErrorInvalidRequest,
    apiErrorInvalidSchema,
    apiErrorInvalidScopeId,
    apiErrorInvalidServiceCount,
    apiErrorInvalidServiceId,
    apiErrorInvalidStartDate,
    apiErrorInvalidTelephone,
    apiErrorInvalidTimestamp,
    apiErrorIpBlacklisted,
    apiErrorMailNotFound,
    apiErrorMismatchedArrays,
    apiErrorNoAppointmentForThisDay,
    apiErrorNoAppointmentForThisScope,
    apiErrorNotFound,
    apiErrorNotImplemented,
    apiErrorOrganisationNotFound,
    apiErrorPreconfirmationExpired,
    apiErrorProcessAlreadyCalled,
    apiErrorProcessAlreadyExists,
    apiErrorProcessDeleteFailed,
    apiErrorProcessInvalid,
    apiErrorProcessNotPreconfirmedAnymore,
    apiErrorProcessNotReservedAnymore,
    apiErrorProviderNotFound,
    apiErrorRateLimitExceeded,
    apiErrorRequestDataTooLarge,
    apiErrorRequestMethodNotAllowed,
    apiErrorRequestNotFound,
    apiErrorScopeNotFound,
    apiErrorScopesNotFound,
    apiErrorServiceUnavailable,
    apiErrorSourceNotFound,
    apiErrorTelephoneIsRequired,
    apiErrorTooManyAppointmentsWithSameMail,
    apiErrorUnknownError,
    apiErrorZmsClientCommunicationError,
    apiErrorGenericFallback,
  };

  return {
    // Individual error refs for direct access when needed
    apiErrorAppointmentCanNotBeCanceled,
    apiErrorAppointmentNotAvailable,
    apiErrorAppointmentNotFound,
    apiErrorAuthKeyMismatch,
    apiErrorCaptchaVerificationError,
    apiErrorCaptchaVerificationFailed,
    apiErrorDepartmentNotFound,
    apiErrorEmailIsRequired,
    apiErrorEmptyServiceArrays,
    apiErrorInternalError,
    apiErrorInvalidApiClient,
    apiErrorInvalidAuthKey,
    apiErrorInvalidCustomTextfield,
    apiErrorInvalidDate,
    apiErrorInvalidEmail,
    apiErrorInvalidEndDate,
    apiErrorInvalidFamilyName,
    apiErrorInvalidLocationAndServiceCombination,
    apiErrorInvalidOfficeId,
    apiErrorInvalidProcessId,
    apiErrorInvalidRequest,
    apiErrorInvalidSchema,
    apiErrorInvalidScopeId,
    apiErrorInvalidServiceCount,
    apiErrorInvalidServiceId,
    apiErrorInvalidStartDate,
    apiErrorInvalidTelephone,
    apiErrorInvalidTimestamp,
    apiErrorIpBlacklisted,
    apiErrorMailNotFound,
    apiErrorMismatchedArrays,
    apiErrorNoAppointmentForThisDay,
    apiErrorNoAppointmentForThisScope,
    apiErrorNotFound,
    apiErrorNotImplemented,
    apiErrorOrganisationNotFound,
    apiErrorPreconfirmationExpired,
    apiErrorProcessAlreadyCalled,
    apiErrorProcessAlreadyExists,
    apiErrorProcessDeleteFailed,
    apiErrorProcessInvalid,
    apiErrorProcessNotPreconfirmedAnymore,
    apiErrorProcessNotReservedAnymore,
    apiErrorProviderNotFound,
    apiErrorRateLimitExceeded,
    apiErrorRequestDataTooLarge,
    apiErrorRequestMethodNotAllowed,
    apiErrorRequestNotFound,
    apiErrorScopeNotFound,
    apiErrorScopesNotFound,
    apiErrorServiceUnavailable,
    apiErrorSourceNotFound,
    apiErrorTelephoneIsRequired,
    apiErrorTooManyAppointmentsWithSameMail,
    apiErrorUnknownError,
    apiErrorZmsClientCommunicationError,
    apiErrorGenericFallback,
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

  // Map error code to the corresponding error state
  switch (errorCode) {
    case "appointmentCanNotBeCanceled":
      errorStates.apiErrorAppointmentCanNotBeCanceled.value = true;
      break;
    case "appointmentNotAvailable":
      errorStates.apiErrorAppointmentNotAvailable.value = true;
      break;
    case "appointmentNotFound":
      errorStates.apiErrorAppointmentNotFound.value = true;
      break;
    case "authKeyMismatch":
      errorStates.apiErrorAuthKeyMismatch.value = true;
      break;
    case "captchaVerificationError":
      errorStates.apiErrorCaptchaVerificationError.value = true;
      break;
    case "captchaVerificationFailed":
      errorStates.apiErrorCaptchaVerificationFailed.value = true;
      break;
    case "departmentNotFound":
      errorStates.apiErrorDepartmentNotFound.value = true;
      break;
    case "emailIsRequired":
      errorStates.apiErrorEmailIsRequired.value = true;
      break;
    case "emptyServiceArrays":
      errorStates.apiErrorEmptyServiceArrays.value = true;
      break;
    case "internalError":
      errorStates.apiErrorInternalError.value = true;
      break;
    case "invalidApiClient":
      errorStates.apiErrorInvalidApiClient.value = true;
      break;
    case "invalidAuthKey":
      errorStates.apiErrorInvalidAuthKey.value = true;
      break;
    case "invalidCustomTextfield":
      errorStates.apiErrorInvalidCustomTextfield.value = true;
      break;
    case "invalidDate":
      errorStates.apiErrorInvalidDate.value = true;
      break;
    case "invalidEmail":
      errorStates.apiErrorInvalidEmail.value = true;
      break;
    case "invalidEndDate":
      errorStates.apiErrorInvalidEndDate.value = true;
      break;
    case "invalidFamilyName":
      errorStates.apiErrorInvalidFamilyName.value = true;
      break;
    case "invalidLocationAndServiceCombination":
      errorStates.apiErrorInvalidLocationAndServiceCombination.value = true;
      break;
    case "invalidOfficeId":
      errorStates.apiErrorInvalidOfficeId.value = true;
      break;
    case "invalidProcessId":
      errorStates.apiErrorInvalidProcessId.value = true;
      break;
    case "invalidRequest":
      errorStates.apiErrorInvalidRequest.value = true;
      break;
    case "invalidSchema":
      errorStates.apiErrorInvalidSchema.value = true;
      break;
    case "invalidScopeId":
      errorStates.apiErrorInvalidScopeId.value = true;
      break;
    case "invalidServiceCount":
      errorStates.apiErrorInvalidServiceCount.value = true;
      break;
    case "invalidServiceId":
      errorStates.apiErrorInvalidServiceId.value = true;
      break;
    case "invalidStartDate":
      errorStates.apiErrorInvalidStartDate.value = true;
      break;
    case "invalidTelephoneNumber":
      errorStates.apiErrorInvalidTelephone.value = true;
      break;
    case "invalidTelephone":
      errorStates.apiErrorInvalidTelephone.value = true;
      break;
    case "invalidTimestamp":
      errorStates.apiErrorInvalidTimestamp.value = true;
      break;
    case "ipBlacklisted":
      errorStates.apiErrorIpBlacklisted.value = true;
      break;
    case "mailNotFound":
      errorStates.apiErrorMailNotFound.value = true;
      break;
    case "mismatchedArrays":
      errorStates.apiErrorMismatchedArrays.value = true;
      break;
    case "noAppointmentForThisDay":
      errorStates.apiErrorNoAppointmentForThisDay.value = true;
      break;
    case "noAppointmentForThisScope":
      errorStates.apiErrorNoAppointmentForThisScope.value = true;
      break;
    case "notFound":
      errorStates.apiErrorNotFound.value = true;
      break;
    case "notImplemented":
      errorStates.apiErrorNotImplemented.value = true;
      break;
    case "organisationNotFound":
      errorStates.apiErrorOrganisationNotFound.value = true;
      break;
    case "preconfirmationExpired":
      errorStates.apiErrorPreconfirmationExpired.value = true;
      break;
    case "processAlreadyCalled":
      errorStates.apiErrorProcessAlreadyCalled.value = true;
      break;
    case "processAlreadyExists":
      errorStates.apiErrorProcessAlreadyExists.value = true;
      break;
    case "processDeleteFailed":
      errorStates.apiErrorProcessDeleteFailed.value = true;
      break;
    case "processInvalid":
      errorStates.apiErrorProcessInvalid.value = true;
      break;
    case "processNotPreconfirmedAnymore":
      errorStates.apiErrorProcessNotPreconfirmedAnymore.value = true;
      break;
    case "processNotReservedAnymore":
      errorStates.apiErrorProcessNotReservedAnymore.value = true;
      break;
    case "providerNotFound":
      errorStates.apiErrorProviderNotFound.value = true;
      break;
    case "rateLimitExceeded":
      errorStates.apiErrorRateLimitExceeded.value = true;
      break;
    case "requestDataTooLarge":
      errorStates.apiErrorRequestDataTooLarge.value = true;
      break;
    case "requestMethodNotAllowed":
      errorStates.apiErrorRequestMethodNotAllowed.value = true;
      break;
    case "requestNotFound":
      errorStates.apiErrorRequestNotFound.value = true;
      break;
    case "scopeNotFound":
      errorStates.apiErrorScopeNotFound.value = true;
      break;
    case "scopesNotFound":
      errorStates.apiErrorScopesNotFound.value = true;
      break;
    case "serviceUnavailable":
      errorStates.apiErrorServiceUnavailable.value = true;
      break;
    case "sourceNotFound":
      errorStates.apiErrorSourceNotFound.value = true;
      break;
    case "telephoneIsRequired":
      errorStates.apiErrorTelephoneIsRequired.value = true;
      break;
    case "tooManyAppointmentsWithSameMail":
      errorStates.apiErrorTooManyAppointmentsWithSameMail.value = true;
      break;
    case "zmsClientCommunicationError":
      errorStates.apiErrorZmsClientCommunicationError.value = true;
      break;
    case "unknownError":
      errorStates.apiErrorUnknownError.value = true;
      break;
    default:
      errorStates.apiErrorGenericFallback.value = true; // Generic fallback
  }
}

export function getApiErrorTranslation(
  errorStates: ErrorStateMap
): ApiErrorTranslation {
  // Find the first active error state and return its translation
  const activeErrorState = Object.entries(errorStates).find(
    ([_, isActive]) => isActive.value
  );

  if (!activeErrorState) {
    // Default fallback for generic errors
    return {
      headerKey: "apiErrorGenericFallbackHeader",
      textKey: "apiErrorGenericFallbackText",
    };
  }

  const [errorStateName] = activeErrorState;

  // Map error state names to translation keys
  switch (errorStateName) {
    case "apiErrorAppointmentCanNotBeCanceled":
      return {
        headerKey: "apiErrorAppointmentCanNotBeCanceledHeader",
        textKey: "apiErrorAppointmentCanNotBeCanceledText",
      };
    case "apiErrorAppointmentNotAvailable":
      return {
        headerKey: "apiErrorAppointmentNotAvailableHeader",
        textKey: "apiErrorAppointmentNotAvailableText",
      };
    case "apiErrorAppointmentNotFound":
      return {
        headerKey: "apiErrorAppointmentNotFoundHeader",
        textKey: "apiErrorAppointmentNotFoundText",
      };
    case "apiErrorAuthKeyMismatch":
      return {
        headerKey: "apiErrorAuthKeyMismatchHeader",
        textKey: "apiErrorAuthKeyMismatchText",
      };
    case "apiErrorCaptchaVerificationError":
      return {
        headerKey: "apiErrorCaptchaVerificationErrorHeader",
        textKey: "apiErrorCaptchaVerificationErrorText",
      };
    case "apiErrorCaptchaVerificationFailed":
      return {
        headerKey: "apiErrorCaptchaVerificationFailedHeader",
        textKey: "apiErrorCaptchaVerificationFailedText",
      };
    case "apiErrorDepartmentNotFound":
      return {
        headerKey: "apiErrorDepartmentNotFoundHeader",
        textKey: "apiErrorDepartmentNotFoundText",
      };
    case "apiErrorEmailIsRequired":
      return {
        headerKey: "apiErrorEmailIsRequiredHeader",
        textKey: "apiErrorEmailIsRequiredText",
      };
    case "apiErrorEmptyServiceArrays":
      return {
        headerKey: "apiErrorEmptyServiceArraysHeader",
        textKey: "apiErrorEmptyServiceArraysText",
      };
    case "apiErrorInternalError":
      return {
        headerKey: "apiErrorInternalErrorHeader",
        textKey: "apiErrorInternalErrorText",
      };
    case "apiErrorInvalidApiClient":
      return {
        headerKey: "apiErrorInvalidApiClientHeader",
        textKey: "apiErrorInvalidApiClientText",
      };
    case "apiErrorInvalidAuthKey":
      return {
        headerKey: "apiErrorInvalidAuthKeyHeader",
        textKey: "apiErrorInvalidAuthKeyText",
      };
    case "apiErrorInvalidCustomTextfield":
      return {
        headerKey: "apiErrorInvalidCustomTextfieldHeader",
        textKey: "apiErrorInvalidCustomTextfieldText",
      };
    case "apiErrorInvalidDate":
      return {
        headerKey: "apiErrorInvalidDateHeader",
        textKey: "apiErrorInvalidDateText",
      };
    case "apiErrorInvalidEmail":
      return {
        headerKey: "apiErrorInvalidEmailHeader",
        textKey: "apiErrorInvalidEmailText",
      };
    case "apiErrorInvalidEndDate":
      return {
        headerKey: "apiErrorInvalidEndDateHeader",
        textKey: "apiErrorInvalidEndDateText",
      };
    case "apiErrorInvalidFamilyName":
      return {
        headerKey: "apiErrorInvalidFamilyNameHeader",
        textKey: "apiErrorInvalidFamilyNameText",
      };
    case "apiErrorInvalidLocationAndServiceCombination":
      return {
        headerKey: "apiErrorInvalidLocationAndServiceCombinationHeader",
        textKey: "apiErrorInvalidLocationAndServiceCombinationText",
      };
    case "apiErrorInvalidOfficeId":
      return {
        headerKey: "apiErrorInvalidOfficeIdHeader",
        textKey: "apiErrorInvalidOfficeIdText",
      };
    case "apiErrorInvalidProcessId":
      return {
        headerKey: "apiErrorInvalidProcessIdHeader",
        textKey: "apiErrorInvalidProcessIdText",
      };
    case "apiErrorInvalidRequest":
      return {
        headerKey: "apiErrorInvalidRequestHeader",
        textKey: "apiErrorInvalidRequestText",
      };
    case "apiErrorInvalidSchema":
      return {
        headerKey: "apiErrorInvalidSchemaHeader",
        textKey: "apiErrorInvalidSchemaText",
      };
    case "apiErrorInvalidScopeId":
      return {
        headerKey: "apiErrorInvalidScopeIdHeader",
        textKey: "apiErrorInvalidScopeIdText",
      };
    case "apiErrorInvalidServiceCount":
      return {
        headerKey: "apiErrorInvalidServiceCountHeader",
        textKey: "apiErrorInvalidServiceCountText",
      };
    case "apiErrorInvalidServiceId":
      return {
        headerKey: "apiErrorInvalidServiceIdHeader",
        textKey: "apiErrorInvalidServiceIdText",
      };
    case "apiErrorInvalidStartDate":
      return {
        headerKey: "apiErrorInvalidStartDateHeader",
        textKey: "apiErrorInvalidStartDateText",
      };
    case "apiErrorInvalidTelephone":
      return {
        headerKey: "apiErrorInvalidTelephoneHeader",
        textKey: "apiErrorInvalidTelephoneText",
      };
    case "apiErrorInvalidTimestamp":
      return {
        headerKey: "apiErrorInvalidTimestampHeader",
        textKey: "apiErrorInvalidTimestampText",
      };
    case "apiErrorIpBlacklisted":
      return {
        headerKey: "apiErrorIpBlacklistedHeader",
        textKey: "apiErrorIpBlacklistedText",
      };
    case "apiErrorMailNotFound":
      return {
        headerKey: "apiErrorMailNotFoundHeader",
        textKey: "apiErrorMailNotFoundText",
      };
    case "apiErrorMismatchedArrays":
      return {
        headerKey: "apiErrorMismatchedArraysHeader",
        textKey: "apiErrorMismatchedArraysText",
      };
    case "apiErrorNoAppointmentForThisDay":
      return {
        headerKey: "apiErrorNoAppointmentForThisDayHeader",
        textKey: "apiErrorNoAppointmentForThisDayText",
      };
    case "apiErrorNoAppointmentForThisScope":
      return {
        headerKey: "apiErrorNoAppointmentForThisScopeHeader",
        textKey: "apiErrorNoAppointmentForThisScopeText",
      };
    case "apiErrorNotFound":
      return {
        headerKey: "apiErrorNotFoundHeader",
        textKey: "apiErrorNotFoundText",
      };
    case "apiErrorNotImplemented":
      return {
        headerKey: "apiErrorNotImplementedHeader",
        textKey: "apiErrorNotImplementedText",
      };
    case "apiErrorOrganisationNotFound":
      return {
        headerKey: "apiErrorOrganisationNotFoundHeader",
        textKey: "apiErrorOrganisationNotFoundText",
      };
    case "apiErrorPreconfirmationExpired":
      return {
        headerKey: "apiErrorPreconfirmationExpiredHeader",
        textKey: "apiErrorPreconfirmationExpiredText",
      };
    case "apiErrorProcessAlreadyCalled":
      return {
        headerKey: "apiErrorProcessAlreadyCalledHeader",
        textKey: "apiErrorProcessAlreadyCalledText",
      };
    case "apiErrorProcessAlreadyExists":
      return {
        headerKey: "apiErrorProcessAlreadyExistsHeader",
        textKey: "apiErrorProcessAlreadyExistsText",
      };
    case "apiErrorProcessDeleteFailed":
      return {
        headerKey: "apiErrorProcessDeleteFailedHeader",
        textKey: "apiErrorProcessDeleteFailedText",
      };
    case "apiErrorProcessInvalid":
      return {
        headerKey: "apiErrorProcessInvalidHeader",
        textKey: "apiErrorProcessInvalidText",
      };
    case "apiErrorProcessNotPreconfirmedAnymore":
      return {
        headerKey: "apiErrorProcessNotPreconfirmedAnymoreHeader",
        textKey: "apiErrorProcessNotPreconfirmedAnymoreText",
      };
    case "apiErrorProcessNotReservedAnymore":
      return {
        headerKey: "apiErrorProcessNotReservedAnymoreHeader",
        textKey: "apiErrorProcessNotReservedAnymoreText",
      };
    case "apiErrorProviderNotFound":
      return {
        headerKey: "apiErrorProviderNotFoundHeader",
        textKey: "apiErrorProviderNotFoundText",
      };
    case "apiErrorRateLimitExceeded":
      return {
        headerKey: "apiErrorRateLimitExceededHeader",
        textKey: "apiErrorRateLimitExceededText",
      };
    case "apiErrorRequestDataTooLarge":
      return {
        headerKey: "apiErrorRequestDataTooLargeHeader",
        textKey: "apiErrorRequestDataTooLargeText",
      };
    case "apiErrorRequestMethodNotAllowed":
      return {
        headerKey: "apiErrorRequestMethodNotAllowedHeader",
        textKey: "apiErrorRequestMethodNotAllowedText",
      };
    case "apiErrorRequestNotFound":
      return {
        headerKey: "apiErrorRequestNotFoundHeader",
        textKey: "apiErrorRequestNotFoundText",
      };
    case "apiErrorScopeNotFound":
      return {
        headerKey: "apiErrorScopeNotFoundHeader",
        textKey: "apiErrorScopeNotFoundText",
      };
    case "apiErrorScopesNotFound":
      return {
        headerKey: "apiErrorScopesNotFoundHeader",
        textKey: "apiErrorScopesNotFoundText",
      };
    case "apiErrorServiceUnavailable":
      return {
        headerKey: "apiErrorServiceUnavailableHeader",
        textKey: "apiErrorServiceUnavailableText",
      };
    case "apiErrorSourceNotFound":
      return {
        headerKey: "apiErrorSourceNotFoundHeader",
        textKey: "apiErrorSourceNotFoundText",
      };
    case "apiErrorTelephoneIsRequired":
      return {
        headerKey: "apiErrorTelephoneIsRequiredHeader",
        textKey: "apiErrorTelephoneIsRequiredText",
      };
    case "apiErrorTooManyAppointmentsWithSameMail":
      return {
        headerKey: "apiErrorTooManyAppointmentsWithSameMailHeader",
        textKey: "apiErrorTooManyAppointmentsWithSameMailText",
      };
    case "apiErrorUnknownError":
      return {
        headerKey: "apiErrorUnknownErrorHeader",
        textKey: "apiErrorUnknownErrorText",
      };
    case "apiErrorZmsClientCommunicationError":
      return {
        headerKey: "apiErrorZmsClientCommunicationErrorHeader",
        textKey: "apiErrorZmsClientCommunicationErrorText",
      };
    case "apiErrorGenericFallback":
      return {
        headerKey: "apiErrorGenericFallbackHeader",
        textKey: "apiErrorGenericFallbackText",
      };
    default:
      // Default fallback for generic errors
      return {
        headerKey: "apiErrorGenericFallbackHeader",
        textKey: "apiErrorGenericFallbackText",
      };
  }
}

export function handleApiResponse(data: any, errorStates: ErrorStateMap): void {
  const firstErrorCode = data?.errors?.[0]?.errorCode ?? "";
  if (firstErrorCode) {
    handleApiError(firstErrorCode, errorStates);
  }
}

export function hasAnyApiError(errorStates: ErrorStateMap): boolean {
  return Object.values(errorStates).some((errorState) => errorState.value);
}

// Context-specific error detection methods
// Each method shows any error that occurs in that specific context
// The component determines which context is active based on the current state
export function hasUpdateContextError(
  errorStates: ErrorStateMap,
  activeContext?: string
): boolean {
  return hasAnyApiError(errorStates) && activeContext === "update";
}

export function hasConfirmContextError(
  errorStates: ErrorStateMap,
  activeContext?: string
): boolean {
  return hasAnyApiError(errorStates) && activeContext === "confirm";
}

export function hasPreconfirmContextError(
  errorStates: ErrorStateMap,
  activeContext?: string
): boolean {
  return hasAnyApiError(errorStates) && activeContext === "preconfirm";
}

export function hasCancelContextError(
  errorStates: ErrorStateMap,
  activeContext?: string
): boolean {
  return hasAnyApiError(errorStates) && activeContext === "cancel";
}

export function hasInitializationContextError(
  errorStates: ErrorStateMap,
  activeContext?: string
): boolean {
  return hasAnyApiError(errorStates) && activeContext === "initialization";
}
