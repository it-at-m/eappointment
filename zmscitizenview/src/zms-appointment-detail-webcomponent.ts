/**
 * main.ts
 *
 * Bootstraps Vuetify and other plugins then mounts the App`
 */

import { defineCustomElement } from "vue";

import I18nHost from "@/i18n-host.ce.vue";
import ZMSAppointmentDetailElement from "@/zms-appointment-detail.ce.vue";

const I18nHostElement = defineCustomElement(I18nHost);
customElements.define("i18n-host", I18nHostElement);

const zmsAppointmentDetailWebcomponent = defineCustomElement(
  ZMSAppointmentDetailElement
);
customElements.define(
  "zms-appointment-detail-wrapped",
  zmsAppointmentDetailWebcomponent
);
