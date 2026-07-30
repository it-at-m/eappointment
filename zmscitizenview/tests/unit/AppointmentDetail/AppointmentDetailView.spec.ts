import { mount } from "@vue/test-utils";
import {afterAll, beforeAll, describe, expect, it, vi} from "vitest";
import de from '@/utils/de-DE.json';
import { nextTick } from "vue";

import AppointmentDetailView from "@/components/AppointmentDetail/AppointmentDetailView.vue";
import { VIDEO_CONSULTATION_INFO_URL } from "@/utils/Constants";

globalThis.scrollTo = vi.fn();

describe("AppointmentDetailView", () => {
  const translate = (key: string): string => {
    const value = key.split(".").reduce<unknown>((current, part) => {
      if (typeof current !== "object" || current === null) {
        return undefined;
      }

      return (current as Record<string, unknown>)[part];
    }, de);

    return typeof value === "string" ? value : key;
  };

  beforeAll(() => {
    vi.stubGlobal("fetch", vi.fn().mockResolvedValue({
      status: 200,
      json: async () => ({
        offices: [],
      }),
    }));
    vi.stubGlobal("matchMedia", vi.fn(() => {
      return {
        matches: false,
        addListener: vi.fn(),
        removeListener: vi.fn(),
      };
    }));
  });

  afterAll(() => {
    vi.unstubAllGlobals();
  });

  const mockAppointmentOverviewUrl = "https://www.muenchen.de/appointment-overview";
  const mockRescheduleAppointmentUrl = "https://www.muenchen.de/reschedule-appointment";

  const mockAppointment =
    {
      timestamp: Math.floor(Date.now() / 1000),
      processId: "12345",
      familyName: "John Doe",
      email: "john@example.com",
      officeId: "1",
      telephone: "1234567890",
      serviceId: "id_12345",
      serviceName: "Personalausweis",
      serviceCount: 1,
      subRequestCounts: [],
    };

  const mockSubRequestAppointment =
    {
      timestamp: Math.floor(Date.now() / 1000),
      processId: "12345",
      familyName: "John Doe",
      email: "john@example.com",
      officeId: "1",
      telephone: "1234567890",
      serviceId: "id_12345",
      serviceName: "Personalausweis",
      serviceCount: 1,
      subRequestCounts: [
        {
          id: "subid_12345",
          name: "Reisepass",
          count: 2,
        },
        {
          id: "subid_6789",
          name: "Wohnsitz ummelden",
          count: 3,
        },
      ],
    };

  const mockProvider = {
    id: "1",
    name: "Rathaus Marienplatz",
    address: {
      street: "Marienplatz",
      house_number: "8",
      postal_code: "80331",
      city: "Muenchen"
    },
  };

  const createWrapper = (props = {}) => {
    return mount(AppointmentDetailView, {
      props: {
        globalState: {
          isLoggedIn: true,
        },
        appointmentOverviewUrl: mockAppointmentOverviewUrl,
        rescheduleAppointmentUrl: mockRescheduleAppointmentUrl,
        t: translate,
        ...props,
      },
      global: {
        stubs: {
          'muc-intro': {
            template: `<div data-test='muc-intro' :tagline="tagline" :title="title">
              <slot></slot>
            </div>`,
            props: ["tagline", "title"],
          },
          'error-alert': {
            template: "<div data-test='error-alert'><slot></slot></div>",
            props: ["message", "header"],
          },
          'muc-button': {
            template: "<div data-test='muc-button'></div>",
            props: ["icon"],
          },
          'appointment-detail-header': {
            template:
              "<div data-test='appointment-detail-header' :data-variant-id='variantId'></div>",
            props: ["appointment", "selectedProvider", "variantId"],
            emits: ["cancelAppointment", "rescheduleAppointment"],
          },
          'calendar-icon': {
            template: "<div data-test='calendar-icon'></div>",
            props: ["timestamp"],
          },
        },
      },
    });
  };

  describe("View States", () => {
    it("shows initial view with error", async () => {
      const wrapper = createWrapper();
      const appointmentId = "1";
      wrapper.vm.loadingError = true;
      wrapper.vm.appointmentId = appointmentId;
      await nextTick();

      expect(wrapper.find('[data-test="muc-intro"]').exists()).toBe(true);
      expect(wrapper.find('[data-test="muc-intro"]').attributes('tagline')).toBe(de.appointment);
      expect(wrapper.find('[data-test="muc-intro"]').attributes('title')).toBe(appointmentId);
      expect(wrapper.find('[data-test="error-alert"]').exists()).toBe(true);
      expect(wrapper.findAll('[data-test="error-alert"]')).toHaveLength(1);
      const errorElement = wrapper.find('[data-test="error-alert"]');
      expect(errorElement.find('[data-test="muc-button"]').exists()).toBe(true);
      expect(wrapper.find('[data-test="appointment-detail-header"]').exists()).toBe(false);
    });

    it("shows initial view after loading", async () => {
      const wrapper = createWrapper();
      wrapper.vm.appointment = mockAppointment;
      wrapper.vm.selectedProvider = mockProvider;
      wrapper.vm.loading = false;
      await nextTick();

      expect(wrapper.find('[data-test="appointment-detail-header"]').exists()).toBe(true);
      expect(wrapper.find('[data-test="calendar-icon"]').exists()).toBe(true);

      expect(wrapper.find('.m-linklist__list__item').exists()).toBe(true);
      expect(wrapper.find('.timeBox').exists()).toBe(true);
      const timeboxElement = wrapper.find('.timeBox');
      expect(timeboxElement.text()).toContain(wrapper.vm.formatAppointmentDateTime(mockAppointment.timestamp));

      expect(wrapper.text()).toContain(de.detailTimeHint);

      expect(wrapper.text()).toContain(mockProvider.name);
      expect(wrapper.text()).toContain(mockProvider.address.street + " " + mockProvider.address.house_number);
      expect(wrapper.text()).toContain(mockProvider.address.postal_code + " " + mockProvider.address.city);

      expect(wrapper.find('.m-linklist__list__item').exists()).toBe(true);

      expect(wrapper.find('[data-test="muc-intro"]').exists()).toBe(false);
      expect(wrapper.find('[data-test="error-alert"]').exists()).toBe(false);
    });
  });

  describe("Appointment Variants", () => {
    it("shows telephone-specific appointment information", async () => {
      const wrapper = createWrapper();

      wrapper.vm.appointment = mockAppointment;
      wrapper.vm.selectedProvider = mockProvider;
      wrapper.vm.selectedService = {
        id: mockAppointment.serviceId,
        name: mockAppointment.serviceName,
        maxQuantity: 1,
        parentId: null,
        variantId: 2,
      };
      wrapper.vm.loading = false;

      await nextTick();

      const locationSection = wrapper.find(".location-text-margin-top");

      expect(locationSection.exists()).toBe(true);
      expect(locationSection.text()).toContain(de.appointmentTypes["2"]);
      expect(locationSection.text()).toContain(
        de.appointmentDetailTelephoneLocationText
      );
      expect(locationSection.text()).toContain(mockAppointment.telephone);
      expect(locationSection.text()).toContain(
        de.appointmentDetailTelephonePreparationHint
      );

      expect(locationSection.text()).not.toContain(
        mockProvider.address.street
      );

      expect(
        wrapper
          .find('[data-test="appointment-detail-header"]')
          .attributes("data-variant-id")
      ).toBe("2");
    });

    it("shows video-specific appointment information", async () => {
      const wrapper = createWrapper();

      wrapper.vm.appointment = mockAppointment;
      wrapper.vm.selectedProvider = mockProvider;
      wrapper.vm.selectedService = {
        id: mockAppointment.serviceId,
        name: mockAppointment.serviceName,
        maxQuantity: 1,
        parentId: null,
        variantId: 3,
      };
      wrapper.vm.loading = false;

      await nextTick();

      const locationSection = wrapper.find(".location-text-margin-top");

      expect(locationSection.exists()).toBe(true);
      expect(locationSection.text()).toContain(de.appointmentTypes["3"]);
      expect(locationSection.text()).toContain(
        de.appointmentDetailVideoLocationText
      );
      expect(locationSection.text()).toContain(
        de.appointmentDetailVideoPreparationHint
      );
      expect(locationSection.text()).toContain(
        de.appointmentDetailVideoDelayHint
      );

      expect(locationSection.text()).not.toContain(
        mockProvider.address.street
      );

      expect(
        wrapper
          .find('[data-test="appointment-detail-header"]')
          .attributes("data-variant-id")
      ).toBe("3");

      const infoLink = locationSection.find(
        `a[href="${VIDEO_CONSULTATION_INFO_URL}"]`
      );

      expect(infoLink.exists()).toBe(true);
      expect(infoLink.text()).toBe(
        de.appointmentDetailVideoInfoLink
      );
    });
  });

  describe("Linklist States", () => {
    it("shows one linklist items", async () => {
      const wrapper = createWrapper();
      wrapper.vm.appointment = mockAppointment;
      wrapper.vm.selectedProvider = mockProvider;
      wrapper.vm.loading = false;
      await nextTick();

      expect(wrapper.find('.m-linklist__list__item').exists()).toBe(true);
      expect(wrapper.findAll('.m-linklist__list__item')).toHaveLength(1);

    });

    it("shows three linklist items", async () => {
      const wrapper = createWrapper();
      wrapper.vm.appointment = mockSubRequestAppointment;
      wrapper.vm.loading = false;
      await nextTick();

      expect(wrapper.find('.m-linklist__list__item').exists()).toBe(true);
      expect(wrapper.findAll('.m-linklist__list__item')).toHaveLength(3);

    });
  });
});
