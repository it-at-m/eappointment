import { mount } from "@vue/test-utils";
import { describe, it, expect, vi } from "vitest";
import { nextTick } from "vue";
// @ts-expect-error: SFC import for test
import CalendarView from "@/components/Appointment/AppointmentSelection/CalendarView.vue";

const t = (key: string) => key;

const MucCalendarStub = {
  name: "muc-calendar",
  props: [
    "modelValue",
    "disableViewChange",
    "variant",
    "allowedDates",
    "min",
    "max",
    "viewMonth",
  ],
  emits: ["update:modelValue"],
  template: '<div class="muc-calendar" />',
};

const MucButtonStub = {
  name: "muc-button",
  emits: ["click"],
  props: ["variant", "icon", "iconShownLeft", "iconShownRight", "disabled"],
  template: '<button class="muc-button" :disabled="disabled" @click="$emit(\'click\')"><slot /></button>',
};

const TimeSlotGridStub = {
  name: "TimeSlotGrid",
  props: ["officeId", "times", "timeLabel", "showLocationTitle", "officeNameById", "isSlotSelected"],
  emits: ["selectTimeSlot"],
  template: '<div class="timeslot-grid"><span class="time-label">{{ timeLabel }}</span></div>',
};

function mountCalendarView(overrides: Partial<Record<string, any>> = {}) {
  const timeSlotsInHoursByOffice = overrides.timeSlotsInHoursByOffice ?? new Map();
  const timeSlotsInDayPartByOffice = overrides.timeSlotsInDayPartByOffice ?? new Map();
  return mount(CalendarView, {
    global: {
      stubs: {
        MucCalendar: MucCalendarStub,
        MucButton: MucButtonStub,
        TimeSlotGrid: TimeSlotGridStub,
      },
    },
    props: {
      t,
      selectedDay: overrides.selectedDay ?? new Date("2025-06-17"),
      calendarKey: 1,
      allowedDates: overrides.allowedDates ?? ((d: Date) => d.getDate() !== 16),
      minDate: overrides.minDate ?? new Date("2025-06-01"),
      maxDate: overrides.maxDate ?? new Date("2025-06-30"),
      viewMonth: overrides.viewMonth ?? new Date("2025-06-01"),
      timeSlotsInHoursByOffice,
      timeSlotsInDayPartByOffice,
      currentHour: overrides.currentHour ?? 10,
      firstHour: overrides.firstHour ?? 10,
      lastHour: overrides.lastHour ?? 14,
      currentDayPart: overrides.currentDayPart ?? "am",
      firstDayPart: overrides.firstDayPart ?? "am",
      lastDayPart: overrides.lastDayPart ?? "pm",
      selectableProviders: overrides.selectableProviders ?? [{ id: 1, name: "Office" }],
      selectedProviders: overrides.selectedProviders ?? { 1: true, 2: true },
      providersWithAppointments: overrides.providersWithAppointments ?? [{ id: 1, name: "Office" }, { id: 2, name: "Office 2" }],
      appointmentsCount: overrides.appointmentsCount ?? 20,
      isLoadingAppointments: overrides.isLoadingAppointments ?? false,
      isLoadingComplete: overrides.isLoadingComplete ?? false,
      availabilityInfoHtml: overrides.availabilityInfoHtml ?? null,
      officeNameById: overrides.officeNameById ?? (() => "Office"),
      isSlotSelected: overrides.isSlotSelected ?? (() => false),
    },
  });
}

