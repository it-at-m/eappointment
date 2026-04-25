<template>
  <Layout>
    <template #nav-bar-content-after>
      <div class="logo">
        <a href="https://www.muenchen.de/" target="_blank">
          <img
            src="https://assets.muenchen.de/logos/lhm/logo-lhm-muenchen.svg"
            alt="Logo Landeshauptstadt Muenchen"
          />
        </a>
      </div>
    </template>
  </Layout>
</template>

<script setup>
import DefaultTheme from "vitepress/theme";

const { Layout } = DefaultTheme;
</script>

<style scoped>
.logo {
  display: flex;
  align-items: center;
}
@media (min-width: 768px) {
  .extra + .logo:before {
    margin-right: 16px;
    margin-left: 16px;
    width: 1px;
    height: 24px;
    background-color: var(--vp-c-divider);
    content: "";
  }
}

.logo img {
  filter: var(--muc-logo-filter);
  height: 28px;
}
</style>
