<template>
  <div class="m-content">
    <h2 tabindex="0">{{ t("service") }}</h2>
  </div>
  <div
    v-if="!service"
    :hidden="!!preselectedServiceId"
    class="m-component"
    style="background-color: var(--color-neutrals-blue-xlight)"
  >
    <div class="container">
      <form class="m-form m-form--default">
        <muc-select
          id="service-search"
          v-model="service"
          :items="services"
          item-title="name"
          :label="t('serviceSearch')"
          :no-item-found-message="t('noServiceFound')"
        />
      </form>
      <p>{{ t("oftenSearchedService") }}</p>
    </div>
  </div>
  <div v-else>
    <div class="m-component">
      <muc-counter
        v-model="countOfService"
        :label="service.name"
        :link="getServiceBaseURL() + service.id"
        :max="maxValueOfService"
        min="1"
      />
    </div>
    <div v-if="service.subServices && service.subServices.length > 0">
      <h3 tabindex="0">{{ t("combinableServices") }}</h3>
      <p
        class="visually-hidden"
        tabindex="0"
        @click="skipSubservices"
      >
        {{ t("skipCombinableServices") }}
      </p>
      <div class="m-listing">
        <ul class="m-listing__list">
          <template
            v-for="subService in service.subServices"
            :key="subService.id"
          >
            <subservice-list-item
              :sub-service="subService"
              :current-slots="currentSlots"
              :max-slots-per-appointment="maxSlotsPerAppointment"
              @change="changeAppointmentCountOfSubservice"
            />
          </template>
        </ul>
      </div>
    </div>
    <div class="m-component">
      <div class="wrapper">
        <clock-svg />
        <div
          ref="durationInfo"
          tabindex="0"
        >
          <b>{{ t("estimatedDuration") }}</b>
          <br />
          {{ t("minutes") }}
        </div>
      </div>
    </div>
  </div>
  <div class="m-button-group">
    <muc-button
      v-if="service"
      icon="arrow-right"
      @click="nextStep"
    >
      <template #default>{{ t("next") }}</template>
    </muc-button>
  </div>
</template>

<script setup lang="ts">
import { MucButton, MucCounter, MucSelect } from "@muenchen/muc-patternlab-vue";
import { computed, inject, onMounted, ref, watch } from "vue";

import { Office } from "@/api/models/Office";
import { Relation } from "@/api/models/Relation";
import { Service } from "@/api/models/Service";
import { fetchServicesAndProviders } from "@/api/ZMSAppointmentAPI";
import ClockSvg from "@/components/Appointment/ClockSvg.vue";
import SubserviceListItem from "@/components/Appointment/SubserviceListItem.vue";
import { OfficeImpl } from "@/types/OfficeImpl";
import { SelectedServiceProvider } from "@/types/ProvideInjectTypes";
import { ServiceImpl } from "@/types/ServiceImpl";
import { getServiceBaseURL, MAX_SLOTS } from "@/utils/Constants";

const props = defineProps<{
  preselectedServiceId: string | undefined;
  preselectedOffiveId: string | undefined;
  exclusiveLocation: string | undefined;
  t: any;
}>();

const emit = defineEmits<(e: "next") => void>();

const services = ref<Service[]>([]);
const relations = ref<Relation[]>([]);
const offices = ref<Office[]>([]);

const { selectedService, updateSelectedService } =
  inject<SelectedServiceProvider>(
    "selectedServiceProvider"
  ) as SelectedServiceProvider;
const service = ref<ServiceImpl>(selectedService.value);
const maxSlotsPerAppointment = ref<number>(25);
const currentSlots = ref<number>(0);
const countOfService = ref<number>(1);

const durationInfo = ref<HTMLElement | null>(null);

watch(service, (newService) => {
  if (!newService) {
    return;
  }
  setServiceData(newService);
  updateSelectedService(newService);
});

watch(countOfService, (newCountOfService) => {
  if (service.value.count < newCountOfService) {
    currentSlots.value += getMinSlotOfProvider(service.value.providers);
  } else {
    currentSlots.value -= getMinSlotOfProvider(service.value.providers);
  }
  service.value.count = newCountOfService;
});