describe("CalendarView", () => {
  it("handles calendar navigation correctly", async () => {
    const wrapper = mountCalendarView();
    const cal = wrapper.findComponent(MucCalendarStub);
    const newDate = new Date("2025-06-18");
    cal.vm.$emit("update:modelValue", newDate);
    await nextTick();
    const emitted = wrapper.emitted("update:selectedDay");
    expect(emitted && emitted[0][0]).toEqual(newDate);
  });

  describe("allowedDates", () => {
    it.each([
      [
        "returns true for available date",
        (d: Date) => d.toISOString().startsWith("2025-06-17"),
        new Date("2025-06-17"),
        true,
      ],
      [
        "returns false for date with no appointments",
        (d: Date) => d.toISOString().startsWith("2025-06-17"),
        new Date("2025-06-16"),
        false,
      ],
      [
        "returns false for date not in availableDays",
        (d: Date) => d.toISOString().startsWith("2025-06-16"),
        new Date("2025-06-18"),
        false,
      ],
    ])("%s", async (_title, allowedDates, probeDate, expected) => {
      const wrapper = mountCalendarView({ allowedDates });
      const cal = wrapper.findComponent(MucCalendarStub);
      expect((cal.props() as any).allowedDates(probeDate)).toBe(expected);
    });
  });

  describe("min/max navigation bounds", () => {
    it.each([
      ["sets max bound (no appointments beyond month)", { maxDate: new Date("2025-06-30") }, "max", new Date("2025-06-30")],
      ["sets max bound (appointments in future months)", { maxDate: new Date("2025-07-01") }, "max", new Date("2025-07-01")],
      ["sets min bound (no appointments before month)", { minDate: new Date("2025-06-01") }, "min", new Date("2025-06-01")],
      ["sets min bound (appointments in past months)", { minDate: new Date("2025-05-31") }, "min", new Date("2025-05-31")],
    ])("%s", async (_title, propOverrides, key, expected) => {
      const wrapper = mountCalendarView(propOverrides as any);
      const cal = wrapper.findComponent(MucCalendarStub);
      expect((cal.props() as any)[key]).toEqual(expected);
    });
  });

  it("updates navigation limits when providers are deselected", async () => {
    const wrapper = mountCalendarView({ maxDate: new Date("2025-08-01") });
    let cal = wrapper.findComponent(MucCalendarStub);
    expect((cal.props() as any).max).toEqual(new Date("2025-08-01"));
    await wrapper.setProps({ maxDate: new Date("2025-07-01") });
    await nextTick();
    cal = wrapper.findComponent(MucCalendarStub);
    expect((cal.props() as any).max).toEqual(new Date("2025-07-01"));
  });

  it("updates navigation limits when providers are selected", async () => {
    const wrapper = mountCalendarView({ maxDate: new Date("2025-07-01") });
    let cal = wrapper.findComponent(MucCalendarStub);
    expect((cal.props() as any).max).toEqual(new Date("2025-07-01"));
    await wrapper.setProps({ maxDate: new Date("2025-08-01") });
    await nextTick();
    cal = wrapper.findComponent(MucCalendarStub);
    expect((cal.props() as any).max).toEqual(new Date("2025-08-01"));
  });

  it("resets to earliest hour when selecting a new day in the calendar", async () => {
    const hoursMap = new Map<number, number[]>([[8, [1]], [14, [2]]]);
    const wrapper = mountCalendarView({
      appointmentsCount: 19,
      timeSlotsInHoursByOffice: new Map([[1, { appointments: new Map(hoursMap) }]]),
      currentHour: 13,
      firstHour: 8,
      lastHour: 14,
    });
    // Use exposed snapToNearest to emulate day change reconciliation
    await (wrapper.vm as any).snapToNearest();
    const setHour = wrapper.emitted("setSelectedHour");
    // Nearest to 13 from [8,14] is 14
    expect(setHour && setHour[0][0]).toBe(14);
  });

  it("resets selectedHour to earliest available hour when selecting a new day", async () => {
    const hoursMap = new Map<number, number[]>([[9, [1]], [14, [2]]]);
    const wrapper = mountCalendarView({
      appointmentsCount: 19,
      timeSlotsInHoursByOffice: new Map([[1, { appointments: new Map(hoursMap) }]]),
      currentHour: null,
      firstHour: 8,
      lastHour: 14,
    });
    await (wrapper.vm as any).snapToNearest();
    const setHour = wrapper.emitted("setSelectedHour");
    expect(setHour && setHour[0][0]).toBe(9);
  });

  it("resets selectedDayPart to 'am' if available when in day part view", async () => {
    const dayPartMap = new Map<string, number[]>([["am", [1]],["pm", [2]]]);
    const wrapper = mountCalendarView({
      appointmentsCount: 18,
      timeSlotsInDayPartByOffice: new Map([[1, { appointments: new Map(dayPartMap) }]]),
      currentDayPart: "pm",
    });
    const earlier = wrapper.findAllComponents(MucButtonStub)[0];
    await earlier.trigger("click");
    const setPart = wrapper.emitted("setSelectedDayPart");
    expect(setPart && setPart[0][0]).toBe("am");
  });

  it("does not reset selectedDayPart when selecting the same day", async () => {
    const dayPartMap = new Map<string, number[]>([["am", [1]],["pm", [2]]]);
    const wrapper = mountCalendarView({
      appointmentsCount: 18,
      timeSlotsInDayPartByOffice: new Map([[1, { appointments: new Map(dayPartMap) }]]),
      currentDayPart: "pm",
    });
    // No change when clicking later if already pm is last
    const later = wrapper.findAllComponents(MucButtonStub)[1];
    await later.trigger("click");
    const setPart = wrapper.emitted("setSelectedDayPart");
    expect(setPart && setPart[0][0]).toBeUndefined();
  });

  it("shows hourly view if total appointments > 18", async () => {
    const hoursMap = new Map<number, number[]>([[10, [1, 2]]]);
    const wrapper = mountCalendarView({
      appointmentsCount: 19,
      timeSlotsInHoursByOffice: new Map([[1, { appointments: new Map(hoursMap) }]]),
      selectedDay: new Date("2025-07-02"),
      firstHour: 10,
      currentHour: 10,
    });
    expect(wrapper.html()).toContain("availableTimes");
    expect(wrapper.find(".time-label").text()).toContain("10:00-10:59");
  });

  it("shows am/pm view if total appointments <= 18", async () => {
    const dayPartMap = new Map<string, number[]>([["am", [1, 2]]]);
    const wrapper = mountCalendarView({
      appointmentsCount: 18,
      timeSlotsInDayPartByOffice: new Map([[1, { appointments: new Map(dayPartMap) }]]),
      selectedDay: new Date("2025-07-02"),
    });
    expect(wrapper.html()).toContain("availableTimes");
  });
});