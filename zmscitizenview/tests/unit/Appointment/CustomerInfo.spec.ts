import { mount } from "@vue/test-utils";
import { describe, expect, it, beforeEach } from "vitest";
import { nextTick, ref } from "vue";

import CustomerInfo from "@/components/Appointment/CustomerInfo.vue";

describe("CustomerInfo", () => {
  let mockCustomerData;
  let mockSelectedProvider;
  let mockAppointmentProvider: any;
  let mockReservationStartMs: any;

  beforeEach(() => {
    mockCustomerData = ref({
      firstName: "",
      lastName: "",
      mailAddress: "",
      telephoneNumber: "",
      customTextfield: "",
      customTextfield2: "",
    });
    mockSelectedProvider = ref({
      scope: {
        telephoneActivated: false,
        telephoneRequired: false,
        customTextfieldActivated: false,
        customTextfieldRequired: false,
        customTextfield2Activated: false,
        customTextfield2Required: false,
      },
    });

    mockAppointmentProvider = {
      appointment: ref<{ scope?: { reservationDuration?: number } } | null>({
        scope: { reservationDuration: 15 },
      }),
    };
    mockReservationStartMs = ref<number | null>(Date.now());
  });

  const createWrapper = (props: Record<string, unknown> = {}) => {
    return mount(CustomerInfo, {
      props: {
        globalState: { isLoggedIn: false, accessToken: null },
        showLoginOption: false,
        t: (key: string) => key,
        ...props,
      },
      global: {
        provide: {
          customerData: { customerData: mockCustomerData },
          selectedTimeslot: { selectedProvider: mockSelectedProvider },
          loadingStates: {
            isReservingAppointment: ref(false),
            isUpdatingAppointment: ref(false),
            isBookingAppointment: ref(false),
            isCancelingAppointment: ref(false),
          },
          appointment: mockAppointmentProvider,
          reservationStartMs: mockReservationStartMs,
        },
        stubs: {
          'muc-input': {
            template: '<input :id="id" />',
            props: ['id'],
          },
          'muc-text-area': {
            template: '<textarea :id="id" />',
            props: ['id'],
          },
          "muc-button": {
            template:
              '<button class="muc-button" :disabled="disabled" @click="$emit(\'click\')"><slot /></button>',
            props: ["icon", "iconShownLeft", "variant", "disabled"],
          },
          "muc-callout": {
            template:
              '<div class="muc-callout"><slot name="header" /><slot name="content" /></div>',
            props: ["type"],
          },
          "muc-banner": {
            template: '<div class="muc-banner" :data-type="type"><slot /></div>',
            props: ["type", "variant"],
          },
        },
      },
    });
  };

  // helper function: finds the next-button (second Button)
  const findNextButton = (wrapper: any) => wrapper.findAll(".muc-button")[1];
  // helper function: finds the back-button (first Button)
  const findBackButton = (wrapper: any) => wrapper.findAll(".muc-button")[0];

  describe("Form Validation", () => {
    it("should not emit next when form is invalid", async () => {
      const wrapper = createWrapper();
      await nextTick();
      const nextButton = findNextButton(wrapper);
      await nextButton.trigger("click");
      await nextTick();
      expect(wrapper.emitted("next")).toBeUndefined();
    });

    it("should show error message for invalid email format", async () => {
      mockCustomerData.value.firstName = "Max";
      mockCustomerData.value.lastName = "Mustermann";
      mockCustomerData.value.mailAddress = "invalid-email";
      const wrapper = createWrapper();
      await nextTick();
      const nextButton = findNextButton(wrapper);
      await nextButton.trigger("click");
      await nextTick();
      expect(wrapper.html()).toContain("errorMessageMailAddressValidation");
      expect(wrapper.emitted("next")).toBeUndefined();
    });

    it("should emit next when form is valid", async () => {
      mockCustomerData.value.firstName = "Max";
      mockCustomerData.value.lastName = "Mustermann";
      mockCustomerData.value.mailAddress = "max@test.de";
      const wrapper = createWrapper();
      await nextTick();
      const nextButton = findNextButton(wrapper);
      await nextButton.trigger("click");
      await nextTick();
      expect(wrapper.emitted("next")).toBeTruthy();
    });

    it("should show error message for blank firstName", async () => {
      mockCustomerData.value.firstName = "";
      mockCustomerData.value.lastName = "Mustermann";
      mockCustomerData.value.mailAddress = "max@example.com";
      const wrapper = createWrapper();
      await nextTick();
      const nextButton = findNextButton(wrapper);
      await nextButton.trigger("click");
      await nextTick();
      expect(wrapper.html()).toContain("errorMessageFirstName");
      expect(wrapper.emitted("next")).toBeUndefined();
    });

    it("should show error message for blank lastName", async () => {
      mockCustomerData.value.firstName = "Max";
      mockCustomerData.value.lastName = "";
      mockCustomerData.value.mailAddress = "max@example.com";
      const wrapper = createWrapper();
      await nextTick();
      const nextButton = findNextButton(wrapper);
      await nextButton.trigger("click");
      await nextTick();
      expect(wrapper.html()).toContain("errorMessageLastName");
      expect(wrapper.emitted("next")).toBeUndefined();
    });

    it("should show error message for blank mailAddress", async () => {
      mockCustomerData.value.firstName = "Max";
      mockCustomerData.value.lastName = "Mustermann";
      mockCustomerData.value.mailAddress = "";
      const wrapper = createWrapper();
      await nextTick();
      const nextButton = findNextButton(wrapper);
      await nextButton.trigger("click");
      await nextTick();
      expect(wrapper.html()).toContain("errorMessageMailAddressRequired");
      expect(wrapper.emitted("next")).toBeUndefined();
    });

    it("should show error message for blank telephoneNumber", async () => {
      mockCustomerData.value.firstName = "Max";
      mockCustomerData.value.lastName = "Mustermann";
      mockCustomerData.value.mailAddress = "max@example.com";
      mockCustomerData.value.telephoneNumber = "";
      mockSelectedProvider.value.scope.telephoneActivated = true;
      mockSelectedProvider.value.scope.telephoneRequired = true;
      const wrapper = createWrapper();
      await nextTick();
      const nextButton = findNextButton(wrapper);
      await nextButton.trigger("click");
      await nextTick();
      expect(wrapper.html()).toContain("errorMessageTelephoneNumberRequired");
      expect(wrapper.emitted("next")).toBeUndefined();
    });

    it("should show error message for invalid telephoneNumber", async () => {
      mockCustomerData.value.firstName = "Max";
      mockCustomerData.value.lastName = "Mustermann";
      mockCustomerData.value.mailAddress = "max@example.com";
      mockCustomerData.value.telephoneNumber = "invalid-phone";
      mockSelectedProvider.value.scope.telephoneActivated = true;
      mockSelectedProvider.value.scope.telephoneRequired = true;
      const wrapper = createWrapper();
      await nextTick();
      const nextButton = findNextButton(wrapper);
      await nextButton.trigger("click");
      await nextTick();
      expect(wrapper.html()).toContain("errorMessageTelephoneNumberValidation");
      expect(wrapper.emitted("next")).toBeUndefined();
    });
  });

  describe("Optional Fields", () => {
    it("should not show telephone field when not activated", async () => {
      const wrapper = createWrapper();
      await nextTick();
      expect(wrapper.find("#telephonenumber").exists()).toBe(false);
    });

    it("should show telephone field when activated", async () => {
      mockSelectedProvider.value.scope.telephoneActivated = true;
      const wrapper = createWrapper();
      await nextTick();
      expect(wrapper.find("#telephonenumber").exists()).toBe(true);
    });

    it("should not show custom textfield when not activated", async () => {
      const wrapper = createWrapper();
      await nextTick();
      expect(wrapper.find("#remarks").exists()).toBe(false);
    });

    it("should show custom textfield when activated", async () => {
      mockSelectedProvider.value.scope.customTextfieldActivated = true;
      const wrapper = createWrapper();
      await nextTick();
      expect(wrapper.find("#remarks").exists()).toBe(true);
    });
  });

  describe("Navigation", () => {
    it("should emit back when back button is clicked", async () => {
      const wrapper = createWrapper();
      await nextTick();
      const backButton = findBackButton(wrapper);
      await backButton.trigger("click");
      await nextTick();
      expect(wrapper.emitted("back")).toBeTruthy();
    });
  });

  describe("Test submission loading state", () => {
    it("test loading state when booking appointment", async () => {
      const wrapper = createWrapper();

      wrapper.vm.loadingStates.isBookingAppointment.value = true;
      await nextTick();
      expect(wrapper.vm.loadingStates.isBookingAppointment.value).toBe(true);

      wrapper.vm.loadingStates.isBookingAppointment.value = false;
      await nextTick();
      expect(wrapper.vm.loadingStates.isBookingAppointment.value).toBe(false);
    });

    it("enables the next button when form is valid and not loading, disables it during update, and re-enables after update", async () => {
      mockCustomerData.value.firstName = "Max";
      mockCustomerData.value.lastName = "Mustermann";
      mockCustomerData.value.mailAddress = "max@test.de";
      const wrapper = createWrapper();
      await nextTick();

      let nextButton = findNextButton(wrapper);
      // enabled
      expect(nextButton.attributes("disabled")).toBeUndefined();

      wrapper.vm.loadingStates.isUpdatingAppointment.value = true;
      await nextTick();
      nextButton = findNextButton(wrapper);
      // disabled
      expect(nextButton.attributes("disabled")).toBeDefined();

      wrapper.vm.loadingStates.isUpdatingAppointment.value = false;
      await nextTick();
      nextButton = findNextButton(wrapper);
      expect(nextButton.attributes("disabled")).toBeUndefined();
    });
  });

  const MAX_LENGTH_STANDARD = 50;
  const MAX_LENGTH_CUSTOM = 250;
  const setupValidCustomerData = () => {
    mockCustomerData.value.firstName = "Max";
    mockCustomerData.value.lastName = "Mustermann";
    mockCustomerData.value.mailAddress = "max@example.com";
  };

  describe("Field length validation", () => {
    it("should show max length error when firstName reaches/exceeds maximum length", async () => {
      setupValidCustomerData();
      mockCustomerData.value.firstName = "A".repeat(MAX_LENGTH_STANDARD);
      const wrapper = createWrapper();
      await nextTick();
      expect(wrapper.html()).toContain("errorMessageMaxLength");
    });

    it("should show max length error when lastName reaches/exceeds maximum length", async () => {
      setupValidCustomerData();
      mockCustomerData.value.lastName = "B".repeat(MAX_LENGTH_STANDARD);
      const wrapper = createWrapper();
      await nextTick();
      expect(wrapper.html()).toContain("errorMessageMaxLength");
    });

    it("should show max length error when mailAddress reaches/exceeds maximum length", async () => {
      setupValidCustomerData();
      mockCustomerData.value.mailAddress =
        "a".repeat(MAX_LENGTH_STANDARD - 12) + "@example.com";
      const wrapper = createWrapper();
      await nextTick();
      expect(wrapper.html()).toContain("errorMessageMaxLength");
    });

    it("should show max length error when telephoneNumber exceeds maximum length", async () => {
      setupValidCustomerData();
      mockCustomerData.value.telephoneNumber = "1".repeat(MAX_LENGTH_STANDARD + 1);
      mockSelectedProvider.value.scope.telephoneActivated = true;
      mockSelectedProvider.value.scope.telephoneRequired = true;
      const wrapper = createWrapper();
      await nextTick();
      expect(wrapper.html()).toContain("errorMessageMaxLength");
    });

    it("should show max length error when customTextfield exceeds maximum length", async () => {
      setupValidCustomerData();
      mockCustomerData.value.customTextfield = "X".repeat(MAX_LENGTH_CUSTOM + 1);
      mockSelectedProvider.value.scope.customTextfieldActivated = true;
      mockSelectedProvider.value.scope.customTextfieldRequired = true;
      const wrapper = createWrapper();
      await nextTick();
      expect(wrapper.html()).toContain("errorMessageMaxLength");
    });

    it("should show max length error when customTextfield2 exceeds maximum length", async () => {
      setupValidCustomerData();
      mockCustomerData.value.customTextfield2 = "Y".repeat(MAX_LENGTH_CUSTOM + 1);
      mockSelectedProvider.value.scope.customTextfield2Activated = true;
      mockSelectedProvider.value.scope.customTextfield2Required = true;
      const wrapper = createWrapper();
      await nextTick();
      expect(wrapper.html()).toContain("errorMessageMaxLength");
    });
  });

  describe("Login failure", () => {
    it("shows emergency banner when loginFailed is true", async () => {
      const wrapper = createWrapper({ loginFailed: true });
      await nextTick();

      const banner = wrapper.find(".muc-banner");
      expect(banner.exists()).toBe(true);
      expect(banner.attributes("data-type")).toBe("emergency");
      expect(banner.text()).toContain("loginFailedText");
    });

    it("hides login failure banner when loginFailed is false", async () => {
      const wrapper = createWrapper({ loginFailed: false });
      await nextTick();

      expect(wrapper.find(".muc-banner").exists()).toBe(false);
    });
  });

  describe("Session timeout", () => {
    it("displays the timeout message when the reservation time has expired", async () => {
      // Reservationtime: 1 minute
      mockAppointmentProvider.appointment.value = { scope: { reservationDuration: 1 } };
      mockReservationStartMs.value = Date.now() - 2 * 60 * 1000;
  
      const wrapper = createWrapper();
      await nextTick();
  
      expect(wrapper.html()).toContain("apiErrorSessionTimeoutHeader");
      expect(wrapper.html()).toContain("apiErrorSessionTimeoutText");
  
      // form and next-button sre not displayed
      expect(wrapper.find(".m-form").exists()).toBe(false);
      const buttons = wrapper.findAll(".m-button-group .muc-button");
      expect(buttons.length).toBe(1); // only back-button
    });
  });
});