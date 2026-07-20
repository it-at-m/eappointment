<script setup>
import CodeExplorer from "./CodeExplorer.vue";
import { defaultPath, files, tree } from "./data/departmentExplorerToday.js";
</script>

<template>
  <CodeExplorer
    root-label="ZMS — today"
    :tree="tree"
    :files="files"
    :default-path="defaultPath"
  />
</template>
