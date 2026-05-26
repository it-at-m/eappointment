<script setup>
import { computed, ref } from "vue";
import inventoryData from "../data/log-inventory.json";

const inventory = ref(inventoryData);
const levelFilter = ref("all");
const moduleFilter = ref("all");
const search = ref("");

const levelOptions = computed(() => ["all", ...inventory.value.levels]);

const moduleOptions = computed(() => ["all", ...inventory.value.modules]);

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
    const haystack = `${entry.module} ${entry.level} ${entry.message} ${entry.file}`.toLowerCase();
    return haystack.includes(q);
  });
});

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
          <option v-for="opt in levelOptions" :key="opt" :value="opt">
            {{ opt === "all" ? "All levels" : opt }}
          </option>
        </select>
      </label>
      <label>
        Module
        <select v-model="moduleFilter">
          <option v-for="opt in moduleOptions" :key="opt" :value="opt">
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
      Showing {{ filteredEntries.length }} of
      {{ inventory.totals.entries }} entries
    </p>

    <div class="log-inventory-table-wrap">
      <table>
        <thead>
          <tr>
            <th>Module</th>
            <th>Level</th>
            <th>Message</th>
            <th>Location</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(row, idx) in filteredEntries" :key="idx">
            <td><code>{{ row.module }}</code></td>
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