const setServiceData = (selectedService: ServiceImpl) => {
  service.value.providers = getProviders(selectedService.id, null);
  service.value.count = 1;
  currentSlots.value = getMinSlotOfProvider(service.value.providers);

  if (selectedService.combinable) {
    const combinable = selectedService.combinable;
    if (typeof combinable[parseInt(selectedService.id)] !== "undefined") {
      delete combinable[parseInt(selectedService.id)];
    }

    service.value.subServices = Object.entries(combinable)
      .map(([subServiceId, providers]) => {
        const subService = services.value.filter(
          (subService) => parseInt(subService.id) === parseInt(subServiceId)
        );
        if (subService && subService.length === 1) {
          return {
            id: parseInt(subServiceId),
            name: subService[0].name,
            maxQuantity: subService[0].maxQuantity,
            providers: getProviders(subServiceId, providers),
            count: 0,
          };
        }
      })
      .filter((subService) => {
        if (subService === undefined) return false;
        if (props.preselectedOffiveId) {
          return subService.providers.some(
            (provider) => provider.id === props.preselectedOffiveId
          );
        }
        return true;
      });
  }

  const maxSlotsOfProvider = getMaxSlotsPerAppointementOfProvider(
    service.value.providers
  );
  maxSlotsPerAppointment.value =
    maxSlotsOfProvider > 0
      ? Math.min(maxSlotsOfProvider, MAX_SLOTS)
      : MAX_SLOTS;
};

const getProviders = (serviceId: string, providers: string[] | null) => {
  const officesAtService = new Array<OfficeImpl>();
  relations.value.forEach((relation) => {
    if (relation.serviceId == serviceId) {
      const foundOffice: OfficeImpl = offices.value.filter((office) => {
        return office.id == relation.officeId;
      })[0];
      if (
        props.exclusiveLocation &&
        foundOffice.id !== props.preselectedOffiveId
      ) {
        return;
      }

      if (
        !providers ||
        providers.find((prov) => prov == foundOffice.id.toString())
      ) {
        foundOffice.slots = relation.slots;
        officesAtService.push(foundOffice);
      }
    }
  });

  return officesAtService;
};

const changeAppointmentCountOfSubservice = (id: string, count: number) => {
  const subservice = service.value.subServices?.find(
    (subService) => subService.id == id
  );

  if (subservice != undefined) {
    if (subservice.count < count) {
      currentSlots.value += getMinSlotOfProvider(subservice.providers);
    } else {
      currentSlots.value -= getMinSlotOfProvider(subservice.providers);
    }
    subservice.count = count;
  }
};

const maxValueOfService = computed(() => {
  return checkPlusEndabled.value
    ? service.value.maxQuantity
    : service.value.count;
});

const checkPlusEndabled = computed(
  () =>
    currentSlots.value + getMinSlotOfProvider(service.value.providers) <=
    maxSlotsPerAppointment.value
);

const getMinSlotOfProvider = (provider: OfficeImpl[]) => {
  let minSlot = MAX_SLOTS;
  provider.forEach((provider) => {
    if (provider.slots) {
      minSlot = Math.min(minSlot, provider.slots);
    }
  });
  return minSlot;
};

const getMaxSlotsPerAppointementOfProvider = (provider: OfficeImpl[]) => {
  let maxSlot = 0;
  provider.forEach((provider) => {
    if (
      provider.maxSlotsPerAppointment &&
      parseInt(provider.maxSlotsPerAppointment) > 0
    ) {
      maxSlot = Math.max(maxSlot, parseInt(provider.maxSlotsPerAppointment));
    }
  });
  return maxSlot;
};

const nextStep = () => emit("next");

const skipSubservices = () => {
  if (durationInfo.value) durationInfo.value.focus();
};

onMounted(() => {
  if (service.value) {
    countOfService.value = service.value.count
      ? service.value.count
      : countOfService.value;
    currentSlots.value =
      getMinSlotOfProvider(service.value.providers) * countOfService.value;
  } else {
    fetchServicesAndProviders(
      props.preselectedServiceId ?? undefined,
      props.preselectedOffiveId ?? undefined
    ).then((data) => {
      services.value = data.services;
      relations.value = data.relations;
      offices.value = data.offices;

      if (props.preselectedServiceId) {
        service.value = services.value.find(
          (service) => service.id === props.preselectedServiceId
        );
      }
    });
  }
});
</script>

<style scoped>
.wrapper {
  display: flex;
}

.wrapper > * {
  margin: 0 8px;
}
</style>
