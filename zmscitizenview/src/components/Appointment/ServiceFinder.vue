<template>
  <div class="m-content">
    <h2>{{ t("service") }}</h2>
  </div>

  <div
    v-if="
      !service && !errorStates.errorStateMap.apiErrorRateLimitExceeded.value
    "
    :hidden="!!preselectedServiceId"
    class="m-component"
    style="background-color: var(--color-neutrals-blue-xlight)"
  >
    <div class="container">
      <form class="m-form m-form--default">
        <muc-select
          id="service-search"
          v-model="service"
          :items="filteredServices"
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
  <div v-else-if="!errorStates.errorStateMap.apiErrorRateLimitExceeded.value">
    <div class="m-component">
      <muc-counter
        v-model="countOfService"
        :label="service?.name || ''"
        :link="getServiceBaseURL() + (baseServiceId || '')"
        :max="maxValueOfService"
        :min="1"
      />
      <div
        v-if="variantServices.length > 1"
        class="m-component"
      >
        <muc-radio-button-group
          v-model="selectedVariant"
          :heading="t('appointmentType')"
        >
          <muc-radio-button
            v-for="variant in variantServices"
            :key="variant.variantId"
            :id="'variant-' + variant.variantId"
            :value="variant.variantId.toString()"
            :label="t(`appointmentTypes.${variant.variantId}`)"
            :hint:="t(`locationVariantText.${variant.variantId}`)"
          />
        </muc-radio-button-group>
      </div>
    </div>
    <div v-if="showSubservices">
      <h3>{{ t("combinableServices") }}</h3>
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
        <div
          v-else-if="shouldShowLessButton"
          class="m-button-group m-button-group--secondary"
        >
          <muc-button
            icon="chevron-up"
            variant="secondary"
            @click="showAllServices = false"
          >
            <template #default>{{ t("showLessServices") }}</template>
          </muc-button>
        </div>
      </div>
    </div>
    <div
      class="m-component"
      v-if="showEstimatedDuration"
    >
      <div class="wrapper">
        <clock-svg
          aria-hidden="true"
          focusable="false"
        />
        <div ref="durationInfo">
          <strong>{{ t("estimatedDuration") }}</strong>
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
        :base-url="globalState.baseUrl"
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
      :disabled="isNextDisabled"
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
  MucCounter,
  MucRadioButton,
  MucRadioButtonGroup,
  MucSelect,
} from "@muenchen/muc-patternlab-vue";
import { computed, inject, onMounted, ref, watch } from "vue";

import { Combinable } from "@/api/models/Combinable";
import { Office } from "@/api/models/Office";
import { Relation } from "@/api/models/Relation";
import { Service } from "@/api/models/Service";
import { fetchServicesAndProviders } from "@/api/ZMSAppointmentAPI";
import AltchaCaptcha from "@/components/Appointment/ServiceFinder/AltchaCaptcha.vue";
import ClockSvg from "@/components/Appointment/ServiceFinder/ClockSvg.vue";
import SubserviceListItem from "@/components/Appointment/ServiceFinder/SubserviceListItem.vue";
import { GlobalState } from "@/types/GlobalState";
import { OfficeImpl } from "@/types/OfficeImpl";
import { SelectedServiceProvider } from "@/types/ProvideInjectTypes";
import { ServiceImpl } from "@/types/ServiceImpl";
import { handleApiResponseForDownTime } from "@/utils/apiStatusService";
import { calculateEstimatedDuration } from "@/utils/calculateEstimatedDuration";
import {
  getServiceBaseURL,
  MAX_SLOTS,
  OFTEN_SEARCHED_SERVICES,
} from "@/utils/Constants";
import {
  createErrorStates,
  handleApiResponse as handleErrorApiResponse,
} from "@/utils/errorHandler";

const isCaptchaValid = ref<boolean>(false);

const props = defineProps<{
  globalState: GlobalState;
  preselectedServiceId: string | undefined;
  preselectedOfficeId: string | undefined;
  exclusiveLocation: string | undefined;
  t: (key: string) => string;
}>();

const emit = defineEmits<{
  (e: "next"): void;
  (e: "captchaTokenChanged", token: string | null): void;
  (e: "invalidJumpinLink"): void;
  (e: "rateLimitError"): void;
}>();

// Error handling state
const errorStates = createErrorStates();
const currentErrorData = computed(() => errorStates.currentErrorData);

// Watch for rate limit errors and emit event
watch(
  () => errorStates.errorStateMap.apiErrorRateLimitExceeded.value,
  (isRateLimitError) => {
    if (isRateLimitError) {
      emit("rateLimitError");
    }
  }
);

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

const shouldShowLessButton = computed(() => {
  return (
    service.value?.subServices &&
    service.value.subServices.length > 5 &&
    showAllServices.value
  );
});

const durationInfo = ref<HTMLElement | null>(null);
const baseServiceId = ref<number | string | null>(null);
const selectedVariant = ref("");

watch(service, (newService) => {
  if (!newService) return;

  baseServiceId.value =
    newService.parentId != null
      ? String(newService.parentId)
      : String(newService.id);

  const variantId = newService.variantId;
  if (typeof variantId === "number" && Number.isFinite(variantId)) {
    const next = String(variantId);
    if (selectedVariant.value !== next) selectedVariant.value = next;
  }
  setServiceData(newService);
  updateSelectedService(newService);

  countOfService.value = newService.count ?? 1;
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
  service.value!.count = Math.max(1, countOfService.value || 1);
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
      const office = offices.value.find(
        (office) => office.id == relation.officeId
      );
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
        office.disabledByServices,
        office.scope,
        office.maxSlotsPerAppointment,
        office.slots,
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
  const provider = service.value?.providers?.[0];
  return calculateEstimatedDuration(service.value, provider);
});

