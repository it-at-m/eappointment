import {mount} from "@vue/test-utils";
import { afterAll, beforeAll, describe, expect, it, vi } from "vitest";
import { nextTick } from "vue";
// @ts-expect-error: Vue SFC import for test
import de from '@/utils/de-DE.json';
// @ts-expect-error: Vue SFC import for test
import AppointmentSliderView from "@/components/AppointmentOverview/AppointmentSliderView.vue";

globalThis.scrollTo = vi.fn();

describe("AppointmentOverviewView", () => {

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
  })

  const mockBaseUrl = "https://www.muenchen.de";
  const mockAppointmentDetailUrl = "https://www.muenchen.de/appointment-detail";
  const mockAppointmentOverviewUrl = "https://www.muenchen.de/appointment-overview";
  const mockNewAppointmentUrl = "https://www.muenchen.de/new-appointment";
  const mockOverviewUrl = "https://www.muenchen.de/overview";

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
    },
    {
      timestamp: Math.floor((Date.now() + 2 * 60 * 60 * 1000) / 1000),
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
    return mount(AppointmentSliderView, {
      props: {
        baseUrl: mockBaseUrl,
        appointmentDetailUrl: mockAppointmentDetailUrl,
        appointmentOverviewUrl: mockAppointmentOverviewUrl,
        newAppointmentUrl: mockNewAppointmentUrl,
        overviewUrl: mockOverviewUrl,
        displayedOnDetailScreen: false,
        t: (key: string) => {
          const translations = de as any;
          return translations[key] || key;
        },

        ...props,
      },
      global: {
        stubs: {
          'appointment-card-viewer': {
            template: "<div data-test='appointment-card-viewer'></div>",
            props: [
              "allAppointments",
              "isMobile",
              "newAppointmentUrl",
              "appointmentDetailUrl",
              "displayedOnDetailScreen",
              "offices",
              "t"
            ],
          },
          'error-alert': {
            template: "<div data-test='error-alert'></div>",
            props: ["message", "header"],
          },
          'skeleton-loader': {
            template: "<div data-test='skeleton-loader'></div>",
          },
          'muc-link': {
            template: "<div data-test='muc-link'></div>",
            props: ["label", "icon", "target", "href"],
          },
        },
      },
    });
  };

  describe("View States", () => {
    beforeAll(() => {
      vi.mock('@/utils/auth', () => ({
        isAuthenticated: () => true,
        getAccessToken: () => ""
      }));
    });

    it("shows initial view after loading", async () => {
      const wrapper = createWrapper();
      wrapper.vm.loading = false;
      wrapper.vm.appointments = mockAppointments;
      await nextTick();

      expect(wrapper.find('[data-test="appointment-card-viewer"]').exists()).toBe(true);
      expect(wrapper.findAll('[data-test="appointment-card-viewer"]')).toHaveLength(1);
      expect(wrapper.find('[data-test="error-alert"]').exists()).toBe(false);
      expect(wrapper.find('[data-test="skeleton-loader"]').exists()).toBe(false);
    });

    it("shows initial view with skeleton loader", async () => {
      const wrapper = createWrapper();
      await nextTick();
      expect(wrapper.find('[data-test="appointment-card-viewer"]').exists()).toBe(false);
      expect(wrapper.find('[data-test="error-alert"]').exists()).toBe(false);
      expect(wrapper.find('[data-test="skeleton-loader"]').exists()).toBe(true);
      expect(wrapper.findAll('[data-test="skeleton-loader"]')).toHaveLength(1);
    });

    it("shows initial view with error", async () => {
      const wrapper = createWrapper();
      wrapper.vm.loadingError = true;
      await nextTick();
      expect(wrapper.find('[data-test="appointment-card-viewer"]').exists()).toBe(false);
      expect(wrapper.find('[data-test="error-alert"]').exists()).toBe(true);
      expect(wrapper.findAll('[data-test="error-alert"]')).toHaveLength(1);
      expect(wrapper.find('[data-test="skeleton-loader"]').exists()).toBe(false);
    });
  });

  describe("Display screen Cases", () => {
    beforeAll(() => {
      vi.mock('@/utils/auth', () => ({
        isAuthenticated: () => true,
        getAccessToken: () => ""
      }));
    });

    it("shows initial view on overview page after loading", async () => {
      const wrapper = createWrapper();
      wrapper.vm.loading = false;
      wrapper.vm.appointments = mockAppointments;
      await nextTick();

      expect(wrapper.text()).toContain(de.myAppointments + " (" + mockAppointments.length + ")");
    });

    it("shows initial view on detail page after loading", async () => {
      const wrapper = createWrapper({displayedOnDetailScreen: true});
      wrapper.vm.loading = false;
      wrapper.vm.appointments = mockAppointments;
      await nextTick();

      expect(wrapper.text()).toContain(de.myFurtherAppointments);
      expect(wrapper.text()).not.toContain(de.myAppointments + " (" + mockAppointments.length + ")");
    });
  });

  describe("Display link Cases", () => {
    beforeAll(() => {
      vi.mock('@/utils/auth', () => ({
        isAuthenticated: () => true,
        getAccessToken: () => ""
      }));
    });
    it("shows link in header", async () => {
      const wrapper = createWrapper();
      wrapper.vm.loading = false;
      wrapper.vm.appointments = mockManyAppointments;

      await nextTick();

      const headerElement = wrapper.find('.header');
      expect(headerElement.find('[data-test="muc-link"]').exists()).toBe(true);
      expect(wrapper.findAll('[data-test="muc-link"]')).toHaveLength(1);
    });

    it("shows link after appointments", async () => {
      const wrapper = createWrapper({displayedOnDetailScreen: true});
      wrapper.vm.loading = false;
      wrapper.vm.appointments = mockManyAppointments;
      wrapper.vm.isMobile = true;

      await nextTick();

      const headerElement = wrapper.find('.header');
      expect(headerElement.find('[data-test="muc-link"]').exists()).toBe(false);
      expect(wrapper.findAll('[data-test="muc-link"]')).toHaveLength(1);
    });

    it("shows no link in header", async () => {
      const wrapper = createWrapper({displayedOnDetailScreen: true});
      wrapper.vm.loading = false;
      wrapper.vm.appointments = mockAppointments;
      wrapper.vm.isMobile = true;

      await nextTick();

      expect(wrapper.findAll('[data-test="muc-link"]')).toHaveLength(0);
    });

    it("shows no link after appointments", async () => {
      const wrapper = createWrapper({displayedOnDetailScreen: true});
      wrapper.vm.loading = false;
      wrapper.vm.appointments = [];
      wrapper.vm.isMobile = true;

      await nextTick();

      expect(wrapper.findAll('[data-test="muc-link"]')).toHaveLength(0);
    });
  });
});
