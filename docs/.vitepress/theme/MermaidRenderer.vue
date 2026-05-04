<template>
  <div style="display: none" />
</template>

<script setup>
import mermaid from "mermaid";
import { useRoute } from "vitepress";
import { nextTick, onMounted, watch } from "vue";

const route = useRoute();

const renderMermaid = async () => {
  await nextTick();
  const nodes = Array.from(document.querySelectorAll(".vp-doc pre.mermaid"));
  if (!nodes.length) {
    return;
  }

  nodes.forEach((node) => node.removeAttribute("data-processed"));
  await mermaid.run({ nodes });
};

onMounted(async () => {
  mermaid.initialize({
    startOnLoad: false,
    securityLevel: "loose",
  });

  await renderMermaid();
});

watch(
  () => route.path,
  async () => {
    await renderMermaid();
  }
);
</script>
