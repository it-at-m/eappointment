<template>
  <InternalToolShell
    :available="available"
    :unavailable="t('jumpinUnavailable')"
    :title="t('scopeOfficeTitle')"
    :intro="t('scopeOfficeIntro')"
  >
    <div
      v-if="loading"
      class="scope-loading"
      role="status"
    >
      <MucSpinner :text="t('scopeOfficeLoading')" />
    </div>
    <p
      v-else-if="loadError"
      class="scope-error"
      role="alert"
    >
      {{ t(loadError) }}
    </p>
    <form
      v-else
      ref="formEl"
      class="m-form m-form--default"
      @submit.prevent
    >
      <div class="scope-field">
        <muc-select
          :key="`scope-${selectGeneration}`"
          id="scope-office-scope"
          :model-value="selectedScope"
          :items="scopes"
          item-title="label"
          :label="t('scopeOfficeScope')"
          :placeholder="t('jumpinNameOrId')"
          :no-item-found-message="t('scopeOfficeNoScopeFound')"
          @update:model-value="onScope"
        />
        <muc-icon
          class="scope-field__icon icon"
          icon="information"
        />
      </div>
      <div class="scope-field">
        <muc-select
          :key="`office-${selectGeneration}`"
          id="scope-office-office"
          :model-value="selectedOffice"
          :items="offices"
          item-title="label"
          :label="t('jumpinLocation')"
          :placeholder="t('jumpinNameOrId')"
          :no-item-found-message="t('jumpinNoLocationFound')"
          @update:model-value="onOffice"
        />
        <muc-icon
          class="scope-field__icon icon"
          icon="map-pin"
        />
      </div>
      <div
        v-if="selectedScope || selectedOffice"
        class="scope-actions"
      >
        <muc-button
          type="button"
          variant="secondary"
          @click="clearSelection"
        >
          {{ t("jumpinClearSelection") }}
        </muc-button>
      </div>
      <p
        v-if="copyError"
        class="scope-error"
        role="alert"
      >
        {{ t(copyError) }}
      </p>
      <div
        v-for="record in shown"
        :key="`${record.officeId}-${record.scopeId}`"
        class="scope-result"
      >
        <p class="scope-result__name">
          {{ record.officeName }}
        </p>
        <div class="scope-result__row">
          <p
            :id="`scope-id-label-${record.officeId}`"
            class="m-label"
          >
            {{ t("scopeOfficeScopeId") }}
          </p>
          <p
            v-if="record.scopeId"
            :ref="
              (el) =>
                setValueEl(`scope-${record.officeId}-${record.scopeId}`, el)
            "
            class="scope-value"
            :aria-labelledby="`scope-id-label-${record.officeId}`"
          >
            {{ record.scopeId }}
          </p>
          <p
            v-else
            class="scope-missing"
          >
            {{ t("scopeOfficeNoScope") }}
          </p>
          <p
            v-if="record.scopeId"
            class="scope-result__meta"
          >
            {{ record.scopeName }}
          </p>
          <muc-button
            v-if="record.scopeId"
            type="button"
            variant="secondary"
            @click="
              copy(`scope-${record.officeId}-${record.scopeId}`, record.scopeId)
            "
          >
            {{
              copiedKey === `scope-${record.officeId}-${record.scopeId}`
                ? t("jumpinCopied")
                : t("jumpinCopy")
            }}
          </muc-button>
        </div>
        <div class="scope-result__row">
          <p
            :id="`office-id-label-${record.officeId}`"
            class="m-label"
          >
            {{ t("scopeOfficeOfficeId") }}
          </p>
          <p
            :ref="(el) => setValueEl(`office-${record.officeId}`, el)"
            class="scope-value"
            :aria-labelledby="`office-id-label-${record.officeId}`"
          >
            {{ record.officeId }}
          </p>
          <muc-button
            type="button"
            variant="secondary"
            @click="copy(`office-${record.officeId}`, record.officeId)"
          >
            {{
              copiedKey === `office-${record.officeId}`
                ? t("jumpinCopied")
                : t("jumpinCopy")
            }}
          </muc-button>
        </div>
      </div>
    </form>
  </InternalToolShell>
