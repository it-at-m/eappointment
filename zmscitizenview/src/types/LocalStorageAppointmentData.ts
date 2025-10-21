import { AppointmentImpl } from "@/types/AppointmentImpl";

export interface LocalStorageAppointmentData {
  timestamp: number;
  currentView: number;
  appointment: AppointmentImpl;
  captchaToken: string | undefined;
}
