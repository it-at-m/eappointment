<script setup>
import { useData } from "vitepress";
import { computed } from "vue";

import {
  cucumberCatalogEntries,
  cucumberCatalogLoadState,
  cucumberUsesRemoteCatalog,
} from "./cucumberAccordion.js";

const { lang } = useData();

const isDe = computed(() => lang.value === "de");

const categoryOf = (entry) => {
  if (entry.category) {
    return entry.category;
  }
  const parts = String(entry.rel || "").split("/");
  return parts.length >= 4 ? parts[2] : "";
};

const categoryLabel = (category) => {
  if (category) {
    return category;
  }
  return isDe.value ? "Unkategorisiert" : "Uncategorized";
};

const typeLabel = (testType) => {
  if (testType === "rest") {
    return "REST";
  }
  if (testType === "ui") {
    return "UI";
  }
  return testType;
};

const sections = computed(() => {
  const byType = new Map();
  for (const entry of cucumberCatalogEntries()) {
    const testType = entry.testType || "other";
    const moduleName = entry.module || "misc";
    const category = categoryOf(entry);
    const count = Number(entry.scenarioCount) || 0;
    if (!byType.has(testType)) {
      byType.set(testType, new Map());
    }
    const modules = byType.get(testType);
    if (!modules.has(moduleName)) {
      modules.set(moduleName, new Map());
    }
    const counts = modules.get(moduleName);
    counts.set(category, (counts.get(category) || 0) + count);
  }

  const typeOrder = ["rest", "ui"];
  return [...byType.keys()]
    .sort((a, b) => {
      const ai = typeOrder.indexOf(a);
      const bi = typeOrder.indexOf(b);
      if (ai !== -1 || bi !== -1) {
        return (ai === -1 ? 99 : ai) - (bi === -1 ? 99 : bi);
      }
      return a.localeCompare(b);
    })
    .map((testType) => {
      const modules = byType.get(testType);
      const categories = [
        ...new Set(
          [...modules.values()].flatMap((counts) => [...counts.keys()])
        ),
      ].sort((a, b) => {
        if (!a) {
          return 1;
        }
        if (!b) {
          return -1;
        }
        return a.localeCompare(b);
      });
      const rows = [...modules.keys()]
        .sort((a, b) => a.localeCompare(b))
        .map((moduleName) => {
          const counts = modules.get(moduleName);
          const cells = categories.map((category) => counts.get(category) || 0);
          return {
            module: moduleName,
            cells,
            total: cells.reduce((sum, count) => sum + count, 0),
          };
        });
      const columnTotals = categories.map((_, index) =>
        rows.reduce((sum, row) => sum + row.cells[index], 0)
      );
      return {
        testType,
        categories,
        rows,
        columnTotals,
        total: columnTotals.reduce((sum, count) => sum + count, 0),
      };
    });
});

const combined = computed(() => {
  const categories = [
    ...new Set(sections.value.flatMap((section) => section.categories)),
  ].sort((a, b) => {
    if (!a) {
      return 1;
    }
    if (!b) {
      return -1;
    }
    return a.localeCompare(b);
  });
  const rows = sections.value.map((section) => {
    const cells = categories.map((category) => {
      const index = section.categories.indexOf(category);
      return index === -1 ? 0 : section.columnTotals[index];
    });
    return {
      label: typeLabel(section.testType),
      cells,
      total: section.total,
    };
  });
  const columnTotals = categories.map((_, index) =>
    rows.reduce((sum, row) => sum + row.cells[index], 0)
  );
  return {
    categories,
    rows,
    columnTotals,
    total: rows.reduce((sum, row) => sum + row.total, 0),
  };
});

const loading = computed(
  () =>
    cucumberUsesRemoteCatalog() && cucumberCatalogLoadState.value === "loading"
);

const title = computed(() =>
  isDe.value ? "Anzahl der Szenarien" : "Scenario counts"
);

const totalLabel = computed(() => (isDe.value ? "Summe" : "Total"));

const moduleLabel = computed(() => (isDe.value ? "Modul" : "Module"));

const loadingLabel = computed(() =>
  isDe.value ? "Szenario-Anzahl wird geladen…" : "Loading scenario counts…"
);
</script>