</template>

<script lang="ts" setup>
import type { JumpinChoice } from "@/utils/jumpinCatalog";
import type { ScopeOfficeRecord } from "@/utils/scopeOfficeLookup";

import {
  MucButton,
  MucIcon,
  MucSelect,
  MucSpinner,
} from "@muenchen/muc-patternlab-vue";
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from "vue";
import { useI18n } from "vue-i18n";

import { fetchServicesAndProviders } from "@/api/ZMSAppointmentAPI";
import InternalToolShell from "@/components/InternalTool/InternalToolShell.vue";
import {
  installJumpinExactIdSearch,
  uninstallJumpinExactIdSearch,
} from "@/utils/jumpinChoiceSearch";
import {
  officeChoices,
  recordsForOfficeId,
  recordsForScopeId,
  scopeChoices,
  scopeOfficeRecords,
} from "@/utils/scopeOfficeLookup";

const props = defineProps<{
  available: boolean;
}>();

if (props.available) {
  installJumpinExactIdSearch();
}

onUnmounted(() => {
  uninstallJumpinExactIdSearch();
});

const { t } = useI18n();

const records = ref<ScopeOfficeRecord[]>([]);
const loading = ref(true);
const loadError = ref("");
const selectedScope = ref<JumpinChoice | null>(null);
const selectedOffice = ref<JumpinChoice | null>(null);
const copiedKey = ref("");
const copyError = ref("");
const selectGeneration = ref(0);
const formEl = ref<HTMLFormElement | null>(null);
const valueEls = new Map<string, HTMLElement>();

const scopes = computed(() => scopeChoices(records.value));
const offices = computed(() => officeChoices(records.value));

const shown = computed(() => {
  const officeId = selectedOffice.value?.id;
  const scopeId = selectedScope.value?.id;
  if (officeId && scopeId) {
    return records.value.filter(
      (record) => record.officeId === officeId && record.scopeId === scopeId
    );
  }
  if (officeId) {
    return recordsForOfficeId(records.value, officeId);
  }
  if (scopeId) {
    return recordsForScopeId(records.value, scopeId);
  }
  return [];
});

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

function choiceFrom(
  list: JumpinChoice[],
  id: string,
  name: string
): JumpinChoice {
  return list.find((item) => item.id === id) ?? { id, name, label: name };
}

function onScope(value: unknown): void {
  const choice = asChoice(value);
  if ((choice?.id ?? "") === (selectedScope.value?.id ?? "")) {
    return;
  }
  selectedScope.value = choice;
  if (!choice) {
    return;
  }
  const matches = recordsForScopeId(records.value, choice.id);
  if (matches.length !== 1) {
    if (
      selectedOffice.value &&
      !matches.some((record) => record.officeId === selectedOffice.value?.id)
    ) {
      selectedOffice.value = null;
    }
    return;
  }
  const office = matches[0];
  if (!office) {
    return;
  }
  selectedOffice.value = choiceFrom(
    offices.value,
    office.officeId,
    office.officeName
  );
}

function onOffice(value: unknown): void {
  const choice = asChoice(value);
  if ((choice?.id ?? "") === (selectedOffice.value?.id ?? "")) {
    return;
  }
  selectedOffice.value = choice;
  if (!choice) {
    return;
  }
  const matches = recordsForOfficeId(records.value, choice.id).filter(
    (record) => record.scopeId
  );
  if (matches.length !== 1) {
    if (
      selectedScope.value &&
      !matches.some((record) => record.scopeId === selectedScope.value?.id)
    ) {
      selectedScope.value = null;
    }
    return;
  }
  const scope = matches[0];
  if (!scope) {
    return;
  }
  selectedScope.value = choiceFrom(
    scopes.value,
    scope.scopeId,
    scope.scopeName
  );
}

