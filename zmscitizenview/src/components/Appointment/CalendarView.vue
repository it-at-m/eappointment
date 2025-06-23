<template>
  <div v-if="providersWithAppointments && providersWithAppointments.length > 1">
    <div class="m-component slider-no-margin">
      <div class="m-content">
        <h2 tabindex="0">{{ t("location") }}</h2>
      </div>
      <div
        class="m-content"
        v-if="providersWithAppointments.length > 1"
      >
        <div v-for="provider in providersWithAppointments">
          <div
            class="m-checkboxes__item"
            :class="{ disabled: isCheckboxDisabled(provider.id) }"
          >
            <input
              :id="'checkbox-' + provider.id"
              class="m-checkboxes__input"
              name="checkbox"
              type="checkbox"
              :checked="selectedProviders[provider.id]"
              :disabled="isCheckboxDisabled(provider.id)"
              @click="handleProviderCheckbox(provider.id)"
            />
            <label
              class="m-label m-checkboxes__label"
              :for="'checkbox-' + provider.id"
            >
              {{ provider.name }}
            </label>
          </div>
          <div class="provider-address">
            {{ provider.address.street }} {{ provider.address.house_number }}
          </div>
        </div>
      </div>
    </div>
  </div>

  <div
    v-if="
      selectedProvider &&
      selectableProviders &&
      selectableProviders.length === 1
    "
  >
    <div class="m-component">
      <div class="m-content">
        <h2 tabindex="0">{{ t("location") }}</h2>
      </div>
      <div class="m-teaser-contained m-teaser-contained-contact">
        <div class="m-teaser-contained-contact__body">
          <div class="m-teaser-contained-contact__body__inner">
            <div class="m-teaser-contained-contact__icon">
              <svg
                aria-hidden="true"
                class="icon"
              >
                <use xlink:href="#icon-place"></use>
              </svg>
            </div>
            <h3 class="m-teaser-contained-contact__headline">
              {{ selectedProvider.name }}
            </h3>
            <div class="m-teaser-contained-contact__details">
              <p class="m-teaser-contained-contact__detail">
                <svg
                  aria-hidden="true"
                  class="icon icon--before"
                >
                  <use xlink:href="#icon-map-pin"></use>
                </svg>
                <span>
                  {{ selectedProvider.address.street }}
                  {{ selectedProvider.address.house_number }}
                </span>
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div
    v-if="availableDaysFetched && !hasAppointmentsForSelectedProviders()"
    class="m-component"
  >
    <muc-callout type="warning">
      <template #header>
        {{ t("noAppointmentsAvailableHeader") }}
      </template>
      <template #content>
        {{ t("noAppointmentsAvailable") }}
      </template>
    </muc-callout>
  </div>

  <div v-else-if="!error">
    <div class="m-content">
      <h2 tabindex="0">{{ t("time") }}</h2>
    </div>
    <div class="m-component">
      <muc-calendar
        :key="calendarKey"
        :model-value="selectedDay"
        @update:model-value="handleDaySelection"
        disable-view-change
        variant="single"
        :allowed-dates="allowedDates"
        :min="minDate"
        :max="maxDate"
        :view-month="viewMonth"
      />
    </div>

    <div
      v-if="
        selectedDay &&
        (timeSlotsInHoursByOffice.size > 0 || isLoadingAppointments) &&
        appointmentsCount > 18
      "
      :key="
        String(selectedDay) +
        String(selectableProviders) +
        String(timeSlotsInHoursByOffice)
      "
      class="m-component"
    >
      <div class="m-content">
        <h3 tabindex="0">{{ t("availableTimes") }}</h3>
      </div>
      <div
        style="
          margin-bottom: 20px;
          background-color: var(--color-neutrals-blue-xlight);
        "
      >
        <h4 tabindex="0">{{ formatDay(selectedDay) }}</h4>
      </div>

      <div
        v-if="isLoadingAppointments || isLoadingComplete"
        class="m-content"
        style="
          display: flex;
          justify-content: center;
          align-items: center;
          min-height: 80px;
        "
      >
        <!--<MucPercentageSpinner
          size="40%"
          :aria-label="t('loadingAppointmentTimes')"
        />-->
      </div>

      <div
        v-else
        v-for="[officeId, office] in timeSlotsInHoursByOffice"
        :key="String(officeId) + String(selectedProviders[officeId])"
      >
        <div
          v-if="
            selectedProviders[officeId] &&
            currentHour !== null &&
            office.appointments.get(currentHour)
          "
        >
          <div>
            <div
              class="ml-4 location-title"
              v-if="(selectableProviders?.length || 0) > 1"
            >
              <svg
                aria-hidden="true"
                class="icon icon--before"
              >
                <use xlink:href="#icon-map-pin"></use>
              </svg>
              {{ officeName(officeId) }}
            </div>
          </div>
          <div
            v-for="[timeslot, times] in office.appointments"
            :key="timeslot"
          >
            <div
              class="wrapper"
              v-if="timeslot == currentHour"
            >
              <div v-if="firstHour !== null && firstHour > 0">
                <p class="centered-text">{{ timeslot }}:00-{{ timeslot }}:59</p>
              </div>
              <div class="grid">
                <div
                  v-for="time in times"
                  :key="time"
                  class="grid-item"
                >
                  <muc-button
                    class="timeslot"
                    variant="secondary"
                    @click="handleTimeSlotSelection(officeId, time)"
                  >
                    <template #default>{{ formatTime(time) }}</template>
                  </muc-button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div
        class="wrapper m-button-group"
        v-if="!isLoadingAppointments"
      >
        <muc-button
          :key="currentHour ?? 0"
          icon="chevron-left"
          icon-shown-left
          variant="ghost"
          @click="earlierAppointments"
          :disabled="
            currentHour === null ||
            firstHour === null ||
            currentHour <= firstHour ||
            isLoadingAppointments
          "
        >
          <template #default>{{ t("earlier") }}</template>
        </muc-button>

        <muc-button
          :key="currentHour ?? 0"
          class="float-right"
          icon="chevron-right"
          icon-shown-right
          variant="ghost"
          @click="laterAppointments"
          :disabled="
            currentHour === null ||
            lastHour === null ||
            currentHour >= lastHour ||
            isLoadingAppointments
          "
        >
          <template #default>{{ t("later") }}</template>
        </muc-button>
      </div>
    </div>

    <div
      v-else-if="
        selectedDay &&
        (timeSlotsInDayPartByOffice.size > 0 || isLoadingAppointments)
      "
      :key="
        String(selectedDay) +
        String(selectableProviders) +
        String(timeSlotsInDayPartByOffice)
      "
      class="m-component"
    >
      <div class="m-content">
        <h3 tabindex="0">{{ t("availableTimes") }}</h3>
      </div>
      <div
        style="
          margin-bottom: 20px;
          background-color: var(--color-neutrals-blue-xlight);
        "
      >
        <b tabindex="0">{{ formatDay(selectedDay) }}</b>
      </div>

      <div
        v-if="isLoadingAppointments || isLoadingComplete"
        class="m-content"
        style="
          display: flex;
          justify-content: center;
          align-items: center;
          min-height: 80px;
        "
      >
        <!--<MucPercentageSpinner
          size="40%"
          :aria-label="t('loadingAppointmentTimes')"
        />-->
      </div>

      <div
        v-else
        v-for="[officeId, office] in timeSlotsInDayPartByOffice"
      >
        <div
          v-if="
            selectedProviders[officeId] &&
            office.appointments.get(currentDayPart)
          "
        >
          <div>
            <div
              class="ml-4 location-title"
              v-if="(selectableProviders?.length || 0) > 1"
            >
              <svg
                aria-hidden="true"
                class="icon icon--before"
              >
                <use xlink:href="#icon-map-pin"></use>
              </svg>
              {{ officeName(office.officeId) }}
            </div>
          </div>
          <div
            v-for="[timeslot, times] in office.appointments"
            :key="timeslot"
          >
            <div
              class="wrapper"
              v-if="timeslot == currentDayPart"
            >
              <div v-if="currentDayPart === 'am'">
                <p class="centered-text">{{ t("am") }}</p>
              </div>
              <div v-else>
                <p class="centered-text">{{ t("pm") }}</p>
              </div>
              <div class="grid">
                <div
                  v-for="time in times"
                  :key="time"
                  class="grid-item"
                >
                  <muc-button
                    class="timeslot"
                    variant="secondary"
                    @click="handleTimeSlotSelection(officeId, time)"
                  >
                    <template #default>{{ formatTime(time) }}</template>
                  </muc-button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div
        class="wrapper m-button-group"
        v-if="!isLoadingAppointments"
      >
        <muc-button
          icon="chevron-left"
          icon-shown-left
          variant="ghost"
          @click="earlierAppointments('dayPart')"
          :disabled="
            currentDayPart === 'am' ||
            firstDayPart === 'pm' ||
            isLoadingAppointments
          "
        >
          <template #default>{{ t("earlier") }}</template>
        </muc-button>

        <muc-button
          class="float-right"
          icon="chevron-right"
          icon-shown-right
          variant="ghost"
          @click="laterAppointments('dayPart')"
          :disabled="
            currentDayPart === 'pm' ||
            lastDayPart === 'am' ||
            isLoadingAppointments
          "
        >
          <template #default>{{ t("later") }}</template>
        </muc-button>
      </div>
    </div>

    <div
      ref="summary"
      tabindex="0"
    >
      <muc-callout
        v-if="selectedProvider && selectedDay && selectedTimeslot !== 0"
        type="info"
      >
        <template #content>
          <div v-if="selectedProvider">
            <b>{{ t("location") }}</b>
            <p class="m-teaser-contained-contact__summary">
              {{ selectedProvider.name }}
              <br />
              {{ selectedProvider.address.street }}
              {{ selectedProvider.address.house_number }}
            </p>
          </div>
          <div v-if="selectedDay">
            <b>{{ t("time") }}</b>
            <br />
            <p class="m-teaser-contained-contact__detail">
              {{ formatDay(selectedDay) }}, {{ formatTime(selectedTimeslot) }}
              {{ t("clock") }}
              <br />
              {{ t("estimatedDuration") }} {{ estimatedDuration() }}
              {{ t("minutes") }}
            </p>
          </div>
          <div
            v-if="selectedProvider.scope && selectedProvider.scope.displayInfo"
          >
            <b>{{ t("hint") }}</b>
            <br />
            <div v-html="selectedProvider.scope.displayInfo"></div>
          </div>
        </template>

        <template #header>{{ t("selectedAppointment") }}</template>
      </muc-callout>
    </div>
  </div>
  <div
    v-if="showError"
    class="m-component"
  >
    <muc-callout type="warning">
      <template #header>
        {{
          showErrorKey === "altcha.invalidCaptcha"
            ? t("altcha.invalidCaptchaHeader")
            : t("noAppointmentsAvailableHeader")
        }}
      </template>
      <template #content>
        {{ t(showErrorKey) }}
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
</template>

