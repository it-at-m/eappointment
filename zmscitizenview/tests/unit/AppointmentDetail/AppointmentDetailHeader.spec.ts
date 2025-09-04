import { mount } from "@vue/test-utils";
import { describe, expect, it } from "vitest";
// @ts-expect-error: Vue SFC import for test
import de from '@/utils/de-DE.json';
import { nextTick } from "vue";

// @ts-expect-error: Vue SFC import for test
import AppointmentDetailHeader from "@/components/AppointmentDetail/AppointmentDetailHeader.vue";

describe("AppointmentDetailHeader", () => {
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

  const mockProvider = {
    id: "1",
    name: "Rathaus Marienplatz",
    address: {
      street: "Marienplatz",
      house_number: "8"
    },
  };

  const createWrapper = (props = {}) => {
    return mount(AppointmentDetailHeader, {
      props: {
        appointment: mockAppointment,
        selectedProvider: mockProvider,
        t: (key: string) => {
          const translations = de as any;
          return translations[key] || key;
        },
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
          'muc-button': {
            template: "<div data-test='muc-button'></div>",
            props: ["icon", "variant"],
          },
        },
      },
    });
  };

  it("renders header", async () => {
    const wrapper = createWrapper();
    await nextTick();

    expect(wrapper.find('[data-test="muc-intro"]').exists()).toBe(true);
    expect(wrapper.find('[data-test="muc-intro"]').attributes('tagline')).toBe(de.appointment);
    expect(wrapper.find('[data-test="muc-intro"]').attributes('title')).toBe(wrapper.vm.formatMultilineTitle(mockAppointment));
    expect(wrapper.find('.multiline-text').exists()).toBe(true);
    expect(wrapper.find('[data-test="muc-button"]').exists()).toBe(true);
    expect(wrapper.findAll('[data-test="muc-button"]')).toHaveLength(2);
    expect(wrapper.text()).toContain(mockAppointment.processId);
    expect(wrapper.text()).toContain(wrapper.vm.formatAppointmentDateTime(mockAppointment.timestamp));
    expect(wrapper.text()).toContain(mockProvider.address.street);
    expect(wrapper.text()).toContain(mockProvider.address.house_number);
  });

  it("renders header without provider", async () => {
    const wrapper = createWrapper({selectedProvider: undefined});
    await nextTick();

    expect(wrapper.find('[data-test="muc-intro"]').exists()).toBe(true);
    expect(wrapper.find('[data-test="muc-intro"]').attributes('tagline')).toBe(de.appointment);
    expect(wrapper.find('[data-test="muc-intro"]').attributes('title')).toBe(wrapper.vm.formatMultilineTitle(mockAppointment));
    expect(wrapper.find('.multiline-text').exists()).toBe(true);
    expect(wrapper.find('[data-test="muc-button"]').exists()).toBe(true);
    expect(wrapper.findAll('[data-test="muc-button"]')).toHaveLength(2);
    expect(wrapper.text()).toContain(mockAppointment.processId);
    expect(wrapper.text()).toContain(wrapper.vm.formatAppointmentDateTime(mockAppointment.timestamp));
    expect(wrapper.text()).not.toContain(mockProvider.address.street);
  });

  it("renders header without appointment", async () => {
    const wrapper = createWrapper({appointment: undefined});
    await nextTick();
    expect(wrapper.find('[data-test="muc-intro"]').exists()).toBe(false);
    expect(wrapper.find('.multiline-text').exists()).toBe(false);
    expect(wrapper.find('[data-test="muc-button"]').exists()).toBe(false);
  });
});
