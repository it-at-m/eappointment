<template>
  <p
    v-if="!available"
    class="jumpin-unavailable"
  >
    {{ t("jumpinUnavailable") }}
  </p>
  <div
    v-else
    class="jumpin-builder m-component m-component-form"
  >
    <svg
      class="jumpin-skyline"
      viewBox="0 0 1440 300"
      preserveAspectRatio="xMidYMax slice"
      aria-hidden="true"
    >
      <path
        fill="currentColor"
        d="M0 300V228H28L64 186L100 228H150V210H190V240H236V120C236 68 300 68 300 120V188H324V120C324 68 388 68 388 120V240H450V208H492V164H516V128L540 64L564 128V164H588V208H598V158H590V142H630V126H686V142H726V158H718V208H728V150C728 100 760 72 774 78V52H790V78C804 72 836 100 836 150V198H940V246H1168V124H1144V92H1168V40H1182V92H1206V124H1182V246H1260V220H1320V200L1364 168L1408 200H1440V300Z"
      />
      <path
        class="jumpin-skyline__arch"
        d="M618 300V224A40 40 0 0 1 698 224V300Z"
      />
      <g
        fill="none"
        stroke="currentColor"
        stroke-width="5"
        stroke-linecap="round"
      >
        <path d="M1018 246L1070 168L1122 246" />
        <circle
          cx="1070"
          cy="168"
          r="48"
        />
        <path
          d="M1070 124V212M1026 168H1114M1039 137L1101 199M1101 137L1039 199"
        />
        <circle
          cx="1070"
          cy="168"
          r="6"
          fill="currentColor"
          stroke="none"
        />
      </g>
    </svg>
    <div class="container">
      <div class="m-component__grid">
        <div class="m-component__column">
          <h1>{{ t("jumpinTitle") }}</h1>
          <p class="jumpin-intro">
            {{ t("jumpinIntro") }}
          </p>
          <div
            v-if="loading"
            class="jumpin-loading"
            role="status"
          >
            <MucSpinner :text="t('jumpinLoading')" />
          </div>
          <p
            v-else-if="loadError"
            class="jumpin-error"
            role="alert"
          >
            {{ t(loadError) }}
          </p>
          <form
            ref="formEl"
            v-else
            class="m-form m-form--default"
            @submit.prevent
          >
            <div class="jumpin-field">
              <muc-select
                id="jumpin-service"
                :model-value="selectedService"
                :items="serviceChoices"
                item-title="label"
                :label="t('service')"
                :placeholder="t('jumpinNameOrId')"
                :no-item-found-message="t('noServiceFound')"
                @update:model-value="selectedService = asChoice($event)"
              />
              <muc-icon
                class="jumpin-field__icon icon"
                icon="shopping-cart"
              />
            </div>
            <div
              v-if="commonServices.length"
              class="m-linklist-inline"
            >
              <h2 class="m-linklist-inline__title">
                {{ t("oftenSearchedService") }}
              </h2>
              <ul class="m-linklist-inline__list">
                <li
                  v-for="service in commonServices"
                  :key="service.choice.id"
                  class="m-linklist-inline__list-item"
                >
                  <a
                    href="#"
                    @click="selectCommonService(service.choice, $event)"
                    >{{ service.label }}</a
                  >
                </li>
              </ul>
            </div>
            <div class="jumpin-field">
              <muc-select
                id="jumpin-office"
                :model-value="selectedOffice"
                :items="officeChoices"
                item-title="label"
                :label="t('jumpinLocation')"
                :placeholder="t('jumpinNameOrId')"
                :no-item-found-message="t('jumpinNoLocationFound')"
                @update:model-value="selectedOffice = asChoice($event)"
              />
              <muc-icon
                class="jumpin-field__icon icon"
                icon="map-pin"
              />
            </div>
            <div class="jumpin-controls">
              <muc-checkbox
                id="jumpin-exclusive"
                :label="t('jumpinExclusiveLocation')"
                :model-value="exclusiveLocation"
                @update:model-value="exclusiveLocation = $event"
              />
              <div
                v-if="link || selectedService || selectedOffice"
                class="jumpin-actions"
              >
                <muc-button
                  v-if="selectedService || selectedOffice"
                  type="button"
                  variant="secondary"
                  @click="clearSelection"
                >
                  {{ t("jumpinClearSelection") }}
                </muc-button>
                <div
                  v-if="link"
                  class="jumpin-actions-main"
                >
                  <muc-button
                    type="button"
                    variant="secondary"
                    @click="copy"
                  >
                    {{ copied ? t("jumpinCopied") : t("jumpinCopy") }}
                  </muc-button>
                  <a
                    class="m-button m-button--primary"
                    :href="link"
                    target="_blank"
                    rel="noopener"
                    >{{ t("jumpinOpen") }}</a
                  >
                </div>
              </div>
            </div>
            <p
              v-if="error"
              class="jumpin-error"
              role="alert"
            >
              {{ t(error) }}
            </p>
            <p
              v-if="copyError"
              class="jumpin-error"
              role="alert"
            >
              {{ t(copyError) }}
            </p>
            <div
              v-if="link"
              class="jumpin-result"
            >
              <p
                id="jumpin-link-label"
                class="m-label"
              >
                {{ t("jumpinLinkLabel") }}
              </p>
              <a
                ref="linkEl"
                class="jumpin-href"
                :href="link"
                target="_blank"
                rel="noopener"
                aria-labelledby="jumpin-link-label"
                >{{ link }}</a
              >
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
import type { JumpinCatalog, JumpinChoice } from "@/utils/jumpinCatalog";

