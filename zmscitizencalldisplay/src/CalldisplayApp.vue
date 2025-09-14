<template>
  <div class="calldisplay-app">
    <div v-if="loading" class="loading">
      <div class="spinner"></div>
      <p>{{ $t("calldisplay.loading") }}</p>
    </div>

    <ConfigMessage
      v-if="!calldisplay?.scopes || calldisplay.scopes.length === 0"
    />

    <CalldisplayView
      v-else
      :calldisplay="calldisplay"
      :queue="queue"
      :scope="scope"
      :loading="loading"
      :refreshing="refreshing"
      :error="error"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from "vue";
import CalldisplayView from "./components/CalldisplayView.vue";
import ConfigMessage from "./components/ConfigMessage.vue";
import {
  CalldisplayAPI,
  type CalldisplayConfig,
  type CalldisplayEntity,
  type QueueList,
  type Scope,
  type Config,
} from "./api/CalldisplayAPI.ts";

// API instance
const api = new CalldisplayAPI();

// Reactive state
const loading = ref(true);
const refreshing = ref(false);
const error = ref<string | null>(null);
const calldisplay = ref<CalldisplayEntity | null>(null);
const queue = ref<QueueList[] | null>(null);
const scope = ref<Scope | null>(null);
const config = ref<Config | null>(null);
const displayNumber = ref<number | null>(null);

// Configuration from URL parameters
const urlConfig = ref<CalldisplayConfig>({
  collections: {},
  template: "default_counter",
});

// Auto-refresh interval
let refreshInterval: number | null = null;

// Parse URL parameters
function parseUrlParameters() {
  const urlParams = new URLSearchParams(window.location.search);

  // Parse collections
  const scopelist = urlParams.get("collections[scopelist]");
  const clusterlist = urlParams.get("collections[clusterlist]");

  if (scopelist) {
    urlConfig.value.collections.scopelist = scopelist
      .split(",")
      .map((id) => parseInt(id.trim()))
      .filter((id) => !isNaN(id));
  }

  if (clusterlist) {
    urlConfig.value.collections.clusterlist = clusterlist
      .split(",")
      .map((id) => parseInt(id.trim()))
      .filter((id) => !isNaN(id));
  }

  // Parse other parameters
  const template = urlParams.get("template");
  if (template) {
    urlConfig.value.template = template;
  }

  const display = urlParams.get("display");
  if (display) {
    displayNumber.value = parseInt(display);
  }

  const queueStatus = urlParams.get("queue[status]");
  if (queueStatus) {
    urlConfig.value.queue = {
      status: queueStatus.split(","),
    };
  }
}

// Load data from API
async function loadData() {
  try {
    loading.value = true;
    error.value = null;

    // Check if we have scope parameters - if not, don't make API calls
    if (
      !urlConfig.value.collections.scopelist ||
      urlConfig.value.collections.scopelist.length === 0
    ) {
      console.log("No scope parameters provided, skipping API calls");
      calldisplay.value = null;
      queue.value = null;
      scope.value = null;
      loading.value = false;
      return;
    }

    // Load calldisplay configuration
    console.log(
      "URL Config being passed to API:",
      JSON.stringify(urlConfig.value, null, 2),
    );
    calldisplay.value = await api.getCalldisplay(urlConfig.value);

    // Load queue data
    const statusList = urlConfig.value.queue?.status || ["called", "pickup"];
    queue.value = await api.getQueue(urlConfig.value, statusList);

    // Load scope if only one scope
    if (calldisplay.value.scopes && calldisplay.value.scopes.length === 1) {
      scope.value = await api.getScope(calldisplay.value.scopes[0].id);
    }

    // Load config (optional - don't block on error)
    try {
      config.value = await api.getConfig();
    } catch (err) {
      console.warn("Config API error:", err);
      // Use default config if API fails
      config.value = {
        template: "default_counter",
        display: "1",
        refreshInterval: 5000,
        organisation: {
          name: "Kreisverwaltungsreferat",
          id: 4,
        },
        preferences: {
          ticketPrinterProtectionEnabled: true,
        },
      };
    }
  } catch (err) {
    error.value = err instanceof Error ? err.message : "Unknown error occurred";
    console.error("Error loading calldisplay data:", err);
  } finally {
    loading.value = false;
  }
}

// Refresh data without showing loading state
async function refreshData() {
  refreshing.value = true;
  try {
    // Check if we have scope parameters - if not, don't make API calls
    if (
      !urlConfig.value.collections.scopelist ||
      urlConfig.value.collections.scopelist.length === 0
    ) {
      console.log("No scope parameters provided, skipping refresh API calls");
      refreshing.value = false;
      return;
    }

    // Load calldisplay data
    calldisplay.value = await api.getCalldisplay(urlConfig.value);

    // Load queue data
    const statusList = urlConfig.value.queue?.status || ["called", "pickup"];
    queue.value = await api.getQueue(urlConfig.value, statusList);

    // Load scope if only one scope
    if (calldisplay.value.scopes && calldisplay.value.scopes.length === 1) {
      scope.value = await api.getScope(calldisplay.value.scopes[0].id);
    }

    // Load config (optional - don't block on error)
    try {
      config.value = await api.getConfig();
    } catch (err) {
      console.warn("Config API error:", err);
      // Use default config if API fails
      config.value = {
        template: "default_counter",
        display: "1",
        refreshInterval: 5000,
        organisation: {
          name: "Kreisverwaltungsreferat",
          id: 4,
        },
        preferences: {
          ticketPrinterProtectionEnabled: true,
        },
      };
    }

    // Clear error state if API calls succeed
    error.value = null;
  } catch (err) {
    console.error("Error refreshing calldisplay data:", err);
    // Set error state on refresh to show error to user
    error.value = err instanceof Error ? err.message : "Unknown error occurred";
  } finally {
    refreshing.value = false;
  }
}

// Start auto-refresh
function startAutoRefresh() {
  // Refresh every 5 seconds
  refreshInterval = window.setInterval(refreshData, 5000);
}

// Stop auto-refresh
function stopAutoRefresh() {
  if (refreshInterval) {
    clearInterval(refreshInterval);
    refreshInterval = null;
  }
}

// Lifecycle hooks
onMounted(async () => {
  parseUrlParameters();
  await loadData();
  startAutoRefresh();
});

onUnmounted(() => {
  stopAutoRefresh();
});
</script>

<style scoped>
.calldisplay-app {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.loading {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 200px;
  gap: 1rem;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #f3f3f3;
  border-top: 4px solid #3498db;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}

.calldisplay-content {
  flex: 1;
}
</style>
