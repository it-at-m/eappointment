<template>
  <div v-if="providersWithAppointments && providersWithAppointments.length > 1">
    <div class="m-component slider-no-margin">
      <div class="m-content">
        <h2
          tabindex="0"
          style="margin-bottom: 0"
        >
          {{ t("location") }}
        </h2>
      </div>
      <div class="m-content">
        <MucCheckboxGroup :errorMsg="providerSelectionError">
          <template #checkboxes>
            <MucCheckbox
              v-for="provider in providersWithAppointments"
              :key="provider.id"
              :id="'checkbox-' + provider.id"
              :label="provider.name"
              :hint="
                provider.address.street + ' ' + provider.address.house_number
              "
              v-model="selectedProviders[provider.id]"
            />
          </template>
        </MucCheckboxGroup>
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
        {{ t("apiErrorNoAppointmentForThisScopeHeader") }}
      </template>
      <template #content>
        {{ t("apiErrorNoAppointmentForThisScopeText") }}
      </template>
    </muc-callout>
  </div>

  <div v-else-if="!error">
    <div
      class="m-content"
      style="
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
      "
    >
      <h2 tabindex="0">{{ t("time") }}</h2>
      <div
        class="m-toggle-switch"
        role="switch"
        :aria-checked="isListView"
        tabindex="0"
        @click="toggleView"
        @keydown.enter.prevent="toggleView"
        @keydown.space.prevent="toggleView"
      >
        <span class="m-toggle-switch__label">{{ t("calendarView") }}</span>
        <span class="m-toggle-switch__indicator"><span></span></span>
        <span class="m-toggle-switch__label">{{ t("listView") }}</span>
      </div>
    </div>
    <div
      v-if="!isListView"
      class="m-component"
    >
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
      v-if="isListView"
      class="m-content"
    >
      <h3 tabindex="0">{{ t("availableTimes") }}</h3>
    </div>

    <div
      v-if="isListView"
      class="m-component m-component-accordion"
    >
      <div class="m-component__body">
        <div
          class="m-accordion"
          id="listViewAccordion"
        >
          <template
            v-for="(day, index) in firstFiveAvailableDays"
            :key="day.dateString"
          >
            <div>
              <h3
                style="
                  margin-bottom: 20px;
                  background-color: var(--color-neutrals-blue-xlight);
                "
                class="m-accordion__section-header"
                :id="'listHeading-' + index"
              >
                <button
                  class="m-accordion__section-button"
                  type="button"
                  data-bs-toggle="collapse"
                  :data-bs-target="'#listContent-' + index"
                  :aria-expanded="index === 0"
                  :aria-controls="'listContent-' + index"
                  @click="onDayAccordionSelect(day)"
                >
                  {{ day.label }}
                  <svg
                    aria-hidden="true"
                    focusable="false"
                    class="icon"
                  >
                    <use
                      :xlink:href="
                        index === openAccordionIndex
                          ? '#icon-chevron-up'
                          : '#icon-chevron-down'
                      "
                    ></use>
                  </svg>
                </button>
              </h3>

              <section
                class="m-accordion__section-content collapse"
                :class="{ show: index === openAccordionIndex }"
                :id="'listContent-' + index"
                :aria-labelledby="'listHeading-' + index"
                data-bs-parent="#listViewAccordion"
              >
                <div class="m-textplus__content">
                  <template
                    v-if="isLoadingAppointments && index === openAccordionIndex"
                  >
                    <div
                      style="
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        min-height: 80px;
                      "
                    ></div>
                  </template>

                  <template
                    v-else-if="
                      day.appointmentsCount >
                      APPOINTMENTS_THRESHOLD_FOR_HOURLY_VIEW
                    "
                  >
                    <template
                      v-for="(hourRow, hIndex) in day.hourRows"
                      :key="hIndex"
                    >
                      <div
                        class="ml-4 location-title"
                        v-if="
                          (selectableProviders?.length || 0) > 1 &&
                          (hIndex === 0 ||
                            day.hourRows[hIndex - 1].officeId !==
                              hourRow.officeId)
                        "
                      >
                        <svg
                          aria-hidden="true"
                          class="icon icon--before"
                        >
                          <use xlink:href="#icon-map-pin"></use>
                        </svg>
                        {{ officeName(hourRow.officeId) }}
                      </div>
                      <div class="wrapper">
                        <p class="centered-text nowrap">
                          {{ hourRow.hour }}:00‑{{ hourRow.hour }}:59
                        </p>
                        <div class="grid">
                          <div
                            v-for="time in hourRow.times"
                            :key="time"
                            class="grid-item"
                          >
                            <muc-button
                              class="timeslot"
                              :variant="
                                isSlotSelected(hourRow.officeId, time)
                                  ? 'primary'
                                  : 'secondary'
                              "
                              @click="
                                handleTimeSlotSelection(hourRow.officeId, time)
                              "
                            >
                              <template #default
                                >{{ formatTime(time) }}
                              </template>
                            </muc-button>
                          </div>
                        </div>
                      </div>
                    </template>
                  </template>

                  <template v-else>
                    <template
                      v-for="(partRow, pIndex) in day.dayPartRows"
                      :key="pIndex"
                    >
                      <div
                        class="ml-4 location-title"
                        v-if="
                          (selectableProviders?.length || 0) > 1 &&
                          (pIndex === 0 ||
                            day.dayPartRows[pIndex - 1].officeId !==
                              partRow.officeId)
                        "
                      >
                        <svg
                          aria-hidden="true"
                          class="icon icon--before"
                        >
                          <use xlink:href="#icon-map-pin"></use>
                        </svg>
                        {{ officeName(partRow.officeId) }}
                      </div>
                      <div class="wrapper">
                        <p class="centered-text nowrap">
                          {{ t(partRow.part) }}
                        </p>
                        <div class="grid">
                          <div
                            v-for="time in partRow.times"
                            :key="time"
                            class="grid-item"
                          >
                            <muc-button
                              class="timeslot"
                              :variant="
                                isSlotSelected(partRow.officeId, time)
                                  ? 'primary'
                                  : 'secondary'
                              "
                              @click="
                                handleTimeSlotSelection(partRow.officeId, time)
                              "
                            >
                              <template #default
                                >{{ formatTime(time) }}
                              </template>
                            </muc-button>
                          </div>
                        </div>
                      </div>
                    </template>
                  </template>
                </div>
              </section>
            </div>
          </template>
        </div>
      </div>
    </div>

    <muc-button
      v-if="isListView && firstFiveAvailableDays.length < availableDays.length"
      @click="loadMoreDays"
      icon="chevron-down"
      icon-animated
      style="margin-top: 16px"
    >
      <template #default>{{ t("loadMore") }}</template>
    </muc-button>

    <div
      v-if="
        !isListView &&
        selectedDay &&
        (timeSlotsInHoursByOffice.size > 0 || isLoadingAppointments) &&
        appointmentsCount > APPOINTMENTS_THRESHOLD_FOR_HOURLY_VIEW
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
      ></div>

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
              v-if="
                timeslot == currentHour ||
                providersWithAppointments.length === 1
              "
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
                    :variant="
                      isSlotSelected(officeId, time) ? 'primary' : 'secondary'
                    "
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
        v-if="!isLoadingAppointments && providersWithAppointments.length > 1"
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
        !isListView &&
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
      ></div>

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
              v-if="
                timeslot == currentDayPart ||
                providersWithAppointments.length === 1
              "
            >
              <div>
                <p class="centered-text">{{ t(timeslot) }}</p>
              </div>
              <div class="grid">
                <div
                  v-for="time in times"
                  :key="time"
                  class="grid-item"
                >
                  <muc-button
                    class="timeslot"
                    :variant="
                      isSlotSelected(officeId, time) ? 'primary' : 'secondary'
                    "
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
        v-if="!isLoadingAppointments && providersWithAppointments.length > 1"
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
        {{ t(apiErrorTranslation.headerKey) }}
      </template>
      <template #content>
        {{ t(apiErrorTranslation.textKey) }}
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
import type { AccordionDay } from "@/types/AccordionDay";
import type { Ref } from "vue";