<template>
  <section
    v-if="loading || sections.length"
    class="cucumber-count-matrix"
    :aria-label="title"
  >
    <h2>{{ title }}</h2>
    <p
      v-if="loading"
      class="cucumber-count-matrix__loading"
    >
      {{ loadingLabel }}
    </p>
    <div
      v-for="section in sections"
      :key="section.testType"
      class="cucumber-count-matrix__table-wrap"
    >
      <table>
        <caption>
          {{
            typeLabel(section.testType)
          }}
        </caption>
        <thead>
          <tr>
            <th scope="col">{{ moduleLabel }}</th>
            <th scope="col">{{ totalLabel }}</th>
            <th
              v-for="category in section.categories"
              :key="category || 'uncategorized'"
              scope="col"
            >
              {{ categoryLabel(category) }}
            </th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="row in section.rows"
            :key="row.module"
          >
            <th scope="row">{{ row.module }}</th>
            <td>{{ row.total }}</td>
            <td
              v-for="(count, index) in row.cells"
              :key="`${row.module}-${section.categories[index]}`"
            >
              {{ count || "–" }}
            </td>
          </tr>
        </tbody>
        <tfoot>
          <tr>
            <th scope="row">{{ totalLabel }}</th>
            <td>{{ section.total }}</td>
            <td
              v-for="(count, index) in section.columnTotals"
              :key="`total-${section.categories[index]}`"
            >
              {{ count }}
            </td>
          </tr>
        </tfoot>
      </table>
    </div>
    <div
      v-if="combined.rows.length"
      class="cucumber-count-matrix__table-wrap"
    >
      <table>
        <caption>
          {{
            totalLabel
          }}
        </caption>
        <thead>
          <tr>
            <th scope="col">{{ isDe ? "REST und UI" : "REST and UI" }}</th>
            <th scope="col">{{ totalLabel }}</th>
            <th
              v-for="category in combined.categories"
              :key="category || 'uncategorized'"
              scope="col"
            >
              {{ categoryLabel(category) }}
            </th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="row in combined.rows"
            :key="row.label"
          >
            <th scope="row">{{ row.label }}</th>
            <td>{{ row.total }}</td>
            <td
              v-for="(count, index) in row.cells"
              :key="`${row.label}-${combined.categories[index]}`"
            >
              {{ count || "–" }}
            </td>
          </tr>
        </tbody>
        <tfoot>
          <tr>
            <th scope="row">{{ totalLabel }}</th>
            <td>{{ combined.total }}</td>
            <td
              v-for="(count, index) in combined.columnTotals"
              :key="`combined-${combined.categories[index]}`"
            >
              {{ count }}
            </td>
          </tr>
        </tfoot>
      </table>
    </div>
  </section>
</template>

<style scoped>
.cucumber-count-matrix {
  margin: 0.25rem 0 1.75rem;
}

.cucumber-count-matrix h2 {
  margin: 0 0 0.75rem;
  font-size: 1.15rem;
}

.cucumber-count-matrix__loading {
  margin: 0;
  color: var(--vp-c-text-2);
}

.cucumber-count-matrix__table-wrap {
  overflow-x: auto;
  margin: 0 0 1rem;
}

.cucumber-count-matrix table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.9rem;
}

.cucumber-count-matrix caption {
  padding: 0 0 0.4rem;
  font-weight: 600;
  text-align: left;
}

.cucumber-count-matrix th,
.cucumber-count-matrix td {
  padding: 0.4rem 0.65rem;
  border-bottom: 1px solid var(--vp-c-divider);
  text-align: right;
  white-space: nowrap;
}

.cucumber-count-matrix th:first-child,
.cucumber-count-matrix td:first-child {
  text-align: left;
}

.cucumber-count-matrix thead th {
  color: var(--vp-c-text-2);
  font-weight: 600;
}

.cucumber-count-matrix tfoot th,
.cucumber-count-matrix tfoot td,
.cucumber-count-matrix th:nth-child(2),
.cucumber-count-matrix td:nth-child(2) {
  font-weight: 600;
}
</style>
