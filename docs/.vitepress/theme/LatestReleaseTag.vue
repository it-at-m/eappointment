<template>
  <template v-if="tagName">
    <!-- Desktop: second line under title (inside title link; use span + handlers) -->
    <template v-if="variant === 'title'">
      <span
        class="title-after-break"
        aria-hidden="true"
      />
      <span
        class="latest-release latest-release--title"
        aria-label="Latest GitHub release"
      >
        <span class="latest-release__label">Latest release</span>
        <span
          class="latest-release__link"
          role="link"
          tabindex="0"
          :title="tagUrl"
          @click.capture.prevent.stop="openRelease"
          @keydown.enter.capture.prevent.stop="openRelease"
          @keydown.space.capture.prevent.stop="openRelease"
          >{{ tagName }}</span
        >
      </span>
    </template>
    <!-- Mobile nav drawer: below Appearance (grid reorder in style.css) -->
    <div
      v-else
      class="latest-release latest-release--menu"
      aria-label="Latest GitHub release"
    >
      <span class="latest-release__label">Latest release</span>
      <a
        class="latest-release__menu-link"
        :href="tagUrl"
        target="_blank"
        rel="noopener noreferrer"
        >{{ tagName }}</a
      >
    </div>
  </template>
</template>

<script setup>
<<<<<<< HEAD
<<<<<<< HEAD
import { onMounted, ref } from "vue";

import { fetchLatestRelease } from "./latestReleaseFetch.js";

defineProps({
  variant: {
    type: String,
    required: true,
    validator: (v) => v === "title" || v === "menu",
  },
});
=======
import { ref, onMounted } from "vue";
=======
import { onMounted, ref } from "vue";

>>>>>>> 9ad782f99 (docs(ZMSKVR): add Prettier and format docs tree)
import { fetchLatestRelease } from "./latestReleaseFetch.js";

<<<<<<< HEAD
const LATEST_API = "https://api.github.com/repos/it-at-m/eappointment/releases/latest";
>>>>>>> f1b63ae05 (docs(ZMSKVR): add Releases nav and dynamic latest tag)
=======
defineProps({
  variant: {
    type: String,
    required: true,
    validator: (v) => v === "title" || v === "menu",
  },
});
>>>>>>> 621d5a5ea (docs(ZMSKVR): refine latest release UI and top nav)

const tagName = ref("");
const tagUrl = ref("");

const openRelease = () => {
  if (tagUrl.value) {
    window.open(tagUrl.value, "_blank", "noopener,noreferrer");
  }
};

onMounted(async () => {
<<<<<<< HEAD
<<<<<<< HEAD
  const data = await fetchLatestRelease();
  tagName.value = data.tagName;
  tagUrl.value = data.tagUrl;
=======
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
>>>>>>> f1b63ae05 (docs(ZMSKVR): add Releases nav and dynamic latest tag)
=======
  const data = await fetchLatestRelease();
  tagName.value = data.tagName;
  tagUrl.value = data.tagUrl;
>>>>>>> 621d5a5ea (docs(ZMSKVR): refine latest release UI and top nav)
});
</script>

<style scoped>
.title-after-break {
  flex-basis: 100%;
  width: 0;
  height: 0;
  overflow: hidden;
}

<<<<<<< HEAD
<<<<<<< HEAD
.latest-release--title {
=======
.latest-release {
>>>>>>> f1b63ae05 (docs(ZMSKVR): add Releases nav and dynamic latest tag)
=======
.latest-release--title {
>>>>>>> 621d5a5ea (docs(ZMSKVR): refine latest release UI and top nav)
  margin-top: -2px;
  margin-left: 0;
  padding-left: 0;
  border-left: none;
<<<<<<< HEAD
<<<<<<< HEAD
  width: 100%;
  display: flex;
  flex-direction: row;
  flex-wrap: wrap;
  align-items: baseline;
  gap: 8px;
  line-height: 1.2;
}

.latest-release--title .latest-release__label {
  flex-shrink: 0;
}

.latest-release__label {
  font-size: 11px;
  font-weight: 600;
  color: var(--vp-c-text-2);
  line-height: 1.3;
}

.latest-release--menu .latest-release__label {
  flex-shrink: 0;
}

.latest-release__link {
  font-size: 11px;
  font-weight: 500;
=======
  font-size: 11px;
  font-weight: 500;
  line-height: 1.2;
=======
>>>>>>> 621d5a5ea (docs(ZMSKVR): refine latest release UI and top nav)
  width: 100%;
  display: flex;
  flex-direction: row;
  flex-wrap: wrap;
  align-items: baseline;
  gap: 8px;
  line-height: 1.2;
}

.latest-release--title .latest-release__label {
  flex-shrink: 0;
}

.latest-release__label {
  font-size: 11px;
  font-weight: 600;
  color: var(--vp-c-text-2);
  line-height: 1.3;
}

.latest-release--menu .latest-release__label {
  flex-shrink: 0;
}

.latest-release__link {
<<<<<<< HEAD
>>>>>>> f1b63ae05 (docs(ZMSKVR): add Releases nav and dynamic latest tag)
=======
  font-size: 11px;
  font-weight: 500;
>>>>>>> 621d5a5ea (docs(ZMSKVR): refine latest release UI and top nav)
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
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> 621d5a5ea (docs(ZMSKVR): refine latest release UI and top nav)

.latest-release--menu {
  margin-top: 0;
  padding-top: 16px;
  border-top: 1px solid var(--vp-c-divider);
  display: flex;
  flex-direction: row;
  flex-wrap: wrap;
  align-items: baseline;
  gap: 8px;
}

.latest-release__menu-link {
  font-size: 11px;
  font-weight: 500;
  color: var(--vp-c-brand-1);
  text-decoration: none;
  min-width: 0;
  white-space: normal;
  word-break: break-word;
}

.latest-release__menu-link:hover {
  text-decoration: underline;
}
<<<<<<< HEAD
=======
>>>>>>> f1b63ae05 (docs(ZMSKVR): add Releases nav and dynamic latest tag)
=======
>>>>>>> 621d5a5ea (docs(ZMSKVR): refine latest release UI and top nav)
</style>
