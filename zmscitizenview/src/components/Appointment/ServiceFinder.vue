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
          :placeholder="t('serviceSelectionPlaceholder')"
        />
      </form>
      <div class="m-linklist-inline">
        <h3 class="m-linklist-inline__title">
          {{ t("oftenSearchedService") }}
        </h3>
        <ul class="m-linklist-inline__list">
          <li
            v-for="searchedService in OFTEN_SEARCHED_SERVICES"
            :key="searchedService[0]"
            class="m-linklist-inline__list-item"
          >
            <a
              href="#"
              @click="setOftenSearchedService(searchedService[0])"
            >
              {{ t(searchedService[1]) }}</a
            >
          </li>
        </ul>
      </div>
    </div>
  </div>
  <div v-else>
    <div class="m-component">
      <muc-counter
        v-model="countOfService"
        :label="service.name"
        :link="getServiceBaseURL() + service.id"
        :max="maxValueOfService"
        :min="1"
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
            v-for="subService in filteredSubServices"
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
      <div
        v-if="shouldShowMoreButton"
        class="m-button-group m-button-group--secondary"
      >
        <muc-button
          icon="chevron-down"
          variant="secondary"
          @click="showAllServices = true"
        >
          <template #default>{{ t("showAllServices") }}</template>
        </muc-button>
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
          {{ estimatedDuration }} {{ t("minutes") }}
        </div>
      </div>
    </div>
    <div
      v-if="showCaptcha"
      style="margin: 2rem 0 2rem 0"
    >
      <AltchaCaptcha
        :t="t"
        :base-url="baseUrl"
        @validationResult="(valid: boolean) => (isCaptchaValid = valid)"
        @tokenChanged="
          (token: string | null) => emit('captchaTokenChanged', token)
        "
      />
    </div>
  </div>
  <div class="m-button-group">
    <muc-button
      v-if="service"
      :disabled="showCaptcha && !isCaptchaValid"
      icon="arrow-right"
      @click="nextStep"
    >
      <template #default>{{ t("next") }}</template>
    </muc-button>
  </div>
</template>

<script setup lang="ts">
import { MucButton, MucCounter, MucSelect } from "@muenchen/muc-patternlab-vue";
import { computed, inject, onMounted, ref, watch } from "@vue/runtime-core";

import { Combinable } from "@/api/models/Combinable";
import { Office } from "@/api/models/Office";
import { Relation } from "@/api/models/Relation";
import { Service } from "@/api/models/Service";
import { fetchServicesAndProviders } from "@/api/ZMSAppointmentAPI";
import AltchaCaptcha from "@/components/Appointment/AltchaCaptcha.vue";
import ClockSvg from "@/components/Appointment/ClockSvg.vue";
import SubserviceListItem from "@/components/Appointment/SubserviceListItem.vue";
import { OfficeImpl } from "@/types/OfficeImpl";
import { SelectedServiceProvider } from "@/types/ProvideInjectTypes";
import { ServiceImpl } from "@/types/ServiceImpl";
import {
  getServiceBaseURL,
  MAX_SLOTS,
  OFTEN_SEARCHED_SERVICES,
} from "@/utils/Constants";

const isCaptchaValid = ref<boolean>(false);

const props = defineProps<{
  baseUrl: string | undefined;
  preselectedServiceId: string | undefined;
  preselectedOfficeId: string | undefined;
  exclusiveLocation: string | undefined;
  t: (key: string) => string;
}>();

const emit = defineEmits<{
  (e: "next"): void;
  (e: "captchaTokenChanged", token: string | null): void;
}>();

const services = ref<Service[]>([]);
const relations = ref<Relation[]>([]);
const offices = ref<Office[]>([]);

const { selectedService, updateSelectedService } =
  inject<SelectedServiceProvider>(
    "selectedServiceProvider"
  ) as SelectedServiceProvider;
const service = ref<ServiceImpl | undefined>(selectedService.value);
const maxSlotsPerAppointment = ref<number>(25);
const currentSlots = ref<number>(0);
const showAllServices = ref<boolean>(false);
const countOfService = ref<number>(1);

const filteredSubServices = computed(() => {
  if (!service.value?.subServices) return [];

  if (service.value.subServices.length <= 5) {
    return service.value.subServices;
  }

  return showAllServices.value
    ? service.value.subServices
    : service.value.subServices.slice(0, 3);
});

const shouldShowMoreButton = computed(() => {
  return (
    service.value?.subServices &&
    service.value.subServices.length > 5 &&
    !showAllServices.value
  );
});

const durationInfo = ref<HTMLElement | null>(null);

watch(service, (newService) => {
  if (!newService) {
    return;
  }
  setServiceData(newService);
  updateSelectedService(newService);
});

/**
 * Calculation of the currently required slots by changing the count of the selected service.
 */
watch(countOfService, (newCountOfService) => {
  if (!service.value) return;
  if ((service.value.count || 0) < newCountOfService) {
    currentSlots.value += getMinSlotOfProvider(service.value.providers || []);
  } else if ((service.value.count || 0) > newCountOfService) {
    currentSlots.value -= getMinSlotOfProvider(service.value.providers || []);
  }
  service.value.count = newCountOfService;
});

