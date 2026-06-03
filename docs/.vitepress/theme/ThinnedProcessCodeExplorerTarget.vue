<script setup>
import CodeExplorer from "./CodeExplorer.vue";
import {
  defaultPath,
  files,
  tree,
} from "./data/thinnedProcessExplorerTarget.js";
</script>

<template>
  <CodeExplorer
    root-label="zmscitizenbackend — target"
    :tree="tree"
    :files="files"
    :default-path="defaultPath"
  />
</template>
