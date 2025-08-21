import {mount} from "@vue/test-utils";
import { afterAll, beforeAll, describe, expect, it, vi } from "vitest";
import { nextTick } from "vue";
// @ts-expect-error: Vue SFC import for test
import de from '@/utils/de-DE.json';
// @ts-expect-error: Vue SFC import for test
import AppointmentOverviewView from "@/components/AppointmentOverview/AppointmentOverviewView.vue";
import AppointmentCard from "../../../src/components/AppointmentOverview/AppointmentCard.vue";
import SkeletonLoader from "../../../src/components/Common/SkeletonLoader.vue";
import ErrorAlert from "../../../src/components/Common/ErrorAlert.vue";

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

  vi.mock("@/api/ZMSAppointmentAPI", async () => {
    const actual = await vi.importActual("@/api/ZMSAppointmentAPI");
    return {
      ...actual,
      confirmAppointment: vi.fn(),
    };
  });

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
        },
      },
    });
  };

  describe("View States", () => {
    it("shows initial view after loading", async () => {
      const wrapper = createWrapper();
      wrapper.vm.loading = false;
      wrapper.vm.appointments = mockAppointments;
      await nextTick();

      expect(wrapper.findAllComponents(AppointmentCard).length).toBe(2);
      expect(wrapper.findAllComponents(SkeletonLoader).length).toBe(0);
      expect(wrapper.findAllComponents(ErrorAlert).length).toBe(0);
    });

    it("shows initial view with skeleton loader", async () => {
      const wrapper = createWrapper();
      await nextTick();
      expect(wrapper.findAllComponents(SkeletonLoader).length).toBe(4);
      expect(wrapper.findAllComponents(AppointmentCard).length).toBe(0);
      expect(wrapper.findAllComponents(ErrorAlert).length).toBe(0);
    });

    it("shows initial view with error", async () => {
      const wrapper = createWrapper();
      wrapper.vm.loadingError = true;
      await nextTick();
      expect(wrapper.findAllComponents(ErrorAlert).length).toBe(1);
      expect(wrapper.findAllComponents(AppointmentCard).length).toBe(0);
      expect(wrapper.findAllComponents(SkeletonLoader).length).toBe(0);
    });
  });

  describe("Edge Cases", () => {
    it("shows initial view after loading with 0 appointments", async () => {
      const wrapper = createWrapper();
      wrapper.vm.loading = false;
      wrapper.vm.appointments = [];
      await nextTick();

      expect(wrapper.findAllComponents(AppointmentCard).length).toBe(0);
      expect(wrapper.findAllComponents(SkeletonLoader).length).toBe(0);
      expect(wrapper.findAllComponents(ErrorAlert).length).toBe(0);
    });
  });
});
