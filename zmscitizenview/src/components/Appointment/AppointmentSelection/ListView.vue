<template>
  <div>
    <div class="m-content">
      <h3 class="no-top-margin">
        {{ t("availableTimes") }}
      </h3>
      <div
        class="m-content"
        style="margin-top: 8px"
        v-if="availabilityInfoHtml"
      >
        <muc-button
          variant="ghost"
          icon="information"
          icon-shown-left
          class="no-bottom-margin"
          @click="$emit('openInfo')"
        >
          <template #default>{{ t("newAppointmentsInfoLink") }}</template>
        </muc-button>
      </div>
    </div>

    <div class="m-component m-component-accordion">
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
              <h4
                style="
                  margin-bottom: 20px;
                  background-color: var(--color-neutrals-blue-xlight);
                "
                class="m-accordion__section-header"
                :id="'listHeading-' + index"
              >
                <button
                  class="m-accordion__section-button"
                  style="padding: 12px 8px"
                  type="button"
                  data-bs-toggle="collapse"
                  :data-bs-target="'#listContent-' + index"
                  :aria-expanded="day.dateString === localOpenAccordionDate"
                  :aria-controls="'#listContent-' + index"
                  @click="onToggleDay(day)"
                >
                  {{ day.label }}
                  <svg
                    aria-hidden="true"
                    focusable="false"
                    class="icon"
                  >
                    <use
                      :xlink:href="
                        day.dateString === localOpenAccordionDate
                          ? '#icon-chevron-up'
                          : '#icon-chevron-down'
                      "
                    ></use>
                  </svg>
                </button>
              </h4>

              <section
                class="m-accordion__section-content collapse"
                :class="{ show: day.dateString === localOpenAccordionDate }"
                :id="'listContent-' + index"
                :aria-labelledby="'listHeading-' + index"
                data-bs-parent="#listViewAccordion"
              >
                <div class="m-textplus__content">
                  <template
                    v-if="
                      isLoadingAppointments &&
                      day.dateString === localOpenAccordionDate
                    "
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
                      <TimeSlotGrid
                        v-if="
                          hourRow.hour ===
                            getCurrentHourForDay(day.dateString) ||
                          providersWithAppointments.length === 1
                        "
                        :officeId="hourRow.officeId"
                        :times="hourRow.times"
                        :timeLabel="`${hourRow.hour}:00-${hourRow.hour}:59`"
                        :showLocationTitle="
                          (selectableProviders?.length || 0) > 1 &&
                          hourRow.hour ===
                            getCurrentHourForDay(day.dateString) &&
                          hourRow.times.length > 0 &&
                          (hIndex === 0 ||
                            day.hourRows[hIndex - 1].officeId !==
                              hourRow.officeId ||
                            day.hourRows[hIndex - 1].hour !== hourRow.hour)
                        "
                        :officeNameById="officeNameById"
                        :isSlotSelected="isSlotSelected"
                        :t="t"
                        @selectTimeSlot="$emit('selectTimeSlot', $event)"
                      />
                    </template>

                    <div
                      v-if="
                        day.hourRows.length > 0 &&
                        providersWithAppointments.length > 1
                      "
                      class="wrapper m-button-group"
                    >
                      <muc-button
                        icon="chevron-left"
                        icon-shown-left
                        variant="ghost"
                        @click="onEarlier(day, 'hour')"
                        :disabled="
                          getCurrentHourForDay(day.dateString) === undefined ||
                          getListDayAvailableHours(day).indexOf(
                            getCurrentHourForDay(day.dateString) ?? -1
                          ) <= 0
                        "
                      >
                        <template #default>{{ t("earlier") }}</template>
                      </muc-button>

                      <muc-button
                        class="float-right"
                        icon="chevron-right"
                        icon-shown-right
                        variant="ghost"
                        @click="onLater(day, 'hour')"
                        :disabled="
                          getCurrentHourForDay(day.dateString) === undefined ||
                          getListDayAvailableHours(day).indexOf(
                            getCurrentHourForDay(day.dateString) ?? -1
                          ) >=
                            getListDayAvailableHours(day).length - 1
                        "
                      >
                        <template #default>{{ t("later") }}</template>
                      </muc-button>
                    </div>
                  </template>

                  <template v-else>
                    <template
                      v-for="(partRow, pIndex) in day.dayPartRows"
                      :key="pIndex"
                    >
                      <TimeSlotGrid
                        v-if="
                          partRow.part ===
                            getCurrentDayPartForDay(day.dateString) ||
                          providersWithAppointments.length === 1
                        "
                        :officeId="partRow.officeId"
                        :times="partRow.times"
                        :timeLabel="t(partRow.part)"
                        :showLocationTitle="
                          (selectableProviders?.length || 0) > 1 &&
                          partRow.part ===
                            getCurrentDayPartForDay(day.dateString) &&
                          partRow.times.length > 0 &&
                          (pIndex === 0 ||
                            day.dayPartRows[pIndex - 1].officeId !==
                              partRow.officeId ||
                            day.dayPartRows[pIndex - 1].part !== partRow.part)
                        "
                        :officeNameById="officeNameById"
                        :isSlotSelected="isSlotSelected"
                        :t="t"
                        @selectTimeSlot="$emit('selectTimeSlot', $event)"
                      />
                    </template>

                    <div
                      v-if="
                        day.dayPartRows.length > 0 &&
                        providersWithAppointments.length > 1
                      "
                      class="wrapper m-button-group"
                    >
                      <muc-button
                        icon="chevron-left"
                        icon-shown-left
                        variant="ghost"
                        @click="onEarlier(day, 'dayPart')"
                        :disabled="
                          getCurrentDayPartForDay(day.dateString) === 'am' ||
                          getListDayAvailableDayParts(day).indexOf('am') === -1
                        "
                      >
                        <template #default>{{ t("earlier") }}</template>
                      </muc-button>

                      <muc-button
                        class="float-right"
                        icon="chevron-right"
                        icon-shown-right
                        variant="ghost"
                        @click="onLater(day, 'dayPart')"
                        :disabled="
                          getCurrentDayPartForDay(day.dateString) === 'pm' ||
                          getListDayAvailableDayParts(day).indexOf('pm') === -1
                        "
                      >
                        <template #default>{{ t("later") }}</template>
                      </muc-button>
                    </div>
                  </template>
                </div>
              </section>
            </div>
          </template>
        </div>
      </div>
    </div>

    <muc-button
      v-if="canLoadMore"
      @click="loadMoreDays"
      icon="chevron-down"
      icon-animated
      style="margin-top: 16px"
    >
      <template #default>{{ t("loadMore") }}</template>
    </muc-button>
  </div>
