<template>
  <h2 class="m-component-form__title">Kontaktdaten</h2>
  <form class="m-form m-form--default">
    <muc-input label="Vorname" />
    <muc-input label="Nachname" />
    <muc-input label="E-Mail-Adresse" />
    <muc-input label="Telefonnummer" />
    <muc-text-area
      label="Bemerkungen"
      hint="Sie möchten uns zu Ihrem Termin noch etwas mitteilen? Geben Sie hier an, wenn Sie Hilfe benötigen, auf Übersetzungen angeweiesen sind oder sonstige Anliegen haben."
    />
  </form>
</template>

<script setup lang="ts">
import { MucInput, MucTextArea } from "@muenchen/muc-patternlab-vue";
</script>

<style scoped></style>
