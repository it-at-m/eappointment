<script setup>
import { computed, onMounted, ref } from "vue";

import CodeExplorerTreeNode from "./CodeExplorerTreeNode.vue";

const props = defineProps({
  title: {
    type: String,
    default: "",
  },
  rootLabel: {
    type: String,
    default: "EXPLORER",
  },
  tree: {
    type: Array,
    required: true,
  },
  files: {
    type: Object,
    required: true,
  },
  defaultPath: {
    type: String,
    default: "",
  },
});

const selectedPath = ref("");
const expandedFolders = ref(new Set());

function collectFolderPaths(nodes, prefix = "") {
  for (const node of nodes) {
    if (node.type === "folder") {
      const folderPath = prefix ? `${prefix}/${node.name}` : node.name;
      expandedFolders.value.add(folderPath);
      if (node.children?.length) {
        collectFolderPaths(node.children, folderPath);
      }
    }
  }
}

function firstFilePath(nodes) {
  for (const node of nodes) {
    if (node.type === "file" && node.path) {
      return node.path;
    }
    if (node.children?.length) {
      const nested = firstFilePath(node.children);
      if (nested) {
        return nested;
      }
    }
  }
  return "";
}

onMounted(() => {
  collectFolderPaths(props.tree);
  selectedPath.value =
    props.defaultPath ||
    firstFilePath(props.tree) ||
    Object.keys(props.files)[0] ||
    "";
});

const currentFile = computed(() => props.files[selectedPath.value] ?? null);

const lineCount = computed(() => {
  if (!currentFile.value?.content) {
    return 0;
  }
  return currentFile.value.content.split("\n").length;
});

const codeText = computed(() => currentFile.value?.content ?? "");

function toggleFolder(folderPath) {
  if (expandedFolders.value.has(folderPath)) {
    expandedFolders.value.delete(folderPath);
  } else {
    expandedFolders.value.add(folderPath);
  }
}

function selectFile(path) {
  if (props.files[path]) {
    selectedPath.value = path;
  }
}

function fileName(path) {
  return path.split("/").pop() ?? path;
}
</script>

<template>
  <div class="code-explorer">
    <div
      v-if="title"
      class="code-explorer__heading"
    >
      {{ title }}
    </div>
    <div class="code-explorer__shell">
      <aside class="code-explorer__sidebar">
        <div class="code-explorer__sidebar-title">{{ rootLabel }}</div>
        <ul class="code-explorer__tree">
          <CodeExplorerTreeNode
            v-for="node in tree"
            :key="node.name"
            :node="node"
            :selected-path="selectedPath"
            :expanded-folders="expandedFolders"
            @toggle-folder="toggleFolder"
            @select-file="selectFile"
          />
        </ul>
      </aside>
      <section class="code-explorer__editor">
        <div class="code-explorer__tab-bar">
          <div
            v-if="selectedPath"
            class="code-explorer__tab code-explorer__tab--active"
          >
            {{ fileName(selectedPath) }}
          </div>
        </div>
        <div
          v-if="currentFile"
          class="code-explorer__editor-body"
        >
          <div
            class="code-explorer__gutter"
            aria-hidden="true"
          >
            <span
              v-for="n in lineCount"
              :key="n"
              >{{ n }}</span
            >
          </div>
          <pre
            class="code-explorer__code"
            :class="`code-explorer__code--${currentFile.language}`"
          ><code>{{ codeText }}</code></pre>
        </div>
        <div
          v-else
          class="code-explorer__empty"
        >
          Select a file in the explorer.
        </div>
      </section>
    </div>
  </div>
</template>

<style scoped>
.code-explorer {
  margin: 1.25rem 0 2rem;
}

.code-explorer__heading {
  font-size: 0.95rem;
  font-weight: 600;
  margin-bottom: 0.5rem;
  color: var(--vp-c-text-1);
}

.code-explorer__shell {
  display: flex;
  min-height: 420px;
  max-height: 640px;
  border: 1px solid var(--vp-c-divider);
  border-radius: 8px;
  overflow: hidden;
  background: #1e1e1e;
  color: #d4d4d4;
  font-family:
    ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono",
    "Courier New", monospace;
  font-size: 12.5px;
  line-height: 1.55;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.18);
}

.code-explorer__sidebar {
  width: min(52%, 480px);
  min-width: 340px;
  background: #252526;
  border-right: 1px solid #1e1e1e;
  overflow: auto;
  flex-shrink: 0;
}

.code-explorer__sidebar-title {
  padding: 8px 12px;
  font-size: 11px;
  letter-spacing: 0.06em;
  text-transform: uppercase;
  color: #bbbbbb;
  border-bottom: 1px solid #1e1e1e;
}

.code-explorer__tree {
  list-style: none;
  margin: 0;
  padding: 4px 0 8px;
}

.code-explorer__editor {
  flex: 1;
  display: flex;
  flex-direction: column;
  min-width: 0;
  background: #1e1e1e;
}

.code-explorer__tab-bar {
  display: flex;
  background: #2d2d2d;
  border-bottom: 1px solid #252526;
  min-height: 35px;
}

.code-explorer__tab {
  padding: 8px 14px;
  color: #969696;
  border-right: 1px solid #252526;
  white-space: nowrap;
}

.code-explorer__tab--active {
  background: #1e1e1e;
  color: #ffffff;
}

.code-explorer__editor-body {
  display: flex;
  flex: 1;
  overflow: auto;
}

.code-explorer__gutter {
  padding: 12px 10px 12px 8px;
  text-align: right;
  color: #858585;
  user-select: none;
  border-right: 1px solid #252526;
  flex-shrink: 0;
}

.code-explorer__gutter span {
  display: block;
}

.code-explorer__code {
  margin: 0;
  padding: 12px 16px;
  overflow: visible;
  white-space: pre;
  color: #d4d4d4;
  tab-size: 4;
}

.code-explorer__empty {
  padding: 24px;
  color: #858585;
}

:deep(.ce-tree) {
  list-style: none;
  margin: 0;
  padding: 0;
}

:deep(.ce-tree-item) {
  margin: 0;
  padding: 0;
}

:deep(.ce-tree-row) {
  display: flex;
  align-items: center;
  gap: 6px;
  width: 100%;
  border: 0;
  background: transparent;
  color: #cccccc;
  text-align: left;
  cursor: pointer;
  padding-top: 3px;
  padding-bottom: 3px;
  padding-right: 8px;
  font: inherit;
}

:deep(.ce-tree-row:hover) {
  background: #2a2d2e;
}

:deep(.ce-tree-row--active) {
  background: #37373d;
  color: #ffffff;
}

:deep(.ce-tree-icon) {
  width: 14px;
  flex-shrink: 0;
  color: #858585;
  font-size: 10px;
}

:deep(.ce-tree-icon--file) {
  font-size: 11px;
}

:deep(.ce-tree-label) {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

@media (max-width: 768px) {
  .code-explorer__shell {
    flex-direction: column;
    max-height: none;
  }

  .code-explorer__sidebar {
    width: 100%;
    max-height: 220px;
    border-right: 0;
    border-bottom: 1px solid #1e1e1e;
  }
}
</style>
