import { mount } from "@vue/test-utils";
import { describe, expect, it, beforeEach, vi } from "vitest";
import { nextTick, ref } from "vue";

// @ts-expect-error: Vue SFC import for test
import AppointmentView from "@/components/Appointment/AppointmentView.vue";

// Mock window.scrollTo for jsdom
globalThis.scrollTo = vi.fn();

describe("AppointmentView", () => {
  const mockT = (key: string) => key;
  const mockBaseUrl = "http://test.com";
  const mockServiceId = "123";
  const mockLocationId = "456";
  const mockExclusiveLocation = "test-location";
  const mockAppointmentHash = "test-hash";

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
  });

  const mockAppointment = ref({
    timestamp: Math.floor(Date.now() / 1000),
    familyName: "John Doe",
    email: "john@example.com",
    telephone: "1234567890",
  });

  const createWrapper = (props = {}) => {
    return mount(AppointmentView, {
      props: {
        baseUrl: mockBaseUrl,
        serviceId: mockServiceId,
        locationId: mockLocationId,
        exclusiveLocation: mockExclusiveLocation,
        appointmentHash: mockAppointmentHash,
        t: mockT,
        ...props,
      },
      global: {
        provide: {
          selectedServiceProvider: {
            selectedService: mockSelectedService,
            updateSelectedService: vi.fn(),
          },
          selectedTimeslot: {
            selectedProvider: mockSelectedProvider,
            selectedTimeslot: ref(0),
          },
          customerData: {
            customerData: ref({
              firstName: "",
              lastName: "",
              mailAddress: "",
              telephoneNumber: "",
              customTextfield: "",
              customTextfield2: "",
            }),
          },
          appointment: {
            appointment: mockAppointment,
          },
        },
        stubs: {
          'service-finder': {
            template: "<div data-test='service-finder'></div>",
            props: ["baseUrl", "preselectedServiceId", "preselectedOfficeId", "exclusiveLocation", "t"],
            emits: ["next", "captchaTokenChanged"],
          },
          'calendar-view': {
            template: "<div data-test='calendar-view'></div>",
            props: ["baseUrl", "isRebooking", "exclusiveLocation", "preselectedOfficeId", "selectedServiceMap", "captchaToken", "t", "bookingError", "bookingErrorKey"],
            emits: ["back", "next"],
          },
          'customer-info': {
            template: "<div data-test='customer-info'></div>",
            props: ["t"],
            emits: ["back", "next"],
          },
          'appointment-summary': {
            template: "<div data-test='appointment-summary'></div>",
            props: ["isRebooking", "rebookOrCancelDialog", "t"],
            emits: ["back", "bookAppointment", "cancelAppointment", "cancelReschedule", "rescheduleAppointment"],
          },
          'muc-stepper': {
            template: "<div data-test='muc-stepper' :data-disable-previous-steps='disablePreviousSteps'></div>",
            props: ["stepItems", "activeItem", "disablePreviousSteps"],
            emits: ["changeStep"],
          },
          'muc-callout': {
            props: ["type"],
            template: `<div data-test='muc-callout' :data-type="type"></div>`
          },
        },
      },
    });
  };

  describe("View States", () => {
    it("shows service finder in initial view", async () => {
      const wrapper = createWrapper({ appointmentHash: undefined });
      wrapper.vm.currentView = 0;
      await nextTick();
      expect(wrapper.find('[data-test="service-finder"]').exists()).toBe(true);
    });

    it("shows calendar view when service is selected", async () => {
      const wrapper = createWrapper({ appointmentHash: undefined });
      wrapper.vm.currentView = 1;
      await nextTick();
      expect(wrapper.find('[data-test="calendar-view"]').exists()).toBe(true);
    });

    it("shows customer info after calendar selection", async () => {
      const wrapper = createWrapper({ appointmentHash: undefined });
      wrapper.vm.currentView = 2;
      await nextTick();
      expect(wrapper.find('[data-test="customer-info"]').exists()).toBe(true);
    });

    it("shows appointment summary after customer info", async () => {
      const wrapper = createWrapper({ appointmentHash: undefined });
      wrapper.vm.currentView = 3;
      await nextTick();
      expect(wrapper.find('[data-test="appointment-summary"]').exists()).toBe(true);
    });
  });

  describe("Error States", () => {
    it("shows appointment not found error", async () => {
      const wrapper = createWrapper();
      wrapper.vm.appointmentNotFoundError = true;
      await nextTick();
      expect(wrapper.find('[data-test="muc-callout"]').exists()).toBe(true);
      expect(wrapper.find('[data-test="muc-callout"]').attributes('data-type')).toBe("error");
    });

    it("shows booking error", async () => {
      const wrapper = createWrapper();
      wrapper.vm.confirmAppointmentError = true;
      await nextTick();
      expect(wrapper.find('[data-test="muc-callout"]').exists()).toBe(true);
      expect(wrapper.find('[data-test="muc-callout"]').attributes('data-type')).toBe("error");
    });
  });

  describe("Success States", () => {
    it("shows success message after booking", async () => {
      const wrapper = createWrapper();
      wrapper.vm.confirmAppointmentSuccess = true;
      await nextTick();
      expect(wrapper.find('[data-test="muc-callout"]').exists()).toBe(true);
      expect(wrapper.find('[data-test="muc-callout"]').attributes('data-type')).toBe("success");
    });

    it("shows success message after cancellation", async () => {
      const wrapper = createWrapper();
      wrapper.vm.cancelAppointmentSuccess = true;
      wrapper.vm.currentView = 4;
      await nextTick();
      expect(wrapper.find('[data-test="muc-callout"]').exists()).toBe(true);
      expect(wrapper.find('[data-test="muc-callout"]').attributes('data-type')).toBe("success");
    });
  });

  describe("Navigation", () => {
    it("allows going back to previous steps", async () => {
      const wrapper = createWrapper();
      wrapper.vm.currentView = 2;
      await nextTick();
      wrapper.vm.currentView = 1; // Simulate going back
      await nextTick();
      expect(wrapper.find('[data-test="calendar-view"]').exists()).toBe(true);
    });

    it("disables previous steps in stepper when appointment hash is present", async () => {
      const wrapper = createWrapper({ appointmentHash: "valid" });
      await nextTick();
      expect(wrapper.find('[data-test="muc-stepper"]').attributes('data-disable-previous-steps')).toBe("true");
    });
  });

  describe("Stepper Navigation", () => {
    it("updates view when stepper emits change-step", async () => {
      const wrapper = createWrapper({ appointmentHash: undefined });
      wrapper.vm.currentView = 1;
      await nextTick();
      wrapper.vm.currentView = 0; // Simulate stepper navigation
      await nextTick();
      expect(wrapper.find('[data-test="service-finder"]').exists()).toBe(true);
    });
  });

  describe("Additional Error Callouts", () => {
    it("shows tooManyAppointmentsWithSameMailError callout in summary", async () => {
      const wrapper = createWrapper();
      wrapper.vm.currentView = 3;
      wrapper.vm.tooManyAppointmentsWithSameMailError = true;
      await nextTick();
      expect(wrapper.find('[data-test="muc-callout"]').exists()).toBe(true);
    });

    it("shows updateAppointmentError callout in summary", async () => {
      const wrapper = createWrapper();
      wrapper.vm.currentView = 3;
      wrapper.vm.updateAppointmentError = true;
      await nextTick();
      expect(wrapper.find('[data-test="muc-callout"]').exists()).toBe(true);
    });

    it("shows confirmAppointmentError callout after booking", async () => {
      const wrapper = createWrapper();
      wrapper.vm.confirmAppointmentError = true;
      await nextTick();
      expect(wrapper.find('[data-test="muc-callout"]').exists()).toBe(true);
      expect(wrapper.find('[data-test="muc-callout"]').attributes('data-type')).toBe("error");
    });
  });

  describe("Rebooking Flow", () => {
    it("starts at summary and disables previous steps when appointmentHash is present", async () => {
      const wrapper = createWrapper({ appointmentHash: "somehash" });
      wrapper.vm.currentView = 3;
      await nextTick();
      expect(wrapper.find('[data-test="appointment-summary"]').exists()).toBe(true);
      expect(wrapper.find('[data-test="muc-stepper"]').attributes('data-disable-previous-steps')).toBe("true");
    });

    it("shows cancellation success callout after cancelling in rebooking", async () => {
      const wrapper = createWrapper({ appointmentHash: "somehash" });
      wrapper.vm.currentView = 4;
      wrapper.vm.cancelAppointmentSuccess = true;
      await nextTick();
      expect(wrapper.find('[data-test="muc-callout"]').exists()).toBe(true);
      expect(wrapper.find('[data-test="muc-callout"]').attributes('data-type')).toBe("success");
    });
  });

  describe("Edge Cases", () => {
    it("shows booking error callout if confirmAppointmentHash is invalid", async () => {
      const wrapper = createWrapper({ confirmAppointmentHash: "invalid" });
      wrapper.vm.confirmAppointmentError = true;
      await nextTick();
      expect(wrapper.find('[data-test="muc-callout"]').exists()).toBe(true);
      expect(wrapper.find('[data-test="muc-callout"]').attributes('data-type')).toBe("error");
    });

    it("shows error callout in calendar view if appointmentNotAvailableError is set", async () => {
      const wrapper = createWrapper();
      wrapper.vm.currentView = 1;
      wrapper.vm.appointmentNotAvailableError = true;
      await nextTick();
      expect(wrapper.find('[data-test="muc-callout"]').exists()).toBe(true);
    });
  });

  describe("Confirmation View", () => {
    it("shows only confirmation message when confirmAppointmentHash is present", async () => {
      const wrapper = createWrapper({ confirmAppointmentHash: "valid" });
      await nextTick();
      expect(wrapper.find('[data-test="muc-stepper"]').exists()).toBe(false);
      expect(wrapper.find('[data-test="service-finder"]').exists()).toBe(false);
      expect(wrapper.find('[data-test="calendar-view"]').exists()).toBe(false);
      expect(wrapper.find('[data-test="customer-info"]').exists()).toBe(false);
      expect(wrapper.find('[data-test="appointment-summary"]').exists()).toBe(false);
      expect(wrapper.find('[data-test="muc-callout"]').exists()).toBe(true);
    });

    it("shows success message after successful confirmation", async () => {
      const wrapper = createWrapper({ confirmAppointmentHash: "valid" });
      wrapper.vm.confirmAppointmentSuccess = true;
      await nextTick();
      expect(wrapper.find('[data-test="muc-callout"]').exists()).toBe(true);
      expect(wrapper.find('[data-test="muc-callout"]').attributes('data-type')).toBe("success");
    });

    it("shows error message if confirmation fails", async () => {
      const wrapper = createWrapper({ confirmAppointmentHash: "invalid" });
      wrapper.vm.confirmAppointmentError = true;
      await nextTick();
      expect(wrapper.find('[data-test="muc-callout"]').exists()).toBe(true);
      expect(wrapper.find('[data-test="muc-callout"]').attributes('data-type')).toBe("error");
    });
  });
}); 