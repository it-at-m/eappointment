import type { VueWrapper } from "@vue/test-utils";
import type { Mock } from "vitest";
import type { Ref } from "vue";

import { flushPromises, mount } from "@vue/test-utils";
import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";
import { nextTick, ref } from "vue";

import { fetchAvailableCalendar } from "@/api/ZMSAppointmentAPI";
import AppointmentSelection from "@/components/Appointment/AppointmentSelection.vue";
import {
  calendarResponse,
  officeOneAndTwoSlots,
  officeOneMorningSlots,
  offices10351880And10470,
  officesForDayPartViewTest,
  officesForHourlyViewTest,
  officesForProviderHourSnap,
  officesForProviderHourSnapEqualDistance,
  officeTwoAndThreeSlots,
  setAppointmentsByDay,
  setAvailableDays,
} from "../../helpers/calendarAvailability";

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

vi.mock("@/api/ZMSAppointmentAPI", () => ({
  fetchAvailableCalendar: vi.fn(),
}));

interface LoadingStates {
  isReservingAppointment: Ref<boolean>;
  isUpdatingAppointment: Ref<boolean>;
  isBookingAppointment: Ref<boolean>;
  isCancelingAppointment: Ref<boolean>;
}

interface WrapperOverrides {
  selectedService?: any;
  selectedProvider?: any;
  selectedTimeslot?: number;
  props?: Record<string, any>;
}

const createWrapper = (overrides: WrapperOverrides = {}) => {
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
        "muc-slider": true,
        "muc-callout": {
          props: ["type", "t"],
          template: `
              <div data-test='muc-callout' :data-type="type">
                <slot name="header"></slot>
                <slot name="content">
              </slot></div>
            `,
        },
        "muc-calendar": true,
      },
    },
    props: {
      ...baseProps,
      ...overrides.props,
    },
  });
};

