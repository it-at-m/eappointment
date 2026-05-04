<template>
  <div class="changelog-embed">
    <p
      v-if="loading"
      class="changelog-embed__status"
    >
      Loading <code>CHANGELOG.md</code> from GitHub…
    </p>
    <p
      v-else-if="error"
      class="changelog-embed__status changelog-embed__status--error"
    >
      {{ error }}
    </p>
    <div
      v-else
      class="vp-doc changelog-embed__body"
      v-html="html"
    />
  </div>
</template>

<script setup>
import MarkdownIt from "markdown-it";
import { onMounted, ref } from "vue";

const API =
  "https://api.github.com/repos/it-at-m/eappointment/contents/CHANGELOG.md?ref=main";

const loading = ref(true);
const error = ref("");
const html = ref("");

// CHANGELOG.md mixes Markdown with raw HTML (Jira links, lists). html must be true so those tags render.
const md = new MarkdownIt({
  html: true,
  linkify: true,
  typographer: true,
});

function decodeGitHubBase64(b64) {
  const clean = b64.replace(/\n/g, "");
  const binary = atob(clean);
  const bytes = new Uint8Array(binary.length);
  for (let i = 0; i < binary.length; i++) {
    bytes[i] = binary.charCodeAt(i);
  }
  return new TextDecoder("utf-8").decode(bytes);
}

onMounted(async () => {
  try {
    const res = await fetch(API, {
      headers: { Accept: "application/vnd.github+json" },
    });
    if (res.status === 403) {
      error.value =
        "GitHub API rate limit or access denied. Open CHANGELOG.md on GitHub using the link above.";
      return;
    }
    if (!res.ok) {
      error.value = `Could not load changelog (HTTP ${res.status}).`;
      return;
    }
    const data = await res.json();
    if (data.encoding !== "base64" || typeof data.content !== "string") {
      error.value = "Unexpected response from GitHub API.";
      return;
    }
    const markdown = decodeGitHubBase64(data.content);
    html.value = md.render(markdown);
  } catch {
    error.value = "Network error while loading changelog from GitHub.";
  } finally {
    loading.value = false;
  }
});
</script>

<style scoped>
.changelog-embed {
  margin-top: 1rem;
}

.changelog-embed__status {
  color: var(--vp-c-text-2);
  font-size: 14px;
}

.changelog-embed__status--error {
  color: var(--vp-c-danger-1);
}

.changelog-embed__body {
  margin-top: 0.5rem;
}
</style>
