<template>
  <div v-if="!error">
    <h2 tabindex="0">{{ t("location") }}</h2>
    <!--  Add location selection-->
    <h2 tabindex="0">{{ t("time") }}</h2>
    <muc-calendar
      v-model="selectedDay"
      variant="single"
      :allowed-dates="allowedDates"
    />
    <div class="m-component">
      <h3 tabindex="0">{{ t("availableTimes") }}</h3>
      <div style="background-color: var(--color-neutrals-blue-xlight)">
        <b tabindex="0">{{ formatDay(selectedDay) }}</b>
      </div>
      <div
        v-for="[timeslot, times] in timeSlotsInHours()"
        :key="timeslot"
      >
        <div class="wrapper">
          <div>
            <p class="centered-text">{{ timeslot }}:00-{{ timeslot }}:59</p>
          </div>
          <div class="grid">
            <div
              v-for="time in times"
              :key="time.unix"
              class="grid-item"
            >
              <muc-button
                class="timeslot"
                variant="secondary"
                @click="handleTimeSlotSelection(time)"
              >
                <template #default>{{ formatTime(time) }}</template>
              </muc-button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <muc-callout
      v-if="selectedTimeslot !== 0"
      type="info"
    >
      <template #content>
        <b>{{ t("location") }}</b>
        <br />
        <b>{{ t("time") }}</b>
        <br />
        {{ formatDay(selectedDay) }}, {{ formatTime(selectedTimeslot) }}
        {{ t("clock") }}
      </template>

      <template #header>{{ t("selectedAppointment") }}</template>
    </muc-callout>
  </div>
  <div
    v-if="error"
    class="m-component"
  >
    <muc-callout type="warning">
      <template #content>
        {{ t("noAppointmentsAvailable") }}
      </template>

      <template #header>{{ t("noAppointmentsAvailableHeader") }}</template>
    </muc-callout>
  </div>
  <div class="m-submit-group">
    <muc-button
      variant="secondary"
      @click="previousStep"
    >
      <template #default>{{ t("back") }}</template>
    </muc-button>
    <muc-button
      :disabled="!selectedTimeslot"
      @click="nextStep"
    >
      <template #default>{{ t("next") }}</template>
    </muc-button>
  </div>
</template>

<script setup lang="ts">
import {
  MucButton,
  MucCalendar,
  MucCallout,
} from "@muenchen/muc-patternlab-vue";
import { inject, onMounted, ref, watch } from "vue";

import { AvailableDaysDTO } from "@/api/models/AvailableDaysDTO";
import { AvailableTimeSlotsDTO } from "@/api/models/AvailableTimeSlotsDTO";
import {
  fetchAvailableDays,
  fetchAvailableTimeSlots,
} from "@/api/ZMSAppointmentAPI";
import { OfficeImpl } from "@/types/OfficeImpl";
import {
  SelectedServiceProvider,
  SelectedTimeslotProvider,
} from "@/types/ProvideInjectTypes";

defineProps<{
  t: any;
}>();

const emit = defineEmits<{
  (e: "next"): void;
  (e: "back"): void;
}>();

const { selectedService } = inject<SelectedServiceProvider>(
  "selectedServiceProvider"
) as SelectedServiceProvider;

const { selectedTimeslot } = inject<SelectedTimeslotProvider>(
  "selectedTimeslot"
) as SelectedTimeslotProvider;

const selectableProviders = ref<OfficeImpl[]>();
const currentProvider = ref<OfficeImpl>();
const displayInfo = ref<string>();
const selectedServices = ref<Map<string, number>>(new Map<string, number>());
const availableDays = ref<string[]>();
const appointmentTimestamps = ref<number[]>();

const selectedDay = ref<Date>();
const error = ref<boolean>(false);

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

const timeSlotsInHours = () => {
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
};

const showSelectionForProvider = (provider: OfficeImpl) => {
  currentProvider.value = provider;
  error.value = false;

  if (
    provider.scope &&
    provider.scope.displayInfo &&
    provider.scope.displayInfo.length > 0
  ) {
    displayInfo.value = provider.scope.displayInfo;
  } else {
    displayInfo.value = undefined;
  }

  fetchAvailableDays(
    currentProvider.value,
    Array.from(selectedServices.value.keys()),
    Array.from(selectedServices.value.values())
  ).then((data) => {
    if ((data as AvailableDaysDTO).availableDays !== undefined) {
      availableDays.value = (data as AvailableDaysDTO).availableDays;
      selectedDay.value = new Date(availableDays.value[0]);
      getAppointmentsOfDay(availableDays.value[0]);
    } else {
      error.value = true;
    }
  });
};

const getAppointmentsOfDay = (date: string) => {
  fetchAvailableTimeSlots(
    date,
    currentProvider.value,
    Array.from(selectedServices.value.keys()),
    Array.from(selectedServices.value.values())
  ).then((data) => {
    if (data as AvailableTimeSlotsDTO) {
      appointmentTimestamps.value = (
        data as AvailableTimeSlotsDTO
      ).appointmentTimestamps;
      timeSlotsInHours();
    } else {
      error.value = true;
    }
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
  return (
    beforeMaxDate && availableDays.value?.includes(convertDateToString(date))
  );
};

watch(selectedDay, (newDate) => {
  if (newDate) {
    getAppointmentsOfDay(convertDateToString(newDate));
  }
});

const handleTimeSlotSelection = (timeSlot: number) => {
  selectedTimeslot.value = timeSlot;
};

const nextStep = () => emit("next");
const previousStep = () => emit("back");

onMounted(() => {
  if (selectedService.value) {
    if (selectedService.value.count) {
      selectedServices.value.set(
        selectedService.value.id,
        selectedService.value.count
      );
    }

    if (selectedService.value.subServices) {
      selectedService.value.subServices.forEach((subservice) => {
        if (subservice.count > 0) {
          selectedServices.value.set(
            subservice.id.toString(),
            subservice.count
          );
        }
      });
    }

    if (selectedService.value.providers && selectedService.value.subServices) {
      const choosenSubservices = selectedService.value.subServices.filter(
        (subservice) => subservice.count > 0
      );
      selectableProviders.value = selectedService.value.providers.filter(
        (provider) => {
          return choosenSubservices.every((subservice) => {
            return subservice.providers.some(
              (subserviceProvider) => subserviceProvider.id === provider.id
            );
          });
        }
      );
      if (selectableProviders.value.length > 0)
        showSelectionForProvider(selectableProviders.value[0]);
    } else {
      showSelectionForProvider(selectedService.value.providers[0]);
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
</style>
