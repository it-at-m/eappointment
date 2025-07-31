import { describe, expect, it, beforeEach } from "vitest";
import { ref } from "vue";
import { handleApiResponse, createErrorStates } from "@/utils/errorHandler";

describe("errorHandler", () => {
  let errorStates: ReturnType<typeof createErrorStates>;

  beforeEach(() => {
    errorStates = createErrorStates();
  });

  describe("handleApiResponse", () => {
    it("should set apiErrorGenericFallback when data is null", () => {
      handleApiResponse(null, errorStates);
      
      expect(errorStates.apiErrorGenericFallback.value).toBe(true);
    });

    it("should set apiErrorGenericFallback when data is undefined", () => {
      handleApiResponse(undefined, errorStates);
      
      expect(errorStates.apiErrorGenericFallback.value).toBe(true);
    });

    it("should set apiErrorGenericFallback when data is not an object", () => {
      handleApiResponse("string data", errorStates);
      
      expect(errorStates.apiErrorGenericFallback.value).toBe(true);
    });

    it("should set apiErrorGenericFallback when data is a number", () => {
      handleApiResponse(123, errorStates);
      
      expect(errorStates.apiErrorGenericFallback.value).toBe(true);
    });

    it("should set apiErrorGenericFallback when data is an array", () => {
      handleApiResponse([1, 2, 3], errorStates);
      
      expect(errorStates.apiErrorGenericFallback.value).toBe(true);
    });

    it("should set apiErrorGenericFallback when errors array exists but has no errorCode", () => {
      const invalidResponse = {
        errors: [
          { message: "Some error message" },
          { status: "error" }
        ]
      };
      
      handleApiResponse(invalidResponse, errorStates);
      
      expect(errorStates.apiErrorGenericFallback.value).toBe(true);
    });

    it("should set apiErrorGenericFallback when errors array is empty", () => {
      const responseWithEmptyErrors = {
        errors: []
      };
      
      handleApiResponse(responseWithEmptyErrors, errorStates);
      
      expect(errorStates.apiErrorGenericFallback.value).toBe(true);
    });

    it("should handle valid error response with errorCode", () => {
      const validErrorResponse = {
        errors: [
          { errorCode: "tooManyAppointmentsWithSameMail", errorMessage: "Too many appointments" }
        ]
      };
      
      handleApiResponse(validErrorResponse, errorStates);
      
      expect(errorStates.apiErrorTooManyAppointmentsWithSameMail.value).toBe(true);
      expect(errorStates.apiErrorGenericFallback.value).toBe(false);
    });

    it("should handle mixed errors array with some having errorCode and some not", () => {
      const mixedErrorResponse = {
        errors: [
          { errorCode: "appointmentNotFound", errorMessage: "Not found" },
          { message: "Some other error without errorCode" },
          { errorCode: "invalidEmail", errorMessage: "Invalid email" }
        ]
      };
      
      handleApiResponse(mixedErrorResponse, errorStates);
      
      // Should handle the first error with errorCode
      expect(errorStates.apiErrorAppointmentNotFound.value).toBe(true);
      expect(errorStates.apiErrorGenericFallback.value).toBe(false);
    });

    it("should handle empty object response", () => {
      handleApiResponse({}, errorStates);
      
      expect(errorStates.apiErrorGenericFallback.value).toBe(false);
    });

    it("should handle object without errors property", () => {
      const responseWithoutErrors = {
        data: "some data",
        meta: { status: "success" }
      };
      
      handleApiResponse(responseWithoutErrors, errorStates);
      
      expect(errorStates.apiErrorGenericFallback.value).toBe(false);
    });
  });
}); 