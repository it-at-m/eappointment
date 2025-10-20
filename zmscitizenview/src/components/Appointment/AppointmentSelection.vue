<template>
  <ProviderSelection
    :t="t"
    :selectableProviders="selectableProviders"
    :providersWithAppointments="providersWithAppointments"
    :selectedProvider="selectedProvider"
    :selectedProviders="selectedProviders"
    @update:selectedProviders="onUpdateSelectedProviders"
    :providerSelectionError="providerSelectionError"
  />
  <div
    v-if="
      availableDaysFetched &&
      (noProviderSelected ||
        (selectedProvider && !providersWithAppointments.length)) &&
      !isSwitchingProvider
    "
    class="m-component"
  >
    <h2>{{ t("time") }}</h2>
    <muc-callout type="info">
      <template #header>
        <h3>{{ t("apiErrorNoAppointmentForThisScopeHeader") }}</h3>
      </template>
      <template #content>
        <div class="m-content">
          {{ t("apiErrorNoAppointmentForThisScopeText") }}
        </div>
        <div
          class="m-content"
          style="margin-top: 8px"
          v-if="noneSelectedAvailabilityInfoHtml"
        >
          <muc-button
            variant="ghost"
            icon="information"
            icon-shown-left
            class="no-bottom-margin"
            @click="openNoneSelectedInfoModal"
          >
            <template #default>{{ t("newAppointmentsInfoLink") }}</template>
          </muc-button>
        </div>
      </template>
    </muc-callout>
  </div>
  <div v-else-if="!error && hasSelectedProviderWithAppointments">
    <CalendarListToggle
      :t="t"
      :isListView="isListView"
      @update:isListView="isListView = $event"
    />
    <CalendarView
      ref="calendarViewRef"
      v-if="!isListView"
      :t="t"
      :selectedDay="selectedDay"
      :calendarKey="calendarKey"
      :allowedDates="allowedDates"
      :minDate="minDate"
      :maxDate="maxDate"
      :viewMonth="viewMonth"
      :timeSlotsInHoursByOffice="timeSlotsInHoursByOffice"
      :timeSlotsInDayPartByOffice="timeSlotsInDayPartByOffice"
      :currentHour="currentHour"
      :firstHour="firstHour"
      :lastHour="lastHour"
      :currentDayPart="currentDayPart"
      :firstDayPart="firstDayPart"
      :lastDayPart="lastDayPart"
      :selectableProviders="selectableProviders"
      :selectedProviders="selectedProviders"
      :providersWithAppointments="providersWithAppointments"
      :appointmentsCount="appointmentsCount"
      :isLoadingAppointments="isLoadingAppointments"
      :isLoadingComplete="isLoadingComplete"
      :availabilityInfoHtml="availabilityInfoHtml"
      :officeNameById="officeNameById"
      :isSlotSelected="isSlotSelected"
      @update:selectedDay="handleDaySelection"
      @selectTimeSlot="
        ({ officeId, time }) =>
          handleTimeSlotSelection(officeId as number, time)
      "
      @setSelectedHour="(h) => (selectedHour = h as number | null)"
      @setSelectedDayPart="(p) => (selectedDayPart = p as any)"
      @openInfo="openAvailabilityInfoModal"
    />

    <ListView
      ref="listViewRef"
      v-if="isListView"
      :t="t"
      :isLoadingAppointments="isLoadingAppointments"
      :availabilityInfoHtml="availabilityInfoHtml"
      :selectableProviders="selectableProviders"
      :selectedProviders="selectedProviders"
      :providersWithAppointments="providersWithAppointments"
      :officeNameById="officeNameById"
      :isSlotSelected="isSlotSelected"
      :availableDays="availableDays"
      :datesWithoutAppointments="datesWithoutAppointments"
      :appointmentTimestampsByOffice="appointmentTimestampsByOffice"
      :officeOrder="officeOrder"
      @update:selectedDay="handleDaySelection"
      @selectTimeSlot="
        ({ officeId, time }) =>
          handleTimeSlotSelection(officeId as number, time)
      "
      @openInfo="openAvailabilityInfoModal"
    />
    <div ref="summary">
      <AppointmentPreview
        :t="t"
        :selectedProvider="selectedProvider"
        :selectedDay="selectedDay"
        :selectedTimeslot="selectedTimeslot"
        :selectedService="selectedService"
      />
    </div>
  </div>
  <div
    v-if="!noProviderSelected && showError && !isSwitchingProvider"
    class="m-component"
  >
    <h2>{{ t("time") }}</h2>
    <muc-callout :type="toCalloutType(apiErrorTranslation.errorType)">
      <template #header>
        <h3>{{ t(apiErrorTranslation.headerKey) }}</h3>
      </template>
      <template #content>
        <div class="m-content">{{ t(apiErrorTranslation.textKey) }}</div>
        <div
          class="m-content"
          style="margin-top: 8px"
          v-if="
            (apiErrorTranslation.textKey ===
              'apiErrorNoAppointmentForThisScopeText' ||
              apiErrorTranslation.textKey ===
                'apiErrorNoAppointmentForThisDayText') &&
            availabilityInfoHtml
          "
        >
          <muc-button
            variant="ghost"
            icon="information"
            icon-shown-left
            class="no-bottom-margin"
            @click="openAvailabilityInfoModal"
          >
            <template #default>{{ t("newAppointmentsInfoLink") }}</template>
          </muc-button>
        </div>
      </template>
    </muc-callout>
  </div>
  <div class="m-button-group">
    <muc-button
      v-if="!isRebooking"
      icon="arrow-left"
      icon-shown-left
      variant="secondary"
      @click="previousStep"
    >
      <template #default>{{ t("back") }}</template>
    </muc-button>
    <muc-button
      :disabled="
        selectedTimeslot === 0 ||
        !selectedDay ||
        loadingStates.isReservingAppointment.value
      "
      :icon="'arrow-right'"
      @click="nextStep"
    >
      <template #default>
        <span>{{ t("next") }}</span>
      </template>
    </muc-button>
  </div>
  <AvailabilityInfoModal
    :show="showAvailabilityInfoModal"
    :html="availabilityInfoHtmlForModal"
    :closeAriaLabel="t('closeDialog')"
    @close="closeAvailabilityInfoModal"
  />
