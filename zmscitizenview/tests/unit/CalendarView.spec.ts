import { describe, it, expect, beforeEach, vi } from "vitest";
import { mount } from "@vue/test-utils";
import CalendarView from "/src/components/Appointment/CalendarView.vue";
import { ref, nextTick } from "vue";
import {
  fetchAvailableDays,
  fetchAvailableTimeSlots,
} from "@/api/ZMSAppointmentAPI";

const t = vi.fn((key: string) => key);

const baseProps = {
  baseUrl: "http://test.url",
  isRebooking: false,
  exclusiveLocation: undefined,
  preselectedOfficeId: undefined,
  selectedServiceMap: new Map([["service1", 1]]),
  captchaToken: "test-token",
  bookingError: false,
  bookingErrorKey: "noAppointmentsAvailable",
  t,
};

vi.mock('@/api/ZMSAppointmentAPI', () => ({
  fetchAvailableDays: vi.fn(),
  fetchAvailableTimeSlots: vi.fn(),
}));


const createWrapper = (overrides = {}) => {
  return mount(CalendarView, {
    global: {
      provide: {
        selectedServiceProvider: {
          selectedService: ref(overrides.selectedService),
        },
        selectedTimeslot: {
          selectedProvider: ref(overrides.selectedProvider ?? null),
          selectedTimeslot: ref(overrides.selectedTimeslot ?? 0),
        },
        selectableProviders: ref([])
      },
      stubs: ["muc-slider", "muc-callout", "muc-calendar"],
    },
    props: {
      ...baseProps,
      ...overrides.props,
    },
  });
};

describe("CalendarView", () => {
  (fetchAvailableDays as vi.Mock).mockResolvedValue({
    availableDays: [
      {
        time: '2025-05-14',
        providerIDs: '102522,54261,10489'
      },
      {
        time: '2025-05-15',
        providerIDs: '102522,54261,10489'
      }]
  });

  (fetchAvailableTimeSlots as vi.Mock).mockResolvedValue({
    offices: [
      {
        officeId: 102522,
        appointments: [1747202400, 1747223100, 1747223400, 1747223700, 1747224000, 1747224300]
      },
      {
        officeId: 54261,
        appointments: [1747223100, 1747223400, 1747223700, 1747224000, 1747224300]
      },
      {
        officeId: 10489,
        appointments: [1747223100, 1747223400, 1747223700, 1747224000, 1747224300]
      }
    ]
  });

  it("renders nothing if no provider is selected", () => {
    const wrapper = createWrapper();
    expect(wrapper.html()).not.toContain("location");
  });
});