</template>

<script setup lang="ts">
import type { AccordionDay } from "@/types/AccordionDay";
import type { OfficeImpl } from "@/types/OfficeImpl";

import { MucButton } from "@muenchen/muc-patternlab-vue";
import { computed, nextTick, ref, watch } from "vue";

import { APPOINTMENTS_THRESHOLD_FOR_HOURLY_VIEW } from "@/utils/Constants";
import {
  berlinHourFormatter,
  convertDateToString,
  formatDayFromDate,
  formatterWeekday,
  formatTimeFromUnix,
} from "@/utils/formatAppointmentDateTime";
import TimeSlotGrid from "./TimeSlotGrid.vue";

const props = defineProps<{
  t: (key: string) => string;
  isLoadingAppointments: boolean;
  availabilityInfoHtml: string | null;
  selectableProviders: OfficeImpl[] | undefined;
  selectedProviders: { [id: string]: boolean };
  providersWithAppointments: OfficeImpl[];
  officeNameById: (id: number | string) => string | null;
  isSlotSelected: (officeId: number | string, time: number) => boolean;
  // New raw data to compute firstFiveAvailableDays inside this component
  availableDays:
    | Array<{ time: string | number; providerIDs: string }>
    | undefined;
  datesWithoutAppointments: Set<string>;
  appointmentTimestampsByOffice: Array<{
    officeId: number | string;
    appointments: number[];
  }>;
  officeOrder: Map<number, number>;
}>();

