import { describe, it, expect, beforeEach, vi } from "vitest";
import { mount } from "@vue/test-utils";
import CalendarView from "/src/components/Appointment/CalendarView.vue";
import { ref, nextTick } from "vue";
import {
  fetchAvailableDays,
  fetchAvailableTimeSlots,
} from "@/api/ZMSAppointmentAPI";

const t = vi.fn((key: string) => key);

const baseProps = {
  baseUrl: "http://test.url",
  isRebooking: false,
  exclusiveLocation: undefined,
  preselectedOfficeId: undefined,
  selectedServiceMap: new Map([["service1", 1]]),
  captchaToken: "test-token",
  bookingError: false,
  bookingErrorKey: "noAppointmentsAvailable",
  t,
};

vi.mock('@/api/ZMSAppointmentAPI', () => ({
  fetchAvailableDays: vi.fn(),
  fetchAvailableTimeSlots: vi.fn(),
}));


const createWrapper = (overrides = {}) => {
  return mount(CalendarView, {
    global: {
      provide: {
        selectedServiceProvider: {
          selectedService: ref(overrides.selectedService),
        },
        selectedTimeslot: {
          selectedProvider: ref(overrides.selectedProvider ?? null),
          selectedTimeslot: ref(overrides.selectedTimeslot ?? 0),
        },
        selectableProviders: ref([])
      },
      stubs: ["muc-slider", "muc-callout", "muc-calendar"],
    },
    props: {
      ...baseProps,
      ...overrides.props,
    },
  });
};