</template>
<script setup lang="ts">
import type { CalloutType } from "@/utils/callout";
import type { ApiErrorTranslation } from "@/utils/errorHandler";
import type { Ref } from "vue";

import { MucButton, MucCallout } from "@muenchen/muc-patternlab-vue";
import {
  computed,
  inject,
  nextTick,
  onMounted,
  onUnmounted,
  ref,
  watch,
} from "vue";

import { AvailableDaysDTO } from "@/api/models/AvailableDaysDTO";
import { AvailableTimeSlotsByOfficeDTO } from "@/api/models/AvailableTimeSlotsByOfficeDTO";
import { OfficeAvailableTimeSlotsDTO } from "@/api/models/OfficeAvailableTimeSlotsDTO";
import {
  fetchAvailableDays,
  fetchAvailableTimeSlots,
} from "@/api/ZMSAppointmentAPI";
import { OfficeImpl } from "@/types/OfficeImpl";
import {
  SelectedServiceProvider,
  SelectedTimeslotProvider,
} from "@/types/ProvideInjectTypes";
import { toCalloutType } from "@/utils/callout";
import {
  createErrorStates,
  getApiErrorTranslation,
  handleApiResponse,
} from "@/utils/errorHandler";
import {
  berlinHourFormatter,
  convertDateToString,
} from "@/utils/formatAppointmentDateTime";
import { generateAvailabilityInfoHtml } from "@/utils/infoForAllAppointments";
import { sanitizeHtml } from "@/utils/sanitizeHtml";
import AppointmentPreview from "./AppointmentSelection/AppointmentPreview.vue";
import AvailabilityInfoModal from "./AppointmentSelection/AvailabilityInfoModal.vue";
import CalendarListToggle from "./AppointmentSelection/CalendarListToggle.vue";
import CalendarView from "./AppointmentSelection/CalendarView.vue";
import ListView from "./AppointmentSelection/ListView.vue";
import ProviderSelection from "./AppointmentSelection/ProviderSelection.vue";

const props = defineProps<{
  baseUrl: string | undefined;
  isRebooking: boolean;
  exclusiveLocation: string | undefined;
  preselectedOfficeId: string | undefined;
  selectedServiceMap: Map<string, number>;
  captchaToken: string | null;
  bookingError: boolean;
  bookingErrorKey: string;
  errorType?: CalloutType;
  t: (key: string) => string;
}>();

const emit = defineEmits<(e: "next" | "back") => void>();

const { selectedService } = inject<SelectedServiceProvider>(
  "selectedServiceProvider"
) as SelectedServiceProvider;

const { selectedProvider, selectedTimeslot } = inject<SelectedTimeslotProvider>(
  "selectedTimeslot"
) as SelectedTimeslotProvider;

const loadingStates = inject("loadingStates", {
  isReservingAppointment: ref(false),
  isUpdatingAppointment: ref(false),
  isBookingAppointment: ref(false),
  isCancelingAppointment: ref(false),
}) as {
  isReservingAppointment: Ref<boolean>;
  isUpdatingAppointment: Ref<boolean>;
  isBookingAppointment: Ref<boolean>;
  isCancelingAppointment: Ref<boolean>;
};

const selectableProviders = ref<OfficeImpl[]>();
const availableDays = ref<Array<{ time: string; providerIDs: string }>>();
const selectedHour = ref<number | null>(null);
const selectedDayPart = ref<"am" | "pm" | null>(null);

const appointmentsCount = ref<number>(0);

const appointmentTimestampsByOffice = ref<OfficeAvailableTimeSlotsDTO[]>([]);
const appointmentTimestamps = ref<number[]>([]);

const errorStates = createErrorStates();
const errorStateMap = computed(() => errorStates.errorStateMap);
const currentErrorData = computed(() => errorStates.currentErrorData);

