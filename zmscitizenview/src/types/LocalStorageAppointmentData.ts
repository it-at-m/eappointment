export interface LocalStorageAppointmentData {
  timestamp: number;
  currentView: number;
  appointmentInfo: string;
  captchaToken: string | undefined;
}
