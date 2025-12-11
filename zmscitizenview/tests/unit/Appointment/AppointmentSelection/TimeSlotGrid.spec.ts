import { mount } from "@vue/test-utils";
import { describe, it, expect, vi } from "vitest";
import { nextTick } from "vue";
vi.mock("@/utils/formatAppointmentDateTime", () => ({
  formatTimeFromUnix: (t: number) => `fmt-${t}`,
}));
// @ts-expect-error: SFC import for test
import TimeSlotGrid from "@/components/Appointment/AppointmentSelection/TimeSlotGrid.vue";

describe("TimeSlotGrid", () => {
  const MucButtonStub = {
    name: "MucButton",
    props: ["variant"],
    emits: ["click"],
    template:
      '<button class="m-button" :data-variant="variant" @click="$emit(\'click\')"><slot/></button>',
  };

  const tMock = (key: string) => {
    if (key === "timeStampSuffix") return "Uhr";
    return key;
  };

  const baseProps = {
    officeId: 1,
    times: [1750915200, 1750918800, 1750922400],
    timeLabel: "14:00-14:59",
    showLocationTitle: true,
    officeNameById: (id: number | string) => (String(id) === "1" ? "Office X" : null),
    isSlotSelected: (officeId: number | string, time: number) => false,
    t: tMock,
  };

  it("show/hide location title and uses officeNameById", async () => {
    const wrapper = mount(TimeSlotGrid, {
      global: { stubs: { MucButton: MucButtonStub } },
      props: { ...baseProps, showLocationTitle: true },
    });
    expect(wrapper.find('.location-title').exists()).toBe(true);
    expect(wrapper.find('.location-title').text()).toContain('Office X');

    await wrapper.setProps({ showLocationTitle: false });
    await nextTick();
    expect(wrapper.find('.location-title').exists()).toBe(false);
  });

  it("renders time label", () => {
    const wrapper = mount(TimeSlotGrid, {
      global: { stubs: { MucButton: MucButtonStub } },
      props: baseProps,
    });
    expect(wrapper.text()).toContain("14:00-14:59");
  });

  it("renders one button per time and emits selectTimeSlot on click", async () => {
    const wrapper = mount(TimeSlotGrid, {
      global: { stubs: { MucButton: MucButtonStub } },
      props: baseProps,
    });
    const buttons = wrapper.findAll('.timeslot, .m-button');
    expect(buttons.length).toBe(baseProps.times.length);

    await buttons[1].trigger('click');
    const emitted = wrapper.emitted('selectTimeSlot');
    expect(emitted && emitted[0]).toBeTruthy();
    expect(emitted![0][0]).toEqual({ officeId: 1, time: baseProps.times[1] });
  });

  it("applies primary variant when isSlotSelected is true, otherwise secondary", () => {
    const props = {
      ...baseProps,
      isSlotSelected: (officeId: number | string, time: number) => time === baseProps.times[1],
    };
    const wrapper = mount(TimeSlotGrid, {
      global: { stubs: { MucButton: MucButtonStub } },
      props,
    });
    const buttons = wrapper.findAll('.m-button');
    expect(buttons[0].attributes('data-variant')).toBe('secondary');
    expect(buttons[1].attributes('data-variant')).toBe('primary');
    expect(buttons[2].attributes('data-variant')).toBe('secondary');
  });

  it("formats time labels using formatter", () => {
    const wrapper = mount(TimeSlotGrid, {
      global: { stubs: { MucButton: MucButtonStub } },
      props: { ...baseProps, times: [111, 222] },
    });
    const text = wrapper.text();
    expect(text).toContain('fmt-111');
    expect(text).toContain('fmt-222');
  });
});