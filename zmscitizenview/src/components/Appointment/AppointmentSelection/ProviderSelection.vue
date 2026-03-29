<template>
  <div>
    <div
      v-if="
        providersWithAppointments &&
        providersWithAppointments.length > 1 &&
        selectableProviders.length > 1
      "
    >
      <div class="m-component slider-no-margin">
        <div class="m-content">
          <h2 style="margin-bottom: 0">
            {{ t("location") }}
          </h2>
        </div>
        <div class="m-content">
          <MucCheckboxGroup :errorMsg="providerSelectionError">
            <template #checkboxes>
              <MucCheckbox
                v-for="provider in selectableProviders"
                :key="provider.id"
                :id="`provider-${provider.id}`"
                :label="provider.name"
                :hint="
                  provider.address.street + ' ' + provider.address.house_number
                "
                :model-value="!!selectedProvidersMap[provider.id]"
                @update:model-value="
                  (val: boolean) => onToggle(provider.id, val)
                "
              />
            </template>
          </MucCheckboxGroup>
        </div>
      </div>
    </div>

    <div
      v-if="
        selectedProvider &&
        selectableProviders &&
        selectableProviders.length === 1
      "
    >
      <div class="m-component">
        <div class="m-content">
          <h2>{{ t("location") }}</h2>
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
              <h3
                :id="`provider-${selectedProvider.id}`"
                class="m-teaser-contained-contact__headline"
              >
                {{ selectedProvider.name }}
              </h3>
              <div class="m-teaser-contained-contact__details">
                <p class="m-teaser-contained-contact__detail">
                  <svg
                    aria-hidden="true"
                    class="icon icon--before"
                  >
                    <use :xlink:href="`#${detailIcon}`"></use>
                  </svg>
                  <span
                    v-if="
                      variantId === VARIANT_ID_TEL ||
                      variantId === VARIANT_ID_VIDEO
                    "
                  >
                    {{ t(`appointmentTypes.${variantId}`) }}
                  </span>

                  <span v-else>
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
  </div>
</template>

<script setup lang="ts">
import type { OfficeImpl } from "@/types/OfficeImpl";

import { MucCheckbox, MucCheckboxGroup } from "@muenchen/muc-patternlab-vue";
import { computed, inject } from "vue";

import { SelectedServiceProvider } from "@/types/ProvideInjectTypes";

const VARIANT_ID_TEL = 2;
const VARIANT_ID_VIDEO = 3;

const props = defineProps<{
  t: (key: string) => string;
  selectableProviders: OfficeImpl[] | undefined;
  providersWithAppointments: OfficeImpl[] | undefined;
  selectedProvider: OfficeImpl | null | undefined;
  selectedProviders: { [id: string]: boolean };
  providerSelectionError: string | undefined;
}>();

const emit = defineEmits<{
  (e: "update:selectedProviders", value: { [id: string]: boolean }): void;
}>();

const selectableProviders = computed(() => props.selectableProviders || []);
const selectedProvidersMap = computed(() => props.selectedProviders || {});

const onToggle = (id: string | number, val: boolean) => {
  const idStr = String(id);
  const next: { [id: string]: boolean } = { ...selectedProvidersMap.value };
  next[idStr] = val;
  emit("update:selectedProviders", next);
};

const { selectedService } = inject<SelectedServiceProvider>(
  "selectedServiceProvider"
)!;

const variantId = computed<number | null>(() => {
  const id = (selectedService.value as any)?.variantId;
  return Number.isFinite(id) ? (id as number) : null;
});

const detailIcon = computed<string>(() => {
  if (variantId.value === VARIANT_ID_TEL) return "icon-telephone";
  if (variantId.value === VARIANT_ID_VIDEO) return "icon-video-camera";
  return "icon-map-pin";
});
</script>
