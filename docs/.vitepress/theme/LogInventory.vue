<script setup>
import { computed, ref } from "vue";

import inventoryData from "../data/log-inventory.json";

const inventory = ref(inventoryData);
const levelFilter = ref("all");
const moduleFilter = ref("all");
const search = ref("");
const sortKey = ref("module");
const sortDir = ref("asc");

const levelOptions = computed(() => ["all", ...inventory.value.levels]);

const moduleOptions = computed(() => ["all", ...inventory.value.modules]);

const levelRank = {
  alert: 7,
  critical: 6,
  debug: 0,
  dynamic: 99,
  emergency: 8,
  error: 5,
  info: 1,
  notice: 2,
  warning: 4,
};

const columns = [
  { key: "module", label: "Module" },
  { key: "level", label: "Level" },
  { key: "message", label: "Message" },
  { key: "location", label: "Location" },
];

const filteredEntries = computed(() => {
  const q = search.value.trim().toLowerCase();
  return inventory.value.entries.filter((entry) => {
    if (levelFilter.value !== "all" && entry.level !== levelFilter.value) {
      return false;
    }
    if (moduleFilter.value !== "all" && entry.module !== moduleFilter.value) {
      return false;
    }
    if (!q) {
      return true;
    }
    const haystack =
      `${entry.module} ${entry.level} ${entry.message} ${entry.file}`.toLowerCase();
    return haystack.includes(q);
  });
});

const compareEntries = (a, b, key, dir) => {
  let cmp = 0;
  if (key === "module") {
    cmp = a.module.localeCompare(b.module) || a.file.localeCompare(b.file);
  } else if (key === "level") {
    cmp =
      (levelRank[a.level] ?? 50) - (levelRank[b.level] ?? 50) ||
      a.module.localeCompare(b.module);
  } else if (key === "message") {
    cmp =
      a.message.localeCompare(b.message, undefined, { sensitivity: "base" }) ||
      a.module.localeCompare(b.module);
  } else if (key === "location") {
    cmp =
      a.file.localeCompare(b.file) ||
      a.line - b.line ||
      a.module.localeCompare(b.module);
  }
  return dir === "asc" ? cmp : -cmp;
};

const sortedEntries = computed(() => {
  const rows = [...filteredEntries.value];
  rows.sort((a, b) => compareEntries(a, b, sortKey.value, sortDir.value));
  return rows;
});

const toggleSort = (key) => {
  if (sortKey.value === key) {
    sortDir.value = sortDir.value === "asc" ? "desc" : "asc";
    return;
  }
  sortKey.value = key;
  sortDir.value = "asc";
};

const sortIndicator = (key) => {
  if (sortKey.value !== key) {
    return "↕";
  }
  return sortDir.value === "asc" ? "↑" : "↓";
};

const githubBase = "https://github.com/it-at-m/eappointment/blob/main/";
</script>

<template>
  <div class="log-inventory">
    <p class="log-inventory-meta">
      Generated:
      <time :datetime="inventory.generatedAt">{{
        new Date(inventory.generatedAt).toLocaleString()
      }}</time>
      · {{ inventory.totals.entries }} call(s) · {{ inventory.scanNote }}
    </p>

    <div class="log-inventory-filters">
      <label>
        Level
        <select v-model="levelFilter">
          <option
            v-for="opt in levelOptions"
            :key="opt"
            :value="opt"
          >
            {{ opt === "all" ? "All levels" : opt }}
          </option>
        </select>
      </label>
      <label>
        Module
        <select v-model="moduleFilter">
          <option
            v-for="opt in moduleOptions"
            :key="opt"
            :value="opt"
          >
            {{ opt === "all" ? "All modules" : opt }}
          </option>
        </select>
      </label>
      <label class="log-inventory-search">
        Search
        <input
          v-model="search"
          type="search"
          placeholder="message, file, module…"
        />
      </label>
    </div>

    <p class="log-inventory-count">
      Showing {{ sortedEntries.length }} of
      {{ inventory.totals.entries }} entries
      <span class="log-inventory-sort-hint">
        · sorted by {{ sortKey }} ({{ sortDir }})
      </span>
    </p>

    <div class="log-inventory-table-wrap">
      <table>
        <thead>
          <tr>
            <th
              v-for="col in columns"
              :key="col.key"
              scope="col"
            >
              <button
                type="button"
                class="log-inventory-sort"
                :class="{ 'log-inventory-sort--active': sortKey === col.key }"
                :aria-sort="
                  sortKey === col.key
                    ? sortDir === 'asc'
                      ? 'ascending'
                      : 'descending'
                    : 'none'
                "
                @click="toggleSort(col.key)"
              >
                {{ col.label }}
                <span class="log-inventory-sort-icon" aria-hidden="true">{{
                  sortIndicator(col.key)
                }}</span>
              </button>
            </th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="row in sortedEntries"
            :key="`${row.module}:${row.file}:${row.line}`"
          >
            <td>
              <code>{{ row.module }}</code>
            </td>
            <td>
              <span :class="['log-level', `log-level--${row.level}`]">{{
                row.level
              }}</span>
            </td>
            <td>{{ row.message }}</td>
            <td>
              <a
                :href="`${githubBase}${row.file}#L${row.line}`"
                target="_blank"
                rel="noopener noreferrer"
                ><code>{{ row.file }}:{{ row.line }}</code></a
              >
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<style scoped>
.log-inventory-meta {
  font-size: 0.9rem;
  color: var(--vp-c-text-2);
}

