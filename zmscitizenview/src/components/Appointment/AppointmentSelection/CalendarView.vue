<template>
  <div>
    <!-- Calendar Component -->
    <div class="m-component">
      <muc-calendar
        :key="calendarKey"
        :model-value="selectedDay"
        @update:model-value="
          (date) => $emit('update:selectedDay', date as Date)
        "
        disable-view-change
        variant="single"
        :allowed-dates="allowedDates"
        :min="minDate"
        :max="maxDate"
        :view-month="viewMonth"
      />
    </div>

    <!-- Hourly View (when appointments > threshold) -->
    <div
      v-if="
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
        <h3 class="no-top-margin">
          {{ t("availableTimes") }}
        </h3>
      </div>
      <div
        class="m-content"
        style="margin: 8px 0 0 0"
        v-if="availabilityInfoHtml"
      >
        <muc-button
          variant="ghost"
          icon="information"
          icon-shown-left
          @click="$emit('openInfo')"
        >
          <template #default>{{ t("newAppointmentsInfoLink") }}</template>
        </muc-button>
      </div>
      <div
        style="
          margin-bottom: 20px;
          background-color: var(--color-neutrals-blue-xlight);
          padding: 12px 8px;
        "
      >
        <h4>{{ formatDayFromDate(selectedDay) }}</h4>
      </div>

      <div
        v-if="isLoadingAppointments && !isLoadingComplete"
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
          <template
            v-for="[timeslot, times] in office.appointments"
            :key="timeslot"
          >
            <TimeSlotGrid
              v-if="
                timeslot == currentHour ||
                providersWithAppointments.length === 1
              "
              :officeId="officeId"
              :times="times"
              :timeLabel="`${timeslot}:00-${timeslot}:59`"
              :showLocationTitle="(selectableProviders?.length || 0) > 1"
              :officeNameById="officeNameById"
              :isSlotSelected="isSlotSelected"
              @selectTimeSlot="$emit('selectTimeSlot', $event)"
            />
          </template>
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
          @click="onEarlier('hour')"
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
          @click="onLater('hour')"
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

    <!-- Day Part View (when appointments <= threshold) -->
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
        <h3 class="no-top-margin">
          {{ t("availableTimes") }}
        </h3>
      </div>
      <div
        class="m-content"
        style="margin: 8px 0 0 0"
        v-if="availabilityInfoHtml"
      >
        <muc-button
          variant="ghost"
          icon="information"
          icon-shown-left
          @click="$emit('openInfo')"
        >
          <template #default>{{ t("newAppointmentsInfoLink") }}</template>
        </muc-button>
      </div>
      <div
        style="
          margin-bottom: 20px;
          background-color: var(--color-neutrals-blue-xlight);
          padding: 12px 8px;
        "
      >
        <h4>{{ formatDayFromDate(selectedDay) }}</h4>
      </div>

      <div
        v-if="isLoadingAppointments && !isLoadingComplete"
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
        :key="String(officeId) + String(selectedProviders[officeId])"
      >
        <div
          v-if="
            selectedProviders[officeId] &&
            office.appointments.get(currentDayPart)
          "
        >
          <template
            v-for="[timeslot, times] in office.appointments"
            :key="timeslot"
          >
            <TimeSlotGrid
              v-if="
                timeslot == currentDayPart ||
                providersWithAppointments.length === 1
              "
              :officeId="officeId"
              :times="times"
              :timeLabel="t(timeslot)"
              :showLocationTitle="(selectableProviders?.length || 0) > 1"
              :officeNameById="officeNameById"
              :isSlotSelected="isSlotSelected"
              @selectTimeSlot="$emit('selectTimeSlot', $event)"
            />
          </template>
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
          @click="onEarlier('dayPart')"
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
          @click="onLater('dayPart')"
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
  </div>
</template>

<script setup lang="ts">
import type { OfficeImpl } from "@/types/OfficeImpl";

import { MucButton, MucCalendar } from "@muenchen/muc-patternlab-vue";
import { nextTick } from "vue";

import { APPOINTMENTS_THRESHOLD_FOR_HOURLY_VIEW } from "@/utils/Constants";
import { formatDayFromDate } from "@/utils/formatAppointmentDateTime";
import TimeSlotGrid from "./TimeSlotGrid.vue";

const props = defineProps<{
  t: (key: string) => string;
  selectedDay: Date | undefined;
  calendarKey: number;
  allowedDates: (date: Date) => boolean;
  minDate: Date | undefined;
  maxDate: Date | undefined;
  viewMonth: Date;
  timeSlotsInHoursByOffice: Map<
    number,
    { appointments: Map<number, number[]> }
  >;
  timeSlotsInDayPartByOffice: Map<
    number,
    { appointments: Map<string, number[]> }
  >;
  currentHour: number | null;
  firstHour: number | null;
  lastHour: number | null;
  currentDayPart: "am" | "pm";
  firstDayPart: "am" | "pm";
  lastDayPart: "am" | "pm";
  selectableProviders: OfficeImpl[] | undefined;
  selectedProviders: { [id: string]: boolean };
  providersWithAppointments: OfficeImpl[];
  appointmentsCount: number;
  isLoadingAppointments: boolean;
  isLoadingComplete: boolean;
  availabilityInfoHtml: string | null;
  officeNameById: (id: number | string) => string | null;
  isSlotSelected: (officeId: number | string, time: number) => boolean;
}>();

