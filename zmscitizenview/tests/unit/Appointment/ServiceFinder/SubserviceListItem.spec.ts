import { mount } from "@vue/test-utils";
import { describe, expect, it, beforeEach } from "vitest";
import { nextTick } from "vue";

// @ts-expect-error: Vue SFC import for test
import SubserviceListItem from "@/components/Appointment/ServiceFinder/SubserviceListItem.vue";

describe("SubserviceListItem", () => {
  const mockSubService = {
    id: "1",
    name: "Test Subservice",
    count: 0,
    maxQuantity: 5,
    providers: [{ slots: 3 }],
  };

  const createWrapper = (props = {}) => {
    return mount(SubserviceListItem, {
      props: {
        subService: mockSubService,
        currentSlots: 0,
        maxSlotsPerAppointment: 10,
        ...props,
      },
    });
  };

  describe("Rendering", () => {
    it("renders the subservice name", () => {
      const wrapper = createWrapper();
      expect(wrapper.text()).toContain(mockSubService.name);
    });
  });

  describe("Event Handling", () => {
    it("emits change event when count is updated", async () => {
      const wrapper = createWrapper();
      wrapper.vm.count = 2;
      await nextTick();
      const changeEvent = wrapper.emitted("change");
      expect(changeEvent).toBeTruthy();
      if (changeEvent) {
        expect(changeEvent[0]).toEqual([mockSubService.id, 2]);
      }
    });
  });

  describe("Computed Properties", () => {
    it("computes maxValue correctly based on slots", () => {
      // slots: 3, maxSlotsPerAppointment: 10, currentSlots: 0
      // availableSlots = 10 - 0 = 10
      // maxCountBySlots = floor(10 / 3) = 3
      // maxValue = min(maxQuantity=5, maxCountBySlots=3) = 3
      const wrapper = createWrapper();
      expect(wrapper.vm.maxValue).toBe(3);
    });

    it("computes maxValue as maxQuantity when slots allow more", () => {
      // slots: 2, maxSlotsPerAppointment: 20, currentSlots: 0, maxQuantity: 5
      // availableSlots = 20 - 0 = 20
      // maxCountBySlots = floor(20 / 2) = 10
      // maxValue = min(maxQuantity=5, maxCountBySlots=10) = 5
      const wrapper = createWrapper({
        subService: { ...mockSubService, providers: [{ slots: 2 }] },
        maxSlotsPerAppointment: 20,
      });
      expect(wrapper.vm.maxValue).toBe(mockSubService.maxQuantity);
    });

    it("computes disabled correctly when maxValue > 0", () => {
      const wrapper = createWrapper();
      expect(wrapper.vm.disabled).toBe(false);
    });

    it("computes disabled correctly when maxValue = 0 and count = 0", () => {
      // currentSlots: 10, maxSlotsPerAppointment: 10
      // availableSlots = 10 - 10 = 0
      // maxCountBySlots = floor(0 / 3) = 0
      // maxValue = 0, count = 0 -> disabled = true
      const wrapper = createWrapper({
        currentSlots: 10,
        maxSlotsPerAppointment: 10,
      });
      expect(wrapper.vm.disabled).toBe(true);
    });
  });

  describe("Business Logic", () => {
    it("maxValue is limited by remaining available slots", () => {
      // currentSlots: 8, maxSlotsPerAppointment: 10, slots: 3
      // availableSlots = 10 - 8 = 2
      // maxCountBySlots = floor(2 / 3) = 0
      // maxValue = max(0, min(5, 0)) = 0
      const wrapper = createWrapper({
        currentSlots: 8,
        maxSlotsPerAppointment: 10,
      });
      expect(wrapper.vm.maxValue).toBe(0);
    });

    it("maxValue considers slots already used by this subservice", async () => {
      // currentSlots: 6 (includes 3 from this subservice with count=1)
      // maxSlotsPerAppointment: 10, slots: 3, count: 1
      // thisSlotsUsed = 3 * 1 = 3
      // slotsUsedByOthers = 6 - 3 = 3
      // availableSlots = 10 - 3 = 7
      // maxCountBySlots = floor(7 / 3) = 2
      // maxValue = min(5, 2) = 2
      const wrapper = createWrapper({
        subService: { ...mockSubService, count: 1 },
        currentSlots: 6,
        maxSlotsPerAppointment: 10,
      });
      wrapper.vm.count = 1;
      await nextTick();
      expect(wrapper.vm.maxValue).toBe(2);
    });

    it("disabled is true when maxValue is 0 and count is 0", () => {
      const wrapper = createWrapper({
        currentSlots: 10,
        maxSlotsPerAppointment: 10,
      });
      wrapper.vm.count = 0;
      expect(wrapper.vm.disabled).toBe(true);
    });

    it("disabled is false when maxValue > 0", () => {
      const wrapper = createWrapper({
        currentSlots: 0,
        maxSlotsPerAppointment: 10,
      });
      expect(wrapper.vm.disabled).toBe(false);
    });

    it("uses maximum slot value from providers (not minimum)", () => {
      // With providers having slots [5, 2, 8], getMaxSlotOfProvider returns 8
      // availableSlots = 10 - 0 = 10
      // maxCountBySlots = floor(10 / 8) = 1
      // maxValue = min(5, 1) = 1
      const wrapper = createWrapper({
        subService: { ...mockSubService, providers: [{ slots: 5 }, { slots: 2 }, { slots: 8 }] },
        currentSlots: 0,
        maxSlotsPerAppointment: 10,
      });
      expect(wrapper.vm.maxValue).toBe(1);
    });

    it("emits correct id and count when count changes", async () => {
      const wrapper = createWrapper();
      wrapper.vm.count = 3;
      await nextTick();
      const changeEvent = wrapper.emitted("change");
      expect(changeEvent).toBeTruthy();
      if (changeEvent) {
        expect(changeEvent[0]).toEqual([mockSubService.id, 3]);
      }
    });

    it("disables subservice when adding it would exceed maxSlotsPerAppointment", () => {
      // currentSlots: 8, maxSlotsPerAppointment: 10, slots: 3
      // availableSlots = 10 - 8 = 2
      // maxCountBySlots = floor(2 / 3) = 0
      // maxValue = 0, count = 0 -> disabled = true
      const wrapper = createWrapper({
        currentSlots: 8,
        maxSlotsPerAppointment: 10,
        subService: { 
          ...mockSubService, 
          providers: [{ slots: 3 }]
        },
      });
      
      expect(wrapper.vm.maxValue).toBe(0);
      expect(wrapper.vm.disabled).toBe(true);
    });

    it("enables subservice when adding it would not exceed maxSlotsPerAppointment", () => {
      // currentSlots: 7, maxSlotsPerAppointment: 10, slots: 3
      // availableSlots = 10 - 7 = 3
      // maxCountBySlots = floor(3 / 3) = 1
      // maxValue = min(5, 1) = 1, count = 0 -> disabled = false
      const wrapper = createWrapper({
        currentSlots: 7,
        maxSlotsPerAppointment: 10,
        subService: { 
          ...mockSubService, 
          providers: [{ slots: 3 }]
        },
      });
      
      expect(wrapper.vm.maxValue).toBe(1);
      expect(wrapper.vm.disabled).toBe(false);
    });
  });
});
