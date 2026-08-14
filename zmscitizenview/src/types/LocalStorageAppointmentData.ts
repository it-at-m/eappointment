/**
 * Non-sensitive UI state persisted across Münchner Login.
 * Store only IDs/counts — never authKey, PII, captchaToken, or full
 * service/provider objects (avoids clear-text storage of appointment-named fields).
 */
export interface LocalStorageUiData {
  timestamp: number;
  currentView: number;
  selectedServiceId: string;
  selectedServiceMap: Record<string, number>;
  selectedProviderId: string;
  selectedTimeslot: number;
}
