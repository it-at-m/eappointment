/**
 * Booking wizard resume payload after optional citizen login.
 * IDs and primitives only — never persist customer PII or Scope objects
 * (Scope contains telephoneActivated / telephoneRequired flags that must
 * not be written to localStorage).
 */
export interface LocalStorageAppointmentData {
  timestamp: number;
  currentView: number;
  selectedServiceId: string;
  selectedServiceMap: Record<string, number>;
  selectedProviderId: string;
  selectedTimeslot: number;
  appointmentProcessId: string;
  appointmentDisplayNumber?: string | null;
  appointmentTimestamp: number;
  appointmentAuthKey: string;
  appointmentOfficeId: string;
  appointmentServiceId: string;
  appointmentServiceName: string;
  appointmentServiceCount: number;
  captchaToken: string | undefined;
}
