import { mount } from "@vue/test-utils";
import { describe, it, expect, vi } from "vitest";
// @ts-expect-error: SFC import for test
import AppointmentPreview from "@/components/Appointment/AppointmentSelection/AppointmentPreview.vue";

const t = vi.fn((key: string) => key);

const calloutStub = {
  template:
    '<div data-test="muc-callout"><slot name="header"></slot><slot name="content"></slot></div>',
};

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
  it("displays appropriate icon and appointment type for variantId", () => {
    const variants = [
      { id: 2, icon: "icon-telephone", textKey: `appointmentTypes.${2}` },
      { id: 3, icon: "icon-video-camera", textKey: `appointmentTypes.${3}` },
    ];

    variants.forEach(variant => {
      const wrapper = mount(AppointmentPreview, {
        global: { stubs: { "muc-callout": { template: '<div data-test="muc-callout"><slot name="header"></slot><slot name="content"></slot></div>' } } },
        props: {
          t,
          selectedProvider: { name: "Office A", address: { street: "Elm", house_number: "99" } },
          selectedDay: new Date("2025-06-17"),
          selectedTimeslot: 1750118400,
          selectedService: { id: "service1", variantId: variant.id },
        },
      });

      expect(wrapper.find(`use[xlink:href="#${variant.icon}"]`).exists()).toBe(true);
      expect(wrapper.text()).toContain(t(variant.textKey));
      expect(wrapper.text()).not.toContain("Elm");
      expect(wrapper.text()).not.toContain("99");
    });
  });

  it("renders infoForAppointment with exactly one p tag when the provided HTML already contains a p tag", () => {
    const wrapper = mount(AppointmentPreview, {
      global: { stubs: { "muc-callout": calloutStub } },
      props: {
        t,
        selectedProvider: {
          id: 1,
          name: "Office A",
          address: { street: "Elm", house_number: "99" },
          scope: {
            infoForAppointment: "<p>Bitte Unterlagen mitbringen.</p>",
          },
        },
        selectedDay: new Date("2025-06-17"),
        selectedTimeslot: 1750118400,
        selectedService: { id: "service1" },
      },
    });

    const hintHeading = Array.from(
      wrapper.element.querySelectorAll("h3")
    ).find((element) => element.textContent?.trim() === "hint");

    expect(hintHeading).toBeTruthy();

    const hintContent = hintHeading?.nextElementSibling as HTMLElement | null;

    expect(hintContent).not.toBeNull();
    expect(hintContent?.tagName).toBe("DIV");
    expect(hintContent?.children.length).toBe(1);
    expect(hintContent?.firstElementChild?.tagName).toBe("P");
    expect(hintContent?.querySelectorAll("p")).toHaveLength(1);
    expect(hintContent?.textContent).toContain("Bitte Unterlagen mitbringen.");
  });

  it("wraps infoForAppointment in a p tag when the provided HTML does not contain a p tag", () => {
    const wrapper = mount(AppointmentPreview, {
      global: { stubs: { "muc-callout": calloutStub } },
      props: {
        t,
        selectedProvider: {
          id: 1,
          name: "Office A",
          address: { street: "Elm", house_number: "99" },
          scope: {
            infoForAppointment: "Bitte Unterlagen mitbringen.",
          },
        },
        selectedDay: new Date("2025-06-17"),
        selectedTimeslot: 1750118400,
        selectedService: { id: "service1" },
      },
    });

    const hintHeading = Array.from(
      wrapper.element.querySelectorAll("h3")
    ).find((element) => element.textContent?.trim() === "hint");

    expect(hintHeading).toBeTruthy();

    const hintContent = hintHeading?.nextElementSibling as HTMLElement | null;

    expect(hintContent).not.toBeNull();
    expect(hintContent?.tagName).toBe("P");
    expect(hintContent?.querySelectorAll("p")).toHaveLength(0);
    expect(hintContent?.textContent).toContain("Bitte Unterlagen mitbringen.");
  });
});
