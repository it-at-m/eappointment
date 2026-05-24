import { mount } from "@vue/test-utils";
import { describe, it, expect, vi } from "vitest";
import { nextTick } from "vue";
// @ts-expect-error: SFC import for test
import CalendarListToggle from "@/components/Appointment/AppointmentSelection/CalendarListToggle.vue";

const t = vi.fn((key: string) => key);

describe("CalendarListToggle", () => {
  it("toggles from calendar view to list view and back", async () => {
    const wrapper = mount(CalendarListToggle, {
      props: { t },
    });

    const toggle = wrapper.find("button.m-toggle-switch");

    // initial state
    expect(toggle.classes()).not.toContain("m-toggle-switch--pressed");
    expect(wrapper.emitted("update:isListView")).toBeUndefined();

    expect(toggle.attributes("aria-label")).toBe(
      "calendarViewActiveLabel switchToListViewAriaLabel"
    );

    // click to toggle ON (list view)
    await toggle.trigger("click");
    await nextTick();
    let emits = wrapper.emitted("update:isListView");
    expect(emits && emits[0] && emits[0][0]).toBe(true);
    expect(toggle.classes()).toContain("m-toggle-switch--pressed");
    expect(toggle.attributes("aria-label")).toBe(
      "listViewActiveLabel switchToCalendarViewAriaLabel"
    );

    // click to toggle OFF (calendar view)
    await toggle.trigger("click");
    await nextTick();
    emits = wrapper.emitted("update:isListView");
    expect(emits && emits[1] && emits[1][0]).toBe(false);
    expect(toggle.classes()).not.toContain("m-toggle-switch--pressed"); // statt aria-checked="false"
    expect(toggle.attributes("aria-label")).toBe(
      "calendarViewActiveLabel switchToListViewAriaLabel"
    );
  });
});