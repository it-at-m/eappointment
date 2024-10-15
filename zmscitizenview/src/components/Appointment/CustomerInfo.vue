<template>
  <h2 class="m-component-form__title">Kontaktdaten</h2>
  <form class="m-form m-form--default">
    <MucInput label="Vorname" />
    <MucInput label="Nachname" />
    <MucInput label="E-Mail-Adresse" />
    <MucInput label="Telefonnummer" />
    <MucTextArea
      label="Bemerkungen"
      hint="Sie möchten uns zu Ihrem Termin noch etwas mitteilen? Geben Sie hier an, wenn Sie Hilfe benötigen, auf Übersetzungen angeweiesen sind oder sonstige Anliegen haben."
    />
  </form>
</template>

<script setup lang="ts">
import {MucInput} from "@muenchen/muc-patternlab-vue";
import {MucTextArea} from "@muenchen/muc-patternlab-vue";

</script>

<style scoped>

</style>