const showEstimatedDuration = computed(() => {
  return !(variantServices.value.length > 1 && !selectedVariant.value);
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
    .filter((relation) => relation.serviceId == service.value?.id)
    .map((relation) => relation.officeId);

  return offices.value.some(
    (office) =>
      relatedOfficeIds.includes(office.id) &&
      office.scope?.captchaActivatedRequired === true
  );
});

onMounted(() => {
  if (service.value) {
    baseServiceId.value = service.value.parentId ?? service.value.id;
    const variantId = (service.value as any)?.variantId;
    if (typeof variantId === "number" && Number.isFinite(variantId)) {
      selectedVariant.value = String(variantId);
    }
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
          slots +=
            getMinSlotOfProvider(subservice.providers) * subservice.count;
        }
      });
    }
    currentSlots.value = slots;
    if (services.value.length === 0) {
      fetchServicesAndProviders(
        props.preselectedServiceId ?? undefined,
        props.preselectedOfficeId ?? undefined,
        props.baseUrl ?? undefined
      ).then((data) => {
        handleErrorApiResponse(
          data,
          errorStates.errorStateMap,
          currentErrorData.value
        );
        if (handleApiResponseForDownTime(data, props.baseUrl)) return;

        services.value = (data as any).services.map(normalizeService);
        relations.value = (data as any).relations;
        offices.value = (data as any).offices;
      });
    }
  } else {
    fetchServicesAndProviders(
      props.preselectedServiceId ?? undefined,
      props.preselectedOfficeId ?? undefined,
      props.globalState.baseUrl ?? undefined
    ).then((data) => {
      // Handle normal errors (like rate limit) first
      handleErrorApiResponse(
        data,
        errorStates.errorStateMap,
        currentErrorData.value
      );

      // Check if any error state should be activated (maintenance/system failure)
      if (handleApiResponseForDownTime(data, props.globalState.baseUrl)) {
        return;
      }

      services.value = (data as any).services.map(normalizeService);
      relations.value = (data as any).relations;
      offices.value = (data as any).offices;

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
        } else {
          emit("invalidJumpinLink");
        }
      }

      if (props.preselectedOfficeId) {
        const foundOffice = offices.value.find(
          (office) => office.id == props.preselectedOfficeId
        );
        if (!foundOffice) {
          emit("invalidJumpinLink");
        }
      }

      if (props.preselectedServiceId && props.preselectedOfficeId) {
        const hasValidRelation = relations.value.some(
          (relation) =>
            relation.serviceId == props.preselectedServiceId &&
            relation.officeId == props.preselectedOfficeId
        );
        if (!hasValidRelation) {
          emit("invalidJumpinLink");
        }
      }
    });
  }
});

const hasNoParent = (service: Service) => service.parentId === null;
const showOnStartPage = (service: Service) => service.showOnStartPage === true;

const filteredServices = computed(() => {
  return services.value.filter(hasNoParent).filter(showOnStartPage);
});

const variantServices = computed<Service[]>(() => {
  if (!baseServiceId.value) return [];

  const variants = services.value
    .filter((s) => s.parentId === baseServiceId.value)
    .filter((s) => typeof s.variantId === "number");

  const base = services.value.find((s) => s.id === baseServiceId.value);

  const hasVariant1 = variants.some((v) => v.variantId === 1);
  if (base && !hasVariant1) {
    variants.unshift({
      ...base,
      parentId: null,
      variantId: 1,
    });
  }

  variants.sort((a, b) => a.variantId! - b.variantId!);
  return variants;
});

watch(selectedVariant, (variantId) => {
  if (!variantId || !baseServiceId.value) return;

  const selectedServiceVariant = variantServices.value.find(
    (v) => String(v.variantId) === String(variantId)
  );

  if (selectedServiceVariant) {
    service.value = selectedServiceVariant as ServiceImpl;
  }
});

const showSubservices = computed(() => {
  const value = service.value;
  if (!value) return false;

  const hasSub =
    Array.isArray(value.subServices) && value.subServices.length > 0;
  if (!hasSub) return false;
  if (value.parentId != null && value.variantId !== 1) return false;
  if (variantServices.value.length > 1 && selectedVariant.value !== "1")
    return false;

  return true;
});

function normalizeService(raw: any): Service {
  return {
    id: String(raw.id),
    name: raw.name,
    maxQuantity: raw.maxQuantity,
    combinable: raw.combinable,
    parentId: raw.parent_id == null ? null : String(raw.parent_id),
    variantId: raw.variant_id == null ? null : Number(raw.variant_id),
    showOnStartPage: raw.showOnStartPage,
  };
}

const hasVariants = computed(() => variantServices.value.length > 1);
const needsVariantSelection = computed(
  () => hasVariants.value && !selectedVariant.value
);
const isNextDisabled = computed(() => {
  const captchaBlocks = showCaptcha.value && !isCaptchaValid.value;
  return captchaBlocks || needsVariantSelection.value;
});
</script>

<style lang="scss" scoped>
@use "@/styles/breakpoints.scss" as *;

.wrapper {
  display: flex;
}

.wrapper > * {
  margin: 0 8px;
}

.m-button-group--secondary {
  margin-top: 1rem;
}

:deep(.counter-btn--disabled) {
  background-color: transparent !important;
  border-color: #7a8d9f !important;
  color: #7a8d9f !important;
}

@include xs-down {
  .container {
    padding-left: 24px;
    padding-right: 24px;
  }
}
.grid {
  grid-template-columns: minmax(150px, auto) 1fr !important;
}

@include sm-down {
  .grid {
    grid-template-columns: 1fr !important;
  }
}
</style>