<script setup lang="ts">
import type { Ref } from "vue";

import {
  MucButton,
  MucCalendar,
  MucCallout,
  MucCheckbox, // Todo: Use MucCheckbox once disabled boxes are available in the patternlab-vue package
  MucPercentageSpinner,
} from "@muenchen/muc-patternlab-vue";
import { computed, inject, nextTick, onMounted, ref, watch } from "vue";

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

const props = defineProps<{
  baseUrl: string | undefined;
  isRebooking: boolean;
  exclusiveLocation: string | undefined;
  preselectedOfficeId: string | undefined;
  selectedServiceMap: Map<string, number>;
  captchaToken: string | null;
  bookingError: boolean;
  bookingErrorKey: string;
  t: (key: string) => string;
}>();

const emit = defineEmits<(e: "next" | "back") => void>();

const { selectedService } = inject<SelectedServiceProvider>(
  "selectedServiceProvider"
) as SelectedServiceProvider;

const { selectedProvider, selectedTimeslot } = inject<SelectedTimeslotProvider>(
  "selectedTimeslot"
) as SelectedTimeslotProvider;

// Inject loading states
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

const errorKey = ref("");
const error = ref<boolean>(false);
const showError = computed(() => error.value || props.bookingError);
const showErrorKey = computed(() =>
  error.value ? errorKey.value : props.bookingErrorKey
);

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

