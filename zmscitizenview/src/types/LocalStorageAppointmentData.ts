/**
 * UI state persisted across Münchner Login (OAuth redirect remount).
 * Store only IDs/counts — never authKey, PII, captchaToken, or full
 * service/provider objects. Contact fields live on the reserved appointment.
 */
export interface LocalStorageUiData {
  timestamp: number;
  currentView: number;
  selectedServiceId: string;
  selectedServiceMap: Record<string, number>;
  selectedProviderId: string;
  selectedTimeslot: number;
  /** Epoch ms when the current reservation started — not PII. */
  reservationStartMs?: number;
}
