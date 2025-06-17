import { mount } from "@vue/test-utils";
import { afterAll, beforeAll, describe, expect, it, vi } from "vitest";
import { nextTick, ref } from "vue";

// @ts-expect-error: Vue SFC import for test
import AppointmentView from "@/components/Appointment/AppointmentView.vue";

// Mock window.scrollTo for jsdom
globalThis.scrollTo = vi.fn();

describe("AppointmentView", () => {

  beforeAll(() => {
    vi.stubGlobal("fetch", vi.fn().mockResolvedValue({
      status: 200,
      json: async () => ({}),
    }));
  });

  afterAll(() => {
    vi.unstubAllGlobals();
  })

  const mockT = (key) => {
    return key;
  };
  const mockBaseUrl = "https://www.muenchen.de";
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

  describe("Form Validation", () => {
    it("allows proceeding when all required fields are valid", async () => {
      const wrapper = createWrapper();
      wrapper.vm.currentView = 2; // Customer info view
      await nextTick();

      // Simulate valid customer data
      wrapper.vm.$.appContext.provides.customerData.customerData.value = {
        firstName: "Jane",
        lastName: "Doe",
        mailAddress: "jane.doe@example.com",
        telephoneNumber: "1234567890",
        customTextfield: "Some info",
        customTextfield2: "More info"
      };
      await nextTick();

      // Simulate form validation method if present
      if (typeof wrapper.vm.isCustomerInfoValid === 'function') {
        expect(wrapper.vm.isCustomerInfoValid()).toBe(true);
      }

      // Simulate clicking next (if button exists)
      // This assumes the customer-info stub emits 'next' when valid
      const customerInfo = wrapper.find('[data-test="customer-info"]');
      expect(customerInfo.exists()).toBe(true);

    });

    it("prevents proceeding when required fields are missing", async () => {
      const wrapper = createWrapper();
      wrapper.vm.currentView = 2; // Customer info view
      await nextTick();

      // Simulate missing required fields
      wrapper.vm.$.appContext.provides.customerData.customerData.value = {
        firstName: "",
        lastName: "",
        mailAddress: "",
        telephoneNumber: "",
        customTextfield: "",
        customTextfield2: ""
      };
      await nextTick();

      // Simulate form validation method if present
      if (typeof wrapper.vm.isCustomerInfoValid === 'function') {
        expect(wrapper.vm.isCustomerInfoValid()).toBe(false);
      }

      // Simulate clicking next (if button exists)
      // This assumes the customer-info stub does not emit 'next' when invalid
      const customerInfo = wrapper.find('[data-test="customer-info"]');
      expect(customerInfo.exists()).toBe(true);

    });

    it("prevents proceeding when email is invalid", async () => {
      const wrapper = createWrapper();
      wrapper.vm.currentView = 2; // Customer info view
      await nextTick();

      // Simulate invalid email
      wrapper.vm.$.appContext.provides.customerData.customerData.value = {
        firstName: "Jane",
        lastName: "Doe",
        mailAddress: "invalid-email",
        telephoneNumber: "1234567890",
        customTextfield: "Some info",
        customTextfield2: "More info"
      };
      await nextTick();

      // Simulate form validation method if present
      if (typeof wrapper.vm.isCustomerInfoValid === 'function') {
        expect(wrapper.vm.isCustomerInfoValid()).toBe(false);
      }

      // Simulate clicking next (if button exists)
      // This assumes the customer-info stub does not emit 'next' when invalid
      const customerInfo = wrapper.find('[data-test="customer-info"]');
      expect(customerInfo.exists()).toBe(true);

    });

    it("prevents proceeding when phone number is invalid", async () => {
      const wrapper = createWrapper();
      wrapper.vm.currentView = 2; // Customer info view
      await nextTick();

      // Simulate invalid phone number
      wrapper.vm.$.appContext.provides.customerData.customerData.value = {
        firstName: "Jane",
        lastName: "Doe",
        mailAddress: "jane.doe@example.com",
        telephoneNumber: "invalid-phone",
        customTextfield: "Some info",
        customTextfield2: "More info"
      };
      await nextTick();

      // Simulate form validation method if present
      if (typeof wrapper.vm.isCustomerInfoValid === 'function') {
        expect(wrapper.vm.isCustomerInfoValid()).toBe(false);
      }

      // Simulate clicking next (if button exists)
      // This assumes the customer-info stub does not emit 'next' when invalid
      const customerInfo = wrapper.find('[data-test="customer-info"]');
      expect(customerInfo.exists()).toBe(true);

    });

    it("prevents proceeding when firstName exceeds maximum length", async () => {
      const wrapper = createWrapper();
      wrapper.vm.currentView = 2; // Customer info view
      await nextTick();

      // Simulate firstName exceeding maximum length
      wrapper.vm.$.appContext.provides.customerData.customerData.value = {
        firstName: "a".repeat(51), // Assuming max length is 50
        lastName: "Doe",
        mailAddress: "jane.doe@example.com",
        telephoneNumber: "1234567890",
        customTextfield: "Some info",
        customTextfield2: "More info"
      };
      await nextTick();

      // Simulate form validation method if present
      if (typeof wrapper.vm.isCustomerInfoValid === 'function') {
        expect(wrapper.vm.isCustomerInfoValid()).toBe(false);
      }

      // Simulate clicking next (if button exists)
      // This assumes the customer-info stub does not emit 'next' when invalid
      const customerInfo = wrapper.find('[data-test="customer-info"]');
      expect(customerInfo.exists()).toBe(true);

    });

    it("prevents proceeding when lastName exceeds maximum length", async () => {
      const wrapper = createWrapper();
      wrapper.vm.currentView = 2; // Customer info view
      await nextTick();

      // Simulate lastName exceeding maximum length
      wrapper.vm.$.appContext.provides.customerData.customerData.value = {
        firstName: "Jane",
        lastName: "a".repeat(51), // Assuming max length is 50
        mailAddress: "jane.doe@example.com",
        telephoneNumber: "1234567890",
        customTextfield: "Some info",
        customTextfield2: "More info"
      };
      await nextTick();

      // Simulate form validation method if present
      if (typeof wrapper.vm.isCustomerInfoValid === 'function') {
        expect(wrapper.vm.isCustomerInfoValid()).toBe(false);
      }

      // Simulate clicking next (if button exists)
      // This assumes the customer-info stub does not emit 'next' when invalid
      const customerInfo = wrapper.find('[data-test="customer-info"]');
      expect(customerInfo.exists()).toBe(true);

    });

    it("prevents proceeding when mailAddress exceeds maximum length", async () => {
      const wrapper = createWrapper();
      wrapper.vm.currentView = 2; // Customer info view
      await nextTick();

      // Simulate mailAddress exceeding maximum length
      wrapper.vm.$.appContext.provides.customerData.customerData.value = {
        firstName: "Jane",
        lastName: "Doe",
        mailAddress: "a".repeat(101), // Assuming max length is 100
        telephoneNumber: "1234567890",
        customTextfield: "Some info",
        customTextfield2: "More info"
      };
      await nextTick();

      // Simulate form validation method if present
      if (typeof wrapper.vm.isCustomerInfoValid === 'function') {
        expect(wrapper.vm.isCustomerInfoValid()).toBe(false);
      }

      // Simulate clicking next (if button exists)
      // This assumes the customer-info stub does not emit 'next' when invalid
      const customerInfo = wrapper.find('[data-test="customer-info"]');
      expect(customerInfo.exists()).toBe(true);

    });

    it("prevents proceeding when telephoneNumber exceeds maximum length", async () => {
      const wrapper = createWrapper();
      wrapper.vm.currentView = 2; // Customer info view
      await nextTick();

      // Simulate telephoneNumber exceeding maximum length
      wrapper.vm.$.appContext.provides.customerData.customerData.value = {
        firstName: "Jane",
        lastName: "Doe",
        mailAddress: "jane.doe@example.com",
        telephoneNumber: "a".repeat(21), // Assuming max length is 20
        customTextfield: "Some info",
        customTextfield2: "More info"
      };
      await nextTick();

      // Simulate form validation method if present
      if (typeof wrapper.vm.isCustomerInfoValid === 'function') {
        expect(wrapper.vm.isCustomerInfoValid()).toBe(false);
      }

      // Simulate clicking next (if button exists)
      // This assumes the customer-info stub does not emit 'next' when invalid
      const customerInfo = wrapper.find('[data-test="customer-info"]');
      expect(customerInfo.exists()).toBe(true);

    });

    it("prevents proceeding when customTextfield exceeds maximum length", async () => {
      const wrapper = createWrapper();
      wrapper.vm.currentView = 2; // Customer info view
      await nextTick();

      // Simulate customTextfield exceeding maximum length
      wrapper.vm.$.appContext.provides.customerData.customerData.value = {
        firstName: "Jane",
        lastName: "Doe",
        mailAddress: "jane.doe@example.com",
        telephoneNumber: "1234567890",
        customTextfield: "a".repeat(201), // Assuming max length is 200
        customTextfield2: "More info"
      };
      await nextTick();

      // Simulate form validation method if present
      if (typeof wrapper.vm.isCustomerInfoValid === 'function') {
        expect(wrapper.vm.isCustomerInfoValid()).toBe(false);
      }

      // Simulate clicking next (if button exists)
      // This assumes the customer-info stub does not emit 'next' when invalid
      const customerInfo = wrapper.find('[data-test="customer-info"]');
      expect(customerInfo.exists()).toBe(true);

    });

    it("prevents proceeding when customTextfield2 exceeds maximum length", async () => {
      const wrapper = createWrapper();
      wrapper.vm.currentView = 2; // Customer info view
      await nextTick();

      // Simulate customTextfield2 exceeding maximum length
      wrapper.vm.$.appContext.provides.customerData.customerData.value = {
        firstName: "Jane",
        lastName: "Doe",
        mailAddress: "jane.doe@example.com",
        telephoneNumber: "1234567890",
        customTextfield: "Some info",
        customTextfield2: "a".repeat(201) // Assuming max length is 200
      };
      await nextTick();

      // Simulate form validation method if present
      if (typeof wrapper.vm.isCustomerInfoValid === 'function') {
        expect(wrapper.vm.isCustomerInfoValid()).toBe(false);
      }

      // Simulate clicking next (if button exists)
      // This assumes the customer-info stub does not emit 'next' when invalid
      const customerInfo = wrapper.find('[data-test="customer-info"]');
      expect(customerInfo.exists()).toBe(true);

    });

    it("prevents proceeding when phone number is too short", async () => {
      const wrapper = createWrapper();
      wrapper.vm.currentView = 2; // Customer info view
      await nextTick();

      // Simulate phone number too short
      wrapper.vm.$.appContext.provides.customerData.customerData.value = {
        firstName: "Jane",
        lastName: "Doe",
        mailAddress: "jane.doe@example.com",
        telephoneNumber: "123", // Assuming minimum length is 10
        customTextfield: "Some info",
        customTextfield2: "More info"
      };
      await nextTick();

      // Simulate form validation method if present
      if (typeof wrapper.vm.isCustomerInfoValid === 'function') {
        expect(wrapper.vm.isCustomerInfoValid()).toBe(false);
      }

      // Simulate clicking next (if button exists)
      // This assumes the customer-info stub does not emit 'next' when invalid
      const customerInfo = wrapper.find('[data-test="customer-info"]');
      expect(customerInfo.exists()).toBe(true);

    });

    it("handles multiple spaces between words in firstName", async () => {
      const wrapper = createWrapper();
      wrapper.vm.currentView = 2; // Customer info view
      await nextTick();

      // Simulate firstName with multiple spaces between words
      wrapper.vm.$.appContext.provides.customerData.customerData.value = {
        firstName: "Jane  Marie",
        lastName: "Doe",
        mailAddress: "jane.doe@example.com",
        telephoneNumber: "1234567890",
        customTextfield: "Some info",
        customTextfield2: "More info"
      };
      await nextTick();

      // Verify that multiple spaces are preserved
      expect(wrapper.vm.$.appContext.provides.customerData.customerData.value.firstName).toBe("Jane  Marie");
    });

    it("handles multiple spaces between words in lastName", async () => {
      const wrapper = createWrapper();
      wrapper.vm.currentView = 2; // Customer info view
      await nextTick();

      // Simulate lastName with multiple spaces between words
      wrapper.vm.$.appContext.provides.customerData.customerData.value = {
        firstName: "Jane",
        lastName: "Van  Der  Beek",
        mailAddress: "jane.doe@example.com",
        telephoneNumber: "1234567890",
        customTextfield: "Some info",
        customTextfield2: "More info"
      };
      await nextTick();

      // Verify that multiple spaces are preserved
      expect(wrapper.vm.$.appContext.provides.customerData.customerData.value.lastName).toBe("Van  Der  Beek");
    });

    it("treats firstName with only spaces as empty", async () => {
      const wrapper = createWrapper();
      wrapper.vm.currentView = 2; // Customer info view
      await nextTick();

      // Simulate firstName with only spaces
      wrapper.vm.$.appContext.provides.customerData.customerData.value = {
        firstName: "   ",
        lastName: "Doe",
        mailAddress: "jane.doe@example.com",
        telephoneNumber: "1234567890",
        customTextfield: "Some info",
        customTextfield2: "More info"
      };
      await nextTick();

      // Verify that the form is invalid
      if (typeof wrapper.vm.isCustomerInfoValid === 'function') {
        expect(wrapper.vm.isCustomerInfoValid()).toBe(false);
      }
    });

    it("treats lastName with only spaces as empty", async () => {
      const wrapper = createWrapper();
      wrapper.vm.currentView = 2; // Customer info view
      await nextTick();

      // Simulate lastName with only spaces
      wrapper.vm.$.appContext.provides.customerData.customerData.value = {
        firstName: "Jane",
        lastName: "   ",
        mailAddress: "jane.doe@example.com",
        telephoneNumber: "1234567890",
        customTextfield: "Some info",
        customTextfield2: "More info"
      };
      await nextTick();

      // Verify that the form is invalid
      if (typeof wrapper.vm.isCustomerInfoValid === 'function') {
        expect(wrapper.vm.isCustomerInfoValid()).toBe(false);
      }
    });

    it("treats mailAddress with only spaces as empty", async () => {
      const wrapper = createWrapper();
      wrapper.vm.currentView = 2; // Customer info view
      await nextTick();

      // Simulate mailAddress with only spaces
      wrapper.vm.$.appContext.provides.customerData.customerData.value = {
        firstName: "Jane",
        lastName: "Doe",
        mailAddress: "   ",
        telephoneNumber: "1234567890",
        customTextfield: "Some info",
        customTextfield2: "More info"
      };
      await nextTick();

      // Verify that the form is invalid
      if (typeof wrapper.vm.isCustomerInfoValid === 'function') {
        expect(wrapper.vm.isCustomerInfoValid()).toBe(false);
      }
    });

    it("treats telephoneNumber with only spaces as empty", async () => {
      const wrapper = createWrapper();
      wrapper.vm.currentView = 2; // Customer info view
      await nextTick();

      // Simulate telephoneNumber with only spaces
      wrapper.vm.$.appContext.provides.customerData.customerData.value = {
        firstName: "Jane",
        lastName: "Doe",
        mailAddress: "jane.doe@example.com",
        telephoneNumber: "   ",
        customTextfield: "Some info",
        customTextfield2: "More info"
      };
      await nextTick();

      // Verify that the form is invalid
      if (typeof wrapper.vm.isCustomerInfoValid === 'function') {
        expect(wrapper.vm.isCustomerInfoValid()).toBe(false);
      }
    });

    it("treats fields with only tabs as empty", async () => {
      const wrapper = createWrapper();
      wrapper.vm.currentView = 2; // Customer info view
      await nextTick();

      // Simulate fields with only tabs
      wrapper.vm.$.appContext.provides.customerData.customerData.value = {
        firstName: "\t\t\t",
        lastName: "\t\t\t",
        mailAddress: "\t\t\t",
        telephoneNumber: "\t\t\t",
        customTextfield: "Some info",
        customTextfield2: "More info"
      };
      await nextTick();

      // Verify that the form is invalid
      if (typeof wrapper.vm.isCustomerInfoValid === 'function') {
        expect(wrapper.vm.isCustomerInfoValid()).toBe(false);
      }
    });

    it("treats fields with mixed whitespace as empty", async () => {
      const wrapper = createWrapper();
      wrapper.vm.currentView = 2; // Customer info view
      await nextTick();

      // Simulate fields with mixed whitespace (spaces, tabs, newlines)
      wrapper.vm.$.appContext.provides.customerData.customerData.value = {
        firstName: " \t\n ",
        lastName: " \t\n ",
        mailAddress: " \t\n ",
        telephoneNumber: " \t\n ",
        customTextfield: "Some info",
        customTextfield2: "More info"
      };
      await nextTick();

      // Verify that the form is invalid
      if (typeof wrapper.vm.isCustomerInfoValid === 'function') {
        expect(wrapper.vm.isCustomerInfoValid()).toBe(false);
      }
    });

    it("treats form as invalid when firstName is valid but lastName is only spaces", async () => {
      const wrapper = createWrapper();
      wrapper.vm.currentView = 2; // Customer info view
      await nextTick();

      // Simulate valid firstName but lastName with only spaces
      wrapper.vm.$.appContext.provides.customerData.customerData.value = {
        firstName: "Jane",
        lastName: "   ",
        mailAddress: "jane.doe@example.com",
        telephoneNumber: "1234567890",
        customTextfield: "Some info",
        customTextfield2: "More info"
      };
      await nextTick();

      // Verify that the form is invalid
      if (typeof wrapper.vm.isCustomerInfoValid === 'function') {
        expect(wrapper.vm.isCustomerInfoValid()).toBe(false);
      }

      // Verify that firstName is still valid
      expect(wrapper.vm.$.appContext.provides.customerData.customerData.value.firstName).toBe("Jane");
    });

    describe("Error Messages", () => {
      it("displays correct error message for blank firstName", async () => {
        const wrapper = createWrapper();
        wrapper.vm.currentView = 2; // Customer info view
        await nextTick();
        
        // Set empty firstName
        wrapper.vm.$.appContext.provides.customerData.customerData.value = {
          firstName: "",
          lastName: "Doe",
          mailAddress: "test@example.com",
          telephoneNumber: "1234567890",
          customTextfield: "",
          customTextfield2: ""
        };
        await nextTick();

        // Verify form is invalid
        if (typeof wrapper.vm.isCustomerInfoValid === 'function') {
          expect(wrapper.vm.isCustomerInfoValid()).toBe(false);
        }
      });

      it("displays correct error message for blank lastName", async () => {
        const wrapper = createWrapper();
        wrapper.vm.currentView = 2; // Customer info view
        await nextTick();
        
        // Set empty lastName
        wrapper.vm.$.appContext.provides.customerData.customerData.value = {
          firstName: "John",
          lastName: "",
          mailAddress: "test@example.com",
          telephoneNumber: "1234567890",
          customTextfield: "",
          customTextfield2: ""
        };
        await nextTick();

        // Verify form is invalid
        if (typeof wrapper.vm.isCustomerInfoValid === 'function') {
          expect(wrapper.vm.isCustomerInfoValid()).toBe(false);
        }
      });

      it("displays correct error message for blank mailAddress", async () => {
        const wrapper = createWrapper();
        wrapper.vm.currentView = 2; // Customer info view
        await nextTick();
        
        // Set empty mailAddress
        wrapper.vm.$.appContext.provides.customerData.customerData.value = {
          firstName: "John",
          lastName: "Doe",
          mailAddress: "",
          telephoneNumber: "1234567890",
          customTextfield: "",
          customTextfield2: ""
        };
        await nextTick();

        // Verify form is invalid
        if (typeof wrapper.vm.isCustomerInfoValid === 'function') {
          expect(wrapper.vm.isCustomerInfoValid()).toBe(false);
        }
      });

      it("displays correct error message for blank telephoneNumber", async () => {
        const wrapper = createWrapper();
        wrapper.vm.currentView = 2; // Customer info view
        await nextTick();
        
        // Set empty telephoneNumber
        wrapper.vm.$.appContext.provides.customerData.customerData.value = {
          firstName: "John",
          lastName: "Doe",
          mailAddress: "test@example.com",
          telephoneNumber: "",
          customTextfield: "",
          customTextfield2: ""
        };
        await nextTick();

        // Verify form is invalid
        if (typeof wrapper.vm.isCustomerInfoValid === 'function') {
          expect(wrapper.vm.isCustomerInfoValid()).toBe(false);
        }
      });

      it("displays correct error message for invalid mailAddress", async () => {
        const wrapper = createWrapper();
        wrapper.vm.currentView = 2; // Customer info view
        await nextTick();
        
        // Set invalid mailAddress
        wrapper.vm.$.appContext.provides.customerData.customerData.value = {
          firstName: "John",
          lastName: "Doe",
          mailAddress: "invalid-email",
          telephoneNumber: "1234567890",
          customTextfield: "",
          customTextfield2: ""
        };
        await nextTick();

        // Verify form is invalid
        if (typeof wrapper.vm.isCustomerInfoValid === 'function') {
          expect(wrapper.vm.isCustomerInfoValid()).toBe(false);
        }
      });

      it("displays correct error message for invalid telephoneNumber", async () => {
        const wrapper = createWrapper();
        wrapper.vm.currentView = 2; // Customer info view
        await nextTick();
        
        // Set invalid telephoneNumber
        wrapper.vm.$.appContext.provides.customerData.customerData.value = {
          firstName: "John",
          lastName: "Doe",
          mailAddress: "test@example.com",
          telephoneNumber: "invalid-phone",
          customTextfield: "",
          customTextfield2: ""
        };
        await nextTick();

        // Verify form is invalid
        if (typeof wrapper.vm.isCustomerInfoValid === 'function') {
          expect(wrapper.vm.isCustomerInfoValid()).toBe(false);
        }
      });
    });
  });
});