const error = ref<boolean>(false);
const showError = computed(() => error.value || props.bookingError);

const selectedDay = ref<Date>();
const minDate = ref<Date>();
const maxDate = ref<Date>();
const viewMonth = ref<Date>(new Date());
const officeOrder = ref<Map<number, number>>(new Map());
const calendarKey = ref(0);

const selectedProviders = ref<{ [id: string]: boolean }>({});
const listViewRef = ref<any>();
const calendarViewRef = ref<any>();

let initialized = false;
const availableDaysFetched = ref(false);
const isLoadingAppointments = ref(false);
const isSwitchingProvider = ref(false);

const datesWithoutAppointments = ref(new Set<string>());

const isLoadingComplete = ref(false);

let refetchTimer: ReturnType<typeof setTimeout> | undefined;

/**
 * Reference to the appointment summary.
 * After selecting a time slot, the focus is placed on the appointment summary.
 */
const summary = ref<HTMLElement | null>(null);

const getOfficeById = (id: number | string): OfficeImpl | undefined => {
  const idStr = String(id);
  return (selectableProviders.value || []).find((p) => String(p.id) === idStr);
};

const officeNameById = (id: number | string): string | null => {
  return getOfficeById(id)?.name ?? null;
};

const timeSlotsInHoursByOffice = computed(() => {
  const offices = new Map<
    number,
    { officeId: number; appointments: Map<number, number[]> }
  >();

  appointmentTimestampsByOffice.value.forEach((office) => {
    if (!selectedProviders.value[office.officeId]) return;

    const timesByHours = new Map<number, number[]>();

    office.appointments?.forEach?.((time) => {
      const berlinDate = new Date(time * 1000);
      const hour = parseInt(berlinHourFormatter.format(berlinDate));

      if (!timesByHours.has(hour)) {
        timesByHours.set(hour, []);
      }
      timesByHours.get(hour)?.push(time);
    });

    if (timesByHours.size > 0) {
      offices.set(office.officeId, {
        officeId: office.officeId,
        appointments: timesByHours,
      });
    }
  });

  return new Map(
    [...offices.entries()].sort((a, b) => {
      const indexA = officeOrder.value.get(a[0]) ?? Infinity;
      const indexB = officeOrder.value.get(b[0]) ?? Infinity;
      return indexA - indexB;
    })
  );
});

const firstHour = computed(() => {
  let min = Infinity;

  for (const [, office] of timeSlotsInHoursByOffice.value) {
    for (const hour of office.appointments.keys()) {
      min = Math.min(min, hour);
    }
  }

  return min === Infinity ? null : min;
});

const lastHour = computed(() => {
  let max = -Infinity;

  for (const [, office] of timeSlotsInHoursByOffice.value) {
    for (const hour of office.appointments.keys()) {
      max = Math.max(max, hour);
    }
  }

  return max === -Infinity ? null : max;
});

const currentHour = computed(() => {
  return selectedHour.value !== null ? selectedHour.value : firstHour.value;
});

const timeSlotsInDayPartByOffice = computed(() => {
  const offices = new Map<
    number,
    { officeId: number; appointments: Map<string, number[]> }
  >();

  appointmentTimestampsByOffice.value.forEach((office) => {
    if (!selectedProviders.value[office.officeId]) return;

    const timesByPartOfDay = new Map<string, number[]>();

    office.appointments?.forEach?.((time) => {
      const berlinDate = new Date(time * 1000);
      const hour = parseInt(berlinHourFormatter.format(berlinDate));
      const dayPart = hour >= 12 ? "pm" : "am";

      if (!timesByPartOfDay.has(dayPart)) {
        timesByPartOfDay.set(dayPart, []);
      }
      timesByPartOfDay.get(dayPart)?.push(time);
    });

    if (timesByPartOfDay.size > 0) {
      offices.set(office.officeId, {
        officeId: office.officeId,
        appointments: timesByPartOfDay,
      });
    }
  });

  return new Map(
    [...offices.entries()].sort((a, b) => {
      const indexA = officeOrder.value.get(a[0]) ?? Infinity;
      const indexB = officeOrder.value.get(b[0]) ?? Infinity;
      return indexA - indexB;
    })
  );
});

const firstDayPart = computed(() => {
  for (const [, office] of timeSlotsInDayPartByOffice.value) {
    if (office.appointments.has("am")) return "am";
  }

  return "pm";
});

const lastDayPart = computed(() => {
  for (const [, office] of timeSlotsInDayPartByOffice.value) {
    if (office.appointments.has("pm")) return "pm";
  }

  return "am";
});

const currentDayPart = computed(() => {
  return selectedDayPart.value !== null
    ? selectedDayPart.value
    : firstDayPart.value;
});

const showSelectionForProvider = async (provider: OfficeImpl) => {
  isSwitchingProvider.value = true;
  selectedProvider.value = provider;
  error.value = false;
  selectedDay.value = undefined;
  selectedTimeslot.value = 0;
  await refetchAvailableDaysForSelection();
};

