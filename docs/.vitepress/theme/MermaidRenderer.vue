<template>
  <div style="display: none" />
  <Teleport to="body">
    <div
      v-if="lightboxOpen"
      class="mermaid-lightbox"
      role="dialog"
      aria-modal="true"
      aria-label="Enlarged diagram"
      tabindex="-1"
      @click.self="closeLightbox"
    >
      <button
        type="button"
        class="mermaid-lightbox-close"
        aria-label="Close enlarged diagram"
        @click="closeLightbox"
      >
        ×
      </button>
      <div
        class="mermaid-lightbox-toolbar"
        @click.stop
      >
        <button
          type="button"
          class="mermaid-lightbox-tool"
          aria-label="Zoom in"
          @click="zoomIn"
        >
          +
        </button>
        <button
          type="button"
          class="mermaid-lightbox-tool"
          aria-label="Zoom out"
          @click="zoomOut"
        >
          −
        </button>
        <button
          type="button"
          class="mermaid-lightbox-tool"
          aria-label="Reset zoom"
          @click="resetZoom"
        >
          Reset
        </button>
      </div>
      <div
        class="mermaid-lightbox-scroll"
        @click.stop
      >
        <div
          ref="lightboxScaleRef"
          class="mermaid-lightbox-scale"
          :style="{ transform: `scale(${zoom})` }"
        />
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import mermaid from "mermaid";
import { useRoute } from "vitepress";
import { nextTick, onMounted, onUnmounted, ref, watch } from "vue";

const route = useRoute();

const lightboxOpen = ref(false);
const lightboxScaleRef = ref(null);
const zoom = ref(4.0);

const ZOOM_STEP = 1.15;
const ZOOM_MIN = 0.5;
const ZOOM_MAX = 4;

function zoomIn() {
  zoom.value = Math.min(
    ZOOM_MAX,
    Math.round(zoom.value * ZOOM_STEP * 100) / 100
  );
}

function zoomOut() {
  zoom.value = Math.max(
    ZOOM_MIN,
    Math.round((zoom.value / ZOOM_STEP) * 100) / 100
  );
}

function resetZoom() {
  zoom.value = 4.0;
}

function closeLightbox() {
  lightboxOpen.value = false;
}

function openLightbox(preEl) {
  const svg = preEl.querySelector("svg");
  if (!svg) {
    return;
  }
  lightboxOpen.value = true;
  resetZoom();
  nextTick(() => {
    const host = lightboxScaleRef.value;
    if (!host) {
      return;
    }
    host.innerHTML = "";
    host.appendChild(svg.cloneNode(true));
  });
}

/** Wrap each raw &lt;pre class="mermaid"&gt; so we can add an enlarge control without breaking mermaid.run(). */
function wrapMermaidBlocks() {
  const pres = document.querySelectorAll(".vp-doc pre.mermaid");
  pres.forEach((pre) => {
    if (pre.closest(".mermaid-diagram-block")) {
      return;
    }
    const block = document.createElement("div");
    block.className = "mermaid-diagram-block";
    const inner = document.createElement("div");
    inner.className = "mermaid-diagram-inner";
    pre.parentNode.insertBefore(block, pre);
    inner.appendChild(pre);
    block.appendChild(inner);

    const btn = document.createElement("button");
    btn.type = "button";
    btn.className = "mermaid-zoom-btn";
    btn.setAttribute("aria-label", "Enlarge diagram");
    btn.setAttribute("title", "Enlarge diagram");
    btn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/><path d="M11 8v6M8 11h6"/></svg>`;
    btn.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
      openLightbox(pre);
    });
    block.appendChild(btn);
  });
}

function wireEscapeKey() {
  const onKey = (e) => {
    if (e.key === "Escape" && lightboxOpen.value) {
      closeLightbox();
    }
  };
  window.addEventListener("keydown", onKey);
  return () => window.removeEventListener("keydown", onKey);
}

let removeEscapeListener = () => {};

watch(lightboxOpen, (open) => {
  removeEscapeListener();
  if (open) {
    removeEscapeListener = wireEscapeKey();
    document.documentElement.style.overflow = "hidden";
  } else {
    document.documentElement.style.overflow = "";
  }
});

const renderMermaid = async () => {
  await nextTick();
  wrapMermaidBlocks();
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

onUnmounted(() => {
  document.documentElement.style.overflow = "";
});
</script>
