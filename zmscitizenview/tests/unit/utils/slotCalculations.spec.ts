import { describe, expect, it } from "vitest";

import {
  adjustMainServiceCount,
  adjustSubserviceCount,
  calculateMaxCountBySlots,
  calculateOtherSubserviceSlots,
  calculateSubserviceSlots,
  calculateTotalSlots,
  exceedsSlotLimit,
  getEffectiveMinSlotsPerAppointment,
  getMaxSlotOfProvider,
  getMinSlotsPerAppointmentOfProvider,
} from "@/utils/slotCalculations";

describe("slotCalculations", () => {
  describe("getMaxSlotOfProvider", () => {
    it("returns 1 for empty providers array", () => {
      expect(getMaxSlotOfProvider([])).toBe(1);
    });

    it("returns max slots from multiple providers", () => {
      const providers = [
        { slots: 2 },
        { slots: 5 },
        { slots: 3 },
      ] as any[];
      expect(getMaxSlotOfProvider(providers)).toBe(5);
    });

    it("returns 1 when all providers have no slots defined", () => {
      const providers = [{ id: "1" }, { id: "2" }] as any[];
      expect(getMaxSlotOfProvider(providers)).toBe(1);
    });

    it("ignores providers with 0 slots", () => {
      const providers = [
        { slots: 0 },
        { slots: 3 },
      ] as any[];
      expect(getMaxSlotOfProvider(providers)).toBe(3);
    });
  });

  describe("getMinSlotsPerAppointmentOfProvider", () => {
    it("returns 0 for empty providers array", () => {
      expect(getMinSlotsPerAppointmentOfProvider([])).toBe(0);
    });

    it("returns minimum slotsPerAppointment from multiple providers", () => {
      const providers = [
        { slotsPerAppointment: "10" },
        { slotsPerAppointment: "3" },
        { slotsPerAppointment: "7" },
      ] as any[];
      expect(getMinSlotsPerAppointmentOfProvider(providers)).toBe(3);
    });

    it("ignores providers with no slotsPerAppointment", () => {
      const providers = [
        { id: "1" },
        { slotsPerAppointment: "5" },
      ] as any[];
      expect(getMinSlotsPerAppointmentOfProvider(providers)).toBe(5);
    });

    it("ignores providers with 0 or negative slotsPerAppointment", () => {
      const providers = [
        { slotsPerAppointment: "0" },
        { slotsPerAppointment: "-1" },
        { slotsPerAppointment: "4" },
      ] as any[];
      expect(getMinSlotsPerAppointmentOfProvider(providers)).toBe(4);
    });
  });

  describe("getEffectiveMinSlotsPerAppointment", () => {
    it("returns MAX_SLOTS (25) for empty providers", () => {
      expect(getEffectiveMinSlotsPerAppointment([])).toBe(25);
    });

    it("returns minimum from providers when less than MAX_SLOTS", () => {
      const providers = [
        { slotsPerAppointment: "10" },
        { slotsPerAppointment: "5" },
      ] as any[];
      expect(getEffectiveMinSlotsPerAppointment(providers)).toBe(5);
    });

    it("caps at MAX_SLOTS when provider value exceeds it", () => {
      const providers = [
        { slotsPerAppointment: "30" },
        { slotsPerAppointment: "50" },
      ] as any[];
      expect(getEffectiveMinSlotsPerAppointment(providers)).toBe(25);
    });

    it("returns MAX_SLOTS when no provider has valid slotsPerAppointment", () => {
      const providers = [
        { slotsPerAppointment: "0" },
        { id: "2" },
      ] as any[];
      expect(getEffectiveMinSlotsPerAppointment(providers)).toBe(25);
    });
  });

  describe("calculateSubserviceSlots", () => {
    it("returns 0 for undefined subservices", () => {
      expect(calculateSubserviceSlots(undefined)).toBe(0);
    });

    it("returns 0 for empty subservices array", () => {
      expect(calculateSubserviceSlots([])).toBe(0);
    });

    it("calculates total slots from subservices", () => {
      const subservices = [
        { count: 2, providers: [{ slots: 3 }] },
        { count: 1, providers: [{ slots: 5 }] },
      ] as any[];
      // 2*3 + 1*5 = 11
      expect(calculateSubserviceSlots(subservices)).toBe(11);
    });

    it("ignores subservices with count 0", () => {
      const subservices = [
        { count: 0, providers: [{ slots: 3 }] },
        { count: 2, providers: [{ slots: 2 }] },
      ] as any[];
      // 0*3 + 2*2 = 4
      expect(calculateSubserviceSlots(subservices)).toBe(4);
    });

    it("uses max slots from multiple providers", () => {
      const subservices = [
        { count: 1, providers: [{ slots: 2 }, { slots: 5 }, { slots: 3 }] },
      ] as any[];
      // 1*5 = 5 (uses max slot of 5)
      expect(calculateSubserviceSlots(subservices)).toBe(5);
    });
  });

  describe("calculateMaxCountBySlots", () => {
    it("returns maxQuantity when serviceSlots is 0", () => {
      expect(calculateMaxCountBySlots(0, 5, 10, 0)).toBe(5);
    });

    it("returns maxQuantity when slots allow more", () => {
      // serviceSlots: 2, maxQuantity: 3, minSlotsPerAppointment: 20, otherSlotsUsed: 0
      // availableSlots = 20 - 0 = 20
      // maxCountBySlots = floor(20 / 2) = 10
      // min(3, 10) = 3
      expect(calculateMaxCountBySlots(2, 3, 20, 0)).toBe(3);
    });

    it("returns slot-based limit when slots constrain more", () => {
      // serviceSlots: 3, maxQuantity: 5, minSlotsPerAppointment: 10, otherSlotsUsed: 0
      // availableSlots = 10 - 0 = 10
      // maxCountBySlots = floor(10 / 3) = 3
      // min(5, 3) = 3
      expect(calculateMaxCountBySlots(3, 5, 10, 0)).toBe(3);
    });

    it("accounts for slots used by other services", () => {
      // serviceSlots: 3, maxQuantity: 5, minSlotsPerAppointment: 10, otherSlotsUsed: 7
      // availableSlots = 10 - 7 = 3
      // maxCountBySlots = floor(3 / 3) = 1
      // min(5, 1) = 1
      expect(calculateMaxCountBySlots(3, 5, 10, 7)).toBe(1);
    });

    it("returns 0 when no slots available", () => {
      // serviceSlots: 3, maxQuantity: 5, minSlotsPerAppointment: 10, otherSlotsUsed: 10
      // availableSlots = 10 - 10 = 0
      // maxCountBySlots = floor(0 / 3) = 0
      // max(0, min(5, 0)) = 0
      expect(calculateMaxCountBySlots(3, 5, 10, 10)).toBe(0);
    });

    it("returns 0 when slots are overused", () => {
      // serviceSlots: 3, maxQuantity: 5, minSlotsPerAppointment: 10, otherSlotsUsed: 15
      // availableSlots = 10 - 15 = -5
      // maxCountBySlots = floor(-5 / 3) = -2
      // max(0, min(5, -2)) = 0
      expect(calculateMaxCountBySlots(3, 5, 10, 15)).toBe(0);
    });
  });

  describe("calculateTotalSlots", () => {
    it("calculates main service slots only when no subservices", () => {
      const providers = [{ slots: 3 }] as any[];
      expect(calculateTotalSlots(providers, 2, undefined)).toBe(6);
    });

    it("calculates main + subservice slots", () => {
      const mainProviders = [{ slots: 3 }] as any[];
      const subservices = [
        { count: 1, providers: [{ slots: 2 }] },
        { count: 2, providers: [{ slots: 4 }] },
      ] as any[];
      // main: 3*2=6, sub: 1*2 + 2*4 = 10, total: 16
      expect(calculateTotalSlots(mainProviders, 2, subservices)).toBe(16);
    });
  });

  describe("exceedsSlotLimit", () => {
    it("returns false when under limit", () => {
      expect(exceedsSlotLimit(5, 10)).toBe(false);
    });

    it("returns false when at limit", () => {
      expect(exceedsSlotLimit(10, 10)).toBe(false);
    });

    it("returns true when over limit", () => {
      expect(exceedsSlotLimit(11, 10)).toBe(true);
    });

    it("returns false when limit is 0 (disabled)", () => {
      expect(exceedsSlotLimit(100, 0)).toBe(false);
    });
  });

  describe("adjustMainServiceCount", () => {
    it("returns requested count when within limit", () => {
      const providers = [{ slots: 3 }] as any[];
      const result = adjustMainServiceCount(2, providers, 0, 10);
      expect(result.adjustedCount).toBe(2);
      expect(result.totalSlots).toBe(6);
    });

    it("adjusts count when exceeding limit", () => {
      const providers = [{ slots: 3 }] as any[];
      // requested: 5, subSlots: 0, limit: 10
      // 5*3=15 > 10, max allowed = floor(10/3) = 3
      const result = adjustMainServiceCount(5, providers, 0, 10);
      expect(result.adjustedCount).toBe(3);
      expect(result.totalSlots).toBe(9);
    });

    it("ensures minimum count of 1", () => {
      const providers = [{ slots: 5 }] as any[];
      // requested: 3, subSlots: 8, limit: 10
      // available = 10-8 = 2, max = floor(2/5) = 0, but min is 1
      const result = adjustMainServiceCount(3, providers, 8, 10);
      expect(result.adjustedCount).toBe(1);
    });

    it("accounts for subservice slots", () => {
      const providers = [{ slots: 3 }] as any[];
      // requested: 3, subSlots: 5, limit: 10
      // 3*3+5=14 > 10, available = 10-5 = 5, max = floor(5/3) = 1
      const result = adjustMainServiceCount(3, providers, 5, 10);
      expect(result.adjustedCount).toBe(1);
      expect(result.totalSlots).toBe(8);
    });
  });

  describe("adjustSubserviceCount", () => {
    it("returns requested count when within limit", () => {
      const providers = [{ slots: 2 }] as any[];
      const result = adjustSubserviceCount(2, providers, 3, 0, 10);
      expect(result.adjustedCount).toBe(2);
      expect(result.totalSlots).toBe(7); // 3 + 0 + 2*2
    });

    it("adjusts count when exceeding limit", () => {
      const providers = [{ slots: 3 }] as any[];
      // mainSlots: 4, otherSubSlots: 2, requested: 3, limit: 10
      // 4 + 2 + 3*3 = 15 > 10
      // available = 10 - 4 - 2 = 4, max = floor(4/3) = 1
      const result = adjustSubserviceCount(3, providers, 4, 2, 10);
      expect(result.adjustedCount).toBe(1);
      expect(result.totalSlots).toBe(9);
    });

    it("allows count of 0", () => {
      const providers = [{ slots: 5 }] as any[];
      // mainSlots: 8, otherSubSlots: 2, limit: 10
      // available = 10 - 8 - 2 = 0, max = 0
      const result = adjustSubserviceCount(2, providers, 8, 2, 10);
      expect(result.adjustedCount).toBe(0);
    });
  });

  describe("calculateOtherSubserviceSlots", () => {
    it("returns 0 for undefined subservices", () => {
      expect(calculateOtherSubserviceSlots(undefined, "1")).toBe(0);
    });

    it("excludes the specified subservice", () => {
      const subservices = [
        { id: "1", count: 2, providers: [{ slots: 3 }] },
        { id: "2", count: 1, providers: [{ slots: 5 }] },
        { id: "3", count: 3, providers: [{ slots: 2 }] },
      ] as any[];
      // Exclude id="2", total = 2*3 + 3*2 = 12
      expect(calculateOtherSubserviceSlots(subservices, "2")).toBe(12);
    });

    it("ignores subservices with count 0", () => {
      const subservices = [
        { id: "1", count: 0, providers: [{ slots: 3 }] },
        { id: "2", count: 2, providers: [{ slots: 5 }] },
      ] as any[];
      // Exclude id="2", only id="1" left but count=0
      expect(calculateOtherSubserviceSlots(subservices, "2")).toBe(0);
    });
  });
});
