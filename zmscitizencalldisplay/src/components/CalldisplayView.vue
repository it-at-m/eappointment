<template>
  <div
    class="calldisplay-view"
    style="background-color: #c9d5e7; min-height: 100vh"
  >
    <div
      class="header-bar"
      style="
        background-color: #015a9f;
        color: #ffffff;
        padding: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
      "
    >
      <div class="header-left" style="font-size: 1.75em; font-weight: bold">
        {{ scopeShortName }}
      </div>
      <div
        class="header-right"
        style="
          font-size: 1.45em;
          font-weight: normal;
          display: flex;
          align-items: center;
          gap: 10px;
        "
      >
        <div
          v-if="!audioEnabled"
          @click="showSoundPermissionModal = true"
          style="
            background: #ffc107;
            color: #000;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 12px;
            margin-right: 10px;
            cursor: pointer;
          "
          title="Click to open Sound permissions for this domain"
        >
          🔊 Click to enable Sound
        </div>
        <span v-html="currentDateTime"></span>
      </div>
    </div>

    <div v-if="loading" class="loading">
      <div class="spinner"></div>
      <p>{{ $t("calldisplay.loading") }}</p>
    </div>

    <div v-else-if="error" class="error-container">
      <muc-callout type="error" style="max-width: 80%; margin: 5% auto">
        <template #header>
          {{ $t("calldisplay.errorHeader") }}
        </template>
        <template #content>
          <span v-html="sanitizeHtml($t('calldisplay.errorContent'))"></span>
          <a :href="$t('calldisplay.errorSupportLink')" target="_blank">{{
            $t("calldisplay.errorSupportLink")
          }}</a>
        </template>
      </muc-callout>
    </div>

    <div v-if="!error">
      <!-- Normal calldisplay content when scopes are provided -->
      <div v-if="calldisplay?.scopes && calldisplay.scopes.length > 0">
        <muc-banner
          v-if="calldisplay?.scopes?.[0]?.preferences?.queue?.callDisplayText"
          style="width: 100%; font-size: 1.25em"
        >
          {{ calldisplay.scopes[0].preferences.queue.callDisplayText }}
        </muc-banner>

        <div
          v-if="calldisplay"
          class="calldisplay-content"
          style="padding: 20px"
        >
          <div class="queue-section">
            <div
              class="queue-tables-container"
              style="display: flex; gap: 20px; width: 100%"
            >
              <!-- First table (rows 1-5) -->
              <div class="queue-table" style="flex: 1; min-width: 300px">
                <table
                  class="calldisplay-table"
                  style="
                    width: 100%;
                    border-collapse: collapse;
                    border: 2px solid #dee2e6;
                    background: white;
                  "
                >
                  <thead>
                    <tr>
                      <th
                        style="
                          background-color: #015a9f;
                          color: #ffffff;
                          border: 1px solid #dee2e6;
                          padding: 15px;
                          text-align: left;
                          font-weight: bold;
                          font-size: 18px;
                        "
                      >
                        <span style="font-weight: bold; font-size: 32px">{{
                          $t("calldisplay.table.number")
                        }}</span>
                        <span style="font-weight: normal; font-size: 28px"
                          >/ {{ $t("calldisplay.table.numberEn") }}</span
                        >
                      </th>
                      <th
                        style="
                          background-color: #015a9f;
                          color: #ffffff;
                          border: 1px solid #dee2e6;
                          padding: 15px;
                          text-align: left;
                          font-weight: bold;
                          font-size: 18px;
                        "
                      >
                        <span style="font-weight: bold; font-size: 32px">{{
                          $t("calldisplay.table.counter")
                        }}</span>
                        <span style="font-weight: normal; font-size: 28px"
                          >/ {{ $t("calldisplay.table.counterEn") }}</span
                        >
                      </th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr
                      v-for="row in 5"
                      :key="row"
                      class="queue-row"
                      :style="{
                        backgroundColor: row % 2 === 1 ? '#f2f6fa' : '#e4eaf3',
                        height: '80px',
                        minHeight: '80px',
                      }"
                    >
                      <td
                        class="number-cell"
                        style="
                          border: 1px solid #dee2e6;
                          padding: 10px;
                          text-align: left;
                          height: 80px;
                          min-height: 80px;
                          vertical-align: middle;
                          color: #3a5368;
                          font-size: 50px;
                          font-weight: bold;
                          line-height: 80px;
                        "
                      >
                        <span
                          v-if="getQueueItemForRow(row, 1)"
                          :class="
                            getStatusClass(
                              getQueueItemForRow(row, 1)?.status || '',
                            )
                          "
                          :data-appointment="getQueueItemForRow(row, 1)?.number"
                          :data-status="
                            getQueueItemForRow(row, 1)?.status || 'called'
                          "
                          class="queue-number"
                        >
                          {{ getQueueItemForRow(row, 1)?.number || "" }}
                        </span>
                        <span v-else>&nbsp;</span>
                      </td>
                      <td
                        class="counter-cell"
                        style="
                          border: 1px solid #dee2e6;
                          padding: 10px;
                          text-align: left;
                          height: 80px;
                          min-height: 80px;
                          vertical-align: middle;
                          color: #3a5368;
                          font-size: 50px;
                          font-weight: bold;
                          line-height: 80px;
                        "
                      >
                        <span
                          v-if="getQueueItemForRow(row, 1)"
                          :class="
                            getStatusClass(
                              getQueueItemForRow(row, 1)?.status || '',
                            )
                          "
                          :data-appointment="getQueueItemForRow(row, 1)?.number"
                          :data-status="
                            getQueueItemForRow(row, 1)?.status || 'called'
                          "
                          class="queue-destination"
                        >
                          {{ getQueueItemForRow(row, 1)?.destination || "" }}
                        </span>
                        <span v-else>&nbsp;</span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <!-- Second table (rows 6-10) -->
              <div class="queue-table" style="flex: 1; min-width: 300px">
                <table
                  class="calldisplay-table"
                  style="
                    width: 100%;
                    border-collapse: collapse;
                    border: 2px solid #dee2e6;
                    background: white;
                  "
                >
                  <thead>
                    <tr>
                      <th
                        style="
                          background-color: #015a9f;
                          color: #ffffff;
                          border: 1px solid #dee2e6;
                          padding: 15px;
                          text-align: left;
                          font-weight: bold;
                          font-size: 18px;
                        "
                      >
                        <span style="font-weight: bold; font-size: 32px">{{
                          $t("calldisplay.table.number")
                        }}</span>
                        <span style="font-weight: normal; font-size: 28px"
                          >/ {{ $t("calldisplay.table.numberEn") }}</span
                        >
                      </th>
                      <th
                        style="
                          background-color: #015a9f;
                          color: #ffffff;
                          border: 1px solid #dee2e6;
                          padding: 15px;
                          text-align: left;
                          font-weight: bold;
                          font-size: 18px;
                        "
                      >
                        <span style="font-weight: bold; font-size: 32px">{{
                          $t("calldisplay.table.counter")
                        }}</span>
                        <span style="font-weight: normal; font-size: 28px"
                          >/ {{ $t("calldisplay.table.counterEn") }}</span
                        >
                      </th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr
                      v-for="row in 5"
                      :key="row + 5"
                      class="queue-row"
                      :style="{
                        backgroundColor:
                          (row + 5) % 2 === 1 ? '#f2f6fa' : '#e4eaf3',
                        height: '80px',
                        minHeight: '80px',
                      }"
                    >
                      <td
                        class="number-cell"
                        style="
                          border: 1px solid #dee2e6;
                          padding: 10px;
                          text-align: left;
                          height: 80px;
                          min-height: 80px;
                          vertical-align: middle;
                          color: #3a5368;
                          font-size: 50px;
                          font-weight: bold;
                          line-height: 80px;
                        "
                      >
                        <span
                          v-if="getQueueItemForRow(row + 5, 1)"
                          :class="
                            getStatusClass(
                              getQueueItemForRow(row + 5, 1)?.status || '',
                            )
                          "
                          :data-appointment="
                            getQueueItemForRow(row + 5, 1)?.number
                          "
                          :data-status="
                            getQueueItemForRow(row + 5, 1)?.status || 'called'
                          "
                          class="queue-number"
                        >
                          {{ getQueueItemForRow(row + 5, 1)?.number || "" }}
                        </span>
                        <span v-else>&nbsp;</span>
                      </td>
                      <td
                        class="counter-cell"
                        style="
                          border: 1px solid #dee2e6;
                          padding: 10px;
                          text-align: left;
                          height: 80px;
                          min-height: 80px;
                          vertical-align: middle;
                          color: #3a5368;
                          font-size: 50px;
                          font-weight: bold;
                          line-height: 80px;
                        "
                      >
                        <span
                          v-if="getQueueItemForRow(row + 5, 1)"
                          :class="
                            getStatusClass(
                              getQueueItemForRow(row + 5, 1)?.status || '',
                            )
                          "
                          :data-appointment="
                            getQueueItemForRow(row + 5, 1)?.number
                          "
                          :data-status="
                            getQueueItemForRow(row + 5, 1)?.status || 'called'
                          "
                          class="queue-destination"
                        >
                          {{
                            getQueueItemForRow(row + 5, 1)?.destination || ""
                          }}
                        </span>
                        <span v-else>&nbsp;</span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <div v-if="scope" class="scope-info">
            <h3>{{ scope.name }}</h3>
            <p v-if="scope.description">{{ scope.description }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Sound Permission Modal -->
  <SoundPermissionModal
    :is-visible="showSoundPermissionModal"
    :domain="currentDomain"
    @close="showSoundPermissionModal = false"
  />
</template>

<script setup lang="ts">
import { computed, ref, onMounted, onUnmounted, watch } from "vue";
import { useI18n } from "vue-i18n";
import SoundPermissionModal from "./SoundPermissionModal.vue";
import type {
  CalldisplayEntity,
  QueueList,
  Scope,
} from "../api/CalldisplayAPI";
import { sanitizeHtml } from "../utils/sanitizeHtml";
import { audioManager } from "../utils/audioManager";
import { blinkElementsByAppointmentIds } from "../utils/blinkEffect";

const { t: $t } = useI18n();

interface Props {
  calldisplay: CalldisplayEntity | null;
  queue: QueueList[] | null;
  scope: Scope | null;
  loading: boolean;
  refreshing: boolean;
  error: string | null;
}

const props = defineProps<Props>();

// Date and time functionality
const currentTime = ref(new Date());

// Audio enabled state
const audioEnabled = ref(false);

// Sound permission modal state
const showSoundPermissionModal = ref(false);
const currentDomain = ref(window.location.origin);

const currentDateTime = computed(() => {
  const date = currentTime.value.toLocaleDateString("de-DE", {
    weekday: "long",
    year: "numeric",
    month: "long",
    day: "numeric",
  });
  const time = currentTime.value.toLocaleTimeString("de-DE", {
    hour: "2-digit",
    minute: "2-digit",
    second: "2-digit",
  });
  return `${date} <strong>${time}</strong>`;
});

const scopeShortName = computed(() => {
  if (props.calldisplay?.scopes && props.calldisplay.scopes.length === 1) {
    return props.calldisplay.scopes[0].shortName || "";
  }
  return "";
});

let timeInterval: NodeJS.Timeout | null = null;

onMounted(() => {
  timeInterval = setInterval(() => {
    currentTime.value = new Date();
  }, 1000);

  // Check audio enabled state periodically
  const checkAudioState = () => {
    const debugState = audioManager.getDebugState();
    audioEnabled.value = debugState.userHasInteracted;
  };

  // Check immediately and then every second
  checkAudioState();
  const audioCheckInterval = setInterval(checkAudioState, 1000);

  // Clean up interval on unmount
  onUnmounted(() => {
    if (audioCheckInterval) {
      clearInterval(audioCheckInterval);
    }
  });
});

onUnmounted(() => {
  if (timeInterval) {
    clearInterval(timeInterval);
  }
  // Reset audio manager when component is unmounted
  audioManager.reset();
});

// Watch for changes in queue data to handle audio and blinking effects
watch(
  () => props.queue,
  (newQueue) => {
    console.log("CalldisplayView - Queue data changed:", newQueue);

    if (!newQueue || newQueue.length === 0) {
      console.log("CalldisplayView - No queue data, skipping audio check");
      return;
    }

    // Convert QueueList to the format expected by audioManager
    const queueItems = newQueue.map((item) => ({
      number: item.number,
      destination: item.destination,
      status: item.status, // Use actual status from API
      appointmentId: item.number, // Use number as appointment ID
    }));

    console.log("CalldisplayView - Raw queue data:", newQueue);
    console.log("CalldisplayView - Converted queue items:", queueItems);
    console.log(
      "CalldisplayView - Status values found:",
      queueItems.map((item) => item.status),
    );

    // Get new appointment IDs and play audio
    const newIds = audioManager.updateAndPlayAudio(queueItems);

    // Blink elements for new appointments
    if (newIds.length > 0) {
      console.log(
        "CalldisplayView - Blinking elements for new appointments:",
        newIds,
      );
      // Use nextTick to ensure DOM is updated
      setTimeout(() => {
        blinkElementsByAppointmentIds(newIds, {
          duration: 3000,
          interval: 300,
          times: 3,
        });
      }, 100);
    }
  },
  { immediate: false },
);

const getStatusClass = (status: string) => {
  switch (status) {
    case "waiting":
      return "status-waiting";
    case "called":
      return "status-called";
    case "confirmed":
      return "status-confirmed";
    case "queued":
      return "status-queued";
    case "pending":
      return "status-pending";
    case "pickup":
      return "status-pickup";
    default:
      return "status-unknown";
  }
};

const getQueueItemForRow = (row: number, column: number) => {
  if (!props.queue || !Array.isArray(props.queue)) return null;

  // Only show called items (status: 'called')
  const calledItems = props.queue.filter((item) => item.status === "called");

  // Calculate index: (row - 1) * 2 + (column - 1)
  const index = (row - 1) * 2 + (column - 1);

  return calledItems[index] || null;
};
</script>

<style scoped>
.calldisplay-view {
  max-width: 800px;
  margin: 0 auto;
  padding: 20px;
  font-family: Arial, sans-serif;
}

.loading {
  text-align: center;
  padding: 40px;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #f3f3f3;
  border-top: 4px solid #3498db;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 20px;
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}

.error-container {
  padding: 20px;
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 200px;
}

.error {
  text-align: center;
  padding: 40px;
  color: #e74c3c;
}

.title {
  text-align: center;
  color: #2c3e50;
  margin-bottom: 30px;
}

.queue-section {
  margin-bottom: 30px;
}

.refresh-indicator {
  display: inline-block;
  margin-left: 10px;
  animation: spin 1s linear infinite;
  color: #6c757d;
  font-size: 0.8em;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

.queue-tables-container {
  display: flex;
  gap: 20px;
  width: 100%;
}

.queue-table {
  flex: 1;
  min-width: 300px;
  overflow-x: auto;
}

.calldisplay-table {
  width: 100%;
  border-collapse: collapse;
  border: 2px solid #dee2e6;
  background: white;
}

.calldisplay-table th {
  background-color: #015a9f;
  color: #ffffff;
  border: 1px solid #dee2e6;
  padding: 15px;
  text-align: center;
  font-weight: bold;
  font-size: 18px;
}

.calldisplay-table td {
  border: 1px solid #dee2e6;
  padding: 20px;
  text-align: center;
  height: 80px;
  min-height: 80px;
  vertical-align: middle;
}

.queue-row {
  height: 80px;
  min-height: 80px;
}

.queue-row td {
  height: 80px;
  min-height: 80px;
  vertical-align: middle;
  color: #3a5368;
  font-size: 50px;
  font-weight: bold;
}

.calldisplay-table tbody tr:nth-child(odd) {
  background-color: #f2f6fa !important;
}

.calldisplay-table tbody tr:nth-child(even) {
  background-color: #e4eaf3 !important;
}

.calldisplay-table tbody tr:nth-child(odd) td {
  background-color: #f2f6fa !important;
}

.calldisplay-table tbody tr:nth-child(even) td {
  background-color: #e4eaf3 !important;
}

.number-cell {
  width: 25%;
  font-size: 1.5em;
  font-weight: bold;
}

.counter-cell {
  width: 25%;
  font-size: 1.2em;
}

.queue-number {
  display: inline-block;
  padding: 8px 16px;
  border-radius: 4px;
  background: #e9ecef;
  color: #2c3e50;
}

.queue-destination {
  display: inline-block;
  padding: 8px 16px;
  border-radius: 4px;
  background: #e9ecef;
  color: #2c3e50;
}

.status-waiting .queue-number,
.status-waiting .queue-destination {
  border-color: #ffc107;
  background: #fff3cd;
  color: #856404;
}

.status-called .queue-number,
.status-called .queue-destination {
  border-color: #28a745;
  background: #d4edda;
  color: #155724;
}

.status-confirmed .queue-number,
.status-confirmed .queue-destination {
  border-color: #17a2b8;
  background: #d1ecf1;
  color: #0c5460;
}

.status-queued .queue-number,
.status-queued .queue-destination {
  border-color: #6f42c1;
  background: #e2e3f0;
  color: #4a2c7a;
}

.status-pending .queue-number,
.status-pending .queue-destination {
  border-color: #fd7e14;
  background: #ffeaa7;
  color: #8a4b00;
}

.status-pickup .queue-number,
.status-pickup .queue-destination {
  border-color: #20c997;
  background: #d1f2eb;
  color: #0a4d3a;
}

.no-data {
  text-align: center;
  padding: 40px;
  color: #6c757d;
  font-style: italic;
}

.scope-info {
  background: #e9ecef;
  padding: 20px;
  border-radius: 8px;
  margin-top: 30px;
}

.scope-info h3 {
  margin: 0 0 10px 0;
  color: #2c3e50;
}

.scope-info p {
  margin: 0;
  color: #6c757d;
}
</style>
