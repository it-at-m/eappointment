<template>
  <div
    v-if="
      selectedProvider && selectableProviders && selectableProviders.length > 1
    "
  >
    <div class="m-component slider-no-margin">
      <div class="m-content">
        <h2 tabindex="0">{{ t("location") }}</h2>
      </div>
      <muc-slider @change-slide="handleProviderSelection">
        <muc-slider-item
          v-for="proverider in selectableProviders"
          :key="proverider.id"
        >
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
                  {{ proverider.name }}
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
                      {{ proverider.address.street }}
                      {{ proverider.address.house_number }}
                    </span>
                  </p>
                </div>
              </div>
            </div>
          </div>
        </muc-slider-item>
      </muc-slider>
    </div>
  </div>
  <div v-if="!error">
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
    <div class="m-content">
      <h2 tabindex="0">{{ t("time") }}</h2>
    </div>
    <div class="m-component">
      <muc-calendar
        v-model="selectedDay"
        disable-view-change
        variant="single"
        :allowed-dates="allowedDates"
        :min="minDate"
        :max="maxDate"
        :view-month="minDate"
      />
    </div>
    <div
      v-if="selectedDay && timeSlotsInHours.size > 0"
      class="m-component"
    >
      <div class="m-content">
        <h3 tabindex="0">{{ t("availableTimes") }}</h3>
      </div>
      <div style="background-color: var(--color-neutrals-blue-xlight)">
        <b tabindex="0">{{ formatDay(selectedDay) }}</b>
      </div>
      <div
        v-for="[timeslot, times] in timeSlotsInHours"
        :key="timeslot"
      >
        <div class="wrapper">
          <div>
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
                @click="handleTimeSlotSelection(time)"
              >
                <template #default>{{ formatTime(time) }}</template>
              </muc-button>
            </div>
          </div>
        </div>
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
            <p class="m-teaser-contained-contact__detail">
              {{ selectedProvider.scope.displayInfo }}
            </p>
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
      :disabled="selectedTimeslot === 0 || !selectedDay"
      icon="arrow-right"
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
  MucSlider,
  MucSliderItem,
} from "@muenchen/muc-patternlab-vue";
import { computed, inject, nextTick, onMounted, ref, watch } from "vue";

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

const selectableProviders = ref<OfficeImpl[]>();
const availableDays = ref<string[]>();
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
  appointmentTimestamps.value.forEach((time) => {
    const berlinDate = new Date(time * 1000);
    const hour = parseInt(berlinHourFormatter.format(berlinDate));
    if (!timesByHours.has(hour)) {
      timesByHours.set(hour, []);
    }
    timesByHours.get(hour)?.push(time);
  });
  return timesByHours;
});

const showSelectionForProvider = (provider: OfficeImpl) => {
  selectedProvider.value = provider;
  error.value = false;
  selectedDay.value = undefined;
  selectedTimeslot.value = 0;

  fetchAvailableDays(
    selectedProvider.value,
    Array.from(props.selectedServiceMap.keys()),
    Array.from(props.selectedServiceMap.values()),
    props.baseUrl ?? undefined,
    props.captchaToken ?? undefined
  ).then((data) => {
    const days = (data as AvailableDaysDTO)?.availableDays;
    if (Array.isArray(days) && days.length > 0) {
      availableDays.value = days;
      selectedDay.value = new Date(days[0]);
      minDate.value = new Date(days[0]);
      maxDate.value = new Date(days[days.length - 1]);
      getAppointmentsOfDay(days[0]);
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
  appointmentTimestamps.value = [];
  fetchAvailableTimeSlots(
    date,
    selectedProvider.value,
    Array.from(props.selectedServiceMap.keys()),
    Array.from(props.selectedServiceMap.values()),
    props.baseUrl ?? undefined,
    props.captchaToken ?? undefined
  ).then((data) => {
    if (data as AvailableTimeSlotsDTO) {
      appointmentTimestamps.value = (
        data as AvailableTimeSlotsDTO
      ).appointmentTimestamps;
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
  selectedTimeslot.value = 0;
  if (newDate && newDate != selectedDay.value) {
    getAppointmentsOfDay(convertDateToString(newDate));
  }
});

const handleProviderSelection = (id: number) => {
  showSelectionForProvider(selectableProviders.value[id]);
};

const handleTimeSlotSelection = async (timeSlot: number) => {
  selectedTimeslot.value = timeSlot;
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
    // Checks whether a provider is already selected so that it is displayed first in the slider.
    let offices = selectedService.value.providers.filter((office) => {
      if (props.preselectedOfficeId) {
        return office.id == props.preselectedOfficeId;
      } else if (selectedProvider.value) {
        return office.id == selectedProvider.value.id;
      }
    });

    // Checks whether there are restrictions on the providers due to the subservices.
    if (selectedService.value.subServices) {
      const choosenSubservices = selectedService.value.subServices.filter(
        (subservice) => subservice.count > 0
      );
      selectableProviders.value = selectedService.value.providers.filter(
        (provider) => {
          return choosenSubservices.every((subservice) => {
            return subservice.providers.some(
              (subserviceProvider) => subserviceProvider.id == provider.id
            );
          });
        }
      );
    } else {
      selectableProviders.value = selectedService.value.providers;
    }

    // If alternative locations are allowed to be selected, they will be added to the slider.
    if (
      !props.exclusiveLocation &&
      ((offices.length > 0 && offices[0].showAlternativeLocations) ||
        offices.length == 0)
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
      selectableProviders.value = offices;
    }

    showSelectionForProvider(offices[0]);
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

<style>
.slider-no-margin .m-component__column {
  margin: 0 !important;
}
</style>
