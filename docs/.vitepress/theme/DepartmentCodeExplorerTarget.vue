<script setup>
import CodeExplorer from "./CodeExplorer.vue";
import { defaultPath, files, tree } from "./data/departmentExplorerTarget.js";
</script>

<template>
  <CodeExplorer
    root-label="zmsbackend — target"
    :tree="tree"
    :files="files"
    :default-path="defaultPath"
  />
</template>