const setServiceData = (selectedService: ServiceImpl) => {
  service.value!.providers = getProviders(selectedService.id, null);
  service.value!.count = 1;
  currentSlots.value = getMinSlotOfProvider(service.value!.providers);

  if (selectedService.combinable) {
    const combinable = selectedService.combinable;
    const selfEntry = Object.entries(combinable).find(
      ([_, serviceObj]) =>
        Object.keys(serviceObj)[0] === selectedService.id.toString()
    );
    if (selfEntry) {
      const combinableServices = combinable as unknown as Combinable;
      Object.keys(combinableServices).forEach((key: string) => {
        const serviceObj = combinableServices[key];
        const serviceId = Object.keys(serviceObj)[0];
        if (serviceId === selectedService.id.toString()) {
          delete combinableServices[key];
        }
      });
    }

    service.value!.subServices = Object.entries(combinable)
      .map(([_, serviceObj]) => {
        const [[subServiceId, providers]] = Object.entries(serviceObj);
        const subService = services.value.filter(
          (subService) => parseInt(subService.id) == parseInt(subServiceId)
        );
        if (subService && subService.length === 1) {
          return {
            id: subServiceId,
            name: subService[0].name,
            maxQuantity: subService[0].maxQuantity,
            providers: getProviders(subServiceId, providers.map(String)),
            count: 0,
          };
        }
      })
      .filter(
        (service): service is NonNullable<typeof service> =>
          service !== undefined &&
          (!props.preselectedOfficeId ||
            service.providers.some(
              (provider) => provider.id == props.preselectedOfficeId
            ))
      );
  }

  const maxSlotsOfProvider = getMaxSlotsPerAppointementOfProvider(
    service.value!.providers
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
      const office = offices.value.find((office) => office.id == relation.officeId);
      if (!office) return;
      
      const foundOffice: OfficeImpl = new OfficeImpl(
        office.id,
        office.name,
        office.address,
        office.showAlternativeLocations,
        office.displayNameAlternatives,
        office.organization,
        office.organizationUnit,
        office.slotTimeInMinutes,
        undefined,
        office.scope,
        office.maxSlotsPerAppointment,
        undefined,
        office.priority || 1
      );

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

/**
 * Calculation of the currently required slots by changing the count of a subservice.
 */
const changeAppointmentCountOfSubservice = (id: string, count: number) => {
  const subservice = service.value?.subServices?.find(
    (subService) => subService.id == id
  );

  if (subservice != undefined) {
    if (subservice.count < count) {
      currentSlots.value += getMinSlotOfProvider(subservice.providers);
    } else if (subservice.count > count) {
      currentSlots.value -= getMinSlotOfProvider(subservice.providers);
    }
    subservice.count = count;
  }
};

const estimatedDuration = computed(() => {
  return service.value?.providers?.[0]?.slotTimeInMinutes
    ? service.value.providers[0].slotTimeInMinutes * currentSlots.value
    : 0;
});

/**
 * Calculates whether the count of selected service may be increased, depending on the maxQuantity of the service and the maxSlotsPerAppointment.
 */
const maxValueOfService = computed(() => {
  if (!service.value) return 0;
  return checkPlusEndabled.value
    ? service.value.maxQuantity
    : service.value.count || 0;
});

const checkPlusEndabled = computed(
  () =>
    currentSlots.value + getMinSlotOfProvider(service.value!.providers || []) <=
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

const setOftenSearchedService = (serviceId: string) => {
  const foundService = services.value.find(
    (service) => service.id == serviceId
  );
  if (foundService) {
    service.value = {
      ...foundService,
      providers: [] as OfficeImpl[],
      count: 0,
      subServices: [],
      combinable: foundService.combinable as unknown as Combinable,
    } as ServiceImpl;
  }
};

const nextStep = () => emit("next");

const skipSubservices = () => {
  if (durationInfo.value) durationInfo.value.focus();
};

/**
 * Determines whether the captcha component should be shown.
 * It checks if the currently selected service is associated with
 * at least one office that has `captchaActivatedRequired` set to true.
 */
const showCaptcha = computed(() => {
  if (!service.value || !relations.value || !offices.value) return false;

  const relatedOfficeIds = relations.value
    .filter((relation) => relation.serviceId === service.value?.id)
    .map((relation) => relation.officeId);

  return offices.value.some(
    (office) =>
      relatedOfficeIds.includes(office.id) &&
      office.scope?.captchaActivatedRequired === true
  );
});

onMounted(() => {
  if (service.value) {
    let slots = 0;
    countOfService.value = service.value.count
      ? service.value.count
      : countOfService.value;
    slots =
      getMinSlotOfProvider(service.value.providers || []) *
      (service.value.count || 0);
    if (service.value.subServices) {
      service.value.subServices.forEach((subservice) => {
        if (subservice.count > 0) {
          slots =
            slots +
            getMinSlotOfProvider(subservice.providers) * subservice.count;
        }
      });
    }
    currentSlots.value = slots;
  } else {
    fetchServicesAndProviders(
      props.preselectedServiceId ?? undefined,
      props.preselectedOfficeId ?? undefined,
      props.baseUrl ?? undefined
    ).then((data) => {
      services.value = data.services;
      relations.value = data.relations;
      offices.value = data.offices;

      if (props.preselectedServiceId) {
        const foundService = services.value.find(
          (service) => service.id == props.preselectedServiceId
        );
        if (foundService) {
          service.value = {
            ...foundService,
            providers: [] as OfficeImpl[],
            count: 0,
            subServices: [],
            combinable: foundService.combinable as unknown as Combinable,
          } as ServiceImpl;
        }
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

.m-button-group--secondary {
  margin-top: 1rem;
}
</style>
