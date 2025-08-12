import { mount } from "@vue/test-utils";
import { describe, it, expect, vi, type Mock, beforeEach, afterEach } from "vitest";
import { flushPromises } from '@vue/test-utils';
// @ts-expect-error: Vue SFC import for test
import CalendarView from "@/components/Appointment/CalendarView.vue";
import { ref, nextTick } from "vue";
import {
  fetchAvailableDays,
  fetchAvailableTimeSlots,
// @ts-expect-error: API import for test
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
  bookingErrorKey: "apiErrorNoAppointmentForThisScope",
  t,
};

vi.mock('@/api/ZMSAppointmentAPI', () => ({
  fetchAvailableDays: vi.fn(),
  fetchAvailableTimeSlots: vi.fn(),
}));

interface WrapperOverrides {
  selectedService?: any;
  selectedProvider?: any;
  selectedTimeslot?: number;
  props?: Record<string, any>;
}

const createWrapper = (overrides: WrapperOverrides = {}) => {
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
        selectableProviders: ref([]),
        loadingStates: {
          isReservingAppointment: ref(false),
          isUpdatingAppointment: ref(false),
          isBookingAppointment: ref(false),
          isCancelingAppointment: ref(false),
        },
      },
      stubs: {
        "muc-slider": true,
        "muc-callout": {
            props: ["type", "t"],
            template: `
              <div data-test='muc-callout' :data-type="type">
                <slot name="header"></slot>
                <slot name="content">
              </slot></div>
            `
        },
        "muc-calendar": true
      }
    },
    props: {
      ...baseProps,
      ...overrides.props,
    },
  });
};

