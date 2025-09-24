<template>
  <div
    v-if="isVisible"
    class="modal-overlay"
    @click="closeModal"
    style="
      position: fixed !important;
      top: 0 !important;
      left: 0 !important;
      width: 100vw !important;
      height: 100vh !important;
      background: rgba(0, 0, 0, 0.8) !important;
      z-index: 999999 !important;
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
    "
  >
    <div
      class="modal-content"
      @click.stop
      style="
        background: white !important;
        padding: 30px !important;
        border-radius: 12px !important;
        max-width: 600px !important;
        width: 90% !important;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3) !important;
        font-family: Arial, sans-serif !important;
        position: relative !important;
        z-index: 1000000 !important;
      "
    >
      <h2
        style="
          margin-top: 0 !important;
          color: #333 !important;
          font-size: 24px !important;
          font-weight: bold !important;
        "
      >
        🔊 Enable Sound Permission
      </h2>

      <div
        class="browser-info"
        style="
          background: #e3f2fd !important;
          padding: 10px !important;
          border-radius: 6px !important;
          margin-bottom: 15px !important;
          border-left: 4px solid #2196f3 !important;
        "
      >
        <strong>🌐 Browser Detected:</strong> {{ browserInfo.name }}
      </div>

      <p style="margin: 10px 0 !important; font-size: 16px !important">
        <strong>To enable Sound permission for this site:</strong>
      </p>
      <ol style="margin: 10px 0 !important; padding-left: 20px !important">
        <li
          v-for="instruction in browserInfo.instructions"
          :key="instruction"
          style="margin: 5px 0 !important; font-size: 14px !important"
        >
          {{ instruction }}
        </li>
      </ol>

      <p style="margin: 10px 0 !important; font-size: 16px !important">
        <strong>{{ browserInfo.name }} Settings URL:</strong>
      </p>
      <div
        class="url-display"
        style="
          background: #f5f5f5 !important;
          padding: 10px !important;
          border-radius: 6px !important;
          margin: 10px 0 !important;
          font-family: monospace !important;
          word-break: break-all !important;
          border: 1px solid #ddd !important;
          font-size: 12px !important;
        "
      >
        {{ browserInfo.url }}
      </div>

      <div
        class="button-group"
        style="
          display: flex !important;
          gap: 10px !important;
          margin-top: 20px !important;
          flex-wrap: wrap !important;
        "
      >
        <button
          @click="copyUrl"
          :class="{ copied: isCopied }"
          class="btn btn-primary"
          :style="
            isCopied
              ? 'border: none !important; padding: 10px 20px !important; border-radius: 6px !important; cursor: pointer !important; font-size: 14px !important; transition: background-color 0.2s !important; background: #4CAF50 !important; color: white !important;'
              : 'border: none !important; padding: 10px 20px !important; border-radius: 6px !important; cursor: pointer !important; font-size: 14px !important; transition: background-color 0.2s !important; background: #2196F3 !important; color: white !important;'
          "
          @mouseover="
            ($event.target as HTMLButtonElement).style.opacity = '0.9'
          "
          @mouseout="($event.target as HTMLButtonElement).style.opacity = '1'"
        >
          {{ isCopied ? "✅ Copied!" : "📋 Copy URL" }}
        </button>

        <button
          @click="tryOpenSettings"
          class="btn btn-success"
          style="
            border: none !important;
            padding: 10px 20px !important;
            border-radius: 6px !important;
            cursor: pointer !important;
            font-size: 14px !important;
            transition: background-color 0.2s !important;
            background: #4caf50 !important;
            color: white !important;
          "
          @mouseover="
            ($event.target as HTMLButtonElement).style.opacity = '0.9'
          "
          @mouseout="($event.target as HTMLButtonElement).style.opacity = '1'"
        >
          🚀 Try to Open
        </button>

        <button
          @click="closeModal"
          class="btn btn-danger"
          style="
            border: none !important;
            padding: 10px 20px !important;
            border-radius: 6px !important;
            cursor: pointer !important;
            font-size: 14px !important;
            transition: background-color 0.2s !important;
            background: #f44336 !important;
            color: white !important;
          "
          @mouseover="
            ($event.target as HTMLButtonElement).style.opacity = '0.9'
          "
          @mouseout="($event.target as HTMLButtonElement).style.opacity = '1'"
        >
          ❌ Close
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from "vue";

interface BrowserInfo {
  name: string;
  url: string;
  instructions: string[];
}

interface Props {
  isVisible: boolean;
  domain: string;
}

const props = defineProps<Props>();
const emit = defineEmits<{
  close: [];
}>();

const isCopied = ref(false);

// Debug logging
console.log("SoundPermissionModal - isVisible:", props.isVisible);
console.log("SoundPermissionModal - domain:", props.domain);

// Watch for visibility changes
watch(
  () => props.isVisible,
  (newValue) => {
    console.log("SoundPermissionModal - visibility changed to:", newValue);
  },
);

