import { mount } from "@vue/test-utils";
import { describe, expect, it } from "vitest";
import de from '@/utils/de-DE.json';
import { nextTick } from "vue";

import AppointmentDetailHeader from "@/components/AppointmentDetail/AppointmentDetailHeader.vue";

describe("AppointmentDetailHeader", () => {
  const translate = (key: string): string => {
    const value = key.split(".").reduce<unknown>((current, part) => {
      if (typeof current !== "object" || current === null) {
        return undefined;
      }

      return (current as Record<string, unknown>)[part];
    }, de);

    return typeof value === "string" ? value : key;
  };

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
        variantId: null,
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
          'muc-button': {
            template: "<div data-test='muc-button'></div>",
            props: ["icon", "variant"],
          },
          "muc-link": {
            template: `
              <a
                data-test="muc-link"
                :data-label="label"
                :data-prepend-icon="prependIcon"
              >
                {{ label }}
              </a>
            `,
            props: ["id", "label", "prependIcon", "ariaLabel"],
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
    expect(wrapper.find('[data-test="muc-button"]').exists()).toBe(true);
    expect(wrapper.findAll('[data-test="muc-button"]')).toHaveLength(2);
    expect(wrapper.text()).toContain(mockAppointment.processId);
    expect(wrapper.text()).toContain(wrapper.vm.formatAppointmentDateTime(mockAppointment.timestamp));
    expect(wrapper.text()).toContain(mockProvider.address.street);
    expect(wrapper.text()).toContain(mockProvider.address.house_number);
  });

  it("renders telephone appointment header", async () => {
    const wrapper = createWrapper({ variantId: 2 });
    await nextTick();

    expect(
      wrapper.find('[data-test="muc-intro"]').attributes("tagline")
    ).toBe(de.appointmentTypes["2"]);

    const locationLink = wrapper.find(
      '[data-test="muc-link"][data-prepend-icon="map-pin"]'
    );

    expect(locationLink.exists()).toBe(true);
    expect(locationLink.attributes("data-label")).toBe(
      mockAppointment.telephone
    );
  });

  it("renders video appointment header", async () => {
    const wrapper = createWrapper({ variantId: 3 });
    await nextTick();

    expect(
      wrapper.find('[data-test="muc-intro"]').attributes("tagline")
    ).toBe(de.appointmentTypes["3"]);

    const locationLink = wrapper.find(
      '[data-test="muc-link"][data-prepend-icon="map-pin"]'
    );

    expect(locationLink.exists()).toBe(true);
    expect(locationLink.attributes("data-label")).toBe(
      de.appointmentDetailVideoIntroLocation
    );
  });

  it("renders header without provider", async () => {
    const wrapper = createWrapper({selectedProvider: undefined});
    await nextTick();

    expect(wrapper.find('[data-test="muc-intro"]').exists()).toBe(true);
    expect(wrapper.find('[data-test="muc-intro"]').attributes('tagline')).toBe(de.appointment);
    expect(wrapper.find('[data-test="muc-intro"]').attributes('title')).toBe(wrapper.vm.formatMultilineTitle(mockAppointment));
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