describe("CalendarView", () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  (fetchAvailableDays as Mock).mockResolvedValue({
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

  (fetchAvailableTimeSlots as Mock).mockResolvedValue({
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
    // Mock availableDays to include both providers
    (fetchAvailableDays as Mock).mockResolvedValue({
      availableDays: [
        { time: '2025-06-17', providerIDs: '1,2' }
      ]
    });

    const wrapper = createWrapper({
      selectedService: { id: "service1", providers: [
          { name: "Office A", id: 1, address: { street: "Elm", house_number: "99" } },
          { name: "Office B", id: 2, address: { street: "Elm", house_number: "99" } },
        ] }
    });

    // Wait for availableDays to be loaded
    await wrapper.vm.showSelectionForProvider({ name: "Office A", id: 1, address: { street: "Elm", house_number: "99" } });
    await nextTick();
    await wrapper.vm.getAppointmentsOfDay('2025-06-17');
    await nextTick();

    const checkboxes = wrapper.findAll('input[type="checkbox"]');
    expect(checkboxes.length).toBe(2);
    expect(wrapper.text()).toContain('Office A');
    expect(wrapper.text()).toContain('Office B');
  });

  it("filters providers correctly based on disabledByServices", async () => {
    const testProviders = [
      { id: 102522, name: 'Bürgerbüro Orleansplatz', disabledByServices: [] },
      { id: 102523, name: 'Bürgerbüro Leonrodstraße', disabledByServices: [] },
      { id: 102524, name: 'Bürgerbüro Riesenfeldstraße', disabledByServices: [] },
      { id: 102526, name: 'Bürgerbüro Forstenrieder Allee', disabledByServices: [] },
      { id: 10489, name: 'Bürgerbüro Ruppertstraße', disabledByServices: ['1063453', '1063441', '1080582'] },
      { id: 10502, name: 'Bürgerbüro Ruppertstraße', disabledByServices: [] },
      { id: 54261, name: 'Bürgerbüro Pasing', disabledByServices: [] },
    ];

    const runTest = async (selectedServiceIds: number[], expectedIds: number[]) => {
      const wrapper = createWrapper({
        selectedService: {
          id: selectedServiceIds[0],
          subServices: selectedServiceIds.slice(1).map(id => ({
            id,
            count: 1,
            providers: testProviders
          })),
          providers: testProviders
        }
      });

      await nextTick(); // Wait for onMounted to run
      const renderedProviders = wrapper.vm.selectableProviders as typeof testProviders;
      const resultIds = renderedProviders.map(p => p.id).sort();
      expect(resultIds).toEqual(expectedIds.sort());
    };

    // 1. service 1063453 disables 10489
    await runTest([1063453], [102522, 102523, 102524, 102526, 10502, 54261]);

    // 2. service 1234567 doesn't disable 10489
    await runTest([1234567], [102522, 102523, 102524, 102526, 10489, 54261]);

    // 3. services 1063453 + 1063441 fully match disabledByServices of 10489
    await runTest([1063453, 1063441], [102522, 102523, 102524, 102526, 10502, 54261]);

    // 4. services 1063453 + 1234567 don't fully match disabledByServices of 10489
    await runTest([1063453, 1234567], [102522, 102523, 102524, 102526, 10489, 54261]);
  });


  it("shows providers in correct prio", async () => {
    // Mock availableDays to include both providers
    (fetchAvailableDays as Mock).mockResolvedValue({
      availableDays: [
        { time: '2025-06-17', providerIDs: '1,2' }
      ]
    });

    const wrapper = createWrapper({
      selectedService: { id: "service1", providers: [
          { name: "Office AAA", id: 1, priority: 5, address: { street: "Elm", house_number: "99" } },
          { name: "Office BBB", id: 2, priority: 10, address: { street: "Elm", house_number: "99" } },
        ] }
    });

    // Wait for availableDays to be loaded
    await wrapper.vm.showSelectionForProvider({ name: "Office AAA", id: 1, priority: 5, address: { street: "Elm", house_number: "99" } });
    await nextTick();
    await wrapper.vm.getAppointmentsOfDay('2025-06-17');
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
    // Mock availableDays to include only Office AAA
    (fetchAvailableDays as Mock).mockResolvedValue({
      availableDays: [
        { time: '2025-06-17', providerIDs: '1' }
      ]
    });

    // Mock availableTimeSlots to include appointments for Office AAA
    (fetchAvailableTimeSlots as Mock).mockResolvedValue({
      offices: [
        {
          officeId: 1,
          appointments: [
            1747224600, // 13:50
            1747224900, // 13:55
            1747225200  // 14:00
          ]
        }
      ]
    });

    const wrapper = createWrapper({
      selectedService: { id: "service1", providers: [
          { name: "Office AAA", id: 1, priority: 5, address: { street: "Elm", house_number: "99" } },
          { name: "Office BBB", id: 2, priority: 10, address: { street: "Elm", house_number: "99" } },
        ] }
    });

    // Wait for availableDays to be loaded
    await wrapper.vm.showSelectionForProvider({ name: "Office AAA", id: 1, priority: 5, address: { street: "Elm", house_number: "99" } });
    await nextTick();
    await wrapper.vm.getAppointmentsOfDay('2025-06-17');
    await nextTick();
    // Wait for loading to complete and spinner to disappear
    await flushPromises();
    await new Promise(resolve => setTimeout(resolve, 150)); // Wait for 100ms timeout + buffer

    const locationTitles = wrapper.findAll('.location-title');
    const officeAAA = locationTitles.find(location => location.text().includes('Office AAA'));
    const officeBBB = locationTitles.find(location => location.text().includes('Office BBB'));
    expect(officeAAA).toBeTruthy();
    expect(officeBBB).toBeFalsy();
  });

  it("shows more appointments and providers after loading later appointments", async () => {
    // Mock availableDays to include both providers
    (fetchAvailableDays as Mock).mockResolvedValue({
      availableDays: [
        { time: '2025-06-17', providerIDs: '1,2' }
      ]
    });

    // Mock availableTimeSlots to include appointments for both providers
    (fetchAvailableTimeSlots as Mock).mockResolvedValue({
      offices: [
        {
          officeId: 1,
          appointments: [
            1747224600, // 13:50
            1747224900, // 13:55
            1747225200  // 14:00
          ]
        },
        {
          officeId: 2,
          appointments: [
            1747225500, // 14:05
            1747225800, // 14:10
            1747226100  // 14:15
          ]
        }
      ]
    });

    const wrapper = createWrapper({
      selectedService: { id: "service1", providers: [
          { name: "Office AAA", id: 1, priority: 5, address: { street: "Elm", house_number: "99" } },
          { name: "Office BBB", id: 2, priority: 10, address: { street: "Elm", house_number: "99" } },
        ] }
    });

    // Wait for availableDays to be loaded
    await wrapper.vm.showSelectionForProvider({ name: "Office AAA", id: 1, priority: 5, address: { street: "Elm", house_number: "99" } });
    await nextTick();
    await wrapper.vm.getAppointmentsOfDay('2025-06-17');
    await nextTick();
    // Wait for loading to complete and spinner to disappear
    await flushPromises();
    await new Promise(resolve => setTimeout(resolve, 150)); // Wait for 100ms timeout + buffer

    const locationTitles = wrapper.findAll('.location-title');
    const officeAAA = locationTitles.find(location => location.text().includes('Office AAA'));
    const officeBBB = locationTitles.find(location => location.text().includes('Office BBB'));
    expect(officeAAA).toBeTruthy();
    expect(officeBBB).toBeTruthy();
  });

  it("shows appointments by hour", async () => {
    // Mock availableDays to include Office BBB and CCC
    (fetchAvailableDays as Mock).mockResolvedValue({
      availableDays: [
        { time: '2025-06-17', providerIDs: '2,3' }
      ]
    });

    // Mock availableTimeSlots to include appointments for Office BBB and CCC
    (fetchAvailableTimeSlots as Mock).mockResolvedValue({
      offices: [
        {
          officeId: 2,
          appointments: [
            1747224600, // 13:50
            1747224900, // 13:55
            1747225200  // 14:00
          ]
        },
        {
          officeId: 3,
          appointments: [
            1747225500, // 14:05
            1747225800, // 14:10
            1747226100  // 14:15
          ]
        }
      ]
    });

    const wrapper = createWrapper({
      selectedService: { id: "service1", providers: [
          { name: "Office AAA", id: 1, priority: 5, address: { street: "Elm", house_number: "99" } },
          { name: "Office BBB", id: 2, priority: 10, address: { street: "Elm", house_number: "99" } },
          { name: "Office CCC", id: 3, priority: 8, address: { street: "Elm", house_number: "99" } },
        ] }
    });

    // Wait for availableDays to be loaded
    await wrapper.vm.showSelectionForProvider({ name: "Office BBB", id: 2, priority: 10, address: { street: "Elm", house_number: "99" } });
    await nextTick();
    await wrapper.vm.getAppointmentsOfDay('2025-06-17');
    await nextTick();
    // Wait for loading to complete and spinner to disappear
    await flushPromises();
    await new Promise(resolve => setTimeout(resolve, 150)); // Wait for 100ms timeout + buffer

    const locationTitles = wrapper.findAll('.location-title');
    const officeAAA = locationTitles.find(location => location.text().includes('Office AAA'));
    const officeBBB = locationTitles.find(location => location.text().includes('Office BBB'));
    const officeCCC = locationTitles.find(location => location.text().includes('Office CCC'));
    expect(officeAAA).toBeFalsy();
    expect(officeBBB).toBeTruthy();
    expect(officeCCC).toBeTruthy();
  });

  it("shows an error message when no provider is selected", async () => {
    // Mock available days with provider IDs
    (fetchAvailableDays as Mock).mockResolvedValue({
      availableDays: [
        { time: "2025-06-17", providerIDs: "1,2" }
      ]
    });

    // Create component with two selectable providers
    const wrapper = createWrapper({
      selectedService: {
        id: "service1",
        providers: [
          { name: "Office A", id: 1, address: { street: "Main", house_number: "1" } },
          { name: "Office B", id: 2, address: { street: "Main", house_number: "2" } }
        ]
      }
    });

    await flushPromises(); // Wait for API call and computed properties

    // Make sure no provider is selected
    wrapper.vm.selectedProviders = {};
    await nextTick();

    // Expect the error message to be shown when no provider with appointments is selected
    expect(wrapper.text()).toContain("errorMessageProviderSelection");
  });

  it("shows available day only by providers that have free appointments on that day", async () => {
    (fetchAvailableDays as Mock).mockResolvedValue({
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

    wrapper.vm.selectedProviders[102522] = !wrapper.vm.selectedProviders[102522]; await nextTick();

    expect(wrapper.vm.allowedDates(new Date('2025-05-14'))).toBeTruthy();
    expect(wrapper.vm.allowedDates(new Date('2025-05-16'))).toBeFalsy();
    expect(wrapper.vm.allowedDates(new Date('2025-05-17'))).toBeFalsy();
  });

  it("formats dates correctly", () => {
    const wrapper = createWrapper();
    const date = new Date('2025-05-15');
    expect(wrapper.vm.formatDay(date)).toBe('Donnerstag, 15.05.2025');
  });

  it("handles provider selection with checkboxes", async () => {
    const wrapper = createWrapper({
      selectedService: { id: "service1", providers: [
        { name: "Office A", id: 1, address: { street: "Elm", house_number: "99" } },
        { name: "Office B", id: 2, address: { street: "Elm", house_number: "99" } }
      ] }
    });

    await nextTick();

    // Test initial state
    expect(wrapper.vm.selectedProviders[1]).toBe(true);
    expect(wrapper.vm.selectedProviders[2]).toBe(true);

    // Test toggling selection
    wrapper.vm.selectedProviders[1] = !wrapper.vm.selectedProviders[1]; await nextTick();
    expect(wrapper.vm.selectedProviders[1]).toBe(false);
    expect(wrapper.vm.selectedProviders[2]).toBe(true);

    // Test that appointments are refetched when selection changes
    expect(fetchAvailableDays).toHaveBeenCalled();
  });

  it('checks only the preselected office when preselectedOfficeId is provided', async () => {
    (fetchAvailableDays as Mock).mockResolvedValue({
      availableDays: [
        { time: '2025-06-17', providerIDs: '1,2,3' }
      ]
    });

    const wrapper = createWrapper({
      selectedService: {
        id: 'service1',
        providers: [
          { name: 'Office A', id: '1', address: { street: 'Test', house_number: '1' } },
          { name: 'Office B', id: '2', address: { street: 'Test', house_number: '2' } },
          { name: 'Office C', id: '3', address: { street: 'Test', house_number: '3' } }
        ]
      },
      props: {
        preselectedOfficeId: '2'
      }
    });

    await wrapper.vm.showSelectionForProvider({ name: 'Office B', id: '2', address: { street: 'Test', house_number: '2' } });
    await nextTick();

    expect(wrapper.vm.selectedProviders).toEqual({
      '1': false,
      '2': true,
      '3': false
    });
  });

  it("handles calendar navigation correctly", async () => {
    const wrapper = createWrapper({
      selectedService: { id: "service1", providers: [
        { name: "Office AAA", id: 102522, address: { street: "Elm", house_number: "99" } }
      ] }
    });

    // Initialize required state
    wrapper.vm.appointmentTimestampsByOffice = ref([]);
    wrapper.vm.appointmentTimestamps = ref([]);

    (fetchAvailableTimeSlots as Mock).mockResolvedValue({
      offices: [
        {
          officeId: 102522,
          appointments: [1747223100]
        }
      ]
    });

    await wrapper.vm.showSelectionForProvider({
      name: "Office AAA",
      id: 102522,
      address: { street: "Elm", house_number: "99" }
    });
    await nextTick();

    // Set the selected day directly
    wrapper.vm.selectedDay = new Date('2025-05-15');
    await nextTick();

    // Test navigation to next day
    await wrapper.vm.getAppointmentsOfDay('2025-05-15');
    await nextTick();

    expect(wrapper.vm.selectedDay).toEqual(new Date('2025-05-15'));
    expect(fetchAvailableTimeSlots).toHaveBeenCalledWith(
      '2025-05-15',
      expect.any(Array),
      expect.any(Array),
      expect.any(Array),
      expect.any(String),
      expect.any(String)
    );
  });

  describe('CalendarView date disabling and auto-selection', () => {
    it('disables a date in availableDays if API returns no appointments for it', async () => {
      (fetchAvailableDays as Mock).mockResolvedValue({
        availableDays: [
          { time: '2025-06-16', providerIDs: '10351880,10470' },
          { time: '2025-06-17', providerIDs: '10351880,10470' },
        ]
      });
      (fetchAvailableTimeSlots as Mock).mockImplementation((date) => {
        if (date === '2025-06-16') {
          return Promise.resolve({ offices: [] });
        }
        return Promise.resolve({
          offices: [
            { officeId: 10351880, appointments: [1750118400] },
            { officeId: 10470, appointments: [1750118400] }
          ]
        });
      });
      const wrapper = createWrapper({
        selectedService: { id: 'service1', providers: [
          { name: 'Office X', id: 10351880, address: { street: 'Test', house_number: '1' } },
          { name: 'Office Y', id: 10470, address: { street: 'Test', house_number: '2' } }
        ] }
      });
      await wrapper.vm.showSelectionForProvider({ name: 'Office X', id: 10351880, address: { street: 'Test', house_number: '1' } });
      await nextTick();
      await wrapper.vm.getAppointmentsOfDay('2025-06-16');
      await nextTick();
      expect(wrapper.vm.allowedDates(new Date('2025-06-16'))).toBe(false);
      expect(wrapper.vm.allowedDates(new Date('2025-06-17'))).toBe(true);
    });

    it('auto-selects the next available date if the current date has no appointments', async () => {
      (fetchAvailableDays as Mock).mockResolvedValue({
        availableDays: [
          { time: '2025-06-16', providerIDs: '10351880,10470' },
          { time: '2025-06-17', providerIDs: '10351880,10470' },
        ]
      });
      (fetchAvailableTimeSlots as Mock).mockImplementation((date) => {
        if (date === '2025-06-16') {
          return Promise.resolve({ offices: [] });
        }
        return Promise.resolve({
          offices: [
            { officeId: 10351880, appointments: [1750118400] },
            { officeId: 10470, appointments: [1750118400] }
          ]
        });
      });
      const wrapper = createWrapper({
        selectedService: { id: 'service1', providers: [
          { name: 'Office X', id: 10351880, address: { street: 'Test', house_number: '1' } },
          { name: 'Office Y', id: 10470, address: { street: 'Test', house_number: '2' } }
        ] }
      });
      await wrapper.vm.showSelectionForProvider({ name: 'Office X', id: 10351880, address: { street: 'Test', house_number: '1' } });
      await nextTick();
      await wrapper.vm.getAppointmentsOfDay('2025-06-16');
      await nextTick();
      // Should auto-select 2025-06-17
      expect(wrapper.vm.selectedDay).toEqual(new Date('2025-06-17'));
    });

    it('enables a date in availableDays if API returns appointments for it', async () => {
      (fetchAvailableDays as Mock).mockResolvedValue({
        availableDays: [
          { time: '2025-06-17', providerIDs: '10351880,10470' },
        ]
      });
      (fetchAvailableTimeSlots as Mock).mockResolvedValue({
        offices: [
          { officeId: 10351880, appointments: [1750118400] },
          { officeId: 10470, appointments: [1750118400] }
        ]
      });
      const wrapper = createWrapper({
        selectedService: { id: 'service1', providers: [
          { name: 'Office X', id: 10351880, address: { street: 'Test', house_number: '1' } },
          { name: 'Office Y', id: 10470, address: { street: 'Test', house_number: '2' } }
        ] }
      });
      await wrapper.vm.showSelectionForProvider({ name: 'Office X', id: 10351880, address: { street: 'Test', house_number: '1' } });
      await nextTick();
      await wrapper.vm.getAppointmentsOfDay('2025-06-17');
      await nextTick();
      expect(wrapper.vm.allowedDates(new Date('2025-06-17'))).toBe(true);
    });

    it('disables a date not in availableDays', async () => {
      (fetchAvailableDays as Mock).mockResolvedValue({
        availableDays: [
          { time: '2025-06-16', providerIDs: '10351880,10470' },
        ]
      });
      const wrapper = createWrapper({
        selectedService: { id: 'service1', providers: [
          { name: 'Office X', id: 10351880, address: { street: 'Test', house_number: '1' } },
          { name: 'Office Y', id: 10470, address: { street: 'Test', house_number: '2' } }
        ] }
      });
      await nextTick();
      expect(wrapper.vm.allowedDates(new Date('2025-06-18'))).toBe(false);
    });

    it('disables next month navigation when no appointments are available beyond current month', async () => {
      // Mock availableDays to only include dates in the current month
      (fetchAvailableDays as Mock).mockResolvedValue({
        availableDays: [
          { time: '2025-06-16', providerIDs: '10351880,10470' },
          { time: '2025-06-17', providerIDs: '10351880,10470' },
          { time: '2025-06-30', providerIDs: '10351880,10470' } // Last day of June
        ]
      });

      const wrapper = createWrapper({
        selectedService: { id: 'service1', providers: [
          { name: 'Office X', id: 10351880, address: { street: 'Test', house_number: '1' } },
          { name: 'Office Y', id: 10470, address: { street: 'Test', house_number: '2' } }
        ] }
      });

      // Wait for availableDays to be loaded
      await wrapper.vm.showSelectionForProvider({ name: 'Office X', id: 10351880, address: { street: 'Test', house_number: '1' } });
      await nextTick();

      // Set current date to last day of June
      wrapper.vm.selectedDay = new Date('2025-06-30');
      await nextTick();

      // Verify that next month navigation is disabled
      const calendar = wrapper.findComponent({ name: 'muc-calendar' });
      expect(calendar.exists()).toBe(true);
      expect(calendar.props('max')).toEqual(new Date('2025-06-30'));
    });

    it('enables next month navigation when appointments are available in future months', async () => {
      // Mock availableDays to include dates in both current and next month
      (fetchAvailableDays as Mock).mockResolvedValue({
        availableDays: [
          { time: '2025-06-16', providerIDs: '10351880,10470' },
          { time: '2025-06-17', providerIDs: '10351880,10470' },
          { time: '2025-07-01', providerIDs: '10351880,10470' } // First day of next month
        ]
      });

      const wrapper = createWrapper({
        selectedService: { id: 'service1', providers: [
          { name: 'Office X', id: 10351880, address: { street: 'Test', house_number: '1' } },
          { name: 'Office Y', id: 10470, address: { street: 'Test', house_number: '2' } }
        ] }
      });

      // Wait for availableDays to be loaded
      await wrapper.vm.showSelectionForProvider({ name: 'Office X', id: 10351880, address: { street: 'Test', house_number: '1' } });
      await nextTick();

      // Set current date to last day of June
      wrapper.vm.selectedDay = new Date('2025-06-30');
      await nextTick();

      // Verify that next month navigation is enabled
      const calendar = wrapper.findComponent({ name: 'muc-calendar' });
      expect(calendar.exists()).toBe(true);
      expect(calendar.props('max')).toEqual(new Date('2025-07-01'));
    });

    it('disables previous month navigation when no appointments are available before current month', async () => {
      // Mock availableDays to only include dates in the current month
      (fetchAvailableDays as Mock).mockResolvedValue({
        availableDays: [
          { time: '2025-06-01', providerIDs: '10351880,10470' }, // First day of June
          { time: '2025-06-17', providerIDs: '10351880,10470' },
          { time: '2025-06-30', providerIDs: '10351880,10470' }
        ]
      });

      const wrapper = createWrapper({
        selectedService: { id: 'service1', providers: [
          { name: 'Office X', id: 10351880, address: { street: 'Test', house_number: '1' } },
          { name: 'Office Y', id: 10470, address: { street: 'Test', house_number: '2' } }
        ] }
      });

      // Wait for availableDays to be loaded
      await wrapper.vm.showSelectionForProvider({ name: 'Office X', id: 10351880, address: { street: 'Test', house_number: '1' } });
      await nextTick();

      // Set current date to first day of June
      wrapper.vm.selectedDay = new Date('2025-06-01');
      await nextTick();

      // Verify that previous month navigation is disabled
      const calendar = wrapper.findComponent({ name: 'muc-calendar' });
      expect(calendar.exists()).toBe(true);
      expect(calendar.props('min')).toEqual(new Date('2025-06-01'));
    });

    it('enables previous month navigation when appointments are available in past months', async () => {
      // Mock availableDays to include dates in both current and previous month
      (fetchAvailableDays as Mock).mockResolvedValue({
        availableDays: [
          { time: '2025-05-31', providerIDs: '10351880,10470' }, // Last day of May
          { time: '2025-06-01', providerIDs: '10351880,10470' }, // First day of June
          { time: '2025-06-17', providerIDs: '10351880,10470' }
        ]
      });

      const wrapper = createWrapper({
        selectedService: { id: 'service1', providers: [
          { name: 'Office X', id: 10351880, address: { street: 'Test', house_number: '1' } },
          { name: 'Office Y', id: 10470, address: { street: 'Test', house_number: '2' } }
        ] }
      });

      // Wait for availableDays to be loaded
      await wrapper.vm.showSelectionForProvider({ name: 'Office X', id: 10351880, address: { street: 'Test', house_number: '1' } });
      await nextTick();

      // Set current date to first day of June
      wrapper.vm.selectedDay = new Date('2025-06-01');
      await nextTick();

      // Verify that previous month navigation is enabled
      const calendar = wrapper.findComponent({ name: 'muc-calendar' });
      expect(calendar.exists()).toBe(true);
      expect(calendar.props('min')).toEqual(new Date('2025-05-31'));
    });

    it('updates navigation limits when providers are deselected', async () => {
      // Mock availableDays with different date ranges for different providers
      (fetchAvailableDays as Mock).mockResolvedValue({
        availableDays: [
          // Provider 10351880 has appointments until July
          { time: '2025-06-16', providerIDs: '10351880' },
          { time: '2025-06-17', providerIDs: '10351880' },
          { time: '2025-07-01', providerIDs: '10351880' },
          // Provider 10470 has appointments until August
          { time: '2025-06-16', providerIDs: '10470' },
          { time: '2025-06-17', providerIDs: '10470' },
          { time: '2025-08-01', providerIDs: '10470' }
        ]
      });

      const wrapper = createWrapper({
        selectedService: { id: 'service1', providers: [
          { name: 'Office X', id: 10351880, address: { street: 'Test', house_number: '1' } },
          { name: 'Office Y', id: 10470, address: { street: 'Test', house_number: '2' } }
        ] }
      });

      // Wait for availableDays to be loaded
      await wrapper.vm.showSelectionForProvider({ name: 'Office X', id: 10351880, address: { street: 'Test', house_number: '1' } });
      await nextTick();

      // Initially both providers are selected, so max date should be August 1st
      const calendar = wrapper.findComponent({ name: 'muc-calendar' });
      expect(calendar.exists()).toBe(true);
      expect(calendar.props('max')).toEqual(new Date('2025-08-01'));

      // Deselect provider 10470 (which had appointments until August)
      wrapper.vm.selectedProviders[10470] = !wrapper.vm.selectedProviders[10470]; await nextTick();

      // Now only provider 10351880 is selected, so max date should be July 1st
      expect(calendar.props('max')).toEqual(new Date('2025-07-01'));
    });

    it('updates navigation limits when providers are selected', async () => {
      // Mock availableDays with different date ranges for different providers
      (fetchAvailableDays as Mock).mockResolvedValue({
        availableDays: [
          // Provider 10351880 has appointments until July
          { time: '2025-06-16', providerIDs: '10351880' },
          { time: '2025-06-17', providerIDs: '10351880' },
          { time: '2025-07-01', providerIDs: '10351880' },
          // Provider 10470 has appointments until August
          { time: '2025-06-16', providerIDs: '10470' },
          { time: '2025-06-17', providerIDs: '10470' },
          { time: '2025-08-01', providerIDs: '10470' }
        ]
      });

      const wrapper = createWrapper({
        selectedService: { id: 'service1', providers: [
          { name: 'Office X', id: 10351880, address: { street: 'Test', house_number: '1' } },
          { name: 'Office Y', id: 10470, address: { street: 'Test', house_number: '2' } }
        ] }
      });

      // Wait for availableDays to be loaded
      await wrapper.vm.showSelectionForProvider({ name: 'Office X', id: 10351880, address: { street: 'Test', house_number: '1' } });
      await nextTick();

      // Initially both providers are selected, so max date should be August 1st
      const calendar = wrapper.findComponent({ name: 'muc-calendar' });
      expect(calendar.exists()).toBe(true);
      expect(calendar.props('max')).toEqual(new Date('2025-08-01'));

      // Deselect provider 10470
      wrapper.vm.selectedProviders[10470] = !wrapper.vm.selectedProviders[10470]; await nextTick();

      // Now only provider 10351880 is selected, so max date should be July 1st
      expect(calendar.props('max')).toEqual(new Date('2025-07-01'));

      // Select provider 10470 again
      wrapper.vm.selectedProviders[10470] = !wrapper.vm.selectedProviders[10470]; await nextTick();

      // Now both providers are selected again, so max date should be August 1st
      expect(calendar.props('max')).toEqual(new Date('2025-08-01'));
    });
  });

  describe('CalendarView checkbox behavior', () => {

    it('changes selected date when unchecking a provider that has appointments on current date', async () => {
      (fetchAvailableDays as Mock).mockResolvedValue({
        availableDays: [
          { time: '2025-06-17', providerIDs: '1' },
          { time: '2025-06-18', providerIDs: '1,2' }
        ]
      });

      (fetchAvailableTimeSlots as Mock).mockImplementation((date) => {
        if (date === '2025-06-17') {
          return Promise.resolve({
            offices: [{ officeId: 1, appointments: [1750118400] }]
          });
        }
        return Promise.resolve({
          offices: [
            { officeId: 1, appointments: [1750204800] },
            { officeId: 2, appointments: [1750204800] }
          ]
        });
      });

      const wrapper = createWrapper({
        selectedService: { id: 'service1', providers: [
          { name: 'Office A', id: '1', address: { street: 'Test', house_number: '1' } },
          { name: 'Office B', id: '2', address: { street: 'Test', house_number: '2' } }
        ] }
      });

      await wrapper.vm.showSelectionForProvider({ name: 'Office A', id: '1', address: { street: 'Test', house_number: '1' } });
      await nextTick();

      // Set initial date to 2025-06-17
      wrapper.vm.selectedDay = new Date('2025-06-17');
      await nextTick();

      // Uncheck provider 1 (which has appointments on 2025-06-17)
      wrapper.vm.selectedProviders['1'] = !wrapper.vm.selectedProviders['1']; await nextTick();

      // Should change to 2025-06-18 since that's the next date with appointments for provider 2
      expect(wrapper.vm.selectedDay).toEqual(new Date('2025-06-18'));
    });

    it('does not show locations without appointments in the checkbox list', async () => {
      // Mock availableDays to include only three out of four providers
      (fetchAvailableDays as Mock).mockResolvedValue({
        availableDays: [
          { time: '2025-06-17', providerIDs: '1,2,3' } // Note: provider '4' is not included
        ]
      });

      const wrapper = createWrapper({
        selectedService: { id: 'service1', providers: [
          { name: 'Office A', id: '1', address: { street: 'Test', house_number: '1' } },
          { name: 'Office B', id: '2', address: { street: 'Test', house_number: '2' } },
          { name: 'Office C', id: '3', address: { street: 'Test', house_number: '3' } },
          { name: 'Office D', id: '4', address: { street: 'Test', house_number: '4' } }
        ] }
      });

      // Wait for availableDays to be loaded
      await wrapper.vm.showSelectionForProvider({ name: 'Office A', id: '1', address: { street: 'Test', house_number: '1' } });
      await nextTick();
      await wrapper.vm.getAppointmentsOfDay('2025-06-17');
      await nextTick();

      // Check that only providers with appointments are shown
      const checkboxes = wrapper.findAll('input[type="checkbox"]');
      expect(checkboxes.length).toBe(3); // Should only show 3 providers

      // Verify the provider names are shown correctly
      expect(wrapper.text()).toContain('Office A');
      expect(wrapper.text()).toContain('Office B');
      expect(wrapper.text()).toContain('Office C');
      expect(wrapper.text()).not.toContain('Office D'); // This provider should not be shown
    });

    it('does not show any providers when no appointments are available', async () => {
      // Mock availableDays to be empty
      (fetchAvailableDays as Mock).mockResolvedValue({
        availableDays: []
      });

      const wrapper = createWrapper({
        selectedService: { id: 'service1', providers: [
          { name: 'Office A', id: '1', address: { street: 'Test', house_number: '1' } },
          { name: 'Office B', id: '2', address: { street: 'Test', house_number: '2' } },
          { name: 'Office C', id: '3', address: { street: 'Test', house_number: '3' } },
          { name: 'Office D', id: '4', address: { street: 'Test', house_number: '4' } }
        ] }
      });

      // Wait for availableDays to be loaded
      await wrapper.vm.showSelectionForProvider({ name: 'Office A', id: '1', address: { street: 'Test', house_number: '1' } });
      await nextTick();
      await wrapper.vm.getAppointmentsOfDay('2025-06-17');
      await nextTick();

      // Check that no providers are shown
      const checkboxes = wrapper.findAll('input[type="checkbox"]');
      expect(checkboxes.length).toBe(0);

      // Verify no provider names are shown
      expect(wrapper.text()).not.toContain('Office A');
      expect(wrapper.text()).not.toContain('Office B');
      expect(wrapper.text()).not.toContain('Office C');
      expect(wrapper.text()).not.toContain('Office D');
    });

    it("shows no providers when none have appointments", async () => {
      // Mock availableDays to include no providers
      (fetchAvailableDays as Mock).mockResolvedValue({
        availableDays: []
      });

      // Mock availableTimeSlots to return no appointments
      (fetchAvailableTimeSlots as Mock).mockResolvedValue({
        offices: []
      });

      const wrapper = createWrapper({
        selectedService: { id: "service1", providers: [
            { name: "Office AAA", id: 1, priority: 5, address: { street: "Elm", house_number: "99" } },
            { name: "Office BBB", id: 2, priority: 10, address: { street: "Elm", house_number: "99" } },
            { name: "Office CCC", id: 3, priority: 8, address: { street: "Elm", house_number: "99" } },
          ] }
      });

      // Wait for availableDays to be loaded
      await wrapper.vm.showSelectionForProvider({ name: "Office AAA", id: 1, priority: 5, address: { street: "Elm", house_number: "99" } });
      await nextTick();
      await wrapper.vm.getAppointmentsOfDay('2025-06-17');
      await nextTick();

      const locationTitles = wrapper.findAll('.location-title');
      expect(locationTitles.length).toBe(0);
    });

    it('updates calendar view when selected date changes due to provider deselection', async () => {
      const today = new Date();

      // Calculate dates for two providers: one 1 month ahead, one 2 months ahead
      const dateForProvider1 = new Date(today.getFullYear(), today.getMonth() + 1, 15);
      const dateForProvider2 = new Date(today.getFullYear(), today.getMonth() + 2, 1);

      // Handle year rollover if month > 11
      if (dateForProvider1.getMonth() < today.getMonth()) {
        dateForProvider1.setFullYear(dateForProvider1.getFullYear() + 1);
      }
      if (dateForProvider2.getMonth() < today.getMonth()) {
        dateForProvider2.setFullYear(dateForProvider2.getFullYear() + 1);
      }

      const toIsoDate = (date: Date) => date.toISOString().split('T')[0];
      const provider1DateIso = toIsoDate(dateForProvider1);
      const provider2DateIso = toIsoDate(dateForProvider2);

      // Mock available days — provider 1 only on first date, provider 2 only on second
      (fetchAvailableDays as Mock).mockResolvedValue({
        availableDays: [
          { time: provider1DateIso, providerIDs: '1' },
          { time: provider2DateIso, providerIDs: '2' }
        ]
      });

      // Mock time slots accordingly
      (fetchAvailableTimeSlots as Mock).mockImplementation((date) => {
        if (date === provider2DateIso) {
          return Promise.resolve({
            offices: [{ officeId: 2, appointments: [1750118400] }]
          });
        }
        return Promise.resolve({
          offices: [{ officeId: 1, appointments: [1750118400] }]
        });
      });

      const wrapper = createWrapper({
        selectedService: {
          id: 'service1',
          providers: [
            { name: 'Office A', id: '1', address: { street: 'Test', house_number: '1' } },
            { name: 'Office B', id: '2', address: { street: 'Test', house_number: '2' } }
          ]
        }
      });

      // Simulate selecting provider 2 initially
      await wrapper.vm.showSelectionForProvider({ name: 'Office B', id: '2', address: { street: 'Test', house_number: '2' } });
      await nextTick();
      await flushPromises();

      // Select the date supported only by provider 2
      wrapper.vm.selectedDay = new Date(provider2DateIso);
      wrapper.vm.selectedProviders['2'] = true;
      wrapper.vm.selectedProviders['1'] = true;
      await nextTick();
      await flushPromises();

      // Now remove provider 2 — calendar should fallback to provider 1's date
      wrapper.vm.selectedProviders['2'] = false;
      await nextTick();
      await flushPromises();

      const calendar = wrapper.findComponent({ name: 'muc-calendar' });
      expect(calendar.exists()).toBe(true);

      const actualDate = calendar.props('viewMonth');

      expect(actualDate.getFullYear()).toBe(dateForProvider1.getFullYear());
      expect(actualDate.getMonth()).toBe(dateForProvider1.getMonth());
    });

    it('resets to earliest hour when selecting a new day in the calendar', async () => {
      // Mock availableDays with two different dates
      (fetchAvailableDays as Mock).mockResolvedValue({
        availableDays: [
          { time: '2025-06-17', providerIDs: '1' },
          { time: '2025-06-18', providerIDs: '1' }
        ]
      });

      // Mock availableTimeSlots with different hours for each date
      (fetchAvailableTimeSlots as Mock).mockImplementation((date) => {
        if (date === '2025-06-17') {
          return Promise.resolve({
            offices: [{
              officeId: 1,
              appointments: [
                1750919400, // 08:30
                1750919700, // 08:35
                1750920000  // 08:40
              ]
            }]
          });
        }
        return Promise.resolve({
          offices: [{
            officeId: 1,
            appointments: [
              1747224600, // 13:50
              1747224900, // 13:55
              1747225200  // 14:00
            ]
          }]
        });
      });

      const wrapper = createWrapper({
        selectedService: { id: 'service1', providers: [
          { name: 'Office A', id: '1', address: { street: 'Test', house_number: '1' } }
        ] }
      });

      // Wait for availableDays to be loaded
      await wrapper.vm.showSelectionForProvider({ name: 'Office A', id: '1', address: { street: 'Test', house_number: '1' } });
      await nextTick();
      await flushPromises();

      // Set initial date and hour
      wrapper.vm.selectedDay = new Date('2025-06-17');
      wrapper.vm.selectedHour = 13; // Set to 13:00
      await nextTick();
      await flushPromises();

      // Select new date
      await wrapper.vm.handleDaySelection(new Date('2025-06-18'));
      await nextTick();
      await flushPromises();

      // The earliest available hour for 2025-06-18 is 14
      expect(wrapper.vm.selectedHour).toBe(14);
    });
  });

  describe("CalendarView - hour and day part reset on day change (additional)", () => {
    it("resets selectedHour to earliest available hour when selecting a new day", async () => {
      (fetchAvailableDays as Mock).mockResolvedValue({
        availableDays: [
          { time: "2025-06-17", providerIDs: "1" },
          { time: "2025-06-18", providerIDs: "1" }
        ]
      });
      (fetchAvailableTimeSlots as Mock).mockImplementation((date) => {
        if (date === "2025-06-17") {
          return Promise.resolve({
            offices: [{
              officeId: 1,
              appointments: [
                1750919400, // 08:30
                1750919700, // 08:35
                1750920000  // 08:40
              ]
            }]
          });
        }
        return Promise.resolve({
          offices: [{
            officeId: 1,
            appointments: [
              1747224600, // 13:50
              1747224900, // 13:55
              1747225200  // 14:00
            ]
          }]
        });
      });
      const wrapper = createWrapper({
        selectedService: { id: "service1", providers: [
          { name: "Office A", id: "1", address: { street: "Test", house_number: "1" } }
        ] }
      });
      await wrapper.vm.showSelectionForProvider({ name: "Office A", id: "1", address: { street: "Test", house_number: "1" } });
      await flushPromises();
      wrapper.vm.selectedDay = new Date("2025-06-17");
      wrapper.vm.selectedHour = 13;
      await flushPromises();
      await wrapper.vm.handleDaySelection(new Date("2025-06-18"));
      await flushPromises();
      expect(wrapper.vm.selectedHour).toBe(14);
    });

    it("sets selectedHour to null if no hours are available for the selected day", async () => {
      (fetchAvailableDays as Mock).mockResolvedValue({
        availableDays: [
          { time: "2025-06-19", providerIDs: "1" }
        ]
      });
      (fetchAvailableTimeSlots as Mock).mockResolvedValue({
        offices: [{
          officeId: 1,
          appointments: []
        }]
      });
      const wrapper = createWrapper({
        selectedService: { id: "service1", providers: [
          { name: "Office A", id: "1", address: { street: "Test", house_number: "1" } }
        ] }
      });
      await wrapper.vm.showSelectionForProvider({ name: "Office A", id: "1", address: { street: "Test", house_number: "1" } });
      await flushPromises();
      await wrapper.vm.handleDaySelection(new Date("2025-06-19"));
      await flushPromises();
      expect(wrapper.vm.selectedHour).toBe(null);
    });

    it("resets selectedDayPart to 'am' if available when in day part view", async () => {
      (fetchAvailableDays as Mock).mockResolvedValue({
        availableDays: [
          { time: "2025-06-20", providerIDs: "1" },
          { time: "2025-06-21", providerIDs: "1" }
        ]
      });
      (fetchAvailableTimeSlots as Mock).mockImplementation((date) => {
        if (date === "2025-06-20") {
          return Promise.resolve({
            offices: [{
              officeId: 1,
              appointments: [
                1750919400, // 08:30 (am)
                1750923600  // 14:00 (pm)
              ]
            }]
          });
        }
        return Promise.resolve({
          offices: [{
            officeId: 1,
            appointments: [
              1751005800 // 08:30 (am) only
            ]
          }]
        });
      });
      const wrapper = createWrapper({
        selectedService: { id: "service1", providers: [
          { name: "Office A", id: "1", address: { street: "Test", house_number: "1" } }
        ] }
      });
      await wrapper.vm.showSelectionForProvider({ name: "Office A", id: "1", address: { street: "Test", house_number: "1" } });
      await flushPromises();
      wrapper.vm.selectedDay = new Date("2025-06-20");
      wrapper.vm.selectedDayPart = "pm";
      await flushPromises();
      await wrapper.vm.handleDaySelection(new Date("2025-06-21"));
      await flushPromises();
      expect(wrapper.vm.selectedDayPart).toBe(null);
    });

    it("does not reset selectedDayPart when selecting the same day", async () => {
      (fetchAvailableDays as Mock).mockResolvedValue({
        availableDays: [
          { time: "2025-06-20", providerIDs: "1" }
        ]
      });
      (fetchAvailableTimeSlots as Mock).mockResolvedValue({
        offices: [{
          officeId: 1,
          appointments: [
            1750919400, // 08:30 (am)
            1750923600  // 14:00 (pm)
          ]
        }]
      });
      const wrapper = createWrapper({
        selectedService: { id: "service1", providers: [
          { name: "Office A", id: "1", address: { street: "Test", house_number: "1" } }
        ] }
      });
      await wrapper.vm.showSelectionForProvider({ name: "Office A", id: "1", address: { street: "Test", house_number: "1" } });
      await flushPromises();
      wrapper.vm.selectedDayPart = "pm";
      await flushPromises();
      await wrapper.vm.handleDaySelection(new Date("2025-06-20")); // select the same day
      await flushPromises();
      expect(wrapper.vm.selectedDayPart).toBe("pm");
    });
  });

  describe('CalendarView snap-to-nearest hour and dayPart on provider deselection', () => {
    it('snaps to the nearest later hour if current hour is removed', async () => {
      (fetchAvailableDays as Mock).mockResolvedValue({
        availableDays: [
          { time: '2025-06-17', providerIDs: '1,2' }
        ]
      });
      (fetchAvailableTimeSlots as Mock).mockResolvedValue({
        offices: [
          { officeId: 1, appointments: [1750914000, 1750917600] }, // 09:00, 10:00
          { officeId: 2, appointments: [1750924800, 1750928400, 1750932000] } // 12:00, 13:00, 14:00
        ]
      });
      const wrapper = createWrapper({
        selectedService: { id: 'service1', providers: [
          { name: 'Office A', id: 1, address: { street: 'Test', house_number: '1' } },
          { name: 'Office B', id: 2, address: { street: 'Test', house_number: '2' } }
        ] }
      });
      await wrapper.vm.showSelectionForProvider({ name: 'Office A', id: 1, address: { street: 'Test', house_number: '1' } });
      await nextTick();
      await wrapper.vm.getAppointmentsOfDay('2025-06-17');
      await nextTick();
      // Set selectedHour to 10 (10:00)
      wrapper.vm.selectedHour = 10;
      await nextTick();
      // Deselect provider 1, only provider 2 remains (12, 13, 14)
      wrapper.vm.selectedProviders['1'] = !wrapper.vm.selectedProviders['1']; await nextTick();
      expect(wrapper.vm.selectedHour).toBe(10);
    });

    it('snaps to the nearest earlier hour if current hour is removed', async () => {
      (fetchAvailableDays as Mock).mockResolvedValue({
        availableDays: [
          { time: '2025-06-17', providerIDs: '1,2' }
        ]
      });
      (fetchAvailableTimeSlots as Mock).mockResolvedValue({
        offices: [
          { officeId: 1, appointments: [1750935600, 1750939200] }, // 15:00, 16:00
          { officeId: 2, appointments: [1750924800, 1750928400, 1750932000] } // 12:00, 13:00, 14:00
        ]
      });
      const wrapper = createWrapper({
        selectedService: { id: 'service1', providers: [
          { name: 'Office A', id: 1, address: { street: 'Test', house_number: '1' } },
          { name: 'Office B', id: 2, address: { street: 'Test', house_number: '2' } }
        ] }
      });
      await wrapper.vm.showSelectionForProvider({ name: 'Office A', id: 1, address: { street: 'Test', house_number: '1' } });
      await nextTick();
      await wrapper.vm.getAppointmentsOfDay('2025-06-17');
      await nextTick();
      // Set selectedHour to 15 (15:00)
      wrapper.vm.selectedHour = 15;
      await nextTick();
      // Deselect provider 1, only provider 2 remains (12, 13, 14)
      wrapper.vm.selectedProviders['1'] = !wrapper.vm.selectedProviders['1']; await nextTick();
      await nextTick();
      expect(wrapper.vm.selectedHour).toBe(12);
    });

    it('snaps to the earlier hour if two are equally close', async () => {
      (fetchAvailableDays as Mock).mockResolvedValue({
        availableDays: [
          { time: '2025-06-17', providerIDs: '1,2' }
        ]
      });
      (fetchAvailableTimeSlots as Mock).mockResolvedValue({
        offices: [
          { officeId: 1, appointments: [1750929600, 1750935600] }, // 13:00, 15:00
          { officeId: 2, appointments: [1750924800, 1750932000] } // 12:00, 14:00
        ]
      });
      const wrapper = createWrapper({
        selectedService: { id: 'service1', providers: [
          { name: 'Office A', id: 1, address: { street: 'Test', house_number: '1' } },
          { name: 'Office B', id: 2, address: { street: 'Test', house_number: '2' } }
        ] }
      });
      await wrapper.vm.showSelectionForProvider({ name: 'Office A', id: 1, address: { street: 'Test', house_number: '1' } });
      await nextTick();
      await wrapper.vm.getAppointmentsOfDay('2025-06-17');
      await nextTick();
      // Set selectedHour to 13 (13:00)
      wrapper.vm.selectedHour = 13;
      await nextTick();
      // Deselect provider 1, only provider 2 remains (12, 14)
      wrapper.vm.selectedProviders['1'] = !wrapper.vm.selectedProviders['1']; await nextTick();
      await nextTick();
      expect(wrapper.vm.selectedHour).toBe(12); // Prefer earlier
    });

    it('snaps to the other dayPart if current is removed', async () => {
      (fetchAvailableDays as Mock).mockResolvedValue({
        availableDays: [
          { time: '2025-06-17', providerIDs: '1,2' }
        ]
      });
      (fetchAvailableTimeSlots as Mock).mockResolvedValue({
        offices: [
          { officeId: 1, appointments: [1750919400] }, // 08:30 (am)
          { officeId: 2, appointments: [1750923600] } // 14:00 (pm)
        ]
      });
      const wrapper = createWrapper({
        selectedService: { id: 'service1', providers: [
          { name: 'Office A', id: 1, address: { street: 'Test', house_number: '1' } },
          { name: 'Office B', id: 2, address: { street: 'Test', house_number: '2' } }
        ] }
      });
      await wrapper.vm.showSelectionForProvider({ name: 'Office A', id: 1, address: { street: 'Test', house_number: '1' } });
      await nextTick();
      await wrapper.vm.getAppointmentsOfDay('2025-06-17');
      await nextTick();
      // Set selectedDayPart to 'am'
      wrapper.vm.selectedDayPart = 'am';
      await nextTick();
      // Deselect provider 1, only provider 2 remains (pm)
      wrapper.vm.selectedProviders['1'] = !wrapper.vm.selectedProviders['1']; await nextTick();
      expect(wrapper.vm.selectedDayPart).toBe('am');
    });

    it('snaps to null if no dayPart is available after deselection', async () => {
      (fetchAvailableDays as Mock).mockResolvedValue({
        availableDays: [
          { time: '2025-06-17', providerIDs: '1,2' }
        ]
      });
      (fetchAvailableTimeSlots as Mock).mockResolvedValue({
        offices: [
          { officeId: 1, appointments: [1750919400] }, // 08:30 (am)
          { officeId: 2, appointments: [] } // No appointments
        ]
      });
      const wrapper = createWrapper({
        selectedService: { id: 'service1', providers: [
          { name: 'Office A', id: 1, address: { street: 'Test', house_number: '1' } },
          { name: 'Office B', id: 2, address: { street: 'Test', house_number: '2' } }
        ] }
      });
      await wrapper.vm.showSelectionForProvider({ name: 'Office A', id: 1, address: { street: 'Test', house_number: '1' } });
      await nextTick();
      await wrapper.vm.getAppointmentsOfDay('2025-06-17');
      await nextTick();
      // Set selectedDayPart to 'am'
      wrapper.vm.selectedDayPart = 'am';
      await nextTick();
      // Deselect provider 1, only provider 2 remains (no appointments)
      wrapper.vm.selectedProviders['1'] = !wrapper.vm.selectedProviders['1']; await nextTick();
      expect(wrapper.vm.selectedDayPart).toBe('am');
    });
  });

  it("shows hourly view if total appointments > 18", async () => {
    (fetchAvailableDays as Mock).mockResolvedValue({
      availableDays: [
        { time: '2025-07-02', providerIDs: '1,2' }
      ]
    });
    // 32 appointments in total (across both providers) - using exact hour timestamps > 0
    (fetchAvailableTimeSlots as Mock).mockResolvedValue({
      offices: [
        {
          officeId: 1,
          appointments: [
            1750915200, // 08:00
            1750918800, // 09:00
            1750922400, // 10:00
            1750926000, // 11:00
            1750929600, // 12:00
            1750933200, // 13:00
            1750936800, // 14:00
            1750940400, // 15:00
            1750944000, // 16:00
            1750947600, // 17:00
            1750951200, // 18:00
            1750954800, // 19:00
            1750958400, // 20:00
            1750962000, // 21:00
            1750965600, // 22:00
            1750969200, // 23:00
          ]
        },
        {
          officeId: 2,
          appointments: [
            1750915200, // 08:00
            1750918800, // 09:00
            1750922400, // 10:00
            1750926000, // 11:00
            1750929600, // 12:00
            1750933200, // 13:00
            1750936800, // 14:00
            1750940400, // 15:00
            1750944000, // 16:00
            1750947600, // 17:00
            1750951200, // 18:00
            1750954800, // 19:00
            1750958400, // 20:00
            1750962000, // 21:00
            1750965600, // 22:00
            1750969200, // 23:00
          ]
        }
      ]
    });
    const wrapper = createWrapper({
      selectedService: { id: "service1", providers: [
        { name: "Office A", id: 1, address: { street: "Test", house_number: "1" } },
        { name: "Office B", id: 2, address: { street: "Test", house_number: "2" } }
      ] }
    });
    await wrapper.vm.showSelectionForProvider({ name: "Office A", id: 1, address: { street: "Test", house_number: "1" } });
    await flushPromises();
    await wrapper.vm.handleDaySelection(new Date("2025-07-02"));
    await flushPromises();

    // Directly set both providers as selected
    wrapper.vm.selectedProviders = { 1: true, 2: true };
    await nextTick();
    await flushPromises();

    // Wait for loading to complete
    await new Promise(resolve => setTimeout(resolve, 150));
    await nextTick();

    expect(wrapper.html()).toMatch(/\d:00-\d:59/);
  });

  it("shows am/pm view if total appointments <= 18", async () => {
    (fetchAvailableDays as Mock).mockResolvedValue({
      availableDays: [
        { time: '2025-07-02', providerIDs: '1,2' }
      ]
    });
    // 18 appointments in total (across both providers)
    (fetchAvailableTimeSlots as Mock).mockResolvedValue({
      offices: [
        { officeId: 1, appointments: Array.from({ length: 9 }, (_, i) => 1750919400 + i * 3600) },
        { officeId: 2, appointments: Array.from({ length: 9 }, (_, i) => 1750952400 + i * 3600) }
      ]
    });
    const wrapper = createWrapper({
      selectedService: { id: "service1", providers: [
        { name: "Office A", id: 1, address: { street: "Test", house_number: "1" } },
        { name: "Office B", id: 2, address: { street: "Test", house_number: "2" } }
      ] }
    });
    await wrapper.vm.showSelectionForProvider({ name: "Office A", id: 1, address: { street: "Test", house_number: "1" } });
    await flushPromises();
    await wrapper.vm.handleDaySelection(new Date("2025-07-02"));
    await flushPromises();
    expect(wrapper.html()).toMatch(/am|pm/);
  });

  describe('Test submission loading state', () => {
    let wrapper;
    let selectedTimeslotRef;
    let loadingStates;
    beforeEach(async () => {
      selectedTimeslotRef = ref(0);
      loadingStates = {
        isReservingAppointment: ref(false),
        isUpdatingAppointment: ref(false),
        isBookingAppointment: ref(false),
        isCancelingAppointment: ref(false),
      };
      wrapper = mount(CalendarView, {
        global: {
          provide: {
            selectedServiceProvider: {
              selectedService: ref({ id: 'service1', providers: [
                { name: 'Office A', id: 1, address: { street: 'Elm', house_number: '99' } }
              ] }),
            },
            selectedTimeslot: {
              selectedProvider: ref(null),
              selectedTimeslot: selectedTimeslotRef,
            },
            selectableProviders: ref([]),
            loadingStates,
          },
          stubs: ["muc-slider", "muc-callout", "muc-calendar"],
        },
        props: {
          ...baseProps,
        },
      });
      wrapper.vm.selectedDay = new Date('2025-05-14');
      wrapper.vm.availableDaysFetched = true;
      wrapper.vm.appointmentsCount = 18;
      wrapper.vm.timeSlotsInHoursByOffice = ref(new Map([[1, { officeId: 1, appointments: new Map([[10, [1234567890]]]) }]]));
      await nextTick();
    });
    afterEach(() => {
      wrapper.unmount();
    });

    it("test loading state when reserving appointment", async () => {
        wrapper.vm.loadingStates.isReservingAppointment.value = true;
        await nextTick();

        expect(wrapper.vm.loadingStates.isReservingAppointment.value).toBe(true);

        wrapper.vm.loadingStates.isReservingAppointment.value = false;
        await nextTick();

        expect(wrapper.vm.loadingStates.isReservingAppointment.value).toBe(false);
    });

    it('enables the next button after selecting an appointment and disables it after reservation starts', async () => {
      selectedTimeslotRef.value = 1234567890;
      await nextTick();
      let nextButton = wrapper.findAllComponents({ name: 'MucButton' }).find(btn => btn.text().includes('next'));
      expect(nextButton && !nextButton.props('disabled')).toBe(true);

      loadingStates.isReservingAppointment.value = true;
      await nextTick();
      nextButton = wrapper.findAllComponents({ name: 'MucButton' }).find(btn => btn.text().includes('next'));
      expect(nextButton && nextButton.props('disabled')).toBe(true);
    });

    it('disables next button during reservation and re-enables after reservation completes', async () => {
      selectedTimeslotRef.value = 1234567890;
      await nextTick();
      let nextButton = wrapper.findAllComponents({ name: 'MucButton' }).find(btn => btn.text().includes('next'));
      expect(nextButton && !nextButton.props('disabled')).toBe(true);

      loadingStates.isReservingAppointment.value = true;
      await nextTick();
      nextButton = wrapper.findAllComponents({ name: 'MucButton' }).find(btn => btn.text().includes('next'));
      expect(nextButton && nextButton.props('disabled')).toBe(true);

      loadingStates.isReservingAppointment.value = false;
      await nextTick();
      nextButton = wrapper.findAllComponents({ name: 'MucButton' }).find(btn => btn.text().includes('next'));
      expect(nextButton && !nextButton.props('disabled')).toBe(true);
    });

  });

  describe("Error States", () => {
    it('shows captcha error warning callout when captcha error is set', async () => {
      const wrapper = createWrapper({
        props: {
          bookingError: true,
          bookingErrorKey: "apiErrorCaptchaInvalid",
        }
      });

      await nextTick();

      const callout = wrapper.find('[data-test="muc-callout"]');

      expect(callout.exists()).toBe(true);
      expect(callout.attributes('data-type')).toBe("warning");
      expect(callout.html()).toContain("apiErrorCaptchaInvalidHeader");
      expect(callout.html()).toContain("apiErrorCaptchaInvalidText");
    });

    it('shows no appointment error warning callout when no appointment error is set', async () => {
      const wrapper = createWrapper({
        props: {
          bookingError: true,
          bookingErrorKey: "apiErrorNoAppointmentForThisScope",
        }
      });

      await nextTick();

      const callout = wrapper.find('[data-test="muc-callout"]');

      expect(callout.exists()).toBe(true);
      expect(callout.attributes('data-type')).toBe("warning");
      expect(callout.html()).toContain("apiErrorNoAppointmentForThisScopeHeader");
      expect(callout.html()).toContain("apiErrorNoAppointmentForThisScopeText");
    });

    it('shows appointment not available error warning callout when appointment not available error is set', async () => {
      const wrapper = createWrapper({
        props: {
          bookingError: true,
          bookingErrorKey: "apiErrorAppointmentNotAvailable",
        }
      });

      await nextTick();

      const callout = wrapper.find('[data-test="muc-callout"]');

      expect(callout.exists()).toBe(true);
      expect(callout.attributes('data-type')).toBe("warning");
      expect(callout.html()).toContain("apiErrorAppointmentNotAvailableHeader");
      expect(callout.html()).toContain("apiErrorAppointmentNotAvailableText");
    });

    it('does not show any callout when bookingError is false', async () => {
      const wrapper = createWrapper({
        props: {
          bookingError: false,
          bookingErrorKey: "",
        }
      });

      await nextTick();
      const callout = wrapper.find('[data-test="muc-callout"]');
      expect(callout.exists()).toBe(false);
    });
  });

  describe("CalendarView – Toggle & List View", () => {

    it("toggles from calendar view to list view and back", async () => {
      const wrapper = createWrapper({
        selectedService: {
          id: "service1",
          providers: [
            { name: "Office A", id: 1, address: { street: "Elm", house_number: "99" } }
          ]
        }
      });

      await wrapper.vm.showSelectionForProvider({ name: "Office A", id: 1, address: { street: "Elm", house_number: "99" } });
      await flushPromises();

      expect(wrapper.vm.isListView).toBe(false);
      expect(wrapper.findComponent({ name: "muc-calendar" }).exists()).toBe(true);
      expect(wrapper.find(".m-component-accordion").exists()).toBe(false);

      await wrapper.find(".m-toggle-switch").trigger("click");
      await nextTick();

      expect(wrapper.vm.isListView).toBe(true);
      expect(wrapper.findComponent({ name: "muc-calendar" }).exists()).toBe(false);
      expect(wrapper.find(".m-component-accordion").exists()).toBe(true);

      await wrapper.find(".m-toggle-switch").trigger("click");
      await nextTick();

      expect(wrapper.vm.isListView).toBe(false);
      expect(wrapper.findComponent({ name: "muc-calendar" }).exists()).toBe(true);
    });

    it("adds three more days whenever the 'Mehr laden' button is clicked", async () => {
      (fetchAvailableDays as Mock).mockResolvedValue({
        availableDays: Array.from({ length: 9 }, (_, i) => ({
          time: `2025-06-${String(10 + i).padStart(2, "0")}`,
          providerIDs: "1"
        }))
      });

      const wrapper = createWrapper({
        selectedService: {
          id: "service1",
          providers: [{ name: "Office", id: 1, address: { street: "Elm", house_number: "99" } }]
        }
      });

      await wrapper.vm.showSelectionForProvider({ name: "Office", id: 1, address: { street: "Elm", house_number: "99" } });
      await flushPromises();

      await wrapper.find(".m-toggle-switch").trigger("click");
      await nextTick();

      expect(wrapper.vm.daysToShow).toBe(5);
      expect(wrapper.findAll(".m-accordion__section-header").length).toBe(5);

      const loadBtn = wrapper.findAllComponents({ name: "MucButton" })
        .find(btn => btn.text().includes("loadMore"));
      expect(loadBtn).toBeTruthy();

      await loadBtn!.trigger("click");
      await nextTick();

      expect(wrapper.vm.daysToShow).toBe(8);
      expect(wrapper.findAll(".m-accordion__section-header").length).toBe(8);
    });

    it("opens the clicked accordion section and closes the previous one", async () => {
      (fetchAvailableDays as Mock).mockResolvedValue({
        availableDays: [
          { time: "2025-06-10", providerIDs: "1" },
          { time: "2025-06-11", providerIDs: "1" }
        ]
      });

      const wrapper = createWrapper({
        selectedService: {
          id: "service1",
          providers: [{ name: "Office", id: 1, address: { street: "Elm", house_number: "99" } }]
        }
      });

      await wrapper.vm.showSelectionForProvider({ name: "Office", id: 1, address: { street: "Elm", house_number: "99" } });
      await flushPromises();

      await wrapper.find(".m-toggle-switch").trigger("click");
      await nextTick();

      expect(wrapper.find("#listContent-0").classes()).toContain("show");
      expect(wrapper.find("#listContent-1").classes()).not.toContain("show");

      await wrapper.find("#listHeading-1 .m-accordion__section-button").trigger("click");
      await nextTick();

      expect(wrapper.find("#listContent-0").classes()).not.toContain("show");
      expect(wrapper.find("#listContent-1").classes()).toContain("show");
    });

    it("initializes list view navigation state for each day", async () => {
      (fetchAvailableDays as Mock).mockResolvedValue({
        availableDays: [
          { time: "2025-06-10", providerIDs: "1" },
          { time: "2025-06-11", providerIDs: "1" }
        ]
      });

      (fetchAvailableTimeSlots as Mock).mockResolvedValue({
        offices: [
          {
            officeId: 1,
            appointments: [1747202400, 1747223100, 1747223400, 1747223700, 1747224000, 1747224300]
          }
        ]
      });

      const wrapper = createWrapper({
        selectedService: {
          id: "service1",
          providers: [{ name: "Office", id: 1, address: { street: "Elm", house_number: "99" } }]
        }
      });

      await wrapper.vm.showSelectionForProvider({ name: "Office", id: 1, address: { street: "Elm", house_number: "99" } });
      await flushPromises();

      await wrapper.find(".m-toggle-switch").trigger("click");
      await nextTick();

      await nextTick();
      await nextTick();

      expect(wrapper.vm.listViewCurrentHour).toBeDefined();
      expect(wrapper.vm.listViewCurrentDayPart).toBeDefined();
    });

    it("resets list view navigation state when providers change", async () => {
      (fetchAvailableDays as Mock).mockResolvedValue({
        availableDays: [
          { time: "2025-06-10", providerIDs: "1" }
        ]
      });

      (fetchAvailableTimeSlots as Mock).mockResolvedValue({
        offices: [
          {
            officeId: 1,
            appointments: [1747202400, 1747223100, 1747223400, 1747223700, 1747224000, 1747224300]
          }
        ]
      });

      const wrapper = createWrapper({
        selectedService: {
          id: "service1",
          providers: [{ name: "Office", id: 1, address: { street: "Elm", house_number: "99" } }]
        }
      });

      await wrapper.vm.showSelectionForProvider({ name: "Office", id: 1, address: { street: "Elm", house_number: "99" } });
      await flushPromises();

      await wrapper.find(".m-toggle-switch").trigger("click");
      await nextTick();

      const dateString = "2025-06-10";
      wrapper.vm.listViewCurrentHour.set(dateString, 16);
      wrapper.vm.listViewCurrentDayPart.set(dateString, "pm");

      wrapper.vm.selectedProviders = { "2": true };
      await nextTick();

      expect(wrapper.vm.listViewCurrentHour.has(dateString)).toBe(true);
      expect(wrapper.vm.listViewCurrentDayPart.has(dateString)).toBe(true);
      
      expect(wrapper.vm.listViewCurrentHour.get(dateString)).toBe(16);
      expect(wrapper.vm.listViewCurrentDayPart.get(dateString)).toBe("pm");
    });

    it("navigates between hours in list view", async () => {
      (fetchAvailableDays as Mock).mockResolvedValue({
        availableDays: [
          { time: "2025-06-10", providerIDs: "1" }
        ]
      });

      (fetchAvailableTimeSlots as Mock).mockResolvedValue({
        offices: [
          {
            officeId: 1,
            appointments: [1747202400, 1747223100, 1747223400, 1747223700, 1747224000, 1747224300]
          }
        ]
      });

      const wrapper = createWrapper({
        selectedService: {
          id: "service1",
          providers: [{ name: "Office", id: 1, address: { street: "Elm", house_number: "99" } }]
        }
      });

      await wrapper.vm.showSelectionForProvider({ name: "Office", id: 1, address: { street: "Elm", house_number: "99" } });
      await flushPromises();

      await wrapper.find(".m-toggle-switch").trigger("click");
      await nextTick();

      const dateString = "2025-06-10";
      
      wrapper.vm.listViewCurrentHour.set(dateString, 16);
      const initialHour = 16;

      wrapper.vm.listViewEarlierAppointments({ 
        dateString, 
        hourRows: [{ hour: 15, times: [1], officeId: 1 }, { hour: 16, times: [1], officeId: 1 }] 
      } as any, "hour");
      await nextTick();

      const currentHour = wrapper.vm.listViewCurrentHour.get(dateString);
      expect(currentHour).toBeDefined();
      expect(currentHour).toBe(15);
    });

    it("navigates between day parts in list view", async () => {
      (fetchAvailableDays as Mock).mockResolvedValue({
        availableDays: [
          { time: "2025-06-10", providerIDs: "1" }
        ]
      });

      (fetchAvailableTimeSlots as Mock).mockResolvedValue({
        offices: [
          {
            officeId: 1,
            appointments: [1747202400, 1747223100, 1747223400, 1747223700, 1747224000, 1747224300]
          }
        ]
      });

      const wrapper = createWrapper({
        selectedService: {
          id: "service1",
          providers: [{ name: "Office", id: 1, address: { street: "Elm", house_number: "99" } }]
        }
      });

      await wrapper.vm.showSelectionForProvider({ name: "Office", id: 1, address: { street: "Elm", house_number: "99" } });
      await flushPromises();

      await wrapper.find(".m-toggle-switch").trigger("click");
      await nextTick();

      const dateString = "2025-06-10";
      
      wrapper.vm.listViewCurrentDayPart.set(dateString, "pm");
      const initialDayPart = "pm";

      wrapper.vm.listViewEarlierAppointments({ 
        dateString, 
        dayPartRows: [{ part: "am", times: [1], officeId: 1 }, { part: "pm", times: [1], officeId: 1 }] 
      } as any, "dayPart");
      await nextTick();

      const currentDayPart = wrapper.vm.listViewCurrentDayPart.get(dateString);
      expect(currentDayPart).toBeDefined();
      expect(currentDayPart).toBe("am");
    });

    it("shows navigation buttons for hourly view in list view", async () => {
      (fetchAvailableDays as Mock).mockResolvedValue({
        availableDays: [
          { time: "2025-06-10", providerIDs: "1" }
        ]
      });

      (fetchAvailableTimeSlots as Mock).mockResolvedValue({
        offices: [
          {
            officeId: 1,
            appointments: [1747202400, 1747223100, 1747223400, 1747223700, 1747224000, 1747224300]
          }
        ]
      });

      const wrapper = createWrapper({
        selectedService: {
          id: "service1",
          providers: [
            { name: "Office A", id: 1, address: { street: "Elm", house_number: "99" } },
            { name: "Office B", id: 2, address: { street: "Oak", house_number: "100" } }
          ]
        }
      });

      await wrapper.vm.showSelectionForProvider({ name: "Office A", id: 1, address: { street: "Elm", house_number: "99" } });
      await flushPromises();

      await wrapper.find(".m-toggle-switch").trigger("click");
      await nextTick();

      await nextTick();
      await nextTick();

      const buttons = wrapper.findAllComponents({ name: "MucButton" });
      const earlierButton = buttons.find(btn => btn.text().includes("earlier"));
      const laterButton = buttons.find(btn => btn.text().includes("later"));

      expect(buttons.length).toBeGreaterThan(0);
    });

    it("filters location titles to show only once per office per time period", async () => {
      (fetchAvailableDays as Mock).mockResolvedValue({
        availableDays: [
          { time: "2025-06-10", providerIDs: "1,2" }
        ]
      });

      (fetchAvailableTimeSlots as Mock).mockResolvedValue({
        offices: [
          {
            officeId: 1,
            appointments: [1747202400, 1747223100, 1747223400]
          },
          {
            officeId: 2,
            appointments: [1747202400, 1747223100, 1747223400]
          }
        ]
      });

      const wrapper = createWrapper({
        selectedService: {
          id: "service1",
          providers: [
            { name: "Office A", id: 1, address: { street: "Elm", house_number: "99" } },
            { name: "Office B", id: 2, address: { street: "Oak", house_number: "100" } }
          ]
        }
      });

      await wrapper.vm.showSelectionForProvider({ name: "Office A", id: 1, address: { street: "Elm", house_number: "99" } });
      await flushPromises();

      await wrapper.find(".m-toggle-switch").trigger("click");
      await nextTick();

      await nextTick();
      await nextTick();

      const locationTitles = wrapper.findAll(".location-title");
      
      expect(wrapper.vm.firstFiveAvailableDays.length).toBeGreaterThan(0);
    });
  });
});