const datesWithoutAppointments = ref(new Set<string>());

const isLoadingComplete = ref(false);
let progressInterval: NodeJS.Timeout | null = null;

watch(isLoadingAppointments, (loading) => {
  if (loading) {
    isLoadingComplete.value = false;
  } else {
    isLoadingComplete.value = true;
    if (progressInterval) {
      clearInterval(progressInterval);
      progressInterval = null;
    }
    setTimeout(() => {
      isLoadingComplete.value = false;
    }, 100);
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

const laterAppointments = (type = "hour") => {
  if (type === "dayPart" && currentDayPart.value === "am") {
    selectedDayPart.value = "pm";
    return;
  }
  if (currentHour.value !== null) {
    selectedHour.value = currentHour.value + 1;
  }
};

const earlierAppointments = (type = "hour") => {
  if (type === "dayPart" && currentDayPart.value === "pm") {
    selectedDayPart.value = "am";
    return;
  }
  if (currentHour.value !== null) {
    selectedHour.value = currentHour.value - 1;
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

const timeSlotsInHours = computed(() => {
  const timesByHours = new Map<number, number[]>();
  appointmentTimestamps.value?.forEach((time) => {
    const berlinDate = new Date(time * 1000);
    const hour = parseInt(berlinHourFormatter.format(berlinDate));
    if (!timesByHours.has(hour)) {
      timesByHours.set(hour, []);
    }
    timesByHours.get(hour)?.push(time);
  });
  return timesByHours;
});

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

const showSelectionForProvider = (provider: OfficeImpl) => {
  selectedProvider.value = provider;
  error.value = false;
  selectedDay.value = undefined;
  selectedTimeslot.value = 0;
  const providers = selectableProviders.value || [];
  const providerIds = providers.map((p) => p.id);

  fetchAvailableDays(
    providerIds.map(Number),
    Array.from(props.selectedServiceMap.keys()),
    Array.from(props.selectedServiceMap.values()),
    props.baseUrl ?? undefined,
    props.captchaToken ?? undefined
  ).then((data) => {
    const days = (data as AvailableDaysDTO)?.availableDays;
    if (
      Array.isArray(days) &&
      days.length > 0 &&
      days.every(
        (d) =>
          typeof d === "object" &&
          d !== null &&
          "time" in d &&
          "providerIDs" in d
      )
    ) {
      availableDays.value = days as { time: string; providerIDs: string }[];
      selectedDay.value = new Date((days[0] as any).time);
      availableDaysFetched.value = true;
      minDate.value = new Date((days[0] as any).time);
      maxDate.value = new Date((days[days.length - 1] as any).time);
      error.value = false;
      errorKey.value = "";
    } else {
      handleError(data);
    }
  });
};

const handleError = (data: any): void => {
  error.value = true;

  const tokenErrors = ["captchaMissing", "captchaExpired", "captchaInvalid"];
  const errorCode = data?.errors?.[0]?.errorCode;

  errorKey.value = tokenErrors.includes(errorCode)
    ? "altcha.invalidCaptcha"
    : "noAppointmentsAvailable";
};

const getAppointmentsOfDay = (date: string) => {
  isLoadingAppointments.value = true;
  appointmentTimestamps.value = [];
  appointmentTimestampsByOffice.value = [];
  const providers = selectableProviders.value || [];
  const providerIds = providers.map((p) => p.id);

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

// Add new computed property to filter providers with appointments
const providersWithAppointments = computed(() => {
  // If no available days or empty available days, return empty array
  if (!availableDays?.value || availableDays.value.length === 0) {
    return [];
  }

  // Filter providers that have appointments and maintain their original order
  return (selectableProviders.value || [])
    .filter((provider) => {
      return (availableDays.value ?? []).some((day) =>
        day.providerIDs.split(",").includes(provider.id.toString())
      );
    })
    .sort((a, b) => {
      const aPriority = a.priority ?? -Infinity;
      const bPriority = b.priority ?? -Infinity;
      return bPriority - aPriority;
    });
});

// Add new computed property to track if any provider with appointments is selected
const hasSelectedProviderWithAppointments = computed(() => {
  // If no available days or empty available days, return false
  if (!availableDays?.value || availableDays.value.length === 0) {
    return false;
  }

  return Object.entries(selectedProviders.value).some(
    ([id, isSelected]) =>
      isSelected &&
      providersWithAppointments.value.some((p) => p.id.toString() === id)
  );
});

watch(providersWithAppointments, (newProviders) => {
  // If no provider with appointments is selected and we have providers with appointments, select the first one
  if (!hasSelectedProviderWithAppointments.value && newProviders.length > 0) {
    selectedProviders.value[newProviders[0].id] = true;
  }
});

watch(selectedDay, (newDate) => {
  selectedTimeslot.value = 0;
  if (newDate) {
    getAppointmentsOfDay(convertDateToString(selectedDay.value || new Date()));
  }
});

const handleProviderCheckbox = async (id: string) => {
  // Count how many providers with appointments are currently selected
  const selectedCount = Object.entries(selectedProviders.value).filter(
    ([providerId, isSelected]) =>
      isSelected &&
      providersWithAppointments.value.some(
        (p) => p.id.toString() === providerId
      )
  ).length;

  // If trying to uncheck the last selected provider with appointments, prevent it
  if (selectedCount === 1 && selectedProviders.value[id]) {
    return;
  }

  selectedProviders.value[id] = !selectedProviders.value[id];

  // Update min and max dates based on selected providers
  if (availableDays.value) {
    const selectedProviderIds = Object.entries(selectedProviders.value)
      .filter(([_, isSelected]) => isSelected)
      .map(([id]) => Number(id));

    const availableDaysForSelectedProviders = (
      availableDays.value || []
    ).filter((day) =>
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

      // If current date is no longer available, find the next available date
      if (selectedDay.value) {
        const currentDate = convertDateToString(selectedDay.value);
        const isCurrentDateAvailable = availableDaysForSelectedProviders.some(
          (day) => convertDateToString(new Date(day.time)) === currentDate
        );

        if (!isCurrentDateAvailable) {
          // First try to find a date after the current date
          let nextAvailableDay = availableDaysForSelectedProviders.find(
            (day) => {
              const dayDate = new Date(day.time);
              return dayDate >= (selectedDay.value ?? new Date());
            }
          );

          // If no future date is available, find the closest date before the current date
          if (!nextAvailableDay) {
            nextAvailableDay = [...availableDaysForSelectedProviders]
              .reverse()
              .find((day) => {
                const dayDate = new Date(day.time);
                return dayDate <= (selectedDay.value ?? new Date());
              });
          }

          if (nextAvailableDay) {
            const newDate = new Date(nextAvailableDay.time);
            selectedDay.value = newDate;
            // Set viewMonth to the first day of the month containing the new date
            viewMonth.value = new Date(
              newDate.getFullYear(),
              newDate.getMonth(),
              1
            );
            calendarKey.value++;
            await nextTick();
            await getAppointmentsOfDay(nextAvailableDay.time);
          }
        }
      }
    }
  }

  // If we just unchecked a provider, we need to check if the current date still has appointments
  if (!selectedProviders.value[id] && selectedDay.value) {
    const currentDate = convertDateToString(selectedDay.value);

    // Check if current date has appointments for remaining selected providers
    const dayEntry = availableDays.value?.find(
      (day) => convertDateToString(new Date(day.time)) === currentDate
    );

    const hasAppointments = dayEntry?.providerIDs
      .split(",")
      .some((providerId) => selectedProviders.value[providerId]);

    // If no appointments on current date, find next available date
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
        // Update the selected day and trigger the appointment fetch
        selectedDay.value = new Date(nextAvailableDay.time);
        await nextTick();
        await getAppointmentsOfDay(nextAvailableDay.time);
      }
    }
  }

  // --- SNAP BACK LOGIC FOR HOURLY AND DAYPART VIEWS ---
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
        // Snap to the nearest available hour
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
};

const isCheckboxDisabled = (providerId: string) => {
  // Count how many providers with appointments are currently selected
  const selectedCount = Object.entries(selectedProviders.value).filter(
    ([id, isSelected]) =>
      isSelected &&
      providersWithAppointments.value.some((p) => p.id.toString() === id)
  ).length;

  // Disable if this is the only selected provider with appointments
  return selectedCount === 1 && selectedProviders.value[providerId];
};

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
  let time = 0;
  const serviceProvider = selectedService.value?.providers?.find(
    (provider) => provider.id == selectedProvider.value?.id
  );
  if (
    serviceProvider &&
    serviceProvider.slots &&
    selectedService.value &&
    selectedService.value.count
  ) {
    time =
      selectedService.value.count *
      serviceProvider.slots *
      serviceProvider.slotTimeInMinutes;
  }

  if (selectedService.value?.subServices) {
    selectedService.value.subServices.forEach((subservice) => {
      const subserviceProvider = subservice.providers?.find(
        (provider) => provider.id == selectedProvider.value?.id
      );
      if (subserviceProvider && subservice.count && subserviceProvider.slots) {
        time +=
          subservice.count *
          subserviceProvider.slots *
          subserviceProvider.slotTimeInMinutes;
      }
    });
  }
  return time;
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
    const selectedIds = [mainId, ...chosenSubservices.map((s) => s.id)];

    // Filter out any provider that is disabled by all of the selected IDs
    let availableProviders = selectedService.value.providers.filter(
      (provider) => {
        if (
          !provider.disabledByServices ||
          provider.disabledByServices.length === 0
        ) {
          return true;
        }
        const allDisabled =
          provider.disabledByServices &&
          selectedIds.every((svcId) =>
            provider.disabledByServices!.includes(svcId)
          );
        return !allDisabled;
      }
    );

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

    // If alternative locations are allowed to be selected, they will be added to the slider.
    if (
      offices.length == 0 ||
      !props.exclusiveLocation ||
      offices[0].showAlternativeLocations
    ) {
      const otherOffices = selectableProviders.value.filter((office) => {
        if (props.preselectedOfficeId)
          return office.id != props.preselectedOfficeId;
        else if (selectedProvider.value)
          return office.id != selectedProvider.value.id;
        else return true;
      });
      offices = [...offices, ...otherOffices];
    }

    if (selectableProviders.value) {
      selectableProviders.value = offices.sort((a, b) => {
        const aPriority = a.priority ?? -Infinity;
        const bPriority = b.priority ?? -Infinity;
        return bPriority - aPriority;
      });
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
</script>

<style scoped>
.wrapper {
  display: flex;
  justify-content: left;
  border-bottom: 1px solid var(--color-neutrals-blue);
  padding-bottom: 16px;
  padding-top: 16px;
}

.wrapper > * {
  margin: 0 8px;
}

.grid {
  display: flex;
  flex-wrap: wrap;
}

.grid-item {
  margin: 8px 8px;
}

.float-right {
  margin-left: auto;
  margin-right: 0 !important;
}

.location-title {
  margin-top: 10px;
}

.timeslot {
  height: 2rem;
}

.centered-text {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100%;
  width: 100px;
}

.m-button-group {
  margin-bottom: 20px;
  padding-bottom: 0;
  padding-top: 30px;
}

.provider-address {
  margin-top: -20px;
  margin-bottom: 20px;
  margin-left: 34px;
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

.float-right .m-button__icon {
  margin-left: 12px !important;
}

.m-callout__content ul {
  list-style-type: disc !important;
  padding-left: 1.5rem !important;
}

.m-callout__content ul li {
  list-style-type: disc !important;
  padding-left: 0.5rem !important;
}
</style>
