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
    it("computes maxValue correctly", () => {
      const wrapper = createWrapper();
      expect(wrapper.vm.maxValue).toBe(mockSubService.maxQuantity);
    });

    it("computes disabled correctly", () => {
      const wrapper = createWrapper();
      expect(wrapper.vm.disabled).toBe(false);
    });
  });

  describe("Business Logic", () => {
    it("maxValue equals count when checkPlusEndabled is false", () => {
      const wrapper = createWrapper({
        currentSlots: 10, // will make checkPlusEndabled false
        maxSlotsPerAppointment: 5,
      });
      wrapper.vm.count = 2;
      expect(wrapper.vm.maxValue).toBe(2);
    });

    it("maxValue equals subService.maxQuantity when checkPlusEndabled is true", () => {
      const wrapper = createWrapper({
        currentSlots: 0, // will make checkPlusEndabled true
        maxSlotsPerAppointment: 10,
      });
      expect(wrapper.vm.maxValue).toBe(mockSubService.maxQuantity);
    });

    it("disabled is true when checkPlusEndabled is false and count is 0", () => {
      const wrapper = createWrapper({
        currentSlots: 10, // will make checkPlusEndabled false
        maxSlotsPerAppointment: 5,
      });
      wrapper.vm.count = 0;
      expect(wrapper.vm.disabled).toBe(true);
    });

    it("disabled is false when checkPlusEndabled is true", () => {
      const wrapper = createWrapper({
        currentSlots: 0, // will make checkPlusEndabled true
        maxSlotsPerAppointment: 10,
      });
      expect(wrapper.vm.disabled).toBe(false);
    });

    it("checkPlusEndabled is true when currentSlots + minProviderSlots <= maxSlotsPerAppointment", () => {
      const wrapper = createWrapper({
        currentSlots: 0,
        maxSlotsPerAppointment: 10,
        subService: { ...mockSubService, providers: [{ slots: 3 }, { slots: 2 }] },
      });
      expect(wrapper.vm.checkPlusEndabled).toBe(true);
    });

    it("checkPlusEndabled is false when currentSlots + minProviderSlots > maxSlotsPerAppointment", () => {
      const wrapper = createWrapper({
        currentSlots: 9,
        maxSlotsPerAppointment: 10,
        subService: { ...mockSubService, providers: [{ slots: 3 }, { slots: 2 }] },
      });
      expect(wrapper.vm.checkPlusEndabled).toBe(false);
    });

    it("getMinSlotOfProvider returns the minimum slot value", () => {
      const wrapper = createWrapper({
        subService: { ...mockSubService, providers: [{ slots: 5 }, { slots: 2 }, { slots: 8 }] },
      });
      expect(wrapper.vm.getMinSlotOfProvider(wrapper.vm.$props.subService.providers)).toBe(2);
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
      // Simulate a scenario where current slots + subservice slots > maxSlotsPerAppointment
      const wrapper = createWrapper({
        currentSlots: 8, // Current slots used
        maxSlotsPerAppointment: 10, // Maximum allowed
        subService: { 
          ...mockSubService, 
          providers: [{ slots: 3 }] // This subservice needs 3 slots
        },
      });
      
      // 8 + 3 = 11, which exceeds maxSlotsPerAppointment of 10
      expect(wrapper.vm.checkPlusEndabled).toBe(false);
      expect(wrapper.vm.disabled).toBe(true); // Should be disabled when count is 0
    });

    it("enables subservice when adding it would not exceed maxSlotsPerAppointment", () => {
      // Simulate a scenario where current slots + subservice slots <= maxSlotsPerAppointment
      const wrapper = createWrapper({
        currentSlots: 7, // Current slots used
        maxSlotsPerAppointment: 10, // Maximum allowed
        subService: { 
          ...mockSubService, 
          providers: [{ slots: 3 }] // This subservice needs 3 slots
        },
      });
      
      // 7 + 3 = 10, which equals maxSlotsPerAppointment of 10
      expect(wrapper.vm.checkPlusEndabled).toBe(true);
      expect(wrapper.vm.disabled).toBe(false); // Should be enabled when count is 0
    });
  });
}); 