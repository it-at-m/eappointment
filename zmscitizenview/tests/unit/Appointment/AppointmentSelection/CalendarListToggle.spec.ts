import { mount } from "@vue/test-utils";
import { describe, expect, it, vi } from "vitest";

import CalendarListToggle from "@/components/Appointment/AppointmentSelection/CalendarListToggle.vue";

const t = vi.fn((key: string) => key);

describe("CalendarListToggle", () => {
  const mountToggle = () => {
    const wrapper = mount(CalendarListToggle, {
      props: {
        t,
        isListView: false,
        "onUpdate:isListView": (value: boolean) =>
          wrapper.setProps({ isListView: value }),
      },
    });
    return wrapper;
  };

  it("renders the patternlab toggle with calendar and list labels", () => {
    const wrapper = mountToggle();
    const toggle = wrapper.find('button[role="switch"]');

    expect(toggle.exists()).toBe(true);
    expect(toggle.text()).toContain("calendarView");
    expect(toggle.text()).toContain("listView");
  });

  it("toggles from calendar view to list view and back", async () => {
    const wrapper = mountToggle();
    const toggle = wrapper.find('button[role="switch"]');

    // initial state
    expect(toggle.attributes("aria-checked")).toBe("false");
    expect(wrapper.emitted("update:isListView")).toBeUndefined();
    expect(toggle.attributes("aria-label")).toBe(
      "calendarViewActiveLabel switchToListViewAriaLabel"
    );

    // click to toggle ON (list view)
    await toggle.trigger("click");
    let emits = wrapper.emitted("update:isListView");
    expect(emits?.[0]?.[0]).toBe(true);
    expect(toggle.attributes("aria-checked")).toBe("true");
    expect(toggle.classes()).toContain("m-toggle-switch--pressed");
    expect(toggle.attributes("aria-label")).toBe(
      "listViewActiveLabel switchToCalendarViewAriaLabel"
    );

    // click to toggle OFF (calendar view)
    await toggle.trigger("click");
    emits = wrapper.emitted("update:isListView");
    expect(emits?.[1]?.[0]).toBe(false);
    expect(toggle.attributes("aria-checked")).toBe("false");
    expect(toggle.attributes("aria-label")).toBe(
      "calendarViewActiveLabel switchToListViewAriaLabel"
    );
  });

  it("follows the isListView prop when the parent changes it", async () => {
    const wrapper = mountToggle();
    await wrapper.setProps({ isListView: true });

    expect(
      wrapper.find('button[role="switch"]').attributes("aria-checked")
    ).toBe("true");
  });
});
