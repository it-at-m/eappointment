import { mount } from "@vue/test-utils";
import { describe, expect, it } from "vitest";
import { nextTick } from "vue";
// @ts-expect-error: Vue SFC import for test
import de from '@/utils/de-DE.json';
// @ts-expect-error: Vue SFC import for test
import AppointmentCard from "@/components/AppointmentOverview/AppointmentCard.vue";

describe("AppointmentCard", () => {
  const mockAppointmentDetailUrl = "https://www.muenchen.de/appointment-detail";

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

  const mockAppointmentSubServices =
      {
        timestamp: Math.floor(Date.now() / 1000),
        processId: "12345",
        familyName: "John Doe",
        email: "john@example.com",
        officeId: "1",
        telephone: "1234567890",
        serviceId: "id_12345",
        serviceName: "Personalausweis",
        serviceCount: 2,
        subRequestCounts: [
          {
            id: "2",
            name: "Reisepass",
            count: 2,
          },
        ],
      };

  const mockProvider = {
      id: "1",
      name: "Rathaus Marienplatz",
      address: {
        street: "Marienplatz",
        house_number: "8"
      },
    };

  const createWrapper = (props = {}, appointment: any) => {
    return mount(AppointmentCard, {
      props: {
        appointment: appointment,
        appointmentDetailUrl: mockAppointmentDetailUrl,
        offices: [],
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
            template: `
              <div data-test='muc-card':tagline="tagline" :title="title">
                <slot name="headerPrefix"></slot>
                <slot name="content"></slot>
              </div>`,
            props: ["tagline", "title"],
          },
        },
      },
    });
  };

  it("renders appointment card with one service", async () => {
    const wrapper = createWrapper({}, mockAppointment);
    wrapper.vm.selectedProvider = mockProvider;
    await nextTick();

    expect(wrapper.find('[data-test="muc-card"]').exists()).toBe(true);
    expect(wrapper.find('[data-test="muc-card"]').attributes('tagline')).toBe('Termin');
    expect(wrapper.find('[data-test="muc-card"]').attributes('title')).toBe(wrapper.vm.formatTitle(mockAppointment));
    expect(wrapper.find('.multiline-text').exists()).toBe(true);
    expect(wrapper.text()).toContain(mockProvider.address.street);
    expect(wrapper.text()).toContain(mockProvider.address.house_number);
  });

  it("renders appointment card with two services", async () => {
    const wrapper = createWrapper({}, mockAppointmentSubServices);
    wrapper.vm.selectedProvider = mockProvider;
    await nextTick();

    expect(wrapper.find('[data-test="muc-card"]').exists()).toBe(true);
    expect(wrapper.find('[data-test="muc-card"]').attributes('tagline')).toBe('Termin');
    expect(wrapper.find('[data-test="muc-card"]').attributes('title')).toBe(wrapper.vm.formatTitle(mockAppointmentSubServices));
    expect(wrapper.find('.multiline-text').exists()).toBe(true);
    expect(wrapper.text()).toContain(mockProvider.address.street);
    expect(wrapper.text()).toContain(mockProvider.address.house_number);
  });
});