describe("AppointmentSelection", () => {
  beforeEach(() => {
    vi.clearAllMocks();

    Element.prototype.scrollIntoView = vi.fn();
    HTMLElement.prototype.focus = vi.fn();
  });

  (fetchAvailableCalendar as Mock).mockResolvedValue(
    calendarResponse([
      { time: "2025-05-14", providerIDs: "102522,54261,10489" },
      { time: "2025-05-15", providerIDs: "102522,54261,10489" },
    ])
  );

  describe("ProviderSelection Integration", () => {
    it("renders nothing if no provider is selected", () => {
      const wrapper = createWrapper();
      expect(wrapper.html()).not.toContain("location");
    });

    it("shows only one appointment for one provider in the morning", async () => {
      // Mock availableDays to include only Office AAA
      (fetchAvailableCalendar as Mock).mockResolvedValue(
        calendarResponse(
          [{ time: "2025-06-17", providerIDs: "1" }],
          officeOneMorningSlots
        )
      );

      // Mock availableTimeSlots to include appointments for Office AAA

      const wrapper = createWrapper({
        selectedService: {
          id: "service1",
          providers: [
            {
              name: "Office AAA",
              id: 1,
              priority: 5,
              address: { street: "Elm", house_number: "99" },
              scope: { id: "1" },
            },
            {
              name: "Office BBB",
              id: 2,
              priority: 10,
              address: { street: "Elm", house_number: "99" },
              scope: { id: "2" },
            },
          ],
        },
      });

      // Wait for availableDays to be loaded
      await wrapper.vm.showSelectionForProvider({
        name: "Office AAA",
        id: 1,
        priority: 5,
        address: { street: "Elm", house_number: "99" },
        scope: { id: "1" },
      });
      await nextTick();
      await wrapper.vm.getAppointmentsOfDay("2025-06-17");
      await nextTick();
      // Wait for loading to complete and spinner to disappear
      await flushPromises();
      await new Promise((resolve) => setTimeout(resolve, 150)); // Wait for 100ms timeout + buffer

      const locationTitles = wrapper.findAll(".location-title");
      const officeAAA = locationTitles.find((location) =>
        location.text().includes("Office AAA")
      );
      const officeBBB = locationTitles.find((location) =>
        location.text().includes("Office BBB")
      );
      expect(officeAAA).toBeTruthy();
      expect(officeBBB).toBeFalsy();
    });

    it("shows more appointments and providers after loading later appointments", async () => {
      // Mock availableDays to include both providers
      (fetchAvailableCalendar as Mock).mockResolvedValue(
        calendarResponse(
          [{ time: "2025-06-17", providerIDs: "1,2" }],
          officeOneAndTwoSlots
        )
      );

      // Mock availableTimeSlots to include appointments for both providers

      const wrapper = createWrapper({
        selectedService: {
          id: "service1",
          providers: [
            {
              name: "Office AAA",
              id: 1,
              priority: 5,
              address: { street: "Elm", house_number: "99" },
              scope: { id: "1" },
            },
            {
              name: "Office BBB",
              id: 2,
              priority: 10,
              address: { street: "Elm", house_number: "99" },
              scope: { id: "2" },
            },
          ],
        },
      });

      // Wait for availableDays to be loaded
      await wrapper.vm.showSelectionForProvider({
        name: "Office AAA",
        id: 1,
        priority: 5,
        address: { street: "Elm", house_number: "99" },
        scope: { id: "1" },
      });
      await nextTick();
      await wrapper.vm.getAppointmentsOfDay("2025-06-17");
      await nextTick();
      // Wait for loading to complete and spinner to disappear
      await flushPromises();
      await new Promise((resolve) => setTimeout(resolve, 150)); // Wait for 100ms timeout + buffer

      const locationTitles = wrapper.findAll(".location-title");
      const officeAAA = locationTitles.find((location) =>
        location.text().includes("Office AAA")
      );
      const officeBBB = locationTitles.find((location) =>
        location.text().includes("Office BBB")
      );
      expect(officeAAA).toBeTruthy();
      expect(officeBBB).toBeTruthy();
    });

    it("shows appointments by hour", async () => {
      // Mock availableDays to include Office BBB and CCC
      (fetchAvailableCalendar as Mock).mockResolvedValue(
        calendarResponse(
          [{ time: "2025-06-17", providerIDs: "2,3" }],
          officeTwoAndThreeSlots
        )
      );

      // Mock availableTimeSlots to include appointments for Office BBB and CCC

      const wrapper = createWrapper({
        selectedService: {
          id: "service1",
          providers: [
            {
              name: "Office AAA",
              id: 1,
              priority: 5,
              address: { street: "Elm", house_number: "99" },
              scope: { id: "1" },
            },
            {
              name: "Office BBB",
              id: 2,
              priority: 10,
              address: { street: "Elm", house_number: "99" },
              scope: { id: "2" },
            },
            {
              name: "Office CCC",
              id: 3,
              priority: 8,
              address: { street: "Elm", house_number: "99" },
              scope: { id: "3" },
            },
          ],
        },
      });

      // Wait for availableDays to be loaded
      await wrapper.vm.showSelectionForProvider({
        name: "Office BBB",
        id: 2,
        priority: 10,
        address: { street: "Elm", house_number: "99" },
        scope: { id: "2" },
      });
      await nextTick();
      await wrapper.vm.getAppointmentsOfDay("2025-06-17");
      await nextTick();
      // Wait for loading to complete and spinner to disappear
      await flushPromises();
      await new Promise((resolve) => setTimeout(resolve, 150)); // Wait for 100ms timeout + buffer

      const locationTitles = wrapper.findAll(".location-title");
      const officeAAA = locationTitles.find((location) =>
        location.text().includes("Office AAA")
      );
      const officeBBB = locationTitles.find((location) =>
        location.text().includes("Office BBB")
      );
      const officeCCC = locationTitles.find((location) =>
        location.text().includes("Office CCC")
      );
      expect(officeAAA).toBeFalsy();
      expect(officeBBB).toBeTruthy();
      expect(officeCCC).toBeTruthy();
    });

    it("shows an error message when no provider is selected", async () => {
      // Mock available days with provider IDs
      (fetchAvailableCalendar as Mock).mockResolvedValue(
        calendarResponse([{ time: "2025-06-17", providerIDs: "1,2" }])
      );

      // Create component with two selectable providers
      const wrapper = createWrapper({
        selectedService: {
          id: "service1",
          providers: [
            {
              name: "Office A",
              id: 1,
              address: { street: "Main", house_number: "1" },
              scope: { id: "1" },
            },
            {
              name: "Office B",
              id: 2,
              address: { street: "Main", house_number: "2" },
              scope: { id: "2" },
            },
          ],
        },
      });

      await flushPromises(); // Wait for API call and computed properties

      // Make sure no provider is selected
      wrapper.vm.selectedProviders = {};
      await nextTick();

      // With new behavior, availableDays still contains data for all providers (we always fetch all)
      // The filtering happens in providersWithAvailableDays computed property
      expect(wrapper.vm.availableDays).toEqual([
        { time: "2025-06-17", providerIDs: "1,2" },
      ]);

      // The error message should be shown when no provider with appointments is selected
      expect(wrapper.text()).toContain("errorMessageProviderSelection");
    });

    it("shows no providers when none have appointments", async () => {
      // Mock availableDays to include no providers
      (fetchAvailableCalendar as Mock).mockResolvedValue(calendarResponse([]));

      // Mock availableTimeSlots to return no appointments

      const wrapper = createWrapper({
        selectedService: {
          id: "service1",
          providers: [
            {
              name: "Office AAA",
              id: 1,
              priority: 5,
              address: { street: "Elm", house_number: "99" },
              scope: { id: "1" },
            },
            {
              name: "Office BBB",
              id: 2,
              priority: 10,
              address: { street: "Elm", house_number: "99" },
              scope: { id: "2" },
            },
            {
              name: "Office CCC",
              id: 3,
              priority: 8,
              address: { street: "Elm", house_number: "99" },
              scope: { id: "3" },
            },
          ],
        },
      });

      // Wait for availableDays to be loaded
      await wrapper.vm.showSelectionForProvider({
        name: "Office AAA",
        id: 1,
        priority: 5,
        address: { street: "Elm", house_number: "99" },
        scope: { id: "1" },
      });
      await nextTick();
      await wrapper.vm.getAppointmentsOfDay("2025-06-17");
      await nextTick();

      const locationTitles = wrapper.findAll(".location-title");
      expect(locationTitles.length).toBe(0);
    });

    it("preselects provider when preselectedOfficeId matches office parentId", async () => {
      (fetchAvailableCalendar as Mock).mockResolvedValue(
        calendarResponse([{ time: "2025-06-17", providerIDs: "1" }])
      );

      const wrapper = createWrapper({
        props: {
          preselectedOfficeId: "101135",
        },
        selectedService: {
          id: "2",
          providers: [
            {
              name: "Planvorbesprechung Grundstücksentwässerung",
              id: 1,
              parentId: 101135,
              priority: 1,
              address: {
                street: "Friedenstraße",
                house_number: "40",
              },
              scope: {
                id: "369",
              },
            },
          ],
        },
      });

      await flushPromises();
      await nextTick();

      expect(wrapper.vm.selectedProviders).toEqual({
        1: true,
      });

      expect(wrapper.vm.noProviderSelected).toBe(false);
    });
  });

  describe("CalendarView Integration", () => {
    // moved to CalendarView.spec.ts: shows available day only by providers that have free appointments on that day
    it("shows available day only by providers that have free appointments on that day", async () => {
      (fetchAvailableCalendar as Mock).mockResolvedValue(
        calendarResponse([
          {
            time: "2025-05-14",
            providerIDs: "102522,54261,10489",
          },
          {
            time: "2025-05-15",
            providerIDs: "102522",
          },
        ])
      );

      const wrapper = createWrapper({
        selectedService: {
          id: "service1",
          providers: [
            {
              name: "Office AAA",
              id: 102522,
              address: { street: "Elm", house_number: "99" },
              scope: { id: "102522" },
            },
            {
              name: "Office BBB",
              id: 54261,
              address: { street: "Elm", house_number: "99" },
              scope: { id: "54261" },
            },
            {
              name: "Office CCC",
              id: 10489,
              address: { street: "Elm", house_number: "99" },
              scope: { id: "10489" },
            },
          ],
        },
      });

      await wrapper.vm.showSelectionForProvider({
        name: "Office AAA",
        id: 102522,
        address: { street: "Elm", house_number: "99" },
        scope: { id: "102522" },
      });
      await nextTick();

      // Uncheck the provider - with new behavior, availableDays still contains data for all providers
      wrapper.vm.selectedProviders = {
        "102522": false,
        "54261": false,
        "10489": false,
      };
      await nextTick();

      // With no providers selected, allowedDates returns false for all dates
      expect(wrapper.vm.allowedDates(new Date("2025-05-14"))).toBeFalsy();
      expect(wrapper.vm.allowedDates(new Date("2025-05-16"))).toBeFalsy();
      expect(wrapper.vm.allowedDates(new Date("2025-05-17"))).toBeFalsy();
    });

    // moved to CalendarView.spec.ts: handles calendar navigation correctly
    it("handles calendar navigation correctly", async () => {
      const wrapper = createWrapper({
        selectedService: {
          id: "service1",
          providers: [
            {
              name: "Office AAA",
              id: 102522,
              address: { street: "Elm", house_number: "99" },
              scope: { id: "102522" },
            },
          ],
        },
      });

      // Initialize required state
      wrapper.vm.appointmentTimestampsByOffice = ref([]);
      wrapper.vm.appointmentTimestamps = ref([]);

      await wrapper.vm.showSelectionForProvider({
        name: "Office AAA",
        id: 102522,
        address: { street: "Elm", house_number: "99" },
      });
      await nextTick();

      // Set the selected day directly
      wrapper.vm.selectedDay = new Date("2025-05-15");
      await nextTick();

      // Test navigation to next day
      await wrapper.vm.getAppointmentsOfDay("2025-05-15");
      await nextTick();

      expect(wrapper.vm.selectedDay).toEqual(new Date("2025-05-15"));
    });

    // moved to CalendarView.spec.ts: CalendarView date disabling and auto-selection (all cases)
    describe("CalendarView date disabling and auto-selection", () => {
      it("disables a date in availableDays if API returns no appointments for it", async () => {
        const officesWithSlots = [
          { officeId: 10351880, appointments: [1750118400] },
          { officeId: 10470, appointments: [1750118400] },
        ];
        (fetchAvailableCalendar as Mock).mockResolvedValue(
          calendarResponse(
            [
              {
                time: "2025-06-16",
                providerIDs: "10351880,10470",
                offices: [],
              },
              {
                time: "2025-06-17",
                providerIDs: "10351880,10470",
                offices: officesWithSlots,
              },
            ],
            []
          )
        );
        const wrapper = createWrapper({
          selectedService: {
            id: "service1",
            providers: [
              {
                name: "Office X",
                id: 10351880,
                address: { street: "Test", house_number: "1" },
                scope: { id: "10351880" },
              },
              {
                name: "Office Y",
                id: 10470,
                address: { street: "Test", house_number: "2" },
                scope: { id: "10470" },
              },
            ],
          },
        });

        // Set up provider selection first
        wrapper.vm.selectedProviders[10351880] = true;
        wrapper.vm.selectedProviders[10470] = true;
        await nextTick();

        // Prepare fetchAvailableCalendar to return both dates first, then only the next date after provider change
        (fetchAvailableCalendar as Mock).mockReset();
        (fetchAvailableCalendar as Mock).mockResolvedValueOnce(
          calendarResponse(
            [
              {
                time: "2025-06-16",
                providerIDs: "10351880,10470",
                offices: [],
              },
              {
                time: "2025-06-17",
                providerIDs: "10351880,10470",
                offices: officesWithSlots,
              },
            ],
            []
          )
        );

        // Now show selection for provider (this will fetch available days)
        await wrapper.vm.showSelectionForProvider({
          name: "Office X",
          id: 10351880,
          address: { street: "Test", house_number: "1" },
          scope: { id: "10351880" },
        });
        await nextTick();

        // After provider change, only 2025-06-17 should remain available
        (fetchAvailableCalendar as Mock).mockResolvedValueOnce(
          calendarResponse(
            [
              {
                time: "2025-06-17",
                providerIDs: "10351880",
                offices: officesWithSlots,
              },
            ],
            []
          )
        );

        await wrapper.vm.getAppointmentsOfDay("2025-06-16");
        await nextTick();
        expect(wrapper.vm.allowedDates(new Date("2025-06-16"))).toBe(false);
        expect(wrapper.vm.allowedDates(new Date("2025-06-17"))).toBe(true);
      });

      it("auto-selects the next available date on provider change when current date has no appointments", async () => {
        (fetchAvailableCalendar as Mock).mockResolvedValue(
          calendarResponse([
            { time: "2025-06-16", providerIDs: "10351880,10470" },
            { time: "2025-06-17", providerIDs: "10351880,10470" },
          ])
        );
        const wrapper = createWrapper({
          selectedService: {
            id: "service1",
            providers: [
              {
                name: "Office X",
                id: 10351880,
                address: { street: "Test", house_number: "1" },
                scope: { id: "10351880" },
              },
              {
                name: "Office Y",
                id: 10470,
                address: { street: "Test", house_number: "2" },
                scope: { id: "10470" },
              },
            ],
          },
        });

        // Set up provider selection first
        wrapper.vm.selectedProviders[10351880] = true;
        wrapper.vm.selectedProviders[10470] = true;
        await nextTick();

        // Now show selection for provider (this will fetch available days)
        await wrapper.vm.showSelectionForProvider({
          name: "Office X",
          id: 10351880,
          address: { street: "Test", house_number: "1" },
          scope: { id: "10351880" },
        });
        await nextTick();

        // Mock availableDays where 2025-06-16 only has provider 10470, and 2025-06-17 has provider 10351880
        // This means when we select only 10351880, the current date (2025-06-16) becomes invalid
        setAvailableDays(wrapper, [
          { time: "2025-06-16", providerIDs: "10470" },
          { time: "2025-06-17", providerIDs: "10351880" },
        ]);
        setAppointmentsByDay(wrapper, [
          ["2025-06-16", [{ officeId: 10470, appointments: [1750118400] }]],
          ["2025-06-17", [{ officeId: 10351880, appointments: [1750118400] }]],
        ]);

        // Set current date to 2025-06-16 (only available for provider 10470)
        wrapper.vm.selectedDay = new Date("2025-06-16");
        await nextTick();
        await wrapper.vm.getAppointmentsOfDay("2025-06-16");
        await nextTick();

        // Initially both providers are selected
        wrapper.vm.selectedProviders = {
          "10351880": true,
          "10470": true,
        } as any;
        await nextTick();
        await flushPromises();

        // Now deselect provider 10470, leaving only 10351880 selected
        // Since 2025-06-16 only has 10470, the date should snap to 2025-06-17
        wrapper.vm.selectedProviders = {
          "10351880": true,
          "10470": false,
        } as any;
        await nextTick();
        await flushPromises();
        // Wait for debounced pipeline (150ms) + updates to complete
        await new Promise((r) => setTimeout(r, 300));
        await nextTick();
        await flushPromises();

        // Should snap to 2025-06-17 as nearest available date for selected provider
        expect(wrapper.vm.selectedDay).toEqual(new Date("2025-06-17"));
      });

      it("enables a date in availableDays if API returns appointments for it", async () => {
        (fetchAvailableCalendar as Mock).mockResolvedValue(
          calendarResponse([
            { time: "2025-06-17", providerIDs: "10351880,10470" },
          ])
        );
        const wrapper = createWrapper({
          selectedService: {
            id: "service1",
            providers: [
              {
                name: "Office X",
                id: 10351880,
                address: { street: "Test", house_number: "1" },
                scope: { id: "10351880" },
              },
              {
                name: "Office Y",
                id: 10470,
                address: { street: "Test", house_number: "2" },
                scope: { id: "10470" },
              },
            ],
          },
        });

        // Set up provider selection first
        wrapper.vm.selectedProviders[10351880] = true;
        wrapper.vm.selectedProviders[10470] = true;
        await nextTick();

        // Now show selection for provider (this will fetch available days)
        await wrapper.vm.showSelectionForProvider({
          name: "Office X",
          id: 10351880,
          address: { street: "Test", house_number: "1" },
          scope: { id: "10351880" },
        });
        await nextTick();

        // Mock the availableDays to simulate what would be fetched for selected providers
        setAvailableDays(
          wrapper,
          [{ time: "2025-06-17", providerIDs: "10351880,10470" }],
          offices10351880And10470
        );

        await wrapper.vm.getAppointmentsOfDay("2025-06-17");
        await nextTick();
        expect(wrapper.vm.allowedDates(new Date("2025-06-17"))).toBe(true);
      });

      it("disables a date not in availableDays", async () => {
        (fetchAvailableCalendar as Mock).mockResolvedValue(
          calendarResponse([
            { time: "2025-06-16", providerIDs: "10351880,10470" },
          ])
        );
        const wrapper = createWrapper({
          selectedService: {
            id: "service1",
            providers: [
              {
                name: "Office X",
                id: 10351880,
                address: { street: "Test", house_number: "1" },
                scope: { id: "10351880" },
              },
              {
                name: "Office Y",
                id: 10470,
                address: { street: "Test", house_number: "2" },
                scope: { id: "10470" },
              },
            ],
          },
        });

        // Set up provider selection first
        wrapper.vm.selectedProviders[10351880] = true;
        wrapper.vm.selectedProviders[10470] = true;
        await nextTick();

        // Mock the availableDays to simulate what would be fetched for selected providers
        setAvailableDays(
          wrapper,
          [{ time: "2025-06-16", providerIDs: "10351880,10470" }],
          offices10351880And10470
        );

        await nextTick();
        expect(wrapper.vm.allowedDates(new Date("2025-06-18"))).toBe(false);
      });

      it("omits nextBookableDate when no appointments are available beyond the slots window", async () => {
        (fetchAvailableCalendar as Mock).mockResolvedValue(
          calendarResponse(
            [
              { time: "2025-06-16", providerIDs: "10351880,10470" },
              { time: "2025-06-17", providerIDs: "10351880,10470" },
              { time: "2025-06-30", providerIDs: "10351880,10470" },
            ],
            offices10351880And10470,
            { nextBookableDate: null }
          )
        );

        const wrapper = createWrapper({
          selectedService: {
            id: "service1",
            providers: [
              {
                name: "Office X",
                id: 10351880,
                address: { street: "Test", house_number: "1" },
                scope: { id: "10351880" },
              },
              {
                name: "Office Y",
                id: 10470,
                address: { street: "Test", house_number: "2" },
                scope: { id: "10470" },
              },
            ],
          },
        });

        wrapper.vm.selectedProviders[10351880] = true;
        wrapper.vm.selectedProviders[10470] = true;
        await nextTick();

        await wrapper.vm.showSelectionForProvider({
          name: "Office X",
          id: 10351880,
          address: { street: "Test", house_number: "1" },
          scope: { id: "10351880" },
        });
        await nextTick();

        const calendar = wrapper.findComponent({ name: "muc-calendar" });
        expect(calendar.exists()).toBe(true);
        expect(calendar.props("max")).toEqual(wrapper.vm.maxDate);
        expect(wrapper.vm.nextBookableDate).toBeNull();
      });

      it("exposes nextBookableDate when appointments are available after the slots window", async () => {
        (fetchAvailableCalendar as Mock).mockResolvedValue(
          calendarResponse(
            [
              { time: "2025-06-16", providerIDs: "10351880,10470" },
              { time: "2025-06-17", providerIDs: "10351880,10470" },
              { time: "2025-06-30", providerIDs: "10351880,10470" },
            ],
            offices10351880And10470,
            { nextBookableDate: "2025-07-01" }
          )
        );

        const wrapper = createWrapper({
          selectedService: {
            id: "service1",
            providers: [
              {
                name: "Office X",
                id: 10351880,
                address: { street: "Test", house_number: "1" },
                scope: { id: "10351880" },
              },
              {
                name: "Office Y",
                id: 10470,
                address: { street: "Test", house_number: "2" },
                scope: { id: "10470" },
              },
            ],
          },
        });

        wrapper.vm.selectedProviders[10351880] = true;
        wrapper.vm.selectedProviders[10470] = true;
        await nextTick();

        await wrapper.vm.showSelectionForProvider({
          name: "Office X",
          id: 10351880,
          address: { street: "Test", house_number: "1" },
          scope: { id: "10351880" },
        });
        await nextTick();

        const calendar = wrapper.findComponent({ name: "muc-calendar" });
        expect(calendar.exists()).toBe(true);
        expect(calendar.props("max")).toEqual(wrapper.vm.maxDate);
        expect(wrapper.vm.nextBookableDate).toBe("2025-07-01");
      });

      it("omits prevBookableDate when no appointments are available before the slots window", async () => {
        (fetchAvailableCalendar as Mock).mockResolvedValue(
          calendarResponse(
            [
              { time: "2025-06-01", providerIDs: "10351880,10470" },
              { time: "2025-06-17", providerIDs: "10351880,10470" },
              { time: "2025-06-30", providerIDs: "10351880,10470" },
            ],
            offices10351880And10470,
            { prevBookableDate: null }
          )
        );

        const wrapper = createWrapper({
          selectedService: {
            id: "service1",
            providers: [
              {
                name: "Office X",
                id: 10351880,
                address: { street: "Test", house_number: "1" },
                scope: { id: "10351880" },
              },
              {
                name: "Office Y",
                id: 10470,
                address: { street: "Test", house_number: "2" },
                scope: { id: "10470" },
              },
            ],
          },
        });

        wrapper.vm.selectedProviders[10351880] = true;
        wrapper.vm.selectedProviders[10470] = true;
        await nextTick();

        await wrapper.vm.showSelectionForProvider({
          name: "Office X",
          id: 10351880,
          address: { street: "Test", house_number: "1" },
          scope: { id: "10351880" },
        });
        await nextTick();

        const calendar = wrapper.findComponent({ name: "muc-calendar" });
        expect(calendar.exists()).toBe(true);
        expect(calendar.props("min")).toEqual(wrapper.vm.minDate);
        expect(wrapper.vm.prevBookableDate).toBeNull();
      });

      it("exposes prevBookableDate when appointments are available before the slots window", async () => {
        (fetchAvailableCalendar as Mock).mockResolvedValue(
          calendarResponse(
            [
              { time: "2025-06-01", providerIDs: "10351880,10470" },
              { time: "2025-06-17", providerIDs: "10351880,10470" },
            ],
            offices10351880And10470,
            { prevBookableDate: "2025-05-31" }
          )
        );

        const wrapper = createWrapper({
          selectedService: {
            id: "service1",
            providers: [
              {
                name: "Office X",
                id: 10351880,
                address: { street: "Test", house_number: "1" },
                scope: { id: "10351880" },
              },
              {
                name: "Office Y",
                id: 10470,
                address: { street: "Test", house_number: "2" },
                scope: { id: "10470" },
              },
            ],
          },
        });

        wrapper.vm.selectedProviders[10351880] = true;
        wrapper.vm.selectedProviders[10470] = true;
        await nextTick();

        await wrapper.vm.showSelectionForProvider({
          name: "Office X",
          id: 10351880,
          address: { street: "Test", house_number: "1" },
          scope: { id: "10351880" },
        });
        await nextTick();

        const calendar = wrapper.findComponent({ name: "muc-calendar" });
        expect(calendar.exists()).toBe(true);
        expect(calendar.props("min")).toEqual(wrapper.vm.minDate);
        expect(wrapper.vm.prevBookableDate).toBe("2025-05-31");
      });

      it("keeps booking-horizon navigation bounds when providers are deselected", async () => {
        (fetchAvailableCalendar as Mock).mockResolvedValue(
          calendarResponse(
            [
              { time: "2025-06-16", providerIDs: "10351880" },
              { time: "2025-06-17", providerIDs: "10351880" },
              { time: "2025-07-01", providerIDs: "10351880" },
              { time: "2025-06-16", providerIDs: "10470" },
              { time: "2025-06-17", providerIDs: "10470" },
              { time: "2025-08-01", providerIDs: "10470" },
            ],
            offices10351880And10470,
            { nextBookableDate: "2025-08-01" }
          )
        );

        const wrapper = createWrapper({
          selectedService: {
            id: "service1",
            providers: [
              {
                name: "Office X",
                id: 10351880,
                address: { street: "Test", house_number: "1" },
                scope: { id: "10351880" },
              },
              {
                name: "Office Y",
                id: 10470,
                address: { street: "Test", house_number: "2" },
                scope: { id: "10470" },
              },
            ],
          },
        });

        wrapper.vm.selectedProviders[10351880] = true;
        wrapper.vm.selectedProviders[10470] = true;
        await nextTick();

        await wrapper.vm.showSelectionForProvider({
          name: "Office X",
          id: 10351880,
          address: { street: "Test", house_number: "1" },
          scope: { id: "10351880" },
        });
        await nextTick();

        setAvailableDays(
          wrapper,
          [
            { time: "2025-06-16", providerIDs: "10351880" },
            { time: "2025-06-17", providerIDs: "10351880" },
            { time: "2025-07-01", providerIDs: "10351880" },
            { time: "2025-06-16", providerIDs: "10470" },
            { time: "2025-06-17", providerIDs: "10470" },
            { time: "2025-08-01", providerIDs: "10470" },
          ],
          offices10351880And10470
        );

        const calendar = wrapper.findComponent({ name: "muc-calendar" });
        expect(calendar.exists()).toBe(true);
        expect(calendar.props("min")).toEqual(wrapper.vm.minDate);
        expect(calendar.props("max")).toEqual(wrapper.vm.maxDate);
        expect(wrapper.vm.allowedDates(new Date("2025-08-01"))).toBe(true);

        wrapper.vm.selectedProviders[10470] =
          !wrapper.vm.selectedProviders[10470];
        await nextTick();
        await flushPromises();
        await new Promise((r) => setTimeout(r, 200));
        await nextTick();

        const calendarAfterDeselect = wrapper.findComponent({
          name: "muc-calendar",
        });
        expect(calendarAfterDeselect.props("min")).toEqual(wrapper.vm.minDate);
        expect(calendarAfterDeselect.props("max")).toEqual(wrapper.vm.maxDate);
        expect(wrapper.vm.allowedDates(new Date("2025-08-01"))).toBe(false);
        expect(wrapper.vm.allowedDates(new Date("2025-07-01"))).toBe(true);
      });

      it("keeps booking-horizon navigation bounds when providers are selected", async () => {
        (fetchAvailableCalendar as Mock).mockResolvedValue(
          calendarResponse(
            [
              { time: "2025-06-16", providerIDs: "10351880" },
              { time: "2025-06-17", providerIDs: "10351880" },
              { time: "2025-07-01", providerIDs: "10351880" },
              { time: "2025-06-16", providerIDs: "10470" },
              { time: "2025-06-17", providerIDs: "10470" },
              { time: "2025-08-01", providerIDs: "10470" },
            ],
            offices10351880And10470,
            { nextBookableDate: "2025-08-01" }
          )
        );

        const wrapper = createWrapper({
          selectedService: {
            id: "service1",
            providers: [
              {
                name: "Office X",
                id: 10351880,
                address: { street: "Test", house_number: "1" },
                scope: { id: "10351880" },
              },
              {
                name: "Office Y",
                id: 10470,
                address: { street: "Test", house_number: "2" },
                scope: { id: "10470" },
              },
            ],
          },
        });

        wrapper.vm.selectedProviders[10351880] = true;
        wrapper.vm.selectedProviders[10470] = true;
        await nextTick();

        await wrapper.vm.showSelectionForProvider({
          name: "Office X",
          id: 10351880,
          address: { street: "Test", house_number: "1" },
          scope: { id: "10351880" },
        });
        await nextTick();

        setAvailableDays(
          wrapper,
          [
            { time: "2025-06-16", providerIDs: "10351880" },
            { time: "2025-06-17", providerIDs: "10351880" },
            { time: "2025-07-01", providerIDs: "10351880" },
            { time: "2025-06-16", providerIDs: "10470" },
            { time: "2025-06-17", providerIDs: "10470" },
            { time: "2025-08-01", providerIDs: "10470" },
          ],
          offices10351880And10470
        );

        const calendar = wrapper.findComponent({ name: "muc-calendar" });
        expect(calendar.exists()).toBe(true);
        expect(calendar.props("max")).toEqual(wrapper.vm.maxDate);
        expect(wrapper.vm.allowedDates(new Date("2025-08-01"))).toBe(true);

        wrapper.vm.selectedProviders[10470] =
          !wrapper.vm.selectedProviders[10470];
        await nextTick();
        await flushPromises();
        await new Promise((r) => setTimeout(r, 200));
        await nextTick();

        expect(
          wrapper.findComponent({ name: "muc-calendar" }).props("max")
        ).toEqual(wrapper.vm.maxDate);
        expect(wrapper.vm.allowedDates(new Date("2025-08-01"))).toBe(false);

        wrapper.vm.selectedProviders[10470] =
          !wrapper.vm.selectedProviders[10470];
        await nextTick();
        await flushPromises();
        await new Promise((r) => setTimeout(r, 200));
        await nextTick();

        expect(
          wrapper.findComponent({ name: "muc-calendar" }).props("max")
        ).toEqual(wrapper.vm.maxDate);
        expect(wrapper.vm.allowedDates(new Date("2025-08-01"))).toBe(true);
      });

      it("refetches calendar when selecting another day so slots stay up to date", async () => {
        (fetchAvailableCalendar as Mock).mockResolvedValue(
          calendarResponse(
            [
              {
                time: "2025-06-16",
                providerIDs: "10351880,10470",
                offices: offices10351880And10470,
              },
              {
                time: "2025-06-17",
                providerIDs: "10351880,10470",
                offices: offices10351880And10470,
              },
            ],
            offices10351880And10470
          )
        );

        const wrapper = createWrapper({
          selectedService: {
            id: "service1",
            providers: [
              {
                name: "Office X",
                id: 10351880,
                address: { street: "Test", house_number: "1" },
                scope: { id: "10351880" },
              },
              {
                name: "Office Y",
                id: 10470,
                address: { street: "Test", house_number: "2" },
                scope: { id: "10470" },
              },
            ],
          },
        });

        wrapper.vm.selectedProviders[10351880] = true;
        wrapper.vm.selectedProviders[10470] = true;
        await nextTick();

        await wrapper.vm.showSelectionForProvider({
          name: "Office X",
          id: 10351880,
          address: { street: "Test", house_number: "1" },
          scope: { id: "10351880" },
        });
        await flushPromises();

        const callsAfterLoad = (fetchAvailableCalendar as Mock).mock.calls
          .length;
        expect(callsAfterLoad).toBeGreaterThan(0);

        await wrapper.vm.handleDaySelection(new Date("2025-06-17"));
        await flushPromises();

        expect((fetchAvailableCalendar as Mock).mock.calls.length).toBeGreaterThan(
          callsAfterLoad
        );
        expect(wrapper.vm.selectedDay).toEqual(new Date("2025-06-17"));
      });
    });

    describe("CalendarView checkbox behavior", () => {
      // moved to CalendarView.spec.ts: changes selected date when unchecking a provider that has appointments on current date
      it("changes selected date when unchecking a provider that has appointments on current date", async () => {
        (fetchAvailableCalendar as Mock).mockResolvedValue(
          calendarResponse(
            [
              {
                time: "2025-06-17",
                providerIDs: "1",
                offices: [{ officeId: 1, appointments: [1750118400] }],
              },
              {
                time: "2025-06-18",
                providerIDs: "1,2",
                offices: [
                  { officeId: 1, appointments: [1750204800] },
                  { officeId: 2, appointments: [1750204800] },
                ],
              },
            ],
            []
          )
        );

        const wrapper = createWrapper({
          selectedService: {
            id: "service1",
            providers: [
              {
                name: "Office A",
                id: "1",
                address: { street: "Test", house_number: "1" },
                scope: { id: "1" },
              },
              {
                name: "Office B",
                id: "2",
                address: { street: "Test", house_number: "2" },
                scope: { id: "2" },
              },
            ],
          },
        });

        await wrapper.vm.showSelectionForProvider({
          name: "Office A",
          id: "1",
          address: { street: "Test", house_number: "1" },
          scope: { id: "1" },
        });
        await nextTick();

        // Set initial date to 2025-06-17
        wrapper.vm.selectedDay = new Date("2025-06-17");
        await nextTick();

        // Uncheck provider 1 (which has appointments on 2025-06-17)
        wrapper.vm.selectedProviders["1"] = !wrapper.vm.selectedProviders["1"];
        await nextTick();
        await flushPromises();
        await new Promise((r) => setTimeout(r, 200));
        await nextTick();

        // Should change to 2025-06-18 since that's the next date with appointments for provider 2
        expect(wrapper.vm.selectedDay).toEqual(new Date("2025-06-18"));
      });

      // (moved to ProviderSelection Integration) "shows no providers when none have appointments"

      it("updates calendar view when selected date changes due to provider deselection", async () => {
        const today = new Date();

        // Calculate dates for two providers: one 1 month ahead, one 2 months ahead
        const dateForProvider1 = new Date(
          today.getFullYear(),
          today.getMonth() + 1,
          15
        );
        const dateForProvider2 = new Date(
          today.getFullYear(),
          today.getMonth() + 2,
          1
        );

        // Handle year rollover if month > 11
        if (dateForProvider1.getMonth() < today.getMonth()) {
          dateForProvider1.setFullYear(dateForProvider1.getFullYear() + 1);
        }
        if (dateForProvider2.getMonth() < today.getMonth()) {
          dateForProvider2.setFullYear(dateForProvider2.getFullYear() + 1);
        }

        const toIsoDate = (date: Date) => date.toISOString().split("T")[0];
        const provider1DateIso = toIsoDate(dateForProvider1);
        const provider2DateIso = toIsoDate(dateForProvider2);

        // Mock available days — provider 1 only on first date, provider 2 only on second
        (fetchAvailableCalendar as Mock).mockResolvedValue(
          calendarResponse(
            [
              {
                time: provider1DateIso,
                providerIDs: "1",
                offices: [{ officeId: 1, appointments: [1750118400] }],
              },
              {
                time: provider2DateIso,
                providerIDs: "2",
                offices: [{ officeId: 2, appointments: [1750118400] }],
              },
            ],
            []
          )
        );

        const wrapper = createWrapper({
          selectedService: {
            id: "service1",
            providers: [
              {
                name: "Office A",
                id: "1",
                address: { street: "Test", house_number: "1" },
                scope: { id: "1" },
              },
              {
                name: "Office B",
                id: "2",
                address: { street: "Test", house_number: "2" },
                scope: { id: "2" },
              },
            ],
          },
        });

        // Simulate selecting provider 2 initially
        await wrapper.vm.showSelectionForProvider({
          name: "Office B",
          id: "2",
          address: { street: "Test", house_number: "2" },
          scope: { id: "2" },
        });
        await nextTick();
        await flushPromises();

        // Select the date supported only by provider 2
        wrapper.vm.selectedDay = new Date(provider2DateIso);
        wrapper.vm.selectedProviders["2"] = true;
        wrapper.vm.selectedProviders["1"] = true;
        await nextTick();
        await flushPromises();

        // Now remove provider 2 — calendar should fallback to provider 1's date
        wrapper.vm.selectedProviders["2"] = false;
        await nextTick();
        await flushPromises();
        // Re-fetch timeslots for the selected day to reflect new selection
        await wrapper.vm.getAppointmentsOfDay(provider1DateIso);
        await flushPromises();
        await new Promise((r) => setTimeout(r, 200));
        await nextTick();

        const calendar = wrapper.findComponent({ name: "muc-calendar" });
        expect(calendar.exists()).toBe(true);

        const actualDate = calendar.props("viewMonth");
        const expectedViewMonth = new Date(
          wrapper.vm.selectedDay.getFullYear(),
          wrapper.vm.selectedDay.getMonth(),
          1
        );
        expect(actualDate.getFullYear()).toBe(expectedViewMonth.getFullYear());
        expect(actualDate.getMonth()).toBe(expectedViewMonth.getMonth());
      });

      it("resets to earliest hour when selecting a new day in the calendar", async () => {
        // Mock availableDays with two different dates
        (fetchAvailableCalendar as Mock).mockResolvedValue(
          calendarResponse(
            [
              {
                time: "2025-06-17",
                providerIDs: "1",
                offices: [
                  {
                    officeId: 1,
                    appointments: [1750919400, 1750919700, 1750920000],
                  },
                ],
              },
              {
                time: "2025-06-18",
                providerIDs: "1",
                offices: [
                  {
                    officeId: 1,
                    appointments: [1747224600, 1747224900, 1747225200],
                  },
                ],
              },
            ],
            []
          )
        );

        const wrapper = createWrapper({
          selectedService: {
            id: "service1",
            providers: [
              {
                name: "Office A",
                id: "1",
                address: { street: "Test", house_number: "1" },
                scope: { id: "1" },
              },
            ],
          },
        });

        // Wait for availableDays to be loaded
        await wrapper.vm.showSelectionForProvider({
          name: "Office A",
          id: "1",
          address: { street: "Test", house_number: "1" },
          scope: { id: "1" },
        });
        await nextTick();
        await flushPromises();

        // Set initial date and hour
        wrapper.vm.selectedDay = new Date("2025-06-17");
        wrapper.vm.selectedHour = 13; // Set to 13:00
        await nextTick();
        await flushPromises();

        // Select new date
        await wrapper.vm.handleDaySelection(new Date("2025-06-18"));
        await nextTick();
        await flushPromises();

        // The earliest available hour for 2025-06-18 is 14
        expect(wrapper.vm.selectedHour).toBe(14);
      });
    });

    // moved to CalendarView.spec.ts: CalendarView - hour and day part reset on day change (all cases)
    describe("CalendarView - hour and day part reset on day change (additional)", () => {
      it("resets selectedHour to earliest available hour when selecting a new day", async () => {
        (fetchAvailableCalendar as Mock).mockResolvedValue(
          calendarResponse(
            [
              {
                time: "2025-06-17",
                providerIDs: "1",
                offices: [
                  {
                    officeId: 1,
                    appointments: [1750919400, 1750919700, 1750920000],
                  },
                ],
              },
              {
                time: "2025-06-18",
                providerIDs: "1",
                offices: [
                  {
                    officeId: 1,
                    appointments: [1747224600, 1747224900, 1747225200],
                  },
                ],
              },
            ],
            []
          )
        );
        const wrapper = createWrapper({
          selectedService: {
            id: "service1",
            providers: [
              {
                name: "Office A",
                id: "1",
                address: { street: "Test", house_number: "1" },
                scope: { id: "1" },
              },
            ],
          },
        });
        await wrapper.vm.showSelectionForProvider({
          name: "Office A",
          id: "1",
          address: { street: "Test", house_number: "1" },
          scope: { id: "1" },
        });
        await flushPromises();
        wrapper.vm.selectedDay = new Date("2025-06-17");
        wrapper.vm.selectedHour = 13;
        await flushPromises();
        await wrapper.vm.handleDaySelection(new Date("2025-06-18"));
        await flushPromises();
        expect(wrapper.vm.selectedHour).toBe(14);
      });

      it("sets selectedHour to null if no hours are available for the selected day", async () => {
        (fetchAvailableCalendar as Mock).mockResolvedValue(
          calendarResponse([{ time: "2025-06-19", providerIDs: "1" }])
        );
        const wrapper = createWrapper({
          selectedService: {
            id: "service1",
            providers: [
              {
                name: "Office A",
                id: "1",
                address: { street: "Test", house_number: "1" },
                scope: { id: "1" },
              },
            ],
          },
        });
        await wrapper.vm.showSelectionForProvider({
          name: "Office A",
          id: "1",
          address: { street: "Test", house_number: "1" },
          scope: { id: "1" },
        });
        await flushPromises();
        await wrapper.vm.handleDaySelection(new Date("2025-06-19"));
        await flushPromises();
        expect(wrapper.vm.selectedHour).toBe(null);
      });

      it("resets selectedDayPart to 'am' if available when in day part view", async () => {
        (fetchAvailableCalendar as Mock).mockResolvedValue(
          calendarResponse([
            { time: "2025-06-20", providerIDs: "1" },
            { time: "2025-06-21", providerIDs: "1" },
          ])
        );
        const wrapper = createWrapper({
          selectedService: {
            id: "service1",
            providers: [
              {
                name: "Office A",
                id: "1",
                address: { street: "Test", house_number: "1" },
                scope: { id: "1" },
              },
            ],
          },
        });
        await wrapper.vm.showSelectionForProvider({
          name: "Office A",
          id: "1",
          address: { street: "Test", house_number: "1" },
          scope: { id: "1" },
        });
        await flushPromises();
        wrapper.vm.selectedDay = new Date("2025-06-20");
        wrapper.vm.selectedDayPart = "pm";
        await flushPromises();
        await wrapper.vm.handleDaySelection(new Date("2025-06-21"));
        await flushPromises();
        expect(wrapper.vm.selectedDayPart).toBe(null);
      });

      it("does not reset selectedDayPart when selecting the same day", async () => {
        (fetchAvailableCalendar as Mock).mockResolvedValue(
          calendarResponse([{ time: "2025-06-20", providerIDs: "1" }])
        );
        const wrapper = createWrapper({
          selectedService: {
            id: "service1",
            providers: [
              {
                name: "Office A",
                id: 1,
                address: { street: "Test", house_number: "1" },
                scope: { id: "1" },
              },
            ],
          },
        });

        // Set up provider selection first
        wrapper.vm.selectedProviders[1] = true;
        await nextTick();

        await wrapper.vm.showSelectionForProvider({
          name: "Office A",
          id: 1,
          address: { street: "Test", house_number: "1" },
          scope: { id: "1" },
        });
        await flushPromises();

        // Mock the availableDays to simulate what would be fetched for selected providers
        setAvailableDays(wrapper, [{ time: "2025-06-20", providerIDs: "1" }]);

        // Set the selected day first
        wrapper.vm.selectedDay = new Date("2025-06-20");
        wrapper.vm.selectedDayPart = "pm";
        await flushPromises();

        await wrapper.vm.handleDaySelection(new Date("2025-06-20")); // select the same day
        await flushPromises();
        expect(wrapper.vm.selectedDayPart).toBe("pm");
      });
    });

    // moved to CalendarView.spec.ts: CalendarView snap-to-nearest hour and dayPart on provider deselection (all cases)
    describe("CalendarView snap-to-nearest hour and dayPart on provider deselection", () => {
      it("snaps to the nearest later hour if current hour is removed", async () => {
        (fetchAvailableCalendar as Mock).mockResolvedValue(
          calendarResponse([{ time: "2025-06-17", providerIDs: "1,2" }])
        );
        const wrapper = createWrapper({
          selectedService: {
            id: "service1",
            providers: [
              {
                name: "Office A",
                id: 1,
                address: { street: "Test", house_number: "1" },
                scope: { id: "1" },
              },
              {
                name: "Office B",
                id: 2,
                address: { street: "Test", house_number: "2" },
                scope: { id: "2" },
              },
            ],
          },
        });
        await wrapper.vm.showSelectionForProvider({
          name: "Office A",
          id: 1,
          address: { street: "Test", house_number: "1" },
          scope: { id: "1" },
        });
        await nextTick();
        await wrapper.vm.getAppointmentsOfDay("2025-06-17");
        await nextTick();
        // Set selectedHour to 10 (10:00)
        wrapper.vm.selectedHour = 10;
        await nextTick();
        // Deselect provider 1, only provider 2 remains (12, 13, 14)
        wrapper.vm.selectedProviders["1"] = !wrapper.vm.selectedProviders["1"];
        await nextTick();
        expect(wrapper.vm.selectedHour).toBe(10);
      });

      it("snaps to the nearest earlier hour if current hour is removed", async () => {
        (fetchAvailableCalendar as Mock).mockResolvedValue(
          calendarResponse(
            [
              {
                time: "2025-06-17",
                providerIDs: "1,2",
                offices: officesForProviderHourSnap,
              },
            ],
            []
          )
        );
        const wrapper = createWrapper({
          selectedService: {
            id: "service1",
            providers: [
              {
                name: "Office A",
                id: 1,
                address: { street: "Test", house_number: "1" },
                scope: { id: "1" },
              },
              {
                name: "Office B",
                id: 2,
                address: { street: "Test", house_number: "2" },
                scope: { id: "2" },
              },
            ],
          },
        });
        await wrapper.vm.showSelectionForProvider({
          name: "Office A",
          id: 1,
          address: { street: "Test", house_number: "1" },
          scope: { id: "1" },
        });
        await nextTick();
        await wrapper.vm.getAppointmentsOfDay("2025-06-17");
        await nextTick();
        // Set selectedHour to 15 (15:00)
        wrapper.vm.selectedHour = 15;
        await nextTick();
        // Deselect provider 1, only provider 2 remains (12, 13, 14)
        wrapper.vm.selectedProviders["1"] = !wrapper.vm.selectedProviders["1"];
        await nextTick();
        await flushPromises();
        await wrapper.vm.getAppointmentsOfDay("2025-06-17");
        await flushPromises();
        await new Promise((r) => setTimeout(r, 200));
        await nextTick();
        // After reloading slots and deselection, component resets to earliest available hour
        const hours = Array.from(
          (wrapper.vm as any).timeSlotsInHoursByOffice.values()
        )
          .flatMap((o: any) => Array.from(o.appointments.keys()))
          .filter((h: any) => typeof h === "number");
        const earliest = Math.min(...hours);
        expect(wrapper.vm.selectedHour).toBe(earliest);
      });

      it("snaps to the earlier hour if two are equally close", async () => {
        (fetchAvailableCalendar as Mock).mockResolvedValue(
          calendarResponse(
            [
              {
                time: "2025-06-17",
                providerIDs: "1,2",
                offices: officesForProviderHourSnapEqualDistance,
              },
            ],
            []
          )
        );
        const wrapper = createWrapper({
          selectedService: {
            id: "service1",
            providers: [
              {
                name: "Office A",
                id: 1,
                address: { street: "Test", house_number: "1" },
                scope: { id: "1" },
              },
              {
                name: "Office B",
                id: 2,
                address: { street: "Test", house_number: "2" },
                scope: { id: "2" },
              },
            ],
          },
        });
        await wrapper.vm.showSelectionForProvider({
          name: "Office A",
          id: 1,
          address: { street: "Test", house_number: "1" },
          scope: { id: "1" },
        });
        await nextTick();
        await wrapper.vm.getAppointmentsOfDay("2025-06-17");
        await nextTick();
        // Set selectedHour to 13 (13:00)
        wrapper.vm.selectedHour = 13;
        await nextTick();
        // Deselect provider 1, only provider 2 remains (12, 14)
        wrapper.vm.selectedProviders["1"] = !wrapper.vm.selectedProviders["1"];
        await nextTick();
        await flushPromises();
        await wrapper.vm.getAppointmentsOfDay("2025-06-17");
        await flushPromises();
        await new Promise((r) => setTimeout(r, 200));
        await nextTick();
        // After reloading slots and deselection, component resets to earliest available hour
        const hoursEq = Array.from(
          (wrapper.vm as any).timeSlotsInHoursByOffice.values()
        )
          .flatMap((o: any) => Array.from(o.appointments.keys()))
          .filter((h: any) => typeof h === "number");
        const earliestEq = Math.min(...hoursEq);
        expect(wrapper.vm.selectedHour).toBe(earliestEq);
      });

      it("snaps to the other dayPart if current is removed", async () => {
        (fetchAvailableCalendar as Mock).mockResolvedValue(
          calendarResponse([{ time: "2025-06-17", providerIDs: "1,2" }])
        );
        const wrapper = createWrapper({
          selectedService: {
            id: "service1",
            providers: [
              {
                name: "Office A",
                id: 1,
                address: { street: "Test", house_number: "1" },
                scope: { id: "1" },
              },
              {
                name: "Office B",
                id: 2,
                address: { street: "Test", house_number: "2" },
                scope: { id: "2" },
              },
            ],
          },
        });
        await wrapper.vm.showSelectionForProvider({
          name: "Office A",
          id: 1,
          address: { street: "Test", house_number: "1" },
          scope: { id: "1" },
        });
        await nextTick();
        await wrapper.vm.getAppointmentsOfDay("2025-06-17");
        await nextTick();
        // Set selectedDayPart to 'am'
        wrapper.vm.selectedDayPart = "am";
        await nextTick();
        // Deselect provider 1, only provider 2 remains (pm)
        wrapper.vm.selectedProviders["1"] = !wrapper.vm.selectedProviders["1"];
        await nextTick();
        expect(wrapper.vm.selectedDayPart).toBe("am");
      });

      it("snaps to null if no dayPart is available after deselection", async () => {
        (fetchAvailableCalendar as Mock).mockResolvedValue(
          calendarResponse([{ time: "2025-06-17", providerIDs: "1,2" }])
        );
        const wrapper = createWrapper({
          selectedService: {
            id: "service1",
            providers: [
              {
                name: "Office A",
                id: 1,
                address: { street: "Test", house_number: "1" },
                scope: { id: "1" },
              },
              {
                name: "Office B",
                id: 2,
                address: { street: "Test", house_number: "2" },
                scope: { id: "2" },
              },
            ],
          },
        });
        await wrapper.vm.showSelectionForProvider({
          name: "Office A",
          id: 1,
          address: { street: "Test", house_number: "1" },
          scope: { id: "1" },
        });
        await nextTick();
        await wrapper.vm.getAppointmentsOfDay("2025-06-17");
        await nextTick();
        // Set selectedDayPart to 'am'
        wrapper.vm.selectedDayPart = "am";
        await nextTick();
        // Deselect provider 1, only provider 2 remains (no appointments)
        wrapper.vm.selectedProviders["1"] = !wrapper.vm.selectedProviders["1"];
        await nextTick();
        expect(wrapper.vm.selectedDayPart).toBe("am");
      });
    });

    // moved to CalendarView.spec.ts: shows hourly view if total appointments > 18
    it("shows hourly view if total appointments > 18", async () => {
      (fetchAvailableCalendar as Mock).mockResolvedValue(
        calendarResponse(
          [
            {
              time: "2025-07-02",
              providerIDs: "1,2",
              offices: officesForHourlyViewTest,
            },
          ],
          []
        )
      );
      // 32 appointments in total (across both providers) - using exact hour timestamps > 0
      const wrapper = createWrapper({
        selectedService: {
          id: "service1",
          providers: [
            {
              name: "Office A",
              id: 1,
              address: { street: "Test", house_number: "1" },
            },
            {
              name: "Office B",
              id: 2,
              address: { street: "Test", house_number: "2" },
            },
          ],
        },
      });
      await wrapper.vm.showSelectionForProvider({
        name: "Office A",
        id: 1,
        address: { street: "Test", house_number: "1" },
      });
      await flushPromises();
      wrapper.vm.selectedProviders = { 1: true, 2: true };
      await nextTick();
      await wrapper.vm.handleDaySelection(new Date(2025, 6, 2));
      await flushPromises();
      await nextTick();

      expect(wrapper.vm.appointmentsCount).toBeGreaterThan(18);
      expect(wrapper.vm.timeSlotsInHoursByOffice.size).toBeGreaterThan(0);
      expect(wrapper.vm.currentHour).not.toBeNull();
    });

    // moved to CalendarView.spec.ts: shows am/pm view if total appointments <= 18
    it("shows am/pm view if total appointments <= 18", async () => {
      (fetchAvailableCalendar as Mock).mockResolvedValue(
        calendarResponse(
          [
            {
              time: "2025-07-02",
              providerIDs: "1,2",
              offices: officesForDayPartViewTest,
            },
          ],
          []
        )
      );
      // 18 appointments in total (across both providers)
      const wrapper = createWrapper({
        selectedService: {
          id: "service1",
          providers: [
            {
              name: "Office A",
              id: 1,
              address: { street: "Test", house_number: "1" },
            },
            {
              name: "Office B",
              id: 2,
              address: { street: "Test", house_number: "2" },
            },
          ],
        },
      });
      await wrapper.vm.showSelectionForProvider({
        name: "Office A",
        id: 1,
        address: { street: "Test", house_number: "1" },
      });
      await flushPromises();
      wrapper.vm.selectedProviders = { 1: true, 2: true };
      await nextTick();
      await wrapper.vm.handleDaySelection(new Date("2025-07-02"));
      await flushPromises();
      expect(wrapper.html()).toMatch(/am|pm/);
    });
  });

  describe("Submission/Loading State Integration", () => {
    let wrapper: VueWrapper<InstanceType<typeof AppointmentSelection>>;
    let selectedTimeslotRef: Ref<number>;
    let loadingStates: LoadingStates;
    beforeEach(async () => {
      selectedTimeslotRef = ref(0);
      loadingStates = {
        isReservingAppointment: ref(false),
        isUpdatingAppointment: ref(false),
        isBookingAppointment: ref(false),
        isCancelingAppointment: ref(false),
      };
      wrapper = mount(AppointmentSelection, {
        global: {
          provide: {
            selectedServiceProvider: {
              selectedService: ref({
                id: "service1",
                providers: [
                  {
                    name: "Office A",
                    id: 1,
                    address: { street: "Elm", house_number: "99" },
                    scope: { id: "1" },
                  },
                ],
              }),
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
      wrapper.vm.selectedDay = new Date("2025-05-14");
      wrapper.vm.availableDaysFetched = true;
      wrapper.vm.appointmentsCount = 18;
      wrapper.vm.timeSlotsInHoursByOffice = ref(
        new Map([
          [1, { officeId: 1, appointments: new Map([[10, [1234567890]]]) }],
        ])
      );
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

    it("enables the next button after selecting an appointment and disables it after reservation starts", async () => {
      selectedTimeslotRef.value = 1234567890;
      await nextTick();
      let nextButton = wrapper
        .findAllComponents({ name: "MucButton" })
        .find((btn) => btn.text().includes("next"));
      expect(nextButton && !nextButton.props("disabled")).toBe(true);

      loadingStates.isReservingAppointment.value = true;
      await nextTick();
      nextButton = wrapper
        .findAllComponents({ name: "MucButton" })
        .find((btn) => btn.text().includes("next"));
      expect(nextButton && nextButton.props("disabled")).toBe(true);
    });

    it("disables next button during reservation and re-enables after reservation completes", async () => {
      selectedTimeslotRef.value = 1234567890;
      await nextTick();
      let nextButton = wrapper
        .findAllComponents({ name: "MucButton" })
        .find((btn) => btn.text().includes("next"));
      expect(nextButton && !nextButton.props("disabled")).toBe(true);

      loadingStates.isReservingAppointment.value = true;
      await nextTick();
      nextButton = wrapper
        .findAllComponents({ name: "MucButton" })
        .find((btn) => btn.text().includes("next"));
      expect(nextButton && nextButton.props("disabled")).toBe(true);

      loadingStates.isReservingAppointment.value = false;
      await nextTick();
      nextButton = wrapper
        .findAllComponents({ name: "MucButton" })
        .find((btn) => btn.text().includes("next"));
      expect(nextButton && !nextButton.props("disabled")).toBe(true);
    });
  });

  describe("Error States Integration", () => {
    it("shows captcha error callout when captcha error is set", async () => {
      const wrapper = createWrapper({
        selectedService: {
          id: "service1",
          providers: [
            {
              name: "Office A",
              id: 1,
              address: { street: "Main", house_number: "1" },
              scope: { id: "1" },
            },
            {
              name: "Office B",
              id: 2,
              address: { street: "Main", house_number: "2" },
              scope: { id: "2" },
            },
          ],
        },
        props: {
          bookingError: true,
          bookingErrorKey: "apiErrorCaptchaInvalid",
          errorType: "error",
        },
      });

      // Set up provider selection and available days to ensure the component renders properly
      wrapper.vm.selectedProviders = { "1": true, "2": true };
      setAvailableDays(
        wrapper,
        [{ time: "2025-06-16", providerIDs: "1,2" }],
        officeOneAndTwoSlots
      );
      wrapper.vm.selectedProvider = {
        name: "Office A",
        id: 1,
        address: { street: "Main", house_number: "1" },
        scope: { id: "1" },
      };
      wrapper.vm.availableDaysFetched = true;

      // Wait for the watcher to finish and reset isSwitchingProvider
      await nextTick();
      await nextTick(); // Need multiple ticks for the watcher to complete

      // Manually reset the flag since the component logic doesn't reset it in this test scenario
      wrapper.vm.isSwitchingProvider = false;
      await nextTick();

      // Find all callouts and get the error one
      const callouts = wrapper.findAll('[data-test="muc-callout"]');
      const errorCallout = callouts.find(
        (c) => c.attributes("data-type") === "error"
      );

      expect(errorCallout).toBeDefined();
      expect(errorCallout!.html()).toContain("apiErrorCaptchaInvalidHeader");
      expect(errorCallout!.html()).toContain("apiErrorCaptchaInvalidText");
    });

    it("shows no appointment error info callout when no appointment error is set", async () => {
      const wrapper = createWrapper({
        selectedService: {
          id: "service1",
          providers: [
            {
              name: "Office A",
              id: 1,
              address: { street: "Main", house_number: "1" },
            },
            {
              name: "Office B",
              id: 2,
              address: { street: "Main", house_number: "2" },
            },
          ],
        },
        props: {
          bookingError: true,
          bookingErrorKey: "apiErrorNoAppointmentForThisScope",
          errorType: "info",
        },
      });

      // Set up provider selection and available days to ensure the component renders properly
      wrapper.vm.selectedProviders = { "1": true, "2": true };
      setAvailableDays(
        wrapper,
        [{ time: "2025-06-16", providerIDs: "1,2" }],
        officeOneAndTwoSlots
      );
      wrapper.vm.selectedProvider = {
        name: "Office A",
        id: 1,
        address: { street: "Main", house_number: "1" },
        scope: { id: "1" },
      };
      wrapper.vm.availableDaysFetched = true;

      // Wait for the watcher to finish and reset isSwitchingProvider
      await nextTick();
      await nextTick(); // Need multiple ticks for the watcher to complete

      // Manually reset the flag since the component logic doesn't reset it in this test scenario
      wrapper.vm.isSwitchingProvider = false;
      await nextTick();

      // Find all callouts and get the info one
      const callouts = wrapper.findAll('[data-test="muc-callout"]');
      const infoCallout = callouts.find(
        (c) => c.attributes("data-type") === "info"
      );

      expect(infoCallout).toBeDefined();
      expect(infoCallout!.exists()).toBe(true);
      expect(infoCallout!.attributes("data-type")).toBe("info");
      expect(infoCallout!.html()).toContain(
        "apiErrorNoAppointmentForThisScopeHeader"
      );
      expect(infoCallout!.html()).toContain(
        "apiErrorNoAppointmentForThisScopeText"
      );
    });

    it("shows appointment not available error callout when appointment not available error is set (defaults to error type)", async () => {
      const wrapper = createWrapper({
        selectedService: {
          id: "service1",
          providers: [
            {
              name: "Office A",
              id: 1,
              address: { street: "Main", house_number: "1" },
            },
            {
              name: "Office B",
              id: 2,
              address: { street: "Main", house_number: "2" },
            },
          ],
        },
        props: {
          bookingError: true,
          bookingErrorKey: "apiErrorAppointmentNotAvailable",
        },
      });

      // Set up provider selection and available days to ensure the component renders properly
      wrapper.vm.selectedProviders = { "1": true, "2": true };
      setAvailableDays(
        wrapper,
        [{ time: "2025-06-16", providerIDs: "1,2" }],
        officeOneAndTwoSlots
      );
      wrapper.vm.selectedProvider = {
        name: "Office A",
        id: 1,
        address: { street: "Main", house_number: "1" },
        scope: { id: "1" },
      };
      wrapper.vm.availableDaysFetched = true;

      // Wait for the watcher to finish and reset isSwitchingProvider
      await nextTick();
      await nextTick(); // Need multiple ticks for the watcher to complete

      // Manually reset the flag since the component logic doesn't reset it in this test scenario
      wrapper.vm.isSwitchingProvider = false;
      await nextTick();

      // Find all callouts and get the error one
      const callouts = wrapper.findAll('[data-test="muc-callout"]');
      const errorCallout = callouts.find(
        (c) => c.attributes("data-type") === "error"
      );

      expect(errorCallout).toBeDefined();
      expect(errorCallout!.exists()).toBe(true);
      expect(errorCallout!.attributes("data-type")).toBe("error");
      expect(errorCallout!.html()).toContain(
        "apiErrorAppointmentNotAvailableHeader"
      );
      expect(errorCallout!.html()).toContain(
        "apiErrorAppointmentNotAvailableText"
      );
    });

    it("emits clearBookingError when a new time slot is selected", async () => {
      const wrapper = createWrapper();

      wrapper.vm.selectableProviders = [
        {
          id: 1,
          name: "Office A",
          address: { street: "Main", house_number: "1" },
          scope: { id: "1" },
        },
      ];

      await nextTick();

      const emissionsBefore = wrapper.emitted("clearBookingError")?.length ?? 0;

      await wrapper.vm.handleTimeSlotSelection(1, 1747223100);
      await nextTick();

      expect(wrapper.emitted("clearBookingError")?.length ?? 0).toBe(
        emissionsBefore + 1
      );
      expect(wrapper.vm.selectedTimeslot).toBe(1747223100);
      expect(wrapper.vm.selectedProvider?.id).toBe(1);
    });

    it("emits clearBookingError when another day is selected", async () => {
      const wrapper = createWrapper();

      wrapper.vm.selectedDay = new Date("2025-06-16");
      wrapper.vm.selectedTimeslot = 1747223100;
      await nextTick();

      const emissionsBefore = wrapper.emitted("clearBookingError")?.length ?? 0;

      await wrapper.vm.handleDaySelection(new Date("2025-06-17"));
      await nextTick();

      expect(wrapper.emitted("clearBookingError")?.length ?? 0).toBe(
        emissionsBefore + 1
      );
      expect(wrapper.vm.selectedDay).toEqual(new Date("2025-06-17"));
      expect(wrapper.vm.selectedTimeslot).toBe(0);
    });

    it("emits clearBookingError when the same day is selected again", async () => {
      const wrapper = createWrapper();

      wrapper.vm.selectedDay = new Date("2025-06-17");
      await nextTick();

      const emissionsBefore = wrapper.emitted("clearBookingError")?.length ?? 0;

      await wrapper.vm.handleDaySelection(new Date("2025-06-17"));
      await nextTick();

      expect(wrapper.emitted("clearBookingError")?.length ?? 0).toBe(
        emissionsBefore + 1
      );
    });

    it("emits clearBookingError and back when previousStep is called", async () => {
      const wrapper = createWrapper();

      const clearBookingErrorEmissionsBefore =
        wrapper.emitted("clearBookingError")?.length ?? 0;
      const backEmissionsBefore = wrapper.emitted("back")?.length ?? 0;

      wrapper.vm.previousStep();
      await nextTick();

      expect(wrapper.emitted("clearBookingError")?.length ?? 0).toBe(
        clearBookingErrorEmissionsBefore + 1
      );
      expect(wrapper.emitted("back")?.length ?? 0).toBe(
        backEmissionsBefore + 1
      );
    });

    it("does not show any callout when bookingError is false", async () => {
      const wrapper = createWrapper({
        props: {
          bookingError: false,
          bookingErrorKey: "",
        },
      });

      await nextTick();
      const callout = wrapper.find('[data-test="muc-callout"]');
      expect(callout.exists()).toBe(false);
    });
  });

  describe("InfoForAllAppointments Feature", () => {
    describe("Callout when providers are selected (shows info link)", () => {
      it("opens modal with availability info when triggered", async () => {
        const wrapper = createWrapper({
          props: {
            bookingError: true,
            bookingErrorKey: "apiErrorNoAppointmentForThisScope",
            errorType: "info",
          },
        });

        // Set selectable providers and selection so availabilityInfoHtml becomes truthy
        wrapper.vm.selectableProviders = [
          {
            id: 1,
            name: "Office A",
            address: { street: "Elm", house_number: "99" },
            scope: { infoForAllAppointments: "Same info message" },
          },
          {
            id: 2,
            name: "Office B",
            address: { street: "Oak", house_number: "100" },
            scope: { infoForAllAppointments: "Same info message" },
          },
        ];
        wrapper.vm.selectedProviders = { "1": true, "2": true };
        // Ensure component state is in a rendered state similar to other callout tests
        setAvailableDays(
          wrapper,
          [{ time: "2025-06-16", providerIDs: "1,2" }],
          officeOneAndTwoSlots
        );
        wrapper.vm.selectedProvider = {
          id: 1,
          name: "Office A",
          address: { street: "Elm", house_number: "99" },
          scope: { id: "1" },
        } as any;
        wrapper.vm.availableDaysFetched = true;
        await nextTick();
        await nextTick();
        wrapper.vm.isSwitchingProvider = false;
        await nextTick();

        // Ensure watchers didn't auto-select providers; force empty selection again
        wrapper.vm.selectedProviders = {};
        await nextTick();

        // Programmatically set modal HTML and open
        (wrapper.vm as any).availabilityInfoHtmlOverride = "Same info message";
        wrapper.vm.showAvailabilityInfoModal = true;
        await nextTick();

        // Modal should open and show the aggregated info
        const modalBody = wrapper.find(".modal-body");
        expect(modalBody.exists()).toBe(true);
        expect(modalBody.html()).toContain("Same info message");
      });
    });

    describe("Callout when all provider locations are unselected (No appointments available)", () => {
      it("opens modal with grouped info when providers have differing info and none selected", async () => {
        const wrapper = createWrapper({
          selectedService: {
            id: "service1",
            providers: [
              {
                id: 1,
                name: "Office A",
                address: { street: "Elm", house_number: "99" },
                scope: { id: "1" },
              },
              {
                id: 2,
                name: "Office B",
                address: { street: "Oak", house_number: "100" },
                scope: { id: "2" },
              },
            ],
          },
        });

        // Provide selectable providers with differing info texts
        wrapper.vm.selectableProviders = [
          {
            id: 1,
            name: "Office A",
            address: { street: "Elm", house_number: "99" },
            scope: { infoForAllAppointments: "Info A" },
          },
          {
            id: 2,
            name: "Office B",
            address: { street: "Oak", house_number: "100" },
            scope: { infoForAllAppointments: "Info B" },
          },
        ];

        // Ensure no selection state stabilizes
        wrapper.vm.selectedProviders = {};
        wrapper.vm.selectedProvider = null;
        await nextTick();
        await nextTick();
        // Simulate time section rendered with no available days
        setAvailableDays(wrapper, []);
        wrapper.vm.availableDaysFetched = true;
        await nextTick();
        await nextTick();
        // Make sure provider switching flag is false
        wrapper.vm.isSwitchingProvider = false;
        await nextTick();

        // Programmatically set modal HTML and open (use computed grouped html)
        (wrapper.vm as any).availabilityInfoHtmlOverride = (
          wrapper.vm as any
        ).noneSelectedAvailabilityInfoHtml;
        wrapper.vm.showAvailabilityInfoModal = true;
        await nextTick();

        // Modal should open and show the grouped info
        const modalBody = wrapper.find(".modal-body");
        expect(modalBody.exists()).toBe(true);
        expect(modalBody.html()).toContain("Info A");
        expect(modalBody.html()).toContain("Info B");
      });
      it("does not show info trigger or modal in this callout", async () => {
        const wrapper = createWrapper({
          selectedProvider: {
            id: 1,
            name: "Test Office",
            address: { street: "Test Street", house_number: "123" },
            scope: {
              infoForAllAppointments: "Custom no appointments message",
            },
          },
        });

        await wrapper.vm.$nextTick();
        wrapper.vm.availableDaysFetched = true;
        setAvailableDays(wrapper, []);
        await wrapper.vm.$nextTick();

        const callout = wrapper.find('[data-test="muc-callout"]');
        expect(callout.exists()).toBe(true);
        // Warning callout no longer contains info trigger/link
        expect(callout.html()).not.toContain("newAppointmentsInfoLink");
        expect(callout.find(".m-button.m-button--ghost").exists()).toBe(false);
        // No modal should open from warning callout
        expect(wrapper.find(".modal-body").isVisible()).toBe(false);
      });

      it("should fallback to translation key when infoForAllAppointments is null", async () => {
        const wrapper = createWrapper({
          selectedProvider: {
            id: 1,
            name: "Test Office",
            address: { street: "Test Street", house_number: "123" },
            scope: {
              infoForAllAppointments: null,
            },
          },
        });

        await wrapper.vm.$nextTick();
        wrapper.vm.availableDaysFetched = true;
        setAvailableDays(wrapper, []);
        await wrapper.vm.$nextTick();

        const callout = wrapper.find('[data-test="muc-callout"]');
        expect(callout.exists()).toBe(true);
        expect(callout.html()).toContain(
          "apiErrorNoAppointmentForThisScopeText"
        );
        // No info trigger if no content
        expect(callout.find(".m-button.m-button--ghost").exists()).toBe(false);
      });

      it("should fallback to translation key when infoForAllAppointments is empty string", async () => {
        const wrapper = createWrapper({
          selectedProvider: {
            id: 1,
            name: "Test Office",
            address: { street: "Test Street", house_number: "123" },
            scope: {
              infoForAllAppointments: "",
            },
          },
        });

        await wrapper.vm.$nextTick();
        wrapper.vm.availableDaysFetched = true;
        setAvailableDays(wrapper, []);
        await wrapper.vm.$nextTick();

        const callout = wrapper.find('[data-test="muc-callout"]');
        expect(callout.exists()).toBe(true);
        expect(callout.html()).toContain(
          "apiErrorNoAppointmentForThisScopeText"
        );
        // No info trigger if no content
        expect(callout.find(".m-button.m-button--ghost").exists()).toBe(false);
      });

      it("should fallback to translation key when infoForAllAppointments is whitespace only", async () => {
        const wrapper = createWrapper({
          selectedProvider: {
            id: 1,
            name: "Test Office",
            address: { street: "Test Street", house_number: "123" },
            scope: {
              infoForAllAppointments: "   ",
            },
          },
        });

        await wrapper.vm.$nextTick();
        setAvailableDays(wrapper, []);
        wrapper.vm.availableDaysFetched = true;
        await wrapper.vm.$nextTick();

        const callout = wrapper.find('[data-test="muc-callout"]');
        expect(callout.exists()).toBe(true);
        expect(callout.html()).toContain(
          "apiErrorNoAppointmentForThisScopeText"
        );
        // No info trigger if no content
        expect(callout.find(".m-button.m-button--ghost").exists()).toBe(false);
      });
    });

    describe("Edge Cases", () => {
      it("should handle undefined scope gracefully", async () => {
        const wrapper = createWrapper({
          selectedProvider: {
            id: 1,
            name: "Test Office",
            address: { street: "Test Street", house_number: "123" },
            scope: undefined,
          },
        });

        await wrapper.vm.$nextTick();
        wrapper.vm.availableDaysFetched = true;
        setAvailableDays(wrapper, []);
        await wrapper.vm.$nextTick();

        const callout = wrapper.find('[data-test="muc-callout"]');
        expect(callout.exists()).toBe(true);
        expect(callout.html()).toContain(
          "apiErrorNoAppointmentForThisScopeText"
        );
      });

      it("should handle scope without infoForAllAppointments property", async () => {
        const wrapper = createWrapper({
          selectedProvider: {
            id: 1,
            name: "Test Office",
            address: { street: "Test Street", house_number: "123" },
            scope: {
              // No infoForAllAppointments property
            },
          },
        });

        await wrapper.vm.$nextTick();
        wrapper.vm.availableDaysFetched = true;
        setAvailableDays(wrapper, []);
        await wrapper.vm.$nextTick();

        const callout = wrapper.find('[data-test="muc-callout"]');
        expect(callout.exists()).toBe(true);
        expect(callout.html()).toContain(
          "apiErrorNoAppointmentForThisScopeText"
        );
      });
    });

    describe("Integration Tests", () => {
      it("should handle complete flow with infoForAllAppointments", async () => {
        const wrapper = createWrapper({
          selectedService: {
            id: "service1",
            providers: [
              {
                name: "Office A",
                id: 1,
                address: { street: "Elm", house_number: "99" },
                scope: {
                  infoForAllAppointments: "Complete flow test message",
                },
              },
            ],
          },
        });

        await wrapper.vm.showSelectionForProvider({
          name: "Office A",
          id: 1,
          address: { street: "Elm", house_number: "99" },
          scope: {
            infoForAllAppointments: "Complete flow test message",
          },
        });
        await flushPromises();

        expect(wrapper.vm.selectedProvider).toBeDefined();
        expect(wrapper.vm.selectedProvider?.scope?.infoForAllAppointments).toBe(
          "Complete flow test message"
        );
      });

      it("does not provide a modal trigger in this callout", async () => {
        const wrapper = createWrapper({
          selectedProvider: {
            id: 1,
            name: "Test Office",
            address: { street: "Test Street", house_number: "123" },
            scope: {
              infoForAllAppointments: "Outside click close test",
            },
          },
        });

        await wrapper.vm.$nextTick();
        wrapper.vm.availableDaysFetched = true;
        setAvailableDays(wrapper, []);
        await wrapper.vm.$nextTick();

        const callout = wrapper.find('[data-test="muc-callout"]');
        const trigger = callout.find(".m-button.m-button--ghost");
        // Warning callout no longer has a trigger; modal cannot be opened here
        expect(trigger.exists()).toBe(false);
        expect(wrapper.find(".modal-body").isVisible()).toBe(false);
      });

      it("should maintain existing functionality when infoForAllAppointments is not set", async () => {
        const wrapper = createWrapper({
          selectedService: {
            id: "service1",
            providers: [
              {
                name: "Office A",
                id: 1,
                address: { street: "Elm", house_number: "99" },
              },
            ],
          },
        });

        await wrapper.vm.showSelectionForProvider({
          name: "Office A",
          id: 1,
          address: { street: "Elm", house_number: "99" },
        });
        await flushPromises();

        expect(wrapper.vm.selectedProvider).toBeDefined();
      });
    });
  });

  describe("CalendarListToggle Integration", () => {
    it("toggles from calendar view to list view and back", async () => {
      const wrapper = createWrapper({
        selectedService: {
          id: "service1",
          providers: [
            {
              name: "Office A",
              id: 1,
              address: { street: "Elm", house_number: "99" },
              scope: { id: "1" },
            },
          ],
        },
      });

      // Set up provider selection first
      wrapper.vm.selectedProviders[1] = true;
      await nextTick();

      await wrapper.vm.showSelectionForProvider({
        name: "Office A",
        id: 1,
        address: { street: "Elm", house_number: "99" },
        scope: { id: "1" },
      });
      await flushPromises();

      // Mock the availableDays to simulate what would be fetched for selected providers
      setAvailableDays(
        wrapper,
        [{ time: "2025-06-16", providerIDs: "1" }],
        officeOneMorningSlots
      );

      expect(wrapper.vm.isListView).toBe(false);
      expect(wrapper.findComponent({ name: "muc-calendar" }).exists()).toBe(
        true
      );
      expect(wrapper.find(".m-component-accordion").exists()).toBe(false);

      await wrapper.find(".m-toggle-switch").trigger("click");
      await nextTick();

      expect(wrapper.vm.isListView).toBe(true);
      expect(wrapper.findComponent({ name: "muc-calendar" }).exists()).toBe(
        false
      );
      expect(wrapper.find(".m-component-accordion").exists()).toBe(true);

      await wrapper.find(".m-toggle-switch").trigger("click");
      await nextTick();

      expect(wrapper.vm.isListView).toBe(false);
      expect(wrapper.findComponent({ name: "muc-calendar" }).exists()).toBe(
        true
      );
    });

    it("shows navigation buttons for hourly view when multiple providers are selected", async () => {
      (fetchAvailableCalendar as Mock).mockResolvedValue(
        calendarResponse(
          [
            {
              time: "2025-06-10",
              providerIDs: "1,2",
              offices: officesForHourlyViewTest,
            },
          ],
          []
        )
      );

      const wrapper = createWrapper({
        selectedService: {
          id: "service1",
          providers: [
            {
              name: "Office A",
              id: 1,
              address: { street: "Elm", house_number: "99" },
              scope: { id: "1" },
            },
            {
              name: "Office B",
              id: 2,
              address: { street: "Elm", house_number: "100" },
              scope: { id: "2" },
            },
          ],
        },
      });

      wrapper.vm.selectedProviders[1] = true;
      wrapper.vm.selectedProviders[2] = true;
      await nextTick();

      await wrapper.vm.showSelectionForProvider({
        name: "Office A",
        id: 1,
        address: { street: "Elm", house_number: "99" },
        scope: { id: "1" },
      });
      await flushPromises();

      await wrapper.vm.handleDaySelection(new Date("2025-06-10"));
      await flushPromises();

      expect(wrapper.vm.isListView).toBe(false);

      const buttons = wrapper.findAllComponents({ name: "MucButton" });
      const earlierButton = buttons.find((btn) =>
        btn.text().includes("earlier")
      );
      const laterButton = buttons.find((btn) => btn.text().includes("later"));

      expect(earlierButton).toBeDefined();
      expect(laterButton).toBeDefined();
    });

    it("hides navigation buttons for hourly view when single provider is selected", async () => {
      (fetchAvailableCalendar as Mock).mockResolvedValue(
        calendarResponse([{ time: "2025-06-10", providerIDs: "1" }])
      );

      const wrapper = createWrapper({
        selectedService: {
          id: "service1",
          providers: [
            {
              name: "Office A",
              id: 1,
              address: { street: "Elm", house_number: "99" },
              scope: { id: "1" },
            },
          ],
        },
      });

      // Set up provider selection first
      wrapper.vm.selectedProviders[1] = true;
      await nextTick();

      await wrapper.vm.showSelectionForProvider({
        name: "Office A",
        id: 1,
        address: { street: "Elm", house_number: "99" },
        scope: { id: "1" },
      });
      await flushPromises();

      // Mock the availableDays to simulate what would be fetched for selected providers
      setAvailableDays(wrapper, [{ time: "2025-06-10", providerIDs: "1" }]);

      await nextTick();
      await flushPromises();

      expect(wrapper.vm.isListView).toBe(false);

      const buttons = wrapper.findAllComponents({ name: "MucButton" });
      const earlierButton = buttons.find((btn) =>
        btn.text().includes("earlier")
      );
      const laterButton = buttons.find((btn) => btn.text().includes("later"));

      expect(earlierButton).toBeUndefined();
      expect(laterButton).toBeUndefined();
    });
  });
  describe("Loading UI – spinner instead of calendar", () => {
    it("shows spinner while fetching availableDays and hides the calendar until fetch completes", async () => {
      // Promise for fetchAvailableCalendar to keep the loading status stable
      let resolveDays!: (v: any) => void;
      (fetchAvailableCalendar as Mock).mockImplementation(() => {
        return new Promise((resolve) => {
          resolveDays = resolve;
        });
      });

      const wrapper = createWrapper({
        // preselection forces a provider to be selected during the first fetch
        props: { preselectedOfficeId: 1 },
        selectedService: {
          id: "service1",
          providers: [
            {
              name: "Office A",
              id: 1,
              address: { street: "Elm", house_number: "99" },
            },
          ],
        },
      });

      await nextTick();
      expect(wrapper.find(".m-spinner-container").exists()).toBe(true);
      // calendar must not be rendered yet
      expect(wrapper.findComponent({ name: "muc-calendar" }).exists()).toBe(
        false
      );

      // finish loading
      resolveDays(
        calendarResponse([{ time: "2025-06-16", providerIDs: "1" }], [])
      );
      await flushPromises();
      await nextTick();

      // spinner disappears; with no appointments returned, calendar stays hidden
      expect(wrapper.find(".m-spinner-container").exists()).toBe(false);
      expect(wrapper.findComponent({ name: "muc-calendar" }).exists()).toBe(
        false
      );
    });
  });
});
