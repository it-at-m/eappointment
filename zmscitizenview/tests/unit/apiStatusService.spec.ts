import { beforeEach, describe, expect, it, vi } from "vitest";

import {
  checkApiStatus,
  getApiStatusState,
  handleApiResponseForMaintenance,
  isInMaintenanceMode,
  isInSystemFailureMode,
  setApiStatus,
// @ts-expect-error: API import for test
} from "@/utils/apiStatusService";

vi.mock("@/api/ZMSAppointmentAPI", () => {
  return {
    fetchServicesAndProviders: vi.fn(),
  };
});

// @ts-expect-error: API import for test
const mockedApi = await import("@/api/ZMSAppointmentAPI");

describe("apiStatusService", () => {
  const baseUrl = "https://www.muenchen.de";
  beforeEach(() => {
    // Reset global state and mocks between tests
    vi.useRealTimers();
    vi.clearAllTimers();
    vi.resetAllMocks();
    setApiStatus("normal", baseUrl);
  });

  describe("checkApiStatus", () => {
    it("returns normal when response has no errors", async () => {
      (mockedApi.fetchServicesAndProviders as any).mockResolvedValueOnce({
        services: [],
        offices: [],
        relations: [],
      });
      const status = await checkApiStatus();
      expect(status).toBe("normal");
    });

    it("returns normal on rateLimitExceeded (handled locally)", async () => {
      (mockedApi.fetchServicesAndProviders as any).mockResolvedValueOnce({
        errors: [{ errorCode: "rateLimitExceeded", statusCode: 429 }],
      });
      const status = await checkApiStatus();
      expect(status).toBe("normal");
    });

    it("returns maintenance on 503", async () => {
      (mockedApi.fetchServicesAndProviders as any).mockResolvedValueOnce({
        errors: [{ statusCode: 503 }],
      });
      const status = await checkApiStatus();
      expect(status).toBe("maintenance");
    });

    it("returns maintenance on serviceUnavailable errorCode (gateway 400)", async () => {
      (mockedApi.fetchServicesAndProviders as any).mockResolvedValueOnce({
        errors: [{ errorCode: "serviceUnavailable", statusCode: 400 }],
      });
      const status = await checkApiStatus();
      expect(status).toBe("maintenance");
    });

    it("returns systemFailure on 500+", async () => {
      (mockedApi.fetchServicesAndProviders as any).mockResolvedValueOnce({
        errors: [{ statusCode: 500 }],
      });
      const status = await checkApiStatus();
      expect(status).toBe("systemFailure");
    });

    it("returns systemFailure on network statusCode 0", async () => {
      (mockedApi.fetchServicesAndProviders as any).mockResolvedValueOnce({
        errors: [{ statusCode: 0 }],
      });
      const status = await checkApiStatus();
      expect(status).toBe("systemFailure");
    });

    it("returns maintenance on generic 4xx", async () => {
      (mockedApi.fetchServicesAndProviders as any).mockResolvedValueOnce({
        errors: [{ statusCode: 404 }],
      });
      const status = await checkApiStatus();
      expect(status).toBe("maintenance");
    });

    it("returns systemFailure when fetch throws (network error)", async () => {
      (mockedApi.fetchServicesAndProviders as any).mockRejectedValueOnce(
        new Error("network")
      );
      const status = await checkApiStatus();
      expect(status).toBe("systemFailure");
    });
  });

  describe("handleApiResponseForMaintenance", () => {
    it("does not change status on normal response", () => {
      const changed = handleApiResponseForMaintenance({ services: [] });
      expect(changed).toBe(false);
      expect(getApiStatusState().status.value).toBe("normal");
    });

    it("ignores rateLimitExceeded for global status", () => {
      const changed = handleApiResponseForMaintenance({
        errors: [{ errorCode: "rateLimitExceeded", statusCode: 429 }],
      });
      expect(changed).toBe(false);
      expect(getApiStatusState().status.value).toBe("normal");
    });

    it("sets maintenance on 503", () => {
      const changed = handleApiResponseForMaintenance({ errors: [{ statusCode: 503 }] });
      expect(changed).toBe(true);
      expect(isInMaintenanceMode()).toBe(true);
    });

    it("sets maintenance on serviceUnavailable", () => {
      const changed = handleApiResponseForMaintenance({
        errors: [{ errorCode: "serviceUnavailable", statusCode: 400 }],
      });
      expect(changed).toBe(true);
      expect(isInMaintenanceMode()).toBe(true);
    });

    it("sets systemFailure on 500+ and statusCode 0", () => {
      let changed = handleApiResponseForMaintenance({ errors: [{ statusCode: 500 }] });
      expect(changed).toBe(true);
      expect(isInSystemFailureMode()).toBe(true);

      setApiStatus("normal", baseUrl);
      changed = handleApiResponseForMaintenance({ errors: [{ statusCode: 0 }] });
      expect(changed).toBe(true);
      expect(isInSystemFailureMode()).toBe(true);
    });

    it("sets maintenance on generic 4xx", () => {
      const changed = handleApiResponseForMaintenance({ errors: [{ statusCode: 404 }] });
      expect(changed).toBe(true);
      expect(isInMaintenanceMode()).toBe(true);
    });
  });

  describe("setApiStatus + getters", () => {
    it("updates state and getters reflect modes", () => {
      setApiStatus("maintenance", baseUrl);
      expect(isInMaintenanceMode()).toBe(true);
      expect(isInSystemFailureMode()).toBe(false);

      setApiStatus("systemFailure", baseUrl);
      expect(isInSystemFailureMode()).toBe(true);
      expect(isInMaintenanceMode()).toBe(false);

      setApiStatus("normal", baseUrl);
      expect(isInMaintenanceMode()).toBe(false);
      expect(isInSystemFailureMode()).toBe(false);
    });
  });
});
