import { AppointmentImpl } from "@/types/AppointmentImpl";
import { CustomerData } from "@/types/CustomerData";
import { OfficeImpl } from "@/types/OfficeImpl";
import { ServiceImpl } from "@/types/ServiceImpl";

export interface LocalStorageAppointmentData {
  timestamp: number;
  currentView: number;
  selectedService: ServiceImpl;
  selectedServiceMap: Record<string, number>;
  selectedProvider: OfficeImpl;
  selectedTimeslot: number;
  customerData: CustomerData;
  appointment: AppointmentImpl;
  captchaToken: string | undefined;
}
