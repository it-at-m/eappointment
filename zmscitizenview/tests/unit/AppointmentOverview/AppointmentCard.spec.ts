import { mount } from "@vue/test-utils";
import { describe, expect, it } from "vitest";
import { nextTick } from "vue";
import de from '@/utils/de-DE.json';
import AppointmentCard from "@/components/AppointmentOverview/AppointmentCard.vue";

describe("AppointmentCard", () => {
  const translate = (key: string): string => {
    const value = key.split(".").reduce<unknown>((current, part) => {
      if (typeof current !== "object" || current === null) {
        return undefined;
      }

      return (current as Record<string, unknown>)[part];
    }, de);

    return typeof value === "string" ? value : key;
  };

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

  const mockProvider =
    {
      id: "1",
      name: "Rathaus Marienplatz",
      address: {
        street: "Marienplatz",
        house_number: "8"
      },
    };

  const createMockService = (
    id: string,
    variantId: number | null
  ) => ({
    id,
    name: "Personalausweis",
    maxQuantity: 1,
    parentId: null,
    variantId,
  });

  const createWrapper = (props = {}, appointment: any) => {
    return mount(AppointmentCard, {
      props: {
        appointment: appointment,
        appointmentDetailUrl: mockAppointmentDetailUrl,
        offices: [],
        services: [],
        t: translate,
        ...props,
      },
      global: {
        stubs: {
          'muc-icon': {
            template:
              "<span data-test='muc-icon' :data-icon='icon'></span>",
            props: ["icon"],
          },
          'muc-card': {
            template: `
              <div data-test='muc-card' :tagline="tagline" :title="title">
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
    const wrapper = createWrapper({offices: [mockProvider]}, mockAppointment);
    await nextTick();

    expect(wrapper.find('[data-test="muc-card"]').exists()).toBe(true);
    expect(wrapper.find('[data-test="muc-card"]').attributes('tagline')).toBe(de.appointmentTypes["1"]);
    expect(wrapper.find('[data-test="muc-card"]').attributes('title')).toBe(wrapper.vm.formatMultilineTitle(mockAppointment));
    expect(wrapper.find('.multiline-text').exists()).toBe(true);
    expect(wrapper.text()).toContain(mockProvider.address.street);
    expect(wrapper.text()).toContain(mockProvider.address.house_number);
  });

  it("renders appointment card with two services", async () => {
    const wrapper = createWrapper({offices: [mockProvider]}, mockAppointmentSubServices);
    await nextTick();

    expect(wrapper.find('[data-test="muc-card"]').exists()).toBe(true);
    expect(wrapper.find('[data-test="muc-card"]').attributes('tagline')).toBe(de.appointmentTypes["1"]);
    expect(wrapper.find('[data-test="muc-card"]').attributes('title')).toBe(wrapper.vm.formatMultilineTitle(mockAppointmentSubServices));
    expect(wrapper.find('.multiline-text').exists()).toBe(true);
    expect(wrapper.text()).toContain(mockProvider.address.street);
    expect(wrapper.text()).toContain(mockProvider.address.house_number);
  });

  it("renders telephone appointment card", async () => {
    const appointment = {
      ...mockAppointment,
      serviceId: "telephone-service",
    };

    const wrapper = createWrapper(
      {
        offices: [mockProvider],
        services: [createMockService("telephone-service", 2)],
      },
      appointment
    );

    await nextTick();

    expect(
      wrapper.find('[data-test="muc-card"]').attributes("tagline")
    ).toBe(de.appointmentTypes["2"]);

    const location = wrapper.find(
      '[data-test="appointment-location"]'
    );

    expect(
      location.find('[data-test="muc-icon"]').attributes("data-icon")
    ).toBe("telephone");

    expect(location.text()).toContain(mockAppointment.telephone);
    expect(location.text()).not.toContain(mockProvider.address.street);
  });

  it("renders video appointment card", async () => {
    const appointment = {
      ...mockAppointment,
      serviceId: "video-service",
    };

    const wrapper = createWrapper(
      {
        offices: [mockProvider],
        services: [createMockService("video-service", 3)],
      },
      appointment
    );

    await nextTick();

    expect(
      wrapper.find('[data-test="muc-card"]').attributes("tagline")
    ).toBe(de.appointmentTypes["3"]);

    const location = wrapper.find(
      '[data-test="appointment-location"]'
    );

    expect(
      location.find('[data-test="muc-icon"]').attributes("data-icon")
    ).toBe("video-camera");

    expect(location.text()).toContain(
      de.appointmentDetailVideoIntroLocation
    );

    expect(location.text()).not.toContain(mockProvider.address.street);
  });

  it.each([null, 1, 4, 5, 6, 7])(
    "renders variant %s as presence appointment",
    async (variantId) => {
      const serviceId = `service-${variantId}`;

      const appointment = {
        ...mockAppointment,
        serviceId,
      };

      const wrapper = createWrapper(
        {
          offices: [mockProvider],
          services: [createMockService(serviceId, variantId)],
        },
        appointment
      );

      await nextTick();

      expect(
        wrapper.find('[data-test="muc-card"]').attributes("tagline")
      ).toBe(de.appointmentTypes["1"]);

      const location = wrapper.find(
        '[data-test="appointment-location"]'
      );

      expect(
        location.find('[data-test="muc-icon"]').attributes("data-icon")
      ).toBe("map-pin");

      expect(location.text()).toContain(mockProvider.address.street);
      expect(location.text()).toContain(
        mockProvider.address.house_number
      );
    }
  );
});