const TODAY = new Date();
const MAXDATE = new Date(
  TODAY.getFullYear(),
  TODAY.getMonth() + 6,
  TODAY.getDate()
);

const allowedDates = (date: Date) => {
  const beforeMaxDate =
    date.getFullYear() < MAXDATE.getFullYear() ||
    (date.getFullYear() === MAXDATE.getFullYear() &&
      date.getMonth() < MAXDATE.getMonth()) ||
    (date.getFullYear() === MAXDATE.getFullYear() &&
      date.getMonth() === MAXDATE.getMonth() &&
      date.getDate() < MAXDATE.getDate());

  if (!beforeMaxDate) return false;

  const dateString = convertDateToString(date);

  // Check if this date is known to have no appointments
  if (datesWithoutAppointments.value.has(dateString)) {
    return false;
  }

  const dayEntry = availableDays.value?.find(
    (day) => convertDateToString(new Date(day.time)) === dateString
  );

  if (!dayEntry) return false;

  // Check if the date has appointments for the selected providers
  const hasAppointments = dayEntry.providerIDs
    .split(",")
    .some((id) => selectedProviders.value[id]);

  if (!hasAppointments) return false;

  return true;
};

const hasAppointmentsForSelectedProviders = () => {
  return (
    availableDays?.value?.some((day) =>
      day.providerIDs.split(",").some((id) => selectedProviders.value[id])
    ) || false
  );
};

const providersWithAppointments = computed(() => {
  // Always return all selectable providers to maintain UI state
  // The filtering for calendar display happens in updateDateRangeForSelectedProviders
  return (selectableProviders.value || []).sort((a, b) => {
    const aPriority = a.priority ?? -Infinity;
    const bPriority = b.priority ?? -Infinity;
    return bPriority - aPriority;
  });
});

const hasSelectedProviderWithAppointments = computed(() => {
  if (!availableDays?.value || availableDays.value.length === 0) {
    return false;
  }

  return Object.entries(selectedProviders.value).some(
    ([id, isSelected]) =>
      isSelected &&
      providersWithAppointments.value.some((p) => p.id.toString() === id)
  );
});

const handleTimeSlotSelection = async (officeId: number, timeSlot: number) => {
  selectedTimeslot.value = timeSlot;
  selectedProvider.value = getOfficeById(officeId);
  if (summary.value) {
    await nextTick();
    summary.value.focus();
    summary.value.scrollIntoView({ behavior: "smooth", block: "center" });
  }
};

const handleDaySelection = async (day: any) => {
  if (!(day instanceof Date)) {
    // Don't allow deselection - if day is not a Date, ignore the selection
    return;
  }

  // If the same date is already selected, don't do anything
  if (selectedDay.value && selectedDay.value.getTime() === day.getTime()) {
    return;
  }

  selectedDay.value = day;
  selectedTimeslot.value = 0;
  selectedHour.value = null;
  selectedDayPart.value = null;

  // Reset to earliest available appointment
  if (timeSlotsInHoursByOffice.value.size > 0) {
    // For hourly view
    const allHours = Array.from(
      timeSlotsInHoursByOffice.value.values()
    ).flatMap((office) => {
      const hours = Array.from((office as any).appointments.keys());
      return hours.filter((hour) => typeof hour === "number" && hour >= 0);
    });
    if (allHours.length > 0) {
      selectedHour.value = Math.min(...(allHours as number[]));
    }
  } else if (timeSlotsInDayPartByOffice.value.size > 0) {
    // For am/pm view
    const allDayParts = Array.from(
      timeSlotsInDayPartByOffice.value.values()
    ).flatMap((office) => {
      const dayParts = Array.from((office as any).appointments.keys());
      return dayParts.filter((part) => part === "am" || part === "pm");
    });
    if (allDayParts.includes("am")) {
      selectedDayPart.value = "am";
    } else if (allDayParts.includes("pm")) {
      selectedDayPart.value = "pm";
    }
  }
};

const providerSelectionError = computed(() => {
  // Check if any providers are selected
  const selectedProviderIds = Object.keys(selectedProviders.value).filter(
    (id) => selectedProviders.value[id]
  );

  // If no providers are selected at all, show error
  if (
    selectedProviderIds.length === 0 &&
    providersWithAppointments.value.length > 0
  ) {
    return props.t("errorMessageProviderSelection");
  }

  // If available days is empty (no data fetched), don't show error yet
  if (!availableDays?.value || availableDays.value.length === 0) {
    return "";
  }

  const hasSelection = Object.entries(selectedProviders.value).some(
    ([id, isSelected]) =>
      isSelected &&
      providersWithAppointments.value.some((p) => p.id.toString() === id)
  );

  return hasSelection ? "" : props.t("errorMessageProviderSelection");
});

const noProviderSelected = computed(() => {
  // Check if any providers are selected
  const selectedProviderIds = Object.keys(selectedProviders.value).filter(
    (id) => selectedProviders.value[id]
  );

  return selectedProviderIds.length === 0;
});

// Threshold moved to constants

const isListView = ref(false);

