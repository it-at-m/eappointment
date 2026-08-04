import { OfficeImpl } from "@/types/OfficeImpl";
import { ServiceImpl } from "@/types/ServiceImpl";

/**
 * Non-sensitive UI state persisted across Münchner Login.
 * Never store authKey, appointment credentials, PII (customerData), or captchaToken.
 */
export interface LocalStorageAppointmentData {
  timestamp: number;
  currentView: number;
  selectedService: ServiceImpl;
  selectedServiceMap: Record<string, number>;
  selectedProvider: OfficeImpl;
  selectedTimeslot: number;
}
