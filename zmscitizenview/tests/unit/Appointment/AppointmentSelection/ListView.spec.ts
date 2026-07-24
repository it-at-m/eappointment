import { mount } from "@vue/test-utils";
import { describe, expect, it } from "vitest";
import { nextTick } from "vue";

import ListView from "@/components/Appointment/AppointmentSelection/ListView.vue";
import {
  listViewDayPartNavigationSlots,
  listViewHourNavigationSlots,
} from "../../../helpers/calendarAvailability";

describe("ListView", () => {
  const t = (key: string) => key;
  const officeNameById = (_: number | string) => "Office";
  const isSlotSelected = () => false;
  const formatTime = (n: number) => String(n);

  const defaultAppointmentsByDay = new Map(
    [
      "2025-06-10",
      "2025-06-11",
      "2025-06-12",
      "2025-06-13",
      "2025-06-14",
      "2025-06-15",
    ].map((date) => [date, listViewDayPartNavigationSlots])
  );

  function mountListView(overrides: Partial<Record<string, any>> = {}) {
    return mount(ListView, {
      props: {
        t,
        isLoadingAppointments: false,
        availabilityInfoHtml: null,
        selectableProviders: [{ id: 1, name: "Office" } as any],
        selectedProviders: { 1: true },
        providersWithAppointments: [{ id: 1, name: "Office" } as any],
        officeNameById,
        isSlotSelected,
        formatTime,
        availableDays: [
          { date: "2025-06-10", providerIDs: "1" },
          { date: "2025-06-11", providerIDs: "1" },
          { date: "2025-06-12", providerIDs: "1" },
          { date: "2025-06-13", providerIDs: "1" },
          { date: "2025-06-14", providerIDs: "1" },
          { date: "2025-06-15", providerIDs: "1" },
        ],
        appointmentsByDay: defaultAppointmentsByDay,
        officeOrder: new Map<number, number>([[1, 0]]),
        ...overrides,
      },
      global: {
        stubs: {
          MucButton: {
            template:
              '<button class="m-button" @click="$emit(\'click\')"><slot/></button>',
          },
        },
      },
    });
  }

  it("adds three more days whenever the 'Mehr laden' button is clicked", async () => {
    const appointmentsByDay = new Map([
      ...defaultAppointmentsByDay,
      ["2025-06-16", [{ officeId: 1, appointments: [1747202400] }]],
      ["2025-06-17", [{ officeId: 1, appointments: [1747202400] }]],
    ]);
    const wrapper = mountListView({
      availableDays: [
        { date: "2025-06-10", providerIDs: "1" },
        { date: "2025-06-11", providerIDs: "1" },
        { date: "2025-06-12", providerIDs: "1" },
        { date: "2025-06-13", providerIDs: "1" },
        { date: "2025-06-14", providerIDs: "1" },
        { date: "2025-06-15", providerIDs: "1" },
        { date: "2025-06-16", providerIDs: "1" },
        { date: "2025-06-17", providerIDs: "1" },
      ],
      appointmentsByDay,
    });
    expect(wrapper.findAll(".m-accordion__section-header").length).toBe(5);

    const btn = wrapper
      .findAll(".m-button")
      .find((b) => b.text().includes("loadMore"));
    expect(btn).toBeTruthy();
    await btn!.trigger("click");
    await nextTick();
    expect(wrapper.findAll(".m-accordion__section-header").length).toBe(8);
  });

  it("lists daylist days even when only the free-slot window has appointments loaded", async () => {
    // Combined calendar API only returns appointment timestamps for the free-slot
    // window (typically one day). Other availableDays are daylist-only until opened.
    const wrapper = mountListView({
      availableDays: [
        { date: "2025-06-10", providerIDs: "1" },
        { date: "2025-06-11", providerIDs: "1" },
        { date: "2025-06-12", providerIDs: "1" },
        { date: "2025-06-13", providerIDs: "1" },
        { date: "2025-06-14", providerIDs: "1" },
        { date: "2025-06-15", providerIDs: "1" },
      ],
      appointmentsByDay: new Map([
        ["2025-06-10", listViewDayPartNavigationSlots],
        ["2025-06-11", [{ officeId: 1, appointments: [] }]],
        ["2025-06-12", [{ officeId: 1, appointments: [] }]],
        ["2025-06-13", [{ officeId: 1, appointments: [] }]],
        ["2025-06-14", [{ officeId: 1, appointments: [] }]],
        ["2025-06-15", [{ officeId: 1, appointments: [] }]],
      ]),
    });

    expect(wrapper.findAll(".m-accordion__section-header").length).toBe(5);
    const btn = wrapper
      .findAll(".m-button")
      .find((b) => b.text().includes("loadMore"));
    expect(btn).toBeTruthy();
  });

  it("keeps Mehr laden when later months may still be fetched", async () => {
    const wrapper = mountListView({
      availableDays: [
        { date: "2025-06-10", providerIDs: "1" },
        { date: "2025-06-11", providerIDs: "1" },
      ],
      appointmentsByDay: new Map([
        ["2025-06-10", listViewDayPartNavigationSlots],
        ["2025-06-11", listViewDayPartNavigationSlots],
      ]),
      hasMoreDaysAhead: true,
    });

    expect(wrapper.findAll(".m-accordion__section-header").length).toBe(2);
    const btn = wrapper
      .findAll(".m-button")
      .find((b) => b.text().includes("loadMore"));
    expect(btn).toBeTruthy();
    await btn!.trigger("click");
    await nextTick();
    expect(wrapper.emitted("requestMoreDays")).toBeTruthy();
  });

  it("does not reopen the first day when Mehr laden expands the list", async () => {
    const wrapper = mountListView({
      availableDays: [
        { date: "2025-06-10", providerIDs: "1" },
        { date: "2025-06-11", providerIDs: "1" },
        { date: "2025-06-12", providerIDs: "1" },
        { date: "2025-06-13", providerIDs: "1" },
        { date: "2025-06-14", providerIDs: "1" },
        { date: "2025-06-15", providerIDs: "1" },
        { date: "2025-06-16", providerIDs: "1" },
        { date: "2025-06-17", providerIDs: "1" },
      ],
      appointmentsByDay: new Map(
        [
          "2025-06-10",
          "2025-06-11",
          "2025-06-12",
          "2025-06-13",
          "2025-06-14",
          "2025-06-15",
          "2025-06-16",
          "2025-06-17",
        ].map((date) => [date, listViewDayPartNavigationSlots])
      ),
    });
    await nextTick();

    // Close the auto-opened first day
    await wrapper
      .find("#listHeading-0 .m-accordion__section-button")
      .trigger("click");
    await nextTick();
    expect(wrapper.find("#listContent-0").classes()).not.toContain("show");

    const btn = wrapper
      .findAll(".m-button")
      .find((b) => b.text().includes("loadMore"));
    await btn!.trigger("click");
    await nextTick();

    expect(wrapper.findAll(".m-accordion__section-header").length).toBe(8);
    expect(wrapper.find("#listContent-0").classes()).not.toContain("show");
  });

  it("opens the clicked accordion section and closes the previous one", async () => {
    const wrapper = mountListView();
    await wrapper
      .find("#listHeading-0 .m-accordion__section-button")
      .trigger("click");
    await nextTick();
    expect(wrapper.find("#listContent-0").classes()).toContain("show");
    expect(wrapper.find("#listContent-1").classes()).not.toContain("show");

    await wrapper
      .find("#listHeading-1 .m-accordion__section-button")
      .trigger("click");
    await nextTick();
    expect(wrapper.find("#listContent-0").classes()).not.toContain("show");
    expect(wrapper.find("#listContent-1").classes()).toContain("show");
  });

  it("initializes list view navigation state for each day", async () => {
    const wrapper = mountListView();
    await nextTick();
    expect((wrapper.vm as any).listViewCurrentHour).toBeDefined();
    expect((wrapper.vm as any).listViewCurrentDayPart).toBeDefined();
  });

  it("navigates between hours in list view", async () => {
    const wrapper = mountListView({
      appointmentsByDay: new Map(
        [
          "2025-06-10",
          "2025-06-11",
          "2025-06-12",
          "2025-06-13",
          "2025-06-14",
          "2025-06-15",
        ].map((date) => [date, listViewHourNavigationSlots])
      ),
    });
    await nextTick();
    const dateString = "2025-06-10";
    (wrapper.vm as any).listViewCurrentHour.set(dateString, 16);
    await nextTick();

    await (wrapper.vm as any).onEarlier(
      {
        dateString,
        hourRows: [
          { hour: 15, times: [1747202400], officeId: 1 },
          { hour: 16, times: [1747223100], officeId: 1 },
        ],
      },
      "hour"
    );
    await nextTick();

    expect((wrapper.vm as any).listViewCurrentHour.get(dateString)).toBe(15);
  });

  it("navigates between day parts in list view", async () => {
    const wrapper = mountListView();
    await nextTick();
    const dateString = "2025-06-10";
    (wrapper.vm as any).listViewCurrentDayPart.set(dateString, "pm");
    await nextTick();

    await (wrapper.vm as any).onEarlier(
      {
        dateString,
        dayPartRows: [
          { part: "am", times: [1749535200], officeId: 1 },
          { part: "pm", times: [1749560400], officeId: 1 },
        ],
      },
      "dayPart"
    );
    await nextTick();

    expect((wrapper.vm as any).listViewCurrentDayPart.get(dateString)).toBe(
      "am"
    );
  });
});
