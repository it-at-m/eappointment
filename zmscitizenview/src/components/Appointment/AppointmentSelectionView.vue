<template>
  <ProviderView
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
    <h2 tabindex="0">{{ t("time") }}</h2>
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
    <ViewToggle
      :t="t"
      @update:isListView="isListView = $event"
    />
    <CalendarView
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
      :APPOINTMENTS_THRESHOLD_FOR_HOURLY_VIEW="
        APPOINTMENTS_THRESHOLD_FOR_HOURLY_VIEW
      "
      :isLoadingAppointments="isLoadingAppointments"
      :isLoadingComplete="isLoadingComplete"
      :availabilityInfoHtml="availabilityInfoHtml"
      :officeName="officeName"
      :isSlotSelected="isSlotSelected"
      :formatTime="formatTime"
      :formatDay="formatDay"
      @update:selectedDay="handleDaySelection"
      @selectTimeSlot="
        ({ officeId, time }) =>
          handleTimeSlotSelection(officeId as number, time)
      "
      @earlier="({ type }) => earlierAppointments(type)"
      @later="({ type }) => laterAppointments(type)"
      @openInfo="openAvailabilityInfoModal"
    />

    <ListView
      v-if="isListView"
      :t="t"
      :firstFiveAvailableDays="firstFiveAvailableDays"
      :openAccordionDate="openAccordionDate"
      :isLoadingAppointments="isLoadingAppointments"
      :availabilityInfoHtml="availabilityInfoHtml"
      :selectableProviders="selectableProviders"
      :selectedProviders="selectedProviders"
      :providersWithAppointments="providersWithAppointments"
      :APPOINTMENTS_THRESHOLD_FOR_HOURLY_VIEW="
        APPOINTMENTS_THRESHOLD_FOR_HOURLY_VIEW
      "
      :canLoadMore="
        firstFiveAvailableDays.length < (availableDays?.length || 0)
      "
      :officeName="officeName"
      :getCurrentHourForDay="getCurrentHourForDay"
      :getCurrentDayPartForDay="getCurrentDayPartForDay"
      :getListDayAvailableHours="getListDayAvailableHours"
      :getListDayAvailableDayParts="getListDayAvailableDayParts"
      :isSlotSelected="isSlotSelected"
      :formatTime="formatTime"
      @toggleDay="onDayAccordionSelect"
      @selectTimeSlot="
        ({ officeId, time }) =>
          handleTimeSlotSelection(officeId as number, time)
      "
      @earlier="({ day, type }) => listViewEarlierAppointments(day, type)"
      @later="({ day, type }) => listViewLaterAppointments(day, type)"
      @loadMore="loadMoreDays"
      @openInfo="openAvailabilityInfoModal"
    />

    <SelectedAppointmentSummary
      ref="summary"
      :t="t"
      :selectedProvider="selectedProvider"
      :selectedDay="selectedDay"
      :selectedTimeslot="selectedTimeslot"
      :formatDay="formatDay"
      :formatTime="formatTime"
      :estimatedDuration="estimatedDuration"
    />
  </div>
  <div
    v-if="!noProviderSelected && showError && !isSwitchingProvider"
    class="m-component"
  >
    <h2 tabindex="0">{{ t("time") }}</h2>
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
import type { AccordionDay } from "@/types/AccordionDay";
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
import { calculateEstimatedDuration } from "@/utils/calculateEstimatedDuration";
import { toCalloutType } from "@/utils/callout";
import {
  createErrorStates,
  getApiErrorTranslation,
  handleApiResponse,
} from "@/utils/errorHandler";
import { generateAvailabilityInfoHtml } from "@/utils/infoForAllAppointments";
import { sanitizeHtml } from "@/utils/sanitizeHtml";
import CalendarView from "./AppointmentSelection/CalendarView.vue";
import ListView from "./AppointmentSelection/ListView.vue";
import ProviderView from "./AppointmentSelection/ProviderView.vue";
import SelectedAppointmentSummary from "./AppointmentSelection/SelectedAppointmentSummary.vue";
import ViewToggle from "./AppointmentSelection/ViewToggle.vue";
import AvailabilityInfoModal from "./AvailabilityInfoModal.vue";

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

