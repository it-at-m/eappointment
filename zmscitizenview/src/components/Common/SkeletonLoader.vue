<script setup lang="ts">
import { MucCard } from "@muenchen/muc-patternlab-vue";
</script>

<template>
  <muc-card title="">
    <template #headerPrefix>
      <div class="skeleton-wrapper">
        <div class="skeleton-col skeleton-details">
          <div class="skeleton-row skeleton-name">
            <span class="skeleton-name-text skeleton-loader" />
          </div>
        </div>
      </div>
    </template>
    <template #content>
      <div class="skeleton-wrapper">
        <div class="skeleton-col skeleton-details">
          <div class="skeleton-row skeleton-description">
            <span class="skeleton-description-text skeleton-loader" />
          </div>
          <div class="skeleton-row skeleton-description">
            <span class="skeleton-description-text skeleton-loader" />
          </div>
        </div>
      </div>
    </template>
  </muc-card>
</template>

<style scoped>
.skeleton-wrapper {
  display: flex;
  width: 95%;
}

.skeleton-wrapper .skeleton-row {
  display: flex;
  flex-direction: row;
}

.skeleton-wrapper .skeleton-col {
  display: flex;
  flex-direction: column;
}

.skeleton-wrapper .skeleton-details {
  width: 90%;
  margin-left: 17px;
}

.skeleton-wrapper .skeleton-details .skeleton-name {
  margin-bottom: 3px;
  width: 85%;
  display: block;
  max-width: 100%;
}

.skeleton-wrapper .skeleton-details .skeleton-name .skeleton-name-text {
  font-weight: bold;
  font-size: 16px;
  line-height: 23px;
  color: #002877;
  height: 40px;
}

.skeleton-wrapper .skeleton-details .skeleton-description {
  margin-bottom: 13px;
}

.skeleton-wrapper
  .skeleton-details
  .skeleton-description
  .skeleton-description-text {
  font-size: 13px;
  line-height: 18px;
  color: #666666;
}

.skeleton-loader:empty {
  width: 100%;
  height: 15px;
  display: block;
  background:
    linear-gradient(
      to right,
      rgba(255, 255, 255, 0),
      rgba(255, 255, 255, 0.5) 50%,
      rgba(255, 255, 255, 0) 80%
    ),
    lightgray;
  background-repeat: repeat-y;
  background-size: 50px 500px;
  background-position: 0 0;
  animation: shine 1s infinite alternate;
}

@keyframes shine {
  to {
    background-position: 100% 0;
  }
}
</style>