// Modal state and handlers
const showAvailabilityInfoModal = ref(false);
const availabilityInfoHtmlOverride = ref("");
const openAvailabilityInfoModal = () => {
  availabilityInfoHtmlOverride.value = "";
  showAvailabilityInfoModal.value = true;
};
const closeAvailabilityInfoModal = () => {
  showAvailabilityInfoModal.value = false;
  availabilityInfoHtmlOverride.value = "";
};

const availabilityInfoHtml = computed(() => {
  return generateAvailabilityInfoHtml(
    selectedProviders.value,
    selectableProviders.value,
    selectedProvider.value,
    sanitizeHtml
  );
});

const noneSelectedAvailabilityInfoHtml = computed(() => {
  if (!noProviderSelected.value) return "";
  const providers = selectableProviders.value || [];
  if (providers.length === 0) return "";

  // Build a synthetic selection that includes all selectable providers
  const allSelectedMap: Record<string, boolean> = {};
  providers.forEach((p: any) => {
    if (p?.id != null) allSelectedMap[String(p.id)] = true;
  });

  return generateAvailabilityInfoHtml(
    allSelectedMap,
    selectableProviders.value,
    undefined,
    sanitizeHtml
  );
});

const availabilityInfoHtmlForModal = computed(() => {
  return availabilityInfoHtmlOverride.value || availabilityInfoHtml.value;
});

const openNoneSelectedInfoModal = () => {
  const html = noneSelectedAvailabilityInfoHtml.value;
  if (html) {
    availabilityInfoHtmlOverride.value = html;
    showAvailabilityInfoModal.value = true;
  }
};

const onUpdateSelectedProviders = (val: { [id: string]: boolean }) => {
  // Avoid unnecessary triggers when value is identical
  const current = JSON.stringify(selectedProviders.value);
  const next = JSON.stringify(val);
  if (current !== next) {
    selectedProviders.value = { ...val };
  }
};

const isSlotSelected = (officeId: number | string, time: number) =>
  selectedTimeslot.value === time &&
  selectedProvider.value?.id?.toString() === officeId.toString();

const nextStep = () => emit("next");
const previousStep = () => emit("back");

// API error handling
const apiErrorTranslation = computed<ApiErrorTranslation>(() => {
  // If we have a booking error from props, use that instead of our own error states
  if (props.bookingError && props.bookingErrorKey) {
    return {
      headerKey: `${props.bookingErrorKey}Header`,
      textKey: `${props.bookingErrorKey}Text`,
      errorType: props.errorType || "error", // Use prop if provided, otherwise default to "error"
    };
  }
  // If we're switching providers, don't show any error messages
  if (isSwitchingProvider.value) {
    return {
      headerKey: "",
      textKey: "",
      errorType: "error", // Default
    };
  }
  // Otherwise, use our own error states
  return getApiErrorTranslation(errorStateMap.value, currentErrorData.value);
});

const handleError = (data: any): void => {
  error.value = true;
  handleApiResponse(data, errorStateMap.value, currentErrorData.value);
};

// API calls
const refetchAvailableDaysForSelection = async (): Promise<void> => {
  // Only fetch available days for currently selected providers
  const selectedProviderIds = Object.keys(selectedProviders.value).filter(
    (id) => selectedProviders.value[id]
  );

  if (selectedProviderIds.length === 0) {
    // No providers selected, clear available days but keep providers visible
    availableDays.value = [];
    availableDaysFetched.value = true;
    error.value = false;
    isSwitchingProvider.value = false;
    updateDateRangeForSelectedProviders();
    return;
  }

  const providerIdsToQuery = selectedProviderIds.map(Number);

  const data = await fetchAvailableDays(
    providerIdsToQuery,
    Array.from(props.selectedServiceMap.keys()),
    Array.from(props.selectedServiceMap.values()),
    props.baseUrl ?? undefined,
    props.captchaToken ?? undefined
  );

  const days = (data as AvailableDaysDTO)?.availableDays;
  if (
    Array.isArray(days) &&
    days.length > 0 &&
    days.every(
      (d) =>
        typeof d === "object" && d !== null && "time" in d && "providerIDs" in d
    )
  ) {
    datesWithoutAppointments.value.clear();
    availableDays.value = days as { time: string; providerIDs: string }[];
    // Only set selectedDay on first load; otherwise preserve current selection
    if (!selectedDay.value) {
      selectedDay.value = new Date((days[0] as any).time);
    }
    // Keep viewMonth in sync with selectedDay and force calendar to re-render
    viewMonth.value = new Date(
      selectedDay.value.getFullYear(),
      selectedDay.value.getMonth(),
      1
    );
    calendarKey.value++;
    availableDaysFetched.value = true;
    error.value = false;
    isSwitchingProvider.value = false;

    // Update date range based on selected providers
    updateDateRangeForSelectedProviders();
  } else {
    handleError(data);
    isSwitchingProvider.value = false;
  }
};

