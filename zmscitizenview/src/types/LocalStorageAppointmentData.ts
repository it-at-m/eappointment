import { AppointmentImpl } from "@/types/AppointmentImpl";
import { OfficeImpl } from "@/types/OfficeImpl";
import { ServiceImpl } from "@/types/ServiceImpl";

/**
 * Persisted booking wizard state for resume after optional citizen login.
 * Must not include customer PII (name, mail, telephone) — see CodeQL
 * js/clear-text-storage-of-sensitive-data.
 */
export interface LocalStorageAppointmentData {
  timestamp: number;
  currentView: number;
  selectedService: ServiceImpl;
  selectedServiceMap: Record<string, number>;
  selectedProvider: OfficeImpl;
  selectedTimeslot: number;
  appointment: AppointmentImpl;
  captchaToken: string | undefined;
}
