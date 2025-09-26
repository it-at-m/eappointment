import {mount} from "@vue/test-utils";
import { afterAll, beforeAll, describe, expect, it, vi } from "vitest";
import { nextTick } from "vue";
// @ts-expect-error: Vue SFC import for test
import de from '@/utils/de-DE.json';
// @ts-expect-error: Vue SFC import for test
import AppointmentOverviewView from "@/components/AppointmentOverview/AppointmentOverviewView.vue";

globalThis.scrollTo = vi.fn();

describe("AppointmentOverviewView", () => {

  beforeAll(() => {
    vi.stubGlobal("fetch", vi.fn().mockResolvedValue({
      status: 200,
      json: async () => ({
        offices: [],
      }),
    }));
  });

  afterAll(() => {
    vi.unstubAllGlobals();
  })

  const mockBaseUrl = "https://www.muenchen.de";
  const mockAppointmentDetailUrl = "https://www.muenchen.de/appointment-detail";
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
    }
  ];

  const createWrapper = (props = {}) => {
    return mount(AppointmentOverviewView, {
      props: {
        baseUrl: mockBaseUrl,
        appointmentDetailUrl: mockAppointmentDetailUrl,
        newAppointmentUrl: mockNewAppointmentUrl,
        overviewUrl: mockOverviewUrl,
        t: (key: string) => {
          const translations = de as any;
          return translations[key] || key;
        },

        ...props,
      },
      global: {
        stubs: {
          'appointment-card': {
            template: "<div data-test='appointment-card'></div>",
            props: ["appointment", "appointmentDetailUrl", "offices", "t"],
          },
          'error-alert': {
            template: "<div data-test='error-alert'></div>",
            props: ["message", "header"],
          },
          'skeleton-loader': {
            template: "<div data-test='skeleton-loader'></div>",
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

      expect(wrapper.find('[data-test="appointment-card"]').exists()).toBe(true);
      expect(wrapper.findAll('[data-test="appointment-card"]')).toHaveLength(2);
      expect(wrapper.find('[data-test="error-alert"]').exists()).toBe(false);
      expect(wrapper.find('[data-test="skeleton-loader"]').exists()).toBe(false);
    });

    it("shows initial view with skeleton loader", async () => {
      const wrapper = createWrapper();
      await nextTick();
      expect(wrapper.find('[data-test="appointment-card"]').exists()).toBe(false);
      expect(wrapper.find('[data-test="error-alert"]').exists()).toBe(false);
      expect(wrapper.find('[data-test="skeleton-loader"]').exists()).toBe(true);
      expect(wrapper.findAll('[data-test="skeleton-loader"]')).toHaveLength(4);
    });

    it("shows initial view with error", async () => {
      const wrapper = createWrapper();
      wrapper.vm.loadingError = true;
      await nextTick();
      expect(wrapper.find('[data-test="appointment-card"]').exists()).toBe(false);
      expect(wrapper.find('[data-test="error-alert"]').exists()).toBe(true);
      expect(wrapper.findAll('[data-test="error-alert"]')).toHaveLength(1);
      expect(wrapper.find('[data-test="skeleton-loader"]').exists()).toBe(false);
    });
  });

  describe("Edge Cases", () => {
    beforeAll(() => {
      vi.mock('@/utils/auth', () => ({
        isAuthenticated: () => true,
        getAccessToken: () => ""
      }));
    });
    it("shows initial view after loading with 0 appointments", async () => {
      const wrapper = createWrapper();
      wrapper.vm.loading = false;
      wrapper.vm.appointments = [];
      await nextTick();

      expect(wrapper.find('[data-test="appointment-card"]').exists()).toBe(false);
      expect(wrapper.findAll('[data-test="appointment-card"]')).toHaveLength(0);
      expect(wrapper.find('[data-test="error-alert"]').exists()).toBe(false);
      expect(wrapper.find('[data-test="skeleton-loader"]').exists()).toBe(false);
    });
  });
});