import {
  MucButton,
  MucCalendar,
  MucCallout,
  MucCheckbox, // Todo: Use MucCheckbox once disabled boxes are available in the patternlab-vue package
  MucCheckboxGroup,
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
import { calculateEstimatedDuration } from "@/utils/calculateEstimatedDuration";
import {
  createErrorStates,
  getApiErrorTranslation,
  handleApiResponse,
} from "@/utils/errorHandler";

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

// Create centralized error states
const errorStates = createErrorStates();
const errorStateMap = computed(() => errorStates.errorStateMap);

const error = ref<boolean>(false);
const showError = computed(() => error.value || props.bookingError);

// Computed property to get the correct translation for API errors
const apiErrorTranslation = computed(() => {
  return getApiErrorTranslation(errorStateMap.value);
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

const datesWithoutAppointments = ref(new Set<string>());

const isLoadingComplete = ref(false);

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
    } else {
      handleError(data);
    }
  });
};

const handleError = (data: any): void => {
  error.value = true;

  handleApiResponse(data, errorStateMap.value);
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

    // If alternative locations are allowed to be selected, they will be added to the slider.
    if (
      offices.length == 0 ||
      !props.exclusiveLocation ||
      offices[0].showAlternativeLocations
    ) {
      const otherOffices = availableProviders.filter((office) => {
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

watch(
  selectedProviders,
  async (newVal, oldVal) => {
    const availableDaysForSelectedProviders =
      updateDateRangeForSelectedProviders();
    await validateAndUpdateSelectedDate(availableDaysForSelectedProviders);
    await snapToNearestAvailableTimeSlot();
    await validateCurrentDateHasAppointments();
  },
  { deep: true }
);

const providerSelectionError = computed(() => {
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

const APPOINTMENTS_THRESHOLD_FOR_HOURLY_VIEW = 18;

const isListView = ref(false);
const toggleView = () => {
  isListView.value = !isListView.value;
};

const openAccordionIndex = ref(0);

const daysToShow = ref(5);

const loadMoreDays = () => {
  daysToShow.value += 3;
  openAccordionIndex.value = -1;
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

    hourRows.sort((a, b) => a.hour - b.hour);
    dayPartRows.sort((a, b) => (a.part === "am" ? -1 : 1));

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

const onDayAccordionSelect = (day: AccordionDay) => {
  const idx = firstFiveAvailableDays.value.findIndex(
    (d) => d.dateString === day.dateString
  );
  if (openAccordionIndex.value === idx) {
    openAccordionIndex.value = -1; // Accordion schließen
  } else {
    openAccordionIndex.value = idx; // Accordion öffnen
    selectedDay.value = day.date;
    handleDaySelection(day.date);
  }
};

const isSlotSelected = (officeId: number | string, time: number) =>
  selectedTimeslot.value === time &&
  selectedProvider.value?.id?.toString() === officeId.toString();
</script>

<style scoped>
.wrapper {
  display: grid;
  grid-template-columns: 6rem 1fr;
  column-gap: 8px;
  padding: 16px 0;
  border-bottom: 1px solid var(--color-neutrals-blue);
  align-items: center;
}

.wrapper > * {
  margin: 0 8px;
}

.nowrap {
  white-space: nowrap;
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
