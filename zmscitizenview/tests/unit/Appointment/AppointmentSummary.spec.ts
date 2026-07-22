import { mount } from "@vue/test-utils";
import { describe, expect, it, beforeEach } from "vitest";
import { nextTick, ref } from "vue";

import AppointmentSummary from "@/components/Appointment/AppointmentSummary.vue";

describe("AppointmentSummary", () => {
  let mockSelectedService: any;
  let mockSelectedProvider: any;
  let mockAppointment: any;
  let mockReservationStartMs: any;

  beforeEach(() => {
    mockSelectedService = ref({
      id: "123",
      name: "Test Service",
      count: 2,
      subServices: [{ id: "456", name: "Sub Service 1", count: 1 }],
    });

    mockSelectedProvider = ref({
      id: "789",
      name: "Test Provider",
      address: {
        street: "Test Street",
        house_number: "123",
        postal_code: "12345",
        city: "Test City",
      },
      scope: {
        infoForAppointment: "Test Info",
        infoForAllAppointments: "<p>Test Info</p>",
        customTextfieldLabel: "Custom Field 1",
        customTextfield2Label: "Custom Field 2",
      },
    });

    mockAppointment = ref({
      scope: {
        reservationDuration: 15,
        infoForAppointment: "Test Info Scope",
        infoForAllAppointments: "<p>Test Info</p>",
        customTextfieldLabel: "Custom Field 1",
        customTextfield2Label: "Custom Field 2",
      },
      timestamp: Math.floor(Date.now() / 1000),
      familyName: "John Doe",
      email: "john@example.com",
      telephone: "1234567890",
      customTextfield: "Custom Value 1",
      customTextfield2: "Custom Value 2",
    });

    mockReservationStartMs = ref<number | null>(Date.now());
  });

  const createWrapper = (props: Partial<{ isRebooking: boolean; rebookOrCancelDialog: boolean }> = {}) => {
    return mount(AppointmentSummary, {
      props: {
        isRebooking: false,
        rebookOrCancelDialog: false,
        t: (key: string) => key,
        ...props,
      },
      global: {
        provide: {
          selectedServiceProvider: { selectedService: mockSelectedService },
          selectedTimeslot: { selectedProvider: mockSelectedProvider },
          appointment: { appointment: mockAppointment },
          reservationStartMs: mockReservationStartMs,
          loadingStates: {
            isReservingAppointment: ref(false),
            isUpdatingAppointment: ref(false),
            isBookingAppointment: ref(false),
            isCancelingAppointment: ref(false),
          },
          serviceLinkProvider: {
            serviceLinkId: ref<string | null>(null),
            updateServiceLinkId: () => { },
          },
        },
        stubs: {
          "muc-button": {
            template:
              '<button class="muc-button" :data-icon="icon" :disabled="disabled" @click="$emit(\'click\')"><slot /></button>',
            props: ["icon", "iconShownLeft", "variant", "disabled"],
          },
          "muc-callout": {
            template:
              '<div class="muc-callout"><div class="header"><slot name="header" /></div><div class="content"><slot name="content" /></div></div>',
            props: ["type"],
          },
        },
      },
    });
  };

  const findButtonByIcon = (wrapper: any, icon: string) =>
    wrapper.findAll(".muc-button").find((b: any) => b.attributes("data-icon") === icon);

  describe("Rendering", () => {
    it("renders service information correctly", () => {
      const wrapper = createWrapper();
      expect(wrapper.text()).toContain("Test Service");
      expect(wrapper.text()).toContain("2x");
      expect(wrapper.text()).toContain("Sub Service 1");
      expect(wrapper.text()).toContain("1x");
    });

    it("uses rootParentId for service link when serviceLinkId is unset", () => {
      mockSelectedService.value = {
        id: "999",
        parentId: "3",
        rootParentId: "1063423",
        name: "Gewerbe-Anmeldung Video",
        count: 1,
        variantId: 2,
        subServices: [],
      };
      const wrapper = createWrapper();
      const link = wrapper.find("a.m-link");
      expect(link.attributes("href")).toContain("1063423");
      expect(link.attributes("href")).not.toContain("/0");
    });

    it("renders provider information correctly", () => {
      const wrapper = createWrapper();
      expect(wrapper.text()).toContain("Test Provider");
      expect(wrapper.text()).toContain("Test Street 123");
      expect(wrapper.text()).toContain("12345 Test City");
      expect(wrapper.text()).toContain("Test Info Scope");
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
    it("disables preconfirm button when electronic communication checkbox is not checked", () => {
      const wrapper = createWrapper();
      const bookButton = findButtonByIcon(wrapper, "check");
      expect(bookButton).toBeTruthy();
      expect(bookButton!.attributes("disabled")).toBeDefined(); // disabled
    });

    it("enables preconfirm button when electronic communication checkbox is checked", async () => {
      const wrapper = createWrapper();
      const communicationCheckbox = wrapper.find('input[name="checkbox-electronic-communication"]');
      await communicationCheckbox.trigger("click");
      await nextTick();
      const bookButton = findButtonByIcon(wrapper, "check");
      expect(bookButton).toBeTruthy();
      expect(bookButton!.attributes("disabled")).toBeUndefined(); // enabled
    });

    it("disables preconfirm button when checkbox is unchecked after it was checked", async () => {
      const wrapper = createWrapper();
      const communicationCheckbox = wrapper.find('input[name="checkbox-electronic-communication"]');
      await communicationCheckbox.trigger("click");
      await nextTick();
      let bookButton = findButtonByIcon(wrapper, "check");
      expect(bookButton).toBeTruthy();
      expect(bookButton!.attributes("disabled")).toBeUndefined();
      await communicationCheckbox.trigger("click");
      await nextTick();
      bookButton = findButtonByIcon(wrapper, "check");
      expect(bookButton!.attributes("disabled")).toBeDefined();
    });

    it("renders privacy text and electronic communication checkbox", () => {
      const wrapper = createWrapper();
      const communicationCheckbox = wrapper.find('input[name="checkbox-electronic-communication"]');
      expect(wrapper.text()).toContain("privacyText");
      expect(wrapper.text()).not.toContain("privacyVideoConsultationText");
      expect(wrapper.text()).not.toContain("termsOfUseForVideoConsultationLabel");
      expect(wrapper.text()).not.toContain("termsOfUseForVideoConsultationText");
      expect(wrapper.find('input[name="checkbox-privacy-policy"]').exists()).toBe(false);
      expect(communicationCheckbox.exists()).toBe(true);
      expect(wrapper.find('label[for="checkbox-electronic-communication"]').exists()).toBe(true);
      expect(wrapper.find('input[name="checkbox-video-consultation"]').exists()).toBe(false);
      expect(wrapper.find('label[for="checkbox-video-consultation"]').exists()).toBe(false);
    });

    it("renders additional video consultation privacy and terms only for video appointments", async () => {
      mockSelectedService.value.variantId = 3;
      const wrapper = createWrapper();
      await nextTick();
      expect(wrapper.text()).toContain("privacyText");
      expect(wrapper.text()).toContain("privacyVideoConsultationText");
      expect(wrapper.text()).toContain("termsOfUseForVideoConsultationLabel");
      expect(wrapper.text()).toContain("termsOfUseForVideoConsultationText");
      expect(wrapper.find('input[name="checkbox-electronic-communication"]').exists()).toBe(true);
      expect(wrapper.find('input[name="checkbox-video-consultation"]').exists()).toBe(true);
      expect(wrapper.find('label[for="checkbox-video-consultation"]').exists()).toBe(true);
    });

    it("keeps preconfirm button disabled for video appointments when only electronic communication is checked", async () => {
      mockSelectedService.value.variantId = 3;
      const wrapper = createWrapper();
      const communicationCheckbox = wrapper.find('input[name="checkbox-electronic-communication"]');
      await communicationCheckbox.trigger("click");
      await nextTick();
      const bookButton = findButtonByIcon(wrapper, "check");
      expect(bookButton).toBeTruthy();
      expect(bookButton!.attributes("disabled")).toBeDefined();
    });

    it("enables preconfirm button for video appointments only when both required checkboxes are checked", async () => {
      mockSelectedService.value.variantId = 3;
      const wrapper = createWrapper();
      const communicationCheckbox = wrapper.find('input[name="checkbox-electronic-communication"]');
      const videoConsultationCheckbox = wrapper.find('input[name="checkbox-video-consultation"]');
      await communicationCheckbox.trigger("click");
      await videoConsultationCheckbox.trigger("click");
      await nextTick();
      const bookButton = findButtonByIcon(wrapper, "check");
      expect(bookButton).toBeTruthy();
      expect(bookButton!.attributes("disabled")).toBeUndefined();
    });

    it("disables preconfirm button for video appointments when video consultation checkbox is unchecked again", async () => {
      mockSelectedService.value.variantId = 3;
      const wrapper = createWrapper();
      const communicationCheckbox = wrapper.find('input[name="checkbox-electronic-communication"]');
      const videoConsultationCheckbox = wrapper.find('input[name="checkbox-video-consultation"]');
      await communicationCheckbox.trigger("click");
      await videoConsultationCheckbox.trigger("click");
      await nextTick();
      let bookButton = findButtonByIcon(wrapper, "check");
      expect(bookButton).toBeTruthy();
      expect(bookButton!.attributes("disabled")).toBeUndefined();
      await videoConsultationCheckbox.trigger("click");
      await nextTick();
      bookButton = findButtonByIcon(wrapper, "check");
      expect(bookButton!.attributes("disabled")).toBeDefined();
    });

    it("does not show consent checkbox in rebookOrCancelDialog mode", () => {
      const wrapper = createWrapper({ rebookOrCancelDialog: true });
      const communicationCheckbox = wrapper.find('input[name="checkbox-electronic-communication"]');
      expect(communicationCheckbox.exists()).toBe(false);
    });
  });

  describe("Navigation", () => {
    it("emits back event when back button is clicked", async () => {
      const wrapper = createWrapper();
      const back = findButtonByIcon(wrapper, "arrow-left");
      expect(back).toBeTruthy();
      await back!.trigger("click");
      expect(wrapper.emitted("back")).toBeTruthy();
    });

    it("emits bookAppointment when preconfirm button is clicked", async () => {
      const wrapper = createWrapper();
      await wrapper.find('input[name="checkbox-electronic-communication"]').trigger("click");
      await nextTick();
      const book = findButtonByIcon(wrapper, "check");
      expect(book).toBeTruthy();
      await book!.trigger("click");
      expect(wrapper.emitted("bookAppointment")).toBeTruthy();
    });
  });

  describe("Rebooking Mode", () => {
    it("shows reschedule and cancel buttons in rebookOrCancelDialog mode", () => {
      const wrapper = createWrapper({ rebookOrCancelDialog: true });
      expect(findButtonByIcon(wrapper, "arrow-right")).toBeTruthy();
      expect(findButtonByIcon(wrapper, "close")).toBeTruthy();
    });

    it("emits rescheduleAppointment when reschedule is clicked", async () => {
      const wrapper = createWrapper({ rebookOrCancelDialog: true });
      const reschedule = findButtonByIcon(wrapper, "arrow-right");
      await reschedule!.trigger("click");
      expect(wrapper.emitted("rescheduleAppointment")).toBeTruthy();
    });

    it("emits cancelAppointment when cancel is clicked", async () => {
      const wrapper = createWrapper({ rebookOrCancelDialog: true });
      const cancel = findButtonByIcon(wrapper, "close");
      await cancel!.trigger("click");
      expect(wrapper.emitted("cancelAppointment")).toBeTruthy();
    });
  });

  describe("Rebooking Flow", () => {
    it("shows reschedule and cancel buttons in rebooking mode", () => {
      const wrapper = createWrapper({ isRebooking: true });
      expect(findButtonByIcon(wrapper, "check")).toBeTruthy();
      expect(findButtonByIcon(wrapper, "close")).toBeTruthy();
    });

    it("emits bookAppointment when reschedule button is clicked", async () => {
      const wrapper = createWrapper({ isRebooking: true });

      // validForm fulfilled (both consents set)
      const comm = wrapper.find('input[name="checkbox-electronic-communication"]');
      await comm.trigger("click");
      await nextTick();

      // find button and make sure it is not disabled
      const reschedule = findButtonByIcon(wrapper, "check");
      expect(reschedule).toBeTruthy();
      expect(reschedule!.attributes("disabled")).toBeUndefined();

      // trigger click and check emission
      await reschedule!.trigger("click");
      await nextTick();
      expect(wrapper.emitted("bookAppointment")).toBeTruthy();
    });

    it("emits cancelReschedule when cancel is clicked", async () => {
      const wrapper = createWrapper({ isRebooking: true });
      const cancel = findButtonByIcon(wrapper, "close");
      await cancel!.trigger("click");
      expect(wrapper.emitted("cancelReschedule")).toBeTruthy();
    });
  });

  describe("Test submission loading state", () => {
    it("toggles booking loading state", async () => {
      const wrapper = createWrapper();
      wrapper.vm.loadingStates.isBookingAppointment.value = true;
      await nextTick();
      expect(wrapper.vm.loadingStates.isBookingAppointment.value).toBe(true);
      wrapper.vm.loadingStates.isBookingAppointment.value = false;
      await nextTick();
      expect(wrapper.vm.loadingStates.isBookingAppointment.value).toBe(false);
    });

    it("book button enables/disables on valid form and loading", async () => {
      const wrapper = createWrapper();
      await wrapper.find('input[name="checkbox-electronic-communication"]').trigger("click");
      await nextTick();
      let book = findButtonByIcon(wrapper, "check");
      expect(book!.attributes("disabled")).toBeUndefined(); // enabled

      wrapper.vm.loadingStates.isBookingAppointment.value = true;
      await nextTick();
      book = findButtonByIcon(wrapper, "check");
      expect(book!.attributes("disabled")).toBeDefined(); // disabled

      wrapper.vm.loadingStates.isBookingAppointment.value = false;
      await nextTick();
      book = findButtonByIcon(wrapper, "check");
      expect(book!.attributes("disabled")).toBeUndefined(); // enabled
    });
  });

  describe("Session timeout", () => {
    it("displays the timeout message and hides content when expired", async () => {
      // short reservation time and start far in the past -> expired
      mockAppointment.value.scope = { reservationDuration: 1 }; // 1 minute
      mockReservationStartMs.value = Date.now() - 2 * 60 * 1000; // 2 minutes in the past

      const wrapper = createWrapper();
      await nextTick();

      // message visible
      expect(wrapper.text()).toContain("apiErrorSessionTimeoutHeader");
      expect(wrapper.text()).toContain("apiErrorSessionTimeoutText");

      // main content not visible
      expect(wrapper.find(".m-component").exists()).toBe(false);

      // only back-button visible
      const anyButtons = wrapper.findAll(".muc-button");
      expect(anyButtons.length).toBe(1);
    });
  });

  describe("Location variants", () => {
    it("should show address/hint and base text when no variant is set (variantId null)", () => {
      const wrapper = createWrapper();

      expect(wrapper.text()).toContain("Test Street 123");
      expect(wrapper.text()).toContain("12345 Test City");
      expect(wrapper.text()).toContain("Test Info Scope");
      expect(wrapper.text()).not.toContain("appointmentTypes.1");
      expect(wrapper.text()).not.toContain("locationVariantText.1");
    });

    it("should show address/hint and base text for variant 1", async () => {
      mockSelectedService.value.variantId = 1;
      const wrapper = createWrapper();
      await nextTick();

      expect(wrapper.text()).toContain("Test Street 123");
      expect(wrapper.text()).toContain("12345 Test City");
      expect(wrapper.text()).toContain("Test Info Scope");

      expect(wrapper.text()).toContain("appointmentTypes.1");
      expect(wrapper.text()).toContain("locationVariantText.1");
    });

    it("should hide address/hint and show variant text for variant 2", async () => {
      mockSelectedService.value.variantId = 2;
      const wrapper = createWrapper();
      await nextTick();

      expect(wrapper.text()).not.toContain("Test Street 123");
      expect(wrapper.text()).not.toContain("12345 Test City");

      const borderBlocks = wrapper.findAll(".m-content.border-bottom");
      const locationBlock = borderBlocks.find(b =>
        b.text().includes("appointmentTypes.2")
      );
      expect(locationBlock).toBeTruthy();

      expect(locationBlock!.text()).not.toContain("Test Info Scope");

      expect(wrapper.text()).toContain("appointmentTypes.2");
      expect(wrapper.text()).toContain("locationVariantText.2");
    });

    it.each([4, 5, 6, 7])(
      "should show address and scope hint for variant %i",
      async (variantId) => {
        mockSelectedService.value.variantId = variantId;

        const wrapper = createWrapper();
        await nextTick();

        expect(wrapper.text()).toContain("Test Street 123");
        expect(wrapper.text()).toContain("12345 Test City");
        expect(wrapper.text()).toContain("Test Info Scope");
      }
    );

    it.each([
      [4, "appointmentTypes.4"],
      [5, "appointmentTypes.5"],
      [6, "appointmentTypes.1"],
      [7, "appointmentTypes.1"],
    ])(
      "should show the correct appointment type label for variant %i",
      async (variantId, expectedAppointmentTypeLabel) => {
        mockSelectedService.value.variantId = variantId;

        const wrapper = createWrapper();
        await nextTick();

        expect(wrapper.text()).toContain(expectedAppointmentTypeLabel);
      }
    );

    it("should hide address for variant 3 (video)", async () => {
      mockSelectedService.value.variantId = 3;
      const wrapper = createWrapper();
      await nextTick();

      expect(wrapper.text()).not.toContain("Test Street 123");
      expect(wrapper.text()).toContain("appointmentTypes.3");
      expect(wrapper.text()).toContain("locationVariantText.3");
    });
  });
});
