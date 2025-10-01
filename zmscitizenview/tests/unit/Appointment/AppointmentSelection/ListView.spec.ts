import { mount } from "@vue/test-utils";
import { describe, it, expect, vi } from "vitest";
import { nextTick } from "vue";
// @ts-expect-error: SFC import for test
import ListView from "@/components/Appointment/AppointmentSelection/ListView.vue";

describe("ListView", () => {
  const t = (key: string) => key;
  const officeNameById = (_: number | string) => "Office";
  const isSlotSelected = () => false;
  const formatTime = (n: number) => String(n);

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
          { time: "2025-06-10", providerIDs: "1" },
          { time: "2025-06-11", providerIDs: "1" },
          { time: "2025-06-12", providerIDs: "1" },
          { time: "2025-06-13", providerIDs: "1" },
          { time: "2025-06-14", providerIDs: "1" },
          { time: "2025-06-15", providerIDs: "1" },
        ],
        datesWithoutAppointments: new Set<string>(),
        appointmentTimestampsByOffice: [
          { officeId: 1, appointments: [1747202400, 1747223100] },
        ],
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
    const wrapper = mountListView({
      // total 8 days available; after load more expect 8
      availableDays: [
        { time: "2025-06-10", providerIDs: "1" },
        { time: "2025-06-11", providerIDs: "1" },
        { time: "2025-06-12", providerIDs: "1" },
        { time: "2025-06-13", providerIDs: "1" },
        { time: "2025-06-14", providerIDs: "1" },
        { time: "2025-06-15", providerIDs: "1" },
        { time: "2025-06-16", providerIDs: "1" },
        { time: "2025-06-17", providerIDs: "1" },
      ],
    });
    // initial shows first 5
    expect(wrapper.findAll(".m-accordion__section-header").length).toBe(5);

    const btn = wrapper.findAll(".m-button").find((b) => b.text().includes("loadMore"));
    expect(btn).toBeTruthy();
    await btn!.trigger("click");
    await nextTick();
    expect(wrapper.findAll(".m-accordion__section-header").length).toBe(8);
  });

  it("opens the clicked accordion section and closes the previous one", async () => {
    const wrapper = mountListView();
    // Explicitly open first, then switch to second
    await wrapper.find("#listHeading-0 .m-accordion__section-button").trigger("click");
    await nextTick();
    expect(wrapper.find("#listContent-0").classes()).toContain("show");
    expect(wrapper.find("#listContent-1").classes()).not.toContain("show");

    await wrapper.find("#listHeading-1 .m-accordion__section-button").trigger("click");
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
      appointmentTimestampsByOffice: [
        { officeId: 1, appointments: [1747202400, 1747223100] },
      ],
    });
    const dateString = "2025-06-10";
    (wrapper.vm as any).listViewCurrentHour.set(dateString, 16);

    await (wrapper.vm as any).onEarlier({
      dateString,
      hourRows: [
        { hour: 15, times: [1747202400], officeId: 1 },
        { hour: 16, times: [1747223100], officeId: 1 },
      ],
    }, "hour");
    await nextTick();

    expect((wrapper.vm as any).listViewCurrentHour.get(dateString)).toBe(15);
  });

  it("navigates between day parts in list view", async () => {
    const wrapper = mountListView({
      appointmentTimestampsByOffice: [
        { officeId: 1, appointments: [1747202400, 1747223100] },
      ],
    });
    const dateString = "2025-06-10";
    (wrapper.vm as any).listViewCurrentDayPart.set(dateString, "pm");

    await (wrapper.vm as any).onEarlier({
      dateString,
      dayPartRows: [
        { part: "am", times: [1747202400], officeId: 1 },
        { part: "pm", times: [1747223100], officeId: 1 },
      ],
    }, "dayPart");
    await nextTick();

    expect((wrapper.vm as any).listViewCurrentDayPart.get(dateString)).toBe("am");
  });

});