const emit = defineEmits<{
  (
    e: "selectTimeSlot",
    payload: { officeId: number | string; time: number }
  ): void;
  (
    e: "earlier",
    payload: { day: AccordionDay; type: "hour" | "dayPart" }
  ): void;
  (e: "later", payload: { day: AccordionDay; type: "hour" | "dayPart" }): void;
  (e: "openInfo"): void;
  (e: "update:selectedDay", date: Date): void;
}>();

const t = props.t;
const officeNameById = props.officeNameById;
const isSlotSelected = props.isSlotSelected;

const daysToShow = ref(5);
const localOpenAccordionDate = ref<string | null>(null);

const listViewCurrentHour = ref<Map<string, number>>(new Map());
const listViewCurrentDayPart = ref<Map<string, "am" | "pm">>(new Map());
const getCurrentHourForDay = (dateString: string): number | undefined => {
  return listViewCurrentHour.value.get(dateString);
};

const getCurrentDayPartForDay = (
  dateString: string
): "am" | "pm" | undefined => {
  return listViewCurrentDayPart.value.get(dateString);
};

function setCurrentHourForDay(dateString: string, hour: number) {
  const m = new Map(listViewCurrentHour.value);
  m.set(dateString, hour);
  listViewCurrentHour.value = m;
}

function setCurrentDayPartForDay(dateString: string, part: "am" | "pm") {
  const m = new Map(listViewCurrentDayPart.value);
  m.set(dateString, part);
  listViewCurrentDayPart.value = m;
}

function getListDayAvailableHours(day: AccordionDay) {
  const hourSet = new Set<number>();
  day.hourRows.forEach((hourRow) => {
    if (hourRow.times.length > 0) {
      hourSet.add(hourRow.hour);
    }
  });
  return Array.from(hourSet).sort((a, b) => a - b);
}

function getListDayAvailableDayParts(day: AccordionDay) {
  const dayParts: ("am" | "pm")[] = [];
  day.dayPartRows.forEach((partRow) => {
    if (partRow.times.length > 0) {
      dayParts.push(partRow.part);
    }
  });
  return dayParts.sort((a, b) => (a === "am" ? -1 : 1));
}

function onEarlier(day: AccordionDay, type: "hour" | "dayPart") {
  if (type === "hour") {
    const hours = getListDayAvailableHours(day);
    const current = getCurrentHourForDay(day.dateString);
    if (current === undefined) return;
    const idx = hours.indexOf(current);
    if (idx > 0) setCurrentHourForDay(day.dateString, hours[idx - 1]);
  } else {
    const parts = getListDayAvailableDayParts(day);
    const current = getCurrentDayPartForDay(day.dateString);
    if (!current) return;
    if (current === "pm" && parts.includes("am"))
      setCurrentDayPartForDay(day.dateString, "am");
  }
}

function onLater(day: AccordionDay, type: "hour" | "dayPart") {
  if (type === "hour") {
    const hours = getListDayAvailableHours(day);
    const current = getCurrentHourForDay(day.dateString);
    if (current === undefined) return;
    const idx = hours.indexOf(current);
    if (idx >= 0 && idx < hours.length - 1)
      setCurrentHourForDay(day.dateString, hours[idx + 1]);
  } else {
    const parts = getListDayAvailableDayParts(day);
    const current = getCurrentDayPartForDay(day.dateString);
    if (!current) return;
    if (current === "am" && parts.includes("pm"))
      setCurrentDayPartForDay(day.dateString, "pm");
  }
}

function loadMoreDays() {
  daysToShow.value += 3;
}

async function snapToNearestForCurrentSelection() {
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
              props.selectedProviders[String(hourRow.officeId)]
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
              setCurrentHourForDay(dateString, nearest);
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
                props.selectedProviders[String(partRow.officeId)]
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
              setCurrentDayPartForDay(dateString, newDayPart);
            }
          }
        }
      }
    }
  }
}

defineExpose({ snapToNearest: snapToNearestForCurrentSelection });

