import { mount, flushPromises } from "@vue/test-utils";
import { describe, it, expect, vi, beforeEach } from "vitest";
import { ref, nextTick } from "vue";
// Mount parent to exercise logic and assert ProviderSelection UI
// @ts-expect-error: Vue SFC import for test
import AppointmentSelection from "@/components/Appointment/AppointmentSelection.vue";

// Mock API to avoid real network calls
vi.mock("@/api/ZMSAppointmentAPI", () => ({
  fetchAvailableDays: vi.fn().mockResolvedValue({ 
    availableDays: [
      { time: '2025-06-17', providerIDs: '1,2,3,4,102522,102523,102524,102526,10489,10502,54261' }
    ] 
  }),
  fetchAvailableTimeSlots: vi.fn().mockResolvedValue({ offices: [] }),
}));

const t = vi.fn((key: string) => key);

const baseProps = {
  globalState: {
    baseUrl: "http://test.url",
  },
  isRebooking: false,
  exclusiveLocation: undefined,
  preselectedOfficeId: undefined,
  selectedServiceMap: new Map([["service1", 1]]),
  captchaToken: "test-token",
  bookingError: false,
  bookingErrorKey: "apiErrorNoAppointmentForThisScope",
  t,
};

const MucCheckboxStub = {
  name: "MucCheckbox",
  props: ["id", "label", "hint", "modelValue"],
  emits: ["update:model-value"],
  template:
    '<label><input type="checkbox" :id="id" :checked="!!modelValue" @change="$emit(\'update:model-value\', !$props.modelValue)"/>{{ label }}</label>',
};

const createWrapper = (overrides: any = {}) => {
  return mount(AppointmentSelection, {
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
        MucCheckbox: MucCheckboxStub,
        MucCheckboxGroup: { template: '<div class="m-checkbox-group"><slot name="checkboxes"/></div>' },
        "muc-slider": true,
        "muc-callout": true,
        "muc-calendar": true,
      },
    },
    props: {
      ...baseProps,
      ...overrides.props,
    },
  });
};

describe("ProviderSelection (UI via AppointmentSelection)", () => {
  beforeEach(() => {
    vi.clearAllMocks();
  });

  it("renders multiple providers with checkboxes", async () => {
    const wrapper = createWrapper({
      selectedService: {
        id: "service1",
        providers: [
          { name: "Office A", id: 1, address: { street: "Elm", house_number: "99" } },
          { name: "Office B", id: 2, address: { street: "Elm", house_number: "99" } },
        ],
      },
    });
    await flushPromises(); // Wait for API call to complete
    await nextTick();
    const checkboxes = wrapper.findAll('input[type="checkbox"]');
    expect(checkboxes.length).toBe(2);
    expect(wrapper.text()).toContain("Office A");
    expect(wrapper.text()).toContain("Office B");
  });

  it("filters providers correctly based on disabledByServices", async () => {
    const testProviders = [
      { id: 102522, name: 'Bürgerbüro Orleansplatz', disabledByServices: [], address: { street: 'Test', house_number: '1' } },
      { id: 102523, name: 'Bürgerbüro Leonrodstraße', disabledByServices: [], address: { street: 'Test', house_number: '2' } },
      { id: 102524, name: 'Bürgerbüro Riesenfeldstraße', disabledByServices: [], address: { street: 'Test', house_number: '3' } },
      { id: 102526, name: 'Bürgerbüro Forstenrieder Allee', disabledByServices: [], address: { street: 'Test', house_number: '4' } },
      { id: 10489, name: 'Bürgerbüro Ruppertstraße', disabledByServices: ['1063453', '1063441', '1080582'], address: { street: 'Test', house_number: '5' } },
      { id: 10502, name: 'Bürgerbüro Ruppertstraße', disabledByServices: [], address: { street: 'Test', house_number: '6' } },
      { id: 54261, name: 'Bürgerbüro Pasing', disabledByServices: [], address: { street: 'Test', house_number: '7' } },
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

    await flushPromises(); // Wait for API call to complete
    await nextTick();

    expect(wrapper.text()).toContain("Office ABC");
    expect(wrapper.text()).toContain("Elm 99");
  });

  it("handles provider selection with checkboxes", async () => {

    const wrapper = createWrapper({
      selectedService: { id: "service1", providers: [
        { name: "Office A", id: 1, address: { street: "Elm", house_number: "99" } },
        { name: "Office B", id: 2, address: { street: "Elm", house_number: "99" } }
      ] }
    });

    // Trigger a provider selection which will call fetchAvailableDays
    await wrapper.vm.showSelectionForProvider({ name: "Office A", id: 1, address: { street: "Elm", house_number: "99" } });
    await nextTick();

    // Test initial state
    expect(wrapper.vm.selectedProviders[1]).toBe(true);
    expect(wrapper.vm.selectedProviders[2]).toBe(true);

    // Test toggling selection
    wrapper.vm.selectedProviders[1] = !wrapper.vm.selectedProviders[1];
    await nextTick();
    expect(wrapper.vm.selectedProviders[1]).toBe(false);
    expect(wrapper.vm.selectedProviders[2]).toBe(true);

  });

  it('checks only the preselected office when preselectedOfficeId is provided', async () => {

    const wrapper = createWrapper({
      selectedService: {
        id: 'service1',
        providers: [
          { name: 'Office A', id: '1', showAlternativeLocations: true, address: { street: 'Test', house_number: '1' } },
          { name: 'Office B', id: '2', showAlternativeLocations: true, address: { street: 'Test', house_number: '2' } },
          { name: 'Office C', id: '3', showAlternativeLocations: true, address: { street: 'Test', house_number: '3' } }
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

  it('checks only the preselected office when it has showAlternativeLocations false', async () => {

    const wrapper = createWrapper({
      selectedService: {
        id: 'service1',
        providers: [
          { name: 'Office A', id: '1', showAlternativeLocations: true, address: { street: 'Test', house_number: '1' } },
          { name: 'Office B', id: '2', showAlternativeLocations: false, address: { street: 'Test', house_number: '2' } },
          { name: 'Office C', id: '3', showAlternativeLocations: true, address: { street: 'Test', house_number: '3' } }
        ]
      },
      props: {
        preselectedOfficeId: '2'
      }
    });

    await wrapper.vm.showSelectionForProvider({ name: 'Office B', id: '2', address: { street: 'Test', house_number: '2' } });
    await nextTick();

    expect(wrapper.vm.selectedProviders).toEqual({
      '2': true
    });
  });

  it('shows all locations in the checkbox list regardless of appointments', async () => {

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

    // With new behavior, all selectable providers render regardless of appointments
    const checkboxes = wrapper.findAll('input[type="checkbox"]');
    expect(checkboxes.length).toBe(4);
    expect(wrapper.text()).toContain('Office A');
    expect(wrapper.text()).toContain('Office B');
    expect(wrapper.text()).toContain('Office C');
    expect(wrapper.text()).toContain('Office D');
  });

  it("does not show single provider when no appointments are available (no checkboxes)", async () => {
    const wrapper = createWrapper({
      selectedService: {
        id: "service1",
        providers: [
          { name: "Only Office", id: "1", address: { street: "Test", house_number: "1" } },
        ],
      },
    });
    await nextTick();
    // With single provider, checkboxes section is not rendered
    const checkboxes = wrapper.findAll('input[type="checkbox"]');
    expect(checkboxes.length).toBe(0);
  });
});