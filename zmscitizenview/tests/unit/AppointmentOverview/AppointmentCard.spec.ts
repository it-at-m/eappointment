import { mount } from "@vue/test-utils";
import { describe, expect, it } from "vitest";
import { nextTick } from "vue";
// @ts-expect-error: Vue SFC import for test
import de from '@/utils/de-DE.json';
// @ts-expect-error: Vue SFC import for test
import AppointmentCard from "@/components/AppointmentOverview/AppointmentCard.vue";
import {MucCard} from "@muenchen/muc-patternlab-vue";

describe("AppointmentCard", () => {
  const mockAppointmentDetailUrl = "https://www.muenchen.de/appointment-detail";

  const mockAppointment =
    {
      timestamp: Math.floor(Date.now() / 1000),
      familyName: "John Doe",
      email: "john@example.com",
      telephone: "1234567890",
    };

  const mockOffices = [
    {
      id: "1",
      name: "Rathaus Marienplatz",
      address: "Marienplatz 8, 80331 München",
    },
    {
      id: "2",
      name: "Bürgerbüro Pasing",
      address: "Landsberger Str. 486, 81241 München",
    },
  ];

  const createWrapper = (props = {}) => {
    return mount(AppointmentCard, {
      props: {
        appointment: mockAppointment,
        appointmentDetailUrl: mockAppointmentDetailUrl,
        offices: mockOffices,
        t: (key: string) => {
          const translations = de as any;
          return translations[key] || key;
        },

        ...props,
      },
      global: {
        stubs: {
          'muc-icon': {
            template: "<div data-test='muc-icon'></div>",
            props: ["icon"],
          },
          'muc-card': {
            template: "<div data-test='muc-card'></div>",
            props: ["tagline", "title"],
          },
        },
      },
    });
  };

  it("renders appointment card with correct details", async () => {
    const wrapper = createWrapper();
    await nextTick();
    expect(wrapper.findAllComponents(MucCard).length).toBe(1);
  });
});
