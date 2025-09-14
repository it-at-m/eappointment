<template>
  <div class="config-message">
    <muc-callout type="info" style="max-width: 80%; margin: 5% auto">
      <template #header>
        {{ $t("calldisplay.config.title") }}
      </template>
      <template #content>
        <p>{{ $t("calldisplay.config.description") }}</p>
        <p>
          <strong>{{ $t("calldisplay.config.example") }}</strong>
        </p>
        <code>{{ $t("calldisplay.config.urlExample") }}</code>
      </template>
    </muc-callout>
  </div>
</template>

<script setup lang="ts">
import { useI18n } from "vue-i18n";

const { t: $t } = useI18n();
</script>

<style scoped>
.config-message {
  padding: 20px;
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 200px;
}

.config-message code {
  background-color: #f8f9fa;
  padding: 8px 12px;
  border-radius: 4px;
  font-family: "Courier New", monospace;
  font-size: 14px;
  display: block;
  margin-top: 10px;
  word-break: break-all;
}
</style>
