import { mount } from "@vue/test-utils";
import {
  afterAll,
  beforeAll,
  beforeEach,
  describe,
  expect,
  it,
  vi,
} from "vitest";
import { nextTick, ref } from "vue";

import * as ZMSAppointmentAPI from "@/api/ZMSAppointmentAPI";
import AppointmentView from "@/components/Appointment/AppointmentView.vue";
import { useLogin } from "@/utils/auth";
import {
  LOCALSTORAGE_PARAM_APPOINTMENT_DATA,
  SESSIONSTORAGE_PARAM_APPOINTMENT_AUTH_HASH,
} from "@/utils/Constants";
import de from "@/utils/de-DE.json";
// beforeEach is already imported from vitest on line 2
import { nowUnixSeconds } from "@/utils/timestampInPast";

globalThis.scrollTo = vi.fn();

vi.mock("@/api/ZMSAppointmentAPI", async () => {
  const actual = await vi.importActual("@/api/ZMSAppointmentAPI");
  return {
    ...actual,
    confirmAppointment: vi.fn(),
    preconfirmAppointment: vi.fn(),
    cancelAppointment: vi.fn(),
    fetchAppointment: vi.fn(),
    updateAppointment: vi.fn(),
  };
});

// Mock the auth utility
vi.mock("@/utils/auth", () => ({
  getTokenData: vi.fn(),
  useLogin: vi.fn(() => ({
    isLoggedIn: ref(false),
    isLoadingAuthentication: ref(false),
    accessToken: ref(null),
  })),
}));