function clearSelection(): void {
  selectedScope.value = null;
  selectedOffice.value = null;
  selectGeneration.value += 1;
}

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

watch([selectedScope, selectedOffice, scopes, offices], async () => {
  await nextTick();
  showFullSelectedLabel();
});

function setValueEl(id: string, el: unknown): void {
  if (el instanceof HTMLElement) {
    valueEls.set(id, el);
    return;
  }
  valueEls.delete(id);
}

function selectValue(id: string): void {
  const el = valueEls.get(id);
  const selection = window.getSelection();
  if (!el || !selection) {
    return;
  }
  const range = document.createRange();
  range.selectNodeContents(el);
  selection.removeAllRanges();
  selection.addRange(range);
}

async function copy(key: string, value: string): Promise<void> {
  const writeText = navigator.clipboard?.writeText;
  if (typeof writeText !== "function") {
    copiedKey.value = "";
    copyError.value = "jumpinCopyFailed";
    selectValue(key);
    return;
  }
  try {
    await writeText.call(navigator.clipboard, value);
  } catch {
    copiedKey.value = "";
    copyError.value = "jumpinCopyFailed";
    selectValue(key);
    return;
  }
  copyError.value = "";
  copiedKey.value = key;
  window.setTimeout(() => {
    if (copiedKey.value === key) {
      copiedKey.value = "";
    }
  }, 2000);
}

onMounted(async () => {
  document.title = t("scopeOfficeTitle");
  if (!props.available) {
    return;
  }
  const data = await fetchServicesAndProviders();
  if (!data || !("offices" in data)) {
    loadError.value = "scopeOfficeLoadError";
    loading.value = false;
    return;
  }
  records.value = scopeOfficeRecords(data.offices ?? []);
  loading.value = false;
});
</script>

<style scoped>
.scope-field {
  display: grid;
  min-width: 0;
  max-width: 100%;
  margin-bottom: 1rem;
}

@media (min-width: 1200px) {
  .scope-field {
    margin-bottom: 1.5rem;
  }
}

.scope-field > :deep(.m-form-group) {
  grid-area: 1 / 1;
  min-width: 0;
  max-width: 100%;
  margin-bottom: 0;
}

.scope-field :deep(.choices),
.scope-field :deep(.choices__inner),
.scope-field :deep(.choices__list--single),
.scope-field :deep(.choices__list--dropdown) {
  min-width: 0;
  max-width: 100%;
}

.scope-field__icon {
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

.scope-field:not(:has(.choices__placeholder)) .scope-field__icon {
  color: #3a5368;
}

.scope-field :deep(.choices__inner) {
  padding-left: 2.75rem;
}

.scope-field :deep(.choices__list--single .choices__item) {
  display: block;
  max-width: 100%;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.scope-loading {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 12rem;
}

.scope-error {
  color: #b00020;
}

.scope-actions {
  display: flex;
  align-items: center;
}

.scope-actions :deep(.m-button),
.scope-result :deep(.m-button) {
  margin-bottom: 0;
}

.scope-result {
  margin-top: 1.5rem;
}

.scope-result + .scope-result {
  margin-top: 1.25rem;
}

.scope-result__name {
  margin-bottom: 0.75rem;
  font-weight: 700;
}

.scope-result__row {
  margin-top: 0.75rem;
}

.scope-result__meta {
  margin: 0.25rem 0 0.5rem;
}

.scope-missing {
  margin-top: 0;
}

.scope-value {
  display: flex;
  align-items: center;
  box-sizing: border-box;
  width: 100%;
  min-height: 3.25rem;
  margin: 0 0 0.75rem;
  padding: 0.75rem 1rem;
  background-color: #fff;
  border: 1px solid #5993c0;
  overflow-wrap: anywhere;
}
</style>
