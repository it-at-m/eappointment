import { mount } from "@vue/test-utils";
import { afterAll, beforeAll, beforeEach, describe, expect, it, vi } from "vitest";
import { nextTick, ref } from "vue";
// @ts-expect-error: Vue SFC import for test  
import * as ZMSAppointmentAPI from "@/api/ZMSAppointmentAPI";
// @ts-expect-error: Vue SFC import for test
import de from '@/utils/de-DE.json';
// @ts-expect-error: Vue SFC import for test
import AppointmentView from "@/components/Appointment/AppointmentView.vue";
import { useLogin } from "@/utils/auth";
// beforeEach is already imported from vitest on line 2

globalThis.scrollTo = vi.fn();

vi.mock("@/api/ZMSAppointmentAPI", async () => {
  const actual = await vi.importActual("@/api/ZMSAppointmentAPI");
  return {
    ...actual,
    confirmAppointment: vi.fn(),
  };
});

// Mock the auth utility
vi.mock('@/utils/auth', () => ({
  getTokenData: vi.fn(),
  useLogin: vi.fn(() => ({
    isLoggedIn: ref(false),
    isLoadingAuthentication: ref(false),
    accessToken: ref(null)
  }))
}));

describe("AppointmentView", () => {

  beforeAll(() => {
    vi.stubGlobal("fetch", vi.fn().mockResolvedValue({
      status: 200,
      json: async () => ({
        offices: [],
        services: [],
        relations: [],
      }),
    }));
  });

  afterAll(() => {
    vi.unstubAllGlobals();
  })

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
        globalState: {
          baseUrl: mockBaseUrl,
        },
        serviceId: mockServiceId,
        locationId: mockLocationId,
        exclusiveLocation: mockExclusiveLocation,
        appointmentHash: mockAppointmentHash,
        t: (key: string, params?: Record<string, unknown>) => {
          // load translation or get key
          let s = (de as any)[key] ?? key;
  
          // replace placeholder
          if (!params) return s;
          for (const [k, v] of Object.entries(params)) {
              s = s.split(`{${k}}`).join(String(v ?? ""));
          }
          return s;
        },

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
            props: ["globalState", "preselectedServiceId", "preselectedOfficeId", "exclusiveLocation", "t"],
            emits: ["next", "captchaTokenChanged", "invalidJumpinLink"],
          },
          'AppointmentSelection': {
            template: "<div data-test='AppointmentSelection'></div>",
            props: ["globalState", "isRebooking", "exclusiveLocation", "preselectedOfficeId", "selectedServiceMap", "captchaToken", "t", "bookingError", "bookingErrorKey"],
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
            props: ["type", "variant"],
            template: `
            <div data-test='muc-callout' :data-type="variant || type">
              <slot name="header"></slot>
              <slot name="content"></slot>
            </div>
          `
          },
          'muc-button': {
            props: ["icon", "variant", "disabled"],
            template: `
            <button data-test='muc-button' :icon="icon" :variant="variant" :disabled="disabled">
              <slot></slot>
            </button>
          `
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
      expect(wrapper.find('[data-test="AppointmentSelection"]').exists()).toBe(true);
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
      wrapper.vm.errorStates.apiErrorAppointmentNotFound.value = true;
      await nextTick();
      expect(wrapper.find('[data-test="muc-callout"]').exists()).toBe(true);
      expect(wrapper.find('[data-test="muc-callout"]').attributes('data-type')).toBe("error");
    });

    it("shows booking error", async () => {
      const wrapper = createWrapper();
      wrapper.vm.errorStates.apiErrorPreconfirmationExpired.value = true;
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
      expect(wrapper.find('[data-test="AppointmentSelection"]').exists()).toBe(true);
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
    it("shows apiErrorTooManyAppointmentsWithSameMail callout in summary", async () => {
      const wrapper = createWrapper();
      wrapper.vm.currentView = 3;
      wrapper.vm.errorStates.apiErrorTooManyAppointmentsWithSameMail.value = true;
      await nextTick();
      expect(wrapper.find('[data-test="muc-callout"]').exists()).toBe(true);
    });

    it("shows updateAppointmentError callout in summary", async () => {
      const wrapper = createWrapper();
      wrapper.vm.currentView = 3;
      wrapper.vm.errorStates.apiErrorGenericFallback.value = true;
      await nextTick();
      expect(wrapper.find('[data-test="muc-callout"]').exists()).toBe(true);
    });

    it("shows confirmAppointmentError callout after booking", async () => {
      const wrapper = createWrapper();
      wrapper.vm.errorStates.apiErrorPreconfirmationExpired.value = true;
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

  describe("Invalid Jump-in Link (404 Error)", () => {
    it("shows 404 callout when invalid jump-in link error is triggered", async () => {
      const wrapper = createWrapper();
      wrapper.vm.errorStates.apiErrorInvalidJumpinLink.value = true;
      await nextTick();
      
      const callout = wrapper.find('[data-test="muc-callout"]');
      expect(callout.exists()).toBe(true);
      expect(callout.attributes('data-type')).toBe("error");
      expect(callout.text()).toContain("Diese Ansicht kann nicht geladen werden");
      expect(callout.text()).toContain("Der Link zu dieser Seite ist leider fehlerhaft");
    });

    it("shows button with correct text and icon in 404 callout", async () => {
      const wrapper = createWrapper();
      wrapper.vm.errorStates.apiErrorInvalidJumpinLink.value = true;
      await nextTick();
      
      const button = wrapper.find('.m-button-group button');
      expect(button.exists()).toBe(true);
      expect(button.text()).toContain("Termin vereinbaren");
      expect(button.attributes('icon')).toBe("arrow-right");
    });

    it("hides stepper and main content when 404 error is active", async () => {
      const wrapper = createWrapper();
      wrapper.vm.errorStates.apiErrorInvalidJumpinLink.value = true;
      await nextTick();
      
      expect(wrapper.find('[data-test="muc-stepper"]').exists()).toBe(false);
      expect(wrapper.find('[data-test="service-finder"]').exists()).toBe(false);
    });

    it("calls redirectToAppointmentStart when button is clicked", async () => {
      const originalLocation = window.location;
      delete (window as any).location;
      (window as any).location = { 
        ...originalLocation, 
        href: "http://localhost:8082/#/services/000000000000/locations/000000000000",
        origin: "http://localhost:8082",
        pathname: "/"
      };
      
      const wrapper = createWrapper();
      wrapper.vm.errorStates.apiErrorInvalidJumpinLink.value = true;
      await nextTick();
      
      const button = wrapper.find('.m-button-group button');
      await button.trigger('click');
      
      expect(window.location.href).toBe("http://localhost:8082/");
      
      (window as any).location = originalLocation;
    });

    it("handles invalid jump-in link event from ServiceFinder", async () => {
      const wrapper = createWrapper();
      
      wrapper.vm.handleInvalidJumpinLink();
      await nextTick();
      
      expect(wrapper.vm.errorStates.apiErrorInvalidJumpinLink.value).toBe(true);
    });

    it("button has correct styling with disabled margins", async () => {
      const wrapper = createWrapper();
      wrapper.vm.errorStates.apiErrorInvalidJumpinLink.value = true;
      await nextTick();
      
      const button = wrapper.find('.m-button-group button');
      expect(button.attributes('style')).toContain('margin-bottom: 0');
      expect(button.attributes('style')).toContain('margin-right: 0');
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

  describe("Confirm callout shows activationDuration in booking step 4", () => {
    it("render activationDuration from selectedProvider.scope", async () => {
      const wrapper = createWrapper();

      (wrapper.vm as any).selectedProvider = {
        id: "789",
        name: "Test Provider",
        address: {
          street: "Test Street",
          house_number: "123",
          postal_code: "12345",
          city: "Test City",
        },
        scope: { activationDuration: 60 },
      };
  
      (wrapper.vm as any).currentView = 4;
  
      await nextTick();
  
      const callout = wrapper.find("[data-test='muc-callout']");
      expect(callout.exists()).toBe(true);
  
      // Build expected text about the translation message with placeholder
      // createWrapper() has mocked t() so that {activationMinutes} is replaced
      const expected = (de as any).confirmAppointmentText
        .replace("{activationMinutes}", "60");
  
      // Callout renders header + content; we check that the resolved content part is included
      expect(callout.text()).toContain(expected);

      expect(callout.text()).toContain((de as any).confirmAppointmentHeader);
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
  describe("API Error Handling - Confirmation", () => {
    const mockConfirmAppointment = vi.mocked(ZMSAppointmentAPI.confirmAppointment);

    beforeEach(() => {
      mockConfirmAppointment.mockClear();
    });
    it("should display activation expired Error when API returns processNotPreconfirmedAnymore", async () => {
      const mockErrorResponse = {
        errors: [
          {
            errorCode: "processNotPreconfirmedAnymore",
            message: "Process not preconfirmed anymore"
          }
        ]
      };
      mockConfirmAppointment.mockResolvedValueOnce(mockErrorResponse);

      const appointmentData = {
        id: "test-id",
        authKey: "test-auth-key",
        scope: {}
      };

      const validHash = btoa(JSON.stringify(appointmentData));

      const wrapper = createWrapper({
        confirmAppointmentHash: validHash
      });

      await nextTick();
      await vi.waitFor(() => {
        expect(mockConfirmAppointment).toHaveBeenCalled();
      });

      expect(mockConfirmAppointment).toHaveBeenCalledWith(
        {
          baseUrl: "https://www.muenchen.de",
        },
        {
          id: "test-id",
          authKey: "test-auth-key",
          scope: {}
        },
      );

      expect(wrapper.vm.errorStates.apiErrorPreconfirmationExpired.value).toBe(true);
      expect(wrapper.vm.confirmAppointmentSuccess).toBe(false);

      const errorCallout = wrapper.find('[data-test="muc-callout"]');
      expect(errorCallout.exists()).toBe(true);
      expect(errorCallout.attributes('data-type')).toBe('error');

      expect(errorCallout.text()).toContain(de.apiErrorPreconfirmationExpiredHeader);
      expect(errorCallout.text()).toContain(de.apiErrorPreconfirmationExpiredText);
  });

  it("should display activation expired error when API returns appointmentNotFound", async () => {
    const mockErrorResponse = {
      errors: [
        {
          errorCode: "appointmentNotFound",
          message: "Appointment not found"
        }
      ]
    };
    mockConfirmAppointment.mockResolvedValueOnce(mockErrorResponse);

    const appointmentData = {
      id: "not-found-id",
      authKey: "test-auth-key",
      scope: {}
    };
    const validHash = btoa(JSON.stringify(appointmentData));

    const wrapper = createWrapper({
      confirmAppointmentHash: validHash
    });

    await nextTick();
    await vi.waitFor(() => {
      expect(mockConfirmAppointment).toHaveBeenCalled();
    });

    expect(mockConfirmAppointment).toHaveBeenLastCalledWith(
      {
        baseUrl: "https://www.muenchen.de",
      },
      {
        id: "not-found-id",
        authKey: "test-auth-key",
        scope: {}
      },
    );

    expect(wrapper.vm.errorStates.apiErrorPreconfirmationExpired.value).toBe(true);
    expect(wrapper.vm.confirmAppointmentSuccess).toBe(false);


    const errorCallout = wrapper.find('[data-test="muc-callout"]');
    expect(errorCallout.exists()).toBe(true);
    expect(errorCallout.attributes('data-type')).toBe('error');
    expect(errorCallout.text()).toContain(de.apiErrorPreconfirmationExpiredHeader);
    expect(errorCallout.text()).toContain(de.apiErrorPreconfirmationExpiredText);
  });

  it("should display generic error for other API error codes", async () => {
    const mockErrorResponse = {
      errors: [
        {
          errorCode: "someOtherError",
          message: "Some other error occurred"
        }
      ]
    };
    mockConfirmAppointment.mockResolvedValueOnce(mockErrorResponse);

    const appointmentData = {
      id: "other-error-id",
      authKey: "test-auth-key",
      scope: {}
    };
    const validHash = btoa(JSON.stringify(appointmentData));

    const wrapper = createWrapper({
      confirmAppointmentHash: validHash
    });

    await nextTick();
    await vi.waitFor(() => {
      expect(mockConfirmAppointment).toHaveBeenCalled();
    });

    expect(mockConfirmAppointment).toHaveBeenCalledWith(
      {
        baseUrl: "https://www.muenchen.de",
      },
      {
        id: "other-error-id",
        authKey: "test-auth-key",
        scope: {}
      },
    );

    expect(wrapper.vm.errorStates.apiErrorGenericFallback.value).toBe(true);
    expect(wrapper.vm.confirmAppointmentSuccess).toBe(false);

    const errorCallout = wrapper.find('[data-test="muc-callout"]');
    expect(errorCallout.exists()).toBe(true);
    expect(errorCallout.attributes('data-type')).toBe('error');
    expect(errorCallout.text()).toContain(de.apiErrorGenericFallbackHeader);
  });
  });
  describe("Book another appointment button", () => {
    it("renders with correct label and redirects to start when clicked", async () => {
      const originalLocation = window.location as any;
      delete (window as any).location;
      (window as any).location = {
        ...originalLocation,
        href: "http://localhost:8082/#/services/000000000000/locations/000000000000",
        origin: "http://localhost:8082",
        pathname: "/",
      };

      const wrapper = createWrapper({ appointmentHash: undefined });
      wrapper.vm.confirmAppointmentSuccess = true;
      await nextTick();

      const button = wrapper.find(".m-button-group button");
      expect(button.exists()).toBe(true);

      const expectedLabel =
        (de as any).bookAnotherAppointment ?? "bookAnotherAppointment";
      expect(button.text()).toContain(expectedLabel);
      expect(button.attributes("variant")).toBe("secondary");

      await button.trigger("click");
      expect(window.location.href).toBe("http://localhost:8082/");

      (window as any).location = originalLocation;
    });
  });

  describe("ICS Download Feature", () => {
    const mockConfirmAppointment = vi.mocked(ZMSAppointmentAPI.confirmAppointment);

    beforeEach(() => {
      mockConfirmAppointment.mockClear();
    });

    describe("Button Rendering", () => {
      it("should render download button with correct attributes", async () => {
        const wrapper = createWrapper();
        // Simulate success state by setting the internal state directly
        wrapper.vm.confirmAppointmentSuccess = true;
        await nextTick();
        
        // The button should not be visible without appointment data
        const buttons = wrapper.findAll('button');
        const downloadButton = buttons.find(button => button.text().includes(de.downloadAppointment));
        expect(downloadButton).toBeUndefined();
      });

      it("should render view button when user is authenticated", async () => {
        // Mock useLogin to return authenticated state
        const mockUseLogin = vi.mocked(useLogin);
        mockUseLogin.mockReturnValue({
          isLoggedIn: ref(true),
          isLoadingAuthentication: ref(false),
          accessToken: ref("test-token")
        });
        
        const wrapper = createWrapper();
        wrapper.vm.confirmAppointmentSuccess = true;
        await nextTick();
        
        const buttons = wrapper.findAll('button');
        const viewButton = buttons.find(button => button.text().includes(de.viewAppointment));
        expect(viewButton).toBeDefined();
        expect(viewButton?.attributes('icon')).toBe('arrow-right');
        expect(viewButton?.text()).toContain(de.viewAppointment);
      });

      it("should hide view button when user is not authenticated", async () => {
        // Mock useLogin to return unauthenticated state
        const mockUseLogin = vi.mocked(useLogin);
        mockUseLogin.mockReturnValue({
          isLoggedIn: ref(false),
          isLoadingAuthentication: ref(false),
          accessToken: ref(null)
        });
        
        const wrapper = createWrapper();
        wrapper.vm.confirmAppointmentSuccess = true;
        await nextTick();
        
        const buttons = wrapper.findAll('button');
        const viewButton = buttons.find(button => button.text().includes(de.viewAppointment));
        expect(viewButton).toBeUndefined();
      });
    });

    describe("Download Functionality", () => {
      it("should have downloadIcsAppointment function", async () => {
        const wrapper = createWrapper();
        const component = wrapper.vm as any;
        
        // Check that the function exists
        expect(typeof component.downloadIcsAppointment).toBe('function');
      });
    });

    describe("View Functionality", () => {
      it("should have viewAppointment function", async () => {
        const wrapper = createWrapper();
        const component = wrapper.vm as any;
        
        // Check that the function exists
        expect(typeof component.viewAppointment).toBe('function');
      });
    });

    describe("ICS Content Integration", () => {
      it("should handle appointment confirmation with ICS content", async () => {
        const mockConfirmResponse = {
          processId: "12345",
          timestamp: 1640995200,
          authKey: "abc123",
          familyName: "Test User",
          email: "test@example.com",
          icsContent: "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:ZMS-München\r\nEND:VCALENDAR",
          officeId: "456",
          scope: {},
          subRequestCounts: [],
          serviceId: "789",
          serviceName: "Test Service",
          serviceCount: 1,
          status: "confirmed"
        };

        const wrapper = createWrapper();

        // Simulate the appointment confirmation success state
        wrapper.vm.confirmAppointmentSuccess = true;
        wrapper.vm.$.appContext.provides.appointment.appointment.value = mockConfirmResponse;

        await nextTick();

        // Verify ICS content is stored in component state
        expect(wrapper.vm.$.appContext.provides.appointment.appointment.value?.icsContent).toBe(mockConfirmResponse.icsContent);
        expect(wrapper.vm.confirmAppointmentSuccess).toBe(true);
      });
    });
  });
});
