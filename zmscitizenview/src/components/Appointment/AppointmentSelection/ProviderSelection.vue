<template>
  <div>
    <div
      v-if="providersWithAppointments && providersWithAppointments.length > 1"
    >
      <div class="m-component slider-no-margin">
        <div class="m-content">
          <h2
            tabindex="0"
            style="margin-bottom: 0"
          >
            {{ t("location") }}
          </h2>
        </div>
        <div class="m-content">
          <MucCheckboxGroup :errorMsg="providerSelectionError">
            <template #checkboxes>
              <MucCheckbox
                v-for="provider in selectableProviders"
                :key="provider.id"
                :id="'checkbox-' + provider.id"
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
  </div>
</template>

<script setup lang="ts">
import type { OfficeImpl } from "@/types/OfficeImpl";

import { MucCheckbox, MucCheckboxGroup } from "@muenchen/muc-patternlab-vue";
import { computed } from "vue";

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
</script>