function onToggleDay(day: AccordionDay) {
  if (localOpenAccordionDate.value === day.dateString) {
    localOpenAccordionDate.value = null;
  } else {
    localOpenAccordionDate.value = day.dateString;
    emit("update:selectedDay", day.date);
    const availableHours = getListDayAvailableHours(day);
    if (availableHours.length > 0) {
      setCurrentHourForDay(day.dateString, availableHours[0]);
    }
    const availableDayParts = getListDayAvailableDayParts(day);
    if (availableDayParts.length > 0) {
      setCurrentDayPartForDay(day.dateString, availableDayParts[0]);
    }
  }
}

const firstFiveAvailableDays = computed<AccordionDay[]>(() => {
  if (!props.availableDays) return [];

  const availableForProviders = props.availableDays.filter((day) =>
    day.providerIDs.split(",").some((id) => props.selectedProviders[id])
  );

  const trulyAvailable = availableForProviders.filter((day) => {
    const dateStr = convertDateToString(new Date(day.time));
    return !props.datesWithoutAppointments.has(dateStr);
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

    props.appointmentTimestampsByOffice.forEach((office) => {
      if (!props.selectedProviders[office.officeId as any]) return;

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
          officeId: Number(office.officeId as any),
        });
      });
      if (byPart.am.length) {
        dayPartRows.push({
          part: "am",
          times: byPart.am,
          officeId: Number(office.officeId as any),
        });
      }
      if (byPart.pm.length) {
        dayPartRows.push({
          part: "pm",
          times: byPart.pm,
          officeId: Number(office.officeId as any),
        });
      }
    });

    // hourRows: first by hour, then by provider order (officeOrder)
    hourRows.sort((hourRowLeft, hourRowRight) => {
      if (hourRowLeft.hour !== hourRowRight.hour) {
        return hourRowLeft.hour - hourRowRight.hour;
      }
      const left =
        props.officeOrder.get(Number(hourRowLeft.officeId)) ??
        Number.MAX_SAFE_INTEGER;
      const right =
        props.officeOrder.get(Number(hourRowRight.officeId)) ??
        Number.MAX_SAFE_INTEGER;
      return left - right;
    });

    // dayPartRows: first AM before PM, then by provider order (officeOrder)
    dayPartRows.sort((dayPartRowLeft, dayPartRowRight) => {
      if (dayPartRowLeft.part !== dayPartRowRight.part) {
        return dayPartRowLeft.part === "am" ? -1 : 1;
      }
      const left =
        props.officeOrder.get(Number(dayPartRowLeft.officeId)) ??
        Number.MAX_SAFE_INTEGER;
      const right =
        props.officeOrder.get(Number(dayPartRowRight.officeId)) ??
        Number.MAX_SAFE_INTEGER;
      return left - right;
    });

    if (!listViewCurrentHour.value.has(dateString)) {
      const availableHours = getListDayAvailableHours({
        hourRows,
        dayPartRows,
      } as AccordionDay);
      if (availableHours.length > 0) {
        setCurrentHourForDay(dateString, availableHours[0]);
      }
    }

    if (!listViewCurrentDayPart.value.has(dateString)) {
      const availableDayParts = getListDayAvailableDayParts({
        hourRows,
        dayPartRows,
      } as AccordionDay);
      if (availableDayParts.length > 0) {
        setCurrentDayPartForDay(dateString, availableDayParts[0]);
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
  if (newDays.length > 0 && !localOpenAccordionDate.value) {
    onToggleDay(newDays[0]);
  }
});

const canLoadMore = computed(() => {
  if (!props.availableDays) return false;
  const availableForProviders = props.availableDays.filter((day) =>
    day.providerIDs.split(",").some((id) => props.selectedProviders[id])
  );
  const trulyAvailableCount = availableForProviders.filter((day) => {
    const dateStr = convertDateToString(new Date(day.time));
    return !props.datesWithoutAppointments.has(dateStr);
  }).length;
  return firstFiveAvailableDays.value.length < trulyAvailableCount;
});
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

.no-bottom-margin,
.no-bottom-margin.m-button,
.no-bottom-margin .m-button {
  margin-bottom: 0 !important;
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
