import { beforeEach, describe, expect, it, vi } from "vitest";

import { fetchServicesAndProviders } from "@/api/ZMSAppointmentAPI";
import {
  catalogFromResponse,
  loadOfficesAndServicesCatalog,
} from "@/utils/appointmentCatalog";
import { createErrorStates } from "@/utils/errorHandler";

vi.mock("@/api/ZMSAppointmentAPI", () => ({
  fetchServicesAndProviders: vi.fn(),
}));

const catalog = {
  services: [{ id: "1", name: "Service", maxQuantity: 1 }],
  offices: [],
  relations: [],
};

describe("appointmentCatalog", () => {
  beforeEach(() => {
    vi.mocked(fetchServicesAndProviders).mockReset();
  });

  it("returns the catalog when the response has offices and services", () => {
    const errorStates = createErrorStates();
    expect(
      catalogFromResponse(
        catalog,
        errorStates.errorStateMap,
        errorStates.currentErrorData
      )
    ).toEqual(catalog);
  });

  it("returns null when the response has errors", () => {
    const errorStates = createErrorStates();
    expect(
      catalogFromResponse(
        { errors: [{ errorCode: "rateLimitExceeded" }] },
        errorStates.errorStateMap,
        errorStates.currentErrorData
      )
    ).toBeNull();
  });

  it("loads the catalog from the API", async () => {
    vi.mocked(fetchServicesAndProviders).mockResolvedValue(catalog as any);
    const errorStates = createErrorStates();
    const loaded = await loadOfficesAndServicesCatalog(
      "1",
      "2",
      "https://www.muenchen.de",
      errorStates.errorStateMap,
      errorStates.currentErrorData
    );
    expect(fetchServicesAndProviders).toHaveBeenCalledWith(
      "1",
      "2",
      "https://www.muenchen.de"
    );
    expect(loaded).toEqual(catalog);
  });
});
