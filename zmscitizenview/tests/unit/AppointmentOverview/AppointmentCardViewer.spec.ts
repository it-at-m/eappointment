import {mount} from "@vue/test-utils";
import { describe, expect, it, vi } from "vitest";
import { nextTick } from "vue";
// @ts-expect-error: Vue SFC import for test
import de from '@/utils/de-DE.json';
// @ts-expect-error: Vue SFC import for test
import AppointmentCardViewer from "@/components/AppointmentOverview/AppointmentCardViewer.vue";

globalThis.scrollTo = vi.fn();

describe("AppointmentCardViewer", () => {

  const mockAppointmentDetailUrl = "https://www.muenchen.de/appointment-detail";
  const mockNewAppointmentUrl = "https://www.muenchen.de/new-appointment";

  const mockAppointments = [
    {
      timestamp: Math.floor(Date.now() / 1000),
      familyName: "John Doe",
      email: "john@example.com",
      telephone: "1234567890",
    },
    {
      timestamp: Math.floor((Date.now() + 24 * 60 * 60 * 1000) / 1000),
      familyName: "John Doe",
      email: "john@example.com",
      telephone: "1234567890",
    }
  ];

  const mockManyAppointments = [
    {
      timestamp: Math.floor(Date.now() / 1000),
      familyName: "John Doe",
      email: "john@example.com",
      telephone: "1234567890",
    },
    {
      timestamp: Math.floor((Date.now() + 24 * 60 * 60 * 1000) / 1000),
      familyName: "John Doe",
      email: "john@example.com",
      telephone: "1234567890",
    },
    {
      timestamp: Math.floor((Date.now() + 2 * 60 * 60 * 1000) / 1000),
      familyName: "John Doe",
      email: "john@example.com",
      telephone: "1234567890",
    },
    {
      timestamp: Math.floor((Date.now() + 2 * 24 * 60 * 60 * 1000) / 1000),
      familyName: "John Doe",
      email: "john@example.com",
      telephone: "1234567890",
    }
  ];
  const createWrapper = (props = {}) => {
    return mount(AppointmentCardViewer, {
      props: {
        allAppointments: [],
        isMobile: false,
        newAppointmentUrl: mockNewAppointmentUrl,
        appointmentDetailUrl: mockAppointmentDetailUrl,
        displayedOnDetailScreen: false,
        offices: [],
        t: (key: string) => {
          const translations = de as any;
          return translations[key] || key;
        },

        ...props,
      },
      global: {
        stubs: {
          'muc-card-container': {
            template: "<div data-test='muc-card-container'><slot></slot></div>",
            props: [
              "title",
              "newAppointmentUrl",
              "t"
            ],
          },
          'appointment-card': {
            template: "<div data-test='appointment-card'></div>",
            props: ["appointment", "appointmentDetailUrl", "offices", "t"],
          },
          'add-appointment-card': {
            template: "<div data-test='add-appointment-card'><slot name='content'></slot></div>",
            props: [
              "title",
              "newAppointmentUrl",
              "t"
            ],
          },
          'muc-slider': {
            template: "<div data-test='muc-slider'><slot></slot></div>",
            props: ["message", "header"],
          },
          'muc-slider-item': {
            template: "<div data-test='muc-slider-item'><slot></slot></div>",
          },
        },
      },
    });
  };

  describe("View States", () => {
    it("shows view with no appointments", async () => {
      const wrapper = createWrapper();
      await nextTick();

      expect(wrapper.find('[data-test="add-appointment-card"]').exists()).toBe(true);
      expect(wrapper.findAll('[data-test="add-appointment-card"]')).toHaveLength(1);
      expect(wrapper.find('[data-test="appointment-card"]').exists()).toBe(false);
      expect(wrapper.find('[data-test="muc-slider-item"]').exists()).toBe(false);
    });

    it("shows mobile view with appointments < 3", async () => {
      const wrapper = createWrapper({allAppointments: mockAppointments, isMobile: true});
      await nextTick();
      expect(wrapper.find('[data-test="add-appointment-card"]').exists()).toBe(true);
      expect(wrapper.findAll('[data-test="add-appointment-card"]')).toHaveLength(1);
      expect(wrapper.find('[data-test="appointment-card"]').exists()).toBe(true);
      expect(wrapper.findAll('[data-test="appointment-card"]')).toHaveLength(2);
      expect(wrapper.find('[data-test="muc-slider-item"]').exists()).toBe(true);
      expect(wrapper.findAll('[data-test="muc-slider-item"]')).toHaveLength(3);
      expect(wrapper.find('[data-test="muc-card-container"]').exists()).toBe(false);
    });

    it("shows mobile view with appointments > 3", async () => {
      const wrapper = createWrapper({allAppointments: mockManyAppointments, isMobile: true});
      await nextTick();
      expect(wrapper.find('[data-test="add-appointment-card"]').exists()).toBe(false);
      expect(wrapper.find('[data-test="appointment-card"]').exists()).toBe(true);
      expect(wrapper.findAll('[data-test="appointment-card"]')).toHaveLength(3);
      expect(wrapper.find('[data-test="muc-slider-item"]').exists()).toBe(true);
      expect(wrapper.findAll('[data-test="muc-slider-item"]')).toHaveLength(3);
      expect(wrapper.find('[data-test="muc-card-container"]').exists()).toBe(false);
    });

    it("shows desktop view with appointments < 3", async () => {
      const wrapper = createWrapper({allAppointments: mockAppointments});
      await nextTick();
      expect(wrapper.find('[data-test="add-appointment-card"]').exists()).toBe(true);
      expect(wrapper.findAll('[data-test="add-appointment-card"]')).toHaveLength(1);
      expect(wrapper.find('[data-test="appointment-card"]').exists()).toBe(true);
      expect(wrapper.findAll('[data-test="appointment-card"]')).toHaveLength(2);
      expect(wrapper.find('[data-test="muc-slider-item"]').exists()).toBe(false);
      expect(wrapper.find('[data-test="muc-card-container"]').exists()).toBe(true);
    });

    it("shows desktop view with appointments > 3", async () => {
      const wrapper = createWrapper({allAppointments: mockManyAppointments});
      await nextTick();
      expect(wrapper.find('[data-test="add-appointment-card"]').exists()).toBe(false);
      expect(wrapper.find('[data-test="appointment-card"]').exists()).toBe(true);
      expect(wrapper.findAll('[data-test="appointment-card"]')).toHaveLength(3);
      expect(wrapper.find('[data-test="muc-slider-item"]').exists()).toBe(false);
      expect(wrapper.find('[data-test="muc-card-container"]').exists()).toBe(true);
    });
  });
});
