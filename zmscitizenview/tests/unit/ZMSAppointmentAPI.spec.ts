import { describe, expect, it, vi, beforeEach } from "vitest";
// @ts-expect-error: API import for test
import { fetchServicesAndProviders } from "@/api/ZMSAppointmentAPI";

global.fetch = vi.fn();

describe("ZMSAppointmentAPI", () => {
  beforeEach(() => {
    vi.resetAllMocks();
  });

  describe("fetchServicesAndProviders", () => {
    it("handles non-JSON response and preserves status code", async () => {
      (global.fetch as any).mockResolvedValueOnce({
        status: 404,
        json: () => Promise.reject(new Error("Unexpected token < in JSON")),
      });

      const result = await fetchServicesAndProviders();

      expect(result.errors).toBeDefined();
      expect(result.errors[0].statusCode).toBe(404);
      expect(result.errors[0].errorCode).toBe("internalError");
      expect(result.errors[0].errorMessage).toBe("HTTP 404");
    });

    it("handles valid JSON response normally", async () => {
      (global.fetch as any).mockResolvedValueOnce({
        status: 200,
        json: () => Promise.resolve({
          services: [
            { 
              id: "1", 
              name: "Passport Application", 
              maxQuantity: 5,
              combinable: {}
            },
            { 
              id: "2", 
              name: "ID Card Renewal", 
              maxQuantity: 3,
              combinable: {}
            }
          ],
          offices: [
            { 
              id: "1", 
              name: "City Hall Main Office", 
              address: "Marienplatz 1, 80331 München",
              showAlternativeLocations: true,
              displayNameAlternatives: ["Rathaus"],
              organization: "City of Munich",
              organizationUnit: "Citizen Services",
              slotTimeInMinutes: 30,
              disabledByServices: [],
              scope: { captchaActivatedRequired: false },
              maxSlotsPerAppointment: "10",
              slots: 5,
              priority: 1
            },
            { 
              id: "2", 
              name: "District Office North", 
              address: "Nordstraße 15, 80801 München",
              showAlternativeLocations: false,
              displayNameAlternatives: [],
              organization: "City of Munich",
              organizationUnit: "District Services",
              slotTimeInMinutes: 45,
              disabledByServices: ["1"],
              scope: { captchaActivatedRequired: true },
              maxSlotsPerAppointment: "8",
              slots: 3,
              priority: 2
            }
          ],
          relations: [
            { 
              id: "1", 
              serviceId: "1", 
              officeId: "1",
              slots: 5
            },
            { 
              id: "2", 
              serviceId: "1", 
              officeId: "2",
              slots: 3
            },
            { 
              id: "3", 
              serviceId: "2", 
              officeId: "1",
              slots: 4
            }
          ]
        }),
      });

      const result = await fetchServicesAndProviders();

      expect(result.services).toBeDefined();
      expect(result.services).toHaveLength(2);
      expect(result.services[0].name).toBe("Passport Application");
      expect(result.offices).toBeDefined();
      expect(result.offices).toHaveLength(2);
      expect(result.offices[0].name).toBe("City Hall Main Office");
      expect(result.relations).toBeDefined();
      expect(result.relations).toHaveLength(3);
    });

    it("handles network errors", async () => {
      (global.fetch as any).mockRejectedValueOnce(new Error("Network error"));

      const result = await fetchServicesAndProviders();

      expect(result.errors).toBeDefined();
      expect(result.errors[0].statusCode).toBe(0);
      expect(result.errors[0].errorCode).toBe("networkError");
    });
  });
});
