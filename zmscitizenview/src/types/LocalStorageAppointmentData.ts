/**
 * UI state persisted across Münchner Login (OAuth redirect remount).
 * Store IDs/counts plus Kontakt scratch fields needed after return
 * (phone / Zusatzfelder). Never authKey, captchaToken, or full
 * service/provider objects.
 */
export interface LocalStorageUiData {
  timestamp: number;
  currentView: number;
  selectedServiceId: string;
  selectedServiceMap: Record<string, number>;
  selectedProviderId: string;
  selectedTimeslot: number;
  /** Optional: survive Bürger-Login remount (ZMSKVR-1571). */
  telephoneNumber?: string;
  customTextfield?: string;
  customTextfield2?: string;
}
