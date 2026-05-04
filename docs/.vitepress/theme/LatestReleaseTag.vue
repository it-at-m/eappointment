<template>
  <template v-if="tagName">
    <span class="title-after-break" aria-hidden="true" />
    <span class="latest-release" aria-label="Latest GitHub release">
    <span
      class="latest-release__link"
      role="link"
      tabindex="0"
      :title="tagUrl"
      @click.capture.prevent.stop="openRelease"
      @keydown.enter.capture.prevent.stop="openRelease"
      @keydown.space.capture.prevent.stop="openRelease"
    >{{ tagName }}</span>
    </span>
  </template>
</template>

<script setup>
import { ref, onMounted } from "vue";

const LATEST_API = "https://api.github.com/repos/it-at-m/eappointment/releases/latest";

const tagName = ref("");
const tagUrl = ref("");

const openRelease = () => {
  if (tagUrl.value) {
    window.open(tagUrl.value, "_blank", "noopener,noreferrer");
  }
};

onMounted(async () => {
  try {
    const res = await fetch(LATEST_API, {
      headers: { Accept: "application/vnd.github+json" }
    });
    if (!res.ok) {
      return;
    }
    const data = await res.json();
    const name = data.tag_name;
    const url = data.html_url;
    if (typeof name === "string" && name && typeof url === "string" && url) {
      tagName.value = name;
      tagUrl.value = url;
    }
  } catch {
    /* ignore: rate limit, offline, ad blockers */
  }
});
</script>

<style scoped>
.title-after-break {
  flex-basis: 100%;
  width: 0;
  height: 0;
  overflow: hidden;
}

.latest-release {
  margin-top: -2px;
  margin-left: 0;
  padding-left: 0;
  border-left: none;
  font-size: 11px;
  font-weight: 500;
  line-height: 1.2;
  width: 100%;
}

.latest-release__link {
  color: var(--vp-c-brand-1);
  cursor: pointer;
  text-decoration: none;
  white-space: nowrap;
}

.latest-release__link:hover {
  text-decoration: underline;
}

.latest-release__link:focus-visible {
  outline: 2px solid var(--vp-c-brand-1);
  outline-offset: 2px;
  border-radius: 2px;
}
</style>
