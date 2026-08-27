import { ref } from "vue";

import featureMeta from "../data/cucumber-features.json";

/** Exclusive open id for cucumber feature rows on the generated docs page. */
export const openCucumberFeatureId = ref(null);

/** Title/tag filter for the cucumber catalog (not Gherkin body). */
export const cucumberSearchQuery = ref("");

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

export function cucumberFeatureMatches(entry, query) {
  const q = (query || "").trim().toLowerCase();
  if (!q) {
    return true;
  }
  if (!entry) {
    return false;
  }
  const tags = Array.isArray(entry.tags) ? entry.tags.join(" ") : "";
  const haystack = [
    entry.title,
    entry.fileName,
    entry.rel,
    tags,
    tags.replaceAll("@", ""),
  ]
    .filter(Boolean)
    .join(" ")
    .toLowerCase();
  return q.split(/\s+/).every((token) => {
    const plain = token.replace(/^@+/, "");
    return (
      haystack.includes(token) || (plain !== "" && haystack.includes(plain))
    );
  });
}

export function cucumberCatalogEntries() {
  return Object.values(featureMeta);
}

export function cucumberGroupHasMatch(testType, module = "") {
  return cucumberCatalogEntries().some((entry) => {
    if (entry.testType !== testType) {
      return false;
    }
    if (module && entry.module !== module) {
      return false;
    }
    return cucumberFeatureMatches(entry, cucumberSearchQuery.value);
  });
}

export function closeCucumberFeatureIfHidden() {
  const id = openCucumberFeatureId.value;
  if (!id) {
    return;
  }
  if (!cucumberFeatureMatches(featureMeta[id], cucumberSearchQuery.value)) {
    openCucumberFeatureId.value = null;
  }
}
