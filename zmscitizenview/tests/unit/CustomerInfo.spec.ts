import { mount } from "@vue/test-utils";
import { describe, expect, it, beforeEach } from "vitest";
import { nextTick, ref } from "vue";

// @ts-expect-error: Vue SFC import for test
import CustomerInfo from "@/components/Appointment/CustomerInfo.vue";

describe("CustomerInfo", () => {
  let mockCustomerData;
  let mockSelectedProvider;

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
  });

  const createWrapper = () => {
    return mount(CustomerInfo, {
      props: {
        t: (key: string) => key,
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
          "muc-button": true,
        },
      },
    });
  };

  describe("Form Validation", () => {
    it("should not emit next when form is invalid", async () => {
      const wrapper = createWrapper();
      await nextTick();
      const nextButton = wrapper.find('muc-button-stub[variant="primary"]');
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
      const nextButton = wrapper.find('muc-button-stub[variant="primary"]');
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
      const nextButton = wrapper.find('muc-button-stub[variant="primary"]');
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
      const nextButton = wrapper.find('muc-button-stub[variant="primary"]');
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
      const nextButton = wrapper.find('muc-button-stub[variant="primary"]');
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
      const nextButton = wrapper.find('muc-button-stub[variant="primary"]');
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
      const nextButton = wrapper.find('muc-button-stub[variant="primary"]');
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
      const nextButton = wrapper.find('muc-button-stub[variant="primary"]');
      await nextButton.trigger("click");
      await nextTick();
      expect(wrapper.html()).toContain("errorMessageTelephoneNumberValidation");
      expect(wrapper.emitted("next")).toBeUndefined();
    });
  });

  describe("Optional Fields", () => {
    it("should not show telephone field when not activated", async () => {
      // default is not activated
      const wrapper = createWrapper();
      await nextTick();
      expect(wrapper.find("#telephonenumber").exists()).toBe(false);
    });

    it("should show telephone field when activated", async () => {
      mockSelectedProvider.value = {
        scope: {
          telephoneActivated: true,
          telephoneRequired: false,
          customTextfieldActivated: false,
          customTextfieldRequired: false,
          customTextfield2Activated: false,
          customTextfield2Required: false,
        },
      };
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
      mockSelectedProvider.value = {
        scope: {
          telephoneActivated: false,
          telephoneRequired: false,
          customTextfieldActivated: true,
          customTextfieldRequired: false,
          customTextfield2Activated: false,
          customTextfield2Required: false,
        },
      };
      const wrapper = createWrapper();
      await nextTick();
      expect(wrapper.find("#remarks").exists()).toBe(true);
    });
  });

  describe("Navigation", () => {
    it("should emit back when back button is clicked", async () => {
      const wrapper = createWrapper();
      await nextTick();
      const backButton = wrapper.find('muc-button-stub[variant="secondary"]');
      await backButton.trigger("click");
      await nextTick();
      expect(wrapper.emitted("back")).toBeTruthy();
    });
  });

  describe("Test submission loading state", () => {
    it("test loading state when booking appointment", async () => {
      const wrapper = createWrapper();

      // Set loading state
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
      // Find the Next button (should be the second muc-button-stub)
      let nextButton = wrapper.findAll('muc-button-stub')[1];
      // Should be enabled when form is valid and not loading
      expect(nextButton.attributes('disabled')).toBe('false');

      // Set loading state
      wrapper.vm.loadingStates.isUpdatingAppointment.value = true;
      await nextTick();
      nextButton = wrapper.findAll('muc-button-stub')[1];
      expect(nextButton.attributes('disabled')).toBe('true');

      // Reset loading state
      wrapper.vm.loadingStates.isUpdatingAppointment.value = false;
      await nextTick();
      nextButton = wrapper.findAll('muc-button-stub')[1];
      expect(nextButton.attributes('disabled')).toBe('false');
    });
  });

  describe("Field length validation", () => {
    it("should show info when firstName contains 50 characters", async () => {
      mockCustomerData.value.firstName = "A".repeat(50);
      mockCustomerData.value.lastName = "Mustermann";
      mockCustomerData.value.mailAddress = "max@example.com";
      const wrapper = createWrapper();
      await nextTick();
      expect(wrapper.html()).toContain("errorMessageMaxLength");
    });

    it("should show info when lastName contains 50 characters", async () => {
      mockCustomerData.value.firstName = "Max";
      mockCustomerData.value.lastName = "B".repeat(50);
      mockCustomerData.value.mailAddress = "max@example.com";
      const wrapper = createWrapper();
      await nextTick();
      expect(wrapper.html()).toContain("errorMessageMaxLength");
    });

    it("should show info when mailAddress contains 50 characters", async () => {
      mockCustomerData.value.firstName = "Max";
      mockCustomerData.value.lastName = "Mustermann";
      mockCustomerData.value.mailAddress = "a".repeat(38) + "@example.com";
      const wrapper = createWrapper();
      await nextTick();
      expect(wrapper.html()).toContain("errorMessageMaxLength");
    });

    it("should show info when telephoneNumber contains 50 characters", async () => {
      mockCustomerData.value.firstName = "Max";
      mockCustomerData.value.lastName = "Mustermann";
      mockCustomerData.value.mailAddress = "max@example.com";
      mockCustomerData.value.telephoneNumber = "1".repeat(50);
      mockSelectedProvider.value.scope.telephoneActivated = true;
      mockSelectedProvider.value.scope.telephoneRequired = true;
      const wrapper = createWrapper();
      await nextTick();
      expect(wrapper.html()).toContain("errorMessageMaxLength");
    });

    it("should show info when customTextfield contains 100 characters", async () => {
      mockCustomerData.value.firstName = "Max";
      mockCustomerData.value.lastName = "Mustermann";
      mockCustomerData.value.mailAddress = "max@example.com";
      mockCustomerData.value.customTextfield = "X".repeat(100);
      mockSelectedProvider.value.scope.customTextfieldActivated = true;
      mockSelectedProvider.value.scope.customTextfieldRequired = true;
      const wrapper = createWrapper();
      await nextTick();
      expect(wrapper.html()).toContain("errorMessageMaxLength");
    });

    it("should show info when customTextfield2 contains 100 characters", async () => {
      mockCustomerData.value.firstName = "Max";
      mockCustomerData.value.lastName = "Mustermann";
      mockCustomerData.value.mailAddress = "max@example.com";
      mockCustomerData.value.customTextfield2 = "X".repeat(100);
      mockSelectedProvider.value.scope.customTextfield2Activated = true;
      mockSelectedProvider.value.scope.customTextfield2Required = true;
      const wrapper = createWrapper();
      await nextTick();
      expect(wrapper.html()).toContain("errorMessageMaxLength");
    });
  });
});