<template>
  <Layout>
    <template #nav-bar-content-after>
      <a
        class="logo"
        href="https://www.muenchen.de"
        target="_blank"
        rel="noopener noreferrer"
        aria-label="Landeshauptstadt Muenchen"
      >
        <img
          src="https://assets.muenchen.de/logos/lhm/logo-lhm-muenchen.svg"
          alt="Landeshauptstadt Muenchen"
        />
      </a>
    </template>
  </Layout>
</template>

<script setup>
import DefaultTheme from "vitepress/theme";

const { Layout } = DefaultTheme;
</script>