const getAppointmentsOfDay = async (date: string): Promise<void> => {
  isLoadingAppointments.value = true;
  appointmentTimestamps.value = [];
  appointmentTimestampsByOffice.value = [];

  // Only fetch appointments for selected providers
  const selectedProviderIds = Object.keys(selectedProviders.value).filter(
    (id) => selectedProviders.value[id]
  );
  if (selectedProviderIds.length === 0) {
    // No providers selected, clear appointments
    isLoadingAppointments.value = false;
    return;
  }

  const providerIds = selectedProviderIds.map(Number);

  try {
    const data = await fetchAvailableTimeSlots(
      date,
      providerIds.map(Number),
      Array.from(props.selectedServiceMap.keys()),
      Array.from(props.selectedServiceMap.values()),
      props.baseUrl ?? undefined,
      props.captchaToken ?? undefined
    );

    if (data && "offices" in data && Array.isArray((data as any).offices)) {
      appointmentTimestampsByOffice.value = (
        data as AvailableTimeSlotsByOfficeDTO
      ).offices;

      appointmentsCount.value = (data as any).offices.reduce(
        (sum: number, office: any) => sum + (office.appointments?.length ?? 0),
        0
      );

      // Track dates without appointments
      if (appointmentsCount.value === 0) {
        datesWithoutAppointments.value.add(date);
      } else {
        datesWithoutAppointments.value.delete(date);
      }

      // Only show error if there are no appointments on any day
      if (
        appointmentsCount.value === 0 &&
        !hasAppointmentsForSelectedProviders()
      ) {
        error.value = true;
      } else {
        error.value = false;

        // Keep selectedDay; provider-change pipeline decides nearest available date
      }
    } else {
      // Track dates without appointments
      datesWithoutAppointments.value.add(date);

      // Only show error if there are no appointments on any day
      if (!hasAppointmentsForSelectedProviders()) {
        error.value = true;
      } else {
        error.value = false;
        // Keep selectedDay; provider-change pipeline decides nearest available date
      }
    }
  } catch (error) {
    // Handle any errors from fetchAvailableTimeSlots
  } finally {
    isLoadingAppointments.value = false;
    isLoadingComplete.value = true;
  }
};

function getAvailableProviders(
  providers: OfficeImpl[],
  selectedIds: number[]
): OfficeImpl[] {
  return Object.values(
    providers.reduce<Record<string, OfficeImpl[]>>((grouped, provider) => {
      (grouped[provider.name] ||= []).push(provider);
      return grouped;
    }, {})
  ).map((group) => {
    if (group.length === 1) return group[0];

    // clean = passport provider
    // restricted = default provider (hidden by passport related services)
    const clean = group.find((p) => (p.disabledByServices ?? []).length === 0);
    const restricted = group.find(
      (p) => (p.disabledByServices ?? []).length > 0
    );

    // Fallbacks if one type is missing
    if (!clean && !restricted) return group[0];
    if (!restricted) return clean as OfficeImpl;
    if (!clean) return restricted as OfficeImpl;

    const restrictedDisabled = (restricted.disabledByServices ?? []).map(
      Number
    );
    const allDisabled = selectedIds.every((id) =>
      restrictedDisabled.includes(id)
    );

    return allDisabled ? (clean as OfficeImpl) : (restricted as OfficeImpl);
  });
}

function updateDateRangeForSelectedProviders() {
  if (!availableDays.value) return [];
  const selectedProviderIds = Object.entries(selectedProviders.value)
    .filter(([_, isSelected]) => isSelected)
    .map(([id]) => Number(id));

  const availableDaysForSelectedProviders = (availableDays.value || []).filter(
    (day) =>
      day.providerIDs
        .split(",")
        .some((providerId) => selectedProviderIds.includes(Number(providerId)))
  );

  if (availableDaysForSelectedProviders.length > 0) {
    minDate.value = new Date(availableDaysForSelectedProviders[0].time);
    maxDate.value = new Date(
      availableDaysForSelectedProviders[
        availableDaysForSelectedProviders.length - 1
      ].time
    );
    // Ensure calendar updates min/max immediately
    if (selectedDay.value) {
      viewMonth.value = new Date(
        selectedDay.value.getFullYear(),
        selectedDay.value.getMonth(),
        1
      );
    }
    calendarKey.value++;

    // Validate that the currently selected date is still available for selected providers
    validateAndUpdateSelectedDate(availableDaysForSelectedProviders);
  }
  return availableDaysForSelectedProviders;
}

async function validateAndUpdateSelectedDate(
  availableDaysForSelectedProviders: any[]
) {
  if (!selectedDay.value) return;
  const currentDate = convertDateToString(selectedDay.value);
  const isCurrentDateAvailable = availableDaysForSelectedProviders.some(
    (day: any) => convertDateToString(new Date(day.time)) === currentDate
  );

  if (!isCurrentDateAvailable) {
    // First try to find a date after the current date
    let nextAvailableDay = availableDaysForSelectedProviders.find(
      (day: any) => {
        const dayDate = new Date(day.time);
        return dayDate >= (selectedDay.value ?? new Date());
      }
    );

    // If no future date is available, find the closest date before the current date
    if (!nextAvailableDay) {
      nextAvailableDay = [...availableDaysForSelectedProviders]
        .reverse()
        .find((day: any) => {
          const dayDate = new Date(day.time);
          return dayDate <= (selectedDay.value ?? new Date());
        });
    }

    if (nextAvailableDay) {
      const newDate = new Date(nextAvailableDay.time);
      selectedDay.value = newDate;
      // Set viewMonth to the first day of the month containing the new date
      viewMonth.value = new Date(newDate.getFullYear(), newDate.getMonth(), 1);
      calendarKey.value++;
      await nextTick();
      await getAppointmentsOfDay(nextAvailableDay.time);
    }
  }
}