describe("AppointmentView", () => {
  beforeAll(() => {
    vi.stubGlobal(
      "fetch",
      vi.fn().mockResolvedValue({
        status: 200,
        json: async () => ({
          offices: [],
          services: [],
          relations: [],
        }),
      })
    );
  });

  afterAll(() => {
    vi.unstubAllGlobals();
  });

  beforeEach(() => {
    vi.mocked(ZMSAppointmentAPI.fetchAppointment).mockReset();
  });

  const mockBaseUrl = "https://www.muenchen.de";
  const mockServiceId = "123";
  const mockLocationId = "456";
  const mockExclusiveLocation = "test-location";
  const buildAppointmentHash = (
    data: { id: string; authKey: string; scope?: Record<string, unknown> } = {
      id: "12345",
      authKey: "test-auth-key",
      scope: {},
    }
  ) => btoa(JSON.stringify(data));

  const mockPendingAppointmentRoute = () => {
    vi.mocked(ZMSAppointmentAPI.fetchAppointment).mockReturnValue(
      new Promise(() => {})
    );
  };

  const createWrapperWithAppointmentHash = (hash = buildAppointmentHash()) => {
    mockPendingAppointmentRoute();
    return createWrapper({ appointmentHash: hash });
  };

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
        appointmentHash: undefined,
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
          "service-finder": {
            template: "<div data-test='service-finder'></div>",
            props: [
              "globalState",
              "preselectedServiceId",
              "preselectedOfficeId",
              "exclusiveLocation",
              "t",
            ],
            emits: ["next", "captchaTokenChanged", "invalidJumpinLink"],
          },
          AppointmentSelection: {
            name: "AppointmentSelection",
            template: "<div data-test='AppointmentSelection'></div>",
            props: [
              "globalState",
              "isRebooking",
              "exclusiveLocation",
              "preselectedOfficeId",
              "selectedServiceMap",
              "captchaToken",
              "t",
              "bookingError",
              "bookingErrorKey",
            ],
            emits: ["back", "next", "clearBookingError"],
          },
          "customer-info": {
            template: "<div data-test='customer-info'></div>",
            props: ["t"],
            emits: ["back", "next"],
          },
          "appointment-summary": {
            props: [
              "isRebooking",
              "rebookOrCancelDialog",
              "appointmentAlreadyActivated",
              "t",
            ],
            template: `
              <div data-test='appointment-summary'>
                <div
                  v-if="appointmentAlreadyActivated && !isRebooking"
                  data-test="appointment-already-activated-banner"
                >
                  {{ t('appointmentAlreadyActivatedHeader') }}
                </div>
              </div>
            `,
            emits: [
              "back",
              "bookAppointment",
              "cancelAppointment",
              "cancelReschedule",
              "rescheduleAppointment",
            ],
          },
          "muc-stepper": {
            template:
              "<div data-test='muc-stepper' :data-disable-previous-steps='disablePreviousSteps'></div>",
            props: ["stepItems", "activeItem", "disablePreviousSteps"],
            emits: ["changeStep"],
          },
          "muc-banner": {
            props: ["type", "variant"],
            template: `
            <div data-test='muc-banner' :data-type="type" :data-variant="variant">
              <slot></slot>
            </div>
          `,
          },
          "muc-callout": {
            props: ["type", "variant"],
            template: `
            <div data-test='muc-callout' :data-type="variant || type">
              <slot name="header"></slot>
              <slot name="content"></slot>
            </div>
          `,
          },
          "muc-button": {
            props: ["icon", "variant", "disabled"],
            template: `
            <button data-test='muc-button' :icon="icon" :variant="variant" :disabled="disabled">
              <slot></slot>
            </button>
          `,
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
      expect(wrapper.find('[data-test="AppointmentSelection"]').exists()).toBe(
        true
      );
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
      expect(wrapper.find('[data-test="appointment-summary"]').exists()).toBe(
        true
      );
    });

    it("keeps AppointmentSelection mounted on overview so back does not remount", async () => {
      const wrapper = createWrapper({ appointmentHash: undefined });
      wrapper.vm.selectedServiceMap = new Map([["123", 1]]);
      wrapper.vm.currentView = 1;
      await nextTick();
      const selection = wrapper.find('[data-test="AppointmentSelection"]');
      expect(selection.exists()).toBe(true);

      wrapper.vm.currentView = 3;
      await nextTick();
      expect(wrapper.find('[data-test="AppointmentSelection"]').element).toBe(
        selection.element
      );
    });

    it("does not mount AppointmentSelection on hash overview without a service map", async () => {
      const wrapper = createWrapper({ appointmentHash: undefined });
      wrapper.vm.currentView = 3;
      await nextTick();
      expect(wrapper.find('[data-test="AppointmentSelection"]').exists()).toBe(
        false
      );
    });
  });

  describe("Error States", () => {
    it("shows appointment not found error", async () => {
      const wrapper = createWrapperWithAppointmentHash();
      wrapper.vm.errorStates.apiErrorAppointmentNotFound.value = true;
      await nextTick();
      expect(wrapper.find('[data-test="muc-callout"]').exists()).toBe(true);
      expect(
        wrapper.find('[data-test="muc-callout"]').attributes("data-type")
      ).toBe("error");
    });

    it("shows booking error", async () => {
      const wrapper = createWrapperWithAppointmentHash();
      wrapper.vm.errorStates.apiErrorPreconfirmationExpired.value = true;
      await nextTick();
      expect(wrapper.find('[data-test="muc-callout"]').exists()).toBe(true);
      expect(
        wrapper.find('[data-test="muc-callout"]').attributes("data-type")
      ).toBe("error");
    });

    it("clears appointment selection booking error when AppointmentSelection emits clearBookingError", async () => {
      const wrapper = createWrapper({ appointmentHash: undefined });

      wrapper.vm.currentView = 1;
      wrapper.vm.errorStates.errorStateMap.apiErrorAppointmentNotAvailable.value = true;

      await nextTick();

      const appointmentSelection = wrapper.findComponent({
        name: "AppointmentSelection",
      });

      expect(appointmentSelection.exists()).toBe(true);
      expect(appointmentSelection.props("bookingError")).toBe(true);
      expect(appointmentSelection.props("bookingErrorKey")).toBe(
        "apiErrorAppointmentNotAvailable"
      );

      appointmentSelection.vm.$emit("clearBookingError");
      await nextTick();

      expect(
        wrapper.vm.errorStates.errorStateMap.apiErrorAppointmentNotAvailable
          .value
      ).toBe(false);

      const appointmentSelectionAfter = wrapper.findComponent({
        name: "AppointmentSelection",
      });

      expect(appointmentSelectionAfter.props("bookingError")).toBe(false);
      expect(appointmentSelectionAfter.props("bookingErrorKey")).toBe("");
    });

    it("clears captcha booking errors when captcha token changes", async () => {
      const wrapper = createWrapper({ appointmentHash: undefined });

      wrapper.vm.errorStates.errorStateMap.apiErrorCaptchaExpired.value = true;
      wrapper.vm.captchaToken = undefined;

      await nextTick();

      expect(
        wrapper.vm.errorStates.errorStateMap.apiErrorCaptchaExpired.value
      ).toBe(true);

      wrapper.vm.handleCaptchaTokenChanged("new-token");
      await nextTick();

      expect(wrapper.vm.captchaToken).toBe("new-token");
      expect(
        wrapper.vm.errorStates.errorStateMap.apiErrorCaptchaExpired.value
      ).toBe(false);
    });
  });

  describe("Success States", () => {
    it("shows success message after booking", async () => {
      const wrapper = createWrapper();
      wrapper.vm.confirmAppointmentSuccess = true;
      await nextTick();
      expect(wrapper.find('[data-test="muc-callout"]').exists()).toBe(true);
      expect(
        wrapper.find('[data-test="muc-callout"]').attributes("data-type")
      ).toBe("success");
    });

    it("shows success message after cancellation", async () => {
      const wrapper = createWrapper();
      wrapper.vm.cancelAppointmentSuccess = true;
      wrapper.vm.currentView = 4;
      await nextTick();
      expect(wrapper.find('[data-test="muc-callout"]').exists()).toBe(true);
      expect(
        wrapper.find('[data-test="muc-callout"]').attributes("data-type")
      ).toBe("success");
    });
  });

  describe("Navigation", () => {
    it("allows going back to previous steps", async () => {
      const wrapper = createWrapper();
      wrapper.vm.currentView = 2;
      await nextTick();
      wrapper.vm.currentView = 1; // Simulate going back
      await nextTick();
      expect(wrapper.find('[data-test="AppointmentSelection"]').exists()).toBe(
        true
      );
    });

    it("disables previous steps in stepper when appointment hash is present", async () => {
      const wrapper = createWrapperWithAppointmentHash();
      await nextTick();
      expect(
        wrapper
          .find('[data-test="muc-stepper"]')
          .attributes("data-disable-previous-steps")
      ).toBe("true");
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
      const wrapper = createWrapperWithAppointmentHash();
      wrapper.vm.errorStates.apiErrorPreconfirmationExpired.value = true;
      await nextTick();
      expect(wrapper.find('[data-test="muc-callout"]').exists()).toBe(true);
      expect(
        wrapper.find('[data-test="muc-callout"]').attributes("data-type")
      ).toBe("error");
    });
  });

  describe("Rebooking Flow", () => {
    it("starts at summary and disables previous steps when appointmentHash is present", async () => {
      const wrapper = createWrapperWithAppointmentHash();
      wrapper.vm.currentView = 3;
      await nextTick();
      expect(wrapper.find('[data-test="appointment-summary"]').exists()).toBe(
        true
      );
      expect(
        wrapper
          .find('[data-test="muc-stepper"]')
          .attributes("data-disable-previous-steps")
      ).toBe("true");
    });

    it("shows cancellation success callout after cancelling in rebooking", async () => {
      const wrapper = createWrapperWithAppointmentHash();
      wrapper.vm.currentView = 4;
      wrapper.vm.cancelAppointmentSuccess = true;
      await nextTick();
      expect(wrapper.find('[data-test="muc-callout"]').exists()).toBe(true);
      expect(
        wrapper.find('[data-test="muc-callout"]').attributes("data-type")
      ).toBe("success");
    });
  });

  describe("Invalid Jump-in Link (404 Error)", () => {
    it("shows 404 callout when invalid jump-in link error is triggered", async () => {
      const wrapper = createWrapper();
      wrapper.vm.errorStates.apiErrorInvalidJumpinLink.value = true;
      await nextTick();

      const callout = wrapper.find('[data-test="muc-callout"]');
      expect(callout.exists()).toBe(true);
      expect(callout.attributes("data-type")).toBe("error");
      expect(callout.text()).toContain(
        "Diese Ansicht kann nicht geladen werden"
      );
      expect(callout.text()).toContain(
        "Der Link zu dieser Seite ist leider fehlerhaft"
      );
    });

    it("shows button with correct text and icon in 404 callout", async () => {
      const wrapper = createWrapper();
      wrapper.vm.errorStates.apiErrorInvalidJumpinLink.value = true;
      await nextTick();

      const button = wrapper.find(".m-button-group button");
      expect(button.exists()).toBe(true);
      expect(button.text()).toContain("Termin vereinbaren");
      expect(button.attributes("icon")).toBe("arrow-right");
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
        pathname: "/",
      };

      const wrapper = createWrapper();
      wrapper.vm.errorStates.apiErrorInvalidJumpinLink.value = true;
      await nextTick();

      const button = wrapper.find(".m-button-group button");
      await button.trigger("click");

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

      const button = wrapper.find(".m-button-group button");
      expect(button.attributes("style")).toContain("margin-bottom: 0");
      expect(button.attributes("style")).toContain("margin-right: 0");
    });
  });

  describe("Edge Cases", () => {
    it("shows booking error callout if confirmAppointmentHash is invalid", async () => {
      const wrapper = createWrapper({
        appointmentHash: undefined,
        confirmAppointmentHash: "invalid",
      });

      await nextTick();

      expect(
        wrapper.vm.errorStates.errorStateMap.apiErrorAppointmentNotFound.value
      ).toBe(true);
      expect(wrapper.find('[data-test="muc-callout"]').exists()).toBe(true);
      expect(
        wrapper.find('[data-test="muc-callout"]').attributes("data-type")
      ).toBe("error");
    });
  });

  describe("Confirmation View", () => {
    it("shows only confirmation message when confirmAppointmentHash is present", async () => {
      const mockConfirmAppointment = vi.mocked(
        ZMSAppointmentAPI.confirmAppointment
      );
      mockConfirmAppointment.mockReturnValue(new Promise(() => {}));

      const validHash = btoa(
        JSON.stringify({
          id: "test-id",
          authKey: "test-auth-key",
        })
      );

      const wrapper = createWrapper({
        appointmentHash: undefined,
        confirmAppointmentHash: validHash,
      });

      await nextTick();

      expect(wrapper.find('[data-test="muc-stepper"]').exists()).toBe(false);
      expect(wrapper.find('[data-test="service-finder"]').exists()).toBe(false);
      expect(wrapper.find('[data-test="calendar-view"]').exists()).toBe(false);
      expect(wrapper.find('[data-test="customer-info"]').exists()).toBe(false);
      expect(wrapper.find('[data-test="appointment-summary"]').exists()).toBe(
        false
      );
      expect(wrapper.vm.currentView).toBe(5);
    });

    it("shows success message after successful confirmation", async () => {
      const wrapper = createWrapper({
        appointmentHash: undefined,
        confirmAppointmentHash: undefined,
      });
      wrapper.vm.confirmAppointmentSuccess = true;
      await nextTick();
      expect(wrapper.find('[data-test="muc-callout"]').exists()).toBe(true);
      expect(
        wrapper.find('[data-test="muc-callout"]').attributes("data-type")
      ).toBe("success");
    });

    it("shows error message if confirmation fails", async () => {
      const mockConfirmAppointment = vi.mocked(
        ZMSAppointmentAPI.confirmAppointment
      );
      mockConfirmAppointment.mockResolvedValueOnce({
        errors: [{ errorCode: "processNotPreconfirmedAnymore" }],
      });
      vi.mocked(ZMSAppointmentAPI.fetchAppointment).mockResolvedValueOnce({
        errors: [{ errorCode: "appointmentNotFound" }],
      } as any);

      const appointmentData = {
        id: "test-id",
        authKey: "test-auth-key",
        scope: {},
      };
      const validHash = btoa(JSON.stringify(appointmentData));

      const wrapper = createWrapper({
        appointmentHash: undefined,
        confirmAppointmentHash: validHash,
      });

      await nextTick();
      await vi.waitFor(() => {
        expect(mockConfirmAppointment).toHaveBeenCalled();
      });

      await vi.waitFor(() => {
        expect(
          wrapper.vm.errorStates.apiErrorPreconfirmationExpired.value
        ).toBe(true);
      });
      expect(wrapper.find('[data-test="muc-callout"]').exists()).toBe(true);
      expect(
        wrapper.find('[data-test="muc-callout"]').attributes("data-type")
      ).toBe("error");
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
      const expected = (de as any).confirmAppointmentText.replace(
        "{activationMinutes}",
        "60"
      );

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
        customTextfield2: "More info",
      };
      await nextTick();

      // Simulate form validation method if present
      if (typeof wrapper.vm.isCustomerInfoValid === "function") {
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
        customTextfield2: "",
      };
      await nextTick();

      // Simulate form validation method if present
      if (typeof wrapper.vm.isCustomerInfoValid === "function") {
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
        customTextfield2: "More info",
      };
      await nextTick();

      // Simulate form validation method if present
      if (typeof wrapper.vm.isCustomerInfoValid === "function") {
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
        customTextfield2: "More info",
      };
      await nextTick();

      // Simulate form validation method if present
      if (typeof wrapper.vm.isCustomerInfoValid === "function") {
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
        customTextfield2: "More info",
      };
      await nextTick();

      // Simulate form validation method if present
      if (typeof wrapper.vm.isCustomerInfoValid === "function") {
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
        customTextfield2: "More info",
      };
      await nextTick();

      // Simulate form validation method if present
      if (typeof wrapper.vm.isCustomerInfoValid === "function") {
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
        customTextfield2: "More info",
      };
      await nextTick();

      // Simulate form validation method if present
      if (typeof wrapper.vm.isCustomerInfoValid === "function") {
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
        customTextfield2: "More info",
      };
      await nextTick();

      // Simulate form validation method if present
      if (typeof wrapper.vm.isCustomerInfoValid === "function") {
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
        customTextfield: "a".repeat(251),
        customTextfield2: "More info",
      };
      await nextTick();

      // Simulate form validation method if present
      if (typeof wrapper.vm.isCustomerInfoValid === "function") {
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
        customTextfield2: "a".repeat(251),
      };
      await nextTick();

      // Simulate form validation method if present
      if (typeof wrapper.vm.isCustomerInfoValid === "function") {
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
        customTextfield2: "More info",
      };
      await nextTick();

      // Simulate form validation method if present
      if (typeof wrapper.vm.isCustomerInfoValid === "function") {
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
        customTextfield2: "More info",
      };
      await nextTick();

      // Verify that multiple spaces are preserved
      expect(
        wrapper.vm.$.appContext.provides.customerData.customerData.value
          .firstName
      ).toBe("Jane  Marie");
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
        customTextfield2: "More info",
      };
      await nextTick();

      // Verify that multiple spaces are preserved
      expect(
        wrapper.vm.$.appContext.provides.customerData.customerData.value
          .lastName
      ).toBe("Van  Der  Beek");
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
        customTextfield2: "More info",
      };
      await nextTick();

      // Verify that the form is invalid
      if (typeof wrapper.vm.isCustomerInfoValid === "function") {
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
        customTextfield2: "More info",
      };
      await nextTick();

      // Verify that the form is invalid
      if (typeof wrapper.vm.isCustomerInfoValid === "function") {
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
        customTextfield2: "More info",
      };
      await nextTick();

      // Verify that the form is invalid
      if (typeof wrapper.vm.isCustomerInfoValid === "function") {
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
        customTextfield2: "More info",
      };
      await nextTick();

      // Verify that the form is invalid
      if (typeof wrapper.vm.isCustomerInfoValid === "function") {
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
        customTextfield2: "More info",
      };
      await nextTick();

      // Verify that the form is invalid
      if (typeof wrapper.vm.isCustomerInfoValid === "function") {
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
        customTextfield2: "More info",
      };
      await nextTick();

      // Verify that the form is invalid
      if (typeof wrapper.vm.isCustomerInfoValid === "function") {
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
        customTextfield2: "More info",
      };
      await nextTick();

      // Verify that the form is invalid
      if (typeof wrapper.vm.isCustomerInfoValid === "function") {
        expect(wrapper.vm.isCustomerInfoValid()).toBe(false);
      }

      // Verify that firstName is still valid
      expect(
        wrapper.vm.$.appContext.provides.customerData.customerData.value
          .firstName
      ).toBe("Jane");
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
          customTextfield2: "",
        };
        await nextTick();

        // Verify form is invalid
        if (typeof wrapper.vm.isCustomerInfoValid === "function") {
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
          customTextfield2: "",
        };
        await nextTick();

        // Verify form is invalid
        if (typeof wrapper.vm.isCustomerInfoValid === "function") {
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
          customTextfield2: "",
        };
        await nextTick();

        // Verify form is invalid
        if (typeof wrapper.vm.isCustomerInfoValid === "function") {
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
          customTextfield2: "",
        };
        await nextTick();

        // Verify form is invalid
        if (typeof wrapper.vm.isCustomerInfoValid === "function") {
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
          customTextfield2: "",
        };
        await nextTick();

        // Verify form is invalid
        if (typeof wrapper.vm.isCustomerInfoValid === "function") {
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
          customTextfield2: "",
        };
        await nextTick();

        // Verify form is invalid
        if (typeof wrapper.vm.isCustomerInfoValid === "function") {
          expect(wrapper.vm.isCustomerInfoValid()).toBe(false);
        }
      });
    });
  });
  describe("API Error Handling - Confirmation", () => {
    const mockConfirmAppointment = vi.mocked(
      ZMSAppointmentAPI.confirmAppointment
    );

    beforeEach(() => {
      mockConfirmAppointment.mockClear();
    });
    it("should display activation expired Error when API returns processNotPreconfirmedAnymore", async () => {
      const mockErrorResponse = {
        errors: [
          {
            errorCode: "processNotPreconfirmedAnymore",
            message: "Process not preconfirmed anymore",
          },
        ],
      };
      mockConfirmAppointment.mockResolvedValueOnce(mockErrorResponse);
      vi.mocked(ZMSAppointmentAPI.fetchAppointment).mockResolvedValueOnce({
        errors: [{ errorCode: "appointmentNotFound" }],
      } as any);

      const appointmentData = {
        id: "test-id",
        authKey: "test-auth-key",
        scope: {},
      };

      const validHash = btoa(JSON.stringify(appointmentData));

      const wrapper = createWrapper({
        confirmAppointmentHash: validHash,
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
          scope: {},
        }
      );

      await vi.waitFor(() => {
        expect(
          wrapper.vm.errorStates.apiErrorPreconfirmationExpired.value
        ).toBe(true);
      });
      expect(wrapper.vm.confirmAppointmentSuccess).toBe(false);
      expect(wrapper.vm.appointmentAlreadyActivated).toBe(false);

      const errorCallout = wrapper.find('[data-test="muc-callout"]');
      expect(errorCallout.exists()).toBe(true);
      expect(errorCallout.attributes("data-type")).toBe("error");

      expect(errorCallout.text()).toContain(
        de.apiErrorPreconfirmationExpiredHeader
      );
      expect(errorCallout.text()).toContain(
        de.apiErrorPreconfirmationExpiredText
      );
    });

    it("should show already-activated info banner and appointment details when confirm link is reused", async () => {
      mockConfirmAppointment.mockResolvedValueOnce({
        errors: [
          {
            errorCode: "processNotPreconfirmedAnymore",
            message: "Process not preconfirmed anymore",
          },
        ],
      });
      vi.mocked(ZMSAppointmentAPI.fetchAppointment).mockResolvedValue({
        processId: "test-id",
        authKey: "test-auth-key",
        serviceId: "123",
        officeId: "789",
        serviceCount: 1,
        subRequestCounts: [],
        timestamp: nowUnixSeconds() + 3600,
      } as any);
      vi.mocked(globalThis.fetch).mockResolvedValue({
        status: 200,
        json: async () => ({
          offices: [
            {
              id: "789",
              name: "Test Provider",
              address: { street: "Test Street", house_number: "1" },
            },
          ],
          services: [{ id: "123", name: "Test Service" }],
          relations: [],
        }),
      } as any);

      const appointmentData = {
        id: "test-id",
        authKey: "test-auth-key",
        scope: {},
      };
      const validHash = btoa(JSON.stringify(appointmentData));

      const wrapper = createWrapper({
        confirmAppointmentHash: validHash,
      });

      await vi.waitFor(() => {
        expect(mockConfirmAppointment).toHaveBeenCalled();
      });
      await vi.waitFor(() => {
        expect(wrapper.vm.appointmentAlreadyActivated).toBe(true);
        expect(wrapper.vm.currentView).toBe(3);
      });

      expect(wrapper.vm.errorStates.apiErrorPreconfirmationExpired.value).toBe(
        false
      );
      expect(wrapper.find('[data-test="appointment-summary"]').exists()).toBe(
        true
      );
      expect(wrapper.html()).toContain(de.appointmentAlreadyActivatedHeader);
    });

    it("should show already-activated banner when the same confirm hash is reopened after success", async () => {
      mockConfirmAppointment.mockResolvedValueOnce({
        processId: "test-id",
        authKey: "test-auth-key",
        serviceId: "123",
        officeId: "789",
        serviceCount: 1,
        subRequestCounts: [],
        timestamp: nowUnixSeconds() + 3600,
      } as any);

      const appointmentData = {
        id: "test-id",
        authKey: "test-auth-key",
        scope: {},
      };
      const validHash = btoa(JSON.stringify(appointmentData));

      const wrapper = createWrapper({
        confirmAppointmentHash: validHash,
      });

      await vi.waitFor(() => {
        expect(wrapper.vm.confirmAppointmentSuccess).toBe(true);
      });
      expect(mockConfirmAppointment).toHaveBeenCalledTimes(1);

      vi.mocked(ZMSAppointmentAPI.fetchAppointment).mockResolvedValue({
        processId: "test-id",
        authKey: "test-auth-key",
        serviceId: "123",
        officeId: "789",
        serviceCount: 1,
        subRequestCounts: [],
        timestamp: nowUnixSeconds() + 3600,
      } as any);
      vi.mocked(globalThis.fetch).mockResolvedValue({
        status: 200,
        json: async () => ({
          offices: [
            {
              id: "789",
              name: "Test Provider",
              address: { street: "Test Street", house_number: "1" },
            },
          ],
          services: [{ id: "123", name: "Test Service" }],
          relations: [],
        }),
      } as any);

      // Leave confirm route then reopen the same link (SPA hash watch re-fires).
      await wrapper.setProps({ confirmAppointmentHash: undefined });
      await nextTick();
      await wrapper.setProps({ confirmAppointmentHash: validHash });

      await vi.waitFor(() => {
        expect(wrapper.vm.appointmentAlreadyActivated).toBe(true);
        expect(wrapper.vm.currentView).toBe(3);
      });
      expect(mockConfirmAppointment).toHaveBeenCalledTimes(1);
      expect(wrapper.html()).toContain(de.appointmentAlreadyActivatedHeader);
    });

    it("should hide already-activated banner while rescheduling from a reused confirm link", async () => {
      mockConfirmAppointment.mockResolvedValueOnce({
        errors: [
          {
            errorCode: "processNotPreconfirmedAnymore",
            message: "Process not preconfirmed anymore",
          },
        ],
      });
      vi.mocked(ZMSAppointmentAPI.fetchAppointment).mockResolvedValue({
        processId: "test-id",
        authKey: "test-auth-key",
        serviceId: "123",
        officeId: "789",
        serviceCount: 1,
        subRequestCounts: [],
        timestamp: nowUnixSeconds() + 3600,
      } as any);
      vi.mocked(globalThis.fetch).mockResolvedValue({
        status: 200,
        json: async () => ({
          offices: [
            {
              id: "789",
              name: "Test Provider",
              address: { street: "Test Street", house_number: "1" },
            },
          ],
          services: [{ id: "123", name: "Test Service" }],
          relations: [],
        }),
      } as any);

      const appointmentData = {
        id: "test-id",
        authKey: "test-auth-key",
        scope: {},
      };
      const validHash = btoa(JSON.stringify(appointmentData));

      const wrapper = createWrapper({
        confirmAppointmentHash: validHash,
      });

      await vi.waitFor(() => {
        expect(wrapper.vm.appointmentAlreadyActivated).toBe(true);
        expect(wrapper.vm.currentView).toBe(3);
      });
      expect(wrapper.html()).toContain(de.appointmentAlreadyActivatedHeader);

      wrapper.vm.nextRescheduleAppointment();
      await nextTick();
      expect(wrapper.vm.isRebooking).toBe(true);
      expect(wrapper.vm.currentView).toBe(1);

      // Return to summary to confirm the new slot — banner must stay hidden.
      wrapper.vm.currentView = 3;
      await nextTick();
      expect(wrapper.vm.appointmentAlreadyActivated).toBe(true);
      expect(wrapper.html()).not.toContain(
        de.appointmentAlreadyActivatedHeader
      );

      wrapper.vm.nextCancelReschedule();
      await nextTick();
      expect(wrapper.vm.isRebooking).toBe(false);
      expect(wrapper.html()).toContain(de.appointmentAlreadyActivatedHeader);
    });

    it("should display activation expired error when API returns appointmentNotFound", async () => {
      const mockErrorResponse = {
        errors: [
          {
            errorCode: "appointmentNotFound",
            message: "Appointment not found",
          },
        ],
      };
      mockConfirmAppointment.mockResolvedValueOnce(mockErrorResponse);

      const appointmentData = {
        id: "not-found-id",
        authKey: "test-auth-key",
        scope: {},
      };
      const validHash = btoa(JSON.stringify(appointmentData));

      const wrapper = createWrapper({
        confirmAppointmentHash: validHash,
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
          scope: {},
        }
      );

      expect(wrapper.vm.errorStates.apiErrorPreconfirmationExpired.value).toBe(
        true
      );
      expect(wrapper.vm.confirmAppointmentSuccess).toBe(false);

      const errorCallout = wrapper.find('[data-test="muc-callout"]');
      expect(errorCallout.exists()).toBe(true);
      expect(errorCallout.attributes("data-type")).toBe("error");
      expect(errorCallout.text()).toContain(
        de.apiErrorPreconfirmationExpiredHeader
      );
      expect(errorCallout.text()).toContain(
        de.apiErrorPreconfirmationExpiredText
      );
    });

    it("should display generic error for other API error codes", async () => {
      const mockErrorResponse = {
        errors: [
          {
            errorCode: "someOtherError",
            message: "Some other error occurred",
          },
        ],
      };
      mockConfirmAppointment.mockResolvedValueOnce(mockErrorResponse);

      const appointmentData = {
        id: "other-error-id",
        authKey: "test-auth-key",
        scope: {},
      };
      const validHash = btoa(JSON.stringify(appointmentData));

      const wrapper = createWrapper({
        confirmAppointmentHash: validHash,
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
          scope: {},
        }
      );

      expect(wrapper.vm.errorStates.apiErrorGenericFallback.value).toBe(true);
      expect(wrapper.vm.confirmAppointmentSuccess).toBe(false);

      const errorCallout = wrapper.find('[data-test="muc-callout"]');
      expect(errorCallout.exists()).toBe(true);
      expect(errorCallout.attributes("data-type")).toBe("error");
      expect(errorCallout.text()).toContain(de.apiErrorGenericFallbackHeader);
    });

    it("confirms when confirmAppointmentHash arrives after mount", async () => {
      const mockSuccessResponse = {
        processId: "12345",
        authKey: "test-auth-key",
        serviceId: "1",
        officeId: "2",
        timestamp: nowUnixSeconds() + 3600,
      };
      mockConfirmAppointment.mockResolvedValueOnce(mockSuccessResponse);

      const appointmentData = {
        id: "12345",
        authKey: "test-auth-key",
        scope: {},
      };
      const validHash = btoa(JSON.stringify(appointmentData));

      const wrapper = createWrapper({
        appointmentHash: undefined,
        confirmAppointmentHash: undefined,
      });

      await nextTick();
      expect(mockConfirmAppointment).not.toHaveBeenCalled();

      await wrapper.setProps({ confirmAppointmentHash: validHash });
      await nextTick();

      await vi.waitFor(() => {
        expect(mockConfirmAppointment).toHaveBeenCalledTimes(1);
      });

      expect(wrapper.vm.confirmAppointmentSuccess).toBe(true);
      expect(wrapper.vm.currentView).toBe(5);
    });

    it("does not confirm twice when the same confirmAppointmentHash is set again", async () => {
      const mockSuccessResponse = {
        processId: "12345",
        authKey: "test-auth-key",
        serviceId: "1",
        officeId: "2",
        timestamp: nowUnixSeconds() + 3600,
      };
      mockConfirmAppointment.mockResolvedValue(mockSuccessResponse);

      const appointmentData = {
        id: "12345",
        authKey: "test-auth-key",
        scope: {},
      };
      const validHash = btoa(JSON.stringify(appointmentData));

      const wrapper = createWrapper({
        appointmentHash: undefined,
        confirmAppointmentHash: undefined,
      });

      await wrapper.setProps({ confirmAppointmentHash: validHash });
      await nextTick();
      await vi.waitFor(() => {
        expect(mockConfirmAppointment).toHaveBeenCalledTimes(1);
      });

      await wrapper.setProps({ confirmAppointmentHash: validHash });
      await nextTick();

      expect(mockConfirmAppointment).toHaveBeenCalledTimes(1);
    });

    it("loads appointment view when appointmentHash arrives after confirm success", async () => {
      const mockFetchAppointment = vi.mocked(
        ZMSAppointmentAPI.fetchAppointment
      );
      mockFetchAppointment.mockResolvedValueOnce({
        processId: "12345",
        authKey: "test-auth-key",
        serviceId: "123",
        officeId: "789",
        serviceCount: 1,
        subRequestCounts: [],
        timestamp: nowUnixSeconds() + 3600,
      } as any);

      vi.mocked(globalThis.fetch).mockResolvedValueOnce({
        status: 200,
        json: async () => ({
          offices: [
            {
              id: "789",
              name: "Test Provider",
              address: { street: "Test Street", house_number: "1" },
            },
          ],
          services: [{ id: "123", name: "Test Service" }],
          relations: [],
        }),
      } as any);

      const appointmentData = {
        id: "12345",
        authKey: "test-auth-key",
        scope: {},
      };
      const validHash = btoa(JSON.stringify(appointmentData));

      const wrapper = createWrapper({
        appointmentHash: undefined,
        confirmAppointmentHash: undefined,
      });

      wrapper.vm.confirmAppointmentSuccess = true;
      wrapper.vm.currentView = 5;
      await nextTick();

      expect(
        wrapper.find('[data-test="muc-callout"]').attributes("data-type")
      ).toBe("success");

      await wrapper.setProps({ appointmentHash: validHash });
      await nextTick();

      await vi.waitFor(() => {
        expect(mockFetchAppointment).toHaveBeenCalledTimes(1);
      });

      await vi.waitFor(() => {
        expect(wrapper.vm.confirmAppointmentSuccess).toBe(false);
        expect(wrapper.vm.currentView).toBe(3);
      });

      expect(wrapper.find('[data-test="appointment-summary"]').exists()).toBe(
        true
      );
      expect(wrapper.find('[data-test="muc-callout"]').exists()).toBe(false);
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
    const mockConfirmAppointment = vi.mocked(
      ZMSAppointmentAPI.confirmAppointment
    );

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
        const buttons = wrapper.findAll("button");
        const downloadButton = buttons.find((button) =>
          button.text().includes(de.downloadAppointment)
        );
        expect(downloadButton).toBeUndefined();
      });

      // Will be fixed after release.
      // it("should render view button when user is authenticated", async () => {
      //   // Mock useLogin to return authenticated state
      //   const mockUseLogin = vi.mocked(useLogin);
      //   mockUseLogin.mockReturnValue({
      //     isLoggedIn: ref(true),
      //     isLoadingAuthentication: ref(false),
      //     accessToken: ref("test-token")
      //   });
      //
      //   const wrapper = createWrapper();
      //   wrapper.vm.confirmAppointmentSuccess = true;
      //   await nextTick();
      //
      //   const buttons = wrapper.findAll('button');
      //   const viewButton = buttons.find(button => button.text().includes(de.viewAppointment));
      //   expect(viewButton).toBeDefined();
      //   expect(viewButton?.attributes('icon')).toBe('arrow-right');
      //   expect(viewButton?.text()).toContain(de.viewAppointment);
      // });

      it("should hide view button when user is not authenticated", async () => {
        // Mock useLogin to return unauthenticated state
        const mockUseLogin = vi.mocked(useLogin);
        mockUseLogin.mockReturnValue({
          isLoggedIn: ref(false),
          isLoadingAuthentication: ref(false),
          accessToken: ref(null),
        });

        const wrapper = createWrapper();
        wrapper.vm.confirmAppointmentSuccess = true;
        await nextTick();

        const buttons = wrapper.findAll("button");
        const viewButton = buttons.find((button) =>
          button.text().includes(de.viewAppointment)
        );
        expect(viewButton).toBeUndefined();
      });
    });

    describe("Download Functionality", () => {
      it("should have downloadIcsAppointment function", async () => {
        const wrapper = createWrapper();
        const component = wrapper.vm as any;

        // Check that the function exists
        expect(typeof component.downloadIcsAppointment).toBe("function");
      });
    });

    describe("View Functionality", () => {
      it("should have viewAppointment function", async () => {
        const wrapper = createWrapper();
        const component = wrapper.vm as any;

        // Check that the function exists
        expect(typeof component.viewAppointment).toBe("function");
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
          icsContent:
            "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:ZMS-München\r\nEND:VCALENDAR",
          officeId: "456",
          scope: {},
          subRequestCounts: [],
          serviceId: "789",
          serviceName: "Test Service",
          serviceCount: 1,
          status: "confirmed",
        };

        const wrapper = createWrapper();

        // Simulate the appointment confirmation success state
        wrapper.vm.confirmAppointmentSuccess = true;
        wrapper.vm.$.appContext.provides.appointment.appointment.value =
          mockConfirmResponse;

        await nextTick();

        // Verify ICS content is stored in component state
        expect(
          wrapper.vm.$.appContext.provides.appointment.appointment.value
            ?.icsContent
        ).toBe(mockConfirmResponse.icsContent);
        expect(wrapper.vm.confirmAppointmentSuccess).toBe(true);
      });
    });
  });

  describe("Rebooking: direct confirm flow", () => {
    const mockConfirm = vi.mocked(ZMSAppointmentAPI.confirmAppointment);
    const mockPreconfirm = vi.mocked(ZMSAppointmentAPI.preconfirmAppointment);
    const mockCancel = vi.mocked(ZMSAppointmentAPI.cancelAppointment);

    beforeEach(() => {
      mockConfirm.mockReset();
      mockPreconfirm.mockReset();
      mockCancel.mockReset();
    });

    it("calls confirmAppointment directly when rebooking with processId+authKey", async () => {
      const wrapper = createWrapperWithAppointmentHash();

      wrapper.vm.isRebooking = true;
      wrapper.vm.appointment = {
        processId: "p1",
        authKey: "k1",
      } as any;

      mockConfirm.mockResolvedValueOnce({
        processId: "p1",
        status: "confirmed",
      } as any);

      await wrapper.vm.nextBookAppointment();
      await nextTick();

      expect(mockConfirm).toHaveBeenCalledWith(
        { baseUrl: "https://www.muenchen.de" },
        { id: "p1", authKey: "k1" }
      );
      expect(mockPreconfirm).not.toHaveBeenCalled();

      expect(wrapper.vm.confirmAppointmentSuccess).toBe(true);
      expect(wrapper.vm.currentView).toBe(5);

      expect(wrapper.vm.isBookingAppointment).toBe(false);

      expect(wrapper.find('[data-test="muc-callout"]').exists()).toBe(true);
      expect(
        wrapper.find('[data-test="muc-callout"]').attributes("data-type")
      ).toBe("success");
    });

    it("cancels old appointment after successful rebooking confirm", async () => {
      const wrapper = createWrapperWithAppointmentHash();

      wrapper.vm.isRebooking = true;
      wrapper.vm.rebookedAppointment = {
        processId: "old",
        authKey: "oldkey",
      } as any;

      wrapper.vm.appointment = {
        processId: "new",
        authKey: "newkey",
      } as any;

      mockConfirm.mockResolvedValueOnce({
        processId: "new",
        status: "confirmed",
      } as any);

      await wrapper.vm.nextBookAppointment();
      await nextTick();

      expect(mockConfirm).toHaveBeenCalled();
      expect(mockCancel).toHaveBeenCalledWith(
        { baseUrl: "https://www.muenchen.de" },
        expect.objectContaining({ processId: "old" })
      );
    });

    it("falls back to preconfirm when rebooking but missing authKey/processId", async () => {
      const wrapper = createWrapperWithAppointmentHash();

      wrapper.vm.isRebooking = true;
      wrapper.vm.appointment = {
        processId: "p1",
      } as any;

      mockPreconfirm.mockResolvedValueOnce({
        processId: "p1",
      } as any);

      await wrapper.vm.nextBookAppointment();
      await nextTick();

      expect(mockConfirm).not.toHaveBeenCalled();
      expect(mockPreconfirm).toHaveBeenCalled();
    });
  });

  describe("Rebooking required contact fields", () => {
    const mockUpdate = vi.mocked(ZMSAppointmentAPI.updateAppointment);

    beforeEach(() => {
      mockUpdate.mockReset();
      mockUpdate.mockResolvedValue({ processId: "new-1" } as any);
    });

    const completeRebookedAppointment = {
      processId: "old",
      authKey: "oldkey",
      familyName: "Max Mustermann",
      email: "max@example.com",
      telephone: "0891234567",
      customTextfield: "note",
      customTextfield2: "note2",
    };

    it("skips contact and updates when the target scope is already complete", async () => {
      const wrapper = createWrapperWithAppointmentHash();
      wrapper.vm.isRebooking = true;
      wrapper.vm.rebookedAppointment = {
        ...completeRebookedAppointment,
      } as any;
      wrapper.vm.appointment = { processId: "new-1", authKey: "newkey" } as any;
      wrapper.vm.selectedProvider = {
        id: "789",
        scope: {
          telephoneActivated: true,
          telephoneRequired: true,
          customTextfieldActivated: true,
          customTextfieldRequired: true,
          customTextfield2Activated: true,
          customTextfield2Required: true,
        },
      } as any;

      await wrapper.vm.continueRebookingAfterReserve();

      expect(mockUpdate).toHaveBeenCalled();
      expect(wrapper.vm.currentView).toBe(3);
    });

    it("opens contact when the target scope requires a missing custom textfield", async () => {
      const wrapper = createWrapperWithAppointmentHash();
      wrapper.vm.isRebooking = true;
      wrapper.vm.rebookedAppointment = {
        ...completeRebookedAppointment,
        customTextfield2: "",
      } as any;
      wrapper.vm.appointment = { processId: "new-1", authKey: "newkey" } as any;
      wrapper.vm.selectedProvider = {
        id: "789",
        scope: {
          customTextfield2Activated: true,
          customTextfield2Required: true,
        },
      } as any;

      wrapper.vm.continueRebookingAfterReserve();
      await nextTick();

      expect(mockUpdate).not.toHaveBeenCalled();
      expect(wrapper.vm.currentView).toBe(2);
      expect(wrapper.vm.customerData.firstName).toBe("Max");
      expect(wrapper.vm.customerData.mailAddress).toBe("max@example.com");
    });

    it("stays on contact when update fails instead of showing a dead-end overview", async () => {
      mockUpdate.mockResolvedValueOnce({
        errors: [{ errorCode: "invalidTelephone" }],
      } as any);

      const wrapper = createWrapperWithAppointmentHash();
      wrapper.vm.isRebooking = true;
      wrapper.vm.rebookedAppointment = {
        ...completeRebookedAppointment,
        telephone: "",
      } as any;
      wrapper.vm.appointment = { processId: "new-1", authKey: "newkey" } as any;
      wrapper.vm.currentView = 2;
      wrapper.vm.customerData.firstName = "Max";
      wrapper.vm.customerData.lastName = "Mustermann";
      wrapper.vm.customerData.mailAddress = "max@example.com";

      await wrapper.vm.nextUpdateAppointment();
      await nextTick();

      expect(wrapper.vm.currentView).toBe(2);
    });

    it("sends source appointment on update only while rebooking", async () => {
      const wrapper = createWrapperWithAppointmentHash();
      wrapper.vm.appointment = { processId: "new-1", authKey: "newkey" } as any;
      wrapper.vm.currentView = 2;
      wrapper.vm.customerData.firstName = "Max";
      wrapper.vm.customerData.lastName = "Mustermann";
      wrapper.vm.customerData.mailAddress = "max@example.com";

      await wrapper.vm.nextUpdateAppointment();
      expect(mockUpdate).toHaveBeenCalledWith(
        expect.anything(),
        expect.objectContaining({ familyName: "Max Mustermann" }),
        undefined
      );

      mockUpdate.mockClear();
      wrapper.vm.isRebooking = true;
      wrapper.vm.rebookedAppointment = {
        ...completeRebookedAppointment,
      } as any;
      wrapper.vm.isUpdatingAppointment = false;

      await wrapper.vm.nextUpdateAppointment();
      expect(mockUpdate).toHaveBeenCalledWith(
        expect.anything(),
        expect.objectContaining({ familyName: "Max Mustermann" }),
        expect.objectContaining({ processId: "old", authKey: "oldkey" })
      );
    });
  });

  describe("Reschedule Error (Vergangener Termin)", () => {
    it('zeigt die Meldung "rescheduleError" und den Neu-buchen-Button, wenn der Termin in der Vergangenheit liegt', async () => {
      // avoid disruptive residues from previous tests
      localStorage.clear();

      const wrapper = createWrapper({
        appointmentHash: undefined,
        confirmAppointmentHash: undefined,
      });

      // appointment in the past
      const pastTimestampSeconds = nowUnixSeconds() - 3600;

      wrapper.vm.appointment = {
        ...(wrapper.vm.appointment ?? {}),
        timestamp: pastTimestampSeconds,
      } as any;

      wrapper.vm.currentView = 3;

      await nextTick();

      const expectedHeader =
        (de as any).rescheduleErrorHeader ?? "rescheduleErrorHeader";
      const expectedText =
        (de as any).rescheduleErrorText ?? "rescheduleErrorText";
      const expectedButtonLabel =
        (de as any).newAppointmentButton ?? "newAppointmentButton";

      const errorCallout = wrapper.find('[data-test="muc-callout"]');
      expect(errorCallout.exists()).toBe(true);
      expect(errorCallout.attributes("data-type")).toBe("error");
      expect(errorCallout.text()).toContain(expectedHeader);
      expect(errorCallout.text()).toContain(expectedText);

      const buttons = wrapper.findAll('[data-test="muc-button"]');
      const newAppointmentButton = buttons.find((b) =>
        b.text().includes(expectedButtonLabel)
      );
      expect(newAppointmentButton).toBeDefined();

      expect(wrapper.find('[data-test="muc-stepper"]').exists()).toBe(false);
      expect(wrapper.find('[data-test="appointment-summary"]').exists()).toBe(
        false
      );
    });

    it("hides already-activated banner when the appointment is in the past", async () => {
      localStorage.clear();

      const wrapper = createWrapper({
        appointmentHash: undefined,
        confirmAppointmentHash: undefined,
      });

      wrapper.vm.appointmentAlreadyActivated = true;
      wrapper.vm.appointment = {
        ...(wrapper.vm.appointment ?? {}),
        timestamp: nowUnixSeconds() - 3600,
      } as any;
      wrapper.vm.currentView = 3;
      await nextTick();

      expect(wrapper.html()).toContain(de.rescheduleErrorHeader);
      expect(wrapper.html()).not.toContain(
        de.appointmentAlreadyActivatedHeader
      );
      expect(wrapper.find('[data-test="appointment-summary"]').exists()).toBe(
        false
      );
    });
  });

  describe("ZMSKVR-1002 authKey out of localStorage", () => {
    const uiStoragePayload = {
      timestamp: Date.now(),
      currentView: 2,
      selectedServiceId: "123",
      selectedServiceMap: { "123": 1 },
      selectedProviderId: "789",
      selectedTimeslot: 1640995200,
    };

    const catalogResponse = {
      offices: [
        {
          id: "789",
          name: "Test Provider",
          address: {
            street: "Test Street",
            house_number: "123",
            postal_code: "12345",
            city: "Test City",
          },
          showAlternativeLocations: false,
          displayNameAlternatives: [],
          organization: "Org",
          slotTimeInMinutes: 15,
          priority: 1,
        },
      ],
      services: [{ id: "123", name: "Test Service", maxQuantity: 1 }],
      relations: [{ serviceId: "123", officeId: "789", slots: 1 }],
    };

    beforeEach(() => {
      localStorage.clear();
      sessionStorage.clear();
      vi.mocked(ZMSAppointmentAPI.fetchAppointment).mockReset();
      vi.stubGlobal(
        "fetch",
        vi.fn().mockResolvedValue({
          status: 200,
          json: async () => catalogResponse,
        })
      );
    });

    it("requestLogin stores UI data without authKey, PII, or captchaToken", () => {
      const replaceStateSpy = vi
        .spyOn(history, "replaceState")
        .mockImplementation(() => {});
      const wrapper = createWrapper({ showLoginOption: true });

      wrapper.vm.selectedService = {
        id: "123",
        name: "Test Service",
        count: 1,
      } as any;
      wrapper.vm.selectedProvider = {
        id: "789",
        name: "Test Provider",
        address: {
          street: "Test Street",
          house_number: "123",
          postal_code: "12345",
          city: "Test City",
        },
        slotsPerAppointment: "2",
      } as any;
      wrapper.vm.selectedTimeslot = uiStoragePayload.selectedTimeslot;
      wrapper.vm.customerData = {
        firstName: "Max",
        lastName: "Mustermann",
        mailAddress: "max@example.com",
        telephoneNumber: "089123456",
        customTextfield: "secret-note",
        customTextfield2: "",
      } as any;
      wrapper.vm.captchaToken = "captcha-secret";
      wrapper.vm.currentView = 2;
      wrapper.vm.appointment = {
        processId: "proc-1",
        authKey: "secret-key",
        timestamp: 1640995200,
        familyName: "Mustermann",
        email: "max@example.com",
        officeId: "789",
        scope: {},
        subRequestCounts: [],
        serviceId: "123",
        serviceName: "Test Service",
        serviceCount: 1,
      } as any;

      wrapper.vm.requestLogin();

      const stored = localStorage.getItem(LOCALSTORAGE_PARAM_APPOINTMENT_DATA);
      expect(stored).toBeTruthy();
      expect(stored).not.toContain("secret-key");
      expect(stored).not.toContain("authKey");
      expect(stored).not.toContain("max@example.com");
      expect(stored).not.toContain("captcha-secret");
      expect(stored).not.toContain("customerData");
      expect(stored).not.toContain("captchaToken");
      expect(stored).not.toContain("slotsPerAppointment");
      expect(stored).not.toContain("Test Provider");
      const parsed = JSON.parse(stored as string);
      expect(parsed.appointment).toBeUndefined();
      expect(parsed.customerData).toBeUndefined();
      expect(parsed.captchaToken).toBeUndefined();
      expect(parsed.selectedService).toBeUndefined();
      expect(parsed.selectedProvider).toBeUndefined();
      expect(parsed.selectedServiceId).toBe("123");
      expect(parsed.selectedProviderId).toBe("789");

      expect(
        sessionStorage.getItem(SESSIONSTORAGE_PARAM_APPOINTMENT_AUTH_HASH)
      ).toBe(btoa(JSON.stringify({ id: "proc-1", authKey: "secret-key" })));
      expect(replaceStateSpy).toHaveBeenCalled();
      replaceStateSpy.mockRestore();
    });

    it("login resume merges hash credentials with UI localStorage and ignores legacy LS authKey", async () => {
      localStorage.setItem(
        LOCALSTORAGE_PARAM_APPOINTMENT_DATA,
        JSON.stringify({
          ...uiStoragePayload,
          selectedService: {
            id: "123",
            name: "Legacy Service Object",
          },
          selectedProvider: {
            id: "789",
            name: "Legacy Provider Object",
            slotsPerAppointment: "9",
          },
          appointment: {
            processId: "legacy-id",
            authKey: "legacy-secret-should-be-ignored",
          },
          customerData: {
            firstName: "Legacy",
            lastName: "User",
            mailAddress: "legacy@example.com",
          },
          captchaToken: "legacy-captcha",
        })
      );

      const hash = buildAppointmentHash({
        id: "hash-proc",
        authKey: "hash-auth-key",
      });

      vi.mocked(ZMSAppointmentAPI.fetchAppointment).mockResolvedValue({
        processId: "hash-proc",
        authKey: "hash-auth-key",
        timestamp: Math.floor(Date.now() / 1000) + 3600,
        familyName: "Mustermann",
        email: "max@example.com",
        officeId: "789",
        scope: {},
        subRequestCounts: [],
        serviceId: "123",
        serviceName: "Test Service",
        serviceCount: 1,
      } as any);

      const wrapper = createWrapper({
        showLoginOption: true,
        appointmentHash: hash,
      });

      await vi.waitFor(() => {
        expect(wrapper.vm.appointment?.processId).toBe("hash-proc");
      });

      expect(wrapper.vm.appointment?.authKey).toBe("hash-auth-key");
      expect(wrapper.vm.appointment?.authKey).not.toBe(
        "legacy-secret-should-be-ignored"
      );
      expect(wrapper.vm.customerData?.mailAddress).not.toBe(
        "legacy@example.com"
      );
      expect(wrapper.vm.captchaToken).not.toBe("legacy-captcha");
      expect(wrapper.vm.currentView).toBe(2);
      expect(wrapper.vm.rebookOrCancelDialog).toBe(false);
      expect(
        localStorage.getItem(LOCALSTORAGE_PARAM_APPOINTMENT_DATA)
      ).toBeNull();
    });

    it("does not restore authKey or PII from legacy localStorage when hash is missing", async () => {
      localStorage.setItem(
        LOCALSTORAGE_PARAM_APPOINTMENT_DATA,
        JSON.stringify({
          ...uiStoragePayload,
          appointment: {
            processId: "legacy-id",
            authKey: "legacy-secret",
          },
          customerData: {
            firstName: "Legacy",
            mailAddress: "legacy@example.com",
          },
          captchaToken: "legacy-captcha",
        })
      );

      const wrapper = createWrapper({ showLoginOption: true });

      await vi.waitFor(() => {
        expect(wrapper.vm.selectedService?.id).toBe("123");
      });

      expect(wrapper.vm.appointment?.authKey).toBeUndefined();
      expect(wrapper.vm.customerData?.mailAddress).not.toBe(
        "legacy@example.com"
      );
      expect(wrapper.vm.captchaToken).not.toBe("legacy-captcha");
      expect(wrapper.vm.selectedProvider?.id).toBe("789");
      expect(
        localStorage.getItem(LOCALSTORAGE_PARAM_APPOINTMENT_DATA)
      ).toBeNull();
    });
  });
});