.log-inventory-filters {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  margin: 1rem 0;
  align-items: flex-end;
}

.log-inventory-filters label {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  font-size: 0.85rem;
  font-weight: 600;
}

.log-inventory-search {
  flex: 1 1 12rem;
  min-width: 12rem;
}

.log-inventory-filters select,
.log-inventory-filters input {
  padding: 0.35rem 0.5rem;
  border: 1px solid var(--vp-c-divider);
  border-radius: 4px;
  background: var(--vp-c-bg);
  color: var(--vp-c-text-1);
  font: inherit;
}

.log-inventory-count {
  font-size: 0.9rem;
  margin-bottom: 0.5rem;
}

.log-inventory-table-wrap {
  overflow-x: auto;
}

table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.88rem;
}

th,
td {
  border: 1px solid var(--vp-c-divider);
  padding: 0.45rem 0.6rem;
  text-align: left;
  vertical-align: top;
}

th {
  background: var(--vp-c-bg-soft);
  padding: 0;
}

.log-inventory-sort-hint {
  color: var(--vp-c-text-2);
}

.log-inventory-sort {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.35rem;
  width: 100%;
  padding: 0.45rem 0.6rem;
  border: 0;
  background: transparent;
  color: inherit;
  font: inherit;
  font-weight: 600;
  text-align: left;
  cursor: pointer;
}

.log-inventory-sort:hover {
  background: var(--vp-c-bg-alt);
}

.log-inventory-sort--active {
  color: var(--vp-c-brand-1);
}

.log-inventory-sort-icon {
  font-size: 0.75rem;
  opacity: 0.7;
}

.log-inventory-sort--active .log-inventory-sort-icon {
  opacity: 1;
}

.log-level {
  display: inline-block;
  padding: 0.1rem 0.4rem;
  border-radius: 3px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
}

.log-level--debug {
  background: #e8eaf6;
  color: #283593;
}
.log-level--info {
  background: #e3f2fd;
  color: #1565c0;
}
.log-level--notice {
  background: #e0f7fa;
  color: #00695c;
}
.log-level--warning {
  background: #fff8e1;
  color: #f57f17;
}
.log-level--error {
  background: #ffebee;
  color: #c62828;
}
.log-level--critical,
.log-level--alert,
.log-level--emergency {
  background: #fce4ec;
  color: #880e4f;
}
.log-level--dynamic {
  background: var(--vp-c-bg-soft);
  color: var(--vp-c-text-2);
}

.dark .log-level--debug {
  background: #1a237e;
  color: #c5cae9;
}
.dark .log-level--info {
  background: #0d47a1;
  color: #bbdefb;
}
.dark .log-level--notice {
  background: #004d40;
  color: #b2dfdb;
}
.dark .log-level--warning {
  background: #ff6f00;
  color: #fff8e1;
}
.dark .log-level--error {
  background: #b71c1c;
  color: #ffcdd2;
}
</style>