import {
  MucButton,
  MucCheckbox,
  MucIcon,
  MucSelect,
  MucSpinner,
} from "@muenchen/muc-patternlab-vue";
import { computed, nextTick, onMounted, ref, watch } from "vue";
import { useI18n } from "vue-i18n";

import { fetchServicesAndProviders } from "@/api/ZMSAppointmentAPI";
import { OFTEN_SEARCHED_SERVICES } from "@/utils/Constants";
import { jumpinChoices } from "@/utils/jumpinCatalog";
import { installJumpinExactIdSearch } from "@/utils/jumpinChoiceSearch";
import {
  buildJumpinHref,
  citizenviewRootHref,
  jumpinLinkError,
} from "@/utils/jumpinLink";

installJumpinExactIdSearch();

const props = defineProps<{
  available: boolean;
}>();

const { t } = useI18n();

const catalog = ref<JumpinCatalog>({
  services: [],
  offices: [],
  relations: [],
});
const loading = ref(true);
const loadError = ref("");
const selectedService = ref<JumpinChoice | null>(null);
const selectedOffice = ref<JumpinChoice | null>(null);
const exclusiveLocation = ref(false);
const copied = ref(false);
const copyError = ref("");
const formEl = ref<HTMLFormElement | null>(null);
const linkEl = ref<HTMLAnchorElement | null>(null);

function showFullSelectedLabel(): void {
  formEl.value
    ?.querySelectorAll<HTMLElement>(".choices__list--single .choices__item")
    .forEach((item) => {
      const text = item.textContent?.trim() ?? "";
      if (text && !item.classList.contains("choices__placeholder")) {
        item.title = text;
        return;
      }
      item.removeAttribute("title");
    });
}

const choices = computed(() =>
  jumpinChoices(catalog.value, {
    serviceId: selectedService.value?.id,
    officeId: selectedOffice.value?.id,
  })
);
const serviceChoices = computed(() => choices.value.services);
const officeChoices = computed(() => choices.value.offices);

const commonServices = computed(() => {
  const byId = new Map(
    serviceChoices.value.map((service) => [service.id, service])
  );
  return [...OFTEN_SEARCHED_SERVICES].flatMap(([id, labelKey]) => {
    const choice = byId.get(id);
    if (!choice) {
      return [];
    }
    return [{ choice, label: t(labelKey) }];
  });
});

function selectCommonService(choice: JumpinChoice, event: Event): void {
  event.preventDefault();
  selectedService.value = choice;
}

watch(serviceChoices, (services) => {
  if (
    selectedService.value &&
    !services.some((service) => service.id === selectedService.value?.id)
  ) {
    selectedService.value = null;
  }
});

watch(officeChoices, (offices) => {
  if (
    selectedOffice.value &&
    !offices.some((office) => office.id === selectedOffice.value?.id)
  ) {
    selectedOffice.value = null;
  }
});

watch(
  [selectedService, selectedOffice, serviceChoices, officeChoices],
  async () => {
    await nextTick();
    showFullSelectedLabel();
  }
);

const input = computed(() => ({
  serviceId: selectedService.value?.id ?? "",
  locationId: selectedOffice.value?.id ?? "",
  exclusiveLocation: exclusiveLocation.value,
}));

const error = computed(() => jumpinLinkError(input.value));

const link = computed(() =>
  buildJumpinHref(citizenviewRootHref(window.location), input.value)
);

function asChoice(value: unknown): JumpinChoice | null {
  if (!value || typeof value !== "object" || Array.isArray(value)) {
    return null;
  }
  const choice = value as Partial<JumpinChoice>;
  if (!choice.id || !choice.label) {
    return null;
  }
  return {
    id: String(choice.id),
    name: choice.name ?? "",
    label: choice.label,
  };
}

function clearSelection(): void {
  selectedService.value = null;
  selectedOffice.value = null;
}

