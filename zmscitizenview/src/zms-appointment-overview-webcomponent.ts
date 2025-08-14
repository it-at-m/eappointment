/**
 * main.ts
 *
 * Bootstraps Vuetify and other plugins then mounts the App`
 */

import { defineCustomElement } from "vue";

import I18nHost from "@/i18n-host.ce.vue";
import ZMSAppointmentOverviewElement from "@/zms-appointment-overview.ce.vue";

const I18nHostElement = defineCustomElement(I18nHost);
customElements.define("i18n-host", I18nHostElement);

const zmsAppointmentOverviewWebcomponent = defineCustomElement(
  ZMSAppointmentOverviewElement
);
customElements.define(
  "zms-appointment-overview-wrapped",
  zmsAppointmentOverviewWebcomponent
);