async function validateCurrentDateHasAppointments() {
  if (!selectedDay.value) return;
  const currentDate = convertDateToString(selectedDay.value);
  const dayEntry = availableDays.value?.find(
    (day) => convertDateToString(new Date(day.time)) === currentDate
  );
  const hasAppointments = dayEntry?.providerIDs
    .split(",")
    .some((providerId) => selectedProviders.value[providerId]);

  if (
    !hasAppointments &&
    availableDays.value &&
    availableDays.value.length > 0
  ) {
    const nextAvailableDay = availableDays.value.find((day) => {
      const dayDate = new Date(day.time);
      return (
        dayDate >= (selectedDay.value ?? new Date()) &&
        day.providerIDs
          .split(",")
          .some((providerId) => selectedProviders.value[providerId])
      );
    });

    if (nextAvailableDay) {
      selectedDay.value = new Date(nextAvailableDay.time);
      await nextTick();
      await getAppointmentsOfDay(nextAvailableDay.time);
    }
  }
}

async function snapToNearestForCurrentView() {
  if (isListView.value) {
    await listViewRef.value?.snapToNearest?.();
  } else {
    await calendarViewRef.value?.snapToNearest?.();
  }
}

function scheduleRefreshAfterProviderChange() {
  const selectionSnapshot = JSON.stringify(selectedProviders.value);
  if (refetchTimer) clearTimeout(refetchTimer);
  refetchTimer = setTimeout(async () => {
    const prevSelectedDay = selectedDay.value
      ? new Date(selectedDay.value)
      : undefined;
    await refetchAvailableDaysForSelection();
    // Selection changed while awaiting? Abort this cycle.
    if (selectionSnapshot !== JSON.stringify(selectedProviders.value)) {
      return;
    }
    const availableDaysForSelectedProviders =
      updateDateRangeForSelectedProviders();

    // Validate and update selected date to ensure it's available for selected providers
    if (availableDaysForSelectedProviders.length > 0) {
      await validateAndUpdateSelectedDate(availableDaysForSelectedProviders);
    }

    const previousSelectedDateString = prevSelectedDay
      ? convertDateToString(prevSelectedDay)
      : null;
    const currentSelectedDateString = selectedDay.value
      ? convertDateToString(selectedDay.value)
      : null;
    if (
      currentSelectedDateString &&
      previousSelectedDateString === currentSelectedDateString
    ) {
      await getAppointmentsOfDay(currentSelectedDateString);
    }

    // Snap inside day
    await validateCurrentDateHasAppointments();
    await snapToNearestForCurrentView();
  }, 150);
}

onMounted(() => {
  if (selectedService.value && selectedService.value.providers) {
    // Gather all selected service IDs (main + any chosen subservices)
    const mainId = selectedService.value.id;
    const chosenSubservices = (selectedService.value.subServices || []).filter(
      (subservice) => subservice.count > 0
    );
    const selectedIds = [mainId, ...chosenSubservices.map((s) => s.id)].map(
      Number
    );
    const providers: OfficeImpl[] = selectedService.value.providers;

    // Passport calendar functionality
    const availableProviders = getAvailableProviders(providers, selectedIds);

    // Checks whether there are restrictions on the providers due to the subservices.
    if (selectedService.value.subServices) {
      selectableProviders.value = availableProviders.filter((provider) => {
        return chosenSubservices.every((subservice) =>
          subservice.providers.some(
            (subserviceProvider) => subserviceProvider.id == provider.id
          )
        );
      });
    } else {
      selectableProviders.value = availableProviders;
    }

    // Checks whether a provider is already selected so that it is displayed first in the slider.
    let offices = selectableProviders.value.filter((office) => {
      if (props.preselectedOfficeId) {
        return office.id == props.preselectedOfficeId;
      } else if (selectedProvider.value) {
        return office.id == selectedProvider.value.id;
      } else {
        return false;
      }
    });

    // Add alternative locations to the slider if allowed
    const allowAlternativeLocations =
      offices.length === 0 ||
      offices[0].showAlternativeLocations === null ||
      offices[0].showAlternativeLocations;

    const allowNonExclusive = offices.length === 0 || !props.exclusiveLocation;

    if (allowAlternativeLocations && allowNonExclusive) {
      const excludedId =
        props.preselectedOfficeId ?? selectedProvider.value?.id;

      const otherOffices = availableProviders.filter(
        (office) => office.id !== excludedId
      );

      const officeIds = new Set(offices.map((office) => office.id));
      offices = [
        ...offices,
        ...otherOffices.filter((office) => !officeIds.has(office.id)),
      ];
    }

    // Remember first office before sorting (preselected/selected stays first)
    const firstOfficeToShow = offices[0];
    if (selectableProviders.value) {
      selectableProviders.value = offices.sort((a, b) => {
        const aPriority = a.priority ?? -Infinity;
        const bPriority = b.priority ?? -Infinity;
        return bPriority - aPriority;
      });
    }

    // If a preselected office ID is provided, only check the corresponding provider's checkbox
    if (props.preselectedOfficeId) {
      selectedProviders.value = selectableProviders.value.reduce(
        (acc, item) => {
          acc[item.id] = String(item.id) === String(props.preselectedOfficeId);
          return acc;
        },
        {} as { [id: string]: boolean }
      );
      initialized = true;
    }

    officeOrder.value = new Map(
      selectableProviders.value.map((office, index) => [
        Number(office.id),
        index,
      ])
    );

    showSelectionForProvider(firstOfficeToShow ?? offices[0]);
  }
});

