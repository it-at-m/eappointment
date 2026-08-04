import { CustomerData } from "@/types/CustomerData";
import { OfficeImpl } from "@/types/OfficeImpl";
import { ServiceImpl } from "@/types/ServiceImpl";

/**
 * Non-sensitive UI state persisted across Münchner Login.
 * Appointment credentials (processId / authKey) must never be stored here —
 * they travel via URL hash (ZMSKVR-1002).
 */
export interface LocalStorageAppointmentData {
  timestamp: number;
  currentView: number;
  selectedService: ServiceImpl;
  selectedServiceMap: Record<string, number>;
  selectedProvider: OfficeImpl;
  selectedTimeslot: number;
  customerData: CustomerData;
  captchaToken: string | undefined;
}
