<script setup>
const props = defineProps({
  node: { type: Object, required: true },
  depth: { type: Number, default: 0 },
  prefix: { type: String, default: "" },
  selectedPath: { type: String, required: true },
  expandedFolders: { type: Object, required: true },
});

const emit = defineEmits(["toggle-folder", "select-file"]);

const folderPath = props.prefix
  ? `${props.prefix}/${props.node.name}`
  : props.node.name;

const paddingLeft = `${props.depth * 12 + 8}px`;

function onToggleFolder() {
  emit("toggle-folder", folderPath);
}

function onSelectFile() {
  emit("select-file", props.node.path);
}

function fileIcon(language) {
  if (language === "java") {
    return "☕";
  }
  if (language === "json") {
    return "{}";
  }
  return "◆";
}
</script>

<template>
  <li class="ce-tree-item">
    <button
      v-if="node.type === 'folder'"
      type="button"
      class="ce-tree-row ce-tree-row--folder"
      :style="{ paddingLeft }"
      @click="onToggleFolder"
    >
      <span class="ce-tree-icon">{{
        expandedFolders.has(folderPath) ? "▼" : "▶"
      }}</span>
      <span class="ce-tree-label">{{ node.name }}</span>
    </button>

    <button
      v-else
      type="button"
      class="ce-tree-row ce-tree-row--file"
      :class="{ 'ce-tree-row--active': selectedPath === node.path }"
      :style="{ paddingLeft }"
      @click="onSelectFile"
    >
      <span class="ce-tree-icon ce-tree-icon--file">{{
        fileIcon(node.language)
      }}</span>
      <span class="ce-tree-label">{{ node.name }}</span>
    </button>

    <ul
      v-if="
        node.type === 'folder' &&
        expandedFolders.has(folderPath) &&
        node.children?.length
      "
      class="ce-tree"
    >
      <CodeExplorerTreeNode
        v-for="child in node.children"
        :key="`${folderPath}/${child.name}`"
        :node="child"
        :depth="depth + 1"
        :prefix="folderPath"
        :selected-path="selectedPath"
        :expanded-folders="expandedFolders"
        @toggle-folder="emit('toggle-folder', $event)"
        @select-file="emit('select-file', $event)"
      />
    </ul>
  </li>
</template>

<script>
export default {
  name: "CodeExplorerTreeNode",
};
</script>
