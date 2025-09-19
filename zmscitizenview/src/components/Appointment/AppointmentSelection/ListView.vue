<template>
  <div>
    <div class="m-content">
      <h3
        class="no-top-margin"
        tabindex="0"
      >
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
                  type="button"
                  data-bs-toggle="collapse"
                  :data-bs-target="'#listContent-' + index"
                  :aria-expanded="day.dateString === openAccordionDate"
                  :aria-controls="'#listContent-' + index"
                  @click="$emit('toggleDay', day)"
                >
                  {{ day.label }}
                  <svg
                    aria-hidden="true"
                    focusable="false"
                    class="icon"
                  >
                    <use
                      :xlink:href="
                        day.dateString === openAccordionDate
                          ? '#icon-chevron-up'
                          : '#icon-chevron-down'
                      "
                    ></use>
                  </svg>
                </button>
              </h4>

              <section
                class="m-accordion__section-content collapse"
                :class="{ show: day.dateString === openAccordionDate }"
                :id="'listContent-' + index"
                :aria-labelledby="'listHeading-' + index"
                data-bs-parent="#listViewAccordion"
              >
                <div class="m-textplus__content">
                  <template
                    v-if="
                      isLoadingAppointments &&
                      day.dateString === openAccordionDate
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
                      <AppointmentLayout
                        v-if="
                          hourRow.hour ===
                            getCurrentHourForDay(day.dateString) ||
                          providersWithAppointments.length === 1
                        "
                        :officeId="hourRow.officeId"
                        :times="hourRow.times"
                        :timeLabel="`${hourRow.hour}:00‑${hourRow.hour}:59`"
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
                        :officeName="officeName"
                        :isSlotSelected="isSlotSelected"
                        :formatTime="formatTime"
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
                        @click="$emit('earlier', { day, type: 'hour' })"
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
                        @click="$emit('later', { day, type: 'hour' })"
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
                      <AppointmentLayout
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
                        :officeName="officeName"
                        :isSlotSelected="isSlotSelected"
                        :formatTime="formatTime"
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
                        @click="$emit('earlier', { day, type: 'dayPart' })"
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
                        @click="$emit('later', { day, type: 'dayPart' })"
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
      @click="$emit('loadMore')"
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

import AppointmentLayout from "./AppointmentLayout.vue";

const props = defineProps<{
  t: (key: string) => string;
  firstFiveAvailableDays: AccordionDay[];
  openAccordionDate: string | null;
  isLoadingAppointments: boolean;
  availabilityInfoHtml: string | null;
  selectableProviders: OfficeImpl[] | undefined;
  selectedProviders: { [id: string]: boolean };
  providersWithAppointments: OfficeImpl[];
  APPOINTMENTS_THRESHOLD_FOR_HOURLY_VIEW: number;
  canLoadMore: boolean;
  officeName: (id: number | string) => string | null;
  getCurrentHourForDay: (dateString: string) => number | undefined;
  getCurrentDayPartForDay: (dateString: string) => "am" | "pm" | undefined;
  getListDayAvailableHours: (day: AccordionDay) => number[];
  getListDayAvailableDayParts: (day: AccordionDay) => ("am" | "pm")[];
  isSlotSelected: (officeId: number | string, time: number) => boolean;
  formatTime: (time: number) => string;
}>();

defineEmits<{
  (e: "toggleDay", day: AccordionDay): void;
  (
    e: "selectTimeSlot",
    payload: { officeId: number | string; time: number }
  ): void;
  (
    e: "earlier",
    payload: { day: AccordionDay; type: "hour" | "dayPart" }
  ): void;
  (e: "later", payload: { day: AccordionDay; type: "hour" | "dayPart" }): void;
  (e: "loadMore"): void;
  (e: "openInfo"): void;
}>();

const t = props.t;
const officeName = props.officeName;
const getCurrentHourForDay = props.getCurrentHourForDay;
const getCurrentDayPartForDay = props.getCurrentDayPartForDay;
const getListDayAvailableHours = props.getListDayAvailableHours;
const getListDayAvailableDayParts = props.getListDayAvailableDayParts;
const isSlotSelected = props.isSlotSelected;
const formatTime = props.formatTime as (time: number) => string;
</script>