onUnmounted(() => {
  if (refetchTimer) clearTimeout(refetchTimer);
});

watch(isLoadingAppointments, (loading) => {
  if (loading) {
    isLoadingComplete.value = false;
  }
});

watch(selectedDay, async (newDate) => {
  selectedTimeslot.value = 0;
  if (newDate) {
    await getAppointmentsOfDay(
      convertDateToString(selectedDay.value || new Date())
    );
  }
});

watch(selectableProviders, (newVal) => {
  if (!initialized && newVal && newVal.length) {
    selectedProviders.value = newVal.reduce(
      (acc, item) => {
        acc[item.id] = true;
        return acc;
      },
      {} as { [id: string]: boolean }
    );
    initialized = true;
  }
});

watch(appointmentTimestampsByOffice, () => {
  // Only reset if we are in hourly view and a day is selected
  if (selectedDay.value && timeSlotsInHoursByOffice.value.size > 0) {
    const allHours = Array.from(
      timeSlotsInHoursByOffice.value.values()
    ).flatMap((office) => {
      const hours = Array.from((office as any).appointments.keys());
      return hours.filter((hour) => typeof hour === "number" && hour >= 0);
    });
    if (allHours.length > 0) {
      selectedHour.value = Math.min(...(allHours as number[]));
    }
  }
  // For am/pm view
  else if (selectedDay.value && timeSlotsInDayPartByOffice.value.size > 0) {
    const allDayParts = Array.from(
      timeSlotsInDayPartByOffice.value.values()
    ).flatMap((office) => {
      const dayParts = Array.from((office as any).appointments.keys());
      return dayParts.filter((part) => part === "am" || part === "pm");
    });
    if (allDayParts.includes("am")) {
      selectedDayPart.value = "am";
    } else if (allDayParts.includes("pm")) {
      selectedDayPart.value = "pm";
    }
  }
});

watch(
  selectedProviders,
  async () => {
    // Sync single-selection provider immediately
    const selectedIds = Object.keys(selectedProviders.value).filter(
      (id) => selectedProviders.value[id]
    );
    if (selectedIds.length === 1 && selectableProviders.value) {
      const provider = selectableProviders.value.find(
        (p) => p.id.toString() === selectedIds[0]
      );
      selectedProvider.value = provider ?? undefined;
    } else if (selectedIds.length !== 1) {
      selectedProvider.value = undefined;
    }

    // Pre-hook: set switching state and clear errors immediately
    isSwitchingProvider.value = true;
    error.value = false;
    Object.values(errorStateMap.value).forEach((es) => (es.value = false));

    // Debounced pipeline
    scheduleRefreshAfterProviderChange();
  },
  { deep: true }
);
</script>

<style lang="scss" scoped>
@use "@/styles/breakpoints.scss" as *;

.m-button-group {
  margin-bottom: 20px;
  padding-bottom: 0;
  padding-top: 30px;
}

.no-bottom-margin,
.no-bottom-margin.m-button,
.no-bottom-margin .m-button {
  margin-bottom: 0 !important;
}

.no-top-margin,
.no-top-margin h3 {
  margin-top: 0 !important;
}

.m-button--ghost.disabled,
.m-button--ghost:disabled {
  background: #fff;
  border-color: #fff;
}

.disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
</style>

<style>
.slider-no-margin .m-component__column {
  margin: 0 !important;
}

.slider-no-margin .m-checkbox-group__heading:empty {
  margin: 0 !important;
  padding: 0 !important;
}

.float-right .m-button__icon {
  margin-left: 12px !important;
}

.muc-calendar-view-full-size .muc-calendar-item,
.muc-calendar-container .muc-calendar-item {
  font-size: 1.125rem !important; /* 18px - default for desktop */
}

.muc-calendar-view-full-size,
.muc-calendar-container {
  font-size: 1.125rem !important; /* Desktop size */
}

#listViewAccordion .m-accordion__section-button {
  font-size: 1.125rem !important;
}
</style>
