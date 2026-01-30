import { describe, expect, it } from "vitest";

import {
  calculateMaxCountBySlots,
  calculateSubserviceSlots,
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
});