// Detect browser and get appropriate settings URL
const browserInfo = computed((): BrowserInfo => {
  const userAgent = navigator.userAgent.toLowerCase();

  if (userAgent.includes("chrome") && !userAgent.includes("edg")) {
    // Chrome
    return {
      name: "Chrome",
      url: `chrome://settings/content/siteDetails?site=${encodeURIComponent(props.domain)}`,
      instructions: [
        "Copy the URL below",
        "Paste it into a new Chrome tab",
        'Find "Sound" and change it from "Automatic" to "Allow"',
        "Refresh this page",
      ],
    };
  } else if (userAgent.includes("firefox")) {
    // Firefox
    return {
      name: "Firefox",
      url: `about:preferences#privacy`,
      instructions: [
        "Copy the URL below",
        "Paste it into a new Firefox tab",
        'Go to "Permissions" section',
        'Find "Autoplay" and set it to "Allow Audio and Video"',
        "Refresh this page",
      ],
    };
  } else if (userAgent.includes("edg")) {
    // Microsoft Edge
    return {
      name: "Microsoft Edge",
      url: `edge://settings/content/siteDetails?site=${encodeURIComponent(props.domain)}`,
      instructions: [
        "Copy the URL below",
        "Paste it into a new Edge tab",
        'Find "Sound" and change it from "Automatic" to "Allow"',
        "Refresh this page",
      ],
    };
  } else if (userAgent.includes("safari") && !userAgent.includes("chrome")) {
    // Safari
    return {
      name: "Safari",
      url: `x-safari-https://${props.domain}`,
      instructions: [
        "Go to Safari menu > Preferences",
        'Click "Websites" tab',
        'Select "Auto-Play" from the left sidebar',
        'Find this site and set it to "Allow All Auto-Play"',
        "Refresh this page",
      ],
    };
  } else if (userAgent.includes("msie") || userAgent.includes("trident")) {
    // Internet Explorer
    return {
      name: "Internet Explorer",
      url: `about:blank`,
      instructions: [
        "Go to Tools > Internet Options",
        'Click "Security" tab',
        'Click "Custom Level"',
        'Find "Scripting" and enable "Active scripting"',
        'Find "Automatic prompting for file downloads" and enable it',
        "Refresh this page",
      ],
    };
  } else {
    // Unknown browser
    return {
      name: "Unknown Browser",
      url: `about:blank`,
      instructions: [
        "Please check your browser settings",
        'Look for "Sound", "Audio", or "Autoplay" permissions',
        "Enable audio for this site",
        "Refresh this page",
      ],
    };
  }
});

const copyUrl = async () => {
  try {
    await navigator.clipboard.writeText(browserInfo.value.url);
    isCopied.value = true;
    setTimeout(() => {
      isCopied.value = false;
    }, 2000);
  } catch (error) {
    // Fallback for older browsers
    const textArea = document.createElement("textarea");
    textArea.value = browserInfo.value.url;
    document.body.appendChild(textArea);
    textArea.select();
    document.execCommand("copy");
    document.body.removeChild(textArea);

    isCopied.value = true;
    setTimeout(() => {
      isCopied.value = false;
    }, 2000);
  }
};

const tryOpenSettings = () => {
  try {
    window.open(browserInfo.value.url, "_blank");
  } catch (error) {
    alert(
      `Cannot open ${browserInfo.value.name} settings automatically. Please copy the URL above and paste it in a new tab.`,
    );
  }
};

const closeModal = () => {
  emit("close");
};
</script>

<style scoped>
.modal-overlay {
  position: fixed !important;
  top: 0 !important;
  left: 0 !important;
  width: 100vw !important;
  height: 100vh !important;
  background: rgba(0, 0, 0, 0.5) !important;
  z-index: 999999 !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  margin: 0 !important;
  padding: 0 !important;
}

.modal-content {
  background: white !important;
  padding: 30px !important;
  border-radius: 12px !important;
  max-width: 600px !important;
  width: 90% !important;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3) !important;
  font-family: Arial, sans-serif !important;
  position: relative !important;
  z-index: 1000000 !important;
  margin: 0 !important;
}

.browser-info {
  background: #e3f2fd;
  padding: 10px;
  border-radius: 6px;
  margin-bottom: 15px;
  border-left: 4px solid #2196f3;
}

.url-display {
  background: #f5f5f5;
  padding: 10px;
  border-radius: 6px;
  margin: 10px 0;
  font-family: monospace;
  word-break: break-all;
  border: 1px solid #ddd;
}

.button-group {
  display: flex;
  gap: 10px;
  margin-top: 20px;
  flex-wrap: wrap;
}

.btn {
  border: none;
  padding: 10px 20px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  transition: background-color 0.2s;
}

.btn-primary {
  background: #2196f3;
  color: white;
}

.btn-primary.copied {
  background: #4caf50;
}

.btn-success {
  background: #4caf50;
  color: white;
}

.btn-danger {
  background: #f44336;
  color: white;
}

.btn:hover {
  opacity: 0.9;
}

h2 {
  margin-top: 0;
  color: #333;
}

ol {
  margin: 10px 0;
  padding-left: 20px;
}

li {
  margin: 5px 0;
}

@media (max-width: 600px) {
  .button-group {
    flex-direction: column;
  }

  .btn {
    width: 100%;
  }
}
</style>

<style>
/* Global styles to ensure modal works properly */
.modal-overlay {
  position: fixed !important;
  top: 0 !important;
  left: 0 !important;
  width: 100vw !important;
  height: 100vh !important;
  background: rgba(0, 0, 0, 0.5) !important;
  z-index: 999999 !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  margin: 0 !important;
  padding: 0 !important;
}

.modal-content {
  background: white !important;
  padding: 30px !important;
  border-radius: 12px !important;
  max-width: 600px !important;
  width: 90% !important;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3) !important;
  font-family: Arial, sans-serif !important;
  position: relative !important;
  z-index: 1000000 !important;
  margin: 0 !important;
}
</style>
