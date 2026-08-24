import { AppointmentHash } from "@/types/AppointmentHashTypes";
import { LocalStorageUiData } from "@/types/LocalStorageAppointmentData";
import {
  LOCALSTORAGE_PARAM_APPOINTMENT_DATA,
  SESSIONSTORAGE_PARAM_APPOINTMENT_AUTH_HASH,
} from "@/utils/Constants";

/** UI localStorage is only valid for login resume within this window. */
export const LOCALSTORAGE_UI_TTL_MS = 30 * 60 * 1000;

export function encodeAppointmentAuthHash(
  processId: string,
  authKey: string
): string {
  return btoa(JSON.stringify({ id: processId, authKey }));
}

export function parseAppointmentHash(hash: string): AppointmentHash | null {
  try {
    // Add missing base64 padding if needed (padding may be stripped from URL)
    const padding = (4 - (hash.length % 4)) % 4;
    const paddedHash = padding > 0 ? hash + "=".repeat(padding) : hash;
    const appointmentData = JSON.parse(window.atob(paddedHash));
    if (
      appointmentData.id == undefined ||
      appointmentData.authKey == undefined
    ) {
      return null;
    }
    return appointmentData;
  } catch {
    return null;
  }
}

/**
 * Store credentials for the OAuth hop: sessionStorage survives IdP redirect
 * (URL fragment does not); replaceState avoids firing hashchange before leave.
 */
export function setAppointmentAuthHashForLogin(
  processId: string | undefined,
  authKey: string | undefined
): void {
  if (!processId || !authKey) {
    return;
  }

  const encoded = encodeAppointmentAuthHash(processId, authKey);
  sessionStorage.setItem(SESSIONSTORAGE_PARAM_APPOINTMENT_AUTH_HASH, encoded);
  history.replaceState(null, "", `#/appointment/${encoded}`);
}

export function saveUiToLocalStorage(uiData: LocalStorageUiData): void {
  localStorage.setItem(
    LOCALSTORAGE_PARAM_APPOINTMENT_DATA,
    JSON.stringify(uiData)
  );
}

export function clearAppointmentLocalStorage(): void {
  if (localStorage.getItem(LOCALSTORAGE_PARAM_APPOINTMENT_DATA)) {
    localStorage.removeItem(LOCALSTORAGE_PARAM_APPOINTMENT_DATA);
  }
}

export function clearAppointmentAuthHashSession(): void {
  if (sessionStorage.getItem(SESSIONSTORAGE_PARAM_APPOINTMENT_AUTH_HASH)) {
    sessionStorage.removeItem(SESSIONSTORAGE_PARAM_APPOINTMENT_AUTH_HASH);
  }
}

/**
 * Prefer hash from the URL/prop; else restore from the short-lived session bridge.
 */
export function resolveAppointmentAuthHash(
  appointmentHashFromProps?: string | null
): string | undefined {
  if (appointmentHashFromProps) {
    return appointmentHashFromProps;
  }

  const pendingHash = sessionStorage.getItem(
    SESSIONSTORAGE_PARAM_APPOINTMENT_AUTH_HASH
  );
  if (!pendingHash) {
    return undefined;
  }

  history.replaceState(null, "", `#/appointment/${pendingHash}`);
  return pendingHash;
}

export function parseUiLocalStorage(data: string): LocalStorageUiData | null {
  try {
    const raw = JSON.parse(data) as {
      timestamp?: number;
      currentView?: number;
      selectedServiceId?: string;
      selectedProviderId?: string;
      selectedServiceMap?: Record<string, number>;
      selectedTimeslot?: number;
      reservationStartMs?: number;
      selectedService?: { id?: string };
      selectedProvider?: { id?: string };
    };
    const selectedServiceId = raw.selectedServiceId ?? raw.selectedService?.id;
    const selectedProviderId =
      raw.selectedProviderId ?? raw.selectedProvider?.id;
    if (
      raw.timestamp == undefined ||
      raw.currentView == undefined ||
      selectedServiceId == undefined ||
      selectedProviderId == undefined
    ) {
      return null;
    }
    // Persist IDs only — never restore credentials / PII / full office objects.
    return {
      timestamp: raw.timestamp,
      currentView: raw.currentView,
      selectedServiceId: String(selectedServiceId),
      selectedServiceMap: raw.selectedServiceMap ?? {},
      selectedProviderId: String(selectedProviderId),
      selectedTimeslot: raw.selectedTimeslot ?? 0,
      ...(typeof raw.reservationStartMs === "number"
        ? { reservationStartMs: raw.reservationStartMs }
        : {}),
    };
  } catch {
    return null;
  }
}

export function getFreshLocalStorageUiData(
  nowMs: number = Date.now()
): LocalStorageUiData | null {
  const raw = localStorage.getItem(LOCALSTORAGE_PARAM_APPOINTMENT_DATA);
  if (!raw) {
    return null;
  }
  const parsed = parseUiLocalStorage(raw);
  if (!parsed || nowMs - parsed.timestamp >= LOCALSTORAGE_UI_TTL_MS) {
    clearAppointmentLocalStorage();
    return null;
  }
  return parsed;
}
