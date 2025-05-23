import { mount } from "@vue/test-utils";
import { describe, expect, it, beforeEach, vi } from "vitest";
import { nextTick, ref } from "vue";

// @ts-expect-error: Vue SFC import for test
import AppointmentSummary from "@/components/Appointment/AppointmentSummary.vue";

describe("AppointmentSummary", () => {
  const mockT = (key: string) => key;
  const mockSelectedService = ref({
    id: "123",
    name: "Test Service",
    count: 2,
    subServices: [
      {
        id: "456",
        name: "Sub Service 1",
        count: 1,
      },
    ],
  });

  const mockSelectedProvider = ref({
    id: "789",
    name: "Test Provider",
    address: {
      street: "Test Street",
      house_number: "123",
      postal_code: "12345",
      city: "Test City",
    },
    scope: {
      displayInfo: "Test Info",
      customTextfieldLabel: "Custom Field 1",
      customTextfield2Label: "Custom Field 2",
    },
  });

  const mockAppointment = ref({
    timestamp: Math.floor(Date.now() / 1000),
    familyName: "John Doe",
    email: "john@example.com",
    telephone: "1234567890",
    customTextfield: "Custom Value 1",
    customTextfield2: "Custom Value 2",
  });

  const createWrapper = (props = {}) => {
    return mount(AppointmentSummary, {
      props: {
        t: mockT,
        isRebooking: false,
        rebookOrCancelDialog: false,
        ...props,
      },
      global: {
        provide: {
          selectedServiceProvider: {
            selectedService: mockSelectedService,
          },
          selectedTimeslot: {
            selectedProvider: mockSelectedProvider,
          },
          appointment: {
            appointment: mockAppointment,
          },
        },
        stubs: {
          "muc-button": true,
        },
      },
    });
  };

  describe("Rendering", () => {
    it("renders service information correctly", () => {
      const wrapper = createWrapper();
      expect(wrapper.text()).toContain("Test Service");
      expect(wrapper.text()).toContain("2x");
      expect(wrapper.text()).toContain("Sub Service 1");
      expect(wrapper.text()).toContain("1x");
    });

    it("renders provider information correctly", () => {
      const wrapper = createWrapper();
      expect(wrapper.text()).toContain("Test Provider");
      expect(wrapper.text()).toContain("Test Street 123");
      expect(wrapper.text()).toContain("12345 Test City");
      expect(wrapper.text()).toContain("Test Info");
    });

    it("renders appointment time correctly", () => {
      const wrapper = createWrapper();
      const date = new Date(mockAppointment.value.timestamp * 1000);
      const formatterDate = new Intl.DateTimeFormat("de-DE", {
        weekday: "long",
        year: "numeric",
        month: "numeric",
        day: "numeric",
      });
      const formatterTime = new Intl.DateTimeFormat("de-DE", {
        timeZone: "Europe/Berlin",
        hour: "numeric",
        minute: "numeric",
        hour12: false,
      });
      const expectedDate = formatterDate.format(date) + ", " + formatterTime.format(date);
      expect(wrapper.text()).toContain(expectedDate);
    });

    it("renders customer information correctly", () => {
      const wrapper = createWrapper();
      expect(wrapper.text()).toContain("John Doe");
      expect(wrapper.text()).toContain("john@example.com");
      expect(wrapper.text()).toContain("1234567890");
      expect(wrapper.text()).toContain("Custom Value 1");
      expect(wrapper.text()).toContain("Custom Value 2");
    });
  });

  describe("Form Validation", () => {
    it("disables book button when checkboxes are not checked", () => {
      const wrapper = createWrapper();
      const bookButton = wrapper.find('muc-button-stub[icon="check"]');
      expect(bookButton.attributes("disabled")).toBe("true");
    });

    it("enables book button when both checkboxes are checked", async () => {
      const wrapper = createWrapper();
      await wrapper.find('input[name="checkbox-privacy-policy"]').trigger("click");
      await wrapper.find('input[name="checkbox-electronic-communication"]').trigger("click");
      const bookButton = wrapper.find('muc-button-stub[icon="check"]');
      expect(bookButton.attributes("disabled")).toBe("false");
    });
  });

  describe("Navigation", () => {
    it("emits back event when back button is clicked", async () => {
      const wrapper = createWrapper();
      await wrapper.find('muc-button-stub[icon="arrow-left"]').trigger("click");
      expect(wrapper.emitted("back")).toBeTruthy();
    });

    it("emits bookAppointment event when book button is clicked", async () => {
      const wrapper = createWrapper();
      await wrapper.find('input[name="checkbox-privacy-policy"]').trigger("click");
      await wrapper.find('input[name="checkbox-electronic-communication"]').trigger("click");
      await wrapper.find('muc-button-stub[icon="check"]').trigger("click");
      expect(wrapper.emitted("bookAppointment")).toBeTruthy();
    });
  });

  describe("Rebooking Mode", () => {
    it("shows reschedule and cancel buttons in rebookOrCancelDialog mode", () => {
      const wrapper = createWrapper({ rebookOrCancelDialog: true });
      expect(wrapper.find('muc-button-stub[icon="arrow-right"]').exists()).toBe(true);
      expect(wrapper.find('muc-button-stub[icon="close"]').exists()).toBe(true);
    });

    it("emits rescheduleAppointment event when reschedule button is clicked", async () => {
      const wrapper = createWrapper({ rebookOrCancelDialog: true });
      await wrapper.find('muc-button-stub[icon="arrow-right"]').trigger("click");
      expect(wrapper.emitted("rescheduleAppointment")).toBeTruthy();
    });

    it("emits cancelAppointment event when cancel button is clicked", async () => {
      const wrapper = createWrapper({ rebookOrCancelDialog: true });
      await wrapper.find('muc-button-stub[icon="close"]').trigger("click");
      expect(wrapper.emitted("cancelAppointment")).toBeTruthy();
    });
  });

  describe("Rebooking Flow", () => {
    it("shows reschedule and cancel buttons in rebooking mode", () => {
      const wrapper = createWrapper({ isRebooking: true });
      expect(wrapper.find('muc-button-stub[icon="check"]').exists()).toBe(true);
      expect(wrapper.find('muc-button-stub[icon="close"]').exists()).toBe(true);
    });

    it("emits bookAppointment event when reschedule button is clicked", async () => {
      const wrapper = createWrapper({ isRebooking: true });
      await wrapper.find('muc-button-stub[icon="check"]').trigger("click");
      expect(wrapper.emitted("bookAppointment")).toBeTruthy();
    });

    it("emits cancelReschedule event when cancel button is clicked", async () => {
      const wrapper = createWrapper({ isRebooking: true });
      await wrapper.find('muc-button-stub[icon="close"]').trigger("click");
      expect(wrapper.emitted("cancelReschedule")).toBeTruthy();
    });
  });
}); 