const emit = defineEmits<{
  (e: "update:selectedDay", day: Date): void;
  (
    e: "selectTimeSlot",
    payload: { officeId: number | string; time: number }
  ): void;
  (e: "openInfo"): void;
  (e: "setSelectedHour", hour: number | null): void;
  (e: "setSelectedDayPart", part: "am" | "pm" | null): void;
}>();

async function snapToNearestForCurrentSelection() {
  await nextTick();

  // Hourly view: snap selectedHour to the nearest available hour if current is not available
  if (props.timeSlotsInHoursByOffice.size > 0) {
    const availableHours = Array.from(props.timeSlotsInHoursByOffice.values())
      .flatMap((office) => Array.from((office as any).appointments.keys()))
      .filter((hour): hour is number => typeof hour === "number");
    if (
      props.currentHour === null ||
      !availableHours.includes(props.currentHour as number)
    ) {
      if (availableHours.length > 0) {
        const prevHour = props.currentHour ?? availableHours[0];
        let nearest = availableHours[0];
        let minDiff = Math.abs(prevHour - nearest);
        for (const hour of availableHours) {
          const diff = Math.abs(prevHour - hour);
          if (diff < minDiff || (diff === minDiff && hour < nearest)) {
            nearest = hour;
            minDiff = diff;
          }
        }
        // request parent to update
        emit("setSelectedHour", nearest);
      } else {
        emit("setSelectedHour", null);
      }
    }
  }
  // DayPart view: snap selectedDayPart if current is not available
  else if (props.timeSlotsInDayPartByOffice.size > 0) {
    const availableDayParts = Array.from(
      props.timeSlotsInDayPartByOffice.values()
    )
      .flatMap((office) => Array.from((office as any).appointments.keys()))
      .filter((part): part is "am" | "pm" => part === "am" || part === "pm");
    if (
      !props.currentDayPart ||
      !availableDayParts.includes(props.currentDayPart as "am" | "pm")
    ) {
      let newPart: "am" | "pm" | null = null;
      if (props.currentDayPart === "am" && availableDayParts.includes("pm")) {
        newPart = "pm";
      } else if (
        props.currentDayPart === "pm" &&
        availableDayParts.includes("am")
      ) {
        newPart = "am";
      } else if (availableDayParts.length > 0) {
        newPart = availableDayParts[0];
      }
      emit("setSelectedDayPart", newPart);
    }
  }
}

defineExpose({ snapToNearest: snapToNearestForCurrentSelection });

function getAvailableHours(): number[] {
  const hourSet = new Set<number>();
  for (const [, office] of props.timeSlotsInHoursByOffice) {
    for (const hour of office.appointments.keys()) {
      if ((office.appointments.get(hour) || []).length > 0) {
        hourSet.add(hour);
      }
    }
  }
  return Array.from(hourSet).sort((a, b) => a - b);
}

function onEarlier(type: "hour" | "dayPart") {
  if (type === "dayPart") {
    if (props.currentDayPart === "pm") emit("setSelectedDayPart", "am");
    return;
  }
  const hours = getAvailableHours();
  const current = props.currentHour;
  if (current === null) return;
  const idx = hours.indexOf(current);
  if (idx > 0) emit("setSelectedHour", hours[idx - 1]);
}

function onLater(type: "hour" | "dayPart") {
  if (type === "dayPart") {
    if (props.currentDayPart === "am") emit("setSelectedDayPart", "pm");
    return;
  }
  const hours = getAvailableHours();
  const current = props.currentHour;
  if (current === null) return;
  const idx = hours.indexOf(current);
  if (idx >= 0 && idx < hours.length - 1)
    emit("setSelectedHour", hours[idx + 1]);
}
</script>
<style lang="scss" scoped>
.m-button--ghost.disabled,
.m-button--ghost:disabled {
  background: #fff;
  border-color: #fff;
}

.disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.float-right {
  margin-left: auto;
  margin-right: 0 !important;
}

.m-button-group {
  margin-bottom: 20px;
  padding-bottom: 0;
  padding-top: 30px;
}

/* Ensure consistent width for earlier/later buttons */
.m-button-group .muc-button[icon-shown-left],
.m-button-group .muc-button[icon-shown-right] {
  min-width: 100px !important;
}

.m-button-group button .m-button__icon--before,
.m-button-group button .m-button__icon--after {
  min-width: 100px !important;
}

.m-button-group button:has(.m-button__icon--before),
.m-button-group button:has(.m-button__icon--after) {
  min-width: 100px !important;
}

/* Remove focus effects from navigation buttons */
.m-button-group button:focus {
  outline: none !important;
  box-shadow: none !important;
  border: none !important;
}

.m-button-group button:focus-visible {
  outline: none !important;
  box-shadow: none !important;
}

.no-top-margin,
.no-top-margin h3 {
  margin-top: 0 !important;
}
</style>