onMounted(async () => {
  document.title = t("jumpinTitle");
  if (!props.available) {
    return;
  }
  const data = await fetchServicesAndProviders();
  if (!data || !("services" in data) || !("offices" in data)) {
    loadError.value = "jumpinLoadError";
    loading.value = false;
    return;
  }
  catalog.value = {
    services: data.services ?? [],
    offices: data.offices ?? [],
    relations: data.relations ?? [],
  };
  loading.value = false;
});

function selectDisplayedLink(): void {
  const el = linkEl.value;
  const selection = window.getSelection();
  if (!el || !selection) {
    return;
  }
  const range = document.createRange();
  range.selectNodeContents(el);
  selection.removeAllRanges();
  selection.addRange(range);
}

async function copy(): Promise<void> {
  if (!link.value) {
    return;
  }
  const writeText = navigator.clipboard?.writeText;
  if (typeof writeText !== "function") {
    copied.value = false;
    copyError.value = "jumpinCopyFailed";
    selectDisplayedLink();
    return;
  }
  try {
    await writeText.call(navigator.clipboard, link.value);
  } catch {
    copied.value = false;
    copyError.value = "jumpinCopyFailed";
    selectDisplayedLink();
    return;
  }
  copyError.value = "";
  copied.value = true;
  window.setTimeout(() => {
    copied.value = false;
  }, 2000);
}
</script>

<style scoped>
.jumpin-builder {
  position: relative;
  box-sizing: border-box;
  width: 100%;
  min-height: 100vh;
  padding-top: 4.5rem;
  padding-bottom: 14rem;
  overflow: hidden;
  background-color: var(--color-neutrals-blue-xlight, #f2f6fa);
}

.jumpin-builder > .container {
  position: relative;
  z-index: 1;
}

.jumpin-skyline__arch {
  fill: var(--color-neutrals-blue-xlight, #f2f6fa);
}

.jumpin-skyline {
  position: absolute;
  right: 0;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 16rem;
  color: color-mix(
    in srgb,
    var(--color-neutrals-blue-xlight, #f2f6fa) 62%,
    #4d6f8c
  );
  pointer-events: none;
}

.jumpin-unavailable {
  padding: 4.5rem 1.5rem 2rem;
}

.jumpin-intro {
  margin-top: 0;
  margin-bottom: 1.5rem;
}

.jumpin-builder :deep(.m-component__grid),
.jumpin-builder :deep(.m-component__column),
.jumpin-builder :deep(.m-form) {
  min-width: 0;
  max-width: 100%;
}

.jumpin-field {
  display: grid;
  min-width: 0;
  max-width: 100%;
  margin-bottom: 1rem;
}

@media (min-width: 1200px) {
  .jumpin-field {
    margin-bottom: 1.5rem;
  }
}

.jumpin-field > :deep(.m-form-group) {
  grid-area: 1 / 1;
  min-width: 0;
  max-width: 100%;
  margin-bottom: 0;
}

.jumpin-field :deep(.choices),
.jumpin-field :deep(.choices__inner),
.jumpin-field :deep(.choices__list--single),
.jumpin-field :deep(.choices__list--dropdown) {
  min-width: 0;
  max-width: 100%;
}

.jumpin-field__icon {
  z-index: 1;
  grid-area: 1 / 1;
  align-self: end;
  justify-self: start;
  width: 1.5rem;
  height: 1.5rem;
  margin: 0 0 11px 0.75rem;
  color: #617586;
  pointer-events: none;
  fill: currentColor;
}

.jumpin-field:not(:has(.choices__placeholder)) .jumpin-field__icon {
  color: #3a5368;
}

.jumpin-field :deep(.choices__inner) {
  padding-left: 2.75rem;
}

.jumpin-field :deep(.choices__list--single .choices__item) {
  display: block;
  max-width: 100%;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.jumpin-loading {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 12rem;
}

.jumpin-error {
  color: #b00020;
}

.jumpin-controls {
  display: flex;
  flex-direction: column;
  align-items: stretch;
  gap: 1rem;
  width: 100%;
}

.jumpin-controls :deep(.m-form-group) {
  margin: 0;
}

.jumpin-actions,
.jumpin-actions-main {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.jumpin-actions {
  flex-wrap: wrap;
  justify-content: space-between;
  width: 100%;
}

.jumpin-actions-main {
  margin-left: auto;
}

.jumpin-actions :deep(.m-button),
.jumpin-href {
  margin-bottom: 0;
}

.jumpin-result {
  margin-top: 1.5rem;
}

.jumpin-href {
  display: flex;
  align-items: center;
  box-sizing: border-box;
  width: 100%;
  min-height: 3.25rem;
  padding: 0.75rem 1rem;
  background-color: #fff;
  border: 1px solid #5993c0;
  overflow-wrap: anywhere;
}
</style>
