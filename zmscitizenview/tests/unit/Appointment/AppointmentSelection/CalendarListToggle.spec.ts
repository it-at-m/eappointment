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

    // initial state
    expect(wrapper.attributes("aria-checked")).toBeUndefined();
    expect(wrapper.find(".m-toggle-switch").attributes("aria-checked")).toBe("false");
    expect(wrapper.emitted("update:isListView")).toBeUndefined();

    // click to toggle ON (list view)
    await wrapper.find(".m-toggle-switch").trigger("click");
    await nextTick();
    let emits = wrapper.emitted("update:isListView");
    expect(emits && emits[0] && emits[0][0]).toBe(true);
    expect(wrapper.find(".m-toggle-switch").attributes("aria-checked")).toBe("true");

    // click to toggle OFF (calendar view)
    await wrapper.find(".m-toggle-switch").trigger("click");
    await nextTick();
    emits = wrapper.emitted("update:isListView");
    expect(emits && emits[1] && emits[1][0]).toBe(false);
    expect(wrapper.find(".m-toggle-switch").attributes("aria-checked")).toBe("false");
  });
});