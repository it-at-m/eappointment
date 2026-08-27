import { ref } from "vue";

/** Exclusive open id for cucumber feature rows on the generated docs page. */
export const openCucumberFeatureId = ref(null);

const featureHashPrefix = "feature-";

const currentHash = () => {
  if (typeof window === "undefined") {
    return "";
  }
  return window.location.hash.replace(/^#/, "");
};

const setFeatureHash = (id) => {
  if (typeof window === "undefined") {
    return;
  }
  const url = new URL(window.location.href);
  if (id) {
    url.hash = id;
  } else {
    url.hash = "";
  }
  history.replaceState(null, "", url);
};

export function toggleCucumberFeature(id) {
  if (openCucumberFeatureId.value === id) {
    openCucumberFeatureId.value = null;
    if (currentHash() === id) {
      setFeatureHash("");
    }
    return;
  }
  openCucumberFeatureId.value = id;
  setFeatureHash(id);
}

export function syncCucumberFeatureFromHash() {
  const hash = currentHash();
  if (hash.startsWith(featureHashPrefix)) {
    openCucumberFeatureId.value = hash;
  }
}

let hashListenerBound = false;

export function ensureCucumberHashListener() {
  if (typeof window === "undefined" || hashListenerBound) {
    return;
  }
  hashListenerBound = true;
  window.addEventListener("hashchange", syncCucumberFeatureFromHash);
}
