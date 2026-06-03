<script setup>
import CodeExplorer from "./CodeExplorer.vue";
import {
  defaultPath,
  files,
  tree,
} from "./data/thinnedProcessExplorerToday.js";
</script>

<template>
  <CodeExplorer
    root-label="zmscitizenapi — today"
    :tree="tree"
    :files="files"
    :default-path="defaultPath"
  />
</template>