const selectedDay = ref<Date>();
const minDate = ref<Date>();
const maxDate = ref<Date>();
const viewMonth = ref<Date>(new Date());
const officeOrder = ref<Map<number, number>>(new Map());
const calendarKey = ref(0);

const selectedProviders = ref<{ [id: string]: boolean }>({});

let initialized = false;
const availableDaysFetched = ref(false);
const isLoadingAppointments = ref(false);
const isSwitchingProvider = ref(false);

const datesWithoutAppointments = ref(new Set<string>());

const isLoadingComplete = ref(false);

let refetchTimer: ReturnType<typeof setTimeout> | undefined;

watch(isLoadingAppointments, (loading) => {
  if (loading) {
    isLoadingComplete.value = false;
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

watch(
  selectedProviders,
  () => {
    // Set flag when provider selection changes to prevent error callout flash
    isSwitchingProvider.value = true;
    // Immediately clear any existing error states
    error.value = false;
    Object.values(errorStateMap.value).forEach((errorState) => {
      errorState.value = false;
    });
    // Note: isSwitchingProvider flag is now reset when the API request completes
    // in refetchAvailableDaysForSelection, so no timeout needed here
  },
  { deep: true }
);

/**
 * Reference to the appointment summary.
 * After selecting a time slot, the focus is placed on the appointment summary.
 */
const summary = ref<HTMLElement | null>(null);

const TODAY = new Date();
const MAXDATE = new Date(
  TODAY.getFullYear(),
  TODAY.getMonth() + 6,
  TODAY.getDate()
);

const formatDay = (date: Date) => {
  if (date) {
    return (
      formatterWeekday.format(date) +
      ", " +
      String(date.getDate()).padStart(2, "0") +
      "." +
      String(date.getMonth() + 1).padStart(2, "0") +
      "." +
      date.getFullYear()
    );
  }
};

const getProvider = (id: number | string): OfficeImpl | undefined => {
  return (selectableProviders.value || []).find(
    (p) => String(p.id) === String(id)
  );
};

const officeName = (id: number | string): string | null => {
  const office = (selectableProviders.value || []).find(
    (p) => String(p.id) === String(id)
  );
  return office?.name ?? null;
};

const getAvailableHours = () => {
  // Only include hours where at least one selected provider has appointments
  const hourSet = new Set<number>();
  for (const [officeId, office] of timeSlotsInHoursByOffice.value) {
    if (selectedProviders.value[officeId]) {
      for (const hour of office.appointments.keys()) {
        if ((office.appointments.get(hour) || []).length > 0) {
          hourSet.add(hour);
        }
      }
    }
  }
  return Array.from(hourSet).sort((a, b) => a - b);
};

const laterAppointments = (type = "hour") => {
  if (type === "dayPart" && currentDayPart.value === "am") {
    selectedDayPart.value = "pm";
    return;
  }
  if (currentHour.value !== null) {
    const availableHours = getAvailableHours();
    const idx = availableHours.indexOf(currentHour.value);
    if (idx !== -1 && idx < availableHours.length - 1) {
      selectedHour.value = availableHours[idx + 1];
    }
  }
};

const earlierAppointments = (type = "hour") => {
  if (type === "dayPart" && currentDayPart.value === "pm") {
    selectedDayPart.value = "am";
    return;
  }
  if (currentHour.value !== null) {
    const availableHours = getAvailableHours();
    const idx = availableHours.indexOf(currentHour.value);
    if (idx > 0) {
      selectedHour.value = availableHours[idx - 1];
    }
  }
};

const getListDayAvailableHours = (day: AccordionDay) => {
  const hourSet = new Set<number>();
  day.hourRows.forEach((hourRow) => {
    if (hourRow.times.length > 0) {
      hourSet.add(hourRow.hour);
    }
  });
  return Array.from(hourSet).sort((a, b) => a - b);
};

const getListDayAvailableDayParts = (day: AccordionDay) => {
  const dayParts: ("am" | "pm")[] = [];
  day.dayPartRows.forEach((partRow) => {
    if (partRow.times.length > 0) {
      dayParts.push(partRow.part);
    }
  });
  return dayParts.sort((a, b) => (a === "am" ? -1 : 1));
};

const listViewEarlierAppointments = (
  day: AccordionDay,
  type: "hour" | "dayPart"
) => {
  const dateString = day.dateString;

  if (type === "dayPart") {
    const currentPart = listViewCurrentDayPart.value.get(dateString);
    if (currentPart === "pm") {
      listViewCurrentDayPart.value.set(dateString, "am");
    }
  } else {
    const currentHour = listViewCurrentHour.value.get(dateString);
    if (currentHour !== undefined) {
      const availableHours = getListDayAvailableHours(day);
      const idx = availableHours.indexOf(currentHour);
      if (idx > 0) {
        listViewCurrentHour.value.set(dateString, availableHours[idx - 1]);
      }
    }
  }
};

const listViewLaterAppointments = (
  day: AccordionDay,
  type: "hour" | "dayPart"
) => {
  const dateString = day.dateString;

  if (type === "dayPart") {
    const currentPart = listViewCurrentDayPart.value.get(dateString);
    if (currentPart === "am") {
      listViewCurrentDayPart.value.set(dateString, "pm");
    }
  } else {
    const currentHour = listViewCurrentHour.value.get(dateString);
    if (currentHour !== undefined) {
      const availableHours = getListDayAvailableHours(day);
      const idx = availableHours.indexOf(currentHour);
      if (idx !== -1 && idx < availableHours.length - 1) {
        listViewCurrentHour.value.set(dateString, availableHours[idx + 1]);
      }
    }
  }
};

const timeSlotsInDayPartBySelectedOffice = computed(() => {
  return Object.entries(timeSlotsInDayPartByOffice).filter(
    ([officeId]) => selectedProviders.value[officeId]
  );
});

const formatterWeekday = new Intl.DateTimeFormat("de-DE", { weekday: "long" });

const formatterTime = new Intl.DateTimeFormat("de-DE", {
  timeZone: "Europe/Berlin",
  hour: "numeric",
  minute: "numeric",
  hour12: false,
});

const berlinHourFormatter = new Intl.DateTimeFormat("de-DE", {
  timeZone: "Europe/Berlin",
  hour: "numeric",
  hour12: false,
});

const formatTime = (time: any) => {
  const date = new Date(time * 1000);
  return formatterTime.format(date);
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
    selectedDay.value = new Date((days[0] as any).time);
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

const showSelectionForProvider = async (provider: OfficeImpl) => {
  isSwitchingProvider.value = true;
  selectedProvider.value = provider;
  error.value = false;
  selectedDay.value = undefined;
  selectedTimeslot.value = 0;
  await refetchAvailableDaysForSelection();
};

const handleError = (data: any): void => {
  error.value = true;
  handleApiResponse(data, errorStateMap.value, currentErrorData.value);
};

const getAppointmentsOfDay = (date: string) => {
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

  fetchAvailableTimeSlots(
    date,
    providerIds.map(Number),
    Array.from(props.selectedServiceMap.keys()),
    Array.from(props.selectedServiceMap.values()),
    props.baseUrl ?? undefined,
    props.captchaToken ?? undefined
  )
    .then((data) => {
      if (data && "offices" in data && Array.isArray((data as any).offices)) {
        appointmentTimestampsByOffice.value = (
          data as AvailableTimeSlotsByOfficeDTO
        ).offices;

        appointmentsCount.value = (data as any).offices.reduce(
          (sum: number, office: any) =>
            sum + (office.appointments?.length ?? 0),
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

          // If no appointments on current date but appointments exist on other days,
          // select the first available date with appointments
          if (
            appointmentsCount.value === 0 &&
            availableDays.value &&
            availableDays.value.length > 0
          ) {
            const firstAvailableDay = availableDays.value.find((day) => {
              const dayDate = new Date(day.time);
              return (
                dayDate > new Date(date) &&
                day.providerIDs
                  .split(",")
                  .some((id) => selectedProviders.value[id])
              );
            });

            if (firstAvailableDay) {
              selectedDay.value = new Date(firstAvailableDay.time);
              // Sync viewMonth and re-render calendar
              viewMonth.value = new Date(
                selectedDay.value.getFullYear(),
                selectedDay.value.getMonth(),
                1
              );
              calendarKey.value++;
            }
          }
        }
      } else {
        // Track dates without appointments
        datesWithoutAppointments.value.add(date);

        // Only show error if there are no appointments on any day
        if (!hasAppointmentsForSelectedProviders()) {
          error.value = true;
        } else {
          error.value = false;

          // If no appointments on current date but appointments exist on other days,
          // select the first available date with appointments
          if (availableDays.value && availableDays.value.length > 0) {
            const firstAvailableDay = availableDays.value.find((day) => {
              const dayDate = new Date(day.time);
              return (
                dayDate > new Date(date) &&
                day.providerIDs
                  .split(",")
                  .some((id) => selectedProviders.value[id])
              );
            });

            if (firstAvailableDay) {
              selectedDay.value = new Date(firstAvailableDay.time);
              // Sync viewMonth and re-render calendar
              viewMonth.value = new Date(
                selectedDay.value.getFullYear(),
                selectedDay.value.getMonth(),
                1
              );
              calendarKey.value++;
            }
          }
        }
      }
      isLoadingAppointments.value = false;
    })
    .catch(() => {
      isLoadingAppointments.value = false;
    });
};

const convertDateToString = (date: Date) => {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const day = String(date.getDate()).padStart(2, "0");
  return `${year}-${month}-${day}`;
};

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

// Watch for changes in selectedProviders and update selectedProvider accordingly
watch(
  selectedProviders,
  (newSelection) => {
    const selectedCount = Object.values(newSelection).filter(Boolean).length;

    if (selectedCount === 1) {
      // Exactly one provider selected, update selectedProvider to that one
      const selectedProviderId = Object.keys(newSelection).find(
        (id) => newSelection[id]
      );

      if (selectedProviderId && selectableProviders.value) {
        const provider = selectableProviders.value.find(
          (p) => p.id.toString() === selectedProviderId
        );
        if (provider) {
          selectedProvider.value = provider;
        }
      }
    } else {
      // Multiple or no providers selected, clear selectedProvider
      selectedProvider.value = undefined;
    }
  },
  { deep: true }
);

watch(selectedDay, (newDate) => {
  selectedTimeslot.value = 0;
  if (newDate) {
    getAppointmentsOfDay(convertDateToString(selectedDay.value || new Date()));
  }
});

const handleTimeSlotSelection = async (officeId: number, timeSlot: number) => {
  selectedTimeslot.value = timeSlot;
  selectedProvider.value = getProvider(officeId);
  if (summary.value) {
    await nextTick();
    summary.value.focus();
    summary.value.scrollIntoView({ behavior: "smooth", block: "center" });
  }
};

/**
 * This function determines the expected duration of the appointment.
 * The provider is queried for the service and each subservice because the slots for the respective service are stored in this provider.
 */
const estimatedDuration = () => {
  return calculateEstimatedDuration(
    selectedService.value,
    selectedProvider.value
  );
};

const nextStep = () => emit("next");
const previousStep = () => emit("back");

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

    showSelectionForProvider(offices[0]);
  }
});

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
    const [clean, restricted] = [
      group.find((p) => (p.disabledByServices ?? []).length === 0)!,
      group.find((p) => (p.disabledByServices ?? []).length > 0)!,
    ];

    const restrictedDisabled = (restricted.disabledByServices ?? []).map(
      Number
    );
    const allDisabled = selectedIds.every((id) =>
      restrictedDisabled.includes(id)
    );

    return allDisabled ? clean : restricted;
  });
}

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
      return hours.filter((hour) => typeof hour === "number" && hour > 0);
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

