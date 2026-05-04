<template>
  <section class="berlin-changelog-embed">
    <hr />
    <h2 id="zms-berlin-basisversion">
      ZMS Berlin — Änderungen an der Basisversion
    </h2>
    <p class="berlin-changelog-embed__note">
      Statischer Verlauf wie im zmsadmin-Changelog; die Quelle im Repository ist
      <code>zmsadmin/templates/block/changelog/changelogitem.twig</code>
      (Block <code>last-changelog-items</code> und Version 1.6.12). Bei
      Anpassungen dort dieses Fragment aktualisieren
      (<code>docs/.vitepress/theme/berlin-changelog-fragment.html</code>).
    </p>
    <div
      class="vp-doc berlin-changelog-embed__body"
      v-html="html"
    />
  </section>
</template>

<script setup>
import berlinHtml from "./berlin-changelog-fragment.html?raw";

const html = berlinHtml;
</script>

<style scoped>
.berlin-changelog-embed {
  margin-top: 2.5rem;
}

.berlin-changelog-embed__note {
  color: var(--vp-c-text-2);
  font-size: 14px;
  margin: 0.75rem 0 1rem;
}

.berlin-changelog-embed__body :deep(.changelog-item) {
  margin-top: 1.25rem;
  padding-top: 1.25rem;
  border-top: 1px solid var(--vp-c-divider);
}

.berlin-changelog-embed__body
  :deep(.last-changelog-items > .changelog-item:first-child) {
  margin-top: 0;
  padding-top: 0;
  border-top: none;
}

.berlin-changelog-embed__body :deep(h2 small) {
  font-weight: 400;
  color: var(--vp-c-text-2);
}
</style>
