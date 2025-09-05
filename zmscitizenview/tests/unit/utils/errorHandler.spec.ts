import { describe, expect, it, beforeEach } from "vitest";
import { ref } from "vue";

// @ts-expect-error: Vue SFC import for test
import { handleApiResponse, createErrorStates, handleApiError, type ApiErrorData } from "@/utils/errorHandler";

describe("errorHandler", () => {
  let errorStates: ReturnType<typeof createErrorStates>;

  beforeEach(() => {
    errorStates = createErrorStates();
  });

  describe("handleApiResponse", () => {
    it("should set apiErrorGenericFallback when data is null", () => {
      handleApiResponse(null, errorStates.errorStateMap);
      
      expect(errorStates.apiErrorGenericFallback.value).toBe(true);
    });

    it("should set apiErrorGenericFallback when data is undefined", () => {
      handleApiResponse(undefined, errorStates.errorStateMap);
      
      expect(errorStates.apiErrorGenericFallback.value).toBe(true);
    });

    it("should set apiErrorGenericFallback when data is not an object", () => {
      handleApiResponse("string data", errorStates.errorStateMap);
      
      expect(errorStates.apiErrorGenericFallback.value).toBe(true);
    });

    it("should set apiErrorGenericFallback when data is a number", () => {
      handleApiResponse(123, errorStates.errorStateMap);
      
      expect(errorStates.apiErrorGenericFallback.value).toBe(true);
    });

    it("should set apiErrorGenericFallback when data is an array", () => {
      handleApiResponse([1, 2, 3], errorStates.errorStateMap);
      
      expect(errorStates.apiErrorGenericFallback.value).toBe(true);
    });

    it("should set apiErrorGenericFallback when errors array exists but has no errorCode", () => {
      const invalidResponse = {
        errors: [
          { message: "Some error message" },
          { status: "error" }
        ]
      };
      
      handleApiResponse(invalidResponse, errorStates.errorStateMap);
      
      expect(errorStates.apiErrorGenericFallback.value).toBe(true);
    });

    it("should set apiErrorGenericFallback when errors array is empty", () => {
      const responseWithEmptyErrors = {
        errors: []
      };
      
      handleApiResponse(responseWithEmptyErrors, errorStates.errorStateMap);
      
      expect(errorStates.apiErrorGenericFallback.value).toBe(true);
    });

    it("should handle valid error response with errorCode", () => {
      const validErrorResponse = {
        errors: [
          { errorCode: "tooManyAppointmentsWithSameMail", errorMessage: "Too many appointments" }
        ]
      };
      
      handleApiResponse(validErrorResponse, errorStates.errorStateMap);
      
      expect(errorStates.apiErrorTooManyAppointmentsWithSameMail.value).toBe(true);
      expect(errorStates.apiErrorGenericFallback.value).toBe(false);
    });

    it("should use dynamic errorType from API response", () => {
      const currentErrorData = ref<ApiErrorData | null>(null);
      const responseWithErrorType = {
        errors: [
          { 
            errorCode: "noAppointmentForThisScope", 
            errorMessage: "No appointments available",
            errorType: "info",
            statusCode: 404
          }
        ]
      };
      
      handleApiResponse(responseWithErrorType, errorStates.errorStateMap, currentErrorData);
      
      expect(errorStates.apiErrorNoAppointmentForThisScope.value).toBe(true);
      expect(currentErrorData.value).toEqual({
        errorCode: "noAppointmentForThisScope",
        errorType: "info",
        errorMessage: "No appointments available",
        statusCode: 404
      });
    });

    it("should use dynamic errorType for warning errors", () => {
      const currentErrorData = ref<ApiErrorData | null>(null);
      const responseWithWarningType = {
        errors: [
          { 
            errorCode: "invalidEmail", 
            errorMessage: "Invalid email format",
            errorType: "warning",
            statusCode: 400
          }
        ]
      };
      
      handleApiResponse(responseWithWarningType, errorStates.errorStateMap, currentErrorData);
      
      expect(currentErrorData.value?.errorType).toBe("warning");
      expect(currentErrorData.value?.errorCode).toBe("invalidEmail");
    });

    it("should use dynamic errorType for error type errors", () => {
      const currentErrorData = ref<ApiErrorData | null>(null);
      const responseWithErrorType = {
        errors: [
          { 
            errorCode: "appointmentNotFound", 
            errorMessage: "Appointment not found",
            errorType: "error",
            statusCode: 404
          }
        ]
      };
      
      handleApiResponse(responseWithErrorType, errorStates.errorStateMap, currentErrorData);
      
      expect(currentErrorData.value?.errorType).toBe("error");
      expect(currentErrorData.value?.errorCode).toBe("appointmentNotFound");
    });

    it("should default to 'error' when errorType is missing from API response", () => {
      const currentErrorData = ref<ApiErrorData | null>(null);
      const responseWithoutErrorType = {
        errors: [
          { 
            errorCode: "someUnknownError", 
            errorMessage: "Some error message",
            statusCode: 500
          }
        ]
      };
      
      handleApiResponse(responseWithoutErrorType, errorStates.errorStateMap, currentErrorData);
      
      expect(currentErrorData.value?.errorType).toBe("error");
      expect(currentErrorData.value?.errorCode).toBe("someUnknownError");
    });

    it("should handle mixed errors array with some having errorCode and some not", () => {
      const mixedErrorResponse = {
        errors: [
          { errorCode: "appointmentNotFound", errorMessage: "Not found" },
          { message: "Some other error without errorCode" },
          { errorCode: "invalidEmail", errorMessage: "Invalid email" }
        ]
      };
      
      handleApiResponse(mixedErrorResponse, errorStates.errorStateMap);
      
      expect(errorStates.apiErrorAppointmentNotFound.value).toBe(true);
      expect(errorStates.apiErrorGenericFallback.value).toBe(false);
    });

    it("should handle empty object response", () => {
      handleApiResponse({}, errorStates.errorStateMap);
      
      expect(errorStates.apiErrorGenericFallback.value).toBe(false);
    });

    it("should handle object without errors property", () => {
      const responseWithoutErrors = {
        data: "some data",
        meta: { status: "success" }
      };
      
      handleApiResponse(responseWithoutErrors, errorStates.errorStateMap);
      
      expect(errorStates.apiErrorGenericFallback.value).toBe(false);
    });
  });

  describe("createErrorStates", () => {
    it("should include apiErrorInvalidJumpinLink error state", () => {
      expect(errorStates.apiErrorInvalidJumpinLink).toBeDefined();
      expect(errorStates.apiErrorInvalidJumpinLink.value).toBe(false);
    });

    it("should be able to set apiErrorInvalidJumpinLink error state", () => {
      errorStates.apiErrorInvalidJumpinLink.value = true;
      expect(errorStates.apiErrorInvalidJumpinLink.value).toBe(true);
    });
  });

  describe("handleApiError", () => {
    it("should set apiErrorInvalidJumpinLink when invalidJumpinLink error is handled", () => {
      handleApiError("invalidJumpinLink", errorStates.errorStateMap);
      
      expect(errorStates.apiErrorInvalidJumpinLink.value).toBe(true);
      expect(errorStates.apiErrorGenericFallback.value).toBe(false);
    });

    it("should reset other error states when setting invalidJumpinLink", () => {
      errorStates.apiErrorAppointmentNotFound.value = true;
      
      handleApiError("invalidJumpinLink", errorStates.errorStateMap);
      
      expect(errorStates.apiErrorInvalidJumpinLink.value).toBe(true);
      expect(errorStates.apiErrorAppointmentNotFound.value).toBe(false);
    });

    it("should use dynamic errorType parameter when provided", () => {
      const currentErrorData = ref<ApiErrorData | null>(null);
      
      handleApiError("noAppointmentForThisScope", errorStates.errorStateMap, currentErrorData, "info");
      
      expect(errorStates.apiErrorNoAppointmentForThisScope.value).toBe(true);
      expect(currentErrorData.value?.errorType).toBe("info");
      expect(currentErrorData.value?.errorCode).toBe("noAppointmentForThisScope");
    });

    it("should default to 'error' when errorType parameter is not provided", () => {
      const currentErrorData = ref<ApiErrorData | null>(null);
      
      handleApiError("appointmentNotFound", errorStates.errorStateMap, currentErrorData);
      
      expect(errorStates.apiErrorAppointmentNotFound.value).toBe(true);
      expect(currentErrorData.value?.errorType).toBe("error");
      expect(currentErrorData.value?.errorCode).toBe("appointmentNotFound");
    });

    it("should handle different error types correctly", () => {
      const currentErrorData = ref<ApiErrorData | null>(null);
      
      // Test warning type
      handleApiError("invalidEmail", errorStates.errorStateMap, currentErrorData, "warning");
      expect(currentErrorData.value?.errorType).toBe("warning");
      
      // Test info type
      handleApiError("noAppointmentForThisDay", errorStates.errorStateMap, currentErrorData, "info");
      expect(currentErrorData.value?.errorType).toBe("info");
      
      // Test error type
      handleApiError("appointmentNotAvailable", errorStates.errorStateMap, currentErrorData, "error");
      expect(currentErrorData.value?.errorType).toBe("error");
    });
  });
}); 