watch(appointmentTimestampsByOffice, () => {
  // Only reset if we are in hourly view and a day is selected
  if (selectedDay.value && timeSlotsInHoursByOffice.value.size > 0) {
    const allHours = Array.from(
      timeSlotsInHoursByOffice.value.values()
    ).flatMap((office) => {
      const hours = Array.from((office as any).appointments.keys());
      return hours.filter((hour) => typeof hour === "number" && hour > 0);
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

async function snapToNearestAvailableTimeSlot() {
  await nextTick(); // Ensure computed properties are updated

  // Hourly view: snap selectedHour to the nearest available hour if current is not available
  if (timeSlotsInHoursByOffice.value.size > 0) {
    const availableHours = Array.from(timeSlotsInHoursByOffice.value.values())
      .flatMap((office) => Array.from((office as any).appointments.keys()))
      .filter((hour): hour is number => typeof hour === "number");
    if (
      selectedHour.value === null ||
      !availableHours.includes(selectedHour.value as number)
    ) {
      if (availableHours.length > 0) {
        // Snap to the nearest available hour, prefer earlier if equally close
        const prevHour = selectedHour.value;
        let nearest = availableHours[0];
        let minDiff = Math.abs((prevHour ?? nearest) - nearest);
        for (const hour of availableHours) {
          const diff = Math.abs((prevHour ?? hour) - hour);
          if (diff < minDiff || (diff === minDiff && hour < nearest)) {
            nearest = hour;
            minDiff = diff;
          }
        }
        selectedHour.value = nearest;
      } else {
        selectedHour.value = null;
      }
    }
  }
  // DayPart view: snap selectedDayPart to the other part if current is not available
  else if (timeSlotsInDayPartByOffice.value.size > 0) {
    const availableDayParts = Array.from(
      timeSlotsInDayPartByOffice.value.values()
    )
      .flatMap((office) => Array.from((office as any).appointments.keys()))
      .filter((part): part is "am" | "pm" => part === "am" || part === "pm");
    if (
      selectedDayPart.value === null ||
      !availableDayParts.includes(selectedDayPart.value as "am" | "pm")
    ) {
      // Prefer the other part if available
      if (selectedDayPart.value === "am" && availableDayParts.includes("pm")) {
        selectedDayPart.value = "pm";
      } else if (
        selectedDayPart.value === "pm" &&
        availableDayParts.includes("am")
      ) {
        selectedDayPart.value = "am";
      } else if (availableDayParts.length > 0) {
        selectedDayPart.value = availableDayParts[0];
      } else {
        selectedDayPart.value = null;
      }
    }
  }
}

async function snapToListViewNearestAvailableTimeSlot() {
  await nextTick();

  for (const [dateString] of listViewCurrentHour.value) {
    const currentHour = listViewCurrentHour.value.get(dateString);
    if (currentHour !== undefined) {
      const hasAppointmentsInHour = firstFiveAvailableDays.value.some((day) => {
        if (day.dateString === dateString) {
          return day.hourRows.some(
            (hourRow) =>
              hourRow.hour === currentHour &&
              hourRow.times.length > 0 &&
              selectedProviders.value[hourRow.officeId]
          );
        }
        return false;
      });

      if (!hasAppointmentsInHour) {
        const day = firstFiveAvailableDays.value.find(
          (d) => d.dateString === dateString
        );
        if (day) {
          const availableHours = getListDayAvailableHours(day);
          if (availableHours.length > 0) {
            let nearest = availableHours[0];
            let minDiff = Math.abs(currentHour - nearest);
            for (const hour of availableHours) {
              const diff = Math.abs(currentHour - hour);
              if (diff < minDiff || (diff === minDiff && hour < nearest)) {
                nearest = hour;
                minDiff = diff;
              }
            }
            if (nearest !== currentHour) {
              listViewCurrentHour.value.set(dateString, nearest);
            }
          }
        }
      }
    }
  }

  for (const [dateString] of listViewCurrentDayPart.value) {
    const currentDayPart = listViewCurrentDayPart.value.get(dateString);
    if (currentDayPart !== undefined) {
      const hasAppointmentsInDayPart = firstFiveAvailableDays.value.some(
        (day) => {
          if (day.dateString === dateString) {
            return day.dayPartRows.some(
              (partRow) =>
                partRow.part === currentDayPart &&
                partRow.times.length > 0 &&
                selectedProviders.value[partRow.officeId]
            );
          }
          return false;
        }
      );

      if (!hasAppointmentsInDayPart) {
        const day = firstFiveAvailableDays.value.find(
          (d) => d.dateString === dateString
        );
        if (day) {
          const availableDayParts = getListDayAvailableDayParts(day);
          if (availableDayParts.length > 0) {
            let newDayPart = currentDayPart;
            if (currentDayPart === "am" && availableDayParts.includes("pm")) {
              newDayPart = "pm";
            } else if (
              currentDayPart === "pm" &&
              availableDayParts.includes("am")
            ) {
              newDayPart = "am";
            } else {
              newDayPart = availableDayParts[0];
            }
            if (newDayPart !== currentDayPart) {
              listViewCurrentDayPart.value.set(dateString, newDayPart);
            }
          }
        }
      }
    }
  }
}

watch(
  selectedProviders,
  async () => {
    // Re-fetch available days whenever provider selection changes (debounced)
    const selectionSnapshot = JSON.stringify(selectedProviders.value);
    if (refetchTimer) clearTimeout(refetchTimer);
    refetchTimer = setTimeout(async () => {
      await refetchAvailableDaysForSelection();
      // Selection changed while awaiting? Abort this cycle.
      if (selectionSnapshot !== JSON.stringify(selectedProviders.value)) {
        return;
      }
      const availableDaysForSelectedProviders =
        updateDateRangeForSelectedProviders();
      await validateAndUpdateSelectedDate(availableDaysForSelectedProviders);
      await snapToNearestAvailableTimeSlot();
      await validateCurrentDateHasAppointments();

      if (isListView.value) {
        await snapToListViewNearestAvailableTimeSlot();
      }
    }, 150);
  },
  { deep: true }
);

onUnmounted(() => {
  if (refetchTimer) clearTimeout(refetchTimer);
});

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

const APPOINTMENTS_THRESHOLD_FOR_HOURLY_VIEW = 18;

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

// When no providers are selected, show info trigger if any availability info exists across providers.
// If all providers share the same info, show that; otherwise, group by provider names using the shared generator.
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

const openAccordionDate = ref<string | null>(null);

const daysToShow = ref(5);

const listViewCurrentHour = ref<Map<string, number>>(new Map());
const listViewCurrentDayPart = ref<Map<string, "am" | "pm">>(new Map());

const loadMoreDays = () => {
  daysToShow.value += 3;
};

const firstFiveAvailableDays = computed<AccordionDay[]>(() => {
  if (!availableDays.value) return [];

  const availableForProviders = availableDays.value.filter((day) =>
    day.providerIDs.split(",").some((id) => selectedProviders.value[id])
  );

  const trulyAvailable = availableForProviders.filter((day) => {
    const dateStr = convertDateToString(new Date(day.time));
    return !datesWithoutAppointments.value.has(dateStr);
  });

  return trulyAvailable.slice(0, daysToShow.value).map((dayObj) => {
    const d = new Date(dayObj.time);
    const dateString = convertDateToString(d);
    const label =
      formatterWeekday.format(d) +
      ", " +
      String(d.getDate()).padStart(2, "0") +
      "." +
      String(d.getMonth() + 1).padStart(2, "0") +
      "." +
      d.getFullYear();

    let appointmentsCount = 0;
    const hourRows: AccordionDay["hourRows"] = [];
    const dayPartRows: AccordionDay["dayPartRows"] = [];

    appointmentTimestampsByOffice.value.forEach((office) => {
      if (!selectedProviders.value[office.officeId]) return;

      const times = office.appointments.filter((ts) => {
        return convertDateToString(new Date(ts * 1000)) === dateString;
      });
      appointmentsCount += times.length;

      const byHour: Record<number, number[]> = {};
      const byPart: { am: number[]; pm: number[] } = { am: [], pm: [] };

      times.forEach((ts) => {
        const hr = parseInt(berlinHourFormatter.format(new Date(ts * 1000)));
        (byHour[hr] ||= []).push(ts);
        const part = hr >= 12 ? "pm" : "am";
        byPart[part].push(ts);
      });

      Object.entries(byHour).forEach(([hour, tsArray]) => {
        hourRows.push({
          hour: Number(hour),
          times: tsArray,
          officeId: office.officeId,
        });
      });
      if (byPart.am.length) {
        dayPartRows.push({
          part: "am",
          times: byPart.am,
          officeId: office.officeId,
        });
      }
      if (byPart.pm.length) {
        dayPartRows.push({
          part: "pm",
          times: byPart.pm,
          officeId: office.officeId,
        });
      }
    });

    // hourRows: first by hour, then by provider order (officeOrder)
    hourRows.sort((hourRowLeft, hourRowRight) => {
      if (hourRowLeft.hour !== hourRowRight.hour) {
        return hourRowLeft.hour - hourRowRight.hour;
      }
      const left =
        officeOrder.value.get(Number(hourRowLeft.officeId)) ??
        Number.MAX_SAFE_INTEGER;
      const right =
        officeOrder.value.get(Number(hourRowRight.officeId)) ??
        Number.MAX_SAFE_INTEGER;
      return left - right;
    });

    // dayPartRows: first AM before PM, then by provider order (officeOrder)
    dayPartRows.sort((dayPartRowLeft, dayPartRowRight) => {
      if (dayPartRowLeft.part !== dayPartRowRight.part) {
        return dayPartRowLeft.part === "am" ? -1 : 1;
      }
      const left =
        officeOrder.value.get(Number(dayPartRowLeft.officeId)) ??
        Number.MAX_SAFE_INTEGER;
      const right =
        officeOrder.value.get(Number(dayPartRowRight.officeId)) ??
        Number.MAX_SAFE_INTEGER;
      return left - right;
    });

    if (!listViewCurrentHour.value.has(dateString)) {
      const availableHours = getListDayAvailableHours({
        hourRows,
        dayPartRows,
      } as AccordionDay);
      if (availableHours.length > 0) {
        listViewCurrentHour.value.set(dateString, availableHours[0]);
      }
    }

    if (!listViewCurrentDayPart.value.has(dateString)) {
      const availableDayParts = getListDayAvailableDayParts({
        hourRows,
        dayPartRows,
      } as AccordionDay);
      if (availableDayParts.length > 0) {
        listViewCurrentDayPart.value.set(dateString, availableDayParts[0]);
      }
    }

    return {
      date: d,
      dateString,
      label,
      appointmentsCount,
      hourRows,
      dayPartRows,
    };
  });
});

watch(firstFiveAvailableDays, (newDays) => {
  if (newDays.length > 0 && !openAccordionDate.value) {
    openAccordionDate.value = newDays[0].dateString;
  }
});

const onDayAccordionSelect = (day: AccordionDay) => {
  if (openAccordionDate.value === day.dateString) {
    openAccordionDate.value = null;
  } else {
    openAccordionDate.value = day.dateString;
    selectedDay.value = day.date;
    handleDaySelection(day.date);

    const availableHours = getListDayAvailableHours(day);
    if (availableHours.length > 0) {
      listViewCurrentHour.value.set(day.dateString, availableHours[0]);
    }

    const availableDayParts = getListDayAvailableDayParts(day);
    if (availableDayParts.length > 0) {
      listViewCurrentDayPart.value.set(day.dateString, availableDayParts[0]);
    }
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

const getCurrentHourForDay = (dateString: string): number | undefined => {
  return listViewCurrentHour.value.get(dateString);
};

const getCurrentDayPartForDay = (
  dateString: string
): "am" | "pm" | undefined => {
  return listViewCurrentDayPart.value.get(dateString);
};
</script>

<style lang="scss" scoped>
@use "@/styles/breakpoints.scss" as *;

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
