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
        <h3
          class="no-top-margin"
          tabindex="0"
        >
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
          <template
            v-for="[timeslot, times] in office.appointments"
            :key="timeslot"
          >
            <AppointmentLayout
              v-if="
                timeslot == currentHour ||
                providersWithAppointments.length === 1
              "
              :officeId="officeId"
              :times="times"
              :timeLabel="
                firstHour !== null && firstHour > 0
                  ? `${timeslot}:00-${timeslot}:59`
                  : ''
              "
              :showLocationTitle="(selectableProviders?.length || 0) > 1"
              :officeName="officeName"
              :isSlotSelected="isSlotSelected"
              :formatTime="formatTime"
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
          @click="$emit('earlier', { type: 'hour' })"
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
          @click="$emit('later', { type: 'hour' })"
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
        <h3
          class="no-top-margin"
          tabindex="0"
        >
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
            <AppointmentLayout
              v-if="
                timeslot == currentDayPart ||
                providersWithAppointments.length === 1
              "
              :officeId="officeId"
              :times="times"
              :timeLabel="t(timeslot)"
              :showLocationTitle="(selectableProviders?.length || 0) > 1"
              :officeName="officeName"
              :isSlotSelected="isSlotSelected"
              :formatTime="formatTime"
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
          @click="$emit('earlier', { type: 'dayPart' })"
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
          @click="$emit('later', { type: 'dayPart' })"
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

import AppointmentLayout from "./AppointmentLayout.vue";

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
  APPOINTMENTS_THRESHOLD_FOR_HOURLY_VIEW: number;
  isLoadingAppointments: boolean;
  isLoadingComplete: boolean;
  availabilityInfoHtml: string | null;
  officeName: (id: number | string) => string | null;
  isSlotSelected: (officeId: number | string, time: number) => boolean;
  formatTime: (time: number) => string;
  formatDay: (date: Date) => string | undefined;
}>();

defineEmits<{
  (e: "update:selectedDay", day: Date): void;
  (
    e: "selectTimeSlot",
    payload: { officeId: number | string; time: number }
  ): void;
  (e: "earlier", payload: { type: "hour" | "dayPart" }): void;
  (e: "later", payload: { type: "hour" | "dayPart" }): void;
  (e: "openInfo"): void;
}>();
</script>

<style lang="scss" scoped>
.no-top-margin,
.no-top-margin h3 {
  margin-top: 0 !important;
}

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
</style>
