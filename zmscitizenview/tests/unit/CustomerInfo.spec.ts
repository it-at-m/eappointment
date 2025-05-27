import { mount } from "@vue/test-utils";
import { describe, expect, it, beforeEach } from "vitest";
import { nextTick, ref } from "vue";

// @ts-expect-error: Vue SFC import for test
import CustomerInfo from "@/components/Appointment/CustomerInfo.vue";

describe("CustomerInfo", () => {
  const mockT = (key: string) => key;
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
        t: mockT,
      },
      global: {
        provide: {
          customerData: { customerData: mockCustomerData },
          selectedTimeslot: { selectedProvider: mockSelectedProvider },
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
        },
      },
    });
  };

  describe("Form Validation", () => {
    it("should not emit next when form is invalid", async () => {
      const wrapper = createWrapper();
      await nextTick();
      const nextButton = wrapper.find(".m-button-group button:last-child");
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
      const nextButton = wrapper.find(".m-button-group button:last-child");
      await nextButton.trigger("click");
      await nextTick();
      // Check error message is present
      expect(wrapper.html()).toContain("errorMessageMailAddressValidation");
      expect(wrapper.emitted("next")).toBeUndefined();
    });

    it("should emit next when form is valid", async () => {
      mockCustomerData.value.firstName = "Max";
      mockCustomerData.value.lastName = "Mustermann";
      mockCustomerData.value.mailAddress = "max@test.de";
      const wrapper = createWrapper();
      await nextTick();
      const nextButton = wrapper.find(".m-button-group button:last-child");
      await nextButton.trigger("click");
      await nextTick();
      expect(wrapper.emitted("next")).toBeTruthy();
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
      // default is not activated
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
      const backButton = wrapper.find(".m-button-group button:first-child");
      await backButton.trigger("click");
      await nextTick();
      expect(wrapper.emitted("back")).toBeTruthy();
    });
  });
}); 