describe("CalendarView", () => {
  (fetchAvailableDays as vi.Mock).mockResolvedValue({
    availableDays: [
      {
        time: '2025-05-14',
        providerIDs: '102522,54261,10489'
      },
      {
        time: '2025-05-15',
        providerIDs: '102522,54261,10489'
      }]
  });

  (fetchAvailableTimeSlots as vi.Mock).mockResolvedValue({
    offices: [
      {
        officeId: 102522,
        appointments: [1747202400, 1747223100, 1747223400, 1747223700, 1747224000, 1747224300]
      },
      {
        officeId: 54261,
        appointments: [1747223100, 1747223400, 1747223700, 1747224000, 1747224300]
      },
      {
        officeId: 10489,
        appointments: [1747223100, 1747223400, 1747223700, 1747224000, 1747224300]
      }
    ]
  });

  it("renders nothing if no provider is selected", () => {
    const wrapper = createWrapper();
    expect(wrapper.html()).not.toContain("location");
  });

  it("renders multiple providers with checkboxes", async () => {
    const wrapper = createWrapper({
      selectedService: { id: "service1", providers: [
          { name: "Office A", id: 1, address: { street: "Elm", house_number: "99" } },
          { name: "Office B", id: 2, address: { street: "Elm", house_number: "99" } },
        ] }
    });

    await nextTick();

    const checkboxes = wrapper.findAll('.m-checkboxes__item input[type="checkbox"]');
    expect(checkboxes.length).toBe(2);

    const officeALabel = wrapper.find('label[for="checkbox-1"]');
    const officeBLabel = wrapper.find('label[for="checkbox-2"]');

    expect(officeALabel.exists()).toBe(true);
    expect(officeBLabel.exists()).toBe(true);

    expect(officeALabel.text()).toBe('Office A');
    expect(officeBLabel.text()).toBe('Office B');
  });

  it("shows providers in correct prio", async () => {
    const wrapper = createWrapper({
      selectedService: { id: "service1", providers: [
          { name: "Office AAA", priority: 5, id: 102522, address: { street: "Elm", house_number: "99" } },
          { name: "Office BBB", priority: 10, id: 54261, address: { street: "Elm", house_number: "99" } }
        ] }
    });

    await wrapper.vm.showSelectionForProvider({ name: "Office AAA", id: 102522, address: { street: "Elm", house_number: "99" }});
    await nextTick();

    await wrapper.vm.getAppointmentsOfDay('2025-05-14');
    await nextTick();

    await wrapper.vm.laterAppointments('dayPart');
    await nextTick();

    const html = wrapper.html();
    const indexBBB = html.indexOf("Office BBB");
    const indexAAA = html.indexOf("Office AAA");

    expect(indexBBB).toBeGreaterThan(-1);
    expect(indexAAA).toBeGreaterThan(-1);
    expect(indexBBB).toBeLessThan(indexAAA);
  });

  it("renders a single provider view", async () => {
    const wrapper = createWrapper({
      selectedService: { id: "service1", providers: [
          { name: "Office ABC", id: 1, address: { street: "Elm", house_number: "99" } }
        ] }
    });

    await nextTick();

    expect(wrapper.text()).toContain("Office ABC");
    expect(wrapper.text()).toContain("Elm 99");
  });

  it("shows only one appointment for one provider in the morning", async () => {
    const wrapper = createWrapper({
      selectedService: { id: "service1", providers: [
          { name: "Office AAA", id: 102522, address: { street: "Elm", house_number: "99" } },
          { name: "Office BBB", id: 54261, address: { street: "Elm", house_number: "99" } }
        ] }
    });

    await wrapper.vm.showSelectionForProvider({ name: "Office AAA", id: 102522, address: { street: "Elm", house_number: "99" }});
    await nextTick();

    await wrapper.vm.getAppointmentsOfDay('2025-05-14');
    await nextTick();

    const locationTitles = wrapper.findAll('.location-title');
    const officeAAA = locationTitles.find(location => location.text().includes('Office AAA'));
    const officeBBB = locationTitles.find(location => location.text().includes('Office BBB'));
    expect(officeAAA).toBeTruthy();
    expect(officeBBB).toBeFalsy();

    const timeslotButton = wrapper.find('button.timeslot');
    expect(timeslotButton.exists()).toBe(true);
    expect(timeslotButton.text()).toContain('08:00');
    expect(wrapper.text()).toContain('Mittwoch, 14.05.2025');
    expect(wrapper.html()).toContain('class="centered-text">am</p>');
  });

  it("shows more appointments and providers after loading later appointments", async () => {
    const wrapper = createWrapper({
      selectedService: { id: "service1", providers: [
          { name: "Office AAA", id: 102522, address: { street: "Elm", house_number: "99" } },
          { name: "Office BBB", id: 54261, address: { street: "Elm", house_number: "99" } }
        ] }
    });

    await wrapper.vm.showSelectionForProvider({ name: "Office AAA", id: 102522, address: { street: "Elm", house_number: "99" }});
    await nextTick();

    await wrapper.vm.getAppointmentsOfDay('2025-05-14');
    await nextTick();

    await wrapper.vm.laterAppointments('dayPart');
    await nextTick();

    const locationTitles = wrapper.findAll('.location-title');
    const officeAAA = locationTitles.find(location => location.text().includes('Office AAA'));
    const officeBBB = locationTitles.find(location => location.text().includes('Office BBB'));
    expect(officeAAA).toBeTruthy();
    expect(officeBBB).toBeTruthy();

    const timeslotButton = wrapper.find('button.timeslot');
    expect(timeslotButton.exists()).toBe(true);
    expect(timeslotButton.text()).toContain('13:45');
    expect(wrapper.text()).toContain('Mittwoch, 14.05.2025');
    expect(wrapper.html()).toContain('class="centered-text">pm</p>');
  });

  it("shows appointments by hour", async () => {
    (fetchAvailableTimeSlots as vi.Mock).mockResolvedValue({
      offices: [
        {
          officeId: 102522,
          appointments: [
            1747232100, // 15:55
            1747232400, // 16:00
            1747232700, // 16:05
            1747233000, // 16:10
            1747233300  // 16:15
          ]
        },
        {
          officeId: 54261,
          appointments: [
            1747224600, // 13:50
            1747224900, // 13:55
            1747225200, // 14:00
            1747225500, // 14:05
            1747225800, // 14:10
            1747226100, // 14:15
            1747226400, // 14:20
            1747226700, // 14:25
            1747227000, // 14:30
            1747227300, // 14:35
            1747227600, // 14:40
            1747227900, // 14:45
            1747228200, // 14:50
            1747228500, // 14:55
            1747228800, // 15:00
            1747229100, // 15:05
            1747229400, // 15:10
            1747229700, // 15:15
            1747230000, // 15:20
            1747230300, // 15:25
            1747230600, // 15:30
            1747230900, // 15:35
            1747231200, // 15:40
            1747231500, // 15:45
            1747231800, // 15:50
            1747232100, // 15:55
            1747232400, // 16:00
            1747232700, // 16:05
            1747233000, // 16:10
            1747233300  // 16:15
          ]
        },
        {
          officeId: 10489,
          appointments: [
            1747224600, // 13:50
            1747224900, // 13:55
            1747225200, // 14:00
            1747225500, // 14:05
            1747225800, // 14:10
            1747226100, // 14:15
            1747226400, // 14:20
            1747226700, // 14:25
            1747227000, // 14:30
            1747227300, // 14:35
            1747227600, // 14:40
            1747227900, // 14:45
            1747228200, // 14:50
            1747228500, // 14:55
            1747228800, // 15:00
            1747229100, // 15:05
            1747229400, // 15:10
            1747229700, // 15:15
            1747230000, // 15:20
            1747230300, // 15:25
            1747230600, // 15:30
            1747230900, // 15:35
            1747231200, // 15:40
            1747231500, // 15:45
            1747231800, // 15:50
            1747232100, // 15:55
            1747232400, // 16:00
            1747232700, // 16:05
            1747233000, // 16:10
            1747233300  // 16:15
          ]
        }
      ]
    });

    const wrapper = createWrapper({
      selectedService: { id: "service1", providers: [
          { name: "Office AAA", id: 102522, address: { street: "Elm", house_number: "99" } },
          { name: "Office BBB", id: 54261, address: { street: "Elm", house_number: "99" } },
          { name: "Office CCC", id: 10489, address: { street: "Elm", house_number: "99" } }
        ] }
    });

    await wrapper.vm.showSelectionForProvider({ name: "Office AAA", id: 102522, address: { street: "Elm", house_number: "99" }});
    await nextTick();

    await wrapper.vm.getAppointmentsOfDay('2025-05-14');
    await nextTick();

    await wrapper.vm.laterAppointments('dayPart');
    await nextTick();

    const locationTitles = wrapper.findAll('.location-title');
    const officeAAA = locationTitles.find(location => location.text().includes('Office AAA'));
    const officeBBB = locationTitles.find(location => location.text().includes('Office BBB'));
    const officeCCC = locationTitles.find(location => location.text().includes('Office CCC'));
    expect(officeAAA).toBeFalsy();
    expect(officeBBB).toBeTruthy();
    expect(officeCCC).toBeTruthy();

    const timeslotButton = wrapper.find('button.timeslot');
    expect(timeslotButton.exists()).toBe(true);
    expect(timeslotButton.text()).toContain('15:00');
    expect(wrapper.text()).toContain('Mittwoch, 14.05.2025');
    expect(wrapper.html()).toContain('15:00-15:59');
  });

  it("shows available day only by providers that have free appointments on that day", async () => {
    (fetchAvailableDays as vi.Mock).mockResolvedValue({
      availableDays: [
        {
          time: '2025-05-14',
          providerIDs: '102522,54261,10489'
        },
        {
          time: '2025-05-15',
          providerIDs: '102522'
        }]
    });

    const wrapper = createWrapper({
      selectedService: { id: "service1", providers: [
          { name: "Office AAA", id: 102522, address: { street: "Elm", house_number: "99" } },
          { name: "Office BBB", id: 54261, address: { street: "Elm", house_number: "99" } },
          { name: "Office CCC", id: 10489, address: { street: "Elm", house_number: "99" } }
        ] }
    });

    await wrapper.vm.showSelectionForProvider({ name: "Office AAA", id: 102522, address: { street: "Elm", house_number: "99" }});
    await nextTick();

    wrapper.vm.handleProviderCheckbox(102522)

    expect(wrapper.vm.allowedDates(new Date('2025-05-14'))).toBeTruthy();
    expect(wrapper.vm.allowedDates(new Date('2025-05-16'))).toBeFalsy();
    expect(wrapper.vm.allowedDates(new Date('2025-05-17'))).toBeFalsy();
  });
});
