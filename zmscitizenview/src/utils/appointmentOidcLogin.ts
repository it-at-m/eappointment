import type { AppointmentDTO } from "@/api/models/AppointmentDTO";
import type { CustomerData } from "@/types/CustomerData";
import type { GlobalState } from "@/types/GlobalState";

import { updateAppointment } from "@/api/ZMSAppointmentAPI";
import {
  saveUiToLocalStorage,
  setAppointmentAuthHashForLogin,
} from "@/utils/appointmentLoginStorage";
import { copyCustomerDataOntoAppointment } from "@/utils/rebookingContact";

export type AppointmentLoginUiSnapshot = {
  currentView: number;
  selectedServiceId?: string;
  selectedProviderId?: string;
  selectedServiceMap: Map<string, number> | Record<string, number>;
  selectedTimeslot: number;
  reservationStartMs?: number | null;
};

function selectedServiceMapRecord(
  selectedServiceMap: AppointmentLoginUiSnapshot["selectedServiceMap"]
): Record<string, number> {
  return selectedServiceMap instanceof Map
    ? Object.fromEntries(selectedServiceMap)
    : selectedServiceMap;
}

/** Persist stepper/office IDs for Münchner Login resume. Never stores PII. */
export function persistUiIdsForLogin(
  snapshot: AppointmentLoginUiSnapshot
): void {
  if (!snapshot.selectedServiceId || !snapshot.selectedProviderId) {
    return;
  }
  saveUiToLocalStorage({
    timestamp: Date.now(),
    currentView: snapshot.currentView,
    selectedServiceId: String(snapshot.selectedServiceId),
    selectedServiceMap: selectedServiceMapRecord(snapshot.selectedServiceMap),
    selectedProviderId: String(snapshot.selectedProviderId),
    selectedTimeslot: snapshot.selectedTimeslot,
    ...(snapshot.reservationStartMs != null
      ? { reservationStartMs: snapshot.reservationStartMs }
      : {}),
  });
}

export function startOidcLogin(
  snapshot: AppointmentLoginUiSnapshot,
  appointment?: Pick<AppointmentDTO, "processId" | "authKey">
): void {
  persistUiIdsForLogin(snapshot);
  setAppointmentAuthHashForLogin(appointment?.processId, appointment?.authKey);
  document.dispatchEvent(
    new CustomEvent("authorization-request", {
      detail: {
        loginProvider: undefined,
        authLevel: undefined,
      },
    })
  );
}

/**
 * Persist phone / Zusatzfelder on the reserved appointment so they survive
 * OAuth remount without writing PII to localStorage (ZMSKVR-1002 / CodeQL).
 */
export function requestLogin(
  snapshot: AppointmentLoginUiSnapshot,
  appointment: AppointmentDTO | undefined,
  customerData: CustomerData,
  globalState: GlobalState
): Promise<void> | void {
  if (appointment?.processId && appointment?.authKey) {
    copyCustomerDataOntoAppointment(appointment, customerData);
    return updateAppointment(globalState, appointment)
      .catch(() => undefined)
      .finally(() => startOidcLogin(snapshot, appointment))
      .then(() => undefined);
  }
  startOidcLogin(snapshot, appointment);
}
