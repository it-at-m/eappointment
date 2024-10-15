<template>
  <h2 tabindex="0">{{ t("service") }}</h2>
  <div
    v-if="!service"
    class="m-component"
    style="background-color: var(--color-neutrals-blue-xlight)"
  >
    <div class="container">
      <form class="m-form m-form--default">
        <MucSelect
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
      <MucCounter
        v-model="countOfService"
        :label="service.name"
        :max="maxValueOfService"
        min="1"
      />
    </div>
    <div v-if="service.subServices">
      <h3 tabindex="0">{{ t("combinableServices") }}</h3>
      <div class="m-listing">
        <ul class="m-listing__list">
          <template
            v-for="subService in service.subServices"
            :key="subService.id"
          >
            <SubserviceListItem
              :sub-service="subService"
              :current-slots="currentSlots"
              :max-slots-per-appointment="maxSlotsPerAppointment"
              v-on:change="changeAppointmentCountOfSubservice"
            />
          </template>
        </ul>
      </div>
    </div>
    <div class="wrapper">
      <ClockSvg />
      <div>
        <b>{{ t("estimatedDuration") }}</b>
        <br />
        {{ t("minutes") }}
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { MucCounter, MucSelect } from "@muenchen/muc-patternlab-vue";
import { computed, inject, onMounted, ref, watch } from "vue";

import { Office } from "@/api/models/Office";
import { Relation } from "@/api/models/Relation";
import { Service } from "@/api/models/Service";
import ClockSvg from "@/components/Appointment/ClockSvg.vue";
import SubserviceListItem from "@/components/Appointment/SubserviceListItem.vue";
import { OfficeImpl } from "@/types/OfficeImpl";
import { ServiceImpl } from "@/types/ServiceImpl";
import { SelectedServiceProvider } from "@/types/ServiceTypes";
import { MAX_SLOTS } from "@/utils/Constants";

const props = defineProps<{
  services: Service[];
  relations: Relation[];
  offices: Office[];
  preselectedServiceId: string | undefined;
  preselectedOffiveId: string | undefined;
  t: any;
}>();

const emit = defineEmits<{
  (e: "setService"): void;
}>();

const { selectedService, updateSelectedService } =
  inject<SelectedServiceProvider>("selectedServiceProvider");
const service = ref<ServiceImpl>(selectedService);
const maxSlotsPerAppointment = ref<number>(25);
const currentSlots = ref<number>(0);
const countOfService = ref<number>(1);

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
    let combinable = selectedService.combinable;
    if (typeof combinable[parseInt(selectedService.id)] !== "undefined") {
      delete combinable[parseInt(selectedService.id)];
    }

    service.value.subServices = Object.entries(combinable)
      .map(([subServiceId, providers]) => {
        const subService = props.services.filter(
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

  emit("setService");
};

const getProviders = (serviceId: string, providers: Array<string> | null) => {
  const officesAtService = new Array<OfficeImpl>();
  props.relations.forEach((relation) => {
    if (relation.serviceId === serviceId) {
      const foundOffice: OfficeImpl = props.offices.filter((office) => {
        return office.id === relation.officeId;
      })[0];

      if (!providers || providers.includes(foundOffice.id.toString())) {
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

const getMinSlotOfProvider = (provider: Array<OfficeImpl>) => {
  let minSlot = MAX_SLOTS;
  provider.forEach((provider) => {
    if (provider.slots) {
      minSlot = Math.min(minSlot, provider.slots);
    }
  });
  return minSlot;
};

const getMaxSlotsPerAppointementOfProvider = (provider: Array<OfficeImpl>) => {
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

onMounted(() => {
  if (props.preselectedServiceId) {
    const preselectedService = props.services.find(
      (service) => service.id === props.preselectedServiceId
    );
    if (preselectedService) setServiceData(preselectedService);
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
