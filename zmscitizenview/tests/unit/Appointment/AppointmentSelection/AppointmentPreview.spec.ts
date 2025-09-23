import { mount } from "@vue/test-utils";
import { describe, it, expect, vi } from "vitest";
// @ts-expect-error: SFC import for test
import AppointmentPreview from "@/components/Appointment/AppointmentSelection/AppointmentPreview.vue";

const t = vi.fn((key: string) => key);

describe("AppointmentPreview", () => {
  it("renders selected service/provider and time summary", () => {
    const wrapper = mount(AppointmentPreview, {
      global: { stubs: { "muc-callout": { template: '<div data-test="muc-callout"><slot name="header"></slot><slot name="content"></slot></div>' } } },
      props: {
        t,
        selectedProvider: { name: "Office A", address: { street: "Elm", house_number: "99" } },
        selectedDay: new Date("2025-06-17"),
        selectedTimeslot: 1750118400,
        selectedService: { id: "service1" },
      },
    });
    expect(wrapper.text()).toContain("Office A");
    expect(wrapper.text()).toContain("Elm");
    expect(wrapper.text()).toContain("99");
    expect(t).toHaveBeenCalledWith("selectedAppointment");
  });

  it("computes and displays estimated duration", () => {
    const wrapper = mount(AppointmentPreview, {
      global: { stubs: { "muc-callout": { template: '<div data-test="muc-callout"><slot name="header"></slot><slot name="content"></slot></div>' } } },
      props: {
        t,
        selectedProvider: { id: 1, name: "Office A", address: { street: "Elm", house_number: "99" } },
        selectedDay: new Date("2025-06-17"),
        selectedTimeslot: 1750118400,
        selectedService: { id: "service1", estimatedDuration: 15 },
      },
    });
    expect(wrapper.text()).toContain("estimatedDuration");
  });

  it("handles selectedProvider = null safely", () => {
    const wrapper = mount(AppointmentPreview, {
      global: { stubs: { "muc-callout": { template: '<div data-test="muc-callout"><slot name="header"></slot><slot name="content"></slot></div>' } } },
      props: {
        t,
        selectedProvider: null,
        selectedDay: new Date("2025-06-17"),
        selectedTimeslot: 1750118400,
        selectedService: { id: "service1" },
      },
    });
    expect(wrapper.exists()).toBe(true);
  });
});