import { mount } from "@vue/test-utils";
import { describe, it, expect, vi } from "vitest";
import { nextTick } from "vue";
vi.mock("@/utils/formatAppointmentDateTime", () => ({
  formatTimeFromUnix: (t: number) => `fmt-${t}`,
}));
import TimeSlotGrid from "@/components/Appointment/AppointmentSelection/TimeSlotGrid.vue";

describe("TimeSlotGrid", () => {
  const MucButtonStub = {
    name: "MucButton",
    props: ["variant", "id"],
    emits: ["click"],
    template:
      '<button class="m-button timeslot" :id="id" :data-variant="variant" v-bind="$attrs" @click="$emit(\'click\')"><slot/></button>',
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

  it("puts real provider id on each timeslot when officeIdForTime is set", () => {
    const officeIdForTime = (time: number) =>
      time === baseProps.times[1] ? 10503 : 10489;
    const wrapper = mount(TimeSlotGrid, {
      global: { stubs: { MucButton: MucButtonStub } },
      props: {
        ...baseProps,
        officeId: 10489,
        officeIdForTime,
        officeNameById: (id: number | string) =>
          String(id) === "10489" || String(id) === "10503" ? "Ort" : null,
      },
    });
    const items = wrapper.findAll(".grid-item");
    expect(items[0].attributes("data-provider-id")).toBe("10489");
    expect(items[1].attributes("data-provider-id")).toBe("10503");
    expect(items[2].attributes("data-provider-id")).toBe("10489");
    expect(wrapper.find(`#provider-10503-timeslot-${baseProps.times[1]}`).exists()).toBe(
      true
    );
  });

  it("emits real officeId from officeIdForTime on click", async () => {
    const officeIdForTime = () => 10503;
    const wrapper = mount(TimeSlotGrid, {
      global: { stubs: { MucButton: MucButtonStub } },
      props: { ...baseProps, officeId: 10489, officeIdForTime },
    });
    await wrapper.findAll(".m-button")[0].trigger("click");
    expect(wrapper.emitted("selectTimeSlot")![0][0]).toEqual({
      officeId: 10503,
      time: baseProps.times[0],
    });
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

  it("renders aria-label with time, suffix, and office name", () => {
    const wrapper = mount(TimeSlotGrid, {
      global: { stubs: { MucButton: MucButtonStub } },
      props: baseProps,
    });
    const buttons = wrapper.findAll('.timeslot');
    expect(buttons[0].attributes('aria-label')).toBe('fmt-1750915200 Uhr, Office X');
  });
});